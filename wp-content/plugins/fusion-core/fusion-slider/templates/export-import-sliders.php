<?php
/**
 * Export/Import settings template.
 *
 * @package Fusion-Slider
 * @subpackage Templates
 * @since 1.0.0
 */

?>
<div class="avada-db-slider-import-export avada-db-card avada-db-card-first">
	<h1><?php esc_html_e( 'Export and Import Avada Sliders', 'fusion-core' ); ?></h1>
	<p><?php esc_html_e( 'Export your Avada Sliders to use them on a different install or import the slider zip to add sliders to this install.', 'fusion-core' ); ?></p>
	<form class="avada-db-card-grid" enctype="multipart/form-data" method="post" action="">
		<div class="avada-db-card-notice">
			<div class="avada-db-card-notice-heading">
				<i class="fusiona-file-upload-solid"></i>
				<h3><?php esc_html_e( 'Export', 'fusion-core' ); ?></h3>
			</div>
			<p class="avada-db-card-notice-content">
				<?php wp_nonce_field( 'fs_export' ); ?>
				<div><?php esc_html_e( 'Sliders will be exported in zip format.', 'fusion-core' ); ?></div>

				<input type="submit" class="button button-primary" name="fusion_slider_export_button" value="<?php esc_attr_e( 'Export All Sliders', 'fusion-core' ); ?>" />
			</p>
		</div>

		<div class="avada-db-card-notice">
			<div class="avada-db-card-notice-heading">
				<i class="fusiona-file-import-solid"></i>
				<h3><?php esc_html_e( 'Import', 'fusion-core' ); ?></h3>
			</div>
			<p class="avada-db-card-notice-content">
				<input type="file" id="upload" name="import" size="25" />
				<input type="hidden" name="action" value="save" />
				<input type="hidden" name="max_file_size" value="33554432" />
				<div><input type="submit" name="upload" class="button" value="<?php esc_attr_e( 'Upload And Import', 'fusion-core' ); ?>" /></div>
			</p>
		</div>
	</form>
</div>

<?php
if ( class_exists( 'Avada' ) ) {
	Avada_Admin::get_admin_screens_footer();
}
