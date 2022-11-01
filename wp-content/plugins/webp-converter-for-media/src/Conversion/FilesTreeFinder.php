<?php

namespace WebpConverter\Conversion;

use WebpConverter\Conversion\Format\AvifFormat;
use WebpConverter\Conversion\Format\WebpFormat;
use WebpConverter\Error\Detector\RewritesErrorsDetector;
use WebpConverter\PluginData;
use WebpConverter\Service\ServerConfigurator;
use WebpConverter\Service\StatsManager;
use WebpConverter\Settings\Option\SupportedDirectoriesOption;
use WebpConverter\Settings\Option\SupportedExtensionsOption;

/**
 * Returns tree of paths to files to conversion.
 */
class FilesTreeFinder {

	/**
	 * @var PluginData
	 */
	private $plugin_data;

	/**
	 * @var ServerConfigurator
	 */
	private $server_configurator;

	/**
	 * @var StatsManager
	 */
	private $stats_manager;

	/**
	 * @var OutputPath
	 */
	private $output_path;

	/**
	 * @var int[]
	 */
	private $files_converted;

	/**
	 * @var int[]
	 */
	private $files_unconverted;

	public function __construct(
		PluginData $plugin_data,
		ServerConfigurator $server_configurator = null,
		StatsManager $stats_manager = null,
		OutputPath $output_path = null
	) {
		$this->plugin_data         = $plugin_data;
		$this->server_configurator = $server_configurator ?: new ServerConfigurator();
		$this->stats_manager       = $stats_manager ?: new StatsManager();
		$this->output_path         = $output_path ?: new OutputPath();
	}

	/**
	 * Returns list of source images for directory.
	 *
	 * @param string[] $output_formats    Allowed extensions.
	 *
	 * @return mixed[] {
	 * @type int[]     $files_converted   .
	 * @type int[]     $files_unconverted .
	 * @type mixed[]   $files_tree        .
	 * }
	 * @internal
	 */
	public function get_tree( array $output_formats ): array {
		$this->server_configurator->set_memory_limit();
		$this->server_configurator->set_execution_time( 900 );

		foreach ( $output_formats as $output_format ) {
			$this->files_converted[ $output_format ]   = 0;
			$this->files_unconverted[ $output_format ] = 0;
		}

		$settings = $this->plugin_data->get_plugin_settings();
		$values   = [];
		foreach ( $settings[ SupportedDirectoriesOption::OPTION_NAME ] as $dir_name ) {
			$source_dir = apply_filters( 'webpc_dir_path', '', $dir_name );
			$values[]   = $this->find_tree_in_directory(
				$source_dir,
				$settings[ SupportedExtensionsOption::OPTION_NAME ],
				$output_formats
			);
		}

		$this->stats_manager->set_images_webp_all( $this->files_converted[ WebpFormat::FORMAT_EXTENSION ] + $this->files_unconverted[ WebpFormat::FORMAT_EXTENSION ] );
		$this->stats_manager->set_images_webp_unconverted( $this->files_unconverted[ WebpFormat::FORMAT_EXTENSION ] );
		$this->stats_manager->set_images_avif_all( $this->files_converted[ AvifFormat::FORMAT_EXTENSION ] + $this->files_unconverted[ AvifFormat::FORMAT_EXTENSION ] );
		$this->stats_manager->set_images_avif_unconverted( $this->files_unconverted[ AvifFormat::FORMAT_EXTENSION ] );

		return [
			'files_converted'   => $this->files_converted,
			'files_unconverted' => $this->files_unconverted,
			'files_tree'        => $values,
		];
	}

	/**
	 * Returns list of source images as tree.
	 *
	 * @param string   $dir_path       Server path of source directory.
	 * @param string[] $source_formats Allowed extensions.
	 * @param string[] $output_formats Allowed extensions.
	 * @param int      $nesting_level  .
	 *
	 * @return mixed[]
	 */
	private function find_tree_in_directory( string $dir_path, array $source_formats, array $output_formats, int $nesting_level = 0 ): array {
		$paths = scandir( $dir_path );
		$list  = [
			'name'  => basename( $dir_path ),
			'items' => [],
			'files' => [],
			'count' => 0,
		];
		if ( ! is_array( $paths ) ) {
			return $list;
		}

		if ( $nesting_level === 0 ) {
			$paths = array_diff( $paths, [ basename( RewritesErrorsDetector::PATH_OUTPUT_FILE_PNG ) ] );
		}

		sort( $paths, SORT_NATURAL | SORT_FLAG_CASE );
		foreach ( $paths as $path ) {
			$current_path = $dir_path . '/' . $path;

			if ( is_dir( $current_path ) ) {
				if ( apply_filters( 'webpc_supported_source_directory', true, basename( $current_path ), $current_path ) ) {
					$children = $this->find_tree_in_directory( $current_path, $source_formats, $output_formats, ( $nesting_level + 1 ) );
					if ( $children['items'] || $children['files'] ) {
						$list['items'][] = $children;
					}
				}
			} elseif ( in_array( strtolower( pathinfo( $current_path, PATHINFO_EXTENSION ) ), $source_formats ) ) {
				if ( apply_filters( 'webpc_supported_source_file', true, basename( $current_path ), $current_path )
					&& ! $this->is_converted_file( $current_path, $output_formats ) ) {
					$list['files'][] = $path;
				}
			}
		}

		$list['count'] = $this->calculate_tree_count( $list );
		return $list;
	}

	/**
	 * @param string   $source_path    .
	 * @param string[] $output_formats .
	 *
	 * @return bool
	 */
	private function is_converted_file( string $source_path, array $output_formats ): bool {
		$is_not_converted = false;

		foreach ( $output_formats as $output_format ) {
			$output_path = $this->output_path->get_path( $source_path, false, $output_format );
			if ( $output_path === null ) {
				continue;
			}

			if ( file_exists( $output_path )
				|| file_exists( $output_path . '.' . SkipLarger::DELETED_FILE_EXTENSION )
				|| file_exists( $output_path . '.' . SkipCrashed::CRASHED_FILE_EXTENSION ) ) {
				$this->files_converted[ $output_format ]++;
			} else {
				$this->files_unconverted[ $output_format ]++;
				$is_not_converted = true;
			}
		}

		return ! $is_not_converted;
	}

	/**
	 * @param mixed[] $tree .
	 *
	 * @return int
	 */
	private function calculate_tree_count( array $tree ): int {
		$count = count( $tree['files'] );

		foreach ( $tree['items'] as $tree_item ) {
			$count += $this->calculate_tree_count( $tree_item );
		}

		return $count;
	}
}
