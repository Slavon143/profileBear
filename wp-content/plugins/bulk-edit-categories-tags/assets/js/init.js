(function (Handsontable) {
	function wpTermHierarchyLevel(hotInstance, td, row, column, prop, value, cellProperties) {
		// Optionally include `BaseRenderer` which is responsible for adding/removing CSS classes to/from the table cells.
		Handsontable.renderers.BaseRenderer.apply(this, arguments);

		var postType = jQuery('#post-data').data('post-type');
		var columnSettings = vgse_editor_settings.final_spreadsheet_columns_settings[prop];
		var levels = parseInt(value);
		var html = '';
		if (levels) {
			for (var i = 0; i < levels; i++) {
				html += ' &#8212; ';
			}
		}
		html = '<i class="fa fa-lock vg-cell-blocked"></i> ' + html;

		td.innerHTML = html;
		return td;
	}

	// Register an alias
	Handsontable.renderers.registerRenderer('wp_term_hierarchy_level', wpTermHierarchyLevel);

})(Handsontable);
jQuery(document).ready(function () {

	if (typeof hot === 'undefined') {
		return true;
	}
	/**
	 * Disable post status cells that contain readonly statuses.
	 * ex. scheduled posts
	 */

	if (typeof hot.getSettings().manualRowMove === 'boolean' && hot.getSettings().manualRowMove) {
		jQuery('head').append('<style>#vgse-wrapper .htCore th .rowHeader {    background: url(' + wpse_tt_data.sort_icon_url + ') no-repeat right -1px;    padding: 0 15px 0 0;    cursor: grab;    width: 15px;    display: inline-block;}</style>');
	}

	if (vgse_editor_settings.post_type === vgse_editor_settings.woocommerce_product_post_type_key) {
		hot.updateSettings({
			beforeRowMove: function (rows, target) {
				console.log('rows', rows, 'target', target);
				var nextId = hot.getDataAtRowProp(target, 'ID');
				rows.forEach(function (rowIndex) {
					var movedId = hot.getDataAtRowProp(rowIndex, 'ID');

					jQuery.post(vgse_editor_settings.ajax_url, {action: 'woocommerce_term_ordering', id: movedId, nextid: nextId, thetaxonomy: jQuery('#post-data').data('post-type')}, function (response) {
						console.log('response: ', response);
					});
				});


			}
		});
	}

});


