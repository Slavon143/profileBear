<?php
/**
 * Prebuilt Website Demo.
 *
 * @package Avada
 */

// Set tags.
if ( ! isset( $demo_details['tags'] ) ) {
	$demo_details['tags'] = [];
}

$tags = array_keys( $demo_details['tags'] );
$tags = implode( ',', $tags );

$demo_imported = false;

if ( empty( $demo_details['plugin_dependencies'] ) ) {
	$demo_details['plugin_dependencies'] = [];
}

$demo_details['plugin_dependencies']['fusion-core']    = true;
$demo_details['plugin_dependencies']['fusion-builder'] = true;

// Generate Import / Remove forms.
$import_form  = '<form id="import-' . esc_attr( strtolower( $demo ) ) . '" data-demo-id="' . esc_attr( strtolower( $demo ) ) . '">';
$import_form .= '<p><input type="checkbox" value="all" id="import-all-' . esc_attr( strtolower( $demo ) ) . '"/> <label for="import-all-' . esc_attr( strtolower( $demo ) ) . '">' . esc_html__( 'All', 'Avada' ) . '</label></p>';
$remove_form  = '<form id="remove-' . esc_attr( strtolower( $demo ) ) . '" data-demo-id="' . esc_attr( strtolower( $demo ) ) . '">';

foreach ( $import_stages as $import_stage ) {

	$import_checked  = '';
	$remove_disabled = 'disabled';
	$data            = '';
	if ( ! empty( $import_stage['plugin_dependency'] ) && empty( $demo_details['plugin_dependencies'][ $import_stage['plugin_dependency'] ] ) ) {
		continue;
	}

	if ( ! empty( $import_stage['feature_dependency'] ) && ! in_array( $import_stage['feature_dependency'], $demo_details['features'] ) ) { // phpcs:ignore WordPress.PHP.StrictInArray
		continue;
	}

	if ( ! empty( $imported_data[ $import_stage['value'] ] ) ) {
		if ( in_array( strtolower( $demo ), $imported_data[ $import_stage['value'] ] ) ) { // phpcs:ignore WordPress.PHP.StrictInArray
			$import_checked  = 'checked="checked" disabled';
			$remove_disabled = 'checked="checked"';
			$demo_imported   = true;
		}
	}
	if ( ! empty( $import_stage['data'] ) ) {
		$data = 'data-type="' . esc_attr( $import_stage['data'] ) . '"';
	}
	$import_form .= '<p><input type="checkbox" value="' . esc_attr( $import_stage['value'] ) . '" ' . $import_checked . ' ' . $data . ' id="import-' . esc_attr( $import_stage['value'] ) . '-' . esc_attr( strtolower( $demo ) ) . '" /> <label for="import-' . esc_attr( $import_stage['value'] ) . '-' . esc_attr( strtolower( $demo ) ) . '">' . $import_stage['label'] . '</label></p>';
	$remove_form .= '<p><input type="checkbox" value="' . esc_attr( $import_stage['value'] ) . '" ' . $remove_disabled . ' ' . $data . ' id="remove-' . esc_attr( $import_stage['value'] ) . '-' . esc_attr( strtolower( $demo ) ) . '" /> <label for="remove-' . esc_attr( $import_stage['value'] ) . '-' . esc_attr( strtolower( $demo ) ) . '">' . $import_stage['label'] . '</label></p>';
}
	$import_form .= '</form>';
	$remove_form .= '</form>';

if ( isset( $_GET['page'] ) && 'avada-prebuilt-websites' === $_GET['page'] ) { //phpcs:ignore WordPress.Security.NonceVerification
	// Websites screen.
	$install_button_label   = ! $demo_imported ? __( 'Import', 'Avada' ) : __( 'Modify', 'Avada' );
	$install_button_classes = 'button-install-open-modal';
} else {
	// Setup screen.
	$install_button_label   = __( 'Select', 'Avada' );
	$install_button_classes = 'button-select-prebuilt';
}

if ( ! empty( $imported_data['all'] ) && in_array( strtolower( $demo ), $imported_data['all'] ) ) { // phpcs:ignore WordPress.PHP.StrictInArray
	$demo_import_badge = __( 'Full Import', 'Avada' );
} else {
	$demo_import_badge = __( 'Partial Import', 'Avada' );
}

	$new_imported = '';
	$tags         = true === $demo_imported ? $tags . ',imported' : $tags;
?>
<div class="fusion-admin-box" data-imported="<?php echo esc_attr( true === $demo_imported ? '1' : '0' ); ?>" data-tags="<?php echo esc_attr( $tags ); ?>" data-title="<?php echo esc_attr( ucwords( str_replace( '_', ' ', $demo ) ) ); ?>" data-demo-id="<?php echo esc_attr( strtolower( $demo ) ); ?>">
	<div id="theme-demo-<?php echo esc_attr( strtolower( $demo ) ); ?>" class="theme">
		<div class="theme-wrapper">
			<div class="theme-screenshot">
				<img src="" <?php echo ( ! empty( $demo_details['previewImage'] ) ) ? 'data-src="' . esc_url_raw( $demo_details['previewImage'] ) . '"' : ''; ?> <?php echo ( ! empty( $demo_details['previewImageRetina'] ) ) ? 'data-src-retina="' . esc_url_raw( $demo_details['previewImageRetina'] ) . '"' : ''; ?>>
			</div>
			<h3 class="theme-name" id="<?php esc_attr( $demo ); ?>"><?php echo esc_html( ucwords( str_replace( '_', ' ', $demo ) ) ); ?></h3>

			<div class="theme-actions">
				<a class="button button-primary <?php echo esc_attr( $install_button_classes ); ?>" data-demo-id="<?php echo esc_attr( strtolower( $demo ) ); ?>" href="#"><?php echo esc_html( $install_button_label ); ?></a>
				<?php $preview_url = $this->theme_url . str_replace( '_', '-', $demo ); ?>
				<a class="button button-primary" target="_blank" href="<?php echo esc_url( $preview_url ); ?>"><?php esc_html_e( 'Preview', 'Avada' ); ?></a>
			</div>

			<?php if ( isset( $demo_details['new'] ) && true === $demo_details['new'] ) : ?>
				<?php $new_imported = ' plugin-required-premium'; ?>
				<div class="plugin-required"><?php esc_html_e( 'New', 'Avada' ); ?></div>
			<?php endif; ?>

			<div class="plugin-premium<?php echo esc_attr( $new_imported ); ?>" style="display: <?php echo esc_attr( true === $demo_imported ? 'block' : 'none' ); ?>;"><?php echo esc_html( $demo_import_badge ); ?></div>
		</div>

		<?php
		if ( isset( $_GET['page'] ) && 'avada-prebuilt-websites' === $_GET['page'] ) { //phpcs:ignore WordPress.Security.NonceVerification
			require Avada::$template_dir_path . '/includes/admin-screens/prebuilt-websites-import-modal.php';
		}
		?>

	</div>
</div>
