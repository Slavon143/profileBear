<?php
/**
 * Underscore.js template.
 *
 * @since 2.0
 * @package fusion-library
 */

?>
<#
var fieldId = 'undefined' === typeof param.param_name ? param.id : param.param_name;
#>
<# if ( 'undefined' !== typeof FusionApp ) { #>
	<div class="fusion-upload-area">
		<input type="hidden" id="{{ fieldId }}" name="{{ fieldId }}" class="regular-text fusion-builder-upload-field fusion-builder-upload-field-id" value='{{ option_value }}' />
		<div class="fusion-uploaded-area fusion-builder-upload-preview">
			<img src="" alt="">
			<ul class="fusion-uploded-image-options">
				<li><a class="upload-image-remove" href="JavaScript:void(0);">{{ fusionBuilderText.remove }}</a></li>				
				<li><a class="fusion-builder-upload-button fusion-upload-btn" href="JavaScript:void(0);" data-param="{{ fieldId }}" data-type="image">{{ fusionBuilderText.edit }}</a></li>
			</ul>
		</div>
	</div>
<# } else { #>
	<div class="fusion-upload-image">
		<input type="hidden" id="{{ fieldId }}" name="{{ fieldId }}" class="regular-text fusion-builder-upload-field" value='{{ option_value }}' />
		<div class="preview"></div>
		<input type='button' class='button-upload fusion-builder-upload-button' value='{{ fusionBuilderText.upload_image }}' data-param="{{ fieldId }}" data-type="image" data-title="{{ fusionBuilderText.select_image }}" />
	</div>
<# } #>
