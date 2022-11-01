<?php
/**
 * Underscore.js template.
 *
 * @since 2.4
 * @package fusion-library
 */

?>
<#
var choices       = param.choices,
	selection     = 'undefined' !== typeof option_value && '' !== option_value ? option_value : param.default,
	value         = selection,
	disabled      = JSON.parse( JSON.stringify( choices ) ),
	enabledClass  = '',
	disabledClass = '',
	emptyString   = 'string' !== typeof param.empty ? '<?php esc_html_e( 'Drag Meta items here to remove them from the page.', 'fusion-builder' ); ?>' : param.empty;

if ( 'string' === typeof selection ) {
	selection = selection.split( ',' );
}

enabledClass  = 1 > Object.keys( selection ).length ? 'empty' : '';

#>
<div id="{{ param.param_name }}_enabled">
	<div class="fusion-connected-sortable-heading"><?php esc_html_e( 'Enabled', 'fusion-builder' ); ?></div>
	<ul class="fusion-connected-sortable clearfix fusion-connected-sortable-enabled {{enabledClass}}" aria-empty="{{ emptyString }}">
		<# _.each( selection, function( key, value ) { #>
			<li class="fusion-connected-sortable-option" data-value="{{ key }}">
				<span>{{ choices[ key ] }}</span>
			</li>
			<# delete disabled[ key ]; #>
		<# }); #>
	</ul>
</div>
<# disabledClass  = 1 > Object.keys( disabled ).length ? 'empty' : ''; #>
<div id="{{ param.param_name }}_disabled">
	<div class="fusion-connected-sortable-heading"><?php esc_html_e( 'Disabled', 'fusion-builder' ); ?></div>
	<ul class="fusion-connected-sortable clearfix fusion-connected-sortable-disabled {{disabledClass}}" aria-empty="{{ emptyString }}">
		<# _.each( disabled, function( key, value ) { #>
			<li class="fusion-connected-sortable-option" data-value="{{ value }}">
				<span>{{ key }}</span>
			</li>
		<# }); #>
	</ul>
</div>
<input class="sort-order" type="hidden" id="{{ param.param_name }}" name="{{ param.param_name }}" value="{{ value }}">
