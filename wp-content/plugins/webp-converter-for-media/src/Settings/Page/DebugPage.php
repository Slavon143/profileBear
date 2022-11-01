<?php


namespace WebpConverter\Settings\Page;

use WebpConverter\Error\Detector\RewritesErrorsDetector;
use WebpConverter\Loader\LoaderAbstract;
use WebpConverter\PluginData;
use WebpConverter\PluginInfo;
use WebpConverter\Service\FileLoader;

/**
 * {@inheritdoc}
 */
class DebugPage extends PageAbstract {

	const PAGE_SLUG      = 'debug';
	const PAGE_VIEW_PATH = 'views/settings-debug.php';

	/**
	 * @var PluginInfo
	 */
	private $plugin_info;

	/**
	 * @var PluginData
	 */
	private $plugin_data;

	/**
	 * @var FileLoader
	 */
	private $file_loader;

	public function __construct(
		PluginInfo $plugin_info,
		PluginData $plugin_data,
		FileLoader $file_loader = null
	) {
		$this->plugin_info = $plugin_info;
		$this->plugin_data = $plugin_data;
		$this->file_loader = $file_loader ?: new FileLoader( $plugin_info, $plugin_data );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_slug(): string {
		return self::PAGE_SLUG;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label(): string {
		return __( 'Help Center', 'webp-converter-for-media' );
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
		$uploads_url  = apply_filters( 'webpc_dir_url', '', 'uploads' );
		$uploads_path = apply_filters( 'webpc_dir_path', '', 'uploads' );
		$ver_param    = time();

		do_action( LoaderAbstract::ACTION_NAME, true, true );

		$data = [
			'logo_url'              => $this->plugin_info->get_plugin_directory_url() . 'assets/img/logo-headline.png',
			'errors_messages'       => apply_filters( 'webpc_server_errors_messages', [] ),
			'errors_codes'          => apply_filters( 'webpc_server_errors', [] ),
			'size_png_path'         => $this->file_loader->get_file_size_by_path(
				$uploads_path . RewritesErrorsDetector::PATH_OUTPUT_FILE_PNG
			),
			'size_png2_path'        => $this->file_loader->get_file_size_by_path(
				$uploads_path . RewritesErrorsDetector::PATH_OUTPUT_FILE_PNG2
			),
			'size_png_url'          => $this->file_loader->get_file_size_by_url(
				$uploads_url . RewritesErrorsDetector::PATH_OUTPUT_FILE_PNG,
				false,
				$ver_param
			),
			'size_png2_url'         => $this->file_loader->get_file_size_by_url(
				$uploads_url . RewritesErrorsDetector::PATH_OUTPUT_FILE_PNG2,
				false,
				$ver_param
			),
			'size_png_as_webp_url'  => $this->file_loader->get_file_size_by_url(
				$uploads_url . RewritesErrorsDetector::PATH_OUTPUT_FILE_PNG,
				true,
				$ver_param
			),
			'size_png2_as_webp_url' => $this->file_loader->get_file_size_by_url(
				$uploads_url . RewritesErrorsDetector::PATH_OUTPUT_FILE_PNG2,
				true,
				$ver_param
			),
			'plugin_settings'       => $this->plugin_data->get_public_settings(),
			'url_debug_page'        => PageIntegration::get_settings_page_url( self::PAGE_SLUG ),
		];

		do_action( LoaderAbstract::ACTION_NAME, true );
		return $data;
	}
}
