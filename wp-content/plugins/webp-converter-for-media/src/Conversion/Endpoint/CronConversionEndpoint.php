<?php

namespace WebpConverter\Conversion\Endpoint;

use WebpConverter\Conversion\Cron\CronInitiator;
use WebpConverter\Conversion\Cron\CronStatusManager;
use WebpConverter\PluginData;
use WebpConverter\Repository\TokenRepository;

/**
 * .
 */
class CronConversionEndpoint extends EndpointAbstract {

	/**
	 * @var CronInitiator
	 */
	private $cron_initiator;

	/**
	 * @var CronStatusManager
	 */
	private $cron_status_manager;

	public function __construct(
		PluginData $plugin_data,
		TokenRepository $token_repository,
		CronInitiator $cron_initiator = null,
		CronStatusManager $cron_status_manager = null
	) {
		$this->cron_initiator      = $cron_initiator ?: new CronInitiator( $plugin_data, $token_repository );
		$this->cron_status_manager = $cron_status_manager ?: new CronStatusManager();
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_route_name(): string {
		return 'cron-conversion';
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
	public function is_valid_request( \WP_REST_Request $request ): bool {
		$nonce_value = $this->cron_status_manager->get_conversion_request_id();
		if ( $nonce_value === null ) {
			return false;
		}

		return ( $request->get_param( EndpointIntegration::ROUTE_NONCE_PARAM ) === $nonce_value );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_route_url(): string {
		$nonce_value = $this->cron_status_manager->refresh_conversion_request_id();

		return get_rest_url(
			null,
			sprintf(
				'%1$s/%2$s-%3$s',
				EndpointIntegration::ROUTE_NAMESPACE,
				$this->get_route_name(),
				$nonce_value
			)
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_route_response( \WP_REST_Request $request ) {
		$request_id = $request->get_param( EndpointIntegration::ROUTE_NONCE_PARAM );
		$this->cron_initiator->init_conversion( $request_id );

		return new \WP_REST_Response( null, 200 );
	}
}
