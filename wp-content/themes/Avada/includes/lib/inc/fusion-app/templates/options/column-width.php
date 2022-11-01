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
		option_value    = 'undefined' === typeof option_value ? '' : option_value,
		rangeValue      = option_value || param.default,
		responsiveState = param.responsive && param.responsive.state;
#>
<div class="fusion-form-column-width fusion-option-{{ fieldId }}">
	<input type="hidden" id="{{ fieldId }}" name="{{ fieldId }}" value="{{ option_value }}" class="width-value" />
	<div class="ui-buttons">
		<div class="ui-buttonset fusion-form-column-width-grid-layout">
			<a href="#" class="ui-button buttonset-item has-tooltip" data-value="1_6" aria-label="1/6"><span>1/6</span></a>
			<a href="#" class="ui-button buttonset-item has-tooltip" data-value="1_5" aria-label="1/5"><span>1/5</span></a>
			<a href="#" class="ui-button buttonset-item has-tooltip" data-value="1_4" aria-label="1/4"><span>1/4</span></a>
			<a href="#" class="ui-button buttonset-item has-tooltip" data-value="1_3" aria-label="1/3"><span>1/3</span></a>
			<a href="#" class="ui-button buttonset-item has-tooltip" data-value="2_5" aria-label="2/5"><span>2/5</span></a>
			<a href="#" class="ui-button buttonset-item has-tooltip" data-value="1_2" aria-label="1/2"><span>1/2</span></a>
			<a href="#" class="ui-button buttonset-item has-tooltip" data-value="3_5" aria-label="3/5"><span>3/5</span></a>
			<a href="#" class="ui-button buttonset-item has-tooltip" data-value="2_3" aria-label="2/3"><span>2/3</span></a>
			<a href="#" class="ui-button buttonset-item has-tooltip" data-value="3_4" aria-label="3/4"><span>3/4</span></a>
			<a href="#" class="ui-button buttonset-item has-tooltip" data-value="4_5" aria-label="4/5"><span>4/5</span></a>
			<a href="#" class="ui-button buttonset-item has-tooltip" data-value="5_6" aria-label="5/6"><span>5/6</span></a>
			<a href="#" class="ui-button buttonset-item has-tooltip" data-value="1_1" aria-label="1/1"><span>1/1</span></a>
			<a href="#" class="ui-button buttonset-item has-tooltip" data-value="auto" aria-label="auto"><span>Auto</span></a>
		</div>
	</div>
	<div class="ui-input">
		<input
			type="text"
			value="{{ rangeValue }}"
			class="custom-width-input fusion-hide-from-atts fusion-dont-update"
		/>
		<div class="custom-width-range-slider" data-direction="<?php echo ( is_rtl() ) ? 'rtl' : 'ltr'; ?>">
		</div>
	</div>
	<div class="column-width-toggle-container">
		<a href="#" class="column-width-toggle">
			<span class="fusiona-pen"></span>
			<span class="label width-default">{{{ fusionBuilderText.toggle_column_custom_width }}}</span>
			<span class="label width-custom">{{{ fusionBuilderText.toggle_column_default_widths }}}</span>
		</a>
		<# if ( 'medium' === responsiveState || 'small' === responsiveState ) { #>
			<a href="#" class="ui-button buttonset-item has-tooltip default" data-value="" aria-label="Default"><span>Default</span></a>
		<# } #>
	</div>
</div>
