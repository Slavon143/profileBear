<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_recent_posts' ) ) {

	if ( ! class_exists( 'FusionSC_RecentPosts' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_RecentPosts extends Fusion_Element {

			/**
			 * Recent Posts element counter.
			 *
			 * @access private
			 * @since 1.5.2
			 * @var int
			 */
			private $recent_posts_counter = 1;

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array
			 */
			protected $args;

			/**
			 * An array of meta settings.
			 *
			 * @access private
			 * @since 1.0
			 * @var array
			 */
			private $meta_info_settings = [];

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_recentposts-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_recentposts-shortcode-section', [ $this, 'section_attr' ] );
				add_filter( 'fusion_attr_recentposts-shortcode-column', [ $this, 'column_attr' ] );
				add_filter( 'fusion_attr_recentposts-shortcode-content', [ $this, 'content_attr' ] );
				add_filter( 'fusion_attr_recentposts-shortcode-slideshow', [ $this, 'slideshow_attr' ] );
				add_filter( 'fusion_attr_recentposts-shortcode-img', [ $this, 'img_attr' ] );
				add_filter( 'fusion_attr_recentposts-shortcode-img-link', [ $this, 'link_attr' ] );

				add_shortcode( 'fusion_recent_posts', [ $this, 'render' ] );

				// Ajax mechanism for query related part.
				add_action( 'wp_ajax_get_fusion_recent_posts', [ $this, 'ajax_query' ] );
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
					'margin_top'                     => '',
					'margin_right'                   => '',
					'margin_bottom'                  => '',
					'margin_left'                    => '',
					'hide_on_mobile'                 => fusion_builder_default_visibility( 'string' ),
					'class'                          => '',
					'id'                             => '',
					'pull_by'                        => '',
					'cat_id'                         => '',
					'cat_slug'                       => '',
					'tag_slug'                       => '',
					'exclude_tags'                   => '',
					'columns'                        => 3,
					'content_alignment'              => '',
					'excerpt'                        => 'no',
					'exclude_cats'                   => '',
					'excerpt_length'                 => '',
					'excerpt_words'                  => '15', // Deprecated.
					'hover_type'                     => 'none',
					'layout'                         => 'default',
					'meta'                           => 'yes',
					'meta_author'                    => 'no',
					'meta_categories'                => 'no',
					'meta_comments'                  => 'yes',
					'meta_date'                      => 'yes',
					'meta_tags'                      => 'no',
					'number_posts'                   => '4',
					'offset'                         => '',
					'picture_size'                   => 'fixed',
					'post_status'                    => '',
					'scrolling'                      => 'no',
					'strip_html'                     => 'yes',
					'title'                          => 'yes',
					'title_size'                     => '4',
					'fusion_font_family_title_font'  => '',
					'fusion_font_variant_title_font' => '',
					'title_font_size'                => '',
					'title_letter_spacing'           => '',
					'title_line_height'              => '',
					'title_text_transform'           => '',
					'thumbnail'                      => 'yes',
					'animation_direction'            => 'left',
					'animation_speed'                => '',
					'animation_type'                 => '',
					'animation_offset'               => $fusion_settings->get( 'animation_offset' ),
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
					'animation_offset' => 'animation_offset',
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
					'pagination_range_global'           => apply_filters( 'fusion_pagination_size', $fusion_settings->get( 'pagination_range' ) ),
					'pagination_start_end_range_global' => apply_filters( 'fusion_pagination_start_end_size', $fusion_settings->get( 'pagination_start_end_range' ) ),
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
			 * @param array $defaults The defaults array.
			 * @return array|Object
			 */
			public function query( $defaults ) {
				$fusion_settings = awb_get_fusion_settings();

				$live_request = false;

				// From Ajax Request.
				if ( isset( $_POST['model'] ) && ! apply_filters( 'fusion_builder_live_request', false ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$defaults     = wp_unslash( $_POST['model']['params'] ); // phpcs:ignore WordPress.Security
					$defaults     = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $defaults, 'fusion_recent_posts' );
					$live_request = true;
					add_filter( 'fusion_builder_live_request', '__return_true' );
				}

				$defaults['offset']         = ( '0' === $defaults['offset'] ) ? '' : $defaults['offset'];
				$defaults['columns']        = min( $defaults['columns'], 6 );
				$defaults['strip_html']     = ( 'yes' === $defaults['strip_html'] || 'true' === $defaults['strip_html'] ) ? true : false;
				$defaults['posts_per_page'] = ( $defaults['number_posts'] ) ? $defaults['number_posts'] : $defaults['posts_per_page'];
				$defaults['posts_per_page'] = '0' === $defaults['posts_per_page'] ? get_option( 'posts_per_page' ) : $defaults['posts_per_page'];
				$defaults['scrolling']      = ( '-1' === $defaults['number_posts'] ) ? 'no' : $defaults['scrolling'];

				if ( $defaults['excerpt_length'] || '0' === $defaults['excerpt_length'] ) {
					$defaults['excerpt_words'] = $defaults['excerpt_length'];
				}
				if ( 'tag' !== $defaults['pull_by'] ) {
					// Check for cats to exclude; needs to be checked via exclude_cats param
					// and '-' prefixed cats on cats param, exclution via exclude_cats param.
					$cats_to_exclude = explode( ',', $defaults['exclude_cats'] );
					if ( $cats_to_exclude ) {
						foreach ( $cats_to_exclude as $cat_to_exclude ) {
							$id_obj = get_category_by_slug( $cat_to_exclude );
							if ( $id_obj ) {
								$cats_id_to_exclude[] = $id_obj->term_id;
							}
						}
						if ( isset( $cats_id_to_exclude ) && $cats_id_to_exclude ) {
							$defaults['category__not_in'] = $cats_id_to_exclude;
						}
					}

					// Setting up cats to be used and exclution using '-' prefix on cats param; transform slugs to ids.
					$cat_ids    = '';
					$categories = explode( ',', $defaults['cat_slug'] );
					if ( isset( $categories ) && $categories ) {
						foreach ( $categories as $category ) {
							if ( $category ) {
								$cat_obj = get_category_by_slug( $category );
								if ( isset( $cat_obj->term_id ) ) {
									$cat_ids .= ( 0 === strpos( $category, '-' ) ) ? '-' . $cat_obj->cat_ID . ',' : $cat_obj->cat_ID . ',';
								}
							}
						}
					}
					$defaults['cat'] = substr( $cat_ids, 0, -1 ) . $defaults['cat_id'];
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
							$defaults['tag__not_in'] = $tags_id_to_exclude;
						}
					}

					// Setting up tags to be used.
					$tag_ids = [];
					if ( '' !== $defaults['tag_slug'] ) {
						$tags = explode( ',', $defaults['tag_slug'] );
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

				$args = [
					'posts_per_page'      => $defaults['number_posts'],
					'ignore_sticky_posts' => 1,
				];

				// Check if there is paged content.
				$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
				if ( is_front_page() ) {
					$paged = ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1;
				}
				$args['paged'] = $paged;

				if ( $defaults['offset'] ) {
					$args['offset'] = $defaults['offset'] + ( $paged - 1 ) * $defaults['number_posts'];
				}

				if ( isset( $defaults['cat'] ) && $defaults['cat'] ) {
					$args['cat'] = $defaults['cat'];
				}

				if ( isset( $defaults['category__not_in'] ) && is_array( $defaults['category__not_in'] ) ) {
					$args['category__not_in'] = $defaults['category__not_in'];
				}

				if ( isset( $defaults['tag__in'] ) && $defaults['tag__in'] ) {
					$args['tag__in'] = $defaults['tag__in'];
				}

				if ( isset( $defaults['tag__not_in'] ) && is_array( $defaults['tag__not_in'] ) ) {
					$args['tag__not_in'] = $defaults['tag__not_in'];
				}

				if ( '' !== $defaults['post_status'] ) {
					$args['post_status'] = explode( ',', $defaults['post_status'] );
				} elseif ( $live_request ) {
					$args['post_status'] = 'publish';
				}
				$return_data['post_status'] = isset( $args['post_status'] ) ? $args['post_status'] : '';
				$recent_posts               = fusion_cached_query( $args );

				$this->args['max_num_pages'] = $recent_posts->max_num_pages;

				if ( ! $live_request ) {
					return $recent_posts;
				}

				// If we are here it means its a live request for builder so we put together package of data.
				if ( ! $recent_posts->have_posts() ) {
					$return_data['placeholder'] = fusion_builder_placeholder( 'post', 'blog posts' );
					echo wp_json_encode( $return_data );
					wp_die();
				}

				while ( $recent_posts->have_posts() ) {
					$recent_posts->the_post();
					$image_sizes = [ 'full', 'recent-posts', 'portfolio-five' ];

					// Get image for standard thumbnail if set.
					$thumbnail = false;
					if ( has_post_thumbnail() ) {
						$thumbnail_id = get_post_thumbnail_id();

						// Get all image sizes, not just 1.
						foreach ( $image_sizes as $image_size ) {

							// Responsive images.
							if ( 'full' === $image_size ) {
								fusion_library()->get_images_obj()->set_grid_image_meta(
									[
										'layout'       => 'grid',
										'columns'      => $defaults['columns'],
										'gutter_width' => '30',
									]
								);

								$attachment_image = wp_get_attachment_image( $thumbnail_id, $image_size );
								$attachment_image = fusion_library()->get_images_obj()->edit_grid_image_src( $attachment_image, null, $thumbnail_id, 'full' );

								fusion_library()->get_images_obj()->set_grid_image_meta( [] );

							} else {
								$attachment_image = wp_get_attachment_image( $thumbnail_id, $image_size );
							}

							$thumbnail[ $image_size ] = $attachment_image;
						}
					}

					// Get array of featured images if set.
					$multiple_featured_images = false;
					$i                        = 2;
					$posts_slideshow_number   = $fusion_settings->get( 'posts_slideshow_number' );
					if ( '' === $posts_slideshow_number ) {
						$posts_slideshow_number = 5;
					}
					while ( $i <= $posts_slideshow_number ) {

						$attachment_new_id = false;

						if ( function_exists( 'fusion_get_featured_image_id' ) && fusion_get_featured_image_id( 'featured-image-' . $i, 'post' ) ) {
							$attachment_new_id = fusion_get_featured_image_id( 'featured-image-' . $i, 'post' );
						}

						if ( $attachment_new_id ) {

							// Get all image sizes, not just 1.
							foreach ( $image_sizes as $image_size ) {

								// Responsive images.
								if ( 'full' === $image_size ) {
									fusion_library()->get_images_obj()->set_grid_image_meta(
										[
											'layout'       => 'grid',
											'columns'      => $defaults['columns'],
											'gutter_width' => '30',
										]
									);

									$attachment_image = wp_get_attachment_image( $attachment_new_id, $image_size );

									$attachment_image = fusion_library()->get_images_obj()->edit_grid_image_src( $attachment_image, null, $attachment_new_id, 'full' );

									fusion_library()->get_images_obj()->set_grid_image_meta( [] );
								} else {
									$attachment_image = wp_get_attachment_image( $attachment_new_id, $image_size );
								}

								$multiple_featured_images[ $attachment_new_id ][ $image_size ] = $attachment_image;
							}
						}

						$i++;
					}

					// Rich snippets for both title options.
					$rich_snippets = [
						'yes' => fusion_builder_render_rich_snippets_for_pages( false ),
						'no'  => fusion_builder_render_rich_snippets_for_pages(),
					];

					// Comments Link.
					ob_start();
					comments_popup_link( esc_attr__( '0 Comments', 'fusion-builder' ), esc_attr__( '1 Comment', 'fusion-builder' ), esc_attr__( '% Comments', 'fusion-builder' ) );
					$comments_link = ob_get_contents();
					ob_get_clean();

					// Contents, strip tags on and off.
					$content = fusion_get_content_data( 'fusion_recent_posts', true );

					$post_id = get_the_ID();

					$date_format = $fusion_settings->get( 'date_format' );
					$date_format = $date_format ? $date_format : get_option( 'date_format' );

					$return_data['max_num_pages'] = $recent_posts->max_num_pages;
					$return_data['posts'][]       = [
						'format'                           => get_post_format(),
						'alternate_date_format_day'        => get_the_time( $fusion_settings->get( 'alternate_date_format_day' ) ),
						'alternate_date_format_month_year' => get_the_time( $fusion_settings->get( 'alternate_date_format_month_year' ) ),
						'thumbnail'                        => $thumbnail,
						'password_required'                => post_password_required( $post_id ),
						'video'                            => apply_filters( 'fusion_builder_post_video', $post_id ),
						'multiple_featured_images'         => $multiple_featured_images,
						'title'                            => get_the_title(),
						'rich_snippet'                     => $rich_snippets,
						'permalink'                        => get_permalink( $post_id ),
						'comments_link'                    => $comments_link,
						'date_format'                      => get_the_time( $date_format, $post_id ),
						'meta_data'                        => fusion_get_meta_data( $post_id ),
						'content'                          => $content,
					];
				}
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
				$fusion_settings = awb_get_fusion_settings();

				add_filter( 'fusion_dynamic_post_id', [ $this, 'post_dynamic_data' ] );

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_recent_posts' );

				$defaults['offset']         = ( '0' === $defaults['offset'] ) ? '' : $defaults['offset'];
				$defaults['columns']        = min( $defaults['columns'], 6 );
				$defaults['strip_html']     = ( 'yes' === $defaults['strip_html'] || 'true' === $defaults['strip_html'] ) ? true : false;
				$defaults['posts_per_page'] = ( $defaults['number_posts'] ) ? $defaults['number_posts'] : $defaults['posts_per_page'];
				$defaults['scrolling']      = ( '-1' === $defaults['number_posts'] ) ? 'no' : $defaults['scrolling'];

				if ( $defaults['excerpt_length'] || '0' === $defaults['excerpt_length'] ) {
					$defaults['excerpt_words'] = $defaults['excerpt_length'];
				}

				$defaults['margin_top']    = FusionBuilder::validate_shortcode_attr_value( $defaults['margin_top'], 'px' );
				$defaults['margin_right']  = FusionBuilder::validate_shortcode_attr_value( $defaults['margin_right'], 'px' );
				$defaults['margin_bottom'] = FusionBuilder::validate_shortcode_attr_value( $defaults['margin_bottom'], 'px' );
				$defaults['margin_left']   = FusionBuilder::validate_shortcode_attr_value( $defaults['margin_left'], 'px' );

				extract( $defaults );

				// Deprecated 5.2.1 hide value, mapped to no.
				if ( 'hide' === $excerpt ) {
					$excerpt = 'no';
				}

				$defaults['meta_author']     = ( 'yes' === $defaults['meta_author'] );
				$defaults['meta_categories'] = ( 'yes' === $defaults['meta_categories'] );
				$defaults['meta_comments']   = ( 'yes' === $defaults['meta_comments'] );
				$defaults['meta_date']       = ( 'yes' === $defaults['meta_date'] );
				$defaults['meta_tags']       = ( 'yes' === $defaults['meta_tags'] );

				// Set the meta info settings for later use.
				$this->meta_info_settings['post_meta']          = $defaults['meta'];
				$this->meta_info_settings['post_meta_author']   = $defaults['meta_author'];
				$this->meta_info_settings['post_meta_date']     = $defaults['meta_date'];
				$this->meta_info_settings['post_meta_cats']     = $defaults['meta_categories'];
				$this->meta_info_settings['post_meta_tags']     = $defaults['meta_tags'];
				$this->meta_info_settings['post_meta_comments'] = $defaults['meta_comments'];

				$this->args   = $defaults;
				$recent_posts = $this->query( $defaults );
				$items        = '';

				if ( ! $recent_posts->have_posts() ) {
					return fusion_builder_placeholder( 'post', 'blog posts' );
				}

				while ( $recent_posts->have_posts() ) {
					$recent_posts->the_post();

					$attachment = $date_box = $slideshow = $slides = $content = '';

					$permalink = get_permalink( get_the_ID() );
					if ( 'private' === get_post_status() && ! is_user_logged_in() || in_array( get_post_status(), [ 'pending', 'draft', 'future' ], true ) && ! current_user_can( 'edit-post' ) ) {
						$permalink = '#';
					}

					if ( 'date-on-side' === $layout ) {
						$post_format = get_post_format();

						switch ( $post_format ) {
							case 'gallery':
								$format_class = 'images';
								break;
							case 'link':
							case 'image':
								$format_class = $post_format;
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

						$date_box = '<div ' . FusionBuilder::attributes( 'fusion-date-and-formats' ) . '><div ' . FusionBuilder::attributes( 'fusion-date-box updated' ) . '><span ' . FusionBuilder::attributes( 'fusion-date' ) . '>' . get_the_time( $fusion_settings->get( 'alternate_date_format_day' ) ) . '</span><span ' . FusionBuilder::attributes( 'fusion-month-year' ) . '>' . get_the_time( $fusion_settings->get( 'alternate_date_format_month_year' ) ) . '</span></div><div ' . FusionBuilder::attributes( 'fusion-format-box' ) . '><i ' . FusionBuilder::attributes( 'awb-icon-' . $format_class ) . ' aria-hidden="true"></i></div></div>';
					}

					if ( 'yes' === $thumbnail && 'date-on-side' !== $layout && ! post_password_required( get_the_ID() ) ) {

						if ( 'auto' === $picture_size ) {
							$image_size = 'full';

							// If set to auto image size and thumbnail on side slideshow is set to 144px of width.
							if ( 'thumbnails-on-side' === $layout ) {
								$image_size = 'fusion-200';
							}
						} elseif ( 'default' === $layout ) {
							$image_size = 'recent-posts';
						} elseif ( 'thumbnails-on-side' === $layout ) {
							$image_size = 'portfolio-five';
						}

						$post_video = apply_filters( 'fusion_builder_post_video', get_the_ID() );

						if ( has_post_thumbnail() || $post_video ) {
							if ( $post_video ) {
								$slides .= '<li><div ' . FusionBuilder::attributes( 'full-video' ) . '>' . $post_video . '</div></li>';
							}

							if ( has_post_thumbnail() ) {
								$thumbnail_id = get_post_thumbnail_id();

								// Responsive images.
								if ( 'full' === $image_size ) {
									fusion_library()->get_images_obj()->set_grid_image_meta(
										[
											'layout'       => 'grid',
											'columns'      => $columns,
											'gutter_width' => '30',
										]
									);

									$attachment_image = wp_get_attachment_image( $thumbnail_id, $image_size );
									$attachment_image = fusion_library()->get_images_obj()->edit_grid_image_src( $attachment_image, null, $thumbnail_id, 'full' );

									fusion_library()->get_images_obj()->set_grid_image_meta( [] );
								} else {
									$attachment_image = wp_get_attachment_image( $thumbnail_id, $image_size );
								}

								$slides .= '<li><a href="' . esc_url( $permalink ) . '" ' . FusionBuilder::attributes( 'recentposts-shortcode-img-link' ) . '>' . $attachment_image . '</a></li>';
							}

							$i                      = 2;
							$posts_slideshow_number = $fusion_settings->get( 'posts_slideshow_number' );
							if ( '' === $posts_slideshow_number ) {
								$posts_slideshow_number = 5;
							}
							while ( $i <= $posts_slideshow_number ) {

								$attachment_new_id = false;

								if ( function_exists( 'fusion_get_featured_image_id' ) && fusion_get_featured_image_id( 'featured-image-' . $i, 'post' ) ) {
									$attachment_new_id = fusion_get_featured_image_id( 'featured-image-' . $i, 'post' );
								}

								if ( $attachment_new_id ) {

									// Responsive images.
									if ( 'full' === $image_size ) {
										fusion_library()->get_images_obj()->set_grid_image_meta(
											[
												'layout'  => 'grid',
												'columns' => $columns,
												'gutter_width' => '30',
											]
										);

										$attachment_image = wp_get_attachment_image( $attachment_new_id, $image_size );
										$attachment_image = fusion_library()->get_images_obj()->edit_grid_image_src( $attachment_image, null, $attachment_new_id, 'full' );

										fusion_library()->get_images_obj()->set_grid_image_meta( [] );
									} else {
										$attachment_image = wp_get_attachment_image( $attachment_new_id, $image_size );
									}

									$slides .= '<li><a href="' . esc_url( $permalink ) . '" ' . FusionBuilder::attributes( 'recentposts-shortcode-img-link' ) . '>' . $attachment_image . '</a></li>';
								}
								$i++;
							}

							$slideshow = '<div ' . FusionBuilder::attributes( 'recentposts-shortcode-slideshow' ) . '><ul ' . FusionBuilder::attributes( 'slides' ) . '>' . $slides . '</ul></div>';
						}
					}

					if ( 'yes' === $title ) {
						$content    .= ( function_exists( 'fusion_builder_render_rich_snippets_for_pages' ) ) ? fusion_builder_render_rich_snippets_for_pages( false ) : '';
						$entry_title = '';
						if ( $fusion_settings->get( 'disable_date_rich_snippet_pages' ) && $fusion_settings->get( 'disable_rich_snippet_title' ) ) {
							$entry_title = 'entry-title';
						}
						$title_tag = $this->get_title_tag();
						$content  .= '<' . $title_tag . ' class="' . $entry_title . '"><a href="' . esc_url( $permalink ) . '">' . get_the_title() . '</a></' . $title_tag . '>';
					} else {
						$content .= fusion_builder_render_rich_snippets_for_pages();
					}

					if ( 'yes' === $meta ) {
						$meta_data = fusion_builder_render_post_metadata( 'recent_posts', $this->meta_info_settings );
						$content  .= '<p ' . FusionBuilder::attributes( 'meta' ) . '>' . $meta_data . '</p>';
					}

					if ( 'yes' === $excerpt ) {
						$content .= fusion_builder_get_post_content( '', 'yes', $excerpt_words, $strip_html );
					} elseif ( 'full' === $excerpt ) {
						$content .= fusion_builder_get_post_content( '', 'no', $excerpt_words, $strip_html );
					}

					$items .= '<article ' . FusionBuilder::attributes( 'recentposts-shortcode-column' ) . '>' . $date_box . $slideshow . '<div ' . FusionBuilder::attributes( 'recentposts-shortcode-content' ) . '>' . $content . '</div></article>';
				}

				// Pagination is used.
				$pagination = '';
				if ( 'no' !== $this->args['scrolling'] ) {
					$infinite_pagination = false;
					if ( 'pagination' !== $this->args['scrolling'] ) {
						$infinite_pagination = true;
					}

					$pagination = fusion_pagination( $recent_posts->max_num_pages, $fusion_settings->get( 'pagination_range' ), $recent_posts, $infinite_pagination, true );

					// If infinite scroll with "load more" button is used.
					if ( 'load_more_button' === $this->args['scrolling'] && 1 < $recent_posts->max_num_pages ) {
						$pagination .= '<button class="fusion-load-more-button fusion-blog-button fusion-clearfix">' . apply_filters( 'avada_load_more_posts_name', esc_attr__( 'Load More Posts', 'fusion-builder' ) ) . '</button>';
					}
				}

				$style = $this->get_style_element();
				$html  = '<div ' . FusionBuilder::attributes( 'recentposts-shortcode' ) . '>' . $style . '<section ' . FusionBuilder::attributes( 'recentposts-shortcode-section' ) . '>' . $items . '</section>' . $pagination . '</div>';

				wp_reset_postdata();
				remove_filter( 'fusion_dynamic_post_id', [ $this, 'post_dynamic_data' ] );
				$this->recent_posts_counter++;

				$this->on_render();

				return apply_filters( 'fusion_element_recent_posts_content', $html, $args );
			}

			/**
			 * Get the style html tag.
			 *
			 * @since 3.8
			 * @return string
			 */
			public function get_style_element() {
				$style             = '';
				$base_class        = '.fusion-recent-posts.fusion-recent-posts-' . $this->recent_posts_counter;
				$this->dynamic_css = [];

				$title_selector = $base_class . ' .columns .column .entry-title';

				$title_typography = Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'title_font', 'array' );

				if ( isset( $title_typography['font-family'] ) && $title_typography['font-family'] ) {
					$this->add_css_property( $title_selector, 'font-family', $title_typography['font-family'] );
				}

				if ( isset( $title_typography['font-weight'] ) && $title_typography['font-weight'] ) {
					$this->add_css_property( $title_selector, 'font-weight', $title_typography['font-weight'] );
				}

				if ( isset( $title_typography['font-style'] ) && $title_typography['font-style'] ) {
					$this->add_css_property( $title_selector, 'font-style', $title_typography['font-style'] );
				}

				if ( $this->args['title_font_size'] ) {
					$this->add_css_property( $title_selector, 'font-size', $this->args['title_font_size'] );
				}

				if ( $this->args['title_line_height'] ) {
					$this->add_css_property( $title_selector, 'line-height', $this->args['title_line_height'] );
				}

				if ( $this->args['title_letter_spacing'] ) {
					$this->add_css_property( $title_selector, 'letter-spacing', $this->args['title_letter_spacing'] );
				}

				if ( $this->args['title_text_transform'] ) {
					$this->add_css_property( $title_selector, 'text-transform', $this->args['title_text_transform'] );
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

				$attr = fusion_builder_visibility_atts(
					$this->args['hide_on_mobile'],
					[
						'class' => 'fusion-recent-posts fusion-recent-posts-' . $this->recent_posts_counter . ' avada-container layout-' . $this->args['layout'] . ' layout-columns-' . $this->args['columns'],
						'style' => '',
					]
				);

				if ( $this->args['content_alignment'] && 'default' === $this->args['layout'] ) {
					$attr['class'] .= ' fusion-recent-posts-' . $this->args['content_alignment'];
				}

				if ( 'infinite' === $this->args['scrolling'] || 'load_more_button' === $this->args['scrolling'] ) {
					$attr['class']     .= ' fusion-recent-posts-infinite';
					$attr['data-pages'] = $this->args['max_num_pages'];
				}

				if ( 'load_more_button' === $this->args['scrolling'] ) {
					$attr['class'] .= ' fusion-recent-posts-load-more';
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
			 * Builds the section attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function section_attr() {
				return [
					'class' => 'fusion-columns columns fusion-columns-' . $this->args['columns'] . ' columns-' . $this->args['columns'],
				];
			}

			/**
			 * Builds the column attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function column_attr() {

				$columns = 3;
				if ( $this->args['columns'] ) {
					$columns = 12 / $this->args['columns'];
				}

				$attr = [
					'class' => 'post fusion-column column col col-lg-' . $columns . ' col-md-' . $columns . ' col-sm-' . $columns . '',
					'style' => '',
				];

				if ( '5' === $this->args['columns'] || 5 === $this->args['columns'] ) {
					$attr['class'] = 'post fusion-column column col-lg-2 col-md-2 col-sm-2';
				}

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				return $attr;
			}

			/**
			 * Builds the slideshow attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function slideshow_attr() {

				$attr = [
					'class' => 'fusion-flexslider fusion-flexslider-loading flexslider',
				];

				if ( 'thumbnails-on-side' === $this->args['layout'] ) {
					$attr['class'] .= ' floated-slideshow';
				}

				if ( $this->args['hover_type'] ) {
					$attr['class'] .= ' flexslider-hover-type-' . $this->args['hover_type'];
				}

				return $attr;
			}

			/**
			 * Builds the image attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @param array $args The arguments array.
			 * @return array
			 */
			public function img_attr( $args ) {

				$attr = [
					'src' => $args['src'],
				];

				if ( $args['alt'] ) {
					$attr['alt'] = $args['alt'];
				}

				return $attr;
			}

			/**
			 * Builds the link attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @param array $args The arguments array.
			 * @return array
			 */
			public function link_attr( $args ) {

				$attr = [
					'aria-label' => the_title_attribute( [ 'echo' => false ] ),
				];

				if ( $this->args['hover_type'] ) {
					$attr['class'] = 'hover-type-' . $this->args['hover_type'];
				}

				return $attr;
			}

			/**
			 * Builds the content wrapper attributes array.
			 *
			 * @access public
			 * @since 1.5.2
			 * @return array
			 */
			public function content_attr() {
				return [
					'class' => 'recent-posts-content',
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
					'fusion-recent-posts',
					FusionBuilder::$js_folder_url . '/general/fusion-recent-posts.js',
					FusionBuilder::$js_folder_path . '/general/fusion-recent-posts.js',
					[ 'jquery', 'fusion-blog' ],
					'1',
					true
				);

				Fusion_Dynamic_JS::localize_script(
					'fusion-recent-posts',
					'fusionRecentPostsVars',
					[
						'infinite_loading_text' => '<em>' . __( 'Loading the next set of posts...', 'fusion-builder' ) . '</em>',
						'infinite_finished_msg' => '<em>' . __( 'All items displayed.', 'fusion-builder' ) . '</em>',
					]
				);
			}

			/**
			 * Get the tag of the title.
			 *
			 * @return string
			 */
			public function get_title_tag() {
				$tag_option = $this->args['title_size'];
				if ( ! $tag_option ) {
					return 'h4';
				}

				if ( is_numeric( $tag_option ) ) {
					return 'h' . $tag_option;
				}

				return $tag_option;
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/recent-posts.min.css' );
			}
		}
	}

	new FusionSC_RecentPosts();
}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_recent_posts() {

	$builder_status = function_exists( 'is_fusion_editor' ) && is_fusion_editor();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_RecentPosts',
			[
				'name'       => esc_attr__( 'Recent Posts', 'fusion-builder' ),
				'shortcode'  => 'fusion_recent_posts',
				'icon'       => 'fusiona-feather',
				'preview'    => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-recent-posts-preview.php',
				'preview_id' => 'fusion-builder-block-module-recent-posts-preview-template',
				'help_url'   => 'https://theme-fusion.com/documentation/avada/elements/recent-posts-element/',
				'params'     => [
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Layout', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the layout for the element.', 'fusion-builder' ),
						'param_name'  => 'layout',
						'value'       => [
							'default'            => esc_attr__( 'Standard', 'fusion-builder' ),
							'thumbnails-on-side' => esc_attr__( 'Thumbnails on Side', 'fusion-builder' ),
							'date-on-side'       => esc_attr__( 'Date on Side', 'fusion-builder' ),
						],
						'default'     => 'default',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Picture Size', 'fusion-builder' ),
						'description' => __( 'Fixed = width and height will be fixed.<br/>Auto = width and height will adjust to the image.<br/>', 'fusion-builder' ),
						'param_name'  => 'picture_size',
						'default'     => 'fixed',
						'value'       => [
							'fixed' => esc_attr__( 'Fixed', 'fusion-builder' ),
							'auto'  => esc_attr__( 'Auto', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'date-on-side',
								'operator' => '!=',
							],
							[
								'element'  => 'thumbnail',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Hover Type', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the hover effect type.', 'fusion-builder' ),
						'param_name'  => 'hover_type',
						'value'       => [
							'none'    => esc_attr__( 'None', 'fusion-builder' ),
							'zoomin'  => esc_attr__( 'Zoom In', 'fusion-builder' ),
							'zoomout' => esc_attr__( 'Zoom Out', 'fusion-builder' ),
							'liftup'  => esc_attr__( 'Lift Up', 'fusion-builder' ),
						],
						'default'     => 'none',
						'preview'     => [
							'selector' => '.fusion-flexslider>.slides>li>a',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'date-on-side',
								'operator' => '!=',
							],
							[
								'element'  => 'thumbnail',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Number of Columns', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the number of columns to display.', 'fusion-builder' ),
						'param_name'  => 'columns',
						'value'       => '3',
						'min'         => '1',
						'max'         => '6',
						'step'        => '1',
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
							'action'   => 'get_fusion_recent_posts',
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
							'action'   => 'get_fusion_recent_posts',
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
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_recent_posts',
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
							'action'   => 'get_fusion_recent_posts',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'multiple_select',
						'heading'     => esc_attr__( 'Categories', 'fusion-builder' ),
						'placeholder' => esc_attr__( 'Categories', 'fusion-builder' ),
						'description' => esc_attr__( 'Select a category or leave blank for all.', 'fusion-builder' ),
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
							'action'   => 'get_fusion_recent_posts',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'multiple_select',
						'heading'     => esc_attr__( 'Exclude Categories', 'fusion-builder' ),
						'placeholder' => esc_attr__( 'Categories', 'fusion-builder' ),
						'description' => esc_attr__( 'Select a category to exclude.', 'fusion-builder' ),
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
							'action'   => 'get_fusion_recent_posts',
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
							'action'   => 'get_fusion_recent_posts',
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
							'action'   => 'get_fusion_recent_posts',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Thumbnail', 'fusion-builder' ),
						'description' => esc_attr__( 'Display the post featured image.', 'fusion-builder' ),
						'param_name'  => 'thumbnail',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'yes',
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'date-on-side',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Title', 'fusion-builder' ),
						'description' => esc_attr__( 'Display the post title below the featured image.', 'fusion-builder' ),
						'param_name'  => 'title',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'yes',
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
						'default'     => '4',
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
						'heading'          => esc_attr__( 'Title Typography', 'fusion-builder' ),
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
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Meta', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to show all meta data.', 'fusion-builder' ),
						'param_name'  => 'meta',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'yes',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Author Name', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to show the author.', 'fusion-builder' ),
						'param_name'  => 'meta_author',
						'default'     => 'no',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'meta',
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
						'default'     => 'no',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'meta',
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
								'element'  => 'meta',
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
								'element'  => 'meta',
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
						'default'     => 'no',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'meta',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Content Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the alignment of contents.', 'fusion-builder' ),
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
								'value'    => 'default',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Text display', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to display the post excerpt.', 'fusion-builder' ),
						'param_name'  => 'excerpt',
						'value'       => [
							'yes'  => esc_attr__( 'Excerpt', 'fusion-builder' ),
							'full' => esc_attr__( 'Full Content', 'fusion-builder' ),
							'no'   => esc_attr__( 'None', 'fusion-builder' ),
						],
						'default'     => 'yes',
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Excerpt Length', 'fusion-builder' ),
						'description' => esc_attr__( 'Insert the number of words/characters you want to show in the excerpt.', 'fusion-builder' ),
						'param_name'  => 'excerpt_length',
						'value'       => '35',
						'min'         => '0',
						'max'         => '500',
						'step'        => '1',
						'dependency'  => [
							[
								'element'  => 'excerpt',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Strip HTML', 'fusion-builder' ),
						'description' => esc_attr__( 'Strip HTML from the post excerpt.', 'fusion-builder' ),
						'param_name'  => 'strip_html',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'yes',
						'dependency'  => [
							[
								'element'  => 'excerpt',
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
						'default'     => 'no',
						'value'       => [
							'no'               => esc_attr__( 'No Pagination', 'fusion-builder' ),
							'pagination'       => esc_attr__( 'Pagination', 'fusion-builder' ),
							'infinite'         => esc_attr__( 'Infinite Scrolling', 'fusion-builder' ),
							'load_more_button' => esc_attr__( 'Load More Button', 'fusion-builder' ),
						],
					],
					'fusion_animation_placeholder' => [
						'preview_selector' => '.fusion-column',
					],
					'fusion_margin_placeholder'    => [
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
				'callback'   => [
					'function' => 'fusion_ajax',
					'action'   => 'get_fusion_recent_posts',
					'ajax'     => true,
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_recent_posts' );
