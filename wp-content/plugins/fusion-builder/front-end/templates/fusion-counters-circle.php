<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_counters_circle-shortcode">
	<div {{{ _.fusionGetAttributes( countersCircleAtts ) }}}></div>
</script>

<script type="text/html" id="tmpl-fusion_counter_circle-shortcode">
	<div class="fusion-counter-circle-content-inner">
		{{{ FusionPageBuilderApp.renderContent( output, cid, parent ) }}}
	</div>
</script>
