<?php
/**
 * Notice displayed in admin panel.
 *
 * @var string $ajax_url     URL of admin-ajax.
 * @var string $close_action Action using in WP Ajax.
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
			<?php echo esc_html( __( 'Thank you for using our plugin Converter for Media!', 'webp-converter-for-media' ) ); ?>
		</h4>
		<p>
			<?php
			echo wp_kses_post(
				__( 'We are glad that you are using our plugin and we hope you are satisfied with it. If you want, you can support us in the development of the plugin by adding a plugin review. This is very important and gives us the opportunity to create even better tools for you. Thank you to everyone.', 'webp-converter-for-media' )
			);
			?>
		</p>
		<div class="webpcContent__buttons">
			<a href="https://url.mattplugins.com/converter-notice-thanks-button-review"
				target="_blank"
				class="webpcContent__button webpcButton webpcButton--blue webpcButton--bg"
			>
				<?php echo esc_html( __( 'Add plugin review', 'webp-converter-for-media' ) ); ?>
			</a>
			<button type="button" data-permanently
				class="webpcContent__button webpcButton webpcButton--gray webpcButton--bg"
			>
				<?php echo esc_html( __( 'Hide, do not show again', 'webp-converter-for-media' ) ); ?>
			</button>
		</div>
	</div>
</div>
