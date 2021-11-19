<?php
/**
 * Plugin Name: Conditional Payment Methods For WooCommerce
 * Plugin URI: https://woocommerce.com/product/conditional-payment-methods-for-woocommerce/
 * Description: Exclude payment methods using conditional logic.
 * Version: 1.3.0
 * Author: StoreApps
 * Author URI: http://www.storeapps.org/
 * Developer: StoreApps
 * Developer URI: https://www.storeapps.org/
 * Requires at least: 4.9.0
 * Tested up to: 5.8.1
 * WC requires at least: 3.6.0
 * WC tested up to: 5.9.0
 * Text Domain: conditional-payment-methods-for-woocommerce
 * Domain Path: /languages/
 * Woo: 5240923:853e94ae4aa78b538d9d3c3fd7a8130d
 * Copyright (c) 2019-2021 StoreApps All rights reserved.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package conditional-payment-methods-for-woocommerce
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load Conditional Payment Methods For WooCommerce only if woocommerce is activated
 */
function initialize_conditional_payment_methods_for_woocommerce() {

	if ( ! defined( 'CPM_PLUGIN_FILE' ) ) {
		define( 'CPM_PLUGIN_FILE', __FILE__ );
	}
	if ( ! defined( 'CPM_PLUGIN_DIRPATH' ) ) {
		define( 'CPM_PLUGIN_DIRPATH', dirname( __FILE__ ) );
	}
	if ( ! defined( 'CPM_PLUGIN_BASENAME' ) ) {
		define( 'CPM_PLUGIN_BASENAME', plugin_basename( CPM_PLUGIN_FILE ) );
	}
	if ( ! defined( 'CPM_PLUGIN_URL' ) ) {
		define( 'CPM_PLUGIN_URL', plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) );
	}

	$active_plugins = (array) get_option( 'active_plugins', array() );

	if ( is_multisite() ) {
		$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
	}

	if ( ( in_array( 'woocommerce/woocommerce.php', $active_plugins, true ) || array_key_exists( 'woocommerce/woocommerce.php', $active_plugins ) ) ) {
		require_once 'includes/class-wc-cpm-controller.php';
		$GLOBALS['wc_cpm_controller'] = WC_CPM_Controller::get_instance();
	}

}
add_action( 'plugins_loaded', 'initialize_conditional_payment_methods_for_woocommerce' );
