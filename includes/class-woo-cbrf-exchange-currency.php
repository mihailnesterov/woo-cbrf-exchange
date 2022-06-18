<?php
/**
 * Currency plugin class
 *
 * @link       https://github.com/mihailnesterov
 * @since      1.0.0
 *
 * @package    Woo_Cbrf_Exchange
 * @subpackage Woo_Cbrf_Exchange/includes
 */

/**
 * Currency plugin class.
 *
 * @package    Woo_Cbrf_Exchange
 * @subpackage Woo_Cbrf_Exchange/includes
 * @author     Mihail Nesterov <mhause@mail.ru>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Woo_Cbrf_Exchange_Currency
{

    public function __construct() {}

    /**
     * Get currency names array.
     *
     * @since    1.0.0
	 * @access   protected
     * @return   array          array of available currency names used for exchange rate
     */
    protected function get_currencies() {
        return get_option('_woo_cbrf_exchange_currencies_selected');
    }

    /**
     * Get currencies result array.
     *
     * @since    1.0.0
	 * @access   protected
     * @param    array          $wc_currencies          array of all WC system currencies
     * @return   array          $res_currencies         array of currencies objects
     */
    protected function get_res_currencies( $wc_currencies = array() ) {
        
        $res_currencies = array();

        if( !empty( $wc_currencies ) ) {
            
            foreach( $wc_currencies as $key => $value ) {
                
                if( in_array( $key, $this->get_currencies() ) ) {
                    
                    array_push(
                        $res_currencies, 
                        (object) [
                            'name' => $key,
                            'title' => $value,
                            'symbol' => ''
                            ]
                    );

                }

            }

        }
        
        return $res_currencies;
    }

    /**
     * Get symbols result array.
     *
     * @since    1.0.0
	 * @access   protected
     * @param    array          $symbols                array of all WC system currencies
     * @return   array          $res_symbols            array of symbols objects
     */
    protected function get_res_symbols( $symbols = array() ) {
        
        $res_symbols = array();
        
        if( !empty( $symbols ) ) {

            foreach( $symbols as $key => $value ) {
        
                if( in_array( $key, $this->get_currencies() ) ) {
                    array_push(
                        $res_symbols, 
                        (object) [
                            'name' => $key,
                            'symbol' => $value
                            ]
                    );
                }

            }

        }
        
        return $res_symbols;
    }

    /**
     * Get options array.
     *
     * @since    1.0.0
	 * @access   protected
     * @param    array          $res_symbols                array of result symbols
     * @param    array          $res_currencies             array of result currencies
     * @return   array          $res_options                array of result options
     */
    protected function get_options( $res_symbols = array(), $res_currencies = array() ) {
        
        $_res_options = array(); 
        $_res_currencies = $res_currencies;
        
        for($i = 0; $i < count( $res_symbols ); $i++ ) {
            $_res_currencies[$i]->symbol = $res_symbols[$i]->symbol;
        }
        
        foreach( $_res_currencies as $key => $value ) {
            $_res_options[$value->name] = esc_html( "$value->name $value->title ($value->symbol)" );
        }

        return $_res_options;
    }
    
}