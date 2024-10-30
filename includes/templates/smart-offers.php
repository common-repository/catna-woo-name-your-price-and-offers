<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! $product_type || ! $product_id || ! $product || ! is_a( $product, 'WC_Product' ) ) {
	return;
}
if ( ! $product->is_in_stock() ) {
	return;
}
$wrap_class           = array(
	'vicatna-wrap vicatna-so-wrap',
	'vicatna-so-wrap-' . $product_type,
	'vicatna-so-wrap-' . $product_id,
);
$wrap_class           = trim( implode( ' ', $wrap_class ) );
switch ( $product_type ) {
	case 'variable':
		echo sprintf( '<div class="%s"></div>', esc_attr( $wrap_class ) );
		break;
	default:
		$html = '';
		$button_class = array( 'vicatna-button vicatna-so-button' );
		if ( ! empty( $cart_data = VICATNA_Frontend_Product::so_check_in_cart( $product_id ) ) && isset( $cart_data['price'], $cart_data['min_qty'] ) ) {
			$button_class[] = 'vicatna-so-single-atc-button-checked vicatna-so-button-checked vicatna-disabled';
			$button_class   = trim( implode( ' ', $button_class ) );
			$button         = sprintf( '<button type="button" class="%s" data-product_id="%s"></button>', esc_attr( $button_class ), esc_attr( $product_id ) );
			$button         = apply_filters( 'vicatna_so_html_button', $button, $settings );
			$html           = sprintf( '<input type="hidden" name="vicatna_so_value" value="%s"><input type="hidden" name="vicatna_so_qty_min" value="%s">',
				esc_attr( $cart_data['price'] ), esc_attr( $cart_data['min_qty'] ) );
			$html           .= $button;
		} else {
			$so_popup          = $settings->get_params( 'so_popup' );
			$button_class[]    = $so_popup ? 'vicatna-so-button-popup' : '';
			$button_class[]    = $settings->get_params( 'so_atc_normal' ) ? ' ' : 'vicatna-disabled';
			$button_class      = trim( implode( ' ', $button_class ) );
			$button            = sprintf( '<button type="button" class="%s" data-product_id="%s">%s</button>',
				esc_attr( $button_class ), esc_attr( $product_id ), esc_attr( $settings->get_params( 'so_offer_bt_label' ) ) );
			$button            = apply_filters( 'vicatna_so_html_button', $button, $settings );
			$product_name      = $product->get_name();
			$symbol_html       = get_woocommerce_currency_symbol();
			$currency_code     = get_woocommerce_currency();
			$currency_enable   = $settings->get_params( 'so_input_currency' );
			$currency_position = $currency_enable ? strpos( get_woocommerce_price_format(), '%1' ) : '';
			$input             = sprintf( '<input type="hidden" class="vicatna-so-qty" value="%s">', esc_attr( $rule['so_qty'] ?? '' ) );
			$input             .= sprintf( '<input class="vicatna-value vicatna_so_value" type="number" value="" placeholder="%s" data-product_id="%s">',
				esc_attr( $settings->get_params( 'so_input' ) ), esc_attr( $product_id ) );
			if ( $currency_position ) {
				$input = sprintf( '<div class="vicatna-value-wrap vicatna-value-wrap-symbol vicatna-value-wrap-symbol-right">%s<span class="vicatna-value-symbol">%s</span></div>',
					$input, $symbol_html );
			} elseif ( '' !== $currency_position && false !== $currency_position ) {
				$input = sprintf( '<div class="vicatna-value-wrap vicatna-value-wrap-symbol vicatna-value-wrap-symbol-left"><span class="vicatna-value-symbol">%s</span>%s</div>',
					$symbol_html, $input );
			}
			if ( ! $so_popup ) {
				$input_title = $settings->get_params( 'so_input_title' );
				$input_title = $input_title && ' ' !== $input_title ? sprintf( '<div class="vicatna-before-wrap">%s</div>', wp_kses_post( wp_unslash( $input_title ) ) ) : '';
				$input       = str_replace( '{currency_symbol}', $symbol_html, $input );
				$input       = str_replace( '{currency_code}', $currency_code, $input );
				$input       = apply_filters( 'vicatna_so_html_input', $input, $product, $rule, $settings );
				$html        = $input_title . $input;
				$html        = str_replace( '{product_name}', $product_name, $html );
			} else {
				$popup_title = $settings->get_params( 'so_popup_title' );
				$input_title = $settings->get_params( 'so_input_title' );
				$popup_title = str_replace( '{product_name}', $product_name, $popup_title );
				$input_title = str_replace( '{product_name}', $product_name, $input_title );
				$input       = str_replace(
					array( '{product_name}', '{currency_symbol}', '{currency_code}' ),
					array( $product_name, $symbol_html, $currency_code ),
					$input
				);
				$input       = apply_filters( 'vicatna_so_html_input', $input, $product, $rule, $settings );
				ob_start();
				do_action( 'woocommerce_before_add_to_cart_quantity' );
				woocommerce_quantity_input(
					array(
						'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
						'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
                        // phpcs:ignore WordPress.Security.NonceVerification.Missing
						'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( sanitize_text_field(wp_unslash( $_POST['quantity'] ) ) ) : $product->get_min_purchase_quantity(), // WPCS: CSRF ok, input var ok.
					)
				);
				do_action( 'woocommerce_after_add_to_cart_quantity' );
				$qty_html  = ob_get_clean();
				$popup_arg = array(
					'popup_title' => $popup_title,
					'input_title' => $input_title,
					'input_html'  => $input,
					'qty_html'    => $qty_html,
					'class_form'  => 'vicatna-popup-form vicatna-popup-form-so vicatna-popup-form-' . $product_type
				);
				$popup_arg = wp_json_encode( $popup_arg );
				$popup_arg = function_exists( 'wc_esc_json' ) ? wc_esc_json( $popup_arg ) : _wp_specialchars( $popup_arg, ENT_QUOTES, 'UTF-8', true );
				$html      = sprintf( '<div class="vicatna-popup-so-value vicatna-disabled" data-val="%s"></div>', $popup_arg );
			}
			if ( ! $settings->get_params( 'so_atc_normal' ) ) {
				remove_filter( 'woocommerce_product_single_add_to_cart_text', array( 'VICATNA_Frontend_Product', 'vicatna_woocommerce_product_single_add_to_cart_text' ), PHP_INT_MAX, 2 );
				$html .= sprintf( '<div class="vicatna-add-to-cart-label-wrap vicatna-disabled">%s</div>', esc_html( $product->single_add_to_cart_text() ) );
				add_filter( 'woocommerce_product_single_add_to_cart_text', array( 'VICATNA_Frontend_Product', 'vicatna_woocommerce_product_single_add_to_cart_text' ), PHP_INT_MAX, 2 );
			}
			$html .= sprintf( '<div class="vicatna-after-wrap">%s</div>', $button );
		}
		?>
        <div class="<?php echo esc_attr( $wrap_class ); ?>" data-product_id="<?php echo esc_attr( $product_id ); ?>">
            <?php wp_nonce_field( 'vicatna_cart_nonce', '_vicatna_cart_nonce' ); ?>
			<?php echo wp_kses( $html, VICATNA_DATA::extend_post_allowed_html() ); ?>
        </div>
	<?php
}
?>