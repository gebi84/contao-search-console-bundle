<?php declare(strict_types=1);

namespace Gebi84\SearchConsoleBundle\Classes\Query;

use Gebi84\SearchConsoleBundle\Classes\SearchModule;

interface QueryInterface
{
    public function __construct(SearchModule $module, string $search);

    public function getQuery(): string;

    public function getParameters(): array;
}