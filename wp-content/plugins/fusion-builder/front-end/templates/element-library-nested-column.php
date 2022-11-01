<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/template" id="fusion-builder-nested-column-modules-template">
	<div class="fusion-builder-modal-top-container">
		<div class="fusion-builder-modal-search">
			<label for="fusion-modal-search" class="fusiona-search"><span><?php esc_html_e( 'Search', 'fusion-builder' ); ?></span></label>
			<input type="text" id="fusion-modal-search" class="fusion-elements-filter" placeholder="{{ fusionBuilderText.search_elements }}" />
		</div>

		<ul class="fusion-tabs-menu">
			<# if ( 'undefined' !== typeof components && components.length && 0 < componentsCounter ) { #>
				<li class=""><a href="#template-elements">{{ fusionBuilderText.layout_section_elements }}</a></li>
			<# } #>
			<# if ( 'undefined' !== typeof form_components && form_components.length && 'fusion_form' === FusionApp.data.postDetails.post_type ) { #>
				<li class=""><a href="#form-elements">{{ fusionBuilderText.form_elements }}</a></li>
			<# } #>
			<li class=""><a href="#default-elements">{{ fusionBuilderText.builder_elements }}</a></li>
			<li class=""><a href="#custom-elements">{{ fusionBuilderText.library_elements }}</a></li>
		</ul>
	</div>

	<div class="fusion-builder-main-settings fusion-builder-main-settings-full has-group-options">
		<div class="fusion-builder-all-elements-container">
			<div class="fusion-tabs">
				<# if ( 'undefined' !== typeof components && components.length && 0 < componentsCounter ) { #>
					<div id="template-elements" class="fusion-tab-content">
						<ul class="fusion-builder-all-modules fusion-template-components">
							<# _.each( components, function( module ) { #>
								<#
								// If element is not supposed to be active on page edit type, skip.
								if ( 'object' === typeof module.templates && ! module.templates.includes( FusionApp.data.template_category ) ) {
									return false;
								}
								#>
								<#
								var additionalClass = false !== module.components_per_template && FusionPageBuilderViewManager.countElementsByType( module.label ) >= module.components_per_template ? ' fusion-builder-disabled-element' : '';

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

							<# for ( var i = 0; i < 16; i++ ) { #>
								<li class="spacer fusion-builder-element"></li>
							<# } #>
						</ul>
					</div>
				<# } #>

				<# if ( 'undefined' !== typeof form_components && form_components.length && 'fusion_form' === FusionApp.data.postDetails.post_type ) { #>
					<div id="form-elements" class="fusion-tab-content">
						<ul class="fusion-builder-all-modules fusion-form-components">
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

							<# for ( var i = 0; i < 16; i++ ) { #>
								<li class="spacer fusion-builder-element"></li>
							<# } #>
						</ul>
					</div>
				<# } #>
				<div id="default-elements" class="fusion-tab-content">
					<ul class="fusion-builder-all-modules">
						<# _.each( generator_elements, function(module) { #>
							<#
							if ( 'fusion_form' === FusionApp.data.postDetails.post_type && 'fusion_form' === module.label ) {
								return;
							}

							if ( 'post_cards' === FusionApp.data.template_category && 'fusion_post_cards' === module.label ) {
								return;
							}
							// If element is not supposed to be active on page edit type, skip.
							if ( 'object' === typeof module.templates && ! module.templates.includes( FusionApp.data.template_category ) ) {
								return false;
							}
							#>
							<# var additionalClass = ( 'undefined' !== typeof module.generator_only ) ? ' fusion-builder-element-generator' : '',
								elementTooltip = 'undefined' !== typeof module.generator_only ? fusionBuilderText.generator_elements_tooltip : '';

								if ( 'string' === typeof module.template_tooltip && false === module.components_per_template ) {
									additionalClass += ' fusion-builder-custom-tooltip-element';

									elementTooltip = module.template_tooltip;
								}

								if ( false !== module.components_per_template && FusionPageBuilderViewManager.countElementsByType( module.label ) >= module.components_per_template ) {
									additionalClass += ' fusion-builder-disabled-element';

									elementTooltip = fusionBuilderText.template_max_use_limit + ' ' + module.components_per_template
									elementTooltip = ( 2 > module.components_per_template ) ? elementTooltip + ' ' + fusionBuilderText.time : elementTooltip + ' ' + fusionBuilderText.times;
									elementTooltip = 'string' === typeof module.template_tooltip ? module.template_tooltip : elementTooltip;
								}
							#>
							<li class="{{ module.label }} fusion-builder-element{{ additionalClass }}">
								<h4 class="fusion_module_title">
									<# if ( 'undefined' !== typeof fusionAllElements[module.label].icon ) { #>
										<div class="fusion-module-icon {{ fusionAllElements[module.label].icon }}"></div>
									<# } #>
									{{{ module.title }}}
								</h4>
								<# if ( '' !== elementTooltip ) { #>
									<span class="fusion-tooltip">{{ elementTooltip }}</span>
								<# } #>
								<span class="fusion_module_label">{{ module.label }}</span>
							</li>
						<# } ); #>

						<# for ( var i = 0; i < 16; i++ ) { #>
							<li class="spacer fusion-builder-element"></li>
						<# } #>
					</ul>
				</div>
				<div id="custom-elements" class="fusion-tab-content"></div>
			</div>
		</div>
	</div>
</script>
