<?php
/**
 * Pay for order form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-pay.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 5.2.0
 */

defined( 'ABSPATH' ) || exit;

$totals = $order->get_order_item_totals();
?>
<div class="woocommerce-content-box full-width avada-checkout checkout">
	<h3 id="order_review_heading"><?php _e( 'Your order', 'woocommerce' ); ?></h3>

	<form id="order_review" method="post">
		<table class="shop_table">
			<thead>
				<tr>
					<th class="product-name"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
					<th class="product-total"><?php esc_html_e( 'Totals', 'woocommerce' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( count( $order->get_items() ) > 0 ) : ?>
					<?php foreach ( $order->get_items() as $item_id => $item ) : ?>
						<?php
						if ( ! apply_filters( 'woocommerce_order_item_visible', true, $item ) ) {
							continue;
						}

						$product           = apply_filters( 'woocommerce_order_item_product', $item->get_product(), $item );
						$is_visible        = $product && $product->is_visible();

						$product_permalink = apply_filters( 'woocommerce_order_item_permalink', $is_visible ? $product->get_permalink( $item ) : '', $item, $order );
						?>
						<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_order_item_class', 'order_item', $item, $order ) ); ?>">
							<td class="product-name">
								<?php // ThemeFusion edit for Avada theme: add thumbnail to product name column. ?>
								<div class="fusion-product-name-wrapper">
									<?php if ( $is_visible ) : ?>
										<span class="product-thumbnail">
											<?php
												$thumbnail = $product->get_image();

												if ( ! $product_permalink ) {
													echo $thumbnail;
												} else {
													printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail );
												}
											?>
										</span>
									<?php endif; ?>

									<div class="product-info">
										<?php
										echo wp_kses_post( apply_filters( 'woocommerce_order_item_name', $product_permalink ? sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $item->get_name() ) : $item->get_name(), $item, $is_visible ) );
										echo apply_filters( 'woocommerce_order_item_quantity_html', ' <strong class="product-quantity">' . sprintf( '&times; %s', esc_html( $item->get_quantity() ) ) . '</strong>', $item );

										do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order, false );

										wc_display_item_meta( $item );

										do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order, false );
										?>
									</div>
								</div>
							</td>
							<td class="product-total"><?php echo $order->get_formatted_line_subtotal( $item ); ?></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
			<tfoot>
				<?php if ( $totals ) : ?>
					<?php $last_total = count( $totals ) - 1; ?>
					<?php $i = 0; ?>
					<?php foreach ( $totals as $total ) : ?>
						<?php if ( $i == $last_total ) : ?>
							<tr class="order-total">
						<?php else : ?>
							<tr>
						<?php endif; ?>
							<th scope="row"><?php echo $total['label']; ?></th>
							<td class="product-total"><?php echo $total['value']; ?>
						</tr>
						<?php $i++; ?>
					<?php endforeach; ?>
				<?php endif; ?>
			</tfoot>
		</table>

		<div id="payment" class="woocommerce-checkout-payment">
			<?php if ( $order->needs_payment() ) : ?>
				<ul class="wc_payment_methods payment_methods methods">
					<?php
						if ( ! empty( $available_gateways ) ) {
							foreach ( $available_gateways as $gateway ) {
								wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) );
							}
						} else {
							echo '<li class="woocommerce-notice woocommerce-notice--info woocommerce-info">' . apply_filters( 'woocommerce_no_available_payment_methods_message', __( 'Sorry, it seems that there are no available payment methods for your location. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce' ) ) . '</li>';
						}
					?>
				</ul>
			<?php endif; ?>

			<div class="form-row">
				<input type="hidden" name="woocommerce_pay" value="1" />

				<?php wc_get_template( 'checkout/terms.php' ); ?>

				<?php do_action( 'woocommerce_pay_order_before_submit' ); ?>

				<?php echo apply_filters( 'woocommerce_pay_order_button_html', '<button type="submit" class="button alt" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '">' . esc_html( $order_button_text ) . '</button>' ); ?>

				<?php do_action( 'woocommerce_pay_order_after_submit' ); ?>

				<?php wp_nonce_field( 'woocommerce-pay', 'woocommerce-pay-nonce' ); ?>
				<div class="clear"></div>
			</div>
		</div>
	</form>
</div>
