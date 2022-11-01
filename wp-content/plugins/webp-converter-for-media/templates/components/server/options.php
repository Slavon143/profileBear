<?php
/**
 * Information about plugin options displayed in server configuration widget.
 *
 * @package Converter for Media
 */

use WebpConverter\Conversion\Cron\CronStatusManager;
use WebpConverter\Notice\CloudflareNotice;
use WebpConverter\Notice\LitespeedNotice;
use WebpConverter\Notice\ThanksNotice;
use WebpConverter\Notice\UpgradeNotice;
use WebpConverter\Notice\WelcomeNotice;
use WebpConverter\Service\OptionsAccessManager;
use WebpConverter\Service\StatsManager;

?>
<h4>Options</h4>
<table>
	<tbody>
	<tr>
		<td class="e"><?php echo esc_html( WelcomeNotice::NOTICE_OPTION ); ?></td>
		<td class="v">
			<?php echo esc_html( OptionsAccessManager::get_option( WelcomeNotice::NOTICE_OPTION, '-' ) ); ?>
		</td>
	</tr>
	<tr>
		<td class="e"><?php echo esc_html( ThanksNotice::NOTICE_OPTION ); ?></td>
		<td class="v">
			<?php echo esc_html( OptionsAccessManager::get_option( ThanksNotice::NOTICE_OPTION, '-' ) ); ?>
		</td>
	</tr>
	<tr>
		<td class="e"><?php echo esc_html( CloudflareNotice::NOTICE_OPTION ); ?></td>
		<td class="v">
			<?php echo esc_html( OptionsAccessManager::get_option( CloudflareNotice::NOTICE_OPTION, '-' ) ); ?>
		</td>
	</tr>
	<tr>
		<td class="e"><?php echo esc_html( LitespeedNotice::NOTICE_OPTION ); ?></td>
		<td class="v">
			<?php echo esc_html( OptionsAccessManager::get_option( LitespeedNotice::NOTICE_OPTION, '-' ) ); ?>
		</td>
	</tr>
	<tr>
		<td class="e"><?php echo esc_html( UpgradeNotice::NOTICE_OPTION ); ?></td>
		<td class="v">
			<?php echo esc_html( OptionsAccessManager::get_option( UpgradeNotice::NOTICE_OPTION, '-' ) ); ?>
		</td>
	</tr>
	<tr>
		<td class="e"><?php echo esc_html( CronStatusManager::CRON_PATHS_TRANSIENT ); ?></td>
		<td class="v">
			<?php echo count( get_site_transient( CronStatusManager::CRON_PATHS_TRANSIENT ) ?: [] ); ?>
		</td>
	</tr>
	<tr>
		<td class="e"><?php echo esc_html( CronStatusManager::CRON_PATHS_SKIPPED_TRANSIENT ); ?></td>
		<td class="v">
			<?php echo esc_html( get_site_transient( CronStatusManager::CRON_PATHS_SKIPPED_TRANSIENT ) ?: '-' ); ?>
		</td>
	</tr>
	<tr>
		<td class="e"><?php echo esc_html( CronStatusManager::CRON_STATUS_LOCKED_TRANSIENT ); ?></td>
		<td class="v">
			<?php echo esc_html( get_site_transient( CronStatusManager::CRON_STATUS_LOCKED_TRANSIENT ) ?: '-' ); ?>
		</td>
	</tr>
	<tr>
		<td class="e"><?php echo esc_html( CronStatusManager::CRON_REQUEST_ID_TRANSIENT ); ?></td>
		<td class="v">
			<?php echo esc_html( get_site_transient( CronStatusManager::CRON_REQUEST_ID_TRANSIENT ) ?: '-' ); ?>
		</td>
	</tr>
	<tr>
		<td class="e"><?php echo esc_html( StatsManager::STATS_REGENERATION_IMAGES_OPTION ); ?></td>
		<td class="v">
			<?php echo esc_html( OptionsAccessManager::get_option( StatsManager::STATS_REGENERATION_IMAGES_OPTION, '-' ) ); ?>
		</td>
	</tr>
	<tr>
		<td class="e"><?php echo esc_html( StatsManager::STATS_IMAGES_WEBP_ALL_OPTION ); ?></td>
		<td class="v">
			<?php echo esc_html( OptionsAccessManager::get_option( StatsManager::STATS_IMAGES_WEBP_ALL_OPTION, '-' ) ); ?>
		</td>
	</tr>
	<tr>
		<td class="e"><?php echo esc_html( StatsManager::STATS_IMAGES_WEBP_UNCONVERTED_OPTION ); ?></td>
		<td class="v">
			<?php echo esc_html( OptionsAccessManager::get_option( StatsManager::STATS_IMAGES_WEBP_UNCONVERTED_OPTION, '-' ) ); ?>
		</td>
	</tr>
	<tr>
		<td class="e"><?php echo esc_html( StatsManager::STATS_IMAGES_AVIF_ALL_OPTION ); ?></td>
		<td class="v">
			<?php echo esc_html( OptionsAccessManager::get_option( StatsManager::STATS_IMAGES_AVIF_ALL_OPTION, '-' ) ); ?>
		</td>
	</tr>
	<tr>
		<td class="e"><?php echo esc_html( StatsManager::STATS_IMAGES_AVIF_UNCONVERTED_OPTION ); ?></td>
		<td class="v">
			<?php echo esc_html( OptionsAccessManager::get_option( StatsManager::STATS_IMAGES_AVIF_UNCONVERTED_OPTION, '-' ) ); ?>
		</td>
	</tr>
	</tbody>
</table>
