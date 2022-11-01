<?php
$instance = vgse_taxonomy_terms();
?>
<p><?php _e('Thank you for installing our plugin.', $instance->textname); ?></p>

<?php
$steps = array();

$sheets = apply_filters('vg_sheet_editor/terms/welcome_sheets', $GLOBALS['wpse_taxonomy_terms_sheet']->get_prop('post_type'));
$sheets_buttons = '';


$taxonomies = array_merge(get_taxonomies(array(
	'public' => true,
	'show_ui' => true,
	'_builtin' => true,
				), 'objects'), get_taxonomies(array(
	'show_ui' => true,
	'_builtin' => false,
				), 'objects'));
$all = apply_filters('vg_sheet_editor/terms/welcome_sheets_all', array(
	'post_types' => array_values(wp_list_pluck($taxonomies, 'name')),
	'labels' => array_values(wp_list_pluck($taxonomies, 'label')),
		));

foreach ($all['post_types'] as $index => $sheet) {
	if (in_array($sheet, $sheets)) {
		$sheets_buttons .= '<br><a href="' . esc_url(VGSE()->helpers->get_editor_url($sheet)) . '" class="button">Edit ' . $all['labels'][$index] . '</a>';
	} else {
		$sheets_buttons .= '<br>' . $all['labels'][$index] . '. <a href="' . esc_url(VGSE()->get_buy_link('sheet-locked-column-' . $sheet)) . '" >Premium</a>';
	}
}
$steps['open_editor'] = '<p>' . sprintf(__('You can open the Bulk Editor Now:  %s', $instance->textname), $sheets_buttons) . '</p>';

include VGSE_DIR . '/views/free-extensions-for-welcome.php';
$steps['free_extensions'] = $free_extensions_html;

$steps = apply_filters('vg_sheet_editor/taxonomy_terms/welcome_steps', $steps);

if (!empty($steps)) {
	echo '<ol class="steps">';
	foreach ($steps as $key => $step_content) {
		if (empty($step_content)) {
			continue;
		}
		?>
		<li><?php echo wp_kses_post($step_content); ?></li>		
		<?php
	}

	echo '</ol>';
}	