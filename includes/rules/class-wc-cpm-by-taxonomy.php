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
                'values'    => wc_get_product_types()
			);

			return $fields;
        }

		private function get_all_product_types(){
			$types = wc_get_product_types();
			$types[] = ['Virtual'];
			$types[] = ['Downloadable'];
			
			return $types;
		}
    }

}
