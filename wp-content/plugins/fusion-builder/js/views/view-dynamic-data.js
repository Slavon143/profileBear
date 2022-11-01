/* global FusionPageBuilderApp, fusionDynamicData */

var FusionPageBuilder = FusionPageBuilder || {};

FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionDynamicData = {
	optionDynamicData: function( $element ) {
		var self = this;

		$element  = $element || this.$el;

		$element.find( '[data-dynamic="true"] .fusion-dynamic-content' ).each( function() {
			self.initEditDynamic( jQuery( this ) );
		} );
	},

	initEditDynamic: function( $targetEl, open ) {

		var dynamicData = this.dynamicParams.getAll(),
			self        = this,
			repeater    = FusionPageBuilder.template( jQuery( '#fusion-app-repeater-fields' ).html() ),
			param       = $targetEl.closest( '.fusion-builder-option' ).attr( 'data-option-id' ),
			options     = FusionPageBuilderApp.dynamicValues.getOptions(),
			values      = 'object' === typeof dynamicData && 'object' === typeof dynamicData[ param ] ? dynamicData[ param ] : { data: undefined },
			dynamic     = values && 'object' === typeof options[ values.data ] ? options[ values.data ] : false,
			fields      = dynamic ? dynamic.fields : false,
			label       = dynamic && 'string' === typeof dynamic.label ? dynamic.label : values.data,
			$html       = '',
			$fields     = $targetEl.find( '.dynamic-param-fields' ),
			supported   = jQuery.extend( true, {}, fusionDynamicData.commonDynamicFields ),
			excludes    = 'object' === typeof dynamic.exclude ? _.values( dynamic.exclude ) : false;

		if ( 'object' !== typeof dynamicData[ param ] ) {
			return;
		}

		if ( excludes && 'object' === typeof supported ) {
			_.each( supported, function( supportField, supportId ) {
				if ( -1 !== _.indexOf( excludes, supportId ) ) {
					delete supported[ supportId ];
				}
			} );
		}

		if ( 'object' === typeof supported && ! _.isEmpty( supported ) ) {
			if ( 'object' === typeof fields ) {
				fields = _.extend( fields, supported );
			} else {
				fields = supported;
			}
		}

		// Update the editable fields.
		$fields.empty();
		if ( fields ) {
			_.each( fields, function( field, id ) {
				var value    = values[ id ],
					attributes = {
						field: field,
						value: value
					};

				$html += jQuery( repeater( attributes ) ).html();
			} );

			$fields.append( $html );
		}

		// Update the title, id and ajax attribute.
		if ( dynamic ) {
			$targetEl.find( '.dynamic-title h3' ).text( label );
			$targetEl.find( '.dynamic-wrapper' ).attr( 'data-id', values.data );
		}

		// Prevent duplicate listeners.
		$targetEl.off( 'click' );

		// Listener for open and close toggle.
		$targetEl.on( 'click', '.dynamic-title', function() {
			jQuery( this ).parent().find( '.dynamic-param-fields' ).slideToggle( 300 );

			if ( jQuery( this ).find( '.dynamic-toggle-icon' ).hasClass( 'fusiona-pen' ) ) {
				jQuery( this ).find( '.dynamic-toggle-icon' ).removeClass( 'fusiona-pen' ).addClass( 'fusiona-minus' );
			} else {
				jQuery( this ).find( '.dynamic-toggle-icon' ).removeClass( 'fusiona-minus' ).addClass( 'fusiona-pen' );
			}
		} );

		// Listener for removing the dynamic data.
		$targetEl.on( 'click', '.dynamic-remove.fusiona-trash-o', function( event ) {
			event.preventDefault();
			self.removeDynamicData( $targetEl );
		} );

		// Init the editable sub options.
		if ( 'function' === typeof this.optionInit ) {
			this.optionInit( $targetEl );
		}

		// If its a newly added one, lets open it.
		if ( 'undefined' !== typeof open && open ) {
			$targetEl.find( '.dynamic-param-fields' ).show();
			$targetEl.find( '.dynamic-toggle-icon' ).removeClass( 'fusiona-pen' ).addClass( 'fusiona-minus' );
		}
	},

	removeDynamicData: function( $targetEl ) {
		var param = $targetEl.closest( '.fusion-builder-option' ).attr( 'data-option-id' );

		if ( 'undefined' !== typeof this.dynamicParams ) {
			this.dynamicParams.removeParam( param );
		}
	},

	/**
	 * Sets dynamic data param value.
	 *
	 * @since 2.0.0
	 * @param {Object} $option - jQuery object.
	 * @param {String} param - Parameter ID.
	 * @param {Mixed} value - Value of this option.
	 * @return {void}
	 */
	setDynamicParamValue: function( $option, subParam, value ) {
		var param    = $option.parent().closest( '.fusion-builder-option' ).attr( 'data-option-id' );

		if ( 'undefined' !== typeof this.dynamicParams ) {
			this.dynamicParams.updateParam( param, subParam, value );
		}
	}
};
