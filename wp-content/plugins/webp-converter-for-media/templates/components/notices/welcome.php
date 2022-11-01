<?php
/**
 * Notice displayed in admin panel.
 *
 * @var string $ajax_url     URL of admin-ajax.
 * @var string $close_action Action using in WP Ajax.
 * @var string $settings_url URL of plugin settings page (default view).
 *
 * @package Converter for Media
 */

?>
<div class="notice notice-success is-dismissible"
	data-notice="webp-converter-for-media"
	data-notice-action="<?php echo esc_attr( $close_action ); ?>"
	data-notice-url="<?php echo esc_url( $ajax_url ); ?>"
>
	<div class="webpcContent webpcContent--notice">
		<h4>
			<?php echo esc_html( __( 'Thank you for installing our plugin Converter for Media!', 'webp-converter-for-media' ) ); ?>
		</h4>
		<p>
			<?php
			echo wp_kses_post(
				sprintf(
				/* translators: %s: icon heart */
					__( 'Go to the plugin settings and optimize all your images with one click! Thank you for being with us! %s', 'webp-converter-for-media' ),
					'<span class="dashicons dashicons-heart"></span>'
				)
			);
			?>
		</p>
		<div class="webpcContent__buttons">
			<a href="<?php echo esc_url( $settings_url ); ?>"
				class="webpcContent__button webpcButton webpcButton--blue webpcButton--bg"
			>
				<?php echo esc_html( __( 'Speed up my website', 'webp-converter-for-media' ) ); ?>
			</a>
			<a href="https://url.mattplugins.com/converter-notice-welcome-button-video" target="_blank"
				class="webpcContent__button webpcButton webpcButton--blue"
			>
				<?php echo esc_html( __( 'Meet the plugin', 'webp-converter-for-media' ) ); ?>
			</a>
		</div>
		<img src="https://mattplugins.com/images/matt-plugins-logo.png" alt="">
	</div>
</div>
