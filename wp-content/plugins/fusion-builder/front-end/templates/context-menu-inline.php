<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/template" id="fusion-builder-context-menu-inline">
	<ul>
		<li data-action="remove-style" aria-label="{{ fusionBuilderText.inline_element_remove }}"><span class="fusiona-undo"></span><span class="fusion-element-tooltip"><span class="fusion-tooltip-text"><?php esc_html_e( 'Reset To Default', 'fusion-builder' ); ?></span></span></li>
		<li data-action="edit" aria-label="{{ fusionBuilderText.inline_element_edit }}"><span class="fusiona-pen"></span><span class="fusion-element-tooltip"><span class="fusion-tooltip-text"><?php esc_html_e( 'Element Options', 'fusion-builder' ); ?></span></span></li>
		<li data-action="remove-node" aria-label="{{ fusionBuilderText.inline_element_delete }}"><span class="fusiona-trash-o"></span><span class="fusion-element-tooltip"><span class="fusion-tooltip-text"><?php esc_html_e( 'Delete Element', 'fusion-builder' ); ?></span></span></li>
	</ul>
</script>
