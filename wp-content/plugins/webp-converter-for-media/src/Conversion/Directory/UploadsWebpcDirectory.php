<?php

namespace WebpConverter\Conversion\Directory;

/**
 * Supports data about /uploads-webpc directory.
 */
class UploadsWebpcDirectory extends DirectoryAbstract {

	const DIRECTORY_TYPE = 'webp';
	const DIRECTORY_PATH = '%s/uploads-webpc';

	/**
	 * {@inheritdoc}
	 */
	public function get_type(): string {
		return self::DIRECTORY_TYPE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_available(): bool {
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_output_directory(): bool {
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_relative_path(): string {
		return sprintf( self::DIRECTORY_PATH, basename( WP_CONTENT_DIR ) );
	}
}
