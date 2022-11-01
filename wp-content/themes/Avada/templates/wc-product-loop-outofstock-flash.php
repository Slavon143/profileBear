<?php
/**
 * Out of stock flash template
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      5.1.0
 */

global $product;
?>

<?php if ( ! $product->is_in_stock() ) : ?>
	<div class="fusion-out-of-stock">
		<div class="fusion-position-text">
			<?php
				$outofstock_label = Avada()->settings->get( 'woo_outofstock_badge_text' );

			if ( '' === $outofstock_label ) {
				$outofstock_label = __( 'Out of stock', 'Avada' );
			}

				echo esc_html( $outofstock_label );
			?>
		</div>
	</div>
<?php endif; ?>
