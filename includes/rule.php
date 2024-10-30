<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VICATNA_Frontend_Rule {
	protected static $instance = null;
	protected static $settings, $nyp_enable, $so_enable;
	public function __construct( $settings ) {
		self::$settings   = $settings;
		self::$nyp_enable = self::$settings->get_params( 'nyp_enable' );
		self::$so_enable  = self::$settings->get_params( 'so_enable' );
	}
	public static function get_instance( $settings, $new = false ) {
		if ( $new || null === self::$instance ) {
			self::$instance = new self( $settings );
		}
		return self::$instance;
	}
	public static function get_rule( $product_id, $product ) {
		if ( ! $product_id || ! $product || ! is_a( $product, 'WC_Product' ) ) {
			return false;
		}
		$pd_settings = $rules = array();
		switch ( $product->get_type() ) {
			case 'variable':
				$child_settings = $product->get_meta( 'vicatna_settings_type', true ) ?: array();
				if ( empty( $child_settings ) ) {
					break;
				}
				$child_type = array_unique( array_values( $child_settings ) );
				if ( count( $child_type ) > 1 ) {
					$rules['type'] = 'both';
				}
				$rules['type'] = $rules['type'] ?? $child_type[0];
				$pd_settings   = array( 'type' => $rules['type'] );
				break;
			default:
				$pd_settings = $product->get_meta( 'vicatna_settings', true ) ?? array();
		}
		if ( ! empty( $pd_settings ) && ( $pd_type = $pd_settings['type'] ?? '' ) !== '' ) {
			if ( count( $pd_settings ) === 1 ) {
				return $pd_settings;
			}
			$pd_rule       = array();
			$rules['type'] = $rules['type'] ?? $pd_type;
			if ( $pd_type ) {
				if ( ! self::$so_enable ) {
					return false;
				}
				$pd_rule['so_qty'] = $pd_settings['so_qty'] ?? '';
				if ( $pd_rule['so_qty'] ) {
					$pd_rule['so_qty_from'] = $pd_settings['so_qty_from'] ?? array();
					$pd_rule['so_qty_to']   = $pd_settings['so_qty_to'] ?? array();
					$pd_rule['so_qty_min']  = $pd_settings['so_qty_min'] ?? array();
					$pd_rule['so_qty_type'] = $pd_settings['so_qty_type'] ?? array();
				} else {
					$pd_rule['so_min']  = $pd_settings['so_min'] ?? '';
					$pd_rule['so_type'] = $pd_settings['so_type'] ?? '';
				}
			} else {
				if ( ! self::$nyp_enable ) {
					return false;
				}
				$pd_rule = array(
					'price_min' => $pd_settings['nyp_min'] ?? '',
					'price_max' => $pd_settings['nyp_max'] ?? ''
				);
			}
			$rules['prices'] = $pd_rule;
		}
		return $rules;
	}
}