<?php
/**
 * Underscore.js template.
 *
 * @since 2.0
 * @package fusion-library
 */

?>
<#
var choice = option_value, index = 0,
	values = 'object' === typeof param.choices ? param.choices : param.value,
	fieldId = 'undefined' === typeof param.param_name ? param.id : param.param_name,
	colors = [ 'Red', 'Light Red', 'Blue', 'Light Blue', 'Green', 'Dark Green', 'Orange', 'Pink', 'Brown', 'Light Grey'];
#>
<div class="fusion-form-radio-button-set radio-image-set ui-buttonset fusion-option-{{ fieldId }}">

	<input type="hidden" id="{{ fieldId }}" name="{{ fieldId }}" value="{{ choice }}" class="button-set-value" />
	<# _.each( values, function( src, value ) { #>
		<# index++; #>
		<# var selected = ( value == choice && 'color_scheme' !== fieldId && 'scheme_type' !== fieldId ) ? ' ui-state-active' : ''; #>
		<# var width = ( '' !== param.width ) ? param.width : '32px'; #>
		<# var height = ( '' !== param.height ) ? param.height : '32px'; #>
		<# src = src.image ? src.image : src; #>
		<# var alt = src.label ? src.label : ''; #>
		<a href="#" class="ui-button buttonset-item{{ selected }}" data-value="{{ value }}">
			<img src="{{ src }}" alt="{{ alt }}" style="width: {{ width }}; height: {{ height }};"/>
			<# if ( 'color_scheme' === fieldId && ! _.contains( colors, value ) ) { #>
				<span class="fusion-elements-option-tooltip fusion-tooltip-description">{{ value }}</span>
			<# } #>
		</a>
	<# } ); #>
</div>
