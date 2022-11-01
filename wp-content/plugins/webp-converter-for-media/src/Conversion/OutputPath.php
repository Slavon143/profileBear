<?php

namespace WebpConverter\Conversion;

use WebpConverter\Conversion\Format\FormatFactory;

/**
 * Generates output paths from source paths.
 */
class OutputPath {

	/**
	 * @var FormatFactory
	 */
	private $format_factory;

	/**
	 * @var string|null
	 */
	private $path_wp_content_dir = null;

	/**
	 * @var string|null
	 */
	private $path_output_dir = null;

	public function __construct( FormatFactory $format_factory = null ) {
		$this->format_factory = $format_factory ?: new FormatFactory();
	}

	/**
	 * Generates output path from path of source image.
	 *
	 * @param string $path           Server path of source image.
	 * @param bool   $create_dir     Create output directory structure?
	 * @param string $file_extension Output format extension.
	 *
	 * @return string|null Server path for output image.
	 */
	public function get_path( string $path, bool $create_dir = false, string $file_extension = '' ) {
		$paths = $this->get_paths( $path, $create_dir, [ $file_extension ] );
		return $paths[0] ?? null;
	}

	/**
	 * Generates output paths from paths of source image for all output formats.
	 * Creates directory structure of output path, if it does not exist.
	 *
	 * @param string   $path            Server path of source image.
	 * @param bool     $create_dir      Create output directory structure?
	 * @param string[] $file_extensions Output format extensions.
	 *
	 * @return string[] Server paths for output images.
	 */
	public function get_paths( string $path, bool $create_dir = false, array $file_extensions = null ): array {
		$new_path = $this->get_directory_path( $path );
		if ( ! $new_path || ( $create_dir && ! $this->make_directories( $this->check_directories( $new_path ) ) ) ) {
			return [];
		}

		$extensions     = $this->format_factory->get_format_extensions();
		$path_extension = strtolower( pathinfo( $new_path, PATHINFO_EXTENSION ) );
		$paths          = [];
		foreach ( $extensions as $extension ) {
			if ( $extension === $path_extension ) {
				continue;
			}

			if ( ( $file_extensions === null ) || in_array( $extension, $file_extensions, true ) ) {
				$paths[] = sprintf( '%1$s.%2$s', $new_path, $extension );
			}
		}
		return $paths;
	}

	/**
	 * Generates output path from path of source directory.
	 *
	 * @param string $path Server path of source directory.
	 *
	 * @return string|null Server paths for output directory.
	 */
	public function get_directory_path( string $path ) {
		$webp_root   = $this->get_output_dir();
		$wp_content  = $this->get_wp_content_dir();
		$output_path = str_replace(
			preg_replace( '/(\/|\\\\)/', DIRECTORY_SEPARATOR, $wp_content ) ?: '',
			'',
			preg_replace( '/(\/|\\\\)/', DIRECTORY_SEPARATOR, $path ) ?: ''
		);
		$output_path = trim( $output_path, '\/' );

		if ( ! $output_path ) {
			return null;
		}

		return sprintf( '%1$s/%2$s', $webp_root, $output_path );
	}

	/**
	 * Checks if directories for output path exist.
	 *
	 * @param string $path Server path of output.
	 *
	 * @return string[] Directory paths to be created.
	 */
	private function check_directories( string $path ): array {
		$current = dirname( $path );
		$paths   = [];
		while ( ! file_exists( $current ) ) {
			$paths[] = $current;
			$current = dirname( $current );
		}
		return $paths;
	}

	/**
	 * Creates new directories.
	 *
	 * @param string[] $paths Output directory paths to be created.
	 *
	 * @return bool Paths created successfully?
	 */
	private function make_directories( array $paths ): bool {
		$paths = array_reverse( $paths );
		foreach ( $paths as $path ) {
			if ( ! is_writable( dirname( $path ) ) ) {
				return false;
			}
			mkdir( $path );
		}
		return true;
	}

	private function get_wp_content_dir(): string {
		if ( $this->path_wp_content_dir === null ) {
			$this->path_wp_content_dir = dirname( apply_filters( 'webpc_dir_path', '', 'uploads' ) );
		}
		return $this->path_wp_content_dir;
	}

	private function get_output_dir(): string {
		if ( $this->path_output_dir === null ) {
			$this->path_output_dir = apply_filters( 'webpc_dir_path', '', 'webp' );
		}
		return $this->path_output_dir;
	}
}
