<?php
/**
 * The main theme class.
 * We're using this one to instantiate uther classes
 * and access the main theme objects.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * The main theme class.
 */
class Avada {

	/**
	 * The template directory path.
	 *
	 * @static
	 * @access public
	 * @var string
	 */
	public static $template_dir_path = '';

	/**
	 * The template directory URL.
	 *
	 * @static
	 * @access public
	 * @var string
	 */
	public static $template_dir_url = '';

	/**
	 * The stylesheet directory path.
	 *
	 * @static
	 * @access public
	 * @var string
	 */
	public static $stylesheet_dir_path = '';

	/**
	 * The stylesheet directory URL.
	 *
	 * @static
	 * @access public
	 * @var string
	 */
	public static $stylesheet_dir_url = '';

	/**
	 * The one, true instance of the Avada object.
	 *
	 * @static
	 * @access public
	 * @var null|object
	 */
	public static $instance = null;

	/**
	 * The theme version.
	 *
	 * @static
	 * @access public
	 * @var string
	 */
	public static $version = AVADA_VERSION;

	/**
	 * The original option name.
	 * This is the untainted option name, without using any languages.
	 * If you want the property including language, use $option_name instead.
	 *
	 * @static
	 * @access private
	 * @var string
	 */
	private static $original_option_name = 'fusion_options';

	/**
	 * The option name including the language suffix.
	 * If you want the option name without language, use $original_option_name.
	 *
	 * @static
	 * @access private
	 * @var string
	 */
	private static $option_name = '';

	/**
	 * The language we're using.
	 * This is used to modify $option_name.
	 * It is the language code prefixed with a '_'
	 *
	 * @static
	 * @access public
	 * @var string
	 */
	public static $lang = '';

	/**
	 * Determine if the language has been applied to the $option_name.
	 *
	 * @static
	 * @access public
	 * @var bool
	 */
	public static $lang_applied = false;

	/**
	 * Dertermine if the current language is set to "all".
	 *
	 * @static
	 * @access private
	 * @var bool
	 */
	private static $language_is_all = false;

	/**
	 * Determine if we're currently upgrading/migration options.
	 *
	 * @static
	 * @access public
	 * @var bool
	 */
	public static $is_updating = false;

	/**
	 * Avada_Settings.
	 *
	 * @access public
	 * @var object
	 */
	public $settings;

	/**
	 * Avada_Options.
	 *
	 * @static
	 * @access public
	 * @var null|object
	 */
	public static $options = null;

	/**
	 * Bundled Plugins.
	 *
	 * @static
	 * @access public
	 * @var array
	 */
	public static $bundled_plugins = [];

	/**
	 * Fusion.
	 *
	 * @access public
	 * @var object
	 */
	public $fusion_library;

	/**
	 * Avada_Init.
	 *
	 * @access public
	 * @var object
	 */
	public $init;

	/**
	 * Avada_Template.
	 *
	 * @access public
	 * @var object
	 */
	public $template;

	/**
	 * Avada_Blog.
	 *
	 * @access public
	 * @var object
	 */
	public $blog;

	/**
	 * Avada_Images.
	 *
	 * @access public
	 * @var object
	 */
	public $images;

	/**
	 * Avada_Head.
	 *
	 * @access public
	 * @var object
	 */
	public $head;

	/**
	 * Avada_Layout.
	 *
	 * @access public
	 * @var object
	 */
	public $layout;

	/**
	 * Avada_GoogleMap.
	 *
	 * @access public
	 * @var object
	 */
	public $google_map;

	/**
	 * Avada_EventsCalendar.
	 *
	 * @access public
	 * @var object Avada_EventsCalendar.
	 */
	public $events_calendar;

	/**
	 * Avada_Remote_Installer.
	 *
	 * @access public
	 * @var object Avada_Remote_Installer.
	 */
	public $remote_install;

	/**
	 * Avada_Product_registration
	 *
	 * @access public
	 * @var object Avada_Product_registration.
	 */
	public $registration;

	/**
	 * Avada_Slider_Revolution.
	 *
	 * @access public
	 * @since 6.0
	 * @var object Avada_Slider_Revolution
	 */
	public $slider_revolution;

	/**
	 * Avada_Sermon_Manager
	 *
	 * @access public
	 * @var object Avada_Sermon_Manager
	 */
	public $sermon_manager;

