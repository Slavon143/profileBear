<?php
/**
 * Avada Builder helper functions.
 *
 * @package fusion-builder
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Update the $fusion_element_rendering_elements global
 *
 * @since 3.0
 * @param bool $new_state The state as a boolean.
 * @return bool
 */
function fusion_element_rendering_elements( $new_state = null ) {
	global $fusion_element_rendering_elements;

	if ( ! isset( $fusion_element_rendering_elements ) ) {
		$fusion_element_rendering_elements = false;
	}

	if ( false === $new_state || true === $new_state ) {
		$fusion_element_rendering_elements = $new_state;
	}

	return $fusion_element_rendering_elements;
}

/**
 * Returns class instance of active column type.
 *
 * @access public
 * @since 3.0
 * @return Object
 */
function fusion_get_parent_column() {
	$nested_column = fusion_builder_column_inner();
	if ( $nested_column->active_parent ) {
		return $nested_column;
	}
	return fusion_builder_column();
}

/**
 * Check if flex behavior is enabled.
 *
 * @access public
 * @since 3.0
 * @return array
 */
function fusion_element_rendering_is_flex() {
	return fusion_builder_container()->is_flex() && ! fusion_element_rendering_elements() && fusion_get_parent_column()->is_flex_rendering();
}

/**
 * Fix shortcode content when floated. Adds clearfix.
 *
 * @since 3.0
 * @param string $html The content.
 * @return string
 */
function fusion_maybe_add_clearfix( $html ) {
	if ( ! fusion_element_rendering_is_flex() ) {
		$html .= '<div class="clearfix"></div>';
	}
	return $html;
}

/**
 * Fix shortcode content. Remove p and br tags.
 *
 * @since 1.0
 * @param string $content The content.
 * @return string
 */
function fusion_builder_fix_shortcodes( $content ) {
	$replace_tags_from_to = [
		'<p>['      => '[',
		']</p>'     => ']',
		']<br />'   => ']',
		"<br />\n[" => '[',
	];

	return strtr( $content, $replace_tags_from_to );
}

/**
 * Get video prodiver.
 *
 * @since 1.0
 * @param string $video_string The video as entered by the user.
 * @return array
 */
function fusion_builder_get_video_provider( $video_string ) {

	$video_string = trim( $video_string );

	// Check for YouTube.
	$video_id = false;
	if ( preg_match( '/youtube\.com\/watch\?v=([^\&\?\/]+)/', $video_string, $id ) ) {
		if ( isset( $id[1] ) ) {
			$video_id = $id[1];
		}
	} elseif ( preg_match( '/youtube\.com\/embed\/([^\&\?\/]+)/', $video_string, $id ) ) {
		if ( isset( $id[1] ) ) {
			$video_id = $id[1];
		}
	} elseif ( preg_match( '/youtube\.com\/v\/([^\&\?\/]+)/', $video_string, $id ) ) {
		if ( isset( $id[1] ) ) {
			$video_id = $id[1];
		}
	} elseif ( preg_match( '/youtu\.be\/([^\&\?\/]+)/', $video_string, $id ) ) {
		if ( isset( $id[1] ) ) {
			$video_id = $id[1];
		}
	}

	if ( ! empty( $video_id ) ) {
		return [
			'type' => 'youtube',
			'id'   => $video_id,
		];
	}

	// Check for Vimeo.
	if ( preg_match( '/vimeo\.com\/(\w*\/)*(\d+)/', $video_string, $id ) ) {
		if ( isset( $id[1] ) ) {
			$video_id = $id[ count( $id ) - 1 ];
		}
	}

	if ( ! empty( $video_id ) ) {
		return [
			'type' => 'vimeo',
			'id'   => $video_id,
		];
	}

	// Non-URL form.
	if ( preg_match( '/^\d+$/', $video_string ) ) {
		return [
			'type' => 'vimeo',
			'id'   => $video_string,
		];
	}

	return [
		'type' => 'youtube',
		'id'   => $video_string,
	];
}

/**
 * Create animation data and class.
 *
 * @since 1.0
 * @param string $animation_type      The animation type.
 * @param string $animation_direction Animation direction.
 * @param string $animation_speed     The animation speed (in miliseconds).
 * @param string $animation_offset    The animation offset.
 */
function fusion_builder_animation_data( $animation_type = '', $animation_direction = '', $animation_speed = '', $animation_offset = '' ) {

	$animation          = [];
	$animation['data']  = '';
	$animation['class'] = '';

	if ( ! empty( $animation_type ) ) {

		if ( ! in_array( $animation_type, [ 'flash', 'shake', 'rubberBand', 'flipinx', 'flipiny' ], true ) ) {
			$animation_type = sprintf( '%1$sIn%2$s', $animation_type, ucfirst( $animation_direction ) );
		}

		$animation['data'] .= ' data-animationType=' . esc_attr( str_replace( 'Static', '', $animation_type ) );
		$animation['data'] .= ' data-animationDuration=' . esc_attr( $animation_speed );
		$animation['class'] = ' fusion-animated';

		if ( $animation_offset ) {
			if ( 'top-into-view' === $animation_offset ) {
				$offset = '100%';
			} elseif ( 'top-mid-of-view' === $animation_offset ) {
				$offset = '50%';
			} else {
				$offset = $animation_offset;
			}
			$animation['data'] .= ' data-animationOffset=' . esc_attr( $offset );
		}
	}

	return $animation;
}

/**
 * List of available animation types.
 *
 * @since 1.0
 */
function fusion_builder_available_animations() {

	$animations = [
		''             => esc_attr__( 'None', 'fusion-builder' ),
		'bounce'       => esc_attr__( 'Bounce', 'fusion-builder' ),
		'fade'         => esc_attr__( 'Fade', 'fusion-builder' ),
		'flash'        => esc_attr__( 'Flash', 'fusion-builder' ),
		'rubberBand'   => esc_attr__( 'Rubberband', 'fusion-builder' ),
		'shake'        => esc_attr__( 'Shake', 'fusion-builder' ),
		'slide'        => esc_attr__( 'Slide', 'fusion-builder' ),
		'zoom'         => esc_attr__( 'Zoom', 'fusion-builder' ),
		'flipinx'      => esc_attr__( 'Flip Vertically', 'fusion-builder' ),
		'flipiny'      => esc_attr__( 'Flip Horizontally', 'fusion-builder' ),
		'lightspeedin' => esc_attr__( 'Light Speed', 'fusion-builder' ),
	];

	return $animations;
}

/**
 * Returns array of layerslider slide groups.
 *
 * @since 1.0
 * @return array slide keys array.
 */
function fusion_builder_get_layerslider_slides() {
	global $wpdb;
	$slides_array[] = 'Select a slider';

	// Check if layer slider is active.
	if ( shortcode_exists( 'layerslider' ) ) {

		// Get sliders.
		$sliders = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}layerslider WHERE flag_hidden = '0' AND flag_deleted = '0' ORDER BY date_c ASC" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		if ( ! empty( $sliders ) ) {
			foreach ( $sliders as $key => $item ) {
				$slides[ $item->id ] = '';
			}
		}

		if ( isset( $slides ) && $slides ) {
			foreach ( $sliders as $slide ) {
				$slides_array[ $slide->id ] = $slide->name . ' (#' . $slide->id . ')';
			}
		}
	}

	return $slides_array;
}

/**
 * Returns array of rev slider slide groups.
 *
 * @since 1.0
 * @return array slide keys array.
 */
function fusion_builder_get_revslider_slides() {
	$revsliders[] = 'Select a slider';
	$revsliders   = [ '' => 'Select a slider' ];

	// Check if slider revolution is active.
	if ( shortcode_exists( 'rev_slider' ) ) {
		$slider_object = new RevSliderSlider();
		$sliders_array = $slider_object->getArrSliders();

		if ( $sliders_array ) {
			foreach ( $sliders_array as $slider ) {
				$revsliders[ $slider->getAlias() ] = $slider->getTitle();
			}
		}
	}

	return $revsliders;
}

/**
 * Get a list of all public post types.
 *
 * The arguments to get the types can be additionally changed.
 *
 * @since 3.5
 * @param array  $args Additional arguments to filter post types.
 * @param string $operator The operator to filter post types. can be 'and',
 *                         'or', 'not'. See get_post_types().
 * @return array
 */
function awb_get_post_types( $args = [], $operator = 'and' ) {
	$returned_types = [];
	$defaults       = [
		'public'  => true,
		'show_ui' => true,
	];

	$post_types_filter = array_merge( $defaults, $args );

	$post_types = get_post_types( $post_types_filter, 'objects', $operator );

	foreach ( $post_types as $post_type ) {
		$returned_types[ $post_type->name ] = $post_type->label;
	}

	return $returned_types;
}

/**
 * Taxonomies.
 *
 * @since 1.0
 * @param string $taxonomy           The taxonomy.
 * @param bool   $empty_choice       If this is an empty choice or not.
 * @param string $empty_choice_label The label for empty choices.
 * @return array
 */
function fusion_builder_shortcodes_categories( $taxonomy, $empty_choice = false, $empty_choice_label = false ) {

	if ( ! $empty_choice_label ) {
		$empty_choice_label = esc_attr__( 'Default', 'fusion-builder' );
	}
	$post_categories = [];

	if ( $empty_choice ) {
		$post_categories[ $empty_choice_label ] = '';
	}

	$get_categories = get_categories( 'hide_empty=0&taxonomy=' . $taxonomy );

	if ( ! is_wp_error( $get_categories ) ) {

		if ( $get_categories && is_array( $get_categories ) ) {
			foreach ( $get_categories as $cat ) {
				if ( isset( $cat->slug ) && isset( $cat->name ) ) {
					$label                                      = $cat->name . ( ( isset( $cat->count ) ) ? ' (' . $cat->count . ')' : '' );
					$post_categories[ urldecode( $cat->slug ) ] = $label;
				}
			}
		}

		if ( isset( $post_categories ) ) {
			return $post_categories;
		}
	}
}
/**
 * Taxonomy terms.
 *
 * @since 1.2
 * @param string $taxonomy           The taxonomy.
 * @param bool   $empty_choice       If this is an empty choice or not.
 * @param string $empty_choice_label The label for empty choices.
 * @return array
 */
function fusion_builder_shortcodes_tags( $taxonomy, $empty_choice = false, $empty_choice_label = false ) {

	if ( ! $empty_choice_label ) {
		$empty_choice_label = esc_attr__( 'Default', 'fusion-builder' );
	}
	$post_tags = [];

	if ( $empty_choice ) {
		$post_tags[ $empty_choice_label ] = '';
	}

	$get_terms = get_terms( $taxonomy, [ 'hide_empty' => true ] );

	if ( ! is_wp_error( $get_terms ) ) {

		if ( $get_terms && is_array( $get_terms ) ) {
			foreach ( $get_terms as $term ) {
				$label = $term->name . ( ( property_exists( $term, 'count' ) ) ? ' (' . $term->count . ')' : '' );

				$post_tags[ urldecode( $term->slug ) ] = $label;
			}
		}

		if ( isset( $post_tags ) ) {
			return $post_tags;
		}
	}
}

/**
 * Column combinations.
 *
 * @since  1.0
 * @param  string $module module being triggered from.
 * @return string html output for column selection.
 */
