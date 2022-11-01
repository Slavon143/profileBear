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

<?php $this->get_admin_screens_header( 'performance' ); ?>

	<?php
	if ( ! PyreThemeFrameworkMetaboxes::$instance ) {
		new PyreThemeFrameworkMetaboxes();
	}
	$metaboxes      = PyreThemeFrameworkMetaboxes::$instance;
	$run_lighthouse = ! empty( $_GET['lighthouse'] ) || false !== apply_filters( 'awb_lighthouse_api_key', false ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	$posts_count_limit = 1000;
	$posts_count       = wp_cache_get( 'awb_performance_posts_count' );

	if ( false === $posts_count ) {
		global $wpdb;
		$results     = (array) $wpdb->get_results( "SELECT COUNT(*) AS posts_count FROM {$wpdb->posts} WHERE post_status = 'publish' OR post_status = 'draft'", ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$posts_count = isset( $results[0]['posts_count'] ) ? $results[0]['posts_count'] : 0;

		// Set cache.
		wp_cache_set( 'awb_performance_posts_count', $posts_count );
	}
	?>

	<section class="avada-db-card avada-db-welcome-setup awb-wizard-section" data-step="1">
		<div class="avada-db-welcome-container">
			<div class="avada-db-welcome-intro">
				<h1 class="avada-db-welcome-heading"><?php esc_html_e( 'Performance Wizard', 'Avada' ); ?></h1>
				<p class="avada-db-welcome-text"><?php esc_html_e( 'Follow the wizard to optimize your website.', 'Avada' ); ?></p>
			</div>
			<img class="avada-db-welcome-image" src="<?php echo esc_url( get_template_directory_uri() . '/assets/admin/images/performance-wizard.jpeg' ); ?>" alt="<?php esc_html_e( 'Performance Wizard' ); ?>" width="1200" height="712" />
		</div>

		<div class="avada-db-setup">
			<h2 class="avada-db-setup-heading"><?php esc_html_e( 'Before Starting', 'Avada' ); ?></h2>
			<p class="avada-db-setup-text"><?php esc_html_e( 'Ensure you are ready to optimize your website.', 'Avada' ); ?></p>

			<div class="avada-db-setup-step avada-db-step-two avada-db-completed">
				<div class="avada-db-setup-step-info">
					<h3 class="avada-db-setup-step-heading"><?php esc_html_e( 'Build Your Website', 'Avada' ); ?></h3>
					<p class="avada-db-setup-step-text avada-db-card-text-small"><?php esc_html_e( 'Build your website before starting with performance optimization.', 'Avada' ); ?></p>
				</div>
				<i class="avada-db-setup-step-icon fusiona-unlock"></i>
			</div>

			<a href="https://theme-fusion.com/documentation/avada/" target="_blank" rel="noopener" class="avada-db-setup-step avada-db-step-three avada-db-completed">
				<div class="avada-db-setup-step-info">
					<h3 class="avada-db-setup-step-heading"><?php esc_html_e( 'Read The Documentation', 'Avada' ); ?></h3>
					<p class="avada-db-setup-step-text avada-db-card-text-small"><?php esc_html_e( 'Learn more about performance in our documentation, and pick up extra tips and tricks.', 'Avada' ); ?></p>
				</div>
				<i class="avada-db-setup-step-icon fusiona-demos"></i>
			</a>

			<?php if ( $run_lighthouse ) : ?>
			<div class="avada-db-setup-step avada-db-step-three avada-db-completed awb-wizard-choices">
				<div class="avada-db-setup-step-info">
					<h3 class="avada-db-setup-step-heading"><?php esc_html_e( 'Select Testing', 'Avada' ); ?></h3>
					<p class="avada-db-setup-step-text avada-db-card-text-small"><?php esc_html_e( 'Select if you would like to attempt before and after testing.  Will slow down process if selected..', 'Avada' ); ?></p>
				</div>
				<div class="awb-choices pyre_field avada-buttonset radio">
					<div class="fusion-form-radio-button-set ui-buttonset">
						<input type="hidden" id="awb_wizard_tests" name="awb_wizard_tests" value="none" class="button-set-value">
						<a href="#" class="ui-button buttonset-item ui-state-active" data-value="none"><?php esc_html_e( 'None', 'Avada' ); ?></a>
						<a href="#" class="ui-button buttonset-item" data-value="mobile"><?php esc_html_e( 'Mobile', 'Avada' ); ?></a>
						<a href="#" class="ui-button buttonset-item" data-value="desktop"><?php esc_html_e( 'Desktop', 'Avada' ); ?></a>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<a href="#2" class="button button-primary awb-wizard-link awb-wizard-next awb-get-started" data-id="2"><span class="awb-text-wrapper"><span><?php esc_html_e( 'Let\'s Start', 'Avada' ); ?></span><i class="fusiona-arrow-forward"></i></span></a>
		</div>
	</section>

	<section class="avada-db-card hidden awb-wizard-section" data-step="2" data-save="0">
		<div class="awb-wizard-hero">
			<div class="awb-wizard-hero-text">
				<h2><?php esc_html_e( 'Avada Features', 'Avada' ); ?></h2>
				<p><?php esc_html_e( 'In this step, you can disable any features you aren’t using, thereby reducing the amount of code loaded. Please be cautious, as disabling a feature you are using will result in broken behaviour until it is enabled again. Click the \'Find Recommendations\' button to scan your website and present recommendations.', 'Avada' ); ?></p>
				<div class="wizard-multi-buttons">
					<a href="#featureScan" class="button button-secondary awb-wizard-scan-button" data-id="features"><i class="awb-wizard-button-icon fusiona-search"></i><?php esc_html_e( 'Find Recommendations', 'Avada' ); ?></a>
					<a href="#applyAll" class="button button-secondary awb-wizard-apply"><?php esc_html_e( 'Apply All', 'Avada' ); ?></a>
				</div>
			</div>
			<i class="fusiona-equalizer awb-wizard-hero-icon"></i>
		</div>

		<h3 class="wizard-sep-heading"><?php esc_html_e( 'Disable Unused Features', 'Avada' ); ?></h3>
		<?php
		$settings        = awb_get_fusion_settings();
		$options         = apply_filters( 'fusion_settings_all_fields', [] );
		$feature_options = [
			$options['disable_megamenu'],
			$options['status_yt'],
			$options['status_vimeo'],
			$options['status_gmap'],
			$options['button_presets'],
			$options['load_block_styles'],
			$options['avada_rev_styles'],
			$options['emojis_disabled'],
			$options['status_eslider'],
			$options['status_fusion_slider'],
			$options['status_fusion_forms'],
			$options['status_awb_Off_Canvas'],
			$options['status_fusion_portfolio'],
			$options['status_fusion_faqs'],
		];

		$on_off = [
			'1' => __( 'On', 'Avada' ),
			'0' => __( 'Off', 'Avada' ),
		];

		foreach ( $feature_options as $feature_option ) {
			$choices = $on_off;
			if ( isset( $feature_option['choices'] ) ) {
				$choices = $feature_option['choices'];
			}
			echo $metaboxes->radio_buttonset( $feature_option['id'], $feature_option['label'], $choices, $feature_option['description'], $feature_option['default'], [] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		?>

		<h3 class="wizard-sep-heading h-margin-top"><?php esc_html_e( 'Other Feature Recommendations', 'Avada' ); ?></h3>
		<?php
		$layouts = Fusion_Template_Builder();
		$globals = $layouts::get_default_layout();

		// Still using legacy containers.
		if ( 1 === (int) $settings->get( 'container_legacy_support' ) ) {
			$metaboxes->radio_buttonset( $options['container_legacy_support']['id'], $options['container_legacy_support']['label'], $choices, $options['container_legacy_support']['description'], $options['container_legacy_support']['default'], [], '0' );
			echo '<p class="wizard-recommendation"><i class="fusiona-af-rating"></i> ' . esc_html__( 'Recommend disabling. Flex layouts are faster to render and cause less content layout shift.', 'Avada' ) . '</p>';
		}
		?>

		<?php if ( empty( $globals ) || empty( $globals['data']['template_terms']['header'] ) ) : ?>
			<div class="pyre_metabox_field">
				<div class="pyre_desc">
					<label for="pyre_container_legacy_support"><?php esc_html_e( 'Global Header', 'Avada' ); ?></label>
					<p><?php esc_html_e( 'A global header can be built on the layout builder page. When set this will be used as the default website header instead of the legacy approach using global options.', 'Avada' ); ?></p>
				</div>
				<div class="pyre_field">
					<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=avada-layouts' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Build Header', 'Avada' ); ?></a>
				</div>
			</div>
			<p class="wizard-recommendation"><i class="fusiona-af-rating"></i><?php esc_html_e( 'Recommend building a global header. Older legacy header assets will not be loaded and new approach causes less content layout shift.', 'Avada' ); ?></p>
		<?php endif; ?>

		<?php
		// Disable animations on mobile.
		if ( 'desktop_and_mobile' === $settings->get( 'status_css_animations' ) ) {
			$metaboxes->radio_buttonset( $options['status_css_animations']['id'], $options['status_css_animations']['label'], $options['status_css_animations']['choices'], $options['status_css_animations']['description'], $options['status_css_animations']['default'], [], 'desktop' );
			echo '<p class="wizard-recommendation"><i class="fusiona-af-rating"></i> ' . esc_html__( 'Recommend setting to desktop only to disable element animations on mobile devices.', 'Avada' ) . '</p>';
		}
		?>

		<p class="awb-empty-feature"><?php esc_html_e( 'No extra feature recommendations found for your current setup.', 'Avada' ); ?></p>

		<div class="awb-wizard-actions">
			<a href="#3" class="button button-secondary awb-wizard-save hidden" data-id="features"><i class="awb-wizard-button-icon fusiona-exclamation-sign"></i><?php esc_html_e( 'Save Changes', 'Avada' ); ?></a>
			<a href="#3" class="button button-primary awb-wizard-link awb-wizard-next" data-id="3"><span class="awb-text-wrapper"><span><?php esc_html_e( 'Next Step', 'Avada' ); ?></span><i class="fusiona-arrow-forward"></i></span></a>
		</div>
	</section>

	<section class="avada-db-card hidden awb-wizard-section" data-step="3">

		<div class="awb-wizard-hero">
			<div class="awb-wizard-hero-text">
				<h2><?php esc_html_e( 'Avada Icons', 'Avada' ); ?></h2>
				<p><?php esc_html_e( 'In this step, you can scan your website to get information about any icons you are using. You can also disable unused icon sets, and read about how to optimize your icons.', 'Avada' ); ?></p>
				<?php if ( $posts_count_limit < $posts_count ) : ?>
					<div class="avada-db-card-notice recommended-notice slim-notice notice-red">
						<i class="fusiona-exclamation-triangle"></i>
						<p class="avada-db-card-notice-heading"><?php esc_html_e( 'Warning: Large number of posts detected, scanning might take a while.', 'Avada' ); ?></p>
					</div>
				<?php endif; ?>
				<div class="wizard-multi-buttons">
					<a href="#iconScan" class="button awb-wizard-scan-button" data-id="icons" title="<?php esc_attr_e( 'Find Recommendations', 'Avada' ); ?>"><i class="awb-wizard-button-icon fusiona-search"></i><?php esc_html_e( 'Find Recommendations', 'Avada' ); ?></a>
					<a href="#applyAll" class="button button-secondary awb-wizard-apply"><?php esc_html_e( 'Apply All', 'Avada' ); ?></a>
				</div>
			</div>
			<i class="fusiona-flag awb-wizard-hero-icon"></i>
		</div>

		<table class="widefat hidden" cellspacing="0" id="fusion-used-icons-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Title', 'Avada' ); ?></th>
					<th><?php esc_html_e( 'Content', 'Avada' ); ?></th>
					<th><?php esc_html_e( 'Meta', 'Avada' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td colspan="3"><?php esc_html_e( 'Run the icon scan to have an overview of usage. Note, the time this will take will depend on the size of your website.', 'Avada' ); ?></th>
				</tr>
			</tbody>
		</table>

		<?php
		echo $metaboxes->multiple( 'status_fontawesome', $options['status_fontawesome']['label'], $options['status_fontawesome']['choices'], $options['status_fontawesome']['description'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $metaboxes->radio_buttonset( 'fontawesome_v4_compatibility', $options['fontawesome_v4_compatibility']['label'], $choices, $options['fontawesome_v4_compatibility']['description'], $options['fontawesome_v4_compatibility']['default'], [] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>

		<div class="avada-db-card-notice awb-wizard-tip">
			<i class="fusiona-info-circle"></i>
			<p class="avada-db-card-notice-heading"><strong><?php esc_html_e( 'Tip', 'Avada' ); ?></strong>: <?php esc_html_e( 'Convert from Font Awesome to a single custom icon set to improve your load time.', 'Avada' ); ?><a href="https://theme-fusion.com/documentation/avada/performance/how-to-replace-font-awesome-icons-with-a-custom-icon-set/" target="_blank" rel="noopener noreferrer"> <?php esc_html_e( 'Find Out How!', 'Avada' ); ?></a></p>
		</div>

		<div class="awb-wizard-actions">
			<a href="#4" class="button button-secondary awb-wizard-save hidden" data-id="icons"><i class="awb-wizard-button-icon fusiona-exclamation-sign"></i><?php esc_html_e( 'Save Changes', 'Avada' ); ?></a>
			<a href="#4" class="button button-primary awb-wizard-link awb-wizard-next" data-id="4"><span class="awb-text-wrapper"><span><?php esc_html_e( 'Next Step', 'Avada' ); ?></span><i class="fusiona-arrow-forward"></i></span></a>
		</div>
	</section>

	<section class="avada-db-card hidden awb-wizard-section" data-step="4">
		<div class="awb-wizard-hero">
			<div class="awb-wizard-hero-text">
				<h2><?php esc_html_e( 'Avada Fonts/Typography', 'Avada' ); ?></h2>
				<p><?php esc_html_e( 'In this step, you can check which fonts are being loaded on your website and optimize how those fonts are served. Ensure all families and variants selected here are required. Less variants will mean less requests and therefore faster loading.', 'Avada' ); ?></p>

				<div class="wizard-multi-buttons">
					<a href="#fontScan" class="button awb-wizard-scan-button" data-id="fonts" title="<?php esc_attr_e( 'Find Recommendations', 'Avada' ); ?>"><i class="awb-wizard-button-icon fusiona-search"></i><?php esc_html_e( 'Find Recommendations', 'Avada' ); ?></a>
					<a href="#applyAll" class="button button-secondary awb-wizard-apply"><?php esc_html_e( 'Apply All', 'Avada' ); ?></a>
				</div>
			</div>
			<i class="fusiona-font awb-wizard-hero-icon"></i>
		</div>

		<div class="avada-db-card-notice variant-analysis recommended-notice" data-count="0">
			<i class="fusiona-exclamation-triangle"></i>
			<?php /* translators: Number of font variants */ ?>
			<p class="avada-db-card-notice-heading"><?php printf( esc_html__( 'You currently have %s different font variants. Recommend using fewer than 5 variants if possible.', 'Avada' ), '<span class="variant-count">0</span>' ); ?></p>
		</div>

		<h3 class="wizard-sep-heading"><?php esc_html_e( 'Global Fonts', 'Avada' ); ?></h3>

		<?php
		foreach ( $options as $option ) {
			if ( isset( $option['type'] ) && 'typography' === $option['type'] ) {
				$option_value  = $settings->get( $option['id'] );
				$family_id     = $option['id'] . '[font-family]';
				$family_value  = $settings->get( $option['id'], 'font-family' );
				$variant_id    = $option['id'] . '[font-variant]';
				$style_value   = $settings->get( $option['id'], 'font-style' );
				$weight_value  = $settings->get( $option['id'], 'font-weight' );
				$weight_value  = 'regular' === $weight_value ? '400' : $weight_value;
				$variant_value = ! empty( $style_value ) ? $weight_value . $style_value : $weight_value;
				?>

				<div class="pyre_metabox_field fusion-builder-font-family" data-id="<?php echo esc_attr( $option['id'] ); ?>">
					<div class="pyre_desc">
						<label><?php echo $option['label']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></label>
						<?php if ( ! empty( $option['description'] ) ) : ?>
							<p><?php echo $option['description']; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
						<?php endif; ?>
					</div>
					<div class="pyre_field">
						<div class="awb-typography" data-global="1">
							<div class="input-wrapper family-selection awb-contains-global">
								<div class="awb-typo-heading">
									<label><?php esc_html_e( 'Font Family', 'Avada' ); ?></label>
									<span class="awb-global"><i class="fusiona-globe" aria-hidden="true"></i></span>
								</div>
								<div class="fusion-select-field">
									<div class="fusion-select-preview-wrap">
										<span class="fusion-select-preview">
											<?php
											if ( ! empty( $family_value ) ) {
												echo esc_html( $family_value );
											} elseif ( ! empty( $option['default']['font-family'] ) ) {
												echo esc_html( $option['default']['font-family'] );
											} else {
												echo '<span class="fusion-select-placeholder">' . esc_attr__( 'Select Font Family', 'Avada' ) . '</span>';
											}
											?>
										</span>
										<div class="fusiona-arrow-down"></div>
									</div>
									<div class="fusion-select-dropdown">
										<div class="fusion-select-search">
											<input type="text" class="fusion-hide-from-atts fusion-dont-update" placeholder="<?php esc_attr_e( 'Search Font Families', 'Avada' ); ?>" />
										</div>
										<div class="fusion-select-options"></div>
									</div>
									<input type="hidden" id="<?php echo esc_attr( $family_id ); ?>" name="<?php echo esc_attr( $family_id ); ?>" value="<?php echo esc_attr( $family_value ); ?>" data-default="<?php echo esc_attr( $option['default']['font-family'] ); ?>" class="input-font_family fusion-select-option-value" data-subset="font-family">
								</div>
								<span class="awb-global-label"></span>
							</div>

							<div class="input-wrapper fusion-builder-typography">
								<div class="awb-typo-heading">
									<label><?php esc_html_e( 'Variant', 'Avada' ); ?></label>
								</div>
								<div class="input fusion-typography-select-wrapper">
									<select
										name="<?php echo esc_attr( $variant_id ); ?>"
										class="input-variant variant skip-select2"
										id="<?php echo esc_attr( $variant_id ); ?>"
										data-value="<?php echo esc_attr( $variant_value ); ?>"
										data-default="<?php echo esc_attr( $option['default']['font-weight'] ); ?>"
										data-subset="variant"></select>
									<div class="fusiona-arrow-down"></div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php
			}
		}
		?>

		<h3 class="wizard-sep-heading h-margin-top"><?php esc_html_e( 'Font Serving', 'Avada' ); ?></h3>

		<?php
		echo $metaboxes->radio_buttonset( $options['font_face_display']['id'], $options['font_face_display']['label'], $options['font_face_display']['choices'], $options['font_face_display']['description'], $options['font_face_display']['default'], [], 'swap-all' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<p class="wizard-recommendation"><i class="fusiona-af-rating"></i> ' . esc_html__( 'Recommend setting to swap all for the fastest initial load.', 'Avada' ) . '</p>';

		echo $metaboxes->radio_buttonset( $options['gfonts_load_method']['id'], $options['gfonts_load_method']['label'], $options['gfonts_load_method']['choices'], $options['gfonts_load_method']['description'], $options['gfonts_load_method']['default'], [] );  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<p class="wizard-recommendation"><i class="fusiona-af-rating"></i> ' . esc_html__( 'Recommend setting to local but CDN will also work.', 'Avada' ) . '</p>';

		echo $metaboxes->radio_buttonset( $options['preload_fonts']['id'], $options['preload_fonts']['label'], $options['preload_fonts']['choices'], $options['preload_fonts']['description'], $options['preload_fonts']['default'], [] );  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<p class="wizard-recommendation"><i class="fusiona-af-rating"></i> ' . esc_html__( 'Recommend setting to none unless your largest contentful paint is text or an icon.', 'Avada' ) . '  <a href="https://theme-fusion.com" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Find out how to optimize the largest contentful paint (LCP).', 'Avada' ) . '</a></p>';

		echo $metaboxes->multiple( $options['preload_fonts_variants']['id'], $options['preload_fonts_variants']['label'], $options['preload_fonts_variants']['choices'], $options['preload_fonts_variants']['description'] );  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $metaboxes->multiple( $options['preload_fonts_subsets']['id'], $options['preload_fonts_subsets']['label'], $options['preload_fonts_subsets']['choices'], $options['preload_fonts_subsets']['description'] );  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>

		<div class="awb-wizard-actions">
			<a href="#5" class="button button-secondary awb-wizard-save hidden" data-id="fonts"><i class="awb-wizard-button-icon fusiona-exclamation-sign"></i><?php esc_html_e( 'Save Changes', 'Avada' ); ?></a>
			<a href="#5" class="button button-primary awb-wizard-link awb-wizard-next" data-id="5"><span class="awb-text-wrapper"><span><?php esc_html_e( 'Next Step', 'Avada' ); ?></span><i class="fusiona-arrow-forward"></i></span></a>
		</div>
	</section>

	<section class="avada-db-card hidden awb-wizard-section" data-step="5">
		<div class="awb-wizard-hero">
			<div class="awb-wizard-hero-text">
				<h2><?php esc_html_e( 'Avada Elements', 'Avada' ); ?></h2>
				<p><?php esc_html_e( 'In this step, you can disable any Design, Layout and Form Elements you aren’t using. When disabled, the Elements will not load and will therefore not be available in the Builder. Click the \'Find Recommendations\' button to scan your website and show which Elements you are using.', 'Avada' ); ?></p>
				<?php if ( $posts_count_limit < $posts_count ) : ?>
					<div class="avada-db-card-notice recommended-notice slim-notice notice-red">
						<i class="fusiona-exclamation-triangle"></i>
						<p class="avada-db-card-notice-heading"><?php esc_html_e( 'Warning: Large number of posts detected, scanning might take a while.', 'Avada' ); ?></p>
					</div>
				<?php endif; ?>
				<div class="wizard-multi-buttons">
					<a href="#elementScan" class="button awb-wizard-scan-button" data-id="elements" title="<?php esc_attr_e( 'Find Recommendations', 'Avada' ); ?>"><i class="awb-wizard-button-icon fusiona-search"></i><?php esc_html_e( 'Find Recommendations', 'Avada' ); ?></a>
					<a href="#enableAll" class="button awb-wizard-checkall" data-id="elements" title="<?php esc_attr_e( 'Run Element Scan', 'Avada' ); ?>"><?php esc_html_e( 'Enable All', 'Avada' ); ?></a>
					<a href="#disableAll" class="button awb-wizard-uncheckall" data-id="elements" title="<?php esc_attr_e( 'Run Element Scan', 'Avada' ); ?>"><?php esc_html_e( 'Disable All', 'Avada' ); ?></a>
				</div>
			</div>
			<i class="fusiona-element awb-wizard-hero-icon"></i>
		</div>

		<div class="avada-db-card-notice recommended-notice" data-count="1">
			<i class="fusiona-exclamation-triangle"></i>
			<p class="avada-db-card-notice-heading"><?php esc_html_e( 'Element scan has completed and only used elements have been selected.', 'Avada' ); ?></p>
		</div>

		<div class="fusion-builder-option-field fusion-builder-element-checkboxes">
			<ul class="element-grid">
				<?php
				global $all_fusion_builder_elements;
				$i               = 0;
				$plugin_elements = [
					'fusion_featured_products_slider' => [
						'name'      => esc_html__( 'Woo Featured', 'fusion-builder' ),
						'shortcode' => 'fusion_featured_products_slider',
						'class'     => ( class_exists( 'WooCommerce' ) ) ? '' : 'hidden',
					],
					'fusion_products_slider'          => [
						'name'      => esc_html__( 'Woo Carousel', 'fusion-builder' ),
						'shortcode' => 'fusion_products_slider',
						'class'     => ( class_exists( 'WooCommerce' ) ) ? '' : 'hidden',
					],
					'fusion_woo_shortcodes'           => [
						'name'      => esc_html__( 'Woo Shortcodes', 'fusion-builder' ),
						'shortcode' => 'fusion_woo_shortcodes',
						'class'     => ( class_exists( 'WooCommerce' ) ) ? '' : 'hidden',
					],
					'layerslider'                     => [
						'name'      => esc_html__( 'Layer Slider', 'fusion-builder' ),
						'shortcode' => 'layerslider',
						'class'     => ( defined( 'LS_PLUGIN_BASE' ) ) ? '' : 'hidden',
					],
					'rev_slider'                      => [
						'name'      => esc_html__( 'Slider Revolution', 'fusion-builder' ),
						'shortcode' => 'rev_slider',
						'class'     => ( defined( 'RS_PLUGIN_PATH' ) ) ? '' : 'hidden',
					],
					'fusion_events'                   => [
						'name'      => esc_html__( 'Events', 'fusion-builder' ),
						'shortcode' => 'fusion_events',
						'class'     => ( class_exists( 'Tribe__Events__Main' ) ) ? '' : 'hidden',
					],
					'fusion_fontawesome'              => [
						'name'      => esc_html__( 'Icon', 'fusion-builder' ),
						'shortcode' => 'fusion_fontawesome',
					],
					'fusion_fusionslider'             => [
						'name'      => esc_html__( 'Avada Slider', 'fusion-builder' ),
						'shortcode' => 'fusion_fusionslider',
					],
				];

				$existing_settings           = get_option( 'fusion_builder_settings', [] );
				$all_fusion_builder_elements = array_merge( $all_fusion_builder_elements, apply_filters( 'fusion_builder_plugin_elements', $plugin_elements ) );

				echo '<li class="grid-expanded-li"><h3 class="wizard-sep-heading">' . esc_html__( 'Design Elements', 'fusion-builder' ) . '</h3></li>';

				usort( $all_fusion_builder_elements, 'fusion_element_sort' );
				$form_elements   = [];
				$layout_elements = [];
				foreach ( $all_fusion_builder_elements as $module ) :
					if ( empty( $module['hide_from_builder'] ) ) {
						$i++;
						// Form Components.
						if ( ! empty( $module['form_component'] ) ) {
							$form_elements[ $i ] = $module;
							continue;
						}

						// Layout Componnents.
						if ( ! empty( $module['component'] ) ) {
							$layout_elements[ $i ] = $module;
							continue;
						}

						$checked = '';
						$class   = ( isset( $module['class'] ) && '' !== $module['class'] ) ? $module['class'] : '';

						if ( ( isset( $existing_settings['fusion_elements'] ) && is_array( $existing_settings['fusion_elements'] ) && in_array( $module['shortcode'], $existing_settings['fusion_elements'] ) ) || ( ! isset( $existing_settings['fusion_elements'] ) || ! is_array( $existing_settings['fusion_elements'] ) ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
							$checked = 'checked';
						}
						echo '<li class="' . esc_attr( $class ) . '">';
						echo '<label for="hide_from_builder_' . esc_attr( $i ) . '">';
						echo '<input name="fusion_elements[]" type="checkbox" value="' . esc_attr( $module['shortcode'] ) . '" ' . $checked . ' id="hide_from_builder_' . esc_attr( $i ) . '"/>'; // phpcs:ignore WordPress.Security.EscapeOutput
						echo $module['name'] . '</label>'; // phpcs:ignore WordPress.Security.EscapeOutput
						echo '</li>';
					}
				endforeach;

				// Layout elements output.
				if ( 0 < count( $layout_elements ) ) {
					echo '<li class="grid-expanded-li"><h3 class="wizard-sep-heading h-margin-top">' . esc_html__( 'Layout Elements', 'fusion-builder' ) . '</h3></li>';
				}
				foreach ( $layout_elements as $i => $module ) :
					$checked = '';
					$class   = ( isset( $module['class'] ) && '' !== $module['class'] ) ? $module['class'] : '';

					if ( ( isset( $existing_settings['fusion_elements'] ) && is_array( $existing_settings['fusion_elements'] ) && in_array( $module['shortcode'], $existing_settings['fusion_elements'] ) ) || ( ! isset( $existing_settings['fusion_elements'] ) || ! is_array( $existing_settings['fusion_elements'] ) ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
						$checked = 'checked';
					}
					echo '<li class="' . esc_attr( $class ) . '">';
					echo '<label for="hide_from_builder_' . esc_attr( $i ) . '">';
					echo '<input name="fusion_elements[]" type="checkbox" value="' . esc_attr( $module['shortcode'] ) . '" ' . $checked . ' id="hide_from_builder_' . esc_attr( $i ) . '"/>'; // phpcs:ignore WordPress.Security.EscapeOutput
					echo $module['name'] . '</label>'; // phpcs:ignore WordPress.Security.EscapeOutput
					echo '</li>';
				endforeach;

				// Form elements output.
				if ( 0 < count( $form_elements ) ) {
					echo '<li class="grid-expanded-li"><h3 class="wizard-sep-heading h-margin-top">' . esc_html__( 'Form Elements', 'fusion-builder' ) . '</h3></li>';
				}
				foreach ( $form_elements as $i => $module ) :
					$checked = '';
					$class   = ( isset( $module['class'] ) && '' !== $module['class'] ) ? $module['class'] : '';

					if ( ( isset( $existing_settings['fusion_elements'] ) && is_array( $existing_settings['fusion_elements'] ) && in_array( $module['shortcode'], $existing_settings['fusion_elements'] ) ) || ( ! isset( $existing_settings['fusion_elements'] ) || ! is_array( $existing_settings['fusion_elements'] ) ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
						$checked = 'checked';
					}
					echo '<li class="' . esc_attr( $class ) . '">';
					echo '<label for="hide_from_builder_' . esc_attr( $i ) . '">';
					echo '<input name="fusion_elements[]" type="checkbox" value="' . esc_attr( $module['shortcode'] ) . '" ' . $checked . ' id="hide_from_builder_' . esc_attr( $i ) . '"/>'; // phpcs:ignore WordPress.Security.EscapeOutput
					echo $module['name'] . '</label>'; // phpcs:ignore WordPress.Security.EscapeOutput
					echo '</li>';
				endforeach;


				?>
			</ul>
		</div>

		<div class="avada-db-card-notice awb-wizard-tip">
			<i class="fusiona-info-circle"></i>
			<p class="avada-db-card-notice-heading"><strong><?php esc_html_e( 'Tip', 'Avada' ); ?></strong>: <?php esc_html_e( 'Avoid using complex dynamic elements above the fold. ', 'Avada' ); ?><a href="https://theme-fusion.com/documentation/performance/avada-optimization-guide/" target="_blank" rel="noopener noreferrer"> <?php esc_html_e( 'Find out more optimization tips in our documentation.', 'Avada' ); ?></a></p>
		</div>

		<div class="awb-wizard-actions">
			<a href="#6" class="button button-secondary awb-wizard-save hidden" data-id="elements"><i class="awb-wizard-button-icon fusiona-exclamation-sign"></i><?php esc_html_e( 'Save Changes', 'Avada' ); ?></a>
			<a href="#6" class="button button-primary awb-wizard-link awb-wizard-next" data-id="6"><span class="awb-text-wrapper"><span><?php esc_html_e( 'Next Step', 'Avada' ); ?></span><i class="fusiona-arrow-forward"></i></span></a>
		</div>
	</section>

	<section class="avada-db-card hidden awb-wizard-section" data-step="6">
		<div class="awb-wizard-hero">
			<div class="awb-wizard-hero-text">
				<h2><?php esc_html_e( 'Avada Optimization', 'Avada' ); ?></h2>
				<p><?php esc_html_e( 'In this step, you can optimize how the CSS and JS assets should be enqueued. These options can have a large impact on the performance of the page load, but some of these options can also break functionality if you are using a caching plugin, so proceed with caution.', 'Avada' ); ?></p>
				<div class="wizard-multi-buttons">
					<a href="#optimizeScan" class="button awb-wizard-scan-button" data-id="optimize" title="<?php esc_attr_e( 'Find Recommendations', 'Avada' ); ?>"><i class="awb-wizard-button-icon fusiona-search"></i><?php esc_html_e( 'Find Recommendations', 'Avada' ); ?></a>
					<a href="#applyAll" class="button button-secondary awb-wizard-apply"><?php esc_html_e( 'Apply All', 'Avada' ); ?></a>
				</div>
			</div>
			<i class="fusiona-status awb-wizard-hero-icon"></i>
		</div>

		<h3 class="wizard-sep-heading"><?php esc_html_e( 'Image & Video Optimization', 'fusion-builder' ); ?></h3>
		<?php
		echo $metaboxes->range( $options['pw_jpeg_quality']['id'], $options['pw_jpeg_quality']['label'], $options['pw_jpeg_quality']['description'], $options['pw_jpeg_quality']['choices']['min'], $options['pw_jpeg_quality']['choices']['max'], $options['pw_jpeg_quality']['choices']['step'], $options['pw_jpeg_quality']['default'] );  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
		<p class="wizard-recommendation"><i class="fusiona-af-rating"></i><?php esc_html_e( 'Recommend setting to around 80 to balance file size and quality.', 'Avada' ); ?></p>

		<?php
		echo $metaboxes->range( $options['wp_big_image_size_threshold']['id'], $options['wp_big_image_size_threshold']['label'], $options['wp_big_image_size_threshold']['description'], $options['wp_big_image_size_threshold']['choices']['min'], $options['wp_big_image_size_threshold']['choices']['max'], $options['wp_big_image_size_threshold']['choices']['step'], $options['wp_big_image_size_threshold']['default'] );  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
		<p class="wizard-recommendation"><i class="fusiona-af-rating"></i><?php esc_html_e( 'Recommend setting to around 2560 to balance file size and quality on very large displays.', 'Avada' ); ?></p>

		<?php
		echo $metaboxes->radio_buttonset( $options['lazy_load']['id'], $options['lazy_load']['label'], $options['lazy_load']['choices'], $options['lazy_load']['description'], $options['lazy_load']['default'], [], 'avada' );  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
		<p class="wizard-recommendation"><i class="fusiona-af-rating"></i><?php esc_html_e( 'Recommend setting to Avada. If using a performance plugin, ensure lazy load is disabled there.', 'Avada' ); ?></p>

		<?php
		echo $metaboxes->radio_buttonset( $options['video_facade']['id'], $options['video_facade']['label'], $options['video_facade']['choices'], $options['video_facade']['description'], $options['video_facade']['default'], [] );  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
		<p class="wizard-recommendation"><i class="fusiona-af-rating"></i><?php esc_html_e( 'Recommend turning on if you are using YouTube or Vimeo elements.', 'Avada' ); ?></p>

		<h3 class="wizard-sep-heading h-margin-top"><?php esc_html_e( 'JS Optimization', 'fusion-builder' ); ?></h3>
		<?php
		echo $metaboxes->radio_buttonset( $options['jquery_migrate_disabled']['id'], $options['jquery_migrate_disabled']['label'], $options['jquery_migrate_disabled']['choices'], $options['jquery_migrate_disabled']['description'], $options['jquery_migrate_disabled']['default'], [], 'disabled' );  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
		<p class="wizard-recommendation"><i class="fusiona-af-rating"></i><?php esc_html_e( 'Recommend disabling unless a third party plugin requires it.', 'Avada' ); ?></p>

		<?php
		echo $metaboxes->radio_buttonset( $options['js_compiler']['id'], $options['js_compiler']['label'], $on_off, $options['js_compiler']['description'], $options['js_compiler']['default'], [], '1' );  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
		<p class="wizard-recommendation"><i class="fusiona-af-rating"></i><?php esc_html_e( 'Strongly recommend turning on to compile JS to a single file.', 'Avada' ); ?></p>

		<h3 class="wizard-sep-heading h-margin-top"><?php esc_html_e( 'CSS Optimization', 'fusion-builder' ); ?></h3>
		<?php
		echo $metaboxes->radio_buttonset( $options['defer_styles']['id'], $options['defer_styles']['label'], $on_off, $options['defer_styles']['description'], $options['defer_styles']['default'], [], '0' );  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
		<p class="wizard-recommendation"><i class="fusiona-af-rating"></i><?php esc_html_e( 'Recommend leaving off unless you are using an external critical CSS service.', 'Avada' ); ?></p>

		<?php
		echo $metaboxes->radio_buttonset( $options['media_queries_async']['id'], $options['media_queries_async']['label'], $on_off, $options['media_queries_async']['description'], $options['media_queries_async']['default'], [], '0' );  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
		<p class="wizard-recommendation"><i class="fusiona-af-rating"></i><?php esc_html_e( 'Recommend leaving off. In most cases load times will be faster.', 'Avada' ); ?></p>

		<?php
		echo $metaboxes->radio_buttonset( $options['css_cache_method']['id'], $options['css_cache_method']['label'], $options['css_cache_method']['choices'], $options['css_cache_method']['description'], $options['css_cache_method']['default'], [], 'file' );  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
		<p class="wizard-recommendation"><i class="fusiona-af-rating"></i><?php esc_html_e( 'Strongly recommend setting to file method.', 'Avada' ); ?></p>

		<?php
		echo $metaboxes->radio_buttonset( $options['css_combine_third_party_assets']['id'], $options['css_combine_third_party_assets']['label'], $on_off, $options['css_combine_third_party_assets']['description'], $options['css_combine_third_party_assets']['default'], [], '1' );  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
		<p class="wizard-recommendation"><i class="fusiona-af-rating"></i><?php esc_html_e( 'Recommend enabling.', 'Avada' ); ?></p>

		<h3 class="wizard-sep-heading h-margin-top"><?php esc_html_e( 'Advanced Optimization', 'fusion-builder' ); ?></h3>

		<?php
		$apache_modules        = function_exists( 'apache_get_modules' ) ? apache_get_modules() : [];
		$is_mod_deflate_loaded = is_array( $apache_modules ) && in_array( 'mod_deflate', $apache_modules, true );

		if ( $is_mod_deflate_loaded ) :
			echo $metaboxes->radio_buttonset( $options['gzip_status']['id'], $options['gzip_status']['label'], $on_off, $options['gzip_status']['description'], $options['gzip_status']['default'], [] );  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
		<p class="wizard-recommendation">
		<i class="fusiona-af-rating"></i><?php esc_html_e( 'Recommend leaving off unless your host does not automatically compress.', 'Avada' ); ?>
		</p>
		<?php endif; ?>

		<?php
		echo $metaboxes->radio_buttonset( $options['defer_jquery']['id'], $options['defer_jquery']['label'], $on_off, $options['defer_jquery']['description'], $options['defer_jquery']['default'], [] );  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
		<p class="wizard-recommendation"><i class="fusiona-af-rating"></i><?php esc_html_e( 'Recommend turning on to move jQuery to footer. Warning, if you encounter problems on the front-end then turn off.', 'Avada' ); ?></p>

		<?php
		echo $metaboxes->radio_buttonset( $options['critical_css']['id'], $options['critical_css']['label'], $on_off, $options['critical_css']['description'], $options['critical_css']['default'], [] );  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
		<p class="wizard-recommendation"><i class="fusiona-af-rating"></i><?php esc_html_e( 'Recommend turning on and then generating critical CSS for key pages on the critical CSS page.', 'Avada' ); ?></p>

		<?php
		echo $metaboxes->radio_buttonset( $options['clear_object_cache']['id'], $options['clear_object_cache']['label'], $on_off, $options['clear_object_cache']['description'], $options['clear_object_cache']['default'], [] );  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
		<p class="wizard-recommendation"><i class="fusiona-af-rating"></i><?php esc_html_e( 'Recommend turning on if your host or your caching plugin supports persistant object caching.', 'Avada' ); ?></p>

		<div class="awb-wizard-actions">
			<a href="#7" class="button button-secondary awb-wizard-save hidden" data-id="assets"><i class="awb-wizard-button-icon fusiona-exclamation-sign"></i><?php esc_html_e( 'Save Changes', 'Avada' ); ?></a>
			<a href="#7" class="button button-primary awb-wizard-link awb-wizard-next" data-id="7"><span class="awb-text-wrapper"><span><?php esc_html_e( 'Finish Wizard', 'Avada' ); ?></span><i class="fusiona-arrow-forward"></i></span></a>
		</div>
	</section>

	<section class="avada-db-card hidden awb-wizard-section" data-step="7" data-id="finish">
		<div class="awb-wizard-hero">
			<div class="awb-wizard-hero-text">
				<h2><?php esc_html_e( 'Performance Wizard Complete!', 'Avada' ); ?></h2>
				<p><?php esc_html_e( 'Thank you for completing the Performance Wizard. You should now be set up for fast page load times. Please note though, content, plugins and setup also play a large role. For further hints and tips, please check out our Performance documentation. Some links can be found below.', 'Avada' ); ?></p>
			</div>
			<i class="fusiona-wink awb-wizard-hero-icon"></i>
		</div>

		<div class="awb-wizard-summary">

			<?php if ( $run_lighthouse ) : ?>
			<div class="awb-wizard-score-holder">
				<div class="awb-wizard-scores">
					<div class="awb-before">
						<h3 class="avada-db-setup-step-heading"><?php esc_html_e( 'Before', 'Avada' ); ?></h3>

						<div class="awb-score-before score-fetching lh-gauge__wrapper" data-score="0" data-type="score">
							<div class="lh-gauge__svg-wrapper">
								<svg viewBox="0 0 120 120" class="lh-gauge">
									<circle class="lh-gauge-base" r="56" cx="60" cy="60" stroke-width="8"></circle>
									<circle class="lh-gauge-arc" r="56" cx="60" cy="60" stroke-width="8" style="transform: rotate(-87.9537deg); stroke-dasharray: 0, 360;"></circle>
								</svg>
							</div>
							<div class="lh-gauge__percentage"><i class="fusiona-loop awb-wizard-fetching"></i></div>
							<div class="lh-gauge__label"><?php esc_html_e( 'Score', 'Avada' ); ?></div>
						</div>

						<div class="awb-score-before score-fetching lh-gauge__wrapper" data-score="0" data-type="fcp">
							<div class="lh-gauge__svg-wrapper">
								<svg viewBox="0 0 120 120" class="lh-gauge">
									<circle class="lh-gauge-base" r="56" cx="60" cy="60" stroke-width="8"></circle>
									<circle class="lh-gauge-arc" r="56" cx="60" cy="60" stroke-width="8" style="transform: rotate(-87.9537deg); stroke-dasharray: 0, 360;"></circle>
								</svg>
							</div>
							<div class="lh-gauge__percentage"><i class="fusiona-loop awb-wizard-fetching"></i></div>
							<div class="lh-gauge__label"><?php esc_html_e( 'FCP', 'Avada' ); ?></div>
						</div>

						<div class="awb-score-before score-fetching lh-gauge__wrapper" data-score="0" data-type="lcp">
							<div class="lh-gauge__svg-wrapper">
								<svg viewBox="0 0 120 120" class="lh-gauge">
									<circle class="lh-gauge-base" r="56" cx="60" cy="60" stroke-width="8"></circle>
									<circle class="lh-gauge-arc" r="56" cx="60" cy="60" stroke-width="8" style="transform: rotate(-87.9537deg); stroke-dasharray: 0, 360;"></circle>
								</svg>
							</div>
							<div class="lh-gauge__percentage"><i class="fusiona-loop awb-wizard-fetching"></i></div>
							<div class="lh-gauge__label"><?php esc_html_e( 'LCP', 'Avada' ); ?></div>
						</div>

						<div class="awb-score-before score-fetching lh-gauge__wrapper" data-score="0" data-type="cls">
							<div class="lh-gauge__svg-wrapper">
								<svg viewBox="0 0 120 120" class="lh-gauge">
									<circle class="lh-gauge-base" r="56" cx="60" cy="60" stroke-width="8"></circle>
									<circle class="lh-gauge-arc" r="56" cx="60" cy="60" stroke-width="8" style="transform: rotate(-87.9537deg); stroke-dasharray: 0, 360;"></circle>
								</svg>
							</div>
							<div class="lh-gauge__percentage"><i class="fusiona-loop awb-wizard-fetching"></i></div>
							<div class="lh-gauge__label"><?php esc_html_e( 'CLS', 'Avada' ); ?></div>
						</div>
					</div>

					<div class="awb-after">
						<h3 class="avada-db-setup-step-heading"><?php esc_html_e( 'After', 'Avada' ); ?></h3>

						<div class="awb-score-after lh-gauge__wrapper score-fetching" data-score="0" data-type="score">
							<div class="lh-gauge__svg-wrapper">
								<svg viewBox="0 0 120 120" class="lh-gauge">
									<circle class="lh-gauge-base" r="56" cx="60" cy="60" stroke-width="8"></circle>
									<circle class="lh-gauge-arc" r="56" cx="60" cy="60" stroke-width="8" style="transform: rotate(-87.9537deg); stroke-dasharray: 0, 360;"></circle>
								</svg>
							</div>
							<div class="lh-gauge__percentage"><i class="fusiona-loop awb-wizard-fetching"></i></div>
							<div class="lh-gauge__label"><?php esc_html_e( 'Score', 'Avada' ); ?></div>
						</div>

						<div class="awb-score-after lh-gauge__wrapper score-fetching" data-score="0" data-type="fcp">
							<div class="lh-gauge__svg-wrapper">
								<svg viewBox="0 0 120 120" class="lh-gauge">
									<circle class="lh-gauge-base" r="56" cx="60" cy="60" stroke-width="8"></circle>
									<circle class="lh-gauge-arc" r="56" cx="60" cy="60" stroke-width="8" style="transform: rotate(-87.9537deg); stroke-dasharray: 0, 360;"></circle>
								</svg>
							</div>
							<div class="lh-gauge__percentage"><i class="fusiona-loop awb-wizard-fetching"></i></div>
							<div class="lh-gauge__label"><?php esc_html_e( 'FCP', 'Avada' ); ?></div>
						</div>

						<div class="awb-score-after lh-gauge__wrapper score-fetching" data-score="0" data-type="lcp">
							<div class="lh-gauge__svg-wrapper">
								<svg viewBox="0 0 120 120" class="lh-gauge">
									<circle class="lh-gauge-base" r="56" cx="60" cy="60" stroke-width="8"></circle>
									<circle class="lh-gauge-arc" r="56" cx="60" cy="60" stroke-width="8" style="transform: rotate(-87.9537deg); stroke-dasharray: 0, 360;"></circle>
								</svg>
							</div>
							<div class="lh-gauge__percentage"><i class="fusiona-loop awb-wizard-fetching"></i></div>
							<div class="lh-gauge__label"><?php esc_html_e( 'LCP', 'Avada' ); ?></div>
						</div>

						<div class="awb-score-after lh-gauge__wrapper score-fetching" data-score="0" data-type="cls">
							<div class="lh-gauge__svg-wrapper">
								<svg viewBox="0 0 120 120" class="lh-gauge">
									<circle class="lh-gauge-base" r="56" cx="60" cy="60" stroke-width="8"></circle>
									<circle class="lh-gauge-arc" r="56" cx="60" cy="60" stroke-width="8" style="transform: rotate(-87.9537deg); stroke-dasharray: 0, 360;"></circle>
								</svg>
							</div>
							<div class="lh-gauge__percentage"><i class="fusiona-loop awb-wizard-fetching"></i></div>
							<div class="lh-gauge__label"><?php esc_html_e( 'CLS', 'Avada' ); ?></div>
						</div>

					</div>
				</div>
			</div>
			<?php endif; ?>

			<div class="awb-extra-recommendations">
				<div class="avada-db-card-notice finish-progress">
					<i class="fusiona-info-circle"></i>
					<p class="avada-db-card-notice-heading"><?php esc_html_e( 'Clearing Cache', 'Avada' ); ?></p>
				</div>

				<div class="awb-recommendation-holder">
					<div class="awb-extra-recommendation">
						<?php
						$active_plugins = count( (array) get_option( 'active_plugins' ) );
						$plugin_state   = 'value-good';
						if ( 10 < $active_plugins ) {
							$plugin_state = 'value-bad';
						} elseif ( 5 < $active_plugins ) {
							$plugin_state = 'value-okay';
						}
						$plugin_counter = '<span class="awb-wizard-count ' . $plugin_state . '">' . $active_plugins . '</span>';
						?>
						<?php /* translators: The number of active plugins. */ ?>
						<h3><?php printf( esc_html__( 'Active Plugins: %s', 'Avada' ), $plugin_counter ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h3>
						<p><?php esc_html_e( 'Try to minimize the number of plugins you have. Make sure you really need each plugin you have. Try to use the features already included in Avada rather than extra plugins. For example, use Avada Forms instead of a form plugin. You may also want to try a caching plugin, however if doing so, try to avoid using the same options in multiple places. For example, we suggest using the Avada lazy loading for images and not the lazy loading from plugins.', 'Avada' ); ?></p>
					</div>

					<div class="awb-extra-recommendation awb-improvements">
						<?php
						$active_plugins = count( (array) get_option( 'active_plugins' ) );
						$plugin_state   = 'value-good';
						if ( 10 < $active_plugins ) {
							$plugin_state = 'value-bad';
						} elseif ( 5 < $active_plugins ) {
							$plugin_state = 'value-okay';
						}
						$plugin_counter = '<span class="awb-wizard-count ' . $plugin_state . '">' . $active_plugins . '</span>';
						?>
						<h3><?php esc_html_e( 'Possible Improvements', 'Avada' ); ?></h3>
						<ul class="awb-possible-improvements">
						</ul>
					</div>

				</div>
			</div>
		</div>

		<div class="awb-extra-reading">
			<a href="https://theme-fusion.com/documentation/avada/performance/what-are-google-core-web-vitals-and-why-they-matter/" target="_blank" rel="noopener noreferrer"><i class="fusiona-external-link"></i><?php esc_html_e( 'What are Google core web vitals', 'Avada' ); ?></a>
			<a href="https://theme-fusion.com/documentation/avada/video-tutorials/how-to-optimize-above-the-fold-content-for-performance-video/" target="_blank" rel="noopener noreferrer"><i class="fusiona-external-link"></i><?php esc_html_e( 'How to optimize above the fold content for performance', 'Avada' ); ?></a>
			<a href="https://theme-fusion.com/documentation/avada/media/image-size-guide/" target="_blank" rel="noopener noreferrer"><i class="fusiona-external-link"></i><?php esc_html_e( 'How to optimize the images on your website', 'Avada' ); ?></a>
		</div>
	</section>

	<?php wp_nonce_field( 'awb_performance_nonce', 'awb-performance-nonce' ); ?>
<?php $this->get_admin_screens_footer(); ?>
