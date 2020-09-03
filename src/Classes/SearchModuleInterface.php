<?php declare(strict_types=1);

namespace Gebi84\SearchConsoleBundle\Classes;

interface SearchModuleInterface
{
    public function setLabel(string $label): SearchModuleInterface;

    public function getLabel(): string;

    public function setModule(string $module): SearchModuleInterface;

    public function getModule(): string;

    public function setTable(string $table): SearchModuleInterface;

    public function getTable(): string;

    public function setPTable(string $pTable): SearchModuleInterface;

    public function getPTable(): string;

    public function setShortcut(string $shortcut): SearchModuleInterface;

    public function getShortcut(): string;

    public function setEnableGoTo(bool $enableGoTo): SearchModuleInterface;

    public function isEnableGoTo(): bool;

    public function setEnableNew(bool $enableGoTo): SearchModuleInterface;

    public function isEnableNew(): bool;

    public function setFields(array $fields): SearchModuleInterface;

    public function getFields(): array;

    public function setSearchFields(array $fields): SearchModuleInterface;

    public function getSearchFields(): array;

    public function setFieldName(string $fieldName);

    public function getFieldName(): ?string;

    public function isEnableSearch(): bool;

    public function setEnableSearch(bool $enable): SearchModuleInterface;

    public function isDcaCallback(): bool;

    public function setDcaCallback(bool $dcaCallback);
}
