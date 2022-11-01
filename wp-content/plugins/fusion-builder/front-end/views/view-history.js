/* global FusionPageBuilderApp, FusionApp, fusionBuilderText, FusionEvents */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	// Builder Builder History
	FusionPageBuilder.BuilderHistory = window.wp.Backbone.View.extend( {

		template: FusionPageBuilder.template( jQuery( '#fusion-builder-front-end-history' ).html() ),
		className: 'fusion-builder-history-list submenu-trigger-target',
		tagName: 'ul',

		/**
		 * Init.
		 *
		 * @since 2.0.0
		 * @param {Object} data - The data.
		 * @return {void}
		 */
		initialize: function() {
			var data = FusionApp.data;

			this.fusionCommands       = new Array( '[]' );
			this.fusionCommandsStates = new Array( '[]' ); // History states
			this.maxSteps             = 25; // Maximum steps allowed/saved
			this.currStep             = 1; // Current Index of step
			this.allElements          = data.postDetails.post_content;
			this.fusionHistoryState   = '';
			this.tracking             = 'on';
			this.trackingPaused       = 'off';
			this.unsavedStep          = 1; // Unsaved steps.

			// Set initial history step
			this.fusionCommands[ this.currStep ]       = { allElements: data.postDetails.post_content };
			this.fusionCommandsStates[ this.currStep ] = fusionBuilderText.empty;

			this.listenTo( FusionEvents, 'fusion-history-pause-tracking', this.pauseTracking );
			this.listenTo( FusionEvents, 'fusion-history-resume-tracking', this.resumeTracking );
			this.listenTo( FusionEvents, 'fusion-history-save-step', this.saveHistoryStep );
			this.listenTo( FusionEvents, 'fusion-history-turn-on-tracking', this.turnOnTracking );
			this.listenTo( FusionEvents, 'fusion-history-turn-off-tracking', this.turnOffTracking );
			this.listenTo( FusionEvents, 'fusion-history-go-to-step', this.historyStep );
			this.listenTo( FusionEvents, 'fusion-history-clear', this.clearEditor );
			this.listenTo( FusionEvents, 'fusion-history-capture-editor', this.captureEditor );
			this.listenTo( FusionEvents, 'fusion-history-undo', this.doUndo );
			this.listenTo( FusionEvents, 'fusion-history-redo', this.doRedo );
			this.listenTo( FusionEvents, 'fusion-app-saved', this.clearEditor );
			this.listenTo( FusionEvents, 'fusion-builder-reset', this.resetStates );
			this.listenTo( FusionEvents, 'fusion-element-removed', this.resetStates );
		},

		resetStates: function( cid ) {
			var self = this;

			if ( 'object' === typeof this.fusionCommands ) {
				_.each( this.fusionCommands, function( state, index ) {
					if ( 'undefined' === typeof cid || ! cid || ( 'param' === state.type && 'undefined' !== typeof state.cid && cid === state.cid ) ) {
						self.fusionCommands[ index ] = { allElements: state.allElements };
					}
				} );
			}
		},

		/**
		 * Renders the view.
		 *
		 * @since 2.0.0
		 * @return {Object} this
		 */
		render: function() {
			var self = this;

			this.$el.html( this.template( { steps: this.fusionCommandsStates, currentStep: this.currStep } ) );
			this.$el.attr( 'aria-expanded', false );
			this.$el.find( 'li' ).on( 'click', function( event ) {
				if ( event ) {
					event.preventDefault();
				}
				self.historyStep( event );
			} );

			this.updateUI();

			return this;
		},

		/**
		 * Saves a step in the history.
		 *
		 * @since 2.0.0
		 * @param {string} text - The text to be displayed in the history log.
		 * @return {void}
		 */
		saveHistoryStep: function( text, state ) {

			this.fusionHistoryState = text;

			this.turnOnTracking();
			this.captureEditor( state );
			this.turnOffTracking();
		},

		/**
		 * Captures the editor (used in front-end.js)
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		captureEditor: function( state ) {
			if ( 'object' !== typeof state ) {
				state = {};
			}

			if ( 'undefined' === typeof FusionPageBuilderApp ) {
				return;
			}

			FusionPageBuilderApp.builderToShortcodes();

			if ( this.isTrackingOn() && ! this.isTrackingPaused() ) {

				// If reached limit
				if ( this.currStep == this.maxSteps ) {

					// Remove first index
					this.fusionCommands.shift();
					this.fusionCommandsStates.shift();
				} else {

					// Else increment index
					this.currStep    += 1;
					this.unsavedStep += 1;
				}

				// If we are not at the end of the states, we need to wipe those ahead.
				if ( this.currStep !== this.fusionCommands.length ) {
					this.fusionCommandsStates.length = this.currStep;
					this.fusionCommands.length       = this.currStep;
				}

				// Get content
				this.allElements = FusionApp.data.postDetails.post_content;

				// Add all elements as fallback method.
				state.allElements = this.allElements;

				// Add editor data to Array
				this.fusionCommands[ this.currStep ] = state;

				// Add history state
				this.fusionCommandsStates[ this.currStep ] = this.fusionHistoryState;

				FusionApp.contentChange( 'page', 'builder-content' );

				// Update buttons
				this.fusionHistoryState = '';
				this.render();
			}
		},

		/**
		 * Turn history tracking ON.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		turnOnTracking: function() {
			this.tracking = 'on';
		},

		/**
		 * Turn history tracking OFF.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		turnOffTracking: function() {
			this.tracking = 'off';
		},

		/**
		 * Turn history tracking ON.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		pauseTracking: function() {
			this.trackingPaused = 'on';
		},

		/**
		 * Turn history tracking OFF.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		resumeTracking: function() {
			this.trackingPaused = 'off';
		},

		canApplyStep: function( historyStep ) {
			if ( 'object' !== typeof historyStep || 'undefined' === typeof historyStep.type ) {
				return false;
			}

			if ( 'param' === historyStep.type || 'price-param' === historyStep.type || 'pricefooter-param' === historyStep.type || 'pricefeatures-param' === historyStep.type ) {
				return true;
			}

			return false;
		},

		canApplySteps: function( stepIndex ) {
			var self     = this,
				redo     = stepIndex < this.currStep ? false : true,
				steps    = [],
				canApply = true;

			if ( ! redo ) {
				steps = this.fusionCommands.slice( stepIndex + 1, this.currStep + 1 );
			} else {
				steps = this.fusionCommands.slice( this.currStep + 1, stepIndex + 1 );
			}

			_.each( steps, function( step ) {
				if ( ! self.canApplyStep( step ) ) {
					canApply = false;
				}
			} );

			return canApply;
		},

		applySteps: function( stepIndex ) {
			var self  = this,
				redo  = stepIndex < this.currStep ? false : true,
				steps = [];

			if ( ! redo ) {
				steps     = this.fusionCommands.slice( stepIndex + 1, this.currStep + 1 ).reverse();
			} else {
				steps = this.fusionCommands.slice( this.currStep + 1, stepIndex + 1 );
			}

			_.each( steps, function( step ) {
				self.applyStep( step, redo );
			} );
		},

		applyStep: function( historyStep, redo ) {
			var elementView,
				params,  // eslint-disable-line no-unused-vars
				columnView;

			redo = 'undefined' === typeof redo ? false : redo;

			switch ( historyStep.type ) {

			case 'param':
				elementView = window.FusionPageBuilderViewManager.getView( historyStep.cid );
				if ( elementView ) {
					params = elementView.model.get( 'params' ); // eslint-disable-line no-unused-vars

					// If undo, set new value to step so redo can use it.
					if ( ! redo ) {
						elementView.historyUpdateParam( historyStep.param, historyStep.oldValue );
						FusionEvents.trigger( 'fusion-param-changed', historyStep.param, historyStep.oldValue );
						FusionEvents.trigger( 'fusion-param-changed-' + historyStep.cid, historyStep.param, historyStep.oldValue );
					} else {
						elementView.historyUpdateParam( historyStep.param, historyStep.newValue );
						FusionEvents.trigger( 'fusion-param-changed', historyStep.param, historyStep.newValue );
						FusionEvents.trigger( 'fusion-param-changed-' + historyStep.cid, historyStep.param, historyStep.newValue );
					}
				}
				break;

			case 'price-param':
				elementView = window.FusionPageBuilderViewManager.getView( historyStep.cid );
				if ( elementView ) {

					// If undo, set new value to step so redo can use it.
					if ( ! redo ) {
						elementView.updatePricingTablePrice( historyStep.param, historyStep.oldValue );
						FusionEvents.trigger( 'fusion-param-changed', historyStep.param, historyStep.oldValue );
						FusionEvents.trigger( 'fusion-param-changed-' + historyStep.cid, historyStep.param, historyStep.oldValue );
					} else {
						elementView.updatePricingTablePrice( historyStep.param, historyStep.newValue );
						FusionEvents.trigger( 'fusion-param-changed', historyStep.param, historyStep.newValue );
						FusionEvents.trigger( 'fusion-param-changed-' + historyStep.cid, historyStep.param, historyStep.newValue );
					}
				}
				break;

			case 'pricefooter-param':
				elementView = window.FusionPageBuilderViewManager.getView( historyStep.cid );
				if ( elementView ) {

					// If undo, set new value to step so redo can use it.
					if ( ! redo ) {
						elementView.updatePricingTableFooter( historyStep.oldValue );
						FusionEvents.trigger( 'fusion-param-changed', 'footer_content', historyStep.oldValue );
						FusionEvents.trigger( 'fusion-param-changed-' + historyStep.cid, 'footer_content', historyStep.oldValue );
					} else {
						elementView.updatePricingTableFooter( historyStep.newValue );
						FusionEvents.trigger( 'fusion-param-changed', 'footer_content', historyStep.newValue );
						FusionEvents.trigger( 'fusion-param-changed-' + historyStep.cid, 'footer_content', historyStep.newValue );
					}
				}
				break;

			case 'pricefeatures-param':
				elementView = window.FusionPageBuilderViewManager.getView( historyStep.cid );
				if ( elementView ) {

					// If undo, set new value to step so redo can use it.
					if ( ! redo ) {
						elementView.updatePricingTableFeatures( historyStep.oldValue );
						FusionEvents.trigger( 'fusion-param-changed', 'footer_content', historyStep.oldValue );
						FusionEvents.trigger( 'fusion-param-changed-' + historyStep.cid, 'feature_rows', historyStep.oldValue );
					} else {
						elementView.updatePricingTableFeatures( historyStep.newValue );
						FusionEvents.trigger( 'fusion-param-changed', 'footer_content', historyStep.newValue );
						FusionEvents.trigger( 'fusion-param-changed-' + historyStep.cid, 'feature_rows', historyStep.newValue );
					}
				}
				break;

			case 'add-element':
				if ( redo ) {
					FusionPageBuilderApp.collection.add( historyStep.model );
				} else {
					elementView = window.FusionPageBuilderViewManager.getView( historyStep.model.cid );
					if ( elementView ) {
						elementView.removeElement();
					}
				}
				break;

			case 'remove-element':
				if ( redo ) {
					elementView = window.FusionPageBuilderViewManager.getView( historyStep.model.cid );
					if ( elementView ) {
						elementView.removeElement();
					}
				} else {
					FusionPageBuilderApp.collection.add( historyStep.model );
				}
				break;

			case 'move-element':
				elementView = window.FusionPageBuilderViewManager.getView( historyStep.cid );

				// Need to ignore itself.
				elementView.$el.addClass( 'ignore-me' );

				if ( redo ) {
					columnView = window.FusionPageBuilderViewManager.getView( historyStep.newParent );
					if ( elementView && columnView ) {
						columnView.$el.find( '.fusion-builder-column-content' ).first().find( '> span, > div' ).not( '.ignore-me' ).eq( ( historyStep.newIndex - 1 ) ).after( elementView.$el );
						FusionPageBuilderApp.onDropCollectionUpdate( elementView.model, historyStep.newIndex, historyStep.newParent );
					}
				} else {
					columnView = window.FusionPageBuilderViewManager.getView( historyStep.oldParent );
					if ( elementView && columnView ) {
						columnView.$el.find( '.fusion-builder-column-content' ).first().find( '> span, > div' ).not( '.ignore-me' ).eq( ( historyStep.oldIndex - 1 ) ).after( elementView.$el );
						FusionPageBuilderApp.onDropCollectionUpdate( elementView.model, historyStep.oldIndex, historyStep.oldParent );
					}
				}

				elementView.$el.removeClass( 'ignore-me' );

				break;
			}
		},

		updateActiveStyling: function() {
			FusionApp.builderToolbarView.$el.find( '.fusion-builder-history-list li' ).removeClass( 'fusion-history-active-state' );
			FusionApp.builderToolbarView.$el.find( '.fusion-builder-history-list' ).find( '[data-state-id="' + this.currStep + '"]' ).addClass( 'fusion-history-active-state' );
		},

		fullContentReplace: function( data ) {
			this.resetStates();
			FusionPageBuilderApp.clearBuilderLayout();
			FusionPageBuilderApp.$el.find( '.fusion_builder_container' ).remove();

			// Try to make the shortcode if the content does not contain them.
			data = FusionPageBuilderApp.validateContent( data );

			// Reset models with new elements
			FusionPageBuilderApp.createBuilderLayout( data );
		},

		/**
		 * Undo last step in history.
		 * Saves the undone step so that we may redo later if needed.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		doUndo: function( event ) {

			var undoData,
				historyStep = {};

			if ( event ) {
				event.preventDefault();
			}

			// Turn off tracking first, so these actions are not captured
			if ( this.hasUndo() ) { // If no data or end of stack and nothing to undo

				// Close opened nested cols to make sure UI works after history change.
				this.closeNestedCols();

				this.turnOffTracking();

				// Data to undo
				historyStep = this.fusionCommands[ this.currStep ];

				if ( this.canApplyStep( historyStep ) ) {
					this.applyStep( historyStep, false );
					this.currStep -= 1;
				} else {
					this.currStep -= 1;
					historyStep    = this.fusionCommands[ this.currStep ];
					undoData       = 'object' === typeof historyStep ? historyStep.allElements : false;
					if ( undoData && '[]' !== undoData ) {
						this.fullContentReplace( undoData );
					}
				}
				this.updateActiveStyling();
			}
		},

		/**
		 * Redo last step.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		doRedo: function( event ) {

			var redoData;

			if ( event ) {
				event.preventDefault();
			}

			if ( this.hasRedo() ) { // If not at end and nothing to redo

				// Close opened nested cols to make sure UI works after history change.
				this.closeNestedCols();

				// Turn off tracking, so these actions are not tracked
				this.turnOffTracking();

				// Move index
				this.currStep += 1;

				window.historyStep = this.fusionCommands[ this.currStep ];
				redoData           = 'object' === typeof window.historyStep ? window.historyStep.allElements : false;

				if ( this.canApplyStep( window.historyStep ) ) {
					this.applyStep( window.historyStep, true );
				} else if ( redoData && '[]' !== redoData ) {
					this.fullContentReplace( redoData );
				}

				this.updateActiveStyling();
			}
		},

		/**
		 * Go to a step.
		 *
		 * @since 2.0.0
		 * @param {string|number} step - The step.
		 * @param {Object}     event - The event.
		 * @return {void}
		 */
		historyStep: function( event ) {
			var step,
				stepData;

			if ( event ) {
				event.preventDefault();
			}

			// Close opened nested cols to make sure UI works after history change.
			this.closeNestedCols();

			step = jQuery( event.currentTarget ).data( 'state-id' );

			// Turn off tracking, so these actions are not tracked
			this.turnOffTracking();

			if ( this.canApplySteps( step ) ) {
				this.applySteps( step );
				this.currStep = step;
			} else {
				this.currStep = step;
				stepData      = 'object' === typeof this.fusionCommands[ this.currStep ] ? this.fusionCommands[ this.currStep ].allElements : false;
				if ( stepData && '[]' !== stepData ) {
					this.fullContentReplace( stepData );
				}
			}
			this.updateActiveStyling();
		},

		/**
		 * Are we currently tracking history?
		 *
		 * @since 2.0.0
		 * @return {boolean}
		 */
		isTrackingOn: function() {
			return 'on' === this.tracking;
		},

		/**
		 * Is tracking paused currently?
		 *
		 * @since 2.0.0
		 * @return {boolean}
		 */
		isTrackingPaused: function() {
			return 'on' === this.trackingPaused;
		},

		/**
		 * Log commands in the console as JSON.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		logStacks: function() {
			console.log( JSON.parse( this.fusionCommands ) );
		},

		/**
		 * Clear the editor.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		clearEditor: function() {
			this.fusionCommands       = new Array( '[]' );
			this.fusionCommandsStates = new Array( '[]' );
			this.currStep             = 1;
			this.unsavedStep          = 1;
			this.fusionHistoryState   = '';

			this.fusionCommands[ this.currStep ]       = { allElements: FusionApp.data.postDetails.post_content };
			this.fusionCommandsStates[ this.currStep ] = fusionBuilderText.empty;
			this.render();
		},

		/**
		 * Do we have an undo? Checks if the current step is the 1st one.
		 *
		 * @since 2.0.0
		 * @return {boolean}
		 */
		hasUndo: function() {
			return 1 !== this.currStep;
		},

		/**
		 * Do we have a redo? Checks if a step greater than current one exists.
		 *
		 * @since 2.0.0
		 * @return {boolean}
		 */
		hasRedo: function() {
			return this.currStep < ( this.fusionCommands.length - 1 );
		},

		/**
		 * Get the array of steps/fusionCommands.
		 *
		 * @since 2.0.0
		 * @return {Array}
		 */
		getCommands: function() {
			return this.fusionCommands;
		},

		/**
		 * Update the undo/redo/history buttons.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		updateUI: function() {
			if ( 1 < this.unsavedStep ) {
				FusionApp.builderToolbarView.$el.find( '#fusion-builder-toolbar-history-menu' ).attr( 'data-has-unsaved', true );
			} else {
				FusionApp.builderToolbarView.$el.find( '#fusion-builder-toolbar-history-menu' ).attr( 'data-has-unsaved', false );
			}
			this.updateActiveStyling();
		},

		/**
		 * Close nested cols.
		 *
		 * @since 2.2
		 * @return {void}
		 */
		closeNestedCols: function() {
			var activeNestedCols = FusionPageBuilderApp.$el.find( '.fusion-nested-columns.editing' );

			if ( activeNestedCols.length ) {
				activeNestedCols.find( '.fusion-builder-cancel-row' ).trigger( 'click' );
			}
		}
	} );
}( jQuery ) );
