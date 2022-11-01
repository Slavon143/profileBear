<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-featured-image">
<#
data                   = jQuery.extend( true, {}, data );
output                 = '';
imageWrapperAttributes = '';
imageAttributes        = {};

if ( 'undefined' !== typeof data.layout && 'masonry' === data.layout && 'undefined' !== typeof data.masonry_data ) {
	imageAttributes = _.fusionGetMasonryAttribute( data.masonry_data );
}
imageAttributes.class = ( 'undefined' !== typeof imageAttributes.class  ) ? imageAttributes.class + ' fusion-image-wrapper' + data.image_size_class : 'fusion-image-wrapper' + data.image_size_class;

_.each( imageAttributes, function(  attributeValue, attributeKey ) {
	imageWrapperAttributes += ' ' + attributeKey + '="' + attributeValue + '"';
} );
output += '<div ' + imageWrapperAttributes + ' aria-haspopup="true">';

if ( 'undefined' !== typeof data.display_woo_sale && false !== data.display_woo_sale && 'undefined' !== typeof data.sale ) {
	output += data.sale;
}
if ( ( data.enable_rollover && 'yes' === data.display_rollover ) || 'force_yes' === data.display_rollover ) {
	if ( 'undefined' !== typeof data.featured_images[ data.image_size ] ) {
		output += data.featured_images[ data.image_size ];
	} else if ( 'undefined' !== typeof FusionApp.settings && 1 == FusionApp.settings.featured_image_placeholder ) {
		output += '<div class="fusion-placeholder-image" data-origheight="150" data-origwidth="1500px" style="height:150px;width:1500px;"></div>';
	}

	output += '<div class="fusion-rollover">';
	output += '<div class="fusion-rollover-content">';
	if ( 'no' !== data.image_rollover_icons && 'product' !== data.post_type ) {
		if ( 'zoom' !== data.image_rollover_icons ) {
			output += '<a class="fusion-rollover-link" href="' + data.icon_permalink + '"' + data.link_target + '>' + data.icon_permalink_title + '</a>';
		}
		if ( 'link' !== data.image_rollover_icons ) {
			if ( ( 'linkzoom' === data.image_rollover_icons || '' === data.image_rollover_icons ) && data.full_image ) {
				output += '<div class="fusion-rollover-sep"></div>';
			}
			if ( data.full_image ) {
				output += '<a class="fusion-rollover-gallery" href="' + data.full_image + '" data-id="' + data.post_id + '" data-rel="' + data.data_rel + '" data-title="' + data.data_title + '" data-caption="' + data.data_caption + '">';
					output += '<?php esc_html_e( 'Gallery', 'fusion-builder' ); ?>';
				output += '</a>';
				output += data.lightbox_content;
			}
		}
	}

	inCart = false;
	inCart = jQuery.inArray( data.post_id, data.items_in_cart );

	if ( false === inCart || -1 === inCart ) {
		if ( '1' == data.display_post_title || 'enable' === data.display_post_title || true === data.display_post_title ) { // jshint ignore: line
			output += '<h4 class="fusion-rollover-title">';
				output += '<a class="fusion-rollover-title-link" href="' + data.permalink + '>"' + data.link_target + '>';
					output += data.title;
				output += '</a>';
			output += '</h4>';
		}

		if ( ( '1' == data.display_post_categories || 'enable' === data.display_post_categories || true === data.display_post_categories ) && data.terms ) { // jshint ignore: line
			output += data.terms;
		}
	}

	if ( 'product' === data.post_type ) {
		iconClass = ( inCart ) ? 'awb-icon-check-square-o' : 'awb-icon-spinner';
		output += '<span class="cart-loading">';
			output += '<a href="' + data.cart_url + '">';
				output += '<i class="' + iconClass + '" aria-hidden="true"></i>';
				output += '<div class="view-cart"><?php esc_html_e( 'View Cart', 'fusion-builder' ); ?></div>';
			output += '</a>';
		output += '</span>';
	}

	if ( 'product' === data.post_type ) {

		if ( false !== data.display_woo_rating ) {
			output += data.rating;
		}

		if ( false !== data.display_woo_price ) {
			output += data.price;
		}

		if ( false !== data.display_woo_buttons ) {
			output += '<div class="fusion-product-buttons">';
				output += data.buttons;
			output += '</div>';
		}
	}
	output += '<a class="fusion-link-wrapper" href="' + data.icon_permalink + '"' + data.link_target + ' aria-label="' + data.title + '"></a>';
	output += '</div>';
	output += '</div>';
} else {
	output += '<a href="' + data.permalink + '">' + data.featured_images[ data.image_size ] + '</a>';
}
output += '</div>';
#>
{{{ output }}}
</script>
