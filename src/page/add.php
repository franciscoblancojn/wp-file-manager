<?php

if (!defined('ABSPATH')) exit;

add_action('admin_menu', function () {
    add_menu_page(
        'WP File Manager',
        'File Manager',
        'manage_options',
        WPFM_KEY,
        'WPFM_REDIRECT_FIRST_SUBMENU',
        'dashicons-media-default'
    );
});

function WPFM_REDIRECT_FIRST_SUBMENU()
{
    wp_redirect(admin_url('admin.php?page=' . WPFM_KEY . '_files'));
    exit;
}
