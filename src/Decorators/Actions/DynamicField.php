<?php

namespace ForestAdmin\AgentPHP\DatasourceCustomizer\Decorators\Actions;

use ForestAdmin\AgentPHP\DatasourceToolkit\Exceptions\ForestException;

class DynamicField extends BaseFormElement
{
    public function __construct(
        protected string $type,
        protected ?string $label = null,
        protected ?string $id = null,
        protected ?string $description = null,
        protected \Closure|bool $isRequired = false,
        protected \Closure|bool $isReadOnly = false,
        protected ?\Closure $if = null,
        protected \Closure|string|null $value = null,
        protected \Closure|string|null $defaultValue = null,
        protected \Closure|string|null $collectionName = null,
        protected \Closure|array|null $enumValues = null,
        // TODO
        // protected ?string $placeholder = null,
    ) {
        parent::__construct($type);

        if($this->id === null && $this->label === null) {
            throw new ForestException("A field must have an 'id' or a 'label' defined.");
        }

        $this->id = $id ?? $label;
        $this->label = $label ?? $id;
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    /**
     * @param bool $isRequired
     */
    public function setIsRequired(bool $isRequired): void
    {
        $this->isRequired = $isRequired;
    }

    /**
     * @return bool
     */
    public function isReadOnly(): bool
    {
        return $this->isReadOnly;
    }

    /**
     * @param bool $isReadOnly
     */
    public function setIsReadOnly(bool $isReadOnly): void
    {
        $this->isReadOnly = $isReadOnly;
    }

    /**
     * @return \Closure|null
     */
    public function getIf(): ?\Closure
    {
        return $this->if;
    }

    /**
     * @param \Closure|null $if
     */
    public function setIf(?\Closure $if): void
    {
        $this->if = $if;
    }

    /**
     * @return \Closure|string|null
     */
    public function getValue(): string|\Closure|null
    {
        return $this->value;
    }

    /**
     * @param \Closure|string|null $value
     */
    public function setValue(string|\Closure|null $value): void
    {
        $this->value = $value;
    }

    /**
     * @return \Closure|string|null
     */
    public function getDefaultValue(): string|\Closure|null
    {
        return $this->defaultValue;
    }

    /**
     * @param \Closure|string|null $defaultValue
     */
    public function setDefaultValue(string|\Closure|null $defaultValue): void
    {
        $this->defaultValue = $defaultValue;
    }

    /**
     * @return \Closure|string|null
     */
    public function getCollectionName(): string|\Closure|null
    {
        return $this->collectionName;
    }

    /**
     * @param \Closure|string|null $collectionName
     */
    public function setCollectionName(string|\Closure|null $collectionName): void
    {
        $this->collectionName = $collectionName;
    }

    /**
     * @return array|\Closure|null
     */
    public function getEnumValues(): array|\Closure|null
    {
        return $this->enumValues;
    }

    /**
     * @param array|\Closure|null $enumValues
     */
    public function setEnumValues(array|\Closure|null $enumValues): void
    {
        $this->enumValues = $enumValues;
    }

    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    public function setPlaceholder(?string $placeholder): void
    {
        $this->placeholder = $placeholder;
    }

    public function isStatic(): bool
    {
        foreach ($this as $field => $value) {
            if (is_callable($value) && ! is_string($value)) {
                return false;
            }
        }

        return true;
    }
}
