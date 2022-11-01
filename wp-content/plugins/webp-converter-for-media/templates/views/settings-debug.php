<?php
/**
 * Debug tab of plugin settings page.
 *
 * @var string     $logo_url              Plugin logo.
 * @var string[][] $menu_items            Tabs on plugin settings page.
 * @var string[][] $errors_messages       Arrays with array of paragraphs.
 * @var string[]   $errors_codes          List of server configuration errors.
 * @var string     $size_png_path         Size of file.
 * @var string     $size_png2_path        Size of file.
 * @var string     $size_png_url          Size of file.
 * @var string     $size_png2_url         Size of file.
 * @var string     $size_png_as_webp_url  Size of file.
 * @var string     $size_png2_as_webp_url Size of file.
 * @var mixed[]    $plugin_settings       Option keys with values.
 * @var string     $url_debug_page        URL of debug tag in settings page.
 *
 * @package Converter for Media
 */

?>
<div class="wrap">
	<hr class="wp-header-end">
	<div class="webpcPage">
		<div class="webpcPage__headline">
			<img src="<?php echo esc_attr( $logo_url ); ?>" alt="<?php echo esc_attr( 'Converter for Media' ); ?>">
		</div>
		<div class="webpcPage__inner">
			<ul class="webpcPage__columns">
				<li class="webpcPage__column webpcPage__column--large">
					<?php
					require_once dirname( __DIR__ ) . '/components/widgets/errors.php';
					require_once dirname( __DIR__ ) . '/components/widgets/menu.php';
					require_once dirname( __DIR__ ) . '/components/widgets/server.php';
					?>
				</li>
				<li class="webpcPage__column webpcPage__column--small">
					<?php
					require_once dirname( __DIR__ ) . '/components/widgets/about.php';
					?>
				</li>
			</ul>
		</div>
		<div class="webpcPage__footer">
			<div class="webpcPage__footerLogo"></div>
			<div class="webpcPage__footerContent">
				<?php
				echo wp_kses_post(
					sprintf(
					/* translators: %1$s: br tag, %2$s: icon heart */
						__( 'Created with %1$s by %2$s - if you like our plugin, please %3$srate one%4$s%5$s', 'webp-converter-for-media' ),
						'<span class="webpcPage__footerIcon webpcPage__footerIcon--heart"></span>',
						'<a href="https://url.mattplugins.com/converter-settings-footer-author-website" target="_blank">matt plugins</a>',
						'<a href="https://url.mattplugins.com/converter-settings-footer-plugin-review" target="_blank">',
						' <span class="webpcPage__footerIcon webpcPage__footerIcon--stars"></span>',
						'</a>'
					)
				);
				?>
			</div>
		</div>
	</div>
</div>
