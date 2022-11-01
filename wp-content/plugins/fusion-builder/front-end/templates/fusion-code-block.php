<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_code-shortcode">
	<#
	if ( FusionPageBuilderApp.base64Encode( FusionPageBuilderApp.base64Decode( content ) ) === content ) {
		content = FusionPageBuilderApp.base64Decode( content );
	}
	content = _.unescape( content );
	#>

	<# if ( -1 !== content.indexOf( '<script' ) || -1 !== content.indexOf( '<style' ) || '' === content ) { #>
		<div class="fusion-builder-placeholder-preview">
			<i class="{{ icon }}" aria-hidden="true"></i> {{ label }}
		</div>
	<# } #>
	<# if ( -1 === content.indexOf( '<script' ) ) { #>
		{{{ FusionPageBuilderApp.renderContent( content, cid, false ) }}}
	<# } #>
</script>
