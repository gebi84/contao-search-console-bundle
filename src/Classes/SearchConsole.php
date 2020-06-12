<?php declare(strict_types=1);

namespace Gebi84\SearchConsoleBundle\Classes;

use Contao\BackendUser;
use Contao\Controller;
use Contao\Database;
use Contao\System;
use Gebi84\SearchConsoleBundle\Classes\Query\Query;
use Gebi84\SearchConsoleBundle\Classes\Query\QueryBuilder;
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
        $items = [];

        $shortCuts = $this->getAvailableShortcutsFromSearch();
        if ($shortCuts) {
            $items = array_merge($items, $shortCuts);
        }
        $resultCount = count($items);
        //do search
        $doSearch = true;
        if ($resultCount === 1) {
            $doSearch = false;
        }

        $linksHtml = [];
        $links = [];

        if ($doSearch) {
            $searchModuleItems = $this->performSearchOnModules();
            if (!empty($searchModuleItems['links'])) {
                $links[] = $searchModuleItems['links'];
                $resultCount += (int) $searchModuleItems['resultCount'];
            }
            if (!empty($searchModuleItems['linksStrings'])) {
                $linksHtml = $searchModuleItems['linksStrings'];
            }
        }

        $return = [
            'items' => $items,
            'resultCount' => $resultCount,
            'linksHtml' => $linksHtml,
            'links' => $links,
        ];

        $sessionArray['return'] = $return;
        $sessionArray['returnTime'] = time();
        $this->session->set('search_connsole', $sessionArray);

        return $return;
    }

    protected function getAvailableShortcutsFromSearch(): array
    {
        $return = [];

        if (!empty($this->searchModules->getModules())) {
            /*  @var $searchModule SearchModule */
            foreach ($this->searchModules->getModules() as $searchModule) {

                //go to
                if ($searchModule->isEnableGoTo()) {
                    $found = false;
                    $cmdShortCut = 'g';
                    $label = $searchModule->getLabel() . '(' . $cmdShortCut . ' ' . ($searchModule->getShortcut() ? ' '.$searchModule->getShortcut() : '') . ')';
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
                    $cmdShortCut = 'new';
                    $label = $searchModule->getLabel() . '(' . $cmdShortCut . ($searchModule->getShortcut() ? ' '.$searchModule->getShortcut() : '') . ')';
                    $value = $cmdShortCut . ' ' . $searchModule->getShortcut();

                    //check for value, example "new p"
                    if (substr($value, 0, strlen($this->search)) === $this->search) {
                        $found = true;
                    } elseif (false !== strpos($cmdShortCut . ' ' . strtolower($label), $this->search)) { //check for string, example: "new Artikel"
                        $found = true;
                    }

                    if (6 === (int) $GLOBALS['TL_DCA'][$searchModule->getTable()]['tableName']['list']['sorting']['mode']) { //Displays the child records within a tree structure
                        $url = sprintf('contao?do=%s&act=paste&mode=create&rt=%s', $searchModule->getModule(), Helper::getRequestToken());
                    } else {
                        $url = sprintf('contao?do=%s&act=create&mode=create&rt=%s', $searchModule->getModule(), Helper::getRequestToken());
                    }

                    if ($found) {
                        $return[] = [
                            'label' => $label,
                            'value' => $value,
                            'id' => $value,
                            'category' => 'new',
                            'action' => 'redirect',
                            'url' => $url,
                        ];
                    }
                }
            }
        }

        usort($return, [$this, 'sortShortcutsByCategory']);

        return $return;
    }

    protected function performSearchOnModules(): array
    {
        $return = [];

        $search = $this->search;
        $fragments = Helper::getSearchFragments($search);

        $modules = [$this->getModuleByShortcut()];
        if (empty($modules[0])) {
            $modules = $this->searchModules->getModules();
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
            $return['links'] = [];
            $return['linksStrings'] = [];
            while ($item = $result->next()) {

                $links = [];

                if ($GLOBALS['TL_DCA'][$item->tableName]['fields']['pid'] && $item->pid) {

                    $parents = [];

                    if (5 === (int) $GLOBALS['TL_DCA'][$item->tableName]['list']['sorting']['mode']) { //treeview
                        $parents = $this->getParentElements((int) $item->pid, $item->tableName, $item->module);
                    } else {
                        if ($GLOBALS['TL_DCA'][$item->tableName]['config']['ptable'] || $item->ptable) {
                            $pTable = ($GLOBALS['TL_DCA'][$item->tableName]['config']['ptable']) ? $GLOBALS['TL_DCA'][$item->tableName]['config']['ptable'] : $item->ptable;
                            $parents = $this->getParentElements((int) $item->pid, $pTable, str_replace('tl_', '', $pTable));
                        }
                    }

                    if (!empty($parents)) {
                        krsort($parents);
                        foreach ($parents as $parent) {
                            $links[] = $parent;
                        }
                    }
                }

                $links[] = [
                    'label' => $item->label,
                    'name' => $item->name,
                    'id' => $item->id,
                    'pid' => $item->pid,
                    'module' => $item->module,
                    'tableName' => $item->tableName,
                ];

                $linkString = '';
                $activeModule = null;
                $counter = 0;
                $linksCount = count($links);

                $fragements = Helper::getSearchFragments($this->search);

                for ($i = 0; $i < $linksCount; $i++) {
                    if ($activeModule != $links[$i]['module']) {
                        if ($activeModule != null) {
                            $linkString .= ' | ';
                        }

                        if (!$links[$i]['label'] || strlen($links[$i]['label']) == 1) {
                            $links[$i]['label'] = $links[$i]['module'];
                        }

                        $linkString .= '<strong>' . $links[$i]['label'] . '</strong>: ';
                        $activeModule = $links[$i]['module'];
                    } else {
                        if ($counter <= $linksCount) {
                            $linkString .= ' < ';
                        }
                    }

                    \Controller::loadDataContainer($links[$i]['tableName']);

                    $name = (($links[$i]['name']) ? $links[$i]['name'] : $links[$i]['id']);
                    foreach ($fragements as $fragement) {
                        $name = preg_replace('#' . preg_quote($fragement) . '#i', '<mark>\\0</mark>', $name);
                    }

                    if (4 === (int) $GLOBALS['TL_DCA'][$links[$i]['tableName']]['list']['sorting']['mode']) { //display child record
                        $do = str_replace('tl_', '', $pTable);
                        if ($do === 'theme') {
                            $do = 'themes';
                        };

                        $link = 'contao'
                            . '?do=' . str_replace('tl_', '', $do)
                            . '&table=' . $links[$i]['tableName'] . '&act=edit&id=' . $links[$i]['id']
                            . '&ref=' . TL_REFERER_ID
                            . '&rt=' . \RequestToken::get();
                    } else {
                        if ($GLOBALS['TL_DCA'][$links[$i]['tableName']]['list']['sorting']['mode'] == 6) { //Displays the child records within a tree structure
                            $link = 'contao'
                                . '?do=' . $links[$i]['module']
                                . '&table=' . $GLOBALS['TL_DCA'][$links[$i]['tableName']]['config']['ctable'][0] . '&id=' . $links[$i]['id']
                                . '&ref=' . TL_REFERER_ID
                                . '&rt=' . \RequestToken::get();
                        } else {
                            $link = 'contao'
                                . '?do=' . $links[$i]['module'] . '&act=edit&id=' . $links[$i]['id']
                                . '&ref=' . TL_REFERER_ID
                                . '&rt=' . \RequestToken::get();
                        }
                    }

                    $linkString .= '<a data-activeModule="' . $activeModule . '" href="' . $link . '">' . $name . '</a>';

                    $counter++;
                }

                $return['linksStrings'][] = $linkString;
                if (!empty($links)) {
                    $return['links'][] = $link;
                }
            }
        }

        return $return;
    }

    protected function getModuleByShortcut(): ?SearchModule
    {
        $fragments = Helper::getSearchFragments($this->search);
        $firstFragment = $fragments[0];

        if (!empty($this->searchModules->getModules())) {
            /*  @var $searchModule SearchModule */
            foreach ($this->searchModules->getModules() as $searchModule) {
                if (substr($firstFragment, 0, strlen($firstFragment)) === $searchModule->getShortcut()) {
                    return $searchModule;
                }
            }
        }

        return null;
    }

    protected function getParentElements(int $pid, string $table, string $module)
    {

        if (!$table) {
            return;
        }

        $return = [];

        $query = 'SELECT * FROM ' . $table . ' WHERE id = ? LIMIT 1';
        $data = Database::getInstance()->prepare($query)->execute($pid)->fetchAssoc();
        $allowedNameFields = ['name', 'title', 'alias'];
        $nameField = 'id';
        if ($data) {
            foreach ($allowedNameFields as $field) {
                if ($data[$field]) {
                    $nameField = $field;
                    break;
                }
            }

            $return[] = [
                'label' => $GLOBALS['TL_LANG']['MOD'][$module][0],
                'name' => $data[$nameField],
                'id' => $data['id'],
                'pid' => $data['pid'],
                'module' => $module,
                'tableName' => $table,
            ];

            if ($data['pid'] > 0 && $GLOBALS['TL_DCA'][$table]['fields']['pid']) {
                if (5 === (int) $GLOBALS['TL_DCA'][$table]['list']['sorting']['mode']) { //treeview
                    $pTable = $table;
                } else {
                    if ($GLOBALS['TL_DCA'][$table]['config']['ptable'] || $table) {
                        $pTable = ($GLOBALS['TL_DCA'][$table]['config']['ptable']) ?? $table;
                        $module = str_replace('tl_', '', $pTable);
                    }
                }

                $r = $this->getParentElements((int) $data['pid'], $pTable, $module);
                if (!empty($r)) {
                    $return[] = $r[0];
                }
            }
        }

        return $return;
    }

    protected function sortShortcutsByCategory($a, $b)
    {
        if ($a['category'] === $b['category']) {
            return 0;
        }

        return ($a['category'] < $b['category']) ? -1 : 1;
    }

    protected function getSearchModules(): SearchModules
    {
        return $this->searchModules;
    }
}