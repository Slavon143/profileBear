/* global ajaxurl, uploader */

var jQuery,
	mla_add_new_bulk_edit_vars,
	mla = {
		// Properties (for mla-set-parent-scripts, too)
		// mla.settings.uploadTitle
		// mla.settings.toggleClose
		// mla.settings.toggleOpen
		// mla.settings.areaOnTop
		// mla.settings.comma for flat taxonomy suggest
		// mla.settings.ajaxFailError for setParent, doExport
		// mla.settings.ajaxDoneError for setParent, doExport
		// mla.settings.setParentAction for setParent
		// mla.settings.exportPresetsAction for doExport
		// mla.settings.useDashicons for setParent, doExport
		// mla.settings.useSpinnerClass for setParent, doExport
		settings: {},

		// Utility functions
		utility: {
			getId : function( o ) {
				var id = jQuery( o ).closest( 'tr' ).attr( 'id' ),
					parts = id.split( '-' );
				return parts[ parts.length - 1 ];
			},

			attachSearch : function( rowId ) {
				var editDiv = jQuery( rowId );
				
				jQuery( '.categorydiv', editDiv ).each( function(){
					var this_id = jQuery(this).attr('id'), taxonomy, taxonomyPrefix;
	
					taxonomy = this_id.split('-');
					taxonomy.shift(); // taxonomy-
					taxonomy = taxonomy.join('-');
					taxonomyPrefix = '#' + taxonomy;
					
					jQuery.extend( jQuery.expr.pseudos || jQuery.expr[":"], {
						"matchTerms": function( elem, i, match, array ) {
							return ( elem.textContent || elem.innerText || "" ).toLowerCase().indexOf( ( match[3] || "" ).toLowerCase() ) >= 0;
						}
					});
	
					jQuery( taxonomyPrefix + '-searcher', editDiv ).addClass( 'wp-hidden-children' );
					jQuery( taxonomyPrefix + 'checklist li', editDiv ).show();

					jQuery( '#search-' + taxonomy, editDiv ).off();

					jQuery( '#search-' + taxonomy, editDiv ).on( 'keydown', function( event ){

						if( 13 === event.keyCode ) {
							event.preventDefault();
							jQuery( '#search-'  + taxonomy, editDiv ).val( '' );
							jQuery( taxonomyPrefix + '-searcher', editDiv ).addClass( 'wp-hidden-children' );
	
							jQuery( taxonomyPrefix + 'checklist li', editDiv ).show();
							return false;
						}
	
					} );
	
					jQuery( '#search-' + taxonomy, editDiv ).on( 'keypress', function( event ){
	
						if( 13 === event.keyCode ) {
							event.preventDefault();
							jQuery( '#search-'  + taxonomy, editDiv ).val( '' );
							jQuery( taxonomyPrefix + '-searcher', editDiv ).addClass( 'wp-hidden-children' );
	
							jQuery( taxonomyPrefix + 'checklist li', editDiv ).show();
							return;
						}
	
					} );
	
					jQuery( '#search-' + taxonomy, editDiv ).on( 'keyup', function( event ){
						var searchValue, termList, matchingTerms;
	
						if( 13 === event.keyCode ) {
							event.preventDefault();
							jQuery( taxonomyPrefix + '-search-toggle', editDiv ).trigger('focus');
							return;
						}
	
						searchValue = jQuery( '#search-' + taxonomy, editDiv ).val();
						termList = jQuery( taxonomyPrefix + 'checklist li', editDiv );
	
						if ( 0 < searchValue.length ) {
							termList.hide();
						} else {
							termList.show();
						}
	
						matchingTerms = jQuery( taxonomyPrefix + "checklist label:matchTerms('" + searchValue + "')", editDiv );
						matchingTerms.closest( 'li' ).find( 'li' ).addBack().show();
						matchingTerms.parents( taxonomyPrefix + 'checklist li', editDiv ).show();
					} );
	
					jQuery( taxonomyPrefix + '-search-toggle', editDiv ).off();

					jQuery( taxonomyPrefix + '-search-toggle', editDiv ).on( 'click', function() {
						jQuery( taxonomyPrefix + '-adder ', editDiv ).addClass( 'wp-hidden-children' );
						jQuery( taxonomyPrefix + '-searcher', editDiv ).toggleClass( 'wp-hidden-children' );
						jQuery( taxonomyPrefix + 'checklist li', editDiv ).show();
	
						if ( false === jQuery( taxonomyPrefix + '-searcher', editDiv ).hasClass( 'wp-hidden-children' ) ) {
							jQuery( '#search-'  + taxonomy, editDiv ).val( '' ).removeClass( 'form-input-tip' );
							jQuery( '#search-' + taxonomy, editDiv ).trigger('focus');
						}
	
						return false;
					});
				}); // .categorydiv.each, 
			}
		},

		// Components
		addNewBulkEdit: null,
		setParent: null
	};

