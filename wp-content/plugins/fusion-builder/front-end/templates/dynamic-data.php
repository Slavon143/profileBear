<?php
/**
 * Underscore.js template.
 *
 * @since 2.1
 * @package fusion-library
 */

?>
<#
var dynamicData = 'object' === typeof atts.dynamic_params ? atts.dynamic_params[ param.param_name ] : false,
	fieldId     = 'undefined' !== typeof dynamicData ? dynamicData.data : '',
	options     = FusionPageBuilderApp.dynamicValues.getOptions(),
	field       = 'object' === typeof options && 'undefined' !== typeof options[ fieldId ] ? options[ fieldId ] : false,
	label       = field && 'string' === typeof field.label ? field.label : fieldId,
	ajax        = field && 'object' === typeof field.callback && 'undefined' !== typeof field.callback.ajax ? field.callback.ajax : false;
#>
<div class="dynamic-wrapper" data-id="{{ fieldId }}" data-ajax="{{ ajax }}">
	<div class="dynamic-title">
		<span class="dynamic-toggle-icon fusiona-pen"></span>
		<h3>{{ label }}</h3>
		<span class="dynamic-remove fusiona-trash-o"></span>
	</div>
	<ul class="dynamic-param-fields">
	</ul>
</div>

