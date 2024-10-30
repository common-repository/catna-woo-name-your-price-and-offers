<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VICATNA_Frontend_Product {
	public static $nyp_enable, $so_enable;
	public static $settings, $rule, $cache = array();
	function __construct() {
		self::$settings   = VICATNA_DATA::get_instance();
		self::$nyp_enable = self::$settings->get_params( 'nyp_enable' );
		self::$so_enable  = self::$settings->get_params( 'so_enable' );
		if ( ! self::$nyp_enable && ! self::$so_enable ) {
			return;
		}
		$positions = array(
			'before_atc' => 'woocommerce_before_add_to_cart_button',
			'after_atc'  => 'woocommerce_after_add_to_cart_button',
		);
		if ( self::$nyp_enable ) {
			$nyp_position = self::$settings->get_params( 'nyp_single_position' );
			$nyp_hook     = apply_filters( 'vicatna_nyp_single_position', $positions[ $nyp_position ] ?? 'woocommerce_before_add_to_cart_button' );
			// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
			$nyp_priority = apply_filters( 'vicatna_nyp_single_priority ', 'woocommerce_single_product_summary' === $nyp_hook ? 11 : 10 );
			add_action( $nyp_hook, array( $this, 'html_name_your_price' ), $nyp_priority );
			add_filter( 'woocommerce_product_is_on_sale', array( $this, 'vicatna_nyp_woocommerce_product_is_on_sale' ), PHP_INT_MAX, 2 );
			if ( self::$settings->get_params( 'nyp_hide_pd_price' ) ) {
				add_filter( 'woocommerce_get_price_html', array( $this, 'vicatna_nyp_woocommerce_get_price_html' ), PHP_INT_MAX, 2 );
			}
			// Set product as purchasable.
			add_filter( 'woocommerce_is_purchasable', array( $this, 'vicatna_nyp_woocommerce_is_purchasable' ), PHP_INT_MAX, 2 );
			add_filter( 'woocommerce_variation_is_purchasable', array( $this, 'vicatna_nyp_woocommerce_is_purchasable' ), PHP_INT_MAX, 2 );
		}
		if ( self::$so_enable ) {
			$so_position = self::$settings->get_params( 'so_offer_bt_position' );
			$so_hook     = apply_filters( 'vicatna_nyp_single_position', $positions[ $so_position ] ?? 'woocommerce_before_add_to_cart_button' );
			add_action( $so_hook, array( $this, 'html_offer' ) );
			if ( ! self::$settings->get_params( 'so_atc_normal' ) ) {
				add_filter( 'woocommerce_product_single_add_to_cart_text', array( __CLASS__, 'vicatna_woocommerce_product_single_add_to_cart_text' ), PHP_INT_MAX, 2 );
			}
			//set min qty in cart
			add_filter( 'woocommerce_quantity_input_args', array( $this, 'vicatna_woocommerce_quantity_input_args' ), PHP_INT_MAX, 2 );
		}
		//remove ajax add to cart on loop product
		add_filter( 'woocommerce_product_add_to_cart_url', array( $this, 'vicatna_woocommerce_product_add_to_cart_url' ), PHP_INT_MAX, 2 );
		add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'vicatna_woocommerce_loop_add_to_cart_link' ), PHP_INT_MAX, 2 );
		//change atc button label on loop product
		add_filter( 'woocommerce_product_add_to_cart_text', array( $this, 'vicatna_woocommerce_product_add_to_cart_text' ), PHP_INT_MAX, 2 );
		add_filter( 'woocommerce_available_variation', array( $this, 'vicatna_woocommerce_available_variation' ), 10, 3 );
		add_action( 'wp_enqueue_scripts', array( $this, 'vicatna_wp_enqueue_scripts' ) );
		add_filter( 'wp_kses_allowed_html', array( 'VICATNA_Frontend_Frontend', 'vicatna_wp_kses_allowed_html' ), PHP_INT_MAX, 2 );
		VICATNA_Frontend_Frontend::add_ajax_events();
		// add to cart event
		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'vicatna_woocommerce_add_cart_item_data' ), PHP_INT_MAX, 3 );
		// set new price
		add_filter( 'woocommerce_add_cart_item', array( $this, 'vicatna_mark_as_cart_item' ), PHP_INT_MIN, 1 );
		add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'vicatna_mark_as_cart_item' ), PHP_INT_MIN, 1 );
		add_filter( 'woocommerce_product_get_price', array( $this, 'vicatna_woocommerce_get_price' ), PHP_INT_MAX, 2 );
		add_filter( 'woocommerce_product_variation_get_price', array( $this, 'vicatna_woocommerce_get_price' ), PHP_INT_MAX, 2 );
		add_action( 'woocommerce_before_variations_form', array( $this, 'vicatna_woocommerce_before_variations_form' ) );
		$this->third_party();
	}

	public function vicatna_woocommerce_before_variations_form() {
		wp_nonce_field( 'vicatna_cart_nonce', '_vicatna_cart_nonce' );
	}
	public function third_party() {
		if ( self::$nyp_enable ) {
			add_action( 'vi_wcaio_before_add_to_cart_button', array( $this, 'html_name_your_price' ) );
		}
		if ( self::$so_enable ) {
			add_action( 'vi_wcaio_before_add_to_cart_button', array( $this, 'html_offer' ) );
			add_action( 'vi_wcaio_sc_pd_plus_after_atc', array( $this, 'vicatna_viwcaio_sc_pd_plus_after_atc' ), 10, 1 );
		}
		add_filter( 'vi_wcaio_sc_pd_plus_atc_class', array( $this, 'vicatna_vi_wcaio_sc_pd_plus_atc_class' ), PHP_INT_MAX, 2 );
	}
	public function vicatna_viwcaio_sc_pd_plus_after_atc( $product ) {
		if ( ! $product ) {
			return;
		}
		if ( in_array( $product->get_type() ?? '', apply_filters( 'vicatna_so_loop_exclude_pd_types', [ 'variable' ] ), true ) ) {
			return;
		}
		$product_id = $product->get_id() ?? $product->ID;
		$rule       = self::get_rule( $product_id, $product ) ?? array();
		if ( empty( $rule ) || ! isset( $rule['type'] ) || ! in_array( strval( $rule['type'] ), [ '1' ], true ) || empty( $rule['prices'] ) ) {
			return;
		}
		if ( ! empty( $cart_data = self::so_check_in_cart( $product_id ) ) && isset( $cart_data['price'], $cart_data['min_qty'] ) ) {
			echo sprintf( '<input type="hidden" name="vicatna_so_value" value="%s"><input type="hidden" name="vicatna_so_qty_min" value="%s">',
				esc_attr( $cart_data['price'] ), esc_attr( $cart_data['min_qty'] ) );
		}
	}
	public function vicatna_vi_wcaio_sc_pd_plus_atc_class( $class, $product ) {
		if ( ! $product ) {
			return $class;
		}
		$product_id = $product->get_id() ?? $product->ID;
		$rule       = self::get_rule( $product_id, $product ) ?? array();
		if ( empty( $rule ) || ! isset( $rule['type'] ) || ! in_array( strval( $rule['type'] ), [ '0', '1' ], true ) || ( isset( $rule['prices'] ) && empty( $rule['prices'] ) ) ) {
			return $class;
		}
		if ( self::$nyp_enable && '0' === $rule['type'] ) {
			$class .= ' vi-wcaio-product-bt-not-atc';
			return $class;
		}
		if ( self::$so_enable && '1' === $rule['type'] && ! in_array( $product->get_type() ?? '', apply_filters( 'vicatna_so_loop_exclude_pd_types', [ 'variable' ] ), true ) ) {
			if ( ! empty( $cart_data = self::so_check_in_cart( $product_id ) ) && isset( $cart_data['price'], $cart_data['min_qty'] ) ) {
				return $class;
			} elseif ( ! self::$settings->get_params( 'so_atc_normal' ) ) {
				$class .= ' vi-wcaio-product-bt-not-atc';
			}
		}
		return $class;
	}

	public function html_offer() {
		global $product;
		$product_id   = $product->get_id() ?? $product->ID;
		$product_type = $product->get_type() ?? '';
		$rule         = self::get_rule( $product_id, $product ) ?? array();
		if ( empty( $rule ) || ! isset( $rule['type'] ) || ! in_array( strval( $rule['type'] ), [ '1', 'both' ], true ) || ( isset( $rule['prices'] ) && empty( $rule['prices'] ) ) ) {
			return;
		}
		self::enqueue_scripts();
		wc_get_template( 'smart-offers.php',
			array(
				'product_id'   => $product_id,
				'product_type' => $product_type,
				'product'      => $product,
				'rule'         => $rule['prices'] ?? array(),
				'settings'     => self::$settings,
			),
			'catna-woo-name-your-price-and-offers' . DIRECTORY_SEPARATOR,
			VICATNA_TEMPLATES );
	}
	public function html_offer_popup() {
		wc_get_template( 'smart-offers-popup.php',
			array(
				'settings' => VICATNA_DATA::get_instance(),
			),
			'catna-woo-name-your-price-and-offers' . DIRECTORY_SEPARATOR,
			VICATNA_TEMPLATES );
	}
	public function html_name_your_price() {
		global $product;
		$product_id   = $product->get_id() ?? $product->ID;
		$product_type = $product->get_type() ?? '';
		$rule         = self::get_rule( $product_id, $product ) ?? array();
		if ( empty( $rule ) || ! isset( $rule['type'] ) || ! in_array( strval( $rule['type'] ), [ '0', 'both' ], true ) || ( isset( $rule['prices'] ) && empty( $rule['prices'] ) ) ) {
			return;
		}
		self::enqueue_scripts();
		wc_get_template( 'name-your-price.php',
			array(
				'product_id'   => $product_id,
				'product_type' => $product_type,
				'product'      => $product,
				'rule'         => $rule['prices'] ?? array(),
				'settings'     => self::$settings,
			),
			'catna-woo-name-your-price-and-offers' . DIRECTORY_SEPARATOR,
			VICATNA_TEMPLATES );
	}

	public function vicatna_woocommerce_get_price( $price, $product ) {
		if ( ! $product ) {
			return $price;
		}
		if ( ! did_action( 'woocommerce_load_cart_from_session' ) ) {
			return $price;
		}
		if ( empty( $product->vicatna_cart_item ) ) {
			return $price;
		}
		$price                 = $price ?: 0;
		$vicatna_cart_item_key = $product->vicatna_cart_item;
		if ( isset( self::$cache['cart_price'][ $vicatna_cart_item_key ][ $price ] ) ) {
			return self::$cache['cart_price'][ $vicatna_cart_item_key ][ $price ];
		}
		if ( isset( $product->vicatna_nyp ) ) {
			return self::$cache['cart_price'][ $vicatna_cart_item_key ][ $price ] = apply_filters( 'vicatna_woocommerce_get_price', floatval( ( $product->vicatna_nyp['price'] ?? 0 ) ?: 0 ), $product );
		}
		if ( isset( $product->vicatna_so ) ) {
			return self::$cache['cart_price'][ $vicatna_cart_item_key ][ $price ] = apply_filters( 'vicatna_woocommerce_get_price', floatval( ( $product->vicatna_so['price'] ?? 0 ) ?: 0 ), $product );
		}
		return self::$cache['cart_price'][ $vicatna_cart_item_key ][ $price ] = $price;
	}
	public function vicatna_mark_as_cart_item( $cart_item_data ) {
		$cart_item_data['data']->vicatna_cart_item = $cart_item_data['key'];
		if ( isset( $cart_item_data['vicatna_nyp'] ) ) {
			$cart_item_data['data']->vicatna_nyp = $cart_item_data['vicatna_nyp'];
		} elseif ( ! empty( $cart_item_data['vicatna_so']['min_qty'] ) && ( floatval( $cart_item_data['vicatna_so']['min_qty'] ) <= floatval( $cart_item_data['quantity'] ) ) ) {
			$cart_item_data['data']->vicatna_so = $cart_item_data['vicatna_so'];
		}
		return $cart_item_data;
	}
	public function vicatna_woocommerce_add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {
		if ( isset( $_REQUEST['_vicatna_cart_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['_vicatna_cart_nonce'] ) ), 'vicatna_cart_nonce' ) ) {
			if ( isset( $_REQUEST['vicatna_nyp_value'] ) && ( ! empty( $_REQUEST['vicatna_nyp_value'] ) || self::$settings->get_params( 'nyp_free_purchase' ) ) ) {
				$cart_item_data['vicatna_nyp'] = array(
					'price'    => wc_clean( wp_unslash( $_REQUEST['vicatna_nyp_value'] ) ),
					'currency' => get_woocommerce_currency()
				);
				return apply_filters( 'vicatna_nyp_woocommerce_add_cart_item_data', $cart_item_data, $product_id, $variation_id );
			}
			if ( isset( $_REQUEST['vicatna_so_value'], $_REQUEST['vicatna_so_qty_min'] ) ) {
				$cart_item_data['vicatna_so'] = array(
					'price'    => wc_clean( wp_unslash( $_REQUEST['vicatna_so_value'] ) ),
					'min_qty'  => wc_clean( wp_unslash( $_REQUEST['vicatna_so_qty_min'] ) ),
					'currency' => get_woocommerce_currency()
				);
				return apply_filters( 'vicatna_so_woocommerce_add_cart_item_data', $cart_item_data, $product_id, $variation_id );
			}
		}
		return $cart_item_data;
	}
	public function vicatna_wp_enqueue_scripts() {
		$suffix = VICATNA_SUFFIX;
		wp_register_style( 'vicatna-single', VICATNA_CSS . 'frontend-single.' . $suffix . 'css', array(), VICATNA_VERSION );
		wp_register_script( 'vicatna-single', VICATNA_JS . 'frontend-single.' . $suffix . 'js', array( 'jquery' ), VICATNA_VERSION, true );
		$args = array(
			'wc_ajax_url' => WC_AJAX::get_endpoint( "%%endpoint%%" ),
			'nonce'       => wp_create_nonce('vicatna_nonce'),
		);
		ob_start();
		$i18n_make_a_selection_text = esc_html__( 'Please select some product options before adding this product to your cart.', 'catna-woo-name-your-price-and-offers' );
		// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
		wc_print_notice( apply_filters( 'vicatna-i18n_make_a_selection_text', $i18n_make_a_selection_text ), 'error' );
		$args['i18n_make_a_selection_text'] = ob_get_clean();
		ob_start();
		$i18n_unavailable_text = esc_html__( 'Sorry, this product is unavailable. Please choose a different combination.', 'catna-woo-name-your-price-and-offers' );
		// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
		wc_print_notice( apply_filters( 'vicatna-i18n_unavailable_text', $i18n_unavailable_text ), 'error' );
		$args['i18n_unavailable_text'] = ob_get_clean();
		ob_start();
		$i18n_empty_qty = esc_html__( 'Please enter your quantity to buy this product.', 'catna-woo-name-your-price-and-offers' );
		// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
		wc_print_notice( apply_filters( 'vicatna-i18n_empty_qty', $i18n_empty_qty ), 'error' );
		$args['i18n_empty_qty'] = ob_get_clean();
		if ( self::$so_enable ) {
			if ( ! self::$settings->get_params( 'so_atc_normal' ) ) {
				$args['so_single_atc_label'] = self::$settings->get_params( 'so_single_atc_label' );
			}
			ob_start();
			wc_print_notice( self::$settings->get_params( 'so_mess_empty' ), 'error' );
			$args['so_mess_empty'] = ob_get_clean();
			$args['so_mess_wait']  = self::$settings->get_params( 'so_mess_wait' );
		}
		if ( self::$nyp_enable ) {
			$args['nyp_free_purchase'] = self::$settings->get_params( 'nyp_free_purchase' ) ?: '';
			ob_start();
			wc_print_notice( self::$settings->get_params( 'nyp_mess_empty' ), 'error' );
			$args['nyp_mess_empty'] = ob_get_clean();
		}
		wp_localize_script( 'vicatna-single', 'vicatna_single', $args );
		if ( ( self::$so_enable || self::$nyp_enable ) && $css = $this->get_inline_css() ) {
			wp_add_inline_style( 'vicatna-single', $css );
		}
		if ( self::$settings->get_params( 'nyp_quickview' ) || ( is_single() && is_product() ) ) {
			wp_enqueue_style( 'vicatna-single' );
			wp_enqueue_script( 'vicatna-single' );
			if ( self::$so_enable && self::$settings->get_params( 'so_popup' ) ) {
				add_action( 'wp_footer', array( $this, 'html_offer_popup' ) );
			}
		}
	}
	public static function enqueue_scripts() {
		if ( ! wp_style_is( 'vicatna-single' ) ) {
			wp_enqueue_style( 'vicatna-single' );
		}
		if ( ! wp_script_is( 'vicatna-single' ) ) {
			wp_enqueue_script( 'vicatna-single' );
		}
	}
	public function get_inline_css() {
		$css = '';
		if ( self::$so_enable ) {
			if ( self::$settings->get_params( 'so_popup' ) ) {
				$css .= $this->add_inline_style(
					array( '.vicatna-popup-wrap-so .vicatna-popup-form-wrap .vicatna-popup-form-header-wrap' ),
					array( 'so_popup_title_color', 'so_popup_title_font_size' ),
					array( 'color', 'font-size' ),
					array( '', 'px' )
				);
				$css .= $this->add_inline_style(
					array( '.vicatna-popup-wrap-so .vicatna-popup-form-wrap .vicatna-popup-form-bt' ),
					array( 'so_popup_bt_border_width', 'so_popup_bt_border_radius', 'so_popup_bt_border_color', 'so_popup_bt_bg_color', 'so_popup_bt_color', 'so_popup_bt_font_size' ),
					array( 'border-width', 'border-radius', 'border-color', 'background', 'color', 'font-size' ),
					array( 'px !important', 'px', '', '', '', 'px' )
				);
				$css .= $this->add_inline_style(
					array( '.vicatna-popup-wrap-so .vicatna-popup-form-content-title' ),
					array( 'so_input_title_color', 'so_input_title_font_size' ),
					array( 'color', 'font-size' ),
					array( '', 'px' )
				);
				$css .= $this->add_inline_style(
					array( '.vicatna-popup-wrap-so .vicatna_so_value, .vicatna-popup-wrap-so .vicatna-value-symbol' ),
					array( 'so_input_border_width', 'so_input_border_radius', 'so_input_border_color', 'so_input_bg_color', 'so_input_color', 'so_input_font_size' ),
					array( 'border-width', 'border-radius', 'border-color', 'background', 'color', 'font-size' ),
					array( 'px !important', 'px', '', '', '', 'px' )
				);
			} else {
				$css .= $this->add_inline_style(
					array( '.vicatna-so-wrap .vicatna_so_value', '.vicatna-so-wrap .vicatna-before-wrap' ),
					array( 'so_input_title_color', 'so_input_title_font_size' ),
					array( 'color', 'font-size' ),
					array( '', 'px' )
				);
				$css .= $this->add_inline_style(
					array( '.vicatna-so-wrap .vicatna_so_value, .vicatna-so-wrap .vicatna-value-symbol' ),
					array( 'so_input_border_width', 'so_input_border_radius', 'so_input_border_color', 'so_input_bg_color', 'so_input_color', 'so_input_font_size' ),
					array( 'border-width', 'border-radius', 'border-color', 'background', 'color', 'font-size' ),
					array( 'px !important', 'px', '', '', '', 'px' )
				);
			}
		}
		if ( self::$nyp_enable ) {
			$css .= $this->add_inline_style(
				array( '.vicatna-nyp-wrap' ),
				array( 'nyp_single_content_color', 'nyp_single_content_font_size' ),
				array( 'color', 'font-size' ),
				array( '', 'px' )
			);
			$css .= $this->add_inline_style(
				array( '.vicatna-nyp-wrap .vicatna_nyp_value, .vicatna-nyp-wrap .vicatna-value-symbol' ),
				array(
					'nyp_single_content_input_border_width',
					'nyp_single_content_input_border_radius',
					'nyp_single_content_input_border_color',
					'nyp_single_content_input_bg_color',
					'nyp_single_content_input_color',
					'nyp_single_content_input_font_size'
				),
				array( 'border-width', 'border-radius', 'border-color', 'background', 'color', 'font-size' ),
				array( 'px !important', 'px', '', '', '', 'px' )
			);
		}
		return $css;
	}
	public function add_inline_style( $element, $name, $style, $suffix = '' ) {
		if ( ! $element || ! is_array( $element ) ) {
			return '';
		}
		$settings = self::$settings ?? VICATNA_DATA::get_instance();
		$element  = implode( ',', $element );
		$return   = $element . '{';
		if ( is_array( $name ) && count( $name ) ) {
			foreach ( $name as $key => $value ) {
				$get_value  = $settings->get_params( $value );
				$get_suffix = $suffix[ $key ] ?? '';
				$return     .= $style[ $key ] . ':' . $get_value . $get_suffix . ';';
			}
		}
		$return .= '}';
		return $return;
	}
	public function vicatna_woocommerce_available_variation( $data, $parent, $variation ) {
		$product_id = $variation->get_id() ?? $variation->ID;
		$rule       = self::get_rule( $product_id, $variation ) ?? array();
		if ( empty( $rule ) || ! isset( $rule['type'] ) || ! in_array( strval( $rule['type'] ), [ '0', '1' ], true ) || empty( $rule['prices'] ) ) {
			return $data;
		}
		if ( self::$nyp_enable && '0' === $rule['type'] ) {
			ob_start();
			wc_get_template( 'name-your-price.php',
				array(
					'product_id'   => $product_id,
					'product_type' => $variation->get_type() ?? '',
					'product'      => $variation,
					'rule'         => $rule['prices'],
					'settings'     => self::$settings,
				),
				'catna-woo-name-your-price-and-offers' . DIRECTORY_SEPARATOR,
				VICATNA_TEMPLATES );
			$data['vicatna_nyp_html'] = ob_get_clean();
			$data['vicatna_nyp_rule'] = $rule;
		} elseif ( self::$so_enable && '1' === $rule['type'] ) {
			ob_start();
			wc_get_template( 'smart-offers.php',
				array(
					'product_id'   => $product_id,
					'product_type' => $variation->get_type() ?? '',
					'product'      => $variation,
					'rule'         => $rule['prices'],
					'settings'     => self::$settings,
				),
				'catna-woo-name-your-price-and-offers' . DIRECTORY_SEPARATOR,
				VICATNA_TEMPLATES );
			$data['vicatna_so_html'] = ob_get_clean();
			$data['vicatna_so_rule'] = $rule;
		}
		return $data;
	}
	public function vicatna_woocommerce_product_add_to_cart_text( $text, $product ) {
		if ( ! $product ) {
			global $product;
		}
		$product_id = $product->get_id() ?? $product->ID;
		$rule       = self::get_rule( $product_id, $product ) ?? array();
		if ( empty( $rule ) || ! isset( $rule['type'] ) || ! in_array( strval( $rule['type'] ), [ '0', '1' ], true ) || ( isset( $rule['prices'] ) && empty( $rule['prices'] ) ) ) {
			return $text;
		}
		if ( self::$so_enable && '1' === $rule['type'] && ( empty( $cart_data = self::so_check_in_cart( $product_id ) ) || ! isset( $cart_data['price'] ) || ! isset( $cart_data['min_qty'] ) ) && ! self::$settings->get_params( 'so_atc_normal' ) && ( $so_loop_atc_label = self::$settings->get_params( 'so_loop_atc_label' ) ) ) {
			return $so_loop_atc_label;
		}
		return $text;
	}
	public function vicatna_woocommerce_loop_add_to_cart_link( $url, $product ) {
		if ( ! $product ) {
			global $product;
		}
		$product_id = $product->get_id() ?? $product->ID;
		$rule       = self::get_rule( $product_id, $product ) ?? array();
		if ( empty( $rule ) || ! isset( $rule['type'] ) || ! in_array( strval( $rule['type'] ), [ '0', 'both', '1' ], true ) || ( isset( $rule['prices'] ) && empty( $rule['prices'] ) ) ) {
			return $url;
		}
		if ( self::$nyp_enable && '0' === $rule['type'] ) {
			$url = str_replace( 'ajax_add_to_cart', '', $url );
			return $url;
		}
		if ( self::$so_enable && ( 'both' === $rule['type'] || '1' === $rule['type'] ) && ! in_array( $product->get_type() ?? '', apply_filters( 'vicatna_so_loop_exclude_pd_types', [ 'variable' ] ), true ) ) {
			if ( ! empty( $cart_data = self::so_check_in_cart( $product_id ) ) && isset( $cart_data['price'], $cart_data['min_qty'] ) ) {
				$url = str_replace( 'class="', 'data-vicatna_so_value="' . $cart_data['price'] . '" data-vicatna_so_qty_min="' . $cart_data['min_qty'] . '" class="', $url );
			} elseif ( ! self::$settings->get_params( 'so_atc_normal' ) ) {
				$url = str_replace( 'ajax_add_to_cart', '', $url );
			}
			return $url;
		}
		return $url;
	}
	public function vicatna_woocommerce_product_add_to_cart_url( $url, $product ) {
		if ( ! $product ) {
			global $product;
		}
		$product_id = $product->get_id() ?? $product->ID;
		$rule       = self::get_rule( $product_id, $product ) ?? array();
		if ( empty( $rule ) || ! isset( $rule['type'] ) || ! in_array( strval( $rule['type'] ), [ '0', 'both', '1' ], true ) || ( isset( $rule['prices'] ) && empty( $rule['prices'] ) ) ) {
			return $url;
		}
		if ( self::$nyp_enable && ( 'both' === $rule['type'] || '0' === $rule['type'] ) ) {
			return get_permalink( $product->get_parent_id() ? $product->get_parent_id() : $product->get_id() );
		}
		if ( self::$so_enable && ( 'both' === $rule['type'] || ( '1' === $rule['type'] && ! self::$settings->get_params( 'so_atc_normal' ) ) ) ) {
			return get_permalink( $product->get_parent_id() ? $product->get_parent_id() : $product->get_id() );
		}
		return $url;
	}
	public function vicatna_woocommerce_quantity_input_args( $args, $product ) {
		if ( ! $args || ! $product ) {
			return $args;
		}
		if ( ! empty( $product->vicatna_cart_item ) && ! empty( $product->vicatna_so['min_qty'] ) ) {
			$args['min_value'] = apply_filters( 'vicatna_so_get_min_quantity', $product->vicatna_so['min_qty'], $product );
		}
		return $args;
	}
	public static function vicatna_woocommerce_product_single_add_to_cart_text( $text, $product ) {
		if ( ! $product ) {
			global $product;
		}
		if ( empty( $product ) || ! is_a( $product, 'WC_Product' ) || ! in_array( $product->get_type() ?? '', [ 'simple' ], true ) ) {
			return $text;
		}
		$product_id = $product->get_id() ?? $product->ID;
		$rule       = self::get_rule( $product_id, $product ) ?? array();
		if ( empty( $rule ) || ! isset( $rule['type'] ) || '1' != $rule['type'] || ( isset( $rule['prices'] ) && empty( $rule['prices'] ) ) ) {
			return $text;
		}
		if ( ! self::$settings->get_params( 'so_atc_normal' ) && ( empty( $cart_data = self::so_check_in_cart( $product_id ) ) || ! isset( $cart_data['price'] ) || ! isset( $cart_data['min_qty'] ) ) && ( $so_single_atc_label = self::$settings->get_params( 'so_single_atc_label' ) ) ) {
			return $so_single_atc_label;
		}
		return $text;
	}
	public function vicatna_nyp_woocommerce_is_purchasable( $purchasable, $product ) {
		$product_id = $product->get_id() ?? $product->ID;
		$rule       = self::get_rule( $product_id, $product ) ?? array();
		if ( empty( $rule ) || ! isset( $rule['type'] ) || ! in_array( $rule['type'], [ 0 ], true ) || ( isset( $rule['prices'] ) && empty( $rule['prices'] ) ) ) {
			return $purchasable;
		}
		$purchasable = apply_filters( 'vicatna_nyp_woocommerce_is_purchasable', true, $product );
		return $purchasable;
	}
	public function vicatna_nyp_woocommerce_get_price_html( $price_html, $product ) {
		if ( ! $price_html || ! $product ) {
			return $price_html;
		}
		if ( ! did_action( 'woocommerce_cart_loaded_from_session' ) ) {
			return $price_html;
		}
		if ( ! empty( $product->vicatna_cart_item ) ) {
			return $price_html;
		}
		$product_id = $product->get_id() ?? $product->ID;
		if ( isset( self::$cache['price_html'][ $product_id ] ) ) {
			return self::$cache['price_html'][ $product_id ];
		}
		$rule = self::get_rule( $product_id, $product ) ?? array();
		if ( empty( $rule ) || ! isset( $rule['type'] ) || ! in_array( $rule['type'], [ 0 ], true ) || ( isset( $rule['prices'] ) && empty( $rule['prices'] ) ) ) {
			return $price_html;
		}
		$price_html = apply_filters( 'vicatna_nyp_woocommerce_get_price_html', '', $product );
		return self::$cache['price_html'][ $product_id ] = $price_html;
	}
	public function vicatna_nyp_woocommerce_product_is_on_sale( $on_sale, $product ) {
		$product_id = $product->get_id() ?? $product->ID;
		$rule       = self::get_rule( $product_id, $product ) ?? array();
		if ( empty( $rule ) || ! isset( $rule['type'] ) || ! in_array( $rule['type'], [ 0 ], true ) || ( isset( $rule['prices'] ) && empty( $rule['prices'] ) ) ) {
			return $on_sale;
		}
		$on_sale = apply_filters( 'vicatna_nyp_woocommerce_product_is_on_sale', false, $product );
		return $on_sale;
	}

	public static function so_check_in_cart( $product_id ) {
		if ( ! $product_id ) {
			return false;
		}
		if ( isset( self::$cache['in_cart'][ $product_id ] ) ) {
			return self::$cache['in_cart'][ $product_id ];
		}
		if ( WC()->cart && ! WC()->cart->is_empty() ) {
			foreach ( WC()->cart->get_cart() as $key => $cart_item ) {
				if ( isset( $cart_item['vicatna_so']['price'], $cart_item['vicatna_so']['min_qty'] ) && ( $product_id == $cart_item['product_id'] || $product_id == $cart_item['variation_id'] ) ) {
					$result = $cart_item['vicatna_so'];
					break;
				}
			}
		}
		return self::$cache['in_cart'][ $product_id ] = $result ?? array();
	}
	public static function get_rule( $product_id, $product ) {
		if ( isset( self::$cache['rule'][ $product_id ] ) ) {
			$rule = self::$cache['rule'][ $product_id ];
		} else {
			if ( ! $product_id || ! self::may_be_apply_to_product( $product ) || isset( self::$cache[ 'doing-' . $product_id ] ) ) {
				return array();
			}
			self::$cache[ 'doing-' . $product_id ] = true;
			self::$rule                            = self::$rule ?? VICATNA_Frontend_Rule::get_instance( self::$settings );
			$rule                                  = self::$rule::get_rule( $product_id, $product ) ?? array();
			$rule                                  = $rule ?: array();
			self::$cache['rule'][ $product_id ]    = $rule;
			unset( self::$cache[ 'doing-' . $product_id ] );
		}
		return $rule ?? array();
	}
	public static function may_be_apply_to_product( $product ) {
		if ( empty( $product ) || ! is_a( $product, 'WC_Product' ) || in_array( $product_type = $product->get_type() ?? '', apply_filters( 'vicatna_exclude_product_types', [ 'grouped', 'external' ] ), true ) ) {
			return false;
		}
		return true;
	}
}