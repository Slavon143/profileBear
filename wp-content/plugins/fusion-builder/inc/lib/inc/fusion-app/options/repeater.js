/* global FusionApp, fusionAllElements, FusionPageBuilderApp */
var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionRepeaterField = {

	optionRepeater: function( context ) {
		var $repeater = this.$el.find( '.fusion-builder-option.repeater' ),
			self      = this;

		// Set context to overall view for easier access.
		this.context = context;

		this.repeaterRowId = 'undefined' === typeof this.repeaterRowId ? 0 : this.repeaterRowId;

		if ( $repeater.length ) {
			$repeater.each( function() {
				self.initRepeater( jQuery( this ) );
			} );
		}

		jQuery( document ).on( 'fusion-init-repeater-in-toggle', function ( e, obj ) { // eslint-disable-line no-unused-vars
			$repeater = jQuery( obj.$toggle ).find( '.fusion-builder-option.repeater' );
			if ( $repeater.length ) {
				$repeater.each( function() {
					self.initRepeater( jQuery( this ), obj.option );
				} );
			}
		} );
	},

	/**
	 * Init the option.
	 *
	 * @since 2.0.0
	 * @param {Object} $repeater - jQuery object of the DOM element.
	 * @return {void}
	 */
	initRepeater: function( $repeater, options ) {
		var self       = this,
			param      = $repeater.data( 'option-id' ),
			$target    = $repeater.find( '.repeater-rows' ),
			$option    = $repeater.find( '.fusion-repeater-value' ),
			rows       = false,
			option,
			fields,
			attributes,
			params,
			values,
			rowTitle;

		// When doing a search we need to set the context correctly.
		if ( 'search' === this.context ) {
			this.context = jQuery( '.fusion-sidebar-section[data-context]' ).data( 'context' );
		}

		switch ( this.context ) {

		case 'TO':
		case 'FBE':

			options = options || this.options;
			option   = options[ param ];
			fields   = option.fields;
			values   = FusionApp.settings[ param ];

			if ( ! _.isEmpty( values ) ) {
				values = self.reduxDataCorrect( values );
				rows   = true;
			}

			break;

		case 'PO':

			options = options || this.options;
			option   = options[ param ];
			fields   = option.fields;
			values   = FusionApp.data.postMeta._fusion[ param ];

			if ( ! _.isEmpty( values ) ) {
				if ( 'string' === typeof values ) {
					values = JSON.parse( values );
					try {
						values = JSON.parse( values );
					} catch ( e ) {
						console.warn( 'Something went wrong! Error triggered - ' + e );
					}
				}
				rows   = true;
			}
			break;

		default:
			options = options || fusionAllElements[ this.model.get( 'element_type' ) ].params;
			option     = options[ param ];
			fields     = 'undefined' !== typeof option ? option.fields : {};
			attributes = jQuery.extend( true, {}, this.model.attributes );

			if ( 'function' === typeof this.filterAttributes ) {
				attributes = this.filterAttributes( attributes );
			}

			params     = attributes.params;
			values     = 'undefined' !== typeof params[ param ] ? params[ param ] : '';

			if ( 'string' === typeof values && '' !== values ) {
				values = self.getRepeaterValue( false, values );
				rows   = true;
			}

			break;
		}

		// Create the rows for existing values.
		if ( 'object' === typeof values && rows ) {
			_.each( values, function( field, index ) {
				rowTitle = 'undefined' !== typeof values[ index ][ option.bind_title ] && values[ index ][ option.bind_title ] ? values[ index ][ option.bind_title ] : '';

				// If select field use label of value.
				if ( '' !== rowTitle && 'object' === typeof option.fields[ option.bind_title ] && 'select' === option.fields[ option.bind_title ].type && 'object' === typeof option.fields[ option.bind_title ].choices ) {
					rowTitle = option.fields[ option.bind_title ].choices[ rowTitle ];
				}
				if ( '' === rowTitle && 'undefined' !== typeof option.row_title ) {
					rowTitle = option.row_title;
				}

				self.createRepeaterRow( fields, values[ index ], $target, rowTitle );
			} );
		} else {
			rowTitle = 'object' === typeof values && 'undefined' !== typeof values[ option.bind_title ] && values[ option.bind_title ] ? values[ option.bind_title ] : '';
			if ( '' === rowTitle && 'undefined' !== typeof option.row_title ) {
				rowTitle = option.row_title;
			}
			self.createRepeaterRow( fields, {}, $target, rowTitle );
		}

		// Repeater row add click event.
		$repeater.on( 'click', '.repeater-row-add', function( event ) {
			var newRowTitle = 'undefined' !== typeof option.row_title ? option.row_title : false;
			event.preventDefault();
			self.createRepeaterRow( fields, {}, $target, newRowTitle );
		} );

		// Repeater row remove click event.
		$repeater.on( 'click', '.repeater-row-remove.fusiona-trash-o', function( event ) {
			var rowIndex = jQuery( this ).closest( '.repeater-row' ).index();

			event.preventDefault();

			self.removeRepeaterRowData( $option, rowIndex );

			jQuery( this ).closest( '.repeater-row' ).remove();
		} );

		$repeater.on( 'click', '.repeater-title', function() {
			jQuery( this ).parent().find( '.repeater-fields' ).slideToggle( 300 );

			if ( jQuery( this ).find( '.repeater-toggle-icon' ).hasClass( 'fusiona-pen' ) ) {
				jQuery( this ).find( '.repeater-toggle-icon' ).removeClass( 'fusiona-pen' ).addClass( 'fusiona-minus' );
			} else {
				jQuery( this ).find( '.repeater-toggle-icon' ).removeClass( 'fusiona-minus' ).addClass( 'fusiona-pen' );
			}
		} );

		$repeater.on( 'change', '.repeater-row [name=' + option.bind_title + ']', function() {
			var title = jQuery( this ).hasClass( 'fusion-select-option' ) || jQuery( this ).hasClass( 'fusion-select-option-value' ) ? jQuery( this ).closest( '.fusion-builder-option' ).find( '.fusion-select-label[for=' + jQuery( this ).attr( 'id' ) + '], .fusion-select-label[data-value="' + jQuery( this ).val() + '"]' ).html() : jQuery( this ).val();
			jQuery( this ).closest( '.repeater-row' ).find( '> .repeater-title > h3' ).html( title );
		} );

		$repeater.sortable( {
			handle: '.repeater-title',
			items: '.repeater-row',
			cursor: 'move',
			cancel: '.repeater-row-remove.fusiona-trash-o',
			start: function( event, ui ) {
				jQuery( this ).attr( 'data-previndex', ui.item.index() );
			},
			update: function( event, ui ) {
				var newIndex = ui.item.index(),
					oldIndex = parseInt( jQuery( this ).attr( 'data-previndex' ), 10 );

				jQuery( this ).removeAttr( 'data-previndex' );

				self.orderRepeaterData( $option, oldIndex, newIndex );
			}
		} );

	},

	/**
	 * Creates a new row for a specific repeater.
	 *
	 * @since 2.0.0
	 * @param {Object} fields - The fields.
	 * @param {Object} values - The values.
	 * @param {Object} $target - jQuery element.
	 * @param {string} rowTitle - The title for this row.
	 * @return {void}
	 */
	createRepeaterRow: function( fields, values, $target, rowTitle ) {
		var self       = this,
			$html      = '',
			attributes = {},
			repeater   = FusionPageBuilder.template( jQuery( '#fusion-app-repeater-fields' ).html() ),
			depFields  = {},
			value,
			optionId;

		rowTitle   = 'undefined' !== typeof rowTitle && rowTitle ? rowTitle : 'Repeater Row';

		$html += '<div class="repeater-row">';
		$html += '<div class="repeater-title">';
		$html += '<span class="repeater-toggle-icon fusiona-pen"></span>';
		$html += '<h3>' + rowTitle + '</h3>';
		$html += '<span class="repeater-row-remove fusiona-trash-o"></span>';
		$html += '</div>';
		$html += '<ul class="repeater-fields">';

		this.repeaterRowId++;

		_.each( fields, function( field ) {
			optionId              = 'builder' === self.context ? field.param_name : field.id;
			value                 = values[ optionId ];
			depFields[ optionId ] = field;

			attributes = {
				field: field,
				value: value,
				context: self.context,
				rowId: self.repeaterRowId
			};
			$html += jQuery( repeater( attributes ) ).html();
		} );

		$html += '</ul>';
		$html += '</div>';

		$target.append( $html );

		if ( _.isEmpty( values ) ) {
			this.addRepeaterRowData( $target, fields );
		}

		if ( 'function' === typeof this.initOptions ) {
			this.initOptions( $target.children( 'div:last-child' ) );
		}

		// Check option dependencies
		if ( 'TO' !== this.context && 'FBE' !== this.context && 'PO' !== this.context && 'undefined' !== typeof this.model && 'undefined' !== typeof this.model.get ) {
			new FusionPageBuilder.Dependencies( fusionAllElements[ this.model.get( 'element_type' ) ].params, this, $target.children( 'div:last-child' ), depFields, this.$el );
		} else {
			new FusionPageBuilder.Dependencies( {}, this, $target.children( 'div:last-child' ), depFields, this.$el );
		}
	},

	/**
	 * Get repeater value in correct format.
	 *
	 * @since 2.0.0
	 * @param {Object} $option - jQuery element.
	 * @param {Array|string} values - The values.
	 * @return {Object} - Values in correct format to be read.
	 */
	getRepeaterValue: function( $option, values ) {
		var self = this;

		values = 'undefined' === typeof values ? $option.val() : values;

		if ( 'string' === typeof values && '' !== values ) {
			switch ( this.context ) {

			case 'TO':
			case 'FBE':
				try {
					values = JSON.parse( values );
					if ( ! _.isEmpty( values ) ) {
						values = self.reduxDataCorrect( values );
					}
				} catch ( e ) {
					console.warn( 'Something went wrong! Error triggered - ' + e );
				}
				break;

			case 'PO':
				try {
					values = JSON.parse( values );
					if ( 'function' !== typeof values.splice ) {
						values = Object.values( values );
					}
				} catch ( e ) {
					console.warn( 'Something went wrong! Error triggered - ' + e );
				}
				break;

			default:
				try {
					values = FusionPageBuilderApp.base64Decode( values );
					values = _.unescape( values );
					values = JSON.parse( values );
				} catch ( e ) {
					console.warn( 'Something went wrong! Error triggered - ' + e );
				}
				break;
			}
		}

		if ( '' === values || _.isEmpty( values ) ) {
			values = [];
		}

		return values;
	},

	/**
	 * Adds a new row of data to the repeater data.
	 *
	 * @since 2.0.0
	 * @param {Object} $repeaters - jQuery object.
	 * @param {Object} fields - The fields.
	 * @return {void}
	 */
	addRepeaterRowData: function( $repeaters, fields ) {
		var self      = this,
			newIndex  = $repeaters.find( '.repeater-row' ).last().index(),
			$option   = $repeaters.closest( '.repeater' ).find( '.fusion-repeater-value' ),
			values    = this.getRepeaterValue( $option ),
			rowValues = {},
			defaultVal,
			paramId;

		if ( 'builder' !== this.context && 'PO' !== this.context ) {
			rowValues.fusionredux_repeater_data = {
				title: ''
			};
		}

		// Get defaults for each field.
		_.each( fields, function( field ) {
			paramId    = 'builder' === self.context ? field.param_name : field.id;
			defaultVal = 'undefined' !== typeof field[ 'default' ] && 'builder' !== self.context && ( 'select' === field.type || 'radio-buttonset' === field.type ) ? field[ 'default' ] : '';
			rowValues[ paramId ] = defaultVal;
		} );

		// Set values.
		values[ newIndex ] = rowValues;
		this.updateRepeaterValues( $option, values );
	},

	/**
	 * Removes a specific row of data from repeater object.
	 *
	 * @since 2.0.0
	 * @param {Object} $option - jQuery object.
	 * @param {number} index - Ror index.
	 * @return {void}
	 */
	removeRepeaterRowData: function( $option, index ) {
		var values = this.getRepeaterValue( $option );

		if ( 'undefined' !== typeof values[ index ] ) {
			values.splice( index, 1 );
			this.updateRepeaterValues( $option, values );
		}
	},

	/**
	 * Changes the order of a rows in repeater data (sortable).
	 *
	 * @since 2.0.0
	 * @param {Object} $option - jQuery object.
	 * @param {number} oldIndex - The old row index.
	 * @param {number} newIndex - The new row index.
	 * @return {void}
	 */
	orderRepeaterData: function( $option, oldIndex, newIndex ) {
		var values  = this.getRepeaterValue( $option ),
			rowData = values[ oldIndex ];

		if ( 'undefined' !== typeof rowData ) {
			values.splice( oldIndex, 1 );
			values.splice( newIndex, 0, rowData );
			this.updateRepeaterValues( $option, values );
		} else {
			console.warn( 'Something went wrong! Old index data not found.' );
		}
	},

	/**
	 * Changes a specific row parameter value in repeater data.
	 *
	 * @since 2.0.0
	 * @param {Object} $option - jQuery object.
	 * @param {sring} param - The parameter we're editing.
	 * @param {number} index - The row index.
	 * @param {mixed} value - The value.
	 * @return {void}
	 */
	setRepeaterValue: function( $option, param, index, value ) {
		var values  = this.getRepeaterValue( $option );

		if ( 'undefined' !== typeof values[ index ] ) {
			values[ index ][ param ] = value;
			this.updateRepeaterValues( $option, values );
		}
	},

	/**
	 * Updates the repeater data on hidden input in correct format
	 * and trigger a change event to update.
	 *
	 * @since 2.0.0
	 * @return {void}
	 */
	updateRepeaterValues: function( $option, values ) {

		if ( '' !== values && ! _.isEmpty( values ) ) {
			switch ( this.context ) {
			case 'TO':
			case 'FBE':
				values = this.reduxDataReverse( values );
				values = JSON.stringify( values );
				break;

			case 'PO':
				values = JSON.stringify( values );
				break;

			default:
				values = JSON.stringify( values );
				values = FusionPageBuilderApp.base64Encode( values );
				break;
			}
		}
		$option.val( values ).trigger( 'change' );
	},

	/**
	 * Changes the redux data format to more logical format which is used
	 * in the builder version of repeater.
	 *
	 * @since 2.0.0
	 * @return {Object} Values in builder type readable format
	 */
	reduxDataCorrect: function( values ) {
		var newFormat = [];

		_.each( values, function( param, paramName ) {
			_.each( param, function( value, index ) {
				if ( 'undefined' === typeof newFormat[ index ] ) {
					newFormat[ index ] = {};
				}
				newFormat[ index ][ paramName ] = value;
			} );
		} );

		return newFormat;
	},

	/**
	 * Changes from builder data structure back to redux.
	 *
	 * @since 2.0.0
	 * @return {Object} Values in redux format
	 */
	reduxDataReverse: function( values ) {
		var oldFormat = {};

		_.each( values, function( param ) {
			_.each( param, function( value, paramName ) {
				if ( 'undefined' === typeof oldFormat[ paramName ] ) {
					oldFormat[ paramName ] = [];
				}
				oldFormat[ paramName ].push( value );
			} );
		} );
		return oldFormat;
	}
};
