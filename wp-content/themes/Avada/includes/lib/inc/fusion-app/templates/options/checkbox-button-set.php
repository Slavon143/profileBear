<?php
/**
 * Underscore.js template.
 *
 * @since 2.0
 * @package fusion-library
 */

?>
<div class="fusion-form-checkbox-button-set ui-buttonset {{ param.param_name }}">
	<# var choice = option_value,
			index = 0,
			icons   = 'undefined' !== typeof FusionApp && 'undefined' !== typeof param.icons ? param.icons : '';
	#>
	<# if ( 'undefined' !== typeof choice && '' !== choice && null !== choice ) { #>
		<# var choices = ( Array.isArray( choice ) ) ? choice : choice.split( ',' ); #>
	<# } else { #>
		<# var choices = ''; #>
	<# } #>
	<input type="hidden" id="{{ param.param_name }}" name="{{ param.param_name }}" value="{{ choice }}" class="button-set-value" />
	<# _.each( param.value, function( name, value ) { #>
		<#
		index++;
		var selected  = ( jQuery.inArray( value, choices ) > -1 ) ? ' ui-state-active' : '',
			icon      = ( 'undefined' !== typeof icons[ value ] && '' !== icons ) ? icons[ value ] : '',
			title     = name,
			iconClass = '' === icon ? '' : 'has-tooltip';

		if ( -1 !== icon.indexOf( 'svg' ) || -1 !== icon.indexOf( 'span' ) ) {
			title = icon;
		} else if ( -1 !== name.indexOf( 'span' ) && -1 !== name.indexOf( '|' ) ) {

			// Exception for visibility options.
			title      = name.split( '|' );
			name       = title[1];
			title      = title[0]
			iconClass += ' has-tooltip';
		} else if ( '' !== icon ) {
			iconClass += ' ' + icon;
			title      = '';
		}
		#>
		<a href="#" class="ui-button buttonset-item{{ selected }} {{ iconClass }}" data-value="{{ value }}" aria-label="{{ name }}">
			{{{ title }}}
		</a>
	<# }); #>

</div>
