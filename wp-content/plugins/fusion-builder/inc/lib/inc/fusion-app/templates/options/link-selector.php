<?php
/**
 * Underscore.js template.
 *
 * @since 2.0
 * @package fusion-library
 */

?>
<#
var inputPlaceholder = 'undefined' !== typeof FusionApp ? fusionBuilderText.select_link : '',
	fieldId          = 'undefined' === typeof param.param_name ? param.id : param.param_name;
#>
<div class="fusion-link-selector">
	<input id="{{ fieldId }}" name="{{ fieldId }}" type="text" class="regular-text fusion-builder-link-field" value="{{ option_value }}" placeholder="{{ inputPlaceholder }}" />

	<# if ( 'undefined' !== typeof FusionApp ) { #>
		<a class='button-link-selector fusion-builder-link-button'><span class="fusiona-link-solid"></span></a>
	<# } else { #>
		<input type='button' class='button-link-selector fusion-builder-link-button' value='{{ fusionBuilderText.select_link }}'/>
	<# } #>
</div>
