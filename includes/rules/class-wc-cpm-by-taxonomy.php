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
			add_action( 'wp_ajax_cpm_get_taxonomy_list', array($this,'cpm_get_taxonomy_list'));
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
		 * Setting rule
		 */

        public function get_rule_fields($fields)
        {
            $fields['product_type'] = array(
				'title'     => __( 'Product type', 'conditional-payment-methods-for-woocommerce' ),
				'type'      => 'string',
                'values'    => $this->get_all_product_types()
			);

			$fields['product_cat'] = array(
				'title'     => __( 'Product category', 'conditional-payment-methods-for-woocommerce' ),
				'type'      => 'string',
                'values'    => $this->get_all_taxonomies('product_cat')
			);

			return $fields;
        }

		/**
		 * Get product types
		 */

		private function get_all_product_types(){

			$types = wc_get_product_types();
			$types['virtual'] = ['Virtual'];
			$types['downloadable'] = ['Downloadable'];

			ksort($types);

			return $types;
		}

		/**
		 * Get terms by taxonomy
		 * @param $taxonomy = taxonomy
		 */

		private function get_all_taxonomies($taxonomy='product_cat'){

			$taxonomies = get_terms(
				array(
					'taxonomy' => $taxonomy, 
					'hide_empty' => false,
					'fields'		=> 'id=>name'
				)
			);

			if(!is_wp_error( $taxonomies )){
				return $taxonomies;
			}
			
			return array();
		}

		/**
		 * Validate a given rule
		 *
		 * @param array $rule Rule array.
		 * @return boolean
		 */
		public function validate( $rule ) {

			$validate = false;

			$taxonomy_array = array('product_cat');
			
			if ( isset( WC()->cart )){

				$items = WC()->cart->get_cart();

				foreach($items as $item => $values) {

					switch ( $rule['field'] ) {

						case 'product_type':

							if(in_array('variable', $rule['value'])){
								$rule['value'][] = 'variation';
							}

							$product_id = !empty($values['variation_id'])?$values['variation_id']:$values['product_id'];

							$validate = $this->product_type_rule_validation($rule['value'], $rule['operator'],$product_id);

							if($validate){
								return $validate;
							}
							break;
						case (in_array( $rule['field'], $taxonomy_array )):

							$validate = $this->product_taxonomy_rule_validation($rule['value'],$rule['operator'],$values['product_id'],$rule['field']);
							
							if($validate){
								return $validate;
							}
							break;
					}

				}

			}
			
			return $validate;

		}

		/**
		 * taxonomy rule validation
		 */

		private function product_taxonomy_rule_validation($value='', $operator='', $product_id ='', $taxonomy = 'product_cat'){

			if(empty($product_id) || empty($taxonomy) || empty($operator) || empty($value)){
				return false;
			}

			return ('in' === $operator) ? has_term($value, $taxonomy, $product_id) : !has_term($value, $taxonomy , $product_id);
		}

		/**
		 * product type rule validation
		 */

		private function product_type_rule_validation($value= '', $operator='',$product_id=0){
			$validate = false;
			if(empty($product_id) || empty($operator) || empty($value)){
				return $validate;
			}

			$product = wc_get_product( $product_id );
			
			if(!$product){
				return $validate;
			}

			switch ($value) {

				case (in_array($product->get_type(), $value, true )):
					$validate = true;
					break;

				case ($product->is_downloadable() && in_array('downloadable', $value, true )):
					$validate = true;
					break;

				case ($product->is_virtual() && in_array('virtual', $value, true )):
					$validate = true;
					break;

				default:
					$validate = false;
					break;

			}

			return ('in' === $operator) ? $validate : !$validate ;
		}

		/**
		 * product type rule validation
		 */


		public function cpm_get_taxonomy_list(){
			
			$page 	= $_REQUEST['page'] ? absint( sanitize_text_field($_REQUEST['page']) ):1;
			$taxonomy =  $_REQUEST['taxonomy'] ? sanitize_text_field( $_REQUEST['taxonomy'] ):'';
			$search =  $_REQUEST['search'] ? sanitize_text_field( $_REQUEST['search'] ):'';
			$limit  = $_REQUEST['limit'] ? absint( sanitize_text_field($_REQUEST['limit']) ) : 5;	

			$res['load_more'] = true;
			$res['results'] = array();
			$cats 	= get_terms(
					array(
					'taxonomy'		=> $taxonomy,
					'hide_empty' 	=> false,
					'search'		=> $search,
					'number'		=> $limit,
					'offset'		=> ($page - 1) * $limit,
					'fields'		=> 'id=>name'
				)
			);
			
			if( count( $cats ) < $limit ) {				
				$res['load_more'] = false;
			}	
			
			if( !empty( $cats ) && !is_wp_error( $cats )){
				foreach ( $cats as $key => $value ) {
					$res['results'][] = array(
						'id' => $key,
						'text' => $value
					);
				}
				
			}

			wp_send_json( $res )  ; 
			
		}
    }

}
