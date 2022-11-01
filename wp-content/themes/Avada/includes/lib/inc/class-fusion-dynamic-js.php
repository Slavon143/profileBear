<?php
/**
 * Dynamic-JS loader.
 *
 * @package Fusion-Library
 * @since 1.0.0
 */

/**
 * Handles enqueueing files dynamically.
 */
final class Fusion_Dynamic_JS {

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
	 * Array of script to enqueue, in the correct order.
	 *
	 * @static
	 * @access protected
	 * @since 3.2
	 * @var array
	 */
	protected static $ordered_scripts = null;

	/**
	 * An array of our wp_localize_script calls.
	 *
	 * @static
	 * @access protected
	 * @since 1.0.0
	 * @var array
	 */
	protected static $localize_scripts = [];

	/**
	 * An array of external dependencies.
	 *
	 * @static
	 * @access protected
	 * @since 3.2
	 * @var array
	 */
	protected static $external_dependencies = [];

	/**
	 * An instance of the Fusion_Dynamic_JS_File class.
	 * null if the class was not instantiated.
	 *
	 * @access public
	 * @since 1.0.0
	 * @var null|object Fusion_Dynamic_JS_File
	 */
	public $file = null;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 1.0.0
	 */
	public function __construct() {

		add_action( 'wp_footer', [ $this, 'init' ] );
		add_action( 'save_post', [ 'Fusion_Dynamic_JS_File', 'reset_cached_filenames' ] );
		add_action( 'fusionredux/options/fusion_options/saved', [ 'Fusion_Dynamic_JS_File', 'delete_dynamic_js_transient' ] );

	}

	/**
	 * This is fired on 'wp'.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function init() {

		// If JS compiler is disabled, or if WP_SCRIPT_DEBUG is set to true or if on builder frame after change load separate files.
		$option = Fusion_Settings::get_option_name();

		if ( ( defined( 'FUSION_DISABLE_COMPILERS' ) && FUSION_DISABLE_COMPILERS ) ||
			'0' === fusion_library()->get_option( 'js_compiler' ) ||
			( defined( 'WP_SCRIPT_DEBUG' ) && WP_SCRIPT_DEBUG ) ||
			post_password_required() ||
			( isset( $_GET['builder_id'] ) && get_transient( 'fusion_app_emulated-' . sanitize_text_field( wp_unslash( $_GET['builder_id'] ) ) . '-' . $option ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			new Fusion_Dynamic_JS_Separate( $this );
			return;
		}
		$this->file = new Fusion_Dynamic_JS_File( $this );

	}

	/**
	 * Registers a script.
	 *
	 * @static
	 * @access public
	 * @since 1.0.0
	 * @param string      $handle    The script's handle.
	 * @param string      $url       The URL to the script.
	 * @param string      $path      The path to the script.
	 * @param array       $deps      An array of dependencies.
	 * @param bool|string $ver       The script version.
	 * @param bool        $in_footer Whether the script should be in the footer or not.
	 */
	public static function register_script( $handle = '', $url = '', $path = '', $deps = [], $ver = false, $in_footer = false ) {
		self::add_script( 'register', $handle, $url, $path, $deps, $ver, $in_footer );
	}

	/**
	 * Enqueues a script.
	 *
	 * @static
	 * @access public
	 * @since 1.0.0
	 * @param string      $handle    The script's handle.
	 * @param string      $url       The URL to the script.
	 * @param string      $path      The path to the script.
	 * @param array       $deps      An array of dependencies.
	 * @param bool|string $ver       The script version.
	 * @param bool        $in_footer Whether the script should be in the footer or not.
	 */
	public static function enqueue_script( $handle = '', $url = '', $path = '', $deps = [], $ver = false, $in_footer = false ) {
		self::add_script( 'enqueue', $handle, $url, $path, $deps, $ver, $in_footer );
	}

	/**
	 * Deregisters a script.
	 *
	 * @static
	 * @access public
	 * @since 1.0.0
	 * @param string $handle The script's handle.
	 */
	public static function deregister_script( $handle ) {
		foreach ( self::$scripts as $key => $script ) {
			if ( $handle === $script['handle'] ) {
				unset( self::$scripts[ $key ] );
			}
		}
	}

	/**
	 * Dequeues a script.
	 *
	 * @static
	 * @access public
	 * @since 1.0.0
	 * @param string $handle The script's handle.
	 */
	public static function dequeue_script( $handle ) {
		foreach ( self::$scripts as $key => $script ) {
			if ( $handle === $script['handle'] ) {
				self::$scripts[ $key ]['action'] = 'register';
			}
		}
	}

