<?php
/**
 * Import Avada demo.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage CLI
 * @since      7.3
 *
 * @usage: wp fusion demo import --demo_type=spa
 * @todo: activate required plugins before import.
 */

$fusion_demo_import_cmd = function( $args, $assoc_args ) {

	$import_stages = avada_get_demo_import_stages();

	$demos = Avada_Importer_Data::get_data();

	foreach ( $demos as $demo => $demo_details ) {
		$demo_import_stages = [ 'download' ];
		$demo_content_types = [];

		if ( empty( $demo_details['plugin_dependencies'] ) ) {
			$demo_details['plugin_dependencies'] = [];
		}

		$demo_details['plugin_dependencies']['fusion-core']    = true;
		$demo_details['plugin_dependencies']['fusion-builder'] = true;

		// Build import stages for this demo.
		foreach ( $import_stages as $import_stage ) {

			if ( ! empty( $import_stage['plugin_dependency'] ) && empty( $demo_details['plugin_dependencies'][ $import_stage['plugin_dependency'] ] ) ) {
				continue;
			}

			if ( ! empty( $import_stage['feature_dependency'] ) && ! in_array( $import_stage['feature_dependency'], $demo_details['features'] ) ) { // phpcs:ignore WordPress.PHP.StrictInArray
				continue;
			}

			if ( isset( $import_stage['data'] ) && 'content' === $import_stage['data'] ) {
				$demo_content_types[] = $import_stage['value'];

				if ( false === in_array( 'content', $demo_import_stages, true ) ) {
					$demo_import_stages[] = 'content';
				}
			} else {
				$demo_import_stages[] = $import_stage['value'];
			}
		}

		$demo_import_stages[]            = 'general_data';
		$demos[ $demo ]['import_stages'] = $demo_import_stages;

		$demos[ $demo ]['content_types'] = $demo_content_types;
	}

	$demo_type = 'classic';
	if ( ! empty( $assoc_args['demo_type'] ) ) {
		$demo_type = $assoc_args['demo_type'];
	}

	// Build import args.
	$args = [
		'importStages'     => $demos[ $demo_type ]['import_stages'],
		'demoType'         => $demo_type,
		'fetchAttachments' => true,
		'contentTypes'     => $demos[ $demo_type ]['content_types'],
		'allImport'        => true,
	];

	// Import demo finally.
	if ( ! class_exists( 'Avada_Demo_Import' ) ) {
		include Avada::$template_dir_path . '/includes/importer/importer.php';
	}
	$avada_import = new Avada_Demo_Import();

	foreach ( $demos[ $demo ]['import_stages'] as $import_stage ) {
		echo 'Processing: ' . $import_stage . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		$avada_import->import_demo_stage( $args );

		// Remove processed import stage.
		array_shift( $args['importStages'] );
	}

	WP_CLI::success( 'Demo imported.' );
};

if ( class_exists( 'WP_CLI' ) ) {
	WP_CLI::add_command( 'fusion demo import', $fusion_demo_import_cmd );
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
