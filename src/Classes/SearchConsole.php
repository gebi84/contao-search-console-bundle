<?php declare(strict_types=1);

namespace Gebi84\SearchConsoleBundle\Classes;

use Contao\BackendUser;
use Contao\Controller;
use Contao\System;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
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
     * @var SearchModules
     */
    protected $searchModules;

    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var string
     */
    private $search;

    public function __construct(
        RequestStack $requestStack,
        AuthorizationCheckerInterface $authorizationChecker,
        SessionInterface $session
    ) {
        $this->request = $requestStack->getCurrentRequest();
        $this->authorizationChecker = $authorizationChecker;
        $this->session = $session;

        $this->searchModules = new SearchModules();

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

        $sessionArray = $this->session->get('search_connsole');
        if (!is_array($sessionArray)) {
            $sessionArray = [];
        }

        //load from session if same search and not older than 10 seconds
        if (isset($sessionArray['return']) && (time() - $sessionArray['returnTime']) < 10) {
//            return $sessionArray['return'];
        }

        //load all allowedModules
        $this->getSearchModules();

        $items = [];

        $shortCuts = $this->getAvailableShortcutsFromSearch();
        if ($shortCuts) {
            $items = array_merge($items, $shortCuts);
        }
        $resultCount = count($items);

        //do search
        $searchModuleItems = $this->performSearchOnModules();
        $linksHtml = '';
        $links = [];
        if (!empty($searchModuleItems['links'])) {

            foreach ($searchModuleItems['links'] as $link) {
                $linksHtml .= '<a href="'.$link['url'].'">'.$link['label'].'</a>';
            }
            
            $links = $searchModuleItems['links'];
            $resultCount += (int) $searchModuleItems['resultCount'];
        }

        $return = [
            'items' => $items,
            'resultCount' => $resultCount,
            'resultHtml' => $linksHtml,
            'links' => $links
        ];

        $sessionArray['return'] = $return;
        $sessionArray['returnTime'] = time();
        $this->session->set('search_connsole', $sessionArray);

        return $return;
    }

    protected function getSearchModules(): SearchModules
    {
        if (empty($this->searchModules->getModules())) {
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
                            $pTable = $GLOBALS['TL_DCA'][$table]['config']['ptable'] ?? '';
                        }

                        $fields = Helper::getFieldsFromDca($table);
                        $searchFields = $searchConsoleConfig['searchFields'] ?? [];
                        $allowedFieldNames = ['name', 'title', 'alias', 'id'];
                        if (!$searchFields) {
                            $searchFields = [];
                            foreach ($allowedFieldNames as $allowedFieldName) {
                                if (array_key_exists($allowedFieldName, $fields)) {
                                    $searchFields[] = $allowedFieldName;
                                }
                            }
                        }

                        $fieldName = $searchConsoleConfig['fieldName'] ?? '';
                        if (empty($fieldName)) {
                            foreach ($allowedFieldNames as $allowedFieldName) {
                                if (array_key_exists($allowedFieldName, $fields)) {
                                    $fieldName = $allowedFieldName;
                                    break;
                                }
                            }
                        }

                        $searchModule = new SearchModule();
                        $searchModule
                            ->setLabel($label)
                            ->setModule($module)
                            ->setTable($table)
                            ->setPTable($pTable)
                            ->setShortcut($searchConsoleConfig['shortcut'] ?? '')
                            ->setEnableGoTo($searchConsoleConfig['enableGoTo'] ?? false)
                            ->setEnableNew($searchConsoleConfig['enableNew'] ?? false)
                            ->setFields($fields)
                            ->setSearchFields($searchFields)
                            ->setFieldName($fieldName);

                        $this->searchModules->addModule($searchModule);
                    }
                }
            }
        }

        return $this->searchModules;
    }

    protected function getAvailableShortcutsFromSearch(): array
    {
        $return = [];

        if (!empty($this->getSearchModules()->getModules())) {
            /*  @var $searchModule SearchModule */
            foreach ($this->getSearchModules()->getModules() as $searchModule) {

                //go to
                if ($searchModule->isEnableGoTo()) {
                    $found = false;
                    $cmdShortCut = 'g';
                    $label = $searchModule->getLabel() . '(' . $cmdShortCut . ' ' . $searchModule->getShortcut() . ')';
                    $value = $cmdShortCut . ' ' . $searchModule->getShortcut();

                    //check for value, example "g p"
                    if (substr($value, 0, strlen($this->search)) === $this->search) {
                        $found = true;
                    } elseif (false !== strpos($cmdShortCut . ' ' . strtolower($label), $this->search)) { //check for string, example: "g Artikel"
                        $found = true;
                    }

                    if ($found) {
                        $return[] = [
                            'label' => $label,
                            'value' => $value,
                            'id' => $value,
                            'category' => 'goto',
                            'action' => 'redirect',
                            'url' => sprintf('contao?do=%s&rt=%s', $searchModule->getModule(), Helper::getRequestToken()),
                        ];
                    }
                }

                //new to
                if ($searchModule->isEnableNew()) {
                    $found = false;
                    $cmdShortCut = 'n';
                    $label = $searchModule->getLabel() . '(' . $cmdShortCut . ' ' . $searchModule->getShortcut() . ')';
                    $value = $cmdShortCut . ' ' . $searchModule->getShortcut();

                    //check for value, example "g p"
                    if (substr($value, 0, strlen($this->search)) === $this->search) {
                        $found = true;
                    } elseif (false !== strpos($cmdShortCut . ' ' . strtolower($label), $this->search)) { //check for string, example: "g Artikel"
                        $found = true;
                    }

                    if ($found) {
                        $return[] = [
                            'label' => $label,
                            'value' => $value,
                            'id' => $value,
                            'category' => 'new',
                            'action' => 'redirect',
                            'url' => sprintf('contao?do=%s&act=paste&mode=create&rt=%s', $searchModule->getModule(), Helper::getRequestToken()),
                        ];
                    }
                }
            }
        }

        return $return;
    }

    protected function performSearchOnModules(): array
    {
        $return = [];

        $search = $this->search;
        $fragments = Helper::getSearchFragments($search);

        $modules = [$this->getModuleByShortcut()];
        if (empty($modules[0])) {
            $modules = $this->getSearchModules()->getModules();
        } else {
            array_shift($fragments);
            $search = implode(' ', $fragments);
        }

        $queryBuilder = new QueryBuilder();
        if (!empty($modules)) {
            /*  @var $searchModule SearchModule */
            foreach ($modules as $searchModule) {
                $queryBuilder->addQuery(new Query($search, $searchModule));
            }
        }

        $result = $queryBuilder->getResult();

        $links = [];

        $return['resultCount'] = 0;
        if ($result->numRows > 0) {
            $return['resultCount'] = $result->numRows;
            while ($item = $result->next()) {

                $link = '';
                if (4 === (int) $GLOBALS['TL_DCA'][$item->tableName]['list']['sorting']['mode']) { //display child record
                    $link = sprintf('contao?do=%s&table=%s&rt=%s&act=edit&id=%s',
                        $item->module,
                        $item->tableName,
                        Helper::getRequestToken(),
                        $item->id
                    );
                } elseif (6 === (int) $GLOBALS['TL_DCA'][$item->tableName]['tableName']['list']['sorting']['mode']) { //Displays the child records within a tree structure
                    $link = sprintf('contao?do=%s&table=%s&rt=%s',
                        $item->module,
                        $item->tableName,
                        Helper::getRequestToken()
                    );
                } else {
                    $link = sprintf('contao?do=%s&table=%s&rt=%s&act=edit&id=%s',
                        $item->module,
                        $item->tableName,
                        Helper::getRequestToken(),
                        $item->id
                    );
                }

                $links[] = [
                    'url' => $link,
                    'label' => $item->label .' '.$item->name
                ];
            }
        }

        $return['links'] = $links;

        return $return;
    }

    protected function getModuleByShortcut(): ?SearchModule
    {
        $fragments = Helper::getSearchFragments($this->search);
        $firstFragment = $fragments[0];

        if (!empty($this->getSearchModules()->getModules())) {
            /*  @var $searchModule SearchModule */
            foreach ($this->getSearchModules()->getModules() as $searchModule) {
                if (substr($firstFragment, 0, strlen($firstFragment)) === $searchModule->getShortcut()) {
                    return $searchModule;
                }
            }
        }

        return null;
    }
}