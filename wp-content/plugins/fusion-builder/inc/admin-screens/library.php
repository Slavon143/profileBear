<?php
/**
 * Admin Screen markup (Library page).
 *
 * @package fusion-builder
 */

?>
<?php Fusion_Builder_Admin::header( 'library' ); ?>

	<div class="fusion-builder-important-notice fusion-template-builder avada-db-card avada-db-card-first">
		<div class="intro-text">
			<h1><?php esc_html_e( 'Avada Library', 'fusion-builder' ); ?></h1>
			<p><?php esc_html_e( 'The Avada Library contains your saved Page Templates, Containers, Columns, Elements and Post Cards. Here, you can create and manage your library content.', 'fusion-builder' ); ?></p>

			<div class="avada-db-card-notice">
				<i class="fusiona-info-circle"></i>
				<p class="avada-db-card-notice-heading">
					<?php
					printf(
						/* translators: %s: "Icons Documentation Link". */
						esc_html__( 'Please see the %s.', 'fusion-builder' ),
						'<a href="https://theme-fusion.com/documentation/avada/library/avada-builder-library/" target="_blank">' . esc_attr__( 'Avada Library Documentation', 'fusion-builder' ) . '</a>'
					);
					?>
				</p>
			</div>
		</div>
		<form class="avada-db-create-form">
			<input type="hidden" name="action" value="fusion_library_new">

			<select id="fusion-library-type" name="fusion_library_type" >
				<option value="" disabled selected><?php esc_html_e( 'Select Library Element Type', 'fusion-builder' ); ?></option>
			<?php
				$types = [
					'templates'  => esc_html__( 'Template', 'fusion-builder' ),
					'sections'   => esc_html__( 'Container', 'fusion-builder' ),
					'columns'    => esc_html__( 'Column', 'fusion-builder' ),
					'elements'   => esc_html__( 'Element', 'fusion-builder' ),
					'post_cards' => esc_html__( 'Post Card', 'fusion-builder' ),
				];
				?>
			<?php foreach ( $types as $type_name => $type_label ) : ?>
				<option value="<?php echo esc_attr( $type_name ); ?>"><?php echo esc_html( $type_label ); ?></option>
			<?php endforeach; ?>

			</select>
			<?php wp_nonce_field( 'fusion_library_new_element' ); ?>

			<input class="library-element-name" type="text" placeholder="<?php esc_attr_e( 'Enter Element Name', 'fusion-builder' ); ?>" required id="fusion-library-name" name="name" />

			<div id="fusion-global-field">
				<label for="fusion-library-global"><?php esc_html_e( 'Global element', 'fusion-builder' ); ?></label>
				<input type="checkbox" id="fusion-library-global" name="global" />
			</div>

			<div>
				<input type="submit" value="<?php esc_attr_e( 'Create Library Element', 'fusion-builder' ); ?>" class="button button-large button-primary avada-large-button" />
			</div>
		</form>
	</div>

	<div class="fusion-library-data-items avada-db-table">
		<?php
			$fusion_library_table = new Fusion_Builder_Library_Table();
			$fusion_library_table->get_status_links();
		?>
		<form id="fusion-library-data" method="get">
			<?php
			$fusion_library_table->prepare_items();
			$fusion_library_table->display();
			?>
		</form>
	</div>

<?php Fusion_Builder_Admin::footer(); ?>
