<?php
/**
 * Underscore.js template.
 *
 * @since 2.0
 * @package fusion-library
 */

?>
<#
	var builderCheck  = 'undefined' !== typeof param.param_name,
		fieldId       = builderCheck ? param.param_name : param.id,
		min           = builderCheck ? param.min : param.choices.min,
		min           = min ? min : 0,
		max           = builderCheck ? param.max : param.choices.max,
		max           = max ? max : 100,
		step          = builderCheck ? param.step : param.choices.step,
		step          = step ? step : '1',
		step          = step.toString(),
		defaultStatus = 'undefined' !== typeof param.default && '' !== param.default && ( builderCheck || 'PO' === option_type ) ? 'fusion-with-default' : '',
		isChecked     = '' == option_value ? 'checked' : '',
		regularId     = ! param.default || ( ! builderCheck && 'TO' === option_type ) ? fieldId : 'slider' + fieldId,
		displayValue  = '' == option_value ? param.default : option_value;

	if ( '' === defaultStatus && ( 'undefined' === typeof option_value || '' === option_value ) && 'undefined' !== typeof param.value ) {
		option_value = displayValue = param.value;
	}
	if ( '.' === step.charAt( 0 ) ) {
		step = '0' + step;
	}
#>
<input
	type="text"
	name="{{ regularId }}"
	id="{{ regularId }}"
	value="{{ displayValue }}"
	class="fusion-slider-input {{ defaultStatus }} <# if ( param.default && ( builderCheck || 'PO' === option_type ) ) { #>fusion-hide-from-atts<# } #>"
/>
<div
	class="fusion-slider-container {{ fieldId }}"
	data-id="{{ fieldId }}"
	data-min="{{ min }}"
	data-max="{{ max }}"
	data-step="{{ step }}"
	data-value="{{ displayValue }}"
	data-direction="<?php echo ( is_rtl() ) ? 'rtl' : 'ltr'; ?>">
</div>
<# if ( 'undefined' !== typeof param.default && ( builderCheck || 'PO' === option_type ) ) { #>
	<input type="hidden" id="{{ fieldId }}" value="{{ option_value }}" class="fusion-hidden-value" />
<# } #>
