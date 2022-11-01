<?php
/**
 * Underscore.js template.
 *
 * @since 2.0
 * @package fusion-library
 */

?>
<#
var fieldId           = 'undefined' === typeof param.param_name ? param.id : param.param_name,
	context           = 'undefined' === typeof param.context ? 'TO' : param.context,
	hasDemos          = 'object' === typeof param.demos && 'TO' === context && ! _.isEmpty( param.demos );
	shouldRenderDemos = 'object' === typeof param.demos && 'TO' === context;
#>
<div class="fusion-form-radio-button-set ui-buttonset fusion-import-mode">
	<input type="hidden" id="fusion-import-mode" name="fusion-import-mode" value="paste" class="fusion-dont-update button-set-value" />
	<a href="#" class="ui-button buttonset-item ui-state-active" data-value="paste" aria-label="<?php esc_attr_e( 'Code', 'fusion-builder' ); ?>"><?php esc_attr_e( 'Code', 'fusion-builder' ); ?></a>
	<a href="#" class="ui-button buttonset-item" data-value="upload" aria-label="<?php esc_attr_e( 'File', 'fusion-builder' ); ?>"><?php esc_attr_e( 'File', 'fusion-builder' ); ?></a>
	<# if ( hasDemos ) { #>
		<a href="#" class="ui-button buttonset-item" data-value="demo" aria-label="<?php esc_attr_e( 'Prebuilt Site', 'fusion-builder' ); ?>"><?php esc_attr_e( 'Prebuilt Site', 'fusion-builder' ); ?></a>
	<# } #>

	<# if ( ! shouldRenderDemos && true === FusionApp.data.singular && 'undefined' !== typeof FusionApp.data.savedPageOptions ) { #>
		<a href="#" class="ui-button buttonset-item" data-value="saved-page-options" aria-label="<?php esc_attr_e( 'Database', 'fusion-builder' ); ?>"><?php esc_attr_e( 'Database', 'fusion-builder' ); ?></a>
	<# } #>
</div>

<div class="fusion-import-options">

	<div data-id="paste" class="fusion-paste-import active">
		<textarea id="import-code-value" rows="5" class="fusion-dont-update"></textarea>
	</div>

	<# if ( hasDemos ) { #>
		<div data-id="demo" class="fusion-demo-import">
			<div class="fusion-select-field<?php echo ( is_rtl() ) ? ' fusion-select-field-rtl' : ''; ?>">
				<div class="fusion-select-preview-wrap">
					<span class="fusion-select-preview">
						<span class="fusion-select-placeholder"><?php esc_attr_e( 'Select a Prebuilt Site', 'fusion-builder' ); ?></span>
					</span>
					<div class="fusiona-arrow-down"></div>
				</div>
				<div class="fusion-select-dropdown">
					<div class="fusion-select-search">
						<input type="text" class="fusion-hide-from-atts fusion-dont-update" placeholder="<?php esc_attr_e( 'Search Prebuilt Sites', 'fusion-builder' ); ?>" />
					</div>
					<div class="fusion-select-options">
						<# _.each( param.demos, function( name, value ) { #>
							<label class="fusion-select-label" data-value="{{ value }}">{{{ name }}}</label>
						<# }); #>
					</div>
				</div>
				<input type="hidden" id="fusion-demo-import" name="demo-import" class="fusion-dont-update fusion-select-option-value">
			</div>
		</div>
	<# } #>

	<# if ( ! shouldRenderDemos && true === FusionApp.data.singular && 'undefined' !== typeof FusionApp.data.savedPageOptions ) { #>
		<div data-id="saved-page-options" class="fusion-page-options-import">
			<div class="fusion-select-field<?php echo ( is_rtl() ) ? ' fusion-select-field-rtl' : ''; ?>">
				<div class="fusion-select-preview-wrap">
					<span class="fusion-select-preview">
						<span class="fusion-select-placeholder"><?php esc_attr_e( 'Select A Page Option Set', 'fusion-builder' ); ?></span>
					</span>
					<div class="fusiona-arrow-down"></div>
				</div>
				<div class="fusion-select-dropdown">
					<div class="fusion-select-search">
						<input type="text" class="fusion-hide-from-atts fusion-dont-update" placeholder="<?php esc_attr_e( 'Select A Page Option Set', 'fusion-builder' ); ?>" />
					</div>
					<div class="fusion-select-options">
						<# _.each( FusionApp.data.savedPageOptions, function( option ) { #>
							<label class="fusion-select-label" data-value="{{ option.id }}">{{{ option.title }}}</label>
						<# }); #>
					</div>
				</div>
				<input type="hidden" id="fusion-page-options-import" name="fusion-page-options-import" class="fusion-dont-update fusion-select-option-value">
			</div>
			<input type="hidden" id="fusion-page-options-nonce" value="<?php echo esc_attr( wp_create_nonce( 'fusion-page-options-nonce' ) ); ?>" />
		</div>
	<# } #>

	<div data-id="upload" class="fusion-upload-import">
		<p><?php esc_attr_e( 'Click the import button to select a previously exported JSON file to upload and import.', 'fusion-builder' ); ?></p>
		<input type="hidden" id="{{ fieldId }}" name="{{ fieldId }}" class="regular-text fusion-builder-import-value" value='{{ option_value }}' />
		<input type="file" class="fusion-import-file-input" style="display:none;opacity:0;">
	</div>
</div>

<input type="button" class="button fusion-builder-import-button" value="<?php esc_attr_e( 'Import', 'fusion-builder' ); ?>"" data-type="json" data-title="<?php esc_attr_e( 'Import', 'fusion-builder' ); ?>" data-context="{{ context }}"/>

<# if ( ! shouldRenderDemos && true === FusionApp.data.singular && 'undefined' !== typeof FusionApp.data.savedPageOptions ) { #>
<input type="button" class="button fusion-builder-delete-button" value="<?php esc_attr_e( 'Delete', 'fusion-builder' ); ?>" data-title="<?php esc_attr_e( 'Delete', 'fusion-builder' ); ?>" data-context="{{ context }}" style="display:none;"/>
<# } #>
