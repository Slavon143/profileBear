<?php
/**
 * Quick View Container.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      6.1
 */

?>
<div class="fusion-woocommerce-quick-view-overlay"></div>
<div class="fusion-woocommerce-quick-view-container quick-view woocommerce">
	<script type="text/javascript">
		var quickViewNonce =  '<?php echo esc_html( wp_create_nonce( 'fusion_quick_view_nonce' ) ); ?>';
	</script>

	<div class="fusion-wqv-close">
		<button type="button"><span class="screen-reader-text"><?php esc_html_e( 'Close product quick view', 'Avada' ); ?></span>&times;</button>
	</div>

	<div class="fusion-wqv-loader product">
		<h2 class="product_title entry-title"><?php esc_html_e( 'Title', 'Avada' ); ?></h2>
		<div class="fusion-price-rating">
			<div class="price"></div>
			<div class="star-rating"></div>
		</div>
		<div class="fusion-slider-loading"></div>
	</div>

	<div class="fusion-wqv-preview-image"></div>

	<div class="fusion-wqv-content">
		<div class="product">
			<div class="woocommerce-product-gallery"></div>

			<div class="summary entry-summary scrollable">
				<div class="summary-content"></div>
			</div>
		</div>
	</div>
</div>
