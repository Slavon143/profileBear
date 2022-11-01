<?php

namespace WebpConverter\Loader;

use WebpConverter\Conversion\Format\FormatFactory;
use WebpConverter\PluginData;
use WebpConverter\PluginInfo;

/**
 * Abstract class for class that supports method of loading images.
 */
abstract class LoaderAbstract implements LoaderInterface {

	const ACTION_NAME = 'webpc_refresh_loader';

	/**
	 * @var PluginInfo
	 */
	protected $plugin_info;

	/**
	 * @var PluginData
	 */
	protected $plugin_data;

	/**
	 * @var FormatFactory
	 */
	protected $format_factory;

	public function __construct( PluginInfo $plugin_info, PluginData $plugin_data, FormatFactory $format_factory = null ) {
		$this->plugin_info    = $plugin_info;
		$this->plugin_data    = $plugin_data;
		$this->format_factory = $format_factory ?: new FormatFactory();
	}

	/**
	 * {@inheritdoc}
	 */
	public function init_hooks() {
	}
}
