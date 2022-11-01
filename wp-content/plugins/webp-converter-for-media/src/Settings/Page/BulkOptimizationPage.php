<?php


namespace WebpConverter\Settings\Page;

/**
 * {@inheritdoc}
 */
class BulkOptimizationPage extends GeneralSettingsPage {

	/**
	 * {@inheritdoc}
	 */
	public function get_slug() {
		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_menu_parent(): string {
		return PageIntegration::UPLOAD_MENU_PAGE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_menu_url() {
		if ( ( $_GET['page'] ?? '' ) !== PageIntegration::UPLOAD_MENU_PAGE ) { // phpcs:ignore WordPress.Security
			return null;
		}

		return admin_url( 'upload.php?page=' . PageIntegration::UPLOAD_MENU_PAGE );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label(): string {
		return __( 'Bulk Optimization', 'webp-converter-for-media' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_template_vars(): array {
		return array_merge(
			parent::get_template_vars(),
			[
				'form_options'     => null,
				'form_input_value' => null,
			]
		);
	}
}
