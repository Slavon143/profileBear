<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/template" id="fusion-builder-generator-modules-template">
	<div class="fusion-builder-modal-top-container">

		<div class="fusion-builder-modal-search">
			<label for="fusion-modal-search" class="fusiona-search"><span><?php esc_html_e( 'Search', 'fusion-builder' ); ?></span></label>
			<input type="text" id="fusion-modal-search" class="fusion-elements-filter" placeholder="{{ fusionBuilderText.search_elements }}" />
		</div>

		<ul class="fusion-tabs-menu">
			<li><a href="#default-elements">{{ fusionBuilderText.builder_elements }}</a></li>
			<li><a href="#default-columns">{{ fusionBuilderText.columns }}</a></li>
		</ul>
	</div>

	<div class="fusion-builder-main-settings fusion-builder-main-settings-full has-group-options">

		<div class="fusion-builder-all-elements-container">

			<div class="fusion-tabs">
				<div id="default-elements" class="fusion-tab-content">
					<ul class="fusion-builder-all-modules">
						<# _.each( generator_elements, function(module) { #>
							<#
							if ( 'post_cards' === FusionApp.data.template_category && 'fusion_post_cards' === module.label ) {
								return;
							}
							// If element is not supposed to be active on page edit type, skip.
							if ( 'object' === typeof module.templates && ! module.templates.includes( FusionApp.data.template_category ) ) {
								return false;
							}
							#>
							<li class="{{ module.label }} fusion-builder-element">
								<h4 class="fusion_module_title">
									<# if ( 'undefined' !== typeof fusionAllElements[module.label].icon ) { #>
										<div class="fusion-module-icon {{ fusionAllElements[module.label].icon }}"></div>
									<# } #>
									{{{ module.title }}}
								</h4>
								<span class="fusion_module_label">{{ module.label }}</span>
							</li>
						<# } ); #>

						<# for ( var i = 0; i < 16; i++ ) { #>
							<li class="spacer fusion-builder-element"></li>
						<# } #>
					</ul>
				</div>

				<div id="default-columns" class="fusion-tab-content">
					<?php echo fusion_builder_generator_column_layouts(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</div>

			</div>
		</div>
	</div>
</script>
