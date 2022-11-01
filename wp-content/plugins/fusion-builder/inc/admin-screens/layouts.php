<?php
/**
 * Admin Screen markup (Layout Sections builder page).
 *
 * @package fusion-builder
 */

?>
<?php Fusion_Builder_Admin::header( 'layouts' ); ?>

	<?php
		$display_notification = '' === get_user_meta( get_current_user_id(), 'fusion-template-builder-layouts', true ) ? true : false;
		$wrapper_class        = true === $display_notification ? 'fusion-has-notification' : '';
	?>

	<div class="fusion-builder-important-notice-wrapper <?php echo esc_attr( $wrapper_class ); ?>">

		<div class="fusion-builder-important-notice fusion-builder-template-notification avada-db-card" data-dismissible="true" data-dismiss-type="user_meta" data-dismiss-option="fusion-template-builder-layouts" data-nonce="<?php echo esc_attr( wp_create_nonce( 'fusion_admin_notice' ) ); ?>">
			<button class="notice-dismiss"></button>
			<div class="intro-text">
				<p>
					<span class="fusion-notification-number">1</span>
					<?php _e( 'Use the <strong>Global Layout</strong> to replace <strong>Layout Sections</strong> on every page of your site, or create a new <strong>Layout</strong> to replace them on specific pages, based on the conditions you choose.', 'fusion-builder' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</p>

				<p>
					<span class="fusion-notification-number">2</span>
					<?php
					printf(
						/* translators: %1$s: "layout sections". */
						esc_html__( 'Create and assign custom %1$s to any layout by clicking on the corresponding area you wish to change.', 'fusion-builder' ),
						'<strong>' . esc_html__( 'Layout Sections', 'fusion-builder' ) . '</strong>'
					);
					?>
				</p>

				<p>
					<span class="fusion-notification-number">3</span>
					<?php
					printf(
						/* translators: %1$s: "layout". */
						esc_html__( 'Choose which pages of your site will be affected by a %1$s by clicking on the cog icon to specify the conditions.', 'fusion-builder' ),
						'<strong>' . esc_html__( 'Layout', 'fusion-builder' ) . '</strong>'
					);
					?>
				</p>
			</div>
		</div>

		<div class="fusion-builder-important-notice fusion-template-builder avada-db-card avada-db-card-first">
			<div class="intro-text">
				<h1><?php esc_html_e( 'Layout Builder', 'fusion-builder' ); ?></h1>
				<p><?php esc_html_e( 'Create a new layout which you can then assign layout sections to and set layout conditions.', 'fusion-builder' ); ?></p>

				<div class="avada-db-card-notice">
					<i class="fusiona-info-circle"></i>
					<p class="avada-db-card-notice-heading">
						<?php
						printf(
							/* translators: %s: "Avada Layouts Documentation Link". */
							esc_html__( 'Please see the %s.', 'fusion-builder' ),
							'<a href="https://theme-fusion.com/documentation/avada/layouts/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Avada Layouts Documentation', 'fusion-builder' ) . '</a>'
						);
						?>
					</p>
				</div>
			</div>
			<form id="fusion-create-layout-form" class="avada-db-create-form fusion-create-layout-form">
				<input type="hidden" name="action" value="fusion_tb_new_layout">

				<div>
					<input type="text" placeholder="<?php esc_attr_e( 'Enter Layout Name', 'fusion-builder' ); ?>" required id="fusion-tb-layout-name" name="name" />
				</div>

				<?php wp_nonce_field( 'fusion_tb_new_layout' ); ?>

				<div>
					<input type="submit" value="<?php esc_attr_e( 'Create New Layout', 'fusion-builder' ); ?>" class="button button-primary" />
				</div>
			</form>

			<button class="avada-db-more-info fusion-builder-tooltip avada-db-tooltip">
				<span class="avada-db-tooltip-text"><?php esc_html_e( 'Show Tutorial', 'fusion-builder' ); ?></span>
			</button>
		</div>
	</div>

	<div class="fusion-layouts avada-db-card avada-db-card-transparent">
		<?php
		$options      = get_option( 'fusion_builder_settings', [] );
		$builder_type = isset( $options['enable_builder_ui_by_default'] ) ? $options['enable_builder_ui_by_default'] : 'backend';
		?>
		<script>
			fusionLayouts   = <?php echo wp_json_encode( Fusion_Template_Builder()->get_registered_layouts(), JSON_FORCE_OBJECT ); ?>;
			fusionTemplates = <?php echo wp_json_encode( Fusion_Template_Builder()->get_templates_by_term(), JSON_FORCE_OBJECT ); ?>;
			builderType     = '<?php echo esc_attr( $builder_type ); ?>';
		</script>
	</div>
<?php Fusion_Builder_Admin::footer(); ?>
