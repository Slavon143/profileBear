<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.1
 */

?>
<script type="text/html" id="tmpl-fusion_audio-shortcode">
	<div {{{ _.fusionGetAttributes( attr ) }}}>
		<audio style="visibility: hidden" controls class="wp-audio-shortcode" width="100%" preload="none">
			<source src="{{ attr.values.src }}"/>
		</audio>
		<#
		if ( ! _.isEmpty( attr.values.src ) ) {
			setTimeout( function() {
				window.frames[0].wp.mediaelement.initialize();
			}, 100 );
		}
		#>
	</div>
</script>
