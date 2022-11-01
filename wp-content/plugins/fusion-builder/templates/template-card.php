<?php
/**
 * Template used for post card.
 *
 * @package Avada
 * @subpackage Templates
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

$studio_styles = false;

if ( isset( $_GET['awb-studio-post-card'] ) && ! isset( $_GET['fb-edit'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
	$width         = fusion_data()->post_meta( get_queried_object_id() )->get( 'preview_width' );
	$studio_styles = ! empty( $width ) ? 'style="width:' . intval( $width ) . '%"' : false;
}
?>
<?php get_header(); ?>
<section id="content" <?php ( class_exists( 'Avada' ) ? Avada()->layout->add_style( 'content_style' ) : '' ); ?>>
	<div class="post-content" <?php echo ( $studio_styles ? $studio_styles : '' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
		<?php
			global $post, $wp_query;
			$post_card                         = $post;
			$target                            = Fusion_Template_Builder()->get_target_example( $post->ID );
			$option                            = fusion_get_page_option( 'dynamic_content_preview_type', $post->ID );
			FusionBuilder()->editing_post_card = true;

			add_filter(
				'fusion_dynamic_post_id',
				function() use ( $target ) {
					if ( property_exists( $target, 'term_id' ) ) {
						return $target->term_id;
					}
					return $target->ID;
				},
				10
			);

			add_filter(
				'fusion_component_element_target',
				function() use ( $target ) {
					return $target;
				},
				10
			);

			add_filter(
				'fusion_app_preview_data',
				function( $data, $page_id, $post_type ) {
					$data['is_fusion_element']   = true;
					$data['fusion_element_type'] = 'post_cards';
					$data['template_category']   = 'post_cards';

					return $data;
				},
				20,
				3
			);

			if ( property_exists( $target, 'term_id' ) ) {
				$GLOBALS['wp_query']->is_tax         = true;
				$GLOBALS['wp_query']->is_archive     = true;
				$GLOBALS['wp_query']->queried_object = $target;
			} else {
				$post = $target;
				$wp_query->setup_postdata( $target );
			}

			FusionBuilder()->post_card_data['is_rendering'] = true;
			Fusion_Template_Builder()->render_content( $post_card, true );
			FusionBuilder()->post_card_data['is_rendering'] = false;
			?>
	</div>
</section>
<?php do_action( 'avada_after_content' ); ?>
<?php get_footer(); ?>
