<?php
/**
 * Add the custom select of curriencies in WooCommerce admin simple product edit page
 *
 * @link       https://github.com/mihailnesterov
 * @since      1.0.0
 *
 * @package    Woo_Cbrf_Exchange
 * @subpackage Woo_Cbrf_Exchange/includes
 */

/**
 * Add the custom select of curriencies in WooCommerce admin simple product edit page.
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

class Woo_Cbrf_Exchange_Simple extends Woo_Cbrf_Exchange_Currency
{

    /**
	 * The name of selected currency for simple product.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $currency_name    The name of selected currency for simple product.
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
    public function __construct()
    {
        /*if( class_exists('woocommerce') ) {
            $this->symbol = get_woocommerce_currency_symbol();
            $this->symbols = get_woocommerce_currency_symbols();
            $this->wc_currencies = get_woocommerce_currencies();
            echo '<div style="float:right"><pre>';
            var_dump($this->wc_currencies);
            echo '</pre></div>';
        }*/

        new Woo_Cbrf_Exchange_Currency();
        
        $this->init();

    }

    /**
     * Class instance initialization method.
     *
     * Add all hooks for simple product admin page customization.
     * 
     * @access   private
     * @return void
     */
    private function init() {
        
        // add custom currency field to admin product page for simple products
        add_action( 'woocommerce_product_options_pricing', [$this, 'get_custom_product_currency_field'], 10, 3 );

        // save custom currency for simple product
        add_action('woocommerce_process_product_meta', [$this, 'save_custom_product_currency'], 10);

        // add javascript for simple product admin page
        add_action('admin_footer', [$this, 'custom_currency_admin_simple_js'], 10);
        
    }

    /**
     * Get custom currency field to admin product page for simple products.
     *
     * @since    1.0.0
	 * @access   public
     */
    public function get_custom_product_currency_field() {

        // get current WooCommerce currency symbol
        $symbol = get_woocommerce_currency_symbol();
        
        // get all WooCommerce currency symbols
        $symbols = get_woocommerce_currency_symbols();

        // get array of all WooCommerce currencies
        $wc_currencies = get_woocommerce_currencies();

        // get array of all CBRF currencies
        $currencies = $this->get_currencies();

        // get CBRF currency of this variation or empty
        $this->currency_name = get_post_meta( get_the_ID(), 'woo_cbrf_exchange_custom_currency', true );

        // get result array of currencies (with symbols)
        $res_currencies = $this->get_res_currencies($wc_currencies, $currencies);

        // get result array of symbols
        $res_symbols = $this->get_res_symbols($symbols, $currencies);
        
        // get result array of options for select CBRF currencies
        $res_options = $this->get_options($res_symbols, $res_currencies);

        woocommerce_wp_select( array(
            'id'      => 'select_woo_cbrf_exchange_custom_currency',
            'label' => __('Валюта', 'woo-cbrf-exchange'),
            'description' => __('Базовая цена и цена распродажи будет пересчитываться по курсу выбранной из списка валюты. Если валюта из списка не выбрана, будет применяться курс базовой валюты', 'woo-cbrf-exchange') . '(' . $symbol . ')',
            'desc_tip' => true,
            'style' => 'background-color:LightGoldenRodYellow',
            'wrapper_class' => 'form-field select_woo_cbrf_exchange_custom_currency_field',
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
     * Save custom currency for simple product.
     *
     * @since    1.0.0
	 * @access   public
     * @param    int            $post_id                simple product ID
     */
    public function save_custom_product_currency( $post_id ){
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

        if( isset($_POST['select_woo_cbrf_exchange_custom_currency']) ) {
            update_post_meta(
                $post_id, 
                'woo_cbrf_exchange_custom_currency', 
                esc_attr($_POST['select_woo_cbrf_exchange_custom_currency'])
            );
        }
    }
    
    /**
     * Add javascript in admin page for simple product.
     *
     * @since    1.0.0
	 * @access   public
     */
    public function custom_currency_admin_simple_js() {

        if( get_current_screen()->id !== 'product' ) 
            return;
    
        $symbol = get_woocommerce_currency_symbol();

        $cbrf_currency = $this->get_cbrf_currency_by_name( $this->currency_name );
        
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {

                const simple_price_input      = $(this).find('input[type="text"].short.wc_input_price')[0];
                const simple_price_input_id   = $(simple_price_input).attr('id');
                const simple_price_label      = $(simple_price_input)
                                                    .closest('p')
                                                    .find('label[for="' + 
                                                        simple_price_input_id + 
                                                        '"'
                                                    );
                
                const simple_sale_price_input     = $(this).find('input[type="text"].short.wc_input_price')[1];
                const simple_sale_price_input_id  = $(simple_sale_price_input).attr('id');
                const simple_sale_price_label     = $(simple_sale_price_input)
                                                        .closest('p')
                                                        .find('label[for="' + 
                                                            simple_sale_price_input_id + 
                                                            '"'
                                                        );
                
                const simple = {
                    id:         simple_price_input_id,
                    price:      $(simple_price_input).val(),
                    sale_price: $(simple_sale_price_input).val(),
                    label:      $(simple_price_label).text(),
                    sale_label: $(simple_sale_price_label).text()
                }

                const selected_currency = $(
                    'select[name="select_woo_cbrf_exchange_custom_currency"] option:selected'
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
                        
                        $(simple_price_label).html( 
                            simple.label
                                .split("(")[0]
                                .trim() + 
                                ' (<span style="color:Crimson">' + selected_currency_symbol + '</span>)' 
                            );

                        const simple_sale_price_label_html = $(simple_sale_price_label)
                                                                .html()
                                                                .split(") ")
                                                                .pop();
                            
                        $(simple_sale_price_label).html( 
                            simple.sale_label
                                .split("(")[0]
                                .trim() + 
                                ' (<span style="color:Crimson">' + 
                                selected_currency_symbol + 
                                '</span>) '
                            );

                            if( simple.price !== 0 && 
                                simple.price !== '') {
                                    
                                    $(simple_price_input)
                                        .css({"backgroundColor":"LightGoldenRodYellow"})
                                        .closest('p')
                                        .append(`
                                            <span class="calc_woo_cbrf_exchange_custom_currency_price">
                                                &nbsp;
                                                ${ simple.price } ${selected_currency_symbol} 
                                                &equals; 
                                                ${ parseFloat(simple.price * <?= $cbrf_currency->rate ?>).toFixed(2) } 
                                                <?= $symbol ?>
                                            </span>
                                        `);

                                }

                            if( simple.sale_price !== 0 && 
                                simple.sale_price !== '') {
                                
                                $(simple_sale_price_input).css({
                                    "backgroundColor":"LightGoldenRodYellow"
                                    });
                                 
                                $(`<span class="calc_woo_cbrf_exchange_custom_currency_sale_price">
                                    &nbsp;
                                    ${ simple.sale_price } ${selected_currency_symbol} 
                                    &equals; 
                                    ${ parseFloat(simple.sale_price * <?= $cbrf_currency->rate ?>).toFixed(2) } <?= $symbol ?>
                                </span>`)
                                .insertBefore( 
                                    $(simple_sale_price_input)
                                    .closest('p')
                                    .find('span.description')  
                                );

                            }
                    }                
            });
        </script>
        <?php
    }

    
}