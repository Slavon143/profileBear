<?php
/**
 * Handles Prebuilt Websites.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      7.7
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Handles layouts.
 */
class AWB_Prebuilt_Websites {

	/**
	 * The one, true instance of this object.
	 *
	 * @static
	 * @access private
	 * @since 7.7
	 * @var object
	 */
	private static $instance;

	/**
	 * Available websites for current Avada version.
	 *
	 * @access protected
	 * @var array
	 */
	protected $available_websites = [];

	/**
	 * Imported websites, even partially.
	 *
	 * @access protected
	 * @var array
	 */
	protected $imported_websites = [];

	/**
	 * All website tags, used for filtering.
	 *
	 * @access protected
	 * @var array
	 */
	protected $all_tags = [];

	/**
	 * Array of all possible plugin dependencies and their status (installed, activated).
	 *
	 * @access protected
	 * @var null|array
	 */
	protected $plugin_dependencies = null;

	/**
	 * The constructor.
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @static
	 * @access public
	 * @since 7.7
	 */
	public static function get_instance() {

		// If an instance hasn't been created and set to $instance create an instance and set it to $instance.
		if ( null === self::$instance ) {
			self::$instance = new AWB_Prebuilt_Websites();
		}
		return self::$instance;
	}

	/**
	 * Init stuff.
	 *
	 * @since 7.7
	 * @access public
	 */
	public function init() {
		$this->process_available_websites();
		$this->set_imported_websites();

		if ( ! fusion_doing_ajax() ) {
			add_action( 'admin_enqueue_scripts', [ $this, 'add_scripts' ] );
		}
	}

	/**
	 * Goes through all available websites, filters out those which dont have high enough version.
	 * While doing that it will also get all website tags and sort them by count (descending).
	 */
	protected function process_available_websites() {
		// Include the Avada_Importer_Data class if it doesn't exist.
		if ( ! class_exists( 'Avada_Importer_Data' ) ) {
			include_once Avada::$template_dir_path . '/includes/importer/class-avada-importer-data.php';
		}

		$demos         = Avada_Importer_Data::get_data();
		$theme_version = Avada()->get_normalized_theme_version();

		foreach ( $demos as $demo => $demo_details ) {

			// Make sure we don't show demos that can't be applied to this version.
			if ( isset( $demo_details['minVersion'] ) ) {

				// Skip websites which require higher theme version.
				$min_version = Avada_Helper::normalize_version( $demo_details['minVersion'] );
				if ( version_compare( $theme_version, $min_version ) < 0 ) {
					continue;
				}

				$this->available_websites[ $demo ] = $demo_details;

				// Tag stuff.
				if ( ! isset( $demo_details['tags'] ) ) {
					$demo_details['tags'] = [];
				}

				foreach ( $demo_details['tags'] as $key => $name ) {
					if ( ! isset( $this->all_tags[ $key ] ) ) {
						$this->all_tags[ $key ] = [
							'name'  => $name,
							'count' => 0,
						];
					}

					$this->all_tags[ $key ]['count']++;
				}
			}
		}

		// Sort websites by tag count, descending. uasort because we need to preserve keys.
		uasort(
			$this->all_tags,
			function ( $a, $b ) {
				return $b['count'] - $a['count'];
			}
		);

	}

	/**
	 * Set imported websites.
	 */
	public function set_imported_websites() {

		$imported_data = get_option( 'fusion_import_data', [] );

		if ( ! function_exists( 'avada_get_demo_import_stages' ) ) {
			include_once Avada::$template_dir_path . '/includes/avada-functions.php';
		}
		$import_stages = avada_get_demo_import_stages();

		foreach ( $imported_data as $stage => $imported_demos ) {
			foreach ( $imported_demos as $imported_demo ) {
				if ( ! in_array( $imported_demo, $this->imported_websites, true ) ) {
					$this->imported_websites[] = $imported_demo;
				}
			}
		}

	}

	/**
	 * Gets required plugins for specific website.
	 *
	 * @param string $website_key Website key.
	 * @return array
	 */
	public function get_required_plugins( $website_key ) {

		$required_plugins = [];

		if ( isset( $this->available_websites[ $website_key ] ) && isset( $this->available_websites[ $website_key ]['plugin_dependencies'] ) ) {
			foreach ( $this->available_websites[ $website_key ]['plugin_dependencies'] as $plugin_key => $is_used ) {
				if ( $is_used ) {
					$required_plugins[] = $plugin_key;
				}
			}
		}

		return $required_plugins;
	}

	/**
	 * Gets imported websites.
	 *
	 * @return array
	 */
	public function get_websites() {
		return $this->available_websites;
	}

	/**
	 * Gets website tags.
	 *
	 * @return array
	 */
	public function get_tags() {
		return $this->all_tags;
	}

	/**
	 * Gets imported websites.
	 *
	 * @return array
	 */
	public function get_imported_websites() {
		return $this->imported_websites;
	}

	/**
	 * Gets plugins info.
	 *
	 * @return array
	 */
	public function get_plugins_info() {

		if ( null === $this->plugin_dependencies ) {
			$this->plugin_dependencies = [];

			// Check which recommended plugins are installed and activated.
			if ( class_exists( 'Avada_TGM_Plugin_Activation' ) ) {
				$this->plugin_dependencies = Avada_TGM_Plugin_Activation::$instance->plugins;
			}

			foreach ( $this->plugin_dependencies as $key => $plugin_args ) {
				$this->plugin_dependencies[ $key ]['active']    = fusion_is_plugin_activated( $plugin_args['file_path'] );
				$this->plugin_dependencies[ $key ]['installed'] = file_exists( WP_PLUGIN_DIR . '/' . $plugin_args['file_path'] );
			}
		}

		return $this->plugin_dependencies;
	}

	/**
	 * Checks if Avada Core & Avada Builder are installed and active.
	 *
	 * @return bool
	 */
	public function are_avada_plugins_active() {
		$plugins = $this->get_plugins_info();

		return $plugins['fusion-core']['active'] && $plugins['fusion-builder']['active'];
	}

	/**
	 * Add scripts.
	 */
	public function add_scripts() {

		wp_localize_script(
			'jquery',
			'awbPrebuilts',
			[
				'websites'              => $this->get_websites(),
				'plugins'               => $this->get_plugins_info(),
				'import_stages'         => avada_get_demo_import_stages(),
				'nonce_install_plugin'  => wp_create_nonce( 'tgmpa-install' ),
				'nonce_activate_plugin' => wp_create_nonce( 'avada-activate' ),
				'nonce_import_prebuilt' => wp_create_nonce( 'avada_demo_ajax' ),
			]
		);
	}

}

/**
 * Instantiates the AWB_Prebuilt_Websites class.
 * Make sure the class is properly set-up.
 *
 * @since object 7.7
 * @return object AWB_Prebuilt_Websites
 */
function AWB_Prebuilt_Websites() { // phpcs:ignore WordPress.NamingConventions
	return AWB_Prebuilt_Websites::get_instance();
}
AWB_Prebuilt_Websites();
