<?php
if ( ! class_exists( 'WP_Sheet_Editor_Bootstrap' ) ) {

	/**
	 * Bootstrap post type spreadsheet.
	 * Use only for a post type. You can create new class
	 * (extending this) for a custom bootstrap
	 */
	class WP_Sheet_Editor_Bootstrap {

		var $allowed_post_types        = null;
		var $enabled_post_types        = array();
		var $columns                   = null;
		var $toolbars                  = null;
		var $quick_access_rendered     = false;
		var $settings                  = array();
		static $initialized_post_types = array();

		public function __construct( $args = array() ) {
			$defaults       = array(
				'allowed_post_types'             => VGSE()->helpers->get_allowed_post_types(),
				'enabled_post_types'             => VGSE()->helpers->get_enabled_post_types(),
				'register_toolbars'              => true,
				'register_columns'               => true,
				'post_type_labels'               => null,
				'register_taxonomy_columns'      => true,
				'register_admin_menus'           => true,
				'register_spreadsheet_editor'    => true,
				'only_allowed_spreadsheets'      => false,
				'current_provider'               => VGSE()->helpers->get_provider_from_query_string(),
				'is_generic_post_type_bootstrap' => true,
			);
			$this->settings = apply_filters( 'vg_sheet_editor/bootstrap/settings', wp_parse_args( $args, $defaults ) );

			// Set allowed post types
			$this->allowed_post_types = $this->settings['allowed_post_types'];

			$this->enabled_post_types = $this->settings['enabled_post_types'];

			// Allow other plugins to skip post type bootstrapping
			if ( ! apply_filters( 'vg_sheet_editor/allow_to_bootstrap', true, $this->settings ) ) {
				return;
			}

			$current_post_type = $this->settings['current_provider'];
			if ( $this->settings['only_allowed_spreadsheets'] && VGSE()->helpers->is_editor_page() && ! empty( $current_post_type ) && ! isset( $this->allowed_post_types[ $current_post_type ] ) ) {
				wp_die( __( 'Error 8391. You dont have enough permissions to view this page.', VGSE()->textname ) );
			}

			// Only initialize spreadsheets once, don't initialize twice
			// This fix was added because the frontend sheet and backend sheets were initialized and
			// some columns were registered in one instance and not on the other, so we had some missing columns
			// But the fix was removed because the backend sheet would not initialize and it would not be accesible anymore
			//          $this->enabled_post_types = array_diff($this->enabled_post_types, self::$initialized_post_types);
			//          if (empty($this->enabled_post_types)) {
			//              return;
			//          }
			//          self::$initialized_post_types = array_merge(self::$initialized_post_types, $this->enabled_post_types);

			$this->columns  = ( $this->settings['register_columns'] ) ? new WP_Sheet_Editor_Columns() : null;
			$this->toolbars = ( $this->settings['register_toolbars'] ) ? clone($this->_register_toolbars( $this->enabled_post_types, new WP_Sheet_Editor_Toolbar() )) : null;

			if ( ! empty( $this->enabled_post_types ) && $this->settings['register_spreadsheet_editor'] ) {
				$this->_register_columns();

				$freezed_columns = false;
				if ( isset( VGSE()->options['be_fix_columns_left'] ) ) {
					$freezed_columns = ( is_numeric( VGSE()->options['be_fix_columns_left'] ) ) ? (int) VGSE()->options['be_fix_columns_left'] : 2;
				}

				new WP_Sheet_Editor_Factory(
					array(
						'posts_per_page'       => ( ! empty( VGSE()->options ) && ! empty( VGSE()->options['be_posts_per_page'] ) ) ? (int) VGSE()->options['be_posts_per_page'] : 20,
						'save_posts_per_page'  => ( ! empty( VGSE()->options ) && ! empty( VGSE()->options['be_posts_per_page_save'] ) ) ? (int) VGSE()->options['be_posts_per_page_save'] : 4,
						'wait_between_batches' => ( ! empty( VGSE()->options ) && ! empty( VGSE()->options['be_timeout_between_batches'] ) ) ? (int) VGSE()->options['be_timeout_between_batches'] : 6,
						'fixed_columns_left'   => $freezed_columns ? $freezed_columns : null,
						'provider'             => $current_post_type,
						'provider_key'         => 'post_type',
						'admin_menu'           => ( $this->settings['register_admin_menus'] ) ? $this->_register_admin_menu() : null,
						'columns'              => $this->columns,
						'toolbars'             => $this->toolbars,
						'enabled_post_types'   => $this->enabled_post_types,
					)
				);

				if ( $this->settings['register_admin_menus'] ) {
					add_action( 'admin_footer', array( $this, 'render_quick_access' ) );
				}
			}
		}

		public function render_quick_access() {
			$screen               = get_current_screen();
			$is_posts_list        = $screen->base === 'edit' && ! empty( $screen->post_type );
			$is_media_upload_page = $screen->base === 'upload';
			if ( ! $this->quick_access_rendered && ( $is_posts_list || $is_media_upload_page ) && in_array( $screen->post_type, $this->enabled_post_types ) ) {
				$transient_key               = VGSE()->helpers->get_current_query_session_id();
				$url                         = esc_url(
					add_query_arg(
						array(
							'wpse_session_query' => $transient_key,
						),
						VGSE()->helpers->get_editor_url( $screen->post_type )
					)
				);
				$this->quick_access_rendered = true;
				?>
				<script>jQuery(window).on('load', function () {
						if (!jQuery('#wpse-quick-access').length) {
							jQuery('.page-title-action, .fusion-split-page-title-action').last().after('<a href=<?php echo json_encode( esc_url( $url ) ); ?> class="page-title-action" id="wpse-quick-access"><?php _e( 'Open in a Spreadsheet', VGSE()->textname ); ?></a>');
						}
					});</script>

				<?php
			}
		}

		public function _register_admin_menu() {
			$admin_menu = array();

			if ( ! isset( $GLOBALS['wpse_registered_menus'] ) ) {
				$GLOBALS['wpse_registered_menus'] = array();
			}
			foreach ( $this->enabled_post_types as $post_type_key ) {
				if ( in_array( $post_type_key, $GLOBALS['wpse_registered_menus'] ) ) {
					continue;
				}
				$GLOBALS['wpse_registered_menus'][] = $post_type_key;
				$page_slug                          = 'vgse-bulk-edit-' . $post_type_key;
				$post_type_label                    = ( ! empty( $this->settings['post_type_labels'][ $post_type_key ] ) ) ? $this->settings['post_type_labels'][ $post_type_key ] : VGSE()->helpers->get_post_type_label( $post_type_key );

				$required_capability = VGSE()->helpers->get_edit_spreadsheet_capability( $post_type_key );
				$admin_menu[]        = array(
					'type'       => 'submenu',
					'name'       => sprintf( __( 'Edit %s', VGSE()->textname ), esc_html( $post_type_label ) ),
					'slug'       => $page_slug,
					'capability' => $required_capability,
				);
				if ( $post_type_key === 'post' ) {
					$parent = 'edit.php';
				} elseif ( $post_type_key === 'attachment' ) {
					$parent = 'upload.php';
				} else {
					$parent = 'edit.php?post_type=' . $post_type_key;
				}
				$admin_menu[] = array(
					'type'         => 'submenu',
					'parent'       => $parent,
					'name'         => __( 'Sheet Editor', VGSE()->textname ),
					'slug'         => 'admin.php?page=' . $page_slug,
					'treat_as_url' => true,
					'capability'   => $required_capability,
				);
			}

			return $admin_menu;
		}

		public function render_support_modal( $provider ) {
			require VGSE_DIR . '/views/support-modal.php';
		}

		public function render_advanced_settings_modal( $provider ) {
			require VGSE_DIR . '/views/advanced-settings-modal.php';
		}

		public function render_extensions_modal( $provider ) {
			require VGSE_DIR . '/views/extensions-modal.php';
		}

		/**
		 * Register core toolbar items
		 */
		public function _register_toolbars( $post_types = array(), $toolbars = null ) {
			if ( empty( $toolbars ) ) {
				$toolbars = new WP_Sheet_Editor_Toolbar();
			}

			foreach ( $post_types as $post_type ) {
				// secondary
				$toolbars->register_item(
					'settings',
					array(
						'type'                    => 'button',
						'content'                 => __( 'Settings', VGSE()->textname ),
						'toolbar_key'             => 'secondary',
						'allow_in_frontend'       => false,
						'require_click_to_expand' => true,
					),
					$post_type
				);
				if ( current_user_can( 'manage_options' ) ) {
					$toolbars->register_item(
						'advanced_settings',
						array(
							'type'                  => 'button',
							'content'               => __( 'Advanced settings', VGSE()->textname ),
							'toolbar_key'           => 'secondary',
							'allow_in_frontend'     => false,
							'parent'                => 'settings',
							'extra_html_attributes' => 'data-remodal-target="modal-advanced-settings"',
							'footer_callback'       => array( $this, 'render_advanced_settings_modal' ),
						),
						$post_type
					);
				}
				if ( empty( VGSE()->options['disable_help_toolbar'] ) ) {
					$toolbars->register_item(
						'support',
						array(
							'type'                  => 'button',
							'content'               => __( 'Help', VGSE()->textname ),
							'toolbar_key'           => 'secondary',
							'extra_html_attributes' => 'data-remodal-target="modal-support"',
							'footer_callback'       => array( $this, 'render_support_modal' ),
						),
						$post_type
					);
				}

				if ( apply_filters( 'vg_sheet_editor/extensions/is_toolbar_allowed', true ) ) {
					$toolbars->register_item(
						'extensions',
						array(
							'type'                  => 'button',
							'content'               => __( 'Extensions', VGSE()->textname ),
							'toolbar_key'           => 'secondary',
							'allow_in_frontend'     => false,
							'extra_html_attributes' => 'data-remodal-target="modal-extensions"',
							'footer_callback'       => array( $this, 'render_extensions_modal' ),
						),
						$post_type
					);
				}
				$sort_options = VGSE()->helpers->get_sheet_sort_options( $post_type );
				if ( ! empty( $sort_options ) && VGSE()->helpers->has_paid_addon_active() ) {
					$toolbars->register_item(
						'default_sort',
						array(
							'type'                    => 'button',
							'content'                 => __( 'Global sort', VGSE()->textname ),
							'toolbar_key'             => 'secondary',
							'allow_in_frontend'       => false,
							'require_click_to_expand' => true,
						),
						$post_type
					);
					$toolbars->register_item(
						'default_sort_select',
						array(
							'parent'       => 'default_sort',
							'type'         => 'html',
							'help_tooltip' => __( 'We\'ll reload the spreadsheet when you change this option.', VGSE()->textname ),
							'content'      => array( $this, 'get_default_sort_select' ),
							'label'        => __( 'Sort', VGSE()->textname ),
						),
						$post_type
					);
				}

				// primary
				$toolbars->register_item(
					'save',
					array(
						'allow_to_hide'         => false,
						'type'                  => 'button', // html | switch | button
						'icon'                  => 'fa fa-save', // Font awesome icon name , including font awesome prefix: fa fa-XXX. Only for type=button.
						'content'               => __( 'Save', VGSE()->textname ), // if type=button : button label | if type=html : html string.
						'css_class'             => 'primary button-only-icon wpse-save', // .button will be added to all items also.
						'extra_html_attributes' => 'data-remodal-target="bulk-save"', // useful for adding data attributes
					),
					$post_type
				);

				$toolbars->register_item(
					'add_rows',
					array(
						'type'         => 'html', // html | switch | button
						'content'      => '<button name="addrow" id="addrow" class="button button-only-icon"><i class="fa fa-plus"></i> ' . __( 'Add new', VGSE()->textname ) . '</button><input type="number" min="1" value="1" class="number_rows" /> <input type="hidden" id="post_type_new_row" value="' . $post_type . '" />', // if type=button : button label | if type=html : html string.
						'help_tooltip' => __('You can create new items here', VGSE()->textname),
						'tooltip_size' => 'small',
					),
					$post_type
				);

				$toolbars->register_item(
					'load',
					array(
						'allow_to_hide'   => false,
						'type'            => 'button', // html | switch | button
						'content'         => __( 'Load', VGSE()->textname ),
						'container_class' => 'hidden',
					),
					$post_type
				);
				$toolbars->register_item(
					'exit_full_screen',
					array(
						'allow_to_hide'     => false,
						'icon'              => 'fa fa-remove',
						'type'              => 'button', // html | switch | button
						'content'           => __( 'Exit Full Screen', VGSE()->textname ),
						'container_class'   => 'right-toolbar-item',
						'css_class'         => 'wpse-full-screen-toggle',
						'allow_in_frontend' => false,
					),
					$post_type
				);
				$toolbars->register_item(
					'cells_format',
					array(
						'type'          => 'switch', // html | switch | button
						'content'       => __( 'Show cells as simple text', VGSE()->textname ),
						'id'            => 'formato',
						'toolbar_key'   => 'secondary',
						'help_tooltip'  => __( 'By default dates show in a calendar, post content has a text editor option, images show preview, etc. you can enable this option to display everything as plain text and disable the fancy formatting.', VGSE()->textname ),
						'default_value' => false,
						'parent'        => 'settings',
					),
					$post_type
				);
				if ( empty( VGSE()->options['enable_pagination'] ) ) {
					$toolbars->register_item(
						'infinite_scroll',
						array(
							'type'          => 'switch', // html | switch | button
							'content'       => __( 'Load more on scroll', VGSE()->textname ),
							'id'            => 'infinito',
							'toolbar_key'   => ( defined( 'VGSE_WC_FILE' ) ) ? 'secondary' : 'primary',
							'help_tooltip'  => __( 'When this is enabled more items will be loaded to the bottom of the spreadsheet when you reach the end of the page', VGSE()->textname ),
							'default_value' => VGSE()->options['be_load_items_on_scroll'] == true,
							'parent'        => 'settings',
						),
						$post_type
					);
				}
				if ( current_user_can( 'manage_options' ) ) {
					$toolbars->register_item(
						'rescan_db',
						array(
							'type'              => 'button',
							'content'           => __( 'Scan DB to find fields', VGSE()->textname ),
							'id'                => 'rescan_db',
							'allow_in_frontend' => false,
							'toolbar_key'       => 'secondary',
							'help_tooltip'      => __( 'We can scan the database, find new fields, and create columns automatically for the supported fields.', VGSE()->textname ),
							'parent'            => 'settings',
							'url'               => esc_url( add_query_arg( 'wpse_rescan_db_fields', VGSE()->helpers->get_provider_from_query_string() ) ),
						),
						$post_type
					);
				}
				if ( ! empty( VGSE()->options['enable_auto_saving'] ) ) {
					$toolbars->register_item(
						'auto_saving_status',
						array(
							'toolbar_key'     => 'secondary',
							'allow_to_hide'   => false,
							'container_class' => 'right-toolbar-item',
							'type'            => 'html',
							'content'         => '<a href="#" data-remodal-target="bulk-save" data-saved-changes="' . esc_attr__( 'All changes saved.', VGSE()->textname ) . '" data-saving-changes="' . esc_attr__( 'Saving changes, don\'t close this page.', VGSE()->textname ) . '"  data-unsaved-changes="' . esc_attr__( 'Some changes are not saved yet.', VGSE()->textname ) . '">' . __( 'All changes saved.', VGSE()->textname ) . '</a>',
							'label'           => __( 'All changes saved.', VGSE()->textname ),
						),
						$post_type
					);
				}
			}

			do_action( 'vg_sheet_editor/toolbar/core_items_registered' );

			return $toolbars;
		}
		public function get_default_sort_select( $toolbar_item, $post_type ) {

			$sort_options = VGSE()->helpers->get_sheet_sort_options( $post_type );
			$sort_select  = '';
			if ( ! $sort_options ) {
				return $sort_select;
			}
			$current_value = ! empty( VGSE()->options[ 'default_sortby_' . $post_type ] ) ? VGSE()->options[ 'default_sortby_' . $post_type ] : '';

			$sort_select = '<select name="default_sortby_' . esc_attr( $post_type ) . '" data-silent-action="0" data-reload-after-success="1" class="wpse-set-settings default-sort-select select2">';
			foreach ( $sort_options as $key => $label ) {
				$sort_select .= '<option value="' . esc_attr( $key ) . '" ' . selected( $current_value, $key, false ) . '>' . esc_html( $label ) . '</option>';
			}
			$sort_select .= '</select>';

			return $sort_select;
		}

		/**
		 * IMPORTANT. We copied the function from wp-admin/includes/post.php
		 * because we need the function before wp loads the file or in pages where
		 * WP core doesn't load it.
		 *
		 * We can't just require the post.php file because it causes error 500 when WP
		 * or other plugins load the file later
		 *
		 * Return whether a post type is compatible with the block editor.
		 *
		 * The block editor depends on the REST API, and if the post type is not shown in the
		 * REST API, then it won't work with the block editor.
		 *
		 * @since 5.0.0
		 *
		 * @param string $post_type The post type.
		 * @return bool Whether the post type can be edited with the block editor.
		 */
		public function use_block_editor_for_post_type( $post_type ) {
			if ( $post_type === 'product' ) {
				return false;
			}
			if ( ! post_type_exists( $post_type ) ) {
				return false;
			}

			if ( ! post_type_supports( $post_type, 'editor' ) ) {
				return false;
			}

			// Added support for the disable_gutenberg plugin
			if ( function_exists( 'disable_gutenberg' ) && disable_gutenberg() ) {
				return false;
			}
			if ( class_exists( 'Classic_Editor' ) ) {
				return false;
			}

			$post_type_object = get_post_type_object( $post_type );
			if ( $post_type_object && ! $post_type_object->show_in_rest ) {
				return false;
			}

			/**
			 * Filter whether a post is able to be edited in the block editor.
			 *
			 * @since 5.0.0
			 *
			 * @param bool   $use_block_editor  Whether the post type can be edited or not. Default true.
			 * @param string $post_type         The post type being checked.
			 */
			return true;
		}

		/**
		 * Register core columns
		 */
		public function _register_columns() {
			if ( ! is_object( $this->columns ) ) {
				return;
			}

			$post_types = $this->enabled_post_types;
			foreach ( $post_types as $post_type ) {
				$this->columns->register_item(
					'ID',
					$post_type,
					array(
						'data_type'         => 'post_data', //String (post_data,post_meta|meta_data)
						'unformatted'       => array(
							'data'     => 'ID',
							'renderer' => 'html',
							'readOnly' => true,
						), //Array (Valores admitidos por el plugin de handsontable)
						'column_width'      => 75, //int (Ancho de la columna)
						'title'             => __( 'ID', VGSE()->textname ), //String (Titulo de la columna)
						'type'              => '', // String (Es para saber si serÃ¡ un boton que abre popup, si no dejar vacio) boton_tiny|boton_gallery|boton_gallery_multiple|(vacio)
						'supports_formulas' => false,
						'allow_to_hide'     => false,
						'allow_to_save'     => false,
						'allow_to_rename'   => false,
						'is_locked'         => true,
						'formatted'         => array(
							'data'     => 'ID',
							'renderer' => 'html',
							'readOnly' => true,
						),
					)
				);
				$this->columns->register_item(
					'post_title',
					$post_type,
					array(
						'data_type'         => 'post_data',
						'unformatted'       => array( 'data' => 'post_title' ),
						'column_width'      => 300,
						'title'             => __( 'Title', VGSE()->textname ),
						'type'              => '',
						'supports_formulas' => true,
						'formatted'         => array(
							'data'     => 'post_title',
							'renderer' => 'html',
						),
						'allow_to_hide'     => true,
						'allow_to_rename'   => true,
					)
				);
				$this->columns->register_item(
					'post_name',
					$post_type,
					array(
						'data_type'         => 'post_data', //String (post_data,post_meta|meta_data)
						'column_width'      => 300, //int (Ancho de la columna)
						'title'             => __( 'URL Slug', VGSE()->textname ), //String (Titulo de la columna)
						'type'              => '', // String (Es para saber si serÃ¡ un boton que abre popup, si no dejar vacio) boton_tiny|boton_gallery|boton_gallery_multiple|(vacio)
						'supports_formulas' => true,
						'allow_to_hide'     => true,
						'allow_to_save'     => true,
						'allow_to_rename'   => true,
						'is_locked'         => true,
						'lock_template_key' => 'enable_lock_cell_template',
					)
				);
				global $wp_version;
				if ( version_compare( $wp_version, '5.0', '>=' ) && $this->use_block_editor_for_post_type( $post_type ) ) {
					// Disable wpautop when using gutenberg because it breaks the block markup
					VGSE()->options['be_disable_wpautop'] = true;
					$post_content_args                    = array(
						'data_type'                => 'post_data',
						'column_width'             => 200,
						'title'                    => __( 'Content', VGSE()->textname ),
						'supports_formulas'        => true,
						'formatted'                => array(
							'data'              => 'post_content',
							'renderer'          => 'wp_tinymce',
							'wpse_template_key' => 'gutenberg_cell_template',
						),
						'allow_to_hide'            => true,
						'allow_to_save'            => true,
						'allow_to_rename'          => true,
						'edit_modal_id'            => 'vgse-modal-editor-' . wp_generate_password( 5, false ),
						'edit_modal_description'   => __( 'Use this editor to edit the content only, other fields like tags and categories should be edited on the spreadsheet.', VGSE()->textname ),
						'edit_modal_save_action'   => 'js_function_name:vgseGutenbergEditToCell,vgse_save_gutenberg_content',
						'edit_modal_cancel_action' => 'js_function_name:vgseCancelGutenbergEdit',
						'metabox_show_selector'    => '#wpcontent',
						'metabox_value_selector'   => 'js_function_name:vgseGetGutenbergContent',
					);
					$this->columns->register_item( 'post_content', $post_type, $post_content_args );
				} else {
					if ( post_type_supports( $post_type, 'editor' ) || $post_type === 'attachment' ) {
						$this->columns->register_item(
							'post_content',
							$post_type,
							array(
								'data_type'         => 'post_data',
								'column_width'      => 200,
								'title'             => __( 'Content', VGSE()->textname ),
								'supports_formulas' => true,
								'formatted'         => array(
									'data'              => 'post_content',
									'renderer'          => 'wp_tinymce',
									'wpse_template_key' => 'tinymce_cell_template',
								),
								'allow_to_hide'     => true,
								'allow_to_save'     => true,
								'allow_to_rename'   => true,
							)
						);
					}
				}

				$this->columns->register_item(
					'open_wp_editor',
					$post_type,
					array(
						'data_type'                => 'post_data',
						'unformatted'              => array(
							'renderer' => 'wp_external_button',
							'readOnly' => true,
						),
						'column_width'             => 115,
						'title'                    => __( 'WP Editor', VGSE()->textname ),
						'type'                     => 'external_button',
						'supports_formulas'        => false,
						'formatted'                => array(
							'renderer' => 'wp_external_button',
							'readOnly' => true,
						),
						'allow_to_hide'            => true,
						'allow_to_save'            => false,
						'allow_to_rename'          => true,
						'external_button_template' => admin_url( 'post.php?post={ID}&action=edit' ),
					)
				);
				$this->columns->register_item(
					'view_post',
					$post_type,
					array(
						'data_type'                => 'post_data',
						'unformatted'              => array(
							'data'     => 'view_post',
							'renderer' => 'wp_external_button',
							'readOnly' => true,
						),
						'column_width'             => 85,
						'title'                    => __( 'View', VGSE()->textname ),
						'type'                     => 'external_button',
						'supports_formulas'        => false,
						'formatted'                => array(
							'data'     => 'view_post',
							'renderer' => 'wp_external_button',
							'readOnly' => true,
						),
						'allow_to_hide'            => true,
						'allow_to_save'            => false,
						'allow_to_rename'          => true,
						'external_button_template' => '{post_url}',
					)
				);
				$this->columns->register_item(
					'post_date',
					$post_type,
					array(
						'data_type'         => 'post_data',
						'unformatted'       => array( 'data' => 'post_date' ),
						'column_width'      => 155,
						'title'             => __( 'Date', VGSE()->textname ),
						'type'              => '',
						'supports_formulas' => true,
						'formatted'         => array(
							'type'             => 'date',
							'dateFormat'       => 'YYYY-MM-DD HH:mm:ss',
							'correctFormat'    => true,
							'defaultDate'      => date( 'Y-m-d H:i:s' ),
							'datePickerConfig' => array(
								'firstDay'       => 0,
								'showWeekNumber' => true,
								'numberOfMonths' => 1,
							),
						),
						'allow_to_hide'     => true,
						'allow_to_rename'   => true,
						'value_type'        => 'date',
					)
				);
				$this->columns->register_item(
					'post_modified',
					$post_type,
					array(
						'data_type'         => 'post_data',
						'column_width'      => 212,
						'title'             => __( 'Modified Date', VGSE()->textname ),
						'type'              => '',
						'supports_formulas' => true,
						'allow_to_hide'     => true,
						'allow_to_save'     => true,
						'allow_to_rename'   => true,
						'is_locked'         => true,
						'lock_template_key' => 'enable_lock_cell_template',
						'value_type'        => 'date',
					)
				);
				if ( post_type_supports( $post_type, 'author' ) ) {
					$this->columns->register_item(
						'post_author',
						$post_type,
						array(
							'data_type'         => 'post_data',
							'unformatted'       => array( 'data' => 'post_author' ),
							'column_width'      => 120,
							'title'             => __( 'Author', VGSE()->textname ),
							'type'              => '',
							'supports_formulas' => true,
							'formatted'         => array(
								'type'   => 'autocomplete',
								'source' => 'searchUsers',
							),
							'allow_to_hide'     => true,
							'allow_to_rename'   => true,
						)
					);
				}
				if ( post_type_supports( $post_type, 'excerpt' ) || $post_type === 'attachment' ) {
					$this->columns->register_item(
						'post_excerpt',
						$post_type,
						array(
							'data_type'         => 'post_data',
							'unformatted'       => array( 'data' => 'post_excerpt' ),
							'column_width'      => 400,
							'title'             => __( 'Excerpt', VGSE()->textname ),
							'type'              => '',
							'supports_formulas' => true,
							'formatted'         => array( 'data' => 'post_excerpt' ),
							'allow_to_hide'     => true,
							'allow_to_rename'   => true,
						)
					);
				}

				$post_statuses = VGSE()->helpers->get_data_provider( $post_type )->get_statuses();

				if ( VGSE()->helpers->get_current_provider()->is_post_type && VGSE()->helpers->user_can_delete_post_type( $post_type ) ) {
					$post_statuses['delete'] = __( 'Delete completely', VGSE()->textname );
				}

				$this->columns->register_item(
					'post_status',
					$post_type,
					array(
						'data_type'         => 'post_data',
						'unformatted'       => array( 'data' => 'post_status' ),
						'column_width'      => 100,
						'title'             => __( 'Status', VGSE()->textname ),
						'type'              => '',
						'supports_formulas' => true,
						'formatted'         => array(
							'data'          => 'post_status',
							'editor'        => 'select',
							'selectOptions' => $post_statuses,
						),
						'allow_to_hide'     => true,
						'allow_to_rename'   => true,
					)
				);
				if ( post_type_supports( $post_type, 'comments' ) ) {
					$this->columns->register_item(
						'comment_status',
						$post_type,
						array(
							'data_type'         => 'post_data',
							'unformatted'       => array( 'data' => 'comment_status' ),
							'column_width'      => 100,
							'title'             => __( 'Comments', VGSE()->textname ),
							'type'              => '',
							'supports_formulas' => true,
							'formatted'         => array(
								'data'              => 'comment_status',
								'type'              => 'checkbox',
								'checkedTemplate'   => 'open',
								'uncheckedTemplate' => 'closed',
							),
							'default_value'     => 'open',
							'allow_to_hide'     => true,
							'allow_to_rename'   => true,
						)
					);
				}

				if ( ( post_type_supports( $post_type, 'page-attributes' ) && $post_type !== 'attachment' ) || $post_type === apply_filters( 'vg_sheet_editor/woocommerce/product_post_type_key', 'product' ) ) {

					$this->columns->register_item(
						'post_parent',
						$post_type,
						array(
							'data_type'         => 'post_data',
							'unformatted'       => array( 'data' => 'post_parent' ),
							'column_width'      => 210,
							'title'             => __( 'Page Parent', VGSE()->textname ),
							'type'              => '',
							'supports_formulas' => true,
							'formatted'         => array(
								'data'   => 'post_parent',
								'type'   => 'autocomplete',
								'source' => 'searchPostByKeyword',
							),
							'allow_to_hide'     => true,
							'allow_to_rename'   => true,
						)
					);
				}
				$this->columns->register_item(
					'menu_order',
					$post_type,
					array(
						'data_type'         => 'post_data', //String (post_data,post_meta|meta_data)
						'column_width'      => 80, //int (Ancho de la columna)
						'title'             => __( 'Order', VGSE()->textname ), //String (Titulo de la columna)
						'type'              => '',
						'supports_formulas' => true,
						'allow_to_hide'     => true,
						'allow_to_save'     => true,
						'allow_to_rename'   => true,
					)
				);
				if ( post_type_supports( $post_type, 'thumbnail' ) ) {
					$this->columns->register_item(
						'_thumbnail_id',
						$post_type,
						array(
							'data_type'         => 'meta_data',
							'unformatted'       => array( 'data' => '_thumbnail_id' ),
							'column_width'      => 160,
							'supports_formulas' => true,
							'title'             => __( 'Featured Image', VGSE()->textname ),
							'type'              => 'boton_gallery', //boton_gallery|boton_gallery_multiple (Multiple para galeria)
							'formatted'         => array( 'data' => '_thumbnail_id' ),
							'allow_to_hide'     => true,
							'allow_to_save'     => true,
							'allow_to_rename'   => true,
						)
					);
				}

				if ( $this->settings['register_taxonomy_columns'] ) {
					$taxonomies = get_object_taxonomies( $post_type, 'objects' );

					if ( ! empty( $taxonomies ) && is_array( $taxonomies ) ) {
						$term_field = ( ! empty( VGSE()->options['manage_taxonomy_columns_term_slugs'] ) ) ? 'slug' : 'name';
						foreach ( $taxonomies as $taxonomy ) {

							if ( ! $taxonomy->show_ui && $taxonomy->name !== 'post_format' ) {
								continue;
							}
							if ( empty( VGSE()->options['be_taxonomy_cell_renderer'] ) ) {
								$formatted = array(
									'data'          => $taxonomy->name,
									'editor'        => 'wp_chosen',
									'width'         => 150,
									'selectOptions' => array(),
									'chosenOptions' => array(
										'multiple'        => true,
										'search_contains' => true,
										'create_option'   => true,
										'skip_no_results' => true,
										'persistent_create_option' => true,
										'data'            => array(),
										'ajaxParams'      => array(
											'action'       => 'vgse_get_taxonomy_terms',
											'taxonomy_key' => $taxonomy->name,
										),
									),
								);
							} else {
								$hierarchy_tip = is_taxonomy_hierarchical( $taxonomy->name ) ? __( '. Add child categories using this format: Parent > child1 > child2', VGSE()->textname ) : '';
								$formatted     = array(
									'data'   => $taxonomy->name,
									'type'   => 'autocomplete',
									'source' => 'loadTaxonomyTerms',
								);

								$formatted['comment'] = array( 'value' => __( 'Enter multiple terms separated by commas', VGSE()->textname ) . $hierarchy_tip );
							}

							$this->columns->register_item(
								$taxonomy->name,
								$post_type,
								array(
									'data_type'         => 'post_terms',
									'unformatted'       => array( 'data' => $taxonomy->name ),
									'column_width'      => 150,
									'title'             => $taxonomy->label,
									'type'              => '',
									'supports_formulas' => true,
									'formatted'         => $formatted,
									'allow_to_hide'     => true,
									'allow_to_rename'   => true,
								)
							);
						}
					}
				}
				$required_capability_for_post_type_column = apply_filters( 'vg_sheet_editor/bootstrap/required_capability_for_post_type_column', 'manage_options' );
				if ( ! $required_capability_for_post_type_column || current_user_can( $required_capability_for_post_type_column ) ) {
					$this->columns->register_item(
						'post_type',
						$post_type,
						array(
							'data_type'         => 'post_data', //String (post_data,post_meta|meta_data)
							'column_width'      => 150, //int (Ancho de la columna)
							'title'             => __( 'Post type', VGSE()->textname ), //String (Titulo de la columna)
							'type'              => '',
							'supports_formulas' => true,
							'allow_to_hide'     => true,
							'allow_to_save'     => true,
							'allow_to_rename'   => true,
							'is_locked'         => true,
							'lock_template_key' => 'enable_lock_cell_template',
							'formatted'         => array(
								'data'          => 'post_type',
								'editor'        => 'select',
								'selectOptions' => apply_filters( 'vg_sheet_editor/bootstrap/post_type_column_dropdown_options', VGSE()->helpers->get_all_post_types_names(), $post_type, $this ),
							),
						)
					);
				}
				if ( current_user_can( 'manage_options' ) ) {
					if ( defined( 'VGSE_ANY_PREMIUM_ADDON' ) && VGSE_ANY_PREMIUM_ADDON ) {
						$this->columns->register_item(
							'post_password',
							$post_type,
							array(
								'data_type'         => 'post_data', //String (post_data,post_meta|meta_data)
								'column_width'      => 80, //int (Ancho de la columna)
								'title'             => __( 'Password', VGSE()->textname ), //String (Titulo de la columna)
								'type'              => '',
								'supports_formulas' => true,
								'allow_to_hide'     => true,
								'allow_to_save'     => true,
								'allow_to_rename'   => true,
							)
						);
					}
				}
			}

			do_action( 'vg_sheet_editor/core_columns_registered' );
		}

		public function __set( $name, $value ) {
			$this->$name = $value;
		}

		public function __get( $name ) {
			return $this->$name;
		}

	}

}
