<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 3.2
 */

?>
<script type="text/html" id="tmpl-fusion_tb_woo_related-shortcode">
{{{styles}}}
<section {{{ _.fusionGetAttributes( attr ) }}}>
{{{ titleElement }}}

<#
// If Query Data is set, use it and continue. If not, echo HTML.
if ( 'undefined' !== typeof query_data && 'undefined' !== typeof query_data.fusion_tb_woo_related && query_data.fusion_tb_woo_related ) {

	if ( 'carousel' === layout ) {
	#>
	<div {{{ _.fusionGetAttributes( carouselAttrs ) }}}>
		<div class="fusion-carousel-positioner">
			<ul {{{ _.fusionGetAttributes( productsAttrs ) }}}>
				{{{ output }}}
			</ul>

			{{{ carouselNav }}}
		</div>
	</div>
	<# } else { #>
		<ul {{{ _.fusionGetAttributes( productsAttrs ) }}}>
			{{{ output }}}
		</ul>
	<# }

} else if ( 'undefined' !== typeof query_data && 'undefined' !== typeof query_data.placeholder ) {
#>
{{{ query_data.placeholder }}}
<# } #>
</section>
</script>
