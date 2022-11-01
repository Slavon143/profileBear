<?php

namespace WebpConverter\Settings\Option;

/**
 * Interface for class that supports data field in plugin settings.
 */
interface OptionInterface {

	/**
	 * Returns name of option.
	 *
	 * @return string
	 */
	public function get_name(): string;

	/**
	 * @return string
	 */
	public function get_form_name(): string;

	/**
	 * Returns type of field.
	 *
	 * @return string
	 */
	public function get_type(): string;

	/**
	 * Returns label of option.
	 *
	 * @return string
	 */
	public function get_label(): string;

	/**
	 * @return string[]|null
	 */
	public function get_notice_lines();

	/**
	 * Returns additional information of field.
	 *
	 * @return string|null
	 */
	public function get_info();

	/**
	 * @param mixed[] $settings Plugin settings.
	 *
	 * @return string[]|null
	 */
	public function get_available_values( array $settings );

	/**
	 * @param mixed[] $settings Plugin settings.
	 *
	 * @return string[]|null
	 */
	public function get_disabled_values( array $settings );

	/**
	 * Returns verified value of field.
	 *
	 * @param mixed|null    $current_value    .
	 * @param string[]|null $available_values .
	 * @param string[]|null $disabled_values  .
	 *
	 * @return mixed|null
	 */
	public function get_valid_value( $current_value, array $available_values = null, array $disabled_values = null );

	/**
	 * Returns default value of field.
	 *
	 * @param mixed[]|null $settings Plugin settings.
	 *
	 * @return string|string[]
	 */
	public function get_default_value( array $settings = null );

	/**
	 * Returns value of field without sensitive data.
	 *
	 * @param mixed|null $current_value .
	 *
	 * @return mixed|null
	 */
	public function get_public_value( $current_value = null );

	/**
	 * Returns default value of field when debugging.
	 *
	 * @param mixed[] $settings Plugin settings.
	 *
	 * @return string|string[]
	 */
	public function get_debug_value( array $settings );
}
