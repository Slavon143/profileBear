<?php
/**
 * Widget displayed on plugin settings page.
 *
 * @var string $url_debug_page URL of debug tag in settings page.
 *
 * @package Converter for Media
 */

?>
<div class="webpcPage__widget">
	<h3 class="webpcPage__widgetTitle">
		<?php echo esc_html( __( 'We are waiting for your message', 'webp-converter-for-media' ) ); ?>
	</h3>
	<div class="webpcContent">
		<p>
			<?php
			echo esc_html(
				__( 'Do you have any technical problems or an idea for a new feature? Feel free to contact us.', 'webp-converter-for-media' )
			);
			?>
		</p>
		<p class="center">
			<a href="<?php echo esc_attr( $url_debug_page ); ?>"
				class="webpcButton webpcButton--blue"
			>
				<?php echo esc_html( __( 'Help Center', 'webp-converter-for-media' ) ); ?>
			</a>
		</p>
	</div>
</div>
