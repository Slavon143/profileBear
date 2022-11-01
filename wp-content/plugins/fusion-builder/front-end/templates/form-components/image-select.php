<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 3.1
 */

?>
<script type="text/html" id="tmpl-fusion_form_image_select-shortcode">
{{{ outerWrapper }}}
	<# if ( 'above' === labelPosition ) { #>
		{{{ elementLabel }}}
	<# } #>

	<fieldset class="fusion-child-element"></fieldset>

	<# if ( 'above' !== labelPosition ) { #>
		{{{ elementLabel }}}
	<# } #>
</div>
{{{ styles }}}
</script>
