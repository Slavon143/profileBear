<?php
/**
 * Featured images for page.
 *
 * @package Avada
 * @subpackage Templates
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

$video           = '';
$featured_images = '';

if ( ! post_password_required( get_the_ID() ) ) {
	if ( Avada()->settings->get( 'featured_images_pages' ) && ! is_archive() ) {
		$pyre_video = apply_filters( 'privacy_iframe_embed', fusion_get_page_option( 'video', get_the_ID() ) );
		if ( 0 < avada_number_of_featured_images() || $pyre_video ) {
			if ( $pyre_video ) {
				$video = '<li><div class="full-video">' . $pyre_video . '</div></li>';
			}

			if ( has_post_thumbnail() && fusion_get_option( 'show_first_featured_image' ) ) {
				$attachment_data = Avada()->images->get_attachment_data( get_post_thumbnail_id() );
				if ( is_array( $attachment_data ) ) {
					$featured_images .= '<li><a href="' . esc_url( $attachment_data['url'] ) . '" data-rel="iLightbox[gallery' . get_the_ID() . ']" title="' . esc_attr( $attachment_data['caption_attribute'] ) . '" data-title="' . esc_attr( $attachment_data['title_attribute'] ) . '" data-caption="' . esc_attr( $attachment_data['caption_attribute'] ) . '">';
					$featured_images .= '<img src="' . esc_url( $attachment_data['url'] ) . '" alt="' . esc_attr( $attachment_data['alt'] ) . '" />';
					$featured_images .= '</a></li>';
				}
			}

			$i = 2;
			while ( $i <= Avada()->settings->get( 'posts_slideshow_number' ) ) :

				$attachment_new_id = fusion_get_featured_image_id( 'featured-image-' . $i, 'page' );

				if ( $attachment_new_id ) {
					$attachment_data = Avada()->images->get_attachment_data( $attachment_new_id );

					$featured_images .= '<li><a href="' . esc_url( $attachment_data['url'] ) . '" data-rel="iLightbox[gallery' . get_the_ID() . ']" title="' . esc_attr( $attachment_data['caption_attribute'] ) . '" data-title="' . esc_attr( $attachment_data['title_attribute'] ) . '" data-caption="' . esc_attr( $attachment_data['caption_attribute'] ) . '">';
					$featured_images .= '<img src="' . esc_url( $attachment_data['url'] ) . '" alt="' . esc_attr( $attachment_data['alt'] ) . '" />';
					$featured_images .= '</a></li>';
				}
				$i++;
			endwhile;
			?>
			<div class="fusion-flexslider fusion-flexslider-loading flexslider post-slideshow">
				<ul class="slides">
					<?php echo $video . $featured_images; // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</ul>
			</div>
			<?php
		}
	}
}
