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
<div class="webpcField">
	<input type="checkbox"
		name="<?php echo esc_attr( $option['name'] ); ?>"
		value="yes"
		id="<?php echo esc_attr( $option['name'] ); ?>"
		class="webpcField__input webpcField__input--toggle"
		<?php echo ( ( $option['value'] === 'yes' ) ) ? 'checked' : ''; ?>
	>
	<label for="<?php echo esc_attr( $option['name'] ); ?>"></label>
	<span class="webpcField__label"><?php echo wp_kses_post( $option['info'] ); ?></span>
</div>
