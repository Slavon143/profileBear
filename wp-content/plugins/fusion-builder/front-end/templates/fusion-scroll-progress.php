<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 3.3
 */

?>
<script type="text/html" id="tmpl-fusion_scroll_progress-shortcode">
	<# if ( 'flow' !== position ) { #>
		<div class="fusion-builder-placeholder-preview">
			<i class="{{ icon }}" aria-hidden="true"></i> {{ label }}
		</div>
	<# } #>

	<progress {{{ _.fusionGetAttributes( wrapperAttr ) }}}></progress>
	{{{ styles }}}
</script>
