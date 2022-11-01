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
        'id' => 'fortnox_purchase_price',
        'value' => WCFH_Util::get_metadata($product_object, 'PurchasePrice'),
        'label' => __('Purchase price', 'woo-fortnox-integration') . ' (' . get_woocommerce_currency_symbol() . ')',
        'class' => 'hide_if_variable hide_if_external hide_if_grouped',
        'desc_tip' => true,
        'description' => __('Purchase price from Fortnox.', 'woo-fortnox-integration'),
        'data_type' => 'price',
    )
);

if ('yes' == get_option('fortnox_enable_housework')) {
    woocommerce_wp_select(
        array(
            'id' => '_fortnox_tax_reduction_type',
            'value' => $product_object->get_meta('fortnox_tax_reduction_type', true),
            'label' => __('Tax reduction type', 'woo-fortnox-integration'),
            'options' => WCFH_Util::valid_housework_types(),
            'desc_tip' => 'true',
            'description' => __('Select what type of tax reduction the product should be tagged with on the invoice. Please note that only one major type (Rot, Rut or Green) can be used on an invoice. Invoice creation fails if two different are selected.', 'woo-fortnox-integration'),
        )
    );

    woocommerce_wp_text_input(
        array(
            'id' => '_fortnox_tax_reduction_hours',
            'value' => $product_object->get_meta('fortnox_tax_reduction_hours', true),
            'label' => __('Housework hours', 'woo-fortnox-integration'),
            'desc_tip' => true,
            'description' => __('To be used if the purchased product itself does not contain the correct hours.', 'woo-fortnox-integration'),
            'type' => 'number',
            'custom_attributes' => array(
                'step' => 1,
                'min' => 0,
            ),
            'data_type' => 'stock',
        )
    );

    woocommerce_wp_text_input(
        array(
            'id' => '_fortnox_tax_reduction_price',
            'value' => $product_object->get_meta('fortnox_tax_reduction_price', true),
            'label' => __('Housework price', 'woo-fortnox-integration') . ' (' . get_woocommerce_currency_symbol() . ')',
            'type' => 'price',
        )
    );
}
