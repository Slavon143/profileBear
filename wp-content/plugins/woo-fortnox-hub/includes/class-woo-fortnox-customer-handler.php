<?php

/**
 * This class contains function to handle the customer data interaction with Fortnox
 *
 * @package   Woo_Fortnox_Hub
 * @author    BjornTech <hello@bjorntech.com>
 * @license   GPL-3.0
 * @link      http://bjorntech.com
 * @copyright 2017-2021 BjornTech
 */

defined('ABSPATH') || exit;

if (!class_exists('Woo_Fortnox_Hub_Customer_Handler', false)) {
    class Woo_Fortnox_Hub_Customer_Handler
    {
        public function __construct()
        {
            add_action('woo_fortnox_hub_create_customer_invoice', array($this, 'create_customer'));
            add_action('woo_fortnox_hub_create_customer_order', array($this, 'create_customer'));
            add_action('woo_fortnox_hub_create_customer', array($this, 'create_customer'));
            add_filter('fortnox_get_sections', array($this, 'add_settings_section'), 100);
            add_filter('woocommerce_get_settings_fortnox_hub', array($this, 'get_settings'), 100, 2);
            add_filter('woocommerce_save_settings_fortnox_hub_customers', array($this, 'save_settings_section'));
            add_filter('fortnox_after_get_details', array($this, 'add_delivery_details_to_document'), 10, 2);
        }

        /**
         * Add setting section for customer settings
         *
         * @param array $sections The incoming array of sections for this plugin settings
         *
         * @since 1.0.0
         *
         * @return array The outgoing array of sections for this plugin settings
         */
        public function add_settings_section($sections)
        {
            if (!array_key_exists('customers', $sections)) {
                $sections = array_merge($sections, array('customers' => __('Customers', 'woo-fortnox-hub')));
            }
            return $sections;
        }

        /**
         * Save setting section for customer settings
         *
         * @param bool $true
         *
         * @since 1.0.0
         *
         * @return bool The result of the saving of this section.
         */
        public function save_settings_section($true)
        {
            return $true;
        }

        /**
         * Get the settings for customers
         *
         * @param array $settings The incoming settings array
         * @param string $current_section The current setting used
         *
         * @since 1.0.0
         *
         * @return array The outgoing settings array
         */
        public function get_settings($settings, $current_section)
        {
            if ('customers' === $current_section) {
                $settings[] = [
                    'title' => __('Customer options', 'woo-fortnox-hub'),
                    'type' => 'title',
                    'desc' => '',
                    'id' => 'fortnox_customers_options',
                ];

                $settings[] = [
                    'title' => __('Do not update billing', 'woo-fortnox-hub'),
                    'type' => 'checkbox',
                    'default' => '',
                    'desc' => __('Check if you do not want customer billing details to be updated on existing Fortnox customers. The billing details will be updated only when the customer does not exist and is created by the plugin.', 'woo-fortnox-hub'),
                    'id' => 'fortnox_do_not_update_customer_billing',
                ];

                $settings[] = [
                    'title' => __('Do not update delivery', 'woo-fortnox-hub'),
                    'type' => 'checkbox',
                    'default' => '',
                    'desc' => __('Check if you do not want customer delivery details to be updated on existing Fortnox customers. The delivery details will be updated only when the customer does not exist and is created by the plugin.', 'woo-fortnox-hub'),
                    'id' => 'fortnox_do_not_update_customer_delivery',
                ];

                $settings[] = [
                    'title' => __('Delivery on document only', 'woo-fortnox-hub'),
                    'type' => 'checkbox',
                    'default' => '',
                    'desc' => __('Check if you do want Fortnox customer delivery details to update the order/invoice only. No delivery details will be updated on the customer card.', 'woo-fortnox-hub'),
                    'id' => 'fortnox_delivery_details_on_document_only',
                ];

                $settings[] = [
                    'title' => __('Default delivery type', 'woo-fortnox-hub'),
                    'type' => 'select',
                    'desc' => __('Set the preferred default delivery option for new customers created by the plugin', 'woo-fortnox-hub'),
                    'default' => '',
                    'options' => array(
                        '' => __('Use Fortnox default', 'woo-fortnox-hub'),
                        'PRINT' => __('Use print', 'woo-fortnox-hub'),
                        'EMAIL' => __('Use e-mail.', 'woo-fortnox-hub'),
                        'PRINTSERVICE' => __('Use external printservice.', 'woo-fortnox-hub'),
                    ),
                    'id' => 'wfh_customer_default_delivery_types',
                ];

                $settings[] = [
                    'title' => __('Do not update invoice email', 'woo-fortnox-hub'),
                    'type' => 'checkbox',
                    'default' => '',
                    'desc' => __('Check if you do not want to update an existing customer card invoice email with the email from the WooCommerce order.', 'woo-fortnox-hub'),
                    'id' => 'fortnox_do_not_update_customercard_invoice_email',
                ];

                $settings[] = [
                    'title' => __('Do not update order email', 'woo-fortnox-hub'),
                    'type' => 'checkbox',
                    'default' => '',
                    'desc' => __('Check if you do not want to update an existing customer card order email with the email from the WooCommerce order.', 'woo-fortnox-hub'),
                    'id' => 'fortnox_do_not_update_customercard_order_email',
                ];

                $settings[] = [
                    'title' => __('Identify customers by', 'woo-fortnox-hub'),
                    'type' => 'select',
                    'default' => '',
                    'desc' => __('The plugin needs to find out if the customer already exists in Fortnox. The first customer with a matching email will be selected. The mapping can also be done by organisation number. In this a metadatafield named "_organisation_number" has to be present on the order', 'woo-fortnox-hub'),
                    'options' => array(
                        '' => __('Customer email', 'woo-fortnox-hub'),
                        'organisation_number' => __('Organisation number (_organisation_number)', 'woo-fortnox-hub'),
                        '_meta' => __('Use a configurable metadata field', 'woo-fortnox-hub'),
                    ),
                    'id' => 'fortnox_identify_customers_by',
                ];

                if ('_meta' == get_option('fortnox_identify_customers_by')) {
                    $settings[] = [
                        'title' => __('Organisation number meta', 'woo-fortnox-hub'),
                        'type' => 'text',
                        'desc' => __('Enter the name of the product metadata field that should be used.', 'woo-fortnox-hub'),
                        'default' => '',
                        'id' => 'fortnox_organisation_number_meta',
                    ];
                }

                if (get_option('fortnox_identify_customers_by')) {
                    $settings[] = [
                        'title' => __('Require organisation number', 'woo-fortnox-hub'),
                        'type' => 'checkbox',
                        'desc' => __('Stop the order processing if the organisation number can not be found.', 'woo-fortnox-hub'),
                        'default' => '',
                        'id' => 'fortnox_organisation_number_only',
                    ];
                }

                if (!(empty($payment_gateways = WCFH_Util::get_available_payment_gateways()))) {
                    foreach ($payment_gateways as $key => $payment_gateway) {
                        $settings[] = [
                            'title' => sprintf(__('Send invoice for %s', 'woo-fortnox-hub'), $payment_gateway->get_title()),
                            'default' => get_option('fortnox_send_customer_email_invoice'),
                            'type' => 'checkbox',
                            'desc' => sprintf(__('Check if you do want the invoice in Fortnox to be emailed to the customer when the WooCommerce order using %s as payment method is set to completed.', 'woo-fortnox-hub'), $payment_gateway->get_title()),
                            'id' => 'fortnox_send_customer_email_invoice_' . $key,
                        ];
                    }

                    $settings[] = [
                        'title' => __('Reply-adress', 'woo-fortnox-hub'),
                        'type' => 'email',
                        'default' => '',
                        'id' => 'fornox_invoice_email_from',
                    ];

                    $settings[] = [
                        'title' => __('E-mail subject', 'woo-fortnox-hub'),
                        'type' => 'text',
                        'desc' => __('Subject text on the Fortnox mail containing the invoice. The variable {no} = document number. The variable {name} =  customer name', 'woo-fortnox-hub'),
                        'id' => 'fornox_invoice_email_subject',
                    ];

                    $settings[] = [
                        'title' => __('E-mail body', 'woo-fortnox-hub'),
                        'desc' => __('Body text on the Fortnox mail containing the invoice.', 'woo-fortnox-hub'),
                        'id' => 'fornox_invoice_email_body',
                        'css' => 'width:100%; height: 65px;',
                        'type' => 'textarea',
                    ];
                }

                $settings[] = [
                    'type' => 'sectionend',
                    'id' => 'fortnox_customers_options',
                ];
            }

            return $settings;
        }

        /**
         * Get the VAT-type for a customer on an order
         * The type can be "SEVAT", "SEREVERSEDVAT", "EUREVERSEDVAT", "EUVAT", or "EXPORT".
         *
         * @param WC_Order $order The order containing the customer
         *
         * @since 1.0.0
         *
         * @return string the VAT-type
         */
        private function get_vat_type($order)
        {
            $country = WCFN_Accounts::get_billing_country($order);
            if ('SE' == $country) {
                $vat_type = 'SEVAT';
            } elseif (WCFH_Util::is_european_country($country)) {
                if (WCFH_Util::eu_number_is_validated($order)) {
                    $vat_type = 'EUREVERSEDVAT';
                } else {
                    $vat_type = 'EUVAT';
                }
            } else {
                $vat_type = 'EXPORT';
            }
            return $vat_type;
        }

        public function billing_details($order, $order_id, $email, $current_customer, $organisation_number)
        {
            $data = array();
            if (!$current_customer || ($current_customer && 'yes' != get_option('fortnox_do_not_update_customer_billing'))) {
                $customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();

                if ($company = $order->get_billing_company()) {
                    $billing_name = $company;
                    $customer_type = 'COMPANY';
                } else {
                    $billing_name = $customer_name;
                    $customer_type = 'PRIVATE';
                }

                $default_delivery_type = ($current_customer && isset($current_customer['DefaultDeliveryTypes'])) ? $current_customer['DefaultDeliveryTypes'] : get_option('wfh_customer_default_delivery_types'); //PRINT EMAIL or PRINTSERVICE

                $data = array_merge($data, array(
                    "Name" => WCFH_Util::clean_fortnox_text($billing_name, 1024),
                    "Address1" => WCFH_Util::clean_fortnox_text($order->get_billing_address_1(), 1024, 'API_BLANK'),
                    "Address2" => WCFH_Util::clean_fortnox_text($order->get_billing_address_2(), 1024, 'API_BLANK'),
                    "City" => WCFH_Util::clean_fortnox_text($order->get_billing_city(), 1024),
                    "ZipCode" => WCFH_Util::clean_fortnox_text($order->get_billing_postcode(), 10),
                    "CountryCode" => WCFN_Accounts::get_billing_country($order),
                    "Phone1" => WCFH_Util::clean_fortnox_text($order->get_billing_phone(), 1024),
                    "Email" => WCFH_Util::clean_fortnox_text($email, 1024),
                    "Currency" => WCFH_Util::clean_fortnox_text($order->get_currency(), 3),
                    "Type" => $customer_type,
                    "ShowPriceVATIncluded" => $customer_type == 'PRIVATE',
                    "OrganisationNumber" => WCFH_Util::clean_fortnox_text($organisation_number, 30),
                    "YourReference" => WCFH_Util::clean_fortnox_text($customer_name, 50),
                    "VATType" => $this->get_vat_type($order),
                    "VATNumber" => $this->get_vat_number($order_id, $current_customer),
                    "DefaultDeliveryTypes" => array(
                        "Invoice" => $default_delivery_type,
                        "Order" => $default_delivery_type,
                        "Offer" => $default_delivery_type,
                    ),
                ));

                if (!($current_customer && wc_string_to_bool(get_option('fortnox_do_not_update_customercard_invoice_email')))) {
                    $data["EmailInvoice"] = apply_filters('fortnox_customercard_invoice_email', WCFH_Util::clean_fortnox_text($email, 1024));
                }

                if (!($current_customer && wc_string_to_bool(get_option('fortnox_do_not_update_customercard_order_email')))) {
                    $data["EmailOrder"] = apply_filters('fortnox_customercard_order_email', WCFH_Util::clean_fortnox_text($email, 1024));
                }

            }

            return $data;
        }

        /**
         * Add delivery details to a Fortnox document
         *
         * @param array $document An array to update a Fortnox document
         * @param WC_Order $order The WooCommerce order to use as basis when updating the Fortnox document
         *
         * @since 4.4.0
         *
         * @return array An array to update a Fortnox document
         */
        public function add_delivery_details_to_document($document, $order)
        {
            if (wc_string_to_bool(get_option('fortnox_delivery_details_on_document_only'))) {
                return array_merge($document, $this->delivery_details($order, false));
            }

            return $document;
        }

        /**
         * Add billing details to a Fortnox document
         *
         * @param array $document An array to update a Fortnox document
         * @param WC_Order $order The WooCommerce order to use as basis when updating the Fortnox document
         *
         * @since 4.4.0
         *
         * @return array An array to update a Fortnox document
         */
        public function add_billing_details_to_document($document, $order)
        {
            if (wc_string_to_bool(get_option('fortnox_billing_details_on_document_only'))) {
                return array_merge($document, $this->billing_details($order, false));
            }

            return $document;
        }

        public function delivery_details($order, $country_as_code = true)
        {
            $data = array();

            if ($order->get_formatted_shipping_address()) {
                $shipping_person = $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name();

                if ($shipping_company = $order->get_shipping_company()) {
                    $shipping_to = $shipping_company . ', ' . __('Att:', 'woo-fortnox-hub') . ' ' . $shipping_person;
                } else {
                    $shipping_to = $shipping_person;
                }

                $data = array(
                    "DeliveryName" => WCFH_Util::clean_fortnox_text($shipping_to, 1024),
                    "DeliveryAddress1" => WCFH_Util::clean_fortnox_text($order->get_shipping_address_1(), 1024, 'API_BLANK'),
                    "DeliveryAddress2" => WCFH_Util::clean_fortnox_text($order->get_shipping_address_2(), 1024, 'API_BLANK'),
                    "DeliveryCity" => WCFH_Util::clean_fortnox_text($order->get_shipping_city(), 1024),
                    "DeliveryZipCode" => WCFH_Util::clean_fortnox_text($order->get_shipping_postcode(), 10),

                );

                if ($country_as_code) {
                    $data["DeliveryCountryCode"] = $order->get_shipping_country();
                } else {
                    $data["DeliveryCountry"] = Fortnox_Countries::get_country($order->get_shipping_country());
                }
            }

            return $data;
        }

        /**
         * Get organisation number
         *
         * @since 4.6.0
         * @param string|int $order_id
         *
         * The option 'fortnox_identify_customers_by' can have three values
         *
         * '' : No organisation number, the email adress will be used ad identifier
         * 'organisation_number' : Use the metadata '_organisation_number' from the order
         * '_meta' : Use metadata in the option 'fortnox_organisation_number_meta' as the metadata to get the organisation number
         *
         * The filter 'fortnox_organisation_number' can be used to alter the result.
         *
         * @return string|bool Returns the organisation number or blank/false if not found
         */

        public function get_organisation_number($order_id)
        {

            $organisation_number = false;

            $identify_customers_by = get_option('fortnox_identify_customers_by');

            if ($identify_customers_by) {
                if ('organisation_number' == $identify_customers_by) {
                    $organisation_number = get_post_meta($order_id, '_organisation_number', true);
                } elseif ($organisation_number_meta = get_option('fortnox_organisation_number_meta')) {
                    $organisation_number = get_post_meta($order_id, $organisation_number_meta, true);
                }
                if (!$organisation_number && wc_string_to_bool(get_option('fortnox_organisation_number_only'))) {
                    throw new Fortnox_Exception(__('Organisation number not found', 'woo-fortnox-hub'));
                }
            }

            return apply_filters('fortnox_organisation_number', $organisation_number, $order_id);

        }

        /**
         * Get VAT number
         *
         * @since 5.1.5
         * @param string $vat_number
         * @param array $customer
         *
         * Clean the organisation number having an existing customer using the common used format xxxxxxnnnn with the required format xxxxxx-nnnn.
         * This must be done before saving the customer in order to prevent an error when saving the customer.
         * https://developer.fortnox.se/blog/new-validation-for-vat-number/
         *
         * @return string Returns the organisation number
         */

        public function get_vat_number($order_id, $customer)
        {

            if ($vat_number = get_post_meta($order_id, '_billing_vat_number', true)) {
                return $vat_number;
            }

            if ($vat_number = get_post_meta($order_id, '_vat_number', true)) {
                return $vat_number;
            }

            if ($vat_number = get_post_meta($order_id, 'vat_number', true)) {
                return $vat_number;
            }

            $clean_vat_number = wc_string_to_bool(get_option('fortnox_clean_vat_number'));

            if ($clean_vat_number && is_array($customer)) {
                if (array_key_exists("CustomerNumber", $customer)) {
                    $customer = WC_FH()->fortnox->getCustomer($customer["CustomerNumber"]);
                }
                if ('SE' === $customer['CountryCode'] && 10 === strlen($customer["VATNumber"]) || 11 === strlen($customer["VATNumber"])) {
                    return 'SE' . str_replace('-', '', $customer["VATNumber"]) . '01';
                }
            }

            return '';

        }

        public function create_customer($order_id)
        {
            if (!apply_filters('fortnox_hub_filter_woocommerce_order', true, 'create_customer', $order_id)) {
                return;
            }

            $order = wc_get_order($order_id);

            if (!WCFH_Util::is_izettle($order)) {

                $organisation_number = $this->get_organisation_number($order_id);
                $email = apply_filters('fortnox_customer_email', $order->get_billing_email(), $order_id);

                if ($customer = apply_filters('fortnox_get_customer', false, $order_id)) {
                    WC_FH()->logger->add(sprintf('create_customer (%s): Found customer by organisation number "%s" in Fortnox', $order_id, $organisation_number));
                } elseif ($organisation_number) {
                    WC_FH()->logger->add(sprintf('create_customer (%s): Searching for customer by organisation number "%s" in Fortnox', $order_id, $organisation_number));
                    $customer = WC_FH()->fortnox->get_first_customer_by_organisation_number(trim($organisation_number));
                } elseif ($email) {
                    WC_FH()->logger->add(sprintf('create_customer (%s): Searching for customer %s by email in Fortnox', $order_id, $email));
                    $customer = WC_FH()->fortnox->get_first_customer_by_email($email);
                } else {
                    throw new Fortnox_Exception(__('No customer identifier found', 'woo-fortnox-hub'));
                }

                $customer_data = $this->billing_details($order, $order_id, $email, $customer, $organisation_number);

                if (!wc_string_to_bool(get_option('fortnox_delivery_details_on_document_only'))) {
                    if ((!$customer || ($customer && !wc_string_to_bool(get_option('fortnox_do_not_update_customer_delivery'))))) {
                        $customer_data = array_merge($this->delivery_details($order), $customer_data);
                    }
                }

                $customer_data = WCFH_Util::remove_blanks(apply_filters('fortnox_customer_data_before_processing', $customer_data, $order_id, $customer));

                if (false === $customer) {
                    $customer = WC_FH()->fortnox->addCustomer($customer_data);
                    WC_FH()->logger->add(sprintf('create_customer (%s): Created Fortnox customer %s', $order_id, $customer['CustomerNumber']));
                } elseif ($customer_data) {
                    if (isset($customer_data['Comments'])) {
                        update_post_meta($order_id, '_fortnox_customer_comments', $customer_data['Comments']);
                    }
                    WC_FH()->logger->add(sprintf('create_customer (%s): Fortnox customer %s found. Updating customer details', $order_id, $customer['CustomerNumber']));
                    $customer = WC_FH()->fortnox->updateCustomer($customer['CustomerNumber'], $customer_data);
                }

                WC_FH()->logger->add(json_encode($customer_data, JSON_INVALID_UTF8_IGNORE));
                $customer_number = $customer['CustomerNumber'];
            } else {
                $customer_number = get_option('fortnox_izettle_customer_number');
            }

            WCFH_Util::set_fortnox_customer_number($order_id, $customer_number);
        }
    }

    new Woo_Fortnox_Hub_Customer_Handler();
}
