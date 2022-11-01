<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_lottie-shortcode">
<div {{{ _.fusionGetAttributes( wrapperAttr ) }}}>
	<{{ tag }} {{{ _.fusionGetAttributes( attr ) }}}></{{ tag }}>
	<# if ( '' !== styleBlock ) { #>
		{{{ styleBlock }}}
	<# } #>
</div>
</script>
