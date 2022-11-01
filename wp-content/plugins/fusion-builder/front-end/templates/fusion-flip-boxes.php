<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_flip_boxes-shortcode">

<div {{{ _.fusionGetAttributes( flipBoxesShortcode ) }}}></div>
<div class="fusion-clearfix"></div>
</script>

<script type="text/html" id="tmpl-fusion_flip_box-shortcode">
	<div {{{ _.fusionGetAttributes( flipBoxAttributes ) }}}>
		<div class="flip-box-inner-wrapper">
			<div {{{ _.fusionGetAttributes( flipBoxShortcodeFrontBox ) }}}>
				<div class="flip-box-front-inner">
					{{{ icon_output }}} {{{ title_front_output }}} {{{ values.text_front }}}
				</div>
			</div>

			<div {{{ _.fusionGetAttributes( flipBoxShortcodeBackBox ) }}}>
				<div class="flip-box-back-inner">
					{{{ title_back_output }}}{{{ FusionPageBuilderApp.renderContent( values.element_content, cid, false )}}}
				</div>
			</div>
		</div>
	</div>
</script>
