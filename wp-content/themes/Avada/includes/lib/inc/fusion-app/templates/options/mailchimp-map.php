<?php
/**
 * Underscore.js template.
 *
 * @since 3.5
 * @package fusion-library
 */

?>
<#
	var fieldId = 'undefined' === typeof param.param_name ? param.id : param.param_name;
#>
<div class="mailchimp-map-holder">
	<div class="fusion-mapping">
		<span><?php esc_attr_e( 'No form fields or Mailchimp merge tags found.', 'Avada' ); ?></span>
	</div>
</div>
<input type="hidden" id="{{ fieldId }}" name="{{ fieldId }}" value="{{ option_value }}">
