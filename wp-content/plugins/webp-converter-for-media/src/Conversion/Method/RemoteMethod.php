<?php

namespace WebpConverter\Conversion\Method;

use WebpConverter\Conversion\SkipCrashed;
use WebpConverter\Conversion\SkipLarger;
use WebpConverter\Exception\ExceptionInterface;
use WebpConverter\Exception\FilesizeOversizeException;
use WebpConverter\Exception\LargerThanOriginalException;
use WebpConverter\Exception\OutputPathException;
use WebpConverter\Exception\RemoteErrorResponseException;
use WebpConverter\Exception\RemoteRequestException;
use WebpConverter\Exception\SourcePathException;
use WebpConverter\Model\Token;
use WebpConverter\Repository\TokenRepository;
use WebpConverter\Service\ServerConfigurator;
use WebpConverter\Settings\Option\AccessTokenOption;
use WebpConverter\Settings\Option\ExtraFeaturesOption;
use WebpConverter\Settings\Option\ImageResizeOption;
use WebpConverter\Settings\Option\ImagesQualityOption;
use WebpConverter\Settings\Option\OutputFormatsOption;
use WebpConverter\WebpConverterConstants;

/**
 * Supports image conversion method using remote API.
 */
class RemoteMethod extends MethodAbstract {

	const METHOD_NAME        = 'remote';
	const MAX_FILESIZE_BYTES = ( 25 * 1024 * 1024 );

	/**
	 * @var SkipCrashed
	 */
	private $skip_crashed;

	/**
	 * @var SkipLarger
	 */
	private $skip_larger;

	/**
	 * @var TokenRepository
	 */
	private $token_repository;

	/**
	 * @var Token
	 */
	private $token;

	/**
	 * @var ServerConfigurator
	 */
	private $server_configurator;

