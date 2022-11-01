<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/template" id="front-end-navigator-template">
	<div class="awb-builder-nav">
		<#
		if ( Array.isArray( navigatorItems ) && navigatorItems.length ) {
			navigatorItems = removeRows( navigatorItems );
			generateNavigationList( navigatorItems, 1 );
		} else {
			displayEmptyMessage();
		}
		#>
	</div>

	<# function displayEmptyMessage() { #>
		<div class="awb-builder-nav__empty-message-wrapper">
			<div class="fusion-builder-option custom">
				<div class="important-description">
					<div class="fusion-redux-important-notice">
						<div class="awb-builder-nav__empty-icon"><i class="fusiona-navigator"></i></div>
						<div class="awb-builder-nav__empty-message-title"><?php esc_html_e( 'Navigator', 'fusion-builder' ); ?></div>
						<div class="awb-builder-nav__empty-message"><?php esc_html_e( 'No content has been added to the post yet.', 'fusion-builder' ); ?></div>
					</div>
				</div>
			</div>
		</div>
	<# } #>

	<# function generateNavigationList( navigationItems, indent) { #>
		<#
		var nextItemWillCollapse,
			collapsedClass,
			haveChildren,
			itemType,
			isElementItem,
			itemTypeClass,
			headerTypeClass,
			addInsideTypeClass,
			listPositionClass = ( indent === 1 ? 'awb-builder-nav__list-main' : 'awb-builder-nav__list-submenu' ),
			i;
		#>

		<ul class="{{ listPositionClass }}">
			<# for ( i = 0; i < navigationItems.length; i++ ) { #>
				<#
				collapsedClass = ( collapsedItems[ navigationItems[i].view.cid ] ? ' awb-builder-nav__list-item--collapsed' : '' );
				haveChildren = ( navigationItems[i].children.length ? true : false );
				noChildrenItemClass = ( haveChildren ? '' : ' awb-builder-nav__list-item--no-children' );
				itemType = getItemType( navigationItems[i] );
				isElementItem = ( 'element' === itemType );
				itemTypeClass = 'awb-builder-nav__list-item--' + itemType;
				headerTypeClass = 'awb-builder-nav__item-header--' + itemType;
				addInsideTypeClass = 'awb-builder-nav__add-inside--' + itemType;
				#>

				<li class="awb-builder-nav__list-item {{ itemTypeClass }}{{ noChildrenItemClass }}{{ collapsedClass }}" data-awb-view-cid="{{ navigationItems[i].view.cid }}">
					<div class="awb-builder-nav__item-header {{ headerTypeClass }}">
						<# if ( haveChildren || ! isElementItem ) { #>
							<button class="awb-builder-nav__collapse-btn" aria-label="<# buttonLabel( 'collapse', '' ) #>" title="<# buttonLabel( 'collapse', '' ) #>"><i class="fusiona-caret-down"></i></button>
						<# } #>

						<# if ( isElementItem ) { #>
							<div class="awb-builder-nav__item-icon"><i class="{{ getItemIcon( navigationItems[i] ) }}" aria-hidden="true"></i></div>
						<# } #>

						<div class="awb-builder-nav__item-name">{{ navigationItems[i].name }}{{ getColumnDisplaySize( navigationItems[i] ) }}</div>

						<div class="awb-builder-nav__item-actions">
							<# if ( isButtonAllowed( 'edit', itemType ) ) { #>
								<button class="awb-builder-nav__btn-edit" aria-label="<# buttonLabel( 'edit', itemType ) #>" title="<# buttonLabel( 'edit', itemType ) #>"><i class="fusiona-pen" aria-hidden="true"></i></button>
							<# } #>

							<# if ( isButtonAllowed( 'add', itemType ) ) { #>
								<button class="awb-builder-nav__btn-add" aria-label="<# buttonLabel( 'add', itemType ) #>" title="<# buttonLabel( 'add', itemType ) #>">
									<# if ( 'column' === itemType || 'nested-column' === itemType ) { #>
										<i class="fusiona-add-columns" aria-hidden="true"></i>
									<# } else if ( 'container' === itemType ) { #>
										<i class="fusiona-add-container" aria-hidden="true"></i>
									<# } else { #>
										<i class="fusiona-plus" aria-hidden="true"></i>
									<# } #>
								</button>
							<# } #>

							<# if ( isButtonAllowed( 'clone', itemType ) ) { #>
								<button class="awb-builder-nav__btn-clone" aria-label="<# buttonLabel( 'clone', itemType ) #>" title="<# buttonLabel( 'clone', itemType ) #>"><i class="fusiona-file-add" aria-hidden="true"></i></button>
							<# } #>

							<# if ( isButtonAllowed( 'remove', itemType ) ) { #>
								<button class="awb-builder-nav__btn-remove" aria-label="<# buttonLabel( 'remove', itemType ) #>" title="<# buttonLabel( 'remove', itemType ) #>"><i class="fusiona-trash-o" aria-hidden="true"></i></button>
							<# } #>
						</div>
					</div>

					<# if ( ! isElementItem ) { #>
						<#
						// UL html list needs to be generated for columns and containers even if they don't have children, to allow jQuery sortable.
						generateNavigationList( navigationItems[i].children, indent + 1 );
						#>

						<div class="awb-builder-nav__add-inside {{ addInsideTypeClass }}">
							<div class="awb-builder-nav__item-icon"><i class="{{ getAddInsideBtnIcon( itemType ) }}" aria-hidden="true"></i></div>
							<div class="awb-builder-nav__add-inside-text"><# addInsideBtnText( itemType ) #></div>
						</div>
					<# } #>
				</li>
			<# } #>
		</ul>
	<# } #>

	<# function removeRows( navigatorItems ) {
		var childrenCopy,
			i,
			j,
			newChildren = [];

		for ( i = 0; i < navigatorItems.length; i++ ) {
			if ( 'container' === getItemType( navigatorItems[i] ) ) {
				newChildren = [];

				for ( j=0; j < navigatorItems[i].children.length; j++ ) {
					newChildren = newChildren.concat( navigatorItems[i].children[j].children );
				}

				navigatorItems[i].children = removeRows( newChildren );
			} else {
				navigatorItems[i].children = removeRows( navigatorItems[i].children );
				continue;
			}
		}

		return navigatorItems;
	} #>


	<# function getItemType( item ) {
		switch(item.model.get( 'element_type' ) ) {
			case 'fusion_builder_container':
				return 'container';

			case 'fusion_builder_row':
				return 'row';
			case 'fusion_builder_column':
				return 'column';

			case 'fusion_builder_row_inner':
				return 'nested-row';
			case 'fusion_builder_column_inner':
				return 'nested-column';

			default:
				return 'element';
		}
	} #>

	<# function getItemIcon( item ) {
		var itemType = item.model.get( 'element_type' );
		if ( ! fusionAllElements || ! fusionAllElements[ itemType ] || ! fusionAllElements[ itemType ].icon ) {
			return '';
		}

		return fusionAllElements[ itemType ].icon;
	} #>

	<# function getColumnDisplaySize( item ) {
		if ( 'column' !== getItemType( item ) ) {
			return '';
		}

		if ( ! item.view || ! item.view.values || ! item.view.values.type ) {
			return '';
		}

		if ( item.view.values.type.indexOf('_') !== -1 ) {
			return ' ' + item.view.values.type.replace( '_', '/' );
		} else {
			if ( 'auto' === item.view.values.type ) {
				return ' Auto';
			} else {
				return ' ' + parseInt( item.view.values.type ) + '%';
			}
		}

	} #>

	<# function isButtonAllowed( buttonType, elementType ) {
		if ( ! navigatorType ) {
			return true;
		}

		if ( 'sections' === navigatorType ) {
			if ( 'container' === elementType ) {
				if ( 'edit' === buttonType ) {
					return true;
				} else {
					return false;
				}
			} else {
				return true;
			}
		}

		if ( 'post_cards' === navigatorType || 'columns' === navigatorType ) {
			if ( 'container' === elementType ) {
				return false;
			} else if ( 'column' === elementType ) {
				if ( 'edit' === buttonType ) {
					return true;
				} else {
					return false;
				}
			} else {
				return true;
			}
		}

		if ( 'elements' === navigatorType ) {
			if ( 'element' !== elementType ) {
				return false;
			} else {
				if ( 'edit' === buttonType ) {
					return true;
				} else {
					return false;
				}
			}
		}

		return true;
	} #>

	<# function buttonLabel( buttonType, itemType ) {
		if ( 'collapse' === buttonType ) {
			#><?php esc_html_e( 'Collapse / Expand', 'fusion-builder' ); ?><#
			return;
		}

		if ( 'container' === itemType ) {
			if ( 'edit' === buttonType ) {
				#><?php esc_html_e( 'Container Options', 'fusion-builder' ); ?><#
			} else if ( 'add' === buttonType ) {
				#><?php esc_html_e( 'Add Container', 'fusion-builder' ); ?><#
			} else if ( 'clone' === buttonType ) {
				#><?php esc_html_e( 'Clone Container', 'fusion-builder' ); ?><#
			} else if ( 'remove' === buttonType ) {
				#><?php esc_html_e( 'Delete Container', 'fusion-builder' ); ?><#
			}
		} else if ( 'column' === itemType ) {
			if ( 'edit' === buttonType ) {
				#><?php esc_html_e( 'Column Options', 'fusion-builder' ); ?><#
			} else if ( 'add' === buttonType ) {
				#><?php esc_html_e( 'Add Columns', 'fusion-builder' ); ?><#
			} else if ( 'clone' === buttonType ) {
				#><?php esc_html_e( 'Clone Column', 'fusion-builder' ); ?><#
			} else if ( 'remove' === buttonType ) {
				#><?php esc_html_e( 'Delete Column', 'fusion-builder' ); ?><#
			}
		} else if ( 'element' === itemType ) {
			if ( 'edit' === buttonType ) {
				#><?php esc_html_e( 'Element Options', 'fusion-builder' ); ?><#
			} else if ( 'add' === buttonType ) {
				#><?php esc_html_e( 'Add Element Below', 'fusion-builder' ); ?><#
			} else if ( 'clone' === buttonType ) {
				#><?php esc_html_e( 'Clone Element', 'fusion-builder' ); ?><#
			} else if ( 'remove' === buttonType ) {
				#><?php esc_html_e( 'Delete Element', 'fusion-builder' ); ?><#
			}
		} else if ( 'nested-row' === itemType ) {
			if ( 'edit' === buttonType ) {
				#><?php esc_html_e( 'Open Nested Columns', 'fusion-builder' ); ?><#
			} else if ( 'add' === buttonType ) {
				#><?php esc_html_e( 'Add Columns', 'fusion-builder' ); ?><#
			} else if ( 'clone' === buttonType ) {
				#><?php esc_html_e( 'Clone Element', 'fusion-builder' ); ?><#
			} else if ( 'remove' === buttonType ) {
				#><?php esc_html_e( 'Delete Element', 'fusion-builder' ); ?><#
			}
		} else if ( 'nested-column' === itemType ) {
			if ( 'edit' === buttonType ) {
				#><?php esc_html_e( 'Column Options', 'fusion-builder' ); ?><#
			} else if ( 'add' === buttonType ) {
				#><?php esc_html_e( 'Add Element Below', 'fusion-builder' ); ?><#
			} else if ( 'clone' === buttonType ) {
				#><?php esc_html_e( 'Clone Column', 'fusion-builder' ); ?><#
			} else if ( 'remove' === buttonType ) {
				#><?php esc_html_e( 'Delete Column', 'fusion-builder' ); ?><#
			}
		}
	} #>

	<# function getAddInsideBtnIcon( type ) {
		if ( 'container' === type ) {
			return 'fusiona-add-columns';
		} else if ( 'nested-row' === type ) {
			return 'fusiona-add-columns';
		} else {
			return 'fusiona-plus';
		}
	} #>

	<# function addInsideBtnText( type ) {
		if ( 'container' === type ) {
			#><?php esc_html_e( 'Add Columns', 'fusion-builder' ); ?><#
		} else if ( 'nested-row' === type ) {
			#><?php esc_html_e( 'Add Nested Columns', 'fusion-builder' ); ?><#
		} else {
			#><?php esc_html_e( 'Add Element', 'fusion-builder' ); ?><#
		}
	} #>

</script>
