jQuery( document ).ready( function() {
	jQuery.ui.plugin.add( 'draggable', 'iframeScroll', {
		drag: function( event, ui, i ) {
			var o              = i.options,
				scrolled       = false,
				iframe         = jQuery( '#fb-preview' ),
				iframeDocument = iframe.contents(),
				offset         = iframe.offset();

			offset.width  = iframe.width();
			offset.height = iframe.height();

			if ( 'undefined' === typeof i.scrollTop ) {
				i.scrollTop = iframeDocument.scrollTop();
			}

			//Check scroll top
			if ( o.scrollSensitivity > event.clientY ) {
				scrolled = iframeDocument.scrollTop( i.scrollTop - o.scrollSpeed );
				i.scrollTop = i.scrollTop - o.scrollSpeed;
			} else if ( o.scrollSensitivity > offset.height - event.clientY - 15 ) {
				scrolled    = iframeDocument.scrollTop( i.scrollTop + o.scrollSpeed );
				i.scrollTop = i.scrollTop + o.scrollSpeed;
			}

			//Check scroll left
			if ( offset.left < event.pageX && event.pageX < offset.left + o.scrollSensitivity ) {
				if ( offset.top < event.pageY && event.pageY < offset.top + offset.height ) {
					scrolled = iframeDocument.scrollLeft( iframeDocument.scrollLeft() - o.scrollSpeed );
				}
			}

			//Check scroll right
			if ( ( offset.left + offset.width - o.scrollSensitivity ) < event.pageX && event.pageX < offset.left + offset.width ) {
				if ( offset.top < event.pageY && event.pageY < offset.top + offset.height ) {
					scrolled = iframeDocument.scrollLeft( iframeDocument.scrollLeft() + o.scrollSpeed );
				}
			}

			if ( false !== scrolled && jQuery.ui.ddmanager && ! o.dropBehaviour ) {
				jQuery.ui.ddmanager.prepareOffsets( i, event );
			}

			clearTimeout( i.scrollTimer );
			if ( i._mouseStarted ) {
				i.scrollTimer = setTimeout( function() {
					i._trigger( 'drag', event );
					if ( jQuery.ui.ddmanager ) {
						jQuery.ui.ddmanager.drag( i, event );
					}
				}, 10 );
			}
		},
		stop: function( event, ui, i ) {
			clearInterval( i.scrollTimer );
		}
	} );

	// Added to fix W grid dragging to 0.
	jQuery.ui.plugin.add( 'resizable', 'grid', {
		resize: function() {
			var outerDimensions,
				that = jQuery( this ).resizable( 'instance' ),
				o = that.options,
				cs = that.size,
				os = that.originalSize,
				op = that.originalPosition,
				a = that.axis,
				grid = 'number' === typeof o.grid ? [ o.grid, o.grid ] : o.grid,
				gridX = ( grid[ 0 ] || 1 ),
				gridY = ( grid[ 1 ] || 1 ),
				ox = Math.round( ( cs.width - os.width ) / gridX ) * gridX,
				oy = Math.round( ( cs.height - os.height ) / gridY ) * gridY,
				newWidth = os.width + ox,
				newHeight = os.height + oy,
				isMaxWidth = o.maxWidth && ( o.maxWidth < newWidth ),
				isMaxHeight = o.maxHeight && ( o.maxHeight < newHeight ),
				isMinWidth = o.minWidth && ( o.minWidth > newWidth ),
				isMinHeight = o.minHeight && ( o.minHeight > newHeight );

			o.grid = grid;

			ox = -0 === ox ? 0 : ox; //eslint-disable-line no-compare-neg-zero

			if ( 0 < newWidth && 1 > newWidth - gridX && ( ( os.width > gridX && 0 >= ox ) || ( os.width <= gridX && 0 <= ox ) ) ) {

				if ( 'undefined' !== typeof that.size.wasReached ) {
					newWidth = 0;
				} else {
					that.size.wasReached = true;
				}
			} else {
				delete that.size.wasReached;
			}

			if ( isMinWidth ) {
				newWidth += gridX;
			}
			if ( isMinHeight ) {
				newHeight += gridY;
			}
			if ( isMaxWidth ) {
				newWidth -= gridX;
			}
			if ( isMaxHeight ) {
				newHeight -= gridY;
			}

			if ( ( /^(se|s|e)$/ ).test( a ) ) {
				that.size.width = newWidth;
				that.size.height = newHeight;
			} else if ( ( /^(ne)$/ ).test( a ) ) {
				that.size.width = newWidth;
				that.size.height = newHeight;
				that.position.top = op.top - oy;
			} else if ( ( /^(sw)$/ ).test( a ) ) {
				that.size.width = newWidth;
				that.size.height = newHeight;
				that.position.left = op.left - ox;
			} else {
				if ( 0 >= newHeight - gridY || 0 >= newWidth - gridX ) {
					outerDimensions = that._getPaddingPlusBorderDimensions( this );
				}

				if ( 0 < newHeight - gridY ) {
					that.size.height = newHeight;
					that.position.top = op.top - oy;
				} else {
					newHeight = gridY - outerDimensions.height;
					that.size.height = newHeight;
					that.position.top = op.top + os.height - newHeight;
				}

				that.size.width = newWidth;
				that.position.left = op.left - ox;
			}
		}
	} );
} );
