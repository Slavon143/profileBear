<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_person-shortcode">
<#

var inner_content = social_icons_content = social_icons_content_top = social_icons_content_bottom = picture = '';

if ( '' !== values.picture ) {
	picture = '<img ' + _.fusionGetAttributes( imageAttr ) + ' />';

	if ( '' !== values.pic_link ) {
		picture = '<a ' + _.fusionGetAttributes( hrefAttr ) + '>' + picture + '</a>';
	}
	picture = '<div ' + _.fusionGetAttributes( wrapperAttr ) + '><div ' + _.fusionGetAttributes( imageContainerAttr ) + '>' + picture + '</div></div>';
} else {
	picture = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 560"><path fill="#EAECEF" d="M0 0h1024v560H0z"/><g fill-rule="evenodd" clip-rule="evenodd"><path fill="#BBC0C4" d="M378.9 432L630.2 97.4c9.4-12.5 28.3-12.6 37.7 0l221.8 294.2c12.5 16.6.7 40.4-20.1 40.4H378.9z"/><path fill="#CED3D6" d="M135 430.8l153.7-185.9c10-12.1 28.6-12.1 38.7 0L515.8 472H154.3c-21.2 0-32.9-24.8-19.3-41.2z"/><circle fill="#FFF" cx="429" cy="165.4" r="55.5"/></g></svg>';
}

if ( '' !== values.name || '' !== values.title || '' !== values.content ) {

	if ( 0 < socialNetworks.length ) {
		social_icons_content_top  = '<div ' + _.fusionGetAttributes( socialAttr ) + '>';
		social_icons_content_top += '<div class="fusion-social-networks-wrapper">' + icons + '</div>';
		social_icons_content_top += '</div>';

		social_icons_content_bottom  = '<div ' + _.fusionGetAttributes( socialAttr ) + '>';
		social_icons_content_bottom += '<div class="fusion-social-networks-wrapper">' + icons + '</div>';
		social_icons_content_bottom += '</div>';
	}

	if ( 'top' === values.icon_position ) {
		social_icons_content_bottom = '';
	}
	if ( 'bottom' === values.icon_position ) {
		social_icons_content_top = '';
	}

	var person_author_wrapper = '<div class="person-author-wrapper"><span class="person-name">' + values.name + '</span><span class="person-title">' + values.title + '</span></div>';

	person_author_content = person_author_wrapper + social_icons_content_top;
	if ( 'right' == values.content_alignment ) {
		person_author_content = social_icons_content_top + person_author_wrapper;
	}

	inner_content += '<div ' + _.fusionGetAttributes( descAttr ) + '>';
	inner_content += '<div class="person-author">' + person_author_content + '</div>';
	inner_content += '<div class="person-content clearfix">' + FusionPageBuilderApp.renderContent( values.element_content, cid, false ) + '</div>';
	inner_content += social_icons_content_bottom;
	inner_content += '</div>';

}

#>
<div {{{ _.fusionGetAttributes( attr ) }}}>
	{{{ styles }}} {{{ picture }}} {{{ inner_content }}}
</div>
</script>
