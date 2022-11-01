<?php
/**
 * Underscore.js template.
 *
 * @package fusion-builder
 */

?>
<script type="text/template" id="fusion-builder-block-module-widget-preview-template">
	<#
	var widget_name = '';

	if ( 'undefined' !== typeof params.type && '' !== params.type ) {

		if ( 'undefined' !== typeof fusionAllElements.fusion_widget ) {
			if ( 'undefined' !== typeof fusionAllElements.fusion_widget.params.type.value[params.type] ) {
				widget_name = fusionAllElements.fusion_widget.params.type.value[params.type];
			}
		}
	}
	#>

	<# if ( '' !== widget_name ) { #>
		<h4 class="fusion_module_title"><span class="fusion-module-icon {{ fusionAllElements[element_type].icon }}"></span>{{ fusionAllElements[element_type].name }}</h4>
		{{{ widget_name }}}
	<# } else { #>
		{{{ fusionBuilderText.select_widget }}}
	<# } #>
</script>
