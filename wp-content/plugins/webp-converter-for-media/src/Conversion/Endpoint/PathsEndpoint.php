<?php

namespace WebpConverter\Conversion\Endpoint;

use WebpConverter\Conversion\Cron\CronStatusManager;
use WebpConverter\Conversion\PathsFinder;
use WebpConverter\PluginData;
use WebpConverter\Repository\TokenRepository;

/**
 * Supports endpoint to get list of image paths to be converted.
 */
class PathsEndpoint extends EndpointAbstract {

	/**
	 * @var PluginData
	 */
	private $plugin_data;

	/**
	 * @var TokenRepository
	 */
	private $token_repository;

	/**
	 * @var CronStatusManager
	 */
	private $cron_status_manager;

	public function __construct(
		PluginData $plugin_data,
		TokenRepository $token_repository,
		CronStatusManager $cron_status_manager = null
	) {
		$this->plugin_data         = $plugin_data;
		$this->token_repository    = $token_repository;
		$this->cron_status_manager = $cron_status_manager ?: new CronStatusManager();
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_route_name(): string {
		return 'paths';
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
			]
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_route_response( \WP_REST_Request $request ) {
		$this->cron_status_manager->set_conversion_status_locked( true, true );
		$this->cron_status_manager->set_paths_to_conversion( [] );
		$this->cron_status_manager->set_paths_skipped( [] );

		$params         = $request->get_params();
		$skip_converted = ( $params['regenerate_force'] !== true );
		$paths          = ( new PathsFinder( $this->plugin_data, $this->token_repository ) )
			->get_paths_by_chunks( $skip_converted );

		if ( ! $paths ) {
			$this->cron_status_manager->set_conversion_status_locked( false );
		}

		return new \WP_REST_Response(
			$paths,
			200
		);
	}
}
