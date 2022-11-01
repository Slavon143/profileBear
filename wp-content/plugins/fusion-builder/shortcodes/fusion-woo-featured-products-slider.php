<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_featured_products_slider' ) && class_exists( 'WooCommerce' ) ) {

	if ( ! class_exists( 'FusionSC_WooFeaturedProductsSlider' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_WooFeaturedProductsSlider extends Fusion_Element {

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
				add_filter( 'fusion_attr_woo-featured-products-slider-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_woo-featured-products-slider-shortcode-carousel', [ $this, 'carousel_attr' ] );
				add_shortcode( 'fusion_featured_products_slider', [ $this, 'render' ] );

				// Ajax mechanism for query related part.
				add_action( 'wp_ajax_get_fusion_featured_products', [ $this, 'ajax_query' ] );
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
					'margin_top'      => '',
					'margin_right'    => '',
					'margin_bottom'   => '',
					'margin_left'     => '',
					'hide_on_mobile'  => fusion_builder_default_visibility( 'string' ),
					'class'           => '',
					'id'              => '',
					'autoplay'        => 'no',
					'carousel_layout' => 'title_on_rollover',
					'cat_slug'        => '',
					'number_posts'    => 10,
					'offset'          => '',
					'order'           => 'DESC',
					'orderby'         => 'date',
					'out_of_stock'    => 'include',
					'columns'         => '5',
					'column_spacing'  => '10',
					'mouse_scroll'    => 'no',
					'picture_size'    => 'auto',
					'scroll_items'    => '',
					'show_buttons'    => 'yes',
					'show_cats'       => 'yes',
					'show_nav'        => 'yes',
					'show_price'      => 'yes',
					'show_sale'       => 'no',

					// Internal params.
					'post_type'       => 'product',
					'posts_per_page'  => -1,
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
					'design_class' => $fusion_settings->get( 'woocommerce_product_box_design', false, 'classic' ),
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
					'woocommerce_product_box_design' => 'design_class',
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

				// From Ajax Request.
				if ( isset( $_POST['model'] ) && ! apply_filters( 'fusion_builder_live_request', false ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$defaults     = $_POST['model']['params']; // phpcs:ignore WordPress.Security
					$return_data  = [];
					$live_request = true;
					add_filter( 'fusion_builder_live_request', '__return_true' );
				}

				$number_posts = (int) $defaults['number_posts'];

				if ( '0' == $defaults['offset'] ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
					$defaults['offset'] = '';
				}

				$ordering_args       = WC()->query->get_catalog_ordering_args( $defaults['orderby'], $defaults['order'] );
				$defaults['orderby'] = $ordering_args['orderby'];
				$defaults['order']   = $ordering_args['order'];
				if ( $ordering_args['meta_key'] ) {
					$defaults['meta_key'] = $ordering_args['meta_key']; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				}

				$defaults['posts_per_page'] = $number_posts;

				$defaults['tax_query'] = [
					[
						'taxonomy' => 'product_visibility',
						'field'    => 'name',
						'terms'    => 'featured',
						'operator' => 'IN',
					],
				];

				// If out of stock are set not to show, hide them.
				if ( 'exclude' === $defaults['out_of_stock'] ) {
					$defaults['meta_query'] = [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
						[
							'key'     => '_stock_status',
							'value'   => 'outofstock',
							'compare' => 'NOT IN',
						],
					];
				}

				if ( '' !== $defaults['cat_slug'] && $defaults['cat_slug'] ) {
					$cat_id = $defaults['cat_slug'];
					if ( false !== strpos( $defaults['cat_slug'], ',' ) ) {
						$cat_id = explode( ',', $defaults['cat_slug'] );
					} elseif ( false !== strpos( $defaults['cat_slug'], '|' ) ) {
						$cat_id = explode( '|', $defaults['cat_slug'] );
					}
					$defaults['tax_query']['relation'] = 'AND';
					$defaults['tax_query'][]           = [
						'taxonomy' => 'product_cat',
						'field'    => 'slug',
						'terms'    => $cat_id,
					];
				}

				if ( $live_request ) {
					// Ajax returns protected posts, but we just want published.
					$defaults['post_status'] = 'publish';
				}

				$products = fusion_cached_query( $defaults );

				fusion_library()->woocommerce->remove_post_clauses( $defaults['orderby'], $defaults['order'] );

				if ( ! $live_request ) {
					return $products;
				}

				if ( ! $products->have_posts() ) {
					$return_data['placeholder'] = fusion_builder_placeholder( 'product', 'products' );
					echo wp_json_encode( $return_data );
					wp_die();
				}

				$return_data['items_in_cart'] = fusion_library()->woocommerce->get_cart_products_ids();

				if ( $products->have_posts() ) {
					while ( $products->have_posts() ) {
						$products->the_post();

						$featured_image_sizes = [ 'shop_single', 'full' ];
						$image_data           = fusion_get_image_data( get_the_ID(), $featured_image_sizes, get_permalink( get_the_ID() ) );

						ob_start();
						fusion_wc_get_template( 'loop/price.php' );
						$price = ob_get_clean();

						$return_data['products'][] = [
							'permalink'  => get_permalink( get_the_ID() ),
							'title'      => get_the_title(),
							'terms'      => get_the_term_list( get_the_ID(), 'product_cat', '', ', ', '' ),
							'price'      => $price,
							'image_data' => $image_data,
						];
					}
				}
				echo wp_json_encode( $return_data );
				wp_die();
			}

			/**
			 * Render the shortcode.
			 *
			 * @access public
			 * @since 1.0
			 * @param  array  $args   Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string         HTML output.
			 */
			public function render( $args, $content = '' ) {
				$fusion_settings = awb_get_fusion_settings();

				$html = '';

				if ( class_exists( 'Woocommerce' ) ) {

					$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_featured_products_slider' );

					$defaults['column_spacing'] = FusionBuilder::validate_shortcode_attr_value( $defaults['column_spacing'], '' );

					$defaults['show_cats']    = ( 'yes' === $defaults['show_cats'] ) ? 'enable' : 'disable';
					$defaults['show_price']   = ( 'yes' === $defaults['show_price'] );
					$defaults['show_buttons'] = ( 'yes' === $defaults['show_buttons'] );

					$products = $this->query( $defaults );

					extract( $defaults );

					$this->args = $defaults;

					$this->args['margin_bottom'] = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_bottom'], 'px' );
					$this->args['margin_left']   = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_left'], 'px' );
					$this->args['margin_right']  = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_right'], 'px' );
					$this->args['margin_top']    = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_top'], 'px' );

					$design_class = 'fusion-' . $fusion_settings->get( 'woocommerce_product_box_design', false, 'classic' ) . '-product-image-wrapper';

					$featured_image_size = ( 'fixed' === $picture_size ) ? 'woocommerce_single' : 'full';

					if ( ! $products->have_posts() ) {
						return fusion_builder_placeholder( 'product', 'products' );
					}

					$product_list = '';

					if ( $products->have_posts() ) {

						while ( $products->have_posts() ) {
							$products->the_post();

							$id         = get_the_ID();
							$in_cart    = fusion_library()->woocommerce->is_product_in_cart( $id );
							$image      = $price_tag = $terms = '';
							$image_args = [
								'post_id'                  => get_the_ID(),
								'post_featured_image_size' => $featured_image_size,
								'post_permalink'           => get_permalink( get_the_ID() ),
								'display_placeholder_image' => true,
								'display_woo_sale'         => 'yes' === $show_sale ? true : false,
								'display_woo_outofstock'   => 'include' === $out_of_stock ? true : false,
								'display_woo_buttons'      => $show_buttons,
							];

							if ( 'auto' === $picture_size ) {
								fusion_library()->images->set_grid_image_meta(
									[
										'layout'       => 'grid',
										'columns'      => $columns,
										'gutter_width' => $column_spacing,
									]
								);
							}

							// Title on rollover layout.
							if ( 'title_on_rollover' === $carousel_layout ) {
								$image_args['display_woo_price']       = $show_price;
								$image_args['display_post_categories'] = $show_cats;
								$image                                 = avada_first_featured_image_markup( $image_args );
								// Title below image layout.
							} else {
								$image_args['display_woo_price']       = false;
								$image_args['display_post_categories'] = 'disable';
								$image_args['display_post_title']      = 'disable';

								if ( true === $show_buttons ) {
									$image .= avada_first_featured_image_markup( $image_args );
								} else {
									$image_args['display_rollover'] = 'no';
									$image                         .= avada_first_featured_image_markup( $image_args );
								}

								// Get the post title.
								$image .= '<h4 ' . FusionBuilder::attributes( 'fusion-carousel-title product-title' ) . '>';
								$image .= '<a href="' . get_permalink( get_the_ID() ) . '" target="_self">' . get_the_title() . '</a>';
								$image .= '</h4>';
								$image .= '<div class="fusion-carousel-meta">';

								// Get the terms.
								if ( 'enable' === $show_cats ) {
									$image .= get_the_term_list( get_the_ID(), 'product_cat', '', ', ', '' );
								}

								// Check if we should render the woo product price.
								if ( $show_price ) {
									ob_start();
									do_action( 'fusion_woocommerce_after_shop_loop_item' );
									$image .= ob_get_clean();

									ob_start();
									fusion_wc_get_template( 'loop/price.php' );
									$image .= '<div class="fusion-carousel-price">' . ob_get_clean() . '</div>';
								}

								$image .= '</div>';
							}

							if ( 'auto' === $picture_size ) {
								fusion_library()->images->set_grid_image_meta( [] );
							} else {
								// Disable quick view.
								$image = preg_replace( '/\<a href="#fusion-quick-view" (.*?)\<\/a\>/s', '', $image );
								$image = str_replace( ' fusion-has-quick-view', '', $image );
							}

							if ( $in_cart ) {
								$product_list .= '<li ' . FusionBuilder::attributes( 'fusion-carousel-item' ) . '><div class="' . $design_class . ' fusion-item-in-cart"><div ' . FusionBuilder::attributes( 'fusion-carousel-item-wrapper' ) . '>' . $image . '</div></div></li>';
							} else {
								$product_list .= '<li ' . FusionBuilder::attributes( 'fusion-carousel-item' ) . '><div class="' . $design_class . '"><div ' . FusionBuilder::attributes( 'fusion-carousel-item-wrapper' ) . '>' . $image . '</div></div></li>';
							}
						}
					}

					wp_reset_query();

					$html  = '<div ' . FusionBuilder::attributes( 'woo-featured-products-slider-shortcode' ) . '>';
					$html .= '<div ' . FusionBuilder::attributes( 'woo-featured-products-slider-shortcode-carousel' ) . '>';
					$html .= '<div ' . FusionBuilder::attributes( 'fusion-carousel-positioner' ) . '>';
					$html .= '<ul ' . FusionBuilder::attributes( 'fusion-carousel-holder' ) . '>';
					$html .= $product_list;
					$html .= '</ul>';
					// Check if navigation should be shown.
					if ( 'yes' === $show_nav ) {
						$html .= awb_get_carousel_nav();
					}
					$html .= '</div>';
					$html .= '</div>';
					$html .= '</div>';

				}

				$this->on_render();

				return apply_filters( 'fusion_element_featured_products_slider_content', $html, $args );

			}

			/**
			 * Builds the array of atributes.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function attr() {

				$attr = fusion_builder_visibility_atts(
					$this->args['hide_on_mobile'],
					[
						'class' => 'fusion-woo-featured-products-slider fusion-woo-slider',
						'style' => '',
					]
				);

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
			 * Builds the carousel attributes.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function carousel_attr() {

				$attr = [
					'class' => 'fusion-carousel',
				];

				if ( 'title_below_image' === $this->args['carousel_layout'] ) {
					$attr['class']           .= ' fusion-carousel-title-below-image';
					$attr['data-metacontent'] = 'yes';
				} else {
					$attr['class'] .= ' fusion-carousel-title-on-rollover';
				}

				$attr['data-autoplay']    = $this->args['autoplay'];
				$attr['data-columns']     = $this->args['columns'];
				$attr['data-itemmargin']  = $this->args['column_spacing'];
				$attr['data-itemwidth']   = 180;
				$attr['data-touchscroll'] = $this->args['mouse_scroll'];
				$attr['data-imagesize']   = $this->args['picture_size'];
				$attr['data-scrollitems'] = $this->args['scroll_items'];

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
				Fusion_Dynamic_JS::enqueue_script( 'fusion-carousel' );
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/woo-featured-products-slider.min.css' );
			}
		}
	}

	new FusionSC_WooFeaturedProductsSlider();

}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_featured_products_slider() {
	if ( class_exists( 'WooCommerce' ) ) {
		$lookup_table_link = admin_url( 'admin.php?page=wc-status&tab=tools' );
		fusion_builder_map(
			fusion_builder_frontend_data(
				'FusionSC_WooFeaturedProductsSlider',
				[
					'name'      => esc_attr__( 'Woo Featured Products Slider', 'fusion-builder' ),
					'shortcode' => 'fusion_featured_products_slider',
					'icon'      => 'fusiona-star-empty',
					'help_url'  => 'https://theme-fusion.com/documentation/avada/elements/woocommerce-featured-products-slider-element/',
					'params'    => [
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Picture Size', 'fusion-builder' ),
							'description' => __( 'fixed = width and height will be fixed <br />auto = width and height will adjust to the image.', 'fusion-builder' ),
							'param_name'  => 'picture_size',
							'value'       => [
								'fixed' => esc_attr__( 'Fixed', 'fusion-builder' ),
								'auto'  => esc_attr__( 'Auto', 'fusion-builder' ),
							],
							'default'     => 'fixed',
						],
						[
							'type'        => 'multiple_select',
							'heading'     => esc_attr__( 'Categories', 'fusion-builder' ),
							'placeholder' => esc_attr__( 'Categories', 'fusion-builder' ),
							'description' => esc_attr__( 'Select a category or leave blank for all.', 'fusion-builder' ),
							'param_name'  => 'cat_slug',
							'value'       => fusion_builder_shortcodes_categories( 'product_cat' ),
							'default'     => '',
							'callback'    => [
								'function' => 'fusion_ajax',
								'action'   => 'get_fusion_featured_products',
								'ajax'     => true,
							],
						],
						[
							'type'        => 'range',
							'heading'     => esc_attr__( 'Number of Products', 'fusion-builder' ),
							'description' => esc_attr__( 'Select the number of products to display.', 'fusion-builder' ),
							'param_name'  => 'number_posts',
							'value'       => '10',
							'min'         => '0',
							'max'         => '24',
							'step'        => '1',
							'callback'    => [
								'function' => 'fusion_ajax',
								'action'   => 'get_fusion_featured_products',
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
							'max'         => '25',
							'step'        => '1',
							'callback'    => [
								'function' => 'fusion_ajax',
								'action'   => 'get_fusion_featured_products',
								'ajax'     => true,
							],
						],
						[
							'type'        => 'select',
							'heading'     => esc_attr__( 'Order By', 'fusion-builder' ),
							/* translators: The link. */
							'description' => sprintf( __( 'Defines how products should be ordered. NOTE: If Order by Price is not working, please regenerate the Product Lookup Tables <a href="%s" target="_blank">here</a>.' ), $lookup_table_link ),
							'param_name'  => 'orderby',
							'default'     => 'date',
							'value'       => [
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
								'action'   => 'get_fusion_featured_products',
								'ajax'     => true,
							],
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Order', 'fusion-builder' ),
							'description' => esc_attr__( 'Defines the sorting order of products.', 'fusion-builder' ),
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
								'action'   => 'get_fusion_featured_products',
								'ajax'     => true,
							],
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Include Out Of Stock Products', 'fusion-builder' ),
							'description' => esc_attr__( 'Choose to include or exclude products which are out of stock.', 'fusion-builder' ),
							'param_name'  => 'out_of_stock',
							'value'       => [
								'include' => esc_attr__( 'Include', 'fusion-builder' ),
								'exclude' => esc_attr__( 'Exclude', 'fusion-builder' ),
							],
							'default'     => 'include',
							'callback'    => [
								'function' => 'fusion_ajax',
								'action'   => 'get_fusion_featured_products',
								'ajax'     => true,
							],
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Carousel Layout', 'fusion-builder' ),
							'description' => esc_attr__( 'Choose to show titles on rollover image, or below image.', 'fusion-builder' ),
							'param_name'  => 'carousel_layout',
							'value'       => [
								'title_on_rollover' => esc_attr__( 'Title on rollover', 'fusion-builder' ),
								'title_below_image' => esc_attr__( 'Title below image', 'fusion-builder' ),
							],
							'default'     => 'title_on_rollover',
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Carousel Autoplay', 'fusion-builder' ),
							'description' => esc_attr__( 'Choose to autoplay the carousel.', 'fusion-builder' ),
							'param_name'  => 'autoplay',
							'value'       => [
								'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
								'no'  => esc_attr__( 'No', 'fusion-builder' ),
							],
							'default'     => 'no',
						],
						[
							'type'        => 'range',
							'heading'     => esc_attr__( 'Maximum Columns', 'fusion-builder' ),
							'description' => esc_attr__( 'Select the number of max columns to display.', 'fusion-builder' ),
							'param_name'  => 'columns',
							'value'       => '5',
							'min'         => '1',
							'max'         => '6',
							'step'        => '1',
						],
						[
							'type'        => 'range',
							'heading'     => esc_attr__( 'Column Spacing', 'fusion-builder' ),
							'description' => esc_attr__( "Insert the amount of spacing between items without 'px'. ex: 13.", 'fusion-builder' ),
							'param_name'  => 'column_spacing',
							'value'       => '10',
							'min'         => '1',
							'max'         => '100',
							'step'        => '1',
						],
						[
							'type'        => 'textfield',
							'heading'     => esc_attr__( 'Scroll Items', 'fusion-builder' ),
							'description' => esc_attr__( 'Insert the amount of items to scroll. Leave empty to scroll number of visible items.', 'fusion-builder' ),
							'param_name'  => 'scroll_items',
							'value'       => '',
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Show Navigation', 'fusion-builder' ),
							'description' => esc_attr__( 'Choose to show navigation buttons on the carousel.', 'fusion-builder' ),
							'param_name'  => 'show_nav',
							'value'       => [
								'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
								'no'  => esc_attr__( 'No', 'fusion-builder' ),
							],
							'default'     => 'yes',
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Mouse Scroll', 'fusion-builder' ),
							'description' => esc_attr__( 'Choose to enable mouse drag control on the carousel. IMPORTANT: For easy draggability, when mouse scroll is activated, links will be disabled.', 'fusion-builder' ),
							'param_name'  => 'mouse_scroll',
							'value'       => [
								'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
								'no'  => esc_attr__( 'No', 'fusion-builder' ),
							],
							'default'     => 'no',
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Show Categories', 'fusion-builder' ),
							'description' => esc_attr__( 'Choose to show or hide the categories.', 'fusion-builder' ),
							'param_name'  => 'show_cats',
							'value'       => [
								'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
								'no'  => esc_attr__( 'No', 'fusion-builder' ),
							],
							'default'     => 'yes',
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
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Show Sale Badge', 'fusion-builder' ),
							'description' => esc_attr__( 'Choose to show or hide the sale badge.', 'fusion-builder' ),
							'param_name'  => 'show_sale',
							'value'       => [
								'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
								'no'  => esc_attr__( 'No', 'fusion-builder' ),
							],
							'default'     => 'no',
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Show Buttons', 'fusion-builder' ),
							'description' => esc_attr__( 'Choose to show or hide Add to Cart / Details buttons on the rollover.', 'fusion-builder' ),
							'param_name'  => 'show_buttons',
							'value'       => [
								'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
								'no'  => esc_attr__( 'No', 'fusion-builder' ),
							],
							'default'     => 'yes',
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
					'callback'  => [
						'function' => 'fusion_ajax',
						'action'   => 'get_fusion_featured_products',
						'ajax'     => true,
					],
				]
			)
		);
	}
}
add_action( 'wp_loaded', 'fusion_element_featured_products_slider' );
