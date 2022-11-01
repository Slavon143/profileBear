<?php
/**
 * A collection of functions used for importing / removing Convert Plugin's modules.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Importer
 * @since      6.2
 */

/**
 * Imports Convert Plugin's Slide In module.
 *
 * @param array $data Import data.
 * @return void
 */
function fusion_cp_import_slide_in( $data ) {

	if ( ! current_user_can( 'access_cp' ) ) {
		die( -1 );
	}

	/* $data     = $_POST; */
	$file     = $data['file'];
	$title    = sanitize_title( $file['title'] );
	$filename = sanitize_file_name( $file['filename'] );
	$file     = realpath( get_attached_file( intval( $file['id'] ) ) );

	// Get the name of the directory inside the exported zip.
	$zip = zip_open( $file );

	if ( is_resource( $zip ) ) {
		while ( zip_read( $zip ) == $zip_entry ) {
			$title = dirname( zip_entry_name( $zip_entry ) );
		}
		zip_close( $zip );
	} else {
		/* translators:%s zip name .*/
		echo sprintf( __( 'Failed to Open. Error Code: %s ', 'smile' ), $zip );
		die();
	}

	// Set the path variable for extracting the zip.
	$paths             = array();
	$paths             = wp_upload_dir();
	$paths['export']   = 'cp_export';
	$paths['tempdir']  = trailingslashit( $paths['basedir'] ) . 'cp_modal';
	$paths['temp']     = trailingslashit( $paths['basedir'] ) . 'cp_modal/' . $title;
	$paths['tempurl']  = trailingslashit( $paths['baseurl'] ) . 'cp_modal/';
	$paths['basepath'] = $paths['basedir'] . '/cp_modal/';
	$folder_path       = $paths['basedir'] . '/cp_modal/' . $title;

	// Create the respective directory inside wp-uploads directory.
	if ( ! is_dir( $paths['temp'] ) ) {
		$tempdir = smile_backend_create_folder( $paths['temp'], false );
	}

	WP_Filesystem();
	$destination_path = $paths['tempdir'];

	// Extract the zip to our newly created directory.
	$unzipfile = unzip_file( $file, $destination_path );

	if ( ! $unzipfile ) {
		die( __( 'Unable to extract the file.', 'smile' ) );
	}

	// Sanitize folder name.
	$new_folder_name = sanitize_file_name( $title );

	// Grant permission.
	chmod( $folder_path, 0755 );

	$new_folder_path = $paths['basepath'] . $new_folder_name;

	// Rename folder.
	rename( $folder_path, $new_folder_path );

	// Rename settings file.
	rename( $new_folder_path . '/' . $title . '.txt', $new_folder_path . '/' . $new_folder_name . '.txt' );

	// Set the json file file url to get the settings for the style.
	$json_file = $paths['tempurl'] . $new_folder_name . '/' . $new_folder_name . '.txt';

	$module         = sanitize_text_field( $data['module'] );
	$data_option    = 'smile_slide_in_styles';
	$variant_option = 'slide_in_variant_tests';

	// Read the text file containing the json formatted settings of style and decode it.
	$content = wp_remote_get( $json_file );

	$json         = $content['body'];
	$obj          = json_decode( $json, true );
	$import_style = array();
	$new_style_id = $obj['style_id'];
	$cp_module    = $obj['module'];

	if ( 'slide_in' !== $cp_module ) {

		print(
			wp_json_encode(
				array(
					'status'      => 'error',
					'description' => /* translators:%s module name .*/
					sprintf( __( 'Seems that the file have uploaded the wrong file. This file can be imported for %s ', 'smile' ), str_replace( '_', ' ', $cp_module ) ),
				)
			)
		);

		die();
	}

	if ( ! isset( $obj['style_id'] ) ) {
		print(
			wp_json_encode(
				array(
					'status'      => 'error',
					'description' => __( 'Seems that the file is different from the exported modal zip. Please try with another zip file.', 'smile' ),
				)
			)
		);
		die();
	}

	$style_settings = (array) $obj['style_settings'];

	if ( isset( $obj['variants'] ) ) {
		foreach ( $obj['variants'] as $key => $value ) {
			$variant_analytics = unserialize( $value['style_settings'] );
			if ( ! empty( $variant_analytics['analytics'] ) ) {
				$analytics_value = $variant_analytics['analytics'];
				$style_id        = $variant_analytics['variant_style_id'];
				smile_update_custom_conversions( $analytics_value, $style_id );
			}
		}
	}

	if ( isset( $style_settings['analytics'] ) && ! empty( $style_settings['analytics'] ) ) {
		$analytics_value = $style_settings['analytics'];
		$style_id        = $style_settings['style_id'];
		smile_update_custom_conversions( $analytics_value, $style_id );
	}

	if ( isset( $style_settings['cp_google_fonts'] ) ) {
		$google_fonts = explode( ',', $style_settings['cp_google_fonts'] );
		cp_import_google_fonts( $google_fonts );
	}
	if ( isset( $obj['media']['slidein_bg_image'] ) ) {
		$old_image = $obj['media']['slidein_bg_image'];
		unset( $obj['media']['slidein_bg_image'] );
		$obj['media']['slide_in_bg_image'] = $old_image;
	}

	if ( isset( $obj['media'] ) ) {
		$media     = (array) $obj['media'];
		$media_ids = array();

		if ( isset( $media ) && is_array( $media ) ) {
			// Import media if any.
			foreach ( $media as $option => $value ) {

				$value = str_replace( $title, $new_folder_name, $value );

				// $filename should be the path to a file in the upload directory.
				$filename = $paths['tempdir'] . '/' . $value;

				// Check the type of file. We'll use this as the 'post_mime_type'.
				$filetype = wp_check_filetype( basename( $filename ), null );

				// Get the path to the upload directory.
				$wp_upload_dir = wp_upload_dir();

				// Prepare an array of post data for the attachment.
				$attachment = array(
					'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ),
					'post_mime_type' => $filetype['type'],
					'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
					'post_content'   => '',
					'post_status'    => 'inherit',
				);

				// Insert the attachment.
				$option               = ( 'close_image' === $option ) ? 'close_img' : $option;
				$media_ids[ $option ] = wp_insert_attachment( $attachment, $filename );

				// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
				require_once ABSPATH . 'wp-admin/includes/image.php';

				// Generate the metadata for the attachment, and update the database record.
				$attach_data = wp_generate_attachment_metadata( $media_ids[ $option ], $filename );
				wp_update_attachment_metadata( $media_ids[ $option ], $attach_data );

				// Get the attachment id and update the setting for media in style.
				if ( isset( $style_settings[ $option ] ) ) {
					$media_image = $style_settings[ $option ];
					$media_image = str_replace( '%7C', '|', $media_image );
					if ( false !== strpos( $media_image, 'http' ) ) {
						$media_image = explode( '|', $media_image );
						$media_image = $media_image[1];
					} else {
						$media_image = explode( '|', $media_image );
						$media_image = $media_image[1];
					}
					$media_image               = $media_ids[ $option ] . '|' . $media_image;
					$style_settings[ $option ] = $media_image;
				}
			}
		}
	}

	$prev_styles   = get_option( $data_option );
	$variant_tests = get_option( $variant_option );

	$prev_styles = empty( $prev_styles ) ? array() : $prev_styles;
	$update      = false;

	foreach ( $style_settings as $title => $value ) {

		if ( 'slidein_bg_image' === $title ) {
			$title = 'slide_in_bg_image';
		}

		if ( ! is_array( $value ) ) {
			$value                  = htmlspecialchars_decode( $value );
			$import_style[ $title ] = $value;
		} else {
			foreach ( $value as $ex_title => $ex_val ) {
				$val[ $ex_title ] = htmlspecialchars_decode( $ex_val );
			}
			$import_style[ $title ] = $val;
		}
	}

	$import                   = $obj;
	$import['style_settings'] = serialize( $import_style );

	if ( isset( $import['variants'] ) ) {
		unset( $import['variants'] );
	}

	if ( ! empty( $prev_styles ) ) {
		foreach ( $prev_styles as $key => $style ) {
			$style_id = $style['style_id'];
			if ( $new_style_id === $style_id ) {
				$update = false;
				print(
					wp_json_encode(
						array(
							'status'      => 'error',
							'description' => __( 'Style Already Exists! Please try importing another style.', 'smile' ),
						)
					)
				);
				die();
			} else {
				$update = true;
			}
		}
	} else {
		$update = true;
	}

	if ( $update ) {
		array_push( $prev_styles, $import );
		$status = update_option( $data_option, $prev_styles );

		// Import variants .
		if ( isset( $obj['variants'] ) ) {
			$variant_tests[ $new_style_id ] = $obj['variants'];
			$status                         = update_option( $variant_option, $variant_tests );
		}
	} else {
		$status = false;
	}

	// Check the status of import and return the object accordingly.
	if ( $status ) {
		print(
			wp_json_encode(
				array(
					'status'      => 'success',
					'description' => ucwords( str_replace( '_', ' ', $module ) ) . ' ' . __( 'imported successfully!', 'smile' ),
				)
			)
		);
	} else {
		print(
			wp_json_encode(
				array(
					'status'      => 'error',
					'description' => __( 'Something went wrong! Please try again with different file.', 'smile' ),
				)
			)
		);
	}
	/* die(); */
}

