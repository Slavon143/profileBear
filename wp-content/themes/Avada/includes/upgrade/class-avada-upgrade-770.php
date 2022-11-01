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
 * Handle migrations for Avada 7.7
 *
 * @since 7.7
 */
class Avada_Upgrade_770 extends Avada_Upgrade_Abstract {

	/**
	 * The version.
	 *
	 * @access protected
	 * @since 7.7
	 * @var string
	 */
	protected $version = '7.7.0';

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
	 * @since 7.7
	 * @return void
	 */
	protected function migration_process() {
		$available_languages       = Fusion_Multilingual::get_available_languages();
		self::$available_languages = ( ! empty( $available_languages ) ) ? $available_languages : [ '' ];

		$this->migrate_options();
		$this->update_forms();
	}

	/**
	 * Migrate options.
	 *
	 * @since 7.7
	 * @access protected
	 */
	protected function migrate_options() {
		$available_langs = self::$available_languages;

		$options = get_option( $this->option_name, [] );
		$options = $this->migrate_faq_options( $options );
		$options = $this->migrate_title_options( $options );
		$options = $this->migrate_fav_icon_options( $options );

		update_option( $this->option_name, $options );

		foreach ( $available_langs as $language ) {

			// Skip langs that are already done.
			if ( '' === $language ) {
				continue;
			}

			$options = get_option( $this->option_name . '_' . $language, [] );
			$options = $this->migrate_faq_options( $options );
			$options = $this->migrate_title_options( $options );
			$options = $this->migrate_fav_icon_options( $options );

			update_option( $this->option_name . '_' . $language, $options );
		}
	}

	/**
	 * Sets FAQs element options.
	 *
	 * @access private
	 * @since 7.7
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function migrate_faq_options( $options ) {

		if ( isset( $options['sep_color'] ) ) {
			$options['faq_accordion_divider_color']       = $options['sep_color'];
			$options['faq_accordion_divider_hover_color'] = $options['sep_color'];
		}

		if ( isset( $options['h4_typography'] ) ) {
			$options['faq_accordion_title_typography'] = [
				'font-family' => $options['h4_typography']['font-family'],
				'font-weight' => $options['h4_typography']['font-weight'],
			];
		}

		if ( isset( $options['link_color'] ) ) {
			$options['faq_accordion_title_typography']['color'] = $options['link_color'];
		}

		if ( isset( $options['faq_accordion_title_font_size'] ) ) {
			$options['faq_accordion_title_typography']['font-size'] = $options['faq_accordion_title_font_size'];
			unset( $options['faq_accordion_title_font_size'] );
		}

		if ( isset( $options['body_typography'] ) ) {
			$options['faq_accordion_content_typography'] = [
				'font-family' => $options['body_typography']['font-family'],
				'font-weight' => $options['body_typography']['font-weight'],
				'font-size'   => $options['body_typography']['font-size'],
				'color'       => $options['body_typography']['color'],
			];
		}

		$options['faq_accordian_active_accent_color'] = '';

		return $options;
	}

	/**
	 * Sets title element options.
	 *
	 * @access private
	 * @since 7.7
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function migrate_title_options( $options ) {
		if ( isset( $options['title_text_transform'] ) && 'none' === $options['title_text_transform'] ) {
			$options['title_text_transform'] = '';
		}

		return $options;
	}

	/**
	 * Migrates fav icon options.
	 *
	 * @access private
	 * @since 7.7
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function migrate_fav_icon_options( $options ) {
		if ( isset( $options['favicon'] ) ) {
			$options['fav_icon'] = $options['favicon'];
			unset( $options['favicon'] );
		}
		if ( isset( $options['iphone_icon_retina'] ) ) {
			$options['fav_icon_apple_touch'] = $options['iphone_icon_retina'];
			unset( $options['iphone_icon_retina'] );
		}
		if ( isset( $options['iphone_icon'] ) ) {
			$options['fav_icon_android'] = $options['iphone_icon'];
			unset( $options['iphone_icon'] );
		}
		if ( isset( $options['ipad_icon'] ) ) {
			$options['fav_icon_edge'] = $options['ipad_icon'];
			unset( $options['ipad_icon'] );
		}
		if ( isset( $options['ipad_icon_retina'] ) ) {
			unset( $options['ipad_icon_retina'] );
		}

		return $options;
	}

	/**
	 * Update Form Options.
	 *
	 * @access private
	 * @since 7.7
	 */
	private function update_forms() {
		$args = [
			'posts_per_page' => 20,
			'post_type'      => 'fusion_form',
			'post_status'    => 'publish',
			'paged'          => 1,
		];

		$posts = get_posts( $args );
		while ( $posts ) {

			foreach ( $posts as $post ) {
				$form_meta = get_post_meta( $post->ID, '_fusion', true );
				if ( ! is_array( $form_meta ) ) {
					$form_meta = [];
				}
				if ( isset( $form_meta['avada_77_upgrade'] ) ) {
					continue;
				}

				// Get default notifications.
				$default_notification = [ 'label' => __( 'Admin Notification', 'Avada' ) ];
				if ( ! empty( $form_meta['email'] ) ) {
					$default_notification['email'] = $form_meta['email'];
				}

				if ( ! empty( $form_meta['email_subject'] ) ) {
					$default_notification['email_subject'] = $form_meta['email_subject'];
				}

				if ( ! empty( $form_meta['email_subject_encode'] ) ) {
					$default_notification['email_subject_encode'] = $form_meta['email_subject_encode'];
				}

				if ( ! empty( $form_meta['email_from'] ) ) {
					$default_notification['email_from'] = $form_meta['email_from'];
				}

				if ( ! empty( $form_meta['email_from_id'] ) ) {
					$default_notification['email_from_id'] = $form_meta['email_from_id'];
				}

				if ( ! empty( $form_meta['email_reply_to'] ) ) {
					$default_notification['email_reply_to'] = $form_meta['email_reply_to'];
				}

				if ( ! empty( $form_meta['email_attachments'] ) ) {
					$default_notification['email_attachments'] = $form_meta['email_attachments'];
				}
				$default_notifications = [ $default_notification ];
				$default_notifications = wp_json_encode( $default_notifications );

				$actions = [];
				if ( isset( $form_meta['form_type'] ) ) {
					if ( 'default' !== $form_meta['form_type'] ) {
						if ( 'database' === $form_meta['form_type'] || 'database_email' === $form_meta['form_type'] ) {
							$actions[] = 'database';
							if ( ! isset( $form_meta['notifications'] ) ) {
								$form_meta['notifications'] = $default_notifications;
							}
						}
						if ( 'email' === $form_meta['form_type'] && ! isset( $form_meta['notifications'] ) ) {
							$form_meta['notifications'] = $default_notifications;
						}
						if ( 'url' === $form_meta['form_type'] ) {
							$actions[] = 'url';
						}
						$form_meta['form_type'] = 'ajax';
					}
				} else {
					$form_meta['form_type'] = 'ajax';
					$actions[]              = 'database';
				}

				if ( isset( $form_meta['mailchimp_action'] ) && 'contact' === $form_meta['mailchimp_action'] ) {
					$actions[] = 'mailchimp';
				}
				if ( isset( $form_meta['hubspot_action'] ) && 'contact' === $form_meta['hubspot_action'] ) {
					$actions[] = 'hubspot';
				}

				if ( ! empty( $actions ) ) {
					$form_meta['form_actions'] = $actions;
				}
				$form_meta['avada_77_upgrade'] = true;

				update_post_meta( $post->ID, '_fusion', $form_meta );
			}

			$args['paged']++;
			$posts = get_posts( $args );
		}
	}

}
