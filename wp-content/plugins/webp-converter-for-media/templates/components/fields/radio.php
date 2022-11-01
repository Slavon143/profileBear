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
<?php foreach ( $option['values'] as $value => $label ) : ?>
	<div class="webpcField">
		<input type="radio"
			name="<?php echo esc_attr( $option['name'] ); ?>"
			value="<?php echo esc_attr( $value ); ?>"
			id="<?php echo esc_attr( $option['name'] . '-' . $value ); ?>"
			class="webpcField__input webpcField__input--radio"
			<?php echo ( in_array( $value, $option['disabled'] ) ) ? 'disabled' : ''; ?>
			<?php echo ( $value == $option['value'] ) ? 'checked' : ''; // phpcs:ignore  ?>
		>
		<label for="<?php echo esc_attr( $option['name'] . '-' . $value ); ?>"></label>
		<span class="webpcField__label"><?php echo wp_kses_post( $label ); ?></span>
	</div>
<?php endforeach; ?>
