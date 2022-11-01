<h3><?php _e( 'Advanced Settings', VGSE()->textname ); ?></h3>
<div class="wpse-settings-form-wrapper">

	<div class="tabs-links">
		<?php
		foreach ( $sections as $tab_index => $section ) {
			?>
			<a href="#tab<?php echo sanitize_html_class( $tab_index ); ?>"><?php echo esc_html( $section['title'] ); ?></a>
			<?php
		}
		?>
			
		<a href="#reset-settings"><?php _e( 'Reset settings', VGSE()->textname ); ?></a>
		<a href="#export-import-settings"><?php _e( 'Export and import settings', VGSE()->textname ); ?></a>
		<?php do_action( 'vg_sheet_editor/settings/after_tab_links', $provider, $sections ); ?>
	</div>
	<form class="wpse-set-settings tabs-content" data-reload-after-success="1">
		<?php
		foreach ( $sections as $tab_index => $section ) {
			?>
			<div class="tab<?php echo sanitize_html_class( $tab_index ); ?> <?php echo esc_attr( $section['title'] ); ?> tab-content">
				<?php
				foreach ( $section['fields'] as $field ) {
					$value      = isset( VGSE()->options[ $field['id'] ] ) ? VGSE()->options[ $field['id'] ] : '';
					$input_type = ! empty( $field['validate'] ) && $field['validate'] === 'numeric' ? 'number' : 'text';
					?>
					<div class="field-wrapper">
						<label for="<?php echo esc_attr( $field['id'] ); ?>">
							<?php if ( $field['type'] === 'switch' ) { ?>
								<input name="settings[<?php echo esc_attr( $field['id'] ); ?>]" type="hidden" value=""/>
								<input class="<?php echo sanitize_html_class( $field['class_name'] ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>"  name="settings[<?php echo esc_attr( $field['id'] ); ?>]" type="checkbox" value="1" <?php checked( 1, (int) $value ); ?> />
							<?php } ?> 
							<?php echo esc_html( $field['title'] ); ?>

							<?php if ( ! empty( $field['desc'] ) ) { ?>
								<a href="#" data-wpse-tooltip="right" aria-label="<?php echo esc_attr( $field['desc'] ); ?>">( ? )</a>
							<?php } ?>
						</label>

						<?php if ( $field['type'] === 'text' ) { ?>
							<input  class="<?php echo sanitize_html_class( $field['class_name'] ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" name="settings[<?php echo esc_attr( $field['id'] ); ?>]" value="<?php echo esc_attr( $value ); ?>" type="<?php echo esc_attr( $input_type ); ?>" />
						<?php } ?>
						<?php if ( $field['type'] === 'textarea' ) { ?>
							<textarea class="<?php echo sanitize_html_class( $field['class_name'] ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" name="settings[<?php echo esc_attr( $field['id'] ); ?>]"><?php echo esc_attr( $value ); ?></textarea>
						<?php } ?>		
						<?php
						if ( $field['type'] === 'new_select' ) {
							$input_name = empty( $field['multi'] ) ? 'settings[' . $field['id'] . ']' : 'settings[' . $field['id'] . '][]';
							if ( ! isset( $field['options'][''] ) ) {
								$field['options'][''] = '---';
							}
							?>							 
							<select class="<?php echo sanitize_html_class( $field['class_name'] ); ?>" 
							<?php
							if ( ! empty( $field['multi'] ) ) {
								echo 'multiple';
							}
							?>
							id="<?php echo esc_attr( $field['id'] ); ?>" name="<?php echo esc_attr( $input_name ); ?>">
									<?php
									foreach ( $field['options'] as $option_key => $option_label ) {
										?>
									<option value="<?php echo esc_attr( $option_key ); ?>" <?php selected( is_array( $value ) ? in_array( $option_key, $value, true ) : $value === $option_key ); ?>><?php echo esc_html( $option_label ); ?></option>
										<?php
									}
									?>
							</select>
						<?php } ?>					
					</div>
					<?php
				}
				?>
			</div>
			<?php
		}
		?>
		<div class="reset-settings tab-content">
			<p><?php _e( 'We will display all the columns that were deleted or disabled, renamed columns will show the original titles, we will rescan the database to find columns again, and the speed/advanced settings will be reset to the defaults. This only affects settings of our plugin and it does not affect the data edited with the sheet.', VGSE()->textname ); ?></p>
			<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wpse_hard_reset', 1 ), 'wpse', 'wpse_nonce' ) ); ?>"><?php _e( 'Reset settings', VGSE()->textname ); ?></a>
		</div>
		<div class="export-import-settings tab-content">
			<p><?php _e( 'These options will be included in the export and import:', VGSE()->textname ); ?></p>
			<ol>
				<li><?php _e( 'Column sizes', VGSE()->textname ); ?></li>
				<li><?php _e( 'Column titles', VGSE()->textname ); ?></li>
				<li><?php _e( 'Column settings defined in the columns manager', VGSE()->textname ); ?></li>
				<li><?php _e( 'Columns created manually', VGSE()->textname ); ?></li>
				<li><?php _e( 'Advanced settings', VGSE()->textname ); ?></li>
				<li><?php _e( 'Saved exports', VGSE()->textname ); ?></li>
				<li><?php _e( 'Saved searches', VGSE()->textname ); ?></li>
				<li><?php _e( 'List of deleted columns', VGSE()->textname ); ?></li>
				<li><?php _e( 'Favorite search fields', VGSE()->textname ); ?></li>
				<li><?php _e( 'Column groups', VGSE()->textname ); ?></li>
				<li><?php _e( 'Post types created with WP Sheet Editor', VGSE()->textname ); ?></li>
			</ol>
			<hr>
			<a target="_blank" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wpse_export_settings', 1 ), 'wpse', 'wpse_nonce' ) ); ?>"><?php _e( 'Click here to export the settings', VGSE()->textname ); ?></a>
			<hr>
			<label><b><?php _e( 'Import settings', VGSE()->textname ); ?></b></label>
			<p><?php _e( 'Paste the settings here (the contents of the exported file). Notes:', VGSE()->textname ); ?></p>
			<ol>
				<li><?php _e( 'The import will overwrite existing settings', VGSE()->textname ); ?></li>
				<li><?php _e( 'Please make a database backup before the import to be safe', VGSE()->textname ); ?></li>
				<li><?php _e( 'Some columns depend on other plugins. So the source site and this site must use the same plugins to have the same columns', VGSE()->textname ); ?></li>
			</ol>
			<textarea name="wpse_import_settings" style="min-height: 150px;"></textarea>

		</div>
		<?php do_action( 'vg_sheet_editor/settings/after_tabs_content', $provider, $sections ); ?>
		<br>
		<div class="actions">
			<button type="submit" class="remodal-confirm"><?php _e( 'Save', VGSE()->textname ); ?></button>
			<button type="button" data-remodal-action="confirm" class="remodal-cancel"><?php _e( 'Close', VGSE()->textname ); ?></button>
		</div>
	</form>
</div>
