<?php

namespace WebpConverter\Conversion\Cron;

use WebpConverter\HookableInterface;

/**
 * Adds time intervals to cron event.
 */
class CronSchedulesGenerator implements HookableInterface {

	const CRON_PATHS_SCHEDULE = 'webpc_cron_paths';

	/**
	 * {@inheritdoc}
	 */
	public function init_hooks() {
		add_filter( 'cron_schedules', [ $this, 'add_cron_interval' ] );
	}

	/**
	 * Adds new cron schedules.
	 *
	 * @param mixed[] $schedules Cron schedules.
	 *
	 * @return mixed[] Cron schedules.
	 * @internal
	 */
	public function add_cron_interval( array $schedules ): array {
		$schedules[ self::CRON_PATHS_SCHEDULE ] = [
			'interval' => apply_filters( 'webpc_cron_paths_interval', 3600 ),
			'display'  => 'Converter for Media',
		];
		return $schedules;
	}
}
