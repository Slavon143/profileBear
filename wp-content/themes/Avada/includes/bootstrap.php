<?php
/**
 * Extra files & functions are hooked here.
 *
 * Functions moved from functions.php file in v6.0.
 *
 * @package Avada
 * @subpackage Core
 * @since 6.0
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Include Fusion-Library.
 */
require_once wp_normalize_path( get_template_directory() . '/includes/lib/fusion-library.php' );

/**
 * Include Avada app.
 */
require_once wp_normalize_path( get_template_directory() . '/includes/avada-app/fusion-panel.php' );

/**
 * Include the main Avada class.
 */
require_once wp_normalize_path( get_template_directory() . '/includes/class-avada.php' );

/**
 * Define basic properties in the Avada class.
 */
Avada::$template_dir_path   = wp_normalize_path( get_template_directory() );
Avada::$template_dir_url    = get_template_directory_uri();
Avada::$stylesheet_dir_path = wp_normalize_path( get_stylesheet_directory() );
Avada::$stylesheet_dir_url  = get_stylesheet_directory_uri();

/**
 * Include the autoloader.
 */
require_once Avada::$template_dir_path . '/includes/class-avada-autoload.php';

/**
 * Instantiate the autoloader.
 */
new Avada_Autoload();

/**
 * Must-use Plugins.
 */
require_once Avada::$template_dir_path . '/includes/plugins/multiple_sidebars.php';
require_once Avada::$template_dir_path . '/includes/plugins/post-link-plus.php';

// Load dynamic css for plugins.
$avada_glob_filenames = glob( Avada::$template_dir_path . '/includes/typography/*.php', GLOB_NOSORT );
foreach ( $avada_glob_filenames as $filename ) {
	require_once wp_normalize_path( $filename );
}

global $wp_customize;
/**
 * If Avada-Builder is installed, add the options.
 */
if ( ( ( defined( 'FUSION_BUILDER_PLUGIN_DIR' ) && is_admin() ) || ! is_admin() ) && ( ! is_customize_preview() && ! $wp_customize ) ) {
	new Fusion_Builder_Redux_Options();
}

/**
 * Load Fusion functions and make them available for later usage.
 */
require_once Avada::$template_dir_path . '/includes/fusion-functions.php';

/**
 * Load global color class.
 */
require_once Avada::$template_dir_path . '/includes/class-awb-global-colors.php';

/**
 * Load global typography class.
 */
require_once Avada::$template_dir_path . '/includes/class-awb-global-typography.php';

/**
 * Load Adobe typography class.
 */
require_once Avada::$template_dir_path . '/includes/class-awb-adobe-typography.php';

/**
 * Make sure language-all works correctly.
 * Uses Fusion_Multilingual action.
 *
 * @since 5.1
 */
function avada_set_language_is_all() {
	Avada::set_language_is_all( true );
}
add_action( 'fusion_library_set_language_is_all', 'avada_set_language_is_all' );

/**
 * Make sure the Fusion_Multilingual class has been instantiated.
 */
if ( ! property_exists( fusion_library(), 'multilingual' ) || ! fusion_library()->multilingual ) {
	fusion_library()->multilingual = new Fusion_Multilingual();
}

/**
 * Instantiate Avada_Upgrade classes.
 * Don't instantiate the class when DOING_AJAX to avoid issues
 * with the WP HeartBeat API.
 */
if ( ! function_exists( 'fusion_doing_ajax' ) ) {
	/**
	 * Wrapper function for wp_doing_ajax, which was introduced in WP 4.7.
	 *
	 * @since 5.1.5
	 */
	function fusion_doing_ajax() {
		if ( function_exists( 'wp_doing_ajax' ) ) {
			return wp_doing_ajax();
		}

		return defined( 'DOING_AJAX' ) && DOING_AJAX;
	}
}

if ( ! fusion_doing_ajax() ) {
	Avada_Upgrade::get_instance();
}

/**
 * Instantiates the Avada class.
 * Make sure the class is properly set-up.
 * The Avada class is a singleton
 * so we can directly access the one true Avada object using this function.
 *
 * @return object Avada
 */
function Avada() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName
	return Avada::get_instance();
}

Avada();

/**
 * Instantiate the Avada_Multiple_Featured_Images object.
 */
