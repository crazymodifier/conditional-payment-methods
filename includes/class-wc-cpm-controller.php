<?php
/**
 * Main class
 *
 * @package     conditional-payment-methods-for-woocommerce/includes/
 * @since       1.0.0
 * @version     1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_CPM_Controller' ) ) {

	/**
	 * Main class for Conditional Payment Methods For WooCommerce
	 */
	class WC_CPM_Controller {

		/**
		 * Plugin's Meta Data
		 *
		 * @var $plugin_data
		 */
		public $plugin_data = array();

		/**
		 * Variable to hold instance of Conditional Payment Methods
		 *
		 * @var $instance
		 */
		private static $instance = null;

		/**
		 * Get single instance of Conditional Payment Methods.
		 *
		 * @return Conditional_Payment_Methods_For_WooCommerce Singleton object of this class
		 */
		public static function get_instance() {

			// Check if instance is already exists.
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Cloning is forbidden.
		 *
		 * @since 3.3.0
		 */
		private function __clone() {
			wc_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'conditional-payment-methods-for-woocommerce' ), '3.3.0' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since 3.3.0
		 */
		private function __wakeup() {
			wc_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'conditional-payment-methods-for-woocommerce' ), '3.3.0' );
		}

		/**
		 * Constructor
		 */
		public function __construct() {
			$this->includes();

			if ( ! is_admin() ) {
				add_filter( 'woocommerce_available_payment_gateways', array( $this, 'filter_payment_gateways' ) );
			}
		}

		/**
		 * Function to handle WC compatibility related function call from appropriate class
		 *
		 * @param string $function_name Function to call.
		 * @param array  $arguments Array of arguments passed while calling $function_name.
		 * @return mixed Result of function call.
		 */
		public function __call( $function_name, $arguments = array() ) {

			if ( ! is_callable( 'SA_WC_Compatibility_3_8', $function_name ) ) {
				return;
			}

			if ( ! empty( $arguments ) ) {
				return call_user_func_array( 'SA_WC_Compatibility_3_8::' . $function_name, $arguments );
			} else {
				return call_user_func( 'SA_WC_Compatibility_3_8::' . $function_name );
			}

		}

		/**
		 * Include plugin files
		 */
		public function includes() {

			// Admin functions and meta-boxes.
			if ( is_admin() ) {
				$this->load_class( CPM_PLUGIN_DIRPATH . '/includes/admin/class-wc-cpm-admin-settings.php', 'WC_CPM_Admin_Settings' );
				$this->load_class( CPM_PLUGIN_DIRPATH . '/includes/admin/class-wc-cpm-admin-notifications.php', 'WC_CPM_Admin_Notifications' );
				$this->load_class( CPM_PLUGIN_DIRPATH . '/includes/rules/class-wc-cpm-by-cart.php', 'WC_CPM_By_Cart' );
				$this->load_class( CPM_PLUGIN_DIRPATH . '/includes/rules/class-wc-cpm-by-location.php', 'WC_CPM_By_Location' );
				$this->load_class( CPM_PLUGIN_DIRPATH . '/includes/rules/class-wc-cpm-by-taxonomy.php', 'WC_CPM_By_Taxonomy' );
			} else {
				include_once CPM_PLUGIN_DIRPATH . '/includes/rules/class-wc-cpm-by-cart.php';
				include_once CPM_PLUGIN_DIRPATH . '/includes/rules/class-wc-cpm-by-location.php';
				include_once CPM_PLUGIN_DIRPATH . '/includes/rules/class-wc-cpm-by-taxonomy.php';
			}

			include_once CPM_PLUGIN_DIRPATH . '/includes/compat/class-sa-wc-compatibility-3-8.php';

		}

		/**
		 * Load class
		 *
		 * @param  string $filepath Path of the file that needs to be included.
		 * @param  string $class_name Name of the class that needs to be loaded.
		 * @return mixed
		 */
		private function load_class( $filepath, $class_name = false ) {
			require_once $filepath;

			if ( $class_name ) {
				return ( ( is_callable( 'get_instance', $class_name ) ) ? $class_name::get_instance() : new $class_name() );
			}

			return true;
		}

		/**
		 * Filter payment gateways to show on checkout page
		 *
		 * @param  array $payment_gateways List of active payment gateways.
		 * @return array
		 */
		public function filter_payment_gateways( $payment_gateways ) {

			$payment_method_conditions = get_option( 'wc_payment_method_conditions', array() );
			
			
			
			if ( empty( $payment_method_conditions ) ) {
				return $payment_gateways;
			}

			// $product = wc_get_product(14);
			// print_r(WC()->countries->get_allowed_countries());

			$is_valid = false;

			$taxonomy_array = array('product_type','product_cat');

			foreach ( $payment_method_conditions['enabled'] as $enabled_conditions ) {

				foreach ( $enabled_conditions['rules'] as $rules ) {
					
					switch ($rules['field']) {

						case 'cart_subtotal':
							$obj = new WC_CPM_By_Cart() ;
							break;

						case (in_array( $rules['field'], $taxonomy_array )):
							$obj = new WC_CPM_By_Taxonomy() ;
							break;	
						default:
							$obj = new WC_CPM_By_Location() ;
							break;

					}

					$is_valid = $obj->validate( $rules );
					// echo '<pre>';print_r($is_valid);echo '</pre>';
					if ( ! $is_valid ) {
						break;
					}
					

				}

				if ( ( ! empty( $enabled_conditions['exclude'] ) && $is_valid ) || ( empty( $enabled_conditions['exclude'] ) && ! $is_valid ) ) {
					$payment_gateway_code = $enabled_conditions['payment_methods'];
					foreach ( $payment_gateway_code as $code ) {
						unset( $payment_gateways[ $code ] );
					}
				}
			}

			return $payment_gateways;
		}

		/**
		 * Get plugins data
		 *
		 * @return array
		 */
		public static function get_plugin_data() {

			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			return get_plugin_data( CPM_PLUGIN_FILE );
		}

		/**
		 * Get plugin version
		 *
		 * @return string
		 */
		public static function get_plugin_version() {

			$version = '';

			if ( is_callable( 'WC_CPM_Controller', 'get_plugin_data' ) ) {
				$plugin_data = self::get_plugin_data();
				$version     = $plugin_data['Version'];
			}

			return $version;
		}

	}

}
