<?php

/**
 * Palettes
 */
foreach ($GLOBALS['TL_DCA']['tl_user']['palettes'] as $palette => $v) {
    if ('__selector__' === $palette) {
        continue;
    }

    $GLOBALS['TL_DCA']['tl_user']['palettes'][$palette] .= ';{legend_search_console},search_console_enable;';
}

/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_user']['fields']['search_console_enable'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_user']['search_console_enable'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => array('tl_class' => 'cbx'),
    'sql'       => "char(1) NOT NULL default ''",
];