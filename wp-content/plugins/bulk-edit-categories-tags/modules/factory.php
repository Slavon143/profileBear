<?php

if ( !class_exists( 'WPSE_Sheet_Factory' ) ) {
    /**
     * Display woocommerce item in the toolbar to tease users of the free 
     * version into purchasing the premium plugin.
     */
    class WPSE_Sheet_Factory
    {
        var  $args = array() ;
        var  $sheets_bootstrap = null ;
        function __construct( $args = array() )
        {
            $defaults = array(
                'fs_object'                         => null,
                'post_type'                         => array(),
                'post_type_label'                   => array(),
                'serialized_columns'                => array(),
                'columns'                           => array(),
                'toolbars'                          => array(),
                'register_default_columns'          => true,
                'register_default_taxonomy_columns' => true,
                'bootstrap_class'                   => 'WP_Sheet_Editor_Bootstrap',
                'allowed_columns'                   => array(),
                'remove_columns'                    => array(),
            );
            $this->args = wp_parse_args( $args, $defaults );
            if ( empty($this->args['post_type']) ) {
                return;
            }
            add_action( 'vg_sheet_editor/initialized', array( $this, 'init' ) );
            add_action( 'vg_sheet_editor/after_init', array( $this, 'after_full_core_init' ) );
        }
        
        function after_full_core_init()
        {
            // Set up spreadsheet.
            // Allow to bootstrap editor manually, later.
            
            if ( !apply_filters( 'vg_sheet_editor/bootstrap/manual_init', false ) ) {
                $bootstrap_class = $this->get_prop( 'bootstrap_class' );
                $this->sheets_bootstrap = new $bootstrap_class( array(
                    'allowed_post_types'          => array(),
                    'only_allowed_spreadsheets'   => false,
                    'enabled_post_types'          => $this->get_prop( 'post_type' ),
                    'register_toolbars'           => true,
                    'register_columns'            => $this->get_prop( 'register_default_columns' ),
                    'register_taxonomy_columns'   => $this->get_prop( 'register_default_taxonomy_columns' ),
                    'register_admin_menus'        => true,
                    'register_spreadsheet_editor' => true,
                    'post_type_labels'            => array_combine( $this->get_prop( 'post_type' ), $this->get_prop( 'post_type_label' ) ),
                ) );
            }
        
        }
        
        function get_prop( $key, $default = null )
        {
            return ( isset( $this->args[$key] ) ? $this->args[$key] : $default );
        }
        
        function init()
        {
            if ( !is_admin() && !apply_filters( 'vg_sheet_editor/allowed_on_frontend', false ) ) {
                return;
            }
            
            if ( is_callable( $this->args['post_type'] ) ) {
                $post_types = call_user_func( $this->get_prop( 'post_type' ) );
                $this->args['post_type'] = $post_types['post_types'];
                $this->args['post_type_label'] = $post_types['labels'];
            }
            
            add_action( 'vg_sheet_editor/editor/before_init', array( $this, 'register_columns' ), 60 );
            add_action( 'vg_sheet_editor/editor/before_init', array( $this, 'lock_disallowed_columns' ), 90 );
            add_action( 'vg_sheet_editor/editor/before_init', array( $this, 'remove_columns' ), 90 );
            add_action( 'vg_sheet_editor/editor/before_init', array( $this, 'register_toolbars' ), 10 );
            add_filter(
                'vg_sheet_editor/custom_columns/teaser/allow_to_lock_column',
                array( $this, 'dont_lock_allowed_columns' ),
                99,
                2
            );
            add_filter( 'vg_sheet_editor/custom_post_types/get_all_post_types', array( $this, 'disable_from_custom_post_types_addon_object' ) );
            add_filter( 'vg_sheet_editor/custom_post_types/get_all_post_types_names', array( $this, 'disable_from_custom_post_types_addon_names' ) );
            // We only need it for the "add custom columns" page, if we activate it globally it causes issues in the front end sheet
            // i.e. the taxonomies sheet shows columns as a post type (title, slug, etc.)
            $is_page_allowed = !empty($_GET['page']) && in_array( $_GET['page'], array( 'vg_sheet_editor_custom_columns', 'vg_sheet_editor_post_type_setup' ), true );
            $is_uri_allowed = strpos( $_SERVER['REQUEST_URI'], '/profile.php' ) !== false || strpos( $_SERVER['REQUEST_URI'], '/user-edit.php' ) !== false;
            if ( $is_page_allowed || $is_uri_allowed ) {
                add_filter( 'vg_sheet_editor/allowed_post_types', array( $this, 'allow_post_types' ) );
            }
            add_filter( 'vg_sheet_editor/frontend/allowed_post_types', array( $this, 'allow_post_types' ) );
        }
        
        function disable_from_custom_post_types_addon_names( $post_types_names )
        {
            foreach ( $this->args['post_type'] as $post_type ) {
                if ( $index = array_search( $post_type, $post_types_names ) ) {
                    unset( $post_types_names[$index] );
                }
            }
            return $post_types_names;
        }
        
        function disable_from_custom_post_types_addon_object( $post_types_objects )
        {
            $indexed_post_types = wp_list_pluck( $post_types_objects, 'name' );
            foreach ( $this->args['post_type'] as $post_type ) {
                if ( $index = array_search( $post_type, $indexed_post_types ) ) {
                    unset( $post_types_objects[$index] );
                }
            }
            return $post_types_objects;
        }
        
        function allow_post_types( $post_types )
        {
            $labels = $this->get_prop( 'post_type_label' );
            foreach ( $this->args['post_type'] as $index => $post_type ) {
                if ( isset( $post_types[$post_type] ) ) {
                    continue;
                }
                $post_types[$post_type] = $labels[$index];
            }
            return $post_types;
        }
        
        function append_post_type_to_post_types_list( $post_types, $args, $output )
        {
            // @todo Test
            $labels = $this->get_prop( 'post_type_label' );
            foreach ( $this->args['post_type'] as $index => $post_type ) {
                if ( isset( $post_types[$post_type] ) ) {
                    continue;
                }
                
                if ( $output === 'names' ) {
                    $post_types[$post_type] = $labels[$index];
                } else {
                    $post_types[$post_type] = (object) array(
                        'label' => $labels[$index],
                        'name'  => $post_type,
                    );
                }
            
            }
            return $post_types;
        }
        
        function allow_post_type( $post_types )
        {
            $labels = $this->get_prop( 'post_type_label' );
            foreach ( $this->args['post_type'] as $index => $post_type ) {
                $post_types[$post_type] = $labels[$index];
            }
            return $post_types;
        }
        
        function dont_lock_allowed_columns( $allowed_to_lock, $column_key )
        {
            if ( !empty($this->args['allowed_columns']) ) {
                $allowed_to_lock = !$this->is_column_allowed( $column_key );
            }
            return $allowed_to_lock;
        }
        
        /**
         * Register spreadsheet columns
         */
        function register_toolbars( $editor )
        {
            $post_types = $this->get_prop( 'post_type' );
            if ( !in_array( $editor->args['provider'], $post_types ) ) {
                return;
            }
            
            if ( $this->toolbars ) {
                $toolbars = ( is_callable( $this->toolbars ) ? call_user_func( $this->get_prop( 'toolbars' ) ) : $this->get_prop( 'toolbars' ) );
                if ( empty($toolbars) ) {
                    return;
                }
                foreach ( $post_types as $post_type ) {
                    foreach ( $toolbars as $key => $toolbar ) {
                        $editor->args['toolbars']->register_item( $key, $toolbar, $post_type );
                    }
                }
            }
            
            if ( current_user_can( 'install_plugins' ) ) {
                foreach ( $post_types as $post_type ) {
                    $editor->args['toolbars']->register_item( 'wpse_license', array(
                        'type'                  => 'button',
                        'content'               => __( 'My license', VGSE()->textname ),
                        'url'                   => $this->args['fs_object']->get_account_url(),
                        'toolbar_key'           => 'secondary',
                        'extra_html_attributes' => ' target="_blank" ',
                        'allow_in_frontend'     => false,
                        'fs_id'                 => $this->args['fs_object']->get_id(),
                    ), $post_type );
                }
            }
        }
        
        /**
         * Register spreadsheet columns
         */
        function register_columns( $editor )
        {
            $post_types = $this->get_prop( 'post_type' );
            if ( !in_array( $editor->args['provider'], $post_types ) || !$this->columns ) {
                return;
            }
            $columns = ( is_callable( $this->columns ) ? call_user_func( $this->get_prop( 'columns' ) ) : $this->get_prop( 'columns' ) );
            if ( empty($columns) ) {
                return;
            }
            foreach ( $post_types as $post_type ) {
                foreach ( $columns as $column_key => $column ) {
                    $editor->args['columns']->register_item( $column_key, $post_type, $column );
                }
            }
        }
        
        function remove_columns( $editor )
        {
            $post_types = $this->get_prop( 'post_type' );
            if ( !in_array( $editor->args['provider'], $post_types ) || !$this->args['remove_columns'] ) {
                return;
            }
            foreach ( $post_types as $post_type ) {
                foreach ( $this->args['remove_columns'] as $column_key ) {
                    $editor->args['columns']->remove_item( $column_key, $post_type );
                }
            }
        }
        
        function lock_disallowed_columns( $editor )
        {
            $post_types = $this->get_prop( 'post_type' );
            if ( !in_array( $editor->args['provider'], $post_types ) || !$this->columns ) {
                return;
            }
            $columns = ( is_callable( $this->columns ) ? call_user_func( $this->get_prop( 'columns' ) ) : $this->get_prop( 'columns' ) );
            if ( empty($columns) ) {
                return;
            }
            foreach ( $post_types as $post_type ) {
                
                if ( !empty($this->args['allowed_columns']) ) {
                    // Increase column width for disabled columns, so the "premium" message fits
                    $spreadsheet_columns = $editor->args['columns']->get_provider_items( $post_type );
                    foreach ( $spreadsheet_columns as $key => $column ) {
                        if ( !$this->is_column_allowed( $key ) ) {
                            $editor->args['columns']->register_item(
                                $key,
                                $post_type,
                                array(
                                'column_width'      => $column['column_width'] + 80,
                                'is_locked'         => true,
                                'lock_template_key' => 'lock_cell_template_pro',
                            ),
                                true
                            );
                        }
                    }
                }
            
            }
        }
        
        function is_column_allowed( $column_key )
        {
            $allowed_columns = $this->allowed_columns;
            if ( empty($allowed_columns) ) {
                return true;
            }
            $allowed = false;
            foreach ( $allowed_columns as $allowed_column ) {
                
                if ( strpos( $column_key, $allowed_column ) !== false ) {
                    $allowed = true;
                    break;
                }
            
            }
            return apply_filters(
                'vg_sheet_editor/factory/is_column_allowed',
                $allowed,
                $column_key,
                $this
            );
        }
        
        function __set( $name, $value )
        {
            $this->args[$name] = $value;
        }
        
        function __get( $name )
        {
            return $this->get_prop( $name );
        }
    
    }
}