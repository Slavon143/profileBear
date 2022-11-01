<?php
/**
 * Footer social icons template.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      5.3.0
 */

?>
<div class="fusion-social-links-footer">
	<?php
	$social_icons = fusion_get_social_icons_class();

	if ( $social_icons ) {
		$footer_social_icon_options = [
			'position'          => 'footer',
			'icon_boxed'        => Avada()->settings->get( 'footer_social_links_boxed' ),
			'tooltip_placement' => fusion_get_option( 'footer_social_links_tooltip_placement' ),
			'linktarget'        => Avada()->settings->get( 'social_icons_new' ),
		];

		echo $social_icons->render_social_icons( $footer_social_icon_options ); // phpcs:ignore WordPress.Security.EscapeOutput
	}
	?>
</div>
