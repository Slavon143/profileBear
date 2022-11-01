<?php

class B2bkingcore_Public{

	function __construct() {
		
		// Include dynamic rules code
		require_once ( B2BKINGCORE_DIR . 'public/class-b2bking-dynamic-rules.php' );

		add_action('plugins_loaded', function(){

			// Only load if WooCommerce is activated
			if ( class_exists( 'woocommerce' ) && !class_exists('B2bking')) {

				// Check that plugin is enabled
				if ( get_option('b2bking_plugin_status_setting', 'b2b') !== 'disabled' ){

					$user_data_current_user_id = get_current_user_id();

					$user_data_current_user_b2b = get_user_meta($user_data_current_user_id, 'b2bking_b2buser', true);

					$user_data_current_user_group = get_user_meta($user_data_current_user_id, 'b2bking_customergroup', true);

					if (intval(get_option('b2bking_disable_registration_setting', 0)) === 0){

						// Custom user registration fields
						add_action( 'woocommerce_register_form', array($this,'b2bking_custom_registration_fields') );

						// only show registration at checkout if user is not already logged in
						if (!is_user_logged_in()){
							if ( intval(get_option('b2bking_registration_at_checkout_setting', 0)) === 1 ){
								add_action( 'woocommerce_after_checkout_billing_form', array($this,'b2bking_custom_registration_fields_checkout') );
							}
						}
						// Save custom registration data
						// use user_register hook as well, seems to fix issues in certain installations
						add_action('user_register', array($this,'b2bking_save_custom_registration_fields') );
						add_action('woocommerce_created_customer', array($this,'b2bking_save_custom_registration_fields') );
						// If user approval is manual, stop automatic login on registration
						add_action('woocommerce_registration_redirect', array($this,'b2bking_check_user_approval_on_registration'), 2);
						// Check for approval meta on login
						add_filter('woocommerce_process_login_errors', array($this,'b2bking_check_user_approval_on_login'), 10, 3);
						if ( intval(get_option('b2bking_registration_at_checkout_setting', 0)) === 1 ){
							add_action( 'woocommerce_thankyou', array($this,'b2bking_check_user_approval_on_registration_checkout'));
						}
						// Modify new account email to include notice of manual account approval, if needed
						add_action( 'woocommerce_email_footer', array($this,'b2bking_modify_new_account_email'), 10, 1 );
					}
								
					// enable dynamic rules IF: plugin is in B2B mode, OR hybrid mode + b2b user OR hybrid mode + option
					if (get_option('b2bking_plugin_status_setting', 'b2b') === 'b2b' || (get_option('b2bking_plugin_status_setting', 'b2b') === 'hybrid' && (get_user_meta( get_current_user_id(), 'b2bking_b2buser', true ) === 'yes')) || (get_option('b2bking_plugin_status_setting', 'b2b') === 'hybrid' && (intval(get_option('b2bking_enable_rules_for_non_b2b_users_setting', 1)) === 1)) ){
						/* Dynamic Rules */
						// Dynamic rule Discounts via fees 
						if (intval(get_option('b2bking_disable_dynamic_rule_discount_setting', 0)) === 0){
							if (get_option('b2bking_have_discount_rules', 'yes') === 'yes'){
								// check if the user's ID or group is part of the list.
								$list = get_option('b2bking_have_discount_rules_list', 'yes');
								if ($this->b2bking_user_is_in_list($user_data_current_user_id, $user_data_current_user_b2b, $user_data_current_user_group, $list) === 'yes'){
									add_action('woocommerce_cart_calculate_fees' , array('B2bkingcore_Dynamic_Rules', 'b2bking_dynamic_rule_cart_discount'));
								}
							}
							if (get_option('b2bking_have_discount_everywhere_rules', 'yes') === 'yes'){
								// check if the user's ID or group is part of the list.
								$list = get_option('b2bking_have_discount_everywhere_rules_list', 'yes');
								if ($this->b2bking_user_is_in_list($user_data_current_user_id, $user_data_current_user_b2b, $user_data_current_user_group, $list) === 'yes'){

									add_filter( 'woocommerce_product_get_regular_price', array('B2bkingcore_Dynamic_Rules', 'b2bking_dynamic_rule_discount_regular_price'), 9999, 2 );
									add_filter( 'woocommerce_product_variation_get_regular_price', array('B2bkingcore_Dynamic_Rules', 'b2bking_dynamic_rule_discount_regular_price'), 9999, 2 );
									// Generate "sale price" dynamically
									add_filter( 'woocommerce_product_get_sale_price', array('B2bkingcore_Dynamic_Rules', 'b2bking_dynamic_rule_discount_sale_price'), 9999, 2 );
									add_filter( 'woocommerce_product_variation_get_sale_price', array('B2bkingcore_Dynamic_Rules', 'b2bking_dynamic_rule_discount_sale_price'), 9999, 2 );
									add_filter( 'woocommerce_variation_prices_price', array('B2bkingcore_Dynamic_Rules', 'b2bking_dynamic_rule_discount_sale_price'), 9999, 2 );
									add_filter( 'woocommerce_variation_prices_sale_price', array('B2bkingcore_Dynamic_Rules', 'b2bking_dynamic_rule_discount_sale_price'), 9999, 2 );
									add_filter( 'woocommerce_get_variation_prices_hash', array('B2bkingcore_Dynamic_Rules', 'b2bking_dynamic_rule_discount_sale_price_variation_hash'), 99, 1);

									// Displayed formatted regular price + sale price
									add_filter( 'woocommerce_get_price_html', array('B2bkingcore_Dynamic_Rules', 'b2bking_dynamic_rule_discount_display_dynamic_price'), 9999, 2 );
									// Set sale price in Cart
									add_action( 'woocommerce_before_calculate_totals', array('B2bkingcore_Dynamic_Rules', 'b2bking_dynamic_rule_discount_display_dynamic_price_in_cart'), 9999, 1 );
									// Function to make this work for MiniCart as well
									add_filter('woocommerce_cart_item_price',array('B2bkingcore_Dynamic_Rules', 'b2bking_dynamic_rule_discount_display_dynamic_price_in_cart_item'),9999,3);
									
									// Change "Sale!" badge text
									add_filter('woocommerce_sale_flash', array('B2bkingcore_Dynamic_Rules', 'b2bking_dynamic_rule_discount_display_dynamic_sale_badge'), 999999, 3);

								}
							}
						}
					}

					/* Set Individual Product Pricing (via product tab) */
					add_filter('woocommerce_product_get_price', array($this, 'b2bking_individual_pricing_fixed_price'), 999, 2 );
					add_filter('woocommerce_product_get_regular_price', array($this, 'b2bking_individual_pricing_fixed_price'), 999, 2 );
					// Variations 
					add_filter('woocommerce_product_variation_get_regular_price', array($this, 'b2bking_individual_pricing_fixed_price'), 999, 2 );
					add_filter('woocommerce_product_variation_get_price', array($this, 'b2bking_individual_pricing_fixed_price'), 999, 2 );
					add_filter( 'woocommerce_variation_prices_price', array($this, 'b2bking_individual_pricing_fixed_price'), 999, 2 );
					add_filter( 'woocommerce_variation_prices_regular_price', array($this, 'b2bking_individual_pricing_fixed_price'), 999, 2 );
					// Set sale price as well
					add_filter( 'woocommerce_product_get_sale_price', array($this, 'b2bking_individual_pricing_discount_sale_price'), 999, 2 );
					add_filter( 'woocommerce_product_variation_get_sale_price', array($this, 'b2bking_individual_pricing_discount_sale_price'), 999, 2 );
					add_filter( 'woocommerce_variation_prices_price', array($this, 'b2bking_individual_pricing_discount_sale_price'), 999, 2 );
					add_filter( 'woocommerce_variation_prices_sale_price', array($this, 'b2bking_individual_pricing_discount_sale_price'), 999, 2 );
					// display html
					// Displayed formatted regular price + sale price
					add_filter( 'woocommerce_get_price_html', array($this, 'b2bking_individual_pricing_discount_display_dynamic_price'), 999, 2 );
					// Set sale price in Cart
					add_action( 'woocommerce_before_calculate_totals', array($this, 'b2bking_individual_pricing_discount_display_dynamic_price_in_cart'), 999, 1 );
					// Function to make this work for MiniCart as well
					add_filter('woocommerce_cart_item_price',array($this, 'b2bking_individual_pricing_discount_display_dynamic_price_in_cart_item'),999,3);
					add_filter( 'woocommerce_get_variation_prices_hash', array('B2bkingcore_Dynamic_Rules', 'b2bking_dynamic_rule_discount_sale_price_variation_hash'), 99, 1);

					if (intval(get_option('b2bking_disable_shipping_payment_control_setting', 0)) === 0){

						WC_Cache_Helper::get_transient_version( 'shipping', true );

						// Disable shipping methods based on group rules

						if (intval(get_option('b2bking_disable_shipping_control_setting', 0)) === 0){

							add_action( 'woocommerce_package_rates', array($this, 'b2bking_disable_shipping_methods'), 1 );

						}

						if (intval(get_option('b2bking_disable_payment_control_setting', 0)) === 0){

							// Disable payment methods based on group rules
							add_filter('woocommerce_available_payment_gateways', array($this,'b2bking_disable_payment_methods'),1);
							// Disable payment methods based on dynamic rule payment methods
							add_filter('woocommerce_available_payment_gateways', array($this,'b2bking_disable_payment_methods_dynamic_rule'),9999, 1);

						}
					}

					// Enqueue resources
					add_action('wp_enqueue_scripts', array($this, 'enqueue_public_resources'));
				}
			}
		});
	}

