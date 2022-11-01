<?php
/**
 * Underscore.js template.
 *
 * @since 2.0
 * @package fusion-library
 */

?>
<#
fieldId  = 'undefined' === typeof param.param_name ? param.id : param.param_name;
if ( 'undefined' === typeof param.row_add ) {
	itemLabel = "<?php esc_html_e( 'Add Another Item', 'fusion-builder' ); ?>";
} else {
	itemLabel = param.row_add
}

option_value = 'object' === typeof option_value ? JSON.stringify( option_value ) : option_value;
#>
<a href="#" class="repeater-row-add button button-primary"><span class="fusiona-plus"></span> {{ itemLabel }}</a>
<div class="repeater-wrapper" data-id="{{ fieldId }}">
	<input type="hidden" name="{{ fieldId }}" id="{{ fieldId }}" value="{{ option_value }}" class="fusion-repeater-value" />
	<div class="repeater-rows"></div>
</div>
