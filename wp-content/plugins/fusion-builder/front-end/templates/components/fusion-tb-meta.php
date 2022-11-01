<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.4
 */

?>
<script type="text/html" id="tmpl-fusion_tb_meta-shortcode">
	{{{styles}}}
	<div {{{ _.fusionGetAttributes( wrapperAttr ) }}}>
		{{{ FusionPageBuilderApp.renderContent( output, cid, false ) }}}
	</div>
</script>
