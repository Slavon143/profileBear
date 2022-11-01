<?php
/**
 * Underscore.js template.
 *
 * @since 2.0
 * @package fusion-library
 */

?>
<div class="awb-typography">
	<# var allowGlobalButton = ( 'undefined' !== typeof param.allow_globals && false === param.allow_globals ? false : true ); #>
	<# if ( 'undefined' === typeof FusionApp && allowGlobalButton ) { #>
	<a class="option-global-typography awb-quick-set" href="JavaScript:void(0);" aria-label="<?php esc_html_e( 'Global Typography', 'Avada' ); ?>'"><i class="fusiona-globe" aria-hidden="true"></i></a>
	<# } #>
	<#
	var optionType,
		optionId,
		elOptions  = param.choices || {},
		defaults   = {
			'font-family': 'typography',
			'font-size': 'font_size',
			'line-height': 'line_height',
			'letter-spacing': 'letter_spacing',
			'text-transform': false,
			'margin-top': false,
			'margin-bottom': false,
			'color': false
		},
		options  = jQuery.extend( {}, defaults, elOptions ),
		saveData;

		if ( 'undefined' === typeof param.param_name ) {
			if ( 'undefined' !== typeof type && 'PO' === type ) {
				optionType = 'PO';
			} else {
				optionType = 'TO';
			}
			optionId   = param.id;
		} else {
			optionType = 'EO';
			optionId   = param.param_name;
		}

		if ( 'TO' === optionType ) {
			saveData = Object.assign( {}, FusionApp.settings );
			if ( 'object' === typeof saveData[ optionId ] ) {
				saveData = saveData[ optionId ];
			} else {
				saveData[ optionId ] = param.default;
			}
		} else if ( 'PO' === optionType ) {
			if ( 'object' === typeof option_value ) {
				saveData = option_value;
			} else {
				saveData = {};
			}
		} else {
			saveData = atts.params;
		}
	#>
	<# if ( false !== options['font-family'] ) { #>
		<#
		var fontId        = options['font-family'],
			familyId      = 'EO' === optionType ? 'fusion_font_family_' + fontId : 'font-family',
			familyDefault = 'object' === typeof param.default && 'undefined' !== typeof param.default['font-family'] ? param.default['font-family'] : '';
			familyValue   = 'undefined' !== typeof saveData[ familyId ] ? saveData[ familyId ] : familyDefault;

			if ( 'TO' === optionType || 'PO' === optionType ) {
				familyId = optionId + '[' + familyId + ']';
			}
		#>
		<div class="input-wrapper family-selection awb-contains-global">
			<div class="awb-typo-heading">
				<label><?php esc_html_e( 'Font Family', 'Avada' ); ?></label>
				<# if ( allowGlobalButton ) { #>
					<span class="awb-global"><i class="fusiona-globe" aria-hidden="true"></i></span>
				<# } #>
			</div>
			<div class="fusion-skip-init fusion-select-field<?php echo ( is_rtl() ) ? ' fusion-select-field-rtl' : ''; ?>">
				<div class="fusion-select-preview-wrap">
					<span class="fusion-select-preview">
						<# if ( '' !== familyValue ) { #>
							<# if ( window.awbTypographySelect.isAdobeFont( familyValue ) ) { #>
								{{ _.unescape( window.awbTypographySelect.getAdobeDisplayName( familyValue ) ) }}
							<# } else { #>
								{{ _.unescape( familyValue ) }}
							<# } #>
						<# } else { #>
							<span class="fusion-select-placeholder"><?php esc_attr_e( 'Select Font Family', 'Avada' ); ?></span>
						<# } #>
					</span>
					<div class="fusiona-arrow-down"></div>
				</div>
				<div class="fusion-select-dropdown">
					<div class="fusion-select-search">
						<input type="text" class="fusion-hide-from-atts fusion-dont-update" placeholder="<?php esc_attr_e( 'Search Font Families', 'Avada' ); ?>" />
					</div>
					<div class="fusion-select-options"></div>
				</div>
				<input type="hidden" id="{{{ familyId }}}" name="{{{ familyId }}}" value="{{ _.unescape( familyValue ) }}" data-default="{{ familyDefault }}" class="input-font_family fusion-select-option-value awb-typo-input" data-subset="font-family">
			</div>
			<span class="awb-global-label"></span>
		</div>

		<# if ( 'TO' === optionType || 'PO' === optionType ) { #>
		<div class="input-wrapper font-backup fusion-font-backup-wrapper">
			<div class="awb-typo-heading">
				<label><?php esc_html_e( 'Backup Font', 'Avada' ); ?></label>
			</div>

			<div class="fusion-skip-init fusion-select-field<?php echo ( is_rtl() ) ? ' fusion-select-field-rtl' : ''; ?>">
				<div class="fusion-select-preview-wrap">
					<span class="fusion-select-preview">
						<# if ( 'string' === typeof saveData['font-backup'] && '' !== saveData['font-backup'] ) { #>
							{{ _.unescape( saveData['font-backup'] ) }}
						<# } else { #>
							<span class="fusion-select-placeholder"><?php esc_attr_e( 'Select Backup Font Family', 'Avada' ); ?></span>
						<# } #>
					</span>
					<div class="fusiona-arrow-down"></div>
				</div>
				<div class="fusion-select-dropdown">
					<div class="fusion-select-search">
						<input type="text" class="fusion-hide-from-atts fusion-dont-update" placeholder="<?php esc_attr_e( 'Search Font Families', 'Avada' ); ?>" />
					</div>
					<div class="fusion-select-options"></div>
				</div>
				<input type="hidden" id="fusion-typography-font-backup-{{{ param.id }}}" name="{{ optionId }}[font-backup]" class="fusion-select-option-value">
			</div>
		</div>
		<# } #>

		<#
		var variantId      = 'EO' === optionType ? 'fusion_font_variant_' + fontId : 'variant',
			variantDefault = 'object' === typeof param.default && 'undefined' !== typeof param.default['variant'] ? param.default['variant'] : '',
			variantValue   = 'undefined' !== typeof saveData[ variantId ] ? saveData[ variantId ] : variantDefault;

		if ( 'TO' === optionType || 'PO' === optionType ) {
			var variantId    = 'variant',
				variantValue = 'undefined' !== typeof saveData[ variantId ] ? saveData[ variantId ] : param.default['font-weight'];
			if ( 'undefined' !== typeof saveData['font-weight'] ) {
				variantValue = saveData['font-weight'];
				if ( 'string' !== typeof variantValue ) {
					variantValue = variantValue.toString();
				}
				if ( 'string' === typeof saveData['font-style'] && 'italic' === saveData['font-style'] ) {
					variantValue += 'italic';
				}
			}
			variantId = optionId + '[' + variantId + ']';
		} else {
			var variantId      = 'fusion_font_variant_' + fontId,
				variantDefault = 'object' === typeof param.default && 'undefined' !== typeof param.default['variant'] ? param.default['variant'] : '',
				variantValue   = 'undefined' !== typeof saveData[ variantId ] ? saveData[ variantId ] : variantDefault;
		}
		#>
		<div class="input-wrapper fusion-builder-typography" style="display:none">
			<div class="awb-typo-heading">
				<label><?php esc_html_e( 'Variant', 'Avada' ); ?></label>
			</div>
			<div class="input fusion-typography-select-wrapper">
				<select name="{{ variantId }}" class="input-variant variant" id="{{ variantId }}" data-default="{{ variantDefault }}" data-value="{{ variantValue }}" data-subset="variant"></select>
				<div class="fusiona-arrow-down"></div>
			</div>
		</div>
	<# } #>

	<#
	var stringMap = {};
	stringMap['font-size']      = '<?php esc_attr_e( 'Font Size', 'Avada' ); ?>';
	stringMap['line-height']    = '<?php esc_attr_e( 'Line Height', 'Avada' ); ?>';
	stringMap['letter-spacing'] = '<?php esc_attr_e( 'Letter Spacing', 'Avada' ); ?>';
	stringMap['margin-top']     = '<?php esc_attr_e( 'Margin Top', 'Avada' ); ?>';
	stringMap['margin-bottom']  = '<?php esc_attr_e( 'Margin Bottom', 'Avada' ); ?>';
	#>
	<# _.each( [ 'font-size', 'line-height', 'letter-spacing', 'margin-top', 'margin-bottom' ], function( field ) { #>
		<# if ( false !== options[ field ] ) { #>
			<#
			var fieldId      = 'EO' === optionType ? options[ field ] : field,
				fieldDefault = 'object' === typeof param.default && 'undefined' !== typeof param.default[ field ] ? param.default[ field ] : '',
				fieldValue   = 'undefined' !== typeof saveData[ fieldId ] ? saveData[ fieldId ] : fieldDefault,
				containsGlobal = -1 === field.indexOf( 'margin' ) ? ' awb-contains-global' : '';

			if ( 'TO' === optionType || 'PO' === optionType ) {
				fieldId = optionId + '[' + fieldId + ']';
			}
			#>
			<div class="input-wrapper{{ containsGlobal }} third">
				<div class="awb-typo-heading">
					<label>{{{ stringMap[ field ] }}}</label>
					<# if ( 'margin-top' !== field && 'margin-bottom' !== field && allowGlobalButton ) { #>
						<span class="awb-global"><i class="fusiona-globe" aria-hidden="true"></i></span>
					<# } #>
				</div>
				<div class="input">
					<input type="text" data-subset="{{ field }}" class="awb-typo-input" name="{{ fieldId }}" value="{{ fieldValue }}">
					<span class="awb-global-label"></span>
				</div>
			</div>
		<# } #>
	<# } ); #>

	<# if ( false !== options['text-transform'] ) { #>
		<#
		var fieldId      =  'EO' === optionType ? options['text-transform'] : 'text-transform',
			fieldDefault = 'object' === typeof param.default && 'undefined' !== typeof param.default['text-transform'] ? param.default['text-transform'] : '',
			fieldValue   = 'undefined' !== typeof saveData[ fieldId ] ? saveData[ fieldId ] : fieldDefault,
			choices      = {};

		if ( 'TO' === optionType || 'PO' === optionType ) {
			fieldId = optionId + '[' + fieldId + ']';
		}

		if ( ! param.text_transform_no_inherit || true !== param.text_transform_no_inherit ) {
			choices[''] = {
				icon: '<span class="fusiona-cog onlyIcon"></span>',
				label: '<?php esc_attr_e( 'Default', 'Avada' ); ?>'
			};
		}
		choices['none']       = {
			icon: '<span class="fusiona-minus onlyIcon"></span>',
			label: '<?php esc_attr_e( 'None', 'Avada' ); ?>'
		};
		choices['uppercase']       = {
			icon: '<span class="fusiona-uppercase onlyIcon"></span>',
			label: '<?php esc_attr_e( 'Uppercase', 'Avada' ); ?>'
		};
		choices['lowercase']       = {
			icon: '<span class="fusiona-lowercase onlyIcon"></span>',
			label: '<?php esc_attr_e( 'Lowercase', 'Avada' ); ?>'
		};
		choices['capitalize']       = {
			icon: '<span class="fusiona-caps onlyIcon"></span>',
			label: '<?php esc_attr_e( 'Capitalize', 'Avada' ); ?>'
		};
		#>
		<div class="input-wrapper awb-contains-global">
			<div class="awb-typo-heading">
				<label><?php esc_attr_e( 'Text Transform', 'Avada' ); ?></label>
				<# if ( allowGlobalButton ) { #>
					<span class="awb-global"><i class="fusiona-globe" aria-hidden="true"></i></span>
				<# } #>
			</div>
			<div class="input radio-button-set ui-buttonset">
				<input type="hidden" id="{{ fieldId }}" name="{{ fieldId }}" value="{{ fieldValue }}" class="button-set-value" data-subset="text-transform"/>
				<# _.each( choices, function( data, value ) { #>
					<# var selected  = value == fieldValue ? ' ui-state-active' : ''; #>
					<a href="#" class="ui-button buttonset-item{{ selected }} has-tooltip" data-value="{{ value }}" aria-label="{{ data.label }}"><div class="fusion-button-set-title">{{{ data.icon }}}</div></a>
				<# } ); #>
			</div>
			<span class="awb-global-label"></span>
		</div>
	<# } #>

	<# if ( false !== options['color'] ) { #>
		<#
		var fieldId      =  'EO' === optionType ? options['color'] : 'color',
			fieldDefault = 'object' === typeof param.default && 'undefined' !== typeof param.default['color'] ? param.default['color'] : '',
			fieldValue   = 'undefined' !== typeof saveData[ fieldId ] ? saveData[ fieldId ] : fieldDefault;

		if ( 'TO' === optionType || 'PO' === optionType ) {
			fieldId = optionId + '[' + fieldId + ']';
		}
		#>
		<div class="input-wrapper">
			<div class="awb-typo-heading">
				<label><?php esc_attr_e( 'Font Color', 'Avada' ); ?></label>
			</div>
			<input
				id="{{ fieldId }}"
				name="{{ fieldId }}"
				class="fusion-builder-color-picker-hex color-picker"
				type="text"
				value="{{ fieldValue }}"
				data-alpha="true"
				data-default="{{ fieldDefault }}"
			/>
		</div>
	<# } #>
</div>
