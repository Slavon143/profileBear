<?php
if (!class_exists('WP_Sheet_Editor_Terms_Teaser')) {

	/**
	 * Display terms item in the toolbar to tease terms of the free 
	 * version into purchasing the premium plugin.
	 */
	class WP_Sheet_Editor_Terms_Teaser {

		static private $instance = false;

		private function __construct() {
			
		}

		function init() {

			if (class_exists('WP_Sheet_Editor_Taxonomy_Terms') || !is_admin()) {
				return;
			}
			foreach (get_taxonomies() as $taxonomy) {
				add_action("{$taxonomy}_pre_add_form", array($this, 'render_quick_access'), 10, 0);
			}
			add_filter('vg_sheet_editor/prepared_post_types', array($this, 'add_lite_version_to_quick_setup'));
		}

		function add_lite_version_to_quick_setup($sheets) {
			if (wp_doing_ajax() || !is_admin()) {
				return $sheets;
			}

			$taxonomies = array_merge(get_taxonomies(array(
				'public' => true,
				'show_ui' => true,
				'_builtin' => true,
							), 'objects'), get_taxonomies(array(
				'show_ui' => true,
				'_builtin' => false,
							), 'objects'));
			$free = array('category', 'post_tag');
			$free_url = VGSE()->get_plugin_install_url('Bulk Edit Categories and Tags - Create Thousands Quickly on the Editor');
			$premium_url = VGSE()->extensions['taxonomy_terms']['inactive_action_url'];

			foreach ($taxonomies as $taxonomy) {
				if (isset($sheets[$taxonomy->name])) {
					continue;
				}

				$is_free = in_array($taxonomy->name, $free);
				$sheets[$taxonomy->name] = array(
					'key' => $taxonomy->name,
					'label' => $taxonomy->label,
					'is_disabled' => true,
					'description' => $is_free ? '<small><a href="' . esc_url($free_url) . '" target="_blank">' . __('(Install free extension)', VGSE()->textname) . '</a></small>' : '<small><a href="' . esc_url($premium_url) . '" target="_blank">' . __('(Pro extension)', VGSE()->textname) . '</a></small>',
				);
			}


			return $sheets;
		}

		function render_quick_access() {
			// We get the taxonomy from $_GET instead of the function parameter to make it
			// compatible with the parent's method which doesn't accept parameters
			if (empty($_GET['taxonomy'])) {
				return;
			}
			$taxonomy = sanitize_text_field($_GET['taxonomy']);
			?>
			<hr><p class="wpse-quick-access"><?php _e('<b>Tip from WP Sheet Editor:</b> Edit thousands of categories at once, make advanced searches, view all the info in one page, and more.', VGSE()->textname); ?><br><a href="https://wpsheeteditor.com/extensions/categories-tags-product-attributes-taxonomies-spreadsheet/?utm_source=wp-admin&utm_medium=terms-list-teaser&utm_campaign=<?php echo esc_attr($taxonomy); ?>"  target="_blank"><?php _e('Edit in a Spreadsheet', VGSE()->textname); ?></a></p><hr>
			<?php
		}

		/**
		 * Creates or returns an instance of this class.
		 *
		 * 
		 */
		static function get_instance() {
			if (null == WP_Sheet_Editor_Terms_Teaser::$instance) {
				WP_Sheet_Editor_Terms_Teaser::$instance = new WP_Sheet_Editor_Terms_Teaser();
				WP_Sheet_Editor_Terms_Teaser::$instance->init();
			}
			return WP_Sheet_Editor_Terms_Teaser::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}


add_action('vg_sheet_editor/initialized', 'vgse_init_terms_teaser');

if (!function_exists('vgse_init_terms_teaser')) {

	function vgse_init_terms_teaser() {
		WP_Sheet_Editor_Terms_Teaser::get_instance();
	}

}
