<?php

namespace WebpConverter\Conversion\Endpoint;

use WebpConverter\Conversion\FilesTreeFinder;
use WebpConverter\Conversion\Format\AvifFormat;
use WebpConverter\Conversion\Format\WebpFormat;
use WebpConverter\PluginData;

/**
 * Generated tree of files that can be optimized.
 */
class FilesStatsEndpoint extends EndpointAbstract {

	/**
	 * @var PluginData
	 */
	private $plugin_data;

	public function __construct( PluginData $plugin_data ) {
		$this->plugin_data = $plugin_data;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_route_name(): string {
		return 'images-stats';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_http_methods(): string {
		return \WP_REST_Server::READABLE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_route_response( \WP_REST_Request $request ) {
		$stats_data = ( new FilesTreeFinder( $this->plugin_data ) )
			->get_tree( [ WebpFormat::FORMAT_EXTENSION, AvifFormat::FORMAT_EXTENSION ] );

		return new \WP_REST_Response(
			[
				'value_webp_all'       => ( $stats_data['files_converted'][ WebpFormat::FORMAT_EXTENSION ] + $stats_data['files_unconverted'][ WebpFormat::FORMAT_EXTENSION ] ),
				'value_webp_converted' => $stats_data['files_converted'][ WebpFormat::FORMAT_EXTENSION ],
				'value_avif_all'       => ( $stats_data['files_converted'][ AvifFormat::FORMAT_EXTENSION ] + $stats_data['files_unconverted'][ AvifFormat::FORMAT_EXTENSION ] ),
				'value_avif_converted' => $stats_data['files_converted'][ AvifFormat::FORMAT_EXTENSION ],
				'tree'                 => $stats_data['files_tree'],
			],
			200
		);
	}
}
