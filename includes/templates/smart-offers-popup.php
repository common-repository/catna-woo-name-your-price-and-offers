<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="vicatna-popup-wrap vicatna-popup-wrap-non-ajax vicatna-popup-wrap-so vicatna-disabled" tabindex="1">
    <div class="vicatna-popup-form-wrap">
        <div class="vicatna-popup-form" data-product_id="">
            <div class="vicatna-popup-form-header-wrap"></div>
            <div class="vicatna-popup-form-content-wrap">
                <div class="vicatna-popup-form-content vicatna-popup-form-content-price vicatna-popup-form-content-price-so">
                    <div class="vicatna-popup-form-content-title"></div>
                    <div class="vicatna-popup-form-content-value"></div>
                </div>
            </div>
            <div class="vicatna-popup-form-content-wrap">
                <div class="vicatna-popup-form-content vicatna-popup-form-content-qty vicatna-popup-form-content-price-so">
                    <div class="vicatna-popup-form-content-title">
						<?php echo esc_html( apply_filters( 'vicatna_popup_so_get_qty_title', esc_html__( 'Quantity', 'catna-woo-name-your-price-and-offers' ) ) ); ?>
                    </div>
                    <div class="vicatna-popup-form-content-value"></div>
                </div>
            </div>
            <div class="vicatna-popup-form-footer-wrap">
                <button type="button" class="vicatna-button vicatna-popup-form-bt vicatna-popup-form-bt-ok">
	                <?php
	                echo wp_kses_post( str_replace(
		                array( '{currency_symbol}', '{currency_code}' ),
		                array( get_woocommerce_currency_symbol(), get_woocommerce_currency() ),
		                $settings->get_params( 'so_popup_bt_label' ) ) );
	                ?>
                </button>
            </div>
            <span class="vicatna-popup-cancel">x</span>
        </div>
    </div>
    <div class="vicatna-popup-overlay"></div>
</div>