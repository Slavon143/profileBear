<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

$can_edit_theme_options   = current_user_can( 'edit_theme_options' );
$can_edit_published_pages = current_user_can( 'edit_published_pages' );
$can_edit_published_posts = current_user_can( 'edit_published_posts' );
$_post_type               = get_post_type();
$is_fusion_element        = apply_filters( 'fusion_hide_live_library_tab', ( 'fusion_element' === $_post_type && ! fusion_is_post_card() ) );
$po_name_array            = [
	'default'           => __( 'Page Options', 'Avada' ),
	'fusion_tb_section' => __( 'Layout Section Options', 'Avada' ),
	'fusion_form'       => __( 'Form Options', 'Avada' ),
	'awb_off_canvas'    => __( 'Off Canvas Options', 'Avada' ),
];

$po_name = isset( $po_name_array[ $_post_type ] ) ? $po_name_array[ $_post_type ] : $po_name_array['default'];
?>
<script type="text/template" id="fusion-builder-sidebar-template">
	<?php if ( $can_edit_theme_options || $can_edit_published_pages || $can_edit_published_posts ) : ?>
		<# var editorActive = 'undefined' !== typeof FusionApp ? FusionApp.builderActive : false; #>
		<div id="customize-controls" class="wrap wp-full-overlay-sidebar" data-context="{{ context }}" data-editor="{{ editorActive }}" data-dialog="{{ dialog }}" data-archive="<?php echo ( ( function_exists( 'is_archive' ) && is_archive() && ( ! function_exists( 'is_shop' ) || function_exists( 'is_shop' ) && ! is_shop() ) ) ? 'true' : 'false' ); ?>">
			<div id="customizer-content">
				<div class="fusion-builder-toggles">
					<?php if ( $can_edit_theme_options ) : ?>
						<a href="#fusion-builder-sections-to" class="fusion-active">
							<span class="icon fusiona-cog"></span>
							<span class="label"><?php esc_html_e( 'Global Options', 'Avada' ); ?></span>
						</a>
					<?php endif; ?>
					<?php if ( $can_edit_published_pages || $can_edit_published_posts ) : ?>
						<?php if ( ! $is_fusion_element ) : ?>
							<a href="#fusion-builder-sections-po">
								<span class="icon fusiona-settings"></span>
								<span class="label fusion-po-only" data_name="<?php echo esc_html( $po_name ); ?>" data-layout="<?php esc_attr_e( 'Layout Section Options', 'Avada' ); ?>" data-page="<?php esc_attr_e( 'Page Options', 'Avada' ); ?>"><?php echo esc_html( $po_name ); ?></span>
								<span class="label fusion-tax-only"><?php esc_html_e( 'Taxonomy Options', 'Avada' ); ?></span>
							</a>
						<?php endif; ?>
						<a href="#fusion-builder-sections-eo">
							<span class="icon fusiona-navigator"></span>
							<span class="icon fusiona-pen hidden"></span>

							<span class="label label-navigator">
								<span class="awb-navigator-toggle-containers"><i class="fusiona-caret-down"></i></span>
								<?php esc_html_e( 'Navigator', 'Avada' ); ?>
							</span>
							<span class="label label-options hidden"><?php esc_attr_e( 'Element Options', 'Avada' ); ?></span>
						</a>
					<?php endif; ?>
				</div>

				<?php if ( $can_edit_theme_options ) : ?>
					<div id="fusion-builder-sections-to" class="fusion-sidebar-section" data-context="TO">
						<div class="fusion-builder-search-wrapper">
							<input type="text" placeholder="<?php esc_attr_e( 'Search for global options', 'Avada' ); ?>" class="fusion-builder-search"/>
						</div>
						<div class="fusion-panels">
							<div class="fusion-panel-section-header-wrapper" data-context="FBE">
								<a href="#" class="fusion-builder-go-back" data-trigger="shortcode_styling" data-context="TO" title="<?php esc_attr_e( 'Back', 'Avada' ); ?>" aria-label="<?php esc_attr_e( 'Back', 'Avada' ); ?>">
									<svg version="1.1" width="18" height="18" viewBox="0 0 32 32"><path d="M12.586 27.414l-10-10c-0.781-0.781-0.781-2.047 0-2.828l10-10c0.781-0.781 2.047-0.781 2.828 0s0.781 2.047 0 2.828l-6.586 6.586h19.172c1.105 0 2 0.895 2 2s-0.895 2-2 2h-19.172l6.586 6.586c0.39 0.39 0.586 0.902 0.586 1.414s-0.195 1.024-0.586 1.414c-0.781 0.781-2.047 0.781-2.828 0z"></path></svg>
								</a>
								<span class="fusion-builder-tab-section-title"><?php esc_html_e( 'Avada Builder Elements', 'Avada' ); ?></span>
							</div>
							<div class="fusion-panel-section-header-wrapper" data-context="FBAO">
								<a href="#" class="fusion-builder-go-back" data-trigger="shortcode_styling" data-context="TO" title="<?php esc_attr_e( 'Back', 'Avada' ); ?>" aria-label="<?php esc_attr_e( 'Back', 'Avada' ); ?>">
									<svg version="1.1" width="18" height="18" viewBox="0 0 32 32"><path d="M12.586 27.414l-10-10c-0.781-0.781-0.781-2.047 0-2.828l10-10c0.781-0.781 2.047-0.781 2.828 0s0.781 2.047 0 2.828l-6.586 6.586h19.172c1.105 0 2 0.895 2 2s-0.895 2-2 2h-19.172l6.586 6.586c0.39 0.39 0.586 0.902 0.586 1.414s-0.195 1.024-0.586 1.414c-0.781 0.781-2.047 0.781-2.828 0z"></path></svg>
								</a>
								<span class="fusion-builder-tab-section-title"><?php esc_html_e( 'Add-on Elements', 'Avada' ); ?></span>
							</div>
						</div>
						<div class="fusion-tabs"></div>
					</div>
				<?php endif; ?>

				<?php if ( $can_edit_published_pages || $can_edit_published_posts ) : ?>
					<?php if ( ! $is_fusion_element ) : ?>
						<div id="fusion-builder-sections-po" style="display:none" class="fusion-sidebar-section">
							<div class="fusion-builder-search-wrapper">
								<input type="text" placeholder="<?php esc_attr_e( 'Search for page option(s)', 'Avada' ); ?>" class="fusion-builder-search fusion-po-only"/>
								<input type="text" placeholder="<?php esc_attr_e( 'Search for taxonomy option(s)', 'Avada' ); ?>" class="fusion-builder-search fusion-tax-only"/>
							</div>
							<div class="fusion-panels">
								<div class="fusion-empty-section">
									<?php esc_html_e( 'No page specific options are available for this page.', 'Avada' ); ?>
								</div>
							</div>
							<div class="fusion-tabs"></div>
						</div>
					<?php endif; ?>
					<div id="fusion-builder-sections-eo" style="display:none" class="fusion-sidebar-section">
						<div class="awb-builder-nav-wrapper">
							<div class="awb-builder-nav__loading-spinner-wrapper">
								<div class="awb-builder-nav__loading-spinner">
									<svg version="1.1" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 354.6 177.3" xml:space="preserve">
										<linearGradient id="SVG-loader-gradient-nav" gradientUnits="userSpaceOnUse" x1="70.3187" y1="247.6187" x2="284.3375" y2="33.6">
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
										<path d="M177.7,24.4c84.6,0,153.2,68.4,153.5,152.9h23.5C354.6,79.4,275.2,0,177.3,0S0,79.4,0,177.3h24.2C24.5,92.8,93.1,24.4,177.7,24.4z"/>
									</svg>
								</div>
							</div>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>
</script>
