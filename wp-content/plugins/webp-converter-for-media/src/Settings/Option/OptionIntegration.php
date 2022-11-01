<?php

namespace WebpConverter\Settings\Option;

/**
 * Allows to integrate with field in plugin settings by specifying its settings and value.
 */
class OptionIntegration {

	/**
	 * Objects of supported settings options.
	 *
	 * @var OptionInterface
	 */
	private $option;

	/**
	 * @param OptionInterface $option .
	 */
	public function __construct( OptionInterface $option ) {
		$this->option = $option;
	}

	/**
	 * Returns data of option based on plugin settings.
	 *
	 * @param mixed[] $settings Plugin settings.
	 * @param bool    $is_debug Is debugging?
	 * @param bool    $is_save  Is saving?
	 *
	 * @return mixed[] Data of option.
	 */
	public function get_option_data( array $settings, bool $is_debug, bool $is_save ): array {
		$option_name     = $this->option->get_name();
		$option_type     = $this->option->get_type();
		$values          = $this->option->get_available_values( $settings );
		$disabled_values = $this->option->get_disabled_values( $settings );

		if ( $is_debug ) {
			$value = $this->option->get_debug_value( $settings );
		} else {
			$value = ( isset( $settings[ $option_name ] ) || $is_save )
				? $this->option->get_valid_value( $settings[ $option_name ] ?? null, $values, $disabled_values )
				: null;
		}

		$value = ( $value !== null ) ? $value : $this->option->get_default_value( $settings );
		return [
			'name'         => $this->option->get_name(),
			'type'         => $option_type,
			'label'        => $this->option->get_label(),
			'notice_lines' => $this->option->get_notice_lines(),
			'info'         => $this->option->get_info(),
			'values'       => $values,
			'disabled'     => $disabled_values ?: [],
			'value'        => $value,
			'value_public' => $this->option->get_public_value( $value ),
		];
	}
}
