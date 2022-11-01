<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_blog' ) ) {

	if ( ! class_exists( 'FusionSC_Blog' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_Blog extends Fusion_Element {

			/**
			 * Blog SC counter.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $blog_sc_counter = 1;

			/**
			 * Posts counter.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $post_count = 1;

			/**
			 * The post ID.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $post_id = 0;


			/**
			 * The post permalink.
			 *
			 * @access private
			 * @since 2.0.3
			 * @var string
			 */
			private $permalink = '';

			/**
			 * The month of the post.
			 *
			 * @access private
			 * @since 1.0
			 * @var null|int|string
			 */
			private $post_month = null;

			/**
			 * The post's year.
			 *
			 * @access private
			 * @since 1.0
			 * @var null|int|string
			 */
			private $post_year = null;

			/**
			 * An array of meta settings.
			 *
			 * @access private
			 * @since 1.0
			 * @var array
			 */
			private $meta_info_settings = [];

			/**
			 * Header arguments.
			 *
			 * @access private
			 * @since 1.0
			 * @var array
			 */
			private $header = [];

			/**
			 * The Query.
			 *
			 * @access private
			 * @since 1.0
			 * @var string|array|object
			 */
			private $query = '';

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
				// Containers.
				add_action( 'fusion_blog_shortcode_before_loop', [ $this, 'before_loop' ] );
				add_action( 'fusion_blog_shortcode_before_loop_timeline', [ $this, 'before_loop_timeline' ] );
				add_action( 'fusion_blog_shortcode_after_loop', [ $this, 'after_loop' ] );

				// Post / loop basic structure.
				add_action( 'fusion_blog_shortcode_loop_header', [ $this, 'loop_header' ] );
				add_action( 'fusion_blog_shortcode_loop_footer', [ $this, 'loop_footer' ] );
				add_action( 'fusion_blog_shortcode_loop_content', [ $this, 'loop_content' ] );
				add_action( 'fusion_blog_shortcode_loop_content', [ $this, 'page_links' ] );
				add_action( 'fusion_blog_shortcode_loop', [ $this, 'loop' ] );

				// Special blog layout structure.
				add_action( 'fusion_blog_shortcode_wrap_loop_open', [ $this, 'wrap_loop_open' ] );
				add_action( 'fusion_blog_shortcode_wrap_loop_close', [ $this, 'wrap_loop_close' ] );
				add_action( 'fusion_blog_shortcode_date_and_format', [ $this, 'add_date_box' ] );
				add_action( 'fusion_blog_shortcode_date_and_format', [ $this, 'add_format_box' ] );
				add_action( 'fusion_blog_shortcode_timeline_date', [ $this, 'timeline_date' ] );

				// Element attributes.
				add_filter( 'fusion_attr_blog-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_blog-shortcode-posts-container', [ $this, 'posts_container_attr' ] );
				add_filter( 'fusion_attr_blog-shortcode-loop', [ $this, 'loop_attr' ] );
				add_filter( 'fusion_attr_blog-shortcode-post-title', [ $this, 'post_title_attr' ] );
				add_filter( 'fusion_attr_blog-shortcode-post-content-wrapper', [ $this, 'post_content_wrapper_attr' ] );
				add_filter( 'fusion_attr_blog-fusion-post-wrapper', [ $this, 'post_wrapper_attr' ] );
				add_filter( 'fusion_attr_blog-fusion-content-sep', [ $this, 'content_sep_attr' ] );

				add_shortcode( 'fusion_blog', [ $this, 'render' ] );

				// Ajax mechanism for query related part.
				add_action( 'wp_ajax_get_fusion_blog', [ $this, 'ajax_query' ] );
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
					'margin_bottom'                  => '',
					'margin_left'                    => '',
					'margin_right'                   => '',
					'margin_top'                     => '',
					'hide_on_mobile'                 => fusion_builder_default_visibility( 'string' ),
					'class'                          => '',
					'id'                             => '',
					'blog_grid_column_spacing'       => $fusion_settings->get( 'blog_grid_column_spacing' ),
					'blog_grid_padding'              => $fusion_settings->get( 'blog_grid_padding' ),
					'content_alignment'              => '',
					'equal_heights'                  => 'no',
					'blog_grid_columns'              => $fusion_settings->get( 'blog_grid_columns' ),
					'pull_by'                        => '',
					'cat_slug'                       => '',
					'tag_slug'                       => '',
					'exclude_tags'                   => '',
					'excerpt'                        => $fusion_settings->get( 'blog_excerpt' ),
					'excerpt_length'                 => $fusion_settings->get( 'blog_excerpt_length' ),
					'exclude_cats'                   => '',
					'grid_box_color'                 => $fusion_settings->get( 'timeline_bg_color' ),
					'grid_element_color'             => $fusion_settings->get( 'timeline_color' ),
					'grid_separator_color'           => $fusion_settings->get( 'grid_separator_color' ),
					'grid_separator_style_type'      => $fusion_settings->get( 'grid_separator_style_type' ),
					'layout'                         => 'large',
					'meta_all'                       => 'yes',
					'meta_author'                    => 'yes',
					'meta_categories'                => 'yes',
					'meta_comments'                  => 'yes',
					'meta_date'                      => 'yes',
					'meta_link'                      => 'yes',
					'meta_read'                      => 'yes',
					'meta_tags'                      => 'no',
					'meta_type'                      => 'no',
					'number_posts'                   => '6',
					'offset'                         => '',
					'order'                          => 'DESC',
					'orderby'                        => 'date',
					'paging'                         => '',
					'posts_per_page'                 => '-1',
					'post_status'                    => '',
					'scrolling'                      => 'infinite',
					'show_title'                     => 'yes',
					'strip_html'                     => 'yes',
					'taxonomy'                       => 'category',
					'thumbnail'                      => 'yes',
					'title_link'                     => 'yes',
					'blog_masonry_grid_ratio'        => $fusion_settings->get( 'masonry_grid_ratio' ),
					'blog_masonry_width_double'      => $fusion_settings->get( 'masonry_width_double' ),
					'excerpt_words'                  => '50', // Deprecated.
					'title'                          => '',   // Deprecated.
					'title_size'                     => '2',
					'timeline_title_size'            => '3',

					'fusion_font_family_title_font'  => '',
					'fusion_font_variant_title_font' => '',
					'title_font_size'                => '',
					'title_line_height'              => '',
					'title_letter_spacing'           => '',
					'title_text_transform'           => '',
					'fusion_font_family_timeline_title_font' => '',
					'fusion_font_variant_timeline_title_font' => '',
					'timeline_title_font_size'       => '',
					'timeline_title_line_height'     => '',
					'timeline_title_letter_spacing'  => '',
					'timeline_title_text_transform'  => '',
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
					'timeline_bg_color'        => 'grid_box_color',
					'timeline_color'           => 'grid_element_color',
					'blog_grid_padding'        => 'blog_grid_padding',
					'blog_grid_columns'        => 'blog_grid_columns',
					'blog_grid_column_spacing' => 'blog_grid_column_spacing',
					'blog_excerpt'             => 'excerpt',
					'blog_excerpt_length'      => 'excerpt_length',
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
					'disable_date_rich_snippet_pages'   => $fusion_settings->get( 'disable_date_rich_snippet_pages' ),
					'disable_read_more'                 => $fusion_settings->get( 'disable_excerpts' ),
					'read_more_text'                    => apply_filters( 'avada_read_more_name', esc_attr__( 'Read More', 'fusion-builder' ) ),
					'link_read_more'                    => $fusion_settings->get( 'link_read_more' ),
					'pagination_global'                 => apply_filters( 'fusion_builder_blog_pagination', '' ),
					'pagination_range_global'           => apply_filters( 'fusion_pagination_size', $fusion_settings->get( 'pagination_range' ) ),
					'pagination_start_end_range_global' => apply_filters( 'fusion_pagination_start_end_size', $fusion_settings->get( 'pagination_start_end_range' ) ),
					'load_more_text'                    => apply_filters( 'avada_load_more_posts_name', esc_attr__( 'Load More Posts', 'fusion-builder' ) ),
					'image_rollover'                    => $fusion_settings->get( 'image_rollover' ),
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
					'disable_date_rich_snippet_pages' => 'disable_date_rich_snippet_pages',
					'image_rollover'                  => 'image_rollover',
					'pagination_range'                => 'pagination_range_global',
					'pagination_start_end_range'      => 'pagination_start_end_range_global',
				];
			}

			/**
			 * Gets the query data.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @param array $defaults An array of defaults.
			 * @return void
			 */
			public function ajax_query( $defaults ) {
				check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );
				$this->query( $defaults );
			}

			/**
			 * Gets the query data.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @param array $defaults The default args.
			 * @return array
			 */
			public function query( $defaults ) {
				$fusion_settings = awb_get_fusion_settings();
				$live_request    = false;

				// Return if there's a query override.
				$query_override = apply_filters( 'fusion_blog_shortcode_query_override', null, $defaults );

				if ( $query_override ) {
					return $query_override;
				}

				// From Ajax Request.
				if ( isset( $_POST['model'] ) && isset( $_POST['model']['params'] ) && ! apply_filters( 'fusion_builder_live_request', false ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$defaults     = $_POST['model']['params']; // phpcs:ignore WordPress.Security
					$return_data  = [];
					$live_request = true;
					fusion_set_live_data();
					add_filter( 'fusion_builder_live_request', '__return_true' );
				}

				// Convert all attributes to correct values for WP query.
				$defaults['posts_per_page'] = '0' === $defaults['number_posts'] ? get_option( 'posts_per_page' ) : $defaults['number_posts'];

				if ( isset( $defaults['title'] ) && $defaults['title'] ) {
					$defaults['show_title'] = $defaults['title'];
					unset( $defaults['title'] );
				}

				$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
				if ( is_front_page() || is_home() ) {
					$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : ( ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1 );
				}

				$defaults['paged'] = $paged;

				if ( '0' === $defaults['offset'] ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
					$defaults['offset'] = '';
				}

				if ( 'tag' !== $defaults['pull_by'] ) {
					// Check for cats to exclude; needs to be checked via exclude_cats param
					// and '-' prefixed cats on cats param exclusion via exclude_cats param.
					$cats_to_exclude    = explode( ',', $defaults['exclude_cats'] );
					$cats_id_to_exclude = [];
					if ( $cats_to_exclude ) {
						foreach ( $cats_to_exclude as $cat_to_exclude ) {
							$id_obj = get_category_by_slug( $cat_to_exclude );
							if ( $id_obj ) {
								$cats_id_to_exclude[] = $id_obj->term_id;
							}
						}
						if ( $cats_id_to_exclude ) {
							// Filter terms, e.g. useful for WPML.
							$defaults['category__not_in'] = apply_filters( 'avada_element_term_selection', $cats_id_to_exclude, 'post', 'category' );
						}
					}

					// Setting up cats to be used and exclusion using '-' prefix on cats param; transform slugs to ids.
					$cat_ids = '';
					if ( '' !== $defaults['cat_slug'] ) {
						$categories = explode( ',', $defaults['cat_slug'] );
						// Filter terms, e.g. useful for WPML.
						$categories = apply_filters( 'avada_element_term_selection', $categories, 'post', 'category' );

						if ( isset( $categories ) && $categories ) {
							foreach ( $categories as $category ) {

								$id_obj = get_category_by_slug( $category );

								if ( $id_obj ) {
									$cat_ids .= ( 0 === strpos( $category, '-' ) ) ? '-' . $id_obj->cat_ID . ',' : $id_obj->cat_ID . ',';
								}
							}
						}
					}
					$defaults['cat'] = substr( $cat_ids, 0, -1 );
				} else {
					// Check for tags to exclude; needs to be checked via exclude_tags param
					// and '-' prefixed tags on tags param exclusion via exclude_tags param.
					$tags_to_exclude    = explode( ',', $defaults['exclude_tags'] );
					$tags_id_to_exclude = [];
					if ( $tags_to_exclude ) {
						foreach ( $tags_to_exclude as $tag_to_exclude ) {
							$id_obj = get_term_by( 'slug', $tag_to_exclude, 'post_tag' );
							if ( $id_obj ) {
								$tags_id_to_exclude[] = $id_obj->term_id;
							}
						}
						if ( $tags_id_to_exclude ) {
							// Filter terms, e.g. useful for WPML.
							$defaults['tag__not_in'] = apply_filters( 'avada_element_term_selection', $tags_id_to_exclude, 'post', 'post_tag' );
						}
					}

					// Setting up tags to be used.
					$tag_ids = [];
					if ( '' !== $defaults['tag_slug'] ) {
						$tags = explode( ',', $defaults['tag_slug'] );
						// Filter terms, e.g. useful for WPML.
						$tags = apply_filters( 'avada_element_term_selection', $tags, 'post', 'post_tag' );
						if ( isset( $tags ) && $tags ) {
							foreach ( $tags as $tag ) {
								$id_obj = get_term_by( 'slug', $tag, 'post_tag' );

								if ( $id_obj ) {
									$tag_ids[] = $id_obj->term_id;
								}
							}
						}
					}
					$defaults['tag__in'] = $tag_ids;
				}

				if ( '' === $defaults['post_status'] ) {
					if ( $live_request ) {
						$args['post_status'] = 'publish';
					} else {
						unset( $defaults['post_status'] );
					}
				} else {
					$defaults['post_status'] = explode( ',', $defaults['post_status'] );
				}

				$fusion_query = fusion_cached_query( apply_filters( 'fusion_blog_shortcode_query_args', $defaults ) );

				wp_reset_postdata();

				if ( ! $live_request ) {
					return $fusion_query;
				}

				if ( ! $fusion_query->have_posts() ) {
					$return_data['placeholder'] = fusion_builder_placeholder( 'post', 'blog posts' );
					echo wp_json_encode( $return_data );
					wp_die();
				}

				$return_data['paged']         = $paged;
				$return_data['max_num_pages'] = $fusion_query->max_num_pages;
				$regular_images_found         = false;

				if ( $fusion_query->have_posts() ) {
					while ( $fusion_query->have_posts() ) {
						$fusion_query->the_post();

						$id        = get_the_ID();
						$title     = get_the_title();
						$permalink = get_the_permalink();

						// Slideshow data.
						$thumbnail             = $video = false;
						$featured_image_width  = fusion_get_option( 'fimg[width]' );
						$featured_image_height = fusion_get_option( 'fimg[height]' );

						$video = apply_filters( 'privacy_iframe_embed', fusion_get_page_option( 'video', $id ) );

						if ( has_post_thumbnail() ) {
							$thumbnail = true;
						}

						$multiple_featured = $featured = [];
						$i                 = 2;

						$image_sizes = [ 'full', 'blog-medium', 'blog-large' ];
						while ( $i <= $fusion_settings->get( 'posts_slideshow_number' ) ) {
							$attachment_id = function_exists( 'fusion_get_featured_image_id' ) ? fusion_get_featured_image_id( 'featured-image-' . $i, 'post' ) : '';
							if ( $attachment_id ) {

								$attachment_data = wp_get_attachment_metadata( $attachment_id );
								$full_image      = wp_get_attachment_image_src( $attachment_id, 'full' );

								$featured['full_src'] = $full_image[0];

								if ( is_array( $attachment_data ) ) {

									$image_title   = get_post_field( 'post_title', $attachment_id );
									$image_caption = get_post_field( 'post_excerpt', $attachment_id );
									$image_alt     = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );

									$featured['title']   = $image_title;
									$featured['caption'] = $image_caption;
									$featured['alt']     = $image_alt;
								}

								foreach ( $image_sizes as $image_size ) {
									$attachment_image        = wp_get_attachment_image_src( $attachment_id, $image_size );
									$image_markup            = '<img src="' . $attachment_image[0] . '" role="presentation"/>';
									$featured[ $image_size ] = $image_markup;
								}
								$multiple_featured[] = $featured;
							}
							$i++;
						}

						// Masonry Attributes.
						$masonry_attributes = [];

						// Set image or placeholder and correct corresponding styling.
						$insert_bg_color = true;
						if ( has_post_thumbnail() ) {
							$post_thumbnail_attachment = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' );
							if ( is_array( $post_thumbnail_attachment ) ) {
								$masonry_attribute_style = 'background-image:url(' . $post_thumbnail_attachment[0] . ');';
								$insert_bg_color         = false;
							}
						}
						if ( $insert_bg_color ) {
							$post_thumbnail_attachment = [];
							$masonry_attribute_style   = 'background-color:#f6f6f6;';
						}

						// Get the correct image orientation class.
						$element_orientation_class = fusion_library()->images->get_element_orientation_class( get_post_thumbnail_id(), $post_thumbnail_attachment, $defaults['blog_masonry_grid_ratio'], $defaults['blog_masonry_width_double'] );
						$element_base_padding      = fusion_library()->images->get_element_base_padding( $element_orientation_class );

						// Check if we have a landscape image, then it has to stretch over 2 cols.
						if ( false === strpos( $element_orientation_class, 'fusion-element-landscape' ) ) {
							$regular_images_found = true;
						}

						$masonry_data = [
							'element_orientation_class' => $element_orientation_class,
							'element_base_padding'      => $element_base_padding,
							'timeline_color'            => Fusion_Sanitize::color( $fusion_settings->get( 'timeline_color' ) ),
							'masonry_attribute_style'   => $masonry_attribute_style,
						];

						if ( ! empty( $post_thumbnail_attachment ) ) {
							$masonry_data['image_width']  = $post_thumbnail_attachment[1];
							$masonry_data['image_height'] = $post_thumbnail_attachment[2];
						}

						$masonry_data['specific_element_orientation_class'] = ( '' !== get_post_meta( get_post_thumbnail_id(), 'fusion_masonry_element_layout', true ) ) ? true : false;

						$image_data                 = fusion_get_image_data( $id, $image_sizes, $permalink );
						$image_data['masonry_data'] = $masonry_data;

						$slideshow = [
							'featured_image_width'  => $featured_image_width,
							'featured_image_height' => $featured_image_height,
							'id'                    => $id,
							'title'                 => $title,
							'permalink'             => $permalink,
							'multiple_featured'     => $multiple_featured,
							'thumbnail'             => $thumbnail,
							'video'                 => $video,
							'image_data'            => $image_data,
						];

						$post_timestamp = get_the_time( 'U' );

						$meta_data = fusion_get_meta_data( $id );

						$timeline_comments = '';
						if ( ! post_password_required( $id ) ) {
							$comments_icon = '<i ' . FusionBuilder::attributes( 'awb-icon-bubbles' ) . ' aria-hidden="true"></i>&nbsp;';
							$comments      = '<i class="awb-icon-bubbles" aria-hidden="true"></i>&nbsp;' . esc_attr__( 'Protected', 'fusion-builder' );
							ob_start();
							comments_popup_link( $comments_icon . '0', $comments_icon . '1', $comments_icon . '%' );
							$timeline_comments = ob_get_contents();
							ob_get_clean();
						}

						$post_video = apply_filters( 'fusion_builder_post_video', '', $id );

						$post_class = false;
						$classes    = get_post_class( [], $id );
						if ( $classes && is_array( $classes ) ) {
							$post_class = ' ' . implode( ' ', $classes ) . ' ';
						}
						$content = fusion_get_content_data( 'fusion_blog' );

						if ( has_excerpt( $id ) ) {
							$content['has_custom_excerpt'] = true;
						} else {
							$content['has_custom_excerpt'] = false;
						}

						$return_data['posts'][] = [
							'id'                        => $id,
							'title'                     => $title,
							'permalink'                 => $permalink,
							'post_month'                => date( 'n', $post_timestamp ),
							'post_year'                 => get_the_date( 'Y' ),
							'timeline_date_format'      => get_the_date( $fusion_settings->get( 'timeline_date_format' ) ),
							'post_video'                => $post_video,
							'post_class'                => $post_class,
							'slideshow'                 => $slideshow,
							'alternate_date_format_day' => get_the_time( $fusion_settings->get( 'alternate_date_format_day' ) ),
							'alternate_date_format_month_year' => get_the_time( $fusion_settings->get( 'alternate_date_format_month_year' ) ),
							'format'                    => get_post_format(),
							'meta_data'                 => $meta_data,
							'link_icon_target'          => apply_filters( 'fusion_builder_link_icon_target', '', $id ),
							'post_links_target'         => apply_filters( 'fusion_builder_post_links_target', '', $id ),
							'content'                   => $content,
							'timeline_comments'         => $timeline_comments,
						];
					}
				}
				$return_data['regular_images_found'] = $regular_images_found;
				echo wp_json_encode( $return_data );
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
				global $post;

				$fusion_settings = awb_get_fusion_settings();

				add_filter( 'fusion_dynamic_post_id', [ $this, 'post_dynamic_data' ] );

				// If on a 404 page we need to reset post back to null, since WP does not do it #3891.
				$reset_to_null = null === $post;

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_blog' );

				$defaults['blog_grid_column_spacing'] = FusionBuilder::validate_shortcode_attr_value( $defaults['blog_grid_column_spacing'], '' );

				if ( isset( $args['padding_top'] ) && '' !== $args['padding_top'] ) {
					$defaults['blog_grid_padding']['top'] = $args['padding_top'];
				}
				if ( isset( $args['padding_right'] ) && '' !== $args['padding_right'] ) {
					$defaults['blog_grid_padding']['right'] = $args['padding_right'];
				}
				if ( isset( $args['padding_bottom'] ) && '' !== $args['padding_bottom'] ) {
					$defaults['blog_grid_padding']['bottom'] = $args['padding_bottom'];
				}
				if ( isset( $args['padding_left'] ) && '' !== $args['padding_left'] ) {
					$defaults['blog_grid_padding']['left'] = $args['padding_left'];
				}
				// Re-index the array to set the correct values.
				if ( ! isset( $args['blog_grid_padding'] ) ) {
					$defaults['blog_grid_padding'] = [
						$defaults['blog_grid_padding']['top'],
						$defaults['blog_grid_padding']['right'],
						$defaults['blog_grid_padding']['bottom'],
						$defaults['blog_grid_padding']['left'],
					];
				}

				if ( $defaults['title'] ) {
					$defaults['show_title'] = $defaults['title'];
				}
				unset( $defaults['title'] );

				extract( $defaults );

				$defaults['scrolling'] = ( isset( $defaults['paging'] ) && 'no' === $defaults['paging'] && 'pagination' === $defaults['scrolling'] ) ? 'no' : $defaults['scrolling'];

				if ( -1 == $defaults['number_posts'] ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
					$defaults['scrolling'] = 'no';
				}

				// Add hyphens for alternate layout options.
				if ( 'large alternate' === $defaults['layout'] ) {
					$defaults['layout'] = 'large-alternate';
				} elseif ( 'medium alternate' === $defaults['layout'] ) {
					$defaults['layout'] = 'medium-alternate';
				}

				$defaults['load_more'] = false;
				if ( 'no' !== $defaults['scrolling'] ) {
					if ( 'load_more_button' === $defaults['scrolling'] ) {
						$defaults['load_more'] = true;
						$defaults['scrolling'] = 'infinite';
					}
				}

				$defaults['meta_all']        = ( 'yes' === $defaults['meta_all'] );
				$defaults['meta_author']     = ( 'yes' === $defaults['meta_author'] );
				$defaults['meta_categories'] = ( 'yes' === $defaults['meta_categories'] );
				$defaults['meta_comments']   = ( 'yes' === $defaults['meta_comments'] );
				$defaults['meta_date']       = ( 'yes' === $defaults['meta_date'] );
				$defaults['meta_link']       = ( 'yes' === $defaults['meta_link'] );
				$defaults['meta_tags']       = ( 'yes' === $defaults['meta_tags'] );
				$defaults['meta_type']       = ( 'yes' === $defaults['meta_type'] );
				$defaults['strip_html']      = ( 'yes' === $defaults['strip_html'] );
				$defaults['thumbnail']       = ( 'yes' === $defaults['thumbnail'] );
				$defaults['show_title']      = ( 'yes' === $defaults['show_title'] );
				$defaults['title_link']      = ( 'yes' === $defaults['title_link'] );

				if ( isset( $args['excerpt_words'] ) && ! isset( $args['excerpt_length'] ) ) {
					$defaults['excerpt_length'] = $args['excerpt_words'];
				}

				// Combine meta info into one variable.
				$defaults['meta_info_combined'] = $defaults['meta_all'] * ( $defaults['meta_author'] + $defaults['meta_date'] + $defaults['meta_categories'] + $defaults['meta_tags'] + $defaults['meta_comments'] + $defaults['meta_link'] + $defaults['meta_type'] );
				// Create boolean that holds info whether content should be excerpted.
				$defaults['is_zero_excerpt'] = ( 'yes' === $defaults['excerpt'] && $defaults['excerpt_length'] < 1 ) ? 1 : 0;

				if ( '0' === $defaults['blog_grid_column_spacing'] ) {
					$defaults['blog_grid_column_spacing'] = '0.0';
				}

				$defaults['blog_sc_query'] = true;

				$this->args = $defaults;

				// Set the meta info settings for later use.
				$this->meta_info_settings['post_meta']          = $defaults['meta_all'];
				$this->meta_info_settings['post_meta_author']   = $defaults['meta_author'];
				$this->meta_info_settings['post_meta_date']     = $defaults['meta_date'];
				$this->meta_info_settings['post_meta_cats']     = $defaults['meta_categories'];
				$this->meta_info_settings['post_meta_tags']     = $defaults['meta_tags'];
				$this->meta_info_settings['post_meta_comments'] = $defaults['meta_comments'];
				$this->meta_info_settings['post_meta_type']     = $defaults['meta_type'];

				$fusion_query = $this->query( $defaults );

				$this->query = $fusion_query;

				$posts = '';

				// Initialize the time stamps for timeline month/year check.
				if ( 'timeline' === $this->args['layout'] ) {
					$this->post_count = 1;

					$prev_post_timestamp = null;
					$prev_post_month     = null;
					$prev_post_year      = null;
					$first_timeline_loop = false;
				}

				// Do the loop.
				if ( $fusion_query->have_posts() ) {

					if ( 'masonry' === $this->args['layout'] ) {
						$posts .= '<article class="fusion-post-grid fusion-post-masonry post fusion-grid-sizer"></article>';
					}

					while ( $fusion_query->have_posts() ) :
						$fusion_query->the_post();

						$this->post_id   = get_the_ID();
						$this->permalink = get_the_permalink();

						if ( 'private' === get_post_status() && ! is_user_logged_in() || in_array( get_post_status(), [ 'pending', 'draft', 'future' ], true ) && ! current_user_can( 'edit-post' ) ) {
							$this->permalink = '#';
						}

						if ( 'timeline' === $this->args['layout'] ) {
							// Set the time stamps for timeline month/year check.
							$post_timestamp   = get_the_time( 'U' );
							$this->post_month = date( 'n', $post_timestamp );
							$this->post_year  = get_the_date( 'Y' );
							$current_date     = get_the_date( 'Y-n' );

							$date_params['prev_post_month'] = $prev_post_month;
							$date_params['post_month']      = $this->post_month;
							$date_params['prev_post_year']  = $prev_post_year;
							$date_params['post_year']       = $this->post_year;

							// Set the timeline month label.
							ob_start();
							do_action( 'fusion_blog_shortcode_timeline_date', $date_params );
							$timeline_date = ob_get_contents();
							ob_get_clean();

							$posts .= $timeline_date;
						}

						ob_start();
						do_action( 'fusion_blog_shortcode_before_loop' );
						$before_loop_action = ob_get_contents();
						ob_get_clean();

						$posts .= $before_loop_action;

						if ( 'grid' === $this->args['layout'] || 'masonry' === $this->args['layout'] || 'timeline' === $this->args['layout'] ) {
							$posts .= '<div ' . FusionBuilder::attributes( 'blog-fusion-post-wrapper' ) . '>';
						}

						$this->header = [
							'title_link' => true,
						];

						ob_start();
						do_action( 'fusion_blog_shortcode_loop_header' );

						do_action( 'fusion_blog_shortcode_loop_content' );

						do_action( 'fusion_blog_shortcode_loop_footer' );

						do_action( 'fusion_blog_shortcode_after_loop' );
						$loop_actions = ob_get_contents();
						ob_get_clean();

						$posts .= $loop_actions;

						if ( 'timeline' === $this->args['layout'] ) {
							$prev_post_timestamp = $post_timestamp;
							$prev_post_month     = $this->post_month;
							$prev_post_year      = $this->post_year;
							$this->post_count++;
						}

					endwhile;
				} else {

					$this->blog_sc_counter++;
					return fusion_builder_placeholder( 'post', 'blog posts' );

				}

				// Prepare needed wrapping containers.
				$html = '';

				$html .= '<div ' . FusionBuilder::attributes( 'blog-shortcode' ) . '>';

				if ( ( 'grid' === $this->args['layout'] || 'masonry' === $this->args['layout'] ) && $this->args['blog_grid_column_spacing'] ) {
					$html .= '<style type="text/css">.fusion-blog-shortcode-' . $this->blog_sc_counter . ' .fusion-blog-layout-grid .fusion-post-grid{padding:' . ( $defaults['blog_grid_column_spacing'] / 2 ) . 'px;}.fusion-blog-shortcode-' . $this->blog_sc_counter . ' .fusion-posts-container{margin-left: -' . ( $defaults['blog_grid_column_spacing'] / 2 ) . 'px !important; margin-right:-' . $defaults['blog_grid_column_spacing'] / 2 . 'px !important;}</style>';
				}
				$html .= $this->get_style_element();

				$html .= '<div ' . FusionBuilder::attributes( 'blog-shortcode-posts-container' ) . '>';

				ob_start();
				do_action( 'fusion_blog_shortcode_wrap_loop_open' );
				$wrap_loop_open = ob_get_contents();
				ob_get_clean();

				$html .= $wrap_loop_open;

				$html .= $posts;

				ob_start();
				do_action( 'fusion_blog_shortcode_wrap_loop_close' );

				$wrap_loop_close_action = ob_get_contents();
				ob_get_clean();

				$html .= $wrap_loop_close_action;

				$html .= '</div>';

				if ( 'no' !== $this->args['scrolling'] ) {
					$pagination = $this->pagination( $this->query->max_num_pages, $fusion_settings->get( 'pagination_range' ), $this->query );

					$html .= $pagination;
				}

				// If infinite scroll with "load more" button is used.
				if ( $this->args['load_more'] && 1 < $this->query->max_num_pages ) {
					$html .= '<button class="fusion-load-more-button fusion-blog-button fusion-clearfix">' . apply_filters( 'avada_load_more_posts_name', esc_attr__( 'Load More Posts', 'fusion-builder' ) ) . '</button>';
				}

				$html .= '</div>';

				wp_reset_postdata();
				if ( $reset_to_null ) {
					$post = null;
				}

				remove_filter( 'fusion_dynamic_post_id', [ $this, 'post_dynamic_data' ] );

				$this->blog_sc_counter++;

				$this->on_render();

				return apply_filters( 'fusion_element_blog_content', $html, $args );

			}

			/**
			 * Render the blog pagination.
			 *
			 * @access public
			 * @since 1.0
			 * @param int    $max_pages     Max number of pages.
			 * @param int    $range         How many page numbers to display to either side of the current page.
			 * @param object $current_query The query.
			 */
			public function pagination( $max_pages = '', $range = 1, $current_query = '' ) {
				global $wp_query;

				$range = apply_filters( 'fusion_pagination_size', $range );

				if ( '' === $max_pages ) {
					if ( '' === $current_query ) {
						$max_pages = $wp_query->max_num_pages;
						$max_pages = ( ! $max_pages ) ? 1 : $max_pages;
					} else {
						$max_pages = $current_query->max_num_pages;
					}
				}
				$max_pages = intval( $max_pages );

				$blog_global_pagination = apply_filters( 'fusion_builder_blog_pagination', '' );
				$infinite_pagination    = 'pagination' !== $this->args['scrolling'] && 'pagination' !== strtolower( $blog_global_pagination );
				$pagination_html        = fusion_pagination( $max_pages, $range, $current_query, $infinite_pagination, true );

				return apply_filters( 'fusion_builder_blog_pagination_html', $pagination_html, $max_pages, $range, $current_query, $blog_global_pagination );
			}

			/**
			 * Get the HTML <style> element.
			 *
			 * @return string
			 */
			public function get_style_element() {
				$style                   = '';
				$base_class_name         = '.fusion-blog-shortcode.fusion-blog-shortcode-' . $this->blog_sc_counter;
				$this->dynamic_css       = [];
				$title_selector          = $base_class_name . ' .entry-title';
				$timeline_title_selector = $base_class_name . ' .fusion-timeline-date';

				$title_typography = Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'title_font', 'array' );

				if ( isset( $title_typography['font-family'] ) && $title_typography['font-family'] ) {
					$this->add_css_property( $title_selector, 'font-family', $title_typography['font-family'], true );
				}

				if ( isset( $title_typography['font-weight'] ) && $title_typography['font-weight'] ) {
					$this->add_css_property( $title_selector, 'font-weight', $title_typography['font-weight'], true );
				}

				if ( isset( $title_typography['font-style'] ) && $title_typography['font-style'] ) {
					$this->add_css_property( $title_selector, 'font-style', $title_typography['font-style'], true );
				}

				if ( $this->args['title_font_size'] ) {
					$this->add_css_property( $title_selector, 'font-size', $this->args['title_font_size'], true );
				}

				if ( $this->args['title_line_height'] ) {
					$this->add_css_property( $title_selector, 'line-height', $this->args['title_line_height'], true );
				}

				if ( $this->args['title_letter_spacing'] ) {
					$this->add_css_property( $title_selector, 'letter-spacing', $this->args['title_letter_spacing'], true );
				}

				if ( $this->args['title_text_transform'] ) {
					$this->add_css_property( $title_selector, 'text-transform', $this->args['title_text_transform'], true );
				}

				$timeline_typography = Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'timeline_title_font', 'array' );

				if ( isset( $timeline_typography['font-family'] ) && $timeline_typography['font-family'] ) {
					$this->add_css_property( $timeline_title_selector, 'font-family', $timeline_typography['font-family'], true );
				}

				if ( isset( $timeline_typography['font-weight'] ) && $timeline_typography['font-weight'] ) {
					$this->add_css_property( $timeline_title_selector, 'font-weight', $timeline_typography['font-weight'], true );
				}

				if ( isset( $timeline_typography['font-style'] ) && $timeline_typography['font-style'] ) {
					$this->add_css_property( $timeline_title_selector, 'font-style', $timeline_typography['font-style'], true );
				}

				if ( $this->args['timeline_title_font_size'] ) {
					$this->add_css_property( $timeline_title_selector, 'font-size', $this->args['timeline_title_font_size'], true );
				}

				if ( $this->args['timeline_title_line_height'] ) {
					$this->add_css_property( $timeline_title_selector, 'line-height', $this->args['timeline_title_line_height'], true );
				}

				if ( $this->args['timeline_title_letter_spacing'] ) {
					$this->add_css_property( $timeline_title_selector, 'letter-spacing', $this->args['timeline_title_letter_spacing'], true );
				}

				if ( $this->args['timeline_title_text_transform'] ) {
					$this->add_css_property( $timeline_title_selector, 'text-transform', $this->args['timeline_title_text_transform'], true );
				}

				$style = $this->parse_css();

				return $style ? '<style>' . $style . '</style>' : '';
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function attr() {

				$attr = [];

				// Set the correct layout class.
				$blog_layout = 'fusion-blog-layout-' . $this->args['layout'];
				if ( 'timeline' === $this->args['layout'] ) {
					$blog_layout = 'fusion-blog-layout-timeline-wrapper';
				} elseif ( 'grid' === $this->args['layout'] || 'masonry' === $this->args['layout'] ) {
					$blog_layout = 'fusion-blog-layout-grid-wrapper';
				}

				$attr['class'] = 'fusion-blog-shortcode fusion-blog-shortcode-' . $this->blog_sc_counter . ' fusion-blog-archive ' . $blog_layout . ' fusion-blog-' . $this->args['scrolling'];

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				$attr['style'] = Fusion_Builder_Margin_Helper::get_margins_style( $this->args );

				if ( ! $this->args['thumbnail'] ) {
					$attr['class'] .= ' fusion-blog-no-images';
				}

				if ( $this->args['content_alignment'] && ( 'grid' === $this->args['layout'] || 'masonry' === $this->args['layout'] || 'timeline' === $this->args['layout'] ) ) {
					$attr['class'] .= ' fusion-blog-layout-' . $this->args['content_alignment'];
				}

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( 0 === $this->args['blog_grid_column_spacing'] || '0' === $this->args['blog_grid_column_spacing'] || '0px' === $this->args['blog_grid_column_spacing'] ) {
					$attr['class'] .= ' fusion-no-col-space';
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				return $attr;

			}

			/**
			 * Builds the posts-container attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function posts_container_attr() {
				global $post, $fusion_settings;

				$attr = [];

				$load_more = '';
				if ( $this->args['load_more'] ) {
					$load_more = ' fusion-posts-container-load-more';
				}

				$attr['class'] = 'fusion-posts-container fusion-posts-container-' . $this->args['scrolling'] . $load_more;

				if ( ! $this->args['meta_info_combined'] ) {
					$attr['class'] .= ' fusion-no-meta-info';
				}

				// Add class if rollover is enabled.
				if ( $fusion_settings->get( 'image_rollover' ) && $this->args['thumbnail'] ) {
					$attr['class'] .= ' fusion-blog-rollover';
				}

				$attr['data-pages'] = $this->query->max_num_pages;

				if ( 'grid' === $this->args['layout'] || 'masonry' === $this->args['layout'] ) {
					$attr['class'] .= ' fusion-blog-layout-grid fusion-blog-layout-grid-' . $this->args['blog_grid_columns'] . ' isotope';

					if ( 'masonry' === $this->args['layout'] ) {
						$attr['class'] .= ' fusion-blog-layout-masonry';
					}

					if ( 'grid' === $this->args['layout'] ) {
						if ( 'yes' === $this->args['equal_heights'] ) {
							$attr['class'] .= ' fusion-blog-equal-heights';
						}
					}

					if ( $this->args['blog_grid_column_spacing'] || '0' === $this->args['blog_grid_column_spacing'] ) {
						$attr['data-grid-col-space'] = $this->args['blog_grid_column_spacing'];
					}

					$negative_margin = ( -1 ) * $this->args['blog_grid_column_spacing'] / 2;

					$min_height = 'min-height:500px;';

					if ( '1' === $this->args['posts_per_page'] ) {
						$min_height = '';
					}

					$attr['style'] = 'margin: ' . $negative_margin . 'px ' . $negative_margin . 'px 0;' . $min_height;
				}

				return $attr;

			}

			/**
			 * Opens the wrapper.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function wrap_loop_open() {
				global $post;

				$wrapper = $class_timeline_icon = '';

				if ( 'timeline' === $this->args['layout'] ) {

					$wrapper  = '<div ' . FusionBuilder::attributes( 'fusion-timeline-icon' . $class_timeline_icon ) . '>';
					$wrapper .= '<i ' . FusionBuilder::attributes( 'awb-icon-bubbles' ) . ' style="color:' . $this->args['grid_element_color'] . ';" aria-hidden="true"></i>';
					$wrapper .= '</div>';
					$wrapper .= '<div ' . FusionBuilder::attributes( 'fusion-blog-layout-timeline fusion-clearfix' ) . '>';
					$wrapper .= '<div class="fusion-timeline-line" style="border-left:1px solid ' . $this->args['grid_element_color'] . ';border-right:1px solid ' . $this->args['grid_element_color'] . ';"></div>';
				}

				echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput

			}

			/**
			 * Closes the wrapper.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function wrap_loop_close() {

				$wrapper = '';

				if ( 'timeline' === $this->args['layout'] ) {
					if ( $this->post_count > 1 ) {
						$wrapper = '</div>';
					}
					$wrapper .= '</div>';
				}

				if ( 'grid' === $this->args['layout'] || 'masonry' === $this->args['layout'] ) {
					$wrapper .= '<div class="fusion-clearfix"></div>';
				}

				echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput

			}

			/**
			 * Add HTML before the loop.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function before_loop() {
				echo '<article ' . FusionBuilder::attributes( 'blog-shortcode-loop' ) . '>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
			}

			/**
			 * Adds markup after the loop.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function after_loop() {
				if ( 'grid' === $this->args['layout'] || 'masonry' === $this->args['layout'] || 'timeline' === $this->args['layout'] ) {
					echo '</div>' . "\n";
					echo '</article>' . "\n";
				} else {
					echo '</article>' . "\n";
				}
			}

			/**
			 * Builds the loop attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function loop_attr() {
				$defaults = [
					'post_id'    => '',
					'post_count' => '',
				];

				$attr['id'] = 'blog-' . $this->blog_sc_counter . '-post-' . $this->post_id;

				$extra_classes = [];

				// Add the correct post class.
				$extra_classes[] = 'fusion-post-' . $this->args['layout'];

				if ( is_sticky() ) {
					$extra_classes[] = 'fusion-sticky';
				}

				if ( 'masonry' === $this->args['layout'] ) {
					// Additional grid class needed for masonry layout.
					$extra_classes[] = 'fusion-post-grid';

					// Get the element orientation class.
					$element_orientation_class = '';
					if ( has_post_thumbnail() ) {
						$element_orientation_class = fusion_library()->images->get_element_orientation_class( get_post_thumbnail_id(), [], $this->args['blog_masonry_grid_ratio'], $this->args['blog_masonry_width_double'] );
					}

					$extra_classes[] = $element_orientation_class;
				}

				// Set the correct column class for every post.
				if ( 'timeline' === $this->args['layout'] ) {

					if ( ( $this->post_count % 2 ) > 0 ) {
						$timeline_align = ' fusion-left-column';
					} else {
						$timeline_align = ' fusion-right-column';
					}

					$extra_classes[] = 'fusion-clearfix' . $timeline_align;

					$attr['style'] = 'border-color:' . $this->args['grid_element_color'] . ';';
				}

				// Set the has-post-thumbnail if a video is used. This is needed if no featured image is present.
				$post_video = apply_filters( 'fusion_builder_post_video', $this->post_id );

				if ( $post_video ) {
					$extra_classes[] = 'has-post-thumbnail';
				}

				$post_class = get_post_class( $extra_classes, $this->post_id );

				if ( $post_class && is_array( $post_class ) ) {
					$classes       = implode( ' ', $post_class );
					$attr['class'] = $classes;
				}

				return $attr;

			}

			/**
			 * Adds attributes to the content separator.
			 *
			 * @since 1.3
			 * @access public
			 * @return array
			 */
			public function content_sep_attr() {
				$attr = [
					'class' => 'fusion-content-sep',
					'style' => 'border-color:' . $this->args['grid_separator_color'] . ';',
				];

				$separator_styles_array = explode( '|', $this->args['grid_separator_style_type'] );
				$separator_styles       = '';

				foreach ( $separator_styles_array as $separator_style ) {
					$separator_styles .= ' sep-' . $separator_style;
				}

				$attr['class'] .= $separator_styles;

				return $attr;
			}

			/**
			 * Gets the HTML for masonry featured image..
			 *
			 * @access public
			 * @since 1.2
			 * @return string
			 */
			public function get_featured_image_masonry() {
				$fusion_settings = awb_get_fusion_settings();

				$lazy_load                 = ( 'avada' === $fusion_settings->get( 'lazy_load' ) && ! is_feed() ) ? true : false;
				$responsive_images_columns = $this->args['blog_grid_columns'];
				$masonry_attributes        = [];
				$element_base_padding      = 0.8;

				// Set image or placeholder and correct corresponding styling.
				$insert_bg_color = true;
				if ( has_post_thumbnail() ) {
					$post_thumbnail_attachment = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' );
					if ( is_array( $post_thumbnail_attachment ) ) {
						$masonry_attribute_style = $lazy_load ? '' : 'background-image:url(' . $post_thumbnail_attachment[0] . ');';
						$insert_bg_color         = false;
					}
				}
				if ( $insert_bg_color ) {
					$post_thumbnail_attachment = [];
					$masonry_attribute_style   = 'background-color:#f6f6f6;';
				}

				// Get the correct image orientation class.
				$element_orientation_class = fusion_library()->images->get_element_orientation_class( get_post_thumbnail_id(), $post_thumbnail_attachment, $this->args['blog_masonry_grid_ratio'], $this->args['blog_masonry_width_double'] );
				$element_base_padding      = fusion_library()->images->get_element_base_padding( $element_orientation_class );

				$masonry_column_offset = ' - ' . ( (int) $this->args['blog_grid_column_spacing'] / 2 ) . 'px';
				if ( false !== strpos( $element_orientation_class, 'fusion-element-portrait' ) ) {
					$masonry_column_offset = '';
				}

				$masonry_column_spacing = ( (int) $this->args['blog_grid_column_spacing'] ) . 'px';

				// Calculate the correct size of the image wrapper container, based on orientation and column spacing.
				if ( class_exists( 'Fusion_Sanitize' ) && class_exists( 'Fusion_Color' ) && ! Fusion_Color::new_color( $this->args['grid_element_color'] )->is_color_transparent() ) {

					$masonry_column_offset = ' - ' . ( (int) $this->args['blog_grid_column_spacing'] / 2 ) . 'px';
					if ( false !== strpos( $element_orientation_class, 'fusion-element-portrait' ) ) {
						$masonry_column_offset = ' + 4px';
					}

					$masonry_column_spacing = ( (int) $this->args['blog_grid_column_spacing'] - 2 ) . 'px';
					if ( false !== strpos( $element_orientation_class, 'fusion-element-landscape' ) ) {
						$masonry_column_spacing = ( (int) $this->args['blog_grid_column_spacing'] - 6 ) . 'px';
					}
				}

				// Check if a featured image is set and also that not a video with no featured image.
				$post_video = apply_filters( 'fusion_builder_post_video', $this->post_id );
				if ( ! empty( $post_thumbnail_attachment ) || ! $post_video ) {

					// Calculate the correct size of the image wrapper container, based on orientation and column spacing.
					$masonry_attribute_style .= 'padding-top:calc((100% + ' . $masonry_column_spacing . ') * ' . $element_base_padding . $masonry_column_offset . ');';
				}

				// Check if we have a landscape image, then it has to stretch over 2 cols.
				if ( '1' !== $this->args['blog_grid_columns'] && 1 !== $this->args['blog_grid_columns'] && false !== strpos( $element_orientation_class, 'fusion-element-landscape' ) ) {
					$responsive_images_columns = (int) $this->args['blog_grid_columns'] / 2;
				}

				// Set the masonry attributes to use them in the first featured image function.
				$masonry_attributes = [
					'class' => 'fusion-masonry-element-container',
					'style' => $masonry_attribute_style,
				];

				if ( $lazy_load && isset( $post_thumbnail_attachment[0] ) ) {
					$masonry_attributes['data-bg'] = $post_thumbnail_attachment[0];
					$masonry_attributes['class']  .= ' lazyload';
				}

				// Get the post image.
				fusion_library()->images->set_grid_image_meta(
					[
						'layout'       => 'portfolio_full',
						'columns'      => $responsive_images_columns,
						'gutter_width' => $this->args['blog_grid_column_spacing'],
					]
				);

				$image = fusion_render_first_featured_image_markup( $this->post_id, 'full', $this->permalink, false, false, false, 'default', 'default', '', '', 'yes', false, $masonry_attributes );

				fusion_library()->images->set_grid_image_meta( [] );

				return $image;
			}

			/**
			 * Gets the HTML for slideshows.
			 *
			 * @access public
			 * @since 1.0
			 * @return string
			 */
			public function get_slideshow() {
				$fusion_settings = awb_get_fusion_settings();

				$html = '';

				if ( ! post_password_required( $this->post_id ) ) {

					$slideshow = [
						'images' => $this->get_post_thumbnails( $this->post_id, $fusion_settings->get( 'posts_slideshow_number' ) ),
					];

					$post_video = apply_filters( 'fusion_builder_post_video', '', $this->post_id );

					if ( $post_video ) {
						$slideshow['video'] = $post_video;
					}

					if ( 'medium' === $this->args['layout'] || 'medium alternate' === $this->args['layout'] ) {
						$slideshow['size'] = 'blog-medium';
					}

					ob_start();
					$atts = $this->args;

					$atts['loop-id'] = '#blog-' . $this->blog_sc_counter . '-post-';

					include FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/shortcodes/new-slideshow-blog-shortcode.php';

					$post_slideshow_action = ob_get_contents();
					ob_get_clean();

					$html .= $post_slideshow_action;
				}

				return $html;
			}

			/**
			 * Gets the post thumbnails.
			 *
			 * @access public
			 * @since 1.0
			 * @param int $post_id The post-ID.
			 * @param int $count   How many thumbnails.
			 * @return array
			 */
			public function get_post_thumbnails( $post_id, $count = '' ) {
				$fusion_settings = awb_get_fusion_settings();

				$attachment_ids = [];

				if ( get_post_thumbnail_id( $post_id ) ) {
					$attachment_ids[] = get_post_thumbnail_id( $post_id );
				}

				$i                      = 2;
				$posts_slideshow_number = $fusion_settings->get( 'posts_slideshow_number' );
				if ( '' === $posts_slideshow_number ) {
					$posts_slideshow_number = 5;
				}
				while ( $i <= $posts_slideshow_number ) {

					if ( function_exists( 'fusion_get_featured_image_id' ) && fusion_get_featured_image_id( 'featured-image-' . $i, 'post' ) ) {
						$attachment_ids[] = fusion_get_featured_image_id( 'featured-image-' . $i, 'post' );
					}

					$i++;
				}

				if ( isset( $count ) && $count >= 1 ) {
					$attachment_ids = array_slice( $attachment_ids, 0, $count );
				}

				return $attachment_ids;

			} // End get_post_thumbnails().

			/**
			 * Adds the loop-header HTML.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function loop_header() {

				$defaults = [
					'title_link' => false,
				];

				$args = wp_parse_args( $this->header, $defaults );

				$pre_title_content = $meta_data = $content_sep = $link = '';

				if ( $this->args['thumbnail'] && 'medium-alternate' !== $this->args['layout'] ) {

					// Masonry layout.
					if ( 'masonry' === $this->args['layout'] ) {
						$pre_title_content = $this->get_featured_image_masonry();
					} else {
						$pre_title_content = $this->get_slideshow();
					}
				}

				if ( 'medium-alternate' === $this->args['layout'] || 'large-alternate' === $this->args['layout'] ) {
					$pre_title_content .= '<div ' . FusionBuilder::attributes( 'fusion-date-and-formats' ) . '>';
					ob_start();
					do_action( 'fusion_blog_shortcode_date_and_format' );
					$pre_title_content .= ob_get_contents();
					ob_get_clean();
					$pre_title_content .= '</div>';

					if ( $this->args['thumbnail'] && 'medium-alternate' === $this->args['layout'] ) {
						$pre_title_content .= $this->get_slideshow();
					}

					if ( $this->args['meta_all'] ) {
						$meta_data .= fusion_builder_render_post_metadata( 'alternate', $this->meta_info_settings );
					}
				}

				if ( 'grid' === $this->args['layout'] || 'masonry' === $this->args['layout'] || 'timeline' === $this->args['layout'] ) {
					$content_wrapper_styles = '';
					$is_there_meta_above    = 0 < $this->args['meta_all'] * ( $this->args['meta_author'] + $this->args['meta_date'] + $this->args['meta_categories'] + $this->args['meta_tags'] );
					$is_there_meta_below    = $this->args['meta_all'] * $this->args['meta_comments'] || $this->args['meta_all'] * $this->args['meta_link'];
					$is_there_content       = 'no' === $this->args['excerpt'] || ( 'yes' === $this->args['excerpt'] && ! $this->args['is_zero_excerpt'] );

					// See 7199.
					if ( 'masonry' !== $this->args['layout'] && ( ( $this->args['show_title'] && $is_there_meta_above && ( $is_there_content || $is_there_meta_below ) ) || ( $this->args['show_title'] && ! $is_there_meta_above && $is_there_meta_below ) ) ) {
						$content_sep = '<div ' . FusionBuilder::attributes( 'blog-fusion-content-sep' ) . '></div>';
					}

					if ( $this->args['meta_all'] ) {
						$meta_data .= fusion_builder_render_post_metadata( 'grid_timeline', $this->meta_info_settings );
					}
					$pre_title_content .= '<div ' . FusionBuilder::attributes( 'blog-shortcode-post-content-wrapper' ) . '>';
				}

				$pre_title_content .= '<div ' . FusionBuilder::attributes( 'fusion-post-content post-content' ) . '>';

				if ( $this->args['show_title'] ) {
					if ( $this->args['title_link'] ) {
						$link_target       = '';
						$link_icon_target  = apply_filters( 'fusion_builder_link_icon_target', '', $this->post_id );
						$post_links_target = apply_filters( 'fusion_builder_post_links_target', '', $this->post_id );

						if ( 'yes' === $link_icon_target || 'yes' === $post_links_target ) {
							$link_target = ' target="_blank" rel="noopener noreferrer"';
						}

						$link = '<a href="' . esc_url( $this->permalink ) . '"' . $link_target . '>' . get_the_title() . '</a>';
					} else {
						$link = get_the_title();
					}
				}

				if ( 'timeline' === $this->args['layout'] ) {
					$pre_title_content .= '<div ' . FusionBuilder::attributes( 'fusion-timeline-circle' ) . ' style="background-color:' . $this->args['grid_element_color'] . ';"></div>';
					$pre_title_content .= '<div ' . FusionBuilder::attributes( 'fusion-timeline-arrow' ) . ' style="color:' . $this->args['grid_element_color'] . ';"></div>';
				}
				if ( '' !== $link ) {
					$title_tag = $this->get_title_tag( 'post' );
					$link      = '<' . $title_tag . ' ' . FusionBuilder::attributes( 'blog-shortcode-post-title' ) . '>' . $link . '</' . $title_tag . '>';
				}
				$html = $pre_title_content . $link . $meta_data . $content_sep;

				echo $html; // phpcs:ignore WordPress.Security.EscapeOutput

			} // End loop_header().

			/**
			 * Builds the post-title attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function post_title_attr() {
				$fusion_settings = awb_get_fusion_settings();

				$attr = [];

				$attr['class'] = 'blog-shortcode-post-title';

				if ( $fusion_settings->get( 'disable_date_rich_snippet_pages' ) && $fusion_settings->get( 'disable_rich_snippet_title' ) ) {
					$attr['class'] .= ' entry-title';
				}

				return $attr;

			}

			/**
			 * Builds the fusion-post-title-wrapper attributes array.
			 *
			 * @access public
			 * @since 1.2
			 * @return array
			 */
			public function post_content_wrapper_attr() {

				$attr = [
					'class' => 'fusion-post-content-wrapper',
				];

				if ( 'grid' === $this->args['layout'] || 'timeline' === $this->args['layout'] || 'masonry' === $this->args['layout'] ) {
					$padding       = ( is_array( $this->args['blog_grid_padding'] ) ) ? implode( ' ', $this->args['blog_grid_padding'] ) : $this->args['blog_grid_padding'];
					$attr['style'] = 'padding:' . $padding . ';';

					if ( 'masonry' === $this->args['layout'] ) {
						$color     = Fusion_Color::new_color( $this->args['grid_box_color'] );
						$color_css = $color->to_css_var_or_rgba();
						if ( $color->is_color_transparent() ) {
							$color_css = $color->get_new( 'alpha', 1 );
							$color_css = $color_css->to_css_var_or_rgba();
						}
						$attr['style'] .= 'background-color:' . $color_css . ';';
					}

					if ( ! $this->args['meta_info_combined'] && ( $this->args['is_zero_excerpt'] || 'hide' === $this->args['excerpt'] ) && ! $this->args['show_title'] ) {
						$attr['style'] .= ' display:none;';
					}
				}

				return $attr;
			}

			/**
			 * Builds the fusion-post-wrapper attributes array.
			 *
			 * @access public
			 * @since 1.3
			 * @return array
			 */
			public function post_wrapper_attr() {
				$attr = [
					'class' => 'fusion-post-wrapper',
				];

				if ( 'masonry' === $this->args['layout'] ) {
					$color     = Fusion_Color::new_color( $this->args['grid_box_color'] );
					$color_css = $color->to_css_var_or_rgba();
					if ( $color->is_color_transparent() ) {
						$color_css = $color->get_new( 'alpha', 1 );
						$color_css = $color_css->to_css_var_or_rgba();
					}
					$attr['style'] = 'background-color:' . $color_css . ';';

					if ( Fusion_Color::new_color( $this->args['grid_element_color'] )->is_color_transparent() ) {
						$attr['class'] .= ' fusion-masonary-is-transparent ';
						$attr['style'] .= 'border:none;';
					} else {
						$attr['style'] .= 'border:1px solid ' . $this->args['grid_element_color'] . ';border-bottom-width:3px;';
					}
				} elseif ( 'grid' === $this->args['layout'] ) {
					$color         = Fusion_Color::new_color( $this->args['grid_box_color'] );
					$color_css     = $color->to_css_var_or_rgba();
					$attr['style'] = 'background-color:' . $color_css . ';';

					if ( Fusion_Color::new_color( $this->args['grid_element_color'] )->is_color_transparent() ) {
						$attr['style'] .= 'border:none;';
					} else {
						$attr['style'] .= 'border:1px solid ' . $this->args['grid_element_color'] . ';border-bottom-width:3px;';
					}
				} elseif ( 'timeline' === $this->args['layout'] ) {
					$color         = Fusion_Color::new_color( $this->args['grid_box_color'] );
					$color_css     = $color->to_css_var_or_rgba();
					$attr['style'] = 'background-color:' . $color_css . ';';
				}
				return $attr;
			}

			/**
			 * Adds the loop-footer HTML.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function loop_footer() {
				if ( in_array( $this->args['layout'], [ 'grid', 'masonry', 'timeline' ], true ) ) {
					echo '</div>';

					if ( 0 < $this->args['meta_info_combined'] && ( $this->args['meta_comments'] || $this->args['meta_link'] ) ) {
						$inner_content  = $this->read_more();
						$inner_content .= $this->grid_timeline_comments();

						echo '<div class="fusion-meta-info">' . $inner_content . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
					}
				}

				echo '</div>';
				echo '<div class="fusion-clearfix"></div>';

				if ( 0 < $this->args['meta_info_combined'] && in_array( $this->args['layout'], [ 'large', 'medium' ], true ) ) {
					echo '<div class="fusion-meta-info">' . fusion_builder_render_post_metadata( 'standard', $this->meta_info_settings ) . $this->read_more() . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
				}

				if ( $this->args['meta_all'] && in_array( $this->args['layout'], [ 'large-alternate', 'medium-alternate' ], true ) ) {
					echo $this->read_more(); // phpcs:ignore WordPress.Security.EscapeOutput
				}

			}

			/**
			 * Adds the date box.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function add_date_box() {
				$fusion_settings = awb_get_fusion_settings();

				$inner_content  = '<div ' . FusionBuilder::attributes( 'fusion-date-box updated' ) . '>';
				$inner_content .= '<span ' . FusionBuilder::attributes( 'fusion-date' ) . '>' . get_the_time( $fusion_settings->get( 'alternate_date_format_day' ) ) . '</span>';
				$inner_content .= '<span ' . FusionBuilder::attributes( 'fusion-month-year' ) . '>' . get_the_time( $fusion_settings->get( 'alternate_date_format_month_year' ) ) . '</span>';
				$inner_content .= '</div>';

				echo $inner_content; // phpcs:ignore WordPress.Security.EscapeOutput

			}

			/**
			 * Adds the format box.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function add_format_box() {

				switch ( get_post_format() ) {
					case 'gallery':
						$format_class = 'images';
						break;
					case 'link':
						$format_class = 'link';
						break;
					case 'image':
						$format_class = 'image';
						break;
					case 'quote':
						$format_class = 'quotes-left';
						break;
					case 'video':
						$format_class = 'film';
						break;
					case 'audio':
						$format_class = 'headphones';
						break;
					case 'chat':
						$format_class = 'bubbles';
						break;
					default:
						$format_class = 'pen';
						break;
				}

				$inner_content  = '<div ' . FusionBuilder::attributes( 'fusion-format-box' ) . '>';
				$inner_content .= '<i ' . FusionBuilder::attributes( 'awb-icon-' . $format_class ) . ' aria-hidden="true"></i>';
				$inner_content .= '</div>';

				echo $inner_content; // phpcs:ignore WordPress.Security.EscapeOutput

			}

			/**
			 * Adds the timeline date.
			 *
			 * @access public
			 * @since 1.0
			 * @param array $date_params The date parameters.
			 */
			public function timeline_date( $date_params ) {
				$fusion_settings = awb_get_fusion_settings();

				$defaults = [
					'prev_post_month' => null,
					'post_month'      => null,
					'prev_post_year'  => null,
					'post_year'       => null,
				];

				$args          = wp_parse_args( $date_params, $defaults );
				$inner_content = '';

				if ( $args['prev_post_month'] !== $args['post_month'] || $args['prev_post_year'] !== $args['post_year'] ) {

					if ( $this->post_count > 1 ) {
						$inner_content = '</div>';
					}

					$timeline_size  = $this->get_title_tag( 'timeline_title' );
					$inner_content .= '<' . $timeline_size . ' ' . FusionBuilder::attributes( 'fusion-timeline-date' ) . ' style="background-color:' . $this->args['grid_element_color'] . ';">' . get_the_date( $fusion_settings->get( 'timeline_date_format' ) ) . '</' . $timeline_size . '>';
					$inner_content .= '<div class="fusion-collapse-month">';
				}

				echo $inner_content; // phpcs:ignore WordPress.Security.EscapeOutput

			}

			/**
			 * The timeline comments for grids.
			 *
			 * @access public
			 * @since 1.0
			 * @return string
			 */
			public function grid_timeline_comments() {

				if ( $this->args['meta_comments'] ) {

					$comments_icon = '<i ' . FusionBuilder::attributes( 'awb-icon-bubbles' ) . ' aria-hidden="true"></i>&nbsp;';

					$comments = '<i class="awb-icon-bubbles" aria-hidden="true"></i>&nbsp;' . esc_attr__( 'Protected', 'fusion-builder' );

					if ( ! post_password_required( $this->post_id ) ) {
						if ( '#' === $this->permalink ) {
							$comments = '<a href="#">' . $comments_icon . get_comments_number() . '</a>';
						} else {
							ob_start();
							comments_popup_link( $comments_icon . '0', $comments_icon . '1', $comments_icon . '%' );
							$comments = ob_get_contents();
							ob_get_clean();
						}
					}

					$comment_align_class = 'fusion-alignright';
					if ( ( ! $this->args['meta_link'] || ! $this->args['meta_read'] ) && $this->args['content_alignment'] ) {
						$comment_align_class = 'fusion-align' . $this->args['content_alignment'];
					}

					return '<div ' . FusionBuilder::attributes( $comment_align_class ) . '>' . $comments . '</div>';

				}

			}

			/**
			 * The read-more element.
			 *
			 * @access public
			 * @since 1.0
			 * @return string
			 */
			public function read_more() {

				if ( $this->args['meta_link'] ) {
					$inner_content = '';

					if ( $this->args['meta_read'] ) {

						$read_more_wrapper_class = 'fusion-alignright';
						if ( 'grid' === $this->args['layout'] || 'masonry' === $this->args['layout'] || 'timeline' === $this->args['layout'] ) {
							$read_more_wrapper_class = 'fusion-alignleft';

							if ( $this->args['content_alignment'] && ! $this->args['meta_comments'] ) {
								$read_more_wrapper_class = 'fusion-align' . $this->args['content_alignment'];
							}
						}

						$link_target       = '';
						$link_icon_target  = apply_filters( 'fusion_builder_link_icon_target', '', $this->post_id );
						$post_links_target = apply_filters( 'fusion_builder_post_links_target', '', $this->post_id );

						if ( 'yes' === $link_icon_target || 'yes' === $post_links_target ) {
							$link_target = ' target="_blank" rel="noopener noreferrer"';
						}

						$inner_content .= '<div ' . FusionBuilder::attributes( $read_more_wrapper_class ) . '>';
						$inner_content .= '<a class="fusion-read-more" href="' . esc_url( $this->permalink ) . '"' . $link_target . ' aria-label="' . esc_attr__( 'More on', 'fusion-builder' ) . ' ' . esc_attr( get_the_title() ) . '">';
						$inner_content .= apply_filters( 'avada_read_more_name', esc_attr__( 'Read More', 'fusion-builder' ) );
						$inner_content .= '</a>';
						$inner_content .= '</div>';

						if ( 'large-alternate' === $this->args['layout'] || 'medium-alternate' === $this->args['layout'] ) {
							$inner_content = '<div class="fusion-meta-info">' . $inner_content . '</div>';
						}
					}

					return $inner_content;
				}

			}

			/**
			 * The loop content.
			 *
			 * @access public
			 * @since 1.0
			 * @return void
			 */
			public function loop_content() {

				if ( 'hide' !== $this->args['excerpt'] ) {
					$content = fusion_builder_get_post_content( '', $this->args['excerpt'], $this->args['excerpt_length'], $this->args['strip_html'] );

					echo '<div class="fusion-post-content-container">' . $content . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
				}

			}

			/**
			 * The page links.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function page_links() {
				fusion_link_pages();
			}

			/**
			 * Get the tag of the title.
			 *
			 * @param string $title_type Either 'post' or 'timeline_title'.
			 * @return string
			 */
			public function get_title_tag( $title_type ) {
				if ( 'post' === $title_type ) {
					$tag_option = $this->args['title_size'];
					if ( ! $tag_option ) {
						return 'h2';
					}
				} elseif ( 'timeline_title' === $title_type ) {
					$tag_option = $this->args['timeline_title_size'];
					if ( ! $tag_option ) {
						return 'h3';
					}
				}

				if ( is_numeric( $tag_option ) ) {
					return 'h' . $tag_option;
				}

				return $tag_option;
			}

			/**
			 * Builds the dynamic styling.
			 *
			 * @access public
			 * @since 1.1
			 * @return array
			 */
			public function add_styling() {

				global $wp_version, $six_fourty_media_query, $three_twenty_six_fourty_media_query, $ipad_portrait_media_query, $content_min_media_query, $small_media_query, $medium_media_query, $large_media_query, $fusion_settings, $dynamic_css_helpers;

				if ( $fusion_settings->get( 'blog_grid_column_spacing' ) || '0' === $fusion_settings->get( 'blog_grid_column_spacing' ) ) {

					$css['global']['#posts-container.fusion-blog-layout-grid']['margin'] = '-' . intval( $fusion_settings->get( 'blog_grid_column_spacing' ) / 2 ) . 'px -' . intval( $fusion_settings->get( 'blog_grid_column_spacing' ) / 2 ) . 'px 0 -' . intval( $fusion_settings->get( 'blog_grid_column_spacing' ) / 2 ) . 'px';

					$css['global']['#posts-container.fusion-blog-layout-grid .fusion-post-grid']['padding'] = intval( $fusion_settings->get( 'blog_grid_column_spacing' ) / 2 ) . 'px';

				}

				if ( $fusion_settings->get( 'slideshow_smooth_height' ) ) {
					$css['global']['.fusion-flexslider.fusion-post-slideshow']['overflow'] = 'hidden';
				}

				return $css;
			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.1
			 * @return array $sections Blog settings.
			 */
			public function add_options() {
				return [
					'blog_shortcode_section' => [
						'label'       => esc_attr__( 'Blog', 'fusion-builder' ),
						'description' => '',
						'id'          => 'blog_shortcode_section',
						'default'     => '',
						'icon'        => 'fusiona-blog',
						'type'        => 'accordion',
						'fields'      => [
							'blog_grid_columns'        => [
								'label'       => esc_attr__( 'Number of Columns', 'fusion-builder' ),
								'description' => __( 'Set the number of columns per row for grid and masonry layout. <strong>IMPORTANT:</strong> Masonry layout does not work with 1 column.', 'fusion-builder' ),
								'id'          => 'blog_grid_columns',
								'default'     => 3,
								'type'        => 'slider',
								'choices'     => [
									'min'  => 1,
									'max'  => 6,
									'step' => 1,
								],
								'transport'   => 'postMessage',
							],
							'blog_grid_column_spacing' => [
								'label'       => esc_attr__( 'Column Spacing', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the column spacing for blog posts for grid and masonry layout.', 'fusion-builder' ),
								'id'          => 'blog_grid_column_spacing',
								'default'     => '40',
								'type'        => 'slider',
								'choices'     => [
									'min'  => '0',
									'step' => '1',
									'max'  => '300',
									'edit' => 'yes',
								],
								'transport'   => 'postMessage',
							],
							'blog_grid_padding'        => [
								'label'       => esc_attr__( 'Blog Grid Text Padding', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the top/right/bottom/left padding of the blog text when using grid / masonry or timeline layout.', 'fusion-builder' ),
								'id'          => 'blog_grid_padding',
								'choices'     => [
									'top'    => true,
									'bottom' => true,
									'left'   => true,
									'right'  => true,
								],
								'default'     => [
									'top'    => '30px',
									'bottom' => '25px',
									'left'   => '25px',
									'right'  => '25px',
								],
								'type'        => 'spacing',

								// Could update variable here, but does not look necessary as set inline.
								'transport'   => 'postMessage',
							],
							'blog_excerpt'             => [
								'label'       => esc_attr__( 'Content Display', 'fusion-builder' ),
								'description' => esc_attr__( 'Controls if the post content displays an excerpt, full content or is completely disabled for blog elements.', 'fusion-builder' ),
								'id'          => 'blog_excerpt',
								'default'     => 'yes',
								'type'        => 'radio-buttonset',
								'choices'     => [
									'hide' => esc_attr__( 'No Text', 'fusion-builder' ),
									'yes'  => esc_attr__( 'Excerpt', 'fusion-builder' ),
									'no'   => esc_attr__( 'Full Content', 'fusion-builder' ),
								],
								'transport'   => 'postMessage',
							],
							'blog_excerpt_length'      => [
								'label'       => esc_attr__( 'Excerpt Length', 'fusion-builder' ),
								'description' => esc_attr__( 'Controls the number of words in the excerpts for blog elements.', 'fusion-builder' ),
								'id'          => 'blog_excerpt_length',
								'default'     => '10',
								'type'        => 'slider',
								'choices'     => [
									'min'  => '0',
									'max'  => '500',
									'step' => '1',
								],
								'required'    => [
									[
										'setting'  => 'blog_excerpt',
										'operator' => '==',
										'value'    => 'yes',
									],
								],
								'transport'   => 'postMessage',
							],
							'blog_element_load_more_posts_button_bg_color' => [
								'label'       => esc_attr__( 'Load More Posts Button Background Color', 'fusion-core' ),
								'description' => esc_attr__( 'Controls the background color of the load more button for ajax post loading for blog elements.', 'fusion-core' ),
								'id'          => 'blog_element_load_more_posts_button_bg_color',
								'default'     => 'var(--awb-color7)',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--blog_element_load_more_posts_button_bg_color',
										'element'  => '.fusion-load-more-button',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'blog_element_load_more_posts_button_text_color' => [
								'label'       => esc_attr__( 'Load More Posts Button Text Color', 'fusion-core' ),
								'description' => esc_attr__( 'Controls the text color of the load more button for ajax post loading for blog elements.', 'fusion-core' ),
								'id'          => 'blog_element_load_more_posts_button_text_color',
								'default'     => 'var(--awb-color1)',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--blog_element_load_more_posts_button_text_color',
										'element'  => '.fusion-load-more-button',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'blog_element_load_more_posts_hover_button_bg_color' => [
								'label'       => esc_attr__( 'Load More Posts Button Hover Background Color', 'fusion-core' ),
								'description' => esc_attr__( 'Controls the hover background color of the load more button for ajax post loading for blog elements.', 'fusion-core' ),
								'id'          => 'blog_element_load_more_posts_hover_button_bg_color',
								'default'     => 'var(--awb-color5)',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--blog_element_load_more_posts_hover_button_bg_color',
										'element'  => '.fusion-load-more-button',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'blog_element_load_more_posts_hover_button_text_color' => [
								'label'       => esc_attr__( 'Load More Posts Hover Button Text Color', 'fusion-core' ),
								'description' => esc_attr__( 'Controls the hover text color of the load more button for ajax post loading for blog elements.', 'fusion-core' ),
								'id'          => 'blog_element_load_more_posts_hover_button_text_color',
								'default'     => 'var(--awb-color1)',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--blog_element_load_more_posts_hover_button_text_color',
										'element'  => '.fusion-load-more-button',
										'callback' => [ 'sanitize_color' ],
									],
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

				Fusion_Dynamic_JS::enqueue_script( 'fusion-blog' );
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/blog.min.css' );
			}
		}
	}

	new FusionSC_Blog();

}

add_filter( 'redirect_canonical', 'fusion_blog_redirect_canonical' );
/**
 * Make sure that the blog pagination also works on front page.
 *
 * @since 1.0
 * @param string $redirect_url The URL we want to redirect to.
 * @return string
 */
function fusion_blog_redirect_canonical( $redirect_url ) {
	global $wp_rewrite, $wp_query;

	if ( $wp_rewrite->using_permalinks() ) {

		$paged = 1;
		// Check the query var.
		if ( get_query_var( 'paged' ) ) {
			$paged = get_query_var( 'paged' );
			// Check query paged.
		} elseif ( ! empty( $wp_query->query['paged'] ) ) {
			$paged = $wp_query->query['paged'];
		}

		if ( 1 < $paged ) {
			return false;
		}
	}

	return $redirect_url;
}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_blog() {
	$fusion_settings = awb_get_fusion_settings();

	$builder_status = function_exists( 'is_fusion_editor' ) && is_fusion_editor();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Blog',
			[
				'name'       => esc_attr__( 'Blog', 'fusion-builder' ),
				'shortcode'  => 'fusion_blog',
				'icon'       => 'fusiona-blog',
				'preview'    => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-blog-preview.php',
				'preview_id' => 'fusion-builder-block-module-blog-preview-template',
				'help_url'   => 'https://theme-fusion.com/documentation/avada/elements/blog-element/',
				'params'     => [
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Blog Layout', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the layout for the element', 'fusion-builder' ),
						'param_name'  => 'layout',
						'default'     => 'large',
						'value'       => [
							'large'            => esc_attr__( 'Large', 'fusion-builder' ),
							'medium'           => esc_attr__( 'Medium', 'fusion-builder' ),
							'large alternate'  => esc_attr__( 'Large Alternate', 'fusion-builder' ),
							'medium alternate' => esc_attr__( 'Medium Alternate', 'fusion-builder' ),
							'grid'             => esc_attr__( 'Grid', 'fusion-builder' ),
							'timeline'         => esc_attr__( 'Timeline', 'fusion-builder' ),
							'masonry'          => esc_attr__( 'Masonry', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Number of Columns', 'fusion-builder' ),
						'description' => __( 'Set the number of columns per row. <strong>IMPORTANT:</strong> Masonry layout does not work with 1 column.', 'fusion-builder' ),
						'param_name'  => 'blog_grid_columns',
						'value'       => '',
						'default'     => $fusion_settings->get( 'blog_grid_columns' ),
						'min'         => '1',
						'max'         => '6',
						'step'        => '1',
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'medium',
								'operator' => '!=',
							],
							[
								'element'  => 'layout',
								'value'    => 'large',
								'operator' => '!=',
							],
							[
								'element'  => 'layout',
								'value'    => 'medium alternate',
								'operator' => '!=',
							],
							[
								'element'  => 'layout',
								'value'    => 'large alternate',
								'operator' => '!=',
							],
							[
								'element'  => 'layout',
								'value'    => 'timeline',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Column Spacing', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the column spacing for blog posts.', 'fusion-builder' ),
						'param_name'  => 'blog_grid_column_spacing',
						'value'       => '',
						'default'     => $fusion_settings->get( 'blog_grid_column_spacing' ),
						'min'         => '0',
						'step'        => '1',
						'max'         => '300',
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'medium',
								'operator' => '!=',
							],
							[
								'element'  => 'layout',
								'value'    => 'large',
								'operator' => '!=',
							],
							[
								'element'  => 'layout',
								'value'    => 'medium alternate',
								'operator' => '!=',
							],
							[
								'element'  => 'layout',
								'value'    => 'large alternate',
								'operator' => '!=',
							],
							[
								'element'  => 'layout',
								'value'    => 'timeline',
								'operator' => '!=',
							],
							[
								'element'  => 'blog_grid_columns',
								'value'    => 1,
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Masonry Image Aspect Ratio', 'fusion-builder' ),
						'description' => __( 'Set the ratio to decide when an image should become landscape (ratio being width : height) and portrait (ratio being height : width). <strong>IMPORTANT:</strong> The value of "1.0" represents a special case, which will use the auto calculated ratios like in versions prior to Avada 5.5.', 'fusion-builder' ),
						'param_name'  => 'blog_masonry_grid_ratio',
						'value'       => '',
						'min'         => '1',
						'max'         => '4',
						'step'        => '0.1',
						'default'     => $fusion_settings->get( 'masonry_grid_ratio' ),
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'masonry',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Masonry 2x2 Width', 'fusion-builder' ),
						'description' => __( 'This option decides when a square 1x1 image should become 2x2. This will not apply to images that highly favor landscape or portrait layouts. <strong>IMPORTANT:</strong> There is a “Masonry Image Layout” setting for every image in the WP media library that allows you to manually set how an image will appear (1x1, landscape, portrait or 2x2), regardless of the original ratio. In pixels.', 'fusion-builder' ),
						'param_name'  => 'blog_masonry_width_double',
						'value'       => '',
						'min'         => '200',
						'max'         => '5120',
						'step'        => '1',
						'default'     => $fusion_settings->get( 'masonry_width_double' ),
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'masonry',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Equal Heights', 'fusion-builder' ),
						'description' => esc_attr__( 'Set to yes to display grid boxes with equal heights per row.', 'fusion-builder' ),
						'param_name'  => 'equal_heights',
						'default'     => 'no',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'grid',
								'operator' => '==',
							],
							[
								'element'  => 'blog_grid_columns',
								'value'    => 1,
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Posts Per Page', 'fusion-builder' ),
						'description' => esc_attr__( 'Select number of posts per page. Set to -1 to display all. Set to 0 to use number of posts from Settings > Reading.', 'fusion-builder' ),
						'param_name'  => 'number_posts',
						'value'       => '6',
						'min'         => '-1',
						'max'         => '25',
						'step'        => '1',
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_blog',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'multiple_select',
						'heading'     => esc_attr__( 'Post Status', 'fusion-builder' ),
						'placeholder' => esc_attr__( 'Post Status', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the status(es) of the posts that should be included or leave blank for published only posts.', 'fusion-builder' ),
						'param_name'  => 'post_status',
						'value'       => [
							'publish' => esc_attr__( 'Published' ),
							'draft'   => esc_attr__( 'Drafted' ),
							'future'  => esc_attr__( 'Scheduled' ),
							'private' => esc_attr__( 'Private' ),
							'pending' => esc_attr__( 'Pending' ),
						],
						'default'     => '',
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_blog',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Post Offset', 'fusion-builder' ),
						'description' => esc_attr__( 'The number of posts to skip. ex: 1.', 'fusion-builder' ),
						'param_name'  => 'offset',
						'value'       => '0',
						'min'         => '0',
						'max'         => '25',
						'step'        => '1',
						'dependency'  => [
							[
								'element'  => 'number_posts',
								'value'    => '-1',
								'operator' => '!=',
							],
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_blog',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Pull Posts By', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to show posts by category or tag.', 'fusion-builder' ),
						'param_name'  => 'pull_by',
						'default'     => 'category',
						'value'       => [
							'category' => esc_attr__( 'Category', 'fusion-builder' ),
							'tag'      => esc_attr__( 'Tag', 'fusion-builder' ),
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_blog',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'multiple_select',
						'heading'     => esc_attr__( 'Categories', 'fusion-builder' ),
						'placeholder' => esc_attr__( 'Categories', 'fusion-builder' ),
						'description' => esc_attr__( 'Select categories or leave blank for all.', 'fusion-builder' ),
						'param_name'  => 'cat_slug',
						'value'       => $builder_status ? fusion_builder_shortcodes_categories( 'category' ) : [],
						'default'     => '',
						'dependency'  => [
							[
								'element'  => 'pull_by',
								'value'    => 'tag',
								'operator' => '!=',
							],
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_blog',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'multiple_select',
						'heading'     => esc_attr__( 'Exclude Categories', 'fusion-builder' ),
						'placeholder' => esc_attr__( 'Categories', 'fusion-builder' ),
						'description' => esc_attr__( 'Select categories to exclude.', 'fusion-builder' ),
						'param_name'  => 'exclude_cats',
						'value'       => $builder_status ? fusion_builder_shortcodes_categories( 'category' ) : [],
						'default'     => '',
						'dependency'  => [
							[
								'element'  => 'pull_by',
								'value'    => 'tag',
								'operator' => '!=',
							],
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_blog',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'multiple_select',
						'heading'     => esc_attr__( 'Tags', 'fusion-builder' ),
						'placeholder' => esc_attr__( 'Tags', 'fusion-builder' ),
						'description' => esc_attr__( 'Select a tag or leave blank for all.', 'fusion-builder' ),
						'param_name'  => 'tag_slug',
						'value'       => $builder_status ? fusion_builder_shortcodes_tags( 'post_tag' ) : [],
						'default'     => '',
						'dependency'  => [
							[
								'element'  => 'pull_by',
								'value'    => 'category',
								'operator' => '!=',
							],
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_blog',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'multiple_select',
						'heading'     => esc_attr__( 'Exclude Tags', 'fusion-builder' ),
						'placeholder' => esc_attr__( 'Tags', 'fusion-builder' ),
						'description' => esc_attr__( 'Select a tag to exclude.', 'fusion-builder' ),
						'param_name'  => 'exclude_tags',
						'value'       => $builder_status ? fusion_builder_shortcodes_tags( 'post_tag' ) : [],
						'default'     => '',
						'dependency'  => [
							[
								'element'  => 'pull_by',
								'value'    => 'category',
								'operator' => '!=',
							],
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_blog',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Order By', 'fusion-builder' ),
						'description' => esc_attr__( 'Defines how posts should be ordered.', 'fusion-builder' ),
						'param_name'  => 'orderby',
						'default'     => 'date',
						'value'       => [
							'date'          => esc_attr__( 'Date', 'fusion-builder' ),
							'title'         => esc_attr__( 'Post Title', 'fusion-builder' ),
							'name'          => esc_attr__( 'Post Slug', 'fusion-builder' ),
							'author'        => esc_attr__( 'Author', 'fusion-builder' ),
							'comment_count' => esc_attr__( 'Number of Comments', 'fusion-builder' ),
							'modified'      => esc_attr__( 'Last Modified', 'fusion-builder' ),
							'rand'          => esc_attr__( 'Random', 'fusion-builder' ),
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_blog',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Order', 'fusion-builder' ),
						'description' => esc_attr__( 'Defines the sorting order of posts.', 'fusion-builder' ),
						'param_name'  => 'order',
						'default'     => 'DESC',
						'value'       => [
							'DESC' => esc_attr__( 'Descending', 'fusion-builder' ),
							'ASC'  => esc_attr__( 'Ascending', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'orderby',
								'value'    => 'rand',
								'operator' => '!=',
							],
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_blog',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Thumbnail', 'fusion-builder' ),
						'description' => esc_attr__( 'Display the post featured image.', 'fusion-builder' ),
						'param_name'  => 'thumbnail',
						'default'     => 'yes',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'masonry',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Title', 'fusion-builder' ),
						'description' => esc_attr__( 'Display the post title below the featured image.', 'fusion-builder' ),
						'param_name'  => 'title',
						'default'     => 'yes',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Title Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose HTML tag of the title heading, either div or the heading tag, h1-h6.', 'fusion-builder' ),
						'param_name'  => 'title_size',
						'value'       => [
							'1'   => 'H1',
							'2'   => 'H2',
							'3'   => 'H3',
							'4'   => 'H4',
							'5'   => 'H5',
							'6'   => 'H6',
							'div' => 'DIV',
						],
						'default'     => '2',
						'dependency'  => [
							[
								'element'  => 'title',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'             => 'typography',
						'remove_from_atts' => true,
						'global'           => true,
						'heading'          => esc_attr__( 'Post Title Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the title typography', 'fusion-builder' ),
						'param_name'       => 'title_typography',
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
						'dependency'       => [
							[
								'element'  => 'title',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Timeline Title Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose HTML tag of the timeline title heading, either div or the heading tag, h1-h6.', 'fusion-builder' ),
						'param_name'  => 'timeline_title_size',
						'value'       => [
							'1'   => 'H1',
							'2'   => 'H2',
							'3'   => 'H3',
							'4'   => 'H4',
							'5'   => 'H5',
							'6'   => 'H6',
							'div' => 'DIV',
						],
						'default'     => '3',
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'timeline',
								'operator' => '==',
							],
						],
					],
					[
						'type'             => 'typography',
						'remove_from_atts' => true,
						'global'           => true,
						'heading'          => esc_attr__( 'Timeline Title Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the timeline title typography', 'fusion-builder' ),
						'param_name'       => 'timeline_title_typography',
						'choices'          => [
							'font-family'    => 'timeline_title_font',
							'font-size'      => 'timeline_title_font_size',
							'line-height'    => 'timeline_title_line_height',
							'letter-spacing' => 'timeline_title_letter_spacing',
							'text-transform' => 'timeline_title_text_transform',
						],
						'default'          => [
							'font-family'    => '',
							'variant'        => '',
							'font-size'      => '',
							'line-height'    => '',
							'letter-spacing' => '',
							'text-transform' => '',
						],
						'dependency'       => [
							[
								'element'  => 'layout',
								'value'    => 'timeline',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Link Title To Post', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose if the title should be a link to the single post page.', 'fusion-builder' ),
						'default'     => 'yes',
						'param_name'  => 'title_link',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'title',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Content Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the alignment of contents.', 'fusion-builder' ),
						'param_name'  => 'content_alignment',
						'default'     => '',
						'value'       => [
							''       => esc_attr__( 'Text Flow', 'fusion-builder' ),
							'left'   => esc_attr__( 'Left', 'fusion-builder' ),
							'center' => esc_attr__( 'Center', 'fusion-builder' ),
							'right'  => esc_attr__( 'Right', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'medium',
								'operator' => '!=',
							],
							[
								'element'  => 'layout',
								'value'    => 'large',
								'operator' => '!=',
							],
							[
								'element'  => 'layout',
								'value'    => 'medium alternate',
								'operator' => '!=',
							],
							[
								'element'  => 'layout',
								'value'    => 'large alternate',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Content Display', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls if the blog post content is displayed as excerpt, full content or is completely disabled.', 'fusion-builder' ),
						'param_name'  => 'excerpt',
						'value'       => [
							''     => esc_attr__( 'Default', 'fusion-builder' ),
							'yes'  => esc_attr__( 'Excerpt', 'fusion-builder' ),
							'no'   => esc_attr__( 'Full Content', 'fusion-builder' ),
							'hide' => esc_attr__( 'No Text', 'fusion-builder' ),
						],
						'default'     => '',
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Excerpt Length', 'fusion-builder' ),
						'description' => esc_attr__( 'Insert the number of words/characters you want to show in the excerpt.', 'fusion-builder' ),
						'param_name'  => 'excerpt_length',
						'value'       => '',
						'min'         => '0',
						'max'         => '500',
						'step'        => '1',
						'default'     => $fusion_settings->get( 'blog_excerpt_length' ),
						'dependency'  => [
							[
								'element'  => 'excerpt',
								'value'    => 'no',
								'operator' => '!=',
							],
							[
								'element'  => 'excerpt',
								'value'    => 'hide',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Strip HTML from Posts Content', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to strip HTML from the post content.', 'fusion-builder' ),
						'param_name'  => 'strip_html',
						'default'     => 'yes',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'excerpt',
								'value'    => 'no',
								'operator' => '!=',
							],
							[
								'element'  => 'excerpt',
								'value'    => 'hide',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Meta Info', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to show all meta data.', 'fusion-builder' ),
						'param_name'  => 'meta_all',
						'default'     => 'yes',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Author Name', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to show the author.', 'fusion-builder' ),
						'param_name'  => 'meta_author',
						'default'     => 'yes',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'meta_all',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Categories', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to show the categories.', 'fusion-builder' ),
						'param_name'  => 'meta_categories',
						'default'     => 'yes',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'meta_all',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Comment Count', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to show the comments.', 'fusion-builder' ),
						'param_name'  => 'meta_comments',
						'default'     => 'yes',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'meta_all',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Date', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to show the date.', 'fusion-builder' ),
						'param_name'  => 'meta_date',
						'default'     => 'yes',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'meta_all',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Read More Link', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to show the Read More link.', 'fusion-builder' ),
						'param_name'  => 'meta_link',
						'default'     => 'yes',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'meta_all',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Tags', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to show the tags.', 'fusion-builder' ),
						'param_name'  => 'meta_tags',
						'default'     => 'yes',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'meta_all',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Pagination Type', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the type of pagination.', 'fusion-builder' ),
						'param_name'  => 'scrolling',
						'default'     => 'pagination',
						'value'       => [
							'no'               => esc_attr__( 'No Pagination', 'fusion-builder' ),
							'pagination'       => esc_attr__( 'Pagination', 'fusion-builder' ),
							'infinite'         => esc_attr__( 'Infinite Scrolling', 'fusion-builder' ),
							'load_more_button' => esc_attr__( 'Load More Button', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Grid Box Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background color for the grid boxes.', 'fusion-builder' ),
						'param_name'  => 'grid_box_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'timeline_bg_color' ),
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'medium',
								'operator' => '!=',
							],
							[
								'element'  => 'layout',
								'value'    => 'large',
								'operator' => '!=',
							],
							[
								'element'  => 'layout',
								'value'    => 'medium alternate',
								'operator' => '!=',
							],
							[
								'element'  => 'layout',
								'value'    => 'large alternate',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Grid Element Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the color of borders/date box/timeline dots and arrows for the grid boxes.', 'fusion-builder' ),
						'param_name'  => 'grid_element_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'timeline_color' ),
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'medium',
								'operator' => '!=',
							],
							[
								'element'  => 'layout',
								'value'    => 'large',
								'operator' => '!=',
							],
							[
								'element'  => 'layout',
								'value'    => 'medium alternate',
								'operator' => '!=',
							],
							[
								'element'  => 'layout',
								'value'    => 'large alternate',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Grid Separator Style', 'fusion-builder' ),
						'description' => __( 'Controls the line style of grid separators. <strong>NOTE:</strong> Separators will display, when excerpt/content or meta data below the separators is displayed.', 'fusion-builder' ),
						'param_name'  => 'grid_separator_style_type',
						'value'       => [
							''              => esc_attr__( 'Default', 'fusion-builder' ),
							'none'          => esc_attr__( 'No Style', 'fusion-builder' ),
							'single|solid'  => esc_attr__( 'Single Border Solid', 'fusion-builder' ),
							'double|solid'  => esc_attr__( 'Double Border Solid', 'fusion-builder' ),
							'single|dashed' => esc_attr__( 'Single Border Dashed', 'fusion-builder' ),
							'double|dashed' => esc_attr__( 'Double Border Dashed', 'fusion-builder' ),
							'single|dotted' => esc_attr__( 'Single Border Dotted', 'fusion-builder' ),
							'double|dotted' => esc_attr__( 'Double Border Dotted', 'fusion-builder' ),
							'shadow'        => esc_attr__( 'Shadow', 'fusion-builder' ),
						],
						'default'     => '',
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'medium',
								'operator' => '!=',
							],
							[
								'element'  => 'layout',
								'value'    => 'large',
								'operator' => '!=',
							],
							[
								'element'  => 'layout',
								'value'    => 'medium alternate',
								'operator' => '!=',
							],
							[
								'element'  => 'layout',
								'value'    => 'large alternate',
								'operator' => '!=',
							],
							[
								'element'  => 'layout',
								'value'    => 'masonry',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Grid Separator Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the line style color of grid separators.', 'fusion-builder' ),
						'param_name'  => 'grid_separator_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'grid_separator_color' ),
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'medium',
								'operator' => '!=',
							],
							[
								'element'  => 'layout',
								'value'    => 'large',
								'operator' => '!=',
							],
							[
								'element'  => 'layout',
								'value'    => 'medium alternate',
								'operator' => '!=',
							],
							[
								'element'  => 'layout',
								'value'    => 'large alternate',
								'operator' => '!=',
							],
							[
								'element'  => 'layout',
								'value'    => 'masonry',
								'operator' => '!=',
							],
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Blog Grid Text Padding ', 'fusion-builder' ),
						'description'      => esc_attr__( 'Controls the padding for the blog text when using grid / masonry or timeline layout. Enter values including any valid CSS unit, ex: 30px, 25px, 0px, 25px.', 'fusion-builder' ),
						'param_name'       => 'blog_grid_padding',
						'value'            => [
							'padding_top'    => '',
							'padding_right'  => '',
							'padding_bottom' => '',
							'padding_left'   => '',
						],
						'dependency'       => [
							[
								'element'  => 'layout',
								'value'    => 'medium',
								'operator' => '!=',
							],
							[
								'element'  => 'layout',
								'value'    => 'large',
								'operator' => '!=',
							],
							[
								'element'  => 'layout',
								'value'    => 'medium alternate',
								'operator' => '!=',
							],
							[
								'element'  => 'layout',
								'value'    => 'large alternate',
								'operator' => '!=',
							],
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Margin', 'fusion-builder' ),
						'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'margin',
						'value'            => [
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
					'function' => 'fusion_ajax',
					'action'   => 'get_fusion_blog',
					'ajax'     => true,
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_blog' );
