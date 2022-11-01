<?php
/**
 * Underscore.js template.
 *
 * @since 2.0
 * @package fusion-library
 */

?>
<#
	var repeaterIndex = 'undefined' !== typeof rowId ? rowId : 0,
	option_value    = 'undefined' !== value ? value : param.value;
	hidden          = 'undefined' !== typeof param.hidden ? ' hidden' : '';
	childDependency = 'undefined' !== typeof param.child_dependency ? ' has-child-dependency' : '';
	optionId        = 'undefined' !== typeof param.param_name ? param.param_name : param.id;
	optionTitle     = 'undefined' !== typeof param.heading ? param.heading : param.label;
	context         = 'undefined' !== typeof context ? context : '';

	if ( param.type == 'select' || param.type == 'multiple_select' || param.type == 'radio_button_set' || param.type == 'checkbox_button_set' || param.type == 'filter' || param.type === 'ajax_select' ) {
		option_value = 'undefined' === typeof option_value || '' === option_value ? param.default : option_value;
	} else if ( 'undefined' === typeof option_value && 'undefined' !== typeof param.value ) {
		option_value = param.value;
	}
#>
<div class="toggle-wrapper">
</div>
