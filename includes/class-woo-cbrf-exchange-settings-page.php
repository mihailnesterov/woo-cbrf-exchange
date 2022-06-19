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
    }

    /**
     * Add menu settings page.
     *
     * @since    1.0.0
	 * @access   public
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
     */
    public function settings_page_callback() { 
        $this->save_currencies();
        ?>
        <div class="wrap woo-cbrf-exchange setting-page">
            <header>
                <h1> <?= get_admin_page_title() ?> </h1>
            </header>
            <?php settings_errors(); ?>
            <div class="settings">
                <h2>
                    <?= wp_sprintf( 
                            '%s на: %l',
                            __('Список конвертируемых валют ЦБ РФ', 'woo-cbrf-exchange'), 
                            date('d.m.Y')
                        ) 
                    ?>
                </h2>
                <p class="help">
                    <?= wp_sprintf( 
                            '%s',
                            __('Отмеченные валюты будут выведены в настройках товаров', 'woo-cbrf-exchange'), 
                        )
                    ?>
                <p>
                
                <form class="settings-form" method="post" action="" enctype="multipart/form-data">
                    <?php $this->get_settings_list(); ?>
                    <div class="form-footer">
                        <?php $this->get_submit_button(); ?>
                        <?php $this->get_success_message(); ?>
                    </div>
                </form>
            </div>
        </div>
    <?php 
    }

    /**
     * Save currencies.
     *
     * @since    1.0.0
	 * @access   private
     */
    private function save_currencies() {
        if(isset($_POST['btn-update-currencies-settings'])) {
            update_option( 
                '_woo_cbrf_exchange_currencies_selected', 
                array_keys(
                    array_filter(
                        $_POST,
                        function($key) {
                            return $key !== 'btn-update-currencies-settings';
                        },
                        ARRAY_FILTER_USE_KEY
                    )
                ) 
            );
        }
    }

    /**
     * Get success message.
     *
     * @since    1.0.0
	 * @access   private
     */
    private function get_success_message() {
       if(isset($_POST['btn-update-currencies-settings'])):?>
            <p class="success" title="<?= __('Сохранено', 'woo-cbrf-exchange') ?>">&#10004;</p>
        <?php endif;
    }

    /**
     * Get settings list.
     *
     * @since    1.0.0
	 * @access   private
     */
    private function get_settings_list() {
        $transient = get_transient( get_option('_woo_cbrf_exchange_transient_name') );
        $currencies_selected = get_option('_woo_cbrf_exchange_currencies_selected');
    ?>
        <ul class="settings-list">
        <?php foreach($transient['Valute'] as $key => $value): ?>
            <li>
                <label for="<?= esc_attr( $value['CharCode'] ) ?>" >
                <?php 
                    
                    printf( 
                            '<input type="%1$s" id="%2$s" name="%2$s" value="%2$s" %3$s />',
                            'checkbox',
                            $value['CharCode'],
                            in_array( esc_attr( $value['CharCode'] ), $currencies_selected) ? 'checked' : null
                    );

                    printf( 
                            '<b>%s</b> (%2$s %3$s = %4$s %5$s)',
                            $value['CharCode'],
                            $value['Nominal'],
                            $value['Name'],
                            round(floatval(preg_replace("/[^-0-9\.]/", ".", $value['Value'] )), 4),
                            get_woocommerce_currency_symbol()
                    );
                ?>
                </label>
            </li>
        <?php endforeach; ?>
        </ul>
    <?php
    }

    /**
     * Get submit button.
     *
     * @since    1.0.0
	 * @access   private
     */
    private function get_submit_button() { ?>
        <button 
            type="submit" 
            id="btn-update-currencies-settings" 
            name="btn-update-currencies-settings" 
            class="button button-primary"
        >
        <?= __('Сохранить настройки', 'woo-cbrf-exchange') ?>
        </button>
    <?php
    }
    
}