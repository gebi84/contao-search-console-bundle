<?php declare(strict_types=1);

namespace Gebi84\SearchConsoleBundle\Classes;

use Contao\Database;
use Contao\Database\Result;

class QueryBuilder
{
    protected $queries = [];

    public function addQuery(QueryInterface $query)
    {
        $this->queries[] = $query;

        return $this;
    }

    public function getResult(): Result
    {
        /* @var $query Query */
        $queries = [];
        $params = [];
        foreach ($this->queries as $query) {
            $queries[] = $query->getQuery();
            if(!empty($query->getParameters())) {
                foreach ($query->getParameters() as $param) {
                    $params[] = $param;
                }
            }
        }

        $query = 'SELECT allData.* FROM (';
        $query .= implode(' UNION ', $queries);
        $query .= ') AS allData LIMIT 20';

        return Database::getInstance()->prepare($query)->execute($params);
    }
}