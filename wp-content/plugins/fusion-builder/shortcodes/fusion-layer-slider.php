<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

/**
 * Map shortcode to Avada Builder
 *
 * @since 1.0
 */
function fusion_element_layer_slider() {
	if ( ! defined( 'LS_PLUGIN_BASE' ) ) {
		return;
	}
	fusion_builder_map(
		[
			'name'       => esc_attr__( 'Layer Slider', 'fusion-builder' ),
			'shortcode'  => 'layerslider',
			'icon'       => 'fusiona-stack',
			'preview'    => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-layer-slider-preview.php',
			'preview_id' => 'fusion-builder-block-module-layer-slider-preview-template',
			'help_url'   => 'https://theme-fusion.com/documentation/avada/elements/layer-slider-element/',
			'params'     => [
				[
					'type'        => 'select',
					'heading'     => esc_attr__( 'Select Slider', 'fusion-builder' ),
					'description' => esc_attr__( 'Select a slider group.', 'fusion-builder' ),
					'param_name'  => 'id',
					'value'       => fusion_builder_get_layerslider_slides(),
				],
			],
		]
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_layer_slider' );

/**
 * Add a filter to trigger the layerSlider script when in the front-end builder.
 *
 * @since 6.0
 * @param string $markup The slider markup.
 * @param object $slider The slider.
 * @param string $id     The slider ID.
 * @return string        Returns $markup without any changes.
 */
add_filter(
	'layerslider_slider_init',
	function( $init, $slider, $id ) {

		// If we're in the front-end builder preview frame
		// then we need to add an extra action to the footer.
		if ( is_user_logged_in() && fusion_is_preview_frame() ) {

			/**
			 * Adds our script to the footer.
			 *
			 * @since 6.0
			 * @uses $id from the parent filter.
			 * @return void
			 */
			add_action(
				'wp_footer',
				function() use ( $init ) {
					/**
					 * Note to theme reviewers:
					 * $init here is a hardcoded value in the LayerSlider plugin.
					 * No user-input is involved so there is no need to escape it.
					 */
					echo '<script>' . $init . '</script>'; // phpcs:ignore WordPress.Security.EscapeOutput
				},
				9999
			);
		}

		return $init;
	},
	10,
	3
);
