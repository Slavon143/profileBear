<?php
/**
 * Avada Builder Element Helper class.
 *
 * @package Avada-Builder
 * @since 2.1
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Avada Builder Element Helper class.
 *
 * @since 2.1
 */
class Fusion_Builder_Element_Helper {

	/**
	 * Class constructor.
	 *
	 * @since 2.1
	 * @access public
	 */
	public function __construct() {

	}

	/**
	 * Replace placeholders with params.
	 *
	 * @since 2.1
	 * @access public
	 * @param array  $params Element params.
	 * @param string $shortcode Shortcode handle.
	 * @return array
	 */
	public static function placeholders_to_params( $params, $shortcode ) {

		// placeholder => callback.
		$placeholders_to_params = [
			'fusion_animation_placeholder'           => 'Fusion_Builder_Animation_Helper::get_params',
			'fusion_filter_placeholder'              => 'Fusion_Builder_Filter_Helper::get_params',
			'fusion_border_radius_placeholder'       => 'Fusion_Builder_Border_Radius_Helper::get_params',
			'fusion_gradient_placeholder'            => 'Fusion_Builder_Gradient_Helper::get_params',
			'fusion_pattern_placeholder'             => 'Fusion_Builder_Pattern_Helper::get_params',
			'fusion_mask_placeholder'                => 'Fusion_Builder_Mask_Helper::get_params',
			'fusion_gradient_text_placeholder'       => 'Fusion_Builder_Gradient_Helper::get_text_params',
			'fusion_margin_placeholder'              => 'Fusion_Builder_Margin_Helper::get_params',
			'fusion_margin_mobile_placeholder'       => 'Fusion_Builder_Margin_Helper::get_params',
			'fusion_box_shadow_placeholder'          => 'Fusion_Builder_Box_Shadow_Helper::get_params',
			'fusion_box_shadow_no_inner_placeholder' => 'Fusion_Builder_Box_Shadow_Helper::get_no_inner_params',
			'fusion_text_shadow_placeholder'         => 'Fusion_Builder_Text_Shadow_Helper::get_params',
			'fusion_sticky_visibility_placeholder'   => 'Fusion_Builder_Sticky_Visibility_Helper::get_params',
			'fusion_form_logics_placeholder'         => 'Fusion_Builder_Form_Logics_Helper::get_params',
			'fusion_conditional_render_placeholder'  => 'Fusion_Builder_Conditional_Render_Helper::get_params',
			'fusion_transform_placeholder'           => 'Fusion_Builder_Transform_Helper::get_params',
		];

		foreach ( $placeholders_to_params as $placeholder => $param_callback ) {
			if ( isset( $params[ $placeholder ] ) ) {

				$placeholder_args              = is_array( $params[ $placeholder ] ) ? $params[ $placeholder ] : [ $params[ $placeholder ] ];
				$placeholder_args['shortcode'] = $shortcode;

				// Get placeholder element position.
				$params_keys = array_keys( $params );
				$position    = array_search( $placeholder, $params_keys, true );

				// Unset placeholder element as we don't need it anymore.
				unset( $params[ $placeholder ] );

				// Insert params.
				$param_callback = false !== strpos( $param_callback, '::' ) ? $param_callback : 'Fusion_Builder_Element_Helper::' . $param_callback;
				if ( is_callable( $param_callback ) ) {
					array_splice( $params, $position, 0, call_user_func_array( $param_callback, [ $placeholder_args ] ) );
				}
			}
		}

		return $params;
	}

