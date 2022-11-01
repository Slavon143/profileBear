/* global AwbTypographySet */
var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionTypographySetsField = {

	/**
	 * Initialize the typography field.
	 *
	 * @since 2.0.0
	 * @param {Object} $element - The element jQuery object.
	 * @return {void}
	 */
	optionTypographySets: function( $element ) {
		var $set;

		$element = 'undefined' !== typeof $element && $element.length ? $element : this.$el;

		$set = $element.find( '.fusion-builder-option.typography-sets' );

		if ( ! $set.length ) {
			return;
		}

		// Init sets.
		if ( _.isUndefined( window.awbTypographySelect ) || _.isUndefined( window.awbTypographySelect.webfonts ) ) {
			jQuery.when( window.awbTypographySelect.getWebFonts() ).done( function() {
				new AwbTypographySet( $set[ 0 ], this );
			} );
		} else {
			new AwbTypographySet( $set[ 0 ], this );
		}
	}

};
