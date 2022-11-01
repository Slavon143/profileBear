<?php
/**
 * Underscore.js template.
 *
 * @since 2.0
 * @package fusion-library
 */

?>
<#
var fieldId = 'undefined' === typeof param.param_name ? param.id : param.param_name;
#>
<input
	type="text"
	name="{{ fieldId }}"
	id="{{ fieldId }}"
	value="{{ option_value }}"
	<# if ( param.css_class ) { #>
	class="{{ param.css_class }}"
	<# } #>
	<# if ( param.placeholder ) { #>
		data-placeholder="{{ param.value }}"
	<# } #>
/>
