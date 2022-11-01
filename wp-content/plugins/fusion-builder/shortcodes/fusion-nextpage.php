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
function fusion_builder_element_next_page() {
	fusion_builder_map(
		[
			'name'              => esc_attr__( 'Next Page', 'fusion-builder' ),
			'shortcode'         => 'fusion_builder_next_page',
			'hide_from_builder' => true,
			'help_url'          => 'https://theme-fusion.com/documentation/avada/elements/next-page-pagination-element/',
			'params'            => [
				[
					'type'        => 'textfield',
					'heading'     => '',
					'description' => '',
					'param_name'  => 'next_page_content',
					'value'       => '',
				],
			],
		]
	);
}
add_action( 'fusion_builder_before_init', 'fusion_builder_element_next_page' );
