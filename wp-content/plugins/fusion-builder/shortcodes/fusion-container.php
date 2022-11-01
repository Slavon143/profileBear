<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( ! class_exists( 'FusionSC_Container' ) ) {
	/**
	 * Shortcode class.
	 *
	 * @since 1.0
	 */
	class FusionSC_Container extends Fusion_Element {

		/**
		 * The internal container counter.
		 *
		 * @access private
		 * @since 1.3
		 * @var int
		 */
		private $container_counter = 0;

		/**
		 * Counter counter for a specific scope, reset for different layout sections.
		 *
		 * @access private
		 * @since 2.2
		 * @var int
		 */
		private $scope_container_counter = 0;

		/**
		 * The internal container counter for nested.
		 *
		 * @access private
		 * @since 2.2
		 * @var int
		 */
		private $nested_counter = 0;

		/**
		 * The internal container counter for nesting depth.
		 *
		 * @access private
		 * @since 3.8
		 * @var int
		 */
		private $nesting_depth = -1;

		/**
		 * Styles for style block.
		 *
		 * @access protected
		 * @since 3.0
		 * @var string
		 */
		protected $styles = '';


		/**
		 * Whether a container is rendering.
		 *
		 * @access public
		 * @since 2.2
		 * @var bool
		 */
		public $rendering = false;

		/**
		 * The counter for 100% height scroll sections.
		 *
		 * @access private
		 * @since 1.3
		 * @var int
		 */
		private $scroll_section_counter = 0;

		/**
		 * The counter for elements in a 100% height scroll section.
		 *
		 * @access private
		 * @since 1.3
		 * @var int
		 */
		private $scroll_section_element_counter = 1;

		/**
		 * Stores the navigation for a scroll section.
		 *
		 * @access private
		 * @since 1.3
		 * @var array
		 */
		private $scroll_section_navigation = [];

		/**
		 * Scope that the scroll section exists on.
		 *
		 * @access private
		 * @since 2.2
		 * @var mixed
		 */
		private $scroll_section_scope = false;

		/**
		 * Container args for parent if nested.
		 *
		 * @access private
		 * @since 3.0
		 * @var mixed
		 */
		private $parent_args = false;

		/**
		 * Column map for parent container.
		 *
		 * @access public
		 * @since 3.0
		 * @var array
		 */
		public $parent_column_map = [];

		/**
		 * Column map for current container.
		 *
		 * @access public
		 * @since 3.0
		 * @var array
		 */
		public $column_map = [];

		/**
		 * The one, true instance of this object.
		 *
		 * @static
		 * @access private
		 * @since 1.0
		 * @var object
		 */
		private static $instance;

		/**
		 * An array of the shortcode arguments.
		 *
		 * @access public
		 * @since 1.0
		 * @var array
		 */
		public $args;

		/**
		 * An array of the shortcode attributes.
		 *
		 * @access public
		 * @since 3.0
		 * @var array
		 */
		public $atts;

		/**
		 * Data arguments.
		 *
		 * @access public
		 * @since 3.0
		 * @var array
		 */
		public $data;

		/**
		 * Parent data.
		 *
		 * @access private
		 * @since 3.0
		 * @var mixed
		 */
		private $parent_data = false;

		/**
		 * Constructor.
		 *
		 * @access public
		 * @since 1.0
		 */
		public function __construct() {
			parent::__construct();
			add_shortcode( 'fusion_builder_container', [ $this, 'render' ] );

			add_filter( 'fusion_attr_container-shortcode', [ $this, 'attr' ] );
			// Parallax attributes.
			add_filter( 'fusion_attr_container-shortcode-parallax', [ $this, 'parallax_attr' ] );
			// Scroll attributes.
			add_filter( 'fusion_attr_container-shortcode-scroll', [ $this, 'scroll_attr' ] );
			add_filter( 'fusion_attr_container-shortcode-scroll-wrapper', [ $this, 'scroll_wrapper_attr' ] );
			add_filter( 'fusion_attr_container-shortcode-scroll-navigation', [ $this, 'scroll_navigation_attr' ] );
			// Fading Background.
			add_filter( 'fusion_attr_container-shortcode-fading-background', [ $this, 'fading_background_attr' ] );
		}

		/**
		 * Creates or returns an instance of this class.
		 *
		 * @static
		 * @access public
		 * @since 2.2
		 */
		public static function get_instance() {

			// If an instance hasn't been created and set to $instance create an instance and set it to $instance.
			if ( null === self::$instance ) {
				self::$instance = new FusionSC_Container();
			}
			return self::$instance;
		}

		/**
		 * Gets the default values.
		 *
		 * @static
		 * @access public
		 * @since 2.0.0
		 * @return array
		 */
		public static function get_element_defaults() {

			$fusion_settings     = awb_get_fusion_settings();
			$legacy_mode_enabled = 1 === (int) $fusion_settings->get( 'container_legacy_support' ) ? true : false;

			return [
				'admin_label'                           => '',
				'align_content'                         => 'stretch',
				'is_nested'                             => '0', // Variable that simply checks if the current container is a nested one (e.g. from FAQ or blog element).
				'hide_on_mobile'                        => fusion_builder_default_visibility( 'string' ),
				'id'                                    => '',
				'class'                                 => '',
				'status'                                => 'published',
				'publish_date'                          => '',
				'type'                                  => $legacy_mode_enabled ? 'legacy' : 'flex',
				'flex_align_items'                      => 'flex-start',
				'flex_column_spacing'                   => $fusion_settings->get( 'col_spacing' ),
				'flex_justify_content'                  => 'flex-start',
				'min_height'                            => '',
				'min_height_medium'                     => '',
				'min_height_small'                      => '',
				'container_tag'                         => 'div',

				// Background.
				'background_color'                      => $fusion_settings->get( 'full_width_bg_color' ),
				'gradient_start_color'                  => $fusion_settings->get( 'full_width_gradient_start_color' ),
				'gradient_end_color'                    => $fusion_settings->get( 'full_width_gradient_end_color' ),
				'gradient_start_position'               => '0',
				'gradient_end_position'                 => '100',
				'gradient_type'                         => 'linear',
				'radial_direction'                      => 'center',
				'linear_angle'                          => '180',
				'background_image'                      => '',
				'background_position'                   => 'center center',
				'background_repeat'                     => 'no-repeat',
				'background_parallax'                   => 'none',
				'parallax_speed'                        => '0.3',
				'background_blend_mode'                 => 'none',
				'opacity'                               => '100',
				'break_parents'                         => '0',
				'fade'                                  => 'no',
				// 100% height.
				'hundred_percent'                       => 'no',
				'hundred_percent_height'                => 'no',
				'hundred_percent_height_scroll'         => 'no',
				'hundred_percent_height_center_content' => 'no',

				// Padding.
				'padding_top'                           => '',
				'padding_right'                         => '',
				'padding_bottom'                        => '',
				'padding_left'                          => '',
				'padding_top_medium'                    => '',
				'padding_right_medium'                  => '',
				'padding_bottom_medium'                 => '',
				'padding_left_medium'                   => '',
				'padding_top_small'                     => '',
				'padding_right_small'                   => '',
				'padding_bottom_small'                  => '',
				'padding_left_small'                    => '',

				// Margin.
				'margin_top'                            => '0px',
				'margin_bottom'                         => '0px',
				'margin_top_medium'                     => '',
				'margin_bottom_medium'                  => '',
				'margin_top_small'                      => '',
				'margin_bottom_small'                   => '',

				// Border.
				'border_color'                          => $fusion_settings->get( 'full_width_border_color' ),
				'border_size'                           => '', // Backwards-compatibility.
				'border_sizes_top'                      => $fusion_settings->get( 'full_width_border_sizes', 'top' ),
				'border_sizes_bottom'                   => $fusion_settings->get( 'full_width_border_sizes', 'bottom' ),
				'border_sizes_left'                     => $fusion_settings->get( 'full_width_border_sizes', 'left' ),
				'border_sizes_right'                    => $fusion_settings->get( 'full_width_border_sizes', 'right' ),
				'border_style'                          => 'solid',

				'equal_height_columns'                  => 'no',
				'data_bg_height'                        => '',
				'data_bg_width'                         => '',
				'enable_mobile'                         => 'no',
				'menu_anchor'                           => '',
				'link_color'                            => '',
				'link_hover_color'                      => '',
				'z_index'                               => '',
				'overflow'                              => '',

				// Render logics.
				'render_logics'                         => '',

				// Lazy loading.
				'skip_lazy_load'                        => '',

				// Absolute.
				'absolute'                              => 'off',
				'absolute_devices'                      => 'small,medium,large',

				// Sticky.
				'sticky'                                => 'off',
				'sticky_devices'                        => fusion_builder_default_visibility( 'string' ),
				'sticky_background_color'               => '',
				'sticky_height'                         => '',
				'sticky_offset'                         => 0,
				'sticky_transition_offset'              => 0,
				'scroll_offset'                         => 0,

				// Video Background.
				'video_mp4'                             => '',
				'video_webm'                            => '',
				'video_ogv'                             => '',
				'video_loop'                            => 'yes',
				'video_mute'                            => 'yes',
				'video_preview_image'                   => '',
				'overlay_color'                         => '',
				'overlay_opacity'                       => '0.5',
				'video_url'                             => '',
				'video_loop_refinement'                 => '',
				'video_aspect_ratio'                    => '16:9',

				// Background Pattern.
				'pattern_bg'                            => '',
				'pattern_custom_bg'                     => '',
				'pattern_bg_color'                      => '',
				'pattern_bg_opacity'                    => '',
				'pattern_bg_size'                       => '',
				'pattern_bg_blend_mode'                 => '',
				'pattern_bg_style'                      => '',

				// Background Mask.
				'mask_bg'                               => '',
				'mask_custom_bg'                        => '',
				'mask_bg_color'                         => '',
				'mask_bg_accent_color'                  => '',
				'mask_bg_opacity'                       => '',
				'mask_bg_blend_mode'                    => '',
				'mask_bg_style'                         => '',
				'mask_bg_transform'                     => '',

				// Animations.
				'animation_type'                        => '',
				'animation_direction'                   => 'left',
				'animation_speed'                       => '0.3',
				'animation_offset'                      => $fusion_settings->get( 'animation_offset' ),

				// Box-shadow.
				'box_shadow'                            => '',
				'box_shadow_blur'                       => '',
				'box_shadow_color'                      => '',
				'box_shadow_horizontal'                 => '',
				'box_shadow_spread'                     => '',
				'box_shadow_style'                      => '',
				'box_shadow_vertical'                   => '',

				// Filters.
				'filter_hue'                            => '0',
				'filter_saturation'                     => '100',
				'filter_brightness'                     => '100',
				'filter_contrast'                       => '100',
				'filter_invert'                         => '0',
				'filter_sepia'                          => '0',
				'filter_opacity'                        => '100',
				'filter_blur'                           => '0',
				'filter_hue_hover'                      => '0',
				'filter_saturation_hover'               => '100',
				'filter_brightness_hover'               => '100',
				'filter_contrast_hover'                 => '100',
				'filter_invert_hover'                   => '0',
				'filter_sepia_hover'                    => '0',
				'filter_opacity_hover'                  => '100',
				'filter_blur_hover'                     => '0',
			];
		}

		/**
		 * Maps settings to param variables.
		 *
		 * @static
		 * @access public
		 * @since 2.0.0
		 * @return array
		 */
		public static function settings_to_params() {
			return [
				'full_width_border_color'         => 'border_color',
				'full_width_border_sizes[top]'    => 'border_sizes_top',
				'full_width_border_sizes[bottom]' => 'border_sizes_bottom',
				'full_width_border_sizes[left]'   => 'border_sizes_left',
				'full_width_border_sizes[right]'  => 'border_sizes_right',
				'full_width_bg_color'             => 'background_color',
				'full_width_gradient_start_color' => 'gradient_start_color',
				'full_width_gradient_end_color'   => 'gradient_end_color',
				'col_spacing'                     => 'flex_column_spacing',
				'lazy_load'                       => 'lazy_load',
			];
		}

		/**
		 * Used to set any other variables for use on front-end editor template.
		 *
		 * @static
		 * @access public
		 * @since 2.0.0
		 * @return array
		 */
		public static function get_element_extras() {
			$fusion_settings = awb_get_fusion_settings();
			return [
				'container_padding_100'     => $fusion_settings->get( 'container_padding_100' ),
				'container_padding_default' => $fusion_settings->get( 'container_padding_default' ),
				'container_legacy_support'  => $fusion_settings->get( 'container_legacy_support' ),
			];
		}

		/**
		 * Maps settings to extra variables.
		 *
		 * @static
		 * @access public
		 * @since 2.0.0
		 * @return array
		 */
		public static function settings_to_extras() {

			return [
				'container_padding_100'     => 'container_padding_100',
				'container_padding_default' => 'container_padding_default',
				'container_legacy_support'  => 'container_legacy_support',
			];
		}

		/**
		 * Check if container is flex or not.
		 *
		 * @access public
		 * @since 3.0
		 * @return bool
		 */
		public function is_flex() {
			$fusion_settings = awb_get_fusion_settings();
			$is_flex         = 1 !== (int) $fusion_settings->get( 'container_legacy_support' ) || ( is_array( $this->args ) && isset( $this->args['type'] ) && 'flex' === $this->args['type'] );
			$is_flex         = apply_filters( 'fusion_container_is_flex', $is_flex );
			return $is_flex;
		}

		/**
		 * Set map of columns within this container.
		 *
		 * @access public
		 * @since 3.0
		 * @param string $content The content.
		 * @return string
		 */
		public function set_column_map( $content ) {

			$this->column_map = [
				'fusion_builder_column'       => [],
				'fusion_builder_column_inner' => [],
			];

			$needles = [
				[
					'row_opening'    => '[fusion_builder_row]',
					'row_closing'    => '[/fusion_builder_row]',
					'column_opening' => '[fusion_builder_column ',
				],
				[
					'row_opening'    => '[fusion_builder_row_inner]',
					'row_closing'    => '[/fusion_builder_row_inner]',
					'column_opening' => '[fusion_builder_column_inner ',
				],
			];

			// Add globals into content.
			$content = apply_filters( 'fusion_add_globals', $content, 0 );

			$column_opening_positions_index = [];

			foreach ( $needles as $needle ) {
				$column_array                 = [];
				$last_pos                     = -1;
				$positions                    = [];
				$row_index                    = -1;
				$row_shortcode_name_length    = strlen( $needle['row_opening'] );
				$column_shortcode_name_length = strlen( $needle['column_opening'] );

				// Get all positions of [fusion_builder_row shortcode.
				while ( ( $last_pos = strpos( $content, $needle['row_opening'], $last_pos + 1 ) ) !== false ) { // phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
					$positions[] = $last_pos;
				}

				// For each row.
				foreach ( $positions as $position ) {

					$row_closing_position = strpos( $content, $needle['row_closing'], $position );

					// Search within this range/row.
					$range = $row_closing_position - $position + 1;
					// Row content.
					$row_content              = substr( $content, $position + strlen( $needle['row_opening'] ), $range );
					$original_row_content     = $row_content;
					$row_last_pos             = -1;
					$row_position_change      = 0;
					$element_positions        = [];
					$container_column_counter = 0;
					$column_index             = 0;
					$row_index++;
					$element_position_change = 0;
					$last_column_was_full    = false;

					while ( ( $row_last_pos = strpos( $row_content, $needle['column_opening'], $row_last_pos + 1 ) ) !== false ) { // phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
						$element_positions[] = $row_last_pos;
					}

					$number_of_elements = count( $element_positions );

					// Loop through each column.
					foreach ( $element_positions as $key => $element_position ) {
						$column_index++;

						// Get all parameters from column.
						$end_position = strlen( $row_content ) - 1;
						if ( isset( $element_position[ $key + 1 ] ) ) {
							$end_position = $element_position[ $key + 1 ];
						}

						$column_values = shortcode_parse_atts( strstr( substr( $row_content, $element_position + $column_shortcode_name_length, $end_position ), ']', true ) );

						// Check that type parameter is found, if so calculate row and set spacing to array.
						if ( isset( $column_values['type'] ) ) {
							$column_type               = explode( '_', $column_values['type'] );
							$column_width              = isset( $column_type[1] ) ? intval( $column_type[0] ) / intval( $column_type[1] ) : $column_type[0] / 100;
							$container_column_counter += $column_width;
							$column_spacing            = ( isset( $column_values['spacing'] ) ) ? $column_values['spacing'] : '4%';

							// First column.
							if ( 0 === $key ) {
								if ( 0 < $row_index && ! empty( $column_array[ $row_index - 1 ] ) ) {
									// Get column index of last column of last row.
									end( $column_array[ $row_index - 1 ] );
									$previous_row_last_column = key( $column_array[ $row_index - 1 ] );

									// Add "last" to the last column of previous row.
									if ( false !== strpos( $column_array[ $row_index - 1 ][ $previous_row_last_column ][1], 'first' ) ) {
										$column_array[ $row_index - 1 ][ $previous_row_last_column ] = [ 'no', 'first_last' ];
									} else {
										$column_array[ $row_index - 1 ][ $previous_row_last_column ] = [ 'no', 'last' ];
									}
								}

								// If column is full width it is automatically first and last of row.
								if ( 1 === $column_width ) {
									$column_array[ $row_index ][ $column_index ] = [ 'no', 'first_last' ];
								} else {
									$column_array[ $row_index ][ $column_index ] = [ $column_spacing, 'first' ];
								}
							} elseif ( 0 === $container_column_counter - $column_width ) { // First column of a row.
								if ( 1 === $column_width ) {
									$column_array[ $row_index ][ $column_index ] = [ 'no', 'first_last' ];
								} else {
									$column_array[ $row_index ][ $column_index ] = [ $column_spacing, 'first' ];
								}
							} elseif ( 1 === $container_column_counter ) { // Column fills remaining space in the row exactly.
								// If column is full width it is automatically first and last of row.
								if ( 1 === $column_width ) {
									$column_array[ $row_index ][ $column_index ] = [ 'no', 'first_last' ];
								} else {
									$column_array[ $row_index ][ $column_index ] = [ 'no', 'last' ];
								}
							} elseif ( 1 < $container_column_counter ) { // Column overflows the current row.
								$container_column_counter = $column_width;
								$row_index++;

								// Get column index of last column of last row.
								end( $column_array[ $row_index - 1 ] );
								$previous_row_last_column = key( $column_array[ $row_index - 1 ] );

								// Add "last" to the last column of previous row.
								if ( false !== strpos( $column_array[ $row_index - 1 ][ $previous_row_last_column ][1], 'first' ) ) {
									$column_array[ $row_index - 1 ][ $previous_row_last_column ] = [ 'no', 'first_last' ];
								} else {
									$column_array[ $row_index - 1 ][ $previous_row_last_column ] = [ 'no', 'last' ];
								}

								// If column is full width it is automatically first and last of row.
								if ( 1 === $column_width ) {
									$column_array[ $row_index ][ $column_index ] = [ 'no', 'first_last' ];
								} else {
									$column_array[ $row_index ][ $column_index ] = [ $column_spacing, 'first' ];
								}
							} elseif ( $number_of_elements - 1 === $key ) { // Last column.
								// If column is full width it is automatically first and last of row.
								if ( 1 === $column_width ) {
									$column_array[ $row_index ][ $column_index ] = [ 'no', 'first_last' ];
								} else {
									$column_array[ $row_index ][ $column_index ] = [ 'no', 'last' ];
								}
							} else {
								$column_array[ $row_index ][ $column_index ] = [ $column_spacing, 'default' ];
							}
						}

						$this->column_map[ str_replace( [ '[', ' ' ], '', $needle['column_opening'] ) ] = $column_array;

						$column_opening_positions_index[] = [ $position + $element_position + $row_shortcode_name_length + $column_shortcode_name_length, $row_index . '_' . $column_index ];

					}
				}
			}

			// If not column spacing is set, check if all columns in container have no spacing and if so set that to container.
			if ( ! isset( $this->atts['flex_column_spacing'] ) ) {
				$empty_column_spacing = true;
				if ( ! empty( $this->column_map ) ) {
					foreach ( $this->column_map as $map ) {
						if ( ! empty( $map ) ) {
							foreach ( $map as $row ) {
								if ( ! empty( $row ) ) {
									foreach ( $row as $column ) {
										if ( isset( $column[1] ) && false !== strpos( $column[1], 'last' ) ) {
											continue;
										}

										if ( 'no' !== $column[0] && 0 !== $column[0] && '0' !== $column[0] ) {
											$empty_column_spacing = false;
											break;
										}
									}
								}
							}
						}
					}
				}

				if ( $empty_column_spacing ) {
					$this->args['flex_column_spacing'] = '0px';
				}
			}

			/*
			 * Make sure columns and inner columns are sorted correctly for index insertion.
			 * Use the start index on shortcode in the content string as order value.
			 */
			usort( $column_opening_positions_index, [ $this, 'column_opening_positions_index_substract' ] );

			// Add column index and if in widget also the widget ID to the column shortcodes.
			foreach ( array_reverse( $column_opening_positions_index ) as $position ) {
				$content = substr_replace( $content, 'row_column_index="' . $position[1] . '" ', $position[0], 0 );
			}

			return $content;
		}

		/**
		 * Helper function that substracts values.
		 * Added for compatibility with older PHP versions.
		 *
		 * @access public
		 * @since 1.0.3
		 * @param array $a 1st value.
		 * @param array $b 2nd value.
		 * @return int
		 */
		public function column_opening_positions_index_substract( $a, $b ) {
			return $a[0] - $b[0];
		}

		/**
		 * Returns column spacing.
		 *
		 * @access public
		 * @since 3.0
		 * @return string
		 */
		public function get_column_spacing() {
			return isset( $this->args['flex_column_spacing'] ) ? $this->args['flex_column_spacing'] : 0;
		}

		/**
		 * Returns column alignment.
		 *
		 * @access public
		 * @since 3.0
		 * @return string
		 */
		public function get_column_alignment() {
			return $this->args['flex_align_items'];
		}

		/**
		 * Returns column justification.
		 *
		 * @access public
		 * @since 3.0
		 * @return string
		 */
		public function get_column_justification() {
			return $this->args['flex_justify_content'];
		}
		/**
		 * Sets gloal container args.
		 *
		 * @access public
		 * @since 3.0
		 * @return void
		 */
		public function set_container_count_data() {
			global $global_container_count, $fusion_settings;

			// If we are inside another container render, then we count nested.
			$rendering = $this->rendering;
			$this->nesting_depth++;

			if ( ! $this->rendering ) {
				$this->scope_container_counter++;
				$this->container_counter++;
				$this->rendering         = true;
				$this->nested_counter    = 0;
				$this->parent_args       = false;
				$this->parent_data       = false;
				$this->parent_column_map = [];
			} else {
				$this->nested_counter++;

				// If not set yet, set args as parent args.
				if ( ! $this->parent_args ) {
					$this->parent_args = $this->args;
				}
				if ( ! $this->parent_data ) {
					$this->parent_data = $this->data;
				}
				if ( empty( $this->parent_column_map ) ) {
					$this->parent_column_map = $this->column_map;
				}
			}

			$this->data['is_nested']            = $rendering ? true : false;
			$this->data['container_counter']    = $rendering ? $this->container_counter . '-' . $this->nested_counter : $this->container_counter;
			$this->data['last_container']       = $rendering ? $global_container_count === $this->nested_counter : $global_container_count === $this->scope_container_counter;
			$this->data['scroll_scope_matches'] = $rendering ? 'parent' !== $this->scroll_section_scope : 'nested' !== $this->scroll_section_scope;
			$this->data['is_content_contained'] = $rendering ? 'no' === $this->args['hundred_percent'] : false;

			// If ajax this will prevent CID colision.
			if ( isset( $_POST['cid'] ) ) { // phpcs:disable WordPress.Security.NonceVerification.Missing
				$this->set_element_id( $this->container_counter );
				$this->data['container_counter'] = $rendering ? $this->element_id . '-' . $this->nested_counter : $this->element_id;
			}

			// Fixes selectors duplication for terms & conditions section on checkout page.
			if ( class_exists( 'WooCommerce' ) && is_checkout() && fusion_library()->get_page_id() !== intval( get_option( 'woocommerce_checkout_page_id' ) ) ) {
				$this->set_element_id( $this->container_counter . '_' . fusion_library()->get_page_id() );
				$this->data['container_counter'] = $rendering ? $this->element_id . '-' . $this->nested_counter : $this->element_id;
			}

			// Reset styles.
			$this->styles = '';

			// Last top level, reset the scoped counters.
			if ( ! $rendering && $this->data['last_container'] ) {
				$global_container_count        = false;
				$this->scope_container_counter = 0;
			}
		}

		/**
		 * Sets the args from the attributes.
		 *
		 * @access public
		 * @since 3.0
		 * @param array $atts Element attributes.
		 * @return void
		 */
		public function set_args( $atts ) {
			$fusion_settings = awb_get_fusion_settings();
			$atts            = fusion_section_deprecated_args( $atts );

			$args = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $atts, 'fusion_builder_container' );

			$this->atts = $atts;
			$this->args = $args;
		}

		/**
		 * Is the container nested within another container.
		 *
		 * @access public
		 * @since 3.1
		 * @return bool
		 */
		public function is_nested() {
			return isset( $this->data['is_nested'] ) ? $this->data['is_nested'] : false;
		}

		/**
		 * Returns the container nesting depth.
		 *
		 * @access public
		 * @since 3.8
		 * @return int The nesting depth.
		 */
		public function get_nesting_depth() {
			return $this->nesting_depth;
		}


		/**
		 * Legacy inherit mode. When old containers are now using flex.
		 *
		 * @access public
		 * @since 3.0
		 * @param array $atts The attributes set on element.
		 * @return void
		 */
		public function legacy_inherit( $atts ) {
			// No column align, but equal heights is on, set to stretch.
			if ( ! isset( $atts['flex_align_items'] ) && 'yes' === $this->args['equal_height_columns'] ) {
				$this->args['flex_align_items'] = 'stretch';
			}

			// No align content, but it is 100% height and centered.
			if ( ! isset( $atts['align_content'] ) && 'yes' === $this->args['hundred_percent_height'] && 'yes' === $this->args['hundred_percent_height_center_content'] ) {
				$this->args['align_content'] = 'center';
			}
		}

		/**
		 * Validate the arguments into correct format.
		 *
		 * @access public
		 * @since 3.0
		 * @return void
		 */
		public function validate_args() {
			$fusion_settings = awb_get_fusion_settings();

			$c_page_id = fusion_library()->get_page_id();

			// Correct radial direction params.
			$new_radial_direction_names = [
				'bottom'        => 'center bottom',
				'bottom center' => 'center bottom',
				'left'          => 'left center',
				'right'         => 'right center',
				'top'           => 'center top',
				'center'        => 'center center',
				'center left'   => 'left center',
			];
			if ( array_key_exists( $this->args['radial_direction'], $new_radial_direction_names ) ) {
				$this->args['radial_direction'] = $new_radial_direction_names [ $this->args['radial_direction'] ];
			}

			if ( false !== strpos( $this->args['background_image'], 'https://placehold.it/' ) ) {
				$dimensions = str_replace( 'x', '', str_replace( 'https://placehold.it/', '', $this->args['background_image'] ) );
				if ( is_numeric( $dimensions ) ) {
					$this->args['background_image'] = $this->args['background_image'] . '/333333/ffffff/';
				}
			}

			// Get correct container padding.
			$paddings = [ 'top', 'right', 'bottom', 'left' ];

			foreach ( $paddings as $padding ) {
				$padding_name = 'padding_' . $padding;

				if ( '' === $this->args[ $padding_name ] ) {

					// TO padding.
					$this->args[ $padding_name ] = $fusion_settings->get( 'container_padding_default', $padding );
					$is_hundred_percent_template = apply_filters( 'fusion_is_hundred_percent_template', false, $c_page_id );
					if ( $is_hundred_percent_template ) {
						$this->args[ $padding_name ] = $fusion_settings->get( 'container_padding_100', $padding );
					}
				}
				$this->args[ $padding_name ] = fusion_library()->sanitize->get_value_with_unit( $this->args[ $padding_name ] );
			}

			// Disable parallax and fade for sticky mode.
			if ( 'on' === $this->args['sticky'] ) {
				$this->args['background_parallax'] = 'none';
				$this->args['fade']                = 'no';
			}
		}

		/**
		 * Sets the extra args.
		 *
		 * @access public
		 * @since 3.0
		 * @return void
		 */
		public function set_extra_args() {
			$fusion_settings = awb_get_fusion_settings();
			$c_page_id       = fusion_library()->get_page_id();

			$this->args['lazy_load']        = ( 'avada' === $fusion_settings->get( 'lazy_load' ) && ! is_feed() && 'skip' !== $this->args['skip_lazy_load'] ) ? true : false;
			$this->args['lazy_load']        = ! $this->args['background_image'] || '' === $this->args['background_image'] ? false : $this->args['lazy_load'];
			$this->args['video_bg']         = false;
			$this->args['width_100']        = false;
			$this->args['background_color'] = $this->args['background_color'];
			if ( '' !== $this->args['overlay_color'] ) {
				$overlay_alpha                  = ( 1 < $this->args['overlay_opacity'] ) ? $this->args['overlay_opacity'] / 100 : $this->args['overlay_opacity'];
				$this->args['background_color'] = Fusion_Color::new_color( $this->args['overlay_color'] )->get_new( 'alpha', $overlay_alpha )->to_css( 'rgba' );
			}
			$this->args['css_id'] = '';

			$this->args['alpha_background_color']     = 1;
			$this->args['alpha_gradient_start_color'] = 1;
			$this->args['alpha_gradient_end_color']   = 1;
			if ( class_exists( 'Fusion_Color' ) ) {
				$this->args['alpha_background_color']     = Fusion_Color::new_color( $this->args['background_color'] )->alpha;
				$this->args['alpha_gradient_start_color'] = Fusion_Color::new_color( $this->args['gradient_start_color'] )->alpha;
				$this->args['alpha_gradient_end_color']   = Fusion_Color::new_color( $this->args['gradient_end_color'] )->alpha;
			}
			$this->args['is_gradient_color'] = ( ! empty( $this->args['gradient_start_color'] ) && 0 !== $this->args['alpha_gradient_start_color'] ) || ( ! empty( $this->args['gradient_end_color'] ) && 0 !== $this->args['alpha_gradient_end_color'] ) ? true : false;

			$is_hundred_percent_template = apply_filters( 'fusion_is_hundred_percent_template', false, $c_page_id );
			if ( $is_hundred_percent_template ) {
				$this->args['width_100'] = true;
			}
		}

		/**
		 * Container shortcode.
		 *
		 * @access public
		 * @since 1.0
		 * @param array  $atts    The attributes array.
		 * @param string $content The content.
		 * @return string
		 */
		public function render( $atts, $content = '' ) {
			global $global_container_count;

			// If container is no published, return early.
			if ( ! apply_filters( 'fusion_is_container_viewable', $this->is_container_viewable( $atts ), $atts ) ) {
				$global_container_count--;
				return;
			}

			if ( ! Fusion_Builder_Conditional_Render_Helper::should_render( $atts ) ) {
				$global_container_count--;
				return;
			}

			$this->set_container_count_data();

			$this->set_args( $atts );

			// If type is not set, or legacy is set, we calculate column map.
			if ( ! isset( $atts['type'] ) || 'legacy' === $atts['type'] ) {
				$content = $this->set_column_map( $content );
			} else {
				// Reset map for further columns.
				$this->column_map = [];
			}

			$this->legacy_inherit( $atts );

			$this->validate_args();

			$this->set_extra_args();

			$this->set_container_video_data();

			$this->set_container_scroll_data();

			$this->container_styles();
			// Sets styles for responsive options.
			if ( $this->is_flex() ) {
				$this->set_responsive_container_styles();
			}

			$this->update_fusion_fwc_type();

			// Save custom CSS for latter.
			$style_block = '' !== $this->styles ? '<style type="text/css">' . $this->styles . '</style>' : '';
			$html        = '';

			// Scroll section container.
			$scroll_navigation      = '';
			$scroll_section_wrapper = '';
			if ( 'yes' === $this->args['hundred_percent_height'] && 'yes' === $this->args['hundred_percent_height_scroll'] && $this->data['scroll_scope_matches'] ) {
				if ( 1 === $this->scroll_section_element_counter ) {
					$html = '<div ' . FusionBuilder::attributes( 'container-shortcode-scroll' ) . ' >';
				}
				$scroll_section_wrapper = '<div ' . FusionBuilder::attributes( 'container-shortcode-scroll-wrapper' ) . ' >';
				$this->scroll_section_element_counter++;
			}
			// Scroll section navigation.
			if ( ( $this->data['last_container'] || 'no' === $this->args['hundred_percent_height_scroll'] || 'no' === $this->args['hundred_percent_height'] ) && $this->data['scroll_scope_matches'] ) {
				if ( 1 < $this->scroll_section_element_counter ) {
					$scroll_navigation = '<nav ' . FusionBuilder::attributes( 'container-shortcode-scroll-navigation' ) . ' ><ul>';
					foreach ( $this->scroll_section_navigation as $section_navigation ) {
						$scroll_navigation .= '<li><a href="#' . $section_navigation['id'] . '" class="fusion-scroll-section-link" data-name="' . $section_navigation['name'] . '" data-element="' . $section_navigation['element'] . '"><span class="fusion-scroll-section-link-bullet"></span></a></li>';
					}
					$scroll_navigation .= '</ul></nav>';
				}
				$this->scroll_section_scope           = false;
				$this->scroll_section_element_counter = 1;
				$this->scroll_section_navigation      = [];
			}

			// Start scroll section wrapper.
			if ( 'yes' === $this->args['hundred_percent_height_scroll'] && 'yes' === $this->args['hundred_percent_height'] && $this->data['scroll_scope_matches'] ) {
				$html .= $scroll_section_wrapper;
			}

			// Start menu anchor.
			if ( ! empty( $this->args['menu_anchor'] ) ) {
				$html .= '<div id="' . $this->args['menu_anchor'] . '">';
			}

			// Parallax helper.
			if ( false === $this->args['video_bg'] && ! empty( $this->args['background_image'] ) && 'none' !== $this->args['background_parallax'] && 'fixed' !== $this->args['background_parallax'] ) {
				$html .= '<div ' . FusionBuilder::attributes( 'container-shortcode-parallax' ) . ' ></div>';
			}

			// Start container.
			$html .= '<' . $this->args['container_tag'] . ' ' . FusionBuilder::attributes( 'container-shortcode' ) . ' >';

			// Video background.
			if ( $this->args['video_bg'] ) {
				$html .= $this->create_video_background();
			}

			// Pattern Background.
			if ( $this->args['pattern_bg'] ) {
				$html .= Fusion_Builder_Pattern_Helper::get_element( $this->args );
			}

			// Mask Background.
			if ( $this->args['mask_bg'] ) {
				$html .= Fusion_Builder_Mask_Helper::get_element( $this->args );
			}

			// Fading Background.
			if ( 'yes' === $this->args['fade'] && ! empty( $this->args['background_image'] ) && false === $this->args['video_bg'] ) {
				$html .= '<div ' . FusionBuilder::attributes( 'container-shortcode-fading-background' ) . ' ></div>';
			}

			// Nested check before content render, to avoid getting wrong value.
			$nested = $this->data['is_nested'];

			// Container Inner content.
			$main_content = do_shortcode( fusion_builder_fix_shortcodes( $content ) );
			if ( ! $this->is_flex() && 'yes' === $this->args['hundred_percent_height'] && 'yes' === $this->args['hundred_percent_height_center_content'] ) {
				$main_content = '<div class="fusion-fullwidth-center-content">' . $main_content . '</div>';
			}
			$html .= $main_content;

			// Add custom CSS.
			$html .= $style_block;

			// End container.
			$html .= '</' . $this->args['container_tag'] . '>';

			// End menu anchor.
			if ( ! empty( $this->args['menu_anchor'] ) ) {
				$html .= '</div>';
			}

			// End scroll section wrapper.
			if ( 'yes' === $this->args['hundred_percent_height_scroll'] && 'yes' === $this->args['hundred_percent_height'] && $this->data['scroll_scope_matches'] ) {
				$html .= '</div>';
			}

			if ( '' !== $scroll_navigation ) {
				if ( $this->data['last_container'] && 'yes' === $this->args['hundred_percent_height_scroll'] && 'yes' === $this->args['hundred_percent_height'] && $this->data['scroll_scope_matches'] ) {
					$html = $html . $scroll_navigation . '</div>';
				} else {
					$html = $scroll_navigation . '</div>' . $html;
				}
			}

			$this->reset_fusion_fwc_type();

			fusion_builder_column()->reset_previous_spacing();

			// If we are rendering a top level container, then set render to false.
			if ( ! $nested ) {
				$this->rendering = false;
			}

			// End of nested, restore parent args in case it is not finished rendering.
			if ( $this->data['is_nested'] ) {
				if ( $this->parent_args ) {
					$this->args = $this->parent_args;
				}
				if ( $this->parent_data ) {
					$this->data = $this->parent_data;
				}
				if ( ! empty( $this->parent_column_map ) ) {
					$this->column_map = $this->parent_column_map;
				}

				$this->update_fusion_fwc_type();
			}

			$this->nesting_depth--;

			$this->on_render();

			return apply_filters( 'fusion_element_container_content', $html, $atts );
		}

		/**
		 * Updates global Fusion FWC Type.
		 *
		 * @access public
		 * @since 3.0
		 * @return void
		 */
		public function update_fusion_fwc_type() {
			global $fusion_fwc_type;

			// When section seps are used inside of layout, then we need to make sure they stretch full width.
			if ( $this->data['is_nested'] && 'yes' === $this->args['hundred_percent'] ) {
				$content = 'contained';
			} else {
				$content = ( 'yes' === $this->args['hundred_percent'] ) ? 'fullwidth' : 'contained';
			}

			$fusion_fwc_type                      = [];
			$fusion_fwc_type['content']           = $content;
			$fusion_fwc_type['width_100_percent'] = $this->args['width_100'];
			$fusion_fwc_type['padding']           = [
				'left'  => $this->args['padding_left'],
				'right' => $this->args['padding_right'],
			];

			if ( $this->is_flex() ) {
				foreach ( [ 'large', 'medium', 'small' ] as $size ) {
					foreach ( [ 'right', 'left' ] as $direction ) {
						$padding_key = 'large' === $size ? 'padding_' . $direction : 'padding_' . $direction . '_' . $size;
						$fusion_fwc_type['padding_flex'][ $size ][ $direction ] = $this->args[ $padding_key ];
					}
				}
			} else {
				$fusion_fwc_type['padding_flex']['large'] = $fusion_fwc_type['padding'];
			}
		}

		/**
		 * Resets global Fusion FWC Type.
		 *
		 * @access public
		 * @since 3.0
		 * @return void
		 */
		public function reset_fusion_fwc_type() {
			global $fusion_fwc_type, $columns;

			$fusion_fwc_type = [];
			$columns         = 0;
		}

		/**
		 * Sets styles necessary for column responsiveness.
		 *
		 * @access public
		 * @since 3.0
		 * @return void
		 */
		public function set_responsive_container_styles() {
			$fusion_settings = awb_get_fusion_settings();

			foreach ( [ 'large', 'medium', 'small' ] as $size ) {
				$container_styles = '';

				foreach ( [ 'top', 'right', 'bottom', 'left' ] as $direction ) {

					// Padding.
					$padding_key = 'large' === $size ? 'padding_' . $direction : 'padding_' . $direction . '_' . $size;
					if ( '' !== $this->args[ $padding_key ] ) {
						$container_styles .= 'padding-' . $direction . ' : ' . $this->args[ $padding_key ] . ';';
					}

					// Margin.
					if ( 'left' === $direction || 'right' === $direction ) {
						continue;
					}
					$spacing_key = 'large' === $size ? 'margin_' . $direction : 'margin_' . $direction . '_' . $size;
					if ( '' !== $this->args[ $spacing_key ] ) {
						$container_styles .= 'margin-' . $direction . ' : ' . $this->args[ $spacing_key ] . ';';
					}
				}

				// Minimum height.
				if ( 'min' === $this->args['hundred_percent_height'] ) {
					$min_height_key = 'large' === $size ? 'min_height' : 'min_height_' . $size;
					if ( '' !== $this->args[ $min_height_key ] ) {
						if ( false !== strpos( $this->args[ $min_height_key ], '%' ) ) {
							$this->args[ $min_height_key ] = str_replace( '%', 'vh', $this->args[ $min_height_key ] );
						}

						$container_styles .= 'min-height:' . fusion_library()->sanitize->get_value_with_unit( $this->args[ $min_height_key ] ) . ';';
					}
				}

				if ( '' === $container_styles ) {
					continue;
				}

				$container_styles = '.fusion-body .fusion-flex-container.fusion-builder-row-' . $this->data['container_counter'] . '{ ' . $container_styles . '}';

				// Large styles, no wrapping needed.
				if ( 'large' === $size ) {
					$this->styles .= $container_styles;
				} else {
					// Medium and Small size screen styles.
					$this->styles .= '@media only screen and (max-width:' . $fusion_settings->get( 'visibility_' . $size ) . 'px) {' . $container_styles . '}';
				}
			}
		}

		/**
		 * Sets scroll container data.
		 *
		 * @access public
		 * @since 7.0
		 * @return void
		 */
		public function set_container_scroll_data() {
			$this->args['active_class'] = '';

			if ( 'yes' === $this->args['hundred_percent_height'] && 'yes' === $this->args['hundred_percent_height_scroll'] && $this->data['scroll_scope_matches'] ) {
				if ( 1 === $this->scroll_section_element_counter ) {
					$this->scroll_section_counter++;
					$this->scroll_section_scope  = $this->data['is_nested'] ? 'nested' : 'parent';
					$this->args['active_class'] .= ' active';
				}

				array_push(
					$this->scroll_section_navigation,
					[
						'id'      => 'fusion-scroll-section-element-' . $this->scroll_section_counter . '-' . $this->scroll_section_element_counter,
						'name'    => $this->args['admin_label'],
						'element' => $this->scroll_section_element_counter,
					]
				);
			}
		}

		/**
		 * Creates Video Background HTML.
		 *
		 * @access public
		 * @since 3.0
		 * @return string
		 */
		public function create_video_background() {
			global $parallax_id;

			$video_background = '';
			$video_src        = '';
			$overlay_style    = '';

			if ( ! empty( $this->args['video_url'] ) ) {
				$video = fusion_builder_get_video_provider( $this->args['video_url'] );
				if ( 'youtube' === $video['type'] ) {
					$video_background .= "<div style='opacity:0;' class='fusion-background-video-wrapper' id='video-" . ( $parallax_id++ ) . "' data-youtube-video-id='" . $video['id'] . "' data-mute='" . $this->args['video_mute'] . "' data-loop='" . ( 'yes' === $this->args['video_loop'] ? 1 : 0 ) . "' data-loop-adjustment='" . $this->args['video_loop_refinement'] . "' data-video-aspect-ratio='" . $this->args['video_aspect_ratio'] . "'><div class='fusion-container-video-bg' id='video-" . ( $parallax_id++ ) . "-inner'></div></div>";
				} elseif ( 'vimeo' === $video['type'] ) {
					$video_background .= '<div id="video-' . $parallax_id . '" class="fusion-background-video-wrapper" data-vimeo-video-id="' . $video['id'] . '" data-mute="' . $this->args['video_mute'] . '" data-video-aspect-ratio="' . $this->args['video_aspect_ratio'] . '" style="opacity:0;"><iframe id="video-iframe-' . $parallax_id . '" class="fusion-container-video-bg" src="//player.vimeo.com/video/' . $video['id'] . '?html5=1&autopause=0&autoplay=1&badge=0&byline=0&autopause=0&loop=' . ( 'yes' === $this->args['video_loop'] ? '1' : '0' ) . '&title=0' . ( 'yes' === $this->args['video_mute'] ? '&muted=1' : '' ) . '" frameborder="0"></iframe></div>';
				}
			} else {
				if ( ! empty( $this->args['video_webm'] ) ) {
					$video_src .= '<source src="' . $this->args['video_webm'] . '" type="video/webm">';
				}

				if ( ! empty( $this->args['video_mp4'] ) ) {
					$video_src .= '<source src="' . $this->args['video_mp4'] . '" type="video/mp4">';
				}

				if ( ! empty( $this->args['video_ogv'] ) ) {
					$video_src .= '<source src="' . $this->args['video_ogv'] . '" type="video/ogg">';
				}
				$video_attributes = 'preload="auto" autoplay playsinline';

				if ( 'yes' === $this->args['video_loop'] ) {
					$video_attributes .= ' loop';
				}

				if ( 'yes' === $this->args['video_mute'] ) {
					$video_attributes .= ' muted';
				}

				// Video Preview Image.
				if ( ! empty( $this->args['video_preview_image'] ) ) {
					$video_preview_image_style = 'background-image:url(' . $this->args['video_preview_image'] . ');';
					$video_background         .= '<div class="fullwidth-video-image" style="' . $video_preview_image_style . '"></div>';
				}

				$video_background .= '<div class="fullwidth-video"><video ' . $video_attributes . '>' . $video_src . '</video></div>';
			}

			// Video Overlay.
			if ( $this->args['is_gradient_color'] ) {
				$overlay_style .= 'background-image: ' . Fusion_Builder_Gradient_Helper::get_gradient_string( $this->args ) . ';';
			}

			if ( ! empty( $this->args['background_color'] ) && 1 > $this->args['alpha_background_color'] ) {
				$overlay_style .= 'background-color:' . $this->args['background_color'] . ';';
			}

			if ( '' !== $overlay_style ) {
				$video_background .= '<div class="fullwidth-overlay" style="' . $overlay_style . '"></div>';
			}

			return $video_background;
		}

		/**
		 * Sets container video data args.
		 *
		 * @access public
		 * @since 3.0
		 * @return void
		 */
		public function set_container_video_data() {
			// If no blend mode is defined, check if we should set to overlay.
			if ( ! isset( $this->atts['background_blend_mode'] ) &&
				1 > $this->args['alpha_background_color'] &&
				0 !== $this->args['alpha_background_color'] &&
				! $this->args['is_gradient_color'] &&
				( ! empty( $this->args['background_image'] ) || $this->args['video_bg'] ) ) {
				$this->args['background_blend_mode'] = 'overlay';
			}

			if ( ! empty( $this->args['video_mp4'] ) || ! empty( $this->args['video_webm'] ) || ! empty( $this->args['video_ogv'] ) || ! empty( $this->args['video_url'] ) ) {
				$this->args['video_bg'] = true;
			}
		}

		/**
		 * Attributes for the scroll wrapper element.
		 *
		 * @access public
		 * @since 3.0
		 * @return array
		 */
		public function scroll_wrapper_attr() {
			$fusion_settings = awb_get_fusion_settings();

			$attrs = [
				'id'           => esc_attr( $this->args['id'] ),
				'class'        => 'fusion-scroll-section-element' . $this->args['active_class'],
				'data-section' => $this->scroll_section_counter,
				'data-element' => $this->scroll_section_element_counter,
			];

			if (
				'yes' === $this->args['hundred_percent_height_scroll'] &&
				'yes' === $this->args['hundred_percent_height'] &&
				$this->data['scroll_scope_matches']
			) {
				$attrs['style'] = 'transition-duration:' . $fusion_settings->get( 'container_hundred_percent_scroll_sensitivity' ) . 'ms;"';
			}
			return $attrs;
		}

		/**
		 * Attributes for the scroll element.
		 *
		 * @access public
		 * @since 3.0
		 * @return array
		 */
		public function scroll_attr() {
			return [
				'id'           => 'fusion-scroll-section-' . $this->scroll_section_counter,
				'class'        => 'fusion-scroll-section',
				'data-section' => $this->scroll_section_counter,
			];
		}

		/**
		 * Attributes for the navigation scroll element.
		 *
		 * @access public
		 * @since 3.0
		 * @return array
		 */
		public function scroll_navigation_attr() {
			$attr = [
				'id'           => 'fusion-scroll-section-nav-' . $this->scroll_section_counter,
				'class'        => 'fusion-scroll-section-nav',
				'data-section' => $this->scroll_section_counter,
			];

			$scroll_navigation_position = ( 'right' === fusion_get_option( 'header_position' ) || is_rtl() ) ? 'scroll-navigation-left' : 'scroll-navigation-right';
			$attr['class']             .= ' ' . $scroll_navigation_position;

			return $attr;
		}

		/**
		 * Attributes for the fading background element.
		 *
		 * @access public
		 * @since 3.0
		 * @return array
		 */
		public function fading_background_attr() {
			$bg_type = 'faded';
			$attr    = [
				'class' => 'fullwidth-faded',
				'style' => '',
			];

			if ( 'fixed' === $this->args['background_parallax'] ) {
				$attr['style'] .= 'background-attachment:' . $this->args['background_parallax'] . ';';
			}

			if ( $this->args['background_color'] ) {
				$attr['style'] .= 'background-color:' . $this->args['background_color'] . ';';
			}

			if ( $this->args['background_image'] && ! $this->args['lazy_load'] ) {
				$attr['style'] .= 'background-image: url(' . $this->args['background_image'] . ');';

				if ( 'skip' === $this->args['skip_lazy_load'] ) {
					$attr['data-preload-img'] = $this->args['background_image'];
				}
			}

			if ( $this->args['is_gradient_color'] ) {
				$attr['style'] .= 'background-image: ' . Fusion_Builder_Gradient_Helper::get_gradient_string( $this->args, 'fade' );
			}

			if ( $this->args['background_position'] ) {
				$attr['style'] .= 'background-position:' . $this->args['background_position'] . ';';
			}

			if ( $this->args['background_repeat'] ) {
				$attr['style'] .= 'background-repeat:' . $this->args['background_repeat'] . ';';
			}

			if ( 'none' !== $this->args['background_blend_mode'] ) {
				$attr['style'] .= 'background-blend-mode: ' . esc_attr( $this->args['background_blend_mode'] ) . ';';
			}

			if ( 'no-repeat' === $this->args['background_repeat'] ) {
				$attr['style'] .= '-webkit-background-size:cover;-moz-background-size:cover;-o-background-size:cover;background-size:cover;';
			}

			if ( $this->args['lazy_load'] ) {
				$attr['class']  .= ' lazyload';
				$attr['data-bg'] = $this->args['background_image'];
			}

			if ( $this->args['lazy_load'] && $this->args['is_gradient_color'] ) {
				$attr['data-bg-gradient'] = Fusion_Builder_Gradient_Helper::get_gradient_string( $this->args );
			}

			return $attr;
		}

		/**
		 * Builds the parallax helper attributes array.
		 *
		 * @access public
		 * @since 7.0
		 * @return array
		 */
		public function parallax_attr() {
			$attr = [];

			$attr['class'] = 'fusion-bg-parallax';

			$attr['data-bg-align']       = esc_attr( $this->args['background_position'] );
			$attr['data-direction']      = $this->args['background_parallax'];
			$attr['data-mute']           = 'mute' === $this->args['video_mute'] ? 'true' : 'false';
			$attr['data-opacity']        = esc_attr( $this->args['opacity'] );
			$attr['data-velocity']       = esc_attr( (float) $this->args['parallax_speed'] * -1 );
			$attr['data-mobile-enabled'] = ( ( 'yes' === $this->args['enable_mobile'] ) ? 'true' : 'false' );
			$attr['data-break_parents']  = esc_attr( $this->args['break_parents'] );
			$attr['data-bg-image']       = esc_attr( $this->args['background_image'] );
			$attr['data-bg-repeat']      = esc_attr( isset( $this->args['background_repeat'] ) && 'no-repeat' !== $this->args['background_repeat'] ? 'true' : 'false' );

			$bg_color_alpha = Fusion_Color::new_color( $this->args['background_color'] )->alpha;
			if ( 0 !== $bg_color_alpha ) {
				$attr['data-bg-color'] = esc_attr( $this->args['background_color'] );
			}

			if ( 'none' !== $this->args['background_blend_mode'] ) {
				$attr['data-blend-mode'] = esc_attr( $this->args['background_blend_mode'] );
			}

			if ( $this->args['is_gradient_color'] ) {
				$attr['data-bg-gradient-type']           = esc_attr( $this->args['gradient_type'] );
				$attr['data-bg-gradient-angle']          = esc_attr( $this->args['linear_angle'] );
				$attr['data-bg-gradient-start-color']    = esc_attr( $this->args['gradient_start_color'] );
				$attr['data-bg-gradient-start-position'] = esc_attr( $this->args['gradient_start_position'] );
				$attr['data-bg-gradient-end-color']      = esc_attr( $this->args['gradient_end_color'] );
				$attr['data-bg-gradient-end-position']   = esc_attr( $this->args['gradient_end_position'] );
				$attr['data-bg-radial-direction']        = esc_attr( $this->args['radial_direction'] );
			}

			$attr['data-bg-height'] = esc_attr( $this->args['data_bg_height'] );
			$attr['data-bg-width']  = esc_attr( $this->args['data_bg_width'] );

			return $attr;
		}

		/**
		 * Builds the container attributes array.
		 *
		 * @access public
		 * @since 7.0
		 * @return array
		 */
		public function attr() {
			$c_page_id = fusion_library()->get_page_id();

			$attr = [
				'class' => 'fusion-fullwidth fullwidth-box fusion-builder-row-' . $this->data['container_counter'],
				'style' => '',
			];

			// Background.
			if ( ! empty( $this->args['background_color'] ) && ! ( 'yes' === $this->args['fade'] && ! empty( $this->args['background_image'] ) && false === $this->args['video_bg'] ) ) {
				$attr['style'] .= 'background-color: ' . $this->args['background_color'] . ';';
			}

			if ( ! empty( $this->args['background_image'] ) && 'yes' !== $this->args['fade'] && ! $this->args['lazy_load'] ) {
				$attr['style'] .= 'background-image: url("' . esc_url_raw( $this->args['background_image'] ) . '");';

				if ( 'skip' === $this->args['skip_lazy_load'] ) {
					$attr['data-preload-img'] = $this->args['background_image'];
				}
			}

			if ( $this->args['is_gradient_color'] ) {
				$attr['style'] .= 'background-image:' . Fusion_Builder_Gradient_Helper::get_gradient_string( $this->args, 'main_bg' );
			}

			if ( ! empty( $this->args['background_position'] ) ) {
				$attr['style'] .= 'background-position: ' . $this->args['background_position'] . ';';
			}

			if ( ! empty( $this->args['background_repeat'] ) ) {
				$attr['style'] .= 'background-repeat: ' . $this->args['background_repeat'] . ';';
			}

			if ( 'none' !== $this->args['background_blend_mode'] ) {
				$attr['style'] .= 'background-blend-mode: ' . $this->args['background_blend_mode'] . ';';
			}

			if ( 'yes' === $this->args['box_shadow'] ) {
				$attr['style'] .= 'box-shadow:';
				$attr['style'] .= Fusion_Builder_Box_Shadow_Helper::get_box_shadow_styles(
					[
						'box_shadow_horizontal' => $this->args['box_shadow_horizontal'],
						'box_shadow_vertical'   => $this->args['box_shadow_vertical'],
						'box_shadow_blur'       => $this->args['box_shadow_blur'],
						'box_shadow_spread'     => $this->args['box_shadow_spread'],
						'box_shadow_color'      => $this->args['box_shadow_color'],
						'box_shadow_style'      => $this->args['box_shadow_style'],
					]
				);
			}

			if ( ! $this->is_flex() ) {
				// Get correct container padding.
				$paddings = [ 'top', 'right', 'bottom', 'left' ];

				foreach ( $paddings as $padding ) {
					$padding_name = 'padding_' . $padding;

					// Add padding to style.
					if ( ! empty( $this->args[ $padding_name ] ) ) {
						$attr['style'] .= 'padding-' . $padding . ':' . fusion_library()->sanitize->get_value_with_unit( $this->args[ $padding_name ] ) . ';';
					}
				}

				// Margin; for separator conversion only.
				if ( ! empty( $this->args['margin_bottom'] ) ) {
					$attr['style'] .= 'margin-bottom: ' . fusion_library()->sanitize->get_value_with_unit( $this->args['margin_bottom'] ) . ';';
				}

				if ( ! empty( $this->args['margin_top'] ) ) {
					$attr['style'] .= 'margin-top: ' . fusion_library()->sanitize->get_value_with_unit( $this->args['margin_top'] ) . ';';
				}
			}

			// Border-sizes.
			$border = [
				'top'    => '0',
				'bottom' => '0',
				'left'   => '0',
				'right'  => '0',
			];

			// Backwards-compatibility for border-size.
			if ( isset( $this->atts['border_size'] ) && '' !== $this->atts['border_size'] && ! isset( $this->atts['border_sizes_top'] ) && ! isset( $this->atts['border_sizes_bottom'] ) ) {
				$border['top']    = absint( $this->args['border_size'] ) . 'px';
				$border['bottom'] = absint( $this->args['border_size'] ) . 'px';
			} else {
				if ( '' !== $this->args['border_sizes_top'] ) {
					$border['top'] = $this->args['border_sizes_top'];
				}
				if ( '' !== $this->args['border_sizes_bottom'] ) {
					$border['bottom'] = $this->args['border_sizes_bottom'];
				}
			}

			if ( '' !== $this->args['border_sizes_left'] ) {
				$border['left'] = $this->args['border_sizes_left'];
			}
			if ( '' !== $this->args['border_sizes_right'] ) {
				$border['right'] = $this->args['border_sizes_right'];
			}

			$attr['style'] .= "border-width: {$border['top']} {$border['right']} {$border['bottom']} {$border['left']};";

			// Border-color.
			if ( ! empty( $this->args['border_color'] ) ) {
				$attr['style'] .= 'border-color:' . $this->args['border_color'] . ';';
			}

			// Border-style.
			if ( ! empty( $this->args['border_style'] ) ) {
				$attr['style'] .= 'border-style:' . $this->args['border_style'] . ';';
			}

			if ( ! empty( $this->args['background_image'] ) && ! $this->args['video_bg'] && 'no-repeat' === $this->args['background_repeat'] ) {
				$attr['style'] .= '-webkit-background-size:cover;-moz-background-size:cover;-o-background-size:cover;background-size:cover;';
			}

			if ( $this->is_flex() ) {
				$attr['class'] .= ' fusion-flex-container';
			}

			if ( $this->args['video_bg'] ) {
				$attr['class'] .= ' video-background';
			}

			if ( $this->args['pattern_bg'] ) {
				$attr['class'] .= ' has-pattern-background';
			}

			if ( $this->args['mask_bg'] ) {
				$attr['class'] .= ' has-mask-background';
			}

			// Fading Background.
			if ( 'yes' === $this->args['fade'] && ! empty( $this->args['background_image'] ) && false === $this->args['video_bg'] ) {
				$attr['class'] .= ' faded-background';
			}

			// Parallax.
			if ( false === $this->args['video_bg'] && ! empty( $this->args['background_image'] ) ) {
				// Parallax css class.
				if ( ! empty( $this->args['background_parallax'] ) ) {
					$attr['class'] .= ' fusion-parallax-' . $this->args['background_parallax'];
				}

				if ( 'fixed' === $this->args['background_parallax'] ) {
					$attr['style'] .= 'background-attachment:' . $this->args['background_parallax'] . ';';
				}
			}

			// Custom CSS class.
			if ( ! empty( $this->args['class'] ) ) {
				$attr['class'] .= ' ' . $this->args['class'];
			}

			// Hundred percent.
			$attr['class'] .= 'yes' === $this->args['hundred_percent'] ? ' hundred-percent-fullwidth' : ' nonhundred-percent-fullwidth';

			// Hundred percent height.
			if ( 'yes' === $this->args['hundred_percent_height'] ) {
				$attr['class'] .= ' hundred-percent-height';
				if ( 'yes' === $this->args['hundred_percent_height_center_content'] ) {
					$attr['class'] .= ' hundred-percent-height-center-content';
				}
				if ( 'yes' === $this->args['hundred_percent_height_scroll'] && $this->data['scroll_scope_matches'] ) {
					$attr['class'] .= ' hundred-percent-height-scrolling';
				} else {
					$attr['class'] .= ' non-hundred-percent-height-scrolling';
				}
			} else {
				$attr['class'] .= ' non-hundred-percent-height-scrolling';
			}

			// Equal column height.
			if ( 'yes' === $this->args['equal_height_columns'] && ! $this->is_flex() ) {
				$attr['class'] .= ' fusion-equal-height-columns';
			}

			// Visibility classes.
			if ( 'no' === $this->args['hundred_percent_height'] || 'no' === $this->args['hundred_percent_height_scroll'] ) {
				$attr['class'] = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr['class'] );
			}

			if ( $this->args['lazy_load'] ) {
				$attr['class']  .= ' lazyload';
				$attr['data-bg'] = $this->args['background_image'];
				if ( $this->args['is_gradient_color'] ) {
					$attr['data-bg-gradient'] = Fusion_Builder_Gradient_Helper::get_gradient_string( $this->args );
				}
			}

			// Animations.
			if ( $this->args['animation_type'] ) {
				$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
			}

			// Custom CSS ID.
			if ( $this->args['id'] ) {
				$attr['id'] = esc_attr( $this->args['id'] );
			}

			// Sticky container.
			if ( 'on' === $this->args['sticky'] ) {
				$attr['class'] .= ' fusion-sticky-container';

				if ( '' !== $this->args['sticky_transition_offset'] && 0 !== $this->args['sticky_transition_offset'] ) {
					$attr['data-transition-offset'] = (float) $this->args['sticky_transition_offset'];
				}
				if ( '' !== $this->args['sticky_offset'] && 0 !== $this->args['sticky_offset'] ) {
					$attr['data-sticky-offset'] = (string) $this->args['sticky_offset'];
				}
				if ( '' !== $this->args['scroll_offset'] && 0 !== $this->args['scroll_offset'] ) {
					$attr['data-scroll-offset'] = (float) $this->args['scroll_offset'];
				}
				if ( '' !== $this->args['sticky_height'] && 'min' === $this->args['hundred_percent_height'] ) {
					$attr['data-sticky-height-transition'] = true;
				}
				if ( '' !== $this->args['sticky_devices'] ) {
					$sticky_devices = explode( ',', (string) $this->args['sticky_devices'] );
					foreach ( $sticky_devices as $sticky_device ) {
						$attr[ 'data-sticky-' . str_replace( ' ', '', $sticky_device ) ] = true;
					}
				}
			}

			// z-index.
			if ( '' !== $this->args['z_index'] ) {
				$attr['class'] .= ' fusion-custom-z-index';
			}

			// Absolute container.
			if ( 'on' === $this->args['absolute'] ) {
				$attr['class'] .= ' fusion-absolute-container';

				if ( '' !== $this->args['absolute_devices'] ) {
					$absolute_devices = explode( ',', (string) $this->args['absolute_devices'] );
					foreach ( $absolute_devices as $absolute_device ) {
						$attr['class'] .= ' fusion-absolute-position-' . $absolute_device;
					}
				}
			}

			// Escape attributes before return.
			if ( '' !== $attr['style'] ) {
				$attr['style'] = esc_attr( $attr['style'] );
			}

			return $attr;
		}

		/**
		 * Builds the container custom CSS styles.
		 *
		 * @access public
		 * @since 7.0
		 * @return void
		 */
		public function container_styles() {

			$style = '';

			if ( '' !== $this->args['link_color'] || '' !== $this->args['link_hover_color'] ) {
				$style_prefix             = '.fusion-fullwidth.fusion-builder-row-' . $this->data['container_counter'];
				$link_exclusion_selectors = ' a:not(.awb-custom-text-color):not(.fusion-button):not(.fusion-builder-module-control):not(.fusion-social-network-icon):not(.fb-icon-element):not(.fusion-countdown-link):not(.fusion-rollover-link):not(.fusion-rollover-gallery):not(.fusion-button-bar):not(.add_to_cart_button):not(.show_details_button):not(.product_type_external):not(.fusion-view-cart):not(.fusion-quick-view):not(.fusion-rollover-title-link):not(.fusion-breadcrumb-link)';

				// Add link styles.
				if ( '' !== $this->args['link_color'] ) {
					$style .= $style_prefix . $link_exclusion_selectors . ' , ' . $style_prefix . $link_exclusion_selectors . ':before, ' . $style_prefix . $link_exclusion_selectors . ':after {color: ' . $this->args['link_color'] . ';}';
				}

				// Add link hover styles.
				if ( '' !== $this->args['link_hover_color'] ) {
					$link_exclusion_selectors = str_replace( 'awb-custom-text-color', 'awb-custom-text-hover-color', $link_exclusion_selectors );

					$style .= $style_prefix . $link_exclusion_selectors . ':hover, ' . $style_prefix . $link_exclusion_selectors . ':hover:before, ' . $style_prefix . $link_exclusion_selectors . ':hover:after {color: ' . $this->args['link_hover_color'] . ';}';
					$style .= $style_prefix . ' .pagination a.inactive:hover, ' . $style_prefix . ' .fusion-filters .fusion-filter.fusion-active a {border-color: ' . $this->args['link_hover_color'] . ';}';
					$style .= $style_prefix . ' .pagination .current {border-color: ' . $this->args['link_hover_color'] . '; background-color: ' . $this->args['link_hover_color'] . ';}';
					$style .= $style_prefix . ' .fusion-filters .fusion-filter.fusion-active a, ' . $style_prefix . ' .fusion-date-and-formats .fusion-format-box, ' . $style_prefix . ' .fusion-popover, ' . $style_prefix . ' .tooltip-shortcode {color: ' . $this->args['link_hover_color'] . ';}';
					$style .= '#wrapper ' . $style_prefix . ' .fusion-widget-area .fusion-vertical-menu-widget .menu li.current_page_ancestor > a, #wrapper ' . $style_prefix . ' .fusion-widget-area .fusion-vertical-menu-widget .menu li.current_page_ancestor > a:before, #wrapper ' . $style_prefix . ' .fusion-widget-area .fusion-vertical-menu-widget .current-menu-item > a, #wrapper ' . $style_prefix . ' .fusion-widget-area .fusion-vertical-menu-widget .current-menu-item > a:before, #wrapper ' . $style_prefix . ' .fusion-widget-area .fusion-vertical-menu-widget .current_page_item > a, #wrapper ' . $style_prefix . ' .fusion-widget-area .fusion-vertical-menu-widget .current_page_item > a:before {color: ' . $this->args['link_hover_color'] . ';}';
					$style .= '#wrapper ' . $style_prefix . ' .fusion-widget-area .widget_nav_menu .menu li.current_page_ancestor > a, #wrapper ' . $style_prefix . ' .fusion-widget-area .widget_nav_menu .menu li.current_page_ancestor > a:before, #wrapper ' . $style_prefix . ' .fusion-widget-area .widget_nav_menu .current-menu-item > a, #wrapper ' . $style_prefix . ' .fusion-widget-area .widget_nav_menu .current-menu-item > a:before, #wrapper ' . $style_prefix . ' .fusion-widget-area .widget_nav_menu .current_page_item > a, #wrapper ' . $style_prefix . ' .fusion-widget-area .widget_nav_menu .current_page_item > a:before {color: ' . $this->args['link_hover_color'] . ';}';
					$style .= '#wrapper ' . $style_prefix . ' .fusion-vertical-menu-widget .menu li.current_page_item > a { border-right-color:' . $this->args['link_hover_color'] . ';border-left-color:' . $this->args['link_hover_color'] . ';}';
					$style .= '#wrapper ' . $style_prefix . ' .fusion-widget-area .tagcloud a:hover { color: #fff; background-color: ' . $this->args['link_hover_color'] . ';border-color: ' . $this->args['link_hover_color'] . ';}';
					$style .= '#main ' . $style_prefix . ' .post .blog-shortcode-post-title a:hover {color: ' . $this->args['link_hover_color'] . ';}';
				}
			}

			// Add filter styles.
			$filter_style = Fusion_Builder_Filter_Helper::get_filter_style_element( $this->args, '.fusion-builder-row-' . $this->data['container_counter'], false );

			if ( '' !== $filter_style ) {
				$style .= $filter_style;
			}

			if ( 'on' === $this->args['sticky'] ) {
				if ( '' !== $this->args['sticky_background_color'] ) {
					$style .= '.fusion-fullwidth.fusion-builder-row-' . $this->data['container_counter'] . '.fusion-sticky-transition { background-color:' . $this->args['sticky_background_color'] . ' !important; }';
				}
				if ( '' !== $this->args['sticky_height'] ) {
					$style .= '.fusion-fullwidth.fusion-builder-row-' . $this->data['container_counter'] . '.fusion-sticky-transition { min-height:' . $this->args['sticky_height'] . ' !important; }';
				}
			}

			if ( '' !== $this->args['z_index'] ) {
				$style .= '.fusion-fullwidth.fusion-builder-row-' . $this->data['container_counter'] . ' { z-index:' . intval( $this->args['z_index'] ) . ' !important; }';
			}

			if ( '' !== $this->args['overflow'] ) {
				$style .= '.fusion-fullwidth.fusion-builder-row-' . $this->data['container_counter'] . ' { overflow:' . $this->args['overflow'] . '; }';
			}

			$this->styles .= $style;
		}

		/**
		 * Check if container should render.
		 *
		 * @access public
		 * @since 1.7
		 * @param array $atts The element attributes.
		 * @return boolean
		 */
		public function is_container_viewable( $atts = [] ) {

			// Published, all can see.
			if ( ! isset( $atts['status'] ) || 'published' === $atts['status'] || '' === $atts['status'] ) {
				return true;
			}

			// If is author, can also see.
			if ( is_user_logged_in() && current_user_can( 'publish_posts' ) ) {
				return true;
			}

			// Set to hide.
			if ( 'draft' === $atts['status'] ) {
				return false;
			}

			if ( ! isset( $atts['publish_date'] ) ) {
				return false;
			}

			// Set to show until or after.
			$time_check    = strtotime( $atts['publish_date'] );
			$wp_local_time = current_time( 'timestamp' );
			if ( '' !== $atts['publish_date'] && $time_check ) {
				if ( 'published_until' === $atts['status'] ) {
					return $wp_local_time < $time_check;
				}
				if ( 'publish_after' === $atts['status'] ) {
					return $wp_local_time > $time_check;
				}
			}

			// Any incorrect set-up default to show.
			return true;
		}

		/**
		 * Builds the dynamic styling.
		 *
		 * @access public
		 * @since 1.1
		 * @return array
		 */
		public function add_styling() {

			$css['global']['.fusion-builder-row.fusion-row']['max-width'] = 'var(--site_width)';

			return $css;
		}

		/**
		 * Adds settings to element options panel.
		 *
		 * @access public
		 * @since 1.1
		 * @return array $sections Column settings.
		 */
		public function add_options() {

			return [
				'container_shortcode_section' => [
					'label'       => esc_html__( 'Container', 'fusion-builder' ),
					'description' => '',
					'id'          => 'container_shortcode_section',
					'type'        => 'accordion',
					'icon'        => 'fusiona-container',
					'fields'      => [
						'container_important_note_info'   => [
							'label'       => '',
							'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> For column spacing option, please check column element options panel.', 'fusion-builder' ) . '</div>',
							'id'          => 'container_important_note_info',
							'type'        => 'custom',
						],
						'container_padding_default'       => [
							'label'       => esc_html__( 'Container Padding for Default Template', 'fusion-builder' ),
							'description' => esc_html__( 'Controls the top/right/bottom/left padding of the container element when using the Default page template. ', 'fusion-builder' ),
							'id'          => 'container_padding_default',
							'choices'     => [
								'top'    => true,
								'bottom' => true,
								'left'   => true,
								'right'  => true,
							],
							'default'     => [
								'top'    => '0px',
								'bottom' => '0px',
								'left'   => '0px',
								'right'  => '0px',
							],
							'type'        => 'spacing',
							'transport'   => 'postMessage',
						],
						'container_padding_100'           => [
							'label'       => esc_html__( 'Container Padding for 100% Width Template', 'fusion-builder' ),
							'description' => esc_html__( 'Controls the top/right/bottom/left padding of the container element when using the 100% width page template.', 'fusion-builder' ),
							'id'          => 'container_padding_100',
							'choices'     => [
								'top'    => true,
								'bottom' => true,
								'left'   => true,
								'right'  => true,
							],
							'default'     => [
								'top'    => '0px',
								'bottom' => '0px',
								'left'   => '30px',
								'right'  => '30px',
							],
							'type'        => 'spacing',
							'transport'   => 'postMessage',
						],
						'full_width_bg_color'             => [
							'label'       => esc_html__( 'Container Background Color', 'fusion-builder' ),
							'description' => esc_html__( 'Controls the background color of the container element.', 'fusion-builder' ),
							'id'          => 'full_width_bg_color',
							'default'     => 'rgba(255,255,255,0)',
							'type'        => 'color-alpha',
							'transport'   => 'postMessage',
						],
						'full_width_gradient_start_color' => [
							'label'       => esc_html__( 'Container Gradient Start Color', 'fusion-builder' ),
							'description' => esc_html__( 'Controls the start color for gradient of the container element.', 'fusion-builder' ),
							'id'          => 'full_width_gradient_start_color',
							'default'     => 'rgba(255,255,255,0)',
							'type'        => 'color-alpha',
							'transport'   => 'postMessage',
						],
						'full_width_gradient_end_color'   => [
							'label'       => esc_html__( 'Container Gradient End Color', 'fusion-builder' ),
							'description' => esc_html__( 'Controls the end color for gradient of the container element.', 'fusion-builder' ),
							'id'          => 'full_width_gradient_end_color',
							'default'     => 'rgba(255,255,255,0)',
							'type'        => 'color-alpha',
							'transport'   => 'postMessage',
						],
						'full_width_border_sizes'         => [
							'label'       => esc_html__( 'Container Border Sizes', 'fusion-builder' ),
							'description' => esc_html__( 'Controls the border size of the container element.', 'fusion-builder' ),
							'id'          => 'full_width_border_sizes',
							'type'        => 'spacing',
							'transport'   => 'postMessage',
							'choices'     => [
								'top'    => true,
								'bottom' => true,
								'left'   => true,
								'right'  => true,
							],
							'default'     => [
								'top'    => '0px',
								'bottom' => '0px',
								'left'   => '0px',
								'right'  => '0px',
							],
						],
						'full_width_border_color'         => [
							'label'       => esc_html__( 'Container Border Color', 'fusion-builder' ),
							'description' => esc_html__( 'Controls the border color of the container element.', 'fusion-builder' ),
							'id'          => 'full_width_border_color',
							'default'     => 'var(--awb-color3)',
							'type'        => 'color-alpha',
							'transport'   => 'postMessage',
						],
						'container_scroll_nav_bg_color'   => [
							'label'       => esc_html__( 'Container 100% Height Navigation Background Color', 'fusion-builder' ),
							'description' => esc_html__( 'Controls the background colors of the navigation area and name box when using 100% height containers.', 'fusion-builder' ),
							'id'          => 'container_scroll_nav_bg_color',
							'default'     => 'hsla(var(--awb-color8-h),var(--awb-color8-s),var(--awb-color8-l),calc(var(--awb-color8-a) - 80%))',
							'type'        => 'color-alpha',
							'css_vars'    => [
								[
									'name'     => '--container_scroll_nav_bg_color',
									'element'  => '.fusion-scroll-section-nav',
									'callback' => [ 'sanitize_color' ],
								],
							],
						],
						'container_scroll_nav_bullet_color' => [
							'label'       => esc_html__( 'Container 100% Height Navigation Element Color', 'fusion-builder' ),
							'description' => esc_html__( 'Controls the color of the navigation circles and text name when using 100% height containers.', 'fusion-builder' ),
							'id'          => 'container_scroll_nav_bullet_color',
							'default'     => 'var(--awb-color3)',
							'type'        => 'color-alpha',
							'css_vars'    => [
								[
									'name'     => '--container_scroll_nav_bullet_color',
									'element'  => '.fusion-scroll-section-link-bullet',
									'callback' => [ 'sanitize_color' ],
								],
							],
						],
						'container_hundred_percent_scroll_sensitivity' => [
							'label'       => esc_html__( 'Container 100% Height Scroll Sensitivity', 'fusion-builder' ),
							'description' => esc_html__( 'Controls the sensitivity of the scrolling transition on 100% height scrolling sections. In milliseconds.', 'fusion-builder' ),
							'id'          => 'container_hundred_percent_scroll_sensitivity',
							'default'     => '450',
							'type'        => 'slider',
							'transport'   => 'postMessage',
							'choices'     => [
								'min'  => '200',
								'max'  => '1500',
								'step' => '10',
							],
						],
						'container_hundred_percent_height_mobile' => [
							'label'       => esc_html__( 'Container 100% Height On Mobile', 'fusion-builder' ),
							'description' => esc_html__( 'Turn on to enable the 100% height containers on mobile. Please note, this feature only works when your containers have minimal content. If the container has a lot of content it will overflow the screen height. In many cases, 100% height containers work well on desktop, but will need disabled on mobile.', 'fusion-builder' ),
							'id'          => 'container_hundred_percent_height_mobile',
							'default'     => '0',
							'type'        => 'switch',
							'output'      => [
								[
									'element'           => 'helperElement',
									'property'          => 'dummy',
									'js_callback'       => [
										'fusionGlobalScriptSet',
										[
											'globalVar' => 'fusionContainerVars',
											'id'        => 'container_hundred_percent_height_mobile',
											'trigger'   => [ 'resize' ],
										],
									],
									'sanitize_callback' => '__return_empty_string',
								],
							],
						],
						'container_legacy_support'        => [
							'label'       => esc_html__( 'Legacy Container Support', 'Avada' ),
							'description' => __( '<strong>IMPORTANT:</strong> If you disable legacy mode and then save a page, all containers on that page will be saved as flex mode.  If you later decide to turn the global legacy support back on then you will have to re-edit those pages if you want legacy mode.', 'Avada' ),
							'id'          => 'container_legacy_support',
							'default'     => '0',
							'type'        => 'switch',
							'transport'   => 'postMessage', // No need to refresh the page.
						],
					],
				],
			];
		}

		/**
		 * Sets the necessary scripts.
		 *
		 * @access public
		 * @since 3.2
		 * @return void
		 */
		public function on_first_render() {
			$fusion_settings = awb_get_fusion_settings();

			$is_sticky_header_transparent = 0;
			if ( 1 > Fusion_Color::new_color( $fusion_settings->get( 'header_sticky_bg_color' ) )->alpha ) {
				$is_sticky_header_transparent = 1;
			}

			Fusion_Dynamic_JS::enqueue_script(
				'fusion-container',
				FusionBuilder::$js_folder_url . '/general/fusion-container.js',
				FusionBuilder::$js_folder_path . '/general/fusion-container.js',
				[ 'jquery', 'modernizr', 'fusion-animations', 'jquery-fade', 'fusion-parallax', 'fusion-video-general', 'fusion-video-bg', 'jquery-sticky-kit' ],
				'1',
				true
			);
			Fusion_Dynamic_JS::localize_script(
				'fusion-container',
				'fusionContainerVars',
				[
					'content_break_point'                => intval( $fusion_settings->get( 'content_break_point' ) ),
					'container_hundred_percent_height_mobile' => intval( $fusion_settings->get( 'container_hundred_percent_height_mobile' ) ),
					'is_sticky_header_transparent'       => $is_sticky_header_transparent,
					'hundred_percent_scroll_sensitivity' => intval( $fusion_settings->get( 'container_hundred_percent_scroll_sensitivity' ) ),
				]
			);
		}
	}
}

