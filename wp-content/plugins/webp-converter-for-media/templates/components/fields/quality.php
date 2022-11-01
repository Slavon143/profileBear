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
<div class="webpcQuality">
	<div class="webpcQuality__items">
		<?php foreach ( $option['values'] as $value => $label ) : ?>
			<div class="webpcQuality__item">
				<input type="radio"
					name="<?php echo esc_attr( $option['name'] ); ?>"
					value="<?php echo esc_attr( $value ); ?>"
					id="webpc-<?php echo esc_attr( $index ); ?>-<?php echo esc_attr( $value ); ?>"
					class="webpcQuality__itemInput"
					<?php echo ( $value == $option['value'] ) ? 'checked' : ''; // phpcs:ignore  ?>>
				<label for="webpc-<?php echo esc_attr( $index ); ?>-<?php echo esc_attr( $value ); ?>"
					class="webpcQuality__itemLabel"></label>
			</div>
		<?php endforeach; ?>
	</div>
	<div class="webpcQuality__texts">
		<div class="webpcQuality__text">
			<?php
			echo wp_kses_post(
				sprintf(
				/* translators: %1$s: Level name */
					__( '%s - maximum reduction of image size with quality loss', 'webp-converter-for-media' ),
					'<strong>' . __( 'Lossy', 'webp-converter-for-media' ) . '</strong>'
				)
			);
			?>
		</div>
		<div class="webpcQuality__text"></div>
		<div class="webpcQuality__text">
			<?php
			echo wp_kses_post(
				sprintf(
				/* translators: %1$s: Level name */
					__( '%s - reduction of image size without quality loss visible to the eye', 'webp-converter-for-media' ),
					'<strong>' . __( 'Optimal', 'webp-converter-for-media' ) . '</strong>'
				)
			);
			?>
		</div>
		<div class="webpcQuality__text"></div>
		<div class="webpcQuality__text">
			<?php
			echo wp_kses_post(
				sprintf(
				/* translators: %1$s: Level name */
					__( '%s - minimal reduction of image size without quality loss', 'webp-converter-for-media' ),
					'<strong>' . __( 'Lossless', 'webp-converter-for-media' ) . '</strong>'
				)
			);
			?>
		</div>
	</div>
</div>
