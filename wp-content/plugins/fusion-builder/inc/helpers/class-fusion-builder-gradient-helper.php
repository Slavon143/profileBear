<?php
/**
 * Avada Builder Gradient Helper class.
 *
 * @package Avada-Builder
 * @since 2.2
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Avada Builder Gradient Helper class.
 *
 * @since 2.2
 */
class Fusion_Builder_Gradient_Helper {

	/**
	 * Class constructor.
	 *
	 * @since 2.2
	 * @access public
	 */
	public function __construct() {

	}

	/**
	 * Get gradient params.
	 *
	 * @since 2.2
	 * @access public
	 * @param array $args The placeholder arguments.
	 * @return array
	 */
	public static function get_params( $args ) {
		$fusion_settings = awb_get_fusion_settings();
		$selector        = isset( $args['selector'] ) ? $args['selector'] : '';
		$defaults        = isset( $args['defaults'] ) ? $args['defaults'] : '';

		return [
			[
				'type'        => 'colorpickeralpha',
				'heading'     => esc_attr__( 'Gradient Start Color', 'fusion-builder' ),
				'param_name'  => 'gradient_start_color',
				'default'     => ! empty( $defaults ) ? $fusion_settings->get( 'full_width_gradient_start_color' ) : '',
				'description' => esc_attr__( 'Select start color for gradient.', 'fusion-builder' ),
				'group'       => esc_attr__( 'Background', 'fusion-builder' ),
				'subgroup'    => [
					'name' => 'background_type',
					'tab'  => 'gradient',
				],
				'callback'    => [
					'function' => 'fusion_update_gradient_style',
					'args'     => [
						'selector' => $selector,
					],
				],
			],
			[
				'type'        => 'colorpickeralpha',
				'heading'     => esc_attr__( 'Gradient End Color', 'fusion-builder' ),
				'param_name'  => 'gradient_end_color',
				'default'     => ! empty( $defaults ) ? $fusion_settings->get( 'full_width_gradient_end_color' ) : '',
				'description' => esc_attr__( 'Select end color for gradient.', 'fusion-builder' ),
				'group'       => esc_attr__( 'Background', 'fusion-builder' ),
				'subgroup'    => [
					'name' => 'background_type',
					'tab'  => 'gradient',
				],
				'callback'    => [
					'function' => 'fusion_update_gradient_style',
					'args'     => [
						'selector' => $selector,
					],
				],
			],
			[
				'type'        => 'range',
				'heading'     => esc_attr__( 'Gradient Start Position', 'fusion-builder' ),
				'description' => esc_attr__( 'Select start position for gradient.', 'fusion-builder' ),
				'param_name'  => 'gradient_start_position',
				'value'       => '0',
				'min'         => '0',
				'max'         => '100',
				'group'       => esc_attr__( 'Background', 'fusion-builder' ),
				'subgroup'    => [
					'name' => 'background_type',
					'tab'  => 'gradient',
				],
				'callback'    => [
					'function' => 'fusion_update_gradient_style',
					'args'     => [
						'selector' => $selector,
					],
				],
			],
			[
				'type'        => 'range',
				'heading'     => esc_attr__( 'Gradient End Position', 'fusion-builder' ),
				'description' => esc_attr__( 'Select end position for gradient.', 'fusion-builder' ),
				'param_name'  => 'gradient_end_position',
				'value'       => '100',
				'min'         => '0',
				'max'         => '100',
				'group'       => esc_attr__( 'Background', 'fusion-builder' ),
				'subgroup'    => [
					'name' => 'background_type',
					'tab'  => 'gradient',
				],
				'callback'    => [
					'function' => 'fusion_update_gradient_style',
					'args'     => [
						'selector' => $selector,
					],
				],
			],
			[
				'type'        => 'radio_button_set',
				'heading'     => esc_attr__( 'Gradient Type', 'fusion-builder' ),
				'description' => esc_attr__( 'Controls gradient type.', 'fusion-builder' ),
				'param_name'  => 'gradient_type',
				'default'     => 'linear',
				'group'       => esc_attr__( 'Background', 'fusion-builder' ),
				'subgroup'    => [
					'name' => 'background_type',
					'tab'  => 'gradient',
				],
				'callback'    => [
					'function' => 'fusion_update_gradient_style',
					'args'     => [
						'selector' => $selector,
					],
				],
				'value'       => [
					'linear' => esc_attr__( 'Linear', 'fusion-builder' ),
					'radial' => esc_attr__( 'Radial', 'fusion-builder' ),
				],
			],
			[
				'type'        => 'select',
				'heading'     => esc_attr__( 'Radial Direction', 'fusion-builder' ),
				'description' => esc_attr__( 'Select direction for radial gradient.', 'fusion-builder' ),
				'param_name'  => 'radial_direction',
				'default'     => 'center center',
				'group'       => esc_attr__( 'Background', 'fusion-builder' ),
				'subgroup'    => [
					'name' => 'background_type',
					'tab'  => 'gradient',
				],
				'value'       => [
					'left top'      => esc_attr__( 'Left Top', 'fusion-builder' ),
					'left center'   => esc_attr__( 'Left Center', 'fusion-builder' ),
					'left bottom'   => esc_attr__( 'Left Bottom', 'fusion-builder' ),
					'right top'     => esc_attr__( 'Right Top', 'fusion-builder' ),
					'right center'  => esc_attr__( 'Right Center', 'fusion-builder' ),
					'right bottom'  => esc_attr__( 'Right Bottom', 'fusion-builder' ),
					'center top'    => esc_attr__( 'Center Top', 'fusion-builder' ),
					'center center' => esc_attr__( 'Center Center', 'fusion-builder' ),
					'center bottom' => esc_attr__( 'Center Bottom', 'fusion-builder' ),
				],
				'dependency'  => [
					[
						'element'  => 'gradient_type',
						'value'    => 'radial',
						'operator' => '==',
					],
				],
				'callback'    => [
					'function' => 'fusion_update_gradient_style',
					'args'     => [
						'selector' => $selector,
					],
				],
			],
			[
				'type'        => 'range',
				'heading'     => esc_attr__( 'Gradient Angle', 'fusion-builder' ),
				'description' => esc_attr__( 'Controls the gradient angle. In degrees.', 'fusion-builder' ),
				'param_name'  => 'linear_angle',
				'value'       => '180',
				'min'         => '0',
				'max'         => '360',
				'group'       => esc_attr__( 'Background', 'fusion-builder' ),
				'subgroup'    => [
					'name' => 'background_type',
					'tab'  => 'gradient',
				],
				'dependency'  => [
					[
						'element'  => 'gradient_type',
						'value'    => 'linear',
						'operator' => '==',
					],
				],
				'callback'    => [
					'function' => 'fusion_update_gradient_style',
					'args'     => [
						'selector' => $selector,
					],
				],
			],
		];
	}

