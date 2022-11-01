<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-new-slideshow-blog-shortcode">
<#
data   = jQuery.extend( true, {}, data );
output = '';

style = '';
if ( 'grid' !== data.layout && 'timeline' !== data.layout ) {
	style = '<style type="text/css">';
	if ( 'undefined' !== typeof data.featured_image_width && 'auto' !== data.featured_image_width ) {
		style += '#post-' + data.id + ' .fusion-post-slideshow { max-width: ' + data.featured_image_width + '!important;}';
	}

	if ( 'undefined' !== typeof data.featured_image_height && 'auto' !== data.featured_image_height ) {
		style += '#post-' + data.id + ' .fusion-post-slideshow, #post-' + data.id + ' .fusion-post-slideshow .fusion-image-wrapper img { max-height: ' + data.featured_image_height + ' !important;}';
	}

	if ( 'undefined' !== typeof data.featured_image_width && 'auto' === data.featured_image_width ) {
		style += '#post-' + data.id + ' .fusion-post-slideshow .fusion-image-wrapper img {width: auto;}';
	}

	if ( 'undefined' !== typeof data.featured_image_height && 'auto' === data.featured_image_height ) {
		style += '#post-' + data.id + ' .fusion-post-slideshow .fusion-image-wrapper img { height: auto; }';
	}

	if ( 'undefined' !== typeof data.featured_image_height && 'undefined' !== typeof data.featured_image_width && 'auto' !== data.featured_image_height && 'auto' !== data.featured_image_width ) {
		style += '@media only screen and (max-width: 479px){';
			style += '#post-' + data.id + ' .fusion-post-slideshow,';
			style += '#post-' + data.id + ' .fusion-post-slideshow .fusion-image-wrapper img {';
				style += 'width :auto !important;';
				style += 'height :auto !important;';
			style += '}';
		style += '}';
	}
	style += '</style>';
}

if ( data.thumbnail || data.video ) {
	output += '<div class="fusion-flexslider flexslider fusion-flexslider-loading fusion-post-slideshow">';
	output += '<ul class="slides">';
		if ( data.video ) {
			output += '<li>';
				output += '<div class="full-video">' + data.video + '</div>';
			output += '</li>';
		}

		if ( data.thumbnail ) {
			data.image_data.image_size = data.image_size;
			output += '<li>' + _.fusionFeaturedImage( data.image_data ) + '</li>';
		}

		_.each( data.multiple_featured, function( featuredImage ) {
			output += '<li>';
				output += '<div class="fusion-image-wrapper">';
					output += '<a href="' + data.permalink + '" aria-label="' + data.title + '">';
						output += featuredImage[ data.image_size ];
					output += '</a>';
					output += '<a style="display:none;" href="' + featuredImage.full_src + '" data-rel="iLightbox[gallery' + data.id + ']"  title="' + featuredImage.caption + '" data-title="' + featuredImage.title + '" data-caption="' + featuredImage.caption + '">';
					if ( featuredImage.alt ) {
						output += '<img style="display:none;" alt="' + featuredImage.alt + '" role="presentation" />';
					}
					output += '</a>';
				output += '</div>';
			output += '</li>';
		} );
	output += '</ul>';
	output += '</div>';
}
#>
{{{ style }}}
{{{ output }}}
</script>
