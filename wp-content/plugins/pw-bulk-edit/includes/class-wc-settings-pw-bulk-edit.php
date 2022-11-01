<?php

defined( 'ABSPATH' ) or exit;

if ( class_exists( 'WC_Settings_PW_Bulk_Edit', false ) ) {
    return new WC_Settings_PW_Bulk_Edit();
}

class WC_Settings_PW_Bulk_Edit extends WC_Settings_Page {
    public function __construct() {
        $this->id    = 'pw-bulk-edit';
        $this->label = __( 'PW Bulk Edit', 'pw-woocommerce-coupons-plus' );

        parent::__construct();
    }

    public function get_sections() {
        $sections = array(
            '' => __( 'General', 'pw-bulk-edit' ),
        );
        return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
    }

    public function get_settings( $current_section = '' ) {
        global $pwbe_settings;

        return apply_filters( 'woocommerce_get_settings_' . $this->id, $pwbe_settings->settings, $current_section );
    }
}

return new WC_Settings_PW_Bulk_Edit();