	/**
	 * Get gradient params.
	 *
	 * @since 2.2
	 * @access public
	 * @param array $args The placeholder arguments.
	 * @return array
	 */
	public static function get_text_params( $args ) {
		$fusion_settings = awb_get_fusion_settings();
		$selector        = isset( $args['selector'] ) ? $args['selector'] : '';
		$group           = isset( $args['group'] ) ? $args['group'] : esc_attr__( 'Design', 'fusion-builder' );

		$start_dependency = [];
		$main_dependency  = [
			[
				'element'  => 'gradient_font',
				'value'    => 'yes',
				'operator' => '==',
			],
		];

		$radial_dependency = [
			[
				'element'  => 'gradient_type',
				'value'    => 'radial',
				'operator' => '==',
			],
			[
				'element'  => 'gradient_font',
				'value'    => 'yes',
				'operator' => '==',
			],
		];

		$linear_dependency = [
			[
				'element'  => 'gradient_type',
				'value'    => 'linear',
				'operator' => '==',
			],
			[
				'element'  => 'gradient_font',
				'value'    => 'yes',
				'operator' => '==',
			],
		];
		if ( isset( $args['dependency'] ) && is_array( $args['dependency'] ) ) {
			foreach ( $args['dependency'] as $additional_dependency ) {
				$start_dependency[]  = $additional_dependency;
				$main_dependency[]   = $additional_dependency;
				$radial_dependency[] = $additional_dependency;
				$linear_dependency[] = $additional_dependency;
			}
		}

		return [
			[
				'type'        => 'radio_button_set',
				'heading'     => esc_attr__( 'Gradient Font Color', 'fusion-builder' ),
				'description' => esc_attr__( 'Set to "Yes" to enable gradient font color.', 'fusion-builder' ),
				'param_name'  => 'gradient_font',
				'default'     => 'no',
				'group'       => $group,
				'value'       => [
					'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
					'no'  => esc_attr__( 'No', 'fusion-builder' ),
				],
				'dependency'  => $start_dependency,
			],
			[
				'type'        => 'colorpickeralpha',
				'heading'     => esc_attr__( 'Gradient Start Color', 'fusion-builder' ),
				'param_name'  => 'gradient_start_color',
				'default'     => 'rgba(0,0,0,0)',
				'description' => esc_attr__( 'Select start color for gradient.', 'fusion-builder' ),
				'group'       => $group,
				'dependency'  => $main_dependency,
			],
			[
				'type'        => 'colorpickeralpha',
				'heading'     => esc_attr__( 'Gradient End Color', 'fusion-builder' ),
				'param_name'  => 'gradient_end_color',
				'default'     => 'rgba(0,0,0,0)',
				'description' => esc_attr__( 'Select end color for gradient.', 'fusion-builder' ),
				'group'       => $group,
				'dependency'  => $main_dependency,
			],
			[
				'type'        => 'range',
				'heading'     => esc_attr__( 'Gradient Start Position', 'fusion-builder' ),
				'description' => esc_attr__( 'Select start position for gradient.', 'fusion-builder' ),
				'param_name'  => 'gradient_start_position',
				'value'       => '0',
				'min'         => '0',
				'max'         => '100',
				'group'       => $group,
				'dependency'  => $main_dependency,
			],
			[
				'type'        => 'range',
				'heading'     => esc_attr__( 'Gradient End Position', 'fusion-builder' ),
				'description' => esc_attr__( 'Select end position for gradient.', 'fusion-builder' ),
				'param_name'  => 'gradient_end_position',
				'value'       => '100',
				'min'         => '0',
				'max'         => '100',
				'group'       => $group,
				'dependency'  => $main_dependency,
			],
			[
				'type'        => 'radio_button_set',
				'heading'     => esc_attr__( 'Gradient Type', 'fusion-builder' ),
				'description' => esc_attr__( 'Controls gradient type.', 'fusion-builder' ),
				'param_name'  => 'gradient_type',
				'default'     => 'linear',
				'group'       => $group,
				'value'       => [
					'linear' => esc_attr__( 'Linear', 'fusion-builder' ),
					'radial' => esc_attr__( 'Radial', 'fusion-builder' ),
				],
				'dependency'  => $main_dependency,
			],
			[
				'type'        => 'select',
				'heading'     => esc_attr__( 'Radial Direction', 'fusion-builder' ),
				'description' => esc_attr__( 'Select direction for radial gradient.', 'fusion-builder' ),
				'param_name'  => 'radial_direction',
				'default'     => 'center center',
				'group'       => $group,
				'value'       => [
					'left top'      => esc_attr__( 'Left Top', 'fusion-builder' ),
					'left center'   => esc_attr__( 'Left Center', 'fusion-builder' ),
					'left bottom'   => esc_attr__( 'Left Bottom', 'fusion-builder' ),
					'right top'     => esc_attr__( 'Right Top', 'fusion-builder' ),
					'right center'  => esc_attr__( 'Right Center', 'fusion-builder' ),
					'right bottom'  => esc_attr__( 'Right Bottom', 'fusion-builder' ),
					'center top'    => esc_attr__( 'Center Top', 'fusion-builder' ),
					'center center' => esc_attr__( 'Center Center', 'fusion-builder' ),
					'center bottom' => esc_attr__( 'Center Bottom', 'fusion-builder' ),
				],
				'dependency'  => $radial_dependency,
			],
			[
				'type'        => 'range',
				'heading'     => esc_attr__( 'Gradient Angle', 'fusion-builder' ),
				'description' => esc_attr__( 'Controls the gradient angle. In degrees.', 'fusion-builder' ),
				'param_name'  => 'linear_angle',
				'value'       => '180',
				'min'         => '0',
				'max'         => '360',
				'group'       => $group,
				'dependency'  => $linear_dependency,
			],
		];
	}

