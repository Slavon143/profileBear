<?php

namespace WebpConverter\Plugin\Activation;

use WebpConverter\Notice\CloudflareNotice;
use WebpConverter\Notice\LitespeedNotice;
use WebpConverter\Notice\NoticeIntegration;
use WebpConverter\Notice\ThanksNotice;
use WebpConverter\Notice\UpgradeNotice;
use WebpConverter\Notice\WelcomeNotice;
use WebpConverter\PluginInfo;
use WebpConverter\Service\StatsManager;

/**
 * Adds default options for plugin settings.
 */
class DefaultSettings {

	/**
	 * @var PluginInfo
	 */
	private $plugin_info;

	/**
	 * @var StatsManager
	 */
	private $stats_manager;

	public function __construct( PluginInfo $plugin_info, StatsManager $stats_manager = null ) {
		$this->plugin_info   = $plugin_info;
		$this->stats_manager = $stats_manager ?: new StatsManager();
	}

	/**
	 * @return void
	 */
	public function add_default_notices_values() {
		NoticeIntegration::set_default_value( WelcomeNotice::NOTICE_OPTION, WelcomeNotice::get_default_value() );
		NoticeIntegration::set_default_value( ThanksNotice::NOTICE_OPTION, ThanksNotice::get_default_value() );
		NoticeIntegration::set_default_value( CloudflareNotice::NOTICE_OPTION, CloudflareNotice::get_default_value() );
		NoticeIntegration::set_default_value( LitespeedNotice::NOTICE_OPTION, LitespeedNotice::get_default_value() );
		NoticeIntegration::set_default_value( UpgradeNotice::NOTICE_OPTION, UpgradeNotice::get_default_value() );
	}

	/**
	 * @return void
	 */
	public function add_default_stats_values() {
		$this->stats_manager->set_plugin_installation_date();
		$this->stats_manager->set_plugin_first_version( $this->plugin_info->get_plugin_version() );
	}
}
