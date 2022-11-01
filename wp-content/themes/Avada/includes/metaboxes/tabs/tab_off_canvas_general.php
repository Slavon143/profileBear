<?php
/**
 * Off Canvas general Metabox options.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage off-canvas
 */

/**
 * Off Canvas general settings
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_page_options_tab_off_canvas_general( $sections ) {

	$sections['off_canvas_general'] = [
		'label'    => esc_html__( 'General', 'Avada' ),
		'alt_icon' => 'fusiona-general-options',
		'id'       => 'off_canvas_general',
		'fields'   => [
			'type'                => [
				'type'        => 'radio-buttonset',
				'label'       => esc_html__( 'Off Canvas Type', 'Avada' ),
				'description' => esc_attr__( 'Select Off Canvas Type. Each Type has a unique set of options.', 'Avada' ),
				'id'          => 'type',
				'default'     => 'popup',
				'choices'     => [
					'popup'       => esc_attr__( 'Popup', 'Avada' ),
					'sliding-bar' => esc_attr__( 'Sliding Bar', 'Avada' ),
				],
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-attr',
					'awb-off-canvas-styles',
				],
			],
			'off_canvas_state'    => [
				'type'        => 'radio-buttonset',
				'label'       => esc_html__( 'Default State', 'Avada' ),
				'description' => esc_attr__( 'Set the default state.', 'Avada' ),
				'id'          => 'off_canvas_state',
				'default'     => 'closed',
				'choices'     => [
					'closed' => esc_attr__( 'Closed', 'Avada' ),
					'opened' => esc_attr__( 'Open', 'Avada' ),
				],
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-attr',
				],
				'dependency'  => [
					[
						'field'      => 'type',
						'value'      => 'sliding-bar',
						'comparison' => '==',
					],
				],
			],
			'position'            => [
				'type'        => 'radio-buttonset',
				'label'       => esc_html__( 'Position', 'Avada' ),
				'description' => esc_attr__( 'Set the position of the sliding bar.', 'Avada' ),
				'id'          => 'position',
				'default'     => 'left',
				'choices'     => [
					'top'    => esc_attr__( 'Top', 'Avada' ),
					'right'  => esc_attr__( 'Right', 'Avada' ),
					'bottom' => esc_attr__( 'Bottom', 'Avada' ),
					'left'   => esc_attr__( 'Left', 'Avada' ),
				],
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-styles',
				],
				'dependency'  => [
					[
						'field'      => 'type',
						'value'      => 'sliding-bar',
						'comparison' => '==',
					],
				],
			],
			'transition'          => [
				'type'        => 'radio-buttonset',
				'label'       => esc_html__( 'Transition', 'Avada' ),
				'description' => esc_attr__( 'Set the transition of the sliding bar.', 'Avada' ),
				'id'          => 'transition',
				'default'     => 'overlap',
				'choices'     => [
					'overlap' => esc_attr__( 'Overlap', 'Avada' ),
					'push'    => esc_attr__( 'Push', 'Avada' ),
				],
				'transport'   => 'postMessage',
				'dependency'  => [
					[
						'field'      => 'type',
						'value'      => 'sliding-bar',
						'comparison' => '==',
					],
					[
						'field'      => 'position',
						'value'      => 'top',
						'comparison' => '!=',
					],
					[
						'field'      => 'position',
						'value'      => 'bottom',
						'comparison' => '!=',
					],
				],
			],
			'width'               => [
				'type'        => 'text',
				'label'       => esc_html__( 'Width', 'Avada' ),
				'description' => esc_attr__( 'Set Off Canvas width. Enter value including any valid CSS unit, ex: 800px', 'Avada' ),
				'id'          => 'width',
				'default'     => '',
				'transport'   => 'postMessage',
				'responsive'  => [
					'state' => 'large',
				],
				'events'      => [
					'awb-off-canvas-styles',
				],
			],
			'height'              => [
				'type'        => 'select',
				'label'       => esc_html__( 'Height', 'Avada' ),
				'description' => esc_attr__( 'Select Off Canvas height.', 'Avada' ),
				'id'          => 'height',
				'default'     => 'fit',
				'choices'     => [
					'fit'    => esc_attr__( 'Fit With Content', 'Avada' ),
					'full'   => esc_attr__( 'Full Height', 'Avada' ),
					'custom' => esc_attr__( 'Custom Height', 'Avada' ),
				],
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-styles',
				],
				'dependency'  => [
					[
						'field'      => 'type',
						'value'      => 'popup',
						'comparison' => '==',
					],
				],
			],
			'custom_height'       => [
				'type'        => 'text',
				'label'       => esc_html__( 'Custom Height', 'Avada' ),
				'description' => esc_attr__( 'Set a custom OFF Canvas height. Enter value including any valid CSS unit, ex: 600px.', 'Avada' ),
				'id'          => 'custom_height',
				'default'     => '',
				'transport'   => 'postMessage',
				'responsive'  => [
					'state' => 'large',
				],
				'events'      => [
					'awb-off-canvas-styles',
				],
				'dependency'  => [
					[
						'field'      => 'type',
						'value'      => 'popup',
						'comparison' => '==',
					],
					[
						'field'      => 'height',
						'value'      => 'custom',
						'comparison' => '==',
					],
				],
			],
			'horizontal_position' => [
				'type'        => 'radio-buttonset',
				'label'       => esc_html__( 'Horizontal Position', 'Avada' ),
				'description' => esc_attr__( 'Set the horizontal position. "Start" meaning left on LTR, and right on RTL sites, "End" meaning right on LTR, and left on RTL sites.', 'Avada' ),
				'id'          => 'horizontal_position',
				'default'     => 'center',
				'choices'     => [
					'flex-start' => esc_attr__( 'Start', 'Avada' ),
					'center'     => esc_attr__( 'Center', 'Avada' ),
					'flex-end'   => esc_attr__( 'End', 'Avada' ),
				],
				'dependency'  => [
					[
						'field'      => 'type',
						'value'      => 'popup',
						'comparison' => '==',
					],
				],
				'transport'   => 'postMessage',
				'responsive'  => [
					'state' => 'large',
				],
				'events'      => [
					'awb-off-canvas-styles',
				],
			],
			'vertical_position'   => [
				'type'        => 'radio-buttonset',
				'label'       => esc_html__( 'Vertical Position', 'Avada' ),
				'description' => esc_attr__( 'Select the vertical position.', 'Avada' ),
				'id'          => 'vertical_position',
				'default'     => 'center',
				'choices'     => [
					'flex-start' => esc_attr__( 'Top', 'Avada' ),
					'center'     => esc_attr__( 'Center', 'Avada' ),
					'flex-end'   => esc_attr__( 'Bottom', 'Avada' ),
				],
				'transport'   => 'postMessage',
				'responsive'  => [
					'state' => 'large',
				],
				'events'      => [
					'awb-off-canvas-styles',
				],
				'dependency'  => [
					[
						'field'      => 'type',
						'value'      => 'popup',
						'comparison' => '==',
					],
				],
			],

			'sb_height'           => [
				'type'        => 'text',
				'label'       => esc_html__( 'Height', 'Avada' ),
				'description' => esc_attr__( 'Select Off Canvas height. Enter value including any valid CSS unit, ex: 300px.', 'Avada' ),
				'id'          => 'sb_height',
				'default'     => '',
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-styles',
				],
				'dependency'  => [
					[
						'field'      => 'type',
						'value'      => 'sliding-bar',
						'comparison' => '==',
					],
					[
						'field'      => 'position',
						'value'      => 'right',
						'comparison' => '!=',
					],
					[
						'field'      => 'position',
						'value'      => 'left',
						'comparison' => '!=',
					],
				],
			],

			// flex content alignment.
			'content_layout'      => [
				'type'        => 'radio-buttonset',
				'label'       => esc_attr__( 'Content Layout', 'fusion-builder' ),
				'description' => esc_attr__( 'Choose Content Layout type. Block will not use Flex positioning, but will allow floated elements.', 'fusion-builder' ),
				'id'          => 'content_layout',
				'default'     => 'column',
				'transport'   => 'postMessage',
				'group'       => esc_attr__( 'General', 'fusion-builder' ),
				'choices'     => [
					'column' => esc_attr__( 'Column', 'fusion-builder' ),
					'row'    => esc_attr__( 'Row', 'fusion-builder' ),
					'block'  => esc_attr__( 'Block', 'fusion-builder' ),
				],
				'events'      => [
					'awb-off-canvas-styles',
				],
			],
			'align_content'       => [
				'type'        => 'radio-buttonset',
				'label'       => esc_attr__( 'Content Alignment', 'fusion-builder' ),
				'description' => esc_attr__( 'Choose how to align Content containers.', 'fusion-builder' ),
				'id'          => 'align_content',
				'default'     => 'flex-start',
				'transport'   => 'postMessage',
				'choices'     => [
					'flex-start'    => esc_attr__( 'Flex Start', 'fusion-builder' ),
					'center'        => esc_attr__( 'Center', 'fusion-builder' ),
					'flex-end'      => esc_attr__( 'Flex End', 'fusion-builder' ),
					'space-between' => esc_attr__( 'Space Between', 'fusion-builder' ),
					'space-around'  => esc_attr__( 'Space Around', 'fusion-builder' ),
					'space-evenly'  => esc_attr__( 'Space Evenly', 'fusion-builder' ),
				],
				'icons'       => [
					'flex-start'    => '<span class="fusiona-align-top-vert"></span>',
					'center'        => '<span class="fusiona-align-center-vert"></span>',
					'flex-end'      => '<span class="fusiona-align-bottom-vert"></span>',
					'space-between' => '<span class="fusiona-space-between"></span>',
					'space-around'  => '<span class="fusiona-space-around"></span>',
					'space-evenly'  => '<span class="fusiona-space-evenly"></span>',
				],
				'grid_layout' => true,
				'back_icons'  => true,
				'dependency'  => [
					[
						'field'      => 'content_layout',
						'value'      => 'block',
						'comparison' => '!=',
					],
				],
				'events'      => [
					'awb-off-canvas-styles',
				],
			],
			'valign_content'      => [
				'type'        => 'radio-buttonset',
				'label'       => esc_attr__( 'Content Vertical Alignment', 'fusion-builder' ),
				'description' => esc_attr__( 'Choose how to align Content containers vertically.', 'fusion-builder' ),
				'id'          => 'valign_content',
				'transport'   => 'postMessage',
				'default'     => 'flex-start',
				'choices'     => [
					'flex-start' => esc_attr__( 'Flex Start', 'fusion-builder' ),
					'center'     => esc_attr__( 'Center', 'fusion-builder' ),
					'flex-end'   => esc_attr__( 'Flex End', 'fusion-builder' ),
					'stretch'    => esc_attr__( 'Stretch', 'fusion-builder' ),
				],
				'icons'       => [
					'flex-start' => '<span class="fusiona-align-top-columns"></span>',
					'center'     => '<span class="fusiona-align-center-columns"></span>',
					'flex-end'   => '<span class="fusiona-align-bottom-columns"></span>',
					'stretch'    => '<span class="fusiona-full-height"></span>',
				],
				'grid_layout' => true,
				'back_icons'  => true,
				'dependency'  => [
					[
						'field'      => 'content_layout',
						'value'      => 'row',
						'comparison' => '==',
					],
				],
				'events'      => [
					'awb-off-canvas-styles',
				],
			],
			'content_wrap'        => [
				'type'        => 'radio-buttonset',
				'label'       => esc_attr__( 'Wrap Content', 'fusion-builder' ),
				'description' => esc_attr__( '"Wrap" wraps elements onto multiple rows, while "No Wrap" will force rlements onto one row.', 'fusion-builder' ),
				'id'          => 'content_wrap',
				'transport'   => 'postMessage',
				'default'     => 'wrap',
				'choices'     => [
					'wrap'   => esc_attr__( 'Wrap', 'fusion-builder' ),
					'nowrap' => esc_attr__( 'No Wrap', 'fusion-builder' ),
				],
				'dependency'  => [
					[
						'field'      => 'content_layout',
						'value'      => 'row',
						'comparison' => '==',
					],
				],
				'events'      => [
					'awb-off-canvas-styles',
				],
			],
			'css_class'           => [
				'type'        => 'text',
				'label'       => esc_html__( 'CSS Class', 'Avada' ),
				'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'Avada' ),
				'id'          => 'css_class',
				'default'     => '',
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-attr',
				],
			],
			'css_id'              => [
				'type'        => 'text',
				'label'       => esc_html__( 'CSS ID', 'Avada' ),
				'description' => esc_attr__( 'Add a unique ID to the wrapping HTML element. This ID will used in Off Canvas link.', 'Avada' ),
				'id'          => 'css_id',
				'default'     => '',
				'transport'   => 'postMessage',
				'events'      => [
					'awb-off-canvas-attr',
				],
			],

		],
	];

	return apply_filters( 'avada_off_canvas_general_sections', $sections );

}
