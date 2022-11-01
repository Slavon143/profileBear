<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 2.2
 */

if ( fusion_is_element_enabled( 'fusion_tb_related' ) ) {

	if ( ! class_exists( 'FusionTB_Related' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 2.2
		 */
		class FusionTB_Related extends Fusion_Component {

			/**
			 * $fusion_settings object.
			 *
			 * @access protected
			 * @since 2.2
			 * @var object
			 */
			protected $fusion_settings = null;

			/**
			 * The internal container counter.
			 *
			 * @access private
			 * @since 2.2
			 * @var int
			 */
			private $counter = 1;

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 2.2
			 * @var array
			 */
			protected $args;

			/**
			 * Target post's post type.
			 *
			 * @access protected
			 * @since 2.2
			 * @var string
			 */
			protected $post_type;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 2.2
			 */
			public function __construct() {
				parent::__construct( 'fusion_tb_related' );
				add_filter( 'fusion_attr_fusion_tb_related-shortcode', [ $this, 'attr' ] );

				$this->fusion_settings = awb_get_fusion_settings();

				add_filter( 'fusion_attr_related-component-carousel', [ $this, 'carousel_attr' ] );

				// Ajax mechanism for query related part.
				add_action( 'wp_ajax_get_fusion_related_posts', [ $this, 'ajax_query' ] );
			}


			/**
			 * Check if component should render
			 *
			 * @access public
			 * @since 2.2
			 * @return boolean
			 */
			public function should_render() {
				return is_singular() || wp_is_json_request();
			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 2.2
			 * @return array
			 */
			public static function get_element_defaults() {
				$fusion_settings = awb_get_fusion_settings();
				return [
					'number_related_posts'         => $fusion_settings->get( 'number_related_posts' ),
					'related_posts_image_size'     => $fusion_settings->get( 'related_posts_image_size' ),
					'related_posts_columns'        => $fusion_settings->get( 'related_posts_columns' ),
					'related_posts_layout'         => $fusion_settings->get( 'related_posts_layout' ),
					'related_posts_navigation'     => $fusion_settings->get( 'related_posts_navigation' ),
					'related_posts_autoplay'       => $fusion_settings->get( 'related_posts_autoplay' ),
					'related_posts_swipe'          => $fusion_settings->get( 'related_posts_swipe' ),
					'related_posts_column_spacing' => $fusion_settings->get( 'related_posts_column_spacing' ),
					'related_posts_swipe_items'    => $fusion_settings->get( 'related_posts_swipe_items' ),
					'heading_enable'               => 'yes',
					'heading_size'                 => '3',
					'margin_bottom'                => '',
					'margin_left'                  => '',
					'margin_right'                 => '',
					'margin_top'                   => '',
					'hide_on_mobile'               => fusion_builder_default_visibility( 'string' ),
					'class'                        => '',
					'id'                           => '',
					'animation_type'               => '',
					'animation_direction'          => 'down',
					'animation_speed'              => '0.1',
					'animation_offset'             => $fusion_settings->get( 'animation_offset' ),
				];
			}

			/**
			 * Maps settings to param variables.
			 *
			 * @static
			 * @access public
			 * @since 2.2
			 * @return array
			 */
			public static function settings_to_params() {
				return [
					'number_related_posts'         => 'number_related_posts',
					'related_posts_image_size'     => 'related_posts_image_size',
					'related_posts_columns'        => 'related_posts_columns',
					'related_posts_navigation'     => 'related_posts_navigation',
					'related_posts_navigation'     => 'related_posts_navigation',
					'related_posts_autoplay'       => 'related_posts_autoplay',
					'related_posts_swipe'          => 'related_posts_swipe',
					'related_posts_column_spacing' => 'related_posts_column_spacing',
					'related_posts_swipe_items'    => 'related_posts_swipe_items',
					'animation_offset'             => 'animation_offset',
				];
			}

			/**
			 * Used to set any other variables for use on front-end editor template.
			 *
			 * @static
			 * @access public
			 * @since 2.2
			 * @return array
			 */
			public static function get_element_extras() {
				$fusion_settings = awb_get_fusion_settings();
				return [
					'title_margin'       => $fusion_settings->get( 'title_margin' ),
					'title_border_color' => $fusion_settings->get( 'title_border_color' ),
					'title_style_type'   => $fusion_settings->get( 'title_style_type' ),
					'content_width'      => apply_filters( 'fusion_library_content_width', 1170 ),
				];
			}

			/**
			 * Maps settings to extra variables.
			 *
			 * @static
			 * @access public
			 * @since 2.2
			 * @return array
			 */
			public static function settings_to_extras() {

				return [
					'title_margin'       => 'title_margin',
					'title_border_color' => 'title_border_color',
					'title_style_type'   => 'title_style_type',
				];
			}

			/**
			 * Gets the query data.
			 *
			 * @access public
			 * @since 2.2
			 * @return void
			 */
			public function ajax_query() {
				check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );

				if ( isset( $_POST['model'] ) && isset( $_POST['model']['params'] ) && ! apply_filters( 'fusion_builder_live_request', false ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$defaults = $_POST['model']['params']; // phpcs:ignore WordPress.Security
					fusion_set_live_data();
					add_filter( 'fusion_builder_live_request', '__return_true' );

					$this->emulate_post();

					$this->post_type = get_post_type( $this->get_target_post() );

					$fusion_query = $this->query( $defaults );

					$this->restore_post();

					// Build live query response.
					$return_data = [];

					// There are no related posts, return placeholder.
					if ( ! $fusion_query->have_posts() ) {
						echo wp_json_encode( $return_data );
						wp_die();
					}

					/**
					 * Get the correct image size.
					 */
					$featured_image_size = ( 'cropped' === $defaults['related_posts_image_size'] ) ? 'fixed' : 'full';
					$data_image_size     = ( 'cropped' === $defaults['related_posts_image_size'] ) ? 'fixed' : 'auto';

					/**
					 * Loop through related posts.
					 */
					while ( $fusion_query->have_posts() ) :
						$fusion_query->the_post();

						$post_id = get_the_ID(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
						// $content .= '<li class="fusion-carousel-item"' . $carousel_item_css . ' >'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

						$display_post_title = 'title_on_rollover' === $defaults['related_posts_layout'] ? 'default' : 'disable';

						if ( 'auto' === $data_image_size ) {
							fusion_library()->images->set_grid_image_meta(
								[
									'layout'  => 'related-posts',
									'columns' => $defaults['related_posts_columns'],
								]
							);
						}

						fusion_library()->images->set_grid_image_meta( [] );

						ob_start();
						comments_popup_link( __( '0 Comments', 'fusion-builder' ), __( '1 Comment', 'fusion-builder' ), __( '% Comments', 'fusion-builder' ) );
						$comments = ob_get_clean();

						$date_format = $this->fusion_settings->get( 'date_format' );
						$date_format = $date_format ? $date_format : get_option( 'date_format' );

						$return_data['related_items'][] = [
							'featured_image' => fusion_render_first_featured_image_markup( $post_id, $featured_image_size, get_permalink( $post_id ), true, false, false, 'disable', $display_post_title, 'related' ),
							'link'           => esc_url_raw( get_permalink( get_the_ID() ) ),
							'title_attr'     => esc_attr( get_the_title() ),
							'title'          => get_the_title(),
							'date'           => get_the_time( $date_format, $post_id ),
							'comments_open'  => comments_open( $post_id ),
							'comments'       => $comments,
						];
					endwhile;

					wp_reset_postdata();

					echo wp_json_encode( $return_data );
					wp_die();
				}
			}

			/**
			 * WIP: Gets the query data.
			 *
			 * @access public
			 * @since 2.0.0
			 * @param array $defaults The default args.
			 * @return array
			 */
			public function query( $defaults ) {

				// Return if there's a query override.
				$query_override = apply_filters( 'fusion_blog_shortcode_query_override', null, $defaults );

				if ( $query_override ) {
					return $query_override;
				}

				$number_related_posts = $defaults['number_related_posts'];
				$number_related_posts = ( '0' == $number_related_posts ) ? '-1' : $number_related_posts; // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison

				$fusion_query = 'post' === $this->post_type
								? $this->get_related_posts( get_the_ID(), $number_related_posts )
								: $this->get_custom_posttype_related_posts( get_the_ID(), $number_related_posts, $this->post_type );

				return $fusion_query;
			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 2.2
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {

				// Set defaults.
				$this->args = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_tb_related' );

				// Validate and normalize args.
				$this->validate_args();

				$this->emulate_post();

				$this->post_type = get_post_type( $this->get_target_post() );

				// Set the needed variables according to post type.
				$main_heading = esc_html__( 'Related Posts', 'fusion-builder' );

				if ( 'avada_portfolio' === $this->post_type ) {
					$main_heading = esc_html__( 'Related Projects', 'fusion-builder' );
				} elseif ( 'avada_faq' === $this->post_type ) {
					$main_heading = esc_html__( 'Related Faqs', 'fusion-builder' );
				}

				$fusion_query = $this->query( $this->args );

				// If there are related posts, display them.
				if ( isset( $fusion_query ) && $fusion_query->have_posts() ) {

					$content .= '<section ' . FusionBuilder::attributes( 'fusion_tb_related-shortcode' ) . '>';

					if ( 'yes' === $this->args['heading_enable'] ) {
						$content .= fusion_render_title( $this->args['heading_size'], apply_filters( 'fusion_related_posts_heading_text', $main_heading, $this->post_type ) );
					}

					/**
					 * Get the correct image size.
					 */
					$featured_image_size = ( 'cropped' === $this->args['related_posts_image_size'] ) ? 'fixed' : 'full';
					$data_image_size     = ( 'cropped' === $this->args['related_posts_image_size'] ) ? 'fixed' : 'auto';
					$carousel_item_css   = '';

					if ( count( $fusion_query->posts ) < $this->args['related_posts_columns'] ) {
						$carousel_item_css = ( apply_filters( 'fusion_library_content_width', 1170 ) - intval( $this->args['related_posts_column_spacing'] ) * ( intval( $this->args['related_posts_columns'] ) - 1 ) ) / intval( $this->args['related_posts_columns'] );
						$carousel_item_css = ' style="max-width: ' . esc_attr( $carousel_item_css ) . 'px;"';
					}

					$content .= '<div ' . FusionBuilder::attributes( 'related-component-carousel' ) . '>';
					$content .= '<div class="fusion-carousel-positioner">';
					$content .= '<ul class="fusion-carousel-holder">';

					/**
					 * Loop through related posts.
					 */
					while ( $fusion_query->have_posts() ) :
						$fusion_query->the_post();
						$post_id  = get_the_ID(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
						$content .= '<li class="fusion-carousel-item"' . $carousel_item_css . ' >'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						$content .= '<div class="fusion-carousel-item-wrapper">';

						$display_post_title = 'title_on_rollover' === $this->args['related_posts_layout'] ? 'default' : 'disable';

						if ( 'auto' === $data_image_size ) {
							fusion_library()->images->set_grid_image_meta(
								[
									'layout'  => 'related-posts',
									'columns' => $this->args['related_posts_columns'],
								]
							);
						}

						$content .= fusion_render_first_featured_image_markup( $post_id, $featured_image_size, get_permalink( $post_id ), true, false, false, 'disable', $display_post_title, 'related' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

						fusion_library()->images->set_grid_image_meta( [] );
						if ( 'title_below_image' === $this->args['related_posts_layout'] ) { // Title on rollover layout.

							/**
							 * Get the post title.
							 */

							$content .= '<h4 class="fusion-carousel-title">';
							$content .= '<a class="fusion-related-posts-title-link" href="' . esc_url_raw( get_permalink( get_the_ID() ) ) . '" target="_self" title="' . esc_attr( get_the_title() ) . '">' . get_the_title() . '</a>';
							$content .= '</h4>';

							$content .= '<div class="fusion-carousel-meta">';
							$content .= '<span class="fusion-date">' . esc_attr( get_the_time( $this->fusion_settings->get( 'date_format' ), $post_id ) ) . '</span>';

							if ( comments_open( $post_id ) ) {
								$content .= '<span class="fusion-inline-sep">|</span>';
								$content .= '<span>';
								ob_start();
								comments_popup_link( __( '0 Comments', 'fusion-builder' ), __( '1 Comment', 'fusion-builder' ), __( '% Comments', 'fusion-builder' ) );
								$content .= ob_get_clean();
								$content .= '</span>';
							}
							$content .= '</div><!-- fusion-carousel-meta -->';
						}

							$content .= '</div><!-- fusion-carousel-item-wrapper -->';
							$content .= '</li>';
					endwhile;
					$content .= '</ul><!-- fusion-carousel-holder -->';

					/**
					 * Add navigation if needed.
					 */
					if ( true === $this->args['related_posts_navigation'] ) {
						$content .= awb_get_carousel_nav();
					}

					$content .= '</div><!-- fusion-carousel-positioner -->';
					$content .= '</div><!-- fusion-carousel -->';
					$content .= '</section><!-- related-posts -->';

					wp_reset_postdata();

				} elseif ( isset( $_POST['action'] ) && 'get_shortcode_render' === $_POST['action'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

					// Add preview for Live Builder.
					$content .= $this->get_placeholder();
				} elseif ( fusion_is_preview_frame() ) {
					$content .= '';
				}

				$this->restore_post();

				$this->counter++;

				$this->on_render();

				return apply_filters( 'fusion_component_' . $this->shortcode_handle . '_content', $content, $args );
			}

			/**
			 * Validates args.
			 *
			 * @since 2.2
			 */
			protected function validate_args() {
				$this->args['related_posts_navigation'] = ( 'yes' === $this->args['related_posts_navigation'] || '1' === $this->args['related_posts_navigation'] ) ? true : false;
				$this->args['related_posts_autoplay']   = ( 'yes' === $this->args['related_posts_autoplay'] || '1' === $this->args['related_posts_autoplay'] ) ? true : false;
				$this->args['related_posts_swipe']      = ( 'yes' === $this->args['related_posts_swipe'] || '1' === $this->args['related_posts_swipe'] ) ? true : false;
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 2.2
			 * @return array
			 */
			public function attr() {
				$attr = [
					'class' => 'related-posts single-related-posts fusion-related-tb fusion-related-tb-' . $this->counter,
					'style' => '',
				];

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
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
			 * Get related posts by category
			 *
			 * @param  integer $post_id      Current post id.
			 * @param  integer $number_posts Number of posts to fetch.
			 * @return object                Object with posts info.
			 */
			protected function get_related_posts( $post_id, $number_posts = -1 ) {

				$args = '';

				$number_posts = (int) $number_posts;
				if ( 0 === $number_posts ) {
					$query = new WP_Query();
					return $query;
				}

				$args = wp_parse_args(
					$args,
					apply_filters(
						'fusion_related_posts_query_args',
						[
							'category__in'        => wp_get_post_categories( $post_id ),
							'ignore_sticky_posts' => 0,
							'posts_per_page'      => $number_posts,
							'post__not_in'        => [ $post_id ],
							'post_status'         => 'publish',
						]
					)
				);

				// If placeholder images are disabled,
				// add the _thumbnail_id meta key to the query to only retrieve posts with featured images.
				if ( ! $this->fusion_settings->get( 'featured_image_placeholder' ) ) {
					$args['meta_key'] = '_thumbnail_id'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				}

				return fusion_cached_query( $args );
			}

			/**
			 * Get related posts by a custom post type category taxonomy.
			 *
			 * @param  integer $post_id      Current post id.
			 * @param  integer $number_posts Number of posts to fetch.
			 * @param  string  $post_type    The custom post type that should be used.
			 * @return object                Object with posts info.
			 */
			protected function get_custom_posttype_related_posts( $post_id, $number_posts = 8, $post_type = 'avada_portfolio' ) {

				$query = new WP_Query();

				$args = '';

				$number_posts = (int) $number_posts;
				if ( 0 === $number_posts || ! $number_posts ) {
					return $query;
				}

				$post_type = str_replace( 'avada_', '', $post_type );

				$item_cats = get_the_terms( $post_id, $post_type . '_category' );

				$item_array = [];
				if ( $item_cats && ! is_wp_error( $item_cats ) ) {
					foreach ( $item_cats as $item_cat ) {
						$item_array[] = $item_cat->term_id;
					}
				}

				if ( ! empty( $item_array ) ) {
					$args = wp_parse_args(
						$args,
						apply_filters(
							'fusion_related_posts_query_args',
							[
								'ignore_sticky_posts' => 0,
								'posts_per_page'      => $number_posts,
								'post__not_in'        => [ $post_id ],
								'post_type'           => 'avada_' . $post_type,
								'post_status'         => 'publish',
								'tax_query'           => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
									[
										'field'    => 'id',
										'taxonomy' => $post_type . '_category',
										'terms'    => $item_array,
									],
								],
							]
						)
					);

					// If placeholder images are disabled, add the _thumbnail_id meta key to the query to only retrieve posts with featured images.
					if ( ! $this->fusion_settings->get( 'featured_image_placeholder' ) ) {
						$args['meta_key'] = '_thumbnail_id'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					}

					$query = fusion_cached_query( apply_filters( 'fusion_related_posts_args', $args ) );

				}

				return $query;
			}

			/**
			 * Builds the carousel wrapper attributes array.
			 *
			 * @access public
			 * @since 2.2
			 * @return array
			 */
			public function carousel_attr() {

				$attr['class'] = 'fusion-carousel';
				if ( 'title_below_image' === $this->args['related_posts_layout'] ) {
					$attr['class'] .= ' fusion-carousel-title-below-image';
				}

				$attr['data-imagesize'] = ( 'cropped' === $this->args['related_posts_image_size'] ) ? 'fixed' : 'auto';

				/**
				 * Set the meta content variable.
				 */
				$attr['data-metacontent'] = ( 'title_on_rollover' === $this->args['related_posts_layout'] ) ? 'no' : 'yes';

				/**
				 * Set the autoplay variable.
				 */
				$attr['data-autoplay'] = ( $this->args['related_posts_autoplay'] ) ? 'yes' : 'no';

				/**
				 * Set the touch scroll variable.
				 */
				$attr['data-touchscroll'] = ( $this->args['related_posts_swipe'] ) ? 'yes' : 'no';

				$attr['data-columns']     = $this->args['related_posts_columns'];
				$attr['data-itemmargin']  = intval( $this->args['related_posts_column_spacing'] ) . 'px';
				$attr['data-itemwidth']   = 180;
				$attr['data-touchscroll'] = 'yes';

				$related_posts_swipe_items = $this->args['related_posts_swipe_items'];
				$related_posts_swipe_items = ( 0 == $related_posts_swipe_items ) ? '' : $related_posts_swipe_items; // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
				$attr['data-scrollitems']  = $related_posts_swipe_items;

				return $attr;
			}

			/**
			 * Get 'no related posts' placeholder.
			 *
			 * @since 2.2
			 * @return string
			 */
			protected function get_placeholder() {
				return '<div class="fusion-builder-placeholder">' . esc_html__( 'There are no related posts.', 'fusion-builder' ) . '</div>';
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
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/components/related.min.css' );
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/image-carousel.min.css' );
			}
		}

	}

	new FusionTB_Related();
}

/**
 * Map shortcode to Avada Builder
 *
 * @since 2.2
 */
function fusion_component_related() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionTB_Related',
			[
				'name'                    => esc_html__( 'Related Posts', 'fusion-builder' ),
				'shortcode'               => 'fusion_tb_related',
				'icon'                    => 'fusiona-related-posts',
				'component'               => true,
				'templates'               => [ 'content' ],
				'components_per_template' => 1,
				'callback'                => [
					'function' => 'fusion_ajax',
					'action'   => 'get_fusion_related_posts',
					'ajax'     => true,
				],
				'params'                  => [
					[
						'heading'     => esc_html__( 'Layout', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the layout style for related posts and related projects.', 'fusion-builder' ),
						'param_name'  => 'related_posts_layout',
						'default'     => 'title_on_rollover',
						'type'        => 'select',
						'value'       => [
							'title_on_rollover' => esc_html__( 'Title on rollover', 'fusion-builder' ),
							'title_below_image' => esc_html__( 'Title below image', 'fusion-builder' ),
						],
					],
					[
						'heading'     => esc_html__( 'Number of Related Posts', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the number of related posts and projects that display on a single post.', 'fusion-builder' ),
						'param_name'  => 'number_related_posts',
						'value'       => '',
						'default'     => $fusion_settings->get( 'number_related_posts' ),
						'type'        => 'range',
						'min'         => '0',
						'max'         => '30',
						'step'        => '1',
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_related_posts',
							'ajax'     => true,
						],
					],
					[
						'heading'     => esc_html__( 'Maximum Columns', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the number of columns for the related posts and projects layout.', 'fusion-builder' ),
						'param_name'  => 'related_posts_columns',
						'value'       => '',
						'default'     => $fusion_settings->get( 'related_posts_columns' ),
						'type'        => 'range',
						'min'         => '1',
						'max'         => '6',
						'step'        => '1',
					],
					[
						'heading'     => esc_html__( 'Column Spacing', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the amount of spacing between columns for the related posts.', 'fusion-builder' ),
						'param_name'  => 'related_posts_column_spacing',
						'value'       => '',
						'default'     => $fusion_settings->get( 'related_posts_column_spacing' ),
						'type'        => 'range',
						'min'         => '0',
						'step'        => '1',
						'max'         => '300',
					],
					[
						'heading'     => esc_html__( 'Image Size', 'fusion-builder' ),
						'description' => esc_html__( 'Controls if the featured image size is fixed (cropped) or auto (full image ratio) for related posts. IMPORTANT: Fixed works best with a standard 940px site width. Auto works best with larger site widths.', 'fusion-builder' ),
						'param_name'  => 'related_posts_image_size',
						'default'     => '',
						'type'        => 'radio_button_set',
						'value'       => [
							''        => esc_html__( 'Default', 'fusion-builder' ),
							'cropped' => esc_html__( 'Fixed', 'fusion-builder' ),
							'full'    => esc_html__( 'Auto', 'fusion-builder' ),
						],
					],
					[
						'heading'     => esc_html__( 'Autoplay', 'fusion-builder' ),
						'description' => esc_html__( 'Turn on to autoplay the related posts carousel.', 'fusion-builder' ),
						'param_name'  => 'related_posts_autoplay',
						'default'     => '',
						'type'        => 'radio_button_set',
						'value'       => [
							''    => esc_html__( 'Default', 'fusion-builder' ),
							'yes' => esc_html__( 'Yes', 'fusion-builder' ),
							'not' => esc_html__( 'No', 'fusion-builder' ),
						],
					],
					[
						'heading'     => esc_html__( 'Show Navigation', 'fusion-builder' ),
						'description' => esc_html__( 'Turn on to display navigation arrows on the carousel.', 'fusion-builder' ),
						'param_name'  => 'related_posts_navigation',
						'default'     => '',
						'type'        => 'radio_button_set',
						'value'       => [
							''    => esc_html__( 'Default', 'fusion-builder' ),
							'yes' => esc_html__( 'Yes', 'fusion-builder' ),
							'not' => esc_html__( 'No', 'fusion-builder' ),
						],
					],
					[
						'heading'     => esc_html__( 'Mouse Scroll', 'fusion-builder' ),
						'description' => esc_html__( 'Turn on to enable mouse drag control on the carousel.', 'fusion-builder' ),
						'param_name'  => 'related_posts_swipe',
						'default'     => '',
						'type'        => 'radio_button_set',
						'value'       => [
							''    => esc_html__( 'Default', 'fusion-builder' ),
							'yes' => esc_html__( 'Yes', 'fusion-builder' ),
							'not' => esc_html__( 'No', 'fusion-builder' ),
						],
					],
					[
						'heading'     => esc_html__( 'Scroll Items', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the number of items that scroll at one time. Set to 0 to scroll the number of visible items.', 'fusion-builder' ),
						'param_name'  => 'related_posts_swipe_items',
						'value'       => '',
						'default'     => $fusion_settings->get( 'related_posts_swipe_items' ),
						'type'        => 'range',
						'min'         => '0',
						'max'         => '15',
						'step'        => '1',
					],
					[
						'type'        => 'checkbox_button_set',
						'heading'     => esc_html__( 'Element Visibility', 'fusion-builder' ),
						'param_name'  => 'hide_on_mobile',
						'value'       => fusion_builder_visibility_options( 'full' ),
						'default'     => fusion_builder_default_visibility( 'array' ),
						'description' => esc_html__( 'Choose to show or hide the element on small, medium or large screens. You can choose more than one at a time.', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_html__( 'CSS Class', 'fusion-builder' ),
						'description' => esc_html__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
						'param_name'  => 'class',
						'value'       => '',
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_html__( 'CSS ID', 'fusion-builder' ),
						'description' => esc_html__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
						'param_name'  => 'id',
						'value'       => '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_html__( 'Enable Heading', 'fusion-builder' ),
						'description' => esc_html__( 'Turn on if you want to display default heading.', 'fusion-builder' ),
						'param_name'  => 'heading_enable',
						'default'     => 'yes',
						'value'       => [
							'yes' => esc_html__( 'Yes', 'fusion-builder' ),
							'no'  => esc_html__( 'No', 'fusion-builder' ),
						],
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_html__( 'HTML Heading Size', 'fusion-builder' ),
						'description' => esc_html__( 'Choose the size of the HTML heading that should be used, h1-h6.', 'fusion-builder' ),
						'param_name'  => 'heading_size',
						'value'       => [
							'1' => 'H1',
							'2' => 'H2',
							'3' => 'H3',
							'4' => 'H4',
							'5' => 'H5',
							'6' => 'H6',
						],
						'default'     => '3',
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'heading_enable',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					'fusion_margin_placeholder'    => [
						'param_name' => 'margin',
						'value'      => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
						],
					],
					'fusion_animation_placeholder' => [
						'preview_selector' => '.fusion-related-tb',
					],
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_component_related' );
