<?php
/**
 * Handles Google fonts in Avada.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      3.8.5
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Manages the way Google Fonts are enqueued.
 */
final class Avada_Google_Fonts {

	/**
	 * The class instance.
	 *
	 * @since 7.4
	 * @static
	 * @access private
	 * @var null|object
	 */
	private static $instance = null;

	/**
	 * An array of all google fonts.
	 *
	 * @static
	 * @access private
	 * @var array
	 */
	private $google_fonts = [];

	/**
	 * The array of fonts
	 *
	 * @access private
	 * @var array
	 */
	private $fonts = [];

	/**
	 * The google link
	 *
	 * @access private
	 * @var string
	 */
	private $remote_link = '';

	/**
	 * The class constructor.
	 *
	 * @access public
	 */
	public function __construct() {

		// Populate the array of google fonts.
		$this->google_fonts = $this->get_google_fonts();

		// Needed, so that fusion_google_fonts filter can be applied for inline fonts.
		add_action( 'wp', [ $this, 'init' ], 10 );

		// Enqueue link.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue' ], 105 );

		add_filter( 'fusion_dynamic_css_final', [ $this, 'add_inline_css' ] );
	}

	/**
	 * Init function, used to make sure inline fonts can be loaded and for call from WP block admin style enqueueing.
	 *
	 * @access public
	 * @since 7.2
	 * @return void
	 */
	public function init() {

		// Go through our fields and populate $this->fonts.
		$this->loop_fields();

		// Allow filter to add in fonts.
		$this->fonts = apply_filters( 'fusion_google_fonts', $this->fonts );

		// Goes through $this->fonts and adds or removes things as needed.
		$this->process_fonts();

		// Go through $this->fonts and populate $this->remote_link.
		$this->create_remote_link();
	}

