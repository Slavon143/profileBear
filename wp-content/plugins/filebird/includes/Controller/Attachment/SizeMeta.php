<?php

namespace FileBird\Controller\Attachment;

class SizeMeta {
    private static $instance = null;
    public $meta_key = 'fb_filesize';

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}