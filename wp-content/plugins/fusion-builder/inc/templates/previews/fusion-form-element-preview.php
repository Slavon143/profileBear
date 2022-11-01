<?php
/**
 * Underscore.js template.
 *
 * @package fusion-builder
 */

?>
<script type="text/template" id="fusion-builder-block-module-form-element-preview-template">
	<#
		hasLogic = 'undefined' !== typeof params.logics && '' !== params.logics && '[]' !== FusionPageBuilderApp.base64Decode( params.logics ) ? true : false;
	#>
	<h4 class="fusion_module_title"><span class="fusion-module-icon {{ fusionAllElements[element_type].icon }}"></span>{{ fusionAllElements[element_type].name }}</h4>
	<span>{{ params.label }}</span>
	<# if ( hasLogic ) { #>
		<span> {<?php echo esc_html__( 'Conditional Logic', 'fusion-builder' ); ?>}</span>
	<# } #>
</script>
