<?php

/**
 * CSS
 */
if (TL_MODE === 'BE') {
    $GLOBALS['TL_CSS'][] = 'bundles/gebi84searchconsole/css/search_console.css';
}

/**
 * SEARCH_CONSOLE_CONFIG
 */

$GLOBALS['search_console']['modules']['article'] = [
    'shortcut' => 'a',
];

$GLOBALS['search_console']['modules']['calendar'] = [
    'shortcut' => 'c',
];

$GLOBALS['search_console']['modules']['faq'] = [
    'shortcut' => 'faq',
];

$GLOBALS['search_console']['modules']['form'] = [
    'shortcut' => 'f',
];

$GLOBALS['search_console']['modules']['comments'] = [
    'shortcut' => 'co',
];

$GLOBALS['search_console']['modules']['news'] = [
    'shortcut' => 'n',
];

$GLOBALS['search_console']['modules']['newsletter'] = [
    'shortcut' => 'nl',
];

$GLOBALS['search_console']['modules']['page'] = [
    'shortcut' => 'p',
];

$GLOBALS['search_console']['modules']['themes'] = [
    'shortcut' => 't',
];

$GLOBALS['search_console']['modules']['content'] = [
    'module' => 'article',
    'table' => 'tl_content',
    'label' => &$GLOBALS['TL_LANG']['CTE']['alias'][0],
    'enableGoTo' => false,
    'enableNew' => false,
    'searchFields' => ['headline', 'html']
];
