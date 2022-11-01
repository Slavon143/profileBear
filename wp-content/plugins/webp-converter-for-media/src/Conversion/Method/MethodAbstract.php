<?php

namespace WebpConverter\Conversion\Method;

use WebpConverter\Conversion\Format\AvifFormat;
use WebpConverter\Conversion\Format\WebpFormat;
use WebpConverter\Conversion\OutputPath;
use WebpConverter\Exception;
use WebpConverter\Settings\Option\ExtraFeaturesOption;

/**
 * Abstract class for class that converts images.
 */
abstract class MethodAbstract implements MethodInterface {

	/**
	 * @var OutputPath
	 */
	private $output_path;

	public function __construct( OutputPath $output_path = null ) {
		$this->output_path = $output_path ?: new OutputPath();
	}

	/**
	 * @var bool
	 */
	protected $is_fatal_error = false;

	/**
	 * Messages of errors that occurred during conversion.
	 *
	 * @var string[]
	 */
	protected $errors = [];

	/**
	 * Sum of size of source images before conversion.
	 *
	 * @var int
	 */
	protected $size_before = 0;

	/**
	 * Sum of size of output images after conversion.
	 *
	 * @var int
	 */
	protected $size_after = 0;

	/**
	 * @var int[]
	 */
	protected $files_available = [
		WebpFormat::FORMAT_EXTENSION => 0,
		AvifFormat::FORMAT_EXTENSION => 0,
	];

	/**
	 * @var int[]
	 */
	protected $files_converted = [
		WebpFormat::FORMAT_EXTENSION => 0,
		AvifFormat::FORMAT_EXTENSION => 0,
	];

	/**
	 * @return bool
	 */
	public static function is_pro_feature(): bool {
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_fatal_error(): bool {
		return $this->is_fatal_error;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_errors(): array {
		return $this->errors;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_size_before(): int {
		return $this->size_before;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_files_available( string $output_format ): int {
		return $this->files_available[ $output_format ];
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_files_converted( string $output_format ): int {
		return $this->files_converted[ $output_format ];
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_size_after(): int {
		return $this->size_after;
	}

	/**
	 * Checks server path of source image.
	 *
	 * @param string $source_path Server path of source image.
	 *
	 * @return string Server path of source image.
	 *
	 * @throws Exception\SourcePathException
	 */
	protected function get_image_source_path( string $source_path ): string {
		if ( ! is_readable( $source_path ) ) {
			throw new Exception\SourcePathException( $source_path );
		}

		return $source_path;
	}

	/**
	 * Returns server path for output image.
	 *
	 * @param string $source_path Server path of source image.
	 * @param string $format      Extension of output format.
	 *
	 * @return string Server path of output image.
	 *
	 * @throws Exception\OutputPathException
	 */
	protected function get_image_output_path( string $source_path, string $format ): string {
		if ( ! $output_path = $this->output_path->get_path( $source_path, true, $format ) ) {
			throw new Exception\OutputPathException( $source_path );
		}

		return $output_path;
	}

	/**
	 * @param string $source_path   Server path of source image.
	 * @param string $output_path   Server path of output image.
	 * @param string $output_format .
	 *
	 * @return void
	 */
	protected function update_conversion_stats( string $source_path, string $output_path, string $output_format ) {
		$output_exist = file_exists( $output_path );
		$size_before  = filesize( $source_path );
		$size_after   = ( $output_exist ) ? filesize( $output_path ) : $size_before;

		$this->size_before += $size_before ?: 0;
		$this->size_after  += $size_after ?: 0;
	}

	/**
	 * @param string  $error_message   .
	 * @param mixed[] $plugin_settings .
	 * @param bool    $is_fatal_error  .
	 *
	 * @return void
	 */
	protected function save_conversion_error( string $error_message, array $plugin_settings, bool $is_fatal_error = false ) {
		if ( $is_fatal_error ) {
			$this->is_fatal_error = true;
		}

		$this->errors[] = $error_message;
		$this->log_conversion_error( $error_message, $plugin_settings );
	}

	/**
	 * @param string  $error_message   .
	 * @param mixed[] $plugin_settings .
	 *
	 * @return void
	 */
	private function log_conversion_error( string $error_message, array $plugin_settings ) {
		$features = $plugin_settings[ ExtraFeaturesOption::OPTION_NAME ];
		if ( in_array( ExtraFeaturesOption::OPTION_VALUE_DEBUG_ENABLED, $features ) ) {
			error_log( sprintf( 'Converter for Media: %s', $error_message ) ); // phpcs:ignore
		}
	}
}
