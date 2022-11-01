<?php

if ( !class_exists( 'WP_Sheet_Editor_CORE_Modules_Init' ) ) {
    class WP_Sheet_Editor_CORE_Modules_Init
    {
        var  $product_directory = null ;
        var  $freemius_instance = null ;
        function __construct( $product_directory, $freemius_instance = null, $auto_init = true )
        {
            $this->product_directory = $product_directory;
            $this->freemius_instance = $freemius_instance;
            
            if ( $auto_init ) {
                $priority = ( strpos( $product_directory, 'wp-sheet-editor' ) !== false ? 1 : 10 );
                add_filter( 'vg_sheet_editor/modules/list', array( $this, 'filter_disallowed_modules_on_editor_page' ), $priority );
                $this->init( array( 'wp-sheet-editor' ) );
                add_action( 'plugins_loaded', array( $this, 'init' ) );
            }
        
        }
        
        public function get_editor_page_key()
        {
            $out = false;
            if ( isset( $_GET['page'] ) && strpos( $_GET['page'], 'vgse-bulk-edit-' ) !== false ) {
                $out = str_replace( 'vgse-bulk-edit-', '', sanitize_text_field( $_GET['page'] ) );
            }
            return apply_filters( 'vg_sheet_editor/modules_init/editor_page_key', $out );
        }
        
        function filter_disallowed_modules_on_editor_page( $directories )
        {
            $package_settings = $this->get_package();
            $current_editor_key = $this->get_editor_page_key();
            
            if ( $current_editor_key && is_object( $this->freemius_instance ) && !empty($package_settings) && !empty($package_settings['vgEditorKeys']) && in_array( $current_editor_key, $package_settings['vgEditorKeys'], true ) ) {
                $directories = ( $this->freemius_instance->can_use_premium_code__premium_only() ? array_merge( $package_settings['sheetEditorModules']['free'], $package_settings['sheetEditorModules']['pro'] ) : $package_settings['sheetEditorModules']['free'] );
                if ( !empty($package_settings['sheetEditorModules']['dev']) ) {
                    $directories = ( defined( 'VGSE_DEBUG' ) && VGSE_DEBUG ? array_merge( $directories, $package_settings['sheetEditorModules']['dev'] ) : array_diff( $directories, $package_settings['sheetEditorModules']['dev'] ) );
                }
            }
            
            return $directories;
        }
        
        function get_package()
        {
            $plugin_slug = basename( $this->product_directory );
            $package_file = WP_PLUGIN_DIR . '/' . $plugin_slug . '/package.json';
            $package_settings = ( file_exists( $package_file ) ? json_decode( file_get_contents( $package_file ), true ) : array() );
            return $package_settings;
        }
        
        /**
         * Get all modules in the folder
         * @return array
         */
        function get_modules_list()
        {
            $directories = glob( $this->product_directory . '/modules/*', GLOB_ONLYDIR );
            if ( !empty($directories) ) {
                $directories = array_map( 'basename', $directories );
            }
            $plugin_slug = basename( $this->product_directory );
            $package_settings = $this->get_package();
            
            if ( !empty($package_settings['sheetEditorModules']) ) {
                // If we're developing locally and the package.json file exists, only load the defined modules
                $directories = array_intersect( $directories, array_merge( $package_settings['sheetEditorModules']['free'], $package_settings['sheetEditorModules']['pro'] ) );
                // If we're developing locally and the package.json file exists, only load the allowed modules according to the freemius license
                if ( is_object( $this->freemius_instance ) && !$this->freemius_instance->can_use_premium_code__premium_only() ) {
                    $directories = array_intersect( $directories, $package_settings['sheetEditorModules']['free'] );
                }
                if ( !empty($package_settings['sheetEditorModules']['dev']) ) {
                    $directories = ( defined( 'VGSE_DEBUG' ) && VGSE_DEBUG ? array_merge( $directories, $package_settings['sheetEditorModules']['dev'] ) : array_diff( $directories, $package_settings['sheetEditorModules']['dev'] ) );
                }
            }
            
            $parent_plugin_slug = str_replace( array( '-premium' ), '', $plugin_slug );
            $directories = apply_filters( 'vg_sheet_editor/modules/' . $parent_plugin_slug . '/list', $directories );
            return apply_filters( 'vg_sheet_editor/modules/list', $directories, $parent_plugin_slug );
        }
        
        function init( $modules = array() )
        {
            if ( empty($modules) ) {
                $modules = $this->get_modules_list();
            }
            if ( empty($modules) ) {
                return;
            }
            // Load all modules
            foreach ( $modules as $module ) {
                $paths = array( $this->product_directory . "/modules/{$module}/{$module}.php" );
                if ( $module === 'wp-sheet-editor' ) {
                    $paths[] = $this->product_directory . "/modules/{$module}/dev/{$module}.php";
                }
                foreach ( $paths as $path ) {
                    if ( file_exists( $path ) ) {
                        require_once $path;
                    }
                }
            }
            $plugin_inc_files = glob( untrailingslashit( $this->product_directory ) . '/inc/*.php' );
            $inc_files = array_merge( glob( untrailingslashit( __DIR__ ) . '/*.php' ), $plugin_inc_files, glob( untrailingslashit( $this->product_directory ) . '/inc/integrations/*.php' ) );
            foreach ( $inc_files as $inc_file ) {
                if ( !is_file( $inc_file ) ) {
                    continue;
                }
                require_once $inc_file;
            }
            // Not like register_uninstall_hook(), you do NOT have to use a static function.
            $this->freemius_instance->add_action( 'after_uninstall', array( $this, 'on_uninstall' ) );
            $this->freemius_instance->add_filter( 'plugin_icon', array( $this, 'load_custom_icon' ) );
            $this->freemius_instance->add_filter( 'show_deactivation_feedback_form', '__return_false' );
            // free code users if they ever had a licence show only the support menu which is the free forum
            $this->freemius_instance->add_filter(
                'is_submenu_visible',
                array( $this, 'hide_contact_page_if_free' ),
                10,
                2
            );
        }
        
        function hide_contact_page_if_free( $is_visible, $menu_id )
        {
            if ( 'contact' === $menu_id ) {
                return false;
            }
            return $is_visible;
        }
        
        function show_contact_page_if_premium( $is_visible, $menu_id )
        {
            if ( 'contact' === $menu_id ) {
                return $this->freemius_instance->has_any_license();
            }
            return $is_visible;
        }
        
        function load_custom_icon( $path )
        {
            if ( defined( 'VGSE_DIR' ) ) {
                $path = VGSE_DIR . '/assets/imgs/freemius-icon.png';
            }
            return $path;
        }
        
        function on_uninstall()
        {
            do_action( 'vg_sheet_editor/on_uninstall', $this->product_directory, $this->freemius_instance );
        }
        
        function __set( $name, $value )
        {
            $this->{$name} = $value;
        }
        
        function __get( $name )
        {
            return $this->{$name};
        }
    
    }
}
// WPML : Change language to english when we load the spreadsheet page

if ( !function_exists( 'vgse_filter_wpml_admin_language' ) ) {
    if ( isset( $_GET['page'] ) && strpos( $_GET['page'], 'vgse-bulk-edit-' ) !== false && strpos( json_encode( $_COOKIE ), '_icl_' ) !== false ) {
        add_filter(
            'get_user_metadata',
            'vgse_filter_wpml_admin_language',
            10,
            4
        );
    }
    function vgse_filter_wpml_admin_language(
        $value,
        $user_id,
        $meta_key,
        $single
    )
    {
        global  $wpdb ;
        $rtl_langs = array(
            'ar',
            'arc',
            'dv',
            'fa',
            'ha',
            'he',
            'khw',
            'ks',
            'ku',
            'ps',
            'ur',
            'yi'
        );
        
        if ( $meta_key === 'icl_admin_language' && $user_id ) {
            $user_lang = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key = 'icl_admin_language'", $user_id ) );
            if ( in_array( $user_lang, $rtl_langs, true ) ) {
                $value = ( $single ? 'en' : array( 'en' ) );
            }
        }
        
        return $value;
    }

}
