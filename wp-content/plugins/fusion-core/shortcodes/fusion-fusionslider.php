<?php
/**
 * Avada-Builder Shortcode Element.
 *
 * @package Avada-Core
 * @since 3.1.0
 */

if ( function_exists( 'fusion_is_element_enabled' ) && fusion_is_element_enabled( 'fusion_fusionslider' ) ) {

	if ( ! class_exists( 'FusionSC_FusionSlider' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @package fusion-builder
		 * @since 1.0
		 */
		class FusionSC_FusionSlider {

			/**
			 * Parent SC arguments.
			 *
			 * @static
			 * @access public
			 * @since 1.0
			 * @var array
			 */
			public static $parent_args;

			/**
			 * The slider settings.
			 *
			 * @static
			 * @access public
			 * @since 1.0
			 * @var array
			 */
			public static $slider_settings;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {

				add_shortcode( 'fusion_fusionslider', [ $this, 'render_parent' ] );

				add_filter( 'fusion_attr_fusion-slider-wrapper', [ $this, 'wrapper_attr' ] );
				add_filter( 'fusion_attr_fusion-slider-container', [ $this, 'container_attr' ] );

				// Ajax mechanism for query related part.
				add_action( 'wp_ajax_get_fusion_fusionslider', [ $this, 'ajax_query' ] );
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

				return apply_filters(
					'fusion_fusionslider_default_parameter',
					[
						'margin_top'     => '',
						'margin_right'   => '',
						'margin_bottom'  => '',
						'margin_left'    => '',
						'hide_on_mobile' => fusion_builder_default_visibility( 'string' ),
						'class'          => '',
						'id'             => '',
						'name'           => '',
						'full_height'    => 'no',
						'offset'         => '',
					]
				);
			}

			/**
			 * Render the parent shortcode
			 *
			 * @access public
			 * @param  array  $args    Shortcode paramters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render_parent( $args, $content = '' ) {
				global $fusion_library;

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_fusionslider' );

				extract( $defaults );

				$defaults['margin_bottom'] = FusionBuilder::validate_shortcode_attr_value( $defaults['margin_bottom'], 'px' );
				$defaults['margin_top']    = FusionBuilder::validate_shortcode_attr_value( $defaults['margin_top'], 'px' );

				self::$parent_args = $defaults;

				ob_start();

				$term = $name;

				$term_details = get_term_by( 'slug', $term, 'slide-page' );

				if ( ! $term_details ) {
					if ( shortcode_exists( 'fusion_alert' ) ) {
						return do_shortcode( '[fusion_alert type="error"]Incorrect slider name. Please make sure to use a valid slider slug.[/fusion_alert]' );
					} else {
						return '<h3 style="color:#b94a48;">' . esc_html__( 'Incorrect slider name. Please make sure to use a valid slider slug.', 'fusion-core' ) . '</h3>';
					}
				}

				$slider_settings = fusion_data()->term_meta( $term_details->term_id )->get_all_meta();

				if ( ! isset( $slider_settings['slider_width'] ) || '' === $slider_settings['slider_width'] ) {
					$slider_settings['slider_width'] = '100%';
				}

				if ( ! isset( $slider_settings['slider_height'] ) || '' === $slider_settings['slider_height'] ) {
					$slider_settings['slider_height'] = '300px';
				}

				if ( ! isset( $slider_settings['full_screen'] ) ) {
					$slider_settings['full_screen'] = false;
				}

				if ( ! isset( $slider_settings['nav_box_width'] ) ) {
					$slider_settings['nav_box_width'] = '63px';
				}

				if ( ! isset( $slider_settings['nav_box_height'] ) ) {
					$slider_settings['nav_box_height'] = '63px';
				}

				if ( ! isset( $slider_settings['nav_arrow_size'] ) ) {
					$slider_settings['nav_arrow_size'] = '25px';
				}

				if ( $slider_settings['nav_box_height'] ) {
					$nav_box_height_half = intval( $slider_settings['nav_box_height'] ) / 2;
				}

				self::$slider_settings = $slider_settings;

				$content_max_width = '';
				if ( isset( $slider_settings['slider_content_width'] ) && '' !== $slider_settings['slider_content_width'] ) {
					$content_max_width = 'max-width:' . $slider_settings['slider_content_width'];
				}

				if ( ! isset( $slider_settings['slider_indicator'] ) ) {
					$slider_settings['slider_indicator'] = '';
				}

				if ( ! isset( $slider_settings['slider_indicator_color'] ) || '' === $slider_settings['slider_indicator_color'] ) {
					$slider_settings['slider_indicator_color'] = '#ffffff';
				}

				$orderby = ( isset( $slider_settings['orderby'] ) ) ? $slider_settings['orderby'] : 'date';
				$order   = ( isset( $slider_settings['order'] ) ) ? $slider_settings['order'] : 'DESC';
				$args    = [
					'post_type'        => 'slide',
					'posts_per_page'   => -1,
					'suppress_filters' => 0,
					'orderby'          => $orderby,
					'order'            => $order,
				];

				$args['tax_query'][] = [
					'taxonomy' => 'slide-page',
					'field'    => 'slug',
					'terms'    => $term,
				];

				$query = FusionCore_Plugin::fusion_core_cached_query( $args );
				if ( $query->have_posts() ) : ?>
					<div <?php echo FusionBuilder::attributes( 'fusion-slider-wrapper' ); // phpcs:ignore WordPress.Security ?>>
						<?php
						echo '<style type="text/css">';
						echo '.fusion-slider-' . esc_attr( $term_details->term_id ) . ' .flex-direction-nav a {';
						if ( $slider_settings['nav_box_width'] ) {
							echo 'width:' . esc_attr( $slider_settings['nav_box_width'] ) . ';';
						}
						if ( $slider_settings['nav_box_height'] ) {
							echo 'height:' . esc_attr( $slider_settings['nav_box_height'] ) . ';';
							echo 'line-height:' . esc_attr( $slider_settings['nav_box_height'] ) . ';';
						}
						if ( $slider_settings['nav_arrow_size'] ) {
							echo 'font-size:' . esc_attr( $slider_settings['nav_arrow_size'] ) . ';';
						}
						echo '}';

						if ( 'pagination_circles' === $slider_settings['slider_indicator'] ) {
							$slider_indicator_color = Fusion_Color::new_color( $slider_settings['slider_indicator_color'], 'hex' );

							echo '.fusion-slider-' . esc_attr( $term_details->term_id ) . ' .flex-control-paging li a {';
								echo 'background:rgba(' . esc_attr( $slider_indicator_color->red ) . ', ' . esc_attr( $slider_indicator_color->green ) . ', ' . esc_attr( $slider_indicator_color->blue ) . ', 0.6);';
							echo '}';

							echo '.fusion-slider-' . esc_attr( $term_details->term_id ) . ' .flex-control-paging li a.flex-active {';
								echo 'background:rgba(' . esc_attr( $slider_indicator_color->red ) . ', ' . esc_attr( $slider_indicator_color->green ) . ', ' . esc_attr( $slider_indicator_color->blue ) . ', 1);';
							echo '}';
						}
						echo '</style>';
						?>
						<div class="fusion-slider-loading"><?php esc_html_e( 'Loading...', 'fusion-core' ); ?></div>
						<div <?php echo FusionBuilder::attributes( 'fusion-slider-container' ); // phpcs:ignore WordPress.Security ?>>
							<ul class="slides">
								<?php
								while ( $query->have_posts() ) :
									$query->the_post();
									$metadata = wp_parse_args(
										fusion_data()->post_meta( get_the_ID() )->get_all_meta(),
										[
											'type'         => 'image',
											'aspect_ratio' => '16:9',
											'youtube_id'   => '',
											'vimeo_id'     => '',
											'mp4'          => '',
											'webm'         => '',
											'ogv'          => '',
											'preview_image' => '',
											'video_display' => 'cover',
											'video_bg_color' => '',
											'mute_video'   => 'no',
											'autoplay_video' => 'no',
											'loop_video'   => 'no',
											'hide_video_controls' => 'yes',
											'content_alignment' => 'center',
											'heading'      => '',
											'heading_separator' => 'none',
											'heading_size' => 2,
											'heading_font_size' => '',
											'heading_color' => '',
											'heading_bg'   => 'yes',
											'heading_bg_color' => '',
											'caption'      => '',
											'caption_separator' => 'none',
											'caption_size' => 3,
											'caption_font_size' => '',
											'caption_color' => '',
											'caption_bg'   => 'yes',
											'caption_bg_color' => '',
											'link_type'    => 'button',
											'slide_link'   => '',
											'slide_target' => 'yes',
											'button_1'     => '',
											'button_2'     => '',
										]
									);

									foreach ( [ 'ogv', 'ogg', 'webm', 'mp4' ] as $filetype ) {
										if ( ! isset( $metadata[ $filetype ] ) ) {
											$metadata[ $filetype ] = '';
										}
										if ( is_array( $metadata[ $filetype ] ) ) {
											$metadata[ $filetype ] = ( isset( $metadata[ $filetype ]['url'] ) ) ? $metadata[ $filetype ]['url'] : '';
										}
									}

									if ( is_array( $metadata['preview_image'] ) ) {
										$metadata['preview_image'] = isset( $metadata['preview_image']['url'] ) ? $metadata['preview_image']['url'] : '';
									}

									$background_image = '';
									$background_class = '';

									$img_width = '';
									$image_url = [ '', '' ];

									$vimeo_consent   = class_exists( 'Avada_Privacy_Embeds' ) ? Avada()->privacy_embeds->get_consent( 'vimeo' ) : true;
									$youtube_consent = class_exists( 'Avada_Privacy_Embeds' ) ? Avada()->privacy_embeds->get_consent( 'youtube' ) : true;

									if ( isset( $metadata['type'] ) && ( 'image' === $metadata['type'] || ( 'youtube' === $metadata['type'] && ! $youtube_consent ) || ( 'vimeo' === $metadata['type'] && ! $vimeo_consent ) ) && has_post_thumbnail() ) {
										$image_id         = get_post_thumbnail_id();
										$image_url        = wp_get_attachment_image_src( $image_id, 'full', true );
										$background_image = 'background-image: url(' . $image_url[0] . ');';
										$background_class = 'background-image';
										$img_width        = $image_url[1];
									}

									$aspect_ratio       = '16:9';
									$video_attributes   = '';
									$youtube_attributes = '';
									$vimeo_attributes   = '';
									$data_mute          = 'no';
									$data_loop          = 'no';
									$data_autoplay      = 'no';

									if ( isset( $metadata['aspect_ratio'] ) && $metadata['aspect_ratio'] ) {
										$aspect_ratio = $metadata['aspect_ratio'];
									}

									if ( isset( $metadata['autoplay_video'] ) && 'yes' === $metadata['autoplay_video'] ) {
										$video_attributes   .= ' autoplay';
										$youtube_attributes .= '&amp;autoplay=1';
										$vimeo_attributes   .= '&amp;autoplay=1';
										$data_autoplay       = 'yes';
									}

									if ( isset( $metadata['mute_video'] ) && 'yes' === $metadata['mute_video'] ) {
										$video_attributes    = 'muted';
										$youtube_attributes .= '&amp;muted=1';
										$vimeo_attributes   .= '&amp;muted=1';
										$data_mute           = 'yes';
									}

									if ( isset( $metadata['loop_video'] ) && 'yes' === $metadata['loop_video'] ) {
										$video_attributes   .= ' loop';
										$youtube_attributes .= isset( $metadata['youtube_id'] ) ? '&amp;loop=1&amp;playlist=' . $metadata['youtube_id'] : '&amp;loop=1';
										$vimeo_attributes   .= '&amp;loop=1';
										$data_loop           = 'yes';
									}

									if ( isset( $metadata['hide_video_controls'] ) && 'no' === $metadata['hide_video_controls'] ) {
										$video_attributes   .= ' controls';
										$youtube_attributes .= '&amp;controls=1';
										$video_zindex        = 'z-index: 1;';
									} else {
										$youtube_attributes .= '&amp;controls=0';
										$video_zindex        = 'z-index: -99;';
									}

									$heading_color = ! empty( $metadata['heading_color'] ) ? $metadata['heading_color'] : '';

									$heading_bg = '';

									if ( isset( $metadata['heading_bg'] ) && 'yes' === $metadata['heading_bg'] ) {
										$heading_bg = 'background-color: rgba(0,0,0, 0.4);';
										if ( isset( $metadata['heading_bg_color'] ) && '' !== $metadata['heading_bg_color'] && class_exists( 'Fusion_Color' ) ) {
											$heading_bg_color_object = Fusion_Color::new_color( $metadata['heading_bg_color'] );

											if ( 1 === intval( $heading_bg_color_object->alpha ) ) {
												$heading_bg_color_object = $heading_bg_color_object->get_new( 'alpha', 0.4 );
											}

											$heading_bg = 'background-color:' . $heading_bg_color_object->to_css_var_or_rgba();
										}
									}

									$caption_color = ! empty( $metadata['caption_color'] ) ? $metadata['caption_color'] : '';

									$caption_bg = '';

									if ( isset( $metadata['caption_bg'] ) && 'yes' === $metadata['caption_bg'] ) {
										$caption_bg = 'background-color: rgba(0, 0, 0, 0.4);';

										if ( isset( $metadata['caption_bg_color'] ) && '' !== $metadata['caption_bg_color'] && class_exists( 'Fusion_Color' ) ) {
											$caption_bg_color_object = Fusion_Color::new_color( $metadata['caption_bg_color'] );

											if ( 1 === intval( $caption_bg_color_object->alpha ) ) {
												$caption_bg_color_object = $caption_bg_color_object->get_new( 'alpha', 0.4 );
											}

											$caption_bg = 'background-color:' . $caption_bg_color_object->to_css_var_or_rgba();
										}
									}

									$video_bg_color = '';

									if ( isset( $metadata['video_bg_color'] ) && $metadata['video_bg_color'] && class_exists( 'Fusion_Color' ) ) {
										$video_bg_color_object = Fusion_Color::new_color( $metadata['video_bg_color'] );

										if ( 1 === intval( $video_bg_color_object->alpha ) ) {
											$video_bg_color_object = $video_bg_color_object->get_new( 'alpha', 0.4 );
										}

										$video_bg_color = 'background-color:' . $video_bg_color_object->to_css_var_or_rgba();
									}

									$video = false;

									if ( isset( $metadata['type'] ) ) {
										if ( isset( $metadata['type'] ) && in_array( $metadata['type'], [ 'self-hosted-video', 'youtube', 'vimeo' ], true ) ) {
											$video = true;
										}
									}

									if ( isset( $metadata['type'] ) && 'self-hosted-video' === $metadata['type'] ) {
										$background_class = 'self-hosted-video-bg';
									}

									$heading_size = 2;
									if ( isset( $metadata['heading_size'] ) && $metadata['heading_size'] ) {
										$heading_size = $metadata['heading_size'];
									}

									$heading_font_size   = '60px';
									$heading_line_height = '80px';
									if ( isset( $metadata['heading_font_size'] ) && $metadata['heading_font_size'] ) {
										$heading_font_size   = str_replace( 'px', '', $metadata['heading_font_size'] ) . 'px';
										$heading_line_height = (float) $heading_font_size * 1.2 . 'px';
									}

									$caption_size = 3;
									if ( isset( $metadata['caption_size'] ) && $metadata['caption_size'] ) {
										$caption_size = $metadata['caption_size'];
									}

									$caption_font_size   = '24px';
									$caption_line_height = '38px';
									if ( isset( $metadata['caption_font_size'] ) && $metadata['caption_font_size'] ) {
										$caption_font_size   = str_replace( 'px', '', $metadata['caption_font_size'] ) . 'px';
										$caption_line_height = (float) $caption_font_size * 1.2 . 'px';
									}

									$heading_title_sc_wrapper_class = '';
									$caption_title_sc_wrapper_class = '';

									if ( ! isset( $metadata['heading_separator'] ) ) {
										$metadata['heading_separator'] = 'none';
									}

									if ( ! isset( $metadata['caption_separator'] ) ) {
										$metadata['caption_separator'] = 'none';
									}

									if ( 'center' !== $metadata['content_alignment'] ) {
										$metadata['heading_separator'] = 'none';
										$metadata['caption_separator'] = 'none';
									}

									if ( 'center' === $metadata['content_alignment'] ) {
										if ( 'none' !== $metadata['heading_separator'] ) {
											$heading_title_sc_wrapper_class = ' fusion-block-element';
										}

										if ( 'none' !== $metadata['caption_separator'] ) {
											$caption_title_sc_wrapper_class = ' fusion-block-element';
										}
									}
									?>
									<li data-mute="<?php echo esc_attr( $data_mute ); ?>" data-loop="<?php echo esc_attr( $data_loop ); ?>" data-autoplay="<?php echo esc_attr( (string) $data_autoplay ); ?>">
										<div class="slide-content-container slide-content-<?php echo ( isset( $metadata['content_alignment'] ) && $metadata['content_alignment'] ) ? esc_attr( $metadata['content_alignment'] ) : ''; ?>" style="display: none;">
											<div class="slide-content" style="<?php echo esc_attr( $content_max_width ); ?>">
												<?php if ( isset( $metadata['heading'] ) && $metadata['heading'] ) : ?>
													<div class="heading <?php echo ( $heading_bg ) ? 'with-bg' : ''; ?>">
														<div class="fusion-title-sc-wrapper<?php echo esc_attr( $heading_title_sc_wrapper_class ); ?>" style="<?php echo esc_attr( $heading_bg ); ?>">
															<?php echo do_shortcode( '[fusion_title size="' . $heading_size . '" font_size="' . $heading_font_size . '" line_height="' . $heading_line_height . '" text_color="' . $heading_color . '" content_align="' . $metadata['content_alignment'] . '" sep_color="' . $heading_color . '" margin_top="0px" margin_bottom="0px" style_type="' . $metadata['heading_separator'] . '"]' . do_shortcode( $metadata['heading'] ) . '[/fusion_title]' ); ?>
														</div>
													</div>
												<?php endif; ?>
												<?php if ( isset( $metadata['caption'] ) && $metadata['caption'] ) : ?>
													<div class="caption <?php echo ( $caption_bg ) ? 'with-bg' : ''; ?>">
														<div class="fusion-title-sc-wrapper<?php echo esc_attr( $caption_title_sc_wrapper_class ); ?>" style="<?php echo esc_attr( $caption_bg ); ?>">
															<?php echo do_shortcode( '[fusion_title size="' . $caption_size . '" font_size="' . $caption_font_size . '" line_height="' . $caption_line_height . '" text_color="' . $caption_color . '" content_align="' . $metadata['content_alignment'] . '" sep_color="' . $caption_color . '" margin_top="0px" margin_bottom="0px" style_type="' . $metadata['caption_separator'] . '"]' . do_shortcode( $metadata['caption'] ) . '[/fusion_title]' ); ?>
														</div>
													</div>
												<?php endif; ?>
												<?php if ( isset( $metadata['link_type'] ) && 'button' === $metadata['link_type'] ) : ?>
													<div class="buttons" >
														<?php if ( isset( $metadata['button_1'] ) && $metadata['button_1'] ) : ?>
															<div class="tfs-button-1"><?php echo do_shortcode( $metadata['button_1'] ); ?></div>
														<?php endif; ?>
														<?php if ( isset( $metadata['button_2'] ) && $metadata['button_2'] ) : ?>
															<div class="tfs-button-2"><?php echo do_shortcode( $metadata['button_2'] ); ?></div>
														<?php endif; ?>
													</div>
												<?php endif; ?>
											</div>
										</div>
										<?php if ( isset( $metadata['link_type'] ) && 'full' === $metadata['link_type'] && isset( $metadata['slide_link'] ) && $metadata['slide_link'] ) : ?>
											<a href="<?php echo esc_url_raw( $metadata['slide_link'] ); ?>" class="overlay-link<?php echo ( isset( $metadata['slide_target'] ) && 'yes' === $metadata['slide_target'] ) ? '" target="_blank" rel="noopener noreferrer"' : ' fusion-one-page-text-link"'; ?>></a>
										<?php endif; ?>
										<?php if ( isset( $metadata['preview_image'] ) && $metadata['preview_image'] && isset( $metadata['type'] ) && 'self-hosted-video' === $metadata['type'] ) : ?>
											<?php $mobile_video_image = ( class_exists( 'Fusion_Sanitize' ) ) ? $fusion_library->sanitize->css_asset_url( $metadata['preview_image'] ) : ''; ?>
											<div class="mobile_video_image" style="background-image: url(<?php echo $mobile_video_image; // phpcs:ignore WordPress.Security ?>);"></div>
										<?php elseif ( isset( $metadata['type'] ) && 'self-hosted-video' === $metadata['type'] ) : ?>
											<?php $mobile_video_image = ( class_exists( 'Fusion_Sanitize' ) ) ? $fusion_library->sanitize->css_asset_url( FUSION_CORE_URL . '/images/video_preview.jpg' ) : FUSION_CORE_URL . '/images/video_preview.jpg'; ?>
											<div class="mobile_video_image" style="background-image: url(<?php echo $mobile_video_image; // phpcs:ignore WordPress.Security ?>);"></div>
										<?php endif; ?>
										<?php if ( $video_bg_color && true === $video ) : ?>
											<div class="overlay" style="<?php echo esc_attr( $video_bg_color ); ?>"></div>
										<?php endif; ?>
										<div class="background <?php echo esc_attr( $background_class ); ?>" style="<?php echo esc_attr( $background_image ); ?>max-width:<?php echo esc_attr( $slider_settings['slider_width'] ); ?>;height:<?php echo esc_attr( $slider_settings['slider_height'] ); ?>;filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php echo esc_url_raw( $image_url[0] ); ?>', sizingMethod='scale');" data-imgwidth="<?php echo esc_attr( $img_width ); ?>">
											<?php if ( isset( $metadata['type'] ) ) : ?>
												<?php if ( 'self-hosted-video' === $metadata['type'] && ( isset( $metadata['webm'] ) || isset( $metadata['mp4'] ) || isset( $metadata['ogg'] ) ) ) : ?>
												<video width="1800" height="700" <?php echo $video_attributes; // phpcs:ignore WordPress.Security ?> preload="auto">
														<?php if ( isset( $metadata['mp4'] ) ) : ?>
															<source src="<?php echo esc_url_raw( $metadata['mp4'] ); ?>" type="video/mp4">
														<?php endif; ?>
														<?php if ( isset( $metadata['ogg'] ) ) : ?>
															<source src="<?php echo esc_url_raw( $metadata['ogg'] ); ?>" type="video/ogg">
														<?php endif; ?>
														<?php if ( isset( $metadata['webm'] ) ) : ?>
															<source src="<?php echo esc_url_raw( $metadata['webm'] ); ?>" type="video/webm">
														<?php endif; ?>
													</video>
												<?php endif; ?>
											<?php endif; ?>
											<?php if ( isset( $metadata['type'] ) && isset( $metadata['youtube_id'] ) && 'youtube' === $metadata['type'] && $metadata['youtube_id'] ) : ?>
												<div style="position: absolute; top: 0; left: 0; <?php echo esc_attr( $video_zindex ); ?> width: 100%; height: 100%" data-youtube-video-id="<?php echo esc_attr( $metadata['youtube_id'] ); ?>" data-video-aspect-ratio="<?php echo esc_attr( $aspect_ratio ); ?>">
													<div id="video-<?php echo esc_attr( $metadata['youtube_id'] ); ?>-inner">
														<iframe height="100%" width="100%" src="https://www.youtube.com/embed/<?php echo esc_attr( $metadata['youtube_id'] ); ?>?wmode=transparent&amp;modestbranding=1&amp;showinfo=0&amp;autohide=1&amp;enablejsapi=1&amp;rel=0&amp;vq=hd720&amp;<?php echo esc_attr( $youtube_attributes ); ?>" data-fusion-no-placeholder></iframe>
													</div>
												</div>
											<?php endif; ?>
											<?php if ( isset( $metadata['type'] ) && isset( $metadata['vimeo_id'] ) && 'vimeo' === $metadata['type'] && $metadata['vimeo_id'] ) : ?>
												<div style="position: absolute; top: 0; left: 0; <?php echo esc_attr( $video_zindex ); ?> width: 100%; height: 100%" data-mute="<?php echo esc_attr( $data_mute ); ?>" data-vimeo-video-id="<?php echo esc_attr( $metadata['vimeo_id'] ); ?>" data-video-aspect-ratio="<?php echo esc_attr( $aspect_ratio ); ?>">
													<iframe src="https://player.vimeo.com/video/<?php echo esc_attr( $metadata['vimeo_id'] ); ?>?title=0&amp;byline=0&amp;portrait=0&amp;color=ffffff&amp;badge=0&amp;title=0<?php echo esc_attr( $vimeo_attributes ); ?>" height="100%" width="100%" webkitallowfullscreen mozallowfullscreen allowfullscreen data-fusion-no-placeholder></iframe>
												</div>
											<?php endif; ?>
										</div>
									</li>
								<?php endwhile; ?>
								<?php wp_reset_postdata(); ?>
							</ul>
						</div>
					</div>
				<?php endif; ?>
				<?php

				$html = ob_get_clean();

				do_action( 'fusion_slider_enqueue' );
				return $html;

			}

			/**
			 * Builds the wrapper attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function wrapper_attr() {

				$attr = fusion_builder_visibility_atts(
					self::$parent_args['hide_on_mobile'],
					[
						'class' => 'fusion-slider-container fusion-slider-sc-' . self::$parent_args['name'],
						'style' => '',
					]
				);

				$term_details = get_term_by( 'slug', self::$parent_args['name'], 'slide-page' );

				$attr['class'] .= ' fusion-slider-' . $term_details->term_id;

				$attr['data-id'] = $term_details->term_id;

				$attr['data-full_height'] = isset( self::$parent_args['full_height'] ) ? self::$parent_args['full_height'] : 'no';
				$attr['data-full_height'] = 'yes' === $attr['data-full_height'];

				$attr['data-offset'] = isset( self::$parent_args['offset'] ) ? self::$parent_args['offset'] : '';

				if ( '100%' === self::$slider_settings['slider_width'] && ! self::$slider_settings['full_screen'] ) {
					$attr['class'] .= ' full-width-slider';
				}

				if ( '100%' !== self::$slider_settings['slider_width'] && ! self::$slider_settings['full_screen'] ) {
					$attr['class'] .= ' fixed-width-slider';
				}

				if ( self::$parent_args['class'] ) {
					$attr['class'] .= ' ' . self::$parent_args['class'];
				}

				$attr['style'] .= Fusion_Builder_Margin_Helper::get_margins_style( self::$parent_args );

				if ( self::$parent_args['id'] ) {
					$attr['id'] = self::$parent_args['id'];
				}

				$attr['style'] .= 'height:' . self::$slider_settings['slider_height'] . '; max-width:' . self::$slider_settings['slider_width'] . ';';

				return $attr;

			}

			/**
			 * Builds the container attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function container_attr() {

				$attr = [
					'class' => 'tfs-slider flexslider main-flex',
				];

				if ( self::$slider_settings ) {
					foreach ( self::$slider_settings as $slider_setting => $slider_setting_value ) {
						$attr[ 'data-' . $slider_setting ] = $slider_setting_value;
					}
				}

				if ( '100%' === self::$slider_settings['slider_width'] && ! self::$slider_settings['full_screen'] ) {
					$attr['class'] .= ' full-width-slider';
				}

				if ( '100%' !== self::$slider_settings['slider_width'] && ! self::$slider_settings['full_screen'] ) {
					$attr['class'] .= ' fixed-width-slider';
				}

				$attr['style'] = 'max-width:' . self::$slider_settings['slider_width'] . ';';

				return $attr;

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
			 * @param array $defaults An array of defaults.
			 * @return void
			 */
			public function query( $defaults ) {
				$return_data = [];
				$sliders     = FusionCore_Plugin::get_fusion_sliders();

				check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );

				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$offset = isset( $_POST['model']['params']['offset'] ) ? wp_unslash( $_POST['model']['params']['offset'] ) : '';
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$full_height = isset( $_POST['model']['params']['full_height'] ) ? wp_unslash( $_POST['model']['params']['full_height'] ) : '';

				if ( ! empty( $sliders ) ) {
					foreach ( $sliders as $slider_id => $slider_name ) {
						$slider_content                       = do_shortcode( '[fusion_fusionslider name="' . $slider_id . '" offset="' . $offset . '" full_height="' . $full_height . '"  ]' );
						$return_data['sliders'][ $slider_id ] = [
							'content' => $slider_content,
						];
					}
				} else {
					$return_data['placeholder'] = fusion_builder_placeholder( 'avada_fusionslider', 'Avada Sliders' );
				}

				echo wp_json_encode( $return_data );
				die();
			}
		}
	}

	new FusionSC_FusionSlider();
}