	/**
	 * Adds responsive params.
	 *
	 * @since 3.0
	 * @access public
	 * @param array  $responsive_atts Element responsive attributes.
	 * @param array  $params          Element params.
	 * @param string $shortcode       Shortcode handle.
	 * @return array
	 */
	public static function add_responsive_params( $responsive_atts, $params, $shortcode ) {

		$fusion_settings = awb_get_fusion_settings();

		foreach ( $responsive_atts as $att ) {
			$position          = array_search( $att['name'], array_keys( $params ), true );
			$states            = isset( $att['args']['additional_states'] ) ? $att['args']['additional_states'] : [ 'medium', 'small' ];
			$responsive_params = [];

			foreach ( $states as $state ) {
				$param                        = $params[ $att['name'] ];
				$param['param_name']          = $att['name'] . '_' . $state;
				$param['description']         = $att['description'];
				$param['default_option']      = false;
				$param['responsive']['state'] = $state;
				$param                        = self::add_responsive_values_data( $param, $state );

				// Add relative description.
				if ( isset( $param['description'] ) ) {

					$builder_map         = fusion_builder_map_descriptions( $shortcode, $param['param_name'] );
					$dynamic_description = '';

					if ( is_array( $builder_map ) ) {
						$setting             = ( isset( $builder_map['theme-option'] ) && '' !== $builder_map['theme-option'] ) ? $builder_map['theme-option'] : '';
						$subset              = ( isset( $builder_map['subset'] ) && '' !== $builder_map['subset'] ) ? $builder_map['subset'] : '';
						$type                = ( isset( $builder_map['type'] ) && '' !== $builder_map['type'] ) ? $builder_map['type'] : '';
						$reset               = ( ( isset( $builder_map['reset'] ) || 'range' === $type ) && '' !== $param['default'] ) ? $param['param_name'] : '';
						$check_page          = isset( $builder_map['check_page'] ) ? $builder_map['check_page'] : false;
						$dynamic_description = $fusion_settings->get_default_description( $setting, $subset, $type, $reset, $param, $check_page );
						$dynamic_description = apply_filters( 'fusion_builder_option_dynamic_description', $dynamic_description, $shortcode, $param['param_name'] );

						$param['default_option'] = $setting;
						$param['default_subset'] = $subset;
						$param['option_map']     = $type;
					}

					if ( '' !== $dynamic_description ) {
						$param['description'] = apply_filters( 'fusion_builder_option_description', $att['description'] . $dynamic_description, $shortcode, $param['param_name'] );
					}
				}

				if ( isset( $att['args']['default_value'] ) && true === $att['args']['default_value'] ) {
					$param['value']   = [ '' => 'Default' ] + $param['value'];
					$param['default'] = '';
				}

				if ( isset( $att['args']['defaults'][ $state ] ) ) {
					$param['default'] = $att['args']['defaults'][ $state ];
				}

				if ( isset( $att['args']['values'][ $state ] ) ) {
					$param['value'] = $att['args']['values'][ $state ];
				}

				if ( isset( $att['args']['descriptions'][ $state ] ) ) {
					$param['description'] = $att['args']['descriptions'][ $state ];
				}

				$responsive_params[ $param['param_name'] ] = $param;
			}

			$position_2 = $position;

			if ( isset( $att['args']['exclude_main_state'] ) && true === $att['args']['exclude_main_state'] ) {
				$position_2 = $position + 1;
			}

			// Insert responsive params.
			$params = array_merge( array_slice( $params, 0, $position ), $responsive_params, array_slice( $params, $position_2 ) );
		}

		return $params;
	}

	/**
	 * Adds responsive values data.
	 *
	 * @since 3.0
	 * @access public
	 * @param array  $param Element params.
	 * @param string $state Responsive state.
	 * @return array
	 */
	public static function add_responsive_values_data( $param, $state ) {

		if ( isset( $param['type'] ) && isset( $param['value'] ) ) {
			switch ( $param['type'] ) {
				case 'dimension':
					foreach ( $param['value'] as $key => $value ) {
						$param['value'][ $key . '_' . $state ] = $value;
						unset( $param['value'][ $key ] );
					}
					break;
			}
		}

		return $param;
	}

	/**
	 * Get font family attributes.
	 *
	 * @since 2.2
	 * @access public
	 * @param array  $params Element params.
	 * @param string $param Font family param name.
	 * @param string $format Format of returned value, string or array.
	 * @param bool   $important Add !important to css props. only for string format.
	 * @return mixed
	 */
	public static function get_font_styling( $params, $param = 'font_family', $format = 'string', $important = false ) {
		$style = [];

		if ( '' !== $params[ 'fusion_font_family_' . $param ] ) {
			if ( false !== strpos( $params[ 'fusion_font_family_' . $param ], 'var(' ) ) {
				$style['font-family'] = $params[ 'fusion_font_family_' . $param ];
				if ( function_exists( 'AWB_Global_Typography' ) ) {
					$style['font-weight'] = AWB_Global_Typography()->get_var_string( $style['font-family'], 'font-weight' );
					$style['font-style']  = AWB_Global_Typography()->get_var_string( $style['font-family'], 'font-style' );
				}
			} elseif ( false !== strpos( $params[ 'fusion_font_family_' . $param ], '\'' ) || 'inherit' === $params[ 'fusion_font_family_' . $param ] || false !== strpos( $params[ 'fusion_font_family_' . $param ], ',' ) || false !== strpos( $params[ 'fusion_font_family_' . $param ], 'var(' ) ) {
				$style['font-family'] = $params[ 'fusion_font_family_' . $param ];
			} else {
				$style['font-family'] = '"' . $params[ 'fusion_font_family_' . $param ] . '"';
			}

			if ( '' !== $params[ 'fusion_font_variant_' . $param ] && ! isset( $style['font-weight'] ) ) {
				$weight = str_replace( 'italic', '', $params[ 'fusion_font_variant_' . $param ] );
				if ( $weight !== $params[ 'fusion_font_variant_' . $param ] ) {
					$style['font-style'] = 'italic';
				}
				if ( '' !== $weight ) {
					$style['font-weight'] = $weight;
				}
			}
		}

		if ( 'string' === $format ) {
			$style_str = '';
			$important = $important ? ' !important' : '';

			foreach ( $style as $key => $value ) {
				$style_str .= $key . ':' . $value . $important . ';';
			}

			return $style_str;
		}

		return $style;
	}

}

// Add replacement filter.
add_filter( 'fusion_builder_element_params', 'Fusion_Builder_Element_Helper::placeholders_to_params', 10, 2 );

// Add responsive filter.
add_filter( 'fusion_builder_responsive_params', 'Fusion_Builder_Element_Helper::add_responsive_params', 10, 3 );
