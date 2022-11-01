<?php
/**
 * The dialog more options template file.
 *
 * @since 2.0
 * @package fusion-library
 */

?>
<script type="text/template" id="fusion-app-dialog-more-options">
<#
	var sidebarEditing = 'dialog' !== FusionApp.preferencesData.editing_mode ? true : false;
#>
	<div class="dialog-more-options-section-resize">

		<# if ( ! sidebarEditing ) { #>
		<div class="resize-section-wrap">
			<span class="resize-section-title"><?php esc_html_e( 'Resize Window', 'fusion-builder' ); ?></span>
			<div class="resize-icons">
				<div class="resize-icon has-tooltip resize-icon-default" data-resize-key="fusion-settings-dialog-default" aria-label="<?php esc_attr_e( 'Contract Popup', 'fusion-builder' ); ?>">
				<svg xmlns="http://www.w3.org/2000/svg" width="560" height="560" viewBox="232 0 560 560"><path d="M722 441v-91c0-21 14-35 35-35s35 14 35 35v140c0 38.5-31.5 70-70 70H582c-21 0-35-14-35-35s14-35 35-35h140v-49zm-420 0v-91c0-21-14-35-35-35s-35 14-35 35v140c0 38.5 31.5 70 70 70h140c21 0 35-14 35-35s-14-35-35-35H302v-49zm420-322v91c0 21 14 35 35 35s35-14 35-35V70c0-38.5-31.5-70-70-70H582c-21 0-35 14-35 35s14 35 35 35h140v49zm-420 0v91c0 21-14 35-35 35s-35-14-35-35V70c0-38.5 31.5-70 70-70h140c21 0 35 14 35 35s-14 35-35 35H302v49zM577 385H447c-22.1 0-40-17.9-40-40V215c0-22.1 17.9-40 40-40h130c22.1 0 40 17.9 40 40v130c0 22.1-17.9 40-40 40z"/></svg>
				</div>
				<div class="resize-icon has-tooltip resize-icon-large" data-resize-key="fusion-settings-dialog-large" aria-label="<?php esc_attr_e( 'Expand Popup', 'fusion-builder' ); ?>">
					<svg xmlns="http://www.w3.org/2000/svg" width="560" height="560" viewBox="231 0 560 560"><path d="M721 441v-91c0-21 14-35 35-35s35 14 35 35v140c0 38.5-31.5 70-70 70H581c-21 0-35-14-35-35s14-35 35-35h140v-49zm-420 0v-91c0-21-14-35-35-35s-35 14-35 35v140c0 38.5 31.5 70 70 70h140c21 0 35-14 35-35s-14-35-35-35H301v-49zm420-322v91c0 21 14 35 35 35s35-14 35-35V70c0-38.5-31.5-70-70-70H581c-21 0-35 14-35 35s14 35 35 35h140v49zm-420 0v91c0 21-14 35-35 35s-35-14-35-35V70c0-38.5 31.5-70 70-70h140c21 0 35 14 35 35s-14 35-35 35H301v49z"/><path d="M611 420H411c-22.1 0-40-17.9-40-40V180c0-22.1 17.9-40 40-40h200c22.1 0 40 17.9 40 40v200c0 22.1-17.9 40-40 40z"/></svg>
				</div>
			</div>
		</div>
		<div class="dialog-more-separator"></div>
		<# } #>
		<div class="dialog-more-options-section-menu">
			<ul class="dialog-more-menu-items">

				<# if ( '' !== elementOption ) { #>
				<li class="dialog-more-menu-item fusion-panel-shortcut" data-fusion-option="{{ elementOption }}"><span class="fas fa-cog"></span><?php esc_html_e( 'Global Options', 'fusion-builder' ); ?></li>
				<# } #>

				<li class="dialog-more-menu-item fusion-reset-default"><span class="fusiona-undo"></span><?php esc_html_e( 'Reset to Default', 'fusion-builder' ); ?></li>

				<# if ( '' !== helpURL ) { #>
				<li class="dialog-more-menu-item fusion-help-article"><a href="{{ helpURL  }}" target="_blank"><span class="fas fa-question-circle"></span><?php esc_html_e( 'Help Article', 'fusion-builder' ); ?></a></li>
				<# } #>

			</ul>
		</div>
		<div class="dialog-more-separator"></div>
		<div class="dialog-more-options-section-remove-item">
			<div class="dialog-more-remove-item">
				<span class="fusiona-trash-o"></span>
				<div class="dialog-more-remote-title"><?php esc_html_e( 'Remove Item', 'fusion-builder' ); ?></div>
			</div>
		</div>
	</div>
</script>
