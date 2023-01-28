<?php
/**
 * Class serves for getting CBRF daily xml with exchange rates.
 *
 * @link       https://github.com/mihailnesterov
 * @since      1.0.0
 *
 * @package    Woo_Cbrf_Exchange
 * @subpackage Woo_Cbrf_Exchange/includes
 */

/**
 * Get CBRF daily xml with exchange rates.
 * Convert xml to json.
 * Prepare exchange rates data for further manipulations.
 *
 * @package    Woo_Cbrf_Exchange
 * @subpackage Woo_Cbrf_Exchange/includes
 * @author     Mihail Nesterov <mhause@mail.ru>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


class Woo_Cbrf_Exchange_Xml
{

    /**
	 * Define the core functionality of the class.
	 *
     * Load $this->init() method where fires the hooks.
	 *
	 * @since    1.0.0
	 */
    public function __construct() {}

    /**
     * Get attribute value from xml.
     *
     * @since    1.0.0
	 * @access   private
     * @param    array             $xml                      The xml data as array
     * @param    string            $attribute                The attribute key as string
     * @return   string            The attribute value as string or nothing
     */
    private function get_currency_attribute_value_from_xml( $xml, $attribute ) {
        if( isset( $xml ) && isset( $xml[$attribute] ) ) {
            return (string) $xml[$attribute];
        }
    }

    /**
     * Get currencies array from xml.
     *
     * @since    1.0.0
	 * @access   private
     * @param    array            $xml                The xml data as array
     * @return   array            The currencies array of CBRF exchange rates
     */
    private function get_currencies_from_xml( $xml ) {
        
        $currencies = array();

		foreach( $xml['Valute'] as $currency ) {
			
			array_push(
                $currencies, 
                (object) array(
					'owncode'	=> get_option( 'woocommerce_currency' ), // The WooCommerce currency code
					'numcode'	=> $currency['NumCode'],	// The numeric foreign currency code
					'charcode'	=> $currency['CharCode'],	// The symbol foreign currency code
					'nominal'	=> intval( $currency['Nominal'] ),	// The foreign currency nominal
					'name'		=> $currency['Name'],		// The foreign currency name
					'value' 	=> $this->round_currency( $this->currency_to_float( $currency['Value'] ), 4 ),	// The foreign currency exchange rate (converted to float and rounded)
				)
			);
			
		}

		return $currencies;
    }

    /**
     * Get xml file from CBRF daily.
     *
     * @since    1.0.0
	 * @access   private
     * @return   array|false            The cached array of CBRF exchange rates or false if transient is empty
     */
    private function get_xml_from_cbrf_daily() {
        $cache = get_transient( get_option('_woo_cbrf_exchange_transient_name') );
        
        if( false === $cache ) {
            $this->set_transient_from_cbrf_daily();
            return get_transient( get_option('_woo_cbrf_exchange_transient_name') );
        }

        return $cache;
    }
    

    /**
     * Get json from xml.
     *
     * @since    1.0.0
	 * @access   private
     * @param    array            $xml                The xml data as array
     * @return   array            The xml data as json
     */
    private function get_json_from_xml( $xml ) {
        return json_decode( json_encode( $xml ), TRUE);
    }

    /**
	 * Convert currency string to float
	 * 
     * Gets currency as string, changes comma on dot, converts string to float.
     * 
 	 * @since    1.0.0
	 * @access   private
     * @param    string            $currency                The currency value as string
	 * @return   float             The currency value as float
	 */
	private function currency_to_float( $currency ) {
		return floatval( preg_replace( "/[^-0-9\.]/", ".", $currency ) );
	}
	
	/**
	 * Rounds currency float to precision ( default = 2 )
	 * 
	 * @since    1.0.0
	 * @access   private
     * @param    float             $currency                The currency value as float
	 * @return   float             The currency value rounded to certain precision
	 */
	private function round_currency( $currency, $precision = 2 ) {
		return round( $currency, $precision );
	}

    /**
	 * Get CBRF exchange rate by currency name
	 * 
	 * @since    1.0.0
	 * @access   public
     * @param    string             $currency_name                The foreign currency name as string
	 * @return   object|void        The exchange rate object of foreign currency given in param or nothing
	 */
	public function get_exchange_rate_by_currency_name( $currency_name = '' ) {

        if( $currency_name === '' ) return;

        $xml = $this->get_xml_from_cbrf_daily();

        if( false !== $xml ) {

            $currencies = $this->get_currencies_from_xml( $xml );

            $exchange_rate = array_values(
                array_filter(
                    $currencies,
                    function( $item ) use( $currency_name ) {
                        return $item->charcode === $currency_name;
                    }
                )
            );

            if( ! empty($exchange_rate) ) {
                return $exchange_rate[0]; 
            }

        }
        
	}


    /**
     * Set transient from xml file CBRF daily.
     *
     * @since    1.0.0
	 * @access   public
     * @return   void
     */
    public function set_transient_from_cbrf_daily() {
        set_transient( 
            get_option( '_woo_cbrf_exchange_transient_name' ), 
            $this->get_json_from_xml(
                simplexml_load_file( 
                    get_option( '_woo_cbrf_exchange_xml_daily_url' ) 
                )
            ), 
            intval( get_option( '_woo_cbrf_exchange_transient_hours' ) * HOUR_IN_SECONDS ) 
        );
    }

}