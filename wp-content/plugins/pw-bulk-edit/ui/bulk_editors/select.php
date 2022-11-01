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
<span id="pwbe-dialog-content-select" class="pwbe-dialog-content" data-function="pwbeBulkEditorSelectHandler">
	<span class="pwbe-bulkedit-field-name"></span>:
	<p>
		&nbsp;&nbsp;&nbsp;&nbsp;<select class="pwbe-bulkedit-editor-select-field"></select>
	</p>
</span>
<script>

	function pwbeBulkEditorSelectHandler(action, oldValue) {
		var dialog = jQuery('#pwbe-dialog-content-select');
		var fieldName = dialog.attr('data-field-name');
		var dataField = dialog.attr('data-field');
		var select = dialog.find('.pwbe-bulkedit-editor-select-field');

		switch (action) {
			case 'init':
				dialog.find('.pwbe-bulkedit-field-name').text(fieldName);

				var options = jQuery('.pwbe-dropdown-template-' + dataField + ' option').not('.pwbe-dropdown-visibility-variation').clone();
				options.appendTo(select);

				select.focus();
			break;

			case 'apply':
				return select.val();
			break;

			case 'reset':
				select.empty();
			break;
		}
	}

</script>