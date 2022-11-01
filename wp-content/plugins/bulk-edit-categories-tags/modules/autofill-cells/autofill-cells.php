<?php


if (!class_exists('WP_Sheet_Editor_Autofill_Cells')) {

	/**
	 * This class enables the autofill cells features.
	 * Also known as fillHandle in handsontable arguments.
	 */
	class WP_Sheet_Editor_Autofill_Cells {

		static private $instance = false;

		private function __construct() {
			
		}

		/**
		 * Creates or returns an instance of this class.
		 */
		static function get_instance() {
			if (null == WP_Sheet_Editor_Autofill_Cells::$instance) {
				WP_Sheet_Editor_Autofill_Cells::$instance = new WP_Sheet_Editor_Autofill_Cells();
				WP_Sheet_Editor_Autofill_Cells::$instance->init();
			}
			return WP_Sheet_Editor_Autofill_Cells::$instance;
		}

		function init() {
			add_filter('vg_sheet_editor/handsontable/custom_args', array($this, 'enable_drag_down'));
		}

		function enable_drag_down($args) {

			$args['fillHandle'] = array(
				'autoInsertRow' => false,
			);
			return $args;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

	add_action('vg_sheet_editor/initialized', 'vgse_autofill_cells_init');

	function vgse_autofill_cells_init() {
		WP_Sheet_Editor_Autofill_Cells::get_instance();
	}

}
