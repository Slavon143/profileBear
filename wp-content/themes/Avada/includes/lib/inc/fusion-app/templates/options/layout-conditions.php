<?php
/**
 * Underscore.js template.
 *
 * @since 3.6
 * @package fusion-library
 */

?>
<#
	var fieldId = 'undefined' === typeof param.param_name ? param.id : param.param_name,
	conditions = '' !== option_value ? JSON.parse( option_value ) : '',
	hasConditions = 'object' ===  typeof conditions && _.size( conditions ) ? 'has-conditions' : false;
#>
<div class="awb-layout-conditions">
	<span class="awb-off-canvas-conditions-constoller {{hasConditions}}">
		<span class="awb-conditions">
			<ul>
				<li class="no-condition-select"><?php echo __( 'Display on Entire Site', 'fusion-builder' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<# if ( false !== hasConditions ) { _.each( conditions, function( condition, value ) { #>
				<li class="{{condition.mode}}">{{condition.label}}</li>
				<# }); } #>
			</ul>
		</span>
	</span>
	<div class="awb-manage-conditions-wrapper">
		<a href="#" id="awb-manage-conditions" class="repeater-row-add button button-primary awb-manage-conditions"><i class="fusiona-cog"></i><?php echo __( 'Manage Conditions', 'fusion-builder' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
	</div>
</div>
<input type="hidden" id="{{ fieldId }}" name="{{ fieldId }}" value="{{ option_value }}" class="awb-conditions-value">
<input type="hidden" id="layout-conditions-nonce" value="<?php echo wp_create_nonce( 'fusion_tb_new_layout' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
