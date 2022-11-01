<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_popover-shortcode">
	<# if ( ! inline && ( ! content || '' === content ) ) { #>
		<div class="fusion-builder-placeholder-preview">
			<i class="{{ icon }}" aria-hidden="true"></i> {{ label }}
		</div>
	<# } else { #>
		<span {{{ _.fusionGetAttributes( attr ) }}} data-html-content="{{ popover }}">{{{ styles }}}{{{ FusionPageBuilderApp.renderContent( content, cid, false ) }}}</span>
	<# } #>
</script>
