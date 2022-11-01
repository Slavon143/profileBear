<?php
/**
 * ToTop template.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

$is_builder = ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() );

if ( 'off' !== Avada()->settings->get( 'status_totop' ) || $is_builder ) {
	$to_top_position = explode( '_', Avada()->settings->get( 'totop_position' ) );
	$to_top_position = isset( $to_top_position[1] ) ? 'to-top-' . $to_top_position[0] . ' to-top-' . $to_top_position[1] : 'to-top-' . $to_top_position[0];

	?>
	<section class="to-top-container <?php echo esc_attr( $to_top_position ); ?>" aria-labelledby="awb-to-top-label">
		<a href="#" id="toTop" class="fusion-top-top-link">
			<span id="awb-to-top-label" class="screen-reader-text"><?php esc_html_e( 'Go to Top', 'Avada' ); ?></span>
		</a>
	</section>
	<?php
}
