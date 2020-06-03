<?php declare(strict_types=1);

namespace Gebi84\SearchConsoleBundle\Controller;

use Contao\Controller;
use Contao\CoreBundle\Controller\AbstractController;
use Gebi84\SearchConsoleBundle\Classes\SearchConsole;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("_contao-search-console", defaults={"_scope" = "backend"})
 */
class SearchConsoleController extends AbstractController
{
    /**
     * @Route("/search")
     * @Route("/search/{search}")
     */
    public function searchAction(
        SearchConsole $searchConsole
    ): Response {
        $this->initializeContaoFramework();

        $response = $searchConsole->search();

        return $this->sendResponse($response);
    }

    protected function sendResponse(array $response): Response
    {
        $responseObj = new Response();
        $responseObj->setContent(
            json_encode($response)
        );
        $responseObj->headers->set('Content-Type', 'application/json');

        return $responseObj;
    }

    /**
     * @Route("/result")
     */
    public function resultAction(
        SearchConsole $searchConsole
    ) {
        $this->initializeContaoFramework();

        $response = $searchConsole->search();
        if ($response['resultCount'] === 1 && $response['items'][0]['action'] === 'redirect') {
            return Controller::redirect($response['items'][0]['url']);
        }

        die('todo');
    }
}