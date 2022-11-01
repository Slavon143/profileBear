<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 3.5
 */

?>
<script type="text/html" id="tmpl-fusion_flickr-shortcode">
				{{{ marginStyles }}}
				{{{ columnStyles }}}
				{{{ aspectRatio }}}
				<div {{{ _.fusionGetAttributes( atts ) }}}>
				<# if ( '' !== flickrItems ) { #>
						{{{ flickrItems }}}
				<# } else { #>
					<div class="fusion-loading-container fusion-clearfix">
						<div class="fusion-loading-spinner">
							<div class="fusion-spinner-1"></div>
							<div class="fusion-spinner-2"></div>
							<div class="fusion-spinner-3"></div>
						</div>
					</div>
				<# } #>

				</div>

</script>
