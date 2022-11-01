<?php
/**
 * Main class for Stock Manager.
 *
 * @package  woocommerce-stock-manager/admin/
 * @version  2.8.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for Stock Manager Admin.
 */
class Stock_Manager_Admin {

	/**
	 * Instance of this class.
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a settings page and menu.
	 */
	private function __construct() {

		// Set page.
		$this->page = ( ! empty( $_GET['page'] ) ) ? wc_clean( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore

		// For stock log history page.
		$this->product_id = ( ! empty( $_GET['product-history'] ) ) ? wc_clean( wp_unslash( $_GET['product-history'] ) ) : 0; // phpcs:ignore

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		include_once 'includes/class-wsm-stock.php';

		add_action( 'admin_notices', array( $this, 'includes' ) );

		// To update footer text on WSM screens.
		add_filter( 'admin_footer_text', array( $this, 'wsm_footer_text' ), 99999 );
		add_filter( 'update_footer', array( $this, 'wsm_update_footer_text' ), 99999 );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Include required core files used in admin.
	 */
	public function includes() {
		$is_wsm_admin = is_wsm_admin_page();
		if ( $is_wsm_admin ) {
			$this->wsm_add_subscribe_notice();

		}
	}

	/**
	 * Get stock class
	 *
	 * @return WSM_Stock
	 */
	public function stock() {
		return WSM_Stock::get_instance();
	}

	/**
	 * Register and enqueue admin-specific CSS.
	 */
	public function enqueue_admin_styles() {
		if ( ( 'stock-manager' === $this->page || 'stock-manager-import-export' === $this->page || 'stock-manager-log' === $this->page ) ) {
			wp_enqueue_style( 'woocommerce-stock-manager-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), WSM_PLUGIN_VERSION );

			$old_styles = get_option( 'woocommerce_stock_old_styles' );
			if ( ! empty( $old_styles ) && 'ok' === $old_styles ) {
				wp_enqueue_style( 'woocommerce-stock-manager-old-styles', plugins_url( 'assets/css/old.css', __FILE__ ), array(), WSM_PLUGIN_VERSION );
			}
		}
	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 */
	public function enqueue_admin_scripts() {
		if ( ( 'stock-manager' === $this->page || 'stock-manager-import-export' === $this->page ) ) {
			$low_stock_threshold = get_option( 'woocommerce_notify_low_stock_amount', 5 );
			$low_stock_threshold = ( ! empty( $low_stock_threshold ) ) ? $low_stock_threshold : 5;

			wp_enqueue_style( 'woocommerce-stock-manager-admin-script-react', plugins_url( 'assets/build/index.css', __FILE__ ), array(), WSM_PLUGIN_VERSION );
			wp_enqueue_script( 'woocommerce-stock-manager-admin-script-react', plugins_url( 'assets/build/index.js', __FILE__ ), array( 'wp-polyfill', 'wp-i18n', 'wp-url' ), WSM_PLUGIN_VERSION, true );
			wp_localize_script(
				'woocommerce-stock-manager-admin-script-react',
				'WooCommerceStockManagerPreloadedState',
				array(
					'app'                  => array(
						'textDomain'        => 'woocommerce-stock-manager',
						'root'              => esc_url_raw( rest_url() ),
						'adminUrl'          => admin_url(),
						'nonce'             => wp_create_nonce( 'wp_rest' ),
						'perPage'           => apply_filters( 'woocommerce_stock_manager_per_page', 50 ),
						'lowStockThreshold' => $low_stock_threshold,
					),
					'product-categories'   => array_reduce(
						get_terms(
							array(
								'taxonomy'   => 'product_cat',
								'hide_empty' => false,
							)
						),
						function( $carry, $item ) {
							$carry[ $item->term_id ] = $item->name;
							return $carry;
						},
						array()
					),
					'product-types'        => wc_get_product_types(),
					'stock-status-options' => wc_get_product_stock_status_options(),
					'shipping-classes'     => array_merge(
						array( '' => __( 'No shipping class', 'woocommerce-stock-manager' ) ),
						array_reduce(
							get_terms(
								array(
									'taxonomy'   => 'product_shipping_class',
									'hide_empty' => false,
								)
							),
							function( $carry, $item ) {
								$carry[ $item->slug ] = $item->name;
								return $carry;
							},
							array()
						)
					),
					'tax-classes'          => wc_get_product_tax_class_options(),
					'tax-statuses'         => array(
						'taxable'  => __( 'Taxable', 'woocommerce-stock-manager' ),
						'shipping' => __( 'Shipping only', 'woocommerce-stock-manager' ),
						'none'     => _x( 'None', 'Tax status', 'woocommerce-stock-manager' ),
					),
					'backorders-options'   => array(
						'no'     => __( 'No', 'woocommerce-stock-manager' ),
						'notify' => __( 'Notify', 'woocommerce-stock-manager' ),
						'yes'    => __( 'Yes', 'woocommerce-stock-manager' ),
					),
				)
			);

			wp_set_script_translations( 'woocommerce-stock-manager-admin-script-react', 'stock-manager', STOCKDIR . 'languages' );
		}

		// Klawoo subscribe.
		$wsm_dismiss_admin_notice = get_option( 'wsm_dismiss_subscribe_admin_notice', false );
		if ( empty( $wsm_dismiss_admin_notice ) ) {
			$is_wsm_admin = is_wsm_admin_page();
			if ( $is_wsm_admin ) {
				$params = array(
					'ajax_nonce' => wp_create_nonce( 'wsm_update' ),
				);
				wp_localize_script( 'woocommerce-stock-manager-admin-script-w', 'ajax_object', $params );
				wp_enqueue_script( 'woocommerce-stock-manager-admin-script-w', plugins_url( 'assets/js/subscribe.js', __FILE__ ), array( 'jquery' ), WSM_PLUGIN_VERSION, true );

			}
		}
	}

	/**
	 * Function to get menu position for Stock Manager.
	 *
	 * @param double $start     Starting position.
	 * @param double $increment Increment by.
	 *
	 * @return double Final menu position.
	 */
	public function get_free_menu_position( $start, $increment = 0.0001 ) {
		foreach ( $GLOBALS['menu'] as $key => $menu ) {
			$menus_positions[] = $key;
		}

		if ( ! in_array( $start, $menus_positions, true ) ) {
			return $start;
		}

		/* the position is already reserved find the closet one */
		while ( in_array( $start, $menus_positions, true ) ) {
			$start += $increment;
		}

		return $start;
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 */
	public function add_plugin_admin_menu() {

		$value = 'manage_woocommerce';

		$manage = apply_filters( 'stock_manager_manage', $value );

		$position = (string) $this->get_free_menu_position( 58.00001 );

		$hook = add_menu_page(
			__( 'Stock Manager', 'woocommerce-stock-manager' ),
			__( 'Stock Manager', 'woocommerce-stock-manager' ),
			$manage,
			'stock-manager',
			array( $this, 'display_plugin_admin_page' ),
			'dashicons-book-alt',
			$position
		);

		// Show screen option for React App.
		add_action(
			'load-' . $hook,
			function() {
				add_filter(
					'screen_options_show_screen',
					function () {
						return true;
					}
				);
			}
		);

		add_submenu_page(
			'stock-manager',
			__( 'Import/Export', 'woocommerce-stock-manager' ),
			__( 'Import/Export', 'woocommerce-stock-manager' ),
			$manage,
			'stock-manager-import-export',
			array( $this, 'display_import_export_page' )
		);
		add_submenu_page(
			'stock-manager',
			__( 'Stock log', 'woocommerce-stock-manager' ),
			__( 'Stock log', 'woocommerce-stock-manager' ),
			$manage,
			'stock-manager-log',
			array( $this, 'display_log_page' )
		);
		add_submenu_page(
			'stock-manager',
			__( 'Stock Manager Setting', 'woocommerce-stock-manager' ),
			__( 'Setting', 'woocommerce-stock-manager' ),
			$manage,
			'stock-manager-setting',
			array( $this, 'display_setting_page' )
		);
		add_submenu_page(
			'stock-manager',
			__( 'StoreApps Plugins', 'woocommerce-stock-manager' ),
			__( 'StoreApps Plugins', 'woocommerce-stock-manager' ),
			$manage,
			'stock-manager-storeapps-plugins',
			array( $this, 'display_sa_marketplace_page' )
		);

	}

	/**
	 * Render the settings page for this plugin.
	 */
	public function display_plugin_admin_page() {
		include_once 'views/admin.php';
	}

	/**
	 * Render the impoer export page for this plugin.
	 */
	public function display_import_export_page() {
		include_once 'views/import-export.php';
	}

	/**
	 * Render the setting page for this plugin.
	 */
	public function display_setting_page() {
		include_once 'views/setting.php';
	}

	/**
	 * Render the StoreApps Marketplace page.
	 */
	public function display_sa_marketplace_page() {
		include_once STOCKDIR . 'sa-includes/class-wsm-storeapps-marketplace.php';
		WSM_StoreApps_Marketplace::init();
	}

	/**
	 * Render the setting page for this plugin.
	 */
	public function display_log_page() {
		if ( ! empty( $this->product_id ) ) { // If found, we are on stock log history page.
			include_once 'views/log-history.php';
		} else {
			include_once 'views/log.php';
		}
	}

	/**
	 * Function to show notice in the admin.
	 */
	public function wsm_add_subscribe_notice() {
		$wsm_dismiss_admin_notice = get_option( 'wsm_dismiss_subscribe_admin_notice', false );

		if ( empty( $wsm_dismiss_admin_notice ) ) {
			?>
			<style type="text/css" class="wsm-subscribe">
				#wsm_promo_msg {
					display: block !important;
					background-color: #f2f6fc;
					border-left-color: #5850ec;
				}
				#wsm_promo_msg table {
					width: 100%;
					padding-bottom: 0.25em;
				}
				#wsm_dashicon {
					padding: 0.5em;
					width: 3%;
				}
				#wsm_promo_msg_content {
					padding: 0.5em;
				}
				#wsm_promo_msg .dashicons.dashicons-awards {
					font-size: 5em;
					color: #b08d57;
					margin-left: -0.2em;
					margin-bottom: 0.65em;
				}
				.wsm_headline {
					padding: 0.5em 0;
					font-size: 1.4em;
				}
				form.wsm_klawoo_subscribe {
					padding: 0.5em 0;
					margin-block-end: 0 !important;
					font-size: 1.1em;
				}
				form.wsm_klawoo_subscribe #email {
					width: 14em;
					height: 1.75em;
				}
				form.wsm_klawoo_subscribe #wsm_gdpr_agree {
					margin-left: 0.5em;
					vertical-align: sub;
				}
				form.wsm_klawoo_subscribe .wsm_gdpr_label {
					margin-right: 0.5em;
				}
				form.wsm_klawoo_subscribe #wsm_submit {
					font-size: 1.3em;
					line-height: 0em;
					margin-top: 0;
					font-weight: bold;
					background: #5850ec;
					border-color: #5850ec;
				}
				.wsm_success {
					font-size: 1.5em;
					font-weight: bold;
				}
			</style>
			<div id="wsm_promo_msg" class="updated fade">
				<table>
					<tbody> 
						<tr>
							<td id="wsm_dashicon"> 
								<span class="dashicons dashicons-awards"></span>
							</td> 
							<td id="wsm_promo_msg_content">
								<div class="wsm_headline">Get latest hacks & tips to better manage your store using Stock Manager for WooCommerce!</div>
								<form name="wsm_klawoo_subscribe" class="wsm_klawoo_subscribe" action="#" method="POST" accept-charset="utf-8">									
									<input type="email" class="regular-text ltr" name="email" id="email" placeholder="Your email address" required="required" />
									<input type="checkbox" name="wsm_gdpr_agree" id="wsm_gdpr_agree" value="1" required="required" />
									<label for="wsm_gdpr_agree" class="wsm_gdpr_label">I have read and agreed to your <a href="https://www.storeapps.org/privacy-policy/?utm_source=wsm&utm_medium=in_app_subscribe&utm_campaign=in_app_subscribe" target="_blank">Privacy Policy</a>.</label>
									<input type="hidden" name="list" value="3pFQTnTsH763gAKTuvOGhPzA"/>
									<?php wp_nonce_field( 'sa-wsm-subscribe', 'sa_wsm_sub_nonce' ); ?>
									<input type="submit" name="submit" id="wsm_submit" class="button button-primary" value="Subscribe" />
								</form>
							</td>
							</tr>
					</tbody> 
				</table> 
			</div>
			<?php
		}
	}

	/**
	 * Function to ask to review the plugin in footer
	 *
	 * @param  string $wsm_rating_text Text in footer (left).
	 * @return string $wsm_rating_text
	 */
	public function wsm_footer_text( $wsm_rating_text ) {

		$is_wsm_admin = is_wsm_admin_page();
		if ( $is_wsm_admin ) {
			/* translators: %1$s & %2$s: Opening & closing strong tag. %3$s: link to Stock Manager for WooCommerce on WordPress.org */
			$wsm_rating_text = sprintf( __( 'If you are liking %1$sStock Manager for WooCommerce%2$s, please rate us %3$s. A huge thanks from StoreApps in advance!', 'woocommerce-stock-manager' ), '<strong>', '</strong>', '<a target="_blank" href="' . esc_url( 'https://wordpress.org/support/plugin/woocommerce-stock-manager/reviews/?filter=5' ) . '" style="color: #5850EC;">5-star</a>' );
		}

		return $wsm_rating_text;

	}

	/**
	 * Function to show installed version of the plugin
	 *
	 * @param  string $wsm_text Text in footer (right).
	 * @return string $wsm_text
	 */
	public function wsm_update_footer_text( $wsm_text ) {

		$is_wsm_admin = is_wsm_admin_page();
		if ( $is_wsm_admin ) {
			$wsm_text = 'Installed Version: ' . WSM_PLUGIN_VERSION;
			?>
			<style type="text/css">
				#wpfooter {
					position: unset;
				}
				#wpfooter #footer-upgrade {
					color: #5850EC;
				}
			</style>
			<?php
		}

		return $wsm_text;

	}

}//end class
