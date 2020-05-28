<?php

declare(strict_types=1);

namespace Gebi84\SearchConsoleBundle;

use Gebi84\SearchConsoleBundle\DependencyInjection\SearchConsoleBundleExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class Gebi84SearchConsoleBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new SearchConsoleBundleExtension();
    }
}
