<?php
if (!class_exists('WP_Sheet_Editor_Advanced_Filters_Teaser')) {

	/**
	 * Display Advanced_Filters to tease users of the free 
	 * version into purchasing the premium plugin.
	 */
	class WP_Sheet_Editor_Advanced_Filters_Teaser {

		static private $instance = false;

		private function __construct() {
			
		}

		function init() {
			if (!is_admin()) {
				return;
			}

			if (class_exists('WP_Sheet_Editor_Advanced_Filters')) {
				return;
			}

			add_action('vg_sheet_editor/filters/after_fields', array($this, 'add_filters_fields'), 10, 2);
		}

		function add_filters_fields($current_post_type, $filters) {
			if ($current_post_type !== 'user') {
				?>
				<li class="<?php
				$labels = VGSE()->helpers->get_post_type_taxonomies_single_data($current_post_type, 'label');
				if (empty($labels)) {
					echo ' hidden';
				}

				$labels[count($labels) - 1] = ' or ' . end($labels);
				?>">
					<label><?php printf(__('Enter %s', VGSE()->textname), implode(', ', $labels)); ?> - <a href="<?php echo esc_url(VGSE()->get_buy_link('advanced-filters-teaser')); ?>" target="_blank"><?php _e('Go Premium', VGSE()->textname); ?></a><a href="#" data-wpse-tooltip="right" aria-label="<?php _e('Enter the names of ' . implode(', ', $labels)); ?>">( ? )</a></label>
					<select readonly data-placeholder="<?php _e('Category name...', VGSE()->textname); ?>" name="apply_to[]" class="select2"  multiple data-remote="true" data-action="vgse_search_taxonomy_terms" data-min-input-length="4">

					</select>
				</li>

				<li>
					<label><?php _e('Date range from', VGSE()->textname); ?> <a href="#" data-wpse-tooltip="right" aria-label="<?php _e('Show items published between these dates'); ?>">( ? )</a></label><input type="date" name="date_from" readonly /> <?php _e('to', VGSE()->textname); ?> <input type="date" name="date_to" readonly/>  - <a href="<?php echo esc_url(VGSE()->get_buy_link('advanced-filters-teaser')); ?>" target="_blank"><?php _e('Go Premium', VGSE()->textname); ?></a>
				</li>
				<?php
			}
			$post_type_columns = function_exists('vgse_init_custom_columns_teaser') ? vgse_init_custom_columns_teaser()->found_columns[$current_post_type] : array();
			if (!empty($post_type_columns)) {
				?>
				<li>
					<h3><?php _e('Advanced search', VGSE()->textname); ?> <small><a href="<?php echo esc_url(VGSE()->get_buy_link('advanced-filters-teaser')); ?>" target="_blank"><?php _e('Go Premium', VGSE()->textname); ?></a></small></h3>
					<ul class="advanced-search-teaser">
						<?php
						foreach ($post_type_columns as $column_label => $column_key) {
							?>
							<li>
								<label><?php echo wp_kses_post($column_label); ?> </label><input type="text" name="<?php echo esc_attr($column_key); ?>" readonly />
							</li>
							<?php
						}
						?>
					</ul>

					<style>
						.advanced-search-teaser {
							max-width: 400px;
							display: block;
							margin: 0 auto;
							text-align: left;
						}
						.advanced-search-teaser label {
							width: 150px;
							display: inline-block;
						}
					</style>
				</li>
				<?php
			}
		}

		/**
		 * Creates or returns an instance of this class.
		 *
		 * 
		 */
		static function get_instance() {
			if (null == WP_Sheet_Editor_Advanced_Filters_Teaser::$instance) {
				WP_Sheet_Editor_Advanced_Filters_Teaser::$instance = new WP_Sheet_Editor_Advanced_Filters_Teaser();
				WP_Sheet_Editor_Advanced_Filters_Teaser::$instance->init();
			}
			return WP_Sheet_Editor_Advanced_Filters_Teaser::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}


add_action('vg_sheet_editor/initialized', 'vgse_init_Advanced_Filters_teaser', 99);

if (!function_exists('vgse_init_Advanced_Filters_teaser')) {

	function vgse_init_Advanced_Filters_teaser() {
		WP_Sheet_Editor_Advanced_Filters_Teaser::get_instance();
	}

}
