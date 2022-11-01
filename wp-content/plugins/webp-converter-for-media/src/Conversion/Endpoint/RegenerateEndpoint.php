<?php

namespace WebpConverter\Conversion\Endpoint;

use WebpConverter\Conversion\Cron\CronStatusManager;
use WebpConverter\Conversion\Method\MethodIntegrator;
use WebpConverter\PluginData;

/**
 * Supports endpoint for converting list of paths to images.
 */
class RegenerateEndpoint extends EndpointAbstract {

	/**
	 * @var PluginData
	 */
	private $plugin_data;

	/**
	 * @var CronStatusManager
	 */
	private $cron_status_manager;

	public function __construct( PluginData $plugin_data, CronStatusManager $cron_status_manager = null ) {
		$this->plugin_data         = $plugin_data;
		$this->cron_status_manager = $cron_status_manager ?: new CronStatusManager();
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_route_name(): string {
		return 'regenerate';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_http_methods(): string {
		return \WP_REST_Server::CREATABLE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_url_lifetime(): int {
		return ( 7 * 24 * 60 * 60 );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_route_args(): array {
		return array_merge(
			parent::get_route_args(),
			[
				'regenerate_force' => [
					'description'       => 'Option to force all images to be converted again (set `1` to enable)',
					'required'          => false,
					'default'           => false,
					'sanitize_callback' => function ( $value ) {
						return ( (string) $value === '1' );
					},
				],
				'paths'            => [
					'description'       => 'Array of file paths (server paths)',
					'required'          => true,
					'default'           => [],
					'validate_callback' => function ( $value ) {
						return ( is_array( $value ) && $value );
					},
				],
			]
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_route_response( \WP_REST_Request $request ) {
		$this->cron_status_manager->set_conversion_status_locked();

		$params = $request->get_params();
		$data   = $this->convert_images( $params['paths'], $params['regenerate_force'] );

		if ( $data !== false ) {
			return new \WP_REST_Response(
				$data,
				200
			);
		} else {
			return new \WP_Error(
				'webpc_rest_api_error',
				'',
				[
					'status' => 405,
				]
			);
		}
	}

	/**
	 * Initializes image conversion to output formats.
	 *
	 * @param string[] $paths            Server paths of source images.
	 * @param bool     $regenerate_force .
	 *
	 * @return mixed[]|false Status of conversion.
	 */
	public function convert_images( array $paths, bool $regenerate_force ) {
		$response = ( new MethodIntegrator( $this->plugin_data ) )->init_conversion( $paths, $regenerate_force );
		if ( $response === null ) {
			return false;
		}
		return $response;
	}
}
