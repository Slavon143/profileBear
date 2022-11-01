<?php
/**
 * Checkout review order block
 *
 * This template can be overridden by copying it to yourtheme/svea-checkout-for-woocommerce/checkout/review-order.php.
 *
 * We will in some rare cases update this file. For this reason, it is important that you keep a look at the version-number and implement the new changes in your theme.
 * If you do not keep this file updated, there is no guarantee that the plugin will work as intended.
 *
 * @author  The Generation
 * @package Svea_Checkout_For_WooCommerce/Templates
 * @version 1.5.0
 */

if( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

?>
<table class="shop_table woocommerce-checkout-review-order-table">

	<?php do_action( 'woocommerce_sco_before_review_order_table_thead' ); ?>

	<thead>
	<tr>
		<th class="product-name"><?php _e( 'Product', WC_Svea_Checkout_i18n::TEXT_DOMAIN ); ?></th>
		<th class="product-total"><?php _e( 'Total', WC_Svea_Checkout_i18n::TEXT_DOMAIN ); ?></th>
	</tr>
	</thead>

	<?php do_action( 'woocommerce_sco_after_review_order_table_thead' ); ?>

	<?php do_action( 'woocommerce_sco_before_review_order_table_tbody' ); ?>

	<tbody>
	<?php do_action( 'woocommerce_review_order_before_cart_contents' );

	foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) :

		$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
		if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) :
			?>

			<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
				<td class="product-name">
					<?php echo apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) . '&nbsp;'; ?>
					<?php echo apply_filters( 'woocommerce_checkout_cart_item_quantity', ' <strong class="product-quantity">' . sprintf( '&times; %s', $cart_item['quantity'] ) . '</strong>', $cart_item, $cart_item_key ); ?>
					<?php echo wc_get_formatted_cart_item_data( $cart_item ); ?>
				</td>
				<td class="product-total">
					<?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); ?>
				</td>
			</tr>
			<?php
		endif;
	endforeach;
	do_action( 'woocommerce_review_order_after_cart_contents' ); ?>
	</tbody>
	<?php do_action( 'woocommerce_sco_before_review_order_table_tfoot' ); ?>
	<tfoot>
	<?php do_action( 'woocommerce_sco_before_review_order_subtotal' ); ?>

	<tr class="cart-subtotal">
		<th><?php _e( 'Subtotal', WC_Svea_Checkout_i18n::TEXT_DOMAIN ); ?></th>
		<td><?php wc_cart_totals_subtotal_html(); ?></td>
	</tr>

	<?php do_action( 'woocommerce_sco_after_review_order_subtotal' ); ?>

	<?php do_action( 'woocommerce_sco_before_review_order_coupons' ); ?>

	<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
		<tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
			<th><?php wc_cart_totals_coupon_label( $coupon ); ?></th>
			<td><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
		</tr>
	<?php endforeach; ?>
	<?php do_action( 'woocommerce_sco_after_review_order_coupons' ); ?>

	<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>

		<?php do_action( 'woocommerce_review_order_before_shipping' ); ?>

		<?php wc_cart_totals_shipping_html(); ?>

		<?php do_action( 'woocommerce_review_order_after_shipping' ); ?>

	<?php endif; ?>

	<?php do_action( 'woocommerce_sco_before_review_order_fee' ); ?>

	<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
		<tr class="fee">
			<th><?php echo esc_html( $fee->name ); ?></th>
			<td><?php wc_cart_totals_fee_html( $fee ); ?></td>
		</tr>
	<?php endforeach; ?>

	<?php do_action( 'woocommerce_sco_after_review_order_fee' ); ?>

	<?php if ( wc_tax_enabled() && WC()->cart->get_tax_price_display_mode() === 'excl' ) : ?>

		<?php do_action( 'woocommerce_sco_before_review_order_tax' ); ?>

		<?php if ( 'itemized' === get_option('woocommerce_tax_total_display' ) ) : ?>

			<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : ?>
				<tr class="tax-rate tax-rate-<?php echo sanitize_title( $code ); ?>">
					<th><?php echo esc_html( $tax->label ); ?></th>
					<td><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
				</tr>
			<?php endforeach; ?>

		<?php else : ?>
			<tr class="tax-total">
				<th><?php echo esc_html(WC()->countries->tax_or_vat()); ?></th>
				<td><?php wc_cart_totals_taxes_total_html(); ?></td>
			</tr>
		<?php endif; ?>

		<?php do_action( 'woocommerce_sco_after_review_order_tax' ); ?>

	<?php endif; ?>

	<?php do_action( 'woocommerce_review_order_before_order_total' ); ?>

	<tr class="order-total">
		<th><?php _e( 'Total', WC_Svea_Checkout_i18n::TEXT_DOMAIN ); ?></th>
		<td><?php wc_cart_totals_order_total_html(); ?></td>
	</tr>

	<?php do_action( 'woocommerce_review_order_after_order_total' ); ?>

	</tfoot>
	<?php do_action( 'woocommerce_sco_after_review_order_table_tfoot' ); ?>

</table>
