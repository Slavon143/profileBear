<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_tb_archives-shortcode">
<#
var html = '',
	wrapLoopOpen = '',
	wrapLoopCloseAction = '';

// If Query Data is set, use it and continue.  If not, echo HTML.
if ( 'undefined' !== typeof query_data && 'undefined' !== typeof query_data.posts ) {

	html += '<div ' + _.fusionGetAttributes( attr ) + '>';

	if ( ( 'grid' === values.layout || 'masonry' === values.layout ) && 0 !== parseInt( values.blog_grid_column_spacing ) ) {
		html += '<style type="text/css">' + styles + '</style>';
	}

	html += '<div ' + _.fusionGetAttributes( attrPostsContainer ) + '>';

	if ( 'timeline' === values.layout ) {

		wrapLoopOpen = '<div class="fusion-timeline-icon">';
		wrapLoopOpen += '<i class="awb-icon-bubbles" style="color: ' + values.grid_element_color + ';" aria-hidden="true"></i>';
		wrapLoopOpen += '</div>';
		wrapLoopOpen += '<div class="fusion-blog-layout-timeline fusion-clearfix">';
		wrapLoopOpen += '<div class="fusion-timeline-line" style="border-color:' + values.grid_element_color + ';"></div>';
	}

	html += wrapLoopOpen;

	if ( 'masonry' === values.layout ) {
		html += '<article class="fusion-post-grid fusion-post-masonry post fusion-grid-sizer"></article>';
	}

	html += blogPosts;

	if ( 'timeline' === values.layout ) {
		if ( values.post_count > 1 ) {
			wrapLoopCloseAction = '</div>';
		}
		wrapLoopCloseAction += '</div>';
	}

	if ( 'grid' === values.layout || 'masonry' === values.layout ) {
		wrapLoopCloseAction += '<div class="fusion-clearfix"></div>';
	}

	html += wrapLoopCloseAction;

	html += '</div>';

	if ( 'no' !== values.scrolling ) {
		html += paginationCode;
	}

	// If infinite scroll with "load more" button is used.
	if ( load_more ) {
		html += '<button class="fusion-load-more-button fusion-blog-button fusion-clearfix">' + load_more_text + '</button>';
	}

	html += '</div>';
	print( html );
} else if ( 'undefined' !== typeof query_data ) {
	print( placeholder );
} #>
<div class="fusion-clearfix"></div>
</script>
