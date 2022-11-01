<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_events' ) && class_exists( 'Tribe__Events__Main' ) ) {

	if ( ! class_exists( 'FusionSC_FusionEvents' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_FusionEvents extends Fusion_Element {

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array
			 */
			protected $args;

			/**
			 * The events counter.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $fusion_events_counter = 1;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_shortcode( 'fusion_events', [ $this, 'render' ] );

				add_filter( 'fusion_attr_events-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_events-shortcode-columns', [ $this, 'column_attr' ] );
				add_filter( 'fusion_events_shortcode_content', [ $this, 'get_post_content' ], 10, 3 );

				// Ajax mechanism for query related part.
				add_action( 'wp_ajax_get_fusion_events', [ $this, 'ajax_query' ] );
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

				$theme_option_content_padding = $fusion_settings->get( 'events_content_padding' );

				$padding_values           = [];
				$padding_values['top']    = Fusion_Sanitize::size( $theme_option_content_padding['top'] );
				$padding_values['right']  = Fusion_Sanitize::size( $theme_option_content_padding['right'] );
				$padding_values['bottom'] = Fusion_Sanitize::size( $theme_option_content_padding['bottom'] );
				$padding_values['left']   = Fusion_Sanitize::size( $theme_option_content_padding['left'] );

				return [
					'column_spacing'    => ( '' !== $fusion_settings->get( 'events_column_spacing' ) ) ? $fusion_settings->get( 'events_column_spacing' ) : '-1',
					'content_length'    => ( '' !== $fusion_settings->get( 'events_content_length' ) ) ? $fusion_settings->get( 'events_content_length' ) : 'no_text',
					'excerpt_length'    => ( '' !== $fusion_settings->get( 'excerpt_length_events' ) ) ? $fusion_settings->get( 'excerpt_length_events' ) : 55,
					'hide_on_mobile'    => fusion_builder_default_visibility( 'string' ),
					'class'             => '',
					'id'                => '',
					'cat_slug'          => '',
					'columns'           => '4',
					'number_posts'      => ( '' !== $fusion_settings->get( 'events_per_page' ) ) ? $fusion_settings->get( 'events_per_page' ) : '4',
					'order'             => 'ASC',
					'pagination'        => 'no',
					'past_events'       => 'no',
					'picture_size'      => 'cover',
					'strip_html'        => ( '' !== $fusion_settings->get( 'events_strip_html_excerpt' ) ) ? $fusion_settings->get( 'events_strip_html_excerpt' ) : 'yes',
					'padding_top'       => $padding_values['top'],
					'padding_right'     => $padding_values['right'],
					'padding_bottom'    => $padding_values['bottom'],
					'padding_left'      => $padding_values['left'],
					'content_alignment' => '',
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
					'events_content_padding[top]'    => 'padding_top',
					'events_content_padding[right]'  => 'padding_right',
					'events_content_padding[bottom]' => 'padding_bottom',
					'events_content_padding[left]'   => 'padding_left',
					'events_column_spacing'          => 'column_spacing',
					'events_content_length'          => 'content_length',
					'excerpt_length_events'          => 'excerpt_length',
					'events_per_page'                => 'number_posts',
					'events_strip_html_excerpt'      => [
						'param'    => 'strip_html',
						'callback' => 'toYes',
					],
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
					'pagination_global'                 => apply_filters( 'fusion_builder_events_pagination', 'no' ),
					'pagination_range_global'           => apply_filters( 'fusion_pagination_size', $fusion_settings->get( 'pagination_range' ) ),
					'pagination_start_end_range_global' => apply_filters( 'fusion_pagination_start_end_size', $fusion_settings->get( 'pagination_start_end_range' ) ),
					'load_more_text'                    => apply_filters( 'avada_load_more_events_name', esc_attr__( 'Load More Events', 'fusion-builder' ) ),
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
					'pagination_range'           => 'pagination_range_global',
					'pagination_start_end_range' => 'pagination_start_end_range_global',
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
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @param array $defaults The default args.
			 * @return array|Object
			 */
			public static function query( $defaults ) {

				$fusion_settings = awb_get_fusion_settings();
				$live_request    = false;

				// From Ajax Request.
				if ( isset( $_POST['model'] ) && isset( $_POST['model']['params'] ) && ! apply_filters( 'fusion_builder_live_request', false ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$return_data  = [];
					$defaults     = wp_unslash( $_POST['model']['params'] ); // phpcs:ignore WordPress.Security
					$live_request = true;
					add_filter( 'fusion_builder_live_request', '__return_true' );
				}

				// Set number of posts to TO value if its blank.
				$number_of_posts = ( '' !== $defaults['number_posts'] ) ? $defaults['number_posts'] : $fusion_settings->get( 'events_per_page' );
				$number_of_posts = '0' === $number_of_posts ? get_option( 'posts_per_page' ) : $number_of_posts;

				// Check if there is paged content.
				$paged = 1;
				if ( 'no' !== $defaults['pagination'] ) {
					$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
					if ( is_front_page() ) {
						$paged = ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1;
					}
				}

				$args = [
					'post_type'      => 'tribe_events',
					'paged'          => $paged,
					'posts_per_page' => $number_of_posts,
					'order'          => $defaults['order'],
					'orderby'        => 'meta_value',
					'meta_key'       => '_EventStartDate', // phpcs:ignore WordPress.DB.SlowDBQuery
					'meta_type'      => 'DATETIME',
				];

				if ( 'no' === $defaults['past_events'] ) {
					$current_time       = current_time( 'Y-m-d H:i:s' );
					$args['meta_query'] = [  // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
						'relation' => 'OR',
						[
							'key'     => '_EventStartDate',
							'value'   => $current_time,
							'compare' => '>=',
						],
						[
							'key'     => '_EventEndDate',
							'value'   => $current_time,
							'compare' => '>=',
						],
					];

				} else {
					$args['eventDisplay'] = 'custom';
				}

				if ( $defaults['cat_slug'] ) {
					$terms             = explode( ',', $defaults['cat_slug'] );
					$args['tax_query'] = [
						[
							'taxonomy' => 'tribe_events_cat',
							'field'    => 'slug',
							'terms'    => array_map( 'trim', $terms ),
						],
					];
				}

				if ( ! $live_request ) {
					return fusion_cached_query( $args );
				}

				// Ajax returns protected posts, but we just want published.
				$args['post_status'] = 'publish';

				wp_reset_postdata();
				// Anything beyond here is for live preview.
				$events = fusion_cached_query( $args );

				if ( ! $events->have_posts() ) {
					$return_data['placeholder'] = fusion_builder_placeholder( 'tribe_events', 'events' );
					echo wp_json_encode( $return_data );
					wp_die();
				}

				$return_data['ec_hover_type'] = $fusion_settings->get( 'ec_hover_type' );

				while ( $events->have_posts() ) {
					$events->the_post();

					$thumbnail = '';
					$post_id   = get_the_ID();

					if ( has_post_thumbnail( $post_id ) ) {
						if ( 'auto' === $defaults['picture_size'] ) {
							fusion_library()->images->set_grid_image_meta(
								[
									'layout'       => 'grid',
									'columns'      => $defaults['columns'],
									'gutter_width' => $defaults['column_spacing'],
								]
							);

							$thumbnail = get_the_post_thumbnail( $post_id, 'full' );

							fusion_library()->images->set_grid_image_meta( [] );
						} else {
							$thumbnail = '<span class="tribe-events-event-image" style="background-image: url(' . get_the_post_thumbnail_url( $post_id ) . '); -webkit-background-size: cover; background-size: cover; background-position: center center;"></span>';
						}
					} elseif ( class_exists( 'Tribe__Events__Pro__Main' ) ) {
						$thumb_url = esc_url( trailingslashit( Tribe__Events__Pro__Main::instance()->pluginUrl ) . 'src/resources/images/tribe-related-events-placeholder.png' );

						if ( 'auto' === $defaults['picture_size'] ) {
							$title     = the_title_attribute(
								[
									'echo' => false,
									'post' => $post_id,
								]
							);
							$thumbnail = '<img class="fusion-events-placeholder" src="' . $thumb_url . '" alt="' . $title . '" />';
						} else {
							$thumbnail = '<span class="tribe-events-event-image" style="background-image: url(' . $thumb_url . '); -webkit-background-size: cover; background-size: cover; background-position: center center;"></span>';
						}
					}

					// No image set thumbnail.
					if ( ! $thumbnail ) {
						ob_start();
						do_action( 'fusion_placeholder_image', 'fixed' );
						$placeholder = ob_get_clean();
						$thumbnail   = str_replace( 'fusion-placeholder-image', ' fusion-placeholder-image tribe-events-event-image', $placeholder );
					}

					$content = fusion_get_content_data( 'fusion_events' );

					$return_data['paged']         = $paged;
					$return_data['max_num_pages'] = $events->max_num_pages;

					$return_data['posts'][] = [
						'thumbnail' => $thumbnail,
						'title'     => get_the_title(),
						'permalink' => get_the_permalink(),
						'tribe_events_event_schedule_details' => tribe_events_event_schedule_details(),
						'content'   => $content,
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

				$html     = '';
				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_events' );

				$padding_values           = [];
				$padding_values['top']    = $defaults['padding_top'];
				$padding_values['right']  = $defaults['padding_right'];
				$padding_values['bottom'] = $defaults['padding_bottom'];
				$padding_values['left']   = $defaults['padding_left'];
				$content_padding          = implode( ' ', $padding_values );

				$this->args = $defaults;

				if ( class_exists( 'Tribe__Events__Main' ) ) {

					$events = $this->query( $defaults );

					extract( $defaults );

					if ( ! $events->have_posts() ) {
						$this->fusion_events_counter++;
						return fusion_builder_placeholder( 'tribe_events', 'events' );
					}

					if ( $events->have_posts() ) {
						$html   .= '<div ' . FusionBuilder::attributes( 'events-shortcode' ) . '>';
						$html   .= '<div class="fusion-events-wrapper" data-pages="' . $events->max_num_pages . '">';
						$i       = 1;
						$last    = false;
						$columns = (int) $columns;

						while ( $events->have_posts() ) {
							$events->the_post();

							if ( $i === $columns ) {
								$last = true;
							}

							if ( $i > $columns ) {
								$i    = 1;
								$last = false;
							}

							if ( 1 === $columns ) {
								$last = true;
							}

							$thumbnail = '';
							$post_id   = get_the_ID();

							$html .= '<div ' . FusionBuilder::attributes( 'events-shortcode-columns', $last ) . '>';
							$html .= '<div class="fusion-column-wrapper">';

							if ( has_post_thumbnail( $post_id ) ) {
								if ( 'auto' === $picture_size ) {
									fusion_library()->images->set_grid_image_meta(
										[
											'layout'       => 'grid',
											'columns'      => $columns,
											'gutter_width' => $column_spacing,
										]
									);

									$thumbnail = get_the_post_thumbnail( $post_id, 'full' );

									fusion_library()->images->set_grid_image_meta( [] );
								} else {
									$thumbnail = '<span class="tribe-events-event-image" style="background-image: url(' . get_the_post_thumbnail_url( $post_id ) . '); -webkit-background-size: cover; background-size: cover; background-position: center center;"></span>';
								}
							} elseif ( class_exists( 'Tribe__Events__Pro__Main' ) ) {
								$thumb_url = esc_url( trailingslashit( Tribe__Events__Pro__Main::instance()->pluginUrl ) . 'src/resources/images/tribe-related-events-placeholder.png' );

								if ( 'auto' === $picture_size ) {
									$title     = the_title_attribute(
										[
											'echo' => false,
											'post' => $post_id,
										]
									);
									$thumbnail = '<img class="fusion-events-placeholder" src="' . $thumb_url . '" alt="' . $title . '" />';
								} else {
									$thumbnail = '<span class="tribe-events-event-image" style="background-image: url(' . $thumb_url . '); -webkit-background-size: cover; background-size: cover; background-position: center center;"></span>';
								}
							}

							$html .= '<div class="fusion-events-thumbnail hover-type-' . $fusion_settings->get( 'ec_hover_type' ) . '">';
							$html .= '<a href="' . get_the_permalink() . '" class="url" rel="bookmark" aria-label="' . the_title_attribute( [ 'echo' => false ] ) . '">';

							if ( $thumbnail ) {
								$html .= $thumbnail;
							} else {
								ob_start();
								/**
								 * The fusion_placeholder_image hook.
								 *
								 * @hooked fusion_render_placeholder_image - 10 (outputs the HTML for the placeholder image)
								 */
								do_action( 'fusion_placeholder_image', 'fixed' );

								$placeholder = ob_get_clean();
								$html       .= str_replace( 'fusion-placeholder-image', ' fusion-placeholder-image tribe-events-event-image', $placeholder );
							}

							$html .= '</a>';
							$html .= '</div>';
							$html .= '<div class="fusion-events-content-wrapper" style="padding:' . $content_padding . ';">';
							$html .= '<div class="fusion-events-meta">';
							$html .= '<h2><a href="' . get_the_permalink() . '" class="url" rel="bookmark">' . get_the_title() . '</a></h2>';
							$html .= '<h4>' . tribe_events_event_schedule_details() . '</h4>';
							$html .= '</div>';

							if ( 'no_text' !== $defaults['content_length'] ) {
								$html .= '<div class="fusion-events-content">';
								$html .= apply_filters( 'fusion_events_shortcode_content', $defaults['content_length'], $defaults['excerpt_length'], $defaults['strip_html'] );
								$html .= '</div>';
							}

							$html .= '</div>';
							$html .= '</div>';
							$html .= '</div>';

							if ( $last && ( 'no' === $defaults['pagination'] || 'pagination' === $defaults['pagination'] ) ) {
								$html .= '<div class="fusion-clearfix"></div>';
							}
							$i++;
						}

						wp_reset_query();

						if ( 'no' === $defaults['pagination'] || 'pagination' === $defaults['pagination'] ) {
							$html .= '<div class="fusion-clearfix"></div>';
						}

						$html .= '</div>';

						// Pagination.
						$pagination_type = ( '' !== $defaults['pagination'] ) ? $defaults['pagination'] : 'no';
						$pagination_html = '';

						if ( 'no' !== $pagination_type && 1 < esc_attr( $events->max_num_pages ) ) {

							// Pagination is set to "load more" button.
							if ( 'load_more_button' === $pagination_type && -1 !== intval( $number_posts ) ) {
								$button_margin = '';
								if ( '-1' !== $this->args['column_spacing'] ) {
									$button_margin    = 'margin-left: ' . ( $this->args['column_spacing'] / 2 ) . 'px;';
									$button_margin   .= 'margin-right: ' . ( $this->args['column_spacing'] / 2 ) . 'px;';
									$style            = '<style type="text/css">';
									$style           .= '.fusion-events-shortcode.fusion-events-shortcode-' . $this->fusion_events_counter . ' .fusion-load-more-button {' . $button_margin . '}';
									$style           .= '.fusion-events-shortcode.fusion-events-shortcode-' . $this->fusion_events_counter . ' .fusion-loading-container {' . $button_margin . '}';
									$style           .= '</style>';
									$pagination_html .= $style;
								}
								$pagination_html .= '<button class="fusion-load-more-button fusion-events-button fusion-clearfix">' . apply_filters( 'avada_load_more_events_name', esc_attr__( 'Load More Events', 'fusion-builder' ) ) . '</button>';
							}

							$infinite_pagination = false;
							if ( 'load_more_button' === $pagination_type || 'infinite' === $pagination_type ) {
								$infinite_pagination = true;
							}

							$pagination_html .= fusion_pagination( $events->max_num_pages, $fusion_settings->get( 'pagination_range' ), $events, $infinite_pagination, true );
						}

						$html .= $pagination_html;

						$html .= '</div>';
					}

					$this->fusion_events_counter++;

					$this->on_render();

					return apply_filters( 'fusion_element_events_content', $html, $args );
				}
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.6
			 * @return array
			 */
			public function attr() {
				$attr = [
					'class' => 'fusion-events-shortcode fusion-events-shortcode-' . $this->fusion_events_counter,
				];

				if ( 'no' !== $this->args['pagination'] ) {
					$attr['class'] .= ' fusion-events-pagination-' . str_replace( '_', '-', $this->args['pagination'] );
				}

				// Add custom class.
				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				// Add custom id.
				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				if ( $this->args['content_alignment'] ) {
					$attr['class'] .= ' fusion-events-layout-' . $this->args['content_alignment'];
				}

				if ( '-1' !== $this->args['column_spacing'] ) {
					$attr['style']  = 'margin-left: -' . ( $this->args['column_spacing'] / 2 ) . 'px;';
					$attr['style'] .= 'margin-right: -' . ( $this->args['column_spacing'] / 2 ) . 'px;';
				}

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				return $attr;
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.6
			 * @param bool $last Holds value for last column in a row.
			 * @return array
			 */
			public function column_attr( $last = false ) {
				$attr = [
					'class' => 'fusion-events-post',
				];

				$fusion_spacing = ( '-1' !== $this->args['column_spacing'] ) ? 'fusion-spacing-no' : 'fusion-spacing-yes';
				$attr['class'] .= ' ' . $fusion_spacing;

				$columns = (int) $this->args['columns'];

				switch ( $columns ) {
					case '1':
						$column_class = 'full-one';
						break;
					case '2':
						$column_class = 'one-half';
						break;
					case '3':
						$column_class = 'one-third';
						break;
					case '4':
						$column_class = 'one-fourth';
						break;
					case '5':
						$column_class = 'one-fifth';
						break;
					case '6':
						$column_class = 'one-sixth';
						break;
				}

				$attr['class'] .= ' fusion-' . $column_class . ' fusion-layout-column';
				$attr['class'] .= ( $last ) ? ' fusion-column-last' : '';

				if ( '-1' !== $this->args['column_spacing'] ) {
					$attr['style'] = 'padding:' . ( $this->args['column_spacing'] / 2 ) . 'px';
				} else {
					$attr['style'] = 'margin-bottom:4%;';
				}

				return $attr;
			}

			/**
			 * Echoes the post-content.
			 *
			 * @access public
			 * @since 1.6
			 * @param string $content_length Display excerpt / full content.
			 * @param int    $excerpt_length Excerpt length in words.
			 * @param string $strip_html     Yes/no option to strip html.
			 * @return string Excerpt / Full content of event.
			 */
			public function get_post_content( $content_length = 'excerpt', $excerpt_length = 55, $strip_html = 'yes' ) {
				if ( 'no_text' !== $content_length ) {
					$excerpt = 'no';
					if ( 'excerpt' === strtolower( $content_length ) ) {
						$excerpt = 'yes';
					}

					return fusion_get_post_content( '', $excerpt, $excerpt_length, $strip_html );
				}
			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.6
			 * @return array $sections Events settings.
			 */
			public function add_options() {
				return [
					'events_shortcode_section' => [
						'label'       => esc_attr__( 'Events', 'fusion-builder' ),
						'description' => '',
						'id'          => 'events_shortcode_section',
						'default'     => '',
						'icon'        => 'fusiona-tag',
						'type'        => 'accordion',
						'fields'      => [
							'events_per_page'           => [
								'label'       => esc_attr__( 'Number of Events Per Page', 'fusion-builder' ),
								'description' => esc_attr__( 'Controls the number of events displayed per page for events element. Set to -1 to display all. Set to 0 to use the number of posts from Settings > Reading.', 'fusion-builder' ),
								'id'          => 'events_per_page',
								'default'     => '4',
								'type'        => 'slider',
								'transport'   => 'postMessage',
								'choices'     => [
									'min'  => '-1',
									'max'  => '50',
									'step' => '1',
								],
							],
							'events_column_spacing'     => [
								'label'       => esc_attr__( 'Column Spacing', 'fusion-builder' ),
								'description' => esc_attr__( 'Controls the column spacing for events items.', 'fusion-builder' ),
								'id'          => 'events_column_spacing',
								'default'     => '40',
								'type'        => 'slider',
								'transport'   => 'postMessage',
								'choices'     => [
									'min'  => '-1',
									'max'  => '300',
									'step' => '1',
								],
							],
							'events_content_padding'    => [
								'label'       => esc_attr__( 'Events Content Padding', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the top/right/bottom/left padding of the events contents.', 'fusion-builder' ),
								'id'          => 'events_content_padding',
								'transport'   => 'postMessage',
								'choices'     => [
									'top'    => true,
									'bottom' => true,
									'left'   => true,
									'right'  => true,
								],
								'default'     => [
									'top'    => '20px',
									'bottom' => '20px',
									'left'   => '20px',
									'right'  => '20px',
								],
								'type'        => 'spacing',
							],
							'events_content_length'     => [
								'label'       => esc_attr__( 'Events Text Display', 'fusion-builder' ),
								'description' => esc_attr__( 'Choose how to display the post excerpt for events elements.', 'fusion-builder' ),
								'id'          => 'events_content_length',
								'default'     => 'no_text',
								'type'        => 'radio-buttonset',
								'transport'   => 'postMessage',
								'choices'     => [
									'no_text'      => esc_attr__( 'No Text', 'fusion-builder' ),
									'excerpt'      => esc_attr__( 'Excerpt', 'fusion-builder' ),
									'full_content' => esc_attr__( 'Full Content', 'fusion-builder' ),
								],
							],
							'excerpt_length_events'     => [
								'label'       => esc_attr__( 'Excerpt Length', 'fusion-builder' ),
								'description' => esc_attr__( 'Controls the number of words in the excerpts for events elements.', 'fusion-builder' ),
								'id'          => 'excerpt_length_events',
								'default'     => '55',
								'type'        => 'slider',
								'transport'   => 'postMessage',
								'choices'     => [
									'min'  => '0',
									'max'  => '500',
									'step' => '1',
								],
								'required'    => [
									[
										'setting'  => 'events_content_length',
										'operator' => '==',
										'value'    => 'excerpt',
									],
								],
							],
							'events_strip_html_excerpt' => [
								'label'       => esc_attr__( 'Strip HTML from Excerpt', 'fusion-builder' ),
								'description' => esc_attr__( 'Turn on to strip HTML content from the excerpt for events elements.', 'fusion-builder' ),
								'id'          => 'events_strip_html_excerpt',
								'default'     => '1',
								'type'        => 'switch',
								'transport'   => 'postMessage',
								'required'    => [
									[
										'setting'  => 'events_content_length',
										'operator' => '==',
										'value'    => 'excerpt',
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
				$fusion_settings = awb_get_fusion_settings();

				Fusion_Dynamic_JS::localize_script(
					'fusion-events',
					'fusionEventsVars',
					[
						'lightbox_behavior'     => $fusion_settings->get( 'lightbox_behavior' ),
						'infinite_finished_msg' => '<em>' . __( 'All items displayed.', 'fusion-builder' ) . '</em>',
						'infinite_blog_text'    => '<em>' . __( 'Loading the next set of posts...', 'fusion-builder' ) . '</em>',
					]
				);

				Fusion_Dynamic_JS::enqueue_script(
					'fusion-events',
					FusionBuilder::$js_folder_url . '/general/fusion-events.js',
					FusionBuilder::$js_folder_path . '/general/fusion-events.js',
					[ 'jquery', 'fusion-equal-heights', 'images-loaded', 'packery', 'jquery-infinite-scroll' ],
					'1',
					true
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
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/events.min.css' );
			}
		}
	}

	new FusionSC_FusionEvents();

}

/**
 * Map shortcode to Avada Builder
 *
 * @since 1.0
 */
function fusion_element_events() {
	$fusion_settings = awb_get_fusion_settings();

	if ( class_exists( 'Tribe__Events__Main' ) ) {
		fusion_builder_map(
			fusion_builder_frontend_data(
				'FusionSC_FusionEvents',
				[
					'name'      => esc_attr__( 'Events', 'fusion-builder' ),
					'shortcode' => 'fusion_events',
					'icon'      => 'fusiona-tag',
					'help_url'  => 'https://theme-fusion.com/documentation/avada/elements/the-events-calendar-element/',
					'params'    => [
						[
							'type'        => 'multiple_select',
							'heading'     => esc_attr__( 'Categories', 'fusion-builder' ),
							'placeholder' => esc_attr__( 'Categories', 'fusion-builder' ),
							'description' => esc_attr__( 'Select a category or leave blank for all.', 'fusion-builder' ),
							'param_name'  => 'cat_slug',
							'value'       => fusion_builder_shortcodes_categories( 'tribe_events_cat' ),
							'default'     => '',
							'callback'    => [
								'function' => 'fusion_ajax',
								'action'   => 'get_fusion_events',
								'ajax'     => true,
							],
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Display Past Events', 'fusion-builder' ),
							'description' => __( 'Turn on if you want the past events to be displayed.', 'fusion-builder' ),
							'param_name'  => 'past_events',
							'value'       => [
								'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
								'no'  => esc_attr__( 'No', 'fusion-builder' ),
							],
							'default'     => 'no',
							'callback'    => [
								'function' => 'fusion_ajax',
								'action'   => 'get_fusion_events',
								'ajax'     => true,
							],
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Order', 'fusion-builder' ),
							'description' => esc_attr__( 'Defines the sorting order of posts.', 'fusion-builder' ),
							'param_name'  => 'order',
							'default'     => 'ASC',
							'value'       => [
								'DESC' => esc_attr__( 'Descending', 'fusion-builder' ),
								'ASC'  => esc_attr__( 'Ascending', 'fusion-builder' ),
							],
							'callback'    => [
								'function' => 'fusion_ajax',
								'action'   => 'get_fusion_events',
								'ajax'     => true,
							],
						],
						[
							'type'        => 'range',
							'heading'     => esc_attr__( 'Number of Events', 'fusion-builder' ),
							'description' => esc_attr__( 'Select the number of events to display. Set to -1 to display all. Set to 0 to use number of posts from Settings > Reading.', 'fusion-builder' ),
							'param_name'  => 'number_posts',
							'value'       => '',
							'min'         => '-1',
							'max'         => '25',
							'step'        => '1',
							'default'     => $fusion_settings->get( 'events_per_page' ),
							'callback'    => [
								'function' => 'fusion_ajax',
								'action'   => 'get_fusion_events',
								'ajax'     => true,
							],
						],
						[
							'type'        => 'range',
							'heading'     => esc_attr__( 'Maximum Columns', 'fusion-builder' ),
							'description' => esc_attr__( 'Select the number of max columns to display.', 'fusion-builder' ),
							'param_name'  => 'columns',
							'value'       => '4',
							'min'         => '1',
							'max'         => '6',
							'step'        => '1',
						],
						[
							'type'        => 'range',
							'heading'     => esc_attr__( 'Column Spacing', 'fusion-builder' ),
							'description' => esc_attr__( 'Controls the column spacing for events items. Setting to -1 will keep the default 4% column spacing.', 'fusion-builder' ),
							'param_name'  => 'column_spacing',
							'value'       => '',
							'min'         => '-1',
							'max'         => '300',
							'step'        => '1',
							'default'     => $fusion_settings->get( 'events_column_spacing' ),
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Picture Size', 'fusion-builder' ),
							'description' => __( 'cover = image will scale to cover the container, <br />auto = width and height will adjust to the image.', 'fusion-builder' ),
							'param_name'  => 'picture_size',
							'value'       => [
								'cover' => esc_attr__( 'Cover', 'fusion-builder' ),
								'auto'  => esc_attr__( 'Auto', 'fusion-builder' ),
							],
							'default'     => 'cover',
							'callback'    => [
								'function' => 'fusion_ajax',
								'action'   => 'get_fusion_events',
								'ajax'     => true,
							],
						],
						[
							'type'             => 'dimension',
							'remove_from_atts' => true,
							'heading'          => esc_attr__( 'Content Padding ', 'fusion-builder' ),
							'description'      => esc_attr__( 'Controls the padding for the event contents. Enter values including any valid CSS unit, ex: 20px, 20px, 20px, 20px.', 'fusion-builder' ),
							'param_name'       => 'content_padding',
							'value'            => [
								'padding_top'    => '',
								'padding_right'  => '',
								'padding_bottom' => '',
								'padding_left'   => '',
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
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Text Display', 'fusion-builder' ),
							'description' => esc_attr__( 'Choose how to display the post excerpt.', 'fusion-builder' ),
							'param_name'  => 'content_length',
							'value'       => [
								''             => esc_attr__( 'Default', 'fusion-builder' ),
								'no_text'      => esc_attr__( 'No Text', 'fusion-builder' ),
								'excerpt'      => esc_attr__( 'Excerpt', 'fusion-builder' ),
								'full_content' => esc_attr__( 'Full Content', 'fusion-builder' ),
							],
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
							'default'     => $fusion_settings->get( 'excerpt_length_events' ),
							'dependency'  => [
								[
									'element'  => 'content_length',
									'value'    => 'excerpt',
									'operator' => '==',
								],
							],
							'transport'   => 'postMessage',
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Strip HTML', 'fusion-builder' ),
							'description' => esc_attr__( 'Strip HTML from the post excerpt.', 'fusion-builder' ),
							'param_name'  => 'strip_html',
							'value'       => [
								''    => esc_attr__( 'Default', 'fusion-builder' ),
								'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
								'no'  => esc_attr__( 'No', 'fusion-builder' ),
							],
							'dependency'  => [
								[
									'element'  => 'content_length',
									'value'    => 'excerpt',
									'operator' => '==',
								],
							],
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Pagination Type', 'fusion-builder' ),
							'description' => esc_attr__( 'Choose the type of pagination.', 'fusion-builder' ),
							'param_name'  => 'pagination',
							'default'     => 'no',
							'value'       => [
								'no'               => esc_attr__( 'No Pagination', 'fusion-builder' ),
								'pagination'       => esc_attr__( 'Pagination', 'fusion-builder' ),
								'infinite'         => esc_attr__( 'Infinite Scrolling', 'fusion-builder' ),
								'load_more_button' => esc_attr__( 'Load More Button', 'fusion-builder' ),
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
					'callback'  => [
						'function' => 'fusion_ajax',
						'action'   => 'get_fusion_events',
						'ajax'     => true,
					],
				]
			)
		);
	}
}
add_action( 'wp_loaded', 'fusion_element_events' );
