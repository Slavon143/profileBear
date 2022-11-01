<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_tabs' ) ) {

	if ( ! class_exists( 'FusionSC_Tabs' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_Tabs extends Fusion_Element {

			/**
			 * Tabs counter.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $tabs_counter = 1;

			/**
			 * Tab counter.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $tab_counter = 1;

			/**
			 * Array of our tabs.
			 *
			 * @access private
			 * @since 1.0
			 * @var array
			 */
			private $tabs = [];

			/**
			 * Whether the tab is active or not.
			 *
			 * @access private
			 * @since 1.0
			 * @var bool
			 */
			private $active = false;

			/**
			 * Parent SC arguments.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array
			 */
			protected $parent_args;

			/**
			 * Child SC arguments.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array
			 */
			protected $child_args;

			/**
			 * Parent fusion_tabs SC arguments.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array
			 */
			protected $fusion_tabs_args;

			/**
			 * Child fusion_tab SC arguments.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array
			 */
			protected $fusion_tab_args;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_tabs-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_tabs-shortcode-navtabs', [ $this, 'navtabs_attr' ] );
				add_filter( 'fusion_attr_tabs-shortcode-link', [ $this, 'link_attr' ] );
				add_filter( 'fusion_attr_tabs-shortcode-icon', [ $this, 'icon_attr' ] );
				add_filter( 'fusion_attr_tabs-shortcode-tab', [ $this, 'tab_attr' ] );

				add_shortcode( 'fusion_old_tabs', [ $this, 'render_parent' ] );
				add_shortcode( 'fusion_old_tab', [ $this, 'render_child' ] );

				add_shortcode( 'fusion_tabs', [ $this, 'fusion_tabs' ] );
				add_shortcode( 'fusion_tab', [ $this, 'fusion_tab' ] );

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
					'hide_on_mobile'                 => fusion_builder_default_visibility( 'string' ),
					'class'                          => '',
					'id'                             => '',
					'backgroundcolor'                => $fusion_settings->get( 'tabs_bg_color' ),
					'bordercolor'                    => $fusion_settings->get( 'tabs_border_color' ),
					'active_border_color'            => $fusion_settings->get( 'primary_color' ),
					'icon_position'                  => $fusion_settings->get( 'tabs_icon_position' ),
					'icon_size'                      => $fusion_settings->get( 'tabs_icon_size' ),
					'icon_color'                     => $fusion_settings->get( 'tabs_icon_color' ),
					'icon_active_color'              => $fusion_settings->get( 'tabs_icon_active_color' ),
					'design'                         => 'classic',
					'inactivecolor'                  => $fusion_settings->get( 'tabs_inactive_color' ),
					'justified'                      => 'yes',
					'sticky_tabs'                    => 'no',
					'sticky_tabs_offset'             => '',
					'layout'                         => 'horizontal',
					'alignment'                      => '',
					'fusion_font_family_title_font'  => '',
					'fusion_font_variant_title_font' => '',
					'title_font_size'                => '',
					'title_line_height'              => '',
					'title_letter_spacing'           => '',
					'title_text_transform'           => '',
					'title_text_color'               => $fusion_settings->get( 'tabs_title_color' ),
					'title_active_text_color'        => $fusion_settings->get( 'tabs_active_title_color' ),
					'title_tag'                      => 'h4',
					'title_padding_top'              => $fusion_settings->get( 'tabs_title_padding', 'top' ),
					'title_padding_right'            => $fusion_settings->get( 'tabs_title_padding', 'right' ),
					'title_padding_bottom'           => $fusion_settings->get( 'tabs_title_padding', 'bottom' ),
					'title_padding_left'             => $fusion_settings->get( 'tabs_title_padding', 'left' ),
					'content_padding_top'            => $fusion_settings->get( 'tabs_content_padding', 'top' ),
					'content_padding_right'          => $fusion_settings->get( 'tabs_content_padding', 'right' ),
					'content_padding_bottom'         => $fusion_settings->get( 'tabs_content_padding', 'bottom' ),
					'content_padding_left'           => $fusion_settings->get( 'tabs_content_padding', 'left' ),
					'mobile_mode'                    => $fusion_settings->get( 'tabs_mobile_mode', false, 'accordion' ),
					'mobile_sticky_tabs'             => $fusion_settings->get( 'tabs_mobile_sticky_tabs', false, 'no' ),
					// margin.
					'margin_top'                     => '',
					'margin_right'                   => '',
					'margin_bottom'                  => '',
					'margin_left'                    => '',
					'margin_top_medium'              => '',
					'margin_right_medium'            => '',
					'margin_bottom_medium'           => '',
					'margin_left_medium'             => '',
					'margin_top_small'               => '',
					'margin_right_small'             => '',
					'margin_bottom_small'            => '',
					'margin_left_small'              => '',
				];

				$child = [
					'icon'       => 'none',
					'id'         => '',
					'fusion_tab' => 'no',
				];

				if ( 'parent' === $context ) {
					return $parent;
				} elseif ( 'child' === $context ) {
					return $child;
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
					'tabs_bg_color'                => 'backgroundcolor',
					'tabs_border_color'            => 'bordercolor',
					'tabs_icon_position'           => 'icon_position',
					'tabs_icon_size'               => 'icon_size',
					'tabs_inactive_color'          => 'inactivecolor',
					'tabs_icon_color'              => 'icon_color',
					'tabs_icon_active_color'       => 'icon_active_color',
					'tabs_title_color'             => 'title_text_color',
					'tabs_active_title_color'      => 'title_active_text_color',
					'tabs_title_padding[top]'      => 'title_padding_top',
					'tabs_title_padding[right]'    => 'title_padding_right',
					'tabs_title_padding[bottom]'   => 'title_padding_bottom',
					'tabs_title_padding[left]'     => 'title_padding_left',
					'tabs_content_padding[top]'    => 'content_padding_top',
					'tabs_content_padding[right]'  => 'content_padding_right',
					'tabs_content_padding[bottom]' => 'content_padding_bottom',
					'tabs_content_padding[left]'   => 'content_padding_left',
					'tabs_mobile_mode'             => 'mobile_mode',
					'tabs_mobile_sticky_tabs'      => 'mobile_sticky_tabs',
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
			 * Render the parent shortcode.
			 *
			 * @access public
			 * @since 1.0
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render_parent( $args, $content = '' ) {

				$html     = '';
				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults( 'parent' ), $args );

				extract( $defaults );

				$this->parent_args = $defaults;

				$styles = '.fusion-tabs.fusion-tabs-' . $this->tabs_counter . ' .nav-tabs li a.tab-link{border-top-color:' . $this->parent_args['inactivecolor'] . ';background-color:' . $this->parent_args['inactivecolor'] . ';}';
				if ( 'clean' !== $design ) {
					$styles .= '.fusion-tabs.fusion-tabs-' . $this->tabs_counter . ' .nav-tabs{background-color:' . $this->parent_args['backgroundcolor'] . ';}';
					$styles .= '.fusion-tabs.fusion-tabs-' . $this->tabs_counter . ' .nav-tabs li.active a.tab-link,.fusion-tabs.fusion-tabs-' . $this->tabs_counter . ' .nav-tabs li.active a.tab-link:hover,.fusion-tabs.fusion-tabs-' . $this->tabs_counter . ' .nav-tabs li.active a.tab-link:focus{border-right-color:' . $this->parent_args['backgroundcolor'] . ';}';
				} else {
					$styles = '#wrapper .fusion-tabs.fusion-tabs-' . $this->tabs_counter . '.clean .nav-tabs li a.tab-link{border-color:' . $this->parent_args['bordercolor'] . ';}.fusion-tabs.fusion-tabs-' . $this->tabs_counter . ' .nav-tabs li a.tab-link{background-color:' . $this->parent_args['inactivecolor'] . ';}';
				}
				if ( 'classic' === $design && '' !== $this->parent_args['active_border_color'] ) {
					$styles .= '.fusion-tabs.fusion-tabs-' . $this->tabs_counter . '.classic .nav-tabs > li.active .tab-link, .fusion-tabs.fusion-tabs-' . $this->tabs_counter . '.classic .nav-tabs > li.active .tab-link:hover { border-color: ' . $this->parent_args['active_border_color'] . ';}';
				}
				$styles .= '.fusion-tabs.fusion-tabs-' . $this->tabs_counter . ' .nav-tabs li.active a.tab-link,.fusion-tabs.fusion-tabs-' . $this->tabs_counter . ' .nav-tabs li.active a.tab-link:hover,.fusion-tabs.fusion-tabs-' . $this->tabs_counter . ' .nav-tabs li.active a.tab-link:focus{background-color:' . $this->parent_args['backgroundcolor'] . ';}';
				$styles .= '.fusion-tabs.fusion-tabs-' . $this->tabs_counter . ' .nav-tabs li a:hover{background-color:' . $this->parent_args['backgroundcolor'] . ';border-top-color:' . $this->parent_args['backgroundcolor'] . ';}';
				$styles .= '.fusion-tabs.fusion-tabs-' . $this->tabs_counter . ' .tab-pane{background-color:' . $this->parent_args['backgroundcolor'] . ';}';
				$styles .= '.fusion-tabs.fusion-tabs-' . $this->tabs_counter . ' .nav,.fusion-tabs.fusion-tabs-' . $this->tabs_counter . ' .nav-tabs,.fusion-tabs.fusion-tabs-' . $this->tabs_counter . ' .tab-content .tab-pane{border-color:' . $this->parent_args['bordercolor'] . ';}';

				if ( 'no' === $this->parent_args['justified'] && 'vertical' !== $this->parent_args['layout'] ) {
					$styles .= '' !== $this->parent_args['alignment'] ? '.fusion-tabs.fusion-tabs-' . $this->tabs_counter . ' .nav:not(.fusion-mobile-tab-nav){display:flex;justify-content:' . $this->parent_args['alignment'] . ';}' : '';

					if ( 'accordion' === $this->parent_args['mobile_mode'] || 'toggle' === $this->parent_args['mobile_mode'] ) {
						global $content_media_query;

						$styles .= $content_media_query . '{ .fusion-tabs.fusion-tabs-' . $this->tabs_counter . ' .nav:not(.fusion-mobile-tab-nav){
							display: none;
						}}';
					}
				}

				// Title typography.
				$title_typography  = '';
				$title_typography .= Fusion_Builder_Element_Helper::get_font_styling( $this->parent_args, 'title_font', 'string', true );
				$title_typography .= ! empty( $this->parent_args['title_font_size'] ) ? 'font-size: ' . fusion_library()->sanitize->get_value_with_unit( $this->parent_args['title_font_size'] ) . ' !important;' : '';
				$title_typography .= ! empty( $this->parent_args['title_line_height'] ) ? 'line-height: ' . fusion_library()->sanitize->size( $this->parent_args['title_line_height'] ) . ' !important;' : '';
				$title_typography .= ! empty( $this->parent_args['title_letter_spacing'] ) ? 'letter-spacing: ' . fusion_library()->sanitize->get_value_with_unit( $this->parent_args['title_letter_spacing'] ) . ' !important;' : '';
				$title_typography .= ! empty( $this->parent_args['title_text_transform'] ) ? 'text-transform: ' . $this->parent_args['title_text_transform'] . ' !important;' : '';
				$title_typography .= ! empty( $this->parent_args['title_text_color'] ) ? 'color: ' . $this->parent_args['title_text_color'] . ' !important;' : '';
				$styles           .= '' !== $title_typography ? '.fusion-tabs.fusion-tabs-' . $this->tabs_counter . ' .nav-tabs li .fusion-tab-heading {' . $title_typography . '}' : '';

				// Active title color.
				$styles .= ! empty( $this->parent_args['title_active_text_color'] ) ? '.fusion-tabs.fusion-tabs-' . $this->tabs_counter . ' .nav-tabs li.active .fusion-tab-heading, .fusion-tabs.fusion-tabs-' . $this->tabs_counter . ' .nav-tabs li:hover .fusion-tab-heading { color:' . $this->parent_args['title_active_text_color'] . ' !important;}' : '';

				// Title Padding.
				$title_padding  = '';
				$title_padding .= isset( $this->parent_args['title_padding_top'] ) && '' !== $this->parent_args['title_padding_top'] ? 'padding-top: ' . fusion_library()->sanitize->get_value_with_unit( $this->parent_args['title_padding_top'] ) . ' !important;' : '';
				$title_padding .= isset( $this->parent_args['title_padding_right'] ) && '' !== $this->parent_args['title_padding_right'] ? 'padding-right: ' . fusion_library()->sanitize->get_value_with_unit( $this->parent_args['title_padding_right'] ) . ' !important;' : '';
				$title_padding .= isset( $this->parent_args['title_padding_bottom'] ) && '' !== $this->parent_args['title_padding_bottom'] ? 'padding-bottom: ' . fusion_library()->sanitize->get_value_with_unit( $this->parent_args['title_padding_bottom'] ) . ' !important;' : '';
				$title_padding .= isset( $this->parent_args['title_padding_left'] ) && '' !== $this->parent_args['title_padding_left'] ? 'padding-left: ' . fusion_library()->sanitize->get_value_with_unit( $this->parent_args['title_padding_left'] ) . ' !important;' : '';
				$styles        .= '' !== $title_padding ? '.fusion-tabs.fusion-tabs-' . $this->tabs_counter . ' .nav-tabs li .tab-link {' . $title_padding . '}' : '';

				// content Padding.
				$content_padding  = '';
				$content_padding .= isset( $this->parent_args['content_padding_top'] ) && '' !== $this->parent_args['content_padding_top'] ? 'padding-top: ' . fusion_library()->sanitize->get_value_with_unit( $this->parent_args['content_padding_top'] ) . ' !important;' : '';
				$content_padding .= isset( $this->parent_args['content_padding_right'] ) && '' !== $this->parent_args['content_padding_right'] ? 'padding-right: ' . fusion_library()->sanitize->get_value_with_unit( $this->parent_args['content_padding_right'] ) . ' !important;' : '';
				$content_padding .= isset( $this->parent_args['content_padding_bottom'] ) && '' !== $this->parent_args['content_padding_bottom'] ? 'padding-bottom: ' . fusion_library()->sanitize->get_value_with_unit( $this->parent_args['content_padding_bottom'] ) . ' !important;' : '';
				$content_padding .= isset( $this->parent_args['content_padding_left'] ) && '' !== $this->parent_args['content_padding_left'] ? 'padding-left: ' . fusion_library()->sanitize->get_value_with_unit( $this->parent_args['content_padding_left'] ) . ' !important;' : '';
				$styles          .= '' !== $content_padding ? '.fusion-tabs.fusion-tabs-' . $this->tabs_counter . ' .tab-content .tab-pane {' . $content_padding . '}' : '';

				$styles .= $this->build_margin_styles();
				$styles  = '<style type="text/css">' . $styles . '</style>';

				$html = '';
				if ( 'yes' === $this->parent_args['sticky_tabs'] && 'horizontal' === $this->parent_args['layout'] ) {
					$html .= '<div class="fusion-tabs-sticky-helper" style="height:1px;"></div>';
				}

				$html .= '<div ' . FusionBuilder::attributes( 'tabs-shortcode' ) . '>' . $styles . '<div ' . FusionBuilder::attributes( 'nav' ) . '>';

				$is_first_tab = true;

				if ( empty( $this->tabs ) ) {
					$this->parse_tab_parameter( $content, 'fusion_old_tab', $args );
				}

				if ( strpos( $content, 'fusion_tab' ) ) {
					preg_match_all( '/(\[fusion_tab (.*?)\](.*?)\[\/fusion_tab\])/s', $content, $matches );
				} else {
					preg_match_all( '/(\[fusion_old_tab (.*?)\](.*?)\[\/fusion_old_tab\])/s', $content, $matches );
				}

				$tab_content = $tab_nav = '';
				$tabs_count  = ! empty( $this->tabs ) ? count( $this->tabs ) : 0;
				for ( $i = 0; $i < $tabs_count; $i++ ) {
					$icon = $tab_title = '';
					if ( 'none' !== $this->tabs[ $i ]['icon'] ) {
						$icon = '<i ' . FusionBuilder::attributes( 'tabs-shortcode-icon', [ 'index' => $i ] ) . '></i>';
					}

					if ( 'right' === $this->parent_args['icon_position'] ) {
						$tab_title = $this->tabs[ $i ]['title'] . $icon;
					} else {
						$tab_title = $icon . $this->tabs[ $i ]['title'];
					}

					$title_tag = $this->parent_args['title_tag'];
					$tab_id    = 'fusion-tab-' . strtolower( preg_replace( '/\s+/', '', $this->tabs[ $i ]['title'] ) );

					if ( $is_first_tab ) {
						$tab_nav_item = '<li ' . FusionBuilder::attributes( 'active' ) . ' role="presentation"><a ' . FusionBuilder::attributes(
							'tabs-shortcode-link',
							[
								'index'  => $i,
								'tab_id' => $tab_id,
								'first'  => 'true',
							]
						) . '><' . $title_tag . ' ' . FusionBuilder::attributes( 'fusion-tab-heading' ) . '>' . $tab_title . '</' . $title_tag . '></a></li>';
						$is_first_tab = false;
					} else {
						$tab_nav_item = '<li role="presentation"><a ' . FusionBuilder::attributes(
							'tabs-shortcode-link',
							[
								'index'  => $i,
								'tab_id' => $tab_id,
								'first'  => 'false',
							]
						) . '><' . $title_tag . ' ' . FusionBuilder::attributes( 'fusion-tab-heading' ) . '>' . $tab_title . '</' . $title_tag . '></a></li>';
					}

					$tab_nav .= $tab_nav_item;

					// Change ID for mobile to ensure no duplicate ID.
					$tab_nav_item = str_replace( 'id="fusion-tab-', 'id="mobile-fusion-tab-', $tab_nav_item );
					if ( 'accordion' === $mobile_mode || 'toggle' === $mobile_mode ) {
						$tab_content .= '<div ' . FusionBuilder::attributes( 'nav fusion-mobile-tab-nav' ) . '><ul ' . FusionBuilder::attributes( 'tabs-shortcode-navtabs' ) . '>' . $tab_nav_item . '</ul></div>';
					}
					$tab_content .= ( isset( $matches[1][ $i ] ) ) ? do_shortcode( $matches[1][ $i ] ) : '';
				}

				$html .= '<ul ' . FusionBuilder::attributes( 'tabs-shortcode-navtabs' ) . '>' . $tab_nav . '</ul></div><div ' . FusionBuilder::attributes( 'tab-content' ) . '>' . $tab_content . '</div></div>';

				$this->tabs_counter++;
				$this->tab_counter = 1;
				$this->active      = false;
				unset( $this->tabs );

				$this->on_render();

				return apply_filters( 'fusion_element_tabs_parent_content', $html, $args );

			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function attr() {

				$attr = fusion_builder_visibility_atts(
					$this->parent_args['hide_on_mobile'],
					[
						'class' => 'fusion-tabs fusion-tabs-' . $this->tabs_counter . ' ' . $this->parent_args['design'],
						'style' => '',
					]
				);

				if ( 'yes' !== $this->parent_args['justified'] && 'vertical' !== $this->parent_args['layout'] ) {
					$attr['class'] .= ' nav-not-justified';
				}

				if ( 'yes' === $this->parent_args['sticky_tabs'] && 'horizontal' === $this->parent_args['layout'] ) {
					$attr['class'] .= ' sticky-tabs';
					if ( '' !== $this->parent_args['sticky_tabs_offset'] && 0 !== $this->parent_args['sticky_tabs_offset'] ) {
						// If its not a selector then get value and set to css variable.
						if ( false === strpos( $this->parent_args['sticky_tabs_offset'], '.' ) && false === strpos( $this->parent_args['sticky_tabs_offset'], '#' ) ) {
							$attr['style'] .= '--awb-sticky-tabs-offset:' . fusion_library()->sanitize->get_value_with_unit( $this->parent_args['sticky_tabs_offset'] ) . ';';
						} else {
							$attr['data-sticky-offset'] = (string) $this->parent_args['sticky_tabs_offset'];
						}
					}
				}

				if ( $this->parent_args['class'] ) {
					$attr['class'] .= ' ' . $this->parent_args['class'];
				}

				$attr['class'] .= ( 'vertical' === $this->parent_args['layout'] ) ? ' vertical-tabs' : ' horizontal-tabs';

				$attr['class'] .= ( '' !== $this->parent_args['icon_position'] ) ? ' icon-position-' . $this->parent_args['icon_position'] : '';

				if ( $this->parent_args['id'] ) {
					$attr['id'] = $this->parent_args['id'];
				}

				if ( '' !== $this->parent_args['mobile_mode'] ) {
					$attr['class'] .= ' mobile-mode-' . $this->parent_args['mobile_mode'];
				}

				if ( 'carousel' === $this->parent_args['mobile_mode'] && 'yes' === $this->parent_args['mobile_sticky_tabs'] ) {
					$attr['class'] .= ' mobile-sticky-tabs';
				}

				if ( ! empty( $this->parent_args['icon_color'] ) ) {
					$attr['style'] .= '--icon-color:' . $this->parent_args['icon_color'] . ';';
				}

				if ( ! empty( $this->parent_args['icon_active_color'] ) ) {
					$attr['style'] .= '--icon-active-color:' . $this->parent_args['icon_active_color'] . ';';
				}

				return $attr;

			}

			/**
			 * Builds the attributes array.
			 *
			 * @param array $atts The attributes array.
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function navtabs_attr( $atts ) {
				$justified_class = '';
				if ( 'yes' === $this->parent_args['justified'] && 'vertical' !== $this->parent_args['layout'] ) {
					$justified_class = ' nav-justified';
				}

				$attr['class'] = 'nav-tabs' . $justified_class;

				$attr['role'] = 'tablist';

				return $attr;
			}

			/**
			 * Builds the link attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @param array $atts Default attributes.
			 * @return array
			 */
			public function link_attr( $atts ) {
				$index = $atts['index'];
				$attr  = [
					'class'         => 'tab-link',
					'data-toggle'   => 'tab',
					'role'          => 'tab',
					'aria-controls' => $this->tabs[ $index ]['unique_id'],
					'aria-selected' => $atts['first'],
				];

				if ( 'false' === $atts['first'] ) {
					$attr['tabindex'] = '-1';
				}

				$attr['id']   = $atts['tab_id'];
				$attr['href'] = '#' . $this->tabs[ $index ]['unique_id'];

				return $attr;
			}

			/**
			 * Builds the icon attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @param array $atts Default attributes.
			 * @return array
			 */
			public function icon_attr( $atts ) {
				$index = $atts['index'];
				$attr  = [
					'class'       => 'fontawesome-icon ' . fusion_font_awesome_name_handler( $this->tabs[ $index ]['icon'] ),
					'aria-hidden' => 'true',
					'style'       => '',
				];

				if ( '' !== $this->parent_args['icon_size'] ) {
					$attr['style'] .= 'font-size:' . $this->parent_args['icon_size'] . 'px;';
				}
				$icon_color = ! empty( $this->tabs[ $index ]['icon_color'] ) ? $this->tabs[ $index ]['icon_color'] : '';

				if ( $icon_color ) {
					$attr['style'] .= ' --icon-color:' . $icon_color . ';';
				}

				$icon_active_color = ! empty( $this->tabs[ $index ]['icon_active_color'] ) ? $this->tabs[ $index ]['icon_active_color'] : '';
				if ( $icon_active_color ) {
					$attr['style'] .= '--icon-active-color:' . $icon_active_color . ';';
				}

				return $attr;
			}

			/**
			 * Render the child shortcode.
			 *
			 * @access public
			 * @since 1.0
			 * @param  array  $args   Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string         HTML output.
			 */
			public function render_child( $args, $content = '' ) {

				$defaults = FusionBuilder::set_shortcode_defaults(
					[
						'icon'        => 'none',
						'id'          => '',
						'fusion_tab'  => 'no',
						'tab_counter' => 1,
					],
					$args
				);

				extract( $defaults );

				$this->child_args = $defaults;

				$html = '<div ' . FusionBuilder::attributes( 'tabs-shortcode-tab' ) . '>' . do_shortcode( $content ) . '</div>';

				return apply_filters( 'fusion_element_tabs_child_content', $html, $args );

			}

			/**
			 * Builds the tab attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function tab_attr() {
				$index = $this->child_args['tab_counter'] - 1;

				$attr = [
					'class'           => 'tab-pane fade fusion-clearfix',
					'role'            => 'tabpanel',
					'tabindex'        => '0',
					'aria-labelledby' => 'fusion-tab-' . ( isset( $this->tabs[ $index ] ) ? strtolower( preg_replace( '/\s+/', '', $this->tabs[ $index ]['title'] ) ) : '' ), // isset check needed for Yoast's page analysis tool.
				];

				if ( ! isset( $this->active ) ) {
					$this->active = false;
				}

				if ( ! $this->active ) {
					$attr['class'] = 'tab-pane fade fusion-clearfix in active';
					$this->active  = true;
				}

				if ( 'yes' === $this->child_args['fusion_tab'] ) {
					$attr['id'] = $this->child_args['id'];
				} else {
					$index      = $this->child_args['id'] - 1;
					$attr['id'] = $this->tabs[ $index ]['unique_id'];
				}

				return $attr;

			}

			/**
			 * Returns the fusion-tabs.
			 *
			 * @access public
			 * @since 1.0
			 * @param array       $atts    The attributes.
			 * @param null|string $content The content.
			 * @return string
			 */
			public function fusion_tabs( $atts, $content = null ) {
				$fusion_settings = awb_get_fusion_settings();

				$defaults = FusionBuilder::set_shortcode_defaults(
					[
						'class'                          => '',
						'id'                             => '',
						'backgroundcolor'                => $fusion_settings->get( 'tabs_bg_color' ),
						'bordercolor'                    => $fusion_settings->get( 'tabs_border_color' ),
						'active_border_color'            => $fusion_settings->get( 'primary_color' ),
						'icon'                           => '',
						'icon_position'                  => $fusion_settings->get( 'tabs_icon_position' ),
						'icon_size'                      => $fusion_settings->get( 'tabs_icon_size' ),
						'icon_color'                     => $fusion_settings->get( 'tabs_icon_color' ),
						'icon_active_color'              => $fusion_settings->get( 'tabs_icon_active_color' ),
						'design'                         => 'classic',
						'inactivecolor'                  => $fusion_settings->get( 'tabs_inactive_color' ),
						'justified'                      => 'yes',
						'alignment'                      => '',
						'sticky_tabs'                    => 'no',
						'sticky_tabs_offset'             => '',
						'layout'                         => 'horizontal',
						'hide_on_mobile'                 => fusion_builder_default_visibility( 'string' ),
						'fusion_font_family_title_font'  => '',
						'fusion_font_variant_title_font' => '',
						'title_font_size'                => '',
						'title_line_height'              => '',
						'title_letter_spacing'           => '',
						'title_text_transform'           => '',
						'title_text_color'               => '',
						'title_active_text_color'        => '',
						'title_tag'                      => '',
						'title_padding_top'              => '',
						'title_padding_right'            => '',
						'title_padding_bottom'           => '',
						'title_padding_left'             => '',
						'content_padding_top'            => '',
						'content_padding_right'          => '',
						'content_padding_bottom'         => '',
						'content_padding_left'           => '',
						'mobile_mode'                    => '',
						'mobile_sticky_tabs'             => '',
						// margin.
						'margin_top'                     => '',
						'margin_right'                   => '',
						'margin_bottom'                  => '',
						'margin_left'                    => '',
						'margin_top_medium'              => '',
						'margin_right_medium'            => '',
						'margin_bottom_medium'           => '',
						'margin_left_medium'             => '',
						'margin_top_small'               => '',
						'margin_right_small'             => '',
						'margin_bottom_small'            => '',
						'margin_left_small'              => '',
					],
					$atts,
					'fusion_tabs'
				);

				extract( $defaults );

				$this->fusion_tabs_args = $defaults;

				$atts = $defaults;

				$content = preg_replace( '/tab\][^\[]*/', 'tab]', $content );
				$content = preg_replace( '/^[^\[]*\[/', '[', $content );

				$this->parse_tab_parameter( $content, 'fusion_tab' );

				$shortcode_wrapper  = '[fusion_old_tabs design="' . $atts['design'] . '" layout="' . $atts['layout'] . '" justified="' . $atts['justified'] . '" backgroundcolor="' . $atts['backgroundcolor'] . '" inactivecolor="' . $atts['inactivecolor'] . '" bordercolor="' . $atts['bordercolor'] . '"  active_border_color="' . $atts['active_border_color'] . '" icon_position="' . $atts['icon_position'] . '" icon_size="' . $atts['icon_size'] . '" icon_color="' . $atts['icon_color'] . '" icon_active_color="' . $atts['icon_active_color'] . '" hide_on_mobile="' . $atts['hide_on_mobile'] . '" class="' . $atts['class'] . '" id="' . $atts['id'] . '" alignment="' . $alignment . '" sticky_tabs="' . $sticky_tabs . '" sticky_tabs_offset="' . $sticky_tabs_offset . '" fusion_font_family_title_font="' . $fusion_font_family_title_font . '" fusion_font_variant_title_font="' . $fusion_font_variant_title_font . '" title_font_size="' . $title_font_size . '" title_line_height="' . $title_line_height . '" title_letter_spacing="' . $title_letter_spacing . '" title_text_transform="' . $title_text_transform . '" title_text_color="' . $title_text_color . '" title_active_text_color="' . $title_active_text_color . '" title_tag="' . $title_tag . '" title_padding_top="' . $title_padding_top . '" title_padding_right="' . $title_padding_right . '" title_padding_bottom="' . $title_padding_bottom . '" title_padding_left="' . $title_padding_left . '" content_padding_top="' . $content_padding_top . '" content_padding_right="' . $content_padding_right . '" content_padding_bottom="' . $content_padding_bottom . '" content_padding_left="' . $content_padding_left . '" mobile_mode="' . $mobile_mode . '" mobile_sticky_tabs="' . $mobile_sticky_tabs . '" margin_top="' . $margin_top . '" margin_right="' . $margin_right . '" margin_bottom="' . $margin_bottom . '" margin_left="' . $margin_left . '" margin_top_medium="' . $margin_top_medium . '" margin_right_medium="' . $margin_right_medium . '" margin_bottom_medium="' . $margin_bottom_medium . '" margin_left_medium="' . $margin_left_medium . '" margin_top_small="' . $margin_top_small . '" margin_right_small="' . $margin_right_small . '" margin_bottom_small="' . $margin_bottom_small . '" margin_left_small="' . $margin_left_small . '" ]';
				$shortcode_wrapper .= $content;
				$shortcode_wrapper .= '[/fusion_old_tabs]';

				fusion_element_rendering_elements( true );
				$html = do_shortcode( $shortcode_wrapper );
				fusion_element_rendering_elements( false );

				return $html;
			}

			/**
			 * Returns the fusion-tab.
			 *
			 * @access public
			 * @since 1.0
			 * @param array       $atts    The attributes.
			 * @param null|string $content The content.
			 * @return string
			 */
			public function fusion_tab( $atts, $content = null ) {
				$defaults = FusionBuilder::set_shortcode_defaults(
					[
						'id'                => '',
						'icon'              => $this->fusion_tabs_args['icon'],
						'icon_color'        => $this->fusion_tabs_args['icon_color'],
						'icon_active_color' => $this->fusion_tabs_args['icon_active_color'],
						'title'             => '',
					],
					$atts,
					'fusion_tab'
				);

				$content = apply_filters( 'fusion_shortcode_content', $content, 'fusion_tab', $atts );

				extract( $defaults );
				$this->fusion_tab_args = $defaults;

				$atts = $defaults;

				// Create unique tab id for linking.
				$sanitized_title = hash( 'md5', $title, false );
				$sanitized_title = 'tab' . str_replace( '-', '_', $sanitized_title );
				$unique_id       = 'tab-' . substr( md5( get_the_ID() . '-' . $this->tabs_counter . '-' . $this->tab_counter . '-' . $sanitized_title ), 13 );

				$shortcode_wrapper = '[fusion_old_tab id="' . $unique_id . '" icon="' . $icon . '" icon_color="' . $icon_color . '" icon_active_color="' . $icon_active_color . '" fusion_tab="yes" tab_counter="' . $this->tab_counter . '"]' . do_shortcode( $content ) . '[/fusion_old_tab]';

				$this->tab_counter++;

				return do_shortcode( $shortcode_wrapper );
			}

			/**
			 * Parses the tab parameters.
			 *
			 * @access public
			 * @since 1.0
			 * @param string $content The content.
			 * @param string $shortcode The shortcode.
			 * @param array  $args      The arguments.
			 */
			public function parse_tab_parameter( $content, $shortcode, $args = null ) {
				$preg_match_tabs_single = preg_match_all( FusionBuilder::get_shortcode_regex( $shortcode ), $content, $tabs_single );

				if ( is_array( $tabs_single[0] ) ) {
					foreach ( $tabs_single[0] as $key => $tab ) {

						if ( is_array( $args ) ) {
							$preg_match_titles = preg_match_all( '/' . $shortcode . ' id=([0-9]+)/i', $tab, $ids );

							if ( array_key_exists( '0', $ids[1] ) ) {
								$id = $ids[1][0];
							} else {
								$title = 'default';
							}

							foreach ( $args as $key => $value ) {
								if ( 'tab' . $id === $key ) {
									$title = $value;
								}
							}
						} else {
							$preg_match_titles = preg_match_all( '/\[\[?' . $shortcode . ' .*?title="([^\"]+)"/i', $tab, $titles );
							$title             = ( array_key_exists( '0', $titles[1] ) ) ? $titles[1][0] : 'default';
						}
						$preg_match_icons = preg_match_all( '/\[\[?' . $shortcode . '( id=[0-9]+| title="[^\"]+")? icon="([^\"]+)"/i', $tab, $icons );
						$icon             = ( array_key_exists( '0', $icons[2] ) ) ? $icons[2][0] : 'none';

						if ( 'none' === $icon && ! empty( $this->fusion_tabs_args['icon'] ) ) {
							$icon = $this->fusion_tabs_args['icon'];
						}

						$preg_match_icon_colors = preg_match_all( '/\[\[?' . $shortcode . ' .*?icon_color="([^\"]+)"/i', $tab, $icon_colors );
						$icon_color             = ( array_key_exists( '0', $icon_colors[1] ) ) ? $icon_colors[1][0] : '';

						$preg_match_icon_colors = preg_match_all( '/\[\[?' . $shortcode . ' .*?icon_active_color="([^\"]+)"/i', $tab, $icon_active_colors );
						$icon_active_color      = ( array_key_exists( '0', $icon_active_colors[1] ) ) ? $icon_active_colors[1][0] : '';

						// Create unique tab id for linking.
						$sanitized_title = hash( 'md5', $title, false );
						$sanitized_title = 'tab' . str_replace( '-', '_', $sanitized_title );
						$unique_id       = 'tab-' . substr( md5( get_the_ID() . '-' . $this->tabs_counter . '-' . $this->tab_counter . '-' . $sanitized_title ), 13 );

						// Create array for every single tab shortcode.
						$this->tabs[] = [
							'title'             => $title,
							'icon'              => $icon,
							'icon_color'        => $icon_color,
							'icon_active_color' => $icon_active_color,
							'unique_id'         => $unique_id,
						];

						$this->tab_counter++;
					}

					$this->tab_counter = 1;
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
				global $wp_version, $content_media_query, $six_fourty_media_query, $three_twenty_six_fourty_media_query, $ipad_portrait_media_query, $content_min_media_query, $fusion_settings, $dynamic_css_helpers;

				$css['global']['.fusion-tabs.icon-position-right .nav-tabs li .tab-link .fontawesome-icon']['margin-right'] = '0';
				$css['global']['.fusion-tabs.icon-position-right .nav-tabs li .tab-link .fontawesome-icon']['margin-left']  = '10px';
				$css['global']['.fusion-tabs.icon-position-top .nav-tabs li .tab-link .fontawesome-icon']['display']        = 'block';
				$css['global']['.fusion-tabs.icon-position-top .nav-tabs li .tab-link .fontawesome-icon']['margin']         = '0 auto';
				$css['global']['.fusion-tabs.icon-position-top .nav-tabs li .tab-link .fontawesome-icon']['margin-bottom']  = '10px';
				$css['global']['.fusion-tabs.icon-position-top .nav-tabs li .tab-link .fontawesome-icon']['text-align']     = 'center';

				$css[ $content_min_media_query ]['.fusion-tabs .nav']['display']                                     = 'block';
				$css[ $content_min_media_query ]['.fusion-tabs .fusion-mobile-tab-nav']['display']                   = 'none';
				$css[ $content_min_media_query ]['.fusion-tabs.clean .tab-pane']['margin']                           = 0;
				$css[ $content_min_media_query ]['.fusion-tabs .nav-tabs']['display']                                = 'inline-block';
				$css[ $content_min_media_query ]['.fusion-tabs .nav-tabs']['vertical-align']                         = 'middle';
				$css[ $content_min_media_query ]['.fusion-tabs .nav-tabs.nav-justified > li']['display']             = 'table-cell';
				$css[ $content_min_media_query ]['.fusion-tabs .nav-tabs.nav-justified > li']['width']               = '1%';
				$css[ $content_min_media_query ]['.fusion-tabs .nav-tabs li .tab-link']['margin-right']              = '1px';
				$css[ $content_min_media_query ]['.fusion-tabs .nav-tabs li:last-child .tab-link']['margin-right']   = '0';
				$css[ $content_min_media_query ]['.fusion-tabs.horizontal-tabs .nav-tabs']['margin']                 = '0 0 -1px';
				$css[ $content_min_media_query ]['.fusion-tabs.horizontal-tabs .nav']['border-bottom']               = '1px solid ' . fusion_library()->sanitize->color( $fusion_settings->get( 'tabs_border_color' ) );
				$css[ $content_min_media_query ]['.fusion-tabs.horizontal-tabs.clean .nav']['border']                = 'none';
				$css[ $content_min_media_query ]['.fusion-tabs.horizontal-tabs.clean .nav']['text-align']            = 'center';
				$css[ $content_min_media_query ]['.fusion-tabs.horizontal-tabs.clean .nav-tabs']['border']           = 'none';
				$css[ $content_min_media_query ]['.fusion-tabs.horizontal-tabs.clean .nav-tabs li']['margin-bottom'] = '0';
				$css[ $content_min_media_query ]['.fusion-tabs.horizontal-tabs.clean .nav-tabs li .tab-link']['margin-right'] = '-1px';
				$css[ $content_min_media_query ]['.fusion-tabs.horizontal-tabs.clean .tab-content']['margin-top']             = '40px';
				$css[ $content_min_media_query ]['.fusion-tabs.nav-not-justified']['border']                                  = 'none';
				$css[ $content_min_media_query ]['.fusion-tabs.nav-not-justified .nav-tabs li']['display']                    = 'inline-block';
				$css[ $content_min_media_query ]['.fusion-tabs.nav-not-justified.clean .nav-tabs li .tab-link']['padding']    = '14px 55px';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs']['display']                                     = '-webkit-flex';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs']['display']                                     = 'flex';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs']['border']                                      = 'none';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs']['clear']                                       = 'both';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs']['zoom']                                        = '1';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs:before, .fusion-tabs.vertical-tabs:after']['content'] = '" "';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs:before, .fusion-tabs.vertical-tabs:after']['display'] = 'table';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs:after']['clear']                                      = 'both';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs .nav-tabs']['display']                                = 'block';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs .nav-tabs']['position']                               = 'relative';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs .nav-tabs']['left']                                   = '1px';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs .nav-tabs']['border']                                 = '1px solid ' . fusion_library()->sanitize->color( $fusion_settings->get( 'tabs_border_color' ) );
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs .nav-tabs']['border-right']                           = 'none';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs .nav-tabs > li .tab-link']['margin-right']            = '0';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs .nav-tabs > li .tab-link']['margin-bottom']           = '1px';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs .nav-tabs > li .tab-link']['padding']                 = '10px 35px';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs .nav-tabs > li .tab-link']['white-space']             = 'nowrap';

				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs .nav-tabs > li .tab-link']['border-top']               = 'none';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs .nav-tabs > li .tab-link']['text-align']               = 'left';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs .nav-tabs > li:last-child .tab-link']['margin-bottom'] = '0';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs .nav-tabs > li.active > .tab-link']['border-bottom']   = 'none';

				if ( is_rtl() ) {
					$css[ $content_min_media_query ]['.rtl .fusion-tabs.vertical-tabs .nav-tabs > li .tab-link']['border-right']          = '3px transparent solid';
					$css[ $content_min_media_query ]['.rtl .fusion-tabs.vertical-tabs .nav-tabs > li.active > .tab-link']['border-right'] = '3px solid var(--primary_color)';
				} else {
					$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs .nav-tabs > li .tab-link']['border-left']          = '3px transparent solid';
					$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs .nav-tabs > li.active > .tab-link']['border-left'] = '3px solid var(--primary_color)';
				}

				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs .nav-tabs > li.active > .tab-link']['border-top']     = 'none';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs .nav-tabs > li.active > .tab-link']['cursor']         = 'pointer';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs .nav']['width']                                       = 'auto';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs .tab-content']['width']                               = '84.5%';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs .tab-pane']['padding']                                = '30px';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs .tab-pane']['border']                                 = '1px solid ' . fusion_library()->sanitize->color( $fusion_settings->get( 'tabs_border_color' ) );
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs.clean .nav-tabs']['background-color']                 = 'transparent';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs.clean .nav-tabs']['border']                           = 'none';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs.clean .nav-tabs li .tab-link']['margin']              = '0';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs.clean .nav-tabs li .tab-link']['padding']             = '10px 35px';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs.clean .nav-tabs li .tab-link']['white-space']         = 'nowrap';
				$css[ $content_min_media_query ]['.fusion-body .fusion-tabs.vertical-tabs.clean .nav-tabs li .tab-link']['border'] = '1px solid';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs.clean .nav']['width']                                 = 'auto';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs.clean .tab-content']['margin']                        = '0';
				$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs.clean .tab-content']['width']                         = '75%';

				if ( is_rtl() ) {
					$css[ $content_min_media_query ]['.rtl .fusion-tabs.vertical-tabs.clean .tab-content']['padding-right']  = '40px';
					$css[ $content_min_media_query ]['.rtl .fusion-tabs.vertical-tabs .nav-tabs li .tab-link']['text-align'] = 'right';
				} else {
					$css[ $content_min_media_query ]['.fusion-tabs.vertical-tabs.clean .tab-content']['padding-left'] = '40px';
				}

				return $css;
			}

			/**
			 * Builds margin styles.
			 *
			 * @access public
			 * @since 3.8
			 * @return string
			 */
			public function build_margin_styles() {
				// Responsive Margin.
				$fusion_settings = awb_get_fusion_settings();
				$styles          = '';

				foreach ( [ 'large', 'medium', 'small' ] as $size ) {
					$margin_styles = '';
					foreach ( [ 'top', 'right', 'bottom', 'left' ] as $direction ) {

						$margin_key = 'large' === $size ? 'margin_' . $direction : 'margin_' . $direction . '_' . $size;
						if ( '' !== $this->parent_args[ $margin_key ] ) {
							$margin_styles .= 'margin-' . $direction . ' : ' . fusion_library()->sanitize->get_value_with_unit( $this->parent_args[ $margin_key ] ) . ';';
						}
					}

					if ( '' === $margin_styles ) {
						continue;
					}

					$margin_styles = '.fusion-tabs.fusion-tabs-' . $this->tabs_counter . '{ ' . $margin_styles . '}';

					// Large styles, no wrapping needed.
					if ( 'large' === $size ) {
						$styles .= $margin_styles;
					} else {
						// Medium and Small size screen styles.
						$styles .= '@media only screen and (max-width:' . $fusion_settings->get( 'visibility_' . $size ) . 'px) {' . $margin_styles . '}';
					}
				}

				return $styles;
			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.1
			 * @return array $sections Tabs settings.
			 */
			public function add_options() {

				return [
					'tabs_shortcode_section' => [
						'label'       => esc_html__( 'Tabs', 'fusion-builder' ),
						'description' => '',
						'id'          => 'tabs_shortcode_section',
						'icon'        => 'fusiona-folder',
						'type'        => 'accordion',
						'fields'      => [
							'tabs_info'               => [
								'id'          => 'social_links_info',
								'type'        => 'custom',
								'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> These tab global options control both the tab element and Avada tab widget, however the widget does not utilize icons.', 'fusion-builder' ) . '</div>',
							],
							'tabs_bg_color'           => [
								'label'       => esc_html__( 'Tabs Background Color + Hover Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the active tab, tab hover and content background.', 'fusion-builder' ),
								'id'          => 'tabs_bg_color',
								'default'     => 'var(--awb-color1)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
								'css_vars'    => [
									[
										'name'     => '--tabs_bg_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'tabs_inactive_color'     => [
								'label'       => esc_html__( 'Tabs Inactive Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the inactive tabs as well as the post date box layout for the Avada Tab Widget.', 'fusion-builder' ),
								'id'          => 'tabs_inactive_color',
								'default'     => 'var(--awb-color2)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
								'css_vars'    => [
									[
										'name'     => '--tabs_inactive_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'tabs_border_color'       => [
								'label'       => esc_html__( 'Tabs Border Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the tab border.', 'fusion-builder' ),
								'id'          => 'tabs_border_color',
								'default'     => 'var(--awb-color3)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
								'css_vars'    => [
									[
										'name'     => '--tabs_border_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'tabs_title_padding'      => [
								'label'       => esc_html__( 'Tabs Title Padding', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the padding of tabs title.', 'fusion-builder' ),
								'id'          => 'tabs_title_padding',
								'type'        => 'spacing',
								'transport'   => 'postMessage',
								'choices'     => [
									'top'    => true,
									'left'   => true,
									'bottom' => true,
									'right'  => true,
								],
								'default'     => [
									'top'    => '',
									'left'   => '',
									'bottom' => '',
									'right'  => '',
								],
							],
							'tabs_content_padding'    => [
								'label'       => esc_html__( 'Tabs Content Padding', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the padding of tabs content.', 'fusion-builder' ),
								'id'          => 'tabs_content_padding',
								'type'        => 'spacing',
								'transport'   => 'postMessage',
								'choices'     => [
									'top'    => true,
									'left'   => true,
									'bottom' => true,
									'right'  => true,
								],
								'default'     => [
									'top'    => '',
									'left'   => '',
									'bottom' => '',
									'right'  => '',
								],
							],
							'tabs_icon_position'      => [
								'label'       => esc_html__( 'Icon Position', 'fusion-builder' ),
								'description' => esc_html__( 'Choose the position of the icon on the tab.', 'fusion-builder' ),
								'id'          => 'tabs_icon_position',
								'default'     => 'left',
								'type'        => 'radio-buttonset',
								'transport'   => 'postMessage',
								'choices'     => [
									'left'  => esc_attr__( 'Left', 'fusion-builder' ),
									'right' => esc_attr__( 'Right', 'fusion-builder' ),
									'top'   => esc_attr__( 'Top', 'fusion-builder' ),
								],
							],
							'tabs_icon_size'          => [
								'label'       => esc_html__( 'Tabs Icon Size', 'fusion-builder' ),
								'description' => esc_html__( 'Set the size of the icon.', 'fusion-builder' ),
								'id'          => 'tabs_icon_size',
								'default'     => '16',
								'transport'   => 'postMessage',
								'choices'     => [
									'min'  => '1',
									'max'  => '150',
									'step' => '1',
								],
								'type'        => 'slider',
							],
							'tabs_icon_color'         => [
								'label'       => esc_html__( 'Tabs Icon Color', 'fusion-builder' ),
								'description' => esc_html__( 'Set the color of the icon.', 'fusion-builder' ),
								'id'          => 'tabs_icon_color',
								'transport'   => 'postMessage',
								'type'        => 'color-alpha',
							],
							'tabs_icon_active_color'  => [
								'label'       => esc_html__( 'Active Tab Icon Color', 'fusion-builder' ),
								'description' => esc_html__( 'Set the color of the active tab icon.', 'fusion-builder' ),
								'id'          => 'tabs_icon_active_color',
								'transport'   => 'postMessage',
								'type'        => 'color-alpha',
							],
							'tabs_title_color'        => [
								'label'       => esc_html__( 'Tabs Title Color', 'fusion-builder' ),
								'description' => esc_html__( 'Set the color of the tabs title.', 'fusion-builder' ),
								'id'          => 'tabs_title_color',
								'transport'   => 'postMessage',
								'type'        => 'color-alpha',
							],
							'tabs_active_title_color' => [
								'label'       => esc_html__( 'Tabs Active Title Color', 'fusion-builder' ),
								'description' => esc_html__( 'Set the color of the tabs active title.', 'fusion-builder' ),
								'id'          => 'tabs_active_title_color',
								'transport'   => 'postMessage',
								'type'        => 'color-alpha',
							],
							'tabs_mobile_mode'        => [
								'label'       => esc_html__( 'Tabs Mobile Mode', 'fusion-builder' ),
								'description' => esc_html__( 'Choose the tabs mode for mobile devices. Carousel will be come active only, if tabs don\'t fit the device screen width.', 'fusion-builder' ),
								'id'          => 'tabs_mobile_mode',
								'default'     => 'accordion',
								'type'        => 'radio-buttonset',
								'transport'   => 'postMessage',
								'choices'     => [
									'accordion' => esc_attr__( 'Accordion', 'fusion-builder' ),
									'toggle'    => esc_attr__( 'Toggle', 'fusion-builder' ),
									'carousel'  => esc_attr__( 'Carousel', 'fusion-builder' ),
								],
							],
							'tabs_mobile_sticky_tabs' => [
								'label'       => esc_html__( 'Mobile Sticky Tabs', 'fusion-builder' ),
								'description' => esc_html__( 'Set tabs to sticky for carousel mode on mobile, useful for long content.', 'fusion-builder' ),
								'id'          => 'tabs_mobile_sticky_tabs',
								'default'     => 'no',
								'type'        => 'radio-buttonset',
								'transport'   => 'postMessage',
								'choices'     => [
									'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
									'no'  => esc_attr__( 'No', 'fusion-builder' ),
								],

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

				Fusion_Dynamic_JS::enqueue_script(
					'fusion-tabs',
					FusionBuilder::$js_folder_url . '/general/fusion-tabs.js',
					FusionBuilder::$js_folder_path . '/general/fusion-tabs.js',
					[ 'modernizr', 'bootstrap-tab' ],
					'1',
					true
				);
				Fusion_Dynamic_JS::localize_script(
					'fusion-tabs',
					'fusionTabVars',
					[
						'content_break_point' => intval( $fusion_settings->get( 'content_break_point' ) ),
					]
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
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/tabs.min.css' );
			}
		}
	}

	new FusionSC_Tabs();

}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_tabs() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Tabs',
			[
				'name'          => esc_attr__( 'Tabs', 'fusion-builder' ),
				'shortcode'     => 'fusion_tabs',
				'multi'         => 'multi_element_parent',
				'element_child' => 'fusion_tab',
				'icon'          => 'fusiona-folder',
				'preview'       => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-tabs-preview.php',
				'preview_id'    => 'fusion-builder-block-module-tabs-preview-template',
				'child_ui'      => true,
				'help_url'      => 'https://theme-fusion.com/documentation/avada/elements/tabs-element/',
				'sortable'      => false,
				'subparam_map'  => [
					'fusion_font_family_title_font'  => 'title_typography',
					'fusion_font_variant_title_font' => 'title_typography',
					'title_font_size'                => 'title_typography',
					'title_line_height'              => 'title_typography',
					'title_letter_spacing'           => 'title_typography',
					'title_text_transform'           => 'title_typography',
				],
				'params'        => [
					[
						'type'        => 'tinymce',
						'heading'     => esc_attr__( 'Content', 'fusion-builder' ),
						'description' => esc_attr__( 'Enter some content for this tabs element.', 'fusion-builder' ),
						'param_name'  => 'element_content',
						'value'       => '[fusion_tab title="' . esc_attr__( 'Your Content Goes Here', 'fusion-builder' ) . '" icon=""]' . esc_attr__( 'Your Content Goes Here', 'fusion-builder' ) . '[/fusion_tab]',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Design', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose a design for the element.', 'fusion-builder' ),
						'param_name'  => 'design',
						'value'       => [
							'classic' => esc_attr__( 'Classic', 'fusion-builder' ),
							'clean'   => esc_attr__( 'Clean', 'fusion-builder' ),
						],
						'default'     => 'classic',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Layout', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the layout of the element.' ),
						'param_name'  => 'layout',
						'value'       => [
							'horizontal' => esc_attr__( 'Horizontal', 'fusion-builder' ),
							'vertical'   => esc_attr__( 'Vertical', 'fusion-builder' ),
						],
						'default'     => 'horizontal',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Justify Tabs', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to get tabs stretched over full element width.', 'fusion-builder' ),
						'param_name'  => 'justified',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'yes',
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'horizontal',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Tabs Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose tabs alignment.', 'fusion-builder' ),
						'param_name'  => 'alignment',
						'default'     => 'start',
						'grid_layout' => true,
						'back_icons'  => true,
						'icons'       => [
							'start'  => '<span class="fusiona-horizontal-flex-start"></span>',
							'center' => '<span class="fusiona-horizontal-flex-center"></span>',
							'end'    => '<span class="fusiona-horizontal-flex-end"></span>',
						],
						'value'       => [
							// We use "start/end" terminology because flex direction changes depending on RTL/LTR.
							'start'  => esc_html__( 'Start', 'fusion-builder' ),
							'center' => esc_html__( 'Center', 'fusion-builder' ),
							'end'    => esc_html__( 'End', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'horizontal',
								'operator' => '==',
							],
							[
								'element'  => 'justified',
								'value'    => 'no',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Sticky Tabs', 'fusion-builder' ),
						'description' => esc_attr__( 'Turn on to have the tabs navigation stick on scroll. Useful for long content.', 'fusion-builder' ),
						'param_name'  => 'sticky_tabs',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'no',
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'horizontal',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Sticky Tabs Offset', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls how far the top of the column is offset from top of viewport when sticky. Use either a unit of measurement, or a CSS selector.', 'fusion-builder' ),
						'param_name'  => 'sticky_tabs_offset',
						'value'       => '',
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'horizontal',
								'operator' => '==',
							],
							[
								'element'  => 'sticky_tabs',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'iconpicker',
						'heading'     => esc_attr__( 'Icon', 'fusion-builder' ),
						'param_name'  => 'icon',
						'value'       => '',
						'description' => esc_attr__( 'Global setting for all tabs, this can be overridden individually. Click an icon to select, click again to deselect.', 'fusion-builder' ),
					],
					[
						'heading'     => esc_html__( 'Icon Position', 'fusion-builder' ),
						'description' => esc_html__( 'Choose the position of the icon on the tab. Icons can be selected in the general tab for the same icon on all tabs, or in each child item for individual tab icons.', 'fusion-builder' ),
						'param_name'  => 'icon_position',
						'default'     => '',
						'type'        => 'radio_button_set',
						'value'       => [
							''      => esc_attr__( 'Default', 'fusion-builder' ),
							'left'  => esc_attr__( 'Left', 'fusion-builder' ),
							'right' => esc_attr__( 'Right', 'fusion-builder' ),
							'top'   => esc_attr__( 'Top', 'fusion-builder' ),
						],
					],
					[
						'heading'     => esc_html__( 'Tabs Icon Size', 'fusion-builder' ),
						'description' => esc_html__( 'Set the size of the icon. In pixels, ex: 13px. Icons can be selected in the general tab for the same icon on all tabs, or in each child item for individual tab icons.', 'fusion-builder' ),
						'param_name'  => 'icon_size',
						'default'     => $fusion_settings->get( 'tabs_icon_size' ),
						'min'         => '1',
						'max'         => '150',
						'step'        => '1',
						'type'        => 'range',
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

					// Design Tab.
					'fusion_margin_placeholder' => [
						'param_name' => 'margin',
						'value'      => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
						],
						'responsive' => [
							'state' => 'large',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Tabs Title Tag', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose HTML tag of the tabs title, either div or the heading tag, h1-h6.', 'fusion-builder' ),
						'param_name'  => 'title_tag',
						'value'       => [
							'h1'  => 'H1',
							'h2'  => 'H2',
							'h3'  => 'H3',
							'h4'  => 'H4',
							'h5'  => 'H5',
							'h6'  => 'H6',
							'div' => 'DIV',
						],
						'default'     => 'h4',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'typography',
						'remove_from_atts' => true,
						'global'           => true,
						'heading'          => esc_attr__( 'Tabs Title Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the tabs title text typography.', 'fusion-builder' ),
						'param_name'       => 'title_typography',
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'choices'          => [
							'font-family'    => 'title_font',
							'font-size'      => 'title_font_size',
							'line-height'    => 'title_line_height',
							'letter-spacing' => 'title_letter_spacing',
							'text-transform' => 'title_text_transform',
						],
						'default'          => [
							'font-family'    => '',
							'variant'        => '',
							'font-size'      => '',
							'line-height'    => '',
							'letter-spacing' => '',
							'text-transform' => '',
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Tabs Title Padding', 'fusion-builder' ),
						'description'      => esc_attr__( 'Controls the padding of tabs title, In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'title_padding',
						'value'            => [
							'title_padding_top'    => '',
							'title_padding_right'  => '',
							'title_padding_bottom' => '',
							'title_padding_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Tabs Content Padding', 'fusion-builder' ),
						'description'      => esc_attr__( 'Controls the padding of tabs content, In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'content_padding',
						'value'            => [
							'content_padding_top'    => '',
							'content_padding_right'  => '',
							'content_padding_bottom' => '',
							'content_padding_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the color of the outer tab border. ', 'fusion-builder' ),
						'param_name'  => 'bordercolor',
						'value'       => '',
						'default'     => $fusion_settings->get( 'tabs_border_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Active Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the color of the active tab top border. ', 'fusion-builder' ),
						'param_name'  => 'active_border_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'primary_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'design',
								'value'    => 'classic',
								'operator' => '==',
							],
						],
					],
					[
						'type'             => 'subgroup',
						'heading'          => esc_html__( 'Colors', 'fusion-builder' ),
						'description'      => esc_html__( 'Customize tabs colors.', 'fusion-builder' ),
						'param_name'       => 'tabs_colors',
						'default'          => 'regular',
						'group'            => esc_html__( 'Design', 'fusion-builder' ),
						'remove_from_atts' => true,
						'value'            => [
							'regular' => esc_html__( 'Regular', 'fusion-builder' ),
							'active'  => esc_html__( 'Active', 'fusion-builder' ),
						],
						'icons'            => [
							'regular' => '<span class="fusiona-regular-state" style="font-size:18px;"></span>',
							'active'  => '<span class="fusiona-hover-state" style="font-size:18px;"></span>',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background inactive tab color. ', 'fusion-builder' ),
						'param_name'  => 'inactivecolor',
						'value'       => '',
						'default'     => $fusion_settings->get( 'tabs_inactive_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'tabs_colors',
							'tab'  => 'regular',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Tabs Title Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the color of the tabs title.', 'fusion-builder' ),
						'param_name'  => 'title_text_color',
						'default'     => $fusion_settings->get( 'tabs_title_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'tabs_colors',
							'tab'  => 'regular',
						],
					],
					[
						'heading'     => esc_html__( 'Icon Color', 'fusion-builder' ),
						'description' => esc_html__( 'Set the color of the icon.', 'fusion-builder' ),
						'param_name'  => 'icon_color',
						'default'     => $fusion_settings->get( 'tabs_icon_color' ),
						'type'        => 'colorpickeralpha',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'tabs_colors',
							'tab'  => 'regular',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background active tab color.', 'fusion-builder' ),
						'param_name'  => 'backgroundcolor',
						'value'       => '',
						'default'     => $fusion_settings->get( 'tabs_bg_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'tabs_colors',
							'tab'  => 'active',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Tabs Title Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the color of the active title.', 'fusion-builder' ),
						'param_name'  => 'title_active_text_color',
						'default'     => $fusion_settings->get( 'tabs_active_title_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'tabs_colors',
							'tab'  => 'active',
						],
					],
					[
						'heading'     => esc_html__( 'Icon Color', 'fusion-builder' ),
						'description' => esc_html__( 'Set the color of the active tab icon.', 'fusion-builder' ),
						'param_name'  => 'icon_active_color',
						'default'     => $fusion_settings->get( 'tabs_icon_active_color' ),
						'type'        => 'colorpickeralpha',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'tabs_colors',
							'tab'  => 'active',
						],
					],

					// Mobile.
					[
						'heading'     => esc_html__( 'Mobile Mode', 'fusion-builder' ),
						'description' => esc_html__( 'Choose the tabs mode for mobile devices. Carousel will be come active only, if tabs don\'t fit the device screen width.', 'fusion-builder' ),
						'param_name'  => 'mobile_mode',
						'default'     => '',
						'type'        => 'radio_button_set',
						'value'       => [
							''          => esc_attr__( 'Default', 'fusion-builder' ),
							'accordion' => esc_attr__( 'Accordion', 'fusion-builder' ),
							'toggle'    => esc_attr__( 'Toggle', 'fusion-builder' ),
							'carousel'  => esc_attr__( 'Carousel', 'fusion-builder' ),
						],
						'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
					],
					[
						'heading'     => esc_html__( 'Sticky Tabs', 'fusion-builder' ),
						'description' => esc_html__( 'Set tabs to sticky for carousel mode on mobile, useful for long content.', 'fusion-builder' ),
						'param_name'  => 'mobile_sticky_tabs',
						'default'     => '',
						'type'        => 'radio_button_set',
						'value'       => [
							''    => esc_attr__( 'Default', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'mobile_mode',
								'value'    => 'carousel',
								'operator' => '==',
							],
						],

					],

				],
			],
			'parent'
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_tabs' );

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_tab() {
	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Tabs',
			[
				'name'              => esc_attr__( 'Tab', 'fusion-builder' ),
				'shortcode'         => 'fusion_tab',
				'hide_from_builder' => true,
				'allow_generator'   => true,
				'params'            => [
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Tab Title', 'fusion-builder' ),
						'description' => esc_attr__( 'Title of the tab.', 'fusion-builder' ),
						'param_name'  => 'title',
						'value'       => esc_attr__( 'Your Content Goes Here', 'fusion-builder' ),
						'placeholder' => true,
					],
					[
						'type'        => 'iconpicker',
						'heading'     => esc_attr__( 'Icon', 'fusion-builder' ),
						'param_name'  => 'icon',
						'value'       => '',
						'description' => esc_attr__( 'Click an icon to select, click again to deselect.', 'fusion-builder' ),
					],
					[
						'heading'     => esc_html__( 'Icon Color', 'fusion-builder' ),
						'description' => esc_html__( 'Set the color of tabs icon.', 'fusion-builder' ),
						'param_name'  => 'icon_color',
						'default'     => '',
						'type'        => 'colorpickeralpha',
					],
					[
						'heading'     => esc_html__( 'Active Icon Color', 'fusion-builder' ),
						'description' => esc_html__( 'Set the color of the active tab icon.', 'fusion-builder' ),
						'param_name'  => 'icon_active_color',
						'default'     => '',
						'type'        => 'colorpickeralpha',
					],
					[
						'type'         => 'tinymce',
						'heading'      => esc_attr__( 'Tab Content', 'fusion-builder' ),
						'description'  => esc_attr__( 'Add content for the tab.', 'fusion-builder' ),
						'param_name'   => 'element_content',
						'value'        => esc_attr__( 'Your Content Goes Here', 'fusion-builder' ),
						'placeholder'  => true,
						'dynamic_data' => true,
					],
				],
				'tag_name'          => 'li',
			],
			'child'
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_tab' );
