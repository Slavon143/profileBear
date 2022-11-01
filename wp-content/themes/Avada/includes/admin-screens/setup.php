<?php
/**
 * Performance Admin page.
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

<?php $this->get_admin_screens_header( 'setup' ); ?>

	<?php
	if ( ! PyreThemeFrameworkMetaboxes::$instance ) {
		new PyreThemeFrameworkMetaboxes();
	}
	$metaboxes     = PyreThemeFrameworkMetaboxes::$instance;
	$typo_sets     = AWB_Setup_Wizard()->get_typo_sets();
	$settings      = awb_get_fusion_settings();
	$options       = apply_filters( 'fusion_settings_all_fields', [] );
	$the_user      = wp_get_current_user();
	$completed_reg = Avada()->registration->appear_registered() ? ' avada-db-completed avada-db-onload-completed' : '';
	?>
	<section class="awb-wizard-section<?php echo ( Avada()->registration->is_registered() && AWB_Prebuilt_Websites()->are_avada_plugins_active() ? ' hidden' : '' ); ?>" data-step="1">

		<div class="avada-db-card avada-db-welcome<?php echo ( Avada()->registration->is_registered() ? ' hidden' : '' ); ?>">
			<div class="avada-db-card-caption">
				<?php /* translators: %s: user name */ ?>
				<h1 class="avada-db-welcome-heading"><?php printf( apply_filters( 'avada_admin_welcome_title', __( 'Welcome To Avada, %s!', 'Avada' ) ), esc_html( ucfirst( $the_user->display_name ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h1>
				<p class="avada-db-welcome-text"><?php echo esc_html( apply_filters( 'avada_admin_setup_welcome_text', __( 'We recommend that you set up your website with the Avada Setup Wizard; the easiest way to get started.', 'Avada' ) ) ); ?></p>
			</div>
			<div class="avada-db-card-image">
				<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/admin/images/welcome.png' ); ?>" alt="<?php esc_html_e( 'Avada Welcome Image', 'Avada' ); ?>" width="613" height="360">
			</div>
		</div>
		<div id="avada-db-registration" class="avada-db-card avada-db-registration <?php echo esc_attr( $completed_reg ); ?><?php echo ( Avada()->registration->is_registered() ? ' hidden' : '' ); ?>">
			<?php Avada()->registration->the_form(); ?>
		</div>

		<div class="avada-db-card<?php echo ( ! Avada()->registration->is_registered() || ( Avada()->registration->is_registered() && AWB_Prebuilt_Websites()->are_avada_plugins_active() ) ? ' hidden' : '' ); ?>">
			<div class="awb-setup-wizard-hero">
				<div class="awb-setup-wizard-hero-text">
					<h2><?php esc_html_e( 'Activate Avada Plugins', 'Avada' ); ?></h2>
					<p>
						<?php esc_html_e( 'Avada plugins are not active, it is required to activate them before continuing. ', 'Avada' ); ?>
						<br>
						<?php esc_html_e( 'Page will reload after plugins are activated.', 'Avada' ); ?>
					</p>
					<p><button class="button button-primary" id="activate-plugins"><?php esc_html_e( 'Activate Plugins', 'Avada' ); ?></button></p>
					<p><span id="awb-activate-plugins-loader" class="avada-db-loader"></span></p>
				</div>
			</div>

		</div>
	</section>

	<section class="awb-setup-wizard-section<?php echo ( ! Avada()->registration->is_registered() || ! AWB_Prebuilt_Websites()->are_avada_plugins_active() ? ' hidden' : '' ); ?>" data-step="2" data-save="0">
		<div class="awb-setup-wizard-hero">
			<div class="awb-setup-wizard-hero-text">
				<h2><?php esc_html_e( 'Select A Setup Type', 'Avada' ); ?></h2>
				<p><?php esc_html_e( 'Choose what sort of website you want, and also set some basic details.', 'Avada' ); ?></p>
			</div>
			<div class="pyre_metabox_field">
				<div class="pyre_field avada-setup-select">
					<input type="hidden" id="setup_type" name="setup_type" value="prebuilt">
					<div class="active" data-value="prebuilt">
						<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/admin/images/prebuilt-website.png' ); ?>" alt="<?php esc_html_e( 'Avada Welcome Image', 'Avada' ); ?>" width="328" height="151">
						<h3><?php esc_html_e( 'Prebuilt Website', 'Avada' ); ?></h3>
						<p><?php esc_html_e( 'Fully designed and prebuilt websites which you import and then edit to your requirements.', 'Avada' ); ?></p>
					</div>
					<div data-value="scratch">
						<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/admin/images/Start-from-Scratch.png' ); ?>" alt="<?php esc_html_e( 'Avada Welcome Image', 'Avada' ); ?>" width="360" height="160">
						<h3><?php esc_html_e( 'New Website', 'Avada' ); ?></h3>
						<p><?php esc_html_e( 'Build a new website from scratch with the help of the Avada Setup Wizard.', 'Avada' ); ?></p>
					</div>
				</div>
			</div>

		</div>

		<div class="awb-setup-wizard-content">
			<div class="pyre_metabox_field">
				<div class="pyre_desc">
					<label for="site_title"><?php esc_html_e( 'Site Title', 'Avada' ); ?></label>
					<p><?php esc_html_e( 'Set your website title.', 'Avada' ); ?></p>
				</div>
				<div class="pyre_field">
					<input type="text" id="site_title" name="site_title" value="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
				</div>
			</div>

			<div class="pyre_metabox_field">
				<div class="pyre_desc">
					<label for="site_tagline"><?php esc_html_e( 'Site Tagline', 'Avada' ); ?></label>
					<p><?php esc_html_e( 'Set your website tagline.', 'Avada' ); ?></p>
				</div>
				<div class="pyre_field">
					<input type="text" id="site_tagline" name="site_tagline" value="<?php echo esc_attr( get_bloginfo( 'description' ) ); ?>">
				</div>
			</div>

			<div class="awb-wizard-actions">
				<div class="awb-wizard-action-links">
					<a class="awb-wizard-not-right-now" href="<?php echo esc_url( admin_url( 'admin.php?page=avada' ) ); ?>" aria-label="<?php esc_attr_e( 'Link to dashboard', 'Avada' ); ?>"><?php esc_html_e( 'Not right now', 'Avada' ); ?></a>
					<span>|</span>
					<a class="awb-wizard-not-right-now" href="<?php echo esc_url( admin_url( 'admin.php?page=avada&skip-wizard=true' ) ); ?>" aria-label="<?php esc_attr_e( 'Link to dashboard', 'Avada' ); ?>"><?php esc_html_e( 'Don\'t remind me again', 'Avada' ); ?></a>
				</div>
				<a href="#setup_type" class="button button-primary awb-wizard-link awb-wizard-next" data-id="setup_type"><span class="awb-text-wrapper"><span><?php esc_html_e( 'Next Step', 'Avada' ); ?></span><i class="fusiona-arrow-forward"></i></span></a>
			</div>
		</div>

	</section>

	<section class="awb-setup-wizard-section hidden" data-step="4" data-save="0">
		<div class="awb-setup-wizard-hero">
			<div class="awb-setup-wizard-hero-text">
				<h2><?php esc_html_e( 'Website Colors', 'Avada' ); ?></h2>
				<p><?php esc_html_e( 'Select your website color palette. You can filter palettes by color, finetune your selection, or create a custom palette.', 'Avada' ); ?></p>
			</div>

			<?php
			// Try to keep these colors ordered as a rainbow.
			// VERY IMPORTANT: An added/remove value to this will also meant a change in avada server. Order is not important.
			$colors = [
				// Red.
				'pink'   => esc_attr__( 'Pink', 'Avada' ),
				'red'    => esc_attr__( 'Red', 'Avada' ),
				'brown'  => esc_attr__( 'Brown', 'Avada' ),
				// Orange.
				'orange' => esc_attr__( 'Orange', 'Avada' ),
				'yellow' => esc_attr__( 'Yellow', 'Avada' ),
				// Green.
				'lime'   => esc_attr__( 'Lime', 'Avada' ),
				'green'  => esc_attr__( 'Green', 'Avada' ),
				'olive'  => esc_attr__( 'Olive', 'Avada' ),
				// Blue & Indigo.
				'aqua'   => esc_attr__( 'Aqua', 'Avada' ),
				'cyan'   => esc_attr__( 'Cyan', 'Avada' ),
				'blue'   => esc_attr__( 'Blue', 'Avada' ),
				'navy'   => esc_attr__( 'Navy', 'Avada' ),
				'violet' => esc_attr__( 'Violet', 'Avada' ),
				'purple' => esc_attr__( 'Purple', 'Avada' ),
				'indigo' => esc_attr__( 'Indigo', 'Avada' ),
			];
			?>

			<div class="awb-setup-wizard-color-buttons">
				<ul id="awb-setup-wizard-color-categories-wrapper" class="color-categories" data-awb-color-names="<?php echo esc_attr( wp_json_encode( $colors ) ); ?>"></ul>
				<button class="awb-setup-wizard-custom-colors-btn awb-button button" type="button"><span class="awb-text-wrapper"><i class="fusiona-customize"></i><span><?php esc_html_e( 'Custom Palette', 'Avada' ); ?></span></span></button>
			</div>
		</div>

		<div class="awb-setup-wizard-content">
			<div id="color-selection">
				<ul id="awb-setup-wizard-color-schemes-wrapper" class="schemes"></ul>
			</div>

			<div class="color-confirm">
				<div class="awb-choices pyre_field avada-buttonset radio">
					<a href="#selection" class="awb-button button"><span class="awb-text-wrapper"><i class="fusiona-arrow-forward"></i><span><?php esc_html_e( 'Back', 'Avada' ); ?></span></span></a>
					<div class="fusion-form-radio-button-set ui-buttonset">
						<input type="hidden" id="dark_light" name="dark_light" value="light" class="button-set-value">
						<a href="#" class="ui-button buttonset-item has-tooltip awb-tooltip-top ui-state-active" data-value="light" aria-label="<?php esc_attr_e( 'Light', 'Avada' ); ?>"><i class="fusiona-dont-invert"></i></a>
						<a href="#" class="ui-button buttonset-item has-tooltip awb-tooltip-top" data-value="dark" aria-label="<?php esc_attr_e( 'Dark', 'Avada' ); ?>"><i class="fusiona-do-invert"></i></a>
					</div>
					<div class="scheme-nav">
						<a href="#previous" class="awb-button button button-icon-only" aria-label="<?php esc_attr_e( 'Previous', 'Avada' ); ?>"><i class="fusiona-arrow-left"></i></a>
						<a href="#next" class="awb-button button button-icon-only" aria-label="<?php esc_attr_e( 'Next', 'Avada' ); ?>"><i class="fusiona-arrow-right"></i></a>
					</div>
				</div>

				<div class="awb-setup-wizard-style-preview color-preview typo-card awb-setup-wizard-allow-invert">
					<div class="awb-setup-wizard-content-preview-wrapper">
						<i class="heading-badge fusiona-submission"></i>
						<h2><?php esc_html_e( 'Heading Title', 'Avada' ); ?></h2>
						<div class="subheading"><?php esc_html_e( 'Subheading Text', 'Avada' ); ?></div>
						<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. In facilisis leo vel lacus semper, quis tincidunt erat egestas.</p>
						<a class="button"><?php esc_html_e( 'Sample Button', 'Avada' ); ?></a>
					</div>
					<div class="awb-setup-wizard-svg-preview-wrapper">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 322 339">
						<path d="M180.105 123.009L182.037 130.77C182.309 131.888 183.728 132.28 184.544 131.435L190.099 125.667C191.73 123.976 194.508 125.606 193.874 127.841L191.669 135.542C191.337 136.659 192.394 137.686 193.511 137.384L201.211 135.179C203.445 134.545 205.045 137.323 203.385 138.954L197.618 144.511C196.772 145.326 197.165 146.715 198.282 147.017L206.042 148.95C208.306 149.524 208.306 152.725 206.042 153.298L198.282 155.231C197.165 155.503 196.772 156.922 197.618 157.738L203.385 163.294C205.076 164.925 203.445 167.703 201.211 167.069L193.511 164.865C192.394 164.532 191.367 165.589 191.669 166.707L193.874 174.407C194.508 176.642 191.73 178.243 190.099 176.582L184.544 170.814C183.728 169.968 182.339 170.361 182.037 171.478L180.105 179.239C179.531 181.504 176.331 181.504 175.757 179.239L173.825 171.478C173.553 170.361 172.134 169.968 171.319 170.814L165.763 176.582C164.132 178.273 161.355 176.642 161.989 174.407L164.193 166.707C164.525 165.589 163.468 164.563 162.351 164.865L154.621 167.099C152.387 167.733 150.787 164.955 152.447 163.324L158.214 157.768C159.06 156.952 158.667 155.563 157.55 155.261L149.79 153.329C147.526 152.755 147.526 149.554 149.79 148.98L157.55 147.047C158.667 146.776 159.06 145.356 158.214 144.541L152.447 138.954C150.756 137.323 152.387 134.545 154.621 135.179L162.321 137.384C163.438 137.716 164.465 136.659 164.163 135.542L161.958 127.841C161.324 125.606 164.102 124.006 165.733 125.667L171.288 131.435C172.104 132.28 173.493 131.888 173.795 130.77L175.727 123.009C176.301 120.744 179.531 120.744 180.105 123.009Z"/>
						<path d="M283.058 232.586L276.855 234.095C283.37 232.509 287.041 225.931 285.069 219.374L284.911 218.85C282.939 212.294 276.081 208.278 269.565 209.864L243.138 216.296C243.955 209.343 244.133 199.336 241.888 185.923C238.954 168.174 226.377 163.185 221.897 167.259C218.124 170.711 222.026 173.082 221.387 184.158C220.817 193.979 213.019 214.734 191.803 231.606L191.751 231.619L188.886 307.633L223.069 313.779C241.112 317.042 259.314 316.552 276.619 312.34L294.706 307.937C301.065 306.389 304.636 299.666 302.617 292.952C300.597 286.239 293.796 282.04 287.384 283.601L291.502 282.598C298.018 281.012 301.689 274.434 299.716 267.877L299.558 267.353C297.586 260.797 290.728 256.781 284.212 258.367L290.415 256.857C296.931 255.271 300.602 248.692 298.629 242.136L298.471 241.611C296.431 235.016 289.573 231 283.058 232.586Z"/>
						<path d="M187.592 226.194L148.754 235.872L176.977 331.91L215.815 322.232L187.592 226.194Z"/>
						<path d="M209.378 3.96027C158.45 -11.1432 105.148 18.2776 90.3437 69.6798L149.477 87.2257C154.788 68.7815 173.906 58.2259 192.18 63.6441C210.454 69.0622 220.958 88.4047 215.647 106.849L274.781 124.395C289.585 72.9644 260.306 19.0637 209.378 3.96027Z"/>
						<path fill-rule="evenodd" clip-rule="evenodd" d="M89.8681 119.429C85.7972 112.783 76.8174 111.055 70.5321 115.702C66.3571 118.783 60.7342 119.173 56.1677 116.699C49.2918 112.965 40.6661 115.914 37.5425 123.059C35.4835 127.805 30.8175 130.92 25.6193 131.036C17.7853 131.212 11.779 138.068 12.6933 145.79C13.3012 150.914 10.8419 155.932 6.39224 158.616C-0.305996 162.655 -2.04682 171.566 2.63671 177.802C5.74154 181.945 6.13474 187.524 3.64116 192.055C-0.122054 198.878 2.84986 207.437 10.074 210.51C14.8568 212.553 17.996 217.183 18.1127 222.341C18.2921 230.138 25.1753 236.075 32.9824 235.167C38.1462 234.563 43.2037 237.004 45.9083 241.419C49.9792 248.065 58.9591 249.792 65.2443 245.145C69.4193 242.064 75.0177 241.676 79.5841 244.15C86.4601 247.884 95.1103 244.934 98.2093 237.79C100.268 233.044 104.934 229.929 110.133 229.813C117.991 229.635 123.975 222.806 123.059 215.059C122.451 209.935 124.91 204.917 129.36 202.233C136.058 198.194 137.799 189.284 133.115 183.047C130.01 178.905 129.619 173.35 132.112 168.818C135.876 161.996 132.902 153.413 125.702 150.338C120.92 148.295 117.78 143.665 117.664 138.507C117.484 130.709 110.601 124.772 102.794 125.681C97.6302 126.284 92.5727 123.844 89.8681 119.429ZM81.0161 164.886C81.8694 164.721 82.7545 164.883 83.477 165.337C84.1845 165.773 84.6847 166.452 84.8732 167.232C85.0616 168.013 84.9236 168.834 84.488 169.524L67.3415 196.635L67.3118 196.681C67.0422 197.107 66.6866 197.48 66.2656 197.778C65.8445 198.075 65.3661 198.292 64.8578 198.415C64.3496 198.538 63.8214 198.565 63.3034 198.494C62.7855 198.423 62.288 198.257 61.8394 198.004C61.4558 197.786 61.1133 197.51 60.8255 197.186L47.2397 181.776C46.7118 181.16 46.4553 180.374 46.523 179.58C46.5908 178.786 46.9776 178.046 47.6038 177.511C48.2299 176.976 49.0475 176.687 49.8878 176.705C50.7281 176.723 51.5269 177.046 52.1193 177.607L60.2668 185.524C61.2745 186.146 62.4273 186.674 63.5756 186.492C64.228 186.362 64.8239 186.045 65.2826 185.585L78.927 166.215C79.4115 165.529 80.1629 165.05 81.0161 164.886Z"/>
						<path d="M35.6165 75.1151L37.9637 76.3978C40.916 78.0142 43.3258 80.4231 44.9323 83.3639L46.2154 85.7102C46.4345 86.1065 46.8518 86.3568 47.3108 86.3568C47.7698 86.3568 48.1871 86.1065 48.4061 85.7102L49.6893 83.3639C51.3062 80.4127 53.7056 78.0142 56.6578 76.3978L59.005 75.1151C59.4015 74.8961 59.6518 74.479 59.6518 74.0201C59.6518 73.5613 59.4015 73.1442 59.005 72.9252L56.6578 71.6425C53.716 70.0261 51.3062 67.6172 49.6893 64.6764L48.4061 62.33C48.1871 61.9338 47.7698 61.6835 47.3108 61.6835C46.8518 61.6835 46.4345 61.9338 46.2154 62.33L44.9323 64.6764C43.3153 67.6276 40.9055 70.0365 37.9637 71.6425L35.6165 72.9252C35.2201 73.1442 34.9697 73.5613 34.9697 74.0201C34.9697 74.479 35.2097 74.8961 35.6165 75.1151Z"/>
						<path d="M306.159 150.862C304.915 150.796 303.672 150.769 302.428 150.72L298.636 150.637C297.743 150.619 296.844 150.607 295.94 150.601C295.081 150.594 294.26 150.267 293.654 149.691C293.048 149.115 292.706 148.336 292.702 147.523C292.702 145.503 292.681 143.482 292.649 141.462C292.599 137.899 292.538 134.338 292.412 130.781C292.405 130.605 292.36 130.432 292.281 130.274C292.201 130.115 292.088 129.973 291.949 129.857C291.809 129.741 291.646 129.653 291.469 129.599C291.293 129.544 291.106 129.524 290.921 129.54C290.583 129.581 290.271 129.737 290.044 129.978C289.817 130.219 289.689 130.53 289.685 130.853C289.561 134.391 289.494 137.93 289.452 141.467C289.419 143.488 289.408 145.508 289.398 147.529C289.395 148.342 289.053 149.121 288.446 149.697C287.84 150.273 287.018 150.6 286.159 150.606C285.266 150.615 284.367 150.626 283.463 150.641L279.671 150.724C278.406 150.773 277.143 150.802 275.879 150.871C275.697 150.88 275.518 150.925 275.354 151.001C275.191 151.077 275.045 151.183 274.925 151.314C274.806 151.444 274.715 151.596 274.659 151.761C274.602 151.925 274.581 152.098 274.597 152.27C274.634 152.586 274.79 152.878 275.036 153.095C275.281 153.311 275.601 153.437 275.936 153.45C277.18 153.516 278.423 153.545 279.667 153.593L283.459 153.677C284.352 153.694 285.25 153.705 286.153 153.711C287.012 153.718 287.834 154.045 288.439 154.621C289.045 155.197 289.387 155.976 289.391 156.789C289.399 158.808 289.415 160.827 289.438 162.845L289.526 168.187C289.58 169.969 289.611 171.75 289.683 173.53C289.691 173.704 289.736 173.876 289.816 174.034C289.895 174.192 290.008 174.333 290.147 174.448C290.286 174.564 290.448 174.651 290.624 174.706C290.8 174.76 290.986 174.78 291.17 174.764C291.507 174.724 291.818 174.57 292.046 174.331C292.273 174.092 292.402 173.783 292.408 173.461C292.479 171.703 292.509 169.945 292.561 168.187L292.649 162.845C292.673 160.825 292.689 158.806 292.697 156.787C292.7 155.974 293.042 155.196 293.648 154.62C294.254 154.044 295.075 153.716 295.934 153.709C296.827 153.701 297.725 153.689 298.628 153.673L302.42 153.59C303.685 153.541 304.948 153.512 306.212 153.445C306.395 153.435 306.573 153.391 306.737 153.315C306.901 153.239 307.048 153.133 307.167 153.002C307.287 152.872 307.378 152.72 307.434 152.556C307.491 152.391 307.512 152.218 307.496 152.046C307.46 151.73 307.304 151.437 307.059 151.219C306.814 151.002 306.494 150.875 306.159 150.862Z"/>
						<path d="M105.061 284.876C105.175 285.281 105.424 285.638 105.768 285.892C106.113 286.147 106.534 286.285 106.967 286.285C107.4 286.285 107.821 286.147 108.165 285.892C108.51 285.638 108.758 285.281 108.873 284.876C109.04 284.232 109.148 283.575 109.196 282.913C109.243 282.258 109.265 281.605 109.245 280.95C109.226 280.295 109.204 279.642 109.149 278.987C109.096 278.328 109.004 277.673 108.873 277.024C108.782 276.599 108.543 276.217 108.196 275.943C107.849 275.669 107.415 275.519 106.967 275.519C106.519 275.519 106.085 275.669 105.737 275.943C105.39 276.217 105.151 276.599 105.061 277.024C104.929 277.673 104.837 278.328 104.785 278.987C104.73 279.642 104.694 280.295 104.688 280.95C104.683 281.605 104.688 282.258 104.738 282.913C104.786 283.575 104.894 284.232 105.061 284.876Z"/>
						<path d="M105.06 305.973C105.173 306.379 105.421 306.738 105.765 306.994C106.11 307.25 106.532 307.389 106.966 307.389C107.4 307.389 107.822 307.25 108.167 306.994C108.511 306.738 108.759 306.379 108.872 305.973C109.037 305.329 109.145 304.672 109.195 304.01C109.239 303.355 109.266 302.702 109.247 302.047C109.228 301.392 109.208 300.739 109.15 300.084C109.099 299.425 109.007 298.769 108.874 298.121C108.784 297.696 108.545 297.314 108.198 297.04C107.851 296.766 107.417 296.616 106.969 296.616C106.521 296.616 106.087 296.766 105.739 297.04C105.392 297.314 105.153 297.696 105.063 298.121C104.931 298.769 104.838 299.425 104.787 300.084C104.732 300.739 104.696 301.392 104.69 302.047C104.685 302.702 104.69 303.355 104.743 304.01C104.79 304.672 104.896 305.329 105.06 305.973Z"/>
						<path d="M113.796 293.343C115.131 293.596 116.488 293.719 117.848 293.71C119.209 293.739 120.569 293.616 121.9 293.343C122.33 293.246 122.714 293.011 122.989 292.676C123.263 292.34 123.413 291.925 123.413 291.497C123.413 291.069 123.263 290.653 122.989 290.318C122.714 289.983 122.33 289.747 121.9 289.65C120.569 289.378 119.209 289.255 117.848 289.284C116.488 289.274 115.131 289.397 113.796 289.65C113.36 289.74 112.969 289.973 112.688 290.309C112.407 290.645 112.254 291.064 112.254 291.497C112.254 291.929 112.407 292.349 112.688 292.685C112.969 293.021 113.36 293.253 113.796 293.343Z"/>
						<path d="M92.0362 293.344C93.3697 293.604 94.7278 293.728 96.0881 293.713C97.4485 293.73 98.8068 293.607 100.14 293.344C100.574 293.251 100.963 293.018 101.242 292.682C101.52 292.346 101.672 291.928 101.672 291.497C101.672 291.066 101.52 290.648 101.242 290.312C100.963 289.976 100.574 289.743 100.14 289.65C98.8068 289.387 97.4485 289.264 96.0881 289.281C94.7278 289.267 93.3696 289.392 92.0362 289.653C91.6018 289.745 91.2132 289.979 90.9345 290.315C90.6558 290.651 90.5039 291.069 90.5039 291.5C90.5039 291.931 90.6558 292.349 90.9345 292.684C91.2132 293.02 91.6018 293.254 92.0362 293.346V293.344Z"/>
					</svg>
					</div>
				</div>

				<div class="awb-setup-wizard-color-preview-bottom">
					<div class="awb-setup-wizard-scheme-mini-preview">
						<div style="background-color:var(--awb-color1);"></div><div style="background-color:var(--awb-color2);"></div><div style="background-color:var(--awb-color3);"></div><div style="background-color:var(--awb-color4);"></div><div style="background-color:var(--awb-color5);"></div><div style="background-color:var(--awb-color6);"></div><div style="background-color:var(--awb-color7);"></div><div style="background-color:var(--awb-color8);"></div>
					</div>

					<button class="awb-setup-wizard-toggle-custom-palette awb-button button"><span class="awb-text-wrapper"><i class="fusiona-customize"></i><span><?php esc_html_e( 'Customize', 'Avada' ); ?></span></span></button>
				</div>
			</div>

			<div class="awb-setup-wizard-custom-colors-wrapper color-preview">
				<div class="awb-setup-wizard-custom-colors">
					<div class="awb-setup-wizard-custom-colors-list">
						<?php for ( $i = 1; $i <= 8; $i++ ) : ?>
							<div class="awb-setup-wizard-change-color-toggler" data-color-id="<?php echo esc_attr( (string) $i ); ?>">
								<div class="awb-setup-wizard-change-color-preview" style="background-color:var(--awb-color<?php echo esc_attr( (string) $i ); ?>)"></div>
								<?php /* translators: %s: number id of a color */ ?>
								<div class="awb-setup-wizard-color-name"><?php echo esc_attr( sprintf( __( 'Color %s', 'Avada' ), $i ) ); ?></div>
							</div>
						<?php endfor; ?>
					</div>

					<div class="awb-setup-wizard-colors-change-list">
						<?php for ( $i = 1; $i <= 8; $i++ ) : ?>
							<div class="awb-setup-wizard-change-color-setting" data-color-id="<?php echo esc_attr( (string) $i ); ?>">
								<div class="awb-setup-wizard-change-color-title"><?php esc_html_e( 'Color Name:', 'Avada' ); ?></div>
								<?php /* translators: %s: number id of a color */ ?>
								<input class="awb-setup-wizard-change-color-name" value="<?php echo esc_attr( sprintf( __( 'Color %s', 'Avada' ), $i ) ); ?>" type="text"/>

								<div class="awb-setup-wizard-change-color-title"><?php esc_html_e( 'Color Code:', 'Avada' ); ?></div>
								<input id="awb-setup-wizard-change-color-<?php echo esc_attr( (string) $i ); ?>" class="color-picker awb-picker" type="text"/>
							</div>
						<?php endfor; ?>
					</div>
				</div>

				<div class="awb-setup-wizard-accessibility">
					<h3 class="awb-setup-wizard-accessibility-title"><?php esc_html_e( 'Accessibility Recommendations', 'Avada' ); ?></h3>
					<?php
					$excellent_text  = __( 'Excellent', 'Avada' );
					$acceptable_text = __( 'Acceptable', 'Avada' );
					$poor_text       = __( 'Poor', 'Avada' );
					$very_poor_text  = __( 'Very Poor', 'Avada' );
					$bad_text        = __( 'Bad', 'Avada' );

					$kses_allowed = [ 'span' => [ 'data-color-id' => [] ] ];
					$color1_html  = '<span data-color-id="1">' . esc_html__( 'Color 1', 'Avada' ) . '</span>';
					$color4_html  = '<span data-color-id="4">' . esc_html__( 'Color 4', 'Avada' ) . '</span>';
					$color5_html  = '<span data-color-id="5">' . esc_html__( 'Color 5', 'Avada' ) . '</span>';
					$color8_html  = '<span data-color-id="8">' . esc_html__( 'Color 8', 'Avada' ) . '</span>';

					/* translators: %1$s: First color name, %2$s: second color name */
					$colors_contrast_title = __( '%1$s & %2$s - Contrast', 'Avada' );
					$color_luminance_title = __( 'Color Luminance Order', 'Avada' );

					/* translators: %1$s: First color name, %2$s: second color name */
					$colors_contrast_very_poor = __( 'The "%1$s" lacks contrast with "%2$s".', 'Avada' );
					/* translators: %1$s: First color name, %2$s: second color name */
					$colors_contrast_poor = __( 'The "%1$s" contrast with "%2$s" could be improved.', 'Avada' );
					/* translators: %s: Color name */
					$color_suggest_darker = __( 'Suggest to make "%s" darker.', 'Avada' );
					/* translators: %s: Color name */
					$color_suggest_lighter = __( 'Suggest to make "%s" lighter.', 'Avada' );

					/* translators: %s: Color name */
					$color_unexpected_darker = __( 'The "%s" is darker than expect. Suggest to make it lighter.', 'Avada' );
					/* translators: %s: Color name */
					$color_unexpected_lighter = __( 'The "%s" is lighter than expected. Suggest to make it darker.', 'Avada' );
					/* translators: %s: Color name */
					$colors_not_in_right_order = __( 'The colors are not in the order of their luminance. The order breaks between "%1$s" and "%2$s". Suggest to modify the colors or swap them.', 'Avada' );
					?>

					<!-- Color 1-5 contrast -->
					<div class="awb-setup-wizard-accessibility-item" data-awb-check="color-1-5-contrast">
						<div class="awb-setup-wizard-accessibility-item-heading">
							<div class="awb-setup-wizard-accessibility-item-preview">
								<span class="awb-setup-wizard-accessibility-color-preview" style="background-color:var(--awb-color1);"></span>
								<span class="awb-setup-wizard-accessibility-color-preview" style="background-color:var(--awb-color5);"></span>
							</div>

							<div class="awb-setup-wizard-accessibility-item-title">
								<span>
									<?php echo wp_kses( sprintf( $colors_contrast_title, $color1_html, $color5_html ), $kses_allowed ); ?>
									<span class="awb-setup-wizard-accessibility-contrast"></span>
								</span>
							</div>

							<div class="awb-setup-wizard-accessibility-item-badge">
								<span data-awb-badge="very-poor"><?php echo esc_html( $very_poor_text ); ?></span>
								<span data-awb-badge="poor"><?php echo esc_html( $poor_text ); ?></span>
								<span data-awb-badge="acceptable"><?php echo esc_html( $acceptable_text ); ?></span>
								<span data-awb-badge="excellent"><?php echo esc_html( $excellent_text ); ?></span>
							</div>
						</div>

						<div class="awb-setup-wizard-accessibility-item-content awb-setup-wizard-accessibility-very-poor">
							<?php echo wp_kses( sprintf( $colors_contrast_very_poor, $color1_html, $color5_html ) . ' ' . sprintf( $color_suggest_darker, $color5_html ), $kses_allowed ); ?>
						</div>

						<div class="awb-setup-wizard-accessibility-item-content awb-setup-wizard-accessibility-poor">
							<?php echo wp_kses( sprintf( $colors_contrast_poor, $color1_html, $color5_html ) . ' ' . sprintf( $color_suggest_darker, $color5_html ), $kses_allowed ); ?>
						</div>
					</div>

					<!-- Color 4-8 contrast -->
					<div class="awb-setup-wizard-accessibility-item" data-awb-check="color-4-8-contrast">
						<div class="awb-setup-wizard-accessibility-item-heading">
							<div class="awb-setup-wizard-accessibility-item-preview">
								<span class="awb-setup-wizard-accessibility-color-preview" style="background-color:var(--awb-color4);"></span>
								<span class="awb-setup-wizard-accessibility-color-preview" style="background-color:var(--awb-color8);"></span>
							</div>

							<div class="awb-setup-wizard-accessibility-item-title">
								<span>
									<?php echo wp_kses( sprintf( $colors_contrast_title, $color4_html, $color8_html ), $kses_allowed ); ?>
									<span class="awb-setup-wizard-accessibility-contrast"></span>
								</span>
							</div>

							<div class="awb-setup-wizard-accessibility-item-badge">
								<span data-awb-badge="very-poor"><?php echo esc_html( $very_poor_text ); ?></span>
								<span data-awb-badge="poor"><?php echo esc_html( $poor_text ); ?></span>
								<span data-awb-badge="acceptable"><?php echo esc_html( $acceptable_text ); ?></span>
								<span data-awb-badge="excellent"><?php echo esc_html( $excellent_text ); ?></span>
							</div>
						</div>

						<div class="awb-setup-wizard-accessibility-item-content awb-setup-wizard-accessibility-very-poor">
							<?php echo wp_kses( sprintf( $colors_contrast_very_poor, $color4_html, $color8_html ) . ' ' . sprintf( $color_suggest_lighter, $color4_html ), $kses_allowed ); ?>
						</div>

						<div class="awb-setup-wizard-accessibility-item-content awb-setup-wizard-accessibility-poor">
							<?php echo wp_kses( sprintf( $colors_contrast_poor, $color4_html, $color8_html ) . ' ' . sprintf( $color_suggest_lighter, $color4_html ), $kses_allowed ); ?>
						</div>
					</div>

					<!-- Color luminance order -->
					<div class="awb-setup-wizard-accessibility-item" data-awb-check="color-luminance-order">
						<div class="awb-setup-wizard-accessibility-item-heading">
							<div class="awb-setup-wizard-accessibility-item-preview">
								<span class="awb-setup-wizard-accessibility-color-preview" style="background-color:var(--awb-color1);"></span>
								<span class="awb-setup-wizard-accessibility-color-preview" style="background-color:var(--awb-color8);"></span>
							</div>

							<div class="awb-setup-wizard-accessibility-item-title">
								<span>
									<?php echo esc_html( $color_luminance_title ); ?>
								</span>
							</div>

							<div class="awb-setup-wizard-accessibility-item-badge">
								<span data-awb-badge="very-poor"><?php echo esc_html( $bad_text ); ?></span>
								<span data-awb-badge="excellent"><?php echo esc_html( $excellent_text ); ?></span>
							</div>
						</div>

						<div class="awb-setup-wizard-accessibility-item-content awb-setup-wizard-accessibility-very-poor">
							<?php echo wp_kses( sprintf( $colors_not_in_right_order, '<span data-color-id></span>', '<span data-color-id></span>' ), $kses_allowed ); ?>
						</div>
					</div>

				</div>
			</div>

			<div class="awb-wizard-actions">
				<a href="#5" class="button button-primary awb-wizard-link awb-wizard-next" data-id="5"><span class="awb-text-wrapper"><span><?php esc_html_e( 'Next Step', 'Avada' ); ?></span><i class="fusiona-arrow-forward"></i></span></a>
			</div>
		</div>

	</section>

	<section class="awb-setup-wizard-section hidden" data-step="5" data-save="0">
		<div class="awb-setup-wizard-hero">
			<div class="awb-setup-wizard-hero-text">
				<h2><?php esc_html_e( 'Website Typography', 'Avada' ); ?></h2>
				<p><?php esc_html_e( 'Select a typography scheme for your website, and finetune it.', 'Avada' ); ?></p>
			</div>
		</div>

		<div class="awb-setup-wizard-content awb-setup-wizard-content-secondary">
			<div id="typo-selection">
				<?php
				$typo_translations = [
					'heading'     => __( 'Heading', 'Avada' ),
					'subheading'  => __( 'Subheading', 'Avada' ),
					'text1'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. In facilisis leo vel lacus semper, quis tincidunt erat egestas.',
					'text2'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
					'button_text' => __( 'Sample Button', 'Avada' ),
				];
				?>
				<ul class="awb-setup-wizard-typography-list awb-setup-wizard-allow-invert" data-item-translations="<?php echo esc_attr( (string) wp_json_encode( $typo_translations, JSON_HEX_APOS | JSON_HEX_QUOT ) ); ?>">
				</ul>

				<ul class="typo-categories">
				</ul>
			</div>

			<div class="awb-setup-wizard-typography-preview-wrapper">
				<div class="typography-options">

					<div class="typography-navigation-wrapper">
						<a href="#selection" class="awb-button button"><span class="awb-text-wrapper"><i class="fusiona-arrow-forward"></i><span><?php esc_html_e( 'Back', 'Avada' ); ?></span></span></a>

						<div class="typo-nav">
							<a href="#previous" class="awb-button button" aria-label="<?php esc_attr_e( 'Previous', 'Avada' ); ?>"><i class="fusiona-arrow-left"></i></a>
							<a href="#next" class="awb-button button" aria-label="<?php esc_attr_e( 'Next', 'Avada' ); ?>"><i class="fusiona-arrow-right"></i></a>
						</div>
					</div>

					<div class="typography-settings-wrapper">
						<?php
						echo $metaboxes->range( // phpcs:ignore WordPress.Security.EscapeOutput
							'base_size',
							esc_html__( 'Base Font Size', 'Avada' ),
							esc_html__( 'Set the base font size. This is what the body text will use.', 'Avada' ),
							'12',
							24,
							0.1,
							16,
							16
						);

						echo $metaboxes->select( // phpcs:ignore WordPress.Security.EscapeOutput
							'sizing_type',
							'Sizing Ratio',
							[
								'1.067' => esc_html__( '1.067 - Minor Second' ),
								'1.125' => esc_html__( '1.125 - Major Second' ),
								'1.200' => esc_html__( '1.200 - Minor Third' ),
								'1.250' => esc_html__( '1.250 - Major Third' ),
								'1.333' => esc_html__( '1.333 - Perfect Fourth' ),
								'1.414' => esc_html__( '1.414 - Augmented Fourth' ),
								'1.500' => esc_html__( '1.500 - Perfect Fifth' ),
								'1.618' => esc_html__( '1.618 - Golden Ratio' ),
							],
							esc_html__( 'Select the sizing ratio to use for headings.', 'Avada' ),
							'1.333',
							[]
						);
						?>
					</div>
				</div>
				<div class="awb-setup-wizard-style-preview awb-setup-wizard-style-preview-typography typography awb-setup-wizard-allow-invert">
					<div class="awb-typo-left-side">
						<div class="awb-typo-desc"><?php esc_html_e( 'Heading H1', 'Avada' ); ?></div>
						<h1><?php esc_html_e( 'A quick brown fox jumps over the lazy dog.', 'Avada' ); ?></h1>

						<div class="awb-typo-desc"><?php esc_html_e( 'Heading H2', 'Avada' ); ?></div>
						<h2><?php esc_html_e( 'A quick brown fox jumps over the lazy dog.', 'Avada' ); ?></h2>

						<div class="awb-typo-desc"><?php esc_html_e( 'Heading H3', 'Avada' ); ?></div>
						<h3><?php esc_html_e( 'A quick brown fox jumps over the lazy dog.', 'Avada' ); ?></h3>

						<div class="awb-typo-desc"><?php esc_html_e( 'Heading H4', 'Avada' ); ?></div>
						<h4><?php esc_html_e( 'A quick brown fox jumps over the lazy dog.', 'Avada' ); ?></h4>

						<div class="awb-typo-desc"><?php esc_html_e( 'Heading H5', 'Avada' ); ?></div>
						<h5><?php esc_html_e( 'A quick brown fox jumps over the lazy dog.', 'Avada' ); ?></h5>

						<div class="awb-typo-desc"><?php esc_html_e( 'Heading H6', 'Avada' ); ?></div>
						<h6><?php esc_html_e( 'A quick brown fox jumps over the lazy dog.', 'Avada' ); ?></h6>
					</div>

					<div class="awb-typo-right-side">
						<div class="awb-typo-desc"><?php esc_html_e( 'Heading H1 with Paragraph', 'Avada' ); ?></div>
						<h1><?php esc_html_e( 'A quick brown fox jumps over the lazy dog.', 'Avada' ); ?></h1>

						<p>
							Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum rhoncus nec metus ut lobortis. Praesent rutrum accumsan massa, vitae semper metus malesuada ac. Duis ac lorem non odio rhoncus faucibus.
						</p>

						<p>
							Maecenas ac arcu sit amet dolor imperdiet egestas at eu elit. Sed finibus dui sed nibh venenatis, quis blandit nisl aliquam. Aliquam non lorem rutrum, bibendum diam et, tincidunt tellus.
						</p>

						<p>
							Suspendisse ultricies elementum dui, ac tempus sapien dapibus sit amet. Aenean ac enim mollis, laoreet ex pretium, rhoncus elit. Phasellus condimentum facilisis malesuada. Fusce nibh diam, facilisis in feugiat imperdiet, mollis sit amet nulla.
						</p>

						<hr>
						<span>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</span>
						<a class="button"><?php esc_html_e( 'Sample Button', 'Avada' ); ?></a>
					</div>

				</div>
			</div>

			<div class="awb-wizard-actions">
				<a href="#6" class="button button-primary awb-wizard-link awb-wizard-next" data-id="6"><span class="awb-text-wrapper"><span><?php esc_html_e( 'Next Step', 'Avada' ); ?></span><i class="fusiona-arrow-forward"></i></span></a>
			</div>
		</div>

	</section>

	<section class="awb-setup-wizard-section layouts-section hidden" data-step="6" data-save="0">
		<div class="awb-setup-wizard-hero">
			<div class="awb-setup-wizard-hero-text">
				<h2><?php esc_html_e( 'Website Layouts', 'Avada' ); ?></h2>
				<p><?php esc_html_e( 'Select header and footer layouts for your website. You can also add a logo at this stage if you have one ready.', 'Avada' ); ?></p>
			</div>
		</div>
		<?php
			$studio_url         = class_exists( 'AWB_Studio_Import' ) ? AWB_Studio_Import()->studio_url : 'https://avada.studio/';
			$default_header_url = $studio_url . 'fusion_tb_section/new-starter-header/?template-only=1';
			$default_footer_url = $studio_url . 'fusion_tb_section/starter-footer/?template-only=1';
		?>
		<div class="awb-setup-wizard-content">
			<div class="awb-wizard-option-group" data-layout="header">
				<div class="pyre_metabox_field layout-input">
					<div class="pyre_desc">
						<label for="header_layout"><?php esc_html_e( 'Header Layout', 'Avada' ); ?></label>
						<p><?php esc_html_e( 'Set your website global header layout.', 'Avada' ); ?></p>
					</div>
					<div class="pyre_field">
						<a href="#" class="button set-layout-to-default" data-context="header" style="display:none;"><?php esc_html_e( 'Default', 'Avada' ); ?></a>
						<a href="#" class="button button-primary awb-studio-link" data-context="header"><?php esc_html_e( 'Select Header', 'Avada' ); ?></a>
						<input type="hidden" name="header" value="" class="layout-input-id" />
						<input type="hidden" name="header-options" value="" class="layout-input-options" />
					</div>
				</div>
				<div class="awb-iframe-preview header-preview">
						<iframe class="lazyload" data-orig-src="<?php echo esc_attr( $default_header_url ); ?>" data-default="<?php echo esc_attr( $default_header_url ); ?>" frameborder="0" width="1400"></iframe>
						<?php AWB_Setup_Wizard::studio_import_options_template(); ?>
				</div>
				<?php
					echo $metaboxes->upload( // phpcs:ignore WordPress.Security.EscapeOutput
						'awb_logo',
						esc_html__( 'Logo', 'Avada' ),
						esc_html__( 'Select an image to be used as the logo. If no image is selected a text logo will be used.', 'Avada' ),
						[],
						false,
						'plus'
					);
					?>
			</div>

			<div class="awb-wizard-option-group" data-layout="footer">
				<div class="pyre_metabox_field layout-input">
					<div class="pyre_desc">
						<label for="header_layout"><?php esc_html_e( 'Footer Layout', 'Avada' ); ?></label>
						<p><?php esc_html_e( 'Set your website global footer layout.', 'Avada' ); ?></p>
					</div>
					<div class="pyre_field">
						<a href="#" class="button set-layout-to-default" data-context="footer" style="display:none;"><?php esc_html_e( 'Default', 'Avada' ); ?></a>
						<a href="#" class="button button-primary awb-studio-link" data-context="footer"><?php esc_html_e( 'Select Footer', 'Avada' ); ?></a>
						<input type="hidden" name="footer" value="" class="layout-input-id" />
						<input type="hidden" name="footer-options" value="" class="layout-input-options" />
					</div>
				</div>
				<div class="awb-iframe-preview footer-preview">
						<?php AWB_Setup_Wizard::studio_import_options_template( 'footer' ); ?>
						<iframe class="lazyload" data-orig-src="<?php echo esc_url( $default_footer_url ); ?>" data-default="<?php echo esc_url( $default_footer_url ); ?>" frameborder="0" width="1400"></iframe>
				</div>
			</div>

			<div class="awb-wizard-actions">
				<a href="#7" class="button button-primary awb-wizard-link awb-wizard-next" data-id="7"><span class="awb-text-wrapper"><span><?php esc_html_e( 'Next Step', 'Avada' ); ?></span><i class="fusiona-arrow-forward"></i></span></a>
			</div>
		</div>

	</section>

	<section class="awb-setup-wizard-section content-features hidden" data-step="7" data-save="0">
		<div class="awb-setup-wizard-hero">
			<div class="awb-setup-wizard-hero-text">
				<h2><?php esc_html_e( 'Website Content', 'Avada' ); ?></h2>
				<p><?php esc_html_e( 'Select pages and additional features to be added to your website.', 'Avada' ); ?></p>
			</div>
		</div>

		<div class="awb-setup-wizard-content">
			<div class="awb-setup-wizard-content-grid-wrap">
				<div class="awb-setup-wizard-dummy-content">
					<label class="dummy-content-button unselectable">
						<input type="checkbox" value="dummy-content" checked>
						<span><?php esc_html_e( 'Import Dummy Content', 'Avada' ); ?></span>
						<i class="dummy-content-info-icon awb-toggle-info-icon" data-selector=".dummy-content-desc">
							<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M13.7817 7.00001C13.7817 10.746 10.7452 13.7815 7.00025 13.7815C3.25525 13.7815 0.21875 10.7465 0.21875 7.00001C0.21875 3.25601 3.25525 0.218506 7.00025 0.218506C10.7452 0.218506 13.7817 3.25601 13.7817 7.00001ZM7.18225 2.46101C5.69225 2.46101 4.74175 3.08851 3.99525 4.20451C3.89875 4.34901 3.93075 4.54401 4.06925 4.64901L5.01825 5.36851C5.16075 5.47651 5.36325 5.45101 5.47375 5.31051C5.96225 4.69101 6.29725 4.33151 7.04075 4.33151C7.59925 4.33151 8.29025 4.69101 8.29025 5.23251C8.29025 5.64201 7.95225 5.85251 7.40075 6.16151C6.75775 6.52201 5.90625 6.97101 5.90625 8.09351V8.20301C5.90625 8.38401 6.05325 8.53101 6.23425 8.53101H7.76575C7.94675 8.53101 8.09375 8.38401 8.09375 8.20301V8.16651C8.09375 7.38801 10.3682 7.35601 10.3682 5.25001C10.3682 3.66401 8.72325 2.46101 7.18175 2.46101H7.18225ZM7.00025 9.24201C6.30675 9.24201 5.74225 9.80601 5.74225 10.5C5.74225 11.194 6.30625 11.758 7.00025 11.758C7.69425 11.758 8.25825 11.1935 8.25825 10.5C8.25825 9.80651 7.69425 9.24201 7.00025 9.24201Z" fill="#198FD9"/>
							</svg>
						</i>
					</label>
				</div>
				<div class="awb-setup-wizard-notice info dummy-content-desc hidden">
					<p><?php esc_html_e( 'Recommended. This will create dummy blog posts, portfolio posts, and WooCommerce products to populate the feature layouts if selected.', 'Avada' ); ?></p>
				</div>
				<?php
					$website_features = [
						'homepage'  => [
							'id'        => 'homepage',
							'title'     => __( 'Home', 'Avada' ),
							'studio_id' => 2161,
							'post_type' => 'page',
						],
						'about'     => [
							'id'        => 'about',
							'title'     => __( 'About', 'Avada' ),
							'studio_id' => 2280,
							'post_type' => 'page',
						],
						'blog'      => [
							'id'              => 'blog',
							'title'           => __( 'Blog', 'Avada' ),
							'studio_id'       => 2237,
							'post_type'       => 'page',
							'section_layouts' => [
								[
									'title'     => __( 'Blog Title Bar', 'Avada' ),
									'studio_id' => 2228,
								],
								[
									'title'     => __( 'Archives Title Bar', 'Avada' ),
									'studio_id' => 2309,
								],
								[
									'title'     => __( 'Blog Single Post', 'Avada' ),
									'studio_id' => 2229,
								],
								[
									'title'     => __( 'Blog Archive', 'Avada' ),
									'studio_id' => 2231,
								],
							],
							'layouts'         => [
								[
									'title'          => __( 'Single Blog Post', 'Avada' ),
									'template_terms' => [
										'page_title_bar' => 2228,
										'content'        => 2229,
									],
									'conditions'     => [
										'singular_post' => [
											'label'    => 'All Posts',
											'type'     => 'singular',
											'mode'     => 'include',
											'singular' => 'singular_post',
										],
									],
								],
								[
									'title'          => __( 'Blog Archive', 'Avada' ),
									'template_terms' => [
										'page_title_bar' => 2309,
										'content'        => 2231,
									],
									'conditions'     => [
										'all_archives'     => [
											'label'    => 'All Archives Pages',
											'type'     => 'archives',
											'mode'     => 'include',
											'archives' => 'all_archives',
										],
										'archive_of_product' => [
											'label'    => 'Products Archive Types',
											'type'     => 'archives',
											'mode'     => 'exclude',
											'archives' => 'archive_of_product',
										],
										'product_cat'      => [
											'label'    => 'All Product categories',
											'type'     => 'archives',
											'mode'     => 'exclude',
											'archives' => 'product_cat',
										],
										'product_tag'      => [
											'label'    => 'All Product tags',
											'type'     => 'archives',
											'mode'     => 'exclude',
											'archives' => 'product_tag',
										],
										'portfolio_category' => [
											'label'    => 'All Portfolio Categories',
											'type'     => 'archives',
											'mode'     => 'exclude',
											'archives' => 'portfolio_category',
										],
										'portfolio_skills' => [
											'label'    => 'All Portfolio Skills',
											'type'     => 'archives',
											'mode'     => 'exclude',
											'archives' => 'portfolio_skills',
										],
										'portfolio_tags'   => [
											'label'    => 'All Portfolio Tags',
											'type'     => 'archives',
											'mode'     => 'exclude',
											'archives' => 'portfolio_tags',
										],

									],
								],
							],
						],
						'portfolio' => [
							'id'              => 'portfolio',
							'title'           => __( 'Portfolio', 'Avada' ),
							'studio_id'       => 2270,
							'post_type'       => 'page',
							'plugins'         => [ 'portfolio' ],
							'section_layouts' => [
								[
									'title'     => __( 'Portfolio Single Content', 'Avada' ),
									'studio_id' => 2267,
								],
								[
									'title'     => __( 'Portfolio Archive Content', 'Avada' ),
									'studio_id' => 2265,
								],
							],
							'layouts'         => [
								[
									'title'          => __( 'Portfolio Single', 'Avada' ),
									'template_terms' => [
										'content' => 2267,
									],
									'conditions'     => [
										'singular_avada_portfolio' => [
											'label'    => 'All Portfolio',
											'type'     => 'singular',
											'mode'     => 'include',
											'singular' => 'singular_avada_portfolio',
										],
									],
								],
								[
									'title'          => __( 'Portfolio Archive', 'Avada' ),
									'template_terms' => [
										'page_title_bar' => 2309,
										'content'        => 2265,
									],
									'conditions'     => [
										'archive_of_avada_portfolio' => [
											'label'    => 'Portfolio Archive Types',
											'type'     => 'archives',
											'mode'     => 'include',
											'archives' => 'archive_of_avada_portfolio',
										],
										'portfolio_category' => [
											'label'    => 'All Portfolio Categories',
											'type'     => 'archives',
											'mode'     => 'include',
											'archives' => 'portfolio_category',
										],
										'portfolio_skills' => [
											'label'    => 'All Portfolio Skills',
											'type'     => 'archives',
											'mode'     => 'include',
											'archives' => 'portfolio_skills',
										],
										'portfolio_tags'   => [
											'label'    => 'All Portfolio Tags',
											'type'     => 'archives',
											'mode'     => 'include',
											'archives' => 'portfolio_tags',
										],
									],
								],
							],
						],
						'shop'      => [
							'id'              => 'shop',
							'title'           => __( 'Shop', 'Avada' ),
							'post_type'       => 'page',
							'plugins'         => [ 'woocommerce' ],
							'shop'            => 2316,
							'cart'            => 2249,
							'checkout'        => 2247,
							'section_layouts' => [
								[
									'title'     => __( 'Product Single Content', 'Avada' ),
									'studio_id' => 2244,
								],
								[
									'title'     => __( 'Products Archive Content', 'Avada' ),
									'studio_id' => 2245,
								],
							],
							'layouts'         => [
								[
									'title'          => __( 'Product Single', 'Avada' ),
									'template_terms' => [
										'content' => 2244,
									],
									'conditions'     => [
										'singular_product' => [
											'label'    => 'All Products',
											'type'     => 'singular',
											'mode'     => 'include',
											'singular' => 'singular_product',
										],
									],
								],
								[
									'title'          => __( 'Products Archive', 'Avada' ),
									'template_terms' => [
										'page_title_bar' => 2309,
										'content'        => 2245,
									],
									'conditions'     => [
										'product_cat' => [
											'label'    => 'All Product categories',
											'type'     => 'archives',
											'mode'     => 'include',
											'archives' => 'product_cat',
										],
										'product_tag' => [
											'label'    => 'All Product tags',
											'type'     => 'archives',
											'mode'     => 'include',
											'archives' => 'product_tag',
										],
									],
								],
							],
						],
						'services'  => [
							'id'        => 'services',
							'title'     => __( 'Services', 'Avada' ),
							'studio_id' => 2273,
							'post_type' => 'page',
						],
						'contact'   => [
							'id'        => 'contact',
							'title'     => __( 'Contact', 'Avada' ),
							'plugins'   => [ 'forms' ],
							'studio_id' => 2283,
							'post_type' => 'page',
						],
						'reviews'   => [
							'id'        => 'reviews',
							'title'     => __( 'Reviews', 'Avada' ),
							'studio_id' => 2300,
							'post_type' => 'page',
						],
					];

					$defaults = [
						'homepage',
						'blog',
						'contact',
						'forms',
						'off-canvas',
					];

					require Avada::$template_dir_path . '/includes/admin-screens/content-step-svgs.php';
					?>
				<div class="awb-db-preview-grid content-grid awb-setup-wizard-allow-invert">
					<?php
					foreach ( $website_features as $feature => $feature_data ) {
						$name = $feature_data['title'];
						?>
						<article class="awb-db-preview awb-feature-wrap">
							<label>
								<input type="checkbox" class="awb-feature hidden" name="awb-feature-<?php echo esc_attr( $feature ); ?>" value="<?php echo esc_attr( $feature ); ?>" <?php checked( in_array( $feature, $defaults, true ) ); ?> data-settings="<?php echo esc_attr( wp_json_encode( $feature_data ) ); ?>" />
								<span class="selected-frame"></span>
								<div class="image-wrap">
									<?php echo balanceTags( $content_svgs[ $feature ] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
								</div>
								<div class="awb-db-preview-bar">
									<input type="text" class="awb-feature-title" value="<?php echo esc_html( $name ); ?>">
								</div>
							</label>
						</article>
					<?php } ?>
				</div>
			</div>

		<div class="awb-setup-wizard-features">
			<h3 class="awb-setup-wizard-features-title"><?php esc_html_e( 'Features To Activate', 'Avada' ); ?></h3>
			<?php
				$features = [
					'woocommerce'                 => [
						'label' => __( 'Shop', 'Avada' ),
						'type'  => 'plugin',
					],
					'the-events-calendar'         => [
						'label' => __( 'Events', 'Avada' ),
						'type'  => 'plugin',
					],
					'bbpress'                     => [
						'label' => __( 'Forum', 'Avada' ),
						'type'  => 'plugin',
					],
					'forms'                       => [
						'label' => __( 'Forms', 'Avada' ),
						'type'  => 'feature',
						'icon'  => 'fusiona-forms',
					],
					'off-canvas'                  => [
						'label' => __( 'Off Canvas', 'Avada' ),
						'type'  => 'feature',
						'icon'  => 'fusiona-off-canvas',
					],
					'portfolio'                   => [
						'label' => __( 'Portfolio', 'Avada' ),
						'type'  => 'feature',
						'icon'  => 'fusiona-insertpicture',
					],
					'leadin'                      => [
						'label' => __( 'Live Chat', 'Avada' ),
						'type'  => 'plugin',
					],
					'fusion-white-label-branding' => [
						'label' => __( 'Custom Branding', 'Avada' ),
						'type'  => 'plugin',
					],
					'filebird-pro'                => [
						'label' => __( 'Media Management', 'Avada' ),
						'type'  => 'plugin',
					],
					'advanced-custom-fields-pro'  => [
						'label' => __( 'Dev Tools', 'Avada' ),
						'type'  => 'plugin',
					],
				];

				$plugins     = AWB_Prebuilt_Websites()->get_plugins_info(); //phpcs:ignore WordPress.WP.GlobalVariablesOverride
				$all_plugins = get_plugins();
				?>

			<div class="awb-db-preview-grid awb-db-needed-plugins">
				<?php foreach ( $features as $slug => $feature ) { ?>
					<?php
					$checked       = '';
					$data_attrs    = '';
					$plugin_status = 'uninstalled';
					if ( 'plugin' === $feature['type'] && isset( $plugins[ $slug ] ) ) {
						if ( is_plugin_active( $plugins[ $slug ]['file_path'] ) ) {
							$plugin_status = 'activated';
						} elseif ( isset( $all_plugins[ $plugins[ $slug ]['file_path'] ] ) ) {
							$plugin_status = 'installed';
						}
					}
					$checked = ( 'plugin' === $feature['type'] && 'activated' === $plugin_status ) || ( 'feature' === $feature['type'] && in_array( $slug, $defaults, true ) ) ? ' checked="checked"' : '';

					$feature_image_url = get_template_directory_uri() . '/assets/admin/images/setup-wizard/' . $slug . '.png';
					?>
						<div class="awb-db-feature">
							<label>
							<input type="checkbox" class="awb-needed-plugin" name="awb-needed-plugin-<?php echo esc_attr( $slug ); ?>" value="<?php echo esc_attr( $slug ); ?>"<?php echo $checked; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> />
							<span class="selected-frame"><span class="checkbox fusiona-checkmark"></span></span>
							<?php
							if ( isset( $feature['icon'] ) ) {
								echo '<span class="feature-icon ' . esc_attr( $feature['icon'] ) . '"></span>';
							} else {
								?>
								<img src="<?php echo esc_url( $feature_image_url ); ?>" alt="">
							<?php } ?>
								<span class="feature-title"><?php echo esc_html( $feature['label'] ); ?></span>
							</label>
						</div>
				<?php } ?>
			</div>
		</div>

			<div class="awb-wizard-actions">
				<a href="#8" class="button button-primary awb-wizard-link awb-wizard-next" data-id="8"><span class="awb-text-wrapper"><span><?php esc_html_e( 'Next Step', 'Avada' ); ?></span><i class="fusiona-arrow-forward"></i></span></a>
			</div>
		</div>

	</section>

	<section class="awb-setup-wizard-section confirm-setup scratch-confirm-step hidden" data-step="8" data-save="0">
		<div class="awb-setup-wizard-hero">
			<div class="awb-setup-wizard-hero-text">
				<h2><?php esc_html_e( 'Your Website Is Almost Ready', 'Avada' ); ?></h2>
				<p><?php esc_html_e( 'Click to complete the setup and your website will be set up. Please note, global options and previous setup wizard install content will be replaced or no longer be used.', 'Avada' ); ?></p>
				<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/admin/images/confirm-setup.jpg' ); ?>" alt="<?php esc_html_e( 'Confirm Setup', 'Avada' ); ?>" width="460" height="138">
			</div>
		</div>

		<div class="awb-setup-wizard-content">
			<a href="#" class="button button-primary awb-setup-build" data-confirm="scratch"><span><?php esc_html_e( 'Complete Setup', 'Avada' ); ?></span></a>
			<ul class="checklist">
				<li data-action="set-globals"><span class="awb-setup-stage-check"><i></i><span class="avada-db-loader"></span></span><span class="awb-setup-stage-label"><?php esc_html_e( 'Global Options', 'Avada' ); ?></span></li>
				<li data-action="import-pages"><span class="awb-setup-stage-check"><i></i><span class="avada-db-loader"></span></span><span class="awb-setup-stage-label"><?php esc_html_e( 'Pages & Dummy Content', 'Avada' ); ?></span></li>
				<li data-action="save-layouts"><span class="awb-setup-stage-check"><i></i><span class="avada-db-loader"></span></span><span class="awb-setup-stage-label"><?php esc_html_e( 'Layouts', 'Avada' ); ?></span></li>
				<li data-action="finalise-scratch-setup"><span class="awb-setup-stage-check"><i></i><span class="avada-db-loader"></span></span><span class="awb-setup-stage-label"><?php esc_html_e( 'Finalise Setup', 'Avada' ); ?></span></li>
			</ul>
		</div>

	</section>

	<section class="awb-setup-wizard-section finished hidden" data-step="9" data-save="0">
		<div class="awb-setup-wizard-hero">
			<div class="awb-setup-wizard-hero-text">
				<?php /* translators: %s: user name */ ?>
				<h2><?php echo esc_html( sprintf( __( 'Congrats, %s!', 'Avada' ), ucfirst( $the_user->display_name ) ) ); ?></h2>
				<p><?php esc_html_e( 'The Setup Wizard has successfully set up your website. Edit your site, and explore major features and support areas from the links below.', 'Avada' ); ?></p>
				<a href="<?php echo esc_url( add_query_arg( 'fb-edit', '1', get_site_url() ) ); ?>" rel="noopener noreferrer" target="_blank" class="button button-primary"><?php esc_html_e( 'Launch Avada Live', 'Avada' ); ?></a>
				<a href="<?php echo esc_url( get_site_url() ); ?>" class="visit-site" rel="noopener noreferrer" target="_blank"><?php esc_html_e( 'Or visit site', 'Avada' ); ?></a>
			</div>
		</div>

		<div class="awb-setup-wizard-content">
			<h3><?php esc_html_e( 'Customize Your Website', 'Avada' ); ?></h3>
			<div class="link-grid">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=avada-layouts' ) ); ?>" rel="noopener noreferrer" target="_blank"><i class="fusiona-layouts"></i><span><strong><?php esc_html_e( 'Layouts', 'Avada' ); ?></strong><small><?php esc_html_e( 'View and manage your layouts.', 'Avada' ); ?></small></span></a>
				<a href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>" rel="noopener noreferrer" target="_blank"><i class="fusiona-bars"></i><span><strong><?php esc_html_e( 'Menus', 'Avada' ); ?></strong><small><?php esc_html_e( 'Edit and create website menus.', 'Avada' ); ?></small></span></a>
				<a href="<?php echo esc_url( admin_url( 'themes.php?page=avada_options' ) ); ?>" rel="noopener noreferrer" target="_blank"><i class="fusiona-cog"></i><span><strong><?php esc_html_e( 'Global Options', 'Avada' ); ?></strong><small><?php esc_html_e( 'Colors, typography and more.', 'Avada' ); ?></small></span></a>
			</div>
			<div class="awb-setup-wizard-notice info">
				<p><?php esc_html_e( 'Create a support account in order to submit tickets and manage your licenses.', 'Avada' ); ?></p>
				<a href="https://theme-fusion.com/login/?purchase_code=<?php echo esc_url( Avada()->registration->get_purchase_code() ); ?>" rel="noopener noreferrer" target="_blank" class="button button-primary"><?php esc_html_e( 'Create an Account', 'Avada' ); ?></a>
			</div>
			<h3><?php esc_html_e( 'We Are Here To Help', 'Avada' ); ?></h3>
			<div class="link-grid">
				<a href="https://theme-fusion.com/documentation/avada/" rel="noopener noreferrer" target="_blank"><i class="fusiona-documentation"></i><span><strong><?php esc_html_e( 'Documentation', 'Avada' ); ?></strong><small><?php esc_html_e( 'Read our extensive docs', 'Avada' ); ?></small></span></a>
				<a href="https://www.youtube.com/c/ThemeFusionVideos/videos" rel="noopener noreferrer" target="_blank"><i class="fusiona-video"></i><span><strong><?php esc_html_e( 'Video Tutorials', 'Avada' ); ?></strong><small><?php esc_html_e( 'Join our YouTube channel.', 'Avada' ); ?></small></span></a>
				<a href="https://theme-fusion.com/support/submit-a-ticket/" rel="noopener noreferrer" target="_blank"><i class="fusiona-author"></i><span><strong><?php esc_html_e( 'Submit a Ticket', 'Avada' ); ?></strong><small><?php esc_html_e( 'Ask our support team.', 'Avada' ); ?></small></span></a>
			</div>
		</div>

	</section>

	<section class="awb-setup-wizard-section hidden" data-step="20" data-save="0">

		<div class="awb-setup-wizard-hero">
			<div class="awb-setup-wizard-hero-text">
				<h2><?php esc_html_e( 'Select A Website', 'Avada' ); ?></h2>
				<p><?php esc_html_e( 'Select a prebuilt website to import.', 'Avada' ); ?></p>
			</div>
		</div>

		<div class="awb-setup-wizard-content">
			<div class="avada-db-demos-wrapper">
				<?php
				$imported_data = get_option( 'fusion_import_data', [] );
				$import_stages = avada_get_demo_import_stages();

				$demos                = AWB_Prebuilt_Websites()->get_websites();
				$all_tags             = AWB_Prebuilt_Websites()->get_tags();
				$imported_demos_count = AWB_Prebuilt_Websites()->get_imported_websites();
				?>

				<?php
				/**
				 * Add the tag-selector.
				 */
				?>
				<section class="avada-db-demo-selector">
					<?php require Avada::$template_dir_path . '/includes/admin-screens/prebuilt-websites-tag-selector.php'; ?>
				</section>

				<div class="avada-db-demos-themes">
					<div class="feature-section theme-browser rendered">

						<?php
						foreach ( $demos as $demo => $demo_details ) { // Loop through all available demos.
							include Avada::$template_dir_path . '/includes/admin-screens/prebuilt-websites-demo.php';
						}
						?>
					</div>
				</div>
			</div>
		</div>

	</section>


	<section class="awb-setup-wizard-section confirm-setup prebuilt-confirm-step hidden" data-step="21" data-save="0">
		<div class="awb-setup-wizard-hero">
			<div class="awb-setup-wizard-hero-text">
				<div class="awb-selected-prebuilt">
					<h2><?php esc_html_e( 'Your Website Is Almost Ready', 'Avada' ); ?></h2>
					<p><?php esc_html_e( 'Click to complete the setup and your website will be set up. Please note, global options and previous setup wizard install content will be replaced or no longer be used.', 'Avada' ); ?></p>
					<p>
						<span id="awb-selected-prebuilt-thumb"></span>
					</p>
				</div>
			</div>
		</div>

		<div class="awb-setup-wizard-content">
			<a href="#" class="button button-primary awb-setup-build" id="awb-temp-install" data-confirm="prebuilt"><span><?php esc_html_e( 'Complete Setup', 'Avada' ); ?></span></a>
			<div class="awb-selected-prebuilt-features">
				<ul id="awb-selected-prebuilt-features-list" class="checklist"></ul>
			</div>
		</div>
	</section>

	<div class="awb-studio-modal">
		<div class="post-modal-bg"></div>
		<div class="post-preview">
			<div id="fusion-loader"><span class="fusion-builder-loader"></span></div>
			<section class="awb-studio-content">
				<div id="filter-bar" class="avada-db-card">
					<input id="search-input" type="search" placeholder="Search">
					<nav data-type=""></nav>
				</div>
				<main id="main-content">
					<section class="previews">
					</section>
				</main>
			</section>
			<iframe class="awb-studio-preview-frame" frameborder="0" scrolling="auto" allowfullscreen=""></iframe><?php //phpcs:ignore WPThemeReview -- iframe usage ?>
			<?php
			if ( class_exists( 'AWB_Studio' ) ) :
				AWB_Studio::studio_import_options_template( 'setup-wizard' );
			endif;
			?>
		</div>
	</div>

	<?php wp_nonce_field( 'awb_setup_nonce', 'awb-setup-nonce' ); ?>
<?php $this->get_admin_screens_footer(); ?>
