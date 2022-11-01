<?php

/**
*
* PHP File that handles Settings management
*
*/

class B2bking_Settings {

	public function register_all_settings() {

		// Set plugin status (Disabled, B2B & B2C, or B2B)
		register_setting('b2bking', 'b2bking_plugin_status_setting');

		// Request a Custom Quote Button
		register_setting('b2bking', 'b2bking_quote_button_cart_setting');

		// Current Tab Setting - Misc setting, hidden, only saves the last opened menu tab
		register_setting( 'b2bking', 'b2bking_current_tab_setting');
		add_settings_field('b2bking_current_tab_setting', '', array($this, 'b2bking_current_tab_setting_content'), 'b2bking', 'b2bking_hiddensettings');


		/* WCFM ADDON */
	    add_settings_section('b2bking_wcfmsettings_section', '',	'',	'b2bking');


		// Show dynamic rules to vendors
		register_setting( 'b2bking', 'b2bking_show_dynamic_rules_vendors_setting_wcfm');
		add_settings_field('b2bking_show_dynamic_rules_vendors_setting_wcfm', esc_html__('Show dynamic rules to vendors', 'b2bkingwcfm'), array($this, 'b2bking_show_dynamic_rules_vendors_setting_wcfm_content'), 'b2bking', 'b2bking_wcfmsettings_section');

	    // Show visibility to vendors
	    register_setting('b2bking', 'b2bking_show_visibility_vendors_setting_wcfm');
	    add_settings_field('b2bking_show_visibility_vendors_setting_wcfm', esc_html__('Allow vendors to set product visibility', 'b2bkingwcfm'), array($this,'b2bking_show_visibility_vendors_setting_wcfm_content'), 'b2bking', 'b2bking_wcfmsettings_section');

		/* MarketKing ADDON */
	    add_settings_section('b2bking_marketkingsettings_section', '',	'',	'b2bking');

		// Show dynamic rules to vendors
		register_setting( 'b2bking', 'b2bking_show_dynamic_rules_vendors_setting_marketking');
		add_settings_field('b2bking_show_dynamic_rules_vendors_setting_marketking', esc_html__('Show dynamic rules to vendors', 'b2bkingmarketking'), array($this, 'b2bking_show_dynamic_rules_vendors_setting_marketking_content'), 'b2bking', 'b2bking_marketkingsettings_section');

	    // Show visibility to vendors
	    register_setting('b2bking', 'b2bking_show_visibility_vendors_setting_marketking');
	    add_settings_field('b2bking_show_visibility_vendors_setting_marketking', esc_html__('Allow vendors to set product visibility', 'b2bkingmarketking'), array($this,'b2bking_show_visibility_vendors_setting_marketking_content'), 'b2bking', 'b2bking_marketkingsettings_section');


		/* Access restriction */

		// Set guest access restriction (none, hide prices, hide website, replace with request quote)
		register_setting('b2bking', 'b2bking_guest_access_restriction_setting');

		add_settings_section('b2bking_access_restriction_settings_section', '',	'',	'b2bking');
		add_settings_section('b2bking_access_restriction_settings_force_section', '',	'',	'b2bking');


		// All products visible to all users
		register_setting('b2bking', 'b2bking_all_products_visible_all_users_setting');
		add_settings_field('b2bking_all_products_visible_all_users_setting', esc_html__('All Products Visible', 'b2bking'), array($this,'b2bking_all_products_visible_all_users_setting_content'), 'b2bking', 'b2bking_access_restriction_settings_section');

		register_setting('b2bking', 'b2bking_guest_access_restriction_setting_website_redirect');
		add_settings_field('b2bking_guest_access_restriction_setting_website_redirect', esc_html__('Restrict all pages', 'b2bking'), array($this,'b2bking_guest_access_restriction_setting_website_redirect_content'), 'b2bking', 'b2bking_access_restriction_settings_force_section');
		

		add_settings_section('b2bking_access_restriction_category_settings_section', '',	'',	'b2bking');
		// Enable rules for non b2b users
		register_setting('b2bking', 'b2bking_hidden_has_priority_setting');
		add_settings_field('b2bking_hidden_has_priority_setting', esc_html__('Enable Hidden Has Priority', 'b2bking'), array($this,'b2bking_hidden_has_priority_setting_content'), 'b2bking', 'b2bking_access_restriction_category_settings_section');		

		/* Registration Settings */
		add_settings_section('b2bking_registration_settings_section', '',	'',	'b2bking');
		add_settings_section('b2bking_registration_settings_section_advanced', '',	'',	'b2bking');

		// Registration Role Dropdown enable (enabled by default)
		register_setting('b2bking', 'b2bking_registration_roles_dropdown_setting');
		add_settings_field('b2bking_registration_roles_dropdown_setting', esc_html__('Enable dropdown & fields', 'b2bking'), array($this,'b2bking_registration_roles_dropdown_setting_content'), 'b2bking', 'b2bking_registration_settings_section');
		
		// Require approval for all users' registration
		register_setting('b2bking', 'b2bking_approval_required_all_users_setting');
		add_settings_field('b2bking_approval_required_all_users_setting', esc_html__('Manual approval for all', 'b2bking'), array($this,'b2bking_approval_required_all_users_setting_content'), 'b2bking', 'b2bking_registration_settings_section_advanced');

		// Enable custom registration in checkout 
		register_setting('b2bking', 'b2bking_registration_at_checkout_setting');
		add_settings_field('b2bking_registration_at_checkout_setting', esc_html__('Registration at checkout', 'b2bking'), array($this,'b2bking_registration_at_checkout_setting_content'), 'b2bking', 'b2bking_registration_settings_section_advanced');


		// Separate my account page for b2b users
		register_setting('b2bking', 'b2bking_registration_separate_my_account_page_setting');
		add_settings_field('b2bking_registration_separate_my_account_page_setting', esc_html__('Separate My Account Page for B2B Users', 'b2bking'), array($this,'b2bking_registration_separate_my_account_page_setting_content'), 'b2bking', 'b2bking_registration_settings_section_advanced');


		// Enable Validate VAT button at checkout
		register_setting('b2bking', 'b2bking_validate_vat_button_checkout_setting');
		add_settings_field('b2bking_validate_vat_button_checkout_setting', esc_html__('Validate VAT button at checkout', 'b2bking'), array($this,'b2bking_validate_vat_button_checkout_setting_content'), 'b2bking', 'b2bking_othersettings_vat_section');


		/* Offers Settings */
		add_settings_section('b2bking_offers_settings_section', '',	'',	'b2bking');
		// Show product selector in Offers
		register_setting('b2bking', 'b2bking_offers_product_selector_setting');
		add_settings_field('b2bking_offers_product_selector_setting', esc_html__('Show product selector in offers', 'b2bking'), array($this,'b2bking_offers_product_selector_setting_content'), 'b2bking', 'b2bking_offers_settings_section');
		// Show product selector in Offers
		register_setting('b2bking', 'b2bking_offers_product_image_setting');
		add_settings_field('b2bking_offers_product_image_setting', esc_html__('Show product image in offers frontend', 'b2bking'), array($this,'b2bking_offers_product_image_setting_content'), 'b2bking', 'b2bking_offers_settings_section');
		// Logo Upload
		register_setting( 'b2bking', 'b2bking_offers_logo_setting');
		add_settings_field('b2bking_offers_logo_setting', esc_html__('Offers PDF Logo','b2bking'), array($this,'b2bking_offers_logo_setting_content'), 'b2bking', 'b2bking_offers_settings_section');		
		// Offer IMG
		register_setting( 'b2bking', 'b2bking_offers_image_setting');
		add_settings_field('b2bking_offers_image_setting', esc_html__('Offers Image','b2bking'), array($this,'b2bking_offers_image_setting_content'), 'b2bking', 'b2bking_offers_settings_section');

		/* Enable Features */

		add_settings_section('b2bking_enable_features_settings_section', '',	'',	'b2bking');

		// Enable conversations
		register_setting('b2bking', 'b2bking_enable_conversations_setting');
		add_settings_field('b2bking_enable_conversations_setting', esc_html__('Enable conversations & quote requests', 'b2bking'), array($this,'b2bking_enable_conversations_setting_content'), 'b2bking', 'b2bking_enable_features_settings_section');

		// Enable offers
		register_setting('b2bking', 'b2bking_enable_offers_setting');
		add_settings_field('b2bking_enable_offers_setting', esc_html__('Enable offers', 'b2bking'), array($this,'b2bking_enable_offers_setting_content'), 'b2bking', 'b2bking_enable_features_settings_section');

		// Enable purchase lists
		register_setting('b2bking', 'b2bking_enable_purchase_lists_setting');
		add_settings_field('b2bking_enable_purchase_lists_setting', esc_html__('Enable purchase lists', 'b2bking'), array($this,'b2bking_enable_purchase_lists_setting_content'), 'b2bking', 'b2bking_enable_features_settings_section');

		// Enable bulk order form
		register_setting('b2bking', 'b2bking_enable_bulk_order_form_setting');
		add_settings_field('b2bking_enable_bulk_order_form_setting', esc_html__('Enable bulk order form', 'b2bking'), array($this,'b2bking_enable_bulk_order_form_setting_content'), 'b2bking', 'b2bking_enable_features_settings_section');

		// Enable subaccounts
		register_setting('b2bking', 'b2bking_enable_subaccounts_setting');
		add_settings_field('b2bking_enable_subaccounts_setting', esc_html__('Enable subaccounts', 'b2bking'), array($this,'b2bking_enable_subaccounts_setting_content'), 'b2bking', 'b2bking_enable_features_settings_section');

		/* Language Settings */

		add_settings_section('b2bking_languagesettings_text_section', '',	'',	'b2bking');

		// Hide prices to guests text
		register_setting('b2bking', 'b2bking_hide_prices_guests_text_setting');
		add_settings_field('b2bking_hide_prices_guests_text_setting', esc_html__('Hide prices text', 'b2bking'), array($this,'b2bking_hide_prices_guests_text_setting_content'), 'b2bking', 'b2bking_languagesettings_text_section');

		// Hide b2b site entirely text
		register_setting('b2bking', 'b2bking_hide_b2b_site_text_setting');
		add_settings_field('b2bking_hide_b2b_site_text_setting', esc_html__('Hide shop & products text', 'b2bking'), array($this,'b2bking_hide_b2b_site_text_setting_content'), 'b2bking', 'b2bking_languagesettings_text_section');

		// Hidden price dynamic rule text
		register_setting('b2bking', 'b2bking_hidden_price_dynamic_rule_text_setting');
		add_settings_field('b2bking_hidden_price_dynamic_rule_text_setting', esc_html__('Hidden price dynamic rule text', 'b2bking'), array($this,'b2bking_hidden_price_dynamic_rule_text_setting_content'), 'b2bking', 'b2bking_languagesettings_text_section');

		// Hide prices to guests text
		register_setting('b2bking', 'b2bking_retail_price_text_setting');
		add_settings_field('b2bking_retail_price_text_setting', esc_html__('Retail price text', 'b2bking'), array($this,'b2bking_retail_price_text_setting_content'), 'b2bking', 'b2bking_languagesettings_text_section');

		// Hide prices to guests text
		register_setting('b2bking', 'b2bking_wholesale_price_text_setting');
		add_settings_field('b2bking_wholesale_price_text_setting', esc_html__('Wholesale price text', 'b2bking'), array($this,'b2bking_wholesale_price_text_setting_content'), 'b2bking', 'b2bking_languagesettings_text_section');


		add_settings_section('b2bking_languagesettings_purchaselists_section', '',	'',	'b2bking');

		// Purchase Lists Language
		register_setting('b2bking', 'b2bking_purchase_lists_language_setting');
		add_settings_field('b2bking_purchase_lists_language_setting', esc_html__('Choose Purchase Lists Language', 'b2bking'), array($this,'b2bking_purchase_lists_language_setting_content'), 'b2bking', 'b2bking_languagesettings_purchaselists_section');

		/* Performance Settings */

		add_settings_section('b2bking_performance_settings_section', '',	'',	'b2bking');

		register_setting('b2bking', 'b2bking_disable_visibility_setting');

		register_setting('b2bking', 'b2bking_disable_registration_setting');
		add_settings_field('b2bking_disable_registration_setting', esc_html__('Disable registration & custom fields', 'b2bking'), array($this,'b2bking_disable_registration_setting_content'), 'b2bking', 'b2bking_performance_settings_section');

		register_setting('b2bking', 'b2bking_disable_registration_scripts_setting');
		add_settings_field('b2bking_disable_registration_scripts_setting', esc_html__('Disable frontend registration scripts', 'b2bking'), array($this,'b2bking_disable_registration_scripts_setting_content'), 'b2bking', 'b2bking_performance_settings_section');

		register_setting('b2bking', 'b2bking_disable_shipping_control_setting');
		add_settings_field('b2bking_disable_shipping_control_setting', esc_html__('Disable shipping methods control', 'b2bking'), array($this,'b2bking_disable_shipping_control_setting_content'), 'b2bking', 'b2bking_performance_settings_section');

		register_setting('b2bking', 'b2bking_disable_payment_control_setting');
		add_settings_field('b2bking_disable_payment_control_setting', esc_html__('Disable payment methods control', 'b2bking'), array($this,'b2bking_disable_payment_control_setting_content'), 'b2bking', 'b2bking_performance_settings_section');

		register_setting('b2bking', 'b2bking_disable_group_tiered_pricing_setting');
		add_settings_field('b2bking_disable_group_tiered_pricing_setting', esc_html__('Disable group & tiered pricing', 'b2bking'), array($this,'b2bking_disable_group_tiered_pricing_setting_content'), 'b2bking', 'b2bking_performance_settings_section');

		
		register_setting('b2bking', 'b2bking_disable_dynamic_rule_discount_setting');
		add_settings_field('b2bking_disable_dynamic_rule_discount_setting', esc_html__('Disable dynamic rule discounts', 'b2bking'), array($this,'b2bking_disable_dynamic_rule_discount_setting_content'), 'b2bking', 'b2bking_performance_settings_section');
		register_setting('b2bking', 'b2bking_disable_dynamic_rule_discount_sale_setting');
		add_settings_field('b2bking_disable_dynamic_rule_discount_sale_setting', esc_html__('Disable dynamic rule discounts as sale price', 'b2bking'), array($this,'b2bking_disable_dynamic_rule_discount_sale_setting_content'), 'b2bking', 'b2bking_performance_settings_section');
		register_setting('b2bking', 'b2bking_disable_dynamic_rule_fixedprice_setting');
		add_settings_field('b2bking_disable_dynamic_rule_fixedprice_setting', esc_html__('Disable dynamic rule fixed price', 'b2bking'), array($this,'b2bking_disable_dynamic_rule_fixedprice_setting_content'), 'b2bking', 'b2bking_performance_settings_section');
		register_setting('b2bking', 'b2bking_disable_dynamic_rule_hiddenprice_setting');
		add_settings_field('b2bking_disable_dynamic_rule_hiddenprice_setting', esc_html__('Disable dynamic rule hidden price', 'b2bking'), array($this,'b2bking_disable_dynamic_rule_hiddenprice_setting_content'), 'b2bking', 'b2bking_performance_settings_section');

		register_setting('b2bking', 'b2bking_disable_dynamic_rule_addtax_setting');
		add_settings_field('b2bking_disable_dynamic_rule_addtax_setting', esc_html__('Disable dynamic rule add tax/fee', 'b2bking'), array($this,'b2bking_disable_dynamic_rule_addtax_setting_content'), 'b2bking', 'b2bking_performance_settings_section');

		register_setting('b2bking', 'b2bking_disable_dynamic_rule_freeshipping_setting');
		add_settings_field('b2bking_disable_dynamic_rule_freeshipping_setting', esc_html__('Disable dynamic rule free shipping', 'b2bking'), array($this,'b2bking_disable_dynamic_rule_freeshipping_setting_content'), 'b2bking', 'b2bking_performance_settings_section');
		register_setting('b2bking', 'b2bking_disable_dynamic_rule_minmax_setting');
		add_settings_field('b2bking_disable_dynamic_rule_minmax_setting', esc_html__('Disable dynamic rule minimum and maximum order', 'b2bking'), array($this,'b2bking_disable_dynamic_rule_minmax_setting_content'), 'b2bking', 'b2bking_performance_settings_section');


		register_setting('b2bking', 'b2bking_disable_dynamic_rule_requiredmultiple_setting');
		add_settings_field('b2bking_disable_dynamic_rule_requiredmultiple_setting', esc_html__('Disable dynamic rule required multiple', 'b2bking'), array($this,'b2bking_disable_dynamic_rule_requiredmultiple_setting_content'), 'b2bking', 'b2bking_performance_settings_section');

		register_setting('b2bking', 'b2bking_disable_dynamic_rule_zerotax_setting');
		add_settings_field('b2bking_disable_dynamic_rule_zerotax_setting', esc_html__('Disable dynamic rule zero tax', 'b2bking'), array($this,'b2bking_disable_dynamic_rule_zerotax_setting_content'), 'b2bking', 'b2bking_performance_settings_section');

		register_setting('b2bking', 'b2bking_disable_dynamic_rule_taxexemption_setting');
		add_settings_field('b2bking_disable_dynamic_rule_taxexemption_setting', esc_html__('Disable dynamic rule tax exemption', 'b2bking'), array($this,'b2bking_disable_dynamic_rule_taxexemption_setting_content'), 'b2bking', 'b2bking_performance_settings_section');

		

		/* Other Settings */

		add_settings_section('b2bking_othersettings_section', '',	'',	'b2bking');

		// Keep data on uninstall 
		register_setting('b2bking', 'b2bking_keepdata_setting');
		add_settings_field('b2bking_keepdata_setting', esc_html__('Keep data on uninstall:', 'b2bking'), array($this,'b2bking_keepdata_setting_content'), 'b2bking', 'b2bking_othersettings_section');


		add_settings_section('b2bking_othersettings_multisite_section', '',	'',	'b2bking');

		// Multisite setting
		register_setting('b2bking', 'b2bking_multisite_separate_b2bb2c_setting');
		add_settings_field('b2bking_multisite_separate_b2bb2c_setting', esc_html__('Separate B2B and B2C sites in multisite', 'b2bking'), array($this,'b2bking_multisite_separate_b2bb2c_setting_content'), 'b2bking', 'b2bking_othersettings_multisite_section');

		add_settings_section('b2bking_othersettings_bulkorderform_section', '',	'',	'b2bking');

		// Order Form Theme
		register_setting('b2bking', 'b2bking_order_form_theme_setting');
		add_settings_field('b2bking_order_form_theme_setting', esc_html__('Order Form Theme', 'b2bking'), array($this,'b2bking_order_form_theme_setting_content'), 'b2bking', 'b2bking_othersettings_bulkorderform_section');

		// Order Form Theme
		register_setting('b2bking', 'b2bking_order_form_sortby_setting');
		add_settings_field('b2bking_order_form_sortby_setting', esc_html__('Sort Products By (Cream & Indigo)', 'b2bking'), array($this,'b2bking_order_form_sortby_setting_content'), 'b2bking', 'b2bking_othersettings_bulkorderform_section');

		// Search by SKU setting
		register_setting('b2bking', 'b2bking_search_by_sku_setting');
		add_settings_field('b2bking_search_by_sku_setting', esc_html__('Search by SKU', 'b2bking'), array($this,'b2bking_search_by_sku_setting_content'), 'b2bking', 'b2bking_othersettings_bulkorderform_section');

		// Search by Description setting
		register_setting('b2bking', 'b2bking_search_product_description_setting');
		add_settings_field('b2bking_search_product_description_setting', esc_html__('Search product description', 'b2bking'), array($this,'b2bking_search_product_description_setting_content'), 'b2bking', 'b2bking_othersettings_bulkorderform_section');

		// Search each individual variation setting
		register_setting('b2bking', 'b2bking_search_each_variation_setting');
		add_settings_field('b2bking_search_each_variation_setting', esc_html__('Search each individual variation', 'b2bking'), array($this,'b2bking_search_each_variation_setting_content'), 'b2bking', 'b2bking_othersettings_bulkorderform_section');

		// Show accounting subtotals
		register_setting('b2bking', 'b2bking_show_accounting_subtotals_setting');
		add_settings_field('b2bking_show_accounting_subtotals_setting', esc_html__('Show accounting subtotals', 'b2bking'), array($this,'b2bking_show_accounting_subtotals_setting_content'), 'b2bking', 'b2bking_othersettings_bulkorderform_section');

		// Show images in bulk order form
		register_setting('b2bking', 'b2bking_show_images_bulk_order_form_setting');
		add_settings_field('b2bking_show_images_bulk_order_form_setting', esc_html__('Show images in order form', 'b2bking'), array($this,'b2bking_show_images_bulk_order_form_setting_content'), 'b2bking', 'b2bking_othersettings_bulkorderform_section');

		// PRICE and PRODUCT DISPLAY SETTINGS START

		add_settings_section('b2bking_othersettings_priceproductdisplay_section', '',	'',	'b2bking');

		register_setting('b2bking', 'b2bking_show_moq_product_page_setting');
		add_settings_field('b2bking_show_moq_product_page_setting', esc_html__('Show MOQ Externally', 'b2bking'), array($this,'b2bking_show_moq_product_page_setting_content'), 'b2bking', 'b2bking_othersettings_priceproductdisplay_section');

		register_setting('b2bking', 'b2bking_show_b2c_price_setting');
		add_settings_field('b2bking_show_b2c_price_setting', esc_html__('Show B2C price to B2B users.', 'b2bking'), array($this,'b2bking_show_b2c_price_setting_content'), 'b2bking', 'b2bking_othersettings_priceproductdisplay_section');
		
		register_setting('b2bking', 'b2bking_modify_suffix_vat_setting');
		add_settings_field('b2bking_modify_suffix_vat_setting', esc_html__('Modify price suffix based on tax exemption rules', 'b2bking'), array($this,'b2bking_modify_suffix_vat_setting_content'), 'b2bking', 'b2bking_othersettings_priceproductdisplay_section');
		// PRICE and PRODUCT DISPLAY SETTINGS END

		// TIEREDPRICING START

		add_settings_section('b2bking_othersettings_tieredpricing_section', '',	'',	'b2bking');

		register_setting('b2bking', 'b2bking_show_discount_in_table_setting');
		add_settings_field('b2bking_show_discount_in_table_setting', esc_html__('Show Discount in Table', 'b2bking'), array($this,'b2bking_show_discount_in_table_setting_content'), 'b2bking', 'b2bking_othersettings_tieredpricing_section');

		register_setting('b2bking', 'b2bking_color_price_range_setting');
		add_settings_field('b2bking_color_price_range_setting', esc_html__('Color Price Range', 'b2bking'), array($this,'b2bking_color_price_range_setting_content'), 'b2bking', 'b2bking_othersettings_tieredpricing_section');

		register_setting('b2bking', 'b2bking_show_tieredp_product_page_setting');
		add_settings_field('b2bking_show_tieredp_product_page_setting', esc_html__('Show Tiered Price Range', 'b2bking'), array($this,'b2bking_show_tieredp_product_page_setting_content'), 'b2bking', 'b2bking_othersettings_tieredpricing_section');
		// TIEREDPRICING END

		
		add_settings_section('b2bking_othersettings_permalinks_section', '',	'',	'b2bking');
		// Force permalinks to show
		register_setting('b2bking', 'b2bking_force_permalinks_setting');
		add_settings_field('b2bking_force_permalinks_setting', esc_html__('Change My Account URL Structure:', 'b2bking'), array($this,'b2bking_force_permalinks_setting_content'), 'b2bking', 'b2bking_othersettings_permalinks_section');

		// Force permalinks to show
		register_setting('b2bking', 'b2bking_force_permalinks_flushing_setting');
		add_settings_field('b2bking_force_permalinks_flushing_setting', esc_html__('Force Permalinks Rewrite', 'b2bking'), array($this,'b2bking_force_permalinks_flushing_setting_content'), 'b2bking', 'b2bking_othersettings_permalinks_hidden_section');
		// hidden section, so that force permalinks is enabled by default with no option to disable via UI

		add_settings_section('b2bking_othersettings_largestores_section', '',	'',	'b2bking');

		register_setting('b2bking', 'b2bking_replace_product_selector_setting');
		add_settings_field('b2bking_replace_product_selector_setting', esc_html__('Dynamic rules: replace product selector with text box', 'b2bking'), array($this,'b2bking_replace_product_selector_setting_content'), 'b2bking', 'b2bking_othersettings_largestores_section');

		register_setting('b2bking', 'b2bking_hide_users_dynamic_rules_setting');
		add_settings_field('b2bking_hide_users_dynamic_rules_setting', esc_html__('Dynamic rules: hide individual users', 'b2bking'), array($this,'b2bking_hide_users_dynamic_rules_setting_content'), 'b2bking', 'b2bking_othersettings_largestores_section');

		register_setting('b2bking', 'b2bking_customers_panel_ajax_setting');
		add_settings_field('b2bking_customers_panel_ajax_setting', esc_html__('Customers panel: Search by AJAX', 'b2bking'), array($this,'b2bking_customers_panel_ajax_setting_content'), 'b2bking', 'b2bking_othersettings_largestores_section');

		add_settings_section('b2bking_othersettings_caching_section', '',	'',	'b2bking');
		// Search by SKU setting
		register_setting('b2bking', 'b2bking_product_visibility_cache_setting');
		add_settings_field('b2bking_product_visibility_cache_setting', esc_html__('Product visibility cache', 'b2bking'), array($this,'b2bking_product_visibility_cache_setting_content'), 'b2bking', 'b2bking_othersettings_caching_section');

		add_settings_section('b2bking_othersettings_stock_section', '',	'',	'b2bking');

		register_setting('b2bking', 'b2bking_different_stock_treatment_b2b_setting');
		add_settings_field('b2bking_different_stock_treatment_b2b_setting', esc_html__('Different B2B & B2C stock', 'b2bking'), array($this,'b2bking_different_stock_treatment_b2b_setting_content'), 'b2bking', 'b2bking_othersettings_stock_section');

		register_setting('b2bking', 'b2bking_hide_stock_for_b2c_setting');
		add_settings_field('b2bking_hide_stock_for_b2c_setting', esc_html__('Hide stock for B2C users', 'b2bking'), array($this,'b2bking_hide_stock_for_b2c_setting_content'), 'b2bking', 'b2bking_othersettings_stock_section');

		add_settings_section('b2bking_othersettings_company_section', '',	'',	'b2bking');

		register_setting('b2bking', 'b2bking_enable_company_approval_setting');
		add_settings_field('b2bking_enable_company_approval_setting', esc_html__('Company Order Approval', 'b2bking'), array($this,'b2bking_enable_company_approval_setting_content'), 'b2bking', 'b2bking_othersettings_company_section');


		add_settings_section('b2bking_othersettings_compatibility_section', '',	'',	'b2bking');
		// Product addon / options compatibility
		register_setting('b2bking', 'b2bking_product_options_compatibility_setting');
		add_settings_field('b2bking_product_options_compatibility_setting', esc_html__('Product addons / options compatibility (beta)', 'b2bking'), array($this,'b2bking_product_options_compatibility_setting_content'), 'b2bking', 'b2bking_othersettings_compatibility_section');

		add_settings_section('b2bking_othersettings_vat_section', '',	'',	'b2bking');
		// Search by SKU setting
		register_setting('b2bking', 'b2bking_vat_exemption_different_country_setting');
		add_settings_field('b2bking_vat_exemption_different_country_setting', esc_html__('Different delivery country for VAT exemption', 'b2bking'), array($this,'b2bking_vat_exemption_different_country_setting_content'), 'b2bking', 'b2bking_othersettings_vat_section');

		// Color and Design
		register_setting('b2bking', 'b2bking_purchase_lists_color_header_setting');
		register_setting('b2bking', 'b2bking_purchase_lists_color_action_buttons_setting');
		register_setting('b2bking', 'b2bking_purchase_lists_color_new_list_setting');

		add_settings_section( 'b2bking_othersettings_colordesign_section', '', '', 'b2bking' );
		register_setting(
			'b2bking',
			'b2bking_color_setting',
			array(
				'sanitize_callback' => function ( $input ) {
					return $input === null ? get_option( 'b2bking_color_setting', '#3AB1E4' ) : $input;
				},
			)
		);
		add_settings_field( 'b2bking_color_setting', esc_html__( 'Frontend Color', 'b2bking' ), array( $this, 'b2bking_color_setting_content' ), 'b2bking', 'b2bking_othersettings_colordesign_section' );

		register_setting(
			'b2bking',
			'b2bking_colorhover_setting',
			array(
				'sanitize_callback' => function ( $input ) {
					return $input === null ? get_option( 'b2bking_colorhover_setting', '#0088c2' ) : $input;
				},
			)
		);
		add_settings_field( 'b2bking_colorhover_setting', esc_html__( 'Frontend Hover Color', 'b2bking' ), array( $this, 'b2bking_colorhover_setting_content' ), 'b2bking', 'b2bking_othersettings_colordesign_section' );

		// Account Endpoints
		add_settings_section( 'b2bking_othersettings_endpoints_section', '', '', 'b2bking' );
		register_setting('b2bking', 'b2bking_conversations_endpoint_setting');
		add_settings_field('b2bking_conversations_endpoint_setting', esc_html__('Conversations endpoint:', 'b2bking'), array($this,'b2bking_conversations_endpoint_setting_content'), 'b2bking', 'b2bking_othersettings_endpoints_section');

		register_setting('b2bking', 'b2bking_conversation_endpoint_setting');
		add_settings_field('b2bking_conversation_endpoint_setting', esc_html__('Conversation endpoint:', 'b2bking'), array($this,'b2bking_conversation_endpoint_setting_content'), 'b2bking', 'b2bking_othersettings_endpoints_section');

		register_setting('b2bking', 'b2bking_offers_endpoint_setting');
		add_settings_field('b2bking_offers_endpoint_setting', esc_html__('Offers endpoint:', 'b2bking'), array($this,'b2bking_offers_endpoint_setting_content'), 'b2bking', 'b2bking_othersettings_endpoints_section');

		register_setting('b2bking', 'b2bking_bulkorder_endpoint_setting');
		add_settings_field('b2bking_bulkorder_endpoint_setting', esc_html__('Bulk Order endpoint:', 'b2bking'), array($this,'b2bking_bulkorder_endpoint_setting_content'), 'b2bking', 'b2bking_othersettings_endpoints_section');

		register_setting('b2bking', 'b2bking_subaccounts_endpoint_setting');
		add_settings_field('b2bking_subaccounts_endpoint_setting', esc_html__('Subaccounts endpoint:', 'b2bking'), array($this,'b2bking_subaccounts_endpoint_setting_content'), 'b2bking', 'b2bking_othersettings_endpoints_section');

		register_setting('b2bking', 'b2bking_subaccount_endpoint_setting');
		add_settings_field('b2bking_subaccount_endpoint_setting', esc_html__('Subaccount endpoint:', 'b2bking'), array($this,'b2bking_subaccount_endpoint_setting_content'), 'b2bking', 'b2bking_othersettings_endpoints_section');

		register_setting('b2bking', 'b2bking_purchaselists_endpoint_setting');
		add_settings_field('b2bking_purchaselists_endpoint_setting', esc_html__('Purchase Lists endpoint:', 'b2bking'), array($this,'b2bking_purchaselists_endpoint_setting_content'), 'b2bking', 'b2bking_othersettings_endpoints_section');

		register_setting('b2bking', 'b2bking_purchaselist_endpoint_setting');
		add_settings_field('b2bking_purchaselist_endpoint_setting', esc_html__('Purchase List endpoint:', 'b2bking'), array($this,'b2bking_purchaselist_endpoint_setting_content'), 'b2bking', 'b2bking_othersettings_endpoints_section');

		if (defined('b2bkingcredit_DIR')){
			register_setting('b2bking', 'b2bking_credit_endpoint_setting');
			add_settings_field('b2bking_credit_endpoint_setting', esc_html__('Company Credit endpoint:', 'b2bking'), array($this,'b2bking_credit_endpoint_setting_content'), 'b2bking', 'b2bking_othersettings_endpoints_section');
		}


		do_action('b2bking_register_settings');

	}

