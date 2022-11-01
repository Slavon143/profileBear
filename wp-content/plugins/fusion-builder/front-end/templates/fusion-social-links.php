<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_social_links-shortcode">
{{{styles}}}
<div {{{ _.fusionGetAttributes( socialLinksShortcode ) }}} >
	<div {{{ _.fusionGetAttributes( socialLinksShortcodeSocialNetworks ) }}}>
		<div class="fusion-social-networks-wrapper">
			{{{ icons }}}
		</div>
	</div>
</div>
</script>
