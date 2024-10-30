<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VICATNA_Admin_Name_Your_Price {
	protected $settings;

	function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 10 );
		add_action( 'admin_init', array( $this, 'save_settings' ), 99 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), PHP_INT_MAX );
	}

	public function admin_menu() {
		$edit_role = apply_filters( 'vicatna_change_role', 'manage_woocommerce' );
		add_menu_page(
			esc_html__( 'Catna', 'catna-woo-name-your-price-and-offers' ),
			esc_html__( 'Catna ', 'catna-woo-name-your-price-and-offers' ),
			$edit_role,
			'catna-woocommerce-name-your-price-and-offers',
			array( $this, 'settings_callback' ),
			'dashicons-money-alt',
			2 );
		add_submenu_page(
			'catna-woocommerce-name-your-price-and-offers',
			esc_html__( 'Name Your Price', 'catna-woo-name-your-price-and-offers' ),
			esc_html__( 'Name Your Price', 'catna-woo-name-your-price-and-offers' ),
			$edit_role,
			'catna-woocommerce-name-your-price-and-offers',
			array( $this, 'settings_callback' )
		);
	}

	public function settings_callback() {
		$this->settings = VICATNA_DATA::get_instance( true );
		?>
        <div class="wrap">
            <h2><?php esc_html_e( 'Name Your Price for WooCommerce', 'catna-woo-name-your-price-and-offers' ); ?></h2>
            <div class="vi-ui raised">
                <form class="vi-ui form" method="post">
					<?php wp_nonce_field( '_vicatna_name_your_price_action', '_vicatna_name_your_price' ); ?>
                    <div class="vi-ui vi-ui-main top tabular attached menu">
                        <a class="item active" data-tab="general">
							<?php esc_html_e( 'General Settings', 'catna-woo-name-your-price-and-offers' ); ?>
                        </a>
                        <a class="item" data-tab="message">
							<?php esc_html_e( 'Message', 'catna-woo-name-your-price-and-offers' ); ?>
                        </a>
                        <a class="item" data-tab="nyp_single">
							<?php esc_html_e( 'Name Your Price On Single Page', 'catna-woo-name-your-price-and-offers' ); ?>
                        </a>
                        <a class="item" data-tab="nyp_pd_list">
							<?php esc_html_e( 'Name Your Price on Product List', 'catna-woo-name-your-price-and-offers' ); ?>
                        </a>
                    </div>
                    <div class="vi-ui bottom attached tab segment active" data-tab="general">
						<?php
						$nyp_enable        = $this->settings->get_params( 'nyp_enable' );
						$nyp_quickview     = $this->settings->get_params( 'nyp_quickview' );
						$nyp_free_purchase = $this->settings->get_params( 'nyp_free_purchase' );
						$nyp_hide_pd_price = $this->settings->get_params( 'nyp_hide_pd_price' );
						$nyp_input_step    = $this->settings->get_params( 'nyp_input_step' ) ?: 1;
						$input_step        = 1;
						for ( $i = 0; $i < wc_get_price_decimals(); $i ++ ) {
							$input_step /= 10;
						}
						?>
                        <table class="form-table">
                            <tr>
                                <th>
                                    <label for=vicatna-nyp_enable-checkbox"><?php esc_html_e( 'Enable', 'catna-woo-name-your-price-and-offers' ); ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui checkbox toggle">
                                        <input type="hidden" name="nyp_enable" id="vicatna-nyp_enable"
                                               value="<?php echo esc_attr( $nyp_enable ); ?>">
                                        <input type="checkbox" id="vicatna-nyp_enable-checkbox"
                                               class="vicatna-nyp_enable-checkbox"<?php checked( $nyp_enable, 1 ); ?>><label></label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for=vicatna-nyp_quickview-checkbox"><?php esc_html_e( 'Quickview', 'catna-woo-name-your-price-and-offers' ); ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui checkbox toggle">
                                        <input type="hidden" name="nyp_quickview" id="vicatna-nyp_quickview"
                                               value="<?php echo esc_attr( $nyp_quickview ); ?>">
                                        <input type="checkbox" id="vicatna-nyp_quickview-checkbox"
                                               class="vicatna-nyp_quickview-checkbox"<?php checked( $nyp_quickview, 1 ); ?>><label></label>
                                    </div>
                                    <p class="description"><?php esc_html_e( 'Enable to use with quickview', 'catna-woo-name-your-price-and-offers' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label><?php esc_html_e( 'Setting for variable products', 'catna-woo-name-your-price-and-offers' ); ?></label>
                                </th>
                                <td>
                                    <a class="vi-ui button" href="https://1.envato.market/kjamOx"
                                       target="_blank"><?php esc_html_e( 'Unlock This Feature', 'catna-woo-name-your-price-and-offers' ); ?> </a>
                                    <p class="description"><?php esc_html_e( 'Enable to set global for all variations by setting for parent product instead of configuring for each individual product', 'catna-woo-name-your-price-and-offers' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for=vicatna-nyp_free_purchase-checkbox"><?php esc_html_e( 'Allow free purchase', 'catna-woo-name-your-price-and-offers' ); ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui checkbox toggle">
                                        <input type="hidden" name="nyp_free_purchase" id="vicatna-nyp_free_purchase"
                                               value="<?php echo esc_attr( $nyp_free_purchase ); ?>">
                                        <input type="checkbox" id="vicatna-nyp_free_purchase-checkbox"
                                               class="vicatna-nyp_free_purchase-checkbox"<?php checked( $nyp_free_purchase, 1 ); ?>><label></label>
                                    </div>
                                    <p class="description"><?php
	                                    // translators: %s is a placeholder for wc_price.
                                        printf( esc_html__( 'Enable to allow to purchase at %s or empty price field', 'catna-woo-name-your-price-and-offers' ), wp_kses_post( wc_price( 0 ) ) );
                                        ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for=vicatna-nyp_hide_pd_price-checkbox"><?php esc_html_e( 'Hidden product price', 'catna-woo-name-your-price-and-offers' ); ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui checkbox toggle">
                                        <input type="hidden" name="nyp_hide_pd_price" id="vicatna-nyp_hide_pd_price"
                                               value="<?php echo esc_attr( $nyp_hide_pd_price ); ?>">
                                        <input type="checkbox" id="vicatna-nyp_hide_pd_price-checkbox"
                                               class="vicatna-nyp_hide_pd_price-checkbox"<?php checked( $nyp_hide_pd_price, 1 ); ?>><label></label>
                                    </div>
                                    <p class="description"><?php esc_html_e( 'Enable to hide product price, using product price as default price of the Name your price. ', 'catna-woo-name-your-price-and-offers' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vicatna-nyp_input_step">
										<?php esc_html_e( 'Input price step', 'catna-woo-name-your-price-and-offers' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="number" class="vicatna-nyp_input_step" id="vicatna-nyp_input_step"
                                           name="nyp_input_step"
                                           step="<?php echo esc_attr( $input_step ); ?>"
                                           min="<?php echo esc_attr( $input_step ); ?>"
                                           value="<?php echo esc_attr( $nyp_input_step ); ?>">
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label><?php esc_html_e( 'Custom CSS', 'catna-woo-name-your-price-and-offers' ); ?></label>
                                </th>
                                <td>
                                    <a class="vi-ui button" href="https://1.envato.market/kjamOx"
                                       target="_blank"><?php esc_html_e( 'Unlock This Feature', 'catna-woo-name-your-price-and-offers' ); ?> </a>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="vi-ui bottom attached tab segment" data-tab="message">
						<?php
						$nyp_mess_empty = $this->settings->get_params( 'nyp_mess_empty' );
						$nyp_mess_low   = $this->settings->get_params( 'nyp_mess_low' );
						$nyp_mess_high  = $this->settings->get_params( 'nyp_mess_high' );
						?>
                        <table class="form-table">
                            <tr class="vicatna-nyp_mess_empty-wrap <?php echo esc_attr( $nyp_free_purchase ? 'vicatna-disabled' : '' ); ?>">
                                <th>
                                    <label for="vicatna-nyp_mess_empty">
										<?php esc_html_e( 'Empty suggested price', 'catna-woo-name-your-price-and-offers' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="text" name="nyp_mess_empty" id="vicatna-nyp_mess_empty"
                                           class="vicatna-nyp_mess_empty"
                                           placeholder="<?php esc_attr_e( 'Please enter your suggested price to buy this product', 'catna-woo-name-your-price-and-offers' ); ?>"
                                           value="<?php echo esc_attr( $nyp_mess_empty ); ?>">
                                    <p class="description"><?php esc_html_e( 'Message when no suggested price entered', 'catna-woo-name-your-price-and-offers' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vicatna-nyp_mess_empty">
										<?php esc_html_e( 'Low suggested price', 'catna-woo-name-your-price-and-offers' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="text" name="nyp_mess_low" id="vicatna-nyp_mess_low"
                                           class="vicatna-nyp_mess_low"
                                           placeholder="<?php esc_attr_e( 'Your suggested price is too low for us', 'catna-woo-name-your-price-and-offers' ); ?>"
                                           value="<?php echo esc_attr( $nyp_mess_low ); ?>">
                                    <p class="description"><?php esc_html_e( 'Message when suggested price lower than the minimum acceptable price.', 'catna-woo-name-your-price-and-offers' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vicatna-nyp_mess_high">
										<?php esc_html_e( 'High suggested price', 'catna-woo-name-your-price-and-offers' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="text" name="nyp_mess_high" id="vicatna-nyp_mess_high"
                                           class="vicatna-nyp_mess_high"
                                           placeholder="<?php esc_attr_e( 'Your suggested price is too high for us', 'catna-woo-name-your-price-and-offers' ); ?>"
                                           value="<?php echo esc_attr( $nyp_mess_high ); ?>">
                                    <p class="description"><?php esc_html_e( 'Message when suggested price higher than the maximum acceptable price', 'catna-woo-name-your-price-and-offers' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label><?php esc_html_e( 'Shortcode', 'catna-woo-name-your-price-and-offers' ); ?></label>
                                </th>
                                <td>
                                    <p class="description">
										<?php printf( '{product_name} - %s', esc_html__( 'Product name', 'catna-woo-name-your-price-and-offers' ) ); ?>
                                    </p>
                                    <p class="description">
										<?php printf( '{min_price} - %s', esc_html__( 'Minimum acceptable price', 'catna-woo-name-your-price-and-offers' ) ); ?>
                                    </p>
                                    <p class="description">
										<?php printf( '{max_price} - %s', esc_html__( 'Maximum acceptable price', 'catna-woo-name-your-price-and-offers' ) ); ?>
                                    </p>
                                    <p class="description">
										<?php printf( '{suggested_price} - %s', esc_html__( 'Suggested price by customer when purchasing product', 'catna-woo-name-your-price-and-offers' ) ); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="vi-ui bottom attached tab segment" data-tab="nyp_single">
						<?php
						$nyp_single_position                    = $this->settings->get_params( 'nyp_single_position' );
						$nyp_single_content                     = $this->settings->get_params( 'nyp_single_content' );
						$nyp_single_content_color               = $this->settings->get_params( 'nyp_single_content_color' );
						$nyp_single_content_font_size           = $this->settings->get_params( 'nyp_single_content_font_size' );
						$nyp_single_content_input               = $this->settings->get_params( 'nyp_single_content_input' );
						$nyp_single_content_input_currency      = $this->settings->get_params( 'nyp_single_content_input_currency' );
						$nyp_single_content_input_border_width  = $this->settings->get_params( 'nyp_single_content_input_border_width' );
						$nyp_single_content_input_border_radius = $this->settings->get_params( 'nyp_single_content_input_border_radius' );
						$nyp_single_content_input_border_color  = $this->settings->get_params( 'nyp_single_content_input_border_color' );
						$nyp_single_content_input_bg_color      = $this->settings->get_params( 'nyp_single_content_input_bg_color' );
						$nyp_single_content_input_color         = $this->settings->get_params( 'nyp_single_content_input_color' );
						$nyp_single_content_input_font_size     = $this->settings->get_params( 'nyp_single_content_input_font_size' );
						$woo_currency_symbol                    = get_woocommerce_currency_symbol();
						$woo_currency_code                      = get_woocommerce_currency();
						?>
                        <table class="form-table">
                            <tr>
                                <th>
                                    <label for="vicatna-nyp_single_position">
										<?php esc_html_e( 'Position', 'catna-woo-name-your-price-and-offers' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <select name="nyp_single_position" id="vicatna-nyp_single_position"
                                            class="vi-ui fluid dropdown vicatna-nyp_single_position">
                                        <option value="before_atc" <?php selected( $nyp_single_position, 'before_atc' ); ?>>
											<?php esc_html_e( 'Before add to cart button', 'catna-woo-name-your-price-and-offers' ); ?>
                                        </option>
                                        <option value="after_atc" <?php selected( $nyp_single_position, 'after_atc' ); ?>>
											<?php esc_html_e( 'after add to cart button', 'catna-woo-name-your-price-and-offers' ); ?>
                                        </option>
                                    </select>
                                    <p class="description">
										<?php esc_html_e( 'Positions of Name your price on single product page', 'catna-woo-name-your-price-and-offers' ); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vicatna-nyp_single_content"><?php esc_html_e( 'Content layout', 'catna-woo-name-your-price-and-offers' ) ?></label>
                                </th>
                                <td>
                                    <textarea name="nyp_single_content" id="vicatna-nyp_single_content"
                                              class="vicatna-nyp_single_content"
                                              rows="10"><?php echo esc_textarea( $nyp_single_content ) ?></textarea>
                                    <p class="description">
										<?php printf( '{input_price} - %s', esc_html__( 'Input field for suggested price', 'catna-woo-name-your-price-and-offers' ) ); ?>
                                    </p>
                                    <p class="description">
										<?php printf( '{product_name} - %s', esc_html__( 'Name of product', 'catna-woo-name-your-price-and-offers' ) ); ?>
                                    </p>
                                    <p class="description">
										<?php printf( '{min_price} - %s', esc_html__( 'Minimum acceptable price', 'catna-woo-name-your-price-and-offers' ) ); ?>
                                    </p>
                                    <p class="description">
										<?php printf( '{max_price} - %s', esc_html__( 'Maximum acceptable price', 'catna-woo-name-your-price-and-offers' ) ); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vicatna-nyp_single_content_color">
										<?php esc_html_e( 'Style of content layout', 'catna-woo-name-your-price-and-offers' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <div class="field">
                                        <div class="equal width fields">
                                            <div class="field">
                                                <div class="vi-ui left labeled input">
                                                    <div class="vi-ui basic label vicatna-basic-label"><?php esc_html_e( 'Color', 'catna-woo-name-your-price-and-offers' ); ?></div>
                                                    <input type="text"
                                                           class="vicatna-color vicatna-nyp_single_content_color"
                                                           name="nyp_single_content_color"
                                                           value="<?php echo esc_attr( $nyp_single_content_color ) ?>">
                                                </div>
                                            </div>
                                            <div class="field">
                                                <div class="vi-ui right labeled input">
                                                    <div class="vi-ui basic label vicatna-basic-label"><?php esc_html_e( 'Font size', 'catna-woo-name-your-price-and-offers' ); ?></div>
                                                    <input type="number" class="vicatna-nyp_single_content_font_size"
                                                           data-allow_empty="1"
                                                           name="nyp_single_content_font_size"
                                                           value="<?php echo esc_attr( $nyp_single_content_font_size ) ?>">
                                                    <div class="vi-ui label vicatna-basic-label"><?php echo esc_html( 'px' ); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vicatna-nyp_single_content_input"><?php esc_html_e( 'Input placeholder', 'catna-woo-name-your-price-and-offers' ) ?></label>
                                </th>
                                <td>
									<textarea name="nyp_single_content_input" id="vicatna-nyp_single_content_input"
                                              class="vicatna-nyp_single_content_input"
                                              rows="10"><?php echo esc_textarea( $nyp_single_content_input ) ?></textarea>
                                    <p class="description">
										<?php printf( '{product_name} - %s', esc_html__( 'Name of product', 'catna-woo-name-your-price-and-offers' ) ); ?>
                                    </p>
                                    <p class="description">
										<?php printf( '{min_price} - %s', esc_html__( 'Minimum acceptable price', 'catna-wooname-your-price-and-offers' ) ); ?>
                                    </p>
                                    <p class="description">
										<?php printf( '{max_price} - %s', esc_html__( 'Maximum acceptable price ', 'catna-woo-name-your-price-and-offers' ) ); ?>
                                    </p>
                                    <p class="description">
										<?php printf( '{currency_symbol} - %s( %s )', esc_html__( 'Currency symbol', 'catna-woo-name-your-price-and-offers' ), esc_html( $woo_currency_symbol ) ); ?>
                                    </p>
                                    <p class="description">
										<?php printf( '{currency_code} - %s( %s )', esc_html__( 'Currency code', 'catna-woo-name-your-price-and-offers' ), esc_html( $woo_currency_code ) ); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vicatna-nyp_single_content_input_border_width">
										<?php esc_html_e( 'Style of input', 'catna-woo-name-your-price-and-offers' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <div class="field">
                                        <div class="equal width fields">
                                            <div class="field">
                                                <label><?php esc_html_e( 'Enable currency', 'catna-woo-name-your-price-and-offers' ); ?></label>
                                                <div class="vi-ui toggle checkbox">
                                                    <input type="hidden" name="nyp_single_content_input_currency"
                                                           value="<?php echo esc_attr( $nyp_single_content_input_currency ); ?>">
                                                    <input type="checkbox"
                                                           class="vicatna-nyp_single_content_input_currency-checkbox" <?php checked( $nyp_single_content_input_currency, 1 ) ?>>
                                                </div>
                                            </div>
                                            <div class="field">
                                                <label><?php esc_html_e( 'Border width', 'catna-woo-name-your-price-and-offers' ); ?></label>
                                                <div class="vi-ui right labeled input">
                                                    <input type="number"
                                                           class="vicatna-nyp_single_content_input_border_width" min="0"
                                                           name="nyp_single_content_input_border_width"
                                                           value="<?php echo esc_attr( $nyp_single_content_input_border_width ) ?>">
                                                    <div class="vi-ui label vicatna-basic-label"><?php echo esc_html( 'px' ); ?></div>
                                                </div>
                                            </div>
                                            <div class="field">
                                                <label><?php esc_html_e( 'Border radius', 'catna-woo-name-your-price-and-offers' ); ?></label>
                                                <div class="vi-ui right labeled input">
                                                    <input type="number"
                                                           class="vicatna-nyp_single_content_input_border_width" min="0"
                                                           name="nyp_single_content_input_border_radius"
                                                           value="<?php echo esc_attr( $nyp_single_content_input_border_radius ) ?>">
                                                    <div class="vi-ui label vicatna-basic-label"><?php echo esc_html( 'px' ); ?></div>
                                                </div>
                                            </div>
                                            <div class="field">
                                                <label><?php esc_html_e( 'Border color', 'catna-woo-name-your-price-and-offers' ); ?></label>
                                                <input type="text"
                                                       class="vicatna-color vicatna-nyp_single_content_input_border_color"
                                                       name="nyp_single_content_input_border_color"
                                                       value="<?php echo esc_attr( $nyp_single_content_input_border_color ) ?>">
                                            </div>
                                        </div>
                                        <div class="equal width fields">
                                            <div class="field">
                                                <label><?php esc_html_e( 'Background color', 'catna-woo-name-your-price-and-offers' ); ?></label>
                                                <input type="text"
                                                       class="vicatna-color vicatna-nyp_single_content_input_bg_color"
                                                       name="nyp_single_content_input_bg_color"
                                                       value="<?php echo esc_attr( $nyp_single_content_input_bg_color ) ?>">
                                            </div>
                                            <div class="field">
                                                <label><?php esc_html_e( 'Color', 'catna-woo-name-your-price-and-offers' ); ?></label>
                                                <input type="text"
                                                       class="vicatna-color vicatna-nyp_single_content_input_color"
                                                       name="nyp_single_content_input_color"
                                                       value="<?php echo esc_attr( $nyp_single_content_input_color ) ?>">
                                            </div>
                                            <div class="field">
                                                <label><?php esc_html_e( 'Font size', 'catna-woo-name-your-price-and-offers' ); ?></label>
                                                <div class="vi-ui right labeled input">
                                                    <input type="number"
                                                           class="vicatna-nyp_single_content_input_font_size"
                                                           data-allow_empty="1"
                                                           name="nyp_single_content_input_font_size"
                                                           value="<?php echo esc_attr( $nyp_single_content_input_font_size ) ?>">
                                                    <div class="vi-ui label vicatna-basic-label"><?php echo esc_html( 'px' ); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="vi-ui bottom attached tab segment vicatna-img-preview-wrap" data-tab="nyp_pd_list">
                        <a class="vi-ui vicatna-img-preview" href="https://1.envato.market/kjamOx"
                           target="_blank"
                           title="<?php esc_attr_e( 'Unlock This Feature', 'catna-woo-name-your-price-and-offers' ); ?>">
                            <img src="<?php echo esc_url( VICATNA_IMAGES . 'nyp_on_product_list.png' ) ?>"
                                 alt="nyp_on_product_list">
                        </a>
                    </div>
                    <p class="vicatna-save-wrap">
                        <button type="submit" class="vicatna-bt-save vi-ui primary button" name="vicatna-save">
							<?php esc_html_e( 'Save', 'catna-woo-name-your-price-and-offers' ); ?>
                        </button>
                    </p>
                </form>
				<?php
				// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
				do_action( 'villatheme_support_catna-woo-name-your-price-and-offers' );
				?>
            </div>
        </div>
		<?php
	}

	public function save_settings() {
		$page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
		if ( 'catna-woocommerce-name-your-price-and-offers' != $page ) {
			return;
		}
		if ( ! current_user_can( apply_filters( 'vicatna_change_role', 'manage_woocommerce' ) ) ) {
			return;
		}
		if ( ! isset( $_POST['_vicatna_name_your_price'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_vicatna_name_your_price'] ) ), '_vicatna_name_your_price_action' ) ) {
			return;
		}
		if ( ! isset( $_POST['vicatna-save'] ) ) {
			return;
		}
		global $vicatna_settings;
		if ( ! $vicatna_settings ) {
			$vicatna_settings = get_option( 'vicatna_params', array() );
		}
		$map_args_1 = array(
			'nyp_enable',
			'nyp_quickview',
			'nyp_free_purchase',
			'nyp_hide_pd_price',
			'nyp_input_step',
			'nyp_single_position',
			'nyp_single_content_color',
			'nyp_single_content_font_size',
			'nyp_single_content_input_currency',
			'nyp_single_content_input_border_width',
			'nyp_single_content_input_border_radius',
			'nyp_single_content_input_border_color',
			'nyp_single_content_input_bg_color',
			'nyp_single_content_input_color',
			'nyp_single_content_input_font_size',
		);
		$map_args_2 = array(
			'nyp_mess_empty',
			'nyp_mess_low',
			'nyp_mess_high',
			'nyp_single_content',
			'nyp_single_content_input',
			'nyp_loop_atc_label',
			'nyp_loop_content',
			'nyp_loop_content_input',
		);
		$args       = array();
		foreach ( $map_args_1 as $item ) {
			$args[ $item ] = isset( $_POST[ $item ] ) ? sanitize_text_field( wp_unslash( $_POST[ $item ] ) ) : '';
		}
		foreach ( $map_args_2 as $item ) {
			$args[ $item ] = isset( $_POST[ $item ] ) ? wp_kses_post( wp_unslash( $_POST[ $item ] ) ) : '';
		}
		$args = wp_parse_args( $args, get_option( 'vicatna_params', $vicatna_settings ) );
		if ( is_plugin_active( 'wp-fastest-cache/wpFastestCache.php' ) ) {
			$cache = new WpFastestCache();
			$cache->deleteCache( true );
		}
		$vicatna_settings = $args;
		update_option( 'vicatna_params', $args );
	}

	public function admin_enqueue_scripts() {
        $screen_id = get_current_screen()->id;
		if ( 'toplevel_page_catna-woocommerce-name-your-price-and-offers' == $screen_id ) {
			$admin = 'VICATNA_Admin_Settings';
			$admin::remove_other_script();
			$admin::enqueue_style(
				array(
					'semantic-ui-button',
					'semantic-ui-checkbox',
					'semantic-ui-dropdown',
					'semantic-ui-form',
					'semantic-ui-icon',
					'semantic-ui-input',
					'semantic-ui-label'
				),
				array(
					'button.min.css',
					'checkbox.min.css',
					'dropdown.min.css',
					'form.min.css',
					'icon.min.css',
					'input.min.css',
					'label.min.css'
				)
			);
			$admin::enqueue_style(
				array( 'semantic-ui-menu', 'semantic-ui-segment', 'semantic-ui-tab' ),
				array( 'menu.min.css', 'segment.min.css', 'tab.min.css' )
			);
			$admin::enqueue_style(
				array( 'vicatna-admin-settings', 'transition', 'minicolors' ),
				array( 'admin-settings.' . VICATNA_SUFFIX . 'css', 'transition.min.css', 'minicolors.css' )
			);
			$admin::enqueue_script(
				array(
					'semantic-ui-address',
					'semantic-ui-checkbox',
					'semantic-ui-dropdown',
					'semantic-ui-form',
					'semantic-ui-tab'
				),
				array( 'address.min.js', 'checkbox.min.js', 'dropdown.min.js', 'form.min.js', 'tab.min.js' )
			);
			$admin::enqueue_script(
				array( 'vicatna-admin-settings', 'transition', 'minicolors' ),
				array( 'admin-settings.js', 'transition.min.js', 'minicolors.min.js' )
			);
		}
	}
}