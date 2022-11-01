<?php

namespace WebpConverter\Error\Notice;

/**
 * {@inheritdoc}
 */
class RewritesNotWorkingNotice implements ErrorNotice {

	const ERROR_KEY = 'rewrites_not_working';

	/**
	 * {@inheritdoc}
	 */
	public function get_key(): string {
		return self::ERROR_KEY;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_message(): array {
		return [
			sprintf(
			/* translators: %1$s: open strong tag, %2$s: close strong tag */
				__( 'Redirects on your server are not working. Check the correct configuration for you in %1$sthe plugin FAQ%2$s. If you have checked the configuration, it means that your server does not support redirects from the .htaccess file or your server configuration is not compatible with this plugin.', 'webp-converter-for-media' ),
				'<a href="https://url.mattplugins.com/converter-error-rewrites-not-working-faq" target="_blank">',
				'</a>'
			),
			__( 'In this case, please contact your server administrator.', 'webp-converter-for-media' ),
		];
	}
}
