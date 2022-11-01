<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_featured_products_slider-shortcode">
	<# if ( product_list ) { #>
		<div {{{ _.fusionGetAttributes( wooFeaturedProductsSliderShortcode ) }}}>
			<div {{{ _.fusionGetAttributes( wooFeaturedProductsSliderShortcodeCarousel ) }}}>
				<div class="fusion-carousel-positioner">
				<ul class="fusion-carousel-holder">
					{{{ product_list }}}
				</ul>
				<# if ( 'yes' === show_nav ) { #>
					<div class="fusion-carousel-nav"><button class="fusion-nav-prev" aria-label="Previous"></button><button class="fusion-nav-next" aria-label="Next"></button></div>
				<# } #>
				</div>
			</div>
		</div>
	<# } else if ( placeholder ) { #>
		{{{ placeholder }}}
	<# } #>
</script>
