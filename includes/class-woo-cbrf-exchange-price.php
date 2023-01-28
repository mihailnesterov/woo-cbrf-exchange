<?php
/**
 * Price plugin class
 *
 * @link       https://github.com/mihailnesterov
 * @since      1.0.0
 *
 * @package    Woo_Cbrf_Exchange
 * @subpackage Woo_Cbrf_Exchange/includes
 */

/**
 * Price plugin class.
 *
 * @package    Woo_Cbrf_Exchange
 * @subpackage Woo_Cbrf_Exchange/includes
 * @author     Mihail Nesterov <mhause@mail.ru>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Woo_Cbrf_Exchange_Price
{

    /**
     * CBRF daily xml exchange rates instanse.
     * 
     * @since   1.0.0
     * @access  private
     */
    private $xml;

    public function __construct() {
        $this->xml = new Woo_Cbrf_Exchange_Xml;
        $this->init();
    }

    /**
     * Class instance initialization method.
     *
     * Add all hooks for product price conversion.
     * 
     * @since   1.0.0
     * @access  private
     * @return  void
     */
    private function init() {

        // add simple product's price
        add_filter( 'woocommerce_product_get_price', [$this, 'get_converted_price'], 888, 2 );
		add_filter( 'woocommerce_product_get_regular_price', [$this, 'get_converted_price'], 888, 2 );

        // add variable product's price
        add_filter('woocommerce_product_variation_get_price', [$this, 'get_converted_price'] , 888, 2 );
        add_filter('woocommerce_product_variation_get_regular_price', [$this, 'get_converted_price'], 888, 2 );

        // add variations of a variable product price
        add_filter('woocommerce_variation_prices_price', [$this, 'get_converted_variations_price'], 888, 3 );
        add_filter('woocommerce_variation_prices_regular_price', [$this, 'get_converted_variations_price'], 99, 3 );

        // add hash for variations price
        add_filter('woocommerce_get_variation_prices_hash', [$this, 'get_variation_prices_hash']);

        // add variable product's price html
        add_filter( 'woocommerce_variable_price_html', [$this, 'get_variable_price_html'], 888, 2 );

    }

    /**
     * Convert product's price.
     *
     * @since    1.0.0
	 * @access   private
     * @param    float          $price
     * @param    WC_Product     $product
     * @return   float          converted price if product has foreign currency metabox value
     */
    private function convert_price( $price, $product ) {
        
        if( $currency = get_post_meta( $product->get_id(), 'woo_cbrf_exchange_custom_currency', true ) ) {         

            $rate = $this->xml->get_exchange_rate_by_currency_name( $currency );

            if( $rate ) {
                return round( floatval($price) * ( floatval($rate->value) / intval($rate->nominal) ), 2 );
            }
        }

        return $price;
    }

    /**
     * Get product's available variations display prices.
     *
     * @since    1.0.0
	 * @access   private
     * @param    WC_Product     $product
     * @return   array
     */
    private function get_variations_display_prices( $product ) {
        return array_unique( 
            array_map(
                function($variation) {
                    return $variation['display_price'];
                },
                $product->get_available_variations()
            )
        );
    }

    /**
     * Get converted price for simple or variable product.
     *
     * @since    1.0.0
	 * @access   public
     * @param    float          $price
     * @param    WC_Product     $product
     * @return   float
     */
    public function get_converted_price( $price, $product ) {
        return $this->convert_price( $price, $product );
    }

    /**
     * Get converted price for variations of a variable product.
     *
     * @since    1.0.0
	 * @access   public
     * @param    float                      $price
     * @param    WC_Product_Variation       $variation
     * @param    WC_Product                 $product
     * @return   float
     */
    public function get_converted_variations_price( $price, $variation, $product ) {
        return $this->convert_price( $price, $product );
    }

    /**
     * Get hash for variations price.
     *
     * @since    1.0.0
	 * @access   public
     * @param    array  $hash
     * @return   array
     */
    public function get_variation_prices_hash( $hash ) {
        $hash[] = get_current_user_id();
        return $hash;
    }

    /**
     * Get converted variable product's price html.
     * 
     * @since    1.0.0
	 * @access   public
     * @param    string       $price_html 
     * @param    WC_Product   $product
     * @return   string
     */
    
	public function get_variable_price_html( $price_html, $product ) {

        $variations_display_prices = $this->get_variations_display_prices( $product );

        if( ! empty($variations_display_prices) && count($variations_display_prices) >= 2 ) {
            
            sort( $variations_display_prices );

            $min_price = current( $variations_display_prices );
            $max_price = end( $variations_display_prices );

            $min_price_html = wc_price( 
                wc_get_price_to_display( 
                    $product, 
                    array( 'price' => $this->convert_price( $min_price, $product ) )
                )
            );

            $max_price_html = wc_price( 
                wc_get_price_to_display( 
                    $product, 
                    array( 'price' => $this->convert_price( $max_price, $product ) ) 
                ) 
            );
            
            $price_html = sprintf( '%s - %s', $min_price_html, $max_price_html );

        }
        
        return $price_html;
    }
    
}

new Woo_Cbrf_Exchange_Price;