	/**
	 * Avada_Privacy_Embeds
	 *
	 * @access public
	 * @var object Avada_Privacy_Embeds
	 */
	public $privacy_embeds;

	/**
	 * Avada_PWA
	 *
	 * @access public
	 * @var object Avada_PWA
	 */
	public $pwa;

	/**
	 * Avada_Block_Editor
	 *
	 * @access public
	 * @var object Avada_Block_Editor
	 */
	public $block_editor;

	/**
	 * Remotely retrieved data.
	 *
	 * @static
	 * @access public
	 * @var mixed
	 */
	public static $data = null;

	/**
	 * Access the single instance of this class.
	 *
	 * @return Avada
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new Avada();
		}
		return self::$instance;
	}

	/**
	 * Shortcut method to get the settings.
	 */
	public static function settings() {
		return self::get_instance()->settings->get_all();
	}

	/**
	 * The class constructor
	 *
	 * @access private
	 */
	private function __construct() {

		// Add a non-persistent cache group.
		wp_cache_add_non_persistent_groups( 'avada' );

		// Set static vars.
		if ( '' === self::$template_dir_path ) {
			self::$template_dir_path = wp_normalize_path( get_template_directory() );
		}
		if ( '' === self::$template_dir_url ) {
			self::$template_dir_url = get_template_directory_uri();
		}
		if ( '' === self::$stylesheet_dir_path ) {
			self::$stylesheet_dir_path = wp_normalize_path( get_stylesheet_directory() );
		}
		if ( '' === self::$stylesheet_dir_url ) {
			self::$stylesheet_dir_url = get_stylesheet_directory_uri();
		}

		$this->set_is_updating();

		// Multilingual handling.
		self::multilingual_options();
		// Make sure that $option_name is set.
		// This is run AFTER the multilingual option as a fallback.
		if ( empty( self::$option_name ) ) {
			self::$option_name = self::get_option_name();
		}

		// Instantiate secondary classes.
		$this->settings     = Avada_Settings::get_instance();
		$this->registration = new Fusion_Product_Registration(
			[
				'type'    => 'theme',
				'name'    => 'Avada',
				'bundled' => [
					'fusion-core'                 => 'Avada Core',
					'fusion-builder'              => 'Avada Builder',
					'fusion-white-label-branding' => 'Avada Custom Branding',
				],
			]
		);

		$this->init              = new Avada_Init();
		$this->template          = new Avada_Template();
		$this->blog              = new Avada_Blog();
		$this->head              = new Avada_Head();
		$this->layout            = new Avada_Layout();
		$this->google_map        = new Avada_GoogleMap();
		$this->remote_install    = new Avada_Remote_Installer();
		$this->fusion_library    = fusion_library();
		$this->images            = new Avada_Images();
		$this->slider_revolution = new Avada_Slider_Revolution();
		$this->sermon_manager    = new Avada_Sermon_Manager();
		$this->privacy_embeds    = new Avada_Privacy_Embeds();
		$this->pwa               = new Avada_PWA();
		$this->block_editor      = new Avada_Block_Editor();

		// Set the Fusion Library Image Class variable to the Avada one, to avoid duplication.
		fusion_library()->images = $this->images;
	}

	/**
	 * Checks if we're in the migration page.
	 * It does that by checking _GET, and then sets the $is_updating property.
	 *
	 * @access public
	 */
	public function set_is_updating() {
		if ( ! self::$is_updating && $_GET && isset( $_GET['avada_update'] ) && '1' == $_GET['avada_update'] ) { // phpcs:ignore WordPress.Security.NonceVerification, WordPress.PHP.StrictComparisons
			self::$is_updating = true;
		}
	}

