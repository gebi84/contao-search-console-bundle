<?php declare(strict_types=1);

namespace Gebi84\SearchConsoleBundle\Classes;

use Contao\BackendUser;
use Contao\Controller;
use Contao\System;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
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
    protected $user;

    /**
     * @var array
     */
    protected $modules;

    /**
     * @var string
     */
    private $search;

    public function __construct(
        RequestStack $requestStack,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->request = $requestStack->getCurrentRequest();
        $this->authorizationChecker = $authorizationChecker;

        System::loadLanguageFile('default');
        System::loadLanguageFile('modules');
    }

    public function search(): array
    {
        if (!$this->authorizationChecker->isGranted('ROLE_USER')) {
            return $this->sendResponse(['redirect' => 'contao/login']);
        }

        $this->user = BackendUser::getInstance();

        $this->search = strtolower($this->request->get('search', ''));

        //load all allowedModules
        $this->getModules();

        $items = [];

        $shortCuts = $this->getAvailableShortcutsFromSearch();
        if ($shortCuts) {
            $items = array_merge($items, $shortCuts);
        }

        return [
            'items' => $items,
            'resultCount' => count($items),
        ];
    }



    protected function getModules(): array
    {
        $modules = [];

        if (empty($this->modules)) {
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

                        $modules[$alias] = [
                            'label' => $label,
                            'module' => $module,
                            'table' => $table,
                            'pTable' => $pTable,
                            'shortcut' => $searchConsoleConfig['shortcut'],
                            'enableGoTo' => $searchConsoleConfig['enableGoTo'],
                            'enableNew' => $searchConsoleConfig['enableNew'],
                            'fields' => Helper::getFieldsFromDca($table),
                        ];
                    }
                }
            }

            $this->modules = $modules;
        }

        return $this->modules;
    }

    protected function getAvailableShortcutsFromSearch(): array
    {
        $return = [];

        foreach ($this->getModules() as $item) {

            //go to
            if ($item['enableGoTo']) {
                $found = false;
                $cmdShortCut = 'g';
                $label = $item['label'] . '('.$cmdShortCut.' ' . $item ['shortcut'] . ')';
                $value = $cmdShortCut.' ' . $item['shortcut'];

                //check for value, example "g p"
                if(substr($value,0, strlen($this->search)) === $this->search) {
                    $found = true;
                } elseif(false !== strpos($cmdShortCut.' ' . strtolower($label), $this->search)) { //check for string, example: "g Artikel"
                    $found = true;
                }

                if ($found) {
                    $return[] = [
                        'label' => $label,
                        'value' => $value,
                        'id' => $value,
                        'category' => 'goto',
                        'action' => 'redirect',
                        'url' => sprintf('contao?do=%s&rt=%s', $item['module'], Helper::getRequestToken()),
                    ];
                }
            }

            //new to
            if ($item['enableNew']) {
                $found = false;
                $cmdShortCut = 'n';
                $label = $item['label'] . '('.$cmdShortCut.' ' . $item ['shortcut'] . ')';
                $value = $cmdShortCut.' ' . $item['shortcut'];

                //check for value, example "g p"
                if(substr($value,0, strlen($this->search)) === $this->search) {
                    $found = true;
                } elseif(false !== strpos($cmdShortCut.' ' . strtolower($label), $this->search)) { //check for string, example: "g Artikel"
                    $found = true;
                }

                if ($found) {
                    $return[] = [
                        'label' => $label,
                        'value' => $value,
                        'id' => $value,
                        'category' => 'new',
                        'action' => 'redirect',
                        'url' => sprintf('contao?do=%s&act=paste&mode=create&rt=%s', $item['module'], Helper::getRequestToken()),
                    ];
                }
            }
        }

        return $return;
    }
}