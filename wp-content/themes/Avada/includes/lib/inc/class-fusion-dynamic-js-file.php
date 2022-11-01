<?php
/**
 * Dynamic-JS loader - File Method.
 *
 * @package Fusion-Library
 * @since 1.0.0
 */

/**
 * Handles enqueueing files dynamically.
 */
final class Fusion_Dynamic_JS_File {
	/**
	 * The Fusion_Dynamic_JS object.
	 *
	 * @access protected
	 * @since 1.0.0
	 * @var object
	 */
	protected $dynamic_js;

	/**
	 * An array of our scripts.
	 * Each script also lists its dependencies.
	 *
	 * @static
	 * @access protected
	 * @since 1.0.0
	 * @var array
	 */
	protected static $scripts = [];

	/**
	 * The filename.
	 *
	 * @access protected
	 * @since 1.0.0
	 * @var false|string
	 */
	protected $filename;

	/**
	 * The Fusion_Filesystem instance of the $filename.
	 *
	 * @access public
	 * @since 1.0.0
	 * @var null|object
	 */
	public $file = null;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 1.0.0
	 * @param object $dynamic_js An instance of the Fusion_Dynamic_JS object.
	 */
	public function __construct( $dynamic_js ) {

		$this->dynamic_js = $dynamic_js;
		$this->filename   = $this->get_filename();
		$this->file       = new Fusion_Filesystem( $this->filename, 'fusion-scripts' );
		$no_file          = false;

		if ( ! file_exists( $this->file->get_path() ) ) {
			$url = $this->write_file();
			if ( ! $url ) {
				$no_file = true;
			}
		}

		if ( $no_file || ! $this->js_file_is_readable() ) {
			new Fusion_Dynamic_JS_Separate( $dynamic_js );
		} else {
			$this->enqueue_scripts();
		}
	}

	/**
	 * Enqueues the file.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_scripts() {
		global $fusion_library_latest_version;

		// Don't need JS on builder frame.
		if ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() ) {
			return;
		}
		// Get an array of external dependencies.
		$dependencies = array_unique( $this->dynamic_js->get_external_dependencies() );

		// Enqueue the script.
		wp_enqueue_script( 'fusion-scripts', $this->file->get_url(), $dependencies, $fusion_library_latest_version, true );
	}

	/**
	 * Check if file is accessable.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return bool
	 */
	public function js_file_is_readable() {
		// Get the file-path.
		$file_path = $this->file->get_path();
		// Check if the file is readable in the transient and apply the filter.
		$is_readable = apply_filters( 'fusion_compiler_js_file_is_readable', get_transient( 'fusion_dynamic_js_readable' ) );

		// If not readable, we need to do some extra checks.
		if ( ! $is_readable ) {

			// Check if we can access the file via PHP.
			$is_readable = (bool) ( is_readable( $file_path ) );

			// If we could access the file via PHP, check that we can get the URL.
			if ( $is_readable ) {

				// Check for 403 / 500.
				$response = wp_safe_remote_get(
					$this->file->get_url(),
					[
						'timeout' => 5,
					]
				);

				$response_code = wp_remote_retrieve_response_code( $response );

				// Check if the response is ok.
				$is_readable = ( 200 === $response_code );

				// Cache readable only. No need to cache unreadable, it's false anyway.
				if ( $is_readable ) {
					set_transient( 'fusion_dynamic_js_readable', true );
				}
			}
		}
		return apply_filters( 'fusion_compiler_js_file_is_readable', $is_readable );
	}

