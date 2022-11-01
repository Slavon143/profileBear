<?php

namespace WebpConverter\Plugin\Deactivation;

use WebpConverter\Conversion\Cron\CronEventGenerator;

/**
 * Removes cron event that starts converting all images.
 */
class CronReset {

	/**
	 * Resets cron event to regenerate all images.
	 *
	 * @return void
	 */
	public function reset_cron_event() {
		wp_clear_scheduled_hook( CronEventGenerator::CRON_PATHS_ACTION );
	}
}
