/* global FusionPageBuilderViewManager, fusionAllElements, FusionEvents, FusionApp */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

    FusionPageBuilder.Navigator = Backbone.Model.extend( {

        defaults: {
            // see _getNavItemObject() function for this object format.
            navigatorItems: null,
            // An object with the views that are collapsed.
            collapsedItems: {},
            // If is either a page/column/element... etc. library navigator. This will not display some buttons.
            navigatorType: ''
        },

        verifiedNestedRowsForCollapse: {},

        initialize: function() {
            this.update = _.debounce( this.update.bind( this ), 20 );

            // Used for nested columns added.
            this.listenTo( FusionEvents, 'fusion-columns-added', this.update );
            this.listenTo( FusionEvents, 'fusion-content-changed', this.update );
            this.listenTo( FusionEvents, 'fusion-element-removed', this.update );
            this.listenTo( FusionEvents, 'fusion-column-resized', this.update );

            this.listenTo( FusionEvents, 'fusion-cancel-nested-row-changes', this.restoreNestedRowCollapsible );
        },

        /**
         * Update all the attributes. This will also trigger render if necessary.
         */
        update: function() {
            var navigatorItems = this.getNavigatorItems();
            this.setNavigatorType();
            this.setNewNestedRowsToCollapsible( navigatorItems );

            // this will render if it's changed.
            this.set( 'navigatorItems', navigatorItems );
        },

        getNavigatorItems: function() {
            var elementViews = FusionPageBuilderViewManager.getViews(),
                index,
                navItems = [],
                isContainerView;

            this._updateModelsToViewsMap( elementViews );

            for ( index in elementViews ) {
                if ( 'object' !== typeof elementViews[ index ] ) {
                    continue; // eslint-disable-line no-continue
                }

                isContainerView = 'fusion_builder_container' === elementViews[ index ].model.get( 'element_type' );
                // Construct all the containers, with their children in order.
                // Only the children are in order, and not the containers themselves.
                if ( isContainerView ) {
                    navItems.push( this._getNavItemObject( elementViews[ index ] ) );
                }
            }

            navItems = this._orderByDisplayedContainers( navItems );

            return navItems;
        },

        setNavigatorType: function() {
            this.set( 'navigatorType', FusionApp.data.fusion_element_type );
        },

        setNewNestedRowsToCollapsible( navigatorItems ) {
            var i, j, k, l,
                item,
                collapsedItems = this.get( 'collapsedItems' );

            if ( ! Array.isArray( navigatorItems ) ) {
                return;
            }

            for ( i = 0; i < navigatorItems.length; i++ ) { // containers
                for ( j = 0; j < navigatorItems[ i ].children.length; j++ ) { // rows
                    for ( k = 0; k < navigatorItems[ i ].children[ j ].children.length; k++ ) { // columns
                        for ( l = 0; l < navigatorItems[ i ].children[ j ].children[ k ].children.length; l++ ) { // elements or nested rows.
                            item = navigatorItems[ i ].children[ j ].children[ k ].children[ l ];

                            // eslint-disable-next-line max-depth
                            if (
                                'fusion_builder_row_inner' === item.type &&
                                'undefined' === typeof this.verifiedNestedRowsForCollapse[ item.view.cid ]
                            ) {
                                this.verifiedNestedRowsForCollapse[ item.view.cid ] = true;
                                collapsedItems[ item.view.cid ] = true;
                            }
                        }
                    }
                }
            }

            this.set( 'collapsedItems', collapsedItems );
        },


        /**
         * When a nested row is canceled from editing, the view cid changes,
         * so that the collapsible refreshes to default state of collapsed.
         * */
        restoreNestedRowCollapsible( data ) {
            var oldStatus,
                collapsedItems = this.get( 'collapsedItems' );

            if ( 'object' !== typeof data.oldView || ! data.oldView.cid || 'object' !== typeof data.newView || ! data.newView.cid ) {
                return;
            }

            oldStatus = collapsedItems[ data.oldView.cid ] ? collapsedItems[ data.oldView.cid ] : false;

            if ( ! oldStatus ) {
                this.verifiedNestedRowsForCollapse[ data.newView.cid ] = true;
            } else {
                this.verifiedNestedRowsForCollapse[ data.newView.cid ] = true;
                collapsedItems[ data.newView.cid ] = true;
            }

            this.set( 'collapsedItems', collapsedItems );
        },

        _updateModelsToViewsMap: function( elementViews ) {
            var index;

            this.modelsToViewsMap = [];
            this.viewsCidToViewsMap = [];

            for ( index in elementViews ) {
                if ( 'object' !== typeof elementViews[ index ] ) {
                    continue; // eslint-disable-line no-continue
                }

               this.modelsToViewsMap[ elementViews[ index ].model.cid ] = elementViews[ index ];
               this.viewsCidToViewsMap[ elementViews[ index ].cid ] = elementViews[ index ];
            }
        },

        _getNavItemObject: function( view ) {
            return {
                view: view,
                model: view.model,
                name: this._getElementDisplayName( view ),
                type: view.model.get( 'element_type' ),

                // An array with items of the same type as this.
                children: this._getNavigationChildrenItems( view )
            };
        },

        _getNavigationChildrenItems: function( view ) {
            var model,
                children = [],
                childView,
                i;

            if ( ! view.model || ! view.model.children || ! view.model.children.length ) {
                return children;
            }

            for ( i = 0; i < view.model.children.length; i++ ) {
                model = view.model.children.models[ i ];

                if ( 'object' === typeof this.modelsToViewsMap[ model.cid ] && 'multi_element_child' !== model.get( 'multi' ) ) {
                    childView = this.modelsToViewsMap[ model.cid ];
                    children.push( this._getNavItemObject( childView ) );
                }
            }

            return children;
        },

        _orderByDisplayedContainers: function( navItems ) {
            var previewIframe = jQuery( '#fb-preview' ),
                containers,
                orderedItems = [],
                j,
                index;

            if ( ! previewIframe.length ) {
                return navItems;
            }

            containers = previewIframe[ 0 ].contentWindow.jQuery( '#fusion_builder_container > .fusion-builder-container' );

            for ( index = 0; index < containers.length; index++ ) {
                for ( j = 0; j < navItems.length; j++ ) {
                    if ( navItems[ j ].view.$el.is( containers.eq( index ) ) ) {
                        orderedItems.push( navItems[ j ] );
                    }
                }
            }

            return orderedItems;
        },

        _getElementDisplayName: function( view ) {
            var elType = view.model.get( 'element_type' ),
                params;

            // If element is container, the use the admin label.
            if ( 'fusion_builder_container' === elType ) {
                params = view.model.get( 'params' );
                if ( params.admin_label ) {
                    return params.admin_label;
                }
            }

            // Return the element name.
            if ( fusionAllElements[ elType ] && fusionAllElements[ elType ].name ) {
                return fusionAllElements[ elType ].name;
            }

            return elType;
        }

    } );
}( jQuery ) );