function fusion_builder_column_layouts( $module = '' ) {

	$layouts = apply_filters(
		'fusion_builder_column_layouts',
		[
			[
				'layout'   => [ '' ],
				'keywords' => esc_attr__( 'empty blank', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_1' ],
				'keywords' => esc_attr__( 'full one 1', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_2', '1_2' ],
				'keywords' => esc_attr__( 'two half 2 1/2', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_3', '1_3', '1_3' ],
				'keywords' => esc_attr__( 'third thee 3 1/3', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_4', '1_4', '1_4', '1_4' ],
				'keywords' => esc_attr__( 'four fourth 4 1/4', 'fusion-builder' ),
			],
			[
				'layout'   => [ '2_3', '1_3' ],
				'keywords' => esc_attr__( 'two third 2/3 1/3', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_3', '2_3' ],
				'keywords' => esc_attr__( 'two third 2/3 1/3', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_4', '3_4' ],
				'keywords' => esc_attr__( 'one four fourth 1/4 3/4', 'fusion-builder' ),
			],
			[
				'layout'   => [ '3_4', '1_4' ],
				'keywords' => esc_attr__( 'one four fourth 1/4 3/4', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_2', '1_4', '1_4' ],
				'keywords' => esc_attr__( 'half one four fourth 1/2 1/4', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_4', '1_4', '1_2' ],
				'keywords' => esc_attr__( 'half one four fourth 1/2 1/4', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_4', '1_2', '1_4' ],
				'keywords' => esc_attr__( 'half one four fourth 1/2 1/4', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_5', '4_5' ],
				'keywords' => esc_attr__( 'one five fifth 1/5 4/5', 'fusion-builder' ),
			],
			[
				'layout'   => [ '4_5', '1_5' ],
				'keywords' => esc_attr__( 'one five fifth 1/5 4/5', 'fusion-builder' ),
			],
			[
				'layout'   => [ '3_5', '2_5' ],
				'keywords' => esc_attr__( 'three fith two fifth 3/5 2/5', 'fusion-builder' ),
			],
			[
				'layout'   => [ '2_5', '3_5' ],
				'keywords' => esc_attr__( 'two fifth three fifth 2/5 3/5', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_5', '1_5', '3_5' ],
				'keywords' => esc_attr__( 'one five fifth three 1/5 3/5', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_5', '3_5', '1_5' ],
				'keywords' => esc_attr__( 'one five fifth three 1/5 3/5', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_2', '1_6', '1_6', '1_6' ],
				'keywords' => esc_attr__( 'one half six sixth 1/2 1/6', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_6', '1_6', '1_6', '1_2' ],
				'keywords' => esc_attr__( 'one half six sixth 1/2 1/6', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_6', '2_3', '1_6' ],
				'keywords' => esc_attr__( 'one two six sixth 2/3 1/6', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_5', '1_5', '1_5', '1_5', '1_5' ],
				'keywords' => esc_attr__( 'one five fifth 1/5', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_6', '1_6', '1_6', '1_6', '1_6', '1_6' ],
				'keywords' => esc_attr__( 'one six sixth 1/6', 'fusion-builder' ),
			],
			[
				'layout'   => [ '5_6' ],
				'keywords' => esc_attr__( 'five sixth 5/6', 'fusion-builder' ),
			],
			[
				'layout'   => [ '4_5' ],
				'keywords' => esc_attr__( 'four fifth 4/5', 'fusion-builder' ),
			],
			[
				'layout'   => [ '3_4' ],
				'keywords' => esc_attr__( 'three fourth 3/4', 'fusion-builder' ),
			],
			[
				'layout'   => [ '2_3' ],
				'keywords' => esc_attr__( 'two third 2/3', 'fusion-builder' ),
			],
			[
				'layout'   => [ '3_5' ],
				'keywords' => esc_attr__( 'three fifth 3/5', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_2' ],
				'keywords' => esc_attr__( 'one half two 1/2', 'fusion-builder' ),
			],
			[
				'layout'   => [ '2_5' ],
				'keywords' => esc_attr__( 'two fifth 2/5', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_3' ],
				'keywords' => esc_attr__( 'one third three 1/3', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_4' ],
				'keywords' => esc_attr__( 'one four fourth 1/4', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_5' ],
				'keywords' => esc_attr__( 'one five fifth 1/5', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_6' ],
				'keywords' => esc_attr__( 'one six sixth 1/6', 'fusion-builder' ),
			],
		]
	);

	$is_builder = fusion_is_builder_frame();

	// If being viewed on a section, remove empty from layout options.
	if ( ! isset( $module ) || 'container' !== $module ) {
		unset( $layouts[0] );
	}

	$html = '<ul class="fusion-builder-column-layouts fusion-builder-all-modules">';
	foreach ( $layouts as $layout ) {
		$html .= '<li data-layout="' . implode( ',', $layout['layout'] ) . '">';
		$html .= '<h4 class="fusion_module_title" style="display:none;">' . $layout['keywords'] . '</h4>';
		$sizes = '';
		if ( $is_builder ) {
			$html .= '<div class="fusion-builder-column-previews">';
		}
		foreach ( $layout['layout'] as $size ) {
			$labelsize = preg_replace( '/[_]+/', '/', $size );
			$html     .= '<div class="fusion_builder_layout_column fusion_builder_column_layout_' . $size . '">' . ( $is_builder ? '' : $labelsize ) . '</div>';
			$sizes    .= '' === $sizes ? $labelsize : ' - ' . $labelsize;
		}
		if ( $is_builder ) {
			$html .= '</div><div class="fusion-builder-column-sizes">' . $sizes . '</div>';
		}
		$html .= '</li>';
	}
	if ( $is_builder ) {
		for ( $i = 0; $i < 16; $i++ ) {
			$html .= '<li class="spacer fusion-builder-element"></li>';
		}
	}
	$html .= '</ul>';

	return $html;
}

/**
 * Nested column combinations.
 *
 * @since 1.0
 */
function fusion_builder_inner_column_layouts() {

	$layouts = apply_filters(
		'fusion_builder_inner_column_layouts',
		[

			[
				'layout'   => [ '1_1' ],
				'keywords' => esc_attr__( 'full one 1', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_2', '1_2' ],
				'keywords' => esc_attr__( 'two half 2 1/2', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_3', '1_3', '1_3' ],
				'keywords' => esc_attr__( 'third thee 3 1/3', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_4', '1_4', '1_4', '1_4' ],
				'keywords' => esc_attr__( 'four fourth 4 1/4', 'fusion-builder' ),
			],
			[
				'layout'   => [ '2_3', '1_3' ],
				'keywords' => esc_attr__( 'two third 2/3 1/3', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_3', '2_3' ],
				'keywords' => esc_attr__( 'two third 2/3 1/3', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_4', '3_4' ],
				'keywords' => esc_attr__( 'one four fourth 1/4 3/4', 'fusion-builder' ),
			],
			[
				'layout'   => [ '3_4', '1_4' ],
				'keywords' => esc_attr__( 'one four fourth 1/4 3/4', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_2', '1_4', '1_4' ],
				'keywords' => esc_attr__( 'half one four fourth 1/2 1/4', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_4', '1_4', '1_2' ],
				'keywords' => esc_attr__( 'half one four fourth 1/2 1/4', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_4', '1_2', '1_4' ],
				'keywords' => esc_attr__( 'half one four fourth 1/2 1/4', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_5', '4_5' ],
				'keywords' => esc_attr__( 'one five fifth 1/5 4/5', 'fusion-builder' ),
			],
			[
				'layout'   => [ '4_5', '1_5' ],
				'keywords' => esc_attr__( 'one five fifth 1/5 4/5', 'fusion-builder' ),
			],
			[
				'layout'   => [ '3_5', '2_5' ],
				'keywords' => esc_attr__( 'three fith two fifth 3/5 2/5', 'fusion-builder' ),
			],
			[
				'layout'   => [ '2_5', '3_5' ],
				'keywords' => esc_attr__( 'two fifth three fifth 2/5 3/5', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_5', '1_5', '3_5' ],
				'keywords' => esc_attr__( 'one five fifth three 1/5 3/5', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_5', '3_5', '1_5' ],
				'keywords' => esc_attr__( 'one five fifth three 1/5 3/5', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_2', '1_6', '1_6', '1_6' ],
				'keywords' => esc_attr__( 'one half six sixth 1/2 1/6', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_6', '1_6', '1_6', '1_2' ],
				'keywords' => esc_attr__( 'one half six sixth 1/2 1/6', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_6', '2_3', '1_6' ],
				'keywords' => esc_attr__( 'one two six sixth 2/3 1/6', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_5', '1_5', '1_5', '1_5', '1_5' ],
				'keywords' => esc_attr__( 'one five fifth 1/5', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_6', '1_6', '1_6', '1_6', '1_6', '1_6' ],
				'keywords' => esc_attr__( 'one six sixth 1/6', 'fusion-builder' ),
			],
			[
				'layout'   => [ '5_6' ],
				'keywords' => esc_attr__( 'five sixth 5/6', 'fusion-builder' ),
			],
			[
				'layout'   => [ '4_5' ],
				'keywords' => esc_attr__( 'four fifth 4/5', 'fusion-builder' ),
			],
			[
				'layout'   => [ '3_4' ],
				'keywords' => esc_attr__( 'three fourth 3/4', 'fusion-builder' ),
			],
			[
				'layout'   => [ '2_3' ],
				'keywords' => esc_attr__( 'two third 2/3', 'fusion-builder' ),
			],
			[
				'layout'   => [ '3_5' ],
				'keywords' => esc_attr__( 'three fifth 3/5', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_2' ],
				'keywords' => esc_attr__( 'one half two 1/2', 'fusion-builder' ),
			],
			[
				'layout'   => [ '2_5' ],
				'keywords' => esc_attr__( 'two fifth 2/5', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_3' ],
				'keywords' => esc_attr__( 'one third three 1/3', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_4' ],
				'keywords' => esc_attr__( 'one four fourth 1/4', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_5' ],
				'keywords' => esc_attr__( 'one five fifth 1/5', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_6' ],
				'keywords' => esc_attr__( 'one six sixth 1/6', 'fusion-builder' ),
			],
		]
	);

	$is_builder = fusion_is_builder_frame();

	$html = '<ul class="fusion-builder-column-layouts fusion-builder-all-modules">';
	foreach ( $layouts as $layout ) {
		$html .= '<li data-layout="' . implode( ',', $layout['layout'] ) . '">';
		$html .= '<h4 class="fusion_module_title" style="display:none;">' . $layout['keywords'] . '</h4>';

		$sizes = '';
		if ( $is_builder ) {
			$html .= '<div class="fusion-builder-column-previews">';
		}
		foreach ( $layout['layout'] as $size ) {
			$labelsize = preg_replace( '/[_]+/', '/', $size );
			$html     .= '<div class="fusion_builder_layout_column fusion_builder_column_layout_' . $size . '">' . ( $is_builder ? '' : $labelsize ) . '</div>';
			$sizes    .= '' === $sizes ? $labelsize : ' - ' . $labelsize;
		}
		if ( $is_builder ) {
			$html .= '</div><div class="fusion-builder-column-sizes">' . $sizes . '</div>';
		}
		$html .= '</li>';
	}
	if ( $is_builder ) {
		for ( $i = 0; $i < 16; $i++ ) {
			$html .= '<li class="spacer fusion-builder-element"></li>';
		}
	}
	$html .= '</ul>';

	return $html;
}

/**
 * Column combinations.
 *
 * @since 1.0
 */
function fusion_builder_generator_column_layouts() {

	$layouts = apply_filters(
		'fusion_builder_generators_column_layouts',
		[
			[
				'layout'   => [ '1_1' ],
				'keywords' => esc_attr__( 'full one 1', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_2', '1_2' ],
				'keywords' => esc_attr__( 'two half 2 1/2', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_3', '1_3', '1_3' ],
				'keywords' => esc_attr__( 'third thee 3 1/3', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_4', '1_4', '1_4', '1_4' ],
				'keywords' => esc_attr__( 'four fourth 4 1/4', 'fusion-builder' ),
			],
			[
				'layout'   => [ '2_3', '1_3' ],
				'keywords' => esc_attr__( 'two third 2/3 1/3', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_3', '2_3' ],
				'keywords' => esc_attr__( 'two third 2/3 1/3', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_4', '3_4' ],
				'keywords' => esc_attr__( 'one four fourth 1/4 3/4', 'fusion-builder' ),
			],
			[
				'layout'   => [ '3_4', '1_4' ],
				'keywords' => esc_attr__( 'one four fourth 1/4 3/4', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_2', '1_4', '1_4' ],
				'keywords' => esc_attr__( 'half one four fourth 1/2 1/4', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_4', '1_4', '1_2' ],
				'keywords' => esc_attr__( 'half one four fourth 1/2 1/4', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_4', '1_2', '1_4' ],
				'keywords' => esc_attr__( 'half one four fourth 1/2 1/4', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_5', '4_5' ],
				'keywords' => esc_attr__( 'one five fifth 1/5 4/5', 'fusion-builder' ),
			],
			[
				'layout'   => [ '4_5', '1_5' ],
				'keywords' => esc_attr__( 'one five fifth 1/5 4/5', 'fusion-builder' ),
			],
			[
				'layout'   => [ '3_5', '2_5' ],
				'keywords' => esc_attr__( 'three fith two fifth 3/5 2/5', 'fusion-builder' ),
			],
			[
				'layout'   => [ '2_5', '3_5' ],
				'keywords' => esc_attr__( 'two fifth three fifth 2/5 3/5', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_5', '1_5', '3_5' ],
				'keywords' => esc_attr__( 'one five fifth three 1/5 3/5', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_5', '3_5', '1_5' ],
				'keywords' => esc_attr__( 'one five fifth three 1/5 3/5', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_2', '1_6', '1_6', '1_6' ],
				'keywords' => esc_attr__( 'one half six sixth 1/2 1/6', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_6', '1_6', '1_6', '1_2' ],
				'keywords' => esc_attr__( 'one half six sixth 1/2 1/6', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_6', '2_3', '1_6' ],
				'keywords' => esc_attr__( 'one two six sixth 2/3 1/6', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_5', '1_5', '1_5', '1_5', '1_5' ],
				'keywords' => esc_attr__( 'one five fifth 1/5', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_6', '1_6', '1_6', '1_6', '1_6', '1_6' ],
				'keywords' => esc_attr__( 'one six sixth 1/6', 'fusion-builder' ),
			],
			[
				'layout'   => [ '5_6' ],
				'keywords' => esc_attr__( 'five sixth 5/6', 'fusion-builder' ),
			],
			[
				'layout'   => [ '4_5' ],
				'keywords' => esc_attr__( 'four fifth 4/5', 'fusion-builder' ),
			],
			[
				'layout'   => [ '3_4' ],
				'keywords' => esc_attr__( 'three fourth 3/4', 'fusion-builder' ),
			],
			[
				'layout'   => [ '2_3' ],
				'keywords' => esc_attr__( 'two third 2/3', 'fusion-builder' ),
			],
			[
				'layout'   => [ '3_5' ],
				'keywords' => esc_attr__( 'three fifth 3/5', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_2' ],
				'keywords' => esc_attr__( 'one half two 1/2', 'fusion-builder' ),
			],
			[
				'layout'   => [ '2_5' ],
				'keywords' => esc_attr__( 'two fifth 2/5', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_3' ],
				'keywords' => esc_attr__( 'one third three 1/3', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_4' ],
				'keywords' => esc_attr__( 'one four fourth 1/4', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_5' ],
				'keywords' => esc_attr__( 'one five fifth 1/5', 'fusion-builder' ),
			],
			[
				'layout'   => [ '1_6' ],
				'keywords' => esc_attr__( 'one six sixth 1/6', 'fusion-builder' ),
			],
		]
	);

	$is_builder = fusion_is_builder_frame();

	$html = '<ul class="fusion-builder-column-layouts fusion-builder-all-modules">';

	foreach ( $layouts as $layout ) {
		$html .= '<li class="generator-column" data-layout="' . implode( ',', $layout['layout'] ) . '">';
		$html .= '<h4 class="fusion_module_title" style="display:none;">' . $layout['keywords'] . '</h4>';

		$sizes = '';
		if ( $is_builder ) {
			$html .= '<div class="fusion-builder-column-previews">';
		}
		foreach ( $layout['layout'] as $size ) {
			$labelsize = preg_replace( '/[_]+/', '/', $size );
			$html     .= '<div class="fusion_builder_layout_column fusion_builder_column_layout_' . $size . '">' . ( $is_builder ? '' : $labelsize ) . '</div>';
			$sizes    .= '' === $sizes ? $labelsize : ' - ' . $labelsize;
		}
		if ( $is_builder ) {
			$html .= '</div><div class="fusion-builder-column-sizes">' . $sizes . '</div>';
		}
		$html .= '</li>';
	}

	if ( $is_builder ) {
		for ( $i = 0; $i < 16; $i++ ) {
			$html .= '<li class="spacer fusion-builder-element"></li>';
		}
	}
	$html .= '</ul>';

	return $html;
}

/**
 * Save the metadata.
 *
 * @since 1.0
 * @param int    $post_id The poist-ID.
 * @param object $post    The Post object.
 */
function fusion_builder_save_meta( $post_id, $post ) {

	// Verify the nonce before proceeding.
	if ( ! isset( $_POST['fusion_builder_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['fusion_builder_nonce'] ), 'fusion_builder_template' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

		return $post_id;
	}

	// Get the post type object.
	$post_type = get_post_type_object( $post->post_type );

	// Check if the current user has permission to edit the post.
	if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) ) {
		return $post_id;
	}

	$meta_key       = '_fusion_builder_custom_css';
	$new_meta_value = ( isset( $_POST[ $meta_key ] ) ? sanitize_textarea_field( $_POST[ $meta_key ] ) : '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$old_meta_value = get_post_meta( $post_id, $meta_key, true );

	if ( $new_meta_value && ! $old_meta_value ) {
		// If a new meta value was added and there was no previous value, add it.
		add_post_meta( $post_id, $meta_key, $new_meta_value, true );
	} elseif ( $new_meta_value && $new_meta_value !== $old_meta_value ) {
		// If the new meta value does not match the old value, update it.
		update_post_meta( $post_id, $meta_key, $new_meta_value );
	} elseif ( ! $new_meta_value && $old_meta_value ) {
		// If there is no new meta value but an old value exists, delete it.
		delete_post_meta( $post_id, $meta_key, $old_meta_value );
	}

}
add_action( 'save_post', 'fusion_builder_save_meta', 10, 2 );

/**
 * Print custom CSS code.
 *
 * @since 1.0
 */
function fusion_builder_custom_css() {
	global $post;

	// Early exit if $post is not defined.
	if ( is_null( $post ) ) {
		return;
	}

	$saved_custom_css = get_post_meta( $post->ID, '_fusion_builder_custom_css', true );
	if ( isset( $saved_custom_css ) && $saved_custom_css ) {
		echo '<style type="text/css" id="fusion-builder-page-css">' . sanitize_textarea_field( $saved_custom_css ) . '</style>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
}
add_action( 'wp_head', 'fusion_builder_custom_css', 1001 );

/**
 * Add shortcode generator toggle button to text editor.
 *
 * @since 1.0
 */
function fusion_builder_add_quicktags_button() {
	?>
	<?php if ( is_object( get_current_screen() ) && 'post' === get_current_screen()->base ) : ?>
		<script type="text/javascript" charset="utf-8">
			if ( 'function' === typeof QTags ) {
				QTags.addButton( 'fusion_shortcodes_text_mode', 'A:','', '', 'f' );
			}
		</script>
	<?php endif; ?>
	<?php
}
add_action( 'admin_print_footer_scripts', 'fusion_builder_add_quicktags_button', 15 );

/**
 * Build Social Network Icons.
 *
 * @since 1.0
 * @param string|array $social_networks The social networks array.
 * @param string       $filter          The filter that will be used to build the attributes.
 * @param array        $defaults        Defaults array.
 * @param int          $i               Increment counter.
 * @return string
 */
function fusion_builder_build_social_links( $social_networks = '', $filter = '', $defaults = [], $i = 0 ) {

	$fusion_settings    = awb_get_fusion_settings();
	$use_brand_colors   = false;
	$icons              = '';
	$shortcode_defaults = [];

	if ( $social_networks && is_array( $social_networks ) ) {

		// Add compatibility for different key names in shortcodes.
		foreach ( $defaults as $key => $value ) {
			$key = ( 'social_icon_boxed' === $key ) ? 'icons_boxed' : $key;
			$key = ( 'social_icon_colors' === $key ) ? 'icon_colors' : $key;
			$key = ( 'social_icon_boxed_colors' === $key ) ? 'box_colors' : $key;
			$key = ( 'social_icon_color_type' === $key ) ? 'color_type' : $key;

			$shortcode_defaults[ $key ] = $value;
		}

		extract( $shortcode_defaults );

		// Custom social icon colors.
		$icon_colors = explode( '|', $icon_colors );
		$box_colors  = explode( '|', $box_colors );

		$num_of_icon_colors = count( $icon_colors );
		$num_of_box_colors  = count( $box_colors );

		// Check for icon color type.
		if ( 'brand' === $color_type || ( '' === $color_type && 'brand' === $fusion_settings->get( 'social_links_color_type' ) ) ) {
			$use_brand_colors = true;

			$box_colors = Fusion_Data::fusion_social_icons( true, true );
			// Backwards compatibility for old social network names.
			$box_colors['mail'] = [
				'label' => esc_html__( 'Email Address', 'fusion-builder' ),
				'color' => '#000000',
			];

		} else {

			$social_networks_count = count( $social_networks );

			for ( $k = 0; $k < $social_networks_count; $k++ ) {
				if ( 1 === $num_of_icon_colors ) {
					$icon_colors[ $k ] = $icon_colors[0];
				}
				if ( 1 === $num_of_box_colors ) {
					$box_colors[ $k ] = $box_colors[0];
				}
			}
		}

		// Process social networks.
		foreach ( $social_networks as $key => $value ) {

			foreach ( $value as $network => $link ) {

				if ( 'custom' === $network && is_array( $link ) ) {

					foreach ( $link as $custom_key => $url ) {

						if ( 'yes' === $icons_boxed ) {

							if ( true === $use_brand_colors ) {
								$custom_icon_box_color = ( $box_colors[ $network ]['color'] ) ? $box_colors[ $network ]['color'] : '';
							} else {
								$custom_icon_box_color = isset( $box_colors[ $i ] ) ? $box_colors[ $i ] : '';
							}
						} else {
							$custom_icon_box_color = '';
						}

						$social_media_icons = $fusion_settings->get( 'social_media_icons' );
						if ( ! is_array( $social_media_icons ) ) {
							$social_media_icons = [];
						}
						if ( ! isset( $social_media_icons['custom_title'] ) ) {
							$social_media_icons['custom_title'] = [];
						}
						if ( ! isset( $social_media_icons['custom_source'] ) ) {
							$social_media_icons['custom_source'] = [];
						}
						if ( ! isset( $social_media_icons['custom_title'][ $custom_key ] ) ) {
							$social_media_icons['custom_title'][ $custom_key ] = '';
						}
						if ( ! isset( $social_media_icons['custom_source'][ $custom_key ] ) ) {
							$social_media_icons['custom_source'][ $custom_key ] = '';
						}
						if ( ! isset( $social_media_icons['icon_mark'][ $custom_key ] ) ) {
							$social_media_icons['icon_mark'][ $custom_key ] = '';
						}

						$icon_options = [
							'social_network' => $social_media_icons['custom_title'][ $custom_key ],
							'social_link'    => $url,
							'icon_color'     => isset( $icon_colors[ $i ] ) ? $icon_colors[ $i ] : '',
							'box_color'      => $custom_icon_box_color,
							'icon_mark'      => str_replace( 'fusion-prefix-', '', $social_media_icons['icon_mark'][ $custom_key ] ),
						];

						$icons .= '<a ' . FusionBuilder::attributes( $filter, $icon_options ) . '>';

						if ( empty( $social_media_icons['icon_mark'][ $custom_key ] ) ) {
							$icons .= '<img';

							if ( isset( $social_media_icons['custom_source'][ $custom_key ]['url'] ) ) {
								$icons .= ' src="' . $social_media_icons['custom_source'][ $custom_key ]['url'] . '"';
							}
							if ( isset( $social_media_icons['custom_title'][ $custom_key ] ) && $social_media_icons['custom_title'][ $custom_key ] ) {
								$icons .= ' alt="' . $social_media_icons['custom_title'][ $custom_key ] . '"';
							}
							if ( isset( $social_media_icons['custom_source'][ $custom_key ]['width'] ) && $social_media_icons['custom_source'][ $custom_key ]['width'] ) {
								$width  = intval( $social_media_icons['custom_source'][ $custom_key ]['width'] );
								$icons .= ' width="' . $width . '"';
							}
							if ( isset( $social_media_icons['custom_source'][ $custom_key ]['height'] ) && $social_media_icons['custom_source'][ $custom_key ]['height'] ) {
								$height = intval( $social_media_icons['custom_source'][ $custom_key ]['height'] );
								$icons .= ' height="' . $height . '"';
							}
							$icons .= ' />';
						}

						$icons .= '</a>';
					}
				} else {

					if ( true === $use_brand_colors ) {
						$icon_options = [
							'social_network' => $network,
							'social_link'    => $link,
							'icon_color'     => ( 'yes' === $icons_boxed ) ? '#ffffff' : $box_colors[ $network ]['color'],
							'box_color'      => ( 'yes' === $icons_boxed ) ? $box_colors[ $network ]['color'] : '',
						];

					} else {
						$icon_options = [
							'social_network' => $network,
							'social_link'    => $link,
							'icon_color'     => isset( $icon_colors[ $i ] ) ? $icon_colors[ $i ] : '',
							'box_color'      => isset( $box_colors[ $i ] ) ? $box_colors[ $i ] : '',
						];
					}
					$icons .= '<a ' . FusionBuilder::attributes( $filter, $icon_options ) . '></a>';
				}
				$i++;
			}
		}
	}
	return $icons;
}

/**
 * Get Social Networks.
 *
 * @since 1.0
 * @param array $defaults The default values.
 * @return array
 */
function fusion_builder_get_social_networks( $defaults ) {

	$fusion_settings    = awb_get_fusion_settings();
	$social_links_array = [];

	// Careful! The icons are also ordered by these.
	if ( $defaults['facebook'] ) {
		$social_links_array['facebook'] = $defaults['facebook'];
	}
	if ( $defaults['twitch'] ) {
		$social_links_array['twitch'] = $defaults['twitch'];
	}
	if ( $defaults['tiktok'] ) {
		$social_links_array['tiktok'] = $defaults['tiktok'];
	}
	if ( $defaults['twitter'] ) {
		$social_links_array['twitter'] = $defaults['twitter'];
	}
	if ( $defaults['instagram'] ) {
		$social_links_array['instagram'] = $defaults['instagram'];
	}
	if ( $defaults['youtube'] ) {
		$social_links_array['youtube'] = $defaults['youtube'];
	}
	if ( $defaults['linkedin'] ) {
		$social_links_array['linkedin'] = $defaults['linkedin'];
	}
	if ( $defaults['dribbble'] ) {
		$social_links_array['dribbble'] = $defaults['dribbble'];
	}
	if ( $defaults['rss'] ) {
		$social_links_array['rss'] = $defaults['rss'];
	}
	if ( $defaults['pinterest'] ) {
		$social_links_array['pinterest'] = $defaults['pinterest'];
	}
	if ( $defaults['flickr'] ) {
		$social_links_array['flickr'] = $defaults['flickr'];
	}
	if ( $defaults['vimeo'] ) {
		$social_links_array['vimeo'] = $defaults['vimeo'];
	}
	if ( $defaults['tumblr'] ) {
		$social_links_array['tumblr'] = $defaults['tumblr'];
	}
	if ( $defaults['discord'] ) {
		$social_links_array['discord'] = $defaults['discord'];
	}
	if ( $defaults['digg'] ) {
		$social_links_array['digg'] = $defaults['digg'];
	}
	if ( $defaults['blogger'] ) {
		$social_links_array['blogger'] = $defaults['blogger'];
	}
	if ( $defaults['skype'] ) {
		$social_links_array['skype'] = $defaults['skype'];
	}
	if ( $defaults['teams'] ) {
		$social_links_array['teams'] = $defaults['teams'];
	}
	if ( $defaults['myspace'] ) {
		$social_links_array['myspace'] = $defaults['myspace'];
	}
	if ( $defaults['deviantart'] ) {
		$social_links_array['deviantart'] = $defaults['deviantart'];
	}
	if ( $defaults['yahoo'] ) {
		$social_links_array['yahoo'] = $defaults['yahoo'];
	}
	if ( $defaults['reddit'] ) {
		$social_links_array['reddit'] = $defaults['reddit'];
	}
	if ( $defaults['forrst'] ) {
		$social_links_array['forrst'] = $defaults['forrst'];
	}
	if ( $defaults['paypal'] ) {
		$social_links_array['paypal'] = $defaults['paypal'];
	}
	if ( $defaults['dropbox'] ) {
		$social_links_array['dropbox'] = $defaults['dropbox'];
	}
	if ( $defaults['soundcloud'] ) {
		$social_links_array['soundcloud'] = $defaults['soundcloud'];
	}
	if ( $defaults['vk'] ) {
		$social_links_array['vk'] = $defaults['vk'];
	}
	if ( $defaults['wechat'] ) {
		$social_links_array['wechat'] = $defaults['wechat'];
	}
	if ( $defaults['whatsapp'] ) {
		$social_links_array['whatsapp'] = $defaults['whatsapp'];
	}
	if ( $defaults['telegram'] ) {
		$social_links_array['telegram'] = $defaults['telegram'];
	}
	if ( $defaults['xing'] ) {
		$social_links_array['xing'] = $defaults['xing'];
	}
	if ( $defaults['yelp'] ) {
		$social_links_array['yelp'] = $defaults['yelp'];
	}
	if ( $defaults['spotify'] ) {
		$social_links_array['spotify'] = $defaults['spotify'];
	}
	if ( $defaults['email'] ) {
		$social_links_array['mail'] = $defaults['email'];
	}
	if ( $defaults['phone'] ) {
		$social_links_array['phone'] = $defaults['phone'];
	}
	if ( $defaults['show_custom'] && 'yes' === $defaults['show_custom'] ) {
		$social_links_array['custom'] = [];

		$social_media_icons_arr = $fusion_settings->get( 'social_media_icons', 'icon' );
		if ( is_array( $social_media_icons_arr ) ) {
			foreach ( $social_media_icons_arr as $key => $icon ) {
				$social_media_icons_url = $fusion_settings->get( 'social_media_icons', 'url' );
				if ( 'custom' === $icon && is_array( $social_media_icons_url ) && isset( $social_media_icons_url[ $key ] ) && ! empty( $social_media_icons_url[ $key ] ) ) {
					// Check if there is a default set for this, if so use that rather than GO link.
					if ( isset( $defaults[ 'custom_' . $key ] ) && ! empty( $defaults[ 'custom_' . $key ] ) ) {
						$social_links_array['custom'][ $key ] = $defaults[ 'custom_' . $key ];
					} else {
						$social_links_array['custom'][ $key ] = $social_media_icons_url[ $key ];
					}
				}
			}
		}
	}

	return $social_links_array;
}

/**
 * Sort Social Network Icons.
 *
 * @since 1.0
 * @param array $social_networks_original Original array of social networks.
 * @return array
 */
function fusion_builder_sort_social_networks( $social_networks_original ) {

	$fusion_settings = awb_get_fusion_settings();
	$social_networks = [];
	$icon_order      = '';

	// Get social networks order from Global Options.
	$social_media_icons = $fusion_settings->get( 'social_media_icons' );
	if ( isset( $social_media_icons['icon'] ) && is_array( $social_media_icons['icon'] ) ) {
		$icon_order = implode( '|', $social_media_icons['icon'] );
	}

	if ( ! is_array( $icon_order ) ) {
		$icon_order = explode( '|', $icon_order );
	}

	if ( is_array( $icon_order ) && ! empty( $icon_order ) ) {
		// First put the icons that exist in the Global Options,
		// and order them using tha same order as in Global Options.
		foreach ( $icon_order as $key => $value ) {

			// Backwards compatibility for old social network names.
			$value = ( 'email' === $value ) ? 'mail' : $value;

			// Check if social network from TO exists in shortcode.
			if ( ! isset( $social_networks_original[ $value ] ) ) {
				continue;
			}

			if ( 'custom' === $value ) {
				$social_networks[] = [ $value => [ $key => $social_networks_original[ $value ][ $key ] ] ];
			} else {
				$social_networks[] = [ $value => $social_networks_original[ $value ] ];
				unset( $social_networks_original[ $value ] );
			}
		}

		// Put any remaining icons after the ones from the Global Options.
		foreach ( $social_networks_original as $name => $url ) {
			if ( 'custom' !== $name ) {
				$social_networks[] = [ $name => $url ];
			}
		}
	}

	return $social_networks;
}

/**
 * Get Custom Social Networks.
 *
 * @since 1.0
 * @return array
 */
function fusion_builder_get_custom_social_networks() {

	$fusion_settings    = awb_get_fusion_settings();
	$social_links_array = [];
	$social_media_icons = $fusion_settings->get( 'social_media_icons' );
	if ( is_array( $social_media_icons ) && isset( $social_media_icons['icon'] ) && is_array( $social_media_icons['icon'] ) ) {
		foreach ( $social_media_icons['icon'] as $key => $icon ) {
			if ( 'custom' === $icon && isset( $social_media_icons['url'][ $key ] ) && ! empty( $social_media_icons['url'][ $key ] ) ) {
				$social_links_array[ $key ] = [
					'url'   => $social_media_icons['url'][ $key ],
					'title' => $social_media_icons['custom_title'][ $key ],
				];
			}
		}
	}
	return $social_links_array;
}

/**
 * Returns an array of visibility options.
 *
 * @since 1.0
 * @param string $type whether to return full array or values only.
 * @return array
 */
function fusion_builder_visibility_options( $type ) {
	$fb_edit            = ( isset( $_GET['fb-edit'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
	$visibility_options = [
		'small-visibility'  => $fb_edit ? '<span class="fusiona-mobile"></span>|' . esc_attr__( 'Small Screen', 'fusion-builder' ) : esc_attr__( 'Small Screen', 'fusion-builder' ),
		'medium-visibility' => $fb_edit ? '<span class="fusiona-tablet"></span>|' . esc_attr__( 'Medium Screen', 'fusion-builder' ) : esc_attr__( 'Medium Screen', 'fusion-builder' ),
		'large-visibility'  => $fb_edit ? '<span class="fusiona-desktop"></span>|' . esc_attr__( 'Large Screen', 'fusion-builder' ) : esc_attr__( 'Large Screen', 'fusion-builder' ),
	];
	if ( 'values' === $type ) {
		$visibility_options = array_keys( $visibility_options );
	}
	return $visibility_options;
}

/**
 * Returns an array of default visibility options.
 *
 * @since 1.0
 * @param  string $type either array or string to return.
 * @return string|array
 */
function fusion_builder_default_visibility( $type ) {

	$default_visibility = fusion_builder_visibility_options( 'values' );
	if ( 'string' === $type ) {
		$default_visibility = implode( ', ', $default_visibility );
	}
	return $default_visibility;
}

/**
 * Reverses the visibility selection and adds to attribute array.
 *
 * @since 1.0
 * @param string|array $selection Devices selected to be shown on.
 * @param array        $attr      Current attributes to add to.
 * @return array
 */
function fusion_builder_visibility_atts( $selection, $attr ) {
	$visibility_values = fusion_builder_visibility_options( 'values' );

	// If empty, show all.
	if ( empty( $selection ) ) {
		$selection = $visibility_values;
	}

	// If no is used, change that to all options selected, as fallback.
	if ( 'no' === $selection ) {
		$selection = $visibility_values;
	}

	// If yes is used, use all selections with mobile visibility removed.
	if ( 'yes' === $selection ) {
		$key = array_search( 'small-visibility', $visibility_values, true );
		if ( false !== $key ) {
			unset( $visibility_values[ $key ] );
			$selection = $visibility_values;
		}
	}

	// Make sure the selection is an array.
	if ( ! is_array( $selection ) ) {
		$selection = explode( ',', str_replace( ' ', '', $selection ) );
	}

	$visibility_options = fusion_builder_visibility_options( 'values' );
	foreach ( $visibility_options as $visibility_option ) {
		if ( ! in_array( $visibility_option, $selection ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			if ( is_array( $attr ) ) {
				$attr['class'] .= ( ( $attr['class'] ) ? ' fusion-no-' . $visibility_option : 'fusion-no-' . $visibility_option );
			} else {
				$attr .= ( ( $attr ) ? ' fusion-no-' . $visibility_option : 'fusion-no-' . $visibility_option );
			}
		}
	}
	return $attr;
}
/**
 * Adds fallbacks for section attributes.
 *
 * @since 1.0
 * @param array $args Array of attributes.
 * @return array
 */
function fusion_section_deprecated_args( $args ) {

	$param_mapping = [
		'backgroundposition'    => 'background_position',
		'backgroundattachment'  => 'background_parallax',
		'background_attachment' => 'background_parallax',
		'bordersize'            => 'border_size',
		'bordercolor'           => 'border_color',
		'borderstyle'           => 'border_style',
		'paddingtop'            => 'padding_top',
		'paddingbottom'         => 'padding_bottom',
		'paddingleft'           => 'padding_left',
		'paddingright'          => 'padding_right',
		'backgroundcolor'       => 'background_color',
		'backgroundimage'       => 'background_image',
		'backgroundrepeat'      => 'background_repeat',
		'paddingBottom'         => 'padding_bottom',
		'paddingTop'            => 'padding_top',
	];

	if ( ! is_array( $args ) ) {
		$args = [];
	}

	if ( ( array_key_exists( 'backgroundattachment', $args ) && 'scroll' === $args['backgroundattachment'] ) || ( array_key_exists( 'background_attachment', $args ) && 'scroll' === $args['background_attachment'] ) ) {
		$args['backgroundattachment'] = $args['background_attachment'] = 'none';
	}

	foreach ( $param_mapping as $old => $new ) {
		if ( ! isset( $args[ $new ] ) && isset( $args[ $old ] ) ) {
			$args[ $new ] = $args[ $old ];
			unset( $args[ $old ] );
		}
	}

	return $args;
}

/**
 * Creates placeholders for empty post type shortcodes.
 *
 * @since 1.0
 * @param string $post_type name of post type.
 * @param string $label label for post type.
 * @return string
 */
function fusion_builder_placeholder( $post_type = '', $label = '' ) {
	if ( current_user_can( 'publish_posts' ) ) {

		// If no label available, use from post type.
		if ( '' === $label ) {
			$post_type_obj = get_post_type_object( $post_type );
			if ( $post_type_obj ) {
				$label = strtolower( $post_type_obj->labels->name );
			}
		}

		/* translators: The label placeholder. */
		$string = sprintf( esc_html__( 'Please add %s for them to display here.', 'fusion-builder' ), $label );

		if ( 'gallery' !== $post_type && 'post_slider' !== $post_type ) {
			$link = admin_url( 'post-new.php?post_type=' . $post_type );
			$html = '<a href="' . $link . '" class="fusion-builder-placeholder">' . $string . '</a>';
		} else {
			$html = '<div class="fusion-builder-placeholder">' . $string . '</div>';
		}
		return $html;
	}
}

/**
 * Sorts modules.
 *
 * @since 1.0.0
 * @param array $a Element settings.
 * @param array $b Element settings.
 */
function fusion_element_sort( $a, $b ) {
	return strnatcmp( $a['name'], $b['name'] );
}

/**
 * Returns a single side dimension.
 *
 * @since 1.0
 * @param string $dimensions current dimensions combined.
 * @param string $direction which side dimension to be retrieved.
 * @return string
 */
function fusion_builder_single_dimension( $dimensions, $direction ) {
	$dimensions = explode( ' ', $dimensions );
	if ( 4 === count( $dimensions ) ) {
		list( $top, $right, $bottom, $left ) = $dimensions;
	} elseif ( 3 === count( $dimensions ) ) {
		$top    = $dimensions[0];
		$right  = $left = $dimensions[1];
		$bottom = $dimensions[2];
	} elseif ( 2 === count( $dimensions ) ) {
		$top   = $bottom = $dimensions[0];
		$right = $left = $dimensions[1];
	} else {
		$top    = $dimensions[0];
		$right  = $dimensions[0];
		$bottom = $dimensions[0];
		$left   = $dimensions[0];
	}
	return ${ $direction };
}

/**
 * Adds admin notice when visual editor is disabled
 *
 * @since 1.0
 */
function fusion_builder_add_notice_of_disabled_rich_editor() {
	global $current_user;
	$user_id = $current_user->ID;

	$current_uri = '';
	if ( isset( $_SERVER['REQUEST_URI'] ) ) {
		$current_uri = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );
	}
	$uri_parts = wp_parse_url( $current_uri );
	if ( ! isset( $uri_parts['query'] ) ) {
		$uri_parts['query'] = '';
	}
	$path      = explode( '/', $uri_parts['path'] );
	$last      = end( $path );
	$full_link = admin_url() . $last . '?' . $uri_parts['query'];

	// Check that the user hasn't already clicked to ignore the message.
	if ( ! get_user_meta( $user_id, 'fusion_richedit_nag_ignore' ) ) {
		printf( '<div id="disabled-rich-editor" class="updated"><p>%s <a href="%s">%s</a><span class="dismiss" style="float:right;"><a href="%s&fusion_richedit_nag_ignore=0">%s</a></span></div>', esc_attr__( 'Note: The visual editor, which is necessary for Avada Builder to work, has been disabled in your profile settings.', 'fusion-builder' ), esc_url_raw( admin_url( 'profile.php' ) ), esc_attr__( 'Go to Profile', 'fusion-builder' ), esc_url_raw( $full_link ), esc_attr__( 'Hide Notice', 'fusion-builder' ) );
	}
}

/**
 * Auto activate Avada Builder element. To be used by addon plugins.
 *
 * @since 1.0.4
 * @param string $shortcode Shortcode tag.
 */
function fusion_builder_auto_activate_element( $shortcode ) {
	$fusion_builder_settings = get_option( 'fusion_builder_settings', [] );

	if ( $fusion_builder_settings && isset( $fusion_builder_settings['fusion_elements'] ) && is_array( $fusion_builder_settings['fusion_elements'] ) ) {
		$fusion_builder_settings['fusion_elements'][] = $shortcode;

		update_option( 'fusion_builder_settings', $fusion_builder_settings );
	}
}

add_action( 'fusion_placeholder_image', 'fusion_render_placeholder_image', 10 );

if ( ! function_exists( 'fusion_render_placeholder_image' ) ) {
	/**
	 * Action to output a placeholder image.
	 *
	 * @param  string $featured_image_size     Size of the featured image that should be emulated.
	 *
	 * @return void
	 */
	function fusion_render_placeholder_image( $featured_image_size = 'full' ) {
		global $_wp_additional_image_sizes;

		if ( in_array( $featured_image_size, [ 'full', 'fixed' ], true ) ) {
			$height = apply_filters( 'fusion_set_placeholder_image_height', '150' );
			$width  = '100%';
		} else {
			@$height = $_wp_additional_image_sizes[ $featured_image_size ]['height']; // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			@$width  = $_wp_additional_image_sizes[ $featured_image_size ]['width'] . 'px'; // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		}
		?>
		<div class="fusion-placeholder-image" data-origheight="<?php echo esc_attr( $height ); ?>" data-origwidth="<?php echo esc_attr( $width ); ?>" style="height:<?php echo esc_attr( $height ); ?>px;width:<?php echo esc_attr( $width ); ?>;"></div>
		<?php
	}
}

/**
 * Returns equivalent global information for FB param.
 *
 * @since 1.0.5
 * @param string $shortcode Name of shortcode.
 * @param string $param     Param name in shortcode.
 * @return array|bool       Element option data.
 */
function fusion_builder_map_descriptions( $shortcode, $param ) {
	if ( 'animation_offset' === $param ) {
		return [
			'theme-option' => 'animation_offset',
			'type'         => 'select',
		];
	}

	if ( isset( FusionBuilder::$element_descriptions_map[ $param ][ $shortcode ] ) ) {
		return FusionBuilder::$element_descriptions_map[ $param ][ $shortcode ];
	}
	return false;
}

/**
 * Set builder element dependencies, for those which involve EO.
 *
 * @since  5.0.0
 * @param  array  $dependencies currently active dependencies.
 * @param  string $shortcode name of shortcode.
 * @param  string $option name of option.
 * @return array  dependency checks.
 */
function fusion_builder_element_dependencies( $dependencies, $shortcode, $option ) {

	$fusion_settings = awb_get_fusion_settings();

	// If has TO related dependency, do checks.
	if ( isset( FusionBuilder::$element_dependency_map[ $option ][ $shortcode ] ) && is_array( FusionBuilder::$element_dependency_map[ $option ][ $shortcode ] ) ) {
		foreach ( FusionBuilder::$element_dependency_map[ $option ][ $shortcode ] as $option_check ) {
			$option_value = $fusion_settings->get( $option_check['check']['element-option'] );
			$pass         = false;

			// Check the result of check.
			if ( '==' === $option_check['check']['operator'] ) {
				$pass = ( $option_value == $option_check['check']['value'] ) ? true : false; // phpcs:ignore WordPress.PHP.StrictComparisons
			}
			if ( '!=' === $option_check['check']['operator'] ) {
				$pass = ( $option_value != $option_check['check']['value'] ) ? true : false; // phpcs:ignore WordPress.PHP.StrictComparisons
			}

			// If check passes then add dependency for checking.
			if ( $pass ) {
				$dependencies[] = $option_check['output'];
			}
		}
	}
	return $dependencies;
}

if ( ! function_exists( 'fusion_builder_render_rich_snippets_for_pages' ) ) {
	/**
	 * Render the full meta data for blog archive and single layouts.
	 *
	 * @param  boolean $title_tag   Set to true to render title rich snippet.
	 * @param  bool    $author_tag  Set to true to render author rich snippet.
	 * @param  bool    $updated_tag Set to true to render updated rich snippet.
	 * @return string               HTML markup to display rich snippets.
	 */
	function fusion_builder_render_rich_snippets_for_pages( $title_tag = true, $author_tag = true, $updated_tag = true ) {

		$fusion_settings = awb_get_fusion_settings();
		ob_start();
		?>
		<?php if ( $fusion_settings->get( 'disable_date_rich_snippet_pages' ) ) : ?>

			<?php if ( $title_tag && $fusion_settings->get( 'disable_rich_snippet_title' ) ) : ?>
				<span class="entry-title" style="display: none;">
					<?php the_title(); ?>
				</span>
			<?php endif; ?>

			<?php if ( $author_tag && $fusion_settings->get( 'disable_rich_snippet_author' ) ) : ?>
				<span class="vcard" style="display: none;">
					<span class="fn">
						<?php the_author_posts_link(); ?>
					</span>
				</span>
			<?php endif; ?>

			<?php if ( $updated_tag && $fusion_settings->get( 'disable_rich_snippet_date' ) ) : ?>
				<span class="updated" style="display:none;">
					<?php echo get_the_modified_time( 'c' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</span>
			<?php endif; ?>

		<?php endif; ?>
		<?php
		$rich_snippets = ob_get_clean();
		return str_replace( [ "\t", "\n", "\r", "\0", "\x0B" ], '', $rich_snippets );
	}
}

if ( ! function_exists( 'fusion_builder_get_post_content' ) ) {
	/**
	 * Return the post content, either excerpted or in full length.
	 *
	 * @param  string  $page_id        The id of the current page or post.
	 * @param  string  $excerpt        Can be either 'blog' (for main blog page), 'portfolio' (for portfolio page template) or 'yes' (for shortcodes).
	 * @param  integer $excerpt_length Length of the excerpts.
	 * @param  boolean $strip_html     Can be used by shortcodes for a custom strip html setting.
	 * @return string Post content.
	 **/
	function fusion_builder_get_post_content( $page_id = '', $excerpt = 'no', $excerpt_length = 55, $strip_html = false ) {
		$content_excerpted = false;

		if ( 'yes' === $excerpt ) {
			$content_excerpted = true;
		}

		// Return excerpted content.
		if ( $content_excerpted ) {
			return fusion_builder_get_post_content_excerpt( $excerpt_length, $strip_html );
		}

		// Return full content.
		ob_start();
		the_content();
		return ob_get_clean();
	}
}

if ( ! function_exists( 'fusion_builder_get_post_content_excerpt' ) ) {
	/**
	 * Do the actual custom excerpting for of post/page content.
	 *
	 * @param  string  $limit      Maximum number of words or chars to be displayed in excerpt.
	 * @param  boolean $strip_html Set to TRUE to strip HTML tags from excerpt.
	 * @return string               The custom excerpt.
	 **/
	function fusion_builder_get_post_content_excerpt( $limit = 285, $strip_html = false ) {

		global $more;

		$fusion_settings = awb_get_fusion_settings();

		// Init variables, cast to correct types.
		$content        = '';
		$read_more      = '';
		$custom_excerpt = false;
		$limit          = intval( $limit );
		$strip_html     = filter_var( $strip_html, FILTER_VALIDATE_BOOLEAN );
		// If excerpt length is set to 0, return empty.
		if ( 0 === $limit ) {
			return $content;
		}
		$post = get_post( get_the_ID() );

		// If read more for excerpts is not disabled.
		if ( $fusion_settings->get( 'disable_excerpts' ) ) {

			$read_more_text = $fusion_settings->get( 'excerpt_read_more_symbol' );
			if ( '' === $read_more_text ) {
				$read_more_text = '&#91;...&#93;';
			}

			// Filter to set the default [...] read more to something arbritary.
			$read_more_text = apply_filters( 'fusion_blog_read_more_excerpt', $read_more_text );

			// Check if the read more [...] should link to single post.
			if ( $fusion_settings->get( 'link_read_more' ) ) {
				$permalink = get_permalink( get_the_ID() );
				if ( 'private' === get_post_status() && ! is_user_logged_in() || in_array( get_post_status(), [ 'pending', 'draft', 'future' ], true ) && ! current_user_can( 'edit-post' ) ) {
					$permalink = '#';
				}

				$read_more = ' <a href="' . esc_url( $permalink ) . '">' . $read_more_text . '</a>';
			} else {
				$read_more = ' ' . $read_more_text;
			}
		}

		// Construct the content.
		// Posts having a custom excerpt.
		if ( has_excerpt() ) {
			$content = '<p>' . do_shortcode( get_the_excerpt() ) . '</p>';
		} else { // All other posts (with and without <!--more--> tag in the contents).
			// HTML tags should be stripped.
			if ( $strip_html ) {
				$content = wp_strip_all_tags( get_the_content( '{{read_more_placeholder}}' ), '<p>' );
				// Strip out all attributes.
				$content = preg_replace( '/<(\w+)[^>]*>/', '<$1>', $content );
				$content = str_replace( '{{read_more_placeholder}}', $read_more, $content );
			} else { // HTML tags remain in excerpt.
				$content = get_the_content( $read_more );
			}
			$pattern = get_shortcode_regex();
			$content = preg_replace_callback( "/$pattern/s", 'fusion_extract_shortcode_contents', $content );
			// <!--more--> tag is used in the post.
			if ( false !== strpos( $post->post_content, '<!--more-->' ) ) {
				$content = apply_filters( 'the_content', $content );
				$content = str_replace( ']]>', ']]&gt;', $content );
				if ( $strip_html ) {
					$content = do_shortcode( $content );
				}
			}
		}
		// Limit the contents to the $limit length.
		if ( ! has_excerpt() ) {
			// Check if the excerpting should be char or word based.
			if ( 'characters' === fusion_get_option( 'excerpt_base' ) ) {
				$content  = mb_substr( $content, 0, $limit );
				$content .= $read_more;
			} else { // Excerpting is word based.
				$content = explode( ' ', $content, $limit + 1 );
				if ( count( $content ) > $limit ) {
					array_pop( $content );
					$content  = implode( ' ', $content );
					$content .= $read_more;
				} else {
					$content = implode( ' ', $content );
				}
			}
			if ( $strip_html ) {
				$content = '<p>' . $content . '</p>';
			} else {
				$content = apply_filters( 'the_content', $content );
				$content = str_replace( ']]>', ']]&gt;', $content );
			}
			$content = do_shortcode( $content );
		}
		return fusion_force_balance_tags( $content );
	}
}



if ( ! function_exists( 'fusion_builder_render_post_metadata' ) ) {
	/**
	 * Render the full meta data for blog archive and single layouts.
	 *
	 * @param string $layout    The blog layout (either single, standard, alternate or grid_timeline).
	 * @param string $settings HTML markup to display the date and post format box.
	 * @return  string
	 */
	function fusion_builder_render_post_metadata( $layout, $settings = [] ) {

		$fusion_settings = awb_get_fusion_settings();

		$html = $author = $date = $metadata = '';

		$settings = ( is_array( $settings ) ) ? $settings : [];

		$default_settings = [
			'post_meta'          => fusion_library()->get_option( 'post_meta' ),
			'post_meta_author'   => fusion_library()->get_option( 'post_meta_author' ),
			'post_meta_date'     => fusion_library()->get_option( 'post_meta_date' ),
			'post_meta_cats'     => fusion_library()->get_option( 'post_meta_cats' ),
			'post_meta_tags'     => fusion_library()->get_option( 'post_meta_tags' ),
			'post_meta_comments' => fusion_library()->get_option( 'post_meta_comments' ),
		];

		$settings  = wp_parse_args( $settings, $default_settings );
		$post_type = get_post_type();
		$post_meta = fusion_data()->post_meta( get_queried_object_id() )->get( 'post_meta' );

		// Check if meta data is enabled.
		if ( ( $settings['post_meta'] && 'no' !== $post_meta ) || ( ! $settings['post_meta'] && 'yes' === $post_meta ) ) {

			// For alternate, grid and timeline layouts return empty single-line-meta if all meta data for that position is disabled.
			if ( in_array( $layout, [ 'alternate', 'grid_timeline' ], true ) && ! $settings['post_meta_author'] && ! $settings['post_meta_date'] && ! $settings['post_meta_cats'] && ! $settings['post_meta_tags'] && ! $settings['post_meta_comments'] ) {
				return '';
			}

			// Render post type meta data.
			if ( isset( $settings['post_meta_type'] ) && $settings['post_meta_type'] ) {
				$metadata .= '<span class="fusion-meta-post-type">' . esc_html( ucwords( $post_type ) ) . '</span>';
				$metadata .= '<span class="fusion-inline-sep">|</span>';
			}

			// Render author meta data.
			if ( $settings['post_meta_author'] ) {
				ob_start();
				the_author_posts_link();
				$author_post_link = ob_get_clean();

				// Check if rich snippets are enabled.
				if ( $fusion_settings->get( 'disable_date_rich_snippet_pages' ) && $fusion_settings->get( 'disable_rich_snippet_author' ) ) {
					/* translators: The author. */
					$metadata .= sprintf( esc_html__( 'By %s', 'fusion-builder' ), '<span class="vcard"><span class="fn">' . $author_post_link . '</span></span>' );
				} else {
					/* translators: The author. */
					$metadata .= sprintf( esc_html__( 'By %s', 'fusion-builder' ), '<span>' . $author_post_link . '</span>' );
				}
				$metadata .= '<span class="fusion-inline-sep">|</span>';
			} else { // If author meta data won't be visible, render just the invisible author rich snippet.
				$author .= fusion_builder_render_rich_snippets_for_pages( false, true, false );
			}

			// Render the updated meta data or at least the rich snippet if enabled.
			if ( $settings['post_meta_date'] ) {
				$metadata      .= fusion_builder_render_rich_snippets_for_pages( false, false, true );
				$date_format    = $fusion_settings->get( 'date_format' );
				$date_format    = $date_format ? $date_format : get_option( 'date_format' );
				$formatted_date = get_the_time( $date_format );
				$date_markup    = '<span>' . $formatted_date . '</span><span class="fusion-inline-sep">|</span>';
				$metadata      .= apply_filters( 'fusion_post_metadata_date', $date_markup, $formatted_date );
			} else {
				$date .= fusion_builder_render_rich_snippets_for_pages( false, false, true );
			}

			// Render rest of meta data.
			// Render categories.
			if ( $settings['post_meta_cats'] ) {
				$categories = '';
				$taxonomies = [
					'avada_portfolio' => 'portfolio_category',
					'avada_faq'       => 'faq_category',
					'product'         => 'product_cat',
					'tribe_events'    => 'tribe_events_cat',
				];

				if ( 'post' === $post_type || isset( $taxonomies[ $post_type ] ) ) {
					$categories = 'post' === $post_type ? get_the_category_list( ', ' ) : get_the_term_list( get_the_ID(), $taxonomies[ $post_type ], '', ', ' );
				}

				if ( $categories ) {
					/* translators: The categories. */
					$metadata .= ( $settings['post_meta_tags'] ) ? sprintf( esc_html__( 'Categories: %s', 'fusion-builder' ), $categories ) : $categories;
					$metadata .= '<span class="fusion-inline-sep">|</span>';
				}
			}

			// Render tags.
			if ( $settings['post_meta_tags'] ) {
				if ( 'avada_portfolio' === $post_type ) {
					$tags = get_the_term_list( get_the_ID(), $taxonomies[ $post_type ], '', ', ', '' );
				} elseif ( 'product' === $post_type ) {
					$tags = get_the_term_list( get_the_ID(), 'product_tag', '', ', ', '' );
				} else {
					ob_start();
					the_tags( '' );
					$tags = ob_get_clean();
				}

				if ( $tags && ! is_wp_error( $tags ) ) {
					/* translators: The tags. */
					$metadata .= '<span class="meta-tags">' . sprintf( esc_html__( 'Tags: %s', 'fusion-builder' ), $tags ) . '</span><span class="fusion-inline-sep">|</span>';
				}
			}

			// Render comments.
			if ( $settings['post_meta_comments'] && 'grid_timeline' !== $layout ) {
				if ( 'private' === get_post_status() && ! is_user_logged_in() || in_array( get_post_status(), [ 'pending', 'draft', 'future' ], true ) && ! current_user_can( 'edit-post' ) ) {
					$comments = '<a href="#">' . get_comments_number() . ' ' . esc_html__( 'Comment(s)', 'fusion-builder' ) . '</a>';
				} else {
					ob_start();
					comments_popup_link( esc_html__( '0 Comments', 'fusion-builder' ), esc_html__( '1 Comment', 'fusion-builder' ), esc_html__( '% Comments', 'fusion-builder' ) );
					$comments = ob_get_clean();
				}

				$metadata .= '<span class="fusion-comments">' . $comments . '</span>';
			}

			// Render the HTML wrappers for the different layouts.
			if ( $metadata ) {
				$metadata = $author . $date . $metadata;

				if ( 'single' === $layout ) {
					$html .= '<div class="fusion-meta-info"><div class="fusion-meta-info-wrapper">' . $metadata . '</div></div>';
				} elseif ( in_array( $layout, [ 'alternate', 'grid_timeline' ], true ) ) {
					$html .= '<p class="fusion-single-line-meta">' . $metadata . '</p>';
				} elseif ( 'recent_posts' === $layout ) {
					$html .= $metadata;
				} else {
					$html .= '<div class="fusion-alignleft">' . $metadata . '</div>';
				}
			} else {
				$html .= $author . $date;
			}
		} else {
			// Render author and updated rich snippets for grid and timeline layouts.
			if ( $fusion_settings->get( 'disable_date_rich_snippet_pages' ) ) {
				$html .= fusion_builder_render_rich_snippets_for_pages( false );
			}
		}

		return apply_filters( 'fusion_post_metadata_markup', $html );
	}
}

if ( ! function_exists( 'fusion_builder_update_element' ) ) {
	/**
	 * Update single element setting value.
	 *
	 * @param string       $element    Shortcode tag of element.
	 * @param string       $param_name Param name to be updated.
	 * @param string/array $values     Settings to be replaced / updated.
	 */
	function fusion_builder_update_element( $element, $param_name, $values ) {

		global $all_fusion_builder_elements, $pagenow;

		if ( is_admin() && isset( $pagenow ) && ( 'admin.php' === $pagenow && isset( $_GET['page'] ) && 'avada-builder-options' === $_GET['page'] ) || ( 'post.php' === $pagenow ) || ( 'post-new.php' === $pagenow ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$element_settings = $all_fusion_builder_elements[ $element ]['params'];

			$settings = $element_settings[ $param_name ]['value'];

			if ( is_array( $values ) ) {
				$settings = array_merge( $settings, $values );
			} else {
				$settings = $values;
			}

			$all_fusion_builder_elements[ $element ]['params'][ $param_name ]['value'] = $settings;
		}
	}
}

/**
 * Check if element is enabled or not.
 *
 * @param  string $element The element shortcode tag.
 * @return boolean
 */
function fusion_is_element_enabled( $element ) {

	$fusion_builder_settings = get_option( 'fusion_builder_settings', [] );
	if ( empty( $fusion_builder_settings ) || ! isset( $fusion_builder_settings['fusion_elements'] ) ) {
		return true;
	}
	// Set Avada Builder enabled elements.
	$enabled_elements = $fusion_builder_settings['fusion_elements'];
	$element_enabled  = (bool) ( empty( $enabled_elements ) || ( ! empty( $enabled_elements ) && in_array( $element, $enabled_elements, true ) ) );

	return apply_filters( "fusion_is_{$element}_enabled", $element_enabled );
}

if ( ! function_exists( 'fusion_get_fields_array' ) ) {
	/**
	 * Get a single fields array from sections.
	 *
	 * @since 1.1
	 * @param  object $sections Sections from redux.
	 * @return array
	 */
	function fusion_get_fields_array( $sections ) {

		$fields = [];
		foreach ( $sections->sections as $section ) {
			if ( ! isset( $section['fields'] ) ) {
				continue;
			}
			foreach ( $section['fields'] as $field ) {
				if ( ! isset( $field['type'] ) ) {
					continue;
				}
				if ( ! in_array( $field['type'], [ 'sub-section', 'accordion' ], true ) ) {
					if ( isset( $field['id'] ) ) {
						$fields[] = $field['id'];
					}
				} else {
					if ( ! isset( $field['fields'] ) ) {
						continue;
					}
					foreach ( $field['fields'] as $sub_field ) {
						if ( isset( $sub_field['id'] ) ) {
							$fields[] = $sub_field['id'];
						}
					}
				}
			}
		}
		return $fields;
	}
}

if ( ! function_exists( 'fusion_builder_frontend_data' ) ) {
	/**
	 * Merges the front-end editor data into map.
	 *
	 * @since 2.0
	 * @param  string $class_name class for shortcode.
	 * @param  array  $map     Array map for shortcode.
	 * @param  string $context Parent or child level.
	 * @return string
	 */
	function fusion_builder_frontend_data( $class_name, $map, $context = '' ) {

		// If element class does not exist (element disabled).
		if ( ! class_exists( $class_name ) ) {
			return $map;
		}

		// TO-DO: add a check for whether or not front-end editor is currently active.
		$data = [];

		if ( method_exists( $class_name, 'get_element_defaults' ) ) {
			$data['defaults'] = $class_name::get_element_defaults( $context );
		}
		if ( method_exists( $class_name, 'get_element_extras' ) ) {
			$data['extras'] = $class_name::get_element_extras( $context );
		}
		if ( method_exists( $class_name, 'settings_to_params' ) ) {
			$data['settings_to_params'] = $class_name::settings_to_params( $context );
		}
		if ( method_exists( $class_name, 'settings_to_extras' ) ) {
			$data['settings_to_extras'] = $class_name::settings_to_extras( $context );
		}

		return apply_filters( 'fusion_builder_frontend_data', array_merge( $data, $map ), $class_name );
	}
}

/**
 * Get default selection from array.
 *
 * @since 1.3
 * @param array $choices Choices for select field.
 * @return array
 */
function fusion_get_array_default( $choices = [] ) {
	reset( $choices );
	return key( $choices );
}

/**
 * Wrap video embeds in WP core with our shortcode wrapper class.
 *
 * @since 1.3
 * @param string $html HTML generated with video embeds.
 * @return string
 */
function fusion_wrap_embed_with_div( $html ) {
	$wrapper  = '<div class="video-shortcode">';
	$wrapper .= $html;
	$wrapper .= '</div>';

	return $wrapper;
}

add_filter( 'embed_oembed_html', 'fusion_wrap_embed_with_div', 10 );

// Add jetpack compatibility.
if ( apply_filters( 'is_jetpack_site', false ) ) {
	add_filter( 'video_embed_html', 'fusion_wrap_embed_with_div', 10 );
}

/**
 * Remove post type from the link selector.
 *
 * @since 1.0
 * @param array $query Default query for link selector.
 * @return array $query
 */
function fusion_builder_wp_link_query_args( $query ) {

	// Get array key for the post type 'fusion_template'.
	$post_type_key = array_search( 'fusion_template', $query['post_type'], true );

	// Remove the post type from query.
	if ( $post_type_key ) {
		unset( $query['post_type'][ $post_type_key ] );
	}

	// Return updated query.
	return $query;
}

add_filter( 'wp_link_query_args', 'fusion_builder_wp_link_query_args' );

/**
 * The template for options.
 *
 * @param array $params The parameters for the option.
 */
function fusion_element_options_loop( $params ) {
	$is_builder         = ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() || ( fusion_doing_ajax() && isset( $_POST['fusion_load_nonce'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
	$descriptions_class = '';
	$descriptions_css   = '';

	if ( $is_builder ) {
		$preferences = Fusion_App()->preferences->get_preferences();
		if ( isset( $preferences['descriptions'] ) && 'show' === $preferences['descriptions'] ) {
			$descriptions_class = ' active';
			$descriptions_css   = ' style="display: block;"';
		}
	}
	?>
	<#
	function fusion_display_option( param ) {
		var hasDynamic,
			supportsDynamic,
			hasResponsive,
			parentContent;

		option_value = 'undefined' !== typeof atts.added ? param.value : atts.params[param.param_name];

		if ( param.type == 'select' || param.type == 'multiple_select' || param.type == 'radio_button_set' || param.type == 'checkbox_button_set' || param.type == 'subgroup' ) {
			option_value = ( 'undefined' !== typeof atts.added || '' === atts.params[param.param_name] || 'undefined' === typeof atts.params[ param.param_name ] ) ? param.default : atts.params[ param.param_name ];
		};
		if ( 'raw_textarea' == param.type || 'repeater' == param.type || 'raw_text' == param.type ) {
			try {
				if ( FusionPageBuilderApp.base64Encode( FusionPageBuilderApp.base64Decode( option_value ) ) === option_value ) {
					option_value = FusionPageBuilderApp.base64Decode( option_value );
				}
			} catch(e) {
				console.warn( 'Something went wrong! Error triggered - ' + e );
			}
		}
		if ( 'code' === param.type && 1 === Number( FusionPageBuilderApp.disable_encoding ) && 'undefined' !== typeof option_value ) {
			if ( FusionPageBuilderApp.base64Encode( FusionPageBuilderApp.base64Decode( option_value ) ) === option_value ) {
				option_value = FusionPageBuilderApp.base64Decode( option_value );
			}
		}

		option_value    = _.unescape( option_value );
		parentContent   = 'string' === typeof atts.multi && 'multi_element_parent' === atts.multi && 'element_content' === param.param_name;
		hidden          = 'undefined' !== typeof param.hidden ? ' hidden' : '';
		supportsDynamic = 'undefined' !== param.dynamic_data && true === param.dynamic_data ? true : false;
		hasDynamic      = 'object' === typeof atts.dynamic_params && 'undefined' !== typeof atts.dynamic_params[ param.param_name ] && supportsDynamic;
		childDependency = 'undefined' !== typeof param.child_dependency ? ' has-child-dependency' : '';
		hasResponsive   = 'undefined' !== typeof param.responsive && 'undefined' !== typeof param.responsive.state ? ' has-responsive fusion-' + param.responsive.state : '';
		#>
		<li data-option-id="{{ param.param_name }}" data-option-type="{{ param.type }}" class="fusion-builder-option {{ param.type }}{{ hidden }}{{ childDependency }}{{hasResponsive}}" data-dynamic="{{ hasDynamic }}" data-dynamic-selection="false" data-parent-content="{{ parentContent }}">
			<# if ( ! jQuery( 'body' ).hasClass( 'fusion-builder-live' ) ) { #>
				<div class="option-details">
					<# if ( 'undefined' !== typeof param.heading ) { #>
						<h3>{{ param.heading }}
							<# if ( supportsDynamic ) { #>
								<a class="option-dynamic-content fusiona-dynamic-data" title="<?php esc_attr_e( 'Dynamic Content', 'fusion-builder' ); ?>"></a>
							<# } #>
						</h3>
					<# }; #>

					<# if ( 'undefined' !== typeof param.description ) { #>
						<p class="description">{{{ param.description }}}</p>
					<# }; #>
				</div>
			<# } else { #>
				<div class="option-details">
					<div class="option-details-inner">
						<# if ( 'undefined' !== typeof param.heading ) { #>
							<h3>
								{{ param.heading }}
							</h3>
							<ul class="fusion-panel-options">
								<li> <a href="JavaScript:void(0);" class="fusion-panel-description<?php echo esc_attr( $descriptions_class ); ?>"><i class="fusiona-question-circle" aria-hidden="true"></i></a> <span class="fusion-elements-option-tooltip fusion-tooltip-description">{{ fusionBuilderText.fusion_panel_desciption_toggle }}</span></li>
								<# if ( 'undefined' !== param.default_option && '' !== param.default_option && param.default_option ) { #>
									<li><a href="JavaScript:void(0);"><span class="fusion-panel-shortcut" data-fusion-option="{{ param.default_option }}"><i class="fusiona-cog" aria-hidden="true"></i></a><span class="fusion-elements-option-tooltip fusion-tooltip-global-settings"><?php esc_html_e( 'Global Options', 'fusion-builder' ); ?></span></li>
								<# } #>
								<# if ( 'undefined' !== param.to_link && '' !== param.to_link && param.to_link ) { #>
									<li><a href="JavaScript:void(0);"><span class="fusion-panel-shortcut" data-fusion-option="{{ param.to_link }}"><i class="fusiona-cog" aria-hidden="true"></i></a><span class="fusion-elements-option-tooltip fusion-tooltip-global-settings"><?php esc_html_e( 'Global Options', 'fusion-builder' ); ?></span></li>
								<# } #>
								<# if ( 'undefined' !== typeof param.description && 'undefined' !== typeof param.default && -1 !== param.description.indexOf( 'fusion-builder-default-reset' )  ) { #>
									<li class="fusion-builder-default-reset"> <a href="JavaScript:void(0);" class="fusion-range-default" data-default="{{ param.default }}"><i class="fusiona-undo" aria-hidden="true"></i></a> <span class="fusion-elements-option-tooltip fusion-tooltip-reset-defaults"><?php esc_html_e( 'Reset To Default', 'fusion-builder' ); ?></span></li>
								<# } #>
								<# if ( 'undefined' !== typeof param.preview ) { #>
									<#
										dataType     = 'undefined' !== typeof param.preview.type     ? param.preview.type       : '';
										dataSelector = 'undefined' !== typeof param.preview.selector ? param.preview.selector   : '';
										dataToggle   = 'undefined' !== typeof param.preview.toggle   ? param.preview.toggle     : '';
										dataAppend   = 'undefined' !== typeof param.preview.append   ? param.preview.append     : '';
									#>
									<li><a class="option-preview-toggle" href="JavaScript:void(0);" aria-label="<?php esc_attr_e( 'Preview', 'fusion-builder' ); ?>" data-type="{{ dataType }}" data-selector="{{ dataSelector }}" data-toggle="{{ dataToggle }}" data-append="{{ dataAppend }}"><i class="fas fusiona-eye" aria-hidden="true"></i></a><span class="fusion-elements-option-tooltip fusion-tooltip-preview"><?php esc_html_e( 'Preview', 'fusion-builder' ); ?></span></li>
								<# }; #>
							</ul>
						<# }; #>
					</div>

					<# if ( 'undefined' !== typeof param.description ) { #>
						<p class="description"<?php echo $descriptions_css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>{{{ param.description }}}</p>
					<# }; #>
				</div>
			<# } #>

			<div class="option-field fusion-builder-option-container">
				<?php
				$field_types = [
					'textarea',
					'textfield',
					'range',
					'colorpickeralpha',
					'colorpicker',
					'column_width',
					'select',
					'upload',
					'uploadfile',
					'uploadattachment',
					'tinymce',
					'iconpicker',
					'multiple_select',
					'multiple_upload',
					'checkbox_button_set',
					'subgroup',
					'radio_button_set',
					'radio_image_set',
					'link_selector',
					'date_time_picker',
					'upload_images',
					'dimension',
					'code',
					'raw_textarea',
					'raw_text',
					'repeater',
					'sortable_text',
					'form_options',
					'fusion_logics',
					'sortable',
					'connected_sortable',
					'info',
					'typography',
					'ajax_select',
					'image_focus_point',
					'toggle',
				];

				$fields = apply_filters( 'fusion_builder_fields', $field_types );
				?>
				<?php foreach ( $fields as $field_type ) : ?>
					<?php if ( is_array( $field_type ) && ! empty( $field_type ) ) : ?>
						<# if ( '<?php echo $field_type[0]; // phpcs:ignore WordPress.Security.EscapeOutput ?>' == param.type ) { #>
						<?php include wp_normalize_path( $field_type[1] ); ?>
						<# }; #>
					<?php else : ?>
					<# if ( '<?php echo $field_type; // phpcs:ignore WordPress.Security.EscapeOutput ?>' == param.type ) { #>
						<?php include FUSION_LIBRARY_PATH . '/inc/fusion-app/templates/options/' . str_replace( '_', '-', $field_type ) . '.php'; ?>
					<# }; #>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>

			<# if ( supportsDynamic ) { #>
			<div class="fusion-dynamic-content">
				<?php include FUSION_BUILDER_PLUGIN_DIR . '/front-end/templates/dynamic-data.php'; ?>
			</div>
			<div class="fusion-dynamic-selection"></div>
			<# } #>
		</li>
	<# } #>
	<ul class="fusion-builder-module-settings {{ atts.element_type }}">
		<#
		var SubGroup,
			activeSubGroup;
		_.each( <?php echo $params; // phpcs:ignore WordPress.Security.EscapeOutput ?>, function( param, index ) {
			if ( 'subgroup' !== param.type ) {
				fusion_display_option( param );
			} else {
				SubGroup = param.default;
				fusion_display_option( param );
				_.each( param.subgroups, function( subgroup, tab ) {
					activeSubGroup = tab === SubGroup ? ' active' : '';
					#>
					<ul class="fusion-subgroup-content fusion-subgroup-{{tab}}{{activeSubGroup}}" data-group="{{param.param_name}}">
						<#
						_.each( subgroup, function( item ) {

							fusion_display_option( item );
						} );
						#>
					</ul>
					<#
				} );
			}
		} ); #>
	</ul>
	<?php
}

/**
 * Checks if on an editor page.
 *
 * @since 2.0
 * @return boolean Whether or not it is a fusion editor page.
 */
function is_fusion_editor() {
	global $pagenow;
	return ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() );
}

/**
 * The template for preference options.
 *
 * @param array $params The parameters for the option.
 */
function fusion_builder_preferences_loop( $params ) {
	$preferences        = Fusion_App()->preferences->get_preferences();
	$descriptions_class = '';
	$descriptions_css   = '';

	if ( isset( $preferences['descriptions'] ) && 'show' === $preferences['descriptions'] ) {
		$descriptions_class = ' active';
		$descriptions_css   = ' style="display: block;"';
	}
	?>
		<# _.each( <?php echo $params; // phpcs:ignore WordPress.Security.EscapeOutput ?>, function(param) { #>
			<# option_value = _.unescape(param.default); #>

			<li data-option-id="{{ param.param_name }}" class="fusion-builder-option {{ param.type }}">

				<div class="option-details">
					<div class="option-details-inner">
						<# if ( 'undefined' !== typeof param.heading ) { #>
							<h3>{{ param.heading }}</h3>
							<ul class="fusion-panel-options">
								<li> <a href="JavaScript:void(0);" class="fusion-panel-description<?php echo esc_attr( $descriptions_class ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"><i class="fusiona-question-circle" aria-hidden="true"></i></a> <span class="fusion-elements-option-tooltip fusion-tooltip-description">{{ fusionBuilderText.fusion_panel_desciption_toggle }}</span></li>
							</ul>
						<# }; #>
					</div>

					<# if ( 'undefined' !== typeof param.description ) { #>
						<p class="description"<?php echo $descriptions_css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>{{{ param.description }}}</p>
					<# }; #>
				</div>

				<div class="option-field fusion-builder-option-container">
					<?php
					$field_types = [
						'textarea',
						'textfield',
						'range',
						'colorpickeralpha',
						'colorpicker',
						'column_width',
						'select',
						'upload',
						'uploadfile',
						'uploadattachment',
						'tinymce',
						'iconpicker',
						'multiple_select',
						'multiple_upload',
						'checkbox_button_set',
						'subgroup',
						'radio_button_set',
						'radio_image_set',
						'link_selector',
						'date_time_picker',
						'upload_images',
						'dimension',
						'code',
						'raw_textarea',
						'raw_text',
						'repeater',
						'sortable',
						'sortable_text',
						'connected_sortable',
						'info',
					];

					$fields = apply_filters( 'fusion_builder_fields', $field_types );
					?>
					<?php foreach ( $fields as $field_type ) : ?>
						<?php if ( is_array( $field_type ) && ! empty( $field_type ) ) : ?>
							<# if ( '<?php echo $field_type[0]; // phpcs:ignore WordPress.Security.EscapeOutput ?>' == param.type ) { #>
							<?php include wp_normalize_path( $field_type[1] ); ?>
							<# }; #>
						<?php else : ?>
						<# if ( '<?php echo $field_type; // phpcs:ignore WordPress.Security.EscapeOutput ?>' == param.type ) { #>
							<?php include FUSION_LIBRARY_PATH . '/inc/fusion-app/templates/options/' . str_replace( '_', '-', $field_type ) . '.php'; ?>
						<# }; #>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			</li>

		<# } ); #>
	<?php
}

/**
 * Get an array of widgets.
 *
 * @since 2.2.0
 * @return array
 */
function fusion_get_available_widgets() {
	$widgets            = [];
	$widgets['default'] = __( 'Select a widget', 'fusion-builder' );
	$widget_data        = fusion_get_widget_data( false );

	foreach ( $widget_data as $widget ) {
		$widgets[ $widget['title'] ] = $widget['name'];
	}

	return $widgets;
}

/**
 * Fetch widget types and their forms.
 *
 * @access public
 * @since 2.0.0
 * @param bool $include_form Whether we want to include the form or not.
 * @return array
 */
function fusion_get_widget_data( $include_form = true ) {
	global $wp_widget_factory;

	foreach ( $wp_widget_factory->widgets as $class => $widget ) {
		$name        = $widget->name;
		$description = isset( $widget->widget_options['description'] ) ? $widget->widget_options['description'] : '';
		$classname   = $widget->widget_options['classname'];
		$id          = $widget->id_base;

		$widget_data[ $class ] = [
			'name'        => $name,
			'description' => $description,
			'classname'   => $classname,
			'id'          => $id,
			'title'       => $class,
		];

		if ( $include_form ) {
			$widget_class = clone $widget;
			$widget_class->_set( '' );
			$settings = $widget_class->get_settings( 'wp' );

			ob_start();
			$widget_class->form( $settings );
			$content = ob_get_clean();

			$widget_data[ $class ]['form'] = str_replace( ':</label>', '</label>', $content );
		}
	}

	return $widget_data;
}

/**
 * Get correct float notation for CSS output.
 *
 * @since 3.0.2
 * @param float $float The float to convert.
 * @return string The converted float as string.
 */
function fusion_i18_float_to_string( $float ) {
	$float         = (string) $float;
	$locale_values = localeconv();
	$search        = [
		$locale_values['decimal_point'],
		$locale_values['mon_decimal_point'],
		$locale_values['thousands_sep'],
		$locale_values['mon_thousands_sep'],
		$locale_values['currency_symbol'],
		$locale_values['int_curr_symbol'],
	];
	$replace       = [ '.', '.', '', '', '', '' ];

	return str_replace( $search, $replace, $float );
}

/**
 * Send a JSON-Success message.
 *
 * @since 2.2.1
 * @return void
 */
function fusion_get_widget_data_forms() {
	wp_send_json_success( fusion_get_widget_data() );
}
add_action( 'wp_ajax_fusion_get_widget_form', 'fusion_get_widget_data_forms' );

/**
 * Get a post reading time, formatted in correct style.
 *
 * @param WP_Post|int|null $post The post object, id, or null for default global.
 * @param array            $args An array with 2 args, "reading_speed" and "image_reading_speed".
 * @return string The time to read the post, ready for display.
 */
function awb_get_reading_time_for_display( $post, $args = [] ) {
	$reading_time = awb_get_reading_time( $post, $args );

	$decimals_rounding = 1;
	if ( ! empty( $args['use_decimal_precision'] ) && 'no' === $args['use_decimal_precision'] ) {
		$decimals_rounding = 0;
	}

	// Remove decimal if is 0.
	if ( 1 === $decimals_rounding && ( (string) round( $reading_time, 0 ) ) === ( (string) round( $reading_time, 1 ) ) ) {
		$decimals_rounding = 0;
	}

	return number_format_i18n( $reading_time, $decimals_rounding );
}

/**
 * Get a post reading time.
 *
 * @param WP_Post|int|null $post The post object, id, or null for default global.
 * @param array            $args An array with 2 args, "reading_speed" and "image_reading_speed".
 * @return float|int The time to read the post.
 */
function awb_get_reading_time( $post, $args = [] ) {
	if ( -99 === $post || '-99' === $post ) {
		$post = Fusion_Dummy_Post::get_dummy_post();
	} else {
		$post = get_post( $post );
	}

	if ( ! $post ) {
		return 0;
	}

	if ( empty( $args['reading_speed'] ) ) {
		$args['reading_speed'] = 200;
	}

	if ( empty( $args['image_reading_speed'] ) ) {
		$args['image_reading_speed'] = 0.05;
	}

	$post_content = $post->post_content;
	// Removes the shortcodes.
	$post_content_wihout_shortcodes = preg_replace( '~(?:\[/?)[^/\]]+/?\]~s', '', $post_content );

	$words_count_content = wp_strip_all_tags( $post_content_wihout_shortcodes );
	$words_count         = str_word_count( $words_count_content );

	$reading_speed = intval( $args['reading_speed'] );
	if ( ! is_int( $words_count ) ) {
		return 0;
	}

	// Calculate image reading time.
	$image_additional_time = 0;
	$preg_match_result     = [];
	preg_match_all( '~<img~i', $post_content_wihout_shortcodes, $preg_match_result );
	if ( count( $preg_match_result[0] ) > 0 ) {
		$image_additional_time = count( $preg_match_result[0] ) * $args['image_reading_speed'];
	}

	$decimals_rounding = 1;
	if ( ! empty( $args['use_decimal_precision'] ) && 'no' === $args['use_decimal_precision'] ) {
		$decimals_rounding = 0;
	}

	$reading_time = round( ( $words_count / $reading_speed ) + $image_additional_time, $decimals_rounding );
	return $reading_time;
}

if ( ! function_exists( 'fusion_comment' ) ) {
	/**
	 * The comment template.
	 *
	 * @access public
	 * @param Object     $comment The comment.
	 * @param array      $args    The comment arguments.
	 * @param int|string $depth   The comment depth.
	 */
	function fusion_comment( $comment, $args, $depth ) {
		$defaults = get_query_var( 'fusion_tb_comments_args' );
		?>
		<?php $add_below = ''; ?>
		<li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">
			<div class="the-comment">
				<?php if ( 'hide' !== $defaults['avatar'] ) : ?>
				<div class="avatar"><?php echo get_avatar( $comment, 54 ); ?></div>
				<?php endif; ?>
				<div class="comment-box">
					<div class="comment-author meta">
						<strong><?php echo get_comment_author_link(); ?></strong>
						<?php
						printf(
							/* translators: %1$s: Comment date. %2$s: Comment time. */
							esc_attr__( '%1$s at %2$s', 'fusion-builder' ),
							get_comment_date(), // phpcs:ignore WordPress.Security.EscapeOutput
							get_comment_time() // phpcs:ignore WordPress.Security.EscapeOutput
						);

						edit_comment_link( __( ' - Edit', 'fusion-builder' ), '  ', '' );

						comment_reply_link(
							array_merge(
								$args,
								[
									'reply_text' => __( ' - Reply', 'fusion-builder' ),
									'add_below'  => 'comment',
									'depth'      => $depth,
									'max_depth'  => $args['max_depth'],
								]
							)
						);
						?>
					</div>
					<div class="comment-text">
						<?php if ( '0' == $comment->comment_approved ) : // phpcs:ignore WordPress.PHP.StrictComparisons ?>
							<em><?php esc_attr_e( 'Your comment is awaiting moderation.', 'fusion-builder' ); ?></em>
							<br />
						<?php endif; ?>
						<?php comment_text(); ?>
					</div>
				</div>
			</div>
		<?php
	}
}
if ( ! function_exists( 'fusion_get_link_attributes' ) ) {
	/**
	 * The comment template.
	 *
	 * @access public
	 * @param array $args    The element arguments.
	 * @param array $attr    The element attributes.
	 * @return array
	 */
	function fusion_get_link_attributes( $args, $attr ) {
		if ( ! isset( $args['link_attributes'] ) || '' === $args['link_attributes'] ) {
			return $attr;
		}

		$args['link_attributes'] = html_entity_decode( $args['link_attributes'], ENT_QUOTES );

		preg_match_all( '/\S+=\'.*\'\s/U', $args['link_attributes'] . ' ', $link_attributes );
		$link_attributes = $link_attributes[0];

		// Add fallback if no single quotes are used for the attributes.
		if ( empty( $link_attributes ) ) {
			$link_attributes = explode( ' ', $args['link_attributes'] );
		}

		$brackets_search  = [ '{', '}' ];
		$brackets_replace = [ '[', ']' ];
		$new_attributes   = [];

		foreach ( $link_attributes as $link_attribute ) {
			$link_attribute      = trim( $link_attribute );
			$attribute_key_value = explode( '=', $link_attribute );

			if ( isset( $attribute_key_value[0] ) ) {
				if ( isset( $attribute_key_value[1] ) ) {
					$attribute_key_value[1]                    = str_replace( $brackets_search, $brackets_replace, $attribute_key_value[1] );
					$attribute_key_value[1]                    = trim( html_entity_decode( $attribute_key_value[1], ENT_QUOTES ), "'" );
					$new_attributes[ $attribute_key_value[0] ] = ( isset( $attr[ $attribute_key_value[0] ] ) ) ? $attr[ $attribute_key_value[0] ] . ' ' . $attribute_key_value[1] : $attribute_key_value[1];
				} else {
					$new_attributes[ $attribute_key_value[0] ] = 'valueless_attribute';
				}
			}
		}

		$new_attributes = apply_filters( 'awb_additional_link_attributes', $new_attributes, $args, $attr );
		$attr           = array_merge( $attr, $new_attributes );

		return $attr;
	}
}

if ( ! function_exists( 'fusion_get_svg_from_file' ) ) {
	/**
	 * Get Svg tag from file.
	 *
	 * @access public
	 * @param array $url    File URL.
	 * @param array $args    The element arguments.
	 * @return array
	 */
	function fusion_get_svg_from_file( $url, $args = [] ) {
		if ( ! $url ) {
			return;
		}

		$file = new DOMDocument();
		$file->load( $url );
		$svg = $file->saveHTML( $file->getElementsByTagName( 'svg' )[0] );

		if ( ! $svg ) {
			return [];
		}

		// Get the default height.
		preg_match( '/viewBox="(.*?)"/', $svg, $view_box );
		$view_box = isset( $view_box[1] ) ? explode( ' ', $view_box[1] ) : [];
		$width    = isset( $view_box[2] ) ? $view_box[2] : '';
		$height   = isset( $view_box[3] ) ? $view_box[3] : '';

		// Replace fill wth background color.
		if ( ! empty( $args['background-color'] ) ) {
			$svg = preg_replace( '/fill="(.*?)"/', 'fill="' . Fusion_Color::new_color( $args['background-color'] )->toCss( 'rgba' ) . '"', $svg );
		}

		return [
			'svg'    => $svg,
			'height' => $height,
			'width'  => $width,
		];

	}
}

if ( ! function_exists( 'awb_get_list_table_edit_links' ) ) {
	/**
	 * Gets list table action edit links.
	 *
	 * @access public
	 * @param array $actions Edit actions.
	 * @param array $item    Current item.
	 * @return array
	 */
	function awb_get_list_table_edit_links( $actions, $item ) {
		$options      = get_option( 'fusion_builder_settings', [] );
		$builder_type = isset( $options['enable_builder_ui_by_default'] ) ? $options['enable_builder_ui_by_default'] : 'backend';
		$live_editor  = apply_filters( 'fusion_load_live_editor', true );
		$edit_url     = get_edit_post_link( $item['id'], 'raw' );
		$title        = _draft_or_post_title( $item['id'] );

		$actions['edit'] = sprintf( '<a href="%s">' . esc_html__( 'Edit', 'fusion-builder' ) . '</a>', 'live' === $builder_type && $live_editor ? esc_url_raw( add_query_arg( 'fb-edit', '1', get_the_permalink( $item['id'] ) ) ) : $edit_url );

		if ( $live_editor && 'backend' === $builder_type ) {
			$actions['fusion_builder_live'] = sprintf(
				'<a href="%s" aria-label="%s">%s</a>',
				esc_url_raw( add_query_arg( 'fb-edit', '1', get_the_permalink( $item['id'] ) ) ),
				esc_attr(
					sprintf(
						/* translators: %s: post title */
						__( 'Edit &#8220;%s&#8221; in Avada Live', 'fusion-builder' ),
						$title
					)
				),
				esc_html__( 'Avada Live', 'fusion-builder' )
			);
		} elseif ( 'live' === $builder_type ) {
			$actions['fusion_builder_backend'] = sprintf(
				'<a href="%s" aria-label="%s">%s</a>',
				esc_url( $edit_url ),
				esc_attr(
					sprintf(
						/* translators: %s: post title */
						__( 'Edit &#8220;%s&#8221; in Back-end Builder', 'fusion-builder' ),
						$title
					)
				),
				esc_html__( 'Back-end Builder', 'fusion-builder' )
			);
		}

		return $actions;
	}
}

if ( ! function_exists( 'awb_get_list_table_title' ) ) {
	/**
	 * Gets list table title.
	 *
	 * @access public
	 * @param array $item Current item.
	 * @return string
	 */
	function awb_get_list_table_title( $item ) {
		$options      = get_option( 'fusion_builder_settings', [] );
		$builder_type = isset( $options['enable_builder_ui_by_default'] ) ? $options['enable_builder_ui_by_default'] : 'backend';
		$live_editor  = apply_filters( 'fusion_load_live_editor', true );
		$title        = _draft_or_post_title( $item['id'] );
		$status       = '';

		if ( 'draft' === $item['status'] ) {
			$status = ' &mdash; <span class="post-state">' . ucwords( $item['status'] ) . '</span>';
		}

		$title = sprintf( '<strong><a href="%s">' . $title . '</a>' . $status . '</strong>', 'live' === $builder_type && $live_editor ? esc_url_raw( add_query_arg( 'fb-edit', '1', get_the_permalink( $item['id'] ) ) ) : get_edit_post_link( $item['id'], 'raw' ) );

		return $title;
	}
}

if ( ! function_exists( 'awb_get_new_post_edit_link' ) ) {
	/**
	 * Gets new post edit link.
	 *
	 * @access public
	 * @param int $id Item id.
	 * @return string
	 */
	function awb_get_new_post_edit_link( $id ) {
		$options      = get_option( 'fusion_builder_settings', [] );
		$builder_type = isset( $options['enable_builder_ui_by_default'] ) ? $options['enable_builder_ui_by_default'] : 'backend';
		$live_editor  = apply_filters( 'fusion_load_live_editor', true );

		return 'live' === $builder_type && $live_editor ? esc_url_raw( add_query_arg( 'fb-edit', '1', get_the_permalink( $id ) ) ) : get_edit_post_link( $id, 'raw' );
	}
}


/* Omit closing PHP tag to avoid "Headers already sent" issues. */
