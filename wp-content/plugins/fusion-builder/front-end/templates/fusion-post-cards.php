<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 3.3
 */

?>
<script type="text/html" id="tmpl-fusion_post_cards-shortcode">
	{{{styles}}}
	<div {{{ _.fusionGetAttributes( attr ) }}}>
	<# if ( 'no' !== values.filters && ( 'grid' === values.layout || 'masonry' === values.layout ) && 'posts' === values.source ) { #>
		{{{filters}}}
	<# } #>
	<#
	// If Query Data is set, use it and continue. If not, echo HTML.
	if ( 'undefined' !== typeof query_data && 'undefined' !== typeof query_data.loop_product && query_data.loop_product ) {
	#>
		<# if ( 'carousel' === values.layout ) { #>
			<div class="fusion-carousel-positioner">
				<ul {{{ _.fusionGetAttributes( productsAttrs ) }}}>
					{{{ productsLoop }}}
				</ul>

				<# if ( 'yes' === values.show_nav ) { #>
					{{{ carouselNav }}}
				<# } #>
			</div>
		<# } else { #>
			<ul {{{ _.fusionGetAttributes( productsAttrs ) }}}>
				{{{ productsLoop }}}
			</ul>
		<# } #>

		<# if ( 'no' !== values.scrolling && 'terms' !== values.source && ( 'grid' === values.layout || 'masonry' === values.layout ) ) { #>
			{{{ pagination }}}
		<# } #>

		<# if ( 'load_more_button' === values.scrolling && -1 !== values.number_posts && 'terms' !== values.source && ( 'grid' === values.layout || 'masonry' === values.layout ) ) { #>
			<button class="fusion-load-more-button fusion-product-button fusion-clearfix">{{{ loadMoreText }}}</button>
		<# } #>

	<#
	} else if ( 'undefined' !== typeof query_data && 'undefined' !== typeof query_data.placeholder ) {
	#>
		{{{ query_data.placeholder }}}
	<# } #>
	</div>
</script>
