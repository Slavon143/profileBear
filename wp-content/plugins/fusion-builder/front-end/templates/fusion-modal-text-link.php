<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_modal_text_link-shortcode">
	<# if ( ( output && '' !== output ) || inline ) { #>
		<# if ( ! inline ) { #>
			<a {{{ _.fusionGetAttributes( modalTextShortcode ) }}}>{{{ FusionPageBuilderApp.renderContent( output, cid, false ) }}}</a>
		<# } else { #>
			<a {{{ _.fusionGetAttributes( modalTextShortcode ) }}}>{{{ output }}}</a>
		<# } #>
	<# } else { #>
		<div class="fusion-builder-placeholder-preview">
			<i class="{{ icon }}" aria-hidden="true"></i> {{ label }} ({{ name }})
		</div>
	<# } #>
</script>
