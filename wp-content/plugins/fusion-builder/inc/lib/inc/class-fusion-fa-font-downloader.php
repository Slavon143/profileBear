<?php
/**
 * Downloads Font Awesome font locally and generates the @font-face CSS for them.
 * The main reasons for this is the GDPR & performance.
 *
 * @package Fusion Library
 * @since 1.8
 */

/**
 * Downloads Font Awesome font locally and generates the @font-face CSS for them.
 *
 * @since 1.8
 */
class Fusion_FA_Font_Downloader {

	/**
	 * The subset.
	 *
	 * @access protected
	 * @since 1.8
	 * @var string
	 */
	protected $subset;

	/**
	 * The name of the folder where files for this font are stored.
	 *
	 * @access protected
	 * @since 1.8
	 * @var string
	 */
	protected $folder_name = 'fusion-fa-font';

	/**
	 * The URL where files for this font are stored.
	 *
	 * @access protected
	 * @since 1.8
	 * @var string
	 */
	protected $folder_url;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 1.8
	 * @param string $subset The Font Awesome subset we're dealing with.
	 */
	public function __construct( $subset ) {
		$this->subset     = $subset;
		$this->folder_url = Fusion_Downloader::get_root_url( $this->folder_name );
		$this->font       = $this->get_font_family();
	}

	/**
	 * Gets the @font-face CSS for all variants this font-family contains.
	 *
	 * @access public
	 * @since 1.8
	 * @param array $variants The variants we want to get.
	 * @return string
	 */
	public function get_fontface_css( $variants = [] ) {
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
	 * Gets the preload tags for all variants this font-family contains.
	 *
	 * @access public
	 * @since 3.2
	 * @param array $variants The variants we want to get.
	 * @return string
	 */
	public function get_preload_tags( $variants = [] ) {
		if ( ! $this->font ) {
			return;
		}
		$tags = '';

		// If $variants is empty then use all variants available.
		if ( empty( $variants ) ) {
			$variants = $this->font['variants'];
		}

		// Download files.
		$this->download_font_family( $variants );

		// Create the tag.
		foreach ( $variants as $variant ) {
			$tags .= $this->get_variant_preload_tag( $variant );
		}

		return $tags;
	}

	/**
	 * Get the @font-face CSS for a specific variant in this font-family.
	 *
	 * @access public
	 * @since 1.8
	 * @param string $variant The variant.
	 * @return string
	 */
	public function get_variant_fontface_css( $variant ) {
		$font_face = "@font-face{font-family:'" . $this->font['family'] . "';";

		// Set font style.
		$font_face .= 'font-style:normal;';

		// Get the font-weight.
		$font_face .= "font-weight:{$variant};";

		// Get the font-url.
		$font_url = str_replace( [ 'http://', 'https://' ], '//', $this->get_variant_local_url( $variant ) );

		// Get the font-format.
		$font_face .= "src: url('{$font_url}') format('woff');}";

		if ( 'fas' === $this->subset ) {
			$font_face .= '.fa,';
		}

		$font_face .= '.' . $this->subset . "{font-family:'" . $this->font['family'] . "';font-weight:" . $variant . '}';

		return $font_face;
	}

	/**
	 * Get the preload tag for a specific variant in this font-family.
	 *
	 * @access public
	 * @since 3.2
	 * @param string $variant The variant.
	 * @return string
	 */
	public function get_variant_preload_tag( $variant ) {
		$font_url = str_replace( [ 'http://', 'https://' ], '//', $this->get_variant_local_url( $variant ) );
		$tag      = '<link rel="preload" href="' . $font_url . '" as="font" type="font/woff2" crossorigin>';

		return $tag;
	}

	/**
	 * Gets the local URL for a variant.
	 *
	 * @access public
	 * @since 1.8
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
	 * Get an array of font-files.
	 * Only contains the filenames.
	 *
	 * @access public
	 * @since 1.8
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
	 * @since 1.8
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
	 * @since 1.8
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
	 * Returns the $wp_filesystem global.
	 *
	 * @access private
	 * @since 1.8
	 * @return WP_Filesystem
	 */
	private function filesystem() {
		return Fusion_Helper::init_filesystem();
	}

	/**
	 * Get a font-family from the array of google-fonts.
	 *
	 * @access public
	 * @since 1.8
	 * @return array
	 */
	public function get_font_family() {

		$fonts = [
			'fab' => [
				'family'   => 'Font Awesome 5 Brands',
				'variants' => [ 'normal' ],
				'files'    => [
					'normal' => 'https://pro.fontawesome.com/releases/v' . Fusion_Font_Awesome::$fa_version . '/webfonts/fa-brands-400.woff',
				],
			],
			'fal' => [
				'family'   => 'Font Awesome 5 Pro',
				'variants' => [ '300' ],
				'files'    => [
					'300' => 'https://pro.fontawesome.com/releases/v' . Fusion_Font_Awesome::$fa_version . '/webfonts/fa-light-300.woff',
				],
			],
			'fas' => [
				'family'   => 'Font Awesome 5 Pro',
				'variants' => [ '900' ],
				'files'    => [
					'900' => 'https://pro.fontawesome.com/releases/v' . Fusion_Font_Awesome::$fa_version . '/webfonts/fa-solid-900.woff',
				],
			],
			'far' => [
				'family'   => 'Font Awesome 5 Pro',
				'variants' => [ '400' ],
				'files'    => [
					'400' => 'https://pro.fontawesome.com/releases/v' . Fusion_Font_Awesome::$fa_version . '/webfonts/fa-regular-400.woff',
				],
			],
		];

		return isset( $fonts[ $this->subset ] ) ? $fonts[ $this->subset ] : [];
	}

	/**
	 * Download font-family files.
	 *
	 * @access public
	 * @since 1.8
	 * @param array $variants An array of variants to download. Leave empty to download all.
	 * @return void
	 */
	public function download_font_family( $variants = [] ) {
		if ( isset( $this->font['files'] ) ) {
			if ( empty( $variants ) ) {
				$variants = $this->font['variants'];
			}
			foreach ( $this->font['files'] as $variant => $file ) {
				if ( in_array( $variant, $variants ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
					$downloader = new Fusion_Downloader( $file, $this->folder_name );
					$downloader->download_file();
				}
			}
		}
	}
}
