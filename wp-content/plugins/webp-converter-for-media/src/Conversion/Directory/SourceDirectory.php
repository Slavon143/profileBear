<?php

namespace WebpConverter\Conversion\Directory;

/**
 * Supports data about source directory.
 */
class SourceDirectory extends DirectoryAbstract {

	/**
	 * @var string
	 */
	private $directory_type;

	/**
	 * @var string
	 */
	private $directory_path;

	/**
	 * @var bool
	 */
	private $is_always_available;

	public function __construct( string $directory_name, bool $is_always_available = false ) {
		$this->directory_type      = trim( $directory_name, '/\\' );
		$this->directory_path      = '%s/' . $this->directory_type;
		$this->is_always_available = $is_always_available;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_type(): string {
		return $this->directory_type;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label(): string {
		return '/' . $this->directory_type;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_relative_path(): string {
		return sprintf( $this->directory_path, basename( WP_CONTENT_DIR ) );
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_available(): bool {
		if ( $this->is_always_available ) {
			return true;
		}
		return parent::is_available();
	}
}
