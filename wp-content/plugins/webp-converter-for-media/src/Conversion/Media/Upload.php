<?php

namespace WebpConverter\Conversion\Media;

use WebpConverter\Conversion\Cron\CronInitiator;
use WebpConverter\HookableInterface;
use WebpConverter\PluginData;
use WebpConverter\Repository\TokenRepository;
use WebpConverter\Settings\Option\AutoConversionOption;
use WebpConverter\Settings\Option\SupportedExtensionsOption;

/**
 * Initializes image conversion when uploading images to media library.
 */
class Upload implements HookableInterface {

	/**
	 * @var PluginData
	 */
	private $plugin_data;

	/**
	 * @var CronInitiator
	 */
	private $cron_initiator;

	/**
	 * Paths of converted images.
	 *
	 * @var string[]
	 */
	private $uploaded_paths = [];

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
		add_filter( 'wp_update_attachment_metadata', [ $this, 'init_attachment_convert' ], 10, 2 );
	}

	/**
	 * Initializes converting attachment images while attachment is uploaded.
	 *
	 * @param mixed[]|null $data          Updated attachment meta data.
	 * @param int|null     $attachment_id ID of attachment.
	 *
	 * @return mixed[]|null Attachment meta data.
	 * @internal
	 */
	public function init_attachment_convert( array $data = null, int $attachment_id = null ) {
		if ( ( $data === null ) || ( $attachment_id === null )
			|| ! is_array( $data ) || ! isset( $data['file'] ) || ! isset( $data['sizes'] ) ) {
			return $data;
		}

		$plugin_settings = $this->plugin_data->get_plugin_settings();
		if ( ! $plugin_settings[ AutoConversionOption::OPTION_NAME ] ) {
			return $data;
		}

		$file_extension = strtolower( pathinfo( $data['file'], PATHINFO_EXTENSION ) );
		if ( ! in_array( $file_extension, $plugin_settings[ SupportedExtensionsOption::OPTION_NAME ] ) ) {
			return $data;
		}

		$paths                = $this->get_sizes_paths( $data );
		$paths                = apply_filters( 'webpc_attachment_paths', $paths, $attachment_id );
		$this->uploaded_paths = array_merge( $this->uploaded_paths, $paths );

		add_action( 'shutdown', [ $this, 'save_paths_to_conversion' ] );

		return $data;
	}

	/**
	 * Returns server paths of attachment image sizes.
	 *
	 * @param mixed[] $data Updated attachment meta data.
	 *
	 * @return string[] Server paths of source images.
	 */
	private function get_sizes_paths( array $data ): array {
		$directory = $this->get_attachment_directory( $data['file'] );
		$list      = [];

		if ( isset( $data['original_image'] ) ) {
			$list[] = $directory . $data['original_image'];
		}

		$list[] = $directory . basename( $data['file'] );
		foreach ( $data['sizes'] as $size ) {
			$path = $directory . $size['file'];
			if ( ! in_array( $path, $list ) ) {
				$list[] = $path;
			}
		}
		return array_values( array_unique( $list ) );
	}

	/**
	 * Returns server path of source image.
	 *
	 * @param string $path Relative path of source image.
	 *
	 * @return string Server path of source image.
	 */
	private function get_attachment_directory( string $path ): string {
		$upload         = wp_upload_dir();
		$path_directory = rtrim( dirname( $path ), '/\\.' );
		$source         = rtrim( $upload['basedir'], '/\\' ) . '/' . $path_directory . '/';

		return str_replace( '\\', '/', $source );
	}

	/**
	 * @return void
	 *
	 * @internal
	 */
	public function save_paths_to_conversion() {
		$paths = array_unique( $this->uploaded_paths );
		if ( ! $paths ) {
			return;
		}

		$this->cron_initiator->add_paths_to_conversion( $paths, true );
		$this->cron_initiator->init_async_conversion();
	}
}