	public function b2bking_color_setting_content() {
		?>
		<input name="b2bking_color_setting" type="color" value="<?php echo esc_attr( get_option( 'b2bking_color_setting', '#3AB1E4' ) ); ?>">
		<?php
	}

	public function b2bking_colorhover_setting_content() {
		?>
		<input name="b2bking_colorhover_setting" type="color" value="<?php echo esc_attr( get_option( 'b2bking_colorhover_setting', '#0088c2' ) ); ?>">
		<?php
	}

	function b2bking_hide_stock_for_b2c_setting_content(){
		?>
		<div class="ui large form">
		  <div class="inline fields">
		  	<div class="field">
		  	  <div class="ui checkbox">
		  	    <input type="radio" tabindex="0" class="hidden" name="b2bking_hide_stock_for_b2c_setting" value="disabled" <?php checked('disabled',get_option( 'b2bking_hide_stock_for_b2c_setting', 'disabled' ), true); ?>">
		  	    <label><?php esc_html_e('Disabled','b2bking'); ?></label>
		  	  </div>
		  	</div>
		    <div class="field">
		      <div class="ui checkbox">
		        <input type="radio" tabindex="0" class="hidden" name="b2bking_hide_stock_for_b2c_setting" value="hidecompletely" <?php checked('hidecompletely',get_option( 'b2bking_hide_stock_for_b2c_setting', 'disabled' ), true); ?>">
		        <label><i class="eye slash icon"></i>&nbsp;<?php esc_html_e('Hide stock completely for B2C','b2bking'); ?></label>
		      </div>
		    </div>
		    <div class="field">
		      <div class="ui checkbox">
		        <input type="radio" tabindex="0" class="hidden" name="b2bking_hide_stock_for_b2c_setting" value="hideprecision" <?php checked('hideprecision',get_option( 'b2bking_hide_stock_for_b2c_setting', 'disabled' ), true); ?>">
		        <label><i class="eye slash outline icon"></i>&nbsp;<?php esc_html_e('Hide stock quantities for B2C','b2bking'); ?></label>
		      </div>
		    </div>
		   		    
		  </div>
		</div>
		<?php
	}

