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
	choices = 'undefined' === typeof param.param_name ? param.choices : param.value,
	icons   = 'undefined' !== typeof param.icons ? param.icons : '';
#>
<div class="fusion-form-radio-button-set ui-buttonset fusion-option-{{ fieldId }}">
	<# var choice = option_value, index = 0; #>
	<input type="hidden" id="{{ fieldId }}" name="{{ fieldId }}" value="{{ choice }}" class="button-set-value" />
	<# _.each( choices, function( name, value ) { #>
		<#
		const wrappedName = '<span class="buttonset-item-label">' + name + '</span>';
		index++;
		var selected  = ( 1 === index ) ? ' ui-state-active' : '',
			icon      = ( 'undefined' !== typeof icons[ value ] && '' !== icons ) ? icons[ value ] : '',
			title     = wrappedName,
			iconClass = '' === icon ? '' : 'has-tooltip';

		if ( -1 !== icon.indexOf( 'svg' ) || -1 !== icon.indexOf( 'span' ) && 'undefined' !== typeof FusionApp  ) {
			title = icon;
		} else if ( -1 !== icon.indexOf( 'svg' ) || -1 !== icon.indexOf( 'span' ) && 'undefined' === typeof FusionApp  ) {
			title = icon + wrappedName;
		} else if ( '' !== icon ) {
			iconClass += ' ' + icon;
			title      = '';
		}

		#>
		<a href="#" class="ui-button buttonset-item{{ selected }} {{ iconClass }}" data-value="{{ value }}" aria-label="{{ name }}">{{{ title }}}</a>
	<# } ); #>
</div>
