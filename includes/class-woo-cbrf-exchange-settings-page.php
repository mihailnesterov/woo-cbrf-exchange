<?php
/**
 * Admin menu settings page plugin class
 *
 * @link       https://github.com/mihailnesterov
 * @since      1.0.0
 *
 * @package    Woo_Cbrf_Exchange
 * @subpackage Woo_Cbrf_Exchange/includes
 */

/**
 * Admin menu settings page plugin class.
 *
 * @package    Woo_Cbrf_Exchange
 * @subpackage Woo_Cbrf_Exchange/includes
 * @author     Mihail Nesterov <mhause@mail.ru>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Woo_Cbrf_Exchange_Settings_Page
{

    public function __construct() {
        add_action( 'admin_menu', array($this, 'add_settings_page'), 25 );
        add_action( 'admin_notices', array($this, 'get_errors_message') );
        add_action( 'admin_notices', array($this, 'get_saved_message') );
        add_action( 'admin_notices', array($this, 'get_updated_message') );
    }

    /**
     * Save currencies.
     *
     * @since    1.0.0
	 * @access   private
     * @return   void
     */
    private function save_currencies() {
        if( $this->saved() ) {
            update_option( '_woo_cbrf_exchange_currencies_selected', $this->get_saved_currencies() );
        }
    }

    /**
     * Get saved currencies.
     *
     * @since    1.0.0
	 * @access   private
     * @return   array
     */
    private function get_saved_currencies() {
        return array_keys(
            array_filter(
                $this->get_form_data(),
                function($key) {
                    return $key !== 'btn-save-currencies-settings';
                },
                ARRAY_FILTER_USE_KEY
            )
        );
    }

    /**
     * Update currencies.
     *
     * @since    1.0.0
	 * @access   private
     * @return   void
     */
    private function update_currencies() {
        if( $this->updated() ) {
            require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-cbrf-exchange-xml.php';
            $xml = new Woo_Cbrf_Exchange_Xml;
            $xml->set_transient_from_cbrf_daily();
        }
    }

    /**
     * Get success message.
     *
     * @since    1.0.0
	 * @access   private
     * @return   string|false
     */
    private function get_success_message() {
        return $this->saved() || $this->updated() ? 
            sprintf('<p class="success" title="%s">&#10004;</p>', __('Выполнено', 'woo-cbrf-exchange')) : 
            false;
    }

    /**
     * Check if settings saved.
     *
     * @since    1.0.0
	 * @access   private
     * @return   boolean
     */
    private function saved() {
        $posts = $this->get_form_data();
        return isset( $posts['btn-save-currencies-settings'] );
    }

    /**
     * Check if settings updated.
     *
     * @since    1.0.0
	 * @access   private
     * @return   boolean
     */
    private function updated() {
        $posts = $this->get_form_data();
        return isset( $posts['btn-update-currencies-settings'] );
    }

    /**
     * Get cleared $_POST.
     *
     * @since    1.0.0
	 * @access   private
     * @return   array
     */
    private function get_form_data() {
        return array_map( 'wp_kses_post', array_map( 'wp_unslash', $_POST ) );
    }

    /**
     * Get settings page html.
     *
     * @since    1.0.0
	 * @access   private
     * @return   void
     */
    private function get_page_html() { 
        printf(
            '<div class="wrap woo-cbrf-exchange setting-page">%s%s</div>',
            $this->get_header_html(),
            $this->get_settings_html()
        );
    }

    /**
     * Get page header html.
     *
     * @since    1.0.0
	 * @access   private
     * @return   string
     */
    private function get_header_html() { 
        return sprintf( '<header><h1>%s</h1></header>', get_admin_page_title() );
    }
    
    /**
     * Get page settings block html.
     *
     * @since    1.0.0
	 * @access   private
     * @return   string
     */
    private function get_settings_html() { 
        return sprintf( 
            '<div class="settings">%s%s%s</div>',
            sprintf(
                '<h2>%s</h2>',
                sprintf( 
                    '%s: %s',
                    __('Курсы валют', 'woo-cbrf-exchange'), 
                    date('d.m.Y')
                )
            ),
            sprintf(
                '<p class="help">%s</p>',
                sprintf( 
                    '%s %s',
                    '&#9745;',
                    __('Отмеченные валюты доступны в настройках товара', 'woo-cbrf-exchange'), 
                )
            ),
            sprintf(
                '<form class="settings-form" method="post" action="" enctype="multipart/form-data">%s%s</form>',
                $this->get_settings_list(),
                sprintf( 
                    '<div class="form-footer">%s%s%s</div>',
                    $this->get_update_button(),
                    $this->get_submit_button(),
                    $this->get_success_message()
                )
            )
        );
    }

    /**
     * Get settings list.
     *
     * @since    1.0.0
	 * @access   private
     * @return   string
     */
    private function get_settings_list() {
        
        $transient = get_transient( get_option('_woo_cbrf_exchange_transient_name') );
        $currencies_selected = get_option('_woo_cbrf_exchange_currencies_selected');
        
        return sprintf( 
            '<ul class="settings-list">%s</ul>',
            implode(
                '',
                array_map(
                    function($valute) use($currencies_selected) {
                        return sprintf('<li>%s</li>',
                            sprintf(
                                '<label for="%s" >%s%s</label>',
                                $valute['CharCode'],
                                sprintf( 
                                    '<input type="%1$s" id="%2$s" name="%2$s" value="%2$s" %3$s />',
                                    'checkbox',
                                    $valute['CharCode'],
                                    in_array( esc_attr( $valute['CharCode'] ), $currencies_selected) ? 'checked' : null
                                ),
                                sprintf( 
                                    '<b>%s</b> (%2$d %3$s = %4$g %5$s)',
                                    $valute['CharCode'],
                                    $valute['Nominal'],
                                    $valute['Name'],
                                    round(floatval(preg_replace("/[^-0-9\.]/", ".", $valute['Value'] )), 4),
                                    get_woocommerce_currency_symbol()
                                )
                            ),
                            
                        );
                    },
                    $transient['Valute']
                )
            ),
        );
    }

    /**
     * Get submit button.
     *
     * @since    1.0.0
	 * @access   private
     * @return   string
     */
    private function get_submit_button() { 
        return sprintf( 
            '<input type="%1$s" id="%2$s" name="%2$s" class="%3$s" value="%4$s" />',
            'submit',
            'btn-save-currencies-settings',
            'button button-primary',
            __('Сохранить изменения', 'woo-cbrf-exchange')
        );
    }

    /**
     * Get update button.
     *
     * @since    1.0.0
	 * @access   private
     * @return   string
     */
    private function get_update_button() { 
        return sprintf( 
            '<input type="%1$s" id="%2$s" name="%2$s" class="%3$s" value="%4$s" title="%5$s" />',
            'submit',
            'btn-update-currencies-settings',
            'button button-primary',
            '&#x21bb;',
            __('Обновить курсы валют', 'woo-cbrf-exchange')
        );
    }


    /**
     * Add menu settings page.
     *
     * @since    1.0.0
	 * @access   public
     * @return   void
     */
    public function add_settings_page() {
        add_menu_page(
            __('Курсы валют ЦБ РФ', 'woo-cbrf-exchange'),
            __('Курсы валют', 'woo-cbrf-exchange'),
            'manage_options',
            'woo_cbrf_exchange_settings_page',
            array($this, 'settings_page_callback'),
            'dashicons-embed-generic',
            
        );
    }

    /**
     * Settings page callback.
     *
     * @since    1.0.0
	 * @access   public
     * @return   void
     */
    public function settings_page_callback() { 
        $this->save_currencies();
        $this->update_currencies();
        $this->get_page_html();
    }

    /**
     * Get settings page errors message.
     *
     * @since    1.0.0
	 * @access   public
     * @return   void
     */
    public function get_errors_message() { 
        settings_errors();
    }

    /**
     * Get saved message.
     * 
     * @since    1.0.0
	 * @access   public
     * @return   void
    */
    public function get_saved_message() {
        
        if( ! $this->saved() ) return;

        printf(
            '<div id="%s" class="%s"><p>%s</p></div>', 
            'saved-message',
            'notice notice-success is-dismissible',
            sprintf( 
                '%s! %s: %d.',
                __('Изменения сохранены', 'woo-cbrf-exchange'),
                __('Количество доступных валют', 'woo-cbrf-exchange'),
                count($this->get_saved_currencies()) 
            )
        );
    }

    /**
     * Get updated message.
     * 
     * @since    1.0.0
	 * @access   public
     * @return   void
    */
    public function get_updated_message() {
        
        if( ! $this->updated() ) return;

        printf(
            '<div id="%s" class="%s"><p>%s</p></div>', 
            'updated-message',
            'notice notice-success is-dismissible',
            sprintf( 
                '%s: %s.',
                __('Курсы валют обновлены', 'woo-cbrf-exchange'),
                date('d.m.Y h:i:s')
            )
        );
    }

}