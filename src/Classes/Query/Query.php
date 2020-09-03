<?php declare(strict_types=1);

namespace Gebi84\SearchConsoleBundle\Classes\Query;

use Gebi84\SearchConsoleBundle\Classes\Helper;
use Gebi84\SearchConsoleBundle\Classes\SearchModule;

class Query implements QueryInterface
{
    /**
     * @var string
     */
    protected $search;

    /**
     * @var SearchModule
     */
    protected $module;

    /**
     * @var array
     */
    protected $parameters = [];

    public function __construct(SearchModule $module, string $search = '')
    {
        $this->search = $search;
        $this->module = $module;
    }

    public function getQuery(): string
    {
        $alias = 's_'.$this->module->getModule();
        $table = $this->module->getTable();

        if ($GLOBALS['TL_DCA'][$table]['fields']['pid']) {
            $pid = $alias . '.pid,';
        } else {
            $pid = '"" AS pid,';
        }
        if(!empty($this->module->getPtable())) {
            $ptable = '"'.$this->module->getPtable().'" AS ptable,';
        } elseif ($GLOBALS['TL_DCA'][$table]['fields']['ptable']) {
            $ptable = $alias . '.ptable,';
        } else {
            $ptable = '"" AS ptable,';
        }

        $query = '
        SELECT
            ' . $alias . '.id,
            ' . $pid . '
            ' . $ptable . '
            "' . $this->module->getModule() . '" AS module,
            "' . $this->module->getLabel() . '" AS label,
            "' . $table . '" AS tableName,
            ' . $alias . '.' . $this->module->getFieldName() . ' AS name
        FROM
            ' . $table . ' AS ' . $alias . '
        ';

        $fragments = Helper::getSearchFragments($this->search);

        $wheres = [];
        foreach ($fragments as $fragment) {
            foreach ($this->module->getSearchFields() as $searchField) {
                $wheres[] = $alias . '.' . $searchField . ' like ?';
                $this->parameters[] = '%' . $fragment . '%';
            }
        }

        if (!empty($wheres)) {
            $query .= ' WHERE ' . implode(' OR ', $wheres);
        }

        return $query;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

}