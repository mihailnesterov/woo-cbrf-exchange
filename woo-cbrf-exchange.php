<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/mihailnesterov
 * @since             1.0.0
 * @package           Woo_Cbrf_Exchange
 *
 * @wordpress-plugin
 * Plugin Name:       CBRF Exchange for WooCommerce
 * Plugin URI:        woo-cbrf-exchange.ru
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Mihail Nesterov
 * Author URI:        https://github.com/mihailnesterov
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woo-cbrf-exchange
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WOO_CBRF_EXCHANGE_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woo-cbrf-exchange-activator.php
 */
function activate_woo_cbrf_exchange() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-cbrf-exchange-activator.php';
	Woo_Cbrf_Exchange_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woo-cbrf-exchange-deactivator.php
 */
function deactivate_woo_cbrf_exchange() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-cbrf-exchange-deactivator.php';
	Woo_Cbrf_Exchange_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_woo_cbrf_exchange' );
register_deactivation_hook( __FILE__, 'deactivate_woo_cbrf_exchange' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woo-cbrf-exchange.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_woo_cbrf_exchange() {

	$plugin = new Woo_Cbrf_Exchange();
	$plugin->run();

}
run_woo_cbrf_exchange();
