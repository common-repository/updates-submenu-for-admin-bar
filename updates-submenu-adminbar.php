<?php
/**
 * Updates Submenu for Admin Bar
 *
 * Add additional submenu that links to all available updates to your
 * WordPress installation.
 *
 * Plugin Name:       Updates Submenu for Admin Bar
 * Plugin URI:        https://wordpress.org/plugins/updates-submenu-for-admin-bar
 * Description:       Show all available updates in submenu in the Admin Bar
 * Version:           1.0.0
 * Author:            Firdaus Zahari
 * Author URI:        http://fsylum.net
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * GitHub Plugin URI: https://github.com/fsylum/updates-submenu-for-admin-bar
 */

// If this file is called directly, abort.
if ( ! defined('WPINC') ) {
    die;
}

if ( is_admin() && ( ! defined('DOING_AJAX') || ! DOING_AJAX ) ) {

    foreach ( glob( plugin_dir_path( __FILE__ ) . 'class/*.php' ) as $file ) {
        require_once $file;
    }

    register_activation_hook( __FILE__, array( 'ASU_Admin', 'activate' ) );
    register_deactivation_hook( __FILE__, array( 'ASU_Admin', 'deactivate' ) );

    add_action( 'plugins_loaded', array( 'ASU_Admin', 'get_instance' ) );

}
