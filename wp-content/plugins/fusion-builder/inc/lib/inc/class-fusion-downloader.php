<?php
/**
 * Downloads files.
 * Used primarily for fonts.
 *
 * @package Fusion-Library
 * @since 1.8
 */

/**
 * Downloads files.
 *
 * @since 1.8
 */
class Fusion_Downloader {

	/**
	 * The URL we want to download.
	 *
	 * @access protected
	 * @since 1.8
	 * @var string
	 */
	protected $url;

	/**
	 * The folder-name. Used to create the $folder_path.
	 *
	 * @access protected
	 * @since 1.8
	 * @var string
	 */
	protected $folder_name;

	/**
	 * The folder-URL.
	 *
	 * @access protected
	 * @since 1.8
	 * @var string
	 */
	protected $folder_url;

	/**
	 * The folder-path where we want to save our file.
	 *
	 * @access protected
	 * @since 1.8
	 * @var string
	 */
	protected $folder_path;

	/**
	 * The target filename of the file we want to download.
	 *
	 * @access protected
	 * @since 1.8
	 * @var string
	 */
	protected $filename;

	/**
	 * The target path.
	 *
	 * @access private
	 * @since 1.8
	 * @var string
	 */
	private $path;

	/**
	 * Constructor.
	 *
	 * @access protected
	 * @since 1.8
	 * @param string $url         The source URL.
	 * @param string $folder_name The target folder-name.
	 * @param string $filename    The target filename.
	 */
	public function __construct( $url, $folder_name, $filename = false ) {

		// Populate the object properties.
		$this->url         = $url;
		$this->folder_name = $folder_name;
		$this->folder_path = self::get_root_path( $this->folder_name );
		$this->folder_url  = self::get_root_url( $this->folder_name );
		$this->filename    = $filename ? $filename : self::get_filename_from_url( $url );
		$this->path        = $this->folder_path . '/' . $this->filename;
	}

	/**
	 * Returns the $wp_filesystem global.
	 *
	 * @access private
	 * @since 5.5.2
	 * @return WP_Filesystem
	 */
	private function filesystem() {
		return Fusion_Helper::init_filesystem();
	}

	/**
	 * Downloads a file and saves it locally.
	 *
	 * @access public
	 * @since 5.5.2
	 * @return bool
	 */
	public function download_file() {

		// If the file exists no reason to do anything.
		if ( file_exists( $this->path ) ) {
			return true;
		}

		// If the folder doesn't exist, create it.
		if ( ! file_exists( $this->folder_path ) ) {
			$this->filesystem()->mkdir( $this->folder_path, FS_CHMOD_DIR );
		}

		// Write file.
		return $this->filesystem()->put_contents( $this->path, $this->get_remote_url_contents(), FS_CHMOD_FILE );
	}

	/**
	 * Gets the remote URL contents.
	 *
	 * @access private
	 * @since 5.5.2
	 * @return string The contents of the remote URL.
	 */
	public function get_remote_url_contents() {
		$response = wp_remote_get( $this->url );
		if ( is_wp_error( $response ) ) {
			return;
		}
		$html = wp_remote_retrieve_body( $response );
		if ( is_wp_error( $html ) ) {
			return;
		}
		return $html;
	}

	/**
	 * Gets the filename by breaking-down the URL parts.
	 *
	 * @static
	 * @access public
	 * @since 5.5.2
	 * @param string $url The URL.
	 * @return string     The filename.
	 */
	public static function get_filename_from_url( $url ) {
		$url_parts   = explode( '/', $url );
		$parts_count = count( $url_parts );
		if ( 1 < $parts_count ) {
			return $url_parts[ count( $url_parts ) - 1 ];
		}
		return $url;
	}

	/**
	 * Gets the root folder path.
	 * Other paths are built based on this.
	 *
	 * @static
	 * @since 1.8
	 * @access public
	 * @param string $folder_name The folder-name.
	 * @return string
	 */
	public static function get_root_path( $folder_name ) {
		// Get the upload directory for this site.
		$upload_dir = wp_upload_dir();
		$path       = untrailingslashit( wp_normalize_path( $upload_dir['basedir'] ) ) . '/' . $folder_name;

		// If the folder doesn't exist, create it.
		if ( ! file_exists( $path ) ) {
			$filesystem = Fusion_Helper::init_filesystem();
			$filesystem->mkdir( $path, FS_CHMOD_DIR );
		}

		// Return the path.
		$folder_name_parts = explode( '/', $folder_name );
		$filter_name       = str_replace( '-', '_', $folder_name_parts[0] ) . '_root_path';
		return apply_filters( $filter_name, $path );
	}

	/**
	 * Gets the root folder url.
	 * Other urls are built based on this.
	 *
	 * @static
	 * @access public
	 * @since 1.8
	 * @param string $folder_name The folder-name.
	 * @return string
	 */
	public static function get_root_url( $folder_name ) {

		// Get the upload directory for this site.
		$upload_dir = wp_upload_dir();

		// The URL.
		$url = trailingslashit( $upload_dir['baseurl'] );
		// Take care of domain mapping.
		// When using domain mapping we have to make sure that the URL to the file
		// does not include the original domain but instead the mapped domain.
		if ( defined( 'DOMAIN_MAPPING' ) && DOMAIN_MAPPING ) {
			if ( function_exists( 'domain_mapping_siteurl' ) && function_exists( 'get_original_url' ) ) {
				$mapped_domain   = domain_mapping_siteurl( false );
				$original_domain = get_original_url( 'siteurl' );
				$url             = str_replace( $original_domain, $mapped_domain, $url );
			}
		}
		$folder_name_parts = explode( '/', $folder_name );
		$filter_name       = str_replace( '-', '_', $folder_name_parts[0] ) . '_root_url';
		return apply_filters( $filter_name, untrailingslashit( esc_url_raw( $url ) ) . '/' . $folder_name );
	}

	/**
	 * Return the new file URL.
	 *
	 * @access public
	 * @since 2.2.0
	 * @return string
	 */
	public function get_new_url() {
		return trailingslashit( $this->folder_url ) . $this->filename;
	}
}
