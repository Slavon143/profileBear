<?php

namespace WebpConverter\Settings\Option;

use WebpConverter\Repository\TokenRepository;

/**
 * .
 */
class OptionsAggregator {

	/**
	 * Objects of supported options.
	 *
	 * @var OptionInterface[]
	 */
	private $options = [];

	public function __construct( TokenRepository $token_repository = null ) {
		$token_repository  = $token_repository ?: new TokenRepository();
		$conversion_method = new ConversionMethodOption( $token_repository );

		$this->set_option( new ImagesQualityOption() );
		$this->set_option( new OutputFormatsOption( $token_repository, $conversion_method ) );
		$this->set_option( new SupportedDirectoriesOption() );
		$this->set_option( new ImageResizeOption( $token_repository ) );
		$this->set_option( new AutoConversionOption() );

		$this->set_option( new AccessTokenOption( $token_repository ) );

		$this->set_option( new SupportedExtensionsOption() );
		$this->set_option( $conversion_method );
		$this->set_option( new LoaderTypeOption() );
		$this->set_option( new ExtraFeaturesOption() );
		$this->set_option( new MediaStatsOption() );
	}

	/**
	 * @param string|null $form_name .
	 *
	 * @return OptionInterface[]
	 */
	public function get_options( string $form_name = null ): array {
		$options = [];
		foreach ( $this->options as $option ) {
			if ( ( $form_name === null ) || ( $form_name === $option->get_form_name() ) ) {
				$options[] = $option;
			}
		}

		return apply_filters( 'webpc_settings_options', $options );
	}

	/**
	 * @param string $option_name .
	 *
	 * @return OptionInterface|null
	 */
	public function get_option( string $option_name ) {
		$options = $this->get_options();

		foreach ( $options as $option ) {
			if ( $option->get_name() === $option_name ) {
				return $option;
			}
		}

		return null;
	}

	/**
	 * @param OptionInterface $new_option .
	 *
	 * @return void
	 */
	private function set_option( OptionInterface $new_option ) {
		foreach ( $this->options as $option_index => $option ) {
			if ( $option->get_name() === $new_option->get_name() ) {
				$this->options[ $option_index ] = $new_option;
				return;
			}
		}

		$this->options[] = $new_option;
	}
}
