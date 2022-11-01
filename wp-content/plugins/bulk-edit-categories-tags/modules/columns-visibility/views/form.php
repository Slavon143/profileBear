
<div data-remodal-id="modal-columns-visibility" data-remodal-options="closeOnOutsideClick: false" class="remodal remodal<?php echo esc_attr($random_id); ?> modal-columns-visibility">

	<div class="modal-content">
		<?php if (!$partial_form) { ?>
			<form action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" method="POST" class="vgse-modal-form" data-nonce="<?php echo wp_create_nonce('bep-nonce'); ?>" id="columns-manager-form">
			<?php } ?>
			<h3><?php _e('Columns manager', VGSE()->textname); ?></h3>
			<ul class="unstyled-list">
				<li>
					<p><?php _e('Drag the columns to the left or right side to enable/disable them, drag them to the top or bottom to sort them, click on the "edit" button to rename them, click on the "x" button to delete them completely (only when they are disabled previously).', VGSE()->textname); ?><?php do_action('vg_sheet_editor/columns_visibility/after_instructions', $post_type, $visible_columns, $options[$post_type], $editor); ?></p> 

					<!--These options were replaced with the "bulk actions" added to each list-->
					<!--<button class="button vgse-change-all-states" data-to="enabled"><?php _e('Enable all', VGSE()->textname); ?></button> ---> 
					<!--<button class="button vgse-change-all-states" data-to="disabled"><?php _e('Disable all', VGSE()->textname); ?></button>-->

				</li>
				<li>
					<div class="vgse-sorter-section">

						<h3><?php _e('Enabled', VGSE()->textname); ?> <button type="button" class="toggle-search-button"><i class="fa fa-edit"></i> <?php _e('Bulk', VGSE()->textname); ?></button></h3>
						<div class="wpse-columns-bulk-actions">
							<input type="search" class="wpse-filter-list" placeholder="<?php _e('Enter a search term...', VGSE()->textname); ?>">
							<select class="wpse-bulk-action">
								<option value=""><?php _e('Bulk actions', VGSE()->textname); ?></option>
								<option value="disable"><?php _e('Disable all', VGSE()->textname); ?></option>
								<option value="sort_alphabetically_asc"><?php _e('Sort alphabetically ASC', VGSE()->textname); ?></option>
								<option value="sort_alphabetically_desc"><?php _e('Sort alphabetically DESC', VGSE()->textname); ?></option>
							</select>
						</div>


						<ul class="vgse-sorter columns-enabled" id="vgse-columns-enabled">
							<?php
							foreach ($visible_columns as $column_key => $column) {
								if (is_numeric($column_key)) {
									continue;
								}
								if (in_array($column_key, $not_allowed_columns)) {
									continue;
								}
								if (isset($options[$post_type]['disabled']) && isset($options[$post_type]['disabled'][$column_key])) {
									continue;
								}
								if (!isset($column['title'])) {
									continue;
								}
								$title = $column['title'];
								?>
								<li><span class="handle">::</span> <span class="column-title" title="<?php echo esc_attr($title); ?>"><?php echo esc_html($title); ?></span>
									<input type="hidden" name="columns[]" class="js-column-key" value="<?php echo esc_attr($column_key); ?>" />
									<input type="hidden" name="columns_names[]" class="js-column-title" value="<?php echo esc_attr($title); ?>" />

									<?php if (current_user_can('manage_options')) { ?>
										<button class="remove-column column-action" title="<?php echo esc_attr(__('Remove column completely. If you want to use it later you can disable it by dragging and dropping to the right column', VGSE()->textname)); ?>"><i class="fa fa-remove"></i></button>
									<?php } ?>
									<button class="deactivate-column column-action" title="<?php echo esc_attr(__('Disable column. You can enable it later.', VGSE()->textname)); ?>"><i class="fa fa-arrow-right"></i></button>
									<button class="enable-column column-action" title="<?php echo esc_attr(__('Enable column', VGSE()->textname)); ?>"><i class="fa fa-arrow-left"></i></button>
									<?php do_action('vg_sheet_editor/columns_visibility/enabled/after_column_action', $column, $post_type); ?>
								</li>
							<?php }
							?>
						</ul>
					</div>
					<div class="vgse-sorter-section">
						<h3><?php _e('Disabled', VGSE()->textname); ?> <button type="button" class="toggle-search-button"><i class="fa fa-edit"></i> <?php _e('Bulk', VGSE()->textname); ?></button></h3>

						<div class="wpse-columns-bulk-actions">
							<input type="search" class="wpse-filter-list" placeholder="<?php _e('Enter a search term...', VGSE()->textname); ?>">
							<select class="wpse-bulk-action">
								<option value=""><?php _e('Bulk actions', VGSE()->textname); ?></option>
								<option value="enable"><?php _e('Enable all', VGSE()->textname); ?></option>
								<option value="delete"><?php _e('Delete all', VGSE()->textname); ?></option>
								<option value="sort_alphabetically_asc"><?php _e('Sort alphabetically ASC', VGSE()->textname); ?></option>
								<option value="sort_alphabetically_desc"><?php _e('Sort alphabetically DESC', VGSE()->textname); ?></option>
							</select>
						</div>
						<ul class="vgse-sorter columns-disabled" id="vgse-columns-disabled"><?php
							if (isset($options[$post_type]['disabled'])) {
								foreach ($options[$post_type]['disabled'] as $column_key => $column_title) {
									if (is_numeric($column_key)) {
										continue;
									}
									if (in_array($column_key, $not_allowed_columns)) {
										continue;
									}
									if (is_object($editor->args['columns']) && $editor->args['columns']->is_column_blacklisted($column_key, $post_type)) {
										continue;
									}
									if (isset($columns[$column_key])) {
										$column_title = $columns[$column_key]['title'];
									}
									?>
									<li><span class="handle">::</span> <span class="column-title" title="<?php echo esc_attr($column_title); ?>"><?php echo esc_html($column_title); ?></span>  <i class="fa fa-refresh"  data-wpse-tooltip="right" aria-label="<?php _e('Enabling this column requires a page reload', VGSE()->textname); ?>">&#xf021;</i>
										<input type="hidden" name="disallowed_columns[]" class="js-column-key" value="<?php echo esc_attr($column_key); ?>" />
										<input type="hidden" name="disallowed_columns_names[]" class="js-column-title" value="<?php echo esc_attr($column_title); ?>" />
										<?php if (current_user_can('manage_options')) { ?>
											<button class="remove-column column-action" title="<?php echo esc_attr(__('Remove column completely. If you want to use it later you can disable it by dragging and dropping to the right column', VGSE()->textname)); ?>"><i class="fa fa-remove"></i></button>
										<?php } ?>
										<button class="deactivate-column column-action" title="<?php echo esc_attr(__('Disable column. You can enable it later.', VGSE()->textname)); ?>"><i class="fa fa-arrow-right"></i></button>
										<button class="enable-column column-action" title="<?php echo esc_attr(__('Enable column', VGSE()->textname)); ?>"><i class="fa fa-arrow-left"></i></button>
										<?php do_action('vg_sheet_editor/columns_visibility/disabled/after_column_action', $column, $post_type); ?>
									</li>
									<?php
								}
							}
							?></ul>
					</div>
					<div class="clear"></div>
				</li>
				<?php if (is_admin() && current_user_can('manage_options')) { ?>
					<li class="missing-column-tips">					
						<h3><?php _e('A column is missing?', VGSE()->textname); ?></h3>
						<ul>
							<li><?php _e('- First, edit one item in the normal editor and fill all the fields manually.', VGSE()->textname); ?></li>
							<?php
							if (empty($options[$post_type]['enabled'])) {
								$options[$post_type]['enabled'] = array();
							}
							if (empty($options[$post_type]['disabled'])) {
								$options[$post_type]['disabled'] = array();
							}
							?>
							<li><?php _e('- We can scan the database, find new fields, and create columns automatically', VGSE()->textname); ?> <a class="wpse-scan-db-link" href="<?php 
								if( wp_doing_ajax() ){
									$rescan_url = add_query_arg(array('wpse_rescan_db_fields' => $post_type), wp_get_referer());
								} else {
									$rescan_url = ( $current_url ) ? add_query_arg(array('wpse_rescan_db_fields' => $post_type), $current_url) : add_query_arg(array('wpse_rescan_db_fields' => $post_type));
								}
								echo esc_url($rescan_url);
								?>"  data-wpse-tooltip="right" aria-label="<?php esc_attr_e('You can do this multiple times', VGSE()->textname); ?>"><?php _e('Scan Now', VGSE()->textname); ?></a></li>

							<?php 
							if (class_exists('WP_Sheet_Editor_Custom_Columns') && VGSE()->helpers->is_editor_page()) {
								?>
								<li><?php _e('- If the previous solution failed, you can create new columns manually.', VGSE()->textname); ?> <a class="" href="<?php echo esc_url(admin_url('admin.php?page=vg_sheet_editor_custom_columns')); ?>"><?php _e('Create column', VGSE()->textname); ?></a></li>
							<?php } ?>
							<li><?php _e('- Maybe you deleted the columns from the list.', VGSE()->textname); ?> <a class="vgse-restore-removed-columns" href="javascript:void(0)"><?php _e('Restore deleted columns', VGSE()->textname); ?></a></li>	
							<li><?php _e('- We can help you.', VGSE()->textname); ?> <a class="" target="_blank" href="<?php echo esc_url(VGSE()->get_support_links('contact_us', 'url', 'sheet-missing-column')); ?>"><?php _e('Contact us', VGSE()->textname); ?></a></li>	
						</ul>
						</p>	
					</li>				
				<?php } ?>
				<li class="vgse-allow-save-settings">
					<label><input type="checkbox" value="yes" name="save_post_type_settings" class="save_post_type_settings" /> <?php _e('Save these settings for future sessions?', VGSE()->textname); ?> <a href="#" data-wpse-tooltip="right" aria-label="If you enable this option, we will use these settings the next time you load the editor for this post type.">( ? )</a></label>

				</li>

				<?php do_action('vg_sheet_editor/columns_visibility/after_fields', $post_type); ?>

				<li class="vgse-save-settings">
					<?php if (!$partial_form) { ?>
						<button type="submit" class="remodal-confirm"><?php _e('Apply settings', VGSE()->textname); ?></button>
						<button data-remodal-action="confirm" class="remodal-cancel"><?php _e('Close', VGSE()->textname); ?></button>
					<?php } ?>
				</li>
			</ul>
			<input type="hidden" value="<?php echo esc_attr(implode(',', $not_allowed_columns)); ?>" class="not-allowed-columns" name="vgse_columns_disabled_all_keys">
			<input type="hidden" value="" class="all-allowed-columns" name="vgse_columns_enabled_all_keys">
			<?php if (!$partial_form) { ?>
				<input type="hidden" value="vgse_update_columns_visibility" name="action">
				<input type="hidden" value="<?php echo esc_attr($nonce); ?>" name="nonce">
				<input type="hidden" value="<?php echo esc_attr($post_type); ?>" name="post_type">
			<?php } ?>
			<input type="hidden" value="<?php echo esc_attr($post_type); ?>" name="wpsecv_post_type">
			<input type="hidden" value="<?php echo esc_attr($nonce); ?>" name="wpsecv_nonce">
			<input type="hidden" value="" name="wpse_auto_reload_after_saving">

			<?php if (!$partial_form) { ?>
			</form>
		<?php } ?>
	</div>
	<br>
</div>