	public function __construct(
		SkipCrashed $skip_crashed,
		SkipLarger $skip_larger,
		TokenRepository $token_repository,
		ServerConfigurator $server_configurator
	) {
		parent::__construct();
		$this->skip_crashed        = $skip_crashed;
		$this->skip_larger         = $skip_larger;
		$this->token_repository    = $token_repository;
		$this->server_configurator = $server_configurator;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_name(): string {
		return self::METHOD_NAME;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label(): string {
		if ( $this->token_repository->get_token()->get_valid_status() ) {
			return __( 'Remote server', 'webp-converter-for-media' );
		}

		return sprintf(
			'%1$s (%2$s)',
			__( 'Remote server', 'webp-converter-for-media' ),
			sprintf(
			/* translators: %%1$s: open anchor tag, %2$s: close anchor tag */
				__( 'available in %1$sthe PRO version%2$s', 'webp-converter-for-media' ),
				'<a href="https://url.mattplugins.com/converter-field-conversion-method-remote-upgrade" target="_blank">',
				'</a>'
			)
		);
	}

	/**
	 * @return bool
	 */
	public static function is_pro_feature(): bool {
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function is_method_installed(): bool {
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function is_method_active( string $format ): bool {
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function convert_paths( array $paths, array $plugin_settings, bool $regenerate_force ) {
		$this->server_configurator->set_memory_limit();
		$this->server_configurator->set_execution_time();

		$output_formats = $plugin_settings[ OutputFormatsOption::OPTION_NAME ];
		$source_paths   = [];
		$output_paths   = [];
		$this->token    = $this->token_repository->get_token();

		foreach ( $output_formats as $output_format ) {
			try {
				$file_paths = $this->get_source_paths( $paths, $plugin_settings, $output_format );
				if ( ! $file_paths ) {
					continue;
				}

				$this->files_available[ $output_format ] += count( $file_paths );

				$output_paths[ $output_format ] = $this->get_output_paths( $file_paths, $output_format );
				$source_paths[ $output_format ] = $file_paths;
			} catch ( ExceptionInterface $e ) {
				$this->save_conversion_error( $e->getMessage(), $plugin_settings );
			}
		}

		if ( ! $regenerate_force ) {
			foreach ( $source_paths as $output_format => $extensions_paths ) {
				foreach ( $extensions_paths as $path_index => $extensions_path ) {
					if ( file_exists( $output_paths[ $output_format ][ $path_index ] )
						|| file_exists( $output_paths[ $output_format ][ $path_index ] . '.' . SkipLarger::DELETED_FILE_EXTENSION ) ) {
						unset( $source_paths[ $output_format ][ $path_index ] );
						unset( $output_paths[ $output_format ][ $path_index ] );

						$this->files_available[ $output_format ]--;
					}
				}
			}
		}

		try {
			$converted_files = $this->init_connections( $source_paths, $plugin_settings, $output_paths );

			foreach ( $converted_files as $output_format => $format_converted_files ) {
				foreach ( $format_converted_files as $path_index => $converted_file ) {
					$source_path = $source_paths[ $output_format ][ $path_index ];
					$output_path = $output_paths[ $output_format ][ $path_index ];

					file_put_contents( $output_path, $converted_file );
					do_action( 'webpc_after_conversion', $output_path, $source_path );

					try {
						$this->skip_larger->remove_image_if_is_larger( $output_path, $source_path, $plugin_settings );
						$this->update_conversion_stats( $source_path, $output_path, $output_format );
					} catch ( LargerThanOriginalException $e ) {
						$this->files_converted[ $output_format ]--;
					}
				}
			}
		} catch ( RemoteErrorResponseException $e ) {
			$this->save_conversion_error( $e->getMessage(), $plugin_settings, true );
		}

		$this->token_repository->update_token( $this->token );
	}

	/**
	 * @param string[] $paths           .
	 * @param mixed[]  $plugin_settings .
	 * @param string   $output_format   .
	 *
	 * @return string[]
	 *
	 * @throws SourcePathException
	 * @throws OutputPathException
	 */
	private function get_source_paths( array $paths, array $plugin_settings, string $output_format ): array {
		$source_paths = [];
		foreach ( $paths as $path ) {
			$source_path = $this->get_image_source_path( $path );
			if ( filesize( $source_path ) > self::MAX_FILESIZE_BYTES ) {
				$this->save_conversion_error(
					( new FilesizeOversizeException( [ self::MAX_FILESIZE_BYTES, $source_path ] ) )->getMessage(),
					$plugin_settings
				);
				$this->skip_crashed->create_crashed_file( $this->get_image_output_path( $source_path, $output_format ) );
				continue;
			}

			$path_extension = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
			if ( $path_extension === $output_format ) {
				continue;
			}

			$source_paths[] = $this->get_image_source_path( $path );
		}

		return $source_paths;
	}

	/**
	 * @param string[] $source_paths  .
	 * @param string   $output_format .
	 *
	 * @return string[]
	 *
	 * @throws OutputPathException
	 */
	private function get_output_paths( array $source_paths, string $output_format ): array {
		$output_path = [];
		foreach ( $source_paths as $path ) {
			$output_path[] = $this->get_image_output_path( $path, $output_format );
		}

		return $output_path;
	}

	/**
	 * @param mixed[] $source_paths    .
	 * @param mixed[] $plugin_settings .
	 * @param mixed[] $output_paths    .
	 *
	 * @return mixed[]
	 *
	 * @throws RemoteErrorResponseException
	 */
	private function init_connections( array $source_paths, array $plugin_settings, array $output_paths ): array {
		$mh_items = [];
		$values   = [];

		$mh = curl_multi_init();
		if ( ! $mh ) {
			return $values;
		}

		foreach ( $source_paths as $output_format => $format_source_paths ) {
			foreach ( $format_source_paths as $resource_id => $source_path ) {
				$connect = $this->get_curl_connection( $source_path, $output_format, $plugin_settings );
				if ( ! $connect ) {
					continue;
				}

				curl_multi_add_handle( $mh, $connect );
				$mh_items[ $output_format ]                 = $mh_items[ $output_format ] ?? [];
				$mh_items[ $output_format ][ $resource_id ] = $connect;
			}
		}

		$running = null;
		do {
			curl_multi_exec( $mh, $running );
			curl_multi_select( $mh );
		} while ( $running > 0 );

		foreach ( $mh_items as $output_format => $format_mh_items ) {
			foreach ( $format_mh_items as $resource_id => $mh_item ) {
				$http_code = curl_getinfo( $mh_item, CURLINFO_HTTP_CODE );
				$response  = curl_multi_getcontent( $mh_item );

				if ( ( $http_code === 200 ) && $response ) {
					$values[ $output_format ]                 = $values[ $output_format ] ?? [];
					$values[ $output_format ][ $resource_id ] = $response;

					$this->files_converted[ $output_format ]++;
				} else {
					$this->handle_request_error(
						$source_paths[ $output_format ][ $resource_id ],
						$output_paths[ $output_format ][ $resource_id ],
						$output_format,
						$plugin_settings,
						$http_code,
						$response
					);
				}
				curl_multi_remove_handle( $mh, $mh_item );
			}
		}

		curl_multi_close( $mh );
		return $values;
	}

	/**
	 * @param string  $source_path     .
	 * @param string  $output_format   .
	 * @param mixed[] $plugin_settings .
	 *
	 * @return resource|null
	 */
	private function get_curl_connection( string $source_path, string $output_format, array $plugin_settings ) {
		$connect = curl_init( WebpConverterConstants::API_CONVERSION_URL );
		if ( ! $connect ) {
			return null;
		}

		curl_setopt( $connect, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt( $connect, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $connect, CURLOPT_TIMEOUT, apply_filters( 'webpc_remote_timeout', 30 ) );
		curl_setopt( $connect, CURLOPT_POST, 1 );
		curl_setopt(
			$connect,
			CURLOPT_POSTFIELDS,
			[
				'access_token'   => $plugin_settings[ AccessTokenOption::OPTION_NAME ],
				'domain_host'    => parse_url( get_site_url(), PHP_URL_HOST ),
				'source_file'    => curl_file_create( $source_path ),
				'output_format'  => $output_format,
				'quality_level'  => $plugin_settings[ ImagesQualityOption::OPTION_NAME ],
				'strip_metadata' => ( ! in_array( ExtraFeaturesOption::OPTION_VALUE_KEEP_METADATA, $plugin_settings[ ExtraFeaturesOption::OPTION_NAME ] ) ),
				'max_width'      => ( $plugin_settings[ ImageResizeOption::OPTION_NAME ][0] === 'yes' )
					? $plugin_settings[ ImageResizeOption::OPTION_NAME ][1]
					: 0,
				'max_height'     => ( $plugin_settings[ ImageResizeOption::OPTION_NAME ][0] === 'yes' )
					? $plugin_settings[ ImageResizeOption::OPTION_NAME ][2]
					: 0,
			]
		);
		curl_setopt(
			$connect,
			CURLOPT_HTTPHEADER,
			[
				'Content-Type: multipart/form-data',
				'Expect:',
			]
		);
		curl_setopt( $connect, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $connect, CURLOPT_HEADERFUNCTION, [ $this, 'handle_request_header' ] );

		return $connect;
	}

	/**
	 * @param resource $curl   .
	 * @param string   $header .
	 *
	 * @return int
	 */
	public function handle_request_header( $curl, string $header ): int {
		$header_length = strlen( $header );
		$header_data   = explode( ':', $header );
		if ( count( $header_data ) > 2 ) {
			return $header_length;
		}

		$header_key = strtolower( trim( $header_data[0] ) );
		if ( $header_key === WebpConverterConstants::API_RESPONSE_VALUE_LIMIT_USAGE ) {
			$this->token->set_images_usage( intval( $header_data[1] ) );
		} elseif ( $header_key === WebpConverterConstants::API_RESPONSE_VALUE_LIMIT_MAX ) {
			$this->token->set_images_limit( intval( $header_data[1] ) );
		} elseif ( $header_key === WebpConverterConstants::API_RESPONSE_VALUE_SUBSCRIPTION_ACTIVE ) {
			$this->token->set_valid_status( ( trim( $header_data[1] ) === '1' ) );
		}

		return $header_length;
	}

	/**
	 * @param string      $source_path     .
	 * @param string      $output_path     .
	 * @param string      $output_format   .
	 * @param mixed[]     $plugin_settings .
	 * @param int         $http_code       .
	 * @param string|null $response        .
	 *
	 * @return void
	 *
	 * @throws RemoteErrorResponseException
	 */
	private function handle_request_error(
		string $source_path,
		string $output_path,
		string $output_format,
		array $plugin_settings,
		int $http_code,
		string $response = null
	) {
		$response_value     = ( $response ) ? json_decode( $response, true ) : [];
		$error_message      = $response_value[ WebpConverterConstants::API_RESPONSE_VALUE_ERROR_MESSAGE ] ?? '';
		$error_fatal_status = $response_value[ WebpConverterConstants::API_RESPONSE_VALUE_ERROR_FATAL_STATUS ] ?? false;

		if ( $error_message && $error_fatal_status ) {
			throw new RemoteErrorResponseException( $error_message );
		} elseif ( $error_message ) {
			$this->save_conversion_error( $error_message, $plugin_settings );
		} elseif ( $http_code === 200 ) {
			$this->skip_crashed->create_crashed_file( $output_path );
			$this->files_converted[ $output_format ]++;
		} else {
			$this->save_conversion_error(
				( new RemoteRequestException( [ $http_code, $source_path ] ) )->getMessage(),
				$plugin_settings
			);
		}
	}
}
