<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 3.5
 */

?>
<script type="text/html" id="tmpl-fusion_news_ticker-shortcode">
	<div {{{ _.fusionGetAttributes( tickerAttr ) }}}>
		{{{ styleTag }}}
		<# if ( ! _.isEmpty( tickerTitle ) ) { #>
			<div {{{ _.fusionGetAttributes( titleAttr ) }}}>
				{{{ tickerTitle }}}
				<# if ( 'triangle' === titleShape ) { #>
					<div class="awb-news-ticker-title-decorator awb-news-ticker-title-decorator-triangle"></div>
				<# } #>
			</div>
		<# } #>
		<div {{{ _.fusionGetAttributes( barAttr ) }}}>
			<div {{{ _.fusionGetAttributes( itemsListAttr ) }}}>{{{ tickerItems }}}</div>
			{{{ carouselButtons }}}
		</div>
	</div>
</script>
