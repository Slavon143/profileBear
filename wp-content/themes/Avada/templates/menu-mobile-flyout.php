<?php
/**
 * Mobile flyout menu template.
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
?>
<div class="fusion-flyout-menu-icons fusion-flyout-mobile-menu-icons">
	<?php echo avada_flyout_menu_woo_cart(); // phpcs:ignore WordPress.Security.EscapeOutput ?>

	<?php if ( 'menu' === Avada()->settings->get( 'slidingbar_toggle_style' ) && Avada()->settings->get( 'mobile_slidingbar_widgets' ) ) : ?>
		<?php $sliding_bar_label = esc_attr__( 'Toggle Sliding Bar', 'Avada' ); ?>
		<div class="fusion-flyout-sliding-bar-toggle">
			<a href="#" class="fusion-toggle-icon fusion-icon fusion-icon-sliding-bar" aria-label="<?php echo esc_attr( $sliding_bar_label ); ?>"></a>
		</div>
	<?php endif; ?>

	<?php if ( Avada()->settings->get( 'mobile_menu_search' ) ) : ?>
		<div class="fusion-flyout-search-toggle">
			<div class="fusion-toggle-icon">
				<div class="fusion-toggle-icon-line"></div>
				<div class="fusion-toggle-icon-line"></div>
				<div class="fusion-toggle-icon-line"></div>
			</div>
			<a class="fusion-icon awb-icon-search" aria-hidden="true" aria-label="<?php esc_attr_e( 'Toggle Search', 'Avada' ); ?>" href="#"></a>
		</div>
	<?php endif; ?>

	<?php // Make sure mobile menu toggle is not loaded when ubermenu is used. ?>
	<?php if ( ! function_exists( 'ubermenu_get_menu_instance_by_theme_location' ) || ( function_exists( 'ubermenu_get_menu_instance_by_theme_location' ) && ( ! ubermenu_get_menu_instance_by_theme_location( 'main_navigation' ) || ( ubermenu_get_menu_instance_by_theme_location( 'main_navigation' ) && function_exists( 'ubermenu_op' ) && 'on' === ubermenu_op( 'disable_mobile', 'main' ) && has_nav_menu( 'mobile_navigation' ) ) ) ) ) : ?>
		<a class="fusion-flyout-menu-toggle" aria-hidden="true" aria-label="<?php esc_attr_e( 'Toggle Menu', 'Avada' ); ?>" href="#">
			<div class="fusion-toggle-icon-line"></div>
			<div class="fusion-toggle-icon-line"></div>
			<div class="fusion-toggle-icon-line"></div>
		</a>
	<?php endif; ?>
</div>

<?php if ( Avada()->settings->get( 'mobile_menu_search' ) ) : ?>
	<div class="fusion-flyout-search">
		<?php get_search_form(); ?>
	</div>
<?php endif; ?>

<div class="fusion-flyout-menu-bg"></div>
<?php

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
