<?php
/**
 * Dynamic-CSS handler.
 *
 * @package Fusion-Library
 * @since 1.0
 */

/**
 * Handle generating the dynamic CSS.
 *
 * @since 1.0
 */
class Fusion_Dynamic_CSS {

	/**
	 * The one, true instance of this object.
	 *
	 * @access protected
	 * @since 1.0
	 * @var null|object
	 */
	protected static $instance = null;

	/**
	 * The mode we'll be using (file/inline).
	 *
	 * @access public
	 * @since 1.0
	 * @var string
	 */
	public $mode;

	/**
	 * An object containing helper methods.
	 *
	 * @access protected
	 * @since 1.0
	 * @var null|object Fusion_Dynamic_CSS_Helpers
	 */
	protected static $helpers = null;

	/**
	 * An instance of the Fusion_Dynamic_CSS_Inline class.
	 * null if we're not using inline mode.
	 *
	 * @access public
	 * @since 1.0
	 * @var null|object Fusion_Dynamic_CSS_Inline
	 */
	public $inline = null;

	/**
	 * An instance of the Fusion_Dynamic_CSS_File class.
	 * null if we're not using file mode.
	 *
	 * @access protected
	 * @since 1.0
	 * @var null|object Fusion_Dynamic_CSS_File
	 */
	protected $file = null;

	/**
	 * Needs update?
	 *
	 * @static
	 * @access public
	 * @since 1.0
	 * @var bool
	 */
	public static $needs_update = false;

	/**
	 * Disable cache?
	 * Used in special cases, for example Avada is active but WPtouch plugin is used.
	 *
	 * @static
	 * @access protected
	 * @since 1.0
	 * @var bool
	 */
	protected static $disable_cache = null;

	/**
	 * An array of extra files that we want to add in our CSS.
	 *
	 * @static
	 * @access private
	 * @since 1.0
	 * @var array
	 */
	private static $extra_files = [];

	/**
	 * An array of CSS variables.
	 *
	 * @static
	 * @access private
	 * @since 1.0
	 * @var array
	 */
	private static $css_vars = [];

	/**
	 * An array of css-variables that should NOT be replaced.
	 *
	 * @access protected
	 * @since 2.2
	 * @var array
	 */
	public static $preserve_vars = [
		'--minFontSize',
		'--minViewportSize',
		'--multiplier',
		'--viewportDiff',
		'--diff',
		'--base-font-size',
		'--typography_sensitivity',
		'--content_break_point',
		'--typography_factor',
		'--grid_main_break_point',
	];

	/**
	 * An array of replace patterns [search=>replace].
	 *
	 * @static
	 * @access private
	 * @since 2.0
	 * @var array
	 */
	private static $replace_patterns = [];

	/**
	 * The final CSS.
	 *
	 * Used here as a static property to avoid multiple constly calls
	 * to the make_css() method.
	 *
	 * @static
	 * @access private
	 * @since 2.0
	 * @var string
	 */
	private static $final_css;

	/**
	 * Constructor.
	 *
	 * @access protected
	 * @since 1.0
	 */
	protected function __construct() {
		self::$helpers = $this->get_helpers();

		add_action( 'wp_enqueue_scripts', [ $this, 'init' ], 110 );

		// When a post is saved, reset its caches to force-regenerate the CSS.
		add_action( 'save_post', [ $this, 'reset_post_transient' ] );
		add_action( 'save_post', [ $this, 'post_update_option' ] );

		add_action( 'customize_save_after', [ $this, 'reset_all_caches' ] );
		add_filter( 'fusion_dynamic_css', [ $this, 'add_extra_files' ] );
		add_filter( 'fusion_dynamic_css', [ $this, 'icomoon_css' ] );
		add_filter( 'fusion_dynamic_css_array', [ $this, 'add_css_vars_to_css' ], PHP_INT_MAX );

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_extra_files' ], 11 );
	}

