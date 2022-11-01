<?php
/**
 * Underscore.js template.
 *
 * @package fusion-builder
 */

?>
<script type="text/template" id="fusion-builder-block-module-settings-table-template">
<#
	var sidebarEditing = 'dialog' !== FusionApp.preferencesData.editing_mode && 'generated_element' !== atts.type ? true : false;
#>

	<div class="fusion-builder-modal-top-container">
	<# if ( sidebarEditing ) { #>
		<div class="ui-dialog-titlebar">

			<h2>
				{{{ atts.title }}}
			</h2>

			<div class="fusion-utility-menu-wrap">
				<span class="fusion-utility-menu fusiona-ellipsis"></span>
			</div>
			<button id="fusion-close-element-settings" type="button" class="fusiona-close-fb" aria-label="Close" role="button" title="Close">
		</div>
	<# } #>

		<ul class="fusion-tabs-menu">
			<li>
				<a href="#table" class="has-tooltip" aria-label="{{ fusionBuilderText.table }}">
					<span class="fusiona-child-items"></span>
					<span>{{ fusionBuilderText.table }}</span>
				</a>
			</li>
			<li>
				<a href="#table-options" class="has-tooltip" aria-label="{{ fusionBuilderText.table_options }}">
					<span class="fusiona-design-options"></span>
					<span>{{ fusionBuilderText.table_options }}</span>
				</a>
			</li>
		</ul>
	</div>

	<div class="fusion-builder-main-settings <# if ( sidebarEditing ) { #>fusion-builder-customizer-settings<# } #> fusion-builder-main-settings-full has-group-options">
		<div class="fusion-tabs">

			<div id="table-options" class="fusion-tab-content">

				<?php fusion_element_options_loop( 'fusionAllElements[atts.element_type].params' ); ?>

			</div>

			<div id="table" class="fusion-tab-content">

				<div class="fusion-child-sortables"></div>

			</div>

		</div>

	</div>

</script>
