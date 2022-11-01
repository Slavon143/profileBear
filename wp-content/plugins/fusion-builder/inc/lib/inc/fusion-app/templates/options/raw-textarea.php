<?php
/**
 * Underscore.js template.
 *
 * @since 2.0
 * @package fusion-library
 */

?>
<textarea
	name="{{ param.param_name }}"
	id="{{ param.param_name }}"
	class="fusion-builder-raw-textarea"
	cols="20"
	rows="5"
	<# if ( param.placeholder ) { #>
		data-placeholder="{{ param.value }}"
	<# } #>
>{{ option_value }}</textarea>
