<?php
/**
 * Main tab of plugin settings page.
 *
 * @var string       $logo_url                 Plugin logo.
 * @var string[][]   $menu_items               Tabs on plugin settings page.
 * @var string[][]   $errors_messages          Arrays with array of paragraphs.
 * @var string[]     $errors_codes             List of server configuration errors.
 * @var mixed[]|null $form_options             Settings options in main container.
 * @var mixed[]      $form_sidebar_options     Settings options in sidebar.
 * @var string       $form_input_name          Name of hidden field with form ID.
 * @var string|null  $form_input_value         ID of settings form in main container.
 * @var string       $form_sidebar_input_value ID of settings form in sidebar.
 * @var string       $nonce_input_name         Name of hidden field with WordPress Nonce value.
 * @var string       $nonce_input_value        WordPress Nonce value.
 * @var bool         $token_valid_status       Status of access token.
 * @var bool         $token_active_status      Status of PRO version.
 * @var string       $api_paths_url            URL of REST API endpoint.
 * @var string       $api_regenerate_url       URL of REST API endpoint.
 * @var string       $api_stats_url            URL of REST API endpoint.
 * @var string       $url_debug_page           URL of debug tag in settings page.
 * @var string[][]   $output_formats           Data about output formats for regeneration.
 *
 * @package Converter for Media
 */

?>
<div class="wrap">
	<hr class="wp-header-end">
	<div class="webpcPage" data-api-stats="<?php echo esc_url( $api_stats_url ); ?>">
		<div class="webpcPage__headline">
			<img src="<?php echo esc_attr( $logo_url ); ?>" alt="<?php echo esc_attr( 'Converter for Media' ); ?>">
		</div>
		<div class="webpcPage__inner">
			<ul class="webpcPage__columns">
				<li class="webpcPage__column webpcPage__column--large">
					<?php if ( ( ( $_POST[ $form_input_name ] ?? '' ) === $form_sidebar_input_value ) && $token_valid_status ) : // phpcs:ignore ?>
						<div class="webpcPage__alert">
							<?php echo esc_html( __( 'The access token has been activated!', 'webp-converter-for-media' ) ); ?>
						</div>
					<?php elseif ( isset( $_POST[ $form_input_name ] ) ) : // phpcs:ignore ?>
						<div class="webpcPage__alert">
							<?php echo esc_html( __( 'Changes were successfully saved!', 'webp-converter-for-media' ) ); ?>
							<?php echo esc_html( __( 'Please flush cache if you use caching plugin or caching via hosting.', 'webp-converter-for-media' ) ); ?>
						</div>
					<?php endif; ?>
					<?php
					require_once dirname( __DIR__ ) . '/components/widgets/errors.php';
					require_once dirname( __DIR__ ) . '/components/widgets/menu.php';
					if ( ( $form_options !== null ) && ( $form_input_value !== null ) ) {
						require_once dirname( __DIR__ ) . '/components/widgets/options.php';
					}
					require_once dirname( __DIR__ ) . '/components/widgets/regenerate.php';
					?>
				</li>
				<li class="webpcPage__column webpcPage__column--small">
					<?php
					require_once dirname( __DIR__ ) . '/components/widgets/options-sidebar.php';
					require_once dirname( __DIR__ ) . '/components/widgets/about.php';
					require_once dirname( __DIR__ ) . '/components/widgets/support.php';
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