	/**
	 * Generate gradient string.
	 *
	 * @since 2.2
	 * @param array  $args The parameters for the option.
	 * @param string $type The section type for which gradient string is required.
	 * @return string
	 */
	public static function get_gradient_string( $args, $type = '' ) {
		$fusion_settings = awb_get_fusion_settings();

		if ( ! isset( $args['lazy_load'] ) ) {
			$lazy_load = 'avada' === $fusion_settings->get( 'lazy_load' ) && ! is_feed() ? true : false;
		} else {
			$lazy_load = $args['lazy_load'];
		}

		$lazy_load = ! $args['background_image'] || '' === $args['background_image'] ? false : $lazy_load;
		$style     = '';

		if ( ! empty( $args['gradient_start_color'] ) || ! empty( $args['gradient_end_color'] ) ) {
			if ( 'linear' === $args['gradient_type'] ) {
				$style .= 'linear-gradient(' . $args['linear_angle'] . 'deg, ';
			} elseif ( 'radial' === $args['gradient_type'] ) {
				$style .= 'radial-gradient(circle at ' . $args['radial_direction'] . ', ';
			}

			$style .= ( ! empty( $args['gradient_start_color'] ) ? $args['gradient_start_color'] : 'rgba(255,255,255,0)' ) . ' ' . $args['gradient_start_position'] . '%,';
			$style .= ( ! empty( $args['gradient_end_color'] ) ? $args['gradient_end_color'] : 'rgba(255,255,255,0)' ) . ' ' . $args['gradient_end_position'] . '%)';

			switch ( $type ) {
				case 'main_bg':
				case 'parallax':
					if ( ! empty( $args['background_image'] ) && 'yes' !== $args['fade'] && ! $lazy_load ) {
						$style .= ',url(' . esc_url_raw( $args['background_image'] ) . ');';
					} else {
						$style .= ';';
					}
					break;
				case 'fade':
					if ( ! empty( $args['background_image'] ) && ! $lazy_load ) {
						$style .= ',url(' . esc_url_raw( $args['background_image'] ) . ');';
					} else {
						$style .= ';';
					}
					break;
				case 'column':
					if ( ! empty( $args['background_image'] ) && ! $lazy_load ) {
						$style .= ',url(' . esc_url_raw( $args['background_image'] ) . ');';
					} else {
						$style .= ';';
					}
					break;
			}
		}

		return $style;
	}

