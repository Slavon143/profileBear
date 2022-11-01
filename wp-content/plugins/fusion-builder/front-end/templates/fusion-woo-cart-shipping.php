<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_woo_cart_shipping-shortcode">
	{{{styles}}}
	<form {{{ _.fusionGetAttributes( wooCartShippingAttr ) }}} method="post" action="#">
		{{{ cart_shipping_content }}}
	</form>
</script>
