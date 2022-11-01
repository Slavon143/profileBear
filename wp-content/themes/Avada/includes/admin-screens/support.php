<?php
/**
 * Support Admin page.
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
<?php self::get_admin_screens_header( 'support' ); ?>
	<section class="avada-db-card avada-db-card-first avada-db-support-start">
		<h1 class="avada-db-support-heading"><?php esc_html_e( 'Avada Support', 'Avada' ); ?></h1>
		<p>
			<?php /* translators: %1$s: "Status" link. %2$s: "View more info here" link. */ ?>
			<?php printf( __( 'Avada comes with 6 months of free support for every license you purchase. Support can be <a %1$s>extended through subscription</a> via ThemeForest (<a %2$s>more information on support extension</a>). To access Avada support, you must first setup an account by <a %3$s>following these steps</a>.', 'Avada' ), 'href="http://bit.ly/2l3jd8A" target="_blank"', 'href="https://help.market.envato.com/hc/en-us/articles/207886473-Extending-and-Renewing-Item-Support" target="_blank"', 'href="https://theme-fusion.com/documentation/avada/getting-started/avada-theme-support/" target="_blank"' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</p>

		<div class="avada-db-card-notice">
			<i class="fusiona-info-circle"></i>
			<p class="avada-db-card-notice-heading">
				<a href="<?php echo esc_url( self::$theme_fusion_url ) . 'documentation/avada/getting-started/avada-theme-support/'; ?>" target="_blank"><?php esc_html_e( 'Create your support account.', 'Avada' ); ?></a>
			</p>
		</div>
	</section>

	<section class="avada-db-card avada-db-support-channels">
		<h2 class="avada-db-support-channels-heading"><?php esc_html_e( 'Channels Of Support', 'Avada' ); ?></h2>

		<div class="avada-db-card-grid">
			<div class="avada-db-card-notice">
				<div class="avada-db-card-notice-heading">
					<i class="fusiona-exclamation-sign"></i>
					<h3><?php esc_html_e( 'Quick Start Guide', 'Avada' ); ?></h3>
				</div>
				<p class="avada-db-card-notice-content">
					<?php esc_html_e( 'We understand that it can be a daunting process getting started with WordPress. In light of this, we have prepared a starter pack for you, which includes all you need to know.', 'Avada' ); ?>
				</p>
				<p class="avada-db-card-notice-content">
					<a href="<?php echo esc_url( self::$theme_fusion_url ) . 'documentation/avada/getting-started/avada-quick-start-guide/'; ?>" class="button button-primary" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Starter Guide', 'Avada' ); ?></a>
				</p>
			</div>

			<div class="avada-db-card-notice">
				<div class="avada-db-card-notice-heading">
					<i class="fusiona-documentation"></i>
					<h3><?php esc_html_e( 'Documentation', 'Avada' ); ?></h3>
				</div>
				<p class="avada-db-card-notice-content">
					<?php esc_html_e( 'This is the place to go to reference different aspects of the product. Our online documentaiton is an incredible resource for learning the ins and outs of using the Avada Website Builder.', 'Avada' ); ?>
				</p>
				<p class="avada-db-card-notice-content">
					<a href="<?php echo esc_url( self::$theme_fusion_url ) . 'documentation/avada/'; ?>" class="button button-primary" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Documentation', 'Avada' ); ?></a>
				</p>
			</div>

			<div class="avada-db-card-notice">
				<div class="avada-db-card-notice-heading">
					<i class="fusiona-author"></i>
					<h3><?php esc_html_e( 'Submit A Ticket', 'Avada' ); ?></h3>
				</div>
				<p class="avada-db-card-notice-content">
					<?php esc_html_e( 'We offer excellent support through our advanced ticket system. Make sure to register your purchase first to access our support services and other resources.', 'Avada' ); ?>
				</p>
				<p class="avada-db-card-notice-content">
					<a href="<?php echo esc_url_raw( self::$theme_fusion_url ) . 'support/submit-a-ticket/'; ?>" class="button button-primary" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Submit A Ticket', 'Avada' ); ?></a>
				</p>
			</div>
		</div>

		<div class="avada-db-card-grid">
			<div class="avada-db-card-notice">
				<div class="avada-db-card-notice-heading">
					<i class="fusiona-video"></i>
					<h3><?php esc_html_e( 'Video Tutorials', 'Avada' ); ?></h3>
				</div>
				<p class="avada-db-card-notice-content">
					<?php esc_html_e( 'Nothing is better than watching a video to learn. We have a growing library of narrated HD video tutorials to help teach you the different aspects of using the Avada Website Builder.', 'Avada' ); ?>
				</p>
				<p class="avada-db-card-notice-content">
					<a href="<?php echo esc_url( self::$theme_fusion_url ) . 'documentation/avada/videos/'; ?>" class="button button-primary" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Watch Videos', 'Avada' ); ?></a>
				</p>
			</div>

			<div class="avada-db-card-notice">
				<div class="avada-db-card-notice-heading">
					<i class="fusiona-users"></i>
					<h3><?php esc_html_e( 'Community Forum', 'Avada' ); ?></h3>
				</div>
				<p class="avada-db-card-notice-content">
					<?php esc_html_e( 'We also have a community forum for user to user interactions. Ask and help other Avada users! Please note that ThemeFusion does not provide product support here.', 'Avada' ); ?>
				</p>
				<p class="avada-db-card-notice-content">
					<a href="<?php echo esc_url( self::$theme_fusion_url ) . 'community/forum/'; ?>" class="button button-large button-primary" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Community Forum', 'Avada' ); ?></a>
				</p>
			</div>

			<div class="avada-db-card-notice">
				<div class="avada-db-card-notice-heading">
					<i class="dashicons dashicons-facebook"></i>
					<h3><?php esc_html_e( 'Facebook Group', 'Avada' ); ?></h3>
				</div>
				<p class="avada-db-card-notice-content">
					<?php esc_html_e( 'We have an amazing Facebook Group! Share with other Avada users and help grow our community. Please note, ThemeFusion does not provide support here.', 'Avada' ); ?>
				</p>
				<p class="avada-db-card-notice-content">
					<a href="https://www.facebook.com/groups/AvadaUsers/" class="button button-primary" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Facebook Group', 'Avada' ); ?></a>
				</p>
			</div>
		</div>
	</section>
	<?php do_action( 'avada_admin_pages_support_after_list' ); ?>

<?php $this->get_admin_screens_footer(); ?>
