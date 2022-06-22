<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://github.com/mihailnesterov
 * @since      1.0.0
 *
 * @package    Woo_Cbrf_Exchange
 * @subpackage Woo_Cbrf_Exchange/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Woo_Cbrf_Exchange
 * @subpackage Woo_Cbrf_Exchange/includes
 * @author     Mihail Nesterov <mhause@mail.ru>
 */
class Woo_Cbrf_Exchange_Deactivator {

	/**
	 * Run the plugin's deactivation tasks.
	 *
	 * Run tasks that the plugin deactivation fires.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		delete_transient(get_option('_woo_cbrf_exchange_transient_name'));
		wp_clear_scheduled_hook( 'woo_cbrf_exchange_twicedaily_transient_update' );
	}

}
