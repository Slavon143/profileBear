/* eslint no-empty-function: ["error", { "allow": ["functions"] }] */

var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	// Single Layout View
	FusionPageBuilder.LayoutView = Backbone.View.extend( {
		template: FusionPageBuilder.template( jQuery( '#fusion-layout-template' ).html() ),
		events: {
			'click .confirm-remove-layout': 'removeLayout',
			'click .cancel-delete': 'hideConfirmation',
			'click .remove-layout': 'showConfirmation',
			'click .remove-template': 'removeTemplate',
			'click .select-template': 'templateSelectionView',
			'click .select-template-container:not( .active )': 'templateSelectionView',
			'click .open-options': 'openOptions',
			'click .fusion-condition-control': 'openOptions',
			'click .cancel-select': 'hideTemplateSelectionView',
			'click .fusion-tabs-menu > li > a': 'switchTab',
			'submit .form-create': 'createTemplate',
			'click .fusion-select-template a': 'setTemplate',
			'keyup .layoutbox-title input': 'titleChanged'
		},

		/**
		 * Initialize the layout
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		initialize: function( options ) {
			this.parent = options.parent;
			// Listeners
			this.listenTo( this.model, 'change:data', this.render );

			this._updateTitle = _.debounce( _.bind( this.updateTitle, this ), 500 );
		},

		/**
		 * Title input has changed.
		 *
		 * @since 2.2
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		titleChanged: function( event ) {
			this._updateTitle( event );
		},

		/**
		 * Update the title via ajax.
		 *
		 * @since 2.2
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		updateTitle: function( event ) {
			var self     = this,
				newTitle = jQuery( event.target ).val();

			this.model.doAjax( {
				action: 'fusion_admin_layout_update',
				action_type: 'update_title',
				layout_id: this.model.get( 'id' ),
				title: newTitle,
				security: jQuery( '.fusion-template-builder #_wpnonce' ).val()
			}, function() {
				self.model.set( 'title', newTitle );
			} );
		},

		/**
		 * Opens layout options view
		 *
		 * @since 2.2
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		openOptions: function( event ) {
			event.preventDefault();

			this.optionsView = new FusionPageBuilder.LayoutOptionsView( { model: this.model }  );

			this.$el.closest( '.fusion-layouts' ).prepend( this.optionsView.render().el );
		},

		/**
		 * Shows confirmation delete screen
		 *
		 * @since 2.2
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		showConfirmation: function( event ) {
			event.preventDefault();
			this.$el.find( '.confirmation' ).addClass( 'active' );
		},

		/**
		 * Hides confirmation delete screen
		 *
		 * @since 2.2
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		hideConfirmation: function( event ) {
			if ( event ) {
				event.preventDefault();
			}
			this.$el.find( '.confirmation' ).removeClass( 'active' );
		},

		/**
		 * Render the template.
		 *
		 * @since 2.2
		 * @return {Object} this.
		 */
		render: function() {
			var attributes = _.extend( {}, this.model.get( 'data' ) ),
				urlParams      = new URLSearchParams( window.location.search );

			attributes.id		  = this.model.get( 'id' );
			attributes.title      = this.model.get( 'title' );
			attributes.terms 	  = this.model.getAssignedTemplates();
			attributes.conditions = this.model.getConditions();

			if ( urlParams.has( 'layout' ) && String( attributes.id ) === urlParams.get( 'layout' ) ) {
				attributes.highlight = true;
			}

			this.$el.html( this.template( attributes ) );
			return this;
		},

		/**
		 * Sets current layout to loading state
		 *
		 * @since 2.2
		 * @return {Object}
		 */
		toggleLoading: function() {
			this.$el.find( '.fusion-layout' ).toggleClass( 'loading' );
		},

		/**
		 * Delete layout.
		 *
		 * @since 2.2
		 * @return {Void}.
		 */
		removeLayout: function( event ) {
			var self = this,
				data = {
					action: 'fusion_admin_layout_delete',
					post_id: this.model.get( 'id' ),
					security: jQuery( '.fusion-template-builder #_wpnonce' ).val()
				};

			event.preventDefault();

			this.hideConfirmation();
			this.toggleLoading();

			this.model.doAjax( data, function( response ) {
				if ( response.success ) {
					_.each( self.model.getConditions(), function( condition, id ) {
						self.model.unregisterCondition( id, condition.mode );
					} );
					self.remove();
				}
			} );
		},

		/**
		 * Select template.
		 *
		 * @since 2.2
		 * @param {Object} event - The event.
		 * @return {Void}.
		 */
		templateSelectionView: function( event ) {
			var self 		= this,
				$target     = jQuery( event.currentTarget ),
				termType 	= $target.hasClass( 'select-template-container' ) ? $target.find( '.select-template' ).data( 'type' ) : $target.data( 'type' ),
				templates   = this.model.getTemplates( termType );

			event.preventDefault();

			this.termType = termType;
			this.$el.find( '.fusion-layout' ).addClass( 'is-selecting' );
			this.$el.find( '.layout-heading .control' ).hide();
			this.$el.find( '.cancel-select' ).show();
			this.$el.find( '.fusion-select-template' ).html( '' );
			this.$el.find( 'input[name="name"]' ).focus();

			if ( ! _.isEmpty( templates ) ) {
				_.each( templates, function( template ) {
					self.$el.find( '.fusion-select-template' ).append( '<a href="#" data-value="' + template.ID + '">' + template.post_title + '</a>' );
				} );
			} else {
				self.$el.find( '.fusion-select-template' ).append( '<span class="fusion-no-sections-note">' + self.$el.find( '.fusion-select-template' ).data( 'no_template' ) + '</span>' );
			}

		},

		/**
		 * Removes template.
		 *
		 * @since 2.2
		 * @param {Object} event - The event.
		 * @return {Void}.
		 */
		removeTemplate: function( event ) {
			var $target = jQuery( event.target ),
				content = this.model.getContent();

			event.preventDefault();
			content.template_terms[ $target.data( 'type' ) ] = '';
			this.updateLayoutContent( content );
		},

		/**
		 * Hides template selection view.
		 *
		 * @since 2.2
		 * @param {Object} event - The event.
		 * @return {Void}.
		 */
		hideTemplateSelectionView: function( event ) {
			event.preventDefault();
			this.termType = undefined;
			this.$el.find( '.fusion-layout' ).removeClass( 'is-selecting' );
			this.$el.find( '.layout-heading .control' ).show();
			this.$el.find( '.cancel-select' ).hide();
		},

		/**
		 * Create template.
		 *
		 * @since 2.2
		 * @param {Object} event - The event.
		 * @return {Void}.
		 */
		createTemplate: function( event ) {
			var self = this,
				data = {
					action: 'fusion_admin_layout_update',
					action_type: 'create_template',
					name: jQuery( event.target ).find( 'input' ).val(),
					layout_id: this.model.get( 'id' ),
					content: this.model.getContent(),
					term: this.termType,
					security: jQuery( '.fusion-template-builder #_wpnonce' ).val()
				};

			event.preventDefault();

			this.toggleLoading();
			this.model.doAjax( data, function( response ) {
				if ( 'object' === typeof response.data.templates ) {
					window.fusionTemplates = response.data.templates;
				}
				// Sanitize values
				if ( Array.isArray( response.data.content.conditions ) ) {
					response.data.content.conditions = {};
				}
				if ( Array.isArray( response.data.content.template_terms ) ) {
					response.data.content.template_terms = {};
				}
				self.model.set( 'data', self.sanitizeContent( response.data.content ) );
			} );
		},

		/**
		 * Checks that default values are objects and not arrays.
		 *
		 * @since 2.2
		 * @param {Object} content - The layout content.
		 * @return {Object}.
		 */
		sanitizeContent: function ( content ) {
			content.conditions = _.isArray( content.conditions ) ? {} : content.conditions;
			content.template_terms = _.isArray( content.template_terms ) ? {} : content.template_terms;

			return content;
		},

		/**
		 * Assigns template to corresponding layout term.
		 *
		 * @since 2.2
		 * @param {Object} event - The event.
		 * @return {Void}.
		 */
		setTemplate: function( event ) {
			var content = this.model.getContent();
			event.preventDefault();

			content.template_terms[ this.termType ] = jQuery( event.target ).data( 'value' );
			this.updateLayoutContent( content );
		},

		updateLayoutContent: function( content ) {
			var	self 		= this;
			this.toggleLoading();

			this.model.doAjax( {
				action: 'fusion_admin_layout_update',
				action_type: 'update_layout',
				layout_id: this.model.get( 'id' ),
				content: content,
				security: jQuery( '.fusion-template-builder #_wpnonce' ).val()
			}, function( response ) {
				self.model.set( 'data', self.sanitizeContent( response.data.content ) );
			} );
		},

		/**
		 * Switches a tab. Takes care of toggling the 'current' & 'inactive' classes
		 * and also changes the 'display' property of elements to properly make the switch.
		 *
		 * @since 2.2
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		switchTab: function( event ) {
			var $tabLink = jQuery( event.target ),
				tab = $tabLink.attr( 'href' );

			if ( event ) {
				event.preventDefault();
			}

			$tabLink.parent( 'li' ).addClass( 'current' ).removeClass( 'inactive' );
			$tabLink.parent( 'li' ).siblings().removeClass( 'current' ).addClass( 'inactive' );

			this.$el.find( '.fusion-tab-content' ).hide();
			this.$el.find( tab ).show();
		}
	} );

}( jQuery ) );
