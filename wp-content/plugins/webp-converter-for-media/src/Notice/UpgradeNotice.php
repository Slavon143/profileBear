<?php

namespace WebpConverter\Notice;

use WebpConverter\PluginData;
use WebpConverter\Service\OptionsAccessManager;
use WebpConverter\Settings\Option\AccessTokenOption;

/**
 * Supports notice displayed information about the PRO version.
 */
class UpgradeNotice extends NoticeAbstract implements NoticeInterface {

	const NOTICE_OPTION    = 'webpc_notice_upgrade';
	const NOTICE_VIEW_PATH = 'components/notices/upgrade.php';

	/**
	 * @var PluginData
	 */
	private $plugin_data;

	public function __construct( PluginData $plugin_data ) {
		$this->plugin_data = $plugin_data;
	}

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
		return (string) strtotime( '+ 1 week' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_available(): bool {
		return ( basename( $_SERVER['PHP_SELF'] ) === 'index.php' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_active(): bool {
		$plugin_settings = $this->plugin_data->get_plugin_settings();
		if ( $plugin_settings[ AccessTokenOption::OPTION_NAME ] ) {
			return false;
		}

		$option_value = OptionsAccessManager::get_option( $this->get_option_name() );
		return ( ( $option_value !== null ) && ( $option_value < time() ) );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_disable_value(): string {
		$is_permanent = ( isset( $_REQUEST['is_permanently'] ) && $_REQUEST['is_permanently'] ); // phpcs:ignore
		return (string) strtotime( ( $is_permanent ) ? '+1 year' : '+ 1 month' );
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
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_ajax_action_to_disable(): string {
		return self::NOTICE_OPTION;
	}
}
