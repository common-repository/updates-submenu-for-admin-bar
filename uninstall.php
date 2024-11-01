<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package   WP_Simple_Site_Backup
 * @author    Firdaus Zahari <fake@fsylum.net>
 * @license   GPL-2.0+
 * @link      http://fsylum.net
 * @copyright 2014
 */

// If uninstall not called from WordPress, then exit
if ( ! defined('WP_UNINSTALL_PLUGIN') ) {
    exit;
}

// Remove the settings
delete_option('asu_opts');
