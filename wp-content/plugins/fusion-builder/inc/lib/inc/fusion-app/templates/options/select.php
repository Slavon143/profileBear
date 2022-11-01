<?php
/**
 * Underscore.js template.
 *
 * @since 2.0
 * @package fusion-library
 */

?>
<#
var fieldId        = 'undefined' === typeof param.param_name ? param.id : param.param_name,
	choices        = 'undefined' === typeof param.param_name ? param.choices : param.value,
	optionValue    = typeof( option_value ) !== 'undefined' ? option_value : '',
	hasSearch      = 'object' === typeof choices && 8 < Object.keys( choices ).length ? true : false,
	skipDebounce   = param.skip_debounce || false,
	searchText     = fusionBuilderText.search,
	conditions     = 'object' === typeof param.conditions ? _.escape( JSON.stringify( param.conditions ) ) : false,
	hasQuickEdit   = 'undefined' !== typeof param.quick_edit && ( ( '0' != optionValue && '' != optionValue && 'undefined' !== typeof FusionApp  ) || ( '0' != optionValue && 'undefined' === typeof FusionApp ) ) ? 'has-quick-edit' : '',
	quickEditTxt   = 'undefined' !== typeof param.quick_edit ? param.quick_edit.label : '';
	quickEditType  = 'undefined' !== typeof param.quick_edit ? param.quick_edit.type : '';
	quickEditItems = 'undefined' !== typeof param.quick_edit && 'undefined' !== param.quick_edit.items  ? JSON.stringify( param.quick_edit.items ) : '';


	if ( 'string' === typeof fusionBuilderText.search_placeholder && 'string' === typeof param.placeholder ) {
		searchText = fusionBuilderText.search_placeholder.replace( '%s', param.placeholder );
	}
#>
<# if ( 'undefined' !== typeof FusionApp ) { #>
<div class="fusion-select-field<?php echo ( is_rtl() ) ? ' fusion-select-field-rtl' : ''; ?>" <# if ( conditions ) {#>data-conditions="{{ conditions }}" <# } #>>
	<div class="fusion-select-preview-wrap">
		<span class="fusion-select-preview">
			<# if ( 'undefined' !== typeof choices[ optionValue ] ) { #>
				{{{ Array.isArray( choices[ optionValue ] ) ? choices[ optionValue ][0] : choices[ optionValue ] }}}
			<# } else { #>
				{{{ 'undefined' !== typeof choices[''] ? choices[''] : choices[0] }}}
			<# } #>
		</span>
		<div class="fusiona-arrow-down"></div>
	</div>
	<div class="fusion-select-dropdown">
		<# if ( hasSearch ) { #>
			<div class="fusion-select-search">
				<input type="text" class="fusion-hide-from-atts fusion-dont-update" placeholder="{{ searchText }}" />
			</div>
		<# } #>
		<div class="fusion-select-options">
			<# _.each( choices, function( name, value ) { #>
				<#
					name = 'object' === typeof name ? name[0] : name;
					checked = value === optionValue ? ' fusion-option-selected' : '';
				#>
				<label class="fusion-select-label{{ checked }}" data-value="{{ value }}">{{{ name }}}</label>
			<# }); #>
		</div>
	</div>
	<input type="hidden" id="{{ fieldId }}" name="{{ fieldId }}" value="{{ optionValue }}" class="fusion-select-option-value<# if ( skipDebounce ) { #> fusion-skip-debounce<# } #>">
</div>
<# if ( 'undefined' !== typeof param.quick_edit ) { #>
	<button type="button" class="button awb-quick-edit-button {{hasQuickEdit}}" data-items="{{quickEditItems}}" data-type="{{quickEditType}}">{{quickEditTxt}} <i class="fusiona-external-link"></i></button>
<# } #>
<# } else { #>
<div class="select_arrow"></div>
<select id="{{ fieldId }}" name="{{ fieldId }}" class="fusion-select-field<# if ( skipDebounce ) { #> fusion-skip-debounce<# } #><?php echo ( is_rtl() ) ? ' fusion-select-field-rtl' : ''; ?>"<?php echo ( is_rtl() ) ? ' data-dir="rtl"' : ''; ?> <# if ( conditions ) {#>data-conditions="{{ conditions }}" <# } #>>
<# _.each( choices, function( name, value ) { #>
	<#
		name = 'object' === typeof name ? name[0] : name;
		option_value = Number.isInteger( value ) ? parseInt( option_value ) : option_value;
	#>
	<option value="{{ value }}" {{ typeof( option_value ) !== 'undefined' && value === option_value ?  ' selected="selected"' : '' }} >{{ name }}</option>
<# }); #>
</select>
<# if ( 'undefined' !== typeof param.quick_edit ) { #>
	<button type="button" class="button awb-quick-edit-button {{hasQuickEdit}}" data-items="{{quickEditItems}}" data-type="{{quickEditType}}">{{quickEditTxt}} <i class="fusiona-external-link"></i></button>
<# } #>
<# } #>