	/**
	 * Gets the instance of this object.
	 *
	 * @static
	 * @since 1.0
	 * @return object
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Add extra actions.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function init() {

		// If builder frame or AJAX request, no need to run.
		if ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() || fusion_doing_ajax() ) {
			return;
		}

		// Add options.
		$this->add_options();

		// Set the $needs_update property.
		$this->needs_update();

		// Set the $disable_cache property.
		$this->is_cache_disabled();

		// Set mode.
		$this->set_mode();

		if ( 'file' === $this->get_mode() ) {
			$this->file = new Fusion_Dynamic_CSS_File( $this );
			return;
		}
		$this->inline = new Fusion_Dynamic_CSS_Inline( $this );
	}

	/**
	 * Determine if we're using file mode or inline mode.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function set_mode() {

		$this->mode = 'inline';
		$option     = Fusion_Settings::get_option_name();

		// Early exit if on the customizer.
		// This will force-using inline mode.
		global $wp_customize;
		if ( $wp_customize || ( isset( $_GET['builder_id'] ) && get_transient( 'fusion_app_emulated-' . sanitize_text_field( wp_unslash( $_GET['builder_id'] ) . '-' . $option ) ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}

		// Make sure Avada is active if being used.
		if ( $this->is_cache_disabled() ) {
			$this->mode = 'inline';
			return;
		}

		// Check if we're using file mode or inline mode.
		// This simply checks the css_cache_method options.
		if ( 'file' === fusion_library()->get_option( 'css_cache_method' ) ) {
			$this->mode = 'file';
		}
	}

	/**
	 * Gets the mode we're using.
	 *
	 * @access public
	 * @since 1.1.5
	 * @return string
	 */
	public function get_mode() {

		if ( ! $this->mode || null === $this->mode ) {
			$this->needs_update();
			$this->set_mode();
		}
		return ( defined( 'FUSION_DISABLE_COMPILERS' ) && FUSION_DISABLE_COMPILERS ) ? 'inline' : $this->mode;

	}

	/**
	 * Creates the final CSS.
	 *
	 * @access public
	 * @since 2.0
	 * @return string The final CSS.
	 */
	public function generate_final_css() {

		if ( self::$final_css ) {
			return self::$final_css;
		}

		$helpers         = self::$helpers;
		self::$final_css = $helpers->get_dynamic_css();

		self::$final_css = apply_filters( 'fusion_dynamic_css_cached', self::$final_css );

		// Apply replace patterns.
		$replace_patterns = self::get_replacement_patterns();
		foreach ( $replace_patterns as $search => $replace ) {
			self::$final_css = str_replace( $search, $replace, self::$final_css );
		}

		// When using domain-mapping plugins we have to make sure that any references to the original domain
		// are replaced with references to the mapped domain.
		// We're also stripping protocols from these domains so that there are no issues with SSL certificates.
		if ( defined( 'DOMAIN_MAPPING' ) && DOMAIN_MAPPING ) {

			if ( function_exists( 'domain_mapping_siteurl' ) && function_exists( 'get_original_url' ) ) {

				// The mapped domain of the site.
				$mapped_domain = domain_mapping_siteurl( false );
				$mapped_domain = str_replace( [ 'https://', 'http://' ], '//', $mapped_domain );

				// The original domain of the site.
				$original_domain = get_original_url( 'siteurl' );
				$original_domain = str_replace( [ 'https://', 'http://' ], '//', $original_domain );

				// Replace original domain with mapped domain.
				self::$final_css = str_replace( $original_domain, $mapped_domain, self::$final_css );
			}
		}

		// Strip protocols. This helps avoid any issues with https sites.
		self::$final_css = str_replace( [ 'https://', 'http://' ], '//', self::$final_css );

		self::$final_css = apply_filters( 'fusion_dynamic_css_final', self::$final_css );

		// We're adding a warning at the top of the file to prevent users from editing it.
		// The warning is then followed by the actual CSS content.
		self::$final_css = '/********* Compiled CSS - Do not edit *********/ ' . self::$final_css;

		// Security: strips style and script tags.
		self::$final_css = preg_replace( '@<(script|style)[^>]*?>.*?@si', '', self::$final_css );

		return self::$final_css;
	}

	/**
	 * This function takes care of creating the CSS.
	 *
	 * @access public
	 * @since 1.0
	 * @return string The final CSS.
	 */
	public function make_css() {
		if ( ! self::$final_css || empty( self::$final_css ) ) {
			$helpers         = self::$helpers;
			self::$final_css = $helpers->dynamic_css_cached();
		}
		return self::$final_css;
	}

	/**
	 * Reset ALL CSS transient caches.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function reset_all_transients() {
		global $wpdb;
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_fusion_dynamic_css_%'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	/**
	 * Reset the dynamic CSS transient for a post.
	 *
	 * @access public
	 * @since 1.0
	 * @param int $post_id The ID of the post that's being reset.
	 */
	public function reset_post_transient( $post_id ) {
		delete_transient( 'fusion_dynamic_css_' . $post_id );
	}

	/**
	 * Create settings.
	 *
	 * @access private
	 */
	private function add_options() {
		// The 'fusion_dynamic_css_posts' option will hold an array of posts that have had their css generated.
		// We can use that to keep track of which pages need their CSS to be recreated and which don't.
		add_option( 'fusion_dynamic_css_posts', [], '', 'yes' );
	}

