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
	 * The plugin currency code.
	 *
	 * @since    1.0.0
	 */
    const CURRENCY = 'RUB';

    /**
	 * The URL address of CBRF daily xml file.
	 *
	 * @since    1.0.0
	 */
    const XML_DAILY_URL = 'http://cbr.ru/scripts/XML_daily.asp';

    /**
	 * Define the core functionality of the class.
	 *
     * Load $this->init() method where fires the hooks.
	 *
	 * @since    1.0.0
	 */
    public function __construct() {}

    /**
	 * Get CBRF exchange rate by currency name
	 * 
	 * @since    1.0.0
	 * @access   public
     * @param    string             $currency_name                The currency name as string
	 * @return   object             The exchange rate object of currency given in param or nothing
	 */
	public function get_exchange_rate_by_currency_name( $currency_name = '' ) {
        
        // get xml from CBRF site
        $xml = $this->get_xml_from_cbrf_daily();
        // get currencies as array of objects
		$currencies = $this->get_currencies_from_xml( $xml );
        // firstly exchange rate sould be as array of one object
        $exchange_rate = array();

        // get result exchange rate as array and save to $exchange_rate
        if( $currency_name !== '' ) {
            
            $exchange_rate = array_values(
                array_filter(
                    $currencies,
                    function( $item ) 
                        use( $currency_name ) {
                        return $item->charcode === $currency_name;
                    }
                )
            );

        }

        // return as object
        if( count( $exchange_rate ) > 0 )
            return $exchange_rate[0]; 
        
	}

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
        if( isset( $xml ) && isset( $xml[$attribute] ) )
            return (string) $xml[$attribute];
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
        $json = $this->get_json_from_xml( $xml );

		foreach( $json['Valute'] as $currency ) {
			
			array_push(
                $currencies, 
                (object) array(
					'owncode'	=> self::CURRENCY, // The plugin currency code
					'numcode'	=> $currency['NumCode'],	// The numeric foreign currency code
					'charcode'	=> $currency['CharCode'],	// The symbol foreign currency code
					'nominal'	=> intval( $currency['Nominal'] ),	// The foreign currency nominal
					'name'		=> $currency['Name'],		// The foreign currency name
					'value' 	=> $this->round_currency( 
                                        $this->currency_to_float( 
                                            $currency['Value'] ), 
                                            4 
                                        ),	// The foreign currency exchange rate (converted to float and rounded)
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
     * @return   array            The array of CBRF exchange rates
     */
    private function get_xml_from_cbrf_daily() {
        return simplexml_load_file( self::XML_DAILY_URL );
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

}