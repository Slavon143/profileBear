<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 2.1
 */

if ( class_exists( 'GFForms' ) ) {
	if ( ! function_exists( 'fusion_builder_get_gravity_forms' ) ) {
		/**
		 * Returns array of gravity forms.
		 *
		 * @since 2.1
		 * @return array form keys array.
		 */
		function fusion_builder_get_gravity_forms() {

			$form_array = [ 0 => esc_attr__( 'Select a form', 'fusion-builder' ) ];

			$forms = GFAPI::get_forms();

			if ( is_array( $forms ) ) {
				foreach ( $forms as $form ) {
					$form_array[ $form['id'] ] = $form['title'];
				}
			}

			return $form_array;
		}
	}

	/**
	 * Map shortcode to Avada Builder.
	 */
	function fusion_element_gravity_form() {
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
				'name'       => esc_attr__( 'Gravity Form', 'fusion-builder' ),
				'shortcode'  => 'gravityform',
				'icon'       => 'fusiona-Gravityforms',
				'preview'    => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-gravity-form-preview.php',
				'preview_id' => 'fusion-builder-block-module-gravity-form-preview-template',
				'params'     => [
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Select Form', 'fusion-builder' ),
						'description' => sprintf(
							/* translators: link to theme-options */
							__( '<strong>NOTE:</strong> The form uses %s for stying.', 'fusion-builder' ),
							$to_link
						),
						'param_name'  => 'id',
						'value'       => fusion_builder_get_gravity_forms(),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Display Form Title', 'fusion-builder' ),
						'description' => esc_attr__( 'Whether or not to display the form title.', 'fusion-builder' ),
						'param_name'  => 'title',
						'default'     => 'true',
						'value'       => [
							'true'  => esc_attr__( 'Yes', 'fusion-builder' ),
							'false' => esc_attr__( 'No', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Display Form Description', 'fusion-builder' ),
						'description' => esc_attr__( 'Whether or not to display the form description.', 'fusion-builder' ),
						'param_name'  => 'description',
						'default'     => 'true',
						'value'       => [
							'true'  => esc_attr__( 'Yes', 'fusion-builder' ),
							'false' => esc_attr__( 'No', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Enable Ajax', 'fusion-builder' ),
						'description' => esc_attr__( 'Specify whether or not to use Ajax to submit the form.', 'fusion-builder' ),
						'param_name'  => 'ajax',
						'default'     => 'false',
						'value'       => [
							'true'  => esc_attr__( 'Yes', 'fusion-builder' ),
							'false' => esc_attr__( 'No', 'fusion-builder' ),
						],
					],
				],
			]
		);
	}
	add_action( 'fusion_builder_before_init', 'fusion_element_gravity_form' );
}