/**
 * Imports Convert Plugin's Modal module.
 *
 * @param array $data Import data.
 * @return void
 */
function fusion_cp_import_modal( $data ) {

	if ( ! current_user_can( 'access_cp' ) ) {
		die( -1 );
	}

	/*$data     = $_POST; */

	$file     = $data['file'];
	$title    = sanitize_title( $file['title'] );
	$filename = sanitize_file_name( $file['filename'] );
	$file     = realpath( get_attached_file( intval( $file['id'] ) ) );

	// Get the name of the directory inside the exported zip.
	$zip = zip_open( $file );

	// valid zip file.
	if ( is_resource( $zip ) ) {
		while ( zip_read( $zip ) == $zip_entry ) {
			$title = dirname( zip_entry_name( $zip_entry ) );
		}
		zip_close( $zip );
	} else {
		/* translators:%s zip name .*/
		echo sprintf( __( 'Failed to Open. Error Code: %s ', 'smile' ), $zip );
		die();
	}

	// Set the path variable for extracting the zip.
	$paths             = array();
	$paths             = wp_upload_dir();
	$paths['export']   = 'cp_export';
	$paths['tempdir']  = trailingslashit( $paths['basedir'] ) . 'cp_modal';
	$paths['temp']     = trailingslashit( $paths['basedir'] ) . 'cp_modal/' . $title;
	$paths['tempurl']  = trailingslashit( $paths['baseurl'] ) . 'cp_modal/';
	$paths['basepath'] = $paths['basedir'] . '/cp_modal/';
	$folder_path       = $paths['basedir'] . '/cp_modal/' . $title;

	// Create the respective directory inside wp-uploads directory.
	if ( ! is_dir( $paths['temp'] ) ) {
		$tempdir = smile_backend_create_folder( $paths['temp'], false );
	}

	WP_Filesystem();
	$destination_path = $paths['tempdir'];

	// Extract the zip to our newly created directory.
	$unzipfile = unzip_file( $file, $destination_path );

	if ( ! $unzipfile ) {
		die( __( 'Unable to extract the file.', 'smile' ) );
	}

	// Sanitize folder name.
	$new_folder_name = sanitize_file_name( $title );

	// Grant permission.
	chmod( $folder_path, 0755 );

	$new_folder_path = $paths['basepath'] . $new_folder_name;

	// Rename folder.
	rename( $folder_path, $new_folder_path );

	// Rename settings file.
	rename( $new_folder_path . '/' . $title . '.txt', $new_folder_path . '/' . $new_folder_name . '.txt' );

	// Set the json file file url to get the settings for the style.
	$json_file = $paths['tempurl'] . $new_folder_name . '/' . $new_folder_name . '.txt';

	$module         = sanitize_text_field( $data['module'] );
	$data_option    = 'smile_modal_styles';
	$variant_option = 'modal_variant_tests';

	// Read the text file containing the json formatted settings of style and decode it.
	$content = wp_remote_get( $json_file );

	$json = $content['body'];

	$obj          = json_decode( $json, true );
	$import_style = array();
	$new_style_id = $obj['style_id'];
	$cp_module    = $obj['module'];

	if ( 'modal' !== $cp_module ) {

		print(
			wp_json_encode(
				array(
					'status'      => 'error',
					'description' => /* translators:%s module name .*/
					sprintf( __( 'Seems that the file have uploaded the wrong file. This file can be imported for %s ', 'smile' ), str_replace( '_', ' ', $cp_module ) ),
				)
			)
		);

		die();
	}

	if ( ! isset( $obj['style_id'] ) ) {
		print(
			wp_json_encode(
				array(
					'status'      => 'error',
					'description' => __( 'Seems that the file is different from the exported modal zip. Please try with another zip file.', 'smile' ),
				)
			)
		);
		die();
	}
	$style_settings = (array) $obj['style_settings'];

	if ( isset( $obj['media'] ) ) {
		$media = (array) $obj['media'];
	}

	if ( isset( $obj['variants'] ) ) {
		foreach ( $obj['variants'] as $key => $value ) {
			$variant_analytics = unserialize( $value['style_settings'] );
			if ( ! empty( $variant_analytics['analytics'] ) ) {
				$analytics_value = $variant_analytics['analytics'];
				$style_id        = $variant_analytics['variant_style_id'];
				smile_update_custom_conversions( $analytics_value, $style_id );
			}
		}
	}

	if ( isset( $style_settings['analytics'] ) && ! empty( $style_settings['analytics'] ) ) {
		$analytics_value = $style_settings['analytics'];
		$style_id        = $style_settings['style_id'];
		smile_update_custom_conversions( $analytics_value, $style_id );
	}

	if ( isset( $style_settings['cp_google_fonts'] ) ) {
		$google_fonts = explode( ',', $style_settings['cp_google_fonts'] );
		cp_import_google_fonts( $google_fonts );
	}

	$media_ids = array();

	if ( isset( $media ) && is_array( $media ) ) {
		// Import media if any.
		foreach ( $media as $option => $value ) {

			$value = str_replace( $title, $new_folder_name, $value );

			// $filename should be the path to a file in the upload directory.
			$filename = $paths['tempdir'] . '/' . $value;

			// Check the type of file. We'll use this as the 'post_mime_type'.
			$filetype = wp_check_filetype( basename( $filename ), null );

			// Get the path to the upload directory.
			$wp_upload_dir = wp_upload_dir();

			// Prepare an array of post data for the attachment.
			$attachment = array(
				'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ),
				'post_mime_type' => $filetype['type'],
				'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
				'post_content'   => '',
				'post_status'    => 'inherit',
			);

			// Insert the attachment.
			$option               = ( 'close_image' === $option ) ? 'close_img' : $option;
			$media_ids[ $option ] = wp_insert_attachment( $attachment, $filename );

			// Make sure that this file is included, as wp_generate_attachment_metadata() .depends on it.
			require_once ABSPATH . 'wp-admin/includes/image.php';

			// Generate the metadata for the attachment, and update the database record.
			$attach_data = wp_generate_attachment_metadata( $media_ids[ $option ], $filename );
			wp_update_attachment_metadata( $media_ids[ $option ], $attach_data );

			// Get the attachment id and update the setting for media in style.
			if ( isset( $style_settings[ $option ] ) ) {
				$media_image = $style_settings[ $option ];
				$media_image = str_replace( '%7C', '|', $media_image );
				if ( false !== strpos( $media_image, 'http' ) ) {
					$media_image = explode( '|', $media_image );
					$media_image = $media_image[1];
				} else {
					$media_image = explode( '|', $media_image );
					$media_image = $media_image[1];
				}
				$media_image               = $media_ids[ $option ] . '|' . $media_image;
				$style_settings[ $option ] = $media_image;

			}
		}
	}

	$prev_styles   = get_option( $data_option );
	$variant_tests = get_option( $variant_option );

	$prev_styles = empty( $prev_styles ) ? array() : $prev_styles;
	$update      = false;

	foreach ( $style_settings as $title => $value ) {
		if ( ! is_array( $value ) ) {
			$value                  = htmlspecialchars_decode( $value );
			$import_style[ $title ] = $value;
		} else {
			foreach ( $value as $ex_title => $ex_val ) {
				foreach ( $ex_val as $key1 => $value1 ) {
					$val[ $key1 ] = htmlspecialchars_decode( $value1 );
				}
			}
			$import_style[ $title ] = $val;
		}
	}
	$import                   = $obj;
	$import['style_settings'] = serialize( $import_style );

	if ( isset( $import['variants'] ) ) {
		unset( $import['variants'] );
	}

	if ( ! empty( $prev_styles ) ) {
		foreach ( $prev_styles as $key => $style ) {
			$style_id = $style['style_id'];
			if ( $new_style_id == $style_id ) {
				$update = false;
				print(
					wp_json_encode(
						array(
							'status'      => 'error',
							'description' => __( 'Style Already Exists! Please try importing another style.', 'smile' ),
						)
					)
				);
				die();
			} else {
				$update = true;
			}
		}
	} else {
		$update = true;
	}

	if ( $update ) {
		array_push( $prev_styles, $import );
		$status = update_option( $data_option, $prev_styles );

		// Import variants.
		if ( isset( $obj['variants'] ) ) {
			$variant_tests[ $new_style_id ] = $obj['variants'];
			$status                         = update_option( $variant_option, $variant_tests );
		}
	} else {
		$status = false;
	}

	// Check the status of import and return the object accordingly.
	if ( $status ) {
		print(
			wp_json_encode(
				array(
					'status'      => 'success',
					'description' => ucwords( str_replace( '_', ' ', $module ) ) . ' ' . __( 'imported successfully!', 'smile' ),
				)
			)
		);
	} else {
		print(
			wp_json_encode(
				array(
					'status'      => 'error',
					'description' => __( 'Something went wrong! Please try again with different file.', 'smile' ),
				)
			)
		);
	}
	/* die(); */
}

