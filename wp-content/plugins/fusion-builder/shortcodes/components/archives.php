<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 2.2.0
 */

if ( fusion_is_element_enabled( 'fusion_blog' ) ) {

	if ( fusion_is_element_enabled( 'fusion_tb_archives' ) ) {

		if ( ! class_exists( 'FusionTB_Archives' ) ) {
			/**
			 * Shortcode class.
			 *
			 * @since 2.2.0
			 */
			class FusionTB_Archives extends Fusion_Component {

				/**
				 * An array of the shortcode arguments.
				 *
				 * @access protected
				 * @since 2.2.0
				 * @var array
				 */
				protected $args;

				/**
				 * Constructor.
				 *
				 * @access public
				 * @since 2.2.0
				 */
				public function __construct() {
					parent::__construct( 'fusion_tb_archives' );

					// Ajax mechanism for query related part.
					add_action( 'wp_ajax_get_fusion_archives', [ $this, 'ajax_query' ] );

					add_filter( 'fusion_tb_component_check', [ $this, 'component_check' ] );

					add_action( 'pre_get_posts', [ $this, 'alter_search_loop' ] );

					add_filter( 'fusion_attr_fusion_tb_archives', [ $this, 'attr' ] );
				}


				/**
				 * Check if component should render
				 *
				 * @access public
				 * @since 2.2
				 * @return boolean
				 */
				public function should_render() {
					return is_search() || is_archive();
				}

				/**
				 * Checks and returns post type for archives component.
				 *
				 * @since  2.2
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
				 * @since 2.0.0
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

					add_filter( 'fusion_blog_shortcode_query_args', [ $this, 'archives_type' ] );
					do_action( 'wp_ajax_get_fusion_blog', $defaults );
				}

				/**
				 * Gets the default values.
				 *
				 * @static
				 * @access public
				 * @since 2.2.0
				 * @return array
				 */
				public static function get_element_defaults() {
					global $post;

					$defaults = FusionSC_Blog::get_element_defaults();

					$defaults['post_type'] = get_post_type( $post );

					return $defaults;
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
					return FusionSC_Blog::get_element_extras();
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
					return FusionSC_Blog::settings_to_extras();
				}

				/**
				 * Renders fusion blog shortcode
				 *
				 * @access public
				 * @since 2.2.0
				 * @return string
				 */
				public function render_blog() {
					global $shortcode_tags;

					return call_user_func( $shortcode_tags['fusion_blog'], $this->args, '', 'fusion_blog' );
				}

				/**
				 * Filters the current query
				 *
				 * @access public
				 * @since 2.2.0
				 * @param array $query The query.
				 * @return array
				 */
				public function fusion_blog_shortcode_query_override( $query ) {
					global $wp_query;

					return $wp_query;
				}

				/**
				 * Render the shortcode
				 *
				 * @access public
				 * @since 2.2.0
				 * @param  array  $args    Shortcode parameters.
				 * @param  string $content Content between shortcode.
				 * @return string          HTML output.
				 */
				public function render( $args, $content = '' ) {
					global $post, $wp_query;

					$this->args = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_tb_archives' );

					if ( isset( $args['padding_top'] ) && '' !== $args['padding_top'] ) {
						$this->args['padding_top'] = $args['padding_top'];
					}
					if ( isset( $args['padding_right'] ) && '' !== $args['padding_right'] ) {
						$this->args['padding_right'] = $args['padding_right'];
					}
					if ( isset( $args['padding_bottom'] ) && '' !== $args['padding_bottom'] ) {
						$this->args['padding_bottom'] = $args['padding_bottom'];
					}
					if ( isset( $args['padding_left'] ) && '' !== $args['padding_left'] ) {
						$this->args['padding_left'] = $args['padding_left'];
					}

					$option = isset( $post->ID ) ? fusion_get_page_option( 'dynamic_content_preview_type', $post->ID ) : '';
					$html   = '<div ' . FusionBuilder::attributes( 'fusion_tb_archives' ) . ' >';

					// Handle empty results.
					if ( ! fusion_is_preview_frame() && ! $post ) {
						$html .= apply_filters( 'fusion_shortcode_content', '<h2 class="fusion-nothing-found">' . $content . '</h2>', 'fusion_tb_archives', $args );

					} elseif ( fusion_is_preview_frame() && ! in_array( $option, [ 'search', 'archives' ], true ) ) {

						// Return notice if Dynamic Content is invalid.
						$html .= apply_filters( 'fusion_component_' . $this->shortcode_handle . '_content', $html, $args );

					} elseif ( ! fusion_is_preview_frame() && $this->should_render() ) {

						// Pass main query to fusion-blog.
						add_filter( 'fusion_blog_shortcode_query_override', [ $this, 'fusion_blog_shortcode_query_override' ] );
						$html .= $this->render_blog();
						remove_filter( 'fusion_blog_shortcode_query_override', [ $this, 'fusion_blog_shortcode_query_override' ] );
					} elseif ( fusion_is_preview_frame() ) {
						add_filter( 'fusion_blog_shortcode_query_args', [ $this, 'archives_type' ] );
						$html .= $this->render_blog();
						remove_filter( 'fusion_blog_shortcode_query_args', [ $this, 'archives_type' ] );
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
						'class' => 'fusion-archives-tb',
					];

					$attr['data-infinite-post-class'] = is_search() ? '' : $this->args['post_type'];

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
						$has_archives_component = $search_override && has_shortcode( $search_override->post_content, 'fusion_tb_archives' );

						if ( $has_archives_component ) {
							$pattern = get_shortcode_regex( [ 'fusion_tb_archives' ] );
							$content = $search_override->post_content;
							if ( preg_match_all( '/' . $pattern . '/s', $search_override->post_content, $matches )
								&& array_key_exists( 2, $matches )
								&& in_array( 'fusion_tb_archives', $matches[2], true ) ) {
								$search_atts = shortcode_parse_atts( $matches[3][0] );
								$query->set( 'paged', ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1 );
								$query->set( 'posts_per_page', $search_atts['number_posts'] );
							}
						}
					}
				}
			}
		}

		new FusionTB_Archives();
	}

	/**
	 * Map shortcode to Avada Builder
	 *
	 * @since 2.2.0
	 */
	function fusion_component_archives() {
		$fusion_settings = awb_get_fusion_settings();

		$builder_status = function_exists( 'is_fusion_editor' ) && is_fusion_editor();

		fusion_builder_map(
			fusion_builder_frontend_data(
				'FusionTB_Archives',
				[
					'name'                    => esc_attr__( 'Archives', 'fusion-builder' ),
					'shortcode'               => 'fusion_tb_archives',
					'icon'                    => 'fusiona-search-results',
					'component'               => true,
					'templates'               => [ 'content' ],
					'components_per_template' => 1,
					'params'                  => [
						[
							'type'        => 'range',
							'heading'     => esc_attr__( 'Posts Per Page', 'fusion-builder' ),
							'description' => esc_attr__( 'Select number of posts per page.  Set to -1 to display all. Set to 0 to use number of posts from Settings > Reading.', 'fusion-builder' ),
							'param_name'  => 'number_posts',
							'value'       => 0,
							'min'         => '-1',
							'max'         => '25',
							'step'        => '1',
							'callback'    => [
								'function' => 'fusion_ajax',
								'action'   => 'get_fusion_archives',
								'ajax'     => true,
							],
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Content Display', 'fusion-builder' ),
							'description' => esc_attr__( 'Controls if the content displays as an excerpt or full content or is completely disabled.', 'fusion-builder' ),
							'param_name'  => 'excerpt',
							'value'       => [
								'yes'  => esc_attr__( 'Excerpt', 'fusion-builder' ),
								'no'   => esc_attr__( 'Full Content', 'fusion-builder' ),
								'hide' => esc_attr__( 'No Text', 'fusion-buider' ),
							],
							'default'     => 'yes',
						],
						[
							'type'        => 'range',
							'heading'     => esc_attr__( 'Excerpt Length', 'fusion-builder' ),
							'description' => esc_attr__( 'Insert the number of words/characters you want to show in the excerpt.', 'fusion-builder' ),
							'param_name'  => 'excerpt_length',
							'value'       => '100',
							'min'         => '0',
							'max'         => '500',
							'step'        => '1',
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
								'pagination'       => esc_html__( 'Pagination', 'fusion-builder' ),
								'infinite'         => esc_html__( 'Infinite Scroll', 'fusion-builder' ),
								'load_more_button' => esc_html__( 'Load More Button', 'fusion-builder' ),
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
						[
							'type'        => 'select',
							'heading'     => esc_attr__( 'Layout', 'fusion-builder' ),
							'description' => esc_attr__( 'Select the layout for the element.', 'fusion-builder' ),
							'param_name'  => 'layout',
							'default'     => 'medium',
							'value'       => [
								'large'            => esc_attr__( 'Large', 'fusion-builder' ),
								'medium'           => esc_attr__( 'Medium', 'fusion-builder' ),
								'large alternate'  => esc_attr__( 'Large Alternate', 'fusion-builder' ),
								'medium alternate' => esc_attr__( 'Medium Alternate', 'fusion-builder' ),
								'grid'             => esc_attr__( 'Grid', 'fusion-builder' ),
								'timeline'         => esc_attr__( 'Timeline', 'fusion-builder' ),
								'masonry'          => esc_attr__( 'Masonry', 'fusion-builder' ),
							],
							'group'       => esc_html__( 'Design', 'fusion-builder' ),
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
							'group'       => esc_html__( 'Design', 'fusion-builder' ),
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
							'group'       => esc_html__( 'Design', 'fusion-builder' ),
						],
						[
							'type'        => 'range',
							'heading'     => esc_attr__( 'Number of Columns', 'fusion-builder' ),
							'description' => __( 'Controls the number of columns for grid layouts.', 'fusion-builder' ),
							'param_name'  => 'blog_grid_columns',
							'value'       => '3',
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
							'group'       => esc_html__( 'Design', 'fusion-builder' ),
						],
						[
							'type'        => 'range',
							'heading'     => esc_attr__( 'Column Spacing', 'fusion-builder' ),
							'description' => esc_attr__( 'Controls the column spacing.', 'fusion-builder' ),
							'param_name'  => 'blog_grid_column_spacing',
							'value'       => '10',
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
							'group'       => esc_html__( 'Design', 'fusion-builder' ),
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
							'group'       => esc_html__( 'Design', 'fusion-builder' ),
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Show Thumbnail', 'fusion-builder' ),
							'description' => esc_attr__( 'Displays featured image.', 'fusion-builder' ),
							'param_name'  => 'thumbnail',
							'default'     => 'yes',
							'value'       => [
								'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
								'no'  => esc_attr__( 'No', 'fusion-builder' ),
							],
							'group'       => esc_html__( 'Design', 'fusion-builder' ),
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
							'group'       => esc_html__( 'Design', 'fusion-builder' ),
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
							'group'       => esc_html__( 'Design', 'fusion-builder' ),
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
							'group'       => esc_html__( 'Design', 'fusion-builder' ),
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
							'group'       => esc_html__( 'Design', 'fusion-builder' ),
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
							'group'       => esc_html__( 'Design', 'fusion-builder' ),
						],
						[
							'type'             => 'dimension',
							'remove_from_atts' => true,
							'heading'          => esc_attr__( 'Grid Text Padding ', 'fusion-builder' ),
							'description'      => esc_attr__( 'Controls the padding for text when using grid / masonry or timeline layout. Enter values including any valid CSS unit, ex: 30px, 25px, 0px, 25px.', 'fusion-builder' ),
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
							'group'            => esc_html__( 'Design', 'fusion-builder' ),
						],
					],
					'callback'                => [
						'function' => 'fusion_ajax',
						'action'   => 'get_fusion_archives',
						'ajax'     => true,
					],
				]
			)
		);
	}
	add_action( 'fusion_builder_before_init', 'fusion_component_archives' );
}
