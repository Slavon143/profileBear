<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_text-shortcode">
	<div {{{ _.fusionGetAttributes( attr ) }}}>
		{{{ FusionPageBuilderApp.renderContent( output, cid, false ) }}}
	</div>
</script>
