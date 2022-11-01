<?php
/**
 * Widget displayed on plugin settings page.
 *
 * @package Converter for Media
 */

?>
<div class="webpcPage__widget">
	<h3 class="webpcPage__widgetTitle">
		<?php echo esc_html( __( 'How does it work?', 'webp-converter-for-media' ) ); ?>
	</h3>
	<div class="webpcContent">
		<p>
			<?php
			echo wp_kses_post(
				__( 'When the browser tries to download an image file, the server checks if it supports the AVIF format (if enabled in the plugin settings). If so, the browser will receive an equivalent of the original image in AVIF format. If it does not support AVIF, but supports the WebP format, the browser will receive an equivalent of the original image in WebP format. If the browser does not support either WebP or AVIF, the original image is loaded. This means full support for all browsers.', 'webp-converter-for-media' )
			);
			?>
		</p>
		<p>
			<?php
			echo wp_kses_post(
				__( 'The plugin in default loading mode (via .htaccess) does not change file URLs, so there are no problems with saving the page to the cache and the page generation time does not increase.', 'webp-converter-for-media' )
			);
			?>
		</p>
		<p class="center">
			<a href="https://url.mattplugins.com/converter-widget-about-button-instruction"
				target="_blank"
				class="webpcButton webpcButton--blue"
			>
				<?php echo esc_html( __( 'Find out more', 'webp-converter-for-media' ) ); ?>
			</a>
		</p>
	</div>
</div>
