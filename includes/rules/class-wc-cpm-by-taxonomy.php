<?php

/**
 * Class to handle product type validation
 *
 * @package  conditional-payment-methods-for-woocommerce/includes/rules/
 * @since    1.0.0
 * @version  1.1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if(!class_exists('WC_CPM_By_Taxonomy')){
    
    /**
	 * Class for Validating Product type in Conditional Payment Methods For WooCommerce
	 */

    class WC_CPM_By_Taxonomy{


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
        

        public function get_rule_fields($fields)
        {
            $fields['product_type'] = array(
				'title'     => __( 'Product', 'conditional-payment-methods-for-woocommerce' ),
				'type'      => 'string',
                'values'    => $this->get_all_product_types()
			);

			$fields['product_taxonomy'] = array(
				'title'     => __( 'Product Taxonomy', 'conditional-payment-methods-for-woocommerce' ),
				'type'      => 'string',
                'values'    => $this->get_all_taxonomies()
			);

			return $fields;
        }

		private function get_all_product_types(){
			$types = wc_get_product_types();
			$types[] = ['Virtual'];
			$types[] = ['Downloadable'];
			
			return $types;
		}

		private function get_all_taxonomies(){
			$taxonomies = [];
			return $taxonomies;
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

				case 'product_type':

					if ( isset( WC()->cart )){
						$items = WC()->cart->get_cart();
				
						foreach($items as $item => $values) {
							$product_id = $values['data']->get_id(); 

							$productType = get_the_terms( $product_id,'product_type')[0]->slug;

							if ( 'in' === $rule['operator'] ) {
								$validate = in_array($productType, $rule['value'], true );
							} elseif ( 'nin' === $rule['operator'] ) {
								$validate = !in_array($productType, $rule['value'], true );
							}

							if($validate){
								return $validate;
							}
						} 
					}
					
					break;

			}

			return $validate;

		}
    }

}
