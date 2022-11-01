<?php
/**
 * Underscore.js template.
 *
 * @since 3.8
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
	class="fusion-builder-raw-text"
	<# if ( param.placeholder ) { #>
		data-placeholder="{{ param.value }}"
	<# } #>
/>