/**
 * Imports Convert Plugin's Info Bar module.
 *
 * @param array $data Import data.
 * @return void
 */
function fusion_cp_import_info_bar( $data ) {

	if ( ! current_user_can( 'access_cp' ) ) {
		die( -1 );
	}

	/* $data     = $_POST; */
	$file     = $data['file'];
	$title    = sanitize_title( $file['title'] );
	$filename = sanitize_file_name( $file['filename'] );
	$file     = realpath( get_attached_file( intval( $file['id'] ) ) );

	// Get the name of the directory inside the exported zip.
	$zip = zip_open( $file );

	if ( is_resource( $zip ) ) {
		while ( zip_read( $zip ) == $zip_entry ) {
			$title = dirname( zip_entry_name( $zip_entry ) );
		}
		zip_close( $zip );
	} else {
		/* translators:%s zip name .*/
		echo sprintf( __( 'Failed to Open. Error Code: %s ', 'smile' ), $zip );
		die();
	}

	// Set the path variable for extracting the zip.
	$paths             = array();
	$paths             = wp_upload_dir();
	$paths['export']   = 'cp_export';
	$paths['tempdir']  = trailingslashit( $paths['basedir'] ) . 'cp_modal';
	$paths['temp']     = trailingslashit( $paths['basedir'] ) . 'cp_modal/' . $title;
	$paths['tempurl']  = trailingslashit( $paths['baseurl'] ) . 'cp_modal/';
	$paths['basepath'] = $paths['basedir'] . '/cp_modal/';
	$folder_path       = $paths['basedir'] . '/cp_modal/' . $title;

	// Create the respective directory inside wp-uploads directory.
	if ( ! is_dir( $paths['temp'] ) ) {
		$tempdir = smile_backend_create_folder( $paths['temp'], false );
	}

	WP_Filesystem();
	$destination_path = $paths['tempdir'];

	// Extract the zip to our newly created directory.
	$unzipfile = unzip_file( $file, $destination_path );

	if ( ! $unzipfile ) {
		die( __( 'Unable to extract the file.', 'smile' ) );
	}

	// Sanitize folder name.
	$new_folder_name = sanitize_file_name( $title );

	// Grant permission.
	chmod( $folder_path, 0755 );

	$new_folder_path = $paths['basepath'] . $new_folder_name;

	// Rename folder.
	rename( $folder_path, $new_folder_path );

	// rename settings file.
	rename( $new_folder_path . '/' . $title . '.txt', $new_folder_path . '/' . $new_folder_name . '.txt' );

	// Set the json file file url to get the settings for the style.
	$json_file = $paths['tempurl'] . $new_folder_name . '/' . $new_folder_name . '.txt';

	$module         = sanitize_text_field( $data['module'] );
	$data_option    = 'smile_info_bar_styles';
	$variant_option = 'info_bar_variant_tests';

	// Read the text file containing the json formatted settings of style and decode it.
	$content = wp_remote_get( $json_file );

	$json = $content['body'];

	$obj          = json_decode( $json, true );
	$import_style = array();
	$new_style_id = $obj['style_id'];

	$cp_module = $obj['module'];

	if ( 'info_bar' !== $cp_module ) {

		print(
			wp_json_encode(
				array(
					'status'      => 'error',
					'description' => /* translators:%s module name .*/
					sprintf( __( 'Seems that the file have uploaded the wrong file. This file can be imported for %s ', 'smile' ), str_replace( '_', ' ', $cp_module ) ),
				)
			)
		);

		die();
	}

	if ( ! isset( $obj['style_id'] ) ) {
		print(
			wp_json_encode(
				array(
					'status'      => 'error',
					'description' => __( 'Seems that the file is different from the exported info bar zip. Please try with another zip file.', 'smile' ),
				)
			)
		);
		die();
	}

	$style_settings = (array) $obj['style_settings'];

	if ( isset( $obj['variants'] ) ) {
		foreach ( $obj['variants'] as $key => $value ) {
			$variant_analytics = unserialize( $value['style_settings'] );
			if ( ! empty( $variant_analytics['analytics'] ) ) {
				$analytics_value = $variant_analytics['analytics'];
				$style_id        = $variant_analytics['variant_style_id'];
				smile_update_custom_conversions( $analytics_value, $style_id );
			}
		}
	}

	if ( isset( $style_settings['analytics'] ) && ! empty( $style_settings['analytics'] ) ) {
		$analytics_value = $style_settings['analytics'];
		$style_id        = $style_settings['style_id'];
		smile_update_custom_conversions( $analytics_value, $style_id );
	}

	if ( isset( $obj['media']['infobar_image'] ) ) {
		$old_ib_image = $obj['media']['infobar_image'];
		unset( $obj['media']['infobar_image'] );
		$obj['media']['info_bar_image'] = $old_ib_image;
	}

	if ( isset( $obj['media']['infobar_bg_image'] ) ) {
		$old_ib_bg_image = $obj['media']['infobar_bg_image'];
		unset( $obj['media']['infobar_bg_image'] );
		$obj['media']['info_bar_bg_image'] = $old_ib_bg_image;
	}

	if ( isset( $obj['media'] ) ) {
		$media = (array) $obj['media'];
	}

	if ( isset( $style_settings['cp_google_fonts'] ) ) {
		$google_fonts = explode( ',', $style_settings['cp_google_fonts'] );
		cp_import_google_fonts( $google_fonts );
	}

	$media_ids = array();

	if ( isset( $media ) && is_array( $media ) ) {

		// Import media if any.
		foreach ( $media as $option => $value ) {

			$value = str_replace( $title, $new_folder_name, $value );

			// $filename should be the path to a file in the upload directory.
			$filename = $paths['tempdir'] . '/' . $value;

			// Check the type of file. We'll use this as the 'post_mime_type'.
			$filetype = wp_check_filetype( basename( $filename ), null );

			// Get the path to the upload directory.
			$wp_upload_dir = wp_upload_dir();

			// Prepare an array of post data for the attachment.
			$attachment = array(
				'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ),
				'post_mime_type' => $filetype['type'],
				'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
				'post_content'   => '',
				'post_status'    => 'inherit',
			);

			// Insert the attachment.
			$option               = ( 'close_image' === $option ) ? 'close_img' : $option;
			$media_ids[ $option ] = wp_insert_attachment( $attachment, $filename );

			// Make sure that this file is included, as wp_generate_attachment_metadata(). depends on it.
			require_once ABSPATH . 'wp-admin/includes/image.php';

			// Generate the metadata for the attachment, and update the database record.
			$attach_data = wp_generate_attachment_metadata( $media_ids[ $option ], $filename );
			wp_update_attachment_metadata( $media_ids[ $option ], $attach_data );

			// Get the attachment id and update the setting for media in style.
			if ( isset( $style_settings[ $option ] ) ) {
				$media_image = $style_settings[ $option ];
				$media_image = str_replace( '%7C', '|', $media_image );
				if ( false !== strpos( $media_image, 'http' ) ) {
					$media_image = explode( '|', $media_image );
					$media_image = $media_image[1];
				} else {
					$media_image = explode( '|', $media_image );
					$media_image = $media_image[1];
				}
				$media_image               = $media_ids[ $option ] . '|' . $media_image;
				$style_settings[ $option ] = $media_image;
			}
		}
	}

	$prev_styles   = get_option( $data_option );
	$variant_tests = get_option( $variant_option );

	$prev_styles = empty( $prev_styles ) ? array() : $prev_styles;

	$update = false;

	foreach ( $style_settings as $title => $value ) {

		if ( 'infobar_bg_image' === $title ) {
			$title = 'info_bar_bg_image';
		}

		if ( 'infobar_image' === $title ) {
			$title = 'info_bar_image';
		}

		if ( ! is_array( $value ) ) {
			$value                  = htmlspecialchars_decode( $value );
			$import_style[ $title ] = $value;
		} else {
			foreach ( $value as $ex_title => $ex_val ) {
				$val[ $ex_title ] = htmlspecialchars_decode( $ex_val );
			}
			$import_style[ $title ] = $val;
		}
	}
	$import                   = $obj;
	$import['style_settings'] = serialize( $import_style );

	if ( isset( $import['variants'] ) ) {
		unset( $import['variants'] );
	}

	if ( ! empty( $prev_styles ) ) {
		foreach ( $prev_styles as $key => $style ) {
			$style_id = $style['style_id'];
			if ( $new_style_id == $style_id ) {
				$update = false;
				print(
					wp_json_encode(
						array(
							'status'      => 'error',
							'description' => __( 'Style Already Exists! Please try importing another style.', 'smile' ),
						)
					)
				);
				die();
			} else {
				$update = true;
			}
		}
	} else {
		$update = true;
	}

	if ( $update ) {
		array_push( $prev_styles, $import );
		$status = update_option( $data_option, $prev_styles );

		// Import variants.
		if ( isset( $obj['variants'] ) ) {
			$variant_tests[ $new_style_id ] = $obj['variants'];
			$status                         = update_option( $variant_option, $variant_tests );
		}
	} else {
		$status = false;
	}

	// Check the status of import and return the object accordingly.
	if ( $status ) {
		print(
			wp_json_encode(
				array(
					'status'      => 'success',
					'description' => ucwords( str_replace( '_', ' ', $module ) ) . ' ' . __( 'imported successfully!', 'smile' ),
				)
			)
		);
	} else {
		print(
			wp_json_encode(
				array(
					'status'      => 'error',
					'description' => __( 'Something went wrong! Please try again with different file.', 'smile' ),
				)
			)
		);
	}
	/* die(); */
}

