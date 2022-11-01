<h3><?php _e('Advanced Settings', $this->textname); ?></h3>
<div class="wpse-settings-form-wrapper">

	<div class="tabs-links">
		<?php
		foreach ($sections as $section) {
			$section_id = sanitize_html_class($section['title']);
			?>
			<a href="#<?php echo $section_id; ?>"><?php echo esc_html($section['title']); ?></a>
		<?php }
		?>	
		<?php do_action('vg_plugin_sdk/settings/' . $this->args['opt_name'] . '/after_tab_links', $this, $sections); ?>
	</div>
	<form class="wpse-set-settings tabs-content">
		<?php
		foreach ($sections as $section) {
			$section_id = sanitize_html_class($section['title']);
			?>
			<div class="<?php echo $section_id; ?> tab-content">
				<?php
				foreach ($section['fields'] as $field) {
					if (empty($field['args'])) {
						$field['args'] = array();
					}
					$section_id = sanitize_html_class($section['title']);
					$value = $this->get_setting($field['id'], '');
					if (!empty($field['validate']) && $field['validate'] === 'numeric') {
						$input_type = 'number';
					} elseif (!empty($field['validate']) && $field['validate'] === 'url') {
						$input_type = 'url';
					} else {
						$input_type = 'text';
					}
					if ($field['type'] === 'info') {
						?>
						<div class="field-wrapper field-info">				
							<?php if (!empty($field['desc'])) { ?>
								<?php echo wpautop(wp_kses_post($field['desc'])); ?>
							<?php } ?>
						</div>
						<?php
						continue;
					}
					// Similar to info, but without background
					if ($field['type'] === 'message') {
						?>
						<div class="field-wrapper field-message">				
							<?php if (!empty($field['desc'])) { ?>
								<?php echo wpautop(wp_kses_post($field['desc'])); ?>
							<?php } ?>
						</div>
						<?php
						continue;
					}
					?>
					<div class="field-wrapper field-<?php echo sanitize_html_class($field['type']); ?>">
						<label for="<?php echo esc_attr($field['id']); ?>">
							<?php if ($field['type'] === 'switch') { ?>
								<input name="<?php echo esc_attr($field['id']); ?>" type="hidden" value=""/>
								<input id="<?php echo esc_attr($field['id']); ?>"  name="<?php echo esc_attr($field['id']); ?>" type="checkbox" value="1" <?php checked(1, (int) $value); ?> />
							<?php } ?> 
							<?php echo esc_html($field['title']); ?>
						</label>						
						<?php if (!empty($field['desc'])) { ?>
							<?php echo wpautop(wp_kses_post($field['desc'])); ?>
						<?php } ?>

						<?php if ($field['type'] === 'text') { ?>
							<input id="<?php echo esc_attr($field['id']); ?>" name="<?php echo esc_attr($field['id']); ?>" value="<?php echo esc_attr($value); ?>" type="<?php echo esc_attr($input_type); ?>" />
						<?php } ?>
						<?php if ($field['type'] === 'date') { ?>
							<input id="<?php echo esc_attr($field['id']); ?>" name="<?php echo esc_attr($field['id']); ?>" value="<?php echo esc_attr($value); ?>" type="date" />
						<?php } ?>
						<?php if ($field['type'] === 'color') { ?>
							<input id="<?php echo esc_attr($field['id']); ?>" name="<?php echo esc_attr($field['id']); ?>" value="<?php echo esc_attr($value); ?>" type="text" class="color-picker" />
						<?php } ?>
						<?php if ($field['type'] === 'password') { ?>
							<input id="<?php echo esc_attr($field['id']); ?>" name="<?php echo esc_attr($field['id']); ?>" value="<?php echo esc_attr($value); ?>" type="password" />
						<?php } ?>
						<?php if ($field['type'] === 'textarea') { ?>
							<textarea id="<?php echo esc_attr($field['id']); ?>" name="<?php echo esc_attr($field['id']); ?>"><?php echo esc_attr($value); ?></textarea>
						<?php } ?>						
						<?php
						if ($field['type'] === 'image_select') {
							if (is_array($value)) {
								$value = $value['id'];
							}
							?>
							<input id="<?php echo esc_attr($field['id']); ?>" name="<?php echo esc_attr($field['id']); ?>" value="<?php echo esc_attr($value); ?>" type="hidden" class="image-target"/>
							<button class="button open-image-library" type="button"><?php _e('Upload'); ?></button>
							<?php if (!empty($value)) { ?>
								<img src="<?php echo esc_url(wp_get_attachment_url($value)); ?>" height="80" />
							<?php } ?>
						<?php } ?>						
						<?php
						if ($field['type'] === 'editor') {
							wp_editor($value, $field['id'], wp_parse_args($field['args'], array(
								'textarea_rows' => 3
							)));
							?>
						<?php } ?>	
						<?php
						if ($field['type'] === 'checkbox') {
							$input_name = count($field['options']) === 1 ? $field['id'] : $field['id'] . '[]';
							foreach ($field['options'] as $option_key => $option_label) {
								?>
								<label>
									<input id="<?php echo esc_attr($field['id'] . $option_key); ?>" name="<?php echo esc_attr($input_name); ?>" value="<?php echo esc_attr($option_key); ?>" type="checkbox" <?php checked($option_key, is_array($value) ? in_array($option_key, $value, true) : $value); ?> />
									<?php echo esc_html($option_label); ?>
								</label>
								<?php
							}
							?>

						<?php } ?>	
						<?php
						if ($field['type'] === 'select') {
							$input_name = empty($field['multi']) ? $field['id'] : $field['id'] . '[]';
							if (!isset($field['options'][''])) {
								$field['options'][''] = '---';
							}
							?> 
							<select <?php
							if (!empty($field['multi'])) {
								echo 'multiple';
							}
							?>  id="<?php echo esc_attr($field['id']); ?>" name="<?php echo esc_attr($input_name); ?>">
									<?php
									foreach ($field['options'] as $option_key => $option_label) {
										?>
									<option value="<?php echo esc_attr($option_key); ?>" <?php selected($option_key, is_array($value) ? in_array($option_key, $value, true) : $value); ?>><?php echo esc_html($option_label); ?></option>
									<?php
								}
								?>
							</select>
						<?php } ?>	
						<?php
						if ($field['type'] === 'radio') {
							foreach ($field['options'] as $option_key => $option_label) {
								?>
								<label>
									<input id="<?php echo esc_attr($field['id'] . $option_key); ?>" name="<?php echo esc_attr($field['id']); ?>" value="<?php echo esc_attr($option_key); ?>" type="radio" <?php checked($option_key, $value); ?> />
									<?php echo esc_html($option_label); ?>
								</label>
								<?php
							}
							?>

						<?php } ?>				
						<?php
						if ($field['type'] === 'multi_text_repeater') {
							if (is_array($value)) {
								foreach ($value as $index => $value_row) {
									?>
									<div class="repeater-row">	
										<label><?php echo esc_html($field['first_placeholder']); ?></label>
										<input name="<?php echo esc_attr($field['id']); ?>[<?php echo (int) $index; ?>][]" value="<?php echo esc_attr($value_row[0]); ?>" type="text"/>

										<label><?php echo esc_html($field['second_placeholder']); ?></label>
										<input name="<?php echo esc_attr($field['id']); ?>[<?php echo (int) $index; ?>][]" value="<?php echo esc_attr($value_row[1]); ?>" type="text" />
										<button type="button" class="button remove">X</button>
									</div>
									<?php
								}
							}
							?>
							<div class="repeater-row-template" style="display: none;">		
								<label><?php echo esc_html($field['first_placeholder']); ?></label>						
								<input name="<?php echo esc_attr($field['id']); ?>[{index}][]" value="" type="text"/>

								<label><?php echo esc_html($field['second_placeholder']); ?></label>
								<input name="<?php echo esc_attr($field['id']); ?>[{index}][]" value="" type="text" />
								<button type="button" class="button remove">X</button>
							</div>
							<button type="button" class="button add-new">Add new</button>
						<?php } ?>		
						<?php
						if (!empty($field['callback_after_field']) && is_callable($field['callback_after_field'])) {
							call_user_func($field['callback_after_field'], $field);
						}
						?>
					</div>
					<?php
				}
				?>
			</div>
			<?php
		}
		?>
		<?php do_action('vg_plugin_sdk/settings/' . $this->args['opt_name'] . '/after_tabs_content', $this, $sections); ?>
		<br>
		<div class="actions">
			<button type="submit" class="button button-primary"><?php _e('Save', $this->textname); ?></button>
		</div>
		<?php wp_nonce_field('vgfpsdk_settings_' . $this->args['opt_name'], 'nonce'); ?>
	</form>