// Merge terms
jQuery(document).ready(function () {
	var $modal = jQuery('.merge-terms-modal');
	if (!$modal.length) {
		return;
	}

	$modal.find('[name="vgse_terms_source"]').on('change', function () {
		var source = jQuery(this).val();
		$modal.find('.final-term').show();
		if (source === 'individual') {
			$modal.find('.individual-term-selector, .individual-term-selector ~  .select2-container').css('display', 'block');
			$modal.find('.use-search-query-container').hide();
		} else if (source === 'search') {
			$modal.find('.individual-term-selector, .individual-term-selector ~ .select2').hide();
			$modal.find('.use-search-query-container').show();


			// Open the search modal
			window.vgseBackToMergeTool = true;
		} else if (source === 'duplicates') {
			$modal.find('.individual-term-selector, .individual-term-selector ~ .select2, .final-term').hide();
			$modal.find('.use-search-query-container').hide();
		} else {
			$modal.find('.individual-term-selector, .individual-term-selector ~ .select2').hide();
			$modal.find('.use-search-query-container').hide();
		}


		var $select2 = $modal.find('select.select2');
		if ($select2.data('select2')) {
			$select2.select2("destroy");
		}
		vgseInitSelect2($select2.filter(function () {
			return jQuery(this).is(':visible');
		}));

		if (source === 'search') {
			jQuery('[name="run_filters"]').first().click();
		}
	});


	// When we are in the formulas modal we revert the changes in the search button
	jQuery(document).on('closed', '[data-remodal-id="modal-filters"]', function () {
		if (typeof window.vgseBackToMergeTool !== 'undefined' && window.vgseBackToMergeTool) {
			window.vgseBackToMergeTool = false;
			jQuery('[data-remodal-target="merge-terms-modal"]').first().click();
		}
	});
	jQuery(document).on('opened', '[data-remodal-id="merge-terms-modal"]', function () {
		// When the terms source is empty (first time opened), we trigger a change to fix a UI issue
		if (!$modal.find('[name="vgse_terms_source"]').val()) {
			$modal.find('[name="vgse_terms_source"]').val('').trigger('change');
		}

		var $select = jQuery('select.individual-term-selector');
		$select.empty();

		var selectedRows = hot.getDataAtCol(0);
		var addedItem = false;
		selectedRows.forEach(function (isSelected, rowIndex) {
			if (isSelected) {
				var termId = hot.getDataAtRowProp(rowIndex, 'slug');
				var termName = hot.getDataAtRowProp(rowIndex, 'name');
				var option_attributes = {
					value: termId,
					class: 'wpecg-appended-from-contextual-menu',
					selected: true
				};

				var $option = $(document.createElement('option')).attr(option_attributes).text(termName);
				$select.append($option);
				addedItem = true;
			}
		});
		$select.find('option[value=""]').remove();
		$select.trigger('change');

		if (addedItem) {
			jQuery('[name="vgse_terms_source"]').val('individual').trigger('change');
		} else {
			jQuery('[name="vgse_terms_source"]').val('').trigger('change');
		}

	});
	jQuery(document).on('closed', '[data-remodal-id="merge-terms-modal"]', function () {
		jQuery('[data-remodal-id="merge-terms-modal"]').find('.response').empty();
		$modal.find('.individual-term-selector').val('').trigger('change');
		$modal.find('[name="use_search_query"]').prop('checked', false).trigger('change');
		$modal.find('#be-merge-terms-nanobar-container').remove();
	});


	// Submit form
	jQuery('body').on('submit', '.merge-terms-modal form', function (e) {
		var $form = jQuery(this);

		if ($form.find('select[name="vgse_terms_source"]').val() === 'search' && !$form.find('input[name="use_search_query"]:checked').length) {
			$form.find('input[name="use_search_query"]').css('border', '1px solid red');
			return false;
		}
		if ($form.find('input[name="use_search_query"]:checked').length && typeof beGetRowsFilters === 'function') {
			$form.find('input[name="filters"]').val(beGetRowsFilters());
		}

		// Get data from the visible tool + fields from outside the tools
		var data = $form.find('input,select,textarea').filter(function () {
			return jQuery(this).is(':visible') || jQuery(this).attr('type') === 'hidden';
		}).serializeArray();

		var useAjaxLoop = $form.find('input[name="use_search_query"]:checked').length || $form.find('select[name="vgse_terms_source"]').val() === 'duplicates';

		if (useAjaxLoop) {
			loading_ajax({estado: false});
			var $progress = $modal.find('.response');
			// Init progress bar
			$progress.before('<div id="be-merge-terms-nanobar-container" />');
			var options = {
				classname: 'be-progress-bar',
				target: document.getElementById('be-merge-terms-nanobar-container')
			};

			var nanobar = new Nanobar(options);
			// We start progress bar with 1% so it doesn't look completely empty
			nanobar.go(4);
			var rowsCount = jQuery('.be-total-rows').text().match(/\d+/g).map(Number);
			var perPage = vgse_editor_settings.save_posts_per_page / 2;
			if (perPage < 3) {
				perPage = 3;
			}
			// Start saving posts, start ajax loop
			beAjaxLoop({
				totalCalls: $form.find('select[name="vgse_terms_source"]').val() === 'duplicates' ? 9999999 : Math.ceil(rowsCount / parseInt(perPage)),
				url: $form.attr('action'),
				method: $form.attr('method'),
				data: data,
				onSuccess: function (res, settings) {
					loading_ajax({estado: false});

					// if the response is empty or has any other format,
					// we create our custom false response
					if (res.success !== true && !res.data) {
						res = {
							data: {
								message: vgse_editor_settings.texts.http_error_try_now
							},
							success: false
						};
					}

					// If error
					if (!res.success) {
						// show error message
						jQuery($progress).append('<p>' + res.data.message + '</p>');

						// Ask the user if he wants to retry the same post
						var goNext = confirm(res.data.message);

						// stop saving if the user chose to not try again
						if (!goNext) {
							jQuery($progress).append(vgse_editor_settings.texts.saving_stop_error);
							$progress.scrollTop($progress[0].scrollHeight);
							return false;
						}
						// reset pointer to try the same batch again
						settings.current = 0;
						$progress.scrollTop($progress[0].scrollHeight);
						return true;
					}


					// Remove rows that were deleted
					if (res.data.deleted.length) {
						vgseRemoveRowFromSheetByID(res.data.deleted);
					}

					var currentNanobar = settings.current / settings.totalCalls * 100;
					if (currentNanobar < 1) {
						currentNanobar = 1;
					}
					nanobar.go(currentNanobar);


					// Display message saying the number of posts saved so far
					var updated = (parseInt(perPage) * settings.current > rowsCount) ? rowsCount : parseInt(perPage) * settings.current;
					var messageTemplate = $form.find('select[name="vgse_terms_source"]').val() === 'duplicates' ? vgse_editor_settings.texts.duplicates_removed_text : vgse_editor_settings.texts.paged_batch_saved;
					var text = messageTemplate.replace('{updated}', updated);
					var text = messageTemplate.replace('{deleted}', res.data.deleted.length);
					var text = text.replace('{total}', rowsCount);
					jQuery($progress).empty().append('<p>' + text + '</p>');


					if (res.data.force_complete) {
						settings.current = settings.totalCalls;
					}

					// is complete, show notification to user, hide loading screen, and display "close" button
					if (settings.current === settings.totalCalls) {
						jQuery($progress).append('<p>' + vgse_editor_settings.texts.everything_saved + '</p>');

						loading_ajax({estado: false});


						nanobar.go(100);
						notification({mensaje: vgse_editor_settings.texts.process_finished});

						$progress.find('.remodal-cancel').removeClass('hidden');
						$form.find('#be-merge-terms-nanobar-container').remove();
					} else {

					}

					// Move scroll to the button to show always the last message in the saving status section
					setTimeout(function () {
						$progress.scrollTop($progress[0].scrollHeight);
					}, 600);

					return true;
				}});
		} else {
			loading_ajax({estado: true});

			jQuery.ajax({
				url: $form.attr('action'),
				method: $form.attr('method'),
				data: data,
			}).done(function (res) {
				console.log(res);
				loading_ajax({estado: false});

				if (res.success) {
					// Remove rows that were deleted
					if (res.data.deleted.length) {
						vgseRemoveRowFromSheetByID(res.data.deleted);
					}

					notification({mensaje: res.data.message});
				} else {
					notification({mensaje: res.data.message, tipo: 'error', tiempo: 60000});
				}
				jQuery('.merge-terms-modal').remodal().close();
			});
		}

		return false;
	});
});