	/**
	 * Set data from updates server.
	 *
	 * @static
	 * @since 7.0.2
	 * @return mixed
	 */
	public static function get_data() {

		$force_update = isset( $_GET['reset_transient'] ) && '1' === $_GET['reset_transient']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// It has already been fetched, return.
		if ( null !== self::$data && ! $force_update ) {
			return self::$data;
		}

		$transient_name = 'avada_dashboard_data';
		$api_url        = FUSION_UPDATES_URL . '/?action=get-data';

		$cached = $force_update ? false : get_transient( $transient_name );

		if ( false !== $cached ) {
			self::$data = $cached;
			return self::$data;
		}

		$previous_version = get_option( 'avada_previous_version', false );
		if ( Avada()->registration->is_registered() && ! empty( $previous_versions ) ) {
			$api_url .= '&version=' . AVADA_VERSION;
		}

		if ( $force_update ) {
			$api_url .= '&avada-api=1';
		}
		$legacy_mode = ! Avada()->registration->is_registered() && '' !== Avada()->registration->get_token() && ! Avada()->registration->is_legacy_update();

		// Legacy mode, get grace end date.
		if ( $legacy_mode ) {
			$api_url .= '&token=' . Avada()->registration->get_token();
		}

		// Get remote server response.
		$response = wp_remote_get(
			$api_url,
			[
				'user-agent' => 'avada-user-agent',
			]
		);

		// Check for error.
		if ( ! is_wp_error( $response ) ) {
			$response_code = (int) wp_remote_retrieve_response_code( $response );

			// If 403, likely blocked and we don't want continuous loop, so we set transient.
			if ( 403 === $response_code ) {
				set_transient( $transient_name, self::$data, HOUR_IN_SECONDS );
				return self::$data;
			}

			// Not 403 and not 200 likely temporary.  Do not set transient and don't process response.
			if ( 200 !== $response_code ) {
				return self::$data;
			}

			// Parse response.
			$data = wp_remote_retrieve_body( $response );

			// Check for error.
			if ( ! is_wp_error( $data ) ) {
				self::$data = json_decode( $data, true );
			} else {

				// Return on error, to run again as likely temporary.
				return self::$data;
			}

			// If we are legacy mode with no data, it means the token was invalid and we do not want to repeat the check.
			if ( $legacy_mode && ( ! isset( self::$data['legacy_end'] ) || '' === self::$data['legacy_end'] ) ) {
				Avada()->registration->reset_token();
			}
		}

		set_transient( $transient_name, self::$data, HOUR_IN_SECONDS );

		return self::$data;
	}

	/**
	 * Gets the theme version.
	 *
	 * @static
	 * @access public
	 * @since 5.0
	 * @return string
	 */
	public static function get_theme_version() {
		return self::$version;
	}

	/**
	 * Gets the latest theme version.
	 *
	 * @static
	 * @access public
	 * @since 7.3
	 * @return string
	 */
	public static function get_latest_version() {
		$data = self::get_data();
		if ( isset( $data['avada_version'] ) && '' !== $data['avada_version'] ) {
			return $data['avada_version'];
		}

		return self::$version;
	}

	/**
	 * Gets the normalized theme version.
	 *
	 * @static
	 * @access public
	 * @since 5.0
	 * @return string
	 */
	public static function get_normalized_theme_version() {
		$theme_version       = self::$version;
		$theme_version_array = explode( '.', $theme_version );

		if ( isset( $theme_version_array[2] ) && '0' === $theme_version_array[2] ) {
			$theme_version = $theme_version_array[0] . '.' . $theme_version_array[1];
		}

		return $theme_version;
	}

	/**
	 * Gets info for premium (and free) plugins from the ThemeFusion API
	 * in a JSON format and then format them as an array.
	 *
	 * @static
	 * @access public
	 * @since 5.0
	 * @return array Array of bundled plugins.
	 */
	public static function get_bundled_plugins() {
		if ( empty( self::$bundled_plugins ) ) {

			// Check for transient, if none, grab remote HTML file.
			$plugins = get_transient( 'avada_premium_plugins_info' );

			if ( ! $plugins || empty( $plugins ) ) {
				$url = FUSION_UPDATES_URL . '/?avada_action=get_plugins&avada_version=' . self::get_normalized_theme_version();

				// Get remote HTML file.
				$response = wp_remote_get(
					$url,
					[
						'user-agent' => 'avada-user-agent',
					]
				);

				// Check for error.
				if ( is_wp_error( $response ) ) {
					return [];
				}

				// Parse remote HTML file.
				$data = wp_remote_retrieve_body( $response );

				// Check for error.
				if ( is_wp_error( $data ) ) {
					return [];
				}

				$data = json_decode( $data, true );

				if ( ! is_array( $data ) ) {
					return [];
				}

				$plugins = [];
				foreach ( $data as $plugin ) {
					if ( $plugin['premium'] ) {

						// Making sure the correct array index is present, latest_version being returned from server plugin.
						$plugin['version']   = $plugin['latest_version'];
						$plugin['Author']    = $plugin['plugin_author'];
						$plugin['AuthorURI'] = $plugin['plugin_author_url'];
					}

					$plugins[ $plugin['slug'] ] = $plugin;
				}

				// Store remote data file in transient, expire after 24 hours.
				set_transient( 'avada_premium_plugins_info', $plugins, 24 * HOUR_IN_SECONDS );
			}

			self::$bundled_plugins = $plugins;
		}

		return self::$bundled_plugins;
	}

