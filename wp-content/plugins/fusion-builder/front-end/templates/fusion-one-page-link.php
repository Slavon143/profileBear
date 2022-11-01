<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_one_page_text_link-shortcode">
	<a {{{ _.fusionGetAttributes( onePageTextLinkShortcode ) }}}>
		<# if ( ( elementContent && '' !== elementContent ) || inline ) { #>
			<# if ( ! inline ) { #>
				{{{ FusionPageBuilderApp.renderContent( elementContent, cid, false ) }}}
			<# } else { #>
				{{{ elementContent }}}
			<# } #>
		<# } else { #>
			<div class="fusion-builder-placeholder-preview">
				<i class="{{ icon }}" aria-hidden="true"></i> {{ label }}
			</div>
		<# } #>
	</a>
</script>
