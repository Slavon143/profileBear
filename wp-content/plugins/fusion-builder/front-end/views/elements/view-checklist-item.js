/* global fusionAllElements, FusionPageBuilderElements */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Toggle child View
		FusionPageBuilder.fusion_li_item = FusionPageBuilder.ChildElementView.extend( {

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {

				var attributes  = {},
					parent      = this.model.get( 'parent' ),
					parentModel = FusionPageBuilderElements.find( function( model ) {
						return model.get( 'cid' ) == parent;
					} );

				this.parentValues = jQuery.extend( true, {}, fusionAllElements.fusion_checklist.defaults, _.fusionCleanParameters( parentModel.get( 'params' ) ) );
				this.parentExtras = parentModel.get( 'extras' );

				// Create attribute objects.
				attributes.checklistShortcodeSpan        = this.buildChecklistShortcodeSpanAttr( atts.values );
				attributes.checklistShortcodeIcon        = this.buildChecklistShortcodeIconAttr( atts.values );
				attributes.checklistShortcodeItemContent = this.buildChecklistShortcodeItemContentAttr( atts.values );

				// Any extras that need passed on.
				attributes.cid    = this.model.get( 'cid' );
				attributes.parent = parent;
				attributes.output = atts.values.element_content;

				return attributes;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildChecklistShortcodeSpanAttr: function( values ) {
				var checklistShortcodeSpan = {
						style: ''
					},
					circleClass = 'circle-no',
					circlecolor;

				this.parentValues.circle = ( 1 == this.parentValues.circle ) ? 'yes' : this.parentValues.circle;

				if ( 'yes' === values.circle || ( 'yes' === this.parentValues.circle && 'no' !== values.circle ) ) {
					circleClass = 'circle-yes';

					if ( ! values.circlecolor || '' === values.circlecolor ) {
						circlecolor = this.parentValues.circlecolor;
					} else {
						circlecolor = values.circlecolor;
					}
					checklistShortcodeSpan.style = 'background-color:' + circlecolor + ';';
					checklistShortcodeSpan.style += 'font-size:' + this.parentExtras.circle_yes_font_size + 'px;';
				}

				checklistShortcodeSpan[ 'class' ] = 'icon-wrapper ' + circleClass;

				checklistShortcodeSpan.style += 'height:' + this.parentExtras.line_height + 'px;';
				checklistShortcodeSpan.style += 'width:' + this.parentExtras.line_height + 'px;';
				checklistShortcodeSpan.style += 'margin-' + this.parentExtras.icon_margin_position + ':' + this.parentExtras.icon_margin + 'px;';

				return checklistShortcodeSpan;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildChecklistShortcodeIconAttr: function( values ) {
				var checklistShortcodeIcon = {},
					icon,
					iconcolor;

				if ( ! values.icon || '' === values.icon ) {
					icon = _.fusionFontAwesome( this.parentValues.icon );
				} else {
					icon = _.fusionFontAwesome( values.icon );
				}

				if ( ! values.iconcolor || '' === values.iconcolor ) {
					iconcolor = this.parentValues.iconcolor;
				} else {
					iconcolor = values.iconcolor;
				}

				checklistShortcodeIcon = {
					class: 'fusion-li-icon ' + icon,
					style: 'color:' + iconcolor + ';',
					'aria-hidden': 'true'
				};

				return checklistShortcodeIcon;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @return {Object}
			 */
			buildChecklistShortcodeItemContentAttr: function() {
				var checklistShortcodeItemContent = {
					class: 'fusion-li-item-content',
					style: 'margin-' + this.parentExtras.content_margin_position + ':' + this.parentExtras.content_margin + 'px;'
				};

				checklistShortcodeItemContent = _.fusionInlineEditor( {
					cid: this.model.get( 'cid' ),
					'data-disable-return': true,
					'data-disable-extra-spaces': true
				}, checklistShortcodeItemContent );

				return checklistShortcodeItemContent;
			}

		} );
	} );
}( jQuery ) );