new Avada_Multiple_Featured_Images();

/**
 * Instantiate Avada_Sidebars.
 */
new Avada_Sidebars();

/**
 * Instantiate Avada_Admin_Notices.
 */
new Avada_Admin_Notices();

/**
 * Instantiate Avada_Widget_style.
 */
new Avada_Widget_Style();

/**
 * Instantiate Avada_Page_Options.
 */
new Avada_Page_Options();

/**
 * Instantiate Avada_Portfolio.
 * This is only needed on the frontend, doesn't do anything for the dashboard.
 */
if ( ! is_admin() ) {
	new Avada_Portfolio();
}

/**
 * Instantiate Avada_Social_Icons.
 * This is only needed on the frontend, doesn't do anything for the dashboard.
 */
global $social_icons;
if ( ! is_admin() ) {
	if ( class_exists( 'Fusion_Social_Icons' ) ) {
		$social_icons = new Fusion_Social_Icons();
	} else {
		$social_icons = false;
	}
}

/**
 * Instantiate Avada_fonts.
 * Only do this while in the dashboard, not needed on the frontend.
 */
if ( is_admin() || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() ) ) {
	new Avada_Fonts();
}

/**
 * Instantiate Avada_Scripts.
 */
new Avada_Scripts();

/**
 * Instantiate Avada_Layout_bbPress.
 * We only need to do this for the frontend, when bbPress is installed.
 */
if ( ! is_admin() && class_exists( 'bbPress' ) ) {
	new Avada_Layout_bbPress();
}

/**
 * Instantiate Avada_EventsCalendar
 * We only need to do this on the frontend if Events Calendar is installed or on customizer preview.
 */
global $avada_events_calender;
if ( ( ! is_admin() || is_customize_preview() || fusion_doing_ajax() ) && class_exists( 'Tribe__Events__Main' ) && class_exists( 'Tribe\Events\Views\V2\Template_Bootstrap' ) ) {
	$avada_events_calender = new Avada_EventsCalendar();
}

/**
 * The arguments for the Avada options panel.
 *
 * @since 6.0
 */
global $avada_avadaredux_args;

// Set vars for i18n handling.
$option_name      = Avada::get_option_name();
$is_language_all  = Avada::get_language_is_all();
$default_language = Fusion_Multilingual::get_default_language();

if ( $is_language_all && 'fusion_options' === $option_name && 'en' !== $default_language ) {
	$option_name = Avada::get_option_name() . '_' . $default_language;
}

$avada_avadaredux_args = [
	'is_language_all'      => $is_language_all,
	'default_language'     => $default_language,
	'option_name'          => $option_name,
	'original_option_name' => Avada::get_original_option_name(),
	'version'              => Avada()->get_theme_version(),
	'textdomain'           => 'Avada',
	'disable_dependencies' => (bool) ( '0' === Avada()->settings->get( 'dependencies_status' ) ),
	'display_name'         => 'Avada',
	'menu_title'           => __( 'Global Options', 'Avada' ),
	'page_title'           => __( 'Global Options', 'Avada' ),
	'global_variable'      => 'fusion_fusionredux_options',
	'page_parent'          => 'themes.php',
	'page_slug'            => 'avada_options',
	'menu_type'            => 'submenu',
	'page_permissions'     => 'switch_themes',
];

// Instantiate the Avada_Admin class.
if ( is_admin() ) {
	new Avada_Admin();
} elseif ( ! is_customize_preview() ) {

	// Instantiate the Avada_Admin_Bar class.
	new Avada_Admin_Bar();
}

/**
 * Conditionally Instantiate Avada_AvadaRedux.
 */
