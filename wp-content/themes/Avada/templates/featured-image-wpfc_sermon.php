<?php
/**
 * Featured images for wpfc_sermon posts.
 *
 * @package Avada
 * @subpackage Templates
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

if ( ! post_password_required( get_the_ID() ) ) :
	global $post;
	?>
	<?php if ( Avada()->settings->get( 'featured_images_single' ) ) : ?>
		<?php $video = apply_filters( 'privacy_iframe_embed', fusion_get_page_option( 'video', get_the_ID() ) ); ?>
		<?php if ( 0 < avada_number_of_featured_images() || $video ) : ?>
			<div class="fusion-flexslider flexslider fusion-flexslider-loading fusion-post-slideshow post-slideshow">
				<ul class="slides">
					<?php if ( $video ) : ?>
						<li>
							<div class="full-video">
								<?php echo $video; // phpcs:ignore WordPress.Security.EscapeOutput ?>
							</div>
						</li>
					<?php endif; ?>
					<?php if ( has_post_thumbnail() && fusion_get_option( 'show_first_featured_image' ) ) : ?>
						<?php $attachment_data = Avada()->images->get_attachment_data( get_post_thumbnail_id() ); ?>
						<?php if ( is_array( $attachment_data ) ) : ?>
							<li>
								<?php if ( Avada()->settings->get( 'status_lightbox' ) && Avada()->settings->get( 'status_lightbox_single' ) ) : ?>
									<a href="<?php echo esc_url_raw( $attachment_data['url'] ); ?>" rel="prettyPhoto[gallery<?php the_ID(); ?>]" title="<?php echo esc_attr( $attachment_data['caption_attribute'] ); ?>" data-title="<?php echo esc_attr( $attachment_data['title_attribute'] ); ?>" data-caption="<?php echo esc_attr( $attachment_data['caption_attribute'] ); ?>">
										<?php /* translators: The link. */ ?>
										<span class="screen-reader-text"><?php printf( esc_attr__( 'Go to "%s"', 'Avada' ), get_the_title( $post ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
										<img src="<?php echo esc_url_raw( $attachment_data['url'] ); ?>" alt="<?php echo esc_attr( $attachment_data['alt'] ); ?>" role="presentation" />
									</a>
								<?php else : ?>
									<img src="<?php echo esc_url_raw( $attachment_data['url'] ); ?>" alt="<?php echo esc_attr( $attachment_data['alt'] ); ?>" role="presentation" />
								<?php endif; ?>
							</li>
						<?php endif; ?>
					<?php endif; ?>
					<?php $i = 2; ?>
					<?php while ( $i <= Avada()->settings->get( 'posts_slideshow_number' ) ) : ?>
						<?php $attachment_new_id = fusion_get_featured_image_id( 'featured-image-' . $i, 'post' ); ?>
						<?php if ( $attachment_new_id ) : ?>
							<?php $attachment_data = Avada()->images->get_attachment_data( $attachment_new_id ); ?>
							<?php if ( is_array( $attachment_data ) ) : ?>
								<li>
									<?php if ( Avada()->settings->get( 'status_lightbox' ) && Avada()->settings->get( 'status_lightbox_single' ) ) : ?>
										<a href="<?php echo esc_url_raw( $attachment_data['url'] ); ?>" rel="prettyPhoto[gallery<?php the_ID(); ?>]" title="<?php echo esc_attr( $attachment_data['caption_attribute'] ); ?>" data-title="<?php echo esc_attr( $attachment_data['title_attribute'] ); ?>" data-caption="<?php echo esc_attr( $attachment_data['caption_attribute'] ); ?>">
											<?php // Translators: The link. ?>
											<span class="screen-reader-text"><?php printf( esc_attr__( 'Go to "%s"', 'Avada' ), get_the_title( $post ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
											<img src="<?php echo esc_url_raw( $attachment_data['url'] ); ?>" alt="<?php echo esc_attr( $attachment_data['alt'] ); ?>" role="presentation" />
										</a>
									<?php else : ?>
										<img src="<?php echo esc_url_raw( $attachment_data['url'] ); ?>" alt="<?php echo esc_attr( $attachment_data['alt'] ); ?>" role="presentation" />
									<?php endif; ?>
								</li>
							<?php endif; ?>
						<?php endif; ?>
						<?php $i++; ?>
					<?php endwhile; ?>
				</ul>
			</div>
		<?php endif; ?>
	<?php endif; ?>
<?php endif; ?>
