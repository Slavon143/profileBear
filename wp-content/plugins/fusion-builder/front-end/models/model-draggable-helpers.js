/* global FusionPageBuilderApp, FusionPageBuilderElements */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	FusionPageBuilder.DraggableHelpers = Backbone.Model.extend( {

		/**
		 * Init.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		initialize: function() {}, // eslint-disable-line no-empty-function

		/**
		 * Get dragable classes.
		 *
		 * @since 2.0.0
		 * @param {string} cid - ID of model.
		 * @return {string}
		 */
		draggableClasses: function( cid ) {
			var $element, $classes, $values, $parentCID, $parentElement;

			$element = FusionPageBuilderElements.find( function( model ) {
				return model.get( 'cid' ) == cid; // jshint ignore: line
			} );

			$values  = _.fusionCleanParameters( jQuery.extend( true, {}, $element.get( 'params' ) ) );
			$parentCID  = $element.get( 'parent' );

			switch ( $element.get( 'type' ) ) {

			case 'fusion_builder_container':

				if ( 'undefined' !== typeof $values.fusion_global ) {
					$classes = ' container-global-helper';
				}

				if ( 'yes' === $values.hundred_percent_height_scroll && 'yes' === $values.hundred_percent_height ) {
					$classes = ' container-scrolling-helper';
				}
				break;

			case 'fusion_builder_column':

				if ( FusionPageBuilderApp.DraggableHelpers.isHeightScroll( $parentCID ) ) {
					$classes = ' column-scrolling-helper';
				}

				if ( 'undefined' !== typeof $values.fusion_global || FusionPageBuilderApp.DraggableHelpers.isGlobalParent( $parentCID ) ) {
					$classes = ' column-global-helper';
				}
				break;

			case 'fusion_builder_row_inner':

				$classes = ' row-inner-nested-helper';
				if ( FusionPageBuilderApp.DraggableHelpers.isHeightScroll( $parentCID ) ) {
					$classes = ' row-inner-scrolling-helper';
				}

				if ( 'undefined' !== typeof $values.fusion_global || FusionPageBuilderApp.DraggableHelpers.isGlobalParent( $parentCID ) ) {
					$classes = ' row-inner-global-helper';
				}
				break;

			case 'fusion_builder_column_inner':

				$classes = ' column-inner-nested-helper';
				if ( FusionPageBuilderApp.DraggableHelpers.isHeightScroll( $parentCID ) ) {
					$classes = ' column-inner-scrolling-helper';
				}

				if ( 'undefined' !== typeof $values.fusion_global || FusionPageBuilderApp.DraggableHelpers.isGlobalParent( $parentCID ) ) {
					$classes = ' column-inner-global-helper';
				}
				break;

			case 'element':
				$parentElement = FusionPageBuilderElements.find( function( model ) {
					return model.get( 'cid' ) == $parentCID; // jshint ignore: line
				} );

				if ( 'fusion_builder_column_inner' === $parentElement.get( 'type' ) ) {
					$classes = ' element-nested-helper';
				}

				if ( FusionPageBuilderApp.DraggableHelpers.isHeightScroll( $parentCID ) ) {
					$classes = ' element-scrolling-helper';
				}

				if ( 'undefined' !== typeof $values.fusion_global || FusionPageBuilderApp.DraggableHelpers.isGlobalParent( $parentCID ) ) {
					$classes = ' element-global-helper';
				}
				break;
			}

			return $classes;
		},

		/**
		 * Check if element has got global parent.
		 *
		 * @since 2.0.0
		 * @param {string} cid - The ID of parent model.
		 * @return {boolean}
		 */
		isGlobalParent: function( parentCID ) {
			var $element, $values;

			$element = FusionPageBuilderElements.find( function( model ) {
				return model.get( 'cid' ) == parentCID; // jshint ignore: line
			} );

			if ( 'undefined' === typeof $element ) {
				return false;
			}

			$values  = _.fusionCleanParameters( jQuery.extend( true, {}, $element.get( 'params' ) ) );

			if ( 'undefined' !== typeof $values.fusion_global ) {
				return true;
			}

			if ( 'undefined' !== typeof $element.get( 'parent' ) ) {
				return FusionPageBuilderApp.DraggableHelpers.isGlobalParent( $element.get( 'parent' ) );
			}
			return false;
		},

		/**
		 * Check if element is inside 100% scrolling height.
		 *
		 * @since 2.0.0
		 * @param {string} cid - The ID of parent model.
		 * @return {boolean}
		 */
		isHeightScroll: function( parentCID ) {
			var $element, $values;

			$element = FusionPageBuilderElements.find( function( model ) {
				return model.get( 'cid' ) == parentCID; // jshint ignore: line
			} );

			if ( 'undefined' === typeof $element ) {
				return false;
			}

			$values  = _.fusionCleanParameters( jQuery.extend( true, {}, $element.get( 'params' ) ) );

			if ( 'fusion_builder_container' === $element.get( 'type' ) && 'yes' === $values.hundred_percent_height_scroll && 'yes' === $values.hundred_percent_height ) {
				return true;
			}

			if ( 'undefined' !== typeof $element.get( 'parent' ) ) {
				return FusionPageBuilderApp.DraggableHelpers.isHeightScroll( $element.get( 'parent' ) );
			}
			return false;
		}
	} );
}( jQuery ) );
