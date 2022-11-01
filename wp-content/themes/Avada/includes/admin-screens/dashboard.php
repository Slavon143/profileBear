<?php
/**
 * Welcome Admin page.
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
<?php self::get_admin_screens_header( 'welcome' ); ?>
	<?php
	ob_start();
	Avada()->registration->the_form();
	$reg_form = ob_get_clean();
	?>

	<div class="avada-db-welcome-wrapper">
		<?php
		$completed_reg = Avada()->registration->appear_registered() ? ' avada-db-completed avada-db-onload-completed' : '';

		// Should Skip wizard permanntly?
		if ( isset( $_GET['skip-wizard'] ) && 'true' === $_GET['skip-wizard'] ) { // phpcs:ignore WordPress.Security.NonceVerification
			update_option( 'awb_skip_wizard', 'yes' );
		}

		$setup_completed = '';
		$the_user        = wp_get_current_user();
		$fresh_install   = get_transient( 'awb_fresh_install' );
		$should_skip     = get_option( 'awb_skip_wizard' );
		if ( 'yes' === $should_skip || ( $completed_reg && ( ! $fresh_install || 'fresh' !== $fresh_install ) ) ) {
			$setup_completed = ' avada-db-welcome-setup-completed';
		}
		?>
		<section id="avada-db-registration" class="avada-db-card avada-db-registration<?php echo esc_attr( $completed_reg ); ?>">
			<?php echo $reg_form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</section>

		<section class="avada-db-card avada-db-main-setup avada-db-welcome-setup<?php echo esc_attr( $setup_completed ); ?>">
			<div class="avada-db-welcome-container">
				<div class="avada-db-welcome-intro">
					<div class="avada-db-card-caption">
						<?php if ( '' === $setup_completed ) : ?>
							<?php /* translators: %s: The username. */ ?>
							<h1 class="avada-db-welcome-heading"><?php echo esc_html( sprintf( apply_filters( 'avada_admin_welcome_title', __( 'Welcome To Avada, %s!', 'Avada' ) ), ucfirst( $the_user->display_name ) ) ); ?></h1>
							<p class="avada-db-welcome-text"><?php echo esc_html( apply_filters( 'avada_admin_setup_welcome_text', __( 'Launch the Avada setup wizard, the easiest way to get your website started.', 'Avada' ) ) ); ?></p>
						<?php else : ?>
							<?php /* translators: %s: The username. */ ?>
							<h1 class="avada-db-welcome-heading"><?php echo esc_html( sprintf( apply_filters( 'avada_admin_welcome_title_completed', __( 'Hello %s!', 'Avada' ) ), ucfirst( $the_user->display_name ) ) ); ?></h1>
							<p class="avada-db-welcome-text"><?php echo esc_html( apply_filters( 'avada_admin_setup_welcome_text_completed', __( 'Welcome back to the Avada dashboard. Take a look at our latest update.', 'Avada' ) ) ); ?></p>
						<?php endif; ?>
					</div>

			<?php // Filter for the dashboard welcome content. ?>
			<?php ob_start(); ?>
			<?php if ( '' !== $setup_completed ) : ?>
					<a class="avada-db-welcome-video" href="#">
						<span class="avada-db-welcome-video-icon">
							<span class="avada-db-triangle"></span>
						</span>
						<?php /* translators: %s: version number. */ ?>
						<span class="avada-db-welcome-video-text"><?php echo esc_html( sprintf( __( 'What’s New In Avada %s', 'Avada' ), AVADA_VERSION ) ); ?></span>
					</a>
				<?php else : ?>
					<a class="avada-db-get-started-button" href="<?php echo esc_url( admin_url( 'admin.php?page=avada-setup' ) ); ?>"><?php esc_html_e( 'Get Started', 'Avada' ); ?></a>
				<?php endif; ?>
				</div>
				<div class="avada-db-welcome-media-container">
					<?php if ( '' !== $setup_completed ) : ?>
						<?php $welcome_video = self::get_dashboard_screen_video_url(); ?>
						<img class="avada-db-welcome-image" src="<?php echo esc_url( get_template_directory_uri() . '/assets/admin/images/welcome-completed.png' ); ?>" alt="<?php esc_html_e( 'Avada Welcome Image', 'Avada' ); ?>" width="646" height="400">
						<iframe class="avada-db-welcome-video-iframe" data-src="<?php echo esc_url( $welcome_video ); ?>" width="100%" height="100%" frameborder="0"></iframe>
					<?php else : ?>
						<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/admin/images/welcome.png' ); ?>" alt="<?php esc_html_e( 'Avada Welcome Image', 'Avada' ); ?>" width="646" height="400">
					<?php endif; ?>
				</div>
			</div>
			<?php $welcome_html = ob_get_clean(); ?>
			<?php echo apply_filters( 'avada_admin_welcome_screen_content', $welcome_html ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</section>

		<?php if ( true === apply_filters( 'avada_admin_display_additional_resources', true ) ) : ?>
		<section class="avada-db-card avada-db-welcome-resources">
			<h2 class="avada-db-card-heading-with-badge avada-db-welcome-resources-heading">
				<span class="avada-db-card-heading-text avada-db-welcome-resources-heading-text"><?php esc_html_e( 'Avada Resources', 'Avada' ); ?></span>
				<span class="avada-db-card-heading-badge avada-db-welcome-resources-heading-badge">
					<i class="fusiona-star"></i>
					<span class="avada-db-card-heading-badge-text"><?php esc_html_e( 'Recommended', 'Avada' ); ?></span>
				</span>
			</h2>

			<div class="avada-db-card-grid">
				<?php
					$dashboard_data     = Avada::get_data();
					$buy_button_classes = '';
					$notice_sale_class  = '';

				if ( ! empty( $dashboard_data['price'] ) ) {
					/* translators: Item price. */
					$buy_button_text = sprintf( esc_html__( 'Only %s - Buy Now', 'Avada' ), $dashboard_data['price'] );

					if ( ! empty( $dashboard_data['on_sale'] ) && $dashboard_data['on_sale'] ) {
						/* translators: Item price. */
						$buy_button_text    = sprintf( esc_html__( 'On Sale - Only %s', 'Avada' ), $dashboard_data['price'] );
						$buy_button_classes = ' avada-db-sale-button';
						$notice_sale_class  = ' avada-db-sale';
					}
				} else {
					$buy_button_text = esc_html__( 'Buy Another License', 'Avada' );
				}

				$resource_order = [ 2, 3, 4 ];
				shuffle( $resource_order );
				?>
				<div class="avada-db-card-notice avada-db-welcome-resources-license<?php echo esc_attr( $notice_sale_class ); ?>" data-sale="<?php esc_attr_e( 'Sale', 'Avada' ); ?>">
					<p class="avada-db-card-notice-heading-image">
						<a href="<?php echo esc_url( 'https://1.envato.market/nYa3R' ); ?>" class="avada-db-imgae-link" target="_blank" rel="noopener noreferrer">
							<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/admin/images/buy-avada.png' ); ?>" alt="<?php esc_html_e( 'Avada Logo', 'Avada' ); ?>" width="800" height="315" />
						</a>
					</p>
					<div class="avada-db-card-notice-heading">
						<h3><?php esc_html_e( 'Buy Another License', 'Avada' ); ?></h3>
					</div>
					<p class="avada-db-card-notice-content">
						<?php esc_html_e( 'Buy another license of the Avada Website Builder for your next project. Streamline your work and save time for the more important things.', 'Avada' ); ?>
					</p>
					<p class="avada-db-card-notice-content">
						<a href="<?php echo esc_url( 'https://1.envato.market/nYa3R' ); ?>" class="button button-primary<?php echo esc_attr( $buy_button_classes ); ?>" target="_blank" rel="noopener noreferrer"><span class="avada-db-buy-now-button-text"><?php echo esc_html( $buy_button_text ); ?></span></a>
					</p>
				</div>

				<div class="avada-db-card-notice avada-db-welcome-resources-hosting avada-db-sale" style="order:<?php echo esc_attr( $resource_order[0] ); ?>;<?php echo 4 === $resource_order[0] ? 'display:none;' : ''; ?>" data-sale="<?php esc_attr_e( 'Discount', 'Avada' ); ?>">
					<p class="avada-db-card-notice-heading-image">
						<a href="<?php echo esc_url( 'https://shareasale.com/r.cfm?b=1632110&u=873588&m=41388' ); ?>" class="avada-db-imgae-link" target="_blank" rel="noopener noreferrer">
							<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/admin/images/avada-wpe-large.png' ); ?>" alt="<?php esc_html_e( 'WPEngine Logo', 'Avada' ); ?>" width="800" height="315" />
						</a>
					</p>
					<div class="avada-db-card-notice-heading">
						<h3><?php esc_html_e( 'Avada Hosting', 'Avada' ); ?></h3>
					</div>
					<p class="avada-db-card-notice-content">
						<?php esc_html_e( 'Launch your site in seconds on WP Engine who offer optimized hosting for the Avada Website Builder.', 'Avada' ); ?><br />
						<?php esc_html_e( 'Enjoy 4 months free!', 'Avada' ); ?>
					</p>
					<p class="avada-db-card-notice-content">
						<a href="<?php echo esc_url( 'https://shareasale.com/r.cfm?b=1632110&u=873588&m=41388' ); ?>" class="button button-primary" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Get Special Offer', 'Avada' ); ?></a>
					</p>
				</div>

				<div class="avada-db-card-notice avada-db-welcome-resources-hosting avada-db-sale" style="order:<?php echo esc_attr( $resource_order[1] ); ?>;<?php echo 4 === $resource_order[1] ? 'display:none;' : ''; ?>" data-sale="<?php esc_attr_e( 'Discount', 'Avada' ); ?>">
					<p class="avada-db-card-notice-heading-image">
						<a href="<?php echo esc_url( 'https://www.siteground.com/avada?afcode=452502cf59bfef470b2806e5ba67670a' ); ?>" class="avada-db-imgae-link" target="_blank" rel="noopener noreferrer">
							<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/admin/images/avada-siteground-large.png' ); ?>" alt="<?php esc_html_e( 'WPEngine Logo', 'Avada' ); ?>" width="800" height="315" />
						</a>
					</p>
					<div class="avada-db-card-notice-heading">
						<h3><?php esc_html_e( 'Avada Special Hosting', 'Avada' ); ?></h3>
					</div>
					<p class="avada-db-card-notice-content">
						<?php esc_html_e( 'We have partnered with SiteGround to bring you full-service WordPress hosting. Sign up and have Avada installed and activated for you with one click.', 'Avada' ); ?><br />
					</p>
					<p class="avada-db-card-notice-content">
						<a href="<?php echo esc_url( 'https://www.siteground.com/avada?afcode=452502cf59bfef470b2806e5ba67670a' ); ?>" class="button button-primary" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Get Special Offer', 'Avada' ); ?></a>
					</p>
				</div>

				<div class="avada-db-card-notice avada-db-welcome-resources-customization" style="order:<?php echo esc_attr( $resource_order[2] ); ?>;<?php echo 4 === $resource_order[2] ? 'display:none;' : ''; ?>">
					<p class="avada-db-card-notice-heading-image">
						<a href="<?php echo esc_url( 'https://codeable.io/?ref=jMHpp' ); ?>" class="avada-db-imgae-link" target="_blank" rel="noopener noreferrer">
							<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/admin/images/avada-codeable.png' ); ?>" alt="<?php esc_html_e( 'Codeable Logo', 'Avada' ); ?>" width="800" height="315" />
						</a>
					</p>
					<div class="avada-db-card-notice-heading">
						<h3><?php esc_html_e( 'Avada Customization', 'Avada' ); ?></h3>
					</div>
					<p class="avada-db-card-notice-content">
						<?php esc_html_e( 'We work with Codeable who offers amazing customization services. They are equipped to handle both large and small customization jobs.', 'Avada' ); ?>
					</p>
					<p class="avada-db-card-notice-content">
						<a href="<?php echo esc_url( 'https://app.codeable.io/tasks/new?ref=jMHpp' ); ?>" class="button button-primary" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Get Free Quote', 'Avada' ); ?></a>
					</p>
				</div>
			</div>
		</section>

		<section class="avada-db-card avada-db-welcome-partners">
			<h2 class="avada-db-card-heading-with-badge avada-db-welcome-partners-heading">
				<span class="avada-db-card-heading-text avada-db-welcome-partners-heading-text"><?php esc_html_e( 'Avada Integrations', 'Avada' ); ?></span>
				<span class="avada-db-card-heading-badge avada-db-welcome-partners-heading-badge">
					<i class="fusiona-tag"></i>
					<span class="avada-db-card-heading-badge-text"><?php esc_html_e( 'Premium Additions', 'Avada' ); ?></span>
				</span>
			</h2>

			<div class="avada-db-card-grid">
				<div class="avada-db-card-notice avada-db-welcome-partners-hubspot">
					<p class="avada-db-card-notice-heading-image">
						<a href="<?php echo esc_url( 'https://hubs.to/39HcRH' ); ?>" class="avada-db-imgae-link" target="_blank" rel="noopener noreferrer">
							<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/admin/images/avada-hubspot.png' ); ?>" alt="<?php esc_html_e( 'HubSpot Logo', 'Avada' ); ?>" width="800" height="315" />
						</a>
					</p>
					<div class="avada-db-card-notice-heading">
						<h3><?php esc_html_e( 'CRM, Marketing & Sales', 'Avada' ); ?></h3>
					</div>
					<p class="avada-db-card-notice-content">
						<?php esc_html_e( 'HubSpot offers a full stack of software for marketing, sales, and also customer service, with a completely free CRM at its core. Grow now!', 'Avada' ); ?>
					</p>
					<p class="avada-db-card-notice-content">
						<a href="<?php echo esc_url( 'https://hubs.to/39HcRH' ); ?>" class="button button-primary" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'WP Marketing', 'Avada' ); ?></a>
					</p>
				</div>

				<div class="avada-db-card-notice avada-db-welcome-partners-wpml">
					<p class="avada-db-card-notice-heading-image">
						<a href="<?php echo esc_url( 'https://wpml.org/?aid=38405&affiliate_key=DYLA9bEPLvPY' ); ?>" class="avada-db-imgae-link" target="_blank" rel="noopener noreferrer">
							<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/admin/images/avada-wpml.png' ); ?>" alt="<?php esc_html_e( 'WPML Logo', 'Avada' ); ?>" width="400" height="315" />
						</a>
					</p>
					<div class="avada-db-card-notice-heading">
						<h3><?php esc_html_e( 'Multilingual Sites', 'Avada' ); ?></h3>
					</div>
					<p class="avada-db-card-notice-content">
						<?php esc_html_e( 'WPML makes it easy to build multilingual sites and run them. It\'s powerful enough for corporate sites, yet simple for blogs.', 'Avada' ); ?>
					</p>
					<p class="avada-db-card-notice-content">
						<a href="<?php echo esc_url( 'https://wpml.org/?aid=38405&affiliate_key=DYLA9bEPLvPY' ); ?>" class="button button-primary" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'WP Multilingual', 'Avada' ); ?></a>
					</p>
				</div>

				<div class="avada-db-card-notice avada-db-welcome-partners-ec">
					<p class="avada-db-card-notice-heading-image">
						<a href="<?php echo esc_url( 'https://theeventscalendar.pxf.io/c/1292887/975969/12892' ); ?>" class="avada-db-imgae-link" target="_blank" rel="noopener noreferrer">
							<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/admin/images/avada-events-calendar.png' ); ?>" alt="<?php esc_html_e( 'EC Logo', 'Avada' ); ?>" width="800" height="315" />
						</a>
					</p>
					<div class="avada-db-card-notice-heading">
						<h3><?php esc_html_e( 'Events Calendar', 'Avada' ); ?></h3>
					</div>
					<p class="avada-db-card-notice-content">
						<?php esc_html_e( 'Power your events for free with The Events Calendar, or upgrade to Pro to unlock recurring events, views, premium support, and more.', 'Avada' ); ?>
					</p>
					<p class="avada-db-card-notice-content">
						<a href="<?php echo esc_url( 'https://theeventscalendar.pxf.io/c/1292887/975969/12892' ); ?>" class="button button-primary" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'WP Events Calendar', 'Avada' ); ?></a>
					</p>
				</div>
			</div>
		</section>
		<?php endif; ?>
	</div>
	<?php $this->get_admin_screens_footer(); ?>
