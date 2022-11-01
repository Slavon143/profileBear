<?php

if ( !class_exists( 'WPSE_Taxonomy_Terms_Sheet' ) ) {
    class WPSE_Taxonomy_Terms_Sheet extends WPSE_Sheet_Factory
    {
        var  $parent_id_mapping = array() ;
        public function __construct()
        {
            $allowed_columns = array();
            $allowed_columns = array();
            parent::__construct( array(
                'fs_object'                         => wpsett_fs(),
                'post_type'                         => array( $this, 'get_taxonomies_and_labels' ),
                'post_type_label'                   => '',
                'serialized_columns'                => array(),
                'register_default_taxonomy_columns' => false,
                'bootstrap_class'                   => 'WPSE_Taxonomy_Terms_Spreadsheet_Bootstrap',
                'columns'                           => array( $this, 'get_columns' ),
                'allowed_columns'                   => $allowed_columns,
                'remove_columns'                    => array(),
            ) );
            add_filter(
                'vg_sheet_editor/provider/default_provider_key',
                array( $this, 'set_default_provider_for_taxonomies' ),
                10,
                2
            );
            add_filter(
                'vg_sheet_editor/provider/term/update_item_meta',
                array( $this, 'filter_cell_data_for_saving' ),
                10,
                3
            );
            add_filter(
                'vg_sheet_editor/provider/term/get_item_meta',
                array( $this, 'filter_cell_data_for_readings' ),
                10,
                5
            );
            add_filter(
                'vg_sheet_editor/provider/term/get_item_data',
                array( $this, 'filter_cell_data_for_readings' ),
                10,
                6
            );
            add_filter(
                'vg_sheet_editor/handsontable/custom_args',
                array( $this, 'enable_row_sorting' ),
                10,
                2
            );
            add_action( 'vg_sheet_editor/after_enqueue_assets', array( $this, 'register_assets' ) );
            add_action( 'wp_ajax_woocommerce_term_ordering', array( $this, 'woocommerce_term_ordering' ), 1 );
            add_filter(
                'vg_sheet_editor/columns/blacklisted_columns',
                array( $this, 'blacklist_private_columns' ),
                10,
                2
            );
            add_filter(
                'vg_sheet_editor/import/find_post_id',
                array( $this, 'find_existing_term_by_slug_for_import' ),
                10,
                6
            );
            add_action( 'vg_sheet_editor/import/before_existing_wp_check_message', array( $this, 'add_wp_check_message_for_import' ) );
            add_filter(
                'vg_sheet_editor/import/wp_check/available_columns_options',
                array( $this, 'filter_wp_check_options_for_import' ),
                10,
                2
            );
            add_filter( 'vg_sheet_editor/welcome_url', array( $this, 'filter_welcome_url' ) );
            add_action(
                'vg_sheet_editor/filters/after_fields',
                array( $this, 'add_filters_fields' ),
                10,
                2
            );
            add_filter(
                'vg_sheet_editor/load_rows/wp_query_args',
                array( $this, 'filter_posts' ),
                10,
                2
            );
            add_filter(
                'vg_sheet_editor/filters/sanitize_request_filters',
                array( $this, 'register_custom_filters' ),
                10,
                2
            );
            add_filter(
                'terms_clauses',
                array( $this, 'search_by_multiple_parents' ),
                10,
                3
            );
        }
        
        public function merge_duplicate_terms( $taxonomy_key )
        {
            global  $wpdb ;
            $sql = $wpdb->prepare( "SELECT term.name, taxonomy.parent, COUNT(*) count, GROUP_CONCAT(term.term_id SEPARATOR ',') as term_ids FROM {$wpdb->prefix}terms term\r\nLEFT JOIN {$wpdb->prefix}term_taxonomy taxonomy ON term.term_id = taxonomy.term_id \r\nWHERE taxonomy.taxonomy = %s\r\nGROUP BY term.name, taxonomy.parent \r\nHAVING count > 1 LIMIT 20", $taxonomy_key );
            $all_duplicate_terms = $wpdb->get_results( $sql, ARRAY_A );
            $deleted = array();
            foreach ( $all_duplicate_terms as $duplicate_terms ) {
                $term_ids = array_map( 'intval', explode( ',', $duplicate_terms['term_ids'] ) );
                $first_term_id = $term_ids[0];
                unset( $term_ids[0] );
                $deleted = array_merge( $deleted, $term_ids );
                $this->_merge_terms__premium_only( $term_ids, $first_term_id, $taxonomy_key );
            }
            $out = compact( 'deleted', 'all_duplicate_terms', 'sql' );
            return $out;
        }
        
        public function search_by_multiple_parents( $pieces, $taxonomies, $args )
        {
            // Check if our custom argument, 'wpse_term_parents' is set, if not, bail
            if ( !isset( $args['wpse_term_parents'] ) || !is_array( $args['wpse_term_parents'] ) ) {
                return $pieces;
            }
            // If  'wpse_term_parents' is set, make sure that 'parent' and 'child_of' is not set
            if ( $args['parent'] || $args['child_of'] ) {
                return $pieces;
            }
            // Validate the array as an array of integers
            $parents = array_map( 'intval', $args['wpse_term_parents'] );
            // Loop through $parents and set the WHERE clause accordingly
            $where = array();
            foreach ( $parents as $parent ) {
                // Make sure $parent is not 0, if so, skip and continue
                if ( 0 === $parent ) {
                    continue;
                }
                $where[] = " tt.parent = '{$parent}'";
            }
            if ( !$where ) {
                return $pieces;
            }
            $where_string = implode( ' OR ', $where );
            $pieces['where'] .= " AND ( {$where_string} ) ";
            return $pieces;
        }
        
        public function register_custom_filters( $sanitized_filters, $dirty_filters )
        {
            if ( isset( $dirty_filters['parent_term_keyword'] ) ) {
                $sanitized_filters['parent_term_keyword'] = sanitize_text_field( $dirty_filters['parent_term_keyword'] );
            }
            return $sanitized_filters;
        }
        
        /**
         * Apply filters to wp-query args
         * @param array $query_args
         * @param array $data
         * @return array
         */
        public function filter_posts( $query_args, $data )
        {
            
            if ( !empty($data['filters']) ) {
                $filters = WP_Sheet_Editor_Filters::get_instance()->get_raw_filters( $data );
                
                if ( !empty($filters['parent_term_keyword']) ) {
                    $terms_by_keyword = get_terms( array(
                        'hide_empty'             => false,
                        'update_term_meta_cache' => false,
                        'name__like'             => sanitize_text_field( $filters['parent_term_keyword'] ),
                        'fields'                 => 'ids',
                    ) );
                    $query_args['wpse_term_parents'] = $terms_by_keyword;
                }
            
            }
            
            return $query_args;
        }
        
        public function add_filters_fields( $current_post_type, $filters )
        {
            if ( !taxonomy_exists( $current_post_type ) ) {
                return;
            }
            
            if ( is_taxonomy_hierarchical( $current_post_type ) ) {
                ?>
				<li>
					<label><?php 
                _e( 'Parent keyword', VGSE()->textname );
                ?> <a href="#" data-wpse-tooltip="right" aria-label="<?php 
                _e( 'We will display all the categories below parent that contains this keyword', VGSE()->textname );
                ?>">( ? )</a></label>
					<input type="text" name="parent_term_keyword" />							
				</li>
				<?php 
            }
        
        }
        
        public function filter_welcome_url( $url )
        {
            $url = esc_url( admin_url( 'admin.php?page=wpsett_welcome_page' ) );
            return $url;
        }
        
        public function filter_wp_check_options_for_import( $columns, $taxonomy )
        {
            if ( !taxonomy_exists( $taxonomy ) ) {
                return $columns;
            }
            $columns = array(
                'ID'   => $columns['ID'],
                'slug' => $columns['slug'],
            );
            return $columns;
        }
        
        public function add_wp_check_message_for_import( $taxonomy )
        {
            if ( !taxonomy_exists( $taxonomy ) ) {
                return;
            }
            ?>
			<style>.field-find-existing-columns .wp-check-message { display: none; }</style>
			<p class="wp-custom-check-message"><?php 
            _e( 'We find items that have the same SLUG in the CSV and the WP Field.<br>Please select the CSV column that contains the slug.<br>You must import the slug column if you want to update existing categories, items without slug will be created as new.', vgse_taxonomy_terms()->textname );
            ?></p>
			<?php 
        }
        
        public function find_existing_term_by_slug_for_import(
            $term_id,
            $row,
            $taxonomy,
            $meta_query,
            $writing_type,
            $check_wp_fields
        )
        {
            
            if ( taxonomy_exists( $taxonomy ) ) {
                $default_term_id = PHP_INT_MAX;
                
                if ( !empty($row['ID']) && in_array( 'ID', $check_wp_fields ) ) {
                    $term_id = ( term_exists( (int) $row['ID'], $taxonomy ) ? (int) $row['ID'] : null );
                } else {
                    
                    if ( !empty($row['old_slug']) && in_array( 'old_slug', $check_wp_fields ) ) {
                        $slug = $row['old_slug'];
                    } elseif ( !empty($row['slug']) && in_array( 'slug', $check_wp_fields ) ) {
                        $slug = $row['slug'];
                    }
                    
                    
                    if ( !empty($slug) ) {
                        $term = get_term_by( 'slug', $slug, $taxonomy );
                        if ( $term && !is_wp_error( $term ) ) {
                            $term_id = $term->term_id;
                        }
                    }
                
                }
                
                if ( !$term_id ) {
                    $term_id = $default_term_id;
                }
            }
            
            return $term_id;
        }
        
        public function blacklist_private_columns( $blacklisted_fields, $provider )
        {
            if ( !in_array( $provider, $this->post_type ) ) {
                return $blacklisted_fields;
            }
            //          We have allowed the product_count_xxx" term meta because WooCommerce uses this as usage count
            //          so we need this for the searches to delete unused tags and categories,
            //          to prevent confusions we are blacklisting the core count column
            //          $blacklisted_fields[] = '^product_count_';
            if ( in_array( $provider, array( 'product_cat', 'product_tag' ), true ) ) {
                $blacklisted_fields[] = '^count$';
            }
            return $blacklisted_fields;
        }
        
        // WooCommerce returns 0 even on success, so we must return
        // something to avoid showing the automatic ajax error notification
        // that sheet editor shows
        public function woocommerce_term_ordering()
        {
            echo  1 ;
        }
        
        /**
         * Register frontend assets
         */
        public function register_assets()
        {
            wp_enqueue_script(
                'wpse-taxonomy-terms-js',
                plugins_url( '/assets/js/init.js', vgse_taxonomy_terms()->args['main_plugin_file'] ),
                array(),
                VGSE()->version,
                false
            );
            wp_localize_script( 'wpse-taxonomy-terms-js', 'wpse_tt_data', array(
                'sort_icon_url' => plugins_url( '/assets/imgs/sort-icon.png', vgse_taxonomy_terms()->args['main_plugin_file'] ),
            ) );
        }
        
        public function enable_row_sorting( $handsontable_args, $provider )
        {
            if ( function_exists( 'WC' ) && (strstr( $provider, 'pa_' ) || in_array( $provider, apply_filters( 'woocommerce_sortable_taxonomies', array( 'product_cat' ) ) )) ) {
                $handsontable_args['manualRowMove'] = true;
            }
            return $handsontable_args;
        }
        
        public function get_taxonomies_and_labels()
        {
            $out = array(
                'post_types' => array( 'category', 'post_tag' ),
                'labels'     => array( 'Blog categories', 'Blog tags' ),
            );
            return $out;
        }
        
        public function set_default_provider_for_taxonomies( $provider_class_key, $provider )
        {
            if ( taxonomy_exists( $provider ) ) {
                $provider_class_key = 'term';
            }
            return $provider_class_key;
        }
        
        public function filter_cell_data_for_readings(
            $value,
            $id,
            $key,
            $single,
            $context,
            $item = null
        )
        {
            if ( $context !== 'read' || $item && !in_array( $item->taxonomy, $this->post_type ) ) {
                return $value;
            }
            
            if ( $key === 'parent' && $value ) {
                $term = VGSE()->helpers->get_current_provider()->get_item( $value );
                $value = $term->name;
            }
            
            if ( $key === 'count' ) {
                $value = (int) $value;
            }
            return $value;
        }
        
        public function filter_cell_data_for_saving( $new_value, $id, $key )
        {
            if ( get_post_type( $id ) !== $this->post_type ) {
                return $new_value;
            }
            if ( $key === 'taxonomy_term_files' && is_array( $new_value ) ) {
                $new_value = $new_value;
            }
            return $new_value;
        }
        
        public function get_columns()
        {
        }
    
    }
    $GLOBALS['wpse_taxonomy_terms_sheet'] = new WPSE_Taxonomy_Terms_Sheet();
}
