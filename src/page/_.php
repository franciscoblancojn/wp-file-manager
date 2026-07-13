<?php

if (!defined('ABSPATH')) exit;

require_once WPFM_DIR . 'src/page/add.php';

add_action('admin_menu', function () {
    add_submenu_page(
        WPFM_KEY,
        'Archivos',
        'Archivos',
        'manage_options',
        WPFM_KEY . '_files',
        'WPFM_PAGE_FILES_VIEW'
    );

    add_submenu_page(
        WPFM_KEY,
        'Configuraci\u00f3n',
        'Configuraci\u00f3n',
        'manage_options',
        WPFM_KEY . '_config',
        'WPFM_PAGE_CONFIG_VIEW'
    );
});

function WPFM_PAGE_FILES_VIEW()
{
    require_once WPFM_DIR . 'src/page/page.php';
}

function WPFM_PAGE_CONFIG_VIEW()
{
    require_once WPFM_DIR . 'src/page/page.php';
}
