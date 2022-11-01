<?php
/**
 * Underscore.js template.
 *
 * @since 2.0
 * @package fusion-library
 */

?>
<#
var fieldId     = 'undefined' === typeof param.param_name ? param.id : param.param_name,
	export_text = 'undefined' === typeof param.text ? fusionBuilderText.upload_image : param.text,
	context     = 'undefined' === typeof param.context ? 'TO' : param.context,
	data        = 'TO' === context ? JSON.stringify( FusionApp.settings ) : JSON.stringify( FusionPageBuilder.options.fusionExport.getFusionMeta() );
#>
<div class="fusion-form-radio-button-set ui-buttonset fusion-export-mode">
	<input type="hidden" id="fusion-export-mode" name="fusion-export-mode" value="copy" class="fusion-dont-update button-set-value" />
	<a href="#" class="ui-button buttonset-item ui-state-active" data-value="copy" aria-label="<?php esc_attr_e( 'Code', 'Avada' ); ?>"><?php esc_attr_e( 'Code', 'Avada' ); ?></a>
	<a href="#" class="ui-button buttonset-item" data-value="download" aria-label="<?php esc_attr_e( 'File', 'Avada' ); ?>"><?php esc_attr_e( 'File', 'Avada' ); ?></a>

	<# if ( ! shouldRenderDemos && true === FusionApp.data.singular && 'undefined' !== typeof FusionApp.data.savedPageOptions ) { #>
		<a href="#" class="ui-button buttonset-item" data-value="database" aria-label="<?php esc_attr_e( 'Database', 'Avada' ); ?>"><?php esc_attr_e( 'Database', 'Avada' ); ?></a>
	<# } #>
</div>

<div class="fusion-export-options">

	<div data-id="copy" class="fusion-copy-export active">
		<textarea id="export-code-value" rows="5" class="fusion-dont-update" data-context="{{ context }}">{{ data }}</textarea>
		<input type='button' id="fusion-export-copy" class='button fusion-builder-export-button' value='<?php esc_attr_e( 'Copy to Clipboard', 'Avada' ); ?>' />
	</div>

	<div data-id="download" class="fusion-export">
		<p><?php esc_html_e( 'Click the export button to export your current set of options as a json file.', 'Avada' ); ?></p>
		<input type='button' id="fusion-export-file" class='button fusion-builder-export-button' value='<?php esc_attr_e( 'Export', 'Avada' ); ?>' data-context="{{ context }}" />
	</div>

	<# if ( ! shouldRenderDemos && true === FusionApp.data.singular && 'undefined' !== typeof FusionApp.data.savedPageOptions ) { #>
		<div data-id="database" class="fusion-page-options-save">
			<input type="text" id="fusion-new-page-options-name" value="" placeholder="<?php esc_attr_e( 'Enter a name', 'Avada' ); ?>" class="fusion-dont-update">
			<a href="#" id="fusion-page-options-save" class="button fusion-builder-save-button" data-post_id="{{ FusionApp.data.postDetails.post_id }}" data-post_type="{{ FusionApp.data.postDetails.post_type }}">
				<?php esc_html_e( 'Save Page Options', 'Avada' ); ?>
			</a>
		</div>
	<# } #>
</div>
