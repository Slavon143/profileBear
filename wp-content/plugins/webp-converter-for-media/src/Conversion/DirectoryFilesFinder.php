<?php

namespace WebpConverter\Conversion;

use WebpConverter\Error\Detector\RewritesErrorsDetector;
use WebpConverter\PluginData;
use WebpConverter\Service\ServerConfigurator;
use WebpConverter\Settings\Option\SupportedExtensionsOption;

/**
 * Returns paths to files in given directory.
 */
class DirectoryFilesFinder {

	/**
	 * @var PluginData
	 */
	private $plugin_data;

	/**
	 * @var ServerConfigurator
	 */
	private $server_configurator;

	public function __construct(
		PluginData $plugin_data,
		ServerConfigurator $server_configurator = null
	) {
		$this->plugin_data         = $plugin_data;
		$this->server_configurator = $server_configurator ?: new ServerConfigurator();
	}

	/**
	 * Returns list of source images for directory.
	 *
	 * @param string $dir_path Server path of source directory.
	 *
	 * @return string[] Server paths of source images.
	 * @internal
	 */
	public function get_files_by_directory( string $dir_path ): array {
		if ( ! file_exists( $dir_path ) ) {
			return [];
		}

		$this->server_configurator->set_memory_limit();
		$this->server_configurator->set_execution_time( 900 );

		$settings = $this->plugin_data->get_plugin_settings();
		return $this->find_files_in_directory(
			$dir_path,
			$settings[ SupportedExtensionsOption::OPTION_NAME ]
		);
	}

	/**
	 * Returns list of source images for directory.
	 *
	 * @param string   $dir_path            Server path of source directory.
	 * @param string[] $allowed_source_exts File extensions to find.
	 * @param string   $path_prefix         File path related to directory path.
	 *
	 * @return string[] Server paths of source images.
	 */
	private function find_files_in_directory( string $dir_path, array $allowed_source_exts, string $path_prefix = '' ): array {
		$paths = scandir( $dir_path );
		$list  = [];
		if ( ! is_array( $paths ) ) {
			return $list;
		}

		if ( $path_prefix === '' ) {
			$paths = array_diff( $paths, [ basename( RewritesErrorsDetector::PATH_OUTPUT_FILE_PNG ) ] );
		}

		rsort( $paths );
		foreach ( $paths as $path ) {
			$current_path = $dir_path . '/' . $path;

			if ( is_dir( $current_path ) ) {
				if ( apply_filters( 'webpc_supported_source_directory', true, basename( $current_path ), $current_path ) ) {
					$list = array_merge(
						$list,
						$this->find_files_in_directory( $current_path, $allowed_source_exts, trim( $path_prefix . '/' . $path, '/' ) )
					);
				}
			} elseif ( in_array( strtolower( pathinfo( $current_path, PATHINFO_EXTENSION ) ), $allowed_source_exts ) ) {
				if ( apply_filters( 'webpc_supported_source_file', true, basename( $current_path ), $current_path ) ) {
					$list[] = trim( $path_prefix . '/' . $path, '/' );
				}
			}
		}
		return $list;
	}
}
