<?php
/**
 * WooCommerce thumbnail template (clean mode).
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      5.1.0
 */

global $product, $woocommerce;

$id             = get_the_ID(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
$in_cart        = fusion_library()->woocommerce->is_product_in_cart( $id );
$size           = 'shop_catalog';
$post_permalink = get_permalink();

?>
<div class="fusion-clean-product-image-wrapper <?php echo ( $in_cart ) ? 'fusion-item-in-cart' : ''; ?>">
	<?php echo fusion_render_first_featured_image_markup( $id, $size, $post_permalink, true, false, true, 'disable', 'disable', '', '', 'yes', true ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
</div>
