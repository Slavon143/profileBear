/* global fusionAllElements, FusionPageBuilderElements */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Slide child View.
		FusionPageBuilder.fusion_slide = FusionPageBuilder.ChildElementView.extend( {

			/**
			 * Runs just before view is removed.
			 *
			 * @since 2.0.0
			 * @return null
			 */
			beforeRemove: function() {
				var parentView = window.FusionPageBuilderViewManager.getView( this.model.get( 'parent' ) );

				if ( 'undefined' !== typeof jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( this.$el.closest( '.flexslider:not(.tfs-slider)' ) ).data( 'flexslider' ) ) {
					jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( this.$el.closest( '.flexslider:not(.tfs-slider)' ) ).flexslider( 'destroy' );
				}

				if ( false === parentView.model.attributes.showPlaceholder && 1 === parentView.model.children.length ) {
					this.$el.closest( '.fusion-slider-sc' ).addClass( 'fusion-show-placeholder' );
					parentView.model.attributes.showPlaceholder = true;
				}
			},

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
			 * @since 2.0.0
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
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			beforePatch: function() {
				var parentView = window.FusionPageBuilderViewManager.getView( this.model.get( 'parent' ) );

				if (
					true === parentView.model.attributes.showPlaceholder &&
					(
						( 'undefined' !== this.model.attributes.params.image && '' !== this.model.attributes.params.image ) ||
						( 'undefined' !== this.model.attributes.params.video && '' !== this.model.attributes.params.video )
					)
				) {
					this.$el.closest( '.fusion-slider-sc' ).removeClass( 'fusion-show-placeholder' );
					parentView.model.attributes.showPlaceholder = false;
				}

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

				// Validate values and extras.
				this.validateValues( atts.values );

				// Create attribute objects.
				attributes.sliderShortcodeSlideLink       = this.buildSlideLinkAttr( atts.values );
				attributes.sliderShortcodeSlideLi         = this.buildLiAttr( atts.values );
				attributes.sliderShortcodeSlideImg        = this.buildImgAttr( atts.values );
				attributes.sliderShortcodeSlideImgWrapper = this.buildSlideImgWrapperAttr( atts.values );
				attributes.link                           = atts.values.link;
				attributes.type                           = atts.values.type;
				attributes.video                          = atts.values.video;
				attributes.elementContent                 = atts.values.element_content;

				// Any extras that need passed on.
				attributes.cid    = this.model.get( 'cid' );
				attributes.parent = this.model.get( 'parent' );

				return attributes;
			},

			/**
			 * Modifies the values.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {void}
			 */
			validateValues: function( values ) {
				values.alt   = '';
				values.title = '';
				values.src   = values.element_content ? values.element_content.replace( '&#215;', 'x' ) : '';
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			buildSlideLinkAttr: function( values ) {
				var sliderShortcodeSlideLink = {};

				if ( 'yes' === values.lightbox ) {
					sliderShortcodeSlideLink[ 'class' ]       = 'lightbox-enabled';
					sliderShortcodeSlideLink[ 'data-rel' ] = 'prettyPhoto[gallery_slider_' + this.model.get( 'cid' ) + ']';
				}

				sliderShortcodeSlideLink.href   = values.link;
				sliderShortcodeSlideLink.target = values.linktarget;

				if ( '_blank' === sliderShortcodeSlideLink.target ) {
					sliderShortcodeSlideLink.rel = 'noopener noreferrer';
				}

				sliderShortcodeSlideLink.title = values.title;

				return sliderShortcodeSlideLink;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			buildLiAttr: function( values ) {
				var sliderShortcodeSlideLi = {
					class: ( 'video' === values.type ) ? 'video' : 'image'
				};

				this.model.set( 'selectors', sliderShortcodeSlideLi );

				return sliderShortcodeSlideLi;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			buildImgAttr: function( values ) {
				var sliderShortcodeSlideImg = {
					src: values.image,
					alt: values.alt
				};

				return sliderShortcodeSlideImg;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @return {Object}
			 */
			buildSlideImgWrapperAttr: function() {
				var sliderShortcodeSlideImgWrapper = {},
					parent = this.model.get( 'parent' ),
					parentModel,
					parentValues;

				if ( parent ) {

					parentModel = FusionPageBuilderElements.find( function( model ) {
						return model.get( 'cid' ) == parent;
					} );

					parentValues = jQuery.extend( true, {}, fusionAllElements.fusion_slider.defaults, _.fusionCleanParameters( parentModel.get( 'params' ) ) );

					if ( '' !== parentValues.hover_type ) {
						sliderShortcodeSlideImgWrapper = {
							class: 'fusion-image-hover-element hover-type-' + parentValues.hover_type
						};
					}

				}

				return sliderShortcodeSlideImgWrapper;
			}

		} );
	} );
}( jQuery ) );
