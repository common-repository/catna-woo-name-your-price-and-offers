<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VICATNA_DATA {
	private $params, $prefix, $default;
	protected static $instance = null;

	/**
	 * VICATNA_DATA constructor.
	 * Init setting
	 */
	public function __construct() {
		$this->prefix = 'vicatna-';
		global $vicatna_settings;
		if ( ! $vicatna_settings ) {
			$vicatna_settings = get_option( 'vicatna_params', array() );
		}
		$name_your_price = array(
			'nyp_enable'                             => 1,
			'nyp_quickview'                          => 0,
			'nyp_free_purchase'                      => 0,
			'nyp_hide_pd_price'                      => 1,
			'nyp_input_step'                         => 1,
			'nyp_mess_empty'                         => 'Please enter your suggested price to buy this product',
			'nyp_mess_low'                           => 'Your suggested price is too low for us',
			'nyp_mess_high'                          => 'Your suggested price is too high for us',
			'nyp_single_position'                    => 'before_atc',
			'nyp_single_content'                     => '{input_price}',
			'nyp_single_content_color'               => '',
			'nyp_single_content_font_size'           => '',
			'nyp_single_content_input'               => 'Name Your Price( {currency_symbol})',
			'nyp_single_content_input_currency'      => 1,
			'nyp_single_content_input_border_width'  => 1,
			'nyp_single_content_input_border_radius' => 0,
			'nyp_single_content_input_border_color'  => '#ddd',
			'nyp_single_content_input_bg_color'      => '#fff',
			'nyp_single_content_input_color'         => '#222',
			'nyp_single_content_input_font_size'     => 16,
		);
		$smart_offers    = array(
			'so_enable'                 => 1,
			'so_atc_normal'             => 1,
			'so_offer_bt_label'         => 'Make your offer now',
			'so_offer_bt_position'      => 'before_atc',
			'so_single_atc_label'       => 'Make your offer now',
			'so_loop_atc_label'         => 'Make your offer now',
			'so_mess_empty'             => 'Please enter your suggested price for this product',
			'so_mess_low'               => 'Your suggested price is too low for us.',
			'so_mess_high'              => 'Your suggested price is more than we could take. Please buy at our original price.',
			'so_mess_wait'              => 'Please wait a moment...',
			'so_mess_success'           => 'Your suggested price has been accepted',
			'so_popup'                  => 1,
			'so_popup_title'            => 'Make us an offer which we can not refuse',
			'so_popup_title_color'      => '#222',
			'so_popup_title_font_size'  => 18,
			'so_input_title'            => 'Offer price ',
			'so_input_title_color'      => '#222',
			'so_input_title_font_size'  => 16,
			'so_input'                  => '{currency_symbol}',
			'so_input_currency'         => 1,
			'so_input_border_width'     => 1,
			'so_input_border_radius'    => 0,
			'so_input_border_color'     => '#ddd',
			'so_input_bg_color'         => '#fff',
			'so_input_color'            => '#222',
			'so_input_font_size'        => '',
			'so_popup_bt_label'         => 'Offer',
			'so_popup_bt_border_width'  => 0,
			'so_popup_bt_border_radius' => 0,
			'so_popup_bt_border_color'  => '',
			'so_popup_bt_bg_color'      => '',
			'so_popup_bt_color'         => '',
			'so_popup_bt_font_size'     => '',
		);
		$this->default   = array_merge( $name_your_price, $smart_offers );
		$this->params    = apply_filters( 'vicatna_params', wp_parse_args( $vicatna_settings, $this->default ) );
	}

	public static function get_instance( $new = false ) {
		if ( $new || null === self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function get_params( $name = "" ) {
		if ( ! $name ) {
			return $this->params;
		}
		// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
		return apply_filters( 'vicatna_params-' . $name, $this->params[ $name ] ?? false );
	}

	public function get_default( $name = "" ) {
		if ( ! $name ) {
			return $this->params;
		}
		// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
		return apply_filters( 'vicatna_params_default-' . $name, $this->params[ $name ] ?? false );
	}

	public function set( $name ) {
		if ( is_array( $name ) ) {
			return implode( ' ', array_map( array( $this, 'set' ), $name ) );
		} else {
			return esc_attr( $this->prefix . $name );
		}
	}

	public static function extend_post_allowed_html() {
		return array_merge( wp_kses_allowed_html( 'post' ), array(
				'input' => array(
					'type'         => 1,
					'id'           => 1,
					'min'          => 1,
					'max'          => 1,
					'step'         => 1,
					'name'         => 1,
					'class'        => 1,
					'placeholder'  => 1,
					'autocomplete' => 1,
					'style'        => 1,
					'value'        => 1,
					'data-*'       => 1,
					'size'         => 1,
					'title'        => 1,
				),
				'form'  => array(
					'type'   => 1,
					'id'     => 1,
					'name'   => 1,
					'class'  => 1,
					'style'  => 1,
					'method' => 1,
					'action' => 1,
					'data-*' => 1,
				),
				'style' => array(
					'id'    => 1,
					'class' => 1,
					'type'  => 1,
				),
			)
		);
	}
}