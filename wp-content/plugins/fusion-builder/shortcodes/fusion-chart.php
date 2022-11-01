<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.5
 */

if ( fusion_is_element_enabled( 'fusion_chart' ) ) {

	if ( ! class_exists( 'FusionSC_Chart' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @package fusion-builder
		 * @since 1.5
		 */
		class FusionSC_Chart extends Fusion_Element {

			/**
			 * Chart SC counter.
			 *
			 * @access protected
			 * @since 1.5
			 * @var int
			 */
			protected $chart_sc_counter = 1;

			/**
			 * The chart dataset counter.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $chart_dataset_counter = 0;

			/**
			 * Parent SC arguments.
			 *
			 * @access protected
			 * @since 1.5
			 * @var array
			 */
			protected $parent_args;

			/**
			 * Child SC arguments.
			 *
			 * @access protected
			 * @since 1.5
			 * @var array
			 */
			protected $child_args;

			/**
			 * Child legend text colors.
			 *
			 * @access protected
			 * @since 1.5
			 * @var array
			 */
			protected $child_legend_text_colors = [];

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_chart-shortcode', [ $this, 'parent_attr' ] );
				add_shortcode( 'fusion_chart', [ $this, 'render_parent' ] );

				add_filter( 'fusion_attr_chart-dataset-shortcode', [ $this, 'child_attr' ] );
				add_shortcode( 'fusion_chart_dataset', [ $this, 'render_child' ] );
			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @param string $context Whether we want parent or child.
			 *                        Returns array( parent, child ) if empty.
			 * @return array
			 */
			public static function get_element_defaults( $context = '' ) {
				$fusion_settings = awb_get_fusion_settings();

				$parent = [
					'margin_bottom'            => '',
					'margin_left'              => '',
					'margin_right'             => '',
					'margin_top'               => '',
					'hide_on_mobile'           => fusion_builder_default_visibility( 'string' ),
					'title'                    => '',
					'chart_padding'            => '',
					'chart_type'               => '',
					'bg_colors'                => '',
					'border_colors'            => '',
					'chart_legend_position'    => $fusion_settings->get( 'chart_legend_position' ),
					'legend_labels'            => '',
					'legend_text_colors'       => '',
					'x_axis_labels'            => '',
					'x_axis_label'             => '',
					'y_axis_label'             => '',
					'show_tooltips'            => $fusion_settings->get( 'chart_show_tooltips' ),
					'chart_border_size'        => 1,
					'chart_border_type'        => 'smooth',
					'chart_fill'               => 'start',
					'chart_point_style'        => '',
					'chart_point_size'         => '',
					'chart_point_bg_color'     => '',
					'chart_point_border_color' => '',
					'chart_bg_color'           => $fusion_settings->get( 'chart_bg_color' ),
					'chart_axis_text_color'    => $fusion_settings->get( 'chart_axis_text_color' ),
					'chart_gridline_color'     => $fusion_settings->get( 'chart_gridline_color' ),
					'class'                    => '',
					'id'                       => '',
				];

				$child = [
					'title'             => '',
					'values'            => '',
					'legend_text_color' => '',
					'background_color'  => '',
					'border_color'      => '',
				];

				if ( 'parent' === $context ) {
					return $parent;
				} elseif ( 'child' === $context ) {
					return $child;
				} else {
					return [
						'parent' => $parent,
						'child'  => $child,
					];
				}
			}

			/**
			 * Maps settings to param variables.
			 *
			 * @static
			 * @access public
			 * @param string $context Whether we want parent or child.
			 * @since 2.0.0
			 * @return array
			 */
			public static function settings_to_params( $context = '' ) {

				$parent = [
					'chart_legend_position' => 'chart_legend_position',
					'chart_show_tooltips'   => 'show_tooltips',
					'chart_bg_color'        => 'chart_bg_color',
					'chart_axis_text_color' => 'chart_axis_text_color',
					'chart_gridline_color'  => 'chart_gridline_color',
				];

				$child = [];

				if ( 'parent' === $context ) {
					return $parent;
				} elseif ( 'child' === $context ) {
					return $child;
				} else {
					return [
						'parent' => $parent,
						'child'  => $child,
					];
				}
			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 1.5
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render_parent( $args, $content = '' ) {

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults( 'parent' ), $args, 'fusion_chart' );

				$defaults['chart_padding'] = [
					'top'    => isset( $args['padding_top'] ) && '' !== $args['padding_top'] ? $args['padding_top'] : 0,
					'right'  => isset( $args['padding_right'] ) && '' !== $args['padding_right'] ? $args['padding_right'] : 0,
					'bottom' => isset( $args['padding_bottom'] ) && '' !== $args['padding_bottom'] ? $args['padding_bottom'] : 0,
					'left'   => isset( $args['padding_left'] ) && '' !== $args['padding_left'] ? $args['padding_left'] : 0,
				];

				$this->parent_args = $defaults;

				$this->parent_args['margin_bottom'] = FusionBuilder::validate_shortcode_attr_value( $this->parent_args['margin_bottom'], 'px' );
				$this->parent_args['margin_left']   = FusionBuilder::validate_shortcode_attr_value( $this->parent_args['margin_left'], 'px' );
				$this->parent_args['margin_right']  = FusionBuilder::validate_shortcode_attr_value( $this->parent_args['margin_right'], 'px' );
				$this->parent_args['margin_top']    = FusionBuilder::validate_shortcode_attr_value( $this->parent_args['margin_top'], 'px' );

				$this->validate_parent_args();

				$html  = '<div ' . FusionBuilder::attributes( 'chart-shortcode' ) . '>';
				$html .= do_shortcode( $content );

				if ( '' !== $this->parent_args['title'] ) {
					$html .= '<h4 class="fusion-chart-title">' . esc_html( $this->parent_args['title'] ) . '</h4>';
				}

				$html .= '<div class="fusion-chart-inner">';
				$html .= '<div class="fusion-chart-wrap">';
				$html .= '<canvas></canvas>';
				$html .= '</div>';

				if ( 'off' !== $this->parent_args['chart_legend_position'] ) {
					$html .= '<div class="fusion-chart-legend-wrap"></div>';
				}

				$html .= '</div>';
				$html .= '</div>';

				$styles = '';

				if ( '' !== $this->parent_args['chart_bg_color'] ) {
					$styles .= '#fusion-chart-' . $this->chart_sc_counter . '{background-color: ' . $this->parent_args['chart_bg_color'] . ';}';
				}

				if ( ! empty( $this->parent_args['chart_padding'] ) && is_array( $this->parent_args['chart_padding'] ) ) {
					$styles .= '#fusion-chart-' . $this->chart_sc_counter . '{padding: ' . implode( ' ', $this->parent_args['chart_padding'] ) . ';}';
				}

				if ( '' !== $this->parent_args['legend_text_colors'] ) {
					if ( 'pie' === $this->parent_args['chart_type'] || 'doughnut' === $this->parent_args['chart_type'] || 'polarArea' === $this->parent_args['chart_type'] || ( ( 'bar' === $this->parent_args['chart_type'] || 'horizontalBar' === $this->parent_args['chart_type'] ) && 1 === $this->chart_dataset_counter ) ) {
						$colors = explode( '|', $this->parent_args['legend_text_colors'] );
					} else {
						$colors = $this->child_legend_text_colors;
					}

					$color_count = count( $colors );
					for ( $i = 0; $i < $color_count; $i++ ) {
						if ( '' !== $colors[ $i ] ) {
							$styles .= '#fusion-chart-' . $this->chart_sc_counter . ' .fusion-chart-legend-wrap li:nth-child(' . ( $i + 1 ) . ') span{color: ' . $colors[ $i ] . ';}';
						}
					}
				}

				if ( $styles ) {
					$styles = '<style type="text/css">' . $styles . '</style>';
				}

				$this->chart_sc_counter++;

				// Reset child element counter.
				$this->chart_dataset_counter    = 0;
				$this->child_legend_text_colors = [];

				$this->on_render();

				return apply_filters( 'fusion_element_chart_content', $styles . $html, $args );
			}

			/**
			 * Builds the prent attributes array.
			 *
			 * @access public
			 * @since 1.5
			 * @return array
			 */
			public function parent_attr() {

				$attr = fusion_builder_visibility_atts(
					$this->parent_args['hide_on_mobile'],
					[
						'id'    => 'fusion-chart-' . $this->chart_sc_counter,
						'class' => 'fusion-chart',
						'style' => '',
					]
				);

				if ( $this->parent_args['chart_type'] ) {
					$attr['data-type'] = $this->parent_args['chart_type'];
				}

				if ( $this->parent_args['chart_legend_position'] && 'off' !== $this->parent_args['chart_legend_position'] ) {
					$attr['class'] .= ' legend-' . $this->parent_args['chart_legend_position'];

					$attr['data-chart_legend_position'] = $this->parent_args['chart_legend_position'];
				}

				if ( $this->parent_args['x_axis_labels'] ) {
					$attr['data-x_axis_labels'] = $this->parent_args['x_axis_labels'];
				}

				if ( $this->parent_args['x_axis_label'] ) {
					$attr['data-x_axis_label'] = $this->parent_args['x_axis_label'];
				}

				if ( $this->parent_args['y_axis_label'] ) {
					$attr['data-y_axis_label'] = $this->parent_args['y_axis_label'];
				}

				if ( $this->parent_args['show_tooltips'] ) {
					$attr['data-show_tooltips'] = $this->parent_args['show_tooltips'];
				}

				if ( $this->parent_args['bg_colors'] ) {
					$attr['data-bg_colors'] = $this->parent_args['bg_colors'];
				}

				if ( $this->parent_args['border_colors'] ) {
					$attr['data-border_colors'] = $this->parent_args['border_colors'];
				}

				if ( $this->parent_args['legend_labels'] ) {
					$attr['data-legend_labels'] = $this->parent_args['legend_labels'];
				}

				if ( '' !== $this->parent_args['chart_border_size'] ) {
					$attr['data-border_size'] = (int) $this->parent_args['chart_border_size'];
				}

				if ( $this->parent_args['chart_border_type'] ) {
					$attr['data-border_type'] = $this->parent_args['chart_border_type'];
				}

				if ( $this->parent_args['chart_fill'] ) {
					$attr['data-chart_fill'] = $this->parent_args['chart_fill'];
				}

				if ( $this->parent_args['chart_point_style'] ) {
					$attr['data-chart_point_style'] = $this->parent_args['chart_point_style'];
				}

				if ( '' !== $this->parent_args['chart_point_size'] ) {
					$attr['data-chart_point_size'] = $this->parent_args['chart_point_size'];
				}

				if ( $this->parent_args['chart_point_bg_color'] ) {
					$attr['data-chart_point_bg_color'] = Fusion_Color::new_color( $this->parent_args['chart_point_bg_color'] )->toCss( 'rgba' );
				}

				if ( $this->parent_args['chart_point_border_color'] ) {
					$attr['data-chart_point_border_color'] = Fusion_Color::new_color( $this->parent_args['chart_point_border_color'] )->toCss( 'rgba' );
				}

				if ( $this->parent_args['chart_axis_text_color'] ) {
					$attr['data-chart_axis_text_color'] = Fusion_Color::new_color( $this->parent_args['chart_axis_text_color'] )->toCss( 'rgba' );
				}

				if ( $this->parent_args['chart_gridline_color'] ) {
					$attr['data-chart_gridline_color'] = Fusion_Color::new_color( $this->parent_args['chart_gridline_color'] )->toCss( 'rgba' );
				}

				$attr['style'] = Fusion_Builder_Margin_Helper::get_margins_style( $this->parent_args );

				if ( $this->parent_args['class'] ) {
					$attr['class'] .= ' ' . $this->parent_args['class'];
				}

				if ( $this->parent_args['id'] ) {
					$attr['id'] = $this->parent_args['id'];
				}

				return $attr;

			}

			/**
			 * Render the child shortcode
			 *
			 * @access public
			 * @since 1.5
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render_child( $args, $content = '' ) {

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults( 'child' ), $args, 'fusion_chart_dataset' );

				$this->child_args = $defaults;

				$this->child_legend_text_colors[] = $this->child_args['legend_text_color'];

				$html = '<div ' . FusionBuilder::attributes( 'chart-dataset-shortcode' ) . '></div>';

				$this->chart_dataset_counter++;

				return $html;

			}

			/**
			 * Builds the child attributes array.
			 *
			 * @access public
			 * @since 1.5
			 * @return array
			 */
			public function child_attr() {

				$attr = [
					'class' => 'fusion-chart-dataset',
				];

				if ( $this->child_args['title'] ) {
					$attr['data-label'] = $this->child_args['title'];
				} else {
					$attr['data-label'] = ' ';
				}

				if ( $this->child_args['values'] ) {
					$attr['data-values'] = $this->child_args['values'];
				}

				if ( $this->child_args['background_color'] ) {
					$attr['data-background_color'] = Fusion_Color::new_color( $this->child_args['background_color'] )->toCss( 'rgba' );
				}

				if ( $this->child_args['border_color'] ) {
					$attr['data-border_color'] = Fusion_Color::new_color( $this->child_args['border_color'] )->toCss( 'rgba' );
				}

				return $attr;

			}

			/**
			 * Validate parent args and set to object array.
			 *
			 * @access public
			 * @since 3.6
			 * @return void
			 */
			public function validate_parent_args() {
				// validate bg_colors.
				if ( $this->parent_args['bg_colors'] ) {
					$this->parent_args['bg_colors'] = $this->global_color_to_rgba( $this->parent_args['bg_colors'] );
				}

				// validate border_colors.
				if ( $this->parent_args['border_colors'] ) {
					$this->parent_args['border_colors'] = $this->global_color_to_rgba( $this->parent_args['border_colors'] );
				}
			}

			/**
			 * Convert global colors to rgba.
			 *
			 * @access public
			 * @since 3.6
			 * @param  string $value    Color parameters.
			 * @return string
			 */
			public function global_color_to_rgba( $value ) {
				$colors     = explode( '|', $value );
				$new_colors = [];
				if ( $colors ) {
					foreach ( $colors as $color ) {
						$new_colors[] = ! empty( $color ) ? Fusion_Color::new_color( $color )->toCss( 'rgba' ) : '';
					}
					return implode( '|', $new_colors );
				}
				return $value;
			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.5
			 * @return array $sections Chart settings.
			 */
			public function add_options() {

				return [
					'chart_shortcode_section' => [
						'label'       => esc_html__( 'Chart', 'fusion-builder' ),
						'description' => '',
						'id'          => 'chart_shortcode_section',
						'type'        => 'accordion',
						'icon'        => 'fusiona-bar-chart',
						'fields'      => [
							'chart_legend_position' => [
								'label'       => esc_attr__( 'Legend Position', 'fusion-builder' ),
								'description' => esc_attr__( 'Set chart legend position. Note that on mobile devices legend will be positioned below the chart when left or right position is used.', 'fusion-builder' ),
								'id'          => 'chart_legend_position',
								'default'     => 'top',
								'type'        => 'radio-buttonset',
								'transport'   => 'postMessage',
								'choices'     => [
									'top'    => esc_attr__( 'Top', 'fusion-builder' ),
									'right'  => esc_attr__( 'Right', 'fusion-builder' ),
									'bottom' => esc_attr__( 'Bottom', 'fusion-builder' ),
									'left'   => esc_attr__( 'Left', 'fusion-builder' ),
									'off'    => esc_attr__( 'Off', 'fusion-builder' ),
								],
							],
							'chart_show_tooltips'   => [
								'label'       => esc_attr__( 'Show Tooltips', 'fusion-builder' ),
								'description' => esc_attr__( 'Choose whether tooltips should be displayed on hover. If your chart is in a column and the column has a hover type or link, tooltips are disabled.', 'fusion-builder' ),
								'id'          => 'chart_show_tooltips',
								'default'     => 'yes',
								'type'        => 'radio-buttonset',
								'transport'   => 'postMessage',
								'choices'     => [
									'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
									'no'  => esc_attr__( 'No', 'fusion-builder' ),
								],
							],
							'chart_bg_color'        => [
								'label'       => esc_attr__( 'Chart Background Color', 'fusion-builder' ),
								'description' => esc_attr__( 'Controls the background of the chart.', 'fusion-builder' ),
								'id'          => 'chart_bg_color',
								'default'     => 'rgba(255,255,255,0)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
							],
							'chart_axis_text_color' => [
								'label'       => esc_attr__( 'Chart Axis Text Color', 'fusion-builder' ),
								'description' => esc_attr__( 'Controls the text color of the x-axis and y-axis.', 'fusion-builder' ),
								'id'          => 'chart_axis_text_color',
								'default'     => 'var(--awb-color8)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
							],
							'chart_gridline_color'  => [
								'label'       => esc_attr__( 'Chart Gridline Color', 'fusion-builder' ),
								'description' => esc_attr__( 'Controls the color of the chart background grid lines and values.', 'fusion-builder' ),
								'id'          => 'chart_gridline_color',
								'default'     => 'hsla(var(--awb-color8-h),var(--awb-color8-s),var(--awb-color8-l),calc(var(--awb-color8-a) - 90%))',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
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

				Fusion_Dynamic_JS::enqueue_script(
					'fusion-chartjs',
					FusionBuilder::$js_folder_url . '/library/Chart.js',
					FusionBuilder::$js_folder_path . '/library/Chart.js',
					[],
					'2.7.1',
					true
				);

				Fusion_Dynamic_JS::enqueue_script(
					'fusion-chart',
					FusionBuilder::$js_folder_url . '/general/fusion-chart.js',
					FusionBuilder::$js_folder_path . '/general/fusion-chart.js',
					[ 'jquery', 'fusion-chartjs' ],
					'1',
					true
				);
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/chart.min.css' );
			}
		}
	}

	new FusionSC_Chart();

}

/**
 * Map shortcode to Avada Builder
 *
 * @since 1.0
 */
function fusion_element_chart() {
	$fusion_settings = awb_get_fusion_settings();
	$is_builder      = ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() );
	$to_link         = '';

	if ( $is_builder ) {
		$to_link = '<span class="fusion-panel-shortcut" data-fusion-option="h4_typography">' . __( 'Global Options', 'fusion-builder' ) . '</span>';
	} else {
		$to_link = '<a href="' . esc_url_raw( $fusion_settings->get_setting_link( 'h4_typography' ) ) . '" target="_blank">' . esc_attr__( 'Global Options', 'fusion-builder' ) . '</a>';
	}

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Chart',
			[
				'name'                                    => esc_attr__( 'Chart', 'fusion-builder' ),
				'shortcode'                               => 'fusion_chart',
				'icon'                                    => 'fusiona-bar-chart',
				'preview'                                 => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-chart-preview.php',
				'multi'                                   => 'multi_element_parent',
				'element_child'                           => 'fusion_chart_dataset',
				'custom_settings_view_name'               => 'ModuleSettingsChartView',
				'custom_settings_view_js'                 => FUSION_BUILDER_PLUGIN_URL . 'inc/templates/custom/js/fusion-chart-settings.js',
				'custom_settings_template_file'           => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/custom/fusion-chart-settings.php',
				'front_end_custom_settings_view_js'       => FUSION_BUILDER_PLUGIN_URL . 'inc/templates/custom/front-end/js/fusion-chart-settings.js',
				'front_end_custom_settings_template_file' => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/custom/front-end/fusion-chart-settings.php',
				'on_save'                                 => 'chartShortcodeFilter',
				'admin_enqueue_js'                        => FUSION_BUILDER_PLUGIN_URL . 'shortcodes/js/fusion-chart.js',
				'preview_id'                              => 'fusion-builder-block-module-chart-preview-template',
				'child_ui'                                => true,
				'help_url'                                => 'https://theme-fusion.com/documentation/avada/elements/chart-element/',
				'params'                                  => [
					[
						'type'             => 'hidden',
						'heading'          => esc_attr__( 'Chart Data', 'fusion-builder' ),
						'param_name'       => 'fake-chart-option',
						'remove_from_atts' => true,
						'callback'         => [
							'function' => 'chartShortcodeFilter',
							'ajax'     => false,
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Title', 'fusion-builder' ),
						/* translators: Link containing the "Global Options" text. */
						'description' => sprintf( esc_html__( 'The chart title utilizes all the H4 typography settings in %s except for top and bottom margins.', 'fusion-builder' ), $to_link ),
						'param_name'  => 'title',
						'value'       => '',
						'css_class'   => 'fusion-debounce-change',
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Chart Type', 'fusion-builder' ),
						'description' => esc_attr__( 'Select chart type.', 'fusion-builder' ),
						'param_name'  => 'chart_type',
						'default'     => 'bar',
						'value'       => [
							'bar'           => esc_attr__( 'Bar', 'fusion-builder' ),
							'horizontalBar' => esc_attr__( 'Horizontal Bar', 'fusion-builder' ),
							'line'          => esc_attr__( 'Line', 'fusion-builder' ),
							'pie'           => esc_attr__( 'Pie', 'fusion-builder' ),
							'doughnut'      => esc_attr__( 'Doughnut', 'fusion-builder' ),
							'radar'         => esc_attr__( 'Radar', 'fusion-builder' ),
							'polarArea'     => esc_attr__( 'Polar Area', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Legend Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Set chart legend position. Note that on mobile devices legend will be positioned below the chart when left or right position is used.', 'fusion-builder' ),
						'param_name'  => 'chart_legend_position',
						'value'       => [
							''       => esc_attr__( 'Default', 'fusion-builder' ),
							'top'    => esc_attr__( 'Top', 'fusion-builder' ),
							'right'  => esc_attr__( 'Right', 'fusion-builder' ),
							'bottom' => esc_attr__( 'Bottom', 'fusion-builder' ),
							'left'   => esc_attr__( 'Left', 'fusion-builder' ),
							'off'    => esc_attr__( 'Off', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'X Axis Label', 'fusion-builder' ),
						'description' => esc_attr__( 'Set X axis label.', 'fusion-builder' ),
						'param_name'  => 'x_axis_label',
						'value'       => '',
						'css_class'   => 'fusion-debounce-change',
						'dependency'  => [
							[
								'element'  => 'chart_type',
								'value'    => 'pie',
								'operator' => '!=',
							],
							[
								'element'  => 'chart_type',
								'value'    => 'doughnut',
								'operator' => '!=',
							],
							[
								'element'  => 'chart_type',
								'value'    => 'polarArea',
								'operator' => '!=',
							],
							[
								'element'  => 'chart_type',
								'value'    => 'radar',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Y Axis Label', 'fusion-builder' ),
						'description' => esc_attr__( 'Set Y axis label.', 'fusion-builder' ),
						'param_name'  => 'y_axis_label',
						'value'       => '',
						'css_class'   => 'fusion-debounce-change',
						'dependency'  => [
							[
								'element'  => 'chart_type',
								'value'    => 'pie',
								'operator' => '!=',
							],
							[
								'element'  => 'chart_type',
								'value'    => 'doughnut',
								'operator' => '!=',
							],
							[
								'element'  => 'chart_type',
								'value'    => 'polarArea',
								'operator' => '!=',
							],
							[
								'element'  => 'chart_type',
								'value'    => 'radar',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Tooltips', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose whether tooltips should be displayed on hover. If your chart is in a column and the column has a hover type or link, tooltips are disabled.', 'fusion-builder' ),
						'param_name'  => 'show_tooltips',
						'value'       => [
							''    => esc_attr__( 'Default', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Border Type', 'fusion-builder' ),
						'description' => esc_attr__( 'Select border type.', 'fusion-builder' ),
						'param_name'  => 'chart_border_type',
						'value'       => [
							'smooth'     => esc_attr__( 'Smooth', 'fusion-builder' ),
							'non_smooth' => esc_attr__( 'Non smooth', 'fusion-builder' ),
							'stepped'    => esc_attr__( 'Stepped', 'fusion-builder' ),
						],
						'default'     => 'smooth',
						'dependency'  => [
							[
								'element'  => 'chart_type',
								'value'    => 'line',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Chart Fill', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose how line chart should be filled.', 'fusion-builder' ),
						'param_name'  => 'chart_fill',
						'value'       => [
							'start'  => esc_attr__( 'Start', 'fusion-builder' ),
							'end'    => esc_attr__( 'End', 'fusion-builder' ),
							'origin' => esc_attr__( 'Origin', 'fusion-builder' ),
							'off'    => esc_attr__( 'Not filled', 'fusion-builder' ),
						],
						'default'     => 'off',
						'dependency'  => [
							[
								'element'  => 'chart_type',
								'value'    => 'line',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Point Style', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose point style for line charts.', 'fusion-builder' ),
						'param_name'  => 'chart_point_style',
						'value'       => [
							'circle'      => esc_attr__( 'Circle', 'fusion-builder' ),
							'cross'       => esc_attr__( 'Cross', 'fusion-builder' ),
							'crossRot'    => esc_attr__( 'Cross Rotated', 'fusion-builder' ),
							'dash'        => esc_attr__( 'Dash', 'fusion-builder' ),
							'line'        => esc_attr__( 'Line', 'fusion-builder' ),
							'rect'        => esc_attr__( 'Rectangle', 'fusion-builder' ),
							'rectRounded' => esc_attr__( 'Rectangle Rounded', 'fusion-builder' ),
							'rectRot'     => esc_attr__( 'Rectangle Rotated', 'fusion-builder' ),
							'star'        => esc_attr__( 'Star', 'fusion-builder' ),
							'triangle'    => esc_attr__( 'Triangle', 'fusion-builder' ),
						],
						'default'     => 'circle',
						'dependency'  => [
							[
								'element'  => 'chart_type',
								'value'    => 'line',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Point Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose point size for line charts.', 'fusion-builder' ),
						'param_name'  => 'chart_point_size',
						'value'       => '3',
						'min'         => '0',
						'max'         => '20',
						'step'        => '1',
						'dependency'  => [
							[
								'element'  => 'chart_type',
								'value'    => 'line',
								'operator' => '==',
							],
							[
								'element'  => 'chart_point_style',
								'value'    => 'cross',
								'operator' => '!=',
							],
							[
								'element'  => 'chart_point_style',
								'value'    => 'crossRot',
								'operator' => '!=',
							],
							[
								'element'  => 'chart_point_style',
								'value'    => 'line',
								'operator' => '!=',
							],
							[
								'element'  => 'chart_point_style',
								'value'    => 'dash',
								'operator' => '!=',
							],
							[
								'element'  => 'chart_point_style',
								'value'    => 'star',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Point Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose point background color for line charts.', 'fusion-builder' ),
						'param_name'  => 'chart_point_bg_color',
						'dependency'  => [
							[
								'element'  => 'chart_type',
								'value'    => 'line',
								'operator' => '==',
							],
							[
								'element'  => 'chart_point_style',
								'value'    => 'cross',
								'operator' => '!=',
							],
							[
								'element'  => 'chart_point_style',
								'value'    => 'crossRot',
								'operator' => '!=',
							],
							[
								'element'  => 'chart_point_style',
								'value'    => 'line',
								'operator' => '!=',
							],
							[
								'element'  => 'chart_point_style',
								'value'    => 'dash',
								'operator' => '!=',
							],
							[
								'element'  => 'chart_point_style',
								'value'    => 'star',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Point Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose point border color for line charts.', 'fusion-builder' ),
						'param_name'  => 'chart_point_border_color',
						'dependency'  => [
							[
								'element'  => 'chart_type',
								'value'    => 'line',
								'operator' => '==',
							],
						],
					],
					'fusion_margin_placeholder' => [
						'param_name' => 'margin',
						'group'      => esc_attr__( 'General', 'fusion-builder' ),
						'value'      => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
						],
					],
					[
						'type'        => 'checkbox_button_set',
						'heading'     => esc_attr__( 'Element Visibility', 'fusion-builder' ),
						'param_name'  => 'hide_on_mobile',
						'value'       => fusion_builder_visibility_options( 'full' ),
						'default'     => fusion_builder_default_visibility( 'array' ),
						'description' => esc_attr__( 'Choose to show or hide the element on small, medium or large screens. You can choose more than one at a time.', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS Class', 'fusion-builder' ),
						'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
						'param_name'  => 'class',
						'value'       => '',
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
						'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
						'param_name'  => 'id',
						'value'       => '',
					],
				],
			],
			'parent'
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_chart' );

/**
 * Map shortcode to Avada Builder
 */
function fusion_element_chart_value() {

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Chart',
			[
				'name'                                    => esc_attr__( 'Chart Value', 'fusion-builder' ),
				'description'                             => esc_attr__( 'Enter some content for this textblock', 'fusion-builder' ),
				'shortcode'                               => 'fusion_chart_dataset',
				'custom_settings_view_name'               => 'ModuleSettingsChartTableView',
				'front_end_custom_settings_view_js'       => FUSION_BUILDER_PLUGIN_URL . 'inc/templates/custom/front-end/js/fusion-chart-table-settings.js',
				'front_end_custom_settings_template_file' => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/custom/front-end/fusion-chart-table-settings.php',
				'hide_from_builder'                       => true,
				'allow_generator'                         => true,
				'params'                                  => [
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Label', 'fusion-builder' ),
						'description' => esc_attr__( 'Add a label for chart function.', 'fusion-builder' ),
						'param_name'  => 'title',
						'placeholder' => true,
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Value', 'fusion-builder' ),
						'description' => __( 'Enter values for axis. <strong>NOTE:</strong> Separate values with "|".', 'fusion-builder' ),
						'param_name'  => 'values',
						'placeholder' => true,
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background color. ', 'fusion-builder' ),
						'param_name'  => 'background_color',
						'value'       => '',
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color. ', 'fusion-builder' ),
						'param_name'  => 'border_color',
						'value'       => '',
					],
				],
			],
			'child'
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_chart_value' );
