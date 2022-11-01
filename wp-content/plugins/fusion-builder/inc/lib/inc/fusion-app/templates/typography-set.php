<?php
/**
 * The typography set template.
 *
 * @since 2.0
 * @package fusion-library
 */

?>
<script type="text/template" id="awb-typo-set-template">
<li class="awb-typo-set-item" data-slug="{{ slug }}">
	<div class="awb-set-title">
		<span class="label">{{{ data.label }}}</span>
		<div class="actions">
			<span class="fusiona-pen"></span>
			<# if ( 'undefined' === typeof data.not_removable || ! data.not_removable ) { #>
				<span class="fusiona-trash-o"></span>
			<# } #>
		</div>
	</div>
	<div class="awb-typo-set-content">
		<div class="awb-typography">
			<#
			var fieldValue = data['label'],
				fieldId    = id + '[label]';
			#>
			<div class="input-wrapper">
				<div class="awb-typo-heading">
					<label><?php esc_html_e( 'Typography Set Name', 'fusion-builder' ); ?></label>
				</div>
				<div class="input">
					<input type="text" data-subset="label" class="awb-typo-input" name="{{ fieldId }}" value="{{ fieldValue }}">
				</div>
			</div>

			<#
			var familyValue = data['font-family'],
				familyId    = id + '[font-family]';
			#>
			<div class="input-wrapper family-selection">
				<div class="awb-typo-heading">
					<label><?php esc_html_e( 'Font Family', 'fusion-builder' ); ?></label>
				</div>
				<div class="fusion-skip-init fusion-select-field<?php echo ( is_rtl() ) ? ' fusion-select-field-rtl' : ''; ?>">
					<div class="fusion-select-preview-wrap">
						<span class="fusion-select-preview">
							<# if ( 'string' === typeof familyValue && '' !== familyValue ) { #>
								<# if ( window.awbTypographySelect.isAdobeFont( familyValue ) ) { #>
									{{ window.awbTypographySelect.getAdobeDisplayName( familyValue ) }}
								<# } else { #>
									{{ familyValue }}
								<# } #>
							<# } else { #>
								<span class="fusion-select-placeholder"><?php esc_attr_e( 'Select Font Family', 'fusion-builder' ); ?></span>
							<# } #>
						</span>
						<div class="fusiona-arrow-down"></div>
					</div>
					<div class="fusion-select-dropdown">
						<div class="fusion-select-search">
							<input type="text" class="fusion-hide-from-atts fusion-dont-update" placeholder="<?php esc_attr_e( 'Search Font Families', 'fusion-builder' ); ?>" />
						</div>
						<div class="fusion-select-options"></div>
					</div>
					<input type="hidden" id="{{{ familyId }}}" name="{{{ familyId }}}" value="{{ familyValue }}" class="input-font_family fusion-select-option-value awb-typo-input" data-subset="font-family">
				</div>
			</div>

			<div class="input-wrapper font-backup fusion-font-backup-wrapper">
				<div class="awb-typo-heading">
					<label><?php esc_html_e( 'Backup Font', 'fusion-builder' ); ?></label>
				</div>

				<div class="fusion-skip-init fusion-select-field<?php echo ( is_rtl() ) ? ' fusion-select-field-rtl' : ''; ?>">
					<div class="fusion-select-preview-wrap">
						<span class="fusion-select-preview">
							<# if ( 'string' === typeof data['font-backup'] && '' !== data['font-backup'] ) { #>
								{{ data['font-backup'] }}
							<# } else { #>
								<span class="fusion-select-placeholder"><?php esc_attr_e( 'Select Backup Font Family', 'fusion-builder' ); ?></span>
							<# } #>
						</span>
						<div class="fusiona-arrow-down"></div>
					</div>
					<div class="fusion-select-dropdown">
						<div class="fusion-select-search">
							<input type="text" class="fusion-hide-from-atts fusion-dont-update" placeholder="<?php esc_attr_e( 'Search Font Families', 'fusion-builder' ); ?>" />
						</div>
						<div class="fusion-select-options"></div>
					</div>
					<input type="hidden" id="fusion-typography-font-backup-{{{ id }}}" name="{{ id }}[font-backup]" value="{{ data['font-backup'] }}" class="fusion-select-option-value" data-subset="font-backup">
				</div>
			</div>

			<#
			var variantId    = id + '[variant]',
				variantValue = data.variant;
			#>
			<div class="input-wrapper fusion-builder-typography" style="display:none">
				<div class="awb-typo-heading">
					<label><?php esc_html_e( 'Variant', 'fusion-builder' ); ?></label>
				</div>
				<div class="input fusion-typography-select-wrapper">
					<select name="{{ variantId }}" class="input-variant variant" id="{{ variantId }}" data-default="400" data-value="{{ variantValue }}" data-subset="variant"></select>
					<div class="fusiona-arrow-down"></div>
				</div>
			</div>

			<#
			var stringMap = {};
			stringMap['font-size']      = '<?php esc_attr_e( 'Font Size', 'fusion-builder' ); ?>';
			stringMap['line-height']    = '<?php esc_attr_e( 'Line Height', 'fusion-builder' ); ?>';
			stringMap['letter-spacing'] = '<?php esc_attr_e( 'Letter Spacing', 'fusion-builder' ); ?>';
			#>
			<# _.each( [ 'font-size', 'line-height', 'letter-spacing' ], function( field ) { #>
				<#
				var fieldId        = id + '[' + field + ']',
					fieldValue     = data[ field ];
				#>
				<div class="input-wrapper third">
					<div class="awb-typo-heading">
						<label>{{{ stringMap[ field ] }}}</label>
					</div>
					<div class="input">
						<input type="text" data-subset="{{ field }}" class="awb-typo-input" name="{{ fieldId }}" value="{{ fieldValue }}">
					</div>
				</div>
			<# } ); #>

			<#
			var fieldId    = id + '[text-transform]',
				fieldValue = data['text-transform'],
				choices    = {};

			choices['none']       = {
				icon: '<span class="fusiona-minus onlyIcon"></span>',
				label: '<?php esc_attr_e( 'None', 'fusion-builder' ); ?>'
			};
			choices['uppercase']       = {
				icon: '<span class="fusiona-uppercase onlyIcon"></span>',
				label: '<?php esc_attr_e( 'Uppercase', 'fusion-builder' ); ?>'
			};
			choices['lowercase']       = {
				icon: '<span class="fusiona-lowercase onlyIcon"></span>',
				label: '<?php esc_attr_e( 'Lowercase', 'fusion-builder' ); ?>'
			};
			choices['capitalize']       = {
				icon: '<span class="fusiona-caps onlyIcon"></span>',
				label: '<?php esc_attr_e( 'Capitalize', 'fusion-builder' ); ?>'
			};
			#>
			<div class="input-wrapper">
				<div class="awb-typo-heading">
					<label><?php esc_attr_e( 'Text Transform', 'fusion-builder' ); ?></label>
				</div>
				<div class="input radio-button-set ui-buttonset">
					<input type="hidden" id="{{ fieldId }}" name="{{ fieldId }}" value="{{ fieldValue }}" class="button-set-value" data-subset="text-transform"/>
					<# _.each( choices, function( data, value ) { #>
						<# var selected  = value == fieldValue ? ' ui-state-active' : ''; #>
						<a href="#" class="ui-button buttonset-item{{ selected }} has-tooltip" data-value="{{ value }}" aria-label="{{ data.label }}"><div class="fusion-button-set-title">{{{ data.icon }}}</div></a>
					<# } ); #>
				</div>
			</div>
		</div>
	</div>
</li>
</script>
