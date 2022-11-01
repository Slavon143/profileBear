<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/template" id="fusion-builder-row-template">

	<div {{{ _.fusionGetAttributes( attr ) }}}>
		<span class="fusion-builder-empty-container">
			<div class="fusion-builder-module-controls-container">
				<div class="fusion-builder-controls fusion-builder-module-controls">
					<a href="#" class="fusion-builder-insert-column fusion-builder-module-control"><span class="fusiona-add-columns"></span><span class="fusion-column-tooltip"><span class="fusion-tooltip-text"><?php esc_html_e( 'Add Columns', 'fusion-builder' ); ?></span></span></a></span>
				</div>
			</div>
			<div class="fusion-builder-empty-container-label"> <?php esc_html_e( 'To add elements, you must first add a column.', 'fusion-builder' ); ?></div>
			<div class="fusion-droppable fusion-droppable-horizontal target-replace fusion-column-target"></div>
		</span>
	</div>
	<a href="#" class="fusion-builder-insert-column fusion-builder-module-control"><span class="open"><span class="fusiona-plus"></span> <?php esc_html_e( 'Add Columns', 'fusion-builder' ); ?></a>
</script>
