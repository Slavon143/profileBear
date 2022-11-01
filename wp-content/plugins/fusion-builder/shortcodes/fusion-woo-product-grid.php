<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_woo_product_grid' ) && class_exists( 'WooCommerce' ) ) {

	if ( ! class_exists( 'FusionSC_WooProductGrid' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_WooProductGrid extends Fusion_Element {

			/**
			 * The counter.
			 *
			 * @access private
			 * @since 3.2
			 * @var int
			 */
			private $element_counter = 1;

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 3.2
			 * @var array
			 */
			protected $args;

			/**
			 * Shortcode name.
			 *
			 * @access public
			 * @since 3.2
			 * @var string
			 */
			public $shortcode_name;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				$this->shortcode_name = 'fusion_woo_product_grid';
				add_filter( 'fusion_attr_woo-product-grid-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_woo-product-grid-shortcode-pagination', [ $this, 'attr_pagination' ] );
				add_filter( 'fusion_attr_woo-product-grid-shortcode-products', [ $this, 'attr_products' ] );

				add_shortcode( $this->shortcode_name, [ $this, 'render' ] );

				// Ajax mechanism for query related part.
				add_action( 'wp_ajax_get_fusion_woo_product_grid', [ $this, 'ajax_query' ] );

				add_action( 'pre_get_posts', [ $this, 'alter_shop_loop' ], 20 );
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
				$default_orderby = apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby', 'menu_order' ) );
				$default_order   = 'menu_order' === $default_orderby ? 'ASC' : 'DESC';

				return [
					'hide_on_mobile'            => fusion_builder_default_visibility( 'string' ),
					'class'                     => '',
					'id'                        => '',
					'pull_by'                   => 'category',
					'cat_slug'                  => '',
					'exclude_cats'              => '',
					'tag_slug'                  => '',
					'exclude_tags'              => '',
					'columns'                   => $fusion_settings->get( 'woocommerce_shop_page_columns' ),
					'column_spacing'            => $fusion_settings->get( 'woocommerce_archive_grid_column_spacing' ),
					'number_posts'              => $fusion_settings->get( 'woo_items' ),
					'offset'                    => '',
					'order'                     => $default_order,
					'orderby'                   => $default_orderby,
					'scrolling'                 => 'pagination',
					'show_title'                => 'yes',
					'show_thumbnail'            => 'yes',
					'show_buttons'              => 'yes',
					'show_price'                => 'yes',
					'show_rating'               => 'yes',
					'grid_box_color'            => $fusion_settings->get( 'timeline_bg_color' ),
					'grid_border_color'         => $fusion_settings->get( 'timeline_color' ),
					'grid_separator_color'      => $fusion_settings->get( 'grid_separator_color' ),
					'grid_separator_style_type' => $fusion_settings->get( 'grid_separator_style_type' ),
					'margin_bottom'             => '',
					'margin_left'               => '',
					'margin_right'              => '',
					'margin_top'                => '',
					'animation_type'            => '',
					'animation_direction'       => 'down',
					'animation_speed'           => '0.1',
					'animation_offset'          => $fusion_settings->get( 'animation_offset' ),
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
					'box_design'        => $fusion_settings->get( 'woocommerce_product_box_design', false, 'classic' ),
					'load_more_text'    => apply_filters( 'avada_load_more_products_name', esc_attr__( 'Load More Products', 'fusion-builder' ) ),
					'visibility_medium' => $fusion_settings->get( 'visibility_medium' ),
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
					'woocommerce_product_box_design' => 'box_design',
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
			 * @return array|Object
			 */
			public function query( $defaults ) {
				$live_request = false;

				// Return if there's a query override.
				$query_override = apply_filters( 'fusion_woo_product_grid_query_override', null, $defaults );

				if ( $query_override ) {
					return $query_override;
				}

				// From Ajax Request.
				if ( isset( $_POST['model'] ) && ! apply_filters( 'fusion_builder_live_request', false ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$defaults     = wp_unslash( $_POST['model']['params'] ); // phpcs:ignore WordPress.Security
					$return_data  = [];
					$live_request = true;
					add_filter( 'fusion_builder_live_request', '__return_true' );
				}

				$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
				if ( is_front_page() || is_home() ) {
					$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : ( ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1 );
				}

				$defaults['paged']   = $paged;
				$number_posts        = ( isset( $_GET['product_count'] ) ) ? (int) $_GET['product_count'] : (int) $defaults['number_posts']; // phpcs:ignore WordPress.Security
				$defaults['orderby'] = ( isset( $_GET['product_orderby'] ) ) ? $_GET['product_orderby'] : $defaults['orderby']; // phpcs:ignore WordPress.Security
				$defaults['order']   = ( isset( $_GET['product_order'] ) ) ? $_GET['product_order'] : $defaults['order']; // phpcs:ignore WordPress.Security

				if ( '0' == $defaults['offset'] ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
					$defaults['offset'] = '';
				}

				if ( 'default' === $defaults['orderby'] ) {
					$defaults['orderby'] = apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby', 'menu_order' ) );
				}

				if ( isset( $_GET['product_orderby'] ) && 'date' === $_GET['product_orderby'] && ! isset( $_GET['product_order'] ) ) { // phpcs:ignore WordPress.Security
					$defaults['order'] = 'DESC';
				}

				$args = [
					'post_type'      => 'product',
					'posts_per_page' => $number_posts,
					'paged'          => $defaults['paged'],
				];

				$ordering_args   = WC()->query->get_catalog_ordering_args( $defaults['orderby'], $defaults['order'] );
				$args['orderby'] = $ordering_args['orderby'];
				$args['order']   = $ordering_args['order'];
				if ( $ordering_args['meta_key'] ) {
					$args['meta_key'] = $ordering_args['meta_key']; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				}

				if ( '' !== $defaults['offset'] ) {
					$args['offset'] = $defaults['offset'];
				}

				// Pull products by category.
				if ( 'tag' !== $defaults['pull_by'] ) {
					if ( '' !== $defaults['cat_slug'] && $defaults['cat_slug'] ) {
						$cat_id = $defaults['cat_slug'];
						if ( false !== strpos( $defaults['cat_slug'], ',' ) ) {
							$cat_id = explode( ',', $defaults['cat_slug'] );
						} elseif ( false !== strpos( $defaults['cat_slug'], '|' ) ) {
							$cat_id = explode( '|', $defaults['cat_slug'] );
						}
						$args['tax_query'] = [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
							[
								'taxonomy' => 'product_cat',
								'field'    => 'slug',
								'terms'    => $cat_id,
							],
						];
					}

					if ( '' !== $defaults['exclude_cats'] && $defaults['exclude_cats'] ) {
						$cat_id = $defaults['exclude_cats'];
						if ( false !== strpos( $defaults['exclude_cats'], ',' ) ) {
							$cat_id = explode( ',', $defaults['exclude_cats'] );
						} elseif ( false !== strpos( $defaults['exclude_cats'], '|' ) ) {
							$cat_id = explode( '|', $defaults['exclude_cats'] );
						}
						$args['tax_query'][] = [
							'taxonomy' => 'product_cat',
							'field'    => 'slug',
							'terms'    => $cat_id,
							'operator' => 'NOT IN',
						];
					}
				} else {
					if ( '' !== $defaults['tag_slug'] && $defaults['tag_slug'] ) {
						$cat_id = $defaults['tag_slug'];
						if ( false !== strpos( $defaults['tag_slug'], ',' ) ) {
							$cat_id = explode( ',', $defaults['tag_slug'] );
						} elseif ( false !== strpos( $defaults['tag_slug'], '|' ) ) {
							$cat_id = explode( '|', $defaults['tag_slug'] );
						}
						$args['tax_query'] = [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
							[
								'taxonomy' => 'product_tag',
								'field'    => 'slug',
								'terms'    => $cat_id,
							],
						];
					}

					if ( '' !== $defaults['exclude_tags'] && $defaults['exclude_tags'] ) {
						$cat_id = $defaults['exclude_tags'];
						if ( false !== strpos( $defaults['exclude_tags'], ',' ) ) {
							$cat_id = explode( ',', $defaults['exclude_tags'] );
						} elseif ( false !== strpos( $defaults['exclude_tags'], '|' ) ) {
							$cat_id = explode( '|', $defaults['exclude_tags'] );
						}
						$args['tax_query'][] = [
							'taxonomy' => 'product_tag',
							'field'    => 'slug',
							'terms'    => $cat_id,
							'operator' => 'NOT IN',
						];
					}
				}

				$args['tax_query']['relation'] = 'AND';
				$args['tax_query'][]           = [
					'taxonomy' => 'product_visibility',
					'field'    => 'slug',
					'terms'    => [ 'exclude-from-catalog', 'exclude-from-search' ],
					'operator' => 'NOT IN',
				];

				// If out of stock are set not to show, hide them.
				if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items', 'no' ) ) {
					$args['meta_query'][] = [
						'key'     => '_stock_status',
						'value'   => 'outofstock',
						'compare' => 'NOT IN',
					];
				}

				// Ajax returns protected posts, but we just want published.
				if ( $live_request ) {
					$args['post_status'] = 'publish';
				}

				$products = fusion_cached_query( apply_filters( $this->shortcode_name . '_query_args', $args ) );

				fusion_library()->woocommerce->remove_post_clauses( $args['orderby'], $args['order'] );

				if ( ! $live_request ) {
					return $products;
				}

				if ( ! $products->have_posts() ) {
					$return_data['placeholder'] = fusion_builder_placeholder( 'product', 'products' );
					echo wp_json_encode( $return_data );
					wp_die();
				}

				if ( $products->have_posts() ) {
					$this->args = $defaults;

					$this->setup_loop( $products );

					ob_start();
					$this->before_shop_loop();
					while ( $products->have_posts() ) {
						$products->the_post();

						do_action( 'woocommerce_shop_loop' );

						wc_get_template_part( 'content', 'product' );
					}
					$this->after_shop_loop();
					$return_data['loop_product'] = ob_get_clean();

					ob_start();
					woocommerce_pagination();
					$return_data['pagination'] = ob_get_clean();
				}
				echo wp_json_encode( $return_data );
				wp_die();
			}

			/**
			 * Render the shortcode.
			 *
			 * @access public
			 * @since 1.0
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output
			 */
			public function render( $args, $content = '' ) {

				$this->defaults = self::get_element_defaults();

				$defaults = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_woo_product_grid' );

				$html = '';

				if ( class_exists( 'Woocommerce' ) ) {

					$products = $this->query( $defaults );

					$this->query = $products;

					$this->args = $defaults;

					$product_list = '';

					if ( $products->have_posts() ) {

						$original_post = $GLOBALS['post'];

						ob_start();

						// Setup the loop.
						$this->setup_loop( $products );

						echo '<ul ' . FusionBuilder::attributes( 'woo-product-grid-shortcode-products' ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

						$this->before_shop_loop();

						while ( $products->have_posts() ) {
							$products->the_post();
							$GLOBALS['post'] = get_post( get_the_ID() ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
							setup_postdata( $GLOBALS['post'] );

							$this->render_card();
						}

						$this->after_shop_loop();

						echo '</ul>';

						if ( 'no' !== $this->args['scrolling'] ) {
							echo '<div ' . FusionBuilder::attributes( 'woo-product-grid-shortcode-pagination' ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							woocommerce_pagination();
							echo '</div>';
						}

						$product_list = ob_get_clean();

						$GLOBALS['post'] = $original_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					} else {
						$this->element_counter++;
						return fusion_builder_placeholder( 'product', 'products' );
					}

					wp_reset_query(); // phpcs:ignore WordPress.WP.DiscouragedFunctions
					wp_reset_postdata();

					$html  = '<div ' . FusionBuilder::attributes( 'woo-product-grid-shortcode' ) . '>';
					$html .= $product_list;

					// If infinite scroll with "load more" button is used.
					if ( 'load_more_button' === $this->args['scrolling'] && 1 < $products->max_num_pages ) {
						$html .= '<button class="fusion-load-more-button fusion-product-button fusion-clearfix">' . apply_filters( 'avada_load_more_products_name', esc_attr__( 'Load More Products', 'fusion-builder' ) ) . '</button>';
					}
					$html .= '</div>';
					$html .= $this->get_styles(); // Get custom styles.
				}

				$this->element_counter++;

				$this->on_render();

				return apply_filters( 'fusion_element_woo_product_grid_content', $html, $args );

			}

			/**
			 * Render product card.
			 *
			 * @access public
			 * @since 3.3
			 * @return void
			 */
			public function render_card() {
				do_action( 'woocommerce_shop_loop' );

				wc_get_template_part( 'content', 'product' );
			}

			/**
			 * Setup WC Loop.
			 *
			 * @access public
			 * @since 3.2
			 * @param object $products the products object.
			 * @return void
			 */
			public function setup_loop( $products ) {
				wc_setup_loop(
					[
						'columns'      => $this->args['columns'],
						'name'         => 'products',
						'is_shortcode' => false,
						'is_search'    => false,
						'is_paginated' => true,
						'total'        => $products->found_posts,
						'total_pages'  => $products->max_num_pages,
						'per_page'     => $this->args['number_posts'],
						'current_page' => $products->query_vars['paged'],
					]
				);
			}

			/**
			 * Before shop loop.
			 *
			 * @access public
			 * @since 3.2
			 * @return void
			 */
			public function before_shop_loop() {
				global $avada_woocommerce;

				if ( class_exists( 'Avada' ) && null !== $avada_woocommerce && 'no' === $this->args['show_thumbnail'] ) {
					$priority = 'clean' === Avada()->settings->get( 'woocommerce_product_box_design' ) ? 10 : 7;
					remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', $priority );
					remove_action( 'woocommerce_before_shop_loop_item_title', [ $avada_woocommerce, 'show_product_loop_outofstock_flash' ], 7 );
					remove_action( 'woocommerce_before_shop_loop_item_title', [ $avada_woocommerce, 'thumbnail' ], 10 );
				}

				if ( class_exists( 'Avada' ) && null !== $avada_woocommerce && 'no' === $this->args['show_title'] && 'no' === $this->args['show_price'] && 'no' === $this->args['show_rating'] && 'no' === $this->args['show_buttons'] ) {
					$priority = 'clean' === Avada()->settings->get( 'woocommerce_product_box_design' ) ? 9 : 5;

					// Avada hooks.
					remove_action( 'woocommerce_after_shop_loop_item', [ $avada_woocommerce, 'before_shop_item_buttons' ], $priority );
					remove_action( 'woocommerce_after_shop_loop_item', [ $avada_woocommerce, 'after_shop_item_buttons' ], 20 );
					remove_action( 'woocommerce_after_shop_loop_item', [ $avada_woocommerce, 'template_loop_add_to_cart' ], 10 );
					remove_action( 'woocommerce_after_shop_loop_item', [ $avada_woocommerce, 'show_details_button' ], 15 );
					remove_action( 'woocommerce_before_shop_loop_item_title', [ $avada_woocommerce, 'add_product_wrappers_open' ], 30 );
					remove_action( 'woocommerce_shop_loop_item_title', [ $avada_woocommerce, 'product_title' ], 10 );
					remove_action( 'woocommerce_after_shop_loop_item_title', [ $avada_woocommerce, 'add_product_wrappers_close' ], 20 );

					// WC hooks.
					remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
					remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
				}

				if ( '' !== $this->args['grid_separator_style_type'] ) {
					add_filter( 'avada_grid_separator_style_types', [ $this, 'separator_style_filter' ] );
				}
			}

			/**
			 * After shop loop.
			 *
			 * @access public
			 * @since 3.2
			 * @return void
			 */
			public function after_shop_loop() {
				global $avada_woocommerce;

				if ( class_exists( 'Avada' ) && null !== $avada_woocommerce && 'no' === $this->args['show_thumbnail'] ) {
					$priority = 'clean' === Avada()->settings->get( 'woocommerce_product_box_design' ) ? 10 : 7;
					add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', $priority );
					add_action( 'woocommerce_before_shop_loop_item_title', [ $this, 'show_product_loop_outofstock_flash' ], 7 );
					add_action( 'woocommerce_before_shop_loop_item_title', [ $avada_woocommerce, 'thumbnail' ], 10 );
				}

				if ( class_exists( 'Avada' ) && null !== $avada_woocommerce && 'no' === $this->args['show_title'] && 'no' === $this->args['show_price'] && 'no' === $this->args['show_rating'] && 'no' === $this->args['show_buttons'] ) {
					$priority = 'clean' === Avada()->settings->get( 'woocommerce_product_box_design' ) ? 9 : 5;

					// Avada hooks.
					add_action( 'woocommerce_after_shop_loop_item', [ $avada_woocommerce, 'before_shop_item_buttons' ], $priority );
					add_action( 'woocommerce_after_shop_loop_item', [ $avada_woocommerce, 'after_shop_item_buttons' ], 20 );
					add_action( 'woocommerce_after_shop_loop_item', [ $avada_woocommerce, 'template_loop_add_to_cart' ], 10 );
					add_action( 'woocommerce_after_shop_loop_item', [ $avada_woocommerce, 'show_details_button' ], 15 );
					add_action( 'woocommerce_before_shop_loop_item_title', [ $avada_woocommerce, 'add_product_wrappers_open' ], 30 );
					add_action( 'woocommerce_shop_loop_item_title', [ $avada_woocommerce, 'product_title' ], 10 );
					add_action( 'woocommerce_after_shop_loop_item_title', [ $avada_woocommerce, 'add_product_wrappers_close' ], 20 );

					// WC hooks.
					add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
					add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
				}

				if ( '' !== $this->args['grid_separator_style_type'] ) {
					remove_filter( 'avada_grid_separator_style_types', [ $this, 'separator_style_filter' ] );
				}
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
						'class' => 'fusion-woo-product-grid fusion-product-archive fusion-woo-product-grid-' . $this->element_counter,
					]
				);

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				if ( $this->is_spacing_off() ) {
					$attr['class'] .= ' fusion-woo-product-grid-spacing-off';
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
			 * @since 3.2
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
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 3.2
			 * @return array
			 */
			public function attr_products() {
				$attr = [
					'class' => 'products products-' . $this->args['columns'],
				];

				if ( $this->is_load_more() ) {
					$attr['class'] .= ' fusion-products-container-infinite';

					$attr['data-pages'] = $this->query->max_num_pages;
				}

				if ( 'load_more_button' === $this->args['scrolling'] ) {
					$attr['class'] .= ' fusion-products-container-load-more';
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
				// Skip if empty.
				if ( null === $this->args || empty( $this->args ) ) {
					return;
				}

				Fusion_Dynamic_JS::enqueue_script(
					'fusion-js-' . $this->shortcode_name,
					FusionBuilder::$js_folder_url . '/general/fusion-product-grid.js',
					FusionBuilder::$js_folder_path . '/general/fusion-product-grid.js',
					[ 'jquery', 'isotope', 'jquery-infinite-scroll' ],
					'3.2',
					true
				);

				Fusion_Dynamic_JS::localize_script(
					'fusion-js-' . $this->shortcode_name,
					'fusionProductGridVars',
					[
						'infinite_blog_text'    => '<em>' . __( 'Loading the next set of products...', 'fusion-builder' ) . '</em>',
						'infinite_finished_msg' => '<em>' . __( 'All items displayed.', 'fusion-builder' ) . '</em>',
						'pagination_type'       => $this->args['scrolling'],
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

				// Needs styling for product rollover.
				if ( class_exists( 'Avada' ) && class_exists( 'WooCommerce' ) ) {
					Fusion_Dynamic_CSS::enqueue_style( Avada::$template_dir_path . '/assets/css/dynamic/woocommerce/woo-products.min.css', Avada::$template_dir_url . '/assets/css/dynamic/woocommerce/woo-products.min.css' );
				}
			}

			/**
			 * Check if pagination is loadmore.
			 *
			 * @access public
			 * @since 3.2
			 * @return boolean
			 */
			public function is_load_more() {
				return in_array( $this->args['scrolling'], [ 'infinite', 'load_more_button' ], true );
			}

			/**
			 * Check if spacing should be off.
			 *
			 * @access public
			 * @since 3.3
			 * @return boolean
			 */
			public function is_spacing_off() {
				return ! $this->is_default( 'show_price' ) && ! $this->is_default( 'show_rating' );
			}

			/**
			 * Remove buttons action.
			 *
			 * @access public
			 * @since 3.2
			 * @return void
			 */
			public function remove_action_buttons() {
				global $avada_woocommerce;

				if ( class_exists( 'Avada' ) && null !== $avada_woocommerce ) {
					$priority = 'clean' === Avada()->settings->get( 'woocommerce_product_box_design' ) ? 9 : 5;
					remove_action( 'woocommerce_after_shop_loop_item', [ $avada_woocommerce, 'before_shop_item_buttons' ], $priority );
					remove_action( 'woocommerce_after_shop_loop_item', [ $avada_woocommerce, 'after_shop_item_buttons' ], 20 );
				}
			}

			/**
			 * Add buttons action.
			 *
			 * @access public
			 * @since 3.2
			 * @return void
			 */
			public function add_action_buttons() {
				global $avada_woocommerce;

				if ( class_exists( 'Avada' ) && null !== $avada_woocommerce ) {
					$priority = 'clean' === Avada()->settings->get( 'woocommerce_product_box_design' ) ? 9 : 5;
					add_action( 'woocommerce_after_shop_loop_item', [ $avada_woocommerce, 'before_shop_item_buttons' ], $priority );
					add_action( 'woocommerce_after_shop_loop_item', [ $avada_woocommerce, 'after_shop_item_buttons' ], 20 );
				}
			}

			/**
			 * Separators style filter.
			 *
			 * @access public
			 * @since 3.2
			 * @param string $separators Separator styles.
			 * @return string
			 */
			public function separator_style_filter( $separators ) {
				return $this->args['grid_separator_style_type'];
			}

			/**
			 * Get the styles.
			 *
			 * @access protected
			 * @since 3.2
			 * @return string
			 */
			protected function get_styles() {

				$this->base_selector = '.fusion-woo-product-grid.fusion-woo-product-grid-' . $this->element_counter;
				$this->dynamic_css   = [];

				// Grid Box styles.
				$selectors = [
					$this->base_selector . ' .products li.product .fusion-product-wrapper',
				];
				if ( ! $this->is_default( 'grid_box_color' ) ) {
					$this->add_css_property( $selectors, 'background-color', $this->args['grid_box_color'] );
				}
				if ( ! $this->is_default( 'grid_border_color' ) ) {
					$this->add_css_property( $selectors, 'border-color', $this->args['grid_border_color'] );
				}

				// Separators styles.
				$selectors = [
					$this->base_selector . ' .fusion-content-sep',
				];
				if ( ! $this->is_default( 'grid_separator_color' ) ) {
					if ( 'shadow' !== $this->args['grid_separator_style_type'] ) {
						$this->add_css_property( $selectors, 'border-color', $this->args['grid_separator_color'] );
					} else {
						$colors         = Fusion_Color::new_color( $this->args['grid_separator_color'] );
						$gradient       = sprintf(
							'linear-gradient(to left, rgba(%1$d, %2$d, %3$d, 0) 0%%, rgba(%1$d, %2$d, %3$d, 0) 15%%, rgba(%1$d, %2$d, %3$d, 0.65) 50%%, rgba(%1$d, %2$d, %3$d, 0) 85%%, rgba(%1$d, %2$d, %3$d, 0) 100%%)',
							$colors->red,
							$colors->green,
							$colors->blue
						);
						$gradient_after = sprintf(
							'radial-gradient(ellipse at 50%% -50%%, rgba(%1$d, %2$d, %3$d, 0.5) 0, rgba(255, 255, 255, 0) 65%%)',
							$colors->red,
							$colors->green,
							$colors->blue
						);
						$this->add_css_property( $selectors, 'background', $gradient );
						$this->add_css_property( [ $this->base_selector . ' .fusion-content-sep:after' ], 'background', $gradient_after );
					}
				}

				// Hide styles.
				$selectors = [
					$this->base_selector . ' .product-title',
				];
				if ( ! $this->is_default( 'show_title' ) ) {
					$this->add_css_property( $selectors, 'display', 'none' );
				}
				$selectors = [
					$this->base_selector . ' .fusion-price-rating .price',
				];
				if ( ! $this->is_default( 'show_price' ) ) {
					$this->add_css_property( $selectors, 'display', 'none' );
				}
				$selectors = [
					$this->base_selector . ' .fusion-price-rating .star-rating',
					$this->base_selector . ' .fusion-rollover .star-rating',
				];
				if ( ! $this->is_default( 'show_rating' ) ) {
					$this->add_css_property( $selectors, 'display', 'none' );
				}
				$selectors = [
					$this->base_selector . ' .product-buttons',
					$this->base_selector . ' .fusion-product-buttons',
				];
				if ( ! $this->is_default( 'show_buttons' ) ) {
					$this->add_css_property( $selectors, 'display', 'none' );
				}
				$selectors = [
					$this->base_selector . ' .fusion-product-content',
				];
				if ( ! $this->is_default( 'show_title' ) && ! $this->is_default( 'show_price' ) && ! $this->is_default( 'show_rating' ) && ! $this->is_default( 'show_buttons' ) ) {
					$this->add_css_property( $selectors, 'display', 'none' );
				}
				$selectors = [
					$this->base_selector . ' .infinite-scroll-hide',
				];
				if ( $this->is_load_more() ) {
					$this->add_css_property( $selectors, 'display', 'none' );
				}
				$this->add_css_property( [ $this->base_selector . '.fusion-woo-product-grid-spacing-off .product .product-buttons' ], 'padding-top', '0' );
				$this->add_css_property( [ $this->base_selector . '.fusion-woo-product-grid-spacing-off .product-details-container' ], 'min-height', '0' );

				if ( ! $this->is_default( 'column_spacing' ) && '1' !== $this->args['columns'] ) {
					$column_spacing = fusion_library()->sanitize->get_value_with_unit( $this->args['column_spacing'] );

					$selectors = [
						$this->base_selector . ' ul.products',
					];
					$this->add_css_property( $selectors, 'margin-top', sprintf( 'calc((%s)/ -2)', $column_spacing ) );
					$this->add_css_property( $selectors, 'margin-right', sprintf( 'calc((%s)/ -2)', $column_spacing ) );
					$this->add_css_property( $selectors, 'margin-left', sprintf( 'calc((%s)/ -2)', $column_spacing ) );

					$selectors = [
						$this->base_selector . ' ul.products .product',
					];
					$this->add_css_property( $selectors, 'padding', sprintf( 'calc((%s)/ 2)', $column_spacing ) );
				}

				$selectors = [
					$this->base_selector,
				];
				// Margin styles.
				if ( ! $this->is_default( 'margin_top' ) ) {
					$this->add_css_property( $selectors, 'margin-top', fusion_library()->sanitize->get_value_with_unit( $this->args['margin_top'] ) );
				}
				if ( ! $this->is_default( 'margin_right' ) ) {
					$this->add_css_property( $selectors, 'margin-right', fusion_library()->sanitize->get_value_with_unit( $this->args['margin_right'] ) );
				}
				if ( ! $this->is_default( 'margin_bottom' ) ) {
					$this->add_css_property( $selectors, 'margin-bottom', fusion_library()->sanitize->get_value_with_unit( $this->args['margin_bottom'] ) );
				}
				if ( ! $this->is_default( 'margin_left' ) ) {
					$this->add_css_property( $selectors, 'margin-left', fusion_library()->sanitize->get_value_with_unit( $this->args['margin_left'] ) );
				}

				$css = $this->parse_css();

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
					$has_archives_component = $search_override && has_shortcode( $search_override->post_content, 'fusion_woo_product_grid' );

					if ( $has_archives_component ) {
						$pattern = get_shortcode_regex( [ 'fusion_woo_product_grid' ] );
						$content = $search_override->post_content;
						if ( preg_match_all( '/' . $pattern . '/s', $search_override->post_content, $matches )
							&& array_key_exists( 2, $matches )
							&& in_array( 'fusion_woo_product_grid', $matches[2], true ) ) {
							$search_atts  = shortcode_parse_atts( $matches[3][0] );
							$number_posts = ( isset( $_GET['product_count'] ) ) ? (int) $_GET['product_count'] : $search_atts['number_posts']; // phpcs:ignore WordPress.Security.NonceVerification
							$query->set( 'paged', ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1 );
							if ( '0' !== $number_posts ) {
								$query->set( 'posts_per_page', $number_posts );
							}
						}
					}
				}
			}
		}
	}

	new FusionSC_WooProductGrid();

}

/**
 * Map shortcode to Avada Builder.
 */
function fusion_element_woo_product_grid() {
	if ( class_exists( 'WooCommerce' ) ) {

		$fusion_settings = awb_get_fusion_settings();

		$builder_status    = function_exists( 'is_fusion_editor' ) && is_fusion_editor();
		$default_orderby   = apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby', 'menu_order' ) );
		$default_order     = 'menu_order' === $default_orderby ? 'ASC' : 'DESC';
		$lookup_table_link = admin_url( 'admin.php?page=wc-status&tab=tools' );

		fusion_builder_map(
			fusion_builder_frontend_data(
				'FusionSC_WooProductGrid',
				[
					'name'      => esc_attr__( 'Woo Product Grid', 'fusion-builder' ),
					'shortcode' => 'fusion_woo_product_grid',
					'icon'      => 'fusiona-product-grid-and-archives',
					'help_url'  => 'https://theme-fusion.com/documentation/avada/elements/woocommerce-product-carousel-element/',
					'params'    => [
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Pull Products By', 'fusion-builder' ),
							'description' => esc_attr__( 'Choose to show products by category or tag.', 'fusion-builder' ),
							'param_name'  => 'pull_by',
							'default'     => 'category',
							'value'       => [
								'category' => esc_attr__( 'Category', 'fusion-builder' ),
								'tag'      => esc_attr__( 'Tag', 'fusion-builder' ),
							],
							'callback'    => [
								'function' => 'fusion_ajax',
								'action'   => 'get_fusion_woo_product_grid',
								'ajax'     => true,
							],
						],
						[
							'type'        => 'multiple_select',
							'heading'     => esc_attr__( 'Categories', 'fusion-builder' ),
							'placeholder' => esc_attr__( 'Categories', 'fusion-builder' ),
							'description' => esc_attr__( 'Select a category or leave blank for all.', 'fusion-builder' ),
							'param_name'  => 'cat_slug',
							'value'       => $builder_status ? fusion_builder_shortcodes_categories( 'product_cat' ) : [],
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
								'action'   => 'get_fusion_woo_product_grid',
								'ajax'     => true,
							],
						],
						[
							'type'        => 'multiple_select',
							'heading'     => esc_attr__( 'Exclude Categories', 'fusion-builder' ),
							'placeholder' => esc_attr__( 'Exclude Categories', 'fusion-builder' ),
							'description' => esc_attr__( 'Select categories to exclude.', 'fusion-builder' ),
							'param_name'  => 'exclude_cats',
							'value'       => $builder_status ? fusion_builder_shortcodes_categories( 'product_cat' ) : [],
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
								'action'   => 'get_fusion_woo_product_grid',
								'ajax'     => true,
							],
						],
						[
							'type'        => 'multiple_select',
							'heading'     => esc_attr__( 'Tags', 'fusion-builder' ),
							'placeholder' => esc_attr__( 'Tags', 'fusion-builder' ),
							'description' => esc_attr__( 'Select a tag or leave blank for all.', 'fusion-builder' ),
							'param_name'  => 'tag_slug',
							'value'       => $builder_status ? fusion_builder_shortcodes_tags( 'product_tag' ) : [],
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
								'action'   => 'get_fusion_woo_product_grid',
								'ajax'     => true,
							],
						],
						[
							'type'        => 'multiple_select',
							'heading'     => esc_attr__( 'Exclude Tags', 'fusion-builder' ),
							'placeholder' => esc_attr__( 'Tags', 'fusion-builder' ),
							'description' => esc_attr__( 'Select a tag to exclude.', 'fusion-builder' ),
							'param_name'  => 'exclude_tags',
							'value'       => $builder_status ? fusion_builder_shortcodes_tags( 'product_tag' ) : [],
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
								'action'   => 'get_fusion_woo_product_grid',
								'ajax'     => true,
							],
						],
						[
							'type'        => 'range',
							'heading'     => esc_attr__( 'Number of Products', 'fusion-builder' ),
							'description' => esc_attr__( 'Select the number of products to display.', 'fusion-builder' ),
							'param_name'  => 'number_posts',
							'min'         => '0',
							'max'         => '50',
							'step'        => '1',
							'value'       => '',
							'default'     => $fusion_settings->get( 'woo_items' ),
							'callback'    => [
								'function' => 'fusion_ajax',
								'action'   => 'get_fusion_woo_product_grid',
								'ajax'     => true,
							],
						],
						[
							'type'        => 'range',
							'heading'     => esc_attr__( 'Product Offset', 'fusion-builder' ),
							'description' => esc_attr__( 'The number of products to skip. ex: 1.', 'fusion-builder' ),
							'param_name'  => 'offset',
							'value'       => '0',
							'min'         => '0',
							'max'         => '24',
							'step'        => '1',
							'callback'    => [
								'function' => 'fusion_ajax',
								'action'   => 'get_fusion_woo_product_grid',
								'ajax'     => true,
							],
						],
						[
							'type'        => 'range',
							'heading'     => esc_attr__( 'Number of Columns', 'fusion-builder' ),
							'description' => esc_attr__( 'Set the number of columns per row.', 'fusion-builder' ),
							'param_name'  => 'columns',
							'value'       => '',
							'default'     => $fusion_settings->get( 'woocommerce_shop_page_columns' ),
							'min'         => '1',
							'max'         => '6',
							'step'        => '1',
						],
						[
							'type'        => 'range',
							'heading'     => esc_attr__( 'Column Spacing', 'fusion-builder' ),
							'description' => esc_attr__( "Insert the amount of spacing between items without 'px'. ex: 40.", 'fusion-builder' ),
							'param_name'  => 'column_spacing',
							'value'       => '',
							'default'     => $fusion_settings->get( 'woocommerce_archive_grid_column_spacing' ),
							'min'         => '1',
							'max'         => '300',
							'step'        => '1',
							'dependency'  => [
								[
									'element'  => 'columns',
									'value'    => '1',
									'operator' => '!=',
								],
							],
						],
						[
							'type'        => 'select',
							'heading'     => esc_attr__( 'Order By', 'fusion-builder' ),
							/* translators: Lookup table link. */
							'description' => sprintf( __( 'Defines how products should be ordered. NOTE: If Order by Price is not working, please regenerate the Product Lookup Tables <a href="%s" target="_blank">here</a>.', 'fusion-builder' ), $lookup_table_link ),
							'param_name'  => 'orderby',
							'default'     => $default_orderby,
							'value'       => [
								'menu_order' => esc_attr__( 'Default sorting', 'fusion-builder' ),
								'date'       => esc_attr__( 'Date', 'fusion-builder' ),
								'title'      => esc_attr__( 'Title', 'fusion-builder' ),
								'rand'       => esc_attr__( 'Random', 'fusion-builder' ),
								'id'         => esc_attr__( 'ID', 'fusion-builder' ),
								'price'      => esc_attr__( 'Price', 'fusion-builder' ),
								'popularity' => esc_attr__( 'Popularity (sales)', 'fusion-builder' ),
								'rating'     => esc_attr__( 'Average Rating', 'fusion-builder' ),
							],
							'callback'    => [
								'function' => 'fusion_ajax',
								'action'   => 'get_fusion_woo_product_grid',
								'ajax'     => true,
							],
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Order', 'fusion-builder' ),
							'description' => esc_attr__( 'Defines the sorting order of products.', 'fusion-builder' ),
							'param_name'  => 'order',
							'default'     => $default_order,
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
								'action'   => 'get_fusion_woo_product_grid',
								'ajax'     => true,
							],
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Show Thumbnail', 'fusion-builder' ),
							'description' => esc_attr__( 'Display the product featured image.', 'fusion-builder' ),
							'param_name'  => 'show_thumbnail',
							'default'     => 'yes',
							'value'       => [
								'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
								'no'  => esc_attr__( 'No', 'fusion-builder' ),
							],
							'group'       => esc_html__( 'Design', 'fusion-builder' ),
							'callback'    => [
								'function' => 'fusion_ajax',
								'action'   => 'get_fusion_woo_product_grid',
								'ajax'     => true,
							],
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Show Title', 'fusion-builder' ),
							'description' => esc_attr__( 'Display the product title below the featured image.', 'fusion-builder' ),
							'param_name'  => 'show_title',
							'default'     => 'yes',
							'value'       => [
								'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
								'no'  => esc_attr__( 'No', 'fusion-builder' ),
							],
							'group'       => esc_html__( 'Design', 'fusion-builder' ),
							'callback'    => [
								'function' => 'fusion_ajax',
								'action'   => 'get_fusion_woo_product_grid',
								'ajax'     => true,
							],
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Show Price', 'fusion-builder' ),
							'description' => esc_attr__( 'Choose to show or hide the price.', 'fusion-builder' ),
							'param_name'  => 'show_price',
							'value'       => [
								'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
								'no'  => esc_attr__( 'No', 'fusion-builder' ),
							],
							'default'     => 'yes',
							'group'       => esc_html__( 'Design', 'fusion-builder' ),
							'callback'    => [
								'function' => 'fusion_ajax',
								'action'   => 'get_fusion_woo_product_grid',
								'ajax'     => true,
							],
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Show Rating', 'fusion-builder' ),
							'description' => esc_attr__( 'Choose to show or hide the rating.', 'fusion-builder' ),
							'param_name'  => 'show_rating',
							'value'       => [
								'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
								'no'  => esc_attr__( 'No', 'fusion-builder' ),
							],
							'default'     => 'yes',
							'group'       => esc_html__( 'Design', 'fusion-builder' ),
							'callback'    => [
								'function' => 'fusion_ajax',
								'action'   => 'get_fusion_woo_product_grid',
								'ajax'     => true,
							],
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Show Buttons', 'fusion-builder' ),
							'description' => esc_attr__( 'Choose to show or hide Add to Cart / Details buttons.', 'fusion-builder' ),
							'param_name'  => 'show_buttons',
							'value'       => [
								'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
								'no'  => esc_attr__( 'No', 'fusion-builder' ),
							],
							'default'     => 'yes',
							'group'       => esc_html__( 'Design', 'fusion-builder' ),
							'callback'    => [
								'function' => 'fusion_ajax',
								'action'   => 'get_fusion_woo_product_grid',
								'ajax'     => true,
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
							'group'       => esc_html__( 'Design', 'fusion-builder' ),
						],
						[
							'type'        => 'colorpickeralpha',
							'heading'     => esc_attr__( 'Grid Border Color', 'fusion-builder' ),
							'description' => esc_attr__( 'Controls the color of borders for the grid boxes.', 'fusion-builder' ),
							'param_name'  => 'grid_border_color',
							'value'       => '',
							'default'     => $fusion_settings->get( 'timeline_color' ),
							'group'       => esc_html__( 'Design', 'fusion-builder' ),
						],
						[
							'type'        => 'select',
							'heading'     => esc_attr__( 'Grid Separator Style', 'fusion-builder' ),
							'description' => __( 'Controls the line style of grid separators. <strong>NOTE:</strong> Separators will display, when buttons below the separators is displayed and Box Design mode set to Classic.', 'fusion-builder' ),
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
							'default'     => $fusion_settings->get( 'grid_separator_style_type' ),
							'group'       => esc_html__( 'Design', 'fusion-builder' ),
							'dependency'  => [
								[
									'element'  => 'show_buttons',
									'value'    => 'no',
									'operator' => '!=',
								],
							],
							'callback'    => [
								'function' => 'fusion_ajax',
								'action'   => 'get_fusion_woo_product_grid',
								'ajax'     => true,
							],
						],
						[
							'type'        => 'colorpickeralpha',
							'heading'     => esc_attr__( 'Grid Separator Color', 'fusion-builder' ),
							'description' => __( 'Controls the line style color of grid separators. <strong>NOTE:</strong> Only work when Box Design mode set to Classic.', 'fusion-builder' ),
							'param_name'  => 'grid_separator_color',
							'value'       => '',
							'default'     => $fusion_settings->get( 'grid_separator_color' ),
							'group'       => esc_html__( 'Design', 'fusion-builder' ),
							'dependency'  => [
								[
									'element'  => 'show_buttons',
									'value'    => 'no',
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
							'group'            => esc_attr__( 'Design', 'fusion-builder' ),
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
						'fusion_animation_placeholder' => [
							'preview_selector' => '.fusion-woo-product-grid',
						],
					],
					'callback'  => [
						'function' => 'fusion_ajax',
						'action'   => 'get_fusion_woo_product_grid',
						'ajax'     => true,
					],
				]
			)
		);
	}
}
add_action( 'wp_loaded', 'fusion_element_woo_product_grid' );
