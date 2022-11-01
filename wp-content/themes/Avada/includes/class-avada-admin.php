<?php
/**
 * A class to manage various stuff in the WordPress admin area.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      3.8
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * A class to manage various stuff in the WordPress admin area.
 */
class Avada_Admin {

	/**
	 * Holds the current theme version.
	 *
	 * @since 5.0.0
	 *
	 * @access private
	 * @var string
	 */
	private $theme_version;

	/**
	 * Holds the WP_Theme object of Avada.
	 *
	 * @since 5.0.0
	 *
	 * @access private
	 * @var WP_Theme object
	 */
	private $theme_object;

	/**
	 * Holds the URL to the Avada live demo site.
	 *
	 * @since 5.0.0
	 *
	 * @access private
	 * @var string
	 */
	private $theme_url = 'https://avada.theme-fusion.com/';

	/**
	 * Holds the URL to ThemeFusion company site.
	 *
	 * @static
	 * @since 5.0.0
	 * @access public
	 * @var string
	 */
	public static $theme_fusion_url = 'https://theme-fusion.com/';

	/**
	 * Normalized path to includes folder.
	 *
	 * @since 5.1.0
	 *
	 * @access private
	 * @var string
	 */
	private $includes_path = '';

	/**
	 * HubSpot code.
	 *
	 * @since 7.0.2
	 * @access public
	 * @var string
	 */
	public $hubspot_code = 'rnE3nv';

