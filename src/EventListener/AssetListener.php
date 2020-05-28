<?php declare(strict_types=1);

namespace Gebi84\SearchConsoleBundle\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Terminal42\ServiceAnnotationBundle\ServiceAnnotationInterface;

class AssetListener implements ServiceAnnotationInterface
{
    /**
     * @Hook("outputBackendTemplate")
     */
    public function addAssets(string $buffer, string $template): string
    {
        $scripts = [];

        //jquery already loaded?
        $hasJquery = strstr($buffer, 'jquery.');
        if (!$hasJquery) {
            if (is_array($GLOBALS['TL_JAVASCRIPT'])) {
                foreach ($GLOBALS['TL_JAVASCRIPT'] as $js) {
                    $hasJquery = strstr($buffer, 'jquery.');
                    if ($hasJquery) {
                        break;
                    }
                }
            }
        }
        if (!$hasJquery) {
            $scripts[] = '<script type="text/javascript" src="assets/jquery/js/jquery.min.js"></script>';
            $scripts[] = '<script>$.noConflict();</script>';
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
            $scripts[] = '<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>';
//            $scripts[] = '<script type="text/javascript" src="assets/jquery-ui/js/jquery-ui.min.js"></script>';
        }

        $scripts[] = '<script type="text/javascript" src="bundles/gebi84searchconsole/js/search_console.js"></script>';

        if (!empty($scripts)) {
            $buffer = str_replace('</head>', implode("\n", $scripts) . '</head>', $buffer);
        }

        return $buffer;
    }
}