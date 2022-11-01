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
<span id="pwbe-dialog-content-checkbox" class="pwbe-dialog-content" data-function="pwbeBulkEditorCheckboxHandler">
	<span class="pwbe-bulkedit-field-name"></span>?
	<p style="margin-left: 24px;">
		<label><input type="radio" name="pwbe-bulkedit-editor-checkbox-field" class="pwbe-bulkedit-editor-checkbox-field" /> <?php _e( 'Yes', 'pw-bulk-edit' ); ?></label>
		<br />
		<label><input type="radio" name="pwbe-bulkedit-editor-checkbox-field" class="pwbe-bulkedit-editor-checkbox-field" /> <?php _e( 'No', 'pw-bulk-edit' ); ?></label>
	</p>
</span>
<script>

	function pwbeBulkEditorCheckboxHandler(action, oldValue) {
		var dialog = jQuery('#pwbe-dialog-content-checkbox');
		var fieldName = dialog.attr('data-field-name');
		var checkbox = dialog.find('.pwbe-bulkedit-editor-checkbox-field');

		switch (action) {
			case 'init':
				dialog.find('.pwbe-bulkedit-field-name').text(fieldName);
			break;

			case 'apply':
				if (checkbox.prop('checked')) {
					return 'yes';
				} else {
					return 'no';
				}
			break;

			case 'reset':
				checkbox.prop('checked', false);
			break;
		}
	}

</script>