$load_avadaredux   = false;
$load_avada_gfonts = true;
if ( is_admin() && isset( $_GET['page'] ) && 'avada_options' === $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification
	$load_avadaredux = true;
}
$http_referer = fusion_get_referer();
if ( $http_referer && false !== strpos( $http_referer, 'avada_options' ) ) {
	$load_avadaredux   = true;
	$load_avada_gfonts = true;
}
$avadaredux_export = ( isset( $_GET['action'] ) && false !== strpos( $_GET['action'], 'fusionredux_link_options-fusion_options' ) && isset( $_GET['secret'] ) && '' !== $_GET['secret'] ) ? true : false; // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
if ( $avadaredux_export ) {
	$load_avadaredux                      = true;
	$load_avada_gfonts                    = false;
	$avada_avadaredux_args['option_name'] = str_replace( 'fusionredux_link_options-', '', $_GET['action'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
}

if ( $load_avadaredux ) {
	$avada_avadaredux = new Avada_AvadaRedux( $avada_avadaredux_args );
}

if ( function_exists( 'Fusion_App' ) && Fusion_App()->get_builder_status() ) {
	$load_avada_gfonts = false;
}
if ( ! is_admin() && $load_avada_gfonts ) {
	Avada_Google_Fonts::get_instance();
}
new Fusion_Dynamic_CSS_From_Options();

/*
 * Include the TGM configuration
 * We only need this while on the dashboard.
 */
if ( is_admin() ) {
	require_once Avada::$template_dir_path . '/includes/class-avada-tgm-plugin-activation.php';
	require_once Avada::$template_dir_path . '/includes/avada-tgm.php';
}

if ( is_admin() && isset( $_GET['page'] ) && ( 'avada-prebuilt-websites' === $_GET['page'] || 'avada-setup' === $_GET['page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
	require_once Avada::$template_dir_path . '/includes/class-awb-prebuilt-websites.php';
}

/*
 * Include deprecated functions
 */
require_once Avada::$template_dir_path . '/includes/deprecated.php';

/**
 * Metaboxes
 */
if ( is_admin() ) {
	require_once Avada::$template_dir_path . '/includes/metaboxes/metaboxes.php';
}

/**
 * Instantiate Avada_System_Status helper class.
 */
if ( is_admin() && ( isset( $_GET['page'] ) && 'avada-status' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) || ( fusion_doing_ajax() && isset( $_GET['action'] ) && ( 'fusion_check_api_status' === $_GET['action'] || 'fusion_create_forms_tables' === $_GET['action'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
	new Avada_System_Status();
}

/**
 * Instantiate the mega menu framework
 */
$mega_menu_framework = new Avada_Megamenu_Framework();

/**
 * Custom Functions
 */
get_template_part( 'includes/custom-functions' );
require_once Avada::$template_dir_path . '/includes/avada-functions.php';

/**
 * WPML Config
 */
if ( defined( 'WPML_PLUGIN_FILE' ) || defined( 'ICL_PLUGIN_FILE' ) ) {
	require_once Avada::$template_dir_path . '/includes/plugins/wpml.php';
}

/**
 * Include the importer
 */
if ( is_admin() ) {
	include Avada::$template_dir_path . '/includes/importer/importer.php';
}

/**
 * Include WP CLI commands.
 */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	include Avada::$template_dir_path . '/includes/cli/shared.php';
	include Avada::$template_dir_path . '/includes/cli/register.php';
	include Avada::$template_dir_path . '/includes/cli/plugins.php';
	include Avada::$template_dir_path . '/includes/cli/import-demo.php';
	include Avada::$template_dir_path . '/includes/cli/apply-patches.php';
}

/**
 * Load Woocommerce Configuraion.
 */
if ( class_exists( 'WooCommerce' ) ) {
	require_once Avada::$template_dir_path . '/includes/wc-functions.php';
	require_once Avada::$template_dir_path . '/includes/class-avada-woocommerce-variations.php';
	global $avada_woocommerce;
	$avada_woocommerce = new Avada_Woocommerce();
}

/**
 * The dynamic CSS.
 */
require_once Avada::$template_dir_path . '/includes/dynamic-css.php';
require_once Avada::$template_dir_path . '/includes/dynamic-css-helpers.php';
global $avada_dynamic_css;
$avada_dynamic_css = new Avada_Dynamic_CSS();

/**
 * Set the $content_width global.
 */
global $content_width;
if ( ! is_admin() && ( ! isset( $content_width ) || empty( $content_width ) ) ) {
	$content_width = (int) Avada()->layout->get_content_width();
}

/**
 * Adds a counter span element to links.
 *
 * @param string $links The links HTML string.
 */
function avada_cat_count_span( $links ) {
	preg_match_all( '#\((.*?)\)#', $links, $matches );
	if ( ! empty( $matches ) ) {
		$i = 0;
		foreach ( $matches[0] as $val ) {
			$links = str_replace( '</a> ' . $val, ' ' . $val . '</a>', $links );
			$links = str_replace( '</a>&nbsp;' . $val, ' ' . $val . '</a>', $links );
			$i++;
		}
	}
	return $links;
}
add_filter( 'get_archives_link', 'avada_cat_count_span' );
add_filter( 'wp_list_categories', 'avada_cat_count_span' );

/**
 * Ignore nag messages.
 */
function avada_nag_ignore() {
	global $current_user;
	$user_id = $current_user->ID;

	// If user clicks to ignore the notice, add that to their user meta.
	if ( isset( $_GET['fusion_richedit_nag_ignore'] ) && '0' === $_GET['fusion_richedit_nag_ignore'] ) { // phpcs:ignore WordPress.Security.NonceVerification
		add_user_meta( $user_id, 'fusion_richedit_nag_ignore', 'true', true );
	}

	// If user clicks to ignore the notice, add that to their user meta.
	if ( isset( $_GET['avada_uber_nag_ignore'] ) && '0' === $_GET['avada_uber_nag_ignore'] ) { // phpcs:ignore WordPress.Security.NonceVerification
		update_option( 'avada_ubermenu_notice', true );
		update_option( 'avada_ubermenu_notice_hidden', true );
		$referer = fusion_get_referer();
		if ( ! $referer ) {
			$referer = '';
		}
		wp_safe_redirect( $referer );
	}
}
add_action( 'admin_init', 'avada_nag_ignore' );

/**
 * Support email login on my account dropdown.
 */
if ( isset( $_POST['fusion_woo_login_box'] ) && 'true' === $_POST['fusion_woo_login_box'] ) { // phpcs:ignore WordPress.Security.NonceVerification
	add_filter( 'authenticate', 'avada_email_login_auth', 10, 3 );
}

/**
 * Allow loging-in via email.
 *
 * @param  object $user     The user.
 * @param  string $username The username.
 * @param  string $password The password.
 */
function avada_email_login_auth( $user, $username, $password ) {
	if ( is_a( $user, 'WP_User' ) ) {
		return $user;
	}

	if ( ! empty( $username ) ) {
		$username = str_replace( '&', '&amp;', stripslashes( $username ) );
		$user     = get_user_by( 'email', $username );
		if ( isset( $user, $user->user_login, $user->user_status ) && 0 === (int) $user->user_status ) {
			$username = $user->user_login;
		}
	}

	return wp_authenticate_username_password( null, $username, $password );
}

/**
 * No redirect on woo my account dropdown login when it fails.
 */
if ( isset( $_POST['fusion_woo_login_box'] ) && 'true' === $_POST['fusion_woo_login_box'] ) { // phpcs:ignore WordPress.Security.NonceVerification
	add_action( 'init', 'avada_load_login_redirect_support' );
}

/**
 * Tweaks the login redirect for WooCommerce.
 */
function avada_load_login_redirect_support() {
	if ( class_exists( 'WooCommerce' ) ) {

		// When on the my account page, do nothing.
		if ( ! empty( $_POST['login'] ) ) {
			if ( isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) ) {
				$nonce = sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
				if ( wp_verify_nonce( $nonce, 'woocommerce-login' ) ) {
					return;
				}
			}
		}

		add_action( 'login_redirect', 'avada_login_fail', 10, 3 );
	}
}

/**
 * Avada Login Fail Test.
 *
 * @param  string $url     The URL.
 * @param  string $raw_url The Raw URL.
 * @param  string $user    User.
 * @return string
 */
function avada_login_fail( $url = '', $raw_url = '', $user = '' ) {
	if ( ! is_account_page() ) {

		if ( isset( $_SERVER ) && isset( $_SERVER['HTTP_REFERER'] ) && esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) ) {
			$referer_array = wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) );
			$parsed_url    = ( isset( $_SERVER['SERVER_PORT'] ) ) ? wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['SERVER_PORT'] ) ) ) : [
				'host' => '80',
			];

			// Make sure it works ok for ports other than 80.
			$port = ( isset( $_SERVER['SERVER_PORT'] ) ) ? ':' . $parsed_url['host'] : ':80';
			$port = ( ':80' === $port ) ? '' : $port;

			// Make sure host doesn't have a trailing slash and append the port.
			$host = untrailingslashit( $referer_array['host'] ) . $port;

			// Make sure path has a slash at the beginning.
			$path = $referer_array['path'];
			if ( 0 !== strpos( $referer_array['path'], '/' ) ) {
				$path = '/' . $referer_array['path'];
			}

			// Combine the above to a $referer.
			if ( false !== strpos( $port, '443' ) ) {
				$referer = 'https://' . $host . $path;
			} else {
				$referer = '//' . $host . $path;
			}

			// If there's a valid referrer, and it's not the default log-in screen.
			if ( ! empty( $referer ) && ! strstr( $referer, 'wp-login' ) && ! strstr( $referer, 'wp-admin' ) ) {
				if ( is_wp_error( $user ) ) {
					// Let's append some information (login=failed) to the URL for the theme to use.
					wp_safe_redirect(
						add_query_arg(
							[
								'login' => 'failed',
							],
							$referer
						)
					);
				} else {
					wp_safe_redirect( $referer );
				}
				exit;
			}
		}
		return $url;
	}
}