	function b2bking_individual_pricing_fixed_price($price, $product){
		
			if (is_user_logged_in()){
				$user_id = get_current_user_id();
		    	$account_type = get_user_meta($user_id,'b2bking_account_type', true);
		    	if ($account_type === 'subaccount'){
		    		// for all intents and purposes set current user as the subaccount parent
		    		$parent_user_id = get_user_meta($user_id, 'b2bking_account_parent', true);
		    		$user_id = $parent_user_id;
		    	}

		    	// check transient to see if the current price has been set already via another function
		    	if (get_transient('b2bking_user_'.$user_id.'_product_'.$product->get_id().'_custom_set_price') === $price){
		    		return $price;
		    	}

		    	$is_b2b_user = get_the_author_meta( 'b2bking_b2buser', $user_id );
				$currentusergroupidnr = get_the_author_meta( 'b2bking_customergroup', $user_id );
				if ($is_b2b_user === 'yes'){
					// Search if there is a specific price set for the user's group
					$b2b_price = get_post_meta($product->get_id(), 'b2bking_regular_product_price_group_'.$currentusergroupidnr, true );
					if (!empty($b2b_price)){
						return $b2b_price;
					} else {
						return $price;
					}
				} else {
					return $price;
				}
			} else {
				return $price;
			}
	}

