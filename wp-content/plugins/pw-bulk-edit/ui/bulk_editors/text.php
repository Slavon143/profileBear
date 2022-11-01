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
<span id="pwbe-dialog-content-text" class="pwbe-dialog-content" data-function="pwbeBulkEditorTextHandler">
	<fieldset id="pwbe-bulkedit-text-mode">
		<input type="radio" value="replace" name="pwbe-bulkedit-text-mode" id="pwbe-bulkedit-text-mode-replace" /> <label for="pwbe-bulkedit-text-mode-replace"><?php _e( 'Search and replace', 'pw-bulk-edit' ); ?></label><br />
		<input type="radio" value="prepend" name="pwbe-bulkedit-text-mode" id="pwbe-bulkedit-text-mode-prepend" /> <label for="pwbe-bulkedit-text-mode-prepend"><?php _e( 'Add to the beginning', 'pw-bulk-edit' ); ?></label><br />
		<input type="radio" value="append" name="pwbe-bulkedit-text-mode" id="pwbe-bulkedit-text-mode-append" /> <label for="pwbe-bulkedit-text-mode-append"><?php _e( 'Add to the end', 'pw-bulk-edit' ); ?></label><br />
		<input type="radio" value="uppercase" name="pwbe-bulkedit-text-mode" id="pwbe-bulkedit-text-mode-uppercase" /> <label for="pwbe-bulkedit-text-mode-uppercase"><?php _e( 'ALL UPPERCASE', 'pw-bulk-edit' ); ?></label><br />
		<input type="radio" value="lowercase" name="pwbe-bulkedit-text-mode" id="pwbe-bulkedit-text-mode-lowercase" /> <label for="pwbe-bulkedit-text-mode-lowercase"><?php _e( 'all lowercase', 'pw-bulk-edit' ); ?></label><br />
		<input type="radio" value="propercase" name="pwbe-bulkedit-text-mode" id="pwbe-bulkedit-text-mode-propercase" /> <label for="pwbe-bulkedit-text-mode-propercase"><?php _e( 'Proper Case Words', 'pw-bulk-edit' ); ?></label>
	</fieldset>
	<div id="pwbe-bulkedit-details-text-container">
		<div class="form-field">
			<div class="pwbe-bulkedit-details pwbe-bulkedit-details-replace">
				<label for="pwbe-bulkedit-search-text"><?php printf( __( 'Search for this text anywhere in %s:', 'pw-bulk-edit' ), '<span class="pwbe-bulkedit-field-name"></span>' ); ?></label><br />
				<input type="text" id="pwbe-bulkedit-search-text" placeholder="Leave blank to replace the entire value." /><br />

				<label for="pwbe-bulkedit-replace-text"><?php _e( 'Replace it with this text:', 'pw-bulk-edit' ); ?></label><br />
				<input type="text" id="pwbe-bulkedit-replace-text" /><br />
				<input type="checkbox" id="pwbe-bulkedit-replace-case-sensitive" /><label for="pwbe-bulkedit-replace-case-sensitive"> <?php _e( 'Case Sensitive', 'pw-bulk-edit' ); ?></label>
			</div>
			<div class="pwbe-bulkedit-details pwbe-bulkedit-details-prepend">
				<label for="pwbe-bulkedit-prepend-text"><?php printf( __( 'Prepend this text to the beginning of %s:', 'pw-bulk-edit' ), '<span class="pwbe-bulkedit-field-name"></span>' ); ?></label><br />
				<input type="text" id="pwbe-bulkedit-prepend-text" />
			</div>
			<div class="pwbe-bulkedit-details pwbe-bulkedit-details-append">
				<label for="pwbe-bulkedit-append-text"><?php printf( __( 'Append this text to the end of %s:', 'pw-bulk-edit' ), '<span class="pwbe-bulkedit-field-name"></span>' ); ?></label><br />
				<input type="text" id="pwbe-bulkedit-append-text" />
			</div>
			<div class="pwbe-bulkedit-details pwbe-bulkedit-details-uppercase">
				<?php _e( 'All words will be changed to uppercase.', 'pw-bulk-edit' ); ?>
				<p><strong><?php _e( 'Mary had a little LAMB &rarr; MARY HAD A LITTLE LAMB', 'pw-bulk-edit' ); ?></strong></p>
			</div>
			<div class="pwbe-bulkedit-details pwbe-bulkedit-details-lowercase">
				<?php _e( 'All words will be changed to lowercase.', 'pw-bulk-edit' ); ?>
				<p><strong><?php _e( 'Mary had a little LAMB &rarr; mary had a little lamb', 'pw-bulk-edit' ); ?></strong></p>
			</div>
			<div class="pwbe-bulkedit-details pwbe-bulkedit-details-propercase">
				<?php _e( 'The first letter of every word will be capitalized.', 'pw-bulk-edit' ); ?>
				<p><strong><?php _e( 'Mary had a little LAMB &rarr; Mary Had A Little Lamb', 'pw-bulk-edit' ); ?></strong></p>
			</div>
		</div>
	</div>
