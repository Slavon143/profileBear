<?php

namespace WebpConverter\Conversion\Cron;

use WebpConverter\HookableInterface;
use WebpConverter\PluginData;
use WebpConverter\Repository\TokenRepository;
use WebpConverter\Settings\Option\ExtraFeaturesOption;

/**
 * Adds cron event that converts images.
 */
class CronEventGenerator implements HookableInterface {

	const CRON_PATHS_ACTION = 'webpc_cron_paths';

	/**
	 * @var PluginData
	 */
	private $plugin_data;

	/**
	 * @var CronInitiator
	 */
	private $cron_initiator;

	public function __construct(
		PluginData $plugin_data,
		TokenRepository $token_repository,
		CronInitiator $cron_initiator = null
	) {
		$this->plugin_data    = $plugin_data;
		$this->cron_initiator = $cron_initiator ?: new CronInitiator( $plugin_data, $token_repository );
	}

	/**
	 * {@inheritdoc}
	 */
	public function init_hooks() {
		add_action( 'init', [ $this, 'add_cron_event' ] );
		add_action( self::CRON_PATHS_ACTION, [ $this, 'get_paths_to_conversion' ] );
	}

	/**
	 * Initializes cron event to convert all images.
	 *
	 * @return void
	 * @internal
	 */
	public function add_cron_event() {
		if ( wp_next_scheduled( self::CRON_PATHS_ACTION )
			|| ! ( $settings = $this->plugin_data->get_plugin_settings() )
			|| ! in_array( ExtraFeaturesOption::OPTION_VALUE_CRON_ENABLED, $settings[ ExtraFeaturesOption::OPTION_NAME ] ) ) {
			return;
		}

		wp_schedule_event( time(), CronSchedulesGenerator::CRON_PATHS_SCHEDULE, self::CRON_PATHS_ACTION );
	}

	/**
	 * @return void
	 * @internal
	 */
	public function get_paths_to_conversion() {
		$this->cron_initiator->refresh_paths_to_conversion();
		$this->cron_initiator->init_conversion();
	}
}