	function b2bking_individual_pricing_discount_sale_price( $sale_price, $product ){

		if (is_user_logged_in()){
			$user_id = get_current_user_id();
	    	$account_type = get_user_meta($user_id,'b2bking_account_type', true);
	    	if ($account_type === 'subaccount'){
	    		// for all intents and purposes set current user as the subaccount parent
	    		$parent_user_id = get_user_meta($user_id, 'b2bking_account_parent', true);
	    		$user_id = $parent_user_id;
	    	}

	    	$is_b2b_user = get_the_author_meta( 'b2bking_b2buser', $user_id );
			$currentusergroupidnr = get_the_author_meta( 'b2bking_customergroup', $user_id );
			if ($is_b2b_user === 'yes'){
				// Search if there is a specific price set for the user's group
				$b2b_price = get_post_meta($product->get_id(), 'b2bking_sale_product_price_group_'.$currentusergroupidnr, true );
				if (!empty($b2b_price)){
					/*
					// ADD WOOCS COMPATIBILITY
		    		if (class_exists('WOOCS')) {
						global $WOOCS;
						$currrent = $WOOCS->current_currency;
						if ($currrent != $WOOCS->default_currency) {
							$currencies = $WOOCS->get_currencies();
							$rate = $currencies[$currrent]['rate'];
							$b2b_price = $b2b_price / ($rate);
						}
					}*/

					return $b2b_price;
				} else {
					return $sale_price;
				}
			} else {
				return $sale_price;
			}
		} else {
			return $sale_price;
		}
	}

