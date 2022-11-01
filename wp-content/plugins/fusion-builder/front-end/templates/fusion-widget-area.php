<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_widget_area-shortcode">
<div {{{ _.fusionGetAttributes( attr ) }}}>
	<# if ( 'undefined' !== typeof styles ) { #>
	<style type="text/css">{{{ styles }}}</style>
	<# } #>

	<div class="fusion-additional-widget-content">
		<# if ( widgetArea ) { #>
		{{{ widgetArea }}}
		<# } #>
	</div>
</div>
</script>
