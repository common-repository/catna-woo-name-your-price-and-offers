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
$loop         = $loop ?? false;
$wrap_class   = array(
	'vicatna-wrap vicatna-nyp-wrap',
	'vicatna-nyp-wrap-' . $product_type,
	'vicatna-nyp-wrap-' . $product_id,
);
$wrap_class[] = $loop ? 'vicatna-loop-wrap' : '';
$wrap_class   = trim( implode( ' ', $wrap_class ) );
switch ( $product_type ) {
	case 'variable':
		echo sprintf( '<div class="%s"></div>', esc_attr( $wrap_class ) );
		break;
	default:
		if ( $loop && 'popup' === VICATNA_Frontend_Product::$nyp_loop_style ) {
			$wrap_class  .= ' vicatna-popup-wrap-non-ajax vicatna-disabled';
			$content     = $settings->get_params( 'nyp_loop_content' );
			$content_arg = explode( '{input_price}', $content );
			if ( count( $content_arg ) < 2 ) {
				break;
			}
			?>
            <div class="<?php echo esc_attr( $wrap_class ); ?>" data-form_class="vicatna-popup-form vicatna-popup-form-nyp vicatna-popup-form-<?php echo esc_attr( $product_type ); ?>">
				<?php do_action( 'vicatna_nyp_get_popup_html_input', $product, $rule, $settings ); ?>
            </div>
			<?php
			break;
		}
		$content     = $loop ? $settings->get_params( 'nyp_loop_content' ) : $settings->get_params( 'nyp_single_content' );
		$content_arg = explode( '{input_price}', $content );
		if ( count( $content_arg ) < 2 ) {
			break;
		}
		$before_input  = $content_arg[0] ?? '';
		$before_input  = $before_input && ' ' !== $before_input ? sprintf( '<div class="vicatna-before-wrap">%s</div>', wp_kses_post( wp_unslash( $before_input ) ) ) : '';
		$after_input   = $content_arg[1] ?? '';
		$after_input   = $after_input && ' ' !== $after_input ? sprintf( '<div class="vicatna-after-wrap">%s</div>', wp_kses_post( wp_unslash( $after_input ) ) ) : '';
		$pd_price      = $product->get_price() ?? '';
		$price_decimal = wc_get_price_decimals();
		$price_default = $pd_price ?: '';
		$price_min     = $rule['price_min'] ?? 0;
		$price_max     = $rule['price_max'] ?? '';
		$price_max     = apply_filters( 'vicatna_nyp_check_get_price_max', $price_max ? wc_format_decimal( $price_max, $price_decimal ) : '', $rule, $product );
		if ( ! $price_min && ! $settings->get_params( 'nyp_free_purchase' ) ) {
			$price_min = 1;
			for ( $i = 0; $i < $price_decimal; $i ++ ) {
				$price_min /= 10;
			}
		}
		$price_min         = apply_filters( 'vicatna_nyp_check_get_price_min', wc_format_decimal( $price_min, $price_decimal ), $rule, $product );
		$price_default     = floatval( $price_default ?: 0 ) < floatval( $price_min ) ? '' : wc_format_decimal( $price_default, $price_decimal );
		$input             = sprintf( '<input name="vicatna_nyp_value" class="vicatna-value vicatna_nyp_value" title="%s" type="number"  value="%s" min="%s" max="%s" step="%s" placeholder="%s" data-product_id="%s">',
			apply_filters( 'vicatna_nyp_html_input_title', esc_html__( 'Name Your Price', 'catna-woo-name-your-price-and-offers' ) ),
			esc_attr( $price_default ), esc_attr( $price_min ), esc_attr( $price_max ), esc_attr( $settings->get_params( 'nyp_input_step' ) ),
			esc_attr( $loop ? $settings->get_params( 'nyp_loop_content_input' ) : $settings->get_params( 'nyp_single_content_input' ) ), esc_attr( $product_id ) );
		$currency_enable   = $loop ? $settings->get_params( 'nyp_loop_content_input_currency' ) : $settings->get_params( 'nyp_single_content_input_currency' );
		$currency_position = $currency_enable ? strpos( get_woocommerce_price_format(), '%1' ) : '';
		$symbol_html       = get_woocommerce_currency_symbol();
		if ( $currency_position ) {
			$input = sprintf( '<div class="vicatna-value-wrap vicatna-value-wrap-symbol vicatna-value-wrap-symbol-right">%s<span class="vicatna-value-symbol">%s</span></div>',
				$input, $symbol_html );
		} elseif ( '' !== $currency_position && false !== $currency_position ) {
			$input = sprintf( '<div class="vicatna-value-wrap vicatna-value-wrap-symbol vicatna-value-wrap-symbol-left"><span class="vicatna-value-symbol">%s</span>%s</div>',
				$symbol_html, $input );
		}
		$input = str_replace( '{currency_symbol}', $symbol_html, $input );
		$input = str_replace( '{currency_code}', get_woocommerce_currency(), $input );
		$input = apply_filters( 'vicatna_nyp_html_input', $input, $product, $rule, $settings );
		$html  = $before_input . $input . $after_input;
		$html  = str_replace( '{product_name}', $product->get_name(), $html );
		$html  = str_replace( '{min_price}', apply_filters( 'vicatna_nyp_html_min_price', wc_price( $price_min ), $price_min, $product ), $html );
		$html  = str_replace( '{max_price}', apply_filters( 'vicatna_nyp_html_max_price', is_numeric( $price_max ) ? wc_price( $price_max ) : esc_html__( 'not limited', 'catna-woo-name-your-price-and-offers' ), $price_max, $product ), $html );
		?>
        <div class="<?php echo esc_attr( $wrap_class ); ?>">
	        <?php wp_nonce_field( 'vicatna_cart_nonce', '_vicatna_cart_nonce' ); ?>
			<?php echo wp_kses( $html, VICATNA_DATA::extend_post_allowed_html() ); ?>
        </div>
	<?php
}
?>