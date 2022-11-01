<?php
/**
 * Sidebar-1 template.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Fusion-Library
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

$sidebar_classes = apply_filters( 'awb_aside_1_tag_class', '' );
$sticky_sidebar  = false !== strpos( $sidebar_classes, 'fusion-sticky-sidebar' );
?>
<aside id="sidebar" class="<?php esc_attr_e( $sidebar_classes, 'fusion-builder' ); ?>" style="<?php esc_attr_e( apply_filters( 'awb_aside_1_tag_style', '' ), 'fusion-builder' ); ?>" data="<?php esc_attr_e( apply_filters( 'awb_aside_1_tag_data', '' ), 'fusion-builder' ); ?>">
	<?php if ( $sticky_sidebar ) : ?>
		<div class="fusion-sidebar-inner-content">
	<?php endif; ?>
		<?php if ( ! AWB_Widget_Framework()->has_sidebar() || 'left' === AWB_Widget_Framework()->sidebars['position'] || ( 'right' === AWB_Widget_Framework()->sidebars['position'] && ! AWB_Widget_Framework()->has_double_sidebars() ) ) : ?>
			<?php echo avada_display_sidenav( Avada()->fusion_library->get_page_id() ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php if ( class_exists( 'Tribe__Events__Main' ) && is_singular( 'tribe_events' ) && 'sidebar' === fusion_library()->get_option( 'ec_meta_layout' ) ) : ?>
				<?php do_action( 'tribe_events_single_event_before_the_meta' ); ?>
				<?php tribe_get_template_part( 'modules/meta' ); ?>
			<?php endif; ?>
		<?php endif; ?>

		<?php if ( isset( AWB_Widget_Framework()->sidebars['sidebar_1'] ) && AWB_Widget_Framework()->sidebars['sidebar_1'] ) : ?>
			<?php generated_dynamic_sidebar( AWB_Widget_Framework()->sidebars['sidebar_1'] ); ?>
		<?php endif; ?>
	<?php if ( $sticky_sidebar ) : ?>
		</div>
	<?php endif; ?>
</aside>
