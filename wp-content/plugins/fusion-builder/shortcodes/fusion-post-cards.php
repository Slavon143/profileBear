<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.3
 */

if ( fusion_is_element_enabled( 'fusion_post_cards' ) ) {

	if ( ! class_exists( 'FusionSC_PostCards' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.3
		 */
		class FusionSC_PostCards extends Fusion_Element {
			/**
			 * The one, true instance of this object.
			 *
			 * @static
			 * @access private
			 * @since 3.3
			 * @var object
			 */
			private static $instance;

			/**
			 * The counter.
			 *
			 * @access private
			 * @since 3.3
			 * @var int
			 */
			private $element_counter = 1;

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 3.3
			 * @var array
			 */
			protected $args;

			/**
			 * Shortcode name.
			 *
			 * @access public
			 * @since 3.3
			 * @var string
			 */
			public $shortcode_name;

			/**
			 * Post types.
			 *
			 * @access public
			 * @since 3.3
			 * @var mixed
			 */
			public $post_types = null;

			/**
			 * Supported taxonomies.
			 *
			 * @access public
			 * @since 3.3
			 * @var mixed
			 */
			public $taxonomies = null;

			/**
			 * Map taxonomies to post types.
			 *
			 * @access public
			 * @since 3.3
			 * @var mixed
			 */
			public $taxonomy_map = [];

			/**
			 * The term ID, in case post cards are used to display terms.
			 *
			 * @access public
			 * @since 3.5
			 * @var string
			 */
			public $term_id = '';

			/**
			 * Whether we are requesting from editor.
			 *
			 * @access protected
			 * @since 3.3
			 * @var array
			 */
			protected $live_request = false;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				$this->shortcode_name = 'fusion_post_cards';
				add_filter( 'fusion_attr_post-cards-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_post-cards-shortcode-pagination', [ $this, 'attr_pagination' ] );
				add_filter( 'fusion_attr_post-cards-shortcode-posts', [ $this, 'attr_posts' ] );
				add_filter( 'fusion_attr_post-cards-shortcode-filter-link', [ $this, 'filter_link_attr' ] );

				add_shortcode( $this->shortcode_name, [ $this, 'render' ] );

				// Ajax mechanism for query related part.
				add_action( 'wp_ajax_get_fusion_post_cards', [ $this, 'ajax_query' ] );

				add_action( 'pre_get_posts', [ $this, 'alter_shop_loop' ], 20 );
			}

			/**
			 * Creates or returns an instance of this class.
			 *
			 * @static
			 * @access public
			 * @since 3.3
			 */
			public static function get_instance() {

				// If an instance hasn't been created and set to $instance create an instance and set it to $instance.
				if ( null === self::$instance ) {
					self::$instance = new FusionSC_PostCards();
				}
				return self::$instance;
			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 3.3
			 * @return array
			 */
			public static function get_element_defaults() {
				$fusion_settings = awb_get_fusion_settings();
				return [
					'animation_direction'              => 'left',
					'animation_offset'                 => $fusion_settings->get( 'animation_offset' ),
					'animation_speed'                  => '',
					'animation_type'                   => '',
					'animation_delay'                  => 0,
					'hide_on_mobile'                   => fusion_builder_default_visibility( 'string' ),
					'class'                            => '',
					'id'                               => '',
					'fusion_font_family_filters_font'  => '',
					'fusion_font_variant_filters_font' => '',
					'filters_font_size'                => '',
					'filters_text_transform'           => 'none',
					'filters_line_height'              => '',
					'filters_letter_spacing'           => '',
					'filters_color'                    => $fusion_settings->get( 'link_color' ),
					'filters_height'                   => '',
					'filters_border_top'               => '1px',
					'filters_border_right'             => '0px',
					'filters_border_bottom'            => '1px',
					'filters_border_left'              => '0px',
					'filters_border_color'             => $fusion_settings->get( 'sep_color' ),
					'filters_alignment'                => 'flex-start',
					'filters_alignment_medium'         => '',
					'filters_alignment_small'          => '',
					'filters_hover_color'              => $fusion_settings->get( 'primary_color' ),
					'filters_active_color'             => $fusion_settings->get( 'primary_color' ),
					'active_filter_border_size'        => '',
					'active_filter_border_color'       => $fusion_settings->get( 'primary_color' ),
					'columns'                          => '4',
					'columns_medium'                   => '0',
					'columns_small'                    => '0',
					'column_spacing'                   => '40',
					'row_spacing'                      => '40',
					'layout'                           => 'grid',
					'margin_bottom'                    => '',
					'margin_left'                      => '',
					'margin_right'                     => '',
					'margin_top'                       => '',
					'filters'                          => 'no',
					'number_posts'                     => '0',
					'offset'                           => '',
					'order'                            => 'DESC',
					'orderby'                          => 'date',
					'orderby_term'                     => 'name',
					'upcoming_events_only'             => 'yes',
					'featured_events_only'             => 'no',
					'post_card'                        => '0',
					'post_card_archives'               => false,
					'post_card_list_view'              => '0',
					'post_type'                        => 'post',
					'posts_by'                         => 'all',
					'scrolling'                        => 'pagination',
					'source'                           => 'posts',
					'terms_by'                         => '',
					'flex_align_items'                 => 'flex-start',
					'out_of_stock'                     => 'include',
					'show_hidden'                      => 'no',
					'custom_field_name'                => '',
					'custom_field_comparison'          => 'exists',
					'custom_field_value'               => '',

					// Load More button.
					'load_more_btn_color'              => '',
					'load_more_btn_bg_color'           => '',
					'load_more_btn_hover_color'        => '',
					'load_more_btn_hover_bg_color'     => '',

					// Carousel.
					'scroll_items'                     => '',
					'mouse_scroll'                     => 'no',
					'autoplay'                         => 'no',
					'show_nav'                         => 'yes',

					// Slider.
					'slider_animation'                 => 'fade',
					'nav_margin_top'                   => '40px',

					// Separator styles.
					'separator_style_type'             => 'none',
					'separator_sep_color'              => '',
					'separator_width'                  => '',
					'separator_alignment'              => '',
					'separator_border_size'            => '',
				];
			}

			/**
			 * Used to set any other variables for use on front-end editor template.
			 *
			 * @static
			 * @access public
			 * @since 3.3
			 * @return array
			 */
			public static function get_element_extras() {
				$fusion_settings = awb_get_fusion_settings();
				$is_builder      = ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() );

				$extras = [
					'load_more_text'                    => apply_filters( 'avada_load_more_item_name', esc_attr__( 'Load More Items', 'fusion-builder' ) ),
					'pagination_global'                 => apply_filters( 'fusion_builder_blog_pagination', '' ),
					'pagination_range_global'           => apply_filters( 'fusion_pagination_size', $fusion_settings->get( 'pagination_range' ) ),
					'pagination_start_end_range_global' => apply_filters( 'fusion_pagination_start_end_size', $fusion_settings->get( 'pagination_start_end_range' ) ),
					'visibility_medium'                 => $fusion_settings->get( 'visibility_medium' ),
					'visibility_small'                  => $fusion_settings->get( 'visibility_small' ),
				];

				if ( $is_builder ) {
					$post_types = fusion_post_cards()->fetch_post_types();
					if ( is_array( $post_types ) ) {
						foreach ( $post_types as $post_type ) {
							/* translators: %s: "Post type label". */
							$extras[ 'load_more_text_' . $post_type->name ] = apply_filters( 'avada_load_more_' . $post_type->name . '_name', sprintf( esc_attr__( 'Load More %s', 'fusion-builder' ), $post_type->label ) );
						}
					}
				}
				return $extras;
			}

			/**
			 * Gets the query data.
			 *
			 * @static
			 * @access public
			 * @since 3.3
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
			 * @since 3.3
			 * @param array $defaults The default args.
			 * @return array|Object
			 */
			public function query( $defaults ) {
				// Return if there's a query override.
				$query_override = apply_filters( 'fusion_post_cards_shortcode_query_override', null, $defaults );

				if ( $query_override ) {
					return $query_override;
				}

				// From Ajax Request.
				if ( isset( $_POST['model'] ) && ! apply_filters( 'fusion_builder_live_request', false ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$defaults           = wp_unslash( $_POST['model']['params'] ); // phpcs:ignore WordPress.Security
					$return_data        = [];
					$this->live_request = true;
					add_filter( 'fusion_builder_live_request', '__return_true' );
				}

				$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
				if ( is_front_page() || is_home() ) {
					$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : ( ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1 );
				}
				$defaults['paged'] = $paged;

				if ( 'terms' === $defaults['source'] ) {
					$posts = $this->term_query( $defaults, $this->live_request );
				} else {
					$posts = $this->post_query( $defaults, $this->live_request );
				}

				if ( ! $this->live_request ) {
					return $posts;
				}
				$this->args = $defaults;

				// post_card_archives should be a boolean value.
				$this->args['post_card_archives'] = filter_var( $this->args['post_card_archives'], FILTER_VALIDATE_BOOLEAN );

				// Check for post card design choice.
				if ( 0 === (int) $defaults['post_card'] ) {
					$return_data['placeholder'] = $this->get_placeholder();
					echo wp_json_encode( $return_data );
					wp_die();
				}

				// Ensure ajax column CSS does not conflict.
				$cid = isset( $_POST['cid'] ) ? sanitize_text_field( wp_unslash( $_POST['cid'] ) ) : $defaults['post_card']; // phpcs:ignore WordPress.Security.NonceVerification.Missing
				FusionBuilder()->set_global_shortcode_parent( $cid );

				// Either we have terms or posts depending on what we want.
				$have_posts = ( 'terms' === $defaults['source'] && ! empty( $posts ) ) || ( in_array( $defaults['source'], [ 'posts', 'related', 'up_sells', 'cross_sells', 'featured_products' ], true ) && $posts->have_posts() );

				if ( ! $have_posts ) {
					$return_data['placeholder'] = $this->get_placeholder( 'empty' );
					echo wp_json_encode( $return_data );
					wp_die();
				}

				$return_data['paged']         = $paged;
				$return_data['max_num_pages'] = '';

				if ( $have_posts ) {

					if ( in_array( $this->args['source'], [ 'posts', 'related', 'up_sells', 'cross_sells', 'featured_products' ], true ) ) {
						ob_start();
						while ( $posts->have_posts() ) {
							$posts->the_post();

							$this->render_custom();
						}
						$return_data['loop_product']  = ob_get_clean();
						$return_data['max_num_pages'] = $posts->max_num_pages;
					} else {
						ob_start();
						foreach ( $posts as $term ) {
							$GLOBALS['wp_query']->is_tax               = true;
							$GLOBALS['wp_query']->is_archive           = true;
							$GLOBALS['wp_query']->is_post_type_archive = false;
							$GLOBALS['wp_query']->queried_object       = $term;
							$this->term_id                             = $term->term_taxonomy_id;

							$this->render_custom();
						}
						$return_data['loop_product'] = ob_get_clean();

						$this->term_id = '';
					}

					$return_data['filters'] = $this->post_cards_filters();
				}

				// Process children elements CSS.
				$return_data['nested_css'] = apply_filters( 'fusion_post_cards_elements_css', [] );

				echo wp_json_encode( $return_data );
				wp_die();
			}

			/**
			 * Get the terms.
			 *
			 * @static
			 * @access public
			 * @since 3.3
			 * @param array $defaults The default args.
			 * @param bool  $live_request Whether this is a live editor request.
			 * @return array|Object
			 */
			public function term_query( $defaults, $live_request = false ) {
				if ( '0' == $defaults['offset'] ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
					$defaults['offset'] = '';
				}
				$taxonomy = $defaults['terms_by'];
				$args     = [
					'taxonomy'   => $taxonomy,
					'number'     => max( (int) $defaults['number_posts'], 0 ),
					'hide_empty' => false,
					'orderby'    => $defaults['orderby_term'],
					'order'      => $defaults['order'],
				];

				if ( '' !== $defaults['offset'] ) {
					$args['offset'] = $defaults['offset'];
				}

				// Filter by taxonomy.
				foreach ( [ 'include', 'exclude' ] as $filter ) {
					$option = $filter . '_term_' . $taxonomy;
					$terms  = isset( $defaults[ $option ] ) && ! empty( $defaults[ $option ] ) ? $defaults[ $option ] : false;

					if ( $terms ) {
						if ( false !== strpos( $terms, ',' ) ) {
							$terms = explode( ',', $terms );
						} elseif ( false !== strpos( $terms, '|' ) ) {
							$terms = explode( '|', $terms );
						}
						$args[ $filter ] = $terms;
					}
				}

				$args['post_cards_query'] = true;
				$args                     = apply_filters( 'fusion_post_cards_shortcode_query_args', $args );

				return get_terms( $args );
			}

			/**
			 * Get single posts.
			 *
			 * @access public
			 * @since 3.3
			 * @param array $defaults The default args.
			 * @param bool  $live_request Whether this is a live editor request.
			 * @return array|Object
			 */
			public function post_query( $defaults, $live_request = false ) {
				global $avada_woocommerce;
				$fusion_settings = awb_get_fusion_settings();

				$is_builder  = ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() );
				$target_post = ( $is_builder || isset( $_GET['awb-studio-content'] ) ) && function_exists( 'Fusion_Template_Builder' ) ? Fusion_Template_Builder()->get_target_example() : false; // phpcs:ignore WordPress.Security
				$post_id     = $target_post ? $target_post->ID : get_the_ID();
				$post_id     = isset( $_POST['post_id'] ) ? $_POST['post_id'] : $post_id; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput, WordPress.Security.NonceVerification.Missing

				if ( '0' == $defaults['offset'] ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
					$defaults['offset'] = '';
				}

				$args = [
					'post_type'      => isset( $defaults['source'] ) && 'posts' === $defaults['source'] ? $defaults['post_type'] : get_post_type( $post_id ),
					'posts_per_page' => (int) $defaults['number_posts'],
					'paged'          => $defaults['paged'],
				];

				$args['orderby'] = $defaults['orderby'];
				$args['order']   = $defaults['order'];

				if ( 'product' === $defaults['post_type'] ) {
					$args['orderby'] = ( isset( $_GET['product_orderby'] ) ) ? sanitize_text_field( wp_unslash( $_GET['product_orderby'] ) ) : $defaults['orderby']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$args['order']   = ( isset( $_GET['product_order'] ) ) ? sanitize_text_field( wp_unslash( $_GET['product_order'] ) ) : $defaults['order']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

					if ( function_exists( 'WC' ) ) {
						remove_filter( 'woocommerce_get_catalog_ordering_args', [ $avada_woocommerce, 'get_catalog_ordering_args' ], 20 );
						$ordering_args = WC()->query->get_catalog_ordering_args( $args['orderby'], $args['order'] );
						add_filter( 'woocommerce_get_catalog_ordering_args', [ $avada_woocommerce, 'get_catalog_ordering_args' ], 20 );
						$args['orderby'] = $ordering_args['orderby'];
						$args['order']   = $ordering_args['order'];

						if ( $ordering_args['meta_key'] ) {
							$args['meta_key'] = $ordering_args['meta_key']; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
						}
					}
					$args['posts_per_page'] = '0' === $defaults['number_posts'] ? $fusion_settings->get( 'woo_items' ) : (int) $defaults['number_posts'];
					$args['posts_per_page'] = ( isset( $_GET['product_count'] ) ) ? (int) $_GET['product_count'] : $args['posts_per_page']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				}

				if ( '' !== $defaults['offset'] ) {
					$args['offset'] = $defaults['offset'];
				}

				// Filter by taxonomy or meta.
				if ( 'all' !== $defaults['posts_by'] ) {
					// Filter by meta.
					if ( 'posts' === $defaults['source'] && 'awb_custom_field' === $defaults['posts_by'] ) {
						if ( ! empty( $defaults['custom_field_name'] ) ) {
							$meta_query        = [];
							$meta_query['key'] = $defaults['custom_field_name'];

							if ( 'exists' === $defaults['custom_field_comparison'] ) {
								$meta_query['compare'] = 'EXISTS';
							} elseif ( 'not_exists' === $defaults['custom_field_comparison'] ) {
								$meta_query['compare'] = 'NOT EXISTS';
							} elseif ( 'equals' === $defaults['custom_field_comparison'] ) {
								$meta_query['compare'] = '=';
								$meta_query['value']   = $defaults['custom_field_value'];
							}

							$args['meta_query'] = [ $meta_query ]; // phpcs:ignore WordPress.DB.SlowDBQuery
						}
					} else { // Filter by taxonomy.
						$post_type_taxonomies = get_object_taxonomies( $defaults['post_type'], 'objects' );
						$taxonomy             = $defaults['posts_by'];

						// If taxonomy is used by post type, then lets filter for it.
						if ( isset( $post_type_taxonomies[ $taxonomy ] ) ) {
							foreach ( [ 'include', 'exclude' ] as $filter ) {
								$option = $filter . '_' . $taxonomy;
								$terms  = isset( $defaults[ $option ] ) && ! empty( $defaults[ $option ] ) ? $defaults[ $option ] : false;

								if ( $terms ) {
									if ( false !== strpos( $terms, ',' ) ) {
										$terms = explode( ',', $terms );
									} elseif ( false !== strpos( $terms, '|' ) ) {
										$terms = explode( '|', $terms );
									}

									$terms = is_array( $terms ) ? $terms : [ $terms ];

									$tax_args = [
										'taxonomy' => $taxonomy,
										'field'    => 'id',
										'terms'    => apply_filters( 'avada_element_term_selection', $terms, $defaults['post_type'], $taxonomy ),
									];

									if ( 'exclude' === $filter ) {
										$tax_args['operator'] = 'NOT IN';
									}
									$args['tax_query'][] = $tax_args;
								}
							}
						}
					}
				}

				// Product visibility option.
				if ( 'product' === $defaults['post_type'] && 'no' === $defaults['show_hidden'] ) {
					$args['tax_query']['relation'] = 'AND';
					$args['tax_query'][]           = [
						'taxonomy' => 'product_visibility',
						'field'    => 'slug',
						'terms'    => [ 'exclude-from-catalog', 'exclude-from-search' ],
						'operator' => 'NOT IN',
					];
				}

				// If out of stock are set not to show, hide them.
				if ( 'product' === $defaults['post_type'] && 'exclude' === $defaults['out_of_stock'] ) {
					$args['meta_query'][] = [
						'key'     => '_stock_status',
						'value'   => 'outofstock',
						'compare' => 'NOT IN',
					];
				}

				// Related items.
				if ( 'related' === $defaults['source'] ) {
					$terms = get_the_terms( $post_id, $defaults['terms_by'] );
					$terms = empty( $terms ) ? [] : $terms;
					$terms = wp_list_pluck( $terms, 'slug' );

					unset( $args['meta_query'] );

					$args['post__not_in'] = [ $post_id ];
					$args['tax_query']    = []; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					$args['tax_query'][]  = [
						'taxonomy' => $defaults['terms_by'],
						'field'    => 'slug',
						'terms'    => $terms,
					];
				}

				// Cross sells && upsells.
				if ( in_array( $defaults['source'], [ 'cross_sells', 'up_sells' ], true ) && class_exists( 'WooCommerce' ) ) {
					$product    = wc_get_product( $post_id );
					$cart_sells = ( is_page() || is_cart() && is_object( WC()->cart ) ) ? WC()->cart->get_cross_sells() : [];
					$sells      = 'cross_sells' === $defaults['source'] && 0 < count( $cart_sells ) ? $cart_sells : [ -1 ];

					if ( 'product' === get_post_type( $post_id ) && $product ) {
						$sells = 'cross_sells' === $defaults['source'] ? $product->get_cross_sell_ids() : $product->get_upsell_ids();
						$sells = 0 < count( $sells ) ? $sells : [ -1 ];
					}

					unset( $args['meta_query'] );
					unset( $args['tax_query'] );

					$args['post_type']    = 'product';
					$args['post__not_in'] = [ $post_id ];
					$args['post__in']     = $sells;
				}

				// Featured Products.
				if ( 'featured_products' === $defaults['source'] && class_exists( 'WooCommerce' ) ) {
					unset( $args['meta_query'] );
					unset( $args['tax_query'] );

					$args['post_type']   = 'product';
					$args['tax_query'][] = [
						'taxonomy' => 'product_visibility',
						'field'    => 'name',
						'terms'    => 'featured',
						'operator' => 'IN',
					];
				}

				if ( 'posts' === $defaults['source'] && 'tribe_events' === $defaults['post_type'] ) {
					if ( 'yes' === $defaults['upcoming_events_only'] ) {
						$args['ends_after'] = 'now';
					}

					if ( 'yes' === $defaults['featured_events_only'] ) {
						$args['featured'] = true;
					}
				}

				// Ajax returns protected posts, but we just want published.
				if ( $live_request ) {
					$args['post_status'] = 'publish';
				}

				$args['post_cards_query'] = true;

				if ( 'posts' === $defaults['source'] && 'tribe_events' === $defaults['post_type'] && function_exists( 'tribe_get_events' ) ) {
					$query = tribe_get_events( apply_filters( 'fusion_post_cards_shortcode_query_args', $args ), true );
				} else {
					$query = fusion_cached_query( apply_filters( 'fusion_post_cards_shortcode_query_args', $args ) );
				}

				if ( 'product' === $defaults['post_type'] ) {
					fusion_library()->woocommerce->remove_post_clauses( $args['orderby'], $args['order'] );
				}

				return $query;
			}

			/**
			 * Render the shortcode.
			 *
			 * @access public
			 * @since  3.3
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output
			 */
			public function render( $args, $content = '' ) {
				$fusion_settings = awb_get_fusion_settings();
				$this->defaults  = self::get_element_defaults();

				// We need dynamic defaults for post type.
				if ( isset( $args['posts_by'] ) ) {
					$this->defaults[ 'include_' . $args['posts_by'] ] = '';
					$this->defaults[ 'exclude_' . $args['posts_by'] ] = '';
				}
				if ( isset( $args['terms_by'] ) ) {
					$this->defaults[ 'include_term_' . $args['terms_by'] ] = '';
					$this->defaults[ 'exclude_term_' . $args['terms_by'] ] = '';
				}
				$this->args = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_post_cards' );

				$this->validate_args();

				$html = '';

				if ( 0 === (int) $this->args['post_card'] ) {
					$this->element_counter++;
					return $this->get_placeholder();
				}

				$posts = $this->query( $this->args );

				$this->query = $posts;

				// Either we have terms or posts depending on what we want.
				$have_posts = ( 'terms' === $this->args['source'] && ! is_wp_error( $posts ) && ! empty( $posts ) ) || ( in_array( $this->args['source'], [ 'posts', 'related', 'up_sells', 'cross_sells', 'featured_products' ], true ) && $posts->have_posts() );

				if ( ! $have_posts ) {
					$this->element_counter++;
					return $this->get_placeholder( 'empty' );
				}

				$post_list = '';

				if ( $have_posts ) {

					// Backup global data.
					$original_post              = $GLOBALS['post'];
					$original_query             = $GLOBALS['wp_query'];
					$original_queried_object    = $GLOBALS['wp_query']->queried_object;
					$original_is_tax            = $GLOBALS['wp_query']->is_tax;
					$original_is_archive        = $GLOBALS['wp_query']->is_archive;
					$original_is_category       = $GLOBALS['wp_query']->is_category;
					$original_is_tag            = $GLOBALS['wp_query']->is_tag;
					$original_is_singular       = $GLOBALS['wp_query']->is_singular;
					$original_post_type_archive = $GLOBALS['wp_query']->is_post_type_archive;
					$original_is_search         = $GLOBALS['wp_query']->is_search;
					$original_is_404            = $GLOBALS['wp_query']->is_404;
					$original_is_author         = $GLOBALS['wp_query']->is_author;
					$original_is_date           = $GLOBALS['wp_query']->is_date;
					$original_is_day            = $GLOBALS['wp_query']->is_day;
					$original_is_month          = $GLOBALS['wp_query']->is_month;

					$post_list .= '<ul ' . FusionBuilder::attributes( 'post-cards-shortcode-posts' ) . '>';

					if ( 'terms' !== $this->args['source'] ) {
						$GLOBALS['wp_query']->is_tax               = false;
						$GLOBALS['wp_query']->is_archive           = false;
						$GLOBALS['wp_query']->is_category          = false;
						$GLOBALS['wp_query']->is_tag               = false;
						$GLOBALS['wp_query']->is_singular          = true;
						$GLOBALS['wp_query']->is_post_type_archive = false;
						$GLOBALS['wp_query']->is_search            = false;
						$GLOBALS['wp_query']->is_404               = false;
						$GLOBALS['wp_query']->is_author            = false;
						$GLOBALS['wp_query']->is_date              = false;
						$GLOBALS['wp_query']->is_day               = false;
						$GLOBALS['wp_query']->is_month             = false;

						ob_start();

						while ( $posts->have_posts() ) {
							$posts->the_post();
							$GLOBALS['post'] = get_post( get_the_ID() ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
							setup_postdata( $GLOBALS['post'] );
							$GLOBALS['wp_query']->queried_object = $GLOBALS['post'];

							$this->render_custom();
						}

						$post_list .= ob_get_clean();
					} else {
						$GLOBALS['wp_query']->is_tax               = true;
						$GLOBALS['wp_query']->is_archive           = true;
						$GLOBALS['wp_query']->is_post_type_archive = false;

						ob_start();

						foreach ( $posts as $term ) {
							$GLOBALS['wp_query']->queried_object = $term;
							$this->term_id                       = $term->term_taxonomy_id;

							$this->render_custom();
						}

						$post_list .= ob_get_clean();

						$this->term_id = '';
					}

					// Restore global data.
					$GLOBALS['post']                           = $original_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					$GLOBALS['wp_query']                       = $original_query; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					$GLOBALS['wp_query']->is_tax               = $original_is_tax; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					$GLOBALS['wp_query']->is_archive           = $original_is_archive; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					$GLOBALS['wp_query']->is_category          = $original_is_category; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					$GLOBALS['wp_query']->is_tag               = $original_is_tag; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					$GLOBALS['wp_query']->is_singular          = $original_is_singular; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					$GLOBALS['wp_query']->queried_object       = $original_queried_object; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					$GLOBALS['wp_query']->is_post_type_archive = $original_post_type_archive; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					$GLOBALS['wp_query']->is_search            = $original_is_search; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					$GLOBALS['wp_query']->is_404               = $original_is_404; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					$GLOBALS['wp_query']->is_author            = $original_is_author; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					$GLOBALS['wp_query']->is_date              = $original_is_date; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					$GLOBALS['wp_query']->is_day               = $original_is_day; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					$GLOBALS['wp_query']->is_month             = $original_is_month; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				}

				$post_list .= '</ul>';

				if ( 'carousel' === $this->args['layout'] ) {
					if ( 'yes' === $this->args['show_nav'] ) {
						$post_list .= awb_get_carousel_nav();
					}

					$post_list = '<div ' . FusionBuilder::attributes( 'fusion-carousel-positioner' ) . '>' . $post_list . '</div>';
				}

				if ( 'no' !== $this->args['scrolling'] && 'terms' !== $this->args['source'] && ( 'grid' === $this->args['layout'] || 'masonry' === $this->args['layout'] ) ) {
					$post_list .= $this->pagination( $this->query->max_num_pages, $fusion_settings->get( 'pagination_range' ), $this->query );
				}

				wp_reset_query(); // phpcs:ignore WordPress.WP.DiscouragedFunctions.wp_reset_query_wp_reset_query
				wp_reset_postdata();

				$html  = '<div ' . FusionBuilder::attributes( 'post-cards-shortcode' ) . '>';
				$html .= $this->post_cards_filters();
				$html .= $post_list;

				// If infinite scroll with "load more" button is used.
				if ( 'load_more_button' === $this->args['scrolling'] && 1 < $posts->max_num_pages && 'terms' !== $this->args['source'] && ( 'grid' === $this->args['layout'] || 'masonry' === $this->args['layout'] ) ) {
					$post_type_obj = get_post_type_object( $this->args['post_type'] );
					$html         .= '<button class="fusion-load-more-button fusion-product-button fusion-clearfix">';
					/* translators: The name. */
					$html .= apply_filters( 'avada_load_more_' . strtolower( $post_type_obj->labels->name ) . '_name', sprintf( esc_attr__( 'Load More %s', 'fusion-builder' ), $post_type_obj->labels->name ) );
					$html .= '</button>';
				}

				$html .= '</div>';
				$html .= $this->get_styles(); // Get custom styles.

				$this->element_counter++;

				$this->on_render();

				do_action( 'fusion_post_cards_rendered' );

				return apply_filters( 'fusion_element_post_cards_content', $html, $args );

			}

			/**
			 * Fires on render.
			 *
			 * @access protected
			 * @since 3.8.1
			 */
			protected function on_render() {
				if ( ! $this->has_rendered ) {
					$this->on_first_render();
					$this->has_rendered = true;

					if ( function_exists( 'wc_enqueue_js' ) && 'product' === $this->args['post_type'] && 'posts' === $this->args['source'] ) {
						wc_enqueue_js(
							"
							jQuery( '.fusion-post-cards-" . ( $this->element_counter - 1 ) . "' ).on( 'change', '.variation_id', function( e ) {
								if ( '' !== jQuery( this ).val() ) {
									jQuery( this ).parent().find( '.add_to_cart_button' ).attr( 'data-product_id', jQuery(this).val() );
								}
								if ( jQuery.trim( jQuery( e.currentTarget ).closest( 'form' ).find( '.avada-variation' ).text() ).length ) {
									jQuery( e.currentTarget ).closest( 'form' ).find( '.avada-variation' ).closest( 'tr' ).removeClass( 'awb-hide-element' );
								}
							} ).on( 'hide_variation', '.variations_form', function( e ) {
								jQuery( e.currentTarget ).find( '.add_to_cart_button' ).removeClass( 'wc-variation-is-unavailable' ).addClass( 'disabled wc-variation-selection-needed' );
								jQuery( e.currentTarget ).find( '.add_to_cart_button' ).attr( 'data-product_id', jQuery( e.currentTarget ).find( '[name=\"product_id\"]' ).val() );
								jQuery( e.currentTarget ).find( '.avada-variation' ).closest( 'tr' ).addClass( 'awb-hide-element' );
							} ).on( 'show_variation', '.variations_form', function( e ) {
								jQuery( e.currentTarget ).find( '.add_to_cart_button' ).removeClass( 'disabled wc-variation-selection-needed wc-variation-is-unavailable' );
							} );
							jQuery( document.body ).on( 'adding_to_cart', function(e, \$thisbutton, data ) {
								if ( \$thisbutton.is('.disabled') ) {
									e.preventDefault();
						
									if ( \$thisbutton.is('.wc-variation-is-unavailable') ) {
										window.alert( wc_add_to_cart_variation_params.i18n_unavailable_text );
									} else if ( \$thisbutton.is('.wc-variation-selection-needed') ) {
										window.alert( wc_add_to_cart_variation_params.i18n_make_a_selection_text );
									}
									return true;
								}
							} );
						"
						);
					}
				}
			}

			/**
			 * Change args to valid values based on other options.
			 *
			 * @access public
			 * @since 3.3
			 * @return void
			 */
			public function validate_args() {
				// Force layout for list view.
				if ( 'product' === $this->args['post_type'] && 'posts' === $this->args['source'] && 'list' === $this->get_product_view() ) {
					$this->args['layout']  = 'grid';
					$this->args['columns'] = 1;
				}

				if ( 1 === (int) $this->args['columns'] && ( 'grid' === $this->args['layout'] || 'masonry' === $this->args['layout'] ) ) {
					$this->args['column_spacing'] = '0';
				}

				// No delay offering for carousels and sliders.
				if ( 'grid' !== $this->args['layout'] ) {
					$this->args['animation_delay'] = 0;
				}
			}

			/**
			 * Render filters.
			 *
			 * @access public
			 * @since 3.8
			 * @return string
			 */
			public function post_cards_filters() {

				// Setup the filters, if enabled.
				$filter_wrapper = '';

				if ( 'no' !== $this->args['filters'] && ( 'grid' === $this->args['layout'] || 'masonry' === $this->args['layout'] ) && 'posts' === $this->args['source'] ) {
					$post_type_taxonomies = get_object_taxonomies( $this->args['post_type'], 'objects' );
					$taxonomy             = $this->args['posts_by'];
					$taxonomy             = isset( $post_type_taxonomies[ $taxonomy ] ) ? $taxonomy : array_key_first( $post_type_taxonomies );
					$included             = ! empty( $this->args[ 'include_' . $taxonomy ] ) ? explode( ',', $this->args[ 'include_' . $taxonomy ] ) : [];
					$excluded             = ! empty( $this->args[ 'exclude_' . $taxonomy ] ) ? explode( ',', $this->args[ 'exclude_' . $taxonomy ] ) : [];
					$first_filter         = true;

					// Get terms.
					$terms = get_terms( $taxonomy );
					$terms = is_array( $terms ) && 0 < count( $terms ) ? $terms : [];

					if ( 'yes-without-all' !== $this->args['filters'] ) {
						$filter       = '<li role="menuitem" ' . FusionBuilder::attributes( 'fusion-filter fusion-filter-all fusion-active' ) . '><a ' . FusionBuilder::attributes(
							'post-cards-shortcode-filter-link',
							[
								'data-filter' => '*',
							]
						) . '>' . esc_attr__( 'All', 'fusion-builder' ) . '</a></li>';
						$first_filter = false;
					}

					foreach ( $terms as $term ) {

						// Only display filters of non excluded terms.
						if ( ! in_array( $term->term_id, $excluded ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict

							// Check if terms have been chosen.
							if ( ! empty( $included ) ) {

								// Only display filters for explicitly included terms.
								if ( in_array( urldecode( $term->term_id ), $included, true ) ) {
									// Set the first terms filter to active, if the all filter isn't shown.
									$active_class = '';
									if ( $first_filter ) {
										$active_class = ' fusion-active';
										$first_filter = false;
									}

									$filter .= '<li role="menuitem" ' . FusionBuilder::attributes( 'fusion-filter fusion-hidden' . $active_class ) . '><a ' . FusionBuilder::attributes(
										'post-cards-shortcode-filter-link',
										[
											'data-filter' => '.' . urldecode( $term->slug ),
										]
									) . '>' . $term->name . '</a></li>';
								}
							} else {

								// Display all terms.
								// Set the first term filter to active, if the all filter isn't shown.
								$active_class = '';
								if ( $first_filter ) {
									$active_class = ' fusion-active';
									$first_filter = false;
								}

								$filter .= '<li role="menuitem" ' . FusionBuilder::attributes( 'fusion-filter fusion-hidden' . $active_class ) . '><a ' . FusionBuilder::attributes(
									'post-cards-shortcode-filter-link',
									[
										'data-filter' => '.' . urldecode( $term->slug ),
									]
								) . '>' . $term->name . '</a></li>';
							}
						}
					}

					$filter_wrapper  = '<div role="menubar">';
					$filter_wrapper .= '<ul ' . FusionBuilder::attributes( 'fusion-filters' ) . ' role="menu" aria-label="filters">' . $filter . '</ul>';
					$filter_wrapper .= '</div>';
				}

				return $filter_wrapper;
			}

			/**
			 * Render custom layout style.
			 *
			 * @access public
			 * @since 3.3
			 * @return void
			 */
			public function render_custom() {
				$post_card = $this->get_post_card_design();

				if ( $post_card ) {
					$separator                      = $this->live_request || function_exists( 'fusion_separator' ) && 'none' !== $this->args['separator_style_type'] && 'grid' === $this->args['layout'] && 1 === (int) $this->args['columns'];
					FusionBuilder()->post_card_data = [
						'is_rendering'          => true,
						'is_post_card_archives' => $this->args['post_card_archives'],
						'columns'               => $this->args['columns'],
						'column_spacing'        => $this->args['column_spacing'],
					];
					add_filter( 'fusion_dynamic_post_id', [ $this, 'nested_post_id' ], 10 );
					add_filter( 'fusion_attr_fusion-column', [ $this, 'column_attributes' ], 20 );
					add_filter( 'fusion_attr_fusion-column-wrapper', [ $this, 'column_wrapper_attributes' ], 20 );
					add_filter( 'fusion_column_tag', [ $this, 'column_tag' ], 20, 2 );

					if ( $separator ) {
						add_filter( 'fusion_column_before_close', [ $this, 'maybe_render_separator' ], 20, 2 );
					}

					Fusion_Template_Builder()->render_content( $post_card );

					remove_filter( 'fusion_dynamic_post_id', [ $this, 'nested_post_id' ], 10 );
					remove_filter( 'fusion_attr_fusion-column', [ $this, 'column_attributes' ], 20 );
					remove_filter( 'fusion_attr_fusion-column-wrapper', [ $this, 'column_wrapper_attributes' ], 20 );
					remove_filter( 'fusion_column_tag', [ $this, 'column_tag' ], 20 );

					if ( $separator ) {
						remove_filter( 'fusion_column_before_close', [ $this, 'maybe_render_separator' ], 20 );
					}
					FusionBuilder()->post_card_data = [
						'is_rendering'          => false,
						'is_post_card_archives' => false,
						'columns'               => 1,
						'column_spacing'        => 0,
					];

					do_action( 'fusion_post_card_rendered' );
				}
			}

			/**
			 * Render separator.
			 *
			 * @since 3.3
			 * @param string $output      The output.
			 * @param string $column_type The column type.
			 * @return string
			 */
			public function maybe_render_separator( $output, $column_type ) {
				if ( 'fusion_builder_column' !== $column_type ) {
					return $output;
				}

				$separator_args = [
					'style_type'  => $this->args['separator_style_type'],
					'sep_color'   => $this->args['separator_sep_color'],
					'width'       => $this->args['separator_width'],
					'alignment'   => $this->args['separator_alignment'],
					'border_size' => $this->args['separator_border_size'],
					'position'    => 'absolute',
				];
				$output        .= fusion_separator()->render( $separator_args );
				return $output;
			}

			/**
			 * Adds children elements CSS rules to <style> block
			 *
			 * @since 3.3
			 * @return void
			 */
			public function add_elements_css_properties() {

				$elements_css = apply_filters( 'fusion_post_cards_elements_css', [] );

				foreach ( $elements_css as $rules ) {
					foreach ( $rules as $rule ) {
						$this->add_css_property( $this->base_selector . ' ' . $rule['selector'], $rule['rule'], $rule['value'], $rule['important'] );
					}
				}
			}

			/**
			 * Return nested post ID for dynamic data.
			 *
			 * @access public
			 * @since 3.3
			 * @param int $post_id Post ID for dynamic data.
			 * @return int
			 */
			public function nested_post_id( $post_id ) {
				return $this->term_id ? $this->term_id . '-archive' : get_the_ID();
			}

			/**
			 * Get product view layout.
			 *
			 * @access public
			 * @since 3.3
			 * @return string
			 */
			public function get_product_view() {
				$product_view = 'grid';
				if ( function_exists( 'Avada' ) && isset( $_SERVER['QUERY_STRING'] ) ) {
					parse_str( sanitize_text_field( wp_unslash( $_SERVER['QUERY_STRING'] ) ), $params );
					$product_view = ( isset( $params['product_view'] ) ) ? $params['product_view'] : Avada()->settings->get( 'woocommerce_product_view' );
				}
				return $product_view;
			}

			/**
			 * Get post card design.
			 *
			 * @access public
			 * @since 3.3
			 * @return object
			 */
			public function get_post_card_design() {
				$post_card = get_post( (int) $this->args['post_card'] );

				if ( 'product' === $this->args['post_type'] && 'posts' === $this->args['source'] && 0 !== (int) $this->args['post_card_list_view'] ) {
					if ( 'list' === $this->get_product_view() ) {
						$post_card = get_post( (int) $this->args['post_card_list_view'] );
					}
				}
				return $post_card;
			}

			/**
			 * Column attributes within custom grid.
			 *
			 * @access public
			 * @since 3.3
			 * @param array $attr Column attributes.
			 * @return array
			 */
			public function column_attributes( $attr ) {

				// Column within content element within post card.
				if ( ! FusionBuilder()->post_card_data['is_rendering'] ) {
					return $attr;
				}

				// No need this class for carousel & slider.
				if ( 'grid' !== $this->args['layout'] && 'masonry' !== $this->args['layout'] ) {
					$attr['class'] = preg_replace( '/fusion-layout-column\s/', '', $attr['class'] );
				}

				$attr['class'] .= ' post-card';
				if ( 'grid' === $this->args['layout'] || 'masonry' === $this->args['layout'] ) {
					$attr['class'] .= ' fusion-grid-column fusion-post-cards-grid-column';
					$attr['class'] .= 'masonry' === $this->args['layout'] ? ' fusion-post-card-masonry' : '';

					if ( 'product' === $this->args['post_type'] && 'posts' === $this->args['source'] ) {
						$product_view   = $this->get_product_view();
						$attr['class'] .= ' product-' . $product_view . '-view';
					}

					// Data for filters.
					if ( 'no' !== $this->args['filters'] && 'posts' === $this->args['source'] ) {
						$post_type_taxonomies = get_object_taxonomies( $this->args['post_type'], 'objects' );
						$taxonomy             = $this->args['posts_by'];
						$taxonomy             = isset( $post_type_taxonomies[ $taxonomy ] ) ? $taxonomy : array_key_first( $post_type_taxonomies );
						$terms                = get_the_terms( get_the_ID(), $taxonomy );

						if ( $terms ) {
							foreach ( $terms as $terms ) {
								$attr['class'] .= ' ' . urldecode( $terms->slug );
							}
						}
					}
				} elseif ( 'carousel' === $this->args['layout'] ) {
					$attr['class'] .= ' fusion-carousel-item';
				}

				if ( 'product' === $this->args['post_type'] && 'posts' === $this->args['source'] ) {
					$attr['class'] .= ' product';
				}

				// Delayed animated, inherit animation from parent.
				if ( 'grid' === $this->args['layout'] ) {
					if ( $this->args['animation_type'] && ! empty( $this->args['animation_delay'] ) ) {
						$attr['class'] .= ' fusion-animated';

						// Animation, no delay, set full to column.
					} elseif ( $this->args['animation_type'] ) {
						$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
					}
				}

				return $attr;
			}

			/**
			 * Column attributes within custom grid.
			 *
			 * @access public
			 * @since 3.3
			 * @param array $attr Column attributes.
			 * @return array
			 */
			public function column_wrapper_attributes( $attr ) {
				if ( 'carousel' === $this->args['layout'] ) {
					$attr['class'] .= ' fusion-carousel-item-wrapper';
				}

				return $attr;
			}

			/**
			 * Column main tag.
			 *
			 * @access public
			 * @since 3.3
			 * @param string $tag Column HTML tag.
			 * @param array  $args Column attributes.
			 * @return string
			 */
			public function column_tag( $tag = 'div', $args = [] ) {

				// We remove straight after using.
				remove_filter( 'fusion_column_tag', [ $this, 'column_tag' ], 20 );
				return 'li';
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 3.3
			 * @return array
			 */
			public function attr() {

				$attr = fusion_builder_visibility_atts(
					$this->args['hide_on_mobile'],
					[
						'class' => 'fusion-post-cards fusion-post-cards-' . $this->element_counter,
					]
				);

				if ( $this->args['animation_type'] ) {

					// Grid and has delay, set parent args here, otherwise it will be on children.
					if ( 'grid' === $this->args['layout'] ) {
						if ( ! empty( $this->args['animation_delay'] ) ) {
							$attr                         = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr, true );
							$attr['data-animation-delay'] = $this->args['animation_delay'];
							$attr['class']               .= ' fusion-delayed-animation';
						}
					} else {

						// Not grid always no delay, add to parent.
						$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
					}
				}

				if ( 'slider' === $this->args['layout'] ) {
					$attr['class'] .= ' fusion-slider-sc fusion-flexslider-loading flexslider';

					$attr['data-slideshow_autoplay']    = 'no' === $this->args['autoplay'] ? 'false' : 'true';
					$attr['data-slideshow_animation']   = $this->args['slider_animation'];
					$attr['data-slideshow_control_nav'] = 'no' === $this->args['show_nav'] ? 'false' : 'true';
				} elseif ( 'carousel' === $this->args['layout'] ) {
					$attr['class'] .= ' fusion-carousel fusion-carousel-responsive';

					$attr['data-autoplay']      = $this->args['autoplay'];
					$attr['data-columns']       = $this->args['columns'];
					$attr['data-columnsmedium'] = $this->args['columns_medium'];
					$attr['data-columnssmall']  = $this->args['columns_small'];
					$attr['data-itemmargin']    = $this->args['column_spacing'];
					$attr['data-itemwidth']     = 180;
					$attr['data-touchscroll']   = $this->args['mouse_scroll'];
					$attr['data-imagesize']     = 'auto';
					$attr['data-scrollitems']   = $this->args['scroll_items'];
				} elseif ( ( 'grid' === $this->args['layout'] || 'masonry' === $this->args['layout'] ) && 'terms' !== $this->args['source'] ) {
					$attr['class'] .= ' fusion-grid-archive';
					$attr['class'] .= 'masonry' === $this->args['layout'] ? ' fusion-post-cards-masonry' : '';
				}

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
			 * @since 3.3
			 * @return array
			 */
			public function attr_pagination() {
				$attr = [
					'class' => 'fusion-woo-product-grid-pagination fusion-clearfix',
				];

				if ( $this->is_load_more() ) {
					$attr['class'] .= ' infinite-scroll infinite-scroll-hide';
				}

				return $attr;
			}

			/**
			 * Render the blog pagination.
			 *
			 * @access public
			 * @since 3.3
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

				return apply_filters( 'fusion_post_cards_pagination_html', $pagination_html, $max_pages, $range, $current_query, $blog_global_pagination );
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 3.3
			 * @return array
			 */
			public function attr_posts() {
				$attr = [
					'class' => '',
				];

				if ( 'grid' === $this->args['layout'] || 'masonry' === $this->args['layout'] ) {
					$attr['class'] .= 'fusion-grid fusion-grid-' . $this->args['columns'] . ' fusion-flex-align-items-' . $this->args['flex_align_items'] . ' fusion-' . $this->args['layout'] . '-posts-cards';
				} elseif ( 'slider' === $this->args['layout'] ) {
					$attr['class'] .= 'slides';
				} elseif ( 'carousel' === $this->args['layout'] ) {
					$attr['class'] .= 'fusion-carousel-holder';
				}

				if ( $this->is_load_more() ) {
					$attr['class'] .= ' fusion-grid-container-infinite';

					$attr['data-pages'] = $this->query->max_num_pages;
				}

				if ( 'load_more_button' === $this->args['scrolling'] ) {
					$attr['class'] .= ' fusion-grid-container-load-more';
				}

				return $attr;
			}

			/**
			 * Builds the filter-link attributes array.
			 *
			 * @access public
			 * @since 3.8
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
			 * Sets the necessary scripts.
			 *
			 * @access public
			 * @since 3.3
			 * @return void
			 */
			public function on_first_render() {
				// Skip if empty.
				if ( true !== apply_filters( 'avada_force_enqueue', false ) && ( null === $this->args || empty( $this->args ) ) ) {
					return;
				}

				// $this->args will be null on Avada Studio site, it precomiles CSS for all elements.
				if ( $this->args ) {
					$post_type_obj = get_post_type_object( $this->args['post_type'] );

					if ( is_null( $post_type_obj ) ) {
						return;
					}

					Fusion_Dynamic_JS::enqueue_script( 'fusion-carousel' );
					Fusion_Dynamic_JS::enqueue_script( 'fusion-flexslider' );

					if ( 'product' === $post_type_obj->name ) {
						if ( class_exists( 'Avada' ) && class_exists( 'WooCommerce' ) ) {
							global $avada_woocommerce;

							$js_folder_suffix = FUSION_BUILDER_DEV_MODE ? '/assets/js' : '/assets/min/js';
							$js_folder_url    = Avada::$template_dir_url . $js_folder_suffix;
							$js_folder_path   = Avada::$template_dir_path . $js_folder_suffix;
							$version          = Avada::get_theme_version();

							Fusion_Dynamic_JS::enqueue_script(
								'avada-woo-products',
								$js_folder_url . '/general/avada-woo-products.js',
								$js_folder_path . '/general/avada-woo-products.js',
								[ 'jquery', 'fusion-flexslider' ],
								$version,
								true
							);

							Fusion_Dynamic_JS::localize_script(
								'avada-woo-products',
								'avadaWooCommerceVars',
								$avada_woocommerce::get_avada_wc_vars()
							);
						}
					}

					Fusion_Dynamic_JS::enqueue_script(
						'fusion-js-' . $this->shortcode_name,
						FusionBuilder::$js_folder_url . '/general/fusion-post-cards.js',
						FusionBuilder::$js_folder_path . '/general/fusion-post-cards.js',
						[ 'jquery', 'isotope', 'packery', 'jquery-infinite-scroll' ],
						'3.2',
						true
					);

					$label = $post_type_obj ? strtolower( $post_type_obj->labels->name ) : esc_html__( 'posts', 'fusion-builder' );

					Fusion_Dynamic_JS::localize_script(
						'fusion-js-' . $this->shortcode_name,
						'fusionPostCardsVars',
						[
							/* translators: The name. */
							'infinite_text'         => '<em>' . sprintf( __( 'Loading the next set of %s...', 'fusion-builder' ), $label ) . '</em>',
							'infinite_finished_msg' => '<em>' . __( 'All items displayed.', 'fusion-builder' ) . '</em>',
							'lightbox_behavior'     => fusion_library()->get_option( 'lightbox_behavior' ) ? fusion_library()->get_option( 'lightbox_behavior' ) : false,
							'pagination_type'       => $this->args['scrolling'],
						]
					);
				} else {
					// Just load everything on Avada Studio site.
					Fusion_Dynamic_JS::enqueue_script( 'fusion-carousel' );
					Fusion_Dynamic_JS::enqueue_script( 'fusion-flexslider' );

					// Add Woo scripts just in case.
					if ( class_exists( 'Avada' ) && class_exists( 'WooCommerce' ) ) {
						global $avada_woocommerce;

						$js_folder_suffix = FUSION_BUILDER_DEV_MODE ? '/assets/js' : '/assets/min/js';
						$js_folder_url    = Avada::$template_dir_url . $js_folder_suffix;
						$js_folder_path   = Avada::$template_dir_path . $js_folder_suffix;
						$version          = Avada::get_theme_version();

						Fusion_Dynamic_JS::enqueue_script(
							'avada-woo-products',
							$js_folder_url . '/general/avada-woo-products.js',
							$js_folder_path . '/general/avada-woo-products.js',
							[ 'jquery', 'fusion-flexslider' ],
							$version,
							true
						);

						Fusion_Dynamic_JS::localize_script(
							'avada-woo-products',
							'avadaWooCommerceVars',
							$avada_woocommerce::get_avada_wc_vars()
						);
					}

					Fusion_Dynamic_JS::enqueue_script(
						'fusion-js-' . $this->shortcode_name,
						FusionBuilder::$js_folder_url . '/general/fusion-post-cards.js',
						FusionBuilder::$js_folder_path . '/general/fusion-post-cards.js',
						[ 'jquery', 'jquery-infinite-scroll' ],
						'3.2',
						true
					);

					$label = esc_html__( 'posts', 'fusion-builder' );
					Fusion_Dynamic_JS::localize_script(
						'fusion-js-' . $this->shortcode_name,
						'fusionPostCardsVars',
						[
							/* translators: The name. */
							'infinite_text'         => '<em>' . sprintf( __( 'Loading the next set of %s...', 'fusion-builder' ), $label ) . '</em>',
							'infinite_finished_msg' => '<em>' . __( 'All items displayed.', 'fusion-builder' ) . '</em>',
							'lightbox_behavior'     => fusion_library()->get_option( 'lightbox_behavior' ) ? fusion_library()->get_option( 'lightbox_behavior' ) : false,
							'pagination_type'       => 'pagination',
						]
					);
				}
			}

			/**
			 * Builds the dynamic styling.
			 *
			 * @access public
			 * @since 3.8
			 * @return array
			 */
			public function add_styling() {
				global $content_media_query;
				$css[ $content_media_query ]['.fusion-post-cards .fusion-filters']['display'] = 'block !important';

				return $css;
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.3
			 * @return void
			 */
			public function add_css_files() {

				// Post cards needs styling for product rollover.
				if ( class_exists( 'Avada' ) && class_exists( 'WooCommerce' ) ) {
					Fusion_Dynamic_CSS::enqueue_style( Avada::$template_dir_path . '/assets/css/dynamic/woocommerce/woo-products.min.css', Avada::$template_dir_url . '/assets/css/dynamic/woocommerce/woo-products.min.css' );
				}
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/grid.min.css' );
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/post-cards.min.css' );
			}

			/**
			 * Check if pagination is loadmore.
			 *
			 * @access public
			 * @since 3.3
			 * @return boolean
			 */
			public function is_load_more() {
				return in_array( $this->args['scrolling'], [ 'infinite', 'load_more_button' ], true ) && ( 'grid' === $this->args['layout'] || 'masonry' === $this->args['layout'] );
			}

			/**
			 * Get placeholder.
			 *
			 * @since 3.3
			 * @param  string $type placeholder type.
			 * @return string
			 */
			protected function get_placeholder( $type = 'card' ) {

				if ( ! current_user_can( 'manage_options' ) ) {
					$msg = '';
				} elseif ( 'card' === $type ) {
					$msg = sprintf( '<a href="%s" target="_blank" class="fusion-builder-placeholder">%s</a>', admin_url( 'admin.php?page=avada-library' ), esc_html__( 'Please select post card design to display here.', 'fusion-builder' ) );
				} else {
					$msg = in_array( $this->args['source'], [ 'posts', 'related', 'up_sells', 'cross_sells', 'featured_products' ], true ) ? esc_html__( 'No posts found.', 'fusion-builder' ) : esc_html__( 'No terms found.', 'fusion-builder' );
					$msg = sprintf( '<div class="fusion-builder-placeholder">%s</div>', $msg );
				}

				return apply_filters( 'awb_post_cards_placeholder_message', $msg, $type, $this->args );
			}

			/**
			 * Get reverse number.
			 *
			 * @access public
			 * @since 3.3
			 * @param string $value number value.
			 * @return string
			 */
			public function get_reverse_num( $value ) {
				return strpos( $value, '-' ) > 0 ? str_replace( '-', '', $value ) : '-' . $value;
			}

			/**
			 * Get the styles.
			 *
			 * @access protected
			 * @since 3.3
			 * @return string
			 */
			protected function get_styles() {
				$fusion_settings = awb_get_fusion_settings();

				$this->base_selector = '.fusion-post-cards.fusion-post-cards-' . $this->element_counter;
				$this->dynamic_css   = [];

				// Add filters styles if active.
				if ( 'no' !== $this->args['filters'] && ( 'grid' === $this->args['layout'] || 'masonry' === $this->args['layout'] ) && 'posts' === $this->args['source'] ) {

					$selectors = [
						$this->base_selector . ' ul.fusion-filters li a',
					];

					$text_styles = Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'filters_font', 'array' );

					foreach ( $text_styles as $rule => $value ) {
						$this->add_css_property( $selectors, $rule, $value );
					}

					if ( ! $this->is_default( 'filters_font_size' ) ) {
						$this->add_css_property( $selectors, 'font-size', fusion_library()->sanitize->get_value_with_unit( $this->args['filters_font_size'] ) );
					}

					if ( ! $this->is_default( 'filters_line_height' ) ) {
						$this->add_css_property( $selectors, 'line-height', $this->args['filters_line_height'] );
					}

					if ( ! $this->is_default( 'filters_letter_spacing' ) ) {
						$this->add_css_property( $selectors, 'letter-spacing', fusion_library()->sanitize->get_value_with_unit( $this->args['filters_letter_spacing'] ) );
					}

					if ( ! $this->is_default( 'filters_text_transform' ) ) {
						$this->add_css_property( $selectors, 'text-transform', $this->args['filters_text_transform'] );
					}

					if ( ! $this->is_default( 'filters_color' ) ) {
						$this->add_css_property( $selectors, 'color', fusion_library()->sanitize->color( $this->args['filters_color'] ) );
					}

					$selectors = [
						$this->base_selector . ' ul.fusion-filters li a:hover',
					];

					if ( '' !== $this->args['filters_hover_color'] ) {
						$this->add_css_property( $selectors, 'color', fusion_library()->sanitize->color( $this->args['filters_hover_color'] ) );
					}

					$selectors = [
						$this->base_selector . ' ul.fusion-filters li.fusion-active a',
					];

					if ( '' !== $this->args['filters_active_color'] ) {
						$this->add_css_property( $selectors, 'color', fusion_library()->sanitize->color( $this->args['filters_active_color'] ) );
					}

					if ( ! $this->is_default( 'active_filter_border_size' ) ) {
						$this->add_css_property( $selectors, 'border-top-width', fusion_library()->sanitize->get_value_with_unit( $this->args['active_filter_border_size'] ) );
					}

					if ( ! $this->is_default( 'active_filter_border_color' ) ) {
						$this->add_css_property( $selectors, 'border-color', fusion_library()->sanitize->color( $this->args['active_filter_border_color'] ) );
					}

					$selectors = [
						$this->base_selector . ' ul.fusion-filters',
					];

					if ( ! $this->is_default( 'filters_border_bottom' ) ) {
						$this->add_css_property( $selectors, 'border-bottom-width', fusion_library()->sanitize->get_value_with_unit( $this->args['filters_border_bottom'] ) );
					}

					if ( ! $this->is_default( 'filters_border_top' ) ) {
						$this->add_css_property( $selectors, 'border-top-width', fusion_library()->sanitize->get_value_with_unit( $this->args['filters_border_top'] ) );
					}

					if ( ! $this->is_default( 'filters_border_left' ) ) {
						$this->add_css_property( $selectors, 'border-left-width', fusion_library()->sanitize->get_value_with_unit( $this->args['filters_border_left'] ) );
						$this->add_css_property( $selectors, 'border-left-style', 'solid' );
					}

					if ( ! $this->is_default( 'filters_border_right' ) ) {
						$this->add_css_property( $selectors, 'border-right-width', fusion_library()->sanitize->get_value_with_unit( $this->args['filters_border_right'] ) );
						$this->add_css_property( $selectors, 'border-right-style', 'solid' );
					}

					if ( ! $this->is_default( 'filters_border_color' ) ) {
						$this->add_css_property( $selectors, 'border-color', fusion_library()->sanitize->color( $this->args['filters_border_color'] ) );
					}

					if ( ! $this->is_default( 'filters_height' ) ) {
						$this->add_css_property( $selectors, 'min-height', fusion_library()->sanitize->get_value_with_unit( $this->args['filters_height'] ) );
					}

					if ( ! $this->is_default( 'filters_alignment' ) ) {
						$this->add_css_property( $selectors, 'justify-content', $this->args['filters_alignment'] );
					}
				}

				$selectors = [
					$this->base_selector . ' .infinite-scroll-hide',
				];
				if ( $this->is_load_more() ) {
					$this->add_css_property( $selectors, 'display', 'none' );
				}
				if ( '1' !== $this->args['columns'] && '0' !== $this->args['column_spacing'] ) {
					$column_spacing = fusion_library()->sanitize->get_value_with_unit( $this->args['column_spacing'] );

					$selectors = [
						$this->base_selector . ' ul.fusion-grid',
					];
					$this->add_css_property( $selectors, 'margin-right', 'calc((' . $column_spacing . ')/ -2)' );
					$this->add_css_property( $selectors, 'margin-left', 'calc((' . $column_spacing . ')/ -2)' );

					$selectors = [
						$this->base_selector . ' ul.fusion-grid > .fusion-grid-column',
					];
					$this->add_css_property( $selectors, 'padding-left', 'calc((' . $column_spacing . ')/ 2)' );
					$this->add_css_property( $selectors, 'padding-right', 'calc((' . $column_spacing . ')/ 2)' );

					$selectors = [
						$this->base_selector . ' ul.fusion-grid > .fusion-grid-column > .fusion-column-inner-bg',
					];
					$this->add_css_property( $selectors, 'margin-left', 'calc((' . $column_spacing . ')/ 2)' );
					$this->add_css_property( $selectors, 'margin-right', 'calc((' . $column_spacing . ')/ 2)' );
				}

				if ( ( 'grid' === $this->args['layout'] || 'masonry' === $this->args['layout'] ) && '0' !== $this->args['row_spacing'] ) {
					$row_spacing = fusion_library()->sanitize->get_value_with_unit( $this->args['row_spacing'] );

					$selectors = [
						$this->base_selector . ' ul.fusion-grid',
					];
					$this->add_css_property( $selectors, 'margin-top', 'calc((' . $row_spacing . ')/ -2)' );

					$selectors = [
						$this->base_selector . ' ul.fusion-grid > .fusion-grid-column',
					];
					$this->add_css_property( $selectors, 'padding-top', 'calc((' . $row_spacing . ')/ 2)' );
					$this->add_css_property( $selectors, 'padding-bottom', 'calc((' . $row_spacing . ')/ 2)' );

					$selectors = [
						$this->base_selector . ' ul.fusion-grid > .fusion-grid-column > .fusion-column-inner-bg',
					];
					$this->add_css_property( $selectors, 'margin-top', 'calc((' . $row_spacing . ')/ 2)' );
					$this->add_css_property( $selectors, 'margin-bottom', 'calc((' . $row_spacing . ')/ 2)' );
				}

				// Margin styles.
				if ( ! $this->is_default( 'margin_top' ) ) {
					$this->add_css_property( $this->base_selector, 'margin-top', fusion_library()->sanitize->get_value_with_unit( $this->args['margin_top'] ) );
				}
				if ( ! $this->is_default( 'margin_right' ) ) {
					$this->add_css_property( $this->base_selector, 'margin-right', fusion_library()->sanitize->get_value_with_unit( $this->args['margin_right'] ) );
				}
				if ( ! $this->is_default( 'margin_bottom' ) ) {
					$this->add_css_property( $this->base_selector, 'margin-bottom', fusion_library()->sanitize->get_value_with_unit( $this->args['margin_bottom'] ) );
				}
				if ( ! $this->is_default( 'margin_left' ) ) {
					$this->add_css_property( $this->base_selector, 'margin-left', fusion_library()->sanitize->get_value_with_unit( $this->args['margin_left'] ) );
				}

				$selectors = [
					$this->base_selector . ' .flex-control-nav',
				];
				if ( 'slider' === $this->args['layout'] ) {
					$this->add_css_property( $selectors, 'bottom', fusion_library()->sanitize->get_value_with_unit( $this->get_reverse_num( $this->args['nav_margin_top'] ) ) );
				}

				if ( 'load_more_button' === $this->args['scrolling'] ) {
					$button_css_selector       = $this->base_selector . ' .fusion-load-more-button';
					$button_hover_css_selector = [
						$this->base_selector . ' .fusion-load-more-button:hover',
						$this->base_selector . ' .fusion-load-more-button:focus',
					];

					if ( ! empty( $this->args['load_more_btn_color'] ) ) {
						$this->add_css_property( $button_css_selector, 'color', fusion_library()->sanitize->color( $this->args['load_more_btn_color'] ) );
					}

					if ( ! empty( $this->args['load_more_btn_bg_color'] ) ) {
						$this->add_css_property( $button_css_selector, 'background-color', fusion_library()->sanitize->color( $this->args['load_more_btn_bg_color'] ) );
					}

					if ( ! empty( $this->args['load_more_btn_hover_color'] ) ) {
						$this->add_css_property( $button_hover_css_selector, 'color', fusion_library()->sanitize->color( $this->args['load_more_btn_hover_color'] ) );
					}

					if ( ! empty( $this->args['load_more_btn_hover_bg_color'] ) ) {
						$this->add_css_property( $button_hover_css_selector, 'background-color', fusion_library()->sanitize->color( $this->args['load_more_btn_hover_bg_color'] ) );
					}
				}

				// Process children elements CSS.
				$this->add_elements_css_properties();

				$css = $this->parse_css();

				if ( 'grid' === $this->args['layout'] || 'masonry' === $this->args['layout'] ) {
					$responsive_style = '';

					// Responsive options.
					foreach ( [ 'medium', 'small' ] as $responsive_size ) {
						$key = 'columns_' . $responsive_size;

						// Check for default value.
						if ( $this->is_default( $key ) ) {
							continue;
						}

						$this->dynamic_css = [];

						// Build responsive styles.
						$selectors = [
							$this->base_selector . ' .fusion-grid .fusion-grid-column',
						];
						$this->add_css_property( $selectors, 'width', $this->get_grid_width_val( $this->args[ $key ] ) . '!important' );

						$media             = sprintf( '@media only screen and (max-width:%spx)', $fusion_settings->get( 'visibility_' . $responsive_size ) );
						$responsive_style .= sprintf( '%s { %s }', $media, $this->parse_css() );
					}
					$css .= $responsive_style;
				}

				// Responsive Filters Alignment.
				if ( 'no' !== $this->args['filters'] && ( 'grid' === $this->args['layout'] || 'masonry' === $this->args['layout'] ) && 'posts' === $this->args['source'] ) {

					foreach ( [ 'medium', 'small' ] as $size ) {
						$key   = 'filters_alignment_' . $size;
						$media = sprintf( '@media only screen and (max-width:%spx)', $fusion_settings->get( 'visibility_' . $size ) );

						if ( '' === $this->args[ $key ] ) {
							continue;
						}

						$this->dynamic_css = [];
						$this->add_css_property( $this->base_selector . ' ul.fusion-filters', 'justify-content', $this->args[ $key ] );
						$css .= sprintf( '%s { %s }', $media, $this->parse_css() );
					}
				}

				return $css ? '<style>' . $css . '</style>' : '';
			}

			/**
			 * Apply post per page on shop page.
			 *
			 * @access public
			 * @since 3.3
			 * @param  object $query The WP_Query object.
			 * @return  void
			 */
			public function alter_shop_loop( $query ) {
				if ( ! is_admin() && $query->is_main_query() && ! $query->is_search && $query->is_post_type_archive( 'product' ) && 'no' === fusion_get_option( 'show_wc_shop_loop' ) ) {
					$search_override        = get_post( wc_get_page_id( 'shop' ) );
					$has_archives_component = $search_override && has_shortcode( $search_override->post_content, 'fusion_post_cards' );

					if ( $has_archives_component ) {
						$pattern = get_shortcode_regex( [ 'fusion_post_cards' ] );
						$content = $search_override->post_content;
						if ( preg_match_all( '/' . $pattern . '/s', $search_override->post_content, $matches )
							&& array_key_exists( 2, $matches )
							&& in_array( 'fusion_post_cards', $matches[2], true ) ) {
							$search_atts  = shortcode_parse_atts( $matches[3][0] );
							$number_posts = ( isset( $_GET['product_count'] ) ) ? (int) $_GET['product_count'] : $search_atts['number_posts']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
							$query->set( 'paged', ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1 );
							if ( '0' !== $number_posts ) {
								$query->set( 'posts_per_page', $number_posts );
							}
						}
					}
				}
			}

			/**
			 * Get grid width value.
			 *
			 * @access public
			 * @since 3.3
			 * @param string $columns Columns count.
			 * @return string
			 */
			public function get_grid_width_val( $columns ) {
				$cols = [
					'1' => '100%',
					'2' => '50%',
					'3' => '33.3333%',
					'4' => '25%',
					'5' => '20%',
					'6' => '16.6666%',
				];
				return $cols[ $columns ];
			}

			/**
			 * Fetch post types.
			 *
			 * @access protected
			 * @since 3.3
			 * @return mixed
			 */
			public function fetch_post_types() {
				if ( null !== $this->post_types ) {
					return $this->post_types;
				}

				$post_types = get_post_types( [ 'public' => true ], 'objects' );
				unset( $post_types['attachment'] );
				unset( $post_types['slide'] );
				unset( $post_types['page'] );
				$this->post_types = apply_filters( 'post_card_post_types', $post_types );
				return $this->post_types;
			}

			/**
			 * Fetch taxonomies.
			 *
			 * @access protected
			 * @since 3.3
			 * @return string
			 */
			public function fetch_taxonomies() {
				if ( null !== $this->taxonomies ) {
					return $this->taxonomies;
				}

				$post_types = $this->fetch_post_types();
				if ( is_array( $post_types ) ) {
					foreach ( $post_types as $post_type ) {
						$new_taxonomies = get_object_taxonomies( $post_type->name, 'objects' );
						foreach ( $new_taxonomies as $new_taxonomy ) {
							$post_taxonomies[ $new_taxonomy->name ] = $new_taxonomy;

							// Need each taxonomy for each post type.
							if ( ! isset( $this->taxonomy_map[ $post_type->name ] ) ) {
								$this->taxonomy_map[ $post_type->name ] = [ 'all' ];
							}
							$this->taxonomy_map[ $post_type->name ][] = $new_taxonomy->name;
						}

						$this->taxonomy_map[ $post_type->name ][] = 'awb_custom_field';
					}
				}

				unset( $post_taxonomies['post_format'] ); // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedUnsetVariable
				unset( $post_taxonomies['product_visibility'] ); // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedUnsetVariable
				$this->taxonomies = apply_filters( 'post_card_post_taxonomies', $post_taxonomies );
				return $this->taxonomies;
			}

			/**
			 * Fetch post type option select.
			 *
			 * @access protected
			 * @since 3.3
			 * @return string
			 */
			public function fetch_post_type_option() {
				$post_types        = $this->fetch_post_types();
				$post_type_options = [];
				if ( is_array( $post_types ) ) {
					foreach ( $post_types as $post_type ) {
						$post_type_options[ esc_attr( $post_type->name ) ] = esc_html( $post_type->label );
					}
				}

				return [
					'type'        => 'select',
					'heading'     => esc_attr__( 'Post Type', 'fusion-builder' ),
					'description' => esc_attr__( 'Select the post type to display.', 'fusion-builder' ),
					'param_name'  => 'post_type',
					'default'     => 'post',
					'value'       => $post_type_options,
					'dependency'  => [
						[
							'element'  => 'source',
							'value'    => 'posts',
							'operator' => '==',
						],
					],
					'callback'    => [
						'function' => 'fusion_ajax',
						'action'   => 'get_fusion_post_cards',
						'ajax'     => true,
					],
				];
			}

			/**
			 * Fetch post taxonomy option select.
			 *
			 * @access protected
			 * @since 3.3
			 * @return string
			 */
			public function fetch_post_terms_option() {
				$taxonomies       = $this->fetch_taxonomies();
				$taxonomy_options = [];

				if ( is_array( $taxonomies ) ) {
					foreach ( $taxonomies as $taxonomy ) {
						$taxonomy_options[ $taxonomy->name ] = ucwords( esc_html( $taxonomy->label ) );
					}
				}

				return [
					'type'        => 'select',
					'heading'     => esc_html__( 'Taxonomy', 'fusion-builder' ),
					'description' => esc_html__( 'Select which taxonomy to use.', 'fusion-builder' ),
					'param_name'  => 'terms_by',
					'default'     => '',
					'value'       => $taxonomy_options,
					'dependency'  => [
						[
							'element'  => 'source',
							'value'    => 'posts',
							'operator' => '!=',
						],
						[
							'element'  => 'source',
							'value'    => 'up_sells',
							'operator' => '!=',
						],
						[
							'element'  => 'source',
							'value'    => 'cross_sells',
							'operator' => '!=',
						],
						[
							'element'  => 'source',
							'value'    => 'featured_products',
							'operator' => '!=',
						],
					],
					'callback'    => [
						'function' => 'fusion_ajax',
						'action'   => 'get_fusion_post_cards',
						'ajax'     => true,
					],
				];
			}

			/**
			 * Fetch post type specific filter options.
			 *
			 * @access protected
			 * @since 3.3
			 * @return string
			 */
			public function fetch_post_filter_option() {
				$taxonomies       = $this->fetch_taxonomies();
				$taxonomy_options = [
					'all' => esc_html__( 'All', 'fusion-builder' ),
				];

				if ( empty( $taxonomies ) ) {
					return [];
				}

				// There ae some, add each to select field if they are accepted.
				foreach ( $taxonomies as $taxonomy ) {
					$taxonomy_options[ $taxonomy->name ] = ucwords( esc_html( $taxonomy->label ) );
				}

				$taxonomy_options['awb_custom_field'] = esc_html__( 'Custom Field', 'fusion-builder' );

				return [
					'type'        => 'select',
					'heading'     => esc_html__( 'Posts By', 'fusion-builder' ),
					'description' => esc_html__( 'Select which taxonomy to pull posts from or select all to pull all.', 'fusion-builder' ),
					'param_name'  => 'posts_by',
					'default'     => 'all',
					'value'       => $taxonomy_options,
					'conditions'  => [
						'option' => 'post_type',
						'map'    => $this->taxonomy_map,
					],
					'dependency'  => [
						[
							'element'  => 'source',
							'value'    => 'posts',
							'operator' => '==',
						],
					],
					'callback'    => [
						'function' => 'fusion_ajax',
						'action'   => 'get_fusion_post_cards',
						'ajax'     => true,
					],
				];
			}

			/**
			 * Fetch taxonomy options.
			 *
			 * @access protected
			 * @since 3.3
			 * @return string
			 */
			public function fetch_post_taxonomy_options() {
				$taxonomies = $this->fetch_taxonomies();
				$options    = [];
				if ( is_array( $taxonomies ) ) {
					foreach ( $taxonomies as $taxonomy ) {
						$field_type  = 'ajax_select';
						$ajax        = 'fusion_search_query';
						$ajax_params = [
							'taxonomy' => $taxonomy->name,
						];
						$selection   = [];

						if ( 25 > wp_count_terms( $taxonomy->name ) ) {
							$ajax       = '';
							$field_type = 'multiple_select';
							$terms      = get_terms(
								[
									'taxonomy'   => $taxonomy->name,
									'hide_empty' => true,
								]
							);

							// All terms.
							foreach ( $terms as $term ) {
								$selection[ $term->term_id ] = $term->name;
							}
						}

						$taxonomy_title  = ucwords( $taxonomy->labels->name );
						$include_options = [
							'type'        => $field_type,
							/* translators: Taxonomy title. */
							'heading'     => sprintf( esc_attr__( 'Include %s', 'fusion-builder' ), $taxonomy_title ),
							'placeholder' => ucwords( $taxonomy->labels->name ),

							/* translators: Taxonomy name. */
							'description' => sprintf( esc_attr__( 'Select a %s or leave blank for all.', 'fusion-builder' ), strtolower( $taxonomy->labels->name ) ),
							'param_name'  => 'include_' . $taxonomy->name,
							'default'     => '',
							'value'       => $selection,
							'ajax'        => $ajax,
							'ajax_params' => $ajax_params,
							'callback'    => [
								'function' => 'fusion_ajax',
								'action'   => 'get_fusion_post_cards',
								'ajax'     => true,
							],
							'dependency'  => [
								[
									'element'  => 'posts_by',
									'value'    => $taxonomy->name,
									'operator' => '==',
								],
								[
									'element'  => 'source',
									'value'    => 'posts',
									'operator' => '==',
								],
							],
						];
						$options[]       = $include_options;

						// Duplicate the option for terms.
						$include_options['param_name'] = 'include_term_' . $taxonomy->name;
						$include_options['dependency'] = [
							[
								'element'  => 'terms_by',
								'value'    => $taxonomy->name,
								'operator' => '==',
							],
							[
								'element'  => 'source',
								'value'    => 'terms',
								'operator' => '==',
							],
						];
						$options[]                     = $include_options;

						$exclude_options = [
							'type'        => $field_type,
							/* translators: Taxonomy title. */
							'heading'     => sprintf( esc_attr__( 'Exclude %s', 'fusion-builder' ), $taxonomy_title ),
							'placeholder' => ucwords( $taxonomy->labels->name ),

							/* translators: Taxonomy name. */
							'description' => sprintf( esc_attr__( 'Select a %s or leave blank for all.', 'fusion-builder' ), strtolower( $taxonomy->labels->name ) ),
							'param_name'  => 'exclude_' . $taxonomy->name,
							'default'     => '',
							'value'       => $selection,
							'ajax'        => $ajax,
							'ajax_params' => $ajax_params,
							'callback'    => [
								'function' => 'fusion_ajax',
								'action'   => 'get_fusion_post_cards',
								'ajax'     => true,
							],
							'dependency'  => [
								[
									'element'  => 'posts_by',
									'value'    => $taxonomy->name,
									'operator' => '==',
								],
								[
									'element'  => 'source',
									'value'    => 'posts',
									'operator' => '==',
								],
							],
						];
						$options[]       = $exclude_options;

						// Duplicate option for terms.
						$exclude_options['param_name'] = 'exclude_term_' . $taxonomy->name;
						$exclude_options['dependency'] = $include_options['dependency'];
						$options[]                     = $exclude_options;
					}
				}

				return $options;
			}

			/**
			 * Fetch post meta options.
			 *
			 * @return array
			 */
			public function fetch_post_meta_options() {
				$options   = [];
				$options[] = [
					'type'         => 'textfield',
					'heading'      => esc_attr__( 'Custom Field - Name', 'fusion-builder' ),
					'description'  => esc_attr__( 'Enter the custom field(or meta) name.', 'fusion-builder' ),
					'param_name'   => 'custom_field_name',
					'default'      => '',
					'dynamic_data' => true,
					'callback'     => [
						'function' => 'fusion_ajax',
						'action'   => 'get_fusion_post_cards',
						'ajax'     => true,
					],
					'dependency'   => [
						[
							'element'  => 'posts_by',
							'value'    => 'awb_custom_field',
							'operator' => '==',
						],
						[
							'element'  => 'source',
							'value'    => 'posts',
							'operator' => '==',
						],
					],
				];

				$options[] = [
					'type'        => 'radio_button_set',
					'heading'     => esc_attr__( 'Custom Field - Value Comparison', 'fusion-builder' ),
					'description' => esc_attr__( ' Select the custom field(or meta) comparison type.', 'fusion-builder' ),
					'param_name'  => 'custom_field_comparison',
					'default'     => 'exists',
					'callback'    => [
						'function' => 'fusion_ajax',
						'action'   => 'get_fusion_post_cards',
						'ajax'     => true,
					],
					'value'       => [
						'exists'     => esc_html__( 'Exists', 'fusion-builder' ),
						'not_exists' => esc_html__( 'Not Exists', 'fusion-builder' ),
						'equals'     => esc_html__( 'Equals', 'fusion-builder' ),
					],
					'dependency'  => [
						[
							'element'  => 'posts_by',
							'value'    => 'awb_custom_field',
							'operator' => '==',
						],
						[
							'element'  => 'source',
							'value'    => 'posts',
							'operator' => '==',
						],
					],
				];

				$options[] = [
					'type'         => 'textfield',
					'heading'      => esc_attr__( 'Custom Field - Value', 'fusion-builder' ),
					'description'  => esc_attr__( 'Enter the custom field(or meta) value.', 'fusion-builder' ),
					'param_name'   => 'custom_field_value',
					'default'      => '',
					'dynamic_data' => true,
					'callback'     => [
						'function' => 'fusion_ajax',
						'action'   => 'get_fusion_post_cards',
						'ajax'     => true,
					],
					'dependency'   => [
						[
							'element'  => 'posts_by',
							'value'    => 'awb_custom_field',
							'operator' => '==',
						],
						[
							'element'  => 'source',
							'value'    => 'posts',
							'operator' => '==',
						],
						[
							'element'  => 'custom_field_comparison',
							'value'    => 'equals',
							'operator' => '==',
						],
					],
				];

				return $options;
			}
		}
	}

	/**
	 * Instantiates the post cards class.
	 *
	 * @return object FusionSC_PostCards
	 */
	function fusion_post_cards() { // phpcs:ignore WordPress.NamingConventions
		return FusionSC_PostCards::get_instance();
	}

	// Instantiate post cards.
	fusion_post_cards();
}

/**
 * Map shortcode to Avada Builder.
 */
function fusion_element_post_cards() {
	$fusion_settings = awb_get_fusion_settings();
	$editing         = function_exists( 'is_fusion_editor' ) && is_fusion_editor();

	$post_type_option  = [];
	$post_type_options = [];
	$taxonomy_options  = [];
	$filter_option     = [];
	$meta_options      = [];
	$post_terms_option = [];
	$layouts_permalink = [];
	$layouts           = [
		'0' => esc_attr__( 'None', 'fusion-builder' ),
	];

	// If builder get custom layout options.
	if ( $editing && function_exists( 'Fusion_Builder_Library' ) ) {
		// In case taxonomy is not registered yet, register.
		Fusion_Builder_Library()->register_layouts();

		$post_cards = get_posts(
			[
				'post_type'      => 'fusion_element',
				'posts_per_page' => '-1',
				'tax_query'      => [ // phpcs:ignore WordPress.DB.SlowDBQuery
					[
						'taxonomy' => 'element_category',
						'field'    => 'slug',
						'terms'    => 'post_cards',
					],
				],
			]
		);

		if ( $post_cards ) {
			foreach ( $post_cards as $post_card ) {
				$layouts[ $post_card->ID ]           = $post_card->post_title;
				$layouts_permalink[ $post_card->ID ] = $post_card->guid;
			}
		}

		// We only need options if element is active so check here is safe.
		if ( function_exists( 'fusion_post_cards' ) ) {
			$post_cards        = fusion_post_cards();
			$post_type_option  = $post_cards->fetch_post_type_option();
			$post_terms_option = $post_cards->fetch_post_terms_option();
			$filter_option     = $post_cards->fetch_post_filter_option();
			$taxonomy_options  = $post_cards->fetch_post_taxonomy_options();
			$meta_options      = $post_cards->fetch_post_meta_options();
		}
	}

	$source_values = [
		'posts'   => esc_attr__( 'Posts', 'fusion-builder' ),
		'terms'   => esc_attr__( 'Terms', 'fusion-builder' ),
		'related' => esc_attr__( 'Related', 'fusion-builder' ),
	];

	if ( class_exists( 'WooCommerce' ) ) {
		$woo_sources = [
			'up_sells'          => esc_attr__( 'Upsells', 'fusion-builder' ),
			'cross_sells'       => esc_attr__( 'Cross-sells', 'fusion-builder' ),
			'featured_products' => esc_attr__( 'Featured Products', 'fusion-builder' ),
		];

		$source_values = array_merge( $source_values, $woo_sources );
	}

	$library_link = '<a href="' . admin_url( 'admin.php?page=avada-library' ) . '">' . esc_attr__( 'Avada Library', 'fusion-builder' ) . '</a>';

	$params = [
		[
			'type'        => 'select',
			'heading'     => esc_attr__( 'Post Card', 'fusion-builder' ),
			'group'       => esc_attr__( 'General', 'fusion-builder' ),

			/* translators: The Avada Library link. */
			'description' => sprintf( __( 'Select a saved Post Card design to use. Create new or edit existing Post Cards in the %s.', 'fusion-builder' ), $library_link ),
			'param_name'  => 'post_card',
			'default'     => '0',
			'value'       => $layouts,
			'callback'    => [
				'function' => 'fusion_ajax',
				'action'   => 'get_fusion_post_cards',
				'ajax'     => true,
			],
			'quick_edit'  => [
				'label' => esc_html__( 'Edit Post Card', 'fusion-builder' ),
				'type'  => 'post_card',
				'items' => $layouts_permalink,
			],
		],
		[
			'type'        => 'select',
			'heading'     => esc_attr__( 'Post Card List View', 'fusion-builder' ),
			'group'       => esc_attr__( 'General', 'fusion-builder' ),

			/* translators: The Avada Library link. */
			'description' => sprintf( __( 'This post card will be used in the list view which can be triggered with the sorting element. Post cards can be created in the %s.', 'fusion-builder' ), $library_link ),
			'param_name'  => 'post_card_list_view',
			'default'     => '0',
			'value'       => $layouts,
			'dependency'  => [
				[
					'element'  => 'source',
					'value'    => 'posts',
					'operator' => '==',
				],
				[
					'element'  => 'post_type',
					'value'    => 'product',
					'operator' => '==',
				],
			],
			'callback'    => [
				'function' => 'fusion_ajax',
				'action'   => 'get_fusion_post_cards',
				'ajax'     => true,
			],
		],
		[
			'type'        => 'radio_button_set',
			'heading'     => esc_attr__( 'Content Source', 'fusion-builder' ),
			'description' => __( 'Select the type of content you would like to show. <strong>NOTE:</strong> The related option will fetch items related to the post that it is placed on based on taxonomy selection.', 'fusion-builder' ),
			'param_name'  => 'source',
			'default'     => 'posts',
			'value'       => $source_values,
			'callback'    => [
				'function' => 'fusion_ajax',
				'action'   => 'get_fusion_post_cards',
				'ajax'     => true,
			],
		],
		$post_type_option,
		$post_terms_option,
		$filter_option,
	];

	foreach ( $taxonomy_options as $taxonomy_option ) {
		$params[] = $taxonomy_option;
	}

	foreach ( $meta_options as $meta_option ) {
		$params[] = $meta_option;
	}

	$params[] = [
		'type'        => 'radio_button_set',
		'heading'     => esc_attr__( 'Include Out Of Stock Products', 'fusion-builder' ),
		'description' => esc_attr__( 'Include or exclude out of stock products.', 'fusion-builder' ),
		'param_name'  => 'out_of_stock',
		'value'       => [
			'include' => esc_attr__( 'Include', 'fusion-builder' ),
			'exclude' => esc_attr__( 'Exclude', 'fusion-builder' ),
		],
		'default'     => 'include',
		'dependency'  => [
			[
				'element'  => 'source',
				'value'    => 'posts',
				'operator' => '==',
			],
			[
				'element'  => 'post_type',
				'value'    => 'product',
				'operator' => '==',
			],
		],
		'callback'    => [
			'function' => 'fusion_ajax',
			'action'   => 'get_fusion_post_cards',
			'ajax'     => true,
		],
	];

	$params[] = [
		'type'        => 'radio_button_set',
		'heading'     => esc_attr__( 'Show Hidden Products', 'fusion-builder' ),
		'description' => esc_attr__( 'Display hidden products that are excluded from search or catalogs.', 'fusion-builder' ),
		'param_name'  => 'show_hidden',
		'value'       => [
			'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
			'no'  => esc_attr__( 'No', 'fusion-builder' ),
		],
		'default'     => 'no',
		'dependency'  => [
			[
				'element'  => 'source',
				'value'    => 'posts',
				'operator' => '==',
			],
			[
				'element'  => 'post_type',
				'value'    => 'product',
				'operator' => '==',
			],
		],
		'callback'    => [
			'function' => 'fusion_ajax',
			'action'   => 'get_fusion_post_cards',
			'ajax'     => true,
		],
	];

	$params[] = [
		'type'        => 'radio_button_set',
		'heading'     => esc_attr__( 'Show Filters', 'fusion-builder' ),
		'description' => esc_attr__( 'Choose to show or hide the filters.', 'fusion-builder' ),
		'param_name'  => 'filters',
		'value'       => [
			'yes'             => esc_attr__( 'Yes', 'fusion-builder' ),
			'yes-without-all' => __( 'Yes without "All"', 'fusion-builder' ),
			'no'              => esc_attr__( 'No', 'fusion-builder' ),
		],
		'default'     => 'no',
		'dependency'  => [
			[
				'element'  => 'layout',
				'value'    => 'carousel',
				'operator' => '!=',
			],
			[
				'element'  => 'layout',
				'value'    => 'slider',
				'operator' => '!=',
			],
			[
				'element'  => 'source',
				'value'    => 'posts',
				'operator' => '==',
			],
		],
		'callback'    => [
			'function' => 'fusion_ajax',
			'action'   => 'get_fusion_post_cards',
			'ajax'     => true,
		],
	];
	$params[] = [
		'type'        => 'range',
		'heading'     => esc_attr__( 'Number of Posts', 'fusion-builder' ),
		'description' => sprintf(
			/* translators: %1$s: Portfolio Link. %2$s: Products Link. */
			esc_attr__( 'Select number of posts per page. Set to -1 to display all. Set to 0 to use the post type default number of posts. For %1$s and %2$s this comes from the global options. For all others Settings > Reading.', 'fusion-builder' ),
			'<a href="' . admin_url( 'themes.php?page=avada_options#portfolio_archive_items' ) . '" target="_blank">' . esc_attr__( 'portfolio', 'fusion-builder' ) . '</a>',
			'<a href="' . admin_url( 'themes.php?page=avada_options#woo_items' ) . '" target="_blank">' . esc_attr__( 'products', 'fusion-builder' ) . '</a>'
		),
		'param_name'  => 'number_posts',
		'min'         => '-1',
		'max'         => '50',
		'step'        => '1',
		'value'       => '0',
		'callback'    => [
			'function' => 'fusion_ajax',
			'action'   => 'get_fusion_post_cards',
			'ajax'     => true,
		],
	];
	$params[] = [
		'type'        => 'range',
		'heading'     => esc_attr__( 'Posts Offset', 'fusion-builder' ),
		'description' => esc_attr__( 'The number of posts to skip. ex: 1.', 'fusion-builder' ),
		'param_name'  => 'offset',
		'value'       => '0',
		'min'         => '0',
		'max'         => '24',
		'step'        => '1',
		'callback'    => [
			'function' => 'fusion_ajax',
			'action'   => 'get_fusion_post_cards',
			'ajax'     => true,
		],
	];
	$params[] = [
		'type'        => 'select',
		'heading'     => esc_attr__( 'Order By', 'fusion-builder' ),
		'description' => __( 'Defines how posts should be ordered. <strong>NOTE:</strong> (Price|Popularity|Rating) options only work for product-related queries.', 'fusion-builder' ),
		'param_name'  => 'orderby',
		'default'     => 'date',
		'value'       => [
			'date'          => esc_attr__( 'Date', 'fusion-builder' ),
			'title'         => esc_attr__( 'Post Title', 'fusion-builder' ),
			'name'          => esc_attr__( 'Post Slug', 'fusion-builder' ),
			'author'        => esc_attr__( 'Author', 'fusion-builder' ),
			'id'            => esc_attr__( 'ID', 'fusion-builder' ),
			'comment_count' => esc_attr__( 'Number of Comments', 'fusion-builder' ),
			'modified'      => esc_attr__( 'Last Modified', 'fusion-builder' ),
			'rand'          => esc_attr__( 'Random', 'fusion-builder' ),
			'price'         => esc_attr__( 'Price', 'fusion-builder' ),
			'popularity'    => esc_attr__( 'Popularity (sales)', 'fusion-builder' ),
			'rating'        => esc_attr__( 'Average Rating', 'fusion-builder' ),
			'event_date'    => esc_attr__( 'Event Date', 'fusion-builder' ),
		],
		'dependency'  => [
			[
				'element'  => 'source',
				'value'    => 'terms',
				'operator' => '!=',
			],
		],
		'callback'    => [
			'function' => 'fusion_ajax',
			'action'   => 'get_fusion_post_cards',
			'ajax'     => true,
		],
	];
	$params[] = [
		'type'        => 'select',
		'heading'     => esc_attr__( 'Order By', 'fusion-builder' ),
		'description' => __( 'Defines how terms should be ordered.', 'fusion-builder' ),
		'param_name'  => 'orderby_term',
		'default'     => 'name',
		'value'       => [
			'name'        => esc_attr__( 'Name', 'fusion-builder' ),
			'slug'        => esc_attr__( 'Slug', 'fusion-builder' ),
			'term_group'  => esc_attr__( 'Term Group', 'fusion-builder' ),
			'term_id'     => esc_attr__( 'Term ID', 'fusion-builder' ),
			'description' => esc_attr__( 'Description', 'fusion-builder' ),
			'parent'      => esc_attr__( 'Parent', 'fusion-builder' ),
		],
		'dependency'  => [
			[
				'element'  => 'source',
				'value'    => 'terms',
				'operator' => '==',
			],
		],
		'callback'    => [
			'function' => 'fusion_ajax',
			'action'   => 'get_fusion_post_cards',
			'ajax'     => true,
		],
	];
	$params[] = [
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
			'action'   => 'get_fusion_post_cards',
			'ajax'     => true,
		],
	];

	$params[] = [
		'type'        => 'radio_button_set',
		'heading'     => esc_attr__( 'Show Only Upcoming Events', 'fusion-builder' ),
		'description' => __( 'Whether or not the events displayed will be only from the current date.', 'fusion-builder' ),
		'param_name'  => 'upcoming_events_only',
		'default'     => 'yes',
		'value'       => [
			'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
			'no'  => esc_attr__( 'No', 'fusion-builder' ),
		],
		'dependency'  => [
			[
				'element'  => 'source',
				'value'    => 'posts',
				'operator' => '==',
			],
			[
				'element'  => 'post_type',
				'value'    => 'tribe_events',
				'operator' => '==',
			],
		],
		'callback'    => [
			'function' => 'fusion_ajax',
			'action'   => 'get_fusion_post_cards',
			'ajax'     => true,
		],
	];
	$params[] = [
		'type'        => 'radio_button_set',
		'heading'     => esc_attr__( 'Show Only Featured Events', 'fusion-builder' ),
		'description' => __( 'Whether or not to display only events that are featured.', 'fusion-builder' ),
		'param_name'  => 'featured_events_only',
		'default'     => 'no',
		'value'       => [
			'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
			'no'  => esc_attr__( 'No', 'fusion-builder' ),
		],
		'dependency'  => [
			[
				'element'  => 'source',
				'value'    => 'posts',
				'operator' => '==',
			],
			[
				'element'  => 'post_type',
				'value'    => 'tribe_events',
				'operator' => '==',
			],
		],
		'callback'    => [
			'function' => 'fusion_ajax',
			'action'   => 'get_fusion_post_cards',
			'ajax'     => true,
		],
	];

	$params[] = [
		'type'        => 'radio_button_set',
		'heading'     => esc_attr__( 'Pagination Type', 'fusion-builder' ),
		'description' => esc_attr__( 'Select the type of pagination.', 'fusion-builder' ),
		'param_name'  => 'scrolling',
		'default'     => 'pagination',
		'value'       => [
			'no'               => esc_attr__( 'No Pagination', 'fusion-builder' ),
			'pagination'       => esc_attr__( 'Pagination', 'fusion-builder' ),
			'infinite'         => esc_attr__( 'Infinite Scrolling', 'fusion-builder' ),
			'load_more_button' => esc_attr__( 'Load More Button', 'fusion-builder' ),
		],
		'dependency'  => [
			[
				'element'  => 'layout',
				'value'    => 'carousel',
				'operator' => '!=',
			],
			[
				'element'  => 'layout',
				'value'    => 'slider',
				'operator' => '!=',
			],
			[
				'element'  => 'source',
				'value'    => 'terms',
				'operator' => '!=',
			],
		],
	];
	$params[] = [
		'type'        => 'checkbox_button_set',
		'heading'     => esc_attr__( 'Element Visibility', 'fusion-builder' ),
		'param_name'  => 'hide_on_mobile',
		'value'       => fusion_builder_visibility_options( 'full' ),
		'default'     => fusion_builder_default_visibility( 'array' ),
		'description' => esc_attr__( 'Choose to show or hide the element on small, medium or large screens. You can choose more than one at a time.', 'fusion-builder' ),
	];
	$params[] = [
		'type'        => 'textfield',
		'heading'     => esc_attr__( 'CSS Class', 'fusion-builder' ),
		'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
		'param_name'  => 'class',
		'value'       => '',
	];
	$params[] = [
		'type'        => 'textfield',
		'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
		'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
		'param_name'  => 'id',
		'value'       => '',
	];
	$params[] = [
		'type'        => 'radio_button_set',
		'heading'     => esc_attr__( 'Layout', 'fusion-builder' ),
		'description' => esc_attr__( 'Select how you want Post Cards to display.', 'fusion-builder' ),
		'param_name'  => 'layout',
		'value'       => [
			'grid'     => esc_attr__( 'Grid', 'fusion-builder' ),
			'carousel' => esc_attr__( 'Carousel', 'fusion-builder' ),
			'slider'   => esc_attr__( 'Slider', 'fusion-builder' ),
			'masonry'  => esc_attr__( 'Masonry', 'fusion-builder' ),
		],
		'default'     => 'grid',
		'group'       => esc_attr__( 'Design', 'fusion-builder' ),
	];
	$params[] = [
		'type'             => 'typography',
		'heading'          => esc_attr__( 'Filters Typography', 'fusion-builder' ),
		'description'      => esc_html__( 'Controls the typography of the filters content. Leave empty for the global font family.', 'fusion-builder' ),
		'param_name'       => 'filters_fonts',
		'choices'          => [
			'font-family'    => 'filters_font',
			'font-size'      => 'filters_font_size',
			'text-transform' => 'filters_text_transform',
			'line-height'    => 'filters_line_height',
			'letter-spacing' => 'filters_letter_spacing',
			'color'          => 'filters_color',
		],
		'default'          => [
			'font-family'    => '',
			'variant'        => '400',
			'font-size'      => '',
			'text-transform' => '',
			'line-height'    => '',
			'letter-spacing' => '',
			'color'          => $fusion_settings->get( 'link_color' ),
		],
		'remove_from_atts' => true,
		'global'           => true,
		'group'            => esc_attr__( 'Design', 'fusion-builder' ),
		'dependency'       => [
			[
				'element'  => 'layout',
				'value'    => 'carousel',
				'operator' => '!=',
			],
			[
				'element'  => 'layout',
				'value'    => 'slider',
				'operator' => '!=',
			],
			[
				'element'  => 'source',
				'value'    => 'posts',
				'operator' => '==',
			],
			[
				'element'  => 'filters',
				'value'    => 'no',
				'operator' => '!=',
			],
		],
		'callback'         => [
			'function' => 'fusion_style_block',
		],
	];
	$params[] = [
		'type'        => 'range',
		'heading'     => esc_attr__( 'Filters Container Height', 'fusion-builder' ),
		'description' => esc_attr__( 'Controls the filters container height. In pixels.', 'fusion-builder' ),
		'param_name'  => 'filters_height',
		'value'       => '36',
		'min'         => '0',
		'max'         => '500',
		'step'        => '1',
		'group'       => esc_attr__( 'Design', 'fusion-builder' ),
		'dependency'  => [
			[
				'element'  => 'layout',
				'value'    => 'carousel',
				'operator' => '!=',
			],
			[
				'element'  => 'layout',
				'value'    => 'slider',
				'operator' => '!=',
			],
			[
				'element'  => 'source',
				'value'    => 'posts',
				'operator' => '==',
			],
			[
				'element'  => 'filters',
				'value'    => 'no',
				'operator' => '!=',
			],
		],
		'callback'    => [
			'function' => 'fusion_style_block',
		],
	];
	$params[] = [
		'type'             => 'dimension',
		'remove_from_atts' => true,
		'heading'          => esc_attr__( 'Filters Container Border Size', 'fusion-builder' ),
		'description'      => esc_attr__( 'Controls the border size of the filters container. In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
		'param_name'       => 'border_sizes',
		'value'            => [
			'filters_border_top'    => '',
			'filters_border_right'  => '',
			'filters_border_bottom' => '',
			'filters_border_left'   => '',
		],
		'group'            => esc_attr__( 'Design', 'fusion-builder' ),
		'dependency'       => [
			[
				'element'  => 'layout',
				'value'    => 'carousel',
				'operator' => '!=',
			],
			[
				'element'  => 'layout',
				'value'    => 'slider',
				'operator' => '!=',
			],
			[
				'element'  => 'source',
				'value'    => 'posts',
				'operator' => '==',
			],
			[
				'element'  => 'filters',
				'value'    => 'no',
				'operator' => '!=',
			],
		],
		'callback'         => [
			'function' => 'fusion_style_block',
			'args'     => [
				'dimension' => true,
			],
		],
	];
	$params[] = [
		'type'        => 'colorpickeralpha',
		'heading'     => esc_attr__( 'Filters Container Border Color', 'fusion-builder' ),
		'description' => esc_attr__( 'Controls the border color of the filters container.', 'fusion-builder' ),
		'param_name'  => 'filters_border_color',
		'value'       => '',
		'default'     => $fusion_settings->get( 'sep_color' ),
		'group'       => esc_attr__( 'Design', 'fusion-builder' ),
		'dependency'  => [
			[
				'element'  => 'layout',
				'value'    => 'carousel',
				'operator' => '!=',
			],
			[
				'element'  => 'layout',
				'value'    => 'slider',
				'operator' => '!=',
			],
			[
				'element'  => 'source',
				'value'    => 'posts',
				'operator' => '==',
			],
			[
				'element'  => 'filters',
				'value'    => 'no',
				'operator' => '!=',
			],
		],
		'callback'    => [
			'function' => 'fusion_style_block',
		],
	];
	$params[] = [
		'type'        => 'radio_button_set',
		'heading'     => esc_attr__( 'Filters Alignment', 'fusion-builder' ),
		'description' => esc_attr__( 'Select the filters content alignment.', 'fusion-builder' ),
		'param_name'  => 'filters_alignment',
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
			'flex-start'    => esc_html__( 'Flex Start', 'fusion-builder' ),
			'center'        => esc_html__( 'Center', 'fusion-builder' ),
			'flex-end'      => esc_html__( 'Flex End', 'fusion-builder' ),
			'space-between' => esc_html__( 'Space Between', 'fusion-builder' ),
			'space-around'  => esc_html__( 'Space Around', 'fusion-builder' ),
			'space-evenly'  => esc_html__( 'Space Evenly', 'fusion-builder' ),
		],
		'group'       => esc_attr__( 'Design', 'fusion-builder' ),
		'dependency'  => [
			[
				'element'  => 'layout',
				'value'    => 'carousel',
				'operator' => '!=',
			],
			[
				'element'  => 'layout',
				'value'    => 'slider',
				'operator' => '!=',
			],
			[
				'element'  => 'source',
				'value'    => 'posts',
				'operator' => '==',
			],
			[
				'element'  => 'filters',
				'value'    => 'no',
				'operator' => '!=',
			],
		],
		'responsive'  => [
			'state' => 'large',
		],
		'callback'    => [
			'function' => 'fusion_style_block',
		],
	];
	$params[] = [
		'type'        => 'colorpickeralpha',
		'heading'     => esc_attr__( 'Filters Link Hover Color', 'fusion-builder' ),
		'description' => esc_attr__( 'Controls the hover color of the filters link.', 'fusion-builder' ),
		'param_name'  => 'filters_hover_color',
		'value'       => '',
		'default'     => $fusion_settings->get( 'primary_color' ),
		'group'       => esc_attr__( 'Design', 'fusion-builder' ),
		'dependency'  => [
			[
				'element'  => 'layout',
				'value'    => 'carousel',
				'operator' => '!=',
			],
			[
				'element'  => 'layout',
				'value'    => 'slider',
				'operator' => '!=',
			],
			[
				'element'  => 'source',
				'value'    => 'posts',
				'operator' => '==',
			],
			[
				'element'  => 'filters',
				'value'    => 'no',
				'operator' => '!=',
			],
		],
		'callback'    => [
			'function' => 'fusion_style_block',
		],
	];
	$params[] = [
		'type'        => 'colorpickeralpha',
		'heading'     => esc_attr__( 'Filters Link Active Color', 'fusion-builder' ),
		'description' => esc_attr__( 'Controls the active color of the filters link.', 'fusion-builder' ),
		'param_name'  => 'filters_active_color',
		'value'       => '',
		'default'     => $fusion_settings->get( 'primary_color' ),
		'group'       => esc_attr__( 'Design', 'fusion-builder' ),
		'dependency'  => [
			[
				'element'  => 'layout',
				'value'    => 'carousel',
				'operator' => '!=',
			],
			[
				'element'  => 'layout',
				'value'    => 'slider',
				'operator' => '!=',
			],
			[
				'element'  => 'source',
				'value'    => 'posts',
				'operator' => '==',
			],
			[
				'element'  => 'filters',
				'value'    => 'no',
				'operator' => '!=',
			],
		],
		'callback'    => [
			'function' => 'fusion_style_block',
		],
	];
	$params[] = [
		'type'        => 'range',
		'heading'     => esc_attr__( 'Active Filter Link Border Size', 'fusion-builder' ),
		'description' => esc_attr__( 'Controls the filters link border size. In pixels.', 'fusion-builder' ),
		'param_name'  => 'active_filter_border_size',
		'value'       => '3',
		'min'         => '0',
		'max'         => '100',
		'step'        => '1',
		'group'       => esc_attr__( 'Design', 'fusion-builder' ),
		'dependency'  => [
			[
				'element'  => 'layout',
				'value'    => 'carousel',
				'operator' => '!=',
			],
			[
				'element'  => 'layout',
				'value'    => 'slider',
				'operator' => '!=',
			],
			[
				'element'  => 'source',
				'value'    => 'posts',
				'operator' => '==',
			],
			[
				'element'  => 'filters',
				'value'    => 'no',
				'operator' => '!=',
			],
		],
		'callback'    => [
			'function' => 'fusion_style_block',
		],
	];
	$params[] = [
		'type'        => 'colorpickeralpha',
		'heading'     => esc_attr__( 'Active Filter Link Border Color', 'fusion-builder' ),
		'description' => esc_attr__( 'Controls the color of the active filter link.', 'fusion-builder' ),
		'param_name'  => 'active_filter_border_color',
		'value'       => '',
		'default'     => $fusion_settings->get( 'primary_color' ),
		'group'       => esc_attr__( 'Design', 'fusion-builder' ),
		'dependency'  => [
			[
				'element'  => 'layout',
				'value'    => 'carousel',
				'operator' => '!=',
			],
			[
				'element'  => 'layout',
				'value'    => 'slider',
				'operator' => '!=',
			],
			[
				'element'  => 'source',
				'value'    => 'posts',
				'operator' => '==',
			],
			[
				'element'  => 'filters',
				'value'    => 'no',
				'operator' => '!=',
			],
		],
		'callback'    => [
			'function' => 'fusion_style_block',
		],
	];
	$params[] = [
		'type'        => 'radio_button_set',
		'heading'     => esc_attr__( 'Post Card Alignment', 'fusion-builder' ),
		'description' => esc_attr__( 'Select the Post Cards alignment within rows.', 'fusion-builder' ),
		'param_name'  => 'flex_align_items',
		'back_icons'  => true,
		'grid_layout' => true,
		'value'       => [
			'flex-start' => esc_attr__( 'Flex Start', 'fusion-builder' ),
			'center'     => esc_attr__( 'Center', 'fusion-builder' ),
			'flex-end'   => esc_attr__( 'Flex End', 'fusion-builder' ),
			'stretch'    => esc_attr__( 'Stretch', 'fusion-builder' ),
		],
		'icons'       => [
			'flex-start' => '<span class="fusiona-align-top-columns"></span>',
			'center'     => '<span class="fusiona-align-center-columns"></span>',
			'flex-end'   => '<span class="fusiona-align-bottom-columns"></span>',
			'stretch'    => '<span class="fusiona-full-height"></span>',
		],
		'default'     => 'flex-start',
		'group'       => esc_attr__( 'Design', 'fusion-builder' ),
		'dependency'  => [
			[
				'element'  => 'layout',
				'value'    => 'grid',
				'operator' => '==',
			],
		],
	];
	$params[] = [
		'type'        => 'range',
		'heading'     => esc_attr__( 'Number of Columns', 'fusion-builder' ),
		'description' => esc_attr__( 'Set the number of columns per row.', 'fusion-builder' ),
		'param_name'  => 'columns',
		'value'       => '4',
		'min'         => '0',
		'max'         => '6',
		'step'        => '1',
		'group'       => esc_attr__( 'Design', 'fusion-builder' ),
		'responsive'  => [
			'state'        => 'large',
			'values'       => [
				'small'  => '0',
				'medium' => '0',
			],
			'descriptions' => [
				'small'  => esc_attr__( 'Set the number of columns per row. Leave at 0 for automatic column breaking', 'fusion-builder' ),
				'medium' => esc_attr__( 'Set the number of columns per row. Leave at 0 for automatic column breaking', 'fusion-builder' ),
			],
		],
		'dependency'  => [
			[
				'element'  => 'layout',
				'value'    => 'slider',
				'operator' => '!=',
			],
		],
	];
	$params[] = [
		'type'        => 'range',
		'heading'     => esc_attr__( 'Column Spacing', 'fusion-builder' ),
		'description' => esc_attr__( "Insert the amount of horizontal spacing between items without 'px'. ex: 40.", 'fusion-builder' ),
		'param_name'  => 'column_spacing',
		'value'       => '40',
		'min'         => '0',
		'max'         => '300',
		'step'        => '1',
		'group'       => esc_attr__( 'Design', 'fusion-builder' ),
		'dependency'  => [
			[
				'element'  => 'columns',
				'value'    => '1',
				'operator' => '!=',
			],
			[
				'element'  => 'layout',
				'value'    => 'slider',
				'operator' => '!=',
			],
		],
	];
	$params[] = [
		'type'        => 'range',
		'heading'     => esc_attr__( 'Row Spacing', 'fusion-builder' ),
		'description' => esc_attr__( "Insert the amount of vertical spacing between items without 'px'. ex: 40.", 'fusion-builder' ),
		'param_name'  => 'row_spacing',
		'value'       => '40',
		'min'         => '0',
		'max'         => '300',
		'step'        => '1',
		'group'       => esc_attr__( 'Design', 'fusion-builder' ),
		'dependency'  => [
			[
				'element'  => 'layout',
				'value'    => 'carousel',
				'operator' => '!=',
			],
			[
				'element'  => 'layout',
				'value'    => 'slider',
				'operator' => '!=',
			],
		],
	];

	// Separator styles.
	$params[] = [
		'type'        => 'select',
		'heading'     => esc_attr__( 'Separator', 'fusion-builder' ),
		'description' => esc_attr__( 'Choose the horizontal separator line style. This will only be used on single column grids or list view.', 'fusion-builder' ),
		'param_name'  => 'separator_style_type',
		'group'       => esc_attr__( 'Design', 'fusion-builder' ),
		'callback'    => [
			'function' => 'fusion_post_card_separator',
			'ajax'     => false,
		],
		'value'       => [
			'none'          => esc_attr__( 'None', 'fusion-builder' ),
			'single solid'  => esc_attr__( 'Single Border Solid', 'fusion-builder' ),
			'double solid'  => esc_attr__( 'Double Border Solid', 'fusion-builder' ),
			'single|dashed' => esc_attr__( 'Single Border Dashed', 'fusion-builder' ),
			'double|dashed' => esc_attr__( 'Double Border Dashed', 'fusion-builder' ),
			'single|dotted' => esc_attr__( 'Single Border Dotted', 'fusion-builder' ),
			'double|dotted' => esc_attr__( 'Double Border Dotted', 'fusion-builder' ),
		],
		'default'     => 'none',
		'dependency'  => [
			[
				'element'  => 'layout',
				'value'    => 'grid',
				'operator' => '==',
			],
		],
	];
	$params[] = [
		'type'        => 'colorpickeralpha',
		'heading'     => esc_attr__( 'Separator Color', 'fusion-builder' ),
		'description' => esc_attr__( 'Controls the separator color.', 'fusion-builder' ),
		'param_name'  => 'separator_sep_color',
		'value'       => '',
		'default'     => $fusion_settings->get( 'sep_color' ),
		'group'       => esc_attr__( 'Design', 'fusion-builder' ),
		'callback'    => [
			'function' => 'fusion_post_card_separator',
			'ajax'     => false,
		],
		'dependency'  => [
			[
				'element'  => 'layout',
				'value'    => 'grid',
				'operator' => '==',
			],
			[
				'element'  => 'separator_style_type',
				'value'    => 'none',
				'operator' => '!=',
			],
		],
	];
	$params[] = [
		'type'             => 'dimension',
		'remove_from_atts' => true,
		'heading'          => esc_attr__( 'Separator Width', 'fusion-builder' ),
		'param_name'       => 'dimensions_width',
		'value'            => [
			'separator_width' => '',
		],
		'description'      => esc_attr__( 'In pixels (px or %), ex: 1px, ex: 50%. Leave blank for full width.', 'fusion-builder' ),
		'group'            => esc_attr__( 'Design', 'fusion-builder' ),
		'callback'         => [
			'function' => 'fusion_post_card_separator',
			'ajax'     => false,
		],
		'dependency'       => [
			[
				'element'  => 'layout',
				'value'    => 'grid',
				'operator' => '==',
			],
			[
				'element'  => 'separator_style_type',
				'value'    => 'none',
				'operator' => '!=',
			],
		],
	];
	$params[] = [
		'type'        => 'radio_button_set',
		'heading'     => esc_attr__( 'Separator Alignment', 'fusion-builder' ),
		'description' => esc_attr__( 'Select the separator alignment; only works when a width is specified.', 'fusion-builder' ),
		'param_name'  => 'separator_alignment',
		'value'       => [
			'center' => esc_attr__( 'Center', 'fusion-builder' ),
			'left'   => esc_attr__( 'Left', 'fusion-builder' ),
			'right'  => esc_attr__( 'Right', 'fusion-builder' ),
		],
		'default'     => 'center',
		'group'       => esc_attr__( 'Design', 'fusion-builder' ),
		'callback'    => [
			'function' => 'fusion_post_card_separator',
			'ajax'     => false,
		],
		'dependency'  => [
			[
				'element'  => 'layout',
				'value'    => 'grid',
				'operator' => '==',
			],
			[
				'element'  => 'separator_style_type',
				'value'    => 'none',
				'operator' => '!=',
			],
		],
	];
	$params[] = [
		'type'        => 'range',
		'heading'     => esc_attr__( 'Separator Border Size', 'fusion-builder' ),
		'param_name'  => 'separator_border_size',
		'value'       => '',
		'min'         => '0',
		'max'         => '50',
		'step'        => '1',
		'default'     => $fusion_settings->get( 'separator_border_size' ),
		'description' => esc_attr__( 'In pixels. ', 'fusion-builder' ),
		'group'       => esc_attr__( 'Design', 'fusion-builder' ),
		'callback'    => [
			'function' => 'fusion_post_card_separator',
			'ajax'     => false,
		],
		'dependency'  => [
			[
				'element'  => 'layout',
				'value'    => 'grid',
				'operator' => '==',
			],
			[
				'element'  => 'separator_style_type',
				'value'    => 'none',
				'operator' => '!=',
			],
		],
	];

	$params[] = [
		'type'        => 'radio_button_set',
		'heading'     => esc_attr__( 'Autoplay', 'fusion-builder' ),
		'description' => esc_attr__( 'Choose to autoplay the items.', 'fusion-builder' ),
		'group'       => esc_attr__( 'Design', 'fusion-builder' ),
		'param_name'  => 'autoplay',
		'value'       => [
			'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
			'no'  => esc_attr__( 'No', 'fusion-builder' ),
		],
		'default'     => 'no',
		'dependency'  => [
			[
				'element'  => 'layout',
				'value'    => 'grid',
				'operator' => '!=',
			],
			[
				'element'  => 'layout',
				'value'    => 'masonry',
				'operator' => '!=',
			],
		],
	];
	$params[] = [
		'type'        => 'textfield',
		'heading'     => esc_attr__( 'Scroll Items', 'fusion-builder' ),
		'description' => esc_attr__( 'Insert the amount of items to scroll. Leave empty to scroll number of visible items.', 'fusion-builder' ),
		'group'       => esc_attr__( 'Design', 'fusion-builder' ),
		'param_name'  => 'scroll_items',
		'value'       => '',
		'dependency'  => [
			[
				'element'  => 'layout',
				'value'    => 'carousel',
				'operator' => '==',
			],
		],
	];
	$params[] = [
		'type'        => 'radio_button_set',
		'heading'     => esc_attr__( 'Show Navigation', 'fusion-builder' ),
		'description' => esc_attr__( 'Choose to show navigation buttons on the carousel / slider.', 'fusion-builder' ),
		'group'       => esc_attr__( 'Design', 'fusion-builder' ),
		'param_name'  => 'show_nav',
		'value'       => [
			'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
			'no'  => esc_attr__( 'No', 'fusion-builder' ),
		],
		'default'     => 'yes',
		'dependency'  => [
			[
				'element'  => 'layout',
				'value'    => 'grid',
				'operator' => '!=',
			],
			[
				'element'  => 'layout',
				'value'    => 'masonry',
				'operator' => '!=',
			],
		],
	];
	$params[] = [
		'type'        => 'radio_button_set',
		'heading'     => esc_attr__( 'Mouse Scroll', 'fusion-builder' ),
		'description' => esc_attr__( 'Choose to enable mouse drag control on the carousel. IMPORTANT: For easy draggability, when mouse scroll is activated, links will be disabled.', 'fusion-builder' ),
		'group'       => esc_attr__( 'Design', 'fusion-builder' ),
		'param_name'  => 'mouse_scroll',
		'value'       => [
			'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
			'no'  => esc_attr__( 'No', 'fusion-builder' ),
		],
		'default'     => 'no',
		'dependency'  => [
			[
				'element'  => 'layout',
				'value'    => 'carousel',
				'operator' => '==',
			],
		],
	];
	$params[] = [
		'type'             => 'dimension',
		'remove_from_atts' => true,
		'heading'          => esc_attr__( 'Navigation Margin', 'fusion-builder' ),
		'description'      => esc_attr__( 'Controls the space between content and navigation. Enter value including any valid CSS unit, default: 40px.', 'fusion-builder' ),
		'group'            => esc_attr__( 'Design', 'fusion-builder' ),
		'param_name'       => 'nav_margin',
		'value'            => [
			'nav_margin_top' => '',
		],
		'dependency'       => [
			[
				'element'  => 'layout',
				'value'    => 'slider',
				'operator' => '==',
			],
			[
				'element'  => 'show_nav',
				'value'    => 'yes',
				'operator' => '==',
			],
		],
	];
	$params[] = [
		'type'        => 'radio_button_set',
		'heading'     => esc_attr__( 'Animation', 'fusion-builder' ),
		'description' => esc_attr__( 'Choose for animation style.', 'fusion-builder' ),
		'group'       => esc_attr__( 'Design', 'fusion-builder' ),
		'param_name'  => 'slider_animation',
		'value'       => [
			'fade'  => esc_attr__( 'Fade', 'fusion-builder' ),
			'slide' => esc_attr__( 'Slide', 'fusion-builder' ),
		],
		'default'     => 'fade',
		'dependency'  => [
			[
				'element'  => 'layout',
				'value'    => 'slider',
				'operator' => '==',
			],
		],
	];
	$params[] = [
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
		'group'            => esc_attr__( 'Design', 'fusion-builder' ),
	];

	$params[] = [
		'type'             => 'subgroup',
		'heading'          => esc_html__( 'Load More - Button Styling', 'fusion-builder' ),
		'description'      => esc_html__( 'Customize "Load More" button colors.', 'fusion-builder' ),
		'param_name'       => 'load_more_button',
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
		'dependency'       => [
			[
				'element'  => 'scrolling',
				'value'    => 'load_more_button',
				'operator' => '==',
			],
		],
	];
	$params[] = [
		'type'        => 'colorpickeralpha',
		'heading'     => esc_attr__( 'Text Color', 'fusion-builder' ),
		'description' => esc_attr__( 'Select the button text color.', 'fusion-builder' ),
		'param_name'  => 'load_more_btn_color',
		'group'       => esc_attr__( 'Design', 'fusion-builder' ),
		'value'       => '',
		'default'     => 'var(--awb-color8)',
		'subgroup'    => [
			'name' => 'load_more_button',
			'tab'  => 'regular',
		],
		'dependency'  => [
			[
				'element'  => 'scrolling',
				'value'    => 'load_more_button',
				'operator' => '==',
			],
		],
	];
	$params[] = [
		'type'        => 'colorpickeralpha',
		'heading'     => esc_attr__( 'Background Color', 'fusion-builder' ),
		'description' => esc_attr__( 'Select the button background color.', 'fusion-builder' ),
		'param_name'  => 'load_more_btn_bg_color',
		'group'       => esc_attr__( 'Design', 'fusion-builder' ),
		'value'       => '',
		'default'     => 'var(--awb-color3)',
		'subgroup'    => [
			'name' => 'load_more_button',
			'tab'  => 'regular',
		],
		'dependency'  => [
			[
				'element'  => 'scrolling',
				'value'    => 'load_more_button',
				'operator' => '==',
			],
		],
	];
	$params[] = [
		'type'        => 'colorpickeralpha',
		'heading'     => esc_attr__( 'Hover Text Color', 'fusion-builder' ),
		'description' => esc_attr__( 'Select the button hover text color.', 'fusion-builder' ),
		'param_name'  => 'load_more_btn_hover_color',
		'group'       => esc_attr__( 'Design', 'fusion-builder' ),
		'value'       => '',
		'default'     => 'var(--awb-color1)',
		'subgroup'    => [
			'name' => 'load_more_button',
			'tab'  => 'active',
		],
		'dependency'  => [
			[
				'element'  => 'scrolling',
				'value'    => 'load_more_button',
				'operator' => '==',
			],
		],
	];
	$params[] = [
		'type'        => 'colorpickeralpha',
		'heading'     => esc_attr__( 'Hover Background Color', 'fusion-builder' ),
		'description' => esc_attr__( 'Select the button hover background color.', 'fusion-builder' ),
		'param_name'  => 'load_more_btn_hover_bg_color',
		'group'       => esc_attr__( 'Design', 'fusion-builder' ),
		'value'       => '',
		'default'     => 'var(--awb-color5)',
		'subgroup'    => [
			'name' => 'load_more_button',
			'tab'  => 'active',
		],
		'dependency'  => [
			[
				'element'  => 'scrolling',
				'value'    => 'load_more_button',
				'operator' => '==',
			],
		],
	];

	$params['fusion_animation_placeholder'] = [
		'preview_selector' => '.fusion-post-cards',
	];
	$params[]                               = [
		'type'        => 'range',
		'heading'     => esc_attr__( 'Animation Delay', 'fusion-builder' ),
		'description' => esc_attr__( 'Controls the delay of animation between each element in a set. In seconds.', 'fusion-builder' ),
		'param_name'  => 'animation_delay',
		'group'       => esc_attr__( 'Extras', 'fusion-builder' ),
		'min'         => '0',
		'max'         => '1',
		'step'        => '0.1',
		'value'       => '0',
		'dependency'  => [
			[
				'element'  => 'animation_type',
				'value'    => '',
				'operator' => '!=',
			],
			[
				'element'  => 'layout',
				'value'    => 'grid',
				'operator' => '==',
			],
		],
		'preview'     => [
			'selector' => '.fusion-post-cards',
			'type'     => 'animation',
		],
	];

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_PostCards',
			[
				'name'         => esc_attr__( 'Post Cards', 'fusion-builder' ),
				'shortcode'    => 'fusion_post_cards',
				'icon'         => 'fusiona-post-cards-element',
				'help_url'     => 'https://theme-fusion.com/documentation/avada/elements/post-cards-cart-element/',
				'params'       => $params,
				'subparam_map' => [
					'separator_width' => 'dimensions_width',
				],
				'callback'     => [
					'function' => 'fusion_ajax',
					'action'   => 'get_fusion_post_cards',
					'ajax'     => true,
				],
			]
		)
	);
}
add_action( 'wp_loaded', 'fusion_element_post_cards' );
