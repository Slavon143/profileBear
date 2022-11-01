<?php
// The free extensions should appear only when using a free plugin
if (VGSE_ANY_PREMIUM_ADDON) {
	$free_extensions_html = '';
	return;
}
$sheets = wp_list_filter(VGSE()->helpers->get_prepared_post_types(), array('is_disabled' => true));
ob_start();
foreach ($sheets as $sheet) {
	if (strpos($sheet['description'], 'free') === false) {
		continue;
	}
	?>

	<p><?php printf(__('Spreadsheet for %s %s', VGSE()->textname), $sheet['label'], str_replace(array('<small>', '</small>'), '', $sheet['description'])); ?></p>
	<?php
}
$free_extensions = ob_get_clean();
$free_extensions_html = __('<p>Free extensions</p>', VGSE()->textname) . $free_extensions;
