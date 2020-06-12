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
    'module' => 'article',
    'shortcut' => 'a',
    'enableGoTo' => true,
    'enableNew' => true,
];

$GLOBALS['search_console']['modules']['calendar'] = [
    'module' => 'calendar',
    'shortcut' => 'c',
    'enableGoTo' => true,
    'enableNew' => true,
];

$GLOBALS['search_console']['modules']['faq'] = [
    'module' => 'faq',
    'shortcut' => 'faq',
    'enableGoTo' => true,
    'enableNew' => true,
];

$GLOBALS['search_console']['modules']['form'] = [
    'module' => 'form',
    'shortcut' => 'f',
    'enableGoTo' => true,
    'enableNew' => true,
];

$GLOBALS['search_console']['modules']['comments'] = [
    'module' => 'comments',
    'shortcut' => 'co',
    'enableGoTo' => true,
    'enableNew' => true,
];

$GLOBALS['search_console']['modules']['news'] = [
    'module' => 'news',
    'shortcut' => 'n',
    'enableGoTo' => true,
    'enableNew' => true,
];

$GLOBALS['search_console']['modules']['newsletter'] = [
    'module' => 'newsletter',
    'shortcut' => 'nl',
    'enableGoTo' => true,
    'enableNew' => true,
];

$GLOBALS['search_console']['modules']['page'] = [
    'module' => 'page',
    'shortcut' => 'p',
    'enableGoTo' => true,
    'enableNew' => true,
];

$GLOBALS['search_console']['modules']['themes'] = [
    'module' => 'themes',
    'shortcut' => 't',
    'enableGoTo' => true,
    'enableNew' => true,
];

//$GLOBALS['search_console']['modules']['content'] = [
//    'module' => 'article',
//    'table' => 'tl_content',
//    'label' => &$GLOBALS['TL_LANG']['CTE']['alias'][0],
//    'enableGoTo' => false,
//    'enableNew' => false,
//];
