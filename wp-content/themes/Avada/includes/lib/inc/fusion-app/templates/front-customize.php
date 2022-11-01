<?php
/**
 * Loads our custom panel.
 *
 * @since 2.0
 * @package fusion-library
 */

?>
<html>
<head>
<?php
global $wp;
do_action( 'wp_enqueue_scripts' );
$permalink = fusion_app_get_permalink();
if ( '' === $permalink ) {
	$permalink = home_url();
}
$permalink = remove_query_arg( 'fb-edit', $permalink );

$page_id = fusion_library()->get_page_id();
if ( is_home() && get_option( 'page_on_front' ) ) {
	$page_id = get_option( 'page_on_front' );
}
?>
<?php if ( wp_is_mobile() ) : ?>
	<meta name="viewport" id="viewport-meta" content="width=device-width, initial-scale=1.0, minimum-scale=0.5, maximum-scale=1.2" />
<?php endif; ?>
<?php

// Create builder ID.
$builder_id   = md5( $page_id . time() );
$options_name = esc_attr( Fusion_Settings::get_option_name() );
$permalink    = add_query_arg( 'builder', 'true', $permalink );
$permalink    = add_query_arg( 'builder_id', esc_attr( $builder_id ), $permalink );
$preferences  = Fusion_App()->preferences->get_preferences();
$overlay_mode = isset( $preferences['sidebar_overlay'] ) && 'on' === $preferences['sidebar_overlay'] ? ' fusion-overlay-mode' : '';
?>
<title><?php esc_html_e( 'Avada Builder', 'Avada' ); // phpcs:ignore WPThemeReview.CoreFunctionality ?></title>

<script type="text/javascript">
// TODO: localize the following vars ?
var ajaxurl                  = <?php echo wp_json_encode( admin_url( 'admin-ajax.php', 'relative' ) ); ?>;
var includesURL              = "<?php echo esc_url_raw( trailingslashit( includes_url() ) ); ?>";
var builderId                = <?php echo wp_json_encode( $builder_id ); ?>;
var fusionOptionName         = originalOptionName = "<?php echo esc_attr( $options_name ); ?>";
var fusionOptionNetworkNames = <?php echo wp_json_encode( Fusion_Options_Map::get_option_map() ); ?>;
</script>
</head>
<body <?php body_class(); ?> style="padding:0; margin:0; overflow:hidden; height:100vh">
<div class="wp-full-overlay">
	<div id="customize-preview" class="wp-full-overlay-main<?php echo esc_attr( $overlay_mode ); ?>">
		<?php if ( $permalink ) : ?>
			<iframe id="fb-preview" name="fb-preview" data-viewport="desktop" title="<?php esc_attr_e( 'Site Preview', 'Avada' ); ?>" name="customize-preview-0" onmousewheel="" src="<?php echo esc_url_raw( $permalink ); ?>" frameborder="0"></iframe>
		<?php else : ?>
			<?php esc_html_e( 'Invalid Page ID', 'Avada' ); ?>
		<?php endif; ?>
		<div id="fb-preview-loader">
			<div class="fb-preview-loader-spinner">
				<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 354.6 177.3" style="width:0;height:0; xml:space="preserve">
					<linearGradient id="SVG-loader-gradient" gradientUnits="userSpaceOnUse" x1="70.3187" y1="247.6187" x2="284.3375" y2="33.6">
						<stop  offset="0.2079" style="stop-color:#FFFFFF;stop-opacity:0"/>
						<stop  offset="0.2139" style="stop-color:#FCFCFC;stop-opacity:7.604718e-03"/>
						<stop  offset="0.345" style="stop-color:#BABABA;stop-opacity:0.1731"/>
						<stop  offset="0.474" style="stop-color:#818181;stop-opacity:0.336"/>
						<stop  offset="0.5976" style="stop-color:#535353;stop-opacity:0.492"/>
						<stop  offset="0.7148" style="stop-color:#2F2F2F;stop-opacity:0.64"/>
						<stop  offset="0.8241" style="stop-color:#151515;stop-opacity:0.7779"/>
						<stop  offset="0.9223" style="stop-color:#050505;stop-opacity:0.9018"/>
						<stop  offset="1" style="stop-color:#000000"/>
					</linearGradient>
					<path class="st0" d="M177.7,24.4c84.6,0,153.2,68.4,153.5,152.9h23.5C354.6,79.4,275.2,0,177.3,0S0,79.4,0,177.3h24.2C24.5,92.8,93.1,24.4,177.7,24.4z"/>
				</svg>
			</div>
		</div>
	</div>
</div>
<?php
wp_footer();
fusion_the_admin_font_async();
?>
<form action="fusion_app_full_refresh" method="post" target="fb-preview" id="refresh-form"></form>
</body>
</html>
