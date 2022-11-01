<?php

class B2bking_Globalhelper{

	private static $instance = null;

	public static function init() {
	    if ( self::$instance === null ) {
	        self::$instance = new self();
	    }

	    return self::$instance;
	}

	public static function tofloat($num) {
	    $dotPos = strrpos($num, '.');
	    $commaPos = strrpos($num, ',');

	    if ($dotPos === false && $commaPos === false){
	    	// if number doesnt have either dot or comma, return number
	    	return $num;
	    }
	    $sep = (($dotPos > $commaPos) && $dotPos) ? $dotPos :
	        ((($commaPos > $dotPos) && $commaPos) ? $commaPos : false);
	  
	    if (!$sep) {
	        return floatval(preg_replace("/[^0-9]/", "", $num));
	    }

	    $decimals = apply_filters('b2bking_rounding_precision', get_option('woocommerce_price_num_decimals', 2));

	    return round(floatval(
	        preg_replace("/[^0-9]/", "", substr($num, 0, $sep)) . '.' .
	        preg_replace("/[^0-9]/", "", substr($num, $sep+1, strlen($num)))
	    ), $decimals);
	}

	public static function get_user_group($user_id = 0){
		if ($user_id === 0){
			$user_id = get_current_user_id();
		}

		$meta_key = apply_filters('b2bking_group_key_name', 'b2bking_customergroup');

		$group = get_user_meta($user_id, $meta_key, true);
		return $group;
	}

	public static function update_user_group($user_id, $value){

		$meta_key = apply_filters('b2bking_group_key_name', 'b2bking_customergroup');

		update_user_meta($user_id, $meta_key, $value);
	}

	public static function custom_modulo($nr1, $nr2){
		$evenlyDivisable = abs(($nr1 / $nr2) - round($nr1 / $nr2, 0)) < 0.00001;

		if ($evenlyDivisable){
			// number has no decimals, therefore remainder is 0
			return 0;
		} else {
			return 1;
		}
	}

	public static function b2bking_wc_get_price_to_display( $product, $args = array() ) {

		if (is_a($product,'WC_Product_Variation') || is_a($product,'WC_Product')){

			// Modify WC function to consider user's vat exempt status
			global $woocommerce;
			$customertest = $woocommerce->customer;

			if (is_a($customertest, 'WC_Customer')){
				$customer = WC()->customer;
				$vat_exempt = $customer->is_vat_exempt();
			} else {
				$vat_exempt = false;
			}
		    $args = wp_parse_args(
		        $args,
		        array(
		            'qty'   => 1,
		            'price' => $product->get_price(),
		        )
		    );

		    $price = $args['price'];
		    $qty   = $args['qty'];

		    if (is_cart() || is_checkout()){
		    	if ( 'incl' === get_option( 'woocommerce_tax_display_cart' ) && !$vat_exempt ){
		    		return 
		    	    wc_get_price_including_tax(
		    	        $product,
		    	        array(
		    	            'qty'   => $qty,
		    	            'price' => $price,
		    	        )
		    	    );
		    	} else {
		    		return
		    	    wc_get_price_excluding_tax(
		    	        $product,
		    	        array(
		    	            'qty'   => $qty,
		    	            'price' => $price,
		    	        )
		    	    );
		    	}
		    } else {
		    	//shop
		    	if ( 'incl' === get_option( 'woocommerce_tax_display_shop' ) && !$vat_exempt ){
		    		return 
		    	    wc_get_price_including_tax(
		    	        $product,
		    	        array(
		    	            'qty'   => $qty,
		    	            'price' => $price,
		    	        )
		    	    );
		    	} else {
		    		return
		    	    wc_get_price_excluding_tax(
		    	        $product,
		    	        array(
		    	            'qty'   => $qty,
		    	            'price' => $price,
		    	        )
		    	    );
		    	}
		    }
		} else {
			return 0;
		}
	    
	}

	public static function get_woocs_price( $price ) {

		if (class_exists('WOOCS')) {
			global $WOOCS;
			$currrent = $WOOCS->current_currency;
			if ($currrent != $WOOCS->default_currency) {
				$currencies = $WOOCS->get_currencies();
				$rate = $currencies[$currrent]['rate'];
				$price = $price * $rate;
			}
		}

		// WPML integration
		$current_currency = apply_filters('wcml_price_currency', NULL );
		if ($current_currency !== NULL){
			$price = apply_filters( 'wcml_raw_price_amount', $price, $current_currency );
		}



		return $price;
		
	}

