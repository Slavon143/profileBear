/* global FusionPageBuilderApp, fusionAllElements */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Tooltip View
		FusionPageBuilder.fusion_popover = FusionPageBuilder.ElementView.extend( {

			/**
			 * Runs before view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			beforePatch: function() {
				var $popover = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( this.$el.find( '[data-toggle~="popover"]' ) );

				$popover.removeData();
				$popover.remove();
			},

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			afterPatch: function() {
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
				var attributes = {};

				attributes.attr    = this.computeAttr( atts.values );
				attributes.styles  = this.computeStyles( atts.values );
				attributes.cid     = this.model.get( 'cid' );
				attributes.parent  = this.model.get( 'parent' );
				attributes.inline  = 'undefined' !== typeof atts.inlineElement;
				attributes.content = atts.values.element_content;
				attributes.label   = window.fusionAllElements[ this.model.get( 'element_type' ) ].name;
				attributes.icon    = window.fusionAllElements[ this.model.get( 'element_type' ) ].icon;
				attributes.popover = atts.values.popover;
				return attributes;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			computeAttr: function( values ) {
				var cid              = this.model.get( 'cid' ),
					atts             = {
						class: 'fusion-popover popover-' + cid
					},
					popoverContent   = values.content;

				if ( 'default' === values.placement ) {
					values.placement = fusionAllElements.fusion_popover.defaults.placement;
				}

				if ( '' !== values[ 'class' ] ) {
					atts[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					atts.id = values.id;
				}

				try {
					if ( popoverContent && '' !== popoverContent && FusionPageBuilderApp.base64Encode( FusionPageBuilderApp.base64Decode( popoverContent ) ) === popoverContent ) {
						popoverContent = FusionPageBuilderApp.base64Decode( popoverContent );
					}
				} catch ( error ) {
					console.log( error ); // jshint ignore:line
				}

				atts[ 'data-animation' ] = values.animation;
				atts[ 'data-class' ]     = 'fusion-popover-' + cid;
				atts[ 'data-delay' ]     = values.delay;
				atts[ 'data-placement' ] = values.placement.toLowerCase();
				atts[ 'data-title' ]     = values.title;
				atts[ 'data-toggle' ]    = 'popover';
				atts[ 'data-trigger' ]   = values.trigger;
				values.popover           = popoverContent;
				return atts;
			},

			/**
			 * Builds the styles.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {string}
			 */
			computeStyles: function( values ) {
				var cid = this.model.get( 'cid' ),
					styles,
					arrowColor;

				if ( 'default' === values.placement ) {
					values.placement = fusionAllElements.fusion_popover.defaults.placement;
				}

				arrowColor = values.content_bg_color;

				if ( 'bottom' === values.placement ) {
					arrowColor = values.title_bg_color;
				}

				styles  = '<style type="text/css">';
				if ( '' !== values.bordercolor ) {
					styles += '.popover.fusion-popover-' + cid + '.' + values.placement + ' .arrow{border-' + values.placement + '-color:' + values.bordercolor + ';}';
					styles += '.popover.fusion-popover-' + cid + '{border-color:' + values.bordercolor + ';}';
				}
				styles += '.popover.fusion-popover-' + cid + ' .popover-title{';
				if ( '' !== values.title_bg_color ) {
					styles += 'background-color:' + values.title_bg_color + ';';
				}
				if ( '' !== values.textcolor ) {
					styles += 'color:' + values.textcolor + ';';
				}
				if ( '' !== values.bordercolor ) {
					styles += 'border-color:' + values.bordercolor + ';';
				}
				styles += '}';

				styles += '.popover.fusion-popover-' + cid + ' .popover-content{';
				if ( '' !==  values.content_bg_color ) {
					styles += 'background-color:' + values.content_bg_color + ';';
				}
				if ( '' !==  values.textcolor ) {
					styles += 'color:' + values.textcolor + ';';
				}
				styles += '}';

				if ( '' !== arrowColor ) {
					styles += '.popover.fusion-popover-' + cid + '.' + values.placement + ' .arrow:after{border-' + values.placement + '-color:' + arrowColor + ';}';
				}
				styles += '</style>';

				return styles;
			}
		} );
	} );
}( jQuery ) );
