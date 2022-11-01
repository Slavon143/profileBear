<?php
/**
 * Field displayed in plugin settings form.
 *
 * @var bool    $token_valid_status  Status of access token.
 * @var bool    $token_active_status Status of PRO version.
 * @var mixed[] $option              Data of field.
 * @var string  $index               Index of field.
 *
 * @package Converter for Media
 */

?>
<?php if ( $option['info'] ) : ?>
	<p><?php echo wp_kses_post( $option['info'] ); ?></p>
<?php endif; ?>
<div class="webpcInput">
	<input type="text"
		name="<?php echo esc_attr( ( $token_valid_status ) ? '' : $option['name'] ); ?>"
		value="<?php echo esc_attr( $option['value_public'] ); ?>"
		id="<?php echo esc_attr( $option['name'] ); ?>"
		class="webpcInput__field"
		<?php echo ( $token_valid_status ) ? 'readonly' : ''; ?>
	>
</div>
<?php if ( ! $token_active_status ) : ?>
	<p data-plans>
		<strong><?php echo esc_html( __( 'Which plan would be the best choice for me?', 'webp-converter-for-media' ) ); ?></strong>
		<br>
		<a href="https://url.mattplugins.com/converter-field-access-token-plans?webp=0&avif=0" target="_blank"
			data-plans-button hidden>
			<?php echo esc_html( __( 'Check the plans for you', 'webp-converter-for-media' ) ); ?>
		</a>
		<span class="webpcContent__loader" data-plans-loader>
			<?php echo esc_html( __( 'Loading, please wait', 'webp-converter-for-media' ) ); ?>
		</span>
	</p>
<?php endif; ?>