	public static function is_rest_api_request() {
	    if ( empty( $_SERVER['REQUEST_URI'] ) ) {
	        // Probably a CLI request
	        return false;
	    }

	    $rest_prefix         = trailingslashit( rest_get_url_prefix() );
	    $is_rest_api_request = strpos( $_SERVER['REQUEST_URI'], $rest_prefix ) !== false;

	    if (defined('REST_REQUEST')){
	    	$is_rest_api_request = true;
	    }

	    return apply_filters( 'is_rest_api_request', $is_rest_api_request );
	}

	// returns an array of all categories including all parent categories of subcategories a product belongs to
	public static function get_all_product_categories($product_id){

		// initialize variable
		global $b2bking_all_categories;
		if (!is_array($b2bking_all_categories)){
			$b2bking_all_categories = array();

			// we are at the beginning of the execution, the categories global is empty, let's merge it with the cached categories global
			$b2bking_cached_categories = get_transient('b2bking_cached_categories');
			if (is_array($b2bking_cached_categories)){
				// if cached categories exist
				$b2bking_all_categories = $b2bking_cached_categories;
			}
		}

		if (isset($b2bking_all_categories[$product_id])){
			// skip
		} else {
			$b2bking_all_categories[$product_id] = $direct_categories = wc_get_product_term_ids($product_id, 'product_cat');

			// set via code snippets that rule apply to the direct categories only (And not apply to parent/sub categories)
			if (apply_filters('b2bking_apply_rules_to_direct_categories_only', false)){
				return $b2bking_all_categories[$product_id];
			}

			foreach ($direct_categories as $directcat){
				// find all parents
				$term = get_term($directcat, 'product_cat');
				while ($term->parent !== 0){
					array_push($b2bking_all_categories[$product_id], $term->parent);
					$term = get_term($term->parent, 'product_cat');
				}
			}
			$b2bking_all_categories[$product_id] = array_filter(array_unique($b2bking_all_categories[$product_id]));
		}

		return $b2bking_all_categories[$product_id];

		
	}

	public static function b2bking_has_category( $category_id, $taxonomy, $product_id ) {

		// initialize variable
		global $b2bking_all_categories;
		if (!is_array($b2bking_all_categories)){
			$b2bking_all_categories = array();
		}
	 	
	 	if (isset($b2bking_all_categories[$product_id])){
	 		// we already have all categories for the product
	 	} else {
	 		// determine all categories for the product
	 		$b2bking_all_categories[$product_id] = $direct_categories = wc_get_product_term_ids($product_id, 'product_cat');

	 		// set via code snippets that rule apply to the direct categories only (And not apply to parent/sub categories)
	 		if (apply_filters('b2bking_apply_rules_to_direct_categories_only', false)){
	 			// skip
	 		} else {
	 			// continue here
	 			foreach ($direct_categories as $directcat){
	 				// find all parents
	 				$term = get_term($directcat, 'product_cat');
	 				while ($term->parent !== 0){
	 					array_push($b2bking_all_categories[$product_id], $term->parent);
	 					$term = get_term($term->parent, 'product_cat');
	 				}
	 			}

	 			$b2bking_all_categories[$product_id] = array_filter(array_unique($b2bking_all_categories[$product_id]));

	 		}
	 	}

	    if (in_array($category_id, $b2bking_all_categories[$product_id])){
	    	return true;
	    }

		return false;
	}

	public static function is_side_cart(){
		$side_cart = false;

		global $b2bking_is_mini_cart; 

		if ($b2bking_is_mini_cart === true){
			$side_cart = true;
		}


		return $side_cart;
	}

	public static function clear_caches_transients(){
		// set that rules have changed so that pricing cache can be updated
		update_option('b2bking_commission_rules_have_changed', 'yes');
		update_option('b2bking_dynamic_rules_have_changed', 'yes');

		// delete all b2bking transients
		global $wpdb;
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '%transient_b2bking%'" );

		wp_cache_flush();

		// force permalinks
		update_option('b2bking_force_permalinks_flushing_setting', 1);

		delete_transient('webwizards_dashboard_data_cache');
		delete_transient('webwizards_dashboard_data_cache_time');
	}

