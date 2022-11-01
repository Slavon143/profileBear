<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 3.5
 */

?>
<script type="text/html" id="tmpl-fusion_star_rating-shortcode">
	<div {{{ _.fusionGetAttributes( elementAttr ) }}} >
		{{{ style }}}
		<div class="awb-stars-rating-icons-wrapper">{{{ iconsHtml }}}</div>
		<# if ( displayRatingText ) { #>
		<div class="awb-stars-rating-text">{{{ ratingTextHtml }}}</div>
		<# } #>
	</div>
</script>
