<?php

class B2bking {

	function __construct() {

		// Include dynamic rules code
		require_once ( B2BKING_DIR . 'public/class-b2bking-dynamic-rules.php' );
		require_once ( B2BKING_DIR . 'public/class-b2bking-public.php' );


		add_action('init', function(){
			// visibility query for pre_get_posts, must be run on init
			$this->get_visibility_set_transient();
		});

		// if cache is disable, calculate visibile items for pre get posts purposes
		if (intval(get_option( 'b2bking_product_visibility_cache_setting', 0 )) !== 1){
			add_action('init', function(){
				// visibility query for pre_get_posts, must be run on init
				$this->get_visibility_set_transient_live();
			});
		}

		// filter to remove B2BKing in all API requests:
		$run_in_api_requests = true;
		if (apply_filters('b2bking_force_cancel_api_requests', false)){
			if (b2bking()->is_rest_api_request()){
				$run_in_api_requests = false;
			}
		}


		// Get current user
		$user_data_current_user_id = get_current_user_id();

    	$account_type = get_user_meta($user_data_current_user_id,'b2bking_account_type', true);
    	if ($account_type === 'subaccount'){
    		// for all intents and purposes set current user as the subaccount parent
    		$parent_user_id = get_user_meta($user_data_current_user_id, 'b2bking_account_parent', true);
    		$user_data_current_user_id = $parent_user_id;
    	}
		
		if ($run_in_api_requests){		

			add_action('plugins_loaded', function(){

				// make hidden items not purchasable (if has visibility cache)
				if (intval(get_option( 'b2bking_all_products_visible_all_users_setting', 1 )) !== 1){

					if (intval(get_option( 'b2bking_product_visibility_cache_setting', 0 )) === 1){
						if (!is_admin()){
							add_filter( 'woocommerce_is_purchasable', array($this, 'b2bking_hidden_items_not_purchasable'), 10, 2);
							add_filter( 'woocommerce_variation_is_purchasable', array($this, 'b2bking_hidden_items_not_purchasable'), 10, 2);
						}			   				
					}
				}

			});


			// Handle Ajax Requests
			if ( wp_doing_ajax() ){

				// interferes in the product page for some reason with variation loading

				add_action('plugins_loaded', function(){
				
					// Add content shortcode
					add_action( 'init', array($this, 'b2bking_content_shortcode'));

					if (intval(get_option('b2bking_enable_bulk_order_form_setting', 1)) === 1){
				   		if (intval(get_option('b2bking_search_product_description_setting', 0)) === 0){
				   			if (!is_admin()){
				   				// if search product description is disabled, search by title only
			   					add_filter('posts_search', array($this, 'b2bking_search_by_title_only'), 500, 2);
			   				}
					   	}
					}

			   	   	// Check that plugin is enabled
			   	   	if ( get_option( 'b2bking_plugin_status_setting', 'b2b' ) !== 'disabled' ){
			   	   	/* Groups */
			   			// Set up product/category user/user group visibility rules
			   	   		if (intval(get_option( 'b2bking_all_products_visible_all_users_setting', 1 )) !== 1){
				   			if (intval(get_option('b2bking_disable_visibility_setting', 0)) === 0){

				   				add_action( 'woocommerce_product_query', array($this, 'b2bking_product_categories_visibility_rules'), 9999, 1 );

				   				if (intval(get_option( 'b2bking_product_visibility_cache_setting', 0 )) === 1){
				   					// not compat. with hidden priority. Possibly because queries do not work with a non-existant category
				   					if (intval(get_option( 'b2bking_hidden_has_priority_setting', 0 )) === 0){
				   						add_filter( 'get_terms_args', array($this,'b2bking_categories_restrict'), 10, 2 );
				   					}
				   				}

				   				// add compatibiltiy with AJAX SEARCH LITE
				   				add_filter('asp_query_args', array($this, 'asl_query_args_postin'), 10, 1);
				   				add_filter('asl_query_args', array($this, 'asl_query_args_postin'), 10, 1);
				   				add_filter('searchwp_live_search_query_args', array($this, 'swp_query_args_postin'), 10, 1);


				   				// if user is not admin or shop manager
				   				if (apply_filters('b2bking_apply_visibility_in_ajax', true)){
					   				if (!current_user_can( 'manage_woocommerce' )){
					   					// if caching is enabled
					   					if (intval(get_option( 'b2bking_product_visibility_cache_setting', 0 )) === 1){
					   						add_action( 'pre_get_posts', array($this, 'b2bking_product_categories_visibility_rules') );
					   					} else {
					   						// cache is disabled, but we still want to apply pre_get_posts, just in a slower way.
					   						// let's calculate visible items each time and set it to them in pre_get_posts
					   						add_action( 'pre_get_posts', array($this, 'b2bking_product_categories_visibility_rules_live') );
					   					}
					   				}
					   			}
				   			}
				   		}
			   		}

			   		$run_in_api_requests = true;
			   		if (apply_filters('b2bking_force_cancel_api_requests', false)){
			   			if (b2bking()->is_rest_api_request()){
			   				$run_in_api_requests = false;
			   			}
			   		}

			   		if ($run_in_api_requests){

			   			// Show tiered pricing variation in AJAX
			   			if (intval(get_option('b2bking_disable_group_tiered_pricing_setting', 0)) === 0){
			   			
			   				add_filter( 'woocommerce_available_variation', array('B2bking_Public','b2bking_show_tiered_pricing_table_variation'), 10, 3 );

			   			}
				
						if (get_option( 'b2bking_plugin_status_setting', 'b2b' ) === 'b2b' || (get_option( 'b2bking_plugin_status_setting', 'b2b' ) === 'hybrid' && (get_user_meta( get_current_user_id(), 'b2bking_b2buser', true ) === 'yes'))){

							if (intval(get_option('b2bking_disable_dynamic_rule_fixedprice_setting', 0)) === 0){
								// check the number of rules saved in the database
								if (get_option('b2bking_have_fixed_price_rules', 'yes') === 'yes'){
									// check if the user's ID or group is part of the list.
									$list = get_option('b2bking_have_fixed_price_rules_list', 'yes');
									if ($this->b2bking_user_is_in_list($list) === 'yes'){
										add_filter('woocommerce_product_get_price', array( 'B2bking_Dynamic_Rules', 'b2bking_dynamic_rule_fixed_price' ), 9999, 2 );
										add_filter('woocommerce_product_get_regular_price', array( 'B2bking_Dynamic_Rules', 'b2bking_dynamic_rule_fixed_price' ), 9999, 2 );
										// Variations 
										add_filter('woocommerce_product_variation_get_regular_price', array('B2bking_Dynamic_Rules', 'b2bking_dynamic_rule_fixed_price' ), 9999, 2 );
										add_filter('woocommerce_product_variation_get_price', array('B2bking_Dynamic_Rules', 'b2bking_dynamic_rule_fixed_price' ), 9999, 2 );
										add_filter( 'woocommerce_variation_prices_price', array('B2bking_Dynamic_Rules', 'b2bking_dynamic_rule_fixed_price'), 9999, 2 );
										add_filter( 'woocommerce_variation_prices_regular_price', array('B2bking_Dynamic_Rules', 'b2bking_dynamic_rule_fixed_price'), 9999, 2 );
									}
								}
							}
						}

						// Add Discount rule to AJAX product searches
						if (get_option( 'b2bking_plugin_status_setting', 'b2b' ) !== 'disabled' ){
							
							if (intval(get_option('b2bking_disable_dynamic_rule_discount_sale_setting', 0)) === 0){
								if (get_option('b2bking_have_discount_everywhere_rules', 'yes') === 'yes'){
									// check if the user's ID or group is part of the list.
									$list = get_option('b2bking_have_discount_everywhere_rules_list', 'yes');
									if ($this->b2bking_user_is_in_list($list) === 'yes'){
										add_filter( 'woocommerce_product_get_regular_price', array('B2bking_Dynamic_Rules', 'b2bking_dynamic_rule_discount_regular_price'), 9999, 2 );
										add_filter( 'woocommerce_product_variation_get_regular_price', array('B2bking_Dynamic_Rules', 'b2bking_dynamic_rule_discount_regular_price'), 9999, 2 );
										// Generate "sale price" dynamically
										add_filter( 'woocommerce_product_get_sale_price', array('B2bking_Dynamic_Rules', 'b2bking_dynamic_rule_discount_sale_price'), 9999, 2 );
										add_filter( 'woocommerce_product_variation_get_sale_price', array('B2bking_Dynamic_Rules', 'b2bking_dynamic_rule_discount_sale_price'), 9999, 2 );
										add_filter( 'woocommerce_variation_prices_price', array('B2bking_Dynamic_Rules', 'b2bking_dynamic_rule_discount_sale_price'), 9999, 2 );
										add_filter( 'woocommerce_variation_prices_sale_price', array('B2bking_Dynamic_Rules', 'b2bking_dynamic_rule_discount_sale_price'), 9999, 2 );
										add_filter( 'woocommerce_get_variation_prices_hash', array('B2bking_Dynamic_Rules', 'b2bking_dynamic_rule_discount_sale_price_variation_hash'), 99, 1);
										 
										// Displayed formatted regular price + sale price
										add_filter( 'woocommerce_get_price_html', array('B2bking_Dynamic_Rules', 'b2bking_dynamic_rule_discount_display_dynamic_price'), 9999, 2 );
										// Set sale price in Cart
										add_action( 'woocommerce_before_calculate_totals', array('B2bking_Dynamic_Rules', 'b2bking_dynamic_rule_discount_display_dynamic_price_in_cart'), 9999, 1 );
										// Function to make this work for MiniCart as well
										add_filter('woocommerce_cart_item_price',array('B2bking_Dynamic_Rules', 'b2bking_dynamic_rule_discount_display_dynamic_price_in_cart_item'),9999,3);
										
										// Change "Sale!" badge text
										add_filter('woocommerce_sale_flash', array('B2bking_Dynamic_Rules', 'b2bking_dynamic_rule_discount_display_dynamic_sale_badge'), 9999, 3);
									}
								}
							}

							if (intval(get_option('b2bking_disable_dynamic_rule_taxexemption_setting', 0)) === 0){
								if (get_option('b2bking_have_tax_exemption_user_rules', 'yes') === 'yes'){
									// check if the user's ID or group is part of the list.
									$list = get_option('b2bking_have_tax_exemption_user_rules_list', 'yes');
									if ($this->b2bking_user_is_in_list($list) === 'yes'){
										// Dynamic rule Tax Exemption (user)
										add_action( 'init', array('B2bking_Dynamic_Rules', 'b2bking_dynamic_rule_tax_exemption') );

										add_filter( 'option_woocommerce_tax_display_cart', array('B2bking_Dynamic_Rules', 'b2bking_dynamic_rule_tax_exemption_prices_excl_tax_in_shop') );
										add_filter( 'option_woocommerce_tax_display_shop', array('B2bking_Dynamic_Rules', 'b2bking_dynamic_rule_tax_exemption_prices_excl_tax_in_shop') );
										
										add_action( 'woocommerce_cart_totals_before_shipping', array('B2bking_Dynamic_Rules', 'b2bking_dynamic_rule_tax_exemption_fees_display_only'));
										add_action( 'woocommerce_review_order_before_shipping', array('B2bking_Dynamic_Rules', 'b2bking_dynamic_rule_tax_exemption_fees_display_only'));

										// Clear user tax exemption cache when checkout is rendered
										add_action( 'woocommerce_checkout_update_order_review', array($this, 'b2bking_clear_tax_cache_checkout'), 1 );

									}
								}
							}
						}

						if (intval(get_option('b2bking_disable_dynamic_rule_hiddenprice_setting', 0)) === 0){
							if (get_option('b2bking_have_hidden_price_rules', 'yes') === 'yes'){
								// check if the user's ID or group is part of the list.
								$list = get_option('b2bking_have_hidden_price_rules_list', 'yes');
								if ($this->b2bking_user_is_in_list($list) === 'yes'){
									// Add product purchasable filter, so that it works with Bulk Order Form checks
									add_filter( 'woocommerce_get_price_html', array('B2bking_Dynamic_Rules', 'b2bking_dynamic_rule_hidden_price'), 99999, 2 );
									add_filter( 'woocommerce_variation_price_html', array('B2bking_Dynamic_Rules', 'b2bking_dynamic_rule_hidden_price'), 99999, 2 );
									// Dynamic rule Hidden price - disable purchasable
									add_filter( 'woocommerce_is_purchasable', array('B2bking_Dynamic_Rules', 'b2bking_dynamic_rule_hidden_price_disable_purchasable'), 10, 2);
									add_filter( 'woocommerce_variation_is_purchasable', array('B2bking_Dynamic_Rules', 'b2bking_dynamic_rule_hidden_price_disable_purchasable'), 10, 2);
								}
							}
						}

	            		if (intval(get_option('b2bking_disable_dynamic_rule_minmax_setting', 0)) === 0){
	            			if (get_option('b2bking_have_minmax_rules', 'yes') === 'yes'){
	            				// check if the user's ID or group is part of the list.
	            				$list = get_option('b2bking_have_minmax_rules_list', 'yes');
	            				if ($this->b2bking_user_is_in_list($list) === 'yes'){

	            					// set quantity inputs
	            				 	add_filter( 'woocommerce_quantity_input_args', array('B2bking_Dynamic_Rules', 'b2bking_dynamic_minmax_order_amount_quantity'), 100, 2 );
	            					add_filter( 'woocommerce_available_variation', array('B2bking_Dynamic_Rules', 'b2bking_dynamic_minmax_order_amount_quantity_variation'), 100, 3 );
	            				}
	            			}
	            		}
						

						if (intval(get_option('b2bking_disable_dynamic_rule_requiredmultiple_setting', 0)) === 0){
							if (get_option('b2bking_have_required_multiple_rules', 'yes') === 'yes'){
								// check if the user's ID or group is part of the list.
								$list = get_option('b2bking_have_required_multiple_rules_list', 'yes');
								if ($this->b2bking_user_is_in_list($list) === 'yes'){
									// add quantity step in product page
									add_filter( 'woocommerce_quantity_input_args', array('B2bking_Dynamic_Rules', 'b2bking_dynamic_rule_required_multiple_quantity'), 10, 2 );
									add_filter( 'woocommerce_available_variation', array('B2bking_Dynamic_Rules', 'b2bking_dynamic_rule_required_multiple_quantity_variation'), 10, 3 );

									// Set product quantity added to cart (handling ajax add to cart)
									add_filter( 'woocommerce_add_to_cart_quantity',array('B2bking_Dynamic_Rules', 'b2bking_dynamic_rule_required_multiple_quantity_number'), 10, 2 );
									
								}
							}
						}

						if (get_option( 'b2bking_plugin_status_setting', 'b2b' ) !== 'disabled' ){
							if (intval(get_option('b2bking_disable_group_tiered_pricing_setting', 0)) === 0){
								// Add tiered pricing to AJAX as well
								/* Set Tiered Pricing via Fixed Price Dynamic Rule */
								add_filter('woocommerce_product_get_price', array($this, 'b2bking_tiered_pricing_fixed_price'), 9999, 2 );
								add_filter('woocommerce_product_get_regular_price', array($this, 'b2bking_tiered_pricing_fixed_price'), 9999, 2 );
								// Variations 
								add_filter('woocommerce_product_variation_get_regular_price', array($this, 'b2bking_tiered_pricing_fixed_price'), 9999, 2 );
								add_filter('woocommerce_product_variation_get_price', array($this, 'b2bking_tiered_pricing_fixed_price'), 9999, 2 );
								add_filter( 'woocommerce_variation_prices_price', array($this, 'b2bking_tiered_pricing_fixed_price'), 9999, 2 );
								add_filter( 'woocommerce_variation_prices_regular_price', array($this, 'b2bking_tiered_pricing_fixed_price'), 9999, 2 );

								// Pricing and Discounts in the Product Page: Add to AJAX
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

							}

							// Show both B2B and B2C price to B2B users
							if (intval(get_option( 'b2bking_show_b2c_price_setting', 0 )) === 1){
								add_filter( 'woocommerce_get_price_html', array($this, 'b2bking_show_both_prices'), 99995, 2);
							}
						}

						if (get_option('b2bking_have_currency_rules', 'yes') === 'yes'){
							// check if the user's ID or group is part of the list.
							$list = get_option('b2bking_have_currency_rules_list', 'yes');
							if ($this->b2bking_user_is_in_list($list) === 'yes'){

								add_filter('woocommerce_currency_symbol', array('B2bking_Dynamic_Rules', 'b2bking_dynamic_rule_currency_symbol'), 10, 2);

								add_filter( 'option_woocommerce_currency', array('B2bking_Dynamic_Rules', 'b2bking_dynamic_rule_currency'));
							}
						}


						if (!is_user_logged_in()){
							if (get_option('b2bking_guest_access_restriction_setting', 'hide_prices') === 'hide_prices'){	
								add_filter( 'woocommerce_get_price_html', array($this, 'b2bking_hide_prices_guest_users'), 9999, 2 );
								add_filter( 'woocommerce_variation_get_price_html', array($this, 'b2bking_hide_prices_guest_users'), 9999, 2 );
								// Hide add to cart button as well / purchasable capabilities
								add_filter( 'woocommerce_is_purchasable', array($this, 'b2bking_disable_purchasable_guest_users'));
								add_filter( 'woocommerce_variation_is_purchasable', array($this, 'b2bking_disable_purchasable_guest_users'));
							}
						}

						// Replace prices with quotes in AJAX - copied from public
						// Replace with Request a Quote
						if ($this->dynamic_replace_prices_with_quotes() === 'yes' || (get_option('b2bking_guest_access_restriction_setting', 'hide_prices') === 'replace_prices_quote') && (!is_user_logged_in() || (intval(get_option( 'b2bking_multisite_separate_b2bb2c_setting', 0 )) === 1 && get_user_meta($user_data_current_user_id, 'b2bking_b2buser', true) !== 'yes')) ){
							// Hide prices
							add_filter( 'woocommerce_get_price_html', array($this, 'b2bking_hide_prices_request_quote'), 9999, 2 );
							add_filter( 'woocommerce_variation_get_price_html', array($this, 'b2bking_hide_prices_request_quote'), 9999, 2 );
							// Replace "Add to cart" with "Request a quote"
							add_filter('woocommerce_product_single_add_to_cart_text', array($this,'b2bking_replace_add_to_cart_text'));
							add_filter('woocommerce_product_add_to_cart_text', array($this,'b2bking_replace_add_to_cart_text'));
							
							add_action('wp_loaded', function(){
								$offer_in_cart = 'no';
								if (is_user_logged_in()){
									if ($this->user_has_offer_in_cart() === 'yes'){
										$offer_in_cart = 'yes';
									}
								}
								if ($offer_in_cart === 'no'){
									// Hide prices on cart page
									add_filter( 'woocommerce_cart_item_price', array($this, 'b2bking_hide_prices_cart'), 10, 3 );
									add_filter( 'woocommerce_cart_item_subtotal', array($this, 'b2bking_hide_prices_cart'), 10, 3 );
									add_filter( 'woocommerce_cart_subtotal', array($this, 'b2bking_hide_prices_cart'), 10, 3 );
									add_filter( 'woocommerce_cart_total', array($this, 'b2bking_hide_prices_cart'), 10, 3 );
								}
							});


							// Hide "on sale" flash badge
							add_filter( 'woocommerce_sale_flash', '__return_false' );
							// Hide coupon
							add_filter( 'woocommerce_coupons_enabled', '__return_false' );

							// If user is logged in, disable offers, bulk order form, purchase lists as they no longer apply
							add_action('wp_loaded', function(){
								if (is_user_logged_in()){
									
									// Get current user
									$user_id = get_current_user_id();

							    	$account_type = get_user_meta($user_id,'b2bking_account_type', true);
							    	if ($account_type === 'subaccount'){
							    		// for all intents and purposes set current user as the subaccount parent
							    		$parent_user_id = get_user_meta($user_id, 'b2bking_account_parent', true);
							    		$user_id = $parent_user_id;
							    	}
							    	set_transient('b2bking_replace_prices_quote_user_'.$user_id, 'yes');

							    	// check if the user has an offer in CART. IF YES, make all other items except offers unpurchasable and disable purchase restrictions

							    	if ($this->user_has_offer_in_cart() === 'yes'){
							    		
							    		// make all other items unpurchasable
							    		add_filter( 'woocommerce_is_purchasable', array($this, 'b2bking_disable_purchasable_except_offers'), 999, 2);
							    		add_filter( 'woocommerce_variation_is_purchasable', array($this, 'b2bking_disable_purchasable_except_offers'), 999, 2);

							    		// show message in cart that other products can't be added to quote while you have an offer in cart
							    		add_action( 'woocommerce_before_cart', array($this,'b2bking_cannot_quote_offer_cart_message'), 100);

							    	}
								
								}
							});
							

						}


					}

					// copy subaccount data
					add_action('b2bking_after_subaccount_created', array($this, 'b2bking_copy_data'), 10, 1);

					
				});

				// Conversations
				add_action( 'wp_ajax_b2bkingconversationmessage', array($this, 'b2bkingconversationmessage') );
	    		add_action( 'wp_ajax_nopriv_b2bkingconversationmessage', array($this, 'b2bkingconversationmessage') );
	    		add_action( 'wp_ajax_b2bkingsendinquiry', array($this, 'b2bkingsendinquiry') );
	    		add_action( 'wp_ajax_nopriv_b2bkingsendinquiry', array($this, 'b2bkingsendinquiry') );
	    		// Request custom quote from cart
	    		add_action( 'wp_ajax_b2bkingrequestquotecart', array($this, 'b2bkingrequestquotecart') );
	    		add_action( 'wp_ajax_nopriv_b2bkingrequestquotecart', array($this, 'b2bkingrequestquotecart') );
	    		// Quote file upload
	    		add_action( 'wp_ajax_b2bkingquoteupload', array($this, 'b2bkingquoteupload') );
	    		add_action( 'wp_ajax_nopriv_b2bkingquoteupload', array($this, 'b2bkingquoteupload') );
	    		// Add offer to cart
	    		add_action( 'wp_ajax_b2bkingaddoffer', array($this, 'b2bkingaddoffer') );
	    		add_action( 'wp_ajax_nopriv_b2bkingaddoffer', array($this, 'b2bkingaddoffer') );
	    		// Add credit to cart
	    		add_action( 'wp_ajax_b2bkingaddcredit', array($this, 'b2bkingaddcredit') );
	    		add_action( 'wp_ajax_nopriv_b2bkingaddcredit', array($this, 'b2bkingaddcredit') );
	    		// Approve and Reject users
	    		add_action( 'wp_ajax_b2bkingapproveuser', array($this, 'b2bkingapproveuser') );
	    		add_action( 'wp_ajax_nopriv_b2bkingapproveuser', array($this, 'b2bkingapproveuser') );
	    		add_action( 'wp_ajax_b2bkingrejectuser', array($this, 'b2bkingrejectuser') );
	    		add_action( 'wp_ajax_nopriv_b2bkingrejectuser', array($this, 'b2bkingrejectuser') );
	    		add_action( 'wp_ajax_b2bkingdeactivateuser', array($this, 'b2bkingdeactivateuser') );
	    		add_action( 'wp_ajax_nopriv_b2bkingdeactivateuser', array($this, 'b2bkingdeactivateuser') );
	    		// Download file (e.g. registration files, company license etc)
	    		add_action( 'wp_ajax_b2bkinghandledownloadrequest', array($this, 'b2bkinghandledownloadrequest') );
	    		// Subaccounts
	    		add_action( 'wp_ajax_nopriv_b2bking_create_subaccount', array($this, 'b2bking_create_subaccount') );
	    		add_action( 'wp_ajax_b2bking_create_subaccount', array($this, 'b2bking_create_subaccount') );
	    		add_action( 'wp_ajax_nopriv_b2bking_update_subaccount', array($this, 'b2bking_update_subaccount') );
	    		add_action( 'wp_ajax_b2bking_update_subaccount', array($this, 'b2bking_update_subaccount') );

	    		add_action( 'wp_ajax_nopriv_b2bking_approve_order', array($this, 'b2bking_approve_order') );
	    		add_action( 'wp_ajax_b2bking_approve_order', array($this, 'b2bking_approve_order') );	    		
	    		add_action( 'wp_ajax_nopriv_b2bking_reject_order', array($this, 'b2bking_reject_order') );
	    		add_action( 'wp_ajax_b2bking_reject_order', array($this, 'b2bking_reject_order') );
	    		// Bulk order
	    		add_action( 'wp_ajax_nopriv_b2bking_ajax_search', array($this, 'b2bking_ajax_search') );
	    		add_action( 'wp_ajax_b2bking_ajax_search', array($this, 'b2bking_ajax_search') );

	    		add_action( 'wp_ajax_nopriv_b2bking_accountingsubtotals', array($this, 'b2bking_accountingsubtotals') );
	    		add_action( 'wp_ajax_b2bking_accountingsubtotals', array($this, 'b2bking_accountingsubtotals') );

	    		add_action( 'wp_ajax_nopriv_b2bking_ajax_get_price', array($this, 'b2bking_ajax_get_price') );
	    		add_action( 'wp_ajax_b2bking_ajax_get_price', array($this, 'b2bking_ajax_get_price') );
	    		add_action( 'wp_ajax_nopriv_b2bking_bulkorder_add_cart', array($this, 'b2bking_bulkorder_add_cart') );
	    		add_action( 'wp_ajax_b2bking_bulkorder_add_cart', array($this, 'b2bking_bulkorder_add_cart') );

	    		add_action( 'wp_ajax_nopriv_b2bking_bulkorder_add_cart_item', array($this, 'b2bking_bulkorder_add_cart_item') );
	    		add_action( 'wp_ajax_b2bking_bulkorder_add_cart_item', array($this, 'b2bking_bulkorder_add_cart_item') );

	    		add_action( 'wp_ajax_nopriv_b2bking_bulkorder_save_list', array($this, 'b2bking_bulkorder_save_list') );
	    		add_action( 'wp_ajax_b2bking_bulkorder_save_list', array($this, 'b2bking_bulkorder_save_list') );
	    		// Purchase lists
	    		add_action( 'wp_ajax_nopriv_b2bking_purchase_list_update', array($this, 'b2bking_purchase_list_update') );
	    		add_action( 'wp_ajax_b2bking_purchase_list_update', array($this, 'b2bking_purchase_list_update') );
	    		add_action( 'wp_ajax_nopriv_b2bking_purchase_list_delete', array($this, 'b2bking_purchase_list_delete') );
	    		add_action( 'wp_ajax_b2bking_purchase_list_delete', array($this, 'b2bking_purchase_list_delete') );
	    		add_action( 'wp_ajax_nopriv_b2bking_save_cart_to_purchase_list', array($this, 'b2bking_save_cart_to_purchase_list') );
	    		add_action( 'wp_ajax_b2bking_save_cart_to_purchase_list', array($this, 'b2bking_save_cart_to_purchase_list') );
	    		// Dismiss "activate woocommerce" admin notice permanently
	    		add_action( 'wp_ajax_b2bking_dismiss_activate_woocommerce_admin_notice', array($this, 'b2bking_dismiss_activate_woocommerce_admin_notice') );
	    		// Save Special group settings (b2c and guests) in groups
	    		add_action( 'wp_ajax_nopriv_b2bking_b2c_special_group_save_settings', array($this, 'b2bking_b2c_special_group_save_settings') );
	    		add_action( 'wp_ajax_b2bking_b2c_special_group_save_settings', array($this, 'b2bking_b2c_special_group_save_settings') );
	    		add_action( 'wp_ajax_nopriv_b2bking_logged_out_special_group_save_settings', array($this, 'b2bking_logged_out_special_group_save_settings') );
	    		add_action( 'wp_ajax_b2bking_logged_out_special_group_save_settings', array($this, 'b2bking_logged_out_special_group_save_settings') );
	    		// Tools
	    		add_action( 'wp_ajax_nopriv_b2bkingdownloadpricelist', array($this, 'b2bkingdownloadpricelist') );
	    		add_action( 'wp_ajax_b2bkingdownloadpricelist', array($this, 'b2bkingdownloadpricelist') );

	    		add_action( 'wp_ajax_nopriv_b2bkingdownloadpurchaselist', array($this, 'b2bkingdownloadpurchaselist') );
	    		add_action( 'wp_ajax_b2bkingdownloadpurchaselist', array($this, 'b2bkingdownloadpurchaselist') );
	    		
	    		add_action( 'wp_ajax_nopriv_b2bkingbulksetusers', array($this, 'b2bkingbulksetusers') );
	    		add_action( 'wp_ajax_b2bkingbulksetusers', array($this, 'b2bkingbulksetusers') );
	    		add_action( 'wp_ajax_nopriv_b2bkingbulksetcategory', array($this, 'b2bkingbulksetcategory') );
	    		add_action( 'wp_ajax_b2bkingbulksetcategory', array($this, 'b2bkingbulksetcategory') );
	    		add_action( 'wp_ajax_nopriv_b2bkingbulksetsubaccounts', array($this, 'b2bkingbulksetsubaccounts') );
	    		add_action( 'wp_ajax_b2bkingbulksetsubaccounts', array($this, 'b2bkingbulksetsubaccounts') );
	    		add_action( 'wp_ajax_nopriv_b2bkingbulksetsubaccountsregular', array($this, 'b2bkingbulksetsubaccountsregular') );
	    		add_action( 'wp_ajax_b2bkingbulksetsubaccountsregular', array($this, 'b2bkingbulksetsubaccountsregular') );
	    		// Backend Customers Panel
	    		add_action( 'wp_ajax_nopriv_b2bking_admin_customers_ajax', array($this, 'b2bking_admin_customers_ajax') );
	    		add_action( 'wp_ajax_b2bking_admin_customers_ajax', array($this, 'b2bking_admin_customers_ajax') );
	    		// Backend Update User Data
	    		add_action( 'wp_ajax_nopriv_b2bkingupdateuserdata', array($this, 'b2bkingupdateuserdata') );
	    		add_action( 'wp_ajax_b2bkingupdateuserdata', array($this, 'b2bkingupdateuserdata') );
	    		// Validate VAT for checkout registration 
	    		add_action( 'wp_ajax_nopriv_b2bkingvalidatevat', array($this, 'b2bkingvalidatevat') );
	    		add_action( 'wp_ajax_b2bkingvalidatevat', array($this, 'b2bkingvalidatevat') );
	    		// Check delivery country for VAT Validation
	    		add_action( 'wp_ajax_nopriv_b2bkingcheckdeliverycountryvat', array($this, 'b2bkingcheckdeliverycountryvat') );
	    		add_action( 'wp_ajax_b2bkingcheckdeliverycountryvat', array($this, 'b2bkingcheckdeliverycountryvat') );


	    		// Variations price in bulk in backend
	    		add_action( 'wp_ajax_nopriv_b2bkingbulksetvariationprices', array($this, 'b2bkingbulksetvariationprices') );
	    		add_action( 'wp_ajax_b2bkingbulksetvariationprices', array($this, 'b2bkingbulksetvariationprices') );

	    		// Backend notifications
	    		add_action( 'wp_ajax_b2bking_dismiss_groups_howto_admin_notice', array($this, 'b2bking_dismiss_groups_howto_admin_notice') );
	    		add_action( 'wp_ajax_b2bking_dismiss_groupsrules_howto_admin_notice', array($this, 'b2bking_dismiss_groupsrules_howto_admin_notice') );

	    		add_action( 'wp_ajax_b2bking_dismiss_quotefields_howto_admin_notice', array($this, 'b2bking_dismiss_quotefields_howto_admin_notice') );


	    		add_action( 'wp_ajax_b2bking_dismiss_customers_howto_admin_notice', array($this, 'b2bking_dismiss_customers_howto_admin_notice') );
	    		add_action( 'wp_ajax_b2bking_dismiss_conversations_howto_admin_notice', array($this, 'b2bking_dismiss_conversations_howto_admin_notice') );
	    		add_action( 'wp_ajax_b2bking_dismiss_rules_howto_admin_notice', array($this, 'b2bking_dismiss_rules_howto_admin_notice') );
	    		add_action( 'wp_ajax_b2bking_dismiss_roles_howto_admin_notice', array($this, 'b2bking_dismiss_roles_howto_admin_notice') );
	    		add_action( 'wp_ajax_b2bking_dismiss_fields_howto_admin_notice', array($this, 'b2bking_dismiss_fields_howto_admin_notice') );
	    		add_action( 'wp_ajax_b2bking_dismiss_offers_howto_admin_notice', array($this, 'b2bking_dismiss_offers_howto_admin_notice') );
	    		// Dismiss onboarding admin notice permanently
	    		add_action( 'wp_ajax_b2bking_dismiss_onboarding_admin_notice', array( $this, 'b2bking_dismiss_onboarding_admin_notice' ) );

	    		// Email Offers
	    		add_action( 'wp_ajax_nopriv_b2bkingemailoffer', array($this, 'b2bkingemailoffer') );
	    		add_action( 'wp_ajax_b2bkingemailoffer', array($this, 'b2bkingemailoffer') );


	    		// Clear Caches Tool
	    		add_action( 'wp_ajax_nopriv_b2bkingclearcaches', array($this, 'b2bkingclearcaches') );
	    		add_action( 'wp_ajax_b2bkingclearcaches', array($this, 'b2bkingclearcaches') );

	    		// Core installer
	    		add_action( 'wp_ajax_b2bking_core_install', array( $this, 'install_b2bking_core' ) );

	    		// Get page content function
				add_action( 'wp_ajax_b2bking_get_page_content', array($this, 'b2bking_get_page_content') );
	    		add_action( 'wp_ajax_nopriv_b2bking_get_page_content', array($this, 'b2bking_get_page_content') );
	    		

	    		// Get quantity in stock for bulk order forms
	    		
	    		add_action( 'wp_ajax_nopriv_b2bking_get_stock_quantity_addable', array($this, 'b2bking_get_stock_quantity_addable') );
	    		add_action( 'wp_ajax_b2bking_get_stock_quantity_addable', array($this, 'b2bking_get_stock_quantity_addable') );

			}			
		}
		
		// add custom billing fields to admin new order email
		add_action('woocommerce_email_customer_details', array($this, 'b2bking_add_billing_fields_admin_email'), 999, 4);

		add_action( 'woocommerce_order_details_after_customer_details', array($this, 'b2bking_add_billing_fields_admin_data'), 10, 1 );
		

		// Add invoice gateway
		add_filter( 'woocommerce_payment_gateways',  array( $this, 'b2bking_add_invoice_gateway' ) );
		// Add approval gateway

		if (intval(get_option( 'b2bking_enable_company_approval_setting', 0 )) === 1){
			add_filter( 'woocommerce_payment_gateways',  array( $this, 'b2bking_add_approval_gateway' ) );
			add_filter( 'woocommerce_order_button_text', array( $this, 'b2bking_place_order_approval_text' ) );
		}
		// Add purchase order gateway
		add_filter( 'woocommerce_payment_gateways',  array( $this, 'b2bking_add_purchase_order_gateway' ) );
		add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'b2bking_display_order_number' ) );
		add_action( 'woocommerce_email_after_order_table', array( $this, 'b2bking_display_order_number' ) );
		add_action( 'woocommerce_order_details_after_order_table', array( $this, 'b2bking_display_order_number' ) );
		add_action( 'wc_pip_after_body', array( $this, 'b2bking_po_number_pip' ), 10, 4 );

		// Add email classes
		add_filter( 'woocommerce_email_classes', array($this, 'b2bking_add_email_classes'));
		// Add extra email actions (account approved finish)
		add_filter( 'woocommerce_email_actions', array($this, 'b2bking_add_email_actions'));
		// Include metadata in REST API
		add_action('rest_api_init', array($this, 'register_metadata'));
		// flush cache for scheduled
		add_action( 'transition_post_status', array($this,'b2bking_flush_cache_scheduled'), 10, 3 );
		add_action( 'save_post', array($this,'b2bking_flush_cache_for_api'), 10, 1 );
		add_action( "rest_insert_b2bking_rule", array($this,'b2bking_flush_cache_for_api_rule'), 10, 3 );

		// woocommerce importer columns names
		add_filter( 'woocommerce_csv_product_import_mapping_options', array($this,'b2bking_woo_importer_columns_display'), 10000, 1 );

		// woocommerce importer process
		add_filter('woocommerce_product_import_pre_insert_product_object', array($this,'b2bking_woo_importer_columns_process'), 10, 2);


		// Add variation bulk edit options
		add_action('woocommerce_variable_product_bulk_edit_actions', array($this,'b2bking_bulk_edit_variations'));

		// customer data in AJAX (in admin new order, this gets user custom fields)
		add_filter('woocommerce_ajax_get_customer_details', array($this,'b2bking_custom_woocommerce_ajax_get_customer_details'), 10, 3);

		// Offers stock
		add_filter('woocommerce_hidden_order_itemmeta', array($this, 'hidden_order_itemmeta'), 50);

		// on order processed reduce stock, on order cancelarray($this,led add stock back
		add_action( 'woocommerce_order_status_processing', array($this, 'decrease_offer_stock_quantity'), 10, 1);
		add_action( 'woocommerce_order_status_cancelled', array($this,'increase_offer_stock_quantity'), 10, 1);

		// Run Admin/Public code 
		if ( is_admin() ) { 
			require_once B2BKING_DIR . '/admin/class-b2bking-admin.php';
			global $b2bking_admin;
			$b2bking_admin = new B2bking_Admin();
		} else if ( !$this->b2bking_is_login_page() ) {
			global $b2bking_public;
			$b2bking_public = new B2bking_Public();
		}

		// give parent account capability to pay for subaccount order
		add_filter( 'user_has_cap', [$this, 'pay_for_order_capability'], 10, 3 );


		// Noindex, nofollow OFFER and CREDIT products
		add_filter( 'wpseo_robots', array($this, 'seo_robots_remove_single' )); //add Yoast filter for meta
		add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', array($this, 'exclude_posts_from_xml_sitemaps' ));

		add_action('wp_loaded', function(){
			// Lost password URL in WP ADMIN
			if (isset($GLOBALS['pagenow'])){
				if (in_array( $GLOBALS['pagenow'],array( 'wp-login.php', 'wp-register.php', 'admin.php' ),  true  )){
					remove_filter( 'lostpassword_url', 'wc_lostpassword_url', 10, 1 );
				}
			}
		});
		
		// Stock Features
		add_action('plugins_loaded', function(){

			add_action('woocommerce_before_mini_cart', function(){
				global $b2bking_is_mini_cart; $b2bking_is_mini_cart = true;
			});
			add_action('woocommerce_after_mini_cart', function(){
				global $b2bking_is_mini_cart; $b2bking_is_mini_cart = false;
			});


			// Hide stock on frontend for B2C
			$hidestock = get_option( 'b2bking_hide_stock_for_b2c_setting', 'disabled' );
			$is_b2b_user = get_user_meta(get_current_user_id(),'b2bking_b2buser', true);
			if ($is_b2b_user !== 'yes'){
				if ($hidestock === 'hidecompletely'){
				    add_filter( 'woocommerce_get_stock_html', function($html, $product){
				        return '';
				    }, 10, 2 );    
				} else if ($hidestock === 'hideprecision'){
					add_filter('option_woocommerce_stock_format', function($val){
						return 'no_amount';
					}, 10, 1);
				}
			}

			// Different Stock for B2B & B2C
			$stocktreatment = get_option( 'b2bking_different_stock_treatment_b2b_setting', 'disabled' );
			if ($stocktreatment === 'b2b'){
				// add option to simple products
				add_action('woocommerce_product_options_stock_status', array($this,'b2bking_simple_product_stock_status_change'));

				// add option to variable products
				add_action('woocommerce_variation_options_inventory', array($this,'b2bking_variable_product_stock_status_change'), 10, 3);
				add_action('woocommerce_save_product_variation', array($this,'b2bking_variable_product_stock_save'), 10, 2);

				// filter data
				if (!is_admin()){
					add_filter('woocommerce_product_get_stock_status', array($this,'b2bking_stock_filter_stock_status'), 10, 2);
					add_filter('woocommerce_product_get_stock_quantity', array($this,'b2bking_stock_filter_stock_quantity'), 10, 2);
					add_filter('woocommerce_product_get_backorders', array($this,'b2bking_stock_filter_backorders'), 10, 2);

					//variation
					add_filter('woocommerce_product_variation_get_stock_status', array($this,'b2bking_variable_stock_filter_stock_status'), 10, 2);
					add_filter('woocommerce_product_variation_get_stock_quantity', array($this,'b2bking_variable_stock_filter_stock_quantity'), 10, 2);
					add_filter('woocommerce_product_variation_get_backorders', array($this,'b2bking_variable_stock_filter_backorders'), 10, 2);

				}

				// filter stock changes
				remove_action( 'woocommerce_payment_complete', 'wc_maybe_reduce_stock_levels' );
				remove_action( 'woocommerce_order_status_completed', 'wc_maybe_reduce_stock_levels' );
				remove_action( 'woocommerce_order_status_processing', 'wc_maybe_reduce_stock_levels' );
				remove_action( 'woocommerce_order_status_on-hold', 'wc_maybe_reduce_stock_levels' );
				remove_action( 'woocommerce_order_status_cancelled', 'wc_maybe_increase_stock_levels' );
				remove_action( 'woocommerce_order_status_pending', 'wc_maybe_increase_stock_levels' );
				add_action( 'woocommerce_order_status_cancelled', array($this, 'b2bking_maybe_increase_stock_levels' ));
				add_action( 'woocommerce_order_status_pending', array($this, 'b2bking_maybe_increase_stock_levels' ));
				add_action( 'woocommerce_payment_complete', array($this, 'b2bking_maybe_reduce_stock_levels' ));
				add_action( 'woocommerce_order_status_completed', array($this, 'b2bking_maybe_reduce_stock_levels' ));
				add_action( 'woocommerce_order_status_processing', array($this, 'b2bking_maybe_reduce_stock_levels' ));
				add_action( 'woocommerce_order_status_on-hold', array($this, 'b2bking_maybe_reduce_stock_levels' ));				

				// save stock
				add_action( 'save_post', array($this,'b2bking_save_stock_settings'), 10, 1 );

			} else if ($stocktreatment === 'b2binstock'){

				$currentuserid = get_current_user_id();
				if (get_user_meta($currentuserid,'b2bking_account_type', true) === 'subaccount'){
					$currentuserid = get_user_meta($currentuserid, 'b2bking_account_parent', true);
				}

				// always in stock for B2B
				$is_b2b_user = get_user_meta($currentuserid,'b2bking_b2buser', true);
				if ($is_b2b_user === 'yes'){
				    // Enable backorders on all products
				    add_filter( 'woocommerce_product_get_backorders', array($this, 'filter_get_backorders_callback'), 10, 2 );
				    add_filter( 'woocommerce_product_variation_get_backorders', array($this, 'filter_get_backorders_callback'), 10, 2 );
				    
				    // Change all products stock statuses to 'instock'
				    add_filter( 'woocommerce_product_get_stock_status', array($this, 'filter_get_stock_status_callback'), 10, 2 );
				    add_filter( 'woocommerce_product_variation_get_stock_status', array($this, 'filter_get_stock_status_callback'), 10, 2 );
				    
				}
			}
		});

		// add shortcode for custom info table
		add_action( 'init', array($this, 'b2bking_product_information_shortcode'));

		add_action( 'delete_user', array($this, 'clear_user_rules'), 10 );

		// fix issue with saving metadata during woo import, define data to let us know import is running
		add_action('woocommerce_product_import_before_import', function($data){
			if (!defined('B2BKING_WOO_IMPORT_RUNNING')){
				define('B2BKING_WOO_IMPORT_RUNNING', 1);
			}
		}, 10, 1);

		// Company Order approval
		if (intval(get_option( 'b2bking_enable_company_approval_setting', 0 )) === 1){
			// add 'Pending company approval status'
			add_action( 'init', array($this, 'b2bking_register_status') );
			if (!is_admin()){
				add_filter( 'wc_order_statuses', array($this, 'b2bking_add_status'), 100, 1 );
			}
		}
	}

	function b2bking_register_status() {

		register_post_status( 'wc-pcompany', array(
			'label'		=> 'Pending company approval',
			'public'	=> true,
			'show_in_admin_all_list'    => false,
			'show_in_admin_status_list' => false, // show count All (12) , Completed (9) , Credit purchase (2) ...
			'label_count'	=> _n_noop( 'Pending company approval (%s)', 'Pending company approval (%s)' )
		) );

		// set up option to exclude status in woocommerce reports
		$ran_already = get_option('marketking_pcompany_status_ran');
		if ($ran_already !== 'yes'){

			$excluded_statuses = get_option( 'woocommerce_excluded_report_order_statuses', array( 'pending', 'failed', 'cancelled' ) );
			$statuses = array_merge( array( 'pcompany' ), $excluded_statuses );
			update_option('woocommerce_excluded_report_order_statuses', $statuses);
			update_option('marketking_pcompany_status_ran', 'yes');
		}
		
	}
	function b2bking_add_status( $wc_statuses_arr ) {

		$new_statuses_arr = array();

		// add new order status after processing
		foreach ( $wc_statuses_arr as $id => $label ) {
			$new_statuses_arr[ $id ] = $label;

			if ( 'wc-completed' === $id ) { // after "Completed" status
				$new_statuses_arr['wc-pcompany'] = 'Pending company approval';
			}
		}

		return $new_statuses_arr;

	}

	// delete user dynamic rules when that user is deleted
	function clear_user_rules( $user_id ) {
    	$user_rules = get_posts([
    		'post_type' => 'b2bking_rule',
    	  	'post_status' => 'publish',
    	  	'numberposts' => -1,
    	  	'fields' => 'ids',
    	  	'meta_query'=> array(
                'relation' => 'AND',
                array(
                        'key' => 'b2bking_rule_who',
                        'value' => 'user_'.$user_id
                    )
            )
    	]);

    	foreach ($user_rules as $rule_id){
    		wp_delete_post($rule_id);
    	}
	}

	function b2bking_product_information_shortcode(){
		add_shortcode('b2bking_product_information_table', array($this, 'b2bking_show_custom_information_table_shortcode_content'));
		add_shortcode('b2bking_tiered_pricing_table', array($this, 'b2bking_tiered_pricing_table_shortcode_content'));
	}

	function b2bking_show_custom_information_table_shortcode_content($atts){
		$atts = shortcode_atts(
	        array(
	            'id' => "",
	        ), 
	    $atts);

	    $prodid = $atts['id'];

	    ob_start();
	    B2bking_Public::b2bking_show_custom_information_table($prodid);
	    $content = ob_get_clean();
	    return $content;
	}

	function b2bking_tiered_pricing_table_shortcode_content($atts){
		$atts = shortcode_atts(
	        array(
	            'id' => "",
	        ), 
	    $atts);

	    $prodid = $atts['id'];

	    ob_start();
	    B2bking_Public::b2bking_show_tiered_pricing_table($prodid);
	    $content = ob_get_clean();
	    return $content;
	}


	function b2bking_woo_importer_columns_display( $mappings ){
		$options = $mappings['price']['options'];
		// generate price options based on group
		$new_options = array();
		$groups = get_posts([
		  'post_type' => 'b2bking_group',
		  'post_status' => 'publish',
		  'numberposts' => -1,
		]);

		//b2c tiered pricing
		$new_options['b2bking_product_pricetiers_group_b2c'] = $group->post_title.esc_html__( 'B2C Price Tiers', 'b2bking' );

		foreach ($groups as $group){

			
			$new_options['b2bking_regular_product_price_group_'.$group->ID] = $group->post_title.esc_html__( ' Regular Price', 'b2bking' );
			$new_options['b2bking_sale_product_price_group_'.$group->ID] = $group->post_title.esc_html__( ' Sale Price', 'b2bking' );
			$new_options['b2bking_product_pricetiers_group_'.$group->ID] = $group->post_title.esc_html__( ' Price Tiers', 'b2bking' );
		}
		$generic_mappings = array( 
			'price'  => array(
				'name'    => __( 'Price', 'woocommerce' ),
				'options' => array_merge($options, $new_options),
			),
		);

		return array_merge( $mappings, $generic_mappings );
	}

	function b2bking_woo_importer_columns_process($object, $data){

		$groups = get_posts([
		  'post_type' => 'b2bking_group',
		  'post_status' => 'publish',
		  'numberposts' => -1,
		]);

		foreach ($groups as $group){
			if (isset($data['b2bking_regular_product_price_group_'.$group->ID])) {
				$object->update_meta_data('b2bking_regular_product_price_group_'.$group->ID, $data['b2bking_regular_product_price_group_'.$group->ID]);
			}
			if (isset($data['b2bking_sale_product_price_group_'.$group->ID])) {
				$object->update_meta_data('b2bking_sale_product_price_group_'.$group->ID, $data['b2bking_sale_product_price_group_'.$group->ID]);
			}

			if (isset($data['b2bking_product_pricetiers_group_'.$group->ID])) {
				$object->update_meta_data('b2bking_product_pricetiers_group_'.$group->ID, $data['b2bking_product_pricetiers_group_'.$group->ID]);
			}

		}
		// b2c price tiers
		if (isset($data['b2bking_product_pricetiers_group_b2c'])) {
			$object->update_meta_data('b2bking_product_pricetiers_group_b2c', $data['b2bking_product_pricetiers_group_b2c']);
		}

		return $object;
	}


	function b2bking_hidden_items_not_purchasable($purchasable, $product){

		$current_product_id = intval($product->get_id());

		$currentuserid = get_current_user_id();
		// if salesking agent, get visibility of sales agent
    	if ($this->check_user_is_agent_with_access()){
			$agent_id = $this->get_current_agent_id();
			$currentuserid = $agent_id;
		}

		$account_type = get_user_meta($currentuserid,'b2bking_account_type', true);
		if ($account_type === 'subaccount'){
			// for all intents and purposes set current user as the subaccount parent
			$parent_user_id = get_user_meta($currentuserid, 'b2bking_account_parent', true);
			$currentuserid = $parent_user_id;
		}

		if (!defined('ICL_LANGUAGE_NAME_EN')){
			$allTheIDs = get_transient('b2bking_user_'.$currentuserid.'_ajax_visibility');
		} else {
			$allTheIDs = get_transient('b2bking_user_'.$currentuserid.'_ajax_visibility'.ICL_LANGUAGE_NAME_EN);
		}

		$allTheIDs = apply_filters('b2bking_ids_post_in_visibility', $allTheIDs);

		$offer_id = intval(get_option('b2bking_offer_product_id_setting', 0));
		$credit_id = intval(get_option('b2bking_credit_product_id_setting', 0));

		// if user is guest, or multisite b2b/b2b separation is enabled and user should be treated as guest
		if (!in_array($current_product_id, $allTheIDs) && $current_product_id !== $offer_id && $current_product_id !== $credit_id){
			$purchasable = false;
		}

		return $purchasable;

	}

	function seo_robots_remove_single( $robots ) {

		// if offer or credit product
		$offer_id = intval(get_option('b2bking_offer_product_id_setting', 0));
		$credit_id = intval(get_option('b2bking_credit_product_id_setting', 0));
		global $post;
		if (is_object($post)){
			if (isset($post->ID)){
				if ($post->ID === $offer_id || $post->ID === $credit_id){
					return 'noindex,nofollow'; //noindex nofollow those pages
				} else {
					return $robots; //else return normal meta
				}
			}
		}
		return $robots; //else return normal meta

	}

	function exclude_posts_from_xml_sitemaps() {
		// if offer or credit product
		$offer_id = intval(get_option('b2bking_offer_product_id_setting', 0));
		$credit_id = intval(get_option('b2bking_credit_product_id_setting', 0));

		$exclude_arr = array();
		if (!$offer_id){
			array_push($exclude_arr, $offer_id);
		}

		if (!$credit_id){
			array_push($exclude_arr, $credit_id);
		}

	    return $exclude_arr;
	}


	public function pay_for_order_capability( $allcaps, $caps, $args )	{

		if($args[0] !== 'pay_for_order' || !isset($args[2])){
		   return $allcaps;
		}

		$order_id = $args[2];

		$order = wc_get_order($order_id);
		$customer_id = $order->get_customer_id();
		// check if this is parent
		$user_id = get_current_user_id();
		$parent_id = get_user_meta( $customer_id, 'b2bking_account_parent', true );
		if (intval($user_id) === intval($parent_id)){
			$allcaps['pay_for_order'] = 1;
		}

		return $allcaps;
	}

	function dynamic_replace_prices_with_quotes(){

		// Get current user
		$user_id = get_current_user_id();

    	$account_type = get_user_meta($user_id,'b2bking_account_type', true);
    	if ($account_type === 'subaccount'){
    		// for all intents and purposes set current user as the subaccount parent
    		$parent_user_id = get_user_meta($user_id, 'b2bking_account_parent', true);
    		$user_id = $parent_user_id;
    	}

		$currentusergroupidnr = b2bking()->get_user_group($user_id);
		if (empty($currentusergroupidnr)){
			$currentusergroupidnr = 'nothave'; // necessary to avoid issues for guests
		}

		$replace_prices_quote = get_transient('b2bking_replace_prices_quote_user_'.$user_id);

		if (!$replace_prices_quote){


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
	                'value' => 'all_registered',
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
		                        'value' => 'all_registered'
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

			$quote_request_rules = get_posts([
	    		'post_type' => 'b2bking_rule',
	    	  	'post_status' => 'publish',
	    	  	'fields'        => 'ids', // Only get post IDs
	    	  	'numberposts' => -1,
	    	  	'meta_query'=> array(
	                'relation' => 'AND',
	                array(
	                    'key' => 'b2bking_rule_what',
	                    'value' => 'replace_prices_quote'
	                ),
	                $array_who,
	            )
	    	]);

	    	if (empty($quote_request_rules)){
	    		$replace_prices_quote = 'no';
	    	} else {
	    		$replace_prices_quote = 'yes';
	    	}

	    	set_transient('b2bking_replace_prices_quote_user_'.$user_id, $replace_prices_quote);

		}

		return $replace_prices_quote;

	}

	function b2bking_get_edit_post_type_page($post_type_input){

		echo B2bking_Admin::get_header_bar();


		/** WordPress Administration Bootstrap */
		//require_once ABSPATH . 'wp-admin/admin.php';
		global $post_type;
		global $post_type_object;
		$post_type = $post_type_input;
		$post_type_object = get_post_type_object( $post_type );
		set_current_screen('edit-'.$post_type);

		if ( ! $post_type_object ) {
			wp_die( __( 'Invalid post type.' ) );
		}

		if ( ! current_user_can( $post_type_object->cap->edit_posts ) ) {
			wp_die(
				'<h1>' . __( 'You need a higher level of permission.' ) . '</h1>' .
				'<p>' . __( 'Sorry, you are not allowed to edit posts in this post type.' ) . '</p>',
				403
			);
		}
		$args = array();
		$args['screen'] = get_current_screen();

		$wp_list_table = _get_list_table( 'WP_Posts_List_Table', $args );
		$pagenum       = $wp_list_table->get_pagenum();

		// Back-compat for viewing comments of an entry.
		foreach ( array( 'p', 'attachment_id', 'page_id' ) as $_redirect ) {
			if ( ! empty( $_REQUEST[ $_redirect ] ) ) {
				wp_redirect( admin_url( 'edit-comments.php?p=' . absint( $_REQUEST[ $_redirect ] ) ) );
				exit;
			}
		}
		unset( $_redirect );

		if ( 'post' !== $post_type ) {
			$parent_file   = "edit.php?post_type=$post_type";
			$submenu_file  = "edit.php?post_type=$post_type";
			$post_new_file = "post-new.php?post_type=$post_type";
		} else {
			$parent_file   = 'edit.php';
			$submenu_file  = 'edit.php';
			$post_new_file = 'post-new.php';
		}

		global $wp_query;
		$args = array('post_type' => $post_type,'post_status' => 'any', 'posts_per_page' => 20 );                                              
		$wp_query = new WP_Query( $args );

		$wp_list_table->prepare_items();

		wp_enqueue_script( 'inline-edit-post' );
		wp_enqueue_script( 'heartbeat' );

		if ( 'wp_block' === $post_type ) {
			wp_enqueue_script( 'wp-list-reusable-blocks' );
			wp_enqueue_style( 'wp-list-reusable-blocks' );
		}

		// Used in the HTML title tag.
		$title = $post_type_object->labels->name;


		get_current_screen()->set_screen_reader_content(
			array(
				'heading_views'      => $post_type_object->labels->filter_items_list,
				'heading_pagination' => $post_type_object->labels->items_list_navigation,
				'heading_list'       => $post_type_object->labels->items_list,
			)
		);

		add_screen_option(
			'per_page',
			array(
				'default' => 20,
				'option'  => 'edit_' . $post_type . '_per_page',
			)
		);

		$bulk_counts = array(
			'updated'   => isset( $_REQUEST['updated'] ) ? absint( $_REQUEST['updated'] ) : 0,
			'locked'    => isset( $_REQUEST['locked'] ) ? absint( $_REQUEST['locked'] ) : 0,
			'deleted'   => isset( $_REQUEST['deleted'] ) ? absint( $_REQUEST['deleted'] ) : 0,
			'trashed'   => isset( $_REQUEST['trashed'] ) ? absint( $_REQUEST['trashed'] ) : 0,
			'untrashed' => isset( $_REQUEST['untrashed'] ) ? absint( $_REQUEST['untrashed'] ) : 0,
		);

		$bulk_messages             = array();
		$bulk_messages['post']     = array(
			'updated'   => _n( '%s post updated.', '%s posts updated.', $bulk_counts['updated'] ),
			'locked'    => ( 1 === $bulk_counts['locked'] ) ? __( '1 post not updated, somebody is editing it.' ) :
							
							_n( '%s post not updated, somebody is editing it.', '%s posts not updated, somebody is editing them.', $bulk_counts['locked'] ),
		
			'deleted'   => _n( '%s post permanently deleted.', '%s posts permanently deleted.', $bulk_counts['deleted'] ),
			'trashed'   => _n( '%s post moved to the Trash.', '%s posts moved to the Trash.', $bulk_counts['trashed'] ),
			'untrashed' => _n( '%s post restored from the Trash.', '%s posts restored from the Trash.', $bulk_counts['untrashed'] ),
		);
		$bulk_messages['page']     = array(
			'updated'   => _n( '%s page updated.', '%s pages updated.', $bulk_counts['updated'] ),
			'locked'    => ( 1 === $bulk_counts['locked'] ) ? __( '1 page not updated, somebody is editing it.' ) :
							_n( '%s page not updated, somebody is editing it.', '%s pages not updated, somebody is editing them.', $bulk_counts['locked'] ),
			'deleted'   => _n( '%s page permanently deleted.', '%s pages permanently deleted.', $bulk_counts['deleted'] ),
			'trashed'   => _n( '%s page moved to the Trash.', '%s pages moved to the Trash.', $bulk_counts['trashed'] ),
			'untrashed' => _n( '%s page restored from the Trash.', '%s pages restored from the Trash.', $bulk_counts['untrashed'] ),
		);
		$bulk_messages['wp_block'] = array(
			'updated'   => _n( '%s block updated.', '%s blocks updated.', $bulk_counts['updated'] ),
			'locked'    => ( 1 === $bulk_counts['locked'] ) ? __( '1 block not updated, somebody is editing it.' ) :
							_n( '%s block not updated, somebody is editing it.', '%s blocks not updated, somebody is editing them.', $bulk_counts['locked'] ),
			'deleted'   => _n( '%s block permanently deleted.', '%s blocks permanently deleted.', $bulk_counts['deleted'] ),
			'trashed'   => _n( '%s block moved to the Trash.', '%s blocks moved to the Trash.', $bulk_counts['trashed'] ),
			'untrashed' => _n( '%s block restored from the Trash.', '%s blocks restored from the Trash.', $bulk_counts['untrashed'] ),
		);

		$bulk_messages = apply_filters( 'bulk_post_updated_messages', $bulk_messages, $bulk_counts );
		$bulk_counts   = array_filter( $bulk_counts );


		?>
		<div class="wrap">
		<h1 class="wp-heading-inline">
		<?php
		echo esc_html( $post_type_object->labels->name );
		?>
		</h1>

		<?php

		if ( current_user_can( $post_type_object->cap->create_posts ) ) {
			echo ' <a href="' . esc_url( admin_url( $post_new_file ) ) . '" class="page-title-action">' . esc_html( $post_type_object->labels->add_new ) . '</a>';
		}

		if ( isset( $_REQUEST['s'] ) && strlen( $_REQUEST['s'] ) ) {
			echo '<span class="subtitle">';
			printf(
				__( 'Search results for: %s' ),
				'<strong>' . get_search_query() . '</strong>'
			);
			echo '</span>';
		}
		?>

		<hr class="wp-header-end">

		<?php
		do_action( 'admin_notices' );

		// If we have a bulk message to issue:
		$messages = array();
		foreach ( $bulk_counts as $message => $count ) {
			if ( isset( $bulk_messages[ $post_type ][ $message ] ) ) {
				$messages[] = sprintf( $bulk_messages[ $post_type ][ $message ], number_format_i18n( $count ) );
			} elseif ( isset( $bulk_messages['post'][ $message ] ) ) {
				$messages[] = sprintf( $bulk_messages['post'][ $message ], number_format_i18n( $count ) );
			}

			if ( 'trashed' === $message && isset( $_REQUEST['ids'] ) ) {
				$ids        = preg_replace( '/[^0-9,]/', '', $_REQUEST['ids'] );
				$messages[] = '<a href="' . esc_url( wp_nonce_url( "edit.php?post_type=$post_type&doaction=undo&action=untrash&ids=$ids", 'bulk-posts' ) ) . '">' . __( 'Undo' ) . '</a>';
			}

			if ( 'untrashed' === $message && isset( $_REQUEST['ids'] ) ) {
				$ids = explode( ',', $_REQUEST['ids'] );

				if ( 1 === count( $ids ) && current_user_can( 'edit_post', $ids[0] ) ) {
					$messages[] = sprintf(
						'<a href="%1$s">%2$s</a>',
						esc_url( get_edit_post_link( $ids[0] ) ),
						esc_html( get_post_type_object( get_post_type( $ids[0] ) )->labels->edit_item )
					);
				}
			}
		}

		if ( $messages ) {
			echo '<div id="message" class="updated notice is-dismissible"><p>' . implode( ' ', $messages ) . '</p></div>';
		}
		unset( $messages );

		$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'locked', 'skipped', 'updated', 'deleted', 'trashed', 'untrashed' ), $_SERVER['REQUEST_URI'] );
		?>

		<?php $wp_list_table->views(); ?>

		<form id="posts-filter" method="get">

		<?php $wp_list_table->search_box( $post_type_object->labels->search_items, 'post' ); ?>

		<input type="hidden" name="post_status" class="post_status_page" value="<?php echo ! empty( $_REQUEST['post_status'] ) ? esc_attr( $_REQUEST['post_status'] ) : 'all'; ?>" />
		<input type="hidden" name="post_type" class="post_type_page" value="<?php echo $post_type; ?>" />

		<?php if ( ! empty( $_REQUEST['author'] ) ) { ?>
		<input type="hidden" name="author" value="<?php echo esc_attr( $_REQUEST['author'] ); ?>" />
		<?php } ?>

		<?php if ( ! empty( $_REQUEST['show_sticky'] ) ) { ?>
		<input type="hidden" name="show_sticky" value="1" />
		<?php } ?>

		<?php
		// set server URI for pagination to work
		$_SERVER['REQUEST_URI'] = '/wp-admin/edit.php?post_type='.$post_type;
		?>

		<?php $wp_list_table->display(); ?>

		</form>

		<?php
		if ( $wp_list_table->has_items() ) {
			$wp_list_table->inline_edit();
		}
		?>

		<div id="ajax-response"></div>
		<div class="clear"></div>
		</div>

		<?php

		
	}

	function b2bking_get_page_content(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		// get page here
		$page = sanitize_text_field($_POST['page']);
		$user_id = sanitize_text_field($_POST['userid']);

		ob_start();

		if ($page === 'groups'){
			B2bking_Admin::b2bking_groups_page_content();
		} else if ($page === 'b2c_users'){
			B2bking_Admin::b2bking_b2c_users_page_content();
		} else if ($page === 'logged_out_users'){
			B2bking_Admin::b2bking_logged_out_users_page_content();
		} else if ($page === 'dashboard'){
			B2bking_Admin::b2bking_dashboard_page_content();
		} else if ($page === 'customers'){
			B2bking_Admin::b2bking_customers_page_content();
		} else if ($page === 'tools'){
			B2bking_Admin::b2bking_tools_page_content();
		} else {
			// post type
			$pageexplode = explode('_', $page, 2);
			if ($pageexplode[0] === 'edit'){
				$page = $pageexplode[1];
				$this->b2bking_get_edit_post_type_page($page);
			}
		}


		
		$content = ob_get_clean();

		echo $content;
		exit();

	}

	function b2bking_hide_prices_request_quote( $price, $product ) {
		return '';
	}

	function b2bking_replace_add_to_cart_text() {
		return esc_html__('Add to Quote Request', 'b2bking');
	}

	function b2bking_hide_prices_cart( $price ) {
		return esc_html__('Quote','b2bking');
	}

	function b2bking_disable_purchasable_except_offers($purchasable, $product){
		$offer_id = intval(get_option('b2bking_offer_product_id_setting', 0));
		$offer_products_integrations = $this->get_all_offer_products_integrations();

		$current_product_id = intval($product->get_id());

		// if user is guest, or multisite b2b/b2b separation is enabled and user should be treated as guest
		if ($offer_id !== $current_product_id && !in_array($current_product_id, $offer_products_integrations)){
			$purchasable = false;
		}

		return $purchasable;
	}

	function b2bking_cannot_quote_offer_cart_message() {
		wc_print_notice( esc_html__('While you have an offer in cart, you cannot add products to quote', 'b2bking'), 'notice' );
	}


	function get_all_offer_products_integrations(){
		// dokan and wcfm integration
		$dokan_offer_products = get_option('b2bking_dokan_hidden_offer_product_ids', 'string');
		$dokan_offer_products_clean = array();
		if ($dokan_offer_products !== 'string' && !empty($dokan_offer_products)){
			$dokan_offer_products = explode(',', $dokan_offer_products);
			$dokan_offer_products_clean = array_unique(array_filter($dokan_offer_products));
		}

		$wcfm_offer_products = get_option('b2bking_wcfm_hidden_offer_product_ids', 'string');
		$wcfm_offer_products_clean = array();
		if ($wcfm_offer_products !== 'string' && !empty($wcfm_offer_products)){
			$wcfm_offer_products = explode(',', $wcfm_offer_products);
			$wcfm_offer_products_clean = array_unique(array_filter($wcfm_offer_products));
		}

		$marketking_offer_products = get_option('b2bking_marketking_hidden_offer_product_ids', 'string');
		$marketking_offer_products_clean = array();
		if ($marketking_offer_products !== 'string' && !empty($marketking_offer_products)){
			$marketking_offer_products = explode(',', $marketking_offer_products);
			$marketking_offer_products_clean = array_unique(array_filter($marketking_offer_products));
		}

		$offer_products_integrations = array_merge($dokan_offer_products_clean, $wcfm_offer_products_clean, $marketking_offer_products_clean);

		return $offer_products_integrations;
	}

	function user_has_offer_in_cart(){
		$has_offer = 'no';
		$offer_id = intval(get_option('b2bking_offer_product_id_setting', 0));

		$offer_products_integrations = $this->get_all_offer_products_integrations();
		// dokan and wcfm integration end
		
		if (is_object( WC()->cart )){

			foreach(WC()->cart->get_cart() as $cart_item){
				if ($cart_item['product_id'] === $offer_id){
					$has_offer = 'yes';
				}

				if (in_array($cart_item['product_id'], $offer_products_integrations)){
					$has_offer = 'yes';
				}
			}
		}

		return $has_offer;
	}



	public function install_b2bking_core(){
		// Check security nonce.
		if ( ! check_ajax_referer( 'b2bking-core-install-nonce', 'security' ) ) {
			wp_send_json_error( 'Invalid security token sent.' );
			wp_die();
		}

		include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		$plugin = 'b2bking-wholesale-for-woocommerce';
		$api    = plugins_api(
		    'plugin_information', [
		        'slug'   => $plugin,
		        'fields' => [ 'sections' => false ],
		    ]
		);

		$upgrader = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );
		$result   = $upgrader->install( $api->download_link );
		activate_plugin( 'b2bking-wholesale-for-woocommerce/b2bking.php' );

		wp_send_json_success();
	}

	public function b2bkingemailoffer(){
		// Check security nonce.
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
			wp_send_json_error( 'Invalid security token sent.' );
			wp_die();
		}

		// get all recipients of the offer
		$offer_id = sanitize_text_field($_POST['offerid']);
		$offer_link = $_POST['offerlink'];
		$emails_send_to = array();
		$emails_send_to_guest = array();
		// for each group, check if visible
		$groups = get_posts( array( 'post_type' => 'b2bking_group','post_status'=>'publish','numberposts' => -1) );
		foreach ($groups as $group){
			$visible = get_post_meta($offer_id, 'b2bking_group_'.$group->ID, true);
			if (intval($visible) === 1){
				// get all users with this group and add them to array
				$users = get_users(array(
				    'meta_key'     => apply_filters('b2bking_group_key_name', 'b2bking_customergroup'),
				    'meta_value'   => $group->ID,
				    'fields' => array('user_email'),
				));
				foreach ($users as $email){
					array_push($emails_send_to, $email->user_email);
				}
				
			}
		}

		// get users
		$userstextarea = get_post_meta($offer_id, 'b2bking_category_users_textarea', true);
		$userarray = explode(',', $userstextarea);
		foreach ($userarray as $user){
			$user = trim($user);
			if (!empty($user)){
				// if email, add directly
				if (strpos($user, '@') !== false) {
					array_push($emails_send_to_guest, $user);
				} else {
					if (username_exists($user)){
						// get email
						$usertemp = get_user_by('login', $user);
						array_push($emails_send_to, $usertemp->user_email);
					}
				}
			}
		}

		foreach ($emails_send_to as $emailad){
			do_action( 'b2bking_new_offer', $emailad, '1', $offer_id, $offer_link );
		}
		foreach ($emails_send_to_guest as $emailad){
			do_action( 'b2bking_new_offer', $emailad, '0', $offer_id, $offer_link );
		}

		echo 'success';
		exit();
	}

	function b2bking_categories_restrict( $args, $taxonomies ) {

		$user_id = get_current_user_id();
		$account_type = get_user_meta($user_id,'b2bking_account_type', true);
		if ($account_type === 'subaccount'){
			// for all intents and purposes set current user as the subaccount parent
			$parent_user_id = get_user_meta($user_id, 'b2bking_account_parent', true);
			$user_id = intval($parent_user_id);

		}

		
		if (intval(get_option( 'b2bking_all_products_visible_all_users_setting', 1 )) !== 1){

			if ( get_option( 'b2bking_plugin_status_setting', 'b2b' ) !== 'disabled' ){

				if ( is_admin() && 'category' !== $taxonomies[0] ){
				    return $args;
				}

				if (!defined('ICL_LANGUAGE_NAME_EN')){
					if (get_transient('b2bking_user_exclude_categories_id_'.$user_id)){
						$args['exclude'] = get_transient('b2bking_user_exclude_categories_id_'.$user_id); // Array of cat ids to exclude
					}
				} else {
					if (get_transient('b2bking_user_exclude_categories_id_'.$user_id)){
						$args['exclude'] = get_transient('b2bking_user_exclude_categories_id_'.$user_id.ICL_LANGUAGE_NAME_EN); // Array of cat ids to exclude
					}
				}
				return $args;
			}
		}
		return $args;
	}

	function asl_query_args_postin($args) {

		$args['post_in'] = is_array($args['post_in']) ? $args['post_in'] : array();


		if (!defined('ICL_LANGUAGE_NAME_EN')){
			$allTheIDs = get_transient('b2bking_user_'.get_current_user_id().'_ajax_visibility');
		} else {
			$allTheIDs = get_transient('b2bking_user_'.get_current_user_id().'_ajax_visibility'.ICL_LANGUAGE_NAME_EN);
		}


		$allTheIDs = apply_filters('b2bking_ids_post_in_visibility', $allTheIDs);

		$currentval = $args['post_in'];
		if (!empty($currentval) && $allTheIDs !== false){
			$allTheIDs = array_intersect($allTheIDs, $currentval);
		}
			
		if ($allTheIDs){
		    if(!empty($allTheIDs)){
		    	$args['post_in'] = array_merge($args['post_in'], $allTheIDs);
		    }
		}				 

		return $args;
	}

	function swp_query_args_postin($args) {

		$args['post__in'] = is_array($args['post__in']) ? $args['post__in'] : array();


		if (!defined('ICL_LANGUAGE_NAME_EN')){
			$allTheIDs = get_transient('b2bking_user_'.get_current_user_id().'_ajax_visibility');
		} else {
			$allTheIDs = get_transient('b2bking_user_'.get_current_user_id().'_ajax_visibility'.ICL_LANGUAGE_NAME_EN);
		}


		$allTheIDs = apply_filters('b2bking_ids_post_in_visibility', $allTheIDs);

		$currentval = $args['post__in'];
		if (!empty($currentval) && $allTheIDs !== false){
			$allTheIDs = array_intersect($allTheIDs, $currentval);
		}
			
		if ($allTheIDs){
		    if(!empty($allTheIDs)){
		    	$args['post__in'] = array_merge($args['post__in'], $allTheIDs);
		    }
		}				 
		
		return $args;
	}

	public function b2bking_dismiss_onboarding_admin_notice() {
		// Check security nonce.
		if ( ! check_ajax_referer( 'b2bking_notice_security_nonce', 'security' ) ) {
			wp_send_json_error( 'Invalid security token sent.' );
			wp_die();
		}

		update_user_meta( get_current_user_id(), 'b2bking_dismiss_onboarding_notice', 1 );

		echo 'success';
		exit();
	}

	function b2bking_show_both_prices($price, $product){

		$product_id = $product->get_id();

		if (!get_transient('b2bking_display_price_both'.$product_id.'_'.get_current_user_id())){

			$is_b2b_user = get_user_meta(get_current_user_id(),'b2bking_b2buser', true);
			if ($is_b2b_user === 'yes'){
				
				$retail_text = get_option('b2bking_retail_price_text_setting', esc_html__('Retail price','b2bking')).': ';
				$retail_text = apply_filters('b2bking_retail_price_text', $retail_text);

				$wholesale_text = get_option('b2bking_wholesale_price_text_setting', esc_html__('Wholesale price','b2bking')).': ';
				$wholesale_text = apply_filters('b2bking_wholesale_price_text', $wholesale_text);

				$wholesale_price = $price;
				
				// variable product
				if( $product->is_type('variable') ){
					$children = $product->get_children();
					$min_price = 0;
					$max_price = 0;
					foreach ($children as $variation_id){
						// get retail price
						$variation = wc_get_product($variation_id);
						$variation_price = b2bking()->tofloat(get_post_meta($variation_id,'_regular_price', true));
						$variation_price_temp = b2bking()->tofloat(get_post_meta($variation_id,'_sale_price', true));
						if (!empty($variation_price_temp)){
							// there is indeed a sale price
							$variation_price = $variation_price_temp;
						}

						if ($max_price === 0){
							$min_price = $max_price = $variation_price;
						} else {
							if ($variation_price < $min_price){
								$min_price = $variation_price;
							}
							if ($variation_price > $max_price){
								$max_price = $variation_price;
							}
						}
					}

					// apply tax
					if (apply_filters('b2bking_both_prices_retail_adjust_tax', true)){
						$min_price = b2bking()->b2bking_wc_get_price_to_display( $product, array( 'price' => $min_price ) );
						$max_price = b2bking()->b2bking_wc_get_price_to_display( $product, array( 'price' => $max_price ) );
					}

					// if min and max prices are different, show range, else show the price
					if ($min_price !== $max_price){
						$retail_price = wc_format_price_range( $min_price, $max_price );
					} else {
						$retail_price = wc_price($min_price);
					}

				} else {
					// Simple product
					// get retail price
					$retail_price = b2bking()->tofloat(get_post_meta($product_id,'_regular_price', true));

					$retail_sale_price = b2bking()->tofloat(get_post_meta($product_id,'_sale_price', true));

					if (!empty($retail_price) && floatval($retail_price) !== 0){
						if (!empty($retail_sale_price)){
							// if there is a sale price
							if (apply_filters('b2bking_both_prices_retail_adjust_tax', true)){

								$retail_price = wc_format_sale_price(b2bking()->b2bking_wc_get_price_to_display( $product, array( 'price' => $retail_price ) ), b2bking()->b2bking_wc_get_price_to_display( $product, array( 'price' => $retail_sale_price ) ));

							} else {
								$retail_price = wc_format_sale_price($retail_price, b2bking()->b2bking_wc_get_price_to_display( $product, array( 'price' => $retail_sale_price ) ));

							}
						} else {
							if (apply_filters('b2bking_both_prices_retail_adjust_tax', true)){
								$retail_price = wc_price(b2bking()->b2bking_wc_get_price_to_display( $product, array( 'price' => $retail_price )));

							} else {
								$retail_price = wc_price($retail_price);
							}
						}
					}
				
				}

				if (($wholesale_price !== $retail_price && $retail_price !== 0 && $retail_price !== '' && $wholesale_price !== '' ) || (apply_filters('b2bking_always_show_both_prices', false))){
					if ( ! $this->b2bking_price_is_already_formatted($wholesale_price)){
						$wholesale_price = wc_price($wholesale_price);
					}

					$wholesale_price = apply_filters('b2bking_filter_wholesale_price', $wholesale_price, $product_id);
					$price = '<span class="b2bking_both_prices_text b2bking_retail_price_text">'.$retail_text .'</span><span class="b2bking_both_prices_price b2bking_retail_price_price">'. $retail_price . '<br></span><span class="b2bking_both_prices_text b2bking_b2b_price_price">'.$wholesale_text.'</span><span class="b2bking_both_prices_price b2bking_b2b_price_price b2bking_b2b_price_id_'.esc_attr($product_id).'">'.$wholesale_price.'</span>';

					$price = apply_filters('b2bking_filter_wholesale_price_final', $price, $retail_text, $retail_price, $wholesale_text, $wholesale_price, $product_id);
				}
						
			}
			set_transient('b2bking_display_price_both'.$product_id.'_'.get_current_user_id(), $price);
		} else {
			$price = get_transient('b2bking_display_price_both'.$product_id.'_'.get_current_user_id());
		}

		return $price;
	}

	function b2bking_price_is_already_formatted($price){

		$symbol = get_woocommerce_currency_symbol();
		if (strpos($price, $symbol) !== false) {
		    return true;
		}
		
		return false;
	}

	function b2bking_dismiss_groups_howto_admin_notice(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_notice_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		update_user_meta(get_current_user_id(), 'b2bking_dismiss_groups_howto_notice', 1);

		echo 'success';
		exit();
	}

	function b2bking_dismiss_groupsrules_howto_admin_notice(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_notice_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		update_user_meta(get_current_user_id(), 'b2bking_dismiss_groupsrules_howto_notice', 1);

		echo 'success';
		exit();
	}

	function b2bking_dismiss_quotefields_howto_admin_notice(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_notice_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		update_user_meta(get_current_user_id(), 'b2bking_dismiss_quotefields_howto_notice', 1);

		echo 'success';
		exit();
	}

	function b2bking_dismiss_customers_howto_admin_notice(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_notice_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		update_user_meta(get_current_user_id(), 'b2bking_dismiss_customers_howto_notice', 1);

		echo 'success';
		exit();
	}

	function b2bking_dismiss_conversations_howto_admin_notice(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_notice_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		update_user_meta(get_current_user_id(), 'b2bking_dismiss_conversations_howto_notice', 1);

		echo 'success';
		exit();
	}

	function b2bking_dismiss_rules_howto_admin_notice(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_notice_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		update_user_meta(get_current_user_id(), 'b2bking_dismiss_rules_howto_notice', 1);

		echo 'success';
		exit();
	}

	function b2bking_dismiss_roles_howto_admin_notice(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_notice_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		update_user_meta(get_current_user_id(), 'b2bking_dismiss_roles_howto_notice', 1);

		echo 'success';
		exit();
	}

	function b2bking_dismiss_fields_howto_admin_notice(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_notice_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		update_user_meta(get_current_user_id(), 'b2bking_dismiss_fields_howto_notice', 1);

		echo 'success';
		exit();
	}

	function b2bking_dismiss_offers_howto_admin_notice(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_notice_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		update_user_meta(get_current_user_id(), 'b2bking_dismiss_offers_howto_notice', 1);

		echo 'success';
		exit();
	}

	function b2bking_add_billing_fields_admin_data($order){
		if (is_user_logged_in()){
			$user_id = get_current_user_id();
			$account_type = get_user_meta($user_id,'b2bking_account_type', true);
			if ($account_type === 'subaccount'){
				// for all intents and purposes set current user as the subaccount parent
				$parent_user_id = get_user_meta($user_id, 'b2bking_account_parent', true);
				$user_id = $parent_user_id;
			}
		} else {
			$user_id = 0; 
		}

		// build array of groups visible
		$array_groups_visible = array(
		    'relation' => 'OR',
		);

		if (!is_user_logged_in()){
			array_push($array_groups_visible, array(
		        'key' => 'b2bking_custom_field_multiple_groups',
		        'value' => 'group_loggedout',
		        'compare' => 'LIKE'
		    ));
		} else {
			// if user is b2c
			if (get_user_meta(get_current_user_id(),'b2bking_b2buser', true) !== 'yes'){
				array_push($array_groups_visible, array(
		            'key' => 'b2bking_custom_field_multiple_groups',
		            'value' => 'group_b2c',
		            'compare' => 'LIKE'
		        ));
			} else {
				array_push($array_groups_visible, array(
		            'key' => 'b2bking_custom_field_multiple_groups',
		            'value' => 'group_'.b2bking()->get_user_group(),
		            'compare' => 'LIKE'
		        ));
        		array_push($array_groups_visible, array(
                    'key' => 'b2bking_custom_field_multiple_groups',
                    'value' => 'group_b2b',
                    'compare' => 'LIKE'
                ));
			}
		}

		// get all enabled custom fields with no default billing connection (first name, last name etc)
		$custom_fields = get_posts([
			    		'post_type' => 'b2bking_custom_field',
			    	  	'post_status' => 'publish',
			    	  	'numberposts' => -1,
			    	  	'meta_key' => 'b2bking_custom_field_sort_number',
		    	  	    'orderby' => 'meta_value_num',
		    	  	    'order' => 'ASC',
			    	  	'meta_query'=> array(
			    	  		'relation' => 'AND',
			                array(
		                        'key' => 'b2bking_custom_field_status',
		                        'value' => 1
			                ),
			                array(
			                	'relation' => 'OR',
				                array(
			                        'key' => 'b2bking_custom_field_billing_connection',
			                        'value' => 'none'
				                ),
				                array(
			                        'key' => 'b2bking_custom_field_billing_connection',
			                        'value' => 'billing_vat'
				                ),
				            ),			               
			                array(
		                        'key' => 'b2bking_custom_field_add_to_billing',
		                        'value' => 1
			                ),
			                $array_groups_visible,
		            	)
			    	]);

		foreach ($custom_fields as $custom_field){
			$field_label = get_post_meta ($custom_field->ID, 'b2bking_custom_field_field_label', true);
			$field_type = get_post_meta ($custom_field->ID, 'b2bking_custom_field_field_type', true);
			$required = intval(get_post_meta ($custom_field->ID, 'b2bking_custom_field_required_billing', true));

			// check if this field is VAT
			if ($field_type !== 'file'){ // not available to files for the moment
				$value = get_post_meta($order->get_id(), 'b2bking_custom_field_'.$custom_field->ID, true);
				if (!empty($value)){
					echo esc_html($field_label).': '.esc_html($value);
					echo '<br />';
				}

				$value = get_post_meta($order->get_id(), 'b2bking_custom_field_'.$custom_field->ID.'bis', true);
				if (!empty($value)){
					echo esc_html($field_label).': '.esc_html($value);
					echo '<br />';
				}

			}
		}

		echo '<br /><br />';

	}

	function b2bking_add_billing_fields_admin_email($order, $sent_to_admin, $plain_text, $email){

		if (is_user_logged_in()){
			$user_id = get_current_user_id();
			$account_type = get_user_meta($user_id,'b2bking_account_type', true);
			if ($account_type === 'subaccount'){
				// for all intents and purposes set current user as the subaccount parent
				$parent_user_id = get_user_meta($user_id, 'b2bking_account_parent', true);
				$user_id = $parent_user_id;
			}
		} else {
			$user_id = 0; 
		}

		// build array of groups visible
		$array_groups_visible = array(
		    'relation' => 'OR',
		);

		if (!is_user_logged_in()){
			array_push($array_groups_visible, array(
		        'key' => 'b2bking_custom_field_multiple_groups',
		        'value' => 'group_loggedout',
		        'compare' => 'LIKE'
		    ));
		} else {
			// if user is b2c
			if (get_user_meta(get_current_user_id(),'b2bking_b2buser', true) !== 'yes'){
				array_push($array_groups_visible, array(
		            'key' => 'b2bking_custom_field_multiple_groups',
		            'value' => 'group_b2c',
		            'compare' => 'LIKE'
		        ));
			} else {
				array_push($array_groups_visible, array(
		            'key' => 'b2bking_custom_field_multiple_groups',
		            'value' => 'group_'.b2bking()->get_user_group(),
		            'compare' => 'LIKE'
		        ));
        		array_push($array_groups_visible, array(
                    'key' => 'b2bking_custom_field_multiple_groups',
                    'value' => 'group_b2b',
                    'compare' => 'LIKE'
                ));
			}
		}

		// get all enabled custom fields with no default billing connection (first name, last name etc)
		$custom_fields = get_posts([
			    		'post_type' => 'b2bking_custom_field',
			    	  	'post_status' => 'publish',
			    	  	'numberposts' => -1,
			    	  	'meta_key' => 'b2bking_custom_field_sort_number',
		    	  	    'orderby' => 'meta_value_num',
		    	  	    'order' => 'ASC',
			    	  	'meta_query'=> array(
			    	  		'relation' => 'AND',
			                array(
		                        'key' => 'b2bking_custom_field_status',
		                        'value' => 1
			                ),
			                array(
			                	'relation' => 'OR',
				                array(
			                        'key' => 'b2bking_custom_field_billing_connection',
			                        'value' => 'none'
				                ),
				                array(
			                        'key' => 'b2bking_custom_field_billing_connection',
			                        'value' => 'billing_vat'
				                ),
				            ),			               
			                array(
		                        'key' => 'b2bking_custom_field_add_to_billing',
		                        'value' => 1
			                ),
			                $array_groups_visible,
		            	)
			    	]);

		foreach ($custom_fields as $custom_field){
			$field_label = get_post_meta ($custom_field->ID, 'b2bking_custom_field_field_label', true);
			$field_type = get_post_meta ($custom_field->ID, 'b2bking_custom_field_field_type', true);
			$required = intval(get_post_meta ($custom_field->ID, 'b2bking_custom_field_required_billing', true));

			// check if this field is VAT
			if ($field_type !== 'file'){ // not available to files for the moment
				if (isset($_POST['b2bking_custom_field_'.$custom_field->ID]) && !empty($_POST['b2bking_custom_field_'.$custom_field->ID])){
					echo esc_html($field_label).': '.esc_html($_POST['b2bking_custom_field_'.$custom_field->ID]);
					echo '<br />';
				}

				// check bis also
				if (isset($_POST['b2bking_custom_field_'.$custom_field->ID.'bis']) && !empty($_POST['b2bking_custom_field_'.$custom_field->ID.'bis'])){
					echo esc_html($field_label).': '.esc_html($_POST['b2bking_custom_field_'.$custom_field->ID.'bis']);
					echo '<br />';
				}
			}
		}



	}


	function hidden_order_itemmeta($args) {
	  $args[] = '_b2bkingstockinfo';
	  return $args;
	}
		
	function decrease_offer_stock_quantity($order_id) {

		$order = wc_get_order( $order_id );
		$stock_message = '';
		// The loop to get the order items which are WC_Order_Item_Product objects since WC 3+
		foreach( $order->get_items() as $item_id => $item ){
		    //Get the product ID
		    $product_id = $item->get_product_id();
			// if not offer, skip
			$offer_id = intval(get_option('b2bking_offer_product_id_setting', 0));
			if (intval($product_id) !== $offer_id && intval($product_id) !== 3225464){ //3225464 is deprecated
				continue;
			}

			// item is offer, continue
			$stockinfo = $item->get_meta('_b2bkingstockinfo', true);
			$stockitems = array_filter(explode(';', $stockinfo));

			foreach ($stockitems as $stockitem){
				$id_qty = explode(':', $stockitem);
				$product = wc_get_product($id_qty[0]);

				$old_stock_quantity = $product->get_stock_quantity();
				$new_stock_quantity = wc_update_product_stock($id_qty[0], $id_qty[1], 'decrease');
				
				if (!empty($new_stock_quantity)){
					$stock_message .= esc_html__('Stock levels reduced: ','b2bking').$product->get_formatted_name().' '.$old_stock_quantity.'→'.$new_stock_quantity.'<br />';
				}
			}

		}

		if (!empty($stock_message)){
			$order->add_order_note( $stock_message );
		}
	}

	
	function increase_offer_stock_quantity($order_id) {
		$order = wc_get_order( $order_id );
		$stock_message = '';
		// The loop to get the order items which are WC_Order_Item_Product objects since WC 3+
		foreach( $order->get_items() as $item_id => $item ){
		    //Get the product ID
		    $product_id = $item->get_product_id();
			// if not offer, skip
			$offer_id = intval(get_option('b2bking_offer_product_id_setting', 0));
			if (intval($product_id) !== $offer_id && intval($product_id) !== 3225464){ //3225464 is deprecated
				continue;
			}

			// item is offer, continue
			$stockinfo = $item->get_meta('_b2bkingstockinfo', true);
			$stockitems = array_filter(explode(';', $stockinfo));
			foreach ($stockitems as $stockitem){
				$id_qty = explode(':', $stockitem);
				$product = wc_get_product($id_qty[0]);

				$old_stock_quantity = $product->get_stock_quantity();
				$new_stock_quantity = wc_update_product_stock($id_qty[0], $id_qty[1], 'increase');
				
				if (!empty($new_stock_quantity)){
					$stock_message .= esc_html__('Stock levels increased: ','b2bking').$product->get_formatted_name().' '.$old_stock_quantity.'→'.$new_stock_quantity.'<br />';
				}
			}

		}

		if (!empty($stock_message)){
			$order->add_order_note( $stock_message );
		}

	}


	function b2bking_custom_woocommerce_ajax_get_customer_details($data, $customer, $user_id){

		// get all enabled custom fields with no default billing connection (first name, last name etc)
		$custom_fields = get_posts([
			    		'post_type' => 'b2bking_custom_field',
			    	  	'post_status' => 'publish',
			    	  	'numberposts' => -1,
			    	  	'meta_key' => 'b2bking_custom_field_sort_number',
		    	  	    'orderby' => 'meta_value_num',
		    	  	    'order' => 'ASC',
			    	  	'meta_query'=> array(
			    	  		'relation' => 'AND',
			                array(
		                        'key' => 'b2bking_custom_field_status',
		                        'value' => 1
			                ),
			                array(
			                	'relation' => 'OR',
        		                array(
        	                        'key' => 'b2bking_custom_field_billing_connection',
        	                        'value' => 'none'
        		                ),
        		                array(
        	                        'key' => 'b2bking_custom_field_billing_connection',
        	                        'value' => 'billing_vat'
        		                ),
        		            ),			               
			                array(
		                        'key' => 'b2bking_custom_field_add_to_billing',
		                        'value' => 1
			                ),
		            	)
			    	]);

		foreach ($custom_fields as $custom_field){

			$field_value = get_user_meta($user_id, 'b2bking_custom_field_'.$custom_field->ID, true);
			if ($field_value === NULL){
				$field_value = '';
			}

			$data['billing']['b2bking_custom_field_'.$custom_field->ID] = $field_value;

		}

		return $data;
	}

	function b2bking_bulk_edit_variations(){
		$groups = get_posts([
		  'post_type' => 'b2bking_group',
		  'post_status' => 'publish',
		  'numberposts' => -1,
		]);

		?>
		<optgroup id="b2bking_b2b_pricing_variations" label="<?php esc_attr_e( 'B2B Pricing', 'b2bking' ); ?>">
			<?php
			global $post;
			foreach ($groups as $group){
				?>
				<option value="b2bking_regular_price_product_<?php echo esc_attr($post->ID);?>_group_<?php echo esc_attr($group->ID);?>"><?php echo esc_html__('Set ','b2bking').esc_html($group->post_title).' '.esc_html__('regular prices','b2bking'); ?></option>
				<option value="b2bking_sale_price_product_<?php echo esc_attr($post->ID);?>_group_<?php echo esc_attr($group->ID);?>"><?php echo esc_html__('Set ','b2bking').esc_html($group->post_title).' '.esc_html__('sale prices','b2bking'); ?></option>
				<?php
			}
			?>
		</optgroup>
		<?php
	}


	function b2bking_flush_cache_for_api_rule( $post, $request, $true) {

    	
    	b2bking()->clear_caches_transients();

    	require_once B2BKING_DIR . '/admin/class-b2bking-admin.php';
    	B2bking_Admin::b2bking_calculate_rule_numbers_database();
	    
	}
	function b2bking_flush_cache_for_api( $post_id) {
		if (isset($_POST['_inline_edit'])){
			return;
		}
	    if (get_post_type($post_id) === 'product' || get_post_type($post_id) === 'b2bking_rule'){

	    	
	    	b2bking()->clear_caches_transients();

	    	require_once B2BKING_DIR . '/admin/class-b2bking-admin.php';
	    	B2bking_Admin::b2bking_calculate_rule_numbers_database();
		    
	    }
	}
	function b2bking_flush_cache_scheduled( $new, $old, $post ) {
		$post_id = $post->ID;
	    if (get_post_type($post_id) === 'product'){

	    	if ($new === 'publish' && $old === 'future' ){

		    	
		    	b2bking()->clear_caches_transients();
		    }
	    }
	}

	function b2bking_copy_data($user_id) {
	    // copy data from parent account
	    $parent_id = get_user_meta($user_id, 'b2bking_account_parent', true);
	    $fields_array = array('billing_first_name', 'billing_last_name', 'billing_company', 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_postcode', 'billing_country', 'billing_state', 'billing_email', 'billing_phone', 'shipping_first_name', 'shipping_last_name', 'shipping_company', 'shipping_address_1', 'shipping_address_2', 'shipping_city', 'shipping_postcode', 'shipping_country', 'shipping_state');
	    foreach ($fields_array as $field){
	        // copy from parent
	        $parent_value = get_user_meta($parent_id,$field,true);
	        update_user_meta($user_id, $field, $parent_value);
	    }
	}

	// B2BKing Content Shortcode
		function b2bking_content_shortcode(){
			add_shortcode('b2bking_content', array($this, 'b2bking_content_shortcode_content'));
		}
		function b2bking_content_shortcode_content($atts = array(), $content = null){

			$atts = shortcode_atts(
		        array(
		            'show_to' => 'none',
		        ), 
		    $atts);
		    if ($atts['show_to'] === 'none'){
		    	return '';
		    } else {
		    	$groups_array=explode(',',$atts['show_to']);
		    	// check if current user has access
		    	$current_user_id = get_current_user_id();
		    	$current_user_group = b2bking()->get_user_group($current_user_id);

		    	$user_is_b2b = get_user_meta($current_user_id,'b2bking_b2buser',true);
		    	if ($user_is_b2b !== 'yes'){
		    		if (is_user_logged_in()){
		    			$current_user_group = 'b2c';
		    		} else {
		    			$current_user_group = 'loggedout';
		    		}
		    	}

		    	if (in_array($current_user_group,$groups_array)){
		    		// allow stacking shortcodes if a piece of content is a shortcode itself
		    		if (shortcode_exists(substr($content, 1, -1))){
		    			return do_shortcode($content);
		    		} else {
		    			// maybe shortcode with parameter, try with a space
		    			$shortcodewithspace = explode(' ', substr($content, 1, -1));
	    				if (shortcode_exists($shortcodewithspace[0])){
	    					return do_shortcode($content);
	    				}
		    		}
		    		return $content;
		    	} else {
		    		// check if user is b2b in general
		    		if ($user_is_b2b === 'yes' && in_array('b2b', $groups_array)){
		    			if (shortcode_exists(substr($content, 1, -1))){
		    				return do_shortcode($content);
		    			} else {
			    			// maybe shortcode with parameter, try with a space
			    			$shortcodewithspace = explode(' ', substr($content, 1, -1));
		    				if (shortcode_exists($shortcodewithspace[0])){
		    					return do_shortcode($content);
		    				}
			    		}
		    			return $content;
		    		} else{
		    			// check user's specific username
		    			$user_login = wp_get_current_user()->user_login;
		    			if (in_array($user_login,$groups_array)){
		    				if (shortcode_exists(substr($content, 1, -1))){
		    					return do_shortcode($content);
		    				} else {
				    			// maybe shortcode with parameter, try with a space
				    			$shortcodewithspace = explode(' ', substr($content, 1, -1));
			    				if (shortcode_exists($shortcodewithspace[0])){
			    					return do_shortcode($content);
			    				}
				    		}
		    				return $content;
		    			} else {
		    				return '';
		    			}
		    		}
		    	}
		    }

		}

	function register_metadata(){

		$array_options = array('b2bking_rule_what','b2bking_rule_howmuch','b2bking_rule_applies','b2bking_rule_who','b2bking_rule_quantity_value','b2bking_rule_discount_show_everywhere','b2bking_rule_conditions','b2bking_rule_discountname','b2bking_rule_applies_multiple_options','b2bking_rule_who_multiple_options','b2bking_rule_taxname','b2bking_rule_replaced','b2bking_rule_showtax','b2bking_rule_requires','b2bking_rule_tax_shipping','b2bking_rule_paymentmethod','b2bking_rule_currency');


		foreach ($array_options as $option){
			register_meta('post', $option, [
			  'object_subtype' => 'b2bking_rule',
			  'show_in_rest' => true
			]);
		}
	}


	function b2bking_user_is_in_list($list){
		// get user data
		$user_data_current_user_id = get_current_user_id();

		$account_type = get_user_meta($user_data_current_user_id,'b2bking_account_type', true);
		if ($account_type === 'subaccount'){
			// for all intents and purposes set current user as the subaccount parent
			$parent_user_id = get_user_meta($user_data_current_user_id, 'b2bking_account_parent', true);
			$user_data_current_user_id = $parent_user_id;
		}


		$user_data_current_user_b2b = get_user_meta($user_data_current_user_id, 'b2bking_b2buser', true);
		$user_data_current_user_group = b2bking()->get_user_group($user_data_current_user_id);
		// checks based on user id, b2b status and group, if it's part of an applicable rules list
		$is_in_list = 'no';
		$list_array = explode(',',$list);
		if (intval($user_data_current_user_id) !== 0){
			if (in_array('all_registered', $list_array)){
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

	// Add email classes to the list of email classes that WooCommerce loads
	function b2bking_add_email_classes( $email_classes ) {

	    $email_classes['B2bking_New_Customer_Email'] = include B2BKING_DIR .'/includes/emails/class-b2bking-new-customer-email.php';

	    $email_classes['B2bking_New_Message_Email'] = include B2BKING_DIR .'/includes/emails/class-b2bking-new-message-email.php';

	    $email_classes['B2bking_New_Customer_Requires_Approval_Email'] = include B2BKING_DIR .'/includes/emails/class-b2bking-new-customer-requires-approval-email.php';

	    $email_classes['B2bking_Your_Account_Approved_Email'] = include B2BKING_DIR .'/includes/emails/class-b2bking-your-account-approved-email.php';

	    $email_classes['B2bking_New_Offer_Email'] = include B2BKING_DIR .'/includes/emails/class-b2bking-new-offer-email.php';

	    return $email_classes;
	}

	// Add email actions
	function b2bking_add_email_actions( $actions ) {
	    $actions[] = 'b2bking_account_approved_finish';
	    $actions[] = 'b2bking_new_message';
	    $actions[] = 'b2bking_new_offer';
	    return $actions;
	}

	// Add invoice payment gateway
	function b2bking_add_invoice_gateway ( $methods ){
		if ( ! class_exists( 'B2BKing_Invoice_Gateway' ) ) {
			include_once('class-b2bking-invoice-gateway.php');
			$methods[] = 'B2BKing_Invoice_Gateway';
		}
    	return $methods;
	}

	// Add company approval gateway
	function b2bking_add_approval_gateway ( $methods ){
		if ( ! class_exists( 'B2BKing_Approval_Gateway' ) ) {
			include_once('class-b2bking-company-approval-gateway.php');
			$methods[] = 'B2BKing_Approval_Gateway';
		}
    	return $methods;
	}

	function b2bking_place_order_approval_text( $button_text ) {
		$user_id = get_current_user_id();
		$account_type = get_user_meta($user_id,'b2bking_account_type', true);
		if ($account_type === 'subaccount'){
			// check if requires approval
			$permission_approval = filter_var(get_user_meta($user_id, 'b2bking_account_permission_buy_approval', true),FILTER_VALIDATE_BOOLEAN);
			if ($permission_approval === true){
				$button_text = esc_html__('Send for approval','b2bking'); // new text is here 
			}
		}
		return $button_text;
	}

	// Add purchase order gateway
	function b2bking_add_purchase_order_gateway ( $methods ){
		if ( ! class_exists( 'B2BKing_Purchase_Order_Gateway' ) ) {
			include_once('class-b2bking-purchase-order-gateway.php');
			$methods[] = 'B2BKing_Purchase_Order_Gateway';
		}
    	return $methods;
	}

	function b2bking_po_number_pip( $type, $action, $document, $order ) {
		if ( 'invoice' != $type ) {
			return;
		}

		$payment_method = version_compare( WC_VERSION, '3.0', '<' ) ? $order->payment_method : $order->get_payment_method();
		$order_id = version_compare( WC_VERSION, '3.0', '<' ) ? $order->id : $order->get_id();

		if ( 'B2BKing_Purchase_Order_Gateway' === $payment_method ) {
			$po_number = get_post_meta( $order_id, '_po_number', true );
			/* translators: Placeholder: %1$s - opening <strong> tag, %2$s - coupons count (used in order), %3$s - closing </strong> tag - %4$s - coupons list */
			printf( '<div class="purchase-order-number">' . __( '%1$sPurchase order number:%2$s %3$s', 'woocommerce-gateway-purchase-order' ) . '</div>', '<strong>', '</strong>', esc_html( $po_number ) );
		}
	}


	function b2bking_display_order_number ( $order ) {
		$payment_method = version_compare( WC_VERSION, '3.0', '<' ) ? $order->payment_method : $order->get_payment_method();
		$order_id = version_compare( WC_VERSION, '3.0', '<' ) ? $order->id : $order->get_id();

		if ( 'B2BKing_Purchase_Order_Gateway' === $payment_method ) {
			$po_number = get_post_meta( $order_id, '_po_number', true );
			if ( '' != $po_number ) {
				if ( 'woocommerce_order_details_after_order_table' == current_filter() ) {
					echo '<ul class="woocommerce-order-overview woocommerce-thankyou-order-details order_details">';
					echo '<li class="woocommerce-order-overview__purchase-order purchase-order">' . __( 'Purchase Order Number:', 'woocommerce-gateway-purchase-order' ) . '<strong>' . $po_number . '</strong></li>';
					echo '</ul>';
				} else {
					echo '<p class="form-field form-field-wide"><strong>' . __( 'Purchase Order Number:', 'woocommerce-gateway-purchase-order' ) . '</strong><h2>' . $po_number . '</h2></p>' . "\n";
				}
			}
		}
	}


	// Helps prevent public code from running on login / register pages, where is_admin() returns false
	function b2bking_is_login_page() {
		if(isset($GLOBALS['pagenow'])){
	    	return in_array( $GLOBALS['pagenow'],array( 'wp-login.php', 'wp-register.php', 'admin.php' ),  true  );
	    }
	}


	function b2bking_admin_customers_ajax(){
    	// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$start = sanitize_text_field($_POST['start']);
		$length = sanitize_text_field($_POST['length']);
		$search = sanitize_text_field($_POST['search']['value']);
		$pagenr = ($start/$length)+1;

		if(get_option( 'b2bking_plugin_status_setting', 'b2b' ) === 'hybrid'){
			$args = array(
				'meta_key'     => 'b2bking_b2buser',
				'meta_value'   => 'yes',
			    'role'    => 'customer',
			    'number'  => $length,
			    'search' => "*{$search}*",
			    'search_columns' => array(
			        'display_name',
		        ),
			    'paged'   => floatval($pagenr),
			    'fields'=> array('ID', 'display_name'),
			);
		} else {
			$args = array(
			    'role'    => 'customer',
			    'number'  => $length,
			    'search' => "*{$search}*",
			    'search_columns' => array(
			        'display_name',
		        ),
			    'paged'   => floatval($pagenr),
			    'fields'=> array('ID', 'display_name'),
			);
		}
		

		$users = get_users( $args );

		$data = array(
			'length'=> $length,
			'data' => array()
		);

		foreach ( $users as $user ) {

			$user_id = $user->ID;
			$original_user_id = $user_id;
			$username = $user->display_name;

			// first check if subaccount. If subaccount, user is equivalent with parent
			$account_type = get_user_meta($user_id, 'b2bking_account_type', true);
			if ($account_type === 'subaccount'){
				// get parent
				$parent_account_id = get_user_meta ($user_id, 'b2bking_account_parent', true);
				$user_id = $parent_account_id;
				$account_type = esc_html__('Subaccount','b2bking');
			} else {
				$account_type = esc_html__('Main business account','b2bking');
			}

			$company_name = get_user_meta($user_id, 'billing_company', true);
			if (empty($company_name)){
				$company_name = '-';
			}

			$b2b_enabled = get_user_meta($user_id, 'b2bking_b2buser', true);
			if ($b2b_enabled === 'yes'){
				$b2b_enabled = 'Business';
			} else {
				$b2b_enabled = 'Consumer';
				$account_type = '-';
			}

			$group_name = get_the_title(b2bking()->get_user_group($user_id));
			if (empty($group_name)){
				$group_name = '-';
				if ($b2b_enabled !== 'yes'){
					$group_name = 'B2C Users';
				}
			}

			$approval = get_user_meta($user_id, 'b2bking_account_approved', true);
			if (empty($approval)){
				$approval = '-';
			} else if ($approval === 'no'){
				$approval = esc_html__('Waiting Approval','b2bking');
			}

			$name_link = '<a href="'.esc_attr(get_edit_user_link($original_user_id)).'">'.esc_html( $username ).'</a>';

			if (defined('SALESKING_DIR')){
				$agent = get_user_meta($user_id, 'salesking_assigned_agent', true);
				$agent = new WP_User($agent);
				$agent = '<td><a href="'.esc_attr(get_edit_user_link($agent->ID)).'">'.esc_html( $agent->user_login ).'</a></td>';

				array_push($data['data'],array($name_link, $company_name, $group_name, $account_type, $approval, $agent));

			} else {
				array_push($data['data'],array($name_link, $company_name, $group_name, $account_type, $approval));

			}

			
		}

		echo json_encode($data);
		
		exit();
	} 
	
 	// Update conversation with user message meta
	function b2bkingconversationmessage(){

    	// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		do_action('b2bking_conversation_message_start');

		// If nonce verification didn't fail, run further
		$message = sanitize_textarea_field($_POST['message']);
		$conversationid = sanitize_text_field($_POST['conversationid']);

		$currentuser = wp_get_current_user()->user_login;
		$conversationuser = get_post_meta ($conversationid, 'b2bking_conversation_user', true);

		// Check message not empty
		if ($message !== NULL && trim($message) !== ''){
			// Check user permission against Conversation user meta. Check subaccounts as well
			$current_user_id = get_current_user_id();
		    $subaccounts_list = get_user_meta($current_user_id,'b2bking_subaccounts_list', true);
		    $subaccounts_list = explode(',', $subaccounts_list);
		    $subaccounts_list = array_filter($subaccounts_list);
		    array_push($subaccounts_list, $current_user_id);

		    $subaccounts_list = apply_filters('b2bking_conversation_permission_list', $subaccounts_list, $conversationid, $current_user_id, $conversationuser);

		    // if current account is subaccount AND has permission to view all account conversations, add parent account+all subaccounts lists
		    $account_type = get_user_meta($current_user_id, 'b2bking_account_type', true);
		    if ($account_type === 'subaccount'){
		    	$permission_view_all_conversations = filter_var(get_user_meta($current_user_id, 'b2bking_account_permission_view_conversations', true),FILTER_VALIDATE_BOOLEAN);
		    	if ($permission_view_all_conversations === true){
		    		// has permission
		    		$parent_account = get_user_meta($current_user_id, 'b2bking_account_parent', true);
		    		$parent_subaccounts_list = explode(',', get_user_meta($parent_account, 'b2bking_subaccounts_list', true));
		    		$parent_subaccounts_list = array_filter($parent_subaccounts_list); // filter blank, null, etc.
		    		array_push($parent_subaccounts_list, $parent_account); // add parent itself to form complete parent accounts list

		    		$subaccounts_list = array_merge($subaccounts_list, $parent_subaccounts_list);
		    	}
		    }

		    foreach ($subaccounts_list as $user){
		    	$subaccounts_list[$user] = get_user_by('id', $user)->user_login;
		    }

		    if (in_array($conversationuser, $subaccounts_list)){
				$nr_messages = get_post_meta ($conversationid, 'b2bking_conversation_messages_number', true);
				$current_message_nr = $nr_messages+1;
				update_post_meta( $conversationid, 'b2bking_conversation_message_'.$current_message_nr, $message);
				update_post_meta( $conversationid, 'b2bking_conversation_messages_number', $current_message_nr);
				update_post_meta( $conversationid, 'b2bking_conversation_message_'.$current_message_nr.'_author', $currentuser );
				update_post_meta( $conversationid, 'b2bking_conversation_message_'.$current_message_nr.'_time', time() );

				do_action('b2bking_conversation_after_message_inserted', $conversationid, $current_message_nr, $message);

				// if status is new, change to open
				$status = get_post_meta ($conversationid, 'b2bking_conversation_status', true);
				if ($status === 'new'){
					update_post_meta( $conversationid, 'b2bking_conversation_status', 'open');
				}

				
				$recipient = get_option( 'admin_email' );

				$recipient = apply_filters('b2bking_recipient_new_message', $recipient, $conversationid);

				do_action( 'b2bking_new_message', $recipient, $message, $current_user_id, $conversationid );
			}
		}
	}


	// Create new conversation by user
	function b2bkingsendinquiry(){

    	// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		do_action( 'b2bking_conversation_message_start');

		// If nonce verification didn't fail, run further
		$message = sanitize_textarea_field($_POST['message']);
		$title = sanitize_text_field($_POST['title']);
		$type = sanitize_text_field($_POST['type']);
		$currentuser = wp_get_current_user()->user_login;
		$conversationid = '';

		// Check message not empty
		if ($message !== NULL && trim($message) !== ''){
			// Insert post
			$args = array(
				'post_title' => $title, 
				'post_type' => 'b2bking_conversation',
				'post_status' => 'publish', 
			);
			$conversationid = wp_insert_post( $args);

			update_post_meta( $conversationid, 'b2bking_conversation_user', $currentuser);
			update_post_meta( $conversationid, 'b2bking_conversation_status', 'new' );
			update_post_meta( $conversationid, 'b2bking_conversation_type', $type );
			update_post_meta( $conversationid, 'b2bking_conversation_message_1', $message);
			update_post_meta( $conversationid, 'b2bking_conversation_messages_number', 1);
			update_post_meta( $conversationid, 'b2bking_conversation_message_1_author', $currentuser );
			update_post_meta( $conversationid, 'b2bking_conversation_message_1_time', time() );
			update_post_meta( $conversationid, 'b2bking_quote_requester', $currentuser);
			update_post_meta( $conversationid, 'b2bking_quote_products', '|');

			do_action( 'b2bking_conversation_after_message_inserted',$conversationid);

			// Add vendor if DOKAN
			if (isset($_POST['vendor'])){
				$vendor_id = sanitize_text_field($_POST['vendor']);
				$vendor_username = get_user_meta($vendor_id,'dokan_store_name', true);
				update_post_meta($conversationid,'b2bking_conversation_vendor', $vendor_username);
				do_action('b2bking_send_inquiry_vendor_dokan', $conversationid, $vendor_id);

				// add conversation to vendor's list of conversations
				$list_conversations = get_user_meta($vendor_id,'b2bking_dokan_vendor_conversations_list_ids', true);
				$list_conversations .= ','.$conversationid.',';
				update_user_meta($vendor_id, 'b2bking_dokan_vendor_conversations_list_ids', $list_conversations);
			}

			// Add vendor if WCFM
			if (isset($_POST['vendor'])){
				$vendor_id = sanitize_text_field($_POST['vendor']);
				$vendor_username = get_user_meta($vendor_id,'wcfmmp_store_name', true);
				update_post_meta($conversationid,'b2bking_conversation_vendor', $vendor_username);
				// add conversation to vendor's list of conversations
				$list_conversations = get_user_meta($vendor_id,'b2bking_wcfm_vendor_conversations_list_ids', true);
				$list_conversations .= ','.$conversationid.',';
				update_user_meta($vendor_id, 'b2bking_wcfm_vendor_conversations_list_ids', $list_conversations);
			}

			// Add vendor if MarketKing
			if (isset($_POST['vendor'])){
				if (defined('MARKETKINGPRO_DIR')){
					$vendor_id = sanitize_text_field($_POST['vendor']);
					$vendor_username = marketking()->get_store_name_display($vendor_id);
					update_post_meta($conversationid,'b2bking_conversation_vendor', $vendor_username);
					// add conversation to vendor's list of conversations
					$list_conversations = get_user_meta($vendor_id,'b2bking_marketking_vendor_conversations_list_ids', true);
					$list_conversations .= ','.$conversationid.',';
					update_user_meta($vendor_id, 'b2bking_marketking_vendor_conversations_list_ids', $list_conversations);
				}
				
			}

			$recipient = get_option( 'admin_email' );
			$recipient = apply_filters('b2bking_recipient_new_message', $recipient, $conversationid);

			// send email notification
			do_action( 'b2bking_new_message', $recipient, $message, get_current_user_id(), $conversationid );
		}
		
		// return conversation id URL
		echo esc_url(add_query_arg('id', $conversationid, wc_get_account_endpoint_url(get_option('b2bking_conversation_endpoint_setting','conversation'))));
		exit();
	}

	function b2bkingquoteupload(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}
		
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );

		if (apply_filters('b2bking_quote_upload_default_process', true)){
			// Upload the file
	        $attachment_id = media_handle_upload( 'file', 0 );
	        // Set attachment author as the user who uploaded it
	        $attachment_post = array(
	            'ID'          => $attachment_id,
	            'post_author' => get_current_user_id()
	        );
	        wp_update_post( $attachment_post );   
	        

			echo wp_get_attachment_url($attachment_id);
			exit();
		} else {
			do_action('b2bking_quote_upload_process_changed');
		}

        
		
	}

	function b2bkingrequestquotecart(){

		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		//File Uploading Validation
		do_action( 'b2bking_conversation_message_start');

		// If nonce verification didn't fail, run further
		$message = sanitize_textarea_field($_POST['message']);
		$messagecart = '<b>'.esc_html__('Requested items:','b2bking').' </b><br /><br />';
		// Add cart details and quantities at the beginning of the message
		$items = WC()->cart->get_cart();
		$productsstring = '';
		foreach($items as $item => $values) { 
            $product =  wc_get_product( $values['data']->get_id());
            $name = $product->get_formatted_name();
            $product_name = apply_filters('b2bking_filter_product_name_quote',$name,$product);
            $messagecart .= $product_name.'  - '.esc_html__('Quantity: ','b2bking').$values['quantity'].'<br>';

            if (strlen($product_name.'  - '.esc_html__('Quantity: ','b2bking').$values['quantity']) > 60) {
            	$messagecart .= '<br>';
            }

            // Formatted cart item data, e.g. product addons
            $itemdata = wc_get_formatted_cart_item_data($values);
            if (!empty($itemdata)){
            	$messagecart .= strip_tags($itemdata).'<br>';
            	if (strlen($itemdata) > 50){
            		$messagecart .= '<br>';
            	}
            }

            $messagecart =  apply_filters('b2bking_quote_item_cart', $messagecart, $values);

            // get item  price instead of 0
            if( $product->is_on_sale() ) {
            	$product_price = $product->get_sale_price();
            } else {
            	$product_price = $product->get_price();	
            }
            if (empty($product_price) || $product_price === null || $product_price === false){
            	$product_price = '0';
            }


            $productsstring .= 'product_'.$values['data']->get_id().';'.$values['quantity'].';'.$product_price.'|';
        }
        $productsstring = substr($productsstring, 0, -1);

        if (!empty($message)){
        	$message = $messagecart.'<br /><b>'.esc_html__('Message:','b2bking').'</b><br /><br />'.$message.'<br /><br />';
        } else {
        	$message = $messagecart.'<br />';

        }

        // get all other elements (custom quote fields)
        $quotetextfields = sanitize_text_field($_POST['quotetextids']);
        $quotecheckboxids = sanitize_text_field($_POST['quotecheckboxids']);
        $quotefileids = sanitize_text_field($_POST['quotefileids']);

        $quotetextfields = explode(',', $quotetextfields);
        $quotecheckboxids = explode(',', $quotecheckboxids);
        $quotefileids = explode(',', $quotefileids);

        $quotetextfields = array_merge($quotetextfields, $quotecheckboxids);

        foreach ($quotetextfields as $field_id){
        	$value = sanitize_text_field($_POST['b2bking_field_'.$field_id]);
        	if (!empty($value)){
        		// get label
        		$label = get_post_meta($field_id,'b2bking_custom_field_field_label', true);
        		$message.='<b>'.$label.': '.'</b>'.$value.'<br>';
        	}
        }

        foreach ($quotefileids as $field_id){
        	$value = sanitize_text_field($_POST['b2bking_field_'.$field_id]);
        	if (!empty($value)){
        		// get label
        		$label = get_post_meta($field_id,'b2bking_custom_field_field_label', true);

        		if (apply_filters('b2bking_quote_file_ids_default_message', true)){
        			$message.='<b>'.$label.': '.'</b><a href="'.$value.'">'.esc_html__('View File','b2bking').'</a><br>';
        		} else {
        			$message = apply_filters('b2bking_quote_file_ids_message', $message, $value, $field_id);
        		}
        	}
        }

        // custom quote fields finish
		$title = sanitize_text_field($_POST['title']);
		$type = sanitize_text_field($_POST['type']);
		$currentuser = wp_get_current_user()->user_login;

		// if quote request is made by guest or B2C
		if (!is_user_logged_in() || (get_user_meta(get_current_user_id(), 'b2bking_b2buser', true) !== 'yes' && get_option( 'b2bking_plugin_status_setting', 'b2b' ) === 'hybrid')){
			$guest_name = sanitize_text_field($_POST['name']);
			$guest_email = sanitize_text_field($_POST['email']);
			$currentuser = esc_html__('Name: ', 'b2bking').$guest_name.' '.esc_html__(' Email: ', 'b2bking').$guest_email;

			$guest_quote_message = esc_html__('We have received your quote request and will be in touch with you shortly. Here is your quote request:','b2bking').'<br><br>'.$message;
			do_action( 'b2bking_new_message', $guest_email, $guest_quote_message, 'Quoteemail:1', 0);
			do_action('b2bking_quote_logged_out_user', $guest_email);	
		}

		// optionally, also send email to logged in users
		if (apply_filters('b2bking_send_quote_email_logged_in_users', false)){
			if (is_user_logged_in() && (get_user_meta(get_current_user_id(), 'b2bking_b2buser', true) === 'yes')){
				$cuser = wp_get_current_user();
				$guest_email = $cuser->user_email;

				$guest_quote_message = esc_html__('We have received your quote request and will be in touch with you shortly. Here is your quote request:','b2bking').'<br><br>'.$message;
				do_action( 'b2bking_new_message', $guest_email, $guest_quote_message, 'Quoteemail:1', 0);
			}
			
		}

		$conversationid = '';

		// Insert post
		$args = array(
			'post_title' => $title, 
			'post_type' => 'b2bking_conversation',
			'post_status' => 'publish', 
		);
		$conversationid = wp_insert_post( $args);

		if (isset($guest_email)){
			update_post_meta($conversationid, 'b2bking_quote_requester', $guest_email);
		} else {
			update_post_meta($conversationid, 'b2bking_quote_requester', $currentuser);
		}
		update_post_meta($conversationid, 'b2bking_quote_products', $productsstring);

		update_post_meta( $conversationid, 'b2bking_conversation_user', $currentuser);
		update_post_meta( $conversationid, 'b2bking_conversation_status', 'new' );
		update_post_meta( $conversationid, 'b2bking_conversation_type', $type );
		update_post_meta( $conversationid, 'b2bking_conversation_message_1', $message);
		update_post_meta( $conversationid, 'b2bking_conversation_messages_number', 1);
		update_post_meta( $conversationid, 'b2bking_conversation_message_1_author', $currentuser );
		update_post_meta( $conversationid, 'b2bking_conversation_message_1_time', time() );

		// Add vendor if DOKAN
		if (isset($_POST['vendor'])){
			$vendor_id = sanitize_text_field($_POST['vendor']);
			$vendor_username = get_user_meta($vendor_id,'dokan_store_name', true);
			update_post_meta($conversationid,'b2bking_conversation_vendor', $vendor_username);
			// add conversation to vendor's list of conversations
			$list_conversations = get_user_meta($vendor_id,'b2bking_dokan_vendor_conversations_list_ids', true);
			$list_conversations .= ','.$conversationid.',';
			update_user_meta($vendor_id, 'b2bking_dokan_vendor_conversations_list_ids', $list_conversations);
		}

		// if DOKAN vendor, set vendor
		if (isset($_POST['vendor'])){
			$vendor_store = sanitize_text_field($_POST['vendor']);
			if (empty(trim($vendor_store)) || $vendor_store === null){
				// do nothing, quote request is to site admin
			} else {
				$vendor_users = get_users(array('meta_key' => 'dokan_store_name', 'meta_value' => $vendor_store));
				if (!empty($vendor_users)){
					$vendorobj = $vendor_users[0];
					$vendorlogin = $vendorobj->user_login;
				} else {
					$vendorlogin = $vendor_store;
					$vendorobj = get_user_by('login', $vendorlogin);
				}

				update_post_meta($conversationid,'b2bking_conversation_vendor',$vendorlogin);
				// add conversation to vendor's list of conversations
				$list_conversations = get_user_meta($vendorobj->ID,'b2bking_dokan_vendor_conversations_list_ids', true);
				$list_conversations .= ','.$conversationid.',';
				update_user_meta($vendorobj->ID, 'b2bking_dokan_vendor_conversations_list_ids', $list_conversations);
			}
		}

		// Add vendor if WCFM
		if (isset($_POST['vendor'])){
			$vendor_id = sanitize_text_field($_POST['vendor']);
			$vendor_username = get_user_meta($vendor_id,'wcfmmp_store_name', true);
			update_post_meta($conversationid,'b2bking_conversation_vendor', $vendor_username);
			// add conversation to vendor's list of conversations
			$list_conversations = get_user_meta($vendor_id,'b2bking_wcfm_vendor_conversations_list_ids', true);
			$list_conversations .= ','.$conversationid.',';
			update_user_meta($vendor_id, 'b2bking_wcfm_vendor_conversations_list_ids', $list_conversations);
		}

		// if WCFM vendor, set vendor
		if (isset($_POST['vendor'])){
			$vendor_store = sanitize_text_field($_POST['vendor']);
			if (empty(trim($vendor_store)) || $vendor_store === null){
				// do nothing, quote request is to site admin
			} else {
				$vendor_users = get_users(array('meta_key' => 'wcfmmp_store_name', 'meta_value' => $vendor_store));
				if (!empty($vendor_users)){
					$vendorobj = $vendor_users[0];
					$vendorlogin = $vendorobj->user_login;
				} else {
					$vendorlogin = $vendor_store;
					$vendorobj = get_user_by('login', $vendorlogin);
				}

				update_post_meta($conversationid,'b2bking_conversation_vendor',$vendorlogin);
				// add conversation to vendor's list of conversations
				$list_conversations = get_user_meta($vendorobj->ID,'b2bking_wcfm_vendor_conversations_list_ids', true);
				$list_conversations .= ','.$conversationid.',';
				update_user_meta($vendorobj->ID, 'b2bking_wcfm_vendor_conversations_list_ids', $list_conversations);
			}
		}

		if (defined('MARKETKINGPRO_DIR')){
			// Add vendor if MarketKing
			if (isset($_POST['vendor'])){
				$vendor_id = sanitize_text_field($_POST['vendor']);
				$vendor_username = marketking()->get_store_name_display($vendor_id);
				update_post_meta($conversationid,'b2bking_conversation_vendor', $vendor_username);
				// add conversation to vendor's list of conversations
				$list_conversations = get_user_meta($vendor_id,'b2bking_marketking_vendor_conversations_list_ids', true);
				$list_conversations .= ','.$conversationid.',';
				update_user_meta($vendor_id, 'b2bking_marketking_vendor_conversations_list_ids', $list_conversations);
			}

			// if MarketKing vendor, set vendor
			if (isset($_POST['vendor'])){
				$vendor_store = sanitize_text_field($_POST['vendor']);
				if (empty(trim($vendor_store)) || $vendor_store === null){
					// do nothing, quote request is to site admin
				} else {
					$vendorobj = new WP_User($vendor_store);
					$vendorlogin = $vendorobj->user_login;

					update_post_meta($conversationid,'b2bking_conversation_vendor',$vendorlogin);
					// add conversation to vendor's list of conversations
					$list_conversations = get_user_meta($vendorobj->ID,'b2bking_marketking_vendor_conversations_list_ids', true);
					$list_conversations .= ','.$conversationid.',';
					update_user_meta($vendorobj->ID, 'b2bking_marketking_vendor_conversations_list_ids', $list_conversations);
				}
			}
		}
		

		if (!is_user_logged_in() || (get_user_meta(get_current_user_id(), 'b2bking_b2buser', true) !== 'yes' && get_option( 'b2bking_plugin_status_setting', 'b2b' ) === 'hybrid')){
			update_post_meta( $conversationid, 'b2bking_conversation_message_2', sanitize_text_field(esc_html__('This quote request was sent by a logged out user, without an account, or a B2C user without access to conversations. Please email the user directly!', 'b2bking')));
			update_post_meta( $conversationid, 'b2bking_conversation_messages_number', 2);
			update_post_meta( $conversationid, 'b2bking_conversation_message_2_author', $currentuser );
			update_post_meta( $conversationid, 'b2bking_conversation_message_2_time', time() );

		}

		// send email notification
		$recipient = get_option( 'admin_email' );
		$recipient = apply_filters('b2bking_recipient_new_message_quote', $recipient, $conversationid);

		if (!is_user_logged_in() || (get_user_meta(get_current_user_id(), 'b2bking_b2buser', true) !== 'yes' && get_option( 'b2bking_plugin_status_setting', 'b2b' ) === 'hybrid')){
			do_action( 'b2bking_new_message', $recipient, $message, $currentuser, $conversationid );
		} else {
			do_action( 'b2bking_new_message', $recipient, $message, get_current_user_id(), $conversationid );
		}

		// empty cart
		WC()->cart->empty_cart();

		// return conversation id URL
		echo esc_url(add_query_arg('id', $conversationid, wc_get_account_endpoint_url(get_option('b2bking_conversation_endpoint_setting','conversation'))));
		exit();
		
	}

	function b2bkingclearcaches(){

		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		// clear b2bking transients
		b2bking()->clear_caches_transients();

		echo 'success';
	}

	function b2bkingaddoffer(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}
		// If nonce verification didn't fail, run further

		$offer_id = sanitize_text_field($_POST['offer']);

		do_action('b2bking_add_offer_start_ajax', $offer_id);

		// Run permission check on offer
		$user = wp_get_current_user() -> user_login;
		$currentusergroupidnr = b2bking()->get_user_group();

		// If permission check is true
		if (intval(get_post_meta($offer_id, 'b2bking_user_'.$user, true)) === 1 || intval(get_post_meta($offer_id, 'b2bking_group_'.$currentusergroupidnr, true)) === 1){

			// Add offer to cart
			$offer_details = get_post_meta(apply_filters( 'wpml_object_id', $offer_id, 'post' , true), 'b2bking_offer_details', true);
			$products = explode ('|', $offer_details);
			$cart_item_data['b2bking_offer_id'] = $offer_id;
			$cart_item_data['b2bking_offer_name'] = get_the_title(apply_filters( 'wpml_object_id', $offer_id, 'post' , true));
			$cart_item_data['b2bking_numberofproducts'] = count($products);
			$i = 1;
			foreach($products as $product){
				$details = explode(';',$product);

				// if item is in the form product_id, change title
				$isproductid = explode('_', $details[0]); 
				if ($isproductid[0] === 'product'){
					// it is a product+id, get product title
					$newproduct = wc_get_product($isproductid[1]);
					$details[0] = $newproduct->get_name();

					//if product is a variation with 3 or more attributes, need to change display because get_name doesnt 
					// show items correctly
					if (is_a($newproduct,'WC_Product_Variation')){
						$attributes = $newproduct->get_variation_attributes();
						$number_of_attributes = count($attributes);
						if ($number_of_attributes > 2){
							$details[0].=' - ';
							foreach ($attributes as $attribute){
								$details[0].=$attribute.', ';
							}
							$details[0] = substr($details[0], 0, -2);
						}
					}

					if (isset($cart_item_data['b2bking_products_stock'])){
						$temp_stock = $cart_item_data['b2bking_products_stock'].$isproductid[1].':'.$details[1].';';
					} else {
						$temp_stock = $isproductid[1].':'.$details[1].';'; // id:quantity;id:quantity
					}
					
					$cart_item_data['b2bking_products_stock'] = $temp_stock; 
				}

				$cart_item_data['b2bking_product_'.$i.'_name'] = $details[0];
				$cart_item_data['b2bking_product_'.$i.'_quantity'] = $details[1];
				$cart_item_data['b2bking_product_'.$i.'_price'] = $details[2];
				$i++;
			}

			$cart_item_data = apply_filters('b2bking_before_add_offer_to_cart', $cart_item_data);

			// Create B2B offer product if it doesn't exist
			$offer_id = intval(get_option('b2bking_offer_product_id_setting', 0));
			if ( !get_post_status ( $offer_id ) ) {
				$offer = array(
				    'post_title' => 'Offer',
				    'post_status' => 'customoffer',
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
				update_option( 'b2bking_offer_product_id_setting', $product_id );
				$offer_id = intval(get_option('b2bking_offer_product_id_setting', 0));
			}
			
			$offer_id = apply_filters('b2bking_offer_id_before_add_offer_to_cart', $offer_id, $cart_item_data['b2bking_offer_id']);

			WC()->cart->add_to_cart( $offer_id, 1, 0, array(), $cart_item_data);

			do_action('b2bking_after_add_offer_to_cart', $offer_id);
		} else {
			// do nothing
		}

		echo 'success';
		exit();	
	}

	function b2bkingaddcredit(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bkingcredit_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}
		// If nonce verification didn't fail, run further

		$amount = sanitize_text_field($_POST['amount']);
		$cart_item_data['b2bking_credit_amount'] = $amount;
		// Create B2B offer product if it doesn't exist
		$credit_id = intval(get_option('b2bking_credit_product_id_setting', 0));
		if ( !get_post_status ( $credit_id ) ) {
			$credit = array(
			    'post_title' => 'Credit',
			    'post_status' => 'publish',
			    'post_type' => 'product',
			    'post_author' => 1,
			);
			$product_id = wp_insert_post($credit);
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
			update_option( 'b2bking_credit_product_id_setting', $product_id );
			$credit_id = intval(get_option('b2bking_credit_product_id_setting', 0));
		}
		
		WC()->cart->add_to_cart( $credit_id, 1, 0, array(), $cart_item_data);


		echo 'success';
		exit();	
	}

	function b2bkingcheckdeliverycountryvat(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}
		// If nonce verification didn't fail, run further


		// Apply VAT Rules
		B2bking_Dynamic_Rules::b2bking_dynamic_rule_tax_exemption();
		echo 'success';
		exit();	
	}

	function b2bkingvalidatevat(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}
		// If nonce verification didn't fail, run further

		$vat_number_inputted = sanitize_text_field($_POST['vat']);
		$vat_number_inputted = strtoupper(str_replace(array('.', ' '), '', $vat_number_inputted));
		$country_inputted = sanitize_text_field($_POST['country']);

		// validate number
		$error_details = '';
		$validation = new stdClass();
		$validation -> valid = 1;
		$debug = '1';
		$countries_list_eu = array('AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'EL', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE');

		if (!empty($vat_number_inputted)){
			if (apply_filters('b2bking_set_default_prefix_vat', false) !== false){
				$prefix = apply_filters('b2bking_set_default_prefix_vat', false);
				// if vat nr does not start with the prefix, add the prefix
				if (substr( $vat_number_inputted, 0, 2 ) !== $prefix){
					$vat_number_inputted = $prefix.$vat_number_inputted;
				}
			}
		}
		
		if (in_array(substr($vat_number_inputted, 0, 2), $countries_list_eu)){
			// check vat
			try {
				$client = new SoapClient("http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl");

				$country_code = substr($vat_number_inputted, 0, 2); // take first 2 chars
				$vat_number = substr($vat_number_inputted, 2); // remove first 2 chars

				$validation = $client->checkVat(array(
				  'countryCode' => $country_code,
				  'vatNumber' => $vat_number
				));
				$error_details = '';

				// check country is same as VAT country
				if (trim(strtolower($country_inputted)) !== trim(strtolower($country_code))){
					// check exception Greece (GR) has EL VAT code
					if( (trim(strtolower($country_inputted)) === 'gr') && (trim(strtolower($country_code)) === 'el')){
						// if indeed the VAT number is EL and country is GR, do nothing
					} else {
						$validation->valid=0;
					}
				}

			} catch (Exception $e) {
				$error = $e->getMessage();
				$error_array = array(
				    'INVALID_INPUT'       => esc_html__('CountryCode is invalid or the VAT number is empty', 'b2bking'),
				    'SERVICE_UNAVAILABLE' => esc_html__('VIES VAT Service is unavailable. Try again later.', 'b2bking'),
				    'MS_UNAVAILABLE'      => esc_html__('VIES VAT Member State Service is unavailable.', 'b2bking'),
				    'TIMEOUT'             => esc_html__('Service timeout. Try again later', 'b2bking'),
				    'SERVER_BUSY'         => esc_html__('VAT Server is too busy. Try again later.', 'b2bking'),
				);

				if ( array_key_exists( $error , $error_array ) ) {
				    $error_details .= $error_array[ $error ];
				}
				$validation->valid=0;
			}
		} else {
			$validation->valid=0;
		}


		if(intval($validation->valid) === 1){
			echo 'valid';

			// if user logged in, save the value
			if (is_user_logged_in()){
				$user_id = get_current_user_id();
				// get field ID
				$custom_fields = get_posts([
		    		'post_type' => 'b2bking_custom_field',
		    	  	'post_status' => 'publish',
		    	  	'numberposts' => -1,
		    	  	'meta_key' => 'b2bking_custom_field_sort_number',
	    	  	    'orderby' => 'meta_value_num',
	    	  	    'order' => 'ASC',
		    	  	'meta_query'=> array(
		    	  		'relation' => 'AND',
		                array(
	                        'key' => 'b2bking_custom_field_status',
	                        'value' => 1
		                ),
		                array(
		                	'relation' => 'OR',
			                array(
		                        'key' => 'b2bking_custom_field_billing_connection',
		                        'value' => 'billing_vat'
			                ),
			            ),			               
	            	)
		    	]);
		    	foreach ($custom_fields as $field){
		    		// update data
		    		update_user_meta($user_id, 'b2bking_custom_field_'.$field->ID, $vat_number_inputted);
		    		update_user_meta($user_id, 'b2bking_custom_field_'.$field->ID.'bis', $vat_number_inputted);
		    	}
				
				update_user_meta( $user_id, 'b2bking_user_vat_status', 'validated_vat');
			}

		} else {
			echo 'invalid';
		}

		exit();	
	}


	function b2bkingapproveuser(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}
		// If nonce verification didn't fail, run further

		$user_id = sanitize_text_field($_POST['user']);
		$group = sanitize_text_field($_POST['chosen_group']);

		if (isset($_POST['credit'])){
			$creditlimit = sanitize_text_field($_POST['credit']);
			if (!empty($creditlimit)){
				update_user_meta($user_id,'b2bking_user_credit_limit',$creditlimit);
			}
		}

		if (isset($_POST['salesagent'])){
			$salesagent = sanitize_text_field($_POST['salesagent']);
			if (!empty($salesagent)){
				update_user_meta($user_id,'salesking_assigned_agent',$salesagent);
			}
		}

		$email_address = sanitize_text_field(get_user_by('id', $user_id)->user_email);
		do_action( 'b2bking_account_approved_finish', $email_address );

		
		// approve account
		update_user_meta($user_id, 'b2bking_account_approved', 'yes');

		// if user was set to be agent
		if (substr($group, 0, 6) === 'salesk'){
			$group_id = explode('_', $group)[1];
			update_user_meta($user_id, 'salesking_group', $group_id);
			update_user_meta( $user_id, 'salesking_user_choice', 'agent');
			update_user_meta( $user_id, 'salesking_assigned_agent', 'none');
			
			do_action('b2bking_after_register_salesking_agent', $user_id);

		} else {

			if ($group !== 'b2c'){
				// place user in customer group 
				b2bking()->update_user_group($user_id, $group);


				if (apply_filters('b2bking_use_wp_roles', false)){
					// add role
					$user_obj = new WP_User($user_id);
					$user_obj->add_role('b2bking_role_'.$group);
				}
				// set user as b2b enabled
				update_user_meta($user_id, 'b2bking_b2buser', 'yes');


			} else {
				// b2c user
				if (apply_filters('b2bking_use_wp_roles', false)){
					// add role
					$user_obj = new WP_User($user_id);
					$user_obj->add_role('b2bking_role_'.$group);
				}
				// set user as b2b enabled
				update_user_meta($user_id, 'b2bking_b2buser', 'no');
			}

			

		}


	

		echo 'success';
		exit();	
	}

	function b2bkingdeactivateuser(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}
		// If nonce verification didn't fail, run further

		$user_id = sanitize_text_field($_POST['user']);

		// approve account
		update_user_meta($user_id, 'b2bking_account_approved', 'no');
		update_user_meta($user_id, 'b2bking_b2buser', 'no');

		echo 'success';
		exit();	
	}

	function b2bkingrejectuser(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		// If nonce verification didn't fail, run further
		$user_id = sanitize_text_field($_POST['user']);

		// check if this function is being run by delete subaccount in the frontend
		if(isset($_POST['issubaccount'])){
			$current_user = get_current_user_id();
			// remove subaccount from user meta
			$subaccounts_number = intval(get_user_meta($current_user, 'b2bking_subaccounts_number', true));
			$subaccounts_number = $subaccounts_number - 1;
			update_user_meta($current_user, 'b2bking_subaccounts_number', sanitize_text_field($subaccounts_number));

			$subaccounts_list = get_user_meta($current_user, 'b2bking_subaccounts_list', true);
			$subaccounts_list = str_replace(','.$user_id,'',$subaccounts_list);
			update_user_meta($current_user, 'b2bking_subaccounts_list', sanitize_text_field($subaccounts_list));

			// assign orders to parent
			$args = array(
			    'customer_id' => $user_id
			);
			$orders = wc_get_orders($args);
			foreach ($orders as $order){
				$order_id = $order->get_id();
				$parent_user_id = get_user_meta($user_id,'b2bking_account_parent', true);

				update_post_meta($order_id,'_customer_user', $parent_user_id);
			}
		} else {
			// user rejection in admin backend
			do_action('b2bking_reject_user_admin_before_delete', $user_id);
		}

		// delete account
		wp_delete_user($user_id);

		if(!isset($_POST['issubaccount'])){
			do_action('b2bking_reject_user_admin_after_delete', $user_id);
		}

		echo 'success';
		exit();	
	}

	// Handles AJAX Download requests, enabling the download of user attachment during registration
	function b2bkinghandledownloadrequest(){

    	// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}


		$requested_file = $_REQUEST['attachment'];
		// If nonce verification didn't fail, run further
		$file = wp_get_attachment_url( $requested_file );

		if( ! $file ) {
			return;
		}

		if (intval(apply_filters('b2bking_download_file_go_to', 0)) === 1){
			echo $file;
		} else {
			//clean the fileurl
			$file_url  = stripslashes( trim( $file ) );
			//get filename
			$file_name = basename( $file );

			header("Expires: 0");
			header("Cache-Control: no-cache, no-store, must-revalidate"); 
			header('Cache-Control: pre-check=0, post-check=0, max-age=0', false); 
			header("Pragma: no-cache");	
			header("Content-Disposition:attachment; filename={$file_name}");
			header("Content-Type: application/force-download");

			readfile("{$file_url}");
		}

		
		exit();

	}

	function b2bking_create_subaccount(){

		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$parent_user_id = get_current_user_id();
		$subaccounts_maximum_limit = 1000;

		// Test subaccounts number
		$current_subaccounts_number = intval(get_user_meta($parent_user_id, 'b2bking_subaccounts_number', true));
		if ($current_subaccounts_number === NULL){
			$current_subaccounts_number = 0;
		}
		
		if (intval($current_subaccounts_number) < $subaccounts_maximum_limit){
			// proceed
			$username = sanitize_text_field($_POST['username']);
			$password = sanitize_text_field($_POST['password']);
			$name = sanitize_text_field($_POST['name']);
			$last_name = sanitize_text_field($_POST['lastName']);
			$job_title = sanitize_text_field($_POST['jobTitle']);
			$phone = sanitize_text_field($_POST['phone']);
			$email = sanitize_text_field($_POST['email']);
			$permission_buy = sanitize_text_field($_POST['permissionBuy']);
			$permission_buy_approval = sanitize_text_field($_POST['permissionBuyApproval']);
			$permission_view_orders = sanitize_text_field($_POST['permissionViewOrders']);
			$permission_view_offers = sanitize_text_field($_POST['permissionViewOffers']);
			$permission_view_conversations = sanitize_text_field($_POST['permissionViewConversations']);
			$permission_view_lists = sanitize_text_field($_POST['permissionViewLists']);

			$wc_create_customer_args = [
				'first_name' => $name,
				'last_name' => $last_name,
			];

			if (apply_filters('b2bking_disable_username_subaccounts', 0) === 0){
				$user_id = wc_create_new_customer($email, $username, $password, $wc_create_customer_args);
			} else {
				$user_id = wc_create_new_customer($email, '', $password, $wc_create_customer_args);
			}

			if ( ! (is_wp_error($user_id))){
				// no errors, proceed
				// set user meta
				update_user_meta($user_id, 'b2bking_b2buser', 'yes');
				update_user_meta($user_id, 'b2bking_account_type', 'subaccount');
				update_user_meta($user_id, 'b2bking_account_parent', $parent_user_id);
				update_user_meta($user_id, 'first_name', $name);
				update_user_meta($user_id, 'b2bking_account_phone', $phone);
				update_user_meta($user_id, 'b2bking_account_job_title', $job_title);
				update_user_meta($user_id, 'b2bking_account_permission_buy', $permission_buy); // true or false
				if (intval(get_option( 'b2bking_enable_company_approval_setting', 0 )) === 1){

					update_user_meta($user_id, 'b2bking_account_permission_buy_approval', $permission_buy_approval); // true or false
				}
				update_user_meta($user_id, 'b2bking_account_permission_view_orders', $permission_view_orders); // true or false
				update_user_meta($user_id, 'b2bking_account_permission_view_offers', $permission_view_orders); // true or false
				update_user_meta($user_id, 'b2bking_account_permission_view_conversations', $permission_view_orders); // true or false
				update_user_meta($user_id, 'b2bking_account_permission_view_lists', $permission_view_lists); // true or false

				// set parent subaccount details meta
				//$current_subaccounts_number = $current_subaccounts_number + 1;
				//update_user_meta($parent_user_id, 'b2bking_subaccounts_number', $current_subaccounts_number);

				$current_subaccounts_list = get_user_meta($parent_user_id, 'b2bking_subaccounts_list', true);
				if (empty($current_subaccounts_list)){
					$current_subaccounts_list = '';
				}
				$current_subaccounts_list = $current_subaccounts_list.','.$user_id;
				update_user_meta($parent_user_id, 'b2bking_subaccounts_list', $current_subaccounts_list);

				$userobj = new WP_User($user_id);
				$userobj->set_role('customer');

				do_action('b2bking_after_subaccount_created', $user_id);

				echo $user_id;
			} else {
				echo 'error'.$user_id->get_error_message();
			}

		} else {
			echo 'error_maximum_subaccounts';
		}
		
		exit();
	}

	function b2bking_update_subaccount(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$subaccount_id = sanitize_text_field($_POST['subaccountId']);
		$name = sanitize_text_field($_POST['name']);
		$last_name = sanitize_text_field($_POST['lastName']);
		$job_title = sanitize_text_field($_POST['jobTitle']);
		$phone = sanitize_text_field($_POST['phone']);
		$permission_buy = sanitize_text_field($_POST['permissionBuy']);
		$permission_buy_approval = sanitize_text_field($_POST['permissionBuyApproval']);
		$permission_view_orders = sanitize_text_field($_POST['permissionViewOrders']);
		$permission_view_offers = sanitize_text_field($_POST['permissionViewOffers']);
		$permission_view_conversations = sanitize_text_field($_POST['permissionViewConversations']);
		$permission_view_lists = sanitize_text_field($_POST['permissionViewLists']);

		// set user meta
		update_user_meta($subaccount_id, 'first_name', $name);
		update_user_meta($subaccount_id, 'b2bking_account_phone', $phone);
		update_user_meta($subaccount_id, 'b2bking_account_job_title', $job_title);
		update_user_meta($subaccount_id, 'b2bking_account_permission_buy', $permission_buy); // true or false
		if (intval(get_option( 'b2bking_enable_company_approval_setting', 0 )) === 1){

			update_user_meta($subaccount_id, 'b2bking_account_permission_buy_approval', $permission_buy_approval); // true or false
		}
		update_user_meta($subaccount_id, 'b2bking_account_permission_view_orders', $permission_view_orders); // true or false
		update_user_meta($subaccount_id, 'b2bking_account_permission_view_offers', $permission_view_offers); // true or false
		update_user_meta($subaccount_id, 'b2bking_account_permission_view_conversations', $permission_view_conversations); // true or false
		update_user_meta($subaccount_id, 'b2bking_account_permission_view_lists', $permission_view_lists); // true or false

		wp_update_user([
			'ID' => $subaccount_id,
			'first_name' => $name,
			'last_name' => $last_name,
		]);

		echo $subaccount_id;
		exit();
	}

	function b2bking_approve_order(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$order_id = sanitize_text_field($_POST['orderid']);
		$order = wc_get_order($order_id);
		$order->update_status( 'pending', esc_html__( 'Order approved and pending payment.', 'b2bking' ) );

		update_post_meta($order_id,'b2bking_order_approval', 'yes');

		echo 'success';
		exit();

	}

	function b2bking_reject_order(){

		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$order_id = sanitize_text_field($_POST['orderid']);
		$order = wc_get_order($order_id);
		$order->update_status( 'cancelled', esc_html__( 'Order cancelled.', 'b2bking' ) );

		echo 'success';
		exit();

		
	}
	function b2bking_search_by_title_only( $search, $wp_query ){
		global $wpdb;
	    if(empty($search)) {
	        return $search; // skip processing - no search term in query
	    }
	    $q = $wp_query->query_vars;
	    $n = !empty($q['exact']) ? '' : '%';
	    $search =
	    $searchand = '';
	    foreach ((array)$q['search_terms'] as $term) {
	        $term = esc_sql($wpdb->esc_like($term));
	        $search .= "{$searchand}($wpdb->posts.post_title LIKE '{$n}{$term}{$n}')";
	        $searchand = ' AND ';
	    }
	    if (!empty($search)) {
	        $search = " AND ({$search}) ";
	        if (!is_user_logged_in())
	            $search .= " AND ($wpdb->posts.post_password = '') ";
	    }
	    return $search;
	}

	function b2bking_accountingsubtotals(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}


		$pricevalue = sanitize_text_field($_POST['pricesent']);
		echo wc_price(floatval($pricevalue));

		exit();
	}
	
	function b2bking_ajax_search(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}


		if (isset($_POST['searchby'])){
			$searchby = sanitize_text_field($_POST['searchby']);
		} else {
			$searchby = 'productname';
		}

		if (isset($_POST['theme'])){
			$theme = sanitize_text_field($_POST['theme']);
			if ($theme === 'indigo' || $theme === 'cream'){
				$searchby = 'both';
			}
		}

		$searched_term = sanitize_text_field($_POST['searchValue']);
		if (isset($_POST['searchType'])){
			$search_type = sanitize_text_field($_POST['searchType']);
			if ($search_type === 'purchaseListLoading'){
				$searched_term = substr($searched_term, 0, 13);
			}
		}

		if (function_exists('relevanssi_didyoumean')) {
            $suggestion = '';
            if (function_exists('relevanssi_premium_didyoumean')) {
                $suggestion = relevanssi_premium_generate_suggestion($searched_term);
            }
            if (empty($suggestion)) {
                $suggestion = relevanssi_simple_generate_suggestion($searched_term);
            }
            if (!empty($suggestion)) {
                $searched_term = $suggestion;
            }
        }

        if (empty($searched_term)){
        	$searched_term = '';
        }
        
		$idsalreadyform = array();
		if (isset($_POST['idsinform'])){
			$idsalreadyform = sanitize_text_field($_POST['idsinform']);
			$idsalreadyform = json_decode(stripslashes($idsalreadyform));
		}
		

		$search_each_variation = get_option('b2bking_search_each_variation_setting',1);
		$search_what = 'product';
		if (intval($search_each_variation) === 1){
			$search_what = array('product','product_variation');
		}

		// Get current user's data: group, id, login, etc
	    $currentuserid = get_current_user_id();
    	$account_type = get_user_meta($currentuserid,'b2bking_account_type', true);
    	if ($account_type === 'subaccount'){
    		// for all intents and purposes set current user as the subaccount parent
    		$parent_user_id = get_user_meta($currentuserid, 'b2bking_account_parent', true);
    		$currentuserid = $parent_user_id;
    	}
        $currentuser = get_user_by('id', $currentuserid);

		$currentuserlogin = $currentuser -> user_login;
		$currentusergroupidnr = b2bking()->get_user_group($currentuserid);

		// if user is B2C, set to B2C
		if (get_user_meta($currentuserid,'b2bking_b2buser', true) !== 'yes'){
			$currentusergroupidnr = 'b2c';
		}


		// if product visibility is set to all,
		if ((intval(get_option( 'b2bking_all_products_visible_all_users_setting', 1 )) === 1)){
			if ($searchby === 'productname'){
				$queryAllparams = array(
				'no_found_rows' => true,
				'post_status' => 'publish',
			    'posts_per_page' => -1,
			    'post_type' => $search_what,
			    'fields' => 'ids',
			    'post__not_in'	=> $idsalreadyform,
			    's' => $searched_term
				);

				$queryAll = new WP_Query($queryAllparams);
				$allTheIDs = $queryAll->posts;

			} else if ($searchby === 'sku'){
				// search by SKU 
				$querySKUparams = array(
				'no_found_rows' => true,
				'post_status' => 'publish',
			    'posts_per_page' => -1,
			    'post_type' => $search_what,
			    'meta_query' => array(
		            'relation' => 'AND',
		            array(
		                'key' => apply_filters('b2bking_sku_search_key','_sku'),
		                'value' => $searched_term,
		                'compare' => 'LIKE',
		            ),
		        ),
			    'fields' => 'ids',
			    'post__not_in'	=> $idsalreadyform,
				);

				$querySKU = new WP_Query($querySKUparams);
				$allTheIDs = $querySKU->posts;
			} else if ($searchby === 'both'){

				// search product name
				$queryAllparams = array(
				'no_found_rows' => true,
				'post_status' => 'publish',
			    'posts_per_page' => -1,
			    'post_type' => $search_what,
			    'fields' => 'ids',
			    's' => $searched_term
				);

				$queryAll = new WP_Query($queryAllparams);
				$allTheIDs = $queryAll->posts;


				// search by SKU 
				$querySKUparams = array(
				'no_found_rows' => true,
				'post_status' => 'publish',
			    'posts_per_page' => -1,
			    'post_type' => $search_what,
			    'meta_query' => array(
		            'relation' => 'AND',
		            array(
		                'key' => apply_filters('b2bking_sku_search_key','_sku'),
		                'value' => $searched_term,
		                'compare' => 'LIKE',
		            ),
		        ),
			    'fields' => 'ids',
				);


				$querySKU = new WP_Query($querySKUparams);
				$allTheIDs2 = $querySKU->posts;


				$allTheIDs = array_merge($allTheIDs, $allTheIDs2);
			}

		} else {


			// if user is guest, set to 0
			if ($currentuser === false){
				$currentuserlogin = 0;
				$currentusergroupidnr = 0;
			}

			
			/*
			* 
			*	There are 2 separate queries that need to be made:
			* 	1. Query of all Categories visible to the USER AND all Categories visible to the USER'S GROUP 
			*	2. Query of all Products set to Manual visibility mode, visible to the user or the user's group 
			*
			*/

			// Build Visible Categories for the 1st Query
			$visiblecategories = array();
			$hiddencategories = array();

			// Get all categories
			$terms = get_terms( array( 
			    'taxonomy' => 'product_cat',
			    'hide_empty' => false
			) );
			foreach ($terms as $term){

				/* 
				* If category is visible to GROUP OR category is visible to USER
				* Push category into visible categories array
				*/

				// first check group
				if (intval(get_term_meta( $term->term_id, 'b2bking_group_'.$currentusergroupidnr, true )) === 1){
					array_push($visiblecategories, $term->term_id);
				// else check user
				} else {
					$userlistcommas = get_term_meta( $term->term_id, 'b2bking_category_users_textarea', true );
					$userarray = explode(',', $userlistcommas);
					foreach ($userarray as $user){
						if (trim($user) === $currentuserlogin){
							array_push($visiblecategories, $term->term_id);
							break;
						}
					}
					// reached this point, therefore category is hidden
					array_push($hiddencategories, $term->term_id);
				}
			}

			$product_category_visibility_array = array(
			           'taxonomy' => 'product_cat',
			           'field' => 'term_id',
			           'terms' => $visiblecategories, 
			           'operator' => 'IN'
			);


			// if user has enabled "hidden has priority", override setting
			if (intval(get_option( 'b2bking_hidden_has_priority_setting', 0 )) === 1){
				$product_category_visibility_array = array(
				           'taxonomy' => 'product_cat',
				           'field' => 'term_id',
				           'terms' => $hiddencategories, 
				           'operator' => 'NOT IN'
				);
			}

			/* Get all items that do not have manual visibility set up */
			// get all products ids
			if (intval(get_option( 'b2bking_product_visibility_cache_setting', 0 )) === 1){

				// WPML INTEGRATION

				if (!defined('ICL_LANGUAGE_NAME_EN')){
					$items_not_manual_visibility_array = get_transient('b2bking_not_manual_visibility_array');

					if (intval($search_each_variation) === 1){
						$items_not_manual_variations_visibility_array = get_transient('b2bking_not_manual_variations_visibility_array');
					}
				} else {
					$items_not_manual_visibility_array = get_transient('b2bking_not_manual_visibility_array'.ICL_LANGUAGE_NAME_EN);

					if (intval($search_each_variation) === 1){
						$items_not_manual_variations_visibility_array = get_transient('b2bking_not_manual_variations_visibility_array'.ICL_LANGUAGE_NAME_EN);
					}
				}
				
			} else {
				$items_not_manual_visibility_array = false;

				if (intval($search_each_variation) === 1){
					$items_not_manual_variations_visibility_array = false;
				}
			}

			
			
			if (!$items_not_manual_visibility_array){
				$all_prods = new WP_Query(array(
			        'posts_per_page' => -1,
			        'post_type' => 'product',
			        'post_status' => 'publish',
			        'fields' => 'ids'));

				$all_prod_ids = $all_prods->posts;

				// get all products with manual visibility ids
				$all_prods_manual = new WP_Query(array(
			        'posts_per_page' => -1,
			        'post_type' => 'product',
			        'post_status' => 'publish',
			        'fields' => 'ids',
    		        'meta_query'=> array(
                            'relation' => 'AND',
                            array(
                                'key' => 'b2bking_product_visibility_override',
                                'value' => 'manual',
                            )
                        )));
				$all_prod_manual_ids = $all_prods_manual->posts;
				// get the difference
				$items_not_manual_visibility_array = array_diff($all_prod_ids,$all_prod_manual_ids);

				if (!defined('ICL_LANGUAGE_NAME_EN')){
					set_transient('b2bking_not_manual_visibility_array', $items_not_manual_visibility_array);
				} else {
					set_transient('b2bking_not_manual_visibility_array'.ICL_LANGUAGE_NAME_EN, $items_not_manual_visibility_array);
				}
			}

			if (intval($search_each_variation) === 1){

				if (!$items_not_manual_variations_visibility_array){

					if (empty($items_not_manual_visibility_array)){
						$items_not_manual_visibility_array = array('invalid');
					}

					$all_variations_not_manual = new WP_Query(array(
				        'posts_per_page' => -1,
				        'post_type' => 'product_variation',
				        'post_status' => 'publish',
				        'fields' => 'ids',
	    		        'post_parent__in' => $items_not_manual_visibility_array
	    		    ));

					$items_not_manual_variations_visibility_array = $all_variations_not_manual->posts;

					if (!defined('ICL_LANGUAGE_NAME_EN')){
						set_transient('b2bking_not_manual_variations_visibility_array', $items_not_manual_variations_visibility_array);
					} else {
						set_transient('b2bking_not_manual_variations_visibility_array'.ICL_LANGUAGE_NAME_EN, $items_not_manual_variations_visibility_array);
					}
				}
			}

			$searcharrayitems = array_diff($items_not_manual_visibility_array, $idsalreadyform);

			if (empty($searcharrayitems)){
				$searcharrayitems = array('invalid');
			}

			// Build first query
		    $queryAparams = array(
		    	'no_found_rows' => true,
		    	'post_status' => 'publish',
		        'posts_per_page' => -1,
		        'post_type' => 'product',
		        'fields' => 'ids',
		        's' => $searched_term,
		        'tax_query' => array(
		        	$product_category_visibility_array
		        ),
		        'post__in' => $searcharrayitems,
		    );

		    if (intval($search_each_variation) === 1){

		    	// if searching individual variations, things like "Tax query" for categories or meta query search does not work
		    	// because individual variations do not have taxonomy or visibiliy meta

		    	// from the above query, we need to adjust for tax query and for post__in
		    	$tax_query_prod_ids = get_transient('b2bking_search_tax_query_prod_ids');
		    	if (!$tax_query_prod_ids){
	    			$tax_query_prod_ids = new WP_Query(array(
	    		        'posts_per_page' => -1,
	    		        'post_type' => 'product',
	    		        'post_status' => 'publish',
	    		        'tax_query' => array($product_category_visibility_array),
	    		        'fields' => 'ids'));
	    			set_transient('b2bking_search_tax_query_prod_ids', $tax_query_prod_ids->posts);
	    			$tax_query_prod_ids = $tax_query_prod_ids->posts;
		    	}

		    	$searcharrayitems2 = array_diff($items_not_manual_variations_visibility_array, $idsalreadyform);
		    	if (empty($searcharrayitems2)){
		    		$searcharrayitems2 = array('invalid');
		    	}

		    	$queryAvariationsparams = array(
		            'posts_per_page' => -1,
		            'post_type' => 'product_variation',
		            'post_status' => 'publish',
		            'fields' => 'ids',
		            's' => $searched_term,
		            'post__in' => $searcharrayitems2,
		            'post_parent__in' => $tax_query_prod_ids,
		        );

		    }

		    // Build 2nd query: all manual visibility products with USER OR USER GROUP visibility
		    $queryBparams = array(
		    	'no_found_rows' => true,
		    	'post_status' => 'publish',
		        'posts_per_page' => -1,
		        'post_type' => 'product',
		        'fields' => 'ids',
		        'post__not_in'	=> $idsalreadyform,
		        's' => $searched_term,
		        'meta_query'=> array(
	                    'relation' => 'AND',
	                    array(
	                        'relation' => 'OR',
	                        array(
	                            'key' => 'b2bking_group_'.$currentusergroupidnr,
	                            'value' => '1'
	                        ),
	                        array(
	                            'key' => 'b2bking_user_'.$currentuserlogin,
	                            'value' => '1'
	                        )
	                    ),
	                    array(
	                        'key' => 'b2bking_product_visibility_override',
	                        'value' => 'manual',
	                    ),
	                ));

		    if (intval($search_each_variation) === 1){

		    	// if searching individual variations, things like "Tax query" for categories or meta query search does not work
		    	// because individual variations do not have taxonomy or visibiliy meta

		    	// from the above query, we need to adjust for tax query and for post__in
		    	$manual_visible_ids = get_transient('b2bking_search_manual_visibility_visible_ids');
		    	if (!$manual_visible_ids){
	    			$manual_visible_ids = new WP_Query(array(
    		        	'no_found_rows' => true,
    		        	'post_status' => 'publish',
    		            'posts_per_page' => -1,
    		            'post_type' => 'product',
    		            'fields' => 'ids',
	    		        'meta_query'=> array(
		                    'relation' => 'AND',
		                    array(
		                        'relation' => 'OR',
		                        array(
		                            'key' => 'b2bking_group_'.$currentusergroupidnr,
		                            'value' => '1'
		                        ),
		                        array(
		                            'key' => 'b2bking_user_'.$currentuserlogin,
		                            'value' => '1'
		                        )
		                    ),
		                    array(
		                        'key' => 'b2bking_product_visibility_override',
		                        'value' => 'manual',
		                    ),
	                )));
	    			set_transient('b2bking_search_manual_visibility_visible_ids', $manual_visible_ids->posts);
	    			$manual_visible_ids = $manual_visible_ids->posts;
	    			if (empty($manual_visible_ids)){
	    				$manual_visible_ids = array('invalid');
	    			}
		    	}

		    	$queryBvariationsparams = array(
		            'posts_per_page' => -1,
		            'post_type' => 'product_variation',
		            'post_status' => 'publish',
		            'fields' => 'ids',
		            's' => $searched_term,
		            'post__not_in'	=> $idsalreadyform,
		            'post_parent__in' => $manual_visible_ids,
		        );

		    }

		    $searcharrayitems3 = array_diff($items_not_manual_visibility_array, $idsalreadyform);
		    if (empty($searcharrayitems3)){
		    	$searcharrayitems3 = array('invalid');
		    }
    	
    		// Build Queries A and B with SKU
			// Build first query
		    $queryASKUparams = array(
		    	'no_found_rows' => true,
		    	'post_status' => 'publish',
		        'posts_per_page' => -1,
		        'post_type' => 'product',
		        'fields' => 'ids',
		        'tax_query' => array(
		        	$product_category_visibility_array
		        ),
		        'meta_query' => array(
					'relation' => 'AND',
					array(
					    'key' => apply_filters('b2bking_sku_search_key','_sku'),
					    'value' => $searched_term,
					    'compare' => 'LIKE',
					),
		        ),
		        'post__in' => $searcharrayitems3,
		    );

		    if (intval($search_each_variation) === 1){

		    	// if searching individual variations, things like "Tax query" for categories or meta query search does not work
		    	// because individual variations do not have taxonomy or visibiliy meta

		    	// from the above query, we need to adjust for tax query and for post__in
		    	$tax_query_prod_ids = get_transient('b2bking_search_tax_query_prod_ids');
		    	if (!$tax_query_prod_ids){
	    			$tax_query_prod_ids = new WP_Query(array(
	    		        'posts_per_page' => -1,
	    		        'post_type' => 'product',
	    		        'post_status' => 'publish',
	    		        'tax_query' => array( $product_category_visibility_array ),
	    		        'fields' => 'ids'));
	    			set_transient('b2bking_search_tax_query_prod_ids', $tax_query_prod_ids->posts);
	    			$tax_query_prod_ids = $tax_query_prod_ids->posts;
		    	}

		    	$searcharrayitems4 = array_diff($items_not_manual_variations_visibility_array, $idsalreadyform);
		    	if (empty($searcharrayitems4)){
		    		$searcharrayitems4 = array('invalid');
		    	}

		    	$queryASKUvariationsparams = array(
		            'posts_per_page' => -1,
		            'post_type' => 'product_variation',
		            'post_status' => 'publish',
		            'fields' => 'ids',
                    'meta_query' => array(
            			'relation' => 'AND',
            			array(
            			    'key' => apply_filters('b2bking_sku_search_key','_sku'),
            			    'value' => $searched_term,
            			    'compare' => 'LIKE',
            			),
                    ),
		            'post__in' => $searcharrayitems4,
		            'post_parent__in' => $tax_query_prod_ids,
		        );

		    }


		    $queryBSKUparams = array(
		    	'no_found_rows' => true,
		    	'post_status' => 'publish',
		        'posts_per_page' => -1,
		        'post_type' => 'product',
		        'fields' => 'ids',
		        'post__not_in'	=> $idsalreadyform,
		        'meta_query'=> array(
	                    'relation' => 'AND',
	                    array(
	                        'relation' => 'OR',
	                        array(
	                            'key' => 'b2bking_group_'.$currentusergroupidnr,
	                            'value' => '1'
	                        ),
	                        array(
	                            'key' => 'b2bking_user_'.$currentuserlogin,
	                            'value' => '1'
	                        )
	                    ),
	                    array(
	                        'key' => 'b2bking_product_visibility_override',
	                        'value' => 'manual',
	                    ),
                    	array(
                    	    'key' => apply_filters('b2bking_sku_search_key','_sku'),
                    	    'value' => $searched_term,
                    	    'compare' => 'LIKE',
                    	),
	                ));
		    if (intval($search_each_variation) === 1){

		    	// if searching individual variations, things like "Tax query" for categories or meta query search does not work
		    	// because individual variations do not have taxonomy or visibiliy meta

		    	// from the above query, we need to adjust for tax query and for post__in
		    	$manual_visible_ids = get_transient('b2bking_search_manual_visibility_visible_ids');
		    	if (!$manual_visible_ids){
	    			$manual_visible_ids = new WP_Query(array(
    		        	'no_found_rows' => true,
    		        	'post_status' => 'publish',
    		            'posts_per_page' => -1,
    		            'post_type' => 'product',
    		            'fields' => 'ids',
	    		        'meta_query'=> array(
		                    'relation' => 'AND',
		                    array(
		                        'relation' => 'OR',
		                        array(
		                            'key' => 'b2bking_group_'.$currentusergroupidnr,
		                            'value' => '1'
		                        ),
		                        array(
		                            'key' => 'b2bking_user_'.$currentuserlogin,
		                            'value' => '1'
		                        )
		                    ),
		                    array(
		                        'key' => 'b2bking_product_visibility_override',
		                        'value' => 'manual',
		                    ),
	                )));
	    			set_transient('b2bking_search_manual_visibility_visible_ids', $manual_visible_ids->posts);
	    			$manual_visible_ids = $manual_visible_ids->posts;

	    			if (empty($manual_visible_ids)){
	    				$manual_visible_ids = array('invalid');
	    			}
		    	}

		    	$queryBSKUvariationsparams = array(
		            'posts_per_page' => -1,
		            'post_type' => 'product_variation',
		            'post_status' => 'publish',
		            'fields' => 'ids',
                    'meta_query' => array(
            			'relation' => 'AND',
            			array(
            			    'key' => apply_filters('b2bking_sku_search_key','_sku'),
            			    'value' => $searched_term,
            			    'compare' => 'LIKE',
            			),
                    ),
		            'post__not_in'	=> $idsalreadyform,
		            'post_parent__in' => $manual_visible_ids,
		        );

		    }
	   		
	   		if ($searchby === 'productname'){
	   			
		   		$queryA = new WP_Query($queryAparams);
			    $queryB = new WP_Query($queryBparams);

			    if (intval($search_each_variation) === 1){
			    	$queryAvariations = new WP_Query($queryAvariationsparams);
			    	$queryBvariations = new WP_Query($queryBvariationsparams);
			    }

	   		} elseif ($searchby === 'sku'){
	   			$queryA = new WP_Query($queryASKUparams);
		    	$queryB = new WP_Query($queryBSKUparams); 

		    	if (intval($search_each_variation) === 1){
		    		$queryAvariations = new WP_Query($queryASKUvariationsparams);
		    		$queryBvariations = new WP_Query($queryBSKUvariationsparams);
		    	}
	   		} elseif ($searchby === 'both'){
	   			$queryA = new WP_Query($queryAparams);
			    $queryB = new WP_Query($queryBparams);
			    $queryASKU = new WP_Query($queryASKUparams);
		    	$queryBSKU = new WP_Query($queryBSKUparams); 
			    if (intval($search_each_variation) === 1){
			    	$queryAvariations = new WP_Query($queryAvariationsparams);
			    	$queryBvariations = new WP_Query($queryBvariationsparams);
			    	$queryASKUvariations = new WP_Query($queryASKUvariationsparams);
			    	$queryBSKUvariations = new WP_Query($queryBSKUvariationsparams);
			    }
	   		}

	   		if ($searchby === 'both'){
	   			if (intval($search_each_variation) !== 1){
	   				$allTheIDs = array_merge($queryA->posts,$queryB->posts,$queryASKU->posts,$queryBSKU->posts);
	   			} else {
	   				$allTheIDs = array_merge($queryA->posts,$queryB->posts,$queryASKU->posts,$queryBSKU->posts, $queryAvariations->posts, $queryBvariations->posts, $queryASKUvariations->posts, $queryBSKUvariations->posts);
	   			}
	   		} else {
	   			if (intval($search_each_variation) !== 1){
	   				$allTheIDs = array_merge($queryA->posts,$queryB->posts);
	   			} else {
	   				$allTheIDs = array_merge($queryA->posts,$queryB->posts, $queryAvariations->posts, $queryBvariations->posts );
	   			}
	   		}
			
			
		}

	    
	    $results = array();
	    $i = apply_filters('b2bking_search_results_number_order_form', 10); // show maximum 8 search results

	    if ($theme === 'indigo'){
	    	// show all products by default, not just 10. Set it to 100
	    	$i = apply_filters('b2bking_search_results_number_order_form_indigo', 100);
	    }

	    if ($theme === 'cream'){
	    	// show all products by default, not just 10. Set it to 100
	    	$i = apply_filters('b2bking_search_results_number_order_form_cream', 100);
	    }


	    $exclude = sanitize_text_field($_POST['exclude']);
	    $exclude_ids = explode(',', $exclude);
	    $category = sanitize_text_field($_POST['category']);
	    $sortby = sanitize_text_field($_POST['sortby']);

	    
	    foreach ($allTheIDs as $product_id){
	    	if($i > 0){

			    // Additional limits in shortcode arguments
				if (isset($_POST['exclude'])){
					if (in_array($product_id, $exclude_ids)){
						// go to the next item
					    continue;
					}
				}

				if (isset($_POST['category'])){
					if ($category !== 'all' && intval($category)!==0){
						// 1 or more categories
						$categories = explode(',', $category);
						if (count($categories) ===1){
		    				if(!b2bking()->b2bking_has_category(intval($category), 'product_cat', $product_id)){
		    					continue;
		    				}
						} else if (count($categories)>1){
							$has_category = 0;
							foreach ($categories as $categoryitem){
								if(b2bking()->b2bking_has_category(intval($categoryitem), 'product_cat', $product_id)){
									$has_category = 1;
									break;
								}
							}
							if ($has_category === 0){
								continue;
							}
						}
		    			
					}
				}
				// Additional limits end

	    		$product = wc_get_product( $product_id );
	    		if ($product->is_purchasable() && $product->is_in_stock() ){
	    			if ($product->is_type('variable')){
	    				$children_ids = $product->get_children();
	    				foreach ($children_ids as $variation_id){
	    					$productvariation = new WC_Product_Variation($variation_id);
	    					//make sure variation does not have empty/unset attributes
	    					$attributes = $productvariation->get_attributes();
	    					if ($productvariation->is_in_stock() && !in_array('',$attributes) && $productvariation->is_purchasable()){
		    					if( $productvariation->is_on_sale() ) {
		    						$product_price = $productvariation->get_sale_price();
		    					} else {
		    						$product_price = $productvariation->get_price();
		    					}
		    					$product_price = round(floatval(b2bking()->b2bking_wc_get_price_to_display( $productvariation, array( 'price' => $product_price))),2);
		    					$product_title = $productvariation->get_formatted_name();

		    					$product_title = apply_filters('b2bking_product_title_bulk_order', $product_title, $variation_id);

		    					$results[$variation_id] = $product_title;
		    					$results[$variation_id.'B2BKINGPRICE'] = $product_price;

		    					// get price tiers
		    					$tieredpricing = get_post_meta($variation_id,'b2bking_product_pricetiers_group_'.$currentusergroupidnr, true);
		    					if (empty($tieredpricing)){
		    						$tieredpricing = get_post_meta($variation_id,'b2bking_product_pricetiers_group_b2c', true);
		    					}
		    					if (empty($tieredpricing)){
		    						$tieredpricing = 0;
		    					}

		    					$results[$variation_id.'B2BTIERPRICE'] = $tieredpricing;

		    					// get stock
		    					$stockqty = $productvariation->get_stock_quantity();
		    					
		    					if ( ! $productvariation->get_manage_stock() ){
		    						$stockqty = 999999999;
		    					} else {
		    						// if backorders, same 
		    						if ('yes' === $productvariation->get_backorders() || 'notify' === $productvariation->get_backorders()){
		    							$stockqty = 999999999;
		    						}
		    					}

		    					$results[$variation_id.'B2BKINGSTOCK'] = $stockqty;

		    					if (intval(get_option( 'b2bking_show_images_bulk_order_form_setting', 1 )) === 1){
		    						// get image
		    						$product_image = wp_get_attachment_image_src( get_post_thumbnail_id( $variation_id ) );
		    						if ( false === $product_image ) {
		    							$product_image = 'no';
		    							// try to find parent image
		    							$parent_image = wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ) );
		    							if ( false !== $parent_image ) {
		    								$product_image = $parent_image[0];
		    							}
		    						} else {
		    							$product_image = $product_image[0];
		    						}

		    						$results[ $variation_id . 'B2BKINGIMAGE' ] = $product_image;
		    					}
		    					
		    				}
	    				}

	    			} else {
	    				$stop = 'no';
	    				if (intval($search_each_variation) === 1){
		    				if (is_a($product,'WC_Product_Variation')){
		    					$attributes = $product->get_attributes();
		    					if (in_array('',$attributes)){
		    						$stop = 'yes';
		    					}
		    				}
		    			}
	    				if ($stop === 'no'){
		    				if( $product->is_on_sale() ) {
		    					$product_price = $product->get_sale_price();
		    				} else {
		    				   $product_price = $product->get_price();	
		    				}
				    		
				    		$product_price = round(floatval(b2bking()->b2bking_wc_get_price_to_display( $product, array( 'price' => $product_price))),2);
				    		$product_title = $product->get_formatted_name();

				    		$product_title = apply_filters('b2bking_product_title_bulk_order', $product_title, $product_id);
				    		
				    		$results[$product_id] = $product_title;
				    		$results[$product_id.'B2BKINGPRICE'] = $product_price;

				    		// get price tiers
				    		$tieredpricing = get_post_meta($product_id,'b2bking_product_pricetiers_group_'.$currentusergroupidnr, true);
				    		if (empty($tieredpricing)){
				    			$tieredpricing = get_post_meta($product_id,'b2bking_product_pricetiers_group_b2c', true);
				    		}
				    		if (empty($tieredpricing)){
				    			$tieredpricing = 0;
				    		}
				    		$results[$product_id.'B2BTIERPRICE'] = $tieredpricing;

				    		// get stock
				    		$stockqty = $product->get_stock_quantity();

				    		if ( ! $product->get_manage_stock() ){
				    			$stockqty = 999999999;
				    		} else {
	    						// if backorders, same 
	    						if ('yes' === $product->get_backorders() || 'notify' === $product->get_backorders()){
	    							$stockqty = 999999999;
	    						}
	    					}

				    		$results[$product_id.'B2BKINGSTOCK'] = $stockqty;

				    		if (intval(get_option( 'b2bking_show_images_bulk_order_form_setting', 1 )) === 1){

					    		// get image
					    		$product_image = wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ) );
					    		if ( false === $product_image ) {
					    			$possible_parent_id = wp_get_post_parent_id($product_id);
					    			if ($possible_parent_id !== 0){
					    				$product_image = wp_get_attachment_image_src( get_post_thumbnail_id( $possible_parent_id ) )[0];
					    			} else {
					    				$product_image = 'no';
					    			}

					    		} else {
					    			$product_image = $product_image[0];
					    		}

					    		$results[ $product_id . 'B2BKINGIMAGE' ] = $product_image;
					    	}

				    	}
		    		}
		    		$i--;
		    	}
	    	}
	    }


	    if ($theme === 'indigo' || $theme === 'cream'){

		    $product_ids = array();
		    foreach ($results as $index => $result){
		    	$resultid = explode('B2B', $index)[0];
		    	array_push($product_ids, $resultid);
		    }
		    $product_ids = array_filter(array_unique($product_ids));

		    // let's sort products
		    if ($sortby === 'atoz' || $sortby === 'ztoa'){
		    	// build array with product name + ID, and sort by first column
		    	$sortarray = array();
		    	foreach ($product_ids as $prodid){
		    		$sortarray[$prodid] = ucfirst($results[$prodid]);
		    	}

		    	if ($sortby === 'atoz'){
		    		asort($sortarray);
		    	}

		    	if ($sortby === 'ztoa'){
		    		arsort($sortarray);
		    	}

		    }

		    if ($sortby === 'bestselling'){
		    	$sortarray = array();
		    	foreach ($product_ids as $prodid){
		    		$numberofsales = intval(get_post_meta($prodid,'total_sales', true));
		    		$sortarray[$prodid] = $numberofsales;
		    	}
		    	arsort($sortarray);
		    }

		    // rebuild results array from sort results
		    $product_ids = array();
		    foreach ($sortarray as $prodid => $title){
		    	array_push($product_ids, $prodid);
		    }

		}

	    if (empty($results)){
	    	$results = 1234;
	    	echo $results;
	    } else {
	    	if ($theme !== 'indigo' && $theme !== 'cream'){
	    		echo json_encode($results);
	    	} else if ($theme === 'indigo'){
	    		// generate HTML

	    		ob_start();

	    		$product_ids = apply_filters('b2bking_order_form_ids_before_display', $product_ids);

            	foreach ($product_ids as $product_id){

            		$productobj = wc_get_product($product_id);

            		if ($productobj !== false){
	     				// Get current user's data: group, id, login, etc
	    			    $currentuserid = get_current_user_id();
	    		    	$account_type = get_user_meta($currentuserid,'b2bking_account_type', true);
	    		    	if ($account_type === 'subaccount'){
	    		    		// for all intents and purposes set current user as the subaccount parent
	    		    		$parent_user_id = get_user_meta($currentuserid, 'b2bking_account_parent', true);
	    		    		$currentuserid = $parent_user_id;
	    		    	}
	    				
	            		?>
	            		<div class="b2bking_bulkorder_form_container_content_line b2bking_bulkorder_form_container_content_line_indigo">
	            			<div class="b2bking_bulkorder_form_container_content_line_product b2bking_selected_product_id_<?php echo esc_attr($product_id); ?>"></div>

	            			<div class="b2bking_bulkorder_indigo_product_container">
	            				<?php
	            				if (intval(get_option( 'b2bking_show_images_bulk_order_form_setting', 1 )) === 1){
	            					?>
			            			<img class="b2bking_bulkorder_indigo_image" src="<?php
			            			$url = wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ) );
			            			if (empty($url)){
			            				$possible_parent_id = wp_get_post_parent_id($product_id);
			            				if ($possible_parent_id !== 0){
			            					$url = wp_get_attachment_image_src( get_post_thumbnail_id( $possible_parent_id ) );
			            					if (!empty($url)){
			            						echo esc_attr($url[0]);
			            					} else {
			            						echo wc_placeholder_img_src();
			            					}
			            				} else {
			            					echo wc_placeholder_img_src();
			            				}
			            			} else {
			            				echo esc_attr($url[0]);
			            			}
			            			?>">
			            			<?php
			            		}
			            		?>

		            			<a class="b2bking_bulkorder_indigo_name" href="<?php echo esc_url($productobj->get_permalink());?>"><div class="b2bking_bulkorder_indigo_name"><?php echo apply_filters('b2bking_bulkorder_indigo_search_name_display',esc_html(strip_tags($productobj->get_formatted_name())), $productobj);?><?php
		            				do_action('b2bking_bulkorder_cream_indigo_after_name', $product_id);
		            			?></div></a>
		            		</div>

		            		<?php
							// get min max step multiple

							$defaults = array(
								'max_value'    => apply_filters( 'woocommerce_quantity_input_max', $productobj->get_max_purchase_quantity(), $productobj ),
								'min_value'    => apply_filters( 'woocommerce_quantity_input_min', $productobj->get_min_purchase_quantity(), $productobj ),
								'step'         => apply_filters( 'woocommerce_quantity_input_step', 1, $productobj ),
							);
							$args = array();
	            			$args = apply_filters( 'woocommerce_quantity_input_args', wp_parse_args( $args, $defaults ), $productobj );

	            			$min = 1;
	            			if (isset($args['min_value'])){
	            				if (!empty($args['min_value'])){
	            					$min = $args['min_value'];
	            				}
	            			}
	            			// sanity
	            			if ($min < 1){
	            				$min = 1;
	            			}
	            			
	            			$max = 999999;
	            			if (isset($args['max_value'])){
	            				if (!empty($args['max_value'])){
	            					$max = $args['max_value'];
	            				}
	            			}
	            			if ($max < 0){
	            				$max = 999999;
	            			}

	            			$step = 1;
	            			if (isset($args['step'])){
	            				if (!empty($args['step'])){
	            					$step = $args['step'];
	            				}
	            			}
	            			if ($step < 1){
	            				$step = 1;
	            			}

	            			$value = $min;

	            			// if this is a variation, and the parent actually has a minimum that's higher than this minimum, set the starting value (not the min) to that.
	            			if ($min === 1){
		            			$possible_parent_id = wp_get_post_parent_id($product_id);
		            			if ($possible_parent_id !== 0){
		            				$parentobj = wc_get_product($possible_parent_id);

		            				$defaultsparent = array(
										'min_value'    => apply_filters( 'woocommerce_quantity_input_min', 1, $parentobj ),
										'step'         => apply_filters( 'woocommerce_quantity_input_step', 1, $parentobj ),
									);
									$argsparent = array();
			            			$argsparent = apply_filters( 'woocommerce_quantity_input_args', wp_parse_args( $argsparent, $defaultsparent ), $parentobj );
			            			if (isset($argsparent['min_value'])){
			            				if (!empty($argsparent['min_value'])){
			            					$value = $argsparent['min_value'];
			            				}
			            			}

		            			}
		            		}

	            			if ($step>$value){
	            				$value = $step;
	            			}
		            		?>

	            			<input type="number" min="<?php echo esc_attr($min);?>" max="<?php echo esc_attr($max);?>" class="b2bking_bulkorder_form_container_content_line_qty b2bking_bulkorder_form_container_content_line_qty_indigo" step="<?php echo esc_attr($step);?>" value="<?php echo esc_attr($value);?>"><?php 
	            			do_action('b2bking_bulkorder_column_header_mid_content'); ?><div class="b2bking_bulkorder_form_container_content_line_subtotal b2bking_bulkorder_form_container_content_line_subtotal_indigo"><?php 

	            		if ($this->dynamic_replace_prices_with_quotes() === 'yes' || (get_option('b2bking_guest_access_restriction_setting', 'hide_prices') === 'replace_prices_quote') && (!is_user_logged_in() || (intval(get_option( 'b2bking_multisite_separate_b2bb2c_setting', 0 )) === 1 && get_user_meta($currentuserid, 'b2bking_b2buser', true) !== 'yes'))){
	            			esc_html_e('Quote','b2bking');
	            		} else {
	            			if (intval(get_option( 'b2bking_show_accounting_subtotals_setting', 0 )) === 1){
	            				echo wc_price($results[$product_id.'B2BKINGPRICE']);
	            			} else {
	            				echo get_woocommerce_currency_symbol().$results[$product_id.'B2BKINGPRICE']; 
	            			}
	            		}

	            		?>
	            		</div>
	            		<div class="b2bking_bulkorder_form_container_content_line_cart_indigo"><button class="b2bking_bulkorder_indigo_add"><?php esc_html_e('Add','b2bking');?></button></div><?php do_action('b2bking_bulkorder_column_header_end_content'); ?></div>
	            		<?php
	            	}
            	} 

            	?>
            	<div class="b2bking_bulkorder_form_container_bottom b2bking_bulkorder_form_container_bottom_indigo <?php if (count($product_ids) > 10) echo 'b2bking_bulkorder_form_container_bottom_indigo_large';?>">
            		<span class="b2bking_bulkorder_back_top">
            		<?php if (count($product_ids) > 10) { esc_html_e('You’ve seen all the products. Back to top','b2bking'); } ?>
            		</span>
            	</div>
            	<?php
            	$content = ob_get_clean();
            	$results['HTML'] = $content;


	    		echo json_encode($results);

	    	} else if ($theme === 'cream'){
	    		

	    		ob_start();

	    		$product_ids = apply_filters('b2bking_order_form_ids_before_display', $product_ids);

            	foreach ($product_ids as $product_id){

            		$productobj = wc_get_product($product_id);

            		if ($productobj !== false){
	     				// Get current user's data: group, id, login, etc
	    			    $currentuserid = get_current_user_id();
	    		    	$account_type = get_user_meta($currentuserid,'b2bking_account_type', true);
	    		    	if ($account_type === 'subaccount'){
	    		    		// for all intents and purposes set current user as the subaccount parent
	    		    		$parent_user_id = get_user_meta($currentuserid, 'b2bking_account_parent', true);
	    		    		$currentuserid = $parent_user_id;
	    		    	}
	    				
	            		?>
	            		<div class="b2bking_bulkorder_form_container_content_line b2bking_bulkorder_form_container_content_line_indigo b2bking_bulkorder_form_container_content_line_cream">
	            			<div class="b2bking_bulkorder_form_container_content_line_product b2bking_selected_product_id_<?php echo esc_attr($product_id); ?>"></div>

	            			<div class="b2bking_bulkorder_indigo_product_container b2bking_bulkorder_cream_product_container">
	            				<?php
	            				if (intval(get_option( 'b2bking_show_images_bulk_order_form_setting', 1 )) === 1){
	            					?>
			            			<img class="b2bking_bulkorder_indigo_image b2bking_bulkorder_cream_image" src="<?php

			            			$url = wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ) );
			            			if (empty($url)){
			            				$possible_parent_id = wp_get_post_parent_id($product_id);
			            				if ($possible_parent_id !== 0){
			            					$url = wp_get_attachment_image_src( get_post_thumbnail_id( $possible_parent_id ) );
			            					if (!empty($url)){
			            						echo esc_attr($url[0]);
			            					} else {
			            						echo wc_placeholder_img_src();
			            					}
			            				} else {
			            					echo wc_placeholder_img_src();
			            				}
			            			} else {
			            				echo esc_attr($url[0]);
			            			}
			            			?>">
			            			<?php
			            		}
			            		?>

		            			<a class="b2bking_bulkorder_indigo_name b2bking_bulkorder_cream_name" href="<?php echo esc_url($productobj->get_permalink());?>"><div class="b2bking_bulkorder_indigo_name b2bking_bulkorder_cream_name"><?php echo apply_filters('b2bking_bulkorder_cream_search_name_display',esc_html(strip_tags($productobj->get_formatted_name())), $productobj);?><?php 
		            				do_action('b2bking_bulkorder_cream_indigo_after_name', $product_id);
		            			?></div></a>
		            		</div>

		            		<?php
							// get min max step multiple

							$defaults = array(
								'max_value'    => apply_filters( 'woocommerce_quantity_input_max', $productobj->get_max_purchase_quantity(), $productobj ),
								'min_value'    => apply_filters( 'woocommerce_quantity_input_min', $productobj->get_min_purchase_quantity(), $productobj ),
								'step'         => apply_filters( 'woocommerce_quantity_input_step', 1, $productobj ),
							);
							$args = array();
	            			$args = apply_filters( 'woocommerce_quantity_input_args', wp_parse_args( $args, $defaults ), $productobj );

	            			$min = 1;
	            			if (isset($args['min_value'])){
	            				if (!empty($args['min_value'])){
	            					$min = $args['min_value'];
	            				}
	            			}
	            			// sanity
	            			if ($min < 1){
	            				$min = 1;
	            			}
	            			
	            			$max = 999999;
	            			if (isset($args['max_value'])){
	            				if (!empty($args['max_value'])){
	            					$max = $args['max_value'];
	            				}
	            			}
	            			if ($max < 0){
	            				$max = 999999;
	            			}

	            			$step = 1;
	            			if (isset($args['step'])){
	            				if (!empty($args['step'])){
	            					$step = $args['step'];
	            				}
	            			}
	            			if ($step < 1){
	            				$step = 1;
	            			}

	            			$value = $min;

	            			// if this is a variation, and the parent actually has a minimum that's higher than this minimum, set the starting value (not the min) to that.
	            			if ($min === 1){
		            			$possible_parent_id = wp_get_post_parent_id($product_id);
		            			if ($possible_parent_id !== 0){
		            				$parentobj = wc_get_product($possible_parent_id);

		            				$defaultsparent = array(
										'min_value'    => apply_filters( 'woocommerce_quantity_input_min', 1, $parentobj ),
										'step'         => apply_filters( 'woocommerce_quantity_input_step', 1, $parentobj ),
									);
									$argsparent = array();
			            			$argsparent = apply_filters( 'woocommerce_quantity_input_args', wp_parse_args( $argsparent, $defaultsparent ), $parentobj );
			            			if (isset($argsparent['min_value'])){
			            				if (!empty($argsparent['min_value'])){
			            					$value = $argsparent['min_value'];
			            				}
			            			}

		            			}
		            		}

	            			if ($step>$value){
	            				$value = $step;
	            			}
		            		?>

	            			<input type="number" min="<?php echo esc_attr($min);?>" max="<?php echo esc_attr($max);?>" class="b2bking_bulkorder_form_container_content_line_qty b2bking_bulkorder_form_container_content_line_qty_indigo b2bking_bulkorder_form_container_content_line_qty_cream" step="<?php echo esc_attr($step);?>" value="<?php echo esc_attr($value);?>"><?php 
	            			do_action('b2bking_bulkorder_column_header_mid_content'); ?><div class="b2bking_bulkorder_form_container_content_line_subtotal b2bking_bulkorder_form_container_content_line_subtotal_indigo b2bking_bulkorder_form_container_content_line_subtotal_cream"><?php 

	            		if ($this->dynamic_replace_prices_with_quotes() === 'yes' || (get_option('b2bking_guest_access_restriction_setting', 'hide_prices') === 'replace_prices_quote') && (!is_user_logged_in() || (intval(get_option( 'b2bking_multisite_separate_b2bb2c_setting', 0 )) === 1 && get_user_meta($currentuserid, 'b2bking_b2buser', true) !== 'yes'))){
	            			esc_html_e('Quote','b2bking');
	            		} else {
	            			if (intval(get_option( 'b2bking_show_accounting_subtotals_setting', 0 )) === 1){
	            				// price x quantity ($value)
	            				echo wc_price($results[$product_id.'B2BKINGPRICE']*$value);
	            			} else {
	            				echo get_woocommerce_currency_symbol().($results[$product_id.'B2BKINGPRICE']*$value); 
	            			}
	            		}

	            		?>
	            		</div>
	            		<div class="b2bking_bulkorder_form_container_content_line_cart_indigo b2bking_bulkorder_form_container_content_line_cart_cream"><button class="b2bking_bulkorder_indigo_add b2bking_bulkorder_cream_add"><?php echo apply_filters('b2bking_cream_order_form_add_cart_text', esc_html__('Add to cart','b2bking'));?></button></div><?php do_action('b2bking_bulkorder_column_header_end_content'); ?></div>
	            		<?php
	            	}
            	} 

            	?>
            	<div class="b2bking_bulkorder_form_container_bottom b2bking_bulkorder_form_container_bottom_indigo b2bking_bulkorder_form_container_bottom_cream <?php if (count($product_ids) > 10) echo 'b2bking_bulkorder_form_container_bottom_indigo_large b2bking_bulkorder_form_container_bottom_cream_large';?>">
            		<span class="b2bking_bulkorder_back_top">
            		<?php if (count($product_ids) > 10) { esc_html_e('You’ve seen all the products. Back to top','b2bking'); } ?>
            		</span>
            	</div>
            	<?php
            	$content = ob_get_clean();
            	$results['HTML'] = $content;


	    		echo json_encode($results);
	    	}
	    }

		
		exit();
	}

	function b2bking_ajax_get_price(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$product_id = sanitize_text_field($_POST['productid']);
		$product_price = wc_get_product( $product_id ) -> get_price();

		echo intval($product_price);
		exit();
	}

	function b2bking_bulkorder_add_cart(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$productstring = sanitize_text_field($_POST['productstring']);
		$products_array = explode('|', $productstring);
		foreach($products_array as $product){
			$product_id = explode(':', $product)[0];
			$product_qty = explode(':', $product)[1];

			$cart_item_data = apply_filters('b2bking_bulkorder_add_cart_item_data', array(), $product_id, $product_qty, $product);

			WC()->cart->add_to_cart( $product_id, $product_qty, 0, array(), $cart_item_data);
		}

		do_action('b2bking_bulkorder_add_cart', get_current_user_id());


		echo 'success';
		exit();
	}

	function b2bking_bulkorder_add_cart_item(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$product_id = sanitize_text_field($_POST['productid']);
		$product_qty = sanitize_text_field($_POST['productqty']);
		$product = wc_get_product($product_id);

		$cart_item_data = apply_filters('b2bking_bulkorder_add_cart_item_data', array(), $product_id, $product_qty, $product);

		WC()->cart->add_to_cart( $product_id, $product_qty, 0, array(), $cart_item_data);

		do_action('b2bking_bulkorder_add_cart', get_current_user_id());

		echo 'success';

		exit();
	}

	function b2bking_bulkorder_save_list(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$productstring = sanitize_text_field($_POST['productstring']);
		$title = sanitize_text_field($_POST['title']);
		$prices = sanitize_text_field($_POST['pricelist']);

		$purchase_list = array(
		    'post_title' => $title,
		    'post_status' => 'publish',
		    'post_type' => 'b2bking_list',
		    'post_author' => get_current_user_id(),
		);
		$purchase_list_id = wp_insert_post($purchase_list);
		update_post_meta($purchase_list_id, 'b2bking_purchase_list_details', $productstring);
		// save prices for later retrieval
		update_post_meta($purchase_list_id, 'b2bking_purchase_list_prices', $prices);

		do_action('b2bking_purchase_list_created', $purchase_list_id, get_current_user_id());


		echo $purchase_list_id;
		exit();
	}

	function b2bking_purchase_list_update(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$productstring = sanitize_text_field($_POST['productstring']);
		$list_id = sanitize_text_field($_POST['listid']);

		update_post_meta($list_id, 'b2bking_purchase_list_details', $productstring);

		do_action('b2bking_purchase_list_updated', $list_id, get_current_user_id());


		echo $list_id;
		exit();
	}

	function b2bking_purchase_list_delete(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$list_id = sanitize_text_field($_POST['listid']);
		wp_delete_post($list_id);

		echo 'success';
		exit();
	}

	function b2bking_save_cart_to_purchase_list(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$productstring = '';
		$items = WC()->cart->get_cart();
		foreach($items as $item => $values) { 
            $product_id = $values['data']->get_id(); 
            $product_qty = $values['quantity'];
            $productstring .= $product_id.':'.$product_qty.'|';
        }

        // if cart not empty, save as list
        if ($productstring !== ''){

			$title = sanitize_text_field($_POST['title']);
			$purchase_list = array(
			    'post_title' => $title,
			    'post_status' => 'publish',
			    'post_type' => 'b2bking_list',
			    'post_author' => get_current_user_id(),
			);
			$purchase_list_id = wp_insert_post($purchase_list);

        	update_post_meta($purchase_list_id, 'b2bking_purchase_list_details', $productstring);

        	do_action('b2bking_purchase_list_created', $purchase_list_id, get_current_user_id());
        }
        
		echo 'success';
		exit();
	}

	function b2bking_send_feedback(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$message = sanitize_text_field($_POST['message']);
		$email = sanitize_text_field($_POST['email']);

		wp_mail('contact@webwizards.dev', esc_html__('New feedback message','b2bking'), $message.' '.esc_html__('Message was sent by:','b2bking').$email);

		echo 'success';
		exit();

	}

	function b2bkingupdateuserdata(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$user_id = sanitize_text_field($_POST['userid']);
		$fields_string = sanitize_text_field($_POST['field_strings']);
		$fields_array = explode(',',$fields_string);
		foreach ($fields_array as $field_id){
			if ($field_id !== NULL && !empty($field_id)){

				// first check if field is VAT, then update user meta if field not empty
				$billing_connection = get_post_meta($field_id,'b2bking_custom_field_billing_connection', true);
				if ($billing_connection !== 'billing_vat'){
					// proceed normally,this is not VAT
					update_user_meta($user_id, 'b2bking_custom_field_'.$field_id, sanitize_text_field($_POST['field_'.$field_id]));
				} else {
					// check if VIES is enabled
					$vies_enabled = get_post_meta($field_id, 'b2bking_custom_field_VAT_VIES_validation', true);
					
					if (intval($vies_enabled) === 1){
						// run VIES check on the data
						$vatnumber = sanitize_text_field($_POST['field_'.$field_id]);
						$vatnumber = strtoupper(str_replace(array('.', ' '), '', $vatnumber));

						$client = new SoapClient("http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl");
						$country_code = substr($vatnumber, 0, 2); // take first 2 chars
						$vat_number = substr($vatnumber, 2); // remove first 2 chars

						$validation = new \stdClass();
						$validation->valid = false;
						
						// check vat
						try {
							$validation = $client->checkVat(array(
							  'countryCode' => $country_code,
							  'vatNumber' => $vat_number
							));

						} catch (Exception $e) {
							$error = $e->getMessage();
							$validation->valid=0;
						}

						$countries_list_eu = array('AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'EL', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE');
						if (!in_array($country_code, $countries_list_eu)){
							$validation->valid = 1;
						}

						if (intval($validation->valid) === 1){
							// update data

							update_user_meta($user_id, 'b2bking_custom_field_'.$field_id, $vatnumber);
							update_user_meta($user_id, 'b2bking_custom_field_'.$field_id.'bis', $vatnumber);
							// also set validated vat
							update_user_meta( $user_id, 'b2bking_user_vat_status', 'validated_vat');
						} else {
							echo 'vatfailed';
						}
					} else {
						update_user_meta($user_id, 'b2bking_custom_field_'.$field_id, sanitize_text_field($_POST['field_'.$field_id])); 
					}
				}
			}
		}

		echo 'success';
		exit();
	}

	function b2bking_dismiss_activate_woocommerce_admin_notice(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_notice_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		update_user_meta(get_current_user_id(), 'b2bking_dismiss_activate_woocommerce_notice', 1);

		echo 'success';
		exit();
	}

	function b2bking_b2c_special_group_save_settings(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		if (apply_filters('b2bking_use_zone_shipping_control', true)){
		
			$shipping_methods = array();

			$delivery_zones = WC_Shipping_Zones::get_zones();
	        foreach ($delivery_zones as $key => $the_zone) {
	            foreach ($the_zone['shipping_methods'] as $value) {
	                array_push($shipping_methods, $value);
	            }
	        }

			foreach ($shipping_methods as $shipping_method){
				if (isset($_POST['b2bking_b2c_users_shipping_method_'.$shipping_method->id.$shipping_method->instance_id])){
					$user_setting = sanitize_text_field($_POST['b2bking_b2c_users_shipping_method_'.$shipping_method->id.$shipping_method->instance_id]);
					if( intval($user_setting) === 1){
					    update_option('b2bking_b2c_users_shipping_method_'.$shipping_method->id.$shipping_method->instance_id, 1);
					} else if( intval($user_setting) === 0){
						update_option('b2bking_b2c_users_shipping_method_'.$shipping_method->id.$shipping_method->instance_id, 0);
					}
				}
			}

		} else {
			// older mechanism here for cases where needed

			// get all shipping methods
			$shipping_methods = WC()->shipping->get_shipping_methods();
			foreach ($shipping_methods as $shipping_method){
				$user_setting = sanitize_text_field($_POST['b2bking_b2c_users_shipping_method_'.$shipping_method->id]);
				if( intval($user_setting) === 1){
				    update_option('b2bking_b2c_users_shipping_method_'.$shipping_method->id, 1);
				} else if( intval($user_setting) === 0){
					update_option('b2bking_b2c_users_shipping_method_'.$shipping_method->id, 0);
				}
			}
		}

		$payment_methods = WC()->payment_gateways->payment_gateways();

		foreach ($payment_methods as $payment_method){
			if (isset($_POST['b2bking_b2c_users_payment_method_'.$payment_method->id])){
				$user_setting = sanitize_text_field($_POST['b2bking_b2c_users_payment_method_'.$payment_method->id]);
				if( intval($user_setting) === 1){
				    update_option('b2bking_b2c_users_payment_method_'.$payment_method->id, 1);
				} else if( intval($user_setting) === 0){
					update_option('b2bking_b2c_users_payment_method_'.$payment_method->id, 0);
				}
			}
		}

		echo 'success';
		exit();
	}

	function b2bking_logged_out_special_group_save_settings(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		if (apply_filters('b2bking_use_zone_shipping_control', true)){
			// get all shipping methods
			$shipping_methods = array();

			$delivery_zones = WC_Shipping_Zones::get_zones();
	        foreach ($delivery_zones as $key => $the_zone) {
	            foreach ($the_zone['shipping_methods'] as $value) {
	                array_push($shipping_methods, $value);
	            }
	        }
			foreach ($shipping_methods as $shipping_method){
				if (isset($_POST['b2bking_logged_out_users_shipping_method_'.$shipping_method->id.$shipping_method->instance_id])){
					$user_setting = sanitize_text_field($_POST['b2bking_logged_out_users_shipping_method_'.$shipping_method->id.$shipping_method->instance_id]);
					if( intval($user_setting) === 1){
					    update_option('b2bking_logged_out_users_shipping_method_'.$shipping_method->id.$shipping_method->instance_id, 1);
					} else if( intval($user_setting) === 0){
						update_option('b2bking_logged_out_users_shipping_method_'.$shipping_method->id.$shipping_method->instance_id, 0);
					}
				}
			}

		} else {
			// older mechanism here for cases where needed
			// get all shipping methods
			$shipping_methods = WC()->shipping->get_shipping_methods();
			foreach ($shipping_methods as $shipping_method){
				$user_setting = sanitize_text_field($_POST['b2bking_logged_out_users_shipping_method_'.$shipping_method->id]);
				if( intval($user_setting) === 1){
				    update_option('b2bking_logged_out_users_shipping_method_'.$shipping_method->id, 1);
				} else if( intval($user_setting) === 0){
					update_option('b2bking_logged_out_users_shipping_method_'.$shipping_method->id, 0);
				}
			}
		}

		$payment_methods = WC()->payment_gateways->payment_gateways();

		foreach ($payment_methods as $payment_method){
			if (isset($_POST['b2bking_logged_out_users_payment_method_'.$payment_method->id])){
				$user_setting = sanitize_text_field($_POST['b2bking_logged_out_users_payment_method_'.$payment_method->id]);
				if( intval($user_setting) === 1){
				    update_option('b2bking_logged_out_users_payment_method_'.$payment_method->id, 1);
				} else if( intval($user_setting) === 0){
					update_option('b2bking_logged_out_users_payment_method_'.$payment_method->id, 0);
				}
			}
		}

		echo 'success';
		exit();
	}

	function b2bkingdownloadpurchaselist(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}


		$listid = sanitize_text_field($_REQUEST['list']);

		$list_name = esc_html__('b2bking_purchase_list','b2bking');
		$list_name = apply_filters('b2bking_purchase_list_file_name', $list_name);

		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=".$list_name."_".$listid.".csv");
		header("Pragma: no-cache");
		header("Expires: 0");

		$output = fopen("php://output", "wb");
		// build header

		$headerrow = apply_filters('b2bking_list_download_columns_header',array(esc_html__('Name','b2bking'), esc_html__('SKU','b2bking'), esc_html__('Quantity','b2bking'), esc_html__('Price', 'b2bking')));

		fputcsv($output, $headerrow);


		// parse list and for each line write data
		$list_details = get_post_meta($listid,'b2bking_purchase_list_details', true);
		$list_items = explode('|', $list_details);
		$list_items = array_filter($list_items);

		foreach ($list_items as $list_item){
			
			$item = explode(':', $list_item);
			$product_id = $item[0];
			$product_qty = $item[1];
			$productobj = wc_get_product($product_id);

			$product_title = $productobj -> get_name();
			$product_sku = $productobj -> get_sku();
			if (empty($product_sku)){
				$product_sku = '-';
			}

			$price = $productobj->get_price();

			$csv_array = apply_filters('b2bking_list_download_columns_items', array($product_title, $product_sku, $product_qty, get_woocommerce_currency().$price), $list_item);


			fputcsv($output, $csv_array); 
		
		}


		fclose($output);
		exit();
	}

	function b2bkingbulksetvariationprices(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$groupid = sanitize_text_field($_POST['group_id']);
		$productid = sanitize_text_field($_POST['product_id']);
		$price = sanitize_text_field($_POST['price']);
		$regularsale = sanitize_text_field($_POST['regular_sale']);

		$product = wc_get_product($productid);
		$children = $product->get_children();
		foreach ($children as $variation_id){
			update_post_meta($variation_id,'b2bking_'.$regularsale.'_product_price_group_'.$groupid, $price);
		}
		exit();
	}

	function b2bkingdownloadpricelist(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		// build and download list
		global $wpdb;

		$tableprefix = $wpdb->prefix;
		$table_name = $tableprefix.'posts';

		$queryresult = $wpdb->get_results( 
			"
		    SELECT `id` FROM $table_name WHERE post_status = 'publish' AND (post_type = 'product' OR post_type = 'product_variation')
			"
		, ARRAY_N);

		// get all groups
		$groups = get_posts( array( 'post_type' => 'b2bking_group','post_status'=>'publish','numberposts' => -1) );

		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=b2bking_price_list.csv");
		header("Pragma: no-cache");
		header("Expires: 0");

		$output = fopen("php://output", "wb");
		// build header
		$headerrow = array("Product or Variation ID / SKU");
		// Regular and Sale Price and Tiered B2C:
		array_push($headerrow, esc_html__('Regular Price'));
		array_push($headerrow, esc_html__('Sale Price'));
		array_push($headerrow, esc_html__('Tiered Price (Qty:Price;)'));

		foreach ($groups as $group){
			array_push($headerrow, $group->ID.': '.$group->post_title.' '.esc_html__('Regular Price'));
			array_push($headerrow, $group->ID.': '.$group->post_title.' '.esc_html__('Sale Price'));
			array_push($headerrow, $group->ID.': '.$group->post_title.' '.esc_html__('Tiered Price'));
		}
		fputcsv($output, $headerrow);


		// build rows
		foreach ($queryresult as $key => $value){
			$id = intval($value[0]);
			$offer_id = intval(get_option('b2bking_offer_product_id_setting', 0));
			$credit_id = intval(get_option('b2bking_credit_product_id_setting', 0));

			if ($id !== 0 && $id !== 3225464 && $id !== $offer_id && $id !== $credit_id){  // deprecated offer nr
				$temparray = array();

				// set title
				$product_title = get_the_title($value[0]);
				$productobj = wc_get_product($value[0]);
				if (is_a($productobj,'WC_Product_Variation')){
					$attributes = $productobj->get_variation_attributes();
					$number_of_attributes = count($attributes);
					if ($number_of_attributes > 2){
						$product_title = $productobj->get_name();
						$product_title.=' - ';
						foreach ($attributes as $attribute){
							if (!empty($attribute)){
								$product_title.=$attribute.', ';
							}
							
						}
						$product_title = substr($product_title, 0, -2);
					} else {
						// remove &#8211;
						$product_title = str_replace('&#8211;', '-', $product_title);
					}
				}

				$skuval = $productobj->get_sku();
				if (!empty($skuval)){
					$product_title.=' (SKU: '.$skuval.' )';
				}

				// add title
				array_push($temparray,$value[0].': '.$product_title);	

				// add regular and sale price and tiered price
				$reg_price = get_post_meta($value[0],'_regular_price', true);
				$sal_price = get_post_meta($value[0],'_sale_price', true);
				$tie_price = get_post_meta($value[0],'b2bking_product_pricetiers_group_b2c', true);
				array_push($temparray, $reg_price);
				array_push($temparray, $sal_price);
				array_push($temparray, $tie_price);

				foreach ($groups as $group){
					$group_price = get_post_meta($value[0],'b2bking_regular_product_price_group_'.$group->ID, true);
					array_push($temparray, $group_price);
					$group_price = get_post_meta($value[0],'b2bking_sale_product_price_group_'.$group->ID, true);
					array_push($temparray, $group_price);
					$tiered_price = get_post_meta($value[0],'b2bking_product_pricetiers_group_'.$group->ID, true);
					array_push($temparray, $tiered_price);
				}
				fputcsv($output, $temparray); 
			}
		}

		fclose($output);
		exit();
		
	}

	function check_user_is_agent_with_access(){


    	// check if switch cookie is set
    	if (isset($_COOKIE['salesking_switch_cookie'])){
	    	$switch_to = sanitize_text_field($_COOKIE['salesking_switch_cookie']);
	    	$current_id = get_current_user_id();

	    	if (!empty($switch_to) && is_user_logged_in()){
	    		// show bar
				$udata = get_userdata( get_current_user_id() );
				$name = $udata->first_name.' '.$udata->last_name;

				// get agent details
				$agent = explode('_',$switch_to);
				$customer_id = intval($agent[0]);
				$agent_id = intval($agent[1]);
				$agent_registration = $agent[2];
				// check real registration in database
				$udataagent = get_userdata( $agent_id );
	            $registered_date = $udataagent->user_registered;

	            // if current logged in user is the one in the cookie + agent cookie checks out
	            if ($current_id === $customer_id && $agent_registration === $registered_date){
	            	return apply_filters('b2bking_enable_salesking_visibility', true);
	            }
	        }
	    }
        return false;
    }

    function get_current_agent_id(){
    	if (isset($_COOKIE['salesking_switch_cookie'])){
	    	$switch_to = sanitize_text_field($_COOKIE['salesking_switch_cookie']);
	    	if (!empty($switch_to)){
	    		$agent = explode('_',$switch_to);
	    		$agent_id = intval($agent[1]);
	    		return $agent_id;
	    	}
	    }
	    return false;
    }

	function b2bkingbulksetusers(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$group = sanitize_text_field($_POST['chosen_group']);

		// get users
		$users = get_users(array(
			'fields'=> 'ids',
		));
		
		if (!empty($users)) {
		    // loop trough each author
		    foreach ($users as $user){
		       // move all users to the group
		       if ($group === 'b2cuser'){
		       		b2bking()->update_user_group($user, 'no');
		       		update_user_meta($user, 'b2bking_b2buser', 'no');

		       } else {

		       		b2bking()->update_user_group($user, $group);
		       		update_user_meta($user, 'b2bking_b2buser', 'yes');
		       }
		    }
		}

		// delete all b2bking transients
		
		b2bking()->clear_caches_transients();


		echo 'success';
		exit();

	}

	function b2bkingbulksetcategory(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$option = sanitize_text_field($_POST['chosen_option']);

		// get categories
		$terms = get_terms(array(
			'fields'=> 'ids',
			'post_status' => 'publish',
			'numberposts' => -1,
		));

		$groups = get_posts([
		  'post_type' => 'b2bking_group',
		  'post_status' => 'publish',
		  'numberposts' => -1,
		  'fields' =>'ids',
		]);
		
		if (!empty($terms)) {
		    // loop trough each term
		    foreach ($terms as $term){
		       // move all users to the group
		       if ($option === 'visibleallgroups'){
					update_term_meta($term, 'b2bking_group_b2c', 1);
					update_term_meta($term, 'b2bking_group_0', 1);
					foreach ($groups as $group){
						update_term_meta($term, 'b2bking_group_'.$group, 1);
					}
		       } else if ($option === 'notvisibleallgroups') {
		       		update_term_meta($term, 'b2bking_group_b2c', 0);
		       		update_term_meta($term, 'b2bking_group_0', 0);
		       		foreach ($groups as $group){
		       			update_term_meta($term, 'b2bking_group_'.$group, 0);
		       		}
		       } else if ($option === 'visibleb2c'){
		       		update_term_meta($term, 'b2bking_group_b2c', 1);
		       } else if ($option === 'notvisibleb2c'){
		       		update_term_meta($term, 'b2bking_group_b2c', 0);
		       } else if ($option === 'visibleloggedout'){
		       		update_term_meta($term, 'b2bking_group_0', 1);
		       } else if ($option === 'notvisibleloggedout'){
		       		update_term_meta($term, 'b2bking_group_0', 0);
		       } else {
		       		// visible for specific group
		       		$chosengroupid = explode('_',$option)[1];
		       		update_term_meta($term, 'b2bking_group_'.$chosengroupid, 1);
		       }
		    }
		}

		// clear cache
		
		b2bking()->clear_caches_transients();


		echo 'success';
		exit();
	}

	function b2bkingbulksetsubaccounts(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$option_first = trim(sanitize_text_field($_POST['option_first']));
		$option_second = trim(sanitize_text_field($_POST['option_second']));

		$subaccount_ids = explode(',',$option_first);
		$parent_id = trim($option_second);

		foreach ($subaccount_ids as $subaccount_id){
			$subaccount_id_trimmed = trim($subaccount_id);
			update_user_meta($subaccount_id_trimmed,'b2bking_account_type', 'subaccount');
			update_user_meta($subaccount_id_trimmed,'b2bking_b2buser', 'yes');
			update_user_meta($subaccount_id_trimmed,'b2bking_account_parent', $parent_id);

			$current_subaccounts_list = get_user_meta($parent_id,'b2bking_subaccounts_list', true);
			update_user_meta($parent_id,'b2bking_subaccounts_list', $current_subaccounts_list.','.$subaccount_id_trimmed);

			// enable all permissions for subaccount
			update_user_meta($subaccount_id_trimmed, 'b2bking_account_permission_buy', 1);
			update_user_meta($subaccount_id_trimmed, 'b2bking_account_permission_view_orders', 1); 
			update_user_meta($subaccount_id_trimmed, 'b2bking_account_permission_view_offers', 1); 
			update_user_meta($subaccount_id_trimmed, 'b2bking_account_permission_view_conversations', 1); 
			update_user_meta($subaccount_id_trimmed, 'b2bking_account_permission_view_lists', 1); 

			do_action('b2bking_bulk_set_subaccounts', $subaccount_id_trimmed, $parent_id);

		}


		// delete all b2bking transients
		
		b2bking()->clear_caches_transients();

		echo 'success';
		exit();

	}

	function b2bkingbulksetsubaccountsregular(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$option_first = trim(sanitize_text_field($_POST['option_first']));

		$subaccount_ids = explode(',',$option_first);

		foreach ($subaccount_ids as $subaccount_id){
			$subaccount_id_trimmed = trim($subaccount_id);
			update_user_meta($subaccount_id_trimmed,'b2bking_account_type', 'regular');
			$parent_id = get_user_meta($subaccount_id_trimmed,'b2bking_account_parent', true);


			//remove from list of subaccounts
			$current_subaccounts_list = get_user_meta($parent_id,'b2bking_subaccounts_list', true);
			$current_subaccounts_list = str_replace(','.$subaccount_id_trimmed,'',$current_subaccounts_list);

			update_user_meta($parent_id,'b2bking_subaccounts_list', $current_subaccounts_list);

		}


		// delete all b2bking transients
		
		b2bking()->clear_caches_transients();

		echo 'success';
		exit();

	}

	//copied from Public
	// Hide prices to guest users
	function b2bking_hide_prices_guest_users( $price, $product ) {
		// if user is guest, OR multisite B2B/B2C separation is enabled and user should be treated as guest
		if (!is_user_logged_in() || (intval(get_option( 'b2bking_multisite_separate_b2bb2c_setting', 0 )) === 1 && get_user_meta(get_current_user_id(), 'b2bking_b2buser', true) !== 'yes')){
			$pricetext = get_option('b2bking_hide_prices_guests_text_setting', esc_html__('Login to view prices','b2bking'));
			$pricetext = apply_filters('b2bking_hide_price_product_text', $pricetext, $product, $price);
			return $pricetext;
		} else {
			return $price;
		}
	}

	function b2bking_disable_purchasable_guest_users($purchasable){
		// if user is guest, or multisite b2b/b2b separation is enabled and user should be treated as guest
		if (!is_user_logged_in() || (intval(get_option( 'b2bking_multisite_separate_b2bb2c_setting', 0 )) === 1 && get_user_meta(get_current_user_id(), 'b2bking_b2buser', true) !== 'yes')){
			return false;
		} else {
			return $purchasable;
		}
	}

	// Tiered pricing for AJAX
	function b2bking_tiered_pricing_fixed_price($price, $product){

		$disabled_product = apply_filters('b2bking_disable_group_tiered_pricing_product_id', false, $product->get_id());
		if ($disabled_product === true){
			return $price;
		}

		if (isset($_POST['_inline_edit'])){
			return $price;
		}
		if (isset($_REQUEST['bulk_edit'])){
		    return $price;
		}
		
		$user_id = get_current_user_id();
    	$account_type = get_user_meta($user_id,'b2bking_account_type', true);
    	if ($account_type === 'subaccount'){
    		// for all intents and purposes set current user as the subaccount parent
    		$parent_user_id = get_user_meta($user_id, 'b2bking_account_parent', true);
    		$user_id = $parent_user_id;
    	}

    	// check transient to see if the current price has been set already via another function
    	//if (get_transient('b2bking_user_'.$user_id.'_product_'.$product->get_id().'_custom_set_price') === $price){
    	if (b2bking()->get_global_data('custom_set_price', $product->get_id(), $user_id) === $price){

    		return $price;
    	}

		$currentusergroupidnr = b2bking()->get_user_group($user_id);
			
		// Search price tiers
		$price_tiers = get_post_meta($product->get_id(), 'b2bking_product_pricetiers_group_'.$currentusergroupidnr, true );

		// if no tiers, get regular
		if (empty($price_tiers)){
			$price_tiers = get_post_meta($product->get_id(), 'b2bking_product_pricetiers_group_b2c', true );
		}

		if (!empty($price_tiers)){
			// if there are price tiers, check product quantity in cart and set price accordingly

			// find product quantity in cart
			$product_id = $product->get_id();
			$quantity = 0;
			if (is_object( WC()->cart )){
			    foreach( WC()->cart->get_cart() as $cart_item ){
			        if ( $product_id === $cart_item['product_id'] || $product_id === $cart_item['variation_id']){
			            $quantity = $cart_item['quantity'];
			            break;
			        }
			    }
			}

		    if ($quantity !== 0){
				$price_tiers = explode(';', $price_tiers);
				$quantities_array = array();
				$prices_array = array();
				// first eliminate all quantities larger than the quantity in cart
				foreach($price_tiers as $tier){
					$tier_values = explode(':', $tier);
					if ($tier_values[0] <= $quantity && !empty($tier_values[0])){
						array_push($quantities_array, $tier_values[0]);
						$prices_array[$tier_values[0]] = b2bking()->tofloat($tier_values[1]);
					}
				}

				// if any number remains
				if(count($quantities_array) !== 0){
					// get the largest number
					$largest = max($quantities_array);
					// clear cache mostly for variable products
					if (apply_filters('b2bking_clear_wc_products_cache', true)){
						WC_Cache_Helper::get_transient_version( 'product', true );
					}

					// if regular table exist, but group table does not exist
					// apply tiered pricing only if the user's group price is not already smaller than tier price
					if (empty(get_post_meta($product->get_id(), 'b2bking_product_pricetiers_group_'.$currentusergroupidnr, true ))){
						if (b2bking()->tofloat($price) > b2bking()->tofloat($prices_array[$largest])){
							return $prices_array[$largest];
						} else {
							// return regular price
							return $price;
						}
					} else {
						return $prices_array[$largest];
					}

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

	function b2bking_individual_pricing_fixed_price($price, $product){

		// compatibility with 'All Products for WooCommerce Subscriptions'
		if (apply_filters('b2bking_use_compatibility_code_wcsatt', true)){
			if (class_exists('WCS_ATT_Product')){
				if ( WCS_ATT_Product::is_subscription( $product ) ) {
					return $price;
				}
			}
		}

		$disabled_product_individual = apply_filters('b2bking_disable_product_individual_product_id', false, $product->get_id());
		if ($disabled_product_individual == true){
			return $price;
		}

		$disabled_product = apply_filters('b2bking_disable_group_tiered_pricing_product_id', false, $product->get_id());
		if ($disabled_product === true){
			return $price;
		}

		if (isset($_POST['_inline_edit'])){
			return $price;
		}
		if (isset($_REQUEST['bulk_edit'])){
		    return $price;
		}
		
			if (is_user_logged_in()){
				$user_id = get_current_user_id();
		    	$account_type = get_user_meta($user_id,'b2bking_account_type', true);
		    	if ($account_type === 'subaccount'){
		    		// for all intents and purposes set current user as the subaccount parent
		    		$parent_user_id = get_user_meta($user_id, 'b2bking_account_parent', true);
		    		$user_id = $parent_user_id;
		    	}

		    	// check transient to see if the current price has been set already via another function
		    	//if (get_transient('b2bking_user_'.$user_id.'_product_'.$product->get_id().'_custom_set_price') === $price){
		    	if (b2bking()->get_global_data('custom_set_price', $product->get_id(), $user_id) === $price){

		    		return $price;
		    	}

		    	$is_b2b_user = get_user_meta( $user_id, 'b2bking_b2buser', true );
				$currentusergroupidnr = b2bking()->get_user_group($user_id);
				if ($is_b2b_user === 'yes'){
					// Search if there is a specific price set for the user's group
					$b2b_price = b2bking()->tofloat(get_post_meta($product->get_id(), 'b2bking_regular_product_price_group_'.$currentusergroupidnr, true ));
										
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

		$disabled_product = apply_filters('b2bking_disable_group_tiered_pricing_product_id', false, $product->get_id());
		if ($disabled_product === true){
			return $price;
		}

		if (isset($_POST['_inline_edit'])){
			return $sale_price;
		}
		if (isset($_REQUEST['bulk_edit'])){
		    return $sale_price;
		}

		if (is_user_logged_in()){
			$user_id = get_current_user_id();
	    	$account_type = get_user_meta($user_id,'b2bking_account_type', true);
	    	if ($account_type === 'subaccount'){
	    		// for all intents and purposes set current user as the subaccount parent
	    		$parent_user_id = get_user_meta($user_id, 'b2bking_account_parent', true);
	    		$user_id = $parent_user_id;
	    	}

	    	$is_b2b_user = get_user_meta( $user_id, 'b2bking_b2buser', true );
			$currentusergroupidnr = b2bking()->get_user_group($user_id);
			if ($is_b2b_user === 'yes'){
				// Search if there is a specific price set for the user's group
				$b2b_price = b2bking()->tofloat(get_post_meta($product->get_id(), 'b2bking_sale_product_price_group_'.$currentusergroupidnr, true ));
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

					// First check that there is no tiered price
					$have_tiered_price = 'no';
					// Search price tiers
					$price_tiers = get_post_meta($product->get_id(), 'b2bking_product_pricetiers_group_'.$currentusergroupidnr, true );

					// if no tiers, get regular
					if (empty($price_tiers)){
						$price_tiers = get_post_meta($product->get_id(), 'b2bking_product_pricetiers_group_b2c', true );
					}

					if (!empty($price_tiers)){
						// if there are price tiers, check product quantity in cart and set price accordingly

						// find product quantity in cart
						$product_id = $product->get_id();
						$quantity = 0;
						if (is_object( WC()->cart )){
						    foreach( WC()->cart->get_cart() as $cart_item ){
						        if ( $product_id === $cart_item['product_id'] || $product_id === $cart_item['variation_id']){
						            $quantity = $cart_item['quantity'];
						            break;
						        }
						    }
						}

					    if ($quantity !== 0){
							$price_tiers = explode(';', $price_tiers);
							$quantities_array = array();
							$prices_array = array();
							// first eliminate all quantities larger than the quantity in cart
							foreach($price_tiers as $tier){
								$tier_values = explode(':', $tier);
								if ($tier_values[0] <= $quantity && !empty($tier_values[0])){
									array_push($quantities_array, $tier_values[0]);
									$prices_array[$tier_values[0]] = b2bking()->tofloat($tier_values[1]);
								}
							}

							// if any number remains
							if(count($quantities_array) !== 0){
								// get the largest number
								$largest = max($quantities_array);
								// clear cache mostly for variable products
								$have_tiered_price = 'yes';

							}
						}
					} 
					if ($have_tiered_price === 'no'){
						return $b2b_price;
					} else {
						return $sale_price;
					}
				} else {
					// we have reached here = sale price is empty
					// if there is a regular price, but the b2c sale price is smaller, return false instead of b2c sale price
					$b2b_regular_price = b2bking()->tofloat(get_post_meta($product->get_id(), 'b2bking_regular_product_price_group_'.$currentusergroupidnr, true ));

					if (b2bking()->tofloat($sale_price) < b2bking()->tofloat($b2b_regular_price)){
						return false;
					} else {
						return $sale_price;
					}

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

		$disabled_product = apply_filters('b2bking_disable_group_tiered_pricing_product_id', false, $product->get_id());
		if ($disabled_product === true){
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

	    	$is_b2b_user = get_user_meta( $user_id, 'b2bking_b2buser', true );
			$currentusergroupidnr = b2bking()->get_user_group($user_id);
			if ($is_b2b_user === 'yes'){
				// Search if there is a specific price set for the user's group
				b2bking()->tofloat($b2b_price = get_post_meta($product->get_id(), 'b2bking_sale_product_price_group_'.$currentusergroupidnr, true ));
				if (!empty($b2b_price)){

					// check that there is no tiered price
					// First check that there is no tiered price
					$have_tiered_price = 'no';
					// Search price tiers
					$price_tiers = get_post_meta($product->get_id(), 'b2bking_product_pricetiers_group_'.$currentusergroupidnr, true );

					// if no tiers, get regular
					if (empty($price_tiers)){
						$price_tiers = get_post_meta($product->get_id(), 'b2bking_product_pricetiers_group_b2c', true );
					}

					if (!empty($price_tiers)){
						// if there are price tiers, check product quantity in cart and set price accordingly

						// find product quantity in cart
						$product_id = $product->get_id();
						$quantity = 0;
						if (is_object( WC()->cart )){
						    foreach( WC()->cart->get_cart() as $cart_item ){
						        if ( $product_id === $cart_item['product_id'] || $product_id === $cart_item['variation_id']){
						            $quantity = $cart_item['quantity'];
						            break;
						        }
						    }
						}

					    if ($quantity !== 0){
							$price_tiers = explode(';', $price_tiers);
							$quantities_array = array();
							$prices_array = array();
							// first eliminate all quantities larger than the quantity in cart
							foreach($price_tiers as $tier){
								$tier_values = explode(':', $tier);
								if ($tier_values[0] <= $quantity && !empty($tier_values[0])){
									array_push($quantities_array, $tier_values[0]);
									$prices_array[$tier_values[0]] = b2bking()->tofloat($tier_values[1]);
								}
							}

							// if any number remains
							if(count($quantities_array) !== 0){
								// get the largest number
								$largest = max($quantities_array);
								// clear cache mostly for variable products
								$have_tiered_price = 'yes';

							}
						}
					} 

					if ($have_tiered_price === 'no'){
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
		}

	    return $price_html;
	}

	function b2bking_individual_pricing_discount_display_dynamic_price_in_cart($cart){

		if (isset($_POST['_inline_edit'])){
			return;
		}
		if (isset($_REQUEST['bulk_edit'])){
		    return;
		}

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

    	$is_b2b_user = get_user_meta( $user_id, 'b2bking_b2buser', true );
		$currentusergroupidnr = b2bking()->get_user_group($user_id);
		if ($is_b2b_user === 'yes'){
			// Iterate through each cart item
			foreach( $cart->get_cart() as $cart_item ) {
				// Search if there is a specific price set for the user's group
				if (isset($cart_item['variation_id']) && intval($cart_item['variation_id']) !== 0){
					$b2b_price = b2bking()->tofloat(get_post_meta($cart_item['variation_id'], 'b2bking_sale_product_price_group_'.$currentusergroupidnr, true ));
					$product_id_set = $cart_item['variation_id'];
				} else {
					$b2b_price = b2bking()->tofloat(get_post_meta($cart_item['product_id'], 'b2bking_sale_product_price_group_'.$currentusergroupidnr, true ));
					$product_id_set = $cart_item['product_id'];
				}

				$disabled_product = apply_filters('b2bking_disable_group_tiered_pricing_product_id', false, $product_id_set);
				if ($disabled_product === true){
					return;
				}
				
				if (!empty($b2b_price)){

					// First check that there is no tiered price
					$product = wc_get_product($product_id_set);
					$have_tiered_price = 'no';
					// Search price tiers
					$price_tiers = get_post_meta($product->get_id(), 'b2bking_product_pricetiers_group_'.$currentusergroupidnr, true );

					// if no tiers, get regular
					if (empty($price_tiers)){
						$price_tiers = get_post_meta($product->get_id(), 'b2bking_product_pricetiers_group_b2c', true );
					}

					if (!empty($price_tiers)){
						// if there are price tiers, check product quantity in cart and set price accordingly

						// find product quantity in cart
						$product_id = $product->get_id();
						$quantity = 0;
						if (is_object( WC()->cart )){
						    foreach( WC()->cart->get_cart() as $cart_item ){
						        if ( $product_id === $cart_item['product_id'] || $product_id === $cart_item['variation_id']){
						            $quantity = $cart_item['quantity'];
						            break;
						        }
						    }
						}

					    if ($quantity !== 0){
							$price_tiers = explode(';', $price_tiers);
							$quantities_array = array();
							$prices_array = array();
							// first eliminate all quantities larger than the quantity in cart
							foreach($price_tiers as $tier){
								$tier_values = explode(':', $tier);
								if ($tier_values[0] <= $quantity && !empty($tier_values[0])){
									array_push($quantities_array, $tier_values[0]);
									$prices_array[$tier_values[0]] = b2bking()->tofloat($tier_values[1]);
								}
							}

							// if any number remains
							if(count($quantities_array) !== 0){
								// get the largest number
								$largest = max($quantities_array);
								// clear cache mostly for variable products
								$have_tiered_price = 'yes';

							}
						}
					} 

					if ($have_tiered_price === 'no'){
						$cart_item['data']->set_price( $b2b_price );
						//set_transient('b2bking_user_'.$user_id.'_product_'.$product_id_set.'_custom_set_price', $b2b_price);
						b2bking()->set_global_data('custom_set_price', $b2b_price, $product_id_set, $user_id);
					}
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

    	$is_b2b_user = get_user_meta( $user_id, 'b2bking_b2buser', true );
		$currentusergroupidnr = b2bking()->get_user_group($user_id);
		if ($is_b2b_user === 'yes'){
			if (isset($cart_item['variation_id']) && intval($cart_item['variation_id']) !== 0){
				$b2b_price = b2bking()->tofloat(get_post_meta($cart_item['variation_id'], 'b2bking_sale_product_price_group_'.$currentusergroupidnr, true ));
				$product_id_set = $cart_item['variation_id'];
			} else {
				$b2b_price = b2bking()->tofloat(get_post_meta($cart_item['product_id'], 'b2bking_sale_product_price_group_'.$currentusergroupidnr, true ));
				$product_id_set = $cart_item['product_id'];
			}

			$disabled_product = apply_filters('b2bking_disable_group_tiered_pricing_product_id', false, $product_id_set);
			if ($disabled_product === true){
				return $price;
			}

			if (!empty($b2b_price)){

				// First check that there is no tiered price
				$product = wc_get_product($product_id_set);
				$have_tiered_price = 'no';
				// Search price tiers
				$price_tiers = get_post_meta($product->get_id(), 'b2bking_product_pricetiers_group_'.$currentusergroupidnr, true );

				// if no tiers, get regular
				if (empty($price_tiers)){
					$price_tiers = get_post_meta($product->get_id(), 'b2bking_product_pricetiers_group_b2c', true );
				}

				if (!empty($price_tiers)){
					// if there are price tiers, check product quantity in cart and set price accordingly

					// find product quantity in cart
					$product_id = $product->get_id();
					$quantity = 0;
					if (is_object( WC()->cart )){
					    foreach( WC()->cart->get_cart() as $cart_item ){
					        if ( $product_id === $cart_item['product_id'] || $product_id === $cart_item['variation_id']){
					            $quantity = $cart_item['quantity'];
					            break;
					        }
					    }
					}

				    if ($quantity !== 0){
						$price_tiers = explode(';', $price_tiers);
						$quantities_array = array();
						$prices_array = array();
						// first eliminate all quantities larger than the quantity in cart
						foreach($price_tiers as $tier){
							$tier_values = explode(':', $tier);
							if ($tier_values[0] <= $quantity && !empty($tier_values[0])){
								array_push($quantities_array, $tier_values[0]);
								$prices_array[$tier_values[0]] = b2bking()->tofloat($tier_values[1]);
							}
						}

						// if any number remains
						if(count($quantities_array) !== 0){
							// get the largest number
							$largest = max($quantities_array);
							// clear cache mostly for variable products
							$have_tiered_price = 'yes';

						}
					}
				} 
				if ($have_tiered_price === 'no'){
					
					$discount_price = b2bking()->b2bking_wc_get_price_to_display( wc_get_product($product_id_set), array( 'price' => $cart_item['data']->get_sale_price() ) ); // get sale price
					
					if ($discount_price !== NULL && $discount_price !== ''){
						$price = wc_price($discount_price, 4); 
					}
				}
			} 
		}
		return $price;
	}

	// Visibility rules, copied from public
	function b2bking_product_categories_visibility_rules( $q ){

		if ( 'product' !== $q->get( 'post_type' ) && array('product_variation') !== $q->get( 'post_type' ) ) { 
			return;
		} 

		if (intval(get_option( 'b2bking_all_products_visible_all_users_setting', 1 )) !== 1){

			if ( get_option( 'b2bking_plugin_status_setting', 'b2b' ) !== 'disabled' ){

				$currentuserid = get_current_user_id();
				// if salesking agent, get visibility of sales agent
    	    	if ($this->check_user_is_agent_with_access()){
    				$agent_id = $this->get_current_agent_id();
    				$currentuserid = $agent_id;
    			}

    			$account_type = get_user_meta($currentuserid,'b2bking_account_type', true);
    			if ($account_type === 'subaccount'){
    				// for all intents and purposes set current user as the subaccount parent
    				$parent_user_id = get_user_meta($currentuserid, 'b2bking_account_parent', true);
    				$currentuserid = $parent_user_id;
    			}

    			if (!defined('ICL_LANGUAGE_NAME_EN')){
					$allTheIDs = get_transient('b2bking_user_'.$currentuserid.'_ajax_visibility');
				} else {
					$allTheIDs = get_transient('b2bking_user_'.$currentuserid.'_ajax_visibility'.ICL_LANGUAGE_NAME_EN);
				}


				$allTheIDs = apply_filters('b2bking_ids_post_in_visibility', $allTheIDs);

				$currentval = $q->query_vars['post__in'];
				if (!empty($currentval) && $allTheIDs !== false){
					$allTheIDs = array_intersect($allTheIDs, $currentval);
				}
					
				if ($allTheIDs){
				    if(!empty($allTheIDs)){
				    	$q->set('post__in',$allTheIDs);
					}
				}
			}
		}
	}


	// Visibility rules, copied from public
	function b2bking_product_categories_visibility_rules_live( $q ){

		if ( 'product' !== $q->get( 'post_type' ) && array('product_variation') !== $q->get( 'post_type' ) ) { 
			return;
		} 

		if (intval(get_option( 'b2bking_all_products_visible_all_users_setting', 1 )) !== 1){

			if ( get_option( 'b2bking_plugin_status_setting', 'b2b' ) !== 'disabled' ){

				$currentuserid = get_current_user_id();
				// if salesking agent, get visibility of sales agent
    	    	if ($this->check_user_is_agent_with_access()){
    				$agent_id = $this->get_current_agent_id();
    				$currentuserid = $agent_id;
    			}

    			$account_type = get_user_meta($currentuserid,'b2bking_account_type', true);
    			if ($account_type === 'subaccount'){
    				// for all intents and purposes set current user as the subaccount parent
    				$parent_user_id = get_user_meta($currentuserid, 'b2bking_account_parent', true);
    				$currentuserid = $parent_user_id;
    			}

				$allTheIDs = get_transient('b2bking_user_'.$currentuserid.'_ajax_visibility_live');
				$allTheIDs = apply_filters('b2bking_ids_post_in_visibility', $allTheIDs);

				$currentval = $q->query_vars['post__in'];
				if (!empty($currentval) && $allTheIDs !== false){
					$allTheIDs = array_intersect($allTheIDs, $currentval);
				}	
				
				if ($allTheIDs){
				    if(!empty($allTheIDs)){
				    	$q->set('post__in',$allTheIDs);
					}
				}
			}
		}
	}

	function get_visibility_set_transient(){
		if (!defined('ICL_LANGUAGE_NAME_EN')){
			$transient_check = get_transient('b2bking_user_'.get_current_user_id().'_ajax_visibility');
		} else {
			$transient_check = get_transient('b2bking_user_'.get_current_user_id().'_ajax_visibility'.ICL_LANGUAGE_NAME_EN);	
		}
		if (!$transient_check){

			if (intval(get_option( 'b2bking_all_products_visible_all_users_setting', 1 )) !== 1){

				if ( get_option( 'b2bking_plugin_status_setting', 'b2b' ) !== 'disabled' ){

					$user_is_b2b = get_user_meta( get_current_user_id(), 'b2bking_b2buser', true );

					// if user logged in and is b2b
					if (is_user_logged_in() && ($user_is_b2b === 'yes')){
						// Get current user's data: group, id, login, etc
					    $currentuserid = get_current_user_id();
				    	$account_type = get_user_meta($currentuserid,'b2bking_account_type', true);
				    	if ($account_type === 'subaccount'){
				    		// for all intents and purposes set current user as the subaccount parent
				    		$parent_user_id = get_user_meta($currentuserid, 'b2bking_account_parent', true);
				    		$currentuserid = $parent_user_id;
				    	}
				        $currentuser = get_user_by('id', $currentuserid);
						$currentuserlogin = $currentuser -> user_login;
						$currentusergroupidnr = b2bking()->get_user_group($currentuserid);
					// if user is b2c
					} else if (is_user_logged_in() && ($user_is_b2b !== 'yes')){
						$currentuserlogin = 'b2c';
						$currentusergroupidnr = 'b2c';
					} else {
						$currentuserlogin = 0;
						$currentusergroupidnr = 0;
					}
					/*
					* 
					*	There are 2 separate queries that need to be made:
					* 	1. Query of all Categories visible to the USER AND all Categories visible to the USER'S GROUP 
					*	2. Query of all Products set to Manual visibility mode, visible to the user or the user's group 
					*
					*/

					// Build Visible Categories for the 1st Query
					$visiblecategories = array();
					$hiddencategories = array();

					$terms = get_terms( array( 
					    'taxonomy' => 'product_cat',
					    'fields' => 'ids',
					    'hide_empty' => false
					) );

					foreach ($terms as $term){

						/* 
						* If category is visible to GROUP OR category is visible to USER
						* Push category into visible categories array
						*/

						// first check group
						$group_meta = get_term_meta( $term, 'b2bking_group_'.$currentusergroupidnr, true );
						if (intval($group_meta) === 1){
							array_push($visiblecategories, $term);
						// else check user
						} else {
							$userlistcommas = get_term_meta( $term, 'b2bking_category_users_textarea', true );
							$userarray = explode(',', $userlistcommas);
							foreach ($userarray as $user){
								if (trim($user) === $currentuserlogin){
									array_push($visiblecategories, $term);
									break;
								}
							}
							// has reached this point, therefore category is not visible
							array_push($hiddencategories, $term);
						}
					}

					$product_category_visibility_array = array(
					           'taxonomy' => 'product_cat',
					           'field' => 'term_id',
					           'terms' => $visiblecategories, 
					           'operator' => 'IN'
					);

					// if user has enabled "hidden has priority", override setting
					if (intval(get_option( 'b2bking_hidden_has_priority_setting', 0 )) === 1){
						$product_category_visibility_array = array(
						           'taxonomy' => 'product_cat',
						           'field' => 'term_id',
						           'terms' => $hiddencategories, 
						           'operator' => 'NOT IN'
						);
					}

					/* Get all items that do not have manual visibility set up */
					// get all products ids
					if (intval(get_option( 'b2bking_product_visibility_cache_setting', 0 )) === 1){
						if (!defined('ICL_LANGUAGE_NAME_EN')){
							$items_not_manual_visibility_array = get_transient('b2bking_not_manual_visibility_array');
						} else {
							$items_not_manual_visibility_array = get_transient('b2bking_not_manual_visibility_array'.ICL_LANGUAGE_NAME_EN);
						}
					} else {
						$items_not_manual_visibility_array = false;
					}
					
					if (!$items_not_manual_visibility_array){
						$all_prods = new WP_Query(array(
					        'posts_per_page' => -1,
					        'post_type' => 'product',
					        'fields' => 'ids'));
						$all_prod_ids = $all_prods->posts;

						// get all products with manual visibility ids
						$all_prods_manual = new WP_Query(array(
					        'posts_per_page' => -1,
					        'post_type' => 'product',
					        'fields' => 'ids',
		    		        'meta_query'=> array(
		                            'relation' => 'AND',
		                            array(
		                                'key' => 'b2bking_product_visibility_override',
		                                'value' => 'manual',
		                            )
		                        )));
						$all_prod_manual_ids = $all_prods_manual->posts;
						// get the difference
						$items_not_manual_visibility_array = array_diff($all_prod_ids,$all_prod_manual_ids);

						if (!defined('ICL_LANGUAGE_NAME_EN')){
							set_transient('b2bking_not_manual_visibility_array', $items_not_manual_visibility_array);
						} else {
							set_transient('b2bking_not_manual_visibility_array'.ICL_LANGUAGE_NAME_EN, $items_not_manual_visibility_array);

						}
					}

					if (empty($items_not_manual_visibility_array)){
						$items_not_manual_visibility_array = array('invalid');
					}

					// Build first query
				    $queryAparams = array(
				        'posts_per_page' => -1,
				        'post_type' => 'product',
				        'fields' => 'ids',
				        'tax_query' => array(
				        	$product_category_visibility_array
				        ),
					    'post__in' => $items_not_manual_visibility_array,
					);

				    // Build 2nd query: all manual visibility products with USER OR USER GROUP visibility
				    $queryBparams = array(
				        'posts_per_page' => -1,
				        'post_type' => 'product',
				        'fields' => 'ids',
				        'meta_query'=> array(
		                        'relation' => 'AND',
		                        array(
		                            'relation' => 'OR',
		                            array(
		                                'key' => 'b2bking_group_'.$currentusergroupidnr,
		                                'value' => '1'
		                            ),
		                            array(
		                                'key' => 'b2bking_user_'.$currentuserlogin,
		                                'value' => '1'
		                            )
		                        ),
		                        array(
		                            'key' => 'b2bking_product_visibility_override',
		                            'value' => 'manual',
		                        )
		                    ));

				    $queryA = new WP_Query($queryAparams);
				    $queryB = new WP_Query($queryBparams);

				    // Merge the 2 queries in an IDs array
				    $allTheIDs = array_merge($queryA->posts,$queryB->posts);

				    // put variations in here as well
    				$allvariationids = new WP_Query(array(
    			        'posts_per_page' => -1,
    			        'post_type' => 'product_variation',
    			        'post_status' => 'publish',
    			        'fields' => 'ids',
        		        'post_parent__in' => $allTheIDs
        		    ));
    				$allTheIDs = array_merge($allTheIDs,$allvariationids->posts);

    				if (!defined('ICL_LANGUAGE_NAME_EN')){
				    	set_transient('b2bking_user_'.get_current_user_id().'_ajax_visibility', $allTheIDs);
				    } else {
				    	set_transient('b2bking_user_'.get_current_user_id().'_ajax_visibility'.ICL_LANGUAGE_NAME_EN, $allTheIDs);	
				    }
				}
			}
		}
	}

	public function b2bking_direct_update_product_stock( $product_id_with_stock, $stock_quantity = null, $operation = 'set', $customer_id = 0) {
		global $wpdb;

		// get if customer is B2B
    	$account_type = get_user_meta($customer_id,'b2bking_account_type', true);
    	if ($account_type === 'subaccount'){
    		// for all intents and purposes set current user as the subaccount parent
    		$parent_user_id = get_user_meta($customer_id, 'b2bking_account_parent', true);
    		$customer_id = $parent_user_id;
    	}
    	$is_b2b = get_user_meta($customer_id,'b2bking_b2buser', true);

    	$product_id = $product_id_with_stock;
    	$productobj = wc_get_product($product_id_with_stock);
    	$metakey = '_stock';
    	if ($is_b2b === 'yes'){
    		$stocktreatment = get_option( 'b2bking_different_stock_treatment_b2b_setting', 'disabled' );
    		if ($stocktreatment === 'b2b'){
    			if ($productobj->is_type('simple')){
    				$separate_stock_quantities_b2b = get_post_meta($product_id,'_separate_stock_quantities_b2b', true);
    				if ($separate_stock_quantities_b2b === 'yes'){
    					$metakey = '_stock_b2b';
    				}

    			} else if ($productobj->is_type('variation')){
    				$separate_stock_quantities_b2b = get_post_meta($product_id,'variable_separate_stock_'.$product_id, true);
    				if ($separate_stock_quantities_b2b === 'yes'){
    					$metakey = 'variable_stock_b2b_'.$product_id;
    				}

    			} else if ($productobj->is_type('variable')){
    				$separate_stock_quantities_b2b = get_post_meta($product_id,'_separate_stock_quantities_b2b', true);
    				if ($separate_stock_quantities_b2b === 'yes'){
    					$metakey = '_stock_b2b';
    				}
    			}
    		}
    	}

		// Ensures a row exists to update.
		add_post_meta( $product_id_with_stock, $metakey, 0, true );

		if ( 'set' === $operation ) {
			$new_stock = wc_stock_amount( $stock_quantity );

			// Generate SQL.
			$sql = $wpdb->prepare(
				"UPDATE {$wpdb->postmeta} SET meta_value = %f WHERE post_id = %d AND meta_key='$metakey'",
				$new_stock,
				$product_id_with_stock
			);
		} else {
			$current_stock = wc_stock_amount(
				$wpdb->get_var(
					$wpdb->prepare(
						"SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key='$metakey'",
						$product_id_with_stock
					)
				)
			);

			// Calculate new value for filter below. Set multiplier to subtract or add the meta_value.
			switch ( $operation ) {
				case 'increase':
					$new_stock  = $current_stock + wc_stock_amount( $stock_quantity );
					$multiplier = 1;
					break;
				default:
					$new_stock  = $current_stock - wc_stock_amount( $stock_quantity );
					$multiplier = -1;
					break;
			}

			// Generate SQL.
			$sql = $wpdb->prepare(
				"UPDATE {$wpdb->postmeta} SET meta_value = meta_value %+f WHERE post_id = %d AND meta_key='$metakey'",
				wc_stock_amount( $stock_quantity ) * $multiplier, // This will either subtract or add depending on operation.
				$product_id_with_stock
			);
		}

		$sql = apply_filters( 'woocommerce_update_product_stock_query', $sql, $product_id_with_stock, $new_stock, $operation );

		$wpdb->query( $sql ); 

		// Cache delete is required (not only) to set correct data for lookup table (which reads from cache).
		// Sometimes I wonder if it shouldn't be part of update_lookup_table.
		wp_cache_delete( $product_id_with_stock, 'post_meta' );

		$datastore = WC_Data_Store::load( 'product' );
		$datastore->update_lookup_table( $product_id_with_stock, 'wc_product_meta_lookup' );


		/**
		 * Fire an action for this direct update so it can be detected by other code.
		 *
		 * @since 3.6
		 * @param int $product_id_with_stock Product ID that was updated directly.
		 */
		do_action( 'woocommerce_updated_product_stock', $product_id_with_stock );

		return $new_stock;
	}

	function b2bking_update_product_stock( $product, $stock_quantity = null, $operation = 'set', $updating = false, $customer_id = 0) {
		if ( ! is_a( $product, 'WC_Product' ) ) {
			$product = wc_get_product( $product );
		}

		if ( ! $product ) {
			return false;
		}

		if ( ! is_null( $stock_quantity ) && $product->managing_stock() ) {
			// Some products (variations) can have their stock managed by their parent. Get the correct object to be updated here.
			$product_id_with_stock = $product->get_stock_managed_by_id();
			$product_with_stock    = $product_id_with_stock !== $product->get_id() ? wc_get_product( $product_id_with_stock ) : $product;
			$data_store            = WC_Data_Store::load( 'product' );

			// Fire actions to let 3rd parties know the stock is about to be changed.
			if ( $product_with_stock->is_type( 'variation' ) ) {
				do_action( 'woocommerce_variation_before_set_stock', $product_with_stock );
			} else {
				do_action( 'woocommerce_product_before_set_stock', $product_with_stock );
			}

			// Update the database.
			$new_stock = $this->b2bking_direct_update_product_stock( $product_id_with_stock, $stock_quantity, $operation, $customer_id );
			
			// Update the product object.
			$data_store->read_stock_quantity( $product_with_stock, $new_stock );

			// If this is not being called during an update routine, save the product so stock status etc is in sync, and caches are cleared.
			if ( ! $updating ) {
				$product_with_stock->save();
			}

			// Fire actions to let 3rd parties know the stock changed.
			if ( $product_with_stock->is_type( 'variation' ) ) {
				do_action( 'woocommerce_variation_set_stock', $product_with_stock );
			} else {
				do_action( 'woocommerce_product_set_stock', $product_with_stock );
			}

			return $product_with_stock->get_stock_quantity();
		}
		return $product->get_stock_quantity();
	}

	function b2bking_get_stock_quantity_addable(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$product_id = intval(sanitize_text_field($_POST['id']));
		$product = wc_get_product($product_id);

		$stockqty = $product->get_stock_quantity();
		if ( ! $product->get_manage_stock() ){
			$stockqty = 999999999;
		} else {
			// if backorders, same 
			if ('yes' === $product->get_backorders() || 'notify' === $product->get_backorders()){
				$stockqty = 999999999;
			}
		}

		// first get the quantity available in cart
		$qtyincart = 0;
		if (is_object( WC()->cart )){
		    foreach( WC()->cart->get_cart() as $cart_item ){
		    	if (isset($cart_item['variation_id']) && intval($cart_item['variation_id']) === $product_id){
		    		$qtyincart += $cart_item['quantity'];
		    	} else if (isset($cart_item['product_id']) && intval($cart_item['product_id']) === $product_id){
		    		$qtyincart += $cart_item['quantity'];
		    	}
		    }
		}

		// get qty in stock

		// get quantity addable
		$qtyaddable = $stockqty - $qtyincart;

		if ($qtyaddable < 0){
			$qtyaddable = 0;
		}

		echo $qtyaddable;

		exit();


	}

	function b2bking_maybe_increase_stock_levels( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		$stock_reduced    = $order->get_data_store()->get_stock_reduced( $order_id );
		$trigger_increase = (bool) $stock_reduced;

		// Only continue if we're increasing stock.
		if ( ! $trigger_increase ) {
			return;
		}

		$this->b2bking_increase_stock_levels( $order );

		// Ensure stock is not marked as "reduced" anymore.
		$order->get_data_store()->set_stock_reduced( $order_id, false );
	}

	function b2bking_increase_stock_levels( $order_id ) {
		if ( is_a( $order_id, 'WC_Order' ) ) {
			$order    = $order_id;
			$order_id = $order->get_id();
		} else {
			$order = wc_get_order( $order_id );
		}

		// We need an order, and a store with stock management to continue.
		if ( ! $order || 'yes' !== get_option( 'woocommerce_manage_stock' ) || ! apply_filters( 'woocommerce_can_restore_order_stock', true, $order ) ) {
			return;
		}

		$changes = array();

		$customer_id = $order->get_customer_id();
		//b2b or b2c
		$stocktext = '';

		$account_type = get_user_meta($customer_id,'b2bking_account_type', true);
		if ($account_type === 'subaccount'){
			// for all intents and purposes set current user as the subaccount parent
			$parent_user_id = get_user_meta($customer_id, 'b2bking_account_parent', true);
			$customer_id = $parent_user_id;
		}
		$is_b2b = get_user_meta($customer_id,'b2bking_b2buser', true);

		$metakey = '_stock';
		if ($is_b2b === 'yes'){
			$stocktreatment = get_option( 'b2bking_different_stock_treatment_b2b_setting', 'disabled' );
			if ($stocktreatment === 'b2b'){
				$stocktext = '(B2B customer)';
			}
		}
		//


		// Loop over all items.
		foreach ( $order->get_items() as $item ) {
			if ( ! $item->is_type( 'line_item' ) ) {
				continue;
			}

			// Only increase stock once for each item.
			$product            = $item->get_product();
			$item_stock_reduced = $item->get_meta( '_reduced_stock', true );

			if ( ! $item_stock_reduced || ! $product || ! $product->managing_stock() ) {
				continue;
			}

			$item_name = $product->get_formatted_name();
			$new_stock = $this->b2bking_update_product_stock( $product, $item_stock_reduced, 'increase', false, $customer_id );

			if ( is_wp_error( $new_stock ) ) {
				/* translators: %s item name. */
				$order->add_order_note( sprintf( __( 'Unable to restore stock for item %s.', 'woocommerce' ), $item_name ) );
				continue;
			}

			$item->delete_meta_data( '_reduced_stock' );
			$item->save();

			$changes[] = $item_name . ' ' . ( $new_stock - $item_stock_reduced ) . '&rarr;' . $new_stock;
		}

		if ( $changes ) {

			$order->add_order_note( __( 'Stock levels increased:', 'woocommerce' ) . ' ' . implode( ', ', $changes ).' '.$stocktext );
		}

		do_action( 'woocommerce_restore_order_stock', $order );
	}

	function b2bking_reduce_stock_levels( $order_id ) {
		if ( is_a( $order_id, 'WC_Order' ) ) {
			$order    = $order_id;
			$order_id = $order->get_id();
		} else {
			$order = wc_get_order( $order_id );
		}
		// We need an order, and a store with stock management to continue.
		if ( ! $order || 'yes' !== get_option( 'woocommerce_manage_stock' ) || ! apply_filters( 'woocommerce_can_reduce_order_stock', true, $order ) ) {
			return;
		}

		$changes = array();

		$customer_id = $order->get_customer_id();

		// Loop over all items.
		foreach ( $order->get_items() as $item ) {
			if ( ! $item->is_type( 'line_item' ) ) {
				continue;
			}

			// Only reduce stock once for each item.
			$product            = $item->get_product();
			$item_stock_reduced = $item->get_meta( '_reduced_stock', true );

			if ( $item_stock_reduced || ! $product || ! $product->managing_stock() ) {
				continue;
			}

			$qty       = apply_filters( 'woocommerce_order_item_quantity', $item->get_quantity(), $order, $item );
			$item_name = $product->get_formatted_name();
			$new_stock = $this->b2bking_update_product_stock( $product, $qty, 'decrease', false, $customer_id );

			if ( is_wp_error( $new_stock ) ) {
				/* translators: %s item name. */
				$order->add_order_note( sprintf( __( 'Unable to reduce stock for item %s.', 'woocommerce' ), $item_name ) );
				continue;
			}

			$item->add_meta_data( '_reduced_stock', $qty, true );
			$item->save();

			$changes[] = array(
				'product' => $product,
				'from'    => $new_stock + $qty,
				'to'      => $new_stock,
			);
		}

		$this->b2bking_trigger_stock_change_notifications( $order, $changes, $customer_id );

		do_action( 'woocommerce_reduce_order_stock', $order );
	}

	function b2bking_trigger_stock_change_notifications( $order, $changes, $customer_id ) {
		if ( empty( $changes ) ) {
			return;
		}

		$order_notes     = array();
		$no_stock_amount = absint( get_option( 'woocommerce_notify_no_stock_amount', 0 ) );

		foreach ( $changes as $change ) {
			$order_notes[]    = $change['product']->get_formatted_name() . ' ' . $change['from'] . '&rarr;' . $change['to'];
			$low_stock_amount = absint( wc_get_low_stock_amount( wc_get_product( $change['product']->get_id() ) ) );
			if ( $change['to'] <= $no_stock_amount ) {
				do_action( 'woocommerce_no_stock', wc_get_product( $change['product']->get_id() ) );
			} elseif ( $change['to'] <= $low_stock_amount ) {
				do_action( 'woocommerce_low_stock', wc_get_product( $change['product']->get_id() ) );
			}

			if ( $change['to'] < 0 ) {
				do_action(
					'woocommerce_product_on_backorder',
					array(
						'product'  => wc_get_product( $change['product']->get_id() ),
						'order_id' => $order->get_id(),
						'quantity' => abs( $change['from'] - $change['to'] ),
					)
				);
			}
		}

		//b2b or b2c
		$stocktext = '';

		$account_type = get_user_meta($customer_id,'b2bking_account_type', true);
		if ($account_type === 'subaccount'){
			// for all intents and purposes set current user as the subaccount parent
			$parent_user_id = get_user_meta($customer_id, 'b2bking_account_parent', true);
			$customer_id = $parent_user_id;
		}
		$is_b2b = get_user_meta($customer_id,'b2bking_b2buser', true);

		$metakey = '_stock';
		if ($is_b2b === 'yes'){
			$stocktreatment = get_option( 'b2bking_different_stock_treatment_b2b_setting', 'disabled' );
			if ($stocktreatment === 'b2b'){
				$stocktext = '(B2B customer)';
			}
		}
		//

		$order->add_order_note( __( 'Stock levels reduced:', 'woocommerce' ) . ' ' . implode( ', ', $order_notes ).' '.$stocktext );
	}

	function b2bking_maybe_reduce_stock_levels($order_id){
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		$stock_reduced  = $order->get_data_store()->get_stock_reduced( $order_id );
		$trigger_reduce = apply_filters( 'woocommerce_payment_complete_reduce_order_stock', ! $stock_reduced, $order_id );

		// Only continue if we're reducing stock.
		if ( ! $trigger_reduce ) {
			return;
		}

		$this->b2bking_reduce_stock_levels( $order );

		// Ensure stock is marked as "reduced" in case payment complete or other stock actions are called.
		$order->get_data_store()->set_stock_reduced( $order_id, true );
	}

	function b2bking_stock_filter_backorders($val, $data){
		$stocktreatment = get_option( 'b2bking_different_stock_treatment_b2b_setting', 'disabled' );
		// Get current user
		$user_id = get_current_user_id();

    	$account_type = get_user_meta($user_id,'b2bking_account_type', true);
    	if ($account_type === 'subaccount'){
    		// for all intents and purposes set current user as the subaccount parent
    		$parent_user_id = get_user_meta($user_id, 'b2bking_account_parent', true);
    		$user_id = $parent_user_id;
    	}
    	$is_b2b = get_user_meta($user_id,'b2bking_b2buser', true);

    	if ($is_b2b === 'yes'){
    		if ($stocktreatment === 'b2b'){
	    		// if current user is b2b
	    		$product_id = $data->get_id();
	    		$val = get_post_meta($product_id,'_backorders_b2b', true);
    		}
    	}
		
		return $val;
	}

	function b2bking_stock_filter_stock_quantity($val, $data){
		$stocktreatment = get_option( 'b2bking_different_stock_treatment_b2b_setting', 'disabled' );
		// Get current user
		$user_id = get_current_user_id();

    	$account_type = get_user_meta($user_id,'b2bking_account_type', true);
    	if ($account_type === 'subaccount'){
    		// for all intents and purposes set current user as the subaccount parent
    		$parent_user_id = get_user_meta($user_id, 'b2bking_account_parent', true);
    		$user_id = $parent_user_id;
    	}
    	$is_b2b = get_user_meta($user_id,'b2bking_b2buser', true);

    	if ($is_b2b === 'yes'){
    		if ($stocktreatment === 'b2b'){
	    		// if current user is b2b
	    		$product_id = $data->get_id();

	    		$separate_stock_quantities_b2b = get_post_meta($product_id,'_separate_stock_quantities_b2b', true);
	    		if ($separate_stock_quantities_b2b === 'yes'){
	    			$val = get_post_meta($product_id,'_stock_b2b', true);
	    		}
    		}
    	}
		
		return $val;
	}

	function b2bking_stock_filter_stock_status($val, $data){
		$stocktreatment = get_option( 'b2bking_different_stock_treatment_b2b_setting', 'disabled' );
		// Get current user
		$user_id = get_current_user_id();

    	$account_type = get_user_meta($user_id,'b2bking_account_type', true);
    	if ($account_type === 'subaccount'){
    		// for all intents and purposes set current user as the subaccount parent
    		$parent_user_id = get_user_meta($user_id, 'b2bking_account_parent', true);
    		$user_id = $parent_user_id;
    	}
    	$is_b2b = get_user_meta($user_id,'b2bking_b2buser', true);

    	if ($is_b2b === 'yes'){
    		if ($stocktreatment === 'b2b'){
    			// if manage stock is not enabled
    			if (!$data->get_manage_stock()){
	    			// if current user is b2b
	    			$product_id = $data->get_id();
	    			$val = get_post_meta($product_id,'_stock_status_b2b', true);
	    		}

	    		// if managing stock, and b2b backorders enabled, force enable stock status
	    		if ($data->get_manage_stock()){
	    			if ($data->get_backorders() !== 'no'){
	    				return 'instock';
	    			}
	    		}

	    		if ($data->get_manage_stock() && intval($data->get_stock_quantity()) === 0){
	    			if ($data->get_backorders() === 'no'){
	    				return 'outofstock';
	    			}
	    		}
	    	}
    	}

		return $val;
	}

		function b2bking_variable_stock_filter_backorders($val, $data){
			$stocktreatment = get_option( 'b2bking_different_stock_treatment_b2b_setting', 'disabled' );
			// Get current user
			$user_id = get_current_user_id();

	    	$account_type = get_user_meta($user_id,'b2bking_account_type', true);
	    	if ($account_type === 'subaccount'){
	    		// for all intents and purposes set current user as the subaccount parent
	    		$parent_user_id = get_user_meta($user_id, 'b2bking_account_parent', true);
	    		$user_id = $parent_user_id;
	    	}
	    	$is_b2b = get_user_meta($user_id,'b2bking_b2buser', true);

	    	if ($is_b2b === 'yes'){
	    		if ($stocktreatment === 'b2b'){
		    		// if current user is b2b
		    		$product_id = $data->get_id();
		    		$val = get_post_meta($product_id,'variable_backorders_b2b_'.$product_id, true);
	    		}
	    	}
			
			return $val;
		}

		function b2bking_variable_stock_filter_stock_quantity($val, $data){
			$stocktreatment = get_option( 'b2bking_different_stock_treatment_b2b_setting', 'disabled' );
			// Get current user
			$user_id = get_current_user_id();

	    	$account_type = get_user_meta($user_id,'b2bking_account_type', true);
	    	if ($account_type === 'subaccount'){
	    		// for all intents and purposes set current user as the subaccount parent
	    		$parent_user_id = get_user_meta($user_id, 'b2bking_account_parent', true);
	    		$user_id = $parent_user_id;
	    	}
	    	$is_b2b = get_user_meta($user_id,'b2bking_b2buser', true);

	    	if ($is_b2b === 'yes'){
	    		if ($stocktreatment === 'b2b'){
		    		// if current user is b2b
		    		$product_id = $data->get_id();

		    		// if not managed stock, need to get the overall product value
	    			if ($data->get_manage_stock() === true){
	    				$separate_stock_b2b = get_post_meta($product_id,'variable_separate_stock_'.$product_id, true);	
	    				if ($separate_stock_b2b === 'yes'){
	    					$val = get_post_meta($product_id,'variable_stock_b2b_'.$product_id, true);
	    				}

	    			} else if ($data->get_manage_stock() === 'parent') {
	    				$parent_id = wp_get_post_parent_id($product_id);

	    				$separate_stock_quantities_b2b = get_post_meta($parent_id,'_separate_stock_quantities_b2b', true);
	    				if ($separate_stock_quantities_b2b === 'yes'){
	    					$val = get_post_meta($parent_id,'_stock_b2b', true);
	    				} else {

	    				}
	    			}

		    		
	    		}
	    	}
			
			return $val;
		}

		function b2bking_variable_stock_filter_stock_status($val, $data){
			$stocktreatment = get_option( 'b2bking_different_stock_treatment_b2b_setting', 'disabled' );
			// Get current user
			$user_id = get_current_user_id();

	    	$account_type = get_user_meta($user_id,'b2bking_account_type', true);
	    	if ($account_type === 'subaccount'){
	    		// for all intents and purposes set current user as the subaccount parent
	    		$parent_user_id = get_user_meta($user_id, 'b2bking_account_parent', true);
	    		$user_id = $parent_user_id;
	    	}
	    	$is_b2b = get_user_meta($user_id,'b2bking_b2buser', true);

	    	if ($is_b2b === 'yes'){
	    		if ($stocktreatment === 'b2b'){
	    			// if manage stock is not enabled
	    			if (!$data->get_manage_stock()){
		    			// if current user is b2b
		    			$product_id = $data->get_id();
		    			$val = get_post_meta($product_id,'_stock_status_b2b', true);
		    		}

		    		// if managing stock, and b2b backorders enabled, force enable stock status
		    		if ($data->get_manage_stock()){
		    			if ($data->get_backorders() !== 'no'){
		    				return 'instock';
		    			}
		    		}

		    		if (intval($data->get_stock_quantity()) === 0){
		    			if ($data->get_backorders() === 'no'){
		    				return 'outofstock';
		    			}
		    		}
		    	}
	    	}

			return $val;
		}

	function filter_get_stock_status_callback( $stock_status, $product ){
	    return is_admin() ? $stock_status : 'instock';
	}
	function filter_get_backorders_callback( $backorders_status, $product ){
	    return 'yes'; // Enable without notifications
	}

	function b2bking_simple_product_stock_status_change(){
		$stocktreatment = get_option( 'b2bking_different_stock_treatment_b2b_setting', 'disabled' );

		if ($stocktreatment === 'b2b'){

			global $post;
			$product_id = $post->ID;
			$stock_status_b2b_value = get_post_meta($product_id,'_stock_status_b2b', true);
			$stock_quantity_b2b_value = get_post_meta($product_id,'_stock_b2b', true);
			$backorders_b2b_value = get_post_meta($product_id,'_backorders_b2b', true);
			$low_stock_amount_b2b_value = get_post_meta($product_id,'_low_stock_amount_b2b', true);
			$separate_stock_quantities_b2b = get_post_meta($product_id,'_separate_stock_quantities_b2b', true);


			if ( 'yes' === get_option( 'woocommerce_manage_stock' ) ) {

				// horizontal line
				echo '<div class="options_group"></div>';

				echo '<div class="stock_fields show_if_simple show_if_variable">';

				woocommerce_wp_select(
					array(
						'id'          => '_separate_stock_quantities_b2b',
						'value'       => $separate_stock_quantities_b2b,
						'label'       => esc_html__( 'Separate B2B stock?', 'b2bking' ),
						'options'     => array('yes' => esc_html__('Yes','b2bking'), 'no' => esc_html__('No','b2bking') ),
						'desc_tip'    => true,
						'description' => esc_html__( 'If set to "yes", B2B stock quantity is entirely separated. If set to "no", the same stock quantity is used, but you can treat backorders differently.', 'b2bking' ),
					)
				);

				woocommerce_wp_text_input(
					array(
						'id'                => '_stock_b2b',
						'value'             => wc_stock_amount($stock_quantity_b2b_value),
						'label'             => esc_html__( 'B2B Stock quantity', 'b2bking' ),
						'desc_tip'          => true,
						'description'       => __( 'Stock quantity. If this is a variable product this value will be used to control stock for all variations, unless you define stock at variation level.', 'woocommerce' ),
						'type'              => 'number',
						'custom_attributes' => array(
							'step' => 'any',
						),
					)
				);

				echo '<input type="hidden" name="stock_b2b_original" value="' . esc_attr( wc_stock_amount( $stock_quantity_b2b_value ) ) . '" />';


				woocommerce_wp_select(
					array(
						'id'          => '_backorders_b2b',
						'value'       => $backorders_b2b_value,
						'label'       => esc_html__( 'B2B Allow backorders?', 'b2bking' ),
						'options'     => wc_get_product_backorder_options(),
						'desc_tip'    => true,
						'description' => __( 'If managing stock, this controls whether or not backorders are allowed. If enabled, stock quantity can go below 0.', 'woocommerce' ),
					)
				);
			/*
				woocommerce_wp_text_input(
					array(
						'id'                => '_low_stock_amount_b2b',
						'value'             => $low_stock_amount_b2b_value,
						'placeholder'       => sprintf(
							esc_attr__( 'Store-wide threshold (%d)', 'woocommerce' ),
							esc_attr( get_option( 'woocommerce_notify_low_stock_amount' ) )
						),
						'label'             => esc_html__( 'B2B Low stock threshold', 'b2bking' ),
						'desc_tip'          => true,
						'description'       => __( 'When product stock reaches this amount you will be notified by email. It is possible to define different values for each variation individually. The shop default value can be set in Settings > Products > Inventory.', 'woocommerce' ),
						'type'              => 'number',
						'custom_attributes' => array(
							'step' => 'any',
						),
					)
				);
			*/
				echo '</div>';
			}


			woocommerce_wp_select(
				array(
					'id'            => '_stock_status_b2b',
					'value'         => $stock_status_b2b_value,
					'wrapper_class' => 'stock_status_field hide_if_variable hide_if_external hide_if_grouped',
					'label'         => esc_html__( 'B2B Stock status', 'b2bking' ),
					'options'       => wc_get_product_stock_status_options(),
					'desc_tip'      => true,
					'description'   => esc_html__( 'Controls stock status for all B2B users.', 'b2bking' ),
				)
			);
		}

		if ($stocktreatment === 'group'){
			
		}
	}

	function b2bking_variable_product_stock_status_change( $loop, $variation_data, $variation ){

		$stocktreatment = get_option( 'b2bking_different_stock_treatment_b2b_setting', 'disabled' );

		if ($stocktreatment === 'b2b'){

			$stock_quantity_b2b_value = get_post_meta($variation->ID,'variable_stock_b2b_'.$variation->ID, true);
			$backorders_b2b_value = get_post_meta($variation->ID,'variable_backorders_b2b_'.$variation->ID, true);
			$separate_stock_b2b = get_post_meta($variation->ID,'variable_separate_stock_'.$variation->ID, true);
			if ($separate_stock_b2b === 'no'){
				$class = 'b2bking_hidden_wrapper';
			} else {
				$style = '';
			}

			woocommerce_wp_select(
				array(
					'id'            => 'variable_separate_stock_'.$variation->ID,
					'name'          => 'variable_separate_stock_'.$variation->ID,
					'value'         => $separate_stock_b2b,
					'label'         => esc_html__( 'Separate B2B stock?', 'b2bking' ),
					'options'       => array('yes' => esc_html__('Yes','b2bking'), 'no' => esc_html__('No','b2bking') ),
					'desc_tip'      => true,
					'description'   => esc_html__( 'If set to "yes", B2B stock quantity is entirely separated. If set to "no", the same stock quantity is used, but you can treat backorders differently.', 'b2bking' ),
					'wrapper_class' => 'form-row form-row-first b2bking_separate_stock',
				)
			);

			woocommerce_wp_text_input(
				array(
					'id'                => 'variable_stock_b2b_'.$variation->ID,
					'name'              => 'variable_stock_b2b_'.$variation->ID,
					'value'             => wc_stock_amount( $stock_quantity_b2b_value ),
					'label'             => esc_html__( 'B2B Stock quantity', 'b2bking' ),
					'desc_tip'          => true,
					'description'       => __( "Enter a number to set stock quantity at the variation level. Use a variation's 'Manage stock?' check box above to enable/disable stock management at the variation level.", 'woocommerce' ),
					'type'              => 'number',
					'custom_attributes' => array(
						'step' => 'any',
					),
					'data_type'         => 'stock',
					'wrapper_class'     => 'form-row form-row-last '.$class,
				)
			);

			echo '<input type="hidden" name="'.'variable_stock_b2b_original_'.$variation->ID.'" value="' . esc_attr( wc_stock_amount( $stock_quantity_b2b_value ) ) . '" />';

			?>
			<?php
			woocommerce_wp_select(
				array(
					'id'            => 'variable_backorders_b2b_'.$variation->ID,
					'name'          => 'variable_backorders_b2b_'.$variation->ID,
					'value'         => $backorders_b2b_value,
					'label'         => esc_html__( 'B2B Allow backorders?', 'b2bking' ),
					'options'       => wc_get_product_backorder_options(),
					'desc_tip'      => true,
					'description'   => __( 'If managing stock, this controls whether or not backorders are allowed. If enabled, stock quantity can go below 0.', 'woocommerce' ),
					'wrapper_class' => 'form-row b2bking_variable_backorders',
				)
			);
		}

		if ($stocktreatment === 'group'){
			
		}
	}

	function b2bking_variable_product_stock_save( $post_id ){

		if (isset($_POST['_inline_edit'])){
			return;
		}

		if (isset($_REQUEST['bulk_edit'])){
		    return;
		}

		if (isset($_POST['variable_stock_b2b_'.$post_id])) {
			$number_field = sanitize_text_field($_POST['variable_stock_b2b_'.$post_id]);

		    $original_b2b_value = sanitize_text_field($_POST['variable_stock_b2b_original_'.$post_id]);

			// check original versus current value. If original is not same as current value, fail (purchases have occured in the meantime)
			$current_value = get_post_meta($post_id,'variable_stock_b2b_'.$post_id, true);

			if (empty($original_b2b_value)){
				$original_b2b_value = $current_value;
			}
			if (intval($current_value) === intval($original_b2b_value)){
				update_post_meta($post_id, 'variable_stock_b2b_'.$post_id, esc_attr($number_field));

			} else {
				// stock has changed, fail
				WC_Admin_Meta_Boxes::add_error( esc_html__( 'The B2B stock has not been updated because the value has changed since editing.', 'b2bking' ) ) ;
			}
		}

		if (isset($_POST['variable_backorders_b2b_'.$post_id])) {
			$number_field = sanitize_text_field($_POST['variable_backorders_b2b_'.$post_id]);
		    update_post_meta($post_id, 'variable_backorders_b2b_'.$post_id, esc_attr($number_field));
		}

		if (isset($_POST['variable_separate_stock_'.$post_id])) {
			$number_field = sanitize_text_field($_POST['variable_separate_stock_'.$post_id]);
		    update_post_meta($post_id, 'variable_separate_stock_'.$post_id, esc_attr($number_field));
		}		


	}

	function b2bking_save_stock_settings($post_id){

		if (isset($_POST['_inline_edit'])){
			return;
		}
		if (isset($_REQUEST['bulk_edit'])){
		    return;
		}
		if (is_a($post_id,'WC_Product') || is_a($post_id,'WC_Product_Variation')){
			$post_id = $post_id->get_id();
		}
			
		$postobj = get_post($post_id);
		if ( $postobj->post_status === 'trash' ) {
	        return;
	    }
	    if (isset($_GET['action'])) {
	    	if ($_GET['action'] === 'untrash'){
	    		return;
	    	}
	    }

		if (isset($_POST['_stock_status_b2b'])){
			$stock_status_b2b = sanitize_text_field($_POST['_stock_status_b2b']);
			update_post_meta($post_id,'_stock_status_b2b', $stock_status_b2b);
		}
		if (isset($_POST['_stock_b2b'])){
			$stock_quantity_b2b_value = sanitize_text_field($_POST['_stock_b2b']);
			$original_b2b_value = sanitize_text_field($_POST['stock_b2b_original']);

			// check original versus current value. If original is not same as current value, fail (purchases have occured in the meantime)
			$current_value = get_post_meta($post_id,'_stock_b2b', true);

			if (empty($original_b2b_value)){
				$original_b2b_value = $current_value;
			}

			if (intval($current_value) === intval($original_b2b_value)){
				update_post_meta($post_id,'_stock_b2b', $stock_quantity_b2b_value);
			} else {
				// stock has changed, fail
				WC_Admin_Meta_Boxes::add_error( esc_html__( 'The B2B stock has not been updated because the value has changed since editing.', 'b2bking' ) ) ;
			}
		}
		if (isset($_POST['_backorders_b2b'])){
			$backorders_b2b_value = sanitize_text_field($_POST['_backorders_b2b']);
			update_post_meta($post_id,'_backorders_b2b', $backorders_b2b_value);
		}
		if (isset($_POST['_low_stock_amount_b2b'])){
			$low_stock_amount_b2b_value = sanitize_text_field($_POST['_low_stock_amount_b2b']);
			update_post_meta($post_id,'_low_stock_amount_b2b', $low_stock_amount_b2b_value);
		}
		if (isset($_POST['_separate_stock_quantities_b2b'])){
			$separate_stock_quantities_b2b = sanitize_text_field($_POST['_separate_stock_quantities_b2b']);
			update_post_meta($post_id,'_separate_stock_quantities_b2b', $separate_stock_quantities_b2b);
		}

	}

	function get_visibility_set_transient_live(){

			if (intval(get_option( 'b2bking_all_products_visible_all_users_setting', 1 )) !== 1){

				if ( get_option( 'b2bking_plugin_status_setting', 'b2b' ) !== 'disabled' ){

					$user_is_b2b = get_user_meta( get_current_user_id(), 'b2bking_b2buser', true );

					// if user logged in and is b2b
					if (is_user_logged_in() && ($user_is_b2b === 'yes')){
						// Get current user's data: group, id, login, etc
					    $currentuserid = get_current_user_id();
				    	$account_type = get_user_meta($currentuserid,'b2bking_account_type', true);
				    	if ($account_type === 'subaccount'){
				    		// for all intents and purposes set current user as the subaccount parent
				    		$parent_user_id = get_user_meta($currentuserid, 'b2bking_account_parent', true);
				    		$currentuserid = $parent_user_id;
				    	}
				        $currentuser = get_user_by('id', $currentuserid);
						$currentuserlogin = $currentuser -> user_login;
						$currentusergroupidnr = b2bking()->get_user_group($currentuserid);
					// if user is b2c
					} else if (is_user_logged_in() && ($user_is_b2b !== 'yes')){
						$currentuserlogin = 'b2c';
						$currentusergroupidnr = 'b2c';
					} else {
						$currentuserlogin = 0;
						$currentusergroupidnr = 0;
					}
					/*
					* 
					*	There are 2 separate queries that need to be made:
					* 	1. Query of all Categories visible to the USER AND all Categories visible to the USER'S GROUP 
					*	2. Query of all Products set to Manual visibility mode, visible to the user or the user's group 
					*
					*/

					// Build Visible Categories for the 1st Query
					$visiblecategories = array();
					$hiddencategories = array();

					$terms = get_terms( array( 
					    'taxonomy' => 'product_cat',
					    'fields' => 'ids',
					    'hide_empty' => false
					) );

					foreach ($terms as $term){

						/* 
						* If category is visible to GROUP OR category is visible to USER
						* Push category into visible categories array
						*/

						// first check group
						$group_meta = get_term_meta( $term, 'b2bking_group_'.$currentusergroupidnr, true );
						if (intval($group_meta) === 1){
							array_push($visiblecategories, $term);
						// else check user
						} else {
							$userlistcommas = get_term_meta( $term, 'b2bking_category_users_textarea', true );
							$userarray = explode(',', $userlistcommas);
							foreach ($userarray as $user){
								if (trim($user) === $currentuserlogin){
									array_push($visiblecategories, $term);
									break;
								}
							}
							// has reached this point, therefore category is not visible
							array_push($hiddencategories, $term);
						}
					}

					$product_category_visibility_array = array(
					           'taxonomy' => 'product_cat',
					           'field' => 'term_id',
					           'terms' => $visiblecategories, 
					           'operator' => 'IN'
					);

					// if user has enabled "hidden has priority", override setting
					if (intval(get_option( 'b2bking_hidden_has_priority_setting', 0 )) === 1){
						$product_category_visibility_array = array(
						           'taxonomy' => 'product_cat',
						           'field' => 'term_id',
						           'terms' => $hiddencategories, 
						           'operator' => 'NOT IN'
						);
					}

					/* Get all items that do not have manual visibility set up */
					// get all products ids
					if (intval(get_option( 'b2bking_product_visibility_cache_setting', 0 )) === 1){
						if (!defined('ICL_LANGUAGE_NAME_EN')){
							$items_not_manual_visibility_array = get_transient('b2bking_not_manual_visibility_array');
						} else {
							$items_not_manual_visibility_array = get_transient('b2bking_not_manual_visibility_array'.ICL_LANGUAGE_NAME_EN);
						}
					} else {
						$items_not_manual_visibility_array = false;
					}
					
					if (!$items_not_manual_visibility_array){
						$all_prods = new WP_Query(array(
					        'posts_per_page' => -1,
					        'post_type' => 'product',
					        'fields' => 'ids'));
						$all_prod_ids = $all_prods->posts;

						// get all products with manual visibility ids
						$all_prods_manual = new WP_Query(array(
					        'posts_per_page' => -1,
					        'post_type' => 'product',
					        'fields' => 'ids',
		    		        'meta_query'=> array(
		                            'relation' => 'AND',
		                            array(
		                                'key' => 'b2bking_product_visibility_override',
		                                'value' => 'manual',
		                            )
		                        )));
						$all_prod_manual_ids = $all_prods_manual->posts;
						// get the difference
						$items_not_manual_visibility_array = array_diff($all_prod_ids,$all_prod_manual_ids);
						if (!defined('ICL_LANGUAGE_NAME_EN')){
							set_transient('b2bking_not_manual_visibility_array', $items_not_manual_visibility_array);
						} else {
							set_transient('b2bking_not_manual_visibility_array'.ICL_LANGUAGE_NAME_EN, $items_not_manual_visibility_array);

						}
					}

					if (empty($items_not_manual_visibility_array)){
						$items_not_manual_visibility_array = array('invalid');
					}

					// Build first query
				    $queryAparams = array(
				        'posts_per_page' => -1,
				        'post_type' => 'product',
				        'fields' => 'ids',
				        'tax_query' => array(
				        	$product_category_visibility_array
				        ),
					    'post__in' => $items_not_manual_visibility_array,
					);

				    // Build 2nd query: all manual visibility products with USER OR USER GROUP visibility
				    $queryBparams = array(
				        'posts_per_page' => -1,
				        'post_type' => 'product',
				        'fields' => 'ids',
				        'meta_query'=> array(
		                        'relation' => 'AND',
		                        array(
		                            'relation' => 'OR',
		                            array(
		                                'key' => 'b2bking_group_'.$currentusergroupidnr,
		                                'value' => '1'
		                            ),
		                            array(
		                                'key' => 'b2bking_user_'.$currentuserlogin,
		                                'value' => '1'
		                            )
		                        ),
		                        array(
		                            'key' => 'b2bking_product_visibility_override',
		                            'value' => 'manual',
		                        )
		                    ));

				    $queryA = new WP_Query($queryAparams);
				    $queryB = new WP_Query($queryBparams);

				    // Merge the 2 queries in an IDs array
				    $allTheIDs = array_merge($queryA->posts,$queryB->posts);

				    				    // put variations in here as well
    				$allvariationids = new WP_Query(array(
    			        'posts_per_page' => -1,
    			        'post_type' => 'product_variation',
    			        'post_status' => 'publish',
    			        'fields' => 'ids',
        		        'post_parent__in' => $allTheIDs
        		    ));
    				$allTheIDs = array_merge($allTheIDs,$allvariationids->posts);
				    
				    set_transient('b2bking_user_'.get_current_user_id().'_ajax_visibility_live', $allTheIDs);
				}
			}
		
	}

}

