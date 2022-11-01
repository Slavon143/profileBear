<?php
/**
 * Featured images for single post.
 *
 * @package Avada
 * @subpackage Templates
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}
?>
<?php if ( ! post_password_required( get_the_ID() ) ) : ?>
	<?php if ( Avada()->settings->get( 'featured_images_single' ) ) : ?>
		<?php $video = apply_filters( 'privacy_iframe_embed', fusion_get_page_option( 'video', get_the_ID() ) ); ?>
		<?php if ( 0 < avada_number_of_featured_images() || $video ) : ?>
			<?php
			Avada()->images->set_grid_image_meta(
				[
					'layout'  => strtolower( 'large' ),
					'columns' => '1',
				]
			);
			?>
			<div class="fusion-flexslider flexslider fusion-flexslider-loading post-slideshow fusion-post-slideshow">
				<ul class="slides">
					<?php if ( $video ) : ?>
						<li>
							<div class="full-video">
								<?php echo $video; // phpcs:ignore WordPress.Security.EscapeOutput ?>
							</div>
						</li>
					<?php endif; ?>
					<?php if ( has_post_thumbnail() && fusion_get_option( 'show_first_featured_image' ) ) : ?>
						<li>
							<?php if ( Avada()->settings->get( 'status_lightbox' ) && Avada()->settings->get( 'status_lightbox_single' ) ) : ?>
								<?php $attachment_data = Avada()->images->get_attachment_data( get_post_thumbnail_id() ); ?>
								<?php if ( is_array( $attachment_data ) ) : ?>
									<a href="<?php echo esc_url_raw( $attachment_data['url'] ); ?>" data-rel="iLightbox[gallery<?php the_ID(); ?>]" title="<?php echo esc_attr( $attachment_data['caption_attribute'] ); ?>" data-title="<?php echo esc_attr( $attachment_data['title_attribute'] ); ?>" data-caption="<?php echo esc_attr( $attachment_data['caption_attribute'] ); ?>" aria-label="<?php echo esc_attr( $attachment_data['title_attribute'] ); ?>">
										<span class="screen-reader-text"><?php esc_attr_e( 'View Larger Image', 'Avada' ); ?></span>
										<?php echo get_the_post_thumbnail( get_the_ID(), 'full' ); ?>
									</a>
								<?php else : ?>
									<?php echo get_the_post_thumbnail( get_the_ID(), 'full' ); ?>
								<?php endif; ?>
							<?php else : ?>
								<?php echo get_the_post_thumbnail( get_the_ID(), 'full' ); ?>
							<?php endif; ?>
						</li>

					<?php endif; ?>
					<?php
					$the_post_type = false !== get_post_type() ? get_post_type() : 'post';
					$image_count   = 2;
					?>
					<?php while ( $image_count <= Avada()->settings->get( 'posts_slideshow_number' ) ) : ?>
						<?php $attachment_new_id = fusion_get_featured_image_id( 'featured-image-' . $image_count, $the_post_type ); ?>
						<?php if ( $attachment_new_id ) : ?>
							<li>
								<?php if ( Avada()->settings->get( 'status_lightbox' ) && Avada()->settings->get( 'status_lightbox_single' ) ) : ?>
									<?php $attachment_data = Avada()->images->get_attachment_data( $attachment_new_id ); ?>
									<?php if ( is_array( $attachment_data ) ) : ?>
										<a href="<?php echo esc_url_raw( $attachment_data['url'] ); ?>" data-rel="iLightbox[gallery<?php the_ID(); ?>]" title="<?php echo esc_attr( $attachment_data['caption_attribute'] ); ?>" data-title="<?php echo esc_attr( $attachment_data['title_attribute'] ); ?>" data-caption="<?php echo esc_attr( $attachment_data['caption_attribute'] ); ?>" aria-label="<?php echo esc_attr( $attachment_data['title_attribute'] ); ?>">
											<?php echo wp_get_attachment_image( $attachment_new_id, 'full' ); ?>
										</a>
									<?php else : ?>
										<?php echo wp_get_attachment_image( $attachment_new_id, 'full' ); ?>
									<?php endif; ?>
								<?php else : ?>
									<?php echo wp_get_attachment_image( $attachment_new_id, 'full' ); ?>
								<?php endif; ?>
							</li>
						<?php endif; ?>
						<?php $image_count++; ?>
					<?php endwhile; ?>
				</ul>
			</div>
			<?php Avada()->images->set_grid_image_meta( [] ); ?>
		<?php endif; ?>
	<?php endif; ?>
<?php endif; ?>
