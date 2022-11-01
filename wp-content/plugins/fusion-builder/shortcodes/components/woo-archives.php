<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.3
 */

if ( fusion_is_element_enabled( 'fusion_woo_product_grid' ) ) {

	if ( fusion_is_element_enabled( 'fusion_tb_woo_archives' ) ) {

		if ( ! class_exists( 'FusionTB_Woo_Archives' ) ) {
			/**
			 * Shortcode class.
			 *
			 * @since 3.3
			 */
			class FusionTB_Woo_Archives extends Fusion_Woo_Component {

				/**
				 * An array of the shortcode arguments.
				 *
				 * @access protected
				 * @since 3.3
				 * @var array
				 */
				protected $args;

				/**
				 * Constructor.
				 *
				 * @access public
				 * @since 3.3
				 */
				public function __construct() {
					parent::__construct( 'fusion_tb_woo_archives' );

					// Ajax mechanism for query related part.
					add_action( 'wp_ajax_get_' . $this->shortcode_handle, [ $this, 'ajax_query' ] );

					add_filter( 'fusion_tb_component_check', [ $this, 'component_check' ] );

					add_action( 'pre_get_posts', [ $this, 'alter_search_loop' ] );

					add_filter( 'fusion_attr_' . $this->shortcode_handle, [ $this, 'attr' ] );
				}


				/**
				 * Check if component should render
				 *
				 * @access public
				 * @since 3.3
				 * @return boolean
				 */
				public function should_render() {
					return is_search() || is_archive();
				}

				/**
				 * Checks and returns post type for archives component.
				 *
				 * @since  3.3
				 * @access public
				 * @param  array $defaults current params array.
				 * @return array $defaults Updated params array.
				 */
				public function archives_type( $defaults ) {
					return Fusion_Template_Builder()->archives_type( $defaults );
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

					if ( isset( $_POST['fusion_meta'] ) && isset( $_POST['post_id'] ) ) {
						$meta = fusion_string_to_array( wp_unslash( $_POST['fusion_meta'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
						$type = isset( $meta['_fusion']['dynamic_content_preview_type'] ) && in_array( $meta['_fusion']['dynamic_content_preview_type'], [ 'search', 'archives' ], true ) ? $meta['_fusion']['dynamic_content_preview_type'] : false;
						if ( ! $type ) {
							echo wp_json_encode( [] );
							wp_die();
						}
					}

					add_filter( 'fusion_woo_product_grid_query_args', [ $this, 'archives_type' ] );
					do_action( 'wp_ajax_get_fusion_woo_product_grid', $defaults );
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
					global $post;

					$defaults = FusionSC_WooProductGrid::get_element_defaults();

					$defaults['post_type'] = get_post_type( $post );

					return $defaults;
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
					return FusionSC_WooProductGrid::get_element_extras();
				}

				/**
				 * Maps settings to extra variables.
				 *
				 * @static
				 * @access public
				 * @since 3.3
				 * @return array
				 */
				public static function settings_to_extras() {
					return FusionSC_WooProductGrid::settings_to_extras();
				}

				/**
				 * Renders fusion woo product grid shortcode.
				 *
				 * @access public
				 * @since 3.3
				 * @return string
				 */
				public function render_product() {
					global $shortcode_tags;

					return call_user_func( $shortcode_tags['fusion_woo_product_grid'], $this->args, '', 'fusion_woo_product_grid' );
				}

				/**
				 * Filters the current query
				 *
				 * @access public
				 * @since 3.3
				 * @param array $query The query.
				 * @return array
				 */
				public function fusion_woo_product_grid_query_override( $query ) {
					global $wp_query;

					return $wp_query;
				}

				/**
				 * Render the shortcode
				 *
				 * @access public
				 * @since 3.3
				 * @param  array  $args    Shortcode parameters.
				 * @param  string $content Content between shortcode.
				 * @return string          HTML output.
				 */
				public function render( $args, $content = '' ) {
					global $post, $wp_query;

					$this->args = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, $this->shortcode_handle );

					$option = isset( $post->ID ) ? fusion_get_page_option( 'dynamic_content_preview_type', $post->ID ) : '';
					$html   = '<div ' . FusionBuilder::attributes( $this->shortcode_handle ) . ' >';

					// Handle empty results.
					if ( ! fusion_is_preview_frame() && ! $post ) {
						$html .= apply_filters( 'fusion_shortcode_content', '<h2 class="fusion-nothing-found">' . $content . '</h2>', $this->shortcode_handle, $args );

					} elseif ( fusion_is_preview_frame() && ! in_array( $option, [ 'search', 'archives' ], true ) ) {

						// Return notice if Dynamic Content is invalid.
						$html .= apply_filters( 'fusion_component_' . $this->shortcode_handle . '_content', $html, $args );

					} elseif ( ! fusion_is_preview_frame() && $this->should_render() ) {

						// Pass main query to woo product grid.
						add_filter( 'fusion_woo_product_grid_query_override', [ $this, 'fusion_woo_product_grid_query_override' ] );
						$html .= $this->render_product();
						remove_filter( 'fusion_woo_product_grid_query_override', [ $this, 'fusion_woo_product_grid_query_override' ] );
					} elseif ( fusion_is_preview_frame() ) {
						add_filter( 'fusion_woo_product_grid_query_args', [ $this, 'archives_type' ] );
						$html .= $this->render_product();
						remove_filter( 'fusion_woo_product_grid_query_args', [ $this, 'archives_type' ] );
					}

					$html .= '</div>';

					$this->on_render();

					return apply_filters( 'fusion_component_' . $this->shortcode_handle . '_content', $html, $args );

				}


				/**
				 * Apply post per page on search pages.
				 *
				 * @access public
				 * @since 7.1
				 * @return array The attribute array
				 */
				public function attr() {
					$attr = [
						'class' => 'fusion-woo-archives-tb',
					];

					$attr['data-infinite-post-class'] = $this->args['post_type'];

					return $attr;
				}

				/**
				 * Apply post per page on search pages.
				 *
				 * @access public
				 * @param  object $query The WP_Query object.
				 * @return  void
				 */
				public function alter_search_loop( $query ) {
					if ( ! is_admin() && $query->is_main_query() && ( $query->is_search() || $query->is_archive() ) ) {

						$search_override        = Fusion_Template_Builder::get_instance()->get_search_override( $query );
						$has_archives_component = $search_override && has_shortcode( $search_override->post_content, $this->shortcode_handle );

						if ( $has_archives_component ) {
							$pattern = get_shortcode_regex( [ $this->shortcode_handle ] );
							$content = $search_override->post_content;
							if ( preg_match_all( '/' . $pattern . '/s', $search_override->post_content, $matches )
								&& array_key_exists( 2, $matches )
								&& in_array( $this->shortcode_handle, $matches[2], true ) ) {
								$search_atts  = shortcode_parse_atts( $matches[3][0] );
								$number_posts = ( isset( $_GET['product_count'] ) ) ? (int) $_GET['product_count'] : (int) $search_atts['number_posts']; // phpcs:ignore WordPress.Security

								// Use GO value.
								if ( 0 === $number_posts ) {
									$fusion_settings = awb_get_fusion_settings();
									$number_posts    = (int) $fusion_settings->get( 'woo_items' );
								}
								$query->set( 'paged', ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1 );
								$query->set( 'posts_per_page', $number_posts );
							}
						}
					}
				}
			}
		}

		new FusionTB_Woo_Archives();
	}

	/**
	 * Map shortcode to Avada Builder
	 *
	 * @since 3.3
	 */
	function fusion_component_woo_archives() {
		$fusion_settings = awb_get_fusion_settings();

		$builder_status  = function_exists( 'is_fusion_editor' ) && is_fusion_editor();
		$default_orderby = apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby', 'menu_order' ) );
		$default_order   = 'menu_order' === $default_orderby ? 'ASC' : 'DESC';

		fusion_builder_map(
			fusion_builder_frontend_data(
				'FusionTB_Woo_Archives',
				[
					'name'                    => esc_attr__( 'Woo Archives', 'fusion-builder' ),
					'shortcode'               => 'fusion_tb_woo_archives',
					'icon'                    => 'fusiona-woo-archive',
					'component'               => true,
					'templates'               => [ 'content' ],
					'components_per_template' => 1,
					'params'                  => [
						[
							'type'        => 'range',
							'heading'     => esc_attr__( 'Number of Products', 'fusion-builder' ),
							'description' => esc_attr__( 'Select number of products per page.  Set to -1 to display all. Set to 0 to use number of products from Avada > Options > WooCommerce > General WooCommerce.', 'fusion-builder' ),
							'param_name'  => 'number_posts',
							'min'         => '-1',
							'max'         => '50',
							'step'        => '1',
							'value'       => $fusion_settings->get( 'woo_items' ),
							'callback'    => [
								'function' => 'fusion_ajax',
								'action'   => 'get_fusion_tb_woo_archives',
								'ajax'     => true,
							],
						],
						[
							'type'        => 'range',
							'heading'     => esc_attr__( 'Number of Columns', 'fusion-builder' ),
							'description' => esc_attr__( 'Set the number of columns per row.', 'fusion-builder' ),
							'param_name'  => 'columns',
							'value'       => $fusion_settings->get( 'woocommerce_shop_page_columns' ),
							'min'         => '1',
							'max'         => '6',
							'step'        => '1',
						],
						[
							'type'        => 'range',
							'heading'     => esc_attr__( 'Column Spacing', 'fusion-builder' ),
							'description' => esc_attr__( "Insert the amount of spacing between items without 'px'. ex: 40.", 'fusion-builder' ),
							'param_name'  => 'column_spacing',
							'value'       => $fusion_settings->get( 'woocommerce_archive_grid_column_spacing' ),
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
							'type'         => 'tinymce',
							'heading'      => esc_attr__( 'Nothing Found Message', 'fusion-builder' ),
							'description'  => esc_attr__( 'Replacement text when no results are found.', 'fusion-builder' ),
							'param_name'   => 'element_content',
							'value'        => esc_html__( 'Nothing Found', 'fusion-builder' ),
							'placeholder'  => true,
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
								'action'   => 'get_fusion_tb_woo_archives',
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
							'default'     => '',
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
								'action'   => 'get_fusion_tb_woo_archives',
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
					],
					'callback'                => [
						'function' => 'fusion_ajax',
						'action'   => 'get_fusion_tb_woo_archives',
						'ajax'     => true,
					],
				]
			)
		);
	}
	add_action( 'fusion_builder_before_init', 'fusion_component_woo_archives' );
}
