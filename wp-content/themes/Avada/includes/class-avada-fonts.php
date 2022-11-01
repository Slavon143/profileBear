<?php
/**
 * Fonts handling.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      3.8
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Fonts handling.
 */
class Avada_Fonts {

	/**
	 * Constructor.
	 *
	 * @access  public
	 */
	public function __construct() {
		add_filter( 'upload_mimes', [ $this, 'mime_types' ] );
	}

	/**
	 * Allow uploading font file types.
	 *
	 * @param array $mimes The mime types allowed.
	 * @access public
	 */
	public function mime_types( $mimes ) {

		$mimes['woff2'] = 'font/woff2';
		$mimes['woff']  = $this->get_mime( 'woff' );
		$mimes['ttf']   = $this->get_mime( 'ttf' );
		$mimes['eot']   = $this->get_mime( 'eot' );
		$mimes['svg']   = $this->get_mime( 'svg' );

		return $mimes;

	}

	/**
	 * Get the MIME type of the font-files
	 * by examining font-files included in the theme.
	 *
	 * @access private
	 * @since 5.2
	 * @param string $file_type The file-type we want to check.
	 * @return string
	 */
	private function get_mime( $file_type ) {
		$path = FUSION_LIBRARY_URL . '/assets/fonts/icomoon/icomoon.' . $file_type;
		if ( file_exists( $path ) && function_exists( 'mime_content_type' ) ) {
			return mime_content_type( $path );
		}
		return 'font/' . $file_type;

	}
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
