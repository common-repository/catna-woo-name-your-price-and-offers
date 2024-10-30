<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VICATNA_Admin_Product {
	protected $settings;

	function __construct() {
		$this->settings = VICATNA_DATA::get_instance();
		//add new column on product list
		add_filter( 'manage_edit-product_columns', array( $this, 'vicatna_type_column' ) );
		add_action( 'manage_product_posts_custom_column', array( $this, 'column_callback_product' ), 10, 2 );
		//Name Your Price & smart offers of single/ variable product
		if ( $this->settings->get_params( 'nyp_variable' ) || $this->settings->get_params( 'so_variable' ) ) {
			add_filter( 'woocommerce_product_data_tabs', array( $this, 'vicatna_woocommerce_product_data_tabs' ) );
			add_action( 'woocommerce_product_data_panels', array( $this, 'vicatna_woocommerce_product_data_panels' ) );
		}
		add_action( 'woocommerce_product_options_pricing', array(
			$this,
			'vicatna_woocommerce_product_options_pricing'
		) );
		$product_type = apply_filters( 'vicatna_applicanle_product_type', [ 'simple', 'variable' ] );
		foreach ( $product_type as $type ) {
			add_action( 'woocommerce_process_product_meta_' . $type, array(
				$this,
				'vicatna_woocommerce_process_product_meta_simple'
			) );
		}
		//Name Your Price & smart offers of variation product
		add_action( 'woocommerce_variation_options_pricing', array(
			$this,
			'vicatna_woocommerce_variation_options_pricing'
		), 10, 3 );
		add_action( 'woocommerce_save_product_variation', array(
			$this,
			'vicatna_woocommerce_save_product_variation'
		), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	public function vicatna_woocommerce_save_product_variation( $variation_id, $i ) {
        if ( isset( $_POST[ '_vicatna_nonce_'.$variation_id ] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST[ '_vicatna_nonce_'.$variation_id ] ) ), 'vicatna_nonce_'.$variation_id ) ) {
	        $type          = isset( $_POST['vicatna_loop_type'][ $i ] ) ? wc_clean( wp_unslash( $_POST['vicatna_loop_type'][ $i ] ) ) : '';
	        $parent_id     = wp_get_post_parent_id( $variation_id );
	        $parent_params = get_post_meta( $parent_id, 'vicatna_settings_type', true ) ?? array();
	        $parent_params = ! is_array( $parent_params ) ? array() : $parent_params;
	        if ( '' === $type ) {
		        if ( isset( $parent_params[ $variation_id ] ) ) {
			        unset( $parent_params[ $variation_id ] );
			        update_post_meta( $parent_id, 'vicatna_settings_type', $parent_params );
		        }
		        update_post_meta( $variation_id, 'vicatna_settings', '' );

		        return;
	        }
	        $parent_params[ $variation_id ] = $type;
	        update_post_meta( $parent_id, 'vicatna_settings_type', $parent_params );
	        $args                = array( 'type' => $type );
	        $args['nyp_min']     = isset( $_POST['vicatna_loop_nyp_min'][ $i ] ) ? wc_clean( wp_unslash( $_POST['vicatna_loop_nyp_min'][ $i ] ) ) : '';
	        $args['nyp_max']     = isset( $_POST['vicatna_loop_nyp_max'][ $i ] ) ? wc_clean( wp_unslash( $_POST['vicatna_loop_nyp_max'][ $i ] ) ) : '';
	        $args['so_qty']      = isset( $_POST['vicatna_loop_so_qty'][ $i ] ) ? wc_clean( wp_unslash( $_POST['vicatna_loop_so_qty'][ $i ] ) ) : '';
	        $args['so_min']      = isset( $_POST['vicatna_loop_so_min'][ $i ] ) ? wc_clean( wp_unslash( $_POST['vicatna_loop_so_min'][ $i ] ) ) : '';
	        $args['so_type']     = isset( $_POST['vicatna_loop_so_type'][ $i ] ) ? wc_clean( wp_unslash( $_POST['vicatna_loop_so_type'][ $i ] ) ) : '';
	        $args['so_qty_from'] = isset( $_POST['vicatna_loop_so_qty_from'][ $i ] ) ? wc_clean( wp_unslash( $_POST['vicatna_loop_so_qty_from'][ $i ] ) ) : array();
	        $args['so_qty_to']   = isset( $_POST['vicatna_loop_so_qty_to'][ $i ] ) ? wc_clean( wp_unslash( $_POST['vicatna_loop_so_qty_to'][ $i ] ) ) : array();
	        $args['so_qty_min']  = isset( $_POST['vicatna_loop_so_qty_min'][ $i ] ) ? wc_clean( wp_unslash( $_POST['vicatna_loop_so_qty_min'][ $i ] ) ) : array();
	        $args['so_qty_type'] = isset( $_POST['vicatna_loop_so_qty_type'][ $i ] ) ? wc_clean( wp_unslash( $_POST['vicatna_loop_so_qty_type'][ $i ] ) ) : array();
	        update_post_meta( $variation_id, 'vicatna_settings', $args );
        }
	}

	public function vicatna_woocommerce_variation_options_pricing( $loop, $variation_data, $variation ) {
		$this->settings    = VICATNA_DATA::get_instance();
		$nyp_enable        = $this->settings->get_params( 'nyp_enable' );
		$so_enable         = $this->settings->get_params( 'so_enable' );
		$variation_id      = $variation->ID;
		$params            = get_post_meta( $variation_id, 'vicatna_settings', true ) ?? array();
		$type              = $params['type'] ?? '';
		$type_class        = array( 'select short' );
		$custom_attributes = array();
		if ( ! $nyp_enable && ! $so_enable ) {
			$type_class[]                  = 'disabled';
			$custom_attributes['disabled'] = 'disabled';
		} else {
			$type_class[] = 'vicatna_type_select';
			$type_class[] = ! $nyp_enable ? 'vicatna_type_not_nyp' : '';
			$type_class[] = ! $so_enable ? 'vicatna_type_not_so' : '';
		}
		wp_nonce_field( 'vicatna_nonce_' . $variation_id, '_vicatna_nonce_' . $variation_id );
		?>
        <div class="vicatnat-settings-wrap vicatnat-variation" data-loop="<?php echo esc_attr( $loop ); ?>">
			<?php
			woocommerce_wp_select(
				array(
					'id'                => 'vicatna_loop_type-' . $loop,
					'name'              => 'vicatna_loop_type[' . $loop . ']',
					'class'             => trim( implode( ' ', $type_class ) ),
					'custom_attributes' => $custom_attributes,
					'label'             => esc_html__( 'Name your price & smart offers', 'catna-woo-name-your-price-and-offers' ),
					'description'       => sprintf( '%s <a href="%s" target="_blank">%s</a>%s<a href="%s" target="_blank">%s</a>%s',
						esc_html__( 'Enable ', 'catna-woo-name-your-price-and-offers' ),
						esc_url( admin_url( 'admin.php?page=catna-woo-name-your-price-and-offers' ) ),
						esc_html__( 'Name Your Price', 'catna-woo-name-your-price-and-offers' ),
						esc_html__( ' or ', 'catna-woo-name-your-price-and-offers' ),
						esc_url( admin_url( 'admin.php?page=catna-woo-name-your-price-and-offers-so' ) ),
						esc_html__( 'Smart Offers', 'catna-woo-name-your-price-and-offers' ),
						esc_html__( ' to use features', 'catna-woo-name-your-price-and-offers' ) ),
					'options'           => array(
						'' => esc_html__( 'Global rules', 'catna-woo-name-your-price-and-offers' ),
						0  => esc_html__( 'Name your price', 'catna-woo-name-your-price-and-offers' ),
						1  => esc_html__( 'Offer', 'catna-woo-name-your-price-and-offers' ),
					),
					'value'             => $type,
				)
			);
			?>
            <div class="vicatna-settings vicatna-settings-nyp<?php echo esc_attr( '0' === $type ? '' : ' vicatna-disabled' );
			echo esc_attr( $nyp_enable || $so_enable ? '' : ' vicatna-not-allowed' ); ?>">
				<?php
				$currency_symbol = get_woocommerce_currency_symbol();
				$nyp_min         = $params['nyp_min'] ?? '';
				$nyp_max         = $params['nyp_max'] ?? '';
				woocommerce_wp_text_input(
					array(
						'id'            => 'vicatna_loop_nyp_min-' . $loop,
						'name'          => 'vicatna_loop_nyp_min[' . $loop . ']',
						'value'         => $nyp_min,
						'desc_tip'      => true,
						'description'   => esc_html__( 'Minimum acceptable price', 'catna-woo-name-your-price-and-offers' ),
						'label'         => esc_html__( 'Minimum price', 'catna-woo-name-your-price-and-offers' ) . ' (' . $currency_symbol . ')',
						'data_type'     => 'price',
						'wrapper_class' => 'form-row form-row-first'
					)
				);
				woocommerce_wp_text_input(
					array(
						'id'            => 'vicatna_loop_nyp_max-' . $loop,
						'name'          => 'vicatna_loop_nyp_max[' . $loop . ']',
						'value'         => $nyp_max,
						'placeholder'   => esc_html__( 'Leave blank to not limit that', 'catna-woo-name-your-price-and-offers' ),
						'label'         => esc_html__( 'Maximum price', 'catna-woo-name-your-price-and-offers' ) . ' (' . $currency_symbol . ')',
						'data_type'     => 'price',
						'wrapper_class' => 'form-row form-row-last',
					)
				);
				?>
            </div>
            <div class="vicatna-settings vicatna-settings-so<?php echo esc_attr( '1' === $type ? '' : ' vicatna-disabled' );
			echo esc_attr( $nyp_enable || $so_enable ? '' : ' vicatna-not-allowed' ); ?>">
				<?php
				$so_qty = $params['so_qty'] ?? 1;
				woocommerce_wp_select(
					array(
						'id'          => 'vicatna_loop_so_qty-' . $loop,
						'class'       => 'select short vicatna_so_qty_select',
						'name'        => 'vicatna_loop_so_qty[' . $loop . ']',
						'label'       => esc_html__( 'Offer with product quantity', 'catna-woo-name-your-price-and-offers' ),
						'desc_tip'    => true,
						'description' => esc_html__( 'Bargin based on quantity', 'catna-woo-name-your-price-and-offers' ),
						'options'     => array(
							0 => esc_html__( 'No', 'catna-woo-name-your-price-and-offers' ),
							1 => esc_html__( 'Yes', 'catna-woo-name-your-price-and-offers' ),
						),
						'value'       => $so_qty,
					)
				);
				?>
                <div class="vicatna-settings-so-qty-disable<?php echo esc_attr( $so_qty ? ' vicatna-disabled' : '' ); ?>">
					<?php
					$so_min  = $params['so_min'] ?? 10;
					$so_type = $params['so_type'] ?? 1;
					woocommerce_wp_text_input(
						array(
							'id'                => 'vicatna_loop_so_min-' . $loop,
							'name'              => 'vicatna_loop_so_min[' . $loop . ']',
							'custom_attributes' => array(
								'max' => $so_type ? '100' : '',
								'min' => '1'
							),
							'value'             => $so_min,
							'label'             => esc_html__( 'Maximum discount value', 'catna-woo-name-your-price-and-offers' ),
							'data_type'         => 'price',
							'wrapper_class'     => 'form-row form-row-first'
						)
					);
					woocommerce_wp_select(
						array(
							'id'            => 'vicatna_loop_so_type-' . $loop,
							'name'          => 'vicatna_loop_so_type[' . $loop . ']',
							'label'         => esc_html__( 'Base on', 'catna-woo-name-your-price-and-offers' ),
							'desc_tip'      => true,
							'options'       => array(
								0 => esc_html__( 'Fixed amount', 'catna-woo-name-your-price-and-offers' ),
								1 => esc_html__( 'Percentage', 'catna-woo-name-your-price-and-offers' ),
							),
							'value'         => $so_type,
							'wrapper_class' => 'form-row form-row-last',
						)
					);
					?>
                </div>
                <div class="vicatna-settings-so-qty-enable<?php echo esc_attr( $so_qty ? '' : ' vicatna-disabled' ); ?>">
					<?php
					$so_qty_from  = $params['so_qty_from'] ?? array( 1 );
					$so_qty_to    = $params['so_qty_to'] ?? array( '' );
					$so_qty_min   = $params['so_qty_min'] ?? array( 10 );
					$so_qty_type  = $params['so_qty_type'] ?? array( 1 );
					$accept_title = esc_html__( 'Accept', 'catna-woo-name-your-price-and-offers' ) . '(' . $currency_symbol . ')';
					?>
                    <div class="vicatna-settings-so-qty-wrap">
						<?php
						foreach ( $so_qty_from as $i => $qty_from ) {
							$so_qty_type_t = $so_qty_type[ $i ] ?? 1;
							?>
                            <div class="vicatna-so-qty-wrap">
                                <span class="vicatna-so-qty-action vicatna-so-qty-move"><i
                                            class="dashicons dashicons-menu-alt"></i></span>
                                <div class="vicatna-so-qty-condition-wrap">
                                    <div class="vicatna-so-qty-condition vicatna-so-qty-condition-left vicatna-so-qty-from-wrap">
                                        <div class="vicatna-so-qty-condition-label"><?php esc_html_e( 'Qty from', 'catna-woo-name-your-price-and-offers' ); ?></div>
                                        <input type="number" min="1"
                                               name="vicatna_loop_so_qty_from[<?php echo esc_attr( $loop ); ?>][]"
                                               class="vicatna-so-qty-from"
                                               value="<?php echo esc_attr( $so_qty_from[ $i ] ?? 1 ); ?>">
                                        <div class="vicatna-so-qty-condition-label"><?php esc_html_e( 'to', 'catna-woo-name-your-price-and-offers' ); ?></div>
                                        <input type="number" min="1"
                                               name="vicatna_loop_so_qty_to[<?php echo esc_attr( $loop ); ?>][]"
                                               data-allow_empty="1"
                                               title="<?php esc_attr_e( 'Leave blank to not limit this', 'catna-woo-name-your-price-and-offers' ); ?>"
                                               class="vicatna-so-qty-to"
                                               value="<?php echo esc_attr( $so_qty_to[ $i ] ?? 1 ); ?>">
                                    </div>
                                    <div class="vicatna-so-qty-condition vicatna-so-qty-condition-right vicatna-so-qty-min_price tips"
                                         data-tip="<?php esc_attr_e( 'Maximum discount value', 'catna-woo-name-your-price-and-offers' ); ?>">
                                        <div class="vicatna-so-qty-condition-label"><?php esc_html_e( 'Value', 'catna-woo-name-your-price-and-offers' ); ?></div>
                                        <input type="number" min="1"
                                               name="vicatna_loop_so_qty_min[<?php echo esc_attr( $loop ); ?>][]"
                                               class="vicatna-so-qty-min"
                                               max="<?php echo esc_attr( $so_qty_type_t ? '100' : '' ); ?>"
                                               value="<?php echo esc_attr( $so_qty_min[ $i ] ?? '' ); ?>">
                                        <select name="vicatna_loop_so_qty_type[<?php echo esc_attr( $loop ); ?>][]"
                                                class="vicatna-so-qty-type">
                                            <option value="0" <?php selected( $so_qty_type_t, 0 ) ?>>
												<?php esc_html_e( 'Fixed amount', 'catna-woo-name-your-price-and-offers' ); ?>
                                            </option>
                                            <option value="1" <?php selected( $so_qty_type_t, 1 ) ?>>
												<?php esc_html_e( 'Percentage', 'catna-woo-name-your-price-and-offers' ); ?>
                                            </option>
                                        </select>
                                    </div>
                                    <div class="vicatna-so-qty-condition vicatna-so-qty-condition-right vicatna-so-qty-condition-accept-price-wrap">
                                        <div class="vicatna-so-qty-condition-label">
											<?php echo wp_kses_post( $accept_title ); ?>
                                        </div>
                                        <div class="vicatna-so-qty-condition-accept-price">
                                            <span class="vicatna-so-qty-condition-accept-min"></span>
                                            <span class="dashicons dashicons-minus"></span>
                                            <span class="vicatna-so-qty-condition-accept-max"></span>
                                        </div>
                                    </div>
                                </div>
                                <span class="vicatna-so-qty-action vicatna-so-qty-clone"><i
                                            class="dashicons dashicons-admin-page"></i></span>
                                <span class="vicatna-so-qty-action vicatna-so-qty-remove"><i
                                            class="dashicons dashicons-no-alt"></i></span>
                            </div>
							<?php
						}
						?>
                    </div>
                </div>
            </div>
        </div>
		<?php
	}

	public function vicatna_woocommerce_process_product_meta_simple( $post_id ) {

		if ( isset( $_POST['_vicatnat_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_vicatnat_nonce'] ) ), 'vicatnat_nonce' ) ) {
			$type = isset( $_POST['vicatna_type'] ) ? wc_clean( wp_unslash( $_POST['vicatna_type'] ) ) : '';
			if ( '' === $type ) {
				update_post_meta( $post_id, 'vicatna_settings', '' );

				return;
			}
			$args                = array( 'type' => $type );
			$args['nyp_min']     = isset( $_POST['vicatna_nyp_min'] ) ? wc_clean( wp_unslash( $_POST['vicatna_nyp_min'] ) ) : '';
			$args['nyp_max']     = isset( $_POST['vicatna_nyp_max'] ) ? wc_clean( wp_unslash( $_POST['vicatna_nyp_max'] ) ) : '';
			$args['so_qty']      = isset( $_POST['vicatna_so_qty'] ) ? wc_clean( wp_unslash( $_POST['vicatna_so_qty'] ) ) : '';
			$args['so_min']      = isset( $_POST['vicatna_so_min'] ) ? wc_clean( wp_unslash( $_POST['vicatna_so_min'] ) ) : '';
			$args['so_type']     = isset( $_POST['vicatna_so_type'] ) ? wc_clean( wp_unslash( $_POST['vicatna_so_type'] ) ) : '';
			$args['so_qty_from'] = isset( $_POST['vicatna_so_qty_from'] ) ? wc_clean( wp_unslash( $_POST['vicatna_so_qty_from'] ) ) : array();
			$args['so_qty_to']   = isset( $_POST['vicatna_so_qty_to'] ) ? wc_clean( wp_unslash( $_POST['vicatna_so_qty_to'] ) ) : array();
			$args['so_qty_min']  = isset( $_POST['vicatna_so_qty_min'] ) ? wc_clean( wp_unslash( $_POST['vicatna_so_qty_min'] ) ) : array();
			$args['so_qty_type'] = isset( $_POST['vicatna_so_qty_type'] ) ? wc_clean( wp_unslash( $_POST['vicatna_so_qty_type'] ) ) : array();
			update_post_meta( $post_id, 'vicatna_settings', $args );
		}
	}

	public function vicatna_woocommerce_product_options_pricing() {
		?>
		<?php wp_nonce_field( 'vicatnat_nonce', '_vicatnat_nonce' ); ?>
        <div class="show_if_simple vicatnat-settings-wrap vicatnat-simple">
			<?php
			$this->product_simple_html();
			?>
        </div>
		<?php
	}

	public function vicatna_woocommerce_product_data_tabs( $tabs ) {
		$tabs['vicatna'] = array(
			'label'    => esc_html__( 'Name your price & smart offers', 'catna-woo-name-your-price-and-offers' ),
			'target'   => 'vicatna_settings',
			'class'    => array( 'show_if_variable' ),
			'priority' => 60,
		);

		return $tabs;
	}

	public function vicatna_woocommerce_product_data_panels() {
		?>
        <div id="vicatna_settings" class="panel woocommerce_options_panel vicatnat-settings-wrap vicatnat-variable">
            <p class="form-field">
                <label><?php esc_html_e( 'Name your price & smart offers', 'catna-woo-name-your-price-and-offers' ); ?></label>
                <a href="https://1.envato.market/kjamOx" target="_blank" class="secondary button">
					<?php esc_html_e( 'Unlock This Feature', 'catna-woo-name-your-price-and-offers' ); ?>
                </a>
            </p>
        </div>
		<?php
	}

	public function product_simple_html() {
		$this->settings = VICATNA_DATA::get_instance();
		global $thepostid;
		$nyp_enable        = $this->settings->get_params( 'nyp_enable' );
		$so_enable         = $this->settings->get_params( 'so_enable' );
		$params            = get_post_meta( $thepostid, 'vicatna_settings', true ) ?? array();
		$type              = $params['type'] ?? '';
		$type_class        = array( 'select short' );
		$custom_attributes = array( 'data-name' => 'vicatna_type' );
		if ( ! $nyp_enable && ! $so_enable ) {
			$type_class[]                  = 'disabled';
			$custom_attributes['disabled'] = 'disabled';
		} else {
			$type_class[] = 'vicatna_type_select';
			$type_class[] = ! $nyp_enable ? 'vicatna_type_not_nyp' : '';
			$type_class[] = ! $so_enable ? 'vicatna_type_not_so' : '';
		}
		woocommerce_wp_select(
			array(
				'id'                => 'vicatna_type',
				'name'              => '',
				'class'             => trim( implode( ' ', $type_class ) ),
				'custom_attributes' => $custom_attributes,
				'label'             => esc_html__( 'Name your price & smart offers', 'catna-woo-name-your-price-and-offers' ),
				'description'       => sprintf( '%s <a href="%s" target="_blank">%s</a>%s<a href="%s" target="_blank">%s</a>%s',
					esc_html__( 'Enable ', 'catna-woo-name-your-price-and-offers' ),
					esc_url( admin_url( 'admin.php?page=catna-woo-name-your-price-and-offers' ) ),
					esc_html__( 'Name Your Price', 'catna-woo-name-your-price-and-offers' ),
					esc_html__( ' or ', 'catna-woo-name-your-price-and-offers' ),
					esc_url( admin_url( 'admin.php?page=catna-woo-name-your-price-and-offers-so' ) ),
					esc_html__( 'Smart Offers', 'catna-woo-name-your-price-and-offers' ),
					esc_html__( ' to use features', 'catna-woo-name-your-price-and-offers' ) ),
				'options'           => array(
					'' => esc_html__( 'Global rules', 'catna-woo-name-your-price-and-offers' ),
					0  => esc_html__( 'Name your price', 'catna-woo-name-your-price-and-offers' ),
					1  => esc_html__( 'Offer', 'catna-woo-name-your-price-and-offers' ),
				),
				'value'             => $type,
			)
		);
		?>
        <div class="vicatna-settings vicatna-settings-nyp<?php echo esc_attr( '0' === $type ? '' : ' vicatna-disabled' );
		echo esc_attr( $nyp_enable || $so_enable ? '' : ' vicatna-not-allowed' ); ?>">
			<?php
			$currency_symbol = get_woocommerce_currency_symbol();
			$nyp_min         = $params['nyp_min'] ?? '';
			$nyp_max         = $params['nyp_max'] ?? '';
			woocommerce_wp_text_input(
				array(
					'id'                => 'vicatna_nyp_min',
					'name'              => '',
					'custom_attributes' => array( 'data-name' => 'vicatna_nyp_min' ),
					'value'             => $nyp_min,
					'desc_tip'          => true,
					'description'       => esc_html__( 'Minimum acceptable price', 'catna-woo-name-your-price-and-offers' ),
					'label'             => esc_html__( 'Minimum price', 'catna-woo-name-your-price-and-offers' ) . ' (' . $currency_symbol . ')',
					'data_type'         => 'price',
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'                => 'vicatna_nyp_max',
					'name'              => '',
					'custom_attributes' => array( 'data-name' => 'vicatna_nyp_max' ),
					'value'             => $nyp_max,
					'placeholder'       => esc_html__( 'Leave blank to not limit that', 'catna-woo-name-your-price-and-offers' ),
					'label'             => esc_html__( 'Maximum price', 'catna-woo-name-your-price-and-offers' ) . ' (' . $currency_symbol . ')',
					'data_type'         => 'price',
				)
			);
			?>
        </div>
        <div class="vicatna-settings vicatna-settings-so<?php echo esc_attr( '1' === $type ? '' : ' vicatna-disabled' );
		echo esc_attr( $nyp_enable || $so_enable ? '' : ' vicatna-not-allowed' ); ?>">
			<?php
			$so_qty = $params['so_qty'] ?? 1;
			woocommerce_wp_select(
				array(
					'id'                => 'vicatna_so_qty',
					'class'             => 'select short vicatna_so_qty_select',
					'name'              => '',
					'custom_attributes' => array( 'data-name' => 'vicatna_so_qty' ),
					'label'             => esc_html__( 'Offer with product quantity', 'catna-woo-name-your-price-and-offers' ),
					'desc_tip'          => true,
					'description'       => esc_html__( 'Bargin based on quantity', 'catna-woo-name-your-price-and-offers' ),
					'options'           => array(
						0 => esc_html__( 'No', 'catna-woo-name-your-price-and-offers' ),
						1 => esc_html__( 'Yes', 'catna-woo-name-your-price-and-offers' ),
					),
					'value'             => $so_qty,
				)
			);
			?>
            <div class="vicatna-settings-so-qty-disable<?php echo esc_attr( $so_qty ? ' vicatna-disabled' : '' ); ?>">
				<?php
				$so_min  = $params['so_min'] ?? 10;
				$so_type = $params['so_type'] ?? 1;
				woocommerce_wp_text_input(
					array(
						'id'                => 'vicatna_so_min',
						'name'              => '',
						'custom_attributes' => array(
							'data-name' => 'vicatna_so_min',
							'min'       => '1',
							'max'       => $so_type ? '100' : ''
						),
						'value'             => $so_min,
						'label'             => esc_html__( 'Maximum discount value', 'catna-woo-name-your-price-and-offers' ),
						'data_type'         => 'price',
					)
				);
				woocommerce_wp_select(
					array(
						'id'                => 'vicatna_so_type',
						'name'              => '',
						'custom_attributes' => array( 'data-name' => 'vicatna_so_type' ),
						'label'             => esc_html__( 'Base on', 'catna-woo-name-your-price-and-offers' ),
						'desc_tip'          => true,
						'options'           => array(
							0 => esc_html__( 'Fixed amount', 'catna-woo-name-your-price-and-offers' ),
							1 => esc_html__( 'Percentage', 'catna-woo-name-your-price-and-offers' ),
						),
						'value'             => $so_type,
					)
				);
				?>
            </div>
            <div class="vicatna-settings-so-qty-enable<?php echo esc_attr( $so_qty ? '' : ' vicatna-disabled' ); ?>">
				<?php
				$so_qty_from  = $params['so_qty_from'] ?? array( 1 );
				$so_qty_to    = $params['so_qty_to'] ?? array( '' );
				$so_qty_min   = $params['so_qty_min'] ?? array( 10 );
				$so_qty_type  = $params['so_qty_type'] ?? array( 1 );
				$accept_title = esc_html__( 'Accept', 'catna-woo-name-your-price-and-offers' ) . '(' . $currency_symbol . ')';
				?>
                <div class="vicatna-settings-so-qty-wrap">
					<?php
					foreach ( $so_qty_from as $i => $qty_from ) {
						$so_qty_type_t = $so_qty_type[ $i ] ?? 1;
						?>
                        <div class="vicatna-so-qty-wrap">
                            <span class="vicatna-so-qty-action vicatna-so-qty-move"><i
                                        class="dashicons dashicons-menu-alt"></i></span>
                            <div class="vicatna-so-qty-condition-wrap">
                                <div class="vicatna-so-qty-condition vicatna-so-qty-condition-left vicatna-so-qty-from-wrap">
                                    <div class="vicatna-so-qty-condition-label"><?php esc_html_e( 'Qty from', 'catna-woo-name-your-price-and-offers' ); ?></div>
                                    <input type="number" min="1" name="" data-name="vicatna_so_qty_from[]"
                                           class="vicatna-so-qty-from"
                                           value="<?php echo esc_attr( $so_qty_from[ $i ] ?? 1 ); ?>">
                                    <div class="vicatna-so-qty-condition-label"><?php esc_html_e( 'to', 'catna-woo-name-your-price-and-offers' ); ?></div>
                                    <input type="number" min="1" name="" data-name="vicatna_so_qty_to[]"
                                           data-allow_empty="1"
                                           data-tip="<?php esc_attr_e( 'Leave blank to not limit this', 'catna-woo-name-your-price-and-offers' ); ?>"
                                           class="vicatna-so-qty-to tips"
                                           value="<?php echo esc_attr( $so_qty_to[ $i ] ?? 1 ); ?>">
                                </div>
                                <div class="vicatna-so-qty-condition vicatna-so-qty-condition-right vicatna-so-qty-min_price tips"
                                     data-tip="<?php esc_attr_e( 'Maximum discount value', 'catna-woo-name-your-price-and-offers' ); ?>">
                                    <div class="vicatna-so-qty-condition-label"><?php esc_html_e( 'Value', 'catna-woo-name-your-price-and-offers' ); ?></div>
                                    <input type="number" min="1" name="" data-name="vicatna_so_qty_min[]"
                                           class="vicatna-so-qty-min"
                                           max="<?php echo esc_attr( $so_qty_type_t ? '100' : '' ); ?>"
                                           value="<?php echo esc_attr( $so_qty_min[ $i ] ?? '' ); ?>">
                                    <select name="" data-name="vicatna_so_qty_type[]" class="vicatna-so-qty-type">
                                        <option value="0" <?php selected( $so_qty_type_t, 0 ) ?>>
											<?php esc_html_e( 'Fixed amount', 'catna-woo-name-your-price-and-offers' ); ?>
                                        </option>
                                        <option value="1" <?php selected( $so_qty_type_t, 1 ) ?>>
											<?php esc_html_e( 'Percentage', 'catna-woo-name-your-price-and-offers' ); ?>
                                        </option>
                                    </select>
                                </div>
                                <div class="vicatna-so-qty-condition vicatna-so-qty-condition-right vicatna-so-qty-condition-accept-price-wrap vicatna-disabled">
                                    <div class="vicatna-so-qty-condition-label">
										<?php echo wp_kses_post( $accept_title ); ?>
                                    </div>
                                    <div class="vicatna-so-qty-condition-accept-price">
                                        <span class="vicatna-so-qty-condition-accept vicatna-so-qty-condition-accept-min"></span>
                                        <span class="vicatna-so-qty-condition-accept dashicons dashicons-minus"></span>
                                        <span class="vicatna-so-qty-condition-accept vicatna-so-qty-condition-accept-max"></span>
                                    </div>
                                </div>
                            </div>
                            <span class="vicatna-so-qty-action vicatna-so-qty-clone tips"
                                  data-tip="<?php esc_attr_e( 'Clone', 'catna-woo-name-your-price-and-offers' ); ?>">
                                <i class="dashicons dashicons-admin-page"></i>
                            </span>
                            <span class="vicatna-so-qty-action vicatna-so-qty-remove tips"
                                  data-tip="<?php esc_attr_e( 'Remove', 'catna-woo-name-your-price-and-offers' ); ?>">
                                <i class="dashicons dashicons-no-alt"></i>
                            </span>
                        </div>
						<?php
					}
					?>
                </div>
            </div>
        </div>
		<?php
	}

	public function vicatna_type_column( $cols ) {
		$cols['vicatna_type'] = esc_html__( 'Name your price & smart offers', 'catna-woo-name-your-price-and-offers' );

		return $cols;
	}

	public function column_callback_product( $col, $post_id ) {
		if ( 'vicatna_type' != $col || ! $post_id ) {
			return;
		}
		$product = wc_get_product( $post_id );
		if ( ! $product ) {
			return;
		}
		if ( ! in_array( $product->get_type(), [ 'variable' ], true ) && $settings = get_post_meta( $post_id, 'vicatna_settings', true ) ) {
			$type = $settings['type'] ?? '';
			if ( '0' === $type ) {
				esc_html_e( 'Name Your Price', 'catna-woo-name-your-price-and-offers' );
			} elseif ( '1' === $type ) {
				esc_html_e( 'Smart Offer', 'catna-woo-name-your-price-and-offers' );
			}
		} elseif ( $settings_variation = get_post_meta( $post_id, 'vicatna_settings_type', true ) ) {
			$settings_variation = array_values( $settings_variation );
			$is_nyp             = in_array( '0', $settings_variation, true );
			$is_so              = in_array( '1', $settings_variation, true );
			$both               = $is_nyp && $is_so;
			if ( $both ) {
				esc_html_e( 'Name Your Price, Smart Offer', 'catna-woo-name-your-price-and-offers' );
			} elseif ( $is_nyp ) {
				esc_html_e( 'Name Your Price', 'catna-woo-name-your-price-and-offers' );
			} elseif ( $is_so ) {
				esc_html_e( 'Smart Offer', 'catna-woo-name-your-price-and-offers' );
			}
		}
	}

	public function admin_enqueue_scripts() {
		$screen = get_current_screen();
		$admin  = 'VICATNA_Admin_Settings';
		if ( 'edit-product' == $screen->id ) {
			$admin::enqueue_style(
				array( 'vicatna-admin-product' ),
				array( 'admin-product.css' )
			);
		}
		if ( 'product' == $screen->id ) {
			$admin::enqueue_style(
				array( 'vicatna-admin-product' ),
				array( 'admin-product.css' )
			);
			$admin::enqueue_script(
				array( 'vicatna-admin-product' ),
				array( 'admin-product.js' ),
				array( array( 'jquery', 'jquery-ui-sortable' ) )
			);
			wp_localize_script( 'vicatna-admin-product', 'vicatna_admin_product',
				array(
					'i18n_nyp_min_greater_than_nyp_max'    => esc_html__( 'Please enter in a value less than Maximum price.', 'catna-woo-name-your-price-and-offers' ),
					'i18n_nyp_max_less_than_nyp_min'       => esc_html__( 'Please enter in a value greater than Minimum price.', 'catna-woo-name-your-price-and-offers' ),
					'i18n_nyp_max_less_than_regular_price' => esc_html__( 'Please enter in a value greater than regular price.', 'catna-woo-name-your-price-and-offers' ),
					'i18n_so_qty_greater_max_percentage'   => esc_html__( 'Please enter in a value not greater than 100 percentage.', 'catna-woo-name-your-price-and-offers' ),
					'i18n_so_qty_less_than_from_qty'       => esc_html__( 'Please enter in a value greater than the Qty from value', 'catna-woo-name-your-price-and-offers' ),
					'i18n_so_qty_greater_than_to_qty'      => esc_html__( 'Please enter in a value less than the Qty to value', 'catna-woo-name-your-price-and-offers' ),
					'vicatna_i18n_regular_empty'           => esc_html__( 'Please enter in a value of the regular price.', 'catna-woo-name-your-price-and-offers' ),
					'price_decimals'                       => wc_get_price_decimals()
				)
			);
		}
	}
}