	function b2bking_different_stock_treatment_b2b_setting_content(){
		?>
		<div class="ui large form">
		  <div class="inline fields">
		  	<div class="field">
		  	  <div class="ui checkbox">
		  	    <input type="radio" tabindex="0" class="hidden" name="b2bking_different_stock_treatment_b2b_setting" value="disabled" <?php checked('disabled',get_option( 'b2bking_different_stock_treatment_b2b_setting', 'disabled' ), true); ?>">
		  	    <label><?php esc_html_e('Disabled','b2bking'); ?></label>
		  	  </div>
		  	</div>
		  	<div class="field">
		  	  <div class="ui checkbox">
		  	    <input type="radio" tabindex="0" class="hidden" name="b2bking_different_stock_treatment_b2b_setting" value="b2binstock" <?php checked('b2binstock',get_option( 'b2bking_different_stock_treatment_b2b_setting', 'disabled' ), true); ?>">
		  	    <label><i class="clipboard check icon"></i>&nbsp;<?php esc_html_e('Always in stock for B2B','b2bking'); ?></label>
		  	  </div>
		  	</div>
		    <div class="field">
		      <div class="ui checkbox">
		        <input type="radio" tabindex="0" class="hidden" name="b2bking_different_stock_treatment_b2b_setting" value="b2b" <?php checked('b2b',get_option( 'b2bking_different_stock_treatment_b2b_setting', 'disabled' ), true); ?>">
		        <label><i class="briefcase icon"></i>&nbsp;<?php esc_html_e('Separate stock for B2B & B2C','b2bking'); ?></label>
		      </div>
		    </div>
		   		    
		  </div>
		</div>
		<?php
	}

	function b2bking_conversations_endpoint_setting_content(){
		echo '
		<div class="ui form">
			<div class="field">
				<label>'.esc_html__('Endpoint for the My Account -> Conversations page','b2bking').'</label>
				<input type="text" name="b2bking_conversations_endpoint_setting" value="'.esc_attr(get_option('b2bking_conversations_endpoint_setting', 'conversations')).'">
			</div>
		</div>
		';
	}