</div>
<style>
	.vg-plugin-sdk-page {
		max-width: 900px !important;
	}
	.wpse-settings-form-wrapper .tabs-links {
		width: 200px;
		text-align: left;
		float: left;
	}

	.wpse-settings-form-wrapper form.wpse-set-settings .field-wrapper {
		margin-bottom: 15px;
	}
	.wpse-settings-form-wrapper form.wpse-set-settings {
		margin-left: 230px;
		padding-left: 10px;
		text-align: left;
		border-left: 1px solid #aeaeae;
	}

	.wpse-settings-form-wrapper .tabs-links a.tab-active {
		background-color: #f1f1f1;
	}
	.wpse-settings-form-wrapper .tabs-links a {
		display: block;
		padding: 10px 0;
		color: black;
		text-decoration: none;
		border-bottom: 1px solid #aeaeae;
	}
	.tabs-content .tab-content {
		display: none;
	}


	.wpse-settings-form-wrapper form.wpse-set-settings label {
		display: block;
		font-weight: bold;
	}

	.wpse-settings-form-wrapper form.wpse-set-settings .actions {
		text-align: center;
	}

	.wpse-settings-form-wrapper form.wpse-set-settings input:not([type="checkbox"]),
	.wpse-settings-form-wrapper form.wpse-set-settings textarea {
		width: 100%;
	}

	.wpse-settings-form-wrapper form.wpse-set-settings textarea {
		min-height: 150px;
	}
	.wpse-settings-form-wrapper form.wpse-set-settings button.button-primary {
		width: 100%;
	}
	.wpse-settings-form-wrapper form.wpse-set-settings .field-wrapper.field-info {
		background-color: #ececec;
		padding: 20px;
		margin-bottom: 30px;
	}
	.wpse-settings-form-wrapper .field-multi_text_repeater .repeater-row {
		margin-bottom: 30px;
	}