	/**
	 * Generate gradient string for font coloring not background..
	 *
	 * @since 2.2
	 * @param array $args The parameters for the option.
	 * @return string
	 */
	public static function get_gradient_font_string( $args ) {
		$style = '';
		if ( ! empty( $args['gradient_start_color'] ) || ! empty( $args['gradient_end_color'] ) ) {
			$gradient_start = ! empty( $args['gradient_start_color'] ) ? $args['gradient_start_color'] : 'rgba(255,255,255,0)';
			$gradient_end   = ! empty( $args['gradient_end_color'] ) ? $args['gradient_end_color'] : 'rgba(255,255,255,0)';

			// Add fallback.
			$style .= 'background-color:' . $gradient_start . ';';

			// Check for type and set accordingly.
			$style .= 'background-image:';
			if ( 'linear' === $args['gradient_type'] ) {
				$style .= 'linear-gradient(' . $args['linear_angle'] . 'deg, ';
			} elseif ( 'radial' === $args['gradient_type'] ) {
				$style .= 'radial-gradient(circle at ' . $args['radial_direction'] . ', ';
			}
			$style .= $gradient_start . ' ' . $args['gradient_start_position'] . '%,';
			$style .= $gradient_end . ' ' . $args['gradient_end_position'] . '%);';
		}

		return $style;
	}

}