	/**
	 * Gets the compiled JS.
	 *
	 * @access protected
	 * @since 1.0.0
	 * @return string
	 */
	protected function get_compiled_js() {

		$scripts = $this->dynamic_js->get_ordered_scripts();
		$l10n    = $this->dynamic_js->get_localizations();
		$content = '';

		// Super-dependencies.
		foreach ( $scripts as $key => $script ) {
			if ( 'cssua' === $script['handle'] || 'modernizr' === $script['handle'] ) {
				$path = $script['path'];

				// Skip if the path is empty or file doesn't exist.
				if ( ! $path || ! file_exists( $path ) ) {
					continue;
				}
				// Add the contents of the JS file.
				$file_content = fusion_file_get_contents( $path );
				$file_content = trim( $file_content );
				if ( ! empty( $file_content ) ) {
					// Sometimes minimized scripts omit the closing column at the end.
					// Check and add missing ';' here.
					if ( ';' !== substr( $file_content, -1 ) && '}' !== substr( $file_content, -1 ) && ')' !== substr( $file_content, -1 ) ) {
						$file_content .= ';';
					}
					$content .= $file_content;
					// Add a blank line after each script.
					$content .= PHP_EOL;
				}
				unset( $scripts[ $key ] );
			}
		}

		// Add enqueued scripts.
		foreach ( $scripts as $script ) {

			// Localize scripts.
			foreach ( $l10n as $l10n_script ) {
				if ( $script['handle'] !== $l10n_script['handle'] ) {
					continue;
				}
				foreach ( (array) $l10n_script['data'] as $key => $value ) {
					if ( ! is_scalar( $value ) ) {
						continue;
					}
					$l10n_script['data'][ $key ] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' );
				}
				$value    = wp_json_encode( $l10n_script['data'] );
				$content .= "var {$l10n_script['name']}={$value};";
			}

			$path = $script['path'];
			// Skip if the path is empty or file doesn't exist.
			if ( ! $path || ! file_exists( $path ) ) {
				continue;
			}
			// Add the contents of the JS file.
			$file_content = fusion_file_get_contents( $path );
			$file_content = trim( $file_content );
			if ( ! empty( $file_content ) ) {
				// Sometimes minimized scripts omit the closing column at the end.
				// Check and add missing ';' here.
				if ( ';' !== substr( $file_content, -1 ) && '}' !== substr( $file_content, -1 ) && ')' !== substr( $file_content, -1 ) ) {
					$file_content .= ';';
				}
				$content .= $file_content;
				// Add a blank line after each script.
				$content .= PHP_EOL;
			}
		}
		return apply_filters( 'fusion_dynamic_js_final', $content );
	}

	/**
	 * Writes the styles to a file.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return bool Whether the file-write was successful or not.
	 */
	public function write_file() {

		// Get the compiled JS.
		$content = $this->get_compiled_js();

		// Attempt to write the file.
		return ( $this->file->write_file( $content ) );

	}

	/**
	 * Gets the filename.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return string
	 */
	public function get_filename() {

		$filenames = [];

		// If page has conditions, we skip filename lookup, no need to fetch.
		if ( empty( fusion_library()->conditional_loading ) ) {
			$filenames = get_transient( 'fusion_dynamic_js_filenames' );
		}

		if ( ! is_array( $filenames ) ) {
			$filenames = [];
		}

		$id = (int) fusion_library()->get_page_id();

		// If page has no conditions then check if we already have filename for this.
		if ( empty( fusion_library()->conditional_loading ) && isset( $_SERVER['HTTP_HOST'] ) && isset( $_SERVER['PHP_SELF'] ) && isset( $_SERVER['REQUEST_URI'] ) ) {
			$host = sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) );
			$self = sanitize_text_field( wp_unslash( $_SERVER['PHP_SELF'] ) );
			$uri  = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
			$id  .= md5( $host . $self . $uri );
			if ( isset( $filenames[ $id ] ) ) {
				return $filenames[ $id ] . '.min.js';
			}
		}

		// Do not reorder files here to improve performace.
		$scripts = wp_json_encode( $this->dynamic_js->get_scripts( false ) );
		$l10n    = wp_json_encode( $this->dynamic_js->get_localizations() );
		// Create a filename using md5() and combining the scripts array with localizations.
		$filename = md5( $scripts . $l10n );

		// If page has no conditions, set filename to lookup.
		if ( empty( fusion_library()->conditional_loading ) ) {
			$filenames[ $id ] = $filename;
			set_transient( 'fusion_dynamic_js_filenames', $filenames, HOUR_IN_SECONDS );
		}
		return $filename . '.min.js';

	}

	/**
	 * DEPRECATED. Deletes all compiled JS files.
	 *
	 * @static
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public static function delete_compiled_js() {
		/* Deprecated. Keeping this around in case someone is using it in a custom implementation. It won't do anything, but it won't throw errors or cause planes to crash either. */
	}

	/**
	 * Resets the cached filenames transient.
	 *
	 * @static
	 * @since 1.0.0
	 * @return bool
	 */
	public static function reset_cached_filenames() {

		return delete_transient( 'fusion_dynamic_js_filenames' );

	}

	/**
	 * Resets JS compiler transient.
	 *
	 * @static
	 * @since 1.0.0
	 * @return bool
	 */
	public static function delete_dynamic_js_transient() {

		return delete_transient( 'fusion_dynamic_js_readable' );

	}
}