	/**
	 * Add a script to the array.
	 *
	 * @static
	 * @access private
	 * @since 1.0.0
	 * @param string      $action    The action to take. Can be enqueue|register.
	 * @param string      $handle    The script's handle.
	 * @param string      $url       The URL to the script.
	 * @param string      $path      The path to the script.
	 * @param array       $deps      An array of dependencies.
	 * @param bool|string $ver       The script version.
	 * @param bool        $in_footer Whether the script should be in the footer or not.
	 */
	private static function add_script( $action = 'enqueue', $handle = '', $url = '', $path = '', $deps = [], $ver = false, $in_footer = false ) {
		$is_builder = ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() );

		// Early exit if $handle is not defined.
		if ( ! $handle ) {
			return;
		}

		// Check if our script already exists in the array.
		foreach ( self::$scripts as $script ) {
			if ( $handle === $script['handle'] ) {
				if ( 'register' === $script['action'] ) {
					// We're enqueueing the script.
					if ( 'enqueue' === $action ) {
						$url       = ( '' === $url ) ? $script['url'] : $url;
						$path      = ( '' === $path ) ? $script['path'] : $path;
						$deps      = ( empty( $deps ) ) ? $script['deps'] : $deps;
						$ver       = ( false ) ? $script['ver'] : $ver;
						$in_footer = ( false ) ? $script['in_footer'] : $in_footer;
					} elseif ( 'register' === $action ) {
						return;
					}
				} elseif ( 'enqueue' === $script['action'] ) {
					// The script was previously enqueued.
					if ( 'enqueue' === $action ) {
						return;
					} elseif ( 'register' === $action ) {
						$action = 'enqueue';
					}
				}
			}
		}

		// If animations are disabled in TO, we have to delete the dependency from the $deps array.
		if ( 'off' === fusion_library()->get_option( 'status_css_animations' ) && ! $is_builder ) {
			$key = array_search( 'fusion-animations', $deps, true );
			if ( false !== $key ) {
				unset( $deps[ $key ] );
			}
		}

