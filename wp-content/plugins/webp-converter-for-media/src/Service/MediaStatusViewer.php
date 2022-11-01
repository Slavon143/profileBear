<?php

namespace WebpConverter\Service;

use WebpConverter\Conversion\Format\AvifFormat;
use WebpConverter\Conversion\Format\WebpFormat;
use WebpConverter\Conversion\Media\Attachment;
use WebpConverter\Conversion\OutputPath;
use WebpConverter\Conversion\SkipLarger;
use WebpConverter\HookableInterface;
use WebpConverter\Model\Token;
use WebpConverter\PluginData;
use WebpConverter\Repository\TokenRepository;
use WebpConverter\Settings\Option\MediaStatsOption;
use WebpConverter\Settings\Page\PageIntegration;

/**
 * Generates information about conversion status in Media Library.
 */
class MediaStatusViewer implements HookableInterface {

	/**
	 * @var PluginData
	 */
	private $plugin_data;

	/**
	 * @var TokenRepository
	 */
	private $token_repository;

	/**
	 * @var OutputPath
	 */
	private $output_path;

	/**
	 * @var Attachment|null
	 */
	private $attachment = null;

	/**
	 * @var Token|null
	 */
	private $token = null;

	public function __construct(
		PluginData $plugin_data,
		TokenRepository $token_repository,
		OutputPath $output_path = null
	) {
		$this->plugin_data      = $plugin_data;
		$this->token_repository = $token_repository;
		$this->output_path      = $output_path ?: new OutputPath();
	}

	/**
	 * {@inheritdoc}
	 */
	public function init_hooks() {
		add_action( 'init', [ $this, 'init_hooks_after_setup' ] );
	}

	/**
	 * @return void
	 * @internal
	 */
	public function init_hooks_after_setup() {
		$plugin_settings = $this->plugin_data->get_plugin_settings();
		if ( ! $plugin_settings[ MediaStatsOption::OPTION_NAME ] ) {
			return;
		}

		add_filter( 'manage_media_columns', [ $this, 'add_custom_table_column' ] );
		add_action( 'manage_media_custom_column', [ $this, 'print_table_column_value' ], 10, 2 );
		add_action( 'attachment_submitbox_misc_actions', [ $this, 'print_attachment_sidebar_value' ], 20 );
		add_filter( 'wp_prepare_attachment_for_js', [ $this, 'add_status_for_attachment_data' ], 10, 2 );
	}

	/**
	 * @param string[] $columns .
	 *
	 * @return string[]
	 * @internal
	 */
	public function add_custom_table_column( array $columns ): array {
		$columns['webpc_status'] = 'Converter for Media';
		return $columns;
	}

	/**
	 * @param string $column_name .
	 * @param int    $post_id     .
	 *
	 * @return void
	 * @internal
	 */
	public function print_table_column_value( string $column_name, int $post_id ) {
		if ( $column_name !== 'webpc_status' ) {
			return;
		}

		$conversion_status = $this->get_conversion_status( $post_id );
		if ( $conversion_status === null ) {
			return;
		}

		echo wp_kses_post( implode( '<br>', $conversion_status ) );
	}

	/**
	 * @param \WP_Post $post .
	 *
	 * @return void
	 * @internal
	 */
	public function print_attachment_sidebar_value( \WP_Post $post ) {
		$conversion_status = $this->get_conversion_status( $post->ID );
		if ( $conversion_status === null ) {
			return;
		}

		$conversion_status[] = sprintf(
		/* translators: %s: plugin name */
			'<small>' . __( 'Optimized by: %s', 'webp-converter-for-media' ) . '</small>',
			sprintf( '<a href="%1$s">%2$s</a>', esc_attr( PageIntegration::get_settings_page_url() ), 'Converter for Media' )
		);

		?>
		<div class="misc-pub-section misc-pub-webpc">
			<?php echo wp_kses_post( implode( '<br>', $conversion_status ) ); ?>
		</div>
		<?php
	}

