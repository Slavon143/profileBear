<?php

class B2bking_Activator {

	public static function activate() {

		// clear b2bking transients
		b2bking()->clear_caches_transients();

		// prevent option update issues due to caching
		wp_cache_delete ( 'alloptions', 'options' );
		

		// Trigger post types and endpoints functions
		require_once ( B2BKING_DIR . 'admin/class-b2bking-admin.php' );
		require_once ( B2BKING_DIR . 'public/class-b2bking-public.php' );
		$adminobj = new B2bking_Admin;
		$publicobj = new B2bking_Public;
		$adminobj->b2bking_register_post_type_customer_groups();
		$adminobj->b2bking_register_post_type_conversation();
		$adminobj->b2bking_register_post_type_offer();
		$adminobj->b2bking_register_post_type_dynamic_rules();
		$adminobj->b2bking_register_post_type_custom_role();
		$adminobj->b2bking_register_post_type_custom_field();
		$publicobj->b2bking_custom_endpoints();
		
		// Flush rewrite rules
		if (apply_filters('b2bking_flush_permalinks', true)){
			// Flush rewrite rules
			flush_rewrite_rules();
		}

		// Set admin notice state to enabled ('activate woocommerce' notice)
		update_user_meta(get_current_user_id(), 'b2bking_dismiss_activate_woocommerce_notice', 0);

		// Create B2B offer product if it doesn't exist
		if (apply_filters('b2bking_use_offer_and_credit_products', true)){
			$offer_id = intval(get_option('b2bking_offer_product_id_setting', 0));
			if ( !get_post_status ( $offer_id ) ) {
				$offer = array(
				    'post_title' => 'Offer',
				    'post_status' => 'publish',
				    'post_type' => 'product',
				    'post_author' => 1,
				);
				$product_id = wp_insert_post($offer);
				//Set product hidden: 
				$terms = array( 'exclude-from-catalog', 'exclude-from-search' );
				wp_set_object_terms( $product_id, $terms, 'product_visibility' );
				wp_set_object_terms( $product_id, 'simple', 'product_type' );
				update_post_meta( $product_id, '_visibility', 'hidden' );
				update_post_meta( $product_id, '_stock_status', 'instock');
				update_post_meta( $product_id, '_regular_price', '' );
				update_post_meta( $product_id, '_sale_price', '' );
				update_post_meta( $product_id, '_purchase_note', '' );
				update_post_meta( $product_id, '_product_attributes', array() );
				update_post_meta( $product_id, '_sale_price_dates_from', '' );
				update_post_meta( $product_id, '_sale_price_dates_to', '' );
				update_post_meta( $product_id, '_price', '1' );
				update_post_meta( $product_id, '_sold_individually', '' );

				// set option to product id
				update_option( 'b2bking_offer_product_id_setting', intval($product_id) );
			}

			// Create B2B credit product if it doesn't exist
			$credit_id = intval(get_option('b2bking_credit_product_id_setting', 0));
			if ( !get_post_status ( $credit_id ) ) {
				$credit_id = array(
				    'post_title' => 'Credit',
				    'post_status' => 'publish',
				    'post_type' => 'product',
				    'post_author' => 1,
				);
				$product_id = wp_insert_post($credit_id);
				//Set product hidden: 
				$terms = array( 'exclude-from-catalog', 'exclude-from-search' );
				wp_set_object_terms( $product_id, $terms, 'product_visibility' );
				wp_set_object_terms( $product_id, 'simple', 'product_type' );
				update_post_meta( $product_id, '_visibility', 'hidden' );
				update_post_meta( $product_id, '_stock_status', 'instock');
				update_post_meta( $product_id, '_regular_price', '' );
				update_post_meta( $product_id, '_sale_price', '' );
				update_post_meta( $product_id, '_purchase_note', '' );
				update_post_meta( $product_id, '_product_attributes', array() );
				update_post_meta( $product_id, '_sale_price_dates_from', '' );
				update_post_meta( $product_id, '_sale_price_dates_to', '' );
				update_post_meta( $product_id, '_price', '1' );
				update_post_meta( $product_id, '_sold_individually', '' );
				update_post_meta( $product_id, '_tax_status', 'none' );
				update_post_meta( $product_id, '_tax_class', 'zero-rate' );

				// set option to product id
				update_option( 'b2bking_credit_product_id_setting', intval($product_id) );
			}
		}

		
		
		// Create 2 default user roles if they don't exist: Individual Customer and B2B

		if ( !get_post_status ( '4225466' ) ) { // Deprecated, don't affect existing customers
			$individual_role_id = intval(get_option('b2bking_individual_role_id_setting', 0));
			// if role does not exist, = 0
			if (intval($individual_role_id === 0)){
				$role = array(
				    'post_title' => sanitize_text_field(esc_html__('Individual Customer','b2bking')),
				    'post_status' => 'publish',
				    'post_type' => 'b2bking_custom_role',
				    'post_author' => 1,
				);
				$role_id = wp_insert_post($role);
				// set role status as enabled
				update_post_meta($role_id, 'b2bking_custom_role_status', 1);
				// set role approval as automatic
				update_post_meta( $role_id, 'b2bking_custom_role_approval', 'automatic');
				update_post_meta( $role_id, 'b2bking_custom_role_sort_number', 1);

				// set option 
				update_option( 'b2bking_individual_role_id_setting', intval($role_id) );
			}

		}
		if ( !get_post_status ( '4225467' ) ) { // Deprecated, don't affect existing customers
			$b2b_role_id = intval(get_option('b2bking_b2b_role_id_setting', 0));
			// if role does not exist, = 0
			if (intval($b2b_role_id === 0)){
				$role = array(
				    'post_title' => sanitize_text_field(esc_html__('B2B (requires approval)','b2bking')),
				    'post_status' => 'publish',
				    'post_type' => 'b2bking_custom_role',
				    'post_author' => 1,
				);
				$role_id = wp_insert_post($role);
				// set role status as enabled
				update_post_meta($role_id, 'b2bking_custom_role_status', 1);
				// set role approval as manual
				update_post_meta($role_id, 'b2bking_custom_role_approval', 'manual');
				update_post_meta( $role_id, 'b2bking_custom_role_sort_number', 2);

				// set option 
				update_option( 'b2bking_b2b_role_id_setting', intval($role_id) );
			}
		}

		// Create 11 default B2B fields
		if ( !get_post_status ( '4225468' ) ) { // Deprecated, don't affect existing customers

			$first_name_initial_field_id = intval(get_option('b2bking_first_name_initial_field_id_setting', 0));
			// if field does not exist, = 0
			if (intval($first_name_initial_field_id === 0)){
				$field = array(
				    'post_title' => sanitize_text_field(esc_html__('First Name','b2bking')),
				    'post_status' => 'publish',
				    'post_type' => 'b2bking_custom_field',
				    'post_author' => 1,
				);
				$field_id = wp_insert_post($field);

				$b2b_role_id = '4225467';
				if ( !get_post_status ( '4225467' ) ) { // deprecated, don't affect existing customers
					$b2b_role_id = intval(get_option('b2bking_b2b_role_id_setting', 0));
				}
				// set field meta
				update_post_meta($field_id, 'b2bking_custom_field_status', 1);
				update_post_meta($field_id, 'b2bking_custom_field_required', 1);
				update_post_meta($field_id, 'b2bking_custom_field_sort_number', 1);
				update_post_meta($field_id, 'b2bking_custom_field_registration_role', 'role_'.$b2b_role_id);
				update_post_meta($field_id, 'b2bking_custom_field_editable', 1);
				update_post_meta($field_id, 'b2bking_custom_field_field_type', 'text');
				update_post_meta($field_id, 'b2bking_custom_field_field_label', sanitize_text_field(esc_html__('First Name','b2bking')));
				update_post_meta($field_id, 'b2bking_custom_field_field_placeholder', sanitize_text_field(esc_html__('Enter your first name here...','b2bking')));
				update_post_meta($field_id, 'b2bking_custom_field_billing_connection', 'billing_first_name');

				// set option 
				update_option( 'b2bking_first_name_initial_field_id_setting', intval($field_id) );
			}
		}

		if ( !get_post_status ( '4225469' ) ) { // Deprecated, don't affect existing customers

			$last_name_initial_field_id = intval(get_option('b2bking_last_name_initial_field_id_setting', 0));
			// if field does not exist, = 0
			if (intval($last_name_initial_field_id === 0)){

				$field = array(
				    'post_title' => sanitize_text_field(esc_html__('Last Name','b2bking')),
				    'post_status' => 'publish',
				    'post_type' => 'b2bking_custom_field',
				    'post_author' => 1,
				);
				$field_id = wp_insert_post($field);

				$b2b_role_id = '4225467';
				if ( !get_post_status ( '4225467' ) ) { // deprecated, don't affect existing customers
					$b2b_role_id = intval(get_option('b2bking_b2b_role_id_setting', 0));
				}
				// set field meta
				update_post_meta($field_id, 'b2bking_custom_field_status', 1);
				update_post_meta($field_id, 'b2bking_custom_field_required', 1);
				update_post_meta($field_id, 'b2bking_custom_field_sort_number', 2);
				update_post_meta($field_id, 'b2bking_custom_field_registration_role', 'role_'.$b2b_role_id);
				update_post_meta($field_id, 'b2bking_custom_field_editable', 1);
				update_post_meta($field_id, 'b2bking_custom_field_field_type', 'text');
				update_post_meta($field_id, 'b2bking_custom_field_field_label', sanitize_text_field(esc_html__('Last Name','b2bking')));
				update_post_meta($field_id, 'b2bking_custom_field_field_placeholder', sanitize_text_field(esc_html__('Enter your last name here...','b2bking')));
				update_post_meta($field_id, 'b2bking_custom_field_billing_connection', 'billing_last_name');

				// set option 
				update_option( 'b2bking_last_name_initial_field_id_setting', intval($field_id) );
			}
		}

		if ( !get_post_status ( '4225470' ) ) { // Deprecated, don't affect existing customers

			$company_name_initial_field_id = intval(get_option('b2bking_company_name_initial_field_id_setting', 0));
			// if field does not exist, = 0
			if (intval($company_name_initial_field_id === 0)){

				$field = array(
				    'post_title' => sanitize_text_field(esc_html__('Company Name','b2bking')),
				    'post_status' => 'publish',
				    'post_type' => 'b2bking_custom_field',
				    'post_author' => 1,
				);
				$field_id = wp_insert_post($field);

				$b2b_role_id = '4225467';
				if ( !get_post_status ( '4225467' ) ) { // deprecated, don't affect existing customers
					$b2b_role_id = intval(get_option('b2bking_b2b_role_id_setting', 0));
				}
				// set field meta
				update_post_meta($field_id, 'b2bking_custom_field_status', 1);
				update_post_meta($field_id, 'b2bking_custom_field_required', 0);
				update_post_meta($field_id, 'b2bking_custom_field_sort_number', 3);
				update_post_meta($field_id, 'b2bking_custom_field_registration_role', 'role_'.$b2b_role_id);
				update_post_meta($field_id, 'b2bking_custom_field_editable', 1);
				update_post_meta($field_id, 'b2bking_custom_field_field_type', 'text');
				update_post_meta($field_id, 'b2bking_custom_field_field_label', sanitize_text_field(esc_html__('Company Name','b2bking')));
				update_post_meta($field_id, 'b2bking_custom_field_field_placeholder', sanitize_text_field(esc_html__('Enter your company name here...','b2bking')));
				update_post_meta($field_id, 'b2bking_custom_field_billing_connection', 'billing_company');

				// set option 
				update_option( 'b2bking_company_name_initial_field_id_setting', intval($field_id) );
			}
		}

		if ( !get_post_status ( '4225471' ) ) { // Deprecated, don't affect existing customers

			$street_address_initial_field_id = intval(get_option('b2bking_street_address_initial_field_id_setting', 0));
			// if field does not exist, = 0
			if (intval($street_address_initial_field_id === 0)){

				$field = array(
				    'post_title' => sanitize_text_field(esc_html__('Street Address','b2bking')),
				    'post_status' => 'publish',
				    'post_type' => 'b2bking_custom_field',
				    'post_author' => 1,
				);
				$field_id = wp_insert_post($field);

				$b2b_role_id = '4225467';
				if ( !get_post_status ( '4225467' ) ) { // deprecated, don't affect existing customers
					$b2b_role_id = intval(get_option('b2bking_b2b_role_id_setting', 0));
				}
				// set field meta
				update_post_meta($field_id, 'b2bking_custom_field_status', 1);
				update_post_meta($field_id, 'b2bking_custom_field_required', 1);
				update_post_meta($field_id, 'b2bking_custom_field_sort_number', 4);
				update_post_meta($field_id, 'b2bking_custom_field_registration_role', 'role_'.$b2b_role_id);
				update_post_meta($field_id, 'b2bking_custom_field_editable', 1);
				update_post_meta($field_id, 'b2bking_custom_field_field_type', 'text');
				update_post_meta($field_id, 'b2bking_custom_field_field_label', sanitize_text_field(esc_html__('Street Address','b2bking')));
				update_post_meta($field_id, 'b2bking_custom_field_field_placeholder', sanitize_text_field(esc_html__('Enter your address here...','b2bking')));
				update_post_meta($field_id, 'b2bking_custom_field_billing_connection', 'billing_address_1');

				// set option 
				update_option( 'b2bking_street_address_initial_field_id_setting', intval($field_id) );
			}
		}

		if ( !get_post_status ( '4225472' ) ) { // Deprecated, don't affect existing customers

			$street_address_second_initial_field_id = intval(get_option('b2bking_street_address_second_initial_field_id_setting', 0));
			// if field does not exist, = 0
			if (intval($street_address_second_initial_field_id === 0)){

				$field = array(
				    'post_title' => sanitize_text_field(esc_html__('Address Line 2','b2bking')),
				    'post_status' => 'publish',
				    'post_type' => 'b2bking_custom_field',
				    'post_author' => 1,
				);
				$field_id = wp_insert_post($field);

				$b2b_role_id = '4225467';
				if ( !get_post_status ( '4225467' ) ) { // deprecated, don't affect existing customers
					$b2b_role_id = intval(get_option('b2bking_b2b_role_id_setting', 0));
				}
				// set field meta
				update_post_meta($field_id, 'b2bking_custom_field_status', 1);
				update_post_meta($field_id, 'b2bking_custom_field_required', 0);
				update_post_meta($field_id, 'b2bking_custom_field_sort_number', 5);
				update_post_meta($field_id, 'b2bking_custom_field_registration_role', 'role_'.$b2b_role_id);
				update_post_meta($field_id, 'b2bking_custom_field_editable', 1);
				update_post_meta($field_id, 'b2bking_custom_field_field_type', 'text');
				update_post_meta($field_id, 'b2bking_custom_field_field_label', sanitize_text_field(esc_html__('Address Line 2','b2bking')));
				update_post_meta($field_id, 'b2bking_custom_field_field_placeholder', sanitize_text_field(esc_html__('Address line 2...','b2bking')));
				update_post_meta($field_id, 'b2bking_custom_field_billing_connection', 'billing_address_2');

				// set option 
				update_option( 'b2bking_street_address_second_initial_field_id_setting', intval($field_id) );
			}
		}

		if ( !get_post_status ( '4225475' ) ) { // Deprecated, don't affect existing customers

			$town_city_initial_field_id = intval(get_option('b2bking_town_city_initial_field_id_setting', 0));
			// if field does not exist, = 0
			if (intval($town_city_initial_field_id === 0)){

				$field = array(
				    'post_title' => sanitize_text_field(esc_html__('Town / City','b2bking')),
				    'post_status' => 'publish',
				    'post_type' => 'b2bking_custom_field',
				    'post_author' => 1,
				);
				$field_id = wp_insert_post($field);

				$b2b_role_id = '4225467';
				if ( !get_post_status ( '4225467' ) ) { // deprecated, don't affect existing customers
					$b2b_role_id = intval(get_option('b2bking_b2b_role_id_setting', 0));
				}
				// set field meta
				update_post_meta($field_id, 'b2bking_custom_field_status', 1);
				update_post_meta($field_id, 'b2bking_custom_field_required', 1);
				update_post_meta($field_id, 'b2bking_custom_field_sort_number', 8);
				update_post_meta($field_id, 'b2bking_custom_field_registration_role', 'role_'.$b2b_role_id);
				update_post_meta($field_id, 'b2bking_custom_field_editable', 1);
				update_post_meta($field_id, 'b2bking_custom_field_field_type', 'text');
				update_post_meta($field_id, 'b2bking_custom_field_field_label', sanitize_text_field(esc_html__('Town / City','b2bking')));
				update_post_meta($field_id, 'b2bking_custom_field_field_placeholder', sanitize_text_field(esc_html__('Enter your town / city here...','b2bking')));
				update_post_meta($field_id, 'b2bking_custom_field_billing_connection', 'billing_city');

				// set option 
				update_option( 'b2bking_town_city_initial_field_id_setting', intval($field_id) );
			}
		}

		if ( !get_post_status ( '4225476' ) ) { // Deprecated, don't affect existing customers

			$postcode_zip_initial_field_id = intval(get_option('b2bking_postcode_zip_initial_field_id_setting', 0));
			// if field does not exist, = 0
			if (intval($postcode_zip_initial_field_id === 0)){

				$field = array(
				    'post_title' => sanitize_text_field(esc_html__('Postcode / ZIP','b2bking')),
				    'post_status' => 'publish',
				    'post_type' => 'b2bking_custom_field',
				    'post_author' => 1,
				);
				$field_id = wp_insert_post($field);

				$b2b_role_id = '4225467';
				if ( !get_post_status ( '4225467' ) ) { // deprecated, don't affect existing customers
					$b2b_role_id = intval(get_option('b2bking_b2b_role_id_setting', 0));
				}
				// set field meta
				update_post_meta($field_id, 'b2bking_custom_field_status', 1);
				update_post_meta($field_id, 'b2bking_custom_field_required', 1);
				update_post_meta($field_id, 'b2bking_custom_field_sort_number', 9);
				update_post_meta($field_id, 'b2bking_custom_field_registration_role', 'role_'.$b2b_role_id);
				update_post_meta($field_id, 'b2bking_custom_field_editable', 1);
				update_post_meta($field_id, 'b2bking_custom_field_field_type', 'text');
				update_post_meta($field_id, 'b2bking_custom_field_field_label', sanitize_text_field(esc_html__('Postcode / ZIP','b2bking')));
				update_post_meta($field_id, 'b2bking_custom_field_field_placeholder', sanitize_text_field(esc_html__('Enter your postcode / ZIP here...','b2bking')));
				update_post_meta($field_id, 'b2bking_custom_field_billing_connection', 'billing_postcode');

				// set option 
				update_option( 'b2bking_postcode_zip_initial_field_id_setting', intval($field_id) );
			}
		}

		if ( !get_post_status ( '4225477' ) ) { // Deprecated, don't affect existing customers

			$phone_initial_field_id = intval(get_option('b2bking_phone_initial_field_id_setting', 0));
			// if field does not exist, = 0
			if (intval($phone_initial_field_id === 0)){

				$field = array(
				    'post_title' => sanitize_text_field(esc_html__('Phone Number','b2bking')),
				    'post_status' => 'publish',
				    'post_type' => 'b2bking_custom_field',
				    'post_author' => 1,
				);
				$field_id = wp_insert_post($field);

				$b2b_role_id = '4225467';
				if ( !get_post_status ( '4225467' ) ) { // deprecated, don't affect existing customers
					$b2b_role_id = intval(get_option('b2bking_b2b_role_id_setting', 0));
				}
				// set field meta
				update_post_meta($field_id, 'b2bking_custom_field_status', 1);
				update_post_meta($field_id, 'b2bking_custom_field_required', 1);
				update_post_meta($field_id, 'b2bking_custom_field_sort_number', 10);
				update_post_meta($field_id, 'b2bking_custom_field_registration_role', 'role_'.$b2b_role_id);
				update_post_meta($field_id, 'b2bking_custom_field_editable', 1);
				update_post_meta($field_id, 'b2bking_custom_field_field_type', 'tel');
				update_post_meta($field_id, 'b2bking_custom_field_field_label', sanitize_text_field(esc_html__('Phone Number','b2bking')));
				update_post_meta($field_id, 'b2bking_custom_field_field_placeholder', sanitize_text_field(esc_html__('Enter your phone here...','b2bking')));
				update_post_meta($field_id, 'b2bking_custom_field_billing_connection', 'billing_phone');

				// set option 
				update_option( 'b2bking_phone_initial_field_id_setting', intval($field_id) );
			}
		}

		if ( !get_post_status ( '4225478' ) ) { // Deprecated, don't affect existing customers

			$country_state_initial_field_id = intval(get_option('b2bking_country_state_initial_field_id_setting', 0));
			// if field does not exist, = 0
			if (intval($country_state_initial_field_id === 0)){

				$field = array(
				    'post_title' => sanitize_text_field(esc_html__('Country + State','b2bking')),
				    'post_status' => 'publish',
				    'post_type' => 'b2bking_custom_field',
				    'post_author' => 1,
				);
				$field_id = wp_insert_post($field);

				$b2b_role_id = '4225467';
				if ( !get_post_status ( '4225467' ) ) { // deprecated, don't affect existing customers
					$b2b_role_id = intval(get_option('b2bking_b2b_role_id_setting', 0));
				}
				// set field meta
				update_post_meta($field_id, 'b2bking_custom_field_status', 1);
				update_post_meta($field_id, 'b2bking_custom_field_required', 1);
				update_post_meta($field_id, 'b2bking_custom_field_sort_number', 7);
				update_post_meta($field_id, 'b2bking_custom_field_registration_role', 'role_'.$b2b_role_id);
				update_post_meta($field_id, 'b2bking_custom_field_editable', 1);
				update_post_meta($field_id, 'b2bking_custom_field_field_type', 'text');
				update_post_meta($field_id, 'b2bking_custom_field_field_label', sanitize_text_field(esc_html__('Country and State','b2bking')));
				update_post_meta($field_id, 'b2bking_custom_field_field_placeholder', sanitize_text_field(esc_html__('Enter your country and state here...','b2bking')));
				update_post_meta($field_id, 'b2bking_custom_field_billing_connection', 'billing_countrystate');

				// set option 
				update_option( 'b2bking_country_state_initial_field_id_setting', intval($field_id) );
			}
		}

		// Set up a VAT field by default
		$vat_initial_field_id = intval(get_option('b2bking_vat_initial_field_id_setting', 0));
		// if field does not exist, = 0
		if (intval($vat_initial_field_id === 0)){

			// if the B2B role exists // may be deleted already for older setups
			if (get_post_status(intval(get_option('b2bking_b2b_role_id_setting', 0))) === 'publish' && get_post_type(intval(get_option('b2bking_b2b_role_id_setting', 0))) === 'b2bking_custom_role' ){
				$field = array(
				    'post_title' => sanitize_text_field(esc_html__('VAT','b2bking')),
				    'post_status' => 'publish',
				    'post_type' => 'b2bking_custom_field',
				    'post_author' => 1,
				);
				$field_id = wp_insert_post($field);

				$b2b_role_id = '4225467';
				if ( !get_post_status ( '4225467' ) ) { // deprecated, don't affect existing customers
					$b2b_role_id = intval(get_option('b2bking_b2b_role_id_setting', 0));
				}
				// set field meta
				update_post_meta($field_id, 'b2bking_custom_field_status', 0);
				update_post_meta($field_id, 'b2bking_custom_field_required', 0);
				update_post_meta($field_id, 'b2bking_custom_field_sort_number', 11);
				update_post_meta($field_id, 'b2bking_custom_field_registration_role', 'role_'.$b2b_role_id);
				update_post_meta($field_id, 'b2bking_custom_field_editable', 1);
				update_post_meta($field_id, 'b2bking_custom_field_field_type', 'text');
				update_post_meta($field_id, 'b2bking_custom_field_field_label', sanitize_text_field(esc_html__('VAT','b2bking')));
				update_post_meta($field_id, 'b2bking_custom_field_field_placeholder', sanitize_text_field(esc_html__('Enter your VAT number here...','b2bking')));
				update_post_meta($field_id, 'b2bking_custom_field_billing_connection', 'billing_vat');

				// set option 
				update_option( 'b2bking_vat_initial_field_id_setting', intval($field_id) );
			}

			
		}


		// Check Product Number and User Number. Deactivate products / users selector in dynamic rules if too many
		$users = count_users();
		if (intval($users['avail_roles']['customer']) > 500){
			// hide users
			update_option('b2bking_hide_users_dynamic_rules_setting', 1);
			update_option('b2bking_customers_panel_ajax_setting', 1);
		}

		$products_over_number = get_posts( array(
							'post_type' => array( 'product', 'product_variation'),
							'post_status'=>'publish', 
							'numberposts' => 500,
							'fields' => 'ids',
						));

		if (count($products_over_number) > 499){
			// over 500 products and variations
			update_option('b2bking_replace_product_selector_setting', 1);
		} else {
			// do nothing
		}

		$groups = get_posts([
		  'post_type' => 'b2bking_group',
		  'post_status' => 'publish',
		  'numberposts' => -1,
		  'fields' => 'ids',
		]);

		if (apply_filters('b2bking_use_wp_roles', false)){
			// add b2c user WP Role
			add_role('b2bking_role_b2cuser', esc_html__('B2BKing B2C User','b2bking'));
			// for an existing user, create all groups as roles
			
			foreach ($groups as $group){
				add_role('b2bking_role_'.$group, get_the_title($group));
			}
		}

		if (intval(count($groups)) === 0){
			// there are no groups, let's create a B2B group
			$groupp = array(
				'post_title'  => sanitize_text_field( esc_html__( 'B2B Users', 'b2bking' ) ),
				'post_status' => 'publish',
				'post_type'   => 'b2bking_group',
				'post_author' => 1,
			);
			$group_id = wp_insert_post( $groupp );
		}
	}

}
