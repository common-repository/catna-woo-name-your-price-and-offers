<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VICATNA_Admin_Admin {
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		add_filter( 'plugin_action_links_catna-woo-name-your-price-and-offers/catna-woo-name-your-price-and-offers.php', array(
			$this,
			'settings_link'
		) );
	}

	public function settings_link( $links ) {
		$settings_link = sprintf( '<a href="%s?page=catna-woocommerce-name-your-price-and-offers" title="%s">%s</a>', esc_url( admin_url( 'admin.php' ) ),
			esc_attr__( 'Settings', 'catna-woo-name-your-price-and-offers' ),
			esc_html__( 'Settings', 'catna-woo-name-your-price-and-offers' )
		);
		array_unshift( $links, $settings_link );

		return $links;
	}

	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'catna-woo-name-your-price-and-offers' );
		load_textdomain( 'catna-woo-name-your-price-and-offers', VICATNA_LANGUAGES . "catna-woo-name-your-price-and-offers-$locale.mo" );
		load_plugin_textdomain( 'catna-woo-name-your-price-and-offers', false, VICATNA_LANGUAGES );
	}

	public function init() {
		$this->load_plugin_textdomain();
		if ( class_exists( 'VillaTheme_Support' ) ) {
			new VillaTheme_Support(
				array(
					'support'    => 'https://wordpress.org/support/plugin/catna-woo-name-your-price-and-offers/',
					'docs'       => 'http://docs.villatheme.com/?item=catna-woocommerce-name-your-price-and-offers',
					'review'     => 'https://wordpress.org/support/plugin/catna-woo-name-your-price-and-offers/reviews/?rate=5#rate-response',
					'pro_url'    => 'https://1.envato.market/kjamOx',
					'css'        => VICATNA_CSS,
					'image'      => VICATNA_IMAGES,
					'slug'       => 'catna-woo-name-your-price-and-offers',
					'menu_slug'  => 'catna-woocommerce-name-your-price-and-offers',
					'survey_url' => 'https://script.google.com/macros/s/AKfycbxDUdcddeEiMtbSMeVNSMMKWw5Lzas7EHMDqo19YFSfeLO7w2A9oUieKW0h8bABDjcr/exec',
					'version'    => VICATNA_VERSION
				)
			);
		}
	}
}