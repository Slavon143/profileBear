<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_gallery-shortcode">
	<# if ( 'undefined' !== typeof usingDynamic && usingDynamic ) { #>
		<div class="fusion-builder-placeholder"><?php esc_html_e( 'This gallery element is set to use dynamic data.  For a preview please check the front-end.', 'fusion-builder' ); ?></div>
	<# } else { #>
		<div {{{ _.fusionGetAttributes( wrapperAttr ) }}}>
			<div {{{ _.fusionGetAttributes( attr ) }}}></div>
			{{{paginationHTML}}}
		</div>
	<# } #>
	{{{captionStyles}}}
	<style>
		{{{aspectRatioStyles}}}
	</style>
	<div class="fusion-clearfix"></div>
</script>

<script type="text/html" id="tmpl-fusion_gallery_image-shortcode">
<#
	var images_html = '',
		image_html  = '';

		if ( 'undefined' !== typeof imageData ) {
			image_html = imageData.image_html;
		} else {
			image_html = '<div class="fusion-builder-placeholder">' + fusionBuilderText.gallery_placeholder + '</div>';
		}

	if ( 'masonry' === galleryLayout ) {
		image_html = '<div ' + _.fusionGetAttributes( imagesAttr.masonryWrapper ) + '>' + image_html + '</div>';
	}

	images_html += '<div ' + _.fusionGetAttributes( imagesAttr.images ) + '>';

	if ( 'above' === parentValues.caption_style && 'undefined' !== typeof captionHtml ) {
		images_html += captionHtml;
	}

	images_html += '<div ' + _.fusionGetAttributes( imageWrapperAttr ) + '>';

	if ( -1 === jQuery.inArray( parentValues.caption_style, [ 'off', 'above', 'below' ] ) && 'undefined' !== typeof captionHtml ) {
		image_html += captionHtml;
	}

	if ( galleryLightbox && 'no' !== galleryLightbox ) {
		images_html += '<a ' + _.fusionGetAttributes( imagesAttr.link ) + '>' + image_html + '</a>';
	} else {
		images_html += image_html;
	}
	images_html += '</div>';

	if ( 'below' === parentValues.caption_style && 'undefined' !== typeof captionHtml ) {
		images_html += captionHtml;
	}

	images_html += '</div>';

	// TODO: between child views ?
	if ( 0 === counter % galleryColumns && 'grid' === galleryLayout ) {
		images_html += '<div class="clearfix"></div>';
	}
#>
	{{{ images_html }}}
</script>
