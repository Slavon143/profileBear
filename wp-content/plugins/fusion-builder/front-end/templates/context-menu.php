<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/template" id="fusion-builder-context-menu">
	<#
	var label         = fusionBuilderText.element,
		elFocus       = '#fusion-builder-save-element-input',
		target        = '#fusion-builder-layouts-elements',
		targetType    = FusionPageBuilderApp.getElementType( element_type ),
		canSave       = canEdit = canRemove = canClone = canCopy = pasteSame = true,
		canInvert     = false,
		clipboard     = 'undefined' !== typeof data.type ? FusionPageBuilderApp.getElementType( data.type ) : false,
		nestedEditing = jQuery( 'body' ).hasClass( 'nested-ui-active' ),
		pasteChild,
		pageType      = 'undefined' !== typeof FusionApp.data.fusion_element_type ? FusionApp.data.fusion_element_type : 'default';

	// Check targeted element context.
	switch ( targetType ) {
		case 'fusion_builder_container' :
			label = fusionBuilderText.full_width_section;
			target = '#fusion-builder-layouts-sections';

			// If clipboard has column, it can be added to container.
			pasteChild = 'fusion_builder_column' === clipboard;
			canRemove  = canSave = canClone = pasteSame = canCopy = canPaste = 'sections' !== pageType;
			canInvert  = 'undefined' !== typeof params.background_color && -1 !== params.background_color.indexOf( 'var(--awb-' ) ? true : canInvert;

			break;

		case 'fusion_builder_column' :
			label = fusionBuilderText.column;
			target = '#fusion-builder-layouts-columns';

			// If clipboard is container then allow paste to same.
			pasteChild = 'element' === clipboard || 'parent_element' === clipboard || 'fusion_builder_row_inner' === clipboard;
			canRemove  = canSave = canClone = pasteSame = canCopy = canPaste = 'columns' !== pageType && 'post_cards' !== pageType;

			break;

		case 'element' :
			label = fusionAllElements[ element_type ].name;

			// Regular element has no children.
			pasteChild = false;
			canRemove  = canSave = canClone = pasteSame = canCopy = canPaste = 'elements' !== pageType;

			// Theme builder components can't be copied or cloned.
			if ( 'fusion_tb_woo_notices' !== element_type && -1 !== element_type.indexOf( 'fusion_tb_' ) ) {
				canCopy = canClone = false;
			}

			break;

		case 'parent_element' :
			label = fusionAllElements[ element_type ].name;

			// If its a child element and the correct child element, allow paste.
			pasteChild = 'child_element' === clipboard && fusionMultiElements[ element_type ] === data.type;
			canRemove  = canSave = canClone = pasteSame = canCopy = canPaste = 'elements' !== pageType;
			break;

		case 'fusion_builder_row_inner' :
			label     = fusionAllElements[ element_type ].name;

			// If its a child element and the correct child element, allow paste.
			pasteChild = 'fusion_builder_column_inner' === clipboard && nestedEditing;
			canRemove = canEdit = canClone = pasteSame = ! nestedEditing;
			// canRemove  = canSave = canClone = canCopy = canPaste = 'elements' !== pageType;
			break;

		case 'fusion_builder_column_inner' :
			label   = fusionAllElements[ element_type ].name;
			canSave = false;

			// Child element has no children.
			pasteChild = 'element' === clipboard || 'parent_element' === clipboard;

			break;

		case 'child_element' :
			label   = fusionAllElements[ element_type ].name;
			canSave = false;

			// Child element has no children.
			pasteChild = false;

			break;
	}

	// For paste before/after parent and regular elements are the same.
	targetType = 'parent_element' === targetType || 'fusion_builder_row_inner' === targetType ? 'element' : targetType;
	clipboard  = 'parent_element' === clipboard || ( 'fusion_builder_row_inner' === clipboard && ! nestedEditing ) ? 'element' : clipboard;

	targetType = 'child_element' === targetType ? element_type : targetType;
	clipboard  = 'child_element' === clipboard ? data.type : clipboard;

	if ( false === FusionApp.data.fusion_element_type ) {
		// Check if can be pasted before and after.
		pasteSame = targetType === clipboard;
	}

	#>
	<span data-element-type="{{ element_type }}">{{ label }}</span>
	<ul>
		<# if ( canEdit ) { #>
			<li data-action="edit"><?php esc_html_e( 'Edit', 'fusion-builder' ); ?></li>
		<# } #>
		<# if ( canSave ) { #>
			<li data-action="save" data-focus="{{ elFocus }}" data-target="{{ target }}"><?php esc_html_e( 'Save', 'fusion-builder' ); ?></li>
		<# } #>
		<# if ( canClone ) { #>
			<li data-action="clone"><?php esc_html_e( 'Clone', 'fusion-builder' ); ?></li>
		<# } #>
		<# if ( canRemove ) { #>
			<li data-action="remove"><?php esc_html_e( 'Remove', 'fusion-builder' ); ?></li>
		<# } #>
		<# if ( canCopy ) { #>
			<li data-action="copy"><?php esc_html_e( 'Copy', 'fusion-builder' ); ?></li>
		<# } #>

		<# if ( pasteSame ) { #>
			<li data-action="paste-before"><?php esc_html_e( 'Paste Before', 'fusion-builder' ); ?></li>
			<li data-action="paste-after"><?php esc_html_e( 'Paste After', 'fusion-builder' ); ?></li>
		<# } #>

		<# if ( pasteChild ) { #>
			<li data-action="paste-start"><?php esc_html_e( 'Paste At Start', 'fusion-builder' ); ?></li>
			<li data-action="paste-end"><?php esc_html_e( 'Paste At End', 'fusion-builder' ); ?></li>
		<# } #>

		<# if ( canInvert ) { #>
			<li data-action="invert"><?php esc_html_e( 'Invert', 'fusion-builder' ); ?></li>
		<# } #>
	</ul>
</script>
