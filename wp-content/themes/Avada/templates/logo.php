<?php
/**
 * Logo template.
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
$logo_opening_markup = '<div class="';
$logo_closing_markup = '</div>';
if ( 'v7' === Avada()->settings->get( 'header_layout' ) && ! Avada()->settings->get( 'logo_background' ) ) {
	$logo_opening_markup = '<li class="fusion-middle-logo-menu-logo ';
	$logo_closing_markup = '</li>';
} elseif ( 'v7' === Avada()->settings->get( 'header_layout' ) && Avada()->settings->get( 'logo_background' ) && 'top' === fusion_get_option( 'header_position' ) ) {
	$logo_opening_markup = '<li class="fusion-logo-background fusion-middle-logo-menu-logo"><div class="';
	$logo_closing_markup = '</div></li>';
} elseif ( Avada()->settings->get( 'logo_background' ) && 'v4' !== Avada()->settings->get( 'header_layout' ) && 'v5' !== Avada()->settings->get( 'header_layout' ) && 'top' === fusion_get_option( 'header_position' ) ) {
	$logo_opening_markup = '<div class="fusion-logo-background"><div class="';
	$logo_closing_markup = '</div></div>';
}
?>
<?php if ( '' !== Avada()->settings->get( 'logo', 'url' ) || '' !== Avada()->settings->get( 'logo_retina', 'url' ) ) : ?>
	<?php echo $logo_opening_markup; // phpcs:ignore WordPress.Security.EscapeOutput ?>fusion-logo" data-margin-top="<?php echo esc_attr( Avada()->settings->get( 'logo_margin', 'top' ) ); ?>" data-margin-bottom="<?php echo esc_attr( Avada()->settings->get( 'logo_margin', 'bottom' ) ); ?>" data-margin-left="<?php echo esc_attr( Avada()->settings->get( 'logo_margin', 'left' ) ); ?>" data-margin-right="<?php echo esc_attr( Avada()->settings->get( 'logo_margin', 'right' ) ); ?>">
<?php elseif ( 'v7' !== Avada()->settings->get( 'header_layout' ) ) : ?>
	<?php echo $logo_opening_markup; // phpcs:ignore WordPress.Security.EscapeOutput ?>fusion-logo" data-margin-top="0px" data-margin-bottom="0px" data-margin-left="0px" data-margin-right="0px">
<?php endif; ?>
<?php
	/**
	 * The avada_logo_prepend hook.
	 */
	do_action( 'avada_logo_prepend' );

	$logo_anchor_tag_attributes       = '';
	$custom_link                      = Avada()->settings->get( 'logo_custom_link' );
	$logo_anchor_tag_attributes_array = apply_filters(
		'avada_logo_anchor_tag_attributes',
		[
			'class' => 'fusion-logo-link',
			'href'  => $custom_link ? esc_url( $custom_link ) : esc_url( home_url( '/' ) ), // phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition.FoundInTernaryCondition
		]
	);

	foreach ( $logo_anchor_tag_attributes_array as $attribute => $value ) {
		if ( 'href' === $attribute ) {
			$value = esc_url( $value );
		} else {
			$value = esc_attr( $value );
		}

		$logo_anchor_tag_attributes .= ' ' . $attribute . '="' . $value . '" ';
	}

	$logo_alt_attribute = apply_filters( 'avada_logo_alt_tag', get_bloginfo( 'name', 'display' ) . ' ' . __( 'Logo', 'Avada' ) );
	?>
	<?php if ( ( Avada()->settings->get( 'logo', 'url' ) && '' !== Avada()->settings->get( 'logo', 'url' ) ) || ( Avada()->settings->get( 'logo_retina', 'url' ) && '' !== Avada()->settings->get( 'logo_retina', 'url' ) ) ) : ?>
		<a<?php echo $logo_anchor_tag_attributes; // phpcs:ignore WordPress.Security.EscapeOutput ?>>

			<?php $standard_logo = Avada()->images->get_logo_image_srcset( 'logo', 'logo_retina' ); ?>
			<!-- standard logo -->
			<img src="<?php echo esc_url_raw( $standard_logo['url'] ); ?>" srcset="<?php echo esc_attr( $standard_logo['srcset'] ); ?>" width="<?php echo esc_attr( $standard_logo['width'] ); ?>" height="<?php echo esc_attr( $standard_logo['height'] ); ?>"<?php echo $standard_logo['style']; // phpcs:ignore WordPress.Security.EscapeOutput ?> alt="<?php echo esc_attr( $logo_alt_attribute ); ?>" data-retina_logo_url="<?php echo esc_url_raw( $standard_logo['is_retina'] ); ?>" class="fusion-standard-logo" />

			<?php if ( Avada()->settings->get( 'mobile_logo', 'url' ) && '' !== Avada()->settings->get( 'mobile_logo', 'url' ) ) : ?>
				<?php $mobile_logo = Avada()->images->get_logo_image_srcset( 'mobile_logo', 'mobile_logo_retina' ); ?>
				<!-- mobile logo -->
				<img src="<?php echo esc_url_raw( $mobile_logo['url'] ); ?>" srcset="<?php echo esc_attr( $mobile_logo['srcset'] ); ?>" width="<?php echo esc_attr( $mobile_logo['width'] ); ?>" height="<?php echo esc_attr( $mobile_logo['height'] ); ?>"<?php echo $mobile_logo['style']; // phpcs:ignore WordPress.Security.EscapeOutput ?> alt="<?php echo esc_attr( $logo_alt_attribute ); ?>" data-retina_logo_url="<?php echo esc_url_raw( $mobile_logo['is_retina'] ); ?>" class="fusion-mobile-logo" />
			<?php endif; ?>

			<?php if ( Avada()->settings->get( 'sticky_header_logo', 'url' ) && '' !== Avada()->settings->get( 'sticky_header_logo', 'url' ) && ( in_array( Avada()->settings->get( 'header_layout' ), [ 'v1', 'v2', 'v3', 'v6', 'v7' ], true ) || ( ( in_array( Avada()->settings->get( 'header_layout' ), [ 'v4', 'v5' ], true ) && ( ( 'menu_and_logo' === Avada()->settings->get( 'header_sticky_type2_layout' ) && ( 'classic' === Avada()->settings->get( 'mobile_menu_design' ) || 'flyout' === Avada()->settings->get( 'mobile_menu_design' ) ) ) || 'modern' === Avada()->settings->get( 'mobile_menu_design' ) ) ) ) ) ) : ?>
				<?php $sticky_logo = Avada()->images->get_logo_image_srcset( 'sticky_header_logo', 'sticky_header_logo_retina' ); ?>
				<!-- sticky header logo -->
				<img src="<?php echo esc_url_raw( $sticky_logo['url'] ); ?>" srcset="<?php echo esc_attr( $sticky_logo['srcset'] ); ?>" width="<?php echo esc_attr( $sticky_logo['width'] ); ?>" height="<?php echo esc_attr( $sticky_logo['height'] ); ?>"<?php echo $sticky_logo['style']; // phpcs:ignore WordPress.Security.EscapeOutput ?> alt="<?php echo esc_attr( $logo_alt_attribute ); ?>" data-retina_logo_url="<?php echo esc_url_raw( $sticky_logo['is_retina'] ); ?>" class="fusion-sticky-logo" />
			<?php endif; ?>
		</a>
	<?php endif; ?>
	<?php
	/**
	 * The avada_logo_append hook.
	 *
	 * @hooked avada_header_content_3 - 10.
	 */
	do_action( 'avada_logo_append' );

	?>
<?php
echo $logo_closing_markup; // phpcs:ignore WordPress.Security.EscapeOutput
