<?php
/**
 * Header-v6 template.
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
<div class="fusion-header-sticky-height"></div>
<div class="fusion-header">
	<div class="fusion-row">
		<div class="fusion-header-v6-content fusion-header-has-flyout-menu-content">
			<?php
			avada_logo();
			$menu = avada_main_menu( true ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
			?>

			<div class="fusion-flyout-menu-icons">
				<?php echo avada_flyout_menu_woo_cart(); // phpcs:ignore WordPress.Security.EscapeOutput ?>

				<?php if ( 'menu' === Avada()->settings->get( 'slidingbar_toggle_style' ) && Avada()->settings->get( 'slidingbar_widgets' ) ) : ?>
					<?php $sliding_bar_label = esc_attr__( 'Toggle Sliding Bar', 'Avada' ); ?>
					<div class="fusion-flyout-sliding-bar-toggle">
						<a href="#" class="fusion-toggle-icon fusion-icon awb-icon-sliding-bar" aria-label="<?php echo esc_attr( $sliding_bar_label ); ?>"></a>
					</div>
				<?php endif; ?>

				<?php if ( Avada()->settings->get( 'main_nav_search_icon' ) || Avada()->settings->get( 'mobile_menu_search' ) ) : ?>
					<div class="fusion-flyout-search-toggle">
						<div class="fusion-toggle-icon">
							<div class="fusion-toggle-icon-line"></div>
							<div class="fusion-toggle-icon-line"></div>
							<div class="fusion-toggle-icon-line"></div>
						</div>
						<a class="fusion-icon awb-icon-search" aria-hidden="true" aria-label="<?php esc_attr_e( 'Toggle Search', 'Avada' ); ?>" href="#"></a>
					</div>
				<?php endif; ?>

				<a class="fusion-flyout-menu-toggle" aria-hidden="true" aria-label="<?php esc_attr_e( 'Toggle Menu', 'Avada' ); ?>" href="#">
					<div class="fusion-toggle-icon-line"></div>
					<div class="fusion-toggle-icon-line"></div>
					<div class="fusion-toggle-icon-line"></div>
				</a>
			</div>
		</div>

		<div class="fusion-main-menu fusion-flyout-menu" role="navigation" aria-label="Main Menu">
			<?php echo $menu; // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>

		<?php if ( Avada()->settings->get( 'main_nav_search_icon' ) || Avada()->settings->get( 'mobile_menu_search' ) ) : ?>
			<div class="fusion-flyout-search">
				<?php get_search_form(); ?>
			</div>
		<?php endif; ?>

		<div class="fusion-flyout-menu-bg"></div>
	</div>
</div>
