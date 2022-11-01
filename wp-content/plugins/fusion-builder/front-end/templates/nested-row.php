<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/template" id="fusion-builder-row-inner-template">
	<div class="fusion-droppable fusion-droppable-horizontal target-before fusion-element-target"></div>
	<div class="fusion-builder-module-controls-container fusion-builder-module-controls-type-row fusion-builder-module-controls-type-row-nested">
		<div class="fusion-builder-controls fusion-builder-module-controls">
			<div class="fusion-builder-module-controls-inner">
				<a href="#" class="fusion-builder-row-drag fusion-builder-module-control fusion-builder-element-drag"><span class="fusiona-icon-move"></span><span class="fusion-element-tooltip"><span class="fusion-tooltip-text"><?php esc_html_e( 'Drag Element', 'fusion-builder' ); ?></span></span></a>
				<a href="#" class="fusion-builder-row-clone fusion-builder-module-control"><span class="fusiona-file-add"></span><span class="fusion-element-tooltip"><span class="fusion-tooltip-text"><?php esc_html_e( 'Clone Element', 'fusion-builder' ); ?></span></span></a>
				<a href="#" class="fusion-builder-row-settings fusion-builder-module-control"><span class="fusiona-pen"></span><span class="fusion-element-tooltip"><span class="fusion-tooltip-text"><?php esc_html_e( 'Open Nested Columns', 'fusion-builder' ); ?></span></span></a>
				<a href="#" class="fusion-builder-add-element fusion-builder-module-control"><span class="fusiona-plus"></span><span class="fusion-column-tooltip"><span class="fusion-tooltip-text"><?php esc_html_e( 'Add Element Below', 'fusion-builder' ); ?></span></span></a>
				<a href="#" class="fusion-builder-row-save fusion-builder-module-control" data-focus="#fusion-builder-save-element-input" data-target="#fusion-builder-layouts-elements"><span class="fusiona-drive"></span><span class="fusion-element-tooltip"><span class="fusion-tooltip-text"><?php esc_html_e( 'Save Element', 'fusion-builder' ); ?></span></span></a>
				<a href="#" class="fusion-builder-row-remove fusion-builder-module-control"><span class="fusiona-trash-o"></span><span class="fusion-element-tooltip"><span class="fusion-tooltip-text"><?php esc_html_e( 'Delete Element', 'fusion-builder' ); ?></span></span></a>
			</div>
			<# if ( 'undefined' !== typeof params && 'undefined' !== typeof params.fusion_global) { #>
				<a href="#" class="fusion-builder-element-global fusion-builder-module-control fusion-builder-unglobal-tooltip" data-cid={{id}}><span class="fusiona-globe"></span><span class="fusion-element-tooltip"><span class="fusion-tooltip-text">{{{ fusionBuilderText.global_element }}}</span></span></a>
			<# } #>
		</div>
	</div>
	<div class="fusion-builder-editing-icons">
		<a href="#" class="fusion-builder-cancel-row fusion-builder-module-control"><span class="fusiona-close-fb"></span><span class="fusion-element-tooltip"><span class="fusion-tooltip-text"><?php esc_html_e( 'Cancel Changes', 'fusion-builder' ); ?></span></span></a>
		<a href="#" class="fusion-builder-stop-editing fusion-builder-module-control"><span class="fusiona-check"></span><span class="fusion-element-tooltip"><span class="fusion-tooltip-text"><?php esc_html_e( 'Finished', 'fusion-builder' ); ?></span></span></a>
	</div>
	<div class="fusion-builder-row-content fusion-builder-data-cid" data-cid="{{ id }}">
		<div class="fusion-builder-nested-columns-settings">
			<div class="fusion-builder-modal-top-container">
				<h4>{{ title }}</h4>
				<div class="fusion-builder-inner-row-close-icon fusion-builder-modal-close fusiona-plus2"></div>
			</div>

			<div class="fusion-builder-modal-bottom-container">
				<a href="#" class="fusion-builder-insert-inner-column fusion-builder-module-control"><span class="fusiona-plus"></span> {{ fusionBuilderText.columns }}</a>
				<a href="#" class="fusion-builder-inner-row-close fusion-builder-module-control"><span>{{ fusionBuilderText.cancel }}</span></a>
				<a href="#" class="fusion-builder-modal-save fusion-builder-module-control"><span>{{ fusionBuilderText.save }}</span></a>
			</div>
			<div {{{ _.fusionGetAttributes( attr ) }}}>
				<span class="fusion-builder-empty-container">
					<div class="fusion-builder-module-controls-container">
						<div class="fusion-builder-controls fusion-builder-module-controls">
							<a href="#" class="fusion-builder-insert-inner-column fusion-builder-module-control"><span class="fusiona-add-columns"></span><span class="fusion-column-tooltip"><span class="fusion-tooltip-text"><?php esc_html_e( 'Add Columns', 'fusion-builder' ); ?></span></span></a></span>
						</div>
					</div>
					<div class="fusion-builder-empty-container-label"> <?php esc_html_e( 'To Add Elements, You Must First Add a Column', 'fusion-builder' ); ?></div>
					<div class="fusion-droppable fusion-droppable-horizontal target-replace fusion-nested-column-target"></div>
				</span>
			</div>
		</div>
		<div class="fusion-builder-nested-columns-settings-overlay"></div>
	</div>
	<div class="fusion-clearfix"></div>
	<div class="fusion-droppable fusion-droppable-horizontal target-after fusion-element-target"></div>
</script>
