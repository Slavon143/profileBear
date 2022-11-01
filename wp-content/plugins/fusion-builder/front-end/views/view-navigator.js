/* global FusionPageBuilderApp, diffDOM */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

    FusionPageBuilder.NavigatorView = Backbone.View.extend( {
        template: FusionPageBuilder.template( jQuery( '#front-end-navigator-template' ).html() ),

        events: {
            'click .awb-builder-nav__collapse-btn': 'handleItemCollapse',
            'click .awb-builder-nav__item-header': 'handleItemNameClick',
            'click .awb-builder-nav__btn-edit': 'handleEditBtnClick',
            'click .awb-builder-nav__btn-remove': 'handleRemoveBtnClick',
            'click .awb-builder-nav__btn-clone': 'handleCloneBtnClick',
            'click .awb-builder-nav__btn-add': 'handleAddBtnClick',
            'click .awb-builder-nav__add-inside': 'handleAddInsideBtnClick'
        },

        originalItemPosition: null,

        originalPlaceholderThatStaysInPlace: jQuery(),

        initialize: function() {
            this.render = _.debounce( this.render.bind( this ), 20 );
            this.model.bind( 'change:navigatorItems', this.render, this );

            this.toggleCollapseAllContainers = _.debounce( this.toggleCollapseAllContainers.bind( this ), 500, true );
            jQuery( document ).on( 'click', '.awb-navigator-toggle-containers', this.toggleCollapseAllContainers );
        },

        render: function() {
            var dd = new diffDOM(),
                currentHTML,
                diff,
                template,
                newHTML;

            template = this.template( this.model.attributes );

            // First render, just set el and return.
            if ( ! jQuery( this.el ).is( '.awb-builder-nav-wrapper' ) ) {
                this.setElement( jQuery( '.awb-builder-nav-wrapper' ) );
                this.$el.html( template );
            } else {
                currentHTML = this.$el.find( '.awb-builder-nav' );
                newHTML = jQuery( template );

                if ( currentHTML.length && newHTML.length ) {
                    diff = dd.diff( currentHTML[ 0 ], newHTML[ 0 ] );
                    dd.apply( currentHTML[ 0 ], diff );
                }
            }

            // This doesn't need a timeout... but is better in it.
            setTimeout( this.makeItemsSortable.bind( this ), 150 );

            return this;
        },

        makeItemsSortable: function() {
            var allLists = this.getAllSortableList(),
                sortableOptions;

            sortableOptions = {
                start: this.handleSortStart.bind( this ),
                stop: this.handleSortStop.bind( this ),
                change: this.handleChange.bind( this ),
                cancel: 'button',
                dropOnEmpty: true,
                axis: 'y',
                cursor: 'move',
                cursorAt: { top: 15 },
                // These 2(delay & distance) are deprecated, but makes user experience better.
                delay: 150,
                distance: 5,
                // Edge Case: If we have a column with lots of elements inside, not collapsing it would be much harder to sort.
                helper: this.createSortHelper.bind( this ),
                placeholder: 'awb-builder-nav__sortable-placeholder',
                scrollSpeed: 7 // Make scroll slower, to not jump everywhere when people sort.
            };

            allLists.sortable( sortableOptions );
        },

        createSortHelper: function( e, $el ) {
            var newHeight = $el.children( '.awb-builder-nav__item-header' ).outerHeight();
            var childrenList  = $el.children( '.awb-builder-nav__list-submenu' );

            this.scrollTopBeforeBegin = $el.closest( '.awb-builder-nav' ).scrollTop();

            if ( ! $el.hasClass( 'awb-builder-nav__list-item--collapsed' ) ) {
                $el.css( 'height', newHeight );
                childrenList.hide();
            }

            return $el;
        },

        handleSortStart: function( event, ui ) {
            var target = jQuery( event.target ),
                columnsMenus,
                nestedColumnsMenus,
                allColumnElements,
                allNestedColumnElements,
                rowColumnsMenu,
                itemType = getItemType();

            // Make placeholder similar with the item style.
            ui.placeholder.addClass( 'awb-builder-nav__list-item awb-builder-nav__list-item--' + itemType );
            ui.placeholder.append( '<div class="awb-builder-nav__item-header awb-builder-nav__item-header--' + itemType + '"><div class="awb-builder-nav__btn-spacer"></div><div class="awb-builder-nav__item-name"></div></div>' );

            // Used to remember the starting position.
            this.originalItemPosition = this.getCurrentPositionOfItem( ui.item );

            // Append an item that looks exactly the same as the dragged item, and hide the placeholder here as we will highlight this item.
            this.originalPlaceholderThatStaysInPlace = ui.item.clone(); // clone the original placeholder marker.
            this.originalPlaceholderThatStaysInPlace.children( '.awb-builder-nav__list-submenu' ).show(); // show the collapsed children from helper.
            this.originalPlaceholderThatStaysInPlace.removeAttr( 'style' ).removeAttr( 'data-awb-view-cid' ).addClass( 'awb-builder-nav__item-is-placeholder awb-builder-nav__item-is-placeholder-over' );
            this.originalPlaceholderThatStaysInPlace.insertAfter( ui.item );
            ui.placeholder.css( 'display', 'none' );

            // Edge Case: Make scroll the same as before, if the item sorted is one of the last. Occurs when sorted item height is big, and the navigator is scrolled to bottom.
            ui.item.closest( '.awb-builder-nav' ).scrollTop( this.scrollTopBeforeBegin );

            // ConnectsWith Property need to be dynamically generated, because static creates some issues:
            // 1. By default all elements can be sorted inside normal column and nested columns.
            // But nested rows(which should be sorted as elements), should not be able to put
            // inside nested columns(as an element does).
            // 2. If the menus that are sortable(especially long menus) are collapsed, then sometimes the sorting will not work good.
            if ( 'nested-row' === itemType ) {
                rowColumnsMenu = this.$el.find( '.awb-builder-nav__list-item--column > .awb-builder-nav__list-submenu' );
                target.sortable( 'option', 'connectWith', filterIfParentIsCollapsed( rowColumnsMenu ) );
            } else if ( 'nested-column' === itemType ) {
                nestedColumnsMenus = this.$el.find( '.awb-builder-nav__list-item--nested-row > .awb-builder-nav__list-submenu' );
                target.sortable( 'option', 'connectWith', filterIfParentIsCollapsed( nestedColumnsMenus ) );
            } else if ( 'column' === itemType ) {
                columnsMenus = this.$el.find( '.awb-builder-nav__list-item--container > .awb-builder-nav__list-submenu' );
                target.sortable( 'option', 'connectWith', filterIfParentIsCollapsed( columnsMenus ) );
            } else if ( 'element' === itemType ) {
                allColumnElements = this.$el.find( '.awb-builder-nav__list-item--column > .awb-builder-nav__list-submenu' ),
                allNestedColumnElements = this.$el.find( '.awb-builder-nav__list-item--nested-column > .awb-builder-nav__list-submenu' );
                target.sortable( 'option', 'connectWith', filterIfParentIsCollapsed( allColumnElements.add( allNestedColumnElements ) ) );
            }

            target.sortable( 'refresh' );

            function getItemType() {
                if ( ui.item.hasClass( 'awb-builder-nav__list-item--container' ) ) {
                    return 'container';
                }
                if ( ui.item.hasClass( 'awb-builder-nav__list-item--nested-row' ) ) {
                    return 'nested-row';
                }
                if ( ui.item.hasClass( 'awb-builder-nav__list-item--nested-column' ) ) {
                    return 'nested-column';
                }
                if ( ui.item.hasClass( 'awb-builder-nav__list-item--column' ) ) {
                    return 'column';
                }
                if ( ui.item.hasClass( 'awb-builder-nav__list-item--element' ) ) {
                    return 'element';
                }

                return 'element';
            }

            function filterIfParentIsCollapsed( $items ) {
                return $items.filter( function() {
                    if ( jQuery( this ).parents( '.awb-builder-nav__list-item--collapsed' ).length ) {
                        return false;
                    }
                    return true;
                } );
            }
        },

        handleSortStop: function( event, ui ) {
            var viewCid = ui.item.attr( 'data-awb-view-cid' ),
                itemChildrenList  = ui.item.children( '.awb-builder-nav__list-submenu' ),
                nearbyObj,
                view;

            // Remove CSS added in Helper.
            if ( ! ui.item.hasClass( 'awb-builder-nav__list-item--collapsed' ) ) {
                ui.item.css( 'height', 'auto' );
                itemChildrenList.show();
            }

            // Remove original position helper:
            this.originalPlaceholderThatStaysInPlace.remove();
            this.originalPlaceholderThatStaysInPlace = jQuery();

            nearbyObj = this._getNearbyItemToDropAndTarget( ui.item );

            ui.item.closest( '.awb-builder-nav__list-main' ).find( '.awb-builder-nav__list-item--no-children-expanded' ).removeClass( 'awb-builder-nav__list-item--no-children-expanded' );

            if ( ! this.model.viewsCidToViewsMap[ viewCid ] || ! nearbyObj ) {
                return;
            }
            view = this.model.viewsCidToViewsMap[ viewCid ];

            if ( 'in' === nearbyObj.targetPosition ) { // If the item dropped doesn't currently have any siblings.
                if ( nearbyObj.view.handleNestedColumnDropInsideRow ) {
                    nearbyObj.view.handleNestedColumnDropInsideRow( view.$el, nearbyObj.view.$el );
                } else if ( nearbyObj.view.handleElementDropInsideColumn ) {
                    nearbyObj.view.handleElementDropInsideColumn( view.$el, nearbyObj.view.$el );
                } else if ( nearbyObj.view.handleColumnDropInsideRow ) {
                    nearbyObj.view.handleColumnDropInsideRow( view.$el, nearbyObj.view.$el );
                }

                // Prevent flickering by removing the no-children class.
                nearbyObj.item.removeClass( 'awb-builder-nav__list-item--no-children' );
            } else {
                if ( view.handleDropContainer ) { // eslint-disable-line no-lonely-if
                    view.handleDropContainer( view.$el, nearbyObj.view.$el, nearbyObj.dropTarget );
                } else if ( view.handleRowNestedDrop ) {
                    view.handleRowNestedDrop( view.$el, nearbyObj.view.$el, nearbyObj.dropTarget );
                } else if ( view.handleColumnNestedDrop ) {
                    view.handleColumnNestedDrop( view.$el, nearbyObj.view.$el, nearbyObj.dropTarget );
                } else if ( view.handleDropColumn ) {
                    view.handleDropColumn( view.$el, nearbyObj.view.$el, nearbyObj.dropTarget );
                } else if ( view.handleDropElement ) { // Drop element needs to be last, because column/container also can have this function.
                    view.handleDropElement( view.$el, nearbyObj.view.$el, nearbyObj.dropTarget );
                }
            }

            // Prevent flickering by adding the no-children class if needed.
            if ( ! this.originalItemPosition.before.length && ! this.originalItemPosition.after.length ) {
                if ( 0 === this.originalItemPosition.parent.children().length ) { // just make sure that it has no children again.
                    this.originalItemPosition.parent.closest( '.awb-builder-nav__list-item' ).addClass( 'awb-builder-nav__list-item--no-children' );
                }
            }

            this.model.update();
        },

        handleChange: function( event, ui ) {
            if ( this.itemInOriginalPosition( ui.placeholder ) ) {
                ui.placeholder.css( 'display', 'none' );
                this.originalPlaceholderThatStaysInPlace.addClass( 'awb-builder-nav__item-is-placeholder-over' );
            } else {
                ui.placeholder.css( 'display', 'block' );
                this.originalPlaceholderThatStaysInPlace.removeClass( 'awb-builder-nav__item-is-placeholder-over' );
            }
        },

        itemInOriginalPosition( $elem ) {
            var currentPosition = this.getCurrentPositionOfItem( $elem );

            if (
                currentPosition.before.length !== this.originalItemPosition.before.length ||
                currentPosition.after.length !== this.originalItemPosition.after.length ||
                currentPosition.parent.length !== this.originalItemPosition.parent.length
            ) {
                return false;
            }

            if ( 1 === currentPosition.parent.length && currentPosition.before[ 0 ] !== this.originalItemPosition.before[ 0 ] ) {
                return false;
            }

            if ( 1 === currentPosition.parent.length && currentPosition.after[ 0 ] !== this.originalItemPosition.after[ 0 ] ) {
                return false;
            }

            if ( 1 === currentPosition.parent.length && currentPosition.parent[ 0 ] !== this.originalItemPosition.parent[ 0 ] ) {
                return false;
            }

            return true;
        },

        getCurrentPositionOfItem( $elem ) {
            return {
                before: $elem.prevAll( '[data-awb-view-cid]:not(.awb-builder-nav__sortable-placeholder):not(.ui-sortable-helper)' ).first(),
                after: $elem.nextAll( '[data-awb-view-cid]:not(.awb-builder-nav__sortable-placeholder):not(.ui-sortable-helper)' ).first(),
                parent: $elem.parent()
            };
        },

        /**
         * Hide/Show Navigation item children. This updates the internal state,
         * but does not need to render it, as the collapsed animation is handled
         * by jquery. The collapsed state is needed for next render.
         */
        handleItemCollapse: function( e ) {
            var item = jQuery( e.currentTarget ).closest( '.awb-builder-nav__list-item' ),
                list = item.children( '.awb-builder-nav__list-submenu' ),
                addInsideBtn = item.children( '.awb-builder-nav__add-inside' ),
                viewCid = item.attr( 'data-awb-view-cid' ),
                collapsed = Object.assign( {}, this.model.get( 'collapsedItems' ) );

            if ( 'string' !== typeof viewCid ) {
                return;
            }

            if ( 'undefined' !== typeof collapsed[ viewCid ] ) {
                delete collapsed[ viewCid ];
                list.slideDown( 250 );
                addInsideBtn.slideDown( 250 );
                setTimeout( function() {
                    item.removeClass( 'awb-builder-nav__list-item--collapsed' );
                }, 10 );
            } else {
                collapsed[ viewCid ] = true;
                item.addClass( 'awb-builder-nav__list-item--collapsing' );
                item.addClass( 'awb-builder-nav__list-item--collapsed' );
                list.slideUp( 250, function() {
                    item.removeClass( 'awb-builder-nav__list-item--collapsing' );
                } );
                addInsideBtn.slideUp( 250 );
            }

            this.model.set( 'collapsedItems', collapsed );
        },

        handleEditBtnClick: function( e ) {
            var item = jQuery( e.currentTarget ).closest( '.awb-builder-nav__list-item' ),
                viewCid = item.attr( 'data-awb-view-cid' ),
                type,
                view;

            if ( ! this.model.viewsCidToViewsMap || 'object' !== typeof this.model.viewsCidToViewsMap[ viewCid ] ) {
                return;
            }

            view = this.model.viewsCidToViewsMap[ viewCid ];

            this.openOrCloseNestedRowsNeeded( view );

            type = view.model.get( 'element_type' );
            if ( 'fusion_builder_row_inner' === type && item.hasClass( 'awb-builder-nav__list-item--collapsed' ) ) {
                item.children( '.awb-builder-nav__item-header' ).children( '.awb-builder-nav__collapse-btn' ).trigger( 'click' );
            }

            if ( view.settings ) {
                view.settings();
            }
        },

        handleItemNameClick: function( e ) {
            var item = jQuery( e.currentTarget ).closest( '.awb-builder-nav__list-item' ),
                view,
                viewCid = item.attr( 'data-awb-view-cid' );

            if ( ! viewCid ) {
                return;
            }

            // Do not outline if an action button inside is clicked.
            if ( jQuery( e.target ).closest( '.awb-builder-nav__btn-remove, .awb-builder-nav__btn-clone, .awb-builder-nav__btn-add, .awb-builder-nav__collapse-btn' ).length ) {
                this.removeItemOutline();
                return;
            }

            view = this.model.viewsCidToViewsMap[ viewCid ];

            this.outlineItem( view );
            this.scrollToViewIfNecessary( view );
        },

        handleRemoveBtnClick: function( e ) {
            var item = jQuery( e.currentTarget ).closest( '.awb-builder-nav__list-item' ),
                view,
                viewCid = item.attr( 'data-awb-view-cid' );

            if ( ! viewCid ) {
                return;
            }
            view = this.model.viewsCidToViewsMap[ viewCid ];

            this.openOrCloseNestedRowsNeeded( view, true );

            if ( view.removeContainer ) {
                view.removeContainer( undefined, undefined, true );
            } else if ( view.removeRow ) {
                view.removeRow( undefined, true );
            } else if ( view.removeColumn ) {
                view.removeColumn( undefined, true );
            } else if ( view.removeElement ) {
                view.removeElement( undefined, undefined, true );
            }
        },

        handleCloneBtnClick: function( e ) {
            var item = jQuery( e.currentTarget ).closest( '.awb-builder-nav__list-item' ),
                view,
                viewCid = item.attr( 'data-awb-view-cid' );

            if ( ! viewCid ) {
                return;
            }
            view = this.model.viewsCidToViewsMap[ viewCid ];

            this.openOrCloseNestedRowsNeeded( view, true );

            if ( view.cloneContainer ) {
                view.cloneContainer();
            } else if ( view.cloneNestedRow ) {
                view.cloneNestedRow( 'navigator' );
            } else if ( view.cloneColumn ) {
                view.cloneColumn( undefined, true );
            } else if ( view.cloneElement ) {
                view.cloneElement( undefined, true );
            }
        },

        handleAddBtnClick: function( e ) {
            var item = jQuery( e.currentTarget ).closest( '.awb-builder-nav__list-item' ),
                view,
                type,
                viewCid = item.attr( 'data-awb-view-cid' );

            if ( ! viewCid ) {
                return;
            }
            view = this.model.viewsCidToViewsMap[ viewCid ];
            type = view.model.get( 'element_type' );

            this.openOrCloseNestedRowsNeeded( view, true );

            if ( 'fusion_builder_container' === type ) {
                view.$el.children().children( '.fusion-builder-module-controls-container-wrapper' ).find( '.fusion-builder-container-add' ).trigger( 'click' );
            } else if ( 'fusion_builder_column' === type ) {
                view.$el.children( '.fusion-builder-module-controls-container' ).find( '.fusion-builder-insert-column' ).trigger( 'click' );
            } else if ( 'fusion_builder_row_inner' === type ) {
                view.$el.children( '.fusion-builder-module-controls-container' ).find( '.fusion-builder-add-element' ).trigger( 'click' );
            } else if ( 'fusion_builder_column_inner' === type ) {
                view.$el.children( '.fusion-builder-module-controls-container' ).find( '.fusion-builder-row-add-child' ).trigger( 'click' );
            } else { // element
                view.$el.children( '.fusion-builder-module-controls-container' ).find( '.fusion-builder-add-element' ).trigger( 'click' );
            }
        },

        handleAddInsideBtnClick: function( e ) {
            var btn = jQuery( e.currentTarget ),
                item = btn.closest( '.awb-builder-nav__list-item' ),
                view,
                viewCid = item.attr( 'data-awb-view-cid' );

            if ( ! viewCid ) {
                return;
            }
            view = this.model.viewsCidToViewsMap[ viewCid ];
            this.openOrCloseNestedRowsNeeded( view, true );

            if ( btn.is( '.awb-builder-nav__add-inside--container' ) ) {
                // Add columns.
                view.$el.find( '.fusion-builder-empty-container .fusion-builder-insert-column' ).first().trigger( 'click' );
            } else if ( btn.is( '.awb-builder-nav__add-inside--column' ) || btn.is( '.awb-builder-nav__add-inside--nested-column' ) ) {
                // Add element.
                view.$el.find( '.fusion-builder-empty-column .fusion-builder-add-element' ).first().trigger( 'click' );
            } else if ( btn.is( '.awb-builder-nav__add-inside--nested-row' ) ) {
                // Add nested column.
                view.$el.find( '.fusion-builder-empty-container .fusion-builder-insert-inner-column' ).first().trigger( 'click' );
            }
        },

        outlineItem: function( view ) {
            // Make sure to add a one-time event, that will remove the outlines if review is clicked.
            if ( ! this.removeOutlineEventAdded ) {
                jQuery( jQuery( '#fb-preview' )[ 0 ].contentWindow ).on( 'click', this.removeItemOutline.bind( this ) );
                this.removeOutlineEventAdded = true;
            }

            if ( ! view.$el.hasClass( 'fusion-builder-navigator-outlined' ) ) {
                this.removeItemOutline();
                if ( this.outlineItemTimeout ) {
                    clearTimeout( this.outlineItemTimeout );
                }

                view.$el.addClass( 'fusion-builder-navigator-outlined' );
                this.activeOutlinedElement = view.cid;
                this.outlineItemTimeout = setTimeout( this.removeItemOutline.bind( this ), 1600 );
            }
        },

        removeItemOutline: function() {
            var view;

            if ( this.activeOutlinedElement ) {
                view = this.model.viewsCidToViewsMap[ this.activeOutlinedElement ];
                if ( 'object' === typeof view ) {
                    view.$el.removeClass( 'fusion-builder-navigator-outlined' );
                    this.activeOutlinedElement = '';
                }
            }
        },

        scrollToViewIfNecessary: function( view ) {
            var elBoundingRect = view.$el.get( 0 ).getBoundingClientRect(),
                iframeWindow = jQuery( '#fb-preview' )[ 0 ].contentWindow,
                windowHeight = jQuery( iframeWindow ).innerHeight(),
                stickyHeaderHeight = ( 'function' === typeof iframeWindow.getStickyHeaderHeight ) ? iframeWindow.getStickyHeaderHeight() : 0,
                elTopIsInViewWindow,
                elBottomIsInViewWindow;

            elTopIsInViewWindow = ( 0 <= elBoundingRect.top - stickyHeaderHeight );
            elBottomIsInViewWindow = ( elBoundingRect.bottom <= ( windowHeight * 0.95 ) );

            if ( ! elTopIsInViewWindow || ! elBottomIsInViewWindow ) {
                if ( view.scrollHighlight ) {
                    view.scrollHighlight( true, false );
                }
            }
        },

        /**
         * After a sortable of elements/columns is done, get the nearest element
         * and the drop zone element needed for the function.
         *
         * @return {Object|false} False on failure.
         */
        _getNearbyItemToDropAndTarget( $navItemSorted ) {
            var nearbyItem,
                nearbyItemCid,
                nearbyItemView,
                rowModelCid,
                $dropTarget,
                dropTargetPosition;

            // Get the column where to add this item.
            if ( $navItemSorted.prev().length ) {
                nearbyItem = $navItemSorted.prev();
                dropTargetPosition = 'after';
            } else if ( $navItemSorted.next().length ) {
                nearbyItem = $navItemSorted.next();
                dropTargetPosition = 'before';
            } else {
                dropTargetPosition = 'in';
                nearbyItem = $navItemSorted.parent().closest( '[data-awb-view-cid]' );
            }
            nearbyItemCid = nearbyItem.attr( 'data-awb-view-cid' );

            if ( ! this.model.viewsCidToViewsMap[ nearbyItemCid ] ) {
                return false;
            }
            nearbyItemView = this.model.viewsCidToViewsMap[ nearbyItemCid ];

            // Special Case: If is a container, and the column is dropped inside(with no other siblings),
            // then in fact the row element is needed and not the container.
            if ( 'in' === dropTargetPosition && 'fusion_builder_container' === nearbyItemView.model.get( 'element_type' ) ) {
                if ( 1 === nearbyItemView.model.children.length ) {
                    rowModelCid = nearbyItemView.model.children.models[ 0 ].cid;
                    if ( ! this.model.modelsToViewsMap[ rowModelCid ] ) {
                        return false;
                    }
                    nearbyItemView = this.model.modelsToViewsMap[ rowModelCid ];
                }
            }

            // Get drop target element.
            if ( 'before' === dropTargetPosition ) {
                $dropTarget = nearbyItemView.$el.children( '.fusion-droppable.target-before' );
            } else if ( 'after' === dropTargetPosition ) {
                $dropTarget = nearbyItemView.$el.children( '.fusion-droppable.target-after' );
            } else {
                $dropTarget = nearbyItem;
            }

            if ( ! nearbyItem.length || ! $dropTarget.length ) {
                return false;
            }

            return {
                targetPosition: dropTargetPosition,
                item: nearbyItem,
                view: nearbyItemView,
                dropTarget: $dropTarget
            };
        },

        getAllSortableList: function() {
            var navigatorType = this.model.get( 'navigatorType' );

            if ( 'elements' === navigatorType ) {
                return jQuery();
            }

            if ( 'post_cards' === navigatorType || 'columns' === navigatorType ) {
                return this.$el.find( '.awb-builder-nav__list-item--column .awb-builder-nav__list-submenu' );
            }

            if ( 'sections' === navigatorType ) {
                return this.$el.find( '.awb-builder-nav__list-item--container .awb-builder-nav__list-submenu' );
            }

            return this.$el.find( '.awb-builder-nav__list-main, .awb-builder-nav__list-submenu' );
        },

        openOrCloseNestedRowsNeeded: function( view, onlyFromInside = false ) {
            var $nestedRows            = view.$el.closest( '.fusion-nested-columns' ),
                viewIsNestedRows       = view.$el.is( '.fusion-nested-columns' ),
                viewIsInsideNestedRows = ( $nestedRows.length ? true : false );

            if ( onlyFromInside && viewIsNestedRows ) {
                closeAndSaveAllNestedRows();
                return;
            }

            if ( viewIsInsideNestedRows ) {
                if ( $nestedRows.is( '.editing' ) ) {
                    // Do nothing, they are already open.
                } else {
                    closeAndSaveAllNestedRows();
                    openNestedRows( $nestedRows );
                }
            } else {
                closeAndSaveAllNestedRows();
            }

            function closeAndSaveAllNestedRows() {
                var activeNestedCols = FusionPageBuilderApp.$el.find( '.fusion-nested-columns.editing' );

                if ( activeNestedCols.length ) {
                    activeNestedCols.find( '.fusion-builder-stop-editing' ).trigger( 'click' );
                }
            }

            function openNestedRows() {
                if ( $nestedRows.is( ':visible' ) ) {
                    $nestedRows.find( '.fusion-builder-module-controls-type-row-nested .fusion-builder-row-settings' ).trigger( 'click' );
                }
            }
        },

        toggleAllContainersState: 'expanded', // or 'collapsed'.
        toggleCollapseAllContainers: function( e ) {
            var collapseBtn,
                button = jQuery( e.currentTarget );

            if ( 'expanded' === this.toggleAllContainersState ) {
                collapseBtn = this.$el.find( '.awb-builder-nav__list-item--container:not(.awb-builder-nav__list-item--collapsed) > .awb-builder-nav__item-header > .awb-builder-nav__collapse-btn' );
                collapseBtn.trigger( 'click' );
                button.addClass( 'awb-navigator-toggle-is-collapsed' );
                this.toggleAllContainersState = 'collapsed';
            } else {
                collapseBtn = this.$el.find( '.awb-builder-nav__list-item--container.awb-builder-nav__list-item--collapsed > .awb-builder-nav__item-header > .awb-builder-nav__collapse-btn' );
                collapseBtn.trigger( 'click' );
                button.removeClass( 'awb-navigator-toggle-is-collapsed' );
                this.toggleAllContainersState = 'expanded';
            }
        }

    } );

}( jQuery ) );
