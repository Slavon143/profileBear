<?php
/**
 * WooCommerce thumbnail template (classic mode).
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      5.1.0
 */

global $product;

$id         = get_the_ID(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
$is_product = class_exists( 'WooCommerce' ) && 'product' === get_post_type();
$size       = 'full';

if ( true === $is_product ) {
	$in_cart = fusion_library()->woocommerce->is_product_in_cart( $id );
	$size    = 'shop_catalog';
}

$first_image_id   = '';
$attachment_image = '';
if ( true === $is_product && Avada()->settings->get( 'woocommerce_disable_crossfade_effect' ) ) {
	$gallery = get_post_meta( $id, '_product_image_gallery', true );

	if ( ! empty( $gallery ) ) {
		$gallery        = explode( ',', $gallery );
		$first_image_id = $gallery[0];
	}
} else {
	$first_image_id = fusion_get_featured_image_id( 'featured-image-2' );
}

if ( $first_image_id ) {
	$attachment_image = wp_get_attachment_image(
		$first_image_id,
		$size,
		false,
		[
			'class' => 'hover-image',
		]
	);
}

$thumb_image = get_the_post_thumbnail( $id, $size );

if ( ! $thumb_image && function_exists( 'wc_placeholder_img_src' ) && wc_placeholder_img_src() ) {
	$thumb_image = wc_placeholder_img( $size );
}

$classes = apply_filters( 'awb_crossfade_image_classes', [ 'featured-image' ], $attachment_image );
if ( $attachment_image ) {
	$classes[] = 'crossfade-images';
}
?>
<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
	<?php echo $attachment_image; // phpcs:ignore WordPress.Security.EscapeOutput ?>
	<?php echo $thumb_image; // phpcs:ignore WordPress.Security.EscapeOutput ?>

	<?php if ( true === $is_product ) : ?>
		<?php if ( $in_cart ) : ?>
			<div class="cart-loading"><i class="awb-icon-check-square-o" aria-hidden="true"></i></div>
		<?php else : ?>
			<div class="cart-loading"><i class="awb-icon-spinner" aria-hidden="true"></i></div>
		<?php endif; ?>
	<?php endif; ?>
</div>
