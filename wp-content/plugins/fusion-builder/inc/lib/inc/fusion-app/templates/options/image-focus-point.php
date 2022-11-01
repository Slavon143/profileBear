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
var value 		= option_value || param.value || param.default || '';
let position = '';

if ( !value.includes( ' ' ) ) {
	// in this case get the value from other element setting or parent setting.
	if ( value.startsWith( 'parent_' ) ) {
		let key = value.replace( 'parent_', '' );
		value = atts.parentValues ? atts.parentValues[key] || '' : '';
	} else {
		value = atts.parentValues ? atts.parentValues[value] || '' : '';
	}
}

position 	= value ? value.split( ' ' ) : [];
var left 		= position[0] ? `${position[0]}`: '50%';
var top 		= position[1] ? `${position[1]}`: '50%';
var pointStyle	= `top:${top}; left:${left};`;
#>
<div class="fusion-image-focus-point mode-{{ param.mode }}" data-lazy="{{ param.lazy }}" data-mode="{{ param.mode }}">
	<div class="preview">
		<div class="image" data-image="{{ param.image }}">
			<!-- image should be here -->
		</div>
		<# 
			if ( 'position' === param.mode ) {
		#>
			<span class="position-point top-left"></span>
			<span class="position-point top-center"></span>
			<span class="position-point top-right"></span>
			<span class="position-point center-left"></span>
			<span class="position-point center-center"></span>
			<span class="position-point center-right"></span>
			<span class="position-point bottom-left"></span>
			<span class="position-point bottom-center"></span>
			<span class="position-point bottom-right"></span>
			<span class="position-axis axis-x"></span>
			<span class="position-axis axis-y"></span>
		<#
		} else {
		#>
			<span class="fusiona-image no-image-holder"></span>
		<# 
			}
		#>
		<span class="point" style="{{ pointStyle }}"></span>
	</div>
	<input type="hidden" id="{{ fieldId }}" name="{{ fieldId }}" class="regular-text fusion-builder-focus-point-field" value='{{ option_value }}' />
</div>
