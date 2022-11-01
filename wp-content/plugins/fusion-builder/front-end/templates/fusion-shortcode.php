<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_shortcode-shortcode">
<#
if ( 'undefined' !== typeof markup ) {
	output = FusionPageBuilderApp.renderContent( markup.output, cid, false );
} else if ( 'undefined' !== typeof shortcode ) {
	output = FusionPageBuilderApp.renderContent( shortcode, cid, false );
} else {
	output = 'No template and no markup found';
}
#>
{{{ output }}}
</script>
