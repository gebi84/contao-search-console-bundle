<?php declare(strict_types=1);

namespace Gebi84\SearchConsoleBundle\Classes;

class SearchModules
{
    /**
     * @var array
     */
    private $modules = [];

    public function addModule(SearchModule $module): self
    {
        $this->modules[] = $module;

        return $this;
    }

    public function getModules()
    {
        return $this->modules;
    }
}