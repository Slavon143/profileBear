<?php
/**
 * Form Submissions Metabox options.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    fusion-builder
 * @subpackage forms
 */

/**
 * Form Submissions page settings
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_page_options_tab_form_appearance( $sections ) {
	$sections['form_appearance'] = [
		'label'    => esc_html__( 'Appearance', 'Avada' ),
		'alt_icon' => 'fusiona-customize',
		'id'       => 'form_appearance',
		'fields'   => [
			'preview_width'            => [
				'type'        => 'radio-buttonset',
				'label'       => esc_attr__( 'Preview Width', 'Avada' ),
				/* translators: Additional description (defaults). */
				'description' => esc_html__( 'Select the width to preview the form at in the live editor.  Note: this is only used for previewing purposes.', 'fusion-builder' ),
				'id'          => 'preview_width',
				'dependency'  => [],
				'type'        => 'slider',
				'transport'   => 'postMessage',
				'events'      => [
					'fusion-content-preview-width',
				],
				'default'     => '100',
				'choices'     => [
					'min'  => '0',
					'max'  => '100',
					'step' => '1',
				],
			],
			'label_position'           => [
				'type'        => 'radio-buttonset',
				'label'       => esc_html__( 'Label Position', 'fusion-builder' ),
				'description' => esc_html__( 'Make a selection for form input fields labels position.', 'fusion-builder' ),
				'id'          => 'label_position',
				'default'     => 'above',
				'choices'     => [
					'above' => esc_html__( 'Above', 'fusion-builder' ),
					'below' => esc_html__( 'Below', 'fusion-builder' ),
				],
				'dependency'  => [],
				'transport'   => 'postMessage',
				'events'      => [
					'fusion-rerender-form-inputs',
				],
			],
			'tooltip_text_color'       => [
				'type'        => 'color-alpha',
				'label'       => esc_html__( 'Tooltip Text Color', 'fusion-builder' ),
				'description' => esc_html__( 'Controls the text color of the field tooltip.', 'fusion-builder' ),
				'id'          => 'tooltip_text_color',
				'default'     => '#ffffff',
				'transport'   => 'postMessage',
				'events'      => [
					'fusion-form-styles',
				],
			],
			'tooltip_background_color' => [
				'type'        => 'color-alpha',
				'label'       => esc_html__( 'Tooltip Background Color', 'fusion-builder' ),
				'description' => esc_html__( 'Controls the background color of the field tooltip.', 'fusion-builder' ),
				'id'          => 'tooltip_background_color',
				'default'     => '#333333',
				'transport'   => 'postMessage',
				'events'      => [
					'fusion-form-styles',
				],
			],
			'field_margin'             => [
				'type'             => 'dimensions',
				'label'            => esc_html__( 'Field Margin', 'fusion-builder' ),
				'remove_from_atts' => true,
				'id'               => 'field_margin',
				'value'            => [
					'top'    => '',
					'bottom' => '',
				],
				'dependency'       => [],
				'description'      => esc_html__( 'Controls the vertical height between form fields. In pixels (px), ex: 10px.', 'fusion-builder' ),
				'transport'        => 'postMessage',
				'events'           => [
					'fusion-form-styles',
				],
			],
			'form_input_height'        => [
				'type'        => 'text',
				'label'       => esc_html__( 'Field Height', 'fusion-builder' ),
				/* translators: Additional description (defaults). */
				'description' => sprintf( esc_html__( 'Controls the height of the form fields. Use any valid CSS value. %s', 'Avada' ), Avada()->settings->get_default_description( 'form_input_height' ) ),
				'id'          => 'form_input_height',
				'default'     => Avada()->settings->get( 'form_input_height' ),
				'to_default'  => [
					'id' => 'form_input_height',
				],
				'transport'   => 'postMessage',
				'events'      => [
					'fusion-form-styles',
				],
			],
			'form_font_size'           => [
				'type'        => 'text',
				'label'       => esc_html__( 'Field Font Size', 'fusion-builder' ),
				/* translators: Additional description (defaults). */
				'description' => sprintf( esc_html__( 'Controls the font size of the form fields text. Use any valid CSS value. %s', 'Avada' ), Avada()->settings->get_default_description( 'form_text_size' ) ),
				'id'          => 'form_font_size',
				'default'     => Avada()->settings->get( 'form_text_size' ),
				'to_default'  => [
					'id' => 'form_text_size',
				],
				'transport'   => 'postMessage',
				'events'      => [
					'fusion-form-styles',
				],
			],
			'form_bg_color'            => [
				'type'        => 'color-alpha',
				'label'       => esc_html__( 'Field Background Color', 'fusion-builder' ),
				/* translators: Additional description (defaults). */
				'description' => sprintf( esc_html__( 'Controls the background color of the form input field. %s', 'Avada' ), Avada()->settings->get_default_description( 'form_bg_color' ) ),
				'id'          => 'form_bg_color',
				'default'     => Avada()->settings->get( 'form_bg_color' ),
				'dependency'  => [],
				'to_default'  => [
					'id' => 'form_bg_color',
				],
				'transport'   => 'postMessage',
				'events'      => [
					'fusion-form-styles',
				],
			],
			'form_text_color'          => [
				'type'        => 'color-alpha',
				'label'       => esc_html__( 'Field Text Color', 'fusion-builder' ),
				/* translators: Additional description (defaults). */
				'description' => sprintf( esc_html__( 'Controls the text color of the form input field. %s', 'Avada' ), Avada()->settings->get_default_description( 'form_text_color' ) ),
				'id'          => 'form_text_color',
				'default'     => Avada()->settings->get( 'form_text_color' ),
				'dependency'  => [],
				'to_default'  => [
					'id' => 'form_text_color',
				],
				'transport'   => 'postMessage',
				'events'      => [
					'fusion-form-styles',
				],
			],
			'form_label_color'         => [
				'type'        => 'color-alpha',
				'label'       => esc_html__( 'Field Label Color', 'fusion-builder' ),
				/* translators: Additional description (defaults). */
				'description' => sprintf( esc_html__( 'Controls the label color of the form input field. %s', 'Avada' ), Avada()->settings->get_default_description( 'body_typography', 'color' ) ),
				'id'          => 'form_label_color',
				'default'     => Avada()->settings->get( 'body_typography', 'color' ),
				'dependency'  => [],
				'to_default'  => [
					'id'     => 'body_typography',
					'subset' => 'color',
				],
				'transport'   => 'postMessage',
				'events'      => [
					'fusion-form-styles',
				],
			],
			'form_placeholder_color'   => [
				'type'        => 'color-alpha',
				'label'       => esc_html__( 'Field Placeholder Color', 'fusion-builder' ),
				/* translators: Additional description (defaults). */
				'description' => esc_html__( 'Controls the label placeholder of the form input field. Leave empty to use a faded version of the form text color.', 'Avada' ),
				'id'          => 'form_placeholder_color',
				'value'       => '',
				'dependency'  => [],
				'transport'   => 'postMessage',
				'events'      => [
					'fusion-form-styles',
				],
			],
			'form_border_width'        => [
				'type'        => 'dimensions',
				'label'       => esc_html__( 'Field Border Size', 'fusion-builder' ),
				/* translators: Additional description (defaults). */
				'description' => sprintf( esc_html__( 'Controls the border size of the form fields. %s', 'Avada' ), Avada()->settings->get_default_description( 'form_border_width', [ 'top', 'right', 'bottom', 'left' ] ) ),
				'id'          => 'form_border_width',
				'value'       => [
					'top'    => '',
					'right'  => '',
					'bottom' => '',
					'left'   => '',
				],
				'default'     => Avada()->settings->get( 'form_border_width' ),
				'to_default'  => [
					'id' => 'form_border_width',
				],
				'choices'     => [
					'min'  => '0',
					'max'  => '50',
					'step' => '1',
				],
				'dependency'  => [],
				'transport'   => 'postMessage',
				'events'      => [
					'fusion-form-styles',
				],
			],
			'form_border_color'        => [
				'type'        => 'color-alpha',
				'label'       => esc_html__( 'Field Border Color', 'fusion-builder' ),
				/* translators: Additional description (defaults). */
				'description' => sprintf( esc_html__( 'Controls the border color of the form input field. %s', 'Avada' ), Avada()->settings->get_default_description( 'form_border_color' ) ),
				'id'          => 'form_border_color',
				'default'     => Avada()->settings->get( 'form_border_color' ),
				'dependency'  => [],
				'to_default'  => [
					'id' => 'form_border_color',
				],
				'transport'   => 'postMessage',
				'events'      => [
					'fusion-form-styles',
				],
			],
			'form_focus_border_color'  => [
				'label'       => esc_html__( 'Form Border Color On Focus', 'Avada' ),
				/* translators: Additional description (defaults). */
				'description' => sprintf( esc_html__( 'Controls the border color of the form input field on focus. %s', 'Avada' ), Avada()->settings->get_default_description( 'form_focus_border_color' ) ),
				'id'          => 'form_focus_border_color',
				'default'     => Avada()->settings->get( 'form_focus_border_color' ),
				'type'        => 'color-alpha',
				'to_default'  => [
					'id' => 'form_focus_border_color',
				],
				'transport'   => 'postMessage',
				'events'      => [
					'fusion-form-styles',
				],
			],
			'form_border_radius'       => [
				'type'        => 'slider',
				'label'       => esc_html__( 'Field Border Radius', 'fusion-builder' ),
				/* translators: Additional description (defaults). */
				'description' => sprintf( esc_html__( 'Controls the border radius of input field. In pixels. %s', 'Avada' ), Avada()->settings->get_default_description( 'form_border_radius' ) ),
				'id'          => 'form_border_radius',
				'default'     => Avada()->settings->get( 'form_border_radius' ),
				'dependency'  => [],
				'to_default'  => [
					'id' => 'form_border_radius',
				],
				'transport'   => 'postMessage',
				'events'      => [
					'fusion-form-styles',
				],
				'choices'     => [
					'min'  => '0',
					'max'  => '50',
					'step' => '1',
				],
			],
		],
	];
	return $sections;
}
