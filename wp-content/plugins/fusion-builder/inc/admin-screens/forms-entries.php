<?php
/**
 * Admin Screen markup (Layout Sections builder page).
 *
 * @package fusion-builder
 */

$fusion_forms = new Fusion_Form_DB_Forms();
$forms        = $fusion_forms->get_formatted();
ksort( $forms );
?>
<?php Fusion_Builder_Admin::header( 'form-entries' ); ?>

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
	<form>
		<input type="hidden" name="action" value="fusion_forms_new">
		<?php wp_nonce_field( 'fusion_new_form' ); ?>

		<div>
			<input type="text" placeholder="<?php esc_attr_e( 'Enter Your Form Name', 'fusion-builder' ); ?>" required id="fusion-form-name" name="name" />
		</div>

		<div>
			<input type="submit" value="<?php esc_attr_e( 'Create New Form', 'fusion-builder' ); ?>" class="button button-large button-primary avada-large-button" />
		</div>
	</form>
</div>

<div class="fusion-form-welcome-content">
	<?php
	$form_id = key( $forms );
	if ( isset( $_GET['form_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$form_id = sanitize_text_field( wp_unslash( $_GET['form_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
	}
	if ( ! empty( $forms ) ) {
		$form_creator_list_table = new Fusion_Form_List_Table( $form_id );
		$form_creator_list_table->prepare_items();
		?>
		<label class="form-heading-inline" for="fusion-forms">
			<?php ob_start(); ?>
			<select id="fusion-forms" onchange="document.location='<?php echo esc_attr( admin_url( 'admin.php?page=avada-form-entries&form_id=' ) ); ?>' + this.value;">
				<?php foreach ( $forms as $key => $form_data ) : ?>
					<?php
					$form_title = get_the_title( $form_data['form_id'] );
					if ( '' !== $form_title ) :
						?>
					?>
					<option class="fusion-form" <?php selected( (int) $key, (int) $form_id ); ?> value="<?php echo esc_attr( $key ); ?>">
						<?php echo esc_html( $form_title ); ?>
					</option>
					<?php endif; ?>
				<?php endforeach; ?>
			</select>
			<?php
			$form_entries_select = ob_get_clean();
			printf(
				/* Translators: The dropdown. */
				esc_html__( 'Form Entries for: %s', 'fusion-builder' ),
				// Note to reviewers: This doesn't need to be escaped, it has already been taken care of.
				$form_entries_select // phpcs:ignore WordPress.Security.EscapeOutput
			);
			?>
			</label>
			<?php wp_nonce_field( 'fusion-form-nonce', 'fusion-form-export-nonce' ); ?>
			<a href="<?php echo esc_url_raw( admin_url( 'admin.php?page=avada-form-entries&fusion_action=csv_export&form_id=' . $form_id ) ); ?>" id="fusion-form-export" class="button <?php echo ! empty( $form_creator_list_table->_pagination_args ) && 0 < $form_creator_list_table->_pagination_args['total_items'] ? '' : 'disabled'; ?>" data-form-id="<?php echo esc_attr( $form_id ); ?>" title="<?php esc_attr_e( 'Export Entries as CSV', 'fusion-builder' ); ?>"><?php esc_html_e( 'Export Entries', 'fusion-builder' ); ?></a>
			<span id="fusion-form-export-status"><span id="fusion-form-export-status-bar"></span></span>
		<?php
		$form_creator_list_table->display();
	} else {
		?>
		<div class="fusion-builder-important-notice avada-db-card">
			<h2><?php esc_html_e( 'No Form Entries Available', 'fusion-builder' ); ?></h2>
			<p>
				<?php esc_html_e( 'Currently no entries are available. If you haven\'t created a form already, you can do it above by adding a name for your new Avada Form and by clicking "Create New Form" button. Saved forms can be displayed on any page or post using the Avada Form element or the Avada Form widget.', 'fusion-builder' ); ?>
			</p>
			<p>
				<?php esc_html_e( 'Once there are form submissions from users, the entries will be displayed here. You can then view individual entries for each form.', 'fusion-builder' ); ?>
			</p>
		</div>
		<?php
	}
	?>
</div>

<?php Fusion_Builder_Admin::footer(); ?>
