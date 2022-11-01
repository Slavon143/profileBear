<?php


namespace WebpConverter\Settings\Page;

use WebpConverter\Conversion\Endpoint\FilesStatsEndpoint;
use WebpConverter\Conversion\Endpoint\PathsEndpoint;
use WebpConverter\Conversion\Endpoint\RegenerateEndpoint;
use WebpConverter\Loader\LoaderAbstract;
use WebpConverter\PluginData;
use WebpConverter\PluginInfo;
use WebpConverter\Repository\TokenRepository;
use WebpConverter\Service\NonceManager;
use WebpConverter\Settings\Option\OptionAbstract;
use WebpConverter\Settings\PluginOptions;
use WebpConverter\Settings\SettingsSave;

/**
 * {@inheritdoc}
 */
class GeneralSettingsPage extends PageAbstract {

	const PAGE_VIEW_PATH = 'views/settings.php';

	/**
	 * @var PluginInfo
	 */
	private $plugin_info;

	/**
	 * @var PluginData
	 */
	private $plugin_data;

	/**
	 * @var TokenRepository
	 */
	private $token_repository;

	public function __construct(
		PluginInfo $plugin_info,
		PluginData $plugin_data,
		TokenRepository $token_repository
	) {
		$this->plugin_info      = $plugin_info;
		$this->plugin_data      = $plugin_data;
		$this->token_repository = $token_repository;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_slug() {
		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label(): string {
		return __( 'General Settings', 'webp-converter-for-media' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_template_path(): string {
		return self::PAGE_VIEW_PATH;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_template_vars(): array {
		( new SettingsSave( $this->plugin_data ) )->save_settings();

		$token = $this->token_repository->get_token();
		$data  = [
			'logo_url'                 => $this->plugin_info->get_plugin_directory_url() . 'assets/img/logo-headline.png',
			'errors_messages'          => apply_filters( 'webpc_server_errors_messages', [] ),
			'errors_codes'             => apply_filters( 'webpc_server_errors', [] ),
			'form_options'             => ( new PluginOptions() )->get_options( OptionAbstract::FORM_TYPE_BASIC ),
			'form_sidebar_options'     => ( new PluginOptions() )->get_options( OptionAbstract::FORM_TYPE_SIDEBAR ),
			'form_input_name'          => SettingsSave::FORM_TYPE_PARAM_KEY,
			'form_input_value'         => OptionAbstract::FORM_TYPE_BASIC,
			'form_sidebar_input_value' => OptionAbstract::FORM_TYPE_SIDEBAR,
			'nonce_input_name'         => SettingsSave::NONCE_PARAM_KEY,
			'nonce_input_value'        => ( new NonceManager() )->generate_nonce( SettingsSave::NONCE_PARAM_VALUE ),
			'token_valid_status'       => $token->get_valid_status(),
			'token_active_status'      => $token->is_active(),
			'api_paths_url'            => ( new PathsEndpoint( $this->plugin_data, $this->token_repository ) )->get_route_url(),
			'api_regenerate_url'       => ( new RegenerateEndpoint( $this->plugin_data ) )->get_route_url(),
			'api_stats_url'            => ( new FilesStatsEndpoint( $this->plugin_data ) )->get_route_url(),
			'url_debug_page'           => PageIntegration::get_settings_page_url( DebugPage::PAGE_SLUG ),
			'output_formats'           => [
				'webp' => [
					'label' => 'WebP',
					'desc'  => ( ! $token->get_valid_status() )
						? __( 'available in the free version', 'webp-converter-for-media' )
						: null,
				],
				'avif' => [
					'label' => 'AVIF',
					'desc'  => ( ! $token->get_valid_status() )
						? sprintf(
						/* translators: %1$s: open anchor tag, %2$s: close anchor tag */
							__( 'available in %1$sthe PRO version%2$s', 'webp-converter-for-media' ),
							'<a href="https://url.mattplugins.com/converter-regeneration-widget-avif-upgrade" target="_blank">',
							'</a>'
						)
						: null,
				],
			],
		];

		do_action( LoaderAbstract::ACTION_NAME, true );
		return $data;
	}
}
