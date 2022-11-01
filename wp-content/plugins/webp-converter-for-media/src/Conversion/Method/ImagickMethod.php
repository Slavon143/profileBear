<?php

namespace WebpConverter\Conversion\Method;

use WebpConverter\Conversion\Format\WebpFormat;
use WebpConverter\Exception\ConversionErrorException;
use WebpConverter\Exception\ExtensionUnsupportedException;
use WebpConverter\Exception\ImageAnimatedException;
use WebpConverter\Exception\ImageInvalidException;
use WebpConverter\Exception\ImagickNotSupportWebpException;
use WebpConverter\Exception\ImagickUnavailableException;
use WebpConverter\Settings\Option\ExtraFeaturesOption;
use WebpConverter\Settings\Option\ImagesQualityOption;
use WebpConverter\Settings\Option\SupportedExtensionsOption;

/**
 * Supports image conversion method using Imagick library.
 */
class ImagickMethod extends LibraryMethodAbstract {

	const METHOD_NAME              = 'imagick';
	const MAX_METHOD_QUALITY       = 99.9;
	const PROTECTED_IMAGE_PROFILES = [
		'icc',
		'icm',
	];

	/**
	 * {@inheritdoc}
	 */
	public function get_name(): string {
		return self::METHOD_NAME;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label(): string {
		return 'Imagick';
	}

	/**
	 * {@inheritdoc}
	 */
	public static function is_method_installed(): bool {
		return ( extension_loaded( 'imagick' ) && class_exists( '\Imagick' ) );
	}

	/**
	 * {@inheritdoc}
	 */
	public static function is_method_active( string $format ): bool {
		if ( ! self::is_method_installed()
			|| ! ( $formats = ( new \Imagick() )->queryformats( 'WEBP' ) )
			|| ! ( $extension = self::get_format_extension( $format ) ) ) {
			return false;
		}
		return in_array( $extension, $formats );
	}

	/**
	 * Returns name of supported format to convert source image to output image.
	 *
	 * @param string $format Extension of output format.
	 *
	 * @return string|null Supported format using for conversion.
	 */
	private static function get_format_extension( string $format ) {
		switch ( $format ) {
			case WebpFormat::FORMAT_EXTENSION:
				return 'WEBP';
			default:
				return null;
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return \Imagick .
	 * @throws ExtensionUnsupportedException
	 * @throws ImagickUnavailableException
	 * @throws ImageInvalidException
	 * @throws ImageAnimatedException
	 */
	public function create_image_by_path( string $source_path, array $plugin_settings ) {
		$extension = strtolower( pathinfo( $source_path, PATHINFO_EXTENSION ) );

		if ( ! extension_loaded( 'imagick' ) || ! class_exists( 'Imagick' ) ) {
			throw new ImagickUnavailableException();
		} elseif ( ! in_array( $extension, $plugin_settings[ SupportedExtensionsOption::OPTION_NAME ] ) ) {
			throw new ExtensionUnsupportedException( [ $extension, $source_path ] );
		}

		try {
			$imagick = new \Imagick( $source_path );
			if ( ( $extension === 'gif' ) && ( $imagick->identifyFormat( '%n' ) > 1 ) ) {
				throw new ImageAnimatedException( $source_path );
			}
			return $imagick;
		} catch ( \ImagickException $e ) {
			throw new ImageInvalidException( $source_path );
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws ConversionErrorException
	 * @throws ImagickNotSupportWebpException
	 */
	public function convert_image_to_output( $image, string $source_path, string $output_path, string $format, array $plugin_settings ) {
		$extension      = self::get_format_extension( $format );
		$image          = apply_filters( 'webpc_imagick_before_saving', $image, $source_path );
		$output_quality = min( $plugin_settings[ ImagesQualityOption::OPTION_NAME ], self::MAX_METHOD_QUALITY );

		if ( ! in_array( $extension, $image->queryFormats() ) ) {
			throw new ImagickNotSupportWebpException();
		}

		$image->setImageFormat( $extension );
		if ( ! in_array( ExtraFeaturesOption::OPTION_VALUE_KEEP_METADATA, $plugin_settings[ ExtraFeaturesOption::OPTION_NAME ] ) ) {
			$image_profiles = $image->getImageProfiles( '*', true );
			foreach ( $image_profiles as $profile_name => $image_profile ) {
				if ( ! in_array( $profile_name, self::PROTECTED_IMAGE_PROFILES ) ) {
					try {
						$image->removeImageProfile( $profile_name );
					} catch ( \ImagickException $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
					}
				}
			}
		}
		$image->setImageCompressionQuality( $output_quality );
		$blob = $image->getImageBlob();

		if ( ! file_put_contents( $output_path, $blob ) ) {
			throw new ConversionErrorException( $source_path );
		}
	}
}