/**
 * Instantiates the container class.
 *
 * @return object FusionSC_Container
 */
function fusion_builder_container() { // phpcs:ignore WordPress.NamingConventions
	return FusionSC_Container::get_instance();
}

// Instantiate container.
fusion_builder_container();

/**
 * Map Column shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_builder_add_section() {

	$fusion_settings     = awb_get_fusion_settings();
	$is_builder          = ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() );
	$to_link             = '';
	$legacy_mode_enabled = 1 === (int) $fusion_settings->get( 'container_legacy_support' ) ? true : false;

	if ( $is_builder ) {
		$to_link = '<span class="fusion-panel-shortcut" data-fusion-option="container_hundred_percent_height_mobile">' . __( 'Global Options', 'fusion-builder' ) . '</span>';
	} else {
		$to_link = '<a href="' . esc_url( $fusion_settings->get_setting_link( 'container_hundred_percent_height_mobile' ) ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Global Options', 'fusion-builder' ) . '</a>';
	}

	$subset   = [ 'top', 'right', 'bottom', 'left' ];
	$setting  = 'container_padding';
	$default  = rtrim( $fusion_settings->get_default_description( $setting . '_default', $subset, '' ), '.' );
	$default .= __( ' on default template. ', 'fusion-builder' );
	$default .= rtrim( $fusion_settings->get_default_description( $setting . '_100', $subset, '' ), '.' );
	$default .= __( ' on 100% width template.', 'fusion-builder' );

	$container_type_param = [
		'type'        => 'textfield',
		'heading'     => esc_attr__( 'Container Type', 'fusion-builder' ),
		'description' => esc_attr__( 'Select the type of container you want to use.', 'fusion-builder' ),
		'param_name'  => 'type',
		'value'       => 'flex',
		'hidden'      => true,
	];

	if ( $legacy_mode_enabled ) {
		$container_type_param = [
			'type'        => 'radio_button_set',
			'heading'     => esc_attr__( 'Container Type', 'fusion-builder' ),
			'description' => esc_attr__( 'Select the type of container you want to use.', 'fusion-builder' ),
			'param_name'  => 'type',
			'value'       => [
				'flex'   => esc_attr__( 'Flex', 'fusion-builder' ),
				'legacy' => esc_attr__( 'Legacy', 'fusion-builder' ),
			],
			'default'     => 'flex',
			'group'       => esc_attr__( 'General', 'fusion-builder' ),
			'dependency'  => [
				[
					'element'  => 'template_type',
					'value'    => 'header',
					'operator' => '!=',
				],
			],
		];
	}

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Container',
			[
				'name'              => esc_attr__( 'Container', 'fusion-builder' ),
				'shortcode'         => 'fusion_builder_container',
				'hide_from_builder' => true,
				'help_url'          => 'https://theme-fusion.com/documentation/avada/elements/container-element/',
				'subparam_map'      => [
					'margin_top'            => 'spacing',
					'margin_bottom'         => 'spacing',
					'margin_top_medium'     => 'spacing_medium',
					'margin_bottom_medium'  => 'spacing_medium',
					'margin_top_small'      => 'spacing_small',
					'margin_bottom_small'   => 'spacing_small',
					'padding_top'           => 'padding_dimensions',
					'padding_right'         => 'padding_dimensions',
					'padding_bottom'        => 'padding_dimensions',
					'padding_left'          => 'padding_dimensions',
					'padding_top_medium'    => 'padding_dimensions_medium',
					'padding_right_medium'  => 'padding_dimensions_medium',
					'padding_bottom_medium' => 'padding_dimensions_medium',
					'padding_left_medium'   => 'padding_dimensions_medium',
					'padding_top_small'     => 'padding_dimensions_small',
					'padding_right_small'   => 'padding_dimensions_small',
					'padding_bottom_small'  => 'padding_dimensions_small',
					'padding_left_small'    => 'padding_dimensions_small',
				],
				'params'            => [
					$container_type_param,
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Interior Content Width', 'fusion-builder' ),
						'description' => esc_attr__( 'Select if the interior content is contained to site width or 100% width.', 'fusion-builder' ),
						'param_name'  => 'hundred_percent',
						'value'       => [
							'yes' => esc_attr__( '100% Width', 'fusion-builder' ),
							'no'  => esc_attr__( 'Site Width', 'fusion-builder' ),
						],
						'default'     => 'no',
						'group'       => esc_attr__( 'General', 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Height', 'fusion-builder' ),
						/* translators: 1. Percentage value 2. URL. */
						'description' => sprintf( __( 'Select if the container should be fixed to %1$s height of the viewport. Larger content that is taller than the screen height will be cut off, this option works best with minimal content. <strong>IMPORTANT:</strong> Mobile devices are even shorter in height so this option can be disabled on mobile in %2$s while still being active on desktop.', 'fusion-builder' ), '100%', $to_link ),
						'param_name'  => 'hundred_percent_height',
						'value'       => [
							'no'  => esc_attr__( 'Auto', 'fusion-builder' ),
							'yes' => esc_attr__( 'Full Height', 'fusion-builder' ),
							'min' => esc_attr__( 'Minimum Height', 'fusion-builder' ),
						],
						'default'     => 'no',
						'group'       => esc_attr__( 'General', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Minimum Height', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the minimum height for the container.', 'fusion-builder' ),
						'param_name'  => 'min_height',
						'value'       => '',
						'group'       => esc_attr__( 'General', 'fusion-builder' ),
						'responsive'  => [
							'state' => 'large',
						],
						'dependency'  => [
							[
								'element'  => 'hundred_percent_height',
								'value'    => 'min',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Enable 100% Height Scroll', 'fusion-builder' ),
						'description' => __( 'Select to add this container to a collection of 100% height containers that share scrolling navigation. <strong>IMPORTANT:</strong> When this option is used, the mobile visibility settings are disabled. This option will not work within off canvas.', 'fusion-builder' ),
						'param_name'  => 'hundred_percent_height_scroll',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'no',
						'group'       => esc_attr__( 'General', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'hundred_percent_height',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Row Alignment', 'fusion-builder' ),
						'description' => __( 'Defines how rows should be aligned vertically within the container. <strong>IMPORTANT:</strong> These settings will only take full effect when multiple rows are present.', 'fusion-builder' ),
						'param_name'  => 'align_content',
						'default'     => 'stretch',
						'group'       => esc_attr__( 'General', 'fusion-builder' ),
						'value'       => [
							'stretch'       => esc_attr__( 'Stretch', 'fusion-builder' ),
							'flex-start'    => esc_attr__( 'Flex Start', 'fusion-builder' ),
							'center'        => esc_attr__( 'Center', 'fusion-builder' ),
							'flex-end'      => esc_attr__( 'Flex End', 'fusion-builder' ),
							'space-between' => esc_attr__( 'Space Between', 'fusion-builder' ),
							'space-around'  => esc_attr__( 'Space Around', 'fusion-builder' ),
							'space-evenly'  => esc_attr__( 'Space Evenly', 'fusion-builder' ),
						],
						'icons'       => [
							'stretch'       => '<span class="fusiona-stretch"></span>',
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
								'element'  => 'hundred_percent_height',
								'value'    => 'no',
								'operator' => '!=',
							],
							[
								'element'  => 'type',
								'value'    => 'flex',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_update_flex_container',
							'args'     => [
								'selector' => '.fusion-fullwidth',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Column Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Select how you want columns to align within rows.', 'fusion-builder' ),
						'param_name'  => 'flex_align_items',
						'back_icons'  => true,
						'grid_layout' => true,
						'value'       => [
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
						'default'     => 'flex-start',
						'group'       => esc_attr__( 'General', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'type',
								'value'    => 'flex',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_update_flex_container',
							'args'     => [
								'selector' => '.fusion-fullwidth',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_html__( 'Column Justification', 'fusion-builder' ),
						'description' => esc_html__( 'Select how the columns will be justified horizontally.', 'fusion-builder' ),
						'param_name'  => 'flex_justify_content',
						'default'     => 'flex-start',
						'grid_layout' => true,
						'back_icons'  => true,
						'icons'       => [
							'flex-start'    => '<span class="fusiona-horizontal-flex-start"></span>',
							'center'        => '<span class="fusiona-horizontal-flex-center"></span>',
							'flex-end'      => '<span class="fusiona-horizontal-flex-end"></span>',
							'space-between' => '<span class="fusiona-horizontal-space-between"></span>',
							'space-around'  => '<span class="fusiona-horizontal-space-around"></span>',
							'space-evenly'  => '<span class="fusiona-horizontal-space-evenly"></span>',
						],
						'value'       => [
							// We use "start/end" terminology because flex direction changes depending on RTL/LTR.
							'flex-start'    => esc_html__( 'Flex Start', 'fusion-builder' ),
							'center'        => esc_html__( 'Center', 'fusion-builder' ),
							'flex-end'      => esc_html__( 'Flex End', 'fusion-builder' ),
							'space-between' => esc_html__( 'Space Between', 'fusion-builder' ),
							'space-around'  => esc_html__( 'Space Around', 'fusion-builder' ),
							'space-evenly'  => esc_html__( 'Space Evenly', 'fusion-builder' ),
						],
						'group'       => esc_attr__( 'General', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'type',
								'value'    => 'flex',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_update_flex_container',
							'args'     => [
								'selector' => '.fusion-fullwidth',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Column Spacing', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the spacing between columns of the container.', 'fusion-builder' ),
						'param_name'  => 'flex_column_spacing',
						'value'       => '',
						'dependency'  => [
							[
								'element'  => 'type',
								'value'    => 'flex',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Center Content', 'fusion-builder' ),
						'description' => esc_attr__( 'Set to "Yes" to center the content vertically on 100% height containers.', 'fusion-builder' ),
						'param_name'  => 'hundred_percent_height_center_content',
						'default'     => 'yes',
						'group'       => esc_attr__( 'General', 'fusion-builder' ),
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'hundred_percent_height',
								'value'    => 'yes',
								'operator' => '==',
							],
							[
								'element'  => 'type',
								'value'    => 'flex',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Set Columns to Equal Height', 'fusion-builder' ),
						'description' => esc_attr__( 'Select to set all columns that are used inside the container to have equal height.', 'fusion-builder' ),
						'param_name'  => 'equal_height_columns',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'no',
						'group'       => esc_attr__( 'General', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_toggle_class',
							'args'     => [
								'selector' => '.fusion-fullwidth',
								'classes'  => [
									'yes' => 'fusion-equal-height-columns',
									'no'  => '',
								],
							],
						],
						'dependency'  => [
							[
								'element'  => 'type',
								'value'    => 'flex',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Container HTML Tag', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose container HTML tag, default is div.', 'fusion-builder' ),
						'param_name'  => 'container_tag',
						'value'       => [
							'div'     => 'Default',
							'section' => 'Section',
							'header'  => 'Header',
							'footer'  => 'Footer',
							'main'    => 'Main',
							'article' => 'Article',
							'aside'   => 'Aside',
							'nav'     => 'Nav',
						],
						'default'     => 'div',
						'group'       => esc_attr__( 'General', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Name Of Menu Anchor', 'fusion-builder' ),
						'description' => esc_attr__( 'This name will be the id you will have to use in your one page menu.', 'fusion-builder' ),
						'param_name'  => 'menu_anchor',
						'value'       => '',
						'group'       => esc_attr__( 'General', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_add_id',
							'args'     => [
								'selector' => '.fusion-fullwidth',
							],
						],
					],
					[
						'type'        => 'checkbox_button_set',
						'heading'     => esc_attr__( 'Container Visibility', 'fusion-builder' ),
						'param_name'  => 'hide_on_mobile',
						'value'       => fusion_builder_visibility_options( 'full' ),
						'default'     => fusion_builder_default_visibility( 'array' ),
						'description' => esc_attr__( 'Choose to show or hide the section on small, medium or large screens. You can choose more than one at a time.', 'fusion-builder' ),
						'or'          => true,
						'dependency'  => [
							[
								'element'  => 'hundred_percent_height',
								'value'    => 'yes',
								'operator' => '!=',
							],
							[
								'element'  => 'hundred_percent_height_scroll',
								'value'    => 'yes',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Container Publishing Status', 'fusion-builder' ),
						'description' => __( 'Controls the publishing status of the container.  If draft is selected the container will only be visible to logged in users with the capability to publish posts.  If publish until or publish after are selected the container will be in draft mode when not published.', 'fusion-builder' ),
						'param_name'  => 'status',
						'default'     => 'published',
						'value'       => [
							'published'       => esc_attr__( 'Published', 'fusion-builder' ),
							'published_until' => esc_attr__( 'Published Until', 'fusion-builder' ),
							'publish_after'   => esc_attr__( 'Publish After', 'fusion-builder' ),
							'draft'           => esc_attr__( 'Draft', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'date_time_picker',
						'heading'     => esc_attr__( 'Container Publishing Date', 'fusion-builder' ),
						'description' => __( 'Controls when the container should be published.  Can be before a date or after a date.  Use SQL time format: YYYY-MM-DD HH:MM:SS. E.g: 2016-05-10 12:30:00.  Timezone of site is used.', 'fusion-builder' ),
						'param_name'  => 'publish_date',
						'value'       => '',
						'dependency'  => [
							[
								'element'  => 'status',
								'value'    => 'published',
								'operator' => '!=',
							],
							[
								'element'  => 'status',
								'value'    => 'draft',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS Class', 'fusion-builder' ),
						'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
						'param_name'  => 'class',
						'value'       => '',
						'group'       => esc_attr__( 'General', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_add_class',
							'args'     => [
								'selector' => '.fusion-fullwidth',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
						'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
						'param_name'  => 'id',
						'value'       => '',
						'group'       => esc_attr__( 'General', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_add_id',
							'args'     => [
								'selector' => '.fusion-fullwidth',
							],
						],
					],
					'fusion_margin_placeholder'     => [
						'param_name'  => 'spacing',
						'description' => esc_attr__( 'Spacing above and below the section. Enter values including any valid CSS unit, ex: 4%.', 'fusion-builder' ),
						'responsive'  => [
							'state' => 'large',
						],
						'callback'    => [
							'function' => 'fusion_container_margin',
							'args'     => [
								'selector'  => '.fusion-fullwidth',
								'property'  => [
									'margin_top'    => 'margin-top',
									'margin_bottom' => 'margin-bottom',
								],
								'dimension' => true,
							],
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Padding', 'fusion-builder' ),
						'description'      => esc_attr__( 'Enter values including any valid CSS unit, ex: 10px or 10%.', 'fusion-builder' ) . $default,
						'param_name'       => 'padding_dimensions',
						'value'            => [
							'padding_top'    => '',
							'padding_right'  => '',
							'padding_bottom' => '',
							'padding_left'   => '',
						],
						'responsive'       => [
							'state' => 'large',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'         => [
							'function' => 'fusion_container_padding',
							'args'     => [
								'selector'  => '.fusion-fullwidth',
								'property'  => [
									'padding_top'    => 'padding-top',
									'padding_right'  => 'padding-right',
									'padding_bottom' => 'padding-bottom',
									'padding_left'   => 'padding-left',
								],
								'dimension' => true,
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Container Link Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the color of container links.', 'fusion-builder' ),
						'param_name'  => 'link_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'link_color' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Container Link Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the color of container links in hover state.', 'fusion-builder' ),
						'param_name'  => 'link_hover_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'primary_color' ),
					],
					[
						'type'        => 'dimension',
						'heading'     => esc_attr__( 'Container Border Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border size of the container element.', 'fusion-builder' ),
						'param_name'  => 'border_sizes',
						'value'       => [
							'border_sizes_top'    => '',
							'border_sizes_right'  => '',
							'border_sizes_bottom' => '',
							'border_sizes_left'   => '',
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Container Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the container element.', 'fusion-builder' ),
						'param_name'  => 'border_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'full_width_border_color' ),
						'or'          => true,
						'dependency'  => [
							[
								'element'  => 'border_sizes_top',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'border_sizes_bottom',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'border_sizes_left',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'border_sizes_right',
								'value'    => '',
								'operator' => '!=',
							],
						],
						'callback'    => [
							'function' => 'fusion_preview',
							'args'     => [
								'selector' => '.fusion-fullwidth',
								'property' => 'border-color',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Border Style', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border style.', 'fusion-builder' ),
						'param_name'  => 'border_style',
						'value'       => [
							'solid'  => esc_attr__( 'Solid', 'fusion-builder' ),
							'dashed' => esc_attr__( 'Dashed', 'fusion-builder' ),
							'dotted' => esc_attr__( 'Dotted', 'fusion-builder' ),
						],
						'default'     => 'solid',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'or'          => true,
						'dependency'  => [
							[
								'element'  => 'border_sizes_top',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'border_sizes_bottom',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'border_sizes_left',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'border_sizes_right',
								'value'    => '',
								'operator' => '!=',
							],
						],
						'callback'    => [
							'function' => 'fusion_preview',
							'args'     => [
								'selector' => '.fusion-fullwidth',
								'property' => [ 'border-style' ],
							],
						],
					],
					'fusion_box_shadow_placeholder' => [
						'callback' => [
							'function' => 'fusion_update_box_shadow',
							'args'     => [
								'selector' => '.fusion-fullwidth',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Z Index', 'fusion-builder' ),
						'description' => esc_attr__( 'Value for container\'s z-index CSS property, can be both positive or negative.', 'fusion-builder' ),
						'param_name'  => 'z_index',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Overflow', 'fusion-builder' ),
						'description' => esc_attr__( 'Value for container\'s overflow CSS property.', 'fusion-builder' ),
						'param_name'  => 'overflow',
						'value'       => [
							''        => esc_attr__( 'Default', 'fusion-builder' ),
							'visible' => esc_attr__( 'Visible', 'fusion-builder' ),
							'scroll'  => esc_attr__( 'Scroll', 'fusion-builder' ),
							'hidden'  => esc_attr__( 'Hidden', 'fusion-builder' ),
							'auto'    => esc_attr__( 'Auto', 'fusion-builder' ),
							'clip'    => esc_attr__( 'Clip', 'fusion-builder' ),
						],
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'subgroup',
						'heading'          => esc_attr__( 'Background Options', 'fusion-builder' ),
						'description'      => esc_attr__( 'Use filters to see specific type of content.', 'fusion-builder' ),
						'param_name'       => 'background_type',
						'default'          => 'single',
						'group'            => esc_attr__( 'Background', 'fusion-builder' ),
						'remove_from_atts' => true,
						'value'            => [
							'single'   => esc_attr__( 'Color', 'fusion-builder' ),
							'gradient' => esc_attr__( 'Gradient', 'fusion-builder' ),
							'image'    => esc_attr__( 'Image', 'fusion-builder' ),
							'video'    => esc_attr__( 'Video', 'fusion-builder' ),
							'pattern'  => esc_attr__( 'Pattern', 'fusion-builder' ),
							'mask'     => esc_attr__( 'Mask', 'fusion-builder' ),
						],
						'icons'            => [
							'single'   => '<span class="fusiona-fill-drip-solid" style="font-size:18px;"></span>',
							'gradient' => '<span class="fusiona-gradient-fill" style="font-size:18px;"></span>',
							'image'    => '<span class="fusiona-image" style="font-size:18px;"></span>',
							'video'    => '<span class="fusiona-video" style="font-size:18px;"></span>',
							'pattern'  => '<span class="fusiona-background-pattern" style="font-size:18px;"></span>',
							'mask'     => '<span class="fusiona-background-mask" style="font-size:18px;"></span>',
						],
					],
					'fusion_gradient_placeholder'   => [
						'selector' => '.fusion-fullwidth',
						'defaults' => 'TO',
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Container Background Color', 'fusion-builder' ),
						'param_name'  => 'background_color',
						'value'       => '',
						'description' => esc_attr__( 'Controls the background color of the container element.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Background', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'background_type',
							'tab'  => 'single',
						],
						'default'     => $fusion_settings->get( 'full_width_bg_color' ),
						'callback'    => [
							'function' => 'fusion_preview',
							'args'     => [
								'selector' => '.fusion-fullwidth, .fullwidth-overlay',
								'property' => 'background-color',
							],
						],
					],
					[
						'type'         => 'upload',
						'heading'      => esc_attr__( 'Background Image', 'fusion-builder' ),
						'description'  => esc_attr__( 'Upload an image to display in the background.', 'fusion-builder' ),
						'param_name'   => 'background_image',
						'value'        => '',
						'group'        => esc_attr__( 'Background', 'fusion-builder' ),
						'dynamic_data' => true,
						'subgroup'     => [
							'name' => 'background_type',
							'tab'  => 'image',
						],
					],
					[
						'type'             => 'select',
						'heading'          => esc_attr__( 'Lazy Load', 'fusion-builder' ),
						'description'      => esc_attr__( 'Lazy load which is being used.', 'fusion-builder' ),
						'param_name'       => 'lazy_load',
						'value'            => [
							'avada'     => esc_attr__( 'Avada', 'fusion-builder' ),
							'wordpress' => esc_attr__( 'WordPress', 'fusion-builder' ),
							'none'      => esc_attr__( 'None', 'fusion-builder' ),
						],
						'default'          => $fusion_settings->get( 'lazy_load' ),
						'hidden'           => true,
						'remove_from_atts' => true,
						'group'            => esc_attr__( 'Background', 'fusion-builder' ),
						'subgroup'         => [
							'name' => 'background_type',
							'tab'  => 'image',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Skip Lazy Loading', 'fusion-builder' ),
						'description' => esc_attr__( 'Select whether you want to skip lazy loading on this image or not.', 'fusion-builder' ),
						'param_name'  => 'skip_lazy_load',
						'default'     => '',
						'group'       => esc_attr__( 'Background', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'background_type',
							'tab'  => 'image',
						],
						'value'       => [
							'skip' => esc_attr__( 'Yes', 'fusion-builder' ),
							''     => esc_attr__( 'No', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'lazy_load',
								'value'    => 'avada',
								'operator' => '==',
							],
							[
								'element'  => 'background_image',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Background Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the position of the background image.', 'fusion-builder' ),
						'param_name'  => 'background_position',
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
						'default'     => 'center center',
						'group'       => esc_attr__( 'Background', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'background_type',
							'tab'  => 'image',
						],
						'dependency'  => [
							[
								'element'  => 'background_image',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Background Repeat', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose how the background image repeats.', 'fusion-builder' ),
						'param_name'  => 'background_repeat',
						'value'       => [
							'no-repeat' => esc_attr__( 'No Repeat', 'fusion-builder' ),
							'repeat'    => esc_attr__( 'Repeat Vertically and Horizontally', 'fusion-builder' ),
							'repeat-x'  => esc_attr__( 'Repeat Horizontally', 'fusion-builder' ),
							'repeat-y'  => esc_attr__( 'Repeat Vertically', 'fusion-builder' ),
						],
						'default'     => 'no-repeat',
						'group'       => esc_attr__( 'Background', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'background_type',
							'tab'  => 'image',
						],
						'dependency'  => [
							[
								'element'  => 'background_image',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Fading Animation', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to have the background image fade and blur on scroll. WARNING: Only works for images.', 'fusion-builder' ),
						'param_name'  => 'fade',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'no',
						'group'       => esc_attr__( 'Background', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'background_type',
							'tab'  => 'image',
						],
						'dependency'  => [
							[
								'element'  => 'background_image',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'sticky',
								'value'    => 'on',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Background Parallax', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose how the background image scrolls and responds. This does not work for videos and must be set to "No Parallax" for the video to show.', 'fusion-builder' ),
						'param_name'  => 'background_parallax',
						'value'       => [
							'none'  => esc_attr__( 'No Parallax (no effects)', 'fusion-builder' ),
							'fixed' => esc_attr__( 'Fixed (fixed on desktop, non-fixed on mobile)', 'fusion-builder' ),
							'up'    => esc_attr__( 'Up (moves up on desktop and mobile)', 'fusion-builder' ),
							'down'  => esc_attr__( 'Down (moves down on desktop and mobile)', 'fusion-builder' ),
							'left'  => esc_attr__( 'Left (moves left on desktop and mobile)', 'fusion-builder' ),
							'right' => esc_attr__( 'Right (moves right on desktop and mobile)', 'fusion-builder' ),
						],
						'default'     => 'none',
						'group'       => esc_attr__( 'Background', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'background_type',
							'tab'  => 'image',
						],
						'dependency'  => [
							[
								'element'  => 'background_image',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'sticky',
								'value'    => 'on',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Enable Parallax on Mobile', 'fusion-builder' ),
						'description' => esc_attr__( 'Works for up/down/left/right only. Parallax effects would most probably cause slowdowns when your site is viewed in mobile devices. If the device width is less than 980 pixels, then it is assumed that the site is being viewed in a mobile device.', 'fusion-builder' ),
						'param_name'  => 'enable_mobile',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'no',
						'group'       => esc_attr__( 'Background', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'background_type',
							'tab'  => 'image',
						],
						'dependency'  => [
							[
								'element'  => 'background_image',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'background_parallax',
								'value'    => 'none',
								'operator' => '!=',
							],
							[
								'element'  => 'background_parallax',
								'value'    => 'fixed',
								'operator' => '!=',
							],
							[
								'element'  => 'sticky',
								'value'    => 'on',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Parallax Speed', 'fusion-builder' ),
						'description' => esc_attr__( 'The movement speed, value should be between 0.1 and 1.0. A lower number means slower scrolling speed. Higher scrolling speeds will enlarge the image more.', 'fusion-builder' ),
						'param_name'  => 'parallax_speed',
						'value'       => '0.3',
						'min'         => '0',
						'max'         => '1',
						'step'        => '0.1',
						'group'       => esc_attr__( 'Background', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'background_type',
							'tab'  => 'image',
						],
						'dependency'  => [
							[
								'element'  => 'background_image',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'background_parallax',
								'value'    => 'none',
								'operator' => '!=',
							],
							[
								'element'  => 'background_parallax',
								'value'    => 'fixed',
								'operator' => '!=',
							],
							[
								'element'  => 'sticky',
								'value'    => 'on',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Background Blend Mode', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose how blending should work for each background layer.', 'fusion-builder' ),
						'param_name'  => 'background_blend_mode',
						'value'       => [
							'none'        => esc_attr__( 'Disabled', 'fusion-builder' ),
							'multiply'    => esc_attr__( 'Multiply', 'fusion-builder' ),
							'screen'      => esc_attr__( 'Screen', 'fusion-builder' ),
							'overlay'     => esc_attr__( 'Overlay', 'fusion-builder' ),
							'darken'      => esc_attr__( 'Darken', 'fusion-builder' ),
							'lighten'     => esc_attr__( 'Lighten', 'fusion-builder' ),
							'color-dodge' => esc_attr__( 'Color Dodge', 'fusion-builder' ),
							'color-burn'  => esc_attr__( 'Color Burn', 'fusion-builder' ),
							'hard-light'  => esc_attr__( 'Hard Light', 'fusion-builder' ),
							'soft-light'  => esc_attr__( 'Soft Light', 'fusion-builder' ),
							'difference'  => esc_attr__( 'Difference', 'fusion-builder' ),
							'exclusion'   => esc_attr__( 'Exclusion', 'fusion-builder' ),
							'hue'         => esc_attr__( 'Hue', 'fusion-builder' ),
							'saturation'  => esc_attr__( 'Saturation', 'fusion-builder' ),
							'color'       => esc_attr__( 'Color', 'fusion-builder' ),
							'luminosity'  => esc_attr__( 'Luminosity', 'fusion-builder' ),
						],
						'default'     => 'none',
						'group'       => esc_attr__( 'Background', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'background_type',
							'tab'  => 'image',
						],
						'dependency'  => [
							[
								'element'  => 'background_image',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					[
						'type'         => 'uploadfile',
						'heading'      => esc_attr__( 'Video MP4 Upload', 'fusion-builder' ),
						'description'  => esc_attr__( 'Add your MP4 video file. This format must be included to render your video with cross-browser compatibility. WebM and OGV are optional. Using videos in a 16:9 aspect ratio is recommended.', 'fusion-builder' ),
						'param_name'   => 'video_mp4',
						'dynamic_data' => true,
						'value'        => '',
						'group'        => esc_attr__( 'Background', 'fusion-builder' ),
						'subgroup'     => [
							'name' => 'background_type',
							'tab'  => 'video',
						],
					],
					[
						'type'         => 'uploadfile',
						'heading'      => esc_attr__( 'Video WebM Upload', 'fusion-builder' ),
						'description'  => esc_attr__( 'Add your WebM video file. This is optional, only MP4 is required to render your video with cross-browser compatibility. Using videos in a 16:9 aspect ratio is recommended.', 'fusion-builder' ),
						'param_name'   => 'video_webm',
						'dynamic_data' => true,
						'value'        => '',
						'group'        => esc_attr__( 'Background', 'fusion-builder' ),
						'subgroup'     => [
							'name' => 'background_type',
							'tab'  => 'video',
						],
					],
					[
						'type'         => 'uploadfile',
						'heading'      => esc_attr__( 'Video OGV Upload', 'fusion-builder' ),
						'description'  => esc_attr__( 'Add your OGV video file. This is optional, only MP4 is required to render your video with cross-browser compatibility. Using videos in a 16:9 aspect ratio is recommended.', 'fusion-builder' ),
						'param_name'   => 'video_ogv',
						'dynamic_data' => true,
						'value'        => '',
						'group'        => esc_attr__( 'Background', 'fusion-builder' ),
						'subgroup'     => [
							'name' => 'background_type',
							'tab'  => 'video',
						],
					],
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'YouTube/Vimeo Video URL or ID', 'fusion-builder' ),
						'description'  => esc_attr__( "Enter the URL to the video or the video ID of your YouTube or Vimeo video you want to use as your background. If your URL isn't showing a video, try inputting the video ID instead. Ads will show up in the video if it has them.", 'fusion-builder' ),
						'param_name'   => 'video_url',
						'dynamic_data' => true,
						'value'        => '',
						'group'        => esc_attr__( 'Background', 'fusion-builder' ),
						'subgroup'     => [
							'name' => 'background_type',
							'tab'  => 'video',
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Video Aspect Ratio', 'fusion-builder' ),
						'description' => esc_attr__( 'The video will be resized to maintain this aspect ratio, this is to prevent the video from showing any black bars. Enter an aspect ratio here such as: "16:9", "4:3" or "16:10". The default is "16:9".', 'fusion-builder' ),
						'param_name'  => 'video_aspect_ratio',
						'value'       => '16:9',
						'group'       => esc_attr__( 'Background', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'background_type',
							'tab'  => 'video',
						],
						'or'          => true,
						'dependency'  => [
							[
								'element'  => 'video_mp4',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'video_ogv',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'video_webm',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'video_url',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					[
						'type'       => 'radio_button_set',
						'heading'    => esc_attr__( 'Loop Video', 'fusion-builder' ),
						'param_name' => 'video_loop',
						'value'      => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'    => 'yes',
						'group'      => esc_attr__( 'Background', 'fusion-builder' ),
						'subgroup'   => [
							'name' => 'background_type',
							'tab'  => 'video',
						],
						'or'         => true,
						'dependency' => [
							[
								'element'  => 'video_mp4',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'video_ogv',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'video_webm',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'video_url',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Mute Video', 'fusion-builder' ),
						'description' => __( '<strong>IMPORTANT:</strong> In some modern browsers, videos with sound won\'t be auto played, and thus won\'t show as container background when not muted.', 'fusion-builder' ),
						'param_name'  => 'video_mute',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'yes',
						'group'       => esc_attr__( 'Background', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'background_type',
							'tab'  => 'video',
						],
						'or'          => true,
						'dependency'  => [
							[
								'element'  => 'video_mp4',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'video_ogv',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'video_webm',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'video_url',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'upload',
						'heading'     => esc_attr__( 'Video Preview Image', 'fusion-builder' ),
						'description' => __( '<strong>IMPORTANT:</strong>  This field is a fallback for self-hosted videos in older browsers that are not able to play the video. If your site is optimized for modern browsers, this field does not need to be filled in.', 'fusion-builder' ),
						'param_name'  => 'video_preview_image',
						'value'       => '',
						'group'       => esc_attr__( 'Background', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'background_type',
							'tab'  => 'video',
						],
						'or'          => true,
						'dependency'  => [
							[
								'element'  => 'video_mp4',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'video_ogv',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'video_webm',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'video_url',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					'fusion_pattern_placeholder'    => [],
					'fusion_mask_placeholder'       => [],
					'fusion_conditional_render_placeholder' => [],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Position Absolute', 'fusion-builder' ),
						'description' => esc_attr__( 'Turn on to set container position to absolute.', 'fusion-builder' ),
						'param_name'  => 'absolute',
						'default'     => 'off',
						'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
						'value'       => [
							'on'  => esc_html__( 'On', 'fusion-builder' ),
							'off' => esc_html__( 'Off', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'checkbox_button_set',
						'heading'     => esc_attr__( 'Responsive Position Absolute', 'fusion-builder' ),
						'param_name'  => 'absolute_devices',
						'value'       => [
							'small'  => esc_attr__( 'Small Screen', 'fusion-builder' ),
							'medium' => esc_attr__( 'Medium Screen', 'fusion-builder' ),
							'large'  => esc_attr__( 'Large Screen', 'fusion-builder' ),
						],
						'icons'       => [
							'small'  => '<span class="fusiona-mobile"></span>',
							'medium' => '<span class="fusiona-tablet"></span>',
							'large'  => '<span class="fusiona-desktop"></span>',
						],
						'default'     => [ 'small', 'medium', 'large' ],
						'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose at which screen sizes the container should get position absolute on.', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'absolute',
								'value'    => 'on',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Position Sticky', 'fusion-builder' ),
						'description' => esc_attr__( 'Turn on to have the container stick to the browser window on scroll.', 'fusion-builder' ),
						'param_name'  => 'sticky',
						'default'     => 'off',
						'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
						'value'       => [
							'on'  => esc_html__( 'On', 'fusion-builder' ),
							'off' => esc_html__( 'Off', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'checkbox_button_set',
						'heading'     => esc_attr__( 'Responsive Position Sticky', 'fusion-builder' ),
						'param_name'  => 'sticky_devices',
						'value'       => fusion_builder_visibility_options( 'full' ),
						'default'     => fusion_builder_default_visibility( 'array' ),
						'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose at which screen sizes the container should be sticky.', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'sticky',
								'value'    => 'on',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Sticky Container Background Color', 'fusion-builder' ),
						'param_name'  => 'sticky_background_color',
						'value'       => '',
						'description' => esc_attr__( 'Controls the background color of the container element when sticky.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'full_width_bg_color' ),
						'dependency'  => [
							[
								'element'  => 'sticky',
								'value'    => 'on',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Sticky Container Minimum Height', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the minimum height of the container when sticky.', 'fusion-builder' ),
						'param_name'  => 'sticky_height',
						'value'       => '',
						'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'sticky',
								'value'    => 'on',
								'operator' => '==',
							],
							[
								'element'  => 'hundred_percent_height',
								'value'    => 'min',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Sticky Container Offset', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls how far the top of the container is offset from top of viewport when sticky. Use either a unit of measurement, or a CSS selector.', 'fusion-builder' ),
						'param_name'  => 'sticky_offset',
						'value'       => '',
						'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'sticky',
								'value'    => 'on',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Sticky Container Transition Offset', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the scroll offset before sticky styling transition applies. In pixels.', 'fusion-builder' ),
						'param_name'  => 'sticky_transition_offset',
						'value'       => '0',
						'min'         => '0',
						'max'         => '1000',
						'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'sticky',
								'value'    => 'on',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Sticky Container Hide On Scroll', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the scroll distance before container is hidden while scrolling downwards.  Set to 0 to keep visible as you scroll down.  In pixels.', 'fusion-builder' ),
						'param_name'  => 'scroll_offset',
						'value'       => '0',
						'min'         => '0',
						'max'         => '1000',
						'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'sticky',
								'value'    => 'on',
								'operator' => '==',
							],
						],
					],
					'fusion_animation_placeholder'  => [
						'preview_selector' => '.fusion-fullwidth',
					],
					'fusion_filter_placeholder'     => [
						'selector_base' => 'fusion-builder-row-live-',
					],
				],
			]
		)
	);
}
add_action( 'wp_loaded', 'fusion_builder_add_section' );
