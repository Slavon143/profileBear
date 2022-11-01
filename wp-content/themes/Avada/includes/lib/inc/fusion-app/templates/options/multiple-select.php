<?php
/**
 * Underscore.js template.
 *
 * @since 2.0
 * @package fusion-library
 */

?>
<#
var fieldId         = 'undefined' === typeof param.param_name ? param.id : param.param_name,
	choices         = 'undefined' === typeof param.value ? param.choices : param.value,
	hasSearch       = 'object' === typeof choices && 8 < Object.keys( choices ).length ? true : false,
	searchText      = fusionBuilderText.search,
	placeholderText = 'undefined' !== typeof param.placeholder_text ? param.placeholder_text : '';
	repeaterId      = 'undefined' === typeof repeaterIndex ? '' : repeaterIndex + '-';

	if ( 'string' === typeof fusionBuilderText.search_placeholder && 'string' === typeof param.placeholder ) {
		searchText = fusionBuilderText.search_placeholder.replace( '%s', param.placeholder );
	}

	if ( '' === placeholderText ) {
		if ( -1 !== fieldId.indexOf( 'cat_slug' ) ) {
			placeholderText = fusionBuilderText.select_categories_or_leave_blank_for_all;
		} else if ( -1 !== fieldId.indexOf( 'exclude_cats' ) ) {
			placeholderText = fusionBuilderText.select_categories_or_leave_blank_for_none;
		} else if ( -1 !== fieldId.indexOf( 'category' ) ) {
			placeholderText = fusionBuilderText.select_categories_or_leave_blank_for_none;
		} else if ( -1 !== fieldId.indexOf( 'post_status' ) ) {
			placeholderText = fusionBuilderText.select_post_status_leave_blank_for_publish;
		} else if ( -1 !== fieldId.indexOf( 'social_share_links' ) ) {
			placeholderText = fusionBuilderText.select_or_leave_blank_for_global;
		} else if ( 'undefined' === typeof param.location || ( 'TO' !== param.location && 'FBE' !== param.location ) ) {
			placeholderText = fusionBuilderText.select_options_or_leave_blank_for_all;
		}

	}
#>
<# if ( 'undefined' !== typeof FusionApp ) { #>
<div id="{{ fieldId }}" class="fusion-select-field fusion-form-multiple-select">

	<#
	var value  = option_value,
		values = '';

	if ( 'undefined' !== typeof value && '' !== value && null !== value && false !== value ) {
		values = 'string' !== typeof value ? value : value.split( ',' );
		if ( 'object' === typeof values && ! Array.isArray( values ) ) {
			values = Object.values( values );
		}
	}
	#>

	<div class="fusion-select-preview-wrap {{ '' === values ? 'fusion-select-show-placeholder' : '' }}">
		<span class="fusion-select-preview">
			<# if ( '' !== values ) { #>
				<# _.each( values, function( value ) { #>
					<span class="fusion-preview-selected-value" data-value="{{ repeaterId }}{{ fieldId }}-{{ value }}">{{{ choices[ value ] }}}<span class="fusion-option-remove">x</span></span>
				<# } ); #>
			<# } #>
			<div class="fusiona-arrow-down"></div>
		</span>
		<div class="fusion-select-placeholder">{{ placeholderText }}</div>
	</div>

	<div class="fusion-select-dropdown">
		<# if ( hasSearch ) { #>
			<div class="fusion-select-search">
				<input type="text" class="fusion-hide-from-atts fusion-dont-update" placeholder="{{ searchText }}" />
			</div>
		<# } #>
		<div class="fusion-select-options">
			<# _.each( choices, function( name, value ) { #>
				<# var checked = ( jQuery.inArray( value, values ) > -1 ) ? ' checked="checked"' : ''; #>
				<input type="checkbox" id="{{ repeaterId }}{{ fieldId }}-{{ value }}" name="{{ fieldId }}[]" value="{{ value }}" data-label="{{ name }}" class="fusion-select-option fusion-multi-select-option"{{{ checked }}}><label for="{{ repeaterId }}{{ fieldId }}-{{ value }}" class="fusion-select-label">{{{ name }}}</label>
			<# } ); #>
		</div>
	</div>
</div>
	<# if ( 'undefined' !== typeof param.add_new ) { #>
	<a href="#" class="fusion-multiselect-addnew">{{fusionBuilderText.add_new}}</a>
	<div class="fusion-multiselect-addnew-section">
		<input type="text" class="fusion-multiselect-input" data-id="{{param.id}}" placeholder="{{fusionBuilderText.separate_with_comma}}">
		<div class="fusion-multiselect-actions">
			<a href="#" class="fusion-multiselect-cancel fusion-panel-cancel-button">{{fusionBuilderText.cancel}}</a>
			<a href="#" class="fusion-multiselect-save fusion-panel-button">{{fusionBuilderText.add}}</a>
		</div>
	</div>
	<# } #>
<# } else { #>
	<select id="{{ fieldId }}" name="{{ fieldId }}" multiple="multiple" class="fusion-form-multiple-select fusion-input">
		<# var value = option_value; #>
		<# if ( 'undefined' !== typeof value && '' !== value && null !== value && false !== value ) { #>
			<# var values = ( Array.isArray( value ) ) ? value : value.split( ',' ); #>
		<# } else { #>
			<# var values = ''; #>
		<# } #>

		<# _.each( choices, function( name, value ) { #>
			<# var selected = ( jQuery.inArray( value, values ) > -1 ) ? ' selected="selected"' : ''; #>
			<option value="{{ value }}"{{ selected }} >{{{ name }}}</option>
		<# } ); #>
	</select>
<# } #>
