<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VICATNA_Frontend_Frontend {
	public static function add_ajax_events() {
		$ajax_events = array(
			'vicatna_so_get_popup'    => true,
			'vicatna_nyp_check_price' => true,
			'vicatna_so_check_price'  => true,
		);
		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_woocommerce_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_woocommerce_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}
			// WC AJAX can be used for frontend ajax requests.
			add_action( 'wc_ajax_' . $ajax_event, array( __CLASS__, $ajax_event ) );
		}
	}

	public static function vicatna_so_check_price() {
		$result = array(
			'status'  => 'error',
			'message' => '',
		);
		if ( ! check_ajax_referer( 'vicatna_nonce', 'vicatna_nonce', false ) ) {
			$result['message'] = 'Invalid nonce';
			wp_send_json( $result );
		}
		$product_id = isset( $_POST['product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : '';
		if ( $product_id && $product = wc_get_product( $product_id ) ) {
			$price = isset( $_POST['price'] ) ? sanitize_text_field( wp_unslash( $_POST['price'] ) ) : '';
			$price = is_numeric( $price ) ? floatval( $price ) : 0;
			$qty   = isset( $_POST['quantity'] ) ? sanitize_text_field( wp_unslash( $_POST['quantity'] ) ) : '';
			$qty   = is_numeric( $qty ) ? floatval( $qty ) : 1;
			$rule  = VICATNA_Frontend_Product::get_rule( $product_id, $product ) ?? array();
			if ( empty( $rule ) || ! isset( $rule['type'] ) || '1' !== $rule['type'] || empty( $rule['prices'] ) || ! is_array( $rule['prices'] ) ) {
				wp_send_json( $result );
			}
			$price_max = floatval( apply_filters( 'vicatna_so_check_get_price_max', $product->get_price(), $rule, $product ) );
			if ( $price > $price_max ) {
				$settings = VICATNA_DATA::get_instance();
				$message  = $settings->get_params( 'so_mess_high' );
			}
			if ( ! isset( $message ) || strpos( $message, '{min_price}' ) !== false ) {
				$rule_t           = self::so_get_price_check( $rule['prices'], $qty );
				$result['rule_t'] = $rule_t;
				if ( empty( $rule_t ) || ! isset( $rule_t['min'] ) || ! isset( $rule_t['type'] ) || '' === $rule_t['min'] || '' === $rule_t['type'] ) {
					wp_send_json( $result );
				}
				if ( $rule_t['type'] ) {
					$price_min = wc_format_decimal( $price_max * ( 1 - floatval( $rule_t['min'] ) / 100 ), wc_get_price_decimals() );
				} else {
					$price_min = wc_format_decimal( $price_max - floatval( $rule_t['min'] ), wc_get_price_decimals() );
				}
				$price_min = apply_filters( 'vicatna_so_check_get_price_min', floatval( $price_min ), $rule_t, $rule, $product );
				$settings  = $settings ?? VICATNA_DATA::get_instance();
				if ( ! isset( $message ) ) {
					if ( $price_min > $price ) {
						$message = $settings->get_params( 'so_mess_low' );
					} else {
						$message          = $settings->get_params( 'so_mess_success' );
						$result['status'] = 'success';
					}
				}
				$message = str_replace(
					array( '{product_name}', '{suggested_price}', '{product_price}', '{min_price}' ),
					array(
						$product->get_name(),
						wc_price( $price ),
						wc_price( $price_max ),
						apply_filters( 'vicatna_so_html_min_price', wc_price( $price_min ), $price_min, $product )
					),
					$message
				);
				ob_start();
				wc_print_notice( $message, 'error' === $result['status'] ? 'error' : 'success' );
				$result['message'] = ob_get_clean();
			} else {
				$message = str_replace(
					array( '{product_name}', '{suggested_price}', '{product_price}' ),
					array( $product->get_name(), wc_price( $price ), wc_price( $price_max ) ),
					$message
				);
				ob_start();
				wc_print_notice( $message, 'error' === $result['status'] ? 'error' : 'success' );
				$result['message'] = ob_get_clean();
			}
		}
		wp_send_json( $result );
	}

	public static function so_get_price_check( $rule = array(), $qty = null ) {
		if ( empty( $rule ) || ! $qty ) {
			return false;
		}
		if ( empty( $rule['so_qty'] ) ) {
			return array(
				'min'  => $rule['so_min'] ?? '',
				'type' => $rule['so_type'] ?? ''
			);
		}
		$from = $rule['so_qty_from'] ?? '';
		if ( empty( $from ) || ! is_array( $from ) ) {
			return false;
		}
		$to     = $rule['so_qty_to'] ?? array();
		$min    = $rule['so_qty_min'] ?? array();
		$type   = $rule['so_qty_type'] ?? array();
		$qty    = floatval( $qty );
		$result = array();
		foreach ( $from as $i => $item ) {
			if ( $qty < floatval( $item ) ) {
				continue;
			}
			$to_t = floatval( $to[ $i ] ?? 0 );
			if ( $to_t && $to_t < $qty ) {
				continue;
			}
			$result['min']  = $min[ $i ] ?? '';
			$result['type'] = $type[ $i ] ?? '';
			break;
		}

		return $result;
	}

	public static function vicatna_nyp_check_price() {
		$result = array(
			'status'  => 'error',
			'message' => ''
		);
		if ( ! check_ajax_referer( 'vicatna_nonce', 'vicatna_nonce', false ) ) {
			$result['message'] = esc_html__( 'Invalid nonce', 'catna-woo-name-your-price-and-offers' );
			wp_send_json( $result );
		}
		$product_id = isset( $_POST['product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : '';
		if ( $product_id && $product = wc_get_product( $product_id ) ) {
			$price     = isset( $_POST['price'] ) ? sanitize_text_field( wp_unslash( $_POST['price'] ) ) : '';
			$price_min = isset( $_POST['price_min'] ) ? sanitize_text_field( wp_unslash( $_POST['price_min'] ) ) : 0;
			$price_max = isset( $_POST['price_max'] ) ? sanitize_text_field( wp_unslash( $_POST['price_max'] ) ) : '';
			$price     = $price ? floatval( $price ) : 0;
			$price_min = $price_min ? floatval( $price_min ) : 0;
			$price_max = $price_max ? floatval( $price_max ) : '';
			if ( $price_min && $price_min > $price ) {
				$error = 0;
			}
			if ( $price_max && $price_max < $price ) {
				$error = 1;
			}
			if ( isset( $error ) ) {
				$settings = VICATNA_DATA::get_instance();
				$message  = $error ? $settings->get_params( 'nyp_mess_high' ) : $settings->get_params( 'nyp_mess_low' );
				$message  = str_replace(
					array( '{product_name}', '{suggested_price}', '{min_price}', '{max_price}' ),
					array(
						$product->get_name(),
						wc_price( $price ),
						apply_filters( 'vicatna_nyp_html_min_price', wc_price( $price_min ), $price_min, $product ),
						apply_filters( 'vicatna_nyp_html_max_price', is_numeric( $price_max ) ? wc_price( $price_max ) : esc_html__( 'not limited', 'catna-woo-name-your-price-and-offers' ), $price_max, $product ),
					),
					$message
				);
				ob_start();
				wc_print_notice( $message, 'error' );
				$result['message'] = ob_get_clean();
			} else {
				$result['status'] = 'success';
			}
		}
		wp_send_json( $result );
	}

	public static function vicatna_so_get_popup() {
		$result = array(
			'status' => 'error',
			'html'   => ''
		);
		if ( ! check_ajax_referer( 'vicatna_nonce', 'vicatna_nonce', false ) ) {
			$result['message'] = 'Invalid nonce';
			wp_send_json( $result );
		}
		$product_id = isset( $_POST['product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : '';
		if ( $product_id && $product_t = wc_get_product( $product_id ) ) {
			global $product;
			$return_product = $product;
			$product        = $product_t;
			$rule           = VICATNA_Frontend_Product::get_rule( $product_id, $product ) ?? array();
			if ( empty( $rule ) || ! isset( $rule['type'] ) || '1' !== $rule['type'] || empty( $rule['prices'] ) ) {
				wp_send_json( $result );
			}
			$html    = wc_get_template_html( 'smart-offers-popup.php',
				array(
					'product_id'   => $product_id,
					'product_type' => $product->get_type() ?? '',
					'product'      => $product,
					'rule'         => $rule['prices'] ?? '',
					'settings'     => VICATNA_DATA::get_instance(),
				),
				'catna-woo-name-your-price-and-offers' . DIRECTORY_SEPARATOR,
				VICATNA_TEMPLATES );
			$product = $return_product;
			if ( $html ) {
				$result['status'] = 'success';
				$result['html']   = $html;
			}
		}
		wp_send_json( $result );
	}

	public static function vicatna_wp_kses_allowed_html( $allowed, $context ) {
		if ( is_array( $context ) ) {
			return $allowed;
		}
		if ( 'post' === $context ) {
			$allowed['a']['data-*']      = true;
			$allowed['select']['name']   = true;
			$allowed['select']['class']  = true;
			$allowed['select']['id']     = true;
			$allowed['select']['data-*'] = true;
			$allowed['option']['data-*'] = true;
			$allowed['option']['value']  = true;
			$allowed['div']['data-*']    = true;
		}

		return $allowed;
	}
}