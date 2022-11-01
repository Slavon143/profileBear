<?php
/**
 * Social-sharing template.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      5.1.0
 */

$social_icons = fusion_get_social_icons_class();

// $post_type is inherited from the avada_render_social_sharing() function.
$setting_name = ( 'post' === $post_type ) ? 'social_sharing_box' : $post_type . '_social_sharing_box';

if ( fusion_get_option( $setting_name ) ) {

	$full_image = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'full' );

	$title = the_title_attribute( // phpcs:ignore WordPress.WP.GlobalVariablesOverride
		[
			'echo' => false,
			'post' => get_the_ID(),
		]
	);

	$sharingbox_social_icon_options = [
		'sharingbox'        => 'yes',
		'icon_colors'       => Avada()->settings->get( 'sharing_social_links_icon_color' ),
		'box_colors'        => Avada()->settings->get( 'sharing_social_links_box_color' ),
		'icon_boxed'        => Avada()->settings->get( 'sharing_social_links_boxed' ),
		'tooltip_placement' => fusion_get_option( 'sharing_social_links_tooltip_placement' ),
		'linktarget'        => Avada()->settings->get( 'social_icons_new' ),
		'title'             => $title,
		'description'       => Avada()->blog->get_content_stripped_and_excerpted( 55, get_the_content() ),
		'link'              => get_permalink( get_the_ID() ),
		'pinterest_image'   => ( $full_image ) ? $full_image[0] : '',
	];
	?>
	<div class="fusion-sharing-box fusion-theme-sharing-box fusion-single-sharing-box">
		<h4><?php echo apply_filters( 'fusion_sharing_box_tagline', Avada()->settings->get( 'sharing_social_tagline' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h4>
		<?php echo fusion_library()->social_sharing->render_social_icons( $sharingbox_social_icon_options ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</div>
	<?php
}
