<?php
/**
 * Downloads gallery items as a ZIP archive
 *
 * Generates a hyperlink that, when clicked, assembles and downloads a ZIP archive containing
 * the attached files for the gallery items. All of the [mla_gallery] data selection parameters
 * are accepted by this plugin and passed to the MLAShortcode_Support::mla_get_shortcode_attachments()
 * function to populate the "gallery".
 *
 * Example: [mla_download_archive archive_name="My Archive" attachment_category=abc]
 *
 * Shortcode parameters for this plugin:
 *
 * archive_name - REQUIRED; prefixes the file name for the ZIP archive.
 *
 * link_attributes - adds one or more HTML attributes to the hyperlink.
 *
 * link_class - adds an HTML "class" attribute to the hyperlink.
 *
 * link_text - replaces the text displayed for the hyperlink. Default is "Download" followed by the archive_name.
 *
 * allow_empty_archive - 'true' to allow download of an empty ZIP archive, 'false' to display nolink_text; default 'true'.
 *
 * nolink_text - replaces the hyperlink with a text message for an empty "gallery".
 *
 * 
 * Created for support topic "Download of a Gallery"
 * opened on 6/7/2020 by "ernstwg"
 * https://wordpress.org/support/topic/download-of-a-gallery/
 *
 * @package MLA Gallery Download Archive
 * @version 1.02
 */

/*
Plugin Name: MLA Gallery Download Archive
Plugin URI: http://davidlingren.com/
Description: Downloads gallery items as a ZIP archive
Author: David Lingren
Version: 1.02
Author URI: http://davidlingren.com/

Copyright 2020 David Lingren

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You can get a copy of the GNU General Public License by writing to the
	Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110, USA
*/

/**
 * Class MLA Gallery Download Archive downloads gallery items as a ZIP archive
 *
 * @package MLA Gallery Download Archive
 * @since 1.00
 */
class MLAGalleryDownloadArchive {
	/**
	 * Name the shortcode
	 */
	const SHORTCODE_NAME = 'mla_download_archive';

	/**
	 * Initialization function, similar to __construct()
	 *
	 * Adds the 'mla_featured_field' shortcode to WordPress
	 *
	 * @since 1.00
	 *
	 * @return	void
	 */
	public static function initialize() {
		// Are we processing the download link?
		if( defined('DOING_AJAX') && DOING_AJAX ) {
			// Defined here because the "admin_init" action is not called for item transfers
			if ( isset( $_REQUEST['action'] ) && self::SHORTCODE_NAME ===  $_REQUEST['action'] ) {
				add_action( 'wp_ajax_' . self::SHORTCODE_NAME, 'MLAGalleryDownloadArchive::mla_download_archive_action' );
				add_action( 'wp_ajax_nopriv_' . self::SHORTCODE_NAME, 'MLAGalleryDownloadArchive::mla_download_archive_action' );
			}
		} else {
			add_shortcode( self::SHORTCODE_NAME, 'MLAGalleryDownloadArchive::mla_download_archive' );
		}
	}

	/**
	 * Make sure $attr is an array, repair line-break damage, merge with $content,
	 * expand page-level, request: and query: substitution parameters
	 *
	 * @since 1.01
	 *
	 * @param	mixed	$attr Array or string containing shortcode attributes
	 * @param	string	$content Optional content for enclosing shortcodes
	 *
	 * @return	array	clean attributes array
	 */
	private static function _prepare_attributes( $attr, $content = NULL ) {
		global $post;

		if ( !class_exists( 'MLAShortcode_Support' ) ) {
			require_once( MLA_PLUGIN_PATH . 'includes/class-mla-shortcode-support.php' );
		}

		// Create a clean array of shortcode parameters
		$attr = MLAShortcode_Support::mla_validate_attributes( $attr, $content );

		// Page values are already known, and can be used in data selection parameters
		$page_values = array(
			'id' => $post->ID,
			'page_ID' => $post->ID,
			'page_author' => $post->post_author,
			'page_date' => $post->post_date,
			'page_content' => $post->post_content,
			'page_title' => $post->post_title,
			'page_excerpt' => $post->post_excerpt,
			'page_status' => $post->post_status,
			'page_name' => $post->post_name,
			'page_modified' => $post->post_modified,
			'page_parent' => $post->post_parent,
			'page_guid' => $post->guid,
			'page_type' => $post->post_type,
			'page_mime_type' => $post->post_mime_type,
			'page_url' => get_page_link(),
		);

		// Look for page-level, 'request:' and 'query:' substitution parameters
		foreach ( $attr as $attr_key => $attr_value ) {
			$attr_value = str_replace( '{+', '[+', str_replace( '+}', '+]', $attr_value ) );
			$replacement_values = MLAData::mla_expand_field_level_parameters( $attr_value, $attr, $page_values );
			$attr[ $attr_key ] = MLAData::mla_parse_template( $attr_value, $replacement_values );
		}

		return $attr;
	}
	
