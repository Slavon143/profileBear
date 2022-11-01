<?php
/**
 * Admin Screen markup (Library page).
 *
 * @package fusion-builder
 */

?>
<?php Fusion_Builder_Admin::header( 'off-canvas' ); ?>

	<div class="fusion-builder-important-notice fusion-template-builder avada-db-card avada-db-card-first">
		<div class="intro-text">
			<h1><?php esc_html_e( 'Off Canvas', 'fusion-builder' ); ?></h1>
			<p><?php esc_html_e( 'The Avada Off Canvas Builder allows you to create a wide range of popups and sliding bars. Here, you can create Off Canvas items and manage existing ones.', 'fusion-builder' ); ?></p>

			<div class="avada-db-card-notice">
				<i class="fusiona-info-circle"></i>
				<p class="avada-db-card-notice-heading">
					<?php
					printf(
						/* translators: %s: "Icons Documentation Link". */
						esc_html__( 'Please see the %s.', 'fusion-builder' ),
						'<a href="https://theme-fusion.com/documentation/categories/off-canvas/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Off Canvas Documentation', 'fusion-builder' ) . '</a>'
					);
					?>
				</p>
			</div>
		</div>
		<form class="avada-db-create-form">
			<input type="hidden" name="action" value="awb_off_canvas_new">
			<?php wp_nonce_field( 'awb_off_canvas_new' ); ?>

			<div>
				<input type="text" placeholder="<?php esc_attr_e( 'Enter Off Canvas Name', 'fusion-builder' ); ?>" required id="awb-off-canvas-name" name="name" />
			</div>

			<div>
				<input type="submit" value="<?php esc_attr_e( 'Create New Off Canvas', 'fusion-builder' ); ?>" class="button button-large button-primary avada-large-button" />
			</div>
		</form>
	</div>

	<div class="fusion-library-data-items avada-db-table">
		<?php
			$awb_off_canvas_table = new AWB_Off_Canvas_Table();
			$awb_off_canvas_table->get_status_links();
		?>
		<form id="fusion-library-data" method="get">
			<?php
			$awb_off_canvas_table->prepare_items();
			$awb_off_canvas_table->display();
			?>
		</form>
	</div>
<?php Fusion_Builder_Admin::footer(); ?>
