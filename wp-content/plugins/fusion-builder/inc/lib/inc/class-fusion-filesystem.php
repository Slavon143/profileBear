<?php
/**
 * Filesystem methods.
 * Used primarily in the compilers.
 *
 * @package Fusion-Library
 * @since 1.0.0
 */

/**
 * Extra methods that are necessary for the compilers.
 */
class Fusion_Filesystem {

	/**
	 * The file relative to wp-content/uploads.
	 * No '/' at the beginning.
	 *
	 * @access private
	 * @since 1.0.0
	 * @var string
	 */
	private $file = '';

	/**
	 * The file path.
	 *
	 * @access private
	 * @since 1.0.0
	 * @var string
	 */
	private $path = '';

	/**
	 * The $wp_filesystem object.
	 *
	 * @access private
	 * @since 1.0.0
	 * @var object WP_Filesystem
	 */
	private $wp_filesystem;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 1.0.0
	 * @param string $file   The file name.
	 * @param string $folder The folder-name (if any).
	 */
	public function __construct( $file = '', $folder = '' ) {

		// Set the $wp_filesystem property.
		$this->wp_filesystem = Fusion_Helper::init_filesystem();

		// Apply the filter for the folder-name.
		$folder = apply_filters( 'fusion_compiler_filesystem_folder_name', $folder );

		if ( $file || $folder ) {
			// Set the $file property.
			$this->set_file( $file, $folder );
			// Set the $path property.
			$this->set_path();
		}
	}

	/**
	 * Sets the $path property.
	 *
	 * @access private
	 * @since 1.0.0
	 * @param string $file   The filename.
	 * @param string $folder The folder in which the file should go.
	 * @return void
	 */
	private function set_file( $file, $folder = '' ) {

		// Remove '/' from the beginning of the string.
		$file = ltrim( $file, '/' );
		if ( $folder ) {
			$folder = ltrim( $folder, '/' );
		}
		$this->file = ltrim( wp_normalize_path( $folder . '/' . $file ), '/' );
		$this->file = apply_filters( 'fusion_compiler_filesystem_filename', $this->file );

	}

	/**
	 * Sets the $path property.
	 *
	 * @access private
	 * @since 1.0.0
	 * @return void
	 */
	private function set_path() {

		// Get the upload directory for this site.
		$upload_dir = wp_upload_dir();

		// Build the path.
		$this->path = wp_normalize_path( $this->get_root_path() . '/' . $this->file );

	}

	/**
	 * Gets the $path property.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return string
	 */
	public function get_path() {
		return $this->path;
	}

	/**
	 * Write file.
	 *
	 * @access public
	 * @since 1.0.0
	 * @param string $content The file contents.
	 * @return bool Whether the file-write was successful or not.
	 */
	public function write_file( $content ) {
		// Check if the file is writable.
		if ( ! $this->is_writable() ) {
			return false;
		}

		if ( ! $this->wp_filesystem->put_contents( $this->path, $content ) ) {
			// Writing to the file failed.
			return false;
		}
		return true;

	}

	/**
	 * Is the file writable?
	 *
	 * @access public
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_writable() {
		$directory_separator = '/';

		// Build the folder path.
		$file_parts = explode( $directory_separator, $this->path );
		if ( 2 <= count( $file_parts ) ) {
			unset( $file_parts[ count( $file_parts ) - 1 ] );
		}
		$folder_path = implode( $directory_separator, $file_parts );

		// Does the folder exist?
		if ( file_exists( $folder_path ) ) {
			// Folder exists, but is it actually writable?
			if ( ! $this->wp_filesystem->is_writable( $folder_path ) ) {
				// Folder is not writable.
				// Does the file exist?
				if ( ! file_exists( $this->path ) ) {
					// If the file does not exist, then we can't create it
					// since its parent folder is not writable.
					return false;
				} else {
					// The file exists. Is it writable?
					if ( ! $this->wp_filesystem->is_writable( $this->path ) ) {
						// Nope, it's not writable.
						return false;
					}
				}
			} else {
				// The folder is writable.
				// Does the file exist?
				if ( file_exists( $this->path ) ) {
					// File exists. Is it writable?
					if ( ! $this->wp_filesystem->is_writable( $this->path ) ) {
						// Nope, it's not writable.
						return false;
					}
				}
			}
		} else {
			// Can we create the folder?
			// returns true if yes and false if not.
			$permissions = ( defined( 'FS_CHMOD_DIR' ) ) ? FS_CHMOD_DIR : 0755;
			return $this->wp_filesystem->mkdir( $folder_path, $permissions );
		}

		// If we passed all of the above tests
		// then the file is writable.
		return true;
	}

	/**
	 * Gets the URL to the file.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return string
	 */
	public function get_url() {

		// Get the file URL.
		$url = trailingslashit( $this->get_root_url() ) . $this->file;

		// Get the scheme.
		$scheme = null;
		// We use get_option() instead of site_url() or get_site_url() because these dedicated functions
		// use set_url_scheme to set the protocol and we need to check what is actually saved in the db.
		if ( false !== strpos( 'https://', get_option( 'siteurl' ) ) ) {
			$scheme = 'https';
		}

		// Return the URL, with the correct scheme applied.
		return set_url_scheme( $url, $scheme );
	}

	/**
	 * Gets the root folder path.
	 * Other paths are built based on this.
	 *
	 * @since 1.5
	 * @access public
	 * @return string
	 */
	public function get_root_path() {
		// Get the upload directory for this site.
		$upload_dir = wp_upload_dir();
		// Return the path.
		return apply_filters( 'fusion_compiler_filesystem_root_path', untrailingslashit( wp_normalize_path( $upload_dir['basedir'] ) ) );
	}

	/**
	 * Gets the root folder url.
	 * Other urls are built based on this.
	 *
	 * @since 1.5
	 * @access public
	 * @return string
	 */
	public function get_root_url() {

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
		return apply_filters( 'fusion_compiler_filesystem_root_url', untrailingslashit( esc_url_raw( $url ) ) );
	}
}
