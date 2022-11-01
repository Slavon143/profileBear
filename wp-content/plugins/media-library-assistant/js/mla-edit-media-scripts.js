var jQuery,
	mla_edit_media_vars,
	mla = {
		// Properties
		// mla.settings.uploadLabel
		// mla.settings.modifyLabel
		settings: {},

		// Utility functions
		utility: {
		},

		// Components
		setParent: null,
		mlaEditAttachment: null
	};

( function( $ ) {
	// Localized settings and strings
	mla.settings = typeof mla_edit_media_vars === 'undefined' ? {} : mla_edit_media_vars;
	mla_edit_media_vars = void 0; // delete won't work on Globals

	// The mlaEditAttachment functions are adapted from wp-admin/js/post.js
	mla.mlaEditAttachment = {
		$uploaddiv : null,
		uploadtimestamp : '',
		$modifydiv : null,
		modifytimestamp : '',
				
		init : function(){
			$( '#mla_set_parent' ).on( 'click', function(){
				return mla.mlaEditAttachment.setParentOpen();
			});

			$('.categorydiv').each( function(){
				var this_id = $(this).attr('id'), taxonomyParts, taxonomy;

				taxonomyParts = this_id.split('-');
				taxonomyParts.shift(); // taxonomy-
				taxonomy = taxonomyParts.join('-');

				$.extend( $.expr.pseudos || $.expr[":"], {
					"matchTerms": function( elem, i, match, array ) {
						return ( elem.textContent || elem.innerText || "" ).toLowerCase().indexOf( ( match[3] || "" ).toLowerCase() ) >= 0;
					}
				});

				$( '#search-' + taxonomy ).on( 'keypress', function( event ){

					if( 13 === event.keyCode ) {
						event.preventDefault();
						$( '#search-'  + taxonomy ).val( '' );
						$( '#' + taxonomy + '-searcher' ).addClass( 'wp-hidden-children' );

						$( '#' + taxonomy + 'checklist li' ).show();
						$( '#' + taxonomy + 'checklist-pop li' ).show();
						return;
					}

				} );

				$( '#search-' + taxonomy ).on( 'keyup', function( event ){
					var searchValue, termList, termListPopular, matchingTerms, matchingTermsPopular;

					if( 13 === event.keyCode ) {
						event.preventDefault();
						$( '#' + taxonomy + '-search-toggle' ).trigger('focus');
						return;
					}

					searchValue = $( '#search-' + taxonomy ).val();
					termList = $( '#' + taxonomy + 'checklist li' );
					termListPopular = $( '#' + taxonomy + 'checklist-pop li' );

					if ( 0 < searchValue.length ) {
						termList.hide();
						termListPopular.hide();
					} else {
						termList.show();
						termListPopular.show();
					}

					matchingTerms = $( '#' + taxonomy + "checklist label:matchTerms('" + searchValue + "')");
					matchingTerms.closest( 'li' ).find( 'li' ).addBack().show();
					matchingTerms.parents( '#' + taxonomy + 'checklist li' ).show();

					matchingTermsPopular = $( '#' + taxonomy + "checklist-pop label:matchTerms('" + searchValue + "')");
					matchingTermsPopular.closest( 'li' ).find( 'li' ).addBack().show();
					matchingTermsPopular.parents( '#' + taxonomy + 'checklist li' ).show();
				} );

				$( '#' + taxonomy + '-search-toggle' ).on( 'click', function() {
					$( '#' + taxonomy + '-adder ').addClass( 'wp-hidden-children' );
					$( '#' + taxonomy + '-searcher' ).toggleClass( 'wp-hidden-children' );
					$( 'a[href="#' + taxonomy + '-all"]', '#' + taxonomy + '-tabs' ).trigger('click');
					$( '#' + taxonomy + 'checklist li' ).show();
					$( '#' + taxonomy + 'checklist-pop li' ).show();

					if ( false === $( '#' + taxonomy + '-searcher' ).hasClass( 'wp-hidden-children' ) ) {
						$( '#search-'  + taxonomy ).val( '' ).removeClass( 'form-input-tip' );
						$( '#search-' + taxonomy ).trigger('focus');
					}

					return false;
				});

				// Supplement the click logic in wp-admin/js/post.js
				$( '#' + taxonomy + '-add-toggle' ).on( 'click', function() {
					$( '#' + taxonomy + '-searcher' ).addClass( 'wp-hidden-children' );
					return false;
				});
			}); // .categorydiv.each, 

			// Save Post box (#submitdiv), for Uploaded on and Last modified dates
			if ( $('#submitdiv').length ) {
				mla.mlaEditAttachment.uploadtimestamp = $('#upload-timestamp').html();
				mla.mlaEditAttachment.$uploaddiv = $('#timestampdiv');
				//mla.mlaEditAttachment.modifytimestamp = $('#modify-timestamp').html();
				//mla.mlaEditAttachment.$modifydiv = $('#modifytimestampdiv');

				// Edit Uploaded on click.
				mla.mlaEditAttachment.$uploaddiv.siblings('a.edit-timestamp').on( 'click', function( event ) {
					if ( mla.mlaEditAttachment.$uploaddiv.is( ':hidden' ) ) {
						mla.mlaEditAttachment.$uploaddiv.slideDown( 'fast', function() {
							$( 'input, select', mla.mlaEditAttachment.$uploaddiv.find( '.timestamp-wrap' ) ).first().trigger('focus');
						} );
						$(this).hide();
					}
					event.preventDefault();
				});
		
				// Cancel editing the Uploaded on time and hide the settings.
				mla.mlaEditAttachment.$uploaddiv.find('.cancel-timestamp').on( 'click', function( event ) {
					mla.mlaEditAttachment.$uploaddiv.slideUp('fast').siblings('a.edit-timestamp').show().trigger('focus');
					$( '#mm', mla.mlaEditAttachment.$uploaddiv ).val($( '#hidden_mm', mla.mlaEditAttachment.$uploaddiv ).val());
					$( '#jj', mla.mlaEditAttachment.$uploaddiv ).val($( '#hidden_jj', mla.mlaEditAttachment.$uploaddiv ).val());
					$( '#aa', mla.mlaEditAttachment.$uploaddiv ).val($( '#hidden_aa', mla.mlaEditAttachment.$uploaddiv ).val());
					$( '#hh', mla.mlaEditAttachment.$uploaddiv ).val($( '#hidden_hh', mla.mlaEditAttachment.$uploaddiv ).val());
					$( '#mn', mla.mlaEditAttachment.$uploaddiv ).val($( '#hidden_mn', mla.mlaEditAttachment.$uploaddiv ).val());
					mla.mlaEditAttachment.updateText( mla.mlaEditAttachment.$uploaddiv, mla.mlaEditAttachment.uploadtimestamp, '#upload-timestamp' );
					event.preventDefault();
				});
		
				// Save the changed Uploaded on timestamp.
				mla.mlaEditAttachment.$uploaddiv.find('.save-timestamp').on( 'click', function( event ) { // crazyhorse - multiple ok cancels
					if ( mla.mlaEditAttachment.updateText( mla.mlaEditAttachment.$uploaddiv, mla.mlaEditAttachment.uploadtimestamp, '#upload-timestamp' ) ) {
						mla.mlaEditAttachment.$uploaddiv.slideUp('fast');
						mla.mlaEditAttachment.$uploaddiv.siblings('a.edit-timestamp').show().trigger('focus');
					}
					event.preventDefault();
				});
		
				// Cancel submit when an invalid Uploaded on timestamp has been selected.
				$('#post').on( 'submit', function( event ) {
					if ( ! mla.mlaEditAttachment.updateText( mla.mlaEditAttachment.$uploaddiv, mla.mlaEditAttachment.uploadtimestamp, '#upload-timestamp' ) ) {
						event.preventDefault();
						mla.mlaEditAttachment.$uploaddiv.show();
		
						if ( wp.autosave ) {
							wp.autosave.enableButtons();
						}
		
						$( '#publishing-action .spinner' ).removeClass( 'is-active' );
					}
				});
			} // $('#submitdiv').length
		}, // function init

		setParentOpen : function() {
			var parentId, postId, postTitle;

			parentId = $( '#mla_post_parent' ).val() || '';
			postId = $( '#post_ID' ).val() || '';
			postTitle = $( '#title' ).val() || '';
			mla.setParent.open( parentId, postId, postTitle );

			// Grab the "Update" button
			$( '#mla-set-parent-submit' ).on( 'click', function( event ){
				event.preventDefault();
				mla.mlaEditAttachment.setParentSave();
				return false;
			});
		},

		setParentSave : function() {
			var foundRow = $( '#mla-set-parent-response-div input:checked' ).closest( 'tr' ),
				parentId, parentTitle, newParent, newTitle;

			if ( foundRow.length ) {
				parentId = $( ':radio', foundRow ).val() || '';
				parentTitle = $( 'label', foundRow ).html() || '';
				newParent = $( '#mla_post_parent' ).clone( true ).val( parentId );
				newTitle = $( '#mla_parent_info' ).clone( true ).val( parentTitle );
				$( '#mla_post_parent' ).replaceWith( newParent );
				$( '#mla_parent_info' ).replaceWith( newTitle );
				mla.setParent.close();
			}

			$( '#mla-set-parent-submit' ).off( 'click' );
		},

		/*
		 * Make sure all Uploaded on or Last Modified labels represent the current settings.
		 *
		 * @returns {boolean} False when an invalid timestamp has been selected, otherwise True.
		 */
		updateText : function( $div, stamp, stampdiv ) {

			if ( ! $div.length )
				return true;

			var attemptedDate, originalDate, currentDate, label, value,
			    aa = $( '#aa', $div ).val(), mm = $( '#mm', $div ).val(), jj = $( '#jj', $div ).val(),
			    hh = $( '#hh', $div ).val(), mn = $( '#mn', $div ).val();

			attemptedDate = new Date( aa, mm - 1, jj, hh, mn );
			originalDate = new Date( $( '#hidden_aa', $div ).val(), $( '#hidden_mm', $div ).val() -1, $( '#hidden_jj', $div ).val(), $( '#hidden_hh', $div ).val(), $( '#hidden_mn', $div ).val() );
			currentDate = new Date( $( '#cur_aa', $div ).val(), $( '#cur_mm', $div ).val() -1, $( '#cur_jj', $div ).val(), $( '#cur_hh', $div ).val(), $( '#cur_mn', $div ).val() );

			// Catch unexpected date problems.
			if ( attemptedDate.getFullYear() != aa || (1 + attemptedDate.getMonth()) != mm || attemptedDate.getDate() != jj || attemptedDate.getMinutes() != mn ) {
				$div.find('.timestamp-wrap').addClass('form-invalid');
				return false;
			} else {
				$div.find('.timestamp-wrap').removeClass('form-invalid');
			}

			// If the date is the same, set it to trigger update events.
			if ( originalDate.toUTCString() == attemptedDate.toUTCString() ) {
				// Re-set to the current value.
				$( stampdiv ).html( stamp );
			} else {
				label = '#upload-timestamp' == stampdiv ? mla.settings.uploadLabel : mla.settings.modifyLabel;

				// wp.i18n replaced postL10n in WP 5.0
				if ( 'object' === typeof wp.i18n ) {
					value = wp.i18n.__( '%1$s %2$s, %3$s at %4$s:%5$s' )
						.replace( '%1$s', $( 'option[value="' + mm + '"]', '#mm' ).attr( 'data-text' ) )
						.replace( '%2$s', parseInt( jj, 10 ) )
						.replace( '%3$s', aa )
						.replace( '%4$s', ( '00' + hh ).slice( -2 ) )
						.replace( '%5$s', ( '00' + mn ).slice( -2 ) );
				} else {
					value = postL10n.dateFormat
						.replace( '%1$s', $( 'option[value="' + mm + '"]', '#mm' ).attr( 'data-text' ) )
						.replace( '%2$s', parseInt( jj, 10 ) )
						.replace( '%3$s', aa )
						.replace( '%4$s', ( '00' + hh ).slice( -2 ) )
						.replace( '%5$s', ( '00' + mn ).slice( -2 ) );
				}
				
				$( stampdiv ).html(
					label + '<b>' + value +	'</b> '
				);
			}

			return true;
		}
	}; // mla.mlaEditAttachment

	$( document ).ready( function(){ mla.mlaEditAttachment.init(); } );
})( jQuery );