	/**
	 * @param mixed[]  $response   .
	 * @param \WP_Post $attachment .
	 *
	 * @return mixed[]
	 * @internal
	 */
	public function add_status_for_attachment_data( array $response, \WP_Post $attachment ): array {
		$source_post_id = (string) ( $_REQUEST['post_id'] ?? '' ); // phpcs:ignore WordPress.Security
		if ( $source_post_id !== '0' ) {
			return $response;
		}

		$conversion_status = $this->get_conversion_status( $attachment->ID );
		if ( $conversion_status === null ) {
			return $response;
		}

		$conversion_status[] = sprintf(
		/* translators: %s: plugin name */
			'<small>' . __( 'Optimized by: %s', 'webp-converter-for-media' ) . '</small>',
			sprintf( '<a href="%1$s">%2$s</a>', esc_attr( PageIntegration::get_settings_page_url() ), 'Converter for Media' )
		);

		$response['compat']         = $response['compat'] ?? [];
		$response['compat']['meta'] = $response['compat']['meta'] ?? '';

		$response['compat']['meta'] .= '<br>' . wp_kses_post( implode( '<br>', $conversion_status ) );
		return $response;
	}

	/**
	 * @param int $post_id .
	 *
	 * @return string[]|null
	 */
	private function get_conversion_status( int $post_id ) {
		$this->attachment = $this->attachment ?: new Attachment( $this->plugin_data );
		$this->token      = $this->token ?: $this->token_repository->get_token();

		$source_paths = $this->attachment->get_attachment_paths( $post_id );
		if ( ! $source_paths ) {
			return null;
		}

		$images_stats   = $this->get_images_stats( $source_paths );
		$size_original  = $images_stats[0]['original_size'];
		$size_optimized = $images_stats[0]['avif_size'] ?: $images_stats[0]['webp_size'];
		if ( ! $size_original ) {
			return null;
		}

		$webp_source_size = [];
		$webp_output_size = [];
		$avif_source_size = [];
		$avif_output_size = [];
		$webp_files_count = 0;
		$avif_files_count = 0;

		foreach ( $images_stats as $images_stat ) {
			if ( $images_stat['original_size'] === null ) {
				continue;
			}

			if ( $images_stat['webp_size'] !== null ) {
				$webp_source_size[] = (int) $images_stat['original_size'];
				$webp_output_size[] = $images_stat['webp_size'];
			}
			if ( $images_stat['avif_size'] !== null ) {
				$avif_source_size[] = (int) $images_stat['original_size'];
				$avif_output_size[] = $images_stat['avif_size'];
			}
			if ( $images_stat['webp_status'] ) {
				$webp_files_count++;
			}
			if ( $images_stat['avif_status'] ) {
				$avif_files_count++;
			}
		}

		$rows = [
			sprintf(
			/* translators: %s: file size */
				__( 'Original file size: %s', 'webp-converter-for-media' ),
				sprintf( '<strong>%s</strong>', size_format( $size_original ) )
			),
			sprintf(
			/* translators: %s: file size */
				__( 'Optimized file size: %s', 'webp-converter-for-media' ),
				( $size_optimized )
					?
					sprintf(
						'<strong>%1$s <abbr title="%2$s">(%3$s)</abbr></strong>',
						size_format( $size_optimized ),
						esc_attr( __( 'File size reduction of the uploaded image compared to the original one.', 'webp-converter-for-media' ) ),
						$this->get_percent_value( $size_original, $size_optimized )
					)
					: '<strong>-</strong>'
			),
			sprintf(
			/* translators: %1$s: output format, %2$s: number of images */
				__( 'Files converted to %1$s: %2$s', 'webp-converter-for-media' ),
				'WebP',
				( count( $webp_source_size ) > 0 )
					?
					sprintf(
						'<strong>%1$s <abbr title="%2$s">(%3$s)</abbr></strong>',
						$webp_files_count,
						esc_attr( __( 'File size reduction of all thumbnails compared to the original ones.', 'webp-converter-for-media' ) ),
						$this->get_percent_value( $webp_source_size, $webp_output_size )
					)
					: '<strong>' . $webp_files_count . '</strong>'
			),
		];

		if ( ! $this->token->get_valid_status() ) {
			$rows[] = sprintf(
			/* translators: %1$s: output format, %2$s: number of images */
				__( 'Files converted to %1$s: %2$s', 'webp-converter-for-media' ),
				'AVIF',
				sprintf(
					'<strong>%1$s</strong> <small>(<a href="%2$s">%3$s</a>)</small>',
					$avif_files_count,
					esc_attr( PageIntegration::get_settings_page_url() ),
					__( 'in the PRO', 'webp-converter-for-media' )
				)
			);
		} else {
			$rows[] = sprintf(
			/* translators: %1$s: output format, %2$s: number of images */
				__( 'Files converted to %1$s: %2$s', 'webp-converter-for-media' ),
				'AVIF',
				( count( $avif_source_size ) > 0 )
					?
					sprintf(
						'<strong>%1$s <abbr title="%2$s">(%3$s)</abbr></strong>',
						$avif_files_count,
						esc_attr( __( 'File size reduction of all thumbnails compared to the original ones.', 'webp-converter-for-media' ) ),
						$this->get_percent_value( $avif_source_size, $avif_output_size )
					)
					: '<strong>' . $avif_files_count . '</strong>'
			);
		}

		return $rows;
	}

