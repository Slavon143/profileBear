<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_tb_content-shortcode">
	<div {{{ _.fusionGetAttributes( wrapperAttr ) }}}>
		{{{ FusionPageBuilderApp.renderContent( output, cid, false ) }}}
		<# if ( 'string' === typeof styles ) { #>
		{{{ styles }}}
		<# } #>
	</div>
</script>
