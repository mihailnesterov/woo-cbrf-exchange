<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       https://github.com/mihailnesterov
 * @since      1.0.0
 *
 * @package    Woo_Cbrf_Exchange
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Remove plugin's transient
delete_transient(get_option('_woo_cbrf_exchange_transient_name'));

// Remove plugin's transient update shedule hook
wp_clear_scheduled_hook( 'woo_cbrf_exchange_twicedaily_transient_update' );

// Remove all plugin's wp options.
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-cbrf-exchange-wp-options.php';
Woo_Cbrf_Exchange_WP_Options::remove();