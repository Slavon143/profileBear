<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 3.5
 */

?>
<script type="text/html" id="tmpl-fusion_views_counter-shortcode">
	<# if ( ! isDisabled ) { #>
		<div {{{ _.fusionGetAttributes( wrapperAttributes ) }}}>
		{{{ styleTag }}}
		<div {{{ _.fusionGetAttributes( contentAttributes ) }}}>{{{ FusionPageBuilderApp.renderContent( mainContent, cid, false ) }}}</div>
	<# } else { #>
		<div class="fusion-builder-placeholder-preview">
			<i class="fusiona-exclamation" aria-hidden="true"></i> {{ isDisabledText }}
		</div>
	<# } #>
</script>
