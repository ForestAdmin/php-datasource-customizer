<?php

namespace ForestAdmin\AgentPHP\DatasourceCustomizer\Decorators\Computed\Utils;

use ForestAdmin\AgentPHP\DatasourceCustomizer\Context\CollectionCustomizationContext;
use ForestAdmin\AgentPHP\DatasourceCustomizer\Decorators\Computed\ComputedCollection;
use ForestAdmin\AgentPHP\DatasourceCustomizer\Decorators\Computed\ComputedDefinition;
use ForestAdmin\AgentPHP\DatasourceToolkit\Components\Query\Projection\Projection;
use Illuminate\Support\Str;

class ComputeField
{
    public static function computeFromRecords(
        CollectionCustomizationContext $context,
        ComputedCollection $collection,
        Projection         $recordsProjection,
        Projection         $desiredProjection,
        array              $records
    ) {
        $paths = Flattener::withNullMarker($recordsProjection);
        $flatten = Flattener::flatten($records, $paths);
        $finalProjection = Flattener::withNullMarker($desiredProjection);

        foreach ($finalProjection as $path) {
            self::queueField($context, $collection, $path, $paths, $flatten);
        }

        return Flattener::unFlatten(
            $finalProjection->map(fn ($path) => $flatten[$paths->search($path)])->toArray(),
            $finalProjection
        );
    }

    public static function queueField(
        CollectionCustomizationContext $context,
        ComputedCollection $collection,
        string             $newPath,
        Projection         $paths,
        array              &$flatten
    ): void {
        if (! $paths->contains($newPath)) {
            $computed = $collection->getComputed($newPath);

            $computedDependencies = Flattener::withNullMarker(new Projection($computed->getDependencies()))->toArray();
            $nestedDependencies = (new Projection($computedDependencies))
                ->nest(Str::contains($newPath, ':') ? Str::before($newPath, ':') : null);

            foreach ($nestedDependencies as $path) {
                self::queueField($context, $collection, $path, $paths, $flatten);
            }

            $dependencyValues = $nestedDependencies->map(fn ($path) => $flatten[$paths->search($path)])->toArray();
            $paths->push($newPath);

            $flatten[] = self::computeField($context, $computed, $computedDependencies, $dependencyValues);
        }
    }

    public static function computeField(
        CollectionCustomizationContext $context,
        ComputedDefinition $computed,
        array              $computedDependencies,
        array              &$flatten
    ): array {
        return self::transformUniqueValues(
            Flattener::unFlatten($flatten, new Projection($computedDependencies)),
            static fn ($uniquePartials) => $computed->getValues($uniquePartials, $context)
        );
    }

    public static function transformUniqueValues(
        array    $inputs,
        \Closure $callback
    ): array {
        $indexes = [];
        $mapping = [];
        $uniqueInputs = [];

        foreach ($inputs as $input) {
            if ($input) {
                $hash = sha1(serialize($input));
                if (! isset($indexes[$hash])) {
                    $indexes[$hash] = count($uniqueInputs);
                    $uniqueInputs[] = $input;
                }
                $mapping[] = $indexes[$hash];
            } else {
                $mapping[] = -1;
            }
        }

        $uniqueOutputs = $callback($uniqueInputs);

        return collect($mapping)->map(fn ($index) => $index !== -1 ? $uniqueOutputs[$index] : null)->toArray();
    }
}
