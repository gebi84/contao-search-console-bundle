<?php declare(strict_types=1);

namespace Gebi84\SearchConsoleBundle\Classes;

use Contao\BackendUser;
use Contao\Controller;
use Contao\System;

class Helper
{
    public static function getBackendModuleConfig(string $module): array
    {
        $arrModule = [];

        $backendUser = BackendUser::getInstance();

        foreach ($GLOBALS['BE_MOD'] as &$arrGroup) {
            if (isset($arrGroup[$module])) {
                $arrModule = &$arrGroup[$module];
                break;
            }
        }

        unset($arrGroup);

        $blnAccess = (isset($arrModule['disablePermissionChecks']) && $arrModule['disablePermissionChecks'] === true) || $backendUser->hasAccess($module, 'modules');

        // Check whether the current user has access to the current module
        if (!$blnAccess) {
            return [];
        }

        // The module does not exist
        if (empty($arrModule)) {
            return [];
        }

        return $arrModule;
    }

    public static function getFieldsFromDca(string $table): array
    {

        $return = [];

        Controller::loadDataContainer($table);
        Controller::loadLanguageFile($table);

        if ($GLOBALS['TL_DCA'][$table]) {
            if ($GLOBALS['TL_DCA'][$table]['fields']) {
                foreach ($GLOBALS['TL_DCA'][$table]['fields'] as $field => $data) {

                    $label = null;
                    if (isset($data['label']))
                    {
                        $label = \is_array($data['label']) ? $data['label'][0] : $data['label'];
                    }
                    else
                    {
                        $label = \is_array($GLOBALS['TL_LANG']['MSC'][$field]) ? $GLOBALS['TL_LANG']['MSC'][$field][0] : $GLOBALS['TL_LANG']['MSC'][$field];
                    }

                    if(!$label) {
                        $label = \is_array($GLOBALS['TL_LANG'][$table][$field]) ? $GLOBALS['TL_LANG'][$table][$field][0] : $GLOBALS['TL_LANG'][$table][$field];
                    }

                    if (!$label)
                    {
                        $label = '-';
                    }

                    $return[$field] = [
                        'label' => $label,
                        'value' => $field,
                        'type' => $data['inputType']
                    ];
                }
            }
        }

        return $return;
    }

    public static function getRequestToken(): string
    {
        $container = System::getContainer();

        return $container->get('contao.csrf.token_manager')
            ->getToken($container->getParameter('contao.csrf_token_name'))
            ->getValue();
    }

    public static function getSearchFragments(string $search): array
    {
        return explode(' ', $search);
    }
}
