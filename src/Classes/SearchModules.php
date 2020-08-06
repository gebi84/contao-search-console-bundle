<?php declare(strict_types=1);

namespace Gebi84\SearchConsoleBundle\Classes;

use Contao\Controller;
use Contao\Model;

class SearchModules
{
    /**
     * @var array
     */
    private $modules = [];

    public function getModules()
    {
        if (empty($this->modules)) {
            //first load global registered modules
            $this->loadBackendModules();

            $this->loadModulesBySearchConsoleConfig();
        }

        return $this->modules;
    }

    protected function loadBackendModules(): void
    {
        if (!empty($GLOBALS['BE_MOD'])) {
            foreach ($GLOBALS['BE_MOD'] as $backendModules) {
                foreach ($backendModules as $backendModule => $backendModuleConfig) {
                    $moduleConfig = Helper::getBackendModuleConfig($backendModule);

                    if ($moduleConfig) {
                        if (isset($moduleConfig['tables'])) {
                            $label = $GLOBALS['TL_LANG']['MOD'][$backendModule][0];
                            $table = $moduleConfig['tables'][0];

                            if (empty($table)) {
                                continue;
                            }

                            //has a db model?
                            $model = Model::getClassFromTable($table);
                            if (!class_exists($model)) {
                                continue;
                            }

                            Controller::loadDataContainer($table);

                            $pTable = $GLOBALS['TL_DCA'][$table]['config']['ptable'] ?? '';
                            $fields = Helper::getFieldsFromDca($table);

                            $allowedFieldNames = ['name', 'title', 'alias', 'id'];
                            $searchFields = [];
                            foreach ($allowedFieldNames as $allowedFieldName) {
                                if (array_key_exists($allowedFieldName, $fields)) {
                                    $searchFields[] = $allowedFieldName;
                                }
                            }

                            $fieldName = 'id';
                            foreach ($allowedFieldNames as $allowedFieldName) {
                                if (array_key_exists($allowedFieldName, $fields)) {
                                    $fieldName = $allowedFieldName;
                                    break;
                                }
                            }

                            $shortCut = '';
                            $enableGoTo = true;
                            $enableNew = true;
                            
                            if (isset($GLOBALS['TL_DCA'][$table]['config']['closed']) && $GLOBALS['TL_DCA'][$table]['config']['closed'] === true) {
                                $enableNew = false;
                            }
                            if (isset($GLOBALS['TL_DCA'][$table]['config']['notCreatable']) && $GLOBALS['TL_DCA'][$table]['config']['notCreatable'] === true) {
                                $enableNew = false;
                            }
                            if (isset($GLOBALS['TL_DCA'][$table]['config']['notEditable']) && $GLOBALS['TL_DCA'][$table]['config']['notEditable'] === true) {
                                $enableGoTo = false;
                            }

                            $searchModule = new SearchModule();
                            $searchModule
                                ->setLabel($label)
                                ->setModule($backendModule)
                                ->setTable($table)
                                ->setPTable($pTable)
                                ->setShortcut($shortCut)
                                ->setEnableGoTo($enableGoTo)
                                ->setEnableNew($enableNew)
                                ->setFields($fields)
                                ->setSearchFields($searchFields)
                                ->setFieldName($fieldName);

                            $this->addModule($searchModule);
                        }
                    }
                }
            }
        }
    }

    public function addModule(SearchModule $module): self
    {
        $this->modules[] = $module;

        return $this;
    }

    protected function loadModulesBySearchConsoleConfig()
    {
        if ($GLOBALS['search_console']['modules'] && is_array($GLOBALS['search_console']['modules'])) {
            foreach ($GLOBALS['search_console']['modules'] as $alias => $searchConsoleConfig) {
                $module = $searchConsoleConfig['module'];
                $moduleConfig = Helper::getBackendModuleConfig($module);
                if (!empty($moduleConfig)) {

                    $searchModule = $this->getModule($module);
                    if (!$searchModule instanceof SearchModule) {
                        $searchModule = new SearchModule();
                    }

                    $label = $searchConsoleConfig['label'] ?? $searchModule->getLabel();
                    if (empty($label)) {
                        $label = $GLOBALS['TL_LANG']['MOD'][$module][0];
                    }

                    $table = $searchConsoleConfig['table'] ?? $moduleConfig['tables'][0];

                    //has a db model?
                    $model = Model::getClassFromTable($table);
                    if (!class_exists($model)) {
                        continue;
                    }
                    $class = new $model();
                    if (!$class instanceof \Model) {
                        continue;
                    }
                    unset($class);

                    Controller::loadDataContainer($table);
                    $pTable = $searchConsoleConfig['pTable'] ?? $searchModule->getPtable();
                    if (empty($pTable)) {
                        $pTable = $GLOBALS['TL_DCA'][$table]['config']['ptable'] ?? '';
                    }

                    $fields = Helper::getFieldsFromDca($table);

                    $searchFields = $searchConsoleConfig['searchFields'] ?? $searchModule->getSearchFields();
                    $allowedFieldNames = ['name', 'title', 'alias', 'id'];
                    if (!$searchFields) {
                        $searchFields = [];
                        foreach ($allowedFieldNames as $allowedFieldName) {
                            if (array_key_exists($allowedFieldName, $fields)) {
                                $searchFields[] = $allowedFieldName;
                            }
                        }
                    }

                    $fieldName = $searchConsoleConfig['fieldName'] ?? $searchModule->getFieldName();

                    if (empty($fieldName)|| !\in_array($fieldName, $fields)) {
                        foreach ($allowedFieldNames as $allowedFieldName) {
                            if (array_key_exists($allowedFieldName, $fields)) {
                                $fieldName = $allowedFieldName;
                                break;
                            }
                        }
                    }

                    $enableGoTo = $searchConsoleConfig['enableGoTo'] ?? $searchModule->isEnableGoTo();
                    $enableNew = $searchConsoleConfig['enableNew'] ?? $searchModule->isEnableNew();

                    if (isset($GLOBALS['TL_DCA'][$table]['config']['closed']) && $GLOBALS['TL_DCA'][$table]['config']['closed'] === true) {
                        $enableNew = false;
                    }
                    if (isset($GLOBALS['TL_DCA'][$table]['config']['notCreatable']) && $GLOBALS['TL_DCA'][$table]['config']['notCreatable'] === true) {
                        $enableNew = false;
                    }
                    if (isset($GLOBALS['TL_DCA'][$table]['config']['notEditable']) && $GLOBALS['TL_DCA'][$table]['config']['notEditable'] === true) {
                        $enableGoTo = false;
                    }

                    $searchModule
                        ->setLabel($label)
                        ->setModule($module)
                        ->setTable($table)
                        ->setPTable($pTable)
                        ->setShortcut($searchConsoleConfig['shortcut'] ?? '')
                        ->setEnableGoTo($enableGoTo)
                        ->setEnableNew($enableNew)
                        ->setFields($fields)
                        ->setSearchFields($searchFields)
                        ->setFieldName($fieldName);
                }
            }
        }
    }

    public function getModule(string $moduleName): ?SearchModule
    {
        if (!empty($this->modules)) {
            foreach ($this->modules as $module) {
                if ($module->getModule() === $moduleName) {
                    return $module;
                }
            }
        }

        return null;
    }
}