	/**
	 * Update the fusion_dynamic_css_posts option when a post is saved.
	 * This adds the current post's ID in the array of IDs that the 'fusion_dynamic_css_posts' option has.
	 *
	 * @access public
	 * @since 1.0
	 * @param int $post_id The post ID.
	 * @return void
	 */
	public function post_update_option( $post_id ) {
		$options = [
			'fusion_dynamic_css_ids',
			'fusion_dynamic_css_posts',
		];
		foreach ( $options as $option_name ) {
			$option             = get_option( $option_name, [] );
			$option[ $post_id ] = false;
			update_option( $option_name, $option );
		}
	}

	/**
	 * Update the fusion_dynamic_css_posts option when the Global Options are saved.
	 * This basically empties the array of page IDs from the 'fusion_dynamic_css_posts' option.
	 *
	 * @access public
	 * @since 1.0
	 */
	public function global_reset_option() {
		update_option( 'fusion_dynamic_css_posts', [] );
		update_option( 'fusion_dynamic_css_ids', [] );
	}

	/**
	 * Do we need to update the CSS file?
	 *
	 * @access public
	 * @since 1.0
	 * @return bool
	 */
	public function needs_update() {

		// Get the 'fusion_dynamic_css_posts' option from the DB.
		$option = get_option( 'fusion_dynamic_css_posts', [] );
		// Get the current page ID.
		$c_page_id = fusion_library()->get_page_id();
		$page_id   = ( $c_page_id ) ? $c_page_id : 'global';

		// If the current page ID exists in the array of pages defined in the 'fusion_dynamic_css_posts' option
		// then the page has already been compiled and we don't need to re-compile it.
		// If it's not in the array then it has not been compiled before so we need to update it.
		if ( ! isset( $option[ $page_id ] ) || ! $option[ $page_id ] ) {
			self::$needs_update = true;
		}

		return self::$needs_update;

	}

	/**
	 * There are special cases when cache should be disabled, for example: Avada is active but WPtouch plugin is used.
	 * In such case all CSS caching should be disabled (and mode set to "inline") in order not to cache CSS which is missing global styles.
	 * Here TextDomain is used, instead of theme Name, so cache is disabled even if a user renames theme.
	 *
	 * @access public
	 * @since 1.1
	 * @return bool
	 */
	public function is_cache_disabled() {

		if ( null === self::$disable_cache ) {
			$theme               = wp_get_theme();
			self::$disable_cache = false;
			if ( 'Avada' === $theme->get( 'TextDomain' ) && ! class_exists( 'Avada' ) || ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) ) {
				self::$disable_cache = true;
			}

			self::$disable_cache = apply_filters( 'fusion_dynamic_cache_disabled', self::$disable_cache );
		}