</style>
<script>
	jQuery(document).ready(function () {

		function vgseSetSettings(data) {
			data.push({
				name: 'action',
				value: <?php echo json_encode('vgfpsdk_settings_' . $this->args['opt_name']); ?>
			});
			jQuery.ajax({
				url: ajaxurl,
				method: 'POST',
				data: data
			}).success(function (response) {
				// Remove the hash from the url so it doesn't open the settings popup again after reload
				window.location.hash = '';
				alert(<?php echo json_encode(__('Settings saved', $this->textname)); ?>);
				jQuery('body').trigger('vgfpsdkSettings/AfterSaved', response);
				jQuery('form.wpse-set-settings button[type="submit"]').each(function () {
					if (jQuery(this).data('originalText')) {
						jQuery(this).text(jQuery(this).data('originalText'));
					}

				});
			});
		}

		jQuery('body').on('submit', 'form.wpse-set-settings', function (e) {
			var $buttons = jQuery(this).find('button[type="submit"]');
			$buttons.each(function () {
				var $button = jQuery(this);
				if (!$button.data('originalText')) {
					$button.data('originalText', $button.text());
				}
				$button.text('Saving...');
			});

			vgseSetSettings(jQuery(this).serializeArray());
			return false;
		});

		// Settings tabs
		jQuery('.wpse-settings-form-wrapper .tabs-links a').click(function (e) {
			e.preventDefault();
			jQuery('.wpse-settings-form-wrapper .tabs-links a').removeClass('tab-active');
			jQuery(this).addClass('tab-active');

			var id = jQuery(this).attr('href').replace('#', '');
			var $links = jQuery(this).parents('.tabs-links');
			var $content = $links.next().find('.' + id);

			$links.next().find('.tab-content').hide();
			$content.show();
		});
		jQuery('.wpse-settings-form-wrapper .color-picker').each(function () {
			jQuery(this).wpColorPicker();
		});
		jQuery('body').on('click', '.wpse-settings-form-wrapper .field-multi_text_repeater .add-new', function (e) {
			e.preventDefault();
			var $repeater = jQuery(this).parents('.field-multi_text_repeater');
			var newHtml = $repeater.find('.repeater-row-template')[0].outerHTML;
			newHtml = newHtml.replace(/\{index\}/g, $repeater.find('.repeater-row').length).replace(/repeater-row-template/g, 'repeater-row').replace('display: none;', '');
			$repeater.find('.repeater-row-template').before(newHtml);
		});
		jQuery('body').on('click', '.wpse-settings-form-wrapper .field-multi_text_repeater .remove', function (e) {
			e.preventDefault();
			jQuery(this).parent().remove();
		});
		jQuery('.wpse-settings-form-wrapper .tabs-links').each(function () {
			jQuery(this).find('a').first().click();
		});

		jQuery('.wpse-settings-form-wrapper .open-image-library').click(function (e) {
			e.preventDefault();
			var $button = jQuery(this);
			var $input = $button.parent().find('input');

			media_uploader = wp.media({
				frame: "post",
				state: "insert",
				multiple: false
			});
			function selectImage(embed) {
				console.log('url: ', embed.url);
				$input.val(embed.id).trigger('change');
				$button.parent().find('img').remove();
				$button.parent().append('<img src="' + embed.url + '" width="80" height="80"/>');
			}
			media_uploader.state('embed').on('select', function () {
				var state = media_uploader.state(),
						type = state.get('type'),
						embed = state.props.toJSON();
				embed.url = embed.url || '';
				console.log(embed);
				console.log(type);
				console.log(state);
				console.log('url: ', embed.url);
				if (type === 'image' && embed.url) {
// Guardar img					
					selectImage(embed);

				}



			});
			media_uploader.on("insert", function () {
				var selection = media_uploader.state().get("selection");
				var length = selection.length;
				var images = selection.models;


				console.log(images);
				if (!length) {
					return true;
				}
				selectImage(images[0].attributes);
			});
			media_uploader.open();
		});
	});
</script>