<?php
/**
 * An underscore.js template.
 *
 * @package fusion-builder
 */

?>
<script type="text/template" id="fusion-builder-modules-template">
	<div class="fusion-builder-modal-top-container fusion-has-close-on-top">
		<h2 class="fusion-builder-settings-heading">
			{{ fusionBuilderText.select_element }}
			<input type="text" class="fusion-elements-filter" placeholder="{{ fusionBuilderText.search_elements }}" />
		</h2>
		<div class="fusion-builder-modal-close fusiona-plus2"></div>

		<ul class="fusion-tabs-menu">
			<# if ( 'undefined' !== typeof components && components.length && 0 < componentsCounter ) { #>
				<li class=""><a href="#template-elements">{{ fusionBuilderText.layout_section_elements }}</a></li>
			<# } #>
			<# if ( 'undefined' !== typeof form_components && form_components.length && 'fusion_form' === fusionBuilderConfig.post_type ) { #>
				<li class=""><a href="#form-elements">{{ fusionBuilderText.form_elements }}</a></li>
			<# } #>
			<li class=""><a href="#default-elements">{{ fusionBuilderText.builder_elements }}</a></li>
			<# if ( true !== FusionPageBuilderApp.shortcodeGenerator ) { #>
				<li class=""><a href="#custom-elements">{{ fusionBuilderText.library_elements }}</a></li>
			<# } #>
			<# if ( true === FusionPageBuilderApp.shortcodeGenerator ) { #>
				<li class=""><a href="#default-columns">{{ fusionBuilderText.columns }}</a></li>
			<# } #>
			<# if ( 'false' == FusionPageBuilderApp.innerColumn  && true !== FusionPageBuilderApp.shortcodeGenerator ) { #>
				<li class=""><a href="#inner-columns">{{ fusionBuilderText.inner_columns }}</a></li>
			<# } #>
			<# if ( '1' === fusionBuilderConfig.studio_status ) { #>
				<li><a href="#fusion-builder-elements-studio"><i class="fusiona-avada-logo"></i> <?php esc_html_e( 'Studio', 'fusion-builder' ); ?></a></li>
			<# } #>
		</ul>
	</div>

	<div class="fusion-builder-main-settings fusion-builder-main-settings-full has-group-options">
		<div class="fusion-builder-all-elements-container">
			<div class="fusion-tabs">

			<# if ( 'undefined' !== typeof components && components.length && 0 < componentsCounter ) { #>
				<div id="template-elements" class="fusion-tab-content">
					<ul class="fusion-builder-all-modules fusion-template-components fusion-clearfix">
						<# _.each( components, function( module ) { #>
							<#
							var additionalClass = false !== module.components_per_template && FusionPageBuilderViewManager.countElementsByType( module.label ) >= module.components_per_template ? ' fusion-builder-disabled-element' : '';

							// If element is not supposed to be active on page edit type, skip.
							if ( 'object' === typeof module.templates && ! module.templates.includes( fusionBuilderConfig.template_category ) ) {
								return false;
							}
							var components_per_template_tooltip = fusionBuilderText.template_max_use_limit + ' ' + module.components_per_template
							components_per_template_tooltip     = ( 2 > module.components_per_template ) ? components_per_template_tooltip + ' ' + fusionBuilderText.time : components_per_template_tooltip + ' ' + fusionBuilderText.times;
							components_per_template_tooltip = 'string' === typeof module.template_tooltip ? module.template_tooltip : components_per_template_tooltip;
							#>
							<li class="{{ module.label }} fusion-builder-element{{ additionalClass }}">
								<h4 class="fusion_module_title">
									<# if ( 'undefined' !== typeof fusionAllElements[module.label].icon ) { #>
										<div class="fusion-module-icon {{ fusionAllElements[module.label].icon }}"></div>
									<# } #>
									{{{ module.title }}}
								</h4>
								<# if ( false !== module.components_per_template && FusionPageBuilderViewManager.countElementsByType( module.label ) >= module.components_per_template ) { #>
									<span class="fusion-tooltip">{{ components_per_template_tooltip }}</span>
								<# } #>
								<span class="fusion_module_label">{{ module.label }}</span>
							</li>
						<# } ); #>
					</ul>
			</div>
			<# } #>

			<# if ( 'undefined' !== typeof form_components && form_components.length && 'fusion_form' === fusionBuilderConfig.post_type ) { #>
				<div id="form-elements" class="fusion-tab-content">
					<ul class="fusion-builder-all-modules fusion-form-components fusion-clearfix">
						<# _.each( form_components, function( module ) { #>
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
					</ul>
			</div>
			<# } #>

				<div id="default-elements" class="fusion-tab-content">
					<ul class="fusion-builder-all-modules">
						<# _.each( generator_elements, function( module ) { #>
							<#
							if ( 'fusion_form' === fusionBuilderConfig.post_type && 'fusion_form' === module.label ) {
								return;
							}
							if ( 'post_cards' === fusionBuilderConfig.template_category && 'fusion_post_cards' === module.label ) {
								return;
							}
							// If element is not supposed to be active on page edit type, skip.
							if ( 'object' === typeof module.templates && ! module.templates.includes( fusionBuilderConfig.template_category ) ) {
								return false;
							}
							#>
							<# var additionalClass = ( 'undefined' !== typeof module.generator_only ) ? ' fusion-builder-element-generator' : '';

								if ( false !== module.components_per_template && FusionPageBuilderViewManager.countElementsByType( module.label ) >= module.components_per_template ) {
									additionalClass += ' fusion-builder-disabled-element';
								}
							#>
							<li class="{{ module.label }} fusion-builder-element{{ additionalClass }}">
								<h4 class="fusion_module_title">
									<# if ( 'undefined' !== typeof fusionAllElements[ module.label ].icon ) { #>
										<div class="fusion-module-icon {{ fusionAllElements[module.label].icon }}"></div>
									<# } #>
									{{{ module.title }}}
								</h4>
								<# if ( 'undefined' !== typeof module.generator_only ) { #>
									<span class="fusion-tooltip">{{ fusionBuilderText.generator_elements_tooltip }}</span>
								<# } #>

								<span class="fusion_module_label">{{ module.label }}</span>
							</li>
						<# } ); #>
					</ul>
				</div>
				<# if ( FusionPageBuilderApp.innerColumn == 'false' && FusionPageBuilderApp.shortcodeGenerator !== true ) { #>
					<div id="inner-columns" class="fusion-tab-content">
						<?php echo fusion_builder_inner_column_layouts(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</div>
				<# } #>
				<# if ( FusionPageBuilderApp.shortcodeGenerator === true ) { #>
					<div id="default-columns" class="fusion-tab-content">
						<?php echo fusion_builder_generator_column_layouts(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</div>
				<# } #>
				<# if ( '1' === fusionBuilderConfig.studio_status ) { #>
					<div id="fusion-builder-elements-studio" class="fusion-tab-content">
						<?php if ( Avada()->registration->is_registered() ) : ?>
							<div class="studio-wrapper">
								<aside>
									<ul></ul>
								</aside>
								<section>
									<div class="fusion-builder-element-content fusion-loader"><span class="fusion-builder-loader"></span><span class="awb-studio-import-status"></span></div>
									<ul class="studio-imports"></ul>
								</section>
								<?php AWB_Studio::studio_import_options_template(); ?>
							</div>
						<?php else : ?>
							<h2 class="awb-studio-not-reg"><?php esc_html_e( 'The product needs to be registered to access the Avada Studio.', 'fusion-builder' ); ?></h2>
						<?php endif; ?>
					</div>
				<# } #>
				<div id="custom-elements" class="fusion-tab-content"></div>
			</div>
		</div>
	</div>
</script>
