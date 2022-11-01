<?php
/**
 * Underscore.js template.
 *
 * @package fusion-builder
 */

?>
<script type="text/template" id="fusion-builder-block-module-tagline-preview-template">
	<# 	var title = params.title;
		try {
			if ( FusionPageBuilderApp.base64Encode( FusionPageBuilderApp.base64Decode( title ) ) === title ) {
				title = FusionPageBuilderApp.base64Decode( title );
			}
		} catch ( error ) {
			console.warn( error );
		}
	#>
	<h4 class="fusion_module_title"><span class="fusion-module-icon {{ fusionAllElements[element_type].icon }}"></span>{{ fusionAllElements[element_type].name }}</h4>

	{{ title }}
</script>
