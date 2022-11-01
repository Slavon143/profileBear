<?php

if (!class_exists('WP_Sheet_Editor_Data')) {

	class WP_Sheet_Editor_Data {

		static private $instance = false;
		var $friendly_terms_to_ids_cache = array();

		private function __construct() {
			
		}

		/**
		 * Get individual post field.
		 * @param string $item
		 * @param int $id
		 * @return mixed
		 */
		function get_post_data($item, $id) {
			$post = VGSE()->helpers->get_current_provider()->get_item($id);

			$out = '';
			if ($item === 'ID') {
				$out = $id;
			} elseif ($item === 'post_title') {
				$post_title = $post->post_title;
				if ($post->post_type === 'attachment' && empty($post_title)) {
					$out = basename($post->guid);
				} else {
					$out = $post_title;
				}
			} elseif ($item === 'post_content') {
				$out = empty(VGSE()->options['be_disable_wpautop']) ? wpautop($post->post_content) : $post->post_content;
			} elseif ($item === 'post_date') {
				$out = get_the_date('Y-m-d H:i:s', $id);
			} elseif ($item === 'modified') {
				$out = get_the_modified_date('Y-m-d H:i:s', $id);
			} elseif ($item === 'post_author') {
				$author = get_userdata($post->post_author);
				$out = ( $author ) ? $author->user_login : '';
			} elseif ($item === 'post_status') {

				// We include the custom statuses added by other plugins
				// The provider get_statuses() is used for the internal capability checks
				$all_statuses = get_post_stati(array('show_in_admin_status_list' => true), 'objects');
				$custom_statuses = array();
				foreach ($all_statuses as $status_key => $status) {
					if (!empty($status->label_count['domain'])) {
						$custom_statuses[$status_key] = $status->label;
					}
				}

				// If the post status is found in the public post statuses we return it directly,
				// otherwise we return it with a lock icon because the cell will be read-only
				$statuses = array_merge(VGSE()->helpers->get_current_provider()->get_statuses(), $custom_statuses);
				if (!isset($statuses['trash'])) {
					$statuses['trash'] = 'Trash';
				}
				$out = ( isset($statuses[$post->post_status]) || VGSE()->helpers->is_plain_text_request() ) ? $post->post_status : '<i class="fa fa-lock vg-cell-blocked"></i> ' . $post->post_status;
			} elseif ($item === 'post_parent') {
				$out = (!empty($post->post_parent) ) ? html_entity_decode(get_the_title($post->post_parent)) : '';
			} else {
				$out = VGSE()->helpers->get_current_provider()->get_item_data($id, $item);
			}

			return $out;
		}

		/**
		 * Prepare individual post field for saving
		 * @param string $key
		 * @param mixed $item
		 * @param int $id
		 * @return mixed
		 */
		function set_post($key, $item, $id = null) {

			if (!VGSE()->helpers->get_current_provider()->is_post_type) {
				return $item;
			}
			$out = false;

			if ($key === 'ID') {
				$out = (int) $item;
			} elseif ($key === 'post_content') {
				// Removed the wpautop to save the value as is received including line breaks
//				$out = empty(VGSE()->options['be_disable_wpautop']) ? wpautop($item) : $item;
				$out = $item;
			} elseif ($key === 'post_date') {
				$out = $this->change_date_format_for_saving($item);
			} elseif ($key === 'post_modified') {
				$out = (!empty($item) ) ? $item : current_time('mysql');
			} elseif ($key === 'post_author') {
				$id = $this->get_author_id_from_username($item);
				if (is_numeric($item) && empty($id)) {
					$id = (int) $item;
				}
				$out = $id;
			} elseif ($key === 'post_parent') {
				$out = $this->get_post_id_from_title($item);
			} elseif ($key === 'post_status') {
				$item = trim($item);
				$statuses_raw = get_post_stati(null, 'objects');
				$statuses = wp_list_pluck($statuses_raw, 'label', 'name');
				// Allow to save status delete, which deletes the post completely
				$statuses['delete'] = 'delete';
				if (isset($statuses[$item])) {
					$out = $item;
				} elseif ($status_key = array_search($item, $statuses)) {
					$out = $status_key;
				}
			} else {
				$out = $item;
			}

			return $out;
		}

		/**
		 * Format term ids to names.
		 * Copied from WC core WC_CSV_Exporter::format_term_ids()
		 * @param  array  $term_ids Term IDs to format.
		 * @param  string $taxonomy Taxonomy name.
		 * @return string
		 */
		public function format_term_ids($term_ids, $taxonomy, $separator) {
			$term_ids = wp_parse_id_list($term_ids);

			if (!count($term_ids)) {
				return '';
			}

			$formatted_terms = array();

			if (is_taxonomy_hierarchical($taxonomy)) {
				foreach ($term_ids as $term_id) {
					$formatted_term = array();
					$ancestor_ids = array_reverse(get_ancestors($term_id, $taxonomy));

					foreach ($ancestor_ids as $ancestor_id) {
						$term = get_term($ancestor_id, $taxonomy);
						if ($term && !is_wp_error($term)) {
							$formatted_term[] = $term->name;
						}
					}

					$term = get_term($term_id, $taxonomy);

					if ($term && !is_wp_error($term)) {
						$formatted_term[] = $term->name;
					}

					$formatted_terms[] = implode(' > ', $formatted_term);
				}
			} else {
				foreach ($term_ids as $term_id) {
					$term = get_term($term_id, $taxonomy);

					if ($term && !is_wp_error($term)) {
						$formatted_terms[] = $term->name;
					}
				}
			}

			return implode("$separator ", $formatted_terms);
		}

		/**
		 * Convert terms list to friendly text.
		 * 
		 * List of terms separated by commas.
		 * 
		 * @param string|array $current_terms
		 * @return string
		 */
		function prepare_post_terms_for_display($current_terms) {
			if (is_string($current_terms)) {
				return $current_terms;
			}
			if (empty($current_terms) || is_wp_error($current_terms)) {
				return '';
			}

			$first_term = current($current_terms);
			$separator = VGSE()->helpers->get_term_separator();
			$term_ids = wp_list_pluck($current_terms, 'term_id');
			if (!empty(VGSE()->options['manage_taxonomy_columns_term_ids'])) {
				$names = implode("$separator ", $term_ids);
			} elseif (!empty(VGSE()->options['manage_taxonomy_columns_term_slugs'])) {
				$names = implode("$separator ", wp_list_pluck($current_terms, 'slug'));
			} else {
				$names = $this->format_term_ids($term_ids, $first_term->taxonomy, $separator);
			}
			return html_entity_decode($names);
		}

		function get_hierarchy_for_single_term($term) {
			$out = $term->name;
			while ($term->parent > 0) {
				$term = get_term_by('id', $term->parent, $term->taxonomy);
				$out = $term->name . ' > ' . $out;
			}

			return html_entity_decode($out);
		}

		function get_taxonomy_hierarchy($taxonomy, $parent = 0, $parent_name = '') {
			// only 1 taxonomy
			$taxonomy = is_array($taxonomy) ? array_shift($taxonomy) : $taxonomy;
			// get all direct decendants of the $parent
			$terms = get_terms(array('taxonomy' => $taxonomy, 'parent' => $parent, 'hide_empty' => false, 'update_term_meta_cache' => false));
			// prepare a new array.  these are the children of $parent
			// we'll ultimately copy all the $terms into this new array, but only after they
			// find their own children
			$out = array();
			// go through all the direct decendants of $parent, and gather their children
			foreach ($terms as $term) {
				// add the term to our new array
				if (!empty($parent_name)) {
					$term->name = $parent_name . ' > ' . $term->name;
				}

				$out[] = $term;

				// recurse to get the direct decendants of "this" term
				$children = $this->get_taxonomy_hierarchy($taxonomy, $term->term_id, $term->name);
				$out = array_merge($out, $children);
			}
			// send the results back to the caller
			return $out;
		}

		/**
		 * Get all terms in taxonomy
		 * @param string $taxonomy
		 * @return array|bool
		 */
		function get_taxonomy_terms($taxonomy, $source = '', $output = 'name') {
			if( ! in_array( $output, array('name', 'slug'), true)) {
				$output = 'name';
			}
			$cache_key = apply_filters('vg_sheet_editor/data/taxonomy_terms/cache_key', 'wpse_terms_' . $taxonomy . $output, $taxonomy, $source);
			$terms = get_transient($cache_key);
			if (empty($terms)) {

				if (!is_taxonomy_hierarchical($taxonomy)) {
					$get_hierarchy = false;
				} else {
					// Building the hierarchy tree is expensive so we do it only for taxonomies with < 2500 terms
					// taxonomies with > 2500 terms get the list of names without hierarchy
					// $terms_count = wp_count_terms($taxonomy, array(
					// 	'hide_empty' => false,
					// ));
					// $get_hierarchy = $terms_count < 2500;
					// 
					// Now we always get the hierarchy because if we dont' get hierarchy for big taxonomies,
					// We might get duplicate term names (child categories of different parent categories) which
					// will certainly save the incorrect term
					$get_hierarchy = true;
				}

				if ($get_hierarchy) {
					$terms = wp_list_pluck($this->get_taxonomy_hierarchy($taxonomy), $output);
				} else {
					$terms = get_terms(array('taxonomy' => $taxonomy, 'hide_empty' => false, 'fields' => $output . 's', 'update_term_meta_cache' => false));
				}
				set_transient($cache_key, $terms, WEEK_IN_SECONDS);
			}

			return apply_filters('vg_sheet_editor/data/taxonomy_terms', $terms, $taxonomy, $source);
		}

		/**
		 * Get users
		 * @param int $first Display first a specific user
		 * @param bool $with_keys include user ID as array keys.
		 * @return array
		 */
		function get_authors_list($first = null, $with_keys = false) {
			global $wpdb;

			if (!VGSE()->helpers->is_editor_page() || !post_type_supports(VGSE()->helpers->get_provider_from_query_string(), 'author')) {
				return array();
			}

			$cache_key = 'wpse_authors' . (int) $with_keys;
			$list = wp_cache_get($cache_key);

			if (!$list) {
				// We use a custom query for performance reasons
				$blogusers = $wpdb->get_results("SELECT ID,user_login FROM $wpdb->users WHERE 1=1
ORDER BY user_login ASC", OBJECT);
				$list = array();

				if (!empty($blogusers)) {
					foreach ($blogusers as $user) {
						if (is_numeric($first) && (int) $first === $user->ID) {

							if ($with_keys) {
								$list = array_merge(array($user->ID => $user->user_login), $list);
							} else {
								array_unshift($list, $user->user_login);
							}
						}

						if ($with_keys) {
							$list[$user->ID] = $user->user_login;
						} else {
							$list[] = $user->user_login;
						}
					}
				}
				wp_cache_set($cache_key, $list);
			}

			return array_map('esc_html', $list);
		}

		/**
		 * Get user ID from username
		 * @param string $author username
		 * @return int
		 */
		function get_author_id_from_username($author) {
			$autor = get_user_by('login', $author);

			if (!$autor) {
				return false;
			}
			return $autor->ID;
		}

		/**
		 * Prepare date format for saving
		 * @param string $date
		 * @param int $post_id
		 * @return string
		 */
		function change_date_format_for_saving($date) {
			// note, we had some logic related to product dates. I removed it because it seemed unnecessary.
			// Keep in mind a possible rollback in case users report issues.
			// The date must always come in Y-m-d format, so we can easily change the format here.
			$date_timestamp = ( empty($date)) ? time() : strtotime($date);
			$savedate = date('Y-m-d H:i:s', $date_timestamp);
			return $savedate;
		}

		/**
		 * Save single post data, either post data or metadata.
		 * @param int $id
		 * @param mixed $content
		 * @param string $key
		 * @param string $type
		 * @return boolean
		 */
		function save_single_post_data($id, $content, $key, $type) {

			if ($type === 'post_data') {
				$my_post['ID'] = $id;
				if (strpos($key, 'post_') === false) {
					$my_post['post_' . $key] = $content;
				} else {
					$my_post[$key] = $content;
				}

				if (!empty($my_post['post_title'])) {
					$my_post['post_title'] = wp_strip_all_tags($my_post['post_title']);
				}
				$post_id = VGSE()->helpers->get_current_provider()->update_item_data($my_post, true);
				if (is_wp_error($post_id)) {
					return $post_id;
				}
			} else if ($type === 'meta_data' || $type === 'post_meta') {
				VGSE()->helpers->get_current_provider()->update_item_meta($id, $key, $content);
			}
			return true;
		}

		/**
		 * Get all post titles from post type
		 * @global type $wpdb
		 * @param string $post_type
		 * @param array $output ARRAY_N or ARRAY_A
		 * @param bool $flatten
		 * @return array
		 */
		function get_all_post_titles_from_post_type($post_type, $output = ARRAY_N, $flatten = false) {

			global $wpdb;
			$statuses = array_keys(VGSE()->helpers->get_current_provider()->get_statuses());
			$statuses_in_query_placeholders = implode(', ', array_fill(0, count($statuses), '%s'));
			$results = $wpdb->get_results($wpdb->prepare("SELECT post_title FROM $wpdb->posts WHERE post_type = %s AND post_status IN ($statuses_in_query_placeholders) ", array_merge(array($post_type), $statuses)), $output);

			if ($flatten) {
				$results = VGSE()->helpers->array_flatten($results, array());
			}

			return $results;
		}

		/**
		 * Parse a category field from a CSV.
		 * Categories are separated by commas and subcategories are "parent > subcategory".
		 * Copied from WC core: WC_Product_CSV_Importer::parse_categories_field()
		 *
		 * @param string $value Field value.
		 * @param string $taxonomy Taxonomy key value.
		 * @return array of arrays with "parent" and "name" keys.
		 */
		public function parse_terms_string_for_saving($value, $taxonomy, $separator = null) {
			$out = array(
				'created' => 0,
				'term_ids' => array()
			);
			if (empty($value)) {
				return $out;
			}

			// Init object cache
			if (!isset($this->friendly_terms_to_ids_cache[$taxonomy])) {
				$this->friendly_terms_to_ids_cache[$taxonomy] = array();
			}

			if (is_null($separator)) {
				$separator = VGSE()->helpers->get_term_separator();
			}
			$row_terms = array_map('trim', explode("$separator", $value));
			// We can't use array_filter because real terms containing '0' would be removed
			foreach ($row_terms as $index => $row_term) {
				if (is_string($row_term) && $row_term === '') {
					unset($row_terms[$index]);
				}
			}

			// Save using ids
			if (!empty(VGSE()->options['manage_taxonomy_columns_term_ids'])) {
				foreach ($row_terms as $term_id) {
					if (is_numeric($term_id)) {
						$out['term_ids'][] = (int) $term_id;
					}
				}
				return $out;
			}

			$categories = array();
			$created = 0;
			$wc_attributes = function_exists('wc_get_attribute_taxonomy_names') ? wc_get_attribute_taxonomy_names() : array();
			$woocommerce_taxonomies = array_merge($wc_attributes, array('product_cat', 'product_tag'));
			$is_taxonomy_hierarchical = is_taxonomy_hierarchical($taxonomy);

			foreach ($row_terms as $row_term) {
				if (isset($this->friendly_terms_to_ids_cache[$taxonomy][$row_term]) && empty($_REQUEST['wpse_no_cache'])) {
					$categories[] = (int) $this->friendly_terms_to_ids_cache[$taxonomy][$row_term];
					continue;
				}

				$parent = null;
				// If the taxonomy is not hierarchical, allow to save > symbol
				if (!$is_taxonomy_hierarchical) {
					$_terms = array(trim($row_term));
				} else {
					$_terms = array_map('trim', explode('>', $row_term));
				}
				$total = count($_terms);

				foreach ($_terms as $index => $_term) {
					// Check if category exists. Parent must be empty string or null if doesn't exists.
					// We can't use term_exists() because it converts the name to slug and it 
					// always returns the term "D" for term "D+"
					$term_exists_args = array(
						'taxonomy' => $taxonomy,
						'name' => $_term,
						'parent' => $parent,
						'fields' => 'ids',
						'hide_empty' => false
					);
					if (!empty(VGSE()->options['manage_taxonomy_columns_term_slugs'])) {
						unset($term_exists_args['name']);
						$term_exists_args['slug'] = $_term;
					}
					$term_exists_raw = get_terms($term_exists_args);
					$term = ( is_array($term_exists_raw) && !empty($term_exists_raw)) ? current($term_exists_raw) : null;

					if ($term) {
						$term_id = (int) $term;
						// Don't allow users without capabilities to create new product categories or tags
					} elseif (in_array($taxonomy, $woocommerce_taxonomies) && !current_user_can('manage_product_terms')) {
						break;
					} else {
						$term = wp_insert_term($_term, $taxonomy, array('parent' => intval($parent)));

						if (is_wp_error($term)) {
							break; // We cannot continue if the term cannot be inserted.
						}

						$term_id = $term['term_id'];
						$created++;
					}

					// Only requires assign the last category.
					if (( 1 + $index ) === $total) {
						$categories[] = (int) $term_id;
						$this->friendly_terms_to_ids_cache[$taxonomy][$row_term] = (int) $term_id;
					} else {
						// Store parent to be able to insert or query categories based in parent ID.
						$parent = (int) $term_id;
					}
				}
			}

			$out = array(
				'created' => $created,
				'term_ids' => $categories
			);
			return $out;
		}

		/**
		 * Prepare post terms for saving.
		 * 
		 * Convert a string of terms separated by commas to a terms IDs array.
		 * If the term doesn't exist, it creates it automatically.
		 * 
		 * @param string $categories
		 * @param string $taxonomy
		 * @return array
		 */
		function prepare_post_terms_for_saving($categories, $taxonomy, $separator = null) {
			global $wpdb;
			if (is_null($separator)) {
				$separator = VGSE()->helpers->get_term_separator();
			}
			// Convert | to the real separator
			$categories = str_replace('|', $separator, $categories);

			$row_terms = explode($separator, $categories);
			$all_row_terms = implode('', $row_terms);
			if (is_numeric($all_row_terms)) {
				$ids_in_query_placeholders = implode(', ', array_fill(0, count($row_terms), '%d'));
				$term_ids_from_number_names = array_map('intval', $wpdb->get_col($wpdb->prepare("SELECT term_id FROM $wpdb->termmeta WHERE meta_key = 'wpse_old_platform_id' AND meta_value IN ($ids_in_query_placeholders) GROUP BY meta_value", array_map('intval', $row_terms))));

				if ($term_ids_from_number_names) {
					return $term_ids_from_number_names;
				}
			}

			// If this is one term and it doesn't contain any spaces, try to find by slug first
			// Disabled because it causes conflict when they want to save a new term by name but the name matches the slug of another term
			// Also, we already have the option "Manage taxonomy column values as term slugs?" which can be activated if they want to use slugs for terms
			// So this was a little redundant
			/* if (!empty($categories) && strpos($categories, $separator) === false && strpos($categories, ' ') === false) {
			  $term = get_term_by('slug', trim($categories), $taxonomy);
			  if ($term && $term->slug === trim($categories)) {
			  return array($term->term_id);
			  }
			  } */

			if (!is_taxonomy_hierarchical($taxonomy)) {
				// Allow to save > symbol when the taxonomy is not hierarchical and the symbol appears at the beginning and there's only one term
				if (strpos($categories, '>') === 0 && strpos($categories, $separator) === false) {
					$categories = trim($categories);
				} else {
					// Replace the > symbol with the regular separator. Necessary when we're copying values from a hierarchical taxonomy into a non-hierarchical taxonomy
					// and we want to save each term as a regular term without hierarchy
					$categories = str_replace('>', $separator, $categories);
				}
			}

			if (is_taxonomy_hierarchical($taxonomy) && !empty(VGSE()->options['save_every_term_in_hierarchy']) && !empty($categories)) {
				$terms = array_map('trim', explode($separator, $categories));
				$final_categories = array();
				foreach ($terms as $term_name) {
					$term_hierarchy = array_map('trim', explode('>', $term_name));
					$prepared_hierarchies = array();
					foreach ($term_hierarchy as $term_hierarchy_level) {
						if (!empty($prepared_hierarchies)) {
							$prepared_hierarchies[] = implode('>', array_filter(array($prepared_hierarchies[count($prepared_hierarchies) - 1], $term_hierarchy_level)));
						} else {
							$prepared_hierarchies[] = $term_hierarchy_level;
						}
					}
					$final_categories[] = implode($separator, $prepared_hierarchies);
				}
				$categories = implode($separator, $final_categories);
			}

			$parsed_data = $this->parse_terms_string_for_saving(html_entity_decode(sanitize_text_field($categories)), $taxonomy, $separator);
			if (!empty($parsed_data['created'])) {
				VGSE()->helpers->increase_counter('editions', $parsed_data['created']);
			}
			return array_unique($parsed_data['term_ids']);
		}

		/**
		 * Get posts count by post type
		 * @global obj $wpdb
		 * @param string $current_post post type
		 * @return int
		 */
		function total_posts($current_post) {
			$provider = VGSE()->helpers->get_data_provider($current_post);
			return $provider->get_total($current_post);
		}

		/**
		 * Get post status key from friendly name
		 * @param string $status
		 * @return boolean|string
		 */
		function get_status_key_from_name($status) {

			$statuses = VGSE()->helpers->get_current_provider()->get_statuses();

			if (!in_array($status, $statuses)) {
				return false;
			}

			$status_key = array_search($status, $statuses);

			return $status_key;
		}

		/*
		 * Devuelve el parent de cada post si lo tiene
		 */

		/**
		 * Get post ID from title
		 * @global obj $wpdb
		 * @param string $page_title
		 * @param string $output OBJECT , ARRAY_N , or ARRAY_A.
		 * @return ID
		 */
		function get_post_id_from_title($page_title, $post_type = null) {
			global $wpdb;

			if (empty($page_title)) {
				return null;
			}

			if (!$post_type) {
				$post_type = (isset($_REQUEST['post_type'])) ? sanitize_text_field($_REQUEST['post_type']) : 'post';
			}
			$sql = $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type= %s", wp_unslash(html_entity_decode($page_title)), esc_sql($post_type));
			$post_id = $wpdb->get_var($sql);
			if ($post_id) {
				return $post_id;
			}
			return null;
		}

		/**
		 * Get post statuses by friendly names.
		 * @return array
		 */
		function get_post_statuses() {

			$status = VGSE()->helpers->get_current_provider()->get_statuses();
			$list = array();

			foreach ($status as $item) {
				$list[] = esc_html($item);
			}

			return $list;
		}

		/**
		 * Creates or returns an instance of this class.
		 *
		 */
		static function get_instance() {
			if (null == WP_Sheet_Editor_Data::$instance) {
				WP_Sheet_Editor_Data::$instance = new WP_Sheet_Editor_Data();
			}
			return WP_Sheet_Editor_Data::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}
	