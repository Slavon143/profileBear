<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 3.5
 */

?>
<script type="text/template" id="fusion-builder-studio-import-modal">
	<div class="awb-admin-modal-wrap">
		<div class="awb-admin-modal-inner">

			<div class="awb-admin-modal-content">

				<h2 class="awb-studio-modal-title">
					<i class="fusiona-exclamation-sign"></i>
					<span><?php echo esc_html( __( 'Importing Avada Studio Content', 'fusion-builder' ) ); ?></span>
				</h2>

				<div class="awb-studio-modal-text">
					<?php echo esc_html( __( 'Your Studio content is now being imported. This includes the layout, and any assets that may be associated (images, menus, forms, post cards etc). The import process should only take a few seconds, depending on the amount of content to be imported.', 'fusion-builder' ) ); ?>
				</div>
			</div>

			<div class="awb-admin-modal-status-bar">
				<div class="awb-admin-modal-status-bar-label"><span></span></div>
				<div class="awb-admin-modal-status-bar-progress-bar"></div>

				<a class="button-done-demo demo-update-modal-close" href="#"><?php echo esc_html( __( 'Done', 'fusion-builder' ) ); ?></a>
			</div>
		</div>

		<a href="#" class="awb-admin-modal-corner-close"><span class="dashicons dashicons-no-alt"></span></a>
	</div>

	<div class="awb-modal-overlay"></div>
</script>
