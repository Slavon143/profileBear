<?php

/*
Copyright (C) Pimwick, LLC

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="pwbe-activation-main" class="pwbe-activation-main">
	<div class="pwbe-activation-p"><?php _e( 'Need more power? Upgrade to Pro and you can do things like:', 'pw-bulk-edit' ); ?> <span id="pwbe-features"></span></div>
	<div class="pwbe-heading"><a href="https://www.pimwick.com/pw-bulk-edit/" target="_blank"><?php _e( 'See what else the Pro version can do', 'pw-bulk-edit' ); ?></a></div>
</div>
<br />
<script>
	var pwbeFeatures = [
		'<?php _e( 'Bulk edit Sale Price, Sale Start Date, and Sale End Date', 'pw-bulk-edit' ); ?>',
		'<?php _e( 'Bulk change the Sale price based on Regular price', 'pw-bulk-edit' ); ?>',
		'<?php _e( 'Edit any of YOUR custom Attributes', 'pw-bulk-edit' ); ?>',
		'<?php _e( 'Set default values for Variable products', 'pw-bulk-edit' ); ?>',
		'<?php _e( 'Edit Categories', 'pw-bulk-edit' ); ?>',
		'<?php _e( 'Edit the Short Description field', 'pw-bulk-edit' ); ?>',
		'<?php _e( 'Edit the Sold Individually field', 'pw-bulk-edit' ); ?>',
		'<?php _e( 'Edit Tags', 'pw-bulk-edit' ); ?>',
		'<?php _e( 'Export results to CSV', 'pw-bulk-edit' ); ?>',
		'<?php _e( 'Bulk Delete Products and Variations', 'pw-bulk-edit' ); ?>',
		'<?php _e( 'Bulk edit Featured Product Image and Product Gallery Images', 'pw-bulk-edit' ); ?>',
		'<?php _e( 'Edit Variation Descriptions', 'pw-bulk-edit' ); ?>',
		'<?php _e( 'Edit dimensions (Weight, Length, Width, and Height)', 'pw-bulk-edit' ); ?>',
		'<?php _e( 'Edit the Shipping Class', 'pw-bulk-edit' ); ?>',
		'<?php _e( 'Use "Is Empty" and "Is Not Empty" filter options', 'pw-bulk-edit' ); ?>',
		'<?php _e( 'Filter by Status, Variation Description, and more', 'pw-bulk-edit' ); ?>',
		'<?php _e( 'Save and load filters', 'pw-bulk-edit' ); ?>',
	];

	jQuery(document).ready(function() {
		pwbeFeatureTicker();
		setInterval(pwbeFeatureTicker, 5000);
	});

	function pwbeFeatureTicker() {
		var feature = pwbeFeatures[Math.floor(Math.random() * pwbeFeatures.length)];
		jQuery('#pwbe-features').text(feature);
	}

</script>