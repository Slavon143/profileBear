<?php
/**
 * Prebuilt Websites Import Modal.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}
?>

<div id="demo-modal-<?php echo esc_attr( strtolower( $demo ) ); ?>" class="awb-admin-modal-wrap" style="display:none;">
	<div class="awb-admin-modal-inner">

		<div class="demo-modal-thumbnail" style="background-image:url(<?php echo esc_attr( $demo_details['previewImage'] ); ?>);">
			<a class="demo-modal-preview" target="_blank" href="<?php echo esc_url( $preview_url ); ?>"><?php esc_html_e( 'Live Preview', 'Avada' ); ?></a>
		</div>

		<div class="awb-admin-modal-content">

			<?php if ( in_array( true, $demo_details['plugin_dependencies'] ) ) : // phpcs:ignore WordPress.PHP.StrictInArray ?>
				<div class="demo-required-plugins">
					<h3><?php esc_html_e( 'Required Plugins To Import Content', 'Avada' ); ?></h3>
					<ul class="required-plugins-list">
						<?php foreach ( $demo_details['plugin_dependencies'] as $slug => $required ) : ?>
							<?php if ( true === $required ) : ?>
								<li>
									<span class="required-plugin-name">
										<?php
											$plugin_name = isset( $plugin_dependencies[ $slug ] ) ? $plugin_dependencies[ $slug ]['plugin_name'] : $slug;
											echo 'HubSpot' === $plugin_name ? sprintf(
												/* translators: %1$s: Plugin Slugh. %2$s: Documentation Link. */
												esc_html__( '%1$s (%2$s)', 'Avada' ),
												esc_html( $plugin_name ),
												'<a href="https://theme-fusion.com/documentation/avada/plugins/how-to-setup-hubspot-live-chat-with-avada/" rel="noopener noreferrer" target="_blank">' . esc_html__( 'Setup Required', 'Avada' ) . '</a>'
											) : esc_html( $plugin_name );
										?>
									</span>

									<?php
									$label  = __( 'Install', 'Avada' );
									$status = 'install'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
									if ( isset( $plugin_dependencies[ $slug ] ) && $plugin_dependencies[ $slug ]['active'] ) {
										$label  = __( 'Active', 'Avada' );
										$status = 'active'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
									} elseif ( isset( $plugin_dependencies[ $slug ] ) && $plugin_dependencies[ $slug ]['installed'] ) {
										$label  = __( 'Activate', 'Avada' );
										$status = 'activate'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
									}
									?>
									<span class="required-plugin-status <?php echo esc_attr( $status ); ?> ">
										<?php if ( 'activate' === $status ) : ?>
											<a href="<?php echo esc_url( admin_url( 'admin.php?page=avada-plugins' ) ); ?>"
												target="_blank"
												data-plugin="<?php echo esc_attr( $slug ); ?>"
												data-plugin_name="<?php echo esc_attr( $plugin_dependencies[ $slug ]['name'] ); ?>"
											>
										<?php elseif ( 'install' === $status ) : ?>
											<a href="<?php echo esc_url( admin_url( 'admin.php?page=avada-plugins' ) ); ?>"
												target="_blank"
												data-plugin="<?php echo esc_attr( $slug ); ?>"
												data-plugin_name="<?php echo esc_attr( $plugin_dependencies[ $slug ]['name'] ); ?>"
											>
										<?php endif; ?>

											<?php echo esc_html( $label ); ?>

										<?php if ( 'active' !== $status ) : ?>
											</a>
										<?php endif; ?>
									</span>
								</li>
							<?php endif; ?>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<div class="demo-update-form-wrap">
				<div class="demo-import-form">
					<h4 class="demo-form-title">
						<?php esc_html_e( 'Import Content', 'Avada' ); ?> <span><?php esc_html_e( '(menus only import with "All")', 'Avada' ); ?></span>
					</h4>
						<?php echo $import_form; // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</div>

				<div class="demo-remove-form">
					<h4 class="demo-form-title">
						<?php esc_html_e( 'Remove Content', 'Avada' ); ?>
					</h4>
					<p>
						<input type="checkbox" value="uninstall" id="uninstall-<?php echo esc_attr( strtolower( $demo ) ); ?>" /> <label for="uninstall-<?php echo esc_attr( strtolower( $demo ) ); ?>"><?php esc_html_e( 'Remove', 'Avada' ); ?></label>
					</p>
						<?php echo $remove_form; // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</div>
			</div>
		</div>

		<div class="awb-admin-modal-status-bar">
			<div class="awb-admin-modal-status-bar-label"><span></span></div>
			<div class="awb-admin-modal-status-bar-progress-bar"></div>

			<a class="button-install-demo" data-demo-id="<?php echo esc_attr( strtolower( $demo ) ); ?>" href="#">
				<?php esc_html_e( 'Import', 'Avada' ); ?>
			</a>

			<a class="button-uninstall-demo" data-demo-id="<?php echo esc_attr( strtolower( $demo ) ); ?>" href="#">
				<?php esc_html_e( 'Remove', 'Avada' ); ?>
			</a>

			<a class="button-done-demo demo-update-modal-close" href="#">
				<?php esc_html_e( 'Done', 'Avada' ); ?>
			</a>
		</div>
	</div>

	<a href="#" class="awb-admin-modal-corner-close demo-update-modal-close"><span class="dashicons dashicons-no-alt"></span></a>
</div> <!-- .awb-admin-modal-wrap -->
