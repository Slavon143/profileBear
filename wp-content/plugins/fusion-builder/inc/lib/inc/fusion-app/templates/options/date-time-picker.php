<?php
/**
 * Underscore.js template.
 *
 * @since 2.0
 * @package fusion-library
 */
?>
<# 
const hasDate = undefined !== param.date ? param.date : true;
const hasTime = undefined !== param.time ? param.time : true;

if ( 'undefined' !== typeof FusionApp ) { #>
	<#
	var value = ( '' !== option_value ) ? option_value.split( ' ' ) : '',
		date = ( '' !== value ) ? value[0] : ''
		time = ( '' !== value ) ? value[1] : '';

	const hasDate = undefined !== param.date ? param.date : true;
	const hasTime = undefined !== param.time ? param.time : true;

	let pickerClass = '';
	if ( !hasDate || !hasTime ) {
		pickerClass = 'one-column';
		date = option_value;
		time = option_value;
	}
	#>
	<div class="fusion-datetime">
		<input
			type="hidden"
			data-format="yyyy-MM-dd hh:mm:ss"
			id="{{ param.param_name }}"
			class="fusion-date-time-picker"
			name="{{ param.param_name }}"
			value="{{ option_value }}" />
	</div>

	<div class="fusion-datetime-container {{pickerClass}}">
		<# if( hasDate ) { #>
			<div class="fusion-datetime-datepicker">
				<input
					type="text"
					data-format="yyyy-MM-dd"
					id="fusion-datetime-datepicker"
					class="fusion-date-picker fusion-hide-from-atts"
					value="{{ date }}" />
				<div class="fusion-date-picker-field add-on">
					<i class="fusiona-calendar-plus-o" data-date-icon="fusiona-calendar-plus-o" aria-hidden="true"></i>
				</div>
			</div>
		<# } #>

		<# if( hasTime ) { #>
			<div class="fusion-datetime-timepicker">
				<input
					type="text"
					data-format="hh:mm:ss"
					id="fusion-datetime-timepicker"
					class="fusion-time-picker fusion-hide-from-atts"
					value="{{ time }}" />
				<div class="fusion-time-picker-field add-on">
					<i data-time-icon="fusiona-clock" class="fusiona-clock" aria-hidden="true"></i>
				</div>
			</div>
		<# } #>
	</div>
<# } else { 
	let icon = 'fusiona-calendar-plus-o';
	let type = 'full-picker';

	// time only
	if ( !hasDate ) {
		icon = 'fusiona-clock';
		type = 'time-picker';
	}	

	// date only
	if ( !hasTime ) {
		format = 'yyyy-MM-dd';
		type = 'date-picker';
	}	
#>
	<div class="fusion-datetime {{ type }}">
	<input
		type="text"
		id="{{ param.param_name }}"
		name="{{ param.param_name }}"
		value="{{ option_value }}"
	/>
	<div class="fusion-dt-picker-field add-on" >
		<i data-time-icon="fusiona-clock" data-date-icon="{{ icon }}" aria-hidden="true"></i>
	</div>
</div>
<# } #>
