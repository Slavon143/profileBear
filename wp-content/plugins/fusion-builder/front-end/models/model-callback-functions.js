/* global fusionOptionName, FusionApp, FusionPageBuilderElements, fusionAppConfig, FusionPageBuilderApp, FusionPageBuilderViewManager */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	_.extend( FusionPageBuilder.Callback.prototype, {
		fusion_preview: function( name, value, args, view ) {
			var property = args.property,
				element  = window.fusionAllElements[ view.model.get( 'element_type' ) ],
				selectors;

			if ( ! args.skip ) {
				view.changeParam( name, value );
			}

			if ( ! value && '' !== value ) {
				return;
			}

			if ( '' === value && 'undefined' !== typeof element && 'undefined' !== typeof element.defaults && 'undefined' !== typeof element.defaults[ name ] ) {
				value = element.defaults[ name ];
			}
			if ( 'undefined' !== typeof args.dimension ) {
				property = ( 'undefined' !== typeof args.property[ name ] ) ? args.property[ name ] : name.replace( /_/g, '-' );
			}
			if ( 'undefined' !== typeof args.unit ) {
				value = _.fusionGetValueWithUnit( value, args.unit );
			}

			selectors = 'undefined' === typeof args.selector ? 'none' : args.selector.split( ',' );

			_.each( selectors, function( selector ) {
				const $theElement = 'none' === selector.trim() ? view.$el : view.$el.find( selector.trim() ).first();

				if ( 'string' === typeof property ) {
					$theElement.css( property, value );
				}
				if ( 'object' === typeof property ) {
					_.each( args.property, function( singleProperty ) {
						$theElement.css( singleProperty, value );
					} );
				}
			} );

			return {
				render: false
			};
		},

		fusion_update_flex_elements: function( name, value, args, view ) {
			var params   = view.model.get( 'params' ),
				oldValue = 'undefined' !== typeof params.content_layout ? params.content_layout : 'column';

			if ( ! args.skip ) {
				view.changeParam( name, value );
			}

			// If we are switching between flex and block then we need to re-render inline elements.
			if ( ( 'block' === oldValue && 'block' !== value ) || ( 'block' === value && 'block' !== oldValue ) ) {
				view.model.children.each( function( child ) {
					var cid         = child.attributes.cid,
						elementView = FusionPageBuilderViewManager.getView( cid ),
						elementType;

					if ( elementView ) {
						elementType = elementView.model.get( 'element_type' );
						if ( 'fusion_title' === elementType || 'fusion_button' === elementType || 'fusion_text' === elementType || 'fusion_imageframe' === elementType ) {
							elementView.reRender();
						}
					}
				} );
			}

			// Add attribute to the option.
			jQuery( '[data-option-id="content_layout"]' ).attr( 'data-direction', value );

			return {
				render: true
			};
		},

		fusion_container_padding: function( name, value, args, view ) {
			if ( 'function' !== typeof view.isFlex || ! view.isFlex() ) {
				return this.fusion_preview( name, value, args, view );
			}
			if ( ! args.skip ) {
				view.changeParam( name, value );
			}
			view.setValues();
			view.setResponsiveContainerStyles();
			view.$el.find( '.fusion-container-responsive-styles' ).first().html( view.responsiveStyles );

			return {
				render: false
			};
		},

		fusion_container_margin: function( name, value, args, view ) {
			return this.fusion_container_padding( name, value, args, view );
		},

		fusion_column_margin: function( name, value, args, view ) {
			if ( ! args.skip ) {
				view.changeParam( name, value );
			}

			if ( view.values.flex ) {
				view.values = {};
				view.setArgs();
				view.validateArgs();
				view.setExtraArgs();
				view.setColumnMapData();
				view.setResponsiveColumnStyles();

				view.$el.find( '.fusion-column-responsive-styles' ).last().html( view.responsiveStyles );
			} else {
				view.$el.css( name.replace( '_', '-' ), value );
				view.values[ name ] = value;
			}
			return {
				render: false
			};
		},

		fusion_column_padding: function( name, value, args, view ) {
			return this.fusion_column_margin( name, value, args, view );
		},

		fusion_add_id: function( name, value, args, view ) {
			var $theEl;

			if ( ! args.skip ) {
				view.changeParam( name, value );
			}

			$theEl = ( 'undefined' === typeof args.selector ) ? view.$el : view.$el.find( args.selector );

			$theEl.attr( 'id', value );

			return {
				render: false
			};
		},

		fusion_add_class: function( name, value, args, view ) {
			var $theEl,
				existingValue = view.model.attributes.params[ name ];

			if ( ! args.skip ) {
				view.changeParam( name, value );
			}

			$theEl = ( 'undefined' === typeof args.selector ) ? view.$el : view.$el.find( args.selector );

			$theEl.removeClass( existingValue );
			$theEl.addClass( value );

			return {
				render: false
			};
		},

		fusion_toggle_class: function( name, value, args, view ) {
			var $theEl;

			if ( ! args.skip ) {
				view.changeParam( name, value );
			}

			$theEl = ( 'undefined' === typeof args.selector ) ? view.$el : view.$el.find( args.selector );

			if ( 'object' === typeof args.classes ) {
				_.each( args.classes, function( optionClass, optionValue ) {
					$theEl.removeClass( optionClass );
					if ( value === optionValue ) {
						$theEl.addClass( optionClass );
					}
				} );
			}

			return {
				render: false
			};
		},

		fusion_cart_hide: function( name, value, args, view ) {

			if ( 'string' !== typeof args.selector ) {
				return {
					render: true
				};
			}

			if ( ! args.skip ) {
				view.changeParam( name, value );
			}

			if ( 'no' === value ) {
				view.$el.find( '.fusion-woo-cart' ).addClass( args.selector );
			} else {
				view.$el.find( '.fusion-woo-cart' ).removeClass( args.selector );
			}

			return {
				render: false
			};
		},

		fusion_ajax: function( name, value, modelData, args, cid, action, model, elementView ) {

			var params   = jQuery.extend( true, {}, modelData.params ),
				ajaxData = {};

			if ( 'undefined' !== typeof name && ! args.skip ) {
				params[ name ] = value;
			}
			ajaxData.params = jQuery.extend( true, {}, window.fusionAllElements[ modelData.element_type ].defaults, _.fusionCleanParameters( params ) );

			jQuery.ajax( {
				url: fusionAppConfig.ajaxurl,
				type: 'post',
				dataType: 'json',
				data: {
					action: action,
					model: ajaxData,
					option_name: 'string' === typeof fusionOptionName ? fusionOptionName : false,
					fusion_options: 'undefined' !== typeof FusionApp && 'object' === typeof FusionApp.settings ? jQuery.param( FusionApp.settings ) : false,
					fusion_meta: 'undefined' !== typeof FusionApp && 'object' === typeof FusionApp.data.postMeta ? jQuery.param( FusionApp.data.postMeta ) : false,
					fusion_load_nonce: fusionAppConfig.fusion_load_nonce,
					post_id: FusionApp.getDynamicPost( 'post_id' ),
					cid: cid
				}
			} )
			.done( function( response ) {
				if ( 'undefined' === typeof model ) {
					model = FusionPageBuilderElements.find( function( scopedModel ) {
						return scopedModel.get( 'cid' ) == cid; // jshint ignore: line
					} );
				}

				// This changes actual model.
				if ( 'undefined' !== typeof name && ! args.skip ) {
					elementView.changeParam( name, value );
				}

				if ( 'image_id' === name && 'undefined' !== typeof response.image_data && 'undefined' !== typeof response.image_data.url && ! args.skip ) {
					elementView.changeParam( 'image', response.image_data.url );
				}

				model.set( 'query_data', response );

				if ( 'generated_element' !== model.get( 'type' ) ) {
					if ( 'undefined' == typeof elementView ) {
						elementView = FusionPageBuilderViewManager.getView( cid );
					}

					if ( 'undefined' !== typeof elementView && ! args.skip ) {
						elementView.reRender();
					}
				}
			} );
		},

		fusion_do_shortcode: function( cid, content, parent, ajaxShortcodes ) {

			jQuery.ajax( {
				url: fusionAppConfig.ajaxurl,
				type: 'post',
				dataType: 'json',
				data: {
					action: 'get_shortcode_render',
					content: content,
					shortcodes: 'undefined' !== typeof ajaxShortcodes ? ajaxShortcodes : '',
					fusion_load_nonce: fusionAppConfig.fusion_load_nonce,
					cid: cid,
					post_id: FusionApp.getDynamicPost( 'post_id' )
				}
			} )
			.done( function( response ) {
				var markup = {},
					modelcid = cid,
					model,
					view;

				if ( 'undefined' !== typeof parent && parent ) {
					modelcid = parent;
				}

				model = FusionPageBuilderElements.find( function( scopedModel ) {
					return scopedModel.get( 'cid' ) == modelcid; // jshint ignore: line
				} );

				view = FusionPageBuilderViewManager.getView( modelcid );

				markup.output = FusionPageBuilderApp.addPlaceholder( content, response.content );

				if ( view && 'function' === typeof view.filterOutput ) {
					markup.output = view.filterOutput( markup.output );
				}

				markup.shortcode = content;

				if ( model ) {
					model.set( 'markup', markup );
				}

				// If multi shortcodes, add each.
				if ( 'object' === typeof response.shortcodes ) {
					_.each( response.shortcodes, function( output, shortcode ) {
						FusionPageBuilderApp.extraShortcodes.addShortcode( shortcode, FusionPageBuilderApp.addPlaceholder( shortcode, output ) );
					} );
				}

				if ( 'undefined' !== typeof view ) {
					view.reRender( 'ajax' );
				}

				if ( FusionPageBuilderApp.viewsToRerender ) {
					_.each( FusionPageBuilderApp.viewsToRerender, function( scopedCID ) {
						FusionPageBuilderViewManager.getView( scopedCID ).reRender( 'ajax' );
					} );

					FusionPageBuilderApp.viewsToRerender = [];
				}
			} );
		},

		fusion_code_mirror: function( name, value, args, view ) {

			// Save encoded value.
			if ( ! args.skip ) {
				view.changeParam( name, value );
			}

			if ( FusionPageBuilderApp.base64Encode( FusionPageBuilderApp.base64Decode( value ) ) === value ) {
				value = FusionPageBuilderApp.base64Decode( value );
			}

			// Update with decoded value.
			view.syntaxHighlighter.getDoc().setValue( value );

			return {
				render: false
			};
		},

		dynamic_shortcode: function( args ) {
			if ( 'undefined' === typeof args.shortcode || '' === args.shortcode ) {
				return '';
			}

			return jQuery.ajax( {
				url: fusionAppConfig.ajaxurl,
				type: 'post',
				dataType: 'json',
				data: {
					action: 'get_shortcode_render',
					content: args.shortcode,
					fusion_load_nonce: fusionAppConfig.fusion_load_nonce,
					cid: false,
					post_id: FusionApp.getDynamicPost( 'post_id' )
				}
			} )
			.done( function( response ) {
				FusionPageBuilderApp.dynamicValues.setValue( args, response.content );
			} );
		},

		post_featured_image: function( args ) {
			var ID     = '',
				postMeta = FusionApp.getDynamicPost( 'post_meta' ),
				postName = FusionApp.getPost( 'post_type_name' ).toLowerCase();

			postMeta._fusion = postMeta._fusion || {};

			if ( 'undefined' !== typeof args.type && 'main' !== args.type ) {
				ID = postMeta._fusion[ 'kd_' + args.type + '_' + postName + '_id' ];
			} else {
				ID = postMeta._thumbnail_id;
			}

			if ( 'undefined' === typeof ID || '' === ID ) {
				return ID;
			}

			return wp.media.attachment( ID ).fetch().then( function() {
				FusionPageBuilderApp.dynamicValues.setValue( args, wp.media.attachment( ID ).get( 'url' ) );
			} );
		},

		fusion_get_object_title: function( args ) {
			if ( 'undefined' === typeof FusionApp.data ) {
				return '';
			}

			return jQuery.ajax( {
				url: fusionAppConfig.ajaxurl,
				type: 'get',
				dataType: 'json',
				data: {
					action: 'ajax_dynamic_data_default_callback',
					callback: FusionApp.data.dynamicOptions[ args.data ].callback[ 'function' ],
					args: args,
					fusion_load_nonce: fusionAppConfig.fusion_load_nonce,
					post_id: FusionApp.getDynamicPost( 'post_id' ),
					is_term: FusionApp.getDynamicPost( 'is_term' )
				}
			} )
			.done( function( response ) {
				FusionPageBuilderApp.dynamicValues.setValue( args, response.content );
			} );
		},

		fusion_get_post_id: function() {
			return 'undefined' !== typeof FusionApp.data ? String( FusionApp.getDynamicPost( 'post_id' ) ) : '';
		},

		fusion_get_object_excerpt: function() {
			return 'undefined' !== typeof FusionApp.data ? FusionApp.getDynamicPost( 'post_excerpt' ) : '';
		},

		fusion_get_post_date: function( args ) {

			if ( 'undefined' === typeof FusionApp.data ) {
				return '';
			}

			if ( 'undefined' === args.format || '' === args.format ) {
				return 'undefined' !== typeof args.type && 'modified' === args.type ? FusionApp.getDynamicPost( 'post_modified' ) : FusionApp.getDynamicPost( 'post_date' );
			}

			return jQuery.ajax( {
				url: fusionAppConfig.ajaxurl,
				type: 'get',
				dataType: 'json',
				data: {
					action: 'ajax_dynamic_data_default_callback',
					callback: FusionApp.data.dynamicOptions[ args.data ].callback[ 'function' ],
					args: args,
					fusion_load_nonce: fusionAppConfig.fusion_load_nonce,
					post_id: FusionApp.getDynamicPost( 'post_id' )
				}
			} )
			.done( function( response ) {
				FusionPageBuilderApp.dynamicValues.setValue( args, response.content );
			} );
		},

		fusion_get_post_time: function( args ) {

			if ( 'undefined' === typeof FusionApp.data ) {
				return '';
			}

			return jQuery.ajax( {
				url: fusionAppConfig.ajaxurl,
				type: 'get',
				dataType: 'json',
				data: {
					action: 'ajax_dynamic_data_default_callback',
					callback: FusionApp.data.dynamicOptions[ args.data ].callback[ 'function' ],
					args: args,
					fusion_load_nonce: fusionAppConfig.fusion_load_nonce,
					post_id: FusionApp.getDynamicPost( 'post_id' )
				}
			} )
			.done( function( response ) {
				FusionPageBuilderApp.dynamicValues.setValue( args, response.content );
			} );
		},

		fusion_get_post_terms: function( args ) {

			if ( 'undefined' === typeof FusionApp.data ) {
				return '';
			}

			return jQuery.ajax( {
				url: fusionAppConfig.ajaxurl,
				type: 'get',
				dataType: 'json',
				data: {
					action: 'ajax_dynamic_data_default_callback',
					callback: FusionApp.data.dynamicOptions[ args.data ].callback[ 'function' ],
					args: args,
					fusion_load_nonce: fusionAppConfig.fusion_load_nonce,
					post_id: FusionApp.getDynamicPost( 'post_id' )
				}
			} )
			.done( function( response ) {
				FusionPageBuilderApp.dynamicValues.setValue( args, response.content );
			} );
		},

		fusion_get_post_custom_field: function( args ) {
			var postMeta = FusionApp.getDynamicPost( 'post_meta' );
			postMeta._fusion = postMeta._fusion || {};
			return 'undefined' !== typeof postMeta[ args.key ] ? postMeta[ args.key ] : '';
		},

		fusion_get_page_option: function( args ) {
			var postMeta = FusionApp.getDynamicPost( 'post_meta' );
			postMeta._fusion = postMeta._fusion || {};
			return 'undefined' !== typeof postMeta._fusion[ args.data ] ? postMeta._fusion[ args.data ] : '';
		},

		fusion_get_site_title: function() {
			return 'undefined' !== typeof FusionApp.data ? FusionApp.data.site_title : '';
		},

		fusion_get_site_tagline: function() {
			return 'undefined' !== typeof FusionApp.data ? FusionApp.data.site_tagline : '';
		},

		fusion_get_logged_in_username: function() {
			return 'undefined' !== typeof FusionApp.data ? FusionApp.data.loggined_in_username : '';
		},

		fusion_get_site_url: function() {
			return 'undefined' !== typeof FusionApp.data ? FusionApp.data.site_url : '';
		},

		fusion_get_site_logo: function( args ) {
			var type = 'undefined' !== typeof args.type ? args.type : false,
				data = {};

			if ( ! type ) {
				return '';
			}

			switch ( type ) {
				case 'default_normal':
					return FusionApp.settings.logo.url;

				case 'default_retina':
					return FusionApp.settings.logo_retina.url;

				case 'sticky_normal':
					return FusionApp.settings.sticky_header_logo.url;

				case 'sticky_retina':
					return FusionApp.settings.sticky_header_logo_retina.url;

				case 'mobile_normal':
					return FusionApp.settings.mobile_logo.url;

				case 'mobile_retina':
					return FusionApp.settings.mobile_logo_retina.url;

				case 'all':
					data[ 'default' ] = {
						'normal': FusionApp.settings.logo,
						'retina': FusionApp.settings.logo_retina
					};
					data.mobile = {
						'normal': FusionApp.settings.mobile_logo,
						'retina': FusionApp.settings.mobile_logo_retina
					};
					data.sticky = {
						'normal': FusionApp.settings.sticky_header_logo,
						'retina': FusionApp.settings.sticky_header_logo_retina
					};

					return JSON.stringify( data );
				}
			return '';
		},

		fusion_menu: function( name, value, args, view ) {
			if ( ! args.skip ) {
				view.changeParam( name, value );
			}

			const attrs = view.getTemplateAtts();

			view.$el.find( 'style' ).first().replaceWith( attrs.inlineStyles );

			// If the ajax markup is still there from initial load then data-count is wrong.
			view.$el.find( 'nav' ).attr( 'data-cid', view.model.get( 'cid' ) );

			return {
				render: false
			};
		},

		fusion_style_block: function( name, value, args, view ) {
			var styleEl;
			if ( ! args.skip ) {
				view.changeParam( name, value );
			}

			const attrs = view.getTemplateAtts();

			// Can't find base selector, markup likely wrong and needs updated.
			if ( ! view.$el.find( view.baseSelector ).length ) {
				return {
					render: true
				};
			}

			styleEl = view.$el.find( 'style' ).first();

			// When element is added there will be no <style> tag, so we have to create it.
			if ( 0 === jQuery( styleEl ).length ) {
				styleEl = view.$el.find( '.fusion-builder-element-content' ).prepend( attrs.styles );
			} else {
				jQuery( styleEl ).replaceWith( attrs.styles );
			}

			return {
				render: false
			};
		},

		woo_get_price: function( args ) {

			return jQuery.ajax( {
				url: fusionAppConfig.ajaxurl,
				type: 'get',
				dataType: 'json',
				data: {
					action: 'ajax_dynamic_data_default_callback',
					callback: FusionApp.data.dynamicOptions[ args.data ].callback[ 'function' ],
					args: args,
					fusion_load_nonce: fusionAppConfig.fusion_load_nonce,
					post_id: FusionApp.getDynamicPost( 'post_id' )
				}
			} )
			.done( function( response ) {
				FusionPageBuilderApp.dynamicValues.setValue( args, response.content );
			} );
		},

		woo_get_sku: function( args ) {

			return jQuery.ajax( {
				url: fusionAppConfig.ajaxurl,
				type: 'get',
				dataType: 'json',
				data: {
					action: 'ajax_dynamic_data_default_callback',
					callback: FusionApp.data.dynamicOptions[ args.data ].callback[ 'function' ],
					args: args,
					fusion_load_nonce: fusionAppConfig.fusion_load_nonce,
					post_id: FusionApp.getDynamicPost( 'post_id' )
				}
			} )
			.done( function( response ) {
				FusionPageBuilderApp.dynamicValues.setValue( args, response.content );
			} );
		},

		woo_get_cart_count: function( args ) {

			return jQuery.ajax( {
				url: fusionAppConfig.ajaxurl,
				type: 'get',
				dataType: 'json',
				data: {
					action: 'ajax_dynamic_data_default_callback',
					callback: FusionApp.data.dynamicOptions[ args.data ].callback[ 'function' ],
					args: args,
					fusion_load_nonce: fusionAppConfig.fusion_load_nonce,
					post_id: FusionApp.getDynamicPost( 'post_id' )
				}
			} )
			.done( function( response ) {
				FusionPageBuilderApp.dynamicValues.setValue( args, response.content );
			} );
		},

		woo_get_cart_total: function( args ) {

			return jQuery.ajax( {
				url: fusionAppConfig.ajaxurl,
				type: 'get',
				dataType: 'json',
				data: {
					action: 'ajax_dynamic_data_default_callback',
					callback: FusionApp.data.dynamicOptions[ args.data ].callback[ 'function' ],
					args: args,
					fusion_load_nonce: fusionAppConfig.fusion_load_nonce,
					post_id: FusionApp.getDynamicPost( 'post_id' )
				}
			} )
			.done( function( response ) {
				FusionPageBuilderApp.dynamicValues.setValue( args, response.content );
			} );
		},

		defaultDynamicCallback: function( args ) {
			return jQuery.ajax( {
				url: fusionAppConfig.ajaxurl,
				type: 'get',
				dataType: 'json',
				data: {
					action: 'ajax_dynamic_data_default_callback',
					callback: FusionApp.data.dynamicOptions[ args.data ].callback[ 'function' ],
					args: args,
					fusion_load_nonce: fusionAppConfig.fusion_load_nonce,
					post_id: FusionApp.getDynamicPost( 'post_id' ),
					is_term: FusionApp.getDynamicPost( 'is_term' )
				}
			} )
			.done( function( response ) {
				FusionPageBuilderApp.dynamicValues.setValue( args, response.content );
			} );
		},

		woo_get_stock: function( args ) {

			return jQuery.ajax( {
				url: fusionAppConfig.ajaxurl,
				type: 'get',
				dataType: 'json',
				data: {
					action: 'ajax_dynamic_data_default_callback',
					callback: FusionApp.data.dynamicOptions[ args.data ].callback[ 'function' ],
					args: args,
					fusion_load_nonce: fusionAppConfig.fusion_load_nonce,
					post_id: FusionApp.getDynamicPost( 'post_id' )
				}
			} )
			.done( function( response ) {
				FusionPageBuilderApp.dynamicValues.setValue( args, response.content );
			} );
		},

		woo_get_rating: function( args ) {

			return jQuery.ajax( {
				url: fusionAppConfig.ajaxurl,
				type: 'get',
				dataType: 'json',
				data: {
					action: 'ajax_dynamic_data_default_callback',
					callback: FusionApp.data.dynamicOptions[ args.data ].callback[ 'function' ],
					args: args,
					fusion_load_nonce: fusionAppConfig.fusion_load_nonce,
					post_id: FusionApp.getDynamicPost( 'post_id' )
				}
			} )
			.done( function( response ) {
				FusionPageBuilderApp.dynamicValues.setValue( args, response.content );
			} );
		},

		/**
		 * Updates selector element with CSS filter style.
		 *
		 * @param {*} name Param name.
		 * @param {*} value Param value.
		 * @param {*} args Args defined.
		 * @param {*} view Element view.
		 */
		fusion_update_filter_style: function( name, value, args, view ) {
			var newStyle = '',
				cid      = view.model.get( 'cid' ),
				$styleEl = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( '#fusion-filter-' + cid + '-style' );

			if ( ! args.skip ) {
				view.changeParam( name, value );
			}

			newStyle = _.fusionGetFilterStyleElem( view.getValues(), '.' + args.selector_base + cid, cid );

			// Update filter style block.
			if ( 0 < $styleEl.length ) {
				$styleEl.replaceWith( newStyle );
			}

			return {
				render: false
			};
		},

		/**
		 * Updates gradient styles for selector element.
		 *
		 * @param  {String} name  Param name.
		 * @param  {String} value Param value.
		 * @param  {Object} args  Args defined.
		 * @param  {Object} view  Element view.
		 * @return {Object}
		 */
		fusion_update_gradient_style: function( name, value, args, view ) {
			var $theEl,
				mainBGStyles         = '',
				parallaxStyles       = '',
				overlayStyles        = '',
				fadedStyles          = '',
				alphaBackgroundColor = '',
				videoBg              = false,
				elementType          = view.model.get( 'element_type' ),
				values               = {};

			if ( ! args.skip ) {
				view.changeParam( name, value );
			}

			values = view.getValues();

			$theEl = ( 'undefined' === typeof args.selector ) ? view.$el : view.$el.find( args.selector ).first();

			switch ( elementType ) {
				case 'fusion_builder_container':
					mainBGStyles         = _.getGradientString( values, 'main_bg' );
					parallaxStyles       = _.getGradientString( values, 'parallax' );
					fadedStyles          = _.getGradientString( values, 'fade' );
					overlayStyles        = _.getGradientString( values );
					alphaBackgroundColor = jQuery.AWB_Color( values.background_color ).alpha();

					if ( '' === mainBGStyles && '' !== values.background_image && 'yes' !== values.fade ) {
						mainBGStyles = 'url(\'' + values.background_image + '\')';
					}

					$theEl.css( 'background-image', mainBGStyles );
					$theEl.find( '.parallax-inner' ).css( 'background-image', parallaxStyles );
					$theEl.find( '.fullwidth-overlay' ).css( 'background-image', overlayStyles );
					$theEl.find( '.fullwidth-faded' ).css( 'background-image', fadedStyles );

					if ( ( 'undefined' !== typeof values.video_mp4 && '' !== values.video_mp4 ) ||
					( 'undefined' !== typeof values.video_webm && '' !== values.video_webm ) ||
					( 'undefined' !== typeof values.video_ogv && '' !== values.video_ogv ) ||
					( 'undefined' !== typeof values.video_url && '' !== values.video_url ) ) {
						videoBg   = true;
					}

					if ( 1 > alphaBackgroundColor && 0 !== alphaBackgroundColor && 'none' === values.background_blend_mode && '' === overlayStyles && ( ! _.isEmpty( values.background_image ) || ! videoBg ) ) {
						$theEl.addClass( 'fusion-blend-mode' );
					} else {
						$theEl.removeClass( 'fusion-blend-mode' );
					}

					break;

				case 'fusion_builder_column':
				case 'fusion_builder_column_inner':
					mainBGStyles         = _.getGradientString( values, 'column' );
					alphaBackgroundColor = jQuery.AWB_Color( values.background_color ).alpha();

					if ( '' === mainBGStyles && '' !== values.background_image ) {
						mainBGStyles = 'url(\'' + values.background_image + '\')';
					}
					$theEl.css( 'background-image', mainBGStyles );

					if ( 1 > alphaBackgroundColor && 0 !== alphaBackgroundColor && 'none' === values.background_blend_mode && '' === _.getGradientString( values ) && _.isEmpty( values.background_image ) && ! _.isEmpty( values.background_color ) ) {
						$theEl.closest( '.fusion-layout-column' ).addClass( 'fusion-blend-mode' );
					} else {
						$theEl.closest( '.fusion-layout-column' ).removeClass( 'fusion-blend-mode' );
					}

					break;
			}

			return {
				render: false
			};
		},

		/**
		 * Updates separator for selector element.
		 *
		 * @param  {String} name  Param name.
		 * @param  {String} value Param value.
		 * @param  {Object} args  Args defined.
		 * @param  {Object} view  Element view.
		 * @return {Object}
		 */
		fusion_update_breadcrumbs_separator: function( name, value, args, view ) {
			var $theEl,
				markup     = {},
				query_data = {};

			if ( ! args.skip ) {
				view.changeParam( name, value );
			}

			if ( FusionApp.data.is_home || FusionApp.data.is_front_page ) {
				return;
			}

			$theEl = ( 'undefined' === typeof args.selector ) ? view.$el : view.$el.find( args.selector );

			if ( '' !== value ) {
				$theEl.find( '.fusion-breadcrumb-sep' ).html( value );
			} else if ( 'undefined' !== typeof FusionApp && 'object' === typeof FusionApp.settings ) {
				$theEl.find( '.fusion-breadcrumb-sep' ).html( FusionApp.settings.breadcrumb_separator );
			}

			markup.output          = $theEl.html();
			query_data.breadcrumbs = $theEl.html();

			view.model.set( 'markup', markup );
			view.model.set( 'query_data', query_data );

			return {
				render: false
			};
		},

		/**
		 * Updates separator for selector element.
		 *
		 * @param  {String} name  Param name.
		 * @param  {String} value Param value.
		 * @param  {Object} args  Args defined.
		 * @param  {Object} view  Element view.
		 * @return {Object}
		 */
		fusion_update_tb_meta_separator: function( name, value, args, view ) {
			var $theEl,
				markup     = {},
				query_data = {};

			if ( ! args.skip ) {
				view.changeParam( name, value );
			}

			$theEl = ( 'undefined' === typeof args.selector ) ? view.$el : view.$el.find( args.selector );

			$theEl.find( '.fusion-meta-tb-sep' ).html( value );

			markup.output   = $theEl.html();
			query_data.meta = $theEl.html();

			view.model.set( 'markup', markup );
			view.model.set( 'query_data', query_data );

			return {
				render: false
			};
		},

		/**
		 * Updates gallery load more button text.
		 *
		 * @param  {String} name  Param name.
		 * @param  {String} value Param value.
		 * @param  {Object} args  Args defined.
		 * @param  {Object} view  Element view.
		 * @return {Object}
		 */
		fusion_update_gallery_load_more_text: function( name, value, args, view ) {
			var $theEl;

			if ( ! args.skip ) {
				view.changeParam( name, value );
			}

			$theEl = ( 'undefined' === typeof args.selector ) ? view.$el : view.$el.find( args.selector );
			value  = '' === value && 'object' === typeof FusionApp ? FusionApp.settings.gallery_load_more_button_text : value;
			value  = '' === value && 'undefined' !== typeof view.values ? view.values[ name ] : value;

			$theEl.find( '.awb-gallery-load-more-btn' ).html( value );

			return {
				render: false
			};
		},

		/**
		 * Updates prefix for selector element.
		 *
		 * @param  {String} name  Param name.
		 * @param  {String} value Param value.
		 * @param  {Object} args  Args defined.
		 * @param  {Object} view  Element view.
		 * @return {Object}
		 */
		fusion_update_breadcrumbs_prefix: function( name, value, args, view ) {
			var $theEl,
				markup     = {},
				query_data = {};

			if ( ! args.skip ) {
				view.changeParam( name, value );
			}

			if ( FusionApp.data.is_home || FusionApp.data.is_front_page ) {
				return;
			}

			$theEl = ( 'undefined' === typeof args.selector ) ? view.$el : view.$el.find( args.selector );

			$theEl.find( '.fusion-breadcrumb-prefix' ).remove();

			if ( '' !== value ) {
				$theEl.prepend( '<span class="fusion-breadcrumb-prefix"><span class="fusion-breadcrumb-item"><span>' + value + '</span></span>:</span>' );
			} else if ( 'undefined' !== typeof FusionApp && 'object' === typeof FusionApp.settings && '' !== FusionApp.settings.breacrumb_prefix ) {
				$theEl.prepend( '<span class="fusion-breadcrumb-prefix"><span class="fusion-breadcrumb-item"><span>' + FusionApp.settings.breacrumb_prefix + '</span></span>:</span>' );
			}

			markup.output          = $theEl.html();
			query_data.breadcrumbs = $theEl.html();

			view.model.set( 'markup', markup );
			view.model.set( 'query_data', query_data );

			return {
				render: false
			};
		},

		/**
		 * Updates container flex attributes.
		 *
		 * @param  {String} name  Param name.
		 * @param  {String} value Param value.
		 * @param  {Object} args  Args defined.
		 * @param  {Object} view  Element view.
		 * @return {Object}
		 */
		fusion_update_flex_container: function( name, value, args, view ) {
			var $theEl;

			if ( ! args.skip ) {
				view.changeParam( name, value );
			}

			$theEl = ( 'undefined' === typeof args.selector ) ? view.$el : view.$el.find( args.selector );

			switch ( name ) {
				case 'flex_align_items':
					$theEl.find( '.fusion-builder-row' ).removeClass( function ( index, className ) {
						return ( className.match( /(^|\s)fusion-flex-align-items-\S+/g ) || [] ).join( ' ' );
					} );

					$theEl.find( '.fusion-builder-row' ).addClass( 'fusion-flex-align-items-' + value );

					return {
						render: false
					};
				case 'flex_justify_content':
					$theEl.find( '.fusion-builder-row' ).removeClass( function ( index, className ) {
						return ( className.match( /(^|\s)fusion-flex-justify-content-\S+/g ) || [] ).join( ' ' );
					} );

					$theEl.find( '.fusion-builder-row' ).addClass( 'fusion-flex-justify-content-' + value );

					return {
						render: false
					};
				case 'align_content':
					$theEl.removeClass( function ( index, className ) {
						return ( className.match( /(^|\s)fusion-flex-align-content-\S+/g ) || [] ).join( ' ' );
					} );

					$theEl.find( '.fusion-builder-row' ).removeClass( function ( index, className ) {
						return ( className.match( /(^|\s)fusion-flex-align-content-\S+/g ) || [] ).join( ' ' );
					} );

					if ( 'stretch' !== value ) {
						$theEl.find( '.fusion-builder-row' ).addClass( 'fusion-flex-align-content-' + value );
						$theEl.addClass( 'fusion-flex-align-content-' + value );
					}

					return {
						render: false
					};
				}
		},

		/**
		 * Updates column flex attributes.
		 *
		 * @param  {String} name  Param name.
		 * @param  {String} value Param value.
		 * @param  {Object} args  Args defined.
		 * @param  {Object} view  Element view.
		 * @return {Object}
		 */
		fusion_update_flex_column: function( name, value, args, view ) {
			var $theEl = view.$el;

			if ( ! args.skip ) {
				view.changeParam( name, value );
			}

			switch ( name ) {
				case 'align_self':

					$theEl.removeClass( function ( index, className ) {
						return ( className.match( /(^|\s)fusion-flex-align-self-\S+/g ) || [] ).join( ' ' );
					} );

					if ( 'auto' !== value ) {
						$theEl.addClass( 'fusion-flex-align-self-' + value );
					}

					return {
						render: false
					};

				case 'content_wrap':

					$theEl.find( '.fusion-column-wrapper' ).removeClass( 'fusion-content-nowrap' );

					if ( 'wrap' !== value ) {
						$theEl.find( '.fusion-column-wrapper' ).addClass( 'fusion-content-nowrap' );
					}

					return {
						render: false
					};

				case 'align_content':

					$theEl.find( '.fusion-column-wrapper' ).eq( 0 ).removeClass( function ( index, className ) {
						return ( className.match( /(^|\s)fusion-flex-justify-content-\S+/g ) || [] ).join( ' ' );
					} );
					$theEl.find( '.fusion-column-wrapper' ).eq( 0 ).addClass( 'fusion-flex-justify-content-' + value );

					return {
						render: false
					};
				}
		},

		/**
		 * Updates menu transition class.
		 *
		 * @param  {String} name  Param name.
		 * @param  {String} value Param value.
		 * @param  {Object} args  Args defined.
		 * @param  {Object} view  Element view.
		 * @return {Object}
		 */
		fusion_update_menu_transition: function( name, value, args, view ) {
			var oldValue  = view.model.get( 'params' )[ name ],
				queryData = view.model.get( 'query_data' ),
				searchRegex;

			if ( ! args.skip ) {
				view.changeParam( name, value );
			}

			// Replace class in actual HTML.
			view.$el.find( '.transition-' + oldValue ).removeClass( 'transition-' + oldValue ).addClass( 'transition-' + value );

			// Replace class in the stored markup in case they change another option.
			if ( 'undefined' !== typeof queryData && 'undefined' !== typeof queryData.menu_markup ) {
				searchRegex = new RegExp( 'transition-' + oldValue, 'g' );
				queryData.menu_markup = queryData.menu_markup.replace( searchRegex, 'transition-' + value );
				view.model.set( 'query_data', queryData );
			}

			return {
				render: false
			};
		},

		/**
		 * Updates column flex attributes.
		 *
		 * @param  {String} name  Param name.
		 * @param  {String} value Param value.
		 * @param  {Object} args  Args defined.
		 * @param  {Object} view  Element view.
		 * @return {Object}
		 */
		fusion_post_card_separator: function( name, value, args, view ) {
			var params    = view.model.get( 'params' ),
				markup,
				separatorArgs;

			if ( ! args.skip ) {
				view.changeParam( name, value );
			}

			// Find existing separator if exists and remove
			if ( view.$el.find( '.fusion-absolute-separator' ).length ) {
				view.$el.find( '.fusion-absolute-separator' ).remove();
			}

			// Separator args.
			separatorArgs = {
				'style_type': params.separator_style_type,
				'sep_color': params.separator_sep_color,
				'width': params.separator_width,
				'alignment': params.separator_alignment,
				'border_size': params.separator_border_size,
				'position': 'absolute'
			};

			// Update whatever changed.
			separatorArgs[ name.replace( 'separator_', '' ) ] = value;

			// Generate markup for element.
			markup = FusionPageBuilderApp.renderElement( 'fusion_separator', separatorArgs, '', view.model.get( 'cid' ) );

			// Append the new separator.
			view.$el.find( '.fusion-grid > li' ).append( markup );

			// Hide if should not be shown.
			if ( 'grid' !== params.layout || 1 < parseInt( params.columns ) ) {
				view.$el.find( '.fusion-absolute-separator' ).css( { display: 'none' } );
			}

			// Ensure to update markup for other changes.
			if ( 'undefined' !== typeof view.model.attributes.markup && 'undefined' !== typeof view.model.attributes.markup.output && 'undefined' === typeof view.model.attributes.query_data ) {
				view.model.attributes.markup.output = view.$el.find( '.fusion-builder-element-content' ).html();
			} else if ( 'undefined' !== typeof view.model.attributes.query_data && 'undefined' !== typeof view.model.attributes.query_data.loop_product ) {
				view.model.attributes.query_data.loop_product = view.$el.find( '.fusion-grid' ).html();
			}

			return {
				render: false
			};
		},

		/**
		 * Updates box shadow.
		 *
		 * @param  {String} name  Param name.
		 * @param  {String} value Param value.
		 * @param  {Object} args  Args defined.
		 * @param  {Object} view  Element view.
		 * @return {Object}
		 */
		fusion_update_box_shadow: function( name, value, args, view ) {
			var $theEl = view.$el;

			if ( ! args.skip ) {
				view.changeParam( name, value );
			}

			$theEl = ( 'undefined' === typeof args.selector ) ? view.$el : view.$el.find( args.selector );

			$theEl[ 0 ].style.removeProperty( 'box-shadow' );

			if ( 'yes' === view.model.attributes.params.box_shadow ) {
				$theEl[ 0 ].style.setProperty( 'box-shadow', _.fusionGetBoxShadowStyle( view.model.attributes.params ).replace( ';', '' ), 'important' );
			}

			return {
				render: false
			};

		},

		acf_get_field: function( args, image ) {
			if ( 'undefined' === typeof args.field || '' === args.field ) {
				return '';
			}

			return jQuery.ajax( {
				url: fusionAppConfig.ajaxurl,
				type: 'post',
				dataType: 'json',
				data: {
					action: 'ajax_acf_get_field',
					field: args.field,
					fusion_load_nonce: fusionAppConfig.fusion_load_nonce,
					post_id: FusionApp.getDynamicPost( 'post_id' ),
					image: 'undefined' !== typeof image ? image : false,
					cid: false
				}
			} )
			.done( function( response ) {
				FusionPageBuilderApp.dynamicValues.setValue( args, response.content );
			} );
		},

		acf_get_image_field: function( args ) {
			return this.acf_get_field( args, true );
		},

		fusion_gallery_image_ar_position: function( name, value, args, view ) {
			view.$el[ 0 ].querySelector( 'img' ).style.objectPosition = value;
			if ( ! args.skip ) {
				view.changeParam( name, value );
			}

			return {
				render: false
			};
		},
		fusion_gallery_image_masonry_position: function( name, value, args, view ) {
			view.$el[ 0 ].querySelector( '.fusion-masonry-element-container' ).style.backgroundPosition = value;
			if ( ! args.skip ) {
				view.changeParam( name, value );
			}

			return {
				render: false
			};
		}
	} );
}( jQuery ) );