		self::$scripts[] = [
			'action'    => (string) $action,
			'handle'    => (string) $handle,
			'url'       => (string) $url,
			'path'      => (string) $path,
			'deps'      => (array) $deps,
			'ver'       => (string) $ver,
			'in_footer' => true,
		];

	}

	/**
	 * Localize scripts and add variables.
	 *
	 * @static
	 * @access public
	 * @since 1.0.0
	 * @param string $handle The script's handle.
	 * @param string $name   The variable name.
	 * @param array  $data   An array of data.
	 */
	public static function localize_script( $handle = '', $name = '', $data = [] ) {

		// Early exit if $handle or $name are not defined.
		if ( ! $handle || ! $name ) {
			return;
		}

		// Early exit if the script already exists in the array.
		foreach ( self::$localize_scripts as $script ) {
			if ( $handle === $script['handle'] && $name === $script['name'] ) {
				return;
			}
		}

		self::$localize_scripts[] = [
			'handle' => (string) $handle,
			'name'   => (string) $name,
			'data'   => (array) $data,
		];

	}

	/**
	 * Get the scripts.
	 *
	 * @static
	 * @access public
	 * @since 1.0.0
	 * @param bool $reorder Whether we want to reorder the scripts or not.
	 * @return array
	 */
	public function get_scripts( $reorder = true ) {
		return self::$scripts;
	}

	/**
	 * Get the scripts.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return array
	 */
	public function get_localizations() {
		return self::$localize_scripts;
	}

	/**
	 * Check for grandparent dependency and merge.
	 *
	 * @access public
	 * @since 3.2
	 */
	public function merge_dependencies() {
		if ( empty( self::$ordered_scripts ) ) {
			return;
		}

		foreach ( self::$ordered_scripts as $key => $script ) {

			// Script is dependencies.
			if ( isset( $script['deps'] ) && ! empty( $script['deps'] ) ) {
				foreach ( $script['deps'] as $dependency_key => $dependency ) {
					$dependency_slug = $this->get_key_from_handle( $dependency );

					if ( false === $dependency_slug ) {
						continue;
					}

					$parent_script = self::$ordered_scripts[ $dependency_slug ];
					if ( $parent_script && isset( $parent_script['deps'] ) && ! empty( $parent_script['deps'] ) ) {
						self::$ordered_scripts[ $key ]['deps'] = array_merge( self::$ordered_scripts[ $key ]['deps'], $parent_script['deps'] );
					}
				}
			}
		}
	}

	/**
	 * Reorder scripts based on their dependencies.
	 *
	 * @access public
	 * @since 1.0.0
	 */
	public function reorder_scripts() {

		// Build an ordered array of our dependent scripts.
		$dependent_scripts     = [];
		self::$ordered_scripts = self::$scripts;

		// Merge grandparent dependency in, so that full array is present on each script.
		$this->merge_dependencies();

		foreach ( self::$ordered_scripts as $key => $script ) {
			if ( 'enqueue' !== $script['action'] ) {
				continue;
			}

			// Check if the script has dependencies.
			if ( isset( $script['deps'] ) && ! empty( $script['deps'] ) ) {
				foreach ( $script['deps'] as $dependency_key => $dependency ) {

					// Check if our dependencies exist and does not start with fusion.
					// If not, assume they are external dependencies.
					if ( false === $this->get_key_from_handle( $dependency ) && 0 !== strpos( $dependency, 'fusion' ) ) {
						self::$external_dependencies[] = $dependency;
						unset( self::$ordered_scripts['deps'][ $dependency_key ] );
						continue;
					}

					// Make sure dependency is enqueued.
					self::$ordered_scripts[ $this->get_key_from_handle( $dependency ) ]['action'] = 'enqueue';

					// Inject item in array.
					if ( in_array( $dependency, $dependent_scripts, true ) ) {
						$dependent_key     = array_search( $dependency, $dependent_scripts, true );
						$dependent_scripts = $this->add_element( $dependent_scripts, $dependent_key, $dependency );
					}

					// Add the script to the end of the array if it doesn't exist.
					if ( ! in_array( $script['handle'], $dependent_scripts, true ) ) {
						$dependent_scripts[] = $script['handle'];
					}
					$dependent_scripts = array_unique( $dependent_scripts );
				}
			}
		}

		// Go through our dependent scripts and shuffle them in the self::$scripts array
		// so that the final array is ordered for dependencies handling.
		$dependent_scripts = array_reverse( $dependent_scripts );
		foreach ( $dependent_scripts as $dependent ) {
			$key                     = $this->get_key_from_handle( $dependent );
			$script                  = self::$ordered_scripts[ $key ];
			self::$ordered_scripts[] = $script;
			unset( self::$ordered_scripts[ $key ] );
		}

		// Remove scripts that are not to be enqueued.
		foreach ( self::$ordered_scripts as $key => $script ) {
			if ( 'enqueue' !== $script['action'] ) {
				unset( self::$ordered_scripts[ $key ] );
			}
		}
	}

	/**
	 * Get scripts to enqueue in proper order.
	 *
	 * @access public
	 * @since 3.2
	 * @return array
	 */
	public function get_ordered_scripts() {

		// We have already worked it out, just return them.
		if ( null !== self::$ordered_scripts ) {
			return self::$ordered_scripts;
		}

		$this->reorder_scripts();

		return self::$ordered_scripts;
	}

	/**
	 * Find the key of an item in the script array using the script's handle.
	 *
	 * @access private
	 * @since 1.0.0
	 * @param string $handle The script's handle.
	 * @return int           The position of the script in self::$scripts.
	 */
	private function get_key_from_handle( $handle ) {

		foreach ( self::$scripts as $key => $script ) {
			if ( $handle === $script['handle'] ) {
				return $key;
			}
		}
		return false;
	}

	/**
	 * Add element in the middle of an array.
	 *
	 * @access public
	 * @access protected
	 * @since 1.0.0
	 * @param array $array     The array.
	 * @param int   $new_key   The position of the new item in the array.
	 * @param mixed $new_value The value of the item we're adding to the array.
	 * @return array
	 */
	protected function add_element( $array, $new_key, $new_value ) {
		$length    = count( $array );
		$new_array = [];
		// If we're adding as the last element it's easy.
		if ( $new_key >= $length ) {
			$array[] = $new_value;
			return $array;
		}

		// Loop the array and add the item where appropriate.
		foreach ( $array as $key => $value ) {
			if ( $key === $new_key ) {
				$new_array[] = $new_value;
				continue;
			}
			$new_array[] = $value;
		}
		return $new_array;
	}

	/**
	 * Get the array of external dependencies.
	 *
	 * @static
	 * @access public
	 * @since 1.0.0
	 * @return array
	 */
	public function get_external_dependencies() {
		return self::$external_dependencies;
	}

	/**
	 * Determine if the server is HTTP/2 or not.
	 *
	 * @static
	 * @access public
	 * @since 1.0.0
	 * @return bool
	 */
	public static function is_http2() {

		if ( isset( $_SERVER['SERVER_PROTOCOL'] ) ) {
			$ver = 1;
			$ver = ( isset( $_SERVER['SERVER_PROTOCOL'] ) ) ? str_replace( 'HTTP/', '', sanitize_text_field( wp_unslash( $_SERVER['SERVER_PROTOCOL'] ) ) ) : '1';
			if ( 2 <= intval( $ver ) ) {
				return true;
			}
		}
		return false;
	}
}
