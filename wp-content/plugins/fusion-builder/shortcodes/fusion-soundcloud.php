<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_soundcloud' ) ) {

	if ( ! class_exists( 'FusionSC_Soundcloud' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_Soundcloud extends Fusion_Element {

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array
			 */
			protected $args;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_soundcloud-shortcode', [ $this, 'attr' ] );
				add_shortcode( 'fusion_soundcloud', [ $this, 'render' ] );
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

				return [
					'margin_top'     => '',
					'margin_right'   => '',
					'margin_bottom'  => '',
					'margin_left'    => '',
					'hide_on_mobile' => fusion_builder_default_visibility( 'string' ),
					'class'          => 'fusion-soundcloud',
					'id'             => '',
					'auto_play'      => 'no',
					'color'          => 'ff7700',
					'comments'       => 'yes',
					'height'         => '',
					'layout'         => 'classic',
					'show_related'   => 'no',
					'show_reposts'   => 'no',
					'show_user'      => 'yes',
					'url'            => '',
					'width'          => '100%',
				];
			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 1.0
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_soundcloud' );

				$defaults['width']  = FusionBuilder::validate_shortcode_attr_value( $defaults['width'], 'px' );
				$defaults['height'] = FusionBuilder::validate_shortcode_attr_value( $defaults['height'], 'px' );

				extract( $defaults );

				$this->args = $defaults;

				$this->args['margin_bottom'] = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_bottom'], 'px' );
				$this->args['margin_left']   = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_left'], 'px' );
				$this->args['margin_right']  = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_right'], 'px' );
				$this->args['margin_top']    = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_top'], 'px' );

				$autoplay = ( 'yes' === $auto_play ) ? 'true' : 'false';
				$comments = ( 'yes' === $comments ) ? 'true' : 'false';

				if ( 'visual' === $layout ) {
					$visual = 'true';

					if ( ! $height ) {
						$height = '450';
					}
				} else {
					$visual = 'false';

					if ( ! $height ) {
						$height = '166';
					}
				}

				$height = (int) $height;

				$show_related = ( 'yes' === $show_related ) ? 'false' : 'true';
				$show_reposts = ( 'yes' === $show_reposts ) ? 'true' : 'false';
				$show_user    = ( 'yes' === $show_user ) ? 'true' : 'false';

				if ( $color ) {
					$color = str_replace( '#', '', Fusion_Color::new_color( $color )->toCss() );
				}

				$html = '<div ' . FusionBuilder::attributes( 'soundcloud-shortcode' ) . '><iframe scrolling="no" frameborder="no" width="' . $width . '" height="' . $height . '" allow="autoplay" src="https://w.soundcloud.com/player/?url=' . $url . '&amp;auto_play=' . $autoplay . '&amp;hide_related=' . $show_related . '&amp;show_comments=' . $comments . '&amp;show_user=' . $show_user . '&amp;show_reposts=' . $show_reposts . '&amp;visual=' . $visual . '&amp;color=' . $color . '" title="soundcloud"></iframe></div>';
				$html = fusion_library()->images->apply_global_selected_lazy_loading_to_iframe( $html );

				$this->on_render();

				return apply_filters( 'fusion_element_soundcloud_content', $html, $args );
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

				if ( $this->args['class'] ) {
					$attr['class'] = $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				$attr['style'] .= Fusion_Builder_Margin_Helper::get_margins_style( $this->args );

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				return $attr;

			}
		}
	}

	new FusionSC_Soundcloud();

}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_soundcloud() {
	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Soundcloud',
			[
				'name'       => esc_attr__( 'Soundcloud', 'fusion-builder' ),
				'shortcode'  => 'fusion_soundcloud',
				'icon'       => 'fusiona-soundcloud',
				'preview'    => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-soundcloud-preview.php',
				'preview_id' => 'fusion-builder-block-module-soundcloud-preview-template',
				'help_url'   => 'https://theme-fusion.com/documentation/avada/elements/soundcloud-element/',
				'params'     => [
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'SoundCloud Url', 'fusion-builder' ),
						'description' => esc_attr__( 'The SoundCloud url, ex: https://soundcloud.com/dani-pop-shocr-n/miles-davis-smoke-gets-in-your.', 'fusion-builder' ),
						'param_name'  => 'url',
						'value'       => '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Layout', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the layout of the soundcloud embed.', 'fusion-builder' ),
						'param_name'  => 'layout',
						'value'       => [
							'classic' => esc_attr__( 'Classic', 'fusion-builder' ),
							'visual'  => esc_attr__( 'Visual', 'fusion-builder' ),
						],
						'default'     => 'classic',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Comments', 'fusion-builder' ),
						'description' => __( 'Choose to display comments. <strong>NOTE:</strong> This feature can only be turned off on tracks uploaded through a SoundCloud pro plan.', 'fusion-builder' ),
						'param_name'  => 'comments',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'yes',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Related', 'fusion-builder' ),
						'description' => __( 'Choose to display related items. <strong>NOTE:</strong> This feature can only be turned off on tracks uploaded through a SoundCloud pro plan.', 'fusion-builder' ),
						'param_name'  => 'show_related',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'yes',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show User', 'fusion-builder' ),
						'description' => __( 'Choose to display the user who posted the item. <strong>NOTE:</strong> This feature can only be turned off on tracks uploaded through a SoundCloud pro plan.', 'fusion-builder' ),
						'param_name'  => 'show_user',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'yes',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Autoplay', 'fusion-builder' ),
						'description' => __( 'Choose to autoplay the track. <strong>NOTE:</strong> SoundCloud does not allow autoplay on mobile devices.', 'fusion-builder' ),
						'param_name'  => 'auto_play',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'no',
					],
					[
						'type'        => 'colorpicker',
						'heading'     => esc_attr__( 'Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the color of the element.', 'fusion-builder' ),
						'param_name'  => 'color',
						'value'       => '#ff7700',
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Dimensions', 'fusion-builder' ),
						'description'      => esc_attr__( 'Width can be specified in pixels (px) or percentage (%) values, height in pixels (px) only.', 'fusion-builder' ),
						'param_name'       => 'dimensions',
						'value'            => [
							'width'  => '100%',
							'height' => '150px',
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
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_soundcloud' );
