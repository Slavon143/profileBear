<?php
/**
 * ElasticSlider template.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      5.1
 */

if ( ! Avada()->settings->get( 'status_eslider' ) ) {
	return;
}
$args = [
	'post_type'        => 'themefusion_elastic',
	'posts_per_page'   => -1, // phpcs:ignore WPThemeReview.CoreFunctionality.PostsPerPage
	'suppress_filters' => 0,
];

$args['tax_query'][] = [
	'taxonomy' => 'themefusion_es_groups',
	'field'    => 'slug',
	'terms'    => $term,
];

$query = fusion_cached_query( $args );
$count = 1;
?>

<?php if ( $query->have_posts() ) : ?>
	<div id="ei-slider" class="ei-slider ei-slider-<?php echo absint( get_term_by( 'slug', $term, 'themefusion_es_groups' )->term_id ); ?>">
		<div class="fusion-slider-loading"><?php esc_attr_e( 'Loading...', 'Avada' ); ?></div>
		<ul class="ei-slider-large">
			<?php while ( $query->have_posts() ) : ?>
				<?php $query->the_post(); ?>
				<li class="ei-slide-<?php the_ID(); ?>" style="<?php echo ( $count > 0 ) ? 'opacity: 0;' : ''; ?>">
					<?php
					the_post_thumbnail(
						'full',
						[
							'title' => '',
							'alt'   => get_post_meta( get_post_thumbnail_id(), '_wp_attachment_image_alt', true ),
						]
					);
					?>
					<div class="ei-title">
						<?php
						$caption1 = fusion_get_page_option( 'caption_1', get_the_ID() );
						$caption2 = fusion_get_page_option( 'caption_2', get_the_ID() );
						?>
						<?php if ( $caption1 ) : ?>
							<h2><?php echo esc_textarea( $caption1 ); ?></h2>
						<?php endif; ?>
						<?php if ( $caption2 ) : ?>
							<h3><?php echo esc_textarea( $caption2 ); ?></h3>
						<?php endif; ?>
					</div>
				</li>
				<?php $count ++; ?>
			<?php endwhile; ?>
		</ul>
		<ul class="ei-slider-thumbs" style="display: none;">
			<li class="ei-slider-element">Current</li>
			<?php while ( $query->have_posts() ) : ?>
				<?php $query->the_post(); ?>
				<li>
					<a href="#"><?php the_title(); ?></a>
					<?php
					the_post_thumbnail(
						'full',
						[
							'title' => '',
							'alt'   => get_post_meta( get_post_thumbnail_id(), '_wp_attachment_image_alt', true ),
						]
					);
					?>
				</li>
			<?php endwhile; ?>
		</ul>
	</div>
	<?php
	wp_reset_postdata();
endif;
