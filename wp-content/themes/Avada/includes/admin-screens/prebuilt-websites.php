<?php
/**
 * Prebuilt Websites Admin page.
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

<?php self::get_admin_screens_header( 'prebuilt-websites' ); ?>

	<div class="updated error avada-db-card avada-db-notice importer-notice importer-notice-1" style="display: none;">
		<h2><?php esc_html_e( 'We\'re sorry but the demo data could not be imported', 'Avada' ); ?></h2>
		<p><?php esc_html_e( 'This is most likely due to low PHP configurations on your server. There are two possible solutions.', 'Avada' ); ?></p>

		<p><strong><?php esc_html_e( 'Solution 1:', 'Avada' ); ?></strong> <?php esc_html_e( 'Import the demo using an alternate method.', 'Avada' ); ?><a href="https://theme-fusion.com/documentation/avada/demo-content-info/alternate-demo-method/" class="button-primary" target="_blank" style="margin-left: 10px;"><?php esc_html_e( 'Alternate Method', 'Avada' ); ?></a></p>
		<?php /* translators: %1$s: RED. %2$s: "Reset WordPress Plugin" link. */ ?>
		<p><strong><?php esc_html_e( 'Solution 2:', 'Avada' ); ?></strong> <?php printf( __( 'Fix the PHP configurations reported in %1$s on the Status page, then use the %2$s, then reimport.', 'Avada' ), '<strong style="color: red;">' . esc_html__( 'RED', 'Avada' ) . '</strong>', '<a href="' . esc_url( admin_url( 'plugin-install.php?tab=plugin-information&amp;plugin=wordpress-reset&amp;TB_iframe=true&amp;width=830&amp;height=472' ) ) . '">' . esc_html__( 'Reset WordPress Plugin', 'Avada' ) . '</a>' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><a href="<?php echo esc_url( admin_url( 'admin.php?page=avada-status' ) ); ?>" class="button-primary" target="_blank" style="margin-left: 10px;"><?php esc_html_e( 'Status', 'Avada' ); ?></a></p>
	</div>

	<div class="updated avada-db-card avada-db-notice avada-db-notice-success importer-notice importer-notice-2" style="display: none;">
		<h2><?php esc_html_e( 'Demo data successfully imported', 'Avada' ); ?></h2>
		<?php /* translators: "Regenerate Thumbnails" plugin link. */ ?>
		<p><?php printf( esc_html__( 'Install and run %s plugin once if you would like images generated to the specific theme sizes. This is not needed if you upload your own images because WP does it automatically.', 'Avada' ), '<a href="' . esc_url( admin_url( 'plugin-install.php?tab=plugin-information&amp;plugin=regenerate-thumbnails&amp;TB_iframe=true&amp;width=830&amp;height=472' ) ) . '" class="thickbox" title="' . esc_html__( 'Regenerate Thumbnails', 'Avada' ) . '">' . esc_html__( 'Regenerate Thumbnails', 'Avada' ) . '</a>' ); ?></p>
		<?php /* translators: "Permalinks" link. */ ?>
		<p><?php printf( esc_html__( 'Please visit the %s page and change your permalinks structure to "Post Name" so that content links work properly.', 'Avada' ), '<a href="' . esc_url( admin_url( 'options-permalink.php' ) ) . '">' . esc_html__( 'Permalinks', 'Avada' ) . '</a>' ); ?></p>
	</div>

	<div class="updated error avada-db-card avada-db-notice importer-notice importer-notice-3" style="display: none;">
		<h2><?php esc_html_e( 'We\'re sorry but the demo data could not be imported', 'Avada' ); ?></h2>
		<p><?php esc_html_e( 'This is most likely due to low PHP configurations on your server. There are two possible solutions.', 'Avada' ); ?></p>

		<p><strong><?php esc_html_e( 'Solution 1:', 'Avada' ); ?></strong> <?php esc_html_e( 'Import the demo using an alternate method.', 'Avada' ); ?><a href="https://theme-fusion.com/documentation/avada/demo-content-info/alternate-demo-method/" class="button-primary" target="_blank" style="margin-left: 10px;"><?php esc_html_e( 'Alternate Method', 'Avada' ); ?></a></p>
		<?php /* translators: %1$s: RED. %2$s: "Reset WordPress Plugin" link. */ ?>
		<p><strong><?php esc_html_e( 'Solution 2:', 'Avada' ); ?></strong> <?php printf( esc_html__( 'Fix the PHP configurations reported in %1$s on the Status page, then use the %2$s, then reimport.', 'Avada' ), '<strong style="color: red;">' . esc_html__( 'RED', 'Avada' ) . '</strong>', '<a href="' . esc_url_raw( admin_url( 'plugin-install.php?tab=plugin-information&amp;plugin=wordpress-reset&amp;TB_iframe=true&amp;width=830&amp;height=472' ) ) . '">' . esc_html__( 'Reset WordPress Plugin', 'Avada' ) . '</a>' ); ?><a href="<?php echo esc_url( admin_url( 'admin.php?page=avada-status' ) ); ?>" class="button-primary" target="_blank" style="margin-left: 10px;"><?php esc_html_e( 'Status', 'Avada' ); ?></a></p>
	</div>

	<div class="updated error avada-db-card avada-db-notice importer-notice importer-notice-4" style="display: none;">
		<h2><?php esc_html_e( 'We\'re sorry but the demo data could not be imported. We were unable to find import file.', 'Avada' ); ?></h2>

		<p><strong><?php esc_html_e( 'Solution 1:', 'Avada' ); ?></strong> <?php esc_html_e( 'Import the demo using an alternate method.', 'Avada' ); ?><a href="https://theme-fusion.com/documentation/avada/demo-content-info/alternate-demo-method/" class="button-primary" target="_blank" style="margin-left: 10px;"><?php esc_html_e( 'Alternate Method', 'Avada' ); ?></a></p>
		<p><strong><?php esc_html_e( 'Solution 2:', 'Avada' ); ?></strong> <?php esc_html_e( 'Make sure WordPress directory permissions are correct and uploads directory is writable.', 'Avada' ); ?><a href="https://codex.wordpress.org/Changing_File_Permissions" class="button-primary" target="_blank" style="margin-left: 10px;"><?php esc_html_e( 'Learn More', 'Avada' ); ?></a></p>
	</div>

	<?php if ( Avada()->registration->should_show( 'prebuilt' ) ) : ?>
		<?php
		// Include the Avada_Importer_Data class if it doesn't exist.
		if ( ! class_exists( 'Avada_Importer_Data' ) ) {
			include_once Avada::$template_dir_path . '/includes/importer/class-avada-importer-data.php';
		}
		?>

		<section class="avada-db-card avada-db-card-first avada-db-demos-start">
			<h1 class="avada-db-demos-heading"><?php esc_html_e( 'Import A Prebuilt Website', 'Avada' ); ?></h1>
			<p><?php esc_html_e( 'Import any of the prebuilt websites below. Once done, your site will have the exact same look and feel as the the sites in the preview.', 'Avada' ); ?></p>

			<div class="avada-db-card-notice">
				<i class="fusiona-info-circle"></i>
				<p class="avada-db-card-notice-heading">
					<?php
					printf(
						/* translators: %1$s: "Status" link. %2$s: "View more info here" link. */
						esc_html__( 'Prebuilt website imports can vary in time. Please check the %1$s page to ensure your server meets all requirements for a successful import. Settings that need attention will be listed in red. %2$s.', 'Avada' ),
						'<a href="' . esc_url( admin_url( 'admin.php?page=avada-status' ) ) . '" target="_blank">' . esc_html__( 'Status', 'Avada' ) . '</a>',
						'<a href="' . esc_url( self::$theme_fusion_url ) . 'documentation/avada/demo-content-info/import-all-demo-content/" target="_blank">' . esc_attr__( 'View more info here', 'Avada' ) . '</a>'
					);
					?>
				</p>
			</div>
		</section>
		<?php



		$imported_data = get_option( 'fusion_import_data', [] );
		$import_stages = avada_get_demo_import_stages();

		$demos                = AWB_Prebuilt_Websites()->get_websites();
		$all_tags             = AWB_Prebuilt_Websites()->get_tags();
		$imported_demos_count = AWB_Prebuilt_Websites()->get_imported_websites();
		$plugin_dependencies  = AWB_Prebuilt_Websites()->get_plugins_info();
		?>
		<div class="avada-db-demos-wrapper">

			<?php
			/**
			 * Add the tag-selector.
			 */
			?>
			<section class="avada-db-demo-selector avada-db-card">
				<?php include Avada::$template_dir_path . '/includes/admin-screens/prebuilt-websites-tag-selector.php'; ?>
			</section>

			<section class="avada-db-demos-themes avada-db-card avada-db-card-transparent">
				<div class="feature-section theme-browser rendered">

					<?php
					foreach ( $demos as $demo => $demo_details ) { // Loop through all available demos.
						include Avada::$template_dir_path . '/includes/admin-screens/prebuilt-websites-demo.php';
					}
					?>
				</div>
			</section>
		</div>

		<div class="awb-modal-overlay preview-all"></div>
		<div id="dialog-demo-confirm" title="<?php esc_attr_e( 'Warning ', 'Avada' ); ?>"></div>

		<script>
			!function(t){t.fn.unveil=function(i,e){function n(){var i=a.filter(function(){var i=t(this);if(!i.is(":hidden")){var e=o.scrollTop(),n=e+o.height(),r=i.offset().top,s=r+i.height();return s>=e-u&&n+u>=r}});r=i.trigger("unveil"),a=a.not(r)}var r,o=t(window),u=i||0,s=window.devicePixelRatio>1,l=s?"data-src-retina":"data-src",a=this;return this.one("unveil",function(){var t=this.getAttribute(l);t=t||this.getAttribute("data-src"),t&&(this.setAttribute("src",t),"function"==typeof e&&e.call(this))}),o.on("scroll.unveil resize.unveil lookup.unveil",n),n(),this}}(window.jQuery||window.Zepto);
			jQuery(document).ready(function() { jQuery( 'img' ).unveil( 200 ); });
		</script>
	<?php else : ?>
		<div class="avada-db-card avada-db-notice">
			<h2><?php esc_html_e( 'Avada\'s Prebuilt Websites Can Only Be Imported With Valid Product Registration', 'Avada' ); ?></h2>

			<?php /* translators: "Product Registration" link. */ ?>
			<p><?php printf( esc_html__( 'Please visit the %s page and enter a valid purchase code to import the full prebuilt websites and the single pages through the page builder.', 'Avada' ), '<a href="' . esc_url_raw( admin_url( 'admin.php?page=avada#avada-db-registration' ) ) . '">' . esc_attr__( 'Product Registration', 'Avada' ) . '</a>' ); ?></p>
		</div>
	<?php endif; ?>
<?php $this->get_admin_screens_footer(); ?>
