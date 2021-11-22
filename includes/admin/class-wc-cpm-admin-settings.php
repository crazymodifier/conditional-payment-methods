<?php
/**
 * Class to handle settings
 *
 * @package  conditional-payment-methods-for-woocommerce/includes/admin/
 * @since    1.0.0
 * @version  1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_CPM_Admin_Settings' ) ) {

	/**
	 * Class for Admin Settings in Conditional Payment Methods For WooCommerce
	 */
	class WC_CPM_Admin_Settings {

		/**
		 * Variable to hold instance of this class
		 *
		 * @var $instance
		 */
		private static $instance = null;

		/**
		 * Custom section name.
		 *
		 * @var string
		 */
		public static $section = 'conditional_payment_methods_for_woocommerce';

		/**
		 * Constructor.
		 */
		public function __construct() {
			// Add conditions tab.
			add_filter( 'woocommerce_get_sections_checkout', array( $this, 'register_section' ), 10, 1 );
			// Output conditions page.
			add_action( 'woocommerce_settings_checkout', array( $this, 'output' ) );

			// Admin scripts.
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_css_js' ), 11 );

			add_action( 'woocommerce_update_options_checkout_' . self::$section, array( $this, 'save_admin_settings' ) );
		}

		/**
		 * Get single instance of this class
		 *
		 * @return WC_CPM_Admin_Settings Singleton object of this class
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
		 * Function to register section under "Payments" settings in WooCommerce
		 *
		 * @param array $sections Existing sections.
		 * @return array $sections
		 */
		public function register_section( $sections ) {
			$sections['conditional_payment_methods_for_woocommerce'] = __( 'Conditions', 'conditional-payment-methods-for-woocommerce' );

			return $sections;
		}

		/**
		 * Function to enqueue scripts & styles for settings page.
		 *
		 * @return void
		 */
		public function enqueue_admin_css_js() {
			global $current_section;

			if ( 'conditional_payment_methods_for_woocommerce' !== $current_section ) {
				return;
			}

			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			// Code for registering & enqueuing scripts.
			wp_register_script( 'selectWoo', WC()->plugin_url() . '/assets/js/selectWoo/selectWoo.full' . $suffix . '.js', array( 'jquery' ), WC()->version, false );
			if ( ! wp_script_is( 'wc-enhanced-select', 'registered' ) ) {
				wp_register_script( 'wc-enhanced-select', WC()->plugin_url() . '/assets/js/admin/wc-enhanced-select' . $suffix . '.js', array( 'jquery', 'selectWoo' ), WC()->version, false );
			}

			$localized_strings = array(
				'clickToToggle'                       => __( 'Click to toggle', 'conditional-payment-methods-for-woocommerce' ),
				'remove'                              => __( 'Remove', 'conditional-payment-methods-for-woocommerce' ),
				'title'                               => __( 'Title', 'conditional-payment-methods-for-woocommerce' ),
				'titlePlaceholder'                    => __( 'Description for this rule&hellip;', 'conditional-payment-methods-for-woocommerce' ),
				'include'                             => __( 'Show', 'conditional-payment-methods-for-woocommerce' ),
				'exclude'                             => __( 'Hide', 'conditional-payment-methods-for-woocommerce' ),
				'selectPaymentMethod'                 => __( 'Select payment methods&hellip;', 'conditional-payment-methods-for-woocommerce' ),
				'expandAll'                           => __( 'Expand all', 'conditional-payment-methods-for-woocommerce' ),
				'closeAll'                            => __( 'Close all', 'conditional-payment-methods-for-woocommerce' ),
				'paymentMethodConditions'             => __( 'Payment Method Conditions', 'conditional-payment-methods-for-woocommerce' ),
				'noConditionsFound'                   => __( 'No conditions found. Add some now?', 'conditional-payment-methods-for-woocommerce' ),
				'addCondition'                        => __( 'Add Condition', 'conditional-payment-methods-for-woocommerce' ),
				'rules'                               => __( 'Rules', 'conditional-payment-methods-for-woocommerce' ),
				'selectField'                         => __( 'Select field&hellip;', 'conditional-payment-methods-for-woocommerce' ),
				'selectOperator'                      => __( 'Select operator&hellip;', 'conditional-payment-methods-for-woocommerce' ),
				'selectBillingCountries'              => __( 'Select billing countries&hellip;', 'conditional-payment-methods-for-woocommerce' ),
				'selectShippingCountries'             => __( 'Select shipping countries&hellip;', 'conditional-payment-methods-for-woocommerce' ),
				'enterMultipleValuesSeparatedByComma' => __( 'Enter multiple values separated by comma', 'conditional-payment-methods-for-woocommerce' ),
				'enterNumber'                         => __( 'Enter value', 'conditional-payment-methods-for-woocommerce' ),
				'addRule'                             => __( 'Add rule', 'conditional-payment-methods-for-woocommerce' ),
				'removeRule'                          => __( 'Remove rule', 'conditional-payment-methods-for-woocommerce' ),
				'and'                                 => __( 'And', 'conditional-payment-methods-for-woocommerce' ),
				'andSmall'                            => __( 'and', 'conditional-payment-methods-for-woocommerce' ),
				'onlyWhen'                            => __( 'only when', 'conditional-payment-methods-for-woocommerce' ),
				'selectProductType'               => __( 'Select product type&hellip;', 'conditional-payment-methods-for-woocommerce' ),
				'selectProductTaxonomy'				=> __( 'Select product categories&hellip;', 'conditional-payment-methods-for-woocommerce' ),
			);

			$payment_methods           = WC()->payment_gateways->get_available_payment_gateways();
			$available_payment_methods = array();
			foreach ( $payment_methods as $key => $obj ) {
				$available_payment_methods[ $key ] = $obj->get_title();
			}

			$available_fields    = apply_filters( 'cpm_available_rule_fields', array() );
			$available_operators = apply_filters(
				'cpm_available_rule_field_operators',
				array(
					'number' => array(
						'lte' => '<=',
						'gte' => '>=',
					),
					'string' => array(
						'in'  => __(
							'in',
							'conditional-payment-methods-for-woocommerce'
						),
						'nin' => __(
							'not in',
							'conditional-payment-methods-for-woocommerce'
						),
					),
				)
			);

			$settings_data = get_option( 'wc_payment_method_conditions', array() );

			$data_params = array(
				'availablePaymentMethods'     => $available_payment_methods,
				'availableRuleFields'         => $available_fields,
				'availableRuleFieldOperators' => $available_operators,
			);

			wp_register_script( 'wc-cpm-settings-js', CPM_PLUGIN_URL . '/assets/js/cpm-admin-settings.js', array( 'wc-enhanced-select' ), WC_CPM_Controller::get_plugin_version(), false );
			wp_enqueue_script( 'wc-cpm-settings-js' );
			$js_params = array(
				'security'         => wp_create_nonce( 'cpm_settings_security' ),
				'localizedStrings' => $localized_strings,
				'settingsData'     => $settings_data,
				'dataParams'       => $data_params,
			);
			wp_localize_script( 'wc-cpm-settings-js', 'cpmSettingsParams', $js_params );

			// Code for registering & enqueuing styles.
			wp_register_style( 'wc-cpm-settings-css', CPM_PLUGIN_URL . '/assets/css/cpm-admin-settings.css', array( 'woocommerce_admin_styles' ), WC_CPM_Controller::get_plugin_version(), false );
			wp_enqueue_style( 'wc-cpm-settings-css' );
		}

		/**
		 * Function to output restrictions page
		 */
		public function output() {

			global $current_section;

			if ( 'conditional_payment_methods_for_woocommerce' === $current_section ) {
				wp_nonce_field( 'wc_cpm_settings', 'cpm_settings_security', false );
				?>
				<p>
					<?php echo esc_html__( 'Restrict the payment methods available at the checkout based on following conditons. A condition becomes valid when all defined rules match.', 'conditional-payment-methods-for-woocommerce' ); ?>
				</p>
				<tr><td>
					<div id="wc_cpm_data" class="panel woocommerce_options_panel wc-metaboxes-wrapper postbox"></div>
				</td></tr>
				<?php
			}

		}

		/**
		 * Save conditional payment methods data in option
		 */
		public function save_admin_settings() {

			if ( empty( $_POST['cpm_settings_security'] ) || ! wp_verify_nonce( wc_clean( wp_unslash( $_POST['cpm_settings_security'] ) ), 'wc_cpm_settings' ) ) { // phpcs:ignore
				return;
			}

			$settings = ( isset( $_POST['condition'] ) ) ? wc_clean( wp_unslash( $_POST['condition'] ) ) : array(); // phpcs:ignore
			update_option( 'wc_payment_method_conditions', $settings, 'no' );
		}

	}

}
