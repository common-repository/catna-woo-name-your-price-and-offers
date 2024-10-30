<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VICATNA_Admin_Settings {

	public static function remove_other_script() {
		global $wp_scripts;
		if ( isset( $wp_scripts->registered['jquery-ui-accordion'] ) ) {
			unset( $wp_scripts->registered['jquery-ui-accordion'] );
			wp_dequeue_script( 'jquery-ui-accordion' );
		}
		if ( isset( $wp_scripts->registered['accordion'] ) ) {
			unset( $wp_scripts->registered['accordion'] );
			wp_dequeue_script( 'accordion' );
		}
		$scripts = $wp_scripts->registered;
		foreach ( $scripts as $k => $script ) {
			preg_match( '/^\/wp-/i', $script->src, $result );
			if ( count( array_filter( $result ) ) ) {
				preg_match( '/^(\/wp-content\/plugins|\/wp-content\/themes)/i', $script->src, $result1 );
				if ( count( array_filter( $result1 ) ) ) {
					wp_dequeue_script( $script->handle );
				}
			} else {
				if ( 'query-monitor' != $script->handle ) {
					wp_dequeue_script( $script->handle );
				}
			}
		}
	}
	public static function enqueue_style( $handles = array(), $srcs = array(), $des = array(), $type = 'enqueue' ) {
		if ( empty( $handles ) || empty( $srcs ) ) {
			return;
		}
		$action = 'enqueue' === $type ? 'wp_enqueue_style' : 'wp_register_style';
		foreach ( $handles as $i => $handle ) {
			if ( ! $handle || empty( $srcs[ $i ] ) ) {
				continue;
			}
			$action( $handle, VICATNA_CSS . $srcs[ $i ], ! empty( $des[ $i ] ) ? $des[ $i ] : array(), VICATNA_VERSION );
		}
	}

	public static function enqueue_script( $handles = array(), $srcs = array(), $des = array(), $type = 'enqueue', $in_footer = false ) {
		if ( empty( $handles ) || empty( $srcs ) ) {
			return;
		}
		$action = 'register' === $type ? 'wp_register_script' : 'wp_enqueue_script';
		foreach ( $handles as $i => $handle ) {
			if ( ! $handle || empty( $srcs[ $i ] ) ) {
				continue;
			}
			$action( $handle, VICATNA_JS . $srcs[ $i ], ! empty( $des[ $i ] ) ? $des[ $i ] : array( 'jquery' ), VICATNA_VERSION, $in_footer );
		}
	}
}