var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	FusionPageBuilder.ExtraShortcodes = Backbone.Model.extend( {
		defaults: {
			elementCount: 0,
			shortcodes: {}
		},

		addData: function( content ) {
			var self         = this,
				shortcodes   = self.get( 'shortcodes' ),
				elementCount = self.get( 'elementCount' );

			_.each( content, function( shortcode ) {
				shortcode.id               = elementCount;
				shortcode.matcher          = self.convert( shortcode.shortcode );
				shortcodes[ elementCount ] = shortcode;
				elementCount++;
			} );

			this.set( { elementCount: elementCount } );
			this.set( { shortcodes: shortcodes } );
		},

		addShortcode: function( shortcode, output, tag ) {
			var self          = this,
				shortcodes    = self.get( 'shortcodes' ),
				elementCount  = self.get( 'elementCount' ),
				originalCount = self.get( 'elementCount' );

			shortcodes[ elementCount ] = {
				shortcode: shortcode,
				output: output,
				matcher: self.convert( shortcode ),
				id: elementCount,
				tag: tag
			};
			elementCount++;

			this.set( { elementCount: elementCount } );
			this.set( { shortcodes: shortcodes } );

			return originalCount;
		},

		byId: function( id ) {
			var shortcodes = this.get( 'shortcodes' );
			return shortcodes[ id ];
		},

		byShortcode: function( content ) {
			var shortcodes = this.get( 'shortcodes' ),
				$matches = _.findWhere( shortcodes, { shortcode: content } );

			if ( 'undefined' === typeof $matches ) {
				content  = this.convert( content );
				$matches = _.findWhere( shortcodes, { matcher: content } );
			}

			return $matches;
		},

		byOutput: function( content ) {
			var shortcodes = this.get( 'shortcodes' );
			return _.findWhere( shortcodes, { ouput: content } );
		},

		getAll: function() {
			return this.get( 'shortcodes' );
		},

		convert: function( content ) {

			// Clean up any parts which can be ignored for sake of matching.
			content = content.replace( / /g, '' );
			content = content.replace( /\r?\n|\r/g, '' );
			content = content.replace( /(<p[^>]+?>|<p>|<\/p>)/g, '' );
			content = content.replace( /(<br[^>]+?>|<br>|<\/br>)/g, '' );
			content = content.replace( /\[fusion_text\]\[\/fusion_text\]/g, '' );
			return content.trim();
		}
	} );
}( jQuery ) );
