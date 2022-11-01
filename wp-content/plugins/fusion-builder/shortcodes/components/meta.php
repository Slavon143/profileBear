<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 2.4
 */

if ( fusion_is_element_enabled( 'fusion_tb_meta' ) ) {

	if ( ! class_exists( 'FusionTB_Meta' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 2.4
		 */
		class FusionTB_Meta extends Fusion_Component {

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 2.4
			 * @var array
			 */
			protected $args;

			/**
			 * The internal container counter.
			 *
			 * @access private
			 * @since 2.4
			 * @var int
			 */
			private $counter = 1;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 2.4
			 */
			public function __construct() {
				parent::__construct( 'fusion_tb_meta' );
				add_filter( 'fusion_attr_fusion_tb_meta-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_pipe_seprator_shortcodes', [ $this, 'allow_separator' ] );

				// Ajax mechanism for live editor.
				add_action( 'wp_ajax_get_fusion_tb_meta', [ $this, 'ajax_render' ] );
			}


			/**
			 * Check if component should render
			 *
			 * @access public
			 * @since 2.4
			 * @return boolean
			 */
			public function should_render() {
				return is_singular() || wp_is_json_request();

			}

			/**
			 * Enables pipe separator for short code.
			 *
			 * @access public
			 * @since 2.4
			 * @param array $shortcodes The shortcodes array.
			 * @return array
			 */
			public function allow_separator( $shortcodes ) {
				if ( is_array( $shortcodes ) ) {
					array_push( $shortcodes, 'fusion_tb_meta' );
				}

				return $shortcodes;
			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 2.4
			 * @return array
			 */
			public static function get_element_defaults() {
				$fusion_settings = awb_get_fusion_settings();

				return [
					'meta'                     => 'author,published_date,categories,comments,tags',
					'layout'                   => 'floated',
					'separator'                => '',
					'font_size'                => $fusion_settings->get( 'meta_font_size' ),
					'text_color'               => $fusion_settings->get( 'link_color' ),
					'link_color'               => '',
					'text_hover_color'         => $fusion_settings->get( 'primary_color' ),
					'border_size'              => null,
					'border_color'             => $fusion_settings->get( 'sep_color' ),
					'alignment'                => 'flex-start',
					'alignment_medium'         => '',
					'alignment_small'          => '',
					'stacked_vertical_align'   => 'flex-start',
					'stacked_horizontal_align' => 'flex-start',
					'height'                   => '33',
					'margin_bottom'            => '',
					'margin_left'              => '',
					'margin_right'             => '',
					'margin_top'               => '',
					'hide_on_mobile'           => fusion_builder_default_visibility( 'string' ),
					'class'                    => '',
					'id'                       => '',
					'animation_type'           => '',
					'animation_direction'      => 'down',
					'animation_speed'          => '0.1',
					'animation_offset'         => $fusion_settings->get( 'animation_offset' ),
					'padding_bottom'           => '',
					'padding_left'             => '',
					'padding_right'            => '',
					'padding_top'              => '',
					'border_bottom'            => '1px',
					'border_left'              => '0px',
					'border_right'             => '0px',
					'border_top'               => '1px',
					'read_time'                => 200,
					'reading_time_decimal'     => 'yes',
					'background_color'         => '',
					'item_background_color'    => '',
					'item_border_color'        => $fusion_settings->get( 'sep_color' ),
					'item_padding_bottom'      => '',
					'item_padding_left'        => '',
					'item_padding_right'       => '',
					'item_padding_top'         => '',
					'item_border_bottom'       => '',
					'item_border_left'         => '',
					'item_border_right'        => '',
					'item_border_top'          => '',
					'item_margin_bottom'       => '',
					'item_margin_left'         => '',
					'item_margin_right'        => '',
					'item_margin_top'          => '',
				];
			}

			/**
			 * Render for live editor.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @param array $defaults An array of defaults.
			 * @return void
			 */
			public function ajax_render( $defaults ) {
				check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );

				$live_request = false;

				// From Ajax Request.
				if ( isset( $_POST['model'] ) && isset( $_POST['model']['params'] ) && ! apply_filters( 'fusion_builder_live_request', false ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$defaults     = $_POST['model']['params']; // phpcs:ignore WordPress.Security
					$this->args   = $defaults;
					$return_data  = [];
					$live_request = true;
					fusion_set_live_data();
					add_filter( 'fusion_builder_live_request', '__return_true' );
				}

				if ( class_exists( 'Fusion_App' ) && $live_request ) {

					$this->emulate_post();

					$return_data['meta'] = $this->get_meta_elements( $defaults, true );
					$this->restore_post();
				}

				echo wp_json_encode( $return_data );
				wp_die();
			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 2.4
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {
				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_tb_meta' );

				if ( null !== $defaults['border_size'] ) {
					$defaults['border_bottom'] = FusionBuilder::validate_shortcode_attr_value( $defaults['border_size'], 'px' );
					$defaults['border_top']    = FusionBuilder::validate_shortcode_attr_value( $defaults['border_size'], 'px' );
				}

				$defaults['height'] = FusionBuilder::validate_shortcode_attr_value( $defaults['height'], 'px' );

				$this->args     = $defaults;
				$this->defaults = self::get_element_defaults();

				$this->emulate_post();

				$content = $this->get_meta_elements( $this->args, false );

				$this->restore_post();

				$content = '<div ' . FusionBuilder::attributes( 'fusion_tb_meta-shortcode' ) . '>' . $content . '</div>';

				$html = $content . $this->get_styles();

				$this->counter++;

				$this->on_render();

				return apply_filters( 'fusion_component_' . $this->shortcode_handle . '_content', $html, $args );
			}

			/**
			 * Get the styles.
			 *
			 * @access protected
			 * @since 3.2
			 * @return string
			 */
			protected function get_styles() {
				$this->base_selector = '.fusion-meta-tb.fusion-meta-tb-' . $this->counter;
				$this->dynamic_css   = [];
				$fusion_settings     = awb_get_fusion_settings();

				$selectors = [
					$this->base_selector,
					$this->base_selector . ' a',
				];

				if ( ! $this->is_default( 'text_color' ) ) {
					$this->add_css_property( $selectors, 'color', $this->args['text_color'] );
				}

				if ( ! $this->is_default( 'link_color' ) ) {
					$this->add_css_property( $this->base_selector . ' span a', 'color', $this->args['link_color'] );
				}

				// Alignment.
				if ( '' !== $this->args['alignment'] && 'stacked' !== $this->args['layout'] ) {
					$this->add_css_property( $this->base_selector, 'justify-content', $this->args['alignment'] );
				}

				$selectors = [
					$this->base_selector . ' a:hover',
					$this->base_selector . ' span a:hover',
				];

				if ( ! $this->is_default( 'text_hover_color' ) ) {
					$this->add_css_property( [ $this->base_selector . ' a:hover' ], 'color', $this->args['text_hover_color'] );
				}

				if ( ! $this->is_default( 'border_color' ) ) {
					$this->add_css_property( [ $this->base_selector ], 'border-color', $this->args['border_color'] );
				}

				if ( ! $this->is_default( 'border_bottom' ) ) {
					$this->add_css_property( [ $this->base_selector ], 'border-bottom-width', $this->args['border_bottom'] );
				}

				if ( ! $this->is_default( 'border_top' ) ) {
					$this->add_css_property( [ $this->base_selector ], 'border-top-width', $this->args['border_top'] );
				}

				if ( ! $this->is_default( 'border_left' ) ) {
					$this->add_css_property( [ $this->base_selector ], 'border-left-width', $this->args['border_left'] );
				}

				if ( ! $this->is_default( 'border_right' ) ) {
					$this->add_css_property( [ $this->base_selector ], 'border-right-width', $this->args['border_right'] );
				}

				$selectors = [
					$this->base_selector . '  > span:not(.fusion-meta-tb-sep)',
				];

				if ( ! $this->is_default( 'item_border_color' ) ) {
					$this->add_css_property( $selectors, 'border-color', $this->args['item_border_color'] );
				}

				if ( ! $this->is_default( 'item_border_bottom' ) ) {
					$this->add_css_property( $selectors, 'border-bottom-width', $this->args['item_border_bottom'] );
				}

				if ( ! $this->is_default( 'item_border_top' ) ) {
					$this->add_css_property( $selectors, 'border-top-width', $this->args['item_border_top'] );
				}

				if ( ! $this->is_default( 'item_border_left' ) ) {
					$this->add_css_property( $selectors, 'border-left-width', $this->args['item_border_left'] );
				}

				if ( ! $this->is_default( 'item_border_right' ) ) {
					$this->add_css_property( $selectors, 'border-right-width', $this->args['item_border_right'] );
				}

				if ( ! $this->is_default( 'item_background_color' ) ) {
					$this->add_css_property( $selectors, 'background-color', $this->args['item_background_color'] );
				}

				if ( ! $this->is_default( 'item_padding_top' ) ) {
					$this->add_css_property( $selectors, 'padding-top', $this->args['item_padding_top'] );
				}

				if ( ! $this->is_default( 'item_padding_bottom' ) ) {
					$this->add_css_property( $selectors, 'padding-bottom', $this->args['item_padding_bottom'] );
				}

				if ( ! $this->is_default( 'item_padding_left' ) ) {
					$this->add_css_property( $selectors, 'padding-left', $this->args['item_padding_left'] );
				}

				if ( ! $this->is_default( 'item_padding_right' ) ) {
					$this->add_css_property( $selectors, 'padding-right', $this->args['item_padding_right'] );
				}

				if ( ! $this->is_default( 'item_margin_top' ) ) {
					$this->add_css_property( $selectors, 'margin-top', $this->args['item_margin_top'] );
				}

				if ( ! $this->is_default( 'item_margin_bottom' ) ) {
					$this->add_css_property( $selectors, 'margin-bottom', $this->args['item_margin_bottom'] );
				}

				if ( ! $this->is_default( 'item_margin_left' ) ) {
					$this->add_css_property( $selectors, 'margin-left', $this->args['item_margin_left'] );
				}

				if ( ! $this->is_default( 'item_margin_right' ) ) {
					$this->add_css_property( $selectors, 'margin-right', $this->args['item_margin_right'] );
				}

				$css = $this->parse_css();

				// Responsive Alignment.
				if ( 'stacked' !== $this->args['layout'] ) {
					foreach ( [ 'medium', 'small' ] as $size ) {
						$key   = 'alignment_' . $size;
						$media = sprintf( '@media only screen and (max-width:%spx)', $fusion_settings->get( 'visibility_' . $size ) );

						if ( '' === $this->args[ $key ] ) {
							continue;
						}

						$this->dynamic_css = [];
						$this->add_css_property( $this->base_selector, 'justify-content', $this->args[ $key ] );
						$css .= sprintf( '%s { %s }', $media, $this->parse_css() );
					}
				}
				return $css ? '<style type="text/css">' . $css . '</style>' : '';
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 2.4
			 * @return array
			 */
			public function attr() {
				$attr = [
					'class' => 'fusion-meta-tb fusion-meta-tb-' . $this->counter,
					'style' => '',
				];

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				$attr['style'] .= Fusion_Builder_Margin_Helper::get_margins_style( $this->args );

				$attr['style'] .= Fusion_Builder_Padding_Helper::get_paddings_style( $this->args );

				if ( $this->args['height'] ) {
					$attr['style'] .= 'min-height:' . $this->args['height'] . ';';
				}

				if ( '' !== $this->args['stacked_vertical_align'] && 'floated' !== $this->args['layout'] ) {
					$attr['style'] .= 'justify-content:' . $this->args['stacked_vertical_align'] . ';';
				}

				if ( '' !== $this->args['stacked_horizontal_align'] && 'floated' !== $this->args['layout'] ) {
					$attr['style'] .= 'align-items:' . $this->args['stacked_horizontal_align'] . ';';
				}

				if ( $this->args['font_size'] ) {
					$attr['style'] .= 'font-size:' . $this->args['font_size'] . ';';
				}

				if ( $this->args['background_color'] ) {
					$attr['style'] .= 'background-color:' . $this->args['background_color'] . ';';
				}

				if ( '' !== $this->args['layout'] ) {
					$attr['class'] .= ' ' . $this->args['layout'];
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
			 * Maps settings to param variables.
			 *
			 * @static
			 * @access public
			 * @since 2.4
			 * @return array
			 */
			public static function settings_to_params() {
				return [
					'sep_color'      => 'border_color',
					'sep_color'      => 'item_border_color',
					'link_color'     => 'text_color',
					'primary_color'  => 'text_hover_color',
					'meta_font_size' => 'font_size',
				];
			}

			/**
			 * Used to set any other variables for use on front-end editor template.
			 *
			 * @static
			 * @access public
			 * @since 3.6
			 * @return array
			 */
			public static function get_element_extras() {
				$fusion_settings = awb_get_fusion_settings();
				return [
					'visibility_medium' => $fusion_settings->get( 'visibility_medium' ),
					'visibility_small'  => $fusion_settings->get( 'visibility_small' ),
				];
			}

			/**
			 * Builds HTML for meta elements.
			 *
			 * @static
			 * @access public
			 * @since 2.4
			 * @param array $args    The arguments.
			 * @param bool  $is_live If it's live editor request or not.
			 * @return array
			 */
			public function get_meta_elements( $args, $is_live ) {
				global $product;

				$options     = explode( ',', $args['meta'] );
				$post_id     = $this->get_post_id();
				$content     = '';
				$date_format = fusion_library()->get_option( 'date_format' );
				$date_format = $date_format ? $date_format : get_option( 'date_format' );
				$separator   = '<span class="fusion-meta-tb-sep">' . $args['separator'] . '</span>';
				$post_type   = get_post_type( $post_id );
				$author_id   = -99 === $post_id ? get_post_field( 'post_author' ) : get_post_field( 'post_author', $post_id );
				$is_builder  = ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() );

				foreach ( $options as $index => $option ) {
					switch ( $option ) {
						case 'author':
							$link = sprintf(
								'<a href="%1$s" title="%2$s" rel="author">%3$s</a>',
								esc_url( get_author_posts_url( $author_id ) ),
								/* translators: %s: Author's display name. */
								esc_attr( sprintf( __( 'Posts by %s' ), get_the_author_meta( 'display_name', $author_id ) ) ),
								get_the_author_meta( 'display_name', $author_id )
							);
							/* Translators: %s: The author. */
							$content .= '<span class="fusion-tb-author">' . sprintf( esc_html__( 'By %s', 'fusion-builder' ), '<span>' . $link . '</span>' ) . '</span>' . $separator;
							break;
						case 'published_date':
							/* Translators: %s: Date. */
							$content .= '<span class="fusion-tb-published-date">' . sprintf( esc_html__( 'Published On: %s', 'fusion-builder' ), get_the_time( $date_format ) ) . '</span>' . $separator;
							break;
						case 'modified_date':
							/* Translators: %s: Date. */
							$content .= '<span class="fusion-tb-modified-date">' . sprintf( esc_html__( 'Last Updated: %s', 'fusion-builder' ), get_the_modified_date( $date_format ) ) . '</span>' . $separator;
							break;
						case 'categories':
							$categories = '';
							$taxonomies = [
								'avada_portfolio' => 'portfolio_category',
								'avada_faq'       => 'faq_category',
								'product'         => 'product_cat',
								'tribe_events'    => 'tribe_events_cat',
							];

							if ( 'post' === $post_type || isset( $taxonomies[ $post_type ] ) ) {
								$categories = 'post' === $post_type ? get_the_category_list( ', ', '', $post_id ) : get_the_term_list( $post_id, $taxonomies[ $post_type ], '', ', ' );
							}
							/* Translators: %s: List of categories. */
							$content .= $categories && ! is_wp_error( $categories ) ? '<span class="fusion-tb-categories">' . sprintf( esc_html__( 'Categories: %s', 'fusion-builder' ), $categories ) . '</span>' . $separator : '';
							break;
						case 'comments':
							ob_start();
							comments_popup_link( esc_html__( '0 Comments', 'fusion-builder' ), esc_html__( '1 Comment', 'fusion-builder' ), esc_html__( '% Comments', 'fusion-builder' ) );
							$comments = ob_get_clean();
							$content .= '<span class="fusion-tb-comments">' . $comments . '</span>' . $separator;
							break;
						case 'tags':
							$tags       = '';
							$taxonomies = [
								'avada_portfolio' => 'portfolio_tags',
								'product'         => 'product_tag',
							];

							if ( 'post' === $post_type || isset( $taxonomies[ $post_type ] ) ) {
								$tags = isset( $taxonomies[ $post_type ] ) ? get_the_term_list( $post_id, $taxonomies[ $post_type ], '', ', ', '' ) : get_the_tag_list( '', ', ', '' );
							}

							/* Translators: %s: List of tags. */
							$content .= $tags && ! is_wp_error( $tags ) ? '<span class="fusion-tb-tags">' . sprintf( esc_html__( 'Tags: %s', 'fusion-builder' ), $tags ) . '</span>' . $separator : '';
							break;
						case 'skills':
							$skills = '';
							if ( 'avada_portfolio' === $post_type ) {
								$skills = get_the_term_list( $post_id, 'portfolio_skills', '', ', ', '' );
							}

							/* Translators: %s: List of skills. */
							$content .= $skills ? apply_filters( 'fusion_portfolio_post_skills_label', '<span class="fusion-tb-skills">' . sprintf( esc_html__( 'Skills Needed: %s', 'fusion-builder' ), $skills ) . '</span>' ) . $separator : '';
							break;
						case 'sku':
							$sku_can_be_displayed = ( function_exists( 'wc_product_sku_enabled' ) && wc_product_sku_enabled() && is_object( $product ) && ( '' !== $product->get_sku() || $product->is_type( 'variable' ) ) );

							if ( $sku_can_be_displayed || $is_live || $is_builder ) {
								$sku_is_not_empty            = ( is_object( $product ) && $product->get_sku() );
								$need_random_sku_for_builder = ( ( $is_live || $is_builder ) && ! $sku_is_not_empty );

								$sku      = ( $sku_is_not_empty ? $product->get_sku() : esc_html__( 'N/A', 'fusion-builder' ) );
								$sku      = $need_random_sku_for_builder ? wp_rand( 10000, 99999 ) : $sku;
								$content .= '<span class="fusion-tb-sku product_meta">' . esc_html__( 'SKU:', 'fusion-builder' ) . ' <span class="sku">' . $sku . '</span></span>' . $separator;
							}
							break;
						case 'event_date':
							$event_date = $this->get_event_date();
							if ( $event_date ) {
								$content .= '<span class="fusion-tb-event-date">' . $event_date . '</span>' . $separator;
							}
							break;
						case 'event_start_date':
							$event_start_date = $this->get_event_start_date();
							if ( $event_start_date ) {
								$content .= '<span class="fusion-tb-event-start-date">' . $event_start_date . '</span>' . $separator;
							}
							break;
						case 'event_end_date':
							$event_end_date = $this->get_event_end_date();
							if ( $event_end_date ) {
								$content .= '<span class="fusion-tb-event-end-date">' . $event_end_date . '</span>' . $separator;
							}
							break;
						case 'word_count':
							/* Translators: %s: number of words. */
							$content .= '<span class="fusion-tb-published-word-count">' . sprintf( esc_html__( '%s words', 'fusion-builder' ), $this->count_post_words() ) . '</span>' . $separator;
							break;
						case 'read_time':
							$reading_time_args = [
								'reading_speed'         => $this->args['read_time'],
								'use_decimal_precision' => $this->args['reading_time_decimal'],
							];

							$reading_time = awb_get_reading_time_for_display( $this->get_post_id(), $reading_time_args );
							/* Translators: %s: minutes of read. */
							$content .= '<span class="fusion-tb-published-read-time">' . sprintf( esc_html__( '%s min read', 'fusion-builder' ), $reading_time ) . '</span>' . $separator;
							break;
						case 'total_views':
							$total_views_num = avada_get_post_views( $post_id );

							$both_views_are_displayed = in_array( 'today_views', $options, true );
							if ( $both_views_are_displayed ) {
								/* Translators: %s: number of total views of a post. */
								$total_views = sprintf( esc_html__( 'Total Views: %s', 'fusion-builder' ), $total_views_num );
							} else {
								/* Translators: %s: number of total views of a post. */
								$total_views = sprintf( esc_html__( 'Views: %s', 'fusion-builder' ), $total_views_num );
							}

							$content .= '<span class="fusion-tb-total-views">' . $total_views . '</span>' . $separator;
							break;
						case 'today_views':
							$today_views_num = avada_get_today_post_views( $post_id );
							/* Translators: %s: number of daily views. */
							$today_views = sprintf( esc_html__( 'Daily Views: %s', 'fusion-builder' ), $today_views_num );
							$content    .= '<span class="fusion-tb-today-views">' . $today_views . '</span>' . $separator;
							break;
					}
				}

				return $content;
			}

			/**
			 * Get the event date.
			 *
			 * @since 3.5
			 * @return string Empty string if the post is not an event.
			 */
			public function get_event_date() {
				$event_id = $this->get_post_id();
				$event    = get_post( $event_id );

				if ( ! $event instanceof WP_Post ) {
					return '';
				}

				$post_is_event_type = ( 'tribe_events' === $event->post_type ? true : false );
				if ( ! $post_is_event_type ) {
					return '';
				}

				add_filter( 'tribe_events_recurrence_tooltip', [ $this, 'remove_event_recurring_info' ], 999 );
				$date = tribe_events_event_schedule_details( $event_id );
				remove_filter( 'tribe_events_recurrence_tooltip', [ $this, 'remove_event_recurring_info' ], 999 );

				return $date;
			}

			/**
			 * Remove the recurring event after the meta, since it will take a
			 * lot of space.
			 *
			 * @param string $tooltip The recurring tooltip.
			 * @return string Empty string, containing no tooltip.
			 */
			public static function remove_event_recurring_info( $tooltip ) {
				return '';
			}

			/**
			 * Get the event start date.
			 *
			 * @since 3.5
			 * @return string Empty string if the post is not an event.
			 */
			public function get_event_start_date() {
				$event_id = $this->get_post_id();
				$event    = get_post( $event_id );

				if ( ! $event instanceof WP_Post ) {
					return '';
				}

				$post_is_event_type = ( 'tribe_events' === $event->post_type ? true : false );
				if ( ! $post_is_event_type ) {
					return '';
				}

				/* translators: %s: a date, representing the start date of an event. */
				return sprintf( __( 'Start Date: %s', 'fusion-builder' ), tribe_get_start_date( $event_id ) );
			}

			/**
			 * Get the event end date.
			 *
			 * @since 3.5
			 * @return string Empty string if the post is not an event.
			 */
			public function get_event_end_date() {
				$event_id = $this->get_post_id();
				$event    = get_post( $event_id );

				if ( ! $event instanceof WP_Post ) {
					return '';
				}

				$post_is_event_type = ( 'tribe_events' === $event->post_type ? true : false );
				if ( ! $post_is_event_type ) {
					return '';
				}

				/* translators: %s: a date, representing the end date of an event. */
				return sprintf( __( 'End Date: %s', 'fusion-builder' ), tribe_get_end_date( $event_id ) );
			}

			/**
			 * Gets total words from the current post
			 *
			 * @since 3.1.1
			 * @return int
			 */
			public function count_post_words() {
				return str_word_count( $this->get_post_content() );
			}


			/**
			 * Get current post content without shortcodes and text.
			 *
			 * @param bool $strip_tags should we strip tags or not.
			 * @since 3.2
			 * @return string|null
			 */
			public function get_post_content( $strip_tags = true ) {
				global $post;

				$content      = $post->post_content;
				$post_content = preg_replace( '~(?:\[/?)[^/\]]+/?\]~s', '', $content );
				if ( $strip_tags ) {
					$post_content = wp_strip_all_tags( $post_content );
				}
				return $post_content;
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/components/meta.min.css' );
			}
		}
	}

	new FusionTB_Meta();
}

/**
 * Map shortcode to Avada Builder
 *
 * @since 2.4
 */
function fusion_component_meta() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionTB_Meta',
			[
				'name'                    => esc_attr__( 'Meta', 'fusion-builder' ),
				'shortcode'               => 'fusion_tb_meta',
				'icon'                    => 'fusiona-meta-data',
				'component'               => true,
				'templates'               => [ 'content', 'post_cards', 'page_title_bar' ],
				'components_per_template' => 1,
				'params'                  => [
					[
						'type'        => 'connected_sortable',
						'heading'     => esc_attr__( 'Meta Elements', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the order of meta elements.', 'fusion-builder' ),
						'param_name'  => 'meta',
						'default'     => 'author,published_date,categories,comments,tags',
						'choices'     => [
							'author'           => esc_attr__( 'Author', 'fusion-builder' ),
							'published_date'   => esc_attr__( 'Published Date', 'fusion-builder' ),
							'modified_date'    => esc_attr__( 'Modified Date', 'fusion-builder' ),
							'categories'       => esc_attr__( 'Categories', 'fusion-builder' ),
							'comments'         => esc_attr__( 'Comments', 'fusion-builder' ),
							'tags'             => esc_attr__( 'Tags', 'fusion-builder' ),
							'skills'           => esc_attr__( 'Portfolio Skills', 'fusion-builder' ),
							'sku'              => esc_attr__( 'Product SKU', 'fusion-builder' ),
							'event_date'       => esc_attr__( 'Event Full Date', 'fusion-builder' ),
							'event_start_date' => esc_attr__( 'Event Start Date', 'fusion-builder' ),
							'event_end_date'   => esc_attr__( 'Event End Date', 'fusion-builder' ),
							'word_count'       => esc_attr__( 'Word Count', 'fusion-builder' ),
							'read_time'        => esc_attr__( 'Reading Time', 'fusion-builder' ),
							'total_views'      => esc_attr__( 'Total Views', 'fusion-builder' ),
							'today_views'      => esc_attr__( 'Daily Views', 'fusion-builder' ),
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_meta',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_html__( 'Layout', 'fusion-builder' ),
						'description' => esc_html__( 'Choose if meta items should be stacked and full width, or if they should be floated.', 'fusion-builder' ),
						'param_name'  => 'layout',
						'default'     => 'floated',
						'value'       => [
							'stacked' => esc_html__( 'Stacked', 'fusion-builder' ),
							'floated' => esc_html__( 'Floated', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Separator', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the type of separator between each meta item.', 'fusion-builder' ),
						'param_name'  => 'separator',
						'escape_html' => true,
						'callback'    => [
							'function' => 'fusion_update_tb_meta_separator',
							'args'     => [
								'selector' => '.fusion-meta-tb',
							],
						],
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'stacked',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Reading Time', 'fusion-builder' ),
						'description' => esc_attr__( 'Average words read per minute. The default value is 200.', 'fusion-builder' ),
						'param_name'  => 'read_time',
						'value'       => '200',
						'default'     => '200',
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_meta',
							'ajax'     => true,
						],
						'dependency'  => [
							[
								'element'  => 'meta',
								'value'    => 'read_time',
								'operator' => 'contains',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Reading Time Decimal Precision', 'fusion-builder' ),
						'description' => esc_attr__( 'Whether to use(Ex: 2.3 min) or not(Ex: 2 min) decimal precision in reading time.', 'fusion-builder' ),
						'param_name'  => 'reading_time_decimal',
						'value'       => [
							'yes' => esc_html__( 'Yes', 'fusion-builder' ),
							'no'  => esc_html__( 'No', 'fusion-builder' ),
						],
						'default'     => 'yes',
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_meta',
							'ajax'     => true,
						],
						'dependency'  => [
							[
								'element'  => 'meta',
								'value'    => 'read_time',
								'operator' => 'contains',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the meta alignment.', 'fusion-builder' ),
						'param_name'  => 'alignment',
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
								'value'    => 'stacked',
								'operator' => '!=',
							],
						],
						'responsive'  => [
							'state' => 'large',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Vertical Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Defines how the meta content should align vertically.', 'fusion-builder' ),
						'param_name'  => 'stacked_vertical_align',
						'default'     => 'flex-start',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => [
							'flex-start'    => esc_attr__( 'Flex Start', 'fusion-builder' ),
							'center'        => esc_attr__( 'Center', 'fusion-builder' ),
							'flex-end'      => esc_attr__( 'Flex End', 'fusion-builder' ),
							'space-between' => esc_attr__( 'Space Between', 'fusion-builder' ),
							'space-around'  => esc_attr__( 'Space Around', 'fusion-builder' ),
							'space-evenly'  => esc_attr__( 'Space Evenly', 'fusion-builder' ),
						],
						'icons'       => [
							'flex-start'    => '<span class="fusiona-align-top-vert"></span>',
							'center'        => '<span class="fusiona-align-center-vert"></span>',
							'flex-end'      => '<span class="fusiona-align-bottom-vert"></span>',
							'space-between' => '<span class="fusiona-space-between"></span>',
							'space-around'  => '<span class="fusiona-space-around"></span>',
							'space-evenly'  => '<span class="fusiona-space-evenly"></span>',
						],
						'grid_layout' => true,
						'back_icons'  => true,
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'floated',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Horizontal Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Defines how the meta content should align horizontally.  Overrides what is set on the container.', 'fusion-builder' ),
						'param_name'  => 'stacked_horizontal_align',
						'default'     => 'flex-start',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => [
							'flex-start' => esc_attr__( 'Flex Start', 'fusion-builder' ),
							'center'     => esc_attr__( 'Center', 'fusion-builder' ),
							'flex-end'   => esc_attr__( 'Flex End', 'fusion-builder' ),
						],
						'icons'       => [
							'flex-start' => '<span class="fusiona-horizontal-flex-start"></span>',
							'center'     => '<span class="fusiona-horizontal-flex-center"></span>',
							'flex-end'   => '<span class="fusiona-horizontal-flex-end"></span>',
						],
						'grid_layout' => true,
						'back_icons'  => true,
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'floated',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Height', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the Meta section height. In pixels.', 'fusion-builder' ),
						'param_name'  => 'height',
						'value'       => '36',
						'min'         => '0',
						'max'         => '500',
						'step'        => '1',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Text Font Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the font size for the meta text. Enter value including CSS unit (px, em, rem), ex: 10px', 'fusion-builder' ),
						'param_name'  => 'font_size',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the meta section text.', 'fusion-builder' ),
						'param_name'  => 'text_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'link_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Link Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the link color of the meta section text.', 'fusion-builder' ),
						'param_name'  => 'link_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Link Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the link hover color of the meta section text.', 'fusion-builder' ),
						'param_name'  => 'text_hover_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'primary_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background color of element wrapper.', 'fusion-builder' ),
						'param_name'  => 'background_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Border Size', 'fusion-builder' ),
						'description'      => esc_attr__( 'Controls the border size of the element wrapper. In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'border_sizes',
						'value'            => [
							'border_top'    => '',
							'border_right'  => '',
							'border_bottom' => '',
							'border_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the element wrapper.', 'fusion-builder' ),
						'param_name'  => 'border_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'sep_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Padding', 'fusion-builder' ),
						'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'padding',
						'value'            => [
							'padding_top'    => '',
							'padding_right'  => '',
							'padding_bottom' => '',
							'padding_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Border Size', 'fusion-builder' ),
						'description'      => esc_attr__( 'Controls the border size of the element wrapper. In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'border_sizes',
						'value'            => [
							'border_top'    => '',
							'border_right'  => '',
							'border_bottom' => '',
							'border_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the element wrapper.', 'fusion-builder' ),
						'param_name'  => 'border_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'sep_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Padding', 'fusion-builder' ),
						'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'padding',
						'value'            => [
							'padding_top'    => '',
							'padding_right'  => '',
							'padding_bottom' => '',
							'padding_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
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
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Meta Item Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background color of the meta item.', 'fusion-builder' ),
						'param_name'  => 'item_background_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Meta Item Border Size', 'fusion-builder' ),
						'description'      => esc_attr__( 'Controls the border size of the meta item. In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'item_border_sizes',
						'value'            => [
							'item_border_top'    => '',
							'item_border_right'  => '',
							'item_border_bottom' => '',
							'item_border_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Meta Item Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the meta item.', 'fusion-builder' ),
						'param_name'  => 'item_border_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'sep_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Meta Item Padding', 'fusion-builder' ),
						'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'item_padding',
						'value'            => [
							'item_padding_top'    => '',
							'item_padding_right'  => '',
							'item_padding_bottom' => '',
							'item_padding_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Meta Item Margin', 'fusion-builder' ),
						'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'item_margin',
						'value'            => [
							'item_margin_top'    => '',
							'item_margin_right'  => '',
							'item_margin_bottom' => '',
							'item_margin_left'   => '',
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
						'preview_selector' => '.fusion-meta-tb',
					],
				],
				'callback'                => [
					'function' => 'fusion_ajax',
					'action'   => 'get_fusion_tb_meta',
					'ajax'     => true,
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_component_meta' );
