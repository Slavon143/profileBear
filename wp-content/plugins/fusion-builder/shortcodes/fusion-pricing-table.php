<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_pricing_table' ) ) {

	if ( ! class_exists( 'FusionSC_PricingTable' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_PricingTable extends Fusion_Element {

			/**
			 * The pricing table counter.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $pricing_table_counter = 1;

			/**
			 * True if this is the first row, otherwise false.
			 *
			 * @access private
			 * @since 1.0
			 * @var bool
			 */
			private $is_first_row = true;

			/**
			 * True if this is the first column, otherwise defaults to false.
			 *
			 * @access private
			 * @since 1.0
			 * @var bool
			 */
			private $is_first_column = true;

			/**
			 * True if this is the list group is closed, otherwise false.
			 *
			 * @access private
			 * @since 1.0
			 * @var bool
			 */
			private $is_list_group_closed = false;

			/**
			 * Parent SC arguments.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array
			 */
			protected $parent_args;

			/**
			 * Arguments for the column.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array
			 */
			protected static $child_column_args;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_pricingtable-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_pricingtable-shortcode-column-wrapper', [ $this, 'column_wrapper_attr' ] );
				add_filter( 'fusion_attr_pricingtable-shortcode-price', [ $this, 'price_attr' ] );
				add_filter( 'fusion_attr_pricingtable-shortcode-row', [ $this, 'row_attr' ] );
				add_filter( 'fusion_attr_pricingtable-shortcode-footer', [ $this, 'footer_attr' ] );

				add_shortcode( 'fusion_pricing_table', [ $this, 'render_parent' ] );
				add_shortcode( 'fusion_pricing_column', [ $this, 'render_child_column' ] );
				add_shortcode( 'fusion_pricing_price', [ $this, 'render_child_price' ] );
				add_shortcode( 'fusion_pricing_row', [ $this, 'render_child_row' ] );
				add_shortcode( 'fusion_pricing_footer', [ $this, 'render_child_footer' ] );

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
					'margin_top'             => '',
					'margin_right'           => '',
					'margin_bottom'          => '',
					'margin_left'            => '',
					'hide_on_mobile'         => fusion_builder_default_visibility( 'string' ),
					'class'                  => '',
					'id'                     => '',
					'backgroundcolor'        => $fusion_settings->get( 'pricing_bg_color' ),
					'background_color_hover' => $fusion_settings->get( 'pricing_background_color_hover' ),
					'bordercolor'            => $fusion_settings->get( 'pricing_border_color' ),
					'heading_color_style_1'  => $fusion_settings->get( 'full_boxed_pricing_box_heading_color' ),
					'heading_color_style_2'  => $fusion_settings->get( 'sep_pricing_box_heading_color' ),
					'pricing_color'          => $fusion_settings->get( 'pricing_box_color' ),
					'body_text_color'        => $fusion_settings->get( 'body_typography', 'color' ),
					'columns'                => '',
					'dividercolor'           => $fusion_settings->get( 'pricing_divider_color' ),
					'type'                   => '1',
				];

				$child = [
					'class'    => 'fusion-pricingtable-column',
					'id'       => '',
					'standout' => 'no',
					'title'    => '',
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
			 * @since 2.0.0
			 * @param string $context Can be "parent" | "child" | "".
			 * @return array
			 */
			public static function settings_to_params( $context = '' ) {
				$parent = [
					'pricing_bg_color'                     => 'backgroundcolor',
					'pricing_background_color_hover'       => 'background_color_hover',
					'pricing_border_color'                 => 'bordercolor',
					'full_boxed_pricing_box_heading_color' => 'heading_color_style_1',
					'sep_pricing_box_heading_color'        => 'heading_color_style_2',
					'pricing_box_color'                    => 'pricing_color',
					'body_typography[color]'               => 'body_text_color',
					'pricing_divider_color'                => 'dividercolor',
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
			 * Render the parent shortcode
			 *
			 * @access public
			 * @since 1.0
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render_parent( $args, $content = '' ) {

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults( 'parent' ), $args, 'fusion_pricing_table' );

				// Make sure the bg color is set to border color in case it is not existing in the shortcode yet and border color is not specifically set.
				if ( ! array_key_exists( 'background_color_hover', $args ) && ( isset( $args['bordercolor'] ) && '' !== $args['bordercolor'] ) ) {
					$defaults['background_color_hover'] = $defaults['bordercolor'];
				}

				extract( $defaults );

				$this->parent_args = $defaults;

				$this->parent_args['columns'] = min( $this->parent_args['columns'], 6 );

				$this->parent_args['margin_bottom'] = FusionBuilder::validate_shortcode_attr_value( $this->parent_args['margin_bottom'], 'px' );
				$this->parent_args['margin_left']   = FusionBuilder::validate_shortcode_attr_value( $this->parent_args['margin_left'], 'px' );
				$this->parent_args['margin_right']  = FusionBuilder::validate_shortcode_attr_value( $this->parent_args['margin_right'], 'px' );
				$this->parent_args['margin_top']    = FusionBuilder::validate_shortcode_attr_value( $this->parent_args['margin_top'], 'px' );

				$this->set_num_of_columns( $content );

				$this->is_first_column = true;

				$styles = "<style type='text/css'>
				.pricing-table-{$this->pricing_table_counter} .panel-container, .pricing-table-{$this->pricing_table_counter} .standout .panel-container,
				.pricing-table-{$this->pricing_table_counter}.full-boxed-pricing {background-color:{$bordercolor};}
				.pricing-table-{$this->pricing_table_counter} .list-group .list-group-item,
				.pricing-table-{$this->pricing_table_counter} .list-group .list-group-item:last-child{background-color:{$backgroundcolor}; border-color:{$dividercolor};}
				.pricing-table-{$this->pricing_table_counter}.full-boxed-pricing .panel-wrapper:hover .panel-heading,
				.pricing-table-{$this->pricing_table_counter} .panel-wrapper:hover .list-group-item {background-color:{$background_color_hover};}
				.pricing-table-{$this->pricing_table_counter}.full-boxed-pricing .panel-heading{background-color:{$backgroundcolor};}
				.pricing-table-{$this->pricing_table_counter} .fusion-panel, .pricing-table-{$this->pricing_table_counter} .panel-wrapper:last-child .fusion-panel,
				.pricing-table-{$this->pricing_table_counter} .standout .fusion-panel, .pricing-table-{$this->pricing_table_counter}  .panel-heading,
				.pricing-table-{$this->pricing_table_counter} .panel-body, .pricing-table-{$this->pricing_table_counter} .panel-footer{border-color:{$dividercolor};}
				.pricing-table-{$this->pricing_table_counter} .panel-body,.pricing-table-{$this->pricing_table_counter} .panel-footer{background-color:{$bordercolor};}
				.pricing-table-{$this->pricing_table_counter}.sep-boxed-pricing .panel-heading h3{color:{$heading_color_style_2};}
				.pricing-table-{$this->pricing_table_counter}.full-boxed-pricing.fusion-pricing-table .panel-heading h3{color:{$heading_color_style_1};}
				.pricing-table-{$this->pricing_table_counter}.fusion-pricing-table .panel-body .price .decimal-part{color:{$pricing_color};}
				.pricing-table-{$this->pricing_table_counter}.fusion-pricing-table .panel-body .price .integer-part{color:{$pricing_color};}
				.pricing-table-{$this->pricing_table_counter} ul.list-group li{color:{$body_text_color};}
				</style>";

				fusion_element_rendering_elements( true );
				$html = $styles . '<div ' . FusionBuilder::attributes( 'pricingtable-shortcode' ) . '>' . do_shortcode( $content ) . '</div>';
				fusion_element_rendering_elements( false );

				$this->pricing_table_counter++;

				$this->on_render();

				return apply_filters( 'fusion_element_pricing_table_content', $html, $args );

			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function attr() {

				$attr = [
					'class' => '',
					'style' => '',
				];

				$type = 'sep';
				if ( '1' == $this->parent_args['type'] ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
					$type = 'full';
				}

				$attr['class'] = 'fusion-pricing-table pricing-table-' . $this->pricing_table_counter . ' ' . $type . '-boxed-pricing row fusion-columns-' . $this->parent_args['columns'] . ' columns-' . $this->parent_args['columns'] . ' fusion-clearfix';

				$attr = fusion_builder_visibility_atts( $this->parent_args['hide_on_mobile'], $attr );

				$attr['style'] .= Fusion_Builder_Margin_Helper::get_margins_style( $this->parent_args );

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
			 * @since 1.0
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render_child_column( $args, $content = '' ) {

				$defaults = FusionBuilder::set_shortcode_defaults(
					self::get_element_defaults( 'child' ),
					$args,
					'fusion_pricing_column'
				);

				extract( $defaults );

				self::$child_column_args = $defaults;

				$this->is_first_row = true;

				$html  = '<div ' . FusionBuilder::attributes( 'pricingtable-shortcode-column-wrapper' ) . '>';
				$html .= '<div ' . FusionBuilder::attributes( 'panel-container' ) . '>';
				$html .= '<div ' . FusionBuilder::attributes( 'fusion-panel' ) . '>';
				$html .= '<div ' . FusionBuilder::attributes( 'panel-heading' ) . '>';
				$html .= '<h3 ' . FusionBuilder::attributes( 'title-row' ) . '>' . $title . '</h3>';
				$html .= '</div>';
				$html .= do_shortcode( $content );

				if ( ! $this->is_list_group_closed ) {
					$html .= '</ul>';
				}

				$html .= '</div></div></div>';

				return $html;

			}

			/**
			 * Builds the column-wrapper attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function column_wrapper_attr() {

				$attr = [];

				$columns = 1;
				if ( $this->parent_args['columns'] ) {
					$columns = 12 / $this->parent_args['columns'];
				}

				$attr['class'] = 'panel-wrapper fusion-column column col-lg-' . $columns . ' col-md-' . $columns . ' col-sm-' . $columns;

				if ( '5' == $this->parent_args['columns'] ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
					$attr['class'] = 'panel-wrapper fusion-column column col-lg-2 col-md-2 col-sm-2';
				}

				if ( 'yes' === self::$child_column_args['standout'] ) {
					$attr['class'] .= ' standout';
				}

				if ( self::$child_column_args['class'] ) {
					$attr['class'] .= ' ' . self::$child_column_args['class'];
				}

				if ( self::$child_column_args['id'] ) {
					$attr['id'] = self::$child_column_args['id'];
				}

				return $attr;

			}

			/**
			 * Render the child shortcode
			 *
			 * @access public
			 * @since 1.0
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render_child_price( $args, $content = '' ) {

				$defaults = FusionBuilder::set_shortcode_defaults(
					[
						'currency'          => '',
						'currency_position' => 'left',
						'price'             => '',
						'time'              => '',
					],
					$args,
					'fusion_pricing_price'
				);
				$content  = apply_filters( 'fusion_shortcode_content', $content, 'fusion_pricing_price', $args );

				extract( $defaults );

				$html = '<div ' . FusionBuilder::attributes( 'panel-body pricing-row' ) . '></div>' . do_shortcode( $content );

				if ( isset( $price ) && ( ! empty( $price ) || ( '0' == $price ) ) ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison

					$pricing_class = $pricing = '';
					$price         = explode( '.', $price );
					if ( array_key_exists( '1', $price ) ) {
						$pricing_class = 'price-with-decimal';
					}

					if ( 'right' !== $currency_position ) {
						$pricing = '<span ' . FusionBuilder::attributes( 'currency' ) . '>' . $currency . '</span>';
					}

					$pricing .= '<span ' . FusionBuilder::attributes( 'integer-part' ) . '>' . $price[0] . '</span>';

					if ( array_key_exists( '1', $price ) ) {
						$pricing .= '<sup ' . FusionBuilder::attributes( 'decimal-part' ) . '>' . $price[1] . '</sup>';
					}

					if ( 'right' === $currency_position ) {
						$currency_classes = 'currency pos-right';
						$time_classes     = 'time pos-right';
						if ( ! array_key_exists( '1', $price ) ) {
							$currency_classes = 'currency pos-right price-without-decimal';
							$time_classes     = 'time pos-right price-without-decimal';
						}

						$pricing .= '<span ' . FusionBuilder::attributes( $currency_classes ) . '>' . $currency . '</span>';

						if ( $time ) {
							$pricing .= '<span ' . FusionBuilder::attributes( $time_classes ) . '>' . $time . '</span>';
						}
					}

					if ( $time && 'right' !== $currency_position ) {
						$time_classes = 'time';
						if ( ! array_key_exists( '1', $price ) ) {
							$time_classes = 'time price-without-decimal';
						}

						$pricing .= '<span ' . FusionBuilder::attributes( $time_classes ) . '>' . $time . '</span>';
					}

					$html  = '<div ' . FusionBuilder::attributes( 'panel-body pricing-row' ) . '>';
					$html .= '<div ' . FusionBuilder::attributes( 'price ' . $pricing_class ) . '>' . $pricing . '</div></div>';
					$html .= do_shortcode( $content );

				}

				return $html;

			}

			/**
			 * Render the child shortcode
			 *
			 * @access public
			 * @since 1.0
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render_child_row( $args, $content = '' ) {

				$html = '';

				if ( $this->is_first_row ) {
					$html               = '<ul ' . FusionBuilder::attributes( 'list-group' ) . '>';
					$this->is_first_row = false;
				}

				$html .= '<li ' . FusionBuilder::attributes( 'list-group-item normal-row' ) . '>' . do_shortcode( $content ) . '</li>';

				return $html;

			}

			/**
			 * Render the child shortcode
			 *
			 * @access public
			 * @since 1.0
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render_child_footer( $args, $content = '' ) {

				$html = '</ul><div ' . FusionBuilder::attributes( 'panel-footer footer-row' ) . '>' . do_shortcode( $content ) . '</div>';

				$this->is_list_group_closed = true;

				return $html;

			}

			/**
			 * Calculate the number of columns automatically.
			 *
			 * @access public
			 * @since 1.0
			 * @param string $content Content to be parsed.
			 */
			public function set_num_of_columns( $content ) {
				if ( ! $this->parent_args['columns'] ) {
					preg_match_all( '/(\[fusion_pricing_column (.*?)\](.*?)\[\/fusion_pricing_column\])/s', $content, $matches );
					$this->parent_args['columns'] = 1;
					if ( is_array( $matches ) && ! empty( $matches ) ) {
						$this->parent_args['columns'] = count( $matches[0] );
						if ( $this->parent_args['columns'] > 6 ) {
							$this->parent_args['columns'] = 6;
						}
					}
				} elseif ( $this->parent_args['columns'] > 6 ) {
					$this->parent_args['columns'] = 6;
				}
			}

			/**
			 * Builds the dynamic styling.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function add_styling() {

				global $wp_version, $content_min_media_query, $six_fourty_media_query, $three_twenty_six_fourty_media_query, $ipad_portrait_media_query, $dynamic_css_helpers;

				$fusion_settings = awb_get_fusion_settings();

				$css['global']['.full-boxed-pricing.fusion-pricing-table .panel-heading h3']['color'] = fusion_library()->sanitize->color( $fusion_settings->get( 'full_boxed_pricing_box_heading_color' ) );

				$css['global']['.sep-boxed-pricing .panel-heading h3']['color'] = fusion_library()->sanitize->color( $fusion_settings->get( 'sep_pricing_box_heading_color' ) );

				$css[ $content_min_media_query ]['.sep-boxed-pricing .panel-wrapper']['padding']                = '0';
				$css[ $content_min_media_query ]['.fusion-pricing-table .standout .panel-container']['z-index'] = '1000';
				$css[ $content_min_media_query ]['.fusion-pricing-table .standout .panel-footer, .fusion-pricing-table .standout .panel-heading']['padding'] = '20px';
				$css[ $content_min_media_query ]['.full-boxed-pricing']['padding']                  = '0 9px';
				$css[ $content_min_media_query ]['.full-boxed-pricing']['background-color']         = '#F8F8F8';
				$css[ $content_min_media_query ]['.full-boxed-pricing .panel-container']['padding'] = '9px 0';
				$css[ $content_min_media_query ]['.full-boxed-pricing .panel-wrapper:last-child .fusion-panel']['border-right'] = '1px solid #E5E4E3';
				$css[ $content_min_media_query ]['.full-boxed-pricing .fusion-panel']['border-right']                           = 'none';
				$css[ $content_min_media_query ]['.full-boxed-pricing .standout .panel-container']['position']                  = 'relative';
				$css[ $content_min_media_query ]['.full-boxed-pricing .standout .panel-container']['box-sizing']                = 'content-box';
				$css[ $content_min_media_query ]['.full-boxed-pricing .standout .panel-container']['margin']                    = '-10px -9px';
				$css[ $content_min_media_query ]['.full-boxed-pricing .standout .panel-container']['padding']                   = '9px';
				$css[ $content_min_media_query ]['.full-boxed-pricing .standout .panel-container']['box-shadow']                = '0 0 6px 6px rgba(0, 0, 0, 0.08)';
				$css[ $content_min_media_query ]['.full-boxed-pricing .standout .panel-container']['background-color']          = '#F8F8F8';
				$css[ $content_min_media_query ]['.full-boxed-pricing .standout .fusion-panel']['border-right']                 = '1px solid #E5E4E3';
				$css[ $content_min_media_query ]['.full-boxed-pricing .standout .panel-heading h3']['color']                    = '#65bc7b';
				$css[ $content_min_media_query ]['.sep-boxed-pricing']['margin']                                = '0 -15px 20px';
				$css[ $content_min_media_query ]['.sep-boxed-pricing .panel-wrapper']['margin']                 = '0';
				$css[ $content_min_media_query ]['.sep-boxed-pricing .panel-wrapper']['padding']                = '0 12px';
				$css[ $content_min_media_query ]['.sep-boxed-pricing .standout .panel-container']['margin']     = '-10px';
				$css[ $content_min_media_query ]['.sep-boxed-pricing .standout .panel-container']['box-shadow'] = '0 0 15px 5px rgba(0, 0, 0, 0.16)';

				$css[ $three_twenty_six_fourty_media_query ]['#wrapper .sep-boxed-pricing .panel-wrapper']['padding'] = '0';
				$css[ $ipad_portrait_media_query ]['#wrapper .sep-boxed-pricing .panel-wrapper']['padding']           = '0';

				$elements = [
					'.full-boxed-pricing .column',
					'.sep-boxed-pricing .column',
				];
				$css[ $six_fourty_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['float']                      = 'none';
				$css[ $six_fourty_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['margin-bottom']              = '10px';
				$css[ $six_fourty_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['margin-left']                = '0';
				$css[ $six_fourty_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['width']                      = '100%';
				$css[ $ipad_portrait_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['float']                   = 'none';
				$css[ $ipad_portrait_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['margin-bottom']           = '10px';
				$css[ $ipad_portrait_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['margin-left']             = '0';
				$css[ $ipad_portrait_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['width']                   = '100%';
				$css[ $three_twenty_six_fourty_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['float']         = 'none';
				$css[ $three_twenty_six_fourty_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['margin-bottom'] = '10px';
				$css[ $three_twenty_six_fourty_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['margin-left']   = '0';
				$css[ $three_twenty_six_fourty_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['width']         = '100%';

				return $css;
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/pricingtable.min.css' );
			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.1
			 * @return array $sections Pricing Table settings.
			 */
			public function add_options() {
				global $dynamic_css_helpers;

				$fusion_settings = awb_get_fusion_settings();

				return [
					'pricing_table_shortcode_section' => [
						'label'       => esc_html__( 'Pricing Table', 'fusion-builder' ),
						'description' => '',
						'id'          => 'pricing_table_shortcode_section',
						'type'        => 'accordion',
						'icon'        => 'fusiona-dollar',
						'fields'      => [
							'full_boxed_pricing_box_heading_color' => [
								'label'       => esc_html__( 'Pricing Box Style 1 Heading Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of style 1 pricing table headings.', 'fusion-builder' ),
								'id'          => 'full_boxed_pricing_box_heading_color',
								'default'     => 'var(--awb-color8)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
							],
							'sep_pricing_box_heading_color' => [
								'label'       => esc_html__( 'Pricing Box Style 2 Heading Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of style 2 pricing table headings.', 'fusion-builder' ),
								'id'          => 'sep_pricing_box_heading_color',
								'default'     => 'var(--awb-color8)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
							],
							'pricing_box_color'     => [
								'label'       => esc_html__( 'Pricing Box Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color portions of pricing boxes.', 'fusion-builder' ),
								'id'          => 'pricing_box_color',
								'default'     => 'var(--awb-color4)',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--pricing_box_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'pricing_bg_color'      => [
								'label'       => esc_html__( 'Pricing Box Background Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the main background and title background.', 'fusion-builder' ),
								'id'          => 'pricing_bg_color',
								'default'     => 'var(--awb-color1)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
							],
							'pricing_background_color_hover' => [
								'label'       => esc_html__( 'Pricing Box Background Hover Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the hover color of the main background and title background.', 'fusion-builder' ),
								'id'          => 'pricing_background_color_hover',
								'default'     => 'var(--awb-color2)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
							],
							'pricing_border_color'  => [
								'label'       => esc_html__( 'Pricing Box Border Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the outer border, pricing row and footer row backgrounds.', 'fusion-builder' ),
								'id'          => 'pricing_border_color',
								'default'     => 'var(--awb-color2)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
							],
							'pricing_divider_color' => [
								'label'       => esc_html__( 'Pricing Box Divider Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the dividers in-between pricing rows.', 'fusion-builder' ),
								'id'          => 'pricing_divider_color',
								'default'     => 'var(--awb-color3)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
							],
						],
					],
				];
			}
		}
	}

	new FusionSC_PricingTable();

}

/**
 * Map shortcode to Avada Builder
 *
 * @since 1.0
 */
function fusion_element_pricing_table() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_PricingTable',
			[
				'name'                                    => esc_attr__( 'Pricing Table', 'fusion-builder' ),
				'shortcode'                               => 'fusion_pricing_table',
				'multi'                                   => 'multi_element_parent',
				'element_child'                           => 'fusion_pricing_column',
				'child_ui'                                => true,
				'icon'                                    => 'fusiona-dollar',
				'preview'                                 => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-pricing-table-preview.php',
				'preview_id'                              => 'fusion-builder-block-module-pricing-table-preview-template',

				'custom_settings_view_name'               => 'ModuleSettingsTableView',
				'custom_settings_view_js'                 => FUSION_BUILDER_PLUGIN_URL . 'inc/templates/custom/js/fusion-pricing-table-settings.js',
				'custom_settings_template_file'           => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/custom/fusion-pricing-table-settings.php',
				'front_end_custom_settings_view_js'       => FUSION_BUILDER_PLUGIN_URL . 'inc/templates/custom/front-end/js/fusion-pricing-table-settings.js',
				'front_end_custom_settings_template_file' => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/custom/front-end/fusion-pricing-table-settings.php',
				// 'custom_settings_template_css'  => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/custom/css/fusion-pricing-table-settings.css',
				'on_save'                                 => 'pricingTableShortcodeFilter',
				'on_change'                               => 'pricingTableShortcodeFilter',
				'admin_enqueue_js'                        => FUSION_BUILDER_PLUGIN_URL . 'shortcodes/js/fusion-pricing-table.js',
				'help_url'                                => 'https://theme-fusion.com/documentation/avada/elements/pricing-table-element/',
				'params'                                  => [
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Type', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the type of pricing table.', 'fusion-builder' ),
						'param_name'  => 'type',
						'value'       => [
							'1' => esc_attr__( 'Style 1', 'fusion-builder' ),
							'2' => esc_attr__( 'Style 2', 'fusion-builder' ),
						],
						'default'     => '1',
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Set pricing table background color. ', 'fusion-builder' ),
						'param_name'  => 'backgroundcolor',
						'value'       => '',
						'default'     => $fusion_settings->get( 'pricing_bg_color' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Background Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Set pricing table background hover color. ', 'fusion-builder' ),
						'param_name'  => 'background_color_hover',
						'value'       => '',
						'default'     => $fusion_settings->get( 'pricing_background_color_hover' ),
						'preview'     => [
							'selector' => '.panel-wrapper',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Set pricing table border color.', 'fusion-builder' ),
						'param_name'  => 'bordercolor',
						'value'       => '',
						'default'     => $fusion_settings->get( 'pricing_border_color' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Divider Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Set pricing table divider color.', 'fusion-builder' ),
						'param_name'  => 'dividercolor',
						'value'       => '',
						'default'     => $fusion_settings->get( 'pricing_divider_color' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Heading Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Set pricing table headings color.', 'fusion-builder' ),
						'param_name'  => 'heading_color_style_1',
						'value'       => '',
						'default'     => $fusion_settings->get( 'full_boxed_pricing_box_heading_color' ),
						'dependency'  => [
							[
								'element'  => 'type',
								'value'    => '1',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Heading Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Set pricing table headings color.', 'fusion-builder' ),
						'param_name'  => 'heading_color_style_2',
						'value'       => '',
						'default'     => $fusion_settings->get( 'sep_pricing_box_heading_color' ),
						'dependency'  => [
							[
								'element'  => 'type',
								'value'    => '2',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Pricing Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Set pricing table price text color.', 'fusion-builder' ),
						'param_name'  => 'pricing_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'pricing_box_color' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Body Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Set pricing table body text color', 'fusion-builder' ),
						'param_name'  => 'body_text_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'body_typography', 'color' ),
					],
					[
						'type'        => 'textarea',
						'heading'     => esc_attr__( 'Short Code', 'fusion-builder' ),
						'description' => esc_attr__( 'Pricing Table short code content.', 'fusion-builder' ),
						'param_name'  => 'element_content',
						'value'       => '[fusion_pricing_column title="Standard" standout="no" class="" id=""][fusion_pricing_price currency="$" price="15.55" time="monthly"][/fusion_pricing_price][fusion_pricing_row]Feature 1[/fusion_pricing_row][fusion_pricing_row]Feature 2[/fusion_pricing_row][fusion_pricing_footer]Order Now[/fusion_pricing_footer][/fusion_pricing_column][fusion_pricing_column title="Premium" standout="yes" class="" id=""][fusion_pricing_price currency="$" price="25.55" time="monthly"][/fusion_pricing_price][fusion_pricing_row]Feature 1[/fusion_pricing_row][fusion_pricing_row]Feature 2[/fusion_pricing_row][fusion_pricing_footer]Order Now[/fusion_pricing_footer][/fusion_pricing_column]',
						'hidden'      => true,
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
						'group'       => esc_attr__( 'General', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
						'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
						'param_name'  => 'id',
						'value'       => '',
						'group'       => esc_attr__( 'General', 'fusion-builder' ),
					],
				],
			],
			'parent'
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_pricing_table' );

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_pricing_table_column() {

	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_PricingTable',
			[
				'name'                              => esc_attr__( 'Pricing Column', 'fusion-builder' ),
				'description'                       => esc_attr__( 'Pricing table column.', 'fusion-builder' ),
				'shortcode'                         => 'fusion_pricing_column',
				'hide_from_builder'                 => true,
				'allow_generator'                   => true,
				'custom_settings_view_name'         => 'ModuleSettingsColumnView',
				'front_end_custom_settings_view_js' => FUSION_BUILDER_PLUGIN_URL . 'inc/templates/custom/front-end/js/fusion-pricing-column-settings.js',
				'inline_editor'                     => true,
				'params'                            => [
					[
						'type'       => 'tinymce',
						'param_name' => 'element_content',
						'value'      => '[fusion_pricing_price currency="$" price="25.55" time="monthly"][/fusion_pricing_price][fusion_pricing_row]Feature 1[/fusion_pricing_row][fusion_pricing_row]Feature 2[/fusion_pricing_row][fusion_pricing_footer]Order Now[/fusion_pricing_footer]',
						'hidden'     => true,
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Column Title', 'fusion-builder' ),
						'description' => esc_attr__( 'The title for the pricing column.', 'fusion-builder' ),
						'param_name'  => 'title',
						'value'       => esc_attr__( 'Standard', 'fusion-builder' ),
						'placeholder' => true,
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Standout', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to apply standout styling to the pricing column..', 'fusion-builder' ),
						'param_name'  => 'standout',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'no',
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Currency Symbol', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the curreny symbol if desired.', 'fusion-builder' ),
						'param_name'  => 'currency',
						'value'       => '$',
						'callback'    => [
							'function' => 'fusionPricingTablePrice',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Currency Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to flip the icon.', 'fusion-builder' ),
						'param_name'  => 'currency_position',
						'value'       => [
							'left'  => esc_attr__( 'Before', 'fusion-builder' ),
							'right' => esc_attr__( 'After', 'fusion-builder' ),
						],
						'default'     => 'left',
						'callback'    => [
							'function' => 'fusionPricingTablePrice',
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Price', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the price.', 'fusion-builder' ),
						'param_name'  => 'price',
						'value'       => '15.99',
						'callback'    => [
							'function' => 'fusionPricingTablePrice',
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Time Period', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the period for the price.', 'fusion-builder' ),
						'param_name'  => 'time',
						'value'       => 'monthly',
						'callback'    => [
							'function' => 'fusionPricingTablePrice',
						],
					],
					[
						'type'        => 'sortable_text',
						'heading'     => esc_attr__( 'Featured Rows', 'fusion-builder' ),
						'description' => esc_attr__( 'Organize and add content to the pricing table.', 'fusion-builder' ),
						'param_name'  => 'feature_rows',
						'placeholder' => 'Feature',
						'add_label'   => 'Add Feature Row',
						'default'     => 'Feature 1|Feature 2',
						'callback'    => [
							'function' => 'fusionPricingTableRows',
						],
					],
					[
						'type'        => 'tinymce',
						'heading'     => esc_attr__( 'Footer Content', 'fusion-builder' ),
						'description' => esc_attr__( 'Enter some content for column footer.', 'fusion-builder' ),
						'param_name'  => 'footer_content',
						'callback'    => [
							'function' => 'fusionPricingTableFooter',
						],
					],
				],
			],
			'child'
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_pricing_table_column' );
