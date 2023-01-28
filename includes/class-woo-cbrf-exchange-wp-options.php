<?php
/**
 * WP options plugin class
 *
 * @link       https://github.com/mihailnesterov
 * @since      1.0.0
 *
 * @package    Woo_Cbrf_Exchange
 * @subpackage Woo_Cbrf_Exchange/includes
 */

/**
 * WP options plugin class.
 *
 * @package    Woo_Cbrf_Exchange
 * @subpackage Woo_Cbrf_Exchange/includes
 * @author     Mihail Nesterov <mhause@mail.ru>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Woo_Cbrf_Exchange_WP_Options
{

    /**
	 * The WP options array.
	 *
	 * @since    1.0.0
	 */
    const OPTIONS = array(
        '_woo_cbrf_exchange_currencies_selected' => array('EUR', 'USD'),
        '_woo_cbrf_exchange_xml_daily_url' => 'http://cbr.ru/scripts/XML_daily.asp',
		'_woo_cbrf_exchange_transient_name' => '_woo_cbrf_exchange_transient',
        '_woo_cbrf_exchange_transient_hours' => 12
    );

    public function __construct() {}

    /**
     * Setup WP options.
     *
     * @since    1.0.0
	 * @access   public
     */
    public static function setup() {
        foreach(self::OPTIONS as $key => $value) {
            add_option( $key, $value );
        }
    }

    /**
     * Remove WP options.
     *
     * @since    1.0.0
	 * @access   public
     */
    public static function remove() {
        foreach(self::OPTIONS as $key => $value) {
            delete_option( $key );
        }
    }
    
}