	/**
	 * Ajax handler to download an archie of Media Library items
	 *
	 * @since 1.00
	 *
	 * @return	void	echo HTML for file streaming or download, then exit()
	 */
	public static function mla_download_archive_action() {
		global $post;
		
		$default_arguments = array(
			'mla_post' => 0,
			'mla_index' => 0,
		);
		
		$arguments = shortcode_atts( $default_arguments, $_REQUEST );

		$mla_post = absint( $arguments['mla_post'] );
		$mla_index = absint( $arguments['mla_index'] );
		$post = get_post( $mla_post );
		
		if ( is_null( $post ) ) {
			exit;
		}

		// Recover the original shortcode parameters from the post/page content
		$shortcode_regex = get_shortcode_regex( array( MLAGalleryDownloadArchive::SHORTCODE_NAME ) );
		if ( preg_match_all( '/'. $shortcode_regex .'/s', $post->post_content, $matches )
		&& array_key_exists( 2, $matches )
		&& isset( $matches[2][ $mla_index ] ) ) {
			if ( !class_exists( 'MLAShortcode_Support' ) ) {
				require_once( MLA_PLUGIN_PATH . 'includes/class-mla-shortcode-support.php' );
			}

		// Create a clean array of shortcode parameters
		$attr = self::_prepare_attributes( $matches[3][ $mla_index ], $matches[5][ $mla_index ] );
		}

		// Populate the "gallery"
		$attachments = 	MLAShortcode_Support::mla_get_shortcode_attachments( $mla_post, $attr, false );

		/*
		 * Create unique local names to handle the case where the same file name
		 * appears in multiple year/month/ directories.
		 */
		$file_names = array();
		foreach ( $attachments as $index => $attachment ) {
			$file_name = get_attached_file( $attachment->ID );
			$path_info = pathinfo( $file_name  );
			$local_name = $path_info['basename'];
			$suffix = 0;
			while( array_key_exists( $local_name, $file_names ) ) {
				$suffix++;
				$local_name = $path_info['filename'] . $suffix . '.' . $path_info['extension'];
			}

			$file_names[ $local_name ] = $file_name;
		}

		// Create the ZIP archive
		$upload_dir = wp_upload_dir();
		$prefix = ( !empty( $attr['archive_name'] ) ) ? sanitize_title_with_dashes( trim( $attr['archive_name'] ) ) : MLAGalleryDownloadArchive::SHORTCODE_NAME;
		$date = date("Ymd_B");
		$archive_name = $upload_dir['basedir'] . '/' . "{$prefix}_{$date}.zip";

		if ( file_exists( $archive_name ) ) {
			@unlink( $archive_name );
		}

		$mla_error = '';
		$zip = new ZipArchive();
		if ( true !== $zip->open( $archive_name, ZIPARCHIVE::CREATE ) ) {
			$mla_error = sprintf( 'ERROR: The ZIP archive ( %1$s ) could not be created.', $archive_name );
		} else {
			foreach( $file_names as $local_name => $file_name ) {
				if ( true !== $zip->addFile( $file_name, $local_name ) ) {
					$mla_error = sprintf( 'ERROR: The file ( %1$s ) could not be added to the ZIP archive.', $file_name );
					break;
				}
			} // foreach file

			if ( true !== $zip->close() ) {
				$mla_error = sprintf( 'ERROR: The ZIP archive ( %1$s ) could not be closed.', $archive_name );
			}
		}

		if ( empty( $mla_error ) ) {
			if ( file_exists( $archive_name ) ) {
				$filemtime = filemtime ( $archive_name );
				$filesize = filesize( $archive_name );
			} else {
				$filemtime = time();
				$filesize = 0;
			}
			
			header('Pragma: public'); 	// required
			header('Expires: 0');		// no cache
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Last-Modified: '.gmdate ( 'D, d M Y H:i:s', $filemtime ).' GMT');
			header('Cache-Control: private',false);
			header('Content-Type: '.$request['mla_download_type']);
			header('Content-Disposition: attachment; filename="'.basename( $archive_name ).'"');
			header('Content-Transfer-Encoding: binary');
			header('Content-Length: '.$filesize);	// provide file size
			header('Connection: close');

			if ( 0 < $filesize ) {
				readfile( $archive_name );
			}

			if ( isset( $request['mla_download_disposition'] ) && 'delete' == $request['mla_download_disposition'] ) {
				@unlink( $archive_name );
			}
		} else {
			echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
			echo '<html xmlns="http://www.w3.org/1999/xhtml">';
			echo '<head>';
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			echo '<title>Download Error</title>';
			echo '</head>';
			echo '';
			echo '<body>';
			echo $mla_error;
			echo '</body>';
			echo '</html> ';
		}

		exit();
	}

