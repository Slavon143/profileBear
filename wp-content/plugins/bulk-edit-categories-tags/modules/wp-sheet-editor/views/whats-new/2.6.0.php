<?php

$buy_link = VGSE()->bundles['custom_post_types']['inactive_action_url'];
$items = array(
	sprintf(__('New extension - WooCommerce Coupons - View/edit coupons in a spreadsheet. Advanced search, Bulk Edit hundreds of coupons, etc. Perfect for the christmas season. <a href="%s" target="_blank">View Extension</a>)', VGSE()->textname), 'https://wpsheeteditor.com/extensions/woocommerce-coupons-spreadsheet/'),
	sprintf(__('New extension - EVENTS - View/edit events, venues, and organizers in a spreadsheet. Advanced search, etc. <a href="%s" target="_blank">View Extension</a>)', VGSE()->textname), 'https://wpsheeteditor.com/extensions/events-spreadsheet/'),
	sprintf(__('New feature - Added support for Advanced Custom Fields > relationship fields. <b>Paid users only</b>. <a href="%s" target="_blank">Upgrade</a>)', VGSE()->textname), $buy_link),
	sprintf(__('Improved tools: formulas engine. Now you can make advanced searches and apply formulas to the search results. <b>Paid users only</b>. <a href="%s" target="_blank">Upgrade</a>)', VGSE()->textname), $buy_link),
	__('Improved sheet: Moved settings to a dropdown to simplify the UI and added new context menu options', VGSE()->textname),
	__('Improved tools: columns visibility. Now you can delete unnecessary columns and improved sorting and disable logic', VGSE()->textname),
	__('Fixed more than 13 bugs', VGSE()->textname),
	__('Improved 7 features', VGSE()->textname),
	__('Updated 6 extensions.', VGSE()->textname),
	sprintf(__('<a href="%s" target="_blank">View the entire changelog</a>', VGSE()->textname), 'https://wpsheeteditor.com/changelog/'),
);