	function b2bking_individual_pricing_discount_display_dynamic_price( $price_html, $product ) {
		if( $product->is_type('variable') && !class_exists('WOOCS')) { // add WOOCS compatibility
			return $price_html;
		}

		if (is_user_logged_in()){
			$user_id = get_current_user_id();
	    	$account_type = get_user_meta($user_id,'b2bking_account_type', true);
	    	if ($account_type === 'subaccount'){
	    		// for all intents and purposes set current user as the subaccount parent
	    		$parent_user_id = get_user_meta($user_id, 'b2bking_account_parent', true);
	    		$user_id = $parent_user_id;
	    	}

	    	$is_b2b_user = get_the_author_meta( 'b2bking_b2buser', $user_id );
			$currentusergroupidnr = get_the_author_meta( 'b2bking_customergroup', $user_id );
			if ($is_b2b_user === 'yes'){
				// Search if there is a specific price set for the user's group
				$b2b_price = get_post_meta($product->get_id(), 'b2bking_sale_product_price_group_'.$currentusergroupidnr, true );
				if (!empty($b2b_price)){

					if( $product->is_type('variable') && class_exists('WOOCS')) { // add WOOCS compatibility

						global $WOOCS;
						$currrent = $WOOCS->current_currency;
						if ($currrent != $WOOCS->default_currency) {
							$currencies = $WOOCS->get_currencies();
							$rate = $currencies[$currrent]['rate'];

							// apply WOOCS rate to price_html
							$min_price = $product->get_variation_price( 'min' ) / ($rate);
							$max_price = $product->get_variation_price( 'max' ) / ($rate);
							$price_html = wc_format_price_range( $min_price, $max_price );
						}

					} else { 

		    			$price_html = wc_format_sale_price( wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) ), wc_get_price_to_display(  $product, array( 'price' => $product->get_sale_price() ) ) ) . $product->get_price_suffix();
					}
		    	}
		    }
		}

	    return $price_html;
	}

	function b2bking_individual_pricing_discount_display_dynamic_price_in_cart($cart){
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ){
		    return;
		}

		if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 ){
		    return;
		}

		// Get current user
    	$user_id = get_current_user_id();
    	$account_type = get_user_meta($user_id,'b2bking_account_type', true);
    	if ($account_type === 'subaccount'){
    		// for all intents and purposes set current user as the subaccount parent
    		$parent_user_id = get_user_meta($user_id, 'b2bking_account_parent', true);
    		$user_id = $parent_user_id;
    	}

    	$is_b2b_user = get_the_author_meta( 'b2bking_b2buser', $user_id );
		$currentusergroupidnr = get_the_author_meta( 'b2bking_customergroup', $user_id );
		if ($is_b2b_user === 'yes'){
			// Iterate through each cart item
			foreach( $cart->get_cart() as $cart_item ) {
				// Search if there is a specific price set for the user's group
				if (isset($cart_item['variation_id']) && intval($cart_item['variation_id']) !== 0){
					$b2b_price = get_post_meta($cart_item['variation_id'], 'b2bking_sale_product_price_group_'.$currentusergroupidnr, true );
					$product_id_set = $cart_item['variation_id'];
				} else {
					$b2b_price = get_post_meta($cart_item['product_id'], 'b2bking_sale_product_price_group_'.$currentusergroupidnr, true );
					$product_id_set = $cart_item['product_id'];
				}
				
				if (!empty($b2b_price)){
					$cart_item['data']->set_price( $b2b_price );
					set_transient('b2bking_user_'.$user_id.'_product_'.$product_id_set.'_custom_set_price', $b2b_price);
		    	}
		    }
	    }

	}

	function b2bking_individual_pricing_discount_display_dynamic_price_in_cart_item( $price, $cart_item, $cart_item_key){

		// Get current user
    	$user_id = get_current_user_id();
    	$account_type = get_user_meta($user_id,'b2bking_account_type', true);
    	if ($account_type === 'subaccount'){
    		// for all intents and purposes set current user as the subaccount parent
    		$parent_user_id = get_user_meta($user_id, 'b2bking_account_parent', true);
    		$user_id = $parent_user_id;
    	}

    	$is_b2b_user = get_the_author_meta( 'b2bking_b2buser', $user_id );
		$currentusergroupidnr = get_the_author_meta( 'b2bking_customergroup', $user_id );
		if ($is_b2b_user === 'yes'){
			if (isset($cart_item['variation_id']) && intval($cart_item['variation_id']) !== 0){
				$b2b_price = get_post_meta($cart_item['variation_id'], 'b2bking_sale_product_price_group_'.$currentusergroupidnr, true );
				$product_id_set = $cart_item['variation_id'];
			} else {
				$b2b_price = get_post_meta($cart_item['product_id'], 'b2bking_sale_product_price_group_'.$currentusergroupidnr, true );
				$product_id_set = $cart_item['product_id'];
			}

			if (!empty($b2b_price)){

				require_once ( B2BKINGCORE_DIR . 'public/class-b2bking-helper.php' );
				$helper = new B2bkingcore_Helper();
				
				$discount_price = $helper->b2bking_wc_get_price_to_display( wc_get_product($product_id_set), array( 'price' => $cart_item['data']->get_sale_price() ) ); // get sale price
				
				if ($discount_price !== NULL && $discount_price !== ''){
					$price = wc_price($discount_price, 4); 
				}
			} 
		}
		return $price;
	}

	function b2bking_check_user_approval_on_login ($errors, $username, $password) {

		// First need to get the user object
		if (!empty($username)){
			$user = get_user_by('login', $username);
			if(!$user) {
				$user = get_user_by('email', $username);
				if(!$user) {
					return $errors;
				}
			}
		}
		if (isset($user->ID)){
			$user_status = get_user_meta($user->ID, 'b2bking_account_approved', true);
			if($user_status === 'no'){
				$errors->add('access', esc_html__('Your account is waiting for approval. Until approved, you cannot login.','b2bking'));
			}
		}
	    return $errors;
	}


	// Modify new account email - Add approval needed notice
	function b2bking_modify_new_account_email( $email ) { 

		if ( $email->id === 'customer_new_account' ) {
			$user = get_user_by('email', $email->user_email);
			$approval_needed = get_user_meta($user->ID, 'b2bking_account_approved', true);
			if ($approval_needed === 'no'){
				?>
				<p>
					<?php
					echo '<strong>';
					esc_html_e('Attention! Your account requires manual approval. ', 'b2bking' );
					echo '</strong>';
					esc_html_e('Our team will review it as soon as possible. Thank you for understanding.', 'b2bking' );
					?>
				</p>
				<?php
			}
		}
	}

	// Custom Registration Fields
	function b2bking_custom_registration_fields(){
		global $woocommerce;    
		global $b2bking_is_b2b_registration;
		global $b2bking_is_b2b_registration_shortcode_role_id;

		if ($b2bking_is_b2b_registration_shortcode_role_id === NULL || $b2bking_is_b2b_registration_shortcode_role_id === ''){
			$b2bking_is_b2b_registration_shortcode_role_id = 'none';
		}

		// if Registration Roles dropdown is enabled (enabled by default), show custom registration roles and fields
		$registration_role_setting = intval(get_option( 'b2bking_registration_roles_dropdown_setting', 1 ));
		if ($registration_role_setting === 1 || $b2bking_is_b2b_registration === 'yes'){

			// get roles
			$custom_roles = get_posts([
			    		'post_type' => 'b2bking_custom_role',
			    	  	'post_status' => 'publish',
			    	  	'numberposts' => -1,
			    	  	'meta_key' => 'b2bking_custom_role_sort_number',
		    	  	    'orderby' => 'meta_value_num',
		    	  	    'order' => 'ASC',
			    	  	'meta_query'=> array(
			    	  		'relation' => 'AND',
			                array(
		                        'key' => 'b2bking_custom_role_status',
		                        'value' => 1
			                ),
		            	)
			    	]);

			?>
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide b2bking_registration_roles_dropdown_section <?php if ($b2bking_is_b2b_registration_shortcode_role_id !== 'none'){ echo 'b2bking_registration_roles_dropdown_section_hidden'; } ?>">
				<label for="b2bking_registration_roles_dropdown">
					<?php esc_html_e('User Type','b2bking'); ?>&nbsp;<span class="required">*</span>
				</label>
				<select id="b2bking_registration_roles_dropdown" name="b2bking_registration_roles_dropdown" required>
					<?php
					foreach ($custom_roles as $role){
						echo '<option value="role_'.esc_attr($role->ID).'" '.selected($role->ID,$b2bking_is_b2b_registration_shortcode_role_id,false).'>'.esc_html(get_the_title(apply_filters( 'wpml_object_id', $role->ID, 'post', true ))).'</option>';
					}
					?>
				</select>
			</p>
			<?php
		}
	}

	function b2bking_custom_registration_fields_checkout(){
		global $woocommerce;    
		global $b2bking_is_b2b_registration;
		global $b2bking_is_b2b_registration_shortcode_role_id;

		if ($b2bking_is_b2b_registration_shortcode_role_id === NULL || $b2bking_is_b2b_registration_shortcode_role_id === ''){
			$b2bking_is_b2b_registration_shortcode_role_id = 'none';
		}

		// if Registration Roles dropdown is enabled (enabled by default), show custom registration roles and fields
		$registration_role_setting = intval(get_option( 'b2bking_registration_roles_dropdown_setting', 1 ));
		if ($registration_role_setting === 1 || $b2bking_is_b2b_registration === 'yes'){

			// get roles
			$custom_roles = get_posts([
			    		'post_type' => 'b2bking_custom_role',
			    	  	'post_status' => 'publish',
			    	  	'numberposts' => -1,
			    	  	'meta_key' => 'b2bking_custom_role_sort_number',
		    	  	    'orderby' => 'meta_value_num',
		    	  	    'order' => 'ASC',
			    	  	'meta_query'=> array(
			    	  		'relation' => 'AND',
			                array(
		                        'key' => 'b2bking_custom_role_status',
		                        'value' => 1
			                ),
		            	)
			    	]);

			?>
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide b2bking_registration_roles_dropdown_section <?php if ($b2bking_is_b2b_registration_shortcode_role_id !== 'none'){ echo 'b2bking_registration_roles_dropdown_section_hidden'; } ?>">
				<label for="b2bking_registration_roles_dropdown">
					<?php esc_html_e('User Type','b2bking'); ?>&nbsp;<span class="required">*</span>
				</label>
				<select id="b2bking_registration_roles_dropdown" name="b2bking_registration_roles_dropdown" required>
					<?php
					foreach ($custom_roles as $role){
						echo '<option value="role_'.esc_attr($role->ID).'" '.selected($role->ID,$b2bking_is_b2b_registration_shortcode_role_id,false).'>'.esc_html(get_the_title(apply_filters( 'wpml_object_id', $role->ID, 'post', true ))).'</option>';
					}
					?>
				</select>
			</p>
			<?php
		}
	}

	// Save Custom Registration Fields
	function b2bking_save_custom_registration_fields($user_id){

		// if user role dropdown enabled, also set user registration role as meta
		$registration_role_setting = intval(get_option( 'b2bking_registration_roles_dropdown_setting', 1 ));
		if ($registration_role_setting === 1){
			$user_role = sanitize_text_field(filter_input(INPUT_POST, 'b2bking_registration_roles_dropdown'));
			if ($user_role !== NULL){
				update_user_meta( $user_id, 'b2bking_registration_role', $user_role);
			}
		}
		
		// if settings require approval on all users OR chosen user role requires approval
		if (intval(get_option('b2bking_approval_required_all_users_setting', 0)) === 1){
			update_user_meta( $user_id, 'b2bking_account_approved', 'no');

		} else if (isset($_POST['b2bking_registration_roles_dropdown'])){
			$user_role = sanitize_text_field(filter_input(INPUT_POST, 'b2bking_registration_roles_dropdown'));
			$user_role_id = explode('_', $user_role)[1];
			$user_role_approval = get_post_meta($user_role_id, 'b2bking_custom_role_approval', true);
			if ($user_role_approval === 'manual'){
				update_user_meta( $user_id, 'b2bking_account_approved', 'no');
			} else if ($user_role_approval === 'automatic'){
				// check if there is a setting to automatically send the user to a particular customer group
				$user_role_automatic_customer_group = get_post_meta($user_role_id, 'b2bking_custom_role_automatic_approval_group', true);
				if ($user_role_automatic_customer_group !== 'none' && $user_role_automatic_customer_group !== NULL && $user_role_automatic_customer_group !== ''){
					$group_id = explode('_',$user_role_automatic_customer_group)[1];
					update_user_meta( $user_id, 'b2bking_customergroup', sanitize_text_field($group_id));
					$user_obj = new WP_User($user_id);
					$user_obj->add_role('b2bking_role_'.$group_id);
				}
			}
		}

		// if customer is being approved automatically, and group is other than none, set customer as B2B
		$user_role = sanitize_text_field(filter_input(INPUT_POST, 'b2bking_registration_roles_dropdown'));
		$user_role_id = explode('_', $user_role)[1];
		$user_role_approval = get_post_meta($user_role_id, 'b2bking_custom_role_approval', true);
		if ($user_role_approval === 'automatic'){
			if ($user_role_automatic_customer_group !== 'none'){
				update_user_meta($user_id, 'b2bking_b2buser', 'yes');
			} else {
				// user must be b2c, add b2c role
				$user_obj = new WP_User($user_id);
				$user_obj->add_role('b2bking_role_b2cuser');
			}
		}

	}

	// If user approval is manual, stop automatic login on registration
	function b2bking_check_user_approval_on_registration($redirection_url) {
		$user_id = get_current_user_id();
		$user_approval = get_user_meta($user_id, 'b2bking_account_approved', true);

		if ($user_approval === 'no'){
			wp_logout();

			do_action( 'woocommerce_set_cart_cookies',  true );

			wc_add_notice( esc_html__('Thank you for registering. Your account requires manual approval. Please wait to be approved.', 'b2bking'), 'success' );			
		}

		$redirection_url = get_permalink( wc_get_page_id( 'myaccount' ) );

		return $redirection_url;
	}

	function b2bking_check_user_approval_on_registration_checkout($order_id) {
		$user_id = get_current_user_id();
		$user_approval = get_user_meta($user_id, 'b2bking_account_approved', true);

		if ($user_approval === 'no'){
			wp_logout();

			do_action( 'woocommerce_set_cart_cookies',  true );

			wc_add_notice( esc_html__('Thank you for registering. Your account requires manual approval. Please wait to be approved.', 'b2bking'), 'success' );			

		}
		exit;
	}

	function b2bking_user_is_in_list($user_data_current_user_id, $user_data_current_user_b2b, $user_data_current_user_group, $list){
		// checks based on user id, b2b status and group, if it's part of an applicable rules list
		$is_in_list = 'no';
		$list_array = explode(',',$list);
		if (intval($user_data_current_user_id) !== 0){
			if (in_array('everyone_registered', $list_array)){
				return 'yes';
			}
			if ($user_data_current_user_b2b === 'yes'){
				// user is b2b
				if (in_array('everyone_registered_b2b', $list_array)){
					return 'yes';
				}
				if (in_array('group_'.$user_data_current_user_group, $list_array)){
					return 'yes';
				}
			} else {
				// user is b2c
				if (in_array('everyone_registered_b2c', $list_array)){
					return 'yes';
				}
			}
			if (in_array('user_'.$user_data_current_user_id, $list_array)){
				return 'yes';
			}

		} else if (intval($user_data_current_user_id) === 0){
			if (in_array('user_0', $list_array)){
				return 'yes';
			}
		}

		return $is_in_list;
	}


	// Disable shipping methods based on user settings (group)
	function b2bking_disable_shipping_methods( $rates ){

	if (apply_filters('b2bking_use_zone_shipping_control', true)){
		$user_id = get_current_user_id();
		$account_type = get_user_meta($user_id,'b2bking_account_type', true);
		if ($account_type === 'subaccount'){
			// for all intents and purposes set current user as the subaccount parent
			$parent_user_id = get_user_meta($user_id, 'b2bking_account_parent', true);
			$user_id = $parent_user_id;
		}

		$available = array();
		// if user is guest, disable shipping methods by guest group options
		if (intval($user_id) === 0){

			// For each shipping method, check if it's available. Add it to available options
			foreach ( $rates as $rate_id => $rate ) {
				$user_access = get_option('b2bking_logged_out_users_shipping_method_'.$rate->method_id.$rate->instance_id, 1);

				// UPS EXCEPTION
				if ($rate->method_id === 'wf_shipping_ups'){
					$user_access = get_option('b2bking_logged_out_users_shipping_method_'.$rate->method_id.'0', 1);
				}
				if (intval($user_access) === 1){
					$available[ $rate_id ] = $rate;
				}
			}

		// else if user is B2C, disable by B2C group options
		} else if (get_user_meta($user_id, 'b2bking_b2buser', true ) !== 'yes'){

			// if user override activated, check user access, else check group access
			$user_override = get_user_meta($user_id, 'b2bking_user_shipping_payment_methods_override', true);
			if ($user_override === 'manual'){
				// follow user rules

				// For each shipping method, check if it's available to the current user. Add it to available options
				foreach ( $rates as $rate_id => $rate ) {
					$user_access = get_user_meta($user_id, 'b2bking_user_shipping_method_'.$rate->method_id.$rate->instance_id, true);

					// enabled if metadata empty
					if (!metadata_exists('user', $user_id, 'b2bking_user_shipping_method_'.$rate->method_id.$rate->instance_id)){
						$user_access = 1;
					}
					// UPS EXCEPTION
					if ($rate->method_id === 'wf_shipping_ups'){
						$user_access = get_user_meta($user_id,'b2bking_user_shipping_method_'.$rate->method_id.'0', true);
					}
					if (intval($user_access) === 1){
						$available[ $rate_id ] = $rate;
					}
				}

			} else {
				// For each shipping method, check if it's available. Add it to available options
				foreach ( $rates as $rate_id => $rate ) {
					$user_access = get_option('b2bking_b2c_users_shipping_method_'.$rate->method_id.$rate->instance_id, 1);

					
					// UPS EXCEPTION
					if ($rate->method_id === 'wf_shipping_ups'){
						$user_access = get_option('b2bking_b2c_users_shipping_method_'.$rate->method_id.'0', 1);
					}
					if (intval($user_access) === 1){
						$available[ $rate_id ] = $rate;
					}
				}
			}

			

		// else it means user is B2B so follow B2B rules
		} else {

			// if user override activated, check user access, else check group access
			$user_override = get_user_meta($user_id, 'b2bking_user_shipping_payment_methods_override', true);
			if ($user_override === 'manual'){
				// follow user rules

				// For each shipping method, check if it's available to the current user. Add it to available options
				foreach ( $rates as $rate_id => $rate ) {
					$user_access = get_user_meta($user_id, 'b2bking_user_shipping_method_'.$rate->method_id.$rate->instance_id, true);

					// enabled if metadata empty
					if (!metadata_exists('user', $user_id, 'b2bking_user_shipping_method_'.$rate->method_id.$rate->instance_id)){
						$user_access = 1;
					}

					// UPS EXCEPTION
					if ($rate->method_id === 'wf_shipping_ups'){
						$user_access = get_user_meta($user_id,'b2bking_user_shipping_method_'.$rate->method_id.'0', true);
					}
					if (intval($user_access) === 1){
						$available[ $rate_id ] = $rate;
					}
				}

			} else {
				// follow group rules
				$currentusergroupidnr = get_user_meta($user_id,'b2bking_customergroup', true);

				// For each shipping method, check if it's available to the current user's group. Add it to available options
				foreach ( $rates as $rate_id => $rate ) {
					$group_access = get_post_meta($currentusergroupidnr, 'b2bking_group_shipping_method_'.$rate->method_id.$rate->instance_id, true);

					// enabled if metadata empty
					if (!metadata_exists('post', $currentusergroupidnr, 'b2bking_group_shipping_method_'.$rate->method_id.$rate->instance_id)){
						$group_access = 1;
					}
					// UPS EXCEPTION
					if ($rate->method_id === 'wf_shipping_ups'){
						$group_access = get_post_meta($currentusergroupidnr, 'b2bking_group_shipping_method_'.$rate->method_id.'0', true);
					}
					if (intval($group_access) === 1){
						$available[ $rate_id ] = $rate;
					}
				}
			}
		}

		return $available;
		
	} else {

		// Older shipping mechanisms here, non-zone, for cases where needed

		$user_id = get_current_user_id();
		$account_type = get_user_meta($user_id,'b2bking_account_type', true);
		if ($account_type === 'subaccount'){
			// for all intents and purposes set current user as the subaccount parent
			$parent_user_id = get_user_meta($user_id, 'b2bking_account_parent', true);
			$user_id = $parent_user_id;
		}

		$available = array();
		// if user is guest, disable shipping methods by guest group options
		if (intval($user_id) === 0){

			// For each shipping method, check if it's available. Add it to available options
			foreach ( $rates as $rate_id => $rate ) {
				$user_access = get_option('b2bking_logged_out_users_shipping_method_'.$rate->method_id, 1);
				if (intval($user_access) === 1){
					$available[ $rate_id ] = $rate;
				}
			}

		// else if user is B2C, disable by B2C group options
		} else if (get_user_meta($user_id, 'b2bking_b2buser', true ) !== 'yes'){

			// For each shipping method, check if it's available. Add it to available options
			foreach ( $rates as $rate_id => $rate ) {
				$user_access = get_option('b2bking_b2c_users_shipping_method_'.$rate->method_id, 1);
				if (intval($user_access) === 1){
					$available[ $rate_id ] = $rate;
				}
			}

		// else it means user is B2B so follow B2B rules
		} else {

			// if user override activated, check user access, else check group access
			$user_override = get_user_meta($user_id, 'b2bking_user_shipping_payment_methods_override', true);
			if ($user_override === 'manual'){
				// follow user rules

				// For each shipping method, check if it's available to the current user. Add it to available options
				foreach ( $rates as $rate_id => $rate ) {
					$user_access = get_user_meta($user_id, 'b2bking_user_shipping_method_'.$rate->method_id, true);
					if (intval($user_access) === 1){
						$available[ $rate_id ] = $rate;
					}
				}

			} else {
				// follow group rules
				$currentusergroupidnr = get_user_meta($user_id,'b2bking_customergroup', true);

				// For each shipping method, check if it's available to the current user's group. Add it to available options
				foreach ( $rates as $rate_id => $rate ) {
					$group_access = get_post_meta($currentusergroupidnr, 'b2bking_group_shipping_method_'.$rate->method_id, true);
					if (intval($group_access) === 1){
						$available[ $rate_id ] = $rate;
					}
				}
			}
		}

		return $available;
	}


	}
	// Disable payment methods based on user settings (group)
	function b2bking_disable_payment_methods($gateways){

	    global $woocommerce;
		$user_id = get_current_user_id();
		$account_type = get_user_meta($user_id,'b2bking_account_type', true);
		if ($account_type === 'subaccount'){
			// for all intents and purposes set current user as the subaccount parent
			$parent_user_id = get_user_meta($user_id, 'b2bking_account_parent', true);
			$user_id = $parent_user_id;
		}

		// if user is guest, disable shipping methods by guest group options
		if (intval($user_id) === 0){

			foreach ($gateways as $gateway_id => $gateway_value){
				$user_access = get_option('b2bking_logged_out_users_payment_method_'.$gateway_id, 1);

				if (intval($user_access) !== 1){
					unset($gateways[$gateway_id]);
				}
			}

		// else if user is B2C, disable by B2C group options
		} else if (get_user_meta($user_id, 'b2bking_b2buser', true ) !== 'yes'){

			 // if user override activated, check user access, else check group access
			$user_override = get_user_meta($user_id, 'b2bking_user_shipping_payment_methods_override', true);
			if ($user_override === 'manual'){

				// follow user rules
				foreach ($gateways as $gateway_id => $gateway_value){
					$user_access = get_user_meta($user_id, 'b2bking_user_payment_method_'.$gateway_id, true);

					// enabled if metadata empty
					if (!metadata_exists('user', $user_id, 'b2bking_user_payment_method_'.$gateway_id)){
						$user_access = 1;
					}

					if (intval($user_access) !== 1){
						unset($gateways[$gateway_id]);
					}
				}

			} else {

				foreach ($gateways as $gateway_id => $gateway_value){
					$user_access = get_option('b2bking_b2c_users_payment_method_'.$gateway_id, 1);
					if (intval($user_access) !== 1){
						unset($gateways[$gateway_id]);
					}
				}
			}

		// else it means user is B2B so follow B2B rules
		} else {

		    // if user override activated, check user access, else check group access
			$user_override = get_user_meta($user_id, 'b2bking_user_shipping_payment_methods_override', true);
			if ($user_override === 'manual'){

				// follow user rules
				foreach ($gateways as $gateway_id => $gateway_value){
					$user_access = get_user_meta($user_id, 'b2bking_user_payment_method_'.$gateway_id, true);

					// enabled if metadata empty
					if (!metadata_exists('user', $user_id, 'b2bking_user_payment_method_'.$gateway_id)){
						$user_access = 1;
					}

					if (intval($user_access) !== 1){
						unset($gateways[$gateway_id]);
					}
				}

			} else {

				// follow group rules
			    $currentusergroupidnr = get_user_meta($user_id,'b2bking_customergroup', true);

				foreach ($gateways as $gateway_id => $gateway_value){
					$group_access = get_post_meta($currentusergroupidnr, 'b2bking_group_payment_method_'.$gateway_id, true);

					// enabled if metadata empty
					if (!metadata_exists('post', $currentusergroupidnr, 'b2bking_group_payment_method_'.$gateway_id)){
						$group_access = 1;
					}
					
					if (intval($group_access) !== 1){
						unset($gateways[$gateway_id]);
					}
				}
			}
		}

	    return $gateways;
	}


	function b2bking_disable_payment_methods_dynamic_rule($gateways){

			$user_id = get_current_user_id();
	    	$account_type = get_user_meta($user_id,'b2bking_account_type', true);
	    	if ($account_type === 'subaccount'){
	    		// for all intents and purposes set current user as the subaccount parent
	    		$parent_user_id = get_user_meta($user_id, 'b2bking_account_parent', true);
	    		$user_id = $parent_user_id;
	    	}
			$currentusergroupidnr = get_the_author_meta( 'b2bking_customergroup', $user_id );

			$array_who_multiple = array(
		                'relation' => 'OR',
		                array(
		                    'key' => 'b2bking_rule_who_multiple_options',
		                    'value' => 'group_'.$currentusergroupidnr,
		                	'compare' => 'LIKE'
		                ),
		                array(
		                    'key' => 'b2bking_rule_who_multiple_options',
		                    'value' => 'user_'.$user_id,
		                    'compare' => 'LIKE'
		                ),
		            );

			if ($user_id !== 0){
				array_push($array_who_multiple, array(
	                'key' => 'b2bking_rule_who_multiple_options',
	                'value' => 'everyone_registered',
	                'compare' => 'LIKE'
	            ));

				// add rules that apply to all registered b2b/b2c users
				$user_is_b2b = get_user_meta($user_id, 'b2bking_b2buser', true);
				if ($user_is_b2b === 'yes'){
					array_push($array_who_multiple, array(
                        'key' => 'b2bking_rule_who_multiple_options',
                        'value' => 'everyone_registered_b2b',
                        'compare' => 'LIKE'
                    ));
				} else if ($user_is_b2b === 'no'){
					array_push($array_who_multiple, array(
                        'key' => 'b2bking_rule_who_multiple_options',
                        'value' => 'everyone_registered_b2c',
                        'compare' => 'LIKE'
                    ));
				}
			}

			$array_who = array(
                'relation' => 'OR',
                array(
                    'key' => 'b2bking_rule_who',
                    'value' => 'group_'.$currentusergroupidnr
                ),
                array(
                    'key' => 'b2bking_rule_who',
                    'value' => 'user_'.$user_id
                ),
                array(
                    'relation' => 'AND',
                    array(
                        'key' => 'b2bking_rule_who',
                        'value' => 'multiple_options'
                    ),
                    $array_who_multiple
                ),
            );
			// if user is registered, also select rules that apply to all registered users
			if ($user_id !== 0){
				array_push($array_who, array(
		                        'key' => 'b2bking_rule_who',
		                        'value' => 'everyone_registered'
		                    ));

				// add rules that apply to all registered b2b/b2c users
				$user_is_b2b = get_user_meta($user_id, 'b2bking_b2buser', true);
				if ($user_is_b2b === 'yes'){
					array_push($array_who, array(
		                        'key' => 'b2bking_rule_who',
		                        'value' => 'everyone_registered_b2b'
		                    ));
				} else if ($user_is_b2b === 'no'){
					array_push($array_who, array(
		                        'key' => 'b2bking_rule_who',
		                        'value' => 'everyone_registered_b2c'
		                    ));
				}
			}

			// Get all dynamic rules that apply to the user or user's group
			$pmmu_user_ids = get_option('b2bking_have_pmmu_rules_list_ids', '');
			if (!empty($pmmu_user_ids)){
				$pmmu_user_ids = explode(',',$pmmu_user_ids);
			} else {
				$pmmu_user_ids = array();
			}
				
			$pmmu_rules = get_transient('b2bking_pmmu_user_'.get_current_user_id());
			if (!$pmmu_rules){

				$pmmu_rules = get_posts([
					'post_type' => 'b2bking_rule',
					'post_status' => 'publish',
		    		'post__in' => $pmmu_user_ids,
		    		'fields'        => 'ids', // Only get post IDs
		    	  	'numberposts' => -1,
		    	  	'meta_query'=> array(
		                $array_who,
		            )
		    	]);
				set_transient ('b2bking_pmmu_user_'.get_current_user_id(), $pmmu_rules);
			}
			
	    	// if there are pmmu rules
	    	if (!empty($pmmu_rules)){
	    		foreach ($gateways as $gateway_id => $gateway_value){
	    			$minimum = 'no';
	    			// for each rule, check minimum, and find lowest minimum
	    			foreach ($pmmu_rules as $rule){
	    				// check if rule applies to gateway
	    				$rule_paymentmethod = get_post_meta($rule, 'b2bking_rule_paymentmethod', true);
	    				if ($gateway_id === $rule_paymentmethod){
	    					// gateway applies, check minimum
	    					$minimumrule = get_post_meta($rule, 'b2bking_rule_howmuch', true);
	    					if ($minimum === 'no'){
	    						$minimum = $minimumrule;
	    					} else if (floatval($minimumrule) < floatval($minimum)){
	    						$minimum = $minimumrule;
	    					}
	    				}
	    			} 

	    			if ($minimum !== 'no'){
	    				// check if minimum is met, and if it is, unset gateway
	    				$cart_total = WC()->cart->total;
	    				if (floatval($cart_total) < floatval($minimum)) {
	    					unset($gateways[$gateway_id]);
	    				}
	    			}
	    		}

	    	} else {
	    		// do nothing since there are no applicable rules
	    	}

	    return $gateways;
	}

	function enqueue_public_resources(){
		// scripts and styles already registered by default
		wp_enqueue_script('jquery'); 
		wp_enqueue_style('b2bking_main_style', plugins_url('../includes/assets/css/style.css', __FILE__));
    }
    	
}

