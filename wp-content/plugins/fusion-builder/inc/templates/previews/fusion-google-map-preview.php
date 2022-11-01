<?php
/**
 * Underscore.js template.
 *
 * @package fusion-builder
 */

?>
<script type="text/template" id="fusion-builder-block-module-google-map-preview-template">
	<h4 class="fusion_module_title"><span class="fusion-module-icon {{ fusionAllElements[element_type].icon }}"></span>{{ fusionAllElements[element_type].name }}</h4>
	<# if ( 'embed' === params.api_type ) { #>
		{{ params.embed_address }}
	<# } else { #>
		{{ params.address }}
	<# } #>
	{{ fusionAllElements[element_type].params.api_type.value[ params.api_type ] }}
	<# if ( '' === params.api_type ) { #>
		API
	<# } #>

</script>
