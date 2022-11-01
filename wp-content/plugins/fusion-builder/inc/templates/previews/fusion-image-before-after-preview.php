<?php
/**
 * Underscore.js template.
 *
 * @package fusion-builder
 */

?>
<script type="text/template" id="fusion-builder-block-module-image-before-after-preview-template">
	<h4 class="fusion_module_title"><span class="fusion-module-icon {{ fusionAllElements[element_type].icon }}"></span>{{ fusionAllElements[element_type].name }}</h4>
	<div>
		<# if ( params.before_image !== '' ) { #>
			<img src="{{ params.before_image }}" class="fusion-slide-preview fusion-before-after-preview" />
		<# } #>
		<# if ( params.after_image !== '' ) { #>
			<img src="{{ params.after_image }}" class="fusion-slide-preview fusion-before-after-preview" />
		<# } #>
	</div>
</script>