	/**
	 * WordPress Shortcode; downloads gallery items as a ZIP archive
	 *
	 * @since 1.00
	 *
	 * @param	array	shortcode parameters; defaults ( 'field_name' => DEFAULT_FIELD, 'ids' => '' )
	 *
	 * @return	string	post/page content to replace the shortcode
	 */
	public static function mla_download_archive( $attr, $content = NULL ) {
		global $post;
		
		$default_arguments = array(
			'archive_name' => '',
			'link_attributes' => '',
			'link_class' => '',
			'link_text' => '',
			'allow_empty_archive' => 'true',
			'nolink_text' => '',
		);
		
		// Create a clean array of shortcode parameters
		$attr = self::_prepare_attributes( $attr, $content );

		// Combine parameters with defaults
		$arguments = shortcode_atts( $default_arguments, $attr );

		// archive_name is required and must be unique in the post/page
		if ( empty( $arguments['archive_name'] ) ) {
			return '';
		}
		
		$archive_title = $arguments['archive_name'];
		$archive_name = sanitize_title_with_dashes( trim( $archive_title ) );

		$link_attributes = trim( $arguments['link_attributes'] );
		if ( !empty( $link_attributes ) ) {
			$link_attributes .= ' ';
		}
		
		$link_class = trim( $arguments['link_class'] );
		if ( !empty( $link_class ) ) {
			$link_class .= ' ';
		}

		$link_text = trim( $arguments['link_text'] );
		if ( empty( $link_text ) ) {
			$link_text = "Download {$archive_title}";
		}

		$allow_empty_archive = 'true' === strtolower( trim( $arguments['allow_empty_archive'] ) );
		
		// Find all of the shortcodes in the post/page content
		$shortcode_regex = get_shortcode_regex( array( MLAGalleryDownloadArchive::SHORTCODE_NAME ) );
		if ( preg_match_all( '/'. $shortcode_regex .'/s', $post->post_content, $matches )
		&& array_key_exists( 2, $matches )
		&& in_array( MLAGalleryDownloadArchive::SHORTCODE_NAME, $matches[2] ) ) {
			foreach ( $matches[0] as $index => $match ) {
				// Match to this specific shortcode instance
				if ( false === strpos( $match, 'archive_name' ) || false === strpos( $match, $archive_title ) ) {
					continue;
				}

				// Check for the empty case, if not allowed
				if ( !$allow_empty_archive ) {
					// Create a clean array of shortcode parameters
					$attr = self::_prepare_attributes( $matches[3][ $index ], $matches[5][ $index ] );
					$attachments = 	MLAShortcode_Support::mla_get_shortcode_attachments( $post->ID, $attr, false );
					if ( empty( $attachments ) ) {
						$nolink_text = trim( $arguments['nolink_text'] );
						if ( empty( $link_text ) ) {
							return '';
						}
						
						return $nolink_text;
					} // found empty archive
				} // Prevent empty archive

				// AJAX-based link for forced downloads
				$args = array_merge( $_REQUEST, array(
					'action' => 'mla_download_archive',
					'mla_post' => $post->ID,
					'mla_index' => $index,
				) );

				$url = add_query_arg( $args, admin_url( 'admin-ajax.php' ) );
				return sprintf( '<a %1$s%2$shref="%3$s">%4$s</a>', $link_class, $link_attributes, $url, $link_text );
			} // each matches[0]
		}		

		return '';
	} //mla_download_archive
} //MLAGalleryDownloadArchive

// Install the shortcode at an early opportunity
add_action('init', 'MLAGalleryDownloadArchive::initialize');
?>