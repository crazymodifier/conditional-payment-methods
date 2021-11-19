<?php
/**
 * Class to handle admin notifications
 *
 * @package  conditional-payment-methods-for-woocommerce/includes/admin/
 * @since    1.0.0
 * @version  1.0.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_CPM_Admin_Notifications' ) ) {

	/**
	 * Class for Admin Notifications in Conditional Payment Methods For WooCommerce
	 */
	class WC_CPM_Admin_Notifications {

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
			// Filter to add links on Plugins page.
			add_filter( 'plugin_action_links_' . plugin_basename( CPM_PLUGIN_BASENAME ), array( $this, 'plugin_action_links' ) );
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
		 * Additional link on plugins page
		 *
		 * @param  array $links Existing links.
		 * @return array Additonal links.
		 */
		public function plugin_action_links( $links = array() ) {

			$settings_link = add_query_arg(
				array(
					'page'    => 'wc-settings',
					'tab'     => 'checkout',
					'section' => 'conditional_payment_methods_for_woocommerce',
				),
				admin_url( 'admin.php' )
			);

			$action_links = array(
				'settings' => '<a href="' . esc_url( $settings_link ) . '" title="' . esc_attr( __( 'View Conditional Payment Methods For WooCommerce Settings', 'conditional-payment-methods-for-woocommerce' ) ) . '">' . esc_html( __( 'Settings', 'conditional-payment-methods-for-woocommerce' ) ) . '</a>',
				'docs'     => '<a href="' . esc_url( 'http://docs.woocommerce.com/document/conditional-payment-methods-for-woocommerce/' ) . '" title="' . esc_attr( __( 'Documentation', 'conditional-payment-methods-for-woocommerce' ) ) . '" target="_blank">' . esc_html( __( 'Docs', 'conditional-payment-methods-for-woocommerce' ) ) . '</a>',
				'faqs'     => '<a href="' . esc_url( 'https://docs.woocommerce.com/document/conditional-payment-methods-for-woocommerce/#section-8' ) . '" title="' . esc_attr( __( 'FAQ', 'conditional-payment-methods-for-woocommerce' ) ) . '" target="_blank">' . esc_html( __( 'FAQ', 'conditional-payment-methods-for-woocommerce' ) ) . '</a>',
				'support'  => '<a href="' . esc_url( 'https://woocommerce.com/my-account/create-a-ticket/' ) . '" title=" ' . esc_attr( __( 'Get support', 'conditional-payment-methods-for-woocommerce' ) ) . '" target="_blank">' . __( 'Support', 'conditional-payment-methods-for-woocommerce' ) . '</a>',
				'review'   => '<a href="' . esc_url( 'https://woocommerce.com/products/conditional-payment-methods-for-woocommerce/#comments' ) . '" title=" ' . esc_attr( __( 'Leave a review', 'conditional-payment-methods-for-woocommerce' ) ) . '" target="_blank">' . __( 'Review', 'conditional-payment-methods-for-woocommerce' ) . '</a>',
			);

			return array_merge( $action_links, $links );
		}

	}

}
