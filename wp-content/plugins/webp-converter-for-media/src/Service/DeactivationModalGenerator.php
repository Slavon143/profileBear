<?php

namespace WebpConverter\Service;

use WebpConverter\Error\Notice\LibsNotInstalledNotice;
use WebpConverter\Error\Notice\LibsWithoutWebpSupportNotice;
use WebpConverter\PluginData;
use WebpConverter\PluginInfo;
use WebpConverter\Settings\Page\PageIntegration;
use WebpConverterVendor\MattPlugins\DeactivationModal;

/**
 * Initiates the popup displayed when the plugin is deactivated.
 */
class DeactivationModalGenerator {

	const API_URL = 'https://data.mattplugins.com/deactivations/%s';

	/**
	 * @var PluginInfo
	 */
	private $plugin_info;

	/**
	 * @var PluginData
	 */
	protected $plugin_data;

	/**
	 * @var StatsManager
	 */
	private $stats_manager;

	public function __construct(
		PluginInfo $plugin_info,
		PluginData $plugin_data,
		StatsManager $stats_manager = null
	) {
		$this->plugin_info   = $plugin_info;
		$this->plugin_data   = $plugin_data;
		$this->stats_manager = $stats_manager ?: new StatsManager();
	}

	/**
	 * @return void
	 * @throws DeactivationModal\Exception\DuplicatedFormOptionKeyException
	 * @throws DeactivationModal\Exception\DuplicatedFormValueKeyException
	 */
	public function load_modal() {
		new DeactivationModal\Modal(
			$this->plugin_info->get_plugin_slug(),
			new DeactivationModal\Model\FormTemplate(
				sprintf( self::API_URL, $this->plugin_info->get_plugin_slug() ),
				sprintf(
				/* translators: %s: plugin name */
					__( 'We are sorry that you are leaving our plugin %s', 'webp-converter-for-media' ),
					'Converter for Media'
				),
				__( 'Can you please take a moment to tell us why you are deactivating this plugin (anonymous answer)?', 'webp-converter-for-media' ),
				__( 'Submit and Deactivate', 'webp-converter-for-media' ),
				__( 'Skip and Deactivate', 'webp-converter-for-media' ),
				'https://mattplugins.com/images/matt-plugins-gray.png'
			),
			( new DeactivationModal\Model\FormOptions() )
				->set_option(
					new DeactivationModal\Model\FormOption(
						'server_config',
						10,
						__( 'I have "Server configuration error" in plugin settings', 'webp-converter-for-media' ),
						function () {
							$errors = apply_filters( 'webpc_server_errors', [] );
							if ( ! in_array( $errors, [ [ LibsWithoutWebpSupportNotice::ERROR_KEY ], [ LibsNotInstalledNotice::ERROR_KEY ] ] ) ) {
								return null;
							}

							return sprintf(
							/* translators: %1$s: open anchor tag, %2$s: close anchor tag */
								__( 'If your server does not meet the technical requirements, you can use "Remote server" as "Conversion method", in %1$sthe plugin settings%2$s.', 'webp-converter-for-media' ),
								'<a href="' . PageIntegration::get_settings_page_url() . '">',
								'</a>'
							);
						},
						__( 'What is your error? Have you been looking for solution to this issue?', 'webp-converter-for-media' )
					)
				)
				->set_option(
					new DeactivationModal\Model\FormOption(
						'misunderstanding',
						20,
						__( 'Images are not displayed in WebP format', 'webp-converter-for-media' ),
						function () {
							return sprintf(
							/* translators: %1$s: open anchor tag, %2$s: close anchor tag */
								__( 'Check out %1$sour instructions%2$s and see how to check if the plugin is working properly.', 'webp-converter-for-media' ),
								'<a href="https://url.mattplugins.com/converter-deactivation-misunderstanding-instruction" target="_blank">',
								'</a>'
							);
						},
						__( 'Did you check the operation of the plugin in accordance with the instructions?', 'webp-converter-for-media' )
					)
				)
				->set_option(
					new DeactivationModal\Model\FormOption(
						'website_broken',
						30,
						__( 'This plugin broke my website', 'webp-converter-for-media' ),
						null,
						__( 'What exactly happened?', 'webp-converter-for-media' )
					)
				)
				->set_option(
					new DeactivationModal\Model\FormOption(
						'better_plugin',
						40,
						__( 'I found a better plugin', 'webp-converter-for-media' ),
						null,
						__( 'What is name of this plugin? Why is it better?', 'webp-converter-for-media' )
					)
				)
				->set_option(
					new DeactivationModal\Model\FormOption(
						'temporary_deactivation',
						50,
						__( 'This is a temporary deactivation', 'webp-converter-for-media' ),
						null,
						null
					)
				)
				->set_option(
					new DeactivationModal\Model\FormOption(
						'other',
						60,
						__( 'Other reason', 'webp-converter-for-media' ),
						null,
						__( 'What is reason? What can we improve for you?', 'webp-converter-for-media' )
					)
				),
			( new DeactivationModal\Model\FormValues() )
				->set_value(
					new DeactivationModal\Model\FormValue(
						'request_error_codes',
						function () {
							return implode( ',', apply_filters( 'webpc_server_errors', [] ) );
						}
					)
				)
				->set_value(
					new DeactivationModal\Model\FormValue(
						'request_plugin_settings',
						function () {
							$settings_json = json_encode( $this->plugin_data->get_public_settings() );
							return base64_encode( $settings_json ?: '' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
						}
					)
				)
				->set_value(
					new DeactivationModal\Model\FormValue(
						'request_plugin_stats',
						function () {
							$stats_data = [
								'usage_time'          => $this->stats_manager->get_plugin_usage_time(),
								'first_version'       => $this->stats_manager->get_plugin_first_version(),
								'regeneration_images' => $this->stats_manager->get_regeneration_images(),
								'webp_all'            => $this->stats_manager->get_images_webp_all(),
								'webp_unconverted'    => $this->stats_manager->get_images_webp_unconverted(),
								'avif_all'            => $this->stats_manager->get_images_avif_all(),
								'avif_unconverted'    => $this->stats_manager->get_images_avif_unconverted(),
							];

							$stats_json = json_encode( $stats_data );
							return base64_encode( $stats_json ?: '' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
						}
					)
				)
				->set_value(
					new DeactivationModal\Model\FormValue(
						'request_plugin_version',
						function () {
							return $this->plugin_info->get_plugin_version();
						}
					)
				)
		);
	}
}
