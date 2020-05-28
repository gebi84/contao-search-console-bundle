<?php

/**
 * CSS
 */
if (TL_MODE === 'BE') {
    $GLOBALS['TL_CSS'][]= 'bundles/gebi84searchconsole/css/search_console.css';
}

/**
 * SEARCH_CONSOLE_CONFIG
 */
$GLOBALS['search_console']['modules']['page'] = [
  'module' => 'page'
];

$GLOBALS['search_console']['modules']['article'] = [
    'module' => 'article'
];

$GLOBALS['search_console']['modules']['content'] = [
    'module' => 'article',
    'table' => 'tl_content',
    'label' => &$GLOBALS['TL_LANG']['CTE']['alias'][0]
];
