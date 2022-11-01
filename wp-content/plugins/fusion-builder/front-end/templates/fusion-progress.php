<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_progress-shortcode">
<#
var value = '',
		text = '',
		text_wrapper = '',
		bar = '';

text = '<span ' + _.fusionGetAttributes( attrEditor ) + '>' + FusionPageBuilderApp.renderContent( values.element_content, cid, false ) + '</span>';

if ( 'yes' == values.show_percentage ) {
	value = '<span ' + _.fusionGetAttributes( 'fusion-progressbar-value' ) + '>' + percentage + values.unit + '</span>';
}

text_wrapper = '<span ' + _.fusionGetAttributes( attrSpan ) + '>' + text + ' ' + value + '</span>';

bar = '<div ' + _.fusionGetAttributes( attrBar ) + '><div ' + _.fusionGetAttributes( attrContent ) + '></div></div>';
#>

<# if ( 'above_bar' === values.text_position ) { #>
	<div {{{ _.fusionGetAttributes( attr ) }}}>{{{ text_wrapper }}} {{{ bar }}}</div>
<# } else { #>
	<div {{{ _.fusionGetAttributes( attr ) }}}>{{{ bar }}} {{{ text_wrapper }}}</div>
<# } #>
</script>
