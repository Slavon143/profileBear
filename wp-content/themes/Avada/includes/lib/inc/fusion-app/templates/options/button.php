<?php
/**
 * Underscore.js template.
 *
 * @since 2.0
 * @package fusion-library
 */

?>
<#
	var label  = 'undefined' !== typeof param.label ? param.label : '',
		id     = 'undefined' !== typeof param.id ? param.id : '',
		action = 'undefined' !== typeof param.action ? param.action : '';
#>
<input type="button" id="{{id}}" onclick="{{action}}" class="button fusion-button-option" value="{{label}}">
