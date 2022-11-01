<?php

namespace WebpConverter;

use WebpConverter\Action;
use WebpConverter\Conversion;
use WebpConverter\Conversion\Cron;
use WebpConverter\Conversion\Endpoint;
use WebpConverter\Conversion\Media;
use WebpConverter\Error\ErrorDetectorAggregator;
use WebpConverter\Notice;
use WebpConverter\Plugin;
use WebpConverter\Repository\TokenRepository;
use WebpConverter\Service;
use WebpConverter\Settings\Page;

/**
 * Class initializes all plugin actions.
 */
class WebpConverter {

	public function __construct( PluginInfo $plugin_info ) {
		$plugin_data      = new PluginData();
		$token_repository = new TokenRepository();

		( new Action\ConvertAttachment( $plugin_data ) )->init_hooks();
		( new Action\ConvertPaths( $plugin_data ) )->init_hooks();
		( new Action\DeletePaths() )->init_hooks();
		( new Conversion\Directory\DirectoryFactory() )->init_hooks();
		( new Endpoint\EndpointIntegration( new Endpoint\CronConversionEndpoint( $plugin_data, $token_repository ) ) )->init_hooks();
		( new Endpoint\EndpointIntegration( new Endpoint\FilesStatsEndpoint( $plugin_data ) ) )->init_hooks();
		( new Endpoint\EndpointIntegration( new Endpoint\PathsEndpoint( $plugin_data, $token_repository ) ) )->init_hooks();
		( new Endpoint\EndpointIntegration( new Endpoint\RegenerateEndpoint( $plugin_data ) ) )->init_hooks();
		( new Conversion\SkipExcludedPaths() )->init_hooks();
		( new Cron\CronEventGenerator( $plugin_data, $token_repository ) )->init_hooks();
		( new Cron\CronSchedulesGenerator() )->init_hooks();
		( new Cron\CronStatusViewer() )->init_hooks();
		( new ErrorDetectorAggregator( $plugin_info, $plugin_data ) )->init_hooks();
		( new Notice\NoticeIntegration( $plugin_info, new Notice\WelcomeNotice() ) )->init_hooks();
		( new Notice\NoticeIntegration( $plugin_info, new Notice\ThanksNotice() ) )->init_hooks();
		( new Notice\NoticeIntegration( $plugin_info, new Notice\CloudflareNotice() ) )->init_hooks();
		( new Notice\NoticeIntegration( $plugin_info, new Notice\LitespeedNotice() ) )->init_hooks();
		( new Notice\NoticeIntegration( $plugin_info, new Notice\UpgradeNotice( $plugin_data ) ) )->init_hooks();
		( new Loader\LoaderIntegration( new Loader\HtaccessLoader( $plugin_info, $plugin_data ) ) )->init_hooks();
		( new Loader\LoaderIntegration( new Loader\PassthruLoader( $plugin_info, $plugin_data ) ) )->init_hooks();
		( new Media\Delete() )->init_hooks();
		( new Media\Upload( $plugin_data, $token_repository ) )->init_hooks();
		( new Plugin\Activation( $plugin_info ) )->init_hooks();
		( new Plugin\Deactivation( $plugin_info ) )->init_hooks();
		( new Plugin\Links( $plugin_info, $token_repository ) )->init_hooks();
		( new Plugin\Uninstall( $plugin_info ) )->init_hooks();
		( new Plugin\Update( $plugin_info ) )->init_hooks();
		( new Page\PageIntegration( $plugin_info ) )
			->set_page_integration( new Page\GeneralSettingsPage( $plugin_info, $plugin_data, $token_repository ) )
			->set_page_integration( new Page\AdvancedSettingsPage( $plugin_info, $plugin_data, $token_repository ) )
			->set_page_integration( new Page\DebugPage( $plugin_info, $plugin_data ) )
			->set_page_integration( new Page\BulkOptimizationPage( $plugin_info, $plugin_data, $token_repository ) )
			->init_hooks();
		( new Service\BackupExcluder() )->init_hooks();
		( new Service\DeactivationModalGenerator( $plugin_info, $plugin_data ) )->load_modal();
		( new Service\MediaStatusViewer( $plugin_data, $token_repository ) )->init_hooks();
		( new Service\WpCliManager( $plugin_data, $token_repository ) )->init_hooks();
		( new Settings\AdminAssets( $plugin_info ) )->init_hooks();
	}
}
