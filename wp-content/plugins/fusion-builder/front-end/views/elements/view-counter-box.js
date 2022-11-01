/* global FusionPageBuilderElements, fusionAllElements */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Counter box child View
		FusionPageBuilder.fusion_counter_box = FusionPageBuilder.ChildElementView.extend( {

			/**
			 * Runs during render() call.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			onRender: function() {
				if ( 'undefined' !== typeof this.model.attributes.selectors ) {
					this.model.attributes.selectors[ 'class' ] += ' ' + this.className;
					this.model.attributes.selectors[ 'class' ] += ( 'video' === this.model.attributes.params.type ) ? ' video' : ' image';
					this.setElementAttributes( this.$el, this.model.attributes.selectors );
				}
			},

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			afterPatch: function() {

				if ( 'undefined' !== typeof this.model.attributes.selectors ) {
					this.model.attributes.selectors[ 'class' ] += ' ' + this.className;
					this.model.attributes.selectors[ 'class' ] += ( 'video' === this.model.attributes.params.type ) ? ' video' : ' image';
					this.setElementAttributes( this.$el, this.model.attributes.selectors );
				}

				this._refreshJs();
			},

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var counterBoxContainer,
					elementContent  = atts.values.element_content,
					parent          = this.model.get( 'parent' ),
					parentModel     = FusionPageBuilderElements.find( function( model ) {
						return model.get( 'cid' ) == parent;
					} ),
					parentValues    = jQuery.extend( true, {}, fusionAllElements.fusion_counters_box.defaults, _.fusionCleanParameters( parentModel.get( 'params' ) ) ),
					counterBoxShortcodeContent,
					counterWrapper;

				// Validate values and extras.
				this.validateValues( atts.values, atts.params );

				this.validateParentValues( parentValues );

				counterBoxContainer        = this.buildContainerAtts( atts.values, parentValues );
				counterWrapper             = this.buildCounterWrapper( atts.values, parentValues );
				counterBoxShortcodeContent = this.buildContentAttr( parentValues );
				this.setSelectors( atts.values, parentValues );

				// Reset attribute objet.
				atts = {};

				// Create attribute objects.
				atts.counterBoxContainer        = counterBoxContainer;
				atts.counterWrapper             = counterWrapper;
				atts.counterBoxShortcodeContent = counterBoxShortcodeContent;

				// Any extras that need passed on.
				atts.cid    = this.model.get( 'cid' );
				atts.parent = parent;
				atts.output = elementContent;

				return atts;
			},

			/**
			 * Modifies values.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @param {Object} params - The parameters.
			 * @return {void}
			 */
			validateValues: function( values, params ) {
				values = jQuery.extend( true, {}, fusionAllElements.fusion_counter_box.defaults, _.fusionCleanParameters( params ) );

				values.value = values.value.replace( ',', '.' );
				values[ 'float' ] = values.value.split( '.' );
				if ( 'undefined' !== typeof values[ 'float' ][ 1 ] ) {
					values.decimals = values[ 'float' ][ 1 ].length;
				}
			},

			/**
			 * Modifies parent values.
			 *
			 * @since 2.0
			 * @param {Object} parentValues - The parent values.
			 * @return {void}
			 */
			validateParentValues: function( parentValues ) {
				parentValues.title_size = _.fusionValidateAttrValue( parentValues.title_size, '' );
				parentValues.icon_size  = _.fusionValidateAttrValue( parentValues.icon_size, '' );
				parentValues.body_size  = _.fusionValidateAttrValue( parentValues.body_size, '' );
				parentValues.columns    = Math.min( 6, parentValues.columns );
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @param {Object} parentValues - The parent element values.
			 * @return {Object}
			 */
			buildContainerAtts: function( values, parentValues ) {
				var counterBoxContainer = {
					class: 'counter-box-container'
				};

				counterBoxContainer.style = 'border: 1px solid ' + parentValues.border_color + ';';

				return counterBoxContainer;
			},

			/**
			 * Builds the HTML for the wrapper element.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @param {Object} parentValues - The parent element values.
			 * @return {string}
			 */
			buildCounterWrapper: function( values, parentValues ) {
				var unitOutput = values.unit ? '<span class="unit">' + values.unit + '</span>' : '',
					initValue  = ( 'up' === values.direction ) ? 0 : values.value,
					iconOutput = '',
					decimals   = 0,
					counter,
					selectedIcon,
					counterBoxShortcodeCounter,
					counterBoxShortcodeCounterContainer,
					counterBoxShortcodeIcon,
					decimalsValue;

				values.value  = values.value.replace( ',', '.' );
				decimalsValue = values.value.split( '.' );

				if ( 'undefined' !== typeof decimalsValue[ 1 ] ) {
					decimals = decimalsValue[ 1 ].length;
				}

				// counterBoxShortcodeCounter Attributes.
				counterBoxShortcodeCounter = {
					class: 'display-counter',
					'data-value': values.value,
					'data-delimiter': values.delimiter,
					'data-direction': values.direction,
					'data-decimals': decimals
				};

				// Make value editable.
				counterBoxShortcodeCounter = _.fusionInlineEditor( {
					cid: this.model.get( 'cid' ),
					param: 'value',
					toolbar: false,
					'disable-return': true,
					'disable-extra-spaces': true
				}, counterBoxShortcodeCounter );

				counter = '<span ' + _.fusionGetAttributes( counterBoxShortcodeCounter ) + '>' + initValue + '</span>';

				if ( values.icon || parentValues.icon ) {
					selectedIcon = ( values.icon ) ? values.icon : parentValues.icon;
					counterBoxShortcodeIcon = {
						class: 'counter-box-icon fontawesome-icon ' + _.fusionFontAwesome( selectedIcon ),
						style: 'font-size:' + parentValues.icon_size + 'px;',
						'aria-hidden': 'true'
					};
					iconOutput = '<i ' + _.fusionGetAttributes( counterBoxShortcodeIcon ) + '></i>';
				}

				counter = ( 'prefix' === values.unit_pos ) ? iconOutput + unitOutput + counter : iconOutput + counter + unitOutput;

				// counterBoxShortcodeCounterContainer Atributes.
				counterBoxShortcodeCounterContainer = {
					class: 'content-box-percentage content-box-counter',
					style: 'color:' + parentValues.color + ';font-size:' + parentValues.title_size + 'px;line-height:normal;'
				};

				return '<div ' + _.fusionGetAttributes( counterBoxShortcodeCounterContainer ) + '>' + counter + '</div>';
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} parentValues - The parent element values.
			 * @return {Object}
			 */
			buildContentAttr: function( parentValues ) {
				var counterBoxShortcodeContent = {
					class: 'counter-box-content',
					style: 'color:' + parentValues.body_color + ';font-size:' + parentValues.body_size + 'px;'
				};

				// Make content editable.
				counterBoxShortcodeContent = _.fusionInlineEditor( {
					cid: this.model.get( 'cid' ),
					param: 'element_content',
					toolbar: 'simple',
					'disable-return': true,
					'disable-extra-spaces': true
				}, counterBoxShortcodeContent );

				return counterBoxShortcodeContent;
			},

			/**
			 * Sets selectors in the model.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @param {Object} parentValues - The parent element values.
			 * @return {void}
			 */
			setSelectors: function( values, parentValues ) {
				var columns             = 1,
					counterBoxShortcode = {},
					animations;

				if ( 'undefined' !== typeof parentValues.columns && '' !== parentValues.columns && 0 !== parentValues.columns ) {
					columns = 12 / parentValues.columns;
				}

				counterBoxShortcode[ 'class' ] = 'fusion-counter-box fusion-column col-counter-box counter-box-wrapper col-lg-' + columns + ' col-md-' + columns + ' col-sm-' + columns;

				if ( '5' === parentValues.columns || 5 === parentValues.columns ) {
					counterBoxShortcode[ 'class' ] = 'fusion-counter-box fusion-column col-counter-box counter-box-wrapper col-lg-2 col-md-2 col-sm-2';
				}

				if ( 'yes' === parentValues.icon_top ) {
					counterBoxShortcode[ 'class' ] += ' fusion-counter-box-icon-top';
				}

				if ( '' !== values[ 'class' ] ) {
					counterBoxShortcode[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					counterBoxShortcode.id = values.id;
				}

				if ( parentValues.animation_type ) {
					animations = _.fusionGetAnimations( {
						offset: parentValues.animation_offset
					} );

					counterBoxShortcode = jQuery.extend( counterBoxShortcode, animations );

					counterBoxShortcode[ 'class' ] += ' ' + counterBoxShortcode.animation_class;
					delete counterBoxShortcode.animation_class;
				}

				this.model.set( 'selectors', counterBoxShortcode );
			}
		} );
	} );
}( jQuery ) );