/**
 * Map shortcode to Avada Builder
 *
 * @since 1.0
 */
function fusion_element_fusionslider() {
	if ( ! class_exists( 'Fusion_Slider' ) || ! function_exists( 'fusion_builder_map' ) || ! function_exists( 'fusion_builder_frontend_data' ) ) {
		return;
	}

	$slider_options = FusionCore_Plugin::get_fusion_sliders();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_FusionSlider',
			[
				'name'       => esc_attr__( 'Avada Slider', 'fusion-core' ),
				'shortcode'  => 'fusion_fusionslider',
				'icon'       => 'fusiona-avada-logo',
				'preview'    => FUSION_CORE_PATH . '/shortcodes/previews/fusion-fusion-slider-preview.php',
				'preview_id' => 'fusion-builder-block-module-fusion-slider-preview-template',
				'front-end'  => FUSION_CORE_PATH . '/shortcodes/previews/front-end/fusion-fusionslider.php',
				'help_url'   => 'https://theme-fusion.com/documentation/avada/elements/avada-slider-element/',
				'params'     => [
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Slider Name', 'fusion-core' ),
						'description' => esc_attr__( 'Select the slider you want to use. The options will appear as the slider name next to the number of slides in brackets.', 'fusion-core' ),
						'param_name'  => 'name',
						'value'       => $slider_options,
						'default'     => function_exists( 'fusion_get_array_default' ) ? fusion_get_array_default( $slider_options ) : '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_html__( 'Full Height', 'fusion-core' ),
						'description' => esc_html__( 'Makes slider full screen height.', 'fusion-core' ),
						'param_name'  => 'full_height',
						'default'     => 'no',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-core' ),
							'no'  => esc_attr__( 'No', 'fusion-core' ),
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_fusionslider',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Full Height Offset', 'fusion-core' ),
						'description' => esc_html__( 'The slider offset. Accepts both pixels and CSS selectors.', 'fusion-core' ),
						'param_name'  => 'offset',
						'default'     => '',
						'dependency'  => [
							[
								'element'  => 'full_height',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_fusionslider',
							'ajax'     => true,
						],
					],
					'fusion_margin_placeholder' => [
						'param_name' => 'margin',
						'group'      => esc_attr__( 'General', 'fusion-core' ),
						'value'      => [
							'margin_top'    => '',
							'margin_bottom' => '',
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
						'param_name'  => 'class',
						'value'       => '',
						'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-core' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS ID', 'fusion-core' ),
						'param_name'  => 'id',
						'value'       => '',
						'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-core' ),
					],
				],
				'callback'   => [
					'function' => 'fusion_ajax',
					'action'   => 'get_fusion_fusionslider',
					'ajax'     => true,
				],
			]
		)
	);
}

// Priority 20 to make sure its loaded after setup_fusion_slider.
add_action( 'wp_loaded', 'fusion_element_fusionslider', 20 );
