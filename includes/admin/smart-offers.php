<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VICATNA_Admin_Smart_Offers {
	protected $settings;

	function __construct() {
		$this->settings = VICATNA_DATA::get_instance();
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 20 );
		add_action( 'admin_init', array( $this, 'save_settings' ), 99 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), PHP_INT_MAX );
	}

	public function admin_menu() {
		add_submenu_page(
			'catna-woocommerce-name-your-price-and-offers',
			esc_html__( 'Smart Offers', 'catna-woo-name-your-price-and-offers' ),
			esc_html__( 'Smart Offers', 'catna-woo-name-your-price-and-offers' ),
			apply_filters( 'vicatna_change_role', 'manage_woocommerce' ),
			'catna-woocommerce-name-your-price-and-offers-so',
			array( $this, 'settings_callback' )
		);
	}

	public function save_settings() {
		$page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
		if ( 'catna-woocommerce-name-your-price-and-offers-so' !== $page ) {
			return;
		}
		if ( ! current_user_can( apply_filters( 'vicatna_change_role', 'manage_woocommerce' ) ) ) {
			return;
		}
		if ( ! isset( $_POST['_vicatna_name_your_price'] ) || ! wp_verify_nonce( wc_clean( wp_unslash( $_POST['_vicatna_name_your_price'] ) ), '_vicatna_name_your_price_action' ) ) {
			return;
		}
		if ( ! isset( $_POST['vicatna-save'] ) ) {
			return;
		}
		global $vicatna_settings;
		$map_args_1 = array(
			'so_enable',
			'so_atc_normal',
			'so_offer_bt_position',
			'so_popup',
			'so_popup_title_color',
			'so_popup_title_font_size',
			'so_input_title_color',
			'so_input_title_font_size',
			'so_input_currency',
			'so_input_border_width',
			'so_input_border_radius',
			'so_input_border_color',
			'so_input_bg_color',
			'so_input_color',
			'so_input_font_size',
			'so_popup_bt_border_width',
			'so_popup_bt_border_radius',
			'so_popup_bt_border_color',
			'so_popup_bt_bg_color',
			'so_popup_bt_color',
			'so_popup_bt_font_size',
		);
		$map_args_2 = array(
			'so_offer_bt_label',
			'so_single_atc_label',
			'so_loop_atc_label',
			'so_mess_empty',
			'so_mess_low',
			'so_mess_high',
			'so_mess_wait',
			'so_mess_success',
			'so_popup_title',
			'so_input_title',
			'so_input',
			'so_popup_bt_label',
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

	public function settings_callback() {
		$this->settings = VICATNA_DATA::get_instance( true );
		?>
        <div class="wrap">
            <h2><?php esc_html_e( 'Smart Offers for WooCommerce', 'catna-woo-name-your-price-and-offers' ); ?></h2>
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
                        <a class="item" data-tab="design">
							<?php esc_html_e( 'Design', 'catna-woo-name-your-price-and-offers' ); ?>
                        </a>
                    </div>
                    <div class="vi-ui bottom attached tab segment active" data-tab="general">
						<?php
						$so_enable                   = $this->settings->get_params( 'so_enable' );
						$so_atc_normal               = $this->settings->get_params( 'so_atc_normal' );
						$so_offer_bt_label           = $this->settings->get_params( 'so_offer_bt_label' );
						$so_offer_bt_position        = $this->settings->get_params( 'so_offer_bt_position' );
						$so_single_atc_label         = $this->settings->get_params( 'so_single_atc_label' );
						$so_loop_atc_label           = $this->settings->get_params( 'so_loop_atc_label' );
						$so_atc_normal_enable_class  = 'vicatna-so_atc_normal-enable';
						$so_atc_normal_disable_class = 'vicatna-so_atc_normal-disable';
						if ( $so_atc_normal ) {
							$so_atc_normal_disable_class .= ' vicatna-disabled';
						} else {
							$so_atc_normal_enable_class .= ' vicatna-disabled';
						}
						?>
                        <table class="form-table">
                            <tr>
                                <th>
                                    <label for="vicatna-so_enable-checkbox"><?php esc_html_e( 'Enable', 'catna-woo-name-your-price-and-offers' ); ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui checkbox toggle">
                                        <input type="hidden" class="vicatna-so_enable" name="so_enable"
                                               value="<?php echo esc_attr( $so_enable ); ?>">
                                        <input type="checkbox" class="vicatna-so_enable-checkbox"
                                               id="vicatna-so_enable-checkbox" <?php checked( $so_enable, 1 ); ?>>
                                    </div>
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
                                    <label for="vicatna-so_atc_normal-checkbox"><?php esc_html_e( 'Add to cart without bargain', 'catna-woo-name-your-price-and-offers' ); ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui checkbox toggle">
                                        <input type="hidden" class="vicatna-so_atc_normal" name="so_atc_normal"
                                               value="<?php echo esc_attr( $so_atc_normal ); ?>">
                                        <input type="checkbox" class="vicatna-so_atc_normal-checkbox"
                                               id="vicatna-so_atc_normal-checkbox" <?php checked( $so_atc_normal, 1 ); ?>>
                                    </div>
                                    <p class="description">
										<?php esc_html_e( 'Enable to allow to add product to cart, skip bargain steps', 'catna-woo-name-your-price-and-offers' ); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr class="<?php echo esc_attr( $so_atc_normal_enable_class ); ?>">
                                <th>
                                    <label for="vicatna-so_offer_bt_label">
										<?php esc_html_e( 'Offer button label', 'catna-woo-name-your-price-and-offers' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="text" name="so_offer_bt_label" class="vicatna-so_offer_bt_label"
                                           id="vicatna-so_offer_bt_label"
                                           placeholder="<?php esc_attr_e( 'Make your offer now', 'catna-woo-name-your-price-and-offers' ); ?>"
                                           value="<?php echo esc_attr( $so_offer_bt_label ); ?>">
                                    <p class="description">
										<?php esc_html_e( 'Offer button label', 'catna-woo-name-your-price-and-offers' ); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr class="<?php echo esc_attr( $so_atc_normal_enable_class ); ?>">
                                <th>
                                    <label for="vicatna-so_offer_bt_position">
										<?php esc_html_e( 'Offer button position', 'catna-woo-name-your-price-and-offers' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <select name="so_offer_bt_position" id="vicatna-so_offer_bt_position"
                                            class="vi-ui fluid dropdown vicatna-so_offer_bt_position">
                                        <option value="before_atc" <?php selected( $so_offer_bt_position, 'before_atc' ) ?>>
											<?php esc_html_e( 'Before add to cart button', 'catna-woo-name-your-price-and-offers' ); ?>
                                        </option>
                                        <option value="after_atc" <?php selected( $so_offer_bt_position, 'after_atc' ) ?>>
											<?php esc_html_e( 'After add to cart button', 'catna-woo-name-your-price-and-offers' ); ?>
                                        </option>
                                    </select>
                                    <p class="description">
										<?php esc_html_e( 'The position of bargain button', 'catna-woo-name-your-price-and-offers' ); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr class="<?php echo esc_attr( $so_atc_normal_disable_class ); ?>">
                                <th>
                                    <label for="vicatna-so_single_atc_label">
										<?php esc_html_e( 'Add to cart button label on single page', 'catna-woo-name-your-price-and-offers' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="text" name="so_single_atc_label" class="vicatna-so_single_atc_label"
                                           id="vicatna-so_single_atc_label"
                                           placeholder="<?php esc_attr_e( 'Make your offer now', 'catna-woo-name-your-price-and-offers' ); ?>"
                                           value="<?php echo esc_attr( $so_single_atc_label ); ?>">
                                    <p class="description">
										<?php esc_html_e( 'Change the label of the add to cart button on single page. Leave blank to user the default', 'catna-woo-name-your-price-and-offers' ); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr class="<?php echo esc_attr( $so_atc_normal_disable_class ); ?>">
                                <th>
                                    <label for="vicatna-so_loop_atc_label">
										<?php esc_html_e( 'Add to cart button label on product list', 'catna-woo-name-your-price-and-offers' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="text" name="so_loop_atc_label" class="vicatna-so_loop_atc_label"
                                           id="vicatna-so_loop_atc_label"
                                           placeholder="<?php esc_attr_e( 'Make your offer now', 'catna-woo-name-your-price-and-offers' ); ?>"
                                           value="<?php echo esc_attr( $so_loop_atc_label ); ?>">
                                    <p class="description">
										<?php esc_html_e( 'Change the label of the add to cart button on product list. Leave blank to user the default', 'catna-woo-name-your-price-and-offers' ); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="vi-ui bottom attached tab segment" data-tab="message">
						<?php
						$so_mess_empty   = $this->settings->get_params( 'so_mess_empty' );
						$so_mess_low     = $this->settings->get_params( 'so_mess_low' );
						$so_mess_high    = $this->settings->get_params( 'so_mess_high' );
						$so_mess_wait    = $this->settings->get_params( 'so_mess_wait' );
						$so_mess_success = $this->settings->get_params( 'so_mess_success' );
						?>
                        <table class="form-table">
                            <tr>
                                <th>
                                    <label for="vicatna-so_mess_empty">
										<?php esc_html_e( 'Empty suggested price', 'catna-woo-name-your-price-and-offers' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="text" name="so_mess_empty" class="vicatna-so_mess_empty"
                                           id="vicatna-so_mess_empty"
                                           placeholder="<?php esc_attr_e( 'Please enter your suggested price for this product', 'catna-woo-name-your-price-and-offers' ); ?>"
                                           value="<?php echo esc_attr( $so_mess_empty ); ?>">
                                    <p class="description">
										<?php esc_html_e( 'Message when no suggested price entered', 'catna-woo-name-your-price-and-offers' ); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vicatna-so_mess_low">
										<?php esc_html_e( 'Low suggested price', 'catna-woo-name-your-price-and-offers' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="text" name="so_mess_low" class="vicatna-so_mess_low"
                                           id="vicatna-so_mess_low"
                                           placeholder="<?php esc_attr_e( 'Your suggested price is too low for us', 'catna-woo-name-your-price-and-offers' ); ?>"
                                           value="<?php echo esc_attr( $so_mess_low ); ?>">
                                    <p class="description">
										<?php esc_html_e( 'Message when suggested price lower than the minimum acceptable price', 'catna-woo-name-your-price-and-offers' ); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vicatna-so_mess_high">
										<?php esc_html_e( 'High suggested price', 'catna-woo-name-your-price-and-offers' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="text" name="so_mess_high" class="vicatna-so_mess_high"
                                           id="vicatna-so_mess_high"
                                           placeholder="<?php echo esc_attr( $so_mess_high ); ?>"
                                           value="<?php echo esc_attr( $so_mess_high ); ?>">
                                    <p class="description">
										<?php esc_html_e( 'Message when suggested price higher than the present price of product', 'catna-woo-name-your-price-and-offers' ); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vicatna-so_mess_wait">
										<?php esc_html_e( 'Waiting for response', 'catna-woo-name-your-price-and-offers' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="text" name="so_mess_wait" class="vicatna-so_mess_wait"
                                           id="vicatna-so_mess_wait"
                                           placeholder="<?php echo esc_attr( $so_mess_wait ); ?>"
                                           value="<?php echo esc_attr( $so_mess_wait ); ?>">
                                    <p class="description">
										<?php esc_html_e( 'Message when suggested price being checked', 'catna-woo-name-your-price-and-offers' ); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vicatna-so_mess_success">
										<?php esc_html_e( 'Successful message', 'catna-woo-name-your-price-and-offers' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="text" name="so_mess_success" class="vicatna-so_mess_success"
                                           id="vicatna-so_mess_success"
                                           placeholder="<?php echo esc_attr( $so_mess_success ); ?>"
                                           value="<?php echo esc_attr( $so_mess_success ); ?>">
                                    <p class="description">
										<?php esc_html_e( 'Message after suggested price acceptable', 'catna-woo-name-your-price-and-offers' ); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label><?php esc_html_e( 'Shortcode', 'catna-woo-name-your-price-and-offers' ); ?></label>
                                </th>
                                <td>
                                    <p class="description">
										<?php echo sprintf( '{product_name} - %s', esc_html__( 'Name of product', 'catna-woo-name-your-price-and-offers' ) ); ?>
                                    </p>
                                    <p class="description">
										<?php echo sprintf( '{min_price} - %s', esc_html__( 'Minimum acceptable price ', 'catna-woo-name-your-price-and-offers' ) ); ?>
                                    </p>
                                    <p class="description">
										<?php echo sprintf( '{product_price} - %s', esc_html__( 'Product price', 'catna-woo-name-your-price-and-offers' ) ); ?>
                                    </p>
                                    <p class="description">
										<?php echo sprintf( '{suggested_price} - %s', esc_html__( 'Suggested price by customer when purchasing product', 'catna-woo-name-your-price-and-offers' ) ); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="vi-ui bottom attached tab segment" data-tab="design">
						<?php
						$so_popup                  = $this->settings->get_params( 'so_popup' );
						$so_popup_title            = $this->settings->get_params( 'so_popup_title' );
						$so_popup_title_color      = $this->settings->get_params( 'so_popup_title_color' );
						$so_popup_title_font_size  = $this->settings->get_params( 'so_popup_title_font_size' );
						$so_input_title            = $this->settings->get_params( 'so_input_title' );
						$so_input_title_color      = $this->settings->get_params( 'so_input_title_color' );
						$so_input_title_font_size  = $this->settings->get_params( 'so_input_title_font_size' );
						$so_input                  = $this->settings->get_params( 'so_input' );
						$so_input_currency         = $this->settings->get_params( 'so_input_currency' );
						$so_input_border_width     = $this->settings->get_params( 'so_input_border_width' );
						$so_input_border_radius    = $this->settings->get_params( 'so_input_border_radius' );
						$so_input_border_color     = $this->settings->get_params( 'so_input_border_color' );
						$so_input_bg_color         = $this->settings->get_params( 'so_input_bg_color' );
						$so_input_color            = $this->settings->get_params( 'so_input_color' );
						$so_input_font_size        = $this->settings->get_params( 'so_input_font_size' );
						$so_popup_bt_label         = $this->settings->get_params( 'so_popup_bt_label' );
						$so_popup_bt_border_width  = $this->settings->get_params( 'so_popup_bt_border_width' );
						$so_popup_bt_border_radius = $this->settings->get_params( 'so_popup_bt_border_radius' );
						$so_popup_bt_border_color  = $this->settings->get_params( 'so_popup_bt_border_color' );
						$so_popup_bt_bg_color      = $this->settings->get_params( 'so_popup_bt_bg_color' );
						$so_popup_bt_color         = $this->settings->get_params( 'so_popup_bt_color' );
						$so_popup_bt_font_size     = $this->settings->get_params( 'so_popup_bt_font_size' );
						$so_popup_enable_class     = 'vicatna-so_popup-enable';
						if ( ! $so_popup ) {
							$so_popup_enable_class .= ' vicatna-disabled';
						}
						?>
                        <table class="form-table">
                            <tr>
                                <th>
                                    <label for="vicatna-so_popup-checkbox"><?php esc_html_e( 'Display as popup', 'catna-woo-name-your-price-and-offers' ); ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui checkbox toggle">
                                        <input type="hidden" class="vicatna-so_popup" name="so_popup"
                                               value="<?php echo esc_attr( $so_popup ); ?>">
                                        <input type="checkbox" class="vicatna-so_popup-checkbox"
                                               id="vicatna-so_popup-checkbox" <?php checked( $so_popup, 1 ); ?>>
                                    </div>
                                    <p class="description">
										<?php esc_html_e( 'Display the bargain form under a popup after clicking the request a bargain button', 'catna-woo-name-your-price-and-offers' ); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr class="<?php echo esc_attr( $so_popup_enable_class ); ?>">
                                <th>
                                    <label for="vicatna-so_popup_title">
										<?php esc_html_e( 'Offer form title', 'catna-woo-name-your-price-and-offers' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="text" name="so_popup_title" class="vicatna-so_popup_title"
                                           id="vicatna-so_popup_title"
                                           placeholder="<?php echo esc_attr( $so_popup_title ); ?>"
                                           value="<?php echo esc_attr( $so_popup_title ); ?>">
                                    <p class="description">
										<?php esc_html_e( 'Change the label of the add to cart button on product list. Leave blank to use the default', 'catna-woo-name-your-price-and-offers' ); ?>
                                    </p>
                                    <p class="description">
										<?php echo sprintf( '{product_name} - %s', esc_html__( 'Name of product', 'catna-woo-name-your-price-and-offers' ) ); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr class="<?php echo esc_attr( $so_popup_enable_class ); ?>">
                                <th>
                                    <label for="vicatna-so_popup_title_color">
										<?php esc_html_e( 'Style of offer form title', 'catna-woo-name-your-price-and-offers' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <div class="equal width fields">
                                        <div class="field">
                                            <div class="vi-ui left labeled input">
                                                <div class="vi-ui basic label vicatna-basic-label"><?php esc_html_e( 'Color', 'catna-woo-name-your-price-and-offers' ); ?></div>
                                                <input type="text" class="vicatna-color vicatna-so_popup_title_color"
                                                       name="so_popup_title_color"
                                                       value="<?php echo esc_attr( $so_popup_title_color ) ?>">
                                            </div>
                                        </div>
                                        <div class="field">
                                            <div class="vi-ui right labeled input">
                                                <div class="vi-ui basic label vicatna-basic-label"><?php esc_html_e( 'Font size', 'catna-woo-name-your-price-and-offers' ); ?></div>
                                                <input type="number" class="vicatna-so_popup_title_font_size"
                                                       data-allow_empty="1"
                                                       name="so_popup_title_font_size"
                                                       value="<?php echo esc_attr( $so_popup_title_font_size ) ?>">
                                                <div class="vi-ui label vicatna-basic-label"><?php echo esc_html( 'px' ); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vicatna-so_input_title">
										<?php esc_html_e( 'Input offer label', 'catna-woo-name-your-price-and-offers' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="text" name="so_input_title" class="vicatna-so_input_title"
                                           id="vicatna-so_input_title"
                                           value="<?php echo esc_attr( $so_input_title ); ?>">
                                    <p class="description">
										<?php esc_html_e( 'Bargain price title', 'catna-woo-name-your-price-and-offers' ); ?>
                                    </p>
                                    <p class="description">
										<?php echo sprintf( '{product_name} - %s', esc_html__( 'Name of product', 'catna-woo-name-your-price-and-offers' ) ); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vicatna-so_input_title_color">
										<?php esc_html_e( 'Style of Input offer label', 'catna-woo-name-your-price-and-offers' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <div class="equal width fields">
                                        <div class="field">
                                            <div class="vi-ui left labeled input">
                                                <div class="vi-ui basic label vicatna-basic-label"><?php esc_html_e( 'Color', 'catna-woo-name-your-price-and-offers' ); ?></div>
                                                <input type="text" class="vicatna-color vicatna-so_input_title_color"
                                                       name="so_input_title_color"
                                                       value="<?php echo esc_attr( $so_input_title_color ) ?>">
                                            </div>
                                        </div>
                                        <div class="field">
                                            <div class="vi-ui right labeled input">
                                                <div class="vi-ui basic label vicatna-basic-label"><?php esc_html_e( 'Font size', 'catna-woo-name-your-price-and-offers' ); ?></div>
                                                <input type="number" class="vicatna-so_input_title_font_size"
                                                       data-allow_empty="1"
                                                       name="so_input_title_font_size"
                                                       value="<?php echo esc_attr( $so_input_title_font_size ) ?>">
                                                <div class="vi-ui label vicatna-basic-label"><?php echo esc_html( 'px' ); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vicatna-so_input">
										<?php esc_html_e( 'Input offer placeholder', 'catna-woo-name-your-price-and-offers' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="text" name="so_input" class="vicatna-so_input" id="vicatna-so_input"
                                           value="<?php echo esc_attr( $so_input ); ?>">
                                    <p class="description">
										<?php printf( '{product_name} - %s', esc_html__( 'Name of product', 'catna-woo-name-your-price-and-offers' ) ); ?>
                                    </p>
                                    <p class="description">
										<?php printf( '{currency_symbol} - %s( %s )', esc_html__( 'Currency symbol', 'catna-woo-name-your-price-and-offers' ), esc_html( get_woocommerce_currency_symbol() ) ); ?>
                                    </p>
                                    <p class="description">
										<?php printf( '{currency_code} - %s( %s )', esc_html__( 'Currency code', 'catna-woo-name-your-price-and-offers' ), esc_html( get_woocommerce_currency() ) ); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vicatna-so_input_border_width">
										<?php esc_html_e( 'Style of input offer', 'catna-woo-name-your-price-and-offers' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <div class="equal width fields">
                                        <div class="field">
                                            <label><?php esc_html_e( 'Enable currency', 'catna-woo-name-your-price-and-offers' ); ?></label>
                                            <div class="vi-ui toggle checkbox">
                                                <input type="hidden" name="so_input_currency"
                                                       value="<?php echo esc_attr( $so_input_currency ); ?>">
                                                <input type="checkbox"
                                                       class="vicatna-so_input_currency-checkbox" <?php checked( $so_input_currency, 1 ) ?>>
                                            </div>
                                        </div>
                                        <div class="field">
                                            <label><?php esc_html_e( 'Border width', 'catna-woo-name-your-price-and-offers' ); ?></label>
                                            <div class="vi-ui right labeled input">
                                                <input type="number" class="vicatna-so_input_border_width" min="0"
                                                       name="so_input_border_width"
                                                       value="<?php echo esc_attr( $so_input_border_width ) ?>">
                                                <div class="vi-ui label vicatna-basic-label"><?php echo esc_html( 'px' ); ?></div>
                                            </div>
                                        </div>
                                        <div class="field">
                                            <label><?php esc_html_e( 'Border radius', 'catna-woo-name-your-price-and-offers' ); ?></label>
                                            <div class="vi-ui right labeled input">
                                                <input type="number" class="vicatna-so_input_border_radius" min="0"
                                                       name="so_input_border_radius"
                                                       value="<?php echo esc_attr( $so_input_border_radius ) ?>">
                                                <div class="vi-ui label vicatna-basic-label"><?php echo esc_html( 'px' ); ?></div>
                                            </div>
                                        </div>
                                        <div class="field">
                                            <label><?php esc_html_e( 'Border color', 'catna-woo-name-your-price-and-offers' ); ?></label>
                                            <input type="text" class="vicatna-color vicatna-so_input_border_color"
                                                   name="so_input_border_color"
                                                   value="<?php echo esc_attr( $so_input_border_color ) ?>">
                                        </div>
                                    </div>
                                    <div class="equal width fields">
                                        <div class="field">
                                            <label><?php esc_html_e( 'Background color', 'catna-woo-name-your-price-and-offers' ); ?></label>
                                            <input type="text" class="vicatna-color vicatna-so_input_bg_color"
                                                   name="so_input_bg_color"
                                                   value="<?php echo esc_attr( $so_input_bg_color ) ?>">
                                        </div>
                                        <div class="field">
                                            <label><?php esc_html_e( 'Color', 'catna-woo-name-your-price-and-offers' ); ?></label>
                                            <input type="text" class="vicatna-color vicatna-so_input_color"
                                                   name="so_input_color"
                                                   value="<?php echo esc_attr( $so_input_color ) ?>">
                                        </div>
                                        <div class="field">
                                            <label><?php esc_html_e( 'Font size', 'catna-woo-name-your-price-and-offers' ); ?></label>
                                            <div class="vi-ui right labeled input">
                                                <input type="number" class="vicatna-so_input_font_size"
                                                       data-allow_empty="1"
                                                       name="so_input_font_size"
                                                       value="<?php echo esc_attr( $so_input_font_size ) ?>">
                                                <div class="vi-ui label vicatna-basic-label"><?php echo esc_html( 'px' ); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr class="<?php echo esc_attr( $so_popup_enable_class ); ?>">
                                <th>
                                    <label for="vicatna-so_popup_bt_label">
										<?php esc_html_e( 'Submit button label', 'catna-woo-name-your-price-and-offers' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="text" name="so_popup_bt_label" class="vicatna-so_popup_bt_label"
                                           id="vicatna-so_popup_bt_label"
                                           value="<?php echo esc_attr( $so_popup_bt_label ); ?>">
                                    <p class="description">
										<?php echo sprintf( '{product_name} - %s', esc_html__( 'Name of product', 'catna-woo-name-your-price-and-offers' ) ); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr class="<?php echo esc_attr( $so_popup_enable_class ); ?>">
                                <th>
                                    <label for="vicatna-so_popup_bt_border_width">
										<?php esc_html_e( 'Style of submit button', 'catna-woo-name-your-price-and-offers' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <div class="equal width fields">
                                        <div class="field">
                                            <label><?php esc_html_e( 'Border width', 'catna-woo-name-your-price-and-offers' ); ?></label>
                                            <div class="vi-ui right labeled input">
                                                <input type="number" class="vicatna-so_popup_bt_border_width" min="0"
                                                       name="so_popup_bt_border_width"
                                                       value="<?php echo esc_attr( $so_popup_bt_border_width ) ?>">
                                                <div class="vi-ui label vicatna-basic-label"><?php echo esc_html( 'px' ); ?></div>
                                            </div>
                                        </div>
                                        <div class="field">
                                            <label><?php esc_html_e( 'Border radius', 'catna-woo-name-your-price-and-offers' ); ?></label>
                                            <div class="vi-ui right labeled input">
                                                <input type="number" class="vicatna-so_popup_bt_border_radius" min="0"
                                                       name="so_popup_bt_border_radius"
                                                       value="<?php echo esc_attr( $so_popup_bt_border_radius ) ?>">
                                                <div class="vi-ui label vicatna-basic-label"><?php echo esc_html( 'px' ); ?></div>
                                            </div>
                                        </div>
                                        <div class="field">
                                            <label><?php esc_html_e( 'Border color', 'catna-woo-name-your-price-and-offers' ); ?></label>
                                            <input type="text" class="vicatna-color vicatna-so_popup_bt_border_color"
                                                   name="so_popup_bt_border_color"
                                                   value="<?php echo esc_attr( $so_popup_bt_border_color ) ?>">
                                        </div>
                                    </div>
                                    <div class="equal width fields">
                                        <div class="field">
                                            <label><?php esc_html_e( 'Background color', 'catna-woo-name-your-price-and-offers' ); ?></label>
                                            <input type="text" class="vicatna-color vicatna-so_popup_bt_bg_color"
                                                   name="so_popup_bt_bg_color"
                                                   value="<?php echo esc_attr( $so_popup_bt_bg_color ) ?>">
                                        </div>
                                        <div class="field">
                                            <label><?php esc_html_e( 'Color', 'catna-woo-name-your-price-and-offers' ); ?></label>
                                            <input type="text" class="vicatna-color vicatna-so_popup_bt_color"
                                                   name="so_popup_bt_color"
                                                   value="<?php echo esc_attr( $so_popup_bt_color ) ?>">
                                        </div>
                                        <div class="field">
                                            <label><?php esc_html_e( 'Font size', 'catna-woo-name-your-price-and-offers' ); ?></label>
                                            <div class="vi-ui right labeled input">
                                                <input type="number" class="vicatna-so_popup_bt_font_size"
                                                       data-allow_empty="1"
                                                       name="so_popup_bt_font_size"
                                                       value="<?php echo esc_attr( $so_popup_bt_font_size ) ?>">
                                                <div class="vi-ui label vicatna-basic-label"><?php echo esc_html( 'px' ); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <p class="vicatna-save-wrap">
                        <button type="submit" class="vicatna-bt-save vi-ui primary button" name="vicatna-save">
							<?php esc_html_e( 'Save', 'catna-woo-name-your-price-and-offers' ); ?>
                        </button>
                    </p>
                </form>
            </div>
        </div>
		<?php
	}

	public function admin_enqueue_scripts() {
		$screen_id = get_current_screen()->id;
		if ( 'catna_page_catna-woocommerce-name-your-price-and-offers-so' === $screen_id ) {
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