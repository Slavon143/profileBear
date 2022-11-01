var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Tabs View.
		FusionPageBuilder.fusion_tabs = FusionPageBuilder.ParentElementView.extend( {

			/**
			 * Runs during render() call.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			onRender: function() {
				var $this = this;

				jQuery( window ).on( 'load', function() {
					$this._refreshJs();
				} );
			},

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			afterPatch: function() {
				var self     = this,
					children = window.FusionPageBuilderViewManager.getChildViews( this.model.get( 'cid' ) );

				this.appendChildren( '.nav-tabs' );

				_.each( children, function( child ) {
					self.appendContents( child );
				} );

				this._refreshJs();
			},

			refreshJs: function() {
				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).trigger( 'fusion-element-render-fusion_tabs', this.model.attributes.cid );

				this.checkActiveTab();
			},

			/**
			 * Find the active tab.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			getActiveTab: function() {
				var self     = this,
					children = window.FusionPageBuilderViewManager.getChildViews( this.model.get( 'cid' ) );

				_.each( children, function( child ) {
					if ( child.$el.hasClass( 'active' ) ) {
						self.model.set( 'activeTab', child.model.get( 'cid' ) );
					}
				} );
			},

			/**
			 * Set tab as active.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			checkActiveTab: function() {
				var self = this,
					children = window.FusionPageBuilderViewManager.getChildViews( this.model.get( 'cid' ) ),
					activeTab = this.model.get( 'activeTab' ) || self.$el.find( '.nav-tabs li.active' ).data( 'cid' );

				if ( 'undefined' !== activeTab ) {
					_.each( children, function( child ) {
						child.checkActive( activeTab );
					} );
					self.$el.find( '.fusion-extra-' + activeTab ).addClass( 'active in' );
				} else {
					_.each( children, function( child ) {
						if ( child.isFirstChild() ) {
							const tabPane = self.$el.find( '.fusion-extra-' + child.model.get( 'cid' ) );
							const tabLi = self.$el.find( 'a[href="#' + tabPane.attr( 'id' ) + '"]' ).parent( 'li' );
							tabPane.addClass( 'active in' );
							tabLi.addClass( 'active' );

						}
					} );
				}
			},

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {Object} - Returns the attributes.
			 */
			filterTemplateAtts: function( atts ) {

				// Create attribute objects.
				atts.tabsShortcode   = this.buildTabsShortcodeAttrs( atts.values );
				atts.styleTag        = this.buildStyleTag( atts.values );
				atts.justifiedClass  = this.setJustifiedClass( atts.values );

				this.model.set( 'first', true );

				atts.cid             = this.model.get( 'cid' );
				return atts;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {Object} - Returns the shortcode object.
			 */
			buildTabsShortcodeAttrs: function( values ) {

				// TabsShortcode  Attributes.
				var tabsShortcode = _.fusionVisibilityAtts( values.hide_on_mobile, {
					class: 'fusion-tabs fusion-tabs-cid' + this.model.get( 'cid' ) + ' ' + values.design,
					style: ''
				} );

				if ( 'yes' !== values.justified && 'vertical' !== values.layout ) {
					tabsShortcode[ 'class' ] += ' nav-not-justified';
				}

				if ( '' !== values.icon_position ) {
					tabsShortcode[ 'class' ] += ' icon-position-' + values.icon_position;
				}

				if ( '' !== values[ 'class' ] ) {
					tabsShortcode[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.mobile_mode ) {
					tabsShortcode[ 'class' ] += ' mobile-mode-' + values.mobile_mode;
				}

				if ( 'carousel' === values.mobile_mode && 'yes' === values.mobile_sticky_tabs ) {
					tabsShortcode[ 'class' ] += ' mobile-sticky-tabs';
				}

				tabsShortcode[ 'class' ] += ( 'vertical' === values.layout ) ? ' vertical-tabs' : ' horizontal-tabs';

				if ( 'no' == values.show_tab_titles ) {
					tabsShortcode[ 'class' ] += ' woo-tabs-hide-headings';
				}

				if ( '' !== values.id ) {
					tabsShortcode.id = values.id;
				}

				// Icon color.
				tabsShortcode.style += '' !== values.icon_color ? '--icon-color:' + values.icon_color + ';' : '';

				// Active icon color.
				tabsShortcode.style += '' !== values.active_icon_color ? '--icon-active-color:' + values.active_icon_color + ';' : '';


				return tabsShortcode;
			},

			/**
			 * Builds styles.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {string} - Returns styles as a string.
			 */
			buildStyleTag: function( values ) {
				var cid    = this.model.get( 'cid' ),
					styles = '.fusion-tabs.fusion-tabs-cid' + cid + ' .nav-tabs li a.tab-link{border-top-color:' + values.inactivecolor + ';background-color:' + values.inactivecolor + ';}';

				if ( 'clean' !== values.design ) {
					styles += '.fusion-tabs.fusion-tabs-cid' + cid + ' .nav-tabs{background-color:' + values.backgroundcolor + ';}';
					styles += '.fusion-tabs.fusion-tabs-cid' + cid + ' .nav-tabs li.active a.tab-link,.fusion-tabs.fusion-tabs-cid' + cid + ' .nav-tabs li.active a.tab-link:hover,.fusion-tabs.fusion-tabs-cid' + cid + ' .nav-tabs li.active a.tab-link:focus{border-right-color:' + values.backgroundcolor + ';}';
				} else {
					styles = '#wrapper .fusion-tabs.fusion-tabs-cid' + cid + '.clean .nav-tabs li a.tab-link{border-color:' + values.bordercolor + ';}.fusion-tabs.fusion-tabs-cid' + cid + ' .nav-tabs li a.tab-link{background-color:' + values.inactivecolor + ';}';
				}

				if ( 'classic' === values.design && '' !== values.active_border_color ) {
					styles += '.fusion-tabs.fusion-tabs-cid' + cid + '.classic .nav-tabs > li.active .tab-link, .fusion-tabs.fusion-tabs-' + cid + '.classic .nav-tabs > li.active .tab-link:hover { border-color: ' + values.active_border_color + ';}';
				}
				styles += '.fusion-tabs.fusion-tabs-cid' + cid + ' .nav-tabs li.active a.tab-link,.fusion-tabs.fusion-tabs-cid' + cid + ' .nav-tabs li.active a.tab-link:hover,.fusion-tabs.fusion-tabs-cid' + cid + ' .nav-tabs li.active a.tab-link:focus{background-color:' + values.backgroundcolor + ';}';
				styles += '.fusion-tabs.fusion-tabs-cid' + cid + ' .nav-tabs li a.tab-link:hover{background-color:' + values.backgroundcolor + ';border-top-color:' + values.backgroundcolor + ';}';
				styles += '.fusion-tabs.fusion-tabs-cid' + cid + ' .tab-pane{background-color:' + values.backgroundcolor + ';}';
				styles += '.fusion-tabs.fusion-tabs-cid' + cid + ' .nav,.fusion-tabs.fusion-tabs-cid' + cid + ' .nav-tabs,.fusion-tabs.fusion-tabs-cid' + cid + ' .tab-content .tab-pane{border-color:' + values.bordercolor + ';}';

				// Tabs Alignment.
				if ( 'no' === values.justified && 'vertical' !== values.layout ) {
					styles += '' !==  values.alignment ? '.fusion-tabs.fusion-tabs-cid' + cid + ' .nav:not(.fusion-mobile-tab-nav){ display:flex; justify-content:' + values.alignment + ';}' : '';

					if ( 'accordion' === values.mobile_mode || 'toggle' === values.mobile_mode ) {
						const content_media_query = `@media only screen and (max-width: ${window.FusionApp.settings.content_break_point}px)`;
						styles +=  content_media_query + '{ .fusion-tabs.fusion-tabs-cid' + cid + ' .nav:not(.fusion-mobile-tab-nav){display:none;} }';
					}
				}

				// Title typography.
				let title_typography = '';
				title_typography += _.fusionGetFontStyle( 'title_font', values, 'string', true );
				title_typography += '' !== values.title_font_size ? 'font-size: ' + _.fusionGetValueWithUnit( values.title_font_size ) + ' !important;' : '';
				title_typography += '' !== values.title_line_height ? 'line-height: ' + values.title_line_height + ' !important;' : '';
				title_typography += '' !== values.title_letter_spacing ? 'letter-spacing: ' + _.fusionGetValueWithUnit( values.title_letter_spacing ) + ' !important;' : '';
				title_typography += '' !== values.title_text_transform ? 'text-transform: ' + values.title_text_transform + ' !important;' : '';
				title_typography += '' !== values.title_text_color ? 'color: ' + values.title_text_color + ' !important;' : '';
				styles += '' !== title_typography ? '.fusion-tabs.fusion-tabs-cid' + cid + ' .nav-tabs li .fusion-tab-heading{' + title_typography + '}' : '';

				// Active title color.
				styles += '' !== values.title_active_text_color ? '.fusion-tabs.fusion-tabs-cid' + cid + ' .nav-tabs li.active .fusion-tab-heading, .fusion-tabs.fusion-tabs-cid' + cid + ' .nav-tabs li:hover .fusion-tab-heading{ color: ' + values.title_active_text_color + ' !important;}' : '';

				// Title padding.
				let title_padding = '';
				title_padding += '' !== values.title_padding_top ? 'padding-top: ' + _.fusionGetValueWithUnit( values.title_padding_top ) + ' !important;' : '';
				title_padding += '' !== values.title_padding_right ? 'padding-right: ' + _.fusionGetValueWithUnit( values.title_padding_right ) + ' !important;' : '';
				title_padding += '' !== values.title_padding_bottom ? 'padding-bottom: ' + _.fusionGetValueWithUnit( values.title_padding_bottom ) + ' !important;' : '';
				title_padding += '' !== values.title_padding_left ? 'padding-left: ' + _.fusionGetValueWithUnit( values.title_padding_left ) + ' !important;' : '';
				styles += '' !== title_padding ? '.fusion-tabs.fusion-tabs-cid' + cid + ' .nav-tabs li .tab-link {' + title_padding + '}' : '';

				// Content padding.
				let content_padding = '';
				content_padding += '' !== values.content_padding_top ? 'padding-top: ' + _.fusionGetValueWithUnit( values.content_padding_top ) + ' !important;' : '';
				content_padding += '' !== values.content_padding_right ? 'padding-right: ' + _.fusionGetValueWithUnit( values.content_padding_right ) + ' !important;' : '';
				content_padding += '' !== values.content_padding_bottom ? 'padding-bottom: ' + _.fusionGetValueWithUnit( values.content_padding_bottom ) + ' !important;' : '';
				content_padding += '' !== values.content_padding_left ? 'padding-left: ' + _.fusionGetValueWithUnit( values.content_padding_left ) + ' !important;' : '';
				styles += '' !== content_padding ? '.fusion-tabs.fusion-tabs-cid' + cid + ' .tab-content .tab-pane {' + content_padding + '}' : '';

				// Margin.
				styles += this.buildMarginStyles( values );

				styles = '<style type="text/css">' + styles + '</style>';
				return styles;
			},

			/**
			 * Set class.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {string} - Returns a string containing the CSS classes.
			 */
			setJustifiedClass: function( values ) {
				var justifiedClass = '';

				if ( 'yes' === values.justified && 'vertical' !== values.layout ) {
					justifiedClass = ' nav-justified';
				}

				return justifiedClass;
			},

			/**
			 * Builds margin styles.
			 *
			 * @since 3.5
			 * @param {Object} values - The values object.
			 * @return {string}
			 */
			buildMarginStyles: function( values ) {
				var extras = jQuery.extend( true, {}, window.fusionAllElements.fusion_imageframe.extras ),
					elementSelector = '.fusion-tabs.fusion-tabs-cid' + this.model.get( 'cid' ),
					responsiveStyles = '';

				_.each( [ 'large', 'medium', 'small' ], function( size ) {
					var marginStyles = '',
						marginKey;

					_.each( [ 'top', 'right', 'bottom', 'left' ], function( direction ) {

						// Margin.
						marginKey = 'margin_' + direction + ( 'large' === size ? '' : '_' + size );
						if ( '' !== values[ marginKey ] ) {
							marginStyles += 'margin-' + direction + ' : ' + _.fusionGetValueWithUnit( values[ marginKey ] ) + ';';
						}

					} );

					if ( '' === marginStyles ) {
						return;
					}

					// Wrap CSS selectors
					if ( '' !== marginStyles ) {
						marginStyles = elementSelector + ' {' + marginStyles + '}';
					}

					// Large styles, no wrapping needed.
					if ( 'large' === size ) {
						responsiveStyles += marginStyles;
					} else {
						// Medium and Small size screen styles.
						responsiveStyles += '@media only screen and (max-width:' + extras[ 'visibility_' + size ] + 'px) {' + marginStyles + '}';
					}
				} );

				return responsiveStyles;
			},

			onInit: function() {
				var params = this.model.get( 'params' );

				// Check for newer margin params.  If unset but regular is, copy from there.
				if ( 'object' === typeof params ) {

					// Split border width into 4.
					if ( 'undefined' === typeof params.alignment && 'clean' === params.design ) {
						params.alignment = 'center';
					}

					this.model.set( 'params', params );
				}

				this.listenTo( window.FusionEvents, 'fusion-preview-viewport-update', this.onChangeDevice );

			},
			onChangeDevice() {
				const device = jQuery( '.viewport-indicator span.active' ).data( 'indicate-viewport' );
				const values = this.model.get( 'params' );
				// Hide active toggle content panels from desktop.
				if ( 'toggle' === values.mobile_mode && 'desktop' === device ) {
					const activeId = this.$el.find( '.nav li.active' ).data( 'cid' );

					this.$el.find( '.tab-pane' ).removeClass( 'active in' );
					this.$el.find( '.tab-pane#tabcid' + activeId ).addClass( 'active in' );

					this.$el.find( '.fusion-mobile-tab-nav li' ).removeClass( 'active' );
					this.$el.find( '.fusion-mobile-tab-nav li a[href="#tabcid' + activeId + '"]' ).parent().addClass( 'active' );
				}
			}

		} );
	} );
}( jQuery ) );
