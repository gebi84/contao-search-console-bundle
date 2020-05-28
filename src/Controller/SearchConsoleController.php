<?php declare(strict_types=1);

namespace Gebi84\SearchConsoleBundle\Controller;

use Contao\CoreBundle\Controller\AbstractController;
use Gebi84\SearchConsoleBundle\Classes\SearchConsole;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("_contao-search-console", defaults={"_scope" = "backend"})
 */
class SearchConsoleController extends AbstractController
{
    /**
     * @Route("/search")
     */
    public function searchAction(SearchConsole $searchConsole)
    {
        $this->initializeContaoFramework();

        return $searchConsole->search();
    }
}