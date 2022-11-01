/* global ajaxurl */

var jQuery,
	mla_inline_edit_vars,
	mla = {
		// Properties (for mla-set-parent-scripts, too)
		// mla.settings.noTitle
		// mla.settings.ntdelTitle
		// mla.settings.quickTagsInit
		// mla.settings.bulkChunkSize
		// mla.settings.ajax_action
		// mla.settings.ajax_nonce
		// mla.settings.bulkWaiting
		// mla.settings.bulkComplete
		// mla.settings.bulkSuccess
		// mla.settings.bulkFailure
		// mla.settings.error
		// mla.settings.bulkCanceled
		// mla.settings.fields
		// mla.settings.bulkTitle
		// mla.settings.comma for flat taxonomy suggest
		// mla.settings.ajaxFailError for setParent
		// mla.settings.ajaxDoneError for setParent
		// mla.settings.useDashicons for setParent
		// mla.settings.useSpinnerClass for setParent
		settings: {},
		bulkEdit: {
			inProcess: false,
			doCancel: false
		},

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
		setParent: null,
		inlineEditAttachment: null
	};

( function( $ ) {
	// Localized settings and strings
	mla.settings = typeof mla_inline_edit_vars === 'undefined' ? {} : mla_inline_edit_vars;
	mla_inline_edit_vars = void 0; // delete won't work on Globals

	// The inlineEditAttachment functions are adapted from wp-admin/js/inline-edit-post.js
	mla.inlineEditAttachment = {
		init : function(){
			var t = this, qeRow = $( '#inline-edit' ), bulkRow = $( '#bulk-edit' ), progressRow = $( '#bulk-progress' );

			if ( mla.settings.deleteAcpBulkEdit ) {
				setTimeout( function() {
					var elements = document.querySelectorAll( 'body.ac-mla-media-assistant .acp-be-editrow, body.ac-mla-media-assistant .acp-be-noticerow' );
				
					for ( i = 0; i < elements.length; ++i ) {
						elements[ i ].remove();
					}
				}, 1000 );
			}
			
			t.type = 'attachment';
			t.what = '#attachment-';

			// prepare the edit rows
			qeRow.on( 'keyup', function(e){
				if (e.which == 27)
					return mla.inlineEditAttachment.revert();
			});
			bulkRow.on( 'keyup', function(e){
				if (e.which == 27)
					return mla.inlineEditAttachment.revert();
			});
			progressRow.on( 'keyup', function(e){
				if (e.which == 27)
					return mla.inlineEditAttachment.revert();
			});

			$('#inline-edit-post-set-parent', qeRow).on( 'click', function(){
				return mla.inlineEditAttachment.inlineParentOpen(this);
			});
			$('a.cancel', qeRow).on( 'click', function(){
				return mla.inlineEditAttachment.revert();
			});
			$('a.save', qeRow).on( 'click', function(){
				return mla.inlineEditAttachment.quickSave(this);
			});
			$('td', qeRow).on( 'keydown', function(e){
				if ( e.which == 13 )
					return mla.inlineEditAttachment.quickSave(this);
			});

			if ( typeof mla.addTerm !== 'undefined' ) {
				mla.addTerm.init( '#bulk-edit-fields-div' );
			}
			mla.utility.attachSearch( '#bulk-edit-fields-div' );

			$('#bulk-edit-set-parent', bulkRow).on( 'click', function(){
				return mla.inlineEditAttachment.bulkParentOpen();
			});
			$('a.cancel', bulkRow).on( 'click', function(){
				return mla.inlineEditAttachment.revert();
			});
			$('a.reset', bulkRow).on( 'click', function(){
				return mla.inlineEditAttachment.doReset();
			});
			$('a.import', bulkRow).on( 'click', function(){
				return mla.inlineEditAttachment.doImport();
			});
			$('a.export', bulkRow).on( 'click', function(){
				return mla.inlineEditAttachment.doExport();
			});
			$('input[type="submit"]', bulkRow).on( 'click', function(e){
				e.preventDefault();
				return mla.inlineEditAttachment.bulkSave(e);
			});

			$('a.cancel', progressRow).on( 'click', function(){
				if ( mla.bulkEdit.inProcess ) {
					mla.bulkEdit.doCancel = true;
					return false;
				} else {
					return mla.inlineEditAttachment.revert();
				}
			});

			// Clicking "Refresh" submits the form, refreshing the page
			$('#bulk_refresh', progressRow).on( 'click', function(){
				$( '#bulk-progress a' ).prop( 'disabled', true );
				$( '#bulk-progress' ).css( 'opacity', '0.5' );
			});

			// add event to the Quick Edit links
			$( '#the-list' ).on( 'click', 'a.editinline', function(){
				mla.inlineEditAttachment.quickEdit(this);
				return false;
			});

			$('select[name="_status"] option[value="future"]', bulkRow).remove();

			$('#doaction, #doaction2').on( 'click', function(e){
				var n = $(this).attr('id').substr(2);

				if ( $('select[name="'+n+'"]').val() == 'edit' ) {
					e.preventDefault();
					t.bulkEdit();
				} else if ( $('form#posts-filter tr.inline-editor').length > 0 ) {
					t.revert();
				}
			});

			// Filter button (dates, categories) in top nav bar
			$('#post-query-submit').on( 'mousedown', function(){
				t.revert();
				$('select[name^="action"]').val('-1');
			});
		},

		bulkEdit : function(){
			var te = '', c = true;
			this.revert();

			$('#bulk-edit td').attr('colspan', $( 'th:visible, td:visible', '.widefat:first thead' ).length);
			/*
			 * Insert the editor at the top of the table with an empty row above
			 * in WP 4.2+ to maintain zebra striping.
			 */
			if ( mla.settings.useSpinnerClass ) {
				$('table.wp-list-table tbody').prepend( $('#bulk-edit') ).prepend('<tr class="hidden"></tr>');
			} else {
				$('table.wp-list-table tbody').prepend( $('#bulk-edit') );
			}

			$('#bulk-edit').addClass('inline-editor').show();

			// Activate the add term ajax response tag
			$('#ajax-response').attr( 'id', 'default-ajax-response' );
			$( '#bulk-edit #bulkrow-ajax-response' ).attr( 'id', 'ajax-response' );

			$('tbody th.check-column input[type="checkbox"]').each(function(){
				if ( $(this).prop('checked') ) {
					c = false;
					var id = $(this).val(), theTitle;
					theTitle = $('#inline_'+id+' .post_title').text() || mla.settings.noTitle;
					te += '<div id="ttle'+id+'"><a id="_'+id+'" class="ntdelbutton" title="'+mla.settings.ntdelTitle+'">X</a>'+theTitle+'</div>';
				}
			});

			if ( c )
				return this.revert();

			$('#bulk-titles').html(te);
			$('#bulk-titles a').on( 'click', function(){
				var id = $(this).attr('id').substr(1);

				$('table.wp-list-table input[value="' + id + '"]').prop('checked', false);
				$('#ttle'+id).remove();
			});

			//flat taxonomies
			$('textarea.mla_tags').each(function(){
				var taxname = $(this).attr('name').replace(']', '').replace('tax_input[', '');

				$(this).suggest( ajaxurl + '?action=ajax-tag-search&tax=' + taxname, { delay: 500, minchars: 2, multiple: true, multipleSep: mla.settings.comma + ' ' } );
			});

			$('html, body').animate( { scrollTop: 0 }, 'fast' );

			if ( ( typeof quicktags !== 'undefined' ) && ( typeof  mla.settings.quickTagsInit !== 'undefined' ) ) {
				for ( id in mla.settings.quickTagsInit ) {
					quicktags( mla.settings.quickTagsInit[id] );

					if ( mla.settings.quickTagsInit[id]['active'] ) {
						window.wpActiveEditor = id;
					}
				}
			}
		},

		bulkSave : function(e) {
			var ids;

			mla.bulkEdit = {
				inProcess: false,
				doCancel: false,
				chunkSize: 0,
				targetName: '',
				fields: '',
				ids: [],
				idsCount: 0,
				offset: 0,
				waiting: 0,
				running: 0,
				complete: 0,
				unchanged:0,
				success: 0,
				failure: 0
			};

			//mla.bulkEdit.ids = []; // clone doesn't do this.
			mla.bulkEdit.chunkSize = +mla.settings.bulkChunkSize;
			mla.bulkEdit.targetName = e.target.name;
			mla.bulkEdit.fields = $('#bulk-edit :input').serialize();
			ids = $('tbody th.check-column input[type="checkbox"]').serializeArray();
			$.each( ids, function( index, id ) {
				mla.bulkEdit.ids[ index ] = +id.value;
			});
			mla.bulkEdit.idsCount = mla.bulkEdit.waiting = mla.bulkEdit.ids.length;
			//console.log( JSON.stringify( mla.bulkEdit ) );

			mla.inlineEditAttachment.bulkProgressOpen();
			mla.inlineEditAttachment.bulkPost();
			return false;
		},

		bulkProgressOpen : function(){
			var te = '', c = true;
			this.revert();

			$('#bulk-progress td').attr('colspan', $( 'th:visible, td:visible', '.widefat:first thead' ).length);
			$('table.wp-list-table tbody').prepend( $('#bulk-progress') );
			$('#bulk-progress').addClass('inline-editor').show();
			$('#cb-select-all-1' ).removeAttr( 'checked' );
			$('#cb-select-all-2' ).removeAttr( 'checked' );

			$('tbody th.check-column input[type="checkbox"]').each(function(){
				if ( $(this).prop('checked') ) {
					c = false;
					var id = $(this).val(), theTitle;
					theTitle = $('#inline_'+id+' .post_title').text() || mla.settings.noTitle;
					te += '<div id="ttle'+id+'"><a id="_'+id+'" class="ntdelbutton" title="'+mla.settings.ntdelTitle+'">X</a>'+theTitle+'</div>';
				}
			});

			if ( c )
				return this.revert();

			$('#bulk-progress-running').html('');
			$('#bulk-progress-complete').html('');
			$('#bulk-progress-waiting').html(te);
			$('#bulk-progress-waiting a').on( 'click', function(){
				var id = $(this).attr('id').substr(1);

				$('table.wp-list-table input[value="' + id + '"]').prop('checked', false);
				$('#ttle'+id).remove();
			});

			// Disable "Refresh" until the bulk updates are complete
			$( '#bulk-progress .inline-edit-save .error' ).html( '' );;
			$( '#bulk_refresh' ).prop( 'disabled', true ).css( 'opacity', '0.5' );
			$('html, body').animate( { scrollTop: 0 }, 'fast' );
		},

		bulkPost : function() {
			var params, chunk, cIndex, item, statusMessage,
				spinner = $('table.wp-list-table .inline-edit-save .spinner'),
				results = $( '#bulk-progress .inline-edit-save .error' ),
				waiting = $( '#bulk-progress-waiting' ),
				running = $( '#bulk-progress-running' );

			// Find the items to process
			chunk = mla.bulkEdit.ids.slice( mla.bulkEdit.offset, mla.bulkEdit.offset + mla.bulkEdit.chunkSize );

			// Move them from waiting to running
			for ( cIndex = 0; cIndex < chunk.length; cIndex++ ) {
				item = $( '#ttle' + chunk[ cIndex ], waiting ).remove()
				$( 'a', item ).hide();
				running.append( item );
			}
			mla.bulkEdit.waiting -= chunk.length;
			mla.bulkEdit.running = chunk.length;

			params = {
				action: mla.settings.ajax_action,
				mla_admin_nonce: mla.settings.ajax_nonce,
				bulk_action: mla.bulkEdit.targetName,
				cb_offset: mla.bulkEdit.offset,
				cb_count: mla.bulkEdit.idsCount,
				cb_attachment: chunk
			};

			mla.bulkEdit.offset += mla.bulkEdit.chunkSize;

			params = $.param( params ) + '&' + mla.bulkEdit.fields;
			//console.log( params );

			// make ajax request
			mla.bulkEdit.inProcess = true;

			if ( mla.settings.useSpinnerClass ) {
				spinner.addClass("is-active");
			} else {
				spinner.show();
			}

			statusMessage = mla.settings.bulkWaiting + ': ' + mla.bulkEdit.waiting
				+ ', ' + mla.settings.bulkComplete + ': ' + mla.bulkEdit.complete
				+ ', ' + mla.settings.bulkUnchanged + ': ' + mla.bulkEdit.unchanged
				+ ', ' + mla.settings.bulkSuccess + ': ' + mla.bulkEdit.success
				+ ', ' + mla.settings.bulkFailure + ': ' + mla.bulkEdit.failure;
			results.html( statusMessage ).show();

			$.ajax( ajaxurl, {
				type: 'POST',
				data: params,
				dataType: 'json'
			}).always( function() {

				if ( mla.settings.useSpinnerClass ) {
					spinner.removeClass("is-active");
				} else {
					spinner.hide();
				}

			}).done( function( response, status ) {
					var responseData = 'no response.data', responseMessage, items;

					if ( mla.settings.useSpinnerClass ) {
						spinner.removeClass("is-active");
					} else {
						spinner.hide();
					}

					if ( response ) {
						if ( ! response.success ) {
							if ( response.responseData ) {
								responseData = response.data;
							}

							results.html( JSON.stringify( response ) ).show();
							mla.bulkEdit.offset = mla.bulkEdit.idsCount; // Stop
						} else {
							// Move the items from Running to Complete
							items = $( '#bulk-progress-running div' ).remove();
							$.each( items, function() {
								var result, title = $( this ).html(),
									id = $( this ).attr('id').substr( 4 );

								if ( 'string' === typeof( response.data.item_results[ id ]['result'] ) ) {
									result = response.data.item_results[ id ]['result'];
									$( this ).html( title + ' (' + id + ') - ' + result );
								}

								$( '#attachment-' + id ).remove();
							});

							$( '#bulk-progress-complete' ).append( items );
							mla.bulkEdit.complete += mla.bulkEdit.running;
							mla.bulkEdit.running = 0;
							mla.bulkEdit.unchanged += response.data.unchanged;
							mla.bulkEdit.success += response.data.success;
							mla.bulkEdit.failure += response.data.failure;

							responseMessage = mla.settings.bulkWaiting + ': ' + mla.bulkEdit.waiting
								+ ', ' + mla.settings.bulkComplete + ': ' + mla.bulkEdit.complete
								+ ', ' + mla.settings.bulkUnchanged + ': ' + mla.bulkEdit.unchanged
								+ ', ' + mla.settings.bulkSuccess + ': ' + mla.bulkEdit.success
								+ ', ' + mla.settings.bulkFailure + ': ' + mla.bulkEdit.failure;
							results.html( responseMessage ).show();
						}
					} else {
						results.html( mla.settings.error ).show();
						mla.bulkEdit.offset = mla.bulkEdit.idsCount; // Stop
					}

					if ( mla.bulkEdit.doCancel ) {
						results.html( mla.settings.bulkCanceled + '. ' +  responseMessage ).show();
					} else {
						if ( mla.bulkEdit.offset < mla.bulkEdit.idsCount ) {
							mla.inlineEditAttachment.bulkPost();
							return;
						}
					}

					$( '#bulk_refresh' ).prop( 'disabled', false ).css( 'opacity', '1.0' );
					mla.bulkEdit.inProcess = false;

				// Restore the default ajax response tag
				$( '#bulk-edit #ajax-response' ).attr( 'id', 'bulkrow-ajax-response' ).empty();
				$('#default-ajax-response').attr( 'id', 'ajax-response' );
			}).fail( function( jqXHR, status ) {
				if ( 200 == jqXHR.status ) {
					results.text( '(' + status + ') ' + jqXHR.responseText );
				} else {
					results.text( mla.settings.ajaxFailError + ' (' + status + '), jqXHR( ' + jqXHR.status + ', ' + jqXHR.statusText + ', ' + jqXHR.responseText + ')' );
				}
			});
		},

		quickEdit : function(id) {
			var t = this, fields, editRow, rowData, checkedOnTop = [], icon, fIndex;
			t.revert();

			if ( typeof(id) == 'object' )
				id = mla.utility.getId(id);

			fields = mla.settings.fields;

			/*
			 * add the new edit row with an extra blank row underneath
			 * in WP 4.2+ to maintain zebra striping
			 */
			editRow = $('#inline-edit').clone(true);
			$( 'td', editRow ).attr('colspan', $( 'th:visible, td:visible', '.widefat:first thead' ).length);

			// Activate the add term ajax response tag
			$('#ajax-response').attr( 'id', 'default-ajax-response' );
			$( '#qerow-ajax-response', editRow ).attr( 'id', 'ajax-response' );

			if ( mla.settings.useSpinnerClass ) {
				$(t.what+id).hide().after(editRow).after('<tr class="hidden"></tr>');
			} else {
				if ( $(t.what+id).hasClass('alternate') ) {
					$(editRow).addClass('alternate');
				}

				$(t.what+id).hide().after(editRow);
			}

			// populate the data
			rowData = $('#inline_'+id);

			icon = $('.item_thumbnail', rowData).html();
			if ( icon.length ) {
				$( '#item_thumbnail', editRow ).html( icon );
			}

			if ( !$(':input[name="post_author"] option[value="' + $('.post_author', rowData).text() + '"]', editRow).val() ) {
				// author no longer has edit caps, so we need to add them to the list of authors
				$(':input[name="post_author"]', editRow).prepend('<option value="' + $('.post_author', rowData).text() + '">' + $('#' + t.type + '-' + id + ' .author').text() + '</option>');
			}

			if ( $(':input[name="post_author"] option', editRow).length == 1 ) {
				$('label.inline-edit-author', editRow).hide();
			}

			for ( fIndex = 0; fIndex < fields.length; fIndex++ ) {
				$(':input[name="' + fields[fIndex] + '"]', editRow).val( $('.'+fields[fIndex], rowData).text() );
			}

			if ( $('.image_alt', rowData).length === 0) {
				$('label.inline-edit-image-alt', editRow).hide();
			}

			// checklist taxonomies having "checked terms on top"
			$('.checked_on_top', rowData).each(function(){
				checkedOnTop[checkedOnTop.length] = $(this).attr('id').replace('_'+id, '');
			});

			// hierarchical taxonomies
			$('.mla_category', rowData).each(function(){
				var term_ids = $(this).text(), taxname = $(this).attr('id').replace('_'+id, ''), checkedLabels, checkedTerms;

				if ( term_ids ) {
					$('ul.'+taxname+'checklist :checkbox', editRow).val(term_ids.split(','));
					
					if ( -1 !== checkedOnTop.indexOf( taxname ) ) {
						checkedLabels = $('ul.'+taxname+'checklist li :checked', editRow ).parents( 'label' ).remove().toArray().reverse();
						checkedTerms = $( '<li></li>' ).html( checkedLabels );
						$('ul.'+taxname+'checklist', editRow ).prepend( checkedTerms );
					}
				}
			});

			//flat taxonomies
			$('.mla_tags', rowData).each(function(){
				var terms = $(this).text(),
					taxname = $(this).attr('id').replace('_' + id, ''),
					textarea = $('textarea.tax_input_' + taxname, editRow),
					comma = mla.settings.comma, langArgument;

				if ( terms ) {
					if ( ',' !== comma )
						terms = terms.replace(/,/g, comma);
					textarea.val(terms);
				}

			langArgument = $('.lang', rowData).text();
			if ( 0 < langArgument.length ) {
				langArgument = '&lang=' + langArgument;
			} else {
				langArgument = '';
			}

				textarea.suggest( ajaxurl + '?action=ajax-tag-search&tax=' + taxname + '&preview_id=' + id + langArgument, { delay: 500, minchars: 2, multiple: true, multipleSep: mla.settings.comma + ' ' } );
			});

			rowData = $(editRow).attr('id', 'edit-'+id).addClass('inline-editor').show().position().top;
			$('.ptitle', editRow).trigger('focus');
			$( 'html, body' ).animate( { scrollTop: rowData }, 'fast' );

			// Make the edit-fields-div id unique again
			$( '#inline-edit-fields-div', editRow ).attr( 'id', 'inline-edit-fields-div-active' );
			if ( typeof mla.addTerm !== 'undefined' ) {
				mla.addTerm.init( '#inline-edit-fields-div-active' );
			}
			mla.utility.attachSearch( '#inline-edit-fields-div-active' );
			return false;
		},

		quickSave : function( id ) {
			var params, fields, page = $('.post_status_page').val() || '';

			if ( typeof(id) == 'object' ) {
				id = mla.utility.getId(id);
			}

			if ( mla.settings.useSpinnerClass ) {
				$('table.wp-list-table .inline-edit-save .spinner').addClass("is-active");
			} else {
				$('table.wp-list-table .inline-edit-save .spinner').show();
			}

			params = {
				action: mla.settings.ajax_action,
				mla_admin_nonce: mla.settings.ajax_nonce,
				post_type: 'attachment',
				post_ID: id,
				edit_date: 'true',
				post_status: page
			};

			fields = $('#edit-' + id + ' :input').serialize();
			params = fields + '&' + $.param(params);

			// make ajax request
			$.post( ajaxurl, params,
				function( response ) {
					if ( mla.settings.useSpinnerClass ) {
						$('table.wp-list-table .inline-edit-save .spinner').removeClass("is-active");
					} else {
						$('table.wp-list-table .inline-edit-save .spinner').hide();
					}

					if ( response ) {
						if ( -1 != response.indexOf( '<tr' ) ) {
							if ( mla.settings.useSpinnerClass ) {
								$( mla.inlineEditAttachment.what + id ).siblings('tr.hidden').addBack().remove();
							} else {
								$( mla.inlineEditAttachment.what + id ).remove();
							}


							$( '#edit-' + id ).before( response ).remove();
							$( mla.inlineEditAttachment.what + id ).hide().fadeIn();

							// Restore the default ajax response tag
							$('#default-ajax-response').attr( 'id', 'ajax-response' );
						} else {
							response = response.replace( /<.[^<>]*?>/g, '' );
							$( '#edit-' + id + ' .inline-edit-save .error' ).html( response ).show();
						}
					} else {
						$( '#edit-' + id + ' .inline-edit-save .error' ).html( mla.settings.error ).show();
					}
				}, 'html');

			return false;
		},

		inlineParentOpen : function( id ) {
			var parentId, postId, postTitle;

			if ( typeof( id ) == 'object' ) {
				postId = mla.utility.getId( id );
				parentId = $( '#edit-' + postId + ' :input[name="post_parent"]' ).val() || '';
				postTitle = $( '#edit-' + postId + ' :input[name="post_title"]' ).val() || '';
				mla.setParent.open( parentId, postId, postTitle );
				/*
				 * Grab the "Update" button
				 */
				$( '#mla-set-parent-submit' ).on( 'click', function( event ){
					event.preventDefault();
					mla.inlineEditAttachment.inlineParentSave( postId );
					return false;
				});
			}
		},

		inlineParentSave : function( postId ) {
			var foundRow = $( '#mla-set-parent-response-div input:checked' ).closest( 'tr' ), parentId, parentTitle,
				editRow = $( '#edit-' + postId ), newParent, newTitle;

			if ( foundRow.length ) {
				parentId = $( ':radio', foundRow ).val() || '';
				parentTitle = $( 'label', foundRow ).html() || '';
				newParent = $(':input[name="post_parent"]', editRow).clone( true ).val( parentId );
				newTitle = $(':input[name="post_parent_title"]', editRow).clone( true ).val( parentTitle );
				$(':input[name="post_parent"]', editRow).replaceWith( newParent );
				$(':input[name="post_parent_title"]', editRow).replaceWith( newTitle );
			}

			mla.setParent.close();
			$('#mla-set-parent-submit' ).off( 'click' );
		},

		bulkParentOpen : function() {
			var parentId, postId, postTitle;

			postId = -1;
			postTitle = mla.settings.bulkTitle;
			parentId = $( '#bulk-edit :input[name="post_parent"]' ).val() || -1;
			mla.setParent.open( parentId, postId, postTitle );
			/*
			 * Grab the "Update" button
			 */
			$( '#mla-set-parent-submit' ).on( 'click', function( event ){
				event.preventDefault();
				mla.inlineEditAttachment.bulkParentSave();
				return false;
			});
		},

		bulkParentSave : function() {
			var foundRow = $( '#mla-set-parent-response-div input:checked' ).closest( 'tr' ), parentId, newParent;

			if ( foundRow.length ) {
				parentId = $( ':radio', foundRow ).val() || '';
				newParent = $('#bulk-edit :input[name="post_parent"]').clone( true ).val( parentId );
				$('#bulk-edit :input[name="post_parent"]').replaceWith( newParent );
			}

			mla.setParent.close();
			$('#mla-set-parent-submit' ).off( 'click' );
		},

		tableParentOpen : function( parentId, postId, postTitle ) {
			mla.setParent.open( parentId, postId, postTitle );
			/*
			 * Grab the "Update" button
			 */
			$( '#mla-set-parent-submit' ).on( 'click', function( event ){
				event.preventDefault();
				mla.inlineEditAttachment.tableParentSave( postId );
				return false;
			});
		},

		tableParentSave : function( postId ) {
			var foundRow = $( '#mla-set-parent-response-div input:checked' ).closest( 'tr' ),
				parentId = $( ':radio', foundRow ).val() || '-1',
				params, tableCell = $( '#attachment-' + postId + " td.attached_to" ).clone( true );

			if ( foundRow.length && ( parentId >= 0 ) ) {
				tableCell = $( '#attachment-' + postId + " td.attached_to" ).clone( true );
				tableCell.html( '<span class="spinner"></span>' );
				$( '#attachment-' + postId + " td.attached_to" ).replaceWith( tableCell );

				if ( mla.settings.useSpinnerClass ) {
					$( '#attachment-' + postId + " td.attached_to .spinner" ).addClass("is-active");
				} else {
					$( '#attachment-' + postId + " td.attached_to .spinner" ).show();
				}

				params = $.param( {
					action: mla.settings.ajax_action + '-set-parent',
					mla_admin_nonce: mla.settings.ajax_nonce,
					post_ID: postId,
					post_parent: parentId,
				} );

				$.post( ajaxurl, params,
					function( response ) {
						if ( response ) {
							if ( -1 == response.indexOf( 'tableParentOpen(' ) ) {
								response = response.replace( /<.[^<>]*?>/g, '' );
							}
						} else {
							response = mla.settings.ajaxFailError;
						}

						$( '#attachment-' + postId ).before( response ).remove();
						$( '#attachment-' + postId ).hide().fadeIn();
					}, 'html');
			} else {
				tableCell.html( mla.settings.error );
				$( '#attachment-' + postId + " td.attached_to" ).replaceWith( tableCell );
			}

			$('#mla-set-parent-submit' ).off( 'click' );
			mla.setParent.close();
		},

		doExport : function(){
//console.log( 'TODO: doExport' );
//return false;

			var post = {
					action: mla.settings.exportPresetsAction,
					mla_preset_values: $( '#bulk-edit :input' ).serialize(),
					mla_preset_option: mla.settings.exportPresetsOption,
					mla_admin_nonce: $('#mla-export-presets-ajax-nonce').val()
				},
				spinner = $( 'table.wp-list-table #bulk-edit-fields-div .spinner' );

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

					$( '#ajax-response' ).html( '<strong>' + mla.settings.ajaxDoneError + ' (' + responseData + ')</strong>' );
				} else {
					// replace the old presets with the current values
					$( '#preset-bulk-edit-fields-div' ).html( response.data );
				}
			}).fail( function( jqXHR, status ) {
				if ( 200 == jqXHR.status ) {
					$( '#ajax-response' ).text( '(' + status + ') ' + jqXHR.responseText );
				} else {
					$( '#ajax-response' ).text( mla.settings.ajaxFailError + ' (' + status + '), jqXHR( ' + jqXHR.status + ', ' + jqXHR.statusText + ', ' + jqXHR.responseText + ')' );
				}
			});

			return false;
		},

		doImport : function(){
			var id = $('table.wp-list-table tr.inline-editor').attr('id'),
				bulkRow = $('table.wp-list-table #bulk-edit'),
				presetRow = $('#inlineedit #preset-bulk-edit'),
				presetCategories = $('.inline-edit-categories', presetRow ).html(),
				presetTags = $('.inline-edit-tags', presetRow ).html(),
				presetFields = $('.inline-edit-fields', presetRow ).html();

			if ( id ) {
				if ( mla.settings.useSpinnerClass ) {
					$('table.wp-list-table .inline-edit-save .spinner').removeClass("is-active");
				} else {
					$('table.wp-list-table .inline-edit-save .spinner').hide();
				}

				if ( 'bulk-edit' == id ) {
					$('.inline-edit-categories', bulkRow ).html( presetCategories ),
					$('.inline-edit-tags', bulkRow ).html( presetTags ),
					$('.inline-edit-fields', bulkRow ).html( presetFields );

					if ( ( typeof quicktags !== 'undefined' ) && ( typeof  mla.settings.quickTagsInit !== 'undefined' ) ) {
						for ( id in mla.settings.quickTagsInit ) {
							quicktags( mla.settings.quickTagsInit[id] );
		
							if ( mla.settings.quickTagsInit[id]['active'] ) {
								window.wpActiveEditor = id;
							}
						}
					}

					if ( typeof mla.addTerm !== 'undefined' ) {
						mla.addTerm.init( '#bulk-edit-fields-div' );
					}
					mla.utility.attachSearch( '#bulk-edit-fields-div' );

					$('#bulk-edit-set-parent', bulkRow).on( 'click', function(){
						return mla.inlineEditAttachment.bulkParentOpen();
					});
				}
			}

			return false;
		},

		doReset : function(){
			var id = $('table.wp-list-table tr.inline-editor').attr('id'),
				bulkRow = $('table.wp-list-table #bulk-edit'),
				blankRow = $('#inlineedit #blank-bulk-edit'),
				blankCategories = $('.inline-edit-categories', blankRow ).html(),
				blankTags = $('.inline-edit-tags', blankRow ).html(),
				blankFields = $('.inline-edit-fields', blankRow ).html();

			if ( id ) {
				if ( mla.settings.useSpinnerClass ) {
					$('table.wp-list-table .inline-edit-save .spinner').removeClass("is-active");
				} else {
					$('table.wp-list-table .inline-edit-save .spinner').hide();
				}

				if ( 'bulk-edit' == id ) {
					$('.inline-edit-categories', bulkRow ).html( blankCategories ),
					$('.inline-edit-tags', bulkRow ).html( blankTags ),
					$('.inline-edit-fields', bulkRow ).html( blankFields );

					if ( ( typeof quicktags !== 'undefined' ) && ( typeof  mla.settings.quickTagsInit !== 'undefined' ) ) {
						for ( id in mla.settings.quickTagsInit ) {
							quicktags( mla.settings.quickTagsInit[id] );
		
							if ( mla.settings.quickTagsInit[id]['active'] ) {
								window.wpActiveEditor = id;
							}
						}
					}

					if ( typeof mla.addTerm !== 'undefined' ) {
						mla.addTerm.init( '#bulk-edit-fields-div' );
					}
					mla.utility.attachSearch( '#bulk-edit-fields-div' );

					$('#bulk-edit-set-parent', bulkRow).on( 'click', function(){
						return mla.inlineEditAttachment.bulkParentOpen();
					});
				}
			}

			return false;
		},

		revert : function(){
			var id = $('table.wp-list-table tr.inline-editor').attr('id');

			if ( id ) {
				if ( mla.settings.useSpinnerClass ) {
					$('table.wp-list-table .inline-edit-save .spinner').removeClass("is-active");
				} else {
					$('table.wp-list-table .inline-edit-save .spinner').hide();
				}

				if ( 'bulk-edit' == id ) {
					if ( mla.settings.useSpinnerClass ) {
						$('table.wp-list-table #bulk-edit').removeClass('inline-editor').hide().siblings('tr.hidden').remove();
					} else {
						$('table.wp-list-table #bulk-edit').removeClass('inline-editor').hide();
					}

					$('#bulk-titles').html('');
					$('#inlineedit').append( $('#bulk-edit') );
				} else {
					if ( 'bulk-progress' == id ) {
						$('table.wp-list-table #bulk-progress').removeClass('inline-editor').hide();
						$('#bulk-progress-waiting').html('');
						$('#inlineedit').append( $('#bulk-progress') );
					} else {
						if ( mla.settings.useSpinnerClass ) {
							$('#'+id).siblings('tr.hidden').addBack().remove();
						} else {
							$('#'+id).remove();
						}

						id = id.substr( id.lastIndexOf('-') + 1 );
						$(this.what+id).show();
					}
				}
			}


			// Restore the default ajax response tag
			$( '#bulk-edit #ajax-response' ).attr( 'id', 'bulkrow-ajax-response' ).empty();
			$('#default-ajax-response').attr( 'id', 'ajax-response' );
			return false;
		}
	}; // mla.inlineEditAttachment

	$( document ).ready( function() {
		mla.inlineEditAttachment.init();
	});
})( jQuery );
