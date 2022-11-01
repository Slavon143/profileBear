<?php
/**
 * Handles PWA implementation.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      5.8
 */

/**
 * Handle PWA implementation.
 *
 * @since 5.8
 */
class Avada_PWA {

	/**
	 * The filetypes.
	 *
	 * @access private
	 * @since 5.8
	 * @var array
	 */
	private $filetypes;

	/**
	 * Cache-first items.
	 *
	 * @access private
	 * @since 5.8
	 * @var array
	 */
	private $cache_first = [];

	/**
	 * Network-first items.
	 *
	 * @access private
	 * @since 5.8
	 * @var array
	 */
	private $network_first = [];

	/**
	 * Stale-while-revalidate items.
	 *
	 * @access private
	 * @since 5.8
	 * @var array
	 */
	private $stale_while_revalidate = [];

	/**
	 * The default manifest icon sizes.
	 *
	 * Copied from Jetpack_PWA_Helpers::get_default_manifest_icon_sizes().
	 * Based on a conversation in https://github.com/GoogleChrome/lighthouse/issues/291
	 *
	 * @access private
	 * @since 5.8
	 * @var int[]
	 */
	public $default_manifest_icon_sizes = [ 192, 512 ];

	/**
	 * The class constructor.
	 *
	 * @access public
	 * @since 5.8
	 */
	public function __construct() {

		// Exit early if we've disabled PWA.
		$pwa_enabled = Fusion_Settings::get_instance()->get( 'pwa_enable' );
		if ( true !== $pwa_enabled && '1' !== $pwa_enabled ) {
			return;
		}

		// Only run on the frontend.
		if ( ! is_admin() ) {
			$this->set_policies();
			$this->add_service_workers();

			// Filter the webapp manifest.
			add_filter( 'web_app_manifest', [ $this, 'web_app_manifest' ] );
			add_filter( 'option_site_icon', [ $this, 'site_icon' ] );
		}

		add_filter( 'wp_service_worker_integrations_enabled', '__return_true' );

		// This filter is not necessary in PWA plugin versions greater than 0.3.
		// If site is using an older PWA version add the filter.
		if ( ! defined( 'PWA_VERSION' ) || version_compare( PWA_VERSION, '0.3.0' ) < 0 ) {
			add_filter( 'wp_service_worker_navigation_preload', '__return_false' );
		}

		// Add theme-support for theme-color.
		$this->add_theme_supports();
	}

	/**
	 * Applies tweaks for the web-app manifest.
	 *
	 * @access public
	 * @since 5.8
	 * @param array $manifest The webapp manifest as an array.
	 * @return array
	 */
	public function web_app_manifest( $manifest ) {
		$settings = Fusion_Settings::get_instance();

		// Page background used as background_color.
		$background = $settings->get( 'bg_color' );
		if ( $background ) {
			$manifest['background_color'] = Fusion_Color::new_color( $background )->get_new( 'alpha', 1 )->to_css( 'hex' );
		}

		// Display mode.
		$display = $settings->get( 'pwa_manifest_display' );
		if ( $display ) {
			$manifest['display'] = $display;
		}

		// Icons.
		$manifest_icons = $this->get_icons();
		if ( $manifest_icons ) {
			$manifest['icons'] = $manifest_icons;
		}

		// Fix for start_url.
		if ( get_home_url() === $manifest['start_url'] ) {
			$manifest['start_url'] = trailingslashit( $manifest['start_url'] );
		}

		return $manifest;
	}

	/**
	 * Filter the 'site_icon' option.
	 * The value of site_icon is used by wp-core to generate the 'apple-touch-icon' meta in <head>.
	 *
	 * @access public
	 * @since 5.8.1
	 * @param int $id The attachment ID.
	 * @return int
	 */
	public function site_icon( $id ) {
		if ( ! $id ) {
			return (int) $this->get_site_icon_id();
		}
		return $id;
	}

