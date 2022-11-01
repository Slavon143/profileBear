<?php
/**
 * Widget displayed on plugin settings page.
 *
 * @var string  $size_png_path         Size of file.
 * @var string  $size_png2_path        Size of file.
 * @var string  $size_png_url          Size of file.
 * @var string  $size_png2_url         Size of file.
 * @var string  $size_png_as_webp_url  Size of file.
 * @var string  $size_png2_as_webp_url Size of file.
 * @var mixed[] $plugin_settings       Option keys with values.
 *
 * @package Converter for Media
 */

?>
<div class="webpcPage__widget">
	<div class="webpcContent">
		<div class="webpcPage__widgetRow">
			<p>
				<?php
				echo wp_kses_post(
					sprintf(
					/* translators: %1$s: open anchor tag, %2$s: close anchor tag, %3$s: open anchor tag, %4$s: close anchor tag */
						__( 'Please %1$scheck our FAQ%2$s before adding a thread with technical problem. If you do not find help there, %3$scheck support forum%4$s for similar problems. If you do not find a solution, please %5$scontact us%6$s.', 'webp-converter-for-media' ),
						'<a href="https://url.mattplugins.com/converter-tab-server-message-faq" target="_blank">',
						'</a>',
						'<a href="https://url.mattplugins.com/converter-tab-server-message-support" target="_blank">',
						'</a>',
						'<a href="https://url.mattplugins.com/converter-tab-server-message-contact" target="_blank">',
						'</a>'
					)
				);
				?>
			</p>
			<p>
				<?php
				echo wp_kses_post(
					__( 'Please attach to your message the configuration of your server (which is available below), e.g. as a screenshot.', 'webp-converter-for-media' )
				);
				?>
			</p>
		</div>
		<div class="webpcPage__widgetRow">
			<div class="webpcServerInfo">
				<?php
				require_once dirname( __DIR__ ) . '/server/debug.php';
				require_once dirname( __DIR__ ) . '/server/filters.php';
				require_once dirname( __DIR__ ) . '/server/wordpress.php';
				require_once dirname( __DIR__ ) . '/server/sub-sizes.php';
				require_once dirname( __DIR__ ) . '/server/options.php';
				require_once dirname( __DIR__ ) . '/server/php.php';
				require_once dirname( __DIR__ ) . '/server/gd.php';
				require_once dirname( __DIR__ ) . '/server/imagick.php';
				?>
			</div>
		</div>
	</div>
</div>
