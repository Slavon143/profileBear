<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 3.5
 */

?>
<script type="text/html" id="tmpl-fusion_tagcloud-shortcode">
	<div>
		{{{ styles }}}
		{{{ marginStyles }}}
		<div {{{ _.fusionGetAttributes( tagcloudAttr ) }}}>
			{{{ tagCloudItems }}}
		</div>
	</div>
</script>