/**
 * Show a shop page description on product archives.
 */
function woocommerce_product_archive_description() {
	if ( is_post_type_archive( 'product' ) && in_array( absint( get_query_var( 'paged' ) ), [ 0, 1 ], true ) ) {
		$shop_page = get_post( fusion_wc_get_page_id( 'shop' ) );
		if ( $shop_page ) {
			$description = apply_filters( 'the_content', $shop_page->post_content );
			if ( $description ) {
				echo '<div class="post-content">' . $description . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
			}
		}
	}
}

/**
 * Layerslider API.
 */
function avada_layerslider_ready() {
	if ( class_exists( 'LS_Sources' ) ) {
		LS_Sources::addSkins( Avada::$template_dir_path . '/includes/ls-skins' );
	}
	if ( defined( 'LS_PLUGIN_BASE' ) ) {
		remove_action( 'after_plugin_row_' . LS_PLUGIN_BASE, 'layerslider_plugins_purchase_notice', 10, 3 );
	}
}
add_action( 'layerslider_ready', 'avada_layerslider_ready' );


// Init patcher only in Dashboard.
if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
	/**
	 * Istantiate the auto-patcher tool.
	 */
	global $avada_patcher;
	$avada_patcher_args = [
		'context'     => 'avada',
		'version'     => AVADA_VERSION,
		'name'        => 'Avada',
		'parent_slug' => 'avada',
		'page_title'  => esc_attr__( 'Avada Patcher', 'Avada' ),
		'menu_title'  => esc_attr__( 'Patcher', 'Avada' ),
		'classname'   => 'Avada',
		'bundled'     => [],
	];

	$avada_plugins = [
		'fusion-builder'              => 'fusion-builder/fusion-builder.php',
		'fusion-core'                 => 'fusion-core/fusion-core.php',
		'fusion-white-label-branding' => 'fusion-white-label-branding/fusion-white-label-branding.php',
	];

	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	foreach ( $avada_plugins as $plugin_context => $plugin_path ) {
		if ( is_plugin_active( $plugin_path ) ) {
			$avada_patcher_args['bundled'][] = $plugin_context;
		}
	}

	$avada_patcher = new Fusion_Patcher( $avada_patcher_args );
}

