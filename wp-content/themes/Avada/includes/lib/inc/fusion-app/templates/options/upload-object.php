<?php
/**
 * Underscore.js template.
 *
 * @since 2.0
 * @package fusion-library
 */

?>
<#
	var mode = 'undefined' !== typeof param.preview && false === param.preview ? 'file' : 'media',
		url  = 'undefined' !== typeof option_value && 'undefined' !== typeof option_value.url ? option_value.url : '';
#>
<div class="fusion-upload-area" data-mode="{{ mode }}">
	<input type="hidden" id="{{ param.id }}" name="{{ param.id }}" class="regular-text fusion-builder-upload-field fusion-image-as-object" value='{{ JSON.stringify( option_value ) }}' />

	<# if ( 'media' === mode ) { #>
		<div class="fusion-uploaded-area fusion-builder-upload-preview">
			<img src="" alt="">
			<ul class="fusion-uploded-image-options">
				<li><a class="upload-image-remove" href="JavaScript:void(0);">{{ fusionBuilderText.remove }}</a></li>
				<li><a class="fusion-builder-upload-button fusion-upload-btn" href="JavaScript:void(0);" data-type="image">{{ fusionBuilderText.edit }}</a></li>
			</ul>
		</div>
	<# } else { #>
		<input type="text" id="{{ param.id }}_url" name="{{ param.id }}[url]" class="regular-text fusion-dont-update fusion-url-only-input" value='{{ url }}' />
		<a href="JavaScript:void(0);" class="upload-image-remove"><span class="fusiona-close-fb"></span></a>
		<a class="fusion-builder-upload-button fusion-upload-btn" href="JavaScript:void(0);"><span class="fusiona-plus"></span></a>
	<# } #>
</div>
