<?php
/**
 * Header-3-content template.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      5.1.0
 */

if ( 'v4' !== Avada()->settings->get( 'header_layout' ) && 'top' === fusion_get_option( 'header_position' ) ) {
	return;
}

$header_content_3 = fusion_get_option( 'header_v4_content' );
?>

<div class="fusion-header-content-3-wrapper">
	<?php if ( 'tagline' === $header_content_3 ) : ?>
		<h3 class="fusion-header-tagline">
			<?php echo do_shortcode( Avada()->settings->get( 'header_tagline' ) ); ?>
		</h3>
	<?php elseif ( 'tagline_and_search' === $header_content_3 ) : ?>
		<h3 class="fusion-header-tagline">
			<?php echo do_shortcode( Avada()->settings->get( 'header_tagline' ) ); ?>
		</h3>
		<div class="fusion-secondary-menu-search">
			<?php get_search_form( true ); ?>
		</div>
	<?php elseif ( 'search' === $header_content_3 ) : ?>
		<div class="fusion-secondary-menu-search">
			<?php get_search_form( true ); ?>
		</div>
	<?php elseif ( 'banner' === $header_content_3 ) : ?>
		<div class="fusion-header-banner">
			<?php echo do_shortcode( Avada()->settings->get( 'header_banner_code' ) ); ?>
		</div>
	<?php endif; ?>
</div>
