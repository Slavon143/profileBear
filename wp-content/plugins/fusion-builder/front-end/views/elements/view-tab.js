/* global FusionPageBuilderElements, fusionAllElements */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Tabs child View
		FusionPageBuilder.fusion_tab = FusionPageBuilder.ChildElementView.extend( {

			/**
			 * Runs during render() call.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			onRender: function() {
				if ( 'undefined' !== typeof this.model.attributes.selectors ) {
					this.model.attributes.selectors[ 'class' ] += ' ' + this.className;
					this.setElementAttributes( this.$el, this.model.attributes.selectors );
				}
			},

			/**
			 * Runs before view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			beforePatch: function() {
				var parentView = window.FusionPageBuilderViewManager.getView( this.model.get( 'parent' ) );

				parentView.getActiveTab();
			},

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			afterPatch: function() {
				var parentView = window.FusionPageBuilderViewManager.getView( this.model.get( 'parent' ) );

				this.updateExtraContents();

				if ( 'undefined' !== typeof this.model.attributes.selectors ) {
					this.model.attributes.selectors[ 'class' ] += ' ' + this.className;
					this.setElementAttributes( this.$el, this.model.attributes.selectors );
				}

				// Using non debounced version for smoothness.
				this.refreshJs();

				parentView._refreshJs();
			},

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {

				atts.tabsShortcodeTab      = this.buildTabsShortcodeTabAttr( atts.values );
				atts.tabsShortcodeIcon     = this.buildTabsShortcodeIconAttr( atts );
				atts.tabsShortcodeLink     = this.buildTabsShortcodeLinkAttr( atts.values );
				atts.justifiedClass        = this.setJustifiedClass( atts.values );

				atts.cid                   = this.model.get( 'cid' );
				atts.parent                = this.model.get( 'parent' );

				atts.parentValues          = this.getParentValues( atts );
				atts.output                = atts.values.element_content;

				return atts;
			},

			/**
			 * Check for the active tab.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			checkActive: function( activeTab ) {
				var parentView = window.FusionPageBuilderViewManager.getView( this.model.get( 'parent' ) );
				activeTab = activeTab || parentView.model.get( 'activeTab' );

				if ( 'undefined' !== typeof activeTab ) {
					if ( activeTab === this.model.get( 'cid' ) ) {
						this.$el.addClass( 'active' );
					} else {
						this.$el.removeClass( 'active' );
					}
				}
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @return {Object}
			 */
			buildTabsShortcodeTabAttr: function() {
				var tabsShortcodeTab;

				tabsShortcodeTab = {
					class: 'tab-pane fade fusion-extra-' + this.model.get( 'cid' )
				};

				tabsShortcodeTab.id = 'tabcid' + this.model.get( 'cid' );

				return tabsShortcodeTab;

			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			buildTabsShortcodeIconAttr: function( atts ) {
				var parentValues = atts.parentValues,
					values       = atts.values,

					// TabsShortcodeIcon Attributes.
					tabsShortcodeIcon = {
						class: 'fontawesome-icon ' + _.fusionFontAwesome( values.icon ),
						'aria-hidden': 'true'
					};

					tabsShortcodeIcon.style = '';

					if ( parentValues.icon_size ) {
						tabsShortcodeIcon.style += 'font-size: ' + parentValues.icon_size + 'px;';
					}

					const icon_color = values.icon_color ? values.icon_color : '';
					if ( icon_color ) {
						tabsShortcodeIcon.style += '--icon-color: ' + icon_color + ';';
					}

					const icon_active_color = values.icon_active_color ? values.icon_active_color : '';
					if ( icon_active_color ) {
						tabsShortcodeIcon.style += '--icon-active-color: ' + icon_active_color + ';';
					}

					return tabsShortcodeIcon;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			buildTabsShortcodeLinkAttr: function( values ) {

				// TabsShortcodeLink Attributes.
				var tabsShortcodeLink = {
						class: 'tab-link'
					},
					sanitizedTitle = 'string' === typeof values.title ? values.title.replace( /\s+/g, '' ).toLowerCase() : '';

				tabsShortcodeLink[ 'data-toggle' ] = 'tab';
				tabsShortcodeLink.id   = 'fusion-tab-' + sanitizedTitle;
				tabsShortcodeLink.href = '#tabcid' + this.model.get( 'cid' );

				return tabsShortcodeLink;
			},

			/**
			 * Set class.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {string}
			 */
			setJustifiedClass: function( values ) {
				var justifiedClass = '';

				if ( 'yes' === values.justified && 'vertical' !== values.layout ) {
					justifiedClass = ' nav-justified';
				}

				return justifiedClass;
			},

			/**
			 * Get parent values.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			getParentValues: function( atts ) {
				var parentModel = FusionPageBuilderElements.find( function( model ) {
					return model.get( 'cid' ) == atts.parent;
				} );

				var parentValues = jQuery.extend( true, {}, fusionAllElements.fusion_tabs.defaults, _.fusionCleanParameters( parentModel.get( 'params' ) ) );

				return parentValues;
			}
		} );
	} );
}( jQuery ) );