// Merge attributes
jQuery(document).ready(function () {
	var $modal = jQuery('.merge-attributes-modal');
	if (!$modal.length) {
		return;
	}

	$modal.find('[name="vgse_attributes_source"]').on('change', function () {
		var source = jQuery(this).val();
		$modal.find('.final-attribute').show();
		$modal.find('input[name="confirmation"]').prop('checked', false).trigger('change');
		$modal.find('.confirmation-individual').hide();
		$modal.find('.confirmation-duplicates').hide();
		$modal.find('.confirmation-search').hide();
		if (source === 'individual') {
			$modal.find('.individual-attribute-selector, .individual-attribute-selector ~  .select2-container').css('display', 'block');
			$modal.find('.confirmation-individual').show();
		} else if (source === 'search') {
			$modal.find('.individual-attribute-selector, .individual-attribute-selector ~ .select2').hide();
			$modal.find('.confirmation-search').show();

			// Open the search modal
			window.vgseBackToMergeTool = true;
		} else if (source === 'duplicates') {
			$modal.find('.individual-attribute-selector, .individual-attribute-selector ~ .select2, .final-attribute').hide();
			$modal.find('.confirmation-duplicates').show();
		} else {
			$modal.find('.individual-attribute-selector, .individual-attribute-selector ~ .select2').hide();
		}


		var $select2 = $modal.find('select.select2');
		if ($select2.data('select2')) {
			$select2.select2("destroy");
		}
		vgseInitSelect2($select2.filter(function () {
			return jQuery(this).is(':visible');
		}));

		if (source === 'search' ) {
			jQuery('[name="run_filters"]').first().click();
		}
	});


	// When we are in the filters modal we revert the changes in the search button
	jQuery(document).on('closed', '[data-remodal-id="modal-filters"]', function () {
		if (typeof window.vgseBackToMergeTool !== 'undefined' && window.vgseBackToMergeTool) {
			window.vgseBackToMergeTool = false;
			jQuery('[data-remodal-target="merge-attributes-modal"]').first().click();
			$modal.find('[name="vgse_attributes_source"]').val('search');
		}
	});
	jQuery(document).on('opened', '[data-remodal-id="merge-attributes-modal"]', function () {
		// When the attributes source is empty (first time opened), we trigger a change to fix a UI issue
		if (!$modal.find('[name="vgse_attributes_source"]').val()) {
			$modal.find('[name="vgse_attributes_source"]').val('').trigger('change');
		}

		var $select = jQuery('select.individual-attribute-selector');
		$select.empty();

		var selectedRows = hot.getDataAtCol(0);
		var addedItem = false;
		selectedRows.forEach(function (isSelected, rowIndex) {
			if (isSelected) {
				var attributeId = hot.getDataAtRowProp(rowIndex, 'ID');
				var attributeName = hot.getDataAtRowProp(rowIndex, 'attribute_label');
				var option_attributes = {
					value: attributeId,
					class: 'wpecg-appended-from-contextual-menu',
					selected: true
				};

				var $option = $(document.createElement('option')).attr(option_attributes).text(attributeName);
				$select.append($option);
				addedItem = true;
			}
		});
		$select.find('option[value=""]').remove();
		$select.trigger('change');

		if (addedItem) {
			jQuery('[name="vgse_attributes_source"]').val('individual').trigger('change');
		}
	});
	jQuery(document).on('closed', '[data-remodal-id="merge-attributes-modal"]', function () {
		jQuery('[data-remodal-id="merge-attributes-modal"]').find('.response').empty();
		$modal.find('.individual-attribute-selector').val('').trigger('change');
		$modal.find('[name="confirmation"]').prop('checked', false).trigger('change');
		$modal.find('#be-merge-attributes-nanobar-container').remove();
	});


	// Submit form
	jQuery('body').on('submit', '.merge-attributes-modal form', function (e) {
		var $form = jQuery(this);

		if (!$form.find('input[name="confirmation"]:checked').length) {
			$form.find('input[name="confirmation"]').css('border', '1px solid red');
			return false;
		}
		if ($form.find('select[name="vgse_attributes_source"]').val() === 'search' &&  typeof beGetRowsFilters === 'function') {
			$form.find('input[name="filters"]').val(beGetRowsFilters());
		}

		// Get data from the visible tool + fields from outside the tools
		var data = $form.find('input,select,textarea').filter(function () {
			return jQuery(this).is(':visible') || jQuery(this).attr('type') === 'hidden';
		}).serializeArray();

		var useAjaxLoop = $form.find('select[name="vgse_attributes_source"]').val() !== 'individual';

		if (useAjaxLoop) {
			loading_ajax({estado: false});
			var $progress = $modal.find('.response');
			// Init progress bar
			$progress.before('<div id="be-merge-attributes-nanobar-container" />');
			var options = {
				classname: 'be-progress-bar',
				target: document.getElementById('be-merge-attributes-nanobar-container')
			};

			var nanobar = new Nanobar(options);
			// We start progress bar with 1% so it doesn't look completely empty
			nanobar.go(4);
			var rowsCount = jQuery('.be-total-rows').text().match(/\d+/g).map(Number);
			var perPage = vgse_editor_settings.save_posts_per_page / 2;
			if (perPage < 3) {
				perPage = 3;
			}
			var totalMerged = 0;
			// Start saving posts, start ajax loop
			beAjaxLoop({
				totalCalls: $form.find('select[name="vgse_attributes_source"]').val() === 'duplicates' ? 9999999 : Math.ceil(rowsCount / parseInt(perPage)),
				url: $form.attr('action'),
				method: $form.attr('method'),
				data: data,
				onSuccess: function (res, settings) {
					loading_ajax({estado: false});

					// if the response is empty or has any other format,
					// we create our custom false response
					if (res.success !== true && !res.data) {
						res = {
							data: {
								message: vgse_editor_settings.texts.http_error_try_now
							},
							success: false
						};
					}

					// If error
					if (!res.success) {
						// show error message
						jQuery($progress).append('<p>' + res.data.message + '</p>');

						// Ask the user if he wants to retry the same post
						var goNext = confirm(res.data.message);

						// stop saving if the user chose to not try again
						if (!goNext) {
							jQuery($progress).append(vgse_editor_settings.texts.saving_stop_error);
							$progress.scrollTop($progress[0].scrollHeight);
							return false;
						}
						// reset pointer to try the same batch again
						settings.current = 0;
						$progress.scrollTop($progress[0].scrollHeight);
						return true;
					}


					// Remove rows that were deleted
					if (res.data.deleted.length) {
						vgseRemoveRowFromSheetByID(res.data.deleted);
					}

					var currentNanobar = settings.current / settings.totalCalls * 100;
					if (currentNanobar < 1) {
						currentNanobar = 1;
					}
					nanobar.go(currentNanobar);


					totalMerged += res.data.deleted.length;
					var messageTemplate = vgse_editor_settings.texts.merged_attributes_message;
					var text = messageTemplate.replace('{updated}', totalMerged);
					jQuery($progress).empty().append('<p>' + text + '</p>');


					if (res.data.force_complete) {
						settings.current = settings.totalCalls;
					}

					// is complete, show notification to user, hide loading screen, and display "close" button
					if (settings.current === settings.totalCalls) {
						jQuery($progress).append('<p>' + vgse_editor_settings.texts.process_finished + '</p>');

						loading_ajax({estado: false});


						nanobar.go(100);
						notification({mensaje: vgse_editor_settings.texts.process_finished});

						$progress.find('.remodal-cancel').removeClass('hidden');
						$form.find('#be-merge-attributes-nanobar-container').remove();
					} else {

					}

					// Move scroll to the button to show always the last message in the saving status section
					setTimeout(function () {
						$progress.scrollTop($progress[0].scrollHeight);
					}, 600);

					return true;
				}});
		} else {
			loading_ajax({estado: true});

			jQuery.ajax({
				url: $form.attr('action'),
				method: $form.attr('method'),
				data: data,
			}).done(function (res) {
				console.log(res);
				loading_ajax({estado: false});

				if (res.success) {
					// Remove rows that were deleted
					if (res.data.deleted.length) {
						vgseRemoveRowFromSheetByID(res.data.deleted);
					}

					notification({mensaje: res.data.message});
				} else {
					notification({mensaje: res.data.message, tipo: 'error', tiempo: 60000});
				}
				jQuery('.merge-attributes-modal').remodal().close();
			});
		}

		return false;
	});
});