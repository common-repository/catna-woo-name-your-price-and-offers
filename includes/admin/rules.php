<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VICATNA_Admin_Rules {
	function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 30 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), PHP_INT_MAX );
	}
	public function admin_menu() {
		add_submenu_page(
			'catna-woocommerce-name-your-price-and-offers',
			esc_html__( 'Global Rules', 'catna-woo-name-your-price-and-offers' ),
			esc_html__( 'Global Rules', 'catna-woo-name-your-price-and-offers' ),
			apply_filters( 'vicatna_change_role', 'manage_woocommerce' ),
			'catna-woocommerce-name-your-price-and-offers-rules',
			array( $this, 'settings_callback' )
		);
	}
	public function settings_callback() {
		?>
        <div class="wrap">
            <h2><?php esc_html_e( 'Global Rules', 'catna-woo-name-your-price-and-offers' ); ?></h2>
            <div class="vi-ui yellow message">
                <p>
					<?php
					esc_html_e( 'This feature allows ou to generate as many name-your-price or smart offer rules as you want that match product conditions or customer conditions set. It helps save time to set minimum acceptable prices and maximum acceptable prices for products or customers that matched the rules', 'catna-woo-name-your-price-and-offers' );
					?>
                </p>
                <p>
	                <?php
	                esc_html_e( 'If Name your price or Offers have not been set on the product editing page yet, the first matched rule( from top to bottom) will be applied', 'catna-woo-name-your-price-and-offers' );
	                ?>
                </p>
                <p>
                    <a class="vi-ui button" href="https://1.envato.market/kjamOx"
                       target="_blank"><?php esc_html_e( 'Unlock This Feature', 'catna-woo-name-your-price-and-offers' ); ?> </a>
                </p>
            </div>
            <div class="vi-ui segment vicatna-img-preview-wrap">
                <a class="vi-ui vicatna-img-preview" href="https://1.envato.market/kjamOx"
                   target="_blank" title="<?php esc_attr_e( 'Unlock This Feature', 'catna-woo-name-your-price-and-offers' ); ?>">
                    <img class="gloabal_rules_settings" src="<?php echo esc_url( VICATNA_IMAGES . 'gloabal_rules_settings.gif' ) ?>" alt="gloabal_rules_settings">
                </a>
            </div>
        </div>
		<?php
	}
	public function admin_enqueue_scripts() {
        $screen_id = get_current_screen()->id;
		if ( 'catna_page_catna-woocommerce-name-your-price-and-offers-rules' === $screen_id ) {
			$admin = 'VICATNA_Admin_Settings';
			$admin::remove_other_script();
			$admin::enqueue_style(
				array( 'vicatna-admin-settings', 'semantic-ui-button', 'semantic-ui-message', 'semantic-ui-popup', 'semantic-ui-segment' ),
				array( 'admin-settings.' . VICATNA_SUFFIX . 'css', 'button.min.css', 'message.min.css', 'popup.min.css', 'segment.min.css' )
			);
		}
	}
}