<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_modal-shortcode">
<div class="fusion-builder-placeholder-preview">
	<i class="{{ icon }}" aria-hidden="true"></i> {{ label }} ({{ name }})
</div>
<#
var style = '';
if ( '' !== borderColor ) {
	style = '<style type="text/css">.modal-' + cid + ' .modal-header, .modal-' + cid + ' .modal-footer{border-color:' + borderColor + ';}</style>';
}
#>
<div {{{ _.fusionGetAttributes( attrModal ) }}}>
	{{{ style }}}
	<div {{{ _.fusionGetAttributes( attrDialog ) }}}>
		<div {{{ _.fusionGetAttributes( attrContent ) }}}>
			<div {{{ _.fusionGetAttributes( 'modal-header' ) }}}>
				<button {{{ _.fusionGetAttributes( attrButton ) }}}>&times;</button>
				<h3 {{{ _.fusionGetAttributes( attrHeading ) }}}>{{{ title }}}</h3>
			</div>
			<div {{{ _.fusionGetAttributes( attrBody ) }}}>
				{{{ FusionPageBuilderApp.renderContent( elementContent, cid, false ) }}}
			</div>
			<# if ( 'yes' === showFooter ) { #>
				<div {{{ _.fusionGetAttributes( 'modal-footer' ) }}}>
					<button {{{ _.fusionGetAttributes( attrFooterButton ) }}}>{{{ closeText }}}</button>
				</div>
			<# } #>
		</div>
	</div>
</div>
</script>