	/**
	 * @param string[] $source_paths  .
	 *
	 * @return mixed[] {
	 * @type int|null  $original_size .
	 * @type int|null  $webp_size     .
	 * @type bool      $webp_status   .
	 * @type int|null  $avif_size     .
	 * @type bool      $avif_status   .
	 * }
	 */
	private function get_images_stats( array $source_paths ): array {
		$items = [];
		foreach ( $source_paths as $source_path ) {
			$output_path_webp = $this->output_path->get_path( $source_path, false, WebpFormat::FORMAT_EXTENSION );
			$output_path_avif = $this->output_path->get_path( $source_path, false, AvifFormat::FORMAT_EXTENSION );

			$filesize_original = ( file_exists( $source_path ) ) ? ( filesize( $source_path ) ?: null ) : null;
			$filesize_webp     = ( $output_path_webp )
				? ( ( file_exists( $output_path_webp ) ) ? ( filesize( $output_path_webp ) ?: null ) : null )
				: null;
			$filesize_avif     = ( $output_path_avif )
				? ( ( file_exists( $output_path_avif ) ) ? ( filesize( $output_path_avif ) ?: null ) : null )
				: null;

			$items[] = [
				'original_size' => $filesize_original,
				'webp_size'     => $filesize_webp,
				'webp_status'   => ( ( $filesize_webp !== null ) || file_exists( $output_path_webp . '.' . SkipLarger::DELETED_FILE_EXTENSION ) ),
				'avif_size'     => $filesize_avif,
				'avif_status'   => ( ( $filesize_avif !== null ) || file_exists( $output_path_avif . '.' . SkipLarger::DELETED_FILE_EXTENSION ) ),
			];
		}

		return $items;
	}

	/**
	 * @param int|int[] $source_size .
	 * @param int|int[] $output_size .
	 *
	 * @return string
	 */
	private function get_percent_value( $source_size, $output_size ): string {
		$source_size = ( is_array( $source_size ) ) ? array_sum( $source_size ) : $source_size;
		$output_size = ( is_array( $output_size ) ) ? array_sum( $output_size ) : $output_size;

		$output_percent = ( $output_size ) ? ( 100 - round( $output_size / $source_size * 100 ) ) : 0;
		return ( $output_percent >= 0 )
			? sprintf( '-%s%%', $output_percent )
			: sprintf( '+%s%%', abs( $output_percent ) );
	}
}
