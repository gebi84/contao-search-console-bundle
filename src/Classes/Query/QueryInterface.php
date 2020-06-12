<?php declare(strict_types=1);

namespace Gebi84\SearchConsoleBundle\Classes\Query;

use Gebi84\SearchConsoleBundle\Classes\SearchModule;

interface QueryInterface
{
    public function __construct(string $search, SearchModule $module);

    public function getQuery(): string;

    public function getParameters(): array;
}