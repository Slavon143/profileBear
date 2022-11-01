<?php
/**
 * Underscore.js template.
 *
 * @since 2.0
 * @package fusion-library
 */

?>
<ul class="fusion-sortable-options">
	<#
		var choices   = param.choices,
			selection = 'undefined' !== typeof option_value && '' !== option_value ? option_value : param.default;

			if ( 'string' === typeof selection ) {
				selection = selection.split( ',' );
			}
	#>
	<# _.each( selection, function( key ) { #>
		<li class="fusion-sortable-option" data-value="{{ key }}">
			<span>{{ choices[ key ] }}</span>
		</li>
	<# }); #>
</ul>
<input class="sort-order" type="hidden" id="{{ param.param_name }}" name="{{ param.param_name }}" value="{{ option_value }}">
