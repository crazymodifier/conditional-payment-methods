<?php
/**
 * Class to handle cart validation
 *
 * @package  conditional-payment-methods-for-woocommerce/includes/rules/
 * @since    1.0.0
 * @version  1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_CPM_By_Cart' ) ) {

	/**
	 * Class for Validating Cart Fields in Conditional Payment Methods For WooCommerce
	 */
	class WC_CPM_By_Cart {

		/**
		 * Variable to hold instance of this class
		 *
		 * @var $instance
		 */
		private static $instance = null;

		/**
		 * Constructor
		 */
		public function __construct() {
			add_filter( 'cpm_available_rule_fields', array( $this, 'get_rule_fields' ), 11, 1 );
		}

		/**
		 * Get single instance of this class
		 *
		 * @return Payment_Gateway_Restrictions_For_WooCommerce Singleton object of this class
		 */
		public static function get_instance() {

			// Check if instance is already exists.
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Handle call to functions which is not available in this class
		 *
		 * @param string $function_name The function name.
		 * @param array  $arguments Array of arguments passed while calling $function_name.
		 * @return result of function call
		 */
		public function __call( $function_name, $arguments = array() ) {

			global $wc_cpm_controller;

			if ( ! is_callable( array( $wc_cpm_controller, $function_name ) ) ) {
				return;
			}

			if ( ! empty( $arguments ) ) {
				return call_user_func_array( array( $wc_cpm_controller, $function_name ), $arguments );
			} else {
				return call_user_func( array( $wc_cpm_controller, $function_name ) );
			}

		}

		/**
		 * Get all available rule fields
		 *
		 * @param array $fields Rule fields array.
		 * @return array
		 */
		public function get_rule_fields( $fields ) {

			$fields['cart_subtotal'] = array(
				'title' => __( 'Cart subtotal', 'conditional-payment-methods-for-woocommerce' ),
				'type'  => 'number',
			);

			return $fields;
		}

		/**
		 * Validate a given rule
		 *
		 * @param array $rule Rule array.
		 * @return boolean
		 */
		public function validate( $rule ) {

			$validate = false;

			switch ( $rule['field'] ) {

				case 'cart_subtotal':
					if ( isset( WC()->cart ) ) {
						$cart_subtotal = WC()->cart->subtotal;
						if ( ! empty( $cart_subtotal ) ) {
							if ( 'gte' === $rule['operator'] && $cart_subtotal >= $rule['value'] ) {
								$validate = true;
							} elseif ( 'lte' === $rule['operator'] && $cart_subtotal <= $rule['value'] ) {
								$validate = true;
							}
						}
					}
					break;

			}

			return $validate;

		}

	}

}
