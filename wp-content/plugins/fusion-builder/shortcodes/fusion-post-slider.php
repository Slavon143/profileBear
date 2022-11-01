<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_postslider' ) ) {

	if ( ! class_exists( 'FusionSC_Flexslider' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_Flexslider extends Fusion_Element {

			/**
			 * The flex counter.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $flex_counter = 1;

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
				add_filter( 'fusion_attr_flexslider-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_flexslider-shortcode-slides-container', [ $this, 'slides_container_attr' ] );
				add_filter( 'fusion_attr_flexslider-shortcode-caption', [ $this, 'caption_attr' ] );
				add_filter( 'fusion_attr_flexslider-shortcode-title-container', [ $this, 'title_container_attr' ] );
				add_filter( 'fusion_attr_flexslider-shortcode-thumbnails', [ $this, 'thumbnails_attr' ] );

				add_shortcode( 'fusion_flexslider', [ $this, 'render' ] );
				add_shortcode( 'fusion_postslider', [ $this, 'render' ] );

				// Ajax mechanism for query related part.
				add_action( 'wp_ajax_get_fusion_post_slider', [ $this, 'query' ] );
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
					'class'          => '',
					'id'             => '',
					'category'       => '',
					'excerpt'        => '35',
					'layout'         => 'attachments',
					'lightbox'       => 'yes',
					'limit'          => '3',
					'post_id'        => '',
				];
			}

			/**
			 * Gets the query data for live preview.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 */
			public static function query() {
				if ( isset( $_POST['params'] ) && isset( $_POST['params']['layout'] ) && ! apply_filters( 'fusion_builder_live_request', false ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$params = wp_unslash( $_POST['params'] ); // phpcs:ignore WordPress.Security
					$layout = sanitize_text_field( $params['layout'] );
					add_filter( 'fusion_builder_live_request', '__return_true' );

					$flex_slider       = new FusionSC_Flexslider();
					$flex_slider->args = $params;

					if ( 'attachments' === $layout ) {
						$return_data = $flex_slider->attachments_and_thumbnails( true );
					} elseif ( 'posts' === $layout ) {
						$return_data = $flex_slider->posts( false, true );
					} elseif ( 'posts-with-excerpt' === $layout ) {
						$return_data = $flex_slider->posts( true, true );
					}

					if ( empty( $return_data ) ) {
						$return_data['placeholder'] = fusion_builder_placeholder( 'post_slider', 'posts' );
					}

					echo wp_json_encode( $return_data );
				}

				wp_die();
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

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_postslider' );
				$content  = apply_filters( 'fusion_shortcode_content', $content, 'fusion_postslider', $args );

				extract( $defaults );

				$this->args = $defaults;

				$this->args['margin_bottom'] = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_bottom'], 'px' );
				$this->args['margin_left']   = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_left'], 'px' );
				$this->args['margin_right']  = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_right'], 'px' );
				$this->args['margin_top']    = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_top'], 'px' );

				$slider = '';
				if ( 'attachments' === $layout ) {
					$attachments_and_thumbnails = $this->attachments_and_thumbnails();
					$slider                     = $attachments_and_thumbnails[0];
				} elseif ( 'posts' === $layout ) {
					$slider = $this->posts( false );
				} elseif ( 'posts-with-excerpt' === $layout ) {
					$slider = $this->posts( true );
				}

				if ( empty( $slider ) ) {
					return fusion_builder_placeholder( 'post_slider', 'posts' );
				}

				$slides_html = '<ul ' . FusionBuilder::attributes( 'flexslider-shortcode-slides-container' ) . '>' . $slider . '</ul>';

				$html = '<div ' . FusionBuilder::attributes( 'flexslider-shortcode' ) . '>' . $slides_html . '</div>';

				if ( 'attachments' === $layout ) {
					$html .= '<div ' . FusionBuilder::attributes( 'flexslider-shortcode-thumbnails' ) . '></div>';
				}

				$this->flex_counter++;

				$this->on_render();

				return apply_filters( 'fusion_element_post_slider_content', $html, $args );

			}

			/**
			 * Creates the attachements and thumbnails HTML.
			 *
			 * @access public
			 * @since 1.0
			 * @param bool $live_request Whether or not this is an ajax request.
			 * @return array Holding the HTML for attachment list and thumbnails list.
			 */
			public function attachments_and_thumbnails( $live_request = false ) {
				$return_data      = [];
				$html_attachments = '';
				$html_thumbnails  = '';

				if ( ! $this->args['post_id'] ) {
					$this->args['post_id'] = get_the_ID();
				}

				$query = fusion_cached_get_posts(
					[
						'post_type'      => 'attachment',
						'posts_per_page' => $this->args['limit'],
						'post_status'    => 'any',
						'post_parent'    => $this->args['post_id'],
						'orderby'        => 'menu_order',
						'order'          => 'ASC',
						'post_mime_type' => 'image',
						'exclude'        => get_post_thumbnail_id(),
					]
				);

				if ( $query ) {

					foreach ( $query as $attachment ) {

						$image_data = fusion_library()->images->get_attachment_data_by_helper( $attachment->ID );

						$thumb = wp_get_attachment_image_src( $attachment->ID, 'thumbnail' );

						// We only need the data if a live request.
						if ( ! $live_request ) {
							$output = '<img class="wp-image-' . $attachment->ID . '" src="' . $image_data['url'] . '" alt="' . esc_attr( $image_data['alt'] ) . '" />';

							fusion_library()->images->set_grid_image_meta(
								[
									'layout'  => 'large',
									'columns' => '1',
								]
							);

							$output = fusion_add_responsive_image_markup( $output );

							$output = fusion_library()->images->apply_lazy_loading( $output, null, $attachment->ID, 'full' );

							fusion_library()->images->set_grid_image_meta( [] );

							if ( 'yes' === $this->args['lightbox'] ) {
								$output = '<a href="' . $image_data['url'] . '" data-title="' . esc_attr( $image_data['title_attribute'] ) . '" title="' . esc_attr( $image_data['title_attribute'] ) . '" data-caption="' . esc_attr( $image_data['caption_attribute'] ) . '" data-rel="prettyPhoto[flex_' . $this->flex_counter . ']">' . $output . '</a>';
							}

							$html_attachments .= '<li data-thumb="' . $thumb[0] . '">' . $output . '</li>';

							$html_thumbnails .= '<li>';
							$html_thumbnails .= fusion_library()->images->apply_lazy_loading( '<img src="' . $thumb[0] . '" alt="' . esc_attr( $image_data['alt'] ) . '" />', null, $attachment->ID, 'thumbnail' );
							$html_thumbnails .= '</li>';
						} else {
							$return_data['datasets'][] = [
								'image'   => $image_data['url'],
								'alt'     => $image_data['alt'],
								'caption' => $image_data['caption_attribute'],
								'title'   => $image_data['title_attribute'],
								'thumb'   => $thumb[0],
							];
						}
					}
				}

				wp_reset_query();

				if ( ! $live_request ) {
					return [ $html_attachments, $html_thumbnails ];
				}

				return $return_data;

			}

			/**
			 * Get the posts HTML.
			 *
			 * @access public
			 * @since 1.0
			 * @param bool $with_excerpts Flag deciding if posts should be returned with excerpt.
			 * @param bool $live_request Whether or not this is an ajax request.
			 * @return string HTML.
			 */
			public function posts( $with_excerpts = false, $live_request = false ) {

				$html        = '';
				$return_data = [];

				$args = [
					'posts_per_page' => $this->args['limit'],
					'meta_query'     => [
						[
							'key' => '_thumbnail_id',
						],
					],
				];

				if ( $this->args['post_id'] ) {
					$post_ids         = explode( ',', $this->args['post_id'] );
					$args['post__in'] = $post_ids;
				}

				$cat_ids = '';
				if ( '' !== $this->args['category'] ) {
					$categories = explode( ',', $this->args['category'] );
					if ( isset( $categories ) && $categories ) {
						foreach ( $categories as $category ) {

							$id_obj = get_category_by_slug( $category );

							if ( $id_obj ) {
								// @codingStandardsIgnoreLine
								$cat_ids .= ( 0 === strpos( $category, '-' ) ) ? '-' . $id_obj->cat_ID . ',' : $id_obj->cat_ID . ',';
							}
						}
					}
				}
				$args['cat'] = substr( $cat_ids, 0, -1 );

				// Ajax returns protected posts, but we just want published.
				if ( $live_request ) {
					$args['post_status'] = 'publish';
				}

				$query = fusion_cached_query( $args );

				if ( $query->have_posts() ) {

					while ( $query->have_posts() ) {
						$query->the_post();

						$post_thumbnail_id = get_post_thumbnail_id();
						$image             = wp_get_attachment_url( $post_thumbnail_id );
						$alt               = get_post_meta( $post_thumbnail_id, '_wp_attachment_image_alt', true );

						if ( ! $live_request ) {
							$image_output = '<img class="wp-image-' . $post_thumbnail_id . '" src="' . $image . '" alt="' . esc_attr( $alt ) . '" />';

							fusion_library()->images->set_grid_image_meta(
								[
									'layout'  => 'large',
									'columns' => '1',
								]
							);

							$image_output = fusion_add_responsive_image_markup( $image_output );

							$image_output = fusion_library()->images->apply_lazy_loading( $image_output, null, $post_thumbnail_id, 'full' );

							fusion_library()->images->set_grid_image_meta( [] );

							$link_output = '<a href="' . get_permalink() . '" aria-label="' . the_title_attribute( [ 'echo' => false ] ) . '">' . $image_output . '</a>';
							$title       = '<h2><a href="' . get_permalink() . '">' . get_the_title() . '</a></h2>';
							$content     = $title;

							if ( $with_excerpts ) {
								$excerpt = fusion_builder_get_post_content( '', 'yes', $this->args['excerpt'], true );
								$content = '<div ' . FusionBuilder::attributes( 'excerpt-container' ) . '>' . $title . $excerpt . '</div>';
							}

							$container = '<div ' . FusionBuilder::attributes( 'flexslider-shortcode-title-container' ) . '>' . $content . '</div>';

							$html .= '<li>' . $link_output . $container . '</li>';

						} else {
							$return_data['datasets'][] = [
								'image'           => $image,
								'title_attribute' => the_title_attribute( [ 'echo' => false ] ),
								'title'           => get_the_title(),
								'alt'             => $alt,
								'permalink'       => get_permalink(),
								'excerpt'         => ( $with_excerpts ) ? fusion_get_content_data( 'fusion_postslider', false ) : '',
							];
						}
					}
				}

				wp_reset_query();

				if ( ! $live_request ) {
					return $html;
				}

				return $return_data;

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
					'class' => 'fusion-post-slider fusion-flexslider fusion-flexslider-loading flexslider-' . $this->args['layout'],
					'style' => '',
				];

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				if ( 'yes' === $this->args['lightbox'] && 'attachments' === $this->args['layout'] ) {
					$attr['class'] .= ' flexslider-lightbox';
				}

				$attr['style'] .= Fusion_Builder_Margin_Helper::get_margins_style( $this->args );

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				return $attr;

			}

			/**
			 * Builds the slider-container attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function slides_container_attr() {
				return [
					'class' => 'slides',
				];
			}

			/**
			 * Builds the caption attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function caption_attr() {
				return [
					'class' => 'flex-caption',
				];
			}

			/**
			 * Builds the title-container attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function title_container_attr() {
				return [
					'class' => 'slide-excerpt',
				];
			}

			/**
			 * Builds the thumbnails attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function thumbnails_attr() {

				$attr = [
					'class' => 'flexslider',
				];
				if ( 'attachments' === $this->args['layout'] ) {
					$attr['class'] .= ' fat';
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
				Fusion_Dynamic_JS::enqueue_script( 'fusion-flexslider' );
			}
		}
	}

	new FusionSC_Flexslider();

}

/**
 * Map shortcode to Avada Builder
 *
 * @since 1.0
 */
function fusion_element_post_slider() {
	$builder_status = function_exists( 'is_fusion_editor' ) && is_fusion_editor();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Flexslider',
			[
				'name'       => esc_attr__( 'Post Slider', 'fusion-builder' ),
				'shortcode'  => 'fusion_postslider',
				'icon'       => 'fusiona-layers-alt',
				'preview'    => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-post-slider-preview.php',
				'preview_id' => 'fusion-builder-block-module-post-slider-preview-template',
				'help_url'   => 'https://theme-fusion.com/documentation/avada/elements/post-slider-element/',
				'params'     => [
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Layout', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose a layout style for Post Slider.', 'fusion-builder' ),
						'param_name'  => 'layout',
						'value'       => [
							'posts'              => esc_attr__( 'Posts with Title', 'fusion-builder' ),
							'posts-with-excerpt' => esc_attr__( 'Posts with Title and Excerpt', 'fusion-builder' ),
							'attachments'        => esc_attr__( 'Attachment Layout, Only Images Attached to Post/Page', 'fusion-builder' ),
						],
						'default'     => 'posts',
						'callback'    => [
							'function' => 'fusion_post_slider_query',
							'ajax'     => true,
						],
					],
					[
						'type'             => 'uploadattachment',
						'heading'          => esc_attr__( 'Attach Images to Post/Page Gallery', 'fusion-builder' ),
						'description'      => esc_attr__( 'To add images to this post or page for attachments layout, navigate to "Upload Files" tab in media manager and upload new images.', 'fusion-builder' ),
						'param_name'       => 'upload_attachments',
						'value'            => '',
						'remove_from_atts' => true,
						'dependency'       => [
							[
								'element'  => 'layout',
								'value'    => 'attachments',
								'operator' => '==',
							],
						],
						'callback'         => [
							'function' => 'fusion_post_slider_query',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Excerpt Number of Words', 'fusion-builder' ),
						'description' => esc_attr__( 'Insert the number of words you want to show in the excerpt.', 'fusion-builder' ),
						'param_name'  => 'excerpt',
						'value'       => '35',
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'posts-with-excerpt',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'multiple_select',
						'heading'     => esc_attr__( 'Categories', 'fusion-builder' ),
						'placeholder' => esc_attr__( 'Categories', 'fusion-builder' ),
						'description' => esc_attr__( 'Select categories of posts to display or leave blank for all.', 'fusion-builder' ),
						'param_name'  => 'category',
						'value'       => $builder_status ? fusion_builder_shortcodes_categories( 'category' ) : [],
						'default'     => '',
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'attachments',
								'operator' => '!=',
							],
						],
						'callback'    => [
							'function' => 'fusion_post_slider_query',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Number of Slides', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the number of slides to display.', 'fusion-builder' ),
						'param_name'  => 'limit',
						'value'       => '3',
						'callback'    => [
							'function' => 'fusion_post_slider_query',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Image Lightbox', 'fusion-builder' ),
						'description' => esc_attr__( 'Only works on attachment layout. Lightbox must be enabled in Global Options or the image will open up by in the same tab by itself.', 'fusion-builder' ),
						'param_name'  => 'lightbox',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'yes',
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'attachments',
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
				'callback'   => [
					'function' => 'fusion_post_slider_query',
					'ajax'     => true,
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_post_slider' );
