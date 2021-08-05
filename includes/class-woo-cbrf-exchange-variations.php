<?php

/**
 * Add the custom select of curriencies in WooCommerce admin variable product edit page
 *
 * @link       https://github.com/mihailnesterov
 * @since      1.0.0
 *
 * @package    Woo_Cbrf_Exchange
 * @subpackage Woo_Cbrf_Exchange/includes
 */

/**
 * Add the custom select of curriencies in WooCommerce admin variable product edit page.
 *
 * @package    Woo_Cbrf_Exchange
 * @subpackage Woo_Cbrf_Exchange/includes
 * @author     Mihail Nesterov <mhause@mail.ru>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// include Woo_Cbrf_Exchange_Currency class
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-cbrf-exchange-currency.php';

class Woo_Cbrf_Exchange_Variations extends Woo_Cbrf_Exchange_Currency
{

    /**
	 * The name of selected currency for variations.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $currency_name    The name of selected currency for variations.
	 */
    private $currency_name;

    /**
	 * Define the core functionality of the class.
	 *
	 * Load Woo_Cbrf_Exchange_Currency class methods for work with currencies.
     * Load $this->init() method where fires the hooks.
	 *
	 * @since    1.0.0
	 */
    public function __construct() {

        new Woo_Cbrf_Exchange_Currency();
        
        $this->init();

    }

    /**
     * Class instance initialization method.
     *
     * Add all hooks for variations admin page customization.
     * 
     * @access   private
     * @return void
     */
    private function init() {
        
        // add custom currency field to admin product page for variations
        add_action( 'woocommerce_variation_options_pricing', [$this, 'get_custom_product_currency_field_variations'], 10, 3 );

        // save custom currency for variations
        add_action( 'woocommerce_save_product_variation', [$this, 'save_custom_currency_product_variations'], 10, 2 );

        // add custom javascript to admin product page for variations
        add_action('admin_footer', [$this, 'custom_currency_admin_variations_js'], 20);
        
    }

    /**
     * Get custom currency field to admin product page for variations.
     *
     * @since    1.0.0
	 * @access   public
     * @param    int                  $loop                 The number of variation in the list of variations.
	 * @param    object               $variation_data       The variation data object.
	 * @param    object               $variation            The variation object.
     */
    public function get_custom_product_currency_field_variations( $loop, $variation_data, $variation ) {

        // get current WooCommerce currency symbol
        $symbol = get_woocommerce_currency_symbol();
        
        // get all WooCommerce currency symbols
        $symbols = get_woocommerce_currency_symbols();

        // get array of all WooCommerce currencies
        $wc_currencies = get_woocommerce_currencies();

        // get array of all CBRF currencies
        $currencies = $this->get_currencies();

        // get CBRF currency of this variation or empty
        $this->currency_name = get_post_meta( $variation->ID, 'woo_cbrf_exchange_custom_currency', true );

        // get result array of currencies (with symbols)
        $res_currencies = $this->get_res_currencies($wc_currencies, $currencies);

        // get result array of symbols
        $res_symbols = $this->get_res_symbols($symbols, $currencies);
        
        // get result array of options for select CBRF currencies
        $res_options = $this->get_options($res_symbols, $res_currencies);
        
        woocommerce_wp_select( array(
            'id'      => 'select_woo_cbrf_exchange_custom_currency[' . $loop . ']',
            'label' => __('Валюта', 'woo-cbrf-exchange'),
            'description' => __('Базовая цена и цена распродажи будет пересчитываться по курсу выбранной из списка валюты. Если валюта из списка не выбрана, будет применяться курс базовой валюты', 'woo-cbrf-exchange') . '(' . $symbol . ')',
            'desc_tip' => true,
            'style' => 'background-color:LightGoldenRodYellow',
            'wrapper_class' => 'form-field select_woo_cbrf_exchange_custom_currency[' . $loop . ']_field form-row form-row-first',
            'value' => $this->currency_name,
            'options' => array_merge( 
                array(
                    '0' => __('Не выбрана...', 'woo-cbrf-exchange'),
                ),
                $res_options
            ) )
        );

    }

    /**
     * Save custom currency for variations.
     *
     * @since    1.0.0
	 * @access   public
     * @param    int            $variation_id               variation's ID
     * @param    int            $i                          variation's number in the list
     */
    public function save_custom_currency_product_variations( $variation_id, $i ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        
        if ( isset( $_POST['select_woo_cbrf_exchange_custom_currency'][$i] ) ) {
            update_post_meta( 
                $variation_id, 
                'woo_cbrf_exchange_custom_currency',
                esc_attr( $_POST['select_woo_cbrf_exchange_custom_currency'][$i] )
            );
        }
    }
    
    /**
     * Add javascript in admin page for variations.
     *
     * @since    1.0.0
	 * @access   public
     */
    public function custom_currency_admin_variations_js() {

        if( get_current_screen()->id !== 'product' ) 
            return;

        $symbol = get_woocommerce_currency_symbol();

        $cbrf_currency = $this->get_cbrf_currency_by_name( $this->currency_name );

        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {

                // https://stackoverflow.com/questions/66626942/where-to-find-a-complete-list-of-javascript-jquery-events-that-fire-on-the-woo
                $(document).on('woocommerce_variations_loaded', function(event) {
                    
                    $('.woocommerce_variation.wc-metabox .variable_pricing').each(function() {
                        
                        const variable_price_input      = $(this).find('input[type="text"].short.wc_input_price')[0];
                        const variable_price_input_id   = $(variable_price_input).attr('id');
                        const variable_price_label      = $(variable_price_input)
                                                            .closest('p')
                                                            .find('label[for="' + 
                                                                variable_price_input_id + 
                                                                '"'
                                                            );
                        
                        const variable_sale_price_input     = $(this).find('input[type="text"].short.wc_input_price')[1];
                        const variable_sale_price_input_id  = $(variable_sale_price_input).attr('id');
                        const variable_sale_price_label     = $(variable_sale_price_input)
                                                                .closest('p')
                                                                .find('label[for="' + 
                                                                    variable_sale_price_input_id + 
                                                                    '"'
                                                                );
                        const variable = {
                            id:         variable_price_input_id,
                            number:     variable_price_input_id.split('_').pop(),
                            price:      $(variable_price_input).val(),
                            sale_price: $(variable_sale_price_input).val(),
                            label:      $(variable_price_label).text(),
                            sale_label: $(variable_sale_price_label).text()
                        }
                        
                        const selected_currency = $(
                            'select[name="select_woo_cbrf_exchange_custom_currency[' + 
                            variable.number + 
                            ']"] option:selected'
                        );
                        
                        if( $(selected_currency).val() !== '0' &&
                            $(selected_currency).val() !== '' ) {
                                
                                const selected_currency_symbol = $(selected_currency)
                                                                    .text()
                                                                    .split("(")
                                                                    .pop()
                                                                    .trim()
                                                                    .split(")")[0]
                                                                    .trim();

                                
                                $(variable_price_label).html( 
                                    variable.label
                                        .split("(")[0]
                                        .trim() + 
                                        ' (<span style="color:Crimson">' + selected_currency_symbol + '</span>)' 
                                    );
                                
                                const variable_sale_price_label_html = $(variable_sale_price_label)
                                                                        .html()
                                                                        .split(") ")
                                                                        .pop();
                                
                                $(variable_sale_price_label).html( 
                                    variable.sale_label
                                        .split("(")[0]
                                        .trim() + 
                                        ' (<span style="color:Crimson">' + 
                                        selected_currency_symbol + 
                                        '</span>) ' +
                                        variable_sale_price_label_html
                                    );

                                if( variable.price !== 0 && 
                                    variable.price !== '') {
                                    
                                    $(variable_price_input)
                                        .css({"backgroundColor":"LightGoldenRodYellow"})
                                        .closest('p')
                                        .append(`
                                            <span class="calc_woo_cbrf_exchange_custom_currency_price[${variable.number}]">
                                                ${ variable.price } ${selected_currency_symbol} 
                                                &equals; 
                                                ${ parseFloat(variable.price * <?= $cbrf_currency->rate ?>).toFixed(2) } <?= $symbol ?>
                                            </span>
                                        `);

                                }

                                if( variable.sale_price !== 0 && 
                                    variable.sale_price !== '') {
                                    
                                    $(variable_sale_price_input)
                                        .css({"backgroundColor":"LightGoldenRodYellow"})
                                        .closest('p')
                                        .append(`
                                            <span class="calc_woo_cbrf_exchange_custom_currency_sale_price[${variable.number}]">
                                                ${ variable.sale_price } ${selected_currency_symbol} 
                                                &equals; 
                                                ${ parseFloat(variable.sale_price * <?= $cbrf_currency->rate ?>).toFixed(2) } 
                                                <?= $symbol ?>
                                            </span>
                                        `);

                                }
                        }
                    });
                });
        
            });
        </script>
        <?php   
    }
    
}