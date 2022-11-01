<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_fontawesome-shortcode">
<#
var hasAlign = false,
	alignVal = { lg: '', md: '', sm: '' },
	alignmentClasses = [];

if ( alignment ) {
	alignVal['lg'] = alignment;
	alignVal['md'] = alignment;
	alignVal['sm'] = alignment;
}
if ( alignment_medium ) {
	alignVal['md'] = alignment_medium;
	alignVal['sm'] = alignment_medium;
}
if ( alignment_small ) {
	alignVal['sm'] = alignment_small;
}

if ( alignment || alignment_medium || alignment_small ) {
	hasAlign = true;
	_.each( alignVal, function( val, key ) {
		if ( alignVal[ key ] ) {
			alignmentClasses.push( key + '-text-align-' + val );
		}
	} );
}
#>
<# if ( hasAlign ) { #>
<div class="{{ alignmentClasses.join( ' ' ) }}">
<# } #>
<# if ( hasLink ) { #>
<a {{{ _.fusionGetAttributes( attr ) }}}>{{{ FusionPageBuilderApp.renderContent( output, cid, false ) }}}</a>
<# } else { #>
<i {{{ _.fusionGetAttributes( attr ) }}}>{{{ FusionPageBuilderApp.renderContent( output, cid, false ) }}}</i>
<# } #>
<# if ( hasAlign ) { #>
</div>
<# } #>
{{{ styleBlock }}}
</script>
