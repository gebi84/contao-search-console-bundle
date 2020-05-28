<?php declare(strict_types=1);

namespace Gebi84\SearchConsoleBundle\Classes;

use Contao\BackendUser;
use Contao\Controller;
use Contao\System;
use Contao\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SearchConsole
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @var BackendUser
     */
    private $user;

    public function __construct(
        RequestStack $requestStack,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->request = $requestStack->getCurrentRequest();
        $this->authorizationChecker = $authorizationChecker;

        System::loadLanguageFile('default');
        System::loadLanguageFile('modules');
    }

    public function search()
    {
        if (!$this->authorizationChecker->isGranted('ROLE_USER')) {
            return $this->sendResponse(['redirect' => 'contao/login']);
        }

        $this->user = BackendUser::getInstance();

        $this->getModules();

        return $this->sendResponse([
            'items' => [
                ['label' => 'Test', 'value' => 'test', 'id' => 'test', 'category' => 'hallo'],
            ],
            'resultCount' => 1,
        ]);
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

    protected function getModules(): array
    {
        $modules = [];

        if(empty($this->modules)) {
            if ($GLOBALS['search_console']['modules'] && is_array($GLOBALS['search_console']['modules'])) {
                foreach ($GLOBALS['search_console']['modules'] as $alias => $searchConsoleConfig) {

                    $module = $searchConsoleConfig['module'];
                    $moduleConfig = Helper::getBackendModuleConfig($module, $this->user);
                    if (!empty($moduleConfig)) {

                        $label = $searchConsoleConfig['label'] ?? null;
                        if (!$label) {
                            $label = $GLOBALS['TL_LANG']['MOD'][$module][0];
                        }

                        $table = $searchConsoleConfig['table'] ?? $moduleConfig['tables'][0];

                        Controller::loadDataContainer($table);
                        $pTable = $searchConsoleConfig['pTable'] ?? null;
                        if (!$pTable) {
                            $pTable = $GLOBALS['TL_DCA'][$table]['config']['ptable'] ?? null;
                        }

                        $pTableField = $searchConsoleConfig['pTableField'] ?? null;

                        $modules[$alias] = [
                            'label' => $label,
                            'module' => $module,
                            'table' => $table,
                            'pTable' => $pTable,
                            'fields' => Helper::getFieldsFromDca($table)
                        ];
                    }

                }
            }
        }

        dd($modules);

        return $modules;
    }
}