<?php
/**
 * Shortcodes helper functions.
 *
 * @package fusion-builder
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $fusion_builder_elements, $fusion_builder_multi_elements, $fusion_builder_enabled_elements, $parallax_id;
$parallax_id = 1;

// Get builder options.
$fusion_builder_settings         = get_option( 'fusion_builder_settings' );
$fusion_builder_enabled_elements = ( isset( $fusion_builder_settings['fusion_elements'] ) ) ? $fusion_builder_settings['fusion_elements'] : '';
$fusion_builder_enabled_elements = apply_filters( 'fusion_builder_enabled_elements', $fusion_builder_enabled_elements );

// Stores an array of all registered elements.
$fusion_builder_elements = [];

// Stores an array of all advanced elements.
$fusion_builder_multi_elements = [];

/**
 * Add an element to $fusion_builder_elements array.
 *
 * @param array $module The element we're loading.
 */
function fusion_builder_map( $module ) {

	// Should only ever be run on backend, for performance reasons.
	$builder_status = false;
	if ( class_exists( 'Fusion_App' ) && function_exists( 'fusion_is_builder_frame' ) ) {
		if ( fusion_is_builder_frame() ) {
			$builder_status = true;
		}
	}

	global $fusion_builder_elements, $fusion_builder_enabled_elements, $fusion_builder_multi_elements, $all_fusion_builder_elements, $fusion_settings, $pagenow;
	$fusion_settings = awb_get_fusion_settings();

	$module          = apply_filters( 'fusion_builder_map', $module );
	$shortcode       = $module['shortcode'];
	$ignored_atts    = [];
	$responsive_atts = [];
	$params          = [];

	if ( ( is_admin() && isset( $pagenow ) && ( 'admin.php' === $pagenow && isset( $_GET['page'] ) && 'avada-builder-options' === $_GET['page'] ) || ( 'post.php' === $pagenow ) || ( 'post-new.php' === $pagenow ) ) || $builder_status ) { // phpcs:ignore WordPress.Security.NonceVerification

		// Should only ever be run on backend, for performance reasons.
		if ( isset( $module['params'] ) ) {

			$module['params'] = apply_filters( 'fusion_builder_element_params', $module['params'], $shortcode );

			// Create an array of descriptions.
			foreach ( $module['params'] as $key => $param ) {

				if ( empty( $param ) ) {
					continue;
				}

				// Check if this element has an ajax callback.
				if ( isset( $param['callback'] ) ) {
					if ( isset( $param['callback']['ajax'] ) && false !== $param['callback']['ajax'] ) {
						$module['has_ajax'][ $param['param_name'] ]['function'] = $param['callback']['function'];

						// TODO: check what the default action should be if none set.
						$module['has_ajax'][ $param['param_name'] ]['action']     = isset( $param['callback']['action'] ) ? $param['callback']['action'] : false;
						$module['has_ajax'][ $param['param_name'] ]['param_name'] = $param['param_name'];
					}
				}

				// Responsive options.
				if ( isset( $param['responsive'] ) && is_array( $param['responsive'] ) ) { // phpcs:ignore WordPress.PHP.StrictComparisons
					$responsive_atts[] = [
						'name'        => $param['param_name'],
						'description' => $param['description'],
						'args'        => $param['responsive'],
					];
				}

				// Allow filtering of description.
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
					$options_label = apply_filters( 'fusion_options_label', esc_html__( 'Global Options', 'fusion-builder' ) );
					if ( 'hide_on_mobile' === $param['param_name'] ) {
						$link = '<a href="' . $fusion_settings->get_setting_link( 'visibility_small' ) . '" target="_blank" rel="noopener noreferrer">' . $options_label . '</a>';
						/* translators: Link with the "Element Options" text. */
						$param['description'] = $param['description'] . sprintf( __( '  Each of the 3 sizes has a custom width setting on the Responsive tab in the %s.', 'fusion-builder' ), $link );
					}

					if ( 'element_content' === $param['param_name'] && ( 'fusion_syntax_highlighter' === $shortcode || 'fusion_code' === $shortcode ) ) {
						$code_block_option = ( $fusion_settings->get( 'disable_code_block_encoding' ) ) ? 'On' : 'Off';
						$link              = '<a href="' . $fusion_settings->get_setting_link( 'disable_code_block_encoding' ) . '" target="_blank" rel="noopener noreferrer">' . $code_block_option . '</a>';
						/* translators: Import note for code block description. */
						$param['description'] = $param['description'] . '<br/>' . sprintf( __( 'IMPORTANT: Please make sure that the "Code Block Encoding" setting in %1$s is enabled in order for the code to appear correctly on the frontend. Currently set to %2$s.', 'fusion-builder' ), $options_label, $link );
					}

					$param['description'] = apply_filters( 'fusion_builder_option_description', $param['description'] . $dynamic_description, $shortcode, $param['param_name'] );
				}

				// Allow filtering of default.
				$current_default = ( isset( $param['default'] ) ) ? $param['default'] : '';
				$new_default     = apply_filters( 'fusion_builder_option_default', $current_default, $shortcode, $param['param_name'] );
				if ( '' !== $new_default ) {
					$param['default'] = $new_default;
				}

				// Allow filtering of value.
				$current_value = ( isset( $param['value'] ) ) ? $param['value'] : '';
				$new_value     = apply_filters( 'fusion_builder_option_value', $current_value, $shortcode, $param['param_name'] );
				if ( '' !== $new_value ) {
					$param['value'] = $new_value;
				}

				// Allow filtering of dependency.
				$current_dependency = ( isset( $param['dependency'] ) ) ? $param['dependency'] : [];
				$current_dependency = fusion_builder_element_dependencies( $current_dependency, $shortcode, $param['param_name'] );
				$new_dependency     = apply_filters( 'fusion_builder_option_dependency', $current_dependency, $shortcode, $param['param_name'] );
				if ( '' !== $new_dependency ) {
					$param['dependency'] = $new_dependency;
				}

				// Allow filtering of param dynamic_data.
				$dynamic_data          = isset( $param['dynamic_data'] ) ? $param['dynamic_data'] : false;
				$param['dynamic_data'] = apply_filters( 'fusion_builder_param_dynamic_data', $dynamic_data, $shortcode, $param );

				// Ignore attributes in the shortcode if 'remove_from_atts' is true.
				if ( isset( $param['remove_from_atts'] ) && true == $param['remove_from_atts'] ) { // phpcs:ignore WordPress.PHP.StrictComparisons
					$ignored_atts[] = $param['param_name'];
				}

				// Set param key as param_name.
				$params[ $param['param_name'] ] = $param;
			}
			if ( '0' === $fusion_settings->get( 'dependencies_status' ) ) {
				foreach ( $params as $key => $value ) {
					if ( isset( $params[ $key ]['dependency'] ) && ! empty( $params[ $key ]['dependency'] ) ) {
						unset( $params[ $key ]['dependency'] );
					}
				}
			}

			if ( 0 < count( $responsive_atts ) ) {
				$params                   = apply_filters( 'fusion_builder_responsive_params', $responsive_atts, $params, $shortcode );
				$module['has_responsive'] = true;
			}

			$module['params']           = $params;
			$module['remove_from_atts'] = $ignored_atts;
		}
	}

	// Create array of unfiltered elements.
	$all_fusion_builder_elements[ $shortcode ] = $module;

	// Add multi element to an array.
	if ( isset( $module['multi'] ) && 'multi_element_parent' === $module['multi'] && isset( $module['element_child'] ) ) {
		$fusion_builder_multi_elements[ $shortcode ] = $module['element_child'];
	}
	// Remove Avada Slider element if disabled from Global Options.
	if ( 'fusion_fusionslider' === $shortcode && ! $fusion_settings->get( 'status_fusion_slider' ) ) {
		unset( $all_fusion_builder_elements[ $shortcode ] );
	}

	// Remove Fusion Form element if disabled from theme optoins.
	if ( ! empty( $module['form_component'] ) && false === Fusion_Form_Builder::is_enabled() ) {
		unset( $all_fusion_builder_elements[ $shortcode ] );
	}

	// Remove Avada Portfolio element if disabled from Global Options.
	if ( 'fusion_portfolio' === $shortcode && ! $fusion_settings->get( 'status_fusion_portfolio' ) ) {
		unset( $all_fusion_builder_elements[ $shortcode ] );
	}

	// Remove Avada FAQs element if disabled from Global Options.
	if ( 'fusion_faq' === $shortcode && ! $fusion_settings->get( 'status_fusion_faqs' ) ) {
		unset( $all_fusion_builder_elements[ $shortcode ] );
	}
}

