<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_postslider-shortcode">
<#
var slider       = '',
	image_output = '',
	link_output  = '',
	title        = '',
	excerpt      = '',
	content      = '';
	container    = '',
	html         = '';

if ( 'undefined' !== typeof query_data && 'undefined' === typeof query_data.placeholder ) {
	_.each( query_data.datasets, function( dataset, index ) {

		if ( 'attachments' === values.layout ) {
			content = '<img ' + _.fusionGetAttributes( datasets[index].image_attributes ) + ' />';

			if ( 'yes' === values.lightbox ) {
				content = '<a ' + _.fusionGetAttributes( datasets[index].link_attributes ) + '>' + content + '</a>';
			}

			slider += '<li ' + _.fusionGetAttributes( datasets[index].li_attributes ) + ' >' + content + '</li>';
		} else if ( 'posts' === values.layout || 'posts-with-excerpt' === values.layout ) {
			image_output = '<img ' + _.fusionGetAttributes( datasets[index].image_attributes ) + ' />';
			link_output  = '<a ' + _.fusionGetAttributes( datasets[index].link_attributes ) + '>' + image_output + '</a>';
			title        = '<h2><a ' + _.fusionGetAttributes( datasets[index].title_link_attributes ) + '>' + dataset.title + '</a></h2>';
			content      = title;

			if ( 'posts-with-excerpt' == values.layout ) {
				excerpt = _.fusionGetFixedContent( dataset.excerpt, 'yes', values.excerpt, true );
				content = '<div class="excerpt-container">' + title + excerpt + '</div>';
			}

			container = '<div class="slide-excerpt">' + content + '</div>';

			slider += '<li>' + link_output + container + '</li>';
		}

	});

	slides_html = '<ul class="slides">' + slider + '</ul>';

	html = '<div ' + _.fusionGetAttributes( sliderAttr ) + '>' + slides_html + '</div>';

	if ( 'attachments' == values.layout ) {
		html += '<div ' + _.fusionGetAttributes( thumbAttr ) + '></div>';
	}
} else if ( 'undefined' !== typeof query_data.placeholder ) {
	html = query_data.placeholder;
} else if ( 'undefined' !== typeof markup ) {
	// No Query data, use shortcode_content as this must be first load.
	html = markup.output;
} else {
	// Everything failed!
	html = 'There was a problem loading the content.';
}
#>
{{{ html }}}
</script>
