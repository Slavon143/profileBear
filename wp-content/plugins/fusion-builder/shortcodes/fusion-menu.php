<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.0
 */

if ( fusion_is_element_enabled( 'fusion_menu' ) ) {

	if ( ! class_exists( 'FusionSC_Menu' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.0
		 */
		class FusionSC_Menu extends Fusion_Element {

			/**
			 * An array of the shortcode defaults.
			 *
			 * @access protected
			 * @since 3.0
			 * @var array
			 */
			protected $defaults;

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 3.0
			 * @var array
			 */
			protected $args;

			/**
			 * Counter for elements.
			 *
			 * @access protected
			 * @since 3.0
			 * @var int
			 */
			protected $count = 0;

			/**
			 * An array of the dynamic CSS.
			 *
			 * @access protected
			 * @since 3.0
			 * @var array
			 */
			protected $dynamic_css;

			/**
			 * The base selector for generated styles.
			 *
			 * @access private
			 * @since 3.0
			 * @var string
			 */
			private $base_selector;

			/**
			 * The base selector for generated styles, without the .fusion-body class prepended.
			 *
			 * @access private
			 * @since 3.0
			 * @var string
			 */
			private $base_selector_no_body;

			/**
			 * Has the inline script already been added?
			 *
			 * @static
			 * @access protected
			 * @since 3.0
			 * @var bool
			 */
			protected static $inline_script_added = false;

			/**
			 * Markup for menu's overlay search.
			 *
			 * @static
			 * @access protected
			 * @since 3.0
			 * @var bool
			 */
			public static $overlay_search_markup = '';

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 3.0
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_menu-shortcode', [ $this, 'attr' ] );

				add_shortcode( 'fusion_menu', [ $this, 'render' ] );

				// Ajax mechanism for query related part.
				add_action( 'wp_ajax_get_fusion_menu', [ $this, 'ajax_query' ] );

				add_action( 'wp_footer', [ $this, 'print_inline_script' ] );
			}

			/**
			 * Gets the query data.
			 *
			 * @static
			 * @access public
			 * @since 3.0
			 * @param array $defaults An array of defaults.
			 * @return void
			 */
			public function ajax_query( $defaults ) {
				check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );

				// From Ajax Request.
				if ( isset( $_POST['model'] ) && isset( $_POST['model']['params'] ) && ! apply_filters( 'fusion_builder_live_request', false ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$defaults   = $_POST['model']['params']; // phpcs:ignore WordPress.Security
					$this->args = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $defaults, 'fusion_menu' );
					// Validate arrows.
					if ( is_array( $this->args['arrows'] ) ) {
						$this->args['arrows'] = implode( ',', $this->args['arrows'] );
					}
					fusion_set_live_data();
					add_filter( 'fusion_builder_live_request', '__return_true' );
				}

				$return_data['menu_markup'] = wp_nav_menu( $this->fetch_menu_args( [ 'method' => 'hover' ] ) );

				// Add search overlay form as direct child of <nav>.
				if ( '' !== self::$overlay_search_markup ) {
					$return_data['menu_markup'] = self::$overlay_search_markup . $return_data['menu_markup'];

					self::$overlay_search_markup = '';
				}

				$return_data['button_markup']        = $this->get_button();
				$return_data['flyout_button_markup'] = $this->get_flyout_button();
				$return_data['styles']               = $this->get_styles();

				echo wp_json_encode( $return_data );

				wp_die();
			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 3.0
			 * @return array
			 */
			public static function get_element_defaults() {
				$fusion_settings = awb_get_fusion_settings();

				return [
					'active_bg'                            => 'rgba(0,0,0,0)',
					'active_border_color'                  => 'rgba(0,0,0,0)',
					'active_border_bottom'                 => '0px',
					'active_border_left'                   => '0px',
					'active_border_right'                  => '0px',
					'active_border_top'                    => '0px',
					'active_color'                         => '',
					'align_items'                          => 'stretch',
					'animation_direction'                  => 'left',
					'animation_offset'                     => $fusion_settings->get( 'animation_offset' ),
					'animation_speed'                      => '',
					'animation_type'                       => '',
					'arrows'                               => [ '' ],
					'arrows_size_height'                   => '12px',
					'arrows_size_width'                    => '23px',
					'bg'                                   => 'rgba(0,0,0,0)',
					'border_color'                         => 'rgba(0,0,0,0)',
					'border_bottom'                        => '0px',
					'border_right'                         => '0px',
					'border_top'                           => '0px',
					'border_left'                          => '0px',
					'border_radius_bottom_left'            => '0px',
					'border_radius_bottom_right'           => '0px',
					'border_radius_top_left'               => '0px',
					'border_radius_top_right'              => '0px',
					'box_shadow'                           => 'no',
					'box_shadow_blur'                      => '',
					'box_shadow_color'                     => '',
					'box_shadow_horizontal'                => '',
					'box_shadow_spread'                    => '',
					'box_shadow_style'                     => '',
					'box_shadow_vertical'                  => '',
					'breakpoint'                           => 'medium',
					'custom_breakpoint'                    => '800',
					'class'                                => '',
					'collapsed_mode'                       => 'dropdown',
					'collapsed_nav_icon_close'             => 'fa-bars fas',
					'collapsed_nav_icon_open'              => 'fa-times fas',
					'collapsed_nav_text'                   => '',
					'color'                                => '#212934',
					'direction'                            => 'row',
					'dropdown_carets'                      => 'yes',
					'expand_direction'                     => 'right',
					'expand_method'                        => 'hover',
					'submenu_mode'                         => 'dropdown',
					'submenu_flyout_direction'             => 'fade',
					'expand_transition'                    => 'fade',
					'font_size'                            => '16px',
					'fusion_font_family_mobile_typography' => 'inherit',
					'fusion_font_family_submenu_typography' => 'inherit',
					'fusion_font_family_typography'        => 'inherit',
					'fusion_font_variant_mobile_typography' => '400',
					'fusion_font_variant_submenu_typography' => '400',
					'fusion_font_variant_typography'       => '400',
					'gap'                                  => '0px',
					'hide_on_mobile'                       => fusion_builder_default_visibility( 'string' ),
					'icons_color'                          => '#212934',
					'icons_hover_color'                    => '#65bc7b',
					'icons_position'                       => 'left',
					'icons_size'                           => '16',
					'id'                                   => '',
					'items_padding_bottom'                 => '0px',
					'items_padding_left'                   => '0px',
					'items_padding_right'                  => '0px',
					'items_padding_top'                    => '0px',
					'justify_content'                      => 'flex-start',
					'justify_title'                        => 'center',
					'margin_bottom'                        => '0px',
					'margin_top'                           => '0px',
					'menu'                                 => false,
					'min_height'                           => '4em',
					'mobile_active_bg'                     => '#f9f9fb',
					'mobile_active_color'                  => '#4a4e57',
					'mobile_sep_color'                     => 'rgba(0,0,0,0.1)',
					'mobile_bg'                            => '#ffffff',
					'mobile_color'                         => '#4a4e57',
					'mobile_font_size'                     => '1em',
					'mobile_text_transform'                => '',
					'mobile_line_height'                   => '',
					'mobile_letter_spacing'                => '',
					'mobile_trigger_font_size'             => '1em',
					'mobile_indent_submenu'                => 'on',
					'mobile_nav_button_align_hor'          => 'flex-start',
					'mobile_nav_items_height'              => '65',
					'mobile_nav_mode'                      => 'collapse-to-button',
					'mobile_nav_size'                      => 'full-absolute',
					'mobile_nav_trigger_fullwidth'         => 'off',
					'mobile_nav_trigger_bottom_margin'     => '0px',
					'mobile_trigger_background_color'      => '#ffffff',
					'mobile_trigger_color'                 => '#4a4e57',
					'sticky_display'                       => '',
					'sticky_min_height'                    => '',
					'submenu_max_width'                    => '',
					'submenu_active_bg'                    => '#f9f9fb',
					'submenu_active_color'                 => '#212934',
					'flyout_close_color'                   => '#212934',
					'flyout_active_close_color'            => '#212934',
					'submenu_bg'                           => '#fff',
					'submenu_border_radius_bottom_left'    => '0px',
					'submenu_border_radius_bottom_right'   => '0px',
					'submenu_border_radius_top_left'       => '0px',
					'submenu_border_radius_top_right'      => '0px',
					'submenu_color'                        => '#212934',
					'submenu_font_size'                    => '14px',
					'submenu_items_padding_bottom'         => '12px',
					'submenu_items_padding_left'           => '20px',
					'submenu_items_padding_right'          => '20px',
					'submenu_items_padding_top'            => '12px',
					'submenu_sep_color'                    => '#e2e2e2',
					'submenu_space'                        => '0px',
					'submenu_text_transform'               => '',
					'submenu_line_height'                  => '',
					'submenu_letter_spacing'               => '',
					'text_transform'                       => '',
					'line_height'                          => '',
					'thumbnail_size_height'                => '14px',
					'thumbnail_size_width'                 => '26px',
					'transition_time'                      => '300',
					'transition_type'                      => 'fade',
					'trigger_padding_bottom'               => '12px',
					'trigger_padding_left'                 => '20px',
					'trigger_padding_right'                => '20px',
					'trigger_padding_top'                  => '12px',
					'mobile_justify_content'               => 'left',
					'main_justify_content'                 => 'left',
					'letter_spacing'                       => '',
					'mobile_sticky_max_height'             => '',
				];
			}

			/**
			 * Maps settings to param variables.
			 *
			 * @static
			 * @access public
			 * @since 3.0
			 * @return array
			 */
			public static function settings_to_params() {
				return [];
			}

			/**
			 * Adds units to attributes that require it.
			 *
			 * @access protected
			 * @since 3.0
			 * @return void
			 */
			protected function add_units_to_args() {
				$requires_units = [
					'margin_top',
					'margin_bottom',
					'items_padding_top',
					'items_padding_bottom',
					'items_padding_left',
					'items_padding_right',
					'gap',
					'font_size',
					'min_height',
					'border_top',
					'border_bottom',
					'border_left',
					'border_right',
					'active_border_top',
					'active_border_bottom',
					'active_border_left',
					'active_border_right',
					'border_radius_top_left',
					'border_radius_top_right',
					'border_radius_bottom_right',
					'border_radius_bottom_left',
					'submenu_border_radius_top_left',
					'submenu_border_radius_top_right',
					'submenu_border_radius_bottom_right',
					'submenu_border_radius_bottom_left',
					'submenu_space',
					'arrows_size_width',
					'arrows_size_height',
					'submenu_items_padding_top',
					'submenu_items_padding_bottom',
					'submenu_items_padding_left',
					'submenu_items_padding_right',
					'submenu_font_size',
					'box_shadow_horizontal',
					'box_shadow_spread',
					'box_shadow_vertical',
					'thumbnail_size_width',
					'thumbnail_size_height',
					'trigger_padding_top',
					'trigger_padding_right',
					'trigger_padding_bottom',
					'trigger_padding_left',
					'mobile_trigger_font_size',
				];

				foreach ( $requires_units as $setting ) {
					if ( isset( $this->args[ $setting ] ) && is_numeric( $this->args[ $setting ] ) ) {
						$this->args[ $setting ] = trim( $this->args[ $setting ] ) . 'px';
					}
				}
			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 3.0
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {

				$this->defaults = self::get_element_defaults();
				$defaults       = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_menu' );
				$content        = apply_filters( 'fusion_shortcode_content', $content, 'fusion_menu', $args );
				$this->args     = $defaults;
				$html           = '';

				// Force click expand mode if submenu flyout is enabled.
				$this->args['expand_method'] = 'flyout' === $this->args['submenu_mode'] ? 'click' : $this->args['expand_method'];

				// Disable box shadow for flyout submenus.
				$this->args['box_shadow'] = 'flyout' === $this->args['submenu_mode'] ? 'no' : $this->args['box_shadow'];

				// Force opacity submenu transition for vertical menus.
				$this->args['expand_transition'] = 'row' !== $this->args['direction'] ? 'opacity' : $this->args['expand_transition'];

				$this->add_units_to_args();

				if ( $this->args['menu'] ) {
					$menu = wp_get_nav_menus( $this->args['menu'] );

					$html .= '<nav ' . FusionBuilder::attributes( 'menu-shortcode' ) . '>';

					$menu_markup = wp_nav_menu( $this->fetch_menu_args() );

					// Add search overlay form as direct child of <nav>.
					if ( '' !== self::$overlay_search_markup ) {
						$html .= self::$overlay_search_markup;

						self::$overlay_search_markup = '';
					}

					// Add button.
					$html .= $this->get_button();

					// Add close 'flyout' submenu button.
					if ( 'flyout' === $this->args['submenu_mode'] ) {
						$html .= $this->get_flyout_button();
					}

					$html .= $this->get_styles();

					// Add the menu.
					$html .= $menu_markup;
					$html .= '</nav>';
				}

				$this->count++;

				$this->on_render();

				return apply_filters( 'fusion_element_menu_content', $html, $args );
			}

			/**
			 * Print inline script.
			 *
			 * We're adding this one inline because it needs to run immediately
			 * before jQuery and other scripts load. Adding the script inline
			 * fixes the menu flashing on initial page-load and properly collapses the menus.
			 * We're using vanilla-JS here since this needs to be executed ASAP.
			 */
			public function print_inline_script() {
				if ( self::$inline_script_added ) {
					return;
				}

				echo '<script type="text/javascript">';
				include FUSION_BUILDER_PLUGIN_DIR . 'assets/js/min/general/fusion-menu-inline.js';
				echo '</script>';
				self::$inline_script_added = true;
			}

			/**
			 * Get the expand/collapse button.
			 *
			 * @access protected
			 * @since 3.0
			 * @return string
			 */
			protected function get_button() {
				$html = '';

				$trigger_class      = 'avada-menu-mobile-menu-trigger';
				$collapsed_nav_text = $this->args['collapsed_nav_text'];
				if ( ! $collapsed_nav_text ) {
					$trigger_class      = 'avada-menu-mobile-menu-trigger no-text';
					$collapsed_nav_text = '<span class="screen-reader-text">' . esc_html__( 'Toggle Navigation', 'fusion-builder' ) . '</span>';
				}

				// Start the button.
				$html .= '<button type="button" class="' . $trigger_class . '" onClick="fusionNavClickExpandBtn(this);" aria-expanded="false" aria-controls="menu-' . $this->args['menu'] . '">';

				// We use a wrapper span because we set it to flex, so RTL & LTR both work properly
				// and the icon changes place automagically depending on language direction.
				$html .= '<span class="inner">';
				// The text.
				$html .= '<span class="collapsed-nav-text">' . $collapsed_nav_text . '</span>';
				// The icons.
				$html .= '<span class="collapsed-nav-icon">';
				$html .= '<span class="collapsed-nav-icon-open ' . fusion_font_awesome_name_handler( $this->args['collapsed_nav_icon_open'] ) . '"></span>';
				$html .= '<span class="collapsed-nav-icon-close ' . fusion_font_awesome_name_handler( $this->args['collapsed_nav_icon_close'] ) . '"></span>';
				$html .= '</span>';
				// Close the wrapper.
				$html .= '</span>';

				// Close the button.
				$html .= '</button>';

				return $html;
			}


			/**
			 * Get the flyout close button.
			 *
			 * @access protected
			 * @since 3.0
			 * @return string
			 */
			protected function get_flyout_button() {
				return '<button type="button" class="fusion-close-flyout" onClick="fusionNavCloseFlyoutSub(this);"></button>';
			}

			/**
			 * Get the styles.
			 *
			 * @access protected
			 * @since 3.0
			 * @return string
			 */
			protected function get_styles() {
				$fusion_settings             = awb_get_fusion_settings();
				$this->base_selector         = '.fusion-body .fusion-menu-element-wrapper[data-count="' . $this->count . '"]';
				$this->base_selector_no_body = '.fusion-menu-element-wrapper[data-count="' . $this->count . '"]';
				$this->dynamic_css           = [];
				$disable_megamenu            = $fusion_settings->get( 'disable_megamenu' );

				if ( ! $this->is_default( 'font_size' ) ) {
					$selectors = [
						$this->base_selector,
						$this->base_selector . ' .fusion-menu-element-list .menu-item > a',
					];

					$this->add_css_property( $selectors, 'font-size', $this->args['font_size'] );
				}

				if ( ! $this->is_default( 'margin_top' ) ) {
					$this->add_css_property( $this->base_selector, 'margin-top', $this->args['margin_top'] );
				}

				if ( ! $this->is_default( 'margin_bottom' ) ) {
					$this->add_css_property( $this->base_selector, 'margin-bottom', $this->args['margin_bottom'] );
				}

				// Flex direction.
				if ( ! $this->is_default( 'direction' ) ) {
					$this->add_css_property( $this->base_selector . ' .fusion-menu-element-list', 'flex-direction', $this->args['direction'] );
				}

				// Justify content.
				if ( ! $this->is_default( 'justify_content' ) ) {
					$this->add_css_property( $this->base_selector . ' .fusion-menu-element-list', 'justify-content', $this->args['justify_content'] );
				}

				// Align items.
				if ( ! $this->is_default( 'align_items' ) ) {
					$this->add_css_property( $this->base_selector . ' .fusion-menu-element-list', 'align-items', $this->args['align_items'] );
				}

				// Font family.
				$selectors = [
					$this->base_selector . ' .fusion-menu-element-list',
					$this->base_selector . ' > .avada-menu-mobile-menu-trigger',
				];

				if ( $disable_megamenu ) {
					$selectors [] = $this->base_selector . ' .fusion-megamenu-wrapper .fusion-megamenu-holder .fusion-megamenu .fusion-megamenu-submenu .fusion-megamenu-title .awb-justify-title';
				}

				$menu_styles = Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'typography', 'array' );

				foreach ( $menu_styles as $rule => $value ) {
					$this->add_css_property( $selectors, $rule, $value );
				}

				// This is outside the condition on purpose.
				$this->add_css_property(
					[
						$this->base_selector . ' [class*="awb-icon-"]',
						$this->base_selector . ' [class^="awb-icon-"]',
					],
					'font-family',
					$this->args['fusion_font_family_typography'],
					true
				);

				// Minimum height.
				if ( ! $this->is_default( 'min_height' ) ) {
					$this->add_css_property( $this->base_selector . ' .fusion-menu-element-list', 'min-height', $this->args['min_height'] );
				}

				// Sticky minimum height for transition.
				if ( ! $this->is_default( 'sticky_min_height' ) ) {
					$this->add_css_property( '.fusion-body .fusion-sticky-container.fusion-sticky-transition ' . $this->base_selector_no_body . ' .fusion-menu-element-list', 'min-height', $this->args['sticky_min_height'] );
				}

				// Text transform.
				if ( ! $this->is_default( 'text_transform' ) ) {
					$this->add_css_property( $this->base_selector . ' .fusion-menu-element-list > li,' . $this->base_selector . ' .fusion-menu-element-list .fusion-megamenu-title', 'text-transform', $this->args['text_transform'] );
				}

				// Line height.
				if ( ! $this->is_default( 'line_height' ) ) {
					$this->add_css_property( $this->base_selector . ' .fusion-menu-element-list > li,' . $this->base_selector . ' .fusion-menu-element-list .fusion-megamenu-title', 'line-height', $this->args['line_height'] );
				}

				// Letter spacing.
				if ( '' !== $this->args['letter_spacing'] ) {
					$this->add_css_property( $this->base_selector . ' .fusion-menu-element-list > li', 'letter-spacing', fusion_library()->sanitize->get_value_with_unit( $this->args['letter_spacing'] ) );
				}

				// Trigger background color.
				if ( ! $this->is_default( 'mobile_trigger_background_color' ) ) {
					$this->add_css_property( $this->base_selector . ' > .avada-menu-mobile-menu-trigger', 'background-color', $this->args['mobile_trigger_background_color'] );
				}

				// Trigger background color.
				if ( ! $this->is_default( 'mobile_trigger_color' ) ) {
					$this->add_css_property( $this->base_selector . ' > .avada-menu-mobile-menu-trigger', 'color', $this->args['mobile_trigger_color'] );
				}

				// Trigger paddings.
				if ( ! $this->is_default( 'trigger_padding_top' ) ) {
					$this->add_css_property( $this->base_selector . ' > .avada-menu-mobile-menu-trigger', 'padding-top', $this->args['trigger_padding_top'] );
				}

				if ( ! $this->is_default( 'trigger_padding_right' ) ) {
					$this->add_css_property( $this->base_selector . ' > .avada-menu-mobile-menu-trigger', 'padding-right', $this->args['trigger_padding_right'] );
				}

				if ( ! $this->is_default( 'trigger_padding_bottom' ) ) {
					$this->add_css_property( $this->base_selector . ' > .avada-menu-mobile-menu-trigger', 'padding-bottom', $this->args['trigger_padding_bottom'] );
				}

				if ( ! $this->is_default( 'trigger_padding_left' ) ) {
					$this->add_css_property( $this->base_selector . ' > .avada-menu-mobile-menu-trigger', 'padding-left', $this->args['trigger_padding_left'] );
				}

				// Transition duration.
				if ( ! $this->is_default( 'transition_time' ) ) {
					$selectors = [
						$this->base_selector . ' .fusion-menu-element-list',
						$this->base_selector . ' .fusion-menu-element-list .menu-item a',
						$this->base_selector . ' .fusion-menu-element-list > li',
						$this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:not(.fusion-menu-item-button)',
						$this->base_selector . ' .fusion-overlay-search',
						$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .background-default',
						$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .background-active',
						$this->base_selector . '.expand-method-click.direction-row > ul > li > .fusion-open-nav-submenu',
						$this->base_selector . ':not(.submenu-mode-flyout) .fusion-menu-element-list li:not(.fusion-mega-menu) .sub-menu',
						$this->base_selector . ' .avada-menu-mobile-menu-trigger .collapsed-nav-icon-open',
						$this->base_selector . ' .avada-menu-mobile-menu-trigger .collapsed-nav-icon-close',
					];

					if ( $disable_megamenu ) {
						$selectors[] = $this->base_selector . ':not(.submenu-mode-flyout) .fusion-menu-element-list .fusion-megamenu-wrapper';
						$selectors[] = $this->base_selector . ' .fusion-megamenu-wrapper li .fusion-megamenu-title-disabled';
					}

					if ( 'never' !== $this->args['breakpoint'] ) {
						$selectors[] = $this->base_selector . '.collapse-enabled.mobile-mode-collapse-to-button > ul';
						$selectors[] = $this->base_selector . '.collapse-enabled .menu-item a > .fusion-button';
					}

					$this->add_css_property( $selectors, 'transition-duration', (int) $this->args['transition_time'] . 'ms' );
				}

				// Gap.
				if ( ! $this->is_default( 'gap' ) ) {
					if ( 'column' !== $this->args['direction'] ) {
						$this->add_css_property( $this->base_selector . ' .fusion-menu-element-list > li', 'margin-left', "calc({$this->args['gap']} / 2)" );
						$this->add_css_property( $this->base_selector . ' .fusion-menu-element-list > li', 'margin-right', "calc({$this->args['gap']} / 2)" );
					} else {
						$this->add_css_property( $this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list > li:not(:last-child)', 'margin-bottom', $this->args['gap'], true );
					}
				}

				// Background color.
				if ( ! $this->is_default( 'bg' ) ) {
					$selectors = [
						$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button)',
						$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .background-default',
						$this->base_selector . ' .custom-menu-search-overlay ~ .fusion-overlay-search',
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list .custom-menu-search-overlay .fusion-overlay-search',
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list .custom-menu-search-dropdown',
					];
					$this->add_css_property( $selectors, 'background-color', $this->args['bg'] );
				}

				// Border radius.
				if ( ! $this->is_default( 'border_radius_top_left' ) || ! $this->is_default( 'border_radius_top_right' ) || ! $this->is_default( 'border_radius_bottom_right' ) || ! $this->is_default( 'border_radius_bottom_left' ) ) {
					$value     = $this->args['border_radius_top_left'] . ' ' . $this->args['border_radius_top_right'] . ' ' . $this->args['border_radius_bottom_right'] . ' ' . $this->args['border_radius_bottom_left'];
					$selectors = [
						$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button)',
						$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .background-default',
						$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .background-active',
					];

					$this->add_css_property( $selectors, 'border-radius', $value );
				}

				// Menu item paddings, combined with borders.
				// Combined, padding top, border top.
				if ( ! $this->is_default( 'items_padding_top' ) || ! $this->is_default( 'border_top' ) ) {
					$selectors = [
						$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) > a',
						$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) .fusion-widget-cart > a',
						$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .fusion-open-nav-submenu',
					];
					$this->add_css_property( $selectors, 'padding-top', 'calc(' . $this->args['items_padding_top'] . ' + ' . $this->args['border_top'] . ')' );
				}

				// Combined, padding right, border right.
				if ( ! $this->is_default( 'items_padding_right' ) || ! $this->is_default( 'border_right' ) ) {
					$selectors = [
						$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) > a',
						$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) .fusion-widget-cart > a',
					];
					$this->add_css_property( $selectors, 'padding-right', 'calc(' . $this->args['items_padding_right'] . ' + ' . $this->args['border_right'] . ')' );
				}

				// Combined, padding bottom, border bottom.
				if ( ! $this->is_default( 'items_padding_bottom' ) || ! $this->is_default( 'border_bottom' ) ) {
					$selectors = [
						$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) > a',
					];

					if ( class_exists( 'WooCommerce' ) ) {
						$selectors[] = $this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) .fusion-widget-cart > a';
					}
					$this->add_css_property( $selectors, 'padding-bottom', 'calc(' . $this->args['items_padding_bottom'] . ' + ' . $this->args['border_bottom'] . ')' );
				}

				// Combined, padding left, border left.
				if ( ! $this->is_default( 'items_padding_left' ) || ! $this->is_default( 'border_left' ) ) {
					$selectors = [
						$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) > a',
					];
					if ( class_exists( 'WooCommerce' ) ) {
						$selectors[] = $this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) .fusion-widget-cart > a';
					}
					$this->add_css_property( $selectors, 'padding-left', 'calc(' . $this->args['items_padding_left'] . ' + ' . $this->args['border_left'] . ')' );
				}

				// Combined padding top and active border top.
				if ( ! $this->is_default( 'items_padding_top' ) || ! $this->is_default( 'active_border_top' ) ) {
					$selectors = [
						$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button):hover > a',
						$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button).hover > a',
						$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button):focus > a',
						$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button):active > a',
						$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button):focus-within > a',
						$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button).current-menu-item > a',
						$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button).current-menu-ancestor > a',
						$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button).current-menu-parent > a',
						$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button).expanded > a',
						$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button):hover > .fusion-open-nav-submenu',
						$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button).hover > .fusion-open-nav-submenu',
						$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button):focus > .fusion-open-nav-submenu',
						$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button):active > .fusion-open-nav-submenu',
						$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button):focus-within > .fusion-open-nav-submenu',
						$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button).current-menu-item > .fusion-open-nav-submenu',
						$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button).current-menu-ancestor > .fusion-open-nav-submenu',
						$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button).current-menu-parent > .fusion-open-nav-submenu',
						$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button).expanded > .fusion-open-nav-submenu',
					];

					if ( 'column' === $this->args['direction'] ) {
						$selectors[] = $this->base_selector . '.direction-column .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .fusion-open-nav-submenu';
					}
					$this->add_css_property( $selectors, 'padding-top', 'calc(' . $this->args['items_padding_top'] . ' + ' . $this->args['active_border_top'] . ')' );
				}

				$selectors = [
					$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button):not(.menu-item-has-children):hover > a',
					$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button):not(.menu-item-has-children).hover > a',
					$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button):not(.menu-item-has-children):focus > a',
					$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button):not(.menu-item-has-children):active > a',
					$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button):not(.menu-item-has-children):focus-within > a',
					$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button):not(.menu-item-has-children).current-menu-item > a',
					$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button):not(.menu-item-has-children).current-menu-ancestor > a',
					$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button):not(.menu-item-has-children).current-menu-parent > a',
					$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button):not(.menu-item-has-children).expanded > a',
				];

				// Combined padding right and active border right.
				if ( ! $this->is_default( 'items_padding_right' ) || ! $this->is_default( 'active_border_right' ) ) {
					$this->add_css_property( $selectors, 'padding-right', 'calc(' . $this->args['items_padding_right'] . ' + ' . $this->args['active_border_right'] . ')' );
				}

				// Combined padding bottom and active border bottom.
				if ( ! $this->is_default( 'items_padding_bottom' ) || ! $this->is_default( 'active_border_bottom' ) ) {
					$this->add_css_property( $selectors, 'padding-bottom', 'calc(' . $this->args['items_padding_bottom'] . ' + ' . $this->args['active_border_bottom'] . ')' );
					if ( 'column' === $this->args['direction'] ) {
						$this->add_css_property( $this->base_selector . '.direction-column .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .fusion-open-nav-submenu', 'padding-bottom', 'calc(' . $this->args['items_padding_bottom'] . ' + ' . $this->args['active_border_bottom'] . ')' );
					} elseif ( 'click' === $this->args['expand_method'] ) {
						$this->add_css_property(
							$this->base_selector . '.expand-method-click.direction-row > ul > li > .fusion-open-nav-submenu',
							'padding-bottom',
							'calc(' . $this->args['items_padding_bottom'] . ' + ' . $this->args['active_border_bottom'] . ')'
						);
					}
				}

				// Combined padding left and active border left.
				if ( ! $this->is_default( 'items_padding_left' ) || ! $this->is_default( 'active_border_left' ) ) {
					$this->add_css_property( $selectors, 'padding-left', 'calc(' . $this->args['items_padding_left'] . ' + ' . $this->args['active_border_left'] . ')' );
				}

				// Padding top.
				if ( ! $this->is_default( 'items_padding_top' ) ) {
					$selectors = [
						$this->base_selector . ' .fusion-menu-form-inline',
						$this->base_selector . ' .custom-menu-search-overlay ~ .fusion-overlay-search',
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list .custom-menu-search-overlay .fusion-overlay-search',
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list .fusion-menu-form-inline',
					];
					$this->add_css_property( $selectors, 'padding-top', $this->args['items_padding_top'] );
				}

				// Combined padding top and submenu item padding top.
				if ( 'row' === $this->args['direction'] && 'click' === $this->args['expand_method'] && ( ! $this->is_default( 'items_padding_top' ) || ! $this->is_default( 'submenu_items_padding_top' ) ) ) {
					$this->add_css_property(
						$this->base_selector . '.expand-method-click.direction-row > ul > li > .fusion-open-nav-submenu',
						'padding-bottom',
						'calc(' . $this->args['items_padding_bottom'] . ' + ' . $this->args['active_border_bottom'] . ')'
					);

					if ( 'yes' === $this->args['dropdown_carets'] ) {
						$this->add_css_property(
							$this->base_selector . '.dropdown-carets-yes:not(.collapse-enabled).direction-row.expand-method-click li.menu-item-has-children:not(.fusion-menu-item-button) ul .fusion-open-nav-submenu',
							'padding-top',
							$this->args['submenu_items_padding_top']
						);
						$this->add_css_property(
							$this->base_selector . '.dropdown-carets-yes:not(.collapse-enabled).direction-row.expand-method-click li.menu-item-has-children:not(.fusion-menu-item-button) ul .fusion-open-nav-submenu',
							'padding-bottom',
							$this->args['submenu_items_padding_bottom']
						);
					}
				}

				// Padding bottom.
				if ( ! $this->is_default( 'items_padding_bottom' ) ) {
					$selectors = [
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .fusion-open-nav-submenu',
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-form-inline',
						$this->base_selector . ':not(.collapse-enabled) .custom-menu-search-overlay ~ .fusion-overlay-search',
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list .custom-menu-search-overlay .fusion-overlay-search',
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list .fusion-menu-form-inline',
					];
					$this->add_css_property( $selectors, 'padding-bottom', $this->args['items_padding_bottom'] );
				}

				// Padding right.
				if ( ! $this->is_default( 'items_padding_right' ) ) {
					if ( ! is_rtl() && 'click' === $this->args['expand_method'] ) {
						$this->add_css_property(
							[ '.ltr' . $this->base_selector . '.expand-method-click li.menu-item-has-children:not(.fusion-menu-item-button) > .fusion-open-nav-submenu' ],
							'padding-right',
							$this->args['items_padding_right']
						);
					}

					// Regular paddings.
					$selectors = [
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-form-inline',
						$this->base_selector . ':not(.collapse-enabled) .custom-menu-search-overlay ~ .fusion-overlay-search',
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list .custom-menu-search-overlay .fusion-overlay-search',
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list .fusion-menu-form-inline',
					];

					if ( ! is_rtl() && 'click' === $this->args['expand_method'] && 'column' === $this->args['direction'] ) {
						$selectors[] = '.ltr' . $this->base_selector . '.direction-column.expand-method-click.expand-left .menu-item-has-children > a';
					}
					$this->add_css_property( $selectors, 'padding-right', $this->args['items_padding_right'] );

					$this->add_css_property( $this->base_selector . ' .custom-menu-search-dropdown .fusion-main-menu-icon', 'padding-right', $this->args['items_padding_right'], true );
				}

				// Padding left.
				if ( ! $this->is_default( 'items_padding_left' ) ) {
					if ( is_rtl() && 'click' === $this->args['expand_method'] ) {
						$selectors = [ '.rtl' . $this->base_selector . '.expand-method-click li.menu-item-has-children:not(.fusion-menu-item-button) > .fusion-open-nav-submenu' ];
						$this->add_css_property( $selectors, 'padding-left', $this->args['items_padding_left'] );
					}

					// Regular padding.
					$selectors = [
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-form-inline',
						$this->base_selector . ':not(.collapse-enabled) .custom-menu-search-overlay ~ .fusion-overlay-search',
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list .custom-menu-search-overlay .fusion-overlay-search',
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list .fusion-menu-form-inline',
					];
					if ( ! is_rtl() && 'click' === $this->args['expand_method'] && 'column' === $this->args['direction'] ) {
						$selectors[] = '.ltr' . $this->base_selector . '.direction-column.expand-method-click.expand-left .menu-item-has-children > a';
					}

					$this->add_css_property( $selectors, 'padding-left', $this->args['items_padding_left'] );

					// Important ones.
					$this->add_css_property( $this->base_selector . ' .custom-menu-search-dropdown .fusion-main-menu-icon', 'padding-left', $this->args['items_padding_left'], true );
				}

				// Color.
				if ( ! $this->is_default( 'color' ) ) {
					// Ones with important.
					$selectors = [
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button) > a',
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .fusion-open-nav-submenu',
					];

					if ( 'flyout' === $this->args['submenu_mode'] ) {
						$selectors[] = $this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button).current-menu-ancestor.awb-flyout-top-level-no-link > a';
					}

					$this->add_css_property( $selectors, 'color', $this->args['color'], true );

					// Ones without important.
					if ( 'click' === $this->args['expand_method'] ) {
						$selectors[] = $this->base_selector . '.expand-method-click li ul .fusion-open-nav-submenu';
					}
					$this->add_css_property( $selectors, 'color', $this->args['color'] );

					// Background, but why?
					$selectors = [
						$this->base_selector . ' .fusion-overlay-search .fusion-close-search:before',
						$this->base_selector . ' .fusion-overlay-search .fusion-close-search:after',
					];
					$this->add_css_property( $selectors, 'background', $this->args['color'] );
				}

				// Active background.
				if ( ! $this->is_default( 'active_bg' ) ) {
					$selectors = [
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .background-active',
					];
					$this->add_css_property( $this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .background-active', 'background', $this->args['active_bg'] );

					// Border top.
					if ( 'row' === $this->args['direction'] ) {
						// Click method.
						$selectors = [
							$this->base_selector . ':not(.collapse-enabled).expand-method-click.dropdown-arrows-parent.direction-row > ul > li.menu-item-has-children.expanded:after',
							$this->base_selector . ':not(.collapse-enabled).expand-method-' . $this->args['expand_method'] . '.dropdown-arrows-parent.direction-row > ul > li.menu-item-has-children:hover:after',
							$this->base_selector . ':not(.collapse-enabled).expand-method-' . $this->args['expand_method'] . '.dropdown-arrows-parent.direction-row > ul > li.menu-item-has-children.hover:after',
							$this->base_selector . ':not(.collapse-enabled).expand-method-' . $this->args['expand_method'] . '.dropdown-arrows-parent.direction-row > ul > li.menu-item-has-children:focus:after',
							$this->base_selector . ':not(.collapse-enabled).expand-method-' . $this->args['expand_method'] . '.dropdown-arrows-parent.direction-row > ul > li.menu-item-has-children:active:after',
							$this->base_selector . ':not(.collapse-enabled).expand-method-' . $this->args['expand_method'] . '.dropdown-arrows-parent.direction-row > ul > li.menu-item-has-children:focus-within:after',
						];

						if ( false !== strpos( $this->args['arrows'], 'active' ) ) {
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-' . $this->args['expand_method'] . '.direction-row > ul > li:hover:after';
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-' . $this->args['expand_method'] . '.direction-row > ul > li:focus:after';
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-' . $this->args['expand_method'] . '.direction-row > ul > li:active:after';
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-' . $this->args['expand_method'] . '.direction-row > ul > li:focus-within:after';
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-' . $this->args['expand_method'] . '.direction-row > ul > li.current-menu-item:after';
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-' . $this->args['expand_method'] . '.direction-row > ul > li.current-menu-ancestor:after';
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-' . $this->args['expand_method'] . '.direction-row > ul > li.current-menu-parent:after';
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-' . $this->args['expand_method'] . '.direction-row > ul > li.expanded:after';

							if ( 'click' === $this->args['expand_method'] ) {
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-row > ul > li.hover:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-row > ul > li:focus:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-row > ul > li:active:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-row > ul > li:focus-within:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-row > ul > li.current-menu-item:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-row > ul > li.current-menu-ancestor:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-row > ul > li.current-menu-parent:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-row > ul > li.expanded:after';
							}
						}
						$this->add_css_property( $selectors, 'border-top-color', $this->args['active_bg'] );
					}

					// Border left.
					if ( 'column' === $this->args['direction'] ) {
						if ( 'click' === $this->args['expand_method'] ) {
							$selectors = [
								$this->base_selector . ':not(.collapse-enabled).expand-method-click.dropdown-arrows-parent.direction-column.expand-right > ul > li.menu-item-has-children.expanded:not(.fusion-megamenu-menu):after',
							];
							if ( false !== strpos( $this->args['arrows'], 'active' ) ) {
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-right > ul > li:hover:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-right > ul > li.hover:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-right > ul > li:focus:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-right > ul > li:active:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-right > ul > li:focus-within:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-right > ul > li.current-menu-item:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-right > ul > li.current-menu-ancestor:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-right > ul > li.current-menu-parent:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-right > ul > li.expanded:after';
							}
						}

						if ( 'hover' === $this->args['expand_method'] ) {
							$selectors = [];
							if ( false !== strpos( $this->args['arrows'], 'main' ) ) {
								$selectors = [
									$this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-right > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):hover:after',
									$this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-right > ul > li.menu-item-has-children:not(.fusion-megamenu-menu).hover:after',
									$this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-right > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):focus:after',
									$this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-right > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):active:after',
									$this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-right > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):focus-within:after',
								];
							}
							if ( false !== strpos( $this->args['arrows'], 'active' ) ) {
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-right > ul > li:hover:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-right > ul > li.hover:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-right > ul > li:focus:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-right > ul > li:active:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-right > ul > li:focus-within:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-right > ul > li.current-menu-item:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-right > ul > li.current-menu-ancestor:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-right > ul > li.current-menu-parent:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-right > ul > li.expanded:after';
							}
						}
						$this->add_css_property( $selectors, 'border-left-color', $this->args['active_bg'] );
					}

					// Border right.
					if ( 'column' === $this->args['direction'] ) {
						if ( 'click' === $this->args['expand_method'] ) {
							$selectors = [
								$this->base_selector . ':not(.collapse-enabled).expand-method-click.dropdown-arrows-parent.direction-column.expand-left > ul > li.menu-item-has-children.expanded:not(.fusion-megamenu-menu):after',
							];
							if ( false !== strpos( $this->args['arrows'], 'active' ) ) {
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-left > ul > li:hover:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-left > ul > li.hover:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-left > ul > li:focus:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-left > ul > li:active:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-left > ul > li:focus-within:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-left > ul > li.current-menu-item:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-left > ul > li.current-menu-ancestor:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-left > ul > li.current-menu-parent:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-left > ul > li.expanded:after';
							}
						}
						if ( 'hover' === $this->args['expand_method'] ) {
							$selectors = [];
							if ( false !== strpos( $this->args['arrows'], 'main' ) ) {
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-left > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):hover:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-left > ul > li.menu-item-has-children:not(.fusion-megamenu-menu).hover:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-left > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):focus:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-left > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):active:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-left > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):focus-within:after';
							}
							if ( false !== strpos( $this->args['arrows'], 'active' ) ) {
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-left > ul > li:hover:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-left > ul > li.hover:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-left > ul > li:focus:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-left > ul > li:active:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-left > ul > li:focus-within:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-left > ul > li.current-menu-item:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-left > ul > li.current-menu-ancestor:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-left > ul > li.current-menu-parent:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-left > ul > li.expanded:after';
							}
						}
						$this->add_css_property( $selectors, 'border-right-color', $this->args['active_bg'] );
					}
				}

				// Active color.
				if ( ! $this->is_default( 'active_color' ) ) {
					// Important ones.
					$selectors = [
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button):not(.custom-menu-search-overlay):hover > a',
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button).hover > a',
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button):not(.custom-menu-search-overlay):focus > a',
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button):not(.custom-menu-search-overlay):active > a',
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button):not(.custom-menu-search-overlay):focus-within > a',
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button).current-menu-item > a',
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button).current-menu-ancestor:not(.awb-flyout-top-level-no-link) > a',
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button).current-menu-parent:not(.awb-flyout-top-level-no-link) > a',
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button).expanded > a',
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button):hover > .fusion-open-nav-submenu',
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button).hover > .fusion-open-nav-submenu',
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button):focus > .fusion-open-nav-submenu',
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button):active > .fusion-open-nav-submenu',
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button):focus-within > .fusion-open-nav-submenu',
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button).current-menu-item > .fusion-open-nav-submenu',
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button).current-menu-ancestor > .fusion-open-nav-submenu',
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button).current-menu-parent > .fusion-open-nav-submenu',
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.fusion-menu-item-button).expanded > .fusion-open-nav-submenu',
					];
					$this->add_css_property( $selectors, 'color', $this->args['active_color'], true );
				}

				// Border top.
				if ( ! $this->is_default( 'border_top' ) ) {
					$selectors = [
						$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .background-default',
					];
					$this->add_css_property( $selectors, 'border-top-width', $this->args['border_top'] );
				}

				// Border right.
				if ( ! $this->is_default( 'border_right' ) ) {
					$selectors = [
						$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .background-default',
					];
					$this->add_css_property( $selectors, 'border-right-width', $this->args['border_right'] );
				}

				// Border bottom.
				if ( ! $this->is_default( 'border_bottom' ) ) {
					$selectors = [
						$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .background-default',
					];
					$this->add_css_property( $selectors, 'border-bottom-width', $this->args['border_bottom'] );
				}

				// Border left.
				if ( ! $this->is_default( 'border_left' ) ) {
					$selectors = [
						$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .background-default',
					];
					$this->add_css_property( $selectors, 'border-left-width', $this->args['border_left'] );
				}

				// Border color.
				if ( ! $this->is_default( 'border_color' ) ) {
					// Important ones.
					$selectors = [
						$this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .background-default',
					];
					$this->add_css_property( $selectors, 'border-color', $this->args['border_color'] );
				}

				// Active border sizes.
				if ( ! $this->is_default( 'active_border_top' ) ) {
					$this->add_css_property( $this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .background-active', 'border-top-width', $this->args['active_border_top'] );
				}

				// Active border sizes.
				if ( ! $this->is_default( 'active_border_right' ) ) {
					$this->add_css_property( $this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .background-active', 'border-right-width', $this->args['active_border_right'] );
				}

				// Active border sizes.
				if ( ! $this->is_default( 'active_border_bottom' ) ) {
					$this->add_css_property( $this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .background-active', 'border-bottom-width', $this->args['active_border_bottom'] );
				}

				// Active border sizes.
				if ( ! $this->is_default( 'active_border_left' ) ) {
					$this->add_css_property( $this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .background-active', 'border-left-width', $this->args['active_border_left'] );
				}

				// Active border color.
				if ( ! $this->is_default( 'active_border_color' ) ) {
					$this->add_css_property( $this->base_selector . ' .fusion-menu-element-list > li:not(.fusion-menu-item-button) > .background-active', 'border-color', $this->args['active_border_color'] );

					if ( 'row' === $this->args['direction'] ) {
						$selectors = [];
						if ( 'click' === $this->args['expand_method'] ) {
							if ( false !== strpos( $this->args['arrows'], 'main' ) ) {
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-click.dropdown-arrows-parent.direction-row.has-active-border-bottom-yes > ul > li.menu-item-has-children.expanded:after';
							}
							if ( false !== strpos( $this->args['arrows'], 'active' ) ) {
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-row.has-active-border-bottom-yes > ul > li:hover:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-row.has-active-border-bottom-yes > ul > li.hover:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-row.has-active-border-bottom-yes > ul > li:focus:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-row.has-active-border-bottom-yes > ul > li:active:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-row.has-active-border-bottom-yes > ul > li:focus-within:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-row.has-active-border-bottom-yes > ul > li.current-menu-item:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-row.has-active-border-bottom-yes > ul > li.current-menu-ancestor:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-row.has-active-border-bottom-yes > ul > li.current-menu-parent:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-row.has-active-border-bottom-yes > ul > li.expanded:after';
							}
						}

						if ( 'hover' === $this->args['expand_method'] ) {
							if ( false !== strpos( $this->args['arrows'], 'main' ) ) {
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-row.has-active-border-bottom-color-yes > ul > li.menu-item-has-children:hover:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-row.has-active-border-bottom-color-yes > ul > li.menu-item-has-children.hover:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-row.has-active-border-bottom-color-yes > ul > li.menu-item-has-children:focus:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-row.has-active-border-bottom-color-yes > ul > li.menu-item-has-children:active:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-row.has-active-border-bottom-color-yes > ul > li.menu-item-has-children:focus-within:after';
							}
							if ( false !== strpos( $this->args['arrows'], 'active' ) ) {
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-row.has-active-border-bottom-yes > ul > li:hover:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-row.has-active-border-bottom-yes > ul > li.hover:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-row.has-active-border-bottom-yes > ul > li:focus:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-row.has-active-border-bottom-yes > ul > li:active:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-row.has-active-border-bottom-yes > ul > li:focus-within:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-row.has-active-border-bottom-yes > ul > li.current-menu-item:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-row.has-active-border-bottom-yes > ul > li.current-menu-ancestor:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-row.has-active-border-bottom-yes > ul > li.current-menu-parent:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-row.has-active-border-bottom-yes > ul > li.expanded:after';
							}
						}
						$this->add_css_property( $selectors, 'border-top-color', $this->args['active_border_color'] );
					}
					if ( 'column' === $this->args['direction'] ) {
						$selectors = [];
						if ( 'click' === $this->args['expand_method'] ) {
							if ( false !== strpos( $this->args['arrows'], 'main' ) ) {
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-click.dropdown-arrows-parent.direction-column.expand-right.has-active-border-right-yes > ul > li.menu-item-has-children.expanded:not(.fusion-megamenu-menu):after';
							}
							if ( false !== strpos( $this->args['arrows'], 'active' ) ) {
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-right.has-active-border-right-yes > ul > li:hover:after';
								$selectors[] = $this->base_selector . ':not(.collaps e-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-right.has-active-border-right-yes > ul > li.hover:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-right.has-active-border-right-yes > ul > li:focus:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-right.has-active-border-right-yes > ul > li:active:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-right.has-active-border-right-yes > ul > li:focus-within:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-right.has-active-border-right-yes > ul > li.current-menu-item:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-right.has-active-border-right-yes > ul > li.current-menu-ancestor:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-right.has-active-border-right-yes > ul > li.current-menu-parent:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-right.has-active-border-right-yes > ul > li.expanded:after';
							}
						}

						if ( 'hover' === $this->args['expand_method'] ) {
							if ( false !== strpos( $this->args['arrows'], 'main' ) ) {
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-right.has-active-border-right-yes > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):hover:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-right.has-active-border-right-yes > ul > li.menu-item-has-children:not(.fusion-megamenu-menu).hover:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-right.has-active-border-right-yes > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):focus:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-right.has-active-border-right-yes > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):active:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-right.has-active-border-right-yes > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):focus-within:after';
							}
							if ( false !== strpos( $this->args['arrows'], 'active' ) ) {
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-right.has-active-border-right-yes > ul > li:hover:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-right.has-active-border-right-yes > ul > li.hover:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-right.has-active-border-right-yes > ul > li:focus:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-right.has-active-border-right-yes > ul > li:active:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-right.has-active-border-right-yes > ul > li:focus-within:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-right.has-active-border-right-yes > ul > li.current-menu-item:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-right.has-active-border-right-yes > ul > li.current-menu-ancestor:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-right.has-active-border-right-yes > ul > li.current-menu-parent:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-right.has-active-border-right-yes > ul > li.expanded:after';
							}
						}
						$this->add_css_property( $selectors, 'border-left-color', $this->args['active_border_color'] );
					}

					if ( 'column' === $this->args['direction'] ) {
						$selectors = [];
						if ( 'click' === $this->args['expand_method'] ) {
							if ( false !== strpos( $this->args['arrows'], 'main' ) ) {
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-click.dropdown-arrows-parent.direction-column.expand-left.has-active-border-left-yes > ul > li.menu-item-has-children.expanded:not(.fusion-megamenu-menu):after';
							}
							if ( false !== strpos( $this->args['arrows'], 'active' ) ) {
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-left.has-active-border-left-yes > ul > li:hover:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-left.has-active-border-left-yes > ul > li.hover:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-left.has-active-border-left-yes > ul > li:focus:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-left.has-active-border-left-yes > ul > li:active:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-left.has-active-border-left-yes > ul > li:focus-within:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-left.has-active-border-left-yes > ul > li.current-menu-item:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-left.has-active-border-left-yes > ul > li.current-menu-ancestor:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-left.has-active-border-left-yes > ul > li.current-menu-parent:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-column.expand-left.has-active-border-left-yes > ul > li.expanded:after';
							}
						}
						if ( 'hover' === $this->args['expand_method'] ) {
							if ( false !== strpos( $this->args['arrows'], 'main' ) ) {
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-left.has-active-border-left-yes > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):hover:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-left.has-active-border-left-yes > ul > li.menu-item-has-children:not(.fusion-megamenu-menu).hover:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-left.has-active-border-left-yes > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):focus:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-left.has-active-border-left-yes > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):active:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-left.has-active-border-left-yes > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):focus-within:after';
							}
							if ( false !== strpos( $this->args['arrows'], 'active' ) ) {
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-left.has-active-border-left-yes > ul > li:hover:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-left.has-active-border-left-yes > ul > li.hover:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-left.has-active-border-left-yes > ul > li:focus:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-left.has-active-border-left-yes > ul > li:active:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-left.has-active-border-left-yes > ul > li:focus-within:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-left.has-active-border-left-yes > ul > li.current-menu-item:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-left.has-active-border-left-yes > ul > li.current-menu-ancestor:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-left.has-active-border-left-yes > ul > li.current-menu-parent:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-left.has-active-border-left-yes > ul > li.expanded:after';
							}
						}
						$this->add_css_property( $selectors, 'border-right-color', $this->args['active_border_color'] );
					}
				}

				// Submenu space.
				if ( ! $this->is_default( 'submenu_space' ) ) {
					if ( 'flyout' !== $this->args['submenu_mode'] ) {
						$selectors = [
							$this->base_selector . '.direction-row:not(.collapse-enabled):not(.submenu-mode-flyout) .fusion-menu-element-list > li > ul.sub-menu:not(.fusion-megamenu)',
						];
						if ( $disable_megamenu ) {
							$selectors[] = $this->base_selector . ':not(.collapse-enabled):not(.submenu-mode-flyout) .fusion-menu-element-list .fusion-megamenu-wrapper';
						}
						$this->add_css_property( $selectors, 'margin-top', $this->args['submenu_space'], true );
					}

					if ( 'row' === $this->args['direction'] ) {
						$selectors = [];
						if ( false !== strpos( $this->args['arrows'], 'active' ) ) {
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click.direction-row > ul > li:after';
						}
						if ( 'hover' === $this->args['expand_method'] ) {
							$selectors[] = $this->base_selector . '.expand-method-hover.direction-row .fusion-menu-element-list > li:hover:before';
							$selectors[] = $this->base_selector . '.expand-method-hover.direction-row .fusion-menu-element-list > li.hover:before';
							$selectors[] = $this->base_selector . '.expand-method-hover.direction-row .fusion-menu-element-list > li:focus:before';
							$selectors[] = $this->base_selector . '.expand-method-hover.direction-row .fusion-menu-element-list > li:active:before';
							$selectors[] = $this->base_selector . '.expand-method-hover.direction-row .fusion-menu-element-list > li:focus-within:before';
							if ( false !== strpos( $this->args['arrows'], 'main' ) ) {
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-row > ul > li.menu-item-has-children:after';
							}
							if ( false !== strpos( $this->args['arrows'], 'submenu' ) ) {
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-row > ul > li.menu-item-has-children:after';
							}
							if ( false !== strpos( $this->args['arrows'], 'active' ) ) {
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-row > ul > li:after';
							}
						}
						$this->add_css_property( $selectors, 'bottom', 'calc(0px - ' . $this->args['submenu_space'] . ')' );

						if ( 'click' === $this->args['expand_method'] ) {
							$selectors = [];
							if ( false !== strpos( $this->args['arrows'], 'submenu' ) ) {
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-click.dropdown-arrows-child.direction-row > ul > li.menu-item-has-children::after';
							}
							if ( false !== strpos( $this->args['arrows'], 'main' ) ) {
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-click.dropdown-arrows-parent.direction-row > ul > li.menu-item-has-children::after';
							}

							$this->add_css_property( $selectors, 'bottom', 'calc(0px - ' . $this->args['submenu_space'] . ')' );
						}
					}

					if ( 'column' === $this->args['direction'] ) {
						if ( 'hover' === $this->args['expand_method'] ) {
							$selectors = [
								$this->base_selector . '.expand-method-hover.direction-column.expand-right li:hover:before',
								$this->base_selector . '.expand-method-hover.direction-column.expand-right li.hover:before',
								$this->base_selector . '.expand-method-hover.direction-column.expand-right li:focus:before',
								$this->base_selector . '.expand-method-hover.direction-column.expand-right li:active:before',
								$this->base_selector . '.expand-method-hover.direction-column.expand-right li:focus-within:before',
							];
							$this->add_css_property( $selectors, 'width', $this->args['submenu_space'] );
						}

						$this->add_css_property( $this->base_selector . '.direction-column.expand-right .fusion-menu-element-list ul', 'margin-left', $this->args['submenu_space'], true );

						$this->add_css_property( $this->base_selector . '.direction-column.expand-left .fusion-menu-element-list ul', 'margin-right', $this->args['submenu_space'], true );

						$selectors = [];
						if ( 'click' === $this->args['expand_method'] ) {
							if ( false !== strpos( $this->args['arrows'], 'main' ) ) {
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-click.dropdown-arrows-parent.direction-column.expand-left > ul > li:after';
							}
							if ( false !== strpos( $this->args['arrows'], 'submenu' ) ) {
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-click.dropdown-arrows-child.direction-column.expand-left > ul > li:after';
							}
							if ( false !== strpos( $this->args['arrows'], 'active' ) ) {
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-click.active-item-arrows-on.direction-column.expand-left > ul > li:after';
							}
						}

						if ( 'hover' === $this->args['expand_method'] ) {
							if ( false !== strpos( $this->args['arrows'], 'main' ) ) {
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-left > ul > li:after';
							}
							if ( false !== strpos( $this->args['arrows'], 'submenu' ) ) {
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-column.expand-left > ul > li:after';
							}
							if ( false !== strpos( $this->args['arrows'], 'active' ) ) {
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover.direction-column.expand-left > ul > li:after';
							}
						}
						$this->add_css_property( $selectors, 'left', 'calc(0px - ' . $this->args['submenu_space'] . ')' );
					}

					if ( 'row' === $this->args['direction'] ) {

						if ( 'hover' === $this->args['expand_method'] ) {
							$selectors = [
								$this->base_selector . '.expand-method-hover.direction-row .fusion-menu-element-list > li:hover:before',
								$this->base_selector . '.expand-method-hover.direction-row .fusion-menu-element-list > li.hover:before',
								$this->base_selector . '.expand-method-hover.direction-row .fusion-menu-element-list > li:focus:before',
								$this->base_selector . '.expand-method-hover.direction-row .fusion-menu-element-list > li:active:before',
								$this->base_selector . '.expand-method-hover.direction-row .fusion-menu-element-list > li:focus-within:before',
							];
							$this->add_css_property( $selectors, 'height', $this->args['submenu_space'] );
						}

						if ( 'slide_up' === $this->args['expand_transition'] ) {
							$this->add_css_property(
								[ $this->base_selector . '.submenu-transition-slide_up:not(.collapse-enabled).expand-method-' . $this->args['expand_method'] . '.direction-row .fusion-menu-element-list li::after' ],
								'transform',
								"translateY({$this->args['submenu_space']})"
							);
						}
					}
				}

				// Submenu and arrow width.
				if ( ( ! $this->is_default( 'submenu_space' ) || ! $this->is_default( 'arrows_size_width' ) ) && 'column' === $this->args['direction'] ) {
					$selectors = [];
					if ( 'click' === $this->args['expand_method'] ) {
						if ( false !== strpos( $this->args['arrows'], 'main' ) ) {
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-click.dropdown-arrows-parent.direction-column > ul > li:not(.fusion-menu-item-button):after';
						}
						if ( false !== strpos( $this->args['arrows'], 'submenu' ) ) {
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-click.dropdown-arrows-child.direction-column > ul > li:not(.fusion-menu-item-button):after';
						}
					}
					if ( 'hover' === $this->args['expand_method'] ) {
						if ( false !== strpos( $this->args['arrows'], 'main' ) ) {
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-right > ul > li:after';
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-left > ul > li:after';
						}
						if ( false !== strpos( $this->args['arrows'], 'submenu' ) ) {
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-column.expand-right > ul > li:after';
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-column.expand-left > ul > li:after';
						}
					}
					$this->add_css_property( $selectors, 'width', 'calc(' . $this->args['submenu_space'] . ' - ' . $this->args['arrows_size_width'] . ' * 2)' );
				}

				// Submenu and arrow height.
				if ( ( ! $this->is_default( 'submenu_space' ) || ! $this->is_default( 'arrows_size_height' ) ) && 'row' === $this->args['direction'] ) {
					if ( false !== strpos( $this->args['arrows'], 'active' ) ) {
						$this->add_css_property(
							[ $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-' . $this->args['expand_method'] . '.direction-row > ul > li:after' ],
							'height',
							$this->args['submenu_space']
						);
					}

					if ( 'slide_up' === $this->args['expand_transition'] ) {
						$this->add_css_property(
							[ $this->base_selector . ':not(.collapse-enabled).submenu-transition-slide_up.direction-row.dropdown-arrows-parent > ul > li:after' ],
							'top',
							'calc(100% - ' . $this->args['submenu_space'] . ')',
							true
						);
					}

					// Expanded.
					$selectors = [];
					if ( 'click' === $this->args['expand_method'] ) {
						if ( false !== strpos( $this->args['arrows'], 'main' ) ) {
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-click.dropdown-arrows-parent.direction-row > ul > li.menu-item-has-children.expanded:after';
						}
						if ( false !== strpos( $this->args['arrows'], 'submenu' ) ) {
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-click.dropdown-arrows-child.direction-row > ul > li.menu-item-has-children.expanded:after';
						}
					}
					if ( false !== strpos( $this->args['arrows'], 'main' ) ) {
						$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-' . $this->args['expand_method'] . '.dropdown-arrows-parent.direction-row > ul > li.menu-item-has-children:hover:after';
						$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-' . $this->args['expand_method'] . '.dropdown-arrows-parent.direction-row > ul > li.menu-item-has-children.hover:after';
						$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-' . $this->args['expand_method'] . '.dropdown-arrows-parent.direction-row > ul > li.menu-item-has-children:focus:after';
						$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-' . $this->args['expand_method'] . '.dropdown-arrows-parent.direction-row > ul > li.menu-item-has-children:active:after';
						$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-' . $this->args['expand_method'] . '.dropdown-arrows-parent.direction-row > ul > li.menu-item-has-children:focus-within:after';
					}
					if ( 'hover' === $this->args['expand_method'] && false !== strpos( $this->args['arrows'], 'submenu' ) ) {
						$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-row > ul > li.menu-item-has-children:hover:after';
						$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-row > ul > li.menu-item-has-children.hover:after';
						$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-row > ul > li.menu-item-has-children:focus:after';
						$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-row > ul > li.menu-item-has-children:active:after';
						$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-row > ul > li.menu-item-has-children:focus-within:after';
					}
					$this->add_css_property( $selectors, 'height', 'calc(' . $this->args['submenu_space'] . ' - ' . $this->args['arrows_size_height'] . ' * 2)' );

					if ( false !== strpos( $this->args['arrows'], 'active' ) ) {
						$this->add_css_property(
							[
								$this->base_selector . '.active-item-arrows-on.direction-row:not(.collapse-enabled) > ul > li:hover:after',
								$this->base_selector . '.active-item-arrows-on.direction-row:not(.collapse-enabled) > ul > li.hover:after',
								$this->base_selector . '.active-item-arrows-on.direction-row:not(.collapse-enabled) > ul > li:focus:after',
								$this->base_selector . '.active-item-arrows-on.direction-row:not(.collapse-enabled) > ul > li:active:after',
								$this->base_selector . '.active-item-arrows-on.direction-row:not(.collapse-enabled) > ul > li:focus-within:after',
								$this->base_selector . '.active-item-arrows-on.direction-row:not(.collapse-enabled) > ul > li.current-menu-item:after',
								$this->base_selector . '.active-item-arrows-on.direction-row:not(.collapse-enabled) > ul > li.current-menu-ancestor:after',
								$this->base_selector . '.active-item-arrows-on.direction-row:not(.collapse-enabled) > ul > li.current-menu-parent:after',
								$this->base_selector . '.active-item-arrows-on.direction-row:not(.collapse-enabled) > ul > li.expanded:after',
							],
							'height',
							'calc(' . $this->args['submenu_space'] . ' - ' . $this->args['arrows_size_height'] . ' * 2)',
							true
						);
					}
				}

				// Arrow size width.
				if ( ! $this->is_default( 'arrows_size_width' ) ) {
					$selectors = [];
					if ( 'click' === $this->args['expand_method'] ) {
						if ( false !== strpos( $this->args['arrows'], 'main' ) ) {
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-click.dropdown-arrows-parent > ul > li.menu-item-has-children.expanded:after';
						}
						if ( false !== strpos( $this->args['arrows'], 'submenu' ) ) {
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-click.dropdown-arrows-child > ul > li.menu-item-has-children.expanded:after';
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-click > ul > li:not(.fusion-menu-item-button):after';
						}
					}
					if ( 'hover' === $this->args['expand_method'] ) {
						if ( false !== strpos( $this->args['arrows'], 'main' ) ) {
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent > ul > li.menu-item-has-children:hover:after';
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent > ul > li.menu-item-has-children.hover:after';
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent > ul > li.menu-item-has-children:focus:after';
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent > ul > li.menu-item-has-children:active:after';
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent > ul > li.menu-item-has-children:focus-within:after';
						}
						if ( false !== strpos( $this->args['arrows'], 'submenu' ) ) {
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child > ul > li.menu-item-has-children:hover:after';
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child > ul > li.menu-item-has-children.hover:after';
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child > ul > li.menu-item-has-children:focus:after';
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child > ul > li.menu-item-has-children:active:after';
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child > ul > li.menu-item-has-children:focus-within:after';
						}
						if ( false !== strpos( $this->args['arrows'], 'active' ) ) {
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover > ul > li:not(.fusion-menu-item-button):after';
						}
					}
					$this->add_css_property( $selectors, 'border-left-width', $this->args['arrows_size_width'] );
					$this->add_css_property( $selectors, 'border-right-width', $this->args['arrows_size_width'] );
				}

				// Arrow size height.
				if ( ! $this->is_default( 'arrows_size_height' ) ) {
					$selectors = [];
					if ( 'click' === $this->args['expand_method'] ) {
						if ( false !== strpos( $this->args['arrows'], 'main' ) ) {
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-click.dropdown-arrows-parent > ul > li.menu-item-has-children.expanded:after';
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-click.dropdown-arrows-parent > ul > li.menu-item-has-children.expanded:after';
						}
						if ( false !== strpos( $this->args['arrows'], 'submenu' ) ) {
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-click.dropdown-arrows-child > ul > li.menu-item-has-children.expanded:after';
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-click.dropdown-arrows-child > ul > li.menu-item-has-children.expanded:after';
						}
						if ( false !== strpos( $this->args['arrows'], 'active' ) ) {
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-click.active-item-arrows-on > ul > li:not(.fusion-menu-item-button):after';
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-click.active-item-arrows-on > ul > li:not(.fusion-menu-item-button):after';
						}
					}
					if ( 'hover' === $this->args['expand_method'] ) {
						if ( false !== strpos( $this->args['arrows'], 'main' ) ) {
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent > ul > li.menu-item-has-children:hover:after';
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent > ul > li.menu-item-has-children.hover:after';
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent > ul > li.menu-item-has-children:focus:after';
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent > ul > li.menu-item-has-children:active:after';
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent > ul > li.menu-item-has-children:focus-within:after';
						}
						if ( false !== strpos( $this->args['arrows'], 'submenu' ) ) {
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child > ul > li.menu-item-has-children:hover:after';
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child > ul > li.menu-item-has-children.hover:after';
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child > ul > li.menu-item-has-children:focus:after';
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child > ul > li.menu-item-has-children:active:after';
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child > ul > li.menu-item-has-children:focus-within:after';
						}
						if ( false !== strpos( $this->args['arrows'], 'active' ) ) {
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover > ul > li:not(.fusion-menu-item-button):after';
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).active-item-arrows-on.expand-method-hover > ul > li:not(.fusion-menu-item-button):after';
						}
					}
					$this->add_css_property( $selectors, 'border-top-width', $this->args['arrows_size_height'] );
					$this->add_css_property( $selectors, 'border-bottom-width', $this->args['arrows_size_height'] );

					$selectors = [];
					if ( 'click' === $this->args['expand_method'] ) {
						if ( false !== strpos( $this->args['arrows'], 'main' ) ) {
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-click.dropdown-arrows-parent.direction-column > ul > li:not(.fusion-menu-item-button):after';
						}
						if ( false !== strpos( $this->args['arrows'], 'submenu' ) ) {
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-click.dropdown-arrows-child.direction-column > ul > li:not(.fusion-menu-item-button):after';
						}
					}
					if ( 'hover' === $this->args['expand_method'] ) {
						if ( false !== strpos( $this->args['arrows'], 'main' ) ) {
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-right > ul > li:after';
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-left > ul > li:after';
						}
						if ( false !== strpos( $this->args['arrows'], 'submenu' ) ) {
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-column.expand-right > ul > li:after';
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-column.expand-left > ul > li:after';
						}
					}
					$this->add_css_property( $selectors, 'top', 'calc(50% - ' . $this->args['arrows_size_height'] . ')' );
				}

				// Submenu border radius.
				if ( 'flyout' !== $this->args['submenu_mode'] ) {
					if ( ! $this->is_default( 'submenu_border_radius_top_left' ) ) {
						$selectors = [
							$this->base_selector . ' .fusion-menu-element-list .sub-menu',
							$this->base_selector . ' .fusion-menu-element-list .sub-menu > li:first-child',
							$this->base_selector . ' .custom-menu-search-dropdown .fusion-menu-searchform-dropdown .fusion-search-form-content',
						];

						if ( $disable_megamenu ) {
							$selectors[] = $this->base_selector . ' .fusion-megamenu-wrapper';
						}

						$this->add_css_property( $selectors, 'border-top-left-radius', $this->args['submenu_border_radius_top_left'] );
					}
					if ( ! $this->is_default( 'submenu_border_radius_top_right' ) ) {
						$selectors = [
							$this->base_selector . ' .fusion-menu-element-list .sub-menu',
							$this->base_selector . ' .fusion-menu-element-list .sub-menu > li:first-child',
							$this->base_selector . ' .custom-menu-search-dropdown .fusion-menu-searchform-dropdown .fusion-search-form-content',
						];

						if ( $disable_megamenu ) {
							$selectors[] = $this->base_selector . ' .fusion-megamenu-wrapper';
						}

						$this->add_css_property( $selectors, 'border-top-right-radius', $this->args['submenu_border_radius_top_right'] );
					}
					if ( ! $this->is_default( 'submenu_border_radius_bottom_left' ) ) {
						$selectors = [
							$this->base_selector . ' .fusion-menu-element-list .sub-menu',
							$this->base_selector . ' .fusion-menu-element-list .sub-menu > li:last-child',
							$this->base_selector . ' .custom-menu-search-dropdown .fusion-menu-searchform-dropdown .fusion-search-form-content',
						];

						if ( $disable_megamenu ) {
							$selectors[] = $this->base_selector . ' .fusion-megamenu-wrapper';
						}

						$this->add_css_property( $selectors, 'border-bottom-left-radius', $this->args['submenu_border_radius_bottom_left'] );
					}
					if ( ! $this->is_default( 'submenu_border_radius_bottom_right' ) ) {
						$selectors = [
							$this->base_selector . ' .fusion-menu-element-list .sub-menu',
							$this->base_selector . ' .fusion-menu-element-list .sub-menu > li:last-child',
							$this->base_selector . ' .custom-menu-search-dropdown .fusion-menu-searchform-dropdown .fusion-search-form-content',
						];

						if ( $disable_megamenu ) {
							$selectors[] = $this->base_selector . ' .fusion-megamenu-wrapper';
						}

						$this->add_css_property( $selectors, 'border-bottom-right-radius', $this->args['submenu_border_radius_bottom_right'] );
					}
				}

				// Submenu font family.
				$selectors = [
					$this->base_selector . ' .fusion-menu-element-list .sub-menu > li',
					$this->base_selector . ' .fusion-menu-element-list .sub-menu li a',
				];

				$menu_styles = Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'submenu_typography', 'array' );

				foreach ( $menu_styles as $rule => $value ) {
					$this->add_css_property( $selectors, $rule, $value );
				}

				if ( ! $this->is_default( 'submenu_bg' ) ) {
					$selectors = [
						$this->base_selector . ' .sub-menu .fusion-menu-cart',
						$this->base_selector . ' .custom-menu-search-dropdown .fusion-menu-searchform-dropdown .fusion-search-form-content',
						$this->base_selector . ' .avada-menu-login-box .avada-custom-menu-item-contents',
					];

					if ( $disable_megamenu ) {
						$selectors[] = $this->base_selector . ' .fusion-megamenu-wrapper .fusion-megamenu-holder';
					}

					if ( 'flyout' === $this->args['submenu_mode'] ) {
						$selectors[] = $this->base_selector . '.submenu-mode-flyout .fusion-custom-menu .sub-menu';
						$selectors[] = $this->base_selector . '.submenu-mode-flyout .fusion-custom-menu .fusion-flyout-menu-backgrounds';

						if ( $disable_megamenu ) {
							$selectors[] = $this->base_selector . '.submenu-mode-flyout .fusion-custom-menu .fusion-megamenu-wrapper';
						}
					} else {
						$selectors[] = $this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:not(.fusion-menu-item-button)';

						if ( $disable_megamenu ) {
							$selectors[] = $this->base_selector . ' .fusion-megamenu-wrapper li .fusion-megamenu-title-disabled';
						}
					}

					$this->add_css_property( $selectors, 'background-color', $this->args['submenu_bg'] );

					if ( 'row' === $this->args['direction'] ) {
						if ( false !== strpos( $this->args['arrows'], 'submenu' ) ) {
							$selectors = [];
							if ( 'click' === $this->args['expand_method'] ) {
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-click.dropdown-arrows-child.direction-row > ul > li.menu-item-has-children.expanded:after';
							}
							if ( 'hover' === $this->args['expand_method'] ) {
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-row > ul > li.menu-item-has-children:hover:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-row > ul > li.menu-item-has-children.hover:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-row > ul > li.menu-item-has-children:focus:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-row > ul > li.menu-item-has-children:active:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-row > ul > li.menu-item-has-children:focus-within:after';
							}
							$this->add_css_property( $selectors, 'border-bottom-color', $this->args['submenu_bg'] );
						}
					}
					if ( 'column' === $this->args['direction'] ) {
						if ( false !== strpos( $this->args['arrows'], 'submenu' ) ) {
							$selectors = [];
							if ( 'click' === $this->args['expand_method'] ) {
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-click.dropdown-arrows-child.direction-column.expand-right > ul > li.menu-item-has-children.expanded:not(.fusion-megamenu-menu):after';
							}
							if ( 'hover' === $this->args['expand_method'] ) {
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-column.expand-right > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):hover:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-column.expand-right > ul > li.menu-item-has-children:not(.fusion-megamenu-menu).hover:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-column.expand-right > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):focus:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-column.expand-right > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):active:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-column.expand-right > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):focus-within:after';
							}
							$this->add_css_property( $selectors, 'border-right-color', $this->args['submenu_bg'] );
						}

						if ( false !== strpos( $this->args['arrows'], 'submenu' ) ) {
							$selectors = [];
							if ( 'click' === $this->args['expand_method'] ) {
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-click.dropdown-arrows-child.direction-column.expand-left > ul > li.menu-item-has-children.expanded:not(.fusion-megamenu-menu):after';
							}
							if ( 'hover' === $this->args['expand_method'] ) {
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-column.expand-left > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):hover:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-column.expand-left > ul > li.menu-item-has-children:not(.fusion-megamenu-menu).hover:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-column.expand-left > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):focus:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-column.expand-left > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):active:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-column.expand-left > ul > li.menu-item-has-children:not(.fusion-megamenu-menu):focus-within:after';
							}
							$this->add_css_property( $selectors, 'border-left-color', $this->args['submenu_bg'] );
						}
					}
				}

				if ( ! $this->is_default( 'submenu_color' ) ) {

					if ( $disable_megamenu ) {
						$selectors = [
							$this->base_selector . ' .fusion-megamenu-wrapper .fusion-megamenu-holder .fusion-megamenu .fusion-megamenu-submenu .fusion-megamenu-title a',
							$this->base_selector . ' .fusion-megamenu-wrapper .fusion-megamenu-holder .fusion-megamenu .fusion-megamenu-submenu .fusion-megamenu-icon',
							$this->base_selector . ' .fusion-megamenu-wrapper .fusion-megamenu-holder .fusion-megamenu .fusion-megamenu-submenu .fusion-megamenu-widgets-container .widget_text .textwidget',
							$this->base_selector . ' .fusion-megamenu-wrapper li .fusion-megamenu-title-disabled',
						];
					}

					// In hover mode color is inherited from parent anchor.
					if ( 'click' === $this->args['expand_method'] ) {
						$selectors[] = $this->base_selector . ' ul ul .fusion-open-nav-submenu';
					}

					$this->add_css_property( $selectors, 'color', $this->args['submenu_color'] );

					// Important ones.
					$this->add_css_property( $this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:not(.fusion-menu-item-button) > a', 'color', $this->args['submenu_color'], true );
					if ( 'click' === $this->args['expand_method'] ) {
						$this->add_css_property( $this->base_selector . '.expand-method-click li .sub-menu .fusion-open-nav-submenu', 'color', $this->args['submenu_color'], true );
					}
				}

				if ( class_exists( 'WooCommerce' ) ) {
					$this->add_css_property(
						[
							$this->base_selector . ' .fusion-menu-cart-checkout a:before',
							$this->base_selector . ' .fusion-menu-cart-items a',
							$this->base_selector . ' ul .fusion-menu-login-box-register',
							$this->base_selector . ' ul .fusion-menu-cart-checkout a:before',
							$this->base_selector . ' .fusion-menu-cart-items a',
						],
						'color',
						$this->args['submenu_color']
					);
				}

				if ( ! $this->is_default( 'submenu_active_bg' ) ) {
					$selectors = [
						$this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu):not(.fusion-menu-searchform-dropdown) > li:not(.fusion-menu-item-button):hover',
						$this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu):not(.fusion-menu-searchform-dropdown) > li:not(.fusion-menu-item-button).hover',
						$this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu):not(.fusion-menu-searchform-dropdown) > li:not(.fusion-menu-item-button):focus',
						$this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu):not(.fusion-menu-searchform-dropdown) > li:not(.fusion-menu-item-button):focus-within',
						$this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu):not(.fusion-menu-searchform-dropdown) > li:not(.fusion-menu-item-button).expanded',
						$this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li.current-menu-item:not(.fusion-menu-item-button)',
						$this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li.current-menu-parent:not(.fusion-menu-item-button)',
						$this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li.current-menu-ancestor:not(.fusion-menu-item-button)',
						$this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li.current_page_item:not(.fusion-menu-item-button)',
					];

					if ( $disable_megamenu ) {
						$selectors[] = $this->base_selector . ' .fusion-megamenu-wrapper .fusion-megamenu-submenu > a:hover';
						$selectors[] = $this->base_selector . ' .fusion-megamenu-wrapper .fusion-megamenu-submenu > a.hover';
						$selectors[] = $this->base_selector . ' .fusion-megamenu-wrapper .fusion-megamenu-submenu > a:focus';
						$selectors[] = $this->base_selector . ' .fusion-megamenu-wrapper .fusion-megamenu-submenu > a:active';
						$selectors[] = $this->base_selector . ' .fusion-megamenu-wrapper .fusion-megamenu-submenu > a:focus-within';
						$selectors[] = $this->base_selector . ' .fusion-megamenu-wrapper .fusion-megamenu-submenu > a:hover > .fusion-open-nav-submenu';
						$selectors[] = $this->base_selector . ' .fusion-megamenu-wrapper .fusion-megamenu-submenu > a.hover > .fusion-open-nav-submenu';
						$selectors[] = $this->base_selector . ' .fusion-megamenu-wrapper .fusion-megamenu-submenu > a:focus > .fusion-open-nav-submenu';
						$selectors[] = $this->base_selector . ' .fusion-megamenu-wrapper .fusion-megamenu-submenu > a:active > .fusion-open-nav-submenu';
						$selectors[] = $this->base_selector . ' .fusion-megamenu-wrapper .fusion-megamenu-submenu > a:focus-within > .fusion-open-nav-submenu';
						$selectors[] = $this->base_selector . '.submenu-mode-dropdown li ul.fusion-megamenu li.menu-item-has-children .sub-menu li.menu-item-has-children:focus-within .fusion-open-nav-submenu';
						$selectors[] = $this->base_selector . '.submenu-mode-dropdown li ul.fusion-megamenu li.menu-item-has-children .sub-menu li.menu-item-has-children .fusion-background-highlight:hover .fusion-open-nav-submenu';
						$selectors[] = $this->base_selector . '.submenu-mode-dropdown li ul.fusion-megamenu li.menu-item-has-children .sub-menu li.menu-item-has-children:focus-within > .fusion-background-highlight';
						$selectors[] = $this->base_selector . '.submenu-mode-dropdown li ul.fusion-megamenu li.menu-item-has-children .sub-menu li.menu-item-has-children .fusion-background-highlight:hover';
					}
					$this->add_css_property( $selectors, 'background-color', $this->args['submenu_active_bg'] );

					if ( 'column' === $this->args['direction'] ) {
						$selectors = [];
						if ( 'click' === $this->args['expand_method'] && false !== strpos( $this->args['arrows'], 'submenu' ) ) {
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-click.dropdown-arrows-child.direction-column.expand-right > ul > li.menu-item-has-children.expanded:not(.fusion-megamenu-menu).alt-arrow-child-color:after';
						}
						if ( 'hover' === $this->args['expand_method'] && false !== strpos( $this->args['arrows'], 'main' ) ) {
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-right > ul > li.menu-item-has-children:not(.fusion-megamenu-menu).alt-arrow-child-color:hover:after';
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-right > ul > li.menu-item-has-children:not(.fusion-megamenu-menu).alt-arrow-child-color.hover:after';
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-right > ul > li.menu-item-has-children:not(.fusion-megamenu-menu).alt-arrow-child-color:focus:after';
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-right > ul > li.menu-item-has-children:not(.fusion-megamenu-menu).alt-arrow-child-color:active:after';
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-right > ul > li.menu-item-has-children:not(.fusion-megamenu-menu).alt-arrow-child-color:focus-within:after';
						}
						$this->add_css_property( $selectors, 'border-right-color', $this->args['submenu_active_bg'] );

						$selectors = [];
						if ( 'click' === $this->args['expand_method'] && false !== strpos( $this->args['arrows'], 'submenu' ) ) {
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-click.dropdown-arrows-child.direction-column.expand-left > ul > li.menu-item-has-children.expanded:not(.fusion-megamenu-menu).alt-arrow-child-color:after';
						}
						if ( 'hover' === $this->args['expand_method'] && false !== strpos( $this->args['arrows'], 'main' ) ) {
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-left > ul > li.menu-item-has-children:not(.fusion-megamenu-menu).alt-arrow-child-color:hover:after';
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-left > ul > li.menu-item-has-children:not(.fusion-megamenu-menu).alt-arrow-child-color.hover:after';
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-left > ul > li.menu-item-has-children:not(.fusion-megamenu-menu).alt-arrow-child-color:focus:after';
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-left > ul > li.menu-item-has-children:not(.fusion-megamenu-menu).alt-arrow-child-color:active:after';
							$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-parent.direction-column.expand-left > ul > li.menu-item-has-children:not(.fusion-megamenu-menu).alt-arrow-child-color:focus-within:after';
						}
						$this->add_css_property( $selectors, 'border-left-color', $this->args['submenu_active_bg'] );
					}

					if ( 'row' === $this->args['direction'] ) {
						if ( false !== strpos( $this->args['arrows'], 'submenu' ) ) {
							$selectors = [];
							if ( 'click' === $this->args['expand_method'] ) {
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-click.dropdown-arrows-child.direction-row > ul > li.menu-item-has-children.expanded.alt-arrow-child-color:after';
							}
							if ( 'hover' === $this->args['expand_method'] ) {
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-row > ul > li.menu-item-has-children.alt-arrow-child-color:hover:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-row > ul > li.menu-item-has-children.alt-arrow-child-color.hover:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-row > ul > li.menu-item-has-children.alt-arrow-child-color:focus:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-row > ul > li.menu-item-has-children.alt-arrow-child-color:active:after';
								$selectors[] = $this->base_selector . ':not(.collapse-enabled).expand-method-hover.dropdown-arrows-child.direction-row > ul > li.menu-item-has-children.alt-arrow-child-color:focus-within:after';
							}
							$this->add_css_property( $selectors, 'border-bottom-color', $this->args['submenu_active_bg'] );
						}
					}
				}

				if ( ! $this->is_default( 'submenu_active_color' ) ) {
					// Important ones.
					$selectors = [
						$this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:hover > a',
						$this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li.hover > a',
						$this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:focus > a',
						$this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:focus-within > a',
						$this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:not(.fusion-menu-item-button):hover > a',
						$this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li.hover:not(.fusion-menu-item-button) > a',
						$this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:not(.fusion-menu-item-button):focus > a',
						$this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:not(.fusion-menu-item-button):focus-within > a',
						$this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li.expanded > a',
						$this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:not(.fusion-menu-item-button).current-menu-item > a',
						$this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:not(.fusion-menu-item-button).current-menu-ancestor > a',
						$this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:not(.fusion-menu-item-button).current-menu-parent > a',
						$this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:hover > a .fusion-button',
						$this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li.hover > a .fusion-button',
						$this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:focus > a .fusion-button',
						$this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:focus-within > a .fusion-button',
						$this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li.expanded > a .fusion-button',
						$this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li.current-menu-item > a .fusion-button',
						$this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li.current-menu-ancestor > a .fusion-button',
						$this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li.current-menu-parent > a .fusion-button',
						$this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:hover > .fusion-open-nav-submenu',
						$this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li.hover > .fusion-open-nav-submenu',
						$this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:focus > .fusion-open-nav-submenu',
						$this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:focus-within > .fusion-open-nav-submenu',
						$this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li.expanded > .fusion-open-nav-submenu',
						$this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li.current-menu-item > .fusion-open-nav-submenu',
						$this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li.current-menu-ancestor > .fusion-open-nav-submenu',
						$this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li.current-menu-parent > .fusion-open-nav-submenu',
					];

					if ( $disable_megamenu ) {
						$selectors[] = $this->base_selector . ' .fusion-megamenu-wrapper .fusion-megamenu-submenu > a:hover';
						$selectors[] = $this->base_selector . ' .fusion-megamenu-wrapper .fusion-megamenu-submenu > a.hover';
						$selectors[] = $this->base_selector . ' .fusion-megamenu-wrapper .fusion-megamenu-submenu > a:focus';
						$selectors[] = $this->base_selector . ' .fusion-megamenu-wrapper .fusion-megamenu-submenu > a:active';
						$selectors[] = $this->base_selector . ' .fusion-megamenu-wrapper .fusion-megamenu-submenu > a:focus-within';
						$selectors[] = $this->base_selector . ' .fusion-megamenu-wrapper .fusion-megamenu-submenu > a:hover > .fusion-open-nav-submenu';
						$selectors[] = $this->base_selector . ' .fusion-megamenu-wrapper .fusion-megamenu-submenu > a.hover > .fusion-open-nav-submenu';
						$selectors[] = $this->base_selector . ' .fusion-megamenu-wrapper .fusion-megamenu-submenu > a:focus > .fusion-open-nav-submenu';
						$selectors[] = $this->base_selector . ' .fusion-megamenu-wrapper .fusion-megamenu-submenu > a:active > .fusion-open-nav-submenu';
						$selectors[] = $this->base_selector . ' .fusion-megamenu-wrapper .fusion-megamenu-submenu > a:focus-within > .fusion-open-nav-submenu';
						$selectors[] = $this->base_selector . '.submenu-mode-dropdown li ul.fusion-megamenu li.menu-item-has-children .sub-menu li.menu-item-has-children:focus-within .fusion-open-nav-submenu';
						$selectors[] = $this->base_selector . ' li ul.fusion-megamenu li.menu-item-has-children .sub-menu li.menu-item-has-children .fusion-background-highlight:hover .fusion-open-nav-submenu';
						$selectors[] = $this->base_selector . ' li ul.fusion-megamenu li.menu-item-has-children .sub-menu li.menu-item-has-children:focus-within > .fusion-background-highlight';
						$selectors[] = $this->base_selector . ' li ul.fusion-megamenu li.menu-item-has-children .sub-menu li.menu-item-has-children .fusion-background-highlight:hover';
					}

					if ( class_exists( 'WooCommerce' ) ) {
						$selectors[] = $this->base_selector . ' .fusion-menu-cart-checkout:hover .fusion-menu-cart-link a';
						$selectors[] = $this->base_selector . ' .fusion-menu-cart-checkout:hover .fusion-menu-cart-checkout-link a';
						$selectors[] = $this->base_selector . ' .fusion-menu-cart-checkout:hover .fusion-menu-cart-link a:before';
						$selectors[] = $this->base_selector . ' .fusion-menu-cart-checkout:hover .fusion-menu-cart-checkout-link a:before';
						$selectors[] = $this->base_selector . ' .fusion-menu-cart-checkout:focus .fusion-menu-cart-link a:before';
						$selectors[] = $this->base_selector . ' .fusion-menu-cart-checkout:focus .fusion-menu-cart-checkout-link a:before';
					}

					$this->add_css_property( $selectors, 'color', $this->args['submenu_active_color'], true );
				}

				if ( 'flyout' === $this->args['submenu_mode'] ) {

					$selectors = [
						$this->base_selector . '.submenu-mode-flyout .fusion-close-flyout:before',
						$this->base_selector . '.submenu-mode-flyout .fusion-close-flyout:after',
					];
					$this->add_css_property( $selectors, 'background-color', $this->args['flyout_close_color'] );

					$selectors = [
						$this->base_selector . '.submenu-mode-flyout .fusion-close-flyout:hover:before',
						$this->base_selector . '.submenu-mode-flyout .fusion-close-flyout:hover:after',
					];
					$this->add_css_property( $selectors, 'background-color', $this->args['flyout_active_close_color'], true );
				}

				if ( ! $this->is_default( 'submenu_max_width' ) && 'dropdown' === $this->args['submenu_mode'] ) {
					$this->add_css_property( [ $this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list ul:not(.fusion-megamenu) > li' ], 'width', $this->args['submenu_max_width'], true );

					// Don't set min width if sub menu width is explictly set.
					$this->add_css_property( [ $this->base_selector . '.direction-row:not(.collapse-enabled) .sub-menu' ], 'min-width', '0' );
				}

				if ( ! $this->is_default( 'submenu_items_padding_top' ) ) {
					$selectors = [
						$this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:not(.fusion-menu-item-button) > a',
						$this->base_selector . ' .sub-menu .fusion-menu-cart a',
						$this->base_selector . ' .custom-menu-search-dropdown .fusion-menu-searchform-dropdown .fusion-search-form-content',
						$this->base_selector . ' .avada-menu-login-box .avada-custom-menu-item-contents form',
					];
					if ( $disable_megamenu ) {
						$selectors[] = $this->base_selector . ' .fusion-megamenu-wrapper li .fusion-megamenu-title-disabled';
						$selectors[] = $this->base_selector . ' .fusion-megamenu-wrapper .fusion-megamenu-submenu .sub-menu a';
					}
					if ( 'click' === $this->args['expand_method'] ) {
						$selectors[] = $this->base_selector . '.expand-method-click li ul .fusion-open-nav-submenu';
					}
					$this->add_css_property( $selectors, 'padding-top', $this->args['submenu_items_padding_top'] );

					if ( 'column' === $this->args['direction'] ) {
						$this->add_css_property( $this->base_selector . '.direction-column .fusion-menu-element-list ul', 'top', 'calc(0.5em - ' . $this->args['submenu_items_padding_top'] . ')' );
					}
				}
				if ( ! $this->is_default( 'submenu_items_padding_right' ) ) {
					$selectors = [
						$this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:not(.fusion-menu-item-button) > a',
						$this->base_selector . ' .sub-menu .fusion-menu-cart a',
						$this->base_selector . ' .custom-menu-search-dropdown .fusion-menu-searchform-dropdown .fusion-search-form-content',
						$this->base_selector . ' ul ul .fusion-open-nav-submenu:before',
						$this->base_selector . ' .avada-menu-login-box .avada-custom-menu-item-contents form',
						$this->base_selector . ' .avada-menu-login-box .avada-custom-menu-item-contents .fusion-menu-login-box-register',
					];
					if ( $disable_megamenu ) {
						$selectors[] = $this->base_selector . ' .fusion-megamenu-wrapper li .fusion-megamenu-title-disabled';
						$selectors[] = $this->base_selector . ' .fusion-megamenu-wrapper .fusion-megamenu-submenu .sub-menu a';
					}
					if ( 'never' !== $this->args['breakpoint'] && $disable_megamenu ) {
						$selectors[] = $this->base_selector . '.collapse-enabled .fusion-megamenu-holder';
					}

					if ( 'column' === $this->args['direction'] && ! is_rtl() && 'click' === $this->args['expand_method'] ) {
						$selectors[] = '.ltr' . $this->base_selector . '.direction-column.expand-method-click.expand-left .menu-item-has-children li a';
					}
					if ( class_exists( 'WooCommerce' ) ) {
						$selectors[] = $this->base_selector . ' .fusion-menu-cart-checkout';
					}
					if ( 'flyout' === $this->args['submenu_mode'] ) {
						$selectors[] = '.ltr' . $this->base_selector . '.submenu-mode-flyout:not(.collapse-enabled) .sub-menu li:not(.fusion-menu-item-button) > a';
					}
					$this->add_css_property( $selectors, 'padding-right', $this->args['submenu_items_padding_right'] );

					if ( class_exists( 'WooCommerce' ) ) {
						$this->add_css_property( '.rtl' . $this->base_selector . ' .fusion-menu-cart-link', 'padding-right', '0' );
						$this->add_css_property( '.ltr' . $this->base_selector . ' .fusion-menu-cart-checkout-link', 'padding-right', '0' );
					}
				}
				if ( ! $this->is_default( 'submenu_items_padding_bottom' ) ) {
					$selectors = [
						$this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:not(.fusion-menu-item-button) > a',
						$this->base_selector . ' .sub-menu .fusion-menu-cart a',
						$this->base_selector . ' .custom-menu-search-dropdown .fusion-menu-searchform-dropdown .fusion-search-form-content',
						$this->base_selector . ' .avada-menu-login-box .avada-custom-menu-item-contents .fusion-menu-login-box-register',
					];
					if ( $disable_megamenu ) {
						$selectors[] = $this->base_selector . ' .fusion-megamenu-wrapper li .fusion-megamenu-title-disabled';
						$selectors[] = $this->base_selector . ' .fusion-megamenu-wrapper .fusion-megamenu-submenu .sub-menu a';
					}
					if ( 'click' === $this->args['expand_method'] ) {
						$selectors[] = $this->base_selector . '.expand-method-click li ul .fusion-open-nav-submenu';
					}
					$this->add_css_property( $selectors, 'padding-bottom', $this->args['submenu_items_padding_bottom'] );
				}
				if ( ! $this->is_default( 'submenu_items_padding_left' ) ) {
					$selectors = [
						$this->base_selector . ' .fusion-menu-element-list ul:not(.fusion-megamenu) > li:not(.fusion-menu-item-button) > a',
						$this->base_selector . ' .sub-menu .fusion-menu-cart a',
						$this->base_selector . ' .custom-menu-search-dropdown .fusion-menu-searchform-dropdown .fusion-search-form-content',
						$this->base_selector . ' ul ul .fusion-open-nav-submenu:before',
						$this->base_selector . ' .avada-menu-login-box .avada-custom-menu-item-contents form',
						$this->base_selector . ' .avada-menu-login-box .avada-custom-menu-item-contents .fusion-menu-login-box-register',
					];
					if ( $disable_megamenu ) {
						$selectors[] = $this->base_selector . ' .fusion-megamenu-wrapper li .fusion-megamenu-title-disabled';
						$selectors[] = $this->base_selector . ' .fusion-megamenu-wrapper .fusion-megamenu-submenu .sub-menu a';
					}
					if ( 'never' !== $this->args['breakpoint'] && $disable_megamenu ) {
						$selectors[] = $this->base_selector . '.collapse-enabled .fusion-megamenu-holder';
					}
					if ( 'column' === $this->args['direction'] && is_rtl() && 'click' === $this->args['expand_method'] ) {
						$selectors[] = '.rtl' . $this->base_selector . '.direction-column.expand-method-click.expand-right .menu-item-has-children li a';
					}
					if ( class_exists( 'WooCommerce' ) ) {
						$selectors[] = $this->base_selector . ' .fusion-menu-cart-checkout';
					}
					if ( 'flyout' === $this->args['submenu_mode'] ) {
						$selectors[] = '.rtl' . $this->base_selector . '.submenu-mode-flyout:not(.collapse-enabled) .sub-menu li:not(.fusion-menu-item-button) > a';
					}
					$this->add_css_property( $selectors, 'padding-left', $this->args['submenu_items_padding_left'] );

					if ( class_exists( 'WooCommerce' ) ) {
						$this->add_css_property( '.rtl' . $this->base_selector . ' .fusion-menu-cart-checkout-link', 'padding-left', '0' );
						$this->add_css_property( '.ltr' . $this->base_selector . ' .fusion-menu-cart-link', 'padding-left', '0' );
					}
				}

				// Combined left and right padding.
				if ( ( ! $this->is_default( 'submenu_items_padding_left' ) || ! $this->is_default( 'submenu_items_padding_right' ) ) && 'click' === $this->args['expand_method'] ) {
					$this->add_css_property( $this->base_selector . '.expand-method-click li ul .fusion-open-nav-submenu', 'width', 'calc(1em + ' . $this->args['submenu_items_padding_left'] . ' / 2 + ' . $this->args['submenu_items_padding_right'] . ' / 2)' );
				}

				// Submenu separator color.
				if ( ! $this->is_default( 'submenu_sep_color' ) ) {
					$this->add_css_property( [ $this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list ul:not(.fusion-megamenu) > li' ], 'border-bottom-color', $this->args['submenu_sep_color'] );
					if ( $disable_megamenu ) {
						$this->add_css_property(
							[
								$this->base_selector . ' .fusion-megamenu-wrapper .fusion-megamenu-holder .fusion-megamenu .fusion-megamenu-submenu',
								$this->base_selector . ' .fusion-megamenu-wrapper .fusion-megamenu-submenu .fusion-megamenu-border',
								$this->base_selector . ' .fusion-megamenu-wrapper li .fusion-megamenu-title-disabled',
							],
							'border-color',
							$this->args['submenu_sep_color']
						);
					}
				}

				// Submenu font size.
				if ( ! $this->is_default( 'submenu_font_size' ) ) {
					$selectors = [
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list ul:not(.fusion-megamenu) a',
					];
					if ( $disable_megamenu ) {
						$selectors[] = $this->base_selector . ':not(.collapse-enabled) .fusion-megamenu-wrapper li .fusion-megamenu-title-disabled';
						$selectors[] = $this->base_selector . ':not(.collapse-enabled) .fusion-megamenu-wrapper .fusion-megamenu-submenu > a';
						$selectors[] = $this->base_selector . ':not(.collapse-enabled) .fusion-megamenu-wrapper .fusion-megamenu-submenu > a.hover';
						$selectors[] = $this->base_selector . ':not(.collapse-enabled) .fusion-megamenu-wrapper .fusion-megamenu-submenu > a:hover';
						$selectors[] = $this->base_selector . ':not(.collapse-enabled) .fusion-megamenu-wrapper .fusion-megamenu-submenu > a:focus';
						$selectors[] = $this->base_selector . ':not(.collapse-enabled) .fusion-megamenu-wrapper .fusion-megamenu-submenu > a:active';
						$selectors[] = $this->base_selector . ':not(.collapse-enabled) .fusion-megamenu-wrapper .fusion-megamenu-submenu > a:focus-within';
						$selectors[] = $this->base_selector . ':not(.collapse-enabled) .fusion-megamenu-wrapper .fusion-megamenu-holder .fusion-megamenu .fusion-megamenu-submenu';
					}
					$this->add_css_property( $selectors, 'font-size', $this->args['submenu_font_size'] );

					if ( 'flyout' === $this->args['submenu_mode'] ) {
						$selectors = [
							$this->base_selector . '.submenu-mode-flyout .fusion-close-flyout',
						];
						$this->add_css_property( $selectors, 'width', $this->args['submenu_font_size'] );
						$this->add_css_property( $selectors, 'height', $this->args['submenu_font_size'] );
					}
				}

				if ( ! $this->is_default( 'mobile_nav_button_align_hor' ) ) {
					$selectors = [];
					if ( 'on' === $this->args['mobile_nav_trigger_fullwidth'] ) {
						$selectors[] = $this->base_selector . '.mobile-trigger-fullwidth-on > .avada-menu-mobile-menu-trigger > .inner';
					}
					if ( 'never' !== $this->args['breakpoint'] ) {
						$selectors[] = $this->base_selector . '.collapse-enabled';
					}
					$this->add_css_property( $selectors, 'justify-content', $this->args['mobile_nav_button_align_hor'] );
				}

				if ( ! $this->is_default( 'mobile_nav_trigger_bottom_margin' ) && 'never' !== $this->args['breakpoint'] ) {
					$this->add_css_property(
						$this->base_selector . '.collapse-enabled .fusion-menu-element-list',
						'margin-top',
						fusion_library()->sanitize->get_value_with_unit( $this->args['mobile_nav_trigger_bottom_margin'] )
					);
				}

				// Text transform.
				if ( ! $this->is_default( 'submenu_text_transform' ) ) {
					$this->add_css_property( $this->base_selector . ' .fusion-menu-element-list ul', 'text-transform', $this->args['submenu_text_transform'] );
				}

				// Line height.
				if ( ! $this->is_default( 'submenu_line_height' ) ) {
					$this->add_css_property( $this->base_selector . ' .fusion-menu-element-list ul', 'line-height', $this->args['submenu_line_height'] );
				}

				// Letter spacing.
				if ( ! $this->is_default( 'submenu_letter_spacing' ) ) {
					$this->add_css_property( $this->base_selector . ' .fusion-menu-element-list ul', 'letter-spacing', fusion_library()->sanitize->get_value_with_unit( $this->args['submenu_letter_spacing'] ) );
				}

				if ( ! $this->is_default( 'icons_size' ) ) {
					$selectors   = [
						$this->base_selector . ':not(.collapse-enabled) li.menu-item > a.fusion-menu-icon-search',
					];
					$selectors[] = $this->base_selector . ':not(.collapse-enabled) li.menu-item > .fusion-megamenu-icon';
					$selectors[] = $this->base_selector . ':not(.collapse-enabled) li.menu-item > a > .fusion-megamenu-icon';

					$this->add_css_property( $selectors, 'font-size', fusion_library()->sanitize->get_value_with_unit( $this->args['icons_size'] ) );
				}

				if ( ! $this->is_default( 'icons_color' ) ) {
					$this->add_css_property(
						[
							$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list > li.menu-item > .fusion-megamenu-icon',
							$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list > li.menu-item > a > .fusion-megamenu-icon',
						],
						'color',
						$this->args['icons_color']
					);

					$this->add_css_property(
						[
							$this->base_selector . ' .custom-menu-search-dropdown .fusion-main-menu-icon',
							$this->base_selector . ' .custom-menu-search-overlay .fusion-menu-icon-search.trigger-overlay',
							$this->base_selector . ' .custom-menu-search-overlay ~ .fusion-overlay-search',
						],
						'color',
						$this->args['icons_color'],
						true
					);
				}

				$this->add_css_property(
					[
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list > li.menu-item:hover > a .fusion-megamenu-icon',
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list > li.menu-item.hover > a .fusion-megamenu-icon',
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list > li.menu-item:focus > a .fusion-megamenu-icon',
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list > li.menu-item:active > a .fusion-megamenu-icon',
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list > li.menu-item:focus-within > a .fusion-megamenu-icon',
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list > li.menu-item.current-menu-item > a .fusion-megamenu-icon',
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list > li.menu-item.current-menu-ancestor > a .fusion-megamenu-icon',
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list > li:not(.awb-flyout-top-level-no-link).menu-item.current-menu-parent > a .fusion-megamenu-icon',
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list > li.menu-item.expanded > a .fusion-megamenu-icon',
					],
					'color',
					$this->args['icons_hover_color']
				);

				$this->add_css_property(
					[
						$this->base_selector . ' .custom-menu-search-dropdown:hover .fusion-main-menu-icon',
						$this->base_selector . ' .custom-menu-search-overlay:hover .fusion-menu-icon-search.trigger-overlay',
						$this->base_selector . ' .custom-menu-search-overlay:hover ~ .fusion-overlay-search',
					],
					'color',
					$this->args['icons_hover_color'],
					true
				);

				// Thumbnail size.
				if ( ! $this->is_default( 'thumbnail_size_width' ) && $disable_megamenu ) {
					$this->add_css_property( $this->base_selector . ':not(.collapse-enabled) .fusion-megamenu-title .fusion-megamenu-image > img', 'width', $this->args['thumbnail_size_width'] );
					$this->add_css_property( $this->base_selector . ':not(.collapse-enabled) .fusion-megamenu-title .fusion-megamenu-thumbnail > img', 'width', $this->args['thumbnail_size_width'] );
				}
				if ( ! $this->is_default( 'thumbnail_size_height' ) && $disable_megamenu ) {
					$this->add_css_property( $this->base_selector . ':not(.collapse-enabled) .fusion-megamenu-title .fusion-megamenu-image > img', 'height', $this->args['thumbnail_size_height'] );
					$this->add_css_property( $this->base_selector . ':not(.collapse-enabled) .fusion-megamenu-title .fusion-megamenu-thumbnail > img', 'height', $this->args['thumbnail_size_width'] );
				}

				// Mega menu title justification.
				if ( ! $this->is_default( 'justify_title' ) && $disable_megamenu ) {
					$this->add_css_property(
						$this->base_selector . ' .fusion-megamenu-wrapper .fusion-megamenu-holder .fusion-megamenu .fusion-megamenu-submenu .fusion-megamenu-title .awb-justify-title',
						'justify-content',
						$this->args['justify_title']
					);
				}

				if ( ! $this->is_default( 'mobile_nav_button_align_hor' ) ) {
					$selectors = [];
					if ( 'on' === $this->args['mobile_nav_trigger_fullwidth'] ) {
						$selectors[] = $this->base_selector . '.mobile-trigger-fullwidth-on > .avada-menu-mobile-menu-trigger > .inner';
					}
					if ( 'never' !== $this->args['breakpoint'] ) {
						$selectors[] = $this->base_selector . '.collapse-enabled';
					}
					$this->add_css_property( $selectors, 'justify-content', $this->args['mobile_nav_button_align_hor'] );
				}

				// Mobile icon size.
				if ( ! $this->is_default( 'mobile_trigger_font_size' ) ) {
					$this->add_css_property(
						$this->base_selector . ' > .avada-menu-mobile-menu-trigger',
						'font-size',
						$this->args['mobile_trigger_font_size']
					);
				}

				// Mobile.
				if ( 'never' !== $this->args['breakpoint'] ) {

					// Mobile background.
					if ( ! $this->is_default( 'mobile_bg' ) ) {
						$selectors = [
							$this->base_selector . '.collapse-enabled ul li > a',
							$this->base_selector . '.collapse-enabled ul li:hover .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded) a',
							$this->base_selector . '.collapse-enabled ul li.hover .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded) a',
							$this->base_selector . '.collapse-enabled ul li:focus .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded) a',
							$this->base_selector . '.collapse-enabled ul li:active .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded) a',
							$this->base_selector . '.collapse-enabled ul li:focus-within .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded) a',
							$this->base_selector . '.collapse-enabled ul li.current-menu-item .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded) a',
							$this->base_selector . '.collapse-enabled ul li.current-menu-ancestor .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded) a',
							$this->base_selector . '.collapse-enabled ul li.current-menu-parent .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded) a',
							$this->base_selector . '.collapse-enabled ul li.expanded .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded) a',
							$this->base_selector . '.collapse-enabled ul li.custom-menu-search-inline',
							$this->base_selector . '.collapse-enabled ul .fusion-menu-form-inline',
							$this->base_selector . '.collapse-enabled ul li.fusion-menu-item-button',
							$this->base_selector . '.collapse-enabled ul',
						];

						if ( $disable_megamenu ) {
							$selectors[] = $this->base_selector . '.collapse-enabled .fusion-megamenu-menu .fusion-megamenu-wrapper .fusion-megamenu-holder ul li';
						}

						$this->add_css_property( $selectors, 'background', $this->args['mobile_bg'], true );
					}

					// Mobile color.
					if ( ! $this->is_default( 'mobile_color' ) ) {
						$selectors = [
							$this->base_selector . '.collapse-enabled ul li > a',
							$this->base_selector . '.collapse-enabled ul li > a .fusion-button',
							$this->base_selector . '.collapse-enabled ul li > .fusion-open-nav-submenu-on-click:before',
							$this->base_selector . '.collapse-enabled ul li:hover .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded) a',
							$this->base_selector . '.collapse-enabled ul li.hover .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded) a',
							$this->base_selector . '.collapse-enabled ul li:focus .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded) a',
							$this->base_selector . '.collapse-enabled ul li:active .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded) a',
							$this->base_selector . '.collapse-enabled ul li:focus-within .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded) a',
							$this->base_selector . '.collapse-enabled ul li.current-menu-item .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded) a',
							$this->base_selector . '.collapse-enabled ul li.current-menu-ancestor .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded) a',
							$this->base_selector . '.collapse-enabled ul li.current-menu-parent .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded) a',
							$this->base_selector . '.collapse-enabled ul li.expanded .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded) a',
						];

						if ( $disable_megamenu ) {
							$selectors[] = $this->base_selector . '.collapse-enabled .fusion-megamenu-menu .fusion-megamenu-wrapper .fusion-megamenu-holder .fusion-megamenu .fusion-megamenu-submenu .fusion-megamenu-title a';
							$selectors[] = $this->base_selector . '.collapse-enabled .fusion-megamenu-menu .fusion-megamenu-wrapper .fusion-megamenu-holder ul li a';
							$selectors[] = $this->base_selector . '.collapse-enabled .fusion-megamenu-menu .fusion-megamenu-wrapper .fusion-megamenu-holder ul li .awb-justify-title';
						}

						$this->add_css_property( $selectors, 'color', $this->args['mobile_color'], true );
					}

					// Mobile active background.
					if ( ! $this->is_default( 'mobile_active_bg' ) ) {
						$selectors = [
							$this->base_selector . '.collapse-enabled ul li:hover > a',
							$this->base_selector . '.collapse-enabled ul li.hover > a',
							$this->base_selector . '.collapse-enabled ul li:focus > a',
							$this->base_selector . '.collapse-enabled ul li:active > a',
							$this->base_selector . '.collapse-enabled ul li:focus-within > a',
							$this->base_selector . '.collapse-enabled ul li.current-menu-item > a',
							$this->base_selector . '.collapse-enabled ul li.current-menu-ancestor > a',
							$this->base_selector . '.collapse-enabled ul li.current-menu-parent > a',
							$this->base_selector . '.collapse-enabled ul li.expanded > a',
							$this->base_selector . '.collapse-enabled ul li.fusion-menu-item-button:hover',
							$this->base_selector . '.collapse-enabled ul li.fusion-menu-item-button.hover',
							$this->base_selector . '.collapse-enabled ul li.fusion-menu-item-button:focus',
							$this->base_selector . '.collapse-enabled ul li.fusion-menu-item-button:active',
							$this->base_selector . '.collapse-enabled ul li.fusion-menu-item-button:focus-within',
							$this->base_selector . '.collapse-enabled ul li.fusion-menu-item-button.current-menu-item',
							$this->base_selector . '.collapse-enabled ul li.fusion-menu-item-button.current-menu-ancestor',
							$this->base_selector . '.collapse-enabled ul li.fusion-menu-item-button.current-menu-parent',
							$this->base_selector . '.collapse-enabled ul li.fusion-menu-item-button.expanded',
							$this->base_selector . '.collapse-enabled ul li .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded):hover a',
							$this->base_selector . '.collapse-enabled ul li .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded).hover a',
							$this->base_selector . '.collapse-enabled ul li .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded):focus a',
							$this->base_selector . '.collapse-enabled ul li .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded):active a',
							$this->base_selector . '.collapse-enabled ul li .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded):focus-within a',
						];
						if ( $disable_megamenu ) {
							$selectors[] = $this->base_selector . '.collapse-enabled .fusion-megamenu-menu .fusion-megamenu-wrapper .fusion-megamenu-holder ul li.current-menu-item';
							$selectors[] = $this->base_selector . '.collapse-enabled .fusion-megamenu-menu .fusion-megamenu-wrapper .fusion-megamenu-holder ul li:hover';
							$selectors[] = $this->base_selector . '.collapse-enabled .fusion-megamenu-menu .fusion-megamenu-wrapper .fusion-megamenu-holder ul li:active';
							$selectors[] = $this->base_selector . '.collapse-enabled .fusion-megamenu-menu .fusion-megamenu-wrapper .fusion-megamenu-holder ul li:focus';
							$selectors[] = $this->base_selector . '.collapse-enabled .fusion-megamenu-menu .fusion-megamenu-wrapper .fusion-megamenu-holder ul li:focus-within';
						}
						$this->add_css_property( $selectors, 'background', $this->args['mobile_active_bg'], true );
					}

					// Mobile active color.
					if ( ! $this->is_default( 'mobile_active_color' ) ) {
						$selectors = [
							$this->base_selector . '.collapse-enabled ul li:hover > .fusion-open-nav-submenu-on-click:before',
							$this->base_selector . '.collapse-enabled ul li.hover > .fusion-open-nav-submenu-on-click:before',
							$this->base_selector . '.collapse-enabled ul li:focus > .fusion-open-nav-submenu-on-click:before',
							$this->base_selector . '.collapse-enabled ul li:active > .fusion-open-nav-submenu-on-click:before',
							$this->base_selector . '.collapse-enabled ul li:focus-within > .fusion-open-nav-submenu-on-click:before',
							$this->base_selector . '.collapse-enabled ul li:hover > a',
							$this->base_selector . '.collapse-enabled ul li.hover > a',
							$this->base_selector . '.collapse-enabled ul li:focus > a',
							$this->base_selector . '.collapse-enabled ul li:active > a',
							$this->base_selector . '.collapse-enabled ul li:focus-within > a',
							$this->base_selector . '.collapse-enabled ul li.current-menu-item > a',
							$this->base_selector . '.collapse-enabled ul li.current-menu-ancestor > a',
							$this->base_selector . '.collapse-enabled ul li.current-menu-parent > a',
							$this->base_selector . '.collapse-enabled ul li.expanded > a',
							$this->base_selector . '.collapse-enabled ul li:hover > a .fusion-button',
							$this->base_selector . '.collapse-enabled ul li.hover > a .fusion-button',
							$this->base_selector . '.collapse-enabled ul li:focus > a .fusion-button',
							$this->base_selector . '.collapse-enabled ul li:active > a .fusion-button',
							$this->base_selector . '.collapse-enabled ul li:focus-within > a .fusion-button',
							$this->base_selector . '.collapse-enabled ul li.current-menu-item > a .fusion-button',
							$this->base_selector . '.collapse-enabled ul li.current-menu-ancestor > a .fusion-button',
							$this->base_selector . '.collapse-enabled ul li.current-menu-parent > a .fusion-button',
							$this->base_selector . '.collapse-enabled ul li.expanded > a .fusion-button',
							$this->base_selector . '.collapse-enabled ul li:hover > .fusion-open-nav-submenu-on-click:before',
							$this->base_selector . '.collapse-enabled ul li.hover > .fusion-open-nav-submenu-on-click:before',
							$this->base_selector . '.collapse-enabled ul li:focus > .fusion-open-nav-submenu-on-click:before',
							$this->base_selector . '.collapse-enabled ul li:active > .fusion-open-nav-submenu-on-click:before',
							$this->base_selector . '.collapse-enabled ul li:focus-within > .fusion-open-nav-submenu-on-click:before',
							$this->base_selector . '.collapse-enabled ul li.current-menu-item > .fusion-open-nav-submenu-on-click:before',
							$this->base_selector . '.collapse-enabled ul li.current-menu-parent > .fusion-open-nav-submenu-on-click:before',
							$this->base_selector . '.collapse-enabled ul li.current-menu-ancestor > .fusion-open-nav-submenu-on-click:before',
							$this->base_selector . '.collapse-enabled ul.sub-menu.sub-menu li.current-menu-ancestor > .fusion-open-nav-submenu-on-click:before',
							$this->base_selector . '.collapse-enabled ul.sub-menu.sub-menu li.current-menu-parent > .fusion-open-nav-submenu-on-click:before',
							$this->base_selector . '.collapse-enabled ul.sub-menu.sub-menu li.expanded > .fusion-open-nav-submenu-on-click:before',
							$this->base_selector . '.collapse-enabled ul.sub-menu.sub-menu li:hover > .fusion-open-nav-submenu-on-click:before',
							$this->base_selector . '.collapse-enabled ul.sub-menu.sub-menu li.hover > .fusion-open-nav-submenu-on-click:before',
							$this->base_selector . '.collapse-enabled ul.sub-menu.sub-menu li:focus > .fusion-open-nav-submenu-on-click:before',
							$this->base_selector . '.collapse-enabled ul.sub-menu.sub-menu li:active > .fusion-open-nav-submenu-on-click:before',
							$this->base_selector . '.collapse-enabled ul.sub-menu.sub-menu li:focus-within > .fusion-open-nav-submenu-on-click:before',
							$this->base_selector . '.collapse-enabled ul.sub-menu.sub-menu li:hover > a',
							$this->base_selector . '.collapse-enabled ul.sub-menu.sub-menu li.hover > a',
							$this->base_selector . '.collapse-enabled ul.sub-menu.sub-menu li:focus > a',
							$this->base_selector . '.collapse-enabled ul.sub-menu.sub-menu li:active > a',
							$this->base_selector . '.collapse-enabled ul.sub-menu.sub-menu li:focus-within > a',
							$this->base_selector . '.collapse-enabled ul.sub-menu.sub-menu li.current-menu-item > a',
							$this->base_selector . '.collapse-enabled ul.sub-menu.sub-menu li.current-menu-ancestor > a',
							$this->base_selector . '.collapse-enabled ul.sub-menu.sub-menu li.current-menu-parent > a',
							$this->base_selector . '.collapse-enabled ul.sub-menu.sub-menu li.expanded > a',
							$this->base_selector . '.collapse-enabled ul.sub-menu.sub-menu li:hover > a:hover',
							$this->base_selector . '.collapse-enabled ul.sub-menu.sub-menu li.hover > a:hover',
							$this->base_selector . '.collapse-enabled ul.sub-menu.sub-menu li:focus > a:hover',
							$this->base_selector . '.collapse-enabled ul.sub-menu.sub-menu li:active > a:hover',
							$this->base_selector . '.collapse-enabled ul.sub-menu.sub-menu li:focus-within > a:hover',
							$this->base_selector . '.collapse-enabled ul.sub-menu.sub-menu li.current-menu-item > a:hover',
							$this->base_selector . '.collapse-enabled ul.sub-menu.sub-menu li.current-menu-ancestor > a:hover',
							$this->base_selector . '.collapse-enabled ul.sub-menu.sub-menu li.current-menu-parent > a:hover',
							$this->base_selector . '.collapse-enabled ul.sub-menu.sub-menu li.expanded > a:hover',
							$this->base_selector . '.collapse-enabled ul.sub-menu.sub-menu li:hover > .fusion-open-nav-submenu-on-click:before',
							$this->base_selector . '.collapse-enabled ul.sub-menu.sub-menu li.hover > .fusion-open-nav-submenu-on-click:before',
							$this->base_selector . '.collapse-enabled ul.sub-menu.sub-menu li:focus > .fusion-open-nav-submenu-on-click:before',
							$this->base_selector . '.collapse-enabled ul.sub-menu.sub-menu li:active > .fusion-open-nav-submenu-on-click:before',
							$this->base_selector . '.collapse-enabled ul.sub-menu.sub-menu li:focus-within > .fusion-open-nav-submenu-on-click:before',
							$this->base_selector . '.collapse-enabled ul.sub-menu.sub-menu li.current-menu-item > .fusion-open-nav-submenu-on-click:before',
							$this->base_selector . '.collapse-enabled ul.sub-menu.sub-menu li.current-menu-ancestor > .fusion-open-nav-submenu-on-click:before',
							$this->base_selector . '.collapse-enabled ul.sub-menu.sub-menu li.current-menu-parent > .fusion-open-nav-submenu-on-click:before',
							$this->base_selector . '.collapse-enabled ul.sub-menu.sub-menu li.expanded > .fusion-open-nav-submenu-on-click:before',
							$this->base_selector . '.collapse-enabled ul li .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded):hover a',
							$this->base_selector . '.collapse-enabled ul li .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded).hover a',
							$this->base_selector . '.collapse-enabled ul li .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded):focus a',
							$this->base_selector . '.collapse-enabled ul li .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded):active a',
							$this->base_selector . '.collapse-enabled ul li .sub-menu li:not(.current-menu-item):not(.current-menu-ancestor):not(.current-menu-parent):not(.expanded):focus-within a',
						];

						if ( $disable_megamenu ) {
							$selectors[] = $this->base_selector . '.collapse-enabled .fusion-megamenu-menu .fusion-megamenu-wrapper .fusion-megamenu-holder ul li.current-menu-item > a';
							$selectors[] = $this->base_selector . '.collapse-enabled .fusion-megamenu-menu .fusion-megamenu-wrapper .fusion-megamenu-holder ul li:hover > a';
							$selectors[] = $this->base_selector . '.collapse-enabled .fusion-megamenu-menu .fusion-megamenu-wrapper .fusion-megamenu-holder ul li:active > a';
							$selectors[] = $this->base_selector . '.collapse-enabled .fusion-megamenu-menu .fusion-megamenu-wrapper .fusion-megamenu-holder ul li:focus > a';
							$selectors[] = $this->base_selector . '.collapse-enabled .fusion-megamenu-menu .fusion-megamenu-wrapper .fusion-megamenu-holder ul li:focus-within > a';
							$selectors[] = $this->base_selector . '.collapse-enabled .fusion-megamenu-menu .fusion-megamenu-wrapper .fusion-megamenu-holder ul li:hover .fusion-megamenu-title';
							$selectors[] = $this->base_selector . '.collapse-enabled .fusion-megamenu-menu .fusion-megamenu-wrapper .fusion-megamenu-holder ul li:hover .fusion-megamenu-title .awb-justify-title';
							$selectors[] = $this->base_selector . '.collapse-enabled .fusion-megamenu-menu .fusion-megamenu-wrapper .fusion-megamenu-holder ul li:active .fusion-megamenu-title';
							$selectors[] = $this->base_selector . '.collapse-enabled .fusion-megamenu-menu .fusion-megamenu-wrapper .fusion-megamenu-holder ul li:active .fusion-megamenu-title .awb-justify-title';
							$selectors[] = $this->base_selector . '.collapse-enabled .fusion-megamenu-menu .fusion-megamenu-wrapper .fusion-megamenu-holder ul li:focus .fusion-megamenu-title';
							$selectors[] = $this->base_selector . '.collapse-enabled .fusion-megamenu-menu .fusion-megamenu-wrapper .fusion-megamenu-holder ul li:focus .fusion-megamenu-title .awb-justify-title';

						}
						$this->add_css_property( $selectors, 'color', $this->args['mobile_active_color'], true );
					}

					// Mobile separators color.
					if ( ! $this->is_default( 'mobile_sep_color' ) ) {
						$selectors = [
							$this->base_selector . '.collapse-enabled li:not(:last-child)',
							$this->base_selector . '.collapse-enabled li.menu-item:not(.expanded)',
						];

						if ( $disable_megamenu ) {
							$selectors[] = $this->base_selector . '.collapse-enabled li.menu-item.expanded .fusion-megamenu-wrapper ul.fusion-megamenu li.menu-item-has-children .fusion-megamenu-title';
						}

						$this->add_css_property( $selectors, 'border-bottom-color', $this->args['mobile_sep_color'], true );

						if ( $disable_megamenu ) {
							$this->add_css_property( $this->base_selector . '.collapse-enabled li.menu-item.expanded .fusion-megamenu-wrapper', 'border-top-color', $this->args['mobile_sep_color'] );
						}

						$this->add_css_property( $this->base_selector . '.collapse-enabled li.menu-item.menu-item-has-children ul.sub-menu li.menu-item-has-children.expanded>ul.sub-menu', 'border-top-color', $this->args['mobile_sep_color'] );
					}

					// Mobile active color.
					if ( ! $this->is_default( 'mobile_nav_items_height' ) ) {
						$selectors = [
							$this->base_selector . '.collapse-enabled ul li > a',
							$this->base_selector . '.collapse-enabled .fusion-open-nav-submenu-on-click:before',
							$this->base_selector . '.collapse-enabled li.menu-item',
						];
						$this->add_css_property( $selectors, 'min-height', fusion_library()->sanitize->get_value_with_unit( $this->args['mobile_nav_items_height'] ) );
					}

					// Mobile sticky maximum height.
					if ( ! $this->is_default( 'mobile_sticky_max_height' ) ) {
						$selectors = [
							'.fusion-body .fusion-sticky-container.fusion-sticky-transition ' . $this->base_selector_no_body . '.collapse-enabled.mobile-size-column-absolute:not(.mobile-mode-always-expanded).expanded>ul',
							'.fusion-body .fusion-sticky-container.fusion-sticky-transition ' . $this->base_selector_no_body . '.collapse-enabled.mobile-size-full-absolute:not(.mobile-mode-always-expanded).expanded>ul',
						];
						$this->add_css_property( $selectors, 'max-height', $this->args['mobile_sticky_max_height'] );
					}

					$selectors = [
						$this->base_selector . '.collapse-enabled .fusion-menu-element-list li a',
						$this->base_selector . '.collapse-enabled .fusion-menu-element-list li a .fusion-button',
						$this->base_selector . '.collapse-enabled .fusion-menu-element-list li .fusion-open-nav-submenu:before',
					];
					if ( $disable_megamenu ) {
						$selectors[] = $this->base_selector . '.collapse-enabled .fusion-megamenu-wrapper .fusion-megamenu-holder .fusion-megamenu-submenu .fusion-megamenu-title a';
						$selectors[] = $this->base_selector . '.collapse-enabled .fusion-megamenu-wrapper .fusion-megamenu-holder .fusion-megamenu-submenu .fusion-megamenu-title .awb-justify-title';
					}

					// Mobile font-size.
					if ( ! $this->is_default( 'mobile_font_size' ) ) {
						$this->add_css_property( $selectors, 'font-size', $this->args['mobile_font_size'] );
					}

					// Mobile text transform.
					if ( ! $this->is_default( 'mobile_text_transform' ) ) {
						$this->add_css_property( $selectors, 'text-transform', $this->args['mobile_text_transform'] );
					}

					// Mobile line height.
					if ( ! $this->is_default( 'mobile_line_height' ) ) {
						$this->add_css_property( $selectors, 'line-height', $this->args['mobile_line_height'] );
					}

					// Mobile letter spacing.
					if ( ! $this->is_default( 'mobile_letter_spacing' ) ) {
						$this->add_css_property( $selectors, 'letter-spacing', fusion_library()->sanitize->get_value_with_unit( $this->args['mobile_letter_spacing'] ) );
					}

					$selectors = [
						$this->base_selector . '.collapse-enabled',
						$this->base_selector . '.collapse-enabled ul li > a',
						$this->base_selector . '.collapse-enabled ul li > a .fusion-button',
					];

					if ( $disable_megamenu ) {
						$selectors[] = $this->base_selector . '.collapse-enabled .fusion-megamenu-wrapper .fusion-megamenu-holder .fusion-megamenu-submenu .fusion-megamenu-title';
						$selectors[] = $this->base_selector . '.collapse-enabled .fusion-megamenu-wrapper .fusion-megamenu-holder .fusion-megamenu-submenu .fusion-megamenu-title a';
						$selectors[] = $this->base_selector . '.collapse-enabled .fusion-megamenu-wrapper .fusion-megamenu-holder .fusion-megamenu-submenu .fusion-megamenu-title .awb-justify-title';
					}

					$menu_styles = Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'mobile_typography', 'array' );

					foreach ( $menu_styles as $rule => $value ) {
						$this->add_css_property( $selectors, $rule, $value );
					}

					// This is outside the condition on purpose.
					$this->add_css_property(
						[
							$this->base_selector . '.collapse-enabled [class*="awb-icon-"]',
							$this->base_selector . '.collapse-enabled [class^="awb-icon-"]',
						],
						'font-family',
						$this->args['fusion_font_family_mobile_typography'],
						true
					);
				}

				// Box shadow.
				if ( ! $this->is_default( 'box_shadow' ) ) {
					$selectors = [
						$this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list ul',
						$this->base_selector . ':not(.collapse-enabled) .custom-menu-search-dropdown .fusion-menu-searchform-dropdown .fusion-search-form-content',
					];
					if ( $disable_megamenu ) {
						$selectors[] = $this->base_selector . ':not(.collapse-enabled) .fusion-menu-element-list .fusion-megamenu-wrapper';
					}
					$this->add_css_property( $selectors, 'box-shadow', Fusion_Builder_Box_Shadow_Helper::get_box_shadow_styles( $this->args ) );
				}
				$css = $this->parse_css();

				return $css ? '<style>' . $css . '</style>' : '';
			}

			/**
			 * Fetch args for menu.
			 *
			 * @access public
			 * @since 3.0
			 * @param array $menu_args The menu arguments.
			 * @return array
			 */
			public function fetch_menu_args( $menu_args = [] ) {

				$main_menu_args = [
					'menu'         => $this->args['menu'],
					'depth'        => 5,
					'menu_class'   => 'fusion-menu fusion-custom-menu fusion-menu-element-list',
					'items_wrap'   => '<ul id="%1$s" class="%2$s">%3$s</ul>',
					'fallback_cb'  => 'Fusion_Nav_Walker::fallback',
					'walker'       => new Fusion_Nav_Walker(
						[
							'header_layout'            => 'v1',
							'header_position'          => 'top',
							'menu_highlight_style'     => 'background',
							'disable_highlight_arrows' => true,
							'fb_menu_element'          => true,
							'transition_type'          => $this->args['transition_type'],
							'expand_method'            => isset( $menu_args['method'] ) ? $menu_args['method'] : $this->args['expand_method'],
							'submenu_mode'             => isset( $this->args['submenu_mode'] ) ? $this->args['submenu_mode'] : 'dropdown',
							'menu_display_dropdown_indicator' => 'parent_child',
							'menu_icon_position'       => $this->args['icons_position'],
						]
					),
					'container'    => false,
					'item_spacing' => 'discard',
					'echo'         => false,
				];

				return $main_menu_args;
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 3.0
			 * @return array
			 */
			public function attr() {
				$attr = [
					'class'                => '',
					'style'                => '',
					'aria-label'           => 'Menu',
					'data-breakpoint'      => $this->args['custom_breakpoint'],
					'data-count'           => esc_attr( $this->count ),
					'data-transition-type' => esc_attr( $this->args['transition_type'] ),
					'data-transition-time' => esc_attr( $this->args['transition_time'] ),
				];

				$has_active_border_bottom = ! $this->args['active_border_bottom'] || in_array( $this->args['active_border_bottom'], [ '', '0', '0px' ], true ) ? 'no' : 'yes';
				$has_active_border_right  = ! $this->args['active_border_right'] || in_array( $this->args['active_border_right'], [ '', '0', '0px' ], true ) ? 'no' : 'yes';
				$has_active_border_left   = ! $this->args['active_border_left'] || in_array( $this->args['active_border_left'], [ '', '0', '0px' ], true ) ? 'no' : 'yes';

				$nav_classes = [
					'fusion-menu-element-wrapper',
					'direction-' . $this->args['direction'],
					'mode-' . $this->args['collapsed_mode'],
					'expand-method-' . $this->args['expand_method'],
					'submenu-mode-' . $this->args['submenu_mode'],
					'mobile-mode-' . $this->args['mobile_nav_mode'],
					'mobile-size-' . $this->args['mobile_nav_size'],
					'icons-position-' . $this->args['icons_position'],
					'dropdown-carets-' . $this->args['dropdown_carets'],
					'has-active-border-bottom-' . $has_active_border_bottom,
					'has-active-border-left-' . $has_active_border_left,
					'has-active-border-right-' . $has_active_border_right,
					'mobile-trigger-fullwidth-' . $this->args['mobile_nav_trigger_fullwidth'],
					'mobile-indent-' . $this->args['mobile_indent_submenu'],
					'mobile-justify-' . $this->args['mobile_justify_content'],
					'main-justify-' . $this->args['main_justify_content'],
				];

				// Don't add loading class in live builder, see Fusion-Builder#4365.
				if ( ! ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) ) {
					$nav_classes[] = 'loading mega-menu-loading';
				}

				// If we have a breakpoint and load is a mobile, set to default as collapse-enabled.
				if ( 'never' !== $this->args['breakpoint'] && wp_is_mobile() ) {
					$nav_classes[] = 'collapse-enabled';
				}
				if ( is_array( $this->args['arrows'] ) ) {
					$this->args['arrows'] = implode( ',', $this->args['arrows'] );
				}

				if ( false !== strpos( $this->args['arrows'], 'active' ) ) {
					$nav_classes[] = 'active-item-arrows-on';
				}

				if ( false !== strpos( $this->args['arrows'], 'main' ) ) {
					$nav_classes[] = 'dropdown-arrows-parent';
				}

				if ( false !== strpos( $this->args['arrows'], 'submenu' ) ) {
					$nav_classes[] = 'dropdown-arrows-child';
				}

				if ( 'flyout' === $this->args['submenu_mode'] ) {
					$nav_classes[] = 'submenu-flyout-direction-' . $this->args['submenu_flyout_direction'];
				}

				if ( 'flyout' !== $this->args['submenu_mode'] ) {
					$nav_classes[] = 'expand-' . $this->args['expand_direction'];
				}

				if ( 'dropdown' === $this->args['submenu_mode'] ) {
					$nav_classes[] = 'submenu-transition-' . $this->args['expand_transition'];
				}

				$attr['class'] .= implode( ' ', $nav_classes );
				$attr['class'] .= Fusion_Builder_Sticky_Visibility_Helper::get_sticky_class( $this->args['sticky_display'] );

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				if ( $this->args['menu'] ) {
					$menu = wp_get_nav_menus( $this->args['menu'] );

					if ( is_object( $menu ) && isset( $menu->name ) ) {
							$attr['aria-label'] = $menu->name;
					}
				}

				if ( 'never' === $this->args['breakpoint'] ) {
					$attr['data-breakpoint'] = '0';
				} elseif ( 'small' === $this->args['breakpoint'] ) {
					$attr['data-breakpoint'] = fusion_library()->get_option( 'visibility_small' );
				} elseif ( 'medium' === $this->args['breakpoint'] ) {
					$attr['data-breakpoint'] = fusion_library()->get_option( 'visibility_medium' );
				} elseif ( 'large' === $this->args['breakpoint'] ) {
					$attr['data-breakpoint'] = 10000;
				}

				if ( '' !== $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( '' !== $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				return $attr;
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
					'fusion-menu',
					FusionBuilder::$js_folder_url . '/general/fusion-menu.js',
					FusionBuilder::$js_folder_path . '/general/fusion-menu.js',
					[ 'jquery' ],
					'1',
					true
				);

				if ( $fusion_settings->get( 'disable_megamenu' ) ) {
					Fusion_Dynamic_JS::enqueue_script(
						'fusion-mega-menu',
						FusionBuilder::$js_folder_url . '/general/fusion-mega-menu.js',
						FusionBuilder::$js_folder_path . '/general/fusion-mega-menu.js',
						[ 'jquery', 'fusion-menu' ],
						'1',
						true
					);
				}

				Fusion_Dynamic_JS::localize_script(
					'fusion-menu',
					'fusionMenuVars',
					[
						/* Translators: The submenu title. */
						'mobile_submenu_open' => esc_attr__( 'Open submenu of %s', 'Avada' ),
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
				$fusion_settings = awb_get_fusion_settings();
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/menu.min.css' );

				if ( $fusion_settings->get( 'disable_megamenu' ) ) {
					FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/mega-menu.min.css' );
				}
			}
		}
	}

	new FusionSC_Menu();
}

/**
 * Map shortcode to Avada Builder
 *
 * @since 3.0
 */
function fusion_element_menu() {

	// Whether we are actually on an edit screen.
	$builder_status  = function_exists( 'is_fusion_editor' ) && is_fusion_editor();
	$menu_options    = [];
	$menu_edit_items = [];

	// If we are on edit screen, fetch menu options.
	if ( $builder_status ) {
		$menus = wp_get_nav_menus();
		foreach ( $menus as $menu ) {
			$menu_options[ $menu->slug ]    = $menu->name;
			$menu_edit_items[ $menu->slug ] = $menu->term_id;
		}
	}

	$preview_active_root = [
		'selector' => '.fusion-menu-element-wrapper .fusion-menu-element-list > li:not(.fusion-menu-item-button):not(.fusion-megamenu-menu)',
		'type'     => 'class',
		'toggle'   => 'hover',
	];

	$preview_active_submenu = [
		'selector' => '.fusion-menu-element-wrapper .fusion-menu-element-list > li:not(.fusion-menu-item-button):not(.fusion-megamenu-menu).menu-item-has-children,.fusion-menu-element-wrapper.expand-method-click .fusion-menu-element-list > li:not(.fusion-menu-item-button):not(.fusion-megamenu-menu) .fusion-open-nav-submenu',
		'type'     => 'class',
		'toggle'   => 'hover',
	];

	$preview_active_submenu_item = [
		'selector' => '.fusion-menu-element-wrapper .fusion-menu-element-list > li:not(.fusion-menu-item-button):not(.fusion-megamenu-menu).menu-item-has-children a,.fusion-menu-element-wrapper.expand-method-click .fusion-menu-element-list > li:not(.fusion-menu-item-button):not(.fusion-megamenu-menu) .fusion-open-nav-submenu,.fusion-menu-element-wrapper .fusion-menu-element-list ul:not(.fusion-megamenu) > li',
		'type'     => 'class',
		'toggle'   => 'hover',
	];

	$params = [
		[
			'type'        => 'select',
			'heading'     => esc_html__( 'Menu', 'fusion-builder' ),
			'description' => esc_html__( 'Select the menu which you want to use.', 'fusion-builder' ),
			'param_name'  => 'menu',
			'value'       => $menu_options,
			'default'     => array_key_first( $menu_options ), // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.array_key_firstFound
			'callback'    => [
				'function' => 'fusion_ajax',
				'action'   => 'get_fusion_menu',
				'ajax'     => true,
			],
			'quick_edit'  => [
				'label' => esc_html__( 'Edit Menu', 'fusion-builder' ),
				'type'  => 'menu',
				'items' => $menu_edit_items,
			],
		],
		[
			'type'        => 'checkbox_button_set',
			'heading'     => esc_html__( 'Element Visibility', 'fusion-builder' ),
			'param_name'  => 'hide_on_mobile',
			'value'       => fusion_builder_visibility_options( 'full' ),
			'default'     => fusion_builder_default_visibility( 'array' ),
			'description' => esc_html__( 'Choose to show or hide the element on small, medium or large screens. You can choose more than one at a time.', 'fusion-builder' ),
		],
		'fusion_sticky_visibility_placeholder' => [],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_html__( 'Direction', 'fusion-builder' ),
			'param_name'  => 'direction',
			'value'       => [
				'row'    => esc_html__( 'Horizontal', 'fusion-builder' ),
				'column' => esc_html__( 'Vertical', 'fusion-builder' ),
			],
			'default'     => 'row',
			'description' => esc_html__( 'Choose to have a horizontal or a vertical menu.', 'fusion-builder' ),
		],
		'fusion_margin_placeholder'            => [
			'param_name'  => 'margin',
			'description' => esc_html__( 'Spacing above and below the section. Enter values including any valid CSS unit, ex: 4%.', 'fusion-builder' ),
			'group'       => esc_html__( 'General', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_menu',
				'args'     => [

					'dimension' => true,
				],
			],
		],
		[
			'type'        => 'range',
			'heading'     => esc_html__( 'Transition Time', 'fusion-builder' ),
			'description' => esc_html__( 'Set the time for submenu expansion and all other hover transitions. In milliseconds.', 'fusion-builder' ),
			'param_name'  => 'transition_time',
			'value'       => '300',
			'min'         => '0',
			'max'         => '1000',
			'step'        => '1',
		],
		[
			'type'        => 'textfield',
			'heading'     => esc_html__( 'Space Between Main Menu and Submenu', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the space between the main menu and dropdowns.', 'fusion-builder' ),
			'param_name'  => 'submenu_space',
			'value'       => '',
			'default'     => '',
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'checkbox_button_set',
			'heading'     => esc_html__( 'Menu Arrows', 'fusion-builder' ),
			'param_name'  => 'arrows',
			'value'       => [
				'main'    => esc_html__( 'Main', 'fusion-builder' ),
				'active'  => esc_html__( 'Main Active', 'fusion-builder' ),
				'submenu' => esc_html__( 'Submenu', 'fusion-builder' ),
			],
			'default'     => [ '' ],
			'description' => esc_html__( 'Choose if you want to show dropdown arrows on the main menu and submenus.', 'fusion-builder' ),
		],
		[
			'type'        => 'dimension',
			'heading'     => esc_html__( 'Arrow Size', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the width and height of the arrows.', 'fusion-builder' ),
			'param_name'  => 'arrows_size',
			'value'       => [
				'arrows_size_width'  => '',
				'arrows_size_height' => '',
			],
			'dependency'  => [
				[
					'element'  => 'arrows',
					'value'    => '',
					'operator' => '!=',
				],
			],
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'textfield',
			'heading'     => esc_html__( 'CSS Class', 'fusion-builder' ),
			'param_name'  => 'class',
			'value'       => '',
			'description' => esc_html__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
		],
		[
			'type'        => 'textfield',
			'heading'     => esc_html__( 'CSS ID', 'fusion-builder' ),
			'param_name'  => 'id',
			'value'       => '',
			'description' => esc_html__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
		],
		[
			'type'        => 'textfield',
			'heading'     => esc_html__( 'Minimum Height', 'fusion-builder' ),
			'description' => esc_html__( 'The minimum height for the main menu. Use any valid CSS unit.', 'fusion-builder' ),
			'param_name'  => 'min_height',
			'value'       => '',
			'default'     => '',
			'group'       => esc_html__( 'Main', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'textfield',
			'heading'     => esc_html__( 'Sticky Minimum Height', 'fusion-builder' ),
			'description' => esc_html__( 'The minimum height for main menu links when the container is sticky. Use any valid CSS unit. ', 'fusion-builder' ),
			'param_name'  => 'sticky_min_height',
			'value'       => '',
			'dependency'  => [
				[
					'element'  => 'fusion_builder_container',
					'param'    => 'sticky',
					'value'    => 'on',
					'operator' => '==',
				],
			],
			'group'       => esc_html__( 'Main', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_html__( 'Align Items', 'fusion-builder' ),
			'description' => esc_html__( 'Select how main menu items will be aligned. Defines the default behavior for how flex items are laid out along the cross axis on the current line (perpendicular to the main-axis).', 'fusion-builder' ),
			'param_name'  => 'align_items',
			'default'     => 'stretch',
			'grid_layout' => true,
			'back_icons'  => true,
			'value'       => [
				'flex-start' => esc_html__( 'Flex Start', 'fusion-builder' ),
				'center'     => esc_html__( 'Center', 'fusion-builder' ),
				'flex-end'   => esc_html__( 'Flex End', 'fusion-builder' ),
				'stretch'    => esc_html__( 'Stretch', 'fusion-builder' ),
			],
			'icons'       => [
				'flex-start' => '<span class="fusiona-align-top-columns"></span>',
				'center'     => '<span class="fusiona-align-center-columns"></span>',
				'flex-end'   => '<span class="fusiona-align-bottom-columns"></span>',
				'stretch'    => '<span class="fusiona-full-height"></span>',
			],
			'dependency'  => [
				[
					'element'  => 'direction',
					'value'    => 'row',
					'operator' => '==',
				],
			],
			'group'       => esc_html__( 'Main', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_html__( 'Justification', 'fusion-builder' ),
			'description' => esc_html__( 'Select how main menu items will be justified.', 'fusion-builder' ),
			'param_name'  => 'justify_content',
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
			'dependency'  => [
				[
					'element'  => 'direction',
					'value'    => 'row',
					'operator' => '==',
				],
			],
			'group'       => esc_html__( 'Main', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'             => 'typography',
			'heading'          => esc_attr__( 'Main Menu Typography', 'fusion-builder' ),
			'description'      => esc_html__( 'Controls the typography of the main menu item. Leave empty for the global font family.', 'fusion-builder' ),
			'param_name'       => 'main_menu_fonts',
			'choices'          => [
				'font-family'    => 'typography',
				'font-size'      => 'font_size',
				'text-transform' => 'text_transform',
				'line-height'    => 'line_height',
				'letter-spacing' => 'letter_spacing',
			],
			'default'          => [
				'font-family' => '',
				'variant'     => '400',
			],
			'remove_from_atts' => true,
			'global'           => true,
			'group'            => esc_attr__( 'Main', 'fusion-builder' ),
			'callback'         => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_html__( 'Main Menu Item Text Align', 'fusion-builder' ),
			'description' => esc_html__( 'Select if main menu items should be aligned to the left, right or centered.', 'fusion-builder' ),
			'param_name'  => 'main_justify_content',
			'grid_layout' => true,
			'back_icons'  => true,
			'value'       => [
				'left'          => esc_html__( 'Flex Start', 'fusion-builder' ),
				'center'        => esc_html__( 'Center', 'fusion-builder' ),
				'right'         => esc_html__( 'Flex End', 'fusion-builder' ),
				'space-between' => esc_html__( 'Space Between', 'fusion-builder' ),
			],
			'icons'       => [
				'left'          => '<span class="fusiona-horizontal-flex-start"></span>',
				'center'        => '<span class="fusiona-horizontal-flex-center"></span>',
				'right'         => '<span class="fusiona-horizontal-flex-end"></span>',
				'space-between' => '<span class="fusiona-horizontal-space-between"></span>',
			],
			'default'     => 'left',
			'group'       => esc_html__( 'Main', 'fusion-builder' ),
			'dependency'  => [
				[
					'element'  => 'direction',
					'value'    => 'row',
					'operator' => '!=',
				],
			],
		],
		[
			'type'        => 'dimension',
			'heading'     => esc_html__( 'Main Menu Item Padding', 'fusion-builder' ),
			'description' => esc_html__( 'Select the padding for main menu items. Enter values including any valid CSS unit, ex: 10px or 10%.', 'fusion-builder' ),
			'param_name'  => 'items_padding',
			'value'       => [
				'items_padding_top'    => '',
				'items_padding_right'  => '',
				'items_padding_bottom' => '',
				'items_padding_left'   => '',
			],
			'group'       => esc_html__( 'Main', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'textfield',
			'heading'     => esc_html__( 'Main Menu Item Spacing', 'fusion-builder' ),
			'description' => esc_html__( 'The gap between main menu items. Use any valid CSS value, including its unit (10px, 4%, 1em etc).', 'fusion-builder' ),
			'param_name'  => 'gap',
			'value'       => '',
			'default'     => '',
			'group'       => esc_html__( 'Main', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'             => 'dimension',
			'remove_from_atts' => true,
			'heading'          => esc_html__( 'Main Menu Item Border Radius', 'fusion-builder' ),
			'description'      => esc_html__( 'Enter values including any valid CSS unit, ex: 10px.', 'fusion-builder' ),
			'param_name'       => 'border_radius',
			'value'            => [
				'border_radius_top_left'     => '',
				'border_radius_top_right'    => '',
				'border_radius_bottom_right' => '',
				'border_radius_bottom_left'  => '',
			],
			'group'            => esc_html__( 'Main', 'fusion-builder' ),
			'callback'         => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'select',
			'heading'     => esc_html__( 'Main Menu Hover Transition', 'fusion-builder' ),
			'param_name'  => 'transition_type',
			'value'       => [
				'bottom-vertical' => esc_html__( 'Bottom', 'fusion-builder' ),
				'center'          => esc_html__( 'Center Horizontal', 'fusion-builder' ),
				'center-grow'     => esc_html__( 'Center Grow', 'fusion-builder' ),
				'center-vertical' => esc_html__( 'Center Vertical', 'fusion-builder' ),
				'fade'            => esc_html__( 'Fade', 'fusion-builder' ),
				'left'            => esc_html__( 'Left', 'fusion-builder' ),
				'right'           => esc_html__( 'Right', 'fusion-builder' ),
				'top-vertical'    => esc_html__( 'Top', 'fusion-builder' ),
			],
			'default'     => 'fade',
			'description' => esc_html__( 'Select the animation type when hovering the main menu items. This animation is applied to the background-color and borders.', 'fusion-builder' ),
			'group'       => esc_html__( 'Main', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_update_menu_transition',
			],
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_html__( 'Main Menu Icon Position', 'fusion-builder' ),
			'param_name'  => 'icons_position',
			'value'       => [
				'top'    => esc_html__( 'Top', 'Avada' ),
				'right'  => esc_html__( 'Right', 'Avada' ),
				'bottom' => esc_html__( 'Bottom', 'Avada' ),
				'left'   => esc_html__( 'Left', 'Avada' ),
			],
			'default'     => 'left',
			'description' => esc_html__( 'Controls the main menu icon position.', 'fusion-builder' ),
			'group'       => esc_html__( 'Main', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_ajax',
				'action'   => 'get_fusion_menu',
				'ajax'     => true,
			],
		],
		[
			'type'        => 'range',
			'heading'     => esc_html__( 'Main Menu Icon Size', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the size of the main menu icons.', 'fusion-builder' ),
			'param_name'  => 'icons_size',
			'value'       => '16',
			'min'         => '10',
			'max'         => '100',
			'step'        => '1',
			'group'       => esc_html__( 'Main', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'dimension',
			'heading'     => esc_html__( 'Mega Menu Thumbnail Size', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the width and height of the main menu mega-menu thumbnails. Use "auto" for automatic resizing if you added either width or height.', 'fusion-builder' ),
			'param_name'  => 'thumbnail_size',
			'value'       => [
				'thumbnail_size_width'  => '',
				'thumbnail_size_height' => '',
			],
			'group'       => esc_html__( 'Main', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_html__( 'Mega Menu Title Justification', 'fusion-builder' ),
			'description' => esc_html__( 'Select how mega menu titles will be justified.', 'fusion-builder' ),
			'param_name'  => 'justify_title',
			'default'     => 'center',
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
				'flex-start'    => esc_html__( 'Flex Start', 'fusion-builder' ),
				'center'        => esc_html__( 'Center', 'fusion-builder' ),
				'flex-end'      => esc_html__( 'Flex End', 'fusion-builder' ),
				'space-between' => esc_html__( 'Space Between', 'fusion-builder' ),
				'space-around'  => esc_html__( 'Space Around', 'fusion-builder' ),
				'space-evenly'  => esc_html__( 'Space Evenly', 'fusion-builder' ),
			],
			'group'       => esc_html__( 'Main', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'             => 'subgroup',
			'heading'          => esc_html__( 'Main Menu Item Styling', 'fusion-builder' ),
			'description'      => esc_html__( 'Use filters to see specific type of content.', 'fusion-builder' ),
			'param_name'       => 'main_styling',
			'default'          => 'regular',
			'group'            => esc_html__( 'Main', 'fusion-builder' ),
			'remove_from_atts' => true,
			'value'            => [
				'regular' => esc_html__( 'Regular', 'fusion-builder' ),
				'hover'   => esc_html__( 'Hover / Active', 'fusion-builder' ),
			],
			'icons'            => [
				'regular' => '<span class="fusiona-regular-state" style="font-size:18px;"></span>',
				'hover'   => '<span class="fusiona-hover-state" style="font-size:18px;"></span>',
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_html__( 'Main Menu Item Background Color', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the background-color for main menu items.', 'fusion-builder' ),
			'param_name'  => 'bg',
			'value'       => '',
			'default'     => 'rgba(0,0,0,0)',
			'group'       => esc_html__( 'Main', 'fusion-builder' ),
			'subgroup'    => [
				'name' => 'main_styling',
				'tab'  => 'regular',
			],
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_html__( 'Main Menu Item Text Color', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the color for main menu item text color.', 'fusion-builder' ),
			'param_name'  => 'color',
			'value'       => '',
			'default'     => '#212934',
			'group'       => esc_html__( 'Main', 'fusion-builder' ),
			'subgroup'    => [
				'name' => 'main_styling',
				'tab'  => 'regular',
			],
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_html__( 'Main Menu Item Hover / Active Background Color', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the background-color for main menu items hover / active states.', 'fusion-builder' ),
			'param_name'  => 'active_bg',
			'value'       => '',
			'default'     => 'rgba(0,0,0,0)',
			'group'       => esc_html__( 'Main', 'fusion-builder' ),
			'subgroup'    => [
				'name' => 'main_styling',
				'tab'  => 'hover',
			],
			'preview'     => $preview_active_root,
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_html__( 'Main Menu Item Hover / Active Text Color', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the color for main menu item text color hover / active states.', 'fusion-builder' ),
			'param_name'  => 'active_color',
			'value'       => '',
			'default'     => '#65bc7b',
			'group'       => esc_html__( 'Main', 'fusion-builder' ),
			'subgroup'    => [
				'name' => 'main_styling',
				'tab'  => 'hover',
			],
			'preview'     => $preview_active_root,
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'dimension',
			'heading'     => esc_html__( 'Main Menu Item Border Size', 'fusion-builder' ),
			'description' => esc_html__( 'Select the border size for main menu items. Enter values including any valid CSS unit, ex: 10px.', 'fusion-builder' ),
			'param_name'  => 'border',
			'value'       => [
				'border_top'    => '',
				'border_right'  => '',
				'border_bottom' => '',
				'border_left'   => '',
			],
			'group'       => esc_html__( 'Main', 'fusion-builder' ),
			'subgroup'    => [
				'name' => 'main_styling',
				'tab'  => 'regular',
			],
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_html__( 'Main Menu Item Border Color', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the border-color for main menu items.', 'fusion-builder' ),
			'param_name'  => 'border_color',
			'value'       => '',
			'default'     => 'rgba(0,0,0,0)',
			'group'       => esc_html__( 'Main', 'fusion-builder' ),
			'subgroup'    => [
				'name' => 'main_styling',
				'tab'  => 'regular',
			],
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'dimension',
			'heading'     => esc_html__( 'Main Menu Item Hover / Active Border Size', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the border size for main menu items hover / active states. Enter values including any valid CSS unit, ex: 10px.', 'fusion-builder' ),
			'param_name'  => 'active_border',
			'value'       => [
				'active_border_top'    => '',
				'active_border_right'  => '',
				'active_border_bottom' => '',
				'active_border_left'   => '',
			],
			'group'       => esc_html__( 'Main', 'fusion-builder' ),
			'subgroup'    => [
				'name' => 'main_styling',
				'tab'  => 'hover',
			],
			'preview'     => $preview_active_root,
			'callback'    => [
				'function' => 'fusion_ajax',
				'action'   => 'get_fusion_menu',
				'ajax'     => true,
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_html__( 'Main Menu Item Hover / Active Border Color', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the border-color for main menu items hover / active states.', 'fusion-builder' ),
			'param_name'  => 'active_border_color',
			'value'       => '',
			'default'     => 'rgba(0,0,0,0)',
			'group'       => esc_html__( 'Main', 'fusion-builder' ),
			'subgroup'    => [
				'name' => 'main_styling',
				'tab'  => 'hover',
			],
			'preview'     => $preview_active_root,
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_html__( 'Main Menu Item Icon Color', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the main menu icon color.', 'fusion-builder' ),
			'param_name'  => 'icons_color',
			'value'       => '',
			'default'     => '#212934',
			'group'       => esc_html__( 'Main', 'fusion-builder' ),
			'subgroup'    => [
				'name' => 'main_styling',
				'tab'  => 'regular',
			],
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_html__( 'Main Menu Item Hover / Active Icon Color', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the main menu icon hover / active color.', 'fusion-builder' ),
			'param_name'  => 'icons_hover_color',
			'value'       => '',
			'default'     => '#65bc7b',
			'group'       => esc_html__( 'Main', 'fusion-builder' ),
			'subgroup'    => [
				'name' => 'main_styling',
				'tab'  => 'hover',
			],
			'preview'     => $preview_active_root,
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_html__( 'Dropdown Carets', 'fusion-builder' ),
			'description' => esc_html__( 'Select whether dropdown carets should show as submenu indicator.', 'fusion-builder' ),
			'param_name'  => 'dropdown_carets',
			'value'       => [
				'yes' => esc_html__( 'Yes', 'fusion-builder' ),
				'no'  => esc_html__( 'No', 'fusion-builder' ),
			],
			'callback'    => [
				'function' => 'fusion_ajax',
				'action'   => 'get_fusion_menu',
				'ajax'     => true,
			],
			'default'     => 'yes',
			'group'       => esc_html__( 'Submenu', 'fusion-builder' ),
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_html__( 'Submenu Mode', 'fusion-builder' ),
			'param_name'  => 'submenu_mode',
			'value'       => [
				'dropdown' => esc_html__( 'Dropdown', 'fusion-builder' ),
				'flyout'   => esc_html__( 'Flyout', 'fusion-builder' ),
			],
			'default'     => 'dropdown',
			'description' => esc_html__( 'Select whether you want a classic dropdown, or a full-screen flyout.', 'fusion-builder' ),
			'group'       => esc_html__( 'Submenu', 'fusion-builder' ),
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_html__( 'Expand Method', 'fusion-builder' ),
			'param_name'  => 'expand_method',
			'value'       => [
				'hover' => esc_html__( 'Hover', 'fusion-builder' ),
				'click' => esc_html__( 'Click', 'fusion-builder' ),
			],
			'default'     => 'hover',
			'description' => esc_html__( 'Select how submenus will expand. If carets are enabled, then they will become clickable.', 'fusion-builder' ),
			'group'       => esc_html__( 'Submenu', 'fusion-builder' ),
			'dependency'  => [
				[
					'element'  => 'submenu_mode',
					'value'    => 'flyout',
					'operator' => '!=',
				],
			],
			'callback'    => [
				'function' => 'fusion_ajax',
				'action'   => 'get_fusion_menu',
				'ajax'     => true,
			],
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_html__( 'Submenu Expand Direction', 'fusion-builder' ),
			'param_name'  => 'expand_direction',
			'value'       => [
				'left'  => esc_html__( 'Left', 'fusion-builder' ),
				'right' => esc_html__( 'Right', 'fusion-builder' ),
			],
			'default'     => ( is_rtl() ) ? 'left' : 'right',
			'description' => esc_html__( 'Changes the expand direction for submenus and vertical menus.', 'fusion-builder' ),
			'group'       => esc_html__( 'Submenu', 'fusion-builder' ),
			'dependency'  => [
				[
					'element'  => 'submenu_mode',
					'value'    => 'dropdown',
					'operator' => '==',
				],
			],
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_html__( 'Submenu Expand Transition', 'fusion-builder' ),
			'param_name'  => 'expand_transition',
			'value'       => [
				'fade'     => esc_html__( 'Fade', 'fusion-builder' ),
				'slide_up' => esc_html__( 'Slide Up', 'fusion-builder' ),
			],
			'default'     => 'fade',
			'description' => esc_html__( 'Changes the expand transition for submenus.', 'fusion-builder' ),
			'group'       => esc_html__( 'Submenu', 'fusion-builder' ),
			'dependency'  => [
				[
					'element'  => 'submenu_mode',
					'value'    => 'dropdown',
					'operator' => '==',
				],
				[
					'element'  => 'direction',
					'value'    => 'row',
					'operator' => '==',
				],
			],
		],
		[
			'type'        => 'textfield',
			'heading'     => esc_html__( 'Submenu Maximum Width', 'fusion-builder' ),
			'description' => esc_html__( 'The maximum width for submenus. Use any valid CSS value.', 'fusion-builder' ),
			'param_name'  => 'submenu_max_width',
			'value'       => '',
			'default'     => '',
			'group'       => esc_html__( 'Submenu', 'fusion-builder' ),
			'dependency'  => [
				[
					'element'  => 'submenu_mode',
					'value'    => 'dropdown',
					'operator' => '==',
				],
			],
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_html__( 'Flyout Direction', 'fusion-builder' ),
			'param_name'  => 'submenu_flyout_direction',
			'value'       => [
				'fade'   => esc_html__( 'Fade', 'Avada' ),
				'left'   => esc_html__( 'Left', 'fusion-builder' ),
				'right'  => esc_html__( 'Right', 'fusion-builder' ),
				'bottom' => esc_html__( 'Bottom', 'Avada' ),
				'top'    => esc_html__( 'Top', 'Avada' ),
			],
			'default'     => 'fade',
			'description' => esc_html__( 'Controls the direction the flyout submenu starts from.', 'fusion-builder' ),
			'group'       => esc_html__( 'Submenu', 'fusion-builder' ),
			'dependency'  => [
				[
					'element'  => 'submenu_mode',
					'value'    => 'flyout',
					'operator' => '==',
				],
			],
		],
		[
			'type'             => 'typography',
			'heading'          => esc_attr__( 'Submenu Typography', 'fusion-builder' ),
			'description'      => esc_html__( 'Controls the typography of the submenu items. Leave empty for the global font family.', 'fusion-builder' ),
			'param_name'       => 'submenu_fonts',
			'choices'          => [
				'font-family'    => 'submenu_typography',
				'font-size'      => 'submenu_font_size',
				'text-transform' => 'submenu_text_transform',
				'line-height'    => 'submenu_line_height',
				'letter-spacing' => 'submenu_letter_spacing',
			],
			'default'          => [
				'font-family' => '',
				'variant'     => '400',
			],
			'remove_from_atts' => true,
			'global'           => true,
			'group'            => esc_attr__( 'Submenu', 'fusion-builder' ),
			'callback'         => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'dimension',
			'heading'     => esc_html__( 'Submenu Item Padding', 'fusion-builder' ),
			'description' => esc_html__( 'Select the padding for submenu items. Enter values including any valid CSS unit, ex: 10px or 10%.', 'fusion-builder' ),
			'param_name'  => 'submenu_items_padding',
			'value'       => [
				'submenu_items_padding_top'    => '',
				'submenu_items_padding_right'  => '',
				'submenu_items_padding_bottom' => '',
				'submenu_items_padding_left'   => '',
			],
			'group'       => esc_html__( 'Submenu', 'fusion-builder' ),
			'preview'     => $preview_active_submenu,
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'             => 'dimension',
			'remove_from_atts' => true,
			'heading'          => esc_html__( 'Submenu Border Radius', 'fusion-builder' ),
			'description'      => __( 'Enter values including any valid CSS unit, ex: 10px.', 'fusion-builder' ),
			'param_name'       => 'submenu_border_radius',
			'value'            => [
				'submenu_border_radius_top_left'     => '',
				'submenu_border_radius_top_right'    => '',
				'submenu_border_radius_bottom_right' => '',
				'submenu_border_radius_bottom_left'  => '',
			],
			'group'            => esc_html__( 'Submenu', 'fusion-builder' ),
			'preview'          => $preview_active_submenu,
			'dependency'       => [
				[
					'element'  => 'submenu_mode',
					'value'    => 'dropdown',
					'operator' => '==',
				],
			],
			'callback'         => [
				'function' => 'fusion_menu',
			],
		],
		'fusion_box_shadow_placeholder'        => [
			'group'      => esc_html__( 'Submenu', 'fusion-builder' ),
			'dependency' => [
				[
					'element'  => 'box_shadow',
					'value'    => 'yes',
					'operator' => '==',
				],
				[
					'element'  => 'submenu_mode',
					'value'    => 'flyout',
					'operator' => '!=',
				],
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_html__( 'Submenu Separator Color', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the color for the submenu items separator. Set to transparent for no separator.', 'fusion-builder' ),
			'param_name'  => 'submenu_sep_color',
			'value'       => '',
			'default'     => '#e2e2e2',
			'group'       => esc_html__( 'Submenu', 'fusion-builder' ),
			'preview'     => $preview_active_submenu,
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'             => 'subgroup',
			'heading'          => esc_html__( 'Submenu Item Styling', 'fusion-builder' ),
			'description'      => esc_html__( 'Use filters to see specific type of content.', 'fusion-builder' ),
			'param_name'       => 'submenu_styling',
			'default'          => 'regular',
			'group'            => esc_html__( 'Submenu', 'fusion-builder' ),
			'remove_from_atts' => true,
			'value'            => [
				'regular' => esc_html__( 'Regular', 'fusion-builder' ),
				'hover'   => esc_html__( 'Hover / Active', 'fusion-builder' ),
			],
			'icons'            => [
				'regular' => '<span class="fusiona-regular-state" style="font-size:18px;"></span>',
				'hover'   => '<span class="fusiona-hover-state" style="font-size:18px;"></span>',
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_html__( 'Submenu Background Color', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the background-color for submenu dropdowns.', 'fusion-builder' ),
			'param_name'  => 'submenu_bg',
			'value'       => '',
			'default'     => '#ffffff',
			'group'       => esc_html__( 'Submenu', 'fusion-builder' ),
			'subgroup'    => [
				'name' => 'submenu_styling',
				'tab'  => 'regular',
			],
			'preview'     => $preview_active_submenu,
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_html__( 'Submenu Text Color', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the text color for submenu dropdowns.', 'fusion-builder' ),
			'param_name'  => 'submenu_color',
			'value'       => '',
			'default'     => '#212934',
			'group'       => esc_html__( 'Submenu', 'fusion-builder' ),
			'subgroup'    => [
				'name' => 'submenu_styling',
				'tab'  => 'regular',
			],
			'preview'     => $preview_active_submenu,
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_html__( 'Close Icon Color', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the close icon color for flyout submenu.', 'fusion-builder' ),
			'param_name'  => 'flyout_close_color',
			'value'       => '',
			'default'     => '#212934',
			'group'       => esc_html__( 'Submenu', 'fusion-builder' ),
			'subgroup'    => [
				'name' => 'submenu_styling',
				'tab'  => 'regular',
			],
			'dependency'  => [
				[
					'element'  => 'submenu_mode',
					'value'    => 'flyout',
					'operator' => '==',
				],
			],
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_html__( 'Submenu Hover / Active Background Color', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the background-color for submenu items hover / active states.', 'fusion-builder' ),
			'param_name'  => 'submenu_active_bg',
			'value'       => '',
			'default'     => '#f9f9fb',
			'group'       => esc_html__( 'Submenu', 'fusion-builder' ),
			'subgroup'    => [
				'name' => 'submenu_styling',
				'tab'  => 'hover',
			],
			'preview'     => $preview_active_submenu_item,
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_html__( 'Submenu Hover / Active Text Color', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the text color for submenu items hover / active states', 'fusion-builder' ),
			'param_name'  => 'submenu_active_color',
			'value'       => '',
			'default'     => '#212934',
			'group'       => esc_html__( 'Submenu', 'fusion-builder' ),
			'subgroup'    => [
				'name' => 'submenu_styling',
				'tab'  => 'hover',
			],
			'preview'     => $preview_active_submenu_item,
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_html__( 'Close Icon Hover Color', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the close icon hover color for flyout submenu.', 'fusion-builder' ),
			'param_name'  => 'flyout_active_close_color',
			'value'       => '',
			'default'     => '#212934',
			'group'       => esc_html__( 'Submenu', 'fusion-builder' ),
			'subgroup'    => [
				'name' => 'submenu_styling',
				'tab'  => 'hover',
			],
			'dependency'  => [
				[
					'element'  => 'submenu_mode',
					'value'    => 'flyout',
					'operator' => '==',
				],
			],
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_html__( 'Collapse to Mobile Breakpoint', 'fusion-builder' ),
			'description' => esc_html__( 'The breakpoint at which your navigation will collapse to mobile mode.', 'fusion-builder' ),
			'param_name'  => 'breakpoint',
			'value'       => [
				'never'  => esc_html__( 'Never', 'fusion-builder' ),
				'small'  => esc_html__( 'Small Screen', 'fusion-builder' ),
				'medium' => esc_html__( 'Medium Screen', 'fusion-builder' ),
				'large'  => esc_html__( 'Large Screen', 'fusion-builder' ),
				'custom' => esc_html__( 'Custom', 'fusion-builder' ),
			],
			'icons'       => [
				'never'  => '<span class="fusiona-close-fb onlyIcon"></span>',
				'small'  => '<span class="fusiona-mobile onlyIcon"></span>',
				'medium' => '<span class="fusiona-tablet onlyIcon"></span>',
				'large'  => '<span class="fusiona-desktop onlyIcon"></span>',
				'custom' => '<span class="fusiona-cog onlyIcon"></span>',
			],
			'default'     => 'medium',
			'callback'    => [
				'function' => 'fusion_ajax',
				'action'   => 'get_fusion_menu',
				'ajax'     => true,
			],
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
		],
		[
			'type'        => 'range',
			'heading'     => esc_html__( 'Collapse to Mobile Breakpoint', 'fusion-builder' ),
			'description' => esc_html__( 'The breakpoint at which your menu will collapse to mobile mode. In pixels.', 'fusion-builder' ),
			'param_name'  => 'custom_breakpoint',
			'value'       => '800',
			'min'         => '360',
			'max'         => '2000',
			'step'        => '1',
			'callback'    => [
				'function' => 'fusion_ajax',
				'action'   => 'get_fusion_menu',
				'ajax'     => true,
			],
			'dependency'  => [
				[
					'element'  => 'breakpoint',
					'value'    => 'custom',
					'operator' => '==',
				],
			],
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_html__( 'Mobile Menu Mode', 'fusion-builder' ),
			'description' => esc_html__( 'Choose if you want the mobile menu to be collapsed to a button, or always expanded.', 'fusion-builder' ),
			'param_name'  => 'mobile_nav_mode',
			'value'       => [
				'collapse-to-button' => esc_html__( 'Collapsed', 'fusion-builder' ),
				'always-expanded'    => esc_html__( 'Expanded', 'fusion-builder' ),
			],
			'default'     => 'collapse-to-button',
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
		],
		[
			'type'        => 'select',
			'heading'     => esc_html__( 'Mobile Menu Expand Mode', 'fusion-builder' ),
			'description' => esc_html__( 'Change the width and position of expanded mobile menus.', 'fusion-builder' ),
			'param_name'  => 'mobile_nav_size',
			'value'       => [
				'column-relative' => esc_html__( 'Within Column - Normal', 'fusion-builder' ),
				'column-absolute' => esc_html__( 'Within Column - Static', 'fusion-builder' ),
				'full-absolute'   => esc_html__( 'Full Width - Static', 'fusion-builder' ),
			],
			'default'     => 'full-absolute',
			'dependency'  => [
				[
					'element'  => 'mobile_nav_mode',
					'value'    => 'collapse-to-button',
					'operator' => '==',
				],
			],
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
		],
		[
			'type'        => 'dimension',
			'heading'     => esc_html__( 'Mobile Menu Trigger Padding', 'fusion-builder' ),
			'description' => esc_html__( 'Select the padding for your mobile menu\'s expand / collapse button. Enter values including any valid CSS unit, ex: 10px.', 'fusion-builder' ),
			'param_name'  => 'trigger_padding',
			'value'       => [
				'trigger_padding_top'    => '',
				'trigger_padding_right'  => '',
				'trigger_padding_bottom' => '',
				'trigger_padding_left'   => '',
			],
			'dependency'  => [
				[
					'element'  => 'mobile_nav_mode',
					'value'    => 'collapse-to-button',
					'operator' => '==',
				],
			],
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_html__( 'Mobile Menu Trigger Background Color', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the background-color for the mobile menu trigger.', 'fusion-builder' ),
			'param_name'  => 'mobile_trigger_background_color',
			'value'       => '',
			'default'     => '#ffffff',
			'dependency'  => [
				[
					'element'  => 'mobile_nav_mode',
					'value'    => 'collapse-to-button',
					'operator' => '==',
				],
			],
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_html__( 'Mobile Menu Trigger Text Color', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the text-color for the mobile menu trigger.', 'fusion-builder' ),
			'param_name'  => 'mobile_trigger_color',
			'value'       => '',
			'default'     => '#4a4e57',
			'dependency'  => [
				[
					'element'  => 'mobile_nav_mode',
					'value'    => 'collapse-to-button',
					'operator' => '==',
				],
			],
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'textfield',
			'heading'     => esc_html__( 'Mobile Menu Trigger Text', 'fusion-builder' ),
			'description' => esc_html__( 'The text shown next to the mobile menu trigger icon.', 'fusion-builder' ),
			'param_name'  => 'collapsed_nav_text',
			'value'       => '',
			'default'     => '',
			'dependency'  => [
				[
					'element'  => 'mobile_nav_mode',
					'value'    => 'collapse-to-button',
					'operator' => '==',
				],
			],
			'callback'    => [
				'function' => 'fusion_ajax',
				'action'   => 'get_fusion_menu',
				'ajax'     => true,
			],
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
		],
		[
			'type'        => 'iconpicker',
			'heading'     => esc_html__( 'Mobile Menu Trigger Expand Icon', 'fusion-builder' ),
			'param_name'  => 'collapsed_nav_icon_open',
			'value'       => 'fa-bars fas',
			'default'     => 'fa-bars fas',
			'description' => esc_html__( 'Select icon for expanding / opening the menu.', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_ajax',
				'action'   => 'get_fusion_menu',
				'ajax'     => true,
			],
			'dependency'  => [
				[
					'element'  => 'mobile_nav_mode',
					'value'    => 'collapse-to-button',
					'operator' => '==',
				],
			],
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
		],
		[
			'type'        => 'iconpicker',
			'heading'     => esc_html__( 'Mobile Menu Trigger Collapse Icon', 'fusion-builder' ),
			'param_name'  => 'collapsed_nav_icon_close',
			'value'       => 'fa-times fas',
			'default'     => 'fa-times fas',
			'description' => esc_html__( 'Select icon for collapsing / closing the menu.', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_ajax',
				'action'   => 'get_fusion_menu',
				'ajax'     => true,
			],
			'dependency'  => [
				[
					'element'  => 'mobile_nav_mode',
					'value'    => 'collapse-to-button',
					'operator' => '==',
				],
			],
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
		],
		[
			'type'        => 'textfield',
			'heading'     => esc_html__( 'Mobile Menu Trigger Font Size', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the size of the mobile menu trigger. Font-Size In pixels.', 'fusion-builder' ),
			'param_name'  => 'mobile_trigger_font_size',
			'value'       => '',
			'default'     => '',
			'dependency'  => [
				[
					'element'  => 'mobile_nav_mode',
					'value'    => 'collapse-to-button',
					'operator' => '==',
				],
			],
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_html__( 'Mobile Menu Trigger Horizontal Align', 'fusion-builder' ),
			'description' => esc_html__( 'Change the horizontal alignment of the collapse / expand button within its container column.', 'fusion-builder' ),
			'param_name'  => 'mobile_nav_button_align_hor',
			'grid_layout' => true,
			'back_icons'  => true,
			'icons'       => [
				'flex-start' => '<span class="fusiona-horizontal-flex-start"></span>',
				'center'     => '<span class="fusiona-horizontal-flex-center"></span>',
				'flex-end'   => '<span class="fusiona-horizontal-flex-end"></span>',
			],
			'value'       => [
				'flex-start' => esc_html__( 'Flex Start', 'fusion-builder' ),
				'center'     => esc_html__( 'Center', 'fusion-builder' ),
				'flex-end'   => esc_html__( 'Flex End', 'fusion-builder' ),
			],
			'default'     => 'flex-start',
			'dependency'  => [
				[
					'element'  => 'mobile_nav_mode',
					'value'    => 'collapse-to-button',
					'operator' => '==',
				],
			],
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_html__( 'Mobile Menu Trigger Button Full Width', 'fusion-builder' ),
			'description' => esc_html__( 'Turn on to make the mobile menu trigger button span full-width.', 'fusion-builder' ),
			'param_name'  => 'mobile_nav_trigger_fullwidth',
			'value'       => [
				'on'  => esc_html__( 'On', 'fusion-builder' ),
				'off' => esc_html__( 'Off', 'fusion-builder' ),
			],
			'default'     => 'off',
			'dependency'  => [
				[
					'element'  => 'mobile_nav_mode',
					'value'    => 'collapse-to-button',
					'operator' => '==',
				],
			],
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
		],
		[
			'type'        => 'textfield',
			'heading'     => esc_html__( 'Mobile Menu Trigger Bottom Margin', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the space between the mobile menu trigger and expanded mobile menu.', 'fusion-builder' ),
			'param_name'  => 'mobile_nav_trigger_bottom_margin',
			'value'       => '',
			'default'     => '',
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
			'dependency'  => [
				[
					'element'  => 'mobile_nav_mode',
					'value'    => 'collapse-to-button',
					'operator' => '==',
				],
			],
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'range',
			'heading'     => esc_html__( 'Mobile Menu Item Minimum Height', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the height of each menu item. In pixels.', 'fusion-builder' ),
			'param_name'  => 'mobile_nav_items_height',
			'value'       => '65',
			'min'         => '10',
			'max'         => '200',
			'step'        => '1',
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'textfield',
			'heading'     => esc_html__( 'Mobile Menu Sticky Maximum Height', 'fusion-builder' ),
			'description' => esc_html__( 'The maximum height for mobile main menu links when the container is sticky. Use any valid CSS unit. ', 'fusion-builder' ),
			'param_name'  => 'mobile_sticky_max_height',
			'value'       => '',
			'dependency'  => [
				[
					'element'  => 'fusion_builder_container',
					'param'    => 'sticky',
					'value'    => 'on',
					'operator' => '==',
				],
			],
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_html__( 'Mobile Menu Text Align', 'fusion-builder' ),
			'description' => esc_html__( 'Select if mobile menu items should be aligned to the left, right or centered.', 'fusion-builder' ),
			'param_name'  => 'mobile_justify_content',
			'value'       => [
				'left'   => esc_html__( 'Left', 'fusion-builder' ),
				'center' => esc_html__( 'Center', 'fusion-builder' ),
				'right'  => esc_html__( 'Right', 'fusion-builder' ),
			],
			'default'     => 'left',
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_html__( 'Mobile Menu Indent Submenus', 'fusion-builder' ),
			'description' => esc_html__( 'Turn on to enable identation for submenus.', 'fusion-builder' ),
			'param_name'  => 'mobile_indent_submenu',
			'value'       => [
				'on'  => esc_html__( 'On', 'fusion-builder' ),
				'off' => esc_html__( 'Off', 'fusion-builder' ),
			],
			'default'     => 'on',
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'             => 'typography',
			'heading'          => esc_attr__( 'Mobile Menu Typography', 'fusion-builder' ),
			'description'      => esc_html__( 'Controls the typography of the mobile menu. Leave empty for the global font family.', 'fusion-builder' ),
			'param_name'       => 'mobile_fonts',
			'choices'          => [
				'font-family'    => 'mobile_typography',
				'font-size'      => 'mobile_font_size',
				'text-transform' => 'mobile_text_transform',
				'line-height'    => 'mobile_line_height',
				'letter-spacing' => 'mobile_letter_spacing',
			],
			'default'          => [
				'font-family' => '',
				'variant'     => '400',
			],
			'remove_from_atts' => true,
			'global'           => true,
			'group'            => esc_attr__( 'Mobile', 'fusion-builder' ),
			'callback'         => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_html__( 'Mobile Menu Separator Color', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the color for mobile menu separators.', 'fusion-builder' ),
			'param_name'  => 'mobile_sep_color',
			'value'       => '',
			'default'     => 'rgba(0,0,0,0.1)',
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'             => 'subgroup',
			'heading'          => esc_html__( 'Mobile Menu Item Styling', 'fusion-builder' ),
			'description'      => esc_html__( 'Use filters to see specific type of content.', 'fusion-builder' ),
			'param_name'       => 'mobile_styling',
			'default'          => 'regular',
			'group'            => esc_html__( 'Mobile', 'fusion-builder' ),
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
			'heading'     => esc_html__( 'Mobile Menu Background Color', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the background color for mobile menus.', 'fusion-builder' ),
			'param_name'  => 'mobile_bg',
			'value'       => '',
			'default'     => '#ffffff',
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
			'subgroup'    => [
				'name' => 'mobile_styling',
				'tab'  => 'regular',
			],
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_html__( 'Mobile Menu Text Color', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the text color for mobile menus.', 'fusion-builder' ),
			'param_name'  => 'mobile_color',
			'value'       => '',
			'default'     => '#4a4e57',
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
			'subgroup'    => [
				'name' => 'mobile_styling',
				'tab'  => 'regular',
			],
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_html__( 'Mobile Menu Active Item Background Color', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the background color for mobile menu hover / active states.', 'fusion-builder' ),
			'param_name'  => 'mobile_active_bg',
			'value'       => '',
			'default'     => '#f9f9fb',
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
			'subgroup'    => [
				'name' => 'mobile_styling',
				'tab'  => 'active',
			],
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		[
			'type'        => 'colorpickeralpha',
			'heading'     => esc_html__( 'Mobile Menu Active Item Text Color', 'fusion-builder' ),
			'description' => esc_html__( 'Controls the text color for mobile menu hover / active states.', 'fusion-builder' ),
			'param_name'  => 'mobile_active_color',
			'value'       => '',
			'default'     => '#4a4e57',
			'group'       => esc_html__( 'Mobile', 'fusion-builder' ),
			'subgroup'    => [
				'name' => 'mobile_styling',
				'tab'  => 'active',
			],
			'callback'    => [
				'function' => 'fusion_menu',
			],
		],
		'fusion_animation_placeholder'         => [
			'preview_selector' => '.fusion-menu-element-wrapper',
		],
	];

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Menu',
			[
				'name'         => esc_html__( 'Menu', 'fusion-builder' ),
				'shortcode'    => 'fusion_menu',
				'icon'         => 'fusiona-bars',
				'params'       => $params,
				'help_url'     => 'https://theme-fusion.com/documentation/avada/elements/menu-element/',
				'subparam_map' => [
					'margin_top'                           => 'margin',
					'margin_bottom'                        => 'margin',
					'items_padding_top'                    => 'items_padding',
					'items_padding_right'                  => 'items_padding',
					'items_padding_bottom'                 => 'items_padding',
					'items_padding_left'                   => 'items_padding',
					'border_radius_top_left'               => 'border_radius',
					'border_radius_top_right'              => 'border_radius',
					'border_radius_bottom_right'           => 'border_radius',
					'border_radius_bottom_left'            => 'border_radius',
					'thumbnail_size_width'                 => 'thumbnail_size',
					'thumbnail_size_height'                => 'thumbnail_size',
					'border_top'                           => 'border',
					'border_right'                         => 'border',
					'border_bottom'                        => 'border',
					'border_left'                          => 'border',
					'submenu_items_padding_top'            => 'submenu_items_padding',
					'submenu_items_padding_right'          => 'submenu_items_padding',
					'submenu_items_padding_bottom'         => 'submenu_items_padding',
					'submenu_items_padding_left'           => 'submenu_items_padding',
					'submenu_border_radius_top_left'       => 'submenu_border_radius',
					'submenu_border_radius_top_right'      => 'submenu_border_radius',
					'submenu_border_radius_bottom_right'   => 'submenu_border_radius',
					'submenu_border_radius_bottom_left'    => 'submenu_border_radius',
					'trigger_padding_top'                  => 'trigger_padding',
					'trigger_padding_right'                => 'trigger_padding',
					'trigger_padding_bottom'               => 'trigger_padding',
					'trigger_padding_left'                 => 'trigger_padding',
					'font_size'                            => 'main_menu_fonts',
					'fusion_font_family_typography'        => 'main_menu_fonts',
					'fusion_font_variant_typography'       => 'main_menu_fonts',
					'letter_spacing'                       => 'main_menu_fonts',
					'text_transform'                       => 'main_menu_fonts',
					'line_height'                          => 'main_menu_fonts',
					'submenu_font_size'                    => 'submenu_fonts',
					'fusion_font_family_submenu_typography' => 'submenu_fonts',
					'fusion_font_variant_submenu_typography' => 'submenu_fonts',
					'submenu_text_transform'               => 'submenu_fonts',
					'submenu_line_height'                  => 'submenu_fonts',
					'submenu_letter_spacing'               => 'submenu_fonts',
					'mobile_font_size'                     => 'mobile_fonts',
					'fusion_font_family_mobile_typography' => 'mobile_fonts',
					'fusion_font_variant_mobile_typography' => 'mobile_fonts',
					'mobile_text_transform'                => 'mobile_fonts',
					'mobile_line_height'                   => 'mobile_fonts',
					'mobile_letter_spacing'                => 'mobile_fonts',
				],
				'callback'     => [
					'function' => 'fusion_ajax',
					'action'   => 'get_fusion_menu',
					'ajax'     => true,
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_menu' );