	// get all rules by user
	// returns array of rule IDs
	public static function get_all_rules($rule_type = 'all', $user_id = 'current'){

		if ($user_id === 'current'){
			$user_id = get_current_user_id();
		}

		$account_type = get_user_meta($user_id,'b2bking_account_type', true);
		if ($account_type === 'subaccount'){
			// for all intents and purposes set current user as the subaccount parent
			$parent_user_id = get_user_meta($user_id, 'b2bking_account_parent', true);
			$user_id = $parent_user_id;
		}

		$currentusergroupidnr = b2bking()->get_user_group($user_id);
		if (!$currentusergroupidnr || empty($currentusergroupidnr)){
			$currentusergroupidnr = 'invalid';
		}

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
			} else {
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
			} else {
				array_push($array_who, array(
							'key' => 'b2bking_rule_who',
							'value' => 'everyone_registered_b2c'
						));
			}

		}

		$rules = get_posts([
			'post_type' => 'b2bking_rule',
			'post_status' => 'publish',
			'numberposts' => -1,
			'fields' 	  => 'ids',
			'meta_query'=> array(
				'relation' => 'AND',
				$array_who,
			)
		]);


		if ($rule_type !== 'all'){
			// remove rules that don't match rule type.
			foreach ($rules as $index=>$rule){
				$type = get_post_meta($rule,'b2bking_rule_what', true);
				if ($rule_type !== $type){
					unset($rules[$index]);
				}
			}
		}

		return $rules;


	}

	/*
	When dealing with a large number of products and / or a large number of dynamic rules,
	We can see situations where the plugin calls for thousands of transients, which affects load times
	It's better for load times to call a single large transient that contains all this info
	We get such a transient and we set it as a global function / data for quicker access
	*/

	public static function get_global_data($requested = false, $product_id = false, $user_id = false){
		//'b2bking_'.$rule_type.'_rules_apply_'.$current_product_id
		global $b2bking_data;

		if (!is_array($b2bking_data)){
			$b2bking_data = array();
		}

		// if data not set, get data from db
		if (empty($b2bking_data)){
			// get it form database
			$b2bking_data = get_transient('b2bking_global_data');
		}

		// Request for all data
		if ($requested === false){
			return $b2bking_data;
		}
		// reached here, it means we have some specific data requested

		// if no specific product id or user id
		if (!$product_id && !$user_id){
			// requested could be 'b2bking_discount_everywhere_rules_apply' for example
			$requested_value = isset($b2bking_data[$requested]) ? $b2bking_data[$requested] : false;
		}

		// if product id set, but not user id
		if ($product_id && !$user_id){
			$requested_value = isset($b2bking_data[$requested][$product_id]) ? $b2bking_data[$requested][$product_id] : false;
		}

		// if user id set, but not product id
		if (!$product_id && $user_id){
			$requested_value = isset($b2bking_data[$requested][$user_id]) ? $b2bking_data[$requested][$user_id] : false;
		}

		// both product and user id are set
		if ($product_id && $user_id){
			$requested_value = isset($b2bking_data[$requested][$product_id][$user_id]) ? $b2bking_data[$requested][$product_id][$user_id] : false;
		}

		if (isset($requested_value)){
			return $requested_value;
		} else {
			return false;
		}
		
	}

	public static function set_global_data($requested = false, $value = false, $product_id = false, $user_id = false){
		global $b2bking_data;

		// if global not set, get it first
		if (empty($b2bking_data)){
			$b2bking_data = b2bking()->get_global_data();
		}

		if (!$product_id && !$user_id){
			// requested could be 'b2bking_discount_everywhere_rules_apply' for example
			$b2bking_data[$requested] = $value;
		}

		// if product id set, but not user id
		if ($product_id && !$user_id){

			// prevent assignment errors
			if (isset($b2bking_data[$requested])){
				if (is_string($b2bking_data[$requested])){
					$b2bking_data[$requested] = array();
				}
			}
			
			
			$b2bking_data[$requested][$product_id] = $value;
		}

		// if user id set, but not product id
		if (!$product_id && $user_id){

			// prevent assignment errors
			if (isset($b2bking_data[$requested])){
				if (is_string($b2bking_data[$requested])){
					$b2bking_data[$requested] = array();
				}
			}

			$b2bking_data[$requested][$user_id] = $value;
		}

		// both product and user id are set
		if ($product_id && $user_id){

			// prevent assignment errors
			if (isset($b2bking_data[$requested])){
				if (is_string($b2bking_data[$requested])){
					$b2bking_data[$requested] = array();
				}
			}
			if (isset($b2bking_data[$requested][$product_id])){
				if (is_string($b2bking_data[$requested][$product_id])){
					$b2bking_data[$requested][$product_id] = array();
				}
			}

			$b2bking_data[$requested][$product_id][$user_id] = $value;
		}

		// finally, update database
		set_transient('b2bking_global_data', $b2bking_data);

	}




	// Function that gets which rules apply for the user /& product
	// Must be fast and efficient - used in dynamic rules
	//
	// This function sets transients, does not actually retrieve rules
	public static function get_applicable_rules($rule_type, $current_product_id = 0, $user_id = 0){

		$currentusergroupidnr = b2bking()->get_user_group($user_id);
		if (!$currentusergroupidnr || empty($currentusergroupidnr)){
			$currentusergroupidnr = 'invalid';
		}

		// 1. Get list of all fixed price rules applicable to the user
		// $user_applicable_rules = get_transient('b2bking_'.$rule_type.'_user_applicable_rules_'.$user_id);
		$user_applicable_rules = b2bking()->get_global_data('b2bking_'.$rule_type.'_user_applicable_rules',false,$user_id);
		if (!$user_applicable_rules){
			$rules_ids_elements = get_option('b2bking_have_'.$rule_type.'_rules_list_ids_elements', array());

			$user_rules = array();
			if (isset($rules_ids_elements['user_'.$user_id])){
				$user_rules = $rules_ids_elements['user_'.$user_id];
			}

			$group_rules = array();
			if (isset($rules_ids_elements['group_'.$currentusergroupidnr])){
				$group_rules = $rules_ids_elements['group_'.$currentusergroupidnr];
			}

			$user_applicable_rules = array_merge($user_rules, $group_rules);
			if (is_user_logged_in()){

				if (isset($rules_ids_elements['all_registered'])){
					// add everyone_registered rules
					$user_applicable_rules = array_merge($user_applicable_rules, $rules_ids_elements['all_registered']);
				}

				// if is user b2b add b2b rules
				if (get_user_meta($user_id,'b2bking_b2buser', true) === 'yes'){
					if (isset($rules_ids_elements['everyone_registered_b2b'])){
						$user_applicable_rules = array_merge($user_applicable_rules, $rules_ids_elements['everyone_registered_b2b']);
					}
				} else {
					// add b2c rules
					if (isset($rules_ids_elements['everyone_registered_b2c'])){
						$user_applicable_rules = array_merge($user_applicable_rules, $rules_ids_elements['everyone_registered_b2c']);
					}
				}
			}

		//	set_transient('b2bking_'.$rule_type.'_user_applicable_rules_'.$user_id,$user_applicable_rules);
			b2bking()->set_global_data('b2bking_'.$rule_type.'_user_applicable_rules',$user_applicable_rules, false, $user_id);
		}

		// if no applicable user rules, skip
		if (empty($user_applicable_rules)){
			return 'norules';
		}

		/*

		If a small number of user rules, it is fastest to check those specific rules
		But if not small, then calculating product rules makes sense, and since it is general for the product,
		it also helps things load faster for other users

		*/

		$skip_calc_rules_apply_product = 'no';
		if (count($user_applicable_rules) < 50){
			$skip_calc_rules_apply_product = 'yes';
		}


		// 2. If not a small number of user rules, get all fixed price product rules
	//	$rules_that_apply_to_product = get_transient('b2bking_'.$rule_type.'_rules_apply_'.$current_product_id);
		$rules_that_apply_to_product = b2bking()->get_global_data('b2bking_'.$rule_type.'_rules_apply', $current_product_id);

		if (!$rules_that_apply_to_product){

			if ($skip_calc_rules_apply_product === 'no'){

				$rules_that_apply = array();
				$ruletype_rules_option = get_option('b2bking_have_'.$rule_type.'_rules_list_ids', '');
				if (!empty($ruletype_rules_option)){
					$ruletype_rules_v2_ids = explode(',',$ruletype_rules_option);
				} else {
					$ruletype_rules_v2_ids = array();
				}

				foreach ($ruletype_rules_v2_ids as $rule_id){
					$applies = get_post_meta($rule_id,'b2bking_rule_applies', true);
					if ($applies === 'cart_total' || $applies === 'product_'.$current_product_id){
						array_push($rules_that_apply, $rule_id);
					} else if ($applies === 'multiple_options'){
						$multiple_options = get_post_meta($rule_id,'b2bking_rule_applies_multiple_options', true);
						$multiple_options_array = explode(',', $multiple_options);
						if (in_array('product_'.$current_product_id, $multiple_options_array)){
							array_push($rules_that_apply, $rule_id);
						} else {
							// try categories
							$current_product_categories = b2bking()->get_all_product_categories( $current_product_id, 'product_cat' );
							$current_product_belongsto_array = array_map(function($value) { return 'category_'.$value; }, $current_product_categories);

							foreach ($current_product_belongsto_array as $item_category){
								if (in_array($item_category, $multiple_options_array)){
									array_push($rules_that_apply, $rule_id);
									break;
								}
							}
						}
						
					} else if (explode('_', $applies)[0] === 'category'){
						// check category
						$current_product_categories = b2bking()->get_all_product_categories( $current_product_id, 'product_cat' );
						$current_product_belongsto_array = array_map(function($value) { return 'category_'.$value; }, $current_product_categories);
						if (in_array($applies, $current_product_belongsto_array)){
							array_push($rules_that_apply, $rule_id);
						}
					} else if ($applies === 'excluding_multiple_options'){
						// check that current product is not in list
						$multiple_options = get_post_meta($rule_id,'b2bking_rule_applies_multiple_options', true);
						$multiple_options_array = explode(',', $multiple_options);

						$product_is_excluded = 'no';

						$variation_parent_id = wp_get_post_parent_id($current_product_id);
						if (in_array('product_'.$current_product_id, $multiple_options_array) || in_array('product_'.$variation_parent_id, $multiple_options_array)){
							$product_is_excluded = 'yes';
						} else {
							// try categories
							$current_product_categories = b2bking()->get_all_product_categories( $current_product_id, 'product_cat' );
							$parent_product_categories = b2bking()->get_all_product_categories( $variation_parent_id, 'product_cat' );
							$current_product_categories = array_merge($current_product_categories, $parent_product_categories);

							$current_product_belongsto_array = array_map(function($value) { return 'category_'.$value; }, $current_product_categories);

							foreach ($current_product_belongsto_array as $item_category){
								if (in_array($item_category, $multiple_options_array)){
									$product_is_excluded = 'yes';
									break;
								}
							}
						}
						// check if product has any variations that are excluded. If yes, then exclude product too, so variations rule can apply.
						// Get children product variation IDs in an array
						$productobjj = wc_get_product($current_product_id);
						$children_ids = $productobjj->get_children();
						foreach ($children_ids as $child_id){
							if (in_array('product_'.$child_id, $multiple_options_array)){
								$product_is_excluded = 'yes';
								break;
							}
						}

						if ($product_is_excluded === 'no'){
							// product is not excluded, therefore rule applies
							array_push($rules_that_apply, $rule_id);
						}
					}
				}

				// set_transient('b2bking_'.$rule_type.'_rules_apply_'.$current_product_id,$rules_that_apply);
				b2bking()->set_global_data('b2bking_'.$rule_type.'_rules_apply', $rules_that_apply, $current_product_id);

				$rules_that_apply_to_product = $rules_that_apply;
			}
		}

		/* 3. Calculate user applicable rules, by either intersecting product/user rules, OR starting with user rules
		and checking each rule for the product */
		
		//if (!get_transient('b2bking_'.$rule_type.'_'.$current_product_id.'_'.get_current_user_id())){
		if (!b2bking()->get_global_data('b2bking_'.$rule_type, $current_product_id, get_current_user_id())){
			// if we have the info about which rules apply to the product, use it, else calculate
			if ($rules_that_apply_to_product){
				// we have the info, simply intersect product rules with user rules
				$final_rules = array_intersect($rules_that_apply_to_product, $user_applicable_rules);
			} else {
				$final_rules = array();
				// for each user rule, check which rules apply to product
				foreach ($user_applicable_rules as $rule_id){

					$applies = get_post_meta($rule_id,'b2bking_rule_applies', true);

					if ($applies === 'cart_total' || $applies === 'product_'.$current_product_id){
						array_push($final_rules, $rule_id);
					} else if ($applies === 'multiple_options'){
						$multiple_options = get_post_meta($rule_id,'b2bking_rule_applies_multiple_options', true);
						$multiple_options_array = explode(',', $multiple_options);
						if (in_array('product_'.$current_product_id, $multiple_options_array)){
							array_push($final_rules, $rule_id);
						} else {
							// try categories
							$current_product_categories = b2bking()->get_all_product_categories( $current_product_id, 'product_cat' );
							if (empty($current_product_categories)){
								// if no categories, this may be a variation, check parent categories
								$possible_parent_id = wp_get_post_parent_id($current_product_id);
								if ($possible_parent_id !== 0){
									// if product has parent
									$current_product_categories = b2bking()->get_all_product_categories( $possible_parent_id, 'product_cat' );
								}
							}
							$current_product_belongsto_array = array_map(function($value) { return 'category_'.$value; }, $current_product_categories);

							foreach ($current_product_belongsto_array as $item_category){
								if (in_array($item_category, $multiple_options_array)){
									array_push($final_rules, $rule_id);
									break;
								}
							}
						}
						
					} else if (explode('_', $applies)[0] === 'category'){
						// check category
						$current_product_categories = b2bking()->get_all_product_categories( $current_product_id, 'product_cat' );
						if (empty($current_product_categories)){
							// if no categories, this may be a variation, check parent categories
							$possible_parent_id = wp_get_post_parent_id($current_product_id);
							if ($possible_parent_id !== 0){
								// if product has parent
								$current_product_categories = b2bking()->get_all_product_categories( $possible_parent_id, 'product_cat' );
							}
						}
						$current_product_belongsto_array = array_map(function($value) { return 'category_'.$value; }, $current_product_categories);
						if (in_array($applies, $current_product_belongsto_array)){
							array_push($final_rules, $rule_id);
						}
					} else if ($applies === 'excluding_multiple_options'){
						// check that current product is not in list
						$multiple_options = get_post_meta($rule_id,'b2bking_rule_applies_multiple_options', true);
						$multiple_options_array = explode(',', $multiple_options);

						$product_is_excluded = 'no';

						$variation_parent_id = wp_get_post_parent_id($current_product_id);
						if (in_array('product_'.$current_product_id, $multiple_options_array) || in_array('product_'.$variation_parent_id, $multiple_options_array)){
							$product_is_excluded = 'yes';
						} else {
							// try categories
							$current_product_categories = b2bking()->get_all_product_categories( $current_product_id, 'product_cat' );
							$parent_product_categories = b2bking()->get_all_product_categories( $variation_parent_id, 'product_cat' );
							$current_product_categories = array_merge($current_product_categories, $parent_product_categories);

							$current_product_belongsto_array = array_map(function($value) { return 'category_'.$value; }, $current_product_categories);

							foreach ($current_product_belongsto_array as $item_category){
								if (in_array($item_category, $multiple_options_array)){
									$product_is_excluded = 'yes';
									break;
								}
							}
						}
						// check if product has any variations that are excluded. If yes, then exclude product too, so variations rule can apply.
						// Get children product variation IDs in an array
						$productobjj = wc_get_product($current_product_id);
						$children_ids = $productobjj->get_children();
						foreach ($children_ids as $child_id){
							if (in_array('product_'.$child_id, $multiple_options_array)){
								$product_is_excluded = 'yes';
								break;
							}
						}
						if ($product_is_excluded === 'no'){
							// product is not excluded, therefore rule applies
							array_push($final_rules, $rule_id);
						}
					}

				}
			}

			//set_transient('b2bking_'.$rule_type.'_'.$current_product_id.'_'.get_current_user_id(), $final_rules);
			b2bking()->set_global_data('b2bking_'.$rule_type, $final_rules, $current_product_id, get_current_user_id());
		}


		// 4. If there are no rules that apply to the product, check if this product is a variation and if 
		// there are any parent rules

		// if (!get_transient('b2bking_'.$rule_type.'_'.$current_product_id.'_'.get_current_user_id())){
		if (!b2bking()->get_global_data('b2bking_'.$rule_type, $current_product_id, get_current_user_id())){
			$post_parent_id = wp_get_post_parent_id($current_product_id);
			if ($post_parent_id !== 0){
				// check if there are parent rules
				$current_product_id = $post_parent_id;

				// based on code above
				// 1) Get all rules and check if any rules apply to the product
				// $rules_that_apply_to_product = get_transient('b2bking_'.$rule_type.'_parent_rules_apply_'.$current_product_id);
				$rules_that_apply_to_product = b2bking()->get_global_data('b2bking_'.$rule_type.'_parent_rules_apply',$current_product_id);

				if (!$rules_that_apply_to_product){

					if ($skip_calc_rules_apply_product === 'no'){

						$rules_that_apply = array();
						$ruletype_rules_option = get_option('b2bking_have_'.$rule_type.'_rules_list_ids', '');
						if (!empty($ruletype_rules_option)){
							$ruletype_rules_v2_ids = explode(',',$ruletype_rules_option);
						} else {
							$ruletype_rules_v2_ids = array();
						}

						foreach ($ruletype_rules_v2_ids as $rule_id){
							$applies = get_post_meta($rule_id,'b2bking_rule_applies', true);
							if ($applies === 'cart_total' || $applies === 'product_'.$current_product_id){
								array_push($rules_that_apply, $rule_id);
							} else if ($applies === 'multiple_options'){
								$multiple_options = get_post_meta($rule_id,'b2bking_rule_applies_multiple_options', true);
								$multiple_options_array = explode(',', $multiple_options);
								if (in_array('product_'.$current_product_id, $multiple_options_array)){
									array_push($rules_that_apply, $rule_id);
								} else {
									// try categories
									$current_product_categories = b2bking()->get_all_product_categories( $current_product_id, 'product_cat' );
									if (empty($current_product_categories)){
										// if no categories, this may be a variation, check parent categories
										$possible_parent_id = wp_get_post_parent_id($current_product_id);
										if ($possible_parent_id !== 0){
											// if product has parent
											$current_product_categories = b2bking()->get_all_product_categories( $possible_parent_id, 'product_cat' );
										}
									}
									$current_product_belongsto_array = array_map(function($value) { return 'category_'.$value; }, $current_product_categories);

									foreach ($current_product_belongsto_array as $item_category){
										if (in_array($item_category, $multiple_options_array)){
											array_push($rules_that_apply, $rule_id);
											break;
										}
									}
								}
								
							} else if (explode('_', $applies)[0] === 'category'){
								// check category
								$current_product_categories = b2bking()->get_all_product_categories( $current_product_id, 'product_cat' );
								if (empty($current_product_categories)){
									// if no categories, this may be a variation, check parent categories
									$possible_parent_id = wp_get_post_parent_id($current_product_id);
									if ($possible_parent_id !== 0){
										// if product has parent
										$current_product_categories = b2bking()->get_all_product_categories( $possible_parent_id, 'product_cat' );
									}
								}
								$current_product_belongsto_array = array_map(function($value) { return 'category_'.$value; }, $current_product_categories);
								if (in_array($applies, $current_product_belongsto_array)){
									array_push($rules_that_apply, $rule_id);
								}
							} else if ($applies === 'excluding_multiple_options'){
								// check that current product is not in list
								$multiple_options = get_post_meta($rule_id,'b2bking_rule_applies_multiple_options', true);
								$multiple_options_array = explode(',', $multiple_options);

								$product_is_excluded = 'no';

								$variation_parent_id = wp_get_post_parent_id($current_product_id);
								if (in_array('product_'.$current_product_id, $multiple_options_array) || in_array('product_'.$variation_parent_id, $multiple_options_array)){
									$product_is_excluded = 'yes';
								} else {
									// try categories
									$current_product_categories = b2bking()->get_all_product_categories( $current_product_id, 'product_cat' );
									$parent_product_categories = b2bking()->get_all_product_categories( $variation_parent_id, 'product_cat' );
									$current_product_categories = array_merge($current_product_categories, $parent_product_categories);

									$current_product_belongsto_array = array_map(function($value) { return 'category_'.$value; }, $current_product_categories);

									foreach ($current_product_belongsto_array as $item_category){
										if (in_array($item_category, $multiple_options_array)){
											$product_is_excluded = 'yes';
											break;
										}
									}
								}
								// check if product has any variations that are excluded. If yes, then exclude product too, so variations rule can apply.
								// Get children product variation IDs in an array
								$productobjj = wc_get_product($current_product_id);
								$children_ids = $productobjj->get_children();
								foreach ($children_ids as $child_id){
									if (in_array('product_'.$child_id, $multiple_options_array)){
										$product_is_excluded = 'yes';
										break;
									}
								}
								if ($product_is_excluded === 'no'){
									// product is not excluded, therefore rule applies
									array_push($rules_that_apply, $rule_id);
								}
							}
						}

						//set_transient('b2bking_'.$rule_type.'_parent_rules_apply_'.$current_product_id,$rules_that_apply);
						b2bking()->set_global_data('b2bking_'.$rule_type.'_parent_rules_apply',$rules_that_apply, $current_product_id);
						$rules_that_apply_to_product = $rules_that_apply;
					}
				}

				// if transient does not already exist
				//if (!get_transient('b2bking_'.$rule_type.'_parent_'.$current_product_id.'_'.get_current_user_id())){
				if (!b2bking()->get_global_data('b2bking_'.$rule_type.'_parent', $current_product_id, get_current_user_id())){
					// if we have the info about which rules apply to the product, use it, else calculate
					if ($rules_that_apply_to_product){
						// we have the info, simply intersect product rules with user rules
						$final_rules = array_intersect($rules_that_apply_to_product, $user_applicable_rules);
					} else {
						$final_rules = array();
						// for each user rule, check which rules apply to product
						foreach ($user_applicable_rules as $rule_id){
							$applies = get_post_meta($rule_id,'b2bking_rule_applies', true);
							if ($applies === 'cart_total' || $applies === 'product_'.$current_product_id){
								array_push($final_rules, $rule_id);
							} else if ($applies === 'multiple_options'){
								$multiple_options = get_post_meta($rule_id,'b2bking_rule_applies_multiple_options', true);
								$multiple_options_array = explode(',', $multiple_options);
								if (in_array('product_'.$current_product_id, $multiple_options_array)){
									array_push($final_rules, $rule_id);
								} else {
									// try categories
									$current_product_categories = b2bking()->get_all_product_categories( $current_product_id, 'product_cat' );
									if (empty($current_product_categories)){
										// if no categories, this may be a variation, check parent categories
										$possible_parent_id = wp_get_post_parent_id($current_product_id);
										if ($possible_parent_id !== 0){
											// if product has parent
											$current_product_categories = b2bking()->get_all_product_categories( $possible_parent_id, 'product_cat' );
										}
									}
									$current_product_belongsto_array = array_map(function($value) { return 'category_'.$value; }, $current_product_categories);

									foreach ($current_product_belongsto_array as $item_category){
										if (in_array($item_category, $multiple_options_array)){
											array_push($final_rules, $rule_id);
											break;
										}
									}
								}
								
							} else if (explode('_', $applies)[0] === 'category'){
								// check category
								$current_product_categories = b2bking()->get_all_product_categories( $current_product_id, 'product_cat' );
								if (empty($current_product_categories)){
									// if no categories, this may be a variation, check parent categories
									$possible_parent_id = wp_get_post_parent_id($current_product_id);
									if ($possible_parent_id !== 0){
										// if product has parent
										$current_product_categories = b2bking()->get_all_product_categories( $possible_parent_id, 'product_cat' );
									}
								}
								$current_product_belongsto_array = array_map(function($value) { return 'category_'.$value; }, $current_product_categories);
								if (in_array($applies, $current_product_belongsto_array)){
									array_push($final_rules, $rule_id);
								}
							} else if ($applies === 'excluding_multiple_options'){
								// check that current product is not in list
								$multiple_options = get_post_meta($rule_id,'b2bking_rule_applies_multiple_options', true);
								$multiple_options_array = explode(',', $multiple_options);

								$product_is_excluded = 'no';

								$variation_parent_id = wp_get_post_parent_id($current_product_id);
								if (in_array('product_'.$current_product_id, $multiple_options_array) || in_array('product_'.$variation_parent_id, $multiple_options_array)){
									$product_is_excluded = 'yes';
								} else {
									// try categories
									$current_product_categories = b2bking()->get_all_product_categories( $current_product_id, 'product_cat' );
									$parent_product_categories = b2bking()->get_all_product_categories( $variation_parent_id, 'product_cat' );
									$current_product_categories = array_merge($current_product_categories, $parent_product_categories);

									$current_product_belongsto_array = array_map(function($value) { return 'category_'.$value; }, $current_product_categories);

									foreach ($current_product_belongsto_array as $item_category){
										if (in_array($item_category, $multiple_options_array)){
											$product_is_excluded = 'yes';
											break;
										}
									}
								}
								// check if product has any variations that are excluded. If yes, then exclude product too, so variations rule can apply.
								// Get children product variation IDs in an array
								$productobjj = wc_get_product($current_product_id);
								$children_ids = $productobjj->get_children();
								foreach ($children_ids as $child_id){
									if (in_array('product_'.$child_id, $multiple_options_array)){
										$product_is_excluded = 'yes';
										break;
									}
								}
								if ($product_is_excluded === 'no'){
									// product is not excluded, therefore rule applies
									array_push($final_rules, $rule_id);
								}
							}

						}
					}
				//	set_transient('b2bking_'.$rule_type.'_parent_'.$current_product_id.'_'.get_current_user_id(), $final_rules);
					b2bking()->set_global_data('b2bking_'.$rule_type.'_parent',$final_rules, $current_product_id, get_current_user_id());
				}

			}	
		}
		
		//$ruletype_rules = get_transient('b2bking_'.$rule_type.'_'.$current_product_id.'_'.get_current_user_id());
		$ruletype_rules = b2bking()->get_global_data('b2bking_'.$rule_type, $current_product_id, get_current_user_id());
		//$ruletype_parent_rules = get_transient('b2bking_'.$rule_type.'_parent_'.$current_product_id.'_'.get_current_user_id());
		$ruletype_parent_rules = b2bking()->get_global_data('b2bking_'.$rule_type.'_parent', $current_product_id, get_current_user_id());

		if (empty($ruletype_rules)){
			$ruletype_rules = $ruletype_parent_rules;
			$current_product_categories = b2bking()->get_all_product_categories( $post_parent_id, 'product_cat' );
			if (empty($current_product_categories)){
				// if no categories, this may be a variation, check parent categories
				$possible_parent_id = wp_get_post_parent_id($current_product_id);
				if ($possible_parent_id !== 0){
					// if product has parent
					$current_product_categories = b2bking()->get_all_product_categories( $possible_parent_id, 'product_cat' );
				}
			}
			$current_product_belongsto_array = array_map(function($value) { return 'category_'.$value; }, $current_product_categories);
			// add the product to the array to search for all relevant rules
			array_push($current_product_belongsto_array, 'product_'.$post_parent_id);
		} else {
			$ruletype_rules = $ruletype_rules;
			$current_product_categories = b2bking()->get_all_product_categories( $current_product_id, 'product_cat' );
			if (empty($current_product_categories)){
				// if no categories, this may be a variation, check parent categories
				$possible_parent_id = wp_get_post_parent_id($current_product_id);
				if ($possible_parent_id !== 0){
					// if product has parent
					$current_product_categories = b2bking()->get_all_product_categories( $possible_parent_id, 'product_cat' );
				}
			}
			$current_product_belongsto_array = array_map(function($value) { return 'category_'.$value; }, $current_product_categories);
			// add the product to the array to search for all relevant rules
			array_push($current_product_belongsto_array, 'product_'.$current_product_id);
		}

		if (empty($ruletype_rules)){
			$ruletype_rules = array();
		}

		return array($ruletype_rules, $current_product_belongsto_array);	

	}

}