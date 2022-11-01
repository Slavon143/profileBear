<?php
$sheets = VGSE()->helpers->get_prepared_post_types();
$enabled_post_types = VGSE()->helpers->get_enabled_post_types();
$post_types = VGSE()->helpers->get_all_post_types(array(
	'show_in_menu' => true,
		));

if (empty($post_types)) {
	return;
}
?>

<form action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" method="POST" class="post-types-form">

	<p><?php _e('Available spreadsheets', VGSE()->textname); ?></p>

	<?php
	foreach ($sheets as $sheet) {
		$key = $sheet['key'];
		$post_type_name = $sheet['label'];
		$disabled = (!empty($sheet['is_disabled'])) ? ' disabled ' : '';
		$maybe_go_premium = $sheet['description'];
		?>
		<div class="post-type-field post-type-<?php echo esc_attr($key); ?>"><input type="checkbox" name="post_types[]" value="<?php echo esc_attr($key); ?>" id="<?php echo esc_attr($key); ?>" <?php echo esc_attr($disabled); ?> <?php checked(in_array($key, $enabled_post_types)); ?>> <label for="<?php echo esc_attr($key); ?>"><?php echo wp_kses_post($post_type_name); ?> <?php echo wp_kses_post($maybe_go_premium); ?></label></div>
	<?php } ?>
	<input type="hidden" name="action" value="vgse_save_post_types_setting">
	<input type="hidden" name="append" value="no">
	<input type="hidden" name="nonce" value="<?php echo wp_create_nonce('bep-nonce'); ?>">
	<button class="button button-primary hidden save-trigger button-primary"><?php _e('Save', VGSE()->textname); ?></button>
</form>
<script>
	jQuery(document).ready(function () {
		jQuery('.post-types-form .post-type-field').each(function () {
			var $postTypeSelector = jQuery(this);
			var postType = $postTypeSelector.find('input').val();
			if (postType && $postTypeSelector.find('input').prop('disabled') && jQuery('#toplevel_page_vg_sheet_editor_setup').html().indexOf('bulk-edit-' + postType) > -1) {
				$postTypeSelector.find('input').prop('disabled', false);
				$postTypeSelector.find('a').remove();
			}
			console.log('$postTypeSelector: ', $postTypeSelector, 'postType: ', postType);
		});
	});
</script>