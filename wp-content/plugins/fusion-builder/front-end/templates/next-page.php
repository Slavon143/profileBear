<?php
/**
 * An underscore.js template.
 *
 * @package fusion-builder
 */

?>

<script type="text/template" id="fusion-builder-next-page-template">
	<div class="fusion-droppable fusion-droppable-horizontal target-before fusion-container-target"></div>

	<div class="fusion-special-item-controls">
		<div class="fusion-builder-controls">
			<a href="#" class="fusion-builder-delete-special-item fusion-builder-delete-next-page" ><span class="fusiona-trash-o"></span><span class="fusion-element-tooltip"><span class="fusion-tooltip-text">{{ fusionBuilderText.delete_element }}</span></span></a>
			<a href="#" class="fusion-builder-next-page-toggle" ><span class="fusiona-toggle-off"></span><span class="fusiona-toggle-on"></span><span class="fusion-element-tooltip"><span class="fusion-tooltip-text">{{ fusionBuilderText.toggle_element }}</span></span></a>
			<a href="#" class="fusion-builder-special-item-drag" ><span class="fusiona-icon-move"></span><span class="fusion-element-tooltip"><span class="fusion-tooltip-text">{{ fusionBuilderText.drag_element }}</span></span></a>
		</div>
	</div>
	<div class="fusion-builder-special-item-desc"><?php esc_html_e( 'Next Page Separator', 'fusion-builder' ); ?></div>
	<div class="page-links pagination fusion-builder-next-page-pagination">
		<span class="page-links-title"><?php esc_html_e( 'Pages:', 'fusion-builder' ); ?></span>
	</div>

	<div class="fusion-droppable fusion-droppable-horizontal target-after fusion-container-target"></div>
</script>
