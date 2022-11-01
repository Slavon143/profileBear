<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/template" id="fusion-builder-container-library-template">
	<div class="fusion-builder-modal-top-container">
		<div class="fusion-builder-modal-search">
			<label for="fusion-modal-search" class="fusiona-search"><span><?php esc_html_e( 'Search', 'fusion-builder' ); ?></span></label>
			<input type="text" id="fusion-modal-search" class="fusion-elements-filter" placeholder="{{ fusionBuilderText.search_containers }}" />
		</div>

		<ul class="fusion-tabs-menu">
			<li><a href="#default-columns">{{ fusionBuilderText.builder_sections }}</a></li>
			<li><a href="#custom-sections">{{ fusionBuilderText.library_sections }}</a></li>
			<li><a href="#misc">{{ fusionBuilderText.library_misc }}</a></li>
			<# if ( '1' === fusionAppConfig.studio_status ) { #>
				<li><a href="#fusion-builder-sections-studio"><i class="fusiona-avada-logo"></i> <?php esc_html_e( 'Studio', 'fusion-builder' ); ?></a></li>
			<# } #>
		</ul>
	</div>

	<div class="fusion-builder-main-settings fusion-builder-main-settings-full">
		<div class="fusion-builder-column-layouts-container">
			<div class="fusion-tabs">
				<div id="default-columns" class="fusion-tab-content">
					<?php echo fusion_builder_column_layouts( 'container' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</div>

				<div id="custom-sections" class="fusion-tab-content">
					<div id="fusion-loader"><span class="fusion-builder-loader"></span></div>
				</div>
				<# if ( '1' === fusionAppConfig.studio_status ) { #>
					<div id="fusion-builder-sections-studio" class="fusion-tab-content">
						<?php if ( Avada()->registration->is_registered() ) : ?>
							<div class="studio-wrapper">
								<aside>
									<ul></ul>
								</aside>
								<section>
									<div class="fusion-builder-element-content fusion-loader"><span class="fusion-builder-loader"></span></div>
									<ul class="studio-imports"></ul>
								</section>
								<?php AWB_Studio::studio_import_options_template(); ?>
							</div>
						<?php else : ?>
							<h2 class="awb-studio-not-reg"><?php esc_html_e( 'The product needs to be registered to access the Avada Studio.', 'fusion-builder' ); ?></h2>
						<?php endif; ?>
					</div>
				<# } #>
				<div id="misc" class="fusion-tab-content">
					<div class="fusion-builder-layouts-header">
						<div class="fusion-builder-layouts-header-info">
							<h2>{{ fusionBuilderText.special_title }}</h2>
							<span class="fusion-builder-layout-info">{{ fusionBuilderText.next_page_description }} <br> {{ fusionBuilderText.checkout_form_description }}</span>
						</div>
					</div>
					<ul class="fusion-builder-all-modules fusion-builder-special-list">
						<li class="fusion-special-item fusion-builder-section-next-page" data-type="fusion_builder_next_page">
							<h4 class="fusion_module_title">{{ fusionBuilderText.nextpage }}</h4>
						</li>
						<?php if ( class_exists( 'WooCommerce' ) ) { ?>
						<li class="fusion-special-item fusion-builder-section-checkout-form" data-type="fusion_woo_checkout_form">
							<h4 class="fusion_module_title">{{ fusionBuilderText.checkout_form }}</h4>
						</li>
					<?php } ?>
						<# for ( var i = 0; i < 16; i++ ) { #>
							<li class="spacer fusion-builder-element"></li>
						<# } #>
					</ul>
				</div>

			</div>
		</div>
	</div>

</script>