	/**
	 * Sets the $lang property for this object.
	 * Languages are prefixed with a '_'
	 *
	 * If we're not currently performing a migration
	 * it also checks if the options for the current language are set.
	 * If they are not, then we will copy the options from the main language.
	 *
	 * @static
	 * @access public
	 */
	public static function multilingual_options() {
		$multilingual = fusion_library()->multilingual;

		// Set the self::$lang.
		$active_language = $multilingual::get_active_language();
		if ( ! in_array( $active_language, [ '', 'en', 'all' ] ) ) { // phpcs:ignore WordPress.PHP.StrictInArray
			self::$lang = '_' . $active_language;
		}
		// Make sure the options are copied if needed.
		if ( ! in_array( self::$lang, [ '', 'en', 'all' ] ) && ! self::$lang_applied ) { // phpcs:ignore WordPress.PHP.StrictInArray
			// Set the $option_name property.
			self::$option_name = self::get_option_name();
			// Get the options without using a language (defaults).
			$original_options = get_option( self::$original_option_name, [] );
			// Get options with a language.
			$options = get_option( self::$original_option_name . self::$lang, [] );
			// If we're not currently performing a migration and the options are not set
			// then we must copy the default options to the new language.
			if ( ! self::$is_updating && ! empty( $original_options ) && empty( $options ) ) {
				update_option( self::$original_option_name . self::$lang, get_option( self::$original_option_name ) );
			}
			// Modify the option_name to include the language.
			self::$option_name = self::$original_option_name . self::$lang;
			// Set $lang_applied to true. Makes sure we don't do the above more than once.
			self::$lang_applied = true;
		}
	}

	/**
	 * Get the private $option_name.
	 * If empty returns the original_option_name.
	 *
	 * @static
	 * @access public
	 * @return string
	 */
	public static function get_option_name() {
		if ( empty( self::$option_name ) ) {
			return self::$original_option_name;
		}
		return self::$option_name;
	}

	/**
	 * Get the private $original_option_name.
	 *
	 * @static
	 * @access public
	 * @return string
	 */
	public static function get_original_option_name() {
		return self::$original_option_name;
	}

	/**
	 * Change the private $option_name.
	 *
	 * @static
	 * @access public
	 * @param false|string $option_name The option name to use.
	 */
	public static function set_option_name( $option_name = false ) {
		if ( false !== $option_name && ! empty( $option_name ) ) {
			self::$option_name = $option_name;
		}
	}

	/**
	 * Change the private $language_is_all property.
	 *
	 * @static
	 * @access public
	 * @param bool $is_all Whether we're on the "all" language option or not.
	 * @return null|void
	 */
	public static function set_language_is_all( $is_all ) {
		if ( true === $is_all ) {
			self::$language_is_all = true;
			return;
		}
		self::$language_is_all = false;
	}

	/**
	 * Get the private $language_is_all property.
	 *
	 * @static
	 * @access public
	 * @return bool
	 */
	public static function get_language_is_all() {
		return self::$language_is_all;
	}

	/**
	 * Gets the $options.
	 *
	 * @static
	 * @access public
	 * @since 6.0
	 * @return Avada_Options
	 */
	public static function get_options() {
		if ( ! self::$options ) {
			self::$options = Avada_Options::get_instance();
		}
		return self::$options;
	}

	/**
	 * Gets the Avada_Images object.
	 *
	 * NOTE: Do not remove, needed for users updating from 6.1.2.
	 *
	 * @since 2.2.0
	 * @return Avada_Images
	 */
	public function get_images_obj() {
		if ( ! $this->images ) {
			$this->images = new Avada_Images();
		}
		return $this->images;
	}
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
