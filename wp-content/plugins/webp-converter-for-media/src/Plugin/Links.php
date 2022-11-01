<?php

namespace WebpConverter\Plugin;

use WebpConverter\HookableInterface;
use WebpConverter\PluginInfo;
use WebpConverter\Repository\TokenRepository;
use WebpConverter\Settings\Page\PageIntegration;

/**
 * Adds links to plugin in list of plugins in panel.
 */
class Links implements HookableInterface {

	/**
	 * @var PluginInfo
	 */
	private $plugin_info;

	/**
	 * @var TokenRepository
	 */
	private $token_repository;

	public function __construct( PluginInfo $plugin_info, TokenRepository $token_repository ) {
		$this->plugin_info      = $plugin_info;
		$this->token_repository = $token_repository;
	}

	/**
	 * {@inheritdoc}
	 */
	public function init_hooks() {
		add_filter( 'plugin_action_links_' . $this->plugin_info->get_plugin_basename(), [ $this, 'add_plugin_links_for_admin' ] );
		add_filter( 'network_admin_plugin_action_links_' . $this->plugin_info->get_plugin_basename(), [ $this, 'add_plugin_links_for_network' ] );
	}

	/**
	 * Adds new links to list of plugin actions for non-multisite websites.
	 *
	 * @param string[] $links Plugin action links.
	 *
	 * @return string[] Plugin action links.
	 * @internal
	 */
	public function add_plugin_links_for_admin( array $links ): array {
		if ( is_multisite() ) {
			return $links;
		}

		$links = $this->add_link_to_settings( $links );
		return $this->add_link_to_pro_upgrade( $links );
	}

	/**
	 * Adds new links to list of plugin actions for multisite websites.
	 *
	 * @param string[] $links Plugin action links.
	 *
	 * @return string[] Plugin action links.
	 * @internal
	 */
	public function add_plugin_links_for_network( array $links ): array {
		$links = $this->add_link_to_settings( $links );
		return $this->add_link_to_pro_upgrade( $links );
	}

	/**
	 * @param string[] $links Plugin action links.
	 *
	 * @return string[] Plugin action links.
	 */
	private function add_link_to_settings( array $links ): array {
		array_unshift(
			$links,
			sprintf(
			/* translators: %1$s: open anchor tag, %2$s: close anchor tag */
				esc_html( __( '%1$sSettings%2$s', 'webp-converter-for-media' ) ),
				'<a href="' . PageIntegration::get_settings_page_url() . '">',
				'</a>'
			)
		);
		return $links;
	}

	/**
	 * @param string[] $links Plugin action links.
	 *
	 * @return string[] Plugin action links.
	 * @internal
	 */
	private function add_link_to_pro_upgrade( array $links ): array {
		if ( $this->token_repository->get_token()->get_valid_status() ) {
			return $links;
		}

		$links[] = sprintf(
		/* translators: %1$s: open anchor tag, %2$s: close anchor tag */
			esc_html( __( '%1$sUpgrade to PRO%2$s', 'webp-converter-for-media' ) ),
			'<a href="https://url.mattplugins.com/converter-plugin-links-upgrade" target="_blank" style="font-weight: bold;">',
			'</a>'
		);
		return $links;
	}
}
