<?php
/**
 * An underscore.js template.
 *
 * @package fusion-builder
 */

?>
<script type="text/template" id="fusion-builder-block-module-template">
	<#
	var cloning_disabled = '';
	if ( 'undefined' !== typeof fusionAllElements[ element_type ].components_per_template && 1 === fusionAllElements[ element_type ].components_per_template ) { 
		cloning_disabled = ' cloning-disabled';
	}
	#>
	<div class="fusion-builder-module-controls-container fusion-builder-module-controls-type-{{ element_type }}{{ cloning_disabled }}">
		<div class="fusion-builder-controls fusion-builder-module-controls">
			<a href="#" class="fusion-builder-settings fusion-builder-module-control" title="{{ fusionBuilderText.element_settings }}"><span class="fusiona-pen"></span></a>
			<a href="#" class="fusion-builder-clone fusion-builder-module-control fusion-builder-clone-module" title="{{ fusionBuilderText.clone_element }}"><span class="fusiona-file-add"></span></a>
			<a href="#" class="fusion-builder-save fusion-builder-module-control fusion-builder-save-module-dialog" title="{{ fusionBuilderText.save_element }}"><span class="fusiona-drive"></span></a>
			<a href="#" class="fusion-builder-remove fusion-builder-module-control" title="{{ fusionBuilderText.delete_element }}"><span class="fusiona-trash-o"></span></a>
		</div>
	</div>
	<# if ( 'undefined' === typeof fusionAllElements[ element_type ].preview ) { #>
		<span class="fusion-builder-module-title">
			<# if ( 'undefined' !== typeof fusionAllElements[ element_type ].icon ) { #>
				<div class="fusion-module-icon {{ fusionAllElements[ element_type ].icon }}"></div>
			<# } #>
			{{ 'undefined' !== typeof fusionAllElements[ element_type ].name ?  fusionAllElements[ element_type ].name : '' }}
		</span>
	<# } #>
	<div class="fusion-builder-module-preview"></div>
</script>
