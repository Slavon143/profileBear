<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_lightbox-shortcode">
<# if ( ! values.thumbnail_image ) { #>
<div class="fusion-builder-placeholder-preview">
	<i class="{{ icon }}" aria-hidden="true"></i> {{ label }} ({{ name }})
</div>
<# } else { #>
<a {{{ _.fusionGetAttributes( attr ) }}}>
	<img {{{ _.fusionGetAttributes( imgAttr ) }}}>
</a>
<# } #>
</script>
