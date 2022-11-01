<?php
/**
 * Notice displayed in admin panel.
 *
 * @var string   $ajax_url     URL of admin-ajax.
 * @var string   $close_action Action using in WP Ajax.
 * @var string   $service_name .
 * @var string[] $steps        Instructions to follow.
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
		<p>
			<?php
			echo esc_html(
				sprintf(
				/* translators: %1$s: service name */
					__( 'You are using %1$s, right? Please follow the steps below for the plugin to function properly:', 'webp-converter-for-media' ),
					$service_name
				)
			);
			?>
		</p>
		<ul>
			<?php foreach ( $steps as $step_index => $step_message ) : ?>
				<li>
					<?php echo wp_kses_post( ( $step_index + 1 ) . '. ' . $step_message ); ?>
				</li>
			<?php endforeach; ?>
		</ul>
		<div class="webpcContent__buttons">
			<button type="button" data-permanently
				class="webpcContent__button webpcButton webpcButton--blue webpcButton--bg"
			>
				<?php echo esc_html( __( 'Done', 'webp-converter-for-media' ) ); ?>
			</button>
		</div>
	</div>
</div>
