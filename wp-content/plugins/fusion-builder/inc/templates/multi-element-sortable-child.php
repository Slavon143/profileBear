<?php
/**
 * An underscore.js template.
 *
 * @package fusion-builder
 */

?>
<script type="text/template" id="fusion-builder-multi-child-sortable">
	<#
	var image,
		imageReplacement = '<span class="fusion-image-placeholder fusiona fusiona-image"></span>',
		extension;

	if ( 'undefined' !== typeof atts.element_type && -1 !== atts.element_type.indexOf( 'image' ) ) {
		image = atts.params.image;
	}

	if ( ! _.isEmpty( image ) )  {
		extension = image.substr( image.lastIndexOf( '.' ) );

		if ( 0 === extension.indexOf( '.' ) ) {
			image = image.replace( /-\d+x\d+\./, '.' );
			image = image.replace( extension, '-66x66' + extension );
		}
	}
	#>
	<span class="multi-element-child-name">
		<# if ( ! _.isEmpty( image ) ) { #>
			<img class="fusion-child-element-image" src="{{ image }}" onerror="this.onerror=null;jQuery( this ).after( '{{ imageReplacement }}' );jQuery( this ).remove();">
		<# } #>
		<span class="fusion-child-name-label">{{ ( ( atts.element_name ) ? atts.element_name : fusionAllElements[atts.element_type].name ) }}</span>
	</span>
	<div class="fusion-builder-controls">
		<a href="#" class="fusion-builder-multi-setting-remove" title="{{ fusionBuilderText.delete_item }}"><span class="fusiona-trash-o"></span></a>
		<a href="#" class="fusion-builder-multi-setting-clone" title="{{ fusionBuilderText.clone_item }}"><span class="fusiona-file-add"></span></a>
		<a href="#" class="fusion-builder-multi-setting-options" title="{{ fusionBuilderText.edit_item }}"><span class="fusiona-pen"></span></a>
	</div>
</script>
