<?php
/**
 * Plugin Name: PW WooCommerce Bulk Edit
 * Plugin URI: https://www.pimwick.com/pw-bulk-edit/
 * Description: A powerful way to update your WooCommerce product catalog. Finally, no more tedious clicking through countless pages making the same change to all products!
 * Version: 2.106
 * Author: Pimwick, LLC
 * Author URI: https://www.pimwick.com
 * Text Domain: pw-bulk-edit
 * Domain Path: /languages
 *
 * WC requires at least: 4.0
 * WC tested up to: 7.0
 *
*/
define('PWBE_VERSION', '2.106');

/*
Copyright (C) Pimwick, LLC

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

// Exit if accessed directly
defined( 'ABSPATH' ) or exit;

// Increase the available memory since returning lots of data can often exhaust typical memory allocation amounts.
defined( 'PWBE_MEMORY_LIMIT' ) or define( 'PWBE_MEMORY_LIMIT', '1024M' );
if ( PWBE_MEMORY_LIMIT !== false ) {
    ini_set( 'memory_limit', PWBE_MEMORY_LIMIT );
    defined( 'WP_MEMORY_LIMIT' ) or define( 'WP_MEMORY_LIMIT', PWBE_MEMORY_LIMIT );
}

// Only change this if you are comfortable with possible unexpected behavior!
defined( 'PWBE_MAX_RESULTS' ) or define( 'PWBE_MAX_RESULTS', '1000' );

// Number of fields to save in a single AJAX call. Lower number can avoid HTTP 504 Timeout errors.
defined( 'PWBE_SAVE_BATCH_SIZE' ) or define( 'PWBE_SAVE_BATCH_SIZE', 25 );

// If the data contains product_variation records that are children of Simple Products you will want to
// enable this flag. This could make the query run slower.
defined( 'PWBE_PREFILTER_VARIATIONS' ) or define( 'PWBE_PREFILTER_VARIATIONS', false );

defined( 'PWBE_REQUIRES_CAPABILITY' ) or define( 'PWBE_REQUIRES_CAPABILITY', 'manage_woocommerce' );

// Verify this isn't called directly.
if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

if ( ! class_exists( 'PW_Bulk_Edit' ) ) :

register_uninstall_hook( __FILE__, array( 'PW_Bulk_Edit', 'plugin_uninstall' ) );

final class PW_Bulk_Edit {

    const NULL = '!!pwbe_null_value!!';

    static $options = array(
        'pwbe_help_dismiss_intro',
        'pwbe_help_minimize_filter_help',
        'pwbe_views',
        'pwbe_selected_view'
    );

    function __construct() {
        add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
        add_action( 'woocommerce_init', array( $this, 'woocommerce_init' ) );

        // Do things when a new version is installed.
        if ( get_option( 'pwbe_database_version' ) != PWBE_VERSION ) {

            // Go from a per-plugin setting to a global setting for hiding the Pimwick Plugins menu.
            if ( get_option( 'pwbe_hide_pimwick_menu', '' ) != '' ) {
                update_option( 'hide_pimwick_menu', get_option( 'pwbe_hide_pimwick_menu' ) );
                delete_option( 'pwbe_hide_pimwick_menu' );
            }

            // Record that the new version is installed.
            update_option( 'pwbe_database_version', PWBE_VERSION );
        }
    }

    function plugins_loaded() {
        load_plugin_textdomain( 'pw-bulk-edit', false, basename( dirname( __FILE__ ) ) . '/languages' );
    }

    function woocommerce_init() {
        remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
        remove_action( 'admin_print_styles', 'print_emoji_styles' );

        if ( is_admin() && current_user_can( get_option( 'pwbe_requires_capability', PWBE_REQUIRES_CAPABILITY ) ) ) {
            require_once( 'includes/columns.php' );
            require_once( 'includes/db.php' );
            require_once( 'includes/filters.php' );
            require_once( 'includes/settings.php' );
            require_once( 'includes/select-options.php' );
            require_once( 'includes/sql-builder.php' );
            require_once( 'includes/views.php' );

            add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ), 9999 );
            add_action( 'wp_ajax_pwbe_options', array( $this, 'ajax_options' ) );
            add_action( 'wp_ajax_pwbe_filter_results', array( $this, 'ajax_filter_results' ) );
            add_action( 'wp_ajax_pwbe_get_view', array( $this, 'ajax_get_view' ) );
            add_action( 'wp_ajax_pwbe_save_view', array( $this, 'ajax_save_view' ) );
            add_action( 'wp_ajax_pwbe_delete_view', array( $this, 'ajax_delete_view' ) );
            add_action( 'wp_ajax_pwbe_save_products', array( $this, 'ajax_save_products' ) );
            add_action( 'wp_ajax_pwbe_get_save_products_error', array( $this, 'ajax_get_save_products_error' ) );
        }
    }

    public static function wc_min_version( $version ) {
        return version_compare( WC()->version, $version, ">=" );
    }

    public static function plugin_uninstall() {
        if ( ! current_user_can( 'activate_plugins' ) )
            return;

        foreach( PW_Bulk_Edit::$options as $option ) {
            delete_option( $option );
        }
    }

    function index() {
        $data = get_plugin_data( __FILE__ );
        $version = $data['Version'];
        $settings_url = add_query_arg( array( 'page' => 'wc-settings', 'tab' => 'pw-bulk-edit' ), admin_url( 'admin.php' ) );
        $help_url = plugins_url( '/docs/index.html', __FILE__ );

        require( 'ui/index.php' );
    }

    function ajax_options() {
        $name = $_POST['option_name'];
        $value = $_POST['option_value'];

        if ( in_array( $name, PW_Bulk_Edit::$options ) ) {
            update_option( $name, $value );
        }
    }

    function ajax_filter_results() {
        require( 'ui/results.php' );
        wp_die();
    }

    function ajax_get_view() {
        $view_name = stripslashes( $_POST['name'] );

        update_option( 'pwbe_selected_view', $view_name );

        $views = PWBE_Views::get();
        $view = $views[ $view_name ];

        wp_send_json( $view );

        wp_die();
    }

    function ajax_save_view() {
        $option_value = get_option( 'pwbe_views' );
        $views = maybe_unserialize( $option_value );

        $clean_name = stripslashes( $_POST['name'] );

        $views[ $clean_name ] = stripslashes( $_POST['view_data'] );

        ksort( $views, SORT_NATURAL );

        update_option( 'pwbe_views', $views );
        update_option( 'pwbe_selected_view', $clean_name );

        wp_die();
    }

    function ajax_delete_view() {
        $option_value = get_option( 'pwbe_views' );
        $views = maybe_unserialize( $option_value );

        $view_name = stripslashes( $_POST['name'] );

        unset( $views[ $view_name ] );

        update_option( 'pwbe_views', $views );
        update_option( 'pwbe_selected_view', '' );

        wp_die();
    }

    function ajax_save_products() {
        register_shutdown_function( array( $this, 'save_products_exception' ) );

        check_ajax_referer( 'pw-bulk-edit-save-products', 'security' );

        require( 'includes/save-products.php' );

        if ( isset( $_POST['fields'] ) ) {
            $fields = $_POST['fields'];

            $save = new PWBE_Save_Products();
            $results = $save->save( $fields );

            require( 'ui/products-saved.php' );
        }

        wp_die();
    }

    function ajax_get_save_products_error() {
        $error_file = plugin_dir_path( __FILE__ ) . 'logs/save_products_exception.txt';

        printf( __( 'Error while saving products: %', 'pw-bulk-edit' ), file_get_contents( $error_file ) );

        wp_die();
    }

    public function save_products_exception() {
        $errfile = 'unknown file';
        $errstr  = 'shutdown';
        $errno   = E_CORE_ERROR;
        $errline = 0;

        $error = error_get_last();

        if ( $error !== NULL ) {
            $errno   = $error['type'];
            $errfile = $error['file'];
            $errline = $error['line'];
            $errstr  = $error['message'];

            if ( PW_Bulk_Edit::starts_with( plugin_dir_path( __FILE__ ), $errfile ) ) {
                $output_dir = plugin_dir_path( __FILE__ ) . 'logs';
                if ( ! file_exists( $output_dir ) ) {
                    mkdir( $output_dir, 0777, true );
                }
                file_put_contents( $output_dir . '/save_products_exception.txt', "$errstr in $errfile on line $errline" );
            }
        }
    }

    function error( $message ) {
        ?>
        <div class="error">
            <p><?php _e( $message, 'pw-bulk-edit' ); ?></p>
        </div>
        <?php
    }

    function register_admin_menu() {

        if ( get_option( 'hide_pimwick_menu', 'no' ) === 'no' ) {
            if ( empty ( $GLOBALS['admin_page_hooks']['pimwick'] ) ) {
                add_menu_page(
                    __( 'PW Bulk Edit', 'pw-bulk-edit' ),
                    __( 'Pimwick Plugins', 'pw-bulk-edit' ),
                    get_option( 'pwbe_requires_capability', PWBE_REQUIRES_CAPABILITY ),
                    'pimwick',
                    array( $this, 'index' ),
                    plugins_url( '/assets/images/pimwick-icon-120x120.png', __FILE__ ),
                    6
                );

                add_submenu_page(
                    'pimwick',
                    __( 'PW Bulk Edit', 'pw-bulk-edit' ),
                    __( 'Pimwick Plugins', 'pw-bulk-edit' ),
                    get_option( 'pwbe_requires_capability', PWBE_REQUIRES_CAPABILITY ),
                    'pimwick',
                    array( $this, 'index' )
                );

                remove_submenu_page('pimwick','pimwick');
            }

            add_submenu_page(
                'pimwick',
                __( 'PW Bulk Edit', 'pw-bulk-edit' ),
                __( 'PW Bulk Edit', 'pw-bulk-edit' ),
                get_option( 'pwbe_requires_capability', PWBE_REQUIRES_CAPABILITY ),
                'pw-bulk-edit',
                array( $this, 'index' )
            );
        }

        add_submenu_page(
            'edit.php?post_type=product',
            __( 'PW Bulk Edit', 'pw-bulk-edit' ),
            __( 'PW Bulk Edit', 'pw-bulk-edit' ),
            get_option( 'pwbe_requires_capability', PWBE_REQUIRES_CAPABILITY ),
            'wc-pw-bulk-edit',
            array( $this, 'index' )
        );
    }

    function admin_scripts( $hook ) {
        if ( !empty( $hook ) && substr( $hook, -strlen( 'pw-bulk-edit' ) ) === 'pw-bulk-edit' ) {
            wp_register_style( 'pwbe-font-awesome', plugins_url( '/assets/css/font-awesome.min.css', __FILE__ ), array(), PWBE_VERSION ); // 4.6.3
            wp_enqueue_style( 'pwbe-font-awesome' );

            wp_register_style( 'pwbe-select2', plugins_url( '/assets/css/select2.min.css', __FILE__ ), array(), PWBE_VERSION ); // 4.0.3
            wp_enqueue_style( 'pwbe-select2' );

            wp_register_style( 'pwbe-context-menu', plugins_url( '/assets/css/jquery.contextMenu.min.css', __FILE__ ), array(), PWBE_VERSION );
            wp_enqueue_style( 'pwbe-context-menu' );

            wp_enqueue_script( 'pwbe-select2', plugins_url( '/assets/js/select2.min.js', __FILE__ ), array(), PWBE_VERSION ); // 4.0.3

            wp_register_style( 'pw-bulk-edit', plugins_url( '/assets/css/style.css', __FILE__ ), array(), PWBE_VERSION );
            wp_enqueue_style( 'pw-bulk-edit' );

            wp_enqueue_script( 'pwbe-context-menu', plugins_url( '/assets/js/jquery.contextMenu.min.js', __FILE__ ), array( 'jquery-ui-position' ), PWBE_VERSION ); // 2.2.3

            wp_enqueue_script( 'pwbe-filters', plugins_url( '/assets/js/filters.js', __FILE__ ), array( 'jquery-form', 'pwbe-select2', 'pwbe-context-menu' ), PWBE_VERSION );

            $string_types = array(
                'contains'          => __( 'contains', 'pw-bulk-edit' ),
                'does not contain'  => __( 'does not contain', 'pw-bulk-edit' ),
                'is'                => __( 'is', 'pw-bulk-edit' ),
                'is not'            => __( 'is not', 'pw-bulk-edit' ),
                'begins with'       => __( 'begins with', 'pw-bulk-edit' ),
                'ends with'         => __( 'ends with', 'pw-bulk-edit' ),
            );

            $boolean_types = array(
                'is checked'        => __( 'is checked', 'pw-bulk-edit' ),
                'is not checked'    => __( 'is not checked', 'pw-bulk-edit' ),
            );

            $numeric_types = array(
                'is'                => __( 'is', 'pw-bulk-edit' ),
                'is not'            => __( 'is not', 'pw-bulk-edit' ),
                'is greater than'   => __( 'is greater than', 'pw-bulk-edit' ),
                'is less than'      => __( 'is less than', 'pw-bulk-edit' ),
                'is in the range'   => __( 'is in the range', 'pw-bulk-edit' ),
            );

            $select_types = array(
                'is any of'         => __( 'is any of', 'pw-bulk-edit' ),
                'is none of'        => __( 'is none of', 'pw-bulk-edit' ),
            );

            wp_localize_script( 'pwbe-filters', 'pwbeFilters', array(
                'stringTypes' => array_keys( $string_types ),
                'booleanTypes' => array_keys( $boolean_types ),
                'numericTypes' => array_keys( $numeric_types ),
                'selectTypes' => array_keys( $select_types ),
                'i18n' => array(
                    'stringTypes' => array_values( $string_types ),
                    'booleanTypes' => array_values( $boolean_types ),
                    'numericTypes' => array_values( $numeric_types ),
                    'selectTypes' => array_values( $select_types ),
                    'unsavedChangesPrompt' => __( 'Unsaved changes will be lost.', 'pw-bulk-edit' ),
                    'searching' => __( 'Searching', 'pw-bulk-edit' ),
                )
            ) );

            wp_enqueue_script( 'pwbe-results', plugins_url( '/assets/js/results.js', __FILE__ ), array( 'pwbe-filters' ), PWBE_VERSION );
            wp_localize_script( 'pwbe-results', 'pwbe', array(
                'i18n' => array(
                    'view_name_prompt' => __( 'Name your custom view, for example "My View"', 'pw-bulk-edit' ),
                    'overwrite_view_prompt' => __( 'A view with this name already exists. Do you want to overwrite it?', 'pw-bulk-edit' ),
                    'editAllCheckedProducts' => __( 'Edit All Checked Products', 'pw-bulk-edit' ),
                    'sortAscending' => __( 'Sort Ascending', 'pw-bulk-edit' ),
                    'sortDescending' => __( 'Sort Descending', 'pw-bulk-edit' ),
                    'hideColumn' => __( 'Hide Column', 'pw-bulk-edit' ),
                    'acceptChanges' => __( 'Accept Changes', 'pw-bulk-edit' ),
                    'cancelChanges' => __( 'Cancel Changes', 'pw-bulk-edit' ),
                    'revertToOriginal' => __( 'Revert to the original value for this field', 'pw-bulk-edit' ),
                    'select' => __( 'Select', 'pw-bulk-edit' ),
                    'saving' => __( 'Saving', 'pw-bulk-edit' ),
                    'confirmDeleteView' => __( 'Are you sure you want to delete this view?', 'pw-bulk-edit' ),
                    'discardAllChanges' => __( 'Discard all unsaved changes? This can\'t be undone.', 'pw-bulk-edit' ),
                ),
                'saveBatchSize' => PWBE_SAVE_BATCH_SIZE,
                'nonces' => array(
                    'save_products' => wp_create_nonce( 'pw-bulk-edit-save-products' ),
                ),
            ) );

            wp_enqueue_script( 'jquery-form' );
            wp_enqueue_script( 'jquery-ui-draggable' );
            wp_enqueue_script( 'jquery-ui-position' );
        }

        wp_register_style( 'pw-bulk-edit-icon', plugins_url( '/assets/css/icon-style.css', __FILE__ ), array(), PWBE_VERSION );
        wp_enqueue_style( 'pw-bulk-edit-icon' );
    }

    public static function starts_with( $needle, $haystack ) {
        $length = strlen( $needle );
        return ( substr( $haystack, 0, $length ) === $needle );
    }

    /**
     * Source: http://wordpress.stackexchange.com/questions/14652/how-to-show-a-hierarchical-terms-list
     * Recursively sort an array of taxonomy terms hierarchically. Child categories will be
     * placed under a 'children' member of their parent term.
     * @param Array   $cats     taxonomy term objects to sort
     * @param Array   $into     result array to put them in
     * @param integer $parentId the current parent ID to put them in
     */
    function sort_terms_hierarchically( array &$cats, array &$into, $parentId = 0 ) {
        foreach ( $cats as $i => $cat ) {
            if ( $cat->parent == $parentId ) {
                $into[$cat->term_id] = $cat;
                unset( $cats[$i] );
            }
        }

        foreach ( $into as $topCat ) {
            $topCat->children = array();
            $this->sort_terms_hierarchically( $cats, $topCat->children, $topCat->term_id );
        }
    }

    function hierarchical_select($categories, $level = 0, $parent = NULL, $prefix = '') {
        $output = '';

        foreach ( $categories as $category ) {
            $output .= "<option value='{$category->slug}'>$prefix {$category->name}</option>\n";

            if ( $category->parent == $parent ) {
                $level = 0;
            }

            if ( count( $category->children ) > 0 ) {
                $output .= $this->hierarchical_select( $category->children, ( $level + 1 ), $category->parent, "$prefix {$category->name} &#8594;" );
            }
        }

        return $output;
    }
}

new PW_Bulk_Edit();

endif;
