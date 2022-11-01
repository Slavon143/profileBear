<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/template" id="fusion-builder-column-library-template">
	<div class="fusion-builder-modal-top-container">
		<div class="fusion-builder-modal-search">
			<label for="fusion-modal-search" class="fusiona-search"><span><?php esc_html_e( 'Search', 'fusion-builder' ); ?></span></label>
			<input type="text" id="fusion-modal-search" class="fusion-elements-filter" placeholder="{{ fusionBuilderText.search_columns }}" />
		</div>

		<# if ( 'undefined' === typeof nested ) { #>
			<ul class="fusion-tabs-menu">
				<li><a href="#default-columns">{{ fusionBuilderText.builder_columns }}</a></li>
				<li><a href="#custom-columns">{{ fusionBuilderText.library_columns }}</a></li>
				<# if ( '1' === fusionAppConfig.studio_status ) { #>
					<li><a href="#fusion-builder-columns-studio"><i class="fusiona-avada-logo"></i> <?php esc_html_e( 'Studio', 'fusion-builder' ); ?></a></li>
				<# } #>
			</ul>
		<# } #>
	</div>
	<div class="fusion-builder-main-settings fusion-builder-main-settings-full">
		<div class="fusion-builder-column-layouts-container">
			<# if ( 'undefined' !== typeof nested && nested ) { #>
				<?php echo fusion_builder_inner_column_layouts(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<# } else { #>
				<div class="fusion-tabs">
					<div id="default-columns" class="fusion-tab-content">
						<?php echo fusion_builder_column_layouts(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</div>
					<# if ( '1' === fusionAppConfig.studio_status ) { #>
						<div id="fusion-builder-columns-studio" class="fusion-tab-content">
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

					<div id="custom-columns" class="fusion-tab-content">
						<div id="fusion-loader"><span class="fusion-builder-loader"></span></div>
					</div>
				</div>
			<# } #>
		</div>
	</div>
</script>
