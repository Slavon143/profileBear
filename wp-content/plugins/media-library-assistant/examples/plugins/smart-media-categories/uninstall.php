<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package   Smart_Media_Categories
 * @author    David Lingren <david@davidlingren.com>
 * @license   GPL-2.0+
 * @link      http://davidlingren.com
 * @copyright 2014 David Lingren
 */

// If uninstall not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// @TODO: Define uninstall functionality here