<?php
/**
 * Underscore.js template.
 *
 * @since 2.0
 * @package fusion-library
 */

?>
<#
	var fieldId  = 'undefined' === typeof param.param_name ? param.id : param.param_name;
#>

<div class="awb-typography-sets-wrapper {{ fieldId }}">
	<ul id="{{ fieldId }}-list" class="awb-typography-sets"></ul>

	<div class="awb-typography-sets-add-btn-wrapper">
		<button class="button button-primary awb-typography-set-add-btn">
			<span class="fusiona-plus"></span>
			<?php esc_html_e( 'Add New Typography', 'fusion-builder' ); ?>
		</button>
	</div>
</div>
