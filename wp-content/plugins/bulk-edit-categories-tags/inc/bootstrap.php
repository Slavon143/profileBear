<?php
if (!class_exists('WPSE_Taxonomy_Terms_Spreadsheet_Bootstrap')) {

	class WPSE_Taxonomy_Terms_Spreadsheet_Bootstrap extends WP_Sheet_Editor_Bootstrap {

		public function __construct($args) {
			parent::__construct($args);

			if ($this->settings['register_admin_menus']) {
				foreach ($this->enabled_post_types as $taxonomy) {
					add_action("{$taxonomy}_pre_add_form", array($this, 'render_quick_access'), 10, 0);
				}

				remove_action('admin_footer', array($this, 'render_quick_access'));
			}
			add_action('admin_footer', array($this, 'remove_overloading_menu'));
			add_action('admin_menu', array($this, 'register_menu'));
		}

		function register_menu() {
			add_submenu_page('vg_sheet_editor_setup', __('Edit categories, tags, attributes, taxonomies', vgse_taxonomy_terms()->textname), __('Edit categories, tags, attributes, taxonomies', vgse_taxonomy_terms()->textname), 'edit_posts', admin_url('admin.php?page=wpsett_welcome_page'), null);
		}

		function remove_overloading_menu() {
			?>
			<script>jQuery(document).ready(function () {
					var taxonomies = <?php echo json_encode(array_map('sanitize_text_field', $this->enabled_post_types)); ?>;
					taxonomies.forEach(function (taxonomy) {
						jQuery('#adminmenu a[href="admin.php?page=vgse-bulk-edit-' + taxonomy + '"]').parent().remove();
					});

				});</script>

			<?php
		}

		function render_quick_access() {
			// We get the taxonomy from $_GET instead of the function parameter to make it
			// compatible with the parent's method which doesn't accept parameters
			if (empty($_GET['taxonomy'])) {
				return;
			}
			$taxonomy = VGSE()->helpers->sanitize_table_key($_GET['taxonomy']);
			?>
			<hr><p class="wpse-quick-access"><a href="<?php echo esc_url(VGSE()->helpers->get_editor_url($taxonomy)); ?>" class="button button-primary"><?php _e('Edit in a Spreadsheet', vgse_taxonomy_terms()->textname); ?></a></p><hr>
			<?php
		}

		function _register_columns() {
			$post_types = $this->enabled_post_types;

			foreach ($post_types as $post_type) {
				$this->columns->register_item('ID', $post_type, array(
					'data_type' => 'post_data', //String (post_data,post_meta|meta_data)	
					'unformatted' => array('data' => 'ID', 'renderer' => 'html', 'readOnly' => true), //Array (Valores admitidos por el plugin de handsontable)
					'column_width' => 75, //int (Ancho de la columna)
					'title' => __('ID', vgse_taxonomy_terms()->textname), //String (Titulo de la columna)
					'type' => '', // String (Es para saber si será un boton que abre popup, si no dejar vacio) boton_tiny|boton_gallery|boton_gallery_multiple|(vacio)
					'supports_formulas' => false,
					'allow_to_hide' => false,
					'allow_to_save' => false,
					'allow_to_rename' => false,
					'formatted' => array('data' => 'ID', 'renderer' => 'html', 'readOnly' => true),
				));
				if (is_taxonomy_hierarchical($post_type)) {
					$this->columns->register_item('wpse_term_levels', $post_type, array(
						'data_type' => 'post_data', //String (post_data,post_meta|meta_data)	
						'column_width' => 100, //int (Ancho de la columna)
						'title' => __('Hierarchy', vgse_taxonomy_terms()->textname), //String (Titulo de la columna)
						'allow_to_hide' => false,
						'allow_to_save' => false,
						'allow_to_save_sanitization' => false,
						'unformatted' => array('renderer' => 'wp_term_hierarchy_level', 'readOnly' => true),
						'formatted' => array('renderer' => 'wp_term_hierarchy_level', 'readOnly' => true),
					));
				}
				$this->columns->register_item('name', $post_type, array(
					'data_type' => 'post_data', //String (post_data,post_meta|meta_data)	
					'unformatted' => array('data' => 'name',), //Array (Valores admitidos por el plugin de handsontable)
					'column_width' => 210, //int (Ancho de la columna)
					'title' => __('Name', vgse_taxonomy_terms()->textname), //String (Titulo de la columna)
					'formatted' => array('data' => 'name',),
					'supports_formulas' => true,
				));
				$this->columns->register_item('slug', $post_type, array(
					'data_type' => 'post_data', //String (post_data,post_meta|meta_data)	
					'unformatted' => array('data' => 'slug'), //Array (Valores admitidos por el plugin de handsontable)
					'column_width' => 150, //int (Ancho de la columna)
					'title' => __('Slug', vgse_taxonomy_terms()->textname), //String (Titulo de la columna)
					'formatted' => array('data' => 'slug'),
					'supports_formulas' => true,
				));
				if (is_taxonomy_hierarchical($post_type)) {
					$this->columns->register_item('parent', $post_type, array(
						'data_type' => 'post_data', //String (post_data,post_meta|meta_data)	
						'unformatted' => array('data' => 'parent'), //Array (Valores admitidos por el plugin de handsontable)
						'column_width' => 100,
						'title' => __('Parent', vgse_taxonomy_terms()->textname),
						'formatted' => array(
							'data' => 'parent',
							'type' => 'autocomplete',
							'source' => 'loadTaxonomyTerms',
							'taxonomy_key' => $post_type
						),
						'supports_formulas' => true,
						'supports_sql_formulas' => false,
					));
				}
				$this->columns->register_item('wpse_status', $post_type, array(
					'data_type' => 'post_data', //String (post_data,post_meta|meta_data)	
					'unformatted' => array('data' => 'wpse_status',), //Array (Valores admitidos por el plugin de handsontable)
					'column_width' => 80, //int (Ancho de la columna)
					'title' => __('Status', vgse_taxonomy_terms()->textname), //String (Titulo de la columna)
					'type' => '', // String (Es para saber si será un boton que abre popup, si no dejar vacio) boton_tiny|boton_gallery|boton_gallery_multiple|(vacio)
					'supports_formulas' => true,
					'allow_to_hide' => false,
					'allow_to_save' => true,
					'allow_to_rename' => true,
					'default_value' => 'active',
					'formatted' => array('data' => 'wpse_status', 'editor' => 'select', 'selectOptions' => array(
							'active',
							'delete',
						)),
				));

				if (in_array($post_type, array('product_cat', 'product_tag'), true)) {
					$count_key = 'product_count_' . $post_type;
				} else {
					$count_key = 'count';
				}
				$this->columns->register_item($count_key, $post_type, array(
					'data_type' => 'post_data', //String (post_data,post_meta|meta_data)	
					'unformatted' => array('renderer' => 'html', 'readOnly' => true), //Array (Valores admitidos por el plugin de handsontable)
					'column_width' => 75,
					'title' => __('Count', vgse_taxonomy_terms()->textname),
					'supports_formulas' => false,
					'allow_to_save' => false,
					'formatted' => array('renderer' => 'html', 'readOnly' => true),
					'is_locked' => true,
				));
				$post_content_args = array(
					'data_type' => 'post_data',
					'unformatted' => array('data' => 'description', 'renderer' => 'html', 'readOnly' => true),
					'column_width' => 180,
					'title' => __('Description', vgse_taxonomy_terms()->textname),
					'type' => 'boton_tiny',
					'supports_formulas' => true,
					'formatted' => array('data' => 'description', 'renderer' => 'html', 'readOnly' => true),
					'allow_to_hide' => true,
					'allow_to_save' => false,
					'allow_to_rename' => true,
				);
				$this->columns->register_item('description', $post_type, $post_content_args);
				$this->columns->register_item('taxonomy', $post_type, array(
					'data_type' => 'post_data', //String (post_data,post_meta|meta_data)	
					'unformatted' => array('data' => 'taxonomy'), //Array (Valores admitidos por el plugin de handsontable)
					'column_width' => 100, //int (Ancho de la columna)
					'supports_formulas' => true,
					'title' => __('Taxonomy', vgse_taxonomy_terms()->textname), //String (Titulo de la columna)
					'formatted' => array('data' => 'taxonomy', 'editor' => 'select', 'selectOptions' => $post_types),
					'supports_sql_formulas' => false,
				));

				if ($post_type === 'product_cat') {
					$this->columns->register_item('display_type', $post_type, array(
						'data_type' => 'meta_data',
						'column_width' => 100,
						'title' => __('Display type', 'woocommerce'),
						'type' => '',
						'supports_formulas' => true,
						'allow_to_hide' => true,
						'allow_to_save' => true,
						'allow_to_rename' => true,
						'formatted' => array('editor' => 'select', 'selectOptions' => array(
								'' => __('Default', 'woocommerce'),
								'products' => __('Products', 'woocommerce'),
								'subcategories' => __('Subcategories', 'woocommerce'),
								'both' => __('Both', 'woocommerce'),
							)),
					));
					$this->columns->register_item('thumbnail_id', $post_type, array(
						'data_type' => 'meta_data',
						'title' => __('Thumbnail', 'woocommerce'),
						'column_width' => 160,
						'supports_formulas' => true,
						'type' => 'boton_gallery',
						'allow_to_hide' => true,
						'allow_to_save' => true,
						'allow_to_rename' => true,
					));
				}
				if (is_taxonomy_hierarchical($post_type)) {
					$this->columns->register_item('wpse_full_hierarchy', $post_type, array(
						'data_type' => 'post_data', //String (post_data,post_meta|meta_data)	
						'column_width' => 100,
						'title' => __('Full hierarchy', vgse_taxonomy_terms()->textname),
						'supports_formulas' => true,
						'supports_sql_formulas' => false,
						'get_value_callback' => array($this, 'get_full_hierarchy'),
						'is_locked' => true
					));
				}
				$this->columns->register_item('wpse_old_platform_id', $post_type, array(
					'data_type' => 'meta_data',
					'title' => __('Old Platform ID', 'woocommerce'),
					'column_width' => 120,
					'supports_formulas' => true,
					'type' => '',
					'allow_to_hide' => true,
					'allow_to_save' => true,
					'allow_to_rename' => true,
				));
			}
		}

		function get_full_hierarchy($post, $cell_key, $cell_args) {
			$value = VGSE()->data_helpers->get_hierarchy_for_single_term($post);
			return $value;
		}

	}

}