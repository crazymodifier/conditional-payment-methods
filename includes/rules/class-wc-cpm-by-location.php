<?php
/**
 * Class to handle location validation
 *
 * @package  conditional-payment-methods-for-woocommerce/includes/rules/
 * @since    1.0.0
 * @version  1.1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_CPM_By_Location' ) ) {

	/**
	 * Class for Validating Location Fields in Conditional Payment Methods For WooCommerce
	 */
	class WC_CPM_By_Location {

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
			add_filter( 'cpm_available_rule_fields', array( $this, 'get_rule_fields' ) );
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

			$location_fields = array( 'billing_country', 'billing_state', 'billing_city', 'billing_postcode', 'shipping_country', 'shipping_state', 'shipping_city', 'shipping_postcode' );

			foreach ( $location_fields as $location_field ) {
				$fields[ $location_field ] = array(
					'title' => str_replace( '_', ' ', ucfirst( $location_field ) ),
					'type'  => 'string',
				);
				if ( 'billing_country' === $location_field || 'shipping_country' === $location_field ) {
					$fields[ $location_field ]['values'] = WC()->countries->get_allowed_countries();
				}
			}

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

			$wc_customer_obj = ( WC()->customer instanceof WC_Customer ) ? WC()->customer : '';

			switch ( $rule['field'] ) {

				case ( 'billing_country' === $rule['field'] || 'shipping_country' === $rule['field'] ):
					$rule_value = ( ! is_array( $rule['value'] ) && strpos( $rule['value'], ',' ) ) ? explode( ',', $rule['value'] ) : $rule['value'];

					if ( ! empty( $wc_customer_obj ) ) {
						$user_country = ( 'billing_country' === $rule['field'] ) ? $wc_customer_obj->get_billing_country() : $wc_customer_obj->get_shipping_country();

						if ( ! empty( $user_country ) ) {
							if ( 'in' === $rule['operator'] ) {
								$validate = in_array( $user_country, $rule_value, true );
							} elseif ( 'nin' === $rule['operator'] ) {
								$validate = ! in_array( $user_country, $rule_value, true );
							}
						}
					}
					break;
				case ( 'billing_city' === $rule['field'] || 'shipping_city' === $rule['field'] ):
					if ( ! is_array( $rule['value'] ) ) {
						if ( strpos( $rule['value'], ',' ) ) {
							$rule_value = explode( ',', $rule['value'] );
						} else {
							$rule_value = array( $rule['value'] );
						}
					}
					$rule_value = array_filter( $rule_value );

					if ( ! empty( $wc_customer_obj ) ) {
						$user_city = ( 'billing_city' === $rule['field'] ) ? $wc_customer_obj->get_billing_city() : $wc_customer_obj->get_shipping_city();

						if ( ! empty( $user_city ) ) {
							foreach ( $rule_value as $key => $value ) {
								if ( 'in' === $rule['operator'] ) {
									$is_match = strpos( $user_city, $value );
									if ( false !== $is_match ) {
										$validate = true;
										break;
									}
								} elseif ( 'nin' === $rule['operator'] ) {
									$is_match = ! strpos( $user_city, $value );
									if ( false !== $is_match ) {
										$validate = true;
										break;
									}
								}
							}
						}
					}
					break;
				case ( 'billing_postcode' === $rule['field'] || 'shipping_postcode' === $rule['field'] ):
					if ( ! is_array( $rule['value'] ) ) {
						if ( strpos( $rule['value'], ',' ) ) {
							$rule_value = explode( ',', $rule['value'] );
						} else {
							$rule_value = array( $rule['value'] );
						}
					}
					$rule_value = array_filter( $rule_value );

					if ( ! empty( $wc_customer_obj ) ) {
						$user_pincode = ( 'billing_postcode' === $rule['field'] ) ? $wc_customer_obj->get_billing_postcode() : $wc_customer_obj->get_shipping_postcode();

						if ( ! empty( $user_pincode ) ) {
							foreach ( $rule_value as $key => $value ) {
								if ( 'in' === $rule['operator'] ) {
									$is_match = strpos( $user_pincode, $value );
									if ( false !== $is_match ) {
										$validate = true;
										break;
									}
								} elseif ( 'nin' === $rule['operator'] ) {
									$is_match = ! strpos( $user_pincode, $value );
									if ( false !== $is_match ) {
										$validate = true;
										break;
									}
								}
							}
						}
					}
					break;
				case ( 'billing_state' === $rule['field'] || 'shipping_state' === $rule['field'] ):
					if ( ! is_array( $rule['value'] ) ) {
						if ( strpos( $rule['value'], ',' ) ) {
							$rule_value = explode( ',', $rule['value'] );
						} else {
							$rule_value = array( $rule['value'] );
						}
					}
					$rule_value = array_filter( $rule_value );

					if ( ! empty( $wc_customer_obj ) ) {
						$user_state   = ( 'billing_state' === $rule['field'] ) ? $wc_customer_obj->get_billing_state() : $wc_customer_obj->get_shipping_state();
						$user_country = ( 'billing_state' === $rule['field'] ) ? $wc_customer_obj->get_billing_country() : $wc_customer_obj->get_shipping_country();

						$get_state_from_country = WC()->countries->get_states( $user_country );
						$user_state_codes       = array_keys( $get_state_from_country );
						$user_state_names       = implode( ',', array_values( $get_state_from_country ) );

						$computed_array = array_intersect( $rule_value, $user_state_codes );

						foreach ( $rule_value as $key => $value ) {
							if ( 'in' === $rule['operator'] ) {
								if ( $value === $user_state ) {
									$validate = true;
									break;
								} else {
									$is_code_match = in_array( $user_state, $computed_array, true );
									if ( empty( $is_code_match ) ) {
										$is_code_match = strpos( $user_state_names, $user_state );
										if ( false !== $is_code_match ) {
											$validate = true;
											break;
										} else {
											if ( in_array( $user_state, $user_state_codes, true ) ) {
												$user_state_name_from_code = $get_state_from_country[ $user_state ];
												$is_string_match           = strpos( $user_state_name_from_code, $value );
												if ( false !== $is_string_match ) {
													$validate = true;
													break;
												}
											}
										}
									} else {
										$validate = true;
										break;
									}
								}
							} elseif ( 'nin' === $rule['operator'] ) {
								if ( $value !== $user_state ) {
									$validate = true;
									break;
								} else {
									$is_code_match = ! in_array( $user_state, $computed_array, true );
									if ( empty( $is_code_match ) ) {
										$is_code_match = ! strpos( $user_state_names, $user_state );
										if ( false !== $is_code_match ) {
											$validate = true;
											break;
										} else {
											if ( ! in_array( $user_state, $user_state_codes, true ) ) {
												$user_state_name_from_code = $get_state_from_country[ $user_state ];
												$is_string_match           = ! strpos( $user_state_name_from_code, $value );
												if ( false !== $is_string_match ) {
													$validate = true;
													break;
												}
											}
										}
									} else {
										$validate = true;
										break;
									}
								}
							}
						}
					}
					break;

			}

			return $validate;

		}

	}

}
