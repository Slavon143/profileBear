<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/template" id="fusion-builder-element-template">
	<div class="fusion-droppable fusion-droppable-horizontal target-before fusion-element-target"></div>
	<div class="fusion-builder-module-controls-container">
		<div class="fusion-builder-controls fusion-builder-module-controls">
			<div class="fusion-builder-module-controls-inner">
				<a href="#" class="fusion-builder-element-drag fusion-builder-module-control"><span class="fusiona-icon-move"></span><span class="fusion-element-tooltip"><span class="fusion-tooltip-text"><?php esc_html_e( 'Drag Element', 'fusion-builder' ); ?></span></span></a>
				<a href="#" class="fusion-builder-clone fusion-builder-module-control"><span class="fusiona-file-add"></span><span class="fusion-element-tooltip"><span class="fusion-tooltip-text"><?php esc_html_e( 'Clone Element', 'fusion-builder' ); ?></span></span></a>
				<a href="#" class="fusion-builder-settings fusion-builder-module-control"><span class="fusiona-pen"></span><span class="fusion-element-tooltip"><span class="fusion-tooltip-text">{{{ editLabel }}}</span></span></a>
				<a href="#" class="fusion-builder-add-element fusion-builder-module-control"><span class="fusiona-plus"></span><span class="fusion-column-tooltip"><span class="fusion-tooltip-text"><?php esc_html_e( 'Add element below', 'fusion-builder' ); ?></span></span></a>
				<a href="#" class="fusion-builder-element-save fusion-builder-module-control" data-focus="#fusion-builder-save-element-input" data-target="#fusion-builder-layouts-elements"><span class="fusiona-drive"></span><span class="fusion-element-tooltip"><span class="fusion-tooltip-text"><?php esc_html_e( 'Save Element', 'fusion-builder' ); ?></span></span></a>
				<a href="#" class="fusion-builder-remove fusion-builder-module-control"><span class="fusiona-trash-o"></span><span class="fusion-element-tooltip"><span class="fusion-tooltip-text"><?php esc_html_e( 'Delete Element', 'fusion-builder' ); ?></span></a>
			</div>
			<# if ( 'undefined' !== typeof params && 'undefined' !== typeof params.fusion_global) { #>
				<a href="#" class="fusion-builder-element-global fusion-builder-module-control fusion-builder-unglobal-tooltip" data-cid={{cid}}><span class="fusiona-globe"></span><span class="fusion-element-tooltip"><span class="fusion-tooltip-text">{{{ fusionBuilderText.global_element }}}</span></span></a>
			<# } #>
		</div>
	</div>

	<div class="fusion-builder-element-content"></div>
	<div class="fusion-droppable fusion-droppable-horizontal target-after fusion-element-target"></div>
</script>
