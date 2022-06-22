<?php
/**
 * Transient sheduler plugin class
 *
 * @link       https://github.com/mihailnesterov
 * @since      1.0.0
 *
 * @package    Woo_Cbrf_Exchange
 * @subpackage Woo_Cbrf_Exchange/includes
 */

/**
 * Transient sheduler plugin class.
 *
 * @package    Woo_Cbrf_Exchange
 * @subpackage Woo_Cbrf_Exchange/includes
 * @author     Mihail Nesterov <mhause@mail.ru>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Woo_Cbrf_Exchange_Transient_Sheduler
{
    public function __construct() {
        add_action( 
            'wp', 
            array($this, 'woo_cbrf_exchange_transient_update_shedule_event'), 
            25 
        );
        add_action( 
            'woo_cbrf_exchange_twicedaily_transient_update', 
            array($this, 'run_transient_update_shedule_task')
        );
    }

    /**
     * Add the transient shedule hook.
     *
     * @since    1.0.0
	 * @access   public
     */
    public function woo_cbrf_exchange_transient_update_shedule_event() {
        if( ! wp_next_scheduled( 'woo_cbrf_exchange_twicedaily_transient_update' ) ) {
            wp_schedule_event( time(), 'twicedaily', 'woo_cbrf_exchange_twicedaily_transient_update');
        }
    }

    /**
     * Run the transient shedule task.
     *
     * @since    1.0.0
	 * @access   public
     */
    public function run_transient_update_shedule_task() {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-cbrf-exchange-xml.php';
        $xml = new Woo_Cbrf_Exchange_Xml;
        $xml->set_transient_from_cbrf_daily();
    }
    
}