/**
 * Find registered third party shortcodes.
 *
 * @since 2.0
 * @param array $fusion_shortcodes An array of shortcodes.
 * @return array
 */
function fusion_get_vendor_shortcodes( $fusion_shortcodes ) {
	global $shortcode_tags;
	$vendor_shortcodes     = [];
	$additional_shortcodes = [
		'fusion_flexslider',
		'fusion_global',
		'fusion_old_tab',
		'fusion_old_tabs',
		'fusion_pricing_footer',
		'fusion_pricing_price',
		'fusion_pricing_row',
	];

	foreach ( $shortcode_tags as $tag => $data ) {
		if ( ! array_key_exists( $tag, $fusion_shortcodes ) && ! in_array( $tag, $additional_shortcodes, true ) ) {
			$vendor_shortcodes[ $tag ] = $tag;
		}
	}
	return $vendor_shortcodes;
}

/**
 * Filter available elements with enabled elements
 */
function fusion_builder_filter_available_elements() {
	global $fusion_builder_enabled_elements, $all_fusion_builder_elements, $fusion_builder_multi_elements;

	// If settings page was not saved, all elements are enabled.
	if ( '' === $fusion_builder_enabled_elements ) {
		$fusion_builder_enabled_elements = array_keys( $all_fusion_builder_elements );
	} else {
		// Add required shortcodes to enabled elements array.
		$fusion_builder_enabled_elements[] = 'fusion_builder_container';
		$fusion_builder_enabled_elements[] = 'fusion_builder_row';
		$fusion_builder_enabled_elements[] = 'fusion_builder_row_inner';
		$fusion_builder_enabled_elements[] = 'fusion_builder_column_inner';
		$fusion_builder_enabled_elements[] = 'fusion_builder_column';
		$fusion_builder_enabled_elements[] = 'fusion_builder_blank_page';
		$fusion_builder_enabled_elements[] = 'fusion_builder_next_page';
		$fusion_builder_enabled_elements[] = 'fusion_builder_inline';

		if ( class_exists( 'WooCommerce' ) ) {
			$fusion_builder_enabled_elements[] = 'fusion_woo_checkout_form';
		}
	}

	foreach ( $all_fusion_builder_elements as $module ) {
		// Get shortcode name.
		$shortcode = $module['shortcode'];

		// Check if its a multi element child.
		$multi_parent = array_search( $shortcode, $fusion_builder_multi_elements, true );

		if ( $multi_parent ) {
			if ( in_array( $multi_parent, $fusion_builder_enabled_elements, true ) ) {
				$fusion_builder_enabled_elements[] = $shortcode;
			}
		}

		// Add available elements to an array.
		if ( in_array( $shortcode, $fusion_builder_enabled_elements, true ) ) {

			$fusion_builder_elements[ $shortcode ] = $module;

		} else {
			// If parent shortcode is removed, also make sure to remove child shortcode.
			if ( isset( $module['multi'] ) && 'multi_element_parent' === $module['multi'] && isset( $module['element_child'] ) ) {

				remove_shortcode( $module['element_child'] );

			}

			remove_shortcode( $shortcode );
		}
	}

	return $fusion_builder_elements;

}
