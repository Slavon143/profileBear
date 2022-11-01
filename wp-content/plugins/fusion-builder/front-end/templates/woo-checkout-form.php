<?php
/**
 * An underscore.js template.
 *
 * @package fusion-builder
 */

?>
<script type="text/template" id="fusion-checkout-form-template">

	<div class="fusion-droppable fusion-droppable-horizontal target-before fusion-container-target"></div>

	<div class="fusion-special-item-controls">
		<div class="fusion-builder-controls">
			<a href="#" class="fusion-builder-delete-special-item fusion-builder-delete-checkout-form" ><span class="fusiona-trash-o"></span><span class="fusion-element-tooltip"><span class="fusion-tooltip-text">{{ fusionBuilderText.delete_element }}</span></span></a>
			<a href="#" class="fusion-builder-special-item-drag" ><span class="fusiona-icon-move"></span><span class="fusion-element-tooltip"><span class="fusion-tooltip-text">{{ fusionBuilderText.drag_element }}</span></span></a>
		</div>
	</div>
	<div class="fusion-builder-special-item-desc"><?php esc_html_e( 'Checkout Form', 'fusion-builder' ); ?></div>

	<div class="fusion-droppable fusion-droppable-horizontal target-after fusion-container-target"></div>
</script>
