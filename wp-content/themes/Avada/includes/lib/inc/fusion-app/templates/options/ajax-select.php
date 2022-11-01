<?php
/**
 * Underscore.js template.
 *
 * @since 2.0
 * @package fusion-library
 */

?>
<#
var fieldId      = 'undefined' === typeof param.param_name ? param.id : param.param_name,
	choices      = 'undefined' === typeof param.param_name ? param.choices : param.value,
	max_input    = 'undefined' !== typeof param.max_input ? param.max_input : 1000,
	isSingle     =  1 === max_input ? 'fusion-ajax-single-select' : '';
	placeholder  = 'undefined' !== typeof param.placeholder ? param.placeholder : '',
	optionValue  = typeof( option_value ) !== 'undefined' ? option_value : '',
	skipDebounce = param.skip_debounce || false,
	searchText   = fusionBuilderText.search;
	ajaxSearch	 = param.ajax || '';
	repeaterId   = 'undefined' === typeof repeaterIndex ? '' : repeaterIndex + '-';
	ajaxParams   = param.ajax_params || [];

	if ( 'string' === typeof fusionBuilderText.search_placeholder && 'string' === typeof param.placeholder ) {
		searchText = fusionBuilderText.search_placeholder.replace( '%s', param.placeholder );
	}

	if ( '' !== placeholder ) {
		searchText = placeholder;
	}

	var value  = option_value,
		values = '',
		initialValues = '';

	if ( 'undefined' !== typeof value && '' !== value && null !== value && false !== value ) {
		values = 'string' !== typeof value ? value : value.split( ',' );
		if ( 'object' === typeof values && ! Array.isArray( values ) ) {
			values = Object.values( values );
		}
		initialValues = _.escape( JSON.stringify(values) );
	}

	if ( ajaxParams ) {
		ajaxParams = _.escape( JSON.stringify(ajaxParams) )
	}
#>

<# if ( 'object' === typeof FusionApp ) { #>
	<div
		class="fusion-select-field {{isSingle}} fusion-ajax-select<?php echo ( is_rtl() ) ? ' fusion-select-field-rtl' : ''; ?>"
		data-field-id="{{fieldId}}"
		data-ajax={{ajaxSearch}}
		data-repeater-id="{{ repeaterId }}"
		data-max-input="{{max_input}}"
		>
		<input type="hidden" value="{{ initialValues }}" class="initial-values" name="values" />
		<input type="hidden" value="{{ ajaxParams }}" class="params" name="values" />
		<div class="fusion-ajax-select-wrap">
			<div class="fusion-ajax-select-search">
				<input type="search" class="fusion-hide-from-atts fusion-dont-update" placeholder="{{ searchText }}" />
			</div>
			<div class="fusion-select-dropdown">
				<div class="fusion-ajax-select-notice" style="display: none;"><?php esc_html_e( 'No search results', 'fusion-builder' ); ?></div>
				<div class="fusion-select-options" style="display: none;"></div>
			</div>
			<# if ( '' !== isSingle ) { #>
				<div class="fusion-select-tags-wrap">
					<span class="fusion-select-tags"></span>
				</div>
			<# } #>
		</div>
		<# if ( '' === isSingle ) { #>
			<div class="fusion-select-tags-wrap">
				<span class="fusion-select-tags"></span>
			</div>
		<# } #>
	</div>
	<# if ( 'undefined' !== typeof param.add_new ) { #>
		<a href="#" class="fusion-multiselect-addnew">{{fusionBuilderText.add_new}}</a>
		<div class="fusion-multiselect-addnew-section">
			<input type="text" class="fusion-multiselect-input fusion-hide-from-atts fusion-dont-update" data-id="{{param.id}}" placeholder="{{fusionBuilderText.separate_with_comma}}">
			<div class="fusion-multiselect-actions">
				<a href="#" class="fusion-multiselect-cancel fusion-panel-cancel-button">{{fusionBuilderText.cancel}}</a>
				<a href="#" class="fusion-multiselect-save fusion-panel-button">{{fusionBuilderText.add}}</a>
			</div>
		</div>
	<# } #>
<# } else { #>
	<input type="hidden" value="{{ ajaxParams }}" class="params" />
	<input type="hidden" value="{{ initialValues }}" class="initial-values" />
	<select class="fusion-ajax-select" style="width: 100%" multiple="multiple" data-max-input="{{max_input}}" data-placeholder="{{ searchText }}" data-ajax="{{ajaxSearch}}" id="{{fieldId}}" name="{{fieldId}}"></select>
	<div class="fusion-loader">
		<div class="fusion-builder-loader">
		</div>
	</div>
<# } #>
