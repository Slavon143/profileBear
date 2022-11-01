/* global FusionApp */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	/**
	 * Builder Container View.
	 *
	 * @since 2.0.0
	 */
	FusionPageBuilder.PanelView = Backbone.View.extend( {

		template: FusionPageBuilder.template( jQuery( '#fusion-builder-panel-template' ).html() ),
		className: 'fusion-builder-custom-panel',
		events: {
			'click .fusion-panel-link': 'showTabs',
			'click .fusion-sub-section-link': 'showTabs'
		},

		/**
		 * Initialization.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		initialize: function() {
			this.$el.attr( 'data-id', this.model.get( 'id' ) );
			this.$el.attr( 'data-cid', this.model.get( 'cid' ) );
			this.$el.attr( 'data-context', this.model.get( 'innerContext' ) );
		},

		/**
		 * Render the model.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		render: function() {
			this.setIcon();

			this.$el.html( this.template( this.model.attributes ) );

			return this;
		},

		setIcon: function() {
			var icon = this.model.get( 'icon' );

			if ( 'undefined' !== typeof this.model.get( 'alt_icon' ) ) {
				icon = this.model.get( 'alt_icon' );
			}
			if ( 'undefined' !== typeof icon && -1 === icon.indexOf( 'fusiona' ) ) {
				delete this.model.attributes.icon;
			} else {
				this.model.set( 'icon', icon );
			}
		},

		/**
		 * Removes panel.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		removePanel: function() {

			// Remove view from manager.
			FusionApp.sidebarView.viewManager.removeView( this.model.get( 'cid' ) );

			this.remove();
		},

		/**
		 * Show or hide tabs.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The click event.
		 * @return {void}
		 */
		showTabs: function( event ) {
			var $clickTarget = jQuery( event.currentTarget ),
				$section     = $clickTarget.closest( '.fusion-sidebar-section' ),
				tab,
				tabSettings,
				id,
				tabCid = FusionApp.sidebarView.viewManager.generateCid(),
				view,
				tabView,
				fields = this.model.get( 'fields' ),
				alreadyOpen = false,
				$visiblePanel;

			event.preventDefault();
			FusionApp.data.postMeta._fusion = FusionApp.data.postMeta._fusion || {};

			if ( $clickTarget.parent().find( 'li' ).length ) {
				if ( 'true' === $clickTarget.parent().find( 'a.fusion-panel-link' ).attr( 'aria-expanded' ) ) {
					alreadyOpen = true;
				}

				// Close all open lists first.
				$section.find( '.fusion-builder-custom-panel ul li' ).hide();
				$section.find( '.fusion-builder-custom-panel ul a.fusion-panel-link' ).attr( 'aria-expanded', 'false' );

				// Open the item that was clicked.
				if ( ! alreadyOpen ) {
					$clickTarget.parent().find( 'li' ).show();
					$clickTarget.parent().find( 'a.fusion-panel-link' ).attr( 'aria-expanded', 'true' );
				} else {
					$clickTarget.parent().find( 'li' ).hide();
					$clickTarget.parent().find( 'a.fusion-panel-link' ).attr( 'aria-expanded', 'false' );
				}
			} else {

				// Scroll to top when new tab is opened.
				setTimeout( function() {
					$visiblePanel = $section.find( '.fusion-panels' ).filter( ':visible' );

					if ( 0 === $visiblePanel.length ) {
						$visiblePanel = $section.find( '.fusion-tabs' ).filter( ':visible' );
					}

					$visiblePanel.scrollTop( 0 );

				}, 50 );

				if ( $clickTarget.hasClass( 'fusion-sub-section-link' ) ) {
					id  = $clickTarget.attr( 'id' );
					tab = fields[ id ].fields;
				} else {
					id  = this.model.get( 'id' );
					tab = fields;
				}

				if ( 'shortcode_styling' === id || 'fusion_builder_elements' === id  ) {
					FusionApp.sidebarView.switchActiveContext( '#fusion-builder-sections-to', 'FBE' );
					return;
				}
				if ( 'fusion_builder_addons' === id ) {
					FusionApp.sidebarView.switchActiveContext( '#fusion-builder-sections-to', 'FBAO' );
					return;
				}

				// To do, check if tab view has already been created and if so just show.
				if ( ! $section.find( '.fusion-builder-custom-tab#tab-' + id ).length ) {
					tabSettings = {
						model: new FusionPageBuilder.Tab( {
							fields: tab,
							id: id,
							type: 'undefined' !== typeof this.model.get( 'innerContext' ) ? this.model.get( 'innerContext' ).toUpperCase() : this.model.get( 'context' ).toUpperCase(),
							cid: tabCid,
							label: jQuery( event.currentTarget ).data( 'label' )
						} )
					};
					view = new FusionPageBuilder.TabView( tabSettings );
					FusionApp.sidebarView.viewManager.addView( tabCid, view );
					$section.find( '.fusion-tabs' ).append( view.render().el );
				} else {
					tabView = FusionApp.sidebarView.viewManager.getView( $section.find( '.fusion-builder-custom-tab#tab-' + id ).data( 'cid' ) );
					if ( 'undefined' !== typeof tabView ) {
						tabView.initialCheckDependencies();
					}
					tabView.showTab();
				}

				$section.find( '.fusion-tabs' ).show();
				$section.find( '.fusion-panels' ).hide();
				$section.find( '.fusion-builder-custom-tab:not( #tab-' + id + ')' ).hide();
			}
		}
	} );
}( jQuery ) );