	/**
	 * Return an array containing file-types that admins can choose.
	 *
	 * @access public
	 * @since 5.8
	 * @return array
	 */
	public function get_filetypes() {
		if ( ! $this->filetypes ) {
			$this->filetypes = apply_filters(
				'avada_pwa_filetypes',
				[
					'images'  => [
						'label' => esc_html__( 'Images', 'Avada' ),
						'rule'  => '^https\:\/\/.*\.(?:png|gif|jpg|jpeg|svg|webp)(\?.*)?$',
						'args'  => [
							'cacheName' => 'fusion_all_images',
							'plugins'   => [
								'expiration' => [
									'maxEntries'        => 60,
									'maxAgeSeconds'     => MONTH_IN_SECONDS,
									'purgeOnQuotaError' => true,
								],
							],
						],
					],
					'fonts'   => [
						'label' => esc_html__( 'Fonts', 'Avada' ),
						'rule'  => '(^https\:\/\/.*(?:googleapis|gstatic)\.com\/.*)|(^https\:\/\/.*\.(?:woff|woff2|ttf|eot)(\?.*)?$)',
						'args'  => [
							'cacheName' => 'fusion_all_fonts',
							'plugins'   => [
								'expiration' => [
									'maxEntries'        => 60,
									'maxAgeSeconds'     => MONTH_IN_SECONDS,
									'purgeOnQuotaError' => true,
								],
							],
						],
					],
					'scripts' => [
						'label' => esc_html__( 'Scripts', 'Avada' ),
						'rule'  => '^https\:\/\/.*\.(?:js)(\?.*)?$',
						'args'  => [
							'cacheName' => 'fusion_all_scripts',
							'plugins'   => [
								'expiration' => [
									'maxEntries'        => 60,
									'maxAgeSeconds'     => MONTH_IN_SECONDS,
									'purgeOnQuotaError' => true,
								],
							],
						],
					],
					'styles'  => [
						'label' => esc_html__( 'Styles', 'Avada' ),
						'rule'  => '^https\:\/\/.*\.(?:css)(\?.*)?$',
						'args'  => [
							'cacheName' => 'fusion_all_styles',
							'plugins'   => [
								'expiration' => [
									'maxEntries'        => 60,
									'maxAgeSeconds'     => MONTH_IN_SECONDS,
									'purgeOnQuotaError' => true,
								],
							],
						],
					],
					/**
					 * Disable caching the content.
					 * This causes the login page to become inoperable
					 * and causes random refreshes.
					 *
					'content' => array(
						'label' => esc_html__( 'Content', 'Avada' ),
						'rule'  => null,
						'args'  => array(
							'cacheName' => 'fusion_all_content',
							'plugins'   => array(
								'expiration' => array(
									'maxEntries'        => 60,
									'maxAgeSeconds'     => DAY_IN_SECONDS,
									'purgeOnQuotaError' => true,
								),
							),
						),
					),
					*/
				]
			);
		}
		return $this->filetypes;
	}

	/**
	 * Set the policy for filetypes.
	 *
	 * @access private
	 * @since 5.8
	 * @return void
	 */
	private function set_policies() {
		$all_filetypes          = $this->get_filetypes();
		$cache_first            = (array) Fusion_Settings::get_instance()->get( 'pwa_filetypes_cache_first' );
		$network_first          = (array) Fusion_Settings::get_instance()->get( 'pwa_filetypes_network_first' );
		$stale_while_revalidate = (array) Fusion_Settings::get_instance()->get( 'pwa_filetypes_stale_while_revalidate' );

		foreach ( $cache_first as $filetype ) {

			// Make sure the filetype is defined before adding it.
			if ( $filetype && isset( $all_filetypes[ $filetype ] ) ) {
				$this->cache_first[] = $filetype;
			}
		}

		foreach ( $stale_while_revalidate as $filetype ) {

			// Make sure the filetype is defined before adding it.
			if ( $filetype && isset( $all_filetypes[ $filetype ] ) ) {
				$this->stale_while_revalidate[] = $filetype;
			}
		}

		foreach ( $network_first as $filetype ) {

			// Make sure the filetype is defined before adding it.
			if ( $filetype && isset( $all_filetypes[ $filetype ] ) ) {
				$this->network_first[] = $filetype;
			}
		}
	}

