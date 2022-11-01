<?php
/**
 * WooCommerce Cart Collaterals template.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      5.1.0
 */

global $woocommerce;
?>

<div class="shipping-coupon">
	<?php get_template_part( 'templates/wc-cart-shipping-calc' ); ?>
	<?php if ( wc_coupons_enabled() ) : ?>
		<div class="coupon">
			<h2><?php esc_attr_e( 'Have A Promotional Code?', 'Avada' ); ?></h2>

			<div class="avada-coupon-fields">
				<input type="text" name="coupon_code" class="input-text" id="avada_coupon_code" value="" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>" />
				<button type="submit" class="fusion-apply-coupon fusion-button button-default fusion-button-default-size button" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>"><?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?></button>
			</div>
			<?php do_action( 'woocommerce_cart_coupon' ); ?>
		</div>
	<?php endif; ?>
</div>
