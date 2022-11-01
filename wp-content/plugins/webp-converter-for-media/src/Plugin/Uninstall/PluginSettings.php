<?php

namespace WebpConverter\Plugin\Uninstall;

use WebpConverter\Conversion\Cron\CronStatusManager;
use WebpConverter\Error\ErrorDetectorAggregator;
use WebpConverter\Notice\CloudflareNotice;
use WebpConverter\Notice\LitespeedNotice;
use WebpConverter\Notice\ThanksNotice;
use WebpConverter\Notice\UpgradeNotice;
use WebpConverter\Notice\WelcomeNotice;
use WebpConverter\Plugin\Update;
use WebpConverter\Repository\TokenRepository;
use WebpConverter\Service\OptionsAccessManager;
use WebpConverter\Service\StatsManager;
use WebpConverter\Settings\SettingsSave;

/**
 * Removes options saved by plugin.
 */
class PluginSettings {

	/**
	 * Removes options from wp_options table.
	 *
	 * @return void
	 */
	public static function remove_plugin_settings() {
		OptionsAccessManager::delete_option( WelcomeNotice::NOTICE_OPTION );
		OptionsAccessManager::delete_option( ThanksNotice::NOTICE_OLD_OPTION );
		OptionsAccessManager::delete_option( ThanksNotice::NOTICE_OPTION );
		OptionsAccessManager::delete_option( CloudflareNotice::NOTICE_OPTION );
		OptionsAccessManager::delete_option( LitespeedNotice::NOTICE_OPTION );
		OptionsAccessManager::delete_option( UpgradeNotice::NOTICE_OPTION );
		OptionsAccessManager::delete_option( 'webpc_notice_avif_support' );
		OptionsAccessManager::delete_option( 'webpc_notice_cloudways' );

		OptionsAccessManager::delete_option( ErrorDetectorAggregator::ERRORS_CACHE_OPTION );
		OptionsAccessManager::delete_option( SettingsSave::SETTINGS_OPTION );
		OptionsAccessManager::delete_option( Update::VERSION_OPTION );
		OptionsAccessManager::delete_option( TokenRepository::TOKEN_OPTION );

		OptionsAccessManager::delete_option( StatsManager::STATS_INSTALLATION_DATE_OPTION );
		OptionsAccessManager::delete_option( StatsManager::STATS_FIRST_VERSION_OPTION );
		OptionsAccessManager::delete_option( StatsManager::STATS_REGENERATION_IMAGES_OPTION );
		OptionsAccessManager::delete_option( StatsManager::STATS_IMAGES_WEBP_ALL_OPTION );
		OptionsAccessManager::delete_option( StatsManager::STATS_IMAGES_WEBP_UNCONVERTED_OPTION );
		OptionsAccessManager::delete_option( StatsManager::STATS_IMAGES_AVIF_ALL_OPTION );
		OptionsAccessManager::delete_option( StatsManager::STATS_IMAGES_AVIF_UNCONVERTED_OPTION );
		OptionsAccessManager::delete_option( 'webpc_stats_calculation_images' );

		delete_site_transient( CronStatusManager::CRON_PATHS_TRANSIENT );
		delete_site_transient( CronStatusManager::CRON_PATHS_SKIPPED_TRANSIENT );
		delete_site_transient( CronStatusManager::CRON_STATUS_LOCKED_TRANSIENT );
		delete_site_transient( CronStatusManager::CRON_REQUEST_ID_TRANSIENT );
	}
}
