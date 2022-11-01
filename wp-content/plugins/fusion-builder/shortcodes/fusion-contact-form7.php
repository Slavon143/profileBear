<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 2.1
 */

if ( defined( 'WPCF7_PLUGIN' ) ) {
	if ( ! function_exists( 'fusion_builder_get_cf7_forms' ) ) {
		/**
		 * Returns array of contactform7 forms.
		 *
		 * @since 2.0
		 * @return array form keys array.
		 */
		function fusion_builder_get_cf7_forms() {

			$form_array = [ 0 => esc_attr__( 'Select a form', 'fusion-builder' ) ];

			$args = [
				'post_type'      => 'wpcf7_contact_form',
				'posts_per_page' => -1,
			];

			$forms = get_posts( $args );

			if ( is_array( $forms ) ) {
				foreach ( $forms as $form ) {
					$form_array[ $form->ID ] = $form->post_title;
				}
			}

			return $form_array;
		}
	}

	/**
	 * Map shortcode to Avada Builder.
	 */
	function fusion_element_cf7() {
		$fusion_settings = awb_get_fusion_settings();
		$is_builder      = ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() );
		$to_link         = '';

		if ( $is_builder ) {
			$to_link = '<span class="fusion-panel-shortcut" data-fusion-option="form_input_height">' . __( 'Global Options', 'fusion-builder' ) . '</span>';
		} else {
			$to_link = '<a href="' . esc_url_raw( $fusion_settings->get_setting_link( 'form_input_height' ) ) . '" target="_blank">' . esc_attr__( 'Global Options', 'fusion-builder' ) . '</a>';
		}

		fusion_builder_map(
			[
				'name'       => esc_attr__( 'Contact Form 7', 'fusion-builder' ),
				'shortcode'  => 'contact-form-7',
				'icon'       => 'fusiona-envelope',
				'preview'    => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-contact-form7-preview.php',
				'preview_id' => 'fusion-builder-block-module-contact-form7-preview-template',
				'params'     => [
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Select Form', 'fusion-builder' ),
						'description' => sprintf(
							/* translators: link to theme-options */
							__( '<strong>NOTE:</strong> The form uses %s for styling.', 'fusion-builder' ),
							$to_link
						),
						'param_name'  => 'id',
						'value'       => fusion_builder_get_cf7_forms(),
						'to_link'     => 'form_input_height',
					],
				],
			]
		);
	}
	add_action( 'fusion_builder_before_init', 'fusion_element_cf7' );
}
