<?php
/**
 * Underscore.js template.
 *
 * @since 2.0
 * @package fusion-library
 */

?>
<# var dataType = 'undefined' === typeof param.data_type ? 'video' : param.data_type; #>

<# if ( 'undefined' !== typeof FusionApp ) { #>
<div class="fusion-upload-file fusion-upload-area" data-mode="file">
	<input id="{{ param.param_name }}" name="{{ param.param_name }}" type="text" class="regular-text fusion-builder-upload-field fusion-url-only-input" value="{{ option_value }}" />
	<a href="JavaScript:void(0);" class="upload-image-remove"><span class="fusiona-close-fb"></span></a>
	<button class='button button-upload fusion-builder-upload-button' data-type="{{ dataType }}" data-title="{{ fusionBuilderText[ 'select_' + dataType ] }}"><i class="fusiona-plus" aria-hidden="true"></i></button>
</div>
<# } else { #>
<div class="fusion-upload-file">
	<input id="{{ param.param_name }}" name="{{ param.param_name }}" type="text" class="regular-text fusion-builder-upload-field" value="{{ option_value }}" />
	<input type='button' class='button button-upload fusion-builder-upload-button' value='{{ fusionBuilderText.upload }}' data-type="{{ dataType }}" data-title="{{ fusionBuilderText[ 'select_' + dataType ] }}"/>
</div>
<# } #>
