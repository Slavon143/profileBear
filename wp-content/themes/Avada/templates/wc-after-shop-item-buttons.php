<?php
/**
 * After shop item buttons.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      5.1.0
 */

$product_view = '';
if ( isset( $_SERVER['QUERY_STRING'] ) ) {
	parse_str( sanitize_text_field( wp_unslash( $_SERVER['QUERY_STRING'] ) ), $params );

	if ( isset( $params['product_view'] ) || Avada()->settings->get( 'woocommerce_product_view' ) ) {
		$product_view = ( isset( $params['product_view'] ) ) ? $params['product_view'] : Avada()->settings->get( 'woocommerce_product_view' );
	}
}
?>

<?php if ( ( 'list' === $product_view && ! is_product() ) || 'classic' === Avada()->settings->get( 'woocommerce_product_box_design' ) ) : ?>
	</div>
	</div>
<?php endif; ?>

<?php if ( 'classic' === Avada()->settings->get( 'woocommerce_product_box_design' ) ) : ?>
	</div> <?php // fusion-product-content container. ?>
<?php endif; ?>