	/**
	 * Adds the service-workers.
	 *
	 * @access private
	 * @since 5.8
	 * @return void
	 */
	private function add_service_workers() {
		global $wp;

		// Exit early if we don't have what we need.
		if ( ! function_exists( 'wp_register_service_worker_caching_route' ) || ! class_exists( 'WP_Service_Worker_Caching_Routes' ) ) {
			return;
		}

		// Get all filetypes. We'll use this to get the rules and arguments for each item.
		$all_filetypes = $this->get_filetypes();

		foreach ( $all_filetypes as $key => $args ) {

			// Get the strategy we want for this item.
			$strategy = false;
			if ( in_array( $key, $this->cache_first, true ) ) {
				$strategy = WP_Service_Worker_Caching_Routes::STRATEGY_CACHE_FIRST;
			} elseif ( in_array( $key, $this->stale_while_revalidate, true ) ) {
				$strategy = WP_Service_Worker_Caching_Routes::STRATEGY_STALE_WHILE_REVALIDATE;
			} elseif ( in_array( $key, $this->network_first, true ) ) {
				$strategy = WP_Service_Worker_Caching_Routes::STRATEGY_NETWORK_FIRST;
			}

			// If we have no strategy then we don't want to cache this item. Early exit.
			if ( false === $strategy ) {
				continue;
			}

			// If we want to cache the content we don't have any rule, use the current URL.
			if ( 'content' === $key ) {
				$args['rule'] = preg_quote( home_url( $wp->request ), '/' );
			}

			wp_register_service_worker_caching_route(
				$args['rule'],
				array_merge(
					$args['args'],
					[
						'strategy' => $strategy,
					]
				)
			);
		}
	}

	/**
	 * Changes the logo ID retrieved and used by the manifest.
	 *
	 * @access public
	 * @since 5.8
	 * @return int
	 */
	public function get_site_icon_id() {
		$logo = Fusion_Settings::get_instance()->get( 'pwa_manifest_logo' );
		if ( is_array( $logo ) && isset( $logo['id'] ) ) {
			return (int) $logo['id'];
		}
	}

	/**
	 * Gets the manifest icons.
	 *
	 * Mainly copied from Jetpack_PWA_Manifest::build_icon_object() and Jetpack_PWA_Helpers::site_icon_url().
	 *
	 * @access private
	 * @since 5.8
	 * @return array|null $icon_object An array of icons, or null if there's no site icon.
	 */
	private function get_icons() {
		$site_icon_id = $this->get_site_icon_id();
		if ( ! $site_icon_id ) {
			return null;
		}

		$icons     = [];
		$mime_type = get_post_mime_type( $site_icon_id );
		foreach ( $this->default_manifest_icon_sizes as $size ) {
			$size_data = ( $size >= 512 ) ? 'full' : [ $size, $size ];
			$icons[]   = [
				'src'   => wp_get_attachment_image_url( $site_icon_id, $size_data ),
				'sizes' => sprintf( '%1$dx%1$d', $size ),
				'type'  => $mime_type,
			];
		}
		return $icons;
	}

	/**
	 * Adds theme support.
	 *
	 * @access public
	 * @since 5.9.1
	 * @return void
	 */
	public function add_theme_supports() {

		// Add support for service-worker.
		add_theme_support( 'service_worker', true );

		// Add support for theme-color.
		$settings = Fusion_Settings::get_instance();
		$color    = $settings->get( 'pwa_theme_color' );
		if ( $color ) {
			// Make sure it's a HEX color.
			// manifests and theme-color metas don't always work with rgba.
			$color = Fusion_Color::new_color( $color )->get_new( 'alpha', 1 )->to_css( 'hex' );
			add_theme_support( 'theme-color', $color );
		}
	}
}
