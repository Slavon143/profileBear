<?php

if (!class_exists('WPSE_User_Path')) {

	/**
	 * Note to reviewers. This class saves in the database the last 100 admin pages 
	 * related to this plugin used by the administrator (either when the admin opens 
	 * a page with a slug from this plugin or opens a admin URL containing a identifier as query string.
	 * 
	 * This information is intended to be used to build a little activity profile and customize
	 * the experience on the site (hide features that the admin rarely uses, for example).
	 * 
	 * This information never leaves your server when you are using the free version hosted on wp.org
	 * , it's just a bunch of activity data stored in your database and used by the plugin locally
	 */
	class WPSE_User_Path {

		var $args = null;

		function __construct($args = array()) {
			if (!is_user_logged_in() || !current_user_can('manage_options')) {
				return;
			}

			$defaults = array(
				'user_id' => get_current_user_id(),
				'user_path_key' => null,
				'is_free' => null, // bool
			);
			$this->args = wp_parse_args($args, $defaults);

			if ((int) get_option('vgse_user_path_sent')) {
				delete_option($this->args['user_path_key'] . '_user_path');
				return;
			}

			$this->register_page_open();
			$this->register_trigger_used();
			$this->register_ajax_used();

			do_action('wpse_user_path_init', $this);
		}

		function register_ajax_used() {
			if (!defined('DOING_AJAX') || !DOING_AJAX || empty($_REQUEST['action']) || strpos($_REQUEST['action'], 'vgse') === false) {
				return;
			}
			$this->register_event(array(
				'eventAction' => 'ajax',
				'eventLabel' => sanitize_text_field($_REQUEST['action']),
			));
		}

		function register_trigger_used() {
			if (empty($_GET['vgseup_t'])) {
				return;
			}
			$this->register_event(array(
				'eventAction' => 'click',
				'eventLabel' => sanitize_text_field($_GET['vgseup_t']),
			));
		}

		function register_page_open() {
			$pages_to_load_assets = VGSE()->frontend_assets_allowed_on_pages();
			if (empty($_GET['page']) ||
					!in_array($_GET['page'], $pages_to_load_assets)) {
				return;
			}

			$this->register_event(array(
				'eventAction' => 'open',
				'eventLabel' => sanitize_text_field($_GET['page']),
			));
		}

		function get_events($user_id = null) {
			if (!$user_id) {
				$user_id = $this->args['user_id'];
			}

			$events = get_option($this->args['user_path_key'] . '_user_path', array());
			if (!is_array($events)) {
				$events = array();
			}
			if (!isset($events[$this->args['user_id']])) {
				$events[$this->args['user_id']] = array();
			}
			return $events;
		}

		function register_event($args = array()) {
			$defaults = array(
				'hitType' => 'event',
				'eventCategory' => ($this->args['is_free'] ? 'Free' : 'Paid') . ' - ' . sanitize_text_field($_SERVER['HTTP_HOST']),
				'eventAction' => '',
				'eventLabel' => '',
				'eventValue' => date('YmdHis', current_time('timestamp')),
				'vgStatus' => 0
			);
			$args = apply_filters('vg_sheet_editor/user_path/event_data', wp_parse_args($args, $defaults), $this);

			if (empty($args['eventLabel'])) {
				return;
			}

			$events = $this->get_events();
			$events[$this->args['user_id']][] = $args;

			$is_event_repeated_much = count(wp_list_filter($events[$this->args['user_id']], array(
						'eventLabel' => $args['eventLabel']
					))) > 5;

			// If the same event happened more than 5 times, we don't register it to
			// avoid crossing the 100 events limit with the same type of events
			if ($is_event_repeated_much) {
				return;
			}

			// Save only the last 100 items
			$events[$this->args['user_id']] = array_slice($events[$this->args['user_id']], -100, 100);
			update_option($this->args['user_path_key'] . '_user_path', $events, false);
		}

	}

}


$inc_files = glob(__DIR__ . '/*.php');
foreach ($inc_files as $inc_file) {
	if (!is_file($inc_file)) {
		continue;
	}

	require_once $inc_file;
}