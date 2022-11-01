/* global AwbTypography */
var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionTypographyField = {

	/**
	 * Initialize the typography field.
	 *
	 * @since 2.0.0
	 * @param {Object} $element - The element jQuery object.
	 * @return {void}
	 */
	optionTypography: function( $element ) {
		var self     = this,
			typoSets = {};

		$element = 'undefined' !== typeof $element && $element.length ? $element : this.$el;

		if ( $element.find( '.awb-typography' ).length ) {
			if ( _.isUndefined( window.awbTypographySelect ) || _.isUndefined( window.awbTypographySelect.webfonts ) ) {
				jQuery.when( window.awbTypographySelect.getWebFonts() ).done( function() {
					$element.find( '.fusion-builder-option.typography' ).each( function() {
						typoSets[ jQuery( this ).attr( 'data-option-id' ) ] = new AwbTypography( this, self );
					} );
				} );
			} else {
				$element.find( '.fusion-builder-option.typography' ).each( function() {
					typoSets[ jQuery( this ).attr( 'data-option-id' ) ] = new AwbTypography( this, self );
				} );
			}
		}
	}
};