/**
 * During updates sometimes there are changes that will break a site.
 * We're adding a maintenance page to make sure users don't see a broken site.
 * As soon as the update is complete the site automatically returns to normal mode.
 */
$maintenance   = false;
$users_message = esc_html__( 'Our site is currently undergoing scheduled maintenance. Please try again in a moment.', 'Avada' );
// Check if we're currently update Avada.
if ( Avada::$is_updating ) {
	$maintenance   = true;
	$admin_message = esc_html__( 'Currently updating the Avada Theme. Your site will be accessible once the update finishes', 'Avada' );
}

/**
 * Make sure that if the fusion-core plugin is activated,
 * it's at least version 2.0.
 */
if ( class_exists( 'FusionCore_Plugin' ) ) {
	$fc_version = FusionCore_Plugin::VERSION;
	if ( version_compare( $fc_version, '2.0', '<' ) ) {
		$maintenance = true;
		/* translators: The "follow this link" link. */
		$admin_message = sprintf( esc_attr__( 'The Avada-Core plugin needs to be updated before your site can exit maintenance mode. Please %s to update the plugin.', 'Avada' ), '<a href="' . admin_url( 'themes.php?page=install-required-plugins' ) . '" style="color:#0088cc;font-weight:bold;">' . esc_attr__( 'follow this link', 'Avada' ) . '</a>' );
	}
}

