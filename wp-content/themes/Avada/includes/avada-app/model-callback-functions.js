/* global FusionApp */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	_.extend( FusionPageBuilder.Callback.prototype, {

		fusion_get_alpha: function( value ) {
			var color = jQuery.AWB_Color( value );
			return color.alpha();
		},

		createSocialNetworks: function() {
			var socialMedia = [];

			if ( '0' != FusionApp.settings.sharing_facebook ) {
				socialMedia.push( 'facebook' );
			}
			if ( '0' != FusionApp.settings.sharing_twitter ) {
				socialMedia.push( 'twitter' );
			}
			if ( '0' != FusionApp.settings.sharing_linkedin ) {
				socialMedia.push( 'linkedin' );
			}
			if ( '0' != FusionApp.settings.sharing_reddit ) {
				socialMedia.push( 'reddit' );
			}
			if ( '0' != FusionApp.settings.sharing_whatsapp ) {
				socialMedia.push( 'whatsapp' );
			}
			if ( '0' != FusionApp.settings.sharing_telegram ) {
				socialMedia.push( 'telegram' );
			}
			if ( '0' != FusionApp.settings.sharing_tumblr ) {
				socialMedia.push( 'tumblr' );
			}
			if ( '0' != FusionApp.settings.sharing_pinterest ) {
				socialMedia.push( 'pinterest' );
			}
			if ( '0' != FusionApp.settings.sharing_vk ) {
				socialMedia.push( 'vk' );
			}
			if ( '0' != FusionApp.settings.sharing_email ) {
				socialMedia.push( 'mail' );
			}
			return socialMedia.join( '|' );
		},

		toYes: function( value ) {
			return 1 == value || true === value ? 'yes' : 'no';
		},

		toLowerCase: function( value ) {
			return value.toLowerCase();
		},

		urlFromObject: function( value ) {
			if ( 'object' === typeof value && 'undefined' !== typeof value.url ) {
				return value.url;
			}
			return '';
		},

		portfolioPaginationFormat: function( value ) {
			return value.toLowerCase().replace( / /g, '' ).replace( /\_/g, '-' ).replace( 'scroll', '' ).replace( /-\s*$/, '' ); // eslint-disable-line no-useless-escape
		},

		/**
		 * Checks if there are portfolio grid or carousels in preview frame.
		 *
		 * @return {boolean} - Return whether the page has portfolios or not.
		 */
		noPortfolioOnPage: function() {
			if ( 0 < jQuery( '#fb-preview' ).contents().find( '.fusion-portfolio-layout-grid, .fusion-portfolio-carousel' ).length ) {
				return false;
			}
			return true;
		},

		/**
		 * Checks if there is pagination on the page.
		 *
		 * @return {boolean} - Return whther the page has pagination or not.
		 */
		isPaginationOnPage: function() {
			if ( 0 === jQuery( '#fb-preview' ).contents().find( '.pagination' ).length ) {
				return false;
			}
			return true;
		},

		/**
		 * Checks if there is rollover on the page.
		 *
		 * @return {boolean} - Return whether the page has tollover or not.
		 */
		isRolloverOnPage: function() {
			if ( 0 === jQuery( '#fb-preview' ).contents().find( '.fusion-image-wrapper' ).length ) {
				return false;
			}
			return true;
		},

		/**
		 * Checks if there is masonry on the page.
		 *
		 * @return {boolean} - Return whether the page has masonry or not.
		 */
		isMasonryOnPage: function() {
			if ( 0 === jQuery( '#fb-preview' ).contents().find( '.fusion-blog-layout-masonry, .fusion-portfolio-masonry, .fusion-gallery-layout-masonry' ).length ) {
				return false;
			}
			return true;
		},

		/**
		 * Updates grid separators.
		 *
		 * @param {string} value - The value (using "|" as separator for multiple elements).
		 * @return {boolean} - Always returns true.
		 */
		updateGridSeps: function( value ) {
			var sepClasses = '',
				$sepElems  = jQuery( '#fb-preview' ).contents().find( 'div.fusion-content-sep' );

			_.each( value.split( '|' ), function( sepClass ) {
				sepClasses += ' sep-' + sepClass;
			} );

			$sepElems.removeClass( 'sep-single sep-solid sep-double sep-dashed sep-dotted sep-shadow' );
			$sepElems.addClass( sepClasses );

			return true;
		},

		/**
		 * Checks if there is twitter widget or blog masonry on the page.
		 *
		 * @return {boolean} - Return whether there's a twitter widget or blogmasonry on the page.
		 */
		timeLineColorCallback: function() {
			if ( 0 < jQuery( '#fb-preview' ).contents().find( '.fusion-blog-layout-masonry, .twitter-timeline-rendered' ).length  ) {
				return false;
			}
			return true;
		},

		fusionEditGlobalSidebar: function( $trigger ) {
			var option = 'pages_sidebar';
			if ( FusionApp.data.is_singular_post ) {
				option = 'posts_sidebar';
			} else if ( FusionApp.data.is_portfolio_single ) {
				option = 'portfolio_sidebar';
			} else if ( FusionApp.data.is_portfolio_archive ) {
				option = 'portfolio_archive_sidebar';
			} else if ( FusionApp.data.is_search ) {
				option = 'search_sidebar';
			} else if ( FusionApp.data.is_product ) {
				option = 'woo_sidebar';
			} else if ( FusionApp.data.is_woo_archive ) {
				option = 'woocommerce_archive_sidebar';
			} else if ( FusionApp.data.is_singular_ec ) {
				option = 'ec_sidebar';
			} else if ( FusionApp.data.is_bbpress || FusionApp.data.is_buddypress ) {
				option = 'ppbress_sidebar';
			} else if ( FusionApp.data.is_home || ( FusionApp.data.is_archive && ! FusionApp.data.is_search ) ) {
				option = 'blog_archive_sidebar';
			}
			if ( -1 < $trigger.data( 'fusion-option' ).indexOf( '_2' ) ) {
				option += '_2';
			}
			FusionApp.sidebarView.openOption( option, 'to', $trigger.data( 'fusion-option-open-parent' ) );
		},

		fusionEditFeaturedImage: function( $trigger ) {
			FusionApp.sidebarView.openOption( '_thumbnail_id', 'po', $trigger.data( 'fusion-option-open-parent' ) );
		}
	} );
}( jQuery ) );
