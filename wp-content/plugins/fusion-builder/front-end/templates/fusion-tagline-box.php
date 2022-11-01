<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_tagline_box-shortcode">

<style type="text/css">
	.reading-box-container-{{ cid }} .element-bottomshadow:before,.reading-box-container-{{ cid }} .element-bottomshadow:after{opacity:{{ values.shadowopacity }};}
</style>

<div {{{ _.fusionGetAttributes( attr ) }}}>
	<div {{{ _.fusionGetAttributes( attrReadingBox ) }}}>
		<# if ( '' !== values.description || '' !== values.element_content ) { #>

			<# if ( '' !== values.link && '' !== values.button && 'center' !== values.content_alignment ) { #>
				<a {{{ _.fusionGetAttributes( desktopAttrButton ) }}}><span {{{ _.fusionGetAttributes( buttonSpanAttr ) }}}>{{{ values.button }}}</span></a>
			<# } #>

			<# if ( '' !== values.title ) { #>
				<h2 {{{ _.fusionGetAttributes( titleAttr ) }}}>{{{ values.title }}}</h2>
			<# } #>

			<# if ( '' !== values.description ) { #>
				<div {{{ _.fusionGetAttributes( descriptionAttr ) }}}>{{{ values.description }}}</div>
			<# } #>

			<# if ( '' !== values.element_content ) { #>
				<div {{{ _.fusionGetAttributes( contentAttr ) }}}>{{{ FusionPageBuilderApp.renderContent( values.element_content, cid, false ) }}}</div>
			<# } #>

			<div class="fusion-clearfix"></div>

		<# } else if ( 'center' === values.content_alignment ) { #>
			<# if ( '' !== values.title ) { #>
				<h2 {{{ _.fusionGetAttributes( titleAttr ) }}}>{{{ values.title }}}</h2>
			<# } #>

		<# } else { #>
			<div class="fusion-reading-box-flex">
				<# if ( 'left' === values.content_alignment ) { #>
					<# if ( '' !== values.title ) { #>
						<h2 {{{ _.fusionGetAttributes( titleAttr ) }}}>{{{ values.title }}}</h2>
					<# } #>

					<# if ( '' !== values.link && '' !== values.button && 'center' !== values.content_alignment ) { #>
						<a {{{ _.fusionGetAttributes( desktopAttrButton ) }}}><span {{{ _.fusionGetAttributes( buttonSpanAttr ) }}}>{{{ values.button }}}</span></a>
					<# } #>
				<# } else { #>
					<# if ( '' !== values.link && '' !== values.button && 'center' !== values.content_alignment ) { #>
						<a {{{ _.fusionGetAttributes( desktopAttrButton ) }}}><span {{{ _.fusionGetAttributes( buttonSpanAttr ) }}}>{{{ values.button }}}</span></a>
					<# } #>

					<# if ( '' !== values.title ) { #>
						<h2 {{{ _.fusionGetAttributes( titleAttr ) }}}>{{{ values.title }}}</h2>
					<# } #>
				<# } #>
			</div>
		<# } #>

		<# if ( '' !== values.link && '' !== values.button ) { #>
			<a {{{ _.fusionGetAttributes( mobileAttrButton ) }}}><span {{{ _.fusionGetAttributes( buttonSpanAttr ) }}}>{{{ values.button }}}</span></a>
		<# } #>
	</div>
</div>

</script>
