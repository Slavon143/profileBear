<?php

namespace WebpConverter\Error\Notice;

/**
 * {@inheritdoc}
 */
class BypassingApacheNotice implements ErrorNotice {

	const ERROR_KEY = 'bypassing_apache';

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
				__( 'Requests to images are processed by Nginx server bypassing Apache. Please log in to your hosting control panel, go to management of this website and try to find one of the following settings %1$sand disable it if it is active%2$s:', 'webp-converter-for-media' ),
				'<strong>',
				'</strong>'
			),
			implode(
				'<br>',
				[
					sprintf(
					/* translators: %1$s: button label, %2$s: tab name, %3$s: tab name, %4$s: section name */
						__( '- for SiteGround hosting click %1$s button on the websites list -> click %2$s and %3$s tab -> find %4$s section', 'webp-converter-for-media' ),
						'"Site Tools"',
						'"Speed"',
						'"Caching"',
						'"Nginx Direct Delivery"'
					),
					sprintf(
					/* translators: %1$s: setting name, %2$s: setting name */
						__( '- %1$s, %2$s or similar in the section related to Speed or Caching', 'webp-converter-for-media' ),
						'"Nginx Direct Delivery"',
						'"Nginx Caching"'
					),
					sprintf(
					/* translators: %s: setting name */
						__( '- %s or similar in the section related to Apache and Nginx configuration', 'webp-converter-for-media' ),
						'"Smart static files processing", "Serve static files directly by Nginx"'
					),
				]
			),
			sprintf(
			/* translators: %1$s: open strong tag, %2$s: close strong tag, %3$s: break line tag, %4$s: setting name, %5$s: setting name */
				__( 'In case of problems with finding such settings, %1$splease contact your hosting support and send them the following message%2$s: %3$s"I would like to disable %4$s (or %5$s) for static content files like .jpg, .jpeg, .png, .gif and .webp. These files should have been handled by Apache server instead of Nginx. I need help on this matter."', 'webp-converter-for-media' ),
				'<strong>',
				'</strong>',
				'<br>',
				'Nginx Caching',
				'Nginx Direct Delivery'
			),
		];
	}
}
