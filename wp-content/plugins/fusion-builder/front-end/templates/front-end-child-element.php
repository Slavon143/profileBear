<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/template" id="fusion-builder-child-element-template">
	<# if ( 'undefined' === typeof show_ui || false !== show_ui ) { #>
		<div class="fusion-builder-module-controls-container">
			<div class="fusion-builder-controls fusion-builder-module-controls fusion-builder-child-controls">
				<# if ( 'undefined' !== typeof sortable && sortable ) { #>
					<a href="#" class="fusion-builder-element-drag fusion-builder-module-control"><span class="fusiona-icon-move"></span><span class="fusion-element-tooltip"><span class="fusion-tooltip-text"><?php esc_html_e( 'Drag Element', 'fusion-builder' ); ?></span></span></a>
				<# } #>

				<a href="#" class="fusion-builder-remove-child fusion-builder-module-control"><span class="fusiona-trash-o"></span><span class="fusion-element-tooltip"><span class="fusion-tooltip-text"><?php esc_html_e( 'Delete Element', 'fusion-builder' ); ?></span></span></a>
				<a href="#" class="fusion-builder-clone-child fusion-builder-module-control"><span class="fusiona-file-add"></span><span class="fusion-element-tooltip"><span class="fusion-tooltip-text"><?php esc_html_e( 'Clone Element', 'fusion-builder' ); ?></span></span></a>
				<a href="#" class="fusion-builder-settings-child fusion-builder-module-control"><span class="fusiona-pen"></span><span class="fusion-element-tooltip"><span class="fusion-tooltip-text">{{{ editLabel }}}</span></span></a>
				<a href="#" class="fusion-builder-add-child fusion-builder-module-control"><span class="fusiona-plus"></span><span class="fusion-element-tooltip"><span class="fusion-tooltip-text"><?php esc_html_e( 'Add Child Element', 'fusion-builder' ); ?></span></span></a>
			</div>
		</div>
	<# } #>
	<div class="fusion-builder-child-element-content"></div>
</script>
