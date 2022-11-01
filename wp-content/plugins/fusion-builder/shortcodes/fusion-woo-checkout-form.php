<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.3
 */

/**
 * Map shortcode to Avada Builder
 *
 * @since 3.3
 */
function fusion_builder_element_checkout_form() {

	fusion_builder_map(
		[
			'name'              => esc_attr__( 'Checkout Form', 'fusion-builder' ),
			'shortcode'         => 'fusion_woo_checkout_form',
			'hide_from_builder' => true,
			'help_url'          => '',
			'params'            => [
				[
					'type'        => 'textfield',
					'heading'     => '',
					'description' => '',
					'param_name'  => 'checkout_form_content',
					'value'       => '',
				],
			],
		]
	);
}
add_action( 'fusion_builder_before_init', 'fusion_builder_element_checkout_form' );
