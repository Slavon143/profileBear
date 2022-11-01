<?php
/**
 * Quick View.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      6.1
 */

global $product, $post;

$product_page_bg_color           = fusion_get_option( 'content_bg_color' );
$product_page_bg_color_lightness = Fusion_Color::new_color( $product_page_bg_color )->lightness;
?>
<script type="text/javascript">
	var productBackgroundColor          = '<?php echo esc_html( $product_page_bg_color ); ?>',
		productBackgroundColorLightness = '<?php echo esc_html( $product_page_bg_color_lightness ); ?>',
		productQuickViewSingleURL       = '<?php echo esc_url( get_permalink() ); ?>';
</script>

<div id="product-<?php echo esc_attr( $product->get_id() ); ?>" <?php wc_product_class( '', $product ); ?>>
	<div class="woocommerce-product-gallery">

		<?php
		$display_badge = ! is_null( $product ) && method_exists( $product, 'is_on_sale' ) && method_exists( $product, 'is_in_stock' );
		$woo_badges    = '';

		ob_start();
		if ( $display_badge ) {
			get_template_part( 'templates/wc-product-loop-outofstock-flash' );
		}

		if ( $display_badge && $product->is_on_sale() ) {
			echo apply_filters( 'woocommerce_sale_flash', '<span class="onsale">' . esc_html__( 'Sale!', 'woocommerce' ) . '</span>', $post, $product ); // phpcs:ignore WordPress.Security.EscapeOutput
		}
		$woo_badges = ob_get_clean();

		if ( '' !== $woo_badges ) {
			echo '<div class="fusion-woo-badges-wrapper">' . $woo_badges . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		?>

		<div class="fusion-flexslider fusion-flexslider-loading fusion-woocommerce-quick-view-slideshow">
			<ul class="slides">
				<?php if ( has_post_thumbnail() ) : ?>
					<li><?php echo get_the_post_thumbnail( $product->get_id(), 'full' ); ?></li>

					<?php $attachment_ids = $product->get_gallery_image_ids(); ?>

					<?php if ( $attachment_ids ) : ?>
						<?php foreach ( $attachment_ids as $attachment_id ) : ?>
							<li><?php echo wp_get_attachment_image( $attachment_id, 'full' ); ?></li>
						<?php endforeach; ?>
					<?php endif; ?>
				<?php else : ?>
					<div class="woocommerce-product-gallery__image--placeholder">
						<img src="<?php echo esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ); ?>" alt="<?php esc_attr_e( 'Awaiting product image', 'Avada' ); ?>" class="wp-post-image" />
					</div>
				<?php endif; ?>
			</ul>
		</div>
	</div>
	<div class="fusion-wqv-content-inner">
		<div class="summary entry-summary">
			<?php
			/**
			 * Hook: fusion_quick_view_summary_content.
			 *
			 * @hooked Avada_Woocommerce->template_single_title - 5
			 * @hooked Avada_Woocommerce->stock_html - 10
			 * @hooked woocommerce_template_single_price - 10
			 * @hooked woocommerce_template_single_rating - 11
			 * @hooked Avada_Woocommerce->add_product_border - 19
			 * @hooked woocommerce_template_single_excerpt - 20
			 * @hooked woocommerce_template_single_add_to_cart - 30
			 */
			do_action( 'fusion_quick_view_summary_content' );
			?>
		</div>

		<a href="<?php echo esc_url( get_permalink() ); ?>" class="fusion-button-view-details fusion-button fusion-button-default-size button-default button"><?php esc_html_e( 'View Details', 'Avada' ); ?></a>
	</div>
</div>
