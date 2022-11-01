<?php
/**
 * Field displayed in plugin settings form.
 *
 * @var mixed[] $option Data of field.
 * @var string  $index  Index of field.
 *
 * @package Converter for Media
 */

?>
<?php if ( $option['info'] ) : ?>
	<p><?php echo wp_kses_post( $option['info'] ); ?></p>
<?php endif; ?>
<div class="webpcInput">
	<input type="text"
		name="<?php echo esc_attr( $option['name'] ); ?>"
		value="<?php echo esc_attr( $option['value'] ); ?>"
		id="<?php echo esc_attr( $option['name'] ); ?>"
		class="webpcInput__field"
	>
</div>
