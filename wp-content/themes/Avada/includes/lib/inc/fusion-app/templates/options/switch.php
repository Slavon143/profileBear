<?php
/**
 * Underscore.js template.
 *
 * @since 2.0
 * @package fusion-library
 */

?>
<#
var fieldId = 'undefined' === typeof param.param_name ? param.id : param.param_name,
	fields  = 'undefined' === typeof param.param_name ? param.required : param.value,
	choice  = option_value,
	index   = 0;
#>
<label class="switch" for="{{ fieldId }}">
	<input class="switch-input screen-reader-text" name="{{ fieldId }}" id="{{ fieldId }}" type="checkbox" value="{{ choice }}"<# if ( '1' == choice ) { #> checked<# } #> />
	<span class="switch-label">
		<span class="yes {{ fieldId }}">{{ fusionBuilderText.yes }}</span>
		<span class="no {{ fieldId }}">{{ fusionBuilderText.no }}</span>
	</span>
</label>
