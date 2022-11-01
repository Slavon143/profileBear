<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_events-shortcode">
<#
if ( 'undefined' !== typeof query_data && 'undefined' !== typeof query_data.posts ) {
	#>
	<div {{{ _.fusionGetAttributes( attr ) }}}>
	{{{ eventsList }}}
	{{{ paginationCode }}}
	<#
	// If infinite scroll with "load more" button is used.
	if ( load_more ) {
		#>
		<button class="fusion-load-more-button fusion-events-button fusion-clearfix">{{{ load_more_text }}}</button>
		<#
	}
	#>
	<div class="fusion-clearfix"></div>
	</div>
	<#
} else if ( 'undefined' !== typeof query_data && 'undefined' !== typeof query_data.placeholder ) {
	#>
	{{{ query_data.placeholder }}}
	<#
}
#>
</script>
