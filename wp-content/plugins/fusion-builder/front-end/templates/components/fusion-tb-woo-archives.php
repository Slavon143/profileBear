<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_tb_woo_archives-shortcode">
{{{styles}}}
<div {{{ _.fusionGetAttributes( attr ) }}}>
<#
// If Query Data is set, use it and continue. If not, echo HTML.
if ( 'undefined' !== typeof query_data && 'undefined' !== typeof query_data.loop_product && query_data.loop_product ) {
#>
	<ul {{{ _.fusionGetAttributes( productsAttrs ) }}}>
		{{{ productsLoop }}}
	</ul>

	<# if ( 'no' !== values.scrolling ) { #>
		<div {{{ _.fusionGetAttributes( paginationAttrs ) }}}>
			{{{ pagination }}}
		</div>
	<# } #>	

	<# if ( 'load_more_button' === values.scrolling && -1 !== values.number_posts ) { #>
		<button class="fusion-load-more-button fusion-product-button fusion-clearfix">{{{ loadMoreText }}}</button>
	<# } #>

<#
} else if ( 'undefined' !== typeof query_data && 'undefined' !== typeof query_data.placeholder ) {
#>
{{{ query_data.placeholder }}}
<# } #>
</div>
</script>