	/**
	 * Returns a single instance of the object (singleton).
	 *
	 * @since 7.4
	 * @access public
	 * @return object
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new Avada_Google_Fonts();
		}
		return self::$instance;
	}

	/**
	 * Calls all the other necessary methods to populate and create the link.
	 *
	 * @access public
	 */
	public function enqueue() {

		// If $this->remote_link is not empty then enqueue it.
		if ( 'local' !== Avada()->settings->get( 'gfonts_load_method' ) && '' !== $this->remote_link && false === $this->get_fonts_inline_styles() ) {

			// The "null" version is there to get around a WP-Core bug.
			// See https://core.trac.wordpress.org/ticket/49742.
			wp_enqueue_style( 'avada_google_fonts', $this->remote_link, [], null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters
		}
	}

	/**
	 * Generates preload tags for Google fonts.
	 *
	 * @access public
	 * @since 7.2
	 */
	public function get_preload_tags() {
		$transient_name = 'fusion_gfonts_preload_tags';
		$tags           = get_transient( $transient_name );
		$user_variants  = (array) Avada()->settings->get( 'preload_fonts_variants' );
		$user_subsets   = (array) Avada()->settings->get( 'preload_fonts_subsets' );

		if ( false === $tags ) {
			$tags = '';

			// Get styles.
			$css = $this->get_fonts_inline_styles();

			// Get font styles.
			preg_match_all( '/font-style: (.*);\n/', $css, $font_styles );
			$font_styles = isset( $font_styles[1] ) ? $font_styles[1] : array_shift( $font_styles );

			// Get font weights.
			preg_match_all( '/font-weight: (.*);\n/', $css, $font_weights );
			$font_weights = isset( $font_weights[1] ) ? $font_weights[1] : array_shift( $font_weights );

			// Get font subsets.
			preg_match_all( '/\/\* (.*) \*\//', $css, $subsets );
			$subsets = isset( $subsets[1] ) ? $subsets[1] : array_shift( $subsets );

			// Get font files.
			preg_match_all( '/http.*?\.woff2/', $css, $fonts );
			$fonts = array_shift( $fonts );

			if ( empty( $user_variants ) && empty( $user_subsets ) ) {
				foreach ( $fonts as $font ) {
					$tags .= '<link rel="preload" href="' . $font . '" as="font" type="font/woff2" crossorigin>';
				}
			} else {

				// Loop through all give font variations and pick the ones where variants and subsets match user selection.
				foreach ( $subsets as $index => $subset ) {
					$font_style = 'normal' === $font_styles[ $index ] ? '' : '-italic';

					if ( ( empty( $user_subsets ) || in_array( $subset, $user_subsets ) ) && ( empty( $user_variants ) || in_array( $font_weights[ $index ] . $font_style, $user_variants ) ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
						$tags .= '<link rel="preload" href="' . $fonts[ $index ] . '" as="font" type="font/woff2" crossorigin>';
					}
				}
			}

			set_transient( $transient_name, $tags );
		}

		return $tags;
	}

	/**
	 * Adds googlefont styles inline in dynamic-css.
	 *
	 * @access public
	 * @since 5.1.5
	 * @param string $original_styles The dynamic-css styles.
	 * @return string The dynamic-css styles with any additional stylesheets appended.
	 */
	public function add_inline_css( $original_styles ) {

		if ( empty( $this->fonts ) ) {
			$this->init();
		}

		$font_styles = $this->get_fonts_inline_styles();

		if ( false === $font_styles ) {
			return $original_styles;
		}
		return $font_styles . $original_styles;

	}

	/**
	 * Goes through all our fields and then populates the $this->fonts property.
	 *
	 * @access private
	 */
	private function loop_fields() {

		// Global overrides.
		$has_global_footer = false;
		$has_global_header = false;
		if ( class_exists( 'Fusion_Template_Builder' ) ) {
			$default_layout    = Fusion_Template_Builder::get_default_layout();
			$has_global_footer = (bool) isset( $default_layout['data']['template_terms'] ) && isset( $default_layout['data']['template_terms']['footer'] ) && $default_layout['data']['template_terms']['footer'];
			$has_global_header = (bool) isset( $default_layout['data']['template_terms'] ) && isset( $default_layout['data']['template_terms']['header'] ) && $default_layout['data']['template_terms']['header'];
		}

		$fields = [
			'button_typography',
			'h1_typography',
			'h2_typography',
			'h3_typography',
			'h4_typography',
			'h5_typography',
			'h6_typography',
			'post_title_typography',
			'post_titles_extras_typography',
			'body_typography',
		];

		if ( ! $has_global_footer ) {
			array_push( $fields, 'footer_headings_typography' );
		}

		if ( ! $has_global_header ) {
			array_push( $fields, 'nav_typography' );
			array_push( $fields, 'mobile_menu_typography' );
		}

		$fields = apply_filters( 'awb_typography_fields', $fields );

		foreach ( $fields as $field ) {
			$this->generate_google_font( $field );
		}

		// If we are in the live builder, load the fonts for all typography sets.
		if ( fusion_is_preview_frame() || fusion_is_builder_frame() ) {
			$typography = AWB_Global_Typography()->get_typography();
			if ( ! empty( $typography ) ) {
				foreach ( $typography as $id => $data ) {
					$this->generate_google_font( '', $data );
				}
			}
		}
	}

	/**
	 * Processes the field.
	 *
	 * @access private
	 * @param array   $field The field arguments.
	 * @param boolean $value The value.
	 */
	private function generate_google_font( $field = '', $value = false ) {
		$variant  = '';
		$variants = [];

		// Get the value.
		$value = ! $value ? Avada()->settings->get( $field ) : $value;

		// If we don't have a font-family.
		if ( ! isset( $value['font-family'] ) ) {
			return;
		}

		// If its a var, get the real family, weight and style.
		if ( false !== strpos( $value['font-family'], 'var(' ) ) {
			$value['font-style']  = AWB_Global_Typography()->get_real_value( $value['font-family'], 'font-style' );
			$value['font-weight'] = AWB_Global_Typography()->get_real_value( $value['font-family'], 'font-weight' );
			$value['font-family'] = AWB_Global_Typography()->get_real_value( $value['font-family'] );
		}

		// Not a google family, skip.
		if ( ! isset( $this->google_fonts[ $value['font-family'] ] ) ) {
			return;
		}

		// Convert font-weight to variant or set default to 400, if nothing is set.
		$variant = ! empty( $value['font-weight'] ) ? $value['font-weight'] : '400';

		// If we have a 400 weight, Google uses "regular".
		if ( 400 === $variant || '400' === $variant ) {
			$variant = 'regular';
		}

		// Check for italic.
		if ( ! empty( $value['font-style'] ) && 'italic' === $value['font-style'] ) {
			$variant = 'regular' === $variant ? 'italic' : $variant . 'italic';
		}

		$variants[] = $variant;

		if ( apply_filters( 'awb_add_auto_load_font_variants', true ) ) {

			// Add italics to all fonts.
			if ( false === strpos( $variant, 'italic' ) ) {
				$variants[] = intval( $variant ) ? intval( $variant ) . 'italic' : 'italic';
			}

			// Make 4 main font variants available for body_typography.
			if ( 'body_typography' === $field ) {
				$font_weight = 'regular' === $value['font-weight'] ? 400 : (int) $value['font-weight'];

				// If only italic is set, load non-italic too.
				if ( ! empty( $value['font-style'] ) && 'italic' === $value['font-style'] ) {
					$variants[] = $font_weight;
				}

				// Load bold and bold italic, based on set weight.
				if ( 400 > $font_weight ) {
					$font_weight = 400;
				} elseif ( 600 > $font_weight ) {
					$font_weight = 700;
				} else {
					$font_weight = 900;
				}

				$variants[] = $font_weight;
				$variants[] = $font_weight . 'italic';
			}
		}

		$variants = array_unique( $variants );

		// Add the requested google-font.
		if ( ! isset( $this->fonts[ $value['font-family'] ] ) ) {
			$this->fonts[ $value['font-family'] ] = $variants;
		} else {
			$this->fonts[ $value['font-family'] ] = array_unique( array_merge( $this->fonts[ $value['font-family'] ], $variants ) );
		}
	}

	/**
	 * Determines the validity of the selected font as well as its properties.
	 * This is vital to make sure that the google-font script that we'll generate later
	 * does not contain any invalid options.
	 *
	 * @access private
	 */
	private function process_fonts() {

		// Early exit if font-family is empty.
		if ( empty( $this->fonts ) ) {
			return;
		}

		foreach ( $this->fonts as $font => $variants ) {
			if ( ! isset( $this->google_fonts[ $font ] ) ) {
				unset( $this->fonts[ $font ] );
				continue;
			}

			// Get all valid font variants for this font.
			$font_variants = [];
			if ( isset( $this->google_fonts[ $font ]['variants'] ) ) {
				$font_variants = $this->google_fonts[ $font ]['variants'];
			}

			// Make sure variant names of element / content fonts are correct for intersection.
			foreach ( $variants as $index => $variant ) {
				if ( '400' === (string) $variant ) {
					$variants[ $index ] = 'regular';
				} elseif ( '400italic' === $variant ) {
					$variants[ $index ] = 'italic';
				}
			}

			// Only use valid variants.
			$this->fonts[ $font ] = array_intersect( $variants, $font_variants );
		}
	}

	/**
	 * Creates the google-fonts link.
	 *
	 * @access private
	 */
	private function create_remote_link() {

		// If we don't have any fonts then we can exit.
		if ( empty( $this->fonts ) ) {
			return;
		}

		// Get font-family.
		$link_fonts = [];
		foreach ( $this->fonts as $font => $variants ) {

			$weights = [
				'regular' => [],
				'italic'  => [],
			];

			if ( ( ! $variants || empty( $variants ) || ( isset( $variants[0] ) && empty( $variants[0] ) && ! isset( $variants[1] ) ) ) && isset( $this->google_fonts[ $font ]['variants'] ) ) {
				$variants = $this->google_fonts[ $font ]['variants'];
			}

			foreach ( $variants as $variant ) {
				$weight = ( 'regular' === $variant || 'italic' === $variant ) ? 400 : intval( $variant );
				if ( $weight ) {
					if ( false === strpos( $variant, 'i' ) ) {
						$weights['regular'][] = $weight;
					} else {
						$weights['italic'][] = $weight;
					}
				}
			}

			// Same as array_unique, just faster.
			$weights['regular'] = array_flip( array_flip( $weights['regular'] ) );
			$weights['italic']  = array_flip( array_flip( $weights['italic'] ) );

			// The new Google-Fonts API requires font-weights in a specific order.
			sort( $weights['regular'] );
			sort( $weights['italic'] );

			if ( empty( $weights['regular'] ) ) {
				unset( $weights['regular'] );
			}

			if ( empty( $weights['italic'] ) ) {
				unset( $weights['italic'] );
			}

			// Build the font-family part.
			$link_font = 'family=' . str_replace( ' ', '+', $font );

			// Define if we want italics.
			if ( isset( $weights['italic'] ) ) {
				$link_font .= ':ital';
			}

			if ( empty( $weights ) ) {
				$weights = [
					'regular' => [ 400, 700 ],
				];
			}

			// Build the font-weights part.
			$font_weights_fragments = [];
			if ( ! isset( $weights['italic'] ) ) {
				$font_weights_fragments = $weights['regular'];
			} else {
				if ( isset( $weights['regular'] ) ) {
					foreach ( $weights['regular'] as $weight ) {
						$font_weights_fragments[] = '0,' . $weight;
					}
				}
				if ( isset( $weights['italic'] ) ) {
					foreach ( $weights['italic'] as $weight ) {
						$font_weights_fragments[] = '1,' . $weight;
					}
				}
			}

			if ( ! isset( $weights['italic'] ) && isset( $weights['regular'] ) && 1 === count( $weights['regular'] ) && 400 === $weights['regular'][0] ) {
				$link_fonts[] = $link_font;
				continue;
			}
			$link_font .= ( isset( $weights['italic'] ) ) ? ',wght@' : ':wght@';
			$link_font .= implode( ';', $font_weights_fragments );

			$link_fonts[] = $link_font;
		}

		$this->remote_link = 'https://fonts.googleapis.com/css2?' . implode( '&', $link_fonts );

		if ( 'block' !== Avada()->settings->get( 'font_face_display' ) ) {
			$this->remote_link .= '&display=swap';
		}
	}

	/**
	 * Get the CSS for local fonts.
	 *
	 * @access public
	 * @since 1.0
	 * @param string $styles The styles from the remote URL.
	 * @return string
	 */
	public function get_local_fonts_css( $styles ) {

		// If we don't have any fonts then we can exit.
		if ( empty( $this->fonts ) ) {
			return;
		}

		$family = new Fusion_GFonts_Downloader( '', $styles );
		return $family->get_fontface_css();
	}

	/**
	 * Return an array of all available Google Fonts.
	 *
	 * @access private
	 * @return array All Google Fonts.
	 */
	private function get_google_fonts() {

		if ( null === $this->google_fonts || empty( $this->google_fonts ) ) {

			$fonts = include_once wp_normalize_path( FUSION_LIBRARY_PATH . '/inc/googlefonts-array.php' );

			$this->google_fonts = [];
			if ( is_array( $fonts ) ) {
				foreach ( $fonts['items'] as $font ) {
					$this->google_fonts[ $font['family'] ] = [
						'label'    => $font['family'],
						'variants' => $font['variants'],
					];
				}
			}
		}

		return $this->google_fonts;

	}

	/**
	 * Get the contents of googlefonts so that they can be added inline.
	 *
	 * @access protected
	 * @since 5.1.5
	 * @return string|false
	 */
	protected function get_fonts_inline_styles() {

		$transient_name = 'avada_googlefonts_contents';
		if ( '' !== Fusion_Multilingual::get_active_language() && 'all' !== Fusion_Multilingual::get_active_language() ) {
			$transient_name .= '_' . Fusion_Multilingual::get_active_language();
		}

		$skip_transient = apply_filters( 'fusion_google_fonts_extra', false ) || ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() );
		$contents       = get_transient( $transient_name );

		if ( false === $contents || $skip_transient ) {

			// If link is empty, early exit.
			if ( empty( $this->remote_link ) ) {
				set_transient( $transient_name, 'failed', DAY_IN_SECONDS );
				return false;
			}

			// Get remote HTML file.
			$response = wp_remote_get(
				$this->remote_link,
				[
					'user-agent' => apply_filters( 'avada_google_fonts_user_agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.190 Safari/537.36' ),
				]
			);

			// Check for errors.
			if ( is_wp_error( $response ) ) {
				set_transient( $transient_name, 'failed', DAY_IN_SECONDS );
				return false;
			}

			// Parse remote HTML file.
			$contents = wp_remote_retrieve_body( $response );

			// Check for error.
			if ( empty( $contents ) ) {
				set_transient( $transient_name, 'failed', DAY_IN_SECONDS );
				return false;
			}

			// Store remote HTML file in transient, expire after 24 hours. Only do so if no extra per page files added.
			if ( ! $skip_transient ) {
				set_transient( $transient_name, $contents, DAY_IN_SECONDS );
			}
		}

		// Return false if we were unable to get the contents of the googlefonts from remote.
		if ( 'failed' === $contents ) {
			return false;
		}

		// If we're using local, early exit after getting the styles.
		if ( 'local' === Avada()->settings->get( 'gfonts_load_method' ) ) {
			return $this->get_local_fonts_css( $contents );
		}

		// If we got this far then we can safely return the contents.
		return $contents;
	}
}