	/**
	 * Construct the admin object.
	 *
	 * @since 3.9.0
	 * @return void
	 */
	public function __construct() {

		$this->includes_path = wp_normalize_path( dirname( __FILE__ ) );

		$this->set_theme_version();
		$this->set_theme_object();

		$this->register_product_envato_hosted();

		add_action( 'admin_init', [ $this, 'admin_init' ] );
		add_action( 'admin_init', [ $this, 'init_permalink_settings' ] );
		add_action( 'admin_init', [ $this, 'save_permalink_settings' ] );
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
		add_action( 'admin_menu', [ $this, 'edit_admin_menus' ], 999 );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ] );
		add_action( 'after_switch_theme', [ $this, 'activation_redirect' ] );

		// Add dashboard header to TO page.
		global $avada_avadaredux_args;

		add_action( 'fusionredux/page/' . $avada_avadaredux_args['option_name'] . '/form/before', [ 'Avada_Admin', 'get_admin_screen_header_to' ] );

		add_action( 'fusionredux/page/' . $avada_avadaredux_args['option_name'] . '/form/after', [ 'Avada_Admin', 'get_admin_screen_footer_to' ] );

		add_filter( 'tgmpa_notice_action_links', [ $this, 'edit_tgmpa_notice_action_links' ] );
		$prefix = ( defined( 'WP_NETWORK_ADMIN' ) && WP_NETWORK_ADMIN ) ? 'network_admin_' : '';
		add_filter( "tgmpa_{$prefix}plugin_action_links", [ $this, 'edit_tgmpa_action_links' ], 10, 4 );

		// Get demos data on theme activation.
		if ( ! class_exists( 'Avada_Importer_Data' ) ) {
			include_once Avada::$template_dir_path . '/includes/importer/class-avada-importer-data.php';
		}
		add_action( 'after_switch_theme', [ 'Avada_Importer_Data', 'get_data' ], 5 );

		// Change auto update notes for LayerSlider.
		add_action( 'layerslider_ready', [ $this, 'layerslider_overrides' ] );

		// Facebook instant articles rule set definition.
		add_filter( 'instant_articles_transformer_rules_loaded', [ $this, 'add_instant_article_rules' ] );

		// Load jQuery in the demos and plugins page.
		if ( isset( $_GET['page'] ) && ( 'avada-prebuilt-websites' === $_GET['page'] || 'avada-plugins' === $_GET['page'] || 'avada-setup' === $_GET['page'] ) ) { // phpcs:ignore WordPress.Security
			add_action( 'admin_enqueue_scripts', [ $this, 'add_jquery' ] );

			if ( 'avada-plugins' === $_GET['page'] ) { // phpcs:ignore WordPress.Security
				add_action( 'admin_enqueue_scripts', [ $this, 'add_jquery_ui_styles' ] );
			}
		}

		add_action( 'wp_ajax_fusion_activate_plugin', [ $this, 'ajax_activate_plugin' ] );
		// By default TGMPA doesn't load in AJAX calls.
		// Filter is applied inside a method which is hooked to 'init'.
		add_filter( 'tgmpa_load', [ $this, 'enable_tgmpa' ], 10 );

		add_action( 'wp_ajax_fusion_install_plugin', [ $this, 'ajax_install_plugin' ] );

		// Add taxonomy meta boxes.
		if ( function_exists( 'update_term_meta' ) ) {
			add_action( 'wp_loaded', [ $this, 'avada_taxonomy_meta' ] );
		}

		// Notice for legacy countdown.
		add_action( 'current_screen', [ $this, 'legacy_countdown' ] );

		// Performance wizard, both needed for page and wizard ajax.
		if ( ( isset( $_GET['page'] ) && 'avada-performance' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) || ( fusion_doing_ajax() && ( isset( $_GET['awb_performance_nonce'] ) || isset( $_POST['awb_performance_nonce'] ) ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended
			$this->init_performance_wizard();
		}

		// Setup wizard, both needed for page and wizard ajax.
		if ( ( isset( $_GET['page'] ) && 'avada-setup' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) || ( fusion_doing_ajax() && ( isset( $_GET['awb_setup_nonce'] ) || isset( $_POST['awb_setup_nonce'] ) ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended
			$this->init_setup_wizard();
		}

		add_filter( 'leadin_impact_code', [ $this, 'get_hubspot_affiliate_code' ] );
	}

	/**
	 * Require and instantiate performance wizard.
	 *
	 * @access public
	 * @since 5.0.0
	 * @return void
	 */
	public function init_performance_wizard() {
		require_once $this->includes_path . '/class-awb-performance-wizard.php';
	}

	/**
	 * Require and instantiate setup wizard.
	 *
	 * @access public
	 * @since 7.5
	 * @return void
	 */
	public function init_setup_wizard() {
		require_once $this->includes_path . '/class-awb-setup-wizard.php';
	}

	/**
	 * Adds jQuery.
	 *
	 * @access public
	 * @since 5.0.0
	 * @return void
	 */
	public function add_jquery() {
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-dialog' );
	}

	/**
	 * Adds jQuery UI styles.
	 *
	 * @access public
	 * @since 5.4.1
	 * @return void
	 */
	public function add_jquery_ui_styles() {
		wp_enqueue_style( 'wp-jquery-ui-dialog' );
	}

	/**
	 * Modify the menu.
	 *
	 * @access public
	 * @since 3.8.0
	 * @return void
	 */
	public function edit_admin_menus() {
		global $submenu;

		// Change Avada to Dashboard.
		if ( current_user_can( 'switch_themes' ) ) {
			$submenu['avada'][0][0] = esc_html__( 'Dashboard', 'Avada' ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
		}

		if ( isset( $submenu['themes.php'] ) && ! empty( $submenu['themes.php'] ) ) {
			foreach ( $submenu['themes.php'] as $key => $value ) {
				// Remove "Header" submenu.
				if ( isset( $value[2] ) && false !== strpos( $value[2], 'customize.php' ) && false !== strpos( $value[2], '=header_image' ) ) {
					unset( $submenu['themes.php'][ $key ] );
				}
				// Remove "Background" submenu.
				if ( isset( $value[2] ) && false !== strpos( $value[2], 'customize.php' ) && false !== strpos( $value[2], '=background_image' ) ) {
					unset( $submenu['themes.php'][ $key ] );
				}
			}

			// Reorder items in the array.
			$submenu['themes.php'] = array_values( $submenu['themes.php'] ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
		}

		// Remove TGMPA menu from Appearance.
		remove_submenu_page( 'themes.php', 'install-required-plugins' );

	}

	/**
	 * Redirect to admin page on theme activation.
	 *
	 * @access public
	 * @since 3.8.0
	 * @return void
	 */
	public function activation_redirect() {

		delete_transient( 'awb_cli_activation' );

		if ( current_user_can( 'switch_themes' ) ) {

			$current_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : false; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			// Early exit if already on desired pages. CLI theme activation triggers the hook multiple times.
			if ( 'avada' === $current_page || 'avada-setup' === $current_page ) {
				return;
			}

			// Fresh install transient is set in class-avada-upgrade.php.
			$fresh_install = get_transient( 'awb_fresh_install' );

			if ( $fresh_install && 'fresh' === $fresh_install && ! wp_doing_ajax() && ! is_network_admin() && apply_filters( 'awb_setup_wizard_redirect', true ) ) {
				delete_transient( 'awb_fresh_install' );
				wp_safe_redirect( admin_url( 'admin.php?page=avada-setup' ) );
				exit;
			} elseif ( true !== Fusion_Builder_Migrate::needs_migration() ) { // Do not redirect if a migration is needed for Avada 5.0.0.
				wp_safe_redirect( admin_url( 'admin.php?page=avada' ) );
				exit;
			}
		}
	}

	/**
	 * Actions to run on initial theme activation.
	 *
	 * @access public
	 * @since 3.8.0
	 * @return void
	 */
	public function admin_init() {

		if ( current_user_can( 'switch_themes' ) ) {

			// Set in register.php, function fusion_register_cmd().
			if ( get_transient( 'awb_cli_activation' ) ) {
				$this->activation_redirect();
			}

			if ( isset( $_GET['avada-deactivate'] ) && 'deactivate-plugin' === $_GET['avada-deactivate'] ) { // phpcs:ignore WordPress.Security
				check_admin_referer( 'avada-deactivate', 'avada-deactivate-nonce' );

				$plugins = Avada_TGM_Plugin_Activation::$instance->plugins;

				foreach ( $plugins as $plugin ) {
					if ( isset( $_GET['plugin'] ) && $plugin['slug'] === $_GET['plugin'] ) {
						deactivate_plugins( $plugin['file_path'] );
					}
				}
			}
			if ( isset( $_GET['avada-activate'] ) && 'activate-plugin' === $_GET['avada-activate'] ) {
				check_admin_referer( 'avada-activate', 'avada-activate-nonce' );

				$plugins = Avada_TGM_Plugin_Activation::$instance->plugins;

				foreach ( $plugins as $plugin ) {
					if ( isset( $_GET['plugin'] ) && $plugin['slug'] === $_GET['plugin'] ) {
						activate_plugin( $plugin['file_path'] );

						wp_safe_redirect( admin_url( 'admin.php?page=avada-plugins' ) );
						exit;
					}
				}
			}
		}
	}

	/**
	 * Adds the admin menu.
	 *
	 * @access public
	 * @since 5.0
	 * @return void
	 */
	public function admin_menu() {
		global $submenu;

		if ( current_user_can( 'switch_themes' ) ) {

			// Work around for theme check.
			$avada_menu_page_creation_method    = 'add_menu_page';
			$avada_submenu_page_creation_method = 'add_submenu_page';

			$dashboard         = $avada_menu_page_creation_method( 'Avada Website Builder', 'Avada', 'switch_themes', 'avada', [ $this, 'dashboard_screen' ], 'dashicons-avada', '2.111111' );
			$options           = $avada_submenu_page_creation_method( 'avada', esc_html__( 'Options', 'Avada' ), esc_html__( 'Options', 'Avada' ), 'switch_themes', 'themes.php?page=avada_options', '', 2 );
			$prebuilt_websites = $avada_submenu_page_creation_method( 'avada', esc_html__( 'Websites', 'Avada' ), esc_html__( 'Websites', 'Avada' ), 'manage_options', 'avada-prebuilt-websites', [ $this, 'prebuilt_websites_tab' ], 3 );

			// Add in pages from Avada Builder.
			do_action( 'avada_add_admin_menu_pages' );

			$maintenance = $avada_submenu_page_creation_method( 'avada', esc_html__( 'Maintenance', 'Avada' ), esc_html__( 'Maintenance', 'Avada' ), 'manage_options', 'avada-maintenance', null, 12 );

			// Patcher is added in through patcher class, order is 9.
			do_action( 'avada_add_admin_menu_maintenance_pages' );

			$plugins_callback = [ $this, 'plugins_tab' ];
			if ( isset( $_GET['tgmpa-install'] ) || isset( $_GET['tgmpa-update'] ) ) { // phpcs:ignore WordPress.Security
				require_once $this->includes_path . '/class-avada-tgm-plugin-activation.php';
				remove_action( 'admin_notices', [ $GLOBALS['avada_tgmpa'], 'notices' ] );
				$plugins_callback = [ $GLOBALS['avada_tgmpa'], 'install_plugins_page' ];
			}

			$plugins     = $avada_submenu_page_creation_method( 'avada', esc_html__( 'Plugins & Add-ons', 'Avada' ), esc_html__( 'Plugins & Add-ons', 'Avada' ), 'install_plugins', 'avada-plugins', $plugins_callback, 14 );
			$performance = $avada_submenu_page_creation_method( 'avada', esc_html__( 'Performance', 'Avada' ), esc_html__( 'Performance', 'Avada' ), 'switch_themes', 'avada-performance', [ $this, 'performance_tab' ], 15 );
			$support     = $avada_submenu_page_creation_method( 'avada', esc_html__( 'Support', 'Avada' ), esc_html__( 'Support', 'Avada' ), 'manage_options', 'avada-support', [ $this, 'support_tab' ], 17 );
			$status      = $avada_submenu_page_creation_method( 'avada', esc_html__( 'Status', 'Avada' ), esc_html__( 'Status', 'Avada' ), 'switch_themes', 'avada-status', [ $this, 'status_tab' ], 18 );
			$setup       = $avada_submenu_page_creation_method( 'avada', esc_html__( 'Setup', 'Avada' ), esc_html__( 'Setup', 'Avada' ), 'switch_themes', 'avada-setup', [ $this, 'setup_tab' ], 19 );

			if ( ! class_exists( 'FusionReduxFrameworkPlugin' ) ) {
				$theme_options_global = $avada_submenu_page_creation_method( 'themes.php', esc_html__( 'Options', 'Avada' ), esc_html__( 'Options', 'Avada' ), 'switch_themes', 'themes.php?page=avada_options' );
			}

			if ( array_key_exists( 'avada', $submenu ) ) {
				foreach ( $submenu['avada'] as $key => $value ) {
					$k = array_search( 'avada-maintenance', $value, true );
					if ( $k ) {
						$submenu['avada'][ $key ][ $k ] = ( current_user_can( $submenu['avada'][ $key ][1] ) ) ? esc_url( admin_url( 'admin.php?page=avada-patcher' ) ) : ''; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
					}
				}
			}

			add_action( 'admin_print_styles-' . $dashboard, [ $this, 'styles_general' ] );
			add_action( 'admin_print_scripts-' . $dashboard, [ $this, 'scripts_general' ] );

			add_action( 'admin_print_styles', [ $this, 'styles_theme_options' ] );
			add_action( 'admin_print_scripts', [ $this, 'scripts_theme_options' ] );

			add_action( 'admin_print_styles-' . $prebuilt_websites, [ $this, 'styles_general' ] );
			add_action( 'admin_print_scripts-' . $prebuilt_websites, [ $this, 'scripts_general' ] );

			add_action( 'admin_print_styles-' . $maintenance, [ $this, 'styles_general' ] );

			add_action( 'admin_print_styles-' . $plugins, [ $this, 'styles_general' ] );
			add_action( 'admin_print_scripts-' . $plugins, [ $this, 'scripts_general' ] );

			add_action( 'admin_print_styles-' . $support, [ $this, 'styles_general' ] );

			add_action( 'admin_print_styles-' . $status, [ $this, 'styles_general' ] );
			add_action( 'admin_print_scripts-' . $status, [ $this, 'scripts_general' ] );

			add_action( 'admin_print_styles-' . $performance, [ $this, 'styles_general' ] );
			add_action( 'admin_print_scripts-' . $performance, [ $this, 'scripts_general' ] );
			add_action( 'admin_print_styles-' . $setup, [ $this, 'styles_general' ] );
			add_action( 'admin_print_scripts-' . $setup, [ $this, 'scripts_general' ] );

			add_action( 'admin_footer', 'fusion_the_admin_font_async' );
		}
	}

	/**
	 * Include file.
	 *
	 * @access public
	 * @since 5.0
	 * @return void
	 */
	public function dashboard_screen() {
		require_once $this->includes_path . '/admin-screens/dashboard.php';
	}

	/**
	 * Include file.
	 *
	 * @access public
	 * @since 5.0
	 * @return void
	 */
	public function prebuilt_websites_tab() {
		require_once $this->includes_path . '/admin-screens/prebuilt-websites.php';
	}

	/**
	 * Include file.
	 *
	 * @access public
	 * @since 5.0
	 * @return void
	 */
	public function plugins_tab() {
		require_once $this->includes_path . '/admin-screens/plugins.php';
	}

	/**
	 * Include file.
	 *
	 * @access public
	 * @since 5.0
	 * @return void
	 */
	public function support_tab() {
		require_once $this->includes_path . '/admin-screens/support.php';
	}

	/**
	 * Include file.
	 *
	 * @access public
	 * @since 5.0
	 * @return void
	 */
	public function status_tab() {
		require_once $this->includes_path . '/admin-screens/status.php';
	}

	/**
	 * Include file.
	 *
	 * @access public
	 * @since 5.0
	 * @return void
	 */
	public function performance_tab() {
		require_once $this->includes_path . '/admin-screens/performance.php';
	}

	/**
	 * Include file.
	 *
	 * @access public
	 * @since 8.0
	 * @return void
	 */
	public function setup_tab() {
		require_once $this->includes_path . '/admin-screens/setup.php';
	}

	/**
	 * Renders the admin screens header with title, logo and tabs.
	 *
	 * @static
	 * @access public
	 * @since 5.0
	 * @param string $screen The current screen.
	 * @return void
	 */
	public static function get_admin_screens_header( $screen = 'welcome' ) {
		if ( ! current_user_can( 'switch_themes' ) ) {
			return;
		}

		if ( 'welcome' === $screen ) {
			Avada()->registration->check_registration();
		}

		$screen_classes  = 'wrap avada-dashboard avada-db-' . $screen;
		$screen_classes .= Avada()->registration->appear_registered() ? ' avada-registration-completed' : ' avada-registration-pending';
		$screen_classes .= class_exists( 'AWB_Prebuilt_Websites' ) && AWB_Prebuilt_Websites()->are_avada_plugins_active() ? ' avada-plugins-activated' : ' avada-pending-plugins-activation';


		if ( in_array( $screen, [ 'builder-options', 'layout-sections', 'layouts', 'off-canvas', 'icons', 'forms', 'form-entries', 'library' ], true ) ) {
			$screen_classes .= ' fusion-builder-wrap';

			if ( 'builder-options' === $screen ) {
				$screen_classes .= ' fusion-builder-settings';
			}
		} elseif ( in_array( $screen, [ 'sliders', 'slides', 'slide-edit' ], true ) ) {
			$screen_classes .= ' avada-db-edit-screen';
		} else {
			$screen_classes .= ' about-wrap';
		}

		if ( 'setup' === $screen ) {
			AWB_Setup_Wizard()->render_header( $screen_classes );
			return;
		}

		if ( 'performance' === $screen ) {
			AWB_Performance_Wizard()->render_header( $screen_classes );
			return;
		}
		?>
		<div class="<?php echo esc_html( $screen_classes ); ?>">
			<header class="avada-db-header-main">
				<div class="avada-db-header-main-container">
					<a class="avada-db-logo" href="<?php echo esc_url( admin_url( 'admin.php?page=avada' ) ); ?>" aria-label="<?php esc_attr_e( 'Link to Avada dashboard', 'Avada' ); ?>">
						<i class="avada-db-logo-icon fusiona-avada-logo"></i>
						<div class="avada-db-logo-image">
							<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo@2x.png' ); ?>" alt="<?php esc_html_e( 'Avada', 'Avada' ); ?>" width="115" height="25" />
						</div>
					</a>
					<nav class="avada-db-menu-main">
						<ul class="avada-db-menu">
							<li class="avada-db-menu-item avada-db-menu-item-options"><a class="avada-db-menu-item-link<?php echo ( 'to' === $screen || 'builder-options' === $screen ) ? ' avada-db-active' : ''; ?>" href="<?php echo esc_url( ( 'to' === $screen ) ? '#' : admin_url( 'themes.php?page=avada_options' ) ); ?>" ><span class="avada-db-menu-item-text"><?php esc_html_e( 'Options', 'Avada' ); ?></span></a>
								<ul class="avada-db-menu-sub avada-db-menu-sub-options">
									<li class="avada-db-menu-sub-item">
										<a class="avada-db-menu-sub-item-link<?php echo ( 'to' === $screen ) ? ' avada-db-active' : ''; ?>" href="<?php echo esc_url( ( 'to' === $screen ) ? '#' : admin_url( 'themes.php?page=avada_options' ) ); ?>">
											<i class="fusiona-cog"></i>
											<div class="avada-db-menu-sub-item-text">
												<div class="avada-db-menu-sub-item-label"><?php esc_html_e( 'Global Options', 'fusion-builder' ); ?></div>
												<div class="avada-db-menu-sub-item-desc"><?php esc_html_e( 'Edit the global site options & styles.', 'fusion-builder' ); ?></div>
											</div>
										</a>
									</li>
									<?php do_action( 'avada_dashboard_main_menu_options_sub_menu_items', $screen ); ?>
								</ul>
							</li>
							<li class="avada-db-menu-item avada-db-menu-item-prebuilt-websites"><a class="avada-db-menu-item-link<?php echo ( 'prebuilt-websites' === $screen ) ? ' avada-db-active' : ''; ?>" href="<?php echo esc_url( ( 'prebuilt-websites' === $screen ) ? '#' : admin_url( 'admin.php?page=avada-prebuilt-websites' ) ); ?>" ><span class="avada-db-menu-item-text"><?php esc_html_e( 'Websites', 'Avada' ); ?></span></a></li>
							<?php if ( class_exists( 'AWB_Studio' ) && AWB_Studio::is_studio_enabled() && current_user_can( 'switch_themes' ) ) : ?>
								<li class="avada-db-menu-item avada-db-menu-item-avada-studio"><a class="avada-db-menu-item-link<?php echo ( 'studio' === $screen ) ? ' avada-db-active' : ''; ?>" href="<?php echo esc_url( ( 'studio' === $screen ) ? '#' : admin_url( 'admin.php?page=avada-studio' ) ); ?>" ><span class="avada-db-menu-item-text"><?php esc_html_e( 'Studio', 'Avada' ); ?></span></a></li>
							<?php endif; ?>
							<li class="avada-db-menu-item avada-db-menu-item-maintenance"><a class="avada-db-menu-item-link<?php echo ( in_array( $screen, [ 'patcher', 'plugins', 'support', 'status', 'performance' ], true ) ) ? ' avada-db-active' : ''; ?>" href="<?php echo esc_url( ( 'patcher' === $screen ) ? '#' : admin_url( 'admin.php?page=avada-patcher' ) ); ?>"><span class="avada-db-menu-item-text"><?php esc_html_e( 'Maintenance', 'Avada' ); ?></span><span class="avada-db-maintenance-counter"></span></a>
								<ul class="avada-db-menu-sub avada-db-menu-sub-maintenance">
									<li class="avada-db-menu-sub-item avada-db-menu-sub-item-patcher">
										<a class="avada-db-menu-sub-item-link<?php echo ( 'patcher' === $screen ) ? ' avada-db-active' : ''; ?>" href="<?php echo esc_url( ( 'patcher' === $screen ) ? '#' : admin_url( 'admin.php?page=avada-patcher' ) ); ?>">
											<i class="fusiona-patcher"></i>
											<div class="avada-db-menu-sub-item-text">
												<div class="avada-db-menu-sub-item-label"><?php esc_html_e( 'Patcher', 'Avada' ); ?></div>
												<div class="avada-db-menu-sub-item-desc"><?php esc_html_e( 'Apply patches for your version.', 'Avada' ); ?></div>
											</div>
										</a>
									</li>
									<li class="avada-db-menu-sub-item avada-db-menu-sub-item-plugins">
										<a class="avada-db-menu-sub-item-link<?php echo ( 'plugins' === $screen ) ? ' avada-db-active' : ''; ?>" href="<?php echo esc_url( ( 'plugins' === $screen ) ? '#' : admin_url( 'admin.php?page=avada-plugins' ) ); ?>">
											<i class="fusiona-plugins"></i>
											<div class="avada-db-menu-sub-item-text">
												<div class="avada-db-menu-sub-item-label"><?php esc_html_e( 'Plugins & Add-ons', 'Avada' ); ?></div>
												<div class="avada-db-menu-sub-item-desc"><?php esc_html_e( 'Manage plugins & get add-ons.', 'Avada' ); ?></div>
											</div>
										</a>
									</li>
									<li class="avada-db-menu-sub-item avada-db-menu-sub-item-support">
										<a class="avada-db-menu-sub-item-link<?php echo ( 'performance' === $screen ) ? ' avada-db-active' : ''; ?>" href="<?php echo esc_url( ( 'performance' === $screen ) ? '#' : admin_url( 'admin.php?page=avada-performance' ) ); ?>">
											<i class="fusiona-tasks"></i>
											<div class="avada-db-menu-sub-item-text">
												<div class="avada-db-menu-sub-item-label"><?php esc_html_e( 'Performance Wizard', 'Avada' ); ?></div>
												<div class="avada-db-menu-sub-item-desc"><?php esc_html_e( 'Optimize the running of your website', 'Avada' ); ?></div>
											</div>
										</a>
									</li>
									<?php $enable_critical = '1' === Avada()->settings->get( 'critical_css' ) && current_user_can( 'switch_themes' ); ?>
									<?php if ( class_exists( 'FusionBuilder' ) && apply_filters( 'enable_awb_critical_css', $enable_critical ) ) : ?>
										<li class="avada-db-menu-sub-item avada-db-menu-sub-item-critical-css">
											<a class="avada-db-menu-sub-item-link<?php echo ( 'critical' === $screen ) ? ' avada-db-active' : ''; ?>" href="<?php echo esc_url( ( 'critical' === $screen ) ? '#' : admin_url( 'admin.php?page=avada-critical' ) ); ?>">
												<i class="fusiona-search-results"></i>
												<div class="avada-db-menu-sub-item-text">
													<div class="avada-db-menu-sub-item-label"><?php esc_html_e( 'Critical CSS', 'Avada' ); ?></div>
													<div class="avada-db-menu-sub-item-desc"><?php esc_html_e( 'Manage critical CSS generation', 'Avada' ); ?></div>
												</div>
											</a>
									</li>
									<?php endif; ?>
									<li class="avada-db-menu-sub-item avada-db-menu-sub-item-changelog">
										<a class="avada-db-menu-sub-item-link avada-db-changelog-link" href="<?php echo esc_url( get_template_directory_uri() . '/changelog.txt' ); ?>" target="_blank">
											<i class="fusiona-documentation"></i>
											<div class="avada-db-menu-sub-item-text">
												<div class="avada-db-menu-sub-item-label"><?php esc_html_e( 'Changelog', 'Avada' ); ?></div>
												<div class="avada-db-menu-sub-item-desc"><?php esc_html_e( 'View the Avada changelog.', 'Avada' ); ?></div>
											</div>
										</a>
									</li>
									<li class="avada-db-menu-sub-item avada-db-menu-sub-item-support">
										<a class="avada-db-menu-sub-item-link<?php echo ( 'support' === $screen ) ? ' avada-db-active' : ''; ?>" href="<?php echo esc_url( ( 'support' === $screen ) ? '#' : admin_url( 'admin.php?page=avada-support' ) ); ?>">
											<i class="fusiona-help-outlined"></i>
											<div class="avada-db-menu-sub-item-text">
												<div class="avada-db-menu-sub-item-label"><?php esc_html_e( 'Support', 'Avada' ); ?></div>
												<div class="avada-db-menu-sub-item-desc"><?php esc_html_e( 'View the different support channels', 'Avada' ); ?></div>
											</div>
										</a>
									</li>
									<li class="avada-db-menu-sub-item avada-db-menu-sub-item-status">
										<a class="avada-db-menu-sub-item-link<?php echo ( 'status' === $screen ) ? ' avada-db-active' : ''; ?>" href="<?php echo esc_url( ( 'status' === $screen ) ? '#' : admin_url( 'admin.php?page=avada-status' ) ); ?>">
											<i class="fusiona-status"></i>
											<div class="avada-db-menu-sub-item-text">
												<div class="avada-db-menu-sub-item-label"><?php esc_html_e( 'System Status', 'Avada' ); ?></div>
												<div class="avada-db-menu-sub-item-desc"><?php esc_html_e( 'View the system status of your install.', 'Avada' ); ?></div>
											</div>
										</a>
									</li>
								</ul>
							</li>
						</ul>
					</nav>
					<?php if ( class_exists( 'FusionBuilder' ) ) : ?>
					<a class="button button-primary avada-db-live" href="<?php echo esc_url( add_query_arg( 'fb-edit', '1', get_site_url() ) ); ?>"><?php esc_html_e( 'Avada Live', 'Avada' ); ?> </a>
					<?php endif; ?>
				</div>
			</header>

			<header class="avada-db-header-sticky">
				<div class="avada-db-menu-sticky">
					<div class="avada-db-menu-sticky-label<?php echo ( Avada()->registration->appear_registered() ) ? ' completed' : ''; ?>">
						<span class="avada-db-version"><span><?php echo esc_html( apply_filters( 'avada_db_version', 'v' . esc_html( AVADA_VERSION ) ) ); ?></span> |</span>
						<span class="avada-db-version-label avada-db-registered"><?php esc_html_e( 'Registered', 'Avada' ); ?></span>
						<span class="avada-db-version-label avada-db-unregistered"><?php esc_html_e( 'Unregistered', 'Avada' ); ?></span>
					</div>

					<?php if ( class_exists( 'FusionBuilder' ) || class_exists( 'FusionCore_Plugin' ) ) : ?>
						<nav class="avada-db-menu-sticky-nav">
							<ul class="avada-db-menu">
								<?php do_action( 'avada_dashboard_sticky_menu_items', $screen ); ?>
							</ul>
						</nav>
					<?php endif; ?>
				</div>
			</header>

			<div class="avada-db-demos-notices"><h1></h1> <?php do_action( 'avada_dashboard_notices' ); ?></div>
		<?php
	}

	/**
	 * Renders the admin screens footer.
	 *
	 * @static
	 * @access public
	 * @since 7.0
	 * @param string $screen The current screen.
	 * @return void
	 */
	public static function get_admin_screens_footer( $screen = 'dashboard' ) {
		if ( ! current_user_can( 'switch_themes' ) ) {
			return;
		}
		?>
			<?php if ( 'slide-edit' !== $screen ) : ?>
				<footer class="avada-db-footer">
					<div class="avada-db-footer-top">
						<nav class="avada-db-footer-menu">
							<span class="avada-db-footer-company"><i class="fusiona-TFicon"></i><strong><?php esc_html_e( 'ThemeFusion', 'Avada' ); ?></strong></span>
							<ul>
								<li>
									<a href="<?php echo esc_url( self::$theme_fusion_url ) . 'documentation/avada/'; ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Documentation', 'Avada' ); ?></a>
								</li>
								<li>
									<a href="<?php echo esc_url( self::$theme_fusion_url ) . 'documentation/avada/videos/'; ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Video Tutorials', 'Avada' ); ?></a>
								</li>
								<li>
									<a href="<?php echo esc_url_raw( self::$theme_fusion_url ) . 'support/submit-a-ticket/'; ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Submit A Ticket', 'Avada' ); ?></a>
								</li>
							</ul>
						</nav>

						<?php echo self::get_social_media_links(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</div>

					<div class="avada-db-footer-bottom">
						<div class="avada-db-footer-thanks"><?php esc_html_e( 'Thank you for choosing Avada. We are honored and are fully dedicated to making your experience perfect.', 'Avada' ); ?></div>
						<nav class="avada-db-footer-menu-bottom">
							<a href="<?php echo esc_url_raw( self::$theme_fusion_url ) . 'support-policy/'; ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Support Policy', 'Avada' ); ?></a>
						</nav>
					</div>
				</footer>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Renders the admin screens header for TO page.
	 *
	 * @static
	 * @access public
	 * @since 7.0
	 * @return void
	 */
	public static function get_admin_screen_header_to() {
		self::get_admin_screens_header( 'to' );
		?>
		</div>
		<?php
	}

	/**
	 * Renders the admin screens footer for TO page.
	 *
	 * @static
	 * @access public
	 * @since 7.0
	 * @return void
	 */
	public static function get_admin_screen_footer_to() {
		?>
		<div class="avada-dashboard">
		<?php
		do_action( 'avada_admin_screen_footer_to' );

		self::get_admin_screens_footer();
	}

	/**
	 * Get social media links
	 *
	 * @static
	 * @access public
	 * @since 7.0
	 * @return string The social media link markup
	 */
	public static function get_social_media_links() {
		$social_media_markup = '<div class="avada-db-footer-social-media">
		<a href="https://www.facebook.com/ThemeFusion-101565403356430/" target="_blank" class="avada-db-social-icon dashicons dashicons-facebook-alt"></a>
		<a href="https://twitter.com/theme_fusion" target="_blank" class="avada-db-social-icon dashicons dashicons-twitter"></a>
		<a href="https://www.instagram.com/themefusion/" target="_blank" class="avada-db-social-icon dashicons dashicons-instagram"></a>
		<a href="https://www.youtube.com/channel/UC_C7uAOAH9RMzZs-CKCZ62w" target="_blank" class="avada-db-social-icon fusiona-youtube"></a>
		</div>';

		return apply_filters( 'fusion_admin_social_media_links', $social_media_markup );
	}

	/**
	 * Enqueues scripts.
	 *
	 * @since 5.0.3
	 * @access  public
	 * @return void
	 */
	public function admin_scripts() {
		global $pagenow;
		$version = Avada::get_theme_version();

		wp_enqueue_style( 'avada-wp-admin-css', get_template_directory_uri() . '/assets/admin/css/admin.css', false, $version );
		wp_enqueue_style( 'fusion-font-icomoon', FUSION_LIBRARY_URL . '/assets/fonts/icomoon-admin/icomoon.css', false, $version, 'all' );

		if ( current_user_can( 'switch_themes' ) ) {

			// Add script to check for fusion option slider changes.
			if ( 'post-new.php' === $pagenow || 'edit.php' === $pagenow || 'post.php' === $pagenow ) {
				wp_enqueue_script( 'slider_preview', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/js/fusion-builder-slider-preview.js', [], $version, true );
			}

			if ( 'nav-menus.php' === $pagenow || 'widgets.php' === $pagenow ) {
				wp_enqueue_style(
					'select2-css',
					Avada::$template_dir_url . '/assets/admin/css/select2.css',
					[],
					'4.0.3',
					'all'
				);
				wp_enqueue_script(
					'selectwoo-js',
					Avada::$template_dir_url . '/assets/admin/js/selectWoo.full.min.js',
					[ 'jquery' ],
					'1.0.2',
					false
				);

				// Range field assets.
				wp_enqueue_style(
					'avadaredux-nouislider-css',
					FUSION_LIBRARY_URL . '/inc/redux/framework/FusionReduxCore/inc/fields/slider/vendor/nouislider/fusionredux.jquery.nouislider.css',
					[],
					'5.0.0',
					'all'
				);
				wp_enqueue_script(
					'avadaredux-nouislider-js',
					Avada::$template_dir_url . '/assets/admin/js/jquery.nouislider.min.js',
					[ 'jquery' ],
					'5.0.0',
					true
				);
				wp_enqueue_script(
					'wnumb-js',
					Avada::$template_dir_url . '/assets/admin/js/wNumb.js',
					[ 'jquery' ],
					'1.0.2',
					true
				);
				wp_enqueue_style( 'fusion-font-icomoon', FUSION_LIBRARY_URL . '/assets/fonts/icomoon-admin/icomoon.css', false, $version, 'all' );

				if ( function_exists( 'AWB_Global_Colors' ) ) {
					AWB_Global_Colors()->enqueue();
				}

				wp_enqueue_style( 'fontawesome', Fusion_Font_Awesome::get_backend_css_url(), [], $version );

				if ( '1' === Avada()->settings->get( 'fontawesome_v4_compatibility' ) ) {
					wp_enqueue_script( 'fontawesome-shim-script', FUSION_LIBRARY_URL . '/assets/fonts/fontawesome/js/fa-v4-shims.js', [], $version, false );
					wp_enqueue_style( 'fontawesome-shims', Fusion_Font_Awesome::get_backend_shims_css_url(), [], $version );
				}
				if ( '1' === Avada()->settings->get( 'status_fontawesome_pro' ) ) {
					wp_enqueue_script( 'fontawesome-search-script', FUSION_LIBRARY_URL . '/assets/fonts/fontawesome/js/icons-search-pro.js', [], $version, false );
				} else {
					wp_enqueue_script( 'fontawesome-search-script', FUSION_LIBRARY_URL . '/assets/fonts/fontawesome/js/icons-search-free.js', [], $version, false );
				}
				wp_enqueue_script( 'fuse-script', FUSION_LIBRARY_URL . '/assets/min/js/library/fuse.js', [], $version, false );

				wp_enqueue_script( 'fusion-menu-options', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/js/fusion-menu-options.js', [ 'selectwoo-js' ], $version, true );

				wp_localize_script(
					'fusion-menu-options',
					'fusionMenuConfig',
					[
						'fontawesomeicons'   => fusion_get_icons_array(),
						'fontawesomesubsets' => Avada()->settings->get( 'status_fontawesome' ),
						'customIcons'        => fusion_get_custom_icons_array(),

						/* translators: The iconset name. */
						'no_results_in'      => esc_html__( 'No Results in "%s"', 'fusion-builder' ),
					]
				);
			}

			// @codingStandardsIgnoreLine
			//wp_enqueue_script( 'beta-test', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/js/avada-beta-testing.js', [], $version, true );
		}

		// Color palette should be available to all users.
		if ( in_array( $pagenow, [ 'themes.php', 'nav-menus.php', 'widgets.php', 'post-new.php', 'edit.php', 'post.php', 'edit-tags.php', 'term.php' ], true ) ) {
			wp_localize_script(
				'wp-color-picker',
				'fusionColorPalette',
				[
					'color_palette' => fusion_get_option( 'color_palette' ),
				]
			);
		}
	}

	/**
	 * Enqueues styles.
	 *
	 * @access public
	 * @return void
	 */
	public function styles_general() {
		$ver = Avada::get_theme_version();
		wp_enqueue_style( 'avada_admin_css', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/css/avada-admin.css', [], $ver );
	}

	/**
	 * Enqueues scripts & styles.
	 *
	 * @access public
	 * @return void
	 */
	public function scripts_general() {
		$ver = Avada::get_theme_version();

		wp_enqueue_script( 'avada_zeroclipboard', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/js/zeroclipboard.js', [], $ver, false );
		wp_enqueue_script( 'tiptip_jquery', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/js/tiptip.jquery.min.js', [], $ver, false );
		wp_enqueue_script( 'avada_admin_js', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/js/avada-admin.js', [ 'tiptip_jquery', 'avada_zeroclipboard', 'underscore' ], $ver, true );
		wp_localize_script( 'avada_admin_js', 'avadaAdminL10nStrings', $this->get_admin_script_l10n_strings() );
	}

	/**
	 * Enqueues styles.
	 *
	 * @access  public
	 * @return void
	 */
	public function styles_theme_options() {
		$ver    = Avada::get_theme_version();
		$screen = get_current_screen();

		if ( 'appearance_page_avada_options' === $screen->id ) {
			$this->styles_general();
		}
	}

	/**
	 * Enqueues scripts & styles.
	 *
	 * @access  public
	 * @return void
	 */
	public function scripts_theme_options() {
		$ver    = Avada::get_theme_version();
		$screen = get_current_screen();

		if ( 'appearance_page_avada_options' === $screen->id ) {
			wp_enqueue_script( 'avada_theme_options_menu_mod', trailingslashit( Avada::$template_dir_url ) . 'assets/admin/js/avada-theme-options-menu-mod.js', [ 'jquery' ], $ver, false );
		}
	}

	/**
	 * AJAX callback method. Used to activate plugin.
	 *
	 * @access public
	 * @since 5.2
	 * @return void
	 */
	public function ajax_activate_plugin() {

		if ( current_user_can( 'activate_plugins' ) ) {

			if ( isset( $_GET['avada_activate'] ) && 'activate-plugin' === $_GET['avada_activate'] ) { // phpcs:ignore WordPress.Security

				check_admin_referer( 'avada-activate', 'avada_activate_nonce' );

				$plugins = Avada_TGM_Plugin_Activation::$instance->plugins;

				foreach ( $plugins as $plugin ) {
					if ( isset( $_GET['plugin'] ) && $plugin['slug'] === $_GET['plugin'] ) {
						$result   = activate_plugin( $plugin['file_path'] );
						$response = [];

						$this->clear_plugin_redirection_transients( $_GET['plugin'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

						if ( ! is_wp_error( $result ) ) {
							$response['message'] = 'plugin activated';
							$response['error']   = false;
						} else {
							$response['message'] = $result->get_error_message();
							$response['error']   = true;
						}

						echo wp_json_encode( $response );
						die();
					}
				}
			}
		}
	}

	/**
	 * AJAX callback method.
	 * Used to install and activate plugin.
	 *
	 * @access public
	 * @since 5.0
	 * @return void
	 */
	public function ajax_install_plugin() {

		if ( current_user_can( 'install_plugins' ) ) {

			if ( isset( $_GET['avada_activate'] ) && 'activate-plugin' === $_GET['avada_activate'] ) { // phpcs:ignore WordPress.Security

				check_admin_referer( 'avada-activate', 'avada_activate_nonce' );

				// Unfortunately 'output buffering' doesn't work here as eventually 'wp_ob_end_flush_all' function is called.
				$GLOBALS['avada_tgmpa']->install_plugins_page();

				$this->clear_plugin_redirection_transients( $_GET['plugin'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

				die();
			}
		}

	}

	/**
	 * Clears plugin's redirection transients.
	 *
	 * @access private
	 * @since 7.7
	 * @param string $plugin_slug Plugin slug.
	 * @return void
	 */
	private function clear_plugin_redirection_transients( $plugin_slug ) {

		// Make sure woo setup won't run after this.
		if ( 'woocommerce' === $plugin_slug ) {
			delete_transient( '_wc_activation_redirect' );
		}

		// Make sure bbpress welcome screen won't run after this.
		if ( 'bbpress' === $plugin_slug ) {
			delete_transient( '_bbp_activation_redirect' );
		}

		// Make sure Convert Plus welcome screen won't run after this.
		if ( 'convertplug' === $plugin_slug ) {
			delete_option( 'convert_plug_redirect' );
		}

		// Make sure events calendar welcome screen won't run after this.
		if ( 'the-events-calendar' === $plugin_slug ) {
			delete_transient( '_tribe_events_activation_redirect' );
		}

		// Make sure HubSpot welcome screen won't load.
		if ( 'leadin' === $plugin_slug ) {
			delete_transient( 'leadin_redirect_after_activation' );
		}
	}

	/**
	 * Get the plugin link.
	 *
	 * @access  public
	 * @param array $item The plugin in question.
	 * @return  array
	 */
	public function plugin_link( $item ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once wp_normalize_path( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		$installed_plugins = get_plugins();

		$item['sanitized_plugin'] = $item['name'];

		$actions = [];

		// We have a repo plugin.
		if ( ! $item['version'] ) {
			$item['version'] = Avada_TGM_Plugin_Activation::$instance->does_plugin_have_update( $item['slug'] );
		}

		$disable_class         = '';
		$data_version          = '';
		$fusion_builder_action = '';

		if ( 'fusion-builder' === $item['slug'] && false !== get_option( 'avada_previous_version' ) ) {
			$fusion_core_version = Avada_TGM_Plugin_Activation::$instance->get_installed_version( Avada_TGM_Plugin_Activation::$instance->plugins['fusion-core']['slug'] );

			if ( version_compare( $fusion_core_version, '3.0', '<' ) ) {
				$disable_class         = ' disabled fusion-builder';
				$data_version          = ' data-version="' . $fusion_core_version . '"';
				$fusion_builder_action = [
					'install' => '<div class="fusion-builder-plugin-install-nag">' . esc_html__( 'Please update Avada Core to latest version.', 'Avada' ) . '</div>',
				];
			} elseif ( ! Avada()->registration->should_show( 'plugins' ) ) {
				$disable_class = ' disabled avada-no-token';
			}
		} elseif ( $item['premium'] && ! Avada()->registration->should_show( 'plugins' ) ) {
			$disable_class = ' disabled avada-no-token';
		}

		// We need to display the 'Install' hover link.
		if ( ! isset( $installed_plugins[ $item['file_path'] ] ) ) {
			if ( ! $disable_class ) {
				$url = esc_url(
					wp_nonce_url(
						add_query_arg(
							[
								'page'          => rawurlencode( Avada_TGM_Plugin_Activation::$instance->menu ),
								'plugin'        => rawurlencode( $item['slug'] ),
								'plugin_name'   => rawurlencode( $item['sanitized_plugin'] ),
								'tgmpa-install' => 'install-plugin',
								'return_url'    => 'fusion_plugins',
							],
							Avada_TGM_Plugin_Activation::$instance->get_tgmpa_url()
						),
						'tgmpa-install',
						'tgmpa-nonce'
					)
				);
			} else {
				$url = '#';
			}
			if ( $fusion_builder_action ) {
				$actions = $fusion_builder_action;
			} else {
				$actions = [
					/* translators: Plugin name. */
					'install' => '<a href="' . $url . '" class="button button-primary' . $disable_class . '"' . $data_version . ' title="' . sprintf( esc_attr__( 'Install %s', 'Avada' ), $item['sanitized_plugin'] ) . '">' . esc_attr__( 'Install', 'Avada' ) . '</a>',
				];
			}
		} elseif ( is_plugin_inactive( $item['file_path'] ) ) {
			// We need to display the 'Activate' hover link.
			$url = esc_url(
				add_query_arg(
					[
						'plugin'               => rawurlencode( $item['slug'] ),
						'plugin_name'          => rawurlencode( $item['sanitized_plugin'] ),
						'avada-activate'       => 'activate-plugin',
						'avada-activate-nonce' => wp_create_nonce( 'avada-activate' ),
					],
					admin_url( 'admin.php?page=avada-plugins' )
				)
			);

			$actions = [
				/* translators: Plugin Name. */
				'activate' => '<a href="' . $url . '" class="button button-primary"' . $data_version . ' title="' . sprintf( esc_attr__( 'Activate %s', 'Avada' ), $item['sanitized_plugin'] ) . '">' . esc_attr__( 'Activate', 'Avada' ) . '</a>',
			];
		} elseif ( version_compare( $installed_plugins[ $item['file_path'] ]['Version'], $item['version'], '<' ) ) {

			// We need to display the 'Update' hover link.
			$url = wp_nonce_url(
				add_query_arg(
					[
						'page'         => rawurlencode( Avada_TGM_Plugin_Activation::$instance->menu ),
						'plugin'       => rawurlencode( $item['slug'] ),
						'tgmpa-update' => 'update-plugin',
						'version'      => rawurlencode( $item['version'] ),
						'return_url'   => 'fusion_plugins',
					],
					Avada_TGM_Plugin_Activation::$instance->get_tgmpa_url()
				),
				'tgmpa-update',
				'tgmpa-nonce'
			);

			$actions = [
				/* translators: Plugin Name. */
				'update' => '<a href="' . $url . '" class="button button-primary' . $disable_class . '" title="' . sprintf( esc_attr__( 'Update %s', 'Avada' ), $item['sanitized_plugin'] ) . '">' . esc_attr__( 'Update', 'Avada' ) . '</a>',
			];
		} elseif ( fusion_is_plugin_activated( $item['file_path'] ) ) {
			$url = esc_url(
				add_query_arg(
					[
						'plugin'                 => rawurlencode( $item['slug'] ),
						'plugin_name'            => rawurlencode( $item['sanitized_plugin'] ),
						'avada-deactivate'       => 'deactivate-plugin',
						'avada-deactivate-nonce' => wp_create_nonce( 'avada-deactivate' ),
					],
					admin_url( 'admin.php?page=avada-plugins' )
				)
			);

			$actions = [
				/* translators: Plugin name. */
				'deactivate' => '<a href="' . $url . '" class="button button-primary" title="' . sprintf( esc_attr__( 'Deactivate %s', 'Avada' ), $item['sanitized_plugin'] ) . '">' . esc_attr__( 'Deactivate', 'Avada' ) . '</a>',
			];
		}

		return $actions;
	}

	/**
	 * Needed in order to enable TGMP in AJAX call.
	 *
	 * @access public
	 * @since 5.0
	 * @param bool $load Whether TGMP should be inited or not.
	 * @return bool
	 */
	public function enable_tgmpa( $load ) {
		return true;
	}

	/**
	 * Removes install link for Avada Builder, if Avada Core was not updated to 3.0
	 *
	 * @since 5.0.0
	 * @param array  $action_links The action link(s) for a required plugin.
	 * @param string $item_slug The slug of a required plugin.
	 * @param array  $item Data belonging to a required plugin.
	 * @param string $view_context Specifying the kind of action (install, activate, update).
	 * @return array The action link(s) for a required plugin.
	 */
	public function edit_tgmpa_action_links( $action_links, $item_slug, $item, $view_context ) {
		if ( 'fusion-builder' === $item_slug && 'install' === $view_context ) {
			$fusion_core_version = Avada_TGM_Plugin_Activation::$instance->get_installed_version( Avada_TGM_Plugin_Activation::$instance->plugins['fusion-core']['slug'] );

			if ( version_compare( $fusion_core_version, '3.0', '<' ) ) {
				$action_links['install'] = '<span class="avada-not-installable" style="color:#555555;">' . esc_attr__( 'Avada Builder will be installable, once Avada Core plugin is updated.', 'Avada' ) . '<span class="screen-reader-text">' . esc_attr__( 'Avada Builder', 'Avada' ) . '</span></span>';
			}
		}

		return $action_links;
	}

	/**
	 * Removes install link for Avada Builder, if Avada Core was not updated to 3.0
	 *
	 * @since 5.0.0
	 * @param array $action_links The action link(s) for a required plugin.
	 * @return array The action link(s) for a required plugin.
	 */
	public function edit_tgmpa_notice_action_links( $action_links ) {
		$fusion_core_version = Avada_TGM_Plugin_Activation::$instance->get_installed_version( Avada_TGM_Plugin_Activation::$instance->plugins['fusion-core']['slug'] );
		$current_screen      = get_current_screen();

		if ( 'avada_page_avada-plugins' === $current_screen->id ) {
			$link_template = '<a id="manage-plugins" class="button-primary" style="margin-top:1em;" href="#avada-install-plugins">' . esc_attr__( 'Manage Plugins Below', 'Avada' ) . '</a>';
			$action_links  = [
				'install' => $link_template,
			];
		} elseif ( version_compare( $fusion_core_version, '3.0', '<' ) ) {
			$link_template = '<a id="manage-plugins" class="button-primary" style="margin-top:1em;" href="' . esc_url( self_admin_url( 'admin.php?page=avada-plugins' ) ) . '#avada-install-plugins">' . esc_attr__( 'Go Manage Plugins', 'Avada' ) . '</a>';
			$action_links  = [
				'install' => $link_template,
			];
		}

		return $action_links;
	}

	/**
	 * Initialize the permalink settings.
	 *
	 * @since 3.9.2
	 */
	public function init_permalink_settings() {
		add_settings_field(
			'avada_portfolio_category_slug',                        // ID.
			esc_attr__( 'Avada portfolio category base', 'Avada' ), // Setting title.
			[ $this, 'permalink_slug_input' ],                 // Display callback.
			'permalink',                                            // Settings page.
			'optional',                                             // Settings section.
			[
				'taxonomy' => 'portfolio_category',
			]             // Args.
		);

		add_settings_field(
			'avada_portfolio_skills_slug',
			esc_attr__( 'Avada portfolio skill base', 'Avada' ),
			[ $this, 'permalink_slug_input' ],
			'permalink',
			'optional',
			[
				'taxonomy' => 'portfolio_skills',
			]
		);

		add_settings_field(
			'avada_portfolio_tag_slug',
			esc_attr__( 'Avada portfolio tag base', 'Avada' ),
			[ $this, 'permalink_slug_input' ],
			'permalink',
			'optional',
			[
				'taxonomy' => 'portfolio_tags',
			]
		);
	}

	/**
	 * Show a slug input box.
	 *
	 * @since 3.9.2
	 * @access  public
	 * @param  array $args The argument.
	 */
	public function permalink_slug_input( $args ) {
		$permalinks     = get_option( 'avada_permalinks' );
		$permalink_base = $args['taxonomy'] . '_base';
		$input_name     = 'avada_' . $args['taxonomy'] . '_slug';
		$placeholder    = $args['taxonomy'];
		?>
		<input name="<?php echo esc_attr( $input_name ); ?>" type="text" class="regular-text code" value="<?php echo ( isset( $permalinks[ $permalink_base ] ) ) ? esc_attr( $permalinks[ $permalink_base ] ) : ''; ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>" />
		<?php
	}

	/**
	 * Save the permalink settings.
	 *
	 * @since 3.9.2
	 */
	public function save_permalink_settings() {

		if ( ! is_admin() ) {
			return;
		}

		if ( fusion_doing_ajax() ) {
			return;
		}
		if ( isset( $_POST['permalink_structure'] ) || isset( $_POST['category_base'] ) ) { // phpcs:ignore WordPress.Security
			// Cat and tag bases.
			$portfolio_category_slug = ( isset( $_POST['avada_portfolio_category_slug'] ) ) ? sanitize_text_field( wp_unslash( $_POST['avada_portfolio_category_slug'] ) ) : ''; // phpcs:ignore WordPress.Security
			$portfolio_skills_slug   = ( isset( $_POST['avada_portfolio_skills_slug'] ) ) ? sanitize_text_field( wp_unslash( $_POST['avada_portfolio_skills_slug'] ) ) : ''; // phpcs:ignore WordPress.Security
			$portfolio_tags_slug     = ( isset( $_POST['avada_portfolio_tags_slug'] ) ) ? sanitize_text_field( wp_unslash( $_POST['avada_portfolio_tags_slug'] ) ) : ''; // phpcs:ignore WordPress.Security

			$permalinks = get_option( 'avada_permalinks' );

			if ( ! $permalinks ) {
				$permalinks = [];
			}

			$permalinks['portfolio_category_base'] = untrailingslashit( $portfolio_category_slug );
			$permalinks['portfolio_skills_base']   = untrailingslashit( $portfolio_skills_slug );
			$permalinks['portfolio_tags_base']     = untrailingslashit( $portfolio_tags_slug );

			update_option( 'avada_permalinks', $permalinks );
		}
	}

	/**
	 * Check for Envato hosted and register product.
	 *
	 * @since 5.3
	 *
	 * @access public
	 * @return void
	 */
	public function register_product_envato_hosted() {
		if ( defined( 'ENVATO_HOSTED_SITE' ) && ENVATO_HOSTED_SITE && defined( 'SUBSCRIPTION_CODE' ) && ! Avada()->registration->is_registered() ) {

			$license_status = Avada()->remote_install->validate_envato_hosted_subscription_code();

			$registration_args = Avada()->registration->get_args();
			$product_id        = sanitize_key( $registration_args['name'] );

			$registration_array = [
				$product_id => $license_status,
				'scopes'    => [],
			];
			update_option( 'fusion_registered', $registration_array );

			$registration_array = [
				$product_id => [
					'token' => SUBSCRIPTION_CODE,
				],
			];

			update_option( 'fusion_registration', $registration_array );
		}
	}

	/**
	 * Sets the theme version.
	 *
	 * @since 5.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function set_theme_version() {
		$this->theme_version = Avada()->get_normalized_theme_version();
	}

	/**
	 * Sets the WP_Object for the theme.
	 *
	 * @since 5.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function set_theme_object() {
		$theme_object = wp_get_theme();
		if ( $theme_object->parent_theme ) {
			$template_dir = basename( Avada::$template_dir_path );
			$theme_object = wp_get_theme( $template_dir );
		}

		$this->theme_object = $theme_object;
	}

	/**
	 * Override some LayerSlider data.
	 *
	 * @since 5.0.5
	 * @access public
	 * @return void
	 */
	public function layerslider_overrides() {

		// Disable auto-updates.
		$GLOBALS['lsAutoUpdateBox'] = false;
	}

	/**
	 * Add custom rules to Facebook instant articles plugin.
	 *
	 * @since 5.1
	 * @access public
	 * @param object $transformers The transformers object from the Facebook Instant Articles plugin.
	 * @return object
	 */
	public function add_instant_article_rules( $transformers ) {
		$selectors_pass   = [ 'fusion-fullwidth', 'fusion-builder-row', 'fusion-layout-column', 'fusion-column-wrapper', 'fusion-title', 'fusion-imageframe', 'imageframe-align-center', 'fusion-checklist', 'fusion-li-item', 'fusion-li-item-content' ];
		$selectors_ignore = [ 'fusion-column-inner-bg-image', 'fusion-clearfix', 'title-sep-container', 'fusion-sep-clear', 'fusion-separator' ];

		$avada_rules = '{ "rules" : [';
		foreach ( $selectors_pass as $selector ) {
			$avada_rules .= '{ "class": "PassThroughRule", "selector" : "div.' . $selector . '" },';
		}

		foreach ( $selectors_ignore as $selector ) {
			$avada_rules .= '{ "class": "IgnoreRule", "selector" : "div.' . $selector . '" },';
		}

		$avada_rules = trim( $avada_rules, ',' ) . ']}';

		$transformers->loadRules( $avada_rules );

		return $transformers;
	}

	/**
	 * Returns an array of strings that will be used by avada-admin.js for translations.
	 *
	 * @access private
	 * @since 5.2
	 * @return array
	 */
	private function get_admin_script_l10n_strings() {
		$import_warning_default = __( '<p>Importing prebuilt content will give you layouts, pages, posts, forms, icons, global options, widgets, sidebars, sliders and other settings. This will replicate the live prebuilt site. <strong>Clicking this option will replace your current global options and widgets.</strong> It can also take a minute to complete.</p><h4>REQUIREMENTS</h4><p class="requirement"><i class="req-icon"></i><span class="req-text">Memory Limit of 128 MB and max execution time (php time limit) of 180 seconds.</span></p><p class="requirement"><i class="req-icon"></i><span class="req-text">Avada Core must be activated for Avada Sliders, portfolios and FAQs to import.</span></p><p class="requirement"><i class="req-icon"></i><span class="req-text">Avada Builder must be activated for layouts, icons, forms and page content to display as intended.</span></p><p class="requirement"><i class="req-icon"></i><span class="req-text">Any other plugins that are listed as required need to be active in order to install the corresponding third-party content.</span></p>', 'Avada' );

		return [
			'content'               => esc_attr__( 'Content', 'Avada' ),
			'modify'                => esc_attr__( 'Modify', 'Avada' ),
			'full_import'           => esc_attr__( 'Full Import', 'Avada' ),
			'partial_import'        => esc_attr__( 'Partial Import', 'Avada' ),
			'import'                => esc_attr__( 'Import', 'Avada' ),
			'download'              => esc_attr__( 'Download', 'Avada' ),
			'general_data'          => esc_attr__( 'General Data', 'Avada' ),
			'default'               => $import_warning_default,
			/* translators: The current step label. */
			'currently_processing'  => esc_attr__( 'Currently Processing: %s', 'Avada' ),
			/* translators: The current step label. */
			'currently_removing'    => esc_attr__( 'Currently Removing: %s', 'Avada' ),
			'file_does_not_exist'   => esc_attr__( 'The file does not exist', 'Avada' ),
			/* translators: URL. */
			'error_timeout'         => wp_kses_post( sprintf( __( 'The server couldn\'t be reached. Please check for wp_remote_get on the <a href="%s" target="_blank">Status</a> page.', 'Avada' ), admin_url( 'admin.php?page=avada-status' ) ) ),
			/* translators: URL. */
			'error_php_limits'      => wp_kses_post( sprintf( __( 'Prebuilt website import failed. Please check for PHP limits in red on the <a href="%s" target="_blank">Status</a> page. Change those to the recommended value and try again.', 'Avada' ), admin_url( 'admin.php?page=avada-status' ) ) ),
			'remove_demo'           => esc_attr__( 'Removing prebuilt website content will remove ALL previously imported content from this prebuilt website and restore your site to the state it was in before this prebuilt content was imported.', 'Avada' ),
			'update_fc'             => __( 'Avada Builder Plugin can only be installed and activated if Avada Core plugin is at version 3.0 or higher. Please update Avada Core first.', 'Avada' ),
			/* translators: URL. */
			'register_first'        => sprintf( __( 'This plugin can only be installed or updated, after you have successfully completed the Avada product registration on the <a href="%s" target="_blank">Dashboard Welcome</a> tab.', 'Avada' ), admin_url( 'admin.php?page=avada#avada-db-registration' ) ),
			'plugin_install_failed' => __( 'Plugin install failed. Please try Again.', 'Avada' ),
			'plugin_active'         => __( 'Active', 'Avada' ),
			'please_wait'           => esc_html__( 'Please wait, this may take a minute...', 'Avada' ),
		];
	}

	/**
	 * Add meta boxes to taxonomies
	 *
	 * @access public
	 * @since 3.1.1
	 * @return void
	 */
	public function avada_taxonomy_meta() {
		global $pagenow;

		if ( ! ( 'term.php' === $pagenow || 'edit-tags.php' === $pagenow || ( fusion_doing_ajax() && ! empty( $_REQUEST['action'] ) && 'add-tag' === $_REQUEST['action'] ) ) ) { // phpcs:ignore WordPress.Security
			return;
		}

		// Include Tax meta class.
		include_once Avada::$template_dir_path . '/includes/class-avada-taxonomy-meta.php';

		// Where to add meta fields.
		$args = [
			'screens' => apply_filters( 'fusion_tax_meta_allowed_screens', [ 'category', 'portfolio_category', 'faq_category', 'product_cat', 'tribe_events_cat', 'post_tag', 'portfolio_tags', 'product_tag', 'topic-tag', 'portfolio_skills' ] ),
		];

		// Init taxonomy meta boxes.
		$avada_meta = new Avada_Taxonomy_Meta( $args );

		$options = $avada_meta::avada_taxonomy_map();
		if ( isset( $options['taxonomy_options']['fields'] ) ) {
			foreach ( $options['taxonomy_options']['fields'] as $field ) {
				// Defaults.
				$field['id']          = isset( $field['id'] ) ? $field['id'] : '';
				$field['label']       = isset( $field['label'] ) ? $field['label'] : '';
				$field['choices']     = isset( $field['choices'] ) ? $field['choices'] : [];
				$field['description'] = isset( $field['description'] ) ? $field['description'] : '';
				$field['default']     = isset( $field['default'] ) ? $field['default'] : '';
				$field['dependency']  = isset( $field['dependency'] ) ? $field['dependency'] : [];
				$field['class']       = isset( $field['class'] ) ? $field['class'] : '';

				switch ( $field['type'] ) {
					case 'header':
						$args = [
							'value' => $field['label'],
							'class' => $field['class'],
						];
						$avada_meta->header( $field['id'], $args );
						break;
					case 'select':
						$args = [
							'name'       => $field['label'],
							'default'    => $field['default'],
							'class'      => $field['class'],
							'desc'       => $field['description'],
							'dependency' => $field['dependency'],
						];
						$avada_meta->select( $field['id'], $field['choices'], $args );
						break;
					case 'radio-buttonset':
						$args = [
							'name'       => $field['label'],
							'default'    => $field['default'],
							'class'      => $field['class'],
							'desc'       => $field['description'],
							'dependency' => $field['dependency'],
						];
						$avada_meta->radio_buttonset( $field['id'], $field['choices'], $args );
						break;
					case 'checkbox-buttonset':
						$args = [
							'name'       => $field['label'],
							'default'    => $field['default'],
							'class'      => $field['class'],
							'desc'       => $field['description'],
							'dependency' => $field['dependency'],
						];
						$avada_meta->checkbox_buttonset( $field['id'], $field['value'], $args );
						break;
					case 'text':
						$args = [
							'name'       => $field['label'],
							'class'      => $field['class'],
							'desc'       => $field['description'],
							'dependency' => $field['dependency'],
						];
						$avada_meta->text( $field['id'], $args );
						break;
					case 'dimensions':
						$args = [
							'name'       => $field['label'],
							'class'      => $field['class'],
							'desc'       => $field['description'],
							'dependency' => $field['dependency'],
							'default'    => $field['value'],
						];
						$avada_meta->dimensions( $field['id'], $args );
						break;
					case 'color-alpha':
						$args = [
							'name'       => $field['label'],
							'class'      => $field['class'],
							'desc'       => $field['description'],
							'default'    => $field['default'],
							'dependency' => $field['dependency'],
						];
						$avada_meta->colorpicker( $field['id'], $args );
						break;
					case 'media_url':
					case 'media':
						$args = [
							'name'       => $field['label'],
							'class'      => $field['class'],
							'desc'       => $field['description'],
							'dependency' => $field['dependency'],
						];
						$avada_meta->image( $field['id'], $args );
						break;
				}
			}
		}
	}

	/**
	 * Gets the dashboard-screen video URL.
	 *
	 * @static
	 * @access public
	 * @since 6.2.0
	 * @return string Returns a URL.
	 */
	public static function get_dashboard_screen_video_url() {

		// Fallback values.
		$video_url = 'https://www.youtube.com/watch?v=Y5k5UMgOpXs?rel=0';
		$data      = Avada::get_data();

		if ( isset( $data['video_url'] ) ) {
			$video_url = $data['video_url'];
		}

		if ( false !== strpos( $video_url, 'https://www.youtube.com/watch?v=' ) ) {
			$video_url = str_replace( [ 'https://www.youtube.com/watch?v=', '?rel=0' ], [ 'https://www.youtube.com/embed/', '' ], $video_url ) . '?rel=0';
		}

		return $video_url;
	}

	/**
	 * Get plugin info from plugins with plugin name.
	 *
	 * @since 7.0
	 * @param string $plugin_name Plugin name to search for.
	 * @param array  $plugins     Plugins array containing all plugins data.
	 * @return array
	 */
	public function fusion_get_plugin_info( $plugin_name, $plugins ) {
		$plugin_info_return = null;
		foreach ( $plugins as $plugin_file => $plugin_info ) {
			if ( $plugin_info['Name'] === $plugin_name ) {
				$plugin_info['plugin_file'] = $plugin_file;
				$plugin_info['is_active']   = fusion_is_plugin_activated( $plugin_file );

				$plugin_info_return = $plugin_info;
			}
		}
		return apply_filters( 'fusion_get_plugin_info', $plugin_info_return, $plugin_name, $plugins );
	}

	/**
	 * Deprecated method, exists to prevent error.
	 *
	 * @since 7.3
	 * @param bool $update Deprecated param.
	 * @return void
	 */
	public static function set_dashboard_data( $update = false ) {
	}

	/**
	 * Deprecated method, exists to prevent error.
	 *
	 * @since 7.3
	 * @return array
	 */
	public static function get_dashboard_data() {
		return [];
	}

	/**
	 * Output warning for legacy mode.
	 *
	 * @since 7.3
	 * @return void
	 */
	public function legacy_countdown() {

		$data = Avada::get_data();

		if ( ! Avada()->registration->is_registered() && '' !== Avada()->registration->get_token() && isset( $data['legacy_end'] ) && '' !== $data['legacy_end'] && ! Avada()->registration->is_legacy_update() ) {

			$legacy_active = Avada()->registration->legacy_support();
			$button_url    = isset( $_GET['page'] ) && 'avada' === $_GET['page'] ? '#avada-db-registration' : admin_url( 'admin.php?page=avada#_avada-db-registration' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$message = '<h2>' . esc_html__( 'Avada Product Registration Needs To Be Updated', 'Avada' ) . '</h2>';
			$link    = '<a href="https://theme-fusion.com/documentation/avada/support/avada-registration-q-and-a/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Avada Registration Q and A', 'Avada' ) . '</a>';

			if ( $legacy_active ) {
				$current    = new DateTime( gmdate( 'D, d M Y H:i' ) );
				$legacy_end = new DateTime( gmdate( 'D, d M Y H:i', $data['legacy_end'] ) );
				$diff       = $legacy_end->diff( $current );
				$days       = 1 !== $diff->d ? esc_html__( 'days', 'Avada' ) : esc_html__( 'day', 'Avada' );
				$hours      = 1 !== $diff->h ? esc_html__( 'hours', 'Avada' ) : esc_html__( 'hour', 'Avada' );

				if ( 0 === $diff->d ) {
					$remaining = $diff->format( '%h ' . $hours );
				} else {
					$remaining = $diff->format( '%a ' . $days . ' and %h ' . $hours );
				}
				/* translators: The link. */
				$message .= '<p>' . sprintf( __( 'To receive further product updates, the registration has to be updated using your Avada purchase code. Updates, using your Envato token will continue to work for a limited period of time. The time at which this support will end can be found below. For more information please read the %s.', 'Avada' ), $link ) . '</p>';
				$message .= '<div class="notice-flex-wrapper">';
				/* translators: The remaining  time. */
				$message .= '<p>' . sprintf( __( 'Updates with the token will stop working in <strong>%s</strong>.', 'Avada' ), $remaining ) . '</p>';
			} else {
				/* translators: The link. */
				$message .= '<p>' . sprintf( __( 'To receive further product updates, the registration has to be updated using your Avada purchase code. The grace period for updates using the Envato token has ended. Please complete registration in order to receive automatic updates, plugin installs and prebuilt website imports. For more information please read the %s.', 'Avada' ), $link ) . '</p>';
				$message .= '<div class="notice-flex-wrapper">';
				/* translators: The expiry date. */
				$message .= '<p>' . sprintf( __( 'Token updates expired on <strong>%s</strong>.', 'Avada' ), gmdate( 'D, d M Y H:i', $data['legacy_end'] ) ) . '</p>';
			}
			$message .= '<a class="button" href="' . $button_url . '">' . esc_html__( 'Update Registration', 'Avada' ) . '</a>';
			$message .= '</div>';
			new Fusion_Admin_Notice(
				'fusion-legacy-notice',
				$message,
				is_super_admin(),
				'error',
				true,
				'user_meta',
				'avada-reg',
				[
					'avada_page_avada-prebuilt-websites',
					'avada_page_avada-plugins',
					'avada_page_avada-patcher',
					'toplevel_page_avada',
				]
			);
		}
	}

	/**
	 * Returns the HubSpot affiliate code.
	 *
	 * @since 7.4
	 * @return string
	 */
	public function get_hubspot_affiliate_code() {
		return $this->hubspot_code;
	}
}
/* Omit closing PHP tag to avoid "Headers already sent" issues. */
