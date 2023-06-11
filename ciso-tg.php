<?php

/**
 * Plugin Name:       CISO AG Threat Globe
 * Description:       Inserts CISO threat globe GLB and associate popup text via shortcode
 * Version:           1.0.0
 * Requires at least: 5.0
 * Requires PHP:      7.4
 * Author:            Engage24
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ciso-tg
 */

defined('ABSPATH') || exit();

add_action('plugins_loaded', function () {

    // constants
    define('CISO_TG_PATH', plugin_dir_path(__FILE__));
    define('CISO_TG_URI', plugin_dir_url(__FILE__));

    // ====
    // ACF
    // ====
    define('CISO_ACF_URL', CISO_TG_URI . 'acf/');
    define('CISO_ACF_PATH', CISO_TG_PATH . 'acf/');

    // main
    include_once CISO_ACF_PATH . 'acf.php';

    // filter uri
    add_filter('acf/settings/url', function () {
        return CISO_ACF_URL;
    });

    // hide admin
    add_filter('acf/settings/show_admin', '__return_false');

    // When including the PRO plugin, hide the ACF Updates menu
    add_filter('acf/settings/show_updates', '__return_false', 100);

    // admin page
    include_once CISO_TG_PATH . 'inc/admin/ciso_admin.php';

    // shortcode
    include_once CISO_TG_PATH . 'inc/shortcode/fnc_tg_shortcode.php';

});
