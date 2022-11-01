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
		defaultParam	= 'undefined' === typeof param.default ? '' : param.default,
		option_value    = _.isEmpty( option_value ) ? defaultParam : option_value,
		options 		= option_value ? JSON.parse( FusionPageBuilderApp.base64Decode( option_value ) ) : [],
		allowMultiple   = !param.allow_multiple ? 'yes' : param.allow_multiple,
		valuesEnabled;

		//Check if there are custom vales.
		valuesEnabled = _.find( options, function( option ) {
			return option[2];
		} );
#>
<div class="fusion-form-form-options fusion-option-{{ fieldId }}">
	<a href="#" class="fusion-builder-add-sortable-child"><span class="fusiona-plus"></span><span class="add-sortable-child-text">{{ fusionBuilderText.add_new_option }}</span></a>
	<div class="options-grid {{valuesEnabled ? 'show-values' : '' }}" data-multiple="{{ allowMultiple }}">
		<ul class="fusion-form-options">
			<label class="header-label">{{ fusionBuilderText.option_label }}</label>
			<label class="header-value">{{ fusionBuilderText.option_value }}</label>
			<# _.each( options, function( option ) { 
				var checked = option[0],
					value	= option[2],
					label	= option[1];
			#>
				<li class="fusion-form-option">
					<div class="form-option-move-1">
						<a href="#" class="fusion-sortable-move" tabIndex="-1" aria-label="<?php esc_attr_e( 'Move Row', 'fusion-builder' ); ?>">
							<span class="fusiona-arrange"></span>
						</a>
					</div>
					<div class="form-option-label">
						<input type="text" value="{{ label }}" class="fusion-hide-from-atts label" />
					</div>
					<div class="form-option-value">
						<input type="text" value="{{ value }}" class="fusion-hide-from-atts value" />
					</div>
					<div class="form-option-checked">
						<a href="#" class="fusion-sortable-check" tabIndex="-1" aria-label="<?php esc_attr_e( 'Check Row', 'fusion-builder' ); ?>">
							<i class="fusiona-check_circle_outline {{ checked ? 'fusiona-check_circle' : '' }}" data-name="check-circle" aria-hidden="true"></i>
						</a>
					</div>
					<div class="form-option-remove">
						<a href="#" class="fusion-sortable-remove" tabIndex="-1" aria-label="<?php esc_attr_e( 'Remove Row', 'fusion-builder' ); ?>">
							<span class="fusiona-trash-o"></span>
						</a>
					</div>
				</li>
			<# }); #>
		</ul>
		<hr style="margin: 10px 0;"/>
		<div class="form-options-settings">
			<a href="#" class="ui-button bulk-add-modal">{{ fusionBuilderText.bulk_add_button }}</a>
			<div class="toggle-values">
				<span>
				{{ fusionBuilderText.option_show_values }}
				</span>
				<label class="switch" for="form-options-settings">
					<input class="switch-input screen-reader-text fusion-hide-from-atts" name="fusion-anchor-target" id="form-options-settings" type="checkbox" value="" {{valuesEnabled ? 'checked' : '' }}>
					<span class="switch-label" data-on="On" data-off="Off"></span>
					<span class="switch-handle"></span><span class="label-helper-calc-on fusion-anchor-target">On</span>
					<span class="label-helper-calc-off fusion-anchor-target">Off</span>
				</label>
			</div>
		</div>
		<input class="option-values" type="hidden" id="{{ param.param_name }}" name="{{ param.param_name }}" value="{{ option_value }}">
	</div>
	<div class="fusion-form-option-template" style="display:none;">
		<div class="form-option-move-1">
			<a href="#" class="fusion-sortable-move" tabIndex="-1" aria-label="<?php esc_attr_e( 'Move Row', 'fusion-builder' ); ?>">
				<span class="fusiona-arrange"></span>
			</a>
		</div>
		<div class="form-option-label">
			<input type="text" value="" class="fusion-hide-from-atts label" />
		</div>
		<div class="form-option-value">
			<input type="text" value="" class="fusion-hide-from-atts value" />
		</div>
		<div class="form-option-checked">
			<a href="#" class="fusion-sortable-check" tabIndex="-1" aria-label="<?php esc_attr_e( 'Check Row', 'fusion-builder' ); ?>">
				<span class="fusiona-check_circle_outline"></i>
			</a>
		</div>
		<div class="form-option-remove">
			<a href="#" class="fusion-sortable-remove" tabIndex="-1" aria-label="<?php esc_attr_e( 'Remove Row', 'fusion-builder' ); ?>">
				<span class="fusiona-trash-o"></span>
			</a>
		</div>
	</div>
</div>

