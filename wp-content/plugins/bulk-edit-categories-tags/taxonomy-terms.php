<?php

/*
  Plugin Name: WP Sheet Editor - Taxonomy Terms
  Description: Edit categories and tags in a spreadsheet.
  Version: 1.7.0
  Author:      WP Sheet Editor
  Author URI:  https://wpsheeteditor.com/?utm_source=wp-admin&utm_medium=plugins-list&utm_campaign=taxonomy-terms
  Plugin URI: https://wpsheeteditor.com/go/taxonomy-terms-addon?utm_source=wp-admin&utm_medium=plugins-list&utm_campaign=taxonomy-terms
  License:     GPL2
  License URI: https://www.gnu.org/licenses/gpl-2.0.html
  WC requires at least: 3.0
  WC tested up to: 6.9
  Text Domain: vg_sheet_editor_taxonomy_terms
  Domain Path: /lang
*/
if ( isset( $_GET['wpse_troubleshoot8987'] ) ) {
    return;
}
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( function_exists( 'wpsett_fs' ) ) {
    wpsett_fs()->set_basename( false, __FILE__ );
}
require_once 'vendor/vg-plugin-sdk/index.php';
require_once 'vendor/freemius/start.php';
require_once 'inc/freemius-init.php';
if ( !class_exists( 'WP_Sheet_Editor_Taxonomy_Terms' ) ) {
    /**
     * Filter rows in the spreadsheet editor.
     */
    class WP_Sheet_Editor_Taxonomy_Terms
    {
        private static  $instance = false ;
        var  $plugin_url = null ;
        var  $plugin_dir = null ;
        var  $textname = 'vg_sheet_editor_taxonomy_terms' ;
        var  $buy_link = null ;
        var  $version = '1.5.4' ;
        var  $settings = null ;
        var  $args = null ;
        var  $vg_plugin_sdk = null ;
        private function __construct()
        {
        }
        
        function init_plugin_sdk()
        {
            $this->args = array(
                'main_plugin_file'         => __FILE__,
                'show_welcome_page'        => true,
                'welcome_page_file'        => $this->plugin_dir . '/views/welcome-page-content.php',
                'logo'                     => plugins_url( '/assets/imgs/logo-248x102.png', __FILE__ ),
                'buy_link'                 => $this->buy_link,
                'plugin_name'              => 'Bulk Edit Blog Categories and Tags',
                'plugin_prefix'            => 'wpsett_',
                'show_whatsnew_page'       => true,
                'whatsnew_pages_directory' => $this->plugin_dir . '/views/whats-new/',
                'plugin_version'           => $this->version,
                'plugin_options'           => $this->settings,
            );
            $this->vg_plugin_sdk = new VG_Freemium_Plugin_SDK( $this->args );
        }
        
        function notify_wrong_core_version()
        {
            $plugin_data = get_plugin_data( __FILE__, false, false );
            ?>
			<div class="notice notice-error">
				<p><?php 
            _e( 'Please update the WP Sheet Editor plugin and all its extensions to the latest version. The features of the plugin "' . $plugin_data['Name'] . '" will be disabled to prevent errors and they will be enabled automatically after you install the updates.', vgse_taxonomy_terms()->textname );
            ?></p>
			</div>
			<?php 
        }
        
        function init()
        {
            require_once __DIR__ . '/modules/init.php';
            $this->modules_controller = new WP_Sheet_Editor_CORE_Modules_Init( __DIR__, wpsett_fs() );
            $this->plugin_url = plugins_url( '/', __FILE__ );
            $this->plugin_dir = __DIR__;
            $this->buy_link = wpsett_fs()->checkout_url();
            $this->init_plugin_sdk();
            
            if ( !class_exists( 'VGSE_Provider_Abstract' ) ) {
                add_action( 'admin_notices', array( $this, 'notify_wrong_core_version' ) );
                return;
            }
            
            $integrations = array_merge( glob( __DIR__ . '/inc/providers/*.php' ), glob( __DIR__ . '/inc/*.php' ), glob( __DIR__ . '/inc/integrations/*.php' ) );
            foreach ( $integrations as $integration_file ) {
                require_once $integration_file;
            }
            // After core has initialized
            add_action( 'vg_sheet_editor/initialized', array( $this, 'after_core_init' ) );
            add_action( 'admin_init', array( $this, 'disable_free_plugins_when_premium_active' ), 1 );
            add_action( 'init', array( $this, 'after_init' ) );
        }
        
        function after_init()
        {
            load_plugin_textdomain( $this->textname, false, basename( dirname( __FILE__ ) ) . '/lang/' );
        }
        
        function disable_free_plugins_when_premium_active()
        {
            $free_plugins_path = array( 'bulk-edit-categories-tags/taxonomy-terms.php' );
            if ( is_plugin_active( 'bulk-edit-categories-tags-premium/taxonomy-terms.php' ) ) {
                foreach ( $free_plugins_path as $relative_path ) {
                    $path = wp_normalize_path( WP_PLUGIN_DIR . '/' . $relative_path );
                    if ( is_plugin_active( $relative_path ) ) {
                        deactivate_plugins( plugin_basename( $path ) );
                    }
                }
            }
        }
        
        function after_core_init()
        {
            
            if ( version_compare( VGSE()->version, '2.24.15' ) < 0 ) {
                add_action( 'admin_notices', array( $this, 'notify_wrong_core_version' ) );
                return;
            }
            
            // Override core buy link with this plugin´s
            VGSE()->buy_link = $this->buy_link;
            // Enable admin pages in case "frontend sheets" addon disabled them
            add_filter( 'vg_sheet_editor/register_admin_pages', '__return_true', 11 );
        }
        
        /**
         * Creates or returns an instance of this class.
         */
        static function get_instance()
        {
            
            if ( null == WP_Sheet_Editor_Taxonomy_Terms::$instance ) {
                WP_Sheet_Editor_Taxonomy_Terms::$instance = new WP_Sheet_Editor_Taxonomy_Terms();
                WP_Sheet_Editor_Taxonomy_Terms::$instance->init();
            }
            
            return WP_Sheet_Editor_Taxonomy_Terms::$instance;
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

if ( !function_exists( 'vgse_taxonomy_terms' ) ) {
    function vgse_taxonomy_terms()
    {
        return WP_Sheet_Editor_Taxonomy_Terms::get_instance();
    }
    
    vgse_taxonomy_terms();
}
