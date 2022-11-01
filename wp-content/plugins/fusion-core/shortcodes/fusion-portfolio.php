<?php
/**
 * Avada-Builder Shortcode Element.
 *
 * @package Avada-Core
 * @since 3.1.0
 */

if ( function_exists( 'fusion_is_element_enabled' ) && fusion_is_element_enabled( 'fusion_portfolio' ) ) {

	if ( ! class_exists( 'FusionSC_Portfolio' ) && class_exists( 'Fusion_Element' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @package fusion-core
		 * @since 1.0
		 */
		class FusionSC_Portfolio extends Fusion_Element {

			/**
			 * The column number (one/two/three etc).
			 *
			 * @access private
			 * @since 1.0
			 * @var string
			 */
			private $column;

			/**
			 * The image size (eg: full, thumbnail etc).
			 *
			 * @access private
			 * @since 1.0
			 * @var string
			 */
			private $image_size;

			/**
			 * The portfolio counter.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $portfolio_counter = 1;


			/**
			 * The portfolio post ID.
			 *
			 * @access private
			 * @since 3.4.2
			 * @var string
			 */
			private $post_id;

			/**
			 * An array of the shortcode arguments.
			 *
			 * @static
			 * @access public
			 * @since 1.0
			 * @var array
			 */
			public static $args;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_action( 'fusion_portfolio_shortcode_content', [ $this, 'get_post_content' ] );

				// Element attributes.
				add_filter( 'fusion_attr_portfolio-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_portfolio-shortcode-portfolio-wrapper', [ $this, 'portfolio_wrapper_attr' ] );
				add_filter( 'fusion_attr_portfolio-shortcode-portfolio-content', [ $this, 'portfolio_content_attr' ] );
				add_filter( 'fusion_attr_portfolio-shortcode-carousel', [ $this, 'carousel_attr' ] );
				add_filter( 'fusion_attr_portfolio-shortcode-slideshow', [ $this, 'slideshow_attr' ] );
				add_filter( 'fusion_attr_portfolio-shortcode-filter-link', [ $this, 'filter_link_attr' ] );
				add_filter( 'fusion_attr_portfolio-fusion-portfolio-content-wrapper', [ $this, 'portfolio_content_wrapper_attr' ] );
				add_filter( 'fusion_attr_portfolio-fusion-content-sep', [ $this, 'content_sep_attr' ] );

				add_shortcode( 'fusion_portfolio', [ $this, 'render' ] );

				// Ajax mechanism for query related part.
				add_action( 'wp_ajax_get_fusion_portfolio', [ $this, 'ajax_query' ] );
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

				return apply_filters(
					'fusion_portfolio_default_parameter',
					[
						'animation_direction'            => 'left',
						'animation_offset'               => $fusion_settings->get( 'animation_offset' ),
						'animation_speed'                => '',
						'animation_type'                 => '',
						'autoplay'                       => 'no',
						'carousel_layout'                => 'title_on_rollover',
						'cat_slug'                       => '',
						'class'                          => '',
						'column_spacing'                 => $fusion_settings->get( 'portfolio_column_spacing' ),
						'columns'                        => $fusion_settings->get( 'portfolio_columns' ),
						'content_length'                 => 'excerpt',
						'grid_box_color'                 => $fusion_settings->get( 'timeline_bg_color' ),
						'grid_element_color'             => $fusion_settings->get( 'timeline_color' ),
						'grid_separator_color'           => $fusion_settings->get( 'grid_separator_color' ),
						'grid_separator_style_type'      => $fusion_settings->get( 'grid_separator_style_type' ),
						'equal_heights'                  => 'no',
						'excerpt_length'                 => $fusion_settings->get( 'portfolio_excerpt_length' ),
						'excerpt_words'                  => '',  // Deprecated.
						'exclude_cats'                   => '',
						'exclude_tags'                   => '',
						'filters'                        => 'yes',
						'margin_top'                     => '',
						'margin_right'                   => '',
						'margin_bottom'                  => '',
						'margin_left'                    => '',
						'hide_on_mobile'                 => fusion_builder_default_visibility( 'string' ),
						'hide_url_params'                => 'off',
						'id'                             => '',
						'layout'                         => 'carousel',
						'mouse_scroll'                   => 'no',
						'number_posts'                   => $fusion_settings->get( 'portfolio_items' ),
						'offset'                         => '',
						'one_column_text_position'       => 'below',
						'order'                          => 'DESC',
						'orderby'                        => 'date',
						'pagination_type'                => 'none',
						'picture_size'                   => $fusion_settings->get( 'portfolio_featured_image_size' ),
						'portfolio_layout_padding'       => '',
						'portfolio_text_alignment'       => 'left',
						'portfolio_title_display'        => 'all',
						'pull_by'                        => '',
						'scroll_items'                   => '',
						'show_nav'                       => 'yes',
						'strip_html'                     => 'yes',
						'tag_slug'                       => '',
						'text_layout'                    => 'unboxed',
						'portfolio_masonry_grid_ratio'   => $fusion_settings->get( 'masonry_grid_ratio' ),
						'portfolio_masonry_width_double' => $fusion_settings->get( 'masonry_width_double' ),
						'boxed_text'                     => '', // Deprecated.
					]
				);
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
					'animation_offset'              => 'animation_offset',
					'timeline_bg_color'             => 'grid_box_color',
					'timeline_color'                => 'grid_element_color',
					'portfolio_column_spacing'      => 'column_spacing',
					'portfolio_columns'             => 'columns',
					'grid_separator_color'          => 'grid_separator_color',
					'grid_separator_style_type'     => 'grid_separator_style_type',
					'portfolio_excerpt_length'      => 'excerpt_length',
					'portfolio_items'               => 'number_posts',
					'portfolio_featured_image_size' => 'picture_size',
					'masonry_grid_ratio'            => 'portfolio_masonry_grid_ratio',
					'masonry_width_double'          => 'portfolio_masonry_width_double',
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
					'portfolio_content_length'      => $fusion_settings->get( 'portfolio_content_length', false, 'excerpt' ),
					'portfolio_title_display'       => $fusion_settings->get( 'portfolio_title_display', false, 'all' ),
					'portfolio_text_alignment'      => $fusion_settings->get( 'portfolio_text_alignment', false, 'left' ),
					'portfolio_text_layout'         => $fusion_settings->get( 'portfolio_text_layout', false, 'unboxed' ),
					'portfolio_featured_image_size' => $fusion_settings->get( 'portfolio_featured_image_size' ),
					'grid_pagination_type'          => trim( str_replace( [ ' ', '_', 'scroll' ], [ '-', '-', '' ], strtolower( $fusion_settings->get( 'portfolio_pagination_type', false, 'none' ) ) ), '-' ),
					'portfolio_strip_html_excerpt'  => $fusion_settings->get( 'portfolio_strip_html_excerpt', false, 'yes' ),
					'button_type'                   => strtolower( $fusion_settings->get( 'button_type' ) ),
					'learn_more'                    => esc_attr__( 'Learn More', 'fusion-core' ),
					'view_project'                  => esc_attr__( 'View Project', 'fusion-core' ),
					'all_text'                      => esc_attr__( 'All', 'fusion-core' ),
					'load_more_posts'               => apply_filters( 'avada_load_more_posts_name', esc_attr__( 'Load More Posts', 'fusion-core' ) ),
					'image_rollover'                => $fusion_settings->get( 'image_rollover' ),
					'timeline_bg_color'             => $fusion_settings->get( 'timeline_bg_color' ),
					'portfolio_layout_padding'      => $fusion_settings->get( 'portfolio_layout_padding' ),
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
					'portfolio_pagination_type'     => [
						'param'    => 'grid_pagination_type',
						'callback' => 'portfolioPaginationFormat',
					],
					'portfolio_content_length'      => 'portfolio_content_length',
					'portfolio_title_display'       => 'portfolio_title_display',
					'portfolio_text_alignment'      => 'portfolio_text_alignment',
					'portfolio_text_layout'         => 'portfolio_text_layout',
					'portfolio_featured_image_size' => 'portfolio_featured_image_size',
					'portfolio_strip_html_excerpt'  => 'portfolio_strip_html_excerpt',
					'button_type'                   => 'button_type',
					'image_rollover'                => 'image_rollover',
					'timeline_bg_color'             => 'timeline_bg_color',
					'portfolio_layout_padding'      => 'portfolio_layout_padding',
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
			 * @return array
			 */
			public function query() {
				global $fusion_library;
				$fusion_settings = awb_get_fusion_settings();
				$live_request    = false;

				// From Ajax Request. @codingStandardsIgnoreLine
				if ( isset( $_POST['model'] ) && ! apply_filters( 'fusion_builder_live_request', false ) ) {

					// Ignore WordPress.CSRF.NonceVerification.NoNonceVerification.
					// No nonce verification is needed here.
					// @codingStandardsIgnoreLine
					self::$args = $_POST['model']['params'];
					$return_data  = [];
					$live_request = true;
					add_filter( 'fusion_builder_live_request', '__return_true' );
				}

				// Transform $cat_slugs to array.
				$cat_slugs = [];
				if ( 'tag' !== self::$args['pull_by'] ) {
					if ( self::$args['cat_slug'] ) {
						$cat_slugs = preg_replace( '/\s+/', '', self::$args['cat_slug'] );
						$cat_slugs = explode( ',', self::$args['cat_slug'] );
					}
				}

				// Filter terms, e.g. useful for WPML.
				self::$args['cat_slugs'] = apply_filters( 'avada_element_term_selection', $cat_slugs, 'avada_portfolio', 'portfolio_category' );

				// Transform $tag_slugs to array.
				$tag_slugs = [];
				if ( 'category' !== self::$args['pull_by'] ) {
					if ( self::$args['tag_slug'] ) {
						$tag_slugs = preg_replace( '/\s+/', '', self::$args['tag_slug'] );
						$tag_slugs = explode( ',', self::$args['tag_slug'] );
					}
				}

				// Filter terms, e.g. useful for WPML.
				self::$args['tag_slugs'] = apply_filters( 'avada_element_term_selection', $tag_slugs, 'avada_portfolio', 'portfolio_tags' );

				// Transform $cats_to_exclude to array.
				$cats_to_exclude = [];
				if ( 'tag' !== self::$args['pull_by'] ) {
					if ( self::$args['exclude_cats'] ) {
						$cats_to_exclude = preg_replace( '/\s+/', '', self::$args['exclude_cats'] );
						$cats_to_exclude = explode( ',', self::$args['exclude_cats'] );
					}
				}

				// Make sure WPML cats match even when using translation management plugin.
				if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
					foreach ( $cats_to_exclude as $cat_slug ) {
						$cat_id            = get_term_by( 'slug', $cat_slug, 'portfolio_category' );
						$translated_id     = apply_filters( 'wpml_object_id', $cat_id->term_id, 'avada_portfolio', true );
						$translated_slug   = get_term_by( 'id', $translated_id, 'portfolio_category' )->slug;
						$cats_to_exclude[] = $translated_slug;
					}

					$cats_to_exclude = array_unique( $cats_to_exclude );
				}

				// Filter terms, e.g. useful for WPML.
				self::$args['cats_to_exclude'] = apply_filters( 'avada_element_term_selection', $cats_to_exclude, 'avada_portfolio', 'portfolio_category' );

				// Transform exclude_tags to array.
				$tags_to_exclude = [];
				if ( 'category' !== self::$args['pull_by'] ) {
					if ( self::$args['exclude_tags'] ) {
						$tags_to_exclude = preg_replace( '/\s+/', '', self::$args['exclude_tags'] );
						$tags_to_exclude = explode( ',', self::$args['exclude_tags'] );
					}
				}

				// Filter terms, e.g. useful for WPML.
				self::$args['tags_to_exclude'] = apply_filters( 'avada_element_term_selection', $tags_to_exclude, 'avada_portfolio', 'portfolio_tags' );

				// Check if there is paged content.
				$paged = 1;
				if ( 'none' !== self::$args['pagination_type'] ) {
					$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
					if ( is_front_page() ) {
						$paged = ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1;
					}
				}

				// Initialize the query array.
				$args = [
					'post_type'      => 'avada_portfolio',
					'paged'          => $paged,
					'posts_per_page' => '' !== self::$args['number_posts'] ? self::$args['number_posts'] : $fusion_settings->get( 'portfolio_items' ),
					'has_password'   => false,
					'orderby'        => self::$args['orderby'],
					'order'          => self::$args['order'],
				];

				$args['posts_per_page'] = '0' === $args['posts_per_page'] ? get_option( 'posts_per_page' ) : $args['posts_per_page'];

				if ( self::$args['offset'] ) {
					$args['offset']       = self::$args['offset'];
					self::$args['offset'] = self::$args['offset'] + ( $paged - 1 ) * $args['posts_per_page'];
				}

				// Check if there are categories that should be excluded.
				if ( ! empty( self::$args['cats_to_exclude'] ) ) {

					// Exclude the correct cats from tax_query.
					$args['tax_query'] = [ // phpcs:ignore WordPress.DB.SlowDBQuery
						[
							'taxonomy' => 'portfolio_category',
							'field'    => 'slug',
							'terms'    => self::$args['cats_to_exclude'],
							'operator' => 'NOT IN',
						],
					];

					// Include the correct cats in tax_query.
					if ( ! empty( self::$args['cat_slugs'] ) ) {
						$args['tax_query']['relation'] = 'AND';
						$args['tax_query'][]           = [
							'taxonomy' => 'portfolio_category',
							'field'    => 'slug',
							'terms'    => self::$args['cat_slugs'],
							'operator' => 'IN',
						];
					}
				} else {
					// Include the cats from $cat_slugs in tax_query.
					if ( ! empty( self::$args['cat_slugs'] ) ) {
						$args['tax_query'] = [ // phpcs:ignore WordPress.DB.SlowDBQuery
							[
								'taxonomy' => 'portfolio_category',
								'field'    => 'slug',
								'terms'    => self::$args['cat_slugs'],
							],
						];
					}
				}

				// Check if there are tags that should be excluded.
				if ( ! empty( self::$args['tags_to_exclude'] ) ) {

					// Exclude the correct cats from tax_query.
					$args['tax_query'] = [ // phpcs:ignore WordPress.DB.SlowDBQuery
						[
							'taxonomy' => 'portfolio_tags',
							'field'    => 'slug',
							'terms'    => self::$args['tags_to_exclude'],
							'operator' => 'NOT IN',
						],
					];

					// Include the correct cats in tax_query.
					if ( ! empty( self::$args['tag_slugs'] ) ) {
						$args['tax_query']['relation'] = 'AND';
						$args['tax_query'][]           = [
							'taxonomy' => 'portfolio_tags',
							'field'    => 'slug',
							'terms'    => self::$args['tag_slugs'],
							'operator' => 'IN',
						];
					}
				} else {
					// Include the tags from $cat_slugs in tax_query.
					if ( ! empty( self::$args['tag_slugs'] ) ) {
						$args['tax_query'] = [ // phpcs:ignore WordPress.DB.SlowDBQuery
							[
								'taxonomy' => 'portfolio_tags',
								'field'    => 'slug',
								'terms'    => self::$args['tag_slugs'],
							],
						];
					}
				}

				// Ajax returns protected posts, but we just want published.
				if ( $live_request ) {
					$args['post_status'] = 'publish';
				}

				$args['portfolio_sc_query'] = true;

				$portfolio_query = FusionCore_Plugin::fusion_core_cached_query( apply_filters( 'fusion_portfolio_query_args', $args ) );

				if ( ! $live_request ) {
					return $portfolio_query;
				}

				if ( ! $portfolio_query->have_posts() ) {
					$return_data['placeholder'] = fusion_builder_placeholder( 'avada_portfolio', 'portfolio posts' );
					echo wp_json_encode( $return_data );
					die();
				}

				$portfolio_categories                = get_terms( 'portfolio_category' );
				$return_data['portfolio_categories'] = $portfolio_categories;

				$return_data['number_of_pages'] = $portfolio_query->max_num_pages;

				$return_data['pagination']['true']  = fusion_pagination( $portfolio_query->max_num_pages, 2, $portfolio_query, true );
				$return_data['pagination']['false'] = fusion_pagination( $portfolio_query->max_num_pages, 2, $portfolio_query, false );

				if ( $portfolio_query->have_posts() ) {
					while ( $portfolio_query->have_posts() ) {
						$portfolio_query->the_post();

						$id                    = get_the_ID();
						$thumbnail_type        = false;
						$image_size_dimensions = false;
						$video                 = false;
						if ( ! has_post_thumbnail() && fusion_get_page_option( 'video', $id ) ) {
							$thumbnail_type        = 'video';
							$image_size_dimensions = avada_get_image_size_dimensions( $image_size );
							$video                 = apply_filters( 'privacy_iframe_embed', fusion_get_page_option( 'video', $id ) );
						} elseif ( $fusion_settings->get( 'featured_image_placeholder' ) || has_post_thumbnail() ) {
							$thumbnail_type       = 'image';
							$featured_image_sizes = [ 'portfolio-two', 'portfolio-three', 'portfolio-five', 'portfolio-six', 'blog-medium', 'full' ];
							$image_data           = fusion_get_image_data( $id, $featured_image_sizes, get_permalink( $id ) );
						}

						// Set image or placeholder and correct corresponding styling.
						if ( has_post_thumbnail() ) {
							$post_thumbnail_attachment = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' );
							$masonry_attribute_style   = 'background-image:url(' . $post_thumbnail_attachment[0] . ');';
						} else {
							$post_thumbnail_attachment = [];
							$masonry_attribute_style   = 'background-color:#f6f6f6;';
						}

						// Get the correct image orientation class.
						$element_orientation_class = $fusion_library->images->get_element_orientation_class( get_post_thumbnail_id(), $post_thumbnail_attachment, self::$args['portfolio_masonry_grid_ratio'], self::$args['portfolio_masonry_width_double'] );
						$element_base_padding      = $fusion_library->images->get_element_base_padding( $element_orientation_class );

						// Check if we have a landscape image, then it has to stretch over 2 cols.
						$regular_images_found = '';
						if ( 'fusion-element-landscape' !== $element_orientation_class ) {
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

						$image_data['masonry_data'] = $masonry_data;

						$rich_snippets['true']  = avada_render_rich_snippets_for_pages();
						$rich_snippets['false'] = avada_render_rich_snippets_for_pages( false );

						$post_categories = get_the_terms( $id, 'portfolio_category' );

						$project_url = fusion_get_page_option( 'project_url', $id );

						$term_list = get_the_term_list( $id, 'portfolio_category', '<div class="fusion-carousel-meta">', ', ', '</div>' );

						$content = fusion_get_content_data( 'fusion_portfolio' );

						$permalink = get_the_permalink();

						$post_title = avada_render_post_title( $id, true, false, '2', $permalink );

						$the_cats = get_the_term_list( $id, 'portfolio_category', '', ', ', '' );

						$post_terms = '';
						if ( $the_cats ) {
							$post_terms = '<div class="fusion-portfolio-meta">' . $the_cats . '</div>';
						}

						$return_data['portfolios'][] = [
							'title'                 => get_the_title( $id ),
							'thumbnail_type'        => $thumbnail_type,
							'image_size_dimensions' => $image_size_dimensions,
							'video'                 => $video,
							'image_data'            => $image_data,
							'rich_snippets'         => $rich_snippets,
							'post_categories'       => $post_categories,
							'project_url'           => $project_url,
							'content'               => $content,
							'term_list'             => $term_list,
							'permalink'             => $permalink,
							'post_title'            => $post_title,
							'post_terms'            => $post_terms,
							'permalink'             => $permalink,
							'has_manual_excerpt'    => has_excerpt() ? true : null,
						];
					}
					wp_reset_postdata();
				}
				$return_data['regular_images_found'] = $regular_images_found;
				echo wp_json_encode( $return_data );
				die();
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

				global $fusion_library;

				$fusion_settings = awb_get_fusion_settings();

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_portfolio' );

				$defaults['column_spacing'] = FusionBuilder::validate_shortcode_attr_value( $defaults['column_spacing'], '' );

				if ( ! isset( $args['portfolio_layout_padding'] ) ) {
					$padding_values           = [];
					$padding_values['top']    = ( isset( $args['padding_top'] ) && '' !== $args['padding_top'] ) ? $args['padding_top'] : Fusion_Sanitize::size( $fusion_settings->get( 'portfolio_layout_padding', 'top' ) );
					$padding_values['right']  = ( isset( $args['padding_right'] ) && '' !== $args['padding_right'] ) ? $args['padding_right'] : Fusion_Sanitize::size( $fusion_settings->get( 'portfolio_layout_padding', 'right' ) );
					$padding_values['bottom'] = ( isset( $args['padding_bottom'] ) && '' !== $args['padding_bottom'] ) ? $args['padding_bottom'] : Fusion_Sanitize::size( $fusion_settings->get( 'portfolio_layout_padding', 'bottom' ) );
					$padding_values['left']   = ( isset( $args['padding_left'] ) && '' !== $args['padding_left'] ) ? $args['padding_left'] : Fusion_Sanitize::size( $fusion_settings->get( 'portfolio_layout_padding', 'left' ) );

					$defaults['portfolio_layout_padding'] = implode( ' ', $padding_values );
				}

				if ( '0' === $defaults['column_spacing'] ) {
					$defaults['column_spacing'] = '0.0';
				}

				if ( '0' === $defaults['offset'] ) {
					$defaults['offset'] = '';
				}

				// Backwards compatibility for old param name.
				if ( 'grid' === $defaults['layout'] && ! isset( $args['text_layout'] ) ) {
					$defaults['boxed_text'] = 'no_text';
				}

				if ( $defaults['boxed_text'] ) {
					$defaults['text_layout'] = $defaults['boxed_text'];
				}

				if ( 'grid-with-excerpts' === $defaults['layout'] || 'grid-with-text' === $defaults['layout'] ) {
					$defaults['layout'] = 'grid';
				}

				if ( 'default' === $defaults['text_layout'] ) {
					$defaults['text_layout'] = $fusion_settings->get( 'portfolio_text_layout', false, 'unboxed' );
				}

				if ( 'full-content' === $defaults['content_length'] ) {
					$defaults['content_length'] = 'full_content';
				}

				if ( 'default' === $defaults['content_length'] ) {
					$defaults['content_length'] = $fusion_settings->get( 'portfolio_content_length', false, 'excerpt' );
				}

				if ( 'default' === $defaults['portfolio_title_display'] ) {
					$defaults['portfolio_title_display'] = $fusion_settings->get( 'portfolio_title_display', false, 'all' );
				}

				if ( 'default' === $defaults['portfolio_text_alignment'] ) {
					$defaults['portfolio_text_alignment'] = $fusion_settings->get( 'portfolio_text_alignment', false, 'left' );
				}

				if ( 'default' === $defaults['picture_size'] ) {
					$image_size = $fusion_settings->get( 'portfolio_featured_image_size' );
					if ( 'full' === $image_size ) {
						$defaults['picture_size'] = 'auto';
					} else {
						$defaults['picture_size'] = 'fixed';
					}
				}

				if ( 'masonry' === $defaults['layout'] ) {
					$defaults['picture_size'] = 'auto';
				}

				if ( 'default' === $defaults['pagination_type'] ) {
					$defaults['pagination_type'] = trim( str_replace( [ '_scroll', '_' ], [ '', '-' ], strtolower( $fusion_settings->get( 'portfolio_pagination_type', false, 'none' ) ) ), '-' );
				}

				if ( 'default' === $defaults['strip_html'] ) {
					$defaults['strip_html'] = $fusion_settings->get( 'portfolio_strip_html_excerpt', false, 'yes' );
				} else {
					$defaults['strip_html'] = ( 'yes' === $defaults['strip_html'] );
				}
				extract( $defaults );

				self::$args = $defaults;

				// Set the image size for the slideshow.
				$this->set_image_size();

				// As $excerpt_words is deprecated, only use it when explicity set.
				if ( $excerpt_words || '0' === $excerpt_words ) {
					$excerpt_length = $excerpt_words;
				}

				$title      = true;
				$categories = true;
				// Check the title and category display options.
				if ( self::$args['portfolio_title_display'] ) {
					$title_display = self::$args['portfolio_title_display'];
					$title         = ( 'all' === $title_display || 'title' === $title_display );
					$categories    = ( 'all' === $title_display || 'cats' === $title_display );
				}

				// Add styling for alignment and padding.
				$styling = '';
				if ( 'carousel' !== self::$args['layout'] && 'no_text' !== self::$args['text_layout'] ) {
					$layout_padding   = ( 'boxed' === self::$args['text_layout'] && '' !== self::$args['portfolio_layout_padding'] ) ? 'padding: ' . self::$args['portfolio_layout_padding'] . ';' : '';
					$layout_alignment = 'text-align: ' . self::$args['portfolio_text_alignment'] . ';';
					$styling         .= '<style type="text/css">.fusion-portfolio-wrapper#fusion-portfolio-' . $this->portfolio_counter . ' .fusion-portfolio-content{ ' . $layout_padding . ' ' . $layout_alignment . ' }</style>';
				}

				$portfolio_query = $this->query();

				if ( ! $portfolio_query->have_posts() ) {
					$this->portfolio_counter++;
					return fusion_builder_placeholder( 'avada_portfolio', 'portfolio posts' );
				}

				$portfolio_posts = '';
				if ( is_array( self::$args['cat_slugs'] ) && 0 < count( self::$args['cat_slugs'] ) && function_exists( 'fusion_add_url_parameter' ) ) {
					$cat_ids = [];
					foreach ( self::$args['cat_slugs'] as $cat_slug ) {
						$cat_obj = get_term_by( 'slug', $cat_slug, 'portfolio_category' );
						if ( isset( $cat_obj->term_id ) ) {
							$cat_ids[] = $cat_obj->term_id;
						}
					}
					$cat_ids = implode( ',', $cat_ids );
				}

				// Set a gallery id for the lightbox triggers on rollovers.
				$gallery_id = '-rw-' . $this->portfolio_counter;

				$lazy_load = ( 'avada' === $fusion_settings->get( 'lazy_load' ) && ! is_feed() ) ? true : false;

				// Loop through returned posts.
				// Setup the inner HTML for each elements.
				while ( $portfolio_query->have_posts() ) {
					$portfolio_query->the_post();

					$this->post_id = get_the_ID();
					$video_meta    = fusion_get_page_option( 'video', $this->post_id );

					// Only add post if it has a featured image, or a video, or if placeholders are activated.
					if ( has_post_thumbnail() || $fusion_settings->get( 'featured_image_placeholder' ) || $video_meta ) {

						// Reset vars.
						$rich_snippets             = '';
						$post_classes              = '';
						$title_terms               = '';
						$image                     = '';
						$post_title                = '';
						$post_terms                = '';
						$separator                 = '';
						$post_content              = '';
						$buttons                   = '';
						$learn_more_button         = '';
						$view_project_button       = '';
						$post_separator            = '';
						$element_orientation_class = '';

						// For carousels we only need the image and a li wrapper.
						if ( 'carousel' === $layout ) {
							// Title on rollover layout.
							if ( 'title_on_rollover' === $carousel_layout ) {
								$show_title = 'default';
								// Title below image layout.
							} else {
								$show_title = 'disable';

								// Get the post title.
								$fusion_portfolio_carousel_title = '<h4 ' . FusionBuilder::attributes( 'fusion-carousel-title' ) . '><a href="' . get_permalink( $this->post_id ) . '" target="_self">' . get_the_title() . '</a></h4>';
								$title_terms                    .= apply_filters( 'fusion_portfolio_carousel_title', $fusion_portfolio_carousel_title );

								// Get the terms.
								$carousel_terms = get_the_term_list( $this->post_id, 'portfolio_category', '<div class="fusion-carousel-meta">', ', ', '</div>' );
								$title_terms   .= apply_filters( 'fusion_portfolio_carousel_terms', $carousel_terms );
							}

							// Render the video set in page options if no featured image is present.
							if ( ! has_post_thumbnail() && $video_meta ) {
								// For the portfolio one column layout we need a fixed max-width.
								if ( '1' === $columns || 1 === $columns ) {
									$video_max_width = '540px';
									// For all other layouts get the calculated max-width from the image size.
								} else {
									$featured_image_size_dimensions = avada_get_image_size_dimensions( $this->image_size );
									$video_max_width                = $featured_image_size_dimensions['width'];
								}

								$video        = $video_meta;
								$video_markup = '<div class="fusion-image-wrapper fusion-video" style="max-width:' . $video_max_width . ';">';
								if ( 'on' === $fusion_settings->get( 'video_facade' ) ) {

									if ( false !== strpos( $video_meta, 'vimeo' ) ) {
										$video_id = fusion_get_vimeo_id( $video_meta );
										$video    = '<lite-vimeo videoid="' . $video_id . '"></lite-vimeo>';
									}

									if ( false !== strpos( $video_meta, 'youtube' ) ) {
										$video_id = fusion_get_youtube_id( $video_meta );
										$video    = '<lite-youtube videoid="' . $video_id . '"></lite-youtube>';
									}
								}
								$video_markup .= $video . '</div>';

								$image = apply_filters( 'fusion_portfolio_item_video', $video_markup, $video, $video_max_width );

							} elseif ( $fusion_settings->get( 'featured_image_placeholder' ) || has_post_thumbnail() ) {
								// Get the post image.
								if ( 'full' === $this->image_size && class_exists( 'Avada' ) ) {
									Avada()->images->set_grid_image_meta(
										[
											'layout'       => 'portfolio_full',
											'columns'      => $columns,
											'gutter_width' => $column_spacing,
										]
									);
								}
								$image = fusion_render_first_featured_image_markup( $this->post_id, $this->image_size, get_permalink( $this->post_id ), true, false, false, 'default', $show_title, '', $gallery_id );

								if ( class_exists( 'Avada' ) ) {
									Avada()->images->set_grid_image_meta( [] );
								}
							}

							$portfolio_posts .= '<li ' . FusionBuilder::attributes( 'fusion-carousel-item' ) . '><div ' . FusionBuilder::attributes( 'fusion-carousel-item-wrapper' ) . '>' . avada_render_rich_snippets_for_pages() . $image . $title_terms . '</div></li>';

						} else {

							$permalink = get_permalink();
							if ( isset( $cat_ids ) && function_exists( 'fusion_add_url_parameter' ) && 'off' === self::$args['hide_url_params'] ) {
								$permalink = fusion_add_url_parameter( $permalink, 'portfolioCats', $cat_ids );

							}

							// Include the post categories or tags based on element option as css classes for later useage with filters.
							$post_categories = get_the_terms( $this->post_id, 'portfolio_category' );
							$post_tags       = get_the_terms( $this->post_id, 'portfolio_tags' );

							if ( 'tag' === $defaults['pull_by'] ) {
								if ( $post_tags ) {
									foreach ( $post_tags as $post_tag ) {
										$post_classes .= urldecode( $post_tag->slug ) . ' ';
									}
								}
							} else {
								if ( $post_categories ) {
									foreach ( $post_categories as $post_category ) {
										$post_classes .= urldecode( $post_category->slug ) . ' ';
									}
								}
							}

							// Add the col-spacing class if needed.
							if ( $column_spacing ) {
								$post_classes .= 'fusion-col-spacing ';
							}

							$post_classes .= 'post-' . $this->post_id;

							// Render the video set in page options if no featured image is present.
							if ( ! has_post_thumbnail() && $video_meta ) {
								// For the portfolio one column layout we need a fixed max-width.
								if ( '1' === $columns || 1 === $columns ) {
									$video_max_width = '540px';
									// For all other layouts get the calculated max-width from the image size.
								} else {
									$featured_image_size_dimensions = avada_get_image_size_dimensions( $this->image_size );
									$video_max_width                = $featured_image_size_dimensions['width'];
								}

								$video        = $video_meta;
								$video_markup = '<div class="fusion-image-wrapper fusion-video" style="max-width:' . $video_max_width . ';">';
								if ( 'on' === $fusion_settings->get( 'video_facade' ) ) {

									if ( false !== strpos( $video_meta, 'vimeo' ) ) {
										$video_id = fusion_get_vimeo_id( $video_meta );
										$video    = '<lite-vimeo videoid="' . $video_id . '"></lite-vimeo>';
									}

									if ( false !== strpos( $video_meta, 'youtube' ) ) {
										$video_id = fusion_get_youtube_id( $video_meta );
										$video    = '<lite-youtube videoid="' . $video_id . '"></lite-youtube>';
									}
								}
								$video_markup .= $video . '</div>';

								$image = apply_filters( 'fusion_portfolio_item_video', $video_markup, $video, $video_max_width );

							} elseif ( $fusion_settings->get( 'featured_image_placeholder' ) || has_post_thumbnail() ) {

								$responsive_images_columns = $columns;
								$masonry_attributes        = [];
								$element_base_padding      = 0.8;

								// Masonry layout.
								if ( 'masonry' === $layout ) {
									// Set image or placeholder and correct corresponding styling.
									if ( has_post_thumbnail() ) {
										$post_thumbnail_attachment = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' );
										$masonry_attribute_style   = $lazy_load ? '' : 'background-image:url(' . $post_thumbnail_attachment[0] . ');';
									} else {
										$post_thumbnail_attachment = [];
										$masonry_attribute_style   = 'background-color:#f6f6f6;';
									}

									// Get the correct image orientation class.
									if ( class_exists( 'Avada' ) ) {
										$element_orientation_class = Avada()->images->get_element_orientation_class( get_post_thumbnail_id(), $post_thumbnail_attachment, $defaults['portfolio_masonry_grid_ratio'], $defaults['portfolio_masonry_width_double'] );
										$element_base_padding      = Avada()->images->get_element_base_padding( $element_orientation_class );
									}
									$post_classes .= ' ' . $element_orientation_class;

									$masonry_column_offset = ' - ' . ( (int) $column_spacing / 2 ) . 'px';
									if ( false !== strpos( $element_orientation_class, 'fusion-element-portrait' ) ) {
										$masonry_column_offset = '';
									}

									$masonry_column_spacing = ( (int) $column_spacing ) . 'px';

									if ( 'no_text' !== $text_layout && 'boxed' === $text_layout &&
										class_exists( 'Fusion_Sanitize' ) && class_exists( 'Fusion_Color' ) &&
										'transparent' !== Fusion_Sanitize::color( self::$args['grid_element_color'] ) &&
										0 !== Fusion_Color::new_color( self::$args['grid_element_color'] )->alpha
									) {
										$masonry_column_offset = ' - ' . ( (int) $column_spacing / 2 ) . 'px';
										if ( false !== strpos( $element_orientation_class, 'fusion-element-portrait' ) ) {
											$masonry_column_offset = ' + 4px';
										}

										$masonry_column_spacing = ( (int) $column_spacing - 4 ) . 'px';
										if ( false !== strpos( $element_orientation_class, 'fusion-element-landscape' ) ) {
											$masonry_column_spacing = ( (int) $column_spacing - 10 ) . 'px';
										}
									}

									// Calculate the correct size of the image wrapper container, based on orientation and column spacing.
									$masonry_attribute_style .= 'padding-top:calc((100% + ' . $masonry_column_spacing . ') * ' . $element_base_padding . $masonry_column_offset . ');';

									// Check if we have a landscape image, then it has to stretch over 2 cols.
									if ( '1' !== $columns && 1 !== $columns && false !== strpos( $element_orientation_class, 'fusion-element-landscape' ) ) {
										$responsive_images_columns = (int) $columns / 2;
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
								}

								// Get the post image.
								if ( 'full' === $this->image_size && class_exists( 'Avada' ) ) {
									Avada()->images->set_grid_image_meta(
										[
											'layout'       => 'portfolio_full',
											'columns'      => $responsive_images_columns,
											'gutter_width' => $column_spacing,
										]
									);
								}
								$image = fusion_render_first_featured_image_markup( $this->post_id, $this->image_size, $permalink, true, false, false, 'default', 'default', '', $gallery_id, 'yes', false, $masonry_attributes );
								if ( class_exists( 'Avada' ) ) {
									Avada()->images->set_grid_image_meta( [] );
								}
							}

							// Additional content for layouts using text.
							if ( 'carousel' !== self::$args['layout'] && 'no_text' !== self::$args['text_layout'] ) {

								// Get the rich snippets, if enabled.
								$rich_snippets = avada_render_rich_snippets_for_pages( false );

								// Get the post title.
								if ( $title ) {
									$post_title = avada_render_post_title( $this->post_id, true, false, '2', $permalink );
								}

								// Get the post terms.
								if ( $categories ) {
									$the_cats = get_the_term_list( $this->post_id, 'portfolio_category', '', ', ', '' );
									if ( $the_cats ) {
										$post_terms = '<div class="fusion-portfolio-meta">' . $the_cats . '</div>';
									}
								}

								// Get the post content.
								ob_start();
								/**
								 * The fusion_portfolio_shortcode_content hook.
								 *
								 * @hooked content - 10 (outputs the post content)
								 */
								do_action( 'fusion_portfolio_shortcode_content' );

								$stripped_content = ob_get_clean();

								// For boxed layouts add a content separator if there is a post content.
								if ( 'boxed' === $text_layout && $stripped_content && 'masonry' !== self::$args['layout'] ) {
									$separator = '<div ' . FusionBuilder::attributes( 'portfolio-fusion-content-sep' ) . '></div>';
								}

								// On one column layouts render the "Learn More" and "View Project" buttons.
								if ( ( '1' === $columns || 1 === $columns ) && 'masonry' !== self::$args['layout'] ) {
									$classes = 'fusion-button fusion-button-small fusion-button-default fusion-button-' . strtolower( $fusion_settings->get( 'button_type' ) );

									// Add the "Learn More" button.
									$learn_more_button = '<a href="' . $permalink . '" ' . FusionBuilder::attributes( $classes ) . '>' . esc_attr__( 'Learn More', 'fusion-core' ) . '</a>';

									// If there is a project url, add the "View Project" button.
									$view_project_button = '';
									if ( fusion_get_page_option( 'project_url', $this->post_id ) ) {
										$view_project_button = '<a href="' . fusion_get_page_option( 'project_url', $this->post_id ) . '" ' . FusionBuilder::attributes( $classes ) . '>' . esc_attr__( 'View Project', 'fusion-core' ) . '</a>';
									}

									// Wrap buttons.
									$button_span_class = ( 'yes' === $fusion_settings->get( 'button_span' ) ) ? ' fusion-portfolio-buttons-full' : '';
									$buttons           = '<div ' . FusionBuilder::attributes( 'fusion-portfolio-buttons' . $button_span_class ) . '>' . $learn_more_button . $view_project_button . '</div>';

								}

								// Put it all together.
								$post_content  = '<div ' . FusionBuilder::attributes( 'portfolio-shortcode-portfolio-content' ) . '>';
								$post_content .= apply_filters( 'fusion_portfolio_grid_title', $post_title );
								$post_content .= apply_filters( 'fusion_portfolio_grid_terms', $post_terms );
								$post_content .= apply_filters( 'fusion_portfolio_grid_separator', $separator );
								$post_content .= '<div ' . FusionBuilder::attributes( 'fusion-post-content' ) . '>';
								$post_content .= apply_filters( 'fusion_portfolio_grid_content', $stripped_content );
								$post_content .= apply_filters( 'fusion_portfolio_grid_buttons', $buttons, $learn_more_button, $view_project_button );
								$post_content .= '</div></div>';
							} else {
								// Get the rich snippets for grid layout without excerpts.
								$rich_snippets = avada_render_rich_snippets_for_pages();
							}

							// Post separator for one column grid layouts.
							if ( ( '1' === $columns || 1 === $columns ) && 'boxed' !== self::$args['text_layout'] && 'grid' === self::$args['layout'] ) {
								$post_separator = '<div class="fusion-clearfix"></div><div class="fusion-separator sep-double"></div>';
							}

							$portfolio_posts .= '<article id="portfolio-' . $this->portfolio_counter . '-post-' . $this->post_id . '" class="fusion-portfolio-post ' . $post_classes . '"><div ' . FusionBuilder::attributes( 'portfolio-fusion-portfolio-content-wrapper' ) . '>' . $rich_snippets . $image . $post_content . '</div>' . apply_filters( 'fusion_portfolio_grid_post_separator', $post_separator ) . '</article>';
						}
					}
				}

				wp_reset_postdata();

				// Wrap all the portfolio posts with the appropriate HTML markup.
				// Carousel layout.
				if ( 'carousel' === $layout ) {
					self::$args['data-pages'] = '';

					$main_carousel = '<ul ' . FusionBuilder::attributes( 'fusion-carousel-holder' ) . '>' . $portfolio_posts . '</ul>';

					// Check if navigation should be shown.
					$navigation = '';
					if ( 'yes' === $show_nav ) {
						$navigation = function_exists( 'awb_get_carousel_nav' ) ? awb_get_carousel_nav() : '';
					}

					$html = '<div ' . FusionBuilder::attributes( 'portfolio-shortcode' ) . '><div ' . FusionBuilder::attributes( 'portfolio-shortcode-carousel' ) . '><div ' . FusionBuilder::attributes( 'fusion-carousel-positioner' ) . '>' . $main_carousel . $navigation . '</div></div></div>';

					// Other layouts.
				} else {
					// Reset vars.
					$filter_wrapper       = '';
					$filter               = '';
					$styles               = '';
					$portfolio_categories = [];
					$portfolio_tags       = [];

					// Setup the filters, if enabled.
					if ( 'no' !== $filters ) {
						if ( 'category' === $defaults['pull_by'] ) {
							$portfolio_categories = get_terms( 'portfolio_category' );
						}

						if ( 'tag' === $defaults['pull_by'] ) {
							$portfolio_tags = get_terms( 'portfolio_tags' );
						}
					}

					// Check if filters should be displayed.
					if ( $portfolio_categories || $portfolio_tags ) {

						// Check if the "All" filter should be displayed.
						$first_filter = true;
						if ( 'yes-without-all' !== $filters ) {
							$filter       = '<li role="menuitem" ' . FusionBuilder::attributes( 'fusion-filter fusion-filter-all fusion-active' ) . '><a ' . FusionBuilder::attributes(
								'portfolio-shortcode-filter-link',
								[
									'data-filter' => '*',
								]
							) . '>' . esc_attr__( 'All', 'fusion-core' ) . '</a></li>';
							$first_filter = false;
						}

						if ( 'tag' === $defaults['pull_by'] ) {
							// Loop through tags.
							foreach ( $portfolio_tags as $portfolio_tag ) {
								// Only display filters of non excluded tags.
								if ( ! in_array( $portfolio_tag->slug, self::$args['tags_to_exclude'], true ) ) {
									// Check if tags have been chosen.
									if ( ! empty( self::$args['tag_slug'] ) ) {

										// Only display filters for explicitly included tags.
										if ( in_array( urldecode( $portfolio_tag->slug ), self::$args['tag_slugs'], true ) ) {
											// Set the first tag filter to active, if the all filter isn't shown.
											$active_class = '';
											if ( $first_filter ) {
												$active_class = ' fusion-active';
												$first_filter = false;
											}

											$filter .= '<li role="menuitem" ' . FusionBuilder::attributes( 'fusion-filter fusion-hidden' . $active_class ) . '><a ' . FusionBuilder::attributes(
												'portfolio-shortcode-filter-link',
												[
													'data-filter' => '.' . urldecode( $portfolio_tag->slug ),
												]
											) . '>' . $portfolio_tag->name . '</a></li>';
										}
									} else {
										// Display all tags.
										// Set the first tag filter to active, if the all filter isn't shown.
										$active_class = '';
										if ( $first_filter ) {
											$active_class = ' fusion-active';
											$first_filter = false;
										}

										$filter .= '<li role="menuitem" ' . FusionBuilder::attributes( 'fusion-filter fusion-hidden' . $active_class ) . '><a ' . FusionBuilder::attributes(
											'portfolio-shortcode-filter-link',
											[
												'data-filter' => '.' . urldecode( $portfolio_tag->slug ),
											]
										) . '>' . $portfolio_tag->name . '</a></li>';
									}
								}
							}
						} else {
							// Loop through categories.
							foreach ( $portfolio_categories as $portfolio_category ) {
								// Only display filters of non excluded categories.
								if ( ! in_array( $portfolio_category->slug, self::$args['cats_to_exclude'], true ) ) {
									// Check if categories have been chosen.
									if ( ! empty( self::$args['cat_slug'] ) ) {

										// Only display filters for explicitly included categories.
										if ( in_array( urldecode( $portfolio_category->slug ), self::$args['cat_slugs'], true ) ) {
											// Set the first category filter to active, if the all filter isn't shown.
											$active_class = '';
											if ( $first_filter ) {
												$active_class = ' fusion-active';
												$first_filter = false;
											}

											$filter .= '<li role="menuitem" ' . FusionBuilder::attributes( 'fusion-filter fusion-hidden' . $active_class ) . '><a ' . FusionBuilder::attributes(
												'portfolio-shortcode-filter-link',
												[
													'data-filter' => '.' . urldecode( $portfolio_category->slug ),
												]
											) . '>' . $portfolio_category->name . '</a></li>';
										}
									} else {
										// Display all categories.
										// Set the first category filter to active, if the all filter isn't shown.
										$active_class = '';
										if ( $first_filter ) {
											$active_class = ' fusion-active';
											$first_filter = false;
										}

										$filter .= '<li role="menuitem" ' . FusionBuilder::attributes( 'fusion-filter fusion-hidden' . $active_class ) . '><a ' . FusionBuilder::attributes(
											'portfolio-shortcode-filter-link',
											[
												'data-filter' => '.' . urldecode( $portfolio_category->slug ),
											]
										) . '>' . $portfolio_category->name . '</a></li>';
									}
								}
							}
						}

						// Wrap filters.
						$filter_wrapper  = '<div role="menubar">';
						$filter_wrapper .= '<ul ' . FusionBuilder::attributes( 'fusion-filters' ) . ' role="menu" aria-label="filters">' . $filter . '</ul>';
						$filter_wrapper .= '</div>';

					}

					// For column spacing set needed css.
					if ( $column_spacing ) {
						$styles = '<style type="text/css">.fusion-portfolio-' . $this->portfolio_counter . ' .fusion-portfolio-wrapper .fusion-col-spacing{padding:' . ( $column_spacing / 2 ) . 'px;}</style>';
					}

					// Pagination.
					self::$args['data-pages'] = $portfolio_query->max_num_pages;
					$pagination               = '';

					if ( 'none' !== $pagination_type && 1 < esc_attr( $portfolio_query->max_num_pages ) ) {

						// Pagination is set to "load more" button.
						if ( 'load-more-button' === $pagination_type && -1 !== intval( $number_posts ) ) {
							$pagination .= '<button class="fusion-load-more-button fusion-portfolio-button fusion-clearfix">' . apply_filters( 'avada_load_more_posts_name', esc_attr__( 'Load More Posts', 'fusion-core' ) ) . '</button>';
						}

						$infinite_pagination = false;
						if ( 'load-more-button' === $pagination_type || 'infinite' === $pagination_type ) {
							$infinite_pagination = true;
						}

						$pagination .= fusion_pagination( $portfolio_query->max_num_pages, $fusion_settings->get( 'pagination_range' ), $portfolio_query, $infinite_pagination );
					}

					if ( 'masonry' === $layout ) {
						$portfolio_posts = '<article class="fusion-portfolio-post fusion-grid-sizer"></article>' . $portfolio_posts;
					}

					// Put it all together.
					$html = $styling . '<div ' . FusionBuilder::attributes( 'portfolio-shortcode' ) . '>' . $filter_wrapper . $styles . '<div ' . FusionBuilder::attributes( 'portfolio-shortcode-portfolio-wrapper' ) . '>' . $portfolio_posts . '</div>' . $pagination . '</div>';

				}

				$this->portfolio_counter++;

				$this->on_render();

				return apply_filters( 'fusion_element_portfolio_content', $html, $args );

			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function attr() {

				$fusion_settings = awb_get_fusion_settings();

				$attr            = fusion_builder_visibility_atts(
					self::$args['hide_on_mobile'],
					[
						'class' => 'fusion-recent-works fusion-portfolio-element fusion-portfolio fusion-portfolio-' . $this->portfolio_counter . ' fusion-portfolio-' . self::$args['layout'] . ' fusion-portfolio-paging-' . self::$args['pagination_type'],
						'style' => '',
					]
				);
				$attr['style']   = Fusion_Builder_Margin_Helper::get_margins_style( self::$args );
				$attr['data-id'] = '-rw-' . $this->portfolio_counter;

				// Add classes for carousel layout.
				if ( 'carousel' === self::$args['layout'] ) {
					$attr['class'] .= ' recent-works-carousel portfolio-carousel';
					if ( 'auto' === self::$args['picture_size'] ) {
						$attr['class'] .= ' picture-size-auto';
					}
				} else {
					// Add classes for grid and masonry layouts.
					$attr['class'] .= ' fusion-portfolio-' . $this->column . ' fusion-portfolio-' . self::$args['text_layout'];

					if ( ( 'grid' === self::$args['layout'] || 'masonry' === self::$args['layout'] ) && 'no_text' !== self::$args['text_layout'] ) {
						$attr['class'] .= ' fusion-portfolio-text';

						if ( '1' === self::$args['columns'] && 'floated' === self::$args['one_column_text_position'] ) {
							$attr['class'] .= ' fusion-portfolio-text-floated';
						}

						if ( 'grid' === self::$args['layout'] ) {
							if ( 'yes' === self::$args['equal_heights'] ) {
								$attr['class'] .= ' fusion-portfolio-equal-heights';
							}
						}
					}

					$attr['data-columns'] = $this->column;
				}

				// Add class for no spacing.
				if ( in_array( self::$args['column_spacing'], [ 0, '0', '0px' ], true ) ) {
					$attr['class'] .= ' fusion-no-col-space';
				}

				// Add class if rollover is enabled.
				if ( $fusion_settings->get( 'image_rollover' ) ) {
					$attr['class'] .= ' fusion-portfolio-rollover';
				}

				// Add custom class.
				if ( self::$args['class'] ) {
					$attr['class'] .= ' ' . self::$args['class'];
				}

				// Add custom id.
				if ( self::$args['id'] ) {
					$attr['id'] = self::$args['id'];
				}

				// Add animation classes.
				if ( self::$args['animation_type'] ) {
					$animations = FusionBuilder::animations(
						[
							'type'      => self::$args['animation_type'],
							'direction' => self::$args['animation_direction'],
							'speed'     => self::$args['animation_speed'],
							'offset'    => self::$args['animation_offset'],
						]
					);

					$attr = array_merge( $attr, $animations );

					$attr['class'] .= ' ' . $attr['animation_class'];
					unset( $attr['animation_class'] );
				}

				return $attr;

			}

			/**
			 * Builds the portfolio-wrapper attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @param array $args The arguments array.
			 * @return array
			 */
			public function portfolio_wrapper_attr( $args ) {

				$attr = [
					'class'            => 'fusion-portfolio-wrapper',
					'id'               => 'fusion-portfolio-' . $this->portfolio_counter,
					'data-picturesize' => self::$args['picture_size'],
				];

				$attr['data-pages'] = self::$args['data-pages'];

				if ( self::$args['column_spacing'] ) {
					$margin        = ( -1 ) * self::$args['column_spacing'] / 2;
					$attr['style'] = 'margin:' . $margin . 'px;';
				}

				return $attr;

			}

			/**
			 * Builds the fusion-portfolio-content attributes array.
			 *
			 * @access public
			 * @since 1.3
			 * @param array $args The arguments array.
			 * @return array
			 */
			public function portfolio_content_attr( $args ) {
				global $fusion_library;

				$attr = [
					'class' => 'fusion-portfolio-content',
					'style' => '',
				];

				if ( 'masonry' === self::$args['layout'] ) {
					$masonry_content_padding = self::$args['column_spacing'] / 2;

					if ( 'boxed' === self::$args['text_layout'] ) {
						$attr['style'] .= 'bottom:0px;';
						$attr['style'] .= 'left:0px;';
						$attr['style'] .= 'right:0px;';
					} else {
						$attr['style'] .= 'padding:20px 0px;';
						$attr['style'] .= 'bottom:0px;';
						$attr['style'] .= 'left:0px;';
						$attr['style'] .= 'right:0px;';
					}
					$color     = Fusion_Color::new_color( self::$args['grid_box_color'] );
					$color_css = $color->to_css_var_or_rgba();
					if ( 0 === $color->alpha ) {
						$color_css = $color->get_new( 'alpha', 1 );
						$color_css = $color->to_css_var_or_rgba();
					}
					$attr['style'] .= 'background-color:' . $color_css . ';';
					$attr['style'] .= 'z-index:1;';
					$attr['style'] .= 'position:absolute;';
					$attr['style'] .= 'margin:0;';

				} elseif ( 'grid' === self::$args['layout'] && 'boxed' === self::$args['text_layout'] ) {
					$color          = Fusion_Color::new_color( self::$args['grid_box_color'] );
					$color_css      = $color->to_css_var_or_rgba();
					$attr['style'] .= 'background-color:' . $color_css . ';';
				}

				return $attr;

			}

			/**
			 * Builds the portfolio-content-wrapper attributes array.
			 *
			 * @access public
			 * @since 1.3
			 * @return array
			 */
			public function portfolio_content_wrapper_attr() {
				$attr          = [
					'class' => 'fusion-portfolio-content-wrapper',
				];
				$attr['style'] = '';

				if ( 'grid' === self::$args['layout'] || 'masonry' === self::$args['layout'] ) {
					$element_color = Fusion_Color::new_color( self::$args['grid_element_color'] );
					if ( 'boxed' !== self::$args['text_layout'] || 0 === $element_color->alpha || 'transparent' === self::$args['grid_element_color'] ) {
						$attr['style'] .= 'border:none;';
					} else {
						$attr['style'] .= 'border:1px solid ' . self::$args['grid_element_color'] . ';border-bottom-width:3px;';
					}
				}

				if ( 'grid' === self::$args['layout'] && 'boxed' === self::$args['text_layout'] ) {
					$color          = Fusion_Color::new_color( self::$args['grid_box_color'] );
					$color_css      = $color->to_css_var_or_rgba();
					$attr['style'] .= 'background-color:' . $color_css . ';';
				}

				return $attr;
			}

			/**
			 * Builds the carousel attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function carousel_attr() {

				$attr = [
					'class' => 'fusion-carousel',
				];

				if ( 'title_below_image' === self::$args['carousel_layout'] ) {
					$attr['data-metacontent'] = 'yes';
					$attr['class']           .= ' fusion-carousel-title-below-image';
				}

				if ( 'fixed' === self::$args['picture_size'] ) {
					$attr['class'] .= ' fusion-portfolio-carousel-fixed';
				}

				$attr['data-autoplay']    = self::$args['autoplay'];
				$attr['data-columns']     = self::$args['columns'];
				$attr['data-itemmargin']  = self::$args['column_spacing'];
				$attr['data-itemwidth']   = 180;
				$attr['data-touchscroll'] = self::$args['mouse_scroll'];
				$attr['data-imagesize']   = self::$args['picture_size'];
				$attr['data-scrollitems'] = self::$args['scroll_items'];

				return $attr;
			}

			/**
			 * Builds the filter-link attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @param array $args The arguments array.
			 * @return array
			 */
			public function filter_link_attr( $args ) {

				$attr = [
					'href' => '#',
				];

				if ( $args['data-filter'] ) {
					$attr['data-filter'] = $args['data-filter'];
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
					'style' => 'border-color:' . self::$args['grid_separator_color'] . ';',
				];

				$separator_styles_array = explode( '|', self::$args['grid_separator_style_type'] );
				$separator_styles       = '';

				foreach ( $separator_styles_array as $separator_style ) {
					$separator_styles .= ' sep-' . $separator_style;
				}

				$border_color = Fusion_Color::new_color( self::$args['grid_separator_color'] );
				if ( 0 === $border_color->alpha || 'transparent' === self::$args['grid_separator_color'] ) {
					$attr['class'] .= ' sep-transparent';
				}

				$attr['class'] .= $separator_styles;

				return $attr;
			}

			/**
			 * Set image size.
			 *
			 * @access public
			 * @since 1.0
			 * @return void
			 */
			public function set_image_size() {

				// Set columns object var to correct string.
				switch ( self::$args['columns'] ) {
					case 1:
						$this->column = 'one';
						break;
					case 2:
						$this->column = 'two';
						break;
					case 3:
						$this->column = 'three';
						break;
					case 4:
						$this->column = 'four';
						break;
					case 5:
						$this->column = 'five';
						break;
					case 6:
						$this->column = 'six';
						break;
				}

				// Set the image size according to picture size param and layout.
				$this->image_size = 'full';
				if ( 'fixed' === self::$args['picture_size'] ) {
					if ( 'carousel' === self::$args['layout'] ) {
						$this->image_size = 'portfolio-two';
						if ( 'six' === $this->column || 'five' === $this->column || 'four' === $this->column ) {
							$this->image_size = 'blog-medium';
						}
					} else {
						$this->image_size = 'portfolio-' . $this->column;
						if ( 'six' === $this->column ) {
							$this->image_size = 'portfolio-five';
						} elseif ( 'four' === $this->column ) {
							$this->image_size = 'portfolio-three';
						}
					}
				}
			}

			/**
			 * Echoes the post-content.
			 *
			 * @access public
			 * @since 1.0
			 * @return void
			 */
			public function get_post_content() {

				if ( 'no_text' !== self::$args['content_length'] ) {
					$excerpt = 'no';
					if ( 'excerpt' === strtolower( self::$args['content_length'] ) ) {
						$excerpt = 'yes';
					}

					if ( function_exists( 'fusion_get_post_content' ) ) {
						echo fusion_get_post_content( '', $excerpt, self::$args['excerpt_length'], self::$args['strip_html'] ); // phpcs:ignore WordPress.Security
					} else {
						the_excerpt();
					}
				}
			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.1
			 * @return array $sections FAQ settings.
			 */
			public function add_options() {

				if ( ! class_exists( 'Fusion_Settings' ) ) {
					return;
				}

				$option_name = Fusion_Settings::get_option_name();

				return [
					'portfolio_shortcode_section' => [
						'label'       => esc_attr__( 'Portfolio', 'fusion-core' ),
						'description' => '',
						'id'          => 'portfolio_shortcode_section',
						'icon'        => 'fusiona-insertpicture',
						'type'        => 'sub-section',
						'fields'      => [
							'portfolio_featured_image_size' => [
								'label'       => esc_attr__( 'Portfolio Featured Image Size', 'fusion-core' ),
								'description' => __( 'Controls if the featured image size is fixed (cropped) or auto (full image ratio) for portfolio elements. <strong>IMPORTANT:</strong> Fixed works best with a standard 940px site width. Auto works best with larger site widths.', 'fusion-core' ),
								'id'          => 'portfolio_featured_image_size',
								'default'     => 'full',
								'type'        => 'radio-buttonset',
								'transport'   => 'postMessage',
								'choices'     => [
									'cropped' => esc_attr__( 'Fixed', 'fusion-core' ),
									'full'    => esc_attr__( 'Auto', 'fusion-core' ),
								],
							],
							'portfolio_columns'            => [
								'label'       => esc_attr__( 'Number of Columns', 'fusion-core' ),
								'description' => __( 'Set the number of columns per row. With Carousel layout this specifies the maximum amount of columns. <strong>IMPORTANT:</strong> Masonry layout does not work with 1 column.', 'fusion-core' ),
								'id'          => 'portfolio_columns',
								'transport'   => 'postMessage',
								'default'     => 3,
								'type'        => 'slider',
								'choices'     => [
									'min'  => 1,
									'max'  => 6,
									'step' => 1,
								],
							],
							'portfolio_column_spacing'     => [
								'label'       => esc_attr__( 'Column Spacing', 'fusion-core' ),
								'description' => esc_attr__( 'Controls the column spacing for portfolio items.', 'fusion-core' ),
								'id'          => 'portfolio_column_spacing',
								'default'     => '30',
								'type'        => 'slider',
								'transport'   => 'postMessage',
								'choices'     => [
									'min'  => '0',
									'max'  => '300',
									'step' => '1',
								],
							],
							'portfolio_items'              => [
								'label'       => esc_attr__( 'Number of Portfolio Items Per Page', 'fusion-core' ),
								'description' => esc_attr__( 'Controls the number of posts that display per page for portfolio elements. Set to -1 to display all. Set to 0 to use the number of posts from Settings > Reading.', 'fusion-core' ),
								'id'          => 'portfolio_items',
								'default'     => '10',
								'type'        => 'slider',
								'transport'   => 'postMessage',
								'choices'     => [
									'min'  => '-1',
									'max'  => '50',
									'step' => '1',
								],
							],
							'portfolio_text_layout'        => [
								'label'       => esc_attr__( 'Portfolio Text Layout', 'fusion-core' ),
								'description' => esc_attr__( 'Controls if the portfolio text content is displayed boxed or unboxed or is completely disabled for portfolio elements.', 'fusion-core' ),
								'id'          => 'portfolio_text_layout',
								'default'     => 'unboxed',
								'type'        => 'radio-buttonset',
								'transport'   => 'postMessage',
								'choices'     => [
									'no_text' => esc_attr__( 'No Text', 'fusion-core' ),
									'boxed'   => esc_attr__( 'Boxed', 'fusion-core' ),
									'unboxed' => esc_attr__( 'Unboxed', 'fusion-core' ),
								],
							],
							'portfolio_content_length'     => [
								'label'       => esc_attr__( 'Portfolio Text Display', 'fusion-core' ),
								'description' => esc_attr__( 'Choose how to display the post excerpt for portfolio elements. Does not apply to image rollovers.', 'fusion-core' ),
								'id'          => 'portfolio_content_length',
								'default'     => 'excerpt',
								'type'        => 'radio-buttonset',
								'transport'   => 'postMessage',
								'choices'     => [
									'full_content' => esc_attr__( 'Full Content', 'fusion-core' ),
									'excerpt'      => esc_attr__( 'Excerpt', 'fusion-core' ),
									'no_text'      => esc_attr__( 'No Text', 'fusion-core' ),
								],
							],
							'portfolio_excerpt_length'     => [
								'label'           => esc_attr__( 'Excerpt Length', 'fusion-core' ),
								'description'     => esc_attr__( 'Controls the number of words in the excerpts for portfolio elements.', 'fusion-core' ),
								'id'              => 'portfolio_excerpt_length',
								'default'         => '10',
								'type'            => 'slider',
								'transport'       => 'postMessage',
								'choices'         => [
									'min'  => '0',
									'max'  => '500',
									'step' => '1',
								],
								'soft_dependency' => true,
							],
							'portfolio_strip_html_excerpt' => [
								'label'       => esc_attr__( 'Strip HTML from Excerpt', 'fusion-core' ),
								'description' => esc_attr__( 'Turn on to strip HTML content from the excerpt for portfolio elements.', 'fusion-core' ),
								'id'          => 'portfolio_strip_html_excerpt',
								'default'     => '1',
								'type'        => 'switch',
								'transport'   => 'postMessage',
							],
							'portfolio_title_display'      => [
								'label'       => esc_attr__( 'Portfolio Title Display', 'fusion-core' ),
								'description' => esc_attr__( 'Controls what displays with the portfolio post title for portfolio elements.', 'fusion-core' ),
								'id'          => 'portfolio_title_display',
								'default'     => 'all',
								'type'        => 'select',
								'transport'   => 'postMessage',
								'choices'     => [
									'all'   => esc_attr__( 'Title and Categories', 'fusion-core' ),
									'title' => esc_attr__( 'Only Title', 'fusion-core' ),
									'cats'  => esc_attr__( 'Only Categories', 'fusion-core' ),
									'none'  => esc_attr__( 'None', 'fusion-core' ),
								],
							],
							'portfolio_text_alignment'     => [
								'label'       => esc_attr__( 'Portfolio Text Alignment', 'fusion-core' ),
								'description' => esc_attr__( 'Controls the alignment of the portfolio title, categories and excerpt text when using the Portfolio Text layouts in portfolio elements.', 'fusion-core' ),
								'id'          => 'portfolio_text_alignment',
								'default'     => 'left',
								'type'        => 'radio-buttonset',
								'transport'   => 'postMessage',
								'choices'     => [
									'left'   => esc_attr__( 'Left', 'fusion-core' ),
									'center' => esc_attr__( 'Center', 'fusion-core' ),
									'right'  => esc_attr__( 'Right', 'fusion-core' ),
								],
							],
							'portfolio_layout_padding'     => [
								'label'           => esc_attr__( 'Portfolio Text Layout Padding', 'fusion-core' ),
								'description'     => esc_attr__( 'Controls the padding for the portfolio text layout when using boxed mode in portfolio elements.', 'fusion-core' ),
								'id'              => 'portfolio_layout_padding',
								'choices'         => [
									'top'    => true,
									'bottom' => true,
									'left'   => true,
									'right'  => true,
								],
								'default'         => [
									'top'    => '25px',
									'bottom' => '25px',
									'left'   => '25px',
									'right'  => '25px',
								],
								'type'            => 'spacing',
								'soft_dependency' => true,
								'output'          => [
									[
										'element'  => '.fusion-portfolio-boxed.fusion-portfolio-element .fusion-portfolio-content',
										'property' => 'padding',
									],
								],
							],
							'portfolio_pagination_type'    => [
								'label'       => esc_attr__( 'Pagination Type', 'fusion-core' ),
								'description' => esc_attr__( 'Controls the pagination type for portfolio elements.', 'fusion-core' ),
								'id'          => 'portfolio_pagination_type',
								'default'     => 'pagination',
								'type'        => 'select',
								'transport'   => 'postMessage',
								'choices'     => [
									'pagination'       => esc_attr__( 'Pagination', 'fusion-core' ),
									'infinite_scroll'  => esc_attr__( 'Infinite Scroll', 'fusion-core' ),
									'load_more_button' => esc_attr__( 'Load More Button', 'fusion-core' ),
								],
							],
							'portfolio_element_load_more_posts_button_bg_color' => [
								'label'       => esc_attr__( 'Load More Posts Button Background Color', 'fusion-core' ),
								'description' => esc_attr__( 'Controls the background color of the load more button for ajax post loading for portfolio elements.', 'fusion-core' ),
								'id'          => 'portfolio_element_load_more_posts_button_bg_color',
								'default'     => 'var(--awb-color7)',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--portfolio_element_load_more_posts_button_bg_color',
										'element'  => '.fusion-load-more-button',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'portfolio_element_load_more_posts_button_text_color' => [
								'label'       => esc_attr__( 'Load More Posts Button Text Color', 'fusion-core' ),
								'description' => esc_attr__( 'Controls the text color of the load more button for ajax post loading for portfolio elements.', 'fusion-core' ),
								'id'          => 'portfolio_element_load_more_posts_button_text_color',
								'default'     => 'var(--awb-color1)',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--portfolio_element_load_more_posts_button_text_color',
										'element'  => '.fusion-load-more-button',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'portfolio_element_load_more_posts_hover_button_bg_color' => [
								'label'       => esc_attr__( 'Load More Posts Button Hover Background Color', 'fusion-core' ),
								'description' => esc_attr__( 'Controls the hover background color of the load more button for ajax post loading for portfolio elements.', 'fusion-core' ),
								'id'          => 'portfolio_element_load_more_posts_hover_button_bg_color',
								'default'     => 'var(--awb-color5)',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--portfolio_element_load_more_posts_hover_button_bg_color',
										'element'  => '.fusion-load-more-button',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'portfolio_element_load_more_posts_hover_button_text_color' => [
								'label'       => esc_attr__( 'Load More Posts Hover Button Text Color', 'fusion-core' ),
								'description' => esc_attr__( 'Controls the hover text color of the load more button for ajax post loading for portfolio elements.', 'fusion-core' ),
								'id'          => 'portfolio_element_load_more_posts_hover_button_text_color',
								'default'     => 'var(--awb-color1)',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--portfolio_element_load_more_posts_hover_button_text_color',
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
			 * Builds the dynamic styling.
			 *
			 * @access public
			 * @since 3.1
			 * @return array
			 */
			public function add_styling() {

				global $wp_version, $content_media_query, $six_fourty_media_query, $three_twenty_six_fourty_media_query, $ipad_portrait_media_query, $fusion_settings, $dynamic_css_helpers, $fusion_library;

				$css['global']['.fusion-portfolio.fusion-portfolio-boxed .fusion-portfolio-content-wrapper']['border-color'] = $fusion_library->sanitize->color( $fusion_settings->get( 'timeline_color' ) );

				$css['global']['.fusion-filters .fusion-filter.fusion-active a']['color']        = 'var(--primary_color)';
				$css['global']['.fusion-filters .fusion-filter.fusion-active a']['border-color'] = 'var(--primary_color)';

				$css[ $content_media_query ]['.fusion-filters']['border-bottom'] = '0';
				$css[ $content_media_query ]['.fusion-filter']['float']          = 'none';
				$css[ $content_media_query ]['.fusion-filter']['margin']         = '0';
				$css[ $content_media_query ]['.fusion-filter']['border-bottom']  = '1px solid ' . $fusion_library->sanitize->color( $fusion_settings->get( 'sep_color' ) );

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
				$fusion_settings = awb_get_fusion_settings();

				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/portfolio.min.css' );

				if ( 'on' === $fusion_settings->get( 'video_facade' ) ) {
					FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/lite-yt-embed.min.css' );
					FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/lite-vimeo-embed.min.css' );
				}
			}

			/**
			 * Enqeueue the necessary scripts
			 *
			 * @access public
			 * @since 5.2
			 * @return void
			 */
			public function on_first_render() {
				$fusion_settings = awb_get_fusion_settings();

				if ( function_exists( 'Fusion_Portfolio' ) ) {
					Fusion_Portfolio()->enqueue_script();
				}

				// WIP.
				if ( 'on' === $fusion_settings->get( 'video_facade' ) ) {
					Fusion_Dynamic_JS::enqueue_script( 'lite-vimeo' );
					Fusion_Dynamic_JS::enqueue_script( 'lite-youtube' );
				}
			}
		}
	}

	new FusionSC_Portfolio();
}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_portfolio() {

	if ( ! function_exists( 'fusion_builder_map' ) || ! function_exists( 'fusion_builder_frontend_data' ) ) {
		return;
	}

	$fusion_settings = awb_get_fusion_settings();

	$builder_status = function_exists( 'is_fusion_editor' ) && is_fusion_editor();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Portfolio',
			[
				'name'       => esc_attr__( 'Portfolio', 'fusion-core' ),
				'shortcode'  => 'fusion_portfolio',
				'icon'       => 'fusiona-insertpicture',
				'preview'    => FUSION_CORE_PATH . '/shortcodes/previews/fusion-portfolio-preview.php',
				'preview_id' => 'fusion-builder-block-module-portfolio-preview-template',
				'front-end'  => FUSION_CORE_PATH . '/shortcodes/previews/front-end/fusion-portfolio.php',
				'help_url'   => 'https://theme-fusion.com/documentation/avada/elements/portfolio-element/',
				'params'     => [
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Layout', 'fusion-core' ),
						'description' => esc_attr__( 'Select the layout for the element.', 'fusion-core' ),
						'param_name'  => 'layout',
						'value'       => [
							'carousel' => esc_attr__( 'Carousel', 'fusion-core' ),
							'grid'     => esc_attr__( 'Grid', 'fusion-core' ),
							'masonry'  => esc_attr__( 'Masonry', 'fusion-core' ),
						],
						'default'     => 'carousel',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Picture Size', 'fusion-core' ),
						'description' => __( 'fixed = width and height will be fixed <br />auto = width and height will adjust to the image.', 'fusion-core' ),
						'param_name'  => 'picture_size',
						'value'       => [
							'default' => esc_attr__( 'Default', 'fusion-core' ),
							'fixed'   => esc_attr__( 'Fixed', 'fusion-core' ),
							'auto'    => esc_attr__( 'Auto', 'fusion-core' ),
						],
						'default'     => 'default',
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
						'heading'     => esc_attr__( 'Text Layout', 'fusion-core' ),
						'description' => esc_attr__( 'Controls if the portfolio text content is displayed boxed or unboxed or is completely disabled.', 'fusion-core' ),
						'param_name'  => 'text_layout',
						'value'       => [
							'default' => esc_attr__( 'Default', 'fusion-core' ),
							'no_text' => esc_attr__( 'No Text', 'fusion-core' ),
							'boxed'   => esc_attr__( 'Boxed', 'fusion-core' ),
							'unboxed' => esc_attr__( 'Unboxed', 'fusion-core' ),
						],
						'default'     => 'default',
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'carousel',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Grid Box Color', 'fusion-core' ),
						'description' => esc_attr__( 'Controls the background color for the grid boxes. For grid layout this option will only work in boxed mode.', 'fusion-core' ),
						'param_name'  => 'grid_box_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'timeline_bg_color' ),
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'carousel',
								'operator' => '!=',
							],
							[
								'element'  => 'text_layout',
								'value'    => 'no_text',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Grid Element Color', 'fusion-core' ),
						'description' => esc_attr__( 'Controls the color of borders of the grid boxes.', 'fusion-core' ),
						'param_name'  => 'grid_element_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'timeline_color' ),
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'carousel',
								'operator' => '!=',
							],
							[
								'element'  => 'text_layout',
								'value'    => 'unboxed',
								'operator' => '!=',
							],
							[
								'element'  => 'text_layout',
								'value'    => 'no_text',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Grid Separator Style', 'fusion-core' ),
						'description' => esc_attr__( 'Controls the line style of grid separators.', 'fusion-core' ),
						'param_name'  => 'grid_separator_style_type',
						'value'       => [
							''              => esc_attr__( 'Default', 'fusion-core' ),
							'none'          => esc_attr__( 'No Style', 'fusion-core' ),
							'single|solid'  => esc_attr__( 'Single Border Solid', 'fusion-core' ),
							'double|solid'  => esc_attr__( 'Double Border Solid', 'fusion-core' ),
							'single|dashed' => esc_attr__( 'Single Border Dashed', 'fusion-core' ),
							'double|dashed' => esc_attr__( 'Double Border Dashed', 'fusion-core' ),
							'single|dotted' => esc_attr__( 'Single Border Dotted', 'fusion-core' ),
							'double|dotted' => esc_attr__( 'Double Border Dotted', 'fusion-core' ),
							'shadow'        => esc_attr__( 'Shadow', 'fusion-core' ),
						],
						'default'     => '',
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'carousel',
								'operator' => '!=',
							],
							[
								'element'  => 'layout',
								'value'    => 'masonry',
								'operator' => '!=',
							],
							[
								'element'  => 'text_layout',
								'value'    => 'unboxed',
								'operator' => '!=',
							],
							[
								'element'  => 'text_layout',
								'value'    => 'no_text',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Grid Separator Color', 'fusion-core' ),
						'description' => esc_attr__( 'Controls the line style color of grid separators.', 'fusion-core' ),
						'param_name'  => 'grid_separator_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'grid_separator_color' ),
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'carousel',
								'operator' => '!=',
							],
							[
								'element'  => 'layout',
								'value'    => 'masonry',
								'operator' => '!=',
							],
							[
								'element'  => 'text_layout',
								'value'    => 'unboxed',
								'operator' => '!=',
							],
							[
								'element'  => 'text_layout',
								'value'    => 'no_text',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Number of Columns', 'fusion-core' ),
						'description' => __( 'Set the number of columns per row. With Carousel layout this specifies the maximum amount of columns. <strong>IMPORTANT:</strong> Masonry layout does not work with 1 column.', 'fusion-core' ),
						'param_name'  => 'columns',
						'value'       => '',
						'default'     => $fusion_settings->get( 'portfolio_columns' ),
						'min'         => '1',
						'max'         => '6',
						'step'        => '1',
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Column Spacing', 'fusion-core' ),
						'description' => esc_attr__( 'Controls the column spacing for portfolio items.', 'fusion-core' ),
						'param_name'  => 'column_spacing',
						'value'       => '',
						'min'         => '0',
						'max'         => '300',
						'step'        => '1',
						'default'     => $fusion_settings->get( 'portfolio_column_spacing' ),
						'dependency'  => [
							[
								'element'  => 'columns',
								'value'    => 1,
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Masonry Image Aspect Ratio', 'fusion-core' ),
						'description' => __( 'Set the ratio to decide when an image should become landscape (ratio being width : height) and portrait (ratio being height : width). <strong>IMPORTANT:</strong> The value of "1.0" represents a special case, which will use the auto calculated ratios like in versions prior to Avada 5.5.', 'fusion-core' ),
						'param_name'  => 'portfolio_masonry_grid_ratio',
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
						'heading'     => esc_attr__( 'Masonry 2x2 Width', 'fusion-core' ),
						'description' => __( 'This option decides when a square 1x1 image should become 2x2. This will not apply to images that highly favor landscape or portrait layouts. <strong>IMPORTANT:</strong> There is a “Masonry Image Layout” setting for every image in the WP media library that allows you to manually set how an image will appear (1x1, landscape, portrait or 2x2), regardless of the original ratio. In pixels.', 'fusion-core' ),
						'param_name'  => 'portfolio_masonry_width_double',
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
						'heading'     => esc_attr__( 'Content Position', 'fusion-core' ),
						'description' => __( 'Select if title, terms and excerpts should be displayed below or next to the featured images.', 'fusion-core' ),
						'param_name'  => 'one_column_text_position',
						'default'     => 'below',
						'value'       => [
							'below'   => esc_attr__( 'Below image', 'fusion-core' ),
							'floated' => esc_attr__( 'Next to Image', 'fusion-core' ),
						],
						'dependency'  => [
							[
								'element'  => 'columns',
								'value'    => '1',
								'operator' => '==',
							],
							[
								'element'  => 'layout',
								'value'    => 'grid',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Equal Heights', 'fusion-core' ),
						'description' => esc_attr__( 'Set to yes to display grid boxes with equal heights per row.', 'fusion-core' ),
						'param_name'  => 'equal_heights',
						'default'     => 'no',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-core' ),
							'no'  => esc_attr__( 'No', 'fusion-core' ),
						],
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'grid',
								'operator' => '==',
							],
							[
								'element'  => 'columns',
								'value'    => 1,
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Posts Per Page', 'fusion-core' ),
						'description' => esc_attr__( 'Select number of posts per page. Set to -1 to display all. Set to 0 to use number of posts from Settings > Reading.', 'fusion-core' ),
						'param_name'  => 'number_posts',
						'value'       => '',
						'min'         => '-1',
						'max'         => '25',
						'step'        => '1',
						'default'     => $fusion_settings->get( 'portfolio_items' ),
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_portfolio',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Portfolio Title Display', 'fusion-core' ),
						'description' => esc_attr__( 'Controls what displays with the portfolio post title.', 'fusion-core' ),
						'param_name'  => 'portfolio_title_display',
						'value'       => [
							'default' => esc_attr__( 'Default', 'fusion-core' ),
							'all'     => esc_attr__( 'Title and Categories', 'fusion-core' ),
							'title'   => esc_attr__( 'Only Title', 'fusion-core' ),
							'cats'    => esc_attr__( 'Only Categories', 'fusion-core' ),
							'none'    => esc_attr__( 'None', 'fusion-core' ),
						],
						'default'     => 'all',
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'carousel',
								'operator' => '!=',
							],
							[
								'element'  => 'text_layout',
								'value'    => 'no_text',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Portfolio Text Alignment', 'fusion-core' ),
						'description' => esc_attr__( 'Controls the alignment of the portfolio title, categories and excerpt text when using the Portfolio Text layouts.', 'fusion-core' ),
						'param_name'  => 'portfolio_text_alignment',
						'value'       => [
							'default' => esc_attr__( 'Default', 'fusion-core' ),
							'left'    => esc_attr__( 'Left', 'fusion-core' ),
							'center'  => esc_attr__( 'Center', 'fusion-core' ),
							'right'   => esc_attr__( 'Right', 'fusion-core' ),
						],
						'default'     => 'default',
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'carousel',
								'operator' => '!=',
							],
							[
								'element'  => 'text_layout',
								'value'    => 'no_text',
								'operator' => '!=',
							],
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Portfolio Text Layout Padding ', 'fusion-core' ),
						'description'      => esc_attr__( 'Controls the padding for the portfolio text layout when using boxed mode. Enter values including any valid CSS unit, ex: 25px, 25px, 25px, 25px.', 'fusion-core' ),
						'param_name'       => 'portfolio_layout_padding',
						'value'            => [
							'padding_top'    => '',
							'padding_right'  => '',
							'padding_bottom' => '',
							'padding_left'   => '',
						],
						'dependency'       => [
							[
								'element'  => 'text_layout',
								'value'    => 'unboxed',
								'operator' => '!=',
							],
							[
								'element'  => 'text_layout',
								'value'    => 'no_text',
								'operator' => '!=',
							],
							[
								'element'  => 'layout',
								'value'    => 'carousel',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Filters', 'fusion-core' ),
						'description' => esc_attr__( 'Choose to show or hide the category filters.', 'fusion-core' ),
						'param_name'  => 'filters',
						'value'       => [
							'yes'             => esc_attr__( 'Yes', 'fusion-core' ),
							'yes-without-all' => __( 'Yes without "All"', 'fusion-core' ),
							'no'              => esc_attr__( 'No', 'fusion-core' ),
						],
						'default'     => 'yes',
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'carousel',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Pull Posts By', 'fusion-core' ),
						'description' => esc_attr__( 'Choose to show posts by category or tag.', 'fusion-core' ),
						'param_name'  => 'pull_by',
						'default'     => 'category',
						'value'       => [
							'category' => esc_attr__( 'Category', 'fusion-core' ),
							'tag'      => esc_attr__( 'Tag', 'fusion-core' ),
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_portfolio',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'multiple_select',
						'heading'     => esc_attr__( 'Categories', 'fusion-core' ),
						'placeholder' => esc_html__( 'Categories', 'fusion-core' ),
						'description' => esc_attr__( 'Select categories or leave blank for all.', 'fusion-core' ),
						'param_name'  => 'cat_slug',
						'value'       => $builder_status ? fusion_builder_shortcodes_categories( 'portfolio_category' ) : [],
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
							'action'   => 'get_fusion_portfolio',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'multiple_select',
						'heading'     => esc_attr__( 'Exclude Categories', 'fusion-core' ),
						'placeholder' => esc_html__( 'Categories', 'fusion-core' ),
						'description' => esc_attr__( 'Select categories to exclude.', 'fusion-core' ),
						'param_name'  => 'exclude_cats',
						'value'       => $builder_status ? fusion_builder_shortcodes_categories( 'portfolio_category' ) : [],
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
							'action'   => 'get_fusion_portfolio',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'multiple_select',
						'heading'     => esc_attr__( 'Tags', 'fusion-core' ),
						'placeholder' => esc_html__( 'Tags', 'fusion-core' ),
						'description' => esc_attr__( 'Select a tag or leave blank for all.', 'fusion-core' ),
						'param_name'  => 'tag_slug',
						'value'       => ( $builder_status && function_exists( 'fusion_builder_shortcodes_tags' ) ) ? fusion_builder_shortcodes_tags( 'portfolio_tags' ) : [],
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
							'action'   => 'get_fusion_portfolio',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'multiple_select',
						'heading'     => esc_attr__( 'Exclude Tags', 'fusion-core' ),
						'placeholder' => esc_html__( 'Tags', 'fusion-core' ),
						'description' => esc_attr__( 'Select a tag to exclude.', 'fusion-core' ),
						'param_name'  => 'exclude_tags',
						'value'       => ( $builder_status && function_exists( 'fusion_builder_shortcodes_tags' ) ) ? fusion_builder_shortcodes_tags( 'portfolio_tags' ) : [],
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
							'action'   => 'get_fusion_portfolio',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Pagination Type', 'fusion-core' ),
						'description' => esc_attr__( 'Choose the type of pagination.', 'fusion-core' ),
						'param_name'  => 'pagination_type',
						'default'     => 'default',
						'value'       => [
							'default'          => esc_attr__( 'Default', 'fusion-core' ),
							'pagination'       => esc_attr__( 'Pagination', 'fusion-core' ),
							'infinite'         => esc_attr__( 'Infinite Scrolling', 'fusion-core' ),
							'load-more-button' => esc_attr__( 'Load More Button', 'fusion-core' ),
							'none'             => esc_attr__( 'None', 'fusion-core' ),
						],
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'carousel',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Hide URL Parameter', 'fusion-core' ),
						'description' => esc_attr__( 'Turn on to remove portfolio category parameters in single post URLs. These are mainly used for single item pagination within selected categories.', 'fusion-core' ),
						'param_name'  => 'hide_url_params',
						'default'     => 'off',
						'value'       => [
							'on'  => esc_attr__( 'On', 'fusion-core' ),
							'off' => esc_attr__( 'Off', 'fusion-core' ),
						],
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'carousel',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Post Offset', 'fusion-core' ),
						'description' => esc_attr__( 'The number of posts to skip. ex: 1.', 'fusion-core' ),
						'param_name'  => 'offset',
						'value'       => '0',
						'min'         => '0',
						'max'         => '25',
						'step'        => '1',
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_portfolio',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Order By', 'fusion-core' ),
						'description' => esc_attr__( 'Defines how portfolios should be ordered.', 'fusion-core' ),
						'param_name'  => 'orderby',
						'default'     => 'date',
						'value'       => [
							'date'          => esc_attr__( 'Date', 'fusion-core' ),
							'title'         => esc_attr__( 'Post Title', 'fusion-core' ),
							'menu_order'    => esc_attr__( 'Portfolio Order', 'fusion-core' ),
							'name'          => esc_attr__( 'Post Slug', 'fusion-core' ),
							'author'        => esc_attr__( 'Author', 'fusion-core' ),
							'comment_count' => esc_attr__( 'Number of Comments', 'fusion-core' ),
							'modified'      => esc_attr__( 'Last Modified', 'fusion-core' ),
							'rand'          => esc_attr__( 'Random', 'fusion-core' ),
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_portfolio',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Order', 'fusion-core' ),
						'description' => esc_attr__( 'Defines the sorting order of portfolios.', 'fusion-core' ),
						'param_name'  => 'order',
						'default'     => 'DESC',
						'value'       => [
							'DESC' => esc_attr__( 'Descending', 'fusion-core' ),
							'ASC'  => esc_attr__( 'Ascending', 'fusion-core' ),
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
							'action'   => 'get_fusion_portfolio',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Text Display', 'fusion-core' ),
						'description' => esc_attr__( 'Choose how to display the post excerpt.', 'fusion-core' ),
						'param_name'  => 'content_length',
						'value'       => [
							'default'      => esc_attr__( 'Default', 'fusion-core' ),
							'full_content' => esc_attr__( 'Full Content', 'fusion-core' ),
							'excerpt'      => esc_attr__( 'Excerpt', 'fusion-core' ),
							'no_text'      => esc_attr__( 'No Text', 'fusion-core' ),
						],
						'default'     => 'excerpt',
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'carousel',
								'operator' => '!=',
							],
							[
								'element'  => 'text_layout',
								'value'    => 'no_text',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Excerpt Length', 'fusion-core' ),
						'description' => esc_attr__( 'Insert the number of words/characters you want to show in the excerpt.', 'fusion-core' ),
						'param_name'  => 'excerpt_length',
						'value'       => '',
						'min'         => '0',
						'max'         => '500',
						'step'        => '1',
						'default'     => $fusion_settings->get( 'portfolio_excerpt_length' ),
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'carousel',
								'operator' => '!=',
							],
							[
								'element'  => 'text_layout',
								'value'    => 'no_text',
								'operator' => '!=',
							],
							[
								'element'  => 'content_length',
								'value'    => 'full_content',
								'operator' => '!=',
							],
						],
						[
							'element'  => 'text_layout',
							'value'    => 'no_text',
							'operator' => '!=',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Strip HTML', 'fusion-core' ),
						'description' => esc_attr__( 'Strip HTML from the post excerpt.', 'fusion-core' ),
						'param_name'  => 'strip_html',
						'value'       => [
							'default' => esc_attr__( 'Default', 'fusion-core' ),
							'yes'     => esc_attr__( 'Yes', 'fusion-core' ),
							'no'      => esc_attr__( 'No', 'fusion-core' ),
						],
						'default'     => 'yes',
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'carousel',
								'operator' => '!=',
							],
							[
								'element'  => 'text_layout',
								'value'    => 'no_text',
								'operator' => '!=',
							],
							[
								'element'  => 'content_length',
								'value'    => 'no_text',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Carousel Layout', 'fusion-core' ),
						'description' => esc_attr__( 'Choose to show titles on rollover image, or below image.', 'fusion-core' ),
						'param_name'  => 'carousel_layout',
						'value'       => [
							'title_below_image' => esc_attr__( 'Title below image', 'fusion-core' ),
							'title_on_rollover' => esc_attr__( 'Title on rollover', 'fusion-core' ),
						],
						'default'     => 'title_on_rollover',
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'carousel',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Carousel Scroll Items', 'fusion-core' ),
						'description' => esc_attr__( 'Insert the amount of items to scroll. Leave empty to scroll number of visible items.', 'fusion-core' ),
						'param_name'  => 'scroll_items',
						'value'       => '',
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'carousel',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Carousel Autoplay', 'fusion-core' ),
						'description' => esc_attr__( 'Choose to autoplay the carousel.', 'fusion-core' ),
						'param_name'  => 'autoplay',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-core' ),
							'no'  => esc_attr__( 'No', 'fusion-core' ),
						],
						'default'     => 'no',
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'carousel',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Carousel Show Navigation', 'fusion-core' ),
						'description' => esc_attr__( 'Choose to show navigation buttons on the carousel.', 'fusion-core' ),
						'param_name'  => 'show_nav',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-core' ),
							'no'  => esc_attr__( 'No', 'fusion-core' ),
						],
						'default'     => 'yes',
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'carousel',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Carousel Mouse Scroll', 'fusion-core' ),
						'description' => esc_attr__( 'Choose to enable mouse drag control on the carousel.', 'fusion-core' ),
						'param_name'  => 'mouse_scroll',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-core' ),
							'no'  => esc_attr__( 'No', 'fusion-core' ),
						],
						'default'     => 'no',
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'carousel',
								'operator' => '==',
							],
						],
					],
					'fusion_animation_placeholder' => [
						'preview_selector' => '.fusion-portfolio',
					],
					'fusion_margin_placeholder'    => [
						'param_name' => 'margin',
						'group'      => esc_attr__( 'General', 'fusion-core' ),
						'value'      => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
						],
					],
					[
						'type'        => 'checkbox_button_set',
						'heading'     => esc_attr__( 'Element Visibility', 'fusion-core' ),
						'param_name'  => 'hide_on_mobile',
						'value'       => fusion_builder_visibility_options( 'full' ),
						'default'     => fusion_builder_default_visibility( 'array' ),
						'description' => esc_attr__( 'Choose to show or hide the element on small, medium or large screens. You can choose more than one at a time.', 'fusion-core' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS Class', 'fusion-core' ),
						'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-core' ),
						'param_name'  => 'class',
						'value'       => '',
						'group'       => esc_attr__( 'General', 'fusion-core' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS ID', 'fusion-core' ),
						'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-core' ),
						'param_name'  => 'id',
						'value'       => '',
						'group'       => esc_attr__( 'General', 'fusion-core' ),
					],
				],
				'callback'   => [
					'function' => 'fusion_ajax',
					'action'   => 'get_fusion_portfolio',
					'ajax'     => true,
				],
			]
		)
	);
}
add_action( 'wp_loaded', 'fusion_element_portfolio' );
