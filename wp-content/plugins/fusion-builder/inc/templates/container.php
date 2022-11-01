<?php
/**
 * An underscore.js template.
 *
 * @package fusion-builder
 */

?>
<script type="text/template" id="fusion-builder-container-template">
	<div class="fusion-builder-section-header">
		<#
		var has_bg = false;
		if ( '' !== params.background_image ) {
			has_bg = true;
		}
		#>
		<# section_name = 'undefined' !== typeof params.admin_label ? _.unescape( params.admin_label ) : fusionBuilderText.full_width_section; #>
		<input type="text" class="fusion-builder-section-name" name="" value="{{ section_name }}" />
		<div class="fusion-builder-controls fusion-builder-section-controls">
			<a href="#" class="fusion-builder-settings fusion-builder-settings-container" title="{{ fusionBuilderText.section_settings }}"><span class="fusiona-pen"></span></a>
			<a href="#" class="fusion-builder-clone fusion-builder-clone-container" title="{{ fusionBuilderText.clone_section }}"><span class="fusiona-file-add"></span></a>
			<a href="#" class="fusion-builder-save-element" title="{{ fusionBuilderText.save_section }}"><span class="fusiona-drive"></span></a>
			<a href="#" class="fusion-builder-remove" title="{{ fusionBuilderText.delete_section }}"><span class="fusiona-trash-o"></span></a>
			<a href="#" class="fusion-builder-toggle" title="{{ fusionBuilderText.click_to_toggle }}"><span class="dashicons-before dashicons-arrow-up"></span></a>
		</div>
	</div>
	<div class="fusion-builder-container-content">
		<#
		var extraClasses = '';
		if ( 'object' === typeof values ) {
			if ( 'flex' === values.type ) {
				extraClasses += ' fusion-flex-container';
				extraClasses += ' fusion-flex-align-items-' + values.flex_align_items;
				if ( 'stretch' !== values.align_content ) {
					extraClasses += ' fusion-flex-align-content-' + values.align_content;
				}
				if ( 'flex-start' !== values.flex_justify_content ) {
					extraClasses += ' fusion-flex-justify-content-' + values.flex_justify_content;
				}
			}
		}
		#>
		<div class="fusion-builder-section-content fusion-builder-data-cid{{ extraClasses }}" data-cid="{{ cid }}" data-bg="{{ has_bg }}">
		</div>
		<a href="#" class="fusion-builder-section-add"><span class="fusiona-plus"></span> {{ fusionBuilderText.full_width_section }}</a>
	</div>
	<div class="fusion-builder-container-utility-toolbar">
	</div>
</script>
