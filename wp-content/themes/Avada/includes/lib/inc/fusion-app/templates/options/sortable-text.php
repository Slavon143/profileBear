<?php
/**
 * Underscore.js template.
 *
 * @since 2.0
 * @package fusion-library
 */

?>
<a href="#" class="fusion-builder-add-sortable-child"><span class="fusiona-plus"></span><span class="add-sortable-child-text">{{{ param.add_label }}}</span></a>
<ul class="fusion-sortable-text-options" id="{{ param.param_name }}">
	<#
		var values = 'undefined' !== typeof option_value && '' !== option_value ? option_value : param.default;
			if ( '' === values ) {
				values = [];
			}
			if ( 'string' === typeof values ) {
				values = values.split( '|' );
			}
	#>
	<# _.each( values, function( value ) { #>
		<div class="fusion-sortable-option">
			<input type="text" value="{{ value }}" class="fusion-hide-from-atts" <# if ( 'undefined' !== typeof param.placeholder ) { #>placeholder="{{ param.placeholder }}"<# } #> />
			<a href="#" class="fusion-sortable-move" tabIndex="-1" aria-label="<?php esc_attr_e( 'Move Row' ); ?>">
				<span class="fusiona-icon-move"></span>
			</a>
			<a href="#" class="fusion-sortable-remove" tabIndex="-1" aria-label="<?php esc_attr_e( 'Remove Row' ); ?>">
				<span class="fusiona-trash-o"></span>
			</a>
		</div>
	<# }); #>
</ul>
<div class="fusion-sortable-option fusion-placeholder-example" style="display:none">
	<input type="text" value="" class="fusion-hide-from-atts" <# if ( 'undefined' !== typeof param.placeholder ) { #>placeholder="{{ param.placeholder }}"<# } #> />
	<a href="#" class="fusion-sortable-move" tabIndex="-1" aria-label="<?php esc_attr_e( 'Move Row' ); ?>">
		<span class="fusiona-icon-move"></span>
	</a>
	<a href="#" class="fusion-sortable-remove" tabIndex="-1" aria-label="<?php esc_attr_e( 'Remove Row' ); ?>">
		<span class="fusiona-trash-o"></span>
	</a>
</div>
<input class="sort-order" type="hidden" id="{{ param.param_name }}" name="{{ param.param_name }}" value="{{ option_value }}">
