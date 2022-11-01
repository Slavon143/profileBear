<?php
/**
 * Information about registered image sub-sizes.
 *
 * @package Converter for Media
 */

$image_sizes = ( function_exists( 'wp_get_registered_image_subsizes' ) )
	? wp_get_registered_image_subsizes()
	: wp_get_additional_image_sizes();

?>
<h4>
	<?php
	echo esc_html(
		__( 'Registered image sub-sizes for generating images in additional sizes by WordPress', 'webp-converter-for-media' )
	);
	?>
</h4>
<table>
	<tbody>
	<?php foreach ( $image_sizes as $size_name => $size_data ) : ?>
		<tr>
			<td class="e"><?php echo esc_html( $size_name ); ?></td>
			<td class="v">
				<?php echo esc_html( json_encode( $size_data ) ?: '-' ); ?>
			</td>
		</tr>
	<?php endforeach; ?>
</table>
