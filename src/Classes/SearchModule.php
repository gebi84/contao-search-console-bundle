<?php declare(strict_types=1);

namespace Gebi84\SearchConsoleBundle\Classes;

class SearchModule implements SearchModuleInterface
{
    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $module;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var string
     */
    protected $ptable;

    /**
     * @var string
     */
    protected $shortcut;

    /**
     * @var bool
     */
    protected $enableGoTo;

    /**
     * @var bool
     */
    protected $enableNew;

    /**
     * @var array
     */
    protected $fields;

    /**
     * @var array
     */
    protected $searchFields;

    /**
     * @var string
     */
    protected $fieldName;

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label ?? '';
    }

    /**
     * @param string $label
     */
    public function setLabel(string $label): SearchModuleInterface
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getModule(): string
    {
        return $this->module;
    }

    /**
     * @param string $module
     */
    public function setModule(string $module): SearchModuleInterface
    {
        $this->module = $module;

        return $this;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @param string $table
     */
    public function setTable(string $table): SearchModuleInterface
    {
        $this->table = $table;

        return $this;
    }

    /**
     * @return string
     */
    public function getPtable(): string
    {
        return $this->ptable ?? '';
    }

    /**
     * @param string $ptable
     */
    public function setPtable(string $ptable): SearchModuleInterface
    {
        $this->ptable = $ptable;

        return $this;
    }

    /**
     * @return string
     */
    public function getShortcut(): string
    {
        return $this->shortcut;
    }

    /**
     * @param string $shortcut
     */
    public function setShortcut(string $shortcut): SearchModuleInterface
    {
        $this->shortcut = $shortcut;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnableGoTo(): bool
    {
        return $this->enableGoTo;
    }

    /**
     * @param bool $enableGoTo
     */
    public function setEnableGoTo(bool $enableGoTo): SearchModuleInterface
    {
        $this->enableGoTo = $enableGoTo;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnableNew(): bool
    {
        return $this->enableNew;
    }

    /**
     * @param bool $enableNew
     */
    public function setEnableNew(bool $enableNew): SearchModuleInterface
    {
        $this->enableNew = $enableNew;

        return $this;
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param array $fields
     */
    public function setFields(array $fields): SearchModuleInterface
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * @return array
     */
    public function getSearchFields(): array
    {
        return $this->searchFields;
    }

    /**
     * @param array $searchFields
     */
    public function setSearchFields(array $searchFields): SearchModuleInterface
    {
        $this->searchFields = $searchFields;

        return $this;
    }

    /**
     * @return string
     */
    public function getFieldName(): ?string
    {
        return $this->fieldName;
    }

    /**
     * @param string $fieldName
     */
    public function setFieldName(string $fieldName): SearchModuleInterface
    {
        $this->fieldName = $fieldName;

        return $this;
    }
}
