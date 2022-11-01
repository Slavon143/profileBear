<?php
/**
 * Clone slide button template.
 *
 * @package Fusion-Slider
 * @subpackage Templates
 * @since 1.0.0
 */

?>
<div id="fusion-slide-clone">
	<?php
	$post_id = 0; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
	if ( isset( $_GET['post'] ) ) { // phpcs:ignore WordPress.Security
		$post_id = wp_unslash( $_GET['post'] ); // phpcs:ignore WordPress.Security, WordPress.WP.GlobalVariablesOverride
	}
	?>
	<a href="<?php echo esc_url_raw( $this->get_slide_clone_link( $post_id ) ); ?>" class="button">
		<?php esc_attr_e( 'Clone this slide', 'fusion-core' ); ?>
	</a>
</div>
