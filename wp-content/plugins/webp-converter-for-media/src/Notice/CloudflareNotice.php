<?php

namespace WebpConverter\Notice;

use WebpConverter\Service\OptionsAccessManager;
use WebpConverter\Settings\Page\PageIntegration;

/**
 * Supports notice asking to clear CDN cache for Cloudflare.
 */
class CloudflareNotice extends NoticeAbstract implements NoticeInterface {

	const NOTICE_OPTION    = 'webpc_notice_cloudflare';
	const NOTICE_VIEW_PATH = 'components/notices/clear-cache.php';

	/**
	 * {@inheritdoc}
	 */
	public function get_option_name(): string {
		return self::NOTICE_OPTION;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_default_value(): string {
		return '';
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_available(): bool {
		$cdn_server = strtolower( $_SERVER['HTTP_CDN_LOOP'] ?? '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		if ( isset( $_SERVER['KINSTA_CACHE_ZONE'] ) ) {
			$cdn_server = '';
		}

		if ( ( strpos( $cdn_server, 'cloudflare' ) === false ) && ! is_plugin_active( 'cloudflare/cloudflare.php' ) ) {
			return false;
		}

		return ( ( $_GET['page'] ?? '' ) === PageIntegration::SETTINGS_MENU_PAGE ); // phpcs:ignore WordPress.Security
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_active(): bool {
		$option_value = OptionsAccessManager::get_option( $this->get_option_name() );
		return ( $option_value !== 'yes' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_disable_value(): string {
		return 'yes';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_output_path(): string {
		return self::NOTICE_VIEW_PATH;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_vars_for_view(): array {
		return [
			'ajax_url'     => admin_url( 'admin-ajax.php' ),
			'close_action' => self::NOTICE_OPTION,
			'service_name' => 'Cloudflare',
			'steps'        => [
				sprintf(
				/* translators: %1$s: service name */
					__( 'Log in to your %1$s dashboard.', 'webp-converter-for-media' ),
					'Cloudflare'
				),
				sprintf(
				/* translators: %1$s: button label */
					__( 'Click %1$s.', 'webp-converter-for-media' ),
					'<strong>"Caching > Configuration"</strong>'
				),
				sprintf(
				/* translators: %1$s: section label, %2$s: button label */
					__( 'Under %1$s, click %2$s. A warning window appears.', 'webp-converter-for-media' ),
					'<strong>"Purge Cache"</strong>',
					'<strong>"Purge Everything"</strong>'
				),
				sprintf(
				/* translators: %1$s: button label */
					__( 'If you agree, click %1$s.', 'webp-converter-for-media' ),
					'<strong>"Purge Everything"</strong>'
				),
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_ajax_action_to_disable(): string {
		return self::NOTICE_OPTION;
	}
}
