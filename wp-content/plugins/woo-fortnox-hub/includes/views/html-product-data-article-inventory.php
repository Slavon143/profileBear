<?php
/**
 * Adds fortnox specific fields.
 *
 * @package WooCommerce\Admin
 */

if (!defined('ABSPATH')) {
    exit;
}

woocommerce_wp_text_input(
    array(
        'id' => "fortnox_manufacturer",
        'value' => WCFH_Util::get_metadata($product_object, 'Manufacturer'),
        'label' => __('Manufacturer', 'woo-fortnox-integration'),
        'desc_tip' => true,
        'description' => __('Product manufacturer from Fortnox.', 'woo-fortnox-integration'),
    )
);

woocommerce_wp_text_input(
    array(
        'id' => "fortnox_manufacturer_article_number",
        'value' => WCFH_Util::get_metadata($product_object, 'ManufacturerArticleNumber'),
        'label' => __('Manufacturer article', 'woo-fortnox-integration'),
        'desc_tip' => true,
        'description' => __('Product Manufacturer article from Fortnox.', 'woo-fortnox-integration'),
    )
);

woocommerce_wp_text_input(
    array(
        'id' => "fortnox_stock_place",
        'value' => WCFH_Util::get_metadata($product_object, 'StockPlace'),
        'label' => __('Stock place', 'woo-fortnox-integration'),
        'desc_tip' => true,
        'description' => __('Product stock place from Fortnox.', 'woo-fortnox-integration'),
    )
);

woocommerce_wp_text_input(
    array(
        'id' => "fortnox_unit",
        'value' => WCFH_Util::get_metadata($product_object, 'Unit'),
        'label' => __('Unit', 'woo-fortnox-integration'),
        'desc_tip' => true,
        'description' => __('Product unit from Fortnox.', 'woo-fortnox-integration'),
    )
);

if ('_fortnox_ean' == get_option('fortnox_metadata_mapping_ean')) {
    woocommerce_wp_text_input(
        array(
            'id' => "fortnox_barcode",
            'value' => WCFH_Util::get_metadata($product_object, 'EAN'),
            'label' => __('Barcode', 'woo-fortnox-integration'),
            'desc_tip' => true,
            'description' => __('Product barcode from Fortnox.', 'woo-fortnox-integration'),
        )
    );
}
