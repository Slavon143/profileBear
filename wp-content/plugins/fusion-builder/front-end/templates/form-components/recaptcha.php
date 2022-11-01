<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 3.1
 */

?>
<script type="text/html" id="tmpl-fusion_form_recaptcha-shortcode">

<# if ( '' !== FusionApp.settings.recaptcha_public && '' !== FusionApp.settings.recaptcha_private ) { #>
<div class="fusion-builder-placeholder"><?php esc_html_e( 'reCAPTCHA will display here.', 'fusion-builder' ); ?></div>
<# } else { #>
<div class="fusion-builder-placeholder"><?php esc_html_e( 'reCAPTCHA configuration error. Please check the Global Options settings and your reCAPTCHA account settings.', 'fusion-builder' ); ?></div>
<# } #>
</script>
