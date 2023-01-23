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

    public function __construct() {
        $this->init();
    }

    /**
     * Class instance initialization method.
     *
     * Add all hooks for simple product admin page customization.
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

    }

    /**
     * Convert product's price.
     *
     * @since    1.0.0
	 * @access   private
     * @return   float|false        converted price value or false if product doesn't have foreign currency metabox value
     */
    private function convert_price( $price, $product ) {
        
        if( $currency = get_post_meta( $product->get_id(), 'woo_cbrf_exchange_custom_currency', true ) ) {
            
            $xml = new Woo_Cbrf_Exchange_Xml;
            $rate = $xml->get_exchange_rate_by_currency_name( $currency );
            
            if( $rate ) {
                return round( $price * ($rate->value / $rate->nominal), 2 );
            }
        }

        return false;
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

        $converted_price = $this->convert_price( $price, $product );

        return false === $converted_price ? $price : $converted_price;
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
        return $this->get_converted_price( $price, $product );
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
    
}

new Woo_Cbrf_Exchange_Price;