/**
 * If we're on maintenance mode, show the screen.
 */
if ( $maintenance ) {
	new Avada_Maintenance( true, $users_message, $admin_message );
}

/**
 * Class for adding Avada specific data to builder.
 * These only affect the dashboard so are not needed when in the front-end.
 */
if ( ( Avada_Helper::is_post_admin_screen() || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() ) ) && defined( 'FUSION_BUILDER_PLUGIN_DIR' ) && ! fusion_doing_ajax() ) {
	Fusion_Builder_Filters::get_instance();
}

/**
 * We will use builder options in Avada, no need for FB to instantiate redux.
 */
add_theme_support( 'fusion-builder-options' );
add_filter( 'fusion_options_label', 'avada_set_options_label' );
add_filter( 'fusion_builder_options_url', 'avada_set_options_url' );


/**
 * Sets options label.
 *
 * @since 5.1
 * @param string $label Label name of options page.
 * @return string
 */
function avada_set_options_label( $label ) {
	return esc_html__( 'Global Options', 'Avada' );
}

/**
 * Set options page URL.
 *
 * @since 5.1
 * @param string $url URL to the options page.
 * @return string
 */
function avada_set_options_url( $url ) {
	return admin_url( 'themes.php?page=avada_options' );
}

if ( Avada()->registration->should_show() && function_exists( 'Fusion_App' ) && Fusion_App()->get_builder_status() ) {
	$fusion_builder_demo_options_importer = new Fusion_Builder_Demos_Theme_Options();
}

/**
 * Filter a sanitized key string.
 *
 * @since 5.0.2
 * @param string $key     Sanitized key.
 * @param string $raw_key The key prior to sanitization.
 * @return string
 */
function avada_auto_update( $key, $raw_key ) {
	return ( 'avada' === $key && 'Avada' === $raw_key ) ? $raw_key : $key;
}

/**
 * Check if doing an ajax theme update,
 * if so make sure Avada theme name is not changed to lowercase.
 */
if ( fusion_doing_ajax() && isset( $_POST['action'] ) && 'update-theme' === $_POST['action'] ) { // phpcs:ignore WordPress.Security.NonceVerification
	add_filter( 'sanitize_key', 'avada_auto_update', 10, 2 );
}

/**
 * Include Avada Builder shared options support.
 */
if ( class_exists( 'FusionBuilder' ) ) {
	require_once Avada::$template_dir_path . '/includes/fusion-shared-options.php';
}

/**
 * Reset all Fusion Caches.
 *
 * @since 5.1
 *
 * @param array $delete_cache An array of caches to delete.
 */
function avada_reset_all_caches( $delete_cache = [] ) {
	// Reset fusion-caches.
	if ( ! class_exists( 'Fusion_Cache' ) ) {
		require_once Avada::$template_dir_path . '/includes/lib/inc/class-fusion-cache.php';
	}

	$fusion_cache = new Fusion_Cache();
	$fusion_cache->reset_all_caches( $delete_cache );

	wp_cache_flush();
}

/**
 * Init the languages updater.
 *
 * @since 6.1
 */
if ( ! class_exists( 'Fusion_Languages_Updater_API' ) ) {
	require_once Avada::$template_dir_path . '/includes/class-fusion-languages-updater-api.php';
}
new Fusion_Languages_Updater_API( 'theme', 'Avada', AVADA_VERSION );

/**
 * Init the Server Rules object, adds necessary filter.
 *
 * @since 7.4
 */
if ( is_admin() && fusion_doing_ajax() && isset( $_POST['action'] ) && ( 'fusion_options_ajax_save' === $_POST['action'] || 'fusion_app_save_post_content' === $_POST['action'] || 'awb_performance_save' === $_POST['action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$avada_server_rules = Avada_Server_Rules::get_instance();
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