		return self::$disable_cache;
	}

	/**
	 * This is just a facilitator that will allow us to reset everything.
	 * Its only job is calling the other methods from this class and reset parts of our caches.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function reset_all_caches() {
		$this->reset_all_transients();
		$this->global_reset_option();
	}

	/**
	 * Get an instance of the Fusion_Dynamic_CSS_Helpers object.
	 *
	 * @access public
	 * @since 1.0
	 * @return object Fusion_Dynamic_CSS_Helpers
	 */
	public function get_helpers() {

		// Instantiate the Fusion_Dynamic_CSS_Helpers object.
		if ( null === self::$helpers ) {
			self::$helpers = new Fusion_Dynamic_CSS_Helpers();
		}
		return self::$helpers;
	}

	/**
	 * Makes adding files to the compiled CSS easier.
	 *
	 * @static
	 * @access public
	 * @since 5.1.0
	 * @param string $path The file path.
	 * @param string $url  The file URL.
	 */
	public static function enqueue_style( $path, $url = '' ) {

		if ( '' === $url ) {
			self::$extra_files[] = $path;
			return;
		}
		self::$extra_files[ $url ] = $path;

	}

	/**
	 * Enqueue extra files.
	 * This is used as a falback in case we can't get the contents of the CSS file.
	 *
	 * @access public
	 * @since 1.0.6
	 */
	public function enqueue_extra_files() {

		if ( fusion_should_defer_styles_loading() && doing_action( 'wp_enqueue_scripts' ) ) {
			add_action( 'wp_body_open', [ $this, 'enqueue_extra_files' ], 11 );
			return;
		}

		global $fusion_library_latest_version;

		// Get the extra files we need to enqueue.
		$extra_assets = get_transient( 'fusion_dynamic_css_extra_files_to_enqueue' );
		$extra_assets = ( ! is_array( $extra_assets ) ) ? [] : $extra_assets;

		// No need to proceed if $extra_assets doesn't have anything.
		if ( empty( $extra_assets ) ) {
			return;
		}

		// If we got this far there are scripts to enqueue.
		foreach ( $extra_assets as $url ) {
			// Early exit if not a string.
			if ( ! is_string( $url ) || is_numeric( $url ) ) {
				continue;
			}
			// Make sure the URL is properly escaped.
			$url = esc_url_raw( $url );

			// The only thing we have available is the url,
			// so we'll simply use md5() to create a unique handle for the script.
			$handle = md5( $url );

			// Enqueue the style.
			wp_enqueue_style( $handle, $url, [], $fusion_library_latest_version );

		}
	}

	/**
	 * Adds our extra files to the final CSS.
	 *
	 * @access public
	 * @since 1.0
	 * @param string $css The final CSS.
	 * @return string     The final CSS after our extra files have been added.
	 */
	public function add_extra_files( $css ) {
		$extra_files  = array_unique( self::$extra_files );
		$extra_assets = [];
		$files_css    = '';

		foreach ( $extra_files as $url => $path ) {
			// Get the file contents.
			$file_contents = fusion_file_get_contents( $path );

			if ( $file_contents ) {
				$files_css .= $file_contents;
			} else {
				$extra_assets[] = $url;
			}
		}
		if ( ! empty( $extra_assets ) ) {
			set_transient( 'fusion_dynamic_css_extra_files_to_enqueue', $extra_assets );
		}
		return $files_css . $css;

	}

	/**
	 * Adds icomoon CSS.
	 *
	 * @access public
	 * @since 1.0.2
	 * @param string $css The original CSS.
	 * @return string The original CSS with the webfont @font-face declaration appended.
	 */
	public function icomoon_css( $css ) {

		$font_url = FUSION_LIBRARY_URL . '/assets/fonts/icomoon';
		$font_url = set_url_scheme( $font_url );

		$font_face_display = fusion_library()->get_option( 'font_face_display' );
		$font_face_display = ( 'swap-all' === $font_face_display ) ? 'swap' : 'block';

		$css .= '@font-face {';
		$css .= 'font-family: "awb-icons";';
		$css .= 'src:';
		$css .= "url('{$font_url}/awb-icons.woff') format('woff'),";
		$css .= "url('{$font_url}/awb-icons.ttf') format('truetype'),";
		$css .= "url('{$font_url}/awb-icons.svg#awb-icons') format('svg');";
		$css .= 'font-weight: normal;';
		$css .= 'font-style: normal;';
		$css .= 'font-display: ' . $font_face_display . ';';
		$css .= '}';

		return $css;

	}

	/**
	 * Add a CSS Variable.
	 *
	 * @static
	 * @access public
	 * @since 2.0
	 * @param array $args The arguments ['name'=>'','value'=>'','element'=>''].
	 * @return void
	 */
	public static function add_css_var( $args ) {

		// Don't add var if it's value is empty.
		if ( isset( $args['value'] ) && '' === $args['value'] ) {
			return;
		}

		if ( isset( $args['preserve'] ) && $args['preserve'] && isset( $args['name'] ) ) {
			self::$preserve_vars[] = $args['name'];
		}
		self::$css_vars[ $args['name'] ] = $args;
	}

	/**
	 * Adds a replace pattern.
	 *
	 * @static
	 * @access public
	 * @since 2.0
	 * @param string $search  What to search for.
	 * @param string $replace What to replace the search string with.
	 * @return void
	 */
	public static function add_replace_pattern( $search, $replace ) {
		self::$replace_patterns[ $search ] = $replace;
	}

	/**
	 * Get an array of replace patterns.
	 *
	 * @static
	 * @access public
	 * @since 2.0
	 * @return array
	 */
	public static function get_replacement_patterns() {
		uksort(
			self::$replace_patterns,
			function( $a, $b ) {
				return strlen( $b ) - strlen( $a );
			}
		);
		return self::$replace_patterns;
	}

	/**
	 * Add css-vars to the final CSS.
	 *
	 * @access public
	 * @since 2.0
	 * @param string $css The CSS.
	 * @return string
	 */
	public function add_css_vars_to_css( $css ) {
		$vars_styles = '';

		foreach ( self::$css_vars as $key => $args ) {
			if ( is_string( $key ) && ! is_array( $args['value'] ) ) {
				$css['global'][ $args['element'] ][ $key ] = $args['value'];
			}
		}
		return $css;
	}
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
