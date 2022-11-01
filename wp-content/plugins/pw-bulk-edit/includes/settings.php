<?php

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'PWBE_Settings' ) ) :

class PWBE_Settings {

    public $settings;

    function __construct() {
        $this->settings = array(
            array(
                'title' => __( 'PW Bulk Edit', 'pw-bulk-edit' ),
                'type'  => 'title',
                'desc'  => '',
                'id'    => 'pw_bulk_edit_options',
            ),
            array(
                'title'    => __( 'Hide Pimwick Plugins Menu', 'pw-bulk-edit' ),
                // translators: %s is PW Bulk Edit.
                'desc'     => sprintf( __( 'Do not show the Pimwick Plugins menu on the left. You can still access the dashboard via Products > %s', 'pw-bulk-edit' ), __( 'PW Bulk Edit', 'pw-bulk-edit' ) ),
                'id'       => 'hide_pimwick_menu',
                'default'  => 'no',
                'type'     => 'checkbox',
                'desc_tip' => false,
            ),
            array(
                'type'  => 'sectionend',
                'id'    => 'pw_bulk_edit_options',
            ),
        );

        add_action( 'woocommerce_get_settings_pages', array( $this, 'woocommerce_get_settings_pages' ), 11 );
    }

    function woocommerce_get_settings_pages( $settings ) {
        // Fix for a conflict with the "Hide Price Until Login" plugin by CedCommerce.
        if ( !is_array( $settings ) ) {
            $settings = array();
        }

        $settings[] = include( 'class-wc-settings-pw-bulk-edit.php' );

        return $settings;
    }

}

global $pwbe_settings;
$pwbe_settings = new PWBE_Settings();

endif;
