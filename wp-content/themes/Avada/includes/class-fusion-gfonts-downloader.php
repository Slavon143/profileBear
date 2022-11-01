<?php
/**
 * Downloads Google-Fonts locally and generates the @font-face CSS for them.
 * The main reasons for this is the GDPR & performance.
 *
 * @package Avada
 * @since 5.5.2
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Downloads Google-Fonts locally and generates the @font-face CSS for them.
 *
 * @since 5.5.2
 */
class Fusion_GFonts_Downloader {

	/**
	 * The font-family.
	 *
	 * @access private
	 * @since 5.5.2
	 * @var string
	 */
	private $family;

	/**
	 * The styles from the normal Google-Fonts request.
	 *
	 * @access private
	 * @since 5.5.2
	 * @var string
	 */
	private $styles = '';

	/**
	 * The path where files for this font are stored.
	 *
	 * @access private
	 * @since 5.5.2
	 * @var string
	 */
	private $folder_path;

	/**
	 * The URL where files for this font are stored.
	 *
	 * @access private
	 * @since 5.5.2
	 * @var string
	 */
	private $folder_url;

	/**
	 * The font-family arguments from the google-fonts API.
	 *
	 * @access private
	 * @since 5.5.2
	 * @var array
	 */
	private $font;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 5.5.2
	 * @param string $family The font-family we're dealing with.
	 * @param string $styles The styles from the google-fonts normal request.
	 */
	public function __construct( $family = '', $styles = '' ) {
		$this->family      = $family;
		$this->styles      = $styles;
		$this->folder_path = Fusion_Downloader::get_root_path( 'fusion-gfonts' ) . '/' . sanitize_key( $this->family );
		$this->folder_url  = Fusion_Downloader::get_root_url( 'fusion-gfonts' ) . '/' . sanitize_key( $this->family );
		$this->font        = $this->get_font_family();
	}

	/**
	 * Gets the @font-face CSS.
	 *
	 * @access public
	 * @since 5.5.2
	 * @param array $variants The variants we want to get.
	 * @return string
	 */
	public function get_fontface_css( $variants = [] ) {

		if ( ! empty( $this->styles ) ) {
			$css         = $this->styles;
			$folder_name = 'fusion-gfonts';

			preg_match_all( '/https\:.*?\.woff2/', $css, $matches );

			$matches = array_shift( $matches );

			foreach ( $matches as $match ) {
				if ( 0 === strpos( $match, 'https://fonts.gstatic.com' ) ) {
					$file = new Fusion_Downloader( $match, $folder_name );
					$file->download_file();

					$new_url = $file->get_new_url();
					if ( $new_url ) {
						$css = str_replace( $match, $new_url, $css );
					}
				}
			}
			return $css;
		}

		if ( ! $this->font ) {
			return;
		}
		$font_face = '';

		// If $variants is empty then use all variants available.
		if ( empty( $variants ) ) {
			$variants = $this->font['variants'];
		}

		// Download files.
		$this->download_font_family( $variants );

		// Create the @font-face CSS.
		foreach ( $variants as $variant ) {
			$font_face .= $this->get_variant_fontface_css( $variant );
		}
		return $font_face;
	}

	/**
	 * Get the @font-face CSS for a specific variant in this font-family.
	 *
	 * @access public
	 * @since 5.5.2
	 * @param string $variant The variant.
	 * @return string
	 */
	public function get_variant_fontface_css( $variant ) {
		$font_face = "@font-face{font-family:'{$this->family}';";

		// Get the font-style.
		$font_style = ( false !== strpos( $variant, 'italic' ) ) ? 'italic' : 'normal';
		$font_face .= "font-style:{$font_style};";

		// Set font display.
		$font_face_display = Avada()->settings->get( 'font_face_display' );
		$font_face_display = ( 'block' === $font_face_display ) ? 'block' : 'swap';

		$font_face .= 'font-display: ' . $font_face_display . ';';

		// Get the font-weight.
		$font_weight = '400';
		$font_weight = str_replace( 'italic', '', $variant );
		$font_weight = ( ! $font_weight || 'regular' === $font_weight ) ? '400' : $font_weight;
		$font_face  .= "font-weight:{$font_weight};";

		// Get the font-names.
		$font_name_0 = $this->get_local_font_name( $variant, false );
		$font_name_1 = $this->get_local_font_name( $variant, true );
		$font_face  .= "src:local('{$font_name_0}'),";
		if ( $font_name_0 !== $font_name_1 ) {
			$font_face .= "local('{$font_name_1}'),";
		}

		// Get the font-url.
		$font_url = $this->get_variant_local_url( $variant );

		// Get the font-format.
		$font_format = ( strpos( $font_url, '.woff2' ) ) ? 'woff2' : 'truetype';
		$font_format = ( strpos( $font_url, '.woff' ) && ! strpos( $font_url, '.woff2' ) ) ? 'woff' : $font_format;
		$font_face  .= "url({$font_url}) format('{$font_format}');}";

		return $font_face;
	}

	/**
	 * Gets the local URL for a variant.
	 *
	 * @access public
	 * @since 5.5.2
	 * @param string $variant The variant.
	 * @return string         The URL.
	 */
	public function get_variant_local_url( $variant ) {
		$local_urls = $this->get_font_files_urls_local();

		if ( empty( $local_urls ) ) {
			return;
		}

		// Return the specific variant if we can find it.
		if ( isset( $local_urls[ $variant ] ) ) {
			return $local_urls[ $variant ];
		}

		// Return regular if the one we want could not be found.
		if ( isset( $local_urls['regular'] ) ) {
			return $local_urls['regular'];
		}

		// Return the first available if all else failed.
		$vals = array_values( $local_urls );
		return $vals[0];
	}

