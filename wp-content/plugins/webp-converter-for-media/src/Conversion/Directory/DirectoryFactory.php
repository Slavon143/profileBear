<?php

namespace WebpConverter\Conversion\Directory;

use WebpConverter\HookableInterface;
use WebpConverter\Plugin\Uninstall\WebpFiles;

/**
 * Initializes integration for all directories.
 */
class DirectoryFactory implements HookableInterface {

	/**
	 * Object of directories integration.
	 *
	 * @var DirectoryIntegration
	 */
	private $directories_integration;

	public function __construct() {
		$this->set_integration( new SourceDirectory( 'gallery' ) );
		$this->set_integration( new SourceDirectory( 'plugins' ) );
		$this->set_integration( new SourceDirectory( 'themes' ) );
		$this->set_integration( new SourceDirectory( 'uploads', true ) );
		foreach ( apply_filters( 'webpc_source_directories', [] ) as $directory_name ) {
			$this->set_integration( new SourceDirectory( $directory_name ) );
		}
		$this->set_integration( new UploadsWebpcDirectory() );
	}

	/**
	 * Sets integration for directory.
	 *
	 * @param DirectoryInterface $directory .
	 *
	 * @return void
	 */
	private function set_integration( DirectoryInterface $directory ) {
		if ( $this->directories_integration === null ) {
			$this->directories_integration = new DirectoryIntegration();
		}
		$this->directories_integration->add_directory( $directory );
	}

	/**
	 * {@inheritdoc}
	 */
	public function init_hooks() {
		$this->directories_integration->init_hooks();
		add_action( 'init', [ $this, 'init_hooks_after_setup' ] );
	}

	/**
	 * @return void
	 * @internal
	 */
	public function init_hooks_after_setup() {
		foreach ( apply_filters( 'webpc_source_directories', [] ) as $directory_name ) {
			$this->set_integration( new SourceDirectory( $directory_name ) );
		}
		$this->directories_integration->init_hooks();
	}

	/**
	 * Returns list of source directories.
	 *
	 * @return string[] Types of directories with labels.
	 */
	public function get_directories(): array {
		return $this->directories_integration->get_input_directories();
	}

	/**
	 * Removes converted files from output directory.
	 *
	 * @param string[] $source_dirs Types of source directories.
	 *
	 * @return void
	 */
	public function remove_unused_output_directories( array $source_dirs ) {
		$all_dirs = $this->directories_integration->get_output_directories();
		foreach ( $all_dirs as $output_dir => $output_path ) {
			if ( in_array( $output_dir, $source_dirs ) ) {
				continue;
			}
			WebpFiles::remove_webp_files( $output_path );
		}
	}
}
