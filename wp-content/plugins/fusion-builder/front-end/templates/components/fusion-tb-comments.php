<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_tb_comments-shortcode">
	<# if ( output !== '' ) { #>
		{{{styles}}}
		<div {{{ _.fusionGetAttributes( wrapperAttr ) }}}>
			{{{output}}}
		</div>
	<# } else if ( placeholder  ) { #>
		{{{placeholder}}}
	<# } #>
</script>
