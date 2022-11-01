<?php
/**
 * Template used for pages.
 *
 * @package Avada
 * @subpackage Templates
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}
?>
<?php get_header(); ?>
<section id="content" <?php ( class_exists( 'Avada' ) ? Avada()->layout->add_style( 'content_style' ) : '' ); ?>>
	<div class="post-content">
		<?php
			$dummy_post = Fusion_Dummy_Post::get_dummy_post();
			echo do_shortcode( $dummy_post->post_content );
			do_action( 'awb_off_canvas_preview_content' );
		?>
	</div>
</section>
<?php do_action( 'avada_after_content' ); ?>
<?php
get_footer();
