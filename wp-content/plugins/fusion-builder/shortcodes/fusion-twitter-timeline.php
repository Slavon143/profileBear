<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_twitter_timeline' ) ) {

	if ( ! class_exists( 'FusionSC_Twitter_Timeline' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @package fusion-builder
		 * @since 1.0
		 */
		class FusionSC_Twitter_Timeline extends Fusion_Element {

			/**
			 * The image-frame counter.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $ttl_counter = 1;

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array
			 */
			protected $args;

			/**
			 * An array of wrapper attributes.
			 *
			 * @access protected
			 * @since 3.0
			 * @var array
			 */
			protected $wrapper_attr = [
				'class' => '',
				'style' => '',
			];

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_twitter-timeline-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_twitter-timeline-iframe', [ $this, 'iframe_attr' ] );

				add_shortcode( 'fusion_twitter_timeline', [ $this, 'render' ] );
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
				$fusion_settings = awb_get_fusion_settings();
				return [
					'username'             => 'Theme_Fusion',
					'width'                => '340',
					'height'               => '500',
					'language'             => '',
					'theme'                => '',
					'header'               => 'show',
					'footer'               => 'show',
					'borders'              => 'show',
					'border_color'         => '',
					'transparent'          => 'no',
					'scrollbar'            => 'show',

					'alignment'            => '',
					// margin.
					'margin_top'           => '',
					'margin_right'         => '',
					'margin_bottom'        => '',
					'margin_left'          => '',
					'margin_top_medium'    => '',
					'margin_right_medium'  => '',
					'margin_bottom_medium' => '',
					'margin_left_medium'   => '',
					'margin_top_small'     => '',
					'margin_right_small'   => '',
					'margin_bottom_small'  => '',
					'margin_left_small'    => '',

					// css.
					'class'                => '',
					'id'                   => '',
					'id'                   => '',

					// animation.
					'animation_direction'  => 'left',
					'animation_offset'     => $fusion_settings->get( 'animation_offset' ),
					'animation_speed'      => '',
					'animation_type'       => '',

					// visibility.
					'hide_on_mobile'       => fusion_builder_default_visibility( 'string' ),

				];
			}

			/**
			 * Sets the args from the attributes.
			 *
			 * @access public
			 * @since 3.0
			 * @param array $args Element attributes.
			 * @return void
			 */
			public function set_args( $args ) {
				$this->defaults = self::get_element_defaults();
				$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_twitter_timeline' );
			}

			/**
			 * Return 'true' or 'false'
			 * form on and off
			 *
			 * @access public
			 * @since 1.0
			 * @param  string $value   on or off.
			 * @return string           true or false.
			 */
			public function is_true( $value ) {
				if ( 'on' === $value ) {
					return 'true';
				}
				return 'false';
			}
			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 1.0
			 * @param  array  $args    Shortcode paramters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {

				$this->set_element_id( $this->ttl_counter );

				$this->set_args( $args );

				$content         = apply_filters( 'fusion_shortcode_content', $content, 'fusion_twitter_timeline', $args );
				$element_styles  = '';
				$element_styles .= $this->build_margin_styles();

				// fix animation.
				if ( $this->args['animation_type'] ) {
					$element_styles .= '.fusion-twitter-timeline-' . $this->ttl_counter . ' iframe { visibility:unset !important;}';
				}

				if ( '' !== $this->args['alignment'] ) {
					$element_styles .= '.fusion-twitter-timeline-' . $this->ttl_counter . ' { display:flex; justify-content:' . $this->args['alignment'] . ';}';
				}

				if ( '' !== $element_styles ) {
					$element_styles = '<style>' . $element_styles . '</style>';
				}
				$html           = '';
				$consent_needed = class_exists( 'Avada_Privacy_Embeds' ) && Avada()->settings->get( 'privacy_embeds' ) && ! Avada()->privacy_embeds->get_consent( 'twitter' );
				if ( $consent_needed ) {
					$html .= Avada()->privacy_embeds->script_placeholder( 'twitter' ); // phpcs:ignore WordPress.Security.EscapeOutput
					$html .= '<span data-privacy-script="true" data-privacy-type="twitter" class="fusion-hidden" data-privacy-src="//platform.twitter.com/widgets.js"></span>';
					$html .= $element_styles . '<div ' . FusionBuilder::attributes( 'twitter-timeline-shortcode' ) . '><a ' . FusionBuilder::attributes( 'twitter-timeline-iframe' ) . ' ></a></div>'; // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
				} else {
					$html .= $element_styles . '<div ' . FusionBuilder::attributes( 'twitter-timeline-shortcode' ) . '><a ' . FusionBuilder::attributes( 'twitter-timeline-iframe' ) . ' >' . __( 'Tweets by', 'fusion-builder' ) . $this->args['username'] . '</a>
								<script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script></div>'; // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
				}

				$this->ttl_counter++;

				$this->on_render();

				return apply_filters( 'fusion_element_twitter_timeline_content', $html, $args );

			}
			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function attr() {

				$attr          = [];
				$attr['class'] = 'fusion-twitter-timeline twitter-timeline fusion-twitter-timeline-' . $this->ttl_counter . ' ' . $this->args['class'];
				$attr          = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				$attr['id'] = $this->args['id'];

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				return $attr;

			}
			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function iframe_attr() {

				$attr          = [];
				$attr['class'] = 'twitter-timeline';

				$attr['href'] = 'https://twitter.com/' . $this->args['username'];

				if ( '' !== $this->args['language'] ) {
					$attr['data-lang'] = $this->args['language'];
				}

				if ( '' !== $this->args['width'] ) {
					$attr['data-width'] = $this->args['width'];
				}

				if ( '' !== $this->args['height'] ) {
					$attr['data-height'] = $this->args['height'];
				}

				if ( '' !== $this->args['theme'] ) {
					$attr['data-theme'] = $this->args['theme'];
				}

				if ( 'hide' !== $this->args['borders'] && '' !== $this->args['border_color'] ) {
					$attr['data-border-color'] = $this->args['border_color'];
				}

				$chrome = '';
				if ( 'hide' === $this->args['header'] ) {
					$chrome .= ' noheader';
				}
				if ( 'hide' === $this->args['footer'] ) {
					$chrome .= ' nofooter';
				}
				if ( 'hide' === $this->args['borders'] ) {
					$chrome .= ' noborders';
				}
				if ( 'hide' === $this->args['scrollbar'] ) {
					$chrome .= ' noscrollbar';
				}
				if ( 'yes' === $this->args['transparent'] ) {
					$chrome .= ' transparent';
				}
				if ( '' !== $chrome ) {
					$attr['data-chrome'] = trim( $chrome );
				}

				return $attr;
			}
			/**
			 * Builds margin styles.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function build_margin_styles() {
				// Responsive Margin.
				$fusion_settings = awb_get_fusion_settings();
				$styles          = '';

				foreach ( [ 'large', 'medium', 'small' ] as $size ) {
					$margin_styles = '';
					foreach ( [ 'top', 'right', 'bottom', 'left' ] as $direction ) {

						$margin_key = 'large' === $size ? 'margin_' . $direction : 'margin_' . $direction . '_' . $size;
						if ( '' !== $this->args[ $margin_key ] ) {
							$margin_styles .= 'margin-' . $direction . ' : ' . fusion_library()->sanitize->get_value_with_unit( $this->args[ $margin_key ] ) . ';';
						}
					}

					if ( '' === $margin_styles ) {
						continue;
					}

					$margin_styles = '.fusion-twitter-timeline-' . $this->ttl_counter . '{ ' . $margin_styles . '}';

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

		}
	}

	new FusionSC_Twitter_Timeline();

}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_twitter_timeline_element() {

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Twitter_Timeline',
			[
				'name'         => esc_attr__( 'Twitter Timeline', 'fusion-builder' ),
				'shortcode'    => 'fusion_twitter_timeline',
				'icon'         => 'fusiona-twtiter-feed',
				'subparam_map' => [
					'margin_top'    => 'margin',
					'margin_right'  => 'margin',
					'margin_bottom' => 'margin',
					'margin_left'   => 'margin',
				],
				'params'       => [
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Twitter Username', 'fusion-builder' ),
						'description' => esc_attr__( 'Enter the username of the twitter timeline you want to display.', 'fusion-builder' ),
						'param_name'  => 'username',
						'value'       => 'Theme_Fusion',
					],
					[
						'type'        => 'dimension',
						'heading'     => esc_attr__( 'Dimensions', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the dimensions of the twitter timeline. In pixels.', 'fusion-builder' ),
						'param_name'  => 'dimension',
						'value'       => [
							'width'  => '340',
							'height' => '500',
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Language', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the language the twitter timeline should be displayed in.', 'fusion-builder' ),
						'param_name'  => 'language',
						'default'     => '',
						'value'       => [
							''      => esc_attr__( 'Automatic', 'fusion-builder' ),
							'en'    => esc_attr__( 'English', 'fusion-builder' ),
							'ar'    => esc_attr__( 'Arabic', 'fusion-builder' ),
							'bn'    => esc_attr__( 'Bengali', 'fusion-builder' ),
							'zh-cn' => esc_attr__( 'Chinese (Simplified)', 'fusion-builder' ),
							'zh-tw' => esc_attr__( 'Chinese (Traditional)', 'fusion-builder' ),
							'cs'    => esc_attr__( 'Czech', 'fusion-builder' ),
							'da'    => esc_attr__( 'Danish', 'fusion-builder' ),
							'nl'    => esc_attr__( 'Dutch', 'fusion-builder' ),
							'fil'   => esc_attr__( 'Filipino', 'fusion-builder' ),
							'fi'    => esc_attr__( 'Finnish', 'fusion-builder' ),
							'fr'    => esc_attr__( 'French', 'fusion-builder' ),
							'de'    => esc_attr__( 'German', 'fusion-builder' ),
							'el'    => esc_attr__( 'Greek', 'fusion-builder' ),
							'he'    => esc_attr__( 'Hebrew', 'fusion-builder' ),
							'hi'    => esc_attr__( 'Hindi', 'fusion-builder' ),
							'hu'    => esc_attr__( 'Hungarian', 'fusion-builder' ),
							'id'    => esc_attr__( 'Indonesian', 'fusion-builder' ),
							'it'    => esc_attr__( 'Italian', 'fusion-builder' ),
							'ja'    => esc_attr__( 'Japanese', 'fusion-builder' ),
							'ko'    => esc_attr__( 'Korean', 'fusion-builder' ),
							'msa'   => esc_attr__( 'Malay', 'fusion-builder' ),
							'no'    => esc_attr__( 'Norwegian', 'fusion-builder' ),
							'fa'    => esc_attr__( 'Persian', 'fusion-builder' ),
							'pl'    => esc_attr__( 'Polish', 'fusion-builder' ),
							'pt'    => esc_attr__( 'Portuguese', 'fusion-builder' ),
							'ro'    => esc_attr__( 'Romanian', 'fusion-builder' ),
							'ru'    => esc_attr__( 'Russian', 'fusion-builder' ),
							'es'    => esc_attr__( 'Spanish', 'fusion-builder' ),
							'sv'    => esc_attr__( 'Swedish', 'fusion-builder' ),
							'th'    => esc_attr__( 'Thai', 'fusion-builder' ),
							'tr'    => esc_attr__( 'Turkish', 'fusion-builder' ),
							'uk'    => esc_attr__( 'Ukrainian', 'fusion-builder' ),
							'ur'    => esc_attr__( 'Urdu', 'fusion-builder' ),
							'vi'    => esc_attr__( 'Vietnamese', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Theme', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the theme of the twitter timeline.', 'fusion-builder' ),
						'param_name'  => 'theme',
						'default'     => 'light',
						'value'       => [
							'light' => esc_attr__( 'Light', 'fusion-builder' ),
							'dark'  => esc_attr__( 'dark', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Header', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to show or hide the header.', 'fusion-builder' ),
						'param_name'  => 'header',
						'default'     => 'show',
						'value'       => [
							'show' => esc_attr__( 'Show', 'fusion-builder' ),
							'hide' => esc_attr__( 'Hide', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Footer', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to show or hide the footer.', 'fusion-builder' ),
						'param_name'  => 'footer',
						'default'     => 'show',
						'value'       => [
							'show' => esc_attr__( 'Show', 'fusion-builder' ),
							'hide' => esc_attr__( 'Hide', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Borders', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to show or hide the borders.', 'fusion-builder' ),
						'param_name'  => 'borders',
						'default'     => 'show',
						'value'       => [
							'show' => esc_attr__( 'Show', 'fusion-builder' ),
							'hide' => esc_attr__( 'Hide', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'colorpicker',
						'heading'     => esc_attr__( 'Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the color of element borders, including the border between tweets.', 'fusion-builder' ),
						'param_name'  => 'border_color',
						'default'     => '',
						'dependency'  => [
							[
								'element'  => 'borders',
								'value'    => 'show',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Transparent Background', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose whether you want a transparent timeline background.', 'fusion-builder' ),
						'param_name'  => 'transparent',
						'default'     => 'no',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Scrollbar', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to show or crop and hide the timeline scrollbar, if visible.', 'fusion-builder' ),
						'param_name'  => 'scrollbar',
						'default'     => 'show',
						'value'       => [
							'show' => esc_attr__( 'Show', 'fusion-builder' ),
							'hide' => esc_attr__( 'Hide', 'fusion-builder' ),
						],
					],
					'fusion_margin_placeholder'            => [
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
						'callback'   => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose how the element should align inside the Column.', 'fusion-builder' ),
						'param_name'  => 'alignment',
						'default'     => 'flex-start',
						'value'       => [
							'flex-start' => esc_attr__( 'Flex Start', 'fusion-builder' ),
							'center'     => esc_attr__( 'Center', 'fusion-builder' ),
							'flex-end'   => esc_attr__( 'Flex End', 'fusion-builder' ),
						],
						'icons'       => [
							'flex-start' => '<span class="fusiona-horizontal-flex-start"></span>',
							'center'     => '<span class="fusiona-horizontal-flex-center"></span>',
							'flex-end'   => '<span class="fusiona-horizontal-flex-end"></span>',
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'grid_layout' => true,
						'back_icons'  => true,
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					'fusion_animation_placeholder'         => [
						'preview_selector' => '.fusion-twitter-timeline',
					],
					[
						'type'        => 'checkbox_button_set',
						'heading'     => esc_attr__( 'Element Visibility', 'fusion-builder' ),
						'param_name'  => 'hide_on_mobile',
						'value'       => fusion_builder_visibility_options( 'full' ),
						'default'     => fusion_builder_default_visibility( 'array' ),
						'description' => esc_attr__( 'Choose to show or hide the element on small, medium or large screens. You can choose more than one at a time.', 'fusion-builder' ),
					],
					'fusion_sticky_visibility_placeholder' => [],
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
add_action( 'fusion_builder_before_init', 'fusion_twitter_timeline_element' );
