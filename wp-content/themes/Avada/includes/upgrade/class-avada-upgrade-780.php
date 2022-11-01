<?php
/**
 * Upgrades Handler.
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

/**
 * Handle migrations for Avada 7.8
 *
 * @since 7.8
 */
class Avada_Upgrade_780 extends Avada_Upgrade_Abstract {

	/**
	 * The version.
	 *
	 * @access protected
	 * @since 7.8
	 * @var string
	 */
	protected $version = '7.8.0';

	/**
	 * An array of all available languages.
	 *
	 * @static
	 * @access private
	 * @since 7.7
	 * @var array
	 */
	private static $available_languages = [];

	/**
	 * The actual migration process.
	 *
	 * @access protected
	 * @since 7.8
	 * @return void
	 */
	protected function migration_process() {
		$available_languages       = Fusion_Multilingual::get_available_languages();
		self::$available_languages = ( ! empty( $available_languages ) ) ? $available_languages : [ '' ];

		$this->migrate_options();

		$this->update_forms();
		$this->set_builder_options();
	}

	/**
	 * Migrate options.
	 *
	 * @since 7.8
	 * @access protected
	 */
	protected function migrate_options() {
		$available_langs = self::$available_languages;

		$options = get_option( $this->option_name, [] );
		$options = $this->set_gallery_pagination_options( $options );
		$options = $this->set_page_template( $options );

		update_option( $this->option_name, $options );

		foreach ( $available_langs as $language ) {

			// Skip langs that are already done.
			if ( '' === $language ) {
				continue;
			}

			$options = get_option( $this->option_name . '_' . $language, [] );
			$options = $this->set_gallery_pagination_options( $options );
			$options = $this->set_page_template( $options );

			update_option( $this->option_name . '_' . $language, $options );
		}
	}

	/**
	 * Sets gallery paginations options.
	 *
	 * @param array $options The options.
	 * @since 7.8
	 */
	private function set_gallery_pagination_options( $options ) {
		$options['gallery_limit']                 = '-1';
		$options['gallery_pagination_type']       = 'button';
		$options['gallery_load_more_button_text'] = esc_html__( 'Load More', 'fusion-builder' );

		return $options;
	}

	/**
	 * Sets Page Template.
	 *
	 * @param array $options The options.
	 * @since 7.8
	 */
	private function set_page_template( $options ) {
		$options['page_template'] = 'site_width';

		return $options;
	}

	/**
	 * Update Form Options.
	 *
	 * @access private
	 * @since 7.8
	 */
	private function update_forms() {
		$args = [
			'posts_per_page' => 20,
			'post_type'      => 'fusion_form',
			'post_status'    => 'publish',
			'paged'          => 1,
		];

		// fix_for_patch_420551.
		$applied_patches = get_site_option( 'fusion_applied_patches', [] );
		$patch_applied   = ( in_array( 420551, $applied_patches, true ) );
		$release_date_77 = '2022-04-12';

		$posts = get_posts( $args );
		while ( $posts ) {

			foreach ( $posts as $post ) {
				$form_meta = get_post_meta( $post->ID, '_fusion', true );
				if ( ! is_array( $form_meta ) ) {
					$form_meta = [];
				}
				if ( isset( $form_meta['avada_78_upgrade'] ) ) {
					continue;
				}

				// if form type value is 'default' change it to 'post'.
				if ( isset( $form_meta['form_type'] ) && 'default' === $form_meta['form_type'] ) {
					$form_meta['form_type'] = 'post';
				}

				// If using post method and form Submission URL in URL action options has a value use this value as a post url.
				if ( 'post' === $form_meta['form_type'] && ! empty( $form_meta['action'] ) && empty( $form_meta['post_method_url'] ) ) {
					$form_meta['post_method_url'] = $form_meta['action'];
				}

				// Only if patch applied and the fix not applied before.
				if ( $patch_applied ) {
					if ( empty( $form_meta['avada_771_fix_upgrade'] ) ) {
						$form_publish_date = get_the_date( 'Y-m-d', $post->ID );

						// Apply only if form publish date after the release of 7.7 update.
						if ( $form_publish_date > $release_date_77 ) {
							if ( ! isset( $form_meta['form_actions'] ) ) {
								$form_meta['form_actions'] = [ 'database' ];
							}
							$form_meta['avada_771_fix_upgrade'] = true;
						}
					}
				}

				$form_meta['avada_78_upgrade'] = true;

				update_post_meta( $post->ID, '_fusion', $form_meta );
			}

			$args['paged']++;
			$posts = get_posts( $args );
		}
	}

	/**
	 * Sets builder options.
	 *
	 * @return void
	 */
	private function set_builder_options() {
		$options = get_option( 'fusion_builder_settings', [] );

		if ( isset( $options['enable_builder_ui_by_default'] ) ) {
			$options['enable_builder_ui_by_default'] = 'backend';
		}

		$options['remove_empty_attributes'] = 'off';

		update_option( 'fusion_builder_settings', $options );
	}

}
