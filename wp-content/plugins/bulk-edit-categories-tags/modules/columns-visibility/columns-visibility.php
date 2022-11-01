<?php

if ( ! class_exists( 'WP_Sheet_Editor_Columns_Visibility' ) ) {

	/**
	 * Hide the columns of the spreadsheet editor that you don't need.
	 */
	class WP_Sheet_Editor_Columns_Visibility {

		private static $instance       = false;
		var $addon_helper              = null;
		var $removed_columns_key       = 'vgse_removed_columns';
		static $columns_visibility_key = 'vgse_columns_visibility';
		static $unfiltered_columns     = array();

		private function __construct() {

		}

		/**
		 * Creates or returns an instance of this class.
		 */
		static function get_instance() {
			if ( null == self::$instance ) {
				self::$instance = new WP_Sheet_Editor_Columns_Visibility();
				self::$instance->init();
			}
			return self::$instance;
		}

		public function init() {
			add_action( 'admin_init', array( $this, 'migrate_old_settings' ) );
			add_filter( 'vg_sheet_editor/columns/all_items', array( 'WP_Sheet_Editor_Columns_Visibility', 'filter_columns_for_visibility' ), 9999 );
			add_action( 'vg_sheet_editor/editor/before_init', array( $this, 'register_toolbar_items' ) );
			add_action( 'vg_sheet_editor/after_enqueue_assets', array( $this, 'enqueue_assets' ) );
			add_action( 'wp_ajax_vgse_update_columns_visibility', array( $this, 'update_columns_settings' ) );
			add_action( 'wp_ajax_vgse_remove_column', array( $this, 'remove_column' ) );
			add_action( 'wp_ajax_vgse_restore_columns', array( $this, 'restore_columns' ) );
			add_filter( 'vg_sheet_editor/columns/blacklisted_columns', array( $this, 'blacklist_removed_columns' ), 10, 2 );
			add_action( 'wp_ajax_vgse_load_columns_manager_content', array( $this, 'ajax_load_columns_manager_content' ) );
		}

		static function get_visibility_options( $post_type = null ) {
			$options = apply_filters( 'vg_sheet_editor/columns_visibility/options', get_option( self::$columns_visibility_key, array() ), $post_type );

			if ( $post_type ) {
				$options = isset( $options[ $post_type ] ) ? $options[ $post_type ] : array();
			}
			if ( empty( $options ) ) {
				$options = array();
			}
			return $options;
		}

		public function change_columns_status( $columns ) {
			$options = self::get_visibility_options();

			$changed = false;
			foreach ( $columns as $column ) {
				$status = ! empty( $column['status'] ) ? $column['status'] : 'enabled';
				if ( is_string( $column['post_types'] ) ) {
					$column['post_types'] = array( $column['post_types'] );
				}

				foreach ( $column['post_types'] as $post_type_key ) {
					if ( isset( $options[ $post_type_key ] ) && ! isset( $options[ $post_type_key ]['disabled'][ $column['key'] ] ) && ! isset( $options[ $post_type_key ][ $status ][ $column['key'] ] ) ) {
						$options[ $post_type_key ][ $status ][ $column['key'] ] = $column['name'];
						$changed = true;
					}
				}
			}

			if ( $changed ) {
				update_option( self::$columns_visibility_key, $options );
			}
		}

		public function migrate_old_settings() {
			if ( (int) get_option( self::$columns_visibility_key . '_migrated' ) ) {
				return;
			}

			// Migrate frontend editors
			if ( post_type_exists( 'vgse_editors' ) ) {
				$frontend_editors = new WP_Query(
					array(
						'post_type'      => 'vgse_editors',
						'posts_per_page' => -1,
						'fields'         => 'ids',
					)
				);
				foreach ( $frontend_editors->posts as $post_id ) {
					$old_settings = get_post_meta( $post_id, 'vgse_columns', true );
					$new_settings = $this->migrate_old_settings_raw( $old_settings );
					update_post_meta( $post_id, 'vgse_columns', $new_settings );
				}
			}

			// Migrate sheets
			$old_settings = VGSE()->options;
			$new_settings = $this->migrate_old_settings_raw( $old_settings );

			update_option( self::$columns_visibility_key, $new_settings );
			update_option( self::$columns_visibility_key . '_migrated', 1 );
		}

		public function migrate_old_settings_raw( $old_settings ) {
			$new_settings = array();

			foreach ( $old_settings as $key => $value ) {
				if ( strpos( $key, 'be_visibility_' ) !== false ) {
					if ( isset( $value['enabled']['placebo'] ) ) {
						unset( $value['enabled']['placebo'] );
					}
					if ( isset( $value['disabled']['placebo'] ) ) {
						unset( $value['disabled']['placebo'] );
					}
					$new_settings[ str_replace( 'be_visibility_', '', $key ) ] = $value;
				}
			}
			return $new_settings;
		}

		public function save_removed_columns( $columns, $post_type ) {
			$removed_columns               = $this->get_removed_columns( $post_type );
			$removed_columns[ $post_type ] = $columns;

			$removed_columns[ $post_type ] = array_unique( array_filter( $removed_columns[ $post_type ] ) );
			update_option( $this->removed_columns_key, $removed_columns );
		}

		public function get_removed_columns( $post_type ) {
			$removed_columns = get_option( $this->removed_columns_key, array() );

			if ( ! is_array( $removed_columns ) ) {
				$removed_columns = array();
			}
			if ( ! isset( $removed_columns[ $post_type ] ) ) {
				$removed_columns[ $post_type ] = array();
			}
			return $removed_columns;
		}

		public function blacklist_removed_columns( $blacklisted_columns, $post_type ) {
			$removed_columns = $this->get_removed_columns( $post_type );
			foreach ( $removed_columns[ $post_type ] as $removed_column_key ) {
				$blacklisted_columns[] = '^' . preg_quote($removed_column_key, '/') . '$';
			}
			return $blacklisted_columns;
		}

		/**
		 * Remove column
		 */
		public function restore_columns() {
			if ( empty( $_REQUEST['nonce'] ) || empty( $_REQUEST['post_type'] ) ) {
				wp_send_json_error( array( 'message' => __( 'Missing parameters.', VGSE()->textname ) ) );
			}

			if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'bep-nonce' ) || ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array( 'message' => __( 'You dont have enough permissions to execute this action.', VGSE()->textname ) ) );
			}
			$post_type = VGSE()->helpers->sanitize_table_key( $_REQUEST['post_type'] );
			$this->save_removed_columns( array(), $post_type );
			wp_send_json_success( array( 'message' => __( 'Columns restored successfully, please reload the page to see the restored columns and enable them', VGSE()->textname ) ) );
		}

		public function remove_column() {
			if ( empty( $_REQUEST['nonce'] ) || empty( $_REQUEST['post_type'] ) || empty( $_REQUEST['column_key'] ) ) {
				wp_send_json_error( array( 'message' => __( 'Missing parameters.', VGSE()->textname ) ) );
			}

			if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'bep-nonce' ) || ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array( 'message' => __( 'You dont have enough permissions to execute this action.', VGSE()->textname ) ) );
			}
			$post_type = VGSE()->helpers->sanitize_table_key( $_REQUEST['post_type'] );

			$removed_columns = $this->get_removed_columns( $post_type );

			if ( is_string( $_REQUEST['column_key'] ) ) {
				$column_keys = array( sanitize_text_field( $_REQUEST['column_key'] ) );
			} else {
				$column_keys = array_map( 'sanitize_text_field', $_REQUEST['column_key'] );
			}
			foreach ( $column_keys as $column_key ) {
				$removed_columns[ $post_type ][] = $column_key;
			}

			$this->save_removed_columns( $removed_columns[ $post_type ], $post_type );
			wp_send_json_success();
		}

		/**
		 * Save modified settings
		 */
		public function update_columns_settings() {
			if ( ! empty( $_REQUEST['extra_data'] ) ) {
				// When we render the form in the spreadsheet editor, we send the form data as JSON in extra_data because some servers have low limits for form post fields
				$_REQUEST = array_merge( $_REQUEST, json_decode( html_entity_decode( wp_unslash( $_REQUEST['extra_data'] ) ), true ) );
				unset( $_REQUEST['extra_data'] );
			}
			// Sanitize every field
			$post_type                = VGSE()->helpers->sanitize_table_key( $_REQUEST['post_type'] );
			$columns_keys             = isset( $_REQUEST['columns'] ) ? array_map( 'sanitize_text_field', $_REQUEST['columns'] ) : array();
			$columns_names            = isset( $_REQUEST['columns_names'] ) ? array_map( 'sanitize_text_field', $_REQUEST['columns_names'] ) : array();
			$disallowed_columns       = isset( $_REQUEST['disallowed_columns'] ) ? array_map( 'sanitize_text_field', $_REQUEST['disallowed_columns'] ) : array();
			$disallowed_columns_names = isset( $_REQUEST['disallowed_columns_names'] ) ? array_map( 'sanitize_text_field', $_REQUEST['disallowed_columns_names'] ) : array();

			if ( empty( $_REQUEST['nonce'] ) || empty( $post_type ) || empty( $columns_keys ) ) {
				wp_send_json_error( array( 'message' => __( 'Missing parameters.', VGSE()->textname ) ) );
			}

			if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'bep-nonce' ) || ! VGSE()->helpers->user_can_edit_post_type( $post_type ) ) {
				wp_send_json_error( array( 'message' => __( 'You dont have enough permissions to view this page.', VGSE()->textname ) ) );
			}

			$options = self::get_visibility_options();
			remove_filter( 'vg_sheet_editor/columns/all_items', array( 'WP_Sheet_Editor_Columns_Visibility', 'filter_columns_for_visibility' ), 9999 );
			if ( method_exists( VGSE()->helpers, 'get_unfiltered_provider_columns' ) ) {
				$post_type_columns = VGSE()->helpers->get_unfiltered_provider_columns( $post_type );
			} else {
				// Backwards compatibility
				$unfiltered_columns = self::$unfiltered_columns;
				$post_type_columns  = isset( $unfiltered_columns[ $post_type ] ) ? $unfiltered_columns[ $post_type ] : array();
				if ( empty( $post_type_columns ) ) {
					$post_type_columns = VGSE()->helpers->get_provider_columns( $post_type );
				}
			}

			add_filter( 'vg_sheet_editor/columns/all_items', array( 'WP_Sheet_Editor_Columns_Visibility', 'filter_columns_for_visibility' ), 9999 );

			$new_columns = array(
				'enabled'  => array(),
				'disabled' => array(),
			);

			foreach ( $columns_keys as $column_index => $column_key ) {
				$new_columns['enabled'][ $column_key ] = ( ! empty( $columns_names[ $column_index ] ) ) ? $columns_names[ $column_index ] : $column_key;
			}
			// Save all the registered columns not found in the enabled list as disabled
			foreach ( $post_type_columns as $key => $existing_column ) {
				if ( isset( $new_columns['enabled'][ $key ] ) ) {
					continue;
				}
				$new_columns['disabled'][ $key ] = $existing_column['title'];
			}
			// Edge case. Sometimes get_provider_columns() doesn't show some columns
			// so we save the disabled columns received from the request in addition to the above
			if ( ! empty( $disallowed_columns ) ) {
				foreach ( $disallowed_columns as $column_index => $column_key ) {
					if ( ! isset( $new_columns['disabled'][ $column_key ] ) ) {
						$new_columns['disabled'][ $column_key ] = ( ! empty( $disallowed_columns_names[ $column_index ] ) ) ? $disallowed_columns_names[ $column_index ] : $column_key;
					}
				}
			}

			$options[ $post_type ] = $new_columns;
			update_option( self::$columns_visibility_key, $options, false );

			do_action( 'vg_sheet_editor/columns_visibility/after_options_saved', $post_type, $options );

			wp_send_json_success(
				array(
					'post_type_editor_url' => VGSE()->helpers->get_editor_url( $post_type ),
				)
			);
		}

		/**
		 * Enqueue frontend assets
		 */
		public function enqueue_assets() {
			wp_enqueue_script( 'wp-sheet-editor-sortable', plugins_url( '/assets/vendor/Sortable/Sortable.min.js', __FILE__ ), array( 'jquery' ), VGSE()->version );
			wp_enqueue_script( 'wp-sheet-editor-columns-visibility-modal', plugins_url( '/assets/js/init.js', __FILE__ ), array( 'wp-sheet-editor-sortable' ), VGSE()->version );
		}

		public function ajax_load_columns_manager_content() {
			if ( empty( $_REQUEST['nonce'] ) || ! isset( $_REQUEST['is_editor_page'] ) || empty( $_REQUEST['post_type'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'bep-nonce' ) ) {
				wp_send_json_error( array( 'message' => __( 'You dont have enough permissions to execute this action.', VGSE()->textname ) ) );
			}
			$post_type = sanitize_text_field( $_REQUEST['post_type'] );
			if ( ! empty( $_REQUEST['is_editor_page'] ) ) {
				add_filter( 'vg_sheet_editor/is_editor_page', '__return_true' );
			}
			ob_start();
			$this->render_settings_modal( $post_type );
			$html = ob_get_clean();

			wp_send_json_success( array( 'html' => $html ) );
		}
		/**
		 * Render modal html
		 * @param str $post_type
		 */
		public function render_lazy_settings_modal( $post_type ) {
			$random_id = rand();
			include __DIR__ . '/views/lazy-form.php';
		}
		public function render_settings_modal( $post_type, $partial_form = false, $options = null, $current_url = null, $visible_columns = null ) {
			$nonce     = wp_create_nonce( 'bep-nonce' );
			$random_id = rand();

			// disable columns visibility filter temporarily
			if ( method_exists( VGSE()->helpers, 'get_unfiltered_provider_columns' ) ) {
				$columns = VGSE()->helpers->get_unfiltered_provider_columns( $post_type );
			} else {
				// Backwards compatibility
				$unfiltered_columns = self::$unfiltered_columns;
				$columns            = isset( $unfiltered_columns[ $post_type ] ) ? $unfiltered_columns[ $post_type ] : array();
			}

			$filtered_columns    = wp_list_filter(
				$columns,
				array(
					'allow_to_hide' => true,
				)
			);
			$not_allowed_columns = apply_filters(
				'vg_sheet_editor/columns_visibility/not_allowed_columns',
				array_keys(
					wp_list_filter(
						$columns,
						array(
							'allow_to_hide' => false,
						)
					)
				)
			);
			if ( ! $visible_columns ) {
				$visible_columns = VGSE()->helpers->get_provider_columns( $post_type );
			}

			if ( ! $options ) {
				$options = self::get_visibility_options();
			}

			if ( empty( $options[ $post_type ] ) ) {
				$options[ $post_type ] = array();
			}
			if ( empty( $options[ $post_type ]['enabled'] ) ) {
				$options[ $post_type ]['enabled'] = wp_list_pluck( $filtered_columns, 'title', 'key' );
			}

			// When we use the columns manager and switch between groups,
			// the saved columns might not include all the current columns so some columns
			// might not appear in the enabled nor disabled lists. Force them to appear at least as disabled.
			foreach ( $filtered_columns as $column_key => $column_settings ) {
				if ( ! isset( $visible_columns[ $column_key ] ) ) {
					$options[ $post_type ]['disabled'][ $column_key ] = $column_settings;
				}
			}

			$editor = VGSE()->helpers->get_provider_editor( $post_type );

			include __DIR__ . '/views/form.php';
		}

		/**
		 * Register toolbar item to edit columns visibility live on the spreadsheet
		 */
		public function register_toolbar_items( $editor ) {
			if ( ! is_admin() ) {
				return;
			}
			$post_types = $editor->args['enabled_post_types'];
			foreach ( $post_types as $post_type ) {
				$editor->args['toolbars']->register_item(
					'visibility_settings',
					array(
						'type'                  => 'button',
						'allow_in_frontend'     => false,
						'content'               => __( 'Hide / Display / Sort columns', VGSE()->textname ),
						'icon'                  => 'fa fa-sort',
						'toolbar_key'           => 'secondary',
						'extra_html_attributes' => 'data-remodal-target="modal-columns-visibility"',
						'parent'                => 'settings',
						'footer_callback'       => array( $this, 'render_lazy_settings_modal' ),
					),
					$post_type
				);
			}
		}

		/**
		 * Filter columns, remove the columns that were marked as hidden in the options page.
		 * @param array $columns
		 * @return array
		 */
		static function filter_columns_for_visibility( $columns, $options = null ) {
			if ( VGSE()->helpers->is_settings_page() ) {
				return $columns;
			}
			self::$unfiltered_columns = array_merge( $columns, self::$unfiltered_columns );

			if ( ! defined( 'WPSE_ONLY_EXPLICITLY_ENABLED_COLUMNS' ) && ! empty( VGSE()->options['dont_auto_enable_new_fields'] ) ) {
				define( 'WPSE_ONLY_EXPLICITLY_ENABLED_COLUMNS', true );
			}

			if ( ! $options ) {
				$options = self::get_visibility_options();
			}
			$current_post_type           = VGSE()->helpers->get_provider_from_query_string();
			$custom_enabled_columns_keys = ( ! empty( $_REQUEST['custom_enabled_columns'] ) ) ? array_filter( array_map( 'sanitize_text_field', explode( ',', $_REQUEST['custom_enabled_columns'] ) ) ) : array();

			$sorted_columns = array();
			foreach ( $columns as $post_type_key => $post_type ) {
				$settings = array();

				if ( isset( $options[ $post_type_key ] ) ) {
					$settings = $options[ $post_type_key ];
				}

				if ( ! isset( $sorted_columns[ $post_type_key ] ) ) {
					$sorted_columns[ $post_type_key ] = array();
				}

				// If zero columns are enabled, enable all
				if ( empty( $settings ) || empty( $settings['enabled'] ) ) {
					$sorted_columns[ $post_type_key ] = $post_type;
				}

				if ( empty( $settings['enabled'] ) ) {
					$settings['enabled'] = array();
				}
				if ( empty( $settings['disabled'] ) ) {
					$settings['disabled'] = array();
				}

				// If the request contains the parameter "custom_enabled_columns" and "post type", we use that
				// instead of the saved settings
				if ( $post_type_key === $current_post_type && ! empty( $custom_enabled_columns_keys ) ) {

					// If the user is not administrator, he can send "custom enabled columns" parameter in the URL
					// but only to hide columns, not enable hidden columns.
					if ( ! current_user_can( 'manage_options' ) ) {
						$all_enabled_columns = ( ! empty( $settings['enabled'] ) ) ? $settings['enabled'] : wp_list_pluck( $post_type, 'key', 'key' );

						$custom_enabled_columns_keys = array_intersect( $custom_enabled_columns_keys, array_keys( $all_enabled_columns ) );
					}
					// If we received custom columns but zero columns are allowed, just use the ID to avoid returning all columns
					if ( empty( $custom_enabled_columns_keys ) ) {
						$custom_enabled_columns_keys = array( 'ID' );
					}

					$sorted_columns[ $post_type_key ] = array();
					$all_settings_columns             = ( empty( $settings['enabled'] ) && empty( $settings['disabled'] ) ) ? wp_list_pluck( $post_type, 'key', 'key' ) : array_merge( $settings['enabled'], $settings['disabled'] );
					$settings['enabled']              = array_combine( $custom_enabled_columns_keys, $custom_enabled_columns_keys );
					$settings['disabled']             = array_diff( $all_settings_columns, $settings['enabled'] );
				}

				foreach ( $settings['enabled'] as $key => $enabled_column_label ) {

					if ( ! isset( $post_type[ $key ] ) ) {

						continue;
					}
					$sorted_columns[ $post_type_key ][ $key ] = $post_type[ $key ];
				}

				$disallow_to_hide = wp_list_filter(
					$post_type,
					array(
						'allow_to_hide' => false,
					)
				);

				$sorted_columns[ $post_type_key ] = array_merge( $disallow_to_hide, $sorted_columns[ $post_type_key ] );

				// Show columns that were added after the
				// columns visibility was saved, we hide columns that were
				// hidden explicitely only
				if ( ! defined( 'WPSE_ONLY_EXPLICITLY_ENABLED_COLUMNS' ) || ! WPSE_ONLY_EXPLICITLY_ENABLED_COLUMNS ) {
					$columns_sorted = ( count( $settings ) > 1 ) ? array_merge( $settings['enabled'], $settings['disabled'] ) : current( $settings );
					foreach ( $post_type as $key => $column ) {
						if ( ! isset( $columns_sorted[ $key ] ) ) {
							$sorted_columns[ $post_type_key ][ $key ] = $column;
						}
					}
				}
			}

			return $sorted_columns;
		}

		public function __set( $name, $value ) {
			$this->$name = $value;
		}

		public function __get( $name ) {
			return $this->$name;
		}

	}

	add_action( 'vg_sheet_editor/initialized', 'vgse_columns_visibility_init' );

	function vgse_columns_visibility_init() {
		WP_Sheet_Editor_Columns_Visibility::get_instance();
	}
}
