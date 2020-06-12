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

        $response['resultHtml'] = $this->renderView('@Gebi84SearchConsole/resultJs.html.twig',
            [
                'linksHtml' => $response['linksHtml'],
            ]
        );

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

        if ((int) $response['resultCount'] === 1 && $response['items'][0]['action'] === 'redirect') {
            return Controller::redirect($response['items'][0]['url']);
        } elseif ((int) $response['resultCount'] === 1 && !empty($response['links'])) {
            return Controller::redirect($response['links'][0]['url']);
        }

        return $this->render(
            '@Gebi84SearchConsole/resultBackend.html.twig',
            [
                'linksHtml' => $response['linksHtml'],
            ]
        );
    }
}