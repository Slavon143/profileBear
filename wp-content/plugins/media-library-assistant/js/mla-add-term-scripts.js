/* global ajaxurl, mla  */

var jQuery;

/*
 * This script requires the global "mla" object to be defined and include the following:
 *
 * properties:
 *     mla.settings.useDashicons
 *
 * components:
 *     mla.addTerm
 *     mla.mlaList
 */

( function( $ ) {
	/**
	 * addTerm opens an area beneath a "checklist-style" taxonomy in the
	 * Media/Assistant Bulk Edit area and Media/Add New Bulk Edit area.
	 * It allows the user to add a term to the associated taxonomy.
	 */
	mla.addTerm = {
		/**
		 * Attach new term functions to each taxonomy in the appropriate context
		 *
		 * @param {string} contextId a CSS "id=" selector, i.e. starting with "#"
		 */
		init: function( contextId ) {
			/*
			 * contextId must be unique on the page, e.g., a <div> around the Bulk Edit or Quick Edit area.
			 * Each taxonomy's '.categorydiv' must appear just once within the context.
			 */
			var context = $( contextId );

			$( '.categorydiv',  context ).each( function(){
				var this_id = $(this).attr('id'), catAddBefore, catAddAfter, taxonomyParts, taxonomy;

				taxonomyParts = this_id.split('-');
				taxonomyParts.shift(); // taxonomy-
				taxonomy = taxonomyParts.join('-');

				// Add category button controls.
				$( '#new' + taxonomy, context ).one( 'focus', function() {
					$( this ).val( '' ).removeClass( 'form-input-tip' );
				});

				// On [enter] submit the taxonomy.
				$( '#new' + taxonomy, context ).on( 'keypress', function(event){
					if( 13 === event.keyCode ) {
						event.preventDefault();
						$( '#' + taxonomy + '-add-submit', context ).trigger('click');					}
				});

				// After submitting a new taxonomy, re-focus the input field.
				$( '#' + taxonomy + '-add-submit', context ).on( 'click', function() {
					$( '#new' + taxonomy, context ).focus();
				});

				/**
				 * Before adding a new taxonomy, disable submit button.
				 *
				 * @param {Object} s Taxonomy object which will be added.
				 *
				 * @returns {Object}
				 */
				catAddBefore = function( s ) {
					if ( !$( '#new' + taxonomy, context ).val() ) {
						return false;
					}

					s.data += '&' + $( ':checked', '#' + taxonomy  +'-checklist', context ).serialize();
					$( '#' + taxonomy + '-add-submit', context ).prop( 'disabled', true );
					return s;
				};

				/**
				 * Re-enable submit button after a taxonomy has been added.
				 *
				 * Re-enable submit button.
				 * If the taxonomy has a parent place the taxonomy underneath the parent.
				 *
				 * @param {Object} r Response.
				 * @param {Object} s Taxonomy data.
				 *
				 * @returns void
				 */
				catAddAfter = function( r, s ) {
					var sup;

					$( '#' + taxonomy + '-add-submit', context ).prop( 'disabled', false );

					// Update the "Parent Term" dropdown control(s)
					if ( 'undefined' != s.parsed.responses[0] && (sup = s.parsed.responses[0].supplemental.newcat_parent) ) {
						$( '.edit-fields-div' ).each( function(){
							var drop = $( '#new' + taxonomy + '_parent', $(this) );

							drop.before(sup);
							drop.remove();
						}); 
					}
				};

				mla.mlaList.settings.context = context;
				$( '#' + taxonomy + 'checklist', context ).mlaList({
					alt: '',
					context: context,
					response: 'ajax-response',
					addBefore: catAddBefore,
					addAfter: catAddAfter
				});

				$( '#' + taxonomy + '-add-toggle', context ).off();

				// Add new taxonomy button toggles input form visibility.
				$( '#' + taxonomy + '-add-toggle', context ).on( 'click', function( e ) {
					e.preventDefault();
					$( '#' + taxonomy + '-adder', context ).toggleClass( 'wp-hidden-children' );
					$( '#new' + taxonomy, context ).focus();
					// Hide the "? Search" text box when displaying the "+ Add New ..." text box
					$( '#' + taxonomy + '-searcher', context ).addClass( 'wp-hidden-children' );
					return false;
				});
			}); // .categorydiv.each, 
		}
	}; // mla.addTerm

	/**
	 * mlaList adapts the WordPress wpList object found in wp-includes/js/wp-lists.js
	 * It handles the case where more than one instance of a taxonomy "metabox" occurs
	 * on a page, e.g., the Bulk and Quick edit areas on the Media/Assistant admin page.
	 */
	mla.mlaList = {

		/**
		 * @member {object}
		 */
		settings: {

			/**
			 * URL for Ajax requests.
			 *
			 * @member {string}
			 */
			url: ajaxurl,

			/**
			 * The HTTP method to use for Ajax requests.
			 *
			 * @member {string}
			 */
			type: 'POST',

			/**
			 * jQuery context of the taxonomy's HTML elements.
			 *
			 * @member {object}
			 */
			context: null,

			/**
			 * ID of the element the parsed Ajax response will be stored in.
			 *
			 * @member {string}
			 */
			response: 'ajax-response',

			/**
			 * The type of list.
			 *
			 * @member {string}
			 */
			what: '',

			/**
			 * CSS class name for alternate styling.
			 *
			 * @member {string}
			 */
			alt: 'alternate',

			/**
			 * Offset to start alternate styling from.
			 *
			 * @member {number}
			 */
			altOffset: 0,

			/**
			 * Color used in animation when adding an element.
			 *
			 * Can be 'none' to disable the animation.
			 *
			 * @member {string}
			 */
			addColor: '#ffff33',

			/**
			 * Callback that's run before a request is made.
			 *
			 * @callback mlaList~confirm
			 * @param {object}      this
			 * @param {HTMLElement} list            The list DOM element.
			 * @param {object}      settings        Settings for the current list.
			 * @param {string}      action          The type of action to perform: 'add', 'delete', or 'dim'.
			 * @param {string}      backgroundColor Background color of the list's DOM element.
			 * @returns {boolean} Whether to proceed with the action or not.
			 */
			confirm: null,

			/**
			 * Callback that's run before an item gets added to the list.
			 *
			 * Allows to cancel the request.
			 *
			 * @callback mlaList~addBefore
			 * @param {object} settings Settings for the Ajax request.
			 * @returns {object|boolean} Settings for the Ajax request or false to abort.
			 */
			addBefore: null,

			/**
			 * Callback that's run after an item got added to the list.
			 *
			 * @callback mlaList~addAfter
			 * @param {XML}    returnedResponse Raw response returned from the server.
			 * @param {object} settings         Settings for the Ajax request.
			 * @param {jqXHR}  settings.xml     jQuery XMLHttpRequest object.
			 * @param {string} settings.status  Status of the request: 'success', 'notmodified', 'nocontent', 'error',
			 *                                  'timeout', 'abort', or 'parsererror'.
			 * @param {object} settings.parsed  Parsed response object.
			 */
			addAfter: null
		},

		/**
		 * Finds a nonce.
		 *
		 * 1. Nonce in settings.
		 * 2. `_ajax_nonce` value in element's href attribute.
		 * 3. `_ajax_nonce` input field that is a descendant of element.
		 * 4. `_wpnonce` value in element's href attribute.
		 * 5. `_wpnonce` input field that is a descendant of element.
		 * 6. 0 if none can be found.
		 *
		 * @param {jQuery} element  Element that triggered the request.
		 * @param {object} settings Settings for the Ajax request.
		 * @returns {string|number} Nonce
		 */
		nonce: function( element, settings ) {
			var url      = wpAjax.unserialize( element.attr( 'href' ) ),
				$element = $( '#' + settings.element, mla.mlaList.settings.context );

			return settings.nonce || url._ajax_nonce || $element.find( 'input[name="_ajax_nonce"]' ).val() || url._wpnonce || $element.find( 'input[name="_wpnonce"]' ).val() || 0;
		},

		/**
		 * Extract list item data from a DOM element.
		 *
		 * Example 1: data-wp-lists="delete:the-comment-list:comment-{comment_ID}:66cc66:unspam=1"
		 * Example 2: data-wp-lists="dim:the-comment-list:comment-{comment_ID}:unapproved:e7e7d3:e7e7d3:new=approved"
		 *
		 * Returns an unassociated array with the following data:
		 * data[0] - Data identifier: 'list', 'add', 'delete', or 'dim'.
		 * data[1] - ID of the corresponding list. If data[0] is 'list', the type of list ('comment', 'category', etc).
		 * data[2] - ID of the parent element of all inputs necessary for the request.
		 * data[3] - Hex color to be used in this request. If data[0] is 'dim', dim class.
		 * data[4] - Additional arguments in query syntax that are added to the request. Example: 'post_id=1234'.
		 *           If data[0] is 'dim', dim add color.
		 * data[5] - Only available if data[0] is 'dim', dim delete color.
		 * data[6] - Only available if data[0] is 'dim', additional arguments in query syntax that are added to the request.
		 *
		 * Result for Example 1:
		 * data[0] - delete
		 * data[1] - the-comment-list
		 * data[2] - comment-{comment_ID}
		 * data[3] - 66cc66
		 * data[4] - unspam=1
		 *
		 * @param  {HTMLElement} element The DOM element.
		 * @param  {string}      type    The type of data to look for: 'list', 'add', 'delete', or 'dim'.
		 * @returns {Array} Extracted list item data.
		 */
		parseData: function( element, type ) {
			var data = [], mlaListsData;

			try {
				mlaListsData = $( element ).data( 'wp-lists' ) || '';
				mlaListsData = mlaListsData.match( new RegExp( type + ':[\\S]+' ) );

				if ( mlaListsData ) {
					data = mlaListsData[0].split( ':' );
				}
			} catch ( error ) {}

			return data;
		},

		/**
		 * Calls a confirm callback to verify the action that is about to be performed.
		 *
		 * @param {HTMLElement} list     The DOM element.
		 * @param {object}      settings Settings for this list.
		 * @param {string}      action   The type of action to perform: 'add', 'delete', or 'dim'.
		 * @returns {object|boolean} Settings if confirmed, false if not.
		 */
		pre: function( list, settings, action ) {
			var $element, backgroundColor, confirmed;

			settings = $.extend( {}, this.mlaList.settings, {
				element: null,
				nonce:   0,
				target:  list.get( 0 )
			}, settings || {} );

			if ( typeof settings.confirm === 'function' ) {
				$element = $( '#' + settings.element, mla.mlaList.settings.context );

				if ( 'add' !== action ) {
					backgroundColor = $element.css( 'backgroundColor' );
					$element.css( 'backgroundColor', '#ff9966' );
				}

				confirmed = settings.confirm.call( this, list, settings, action, backgroundColor );

				if ( 'add' !== action ) {
					$element.css( 'backgroundColor', backgroundColor );
				}

				if ( ! confirmed ) {
					return false;
				}
			}

			return settings;
		},

		/**
		 * Adds an item to the list via AJAX.
		 *
		 * @param {HTMLElement} element  The DOM element.
		 * @param {object}      settings Settings for this list.
		 * @returns {boolean} Whether the item was added.
		 */
		ajaxAdd: function( element, settings ) {
			var list     = this,
				$element = $( element, mla.mlaList.settings.context ),
				data     = mla.mlaList.parseData( $element, 'add' ),
				formValues, formData, parsedResponse, returnedResponse;

			settings = settings || {};
			settings = mla.mlaList.pre.call( list, $element, settings, 'add' );

			settings.element  = data[2] || $element.prop( 'id' ) || settings.element || null;
			settings.addColor = data[3] ? '#' + data[3] : settings.addColor;

			if ( ! settings ) {
				return false;
			}

			if ( ! $element.is( '[id="' + settings.element + '-submit"]', mla.mlaList.settings.context ) ) {
				return ! mla.mlaList.add.call( list, $element, settings );
			}

			if ( ! settings.element ) {
				return true;
			}

			settings.action = 'add-' + settings.what;
			settings.nonce  = mla.mlaList.nonce( $element, settings );

			if ( ! wpAjax.validateForm( mla.mlaList.settings.context.selector + ' #' + settings.element ) ) {
				return false;
			}

			settings.data = $.param( $.extend( {
				_ajax_nonce: settings.nonce,
				action:      settings.action
			}, wpAjax.unserialize( data[4] || '' ) ) );

			formValues = $( '#' + settings.element + ' :input', mla.mlaList.settings.context ).not( '[name="_ajax_nonce"], [name="_wpnonce"], [name="action"]' );
			formData   = typeof formValues.fieldSerialize === 'function' ? formValues.fieldSerialize() : formValues.serialize();

			if ( formData ) {
				settings.data += '&' + formData;
			}

			if ( typeof settings.addBefore === 'function' ) {
				settings = settings.addBefore( settings );

				if ( ! settings ) {
					return true;
				}
			}

			if ( ! settings.data.match( /_ajax_nonce=[a-f0-9]+/ ) ) {
				return true;
			}

			settings.success = function( response ) {
				parsedResponse   = wpAjax.parseAjaxResponse( response, settings.response, settings.element );
				returnedResponse = response;

				if ( ! parsedResponse || parsedResponse.errors ) {
					return false;
				}

				if ( true === parsedResponse ) {
					return true;
				}

				// Add the new term, checked/selected, to the list
				$.each( parsedResponse.responses, function() {
					mla.mlaList.add.call( list, this.data, $.extend( {}, settings, { // this.firstChild.nodevalue
						position: this.position || 0,
						id:       this.id || 0,
						oldId:    this.oldId || null
					} ) );
				} );

				list.mlaList.recolor();
				$( list ).trigger( 'mlaListAddEnd', [ settings, list.mlaList ] );
				mla.mlaList.clear.call( list, '#' + settings.element );
			};

			// The addAfter function uses the supplemental data to update the "Parent Term" dropdown ontrol
			settings.complete = function( jqXHR, status ) {
				if ( typeof settings.addAfter === 'function' ) {
					settings.addAfter( returnedResponse, $.extend( {
						xml:    jqXHR,
						status: status,
						parsed: parsedResponse
					}, settings ) );
				}
			};

			$.ajax( settings );

			return false;
		},

		/**
		 * Returns the background color of the passed element.
		 *
		 * @param {jQuery|string} element Element to check.
		 * @returns {string} Background color value in HEX. Default: '#ffffff'.
		 */
		getColor: function( element ) {
			return $( element ).css( 'backgroundColor' ) || '#ffffff';
		},

		/**
		 * Adds a new item to all other taxonomy checklists on the page.
		 *
		 * @param {HTMLElement} element  A DOM element containing new item data.
		 * @param {object}      $list    jQuery object for the current list.
		 * @param {object}      settings Settings for this list.
		 */
		addToOtherLists: function( element, $list, settings ) {
			$( '.edit-fields-div' ).each( function() {
				var $thisDiv = $( this ),
					thisId = $thisDiv.attr( 'id' ),
					targetId = settings.element,
					$checklist, $element, old, position, reference;

				if ( targetId !== thisId ) {
					$checklist = $( '.' + $list.attr('id'), $thisDiv );
					$element = $( element );
					old = false;

					$( 'input', $element ).removeAttr( 'checked' )

					if ( settings.oldId ) {
						old = $( '#' + settings.what + '-' + settings.oldId, $checklist );
					}

					if ( settings.id && ( settings.id !== settings.oldId || ! old || ! old.length ) ) {
						$( '#' + settings.what + '-' + settings.id, $checklist ).remove();
					}

					if ( old && old.length ) {
						old.before( $element );
						old.remove();

					} else if ( isNaN( settings.position ) ) {
						position = 'after';

						if ( '-' === settings.position.substr( 0, 1 ) ) {
							settings.position = settings.position.substr( 1 );
							position = 'before';
						}

						reference = $checklist.find( '#' + settings.position );

						if ( 1 === reference.length ) {
							reference[position]( $element );
						} else {
							$checklist.append( $element );
						}

					} else if ( 'comment' !== settings.what || 0 === $( '#' + settings.element, $checklist ).length ) {
						if ( settings.position < 0 ) {
							$checklist.prepend( $element );
						} else {
							$checklist.append( $element );
						}
					}
				}
			});
		},

		/**
		 * Adds a new item to the enclosing list.
		 *
		 * @param {HTMLElement} element  A DOM element containing item data.
		 * @param {object}      settings Settings for this list.
		 * @returns {boolean} Whether the item was added.
		 */
		add: function( element, settings ) {
			var $list    = $( this ),
				$element = $( element ),
				old      = false,
				position, reference;

			if ( 'string' === typeof settings ) {
				settings = { what: settings };
			}

			settings = $.extend( { position: 0, id: 0, oldId: null }, this.mlaList.settings, settings );

			if ( ! $element.length || ! settings.what ) {
				return false;
			}

			mla.mlaList.addToOtherLists( element, $list, settings );

			if ( settings.oldId ) {
				old = $( '#' + settings.what + '-' + settings.oldId, settings.context );
			}

			if ( settings.id && ( settings.id !== settings.oldId || ! old || ! old.length ) ) {
				$( '#' + settings.what + '-' + settings.id, settings.context ).remove();
			}

			if ( old && old.length ) {
				old.before( $element );
				old.remove();

			} else if ( isNaN( settings.position ) ) {
				position = 'after';

				if ( '-' === settings.position.substr( 0, 1 ) ) {
					settings.position = settings.position.substr( 1 );
					position = 'before';
				}

				reference = $list.find( '#' + settings.position );

				if ( 1 === reference.length ) {
					reference[position]( $element );
				} else {
					$list.append( $element );
				}

			} else if ( 'comment' !== settings.what || 0 === $( '#' + settings.element, mla.mlaList.settings.context ).length ) {
				if ( settings.position < 0 ) {
					$list.prepend( $element );
				} else {
					$list.append( $element );
				}
			}

			if ( settings.alt ) {
				$element.toggleClass( settings.alt, ( $list.children( ':visible' ).index( $element[0] ) + settings.altOffset ) % 2 );
			}

			if ( 'none' !== settings.addColor ) {
				$element.css( 'backgroundColor', settings.addColor ).animate( { backgroundColor: mla.mlaList.getColor( $element ) }, {
					complete: function() {
						$( this ).css( 'backgroundColor', '' );
					}
				} );
			}

			// Add event handlers.
			$list.each( function( index, list ) {
				list.mlaList.process( $element );
			} );

			return $element;
		},

		/**
		 * Clears all input fields within the element passed.
		 *
		 * @param {string} elementId ID of the element to check, including leading #.
		 */
		clear: function( elementId ) {
			var list     = this,
				$element = $( elementId ),
				type, tagName;

			// Bail if we're within the list.
			if ( list.mlaList && $element.parents( '#' + list.id ).length ) {
				return;
			}

			// Check each input field.
			$element.find( ':input' ).each( function( index, input ) {

				// Bail if the form was marked to not to be cleared.
				if ( $( input ).parents( '.form-no-clear' ).length ) {
					return;
				}

				type    = input.type.toLowerCase();
				tagName = input.tagName.toLowerCase();

				if ( 'text' === type || 'password' === type || 'textarea' === tagName ) {
					input.value = '';

				} else if ( 'checkbox' === type || 'radio' === type ) {
					input.checked = false;

				} else if ( 'select' === tagName ) {
					input.selectedIndex = null;
				}
			} );
		},

		/**
		 * Registers event handlers to add, delete, and dim items.
		 *
		 * @param {string} elementId
		 */
		process: function( elementId ) {
			var list     = this,
				$element = $( elementId || document );

			$element.on( 'submit', 'form[data-wp-lists^="add:' + list.id + ':"]', function() {
				return list.mlaList.add( this );
			} );

			$element.on( 'click', 'a[data-wp-lists^="add:' + list.id + ':"], input[data-wp-lists^="add:' + list.id + ':"]', function() {
				return list.mlaList.add( this );
			} );
		},

		/**
		 * Updates list item background colors.
		 */
		recolor: function() {
			var list    = this,
				evenOdd = [':even', ':odd'],
				items;

			// Bail if there is no alternate class name specified.
			if ( ! list.mlaList.settings.alt ) {
				return;
			}

			items = $( '.list-item:visible', list );

			if ( ! items.length ) {
				items = $( list ).children( ':visible' );
			}

			if ( list.mlaList.settings.altOffset % 2 ) {
				evenOdd.reverse();
			}

			items.filter( evenOdd[0] ).addClass( list.mlaList.settings.alt ).end();
			items.filter( evenOdd[1] ).removeClass( list.mlaList.settings.alt );
		},

		/**
		 * Sets up `process()` and `recolor()` functions.
		 */
		init: function() {
			var $list = this;

			$list.mlaList.process = function( element ) {
				$list.each( function() {
					this.mlaList.process( element );
				} );
			};

			$list.mlaList.recolor = function() {
				$list.each( function() {
					this.mlaList.recolor();
				} );
			};
		}
	};

	var functions = {
		add:     'ajaxAdd',
		process: 'process',
		recolor: 'recolor'
	};

	/**
	 * Initializes mlaList object.
	 *
	 * @param {Object}           settings
	 * @param {string}           settings.url         URL for ajax calls. Default: ajaxurl.
	 * @param {string}           settings.type        The HTTP method to use for Ajax requests. Default: 'POST'.
	 * @param {string}           settings.response    ID of the element the parsed ajax response will be stored in.
	 *                                                Default: 'ajax-response'.
	 *
	 * @param {string}           settings.what        Default: ''.
	 * @param {string}           settings.alt         CSS class name for alternate styling. Default: 'alternate'.
	 * @param {number}           settings.altOffset   Offset to start alternate styling from. Default: 0.
	 * @param {string}           settings.addColor    Hex code or 'none' to disable animation. Default: '#ffff33'.
	 *
	 * @param {mlaList~confirm}   settings.confirm     Callback that's run before a request is made. Default: null.
	 * @param {mlaList~addBefore} settings.addBefore   Callback that's run before an item gets added to the list.
	 *                                                Default: null.
	 * @param {mlaList~addAfter}  settings.addAfter    Callback that's run after an item got added to the list.
	 *                                                Default: null.
	 * @returns {$.fn} mlaList API function.
	 */
	$.fn.mlaList = function( settings ) {
		this.each( function( index, list ) {
			list.mlaList = {
				settings: $.extend( {}, mla.mlaList.settings, { what: mla.mlaList.parseData( list, 'list' )[1] || '' }, settings )
			};

			$.each( functions, function( func, callback ) {
				list.mlaList[func] = function( element, setting ) {
					return mla.mlaList[callback].call( list, element, setting );
				};
			} );
		} );

		mla.mlaList.init.call( this );
		this.mlaList.process( settings.context );

		return this;
	};
})( jQuery );
