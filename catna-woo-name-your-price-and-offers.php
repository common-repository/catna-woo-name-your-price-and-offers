<?php
/**
 * Plugin Name: Catna - Woo Name Your Price and Offers
 * Plugin URI: https://villatheme.com/extensions/catna-woocommerce-name-your-price-and-offers/
 * Description: Let customers propose their desired price for products on your online store. Approve offers based on your set conditions and rules.
 * Version: 1.1.1
 * Author: VillaTheme
 * Author URI: https://villatheme.com
 * Text Domain: catna-woo-name-your-price-and-offers
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Copyright 2021 - 2024 VillaTheme.com. All rights reserved.
 * Requires Plugins: woocommerce
 * Requires PHP: 7.0
 * Requires at least: 5.0
 * Tested up to: 6.5
 * WC requires at least: 7.0
 * WC tested up to: 8.7
 **/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

/**
 * Class VICATNA_INIT
 */
class VICATNA_INIT {
	public function __construct() {
		//compatible with 'High-Performance order storage (COT)'
		add_action( 'before_woocommerce_init', array( $this, 'before_woocommerce_init' ) );
		if ( is_plugin_active( 'catna-woocommerce-name-your-price-and-offers/catna-woocommerce-name-your-price-and-offers.php' ) ) {
			return;
		}
		$this->define();
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}
	public function init() {
		$include_dir = plugin_dir_path( __FILE__ ) . 'includes/';
		if ( ! class_exists( 'VillaTheme_Require_Environment' ) ) {
			include_once $include_dir . 'support.php';
		}

		$environment = new VillaTheme_Require_Environment( [
				'plugin_name'     => 'Catna - Woo Name Your Price and Offers',
				'php_version'     => '7.0',
				'wp_version'      => '5.0',
				'wc_version'      => '7.0',
				'require_plugins' => [ [ 'slug' => 'woocommerce', 'name' => 'WooCommerce' ] ]
			]
		);

		if ( $environment->has_error() ) {
			return;
		}
		$this->includes();
	}
	protected function define() {
		define( 'VICATNA_VERSION', '1.1.1' );
		define( 'VICATNA_DIR', plugin_dir_path( __FILE__ ) );
		define( 'VICATNA_INCLUDES', VICATNA_DIR . "includes" . DIRECTORY_SEPARATOR );
		define( 'VICATNA_ADMIN', VICATNA_INCLUDES . "admin" . DIRECTORY_SEPARATOR );
		define( 'VICATNA_FRONTEND', VICATNA_INCLUDES . "frontend" . DIRECTORY_SEPARATOR );
		define( 'VICATNA_LANGUAGES', VICATNA_DIR . "languages" . DIRECTORY_SEPARATOR );
		define( 'VICATNA_TEMPLATES', VICATNA_INCLUDES . "templates" . DIRECTORY_SEPARATOR );
		$plugin_url = plugins_url( 'assets/', __FILE__ );
		define( 'VICATNA_CSS', $plugin_url . "css/" );
		define( 'VICATNA_JS', $plugin_url . "js/" );
		define( 'VICATNA_IMAGES', $plugin_url . "images/" );
		define( 'VICATNA_SUFFIX', WP_DEBUG ? '' : 'min.' );
	}
	protected function includes() {
		$files = array(
			VICATNA_INCLUDES . "functions.php",
			VICATNA_INCLUDES . 'support.php',
			VICATNA_INCLUDES . 'data.php',
			VICATNA_INCLUDES . 'rule.php',
		);
		foreach ( $files as $file ) {
			if ( file_exists( $file ) ) {
				require_once $file;
			}
		}
		villatheme_include_folder( VICATNA_ADMIN, 'VICATNA_Admin_' );
		if ( ! is_admin() || wp_doing_ajax() ) {
			villatheme_include_folder( VICATNA_FRONTEND, 'VICATNA_Frontend_' );
		}
	}
	public function before_woocommerce_init() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
}

new VICATNA_INIT();