( function( $ ) {
	// Localized settings and strings
	mla.settings = typeof mla_add_new_bulk_edit_vars === 'undefined' ? {} : mla_add_new_bulk_edit_vars;
	mla_add_new_bulk_edit_vars = void 0; // delete won't work on Globals

	if ( typeof mla.settings.areaOnTop === 'undefined' ) {
		mla.settings.areaOnTop = false;
	};

	mla.addNewBulkEdit = {
		init: function() {
			var presetContent, blankContent, toggleButton, resetButton, importButton, exportButton,
				bypass = $( '.upload-flash-bypass' ), title = $( '#wpbody .wrap' ).children ( 'h1, h2' ),
				uploadContent, uploadDiv = $( '#mla-add-new-bulk-edit-div' ).hide(); // Start with area closed up

			if ( typeof mla.addTerm !== 'undefined' ) {
				mla.addTerm.init( '#mla-add-new-bulk-edit-div' );
			}
			mla.utility.attachSearch( '#mla-add-new-bulk-edit-div' );
				
			$( '#bulk-edit-set-parent', uploadDiv ).on( 'click', function(){
				return mla.addNewBulkEdit.parentOpen();
			});

			// Move the import/export preset content out of the form so it won't pollute the serialize() results
			presetContent = $('#mla-preset-add-new-bulk-edit-div').detach();
			$( '#file-form' ).after( presetContent );

			// Move the blank content out of the form so it won't pollute the serialize() results
			blankContent = $('#mla-blank-add-new-bulk-edit-div').detach();
			$( '#file-form' ).after( blankContent );

			// Move the Open/Close Bulk Edit area toggleButton to save space on the page
			toggleButton = $( '#bulk-edit-toggle', uploadDiv ).detach();
			resetButton = $( '#bulk-edit-reset', uploadDiv ).detach();
			importButton = $( '#bulk-edit-import', uploadDiv ).detach();
			exportButton = $( '#bulk-edit-export', uploadDiv ).detach();

			if ( mla.settings.areaOnTop ) {
				toggleButton.appendTo( title );
				resetButton.appendTo( title );
				importButton.appendTo( title );
				exportButton.appendTo( title );
				uploadContent = uploadDiv.detach();
				$( '#media-upload-notice' ).before( uploadContent );
			} else {
				toggleButton.appendTo( bypass );
				resetButton.appendTo( bypass );
				importButton.appendTo( bypass );
				exportButton.appendTo( bypass );
			};

			// Hook the "browser uploader" link to close the Bulk Edit area when it is in use
			toggleButton.siblings( 'a' ).on( 'click', function(){
				toggleButton.attr( 'title', mla.settings.toggleOpen );
				toggleButton.attr( 'value', mla.settings.toggleOpen );
				resetButton.hide();
				importButton.hide();
				exportButton.hide();
				uploadDiv.hide();
			});

			toggleButton.on( 'click', function(){
				return mla.addNewBulkEdit.formToggle();
			});

			resetButton.on( 'click', function(){
				return mla.addNewBulkEdit.doReset();
			});

			importButton.on( 'click', function(){
				return mla.addNewBulkEdit.doImport();
			});

			exportButton.on( 'click', function(){
				return mla.addNewBulkEdit.doExport();
			});

			if ( mla.settings.areaOpen ) {
				mla.addNewBulkEdit.formToggle();
			};

			//auto-complete/suggested matches for flat taxonomies
			$( 'textarea.mla_tags', uploadDiv ).each(function(){
				var taxname = $(this).attr('name').replace(']', '').replace('tax_input[', '');

				$(this).suggest( ajaxurl + '?action=ajax-tag-search&tax=' + taxname, { delay: 500, minchars: 2, multiple: true, multipleSep: mla.settings.comma + ' ' } );
			});

			uploader.bind( 'BeforeUpload', function( up, file ) {
				var formString = $( '#file-form' ).serialize();

				up.settings.multipart_params.mlaAddNewBulkEditFormString = formString;
			});
		},

		doReset : function(){
			var bulkDiv = $('#mla-add-new-bulk-edit-div'),
				blankDiv = $('#mla-blank-add-new-bulk-edit-div'),
				blankCategories = $('.inline-edit-categories', blankDiv ).html(),
				blankTags = $('.inline-edit-tags', blankDiv ).html(),
				blankFields = $('.inline-edit-fields', blankDiv ).html();

			$('.inline-edit-categories', bulkDiv ).html( blankCategories ),
			$('.inline-edit-tags', bulkDiv ).html( blankTags ),
			$('.inline-edit-fields', bulkDiv ).html( blankFields );

			if ( typeof mla.addTerm !== 'undefined' ) {
				mla.addTerm.init( '#mla-add-new-bulk-edit-div' );
			}
			mla.utility.attachSearch( '#mla-add-new-bulk-edit-div' );
				
			$('#bulk-edit-set-parent', bulkDiv).on( 'click', function(){
				return mla.addNewBulkEdit.parentOpen();
			});

			return false;
		},

		doImport : function(){
			var bulkDiv = $('#mla-add-new-bulk-edit-div'),
				presetDiv = $('#mla-preset-add-new-bulk-edit-div'),
				presetCategories = $('.inline-edit-categories', presetDiv ).html(),
				presetTags = $('.inline-edit-tags', presetDiv ).html(),
				presetFields = $('.inline-edit-fields', presetDiv ).html();

			$('.inline-edit-categories', bulkDiv ).html( presetCategories ),
			$('.inline-edit-tags', bulkDiv ).html( presetTags ),
			$('.inline-edit-fields', bulkDiv ).html( presetFields );

			if ( typeof mla.addTerm !== 'undefined' ) {
				mla.addTerm.init( '#mla-add-new-bulk-edit-div' );
			}
			mla.utility.attachSearch( '#mla-add-new-bulk-edit-div' );
				
			$('#bulk-edit-set-parent', bulkDiv).on( 'click', function(){
				return mla.addNewBulkEdit.parentOpen();
			});

			return false;
		},

		doExport : function(){
			var post = {
					action: mla.settings.exportPresetsAction,
					mla_preset_values: $( '#file-form' ).serialize(),
					mla_preset_option: mla.settings.exportPresetsOption,
					mla_admin_nonce: $('#mla-export-presets-ajax-nonce').val()
				},
				spinner = $( '#mla-add-new-bulk-edit-div .spinner' );

			if ( mla.settings.useSpinnerClass ) {
				spinner.addClass("is-active");
			} else {
				spinner.show();
			}

			$.ajax( ajaxurl, {
				type: 'POST',
				data: post,
				dataType: 'json'
			}).always( function( response ) {
				if ( mla.settings.useSpinnerClass ) {
					spinner.removeClass("is-active");
				} else {
					spinner.hide();
				}
			}).done( function( response ) {
				var responseData = 'no response.data';

				if ( ! response.success ) {
					if ( response.data ) {
						responseData = response.data;
					}

					$( '#media-upload-error' ).html( '<strong>' + mla.settings.ajaxDoneError + ' (' + responseData + ')</strong>' );
				} else {
					// replace the old presets with the current values
					$( '#mla-preset-add-new-bulk-edit-div' ).html( response.data );
				}
			}).fail( function( jqXHR, status ) {
				if ( 200 == jqXHR.status ) {
					$( '#media-upload-error' ).text( '(' + status + ') ' + jqXHR.responseText );
				} else {
					$( '#media-upload-error' ).text( mla.settings.ajaxFailError + ' (' + status + '), jqXHR( ' + jqXHR.status + ', ' + jqXHR.statusText + ', ' + jqXHR.responseText + ')' );
				}
			});

			return false;
		},

		formToggle : function() {
			var toggleButton = $( '#bulk-edit-toggle' ), resetButton = $( '#bulk-edit-reset' ), 
			    importButton = $( '#bulk-edit-import' ), exportButton = $( '#bulk-edit-export' ), 
				area = $( '#mla-add-new-bulk-edit-div' );

			// Expand/collapse the Bulk Edit area
			if ( 'none' === area.css( 'display' ) ) {
				toggleButton.attr( 'title', mla.settings.toggleClose );
				toggleButton.attr( 'value', mla.settings.toggleClose );
				resetButton.show();
				importButton.show();
				exportButton.show();
			} else {
				toggleButton.attr( 'title', mla.settings.toggleOpen );
				toggleButton.attr( 'value', mla.settings.toggleOpen );
				resetButton.hide();
				importButton.hide();
				exportButton.hide();
			}

			area.slideToggle( 'slow' );
		},

		parentOpen : function() {
			var parentId, postId, postTitle;

			postId = -1;
			postTitle = mla.settings.uploadTitle;
			parentId = $( '#mla-add-new-bulk-edit-div :input[name="post_parent"]' ).val() || -1;
			mla.setParent.open( parentId, postId, postTitle );
			/*
			 * Grab the "Update" button
			 */
			$( '#mla-set-parent-submit' ).on( 'click', function( event ){
				event.preventDefault();
				mla.addNewBulkEdit.parentSave();
				return false;
			});
		},

		parentSave : function() {
			var foundRow = $( '#mla-set-parent-response-div input:checked' ).closest( 'tr' ), parentId, newParent;

			if ( foundRow.length ) {
				parentId = $( ':radio', foundRow ).val() || '';
				newParent = $('#mla-add-new-bulk-edit-div :input[name="post_parent"]').clone( true ).val( parentId );
				$('#mla-add-new-bulk-edit-div :input[name="post_parent"]').replaceWith( newParent );
			}

			mla.setParent.close();
			$('#mla-set-parent-submit' ).off( 'click' );
		},

	}; // mla.addNewBulkEdit

	$( document ).ready( function() {
		mla.addNewBulkEdit.init();
	});
})( jQuery );
