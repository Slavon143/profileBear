<?php

if ( ! class_exists( 'WP_Sheet_Editor_Serialized_Field' ) ) {

	class WP_Sheet_Editor_Serialized_Field {

		var $snippets_options = array();
		var $settings         = array();
		var $column_keys      = array();

		function __construct( $args ) {

			if ( ! empty( VGSE()->options['be_disable_serialized_columns'] ) || ! apply_filters( 'vg_sheet_editor/serialized_addon/is_enabled', true ) ) {
				return;
			}
			$defaults       = array(
				'sample_field_key'               => '',
				'sample_field'                   => array( '' ),
				'column_width'                   => 150,
				'column_title_prefix'            => '', // to remove the field key from the column title
				'level'                          => 1, // int
				'allowed_post_types'             => array( 'post' ),
				'is_single_level'                => true,
				'allow_in_wc_product_variations' => false,
				'index_start'                    => 0,
				'label_index_start'              => 1,
				'disable_precache'               => false,
				'wpse_source'                    => null,
				'column_init_priority'           => 20,
				'column_settings'                => array(),
			);
			$args           = wp_parse_args( $args, $defaults );
			$this->settings = $args;

			add_action( 'vg_sheet_editor/save_rows/before_saving_cell', array( $this, 'save_column' ), 10, 4 );
			// Priority 12 to allow to instantiate from another editor/before_init function
			add_action( 'vg_sheet_editor/editor/before_init', array( $this, 'register_columns' ), $this->settings['column_init_priority'] );

			add_filter( 'vg_sheet_editor/provider/user/get_item_meta', array( $this, 'filter_cell_data_for_readings_user' ), 10, 5 );
			add_filter( 'vg_sheet_editor/provider/post/get_item_meta', array( $this, 'filter_cell_data_for_readings_post' ), 10, 5 );
			add_filter( 'vg_sheet_editor/formulas/sql_execution/can_execute', array( $this, 'can_execute_sql_formula' ), 10, 4 );

			if ( ! empty( $this->settings['allow_in_wc_product_variations'] ) ) {
				add_filter( 'vg_sheet_editor/woocommerce/variation_columns', array( $this, 'allow_in_variations' ) );
			}
			add_filter( 'vgse_sheet_editor/provider/post/prefetch/meta_keys', array( $this, 'prefetch_meta_field' ), 10, 2 );
			add_filter( 'vg_sheet_editor/load_rows/preload_data', array( $this, 'preload_all_subfield_column_data' ), 10, 5 );
		}

		function prefetch_meta_field( $meta_keys, $post_type ) {

			if ( $this->post_type_has_serialized_field( $post_type ) && array_search( $this->settings['sample_field_key'], $meta_keys ) === false && empty( $this->settings['disable_precache'] ) ) {
				$meta_keys[] = $this->settings['sample_field_key'];
			}
			return $meta_keys;
		}

		function can_execute_sql_formula( $can, $formula, $column, $post_type ) {
			if ( ! $this->post_type_has_serialized_field( $post_type ) || strpos( $column['key'], $this->settings['sample_field_key'] ) === false ) {
				return $can;
			}

			return false;
		}

		function filter_cell_data_for_readings_user( $value, $id, $key, $single, $context ) {
			if ( strpos( $key, $this->settings['sample_field_key'] ) === false || $context !== 'read' || ! $this->post_type_has_serialized_field( 'user' ) ) {
				return $value;
			}
			return $this->filter_cell_data_for_readings( $value, $id, $key );
		}

		function filter_cell_data_for_readings_post( $value, $id, $key, $single, $context ) {
			if ( strpos( $key, $this->settings['sample_field_key'] ) === false || $context !== 'read' || ! $this->post_type_has_serialized_field( get_post_type( $id ) ) ) {
				return $value;
			}
			return $this->filter_cell_data_for_readings( $value, $id, $key );
		}

		function preload_all_subfield_column_data( $data, $posts, $wp_query_args, $settings, $spreadsheet_columns ) {
			if ( ! $this->post_type_has_serialized_field( $settings['post_type'] ) ) {
				return $data;
			}
			$enabled_sub_columns = array();
			foreach ( $spreadsheet_columns as $column_key => $column_settings ) {
				if ( ! empty( $column_settings['serialized_field_original_key'] ) && $column_settings['serialized_field_original_key'] === $this->settings['sample_field_key'] && empty( $column_settings['infinite_serialized_handler'] ) ) {
					$enabled_sub_columns[ $column_key ] = '';
				}
			}
			if ( ! $enabled_sub_columns ) {
				return $data;
			}

			remove_filter( 'vg_sheet_editor/provider/user/get_item_meta', array( $this, 'filter_cell_data_for_readings_user' ), 10, 5 );
			remove_filter( 'vg_sheet_editor/provider/post/get_item_meta', array( $this, 'filter_cell_data_for_readings_post' ), 10, 5 );
			foreach ( $posts as $post ) {
				$serialized_columns_values = $this->_get_serialized_column_values( $post->ID );
				$serialized_columns_values = array_intersect_key( $serialized_columns_values, $enabled_sub_columns );
				$data[ $post->ID ]         = array_merge( isset( $data[ $post->ID ] ) ? $data[ $post->ID ] : array(), $enabled_sub_columns, $serialized_columns_values );
			}
			return $data;
		}

		function _get_serialized_column_values( $post_id ) {

			$post_value = maybe_unserialize( VGSE()->helpers->get_current_provider()->get_item_meta( $post_id, $this->settings['sample_field_key'], true ) );

			if ( empty( $post_value ) || ! is_array( $post_value ) ) {
				return array();
			}
			$cache_key                 = 'wpse_sf' . $post_id . '_' . $this->settings['sample_field_key'];
			$serialized_columns_values = wp_cache_get( $cache_key );

			if ( ! is_array( $serialized_columns_values ) ) {
				$serialized_columns_values = array();
				if ( ! empty( $this->settings['is_single_level'] ) ) {
					$post_value_index = $this->settings['index_start'];
					foreach ( $post_value as $post_value_key => $post_value_value ) {
						if ( is_numeric( $post_value_key ) ) {
							$key = $this->settings['sample_field_key'] . '_' . $post_value_index . '_i_' . $post_value_key;
						} else {
							$key = $this->settings['sample_field_key'] . '_' . $post_value_key . '_i_' . $post_value_index;
						}

						$serialized_columns_values[ $key ] = apply_filters( 'vg_sheet_editor/serialized_addon/load_cell_value', $post_value_value, $key, $post_value, $post_id, $this->settings );
					}
				} else {
					foreach ( $post_value as $post_value_index => $post_value_inside ) {
						if ( ! is_array( $post_value_inside ) ) {
							continue;
						}
						foreach ( $post_value_inside as $post_value_key => $post_value_value ) {
							$key = $this->settings['sample_field_key'] . '_' . $post_value_key . '_i_' . $post_value_index;

							$serialized_columns_values[ $key ] = apply_filters( 'vg_sheet_editor/serialized_addon/load_cell_value', $post_value_value, $key, $post_value, $post_id, $this->settings );
						}
					}
				}
				wp_cache_set( $cache_key, $serialized_columns_values );
			}

			$serialized_columns_values = apply_filters( 'vg_sheet_editor/serialized_addon/serialized_columns_values', $serialized_columns_values, $post_id, null, $this->settings );
			return $serialized_columns_values;
		}
		function filter_cell_data_for_readings( $value, $post_id, $column_key ) {
			if ( strpos( $column_key, $this->settings['sample_field_key'] ) === false ) {
				return $value;
			}
			$serialized_columns_values = $this->_get_serialized_column_values( $post_id );
			if ( $serialized_columns_values ) {
				return $value;
			}

			if ( isset( $serialized_columns_values[ $column_key ] ) ) {
				$value = $serialized_columns_values[ $column_key ];
			}

			return $value;
		}

		function allow_in_variations( $variation_columns ) {
			$variation_columns = array_merge( $variation_columns, $this->column_keys );
			return $variation_columns;
		}

		function save_column( $item, $post_type, $column_settings, $key ) {
			if ( ! $this->post_type_has_serialized_field( $post_type ) || strpos( $key, $this->settings['sample_field_key'] ) === false || ! empty( $column_settings['infinite_serialized_handler'] ) ) {
				return;
			}

			$post_id = VGSE()->helpers->sanitize_integer( $item['ID'] );

			$value = $item[ $key ];

			$criteria_parts = explode( '_i_', str_replace( $this->settings['sample_field_key'] . '_', '', $key ) );

			if ( is_numeric( current( $criteria_parts ) ) ) {
				$criteria_key   = end( $criteria_parts );
				$criteria_index = current( $criteria_parts );
			} else {
				$criteria_key   = current( $criteria_parts );
				$criteria_index = end( $criteria_parts );
			}

			if ( is_numeric( $criteria_index ) ) {
				$criteria_index = (int) $criteria_index;
			}

			$post_criterias = maybe_unserialize( VGSE()->helpers->get_current_provider()->get_item_meta( $post_id, $this->settings['sample_field_key'], true, 'save', true ) );

			if ( empty( $post_criterias ) || ! is_array( $post_criterias ) ) {
				$post_criterias = array();
			}

			if ( ! empty( $column_settings['is_single_level'] ) ) {
				$post_criterias[ $criteria_key ] = $value;
			} else {
				$post_criterias[ $criteria_index ][ $criteria_key ] = $value;
			}

			// Make sure that we don't save empty arrays
			if ( is_array( $post_criterias ) ) {
				$number_of_empty_strings = 0;
				foreach ( $post_criterias as $value ) {
					if ( is_string( $value ) && empty( $value ) ) {
						$number_of_empty_strings++;
					}
				}

				if ( count( $post_criterias ) === $number_of_empty_strings ) {
					$post_criterias = null;
				}
			}

			VGSE()->helpers->get_current_provider()->update_item_meta( $post_id, $this->settings['sample_field_key'], apply_filters( 'vg_sheet_editor/serialized_addon/save_cell', $post_criterias, $post_id, $this->settings, $item, $post_type, $column_settings, $key ) );

			$cache_key = 'wpse_sf' . $post_id . '_' . $this->settings['sample_field_key'];
			wp_cache_delete( $cache_key );
		}

		function post_type_has_serialized_field( $post_type ) {

			$allowed = in_array( $post_type, $this->settings['allowed_post_types'] );

			return $allowed;
		}

		function get_first_set_keys() {
			if ( ! empty( $this->settings['sample_field'] ) && is_array( $this->settings['sample_field'] ) ) {
				$sample_field = $this->settings['sample_field'];
			} else {
				$sample_field = maybe_unserialize( VGSE()->helpers->get_current_provider()->get_item_meta( $this->settings['sample_post_id'], $this->settings['sample_field_key'], true ) );
			}
			if ( ! is_array( $sample_field ) ) {
				return array();
			}

			if ( $this->settings['is_single_level'] ) {
				$first_set_keys = array_keys( $sample_field );
			} else {
				$first_set = current( $sample_field );
				if ( empty( $first_set ) ) {
					return array();
				}
				$first_set_keys = array_keys( current( $sample_field ) );
			}

			return $first_set_keys;
		}

		function register_columns( $editor ) {

			$post_types = $editor->args['enabled_post_types'];

			$first_set_keys = $this->get_first_set_keys();
			if ( empty( $first_set_keys ) ) {
				return;
			}

			foreach ( $post_types as $post_type ) {

				if ( ! $this->post_type_has_serialized_field( $post_type ) ) {
					continue;
				}

				if ( is_int( $this->settings['level'] ) ) {
					$this->settings['level'] = range( $this->settings['index_start'], $this->settings['index_start'] + $this->settings['level'] - 1 );
				}
				$label_index = $this->settings['label_index_start'];
				foreach ( $this->settings['level'] as $i ) {
					if ( method_exists( $editor->args['columns'], 'columns_limit_reached' ) && $editor->args['columns']->columns_limit_reached( $post_type ) ) {
						$editor->args['columns']->add_rejection( $this->settings['sample_field_key'], 'columns_limit_reached', $post_type );
						break;
					}

					foreach ( $first_set_keys as $field ) {
						if ( method_exists( $editor->args['columns'], 'columns_limit_reached' ) && $editor->args['columns']->columns_limit_reached( $post_type ) ) {
							$editor->args['columns']->add_rejection( $this->settings['sample_field_key'], 'columns_limit_reached', $post_type );
							break;
						}

						$field_label     = ( ! empty( $this->settings['column_title_prefix'] ) ? $this->settings['column_title_prefix'] : $this->settings['sample_field_key'] ) . ': ' . $field . ': ' . ( is_numeric( $i ) ? $label_index : $i );
						$key             = $this->settings['sample_field_key'] . '_' . $field . '_i_' . $i;
						$column_settings = apply_filters(
							'vg_sheet_editor/serialized_addon/column_settings',
							array_merge(
								array(
									'data_type'            => 'meta_data',
									'key'                  => $key,
									'unformatted'          => array( 'data' => $key ),
									'column_width'         => ( empty( $this->settings['column_width'] ) ) ? 150 : $this->settings['column_width'],
									'title'                => ucwords( str_replace( array( '_', '-' ), ' ', $field_label ) ),
									'type'                 => '',
									'supports_formulas'    => true,
									'formatted'            => array( 'data' => $key ),
									'allow_to_hide'        => true,
									'allow_direct_search'  => false,
									'allow_search_during_import' => false,
									'allow_for_variations' => ! empty( $this->settings['allow_in_wc_product_variations'] ),
									'allow_to_rename'      => true,
									// Allow to edit on view requests, this will prevent core from adding the
									// lock icon and locking edits, and it will disable saving because this class
									// has its own saving controller
									'allow_to_save'        => ( ( isset( $_REQUEST['action'] ) && in_array( $_REQUEST['action'], array( 'vgse_load_data', 'vgse_insert_individual_post' ) ) ) || empty( $_POST ) ) ? true : false,
									'is_single_level'      => $this->settings['is_single_level'],
									'default_value'        => ( ! empty( $this->settings['sample_field'][ $field ] ) ) ? $this->settings['sample_field'][ $field ] : '',
									'serialized_field_type' => 'old',
									'serialized_field_settings' => $this->settings,
									'serialized_field_original_key' => $this->settings['sample_field_key'],
								),
								$this->settings['column_settings']
							),
							$first_set_keys,
							$field,
							$key,
							$post_type,
							$this->settings
						);

						if ( ! empty( $column_settings ) ) {
							$editor->args['columns']->register_item( $key, $post_type, $column_settings );
							$this->column_keys[] = $key;
						}
					}
					$label_index++;
				}
			}
		}

		function __set( $name, $value ) {
			$this->$name = $value;
		}

		function __get( $name ) {
			return $this->$name;
		}

	}

}
