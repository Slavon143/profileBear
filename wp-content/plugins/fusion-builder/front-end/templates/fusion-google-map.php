<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_map-shortcode">
<# 	if ( '' !== id ) { #>
		<div id="{{ id }}">
<#	}  #>

		<div {{{ _.fusionGetAttributes( googleMapShortcode ) }}}>{{{ html }}}</div>

<# 	if ( '' !== id ) { #>
		</div>
<#	}  #>
</script>
