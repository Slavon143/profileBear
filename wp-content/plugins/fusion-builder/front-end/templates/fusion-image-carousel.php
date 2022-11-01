<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_images-shortcode">
<# if ( 'undefined' !== typeof usingDynamic && usingDynamic ) { #>
	<div class="fusion-builder-placeholder"><?php esc_html_e( 'This image carousel element is set to use dynamic data.  For a preview please check the front-end.', 'fusion-builder' ); ?></div>
<# } else { #>
<div {{{ _.fusionGetAttributes( attr ) }}}>
	<div  {{{ _.fusionGetAttributes( attrCarousel ) }}}>
		<div class="fusion-carousel-positioner">
			<ul class="fusion-carousel-holder fusion-child-element">
			</ul>
			<# if ( 'yes' === show_nav ) { #>
			<div class="fusion-carousel-nav">
				<button class="fusion-nav-prev" aria-label="Previous"></button>
				<button class="fusion-nav-next" aria-label="Next"></button>
			</div>
			<# } #>
		</div>
	</div>

	<div class="fusion-element-placeholder">
		<div class="fusion-carousel-item-wrapper" style="visibility: inherit">
			<div class="fusion-image-wrapper hover-type-none" style="width:100%">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 560"><path fill="#EAECEF" d="M0 0h1024v560H0z"/><g fill-rule="evenodd" clip-rule="evenodd"><path fill="#BBC0C4" d="M378.9 432L630.2 97.4c9.4-12.5 28.3-12.6 37.7 0l221.8 294.2c12.5 16.6.7 40.4-20.1 40.4H378.9z"/><path fill="#CED3D6" d="M135 430.8l153.7-185.9c10-12.1 28.6-12.1 38.7 0L515.8 472H154.3c-21.2 0-32.9-24.8-19.3-41.2z"/><circle fill="#FFF" cx="429" cy="165.4" r="55.5"/></g></svg>
			</div>
		</div>
	</div>
</div>
{{{captionStyles}}}
<# } #>
</script>
<script type="text/html" id="tmpl-fusion_image-shortcode">
<#
var image_html = '';

image_html += '<div ' + _.fusionGetAttributes( attrItemWrapper ) + '>';

if ( 'above' === parentValues.caption_style && 'undefined' !== typeof captionHtml ) {
	image_html += captionHtml;
}

image_html += '<div ' + _.fusionGetAttributes( attrImageWrapper ) + '>';

if ( 'no' === mouseScroll && ( ( null !== link && '' !== link ) || 'yes' === lightbox ) ) {
	image_html += '<a ' + _.fusionGetAttributes( attrCarouselLink ) + '>' + imageElement + '</a>';
} else {
	image_html += imageElement;
}

image_html += '</div>';

if ( 'below' === parentValues.caption_style && 'undefined' !== typeof captionHtml ) {
	image_html += captionHtml;
}

image_html += '</div>';
#>
{{{ image_html }}}
</script>
