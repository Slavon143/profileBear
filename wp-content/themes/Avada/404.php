<?php
/**
 * The template used for 404 pages.
 *
 * @package Avada
 * @subpackage Templates
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}
?>
<?php get_header(); ?>
<section id="content" class="full-width">
	<div id="post-404page">
		<div class="post-content">
			<?php
			// Render the page titles.
			$subtitle = esc_html__( 'Oops, This Page Could Not Be Found!', 'Avada' );
			Avada()->template->title_template( $subtitle );
			?>
			<div class="fusion-clearfix"></div>
			<div class="error-page">
				<div class="fusion-columns fusion-columns-3">
					<div class="fusion-column col-lg-4 col-md-4 col-sm-4 fusion-error-page-404">
						<div class="error-message">404</div>
					</div>
					<div class="fusion-column col-lg-4 col-md-4 col-sm-4 useful-links fusion-error-page-useful-links">
						<h3><?php esc_html_e( 'Helpful Links', 'Avada' ); ?></h3>
						<?php
						// Get needed checklist default settings.
						$circle_class             = ( Avada()->settings->get( 'checklist_circle' ) ) ? 'circle-yes' : 'circle-no';
						$checklist_base_font_size = Avada()->settings->get( 'checklist_item_size' );
						$body_font_size           = Avada()->settings->get( 'body_typography', 'font-size' );
						$font_size                = $checklist_base_font_size ? Fusion_Sanitize::convert_font_size_to_px( $checklist_base_font_size, $body_font_size ) : Fusion_Sanitize::convert_font_size_to_px( $body_font_size, $body_font_size );
						$checklist_divider        = ( 'yes' === Avada()->settings->get( 'checklist_divider' ) ) ? ' fusion-checklist-divider' : '';

						// Calculated derived values.
						$circle_yes_font_size    = $font_size * 0.88;
						$line_height             = $font_size * 1.7;
						$icon_margin             = $font_size * 0.7;
						$icon_margin_position    = ( is_rtl() ) ? 'left' : 'right';
						$content_margin          = $line_height + $icon_margin;
						$content_margin_position = ( is_rtl() ) ? 'right' : 'left';

						// Set markup depending on icon circle being used or not.
						if ( Avada()->settings->get( 'checklist_circle' ) ) {
							$before = '<span class="icon-wrapper circle-yes" style="background-color:var(--checklist_circle_color);font-size:' . $font_size . 'px;height:' . $line_height . 'px;width:' . $line_height . 'px;margin-' . $icon_margin_position . ':' . $icon_margin . 'px;" ><i class="fusion-li-icon fa fa-angle-right" style="color:var(--checklist_icons_color);" aria-hidden="true"></i></span><div class="fusion-li-item-content" style="margin-' . $content_margin_position . ':' . $content_margin . 'px;">';
						} else {
							$before = '<span class="icon-wrapper circle-no" style="font-size:' . $font_size . 'px;height:' . $line_height . 'px;width:' . $line_height . 'px;margin-' . $icon_margin_position . ':' . $icon_margin . 'px;" ><i class="fusion-li-icon fa fa-angle-right" style="color:var(--checklist_icons_color);" aria-hidden="true"></i></span><div class="fusion-li-item-content" style="margin-' . $content_margin_position . ':' . $content_margin . 'px;">';
						}

						$error_page_menu_args = [
							'theme_location' => '404_pages',
							'depth'          => 1,
							'container'      => false,
							'menu_id'        => 'fusion-checklist-1',
							'menu_class'     => 'fusion-checklist fusion-404-checklist error-menu' . $checklist_divider,
							'items_wrap'     => '<ul id="%1$s" class="%2$s" style="font-size:' . $font_size . 'px;line-height:' . $line_height . 'px;">%3$s</ul>',
							'before'         => $before,
							'after'          => '</div>',
							'echo'           => 0,
							'item_spacing'   => 'discard',
							'fallback_cb'    => 'fusion_error_page_menu_fallback',
						];

						// Get the menu markup with correct containers.
						$error_page_menu = wp_nav_menu( $error_page_menu_args );

						/**
						 * Fallback to main menu if no 404 menu is set.
						 *
						 * @since 5.5
						 * @param array $error_page_menu_args The menu arguments.
						 * @return string|false
						 */
						function fusion_error_page_menu_fallback( $error_page_menu_args ) {
							if ( has_nav_menu( 'main_navigation' ) ) {
								$error_page_menu_args['theme_location'] = 'main_navigation';
							}

							unset( $error_page_menu_args['fallback_cb'] );

							return wp_nav_menu( $error_page_menu_args );
						}

						// Make sure divider lines have correct color.
						if ( $checklist_divider ) {
							$error_page_menu = str_replace( 'class="menu-item ', 'style="border-bottom-color:var(--checklist_divider_color);" class="menu-item ', $error_page_menu );
						}

						echo $error_page_menu; // phpcs:ignore WordPress.Security.EscapeOutput
						?>
					</div>
					<div class="fusion-column col-lg-4 col-md-4 col-sm-4 fusion-error-page-search">
						<h3><?php esc_html_e( 'Search Our Website', 'Avada' ); ?></h3>
						<p><?php esc_html_e( 'Can\'t find what you need? Take a moment and do a search below!', 'Avada' ); ?></p>
						<div class="search-page-search-form">
							<?php echo get_search_form( false ); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
<?php get_footer(); ?>
