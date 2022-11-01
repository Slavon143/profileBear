<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_soundcloud-shortcode">
<div {{{ _.fusionGetAttributes( attr ) }}}>
	<iframe scrolling="no" frameborder="no" width="{{ values.width }}" height="{{ values.height }}" src="https://w.soundcloud.com/player/?url={{{ values.url }}}&amp;auto_play={{ values.autoplay }}&amp;hide_related={{ values.show_related }}&amp;show_comments={{ values.comments }}&amp;show_user={{ values.show_user }}&amp;show_reposts={{ values.show_reposts }}&amp;visual={{ values.visual }}&amp;color={{ values.color }}" title="soundcloud"></iframe>
</div>
</script>
