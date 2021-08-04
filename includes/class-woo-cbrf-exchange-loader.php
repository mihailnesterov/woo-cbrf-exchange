<?php

/**
 * Register all actions and filters for the plugin
 *
 * @link       https://github.com/mihailnesterov
 * @since      1.0.0
 *
 * @package    Woo_Cbrf_Exchange
 * @subpackage Woo_Cbrf_Exchange/includes
 */

/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 *
 * @package    Woo_Cbrf_Exchange
 * @subpackage Woo_Cbrf_Exchange/includes
 * @author     Mihail Nesterov <mhause@mail.ru>
 */
class Woo_Cbrf_Exchange_Loader {

	/**
	 * The array of actions registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $actions    The actions registered with WordPress to fire when the plugin loads.
	 */
	protected $actions;

	/**
	 * The array of filters registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $filters    The filters registered with WordPress to fire when the plugin loads.
	 */
	protected $filters;

	/**
	 * Initialize the collections used to maintain the actions and filters.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->actions = array();
		$this->filters = array();

	}

	/**
	 * Add a new action to the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 * @param    string               $hook             The name of the WordPress action that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the action is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         Optional. The priority at which the function should be fired. Default is 10.
	 * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1.
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Add a new filter to the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 * @param    string               $hook             The name of the WordPress filter that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the filter is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         Optional. The priority at which the function should be fired. Default is 10.
	 * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * A utility function that is used to register the actions and hooks into a single
	 * collection.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    array                $hooks            The collection of hooks that is being registered (that is, actions or filters).
	 * @param    string               $hook             The name of the WordPress filter that is being registered.
	 * @param    object               $component        A reference to the instance of the object on which the filter is defined.
	 * @param    string               $callback         The name of the function definition on the $component.
	 * @param    int                  $priority         The priority at which the function should be fired.
	 * @param    int                  $accepted_args    The number of arguments that should be passed to the $callback.
	 * @return   array                                  The collection of actions and filters registered with WordPress.
	 */
	private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {

		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args
		);

		return $hooks;

	}

	/**
	 * Register the filters and actions with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {

		foreach ( $this->filters as $hook ) {
			add_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

		foreach ( $this->actions as $hook ) {
			add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

		// include Woo_Cbrf_Exchange_Simple class
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-cbrf-exchange-simple.php';
		// include Woo_Cbrf_Exchange_Variations class
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-cbrf-exchange-variations.php';
		
		// create classes instances
		new Woo_Cbrf_Exchange_Simple;
		new Woo_Cbrf_Exchange_Variations;
		
		//1. Добавляем "кастомный" input (в functions.php или в другой скрипт, в котором у вас собрана логика WC):

		//add_action('woocommerce_product_options_general_product_data', 'add_custom_price_usd');
		function add_custom_price_usd() {
		
			echo '<h3>Цена в долларах</h3>';
		
			woocommerce_wp_text_input(
					array(
						'id' => 'price_usd',
						'placeholder' => 'Цена в долларах',
						'label' => __('Цена в долларах', 'my-text-domain'),
						'desc_tip' => 'true'
					)
			);
		}

		// 2. Добавляем хук для сохранения второй цены:

		//add_action('woocommerce_process_product_meta', 'save_custom_price_usd');
		function save_custom_price_usd( $post_id ){

			$price_usd_field = $_POST['price_usd'];
			update_post_meta($post_id, 'price_usd', esc_html($price_usd_field));

		}

		// 3. Добавляем "обработку" второй цены в корзине:

		//add_action( 'woocommerce_before_calculate_totals', 'set_custom_price_usd_to_cart', 10, 1 );
		function set_custom_price_usd_to_cart( $cart ){
			if ( is_admin() && ! defined( 'DOING_AJAX' ) )
				return;

			if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 )
				return;

			foreach ( $cart->cart_contents as $key => $value ) {				

				$product_id = $value['data']->get_ID();

				if ( has_term( 'Футболки', 'product_cat', $product_id )  ) {
					/* если да - меняем цену и пересчитываем по курсу "родной"  валюты */
					$price_usd = get_post_meta( $product_id, 'price_usd', true );
					$value['data']->set_price( convertUsdToRub(floatval($price_usd)) );
				}
			}
		}


		// Конвертируем цену в долларах в рубли по курсу ЦБ РФ:

		function convertUsdToRub($price) {
			return $price * getUsdFromXmlDaily();
		}


		// Получаем курс доллара из xml файла. Доллары в xml - это 12-й по счету элемент:

		function getUsdFromXmlDaily() {
			$array = getXmlDailyFromCbrf();
					// получаем 10 по счету валюту (usd)
			$usd = $array['Valute'][10];
					// наверное лучше округлить и сделать числом с плавающей точкой
			$usd = floatval( round( preg_replace("/[^-0-9\.]/", ".", $usd['Value']), 2) );

			return $usd;
		}

		// Загружаем xml с сайта cbr.ru и кэшируем на 12 часов:

		function getXmlDailyFromCbrf() {
			
			// берем из объектного кэша если он есть, или загружаем с http://cbr.ru/scripts/XML_daily.asp
			$xml_daily = get_transient( 'cbrf_exchange' );
			$result = json_decode( json_encode( $xml_daily ), TRUE);
			if( false === $xml_daily ) {
				$xml_daily = simplexml_load_file( 'http://cbr.ru/scripts/XML_daily.asp' );
				$result = json_decode( json_encode( $xml_daily ), TRUE);
							// сохраняем в кэш на сутки
				set_transient( 'cbrf_exchange', $result, 12 * HOUR_IN_SECONDS );
			}
					// если нужно очистить кэш
			//delete_transient('cbrf_exchange');
		
			return $result;
		}

		/**
		 * 4. Выводим вторую цену в интерфейс - 
		 * поскольку цену мы сохранили как метабокс, 
		 * то в интерфейсе получаем ее по id товара с помощью get_post_meta() :
		 * 
		 * get_post_meta( $product->get_ID(), 'price_usd', true );
		 */

		
	}

}
