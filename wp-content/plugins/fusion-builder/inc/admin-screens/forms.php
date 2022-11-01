<?php
/**
 * Admin Screen markup (Form builder page).
 *
 * @package fusion-builder
 */

?>

<?php Fusion_Builder_Admin::header( 'forms' ); ?>

<div class="fusion-builder-important-notice fusion-template-builder avada-db-card avada-db-card-first">
		<div class="intro-text">
			<h1><?php esc_html_e( 'Form Builder', 'fusion-builder' ); ?></h1>
			<p><?php esc_html_e( 'Add a name for your Avada Form. You will be redirected to the Edit Form Page.', 'fusion-builder' ); ?></p>

			<div class="avada-db-card-notice">
				<i class="fusiona-info-circle"></i>
				<p class="avada-db-card-notice-heading">
					<?php
					printf(
						/* translators: %s: "Forms Documentation Link". */
						esc_html__( 'Please see the %s.', 'fusion-builder' ),
						'<a href="https://theme-fusion.com/documentation/avada/forms/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Avada Forms Documentation', 'fusion-builder' ) . '</a>'
					);
					?>
				</p>
			</div>
			<?php do_action( 'fusion_form_admin_text' ); ?>
		</div>
		<form class="avada-db-create-form">
			<input type="hidden" name="action" value="fusion_form_new">
			<?php wp_nonce_field( 'fusion_new_form' ); ?>

			<div>
				<input type="text" placeholder="<?php esc_attr_e( 'Enter Your Form Name', 'fusion-builder' ); ?>" required id="fusion-form-set-name" name="name" />
			</div>

			<div>
				<input type="submit" value="<?php esc_attr_e( 'Create New Form', 'fusion-builder' ); ?>" class="button button-large button-primary avada-large-button" />
			</div>
		</form>
	</div>

	<div class="fusion-library-data-items avada-db-table">
		<?php
			$fusion_icons_table = new Fusion_Form_Builder_Table();
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
