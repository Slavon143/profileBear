<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 2.2.0
 */

if ( fusion_is_element_enabled( 'fusion_widget' ) ) {

	if ( ! class_exists( 'FusionSC_Widget' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 2.2.0
		 */
		class FusionSC_Widget extends Fusion_Element {

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 2.2.0
			 * @var array
			 */
			protected $args;

			/**
			 * The widget counter.
			 *
			 * @access private
			 * @since 2.2.0
			 * @var int
			 */
			private $widget_counter = 1;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 2.2.0
			 */
			public function __construct() {
				parent::__construct();

				add_filter( 'fusion_builder_all_elements', [ $this, 'map_style_options_to_params' ] );

				if ( ! fusion_is_preview_frame() ) {
					add_filter( 'fusion_attr_widget-shortcode', [ $this, 'attr' ] );
				}

				add_action( 'wp_ajax_fusion_get_widget_markup', [ $this, 'get_widget_markup' ] );
				add_action( 'wp_ajax_nopriv_fusion_get_widget_markup', [ $this, 'get_widget_markup' ] );

				add_shortcode( 'fusion_widget', [ $this, 'render' ] );

				// If preview frame.
				if ( fusion_is_preview_frame() ) {
					add_action( 'wp_print_footer_scripts', [ $this, 'preview_scripts' ], 999 );
					add_action( 'wp_enqueue_scripts', [ $this, 'preview_styles' ], 999 );
					add_action( 'wp_footer', [ $this, 'widget_assets' ] );
				}

				// if builder frame.
				if ( fusion_is_builder_frame() ) {
					add_action( 'wp_enqueue_scripts', [ $this, 'live_scripts' ], 998 );
					add_action( 'wp_enqueue_scripts', [ $this, 'widget_wp_scripts' ], 999 );
					add_action( 'wp_footer', [ $this, 'core_widget_templates' ] );
				}

				if ( is_fusion_editor() && is_admin() ) {
					add_action( 'admin_enqueue_scripts', [ $this, 'live_scripts' ], 998 );
					add_action( 'admin_enqueue_scripts', [ $this, 'widget_wp_scripts' ], 999 );
					add_action( 'admin_footer', [ $this, 'core_widget_templates' ] );
				}
			}

			/**
			 * Enqueue widget core scripts.
			 *
			 * @access public
			 * @since 2.2.0
			 * @return void
			 */
			public function widget_wp_scripts() {
				do_action( 'admin_print_scripts-widgets.php' ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
			}

			/**
			 * Enqueue scripts.
			 *
			 * @since 2.2.0
			 * @param mixed $hook The hook.
			 */
			public function live_scripts( $hook ) {
				$suffix  = FUSION_BUILDER_DEV_MODE ? '' : '.min';
				$version = class_exists( 'Avada' ) ? Fusion_Helper::normalize_version( Avada::get_theme_version() ) : false;

				wp_enqueue_script( 'editor' );
				wp_enqueue_script( 'media-models' );
				wp_enqueue_script( 'media-views' );
				wp_enqueue_script( 'media-widgets', "/wp-admin/js/widgets/media-widgets$suffix.js", [], $version, false );
				wp_enqueue_script( 'media-audiovideo' );
				wp_enqueue_script( 'media-audio-widget', "/wp-admin/js/widgets/media-audio-widget$suffix.js", [], $version, false );
				wp_enqueue_script( 'media-image-widget', "/wp-admin/js/widgets/media-image-widget$suffix.js", [], $version, false );
				wp_enqueue_script( 'media-video-widget', "/wp-admin/js/widgets/media-video-widget$suffix.js", [], $version, false );
				wp_enqueue_script( 'media-gallery-widget', "/wp-admin/js/widgets/media-gallery-widget$suffix.js", [], $version, false );
				wp_enqueue_script( 'text-widgets', "/wp-admin/js/widgets/text-widgets$suffix.js", [], $version, false );
				wp_enqueue_script( 'custom-html-widgets', "/wp-admin/js/widgets/custom-html-widgets$suffix.js", [], $version, false );
				wp_enqueue_script( 'wp-mediaelement' );
				wp_enqueue_style( 'widgets' );
				wp_enqueue_style( 'media-views' );
			}

			/**
			 * Add the core WP widget underscore tempaltes.
			 *
			 * @access public
			 * @since 2.0.0
			 * @return void
			 */
			public function core_widget_templates() {
				do_action( 'admin_footer-widgets.php' ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
			}

			/**
			 * Enqueue scripts and styles.
			 *
			 * @since 2.2.0
			 * @param mixed $hook The hook.
			 */
			public function preview_styles( $hook ) {
				wp_enqueue_style( 'wp-mediaelement' );
			}

			/**
			 * Load required assets.
			 *
			 * @access public
			 * @since 2.0.0
			 */
			public function widget_assets() {
				// Widget required preview assets.
				echo '<script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>'; // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
				?>
				<script>
					jQuery( document ).on( 'fusion-widget-render-Fusion_Widget_Tweets', function(){
						setTimeout( function() {
							if ( 'object' === typeof twttr ) {
								twttr.widgets.load();
							}
						}, 200 );
					});
				</script>
				<?php
			}

			/**
			 * Enqueue scripts and styles.
			 *
			 * @since 2.2.0
			 * @param mixed $hook The hook.
			 */
			public function preview_scripts( $hook ) {
				if ( ! wp_script_is( 'wp-mediaelement', 'enqueued' ) ) {
					wp_print_scripts( 'wp-mediaelement' );
				}

				$this->add_facebook_scripts();
			}

			/**
			 * Adds the facebook script.
			 *
			 * @access public
			 * @since 2.2.0
			 * @return void
			 */
			public function add_facebook_scripts() {
				?>
				<script>
					(function(d, s, id) {
						var js, fjs = d.getElementsByTagName(s)[0];
						if (d.getElementById(id)) return;
						js = d.createElement(s); js.id = id;

						let lang = 'en_US';
						const el = document.querySelector('.fusion-facebook-page');
						if ( el ) {
							lang = el.dataset.language;
						}
						js.src = "https://connect.facebook.net/"+ lang +"/sdk.js#xfbml=1&version=v2.11&appId=";
						fjs.parentNode.insertBefore(js, fjs);
					}(document, 'script', 'facebook-jssdk'));
				</script>
				<?php
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 2.2.0
			 * @return array
			 */
			public function attr() {
				$attr   = [];
				$styles = '';

				$attr['class'] = 'fusion-widget fusion-widget-element fusion-widget-area fusion-content-widget-area ' . $this->element_id;

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['fusion_align'] ) {
					$attr['class'] .= ' fusion-widget-align-' . $this->args['fusion_align'];
				}

				if ( $this->args['fusion_align_mobile'] ) {
					$attr['class'] .= ' fusion-widget-mobile-align-' . $this->args['fusion_align_mobile'];
				}

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				$attr['id'] = $this->args['id'];

				$styles .= ( $this->args['fusion_bg_color'] ) ? 'background-color:' . $this->args['fusion_bg_color'] . ';' : '';
				$styles .= ( $this->args['fusion_padding_color'] ) ? 'padding:' . $this->args['fusion_padding_color'] . ';' : '';
				$styles .= ( $this->args['fusion_border_size'] ) ? 'border-width:' . $this->args['fusion_border_size'] . ';' : '';
				$styles .= ( $this->args['fusion_border_color'] ) ? 'border-color:' . $this->args['fusion_border_color'] . ';' : '';
				$styles .= ( $this->args['fusion_border_style'] && '' !== $this->args['fusion_border_size'] ) ? 'border-style:' . $this->args['fusion_border_style'] . ';' : '';
				$styles .= ( $this->args['fusion_bg_radius_size'] ) ? 'border-radius:' . $this->args['fusion_bg_radius_size'] . ';' : '';
				$styles .= ( $this->args['fusion_margin'] ) ? 'margin:' . $this->args['fusion_margin'] . ';' : '';

				if ( $styles ) {
					$attr['style'] = $styles;
				}

				$attr['style'] .= Fusion_Builder_Margin_Helper::get_margins_style( $this->args );

				return $attr;
			}

			/**
			 * Builds the dynamic styling.
			 *
			 * @access public
			 * @since 1.1
			 * @return array
			 */
			public function add_styling() {
				global $content_media_query;

				$css[ $content_media_query ]['.fusion-widget.fusion-widget-mobile-align-left']['text-align']   = 'left';
				$css[ $content_media_query ]['.fusion-widget.fusion-widget-mobile-align-right']['text-align']  = 'right';
				$css[ $content_media_query ]['.fusion-widget.fusion-widget-mobile-align-center']['text-align'] = 'center';

				return $css;
			}

			/**
			 * Maps style-options to our parameters.
			 *
			 * @access public
			 * @since 2.2.0
			 * @param array $all_elements An array of all our elements.
			 * @return array              Returns $all_elements after modifications.
			 */
			public function map_style_options_to_params( $all_elements ) {
				if ( ! class_exists( 'Avada' ) || ! class_exists( 'Avada_Widget_Style' ) ) {
					return $all_elements;
				}

				$style_options = Avada_Widget_Style::get_instance()->widget_options;
				$widget_styles = [];

				foreach ( $style_options as $option ) {

					if ( 'text' === $option['type'] ) {
						$option['type'] = 'textfield';
					}

					$widget_styles[ $option['key'] ]['type']        = $option['type'];
					$widget_styles[ $option['key'] ]['param_name']  = $option['key'];
					$widget_styles[ $option['key'] ]['group']       = esc_attr__( 'Design', 'fusion-builder' );
					$widget_styles[ $option['key'] ]['description'] = $option['description'];
					$widget_styles[ $option['key'] ]['heading']     = $option['title'];
					$widget_styles[ $option['key'] ]['value']       = '';

					if ( isset( $option['css_property'] ) ) {
						$widget_styles[ $option['key'] ]['css_property'] = $option['css_property'];
					}

					if ( isset( $option['dependency'] ) ) {
						$widget_styles[ $option['key'] ]['dependency'] = $option['dependency'];
					}

					if ( isset( $option['default'] ) ) {
						$widget_styles[ $option['key'] ]['default'] = $option['default'];
					}

					if ( 'range' === $option['type'] ) {
						$widget_styles[ $option['key'] ]['min']   = $option['min'];
						$widget_styles[ $option['key'] ]['max']   = $option['max'];
						$widget_styles[ $option['key'] ]['step']  = $option['step'];
						$widget_styles[ $option['key'] ]['value'] = $option['value'];
					}

					if ( 'select' === $option['type'] || 'radio_button_set' === $option['type'] ) {
						$widget_styles[ $option['key'] ]['value'] = $option['choices'];
					}
				};

				$all_elements['fusion_widget']['params'] = array_merge( $all_elements['fusion_widget']['params'], $widget_styles );

				return $all_elements;
			}

			/**
			 * Adds the script for Events-Calendar widgets.
			 *
			 * @access public
			 * @since 2.2.0
			 * @param string $classname The widget's class-name.
			 * @return string
			 */
			public function events_pro_scripts( $classname ) {
				$url = '';

				switch ( $classname ) {
					case 'Tribe__Events__Pro__Countdown_Widget':
						$url = tribe_events_pro_resource_url( 'widget-countdown.js' );
						break;

					case 'Tribe__Events__Pro__Mini_Calendar_Widget':
						$url = tribe_events_pro_resource_url( 'widget-calendar.js' );
						break;

					case 'Tribe__Events__Pro__This_Week_Widget':
						$url = tribe_events_pro_resource_url( 'widget-this-week.js' );
						break;

					default:
						break;
				}

				return $url ? '<script src="' . $url . '"></script>' : ''; // phpcs:ignore WordPress.WP.EnqueuedResources
			}

			/**
			 * Fetch markup for specific widget instance.
			 *
			 * @access public
			 * @since 2.2.0
			 * @return void
			 */
			public function get_widget_markup() {
				check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );

				$content   = '';
				$instance  = [];
				$args      = [];
				$widget    = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
				$params    = isset( $_POST['params'] ) ? stripslashes_deep( wp_unslash( $_POST['params'] ) ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				$widget_id = isset( $_POST['widget_id'] ) ? sanitize_text_field( wp_unslash( $_POST['widget_id'] ) ) : random_int( 100, 1000 );
				$prefix    = str_replace( '\\', '_', strtolower( $widget ) ) . '__';

				if ( 'default' === $widget ) {
					echo wp_json_encode( '' );
					wp_die();
				}

				foreach ( $params as $key => $param ) {
					if ( 0 === strpos( $key, $prefix ) ) {
						$instance[ substr( $key, strlen( $prefix ) ) ] = 'off' === $param ? '' : $param;
					}
				}

				$args = [
					'before_title' => '<div class="heading"><h4 class="widget-title">',
					'after_title'  => '</h4></div>',
					'widget_id'    => $this->get_widget_id( $widget_id ),
				];

				if ( class_exists( 'Tribe__Events__Pro__Main' ) ) {
					$content .= $this->events_pro_scripts( $widget );
				}

				ob_start();
				the_widget( $widget, $instance, $args );
				$content .= ob_get_clean();

				echo wp_json_encode( $content );
				wp_die();
			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 2.2.0
			 * @return array
			 */
			public static function get_element_defaults() {
				return [
					'margin_top'            => '',
					'margin_right'          => '',
					'margin_bottom'         => '',
					'margin_left'           => '',
					'hide_on_mobile'        => fusion_builder_default_visibility( 'string' ),
					'class'                 => '',
					'id'                    => '',
					'type'                  => '',
					'fusion_bg_color'       => '',
					'fusion_padding_color'  => '',
					'fusion_border_style'   => '',
					'fusion_border_color'   => '',
					'fusion_border_size'    => '',
					'fusion_bg_radius_size' => '',
					'fusion_margin'         => '',
					'fusion_align'          => '',
					'fusion_align_mobile'   => '',
					'fusion_display_title'  => '',
					'fusion_divider_color'  => '',
				];
			}

			/**
			 * Get the widget-ID.
			 *
			 * @access public
			 * @since 2.2.0
			 * @param int $id The widget id/counter as an integer.
			 * @return string Returns wpWidget-{$id}. If $id is null get int from counter.
			 */
			public function get_widget_id( $id = null ) {
				$key = 'wpWidget';
				$id  = $id ? $id : $this->widget_counter;
				return $key . '-' . $id;
			}

			/**
			 * Get the style tag with additional styles.
			 *
			 * @access public
			 * @since 3.0
			 * @param  array $instance The widget instance.
			 * @return string The style tag with styles.
			 */
			public function get_style_tag( $instance ) {
				$divider_styles = '';
				if ( isset( $this->args['fusion_divider_color'] ) && '' !== $this->args['fusion_divider_color'] ) {
					$fusion_divider_color = Fusion_Sanitize::color( $this->args['fusion_divider_color'] );
					$divider_styles       = '#wrapper .' . $this->element_id . ' li { border-color:' . $fusion_divider_color . ';}';

					if ( 'WP_Widget_Tag_Cloud' === $this->args['type'] ) {
						$divider_styles .= '#wrapper .' . $this->element_id . ' .tagcloud a { border-color:' . $fusion_divider_color . ';}';
					} elseif ( 'Fusion_Widget_Menu' === $this->args['type'] ) {
						$divider_styles .= '#wrapper .' . $this->element_id . ' .fusion-widget-menu ul li a:after { color:' . $fusion_divider_color . ';}';
					}
				}

				// Special handling for vertical menu widget, to take into account the deprecated border_color option.
				if ( 'Fusion_Widget_Vertical_Menu' === $this->args['type'] ) {
					if ( isset( $instance['border_color'] ) && ! isset( $args['fusion_divider_color'] ) ) {
						$this->args['fusion_divider_color'] = $instance['border_color'];
					}

					if ( '' !== $this->args['fusion_divider_color'] ) {
						$fusion_divider_color = Fusion_Sanitize::color( $this->args['fusion_divider_color'] );

						$divider_styles .= '#wrapper .' . $this->element_id . ' .menu { border-right-color:' . $fusion_divider_color . ' !important; border-top-color:' . $fusion_divider_color . ' !important;}';
						$divider_styles .= '#wrapper .' . $this->element_id . ' .menu li a { border-bottom-color:' . $fusion_divider_color . ' !important; }';
						$divider_styles .= '#wrapper .' . $this->element_id . ' .right .menu { border-left-color:' . $fusion_divider_color . ' !important; }';
					} else {
						$divider_styles .= '#wrapper .' . $this->element_id . ' > ul.menu { margin-top: -8px; }'; // phpcs:ignore WordPress.Security.EscapeOutput
					}
				}

				if ( ! empty( $divider_styles ) ) {
					return '<style type="text/css" data-id="' . $this->element_id . '">' . $divider_styles . '</style>';
				}

				return '';
			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 2.2.0
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {
				global $wp_widget_factory;

				$this->set_element_id( $this->get_widget_id() );

				$defaults                          = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_widget' );
				$defaults['fusion_border_size']    = FusionBuilder::validate_shortcode_attr_value( $defaults['fusion_border_size'], 'px' );
				$defaults['fusion_bg_radius_size'] = FusionBuilder::validate_shortcode_attr_value( $defaults['fusion_bg_radius_size'], 'px' );

				extract( $defaults );

				$this->args = $defaults;

				$this->args['margin_bottom'] = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_bottom'], 'px' );
				$this->args['margin_left']   = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_left'], 'px' );
				$this->args['margin_right']  = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_right'], 'px' );
				$this->args['margin_top']    = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_top'], 'px' );

				$instance = [];

				if ( ! isset( $this->args['type'] ) || 'default' === $this->args['type'] ) {
					return;
				}

				if ( 'TribeEventsViewsV2WidgetsWidget_List' === $type ) {
					$prefix = 'tribe_events_views_v2_widgets_widget_list__';
				} else {
					$prefix = strtolower( $args['type'] ) . '__';
				}

				foreach ( $args as $key => $param ) {

					if ( 0 === strpos( $key, $prefix ) ) {
						$instance[ substr( $key, strlen( $prefix ) ) ] = 'off' === $param ? '' : $param;
					} elseif ( 'fusion_display_title' === $key ) {
						$instance[ $key ] = $param;
					}
				}

				// HTML and Text widget special unescape.
				if ( 'WP_Widget_Custom_HTML' === $type && isset( $instance['content'] ) ) {
					$instance['content'] = html_entity_decode( $instance['content'] );
				} elseif ( 'WP_Widget_Text' === $type && isset( $instance['text'] ) ) {
					$instance['text'] = html_entity_decode( $instance['text'] );
				} elseif ( 'TribeEventsViewsV2WidgetsWidget_List' === $type ) {

					// EC v2 views change list widget name.
					$type = 'Tribe\Events\Views\V2\Widgets\Widget_List';
				}

				$args = [
					'before_title' => '<div class="heading"><h4 class="widget-title">',
					'after_title'  => '</h4></div>',
					'widget_id'    => $this->widget_counter,
				];

				$html = '<div ' . FusionBuilder::attributes( 'widget-shortcode' ) . '>';

				if ( ! fusion_is_preview_frame() ) {
					$html .= $this->get_style_tag( $instance );
				}

				fusion_element_rendering_elements( true );
				if ( class_exists( 'Tribe__Events__Pro__Main' ) ) {
					$html .= $this->events_pro_scripts( $type );
				}

				if ( isset( $wp_widget_factory->widgets ) && array_key_exists( $type, $wp_widget_factory->widgets ) ) {
					ob_start();
					the_widget( $type, $instance, $args );
					$html .= ob_get_clean();
					fusion_element_rendering_elements( false );
				}

				$html .= '</div>';

				$this->widget_counter++;

				$this->on_render();

				return apply_filters( 'fusion_element_widget_content', $html, $args );
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/widget.min.css' );
			}
		}
	}

	new FusionSC_Widget();

}

/**
 * Map shortcode to Avada Builder
 *
 * @since 2.2.0
 */
function fusion_element_widget() {
	$widgets = is_fusion_editor() ? fusion_get_available_widgets() : [];

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Widget',
			[
				'name'                              => esc_attr__( 'Widget', 'fusion-builder' ),
				'shortcode'                         => 'fusion_widget',
				'icon'                              => 'fusion-module-icon fusiona-widget',
				'custom_settings_view_name'         => 'ModuleSettingsWidget',
				'custom_settings_view_js'           => FUSION_BUILDER_PLUGIN_URL . 'inc/templates/custom/js/fusion-widget-settings.js',
				'custom_settings_template_file'     => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/custom/front-end/fusion-widget-settings.php',
				'front_end_custom_settings_view_js' => FUSION_BUILDER_PLUGIN_URL . 'inc/templates/custom/front-end/js/fusion-widget-settings.js',
				'admin_enqueue_js'                  => FUSION_BUILDER_PLUGIN_URL . 'shortcodes/js/fusion-widget.js',
				'on_save'                           => 'widgetShortcodeFilter',
				'help_url'                          => 'https://theme-fusion.com/documentation/avada/elements/widget/',
				'preview'                           => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-widget-preview.php',
				'preview_id'                        => 'fusion-builder-block-module-widget-preview-template',
				'params'                            => [
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Widget', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose widget type.', 'fusion-builder' ),
						'param_name'  => 'type',
						'value'       => $widgets,
						'default'     => 'default',
						'callback'    => [
							'function' => 'fusion_widget_changed',
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
						'param_name'  => 'class',
						'value'       => '',
						'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
						'param_name'  => 'id',
						'value'       => '',
						'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
					],
				],
			]
		)
	);
}

add_action( 'wp_loaded', 'fusion_element_widget' );
