<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_separator-shortcode">
<# if ( 'absolute' !== values.position ) { #>
	<div class="fusion-sep-clear"></div>
<# } #>
<div {{{ _.fusionGetAttributes( attr ) }}}>
	<div {{{ _.fusionGetAttributes( borderParts ) }}}></div>
	<# if ( '' !== values.icon && 'none' !== values.style_type ) { #>
	<span {{{ _.fusionGetAttributes( iconWrapperAttr ) }}}><i {{{ _.fusionGetAttributes( iconAttr ) }}}></i></span>
	<div {{{ _.fusionGetAttributes( borderParts ) }}}></div>
	<# } #>
</div>

<# if ( ( 'center' !== values.alignment || '' !== values.bottom_margin ) && 'absolute' !== values.position ) { #>
	<div class="fusion-sep-clear"></div>
<# } #>
</script>
