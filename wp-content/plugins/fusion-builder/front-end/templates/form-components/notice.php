<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 3.1
 */

?>
<script type="text/html" id="tmpl-fusion_form_notice-shortcode">
	<# if ( showNotices ) { #>
	{{{ successAlert }}}
	{{{ errorAlert }}}
	<# } else { #>
	<div class="fusion-builder-placeholder"><?php echo __( 'Notices will only show if the <strong>Form Confirmation Type</strong> is set to <strong>Display Message</strong>.', 'fusion-builder' );  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
	<# } #>
</script>
