<?php
/**
 * Underscore.js template.
 *
 * @package fusion-builder
 */

?>
<script type="text/template" id="fusion-builder-block-module-alert-preview-template">

	<#
	var icon_type       = '',
		content         = params.element_content,
		text_block      = jQuery.parseHTML( content ),
		element_content = jQuery(text_block).text();

	if ( params.type == 'general' ) {
		icon_type = 'fa-info-circle';
	}
	if ( params.type == 'error' ) {
		icon_type = 'fa-exclamation-triangle';
	}
	if ( params.type == 'success' ) {
		icon_type = 'fa-check-circle';
	}
	if ( params.type == 'notice' ) {
		icon_type = 'fa fa-lg fa-cog';
	}
	if ( params.type == 'custom' ) {
		icon_type = params.icon;
	}

	if ( 'undefined' !== typeof icon_type && -1 === icon_type.trim().indexOf( ' ' ) ) {
		icon_type = 'fa ' + icon_type;
	}
	#>

	<span class="fusion-module-icon fa-lg {{ icon_type }}"></span> {{{ element_content }}}

</script>