	function b2bking_conversation_endpoint_setting_content(){
		echo '
		<div class="ui form">
			<div class="field">
				<label>'.esc_html__('Endpoint for a specific conversation page','b2bking').'</label>
				<input type="text" name="b2bking_conversation_endpoint_setting" value="'.esc_attr(get_option('b2bking_conversation_endpoint_setting', 'conversation')).'">
			</div>
		</div>
		';
	}

	function b2bking_offers_endpoint_setting_content(){
		echo '
		<div class="ui form">
			<div class="field">
				<label>'.esc_html__('Endpoint for the My Account -> Offers page','b2bking').'</label>
				<input type="text" name="b2bking_offers_endpoint_setting" value="'.esc_attr(get_option('b2bking_offers_endpoint_setting', 'offers')).'">
			</div>
		</div>
		';
	}

	function b2bking_bulkorder_endpoint_setting_content(){
		echo '
		<div class="ui form">
			<div class="field">
				<label>'.esc_html__('Endpoint for the My Account -> Bulk Order page','b2bking').'</label>
				<input type="text" name="b2bking_bulkorder_endpoint_setting" value="'.esc_attr(get_option('b2bking_bulkorder_endpoint_setting', 'bulkorder')).'">
			</div>
		</div>
		';
	}

	function b2bking_subaccounts_endpoint_setting_content(){
		echo '
		<div class="ui form">
			<div class="field">
				<label>'.esc_html__('Endpoint for the My Account -> Subaccounts page','b2bking').'</label>
				<input type="text" name="b2bking_subaccounts_endpoint_setting" value="'.esc_attr(get_option('b2bking_subaccounts_endpoint_setting', 'subaccounts')).'">
			</div>
		</div>
		';
	}

	function b2bking_subaccount_endpoint_setting_content(){
		echo '
		<div class="ui form">
			<div class="field">
				<label>'.esc_html__('Endpoint for a specific subaccount page','b2bking').'</label>
				<input type="text" name="b2bking_subaccount_endpoint_setting" value="'.esc_attr(get_option('b2bking_subaccount_endpoint_setting', 'subaccount')).'">
			</div>
		</div>
		';
	}

	function b2bking_purchaselists_endpoint_setting_content(){
		echo '
		<div class="ui form">
			<div class="field">
				<label>'.esc_html__('Endpoint for the My Account -> Purchase Lists page','b2bking').'</label>
				<input type="text" name="b2bking_purchaselists_endpoint_setting" value="'.esc_attr(get_option('b2bking_purchaselists_endpoint_setting', 'purchase-lists')).'">
			</div>
		</div>
		';
	}

