var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Counter circle child View.
		FusionPageBuilder.fusion_testimonial = FusionPageBuilder.ChildElementView.extend( {

			/**
			 * Runs during render() call.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			onRender: function() {
				if ( 'undefined' !== typeof this.model.attributes.selectors ) {
					this.model.attributes.selectors[ 'class' ] += ' ' + this.className;
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
					this.setElementAttributes( this.$el, this.model.attributes.selectors );
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

				this.validateValues( atts.values );
				this.buildReviewAttr( atts.values );

				attributes.values = atts.values;
				attributes.parentValues = atts.parentValues;

				attributes.imageAttr      = this.buildImageAttr( atts.values );
				attributes.thumbnailAttr  = this.buildThumbnailAttr( atts );
				attributes.blockquoteAttr = this.buildBlockquoteAttr( atts );
				attributes.quoteAttr      = this.buildQuoteAttr( atts );
				attributes.authorAttr     = this.buildAuthorAttr( atts );

				attributes.cid     = this.model.get( 'cid' );
				attributes.parent  = this.model.get( 'parent' );
				attributes.content = atts.values.element_content;

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

				if ( 'round' === values.image_border_radius ) {
					values.image_border_radius = '50%';
				} else {
					values.image_border_radius = _.fusionValidateAttrValue( values.image_border_radius, 'px' );
				}

				// Check for deprecated.
				if ( 'undefined' !== typeof values.gender && '' !== values.gender ) {
					values.avatar = values.gender;
				}

				if ( 'image' === values.avatar && ! values.image ) {
					values.avatar = 'none';
				}
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			buildReviewAttr: function( values ) {
				var reviewAttr = {
					class: 'review '
				};

				if ( this.isFirstChild() ) {
					reviewAttr[ 'class' ] += 'active-testimonial ';
				}

				if ( 'none' === values.avatar ) {
					reviewAttr[ 'class' ] += 'no-avatar';
				} else if ( 'image' === values.avatar ) {
					reviewAttr[ 'class' ] += 'avatar-image';
				} else {
					reviewAttr[ 'class' ] += values.avatar;
				}

				this.model.set( 'selectors', reviewAttr );
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			buildImageAttr: function( values ) {
				var imageAttr = {
					class: 'testimonial-image',
					src: values.image,
					alt: ''
				};

				if ( 'image' === values.avatar ) {
					imageAttr.style = '-webkit-border-radius: ' + values.image_border_radius + ';-moz-border-radius: ' + values.image_border_radius + ';border-radius: ' + values.image_border_radius + ';';
				}

				return imageAttr;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			buildThumbnailAttr: function( atts ) {
				var values = atts.values,
					parentValues = atts.parentValues,
					thumbnailAttr = {
						class: 'testimonial-thumbnail'
					};

				if ( 'image' !== values.avatar ) {
					thumbnailAttr[ 'class' ] += ' doe';
					thumbnailAttr.style = 'color:' + parentValues.textcolor + ';';
				}

				return thumbnailAttr;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			buildBlockquoteAttr: function( atts ) {
				var parentValues = atts.parentValues,
					blockquoteAttr = {
						style: 'background-color:' + parentValues.backgroundcolor + ';'
					};

				if ( 'clean' === parentValues.design && ( 'transparent' === parentValues.backgroundcolor || 0 === jQuery.AWB_Color( parentValues.backgroundcolor ).alpha() ) ) {
					blockquoteAttr.style += 'margin: -25px;';
				}

				return blockquoteAttr;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			buildQuoteAttr: function( atts ) {
				var parentValues = atts.parentValues,
					quoteAttr = {
						style: 'background-color:' + parentValues.backgroundcolor + ';color:' + parentValues.textcolor + ';'
					};

				return quoteAttr;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			buildAuthorAttr: function( atts ) {
				var parentValues = atts.parentValues,
					authorAttr = {
						class: 'author',
						style: 'color:' + parentValues.textcolor + ';'
					};

				return authorAttr;
			}
		} );
	} );
}( jQuery ) );