	/**
	 * Get the name of the font-family.
	 * This is used by @font-face in case the user already has the font downloaded locally.
	 *
	 * @access public
	 * @since 5.5.2
	 * @param string $variant The variant.
	 * @param bool   $compact Whether we want the compact formatting or not.
	 * @return string
	 */
	public function get_local_font_name( $variant, $compact = false ) {
		$variant_names = [
			'100'       => 'Thin',
			'100i'      => 'Thin Italic',
			'100italic' => 'Thin Italic',
			'200'       => 'Extra-Light',
			'200i'      => 'Extra-Light Italic',
			'200italic' => 'Extra-Light Italic',
			'300'       => 'Light',
			'300i'      => 'Light Italic',
			'300italic' => 'Light Italic',
			'400'       => 'Regular',
			'regular'   => 'Regular',
			'400i'      => 'Regular Italic',
			'italic'    => 'Italic',
			'400italic' => 'Regular Italic',
			'500'       => 'Medium',
			'500i'      => 'Medium Italic',
			'500italic' => 'Medium Italic',
			'600'       => 'Semi-Bold',
			'600i'      => 'Semi-Bold Italic',
			'600italic' => 'Semi-Bold Italic',
			'700'       => 'Bold',
			'700i'      => 'Bold Italic',
			'700italic' => 'Bold Italic',
			'800'       => 'Extra-Bold',
			'800i'      => 'Extra-Bold Italic',
			'800italic' => 'Extra-Bold Italic',
			'900'       => 'Black',
			'900i'      => 'Black Italic',
			'900italic' => 'Black Italic',
		];

		$variant = (string) $variant;
		if ( $compact ) {
			if ( isset( $variant_names[ $variant ] ) ) {
				return str_replace( [ ' ', '-' ], '', $this->family ) . '-' . str_replace( [ ' ', '-' ], '', $variant_names[ $variant ] );
			}
			return str_replace( [ ' ', '-' ], '', $this->family );
		}

		if ( isset( $variant_names[ $variant ] ) ) {
			return $this->family . ' ' . $variant_names[ $variant ];
		}
		return $this->family;
	}

	/**
	 * Get an array of font-files.
	 * Only contains the filenames.
	 *
	 * @access public
	 * @since 5.5.2
	 * @return array
	 */
	public function get_font_files() {
		$files       = [];
		$remote_urls = $this->get_font_files_urls_remote();
		foreach ( $remote_urls as $key => $url ) {
			$files[ $key ] = Fusion_Downloader::get_filename_from_url( $url );
		}
		return $files;
	}

	/**
	 * Get the remote URLs for the font-files.
	 *
	 * @access public
	 * @since 5.5.2
	 * @return array
	 */
	public function get_font_files_urls_remote() {
		if ( isset( $this->font['files'] ) ) {
			return $this->font['files'];
		}
		return [];
	}

	/**
	 * Get an array of local file URLs.
	 *
	 * @access public
	 * @since 5.5.2
	 * @return array
	 */
	public function get_font_files_urls_local() {
		$urls  = [];
		$files = $this->get_font_files();
		foreach ( $files as $key => $file ) {
			$urls[ $key ] = $this->folder_url . '/' . $file;
		}
		return $urls;
	}

	/**
	 * Get an array of local file paths.
	 *
	 * @access public
	 * @since 5.5.2
	 * @return array
	 */
	public function get_font_files_paths() {
		$paths = [];
		$files = $this->get_font_files();
		foreach ( $files as $key => $file ) {
			$paths[ $key ] = $this->folder_path . '/' . $file;
		}
		return $paths;
	}

	/**
	 * Get a font-family from the array of google-fonts.
	 *
	 * @access public
	 * @since 5.5.2
	 * @return array
	 */
	public function get_font_family() {

		// Get the fonts array.
		$fonts = $this->get_fonts();

		// Make sure we've got fonts.
		if ( isset( $fonts['items'] ) ) {
			$fonts = $fonts['items'];

			// Loop array of fonts.
			foreach ( $fonts as $font ) {

				// When we find the font, return it.
				if ( isset( $font['family'] ) && $this->family === $font['family'] ) {
					return $font;
				}
			}
		}
	}

	/**
	 * Get the font defined in the google-fonts API.
	 *
	 * @access private
	 * @since 5.5.2
	 * @return array
	 */
	private function get_fonts() {
		return include FUSION_LIBRARY_PATH . '/inc/googlefonts-array.php';
	}

	/**
	 * Download font-family files.
	 *
	 * @access public
	 * @since 5.5.2
	 * @param array $variants An array of variants to download. Leave empty to download all.
	 * @return void
	 */
	public function download_font_family( $variants = [] ) {
		if ( isset( $this->font['files'] ) ) {
			if ( empty( $variants ) ) {
				$variants = $this->font['variants'];
			}
			foreach ( $this->font['files'] as $variant => $file ) {
				if ( in_array( $variant, $variants ) ) { // phpcs:ignore WordPress.PHP.StrictInArray
					$file = new Fusion_Downloader( $file, 'fusion-gfonts/' . sanitize_key( $this->family ) );
					$file->download_file();
				}
			}
		}
	}
}
