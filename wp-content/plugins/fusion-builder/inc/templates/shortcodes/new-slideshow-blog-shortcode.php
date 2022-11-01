<?php
/**
 * Template file.
 *
 * @package fusion-builder
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

global $post;
$fusion_settings = awb_get_fusion_settings();

$featured_image_width  = fusion_get_page_option( 'fimg[width]', $post->ID );
$featured_image_height = fusion_get_page_option( 'fimg[height]', $post->ID );
$blog_loop_id          = isset( $atts['loop-id'] ) ? $atts['loop-id'] : '#post-';
$article_class         = $blog_loop_id . absint( $post->ID );
?>
<?php if ( 'grid' !== $atts['layout'] && 'timeline' !== $atts['layout'] ) : ?>
	<style type="text/css">
		<?php if ( $featured_image_width && 'auto' !== $featured_image_width ) : ?>
			<?php echo $article_class; // phpcs:ignore WordPress.Security.EscapeOutput ?> .fusion-post-slideshow {
				max-width: <?php echo esc_attr( $featured_image_width ); ?> !important;
			}
		<?php endif; ?>

		<?php if ( $featured_image_height && 'auto' !== $featured_image_height ) : ?>
			<?php echo $article_class; // phpcs:ignore WordPress.Security.EscapeOutput ?> .fusion-post-slideshow,
			<?php echo $article_class; // phpcs:ignore WordPress.Security.EscapeOutput ?> .fusion-post-slideshow .fusion-image-wrapper img {
				max-height: <?php echo esc_attr( $featured_image_height ); ?> !important;
			}
		<?php endif; ?>

		<?php if ( $featured_image_width && 'auto' === $featured_image_width ) : ?>
			<?php echo $article_class; // phpcs:ignore WordPress.Security.EscapeOutput ?> .fusion-post-slideshow .fusion-image-wrapper img {
				width: auto;
			}
		<?php endif; ?>

		<?php if ( $featured_image_height && 'auto' === $featured_image_height ) : ?>
			<?php echo $article_class; // phpcs:ignore WordPress.Security.EscapeOutput ?> .fusion-post-slideshow .fusion-image-wrapper img {
				height: auto;
			}
		<?php endif; ?>

		<?php if ( $featured_image_height && $featured_image_width && 'auto' !== $featured_image_height && 'auto' !== $featured_image_width ) : ?>
			@media only screen and (max-width: 479px){
				<?php echo $article_class; // phpcs:ignore WordPress.Security.EscapeOutput ?> .fusion-post-slideshow,
				<?php echo $article_class; // phpcs:ignore WordPress.Security.EscapeOutput ?> .fusion-post-slideshow .fusion-image-wrapper img {
					width :auto !important;
					height :auto !important;
				}
			}
		<?php endif; ?>
	</style>
<?php endif; ?>

<?php
$permalink = isset( $this->permalink ) ? $this->permalink : get_permalink( $post->ID );

$size = 'blog-large';
if ( class_exists( 'Avada' ) ) {
	$size = ( ! Avada()->template->has_sidebar() || fusion_get_option( 'blog_width_100' ) ) ? 'full' : 'blog-large';
	$size = ( 'medium' === $atts['layout'] || 'medium-alternate' === $atts['layout'] ) ? 'blog-medium' : $size;
	$size = ( $featured_image_height && $featured_image_width && 'auto' !== $featured_image_height && 'auto' !== $featured_image_width ) ? 'full' : $size;
	$size = ( 'auto' === $featured_image_height || 'auto' === $featured_image_width ) ? 'full' : $size;
	$size = ( 'grid' === $atts['layout'] || 'masonry' === $atts['layout'] || 'timeline' === $atts['layout'] ) ? 'full' : $size;
}

$post_video = apply_filters( 'privacy_iframe_embed', fusion_get_page_option( 'video', $post->ID ) );
?>

<?php if ( has_post_thumbnail() || $post_video ) : ?>
	<?php $thumbnail_id = get_post_thumbnail_id(); ?>
	<?php $border_style = ( 'grid' === $atts['layout'] || 'masonry' === $atts['layout'] || 'timeline' === $atts['layout'] ) ? 'border-color:' . $atts['grid_element_color'] . ';' : ''; ?>
	<div class="fusion-flexslider flexslider fusion-flexslider-loading fusion-post-slideshow" style="<?php echo esc_attr( $border_style ); ?>">
		<ul class="slides">
			<?php if ( $post_video ) : ?>
				<li>
					<div class="full-video"><?php echo $post_video; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
				</li>
			<?php endif; ?>

			<?php
			if ( 'grid' === $atts['layout'] || 'masonry' === $atts['layout'] ) {
				fusion_library()->images->set_grid_image_meta(
					[
						'layout'       => $atts['layout'],
						'columns'      => $atts['blog_grid_columns'],
						'gutter_width' => $atts['blog_grid_column_spacing'],
					]
				);
			} elseif ( 'timeline' === $atts['layout'] ) {
				fusion_library()->images->set_grid_image_meta(
					[
						'layout'  => $atts['layout'],
						'columns' => '2',
					]
				);
			} elseif ( false !== strpos( $atts['layout'], 'large' ) && 'full' === $size ) {
				fusion_library()->images->set_grid_image_meta(
					[
						'layout'  => $atts['layout'],
						'columns' => '1',
					]
				);
			}
			?>
			<?php if ( has_post_thumbnail() ) : ?>
				<?php $full_image = wp_get_attachment_image_src( $thumbnail_id, 'full' ); ?>
				<li><?php echo fusion_render_first_featured_image_markup( $post->ID, $size, $permalink ); // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
			<?php endif; ?>

			<?php $i = 2; ?>

			<?php while ( $i <= $fusion_settings->get( 'posts_slideshow_number' ) ) : ?>
				<?php $attachment_id = function_exists( 'fusion_get_featured_image_id' ) ? fusion_get_featured_image_id( 'featured-image-' . $i, 'post' ) : ''; ?>
				<?php if ( $attachment_id ) : ?>

					<?php
					$attachment_data = fusion_library()->images->get_attachment_data( $attachment_id );

					if ( 'grid' === $atts['layout'] ) {
						$size = fusion_library()->images->get_grid_image_base_size( $attachment_id, Fusion_Images::$grid_image_meta['layout'], Fusion_Images::$grid_image_meta['columns'] );
					}

					$attachment_image = wp_get_attachment_image_src( $attachment_id, $size );
					?>

					<?php if ( is_array( $attachment_data ) ) : ?>
						<li>
							<div class="fusion-image-wrapper">
								<a href="<?php echo esc_url( $permalink ); ?>" aria-label="<?php the_title_attribute(); ?>">
									<?php
									$image_markup = '<img src="' . $attachment_image[0] . '" alt="' . $attachment_data['alt'] . '" class="wp-image-' . $attachment_id . '" role="presentation"/>';
									$image_markup = fusion_library()->images->edit_grid_image_src( $image_markup, $post->ID, $attachment_id, $size );

									echo fusion_add_responsive_image_markup( $image_markup ); // phpcs:ignore WordPress.Security.EscapeOutput
									?>
								</a>
								<a style="display:none;" href="<?php echo esc_url_raw( $attachment_data['url'] ); ?>" data-rel="iLightbox[gallery<?php echo absint( $post->ID ); ?>]"  title="<?php echo esc_attr( $attachment_data['caption_attribute'] ); ?>" data-title="<?php echo esc_attr( $attachment_data['title_attribute'] ); ?>" data-caption="<?php echo esc_attr( $attachment_data['caption_attribute'] ); ?>">
									<?php if ( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) : ?>
										<img style="display:none;" alt="<?php echo esc_attr( $attachment_data['alt'] ); ?>" role="presentation" />
									<?php endif; ?>
								</a>
							</div>
						</li>
					<?php endif; ?>
				<?php endif; ?>
				<?php $i++; ?>
			<?php endwhile; ?>
			<?php fusion_library()->images->set_grid_image_meta( [] ); ?>
		</ul>
	</div>
	<?php
endif;

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
