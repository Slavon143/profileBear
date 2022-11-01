<?php

if ( !function_exists( 'wpsett_fs' ) ) {
    // Create a helper function for easy SDK access.
    function wpsett_fs()
    {
        global  $wpsett_fs ;
        
        if ( !isset( $wpsett_fs ) ) {
            if ( !defined( 'WP_FS__PRODUCT_3165_MULTISITE' ) ) {
                define( 'WP_FS__PRODUCT_3165_MULTISITE', true );
            }
            $wpsett_fs = fs_dynamic_init( array(
                'id'             => '3165',
                'slug'           => 'bulk-edit-categories-tags',
                'type'           => 'plugin',
                'public_key'     => 'pk_a2e855c533dd206fd78b8a0d178e1',
                'is_premium'     => false,
                'premium_suffix' => 'Pro',
                'has_addons'     => false,
                'has_paid_plans' => true,
                'menu'           => array(
                'slug'       => 'wpsett_welcome_page',
                'first-path' => 'admin.php?page=wpsett_welcome_page',
                'support'    => false,
            ),
                'is_live'        => true,
            ) );
        }
        
        return $wpsett_fs;
    }
    
    // Init Freemius.
    wpsett_fs();
    // Signal that SDK was initiated.
    do_action( 'wpsett_fs_loaded' );
}
