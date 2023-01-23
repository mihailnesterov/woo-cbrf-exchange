<?php

/**
 * Fired during plugin activation
 *
 * @link       https://github.com/mihailnesterov
 * @since      1.0.0
 *
 * @package    Woo_Cbrf_Exchange
 * @subpackage Woo_Cbrf_Exchange/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Woo_Cbrf_Exchange
 * @subpackage Woo_Cbrf_Exchange/includes
 * @author     Mihail Nesterov <mhause@mail.ru>
 */
class Woo_Cbrf_Exchange_Activator {

	/**
	 * Run the plugin's activation tasks.
	 *
	 * Run tasks that the plugin activation fires.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		Woo_Cbrf_Exchange_WP_Options::setup();
		$xml = new Woo_Cbrf_Exchange_Xml;
		$xml->set_transient_from_cbrf_daily();
	}

}
