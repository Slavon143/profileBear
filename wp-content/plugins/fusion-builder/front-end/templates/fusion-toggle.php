<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_accordion-shortcode">
<# if ( '' !== styles ) { #>
	<style type="text/css">{{{ styles }}}</style>
<# } #>
<div {{{ _.fusionGetAttributes( toggleShortcode ) }}}>
	<div {{{ _.fusionGetAttributes( toggleShortcodePanelGroup ) }}}></div>
</div>
</script>
<script type="text/html" id="tmpl-fusion_toggle-shortcode">
	<# if ( '' !== childStyles ) { #>
		<style type="text/css">{{{ childStyles }}}</style>
	<# } #>
<div class="panel-heading">
	<{{titleTag}} class="panel-title toggle">
		<a {{{ _.fusionGetAttributes( toggleShortcodeDataToggle ) }}}>
			<span class="fusion-toggle-icon-wrapper" aria-hidden="true">
				<i class="fa-fusion-box active-icon {{activeIcon}}" aria-hidden="true"></i>
				<i class="fa-fusion-box inactive-icon {{inActiveIcon}}" aria-hidden="true"></i>
			</span>
			<span {{{ _.fusionGetAttributes( headingAttr ) }}}>
				{{{ title }}}
			</span>
		</a>
	</{{titleTag}}>
</div>
<div {{{ _.fusionGetAttributes( toggleShortcodeCollapse ) }}}>
	<div {{{ _.fusionGetAttributes( contentAttr ) }}}>
		{{{ FusionPageBuilderApp.renderContent( elementContent, cid, false ) }}}
	</div>
</div>
</script>
