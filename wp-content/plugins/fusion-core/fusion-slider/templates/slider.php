<?php
/**
 * Slide template.
 *
 * @package Fusion-Slider
 * @subpackage Templates
 * @since 1.0.0
 */

?>
<?php $max_width = ( 'fade' === $slider_settings['animation'] ) ? 'max-width:' . $slider_settings['slider_width'] : ''; ?>
<?php $container_class = ( $slider_class ) ? $slider_class . '-container' : ''; ?>
<div id="fusion-slider-<?php echo esc_attr( $slider_settings['slider_id'] ); ?>" data-id="<?php echo esc_attr( $slider_settings['slider_id'] ); ?>" class="fusion-slider-container fusion-slider-<?php the_ID(); ?><?php echo esc_attr( $container_class ); ?>" style="height:<?php echo esc_attr( $slider_settings['slider_height'] ); ?>;max-width:<?php echo esc_attr( $slider_settings['slider_width'] ); ?>;">
	<style type="text/css">
		<?php
		echo '#fusion-slider-' . esc_attr( $slider_settings['slider_id'] ) . ' .flex-direction-nav a {';
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

			if ( '' === $slider_settings['slider_indicator_color'] ) {
				$slider_settings['slider_indicator_color'] = '#ffffff';
			}

			$slider_indicator_color = Fusion_Color::new_color( $slider_settings['slider_indicator_color'], 'hex' );

			echo '#fusion-slider-' . esc_attr( $term_details->term_id ) . ' .flex-control-paging li a {';
			echo 'background: rgba(' . esc_attr( $slider_indicator_color->red ) . ', ' . esc_attr( $slider_indicator_color->green ) . ', ' . esc_attr( $slider_indicator_color->blue ) . ', 0.6);';
			echo '}';

			echo '#fusion-slider-' . esc_attr( $term_details->term_id ) . ' .flex-control-paging li a.flex-active {';
			echo 'background: rgba(' . esc_attr( $slider_indicator_color->red ) . ', ' . esc_attr( $slider_indicator_color->green ) . ', ' . esc_attr( $slider_indicator_color->blue ) . ', 1);';
			echo '}';
		}
		?>
	</style>
	<div class="fusion-slider-loading"><?php esc_html_e( 'Loading...', 'fusion-core' ); ?></div>
	<?php
	$typo_sensitivity = ( ! isset( $slider_settings['typo_sensitivity'] ) || empty( $slider_settings['typo_sensitivity'] ) ) ? '' : '--typography_sensitivity:' . $slider_settings['typo_sensitivity'] . ';';
	$typo_factor      = ( ! isset( $slider_settings['typo_factor'] ) || empty( $slider_settings['typo_factor'] ) ) ? '' : '--typography_factor:' . $slider_settings['typo_factor'] . ';';
	?>
	<div class="tfs-slider flexslider main-flex<?php echo esc_attr( $slider_class ); ?>" style="max-width:<?php echo esc_attr( $slider_settings['slider_width'] ); ?>;<?php echo esc_attr( $typo_sensitivity ); ?>" <?php echo $slider_data; // phpcs:ignore WordPress.Security ?>>
		<ul class="slides" style="<?php echo esc_attr( $max_width ); ?>;">
			<?php while ( $query->have_posts() ) : ?>
				<?php $query->the_post(); ?>
				<?php
				$metadata = wp_parse_args(
					fusion_data()->post_meta( get_the_ID() )->get_all_meta(),
					[
						'type'                => 'image',
						'aspect_ratio'        => '16:9',
						'youtube_id'          => '',
						'vimeo_id'            => '',
						'mp4'                 => '',
						'webm'                => '',
						'ogv'                 => '',
						'preview_image'       => '',
						'video_display'       => 'cover',
						'video_bg_color'      => '',
						'mute_video'          => 'no',
						'autoplay_video'      => 'no',
						'loop_video'          => 'no',
						'hide_video_controls' => 'yes',
						'content_alignment'   => 'center',
						'heading'             => '',
						'heading_separator'   => 'none',
						'heading_size'        => 2,
						'heading_font_size'   => '',
						'heading_color'       => '',
						'heading_bg'          => 'yes',
						'heading_bg_color'    => '',
						'caption'             => '',
						'caption_separator'   => 'none',
						'caption_size'        => 3,
						'caption_font_size'   => '',
						'caption_color'       => '',
						'caption_bg'          => 'yes',
						'caption_bg_color'    => '',
						'link_type'           => 'button',
						'slide_link'          => '',
						'slide_target'        => 'yes',
						'button_1'            => '',
						'button_2'            => '',
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

				if ( ( 'image' === $metadata['type'] || ( 'youtube' === $metadata['type'] && ! $youtube_consent ) || ( 'vimeo' === $metadata['type'] && ! $vimeo_consent ) ) && has_post_thumbnail() ) {
					$image_id         = get_post_thumbnail_id();
					$image_url        = wp_get_attachment_image_src( $image_id, 'full', true );
					$background_image = 'background-image: url(' . $image_url[0] . ');';
					$background_class = 'background-image';
					$img_width        = $image_url[1];
				}

				$video_attributes   = '';
				$youtube_attributes = '';
				$vimeo_attributes   = '';

				if ( 'yes' === $metadata['autoplay_video'] ) {
					$video_attributes   .= ' autoplay';
					$youtube_attributes .= '&amp;autoplay=1';
					$vimeo_attributes   .= '&amp;autoplay=1';
				}

				if ( 'yes' === $metadata['mute_video'] ) {
					$video_attributes    = 'muted';
					$youtube_attributes .= '&amp;muted=1';
					$vimeo_attributes   .= '&amp;muted=1';
				}

				if ( 'yes' === $metadata['loop_video'] ) {
					$video_attributes   .= ' loop';
					$youtube_attributes .= '&amp;loop=1&amp;playlist=' . $metadata['youtube_id'];
					$vimeo_attributes   .= '&amp;loop=1';
				}

				if ( 'no' === $metadata['hide_video_controls'] ) {
					$video_attributes   .= ' controls';
					$youtube_attributes .= '&amp;controls=1';
					$video_zindex        = 'z-index:1;';
				} else {
					$youtube_attributes .= '&amp;controls=0';
					$video_zindex        = 'z-index:-99;';
				}

				$heading_color = ! empty( $metadata['heading_color'] ) ? $metadata['heading_color'] : '';

				$heading_bg = '';
				if ( 'yes' === $metadata['heading_bg'] ) {
					$heading_bg = 'background-color: rgba(0,0,0,0.4);';
					if ( $metadata['heading_bg_color'] && class_exists( 'Fusion_Color' ) ) {
						$heading_bg_color_object = Fusion_Color::new_color( $metadata['heading_bg_color'] );

						if ( 1 === intval( $heading_bg_color_object->alpha ) ) {
							$heading_bg_color_object = $heading_bg_color_object->get_new( 'alpha', 0.4 );
						}

						$heading_bg = 'background-color:' . $heading_bg_color_object->toCSS( 'rgba' );
					}
				}

				$caption_color = ! empty( $metadata['caption_color'] ) ? $metadata['caption_color'] : '';

				$caption_bg = '';
				if ( 'yes' === $metadata['caption_bg'] ) {
					$caption_bg = 'background-color:rgba(0,0,0,0.4);';
					if ( $metadata['caption_bg_color'] && class_exists( 'Fusion_Color' ) ) {
						$caption_bg_color_object = Fusion_Color::new_color( $metadata['caption_bg_color'] );

						if ( 1 === intval( $caption_bg_color_object->alpha ) ) {
							$caption_bg_color_object = $caption_bg_color_object->get_new( 'alpha', 0.4 );
						}

						$caption_bg = 'background-color:' . $caption_bg_color_object->toCSS( 'rgba' );
					}
				}

				if ( $metadata['video_bg_color'] && class_exists( 'Fusion_Color' ) ) {
					$video_bg_color_object = Fusion_Color::new_color( $metadata['video_bg_color'] );

					if ( 1 === intval( $video_bg_color_object->alpha ) ) {
						$video_bg_color_object = $video_bg_color_object->get_new( 'alpha', 0.4 );
					}

					$metadata['video_bg_color'] = 'background-color:' . $video_bg_color_object->toCSS( 'rgba' );
				}

				$video = false;

				if ( in_array( $metadata['type'], [ 'self-hosted-video', 'youtube', 'vimeo' ], true ) ) {
					$video = true;
				}

				if ( 'self-hosted-video' === $metadata['type'] ) {
					$background_class = 'self-hosted-video-bg';
				}

				$heading_font_size   = '60px';
				$heading_line_height = '80px';
				if ( isset( $metadata['heading_font_size'] ) && $metadata['heading_font_size'] ) {
					$heading_font_size   = str_replace( 'px', '', $metadata['heading_font_size'] ) . 'px';
					$heading_line_height = (float) $heading_font_size * 1.2 . 'px';
				}

				$caption_font_size   = '24px';
				$caption_line_height = '38px';
				if ( isset( $metadata['caption_font_size'] ) && $metadata['caption_font_size'] ) {
					$caption_font_size   = str_replace( 'px', '', $metadata['caption_font_size'] ) . 'px';
					$caption_line_height = (float) $caption_font_size * 1.2 . 'px';
				}


				$heading_title_sc_wrapper_class = '';
				$caption_title_sc_wrapper_class = '';

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

				if ( ! isset( $metadata['slider_indicator_color'][0] ) ) {
					$metadata['slider_indicator_color'][0] = '#ffffff';
				}
				?>
				<li class="slide-id-<?php the_ID(); ?>" data-mute="<?php echo esc_attr( $metadata['mute_video'] ); ?>" data-loop="<?php echo esc_attr( $metadata['loop_video'] ); ?>" data-autoplay="<?php echo esc_attr( $metadata['autoplay_video'] ); ?>">
					<div class="slide-content-container slide-content-<?php echo esc_attr( $metadata['content_alignment'] ); ?>" style="display: none;">
						<div class="slide-content" style="<?php echo esc_html( $content_max_width ); ?>">
							<?php if ( $metadata['heading'] ) : ?>
								<div class="heading <?php echo ( $heading_bg ) ? 'with-bg' : ''; ?>">
									<div class="fusion-title-sc-wrapper<?php echo esc_attr( $heading_title_sc_wrapper_class ); ?>" style="<?php echo esc_attr( $heading_bg ); ?>">
										<?php echo do_shortcode( '[fusion_title size="' . $metadata['heading_size'] . '" font_size="' . $heading_font_size . '" line_height="' . $heading_line_height . '" text_color="' . $heading_color . '" content_align="' . $metadata['content_alignment'] . '" sep_color="' . $heading_color . '" margin_top="0px" margin_bottom="0px" style_type="' . $metadata['heading_separator'] . '"]' . do_shortcode( $metadata['heading'] ) . '[/fusion_title]' ); ?>
									</div>
								</div>
							<?php endif; ?>
							<?php if ( $metadata['caption'] ) : ?>
								<div class="caption <?php echo ( $caption_bg ) ? 'with-bg' : ''; ?>">
									<div class="fusion-title-sc-wrapper<?php echo esc_attr( $caption_title_sc_wrapper_class ); ?>" style="<?php echo esc_attr( $caption_bg ); ?>">
										<?php echo do_shortcode( '[fusion_title size="' . $metadata['caption_size'] . '" font_size="' . $caption_font_size . '" line_height="' . $caption_line_height . '" text_color="' . $caption_color . '" content_align="' . $metadata['content_alignment'] . '" sep_color="' . $caption_color . '" margin_top="0px" margin_bottom="0px" style_type="' . $metadata['caption_separator'] . '"]' . do_shortcode( $metadata['caption'] ) . '[/fusion_title]' ); ?>
									</div>
								</div>
							<?php endif; ?>
							<?php if ( 'button' === $metadata['link_type'] ) : ?>
								<div class="buttons" >
									<?php if ( $metadata['button_1'] ) : ?>
										<div class="tfs-button-1"><?php echo do_shortcode( $metadata['button_1'] ); ?></div>
									<?php endif; ?>
									<?php if ( $metadata['button_2'] ) : ?>
										<div class="tfs-button-2"><?php echo do_shortcode( $metadata['button_2'] ); ?></div>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						</div>
					</div>
					<?php if ( isset( $slider_settings['slider_indicator'] ) && 'scroll_down_indicator' === $slider_settings['slider_indicator'] ) : ?>
						<a class="tfs-scroll-down-indicator fusion-one-page-text-link" href="#main" style="opacity:0;color:<?php echo esc_attr( $slider_settings['slider_indicator_color'] ); ?>;"></a>
					<?php endif; ?>
					<?php if ( 'full' === $metadata['link_type'] && $metadata['slide_link'] ) : ?>
						<a href="<?php echo esc_url_raw( $metadata['slide_link'] ); ?>" class="overlay-link<?php echo ( 'yes' === $metadata['slide_target'] ) ? '" target="_blank" rel="noopener noreferrer"' : ' fusion-one-page-text-link"'; ?> aria-label="<?php the_title_attribute(); ?>"></a>
					<?php endif; ?>
					<?php if ( $metadata['preview_image'] && 'self-hosted-video' === $metadata['type'] ) : ?>
						<div class="mobile_video_image" style="background-image: url('<?php echo esc_url_raw( Fusion_Sanitize::css_asset_url( $metadata['preview_image'] ) ); ?>');"></div>
					<?php elseif ( 'self-hosted-video' === $metadata['type'] ) : ?>
						<div class="mobile_video_image" style="background-image: url('<?php echo esc_url_raw( Fusion_Sanitize::css_asset_url( FUSION_CORE_URL . '/images/video_preview.jpg' ) ); ?>');"></div>
					<?php endif; ?>
					<?php if ( $metadata['video_bg_color'] && ( true === $video || 1 === $video || '1' === $video || 'true' === $video ) ) : ?>
						<div class="overlay" style="<?php echo esc_html( $metadata['video_bg_color'] ); ?>"></div>
					<?php endif; ?>
					<div class="background <?php echo esc_attr( $background_class ); ?>" style="<?php echo esc_html( $background_image ); ?>max-width:<?php echo esc_attr( $slider_settings['slider_width'] ); ?>;height:<?php echo esc_attr( $slider_settings['slider_height'] ); ?>;filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php echo esc_url_raw( $image_url[0] ); ?>', sizingMethod='scale');" data-imgwidth="<?php echo esc_attr( $img_width ); ?>">
						<?php if ( 'self-hosted-video' === $metadata['type'] && ( ( isset( $metadata['webm'] ) && $metadata['webm'] ) || ( isset( $metadata['mp4'] ) && $metadata['mp4'] ) || ( isset( $metadata['ogg'] ) && $metadata['ogg'] ) ) ) : ?>
							<span
								class="fusion-slider-self-hosted-video-placeholder"
								data-ogg="<?php echo ( isset( $metadata['ogg'] ) ) ? esc_url_raw( $metadata['ogg'] ) : ''; ?>"
								data-webm="<?php echo ( isset( $metadata['webm'] ) ) ? esc_url_raw( $metadata['webm'] ) : ''; ?>"
								data-mp4="<?php echo ( isset( $metadata['mp4'] ) ) ? esc_url_raw( $metadata['mp4'] ) : ''; ?>"
								<?php echo $video_attributes; // phpcs:ignore WordPress.Security ?>
								preload="auto"
							></span>
						<?php endif; ?>
						<?php if ( 'youtube' === $metadata['type'] && $metadata['youtube_id'] ) : ?>
							<div style="position: absolute; top: 0; left: 0; <?php echo esc_attr( $video_zindex ); ?> width: 100%; height: 100%" data-youtube-video-id="<?php echo esc_attr( $metadata['youtube_id'] ); ?>" data-video-aspect-ratio="<?php echo esc_attr( $metadata['aspect_ratio'] ); ?>" data-display="<?php echo esc_attr( $metadata['video_display'] ); ?>">
								<div id="video-<?php echo esc_attr( $metadata['youtube_id'] ); ?>-inner">
									<?php echo apply_filters( 'privacy_iframe_embed', '<iframe height="100%" width="100%" src="https://www.youtube.com/embed/' . esc_attr( $metadata['youtube_id'] ) . '?wmode=transparent&amp;modestbranding=1&amp;showinfo=0&amp;autohide=1&amp;enablejsapi=1&amp;rel=0&amp;vq=hd720&amp;' . esc_attr( $youtube_attributes ) . '" data-fusion-no-placeholder allowfullscreen allow="autoplay; fullscreen"></iframe>' ); // phpcs:ignore WordPress.Security ?>
								</div>
							</div>
						<?php endif; ?>
						<?php if ( 'vimeo' === $metadata['type'] && $metadata['vimeo_id'] ) : ?>
							<div style="position: absolute; top: 0; left: 0; <?php echo esc_attr( $video_zindex ); ?> width: 100%; height: 100%" data-mute="<?php echo esc_attr( $metadata['mute_video'] ); ?>" data-vimeo-video-id="<?php echo esc_attr( $metadata['vimeo_id'] ); ?>" data-video-aspect-ratio="<?php echo esc_attr( $metadata['aspect_ratio'] ); ?>" data-display="<?php echo esc_attr( $metadata['video_display'] ); ?>">
								<?php echo apply_filters( 'privacy_iframe_embed', '<iframe src="https://player.vimeo.com/video/' . esc_attr( $metadata['vimeo_id'] ) . '?title=0&amp;byline=0&amp;portrait=0&amp;color=ffffff&amp;badge=0&amp;autopause=0' . esc_attr( $vimeo_attributes ) . '" height="100%" width="100%" data-fusion-no-placeholder allowfullscreen allow="autoplay; fullscreen"></iframe>' ); // phpcs:ignore WordPress.Security ?>
							</div>
						<?php endif; ?>
					</div>
				</li>
			<?php endwhile; ?>
		</ul>
	</div>
</div>
