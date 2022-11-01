<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_chart-shortcode">
<div {{{ _.fusionGetAttributes( chartShortcode ) }}}>

	<# if ( '' !== title ) { #>
		<h4 class="fusion-chart-title">{{{ title }}}</h4>
	<# } #>

	<div class="fusion-chart-inner">
		<div class="fusion-chart-wrap">
			<canvas></canvas>
		</div>

	<# if ( 'off' !== chartLegendPosition ) { #>
		<div class="fusion-chart-legend-wrap"></div>
	<# } #>

		</div>
	</div>

<# if ( styles ) { #>
	<style type="text/css">{{{ styles }}}</style>
<# } #>
</script>
<script type="text/html" id="tmpl-fusion_chart_dataset-shortcode">
<div {{{ _.fusionGetAttributes( chartDatasetShortcode ) }}}></div>
</script>
