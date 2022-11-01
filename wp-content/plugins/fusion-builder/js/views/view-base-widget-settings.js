/* global CodeMirror, fusionAppConfig, fusionAllElements, fusionBuilderConfig */
/* eslint no-shadow: 0 */
/* eslint no-empty: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function( $ ) {
	var widgets, widget, widgetData, widgetDataPromise, widgetDataLoading, widgetDataLoaded, fusionConfig;

	widgets = [];

	/**
	 * Gets all widget HTML forms
	 * @param {Function} callback
	 */
	function getWidgetForms( callback ) {
		if ( widgetDataLoaded ) {
			return callback();
		}
		if ( widgetDataLoading ) {
			widgetDataPromise.success( callback );
		} else {
			widgetDataLoading = true;
			widgetDataPromise = jQuery.ajax( {
				url: fusionConfig.ajaxurl,
				type: 'post',
				dataType: 'json',
				data: {
					action: 'fusion_get_widget_form'
				}
			} ).done( function( response ) {
				widgetDataLoaded 	= true;
				widgetDataLoading 	= false;
				widgetData			= response.data;

				// Fix for the hashed widget php class names
				_.each( widgetData, function( value, key ) {

					if ( 'undefined' === typeof fusionAllElements.fusion_widget.params.type.value[ key ] ) {

						// Try to find a corresponding class name by widget name
						jQuery.map( fusionAllElements.fusion_widget.params.type.value, function( val, i ) {
							if ( val === value.name ) {

								// Add the new class name key
								value.title   = i;
								widgetData[ i ] = value;

								// Delete the old class name key
								widgetData[ key ][ 'delete' ];
							}
						} );
					}
				} );

				return callback && callback();
			} );
		}
	}

	$( document ).ready( function() {
		// Get correct fusion config in both front/backend
		fusionConfig = ( 'undefined' !== typeof FusionApp ) ? fusionAppConfig : fusionBuilderConfig;

		// Check if widget_element is enabled and get corresponding form html
		if ( fusionConfig.widget_element_enabled ) {
			getWidgetForms();
		}

		FusionPageBuilder.BaseWidgetSettingsView = FusionPageBuilder.ElementSettingsView.extend( {

			/**
			 *  Invalid widgets list
			 * 	Different from blacklist, because invalid widgets will force form append rather than use compatibility mode
			 *
			 * 	@since 2.0.0
			 */
			invalidWidgets: [ 'Fusion_Widget_Social_Links' ],

			/**
			 * Creates a formatted widget form field name.
			 *
			 * @since 2.0.0
			 * @param {String} className
			 * @param {String} name
			 */
			createWidgetFieldName: function ( className, name ) {
				var prefix = className.toLowerCase().replace( /\\/g, '_' ) + '__';

				name = name.toLowerCase();

				try {
					prefix += name.match( /\[(.*?)\]/g ).slice( -1 )[ 0 ].replace( /\[|(\])/g, '' );
				} catch ( e ) {}

				return prefix;
			},

			/**
			 * Parses a widget and registers it to `widgets` variable.
			 *
			 * @since 2.0.0
			 * @param {Object} widget
			 * @param {String} className
			 */
			registerWidget: function( widget, className ) {
				var $form,
					selectors,
					fields,
					getElementAttributes,
					incorrectFields,
					self;

				self   = this;
				fields = {};
				// Make a form and insert widget form contents for easy manipulation
				$form  = $( '<form></form>' ).html( widget.form );
				// HTML selectors we want to retrieve from `$form`
				selectors   = [
					'fieldset',
					'input',
					'select',
					'textarea'
				].join( ',' );

				getElementAttributes = function ( element, keys ) {
					var result = {};
					_.each( keys, function( key ) {
					result[ key ] = element[ key ];
					} );
					return result;
				};

				// Start form parsing
				$form.find( selectors ).not( '[type="button"]' ).each( function() {
					var field, name;

					// The form field attributes
					field = getElementAttributes(
						this,
						[ 'id', 'className', 'name', 'tagName', 'type', 'value', 'placeholder', 'options', 'checked' ]
					);
					// The form field name
					name = self.createWidgetFieldName( className, field.name );

					// Map options for select elements
					field.options = _.map( field.options, function( option ) {
						return getElementAttributes( option, [ 'value', 'text', 'selected' ] );
					} );

					// Match field attributes to fusion form fields by tagName
					switch ( field.tagName ) {
						case 'INPUT': {
							if ( [ 'text', 'number' ].includes( field.type ) ) {
								field.type = 'textfield';
							} else if ( 'checkbox' === field.type ) {
								field.type = 'radio_button_set';
								field.value = {
									'on': 'On',
									'off': 'Off'
								};
								// eslint-disable-next-line dot-notation
								field.default = field.checked ? 'on' : 'off';
							}
							break;
						}
						case 'SELECT': {
							field.type = 'select';
							field.value = _.reduce( field.options, function( options, option ) {
								if ( option.selected ) {
									field[ 'default' ] = option.value;
								}
								options[ option.value ] = option.text;
								return options;
							}, {} );
							break;
						}

						case 'TEXTAREA': {
							field.type = 'textarea';
							break;
						}

						default:
							break;
					}

					// Match field attributes to fusion form fields
					if ( field.id ) {
						// Temporal fix for menu widget typo
						field.id = 'widget-menu-widget--menu_Link_color' === field.id ? field.id.toLowerCase() : field.id;
						field.heading = $form.find( 'label[for="' + field.id + '"]' ).text() || field.placeholder;
					}

					field.param_name 	= name;
					// assing same ajax callback
					field.callback		= {};
					field.callback[ 'function' ] = 'fusion_get_widget_markup';
					field.callback.ajax     = true;

					if ( 'hidden' === field.type ) {
						delete field.dependency;
						field.hidden = true;
					}

					fields[ name ] = field;
				} );

				// Check if there were errors during parsing and add it to incorrect fields array
				incorrectFields = _.find( fields, function ( field ) {
					return ! field.heading || !field.name;
				} );

				widgets[ className ] = {
					className: className,
					fields: fields,
					data: widget,
					isInvalid: incorrectFields || this.invalidWidgets.includes( className ),
					isCoreWidget: [ 'WP_Widget_Text', 'WP_Widget_Media_Audio', 'WP_Widget_Media_Image', 'WP_Widget_Media_Video', 'WP_Widget_Media_Gallery', 'WP_Widget_Custom_HTML' ].includes( className )
				};
			},

			/**
			 * Register all widgets from `widgetData` to `widgets`
			 *
			 * @since 2.0.0
			 * @returns {void}
			 */
			registerWidgets: function () {
				var self = this;

				// Attach a callback to getWidgetForms() in case `widgetData` is not fully loaded
				getWidgetForms( function() {
					self.widgetData = widgetData;
					// Just parse `widgetData` once
					if ( ! widgets.length ) {
						_.each( self.widgetData, self.registerWidget.bind( self ) );
					}
					self.setWidgetFields();

					// HTML and Text widget especial unescape.
					if ( widget ) {
						if ( 'WP_Widget_Custom_HTML' === widget.className ) {
							self.model.attributes.params.wp_widget_custom_html__content = _.unescape( self.model.attributes.params.wp_widget_custom_html__content );
						} else if ( 'WP_Widget_Text' === widget.className ) {
							self.model.attributes.params.wp_widget_text__text = _.unescape( self.model.attributes.params.wp_widget_text__text );
						}
					}
				} );
			},

			/**
			 * Cleans selection of a appended widget form
			 *
			 * @since 2.0.0
			 * @returns {void}
			 */
			clean: function() {
				//NOTE: required for form only
				this.destroyWidgetOptions();
				this.deleteWpModels();
				this.cleanForm();
			},

			/**
			 * Cleans previous appended forms
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			cleanForm: function() {
				this.$el.find( '.fusion-widget-settings-form' ).remove();
			},

			/**
			 * Appends form into settings view
			 *
			 * @since 2.2.0
			 * @return {void}
			 */
			insertForm: function () {
				var container,
					event,
					formSettings,
					widgetData,
					codeElement,
					$el,
					widgetClassName,
					self = this;

				// Create form and append it below the widget selector.
				container    = this.$el.find( '#general .fusion_widget .fusion-builder-option:first ' );
				widgetData   = widget.data;
				widgetClassName = widget.className;


				formSettings = {
					coreWidget: widget.isCoreWidget,
					attributes: {
						id: widgetData.id + '-' + this.cid,
						base: widgetData.id,
						form: widgetData.form,
						type: widgetClassName,
						class: widgetData.classname
					},
					widgetData: this.widgetData
				};

				this.appendedForm = this.formTemplate( formSettings );

				container.after( this.appendedForm );

				jQuery( container.next() ).find( 'label' ).each( function() {
					if ( 'INPUT' === jQuery( this ).prev().prop( 'tagName' ) ) {
						jQuery( this ).prev().before( jQuery( this ) );
					}

					jQuery( this ).children().insertAfter( jQuery( this ) );
				} );

				// Aditional steps for core widgets.
				if ( formSettings.coreWidget ) {
					$el = this.$el.find( '.widget-inside' ).parent();

					this.setFormValues( widgetClassName );

					// Create the core WP view.
					event = new jQuery.Event( 'widget-added' );

					if ( 'WP_Widget_Text' === widgetClassName ) {
						wp.textWidgets.handleWidgetAdded( event, $el );
					} else if ( 'WP_Widget_Custom_HTML' === widgetClassName ) {
						wp.customHtmlWidgets.handleWidgetAdded( event, $el );
					} else {
						wp.mediaWidgets.handleWidgetAdded( event, $el );
					}

					this.$el.find( '.widget-inside' ).css( 'display', 'block' );
					if ( this.$el.find( '.wp-editor-area' ).length ) {
						setTimeout( function() {
							wp.textWidgets.widgetControls[ 'text-' + self.cid ].initializeEditor();
						}, 200 );
					}

					if ( this.$el.find( '.custom-html-widget-fields .code' ).length ) {
						codeElement = this.$el.find( '.custom-html-widget-fields .code' );
						self.codeBlock = codeElement.next( '.CodeMirror' ).get( 0 ).CodeMirror;

						if ( ! self.codeBlock ) {
							self.codeBlock = CodeMirror.fromTextArea( codeElement[ 0 ], {
								lineNumbers: true,
								lineWrapping: true,
								autofocus: true,
								mode: 'htmlmixed'
							} );
						}
						// Refresh editor after initialization
						setTimeout( function() {
							self.codeBlock.refresh();
							self.codeBlock.focus();
						}, 100 );
					}
				} else {
					$el = this.$el.find( '.fusion-widget-settings-form' );

					this.setFormValues( widgetClassName );
					setTimeout( function() {
						$el.find( '.widget-inside' ).css( 'display', 'block' );
					}, 100 );
				}
			},

			/**
			 * Goes to each form value and set's default values or previously selected ones.
			 *
			 * @since 2.2.0
			 * @return {void}
			 */
			setFormValues: function( widgetClassName ) {
				var self = this;
				// Set form values
				var paramPrefix = widgetClassName.toLowerCase() + '__';

				_.each( this.model.attributes.params, function( value, key ) {
					var $input, selector;

					if ( 'type' !== key && -1 !== key.indexOf( paramPrefix ) ) {
						selector = '[name$="[' + key.replace( paramPrefix, '' ) + ']"]';
						$input   = self.$el.find( selector );
					}

					if ( $input && $input.length ) {
						if ( $input.is( ':checkbox' ) ) {
							if ( 1 === parseInt( value ) || true === value || 'on' === value || 'enabled' === value ) {
								$input.prop( 'checked', true );
							} else {
								$input.prop( 'checked', false );
							}
						} else {
							$input.val( value );
						}
					}
				} );
			},

			/**
			 * Delete the models.
			 *
			 * @since 2.2.0
			 * @returns {void}
			 */
			deleteWpModels: function() {
				if ( 'undefined' !== typeof wp.mediaWidgets.widgetControls && 'undefined' !== typeof wp.mediaWidgets.modelCollection ) {
					wp.mediaWidgets.modelCollection.reset();
					wp.mediaWidgets.widgetControls = {};
				}
				if ( 'undefined' !== typeof wp.textWidgets.widgetControls ) {
					wp.textWidgets.widgetControls = {};
				}

				if ( 'undefined' !== typeof wp.customHtmlWidgets.widgetControls ) {
					wp.customHtmlWidgets.widgetControls = {};
				}
			},

			/**
			 * Handler to destroy specific widget options.
			 *
			 * @since 2.2.0
			 * @returns {void}
			 */
			destroyWidgetOptions: function() {

				// Remove each instance of tinyMCE editor from this view
				this.$el.find( '.wp-editor-area' ).each( function() {
					var editorID = jQuery( this ).attr( 'id' );
					if ( 'undefined' !== typeof window.tinyMCE ) {
						window.tinyMCE.execCommand( 'mceRemoveEditor', false, editorID );
						if ( 'undefined' !== typeof window.tinyMCE.get( editorID ) ) {
							window.tinyMCE.remove( '#' + editorID );
						}
					}
				} );
			},

			/**
			 * Removes all generated options from selected widget
			 *
			 * @since 2.2.0
			 * @returns {void}
			 */
			cleanWidget: function() {
				var self = this;

				if ( ! widget ) {
					return;
				}
				// Clean prev fields
				_.each( widget.fields, function( field, key ) {
					delete fusionAllElements.fusion_widget.params[ key ];
					delete self.model.attributes.params[ key ];
				} );
				widget = null;
			},

			/**
			 * Returns current selected widget
			 *
			 * @since 2.2.0
			 * @returns {void}
			 */
			getWidget: function() {
				return widget;
			},

			/**
			 * Sets the widget
			 * Action get's called when user selects a widget in <select />.
			 *
			 * @since 2.2.0
			 * @returns {void}
			 */
			setWidget: function() {
				var className = this.model.attributes.params.type;
				if ( ! className  || ! widgets[ className ] ) {
					return;
				}

				widget = widgets[ className ];
			},

			/**
			 * Updates settings fields according to the selected widget
			 *
			 * @since 2.2.0
			 * @returns {Void}
			 */
			updateWidget: function() {
				var self = this;

				if ( ! widget || widget.isCoreWidget || widget.isInvalid ) {
					return;
				}

				// Update with widget fields
				fusionAllElements.fusion_widget.params = _.extend(
					{
						type: fusionAllElements.fusion_widget.params.type
					},
					widget.fields,
					fusionAllElements.fusion_widget.params
				);

				// Set default values
				_.each( widget.fields, function( field, key ) {

					// Skip if it already have a default param
					if ( 'undefined' !== typeof self.model.attributes.params[ key ] ) {
						return;
					}

					if ( 'object' === typeof field.value && field.value[ field[ 'default' ] ] ) {
						self.model.attributes.params[ key ] = field[ 'default' ];
					} else if ( 'object' !== typeof field.value && ( field[ 'default' ] || field.value ) ) {
						self.model.attributes.params[ key ] = field[ 'default' ] || field.value;
					}
				} );
			},

			/**
			 * Sets settings fields according to the selected widget
			 *
			 * @since 2.2.0
			 * @returns {Void}
			 */
			setWidgetFields: function () {
				if ( widget && widget.className === this.model.attributes.params.type ) {
					return;
				}
				this.cleanWidget();

				this.setWidget();

				this.updateWidget();
			}

		} );
	} );

}( jQuery ) );
