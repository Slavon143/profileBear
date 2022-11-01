<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_image_before_after-shortcode">
<#
if ( 'undefined' !== typeof attrBeforeImage || 'undefined' !== typeof attrAfterImage ) {
	var before_direction = 'vertical' === values.orientation ? 'down' : 'left',
		after_direction    = 'vertical' === values.orientation ? 'up' : 'right';
	#>
	<style type="text/css">{{{ styles }}}</style>

	<div {{{ _.fusionGetAttributes( attrWrapper ) }}}>

	<# if ( '' !== values.before_label && '' !== values.after_label && 'before_after' === values.type && 'out-image-up-down' === values.label_placement ) { #>
		<div class="fusion-image-before-after-before-label before-after-label-out-image-up-down" data-content="{{ values.before_label }}"></div>
	<# } #>

	<div {{{ _.fusionGetAttributes( attr ) }}}>

	<# if ( 'before_after' !== values.type && '' !== values.link ) { #>
		<a {{{ _.fusionGetAttributes( attrLink ) }}}>
	<# } #>

	<# if ( '' !== values.before_image ) { #>
		<img {{{ _.fusionGetAttributes( attrBeforeImage ) }}}>
	<# } #>

	<# if ( '' !== values.after_image ) { #>
		<img {{{ _.fusionGetAttributes( attrAfterImage ) }}}>
	<# } #>

	<# if ( 'before_after' !== values.type && '' !== values.link ) { #>
		</a>
	<# } #>

	<# if ( '' !== values.before_label && '' !== values.after_label && 'before_after' === values.type && ( 'image-centered' === values.label_placement || 'image-up-down' === values.label_placement ) ) { #>
		<div {{{ _.fusionGetAttributes( attrOverlay ) }}}>
			<div class="fusion-image-before-after-before-label" data-content="{{ values.before_label }}"></div>
			<div class="fusion-image-before-after-after-label" data-content="{{ values.after_label }}"></div>
		</div>
	<# } #>

	<# if ( 'before_after' === values.type ) { #>
		<div {{{ _.fusionGetAttributes( attrHandle ) }}}>
			<span class="fusion-image-before-after-{{ before_direction }}-arrow"></span>
			<span class="fusion-image-before-after-{{ after_direction }}-arrow"></span>
		</div>
	<# } #>

	</div>

	<# if ( '' !== values.before_label && '' !== values.after_label && 'before_after' === values.type && 'out-image-up-down' === values.label_placement ) { #>
		<div class="fusion-image-before-after-after-label before-after-label-out-image-up-down" data-content="{{ values.after_label }}"></div>
	<# } #>
	</div>
<# } else { #>
	<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 560"><path fill="#EAECEF" d="M0 0h1024v560H0z"/><g fill-rule="evenodd" clip-rule="evenodd"><path fill="#BBC0C4" d="M378.9 432L630.2 97.4c9.4-12.5 28.3-12.6 37.7 0l221.8 294.2c12.5 16.6.7 40.4-20.1 40.4H378.9z"/><path fill="#CED3D6" d="M135 430.8l153.7-185.9c10-12.1 28.6-12.1 38.7 0L515.8 472H154.3c-21.2 0-32.9-24.8-19.3-41.2z"/><circle fill="#FFF" cx="429" cy="165.4" r="55.5"/></g></svg>
<# } #>
</script>
