<?php

namespace WebpConverter\Settings;

use WebpConverter\Service\OptionsAccessManager;
use WebpConverter\Settings\Option\OptionIntegration;
use WebpConverter\Settings\Option\OptionsAggregator;

/**
 * Allows to integration with plugin settings by providing list of settings fields and saved values.
 */
class PluginOptions {

	/**
	 * @var OptionsAggregator
	 */
	private $options_aggregator;

	public function __construct() {
		$this->options_aggregator = new OptionsAggregator();
	}

	/**
	 * Returns options of plugin settings.
	 *
	 * @param string|null  $form_name       .
	 * @param bool         $is_debug        Is debugging?
	 * @param mixed[]|null $posted_settings Settings submitted in form.
	 *
	 * @return mixed[] Options of plugin settings.
	 */
	public function get_options( string $form_name = null, bool $is_debug = false, array $posted_settings = null ): array {
		$is_save  = ( $posted_settings !== null );
		$settings = ( $is_save ) ? $posted_settings : OptionsAccessManager::get_option( SettingsSave::SETTINGS_OPTION, [] );

		$options = [];
		foreach ( $this->options_aggregator->get_options( $form_name ) as $option_object ) {
			$options[] = ( new OptionIntegration( $option_object ) )->get_option_data( $settings, $is_debug, $is_save );
		}
		return $options;
	}

	/**
	 * Returns values of plugin settings.
	 *
	 * @param string|null  $form_name       .
	 * @param bool         $is_debug        Is debugging?
	 * @param mixed[]|null $posted_settings Settings submitted in form.
	 *
	 * @return mixed[] Values of plugin settings.
	 */
	public function get_values( string $form_name = null, bool $is_debug = false, array $posted_settings = null ): array {
		$values = [];
		foreach ( $this->get_options( $form_name, $is_debug, $posted_settings ) as $option ) {
			$values[ $option['name'] ] = $option['value'];
		}
		return $values;
	}

	/**
	 * Returns values of plugin settings without sensitive data.
	 *
	 * @return mixed[] Values of plugin settings.
	 */
	public function get_public_values(): array {
		$values = [];
		foreach ( $this->get_options() as $option ) {
			$values[ $option['name'] ] = $option['value_public'];
		}
		return $values;
	}
}
