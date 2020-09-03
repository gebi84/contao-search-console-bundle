<?php declare(strict_types=1);

namespace Gebi84\SearchConsoleBundle\EventListener;

use Contao\BackendUser;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Terminal42\ServiceAnnotationBundle\ServiceAnnotationInterface;

class AssetListener implements ServiceAnnotationInterface
{
    /**
     * @Hook("outputBackendTemplate")
     */
    public function addAssets(string $buffer, string $template): string
    {
        $user = BackendUser::getInstance();
        if (1 !== (int) $user->search_console_enable) {
            return $buffer;
        }

        $assets = [];

        //jquery already loaded?
        $jqueryString = '/js/jquery.';
        $hasJquery = strstr($buffer, $jqueryString);
        if (!$hasJquery) {
            if (is_array($GLOBALS['TL_JAVASCRIPT'])) {
                foreach ($GLOBALS['TL_JAVASCRIPT'] as $js) {
                    $hasJquery = strstr($buffer, $jqueryString);
                    if ($hasJquery) {
                        break;
                    }
                }
            }
        }
        if (!$hasJquery) {
            $assets[] = '<script type="text/javascript" src="assets/jquery/js/jquery.min.js"></script>';
            $assets[] = '<script>$.noConflict();</script>';
        }

        //jquery already loaded?
        $hasJqueryUi = strstr($buffer, 'jquery-ui.');
        if (!$hasJquery) {
            if (is_array($GLOBALS['TL_JAVASCRIPT'])) {
                foreach ($GLOBALS['TL_JAVASCRIPT'] as $js) {
                    $hasJquery = strstr($buffer, 'jquery-ui.');
                    if ($hasJquery) {
                        break;
                    }
                }
            }
        }
        if (!$hasJqueryUi) {
            $assets[] =  '  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">' . "\n";
            $assets[] = '<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>';
        }

        $assets[] = '<script type="text/javascript" src="bundles/gebi84searchconsole/js/search_console.js"></script>';

        if (!empty($assets)) {
            $buffer = str_replace('</head>', implode("\n", $assets) . '</head>', $buffer);
        }

        return $buffer;
    }
}