	function b2bking_purchaselist_endpoint_setting_content(){
		echo '
		<div class="ui form">
			<div class="field">
				<label>'.esc_html__('Endpoint for a specific purchase list page','b2bking').'</label>
				<input type="text" name="b2bking_purchaselist_endpoint_setting" value="'.esc_attr(get_option('b2bking_purchaselist_endpoint_setting', 'purchase-list')).'">
			</div>
		</div>
		';
	}
	function b2bking_credit_endpoint_setting_content(){
		echo '
		<div class="ui form">
			<div class="field">
				<label>'.esc_html__('Endpoint for the company credit section','b2bking').'</label>
				<input type="text" name="b2bking_credit_endpoint_setting" value="'.esc_attr(get_option('b2bking_credit_endpoint_setting', 'company-credit')).'">
			</div>
		</div>
		';
	}

	
	function b2bking_show_discount_in_table_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_show_discount_in_table_setting" value="1" '.checked(1,get_option( 'b2bking_show_discount_in_table_setting', 0 ), false).'">
		  <label>'.esc_html__('Calculate and show discount percentage in table','b2bking').'</label>
		</div>
		';
	}

	function b2bking_color_price_range_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_color_price_range_setting" value="1" '.checked(1,get_option( 'b2bking_color_price_range_setting', 1 ), false).'">
		  <label>'.esc_html__('Dynamically color the active price range in table','b2bking').'</label>
		</div>
		';
	}

	function b2bking_vat_exemption_different_country_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_vat_exemption_different_country_setting" value="1" '.checked(1,get_option( 'b2bking_vat_exemption_different_country_setting', 0 ), false).'">
		  <label>'.esc_html__('Require delivery country to be different than shop country for VAT exemption. Not recommended for most setups - enable only if needed.','b2bking').'</label>
		</div>
		';
	}

	function b2bking_product_visibility_cache_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_product_visibility_cache_setting" value="1" '.checked(1,get_option( 'b2bking_product_visibility_cache_setting', 0 ), false).'">
		  <label>'.esc_html__('Some situations may require disabling this setting.','b2bking').'</label>
		</div>
		';
	}

	function b2bking_enable_company_approval_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_enable_company_approval_setting" value="1" '.checked(1,get_option( 'b2bking_enable_company_approval_setting', 0 ), false).'">
		  <label>'.esc_html__('Allows users to enable order review and approval for their subaccounts\' orders.','b2bking').'</label>
		</div>
		';
	}

	function b2bking_product_options_compatibility_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_product_options_compatibility_setting" value="1" '.checked(1,get_option( 'b2bking_product_options_compatibility_setting', 0 ), false).'">
		  <label>'.esc_html__('Improves pricing compatibility with plugins that add product options / addons. Not recommended for most setups - enable only if needed.','b2bking').'</label>
		</div>
		';
	}

	function b2bking_replace_product_selector_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_replace_product_selector_setting" value="1" '.checked(1,get_option( 'b2bking_replace_product_selector_setting', 0 ), false).'">
		  <label>'.esc_html__('For large numbers of products, this prevents crashes.','b2bking').'</label>
		</div>
		';
	}

	function b2bking_hide_users_dynamic_rules_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_hide_users_dynamic_rules_setting" value="1" '.checked(1,get_option( 'b2bking_hide_users_dynamic_rules_setting', 0 ), false).'">
		  <label>'.esc_html__('For large numbers of users, this prevents crashes.','b2bking').'</label>
		</div>
		';
	}

	function b2bking_customers_panel_ajax_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_customers_panel_ajax_setting" value="1" '.checked(1,get_option( 'b2bking_customers_panel_ajax_setting', 0 ), false).'">
		  <label>'.esc_html__('Load users with AJAX in the admin customers panel.','b2bking').'</label>
		</div>
		';
	}

	function b2bking_hidden_has_priority_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_hidden_has_priority_setting" value="1" '.checked(1,get_option( 'b2bking_hidden_has_priority_setting', 0 ), false).'">
		  <label>'.esc_html__('Hide products if they are part of at least 1 hidden category','b2bking').'</label>
		</div>
		';
	}

	function b2bking_force_permalinks_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_force_permalinks_setting" value="1" '.checked(1,get_option( 'b2bking_force_permalinks_setting', 1 ), false).'">
		  <label>'.esc_html__('Changes URL structure in My Account. Can solve 404 error issues and improve loading speed.','b2bking').'</label>
		</div>
		';
	}

	function b2bking_force_permalinks_flushing_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_force_permalinks_flushing_setting" value="1" '.checked(1,get_option( 'b2bking_force_permalinks_flushing_setting', 1 ), false).'">
		  <label>'.esc_html__('Force permalinks rewrite. Can solve 404 issues in My Account page.','b2bking').'</label>
		</div>
		';
	}

    function b2bking_show_dynamic_rules_vendors_setting_wcfm_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_show_dynamic_rules_vendors_setting_wcfm" value="1" '.checked(1,get_option( 'b2bking_show_dynamic_rules_vendors_setting_wcfm', 1 ), false).'">
		  <label>'.esc_html__('Show rules to vendors in dashboard','b2bkingwcfm').'</label>
		</div>
		';
    }

    function b2bking_show_visibility_vendors_setting_wcfm_content(){
        echo '
        <div class="ui toggle checkbox">
          <input type="checkbox" name="b2bking_show_visibility_vendors_setting_wcfm" value="1" '.checked(1,get_option( 'b2bking_show_visibility_vendors_setting_wcfm', 1 ), false).'">
          <label>'.esc_html__('Show visibility to vendors in dashboard for each product','b2bkingwcfm').'</label>
        </div>
        ';
    }

    function b2bking_show_dynamic_rules_vendors_setting_marketking_content(){
    	echo '
    	<div class="ui toggle checkbox">
    	  <input type="checkbox" name="b2bking_show_dynamic_rules_vendors_setting_marketking" value="1" '.checked(1,get_option( 'b2bking_show_dynamic_rules_vendors_setting_marketking', 1 ), false).'">
    	  <label>'.esc_html__('Show rules to vendors in dashboard','b2bkingmarketking').'</label>
    	</div>
    	';
    }

    function b2bking_show_visibility_vendors_setting_marketking_content(){
        echo '
        <div class="ui toggle checkbox">
          <input type="checkbox" name="b2bking_show_visibility_vendors_setting_marketking" value="1" '.checked(1,get_option( 'b2bking_show_visibility_vendors_setting_marketking', 1 ), false).'">
          <label>'.esc_html__('Show visibility to vendors in dashboard for each product','b2bkingmarketking').'</label>
        </div>
        ';
    }



	/* Offer Settings */


	function b2bking_offers_product_selector_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_offers_product_selector_setting" value="1" '.checked(1,get_option( 'b2bking_offers_product_selector_setting', 0 ), false).'">
		  <label>'.esc_html__('Replace text box with product selector','b2bking').'</label>
		</div>
		';
	}

	function b2bking_offers_product_image_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_offers_product_image_setting" value="1" '.checked(1,get_option( 'b2bking_offers_product_image_setting', 0 ), false).'">
		  <label>'.esc_html__('Show product images in My Account->Offers and in Offer PDFs','b2bking').'</label>
		</div>
		';
	}

	function b2bking_offers_logo_setting_content(){
		echo '
			<div>
			    <input type="text" name="b2bking_offers_logo_setting" id="b2bking_offers_logo_setting" class="regular-text" placeholder="'.esc_attr__('Your Custom Logo', 'salesking').'" value="'.esc_attr(get_option('b2bking_offers_logo_setting','')).'">&nbsp;&nbsp;
			    <input type="button" name="b2bking-logo-upload-btn" id="b2bking-logo-upload-btn" class="ui blue button tiny" value="'.esc_attr__('Select Image','b2bking').'"><label>&nbsp;&nbsp;'.esc_html__('Logo shown on Offer PDFs (e.g. company logo)','b2bking').'</label>
			</div>
		';
	}
	function b2bking_offers_image_setting_content(){
		echo '
			<div>
			    <input type="text" name="b2bking_offers_image_setting" id="b2bking_offers_image_setting" class="regular-text" placeholder="'.esc_attr__('Image for offers', 'salesking').'" value="'.esc_attr(get_option('b2bking_offers_image_setting','')).'">&nbsp;&nbsp;<label>
			    <input type="button" name="b2bking-logoimg-upload-btn" id="b2bking-logoimg-upload-btn" class="ui blue button tiny" value="'.esc_attr__('Select Image','b2bking').'">&nbsp;&nbsp;'.esc_html__('Applies to offers with 2 or more products','b2bking').'</label>
			</div>
		';
	}

	/* Performance Settings	*/

	function b2bking_disable_group_tiered_pricing_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_disable_group_tiered_pricing_setting" value="1" '.checked(1,get_option( 'b2bking_disable_group_tiered_pricing_setting', 0 ), false).'">
		</div>
		';
	}

	
	function b2bking_disable_registration_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_disable_registration_setting" value="1" '.checked(1,get_option( 'b2bking_disable_registration_setting', 0 ), false).'">
		</div>
		';
	}


	function b2bking_disable_registration_scripts_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_disable_registration_scripts_setting" value="1" '.checked(1,get_option( 'b2bking_disable_registration_scripts_setting', 0 ), false).'">
		</div>
		';
	}
	function b2bking_disable_dynamic_rule_discount_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_disable_dynamic_rule_discount_setting" value="1" '.checked(1,get_option( 'b2bking_disable_dynamic_rule_discount_setting', 0 ), false).'">
		</div>
		';
	}
	function b2bking_disable_dynamic_rule_discount_sale_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_disable_dynamic_rule_discount_sale_setting" value="1" '.checked(1,get_option( 'b2bking_disable_dynamic_rule_discount_sale_setting', 0 ), false).'">
		</div>
		';
	}
	function b2bking_disable_dynamic_rule_addtax_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_disable_dynamic_rule_addtax_setting" value="1" '.checked(1,get_option( 'b2bking_disable_dynamic_rule_addtax_setting', 0 ), false).'">
		</div>
		';
	}
	function b2bking_disable_dynamic_rule_fixedprice_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_disable_dynamic_rule_fixedprice_setting" value="1" '.checked(1,get_option( 'b2bking_disable_dynamic_rule_fixedprice_setting', 0 ), false).'">
		</div>
		';
	}

	function b2bking_disable_dynamic_rule_freeshipping_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_disable_dynamic_rule_freeshipping_setting" value="1" '.checked(1,get_option( 'b2bking_disable_dynamic_rule_freeshipping_setting', 0 ), false).'">
		</div>
		';
	}

	function b2bking_disable_dynamic_rule_minmax_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_disable_dynamic_rule_minmax_setting" value="1" '.checked(1,get_option( 'b2bking_disable_dynamic_rule_minmax_setting', 0 ), false).'">
		</div>
		';
	}

	function b2bking_disable_dynamic_rule_hiddenprice_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_disable_dynamic_rule_hiddenprice_setting" value="1" '.checked(1,get_option( 'b2bking_disable_dynamic_rule_hiddenprice_setting', 0 ), false).'">
		</div>
		';
	}

	function b2bking_disable_dynamic_rule_requiredmultiple_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_disable_dynamic_rule_requiredmultiple_setting" value="1" '.checked(1,get_option( 'b2bking_disable_dynamic_rule_requiredmultiple_setting', 0 ), false).'">
		</div>
		';
	}

	function b2bking_disable_dynamic_rule_zerotax_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_disable_dynamic_rule_zerotax_setting" value="1" '.checked(1,get_option( 'b2bking_disable_dynamic_rule_zerotax_setting', 0 ), false).'">
		</div>
		';
	}

	function b2bking_disable_dynamic_rule_taxexemption_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_disable_dynamic_rule_taxexemption_setting" value="1" '.checked(1,get_option( 'b2bking_disable_dynamic_rule_taxexemption_setting', 0 ), false).'">
		</div>
		';
	}
	function b2bking_disable_shipping_control_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_disable_shipping_control_setting" value="1" '.checked(1,get_option( 'b2bking_disable_shipping_control_setting', 0 ), false).'">
		</div>
		';
	}

	function b2bking_disable_payment_control_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_disable_payment_control_setting" value="1" '.checked(1,get_option( 'b2bking_disable_payment_control_setting', 0 ), false).'">
		</div>
		';
	}
	
	

	// This function remembers the current tab as a hidden input setting. When the page loads, it goes to the saved tab
	function b2bking_current_tab_setting_content(){
		echo '
		 <input type="hidden" id="b2bking_current_tab_setting_input" name="b2bking_current_tab_setting" value="'.esc_attr(get_option( 'b2bking_current_tab_setting', 'accessrestriction' )).'">
		';
	}

	function b2bking_all_products_visible_all_users_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_all_products_visible_all_users_setting" value="1" '.checked(1,get_option( 'b2bking_all_products_visible_all_users_setting', 1 ), false).'">
		  <label>'.esc_html__('All products are visible to all users. Disable this if you want to set product/category visibility manually.','b2bking').'</label>
		</div>
		';
	}

	function b2bking_guest_access_restriction_setting_website_redirect_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_guest_access_restriction_setting_website_redirect" value="1" '.checked(1,get_option( 'b2bking_guest_access_restriction_setting_website_redirect', 0 ), false).'">
		  <label>'.esc_html__('Enable this to also restrict access to pages. Disable this to hide shop & products, but show other pages.','b2bking').'</label>
		</div>
		';
	}

	function b2bking_registration_roles_dropdown_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_registration_roles_dropdown_setting" value="1" '.checked(1,get_option( 'b2bking_registration_roles_dropdown_setting', 1 ), false).'">
		  <label>'.esc_html__('Show registration roles dropdown and custom fields in registration','b2bking').'</label>
		</div>
		';
	}

	function b2bking_approval_required_all_users_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_approval_required_all_users_setting" value="1" '.checked(1,get_option( 'b2bking_approval_required_all_users_setting', 0 ), false).'">
		  <label>'.esc_html__('Require manual approval for all users\' registration','b2bking').'</label>
		</div>
		';	
	}

	function b2bking_registration_at_checkout_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_registration_at_checkout_setting" value="1" '.checked(1,get_option( 'b2bking_registration_at_checkout_setting', 0 ), false).'">
		  <label>'.esc_html__('For websites that allow registration at checkout','b2bking').'</label>
		</div>
		';	
	}

	function b2bking_order_form_theme_setting_content(){
		echo '
		  <select name="b2bking_order_form_theme_setting" id="b2bking_order_form_theme_setting_select">
		  	<option value="classic" '.selected('classic', get_option( 'b2bking_order_form_theme_setting', 'classic' ), false).'">'.esc_html__('Classic','b2bking').'</option>
		  	<option value="indigo" '.selected('indigo', get_option( 'b2bking_order_form_theme_setting', 'classic' ), false).'">'.esc_html__('Indigo','b2bking').'</option>		  
		  	<option value="cream" '.selected('cream', get_option( 'b2bking_order_form_theme_setting', 'classic' ), false).'">'.esc_html__('Cream','b2bking').'</option>		  
		  </select>
		';
	}

	function b2bking_order_form_sortby_setting_content(){
		echo '
		  <select name="b2bking_order_form_sortby_setting" id="b2bking_order_form_sortby_setting_select">
		  	<option value="atoz" '.selected('atoz', get_option( 'b2bking_order_form_sortby_setting', 'atoz' ), false).'">'.esc_html__('Alphabetically, A -> Z','b2bking').'</option>
		  	<option value="ztoa" '.selected('ztoa', get_option( 'b2bking_order_form_sortby_setting', 'atoz' ), false).'">'.esc_html__('Alphabetically, Z -> A','b2bking').'</option>		  
		  	<option value="bestselling" '.selected('bestselling', get_option( 'b2bking_order_form_sortby_setting', 'atoz' ), false).'">'.esc_html__('Best Selling','b2bking').'</option>		  
		  </select>
		';
	}

	function b2bking_registration_separate_my_account_page_setting_content(){
		echo '
		  <select name="b2bking_registration_separate_my_account_page_setting">
		  	<option value="disabled" '.selected('disabled', get_option( 'b2bking_registration_separate_my_account_page_setting', 'disabled' ), false).'">'.esc_html__('Disabled','b2bking').'</option>';
		  // get pages
		  $pages = get_pages();
		  foreach ($pages as $page){
		  	echo '<option value="'.esc_attr($page->ID).'" '.selected($page->ID, get_option( 'b2bking_registration_separate_my_account_page_setting', 'disabled' ), false).'">'.esc_html($page->post_title).'</option>';
		  }
		  echo'</select>&nbsp;&nbsp;<label>'.esc_html__('You can use this setting to set a different My Account page for business users. Not recommended for most setups.','b2bking').'</label>
		';	
	}

	function b2bking_validate_vat_button_checkout_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_validate_vat_button_checkout_setting" value="1" '.checked(1,get_option( 'b2bking_validate_vat_button_checkout_setting', 0 ), false).'">
		  <label>'.esc_html__('If VAT Number is provided during checkout / checkout registration, this button validates and applies VAT exemptions','b2bking').'</label>
		</div>
		';	
	}

	function b2bking_enable_conversations_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_enable_conversations_setting" value="1" '.checked(1,get_option( 'b2bking_enable_conversations_setting', 1 ), false).'">
		</div>
		';	
	}

	function b2bking_enable_offers_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_enable_offers_setting" value="1" '.checked(1,get_option( 'b2bking_enable_offers_setting', 1 ), false).'">
		</div>
		';	
	}

	function b2bking_enable_subaccounts_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_enable_subaccounts_setting" value="1" '.checked(1,get_option( 'b2bking_enable_subaccounts_setting', 1 ), false).'">
		</div>
		';	
	}

	function b2bking_enable_bulk_order_form_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_enable_bulk_order_form_setting" value="1" '.checked(1,get_option( 'b2bking_enable_bulk_order_form_setting', 1 ), false).'">
		</div>
		';	
	}

	function b2bking_enable_purchase_lists_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_enable_purchase_lists_setting" value="1" '.checked(1,get_option( 'b2bking_enable_purchase_lists_setting', 1 ), false).'">
		</div>
		';	
	}

	function b2bking_keepdata_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_keepdata_setting" value="1" '.checked(1,get_option( 'b2bking_keepdata_setting', 1 ), false).'">
		  <label>'.esc_html__('WARNING: Disabling this DELETES ALL plugin data when the plugin is uninstalled. We recommend you keep this enabled.','b2bking').'</label>
		</div>
		';	
	}

	function b2bking_multisite_separate_b2bb2c_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_multisite_separate_b2bb2c_setting" value="1" '.checked(1,get_option( 'b2bking_multisite_separate_b2bb2c_setting', 0 ), false).'">
		  <label>'.esc_html__('If you have a multisite and separate B2B and B2C sites, this option will treat B2C users as guests when visiting the B2B site and lock them out','b2bking').'</label>
		</div>
		';	
	}

	function b2bking_search_by_sku_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_search_by_sku_setting" value="1" '.checked(1,get_option( 'b2bking_search_by_sku_setting', 1 ), false).'">
		  <label>'.esc_html__('Enable searching by SKU in the Bulk Order Form','b2bking').'</label>
		</div>
		';	
	}

	function b2bking_search_product_description_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_search_product_description_setting" value="1" '.checked(1,get_option( 'b2bking_search_product_description_setting', 0 ), false).'">
		  <label>'.esc_html__('Also search product descriptions (slower)','b2bking').'</label>
		</div>
		';		
	}

	function b2bking_search_each_variation_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_search_each_variation_setting" value="1" '.checked(1,get_option( 'b2bking_search_each_variation_setting', 1 ), false).'">
		  <label>'.esc_html__('Necessary for individual SKU/name search for each variation. (slower)','b2bking').'</label>
		</div>
		';		
	}

	function b2bking_show_accounting_subtotals_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_show_accounting_subtotals_setting" value="1" '.checked(1,get_option( 'b2bking_show_accounting_subtotals_setting', 0 ), false).'">
		  <label>'.esc_html__('Accurate price display based on store settings (slower)','b2bking').'</label>
		</div>
		';		
	}

	function b2bking_show_images_bulk_order_form_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_show_images_bulk_order_form_setting" value="1" '.checked(1,get_option( 'b2bking_show_images_bulk_order_form_setting', 1 ), false).'">
		  <label>'.esc_html__('Show images in bulk order form search results','b2bking').'</label>
		</div>
		';
	}

	function b2bking_show_b2c_price_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_show_b2c_price_setting" value="1" '.checked(1,get_option( 'b2bking_show_b2c_price_setting', 0 ), false).'">
		  <label>'.esc_html__('Show both retail (e.g. RRP) and wholesale price to B2B users','b2bking').'</label>
		</div>
		';	
	}

	function b2bking_modify_suffix_vat_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_modify_suffix_vat_setting" value="1" '.checked(1,get_option( 'b2bking_modify_suffix_vat_setting', 0 ), false).'">
		  <label>'.esc_html__('B2BKing will add "ex. VAT / inc. VAT" to prices based on tax exemption dynamic rules','b2bking').'</label>
		</div>
		';	
	}

	function b2bking_show_moq_product_page_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_show_moq_product_page_setting" value="1" '.checked(1,get_option( 'b2bking_show_moq_product_page_setting', 0 ), false).'">
		  <label>'.esc_html__('Show Minimum Order Quantity in Archive / Shop / Cat Pages','b2bking').'</label>
		</div>
		';		
	}

	function b2bking_show_tieredp_product_page_setting_content(){
		echo '
		<div class="ui toggle checkbox">
		  <input type="checkbox" name="b2bking_show_tieredp_product_page_setting" value="1" '.checked(1,get_option( 'b2bking_show_tieredp_product_page_setting', 0 ), false).'">
		  <label>'.esc_html__('Show Tiered Price Range instead of Current Price','b2bking').'</label>
		</div>
		';		
	}

	function b2bking_retail_price_text_setting_content(){
		echo '
		<div class="ui form">
			<div class="field">
				<label>'.esc_html__('Controls price display in the product page with certain settings','b2bking').'</label>
				<input type="text" name="b2bking_retail_price_text_setting" value="'.esc_attr(get_option('b2bking_retail_price_text_setting', esc_html__('Retail price','b2bking'))).'">
			</div>
		</div>
		';
	}

	function b2bking_wholesale_price_text_setting_content(){
		echo '
		<div class="ui form">
			<div class="field">
				<label>'.esc_html__('Controls price display in the product page with certain settings','b2bking').'</label>
				<input type="text" name="b2bking_wholesale_price_text_setting" value="'.esc_attr(get_option('b2bking_wholesale_price_text_setting', esc_html__('Wholesale price','b2bking'))).'">
			</div>
		</div>
		';
	}


	function b2bking_hide_prices_guests_text_setting_content(){
		echo '
		<div class="ui form">
			<div class="field">
				<label>'.esc_html__('What guests see when "Hide prices" is enabled','b2bking').'</label>
				<input type="text" name="b2bking_hide_prices_guests_text_setting" value="'.esc_attr(get_option('b2bking_hide_prices_guests_text_setting', esc_html__('Login to view prices','b2bking'))).'">
			</div>
		</div>
		';
	}

	function b2bking_hide_b2b_site_text_setting_content(){
		echo '
		<div class="ui form">
			<div class="field">
				<label>'.esc_html__('What guests see when "Hide Shop & Products" is enabled','b2bking').'</label>
				<input type="text" name="b2bking_hide_b2b_site_text_setting" value="'.esc_attr(get_option('b2bking_hide_b2b_site_text_setting', esc_html__('Please login to access the B2B Portal.','b2bking'))).'">
			</div>
		</div>
		';
	}

	function b2bking_hidden_price_dynamic_rule_text_setting_content(){
		echo '
		<div class="ui form">
			<div class="field">
				<label>'.esc_html__('What users see when "Hidden Price" dynamic rules apply','b2bking').'</label>
				<input type="text" name="b2bking_hidden_price_dynamic_rule_text_setting" value="'.esc_attr(get_option('b2bking_hidden_price_dynamic_rule_text_setting', esc_html__('Price is unavailable','b2bking'))).'">
			</div>
		</div>
		';
	}



	function b2bking_purchase_lists_language_setting_content(){
		?>

		<div class="ui fluid search selection dropdown b2bking_purchase_lists_language_setting">
		  <input type="hidden" name="b2bking_purchase_lists_language_setting">
		  <i class="dropdown icon"></i>
		  <div class="default text"><?php esc_html_e('Select Country','b2bking'); ?></div>
		  <div class="menu">
		  <div class="item" data-value="English"><i class="uk flag"></i>English</div>
		  <div class="item" data-value="Afrikaans"><i class="za flag"></i>Afrikaans</div>
		  <div class="item" data-value="Albanian"><i class="al flag"></i>Albanian</div>
		  <div class="item" data-value="Arabic"><i class="dz flag"></i>Arabic</div>
		  <div class="item" data-value="Armenian"><i class="am flag"></i>Armenian</div>
		  <div class="item" data-value="Azerbaijan"><i class="az flag"></i>Azerbaijan</div>
		  <div class="item" data-value="Bangla"><i class="bd flag"></i>Bangla</div>
		  <div class="item" data-value="Basque"><i class="es flag"></i>Basque</div>
		  <div class="item" data-value="Belarusian"><i class="by flag"></i>Belarusian</div>
		  <div class="item" data-value="Bulgarian"><i class="bg flag"></i>Bulgarian</div>
		  <div class="item" data-value="Catalan"><i class="es flag"></i>Catalan</div>
		  <div class="item" data-value="Chinese"><i class="cn flag"></i>Chinese</div>
		  <div class="item" data-value="Chinese-traditional"><i class="cn flag"></i>Chinese Traditional</div>
		  <div class="item" data-value="Croatian"><i class="hr flag"></i>Croatian</div>
		  <div class="item" data-value="Czech"><i class="cz flag"></i>Czech</div>
		  <div class="item" data-value="Danish"><i class="dk flag"></i>Danish</div>
		  <div class="item" data-value="Dutch"><i class="nl flag"></i>Dutch</div>
		  <div class="item" data-value="Estonian"><i class="ee flag"></i>Estonian</div>
		  <div class="item" data-value="Filipino"><i class="ph flag"></i>Filipino</div>
		  <div class="item" data-value="Finnish"><i class="fi flag"></i>Finnish</div>
		  <div class="item" data-value="French"><i class="fr flag"></i>French</div>
		  <div class="item" data-value="Galician"><i class="es flag"></i>Galician</div>
		  <div class="item" data-value="Georgian"><i class="ge flag"></i>Georgian</div>
		  <div class="item" data-value="German"><i class="de flag"></i>German</div>
		  <div class="item" data-value="Greek"><i class="gr flag"></i>Greek</div>
		  <div class="item" data-value="Hebrew"><i class="il flag"></i>Hebrew</div>
		  <div class="item" data-value="Hindi"><i class="in flag"></i>Hindi</div>
		  <div class="item" data-value="Hungarian"><i class="hu flag"></i>Hungarian</div>
		  <div class="item" data-value="Icelandic"><i class="is flag"></i>Icelandic</div>
		  <div class="item" data-value="Indonesian"><i class="id flag"></i>Indonesian</div>
		  <div class="item" data-value="Italian"><i class="it flag"></i>Italian</div>
		  <div class="item" data-value="Japanese"><i class="jp flag"></i>Japanese</div>
		  <div class="item" data-value="Kazakh"><i class="kz flag"></i>Kazakh</div>
		  <div class="item" data-value="Korean"><i class="kr flag"></i>Korean</div>
		  <div class="item" data-value="Kyrgyz"><i class="kg flag"></i>Kyrgyz</div>
		  <div class="item" data-value="Latvian"><i class="lv flag"></i>Latvian</div>
		  <div class="item" data-value="Lithuanian"><i class="lt flag"></i>Lithuanian</div>
		  <div class="item" data-value="Macedonian"><i class="mk flag"></i>Macedonian</div>
		  <div class="item" data-value="Malay"><i class="my flag"></i>Malay</div>
		  <div class="item" data-value="Mongolian"><i class="mn flag"></i>Mongolian</div>
		  <div class="item" data-value="Nepali"><i class="np flag"></i>Nepali</div>
		  <div class="item" data-value="Norwegian"><i class="no flag"></i>Norwegian</div>
		  <div class="item" data-value="Polish"><i class="pl flag"></i>Polish</div>
		  <div class="item" data-value="Portuguese"><i class="pt flag"></i>Portuguese</div>
		  <div class="item" data-value="Romanian"><i class="ro flag"></i>Romanian</div>
		  <div class="item" data-value="Russian"><i class="ru flag"></i>Russian</div>
		  <div class="item" data-value="Serbia"><i class="cs flag"></i>Serbia</div>
		  <div class="item" data-value="Slovak"><i class="sk flag"></i>Slovak</div>
		  <div class="item" data-value="Slovenian"><i class="si flag"></i>Slovenian</div>
		  <div class="item" data-value="Spanish"><i class="es flag"></i>Spanish</div>
		  <div class="item" data-value="Swedish"><i class="se flag"></i>Swedish</div>
		  <div class="item" data-value="Thai"><i class="th flag"></i>Thai</div>
		  <div class="item" data-value="Turkish"><i class="tr flag"></i>Turkish</div>
		  <div class="item" data-value="Ukrainian"><i class="ua flag"></i>Ukrainian</div>
		  <div class="item" data-value="Uzbek"><i class="uz flag"></i>Uzbek</div>
		  <div class="item" data-value="Vietnamese"><i class="vn flag"></i>Vietnamese</div>
		</div>
		 </div>
		<?php	
	}

	
		
	public function render_settings_page_content() {
		?>

		<!-- Admin Menu Page Content -->
		<form id="b2bking_admin_form" method="POST" action="options.php">
			<?php settings_fields('b2bking'); ?>
			<?php do_settings_fields( 'b2bking', 'b2bking_hiddensettings' ); ?>

			<div id="b2bking_admin_wrapper" >

				<!-- Admin Menu Tabs --> 
				<div id="b2bking_admin_menu" class="ui labeled stackable large vertical menu attached">
					<img id="b2bking_menu_logo" src="<?php echo plugins_url('../includes/assets/images/logo.png', __FILE__); ?>">
					<a class="green item <?php echo $this->b2bking_isactivetab('mainsettings'); ?>" data-tab="mainsettings">
						<i class="power off icon"></i>
						<div class="header"><?php esc_html_e('Main Settings','b2bking'); ?></div>
						<span class="b2bking_menu_description"><?php esc_html_e('Primary plugin settings','b2bking'); ?></span>
					</a>
					<a class="green item <?php echo $this->b2bking_isactivetab('accessrestriction'); ?>" data-tab="accessrestriction">
						<i class="lock icon"></i>
						<div class="header"><?php esc_html_e('Access Restriction','b2bking'); ?></div>
						<span class="b2bking_menu_description"><?php esc_html_e('Hide pricing & products','b2bking'); ?></span>
					</a>
					<a class="green item <?php echo $this->b2bking_isactivetab('registration'); ?>" data-tab="registration">
						<i class="users icon"></i>
						<div class="header"><?php esc_html_e('Registration','b2bking'); ?></div>
						<span class="b2bking_menu_description"><?php esc_html_e('Registration settings','b2bking'); ?></span>
					</a>
					<a class="green item <?php echo $this->b2bking_isactivetab('bulkorderform'); ?>" data-tab="bulkorderform">
						<i class="th list icon"></i>
						<div class="header"><?php esc_html_e('Bulk Order Form','b2bking'); ?></div>
						<span class="b2bking_menu_description"><?php esc_html_e('Order form settings','b2bking'); ?></span>
					</a>
					<a class="green item <?php echo $this->b2bking_isactivetab('tieredpricing'); ?>" data-tab="tieredpricing">
						<i class="table icon"></i>
						<div class="header"><?php esc_html_e('Tiered Pricing & Table','b2bking'); ?></div>
						<span class="b2bking_menu_description"><?php esc_html_e('Tiered price settings','b2bking'); ?></span>
					</a>
					<a class="green item <?php echo $this->b2bking_isactivetab('language'); ?>" data-tab="language">
						<i class="language icon"></i>
						<div class="header"><?php esc_html_e('Language and Text','b2bking'); ?></div>
						<span class="b2bking_menu_description"><?php esc_html_e('Strings & language settings','b2bking'); ?></span>
					</a>
					<?php
					do_action('b2bking_settings_panel_end_items');
					?>
					<a class="green item b2bking_othersettings_margin <?php echo $this->b2bking_isactivetab('othersettings'); ?>" data-tab="othersettings">
						<i class="cog icon"></i>
						<div class="header"><?php esc_html_e('Other & Advanced Settings','b2bking'); ?></div>
						<span class="b2bking_menu_description"><?php esc_html_e('Miscellaneous settings','b2bking'); ?></span>
					</a>

					<!-- <a class="green item b2bking_license <?php // echo $this->b2bking_isactivetab('license'); ?>" data-tab="license">
						<i class="key icon"></i>
						<div class="header"><?php // esc_html_e('License','b2bking'); ?></div>
						<span class="b2bking_menu_description"><?php // esc_html_e('Manage plugin license','b2bking'); ?></span>
					</a> -->

				
				</div>
			
				<!-- Admin Menu Tabs Content--> 
				<div id="b2bking_tabs_wrapper">

					<!-- Main Settings Tab--> 
					<div class="ui bottom attached tab segment <?php echo $this->b2bking_isactivetab('mainsettings'); ?>" data-tab="mainsettings">
						<div class="b2bking_attached_content_wrapper">
							<h2 class="ui block header">
								<i class="power off icon"></i>
								<div class="content">
									<?php esc_html_e('Set Plugin Status','b2bking'); ?>
									<div class="sub header">
										<?php esc_html_e('Turn plugin on and off','b2bking'); ?>
									</div>
								</div>
							</h2>
							<table class="form-table">
								<div class="ui info message">
								  <i class="close icon"></i>
								  <div class="header"> <i class="question circle icon"></i>
								  	<?php esc_html_e('Documentation','b2bking'); ?>
								  </div>
								  <ul class="list">
								    <li><a href="https://woocommerce-b2b-plugin.com/docs/plugin-status/"><?php esc_html_e('"Plugin Status" options explained','b2bking'); ?></a></li>
								  </ul>
								</div>
								<div class="ui large form b2bking_plugin_status_container">
								  <div class="inline fields">
								    <label><?php esc_html_e('Plugin Status','b2bking'); ?></label>
								    <div class="field">
								      <div class="ui checkbox">
								        <input type="radio" tabindex="0" class="hidden" name="b2bking_plugin_status_setting" value="hybrid" <?php checked('hybrid',get_option( 'b2bking_plugin_status_setting', 'b2b' ), true); ?>">
								        <label><i class="shopping basket icon"></i>&nbsp;<?php esc_html_e('B2B & B2C Hybrid','b2bking'); ?>&nbsp;&nbsp;<span class="b2bking_settings_explained"><?php esc_html_e('(Plugin active only for B2B users)','b2bking'); ?></span></label>
								      </div>
								    </div>
								    <div class="field">
								      <div class="ui checkbox">
								        <input type="radio" tabindex="0" class="hidden" name="b2bking_plugin_status_setting" value="b2b" <?php checked('b2b',get_option( 'b2bking_plugin_status_setting', 'b2b' ), true); ?>">
								        <label><i class="dolly icon"></i>&nbsp;<?php esc_html_e('B2B Shop','b2bking'); ?>&nbsp;&nbsp;<span class="b2bking_settings_explained"><?php esc_html_e('(Plugin active for all users)','b2bking'); ?></span></label>
								      </div>
								    </div>
								    
								  </div>
								</div>
							</table>
							<h3 class="ui block header">
								<i class="plug icon"></i>
								<?php esc_html_e('Enable / Disable Features','b2bking'); ?>
							</h3>
							<table class="form-table">
								<?php do_settings_fields( 'b2bking', 'b2bking_enable_features_settings_section' ); ?>
							</table>
					
							
						</div>
					</div>
					
					<!-- Access Restriction Tab--> 
					<div class="ui bottom attached tab segment <?php echo $this->b2bking_isactivetab('accessrestriction'); ?>" data-tab="accessrestriction">
						<div class="b2bking_attached_content_wrapper">
							<h2 class="ui block header">
								<i class="eye slash icon"></i>
								<div class="content">
									<?php esc_html_e('Access Restriction','b2bking'); ?>
									<div class="sub header">
										<?php esc_html_e('Hide prices, products and functionalities','b2bking'); ?>
									</div>
								</div>
							</h2>
							<div class="ui info message">
							  <i class="close icon"></i>
							  <div class="header"> <i class="question circle icon"></i>
							  	<?php esc_html_e('Documentation','b2bking'); ?>
							  </div>
							  <ul class="list">
							    <li><a href="https://woocommerce-b2b-plugin.com/docs/guest-access-restriction-hide-prices-hide-the-website-replace-prices-with-quote-request/"><?php esc_html_e('Guest Access Restriction - functionality explained','b2bking'); ?></a></li>
							  </ul>
							</div>

							<table class="form-table">
								<div class="ui large form b2bking_plugin_status_container">
									<label class="b2bking_access_restriction_label"><?php esc_html_e('Guest Access Restriction','b2bking'); ?></label>

								  <div class="inline fields">
								    <div class="field">
								      <div class="ui checkbox">
								        <input type="radio" tabindex="0" class="hidden" name="b2bking_guest_access_restriction_setting" value="none" <?php checked('none', get_option( 'b2bking_guest_access_restriction_setting', 'hide_prices' ), true); ?>">
								        <label><?php esc_html_e('None','b2bking'); ?></label>
								      </div>
								    </div>
								    <div class="field">
								      <div class="ui checkbox">
								        <input type="radio" tabindex="0" class="hidden" name="b2bking_guest_access_restriction_setting" value="hide_prices" <?php checked('hide_prices', get_option( 'b2bking_guest_access_restriction_setting', 'hide_prices' ), true); ?>">
								        <label><i class="euro sign icon"></i><?php esc_html_e('Hide prices','b2bking'); ?></label>
								      </div>
								    </div>
								    <div class="field">
								      <div class="ui checkbox">
								        <input type="radio" tabindex="0" class="hidden" name="b2bking_guest_access_restriction_setting" value="hide_website" <?php checked('hide_website', get_option( 'b2bking_guest_access_restriction_setting', 'hide_prices' ), true); ?>">
								        <label><i class="building outline icon"></i><?php esc_html_e('Hide shop & products','b2bking'); ?></label>
								      </div>
								    </div>
								    <div class="field">
								      <div class="ui checkbox">
								        <input type="radio" tabindex="0" class="hidden" name="b2bking_guest_access_restriction_setting" value="hide_website_completely" <?php checked('hide_website_completely', get_option( 'b2bking_guest_access_restriction_setting', 'hide_prices' ), true); ?>">
								        <label><i class="lock icon"></i><?php esc_html_e('Hide website / force login','b2bking'); ?></label>
								      </div>
								    </div>
								    <div class="field">
								      <div class="ui checkbox">
								        <input type="radio" tabindex="0" class="hidden" name="b2bking_guest_access_restriction_setting" value="replace_prices_quote" <?php checked('replace_prices_quote', get_option( 'b2bking_guest_access_restriction_setting', 'hide_prices' ), true); ?>">
								        <label><i class="clipboard outline icon"></i><?php esc_html_e('Replace prices with "Request a Quote"','b2bking'); ?></label>
								      </div>
								    </div>
								    
								  </div>

								</div>
							</table>
							<table class="form-table" id="b2bking_access_restriction_force_redirect">
								<?php do_settings_fields( 'b2bking', 'b2bking_access_restriction_settings_force_section' ); ?>
							</table>
							<table class="form-table">
								<h3 class="ui block header">
									<i class="eye icon"></i>
									<?php esc_html_e('Product & Category Visibility Settings','b2bking'); ?>
								</h3>
								<?php do_settings_fields( 'b2bking', 'b2bking_access_restriction_settings_section' ); ?>
							</table>

							<h3 class="ui block header">
								<i class="wrench icon"></i>
								<?php esc_html_e('Advanced Visibility Settings','b2bking'); ?>
							</h3>

							<div class="ui info message">
							  <i class="close icon"></i>
							  <div class="header"> <i class="question circle icon"></i>
							  	<?php esc_html_e('Documentation','b2bking'); ?>
							  </div>
							  <ul class="list">
							    <li><a href="https://woocommerce-b2b-plugin.com/docs/advanced-visibility-settings-explained/"><?php esc_html_e('Advanced visibility settings -  explained','b2bking'); ?></a></li>
							  </ul>
							</div>
						
							<table class="form-table">
								<?php do_settings_fields( 'b2bking', 'b2bking_access_restriction_category_settings_section' ); ?>
							</table>


							
						</div>
					</div>

					<!-- Registration Tab--> 
					<div class="ui bottom attached tab segment <?php echo $this->b2bking_isactivetab('registration'); ?>" data-tab="registration">
						<div class="b2bking_attached_content_wrapper">
							<h2 class="ui block header">
								<i class="users icon"></i>
								<div class="content">
									<?php esc_html_e('Registration','b2bking'); ?>
									<div class="sub header">
										<?php esc_html_e('User registration settings','b2bking'); ?>
									</div>
								</div>
							</h2>
							<table class="form-table">
								<div class="ui info message">
								  <i class="close icon"></i>
								  <div class="header"> <i class="question circle icon"></i>
								  	<?php esc_html_e('Documentation','b2bking'); ?>
								  </div>
								  <ul class="list">
								    <li><a href="https://woocommerce-b2b-plugin.com/docs/extended-registration-and-custom-fields/"><?php esc_html_e('Extended Registration and Custom Fields -  explained','b2bking'); ?></a></li>
								    <li><a href="https://woocommerce-b2b-plugin.com/docs/how-to-completely-separate-b2b-and-b2c-registration-in-woocommerce-with-b2bking/"><?php esc_html_e('How to completely separate B2B and B2C registration','b2bking'); ?></a></li>
								  </ul>
								</div>
							
								<?php do_settings_fields( 'b2bking', 'b2bking_registration_settings_section' ); ?>
							</table>

							<table class="form-table">
								<h3 class="ui block header">
									<i class="wrench icon"></i>
									<?php esc_html_e('Advanced Registration Settings','b2bking'); ?>
								</h3>
								<?php do_settings_fields( 'b2bking', 'b2bking_registration_settings_section_advanced' ); ?>
							</table>

						</div>
					</div>

					<!-- Bulk Order Form Tab--> 
					<div class="ui bottom attached tab segment <?php echo $this->b2bking_isactivetab('bulkorderform'); ?>" data-tab="bulkorderform">
						<div class="b2bking_attached_content_wrapper">
							<h2 class="ui block header">
								<i class="th list icon"></i>
								<div class="content">
									<?php esc_html_e('Bulk Order Form','b2bking'); ?>
									<div class="sub header">
										<?php esc_html_e('Order form settings',' 2bking'); ?>
									</div>
								</div>
							</h2>
							<div class="ui info message">
							  <i class="close icon"></i>
							  <div class="header"> <i class="question circle icon"></i>
							  	<?php esc_html_e('Documentation','b2bking'); ?>
							  </div>
							  <ul class="list">
							    <li><a href="https://woocommerce-b2b-plugin.com/docs/order-form-themes-styling/"><?php esc_html_e('Order Form Themes & Styling','b2bking'); ?></a></li>
							  </ul>
							</div>
							<table class="form-table b2bking_bulkorder_section_settings">
								<?php do_settings_fields( 'b2bking', 'b2bking_othersettings_bulkorderform_section' ); ?>
							</table>

						</div>
					</div>

					<!-- Tiered Pricing Tab--> 
					<div class="ui bottom attached tab segment <?php echo $this->b2bking_isactivetab('tieredpricing'); ?>" data-tab="tieredpricing">
						<div class="b2bking_attached_content_wrapper">
							<h2 class="ui block header">
								<i class="table icon"></i>
								<div class="content">
									<?php esc_html_e('Tiered Pricing & Table','b2bking'); ?>
									<div class="sub header">
										<?php esc_html_e('Tiered price settings',' 2bking'); ?>
									</div>
								</div>
							</h2>
							<table class="form-table">
								<?php do_settings_fields( 'b2bking', 'b2bking_othersettings_tieredpricing_section' ); ?>

							</table>

						</div>
					</div>

					<!-- Language Tab--> 
					<div class="ui bottom attached tab segment <?php echo $this->b2bking_isactivetab('language'); ?>" data-tab="language">
						<div class="b2bking_attached_content_wrapper">
							<h2 class="ui block header">
								<i class="language icon"></i>
								<div class="content">
									<?php esc_html_e('Language and Text','b2bking'); ?>
									<div class="sub header">
										<?php esc_html_e('Change text and translate B2BKing','b2bking'); ?>
									</div>
								</div>

							</h2>
							<table class="form-table">
								<div class="ui info message">
								  <i class="close icon"></i>
								  <div class="header"> <i class="question circle icon"></i>
								  	<?php esc_html_e('Documentation','b2bking'); ?>
								  </div>
								  <ul class="list">
								    <li><a href="https://woocommerce-b2b-plugin.com/docs/how-to-translate-b2bking-to-any-language-localization"><?php esc_html_e('How to translate B2BKing to any language (Localization)','b2bking'); ?></a></li>
								  </ul>
								</div>
								<h3 class="ui block header">
									<i class="edit outline icon"></i>
									<?php esc_html_e('Text Settings','b2bking'); ?>
								</h3>
								<table class="form-table">
									<?php do_settings_fields( 'b2bking', 'b2bking_languagesettings_text_section' ); ?>
								</table>
								<h3 class="ui block header">
									<i class="list alternate icon"></i>
									<?php esc_html_e('Purchase Lists Language','b2bking'); ?>
								</h3>
								<table class="form-table">
									<?php do_settings_fields( 'b2bking', 'b2bking_languagesettings_purchaselists_section' ); ?>
								</table>
							</table>
							
						</div>
					</div>

					<!-- License Tab--> 
					<div class="ui bottom attached tab segment <?php echo $this->b2bking_isactivetab('license'); ?>" data-tab="license">
						<div class="b2bking_attached_content_wrapper">
							<h2 class="ui block header">
								<i class="key icon"></i>
								<div class="content">
									<?php esc_html_e('License management','b2bking'); ?>
									<div class="sub header">
										<?php esc_html_e('Activate the plugin','b2bking'); ?>
									</div>
								</div>
							</h2>
							<table class="form-table">
								<table class="form-table">

								</table>
							</table>
							
						</div>
					</div>


					<?php

						do_action('b2bking_settings_panel_end_items_tabs');

					?>

					<!-- Other settings tab--> 
					<div class="ui bottom attached tab segment <?php echo $this->b2bking_isactivetab('othersettings'); ?>" data-tab="othersettings">
						<div class="b2bking_attached_content_wrapper">
							<h2 class="ui block header">
								<i class="cog icon"></i>
								<div class="content">
									<?php esc_html_e('Other settings','b2bking'); ?>
									<div class="sub header">
										<?php esc_html_e('Miscellaneous settings','b2bking'); ?>
									</div>
								</div>
							</h2>
							<h3 class="ui block header">
								<i class="paint brush icon"></i>
								<?php esc_html_e('Color & Design','b2bking'); ?>
							</h3>
							<table class="form-table">
								<?php do_settings_fields( 'b2bking', 'b2bking_othersettings_colordesign_section' ); ?>

								<!-- PURCHASE LISTS -->
								<tr>
									<th scope="row"><?php esc_html_e('Purchase Lists Header','b2bking');?></th>
									<td><input name="b2bking_purchase_lists_color_header_setting" type="color" value="<?php echo esc_attr( get_option( 'b2bking_purchase_lists_color_header_setting', '#353042' ) ); ?>"></td>
									<td class="b2bking_settings_row_td"><span class="b2bking_settings_row_label"><?php esc_html_e('Lists Action Buttons','b2bking');?></span><input name="b2bking_purchase_lists_color_action_buttons_setting" type="color" value="<?php echo esc_attr( get_option( 'b2bking_purchase_lists_color_action_buttons_setting', '#b1b1b1' ) ); ?>"></td>
									<td class="b2bking_settings_row_td"><span class="b2bking_settings_row_label"><?php esc_html_e('New List Button','b2bking');?></span><input name="b2bking_purchase_lists_color_new_list_setting" type="color" value="<?php echo esc_attr( get_option( 'b2bking_purchase_lists_color_new_list_setting', '#353042' ) ); ?>"></td>
								</tr>
							</table>
							<h3 class="ui block header">
								<i class="clipboard list icon"></i>
								<?php esc_html_e('Quote Requests','b2bking'); ?>
							</h3>
							<table class="form-table">
								<div class="ui info message">
								  <i class="close icon"></i>
								  <div class="header"> <i class="question circle icon"></i>
								  	<?php esc_html_e('Documentation','b2bking'); ?>
								  </div>
								  <ul class="list">
								    <li><a href="https://woocommerce-b2b-plugin.com/docs/request-a-custom-quote-button-in-cart-explained/"><?php esc_html_e('"Request a Custom Quote" button in detail','b2bking'); ?></a></li>
								  </ul>
								</div>
								<div class="ui large form b2bking_plugin_status_container">
								  <div class="inline fields">
								    <label><?php esc_html_e('"Request a Custom Quote" button in Cart','b2bking'); ?></label>
								    <div class="field">
								      <div class="ui checkbox">
								        <input type="radio" tabindex="0" class="hidden" name="b2bking_quote_button_cart_setting" value="disabled" <?php checked('disabled',get_option( 'b2bking_quote_button_cart_setting', 'enableb2b' ), true); ?>">
								        <label><?php esc_html_e('Disabled','b2bking'); ?></label>
								      </div>
								    </div>
								    <div class="field">
								      <div class="ui checkbox">
								        <input type="radio" tabindex="0" class="hidden" name="b2bking_quote_button_cart_setting" value="enableb2b" <?php checked('enableb2b',get_option( 'b2bking_quote_button_cart_setting', 'enableb2b' ), true); ?>">
								        <label><?php esc_html_e('Enabled for B2B','b2bking'); ?></label>
								      </div>
								    </div>
								    <div class="field">
								      <div class="ui checkbox">
								        <input type="radio" tabindex="0" class="hidden" name="b2bking_quote_button_cart_setting" value="enableb2c" <?php checked('enableb2c',get_option( 'b2bking_quote_button_cart_setting', 'enableb2b' ), true); ?>">
								        <label><?php esc_html_e('Enabled for Guests + B2C','b2bking'); ?></label>
								      </div>
								    </div>
								    <div class="field">
								      <div class="ui checkbox">
								        <input type="radio" tabindex="0" class="hidden" name="b2bking_quote_button_cart_setting" value="enableall" <?php checked('enableall',get_option( 'b2bking_quote_button_cart_setting', 'enableb2b' ), true); ?>">
								        <label><?php esc_html_e('Enabled for ALL','b2bking'); ?></label>
								      </div>
								    </div>
								    
								  </div>
								</div>
								<!-- BUTTON QUOTE FIELDS -->
								<a href="<?php echo esc_attr(admin_url('/edit.php?post_type=b2bking_quote_field'));?>">
									<button type="button" name="b2bking-quote-fields" id="b2bking-quote-fields" class="ui blue button">
										<i class="th list icon"></i>
										<?php esc_html_e('Manage Quote Form Fields', 'b2bking'); ?>
									</button>
								</a>
							</table>
							<h3 class="ui block header">
								<i class="box icon"></i>
								<?php esc_html_e('Offers','b2bking'); ?>
							</h3>
							<table class="form-table">
								<div class="ui info message">
								  <i class="close icon"></i>
								  <div class="header"> <i class="question circle icon"></i>
								  	<?php esc_html_e('Documentation','b2bking'); ?>
								  </div>
								  <ul class="list">
								    <li><a href="https://woocommerce-b2b-plugin.com/docs/offers/"><?php esc_html_e('Offers - feature in detail','b2bking'); ?></a></li>
								  
								</div>
								<?php do_settings_fields( 'b2bking', 'b2bking_offers_settings_section' ); ?>
							</table>
							<h3 class="ui block header">
								<i class="laptop icon"></i>
								<?php esc_html_e('Price and Product Display','b2bking'); ?>
							</h3>
							<table class="form-table">
								<?php do_settings_fields( 'b2bking', 'b2bking_othersettings_priceproductdisplay_section' ); ?>
							</table>
							<h3 class="ui block header">
								<i class="linkify icon"></i>
								<?php esc_html_e('Permalinks','b2bking'); ?>
							</h3>
							<table class="form-table">
								<?php do_settings_fields( 'b2bking', 'b2bking_othersettings_permalinks_section' ); ?>
							</table>
							<h3 class="ui block header">
								<i class="sitemap icon"></i>
								<?php esc_html_e('Multisite','b2bking'); ?>
							</h3>
							<table class="form-table">
								<?php do_settings_fields( 'b2bking', 'b2bking_othersettings_multisite_section' ); ?>
							</table>
							<h3 class="ui block header">
								<i class="shopping basket icon"></i>
								<?php esc_html_e('Large Stores','b2bking'); ?>
							</h3>
							<table class="form-table">
								<?php do_settings_fields( 'b2bking', 'b2bking_othersettings_largestores_section' ); ?>
							</table>
							<h3 class="ui block header">
								<i class="sliders horizontal icon"></i>
								<?php esc_html_e('VAT Validation','b2bking'); ?>
							</h3>
							<table class="form-table">
								<?php do_settings_fields( 'b2bking', 'b2bking_othersettings_vat_section' ); ?>
							</table>
							<h3 class="ui block header">
								<i class="rocket icon"></i>
								<?php esc_html_e('Caching','b2bking'); ?>
							</h3>
							<table class="form-table">
								<?php do_settings_fields( 'b2bking', 'b2bking_othersettings_caching_section' ); ?>
							</table>

							<h3 class="ui block header">
								<i class="warehouse icon"></i>
								<?php esc_html_e('Stock','b2bking'); ?>
							</h3>
							<table class="form-table">
								<?php do_settings_fields( 'b2bking', 'b2bking_othersettings_stock_section' ); ?>
							</table>

							<h3 class="ui block header">
								<i class="id badge icon"></i>
								<?php esc_html_e('Subaccounts & Company','b2bking'); ?>
							</h3>
							<table class="form-table">
								<?php do_settings_fields( 'b2bking', 'b2bking_othersettings_company_section' ); ?>
							</table>

							<h3 class="ui block header">
								<i class="object group icon"></i>
								<?php esc_html_e('Compatibility','b2bking'); ?>
							</h3>
							<table class="form-table">
								<?php do_settings_fields( 'b2bking', 'b2bking_othersettings_compatibility_section' ); ?>
							</table>

							<!-- ACCORDIONS -->
							<h3 class="ui block header">
								<i class="cubes icon"></i>
								<?php esc_html_e('Advanced: Endpoints & Components','b2bking'); ?>
							</h3>
								<div class="ui styled accordion b2bking_accordion">

									<!-- ENDPOINTS -->
									<div class="title">
										<i class="dropdown icon"></i>
									  	<?php esc_html_e('Endpoints', 'b2bking'); ?>
									</div>
									<div class="content">
									  	<h2 class="ui block header">
									  		<i class="window maximize outline icon"></i>
									  		<?php esc_html_e('Endpoints','b2bking'); ?>
									  	</h2>
									  	<table class="form-table">
									  		
									  		<?php  do_settings_fields( 'b2bking', 'b2bking_othersettings_endpoints_section' ); ?>
									  			
									  	</table>
									</div>

									<!-- COMPONENTS -->
							        <div class="title">
							        	<i class="dropdown icon"></i>
							          	<?php esc_html_e('Components', 'b2bking'); ?>
							        </div>
							        <div class="content">
							          	<h2 class="ui block header">
							          		<i class="cubes icon"></i>
							          		<div class="content">
							          			<?php esc_html_e('Components Settings','b2bking'); ?>
							          			<div class="sub header">
							          				<?php esc_html_e('Disable individual plugin components','b2bking'); ?>
							          			</div>
							          		</div>
							          	</h2>
							          	<table class="form-table">
							          		<div class="ui info message">
							          		  <i class="close icon"></i>
							          		  <div class="header">
							          		  	<?php esc_html_e('Functionality Explained','b2bking'); ?>
							          		  </div>
							          		  <ul class="list">
							          		    <?php esc_html_e('Disabling individual plugin components may help you troubleshoot issues, prevent plugin conflicts, or in edge cases improve performance. ','b2bking');?>
							          		  </ul>
							          		</div>
							          		<?php  do_settings_fields( 'b2bking', 'b2bking_performance_settings_section' ); ?>
							          			
							          	</table>
							        </div>
							    </div>


								
								



							<h3 class="ui block header">
								<i class="trash icon"></i>
								<?php esc_html_e('Uninstall','b2bking'); ?>
							</h3>
							<table class="form-table">
								<?php do_settings_fields( 'b2bking', 'b2bking_othersettings_section' ); ?>
							</table>
							
					
						</div>
					</div>
				</div>
			</div>

			<br>
			<input type="submit" name="submit" id="b2bking-admin-submit" class="ui primary button" value="Save Settings">
		</form>

		<?php
	}

	function b2bking_isactivetab($tab){
		$gototab = get_option( 'b2bking_current_tab_setting', 'accessrestriction' );
		if ($tab === $gototab){
			return 'active';
		} 
	}

}