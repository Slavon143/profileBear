<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_instagram' ) ) {

	if ( ! class_exists( 'FusionSC_Instagram' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @package fusion-builder
		 * @since 1.0
		 */
		class FusionSC_Instagram extends Fusion_Element {

			/**
			 * The image-frame counter.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $instagram_counter = 1;

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @var array
			 */
			protected $args;

			/**
			 * An array of wrapper attributes.
			 *
			 * @access protected
			 * @since 3.0
			 * @var array
			 */
			protected $wrapper_attr = [
				'class' => '',
				'style' => '',
			];

			/**
			 * Token data.
			 *
			 * @static
			 * @access private
			 * @since 3.8
			 * @var mixed
			 */
			private $token = null;

			/**
			 * Localize status.
			 *
			 * @static
			 * @access private
			 * @since 3.8
			 * @var mixed
			 */
			private $localize_status = null;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_instagram-shortcode', [ $this, 'attr' ] );

				add_shortcode( 'fusion_instagram', [ $this, 'render' ] );

				add_action( 'rest_api_init', [ $this, 'register_instagram_endpoint' ] );

				// get HTML.
				add_action( 'wp_ajax_awb_instagram_get_data', [ $this, 'ajax_get_data' ] );
				add_action( 'wp_ajax_nopriv_awb_instagram_get_data', [ $this, 'ajax_get_data' ] );

				// Reset caches.
				add_action( 'wp_ajax_fusion_reset_instagram_caches', [ $this, 'reset_caches_handler' ] );

				// This is a redirect from our site with token.
				if ( is_admin() && current_user_can( 'manage_options' ) ) {

					// Trying to save a token.
					if ( isset( $_GET['instagram_auth'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
						$this->authenticate();
					}

					// Trying to revoke a token.
					if ( isset( $_GET['revoke_instagram'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
						$this->revoke_access();
					}

					// Enqueue the OAuth script where required.
					if ( ( isset( $_GET['page'] ) && 'avada_options' === $_GET['page'] ) || isset( $_GET['instagram_auth'] ) || isset( $_GET['revoke_instagram'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
						add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_api_script' ] );
					}
				}

				// Refresh token before expire date.
				$this->refresh_token();
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
					'layout'                       => '',
					'limit'                        => 10,
					'aspect_ratio'                 => '',
					'custom_aspect_ratio'          => '',
					'aspect_ratio_position'        => '',
					'hover_type'                   => '',
					'link_type'                    => '',
					'link_target'                  => '',
					'load_more'                    => '',
					'load_more_btn_text'           => esc_html__( 'Load More', 'fusion-builder' ),
					'follow_btn'                   => 'no',
					'follow_btn_text'              => esc_html__( 'Follow Us On Instagram', 'fusion-builder' ),

					'columns'                      => '',
					'columns_medium'               => '',
					'columns_small'                => '',
					'columns_spacing'              => '',
					'columns_spacing_medium'       => '',
					'columns_spacing_small'        => '',

					'bordersize'                   => '',
					'bordercolor'                  => '',
					'border_radius'                => '',

					'buttons_alignment'            => '',
					'buttons_span'                 => '',
					'load_more_btn_color'          => '',
					'load_more_btn_bg_color'       => '',
					'load_more_btn_hover_color'    => '',
					'load_more_btn_hover_bg_color' => '',
					'follow_btn_color'             => '',
					'follow_btn_bg_color'          => '',
					'follow_btn_hover_color'       => '',
					'follow_btn_hover_bg_color'    => '',

					// margin.
					'margin_top'                   => '',
					'margin_right'                 => '',
					'margin_bottom'                => '',
					'margin_left'                  => '',
					'margin_top_medium'            => '',
					'margin_right_medium'          => '',
					'margin_bottom_medium'         => '',
					'margin_left_medium'           => '',
					'margin_top_small'             => '',
					'margin_right_small'           => '',
					'margin_bottom_small'          => '',
					'margin_left_small'            => '',

					// css.
					'class'                        => '',
					'id'                           => '',
					'id'                           => '',

					// animation.
					'animation_direction'          => 'left',
					'animation_offset'             => $fusion_settings->get( 'animation_offset' ),
					'animation_speed'              => '',
					'animation_type'               => '',

					// visibility.
					'hide_on_mobile'               => fusion_builder_default_visibility( 'string' ),
				];
			}

			/**
			 * Sets the args from the attributes.
			 *
			 * @access public
			 * @since 3.0
			 * @param array $args Element attributes.
			 * @return void
			 */
			public function set_args( $args ) {
				$this->defaults = self::get_element_defaults();
				$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_instagram' );
			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 1.0
			 * @param  array  $args    Shortcode paramters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {

				$this->set_element_id( $this->instagram_counter );
				$this->set_args( $args );
				$cached_content = $this->get_cached_content();

				$html = '<div ' . FusionBuilder::attributes( 'instagram-shortcode' ) . '><div class="instagram-posts">';
				if ( $cached_content ) {
					$html .= self::build_content_from_array( $cached_content, $this->args, $this->instagram_counter );
				} else {
					$html .= self::loading_container_markup();
				}
				$html .= '</div>';

				// Load more loading container.
				if ( 'button' === $this->args['load_more'] || 'infinite' === $this->args['load_more'] ) {
					$html .= self::loading_container_markup( 'instagram-posts-loading-container', __( 'Loading the next set of instagram posts...', 'fusion-builder' ) );
				}

				$next_url = '';
				if ( ! empty( $cached_content['paging']['next'] ) ) {
					$next_url = 'data-url="' . $cached_content['paging']['next'] . '"';
				}

				// Infinite scroll container.
				if ( 'infinite' === $this->args['load_more'] ) {
					// is-active class in general will prevent infinite scroll handler from calls multiple times, in here it disable if from run before loading.
					$html .= '<div class="awb-instagram-infinite-scroll-handle is-active" ' . $next_url . '></div>';
				}

				// Buttons.
				$token = $this->get_token();
				if ( $token && ! empty( $token ) && ( 'button' === $this->args['load_more'] || 'yes' === $this->args['follow_btn'] ) ) {
					$html              .= '<div class="awb-instagram-buttons">';
					$buttons_span_class = '';
					if ( 'default' !== $this->args['buttons_span'] ) {
						$buttons_span_class = ' fusion-button-span-' . $this->args['buttons_span'];
					}
					if ( 'button' === $this->args['load_more'] ) {
						$html .= '<a href="#" class="fusion-button button-flat button-default fusion-button-default-size awb-instagram-load-more-btn' . $buttons_span_class . '" ' . $next_url . '>' . $this->args['load_more_btn_text'] . '</a>';
					}
					if ( 'yes' === $this->args['follow_btn'] ) {
						$instagram_user_link = isset( $token['username'] ) ? 'https://www.instagram.com/' . $token['username'] : '#';
						$html               .= '<a href="' . esc_url( $instagram_user_link ) . '" target="_blank" class="fusion-button button-flat button-default fusion-button-default-size awb-instagram-follow-btn ' . $buttons_span_class . '"><i class="awb-icon-instagram button-icon-left"></i>' . $this->args['follow_btn_text'] . '</a>';
					}
					$html .= '</div>';
				}
				$html .= '</div>';

				$this->instagram_counter++;

				$this->on_render();

				return apply_filters( 'fusion_element_instagram_content', $html, $args );

			}

			/**
			 * Get cached content.
			 *
			 * @access public
			 * @since 3.8
			 * @return array
			 */
			public function get_cached_content() {
				$token = $this->get_token();
				$limit = $this->args['limit'];

				// Return placeholder if no token.
				if ( empty( $token ) ) {
					return [
						'error_message' => __( 'Currently not connected, Please connect to instagram and try again.', 'fusion-builder' ),
					];
				}

				$key = "fusion-instagram-element_user-{$token['user_id']}_limit-{$limit}_page-1";

				$cached_json = isset( $token['user_id'] ) ? get_transient( $key ) : false;

				if ( $cached_json ) {
					return $cached_json;
				}

				return false;

			}

			/**
			 * Get cached content.
			 *
			 * @access public
			 * @since 3.8
			 * @param array $array   The data array.
			 * @param array $args    The arguments.
			 * @param int   $counter The counter.
			 * @return array
			 */
			public static function build_content_from_array( $array, $args = [], $counter = 1 ) {

				// Early return if has error.
				if ( ! empty( $array['error_message'] ) ) {
					return '<div class="fusion-builder-placeholder" style="width:100%;">' . $array['error_message'] . '</div>';
				}

				// @TODO. return some error message if no data.
				if ( empty( $array['data'] ) ) {
					return;
				}

				$fusion_settings = awb_get_fusion_settings();

				$html        = '';
				$posts       = is_array( $array['data'] ) ? $array['data'] : [];
				$gallery_id  = 'awb-instagram-' . $counter;
				$is_masonry  = 'masonry' === $args['layout'] ? true : false;
				$lazy_method = $fusion_settings->get( 'lazy_load' );

				foreach ( $posts as $post ) {
					$id                  = isset( $post['id'] ) ? $post['id'] : '';
					$media_type          = isset( $post['media_type'] ) ? $post['media_type'] : '';
					$media_url           = isset( $post['media_url'] ) ? $post['media_url'] : '';
					$thumbnail_url       = isset( $post['thumbnail_url'] ) ? $post['thumbnail_url'] : '';
					$post_url            = isset( $post['permalink'] ) ? $post['permalink'] : '';
					$caption             = isset( $post['caption'] ) ? $post['caption'] : '';
					$icon                = '';
					$children_gallery_id = 'children-' . $gallery_id . '-' . $id;
					$children_html       = '';

					if ( 'VIDEO' === $media_type ) {
						$media_url = $thumbnail_url;
						$icon      = '<span class="instagram-icon awb-icon-video"></span>';
					}

					if ( 'CAROUSEL_ALBUM' === $media_type ) {
						$icon = '<span class="instagram-icon awb-icon-carousel"></span>';
					}

					// Get Carousel children.
					if ( 'CAROUSEL_ALBUM' === $media_type && 'lightbox' === $args['link_type'] ) {
						$children_key  = "fusion-instagram-element-media-children-{$id}";
						$children_data = get_transient( $children_key );

						// If no children data get it from the API.
						if ( ! $children_data || ! is_array( $children_data ) ) {
							$children_url = get_rest_url() . 'awb/instagram/' . $id . '/children';
							$response     = wp_remote_get( $children_url );

							if ( is_wp_error( $response ) ) { // phpcs:ignore
								// TODO: better error handling.
							} else {
								$children_data = json_decode( wp_remote_retrieve_body( $response ), true );
								$children_data = isset( $children_data['json'] ) ? $children_data['json'] : false;
							}
						}

						// Handle children items.
						if ( isset( $children_data['data'] ) ) {
							foreach ( array_slice( $children_data['data'], 1 ) as $child ) {
								$child_url           = isset( $child['media_url'] ) ? $child['media_url'] : '';
								$child_thumbnail_url = isset( $child['thumbnail_url'] ) ? $child['thumbnail_url'] : '';
								$child_type          = isset( $child['media_type'] ) ? strtolower( $child['media_type'] ) : '';

								$child_atts   = [];
								$child_atts[] = 'href="' . esc_url( $child_url ) . '"';
								$child_atts[] = 'data-rel="' . esc_attr( $children_gallery_id ) . '"';
								$child_atts[] = 'data-media-type="' . esc_attr( $child_type ) . '"';

								if ( 'video' === $child_type ) {
									$child_url    = $child_thumbnail_url;
									$child_atts[] = "data-options=\"videoType: 'video/mp4', thumbnail: '$child_url', html5video: { poster: '$child_url'}\"";
								}
								$children_html .= '<a ' . join( ' ', $child_atts ) . ' ><img src="' . $child_url . '"></a>';
							}
							$children_html = $children_html ? '<div class="instagram-post-children">' . $children_html . '</div>' : '';
						}
					} // end carousel children.

					// Lightbox.
					if ( 'lightbox' === $args['link_type'] ) {
						$post_url = $post['media_url'];
					}

					$atts = [];
					if ( $args['link_type'] ) {
						$atts[] = 'href="' . esc_url( $post_url ) . '"';
					}

					if ( $caption ) {
						$atts[] = 'data-caption="' . esc_attr( $caption ) . '"';
					}

					if ( $args['link_target'] ) {
						$atts[] = 'target="' . esc_attr( $args['link_target'] ) . '"';
					}

					// Lightbox gallery.
					if ( 'lightbox' === $args['link_type'] ) {
						$link_gallery_id = $gallery_id;
						if ( 'CAROUSEL_ALBUM' === $media_type ) {
							$atts[]          = 'data-has-children="1"';
							$link_gallery_id = $children_gallery_id;
						}

						$atts[]  = 'data-rel="' . $link_gallery_id . '"';
						$il_type = 'image';

						if ( 'VIDEO' === $media_type ) {
							$il_type = 'video';
							$atts[]  = "data-options=\"videoType: 'video/mp4', width: '690', height: '690', thumbnail: '{$media_url}', html5video: { poster: '{$media_url}'}\"";
						}

						$atts[] = 'data-media-type="' . esc_attr( $il_type ) . '"';
					}

					// lazy loading.
					$image_atts         = [];
					$src                = 'src="' . esc_url( $media_url ) . '"';
					$image_place_holder = 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'640\' height=\'auto\' viewBox=\'0 0 640 auto\'%3E%3Crect width=\'640\' height=\'100%25\' fill=\'%23ff0\' fill-opacity=\'0\'/%3E%3C/svg%3E';

					$image_atts[] = 'alt="' . esc_attr( $caption ) . '"';

					// No lazy load if masonry layout enabled.
					if ( ! $is_masonry ) {
						if ( 'avada' === $lazy_method ) {
							$src          = 'src="' . $image_place_holder . '"';
							$image_atts[] = 'data-orig-src="' . $media_url . '"';
							$image_atts[] = 'class="lazyload"';
						} elseif ( 'wordpress' === $lazy_method ) {
							$image_atts[] = 'loading="lazy"';
						}
					} else {
						// Exclude image from lazy loading filter if layout = masonry.
						$image_atts[] = 'class="awb-instagram-masonry-image"';
					}
					$image_atts[] = $src;

					$html .= '<div class="instagram-post"><a ' . join( ' ', $atts ) . '>' . $icon . '<img ' . join( ' ', $image_atts ) . '/></a>' . $children_html . '</div>';
				}

				return $html;
			}

			/**
			 * Ajax get HTML.
			 *
			 * @access public
			 * @since 3.8
			 * @return void
			 */
			public function ajax_get_data() {
				check_ajax_referer( 'awb-instagram-nonce', 'nonce' );

				$url     = isset( $_POST['url'] ) ? sanitize_text_field( wp_unslash( $_POST['url'] ) ) : '';
				$args    = isset( $_POST['args'] ) ? sanitize_text_field( wp_unslash( $_POST['args'] ) ) : '';
				$counter = isset( $_POST['counter'] ) ? sanitize_text_field( wp_unslash( $_POST['counter'] ) ) : '';

				// Combine with defaults to ensure nothing is missing.
				$args = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), (array) $args, 'fusion_instagram' );

				$response = wp_remote_get( $url );
				$output   = [];
				if ( ! is_wp_error( $response ) ) {
					$response_body  = json_decode( wp_remote_retrieve_body( $response ), true );
					$output['html'] = self::build_content_from_array( $response_body['json'], $args, $counter );
					$output['data'] = $response_body['json'];
				}

				die( wp_json_encode( $output ) );
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

				$attr = [
					'class' => '',
					'style' => '',
				];

				$attr['id']     = $this->args['id'];
				$attr['class'] .= 'awb-instagram-element instagram-' . $this->instagram_counter . ' ' . $this->args['class'];

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				$cached_content = $this->get_cached_content();
				if ( ! $cached_content ) {
					$attr['class'] .= ' loading';
				}

				// Set next page if has cached content.
				if ( $cached_content ) {
					$attr['data-page'] = 2;
				}

				if ( '' !== $this->args['layout'] ) {
					$attr['class'] .= ' layout-' . $this->args['layout'];
				}

				if ( '' !== $this->args['hover_type'] ) {
					$attr['class'] .= ' hover-' . $this->args['hover_type'];
				}

				if ( '' !== $this->args['limit'] ) {
					$attr['data-limit'] = $this->args['limit'];
				}

				$attr['data-counter'] = $this->instagram_counter;

				if ( 'lightbox' !== $this->args['link_type'] ) {
					$attr['data-lightbox'] = 'true';
				}
				if ( '' !== $this->args['link_type'] ) {
					$attr['data-link_type'] = $this->args['link_type'];
				}
				if ( 'page' === $this->args['link_type'] && '_blank' === $this->args['link_target'] ) {
					$attr['data-link_target'] = $this->args['link_target'];
				}
				if ( '' !== $fusion_settings->get( 'lazy_load' ) ) {
					$attr['data-lazy'] = $fusion_settings->get( 'lazy_load' );
				}

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				// Aspect ratio.
				$attr['style'] .= $this->generate_aspect_ratio_styles();

				// Margins.
				$attr['style'] .= $this->build_margin_styles();

				// columns.
				$attr['style'] .= $this->build_columns_styles();

				// border.
				$attr['style'] .= $this->generate_border_styles();

				// button colors.
				$attr['style'] .= $this->generate_buttons_styles();

				return $attr;
			}

			/**
			 * Generate aspect ratio styles.
			 *
			 * @access public
			 * @since 3.8
			 * @return string CSS output.
			 */
			public function generate_aspect_ratio_styles() {
				if ( '' === $this->args['aspect_ratio'] || 'masonry' === $this->args['layout'] ) {
					return '';
				}

				$style = '';

				// Calc Ratio.
				if ( 'custom' === $this->args['aspect_ratio'] && '' !== $this->args['custom_aspect_ratio'] ) {
					$style .= '--awb-aspect-ratio: 100 / ' . $this->args['custom_aspect_ratio'] . ';';
				} else {
					$aspect_ratio = explode( '-', $this->args['aspect_ratio'] );
					$width        = isset( $aspect_ratio[0] ) ? $aspect_ratio[0] : '';
					$height       = isset( $aspect_ratio[1] ) ? $aspect_ratio[1] : '';

					$style .= '--awb-aspect-ratio:' . $width . ' / ' . $height . ';';

				}

				// Set Image Position.
				if ( '' !== $this->args['aspect_ratio_position'] ) {
					$style .= '--awb-object-position:' . $this->args['aspect_ratio_position'] . ';';
				}

				return $style;
			}

			/**
			 * Generate border styles.
			 *
			 * @access public
			 * @since 3.8
			 * @return string CSS output.
			 */
			public function generate_border_styles() {

				$style = '';

				$border_radius = FusionBuilder::validate_shortcode_attr_value( $this->args['border_radius'], 'px' );
				if ( 'round' === $this->args['border_radius'] ) {
					$border_radius = '50%';
				}
				if ( '0' !== $this->args['border_radius'] && 0 !== $this->args['border_radius'] && '0px' !== $this->args['border_radius'] && 'px' !== $this->args['border_radius'] ) {
					$style .= "--awb-bd-radius: {$border_radius};";
				}

				if ( '' !== $this->args['bordersize'] && 0 !== $this->args['bordersize'] ) {
					$border_size = FusionBuilder::validate_shortcode_attr_value( $this->args['bordersize'], 'px' );

					if ( $border_size ) {
						$style .= "--awb-bd-width: {$border_size};";
					}

					if ( '' !== $this->args['bordercolor'] ) {
						$style .= "--awb-bd-color: {$this->args['bordercolor']};";
					}
				}

				return $style;
			}

			/**
			 * Generate buttons color.
			 *
			 * @access public
			 * @since 3.8
			 * @return string CSS output.
			 */
			public function generate_buttons_styles() {

				$style = '';

				if ( '' !== $this->args['buttons_alignment'] ) {
					$style .= '--awb-buttons-alignment:' . $this->args['buttons_alignment'] . ';';
				}

				if ( '' !== $this->args['load_more_btn_color'] ) {
					$style .= '--awb-more-btn-color:' . $this->args['load_more_btn_color'] . ';';
				}

				if ( '' !== $this->args['load_more_btn_bg_color'] ) {
					$style .= '--awb-more-btn-bg:' . $this->args['load_more_btn_bg_color'] . ';';
				}

				if ( '' !== $this->args['load_more_btn_hover_color'] ) {
					$style .= '--awb-more-btn-hover-color:' . $this->args['load_more_btn_hover_color'] . ';';
				}

				if ( '' !== $this->args['load_more_btn_hover_bg_color'] ) {
					$style .= '--awb-more-btn-hover-bg:' . $this->args['load_more_btn_hover_bg_color'] . ';';
				}

				if ( '' !== $this->args['follow_btn_color'] ) {
					$style .= '--awb-follow-btn-color:' . $this->args['follow_btn_color'] . ';';
				}

				if ( '' !== $this->args['follow_btn_bg_color'] ) {
					$style .= '--awb-follow-btn-bg:' . $this->args['follow_btn_bg_color'] . ';';
				}

				if ( '' !== $this->args['follow_btn_hover_color'] ) {
					$style .= '--awb-follow-btn-hover-color:' . $this->args['follow_btn_hover_color'] . ';';
				}

				if ( '' !== $this->args['follow_btn_hover_bg_color'] ) {
					$style .= '--awb-follow-btn-hover-bg:' . $this->args['follow_btn_hover_bg_color'] . ';';
				}

				return $style;
			}
			/**
			 * Builds margin styles.
			 *
			 * @access public
			 * @since 1.0
			 * @return string
			 */
			public function build_margin_styles() {
				// Responsive Margin.
				$styles = '';

				foreach ( [ 'large', 'medium', 'small' ] as $size ) {
					$margin_styles = '';
					$device_abbr   = '';
					if ( 'small' === $size ) {
						$device_abbr = 'sm-';
					}
					if ( 'medium' === $size ) {
						$device_abbr = 'md-';
					}
					foreach ( [ 'top', 'right', 'bottom', 'left' ] as $direction ) {
						$margin_key = 'large' === $size ? 'margin_' . $direction : 'margin_' . $direction . '_' . $size;
						if ( '' !== $this->args[ $margin_key ] ) {
							$margin_styles .= '--awb-' . $device_abbr . 'margin-' . $direction . ':' . fusion_library()->sanitize->get_value_with_unit( $this->args[ $margin_key ] ) . ';';
						}
					}

					if ( '' === $margin_styles ) {
						continue;
					}

					$styles .= $margin_styles;
				}
				return $styles;
			}
			/**
			 * Build Responsive columns.
			 *
			 * @access public
			 * @since 1.0
			 * @return string
			 */
			public function build_columns_styles() {
				$styles = '';

				foreach ( [ 'large', 'medium', 'small' ] as $size ) {
					$device_abbr = '';
					if ( 'small' === $size ) {
						$device_abbr = 'sm-';
					}
					if ( 'medium' === $size ) {
						$device_abbr = 'md-';
					}

					$columns         = 'large' === $size ? $this->args['columns'] : $this->args[ 'columns_' . $size ];
					$columns_spacing = 'large' === $size ? $this->args['columns_spacing'] : $this->args[ 'columns_spacing_' . $size ];

					if ( '' !== $columns ) {
						$styles .= '--awb-' . $device_abbr . 'column-width:' . 100 / intval( $columns ) . '%;';
					}
					if ( '' !== $columns_spacing ) {
						$styles .= '--awb-' . $device_abbr . 'column-space:' . $columns_spacing . ';';
					}
				}

				return $styles;
			}
			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/instagram.min.css' );

				if ( class_exists( 'Avada' ) ) {
					$version = Avada::get_theme_version();
					Fusion_Media_Query_Scripts::$media_query_assets[] = [
						'avada-instagram-md',
						get_template_directory_uri() . '/assets/css/media/instagram-md.min.css',
						[],
						$version,
						Fusion_Media_Query_Scripts::get_media_query_from_key( 'fusion-max-medium' ),
					];
					Fusion_Media_Query_Scripts::$media_query_assets[] = [
						'avada-instagram-sm',
						get_template_directory_uri() . '/assets/css/media/instagram-sm.min.css',
						[],
						$version,
						Fusion_Media_Query_Scripts::get_media_query_from_key( 'fusion-max-small' ),
					];
				}
			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.1
			 * @return array $sections Instagram settings.
			 */
			public function add_options() {

				return [
					'instagram_shortcode_section' => [
						'label'  => esc_html__( 'Instagram', 'fusion-builder' ),
						'id'     => 'instagram_shortcode_section',
						'type'   => 'accordion',
						'icon'   => 'fusiona-instagram',
						'fields' => [
							'instagram_oauth'         => [
								'label'       => '',
								'description' => $this->oauth_option_render(),
								'id'          => 'instagram_oauth',
								'type'        => 'custom',
							],
							'instagram_cache_timeout' => [
								'label'       => esc_html__( 'Check For New Posts', 'fusion-builder' ),
								'description' => esc_html__( 'Select when check instagram for new posts.', 'fusion-builder' ),
								'id'          => 'instagram_cache_timeout',
								'type'        => 'select',
								'default'     => 'hour',
								'choices'     => [
									'half_hour' => esc_html__( 'Every 30 Minutes', 'fusion-builder' ),
									'hour'      => esc_html__( 'Every Hour', 'fusion-builder' ),
									'six_hours' => esc_html__( 'Every 6 Hours', 'fusion-builder' ),
									'half_day'  => esc_html__( 'Every 12 Hours', 'fusion-builder' ),
									'day'       => esc_html__( 'Daily', 'fusion-builder' ),
								],
							],
							'reset_instagram_caches'  => [
								'label'         => esc_html__( 'Reset Instagram Caches', 'Avada' ),
								'description'   => esc_html__( 'Reset all Instagram data.', 'Avada' ),
								'id'            => 'reset_instagram_caches',
								'default'       => '',
								'type'          => 'raw',
								'content'       => '<a class="button button-secondary" href="#" onclick="fusionResetInstagramCache(event);" target="_self" >' . esc_html__( 'Reset Instagram Caches', 'Avada' ) . '</a><span class="spinner fusion-spinner"></span>',
								'full_width'    => false,
								'transport'     => 'postMessage', // No need to refresh the page.
								'hide_on_front' => true,
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
				Fusion_Dynamic_JS::enqueue_script( 'fusion-instagram' );

				Fusion_Dynamic_JS::localize_script(
					'fusion-instagram',
					'fusionInstagramVars',
					[
						'ajax_url'          => admin_url( 'admin-ajax.php' ),
						'nonce'             => wp_create_nonce( 'awb-instagram-nonce' ),
						'api_url'           => get_rest_url(),
						'no_more_posts_msg' => '<em>' . __( 'All items displayed.', 'fusion-builder' ) . '</em>',
						'is_admin'          => current_user_can( 'manage_options' ) ? true : false,
					]
				);
			}

			/**
			 * Fusion loading container.
			 *
			 * @access public
			 * @since 3.2
			 * @param string $class The loading container class.
			 * @param string $msg   The loading container message.
			 * @return string
			 */
			public static function loading_container_markup( $class = '', $msg = false ) {
				$html = '<div class="fusion-loading-container fusion-clearfix ' . esc_attr( $class ) . '">
				<div class="fusion-loading-spinner">
					<div class="fusion-spinner-1"></div>
					<div class="fusion-spinner-2"></div>
					<div class="fusion-spinner-3"></div>
				</div>';

				if ( $msg ) {
					$html .= '<div class="fusion-loading-msg"><em>' . $msg . '</em></div>';
				}

				$html .= '</div>';

				return $html;
			}

			/**
			 * Instagram oauth option.
			 *
			 * @access public
			 * @since 3.8
			 * @return string HTMl markup for instagram oauth option.
			 */
			public function oauth_option_render() {
				$auth_url = 'https://api.instagram.com/oauth/authorize?response_type=code&client_id=678411916571642&redirect_uri=https://updates.theme-fusion.com/instagram-api&scope=user_profile,user_media&state=' . rawurlencode( admin_url( 'admin.php?page=avada' ) );

				$type = 'connected';
				if ( isset( $_GET['error'] ) && ! empty( $_GET['error'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
					$type = 'error';
				} elseif ( ! $this->get_token() ) {
					$type = 'no_token';
				}

				$output  = '<div id="fusion-instagram-content" class="fusion-redux-important-notice">';
				$output .= '<div data-id="error" style="display:' . ( 'error' === $type ? 'flex' : 'none' ) . '">';
				$output .= '<span><strong>' . esc_html__( 'There was a problem when trying to connect. ', 'fusion-builder' ) . '</strong>';
				$output .= '<a target="_blank" href="#">' . esc_html__( 'Instagram element documentation.', 'fusion-builder' ) . '</a></span>';
				$output .= '<a class="button-primary" target="_blank" href="' . $auth_url . '">' . esc_html__( 'Try again.', 'fusion-builder' ) . '</a>';
				$output .= '</div>';
				$output .= '<div data-id="no_token"  style="display:' . ( 'no_token' === $type ? 'flex' : 'none' ) . '">';
				$output .= '<span><strong>' . esc_html__( 'Currently not connected. ', 'fusion-builder' ) . '</strong>';
				$output .= '<a target="_blank" href="#">' . esc_html__( 'Instagram element documentation.', 'fusion-builder' ) . '</a></span>';
				$output .= '<a class="button-primary" target="_blank" href="' . $auth_url . '">' . esc_html__( 'Connect with Instagram', 'fusion-builder' ) . '</a>';
				$output .= '</div>';
				$output .= '<div data-id="connected"  style="display:' . ( 'connected' === $type ? 'flex' : 'none' ) . '">';
				$output .= '<strong>' . esc_html__( 'Connected with Instagram', 'fusion-builder' ) . '</strong>';
				$output .= '<a class="button-primary" target="_blank" href="' . esc_url( admin_url( 'admin.php?page=avada&revoke_instagram=1' ) ) . '">' . __( 'Revoke Access', 'fusion-builder' ) . '</a>';
				$output .= '</div>';

				return $output;
			}


			/**
			 * Get Instagram token.
			 *
			 * @access public
			 * @since 3.8
			 * @return string Instagram token.
			 */
			public function get_token() {
				// We already have retrieved a token, continue to use it.
				if ( null !== $this->token ) {
					return $this->token;
				}

				$this->token = get_option( 'fusion_instagram_token' );

				// No tokens.
				if ( ! $this->token ) {
					$this->token = false;
				}

				// Return what we have.
				return $this->token;
			}


			/**
			 * Enqueue script for handling OAuth.
			 *
			 * @since 3.8
			 * @access public
			 * @return mixed
			 */
			public function enqueue_api_script() {
				wp_enqueue_script( 'fusion_instagram_oauth', FUSION_BUILDER_PLUGIN_URL . 'assets/admin/js/fusion-instagram-oauth.js', [], FUSION_BUILDER_VERSION, true );
				wp_localize_script(
					'fusion_instagram_oauth',
					'fusionInstagramOAuth',
					[
						'status' => $this->localize_status,
					]
				);

			}

			/**
			 * Authenticate.
			 *
			 * @access public
			 * @since 3.8
			 */
			public function authenticate() {
				$token = [];

				// phpcs:disable WordPress.Security.NonceVerification

				if ( ! isset( $_GET['token'] ) ) {
					$this->localize_status = 'error';
				}

				if ( isset( $_GET['token'] ) ) {
					$token['token']        = sanitize_text_field( wp_unslash( $_GET['token'] ) );
					$this->localize_status = 'success';
				}
				if ( isset( $_GET['expires_in'] ) ) {
					$token['expires_in'] = sanitize_text_field( wp_unslash( $_GET['expires_in'] ) );

					// Expire date.
					$expire_date          = new DateTime( '+' . sanitize_text_field( wp_unslash( $_GET['expires_in'] ) ) . ' seconds' );
					$token['expire_date'] = $expire_date->format( 'Y-m-d' );
				}
				if ( isset( $_GET['account_type'] ) ) {
					$token['account_type'] = sanitize_text_field( wp_unslash( $_GET['account_type'] ) );
				}
				if ( isset( $_GET['username'] ) ) {
					$token['username'] = sanitize_text_field( wp_unslash( $_GET['username'] ) );
				}
				if ( isset( $_GET['user_id'] ) ) {
					$token['user_id'] = sanitize_text_field( wp_unslash( $_GET['user_id'] ) );
				}

				// phpcs:enable WordPress.Security.NonceVerification

				update_option( 'fusion_instagram_token', $token );
			}

			/**
			 * Revoke access.
			 *
			 * @access public
			 * @since 3.8
			 */
			public function revoke_access() {
				delete_option( 'fusion_instagram_token' );

				// delete instagram cache on revoke.
				$this->reset_caches_handler();

				$this->localize_status = 'revoke';
			}

			/**
			 * Refresh token before expire date.
			 *
			 * @access public
			 * @since 3.8
			 */
			public function refresh_token() {
				$token = $this->get_token();

				// Exit if no token or expire date.
				if ( ! $token || ! is_array( $token ) || ! isset( $token['token'] ) || ! isset( $token['expire_date'] ) ) {
					return;
				}

				$expire_date = isset( $token['expire_date'] ) ? $token['expire_date'] : false;

				// Check 7 days before the expire date.
				$expire_date_gap = date( 'Y-m-d', strtotime( $expire_date . '-7 days' ) );
				$today           = date( 'Y-m-d' );

				// Exit if today before the expire date gap.
				if ( $today < $expire_date_gap ) {
					return;
				}

				// Refresh token request.
				$response = wp_remote_get( 'https://graph.instagram.com/refresh_access_token?grant_type=ig_refresh_token&access_token=' . $token['token'] );

				// Do nothing on error.
				if ( is_wp_error( $response ) ) {
					return;
				}

				$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

				// Update the current token.
				if ( isset( $response_body['access_token'], $response_body['expires_in'] ) ) {
					$token['token']      = $response_body['access_token'];
					$token['expires_in'] = $response_body['expires_in'];
					// Expire date.
					$expire_date          = new DateTime( '+' . $response_body['expires_in'] . ' seconds' );
					$token['expire_date'] = $expire_date->format( 'Y-m-d' );
					update_option( 'fusion_instagram_token', $token );
				}

			}

			/**
			 * Instagram user media endpoint.
			 *
			 * @access public
			 * @param array $data The enpoint data.
			 * @since 3.8
			 */
			public function media_endpoint( $data ) {
				$output  = [];
				$limit   = isset( $data['limit'] ) ? $data['limit'] : '20';
				$page    = isset( $data['page'] ) ? $data['page'] : '1';
				$url     = isset( $data['url'] ) ? $data['url'] : '';
				$token   = $this->get_token();
				$timeout = self::get_timeout();

				if ( empty( $token ) ) {
						$output['code']          = 400;
						$output['error_message'] = __( 'Currently not connected, Please connect to instagram and try again.', 'fusion-builder' );
				}

				if ( ! empty( $token ) ) {
					$key         = "fusion-instagram-element_user-{$token['user_id']}_limit-{$limit}_page-{$page}";
					$cached_json = isset( $token['user_id'] ) ? get_transient( $key ) : false;

					if ( $cached_json ) {
						$output['code'] = 200;
						$output['json'] = $cached_json;
					}
				}

				// Get IG JSON request.
				if ( $token && is_array( $token ) && ! $cached_json ) {
					if ( ! $url ) {
						$url = "https://graph.instagram.com/v13.0/{$token['user_id']}/media/?limit={$limit}&fields=caption,media_type,media_url,permalink,thumbnail_url,id&access_token={$token['token']}";
					}
						$response = wp_remote_get( $url );

						// Do nothing on error.
					if ( is_wp_error( $response ) ) {
						$output['code']          = 400;
						$output['error_message'] = $response->get_error_message();
					} else {
						$output['code'] = 200;
						$json           = json_decode( wp_remote_retrieve_body( $response ), true );
						$output['json'] = $json;

						if ( isset( $json['error'] ) ) {
							$output['code']          = 400;
							$output['error_message'] = $json['error']['message'];
						} else {
							set_transient( $key, $json, $timeout );

							// Add the transient dynamic key to the Instagram transient keys.
							// to delete dynamic transient properly especially if user has object cache enabled.
							$saved_keys = get_transient( 'fusion-instagram-transient-keys' );
							if ( ! is_array( $saved_keys ) ) {
								$saved_keys = [];
							}
							$saved_keys[] = $key;

							set_transient( 'fusion-instagram-transient-keys', $saved_keys, $timeout );
						}
					}
				}

				return $output;
			}

			/**
			 * Instagram user media endpoint.
			 *
			 * @access public
			 * @param array $data The enpoint data.
			 * @since 3.8
			 */
			public function media_children_endpoint( $data ) {
				$output  = [];
				$token   = $this->get_token();
				$id      = $data['id'];
				$timeout = self::get_timeout();

				if ( empty( $token ) ) {
					$output['code']          = 400;
					$output['error_message'] = __( 'Currently not connected, Please connect to instagram and try again.', 'fusion-builder' );
				}

				$key         = "fusion-instagram-element-media-children-{$id}";
				$cached_json = get_transient( $key );

				if ( $cached_json ) {
					$output['code'] = 200;
					$output['json'] = $cached_json;
				}

				// Get IG JSON request.
				if ( $token && is_array( $token ) && ! $cached_json ) {
						$url      = "https://graph.instagram.com/v13.0/{$id}/children/?fields=media_type,media_url,permalink,thumbnail_url,id&access_token={$token['token']}";
						$response = wp_remote_get( $url );

					if ( is_wp_error( $response ) ) {
						$output['code']          = 400;
						$output['error_message'] = $response->get_error_message();
					} else {

						$output['code'] = 200;
						$json           = json_decode( wp_remote_retrieve_body( $response ), true );
						$output['json'] = $json;

						if ( isset( $json['error'] ) ) {
							$output['code']          = 400;
							$output['error_message'] = $json['error']['message'];
						} else {
							set_transient( $key, $json, $timeout );

							// Add the transient dynamic key to the Instagram transient keys.
							// to delete dynamic transient properly especially if user has object cache enabled.
							$saved_keys = get_transient( 'fusion-instagram-transient-keys' );
							if ( ! is_array( $saved_keys ) ) {
								$saved_keys = [];
							}
							$saved_keys[] = $key;

							set_transient( 'fusion-instagram-transient-keys', $saved_keys, $timeout );
						}
					}
				}

				return $output;
			}

			/**
			 * Registers instagrame endpoint.
			 *
			 * @access public
			 * @since 3.8
			 */
			public function register_instagram_endpoint() {
				// User media.
				register_rest_route(
					'awb/instagram',
					'/media',
					[
						'methods'             => 'GET',
						'callback'            => [ $this, 'media_endpoint' ],
						'permission_callback' => '__return_true',
					]
				);

				// media_children.
				register_rest_route(
					'awb/instagram',
					'/(?P<id>\d+)/children',
					[
						'methods'             => 'GET',
						'callback'            => [ $this, 'media_children_endpoint' ],
						'permission_callback' => '__return_true',
					]
				);
			}


			/**
			 * Delete cache.
			 *
			 * @access public
			 * @since 3.8
			 * @return string
			 */
			public static function get_timeout() {
				$fusion_settings = awb_get_fusion_settings();
				$timeout_option  = $fusion_settings->get( 'instagram_cache_timeout' );
				$timeout         = DAY_IN_SECONDS; // day is the default.

				switch ( $timeout_option ) {
					case 'half_hour':
						$timeout = 60 * 30;
						break;
					case 'hour':
						$timeout = HOUR_IN_SECONDS;
						break;
					case 'half_day':
						$timeout = 60 * 60 * 12;
						break;
					case 'week':
						$timeout = WEEK_IN_SECONDS;
						break;
				}
				return $timeout;
			}

			/**
			 * Delete cache.
			 *
			 * @access public
			 * @since 3.8
			 * @return void
			 */
			public function delete_cache() {
				$transient_keys = get_transient( 'fusion-instagram-transient-keys' );
				if ( is_array( $transient_keys ) ) {
					foreach ( $transient_keys as $key ) {
						delete_transient( $key );
					}
					delete_transient( 'fusion-instagram-transient-keys' );
				}
			}

			/**
			 * Handles resetting caches.
			 *
			 * @access public
			 * @since 3.8
			 * @return void
			 */
			public function reset_caches_handler() {
				if ( is_multisite() && is_main_site() ) {
					$sites = get_sites();
					foreach ( $sites as $site ) {
						switch_to_blog( $site->blog_id );
							$this->delete_cache();
						restore_current_blog();
					}
					return;
				}
				$this->delete_cache();
			}
		}
	}

	new FusionSC_Instagram();

}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_instagram_element() {

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Instagram',
			[
				'name'      => esc_attr__( 'Instagram', 'fusion-builder' ),
				'shortcode' => 'fusion_instagram',
				'icon'      => 'fusiona-instagram',
				'params'    => [
					[
						'type'        => 'range',
						'param_name'  => 'limit',
						'heading'     => esc_attr__( 'Number Of Posts', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the number of instagram posts you want to display.', 'fusion-builder' ),
						'value'       => 10,
						'min'         => 1,
						'max'         => 100,
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Layout', 'fusion-builder' ),
						'description' => __( 'Select the instagram feed layout type. <strong>Note:</strong> Image lazy loading disabled for masonry layout.', 'fusion-builder' ),
						'param_name'  => 'layout',
						'value'       => [
							'grid'    => esc_attr__( 'Grid', 'fusion-builder' ),
							'masonry' => esc_attr__( 'Masonry', 'fusion-builder' ),
						],
						'default'     => 'grid',
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Images Aspect Ratio', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the aspect ratio of the images. Images will be cropped.', 'fusion-builder' ),
						'param_name'  => 'aspect_ratio',
						'value'       => [
							''       => esc_attr__( 'Automatic', 'fusion-builder' ),
							'1-1'    => esc_attr__( '1:1', 'fusion-builder' ),
							'2-1'    => esc_attr__( '2:1', 'fusion-builder' ),
							'2-3'    => esc_attr__( '2:3', 'fusion-builder' ),
							'3-1'    => esc_attr__( '3:1', 'fusion-builder' ),
							'3-2'    => esc_attr__( '3:2', 'fusion-builder' ),
							'4-1'    => esc_attr__( '4:1', 'fusion-builder' ),
							'4-3'    => esc_attr__( '4:3', 'fusion-builder' ),
							'5-4'    => esc_attr__( '5:4', 'fusion-builder' ),
							'16-9'   => esc_attr__( '16:9', 'fusion-builder' ),
							'9-16'   => esc_attr__( '9:16', 'fusion-builder' ),
							'21-9'   => esc_attr__( '21:9', 'fusion-builder' ),
							'9-21'   => esc_attr__( '9:21', 'fusion-builder' ),
							'custom' => esc_attr__( 'Custom', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'masonry',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Custom Aspect Ratio', 'fusion-builder' ),
						'description' => esc_attr__( 'Set a custom aspect ratio for the images.', 'fusion-builder' ),
						'param_name'  => 'custom_aspect_ratio',
						'min'         => 0,
						'max'         => 500,
						'value'       => 100,
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'masonry',
								'operator' => '!=',
							],
							[
								'element'  => 'aspect_ratio',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'image_focus_point',
						'heading'     => esc_attr__( 'Image Focus Point', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the image focus point by dragging the blue dot.', 'fusion-builder' ),
						'param_name'  => 'aspect_ratio_position',
						'image'       => 'element_content',
						'image_id'    => 'image_id',
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'masonry',
								'operator' => '!=',
							],
							[
								'element'  => 'aspect_ratio',
								'value'    => '',
								'operator' => '!=',
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
					],
					[
						'type'        => 'radio_button_set',
						'param_name'  => 'link_type',
						'heading'     => esc_attr__( 'Image Link', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose where the image should link to.', 'fusion-builder' ),
						'default'     => '',
						'value'       => [
							''         => esc_html__( 'None', 'fusion-builder' ),
							'lightbox' => esc_html__( 'Lightbox', 'fusion-builder' ),
							'post'     => esc_html__( 'Instagram Post', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'param_name'  => 'link_target',
						'heading'     => esc_attr__( 'Image Link Target', 'fusion-builder' ),
						'description' => __( '_self = open in same window<br />_blank = open in new window.', 'fusion-builder' ),
						'default'     => '',
						'value'       => [
							''       => esc_html__( '_self', 'fusion-builder' ),
							'_blank' => esc_html__( '_blank', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'link_type',
								'value'    => 'post',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'param_name'  => 'load_more',
						'heading'     => esc_attr__( 'Load More', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose load more type.', 'fusion-builder' ),
						'default'     => '',
						'value'       => [
							''         => esc_html__( 'None', 'fusion-builder' ),
							'button'   => esc_html__( 'Load More Button', 'fusion-builder' ),
							'infinite' => esc_html__( 'Infinite Scrolling', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'textfield',
						'param_name'  => 'load_more_btn_text',
						'heading'     => esc_attr__( 'Load More Button Text', 'fusion-builder' ),
						'description' => esc_attr__( 'Insert custom load more button text.', 'fusion-builder' ),
						'default'     => esc_html__( 'Load More', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'load_more',
								'value'    => 'button',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'param_name'  => 'follow_btn',
						'heading'     => esc_attr__( 'Follow Button', 'fusion-builder' ),
						'description' => esc_attr__( 'Enable/Disable follow button.', 'fusion-builder' ),
						'default'     => 'no',
						'value'       => [
							'yes' => esc_html__( 'Yes', 'fusion-builder' ),
							'no'  => esc_html__( 'No', 'fusion-builder' ),

						],
					],
					[
						'type'        => 'textfield',
						'param_name'  => 'follow_btn_text',
						'heading'     => esc_attr__( 'Follow Button Text', 'fusion-builder' ),
						'description' => esc_attr__( 'Insert custom follow button text.', 'fusion-builder' ),
						'default'     => esc_html__( 'Follow Us On Instagram', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'follow_btn',
								'value'    => 'yes',
								'operator' => '==',
							],
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
					'fusion_sticky_visibility_placeholder' => [],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS Class', 'fusion-builder' ),
						'param_name'  => 'class',
						'value'       => '',
						'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
						'param_name'  => 'id',
						'value'       => '',
						'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
					],
					'fusion_margin_placeholder'            => [
						'param_name' => 'margin',
						'value'      => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
						],
						'responsive' => [
							'state' => 'large',
						],
					],
					[
						'type'        => 'range',
						'param_name'  => 'columns',
						'heading'     => esc_attr__( 'Number Of Columns', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the number of columns to display.', 'fusion-builder' ),
						'value'       => 4,
						'min'         => 1,
						'max'         => 10,
						'responsive'  => [
							'state' => 'large',
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'range',
						'param_name'  => 'columns_spacing',
						'heading'     => esc_attr__( 'Column Spacing', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the spacing between columns.', 'fusion-builder' ),
						'value'       => 10,
						'min'         => 0,
						'max'         => 100,
						'responsive'  => [
							'state' => 'large',
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Instagram Post Border Size', 'fusion-builder' ),
						'description' => esc_attr__( 'In pixels.', 'fusion-builder' ),
						'param_name'  => 'bordersize',
						'value'       => 0,
						'min'         => 0,
						'max'         => 50,
						'step'        => 1,
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Instagram Post Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the border color. ', 'fusion-builder' ),
						'param_name'  => 'bordercolor',
						'value'       => '',
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'bordersize',
								'value'    => 0,
								'operator' => '>',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Instagram Post Border Radius', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the instagram post border radius. In pixels (px), ex: 1px, or "round". ', 'fusion-builder' ),
						'param_name'  => 'border_radius',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Buttons Span', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls if the buttons spans the full width/remaining width of row.', 'fusion-builder' ),
						'param_name'  => 'buttons_span',
						'default'     => 'no',
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'value'       => [
							'default' => esc_attr__( 'Default', 'fusion-builder' ),
							'yes'     => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'      => esc_attr__( 'No', 'fusion-builder' ),
						],
						'or'          => true,
						'dependency'  => [
							[
								'element'  => 'load_more',
								'value'    => 'button',
								'operator' => '==',
							],
							[
								'element'  => 'follow_btn',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_html__( 'Buttons Alignment', 'fusion-builder' ),
						'description' => esc_html__( 'Select buttons alignment.', 'fusion-builder' ),
						'param_name'  => 'buttons_alignment',
						'default'     => 'center',
						'grid_layout' => true,
						'back_icons'  => true,
						'icons'       => [
							'flex-start'    => '<span class="fusiona-horizontal-flex-start"></span>',
							'center'        => '<span class="fusiona-horizontal-flex-center"></span>',
							'flex-end'      => '<span class="fusiona-horizontal-flex-end"></span>',
							'space-between' => '<span class="fusiona-horizontal-space-between"></span>',
						],
						'value'       => [
							// We use "start/end" terminology because flex direction changes depending on RTL/LTR.
							'flex-start'    => esc_html__( 'Flex Start', 'fusion-builder' ),
							'center'        => esc_html__( 'Center', 'fusion-builder' ),
							'flex-end'      => esc_html__( 'Flex End', 'fusion-builder' ),
							'space-between' => esc_html__( 'Space Between', 'fusion-builder' ),
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'or'          => true,
						'dependency'  => [
							[
								'element'  => 'load_more',
								'value'    => 'button',
								'operator' => '==',
							],
							[
								'element'  => 'follow_btn',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'             => 'subgroup',
						'heading'          => esc_html__( 'Load More Button Styling', 'fusion-builder' ),
						'description'      => esc_html__( 'Customize Load More and follow button colors.', 'fusion-builder' ),
						'param_name'       => 'load_more_btn_styles',
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
								'element'  => 'load_more',
								'value'    => 'button',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Select load more button text color.', 'fusion-builder' ),
						'param_name'  => 'load_more_btn_color',
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'value'       => '',
						'default'     => 'var(--awb-color8)',
						'subgroup'    => [
							'name' => 'load_more_btn_styles',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'load_more',
								'value'    => 'button',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Select load more button background color.', 'fusion-builder' ),
						'param_name'  => 'load_more_btn_bg_color',
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'value'       => '',
						'default'     => 'var(--awb-color3)',
						'subgroup'    => [
							'name' => 'load_more_btn_styles',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'load_more',
								'value'    => 'button',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Hover Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Select load more button hover text color.', 'fusion-builder' ),
						'param_name'  => 'load_more_btn_hover_color',
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'value'       => '',
						'default'     => 'var(--awb-color1)',
						'subgroup'    => [
							'name' => 'load_more_btn_styles',
							'tab'  => 'active',
						],
						'dependency'  => [
							[
								'element'  => 'load_more',
								'value'    => 'button',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Hover Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Select load more button hover background color.', 'fusion-builder' ),
						'param_name'  => 'load_more_btn_hover_bg_color',
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'value'       => '',
						'default'     => 'var(--awb-color5)',
						'subgroup'    => [
							'name' => 'load_more_btn_styles',
							'tab'  => 'active',
						],
						'dependency'  => [
							[
								'element'  => 'load_more',
								'value'    => 'button',
								'operator' => '==',
							],
						],
					],
					[
						'type'             => 'subgroup',
						'heading'          => esc_html__( 'Follow Button Button Styling', 'fusion-builder' ),
						'description'      => esc_html__( 'Customize the follow button colors.', 'fusion-builder' ),
						'param_name'       => 'follow_btn_styles',
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
								'element'  => 'follow_btn',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Select follow button text color.', 'fusion-builder' ),
						'param_name'  => 'follow_btn_color',
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'value'       => '',
						'default'     => 'var(--awb-color1)',
						'subgroup'    => [
							'name' => 'follow_btn_styles',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'follow_btn',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Select follow button background color.', 'fusion-builder' ),
						'param_name'  => 'follow_btn_bg_color',
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'value'       => '',
						'default'     => 'var(--awb-color7)',
						'subgroup'    => [
							'name' => 'follow_btn_styles',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'follow_btn',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Hover Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Select follow button hover text color.', 'fusion-builder' ),
						'param_name'  => 'follow_btn_hover_color',
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'value'       => '',
						'default'     => 'var(--awb-color1)',
						'subgroup'    => [
							'name' => 'follow_btn_styles',
							'tab'  => 'active',
						],
						'dependency'  => [
							[
								'element'  => 'follow_btn',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Hover Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Select follow button hover background color.', 'fusion-builder' ),
						'param_name'  => 'follow_btn_hover_bg_color',
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'value'       => '',
						'default'     => 'var(--awb-color5)',
						'subgroup'    => [
							'name' => 'follow_btn_styles',
							'tab'  => 'active',
						],
						'dependency'  => [
							[
								'element'  => 'follow_btn',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					'fusion_animation_placeholder'         => [
						'preview_selector' => '.awb-instagram-element',
					],
					[
						'type'        => 'raw',
						'description' => '<div></div>',
						'param_name'  => 'extra_space',
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'value'       => '',
					],
				],
			]
		)
	);

}
add_action( 'fusion_builder_before_init', 'fusion_instagram_element' );
