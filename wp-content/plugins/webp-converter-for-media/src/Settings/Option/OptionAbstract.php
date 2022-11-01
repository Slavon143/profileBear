<?php

namespace WebpConverter\Settings\Option;

/**
 * Abstract class for class that supports notice displayed in admin panel.
 */
abstract class OptionAbstract implements OptionInterface {

	const OPTION_TYPE_CHECKBOX   = 'checkbox';
	const OPTION_TYPE_RADIO      = 'radio';
	const OPTION_TYPE_QUALITY    = 'quality';
	const OPTION_TYPE_INPUT      = 'input';
	const OPTION_TYPE_TOKEN      = 'token';
	const OPTION_TYPE_IMAGE_SIZE = 'image_size';
	const OPTION_TYPE_TOGGLE     = 'toggle';
	const FORM_TYPE_BASIC        = 'basic';
	const FORM_TYPE_ADVANCED     = 'settings_advanced';
	const FORM_TYPE_CLOUDFLARE   = 'settings_cloudflare';
	const FORM_TYPE_SIDEBAR      = 'settings_sidebar';

	/**
	 * {@inheritdoc}
	 */
	public function get_notice_lines() {
		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_info() {
		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_debug_value( array $settings ) {
		return $this->get_default_value( $settings );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_public_value( $current_value = null ) {
		return $current_value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_disabled_values( array $settings ) {
		return null;
	}
}
