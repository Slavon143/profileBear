<?php
/**
 * Admin Screen markup (Library page).
 *
 * @package fusion-builder
 */

?>
<?php Fusion_Builder_Admin::header( 'icons' ); ?>

	<div class="fusion-builder-important-notice fusion-template-builder avada-db-card avada-db-card-first">
		<div class="intro-text">
			<h1><?php esc_html_e( 'Custom Icons', 'fusion-builder' ); ?></h1>
			<p><?php esc_html_e( 'Add a name for your Custom Icon Set. You will be redirected to the Edit Icon Set Page, where you can upload your custom Icomoon icon set.', 'fusion-builder' ); ?></p>

			<div class="avada-db-card-notice">
				<i class="fusiona-info-circle"></i>
				<p class="avada-db-card-notice-heading">
					<?php
					printf(
						/* translators: %s: "Icons Documentation Link". */
						esc_html__( 'Please see the %s.', 'fusion-builder' ),
						'<a href="https://theme-fusion.com/documentation/avada/how-to/how-to-upload-and-use-custom-icons-in-avada/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Custom Icons Documentation', 'fusion-builder' ) . '</a>'
					);
					?>
				</p>
			</div>
		</div>
		<form class="avada-db-create-form">
			<input type="hidden" name="action" value="fusion_custom_icons_new">
			<?php wp_nonce_field( 'fusion_new_custom_icon_set' ); ?>

			<div>
				<input type="text" placeholder="<?php esc_attr_e( 'Enter Icon Set Name', 'fusion-builder' ); ?>" required id="fusion-icon-set-name" name="name" />
			</div>

			<div>
				<input type="submit" value="<?php esc_attr_e( 'Create New Icon Set', 'fusion-builder' ); ?>" class="button button-large button-primary avada-large-button" />
			</div>
		</form>
	</div>

	<div class="fusion-library-data-items avada-db-table">
		<?php
			$fusion_icons_table = new Fusion_Custom_Icons_Table();
			$fusion_icons_table->get_status_links();
		?>
		<form id="fusion-library-data" method="get">
			<?php
			$fusion_icons_table->prepare_items();
			$fusion_icons_table->display();
			?>
		</form>
	</div>

<?php Fusion_Builder_Admin::footer(); ?>
