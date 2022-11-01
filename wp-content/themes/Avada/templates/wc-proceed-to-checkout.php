<?php
/**
 * Proceed to Checkout Button.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      5.1.0
 */

?>
<a href="" class="fusion-button button-default fusion-button-default-size button fusion-update-cart">
	<?php esc_attr_e( 'Update cart', 'woocommerce' ); ?>
</a>
<a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="fusion-button button-default fusion-button-default-size button checkout-button button alt wc-forward">
	<?php esc_html_e( 'Proceed to checkout', 'woocommerce' ); ?>
</a>