</span>
<style>
	#pwbe-dialog-content-text {
		white-space: nowrap;
	}

	#pwbe-bulkedit-text-mode, #pwbe-bulkedit-details-text-container {
		display: inline-block;
		vertical-align: top;
	}

	#pwbe-bulkedit-details-text-container {
		padding-left: 30px;
		min-width: 400px;
	}

	#pwbe-bulkedit-replace-text {
		margin-bottom: 20px;
	}

	.pwbe-bulkedit-details {
		display: none;
	}
</style>
<script>

	jQuery(function() {
		jQuery('#pwbe-dialog-content-text').find('input[type=radio][name=pwbe-bulkedit-text-mode]').on('change', function() {
			var dialog = jQuery('#pwbe-dialog-content-text');
			var details = dialog.find('.pwbe-bulkedit-details-' + jQuery(this).val()).first();

			dialog.find('.pwbe-bulkedit-details').hide();
			details.show().find('input:first').focus();
		});

		jQuery('#pwbe-dialog-content-text').find('#pwbe-bulkedit-search-text, #pwbe-bulkedit-replace-text, #pwbe-bulkedit-prepend-text, #pwbe-bulkedit-append-text').on('keydown', function(e) {
			if (e.keyCode == 13) {
				jQuery('#pwbe-bulkedit-dialog-button-apply').trigger('click');
				e.preventDefault();
				return false;
			}
		});
	});

	function pwbeBulkEditorTextHandler(action, oldValue) {
		var dialog = jQuery('#pwbe-dialog-content-text');
		var fieldName = dialog.attr('data-field-name');
		var prepend = dialog.find('#pwbe-bulkedit-prepend-text');
		var append = dialog.find('#pwbe-bulkedit-append-text');
		var search = dialog.find('#pwbe-bulkedit-search-text');
		var replace = dialog.find('#pwbe-bulkedit-replace-text');
		var caseSensitive = dialog.find('#pwbe-bulkedit-replace-case-sensitive');
		var mode = dialog.find('input[name=pwbe-bulkedit-text-mode]:checked').first();

		switch (action) {
			case 'init':
				dialog.find('.pwbe-bulkedit-field-name').text(fieldName);
			break;

			case 'apply':
				if (!mode.val()) {
					return oldValue;
				}

				var newValue = oldValue;
				if (!oldValue) { oldValue = ''; }

				switch (mode.val()) {
					case 'replace':
						if (search.val()) {
							if (caseSensitive.prop('checked')) {
								newValue = oldValue.replace(search.val(), replace.val());
							} else {
								newValue = oldValue.replace(new RegExp('(' + pwbePregQuote(search.val()) + ')', 'gi'), replace.val());
							}
						} else {
							newValue = replace.val();
						}
					break;

					case 'prepend':
						newValue = prepend.val() + oldValue;
					break;

					case 'append':
						newValue = oldValue + append.val();
					break;

					case 'uppercase':
						newValue = oldValue.toUpperCase();
					break;

					case 'lowercase':
						newValue = oldValue.toLowerCase();
					break;

					case 'propercase':
						newValue = oldValue.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
					break;
				}

				return newValue;
			break;

			case 'reset':
				mode.prop('checked', false);
				prepend.val('');
				append.val('');
				search.val('');
				replace.val('');
				caseSensitive.prop('checked', false);
				dialog.find('.pwbe-bulkedit-details').hide();
			break;
		}
	}

</script>