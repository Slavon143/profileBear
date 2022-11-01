<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_button-shortcode">
<#
var iconHTML = '';
if ( values.icon ) {
	iconHTML = '<i' + _.fusionGetAttributes( IconAttr ) + '></i>';
	if ( 'yes' === values.icon_divider ) {
		iconHTML = '<span class="' + 'fusion-button-icon-divider button-icon-divider-' + values.icon_position + '">' + iconHTML + '</span>';
	}
}

buttonText   = '<span' + _.fusionGetAttributes( textAttr ) + '>' + values.element_content + '</span>';
innerContent = ( 'left' === values.icon_position ) ? iconHTML + buttonText : buttonText + iconHTML;
#>

<div {{{ _.fusionGetAttributes( wrapperAttr ) }}}>
	{{{ buttonStyles }}}
	<# if ( 'undefined' !== typeof values.button_el_type && 'submit' === values.button_el_type ) { #>
		<button {{{ _.fusionGetAttributes( attr ) }}} >
			{{{ innerContent }}}
		</button>
	<# } else { #>
		<a {{{ _.fusionGetAttributes( attr ) }}} >
			{{{ innerContent }}}
		</a>
	<# } #>
</div>
</script>
