<?php
/**
 * Featured images for single portfolio.
 *
 * @package Avada-Core
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}
if ( ! class_exists( 'Avada' ) ) {
	exit( 'This feature requires the Avada theme.' );
}

$fusion_settings = awb_get_fusion_settings();
$video           = fusion_get_option( 'video' );

if ( ! post_password_required( get_the_ID() ) && function_exists( 'fusion_library' ) ) : ?>
	<?php if ( $fusion_settings->get( 'portfolio_featured_images' ) ) : ?>
		<?php if ( 0 < avada_number_of_featured_images() || $video ) : ?>
			<div class="fusion-flexslider flexslider fusion-post-slideshow post-slideshow fusion-flexslider-loading">
				<ul class="slides">
					<?php if ( $video ) : ?>
						<li>
							<div class="full-video">
								<?php echo apply_filters( 'privacy_iframe_embed', $video ); // phpcs:ignore WordPress.Security ?>
							</div>
						</li>
					<?php endif; ?>
					<?php
						fusion_library()->images->set_grid_image_meta(
							[
								'layout'  => 'large',
								'columns' => '1',
							]
						);
					?>
					<?php if ( has_post_thumbnail() && fusion_get_option( 'show_first_featured_image' ) ) : ?>
						<li>
							<?php if ( $fusion_settings->get( 'status_lightbox' ) && $fusion_settings->get( 'status_lightbox_single' ) ) : ?>
								<?php $attachment_data = fusion_library()->images->get_attachment_data( get_post_thumbnail_id() ); ?>
								<?php if ( is_array( $attachment_data ) ) : ?>
									<a href="<?php echo esc_url( $attachment_data['url'] ); ?>" data-rel="iLightbox[gallery<?php the_ID(); ?>]" title="<?php echo esc_attr( $attachment_data['title_attribute'] ); ?>" data-title="<?php echo esc_attr( $attachment_data['title_attribute'] ); ?>" data-caption="<?php echo esc_attr( $attachment_data['caption_attribute'] ); ?>">
										<span class="screen-reader-text"><?php esc_html_e( 'View Larger Image', 'fusion-core' ); ?></span>
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
					<?php $i = 2; ?>
					<?php while ( $i <= $fusion_settings->get( 'posts_slideshow_number' ) ) : ?>
						<?php $attachment_new_id = fusion_get_featured_image_id( 'featured-image-' . $i, 'avada_portfolio' ); ?>
						<?php if ( $attachment_new_id ) : ?>
							<li>
								<?php if ( $fusion_settings->get( 'status_lightbox' ) && $fusion_settings->get( 'status_lightbox_single' ) ) : ?>
									<?php $attachment_data = fusion_library()->images->get_attachment_data( $attachment_new_id ); ?>
									<?php if ( is_array( $attachment_data ) ) : ?>
										<a href="<?php echo esc_url( $attachment_data['url'] ); ?>" data-rel="iLightbox[gallery<?php the_ID(); ?>]" title="<?php echo esc_attr( $attachment_data['title_attribute'] ); ?>" data-title="<?php echo esc_attr( $attachment_data['title_attribute'] ); ?>" data-caption="<?php echo esc_attr( $attachment_data['caption_attribute'] ); ?>">
											<span class="screen-reader-text"><?php esc_html_e( 'View Larger Image', 'fusion-core' ); ?></span>
											<?php echo wp_get_attachment_image( $attachment_new_id, 'full' ); ?>
										</a>
									<?php else : ?>
										<?php echo get_the_post_thumbnail( get_the_ID(), 'full' ); ?>
									<?php endif; ?>
								<?php else : ?>
									<?php echo wp_get_attachment_image( $attachment_new_id, 'full' ); ?>
								<?php endif; ?>
							</li>
						<?php endif; ?>
						<?php $i++; ?>
					<?php endwhile; ?>
					<?php fusion_library()->images->set_grid_image_meta( [] ); ?>
				</ul>
			</div>
		<?php endif; ?>
	<?php endif; // Portfolio single image Global Options check. ?>
<?php endif; // Password check. ?>