/**
 * Deletes Convert Plugin's module
 *
 * @param array $data Import data.
 * @return void
 */
function fusion_cp_delete_all_modal_action( $data ) {

	if ( ! current_user_can( 'access_cp' ) ) {
		die( -1 );
	}

	$delete_all_ids = esc_attr( $data['style_id'] );
	$analtics_data  = get_option( 'smile_style_analytics' );

	$style_array    = explode( ',', $delete_all_ids );
	$option         = isset( $data['option'] ) ? esc_attr( $data['option'] ) : '';
	$variant_option = isset( $data['variant_option'] ) ? esc_attr( $data['variant_option'] ) : '';
	$result         = true;
	$prev_styles    = get_option( $option );

	foreach ( $style_array as $key => $value ) {
		$style_id = $value;
		$key = search_style( $prev_styles, $style_id );

		$has_variants = false;

		$modal_arrays = array();

		$smile_variant_tests = get_option( $variant_option );
		if ( $smile_variant_tests && is_array( $smile_variant_tests ) ) {
			$has_variants = array_key_exists( $style_id, $smile_variant_tests );
		}

		if ( $has_variants && null !== $key ) {

			$del_method = esc_attr( $_POST['deleteMethod'] );
			if ( 'soft' === $del_method ) {
				$prev_styles[ $key ]['multivariant']   = true;
				$settings                              = unserialize( $prev_styles[ $key ]['style_settings'] );
				$settings['live']                      = '0';
				$prev_styles[ $key ]['style_settings'] = serialize( $settings );
			} else {
				unset( $prev_styles[ $key ] );
				unset( $smile_variant_tests[ $style_id ] );
			}
			update_option( $option, $prev_styles );
			update_option( $variant_option, $smile_variant_tests );

			// Reset analytics data for style.
			cp_reset_analytics( $style_id );
		} else {

			if ( null !== $key ) {
				unset( $prev_styles[ $key ] );
				$modal_arrays = $prev_styles;
				$result       = update_option( $option, $modal_arrays );

				// Reset analytics data for style.
				cp_reset_analytics( $style_id );

			} else {
				foreach ( $smile_variant_tests as $key1 => $arrays ) {
					foreach ( $arrays as $key2 => $array ) {
						if ( $array['style_id'] == $style_id ) {
							$modal_arrays = $array;
							unset( $smile_variant_tests[ $key1 ][ $key2 ] );
							$modal_arrays = $smile_variant_tests;
							$result       = update_option( $variant_option, $modal_arrays );

							// Reset analytics data for style.
							cp_reset_analytics( $style_id );
							break;
						}
					}
				}
			}
		}
	}
	if ( $result ) {
		print(
			wp_json_encode(
				array(
					'message' => 'Deleted',
				)
			)
		);
		/* die() */
	} else {
		echo __( 'Unable to delete the style. Please Try again.', 'smile' );
	}

	/* die(); */
}