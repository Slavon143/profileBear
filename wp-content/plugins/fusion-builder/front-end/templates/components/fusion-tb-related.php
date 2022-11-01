<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_tb_related-shortcode">
<section {{{ _.fusionGetAttributes( attr ) }}}>
	{{{ titleElement }}}

	<#
	// If Query Data is set, use it and continue. If not, echo HTML.
	if ( 'undefined' !== typeof query_data && 'undefined' !== typeof query_data.related_items ) {
	#>
	<div {{{ _.fusionGetAttributes( carouselAttrs ) }}}>
		<div class="fusion-carousel-positioner">
			<ul class="fusion-carousel-holder">
				{{{ relatedCarousel }}}
			</ul>

			{{{ carouselNav }}}
		</div>
	</div>
	<#
	} else if ( 'undefined' !== typeof query_data && 'undefined' !== typeof query_data.placeholder ) {
	#>
	{{{ query_data.placeholder }}}
	<# } #>

</section>
</script>
