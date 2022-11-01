/* globals layoutBuilder */
/* eslint no-empty-function: ["error", { "allow": ["functions"] }] */

var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	// Layout Options View
	FusionPageBuilder.LayoutOptionsView = Backbone.View.extend( {
        template: FusionPageBuilder.template( jQuery( '#fusion-layout-options' ).html() ),
		events: {
			'click .close,.fusion-layout-overlay': 'closeModal',
			'change input[type="checkbox"]': 'inputChange',
			'click .layout-option-type,.layout-mode a': 'switchTab',
			'click .layout-option-parent:not(.active) .load-child': 'showChildOptions',
			'click .layout-option-parent.active .load-child': 'hideChildOptions',
			'click .load-more': '_loadMore',
			'input .layoutbox-search input[type="search"]': '_handleSearchInput',
			'keyup .layoutbox-search input[type="search"]': '_handleSearchInput',
			'click .remove-condition': 'removeCondition',
			'click .cancel-condition,.confirm-condition': 'handleConfirmation'
		},

		templateForChildOption: FusionPageBuilder.template( jQuery( '#fusion-layout-child-option' ).html() ),

		/**
		 * Initialize the layout
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		initialize: function() {
			this.handleSearchInput = _.debounce( this.handleSearchInput, 300 );

			this.saveLayout = _.debounce( this.saveLayout, 500 );

			this.loadMore = _.debounce( this.loadMore, 300 );

		},

		/**
		 * Calls loadMore() so it can debounce correctly
		 * @since 2.2
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		_loadMore: function( event ) {
			this.loadMore( event );
		},

		/**
		 * Calls handleSearchInput() so it can debounce correctly
		 * @since 2.2
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		_handleSearchInput: function( event ) {
			this.handleSearchInput( event );
		},

		/**
		 * Removes condition from Manage Conditions section.
		 *
		 * @since 2.2
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		removeCondition: function( event ) {
			var conditions 	= this.model.getConditions(),
				$parent		= jQuery( event.target ).parent(),
				id 			= $parent.data( 'condition-id' ),
				condition 	= conditions[ id ];

			event.preventDefault();

			// Uncheck current condition box
			this.getConditionCheckbox( id ).prop( 'checked', false );
			// If it's a child condition remove it from preview
			if ( condition.parent ) {
				this.$el.find( 'li[data-condition-id="' + id + '"]' ).remove();
			}
			this.model.unregisterCondition( id, condition.mode );
			delete conditions[ id ];
			// Remove condition from Manage Conditions section
			$parent.remove();
			// Trigger autosave
			this.saveLayout();

			this.updateConditionsSectionsVisibility();

			// Render layout box conditions sections.
			this.renderLayoutBoxConditionsSection( this.model );
		},

		/**
		 * Hide or show Manage Conditions parts.
		 *
		 * @since 2.2
		 * @return {void}
		 */
		updateConditionsSectionsVisibility: function() {
			var $includeConditions 		= this.$el.find( '.include .layout-conditions' ),
				$excludeConditions 		= this.$el.find( '.exclude .layout-conditions' ),
				hasIncludeConditions	= Boolean( $includeConditions.find( 'span' ).length ),
				hasExcludeConditions	= Boolean( $excludeConditions.find( 'span' ).length );
			// If there are include or exclude conditions we show the corresponding section
			// If there are no conditions we  show empty conditions placeholder
			if ( hasIncludeConditions ) {
				$includeConditions.parent().show();
			} else {
				$includeConditions.parent().hide();
			}
			if ( hasExcludeConditions ) {
				$excludeConditions.parent().show();
			} else {
				$excludeConditions.parent().hide();
			}
			if ( hasIncludeConditions || hasExcludeConditions ) {
				this.$el.find( '.empty-conditions' ).hide();
			} else {
				this.$el.find( '.empty-conditions' ).show();
			}
		},

		/**
		 * Render the template.
		 *
		 * @since 2.2
		 * @return {Object} this.
		 */
		render: function() {
			var self		= this,
				conditions 	= this.model.getConditions();

			this.$el.html( this.template( this ) );

			// Update checkboxes state
			this.$el.find( 'input[type="checkbox"]' ).each( function() {
				if ( this.value in conditions && this.dataset.mode === conditions[ this.value ].mode ) {
					this.checked = true;
				}
			} );

			// Update previews and update checkboxes that were previously selected.
			_.each( this.model.getConditions(), function( condition, id ) {
				if ( condition.parent ) {
					self.$el.find( '.layout-option-parent[data-condition="' + condition.parent + '"] + .child-options-preview' )
					.append(
						'<li data-condition-id="' + id + '" class="preview-' + condition.mode + '">' + condition.label + '</li>'
					);
				}
				self.getConditionCheckbox( id ).prop( 'checked', true );
			} );

			this.renderConditionsSection();

			// Add listener for escape key to close modal.
			jQuery( 'body' ).on( 'keydown', function( event ) {
				if ( 27 === event.keyCode || '27' === event.keyCode ) {
					jQuery( 'body' ).off( 'keydown' );
					self.remove( event );
					return false;
				}
				return true;
			} );

			return this;
		},

		/**
		 * Returns a DOM element for condition checkbox
		 *
		 * @since 2.2
		 * @param {String} id - Condition id.
		 * @return {Object} this.
		 */
		getConditionCheckbox: function( id ) {
			var condition = this.model.getConditions()[ id ];
			if ( condition.parent ) {
				return this.$el.find( '#' + id.replace( '|', '\\|' ) + '-' + condition.mode );
			}
			return this.$el.find( '#' + id + '-' + condition.mode );
		},

		/**
		 * Loads child options.
		 *
		 * @since 2.2
		 * @param {Object} event - The event.
		 * @return {Object} this.
		 */
		showChildOptions: function( event ) {
			var $target = jQuery( event.currentTarget ),
				$parent = $target.parent();

			event.preventDefault();

			$target.find( 'i' ).addClass( 'fusiona-chevron-small-up' );

			// Hide Preview
			$parent.siblings( '.child-options-preview' ).hide();
			$parent.addClass( 'active' );
		},

		/**
		 * Hide child options.
		 *
		 * @since 2.2
		 * @param {Object} event - The event.
		 * @return {Object} this.
		 */
		hideChildOptions: function( event ) {
			var $input		= jQuery( event.currentTarget ),
				$parent		= $input.parent(),
				$preview 	= $parent.siblings( '.child-options-preview' );

			event.preventDefault();
			$input.find( 'i' ).removeClass( 'fusiona-chevron-small-up' );
			$parent.removeClass( 'active loading' );


			// Update and show child previews
			$preview.html( '' );
			_.each( this.model.getConditions(), function( condition, id ) { //eslint-disable-line no-unused-vars
				if ( condition.parent ===  $parent.data( 'condition' ) ) {
					$preview.append(
						'<li data-condition-id="' + id + '" class="preview-' + condition.mode + '">' + condition.label + '</li>'
					);
				}
			} );
			$preview.show();
		},

		/**
		 * Fetches child options for specific parent.
		 *
		 * @since 2.2
		 * @param {Object} $parent - The layout option parent Element.
		 * @return {void}
		 */
		loadChildOptions: function( $parent ) {
			var self			= this,
				page 			= $parent.data( 'page' ),
				parentCondition = $parent.data( 'condition' );

			page = page ? parseInt( page ) + 1 : 1;

			this.model.doAjax( {
				action: 'fusion_admin_layout_options',
				parent: parentCondition,
				page: page,
				security: jQuery( '.fusion-template-builder #_wpnonce' ).val()
			}, function( response ) {
				if ( response.success ) {
					self.renderChildOptions( $parent, page, response.data );
				}
			} );
		},

		/**
		 * Renders child options for specific parent.
		 *
		 * @since 2.2
		 * @param {Object} $parent
		 * @param {Number} page
		 * @param {Array} options
		 * @return {void}
		 */
		renderChildOptions: function( $parent, page, options ) {
			var self 		= this,
				container 	= $parent.find( '.child-options' ),
				conditions 	= this.model.getConditions();

			_.each( options, function( option ) {
				option.checked = conditions[ option.id ] && conditions[ option.id ].mode;
				container.append( self.templateForChildOption( option ) );
			} );

			$parent.removeClass( 'loading' );
			// Update results page
			$parent.data( 'page', page );

			// If less than 10 results change button label and disable button
			// else show button and enable it again
			if ( 10 > options.length ) {
				$parent.find( '.load-more' ).addClass( 'disabled' );
				$parent.find( '.load-more span' ).text( $parent.find( '.load-more' ).data( 'empty' ) );
			} else {
				$parent.find( '.load-more' ).show().prop( 'disabled', false ).removeClass( 'loading' );
			}
		},

		/**
		 * Handler for load more button.
		 *
		 * @since 2.2
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		loadMore: function( event ) {
			var $parent = jQuery( event.target ).closest( '.layout-option-parent' );
			jQuery( event.currentTarget ).addClass( 'loading' ).prop( 'disabled', true );
			this.loadChildOptions( $parent );
		},

		/**
		 * Fetches child options for specific parent.
		 *
		 * @since 2.2
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		inputChange: function( event ) {
			var conditions 		= this.model.getConditions(),
				input 			= event.target,
				conditionId		= input.name,
				conditionMode 	= input.value,
				confirmation	= jQuery( input ).parent().data( 'confirmation' );

			// If condition is already selected in other view show input confirmation
			if ( this.model.getConditionLayout( conditionId, conditionMode ) && this.model.getConditionLayout( conditionId, conditionMode ) !== this.model.cid ) {
				input.checked = false;

				return this.showInputConfirmation( input.parentElement, conditionId, {
					label: event.target.dataset.label,
					type: input.dataset.type,
					mode: conditionMode,
					[ input.dataset.type ]: conditionId,
					parent: input.dataset.parent
				} );
			}

			// If element has active confirmation, remove it
			if ( confirmation ) {
				jQuery( input ).parent().data( 'confirmation', null ).removeClass( 'show-confirmation' );
				confirmation.$el.remove();
			}

			// If the user is selecting the same condition perform a deselect
			// Else if is selecting same condition but the mode is different perform a toggle
			// Else were adding a new condition
			if ( conditions[ conditionId ] && conditions[ conditionId ].mode === conditionMode ) {
				jQuery( input ).prop( 'checked', false );
				this.updateParent( input, conditionId );
				this.model.unregisterCondition( conditionId, conditionMode );
				delete conditions[ conditionId ];
			} else if ( conditions[ conditionId ] ) {
				this.model.unregisterCondition( conditionId, conditions[ conditionId ].mode );
				jQuery( input ).siblings( 'input' ).prop( 'checked', false );
				conditions[ conditionId ].mode = conditionMode;
				this.model.registerCondition( conditionId, conditionMode );
				this.updateParent( input, conditionId );
			} else {
				conditions[ conditionId ] = {
					label: input.dataset.label,
					slug: input.dataset.slug,
					type: input.dataset.type,
					mode: conditionMode,
					[ input.dataset.type ]: conditionId,
					parent: input.dataset.parent
				};
				this.model.registerCondition( conditionId, conditionMode );
				this.updateParent( input, conditionId );
			}

			// Trigger autosave
			this.saveLayout();

			this.renderConditionsSection();
			this.renderLayoutBoxConditionsSection( this.model );
		},

		/**
		 * Updates parent options if options selected from search results
		 * @since 3.1.1
		 * @param input
		 * @param conditionId
		 */
		updateParent: function( input, conditionId ) {
			// If checkbox is from search results update child option if exist
			if ( jQuery( input ).closest( '.layoutbox-search-results' ).length ) {
				this.getConditionCheckbox( conditionId ).each( function() {
					var checkbox = jQuery( this );
					var status = jQuery( input ).prop( 'checked' );
					if ( ! checkbox.is( input ) ) {
						checkbox.siblings( 'input' ).prop( 'checked', false );
						checkbox.prop( 'checked', status );
					}
				} );
			}
		},

		/**
		 * Saves current layout content via ajax.
		 * Can also handle multiple layout saving via layouts prop.
		 * @since 2.2
		 * @param {Object} layouts - Multiple layouts to be saved.
		 * @return {void}
		 */
		saveLayout: function( layouts ) {
			var	self 		= this,
				data 		= {
					action: 'fusion_admin_layout_update',
					security: jQuery( '.fusion-template-builder #_wpnonce' ).val()
				};

			this.showLoader();

			if ( layouts ) {
				_.extend( data, {
					action_type: 'update_layouts',
					layouts: layouts
				} );
			} else {
				_.extend( data, {
					action_type: 'update_layout',
					layout_id: this.model.get( 'id' ),
					content: this.model.getContent()
				} );
			}

			this.model.doAjax( data, function() {
				self.hideLoader();
			} );
		},

		/**
		 * Shows Saving Conditions label
		 *
		 * @since 2.2
		 * @return {void}
		 */
		showLoader: function() {
			this.$el.find( '.loader' ).show();
		},

		/**
		 * Hides Saving Conditions label
		 *
		 * @since 2.2
		 * @return {void}
		 */
		hideLoader: function() {
			this.$el.find( '.loader' ).hide();
		},

		/**
		 * Displays condition confirmation dialog
		 *
		 * @since 2.2
		 * @param {Element} container - The checkbox container
		 * @param {String} id - The condition id
		 * @param {Object} condition - The condition
		 * @return {void}
		 */
		showInputConfirmation: function( container, id, condition ) {
			var $confirmation;
			jQuery( container ).addClass( 'show-confirmation' );
			// If confirmation already exist we refresh the id and the condition
			// else we insert confirmation modal.
			if ( jQuery( container ).data( 'confirmation' ) ) {
				jQuery( container ).data( 'confirmation', {
					id: id,
					condition: condition
				} );
			} else {
				$confirmation = jQuery( '<div class="layout-options-confirmation"><div><i class="fusiona-exclamation-sign" aria-hidden="true"></i>This condition is already assigned to another layout.You cannot use the same conditions on multiple layouts.</div><div><button class="cancel-condition">Cancel</button><button class="confirm-condition">Assign Here</button></div></div>' );
				jQuery( container ).data( 'confirmation', {
					id: id,
					condition: condition,
					$el: $confirmation
				} );
				jQuery( container ).append( $confirmation );
			}
		},

		/**
		 * Handle condition confirmation actions
		 *
		 * @since 2.2
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		handleConfirmation: function( event ) {
			var self			= this,
				$target 		= jQuery( event.target ),
				$parent			= $target.closest( '.layout-option,.layout-child-option' ),
				confirmation 	= $parent.data( 'confirmation' ),
				layouts			= {},
				layoutID,
				layout;
			event.preventDefault();

			if ( $target.hasClass( 'confirm-condition' ) ) {
				// Add condition to current layout
				this.model.getConditions()[ confirmation.id ] = confirmation.condition;
				layouts[ this.model.get( 'id' ) ] = this.model.getContent();
				// Remove condition from the other layout
				layoutID 	= this.model.getConditionLayout( confirmation.id, confirmation.condition.mode );
				layout 		= layoutBuilder.layouts[ layoutID ];
				delete layout.getConditions()[ confirmation.id ];
				layouts[ layout.get( 'id' ) ] = layout.getContent();
				// Update conditions in layout box.
				this.renderLayoutBoxConditionsSection( layout );
				// Assign checked state to the current condition
				self.getConditionCheckbox( confirmation.id ).siblings( 'input' ).prop( 'checked', false );
				self.getConditionCheckbox( confirmation.id ).prop( 'checked', true );
				// Register to global conditions
				this.model.registerCondition( confirmation.id, confirmation.condition.mode );
				// Save both layouts
				this.saveLayout( layouts );
			}

			$parent.removeClass( 'show-confirmation' );
			// Remove InputConfirmation
			confirmation.$el.remove();
			$parent.data( 'confirmation', null );

			// Render layout box conditions sections.
			this.renderLayoutBoxConditionsSection( this.model );
			this.renderConditionsSection();
		},

		/**
		 * Renders conditions section
		 *
		 * @since 2.2
		 * @return {void}
		 */
		renderConditionsSection: function() {
			// TODO use DiffDOM to avoid jank.
			var $includeConditions 		= this.$el.find( '.include .layout-conditions' ),
				$excludeConditions 		= this.$el.find( '.exclude .layout-conditions' );

			$includeConditions.html( '' );
			$excludeConditions.html( '' );

			_.each( this.model.getConditions(), function( condition, id ) {
				var $condition = jQuery( '<span data-condition-id="' + id + '">' + condition.label + '<a href="#" class="fusiona-cross remove-condition" aria-label="Remove condition" /></span>' );
				if ( 'include' === condition.mode ) {
					$includeConditions.append( $condition );
				} else {
					$excludeConditions.append( $condition );
				}
			} );
			this.updateConditionsSectionsVisibility();
		},

		/**
		 * Handler for search input.
		 *
		 * @since 2.2
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		handleSearchInput: function( event ) {
			var self 	= this,
				search	= event.target.value,
				$parent	= jQuery( event.target ).closest( '.layout-option-parent' ),
				conditions = this.model.getConditions();
			// If search is valid do ajax.
			// Else clean results and close dropdown.
			if ( search ) {
				// Add loader
				$parent.find( '.layoutbox-search-results' )
				.attr( 'data-state', 'active' )
				.html( '' )
				.append( '<div class="layoutbox-loader"><div class="fusion-builder-loader"></div></div>' );

				this.model.doAjax( {
					action: 'fusion_admin_layout_options',
					parent: $parent.data( 'condition' ),
					search: search,
					security: jQuery( '.fusion-template-builder #_wpnonce' ).val()
				}, function( response ) {
					var $container, hideSearch;
					if ( response.success ) {
						$container = $parent.find( '.layoutbox-search-results' );
						$container.html( '' );
						if ( response.data.length ) {
							_.each( response.data, function( result ) {
								result.checked = conditions[ result.id ] && conditions[ result.id ].mode;
								$container.append( self.templateForChildOption( result ) );
							} );
							// Hide search results when a click outside $container occurs
							hideSearch = function ( e ) {
								if ( ! $container.is( e.target ) && 0 === $container.has( e.target ).length ) {
									$container.attr( 'data-state', '' );
									jQuery( document ).off( 'mouseup', hideSearch );
								}
							};
							jQuery( document ).on( 'mouseup', hideSearch );
						} else {
							$container.attr( 'data-state', '' );
						}
					}
				} );
			} else {
				$parent.find( '.layoutbox-search-results' ).html( '' ).attr( 'data-state', '' );
			}
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
			var $tabLink = jQuery( event.currentTarget ),
				tab      = $tabLink.attr( 'href' );

			if ( event ) {
				event.preventDefault();
			}

			$tabLink.addClass( 'current' ).removeClass( 'inactive' );
			$tabLink.siblings().removeClass( 'current' ).addClass( 'inactive' );


			this.$el.find( tab ).siblings( '.layout-mode-tab, .layout-option-tab' ).hide();
			this.$el.find( tab ).show();
		},

		/**
		 * Renders conditions section
		 *
		 * @since 2.2
		 * @return {void}
		 */
		renderLayoutBoxConditionsSection: function( layout ) {
			var $layoutBox 	= jQuery( '.layoutbox.fusion-layout[data-id="' + layout.get( 'id' ) + '"]' ).find( 'ul.fusion-condtions' ),
				conditions	= layout.getConditions();

			$layoutBox.find( '.include, .exclude' ).remove();
			$layoutBox.closest( '.fusion-condition-control' ).removeClass( 'has-conditions' );

			if ( 'object' === typeof conditions && 0 < Object.keys( conditions ).length ) {
				$layoutBox.closest( '.fusion-condition-control' ).addClass( 'has-conditions' );
				_.each( conditions, function( condition ) {
					var $condition = jQuery( '<li class="' + condition.mode + '"><span>' + condition.label + '</span></li>' );
					$layoutBox.append( $condition );
				} );
			}
		},

		/**
		 * Close layout options modal.
		 *
		 * @since 3.3
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		closeModal: function( event ) {
			event.preventDefault();
			this.remove();
		}
	} );

}( jQuery ) );
