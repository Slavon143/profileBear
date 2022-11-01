<?php
/**
 * Custom API implementation to automatically update languages for ThemeFusion products.
 *
 * @since 6.1
 * @package Avada
 */

/**
 * Custom API client.
 *
 * @since 6.1
 * @example new Fusion_Languages_Updater( 'plugin', 'fusion-builder', '2.0.2', 'el' );
 */
class Fusion_Languages_Updater_API {

	/**
	 * Plugin or theme?
	 *
	 * @access private
	 * @since 6.1
	 * @var string
	 */
	private $type;

	/**
	 * The slug.
	 *
	 * @access private
	 * @since 6.1
	 * @var string
	 */
	private $slug;

	/**
	 * The language code.
	 *
	 * @access private
	 * @since 6.1
	 * @var string
	 */
	private $lang;

	/**
	 * The version.
	 *
	 * @access private
	 * @since 6.1
	 * @var string
	 */
	private $ver;

	/**
	 * The API response. Decoded from JSON to an array.
	 *
	 * @access private
	 * @since 6.1
	 * @var array
	 */
	private $vendor_api;

	/**
	 * The API URL.
	 *
	 * @access private
	 * @since 6.1
	 * @var string
	 */
	private $api_url = 'https://raw.githubusercontent.com/Theme-Fusion/Localization-l10n/master';

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 6.1
	 * @param string $type Can be "plugin" or "theme".
	 * @param string $slug The plugin - or theme - slug.
	 * @param string $ver  The version.
	 * @param string $lang The language.
	 */
	public function __construct( $type = 'plugin', $slug = '', $ver = '', $lang = '' ) {

		// Early exit if the user has disabled our API.
		if ( ! fusion_get_option( 'enable_language_updates' ) ) {
			return;
		}

		// Assign object properties.
		$this->type = $type;
		$this->slug = $slug;
		$this->lang = $lang;
		$this->ver  = $ver;

		// If no language was defined, get the current language.
		if ( '' === $this->lang ) {
			$this->lang = get_user_locale();
		}

		// Early exit for en_US.
		if ( 'en_US' === $this->lang ) {
			return;
		}

		// Get the custom API response.
		$this->vendor_api = $this->get_vendor_api();

		// Change the hook for themes/plugins.
		$hook = ( 'theme' === $type ) ? 'site_transient_update_themes' : 'site_transient_update_plugins';

		// Add the filter.
		add_filter( $hook, [ $this, 'modify_api_response' ] );
	}

	/**
	 * Modify the transient based on our API response and the context of this object.
	 *
	 * @access public
	 * @since 6.1
	 * @param object $results The WP API response.
	 * @return object
	 */
	public function modify_api_response( $results ) {

		// Sanity check.
		if ( ! is_object( $results ) || ! $this->should_update() ) {
			return $results;
		}

		// Make sure the translations property is defined in the object.
		if ( ! isset( $results->translations ) || ! is_array( $results->translations ) ) {
			$results->translations = [];
		}

		// Set the translations from our custom API.
		$results->translations[] = [
			'type'       => $this->type,
			'slug'       => $this->slug,
			'language'   => $this->lang,
			'version'    => $this->ver,
			'updated'    => $this->get_updated(),
			'package'    => $this->get_package(),
			'autoupdate' => true,
		];
		return $results;
	}

	/**
	 * Figure out if there's an updated language file or not.
	 *
	 * @access private
	 * @since 6.1
	 * @return bool
	 */
	private function should_update() {

		// Get the translation.
		$translation = $this->get_item_from_api();

		// Only proceed with these checks if translation was found in the API.
		if ( $translation ) {

			// Build the translation path for our checks.
			$translation_path  = WP_LANG_DIR;
			$translation_path .= ( 'theme' === $this->type ) ? '/themes/' : '/plugins/';
			$translation_path .= $this->slug . '-' . $this->lang . '.mo';

			// If translation file does not exist then we should download it.
			if ( ! file_exists( $translation_path ) ) {
				return true;
			}

			// If the existing translation file is older than the one on the API, then we should update it.
			if ( filemtime( $translation_path ) < strtotime( $this->get_updated() ) ) {
				return true;
			}
		}

		// Fallback to false.
		return false;
	}

	/**
	 * Get the vendor API response.
	 *
	 * @access private
	 * @since 6.1
	 * @return array|false Returns the array, or false on failure.
	 */
	private function get_vendor_api() {

		// Try to get the response from cache.
		$transient_name = 'fusion_l10n_api_' . sanitize_key( $this->slug );
		$results        = get_site_transient( $transient_name );

		// If the cache is not populated or expired, get the data.
		if ( ! $results ) {
			$api_url = "{$this->api_url}/api-{$this->slug}.json";

			// Get the server response.
			$response = wp_remote_get( $api_url );

			// Make sure the response was OK and we have a body.
			if ( ! is_wp_error( $response ) && isset( $response['body'] ) ) {

				// JSON-decode the response's body.
				$results = json_decode( $response['body'], true );

				// Set cache for a day.
				set_site_transient( $transient_name, $results, DAY_IN_SECONDS );
			}
		}

		// Sanity check: If results is an array return them.
		if ( is_array( $results ) ) {
			return $results;
		}

		// Fallback to returning false.
		return false;
	}

	/**
	 * Get the item we need from the API.
	 *
	 * @access private
	 * @since 6.1
	 * @return array|false
	 */
	private function get_item_from_api() {
		$result = false;

		// Sanity check.
		if ( is_array( $this->vendor_api ) && isset( $this->vendor_api['translations'] ) && is_array( $this->vendor_api['translations'] ) ) {

			// Loop results.
			foreach ( $this->vendor_api['translations'] as $translation ) {

				// Check if item exists for the language and version specified.
				if ( isset( $translation['language'] ) && $this->lang === $translation['language'] && isset( $translation['version'] ) && $this->ver === $translation['version'] ) {
					$result = $translation;
					break;
				}
			}
		}

		// Return the result (falls-back to false if none was found).
		return $result;
	}

	/**
	 * Get the "updated" argument.
	 *
	 * @access private
	 * @since 6.1
	 * @return string|false Datetime or false on fail.
	 */
	private function get_updated() {
		$translation = $this->get_item_from_api();
		return ( $translation && isset( $translation['updated'] ) ) ? $translation['updated'] : false;
	}

	/**
	 * Get the "package" argument.
	 *
	 * @access private
	 * @since 6.1
	 * @return string URL.
	 */
	private function get_package() {
		$translation = $this->get_item_from_api();
		if ( $translation && isset( $translation['package'] ) ) {
			return $translation['package'];
		}

		// Fallback.
		return "{$this->api_url}/{$this->slug}/{$this->slug}-{$this->lang}.zip";
	}
}
