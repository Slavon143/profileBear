<?php

if (!class_exists('WPSE_Queues')) {

	class WPSE_Queues {

		static private $instance = false;
		var $directory = null;
		var $secret_key = null;

		private function __construct() {
			
		}

		function file_expiration_hours() {
			// Expire in 7 days
			return apply_filters('vg_sheet_editor/queues/file_expiration_hours', 24 * 7);
		}

		function maybe_create_directories() {
			if (!is_dir($this->directory)) {
				wp_mkdir_p($this->directory);
			}
			if (!file_exists($this->directory . '/index.html')) {
				file_put_contents($this->directory . '/index.html', '');
			}
		}

		function delete_old_files() {
			$files = VGSE()->helpers->get_files_list($this->directory, '.json');
			foreach ($files as $file) {
				// Delete csv files older than 7 days to avoid deleting exports in progress.
				$expiration_hours = (int) $this->file_expiration_hours();
				if (file_exists($file) && (time() - filemtime($file) > $expiration_hours * 3600)) {
					unlink($file);
				}
			}
		}

		function get_job_file($job_id) {
			$file_name = str_replace(array('.', '/', '\\', ':'), '', wp_normalize_path(sanitize_file_name($job_id . '-' . $this->secret_key)));
			$file_path = wp_normalize_path($this->directory . '/' . $file_name . '.json');
			if (!file_exists($file_path)) {
				file_put_contents($file_path, '');
			}
			return $file_path;
		}

		function mark_tasks_as_processed($job_id, $number = 10) {
			$file_path = $this->get_job_file($job_id);
			if (!file_exists($file_path)) {
				return false;
			}
			$file_content = file_get_contents($file_path);
			if (empty($file_content)) {
				$file_content = '[]';
			}
			$file_data = json_decode($file_content, true);
			$remaining_tasks = array_slice($file_data, $number, count($file_data));
			if (empty($remaining_tasks)) {
				unlink($file_path);
			} else {
				file_put_contents($file_path, json_encode($remaining_tasks, JSON_PRETTY_PRINT));
			}
			return true;
		}

		function get_tasks_for_processing($job_id, $number = 10) {

			$file_path = $this->get_job_file($job_id);
			if (!file_exists($file_path)) {
				return array();
			}
			$file_content = file_get_contents($file_path);
			if (empty($file_content)) {
				$file_content = '[]';
			}
			$file_data = json_decode($file_content, true);
			$tasks = array_slice($file_data, 0, $number);
			return $tasks;
		}

		function count_tasks($job_id) {

			$file_path = $this->get_job_file($job_id);
			if (!file_exists($file_path)) {
				return false;
			}
			$file_content = file_get_contents($file_path);
			if (empty($file_content)) {
				$file_content = '[]';
			}
			$file_data = json_decode($file_content, true);
			return count($file_data);
		}

		function queue_exists($job_id) {

			$file_path = $this->get_job_file($job_id);
			if (!file_exists($file_path)) {
				return false;
			}
			$file_content = file_get_contents($file_path);
			if (empty($file_content)) {
				$file_content = '[]';
			}
			$file_data = json_decode($file_content, true);
			return !empty($file_data);
		}

		function entry($task, $job_id) {
			$file_path = $this->get_job_file($job_id);
			if (!file_exists($file_path)) {
				return $this;
			}
			$file_content = file_get_contents($file_path);
			if (empty($file_content)) {
				$file_content = '[]';
			}
			$file_data = json_decode($file_content, true);
			$file_data[] = $task;
			file_put_contents($file_path, json_encode($file_data, JSON_PRETTY_PRINT));
			return $this;
		}

		function bulk_entry($tasks, $job_id) {
			$file_path = $this->get_job_file($job_id);
			if (!file_exists($file_path)) {
				return $this;
			}
			$file_content = file_get_contents($file_path);
			if (empty($file_content)) {
				$file_content = '[]';
			}
			$file_data = json_decode($file_content, true);
			$file_data = array_merge($file_data, $tasks);
			file_put_contents($file_path, json_encode($file_data, JSON_PRETTY_PRINT));
			return $this;
		}

		function init() {
			// We use the secret key to add extra security to the file names
			$this->secret_key = 'O0oGtcI8Zc';
			$this->directory = apply_filters('vg_sheet_editor/queues/directory', WP_CONTENT_DIR . '/uploads/wp-sheet-editor-universal-sheet/queues');
			do_action('wpse_delete_old_csvs', array($this, 'delete_old_files'));
			if (is_admin()) {
				$this->maybe_create_directories();
				add_action('admin_init', array($this, 'delete_old_files'));
			}
		}

		/**
		 * Creates or returns an instance of this class.
		 */
		static function get_instance() {
			if (null == WPSE_Queues::$instance) {
				WPSE_Queues::$instance = new WPSE_Queues();
				WPSE_Queues::$instance->init();
			}
			return WPSE_Queues::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}

if (!function_exists('WPSE_Queues_Obj')) {

	function WPSE_Queues_Obj() {
		return WPSE_Queues::get_instance();
	}

}
WPSE_Queues_Obj();
