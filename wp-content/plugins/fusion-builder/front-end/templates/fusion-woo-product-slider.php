<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_products_slider-shortcode">
<# if ( productList ) { #>
	<div {{{ _.fusionGetAttributes( wooProductSliderShortcode ) }}}>
		<div {{{ _.fusionGetAttributes( wooProductSliderShortcodeCarousel ) }}}>
			<div class="fusion-carousel-positioner">
			<ul class="fusion-carousel-holder">
				{{{ productList }}}
			</ul>
			<# if ( 'yes' == showNav ) { #>
				<div class="fusion-carousel-nav"><span class="fusion-nav-prev" aria-label="Previous"></span><span class="fusion-nav-next" aria-label="Next"></span></div>
			<# } #>
			</div>
		</div>
	</div>
<# } else if ( placeholder ) { #>
	{{{ placeholder }}}
<# } #>
</script>
