<?php


class B2bkingcore_Activator {

	public static function activate() {

		if (!defined('B2BKING_DIR')){

			// prevent option update issues due to caching
			wp_cache_delete ( 'alloptions', 'options' );

			// Trigger post types and endpoints functions
			require_once ( B2BKINGCORE_DIR . 'admin/class-b2bking-admin.php' );
			require_once ( B2BKINGCORE_DIR . 'public/class-b2bking-public.php' );
			$adminobj = new B2bkingcore_Admin;
			$publicobj = new B2bkingcore_Public;
			$adminobj->b2bking_register_post_type_customer_groups();
			$adminobj->b2bking_register_post_type_dynamic_rules();
			$adminobj->b2bking_register_post_type_custom_role();
			
			// Flush rewrite rules
			flush_rewrite_rules();

			if (empty(get_user_meta(get_current_user_id(),'b2bking_dismiss_activate_woocommerce_notice', true))){
				// give users 48 hours first
				update_user_meta( get_current_user_id(), 'b2bking_dismiss_review_notice_time', time());
				update_user_meta( get_current_user_id(), 'b2bking_dismiss_review_notice', 1 );
			}

			
			// Set admin notice state to enabled ('activate woocommerce' notice)
			update_user_meta(get_current_user_id(), 'b2bking_dismiss_activate_woocommerce_notice', 0);


			// Create 2 default user roles if they don't exist: Individual Customer and B2B
			// set plugin to active
			update_option('b2bking_plugin_status_setting', 'b2b');

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


			$groups = get_posts([
			  'post_type' => 'b2bking_group',
			  'post_status' => 'publish',
			  'numberposts' => -1,
			  'fields' => 'ids',
			]);

			if (intval(count($groups)) === 0){
				// Create a default b2b group
				$b2b_group_id = intval(get_option('b2bking_b2bgroup_id_setting', 0));
				// if role does not exist, = 0
				if (intval($b2b_group_id === 0)){
					$groupp = array(
					    'post_title' => sanitize_text_field(esc_html__('B2B Users','b2bking')),
					    'post_status' => 'publish',
					    'post_type' => 'b2bking_group',
					    'post_author' => 1,
					);
					$group_id = wp_insert_post($groupp);

					// Only load if WooCommerce is activated
					if ( class_exists( 'woocommerce' ) ) {
						// Save Payment methods and Shipping Methods
						$shipping_methods = WC()->shipping->get_shipping_methods();
						foreach ($shipping_methods as $shipping_method){
							update_post_meta( $group_id, 'b2bking_group_shipping_method_'.$shipping_method->id, 1);
						}
						$payment_methods = WC()->payment_gateways->payment_gateways();
						foreach ($payment_methods as $payment_method){
							update_post_meta( $group_id, 'b2bking_group_payment_method_'.$payment_method->id, 1);
						}
					}

					// set option 
					update_option( 'b2bking_b2bgroup_id_setting', intval($group_id) );
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
			
		}

	}

}
