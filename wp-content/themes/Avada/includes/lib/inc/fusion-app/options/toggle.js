/* global FusionApp, fusionAllElements, fusionMailchimpMapOption, fusionHubSpotMapOption */
var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionToggleField = {

	optionToggle: function( context ) {
		var $toggle = this.$el.find( '.fusion-builder-option.toggle' ),
			self      = this;

		// Set context to overall view for easier access.
		this.context = context;

		this.repeaterRowId = 'undefined' === typeof this.repeaterRowId ? 0 : this.repeaterRowId;

		if ( $toggle.length ) {
			$toggle.each( function() {
				self.initToggle( jQuery( this ), context );
			} );
		}
	},

	/**
	 * Init the option.
	 *
	 * @since 2.0.0
	 * @param {Object} $toggle - jQuery object of the DOM element.
	 * @return {void}
	 */
	initToggle: function( $toggle ) {
		var self       = this,
			param      = $toggle.data( 'option-id' ),
			$target    = $toggle.find( '.toggle-wrapper' ),
			option,
			values,
			params,
			attributes,
			fields;


		switch ( this.context ) {

		case 'TO':
		case 'FBE':

			option   = this.options[ param ];
			fields   = option.fields;
			values   = FusionApp.settings;

			break;

		case 'PO':

			option   = this.options[ param ];
			fields   = option.fields;
			values   = FusionApp.data.postMeta._fusion;

			break;

		default:

			option     = fusionAllElements[ this.model.get( 'element_type' ) ].params[ param ];
			fields     = 'undefined' !== typeof option ? option.fields : {};
			attributes = jQuery.extend( true, {}, this.model.attributes );

			if ( 'function' === typeof this.filterAttributes ) {
				attributes = this.filterAttributes( attributes );
			}

			params     = attributes.params;
			values     = 'undefined' !== typeof params ? params : '';


			break;
		}
		self.createToggleRow( fields, values, $target, option.row_title );

		$toggle.on( 'click', '.toggle-title', function() {
			jQuery( this ).parent().find( '.toggle-fields' ).slideToggle( 300 );

			if ( jQuery( this ).find( '.toggle-toggle-icon' ).hasClass( 'fusiona-pen' ) ) {
				jQuery( this ).find( '.toggle-toggle-icon' ).removeClass( 'fusiona-pen' ).addClass( 'fusiona-minus' );
			} else {
				jQuery( this ).find( '.toggle-toggle-icon' ).removeClass( 'fusiona-minus' ).addClass( 'fusiona-pen' );
			}
		} );

		$toggle.one( 'click', '.toggle-title', function() {
			// Init repeaters if exists.
			const $repeater = $toggle.find( '.fusion-builder-option.repeater' );
			if ( $repeater.length && !this.repeaterInitialized ) {
				jQuery( document ).trigger( 'fusion-init-repeater-in-toggle', { $toggle, option: option.fields } );
				this.repeaterInitialized = true;
			}

			//init mailchimp map inside toggle.
			if ( $target.find( '.mailchimp_map' ) ) {
				new fusionMailchimpMapOption( $target );
			}

			//init hubspot map inside toggle.
			if ( $target.find( '.hubspot_map' ) ) {
				new fusionHubSpotMapOption( $target );
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
	createToggleRow: function( fields, values, $target, rowTitle ) {
		var self       = this,
			$html      = '',
			attributes = {},
			repeater   = FusionPageBuilder.template( jQuery( '#fusion-app-repeater-fields' ).html() ),
			depFields  = {},
			value,
			optionId;

		rowTitle   = 'undefined' !== typeof rowTitle && rowTitle ? rowTitle : 'Toggle Row';

		$html += '<div class="toggle-row">';
		$html += '<div class="toggle-title">';
		$html += '<span class="toggle-toggle-icon fusiona-pen"></span>';
		$html += '<h3>' + rowTitle + '</h3>';
		$html += '<span></span>';
		$html += '</div>';
		$html += '<ul class="toggle-fields" style="display:none;">';

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

		if ( 'function' === typeof this.initOptions ) {
			this.initOptions( $target.children( 'div:last-child' ) );
		}

		// Check option dependencies
		if ( 'TO' !== this.context && 'FBE' !== this.context && 'PO' !== this.context && 'undefined' !== typeof this.model && 'undefined' !== typeof this.model.get ) {
			new FusionPageBuilder.Dependencies( fusionAllElements[ this.model.get( 'element_type' ) ].params, this, $target.children( 'div:last-child' ), depFields, this.$el );
		} else {
			new FusionPageBuilder.Dependencies( {}, this, $target.children( 'div:last-child' ), depFields, this.$el );
		}
	}
};
