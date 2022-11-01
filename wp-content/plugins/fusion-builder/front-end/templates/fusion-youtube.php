<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_youtube-shortcode">
<div {{{ _.fusionGetAttributes( attr ) }}}>
	<div {{{ _.fusionGetAttributes( attrSrc ) }}}>
		<iframe title="{{ title_attribute }}" src="https://www.youtube.com/embed/{{ id }}?wmode=transparent&autoplay=0{{{ api_params }}}" width="{{ width }}" height="{{ height }}" allowfullscreen allow="autoplay; fullscreen"></iframe>
	</div>
</div>
</script>
