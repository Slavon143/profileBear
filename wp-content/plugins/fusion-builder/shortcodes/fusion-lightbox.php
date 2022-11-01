<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_lightbox' ) ) {

	if ( ! class_exists( 'FusionSC_FusionLightbox' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_FusionLightbox extends Fusion_Element {

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array
			 */
			protected $args;

			/**
			 * The lightbox counter.
			 *
			 * @access private
			 * @since 3.5
			 * @var int
			 */
			private $lightbox_counter = 1;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_lightbox-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_lightbox-shortcode-image', [ $this, 'image_attr' ] );
				add_shortcode( 'fusion_lightbox', [ $this, 'render' ] );
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
					'type'            => '',
					'full_image'      => '',
					'video_url'       => '',
					'link_url'        => '',
					'thumbnail_image' => '',
					'alt_text'        => '',
					'title'           => '',
					'description'     => '',
					'hide_on_mobile'  => fusion_builder_default_visibility( 'string' ),
					'class'           => '',
					'id'              => '',
				];
			}

			/**
			 * Sets the args from the attributes.
			 *
			 * @access public
			 * @since 3.5
			 * @param array $args Element attributes.
			 * @return void
			 */
			public function set_args( $args ) {
				$this->defaults = self::get_element_defaults();
				$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_lightbox' );
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 3.5
			 * @return array
			 */
			public function attr() {

				$attr = [
					'class' => 'awb-lightbox awb-lightbox-' . $this->element_id,
					'style' => '',
				];

				if ( $this->args['title'] ) {
					$attr['title'] = $attr['data-title'] = $this->args['title'];
				}

				if ( '' === $this->args['type'] ) {
					$attr['href'] = $this->args['full_image'];
				} elseif ( 'link' === $this->args['type'] ) {
					$attr['href'] = $this->args['link_url'];
				} else {
					$attr['href'] = $this->args['video_url'];
				}

				if ( '' !== $this->args['description'] ) {
					$attr['data-caption'] = $this->args['description'];
				}

				if ( '' !== $attr['href'] ) {
					$attr['data-rel'] = 'iLightbox';
				}

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				return $attr;
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 3.5
			 * @return array
			 */
			public function image_attr() {

				$attr = [
					'src' => '',
					'alt' => $this->args['alt_text'],
				];

				if ( $this->args['thumbnail_image'] ) {
					$attr['src'] = $this->args['thumbnail_image'];
				}

				return $attr;
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

				$this->set_args( $args );

				$this->set_element_id( $this->lightbox_counter );
				$html = '';

				if ( '' !== $this->args['thumbnail_image'] ) {
					$html .= '<a ' . FusionBuilder::attributes( 'lightbox-shortcode' ) . '>';
					$html .= '<img ' . FusionBuilder::attributes( 'lightbox-shortcode-image' ) . '>';
					$html .= '</a>';
				}

				$this->lightbox_counter++;

				$this->on_render();

				return apply_filters( 'fusion_element_lightbox_content', $html, $args );

			}

			/**
			 * Sets the necessary scripts.
			 *
			 * @access public
			 * @since 3.2
			 * @return void
			 */
			public function on_first_render() {
				Fusion_Dynamic_JS::enqueue_script( 'fusion-lightbox' );
			}
		}
	}

	new FusionSC_FusionLightbox();

}

/**
 * Map shortcode to Avada Builder
 *
 * @since 1.0
 */
function fusion_element_lightbox() {
	fusion_builder_map(
		[
			'name'      => esc_attr__( 'Lightbox', 'fusion-builder' ),
			'shortcode' => 'fusion_lightbox',
			'icon'      => 'fusiona-uniF602',
			'help_url'  => 'https://theme-fusion.com/documentation/avada/elements/lightbox-element/',
			'params'    => [
				[
					'type'         => 'upload',
					'heading'      => esc_attr__( 'Thumbnail Image', 'fusion-builder' ),
					'description'  => esc_attr__( 'Clicking this image will show lightbox.', 'fusion-builder' ),
					'param_name'   => 'thumbnail_image',
					'value'        => '',
					'dynamic_data' => true,
				],
				[
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Content Type', 'fusion-builder' ),
					'description' => esc_attr__( 'Select what you want to display in the lightbox.', 'fusion-builder' ),
					'param_name'  => 'type',
					'defaults'    => '',
					'value'       => [
						''      => esc_attr__( 'Image', 'fusion-builder' ),
						'video' => esc_attr__( 'Video', 'fusion-builder' ),
						'link'  => esc_attr__( 'Link', 'fusion-builder' ),
					],
				],
				[
					'type'         => 'upload',
					'heading'      => esc_attr__( 'Full Image', 'fusion-builder' ),
					'description'  => esc_attr__( 'Upload an image that will show up in the lightbox.', 'fusion-builder' ),
					'param_name'   => 'full_image',
					'value'        => '',
					'dynamic_data' => true,
					'dependency'   => [
						[
							'element'  => 'type',
							'value'    => '',
							'operator' => '==',
						],
					],
				],
				[
					'type'         => 'link_selector',
					'heading'      => esc_attr__( 'Video URL', 'fusion-builder' ),
					'description'  => esc_attr__( 'Insert the video URL that will show in the lightbox. This can be a YouTube, Vimeo or a self-hosted video URL.', 'fusion-builder' ),
					'param_name'   => 'video_url',
					'value'        => '',
					'dynamic_data' => true,
					'dependency'   => [
						[
							'element'  => 'type',
							'value'    => 'video',
							'operator' => '==',
						],
					],
				],
				[
					'type'         => 'link_selector',
					'heading'      => esc_attr__( 'Link URL', 'fusion-builder' ),
					'description'  => esc_attr__( 'Insert the link URL that will show in the lightbox. This can be a link to website URL.', 'fusion-builder' ),
					'param_name'   => 'link_url',
					'value'        => '',
					'dynamic_data' => true,
					'dependency'   => [
						[
							'element'  => 'type',
							'value'    => 'link',
							'operator' => '==',
						],
					],
				],
				[
					'type'        => 'textfield',
					'heading'     => esc_attr__( 'Alt Text', 'fusion-builder' ),
					'param_name'  => 'alt_text',
					'value'       => '',
					'description' => esc_attr__( 'The alt attribute provides alternative information if an image cannot be viewed.', 'fusion-builder' ),
				],
				[
					'type'         => 'textfield',
					'heading'      => esc_attr__( 'Lightbox Title', 'fusion-builder' ),
					'param_name'   => 'title',
					'value'        => '',
					'description'  => esc_attr__( 'This will show up in the lightbox as a title above the image.', 'fusion-builder' ),
					'dynamic_data' => true,
				],
				[
					'type'         => 'textfield',
					'heading'      => esc_attr__( 'Lightbox Description', 'fusion-builder' ),
					'param_name'   => 'description',
					'value'        => '',
					'description'  => esc_attr__( 'This will show up in the lightbox as a description below the image.', 'fusion-builder' ),
					'dynamic_data' => true,
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
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_lightbox' );
