<?php
/**
 * Avada Builder Border Radius  Helper class.
 *
 * @package Avada-Builder
 * @since 2.2
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Avada Builder Sticky Visibility Helper class.
 *
 * @since 3.0
 */
class Fusion_Builder_Sticky_Visibility_Helper {

	/**
	 * Class constructor.
	 *
	 * @since 3.0
	 * @access public
	 */
	public function __construct() {

	}

	/**
	 * Get sticky visibility params.
	 *
	 * @static
	 * @access public
	 * @since  3.0
	 * @param  array $args The placeholder arguments.
	 * @return array
	 */
	public static function get_params( $args ) {

		return [
			[
				'type'        => 'checkbox_button_set',
				'heading'     => esc_html__( 'Element Sticky Visibility', 'fusion-builder' ),
				'description' => esc_html__( 'Choose to show or hide the element, based on the current mode (normal or sticky) of its parent container. You can choose more than one at a time.', 'fusion-builder' ),
				'param_name'  => 'sticky_display',
				'default'     => 'normal,sticky',
				'value'       => [
					'normal' => esc_attr__( 'Normal', 'fusion-builder' ),
					'sticky' => esc_attr__( 'Sticky', 'fusion-builder' ),
				],
				'icons'       => [
					'normal' => '<span class="fusiona-non-sticky"></span>',
					'sticky' => '<span class="fusiona-sticky"></span>',
				],
				'dependency'  => [
					[
						'element'  => 'fusion_builder_container',
						'param'    => 'sticky',
						'value'    => 'on',
						'operator' => '==',
					],
				],
			],
		];
	}

	/**
	 * Generates sticky visibility class.
	 *
	 * @static
	 * @access public
	 * @since  3.0
	 * @param  string $sticky_display Sticky visibility selection.
	 * @return string
	 */
	public static function get_sticky_class( $sticky_display ) {
		return '' !== $sticky_display && false === strpos( $sticky_display, ',' ) ? ' fusion-display-' . $sticky_display . '-only' : '';
	}
}
