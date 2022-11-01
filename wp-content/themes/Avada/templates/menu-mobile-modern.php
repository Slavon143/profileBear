<?php
/**
 * Mobile modern menu template.
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
$displayed_menu = fusion_get_page_option( 'displayed_menu', fusion_library()->get_page_id() );
?>
<?php if ( 'modern' === Avada()->settings->get( 'mobile_menu_design' ) && ( has_nav_menu( 'main_navigation' ) || ( $displayed_menu && '' !== $displayed_menu && 'default' !== $displayed_menu ) ) ) : ?>
	<div class="fusion-mobile-menu-icons">
		<?php // Make sure mobile menu toggle is not loaded when ubermenu is used. ?>
		<?php if ( ! function_exists( 'ubermenu_get_menu_instance_by_theme_location' ) || ( function_exists( 'ubermenu_get_menu_instance_by_theme_location' ) && ( ! ubermenu_get_menu_instance_by_theme_location( 'main_navigation' ) || ( ubermenu_get_menu_instance_by_theme_location( 'main_navigation' ) && function_exists( 'ubermenu_op' ) && 'on' === ubermenu_op( 'disable_mobile', 'main' ) && has_nav_menu( 'mobile_navigation' ) ) ) ) ) : ?>
			<a href="#" class="fusion-icon awb-icon-bars" aria-label="<?php esc_attr_e( 'Toggle mobile menu', 'Avada' ); ?>" aria-expanded="false"></a>
		<?php endif; ?>

		<?php if ( Avada()->settings->get( 'mobile_menu_search' ) ) : ?>
			<a href="#" class="fusion-icon awb-icon-search" aria-label="<?php esc_attr_e( 'Toggle mobile search', 'Avada' ); ?>"></a>
		<?php endif; ?>

		<?php if ( 'menu' === Avada()->settings->get( 'slidingbar_toggle_style' ) && Avada()->settings->get( 'mobile_slidingbar_widgets' ) ) : ?>
			<?php $sliding_bar_label = esc_attr__( 'Toggle Sliding Bar', 'Avada' ); ?>
			<a href="#" class="fusion-icon awb-icon-sliding-bar" aria-label="<?php echo esc_attr( $sliding_bar_label ); ?>"></a>
		<?php endif; ?>

		<?php if ( class_exists( 'WooCommerce' ) && Avada()->settings->get( 'woocommerce_cart_link_main_nav' ) ) : ?>
			<a href="<?php echo esc_url_raw( get_permalink( get_option( 'woocommerce_cart_page_id' ) ) ); ?>" class="fusion-icon awb-icon-shopping-cart"  aria-label="<?php esc_attr_e( 'Toggle mobile cart', 'Avada' ); ?>"></a>
		<?php endif; ?>
	</div>
<?php endif; ?>
