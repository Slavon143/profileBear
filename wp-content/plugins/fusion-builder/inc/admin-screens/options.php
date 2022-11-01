<?php
/**
 * Admin Screen markup (Settings page).
 *
 * @package fusion-builder
 */

global $all_fusion_builder_elements;
// If Avada Builder is not bundled in another plugin/theme, it has its own options panel.
if ( null === FusionBuilder()->registration ) {
	$options_name = __( 'Fusion Element Global Options', 'fusion-builder' );
} else {
	$options_name = __( 'Fusion Element Options', 'fusion-builder' );
}
?>
<?php Fusion_Builder_Admin::header( 'builder-options' ); ?>
	<?php $existing_settings = get_option( 'fusion_builder_settings', [] ); ?>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">

		<section class="avada-db-card avada-db-card-first avada-db-settings-start">
			<h1 class="avada-db-settings-heading"><?php esc_html_e( 'Avada Builder Options', 'fusion-builder' ); ?></h1>
			<p><?php esc_html_e( 'Here you can set some global options, enable, or disable elements, manage post types, and import and export builder content.', 'fusion-builder' ); ?></p>

			<div class="avada-db-card-notice">
				<i class="fusiona-info-circle"></i>
				<p class="avada-db-card-notice-heading">
					<?php
					printf(
						/* translators: %s: "Avada Builder Elements Global Options". */
						esc_html__( 'To change global options of the Avada Builder elements, please go to the %s.', 'fusion-builder' ),
						'<a href="' . esc_url( admin_url( 'themes.php?page=avada_options#alert_shortcode_section_start_accordion' ) ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Element Global Options', 'fusion-builder' ) . '</a>'
					);
					?>
				</p>
			</div>
		</section>

		<div class="fusion-builder-settings">

			<section class="avada-db-card">
				<div class="fusion-builder-option">
					<div class="fusion-builder-option-title">
						<h2><?php esc_html_e( 'Builder Auto Activation', 'fusion-builder' ); ?></h2>
						<span class="fusion-builder-option-label">
							<p><?php esc_html_e( 'Turn on to enable the desired Builder user interface by default when opening a page or post. Turn off to enable the default WP editor view.', 'fusion-builder' ); ?></p>
						</span>
					</div>

					<div class="fusion-builder-option-field">
						<div class="fusion-form-radio-button-set ui-buttonset enable-builder-ui">
							<?php
							$enable_builder_ui_by_default = '0';
							if ( isset( $existing_settings['enable_builder_ui_by_default'] ) ) {
								$enable_builder_ui_by_default = $existing_settings['enable_builder_ui_by_default'];
							}
							?>
							<input type="hidden" class="button-set-value" value="<?php echo esc_attr( $enable_builder_ui_by_default ); ?>" name="enable_builder_ui_by_default" id="enable_builder_ui_by_default">
							<a data-value="live" class="ui-button buttonset-item<?php echo ( 'live' === $enable_builder_ui_by_default ) ? ' ui-state-active' : ''; ?>" href="#"><?php esc_html_e( 'Live Builder', 'fusion-builder' ); ?></a>
							<a data-value="backend" class="ui-button buttonset-item<?php echo ( 'backend' === $enable_builder_ui_by_default ) ? ' ui-state-active' : ''; ?>" href="#"><?php esc_html_e( 'Back-end Builder', 'fusion-builder' ); ?></a>
							<a data-value="0" class="ui-button buttonset-item<?php echo ( ! $enable_builder_ui_by_default ) ? ' ui-state-active' : ''; ?>" href="#"><?php esc_html_e( 'Off', 'fusion-builder' ); ?></a>
						</div>
					</div>
				</div>
			</section>

			<section class="avada-db-card">
				<div class="fusion-builder-option">
					<div class="fusion-builder-option-title">
						<h2><?php esc_html_e( 'Avada Builder Elements', 'fusion-builder' ); ?></h2>
						<span class="fusion-builder-option-label">
							<p><?php esc_html_e( 'Each Avada Builder element can be enabled or disabled. This can increase performance if you are not using a specific element. Check the box to enable, uncheck to disable.', 'fusion-builder' ); ?></p>
							<p><?php _e( '<strong>NOTE:</strong> Elements for plugins like WooCommere or The Events Calendar will only be available in the builder, if the corresponding options are activated here and if those plugins are active.', 'fusion-builder' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
							<p><?php _e( '<strong>WARNING:</strong> Use with caution. Disabling an element will remove it from all pages/posts, old and new. If it was on a previous page/post, it will render as regular element markup on the frontend.', 'fusion-builder' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
							<div class="avada-db-single-button-set fuion-builder-element-activation">
								<a href="#" class="button fusion-check-all" title="<?php esc_attr_e( 'Check All Elements', 'fusion-builder' ); ?>"><?php esc_html_e( 'Check All Elements', 'fusion-builder' ); ?></a>
								<a href="#" class="button fusion-uncheck-all" title="<?php esc_attr_e( 'Uncheck All Elements', 'fusion-builder' ); ?>"><?php esc_html_e( 'Uncheck All Elements', 'fusion-builder' ); ?></a>
							</div>

							<p style="margin-top:2em;"><?php _e( 'You can run an element scan which will scan for elements throughout your site and uncheck any which are not used. <strong>WARNING:</strong> If your website has a lot of content this may not run fully.', 'fusion-builder' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
							<div class="fuion-builder-element-activation">
								<a href="#" class="button fusion-runcheck" title="<?php esc_attr_e( 'Run Element Scan', 'fusion-builder' ); ?>"><?php esc_html_e( 'Run Element Scan', 'fusion-builder' ); ?></a>
								<span class="spinner avada-db-loader"></span>
							</div>
						</span>
					</div>

					<div class="fusion-builder-option-field fusion-builder-element-checkboxes">
						<ul>
							<?php
							$i               = 0;
							$plugin_elements = [
								'fusion_featured_products_slider' => [
									'name'      => esc_html__( 'Woo Featured', 'fusion-builder' ),
									'shortcode' => 'fusion_featured_products_slider',
									'class'     => ( class_exists( 'WooCommerce' ) ) ? '' : 'hidden',
								],
								'fusion_products_slider' => [
									'name'      => esc_html__( 'Woo Carousel', 'fusion-builder' ),
									'shortcode' => 'fusion_products_slider',
									'class'     => ( class_exists( 'WooCommerce' ) ) ? '' : 'hidden',
								],
								'fusion_woo_shortcodes'  => [
									'name'      => esc_html__( 'Woo Shortcodes', 'fusion-builder' ),
									'shortcode' => 'fusion_woo_shortcodes',
									'class'     => ( class_exists( 'WooCommerce' ) ) ? '' : 'hidden',
								],
								'layerslider'            => [
									'name'      => esc_html__( 'Layer Slider', 'fusion-builder' ),
									'shortcode' => 'layerslider',
									'class'     => ( defined( 'LS_PLUGIN_BASE' ) ) ? '' : 'hidden',
								],
								'rev_slider'             => [
									'name'      => esc_html__( 'Slider Revolution', 'fusion-builder' ),
									'shortcode' => 'rev_slider',
									'class'     => ( defined( 'RS_PLUGIN_PATH' ) ) ? '' : 'hidden',
								],
								'fusion_events'          => [
									'name'      => esc_html__( 'Events', 'fusion-builder' ),
									'shortcode' => 'fusion_events',
									'class'     => ( class_exists( 'Tribe__Events__Main' ) ) ? '' : 'hidden',
								],
								'fusion_fontawesome'     => [
									'name'      => esc_html__( 'Icon', 'fusion-builder' ),
									'shortcode' => 'fusion_fontawesome',
								],
								'fusion_fusionslider'    => [
									'name'      => esc_html__( 'Avada Slider', 'fusion-builder' ),
									'shortcode' => 'fusion_fusionslider',
								],
							];

							$all_fusion_builder_elements = array_merge( $all_fusion_builder_elements, apply_filters( 'fusion_builder_plugin_elements', $plugin_elements ) );

							usort( $all_fusion_builder_elements, 'fusion_element_sort' );
							$form_elements   = [];
							$layout_elements = [];
							foreach ( $all_fusion_builder_elements as $module ) :
								if ( empty( $module['hide_from_builder'] ) ) {
									$i++;
									// Form Components.
									if ( ! empty( $module['form_component'] ) ) {
										$form_elements[ $i ] = $module;
										continue;
									}

									// Layout Componnents.
									if ( ! empty( $module['component'] ) ) {
										$layout_elements[ $i ] = $module;
										continue;
									}

									$checked = '';
									$class   = ( isset( $module['class'] ) && '' !== $module['class'] ) ? $module['class'] : '';

									if ( ( isset( $existing_settings['fusion_elements'] ) && is_array( $existing_settings['fusion_elements'] ) && in_array( $module['shortcode'], $existing_settings['fusion_elements'] ) ) || ( ! isset( $existing_settings['fusion_elements'] ) || ! is_array( $existing_settings['fusion_elements'] ) ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
										$checked = 'checked';
									}
									echo '<li class="' . esc_attr( $class ) . '">';
									echo '<label for="hide_from_builder_' . esc_attr( $i ) . '">';
									echo '<input name="fusion_elements[]" type="checkbox" value="' . esc_attr( $module['shortcode'] ) . '" ' . $checked . ' id="hide_from_builder_' . esc_attr( $i ) . '"/>'; // phpcs:ignore WordPress.Security.EscapeOutput
									echo $module['name'] . '</label>'; // phpcs:ignore WordPress.Security.EscapeOutput
									echo '</li>';
								}
							endforeach;

							// Layout elements output.
							if ( 0 < count( $layout_elements ) ) {
								echo '<li style="margin-bottom: 1em;width: 100%;"><hr style="margin:1.3em 0;"/>' . esc_html__( 'Layout Elements', 'fusion-builder' ) . '</li>';
							}
							foreach ( $layout_elements as $i => $module ) :
								$checked = '';
								$class   = ( isset( $module['class'] ) && '' !== $module['class'] ) ? $module['class'] : '';

								if ( ( isset( $existing_settings['fusion_elements'] ) && is_array( $existing_settings['fusion_elements'] ) && in_array( $module['shortcode'], $existing_settings['fusion_elements'] ) ) || ( ! isset( $existing_settings['fusion_elements'] ) || ! is_array( $existing_settings['fusion_elements'] ) ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
									$checked = 'checked';
								}
								echo '<li class="' . esc_attr( $class ) . '">';
								echo '<label for="hide_from_builder_' . esc_attr( $i ) . '">';
								echo '<input name="fusion_elements[]" type="checkbox" value="' . esc_attr( $module['shortcode'] ) . '" ' . $checked . ' id="hide_from_builder_' . esc_attr( $i ) . '"/>'; // phpcs:ignore WordPress.Security.EscapeOutput
								echo $module['name'] . '</label>'; // phpcs:ignore WordPress.Security.EscapeOutput
								echo '</li>';
							endforeach;

							// Form elements output.
							if ( 0 < count( $form_elements ) ) {
								echo '<li style="margin-bottom: 1em;width: 100%;"><hr style="margin:1.3em 0;"/>' . esc_html__( 'Form Elements', 'fusion-builder' ) . '</li>';
							}
							foreach ( $form_elements as $i => $module ) :
								$checked = '';
								$class   = ( isset( $module['class'] ) && '' !== $module['class'] ) ? $module['class'] : '';

								if ( ( isset( $existing_settings['fusion_elements'] ) && is_array( $existing_settings['fusion_elements'] ) && in_array( $module['shortcode'], $existing_settings['fusion_elements'] ) ) || ( ! isset( $existing_settings['fusion_elements'] ) || ! is_array( $existing_settings['fusion_elements'] ) ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
									$checked = 'checked';
								}
								echo '<li class="' . esc_attr( $class ) . '">';
								echo '<label for="hide_from_builder_' . esc_attr( $i ) . '">';
								echo '<input name="fusion_elements[]" type="checkbox" value="' . esc_attr( $module['shortcode'] ) . '" ' . $checked . ' id="hide_from_builder_' . esc_attr( $i ) . '"/>'; // phpcs:ignore WordPress.Security.EscapeOutput
								echo $module['name'] . '</label>'; // phpcs:ignore WordPress.Security.EscapeOutput
								echo '</li>';
							endforeach;


							?>
						</ul>
					</div>
				</div>
			</section>

			<section class="avada-db-card">
				<div class="fusion-builder-option">
					<div class="fusion-builder-option-title">
						<h2><?php esc_html_e( 'Post Types', 'fusion-builder' ); ?></h2>
						<span class="fusion-builder-option-label">
							<p><?php esc_html_e( 'Avada Builder can be enabled or disabled on registered post types. Check the box to enable, uncheck to disable. Please note the Avada element generator will still be active on any post type that is disabled.', 'fusion-builder' ); ?></p>
						</span>
					</div>

					<div class="fusion-builder-option-field">
						<ul>
							<input type="hidden" name="post_types[]" value=" " />
							<?php
							$args       = [
								'public' => true,
							];
							$post_types = get_post_types( $args, 'names', 'and' );
							// Filter out not relevant post types (can add filter later).
							$disabled_post_types = [ 'attachment', 'slide', 'themefusion_elastic', 'fusion_template', 'fusion_tb_section', 'fusion_tb_layout', 'fusion_form' ];
							foreach ( $disabled_post_types as $disabled ) {
								unset( $post_types[ $disabled ] );
							}
							$defaults = FusionBuilder::default_post_types();
							$i        = 0;
							foreach ( $post_types as $post_type ) :
								$i++;
								$post_type_obj = get_post_type_object( $post_type );
								// Either selected in options saved, or in array of default post types.
								$checked = (
									( isset( $existing_settings['post_types'] ) && is_array( $existing_settings['post_types'] ) && in_array( $post_type, $existing_settings['post_types'] ) ) || // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
									( ! isset( $existing_settings['post_types'] ) && in_array( $post_type, $defaults ) ) ) // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
									? 'checked' : '';
								echo '<li>';
								echo '<label for="fusion_post_type_' . esc_attr( $i ) . '">';
								echo '<input type="checkbox" name="post_types[]" value="' . esc_attr( $post_type ) . '" ' . $checked . ' id="fusion_post_type_' . esc_attr( $i ) . '"/>'; // phpcs:ignore WordPress.Security.EscapeOutput
								echo $post_type_obj->labels->singular_name . '</label>'; // phpcs:ignore WordPress.Security.EscapeOutput
								echo '</li>';
							endforeach;
							?>
							<input type="hidden" name="post_types[]" value="fusion_template" checked="checked" />
							<input type="hidden" name="post_types[]" value="fusion_tb_section" checked="checked" />
							<input type="hidden" name="post_types[]" value="fusion_form" checked="checked" />
						</ul>
					</div>
				</div>
			</section>

			<section class="avada-db-card">
				<div class="fusion-builder-option">
					<div class="fusion-builder-option-title">
						<h2><?php esc_html_e( 'Import Library Content', 'fusion-builder' ); ?></h2>
						<span class="fusion-builder-option-label">
							<p><?php esc_html_e( 'Choose to import Avada Builder Library content. You can import either your saved containers, columns and elements, or your saved full page templates. Click "Choose File" and select your Avada Builder XML file.', 'fusion-builder' ); ?></p>
						</span>
					</div>

					<div class="fusion-builder-option-field">
						<form id="fusion-importer-form" method="post" enctype="multipart/form-data" name="fusion-importer-form">
							<input type="file" id="fusion-builder-import-file" name="fusion-builder-import-file" size="25" value="" accept=".xml" />
							<input type="submit" name="submit" id="submit" class="button fusion-builder-import-data" value="Import" disabled />
						</form>
						<div class="fusion-builder-import-success"><?php esc_html_e( 'Content Successfully  Imported', 'fusion-builder' ); ?></div>
					</div>
				</div>
			</section>

			<section class="avada-db-card">
				<div class="fusion-builder-option">
					<div class="fusion-builder-option-title">
						<h2><?php esc_html_e( 'Export Library Content', 'fusion-builder' ); ?></h2>
						<span class="fusion-builder-option-label">
							<p><?php esc_html_e( 'Choose to export Avada Builder Library content. You can export your saved containers, columns and elements, or your saved full page templates. A corresponding XML file will be downloaded to your computer.' ); ?></p>
						</span>
					</div>

					<div class="fusion-builder-option-field avada-db-single-button-set">
						<a href="<?php echo esc_url_raw( admin_url( 'admin.php?page=avada-builder-options&fusion_action=export&fusion_export_type=fusion_element' ) ); ?>" class="button" title="<?php esc_attr_e( 'Export Fusion Elements from your Library', 'fusion-builder' ); ?>"><?php esc_html_e( 'Export Content', 'fusion-builder' ); ?></a>

						<a href="<?php echo esc_url_raw( admin_url( 'admin.php?page=avada-builder-options&fusion_action=export&fusion_export_type=fusion_template' ) ); ?>" class="button" title="<?php esc_attr_e( 'Export Fusion Templates from your Library', 'fusion-builder' ); ?>"><?php esc_html_e( 'Export Templates', 'fusion-builder' ); ?></a>
					</div>
				</div>
			</section>

			<section class="avada-db-card">
				<div class="fusion-builder-option">
					<div class="fusion-builder-option-title">
						<h2><?php esc_html_e( 'Sticky Preview / Publish Buttons', 'fusion-builder' ); ?></h2>
						<span class="fusion-builder-option-label">
							<p><?php esc_html_e( 'This option allows the preview and publish button to stick to the bottom of the page so you can quickly access them.', 'fusion-builder' ); ?></p>
						</span>
					</div>

					<div class="fusion-builder-option-field">
						<div class="fusion-form-radio-button-set ui-buttonset enable-builder-ui">
							<?php
							$enable_builder_sticky_publish_buttons = '1';
							if ( isset( $existing_settings['enable_builder_sticky_publish_buttons'] ) ) {
								$enable_builder_sticky_publish_buttons = $existing_settings['enable_builder_sticky_publish_buttons'];
							}
							?>
							<input type="hidden" class="button-set-value" value="<?php echo esc_attr( $enable_builder_sticky_publish_buttons ); ?>" name="enable_builder_sticky_publish_buttons" id="enable_builder_sticky_publish_buttons">
							<a data-value="1" class="ui-button buttonset-item<?php echo ( $enable_builder_sticky_publish_buttons ) ? ' ui-state-active' : ''; ?>" href="#"><?php esc_html_e( 'On', 'fusion-builder' ); ?></a>
							<a data-value="0" class="ui-button buttonset-item<?php echo ( ! $enable_builder_sticky_publish_buttons ) ? ' ui-state-active' : ''; ?>" href="#"><?php esc_html_e( 'Off', 'fusion-builder' ); ?></a>
						</div>
					</div>
				</div>
			</section>

			<section class="avada-db-card">
				<div class="fusion-builder-option">
					<div class="fusion-builder-option-title">
						<h2><?php esc_html_e( 'Remove Empty Attributes', 'fusion-builder' ); ?></h2>
						<span class="fusion-builder-option-label">
							<p><?php esc_html_e( 'Set to "on" to remove empty attributes from elements at saving.', 'fusion-builder' ); ?></p>
						</span>
					</div>

					<div class="fusion-builder-option-field">
						<div class="fusion-form-radio-button-set ui-buttonset enable-builder-ui">
							<?php
							$remove_empty_attributes = 'off';
							if ( isset( $existing_settings['remove_empty_attributes'] ) ) {
								$remove_empty_attributes = $existing_settings['remove_empty_attributes'];
							}
							?>
							<input type="hidden" class="button-set-value" value="<?php echo esc_attr( $remove_empty_attributes ); ?>" name="remove_empty_attributes" id="remove_empty_attributes">
							<a data-value="on" class="ui-button buttonset-item<?php echo ( 'on' === $remove_empty_attributes ) ? ' ui-state-active' : ''; ?>" href="#"><?php esc_html_e( 'On', 'fusion-builder' ); ?></a>
							<a data-value="off" class="ui-button buttonset-item<?php echo ( 'off' === $remove_empty_attributes ) ? ' ui-state-active' : ''; ?>" href="#"><?php esc_html_e( 'Off', 'fusion-builder' ); ?></a>
						</div>
					</div>
				</div>
			</section>

			<?php if ( class_exists( 'AWB_Studio' ) && AWB_Studio::is_studio_enabled() ) : ?>
				<?php wp_nonce_field( 'awb_remove_studio_content', 'awb_remove_studio_content' ); ?>
			<section class="avada-db-card">
				<div class="fusion-builder-option">
					<div class="fusion-builder-option-title">
						<h2><?php esc_html_e( 'Remove Avada Studio Content', 'fusion-builder' ); ?></h2>
						<span class="fusion-builder-option-label">
							<p>
								<?php
								/* translators: Opening and closing strong tags. */
								printf( esc_html__( 'Remove previously imported Avada Studio content. %1$sWARNING:%2$s Use with caution. It will remove all imported content including images which might be used in your pages.', 'fusion-builder' ), '<strong>', '</strong>' );
								?>
							</p>
						</span>
					</div>

					<div class="fusion-builder-option-field">
						<div class="awb-studio-content-remove-wrap">
							<button id="awb-remove-studio-content" class="button"><?php esc_html_e( 'Remove', 'fusion-builder' ); ?></button>
							<span class="spinner avada-db-loader"></span>
							<div class="awb-remove-studio-content-status"><i class="fusiona-exclamation-sign"></i><?php esc_html_e( 'Something went wrong.', 'fusion-builder' ); ?></div>
						</div>
					</div>
				</div>
			</section>
			<?php endif; ?>

			<section class="fusion-builder-settings-save-settings avada-db-card avada-db-card-transparent">
				<input type="hidden" name="action" value="save_fb_settings">
				<?php wp_nonce_field( 'fusion_builder_save_fb_settings', 'fusion_builder_save_fb_settings' ); ?>
				<input type="submit" class="button button-primary fusion-builder-save-settings" value="<?php esc_attr_e( 'Save Options', 'fusion-builder' ); ?>" />
			</section>

		</div>

	</form>
<?php Fusion_Builder_Admin::footer(); ?>
