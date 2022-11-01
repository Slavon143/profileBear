<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/*
* Functions in this file, in order:
* b2bking_dynamic_rule_hidden_price - Dynamic rule: Hidden Price
b2bking_dynamic_rule_hidden_price_disable_purchasable - Dynamic rule: Hidden Price - Disables purchasable capability 
b2bking_dynamic_rule_unpurchasable_disable_purchasable - Dynamic rule: UNPURCHASABLE
* b2bking_dynamic_rule_cart_discount - Dynamic rules: Discount Amount and Discount Percentage
* b2bking_dynamic_rule_bogo_discount - Dynamic rule: Buy X Get 1 Free Discount
b2bking_dynamic_rule_discount_regular_price
b2bking_dynamic_rule_discount_sale_price
b2bking_dynamic_rule_discount_display_dynamic_price
b2bking_dynamic_rule_discount_display_dynamic_price_in_cart
b2bking_dynamic_rule_discount_display_dynamic_price_in_cart_item
b2bking_dynamic_rule_discount_display_dynamic_sale_bab2bking_dynamic_rule_discount_display_dynamic_price_in_cart_itemdge
* b2bking_dynamic_rule_add_tax_fee - Dynamic rules: Add tax / Fee (Percentage and Amount)
* b2bking_dynamic_rule_fixed_price - Dynamic rule: Fixed Price
* b2bking_dynamic_rule_free_shipping - Dynamic rule: Free Shipping
* b2bking_dynamic_minmax_order_amount - Dynamic rules: Minimum Order and Maximum Order
* b2bking_dynamic_minmax_order_amount_quantity - Dynamic rules: Minimum Order and Maximum Order step inputs
* b2bking_dynamic_minmax_order_amount_quantity_variation - Dynamic rules: Minimum Order and Maximum Order step inputs

* b2bking_dynamic_rule_required_multiple - Dynamic rule: Required multiple
* b2bking_dynamic_rule_required_multiple_quantity - sets quantity step for simple products
* b2bking_dynamic_rule_required_multiple_quantity_variation - sets quantity step for individual variations
* b2bking_dynamic_rule_required_multiple_quantity_number - sets minimum for ajax add to cart

* b2bking_dynamic_rule_zero_tax_product - Dynamic rule: Zero Tax Product
* b2bking_dynamic_rule_tax_exemption - Dynamic rule: Tax Exemption
b2bking_dynamic_rule_tax_exemption_fees
*/
		
class B2bking_Dynamic_Rules {

		// Dynamic rule Hidden price
		public static function b2bking_dynamic_rule_hidden_price($price, $product){
			if (isset($_POST['_inline_edit'])){
				return $price;
			}
			if (isset($_REQUEST['bulk_edit'])){
			    return $price;
			}

			// Get current product
			$current_product_id = $product->get_id();

			$offer_id = intval(get_option('b2bking_offer_product_id_setting', 0));
			if (intval($current_product_id) === $offer_id){
				return $price;
			}

			$user_id = get_current_user_id();
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

			$response = b2bking()->get_applicable_rules('hidden_price', $current_product_id, $user_id);

			// abort early
			if ($response === 'norules'){
				return $price;
			}

			$hidden_price_rules = $response[0];
			$current_product_belongsto_array = $response[1];


			// if there are no hidden price rules, than price is not hidden, and viceversa
			if (empty($hidden_price_rules)){
				return $price;
			} else {
				return apply_filters('b2bking_hidden_price_rule_text',get_option('b2bking_hidden_price_dynamic_rule_text_setting', esc_html__('Price is unavailable','b2bking')), $current_product_id);
			}

		}

		// Dynamic rule hidden price disable purchasable ability on product
		public static function b2bking_dynamic_rule_hidden_price_disable_purchasable($purchasable, $product){

			// Get current product
			$current_product_id = $product->get_id();

			$offer_id = intval(get_option('b2bking_offer_product_id_setting', 0));
			if (intval($current_product_id) === $offer_id){
				return $purchasable;
			}


			$user_id = get_current_user_id();
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

			$response = b2bking()->get_applicable_rules('hidden_price', $current_product_id, $user_id);

			// abort early
			if ($response === 'norules'){
				return $purchasable;
			}

			$hidden_price_rules = $response[0];

			// if there are no hidden price rules, than price is not hidden, and viceversa
			if (empty($hidden_price_rules)){
				return $purchasable;
			} else {
				return false;
			}
		}

		public static function b2bking_dynamic_rule_unpurchasable_disable_purchasable($purchasable, $product){

			// Get current product
			$current_product_id = $product->get_id();

			$offer_id = intval(get_option('b2bking_offer_product_id_setting', 0));
			if (intval($current_product_id) === $offer_id){
				return $purchasable;
			}

			$user_id = get_current_user_id();
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

			$response = b2bking()->get_applicable_rules('unpurchasable', $current_product_id, $user_id);

			// abort early
			if ($response === 'norules'){
				return $purchasable;
			}

			$unpurchasable_rules = $response[0];

			// if there are no hidden price rules, than price is not hidden, and viceversa
			if (empty($unpurchasable_rules)){
				return $purchasable;
			} else {
				return false;
			}
		}

		// Dynamic rule cart discount
		public static function b2bking_dynamic_rule_cart_discount( WC_Cart $cart ){

			$user_id = get_current_user_id();
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

			/*
			* Apply all discounts for "all products excluding"
			*/

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

			$discount_rule_ids = get_option('b2bking_have_discount_rules_list_ids', '');
			if (!empty($discount_rule_ids)){
				$discount_rule_ids = explode(',',$discount_rule_ids);
			} else {
				$discount_rule_ids = array();
			}

			$discount_rules_excluding = get_posts([
				'post_type' => 'b2bking_rule',
				'post_status' => 'publish',
				'fields' => 'ids',
				'post__in' => $discount_rule_ids,
				'numberposts' => -1,
				'meta_query'=> array(
					'relation' => 'AND',
					$array_who,
					array(
						'key' => 'b2bking_rule_applies',
						'value' => 'excluding_multiple_options'
					),
				)
			]);

			// foreach item in cart, check if any rule applies
			if(is_object($cart)) {
				foreach($cart->get_cart() as $cart_item){

					$variation_parent_id = 0;
					if (isset($cart_item['variation_id']) && intval($cart_item['variation_id']) !== 0){
						$current_product_id = $cart_item['variation_id'];
						$variation_parent_id = wp_get_post_parent_id($current_product_id);
					} else {
						$current_product_id = $cart_item['product_id'];
					}

					foreach ($discount_rules_excluding as $rule_id){
						$multiple_options = get_post_meta($rule_id,'b2bking_rule_applies_multiple_options', true);
						$multiple_options_array = explode(',', $multiple_options);

						$product_is_excluded = 'no';
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

						if ($product_is_excluded === 'no'){
							// check rule conditions
							$passconditions = 'yes';
							$conditions = get_post_meta($rule_id, 'b2bking_rule_conditions', true);

							if (!empty($conditions)){
								$conditions = explode('|',$conditions);
								foreach ($conditions as $condition){
									$condition_details = explode(';',$condition);

						    		if (substr($condition_details[0], -5) === 'value') {
									if (substr($condition_details[0], -5) === 'value') {
									$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
								}
								}
									switch ($condition_details[0]){
										case 'cart_total_quantity':
											switch ($condition_details[1]){
												case 'greater':
													if (! ($cart->cart_contents_count > intval($condition_details[2]))){
														$passconditions = 'no';
														break 3;
													}
												break;
												case 'equal':
													if (! ($cart->cart_contents_count === intval($condition_details[2]))){
														$passconditions = 'no';
														break 3;
													}
												break;
												case 'smaller':
													if (! ($cart->cart_contents_count < intval($condition_details[2]))){
														$passconditions = 'no';
														break 3;
													}
												break;
											}
											break;
										case 'cart_total_value':
											switch ($condition_details[1]){
												case 'greater':
													if (! (floatval(apply_filters('b2bking_cart_subtotal', WC()->cart->get_subtotal())) > floatval($condition_details[2]))){
														$passconditions = 'no';
														break 3;
													}
												break;
												case 'equal':
													if (! (floatval(apply_filters('b2bking_cart_subtotal', WC()->cart->get_subtotal())) === floatval($condition_details[2]))){
														$passconditions = 'no';
														break 3;
													}
												break;
												case 'smaller':
													if (! (floatval(apply_filters('b2bking_cart_subtotal', WC()->cart->get_subtotal())) < floatval($condition_details[2]))){
														$passconditions = 'no';
														break 3;
													}
												break;
											}
											break;
									}
								}
							}

							// Passed conditions
							if ($passconditions === 'yes'){

								// calculate discount amount. If bigger, replace total
								$type = get_post_meta($rule_id, 'b2bking_rule_what', true);
								$howmuch = get_post_meta($rule_id, 'b2bking_rule_howmuch', true);
								$discount_name = get_post_meta($rule_id, 'b2bking_rule_discountname', true);

								$decimals = apply_filters('b2bking_rounding_precision', get_option('woocommerce_price_num_decimals', 2));

								if ($type === 'discount_amount'){
									$howmuch = round(floatval ($howmuch) * $cart_item['quantity'], $decimals);
								} else if ($type === 'discount_percentage') {
									$howmuch = round((floatval($howmuch)/100) * $cart_item['line_total'], $decimals);

									// raise prices
									$raise_price = get_post_meta($rule_id,'b2bking_rule_raise_price', true);
									if ($raise_price === 'yes'){
										$howmuch = round((floatval(-1 * abs($howmuch))/100) * $cart_item['line_total'], $decimals);
									}
								}

								if ($howmuch > 0){
									// if user gave discount a name, use that
									if($discount_name !== NULL && $discount_name !== ''){
										$cart->add_fee( get_the_title($current_product_id).' '.apply_filters('b2bking_discount_name_excluding_products',$discount_name), - $howmuch);
									} else {
										$cart->add_fee( get_the_title($current_product_id).esc_html__(' Discount','b2bking'), - $howmuch);
									}
								}
							}

						}
					}

				}
			}

			/*
			* Apply all cart total discounts
			*/

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

			$discount_rule_ids = get_option('b2bking_have_discount_rules_list_ids', '');
			if (!empty($discount_rule_ids)){
				$discount_rule_ids = explode(',',$discount_rule_ids);
			} else {
				$discount_rule_ids = array();
			}

			//$total_cart_rules = get_transient('b2bking_total_cart_rules_'.get_current_user_id());
			$total_cart_rules = b2bking()->get_global_data('b2bking_total_cart_rules', false, get_current_user_id());

			if (!$total_cart_rules){
				// Get all dynamic rule total cart discounts that apply to the user or the user's group
				$total_cart_rules = get_posts([
					'post_type' => 'b2bking_rule',
					'post_status' => 'publish',
					'fields' => 'ids',
					'post__in' => $discount_rule_ids,
					'numberposts' => -1,
					'meta_query'=> array(
						'relation' => 'AND',
						$array_who,
						array(
							'key' => 'b2bking_rule_applies',
							'value' => 'cart_total'
						),
					)
				]);

			//	set_transient('b2bking_total_cart_rules_'.get_current_user_id(), $total_cart_rules);
				b2bking()->set_global_data('b2bking_total_cart_rules', $total_cart_rules, false, get_current_user_id());

			}

			// If multiply discounts apply, give only the bigger discount (rather than cumulated)
			$current_total_cart_discount = 0;
			$current_total_cart_discount_name = '';

			foreach($total_cart_rules as $total_cart_rule){
				// Check discount conditions
				$passconditions = 'yes';
				$conditions = get_post_meta($total_cart_rule, 'b2bking_rule_conditions', true);

				if (!empty($conditions)){
					$conditions = explode('|',$conditions);
					foreach ($conditions as $condition){
						$condition_details = explode(';',$condition);

			    		if (substr($condition_details[0], -5) === 'value') {
							$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
						}

						switch ($condition_details[0]){
							case 'cart_total_quantity':
								switch ($condition_details[1]){
									case 'greater':
										if (! ($cart->cart_contents_count > intval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'equal':
										if (! ($cart->cart_contents_count === intval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'smaller':
										if (! ($cart->cart_contents_count < intval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
								}
								break;
							case 'cart_total_value':
								switch ($condition_details[1]){
									case 'greater':
										if (! (floatval(apply_filters('b2bking_cart_subtotal', WC()->cart->get_subtotal())) > floatval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'equal':
										if (! (floatval(apply_filters('b2bking_cart_subtotal', WC()->cart->get_subtotal())) === floatval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'smaller':
										if (! (floatval(apply_filters('b2bking_cart_subtotal', WC()->cart->get_subtotal())) < floatval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
								}
								break;
						}
					}
				}

				// Passed conditions
				if ($passconditions === 'yes'){
					
					// calculate discount amount. If bigger, replace total
					$type = get_post_meta($total_cart_rule, 'b2bking_rule_what', true);
					$howmuch = get_post_meta($total_cart_rule, 'b2bking_rule_howmuch', true);
					$discount_name = get_post_meta($total_cart_rule, 'b2bking_rule_discountname', true);
					if ($type === 'discount_amount'){
						$howmuch = apply_filters('b2bking_rule_total_discount_howmuch', floatval ($howmuch), $cart->cart_contents_count, $total_cart_rule);
					} else if ($type === 'discount_percentage') {
						$howmuch = (floatval($howmuch)/100) * apply_filters('b2bking_cart_subtotal', WC()->cart->get_subtotal());

						$raise_price = get_post_meta($total_cart_rule,'b2bking_rule_raise_price', true);
						if ($raise_price === 'yes'){
							$howmuch = (floatval(-1 * abs($howmuch))/100) * apply_filters('b2bking_cart_subtotal', WC()->cart->get_subtotal());
						}
					}

					if($howmuch > $current_total_cart_discount){
						$current_total_cart_discount = $howmuch;
						$current_total_cart_discount_name = $discount_name;
					}
				}
			}

			// Apply the biggest total cart discount, if any
			if($current_total_cart_discount > 0){
				$discount_display_name = esc_html__('Total Cart Discount','b2bking');
				if ($current_total_cart_discount_name !== '' && $current_total_cart_discount_name !== NULL){
					$discount_display_name = $current_total_cart_discount_name;
				}

				$decimals = apply_filters('b2bking_rounding_precision', get_option('woocommerce_price_num_decimals', 2));
				$cart->add_fee( $discount_display_name, -round($current_total_cart_discount, $decimals));
			}


			/*
			* Apply all product category discounts
			*/

			$categorydiscounts = array();


			// Get all dynamic rule product category discounts discounts that apply to the user or the user's group

			//$category_discount_rules = get_transient('b2bking_category_discount_rules_'.get_current_user_id());
			$category_discount_rules = b2bking()->get_global_data('b2bking_category_discount_rules', false,  get_current_user_id());


			if (!$category_discount_rules){
				$category_discount_rules = get_posts([
					'post_type' => 'b2bking_rule',
					'post_status' => 'publish',
					'numberposts' => -1,
					'fields' => 'ids',
					'post__in' => $discount_rule_ids,
					'meta_query'=> array(
						'relation' => 'AND',
						$array_who,
						array(
							'key' => 'b2bking_rule_applies',
							'value' => 'category', // values are of the form: category_idnumber, category_5, category_47 etc
							'compare' => 'LIKE'
						),
					)
				]);
			//	set_transient('b2bking_category_discount_rules_'.get_current_user_id(), $category_discount_rules);
				b2bking()->set_global_data('b2bking_category_discount_rules', $category_discount_rules, false, get_current_user_id());
			}
			foreach ($category_discount_rules as $category_discount_rule){
				
				// Get discount details
				$type = get_post_meta($category_discount_rule, 'b2bking_rule_what', true);
				$howmuch = get_post_meta($category_discount_rule, 'b2bking_rule_howmuch', true);
				$category_id = explode('_',get_post_meta($category_discount_rule, 'b2bking_rule_applies', true))[1];
				$discount_name = get_post_meta($category_discount_rule, 'b2bking_rule_discountname', true);

				$category_title = get_term( $category_id )->name;
				$number_products = 0;
				$total_price_products = 0;

				// Calculate number of products in cart of this category AND total price of these products
				if(is_object($cart)) {
					foreach($cart->get_cart() as $cart_item){

						if(b2bking()->b2bking_has_category($category_id, 'product_cat', $cart_item['product_id'])){
							$item_price = $cart_item['data']->get_price(); 
							$item_qty = $cart_item["quantity"];// Quantity
							$item_line_total = $cart_item["line_total"]; // Item total price (price x quantity)
							$number_products += $item_qty; // ctotal number of items in cart
							$total_price_products += $item_line_total; // calculated total items amount
						}
					}
				}

				// Check discount conditions
				$passconditions = 'yes';
				$conditions = get_post_meta($category_discount_rule, 'b2bking_rule_conditions', true);

				if (!empty($conditions)){
					$conditions = explode('|',$conditions);
					foreach ($conditions as $condition){
						$condition_details = explode(';',$condition);

			    		if (substr($condition_details[0], -5) === 'value') {
							$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
						}

						switch ($condition_details[0]){
							case 'category_product_quantity':
								switch ($condition_details[1]){
									case 'greater':
										if (! ($number_products > intval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'equal':
										if (! ($number_products === intval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'smaller':
										if (! ($number_products < intval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
								}
								break;
							case 'category_product_value':
								switch ($condition_details[1]){
									case 'greater':
										if (! (floatval($total_price_products) > floatval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'equal':
										if (! (floatval($total_price_products) === floatval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'smaller':
										if (! (floatval($total_price_products) < floatval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
								}
								break;	
							case 'cart_total_quantity':
								switch ($condition_details[1]){
									case 'greater':
										if (! ($cart->cart_contents_count > intval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'equal':
										if (! ($cart->cart_contents_count === intval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'smaller':
										if (! ($cart->cart_contents_count < intval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
								}
								break;

							case 'cart_total_value':
								switch ($condition_details[1]){
									case 'greater':
										if (! (floatval(apply_filters('b2bking_cart_subtotal', WC()->cart->get_subtotal())) > floatval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'equal':
										if (! (floatval(apply_filters('b2bking_cart_subtotal', WC()->cart->get_subtotal())) === floatval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'smaller':
										if (! (floatval(apply_filters('b2bking_cart_subtotal', WC()->cart->get_subtotal())) < floatval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
								}
								break;
						}
					}
				}

				// Passed conditions
				if ($passconditions === 'yes'){

					if ($type === 'discount_amount'){
						$howmuch = floatval ($howmuch) * apply_filters('b2bking_discount_amount_rules_times_applied',$number_products);
					} else if ($type === 'discount_percentage') {
						$howmuch = (floatval($howmuch)/100) * $total_price_products;

						$raise_price = get_post_meta($category_discount_rule,'b2bking_rule_raise_price', true);
						if ($raise_price === 'yes'){
							$howmuch = (floatval(-1 * abs($howmuch))/100) * $total_price_products;
						}
					}

					if ($howmuch > 0){
						if (!isset($categorydiscounts[$category_id])){
							$categorydiscounts[$category_id] = array($category_title.esc_html__(' Discount','b2bking'), $howmuch);
							// if the user gave the discount a name, use that
							if ($discount_name !== NULL && $discount_name !== ''){
								$categorydiscounts[$category_id][0] = $discount_name;
							}
						} else {
							if ($howmuch > $categorydiscounts[$category_id][1]){
								$categorydiscounts[$category_id][1] = $howmuch;
								$categorydiscounts[$category_id][0] = $category_title.esc_html__(' Discount','b2bking');
								// if the user gave the discount a name, use that
								if ($discount_name !== NULL && $discount_name !== ''){
									$categorydiscounts[$category_id][0] = $discount_name;
								}
							}
						}
					}
				}
			}

			// Apply all the category discounts
			if (!empty($categorydiscounts)){
				foreach ($categorydiscounts as $discount){
					$decimals = apply_filters('b2bking_rounding_precision', get_option('woocommerce_price_num_decimals', 2));
					$cart->add_fee( $discount[0], - round($discount[1], $decimals));
				}
			}
			
			/*
			* Apply all individual product discounts
			*/

			$productdiscounts = array();

		//	$product_discount_rules = get_transient('b2bking_product_discount_rules_'.get_current_user_id());
			$product_discount_rules = b2bking()->get_global_data('b2bking_product_discount_rules', false, get_current_user_id());

			if (!$product_discount_rules){
				// Get all dynamic rule individual product discounts  that apply to the user or the user's group
				$product_discount_rules = get_posts([
					'post_type' => 'b2bking_rule',
					'post_status' => 'publish',
					'numberposts' => -1,
					'fields' => 'ids',
					'post__in' => $discount_rule_ids,
					'meta_query'=> array(
						'relation' => 'AND',
						$array_who,
						array(
							'key' => 'b2bking_rule_applies',
							'value' => 'product', // values are of the form: product_idnumber
							'compare' => 'LIKE'
						),
					)
				]);

			//	set_transient('b2bking_product_discount_rules_'.get_current_user_id(), $product_discount_rules);
				b2bking()->set_global_data('b2bking_product_discount_rules', $product_discount_rules, false, get_current_user_id());
			}

			foreach ($product_discount_rules as $product_discount_rule){
				// Get discount details
				$type = get_post_meta($product_discount_rule, 'b2bking_rule_what', true);
				$howmuch = get_post_meta($product_discount_rule, 'b2bking_rule_howmuch', true);
				$product_id = explode('_',get_post_meta($product_discount_rule, 'b2bking_rule_applies', true))[1];
				$discount_name = get_post_meta($product_discount_rule, 'b2bking_rule_discountname', true);

				$product_title = get_the_title( $product_id );
				$number_products = 0;
				$total_price_products = 0;

				if(is_object($cart)) {
					foreach($cart->get_cart() as $cart_item){

						if(intval($product_id) === intval($cart_item['product_id']) || intval($product_id) === intval($cart_item['variation_id'])){
							$item_price = $cart_item['data']->get_price(); 
							$item_qty = $cart_item["quantity"];// Quantity
							$item_line_total = $cart_item["line_total"]; // Item total price (price x quantity)
							$number_products += $item_qty; // ctotal number of items in cart
							$total_price_products += $item_line_total; // calculated total items amount
						}

					}
				}
				// Check discount conditions
				$passconditions = 'yes';
				$conditions = get_post_meta($product_discount_rule, 'b2bking_rule_conditions', true);

				if (!empty($conditions)){
					$conditions = explode('|',$conditions);
					foreach ($conditions as $condition){
						$condition_details = explode(';',$condition);

			    		if (substr($condition_details[0], -5) === 'value') {
									$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
								}
						switch ($condition_details[0]){
							case 'product_quantity':
								switch ($condition_details[1]){
									case 'greater':
										if (! ($number_products > intval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'equal':
										if (! ($number_products === intval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'smaller':
										if (! ($number_products < intval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
								}
								break;
							case 'product_value':
								switch ($condition_details[1]){
									case 'greater':
										if (! (floatval($total_price_products) > floatval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'equal':
										if (! (floatval($total_price_products) === floatval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'smaller':
										if (! (floatval($total_price_products) < floatval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
								}
								break;	
							case 'cart_total_quantity':
								switch ($condition_details[1]){
									case 'greater':
										if (! ($cart->cart_contents_count > intval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'equal':
										if (! ($cart->cart_contents_count === intval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'smaller':
										if (! ($cart->cart_contents_count < intval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
								}
								break;
							case 'cart_total_value':
								switch ($condition_details[1]){
									case 'greater':
										if (! (floatval(apply_filters('b2bking_cart_subtotal', WC()->cart->get_subtotal())) > floatval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'equal':
										if (! (floatval(apply_filters('b2bking_cart_subtotal', WC()->cart->get_subtotal())) === floatval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'smaller':
										if (! (floatval(apply_filters('b2bking_cart_subtotal', WC()->cart->get_subtotal())) < floatval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
								}
								break;
						}
					}
				}

				// Passed conditions
				if ($passconditions === 'yes'){

					if ($type === 'discount_amount'){
						$howmuch = floatval ($howmuch) * $number_products;
					} else if ($type === 'discount_percentage') {
						$howmuch = (floatval($howmuch)/100) * $total_price_products;

						$raise_price = get_post_meta($product_discount_rule,'b2bking_rule_raise_price', true);
						if ($raise_price === 'yes'){
							$howmuch = (floatval(-1 * abs($howmuch))/100) * $total_price_products;
						}
					}

					if ($howmuch > 0){
						if (!isset($productdiscounts[$product_id])){
							$productdiscounts[$product_id] = array($product_title.esc_html__(' Discount','b2bking'), $howmuch);
							// if user gave discount a name, use that
							if($discount_name !== NULL && $discount_name !== ''){
								$productdiscounts[$product_id][0] = $discount_name;
							}
						} else {
							if ($howmuch > $productdiscounts[$product_id][1]){
								$productdiscounts[$product_id][1] = $howmuch;
								$productdiscounts[$product_id][0] = $product_title.esc_html__(' Discount','b2bking');
								// if user gave discount a name, use that
								if($discount_name !== NULL && $discount_name !== ''){
									$productdiscounts[$product_id][0] = $discount_name;
								}
							}
						}
					}
				}	
			}

			// Apply all the product discounts
			if (!empty($productdiscounts)){
				foreach ($productdiscounts as $discount){
					$decimals = apply_filters('b2bking_rounding_precision', get_option('woocommerce_price_num_decimals', 2));
					$cart->add_fee( $discount[0], - round($discount[1], $decimals));
				}
			}

			/*
			* Apply all multi select discounts
			*/
			
		//	$multiselect_discount_rules = get_transient('b2bking_multiselect_discount_rules_'.get_current_user_id());
			$multiselect_discount_rules = b2bking()->get_global_data('b2bking_multiselect_discount_rules', false, get_current_user_id());

			if (!$multiselect_discount_rules){
				// Get all multiselect discounts that apply to the user or the user's group
				$multiselect_discount_rules = get_posts([
					'post_type' => 'b2bking_rule',
					'post_status' => 'publish',
					'numberposts' => -1,
					'fields' => 'ids',
					'post__in' => $discount_rule_ids,
					'meta_query'=> array(
						'relation' => 'AND',
						$array_who,
						array(
							'key' => 'b2bking_rule_applies',
							'value' => 'multiple_options', 
						),
					)
				]);
			//	set_transient('b2bking_multiselect_discount_rules_'.get_current_user_id(), $multiselect_discount_rules);
				b2bking()->set_global_data('b2bking_multiselect_discount_rules', $multiselect_discount_rules,false, get_current_user_id());

			}

			// product discounts rules as part of multiselect
			foreach ($multiselect_discount_rules as $multiselect_discount_rule){
				// Get discount details
				$type = get_post_meta($multiselect_discount_rule, 'b2bking_rule_what', true);
				$howmuch = get_post_meta($multiselect_discount_rule, 'b2bking_rule_howmuch', true);
				$discount_name = get_post_meta($multiselect_discount_rule, 'b2bking_rule_discountname', true);

				$rule_multiple_options = get_post_meta($multiselect_discount_rule, 'b2bking_rule_applies_multiple_options', true);
				$rule_multiple_options_array = explode(',',$rule_multiple_options);
				foreach ($rule_multiple_options_array as $rule_element){
					$rule_element_array = explode('_',$rule_element);
					if ($rule_element_array[0] === 'category'){
						$howmuch = get_post_meta($multiselect_discount_rule, 'b2bking_rule_howmuch', true);
						$categorydiscountsmulti = array();
						$category_id = $rule_element_array[1];
						$category_title = get_term( $category_id );
						if (is_object($category_title)){
							$category_title = $category_title->name;
						} else {
							$category_title = '';
						}
						$number_products = 0;
						$total_price_products = 0;

						// Calculate number of products in cart of this category AND total price of these products
						if(is_object($cart)) {
							foreach($cart->get_cart() as $cart_item){

								if(b2bking()->b2bking_has_category($category_id, 'product_cat', $cart_item['product_id'])){
									$item_price = $cart_item['data']->get_price(); 
									$item_qty = $cart_item["quantity"];// Quantity
									$item_line_total = $cart_item["line_total"]; // Item total price (price x quantity)
									$number_products += $item_qty; // ctotal number of items in cart
									$total_price_products += $item_line_total; // calculated total items amount
								}
							}
						}

						// Check discount conditions
						$passconditions = 'yes';
						$conditions = get_post_meta($multiselect_discount_rule, 'b2bking_rule_conditions', true);

						if (!empty($conditions)){
							$conditions = explode('|',$conditions);
							foreach ($conditions as $condition){
								$condition_details = explode(';',$condition);

					    		if (substr($condition_details[0], -5) === 'value') {
									$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
								}
								switch ($condition_details[0]){
									case 'category_product_quantity':
										switch ($condition_details[1]){
											case 'greater':
												if (! ($number_products > intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'equal':
												if (! ($number_products === intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'smaller':
												if (! ($number_products < intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
										}
										break;
									case 'category_product_value':
										switch ($condition_details[1]){
											case 'greater':
												if (! (floatval($total_price_products) > floatval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'equal':
												if (! (floatval($total_price_products) === floatval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'smaller':
												if (! (floatval($total_price_products) < floatval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
										}
										break;	
									case 'cart_total_quantity':
										switch ($condition_details[1]){
											case 'greater':
												if (! ($cart->cart_contents_count > intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'equal':
												if (! ($cart->cart_contents_count === intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'smaller':
												if (! ($cart->cart_contents_count < intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
										}
										break;
									case 'cart_total_value':
										switch ($condition_details[1]){
											case 'greater':
												if (! (floatval(apply_filters('b2bking_cart_subtotal', WC()->cart->get_subtotal())) > floatval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'equal':
												if (! (floatval(apply_filters('b2bking_cart_subtotal', WC()->cart->get_subtotal())) === floatval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'smaller':
												if (! (floatval(apply_filters('b2bking_cart_subtotal', WC()->cart->get_subtotal())) < floatval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
										}
										break;
								}
							}
						}

						// Passed conditions
						if ($passconditions === 'yes'){

							if ($type === 'discount_amount'){
								$howmuch = floatval ($howmuch) * $number_products;
							} else if ($type === 'discount_percentage') {
								$howmuch = (floatval($howmuch)/100) * $total_price_products;

								$raise_price = get_post_meta($multiselect_discount_rule,'b2bking_rule_raise_price', true);
								if ($raise_price === 'yes'){
									$howmuch = (floatval(-1 * abs($howmuch))/100) * $total_price_products;
								}
							}

							if ($howmuch > 0){
								if (!isset($categorydiscountsmulti[$category_id])){
									$categorydiscountsmulti[$category_id] = array($category_title.esc_html__(' Discount','b2bking'), $howmuch);
									// if the user gave the discount a name, use that
									if ($discount_name !== NULL && $discount_name !== ''){
										$categorydiscountsmulti[$category_id][0] = $discount_name.' '.$category_title;
									}
								} else {
									if ($howmuch > $categorydiscounts[$category_id][1]){
										$categorydiscountsmulti[$category_id][1] = $howmuch;
										$categorydiscountsmulti[$category_id][0] = $category_title.esc_html__(' Discount','b2bking');
										// if the user gave the discount a name, use that
										if ($discount_name !== NULL && $discount_name !== ''){
											$categorydiscountsmulti[$category_id][0] = $discount_name.' '.$category_title;
										}
									}
								}
							}
						}

						// Apply all the category discounts
						if (!empty($categorydiscountsmulti)){
							foreach ($categorydiscountsmulti as $discount){
								$decimals = apply_filters('b2bking_rounding_precision', get_option('woocommerce_price_num_decimals', 2));
								$cart->add_fee( $discount[0], - round($discount[1], $decimals));
							}
						}

					} else if ($rule_element_array[0] === 'product'){
						$howmuch = get_post_meta($multiselect_discount_rule, 'b2bking_rule_howmuch', true);
						$productdiscountsmulti = array();
						$product_id = $rule_element_array[1];
						$product_title = get_the_title( $product_id );
						$number_products = 0;
						$total_price_products = 0;

						if(is_object($cart)) {
							foreach($cart->get_cart() as $cart_item){

								if(intval($product_id) === intval($cart_item['product_id']) || intval($product_id) === intval($cart_item['variation_id'])){
									$item_price = $cart_item['data']->get_price(); 
									$item_qty = $cart_item["quantity"];// Quantity
									$item_line_total = $cart_item["line_total"]; // Item total price (price x quantity)
									$number_products += $item_qty; // ctotal number of items in cart
									$total_price_products += $item_line_total; // calculated total items amount
								}

							}
						}
						// Check discount conditions
						$passconditions = 'yes';
						$conditions = get_post_meta($multiselect_discount_rule, 'b2bking_rule_conditions', true);

						if (!empty($conditions)){
							$conditions = explode('|',$conditions);
							foreach ($conditions as $condition){
								$condition_details = explode(';',$condition);

					    		if (substr($condition_details[0], -5) === 'value') {
									$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
								}
								switch ($condition_details[0]){
									case 'product_quantity':
										switch ($condition_details[1]){
											case 'greater':
												if (! ($number_products > intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'equal':
												if (! ($number_products === intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'smaller':
												if (! ($number_products < intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
										}
										break;
									case 'product_value':
										switch ($condition_details[1]){
											case 'greater':
												if (! (floatval($total_price_products) > floatval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'equal':
												if (! (floatval($total_price_products) === floatval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'smaller':
												if (! (floatval($total_price_products) < floatval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
										}
										break;	
									case 'cart_total_quantity':
										switch ($condition_details[1]){
											case 'greater':
												if (! ($cart->cart_contents_count > intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'equal':
												if (! ($cart->cart_contents_count === intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'smaller':
												if (! ($cart->cart_contents_count < intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
										}
										break;
									case 'cart_total_value':
										switch ($condition_details[1]){
											case 'greater':
												if (! (floatval(apply_filters('b2bking_cart_subtotal', WC()->cart->get_subtotal())) > floatval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'equal':
												if (! (floatval(apply_filters('b2bking_cart_subtotal', WC()->cart->get_subtotal())) === floatval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'smaller':
												if (! (floatval(apply_filters('b2bking_cart_subtotal', WC()->cart->get_subtotal())) < floatval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
										}
										break;
								}
							}
						}

						// Passed conditions
						if ($passconditions === 'yes'){

							if ($type === 'discount_amount'){
								$howmuch = floatval ($howmuch) * $number_products;
							} else if ($type === 'discount_percentage') {
								$howmuch = (floatval($howmuch)/100) * $total_price_products;

								$raise_price = get_post_meta($multiselect_discount_rule,'b2bking_rule_raise_price', true);
								if ($raise_price === 'yes'){
									$howmuch = (floatval(-1 * abs($howmuch))/100) * $total_price_products;
								}
							}

							if ($howmuch > 0){
								if (!isset($productdiscountsmulti[$product_id])){
									$productdiscountsmulti[$product_id] = array($product_title.esc_html__(' Discount','b2bking'), $howmuch);
									// if user gave discount a name, use that
									if($discount_name !== NULL && $discount_name !== ''){
										$productdiscountsmulti[$product_id][0] = $discount_name.' '.$product_title;
									}
								} else {
									if ($howmuch > $productdiscountsmulti[$product_id][1]){
										$productdiscountsmulti[$product_id][1] = $howmuch;
										$productdiscountsmulti[$product_id][0] = $product_title.esc_html__(' Discount','b2bking');
										// if user gave discount a name, use that
										if($discount_name !== NULL && $discount_name !== ''){
											$productdiscountsmulti[$product_id][0] = $discount_name.' '.$product_title;
										}
									}
								}
							}
						}	

						
						// Apply all the category discounts
						if (!empty($productdiscountsmulti)){
							foreach ($productdiscountsmulti as $discount){
								$decimals = apply_filters('b2bking_rounding_precision', get_option('woocommerce_price_num_decimals', 2));
								$cart->add_fee( $discount[0], - round($discount[1], $decimals));
							}
						}
					}
				}


			}
		}

		public static function b2bking_dynamic_rule_bogo_discount( WC_Cart $cart ){

			$user_id = get_current_user_id();
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

			if(is_object($cart)) {

				foreach($cart->get_cart() as $cart_item){
					// Get current product
					if (isset($cart_item['variation_id']) && intval($cart_item['variation_id']) !== 0){
						$current_product_id = $cart_item['variation_id'];
					} else {
						$current_product_id = $cart_item['product_id'];
					}

					$item_quantity = $cart_item["quantity"];// Quantity
					$item_line_total = $cart_item["line_total"]; // Item total price (price x quantity)
					$item_price = $item_line_total / $item_quantity;


					$response = b2bking()->get_applicable_rules('bogo_discount', $current_product_id, $user_id);

					// abort early
					if ($response === 'norules'){
						continue;
					}

					$discount_rules = $response[0];
					$current_product_belongsto_array = $response[1];


					// if multiple discount rules apply, give the smallest price to the user
					$have_bogo_number = NULL;
					$smallest_bogo_number = 0;

					foreach ($discount_rules as $discount_rule){

						// Get rule details
						$type = get_post_meta($discount_rule, 'b2bking_rule_what', true);
						$howmuch = get_post_meta($discount_rule, 'b2bking_rule_howmuch', true);
						$applies = explode('_',get_post_meta($discount_rule, 'b2bking_rule_applies', true));
						$rule_multiple_options = get_post_meta($discount_rule, 'b2bking_rule_applies_multiple_options', true);
						$rule_multiple_options_array = explode(',',$rule_multiple_options);
						$cart = WC()->cart;
						// Get conditions
						$passconditions = 'yes';
						$conditions = get_post_meta($discount_rule, 'b2bking_rule_conditions', true);

						if (!empty($conditions)){
							$conditions = explode('|',$conditions);

							if ($applies[0] === 'excluding'){
								// check that current product is not in excluded list
								$product_is_excluded = 'no';

								$variation_parent_id = wp_get_post_parent_id($current_product_id);

								foreach ($rule_multiple_options_array as $excluded_option){
									if ('product_'.$current_product_id === $excluded_option || 'product_'.$variation_parent_id === $excluded_option){
										$product_is_excluded = 'yes';
										break;
									} else {
										// check categories
										$current_product_categories = b2bking()->get_all_product_categories( $current_product_id, 'product_cat' );
										$parent_product_categories = b2bking()->get_all_product_categories( $variation_parent_id, 'product_cat' );
										$current_product_categories = array_merge($current_product_categories, $parent_product_categories);

										$current_product_belongsto_array = array_map(function($value) { return 'category_'.$value; }, $current_product_categories);

										foreach ($current_product_belongsto_array as $item_category){
											if ($item_category === $excluded_option){
												$product_is_excluded = 'yes';
												break 2;
											}
										}
									}
								}
								// check if product has any variations that are excluded. If yes, then exclude product too, so variations rule can apply.
								// Get children product variation IDs in an array
								$productobjj = wc_get_product($current_product_id);
								$children_ids = $productobjj->get_children();
								foreach ($children_ids as $child_id){
									if (in_array('product_'.$child_id, $rule_multiple_options_array)){
										$product_is_excluded = 'yes';
										break;
									}
								}
								if ($product_is_excluded === 'no'){
									// go forward with discount, check conditions
									foreach ($conditions as $condition){
										$condition_details = explode(';',$condition);

							    		if (substr($condition_details[0], -5) === 'value') {
											$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
										}

										switch ($condition_details[0]){
											case 'cart_total_value':
												switch ($condition_details[1]){
													case 'greater':
														if (! ($cart->cart_contents_total > intval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
													case 'equal':
														if (! ($cart->cart_contents_total === intval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
													case 'smaller':
														if (! ($cart->cart_contents_total < intval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
												}
												break;
											
											case 'cart_total_quantity':
												switch ($condition_details[1]){
													case 'greater':
														if (! ($cart->cart_contents_count > intval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
													case 'equal':
														if (! ($cart->cart_contents_count === intval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
													case 'smaller':
														if (! ($cart->cart_contents_count < intval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
												}
												break;
										}
									}
								}
							} else if ($applies[0] === 'multiple'){ // if rule is multiple products / categories rule
								$temporary_pass_conditions = 'no'; // if at least 1 element passes, change to yes
								// for each element that applies to the product and is part of the rule, check if at least 1 passes the conditions
								foreach($current_product_belongsto_array as $element){
									if(in_array($element, $rule_multiple_options_array)){
										$element_array = explode('_', $element);
										// if element is product or if element is category
										if ($element_array[0] === 'product'){
											$passes_inside_conditions = 'yes';
											$product_quantity = 0;
											if(is_object($cart)) {
												foreach($cart->get_cart() as $cart_item){
													if(intval($element_array[1]) === intval($cart_item['product_id']) || intval($element_array[1]) === intval($cart_item['variation_id'])){
														$product_quantity = $cart_item["quantity"];// Quantity
														break;
													}
												}
											}
											// check all product conditions against it
											foreach ($conditions as $condition){
												$condition_details = explode(';',$condition);

									    		if (substr($condition_details[0], -5) === 'value') {
													$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
												}

												switch ($condition_details[0]){
													case 'product_quantity':
														switch ($condition_details[1]){
															case 'greater':
																if (! ($product_quantity > intval($condition_details[2]))){
																	$passes_inside_conditions = 'no';
																	break 3;
																}
															break;
															case 'equal':
																if (! ($product_quantity === intval($condition_details[2]))){
																	$passes_inside_conditions = 'no';
																	break 3;
																}
															break;
															case 'smaller':
																if (! ($product_quantity < intval($condition_details[2]))){
																	$passes_inside_conditions = 'no';
																	break 3;
																}
															break;
														}
														break;
													case 'cart_total_quantity':
														switch ($condition_details[1]){
															case 'greater':
																if (! ($cart->cart_contents_count > intval($condition_details[2]))){
																	$passes_inside_conditions = 'no';
																	break 3;
																}
															break;
															case 'equal':
																if (! ($cart->cart_contents_count === intval($condition_details[2]))){
																	$passes_inside_conditions = 'no';
																	break 3;
																}
															break;
															case 'smaller':
																if (! ($cart->cart_contents_count < intval($condition_details[2]))){
																	$passes_inside_conditions = 'no';
																	break 3;
																}
															break;
														}
														break;
												}
											}
											if ($passes_inside_conditions === 'yes'){
												$temporary_pass_conditions = 'yes';
												break; // if 1 element passed, no need to check all other elements
											}
										} else if ($element_array[0] === 'category'){
											// check all category conditions against it + car total conditions
											$passes_inside_conditions = 'yes';
											$category_quantity = 0;
											if(is_object($cart)) {
												foreach($cart->get_cart() as $cart_item){
													if(b2bking()->b2bking_has_category($element_array[1], 'product_cat', $cart_item['product_id'])){
														$category_quantity += $cart_item["quantity"]; // add item quantity
													}
												}
											}
											foreach ($conditions as $condition){
												$condition_details = explode(';',$condition);

									    		if (substr($condition_details[0], -5) === 'value') {
													$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
												}

												switch ($condition_details[0]){
													case 'category_product_quantity':
														switch ($condition_details[1]){
															case 'greater':
																if (! ($category_quantity > intval($condition_details[2]))){
																	$passes_inside_conditions = 'no';
																	break 3;
																}
															break;
															case 'equal':
																if (! ($category_quantity === intval($condition_details[2]))){
																	$passes_inside_conditions = 'no';
																	break 3;
																}
															break;
															case 'smaller':
																if (! ($category_quantity < intval($condition_details[2]))){
																	$passes_inside_conditions = 'no';
																	break 3;
																}
															break;
														}
														break;
													case 'cart_total_quantity':
														switch ($condition_details[1]){
															case 'greater':
																if (! ($cart->cart_contents_count > intval($condition_details[2]))){
																	$passes_inside_conditions = 'no';
																	break 3;
																}
															break;
															case 'equal':
																if (! ($cart->cart_contents_count === intval($condition_details[2]))){
																	$passes_inside_conditions = 'no';
																	break 3;
																}
															break;
															case 'smaller':
																if (! ($cart->cart_contents_count < intval($condition_details[2]))){
																	$passes_inside_conditions = 'no';
																	break 3;
																}
															break;
														}
														break;
												}
											}
											if ($passes_inside_conditions === 'yes'){
												$temporary_pass_conditions = 'yes';
												break; // if 1 element passed, no need to check all other elements
											}
										}
									}
								} //foreach element end

								if ($temporary_pass_conditions === 'no'){
									$passconditions = 'no';
								}

							} else {

								$category_products_number = 0;
								$category_products_value = 0;
								$products_number = 0;
								$products_value = 0;
								$cart = WC()->cart;

								// Check rule is category rule or product rule
								if ($applies[0] === 'category'){

									// Calculate number of products in cart of this category AND total price of these products
									if(is_object($cart)) {
										foreach($cart->get_cart() as $cart_item){
											if(b2bking()->b2bking_has_category($applies[1], 'product_cat', $cart_item['product_id'])){
												$item_qty = $cart_item["quantity"];// Quantity
												$item_line_total = $cart_item["line_total"]; // Item total price (price x quantity)
												$category_products_number += $item_qty; // ctotal number of items in cart
												$category_products_value += $item_line_total; // calculated total items amount
											}
										}
									}
								} else if ($applies[0] === 'product') {
									if(is_object($cart)) {
										foreach($cart->get_cart() as $cart_item){
											if(intval($current_product_id) === intval($cart_item['product_id']) || intval($current_product_id) === intval($cart_item['variation_id'])){
												$item_qty = $cart_item["quantity"];// Quantity
												$products_number += $item_qty; // ctotal number of items in cart
												if (isset($cart_item["line_total"])){
													$item_line_total = $cart_item["line_total"]; // Item total price (price x quantity)
													$products_value += $item_line_total; // calculated total items amount
												}
											}
										}
									}
								}

								// Check discount conditions
								$passconditions = 'yes';
								$conditions = get_post_meta($discount_rule, 'b2bking_rule_conditions', true);

								if (!empty($conditions)){
									$conditions = explode('|',$conditions);
									foreach ($conditions as $condition){
										$condition_details = explode(';',$condition);

							    		if (substr($condition_details[0], -5) === 'value') {
												$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
											}

										switch ($condition_details[0]){
											case 'product_quantity':
												switch ($condition_details[1]){
													case 'greater':
														if (! ($products_number > intval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
													case 'equal':
														if (! ($products_number === intval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
													case 'smaller':
														if (! ($products_number < intval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
												}
												break;
											
											case 'category_product_quantity':
												switch ($condition_details[1]){
													case 'greater':
														if (! ($category_products_number > intval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
													case 'equal':
														if (! ($category_products_number === intval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
													case 'smaller':
														if (! ($category_products_number < intval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
												}
												break;
											
											case 'cart_total_quantity':
												switch ($condition_details[1]){
													case 'greater':
														if (! ($cart->cart_contents_count > intval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
													case 'equal':
														if (! ($cart->cart_contents_count === intval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
													case 'smaller':
														if (! ($cart->cart_contents_count < intval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
												}
												break;
										}
									}
								}
							}			
						}
						

						// Rule passed conditions, so it applies. Calculate discounted price
						if ($passconditions === 'yes'){
							if ($have_bogo_number === NULL){
								$have_bogo_number = 'yes';
								// calculate discount and regular price based on $howmuch and discount type
								$smallest_bogo_number = $howmuch;
							} else {
								if ($howmuch < $smallest_bogo_number){
									$smallest_bogo_number = $howmuch;
								}   
							}
						} else {
							// do nothing
						}
					} //foreach end

					if($have_bogo_number !== NULL){
						$free_quantity = floor($item_quantity/$smallest_bogo_number);
						if ($free_quantity !== null && intval($free_quantity) !== 0){
							$product_name = wc_get_product($current_product_id)->get_name();
							// discount is item price x free quantity
							$discount_value = $item_price * $free_quantity;
							$decimals = apply_filters('b2bking_rounding_precision', get_option('woocommerce_price_num_decimals', 2));
							$cart->add_fee( $product_name.': '.esc_html__('Buy ','b2bking').$smallest_bogo_number.' '.esc_html__('Get 1 Free','b2bking'), -round($discount_value, $decimals));

						}
					}
				}// foreach cart item end
			}  // if is object cart end
		} 

		public static function b2bking_dynamic_rule_discount_regular_price( $regular_price, $product ){

			if (isset($_POST['_inline_edit'])){
				return $regular_price;
			}
			if (isset($_REQUEST['bulk_edit'])){
			    return $regular_price;
			}

			// Get current product
			$current_product_id = $product->get_id();
			// skip offers
			$offer_id = intval(get_option('b2bking_offer_product_id_setting', 0));
			$offer_id = apply_filters('b2bking_get_offer_product_id', $offer_id, $product->get_id(), 'discount');
			if (intval($current_product_id) === $offer_id || intval($current_product_id) === 3225464){ //3225464 is deprecated
				return $regular_price;
			}

			if( empty($regular_price) || $regular_price === 0 ){
				return $product->get_price();
			} else {
				return $regular_price;
			}
		}

		public static function b2bking_dynamic_rule_discount_sale_price( $sale_price, $product ){

			if (isset($_POST['_inline_edit'])){
				return $sale_price;
			}
			if (isset($_REQUEST['bulk_edit'])){
			    return $sale_price;
			}

			// Get current product
			$current_product_id = $product->get_id();

			// skip offers
			$offer_id = intval(get_option('b2bking_offer_product_id_setting', 0));
			$offer_id = apply_filters('b2bking_get_offer_product_id', $offer_id, $current_product_id, 'discount');
			if (intval($current_product_id) === $offer_id || intval($current_product_id) === 3225464){ //3225464 is deprecated
				return $sale_price;
			}

			$user_id = get_current_user_id();
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

			$response = b2bking()->get_applicable_rules('discount_everywhere', $current_product_id, $user_id);

			// abort early
			if ($response === 'norules'){
				return $sale_price;
			}

			$discount_rules = $response[0];
			$current_product_belongsto_array = $response[1];

			$regular_price = floatval($product->get_regular_price());

			// if multiple discount rules apply, give the smallest price to the user
			$have_discounted_price = NULL;
			$smallest_discounted_price = 0;
			foreach ($discount_rules as $discount_rule){

				// Get rule details
				$type = get_post_meta($discount_rule, 'b2bking_rule_what', true);
				$howmuch = get_post_meta($discount_rule, 'b2bking_rule_howmuch', true);
				$applies = explode('_',get_post_meta($discount_rule, 'b2bking_rule_applies', true));
				$rule_multiple_options = get_post_meta($discount_rule, 'b2bking_rule_applies_multiple_options', true);
				$rule_multiple_options_array = explode(',',$rule_multiple_options);
				$cart = WC()->cart;
				// Get conditions
				$passconditions = 'yes';
				$conditions = get_post_meta($discount_rule, 'b2bking_rule_conditions', true);
				if (!empty($conditions)){
					$conditions = explode('|',$conditions);

					if ($applies[0] === 'excluding'){
						// check that current product is not in excluded list
						$product_is_excluded = 'no';

						$variation_parent_id = wp_get_post_parent_id($current_product_id);

						foreach ($rule_multiple_options_array as $excluded_option){
							if ('product_'.$current_product_id === $excluded_option || 'product_'.$variation_parent_id === $excluded_option){
								$product_is_excluded = 'yes';
								break;
							} else {
								// check categories
								$current_product_categories = b2bking()->get_all_product_categories( $current_product_id, 'product_cat' );
								$parent_product_categories = b2bking()->get_all_product_categories( $variation_parent_id, 'product_cat' );
								$current_product_categories = array_merge($current_product_categories, $parent_product_categories);

								$current_product_belongsto_array = array_map(function($value) { return 'category_'.$value; }, $current_product_categories);

								foreach ($current_product_belongsto_array as $item_category){
									if ($item_category === $excluded_option){
										$product_is_excluded = 'yes';
										break 2;
									}
								}
							}
						}
						// check if product has any variations that are excluded. If yes, then exclude product too, so variations rule can apply.
						// Get children product variation IDs in an array
						$productobjj = wc_get_product($current_product_id);
						$children_ids = $productobjj->get_children();
						foreach ($children_ids as $child_id){
							if (in_array('product_'.$child_id, $rule_multiple_options_array)){
								$product_is_excluded = 'yes';
								break;
							}
						}
						if ($product_is_excluded === 'no'){
							// go forward with discount, check conditions
							foreach ($conditions as $condition){
								$condition_details = explode(';',$condition);

			    				if (substr($condition_details[0], -5) === 'value') {
									$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
								}
								
								switch ($condition_details[0]){
									case 'cart_total_value':
										switch ($condition_details[1]){
											case 'greater':
												if (! ($cart->cart_contents_total > intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'equal':
												if (! ($cart->cart_contents_total === intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'smaller':
												if (! ($cart->cart_contents_total < intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
										}
										break;
									
									case 'cart_total_quantity':
										switch ($condition_details[1]){
											case 'greater':
												if (! ($cart->cart_contents_count > intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'equal':
												if (! ($cart->cart_contents_count === intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'smaller':
												if (! ($cart->cart_contents_count < intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
										}
										break;
								}
							}
						}
					} else if ($applies[0] === 'multiple'){ // if rule is multiple products / categories rule
						$temporary_pass_conditions = 'no'; // if at least 1 element passes, change to yes
						// for each element that applies to the product and is part of the rule, check if at least 1 passes the conditions
						foreach($current_product_belongsto_array as $element){
							if(in_array($element, $rule_multiple_options_array)){
								$element_array = explode('_', $element);
								// if element is product or if element is category
								if ($element_array[0] === 'product'){
									$passes_inside_conditions = 'yes';
									$product_quantity = 0;
									if(is_object($cart)) {
										foreach($cart->get_cart() as $cart_item){
											if(intval($element_array[1]) === intval($cart_item['product_id']) || intval($element_array[1]) === intval($cart_item['variation_id'])){
												$product_quantity = $cart_item["quantity"];// Quantity
												break;
											}
										}
									}
									// check all product conditions against it
									foreach ($conditions as $condition){
										$condition_details = explode(';',$condition);

							    		if (substr($condition_details[0], -5) === 'value') {
											$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
										}
										switch ($condition_details[0]){
											case 'product_quantity':
												switch ($condition_details[1]){
													case 'greater':
														if (! ($product_quantity > intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
													case 'equal':
														if (! ($product_quantity === intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
													case 'smaller':
														if (! ($product_quantity < intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
												}
												break;
											case 'cart_total_quantity':
												switch ($condition_details[1]){
													case 'greater':
														if (! ($cart->cart_contents_count > intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
													case 'equal':
														if (! ($cart->cart_contents_count === intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
													case 'smaller':
														if (! ($cart->cart_contents_count < intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
												}
												break;
										}
									}
									if ($passes_inside_conditions === 'yes'){
										$temporary_pass_conditions = 'yes';
										break; // if 1 element passed, no need to check all other elements
									}
								} else if ($element_array[0] === 'category'){
									// check all category conditions against it + car total conditions
									$passes_inside_conditions = 'yes';
									$category_quantity = 0;
									if(is_object($cart)) {
										foreach($cart->get_cart() as $cart_item){
											if(b2bking()->b2bking_has_category($element_array[1], 'product_cat', $cart_item['product_id'])){
												$category_quantity += $cart_item["quantity"]; // add item quantity
											}
										}
									}
									foreach ($conditions as $condition){
										$condition_details = explode(';',$condition);

							    		if (substr($condition_details[0], -5) === 'value') {
											$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
										}
										switch ($condition_details[0]){
											case 'category_product_quantity':
												switch ($condition_details[1]){
													case 'greater':
														if (! ($category_quantity > intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
													case 'equal':
														if (! ($category_quantity === intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
													case 'smaller':
														if (! ($category_quantity < intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
												}
												break;
											case 'cart_total_quantity':
												switch ($condition_details[1]){
													case 'greater':
														if (! ($cart->cart_contents_count > intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
													case 'equal':
														if (! ($cart->cart_contents_count === intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
													case 'smaller':
														if (! ($cart->cart_contents_count < intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
												}
												break;
										}
									}
									if ($passes_inside_conditions === 'yes'){
										$temporary_pass_conditions = 'yes';
										break; // if 1 element passed, no need to check all other elements
									}
								}
							}
						} //foreach element end

						if ($temporary_pass_conditions === 'no'){
							$passconditions = 'no';
						}

					} else {

						$category_products_number = 0;
						$category_products_value = 0;
						$products_number = 0;
						$products_value = 0;
						$cart = WC()->cart;

						// Check rule is category rule or product rule
						if ($applies[0] === 'category'){

							// Calculate number of products in cart of this category AND total price of these products
							if(is_object($cart)) {
								foreach($cart->get_cart() as $cart_item){
									if(b2bking()->b2bking_has_category($applies[1], 'product_cat', $cart_item['product_id'])){
										$item_qty = $cart_item["quantity"];// Quantity
										$item_line_total = $cart_item["line_total"]; // Item total price (price x quantity)
										$category_products_number += $item_qty; // ctotal number of items in cart
										$category_products_value += $item_line_total; // calculated total items amount
									}
								}
							}
						} else if ($applies[0] === 'product') {
							if(is_object($cart)) {
								foreach($cart->get_cart() as $cart_item){
									if(intval($current_product_id) === intval($cart_item['product_id']) || intval($current_product_id) === intval($cart_item['variation_id'])){
										$item_qty = $cart_item["quantity"];// Quantity
										$products_number += $item_qty; // ctotal number of items in cart
										if (isset($cart_item["line_total"])){
											$item_line_total = $cart_item["line_total"]; // Item total price (price x quantity)
											$products_value += $item_line_total; // calculated total items amount
										}
									}
								}
							}
						}

						// Check discount conditions
						$passconditions = 'yes';
						$conditions = get_post_meta($discount_rule, 'b2bking_rule_conditions', true);

						if (!empty($conditions)){
							$conditions = explode('|',$conditions);
							foreach ($conditions as $condition){
								$condition_details = explode(';',$condition);

					    		if (substr($condition_details[0], -5) === 'value') {
									$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
								}
								switch ($condition_details[0]){
									case 'product_quantity':
										switch ($condition_details[1]){
											case 'greater':
												if (! ($products_number > intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'equal':
												if (! ($products_number === intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'smaller':
												if (! ($products_number < intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
										}
										break;
									
									case 'category_product_quantity':
										switch ($condition_details[1]){
											case 'greater':
												if (! ($category_products_number > intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'equal':
												if (! ($category_products_number === intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'smaller':
												if (! ($category_products_number < intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
										}
										break;
									
									case 'cart_total_quantity':
										switch ($condition_details[1]){
											case 'greater':
												if (! ($cart->cart_contents_count > intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'equal':
												if (! ($cart->cart_contents_count === intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'smaller':
												if (! ($cart->cart_contents_count < intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
										}
										break;
								}
							}
						}
					}
				}
					

				// Rule passed conditions, so it applies. Calculate discounted price
				if ($passconditions === 'yes'){
					if ($have_discounted_price === NULL){
						$have_discounted_price = 'yes';
						// calculate discount and regular price based on $howmuch and discount type
						if ($type === 'discount_amount'){
							$smallest_discounted_price = floatval($regular_price - $howmuch);
						} else if ($type === 'discount_percentage') {
							$smallest_discounted_price = floatval($regular_price - ($howmuch/100 * $regular_price));

							$raise_price = get_post_meta($discount_rule,'b2bking_rule_raise_price', true);
							if ($raise_price === 'yes'){
								$smallest_discounted_price = floatval($regular_price - (-1 * abs($howmuch)/100 * $regular_price));
							}
						}
					} else {
						if ($type === 'discount_amount'){
							$temporary_discounted_price = floatval($regular_price - $howmuch);
						} else if ($type === 'discount_percentage') {
							$temporary_discounted_price = floatval($regular_price - ($howmuch/100 * $regular_price));

							$raise_price = get_post_meta($discount_rule,'b2bking_rule_raise_price', true);
							if ($raise_price === 'yes'){
								$temporary_discounted_price = floatval($regular_price - (-1 * abs($howmuch)/100 * $regular_price));
							}
						}
						if ($temporary_discounted_price < $smallest_discounted_price){
							$smallest_discounted_price = $temporary_discounted_price;
						}   
					}
				} else {
					// do nothing
				}
			} //foreach end

			if($have_discounted_price !== NULL){
				$decimals = apply_filters('b2bking_rounding_precision', get_option('woocommerce_price_num_decimals', 2));

				return round($smallest_discounted_price, $decimals);
			} else {
				return $sale_price;
			}
		}

		public static function b2bking_dynamic_rule_discount_sale_price_variation_hash( $hash ) {
			// if dynamic rules have changed, clear pricing cache
			$rules_have_changed = get_option('b2bking_dynamic_rules_have_changed', 'no');
			if ($rules_have_changed === 'yes'){
				// clear cache
				if (apply_filters('b2bking_clear_wc_products_cache', true)){
					WC_Cache_Helper::get_transient_version( 'product', true );
				}
				update_option('b2bking_dynamic_rules_have_changed', 'no');
			}

			$hash[] = get_current_user_id();
			return $hash;
		}

		public static function b2bking_dynamic_rule_discount_display_dynamic_price( $price_html, $product ) {

			// Get current product
			$current_product_id = $product->get_id();

			// skip offers
			$offer_id = intval(get_option('b2bking_offer_product_id_setting', 0));
			$offer_id = apply_filters('b2bking_get_offer_product_id', $offer_id, $current_product_id, 'discount');
			if (intval($current_product_id) === $offer_id || intval($current_product_id) === 3225464){ //3225464 is deprecated
				return $price_html;
			}

			if( $product->is_type('variable') && !class_exists('WOOCS')) { // add WOOCS compatibility
				return $price_html;
			}
			// check if discount sale rules apply. If they do, show formatted sale price

			$user_id = get_current_user_id();
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

			$response = b2bking()->get_applicable_rules('discount_everywhere', $current_product_id, $user_id);

			// abort early
			if ($response === 'norules'){
				return $price_html;
			}

			$discount_rules = $response[0];
			$current_product_belongsto_array = $response[1];

			// if multiple discount rules apply, give the smallest price to the user
			$have_discounted_price = NULL;
			$smallest_discounted_price = 0;

			foreach ($discount_rules as $discount_rule){
				// Get rule details
				$type = get_post_meta($discount_rule, 'b2bking_rule_what', true);
				$howmuch = get_post_meta($discount_rule, 'b2bking_rule_howmuch', true);
				$applies = explode('_',get_post_meta($discount_rule, 'b2bking_rule_applies', true));
				$rule_multiple_options = get_post_meta($discount_rule, 'b2bking_rule_applies_multiple_options', true);
				$rule_multiple_options_array = explode(',',$rule_multiple_options);
				$cart = WC()->cart;
				// Get conditions
				$passconditions = 'yes';
				$conditions = get_post_meta($discount_rule, 'b2bking_rule_conditions', true);


				if (!empty($conditions)){
					$conditions = explode('|',$conditions);

					if ($applies[0] === 'excluding'){
						// check that current product is not in excluded list
						$product_is_excluded = 'no';

						$variation_parent_id = wp_get_post_parent_id($current_product_id);

						foreach ($rule_multiple_options_array as $excluded_option){
							if ('product_'.$current_product_id === $excluded_option || 'product_'.$variation_parent_id === $excluded_option){
								$product_is_excluded = 'yes';
								break;
							} else {
								// check categories
								$current_product_categories = b2bking()->get_all_product_categories( $current_product_id, 'product_cat' );
								$parent_product_categories = b2bking()->get_all_product_categories( $variation_parent_id, 'product_cat' );
								$current_product_categories = array_merge($current_product_categories, $parent_product_categories);

								$current_product_belongsto_array = array_map(function($value) { return 'category_'.$value; }, $current_product_categories);

								foreach ($current_product_belongsto_array as $item_category){
									if ($item_category === $excluded_option){
										$product_is_excluded = 'yes';
										break 2;
									}
								}
							}
						}
						// check if product has any variations that are excluded. If yes, then exclude product too, so variations rule can apply.
						// Get children product variation IDs in an array
						$productobjj = wc_get_product($current_product_id);
						$children_ids = $productobjj->get_children();
						foreach ($children_ids as $child_id){
							if (in_array('product_'.$child_id, $rule_multiple_options_array)){
								$product_is_excluded = 'yes';
								break;
							}
						}
						if ($product_is_excluded === 'no'){
							// go forward with discount, check conditions
							foreach ($conditions as $condition){
								$condition_details = explode(';',$condition);

					    		if (substr($condition_details[0], -5) === 'value') {
									$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
								}
								switch ($condition_details[0]){
									case 'cart_total_value':
										switch ($condition_details[1]){
											case 'greater':
												if (! ($cart->cart_contents_total > intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'equal':
												if (! ($cart->cart_contents_total === intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'smaller':
												if (! ($cart->cart_contents_total < intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
										}
										break;
									
									case 'cart_total_quantity':
										switch ($condition_details[1]){
											case 'greater':
												if (! ($cart->cart_contents_count > intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'equal':
												if (! ($cart->cart_contents_count === intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'smaller':
												if (! ($cart->cart_contents_count < intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
										}
										break;
								}
							}
						}
					} else if ($applies[0] === 'multiple'){ // if rule is multiple products / categories rule
						$temporary_pass_conditions = 'no'; // if at least 1 element passes, change to yes
						// for each element that applies to the product and is part of the rule, check if at least 1 passes the conditions
						foreach($current_product_belongsto_array as $element){
							if(in_array($element, $rule_multiple_options_array)){
								$element_array = explode('_', $element);
								// if element is product or if element is category
								if ($element_array[0] === 'product'){
									$passes_inside_conditions = 'yes';
									$product_quantity = 0;
									if(is_object($cart)) {
										foreach($cart->get_cart() as $cart_item){
											if(intval($element_array[1]) === intval($cart_item['product_id']) || intval($element_array[1]) === intval($cart_item['variation_id'])){
												$product_quantity = $cart_item["quantity"];// Quantity
												break;
											}
										}
									}
									// check all product conditions against it
									foreach ($conditions as $condition){
										$condition_details = explode(';',$condition);

							    		if (substr($condition_details[0], -5) === 'value') {
											$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
										}
										switch ($condition_details[0]){
											case 'product_quantity':
												switch ($condition_details[1]){
													case 'greater':
														if (! ($product_quantity > intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
													case 'equal':
														if (! ($product_quantity === intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
													case 'smaller':
														if (! ($product_quantity < intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
												}
												break;
											case 'cart_total_quantity':
												switch ($condition_details[1]){
													case 'greater':
														if (! ($cart->cart_contents_count > intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
													case 'equal':
														if (! ($cart->cart_contents_count === intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
													case 'smaller':
														if (! ($cart->cart_contents_count < intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
												}
												break;
										}
									}
									if ($passes_inside_conditions === 'yes'){
										$temporary_pass_conditions = 'yes';
										break; // if 1 element passed, no need to check all other elements
									}
								} else if ($element_array[0] === 'category'){
									// check all category conditions against it + car total conditions
									$passes_inside_conditions = 'yes';
									$category_quantity = 0;
									if(is_object($cart)) {
										foreach($cart->get_cart() as $cart_item){
											if(b2bking()->b2bking_has_category($element_array[1], 'product_cat', $cart_item['product_id'])){
												$category_quantity += $cart_item["quantity"]; // add item quantity
											}
										}
									}
									foreach ($conditions as $condition){
										$condition_details = explode(';',$condition);

							    		if (substr($condition_details[0], -5) === 'value') {
											$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
										}
										switch ($condition_details[0]){
											case 'category_product_quantity':
												switch ($condition_details[1]){
													case 'greater':
														if (! ($category_quantity > intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
													case 'equal':
														if (! ($category_quantity === intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
													case 'smaller':
														if (! ($category_quantity < intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
												}
												break;
											case 'cart_total_quantity':
												switch ($condition_details[1]){
													case 'greater':
														if (! ($cart->cart_contents_count > intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
													case 'equal':
														if (! ($cart->cart_contents_count === intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
													case 'smaller':
														if (! ($cart->cart_contents_count < intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
												}
												break;
										}
									}
									if ($passes_inside_conditions === 'yes'){
										$temporary_pass_conditions = 'yes';
										break; // if 1 element passed, no need to check all other elements
									}
								}
							}
						} //foreach element end

						if ($temporary_pass_conditions === 'no'){
							$passconditions = 'no';
						}

					} else {
						// Get rule details
						$type = get_post_meta($discount_rule, 'b2bking_rule_what', true);
						$howmuch = get_post_meta($discount_rule, 'b2bking_rule_howmuch', true);
						$applies = explode('_',get_post_meta($discount_rule, 'b2bking_rule_applies', true));

						$category_products_number = 0;
						$category_products_value = 0;
						$products_number = 0;
						$products_value = 0;
						$cart = WC()->cart;

						// Check rule is category rule or product rule
						if ($applies[0] === 'category'){

							// Calculate number of products in cart of this category AND total price of these products
							if(is_object($cart)) {
								foreach($cart->get_cart() as $cart_item){
									if(b2bking()->b2bking_has_category($applies[1], 'product_cat', $cart_item['product_id'])){
										$item_qty = $cart_item["quantity"];// Quantity
										$item_line_total = $cart_item["line_total"]; // Item total price (price x quantity)
										$category_products_number += $item_qty; // ctotal number of items in cart
										$category_products_value += $item_line_total; // calculated total items amount
									}
								}
							}
						} else if ($applies[0] === 'product') {
							if(is_object($cart)) {
								foreach($cart->get_cart() as $cart_item){
									if(intval($current_product_id) === intval($cart_item['product_id']) || intval($current_product_id) === intval($cart_item['variation_id'])){
										$item_qty = $cart_item["quantity"];// Quantity
										$item_line_total = $cart_item["line_total"]; // Item total price (price x quantity)
										$products_number += $item_qty; // ctotal number of items in cart
										$products_value += $item_line_total; // calculated total items amount
									}
								}
							}
						}

						// Check discount conditions
						$passconditions = 'yes';
						$conditions = get_post_meta($discount_rule, 'b2bking_rule_conditions', true);

						if (!empty($conditions)){
							$conditions = explode('|',$conditions);
							foreach ($conditions as $condition){
								$condition_details = explode(';',$condition);

					    		if (substr($condition_details[0], -5) === 'value') {
									$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
								}
								switch ($condition_details[0]){
									case 'product_quantity':
										switch ($condition_details[1]){
											case 'greater':
												if (! ($products_number > intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'equal':
												if (! ($products_number === intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'smaller':
												if (! ($products_number < intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
										}
										break;
									
									case 'category_product_quantity':
										switch ($condition_details[1]){
											case 'greater':
												if (! ($category_products_number > intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'equal':
												if (! ($category_products_number === intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'smaller':
												if (! ($category_products_number < intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
										}
										break;
									
									case 'cart_total_quantity':
										switch ($condition_details[1]){
											case 'greater':
												if (! ($cart->cart_contents_count > intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'equal':
												if (! ($cart->cart_contents_count === intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'smaller':
												if (! ($cart->cart_contents_count < intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
										}
										break;
								}
							}
						}
					}
				}
				

				// Rule passed conditions, so it applies. Calculate discounted price
				if ($passconditions === 'yes'){
					if ($have_discounted_price === NULL){
						$have_discounted_price = 'yes';
					}
				} else {
					// do nothing
				}
			} //foreach end

			if($have_discounted_price !== NULL){
				if( $product->is_type('variable') && class_exists('WOOCS')) { // add WOOCS compatibility

					global $WOOCS;
					$currrent = $WOOCS->current_currency;
					if ($currrent != $WOOCS->default_currency) {
						$currencies = $WOOCS->get_currencies();
						$rate = $currencies[$currrent]['rate'];

						// apply WOOCS rate to price_html
						$min_price = $product->get_variation_price( 'min' ) / ($rate);
						$max_price = $product->get_variation_price( 'max' ) / ($rate);

						if ($min_price !== $max_price){
							$price_html = wc_format_price_range( $min_price, $max_price );
						} else {
							$price_html = wc_price($min_price);
						}

						if (apply_filters('b2bking_clear_wc_products_cache', true)){
							WC_Cache_Helper::get_transient_version( 'product', true );
						}
					}

				} else { 

					$price_html = wc_format_sale_price( wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) ), wc_get_price_to_display(  $product, array( 'price' => $product->get_sale_price() ) ) ) . $product->get_price_suffix();

					$price_html = apply_filters('b2bking_dynamic_recalculate_sale_price_display', $price_html, $product, wc_get_price_to_display(  $product, array( 'price' => $product->get_sale_price() ) ));

				}
			} else {
				// do nothing
			}

			return $price_html;
		}

		public static function b2bking_dynamic_rule_discount_display_dynamic_price_in_cart( $cart ) {
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

			$currentusergroupidnr = b2bking()->get_user_group($user_id);
			if (!$currentusergroupidnr || empty($currentusergroupidnr)){
				$currentusergroupidnr = 'invalid';
			}
			// Iterate through each cart item
			if(is_object($cart)) {
				foreach( $cart->get_cart() as $cart_item ) {

					// skip offers
					$offer_id = intval(get_option('b2bking_offer_product_id_setting', 0));
					$offer_id = apply_filters('b2bking_get_offer_product_id', $offer_id, $cart_item['product_id'], 'discount');
					if (intval($cart_item['product_id']) === intval($offer_id) || intval($cart_item['product_id']) === 3225464){ //3225464 is deprecated
						continue;
					}

					if (isset($cart_item['variation_id']) && intval($cart_item['variation_id']) !== 0){
						$current_product_id = $cart_item['variation_id'];
						$product = new WC_Product_Variation($current_product_id);
					} else {
						$current_product_id = $cart_item['product_id'];
						$product = new WC_Product($current_product_id);
					}

					$response = b2bking()->get_applicable_rules('discount_everywhere', $current_product_id, $user_id);

					// abort early
					if ($response === 'norules'){
						continue;
					}

					$discount_rules = $response[0];
					$current_product_belongsto_array = $response[1];

					$regular_price = floatval($product->get_regular_price());

					
					// if multiple discount rules apply, give the smallest price to the user
					$have_discounted_price = NULL;
					$smallest_discounted_price = 0;

					foreach ($discount_rules as $discount_rule){
						// Get rule details
						$type = get_post_meta($discount_rule, 'b2bking_rule_what', true);
						$howmuch = get_post_meta($discount_rule, 'b2bking_rule_howmuch', true);
						$applies = explode('_',get_post_meta($discount_rule, 'b2bking_rule_applies', true));
						$rule_multiple_options = get_post_meta($discount_rule, 'b2bking_rule_applies_multiple_options', true);
						$rule_multiple_options_array = explode(',',$rule_multiple_options);
						$cart = WC()->cart;
						// Get conditions
						$passconditions = 'yes';
						$conditions = get_post_meta($discount_rule, 'b2bking_rule_conditions', true);
						
						if (!empty($conditions)){
							$conditions = explode('|',$conditions);

							if ($applies[0] === 'excluding'){
								// check that current product is not in excluded list
								$product_is_excluded = 'no';

								$variation_parent_id = wp_get_post_parent_id($current_product_id);

								foreach ($rule_multiple_options_array as $excluded_option){
									if ('product_'.$current_product_id === $excluded_option || 'product_'.$variation_parent_id === $excluded_option){
										$product_is_excluded = 'yes';
										break;
									} else {
										// check categories
										$current_product_categories = b2bking()->get_all_product_categories( $current_product_id, 'product_cat' );
										$parent_product_categories = b2bking()->get_all_product_categories( $variation_parent_id, 'product_cat' );
										$current_product_categories = array_merge($current_product_categories, $parent_product_categories);
										$current_product_belongsto_array = array_map(function($value) { return 'category_'.$value; }, $current_product_categories);

										foreach ($current_product_belongsto_array as $item_category){
											if ($item_category === $excluded_option){
												$product_is_excluded = 'yes';
												break 2;
											}
										}
									}
								}
								// check if product has any variations that are excluded. If yes, then exclude product too, so variations rule can apply.
								// Get children product variation IDs in an array
								$productobjj = wc_get_product($current_product_id);
								$children_ids = $productobjj->get_children();
								foreach ($children_ids as $child_id){
									if (in_array('product_'.$child_id, $rule_multiple_options_array)){
										$product_is_excluded = 'yes';
										break;
									}
								}
								if ($product_is_excluded === 'no'){
									// go forward with discount, check conditions
									foreach ($conditions as $condition){
										$condition_details = explode(';',$condition);

							    		if (substr($condition_details[0], -5) === 'value') {
											$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
										}
										switch ($condition_details[0]){
											case 'cart_total_value':
												switch ($condition_details[1]){
													case 'greater':
														if (! ($cart->cart_contents_total > intval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
													case 'equal':
														if (! ($cart->cart_contents_total === intval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
													case 'smaller':
														if (! ($cart->cart_contents_total < intval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
												}
												break;
											
											case 'cart_total_quantity':
												switch ($condition_details[1]){
													case 'greater':
														if (! ($cart->cart_contents_count > intval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
													case 'equal':
														if (! ($cart->cart_contents_count === intval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
													case 'smaller':
														if (! ($cart->cart_contents_count < intval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
												}
												break;
										}
									}
								}
							} else if ($applies[0] === 'multiple'){ // if rule is multiple products / categories rule
								$temporary_pass_conditions = 'no'; // if at least 1 element passes, change to yes
								// for each element that applies to the product and is part of the rule, check if at least 1 passes the conditions
								foreach($current_product_belongsto_array as $element){
									if(in_array($element, $rule_multiple_options_array)){
										$element_array = explode('_', $element);
										// if element is product or if element is category
										if ($element_array[0] === 'product'){
											$passes_inside_conditions = 'yes';
											$product_quantity = 0;
											if(is_object($cart)) {
												foreach($cart->get_cart() as $cart_item2){
													if(intval($element_array[1]) === intval($cart_item2['product_id']) || intval($element_array[1]) === intval($cart_item2['variation_id'])){
														$product_quantity = $cart_item2["quantity"];// Quantity
														break;
													}
												}
											}
											// check all product conditions against it
											foreach ($conditions as $condition){
												$condition_details = explode(';',$condition);

									    		if (substr($condition_details[0], -5) === 'value') {
													$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
												}
												switch ($condition_details[0]){
													case 'product_quantity':
														switch ($condition_details[1]){
															case 'greater':
																if (! ($product_quantity > intval($condition_details[2]))){
																	$passes_inside_conditions = 'no';
																	break 3;
																}
															break;
															case 'equal':
																if (! ($product_quantity === intval($condition_details[2]))){
																	$passes_inside_conditions = 'no';
																	break 3;
																}
															break;
															case 'smaller':
																if (! ($product_quantity < intval($condition_details[2]))){
																	$passes_inside_conditions = 'no';
																	break 3;
																}
															break;
														}
														break;
													case 'cart_total_quantity':
														switch ($condition_details[1]){
															case 'greater':
																if (! ($cart->cart_contents_count > intval($condition_details[2]))){
																	$passes_inside_conditions = 'no';
																	break 3;
																}
															break;
															case 'equal':
																if (! ($cart->cart_contents_count === intval($condition_details[2]))){
																	$passes_inside_conditions = 'no';
																	break 3;
																}
															break;
															case 'smaller':
																if (! ($cart->cart_contents_count < intval($condition_details[2]))){
																	$passes_inside_conditions = 'no';
																	break 3;
																}
															break;
														}
														break;
												}
											}
											if ($passes_inside_conditions === 'yes'){
												$temporary_pass_conditions = 'yes';
												break; // if 1 element passed, no need to check all other elements
											}
										} else if ($element_array[0] === 'category'){
											// check all category conditions against it + car total conditions
											$passes_inside_conditions = 'yes';
											$category_quantity = 0;
											if(is_object($cart)) {
												foreach($cart->get_cart() as $cart_item2){
													if(b2bking()->b2bking_has_category($element_array[1], 'product_cat', $cart_item2['product_id'])){
														$category_quantity += $cart_item2["quantity"]; // add item quantity
													}
												}
											}
											foreach ($conditions as $condition){
												$condition_details = explode(';',$condition);

									    		if (substr($condition_details[0], -5) === 'value') {
														$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
													}
												switch ($condition_details[0]){
													case 'category_product_quantity':
														switch ($condition_details[1]){
															case 'greater':
																if (! ($category_quantity > intval($condition_details[2]))){
																	$passes_inside_conditions = 'no';
																	break 3;
																}
															break;
															case 'equal':
																if (! ($category_quantity === intval($condition_details[2]))){
																	$passes_inside_conditions = 'no';
																	break 3;
																}
															break;
															case 'smaller':
																if (! ($category_quantity < intval($condition_details[2]))){
																	$passes_inside_conditions = 'no';
																	break 3;
																}
															break;
														}
														break;
													case 'cart_total_quantity':
														switch ($condition_details[1]){
															case 'greater':
																if (! ($cart->cart_contents_count > intval($condition_details[2]))){
																	$passes_inside_conditions = 'no';
																	break 3;
																}
															break;
															case 'equal':
																if (! ($cart->cart_contents_count === intval($condition_details[2]))){
																	$passes_inside_conditions = 'no';
																	break 3;
																}
															break;
															case 'smaller':
																if (! ($cart->cart_contents_count < intval($condition_details[2]))){
																	$passes_inside_conditions = 'no';
																	break 3;
																}
															break;
														}
														break;
												}
											}
											if ($passes_inside_conditions === 'yes'){
												$temporary_pass_conditions = 'yes';
												break; // if 1 element passed, no need to check all other elements
											}
										}
									}
								} //foreach element end

								if ($temporary_pass_conditions === 'no'){
									$passconditions = 'no';
								}

							} else {
								// Get rule details
								$type = get_post_meta($discount_rule, 'b2bking_rule_what', true);
								$howmuch = get_post_meta($discount_rule, 'b2bking_rule_howmuch', true);
								$applies = explode('_',get_post_meta($discount_rule, 'b2bking_rule_applies', true));

								$category_products_number = 0;
								$category_products_value = 0;
								$products_number = 0;
								$products_value = 0;
								$cart = WC()->cart;

								// Check rule is category rule or product rule
								if ($applies[0] === 'category'){

									// Calculate number of products in cart of this category AND total price of these products
									if(is_object($cart)) {
										foreach($cart->get_cart() as $cart_item2){
										if(b2bking()->b2bking_has_category($applies[1], 'product_cat', $cart_item2['product_id'])){
											$item_qty = $cart_item2["quantity"];// Quantity
											$item_line_total = $cart_item2["line_total"]; // Item total price (price x quantity)
											$category_products_number += $item_qty; // ctotal number of items in cart
											$category_products_value += $item_line_total; // calculated total items amount
										}
										}
									}
								} else if ($applies[0] === 'product') {
									if(is_object($cart)) {
										foreach($cart->get_cart() as $cart_item2){
										if(intval($current_product_id) === intval($cart_item2['product_id']) || intval($current_product_id) === intval($cart_item2['variation_id'])){
											$item_qty = $cart_item2["quantity"];// Quantity
											$item_line_total = $cart_item2["line_total"]; // Item total price (price x quantity)
											$products_number += $item_qty; // ctotal number of items in cart
											$products_value += $item_line_total; // calculated total items amount
										}
										}
									}
								}

								// Check discount conditions
								$passconditions = 'yes';
								$conditions = get_post_meta($discount_rule, 'b2bking_rule_conditions', true);

								if (!empty($conditions)){
									$conditions = explode('|',$conditions);
									foreach ($conditions as $condition){
										$condition_details = explode(';',$condition);

							    		if (substr($condition_details[0], -5) === 'value') {
											$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
										}
										switch ($condition_details[0]){
											case 'product_quantity':
												switch ($condition_details[1]){
													case 'greater':
														if (! ($products_number > intval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
													case 'equal':
														if (! ($products_number === intval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
													case 'smaller':
														if (! ($products_number < intval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
												}
												break;
											
											case 'category_product_quantity':
												switch ($condition_details[1]){
													case 'greater':
														if (! ($category_products_number > intval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
													case 'equal':
														if (! ($category_products_number === intval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
													case 'smaller':
														if (! ($category_products_number < intval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
												}
												break;
											
											case 'cart_total_quantity':
												switch ($condition_details[1]){
													case 'greater':
														if (! ($cart->cart_contents_count > intval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
													case 'equal':
														if (! ($cart->cart_contents_count === intval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
													case 'smaller':
														if (! ($cart->cart_contents_count < intval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
												}
												break;
										}
									}
								}
								
							}
						}


						// Rule passed conditions, so it applies. Calculate discounted price
						if ($passconditions === 'yes'){
							if ($have_discounted_price === NULL){
								$have_discounted_price = 'yes';
							}
						} else {
							// do nothing
						}
					} //foreach end

					if($have_discounted_price !== NULL){

						$price = $cart_item['data']->get_sale_price(); // get sale price

						// add WOOCS compatibility
						if (class_exists('WOOCS')) {
							global $WOOCS;
							$currrent = $WOOCS->current_currency;
							if ($currrent != $WOOCS->default_currency) {
								$currencies = $WOOCS->get_currencies();
								$rate = $currencies[$currrent]['rate'];
								$price = $price / ($rate);
							}
						}
						
						if ($price !== NULL && $price !== ''){
							$cart_item['data']->set_price( $price ); // Set the sale price
							if ($cart_item['variation_id'] !== 0 && $cart_item['variation_id'] !== NULL){
								$product_id_set = $cart_item['variation_id'];
							} else {
								$product_id_set = $cart_item['product_id'];
							}

						//	set_transient('b2bking_user_'.$user_id.'_product_'.$product_id_set.'_custom_set_price', $price);
							b2bking()->set_global_data('custom_set_price', $price, $product_id_set, $user_id);

						}
					} else {
						// do nothing
					}

				}
			}
		}

		public static function b2bking_dynamic_rule_discount_display_dynamic_price_in_cart_item( $price, $cart_item, $cart_item_key){
			// Get current product
			if (isset($_POST['_inline_edit'])){
				return $price;
			}
			if (isset($_REQUEST['bulk_edit'])){
			    return $price;
			}

			// skip offers
			$offer_id = intval(get_option('b2bking_offer_product_id_setting', 0));
			$offer_id = apply_filters('b2bking_get_offer_product_id', $offer_id, $cart_item['product_id'], 'discount');
			if (intval($cart_item['product_id']) === intval($offer_id) || intval($cart_item['product_id']) === 3225464){ //3225464 is deprecated
				return $price;
			}
			
			if (isset($cart_item['variation_id']) && intval($cart_item['variation_id']) !== 0){
				$current_product_id = $cart_item['variation_id'];
				$product = new WC_Product_Variation($current_product_id);
			} else {
				$current_product_id = $cart_item['product_id'];
				$product = new WC_Product($current_product_id);
			}

			$user_id = get_current_user_id();
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

			$response = b2bking()->get_applicable_rules('discount_everywhere', $current_product_id, $user_id);

			// abort early
			if ($response === 'norules'){
				return $price;
			}

			$discount_rules = $response[0];
			$current_product_belongsto_array = $response[1];

			$regular_price = floatval($product->get_regular_price());

			// if multiple discount rules apply, give the smallest price to the user
			$have_discounted_price = NULL;
			$smallest_discounted_price = 0;

			foreach ($discount_rules as $discount_rule){
				// Get rule details
				$type = get_post_meta($discount_rule, 'b2bking_rule_what', true);
				$howmuch = get_post_meta($discount_rule, 'b2bking_rule_howmuch', true);
				$applies = explode('_',get_post_meta($discount_rule, 'b2bking_rule_applies', true));
				$rule_multiple_options = get_post_meta($discount_rule, 'b2bking_rule_applies_multiple_options', true);
				$rule_multiple_options_array = explode(',',$rule_multiple_options);
				$cart = WC()->cart;
				// Get conditions
				$passconditions = 'yes';
				$conditions = get_post_meta($discount_rule, 'b2bking_rule_conditions', true);

				if (!empty($conditions)){
					$conditions = explode('|',$conditions);

					if ($applies[0] === 'excluding'){
						// check that current product is not in excluded list
						$product_is_excluded = 'no';

						$variation_parent_id = wp_get_post_parent_id($current_product_id);

						foreach ($rule_multiple_options_array as $excluded_option){
							if ('product_'.$current_product_id === $excluded_option || 'product_'.$variation_parent_id === $excluded_option){
								$product_is_excluded = 'yes';
								break;
							} else {
								// check categories
								$current_product_categories = b2bking()->get_all_product_categories( $current_product_id, 'product_cat' );
								$parent_product_categories = b2bking()->get_all_product_categories( $variation_parent_id, 'product_cat' );
								$current_product_categories = array_merge($current_product_categories, $parent_product_categories);
								$current_product_belongsto_array = array_map(function($value) { return 'category_'.$value; }, $current_product_categories);

								foreach ($current_product_belongsto_array as $item_category){
									if ($item_category === $excluded_option){
										$product_is_excluded = 'yes';
										break 2;
									}
								}
							}
						}
						// check if product has any variations that are excluded. If yes, then exclude product too, so variations rule can apply.
						// Get children product variation IDs in an array
						$productobjj = wc_get_product($current_product_id);
						$children_ids = $productobjj->get_children();
						foreach ($children_ids as $child_id){
							if (in_array('product_'.$child_id, $rule_multiple_options_array)){
								$product_is_excluded = 'yes';
								break;
							}
						}
						if ($product_is_excluded === 'no'){
							// go forward with discount, check conditions
							foreach ($conditions as $condition){
								$condition_details = explode(';',$condition);

			    				if (substr($condition_details[0], -5) === 'value') {
									$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
								}
								switch ($condition_details[0]){
									case 'cart_total_value':
										switch ($condition_details[1]){
											case 'greater':
												if (! ($cart->cart_contents_total > intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'equal':
												if (! ($cart->cart_contents_total === intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'smaller':
												if (! ($cart->cart_contents_total < intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
										}
										break;
									
									case 'cart_total_quantity':
										switch ($condition_details[1]){
											case 'greater':
												if (! ($cart->cart_contents_count > intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'equal':
												if (! ($cart->cart_contents_count === intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'smaller':
												if (! ($cart->cart_contents_count < intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
										}
										break;
								}
							}
						}
					} else if ($applies[0] === 'multiple'){ // if rule is multiple products / categories rule
						$temporary_pass_conditions = 'no'; // if at least 1 element passes, change to yes
						// for each element that applies to the product and is part of the rule, check if at least 1 passes the conditions
						foreach($current_product_belongsto_array as $element){
							if(in_array($element, $rule_multiple_options_array)){
								$element_array = explode('_', $element);
								// if element is product or if element is category
								if ($element_array[0] === 'product'){
									$passes_inside_conditions = 'yes';
									$product_quantity = 0;
									if(is_object($cart)) {
										foreach($cart->get_cart() as $cart_item2){
											if(intval($element_array[1]) === intval($cart_item2['product_id']) || intval($element_array[1]) === intval($cart_item2['variation_id'])){
												$product_quantity = $cart_item2["quantity"];// Quantity
												break;
											}
										}
									}
									// check all product conditions against it
									foreach ($conditions as $condition){
										$condition_details = explode(';',$condition);

					    				if (substr($condition_details[0], -5) === 'value') {
											$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
										}
										switch ($condition_details[0]){
											case 'product_quantity':
												switch ($condition_details[1]){
													case 'greater':
														if (! ($product_quantity > intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
													case 'equal':
														if (! ($product_quantity === intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
													case 'smaller':
														if (! ($product_quantity < intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
												}
												break;
											case 'cart_total_quantity':
												switch ($condition_details[1]){
													case 'greater':
														if (! ($cart->cart_contents_count > intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
													case 'equal':
														if (! ($cart->cart_contents_count === intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
													case 'smaller':
														if (! ($cart->cart_contents_count < intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
												}
												break;
										}
									}
									if ($passes_inside_conditions === 'yes'){
										$temporary_pass_conditions = 'yes';
										break; // if 1 element passed, no need to check all other elements
									}
								} else if ($element_array[0] === 'category'){
									// check all category conditions against it + car total conditions
									$passes_inside_conditions = 'yes';
									$category_quantity = 0;
									if(is_object($cart)) {
										foreach($cart->get_cart() as $cart_item2){
											if(b2bking()->b2bking_has_category($element_array[1], 'product_cat', $cart_item2['product_id'])){
												$category_quantity += $cart_item2["quantity"]; // add item quantity
											}
										}
									}
									foreach ($conditions as $condition){
										$condition_details = explode(';',$condition);

				    					if (substr($condition_details[0], -5) === 'value') {
											$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
										}
										switch ($condition_details[0]){
											case 'category_product_quantity':
												switch ($condition_details[1]){
													case 'greater':
														if (! ($category_quantity > intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
													case 'equal':
														if (! ($category_quantity === intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
													case 'smaller':
														if (! ($category_quantity < intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
												}
												break;
											case 'cart_total_quantity':
												switch ($condition_details[1]){
													case 'greater':
														if (! ($cart->cart_contents_count > intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
													case 'equal':
														if (! ($cart->cart_contents_count === intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
													case 'smaller':
														if (! ($cart->cart_contents_count < intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
												}
												break;
										}
									}
									if ($passes_inside_conditions === 'yes'){
										$temporary_pass_conditions = 'yes';
										break; // if 1 element passed, no need to check all other elements
									}
								}
							}
						} //foreach element end

						if ($temporary_pass_conditions === 'no'){
							$passconditions = 'no';
						}

					} else {
						// Get rule details
						$type = get_post_meta($discount_rule, 'b2bking_rule_what', true);
						$howmuch = get_post_meta($discount_rule, 'b2bking_rule_howmuch', true);
						$applies = explode('_',get_post_meta($discount_rule, 'b2bking_rule_applies', true));

						$category_products_number = 0;
						$category_products_value = 0;
						$products_number = 0;
						$products_value = 0;
						$cart = WC()->cart;

						// Check rule is category rule or product rule
						if ($applies[0] === 'category'){

							// Calculate number of products in cart of this category AND total price of these products
							if(is_object($cart)) {
								foreach($cart->get_cart() as $cart_item2){
									if(b2bking()->b2bking_has_category($applies[1], 'product_cat', $cart_item2['product_id'])){
										$item_qty = $cart_item2["quantity"];// Quantity
										$item_line_total = $cart_item2["line_total"]; // Item total price (price x quantity)
										$category_products_number += $item_qty; // ctotal number of items in cart
										$category_products_value += $item_line_total; // calculated total items amount
									}
								}
							}
						} else if ($applies[0] === 'product') {
							if(is_object($cart)) {
								foreach($cart->get_cart() as $cart_item2){
									if(intval($current_product_id) === intval($cart_item2['product_id']) || intval($current_product_id) === intval($cart_item2['variation_id'])){
										$item_qty = $cart_item2["quantity"];// Quantity
										$item_line_total = $cart_item2["line_total"]; // Item total price (price x quantity)
										$products_number += $item_qty; // ctotal number of items in cart
										$products_value += $item_line_total; // calculated total items amount
									}
								}
							}
						}

						// Check discount conditions
						$passconditions = 'yes';
						$conditions = get_post_meta($discount_rule, 'b2bking_rule_conditions', true);

						if (!empty($conditions)){
							$conditions = explode('|',$conditions);
							foreach ($conditions as $condition){
								$condition_details = explode(';',$condition);

					    		if (substr($condition_details[0], -5) === 'value') {
									$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
								}
								switch ($condition_details[0]){
									case 'product_quantity':
										switch ($condition_details[1]){
											case 'greater':
												if (! ($products_number > intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'equal':
												if (! ($products_number === intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'smaller':
												if (! ($products_number < intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
										}
										break;
									
									case 'category_product_quantity':
										switch ($condition_details[1]){
											case 'greater':
												if (! ($category_products_number > intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'equal':
												if (! ($category_products_number === intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'smaller':
												if (! ($category_products_number < intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
										}
										break;
									
									case 'cart_total_quantity':
										switch ($condition_details[1]){
											case 'greater':
												if (! ($cart->cart_contents_count > intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'equal':
												if (! ($cart->cart_contents_count === intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'smaller':
												if (! ($cart->cart_contents_count < intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
										}
										break;
								}
							}
						}
					}
				}
				


				// Rule passed conditions, so it applies. Calculate discounted price
				if ($passconditions === 'yes'){
					if ($have_discounted_price === NULL){
						$have_discounted_price = 'yes';
					}
				} else {
					// do nothing
				}
			} //foreach end

			if($have_discounted_price !== NULL){
				
				$discount_price = b2bking()->b2bking_wc_get_price_to_display( $product, array( 'price' => $cart_item['data']->get_sale_price() ) ); // get sale price
				
				if ($discount_price !== NULL && $discount_price !== ''){
					$decimals = apply_filters('b2bking_rounding_precision', get_option('woocommerce_price_num_decimals', 2));

					$price = wc_price(round($discount_price,$decimals)); 
				}
			} else {
				// do nothing
			}
			return $price;

		}

		public static function b2bking_dynamic_rule_discount_display_dynamic_sale_badge($text, $post, $product){
			// Check product and get discount text, if any

			// Get current product
			$current_product_id = $product->get_id();

			$user_id = get_current_user_id();
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

			$response = b2bking()->get_applicable_rules('discount_everywhere', $current_product_id, $user_id);

			// abort early
			if ($response === 'norules'){
				return $text;
			}

			$discount_rules = $response[0];
			$current_product_belongsto_array = $response[1];

			$regular_price = floatval($product->get_regular_price());

			// if multiple discount rules apply, give the smallest price to the user
			$have_discounted_price = NULL;
			$smallest_discount_name = '';
			$smallest_discounted_price = 0;

			foreach ($discount_rules as $discount_rule){
				// Get rule details
				$type = get_post_meta($discount_rule, 'b2bking_rule_what', true);
				$howmuch = get_post_meta($discount_rule, 'b2bking_rule_howmuch', true);
				$applies = explode('_',get_post_meta($discount_rule, 'b2bking_rule_applies', true));
				$rule_multiple_options = get_post_meta($discount_rule, 'b2bking_rule_applies_multiple_options', true);
				$rule_multiple_options_array = explode(',',$rule_multiple_options);
				$cart = WC()->cart;
				$discount_name = get_post_meta($discount_rule, 'b2bking_rule_discountname', true);
				// Get conditions
				$passconditions = 'yes';
				$conditions = get_post_meta($discount_rule, 'b2bking_rule_conditions', true);

				if (!empty($conditions)){
					$conditions = explode('|',$conditions);

					if ($applies[0] === 'excluding'){
						// check that current product is not in excluded list
						$product_is_excluded = 'no';
						$variation_parent_id = wp_get_post_parent_id($current_product_id);
										
						foreach ($rule_multiple_options_array as $excluded_option){
							if ('product_'.$current_product_id === $excluded_option || 'product_'.$variation_parent_id === $excluded_option){
								$product_is_excluded = 'yes';
								break;
							} else {
								// check categories
								$current_product_categories = b2bking()->get_all_product_categories( $current_product_id, 'product_cat' );
								$parent_product_categories = b2bking()->get_all_product_categories( $variation_parent_id, 'product_cat' );
								$current_product_categories = array_merge($current_product_categories, $parent_product_categories);
								$current_product_belongsto_array = array_map(function($value) { return 'category_'.$value; }, $current_product_categories);

								foreach ($current_product_belongsto_array as $item_category){
									if ($item_category === $excluded_option){
										$product_is_excluded = 'yes';
										break 2;
									}
								}
							}
						}
						// check if product has any variations that are excluded. If yes, then exclude product too, so variations rule can apply.
						// Get children product variation IDs in an array
						$productobjj = wc_get_product($current_product_id);
						$children_ids = $productobjj->get_children();
						foreach ($children_ids as $child_id){
							if (in_array('product_'.$child_id, $rule_multiple_options_array)){
								$product_is_excluded = 'yes';
								break;
							}
						}
						if ($product_is_excluded === 'no'){
							// go forward with discount, check conditions
							foreach ($conditions as $condition){
								$condition_details = explode(';',$condition);
					    		if (substr($condition_details[0], -5) === 'value') {
									$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
								}
								switch ($condition_details[0]){
									case 'cart_total_value':
										switch ($condition_details[1]){
											case 'greater':
												if (! ($cart->cart_contents_total > intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'equal':
												if (! ($cart->cart_contents_total === intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'smaller':
												if (! ($cart->cart_contents_total < intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
										}
										break;
									
									case 'cart_total_quantity':
										switch ($condition_details[1]){
											case 'greater':
												if (! ($cart->cart_contents_count > intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'equal':
												if (! ($cart->cart_contents_count === intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'smaller':
												if (! ($cart->cart_contents_count < intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
										}
										break;
								}
							}
						}
					} else if ($applies[0] === 'multiple'){ // if rule is multiple products / categories rule
						$temporary_pass_conditions = 'no'; // if at least 1 element passes, change to yes
						// for each element that applies to the product and is part of the rule, check if at least 1 passes the conditions
						foreach($current_product_belongsto_array as $element){
							if(in_array($element, $rule_multiple_options_array)){
								$element_array = explode('_', $element);
								// if element is product or if element is category
								if ($element_array[0] === 'product'){
									$passes_inside_conditions = 'yes';
									$product_quantity = 0;
									if(is_object($cart)) {
										foreach($cart->get_cart() as $cart_item){
											if(intval($element_array[1]) === intval($cart_item['product_id']) || intval($element_array[1]) === intval($cart_item['variation_id'])){
												$product_quantity = $cart_item["quantity"];// Quantity
												break;
											}
										}
									}
									// check all product conditions against it
									foreach ($conditions as $condition){
										$condition_details = explode(';',$condition);

							    		if (substr($condition_details[0], -5) === 'value') {
									$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
								}
										switch ($condition_details[0]){
											case 'product_quantity':
												switch ($condition_details[1]){
													case 'greater':
														if (! ($product_quantity > intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
													case 'equal':
														if (! ($product_quantity === intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
													case 'smaller':
														if (! ($product_quantity < intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
												}
												break;
											case 'cart_total_quantity':
												switch ($condition_details[1]){
													case 'greater':
														if (! ($cart->cart_contents_count > intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
													case 'equal':
														if (! ($cart->cart_contents_count === intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
													case 'smaller':
														if (! ($cart->cart_contents_count < intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
												}
												break;
										}
									}
									if ($passes_inside_conditions === 'yes'){
										$temporary_pass_conditions = 'yes';
										break; // if 1 element passed, no need to check all other elements
									}
								} else if ($element_array[0] === 'category'){
									// check all category conditions against it + car total conditions
									$passes_inside_conditions = 'yes';
									$category_quantity = 0;
									if(is_object($cart)) {
										foreach($cart->get_cart() as $cart_item){
											if(b2bking()->b2bking_has_category($element_array[1], 'product_cat', $cart_item['product_id'])){
												$category_quantity += $cart_item["quantity"]; // add item quantity
											}
										}
									}
									foreach ($conditions as $condition){
										$condition_details = explode(';',$condition);

							    		if (substr($condition_details[0], -5) === 'value') {
											$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
										}
										switch ($condition_details[0]){
											case 'category_product_quantity':
												switch ($condition_details[1]){
													case 'greater':
														if (! ($category_quantity > intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
													case 'equal':
														if (! ($category_quantity === intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
													case 'smaller':
														if (! ($category_quantity < intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
												}
												break;
											case 'cart_total_quantity':
												switch ($condition_details[1]){
													case 'greater':
														if (! ($cart->cart_contents_count > intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
													case 'equal':
														if (! ($cart->cart_contents_count === intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
													case 'smaller':
														if (! ($cart->cart_contents_count < intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
												}
												break;
										}
									}
									if ($passes_inside_conditions === 'yes'){
										$temporary_pass_conditions = 'yes';
										break; // if 1 element passed, no need to check all other elements
									}
								}
							}
						} //foreach element end

						if ($temporary_pass_conditions === 'no'){
							$passconditions = 'no';
						}

					} else {
						// Get rule details
						$type = get_post_meta($discount_rule, 'b2bking_rule_what', true);
						$howmuch = get_post_meta($discount_rule, 'b2bking_rule_howmuch', true);
						$applies = explode('_',get_post_meta($discount_rule, 'b2bking_rule_applies', true));
						$discount_name = get_post_meta($discount_rule, 'b2bking_rule_discountname', true);

						$category_products_number = 0;
						$category_products_value = 0;
						$products_number = 0;
						$products_value = 0;
						$cart = WC()->cart;

						// Check rule is category rule or product rule
						if ($applies[0] === 'category'){

							// Calculate number of products in cart of this category AND total price of these products
							if(is_object($cart)) {
								foreach($cart->get_cart() as $cart_item){
								if(b2bking()->b2bking_has_category($applies[1], 'product_cat', $cart_item['product_id'])){
									$item_qty = $cart_item["quantity"];// Quantity
									$item_line_total = $cart_item["line_total"]; // Item total price (price x quantity)
									$category_products_number += $item_qty; // ctotal number of items in cart
									$category_products_value += $item_line_total; // calculated total items amount
								}
								}
							}
						} else if ($applies[0] === 'product') {
							if(is_object($cart)) {
								foreach($cart->get_cart() as $cart_item){
								if(intval($current_product_id) === intval($cart_item['product_id']) || intval($current_product_id) === intval($cart_item['variation_id'])){
									$item_qty = $cart_item["quantity"];// Quantity
									$item_line_total = $cart_item["line_total"]; // Item total price (price x quantity)
									$products_number += $item_qty; // ctotal number of items in cart
									$products_value += $item_line_total; // calculated total items amount
								}
								}
							}
						}

						// Check discount conditions
						$passconditions = 'yes';
						$conditions = get_post_meta($discount_rule, 'b2bking_rule_conditions', true);

						if (!empty($conditions)){
							$conditions = explode('|',$conditions);
							foreach ($conditions as $condition){
								$condition_details = explode(';',$condition);

					    		if (substr($condition_details[0], -5) === 'value') {
									$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
								}
								switch ($condition_details[0]){
									case 'product_quantity':
										switch ($condition_details[1]){
											case 'greater':
												if (! ($products_number > intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'equal':
												if (! ($products_number === intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'smaller':
												if (! ($products_number < intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
										}
										break;
									
									case 'category_product_quantity':
										switch ($condition_details[1]){
											case 'greater':
												if (! ($category_products_number > intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'equal':
												if (! ($category_products_number === intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'smaller':
												if (! ($category_products_number < intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
										}
										break;
									
									case 'cart_total_quantity':
										switch ($condition_details[1]){
											case 'greater':
												if (! ($cart->cart_contents_count > intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'equal':
												if (! ($cart->cart_contents_count === intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'smaller':
												if (! ($cart->cart_contents_count < intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
										}
										break;
								}
							}
						}
					}		
				}
				

				// Rule passed conditions, so it applies. Calculate discounted price
				if ($passconditions === 'yes'){
					if ($have_discounted_price === NULL){
						$have_discounted_price = 'yes';
						$smallest_discount_name = $discount_name;
						// calculate discount and regular price based on $howmuch and discount type
						if ($type === 'discount_amount'){
							$smallest_discounted_price = floatval($regular_price - $howmuch);
						} else if ($type === 'discount_percentage') {
							$smallest_discounted_price = floatval($regular_price - ($howmuch/100 * $regular_price));

							$raise_price = get_post_meta($discount_rule,'b2bking_rule_raise_price', true);
							if ($raise_price === 'yes'){
								$smallest_discounted_price = floatval($regular_price - (-1 * abs($howmuch)/100 * $regular_price));
							}
						}
					} else {
						if ($type === 'discount_amount'){
							$temporary_discounted_price = floatval($regular_price - $howmuch);
						} else if ($type === 'discount_percentage') {
							$temporary_discounted_price = floatval($regular_price - ($howmuch/100 * $regular_price));

							$raise_price = get_post_meta($discount_rule,'b2bking_rule_raise_price', true);
							if ($raise_price === 'yes'){
								$temporary_discounted_price = floatval($regular_price - (-1 * abs($howmuch)/100 * $regular_price));
							}
						}
						if ($temporary_discounted_price < $smallest_discounted_price){
							$smallest_discounted_price = $temporary_discounted_price;
							$smallest_discount_name = $discount_name;
						}   
					}
				} else {
					// do nothing
				}
			} //foreach end

			if($have_discounted_price !== NULL && $smallest_discount_name !== '' && $smallest_discount_name !== NULL){
				$returned = str_replace( __( 'Sale!', 'woocommerce' ), $smallest_discount_name, $text );
			} else {
				$returned = $text;
			}

			if($have_discounted_price !== NULL){
				$returned = apply_filters('b2bking_dynamic_recalculate_sale_price_badge',$returned);
			}

			return $returned;
   
		}

		
		public static function b2bking_dynamic_rule_add_tax_fee( WC_Cart $cart ){

			$user_id = get_current_user_id();

		//	$tax_rules = get_transient('b2bking_tax_rules_'.get_current_user_id());
			$tax_rules = b2bking()->get_global_data('b2bking_tax_rules', false, get_current_user_id());
			if (!$tax_rules){

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

				// Get all dynamic rule tax fees amounts and percentages
				$tax_rules_ids = get_option('b2bking_have_add_tax_rules_list_ids', '');
				if (!empty($tax_rules_ids)){
					$tax_rules_ids = explode(',',$tax_rules_ids);
				} else {
					$tax_rules_ids = array();
				}

				$tax_rules = get_posts([
					'post_type' => 'b2bking_rule',
					'post__in' => $tax_rules_ids,
					'fields'        => 'ids', // Only get post IDs
					'numberposts' => -1,
					'meta_query'=> array(
						$array_who,
					)
				]);

			//	set_transient ('b2bking_tax_rules_'.get_current_user_id(), $tax_rules);
				b2bking()->set_global_data('b2bking_tax_rules', $tax_rules, false, get_current_user_id());
			}


			foreach ($tax_rules as $tax_rule){
				// Get rule details
				$taxname = get_post_meta(apply_filters( 'wpml_object_id', $tax_rule, 'post' ), 'b2bking_rule_taxname', true);
				$type = get_post_meta($tax_rule, 'b2bking_rule_what', true);
				$howmuch = get_post_meta($tax_rule, 'b2bking_rule_howmuch', true);
				$applies = explode('_',get_post_meta($tax_rule, 'b2bking_rule_applies', true));
				$taxtaxable = get_post_meta($tax_rule, 'b2bking_rule_tax_taxable', true);
				if ($taxtaxable === 'yes'){
					$taxtaxable = true;
				} else {
					$taxtaxable = false;
				}

				if ($applies[0] === 'multiple'){
					$rule_multiple_options = get_post_meta($tax_rule, 'b2bking_rule_applies_multiple_options', true);
					$rule_multiple_options_array = explode(',',$rule_multiple_options);
					//foreach rule element
					foreach ($rule_multiple_options_array as $rule_element){
						$rule_element_array = explode('_',$rule_element);
						// if is category
						if ($rule_element_array[0] === 'category'){
							$category_products_number = 0;
							$category_products_value = 0;
							if(is_object($cart)) {
								foreach($cart->get_cart() as $cart_item){
								if(b2bking()->b2bking_has_category($rule_element_array[1], 'product_cat', $cart_item['product_id'])){
									$item_qty = $cart_item["quantity"];// Quantity
									$item_line_total = $cart_item["line_total"]; // Item total price (price x quantity)
									$category_products_number += $item_qty; // ctotal number of items in cart
									$category_products_value += $item_line_total; // calculated total items amount
								}
								}
							}

							$passconditions = 'yes';
							$conditions = get_post_meta($tax_rule, 'b2bking_rule_conditions', true);

							if (!empty($conditions)){
								$conditions = explode('|',$conditions);

								foreach ($conditions as $condition){
									$condition_details = explode(';',$condition);

						    		if (substr($condition_details[0], -5) === 'value') {
										$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
									}
									switch ($condition_details[0]){
									
										case 'category_product_quantity':
											switch ($condition_details[1]){
												case 'greater':
													if (! ($category_products_number > intval($condition_details[2]))){
														$passconditions = 'no';
														break 3;
													}
												break;
												case 'equal':
													if (! ($category_products_number === intval($condition_details[2]))){
														$passconditions = 'no';
														break 3;
													}
												break;
												case 'smaller':
													if (! ($category_products_number < intval($condition_details[2]))){
														$passconditions = 'no';
														break 3;
													}
												break;
											}
											break;

										case 'category_product_value':
											switch ($condition_details[1]){
												case 'greater':
													if (! ($category_products_value > intval($condition_details[2]))){
														$passconditions = 'no';
														break 3;
													}
												break;
												case 'equal':
													if (! ($category_products_value === intval($condition_details[2]))){
														$passconditions = 'no';
														break 3;
													}
												break;
												case 'smaller':
													if (! ($category_products_value < intval($condition_details[2]))){
														$passconditions = 'no';
														break 3;
													}
												break;
											}
											break;
										
										case 'cart_total_quantity':
											switch ($condition_details[1]){
												case 'greater':
													if (! ($cart->cart_contents_count > intval($condition_details[2]))){
														$passconditions = 'no';
														break 3;
													}
												break;
												case 'equal':
													if (! ($cart->cart_contents_count === intval($condition_details[2]))){
														$passconditions = 'no';
														break 3;
													}
												break;
												case 'smaller':
													if (! ($cart->cart_contents_count < intval($condition_details[2]))){
														$passconditions = 'no';
														break 3;
													}
												break;
											}
											break;

										case 'cart_total_value':
											switch ($condition_details[1]){
												case 'greater':
													if (! (apply_filters('b2bking_addtax_order_total_calculation_basis', WC()->cart->cart_contents_total) > intval($condition_details[2]))){
														$passconditions = 'no';
														break 3;
													}
												break;
												case 'equal':
													if (! (apply_filters('b2bking_addtax_order_total_calculation_basis', WC()->cart->cart_contents_total) === intval($condition_details[2]))){
														$passconditions = 'no';
														break 3;
													}
												break;
												case 'smaller':
													if (! (apply_filters('b2bking_addtax_order_total_calculation_basis', WC()->cart->cart_contents_total) < intval($condition_details[2]))){
														$passconditions = 'no';
														break 3;
													}
												break;
											}
											break;
										
									}
								}
							}

							// Passed conditions
							if ($passconditions === 'yes'){
								if ($type === 'add_tax_amount'){
									$tax = $howmuch * apply_filters('b2bking_add_tax_apply_times', $category_products_number);
								} else if ($type === 'add_tax_percentage'){
									$tax = round((($howmuch * $category_products_value)/100), 4);
								}
							} else {
								// do nothing
								$tax = NULL;
							}
							if (isset($tax)){
								$category_name = get_term( $rule_element_array[1] )->name;
								$decimals = apply_filters('b2bking_rounding_precision', get_option('woocommerce_price_num_decimals', 2));
								if ($tax !== null && intval($tax) !== 0){
									$cart->add_fee( esc_html($taxname.' '.$category_name), round($tax, $decimals), apply_filters('b2bking_taxable_fee', $taxtaxable, $taxname), apply_filters('b2bking_taxable_fee_tax_class', ''));
								}
							}

						// if is product
						} else if ($rule_element_array[0] === 'product'){
							$products_number = 0;
							$products_value = 0;
							if(is_object($cart)) {
								foreach($cart->get_cart() as $cart_item){
								if(intval($rule_element_array[1]) === intval($cart_item['product_id']) || intval($rule_element_array[1]) === intval($cart_item['variation_id'])){
									$item_qty = $cart_item["quantity"];// Quantity
									$item_line_total = $cart_item["line_total"]; // Item total price (price x quantity)
									$products_number += $item_qty; // ctotal number of items in cart
									$products_value += $item_line_total; // calculated total items amount
								}
								}
							}

							$passconditions = 'yes';
							$conditions = get_post_meta($tax_rule, 'b2bking_rule_conditions', true);

							if (!empty($conditions)){
								$conditions = explode('|',$conditions);

								foreach ($conditions as $condition){
									$condition_details = explode(';',$condition);

						    		if (substr($condition_details[0], -5) === 'value') {
										$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
									}
									switch ($condition_details[0]){
									
										case 'product_quantity':
											switch ($condition_details[1]){
												case 'greater':
													if (! ($products_number > intval($condition_details[2]))){
														$passconditions = 'no';
														break 3;
													}
												break;
												case 'equal':
													if (! ($products_number === intval($condition_details[2]))){
														$passconditions = 'no';
														break 3;
													}
												break;
												case 'smaller':
													if (! ($products_number < intval($condition_details[2]))){
														$passconditions = 'no';
														break 3;
													}
												break;
											}
											break;

											case 'product_value':
												switch ($condition_details[1]){
													case 'greater':
														if (! ($products_value > intval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
													case 'equal':
														if (! ($products_value === intval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
													case 'smaller':
														if (! ($products_value < intval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
												}
												break;
										
										case 'cart_total_quantity':
											switch ($condition_details[1]){
												case 'greater':
													if (! ($cart->cart_contents_count > intval($condition_details[2]))){
														$passconditions = 'no';
														break 3;
													}
												break;
												case 'equal':
													if (! ($cart->cart_contents_count === intval($condition_details[2]))){
														$passconditions = 'no';
														break 3;
													}
												break;
												case 'smaller':
													if (! ($cart->cart_contents_count < intval($condition_details[2]))){
														$passconditions = 'no';
														break 3;
													}
												break;
											}
											break;

										case 'cart_total_value':
											switch ($condition_details[1]){
												case 'greater':
													if (! (apply_filters('b2bking_addtax_order_total_calculation_basis', WC()->cart->cart_contents_total) > intval($condition_details[2]))){
														$passconditions = 'no';
														break 3;
													}
												break;
												case 'equal':
													if (! (apply_filters('b2bking_addtax_order_total_calculation_basis', WC()->cart->cart_contents_total) === intval($condition_details[2]))){
														$passconditions = 'no';
														break 3;
													}
												break;
												case 'smaller':
													if (! (apply_filters('b2bking_addtax_order_total_calculation_basis', WC()->cart->cart_contents_total) < intval($condition_details[2]))){
														$passconditions = 'no';
														break 3;
													}
												break;
											}
											break;
										
									}
								}
							}

							// Passed conditions
							if ($passconditions === 'yes'){
								if ($type === 'add_tax_amount'){
									$tax = $howmuch * apply_filters('b2bking_add_tax_apply_times', $products_number);
								} else if ($type === 'add_tax_percentage'){
									$tax = round((($howmuch * $products_value)/100), 4);
								}
							} else {
								// do nothing
								$tax = NULL;
							}
							if (isset($tax)){
								if ($tax !== null && intval($tax) !== 0){
									$product_name = get_the_title(intval($rule_element_array[1]));
									$decimals = apply_filters('b2bking_rounding_precision', get_option('woocommerce_price_num_decimals', 2));

									$cart->add_fee( esc_html($taxname.' '.$product_name), round($tax, $decimals), apply_filters('b2bking_taxable_fee', $taxtaxable, $taxname), apply_filters('b2bking_taxable_fee_tax_class', ''));
								}
							}

						}
					}
				} else {

					$category_products_number = 0;
					$category_products_value = 0;
					$products_number = 0;
					$products_value = 0;

					// Check rule is category rule or product rule
					if ($applies[0] === 'category'){

						// Calculate number of products in cart of this category AND total price of these products
						if(is_object($cart)) {	
							foreach($cart->get_cart() as $cart_item){
							if(b2bking()->b2bking_has_category($applies[1], 'product_cat', $cart_item['product_id'])){
								$item_qty = $cart_item["quantity"];// Quantity
								$item_line_total = $cart_item["line_total"]; // Item total price (price x quantity)
								$category_products_number += $item_qty; // ctotal number of items in cart
								$category_products_value += $item_line_total; // calculated total items amount
							}
							}
						}
					} else if ($applies[0] === 'product') {
						if(is_object($cart)) {
							foreach($cart->get_cart() as $cart_item){
							if(intval($applies[1]) === intval($cart_item['product_id']) || intval($applies[1]) === intval($cart_item['variation_id'])){
								$item_qty = $cart_item["quantity"];// Quantity
								$item_line_total = $cart_item["line_total"]; // Item total price (price x quantity)
								$products_number += $item_qty; // ctotal number of items in cart
								$products_value += $item_line_total; // calculated total items amount
							}
							}
						}
					} else if ($applies[0] === 'excluding'){
						if(is_object($cart)) {
							foreach($cart->get_cart() as $cart_item){
								if (isset($cart_item['variation_id']) && intval($cart_item['variation_id']) !== 0){
									$current_product_id = $cart_item['variation_id'];
								} else {
									$current_product_id = $cart_item['product_id'];
								}

								// check that current product is not in list
								$multiple_options = get_post_meta($tax_rule,'b2bking_rule_applies_multiple_options', true);
								$multiple_options_array = explode(',', $multiple_options);

								$product_is_excluded = 'no';
								if (in_array('product_'.$current_product_id, $multiple_options_array)){
									$product_is_excluded = 'yes';
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
											$product_is_excluded = 'yes';
											break;
										}
									}
								}
								if ($product_is_excluded === 'no'){
									// product is not excluded, therefore rule applies
									$item_qty = $cart_item["quantity"];// Quantity
									$item_line_total = $cart_item["line_total"]; // Item total price (price x quantity)
									$products_number += $item_qty; // ctotal number of items in cart
									$products_value += $item_line_total; // calculated total items amount
								}
							}
						}
						

						
					}

					// Check discount conditions
					$passconditions = 'yes';
					$conditions = get_post_meta($tax_rule, 'b2bking_rule_conditions', true);

					if (!empty($conditions)){

						$conditions = explode('|',$conditions);
						foreach ($conditions as $condition){
							$condition_details = explode(';',$condition);

				    		if (substr($condition_details[0], -5) === 'value') {
									$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
								}
							switch ($condition_details[0]){
								case 'product_quantity':
									switch ($condition_details[1]){
										case 'greater':
											if (! ($products_number > intval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
										case 'equal':
											if (! ($products_number === intval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
										case 'smaller':
											if (! ($products_number < intval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
									}
									break;

								case 'product_value':
									switch ($condition_details[1]){
										case 'greater':
											if (! ($products_value > intval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
										case 'equal':
											if (! ($products_value === intval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
										case 'smaller':
											if (! ($products_value < intval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
									}
									break;
								
								case 'category_product_quantity':
									switch ($condition_details[1]){
										case 'greater':
											if (! ($category_products_number > intval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
										case 'equal':
											if (! ($category_products_number === intval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
										case 'smaller':
											if (! ($category_products_number < intval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
									}
									break;

								case 'category_product_value':
									switch ($condition_details[1]){
										case 'greater':
											if (! ($category_products_value > intval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
										case 'equal':
											if (! ($category_products_value === intval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
										case 'smaller':
											if (! ($category_products_value < intval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
									}
									break;
								
								case 'cart_total_quantity':
									switch ($condition_details[1]){
										case 'greater':
											if (! ($cart->cart_contents_count > intval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
										case 'equal':
											if (! ($cart->cart_contents_count === intval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
										case 'smaller':
											if (! ($cart->cart_contents_count < intval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
									}
									break;

								case 'cart_total_value':
									switch ($condition_details[1]){
										case 'greater':
											if (! (apply_filters('b2bking_addtax_order_total_calculation_basis', WC()->cart->cart_contents_total) > intval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
										case 'equal':
											if (! (apply_filters('b2bking_addtax_order_total_calculation_basis', WC()->cart->cart_contents_total) === intval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
										case 'smaller':
											if (! (apply_filters('b2bking_addtax_order_total_calculation_basis', WC()->cart->cart_contents_total) < intval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
									}
									break;
								
							}
						}
					}

					// Passed conditions
					if ($passconditions === 'yes'){
						if ($applies[0] === 'one'){
							if ($type === 'add_tax_amount'){
								$tax = $howmuch;
							} else if ($type === 'add_tax_percentage'){
								// check if shipping should be included
								$shipping_included = get_post_meta($tax_rule, 'b2bking_rule_tax_shipping', true);
								$cart = WC()->cart;
								$shipping_cost = $cart->get_shipping_total();
								if ($shipping_included === 'no'){
									$tax = round((($howmuch * apply_filters('b2bking_addtax_order_total_calculation_basis', WC()->cart->cart_contents_total))/100), 4);
								} else {
									$tax = round((($howmuch * (apply_filters('b2bking_addtax_order_total_calculation_basis', WC()->cart->cart_contents_total)+$shipping_cost))/100), 4);
								}
							}
						} else if ($applies[0] === 'cart'){
							if ($type === 'add_tax_amount'){
								$tax = $howmuch * $cart->cart_contents_count;
							} else if ($type === 'add_tax_percentage'){
								$tax = round((($howmuch * apply_filters('b2bking_addtax_order_total_calculation_basis', WC()->cart->cart_contents_total))/100), 4);
							}
						} else if ($applies[0] === 'category'){
							if ($type === 'add_tax_amount'){
								$tax = $howmuch * $category_products_number;
							} else if ($type === 'add_tax_percentage'){
								$tax = round((($howmuch * $category_products_value)/100), 4);
							}
						} else if ($applies[0] === 'product'){
							if ($type === 'add_tax_amount'){
								$tax = $howmuch * apply_filters('b2bking_add_tax_apply_times', $products_number);
							} else if ($type === 'add_tax_percentage'){
								$tax = round((($howmuch * $products_value)/100), 4);
							}
						} else if ($applies[0] === 'excluding'){
							if ($type === 'add_tax_amount'){
								$tax = $howmuch * apply_filters('b2bking_add_tax_apply_times', $products_number);
							} else if ($type === 'add_tax_percentage'){
								$tax = round((($howmuch * $products_value)/100), 4);
							}
						}
					} else {
						// do nothing
						$tax = null;
					}
					if (isset($tax)){
						if ($tax !== null && floatval($tax)>0){
							$decimals = apply_filters('b2bking_rounding_precision', get_option('woocommerce_price_num_decimals', 2));
							$cart->add_fee( esc_html($taxname), round($tax, $decimals), apply_filters('b2bking_taxable_fee', $taxtaxable, $taxname), apply_filters('b2bking_taxable_fee_tax_class', ''));
						}
					}
				}

			}
		}

		// Dynamic rule fixed price
		// Dynamic rule fixed price
		public static function b2bking_dynamic_rule_fixed_price( $price, $product ) {

			if (isset($_POST['_inline_edit'])){
				return $price;
			}
			if (isset($_REQUEST['bulk_edit'])){
			    return $price;
			}

			// not applicable to offers
			$offer_id_prod = get_option('b2bking_offer_product_id_setting', 0);
			$offer_id_prod = apply_filters('b2bking_get_offer_product_id', $offer_id_prod, $product->get_id(), 'fixed_price');
			if ($product->get_id() === intval ($offer_id_prod)){
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
		//	if (get_transient('b2bking_user_'.$user_id.'_product_'.$product->get_id().'_custom_set_price') === $price){
			if (b2bking()->get_global_data('custom_set_price',$product->get_id(), $user_id) === $price){
				return $price;
			}

			$currentusergroupidnr = b2bking()->get_user_group($user_id);
			if (!$currentusergroupidnr || empty($currentusergroupidnr)){
				$currentusergroupidnr = 'invalid';
			}

			// Get current product
			$current_product_id = $product->get_id();

			$response = b2bking()->get_applicable_rules('fixed_price', $current_product_id, $user_id);

			// abort early
			if ($response === 'norules'){
				return $price;
			}

			$fixed_price_rules = $response[0];
			$current_product_belongsto_array = $response[1];




			// if multiple fixed price rules apply, give the smallest price to the user
			$have_fixed_price = NULL;
			$smallest_fixed_price = 0;

			foreach ($fixed_price_rules as $fixed_price_rule){
				// Get rule details
				$type = get_post_meta($fixed_price_rule, 'b2bking_rule_what', true);
				$howmuch = get_post_meta($fixed_price_rule, 'b2bking_rule_howmuch', true);
				$applies = explode('_',get_post_meta($fixed_price_rule, 'b2bking_rule_applies', true));
				$rule_multiple_options = get_post_meta($fixed_price_rule, 'b2bking_rule_applies_multiple_options', true);
				$rule_multiple_options_array = explode(',',$rule_multiple_options);
				$cart = WC()->cart;
				// Get conditions
				$passconditions = 'yes';
				$conditions = get_post_meta($fixed_price_rule, 'b2bking_rule_conditions', true);

				if (!empty($conditions)){
					$conditions = explode('|',$conditions);

					if ($applies[0] === 'multiple'){ // if rule is multiple products / categories rule
						$temporary_pass_conditions = 'no'; // if at least 1 element passes, change to yes
						// for each element that applies to the product and is part of the rule, check if at least 1 passes the conditions
						foreach($current_product_belongsto_array as $element){
							if(in_array($element, $rule_multiple_options_array)){
								$element_array = explode('_', $element);
								// if element is product or if element is category
								if ($element_array[0] === 'product'){
									$passes_inside_conditions = 'yes';
									$product_quantity = 0;
									if(is_object($cart)) {
										foreach($cart->get_cart() as $cart_item){
										if(intval($element_array[1]) === intval($cart_item['product_id'])){
											$product_quantity = $cart_item["quantity"];// Quantity
											break;
										}
										}
									}
									// check all product conditions against it
									foreach ($conditions as $condition){
										$condition_details = explode(';',$condition);

			    		$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
										switch ($condition_details[0]){
											case 'product_quantity':
												switch ($condition_details[1]){
													case 'greater':
														if (intval($condition_details[2]) !== 0){
															if (! ($product_quantity > intval($condition_details[2]))){
																$passes_inside_conditions = 'no';
																break 3;
															}
														}
													break;
													case 'equal':
														if (intval($condition_details[2]) !== 0){
															if (! ($product_quantity === intval($condition_details[2]))){
																$passes_inside_conditions = 'no';
																break 3;
															}
														}
													break;
													case 'smaller':
														if (! ($product_quantity < intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
												}
												break;
											case 'cart_total_quantity':
												switch ($condition_details[1]){
													case 'greater':
														if (intval($condition_details[2]) !== 0){
															if (! ($cart->cart_contents_count > intval($condition_details[2]))){
																$passes_inside_conditions = 'no';
																break 3;
															}
														}
													break;
													case 'equal':
														if (intval($condition_details[2]) !== 0){
															if (! ($cart->cart_contents_count === intval($condition_details[2]))){
																$passes_inside_conditions = 'no';
																break 3;
															}
														}
													break;
													case 'smaller':
														if (! ($cart->cart_contents_count < intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
												}
												break;
										}
									}
									if ($passes_inside_conditions === 'yes'){
										$temporary_pass_conditions = 'yes';
										break; // if 1 element passed, no need to check all other elements
									}
								} else if ($element_array[0] === 'category'){
									// check all category conditions against it + car total conditions
									$passes_inside_conditions = 'yes';
									$category_quantity = 0;
									if(is_object($cart)) {
										foreach($cart->get_cart() as $cart_item){
											if(b2bking()->b2bking_has_category($element_array[1], 'product_cat', $cart_item['product_id'])){
												$category_quantity += $cart_item["quantity"]; // add item quantity
											}
										}
									}
									foreach ($conditions as $condition){
										$condition_details = explode(';',$condition);

			    		$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
										switch ($condition_details[0]){
											case 'category_product_quantity':
												switch ($condition_details[1]){
													case 'greater':
														if (intval($condition_details[2]) !== 0){
															if (! ($category_quantity > intval($condition_details[2]))){
																$passes_inside_conditions = 'no';
																break 3;
															}
														}
													break;
													case 'equal':
														if (intval($condition_details[2]) !== 0){
															if (! ($category_quantity === intval($condition_details[2]))){
																$passes_inside_conditions = 'no';
																break 3;
															}
														}
													break;
													case 'smaller':
														if (! ($category_quantity < intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
												}
												break;
											case 'cart_total_quantity':
												switch ($condition_details[1]){
													case 'greater':
														if (intval($condition_details[2]) !== 0){
															if (! ($cart->cart_contents_count > intval($condition_details[2]))){
																$passes_inside_conditions = 'no';
																break 3;
															}
														}
													break;
													case 'equal':
														if (intval($condition_details[2]) !== 0){
															if (! ($cart->cart_contents_count === intval($condition_details[2]))){
																$passes_inside_conditions = 'no';
																break 3;
															}
														}
													break;
													case 'smaller':
														if (! ($cart->cart_contents_count < intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
												}
												break;
										}
									}
									if ($passes_inside_conditions === 'yes'){
										$temporary_pass_conditions = 'yes';
										break; // if 1 element passed, no need to check all other elements
									}
								}
							}
						} //foreach element end

						if ($temporary_pass_conditions === 'no'){
							$passconditions = 'no';
						}

					} else { // if rule is simple product, category, or total cart role 
						$category_products_number = 0;
						$category_products_value = 0;
						$products_number = 0;
						$products_value = 0;
						

						// Check rule is category rule or product rule
						if ($applies[0] === 'category'){

							// Calculate number of products in cart of this category AND total price of these products
							if(is_object($cart)) {
								foreach($cart->get_cart() as $cart_item){
								if(b2bking()->b2bking_has_category($applies[1], 'product_cat', $cart_item['product_id'])){
									$item_qty = $cart_item["quantity"];// Quantity
									$item_line_total = $cart_item["line_total"]; // Item total price (price x quantity)
									$category_products_number += $item_qty; // ctotal number of items in cart
									$category_products_value += $item_line_total; // calculated total items amount
								}
								}
							}
						} else if ($applies[0] === 'product') {
							if(is_object($cart)) {
								foreach($cart->get_cart() as $cart_item){
								if(intval($current_product_id) === intval($cart_item['product_id'])){
									$item_qty = $cart_item["quantity"];// Quantity
									if (isset($cart_item['line_total'])){
										$item_line_total = $cart_item["line_total"]; // Item total price (price x quantity)
										$products_value += $item_line_total; // calculated total items amount
									} 
									$products_number += $item_qty; // ctotal number of items in cart
								
								}
								}
							}
						}

						foreach ($conditions as $condition){
							$condition_details = explode(';',$condition);

			    		$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
							switch ($condition_details[0]){
								case 'product_quantity':
									switch ($condition_details[1]){
										case 'greater':
											if (intval($condition_details[2]) !== 0){
												if (! ($products_number > intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											}
										break;
										case 'equal':
											if (intval($condition_details[2]) !== 0){
												if (! ($products_number === intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											}
										break;
										case 'smaller':
											if (! ($products_number < intval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
									}
									break;
								
								case 'category_product_quantity':
									switch ($condition_details[1]){
										case 'greater':
											if (intval($condition_details[2]) !== 0){
												if (! ($category_products_number > intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											}
										break;
										case 'equal':
											if (intval($condition_details[2]) !== 0){
												if (! ($category_products_number === intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											}
										break;
										case 'smaller':
											if (! ($category_products_number < intval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
									}
									break;
								
								case 'cart_total_quantity':
									switch ($condition_details[1]){
										case 'greater':
											if (intval($condition_details[2]) !== 0){
												if (! ($cart->cart_contents_count > intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											}
										break;
										case 'equal':
											if (intval($condition_details[2]) !== 0){
												if (! ($cart->cart_contents_count === intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											}
										break;
										case 'smaller':
											if (! ($cart->cart_contents_count < intval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
									}
									break;
								
							}
						}
					}		
				}
				

				// Passed conditions
				if ($passconditions === 'yes'){
					if ($have_fixed_price === NULL){
						$have_fixed_price = 'yes';
						$smallest_fixed_price = floatval($howmuch);
					} else {
						if (floatval($howmuch) < $smallest_fixed_price){
							$smallest_fixed_price = floatval($howmuch);
						}   
					}
				} else {
					// do nothing
				}

			} //foreach end
			if($have_fixed_price !== NULL){

				// add WOOCS compatibility
				if (class_exists('WOOCS')) {
					global $WOOCS;
					$currrent = $WOOCS->current_currency;
					if ($currrent != $WOOCS->default_currency) {
						$currencies = $WOOCS->get_currencies();
						$rate = $currencies[$currrent]['rate'];
						$smallest_fixed_price = $smallest_fixed_price * $rate;
					}
				}
				
				return $smallest_fixed_price;

			} else {
				return $price;
			}
		}

		// Dynamic rule free shipping
		public static function b2bking_dynamic_rule_free_shipping( $is_available, $package, $shipping_method ) {
			$user_id = get_current_user_id();
			$cart = WC()->cart;

			$free_shipping = $is_available;

		//	$free_shipping_rules = get_transient('b2bking_free_shipping_'.get_current_user_id());
			$free_shipping_rules = b2bking()->get_global_data('b2bking_free_shipping', false, get_current_user_id());
			if (!$free_shipping_rules){

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
				// Get all free shipping dynamic rules that apply to the user or the user's group
				$free_shipping_ids = get_option('b2bking_have_free_shipping_rules_list_ids', '');
				if (!empty($free_shipping_ids)){
					$free_shipping_ids = explode(',',$free_shipping_ids);
				} else {
					$free_shipping_ids = array();
				}

				$free_shipping_rules = get_posts([
					'post_type' => 'b2bking_rule',
					'post_status' => 'publish',
					'fields'        => 'ids', // Only get post IDs
					'numberposts' => -1,
					'post__in' => $free_shipping_ids,
					'meta_query'=> array(
						'relation' => 'AND',
						$array_who,
					)
				]);

			//	set_transient ('b2bking_free_shipping_'.get_current_user_id(), $free_shipping_rules);
				b2bking()->set_global_data('b2bking_free_shipping',$free_shipping_rules, false, get_current_user_id());

			}

			foreach ($free_shipping_rules as $free_shipping_rule){
				// Get rule details
				$applies = explode('_',get_post_meta($free_shipping_rule, 'b2bking_rule_applies', true));
				$passconditions = 'yes';
				$conditions = get_post_meta($free_shipping_rule, 'b2bking_rule_conditions', true);

				$conditions = explode('|',$conditions);

				if ($applies[0] === 'multiple'){
					$rule_multiple_options = get_post_meta($free_shipping_rule, 'b2bking_rule_applies_multiple_options', true);
					$rule_multiple_options_array = explode(',',$rule_multiple_options);
					foreach ($rule_multiple_options_array as $rule_element){
						$rule_element_array = explode('_',$rule_element);
						// if is category
						if ($rule_element_array[0] === 'category'){
							$category_products_number = 0;
							$category_products_value = 0;
							if(is_object($cart)) {
								foreach($cart->get_cart() as $cart_item){
									if(b2bking()->b2bking_has_category($rule_element_array[1], 'product_cat', $cart_item['product_id'])){
										$item_price = $cart_item['data']->get_price(); 
										$item_qty = $cart_item["quantity"];// Quantity
										$item_line_total = $cart_item["line_total"]; // Item total price (price x quantity)
										$category_products_number += $item_qty; // ctotal number of items in cart
										$category_products_value += $item_line_total; // calculated total items amount
									}
								}
							}

							foreach ($conditions as $condition){
								$condition_details = explode(';',$condition);

					    		if (substr($condition_details[0], -5) === 'value') {
									$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
								}
								switch ($condition_details[0]){
																		
									case 'category_product_quantity':
										switch ($condition_details[1]){
											case 'greater':
												if (! ($category_products_number > intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'equal':
												if (! ($category_products_number === intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'smaller':
												if (! ($category_products_number < intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
										}
										break;
																		
									case 'category_product_value':
										switch ($condition_details[1]){
											case 'greater':
												if (! (floatval($category_products_value) > floatval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'equal':
												if (! (floatval($category_products_value) === floatval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'smaller':
												if (! (floatval($category_products_value) < floatval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
										}
										break;
									case 'cart_total_quantity':
										switch ($condition_details[1]){
											case 'greater':
												if (! ($cart->cart_contents_count > intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'equal':
												if (! ($cart->cart_contents_count === intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'smaller':
												if (! ($cart->cart_contents_count < intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
										}
										break;
									case 'cart_total_value':
										switch ($condition_details[1]){
											case 'greater':
												if (! (floatval(apply_filters('b2bking_cart_subtotal', WC()->cart->get_subtotal())) > floatval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'equal':
												if (! (floatval(apply_filters('b2bking_cart_subtotal', WC()->cart->get_subtotal())) === floatval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'smaller':
												if (! (floatval(apply_filters('b2bking_cart_subtotal', WC()->cart->get_subtotal())) < floatval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
										}
										break;
								}
							}

							// Passed conditions
							if ($passconditions === 'yes'){
								$free_shipping = true;
								break 2; // break out of foreach. No need to check the other free shipping rules anymore
							}
						} else if ($rule_element_array[0] === 'product'){
							$products_number = 0;
							$products_value = 0;
							if(is_object($cart)) {
								foreach($cart->get_cart() as $cart_item){
								if(intval($rule_element_array[1]) === $cart_item['product_id'] || intval($rule_element_array[1]) === $cart_item['variation_id']){
									$item_price = $cart_item['data']->get_price(); 
									$item_qty = $cart_item["quantity"];// Quantity
									$item_line_total = $cart_item["line_total"]; // Item total price (price x quantity)
									$products_number += $item_qty; // ctotal number of items in cart
									$products_value += $item_line_total; // calculated total items amount
								}
								}
							}
							foreach ($conditions as $condition){
								$condition_details = explode(';',$condition);

					    		if (substr($condition_details[0], -5) === 'value') {
									$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
								}
								switch ($condition_details[0]){
																		
									case 'product_quantity':
										switch ($condition_details[1]){
											case 'greater':
												if (! ($products_number > intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'equal':
												if (! ($products_number === intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'smaller':
												if (! ($products_number < intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
										}
										break;

									case 'product_value':
										switch ($condition_details[1]){
											case 'greater':
												if (! (floatval($products_value) > floatval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'equal':
												if (! (floatval($products_value) === floatval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'smaller':
												if (! (floatval($products_value) < floatval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
										}
										break;

									case 'cart_total_quantity':
										switch ($condition_details[1]){
											case 'greater':
												if (! ($cart->cart_contents_count > intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'equal':
												if (! ($cart->cart_contents_count === intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'smaller':
												if (! ($cart->cart_contents_count < intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
										}
										break;
									case 'cart_total_value':
										switch ($condition_details[1]){
											case 'greater':
												if (! (floatval(apply_filters('b2bking_cart_subtotal', WC()->cart->get_subtotal())) > floatval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'equal':
												if (! (floatval(apply_filters('b2bking_cart_subtotal', WC()->cart->get_subtotal())) === floatval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'smaller':
												if (! (floatval(apply_filters('b2bking_cart_subtotal', WC()->cart->get_subtotal())) < floatval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
										}
										break;
								}
							}

							// Passed conditions
							if ($passconditions === 'yes'){
								$free_shipping = true;
								break 2; // break out of foreach. No need to check the other free shipping rules anymore
							}
						}
					}
				} else {

					$category_products_number = 0;
					$category_products_value = 0;
					$products_number = 0;
					$products_value = 0;

					// Check rule is category rule or product rule
					if ($applies[0] === 'category'){

						// Calculate number of products in cart of this category AND total price of these products
						if(is_object($cart)) {
							foreach($cart->get_cart() as $cart_item){
							if(b2bking()->b2bking_has_category($applies[1], 'product_cat', $cart_item['product_id'])){
								$item_price = $cart_item['data']->get_price(); 
								$item_qty = $cart_item["quantity"];// Quantity
								$item_line_total = $cart_item["line_total"]; // Item total price (price x quantity)
								$category_products_number += $item_qty; // ctotal number of items in cart
								$category_products_value += $item_line_total; // calculated total items amount
							}
							}
						}
					} else if ($applies[0] === 'product') {
						if(is_object($cart)) {
							foreach($cart->get_cart() as $cart_item){
							if(intval($applies[1]) === $cart_item['product_id'] || intval($applies[1]) === $cart_item['variation_id']){
								$item_price = $cart_item['data']->get_price(); 
								$item_qty = $cart_item["quantity"];// Quantity
								$item_line_total = $cart_item["line_total"]; // Item total price (price x quantity)
								$products_number += $item_qty; // ctotal number of items in cart
								$products_value += $item_line_total; // calculated total items amount
							}
							}
						}
					}

					// Check rule conditions
					$passconditions = 'yes';
					$conditions = get_post_meta($free_shipping_rule, 'b2bking_rule_conditions', true);

					if (!empty($conditions)){
						$conditions = explode('|',$conditions);
						foreach ($conditions as $condition){
							$condition_details = explode(';',$condition);

				    		if (substr($condition_details[0], -5) === 'value') {
									$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
								}
							switch ($condition_details[0]){
								case 'product_quantity':
									switch ($condition_details[1]){
										case 'greater':
											if (! ($products_number > intval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
										case 'equal':
											if (! ($products_number === intval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
										case 'smaller':
											if (! ($products_number < intval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
									}
									break;
								
								case 'category_product_quantity':
									switch ($condition_details[1]){
										case 'greater':
											if (! ($category_products_number > intval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
										case 'equal':
											if (! ($category_products_number === intval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
										case 'smaller':
											if (! ($category_products_number < intval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
									}
									break;
								case 'product_value':
									switch ($condition_details[1]){
										case 'greater':
											if (! (floatval($products_value) > floatval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
										case 'equal':
											if (! (floatval($products_value) === floatval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
										case 'smaller':
											if (! (floatval($products_value) < floatval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
									}
									break;
								
								case 'category_product_value':
									switch ($condition_details[1]){
										case 'greater':
											if (! (floatval($category_products_value) > floatval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
										case 'equal':
											if (! (floatval($category_products_value) === floatval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
										case 'smaller':
											if (! (floatval($category_products_value) < floatval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
									}
									break;
								case 'cart_total_quantity':
									switch ($condition_details[1]){
										case 'greater':
											if (! ($cart->cart_contents_count > intval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
										case 'equal':
											if (! ($cart->cart_contents_count === intval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
										case 'smaller':
											if (! ($cart->cart_contents_count < intval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
									}
									break;
								case 'cart_total_value':
									switch ($condition_details[1]){
										case 'greater':
											if (! (floatval(apply_filters('b2bking_cart_subtotal', WC()->cart->get_subtotal())) > floatval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
										case 'equal':
											if (! (floatval(apply_filters('b2bking_cart_subtotal', WC()->cart->get_subtotal())) === floatval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
										case 'smaller':
											if (! (floatval(apply_filters('b2bking_cart_subtotal', WC()->cart->get_subtotal())) < floatval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
									}
									break;
							}
						}
					}
					

					// Passed conditions
					if ($passconditions === 'yes'){
						$free_shipping = true;
						break; // break out of foreach. No need to check the other free shipping rules anymore
					}
				}	
				
			}

			// if no free shipping rule, return default
			if (empty($free_shipping_rules)){
				return $is_available;
			}

			return $free_shipping;

		}


	// Dynamic rule minimum / maximum order
	public static function b2bking_dynamic_minmax_order_amount() {
		$user_id = get_current_user_id();
		$cart = WC()->cart;

		//$dynamic_minmax_rules = get_transient('b2bking_minmax_'.get_current_user_id());
		$dynamic_minmax_rules = b2bking()->get_global_data('b2bking_minmax', false, get_current_user_id());
		if (!$dynamic_minmax_rules){

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

			$rules_ids_elements = get_option('b2bking_have_minmax_rules_list_ids_elements', array());

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

			//set_transient ('b2bking_minmax_'.get_current_user_id(), $user_applicable_rules);
			b2bking()->set_global_data('b2bking_minmax', $user_applicable_rules, false, get_current_user_id());
			$dynamic_minmax_rules = $user_applicable_rules;

		}

		// parse all rules and create an array of the smallest minimums and largest maximums, for all the elements,  
		// elements = cart total, categories, products

		$computed_rules = array( 
		    'minimum_order' => array( 
		        	'quantity' => array(), 
		        	'value' => array()
		    ),
		    'maximum_order' => array( 
		        	'quantity' => array(),
		        	'value' => array()
		    )
		);

		foreach($dynamic_minmax_rules as $dynamic_minmax_rule){
			$applies = get_post_meta($dynamic_minmax_rule, 'b2bking_rule_applies', true);
			$minimum_maximum = get_post_meta($dynamic_minmax_rule, 'b2bking_rule_what', true);
			$quantity_value = get_post_meta($dynamic_minmax_rule, 'b2bking_rule_quantity_value', true);
			$howmuch = floatval(get_post_meta($dynamic_minmax_rule, 'b2bking_rule_howmuch', true));

			if ($quantity_value === 'value'){
				// woocs
				$howmuch = b2bking()->get_woocs_price($howmuch);
			}

			$minimumall = intval(get_post_meta($dynamic_minmax_rule, 'b2bking_rule_minimum_all', true));

			if ($applies === 'multiple_options'){
				$rule_applies_multiple = get_post_meta($dynamic_minmax_rule, 'b2bking_rule_applies_multiple_options', true);
				$rule_applies_multiple_elements = explode(',',$rule_applies_multiple);
				foreach ($rule_applies_multiple_elements as $rule_applies_element){
					if (!isset($computed_rules[$minimum_maximum][$quantity_value][$rule_applies_element])){
						$computed_rules[$minimum_maximum][$quantity_value][$rule_applies_element] = $howmuch;
					} else {
						if ($minimum_maximum === 'minimum_order'){
							$computed_rules[$minimum_maximum][$quantity_value][$rule_applies_element] = min($computed_rules[$minimum_maximum][$quantity_value][$rule_applies_element], $howmuch);
						} else {
							$computed_rules[$minimum_maximum][$quantity_value][$rule_applies_element] = max($computed_rules[$minimum_maximum][$quantity_value][$rule_applies_element], $howmuch);
						}
					}
				}
			} else {
				if (!isset($computed_rules[$minimum_maximum][$quantity_value][$applies])){
					$computed_rules[$minimum_maximum][$quantity_value][$applies] = $howmuch;
				} else {
					if ($minimum_maximum === 'minimum_order'){
						$computed_rules[$minimum_maximum][$quantity_value][$applies] = min($computed_rules[$minimum_maximum][$quantity_value][$applies], $howmuch);
					} else {
						$computed_rules[$minimum_maximum][$quantity_value][$applies] = max($computed_rules[$minimum_maximum][$quantity_value][$applies], $howmuch);
					}
				}
			}
		}

		$error = 0;

		foreach($dynamic_minmax_rules as $dynamic_minmax_rule){
			// get rule details
			$minimum_maximum = get_post_meta($dynamic_minmax_rule, 'b2bking_rule_what', true);
			$quantity_value = get_post_meta($dynamic_minmax_rule, 'b2bking_rule_quantity_value', true);
			$howmuch = floatval(get_post_meta($dynamic_minmax_rule, 'b2bking_rule_howmuch', true));

			if ($quantity_value === 'value'){
				// woocs
				$howmuch = b2bking()->get_woocs_price($howmuch);
			}

			$applies = get_post_meta($dynamic_minmax_rule, 'b2bking_rule_applies', true);
			$minimumall = get_post_meta($dynamic_minmax_rule, 'b2bking_rule_minimum_all', true);
			if ($applies === 'cart_total'){
				// only if largest/smallest
				if ($howmuch === $computed_rules[$minimum_maximum][$quantity_value][$applies]){
					if ($quantity_value === 'value'){

						// includes tax by default
						$order_total_calc_basis = apply_filters('b2bking_minmax_order_total_calculation_basis', WC()->cart->cart_contents_total + WC()->cart->tax_total);

						if ($minimum_maximum === 'minimum_order'){
							if ( floatval($order_total_calc_basis) < $howmuch ) {
								$error = 1;
								if( is_cart() ) {
									wc_print_notice( 
										sprintf( esc_html__('Your current order total is %s — you must have an order with a minimum of %s to place your order ','b2bking') , 
											wc_price( $order_total_calc_basis ), 
											apply_filters('b2bking_minmax_value_display', wc_price( $howmuch ))
										), 'error' 
									);
								} else {
									wc_add_notice( 
										sprintf( esc_html__('Your current order total is %s — you must have an order with a minimum of %s to place your order','b2bking') , 
											wc_price( $order_total_calc_basis ), 
											apply_filters('b2bking_minmax_value_display', wc_price( $howmuch ))
										), 'error' 
									);
								}
							}
						} else if ($minimum_maximum === 'maximum_order'){
							if ( floatval($order_total_calc_basis) > $howmuch ) {
								$error = 1;
								if( is_cart() ) {
									wc_print_notice( 
										sprintf( esc_html__('Your current order total is %s — you must have an order with a maximum of %s to place your order ','b2bking') , 
											wc_price( $order_total_calc_basis ), 
											apply_filters('b2bking_minmax_value_display', wc_price( $howmuch ))
										), 'error' 
									);
								} else {
									wc_add_notice( 
										sprintf( esc_html__('Your current order total is %s — you must have an order with a maximum of %s to place your order','b2bking') , 
											wc_price( $order_total_calc_basis ), 
											apply_filters('b2bking_minmax_value_display', wc_price( $howmuch ))
										), 'error' 
									);
								}
							}
						}
					} else if ($quantity_value === 'quantity'){
						if ($minimum_maximum === 'minimum_order'){
							if ( WC()->cart->cart_contents_count < intval($howmuch) ) {
								$error = 1;
								if( is_cart() ) {
									wc_print_notice( 
										sprintf( esc_html__('Your current order quantity total is %s — you must have an order with a minimum quantity of %s to place your order ','b2bking') , 
											WC()->cart->cart_contents_count, 
											$howmuch
										), 'error' 
									);
								} else {
									wc_add_notice( 
										sprintf( esc_html__('Your current order quantity total is %s — you must have an order with a minimum quantity of %s to place your order ','b2bking') , 
											WC()->cart->cart_contents_count, 
											$howmuch 
										), 'error' 
									);
								}
							}
						} else if ($minimum_maximum === 'maximum_order'){
							if ( WC()->cart->cart_contents_count > intval($howmuch) ) {
								$error = 1;
								if( is_cart() ) {
									wc_print_notice( 
										sprintf( esc_html__('Your current order quantity total is %s — the maximum total quantity you can order is %s ','b2bking') , 
											WC()->cart->cart_contents_count , 
											$howmuch 
										), 'error' 
									);
								} else {
									wc_add_notice( 
										sprintf( esc_html__('Your current order quantity total is %s — the maximum total quantity you can order is %s ','b2bking') , 
											WC()->cart->cart_contents_count, 
											$howmuch
										), 'error' 
									);
								}
							}
						}
					}
				}
			} else {
				// rule is category or product rule or multiple select rule
				$appliesnew = $applies;
				$applies = explode('_',$applies);
				if ($applies[0] === 'category'){
					// rule is category rule
					// meaning that if the category exists in cart, category products must be in min / max quantity/value
					// get category products quantity and total value in cart
					$category_name = get_term( $applies[1] )->name;
					$category_products_number = 0;
					$category_products_value = 0;
					if(is_object($cart)) {
						foreach($cart->get_cart() as $cart_item){
						if(b2bking()->b2bking_has_category($applies[1], 'product_cat', $cart_item['product_id'])){
							$item_price = $cart_item['data']->get_price(); 
							$item_qty = $cart_item["quantity"];// Quantity
							$item_line_total = $cart_item["line_total"]+$cart_item['line_tax']; // Item total price (price x quantity)
							$category_products_number += $item_qty; // ctotal number of items in cart
							$category_products_value += $item_line_total; // calculated total items amount
						}
						}
					}
					// if category exists in cart, continue process
					if ($category_products_number !== 0){
						// only if largest/smallest
						if ($howmuch === $computed_rules[$minimum_maximum][$quantity_value][$appliesnew]){
							if ($quantity_value === 'value'){
								if ($minimum_maximum === 'minimum_order'){
									if ( floatval($category_products_value) < $howmuch ) {
										$error = 1;
										if( is_cart() ) {
											wc_print_notice( 
												sprintf( esc_html__('Your current order value total of products in category %s is %s — the minimum value you can order is %s ','b2bking') , 
													$category_name,
													wc_price ($category_products_value), 
													wc_price ($howmuch) 
												), 'error' 
											);
										} else {
											wc_add_notice( 
												sprintf( esc_html__('Your current order value total of products in category %s is %s — the minimum value you can order is %s ','b2bking') , 
													$category_name,
													wc_price ($category_products_value), 
													wc_price ($howmuch) 
												), 'error' 
											);
										}
									}
								} else if ($minimum_maximum === 'maximum_order'){
									if ( floatval($category_products_value) > $howmuch ) {
										$error = 1;
										if( is_cart() ) {
											wc_print_notice( 
												sprintf( esc_html__('Your current order value total of products in category %s is %s — the maximum value you can order is %s ','b2bking') , 
													$category_name,
													wc_price ($category_products_value), 
													wc_price ($howmuch) 
												), 'error' 
											);
										} else {
											wc_add_notice( 
												sprintf( esc_html__('Your current order value total of products in category %s is %s — the maximum value you can order is %s ','b2bking') , 
													$category_name,
													wc_price ($category_products_value), 
													wc_price ($howmuch) 
												), 'error' 
											);
										}
									}
								}
							} else if ($quantity_value === 'quantity'){
								if ($minimum_maximum === 'minimum_order'){
									if ( $category_products_number < intval($howmuch) ) {
										$error = 1;
										if( is_cart() ) {
											wc_print_notice( 
												sprintf( esc_html__('Your current order quantity total of products in category %s is %s — the minimum quantity you can order is %s ','b2bking') , 
													$category_name,
													$category_products_number, 
													$howmuch 
												), 'error' 
											);
										} else {
											wc_add_notice( 
												sprintf( esc_html__('Your current order quantity total of products in category %s is %s — the minimum quantity you can order is %s ','b2bking') , 
													$category_name,
													$category_products_number, 
													$howmuch 
												), 'error' 
											);
										}
									}
								} else if ($minimum_maximum === 'maximum_order'){
									if ( $category_products_number > intval($howmuch) ) {
										$error = 1;
										if( is_cart() ) {
											wc_print_notice( 
												sprintf( esc_html__('Your current order quantity total of products in category %s is %s — the maximum quantity you can order is %s ','b2bking') , 
													$category_name,
													$category_products_number, 
													$howmuch 
												), 'error' 
											);
										} else {
											wc_add_notice( 
												sprintf( esc_html__('Your current order quantity total of products in category %s is %s — the maximum quantity you can order is %s ','b2bking') , 
													$category_name,
													$category_products_number, 
													$howmuch 
												), 'error' 
											);
										}
									}
								}
							}
						}
					}
				}  else if ($applies[0] === 'product'){
					// rule is product rule
					// meaning that if product exists in cart, it can only be in min / max quantity/value
					// get product's quantity and total value in cart
					$product_name = get_the_title(intval($applies[1]));
					$product_number = 0;
					$product_value = 0;
					if(is_object($cart)) {
						foreach($cart->get_cart() as $cart_item){
						if(intval($applies[1]) === $cart_item['product_id'] || intval($applies[1]) === $cart_item['variation_id']){
							$item_price = $cart_item['data']->get_price(); 
							$item_qty = $cart_item["quantity"];// Quantity
							$item_line_total = $cart_item["line_total"]+$cart_item['line_tax']; // Item total price (price x quantity)
							$product_number += $item_qty; // ctotal number of items in cart
							$product_value += $item_line_total; // calculated total items amount
						}
						}
					}
					// if product exists in cart, continue process
					if ($product_number !== 0){
						// only if largest/smallest
						if ($howmuch === $computed_rules[$minimum_maximum][$quantity_value][$appliesnew]){
							if ($quantity_value === 'value'){
								if ($minimum_maximum === 'minimum_order'){
									if ( floatval($product_value) < $howmuch ) {
										$error = 1;
										if( is_cart() ) {
											wc_print_notice( 
												sprintf( esc_html__('Your current order value total of %s is %s — the minimum value you can order is %s ','b2bking') , 
													$product_name,
													wc_price ($product_value), 
													wc_price ($howmuch) 
												), 'error' 
											);
										} else {
											wc_add_notice( 
												sprintf( esc_html__('Your current order value total of %s is %s — the minimum value you can order is %s ','b2bking') , 
													$product_name,
													wc_price ($product_value), 
													wc_price ($howmuch) 
												), 'error' 
											);
										}
									}
								} else if ($minimum_maximum === 'maximum_order'){
									if ( floatval($product_value) > $howmuch ) {
										$error = 1;
										if( is_cart() ) {
											wc_print_notice( 
												sprintf( esc_html__('Your current order value total of %s is %s — the maximum value you can order is %s ','b2bking') , 
													$product_name,
													wc_price ($product_value), 
													wc_price ($howmuch) 
												), 'error' 
											);
										} else {
											wc_add_notice( 
												sprintf( esc_html__('Your current order value total of %s is %s — the maximum value you can order is %s ','b2bking') , 
													$product_name,
													wc_price ($product_value), 
													wc_price ($howmuch) 
												), 'error' 
											);
										}
									}
								}
							} else if ($quantity_value === 'quantity'){
								if ($minimum_maximum === 'minimum_order'){
									if ( $product_number < intval($howmuch) ) {
										$error = 1;
										if( is_cart() ) {
											wc_print_notice( 
												sprintf( esc_html__('Your current order quantity total of %s is %s — the minimum quantity you can order is %s ','b2bking') , 
													$product_name,
													$product_number, 
													$howmuch 
												), 'error' 
											);
										} else {
											wc_add_notice( 
												sprintf( esc_html__('Your current order quantity total of %s is %s — the minimum quantity you can order is %s ','b2bking') , 
													$product_name,
													$product_number, 
													$howmuch 
												), 'error' 
											);
										}
									}
								} else if ($minimum_maximum === 'maximum_order'){
									if ( $product_number > intval($howmuch) ) {
										$error = 1;
										if( is_cart() ) {
											wc_print_notice( 
												sprintf( esc_html__('Your current order quantity total of %s is %s — the maximum quantity you can order is %s ','b2bking') , 
													$product_name,
													$product_number, 
													$howmuch 
												), 'error' 
											);
										} else {
											wc_add_notice( 
												sprintf( esc_html__('Your current order quantity total of %s is %s — the maximum quantity you can order is %s ','b2bking') , 
													$product_name,
													$product_number, 
													$howmuch 
												), 'error' 
											);
										}
									}
								}
							}
						}
					}
				// multiple select rule
				} else if ($applies[0] === 'multiple'){
					$rule_multiple_options = get_post_meta($dynamic_minmax_rule, 'b2bking_rule_applies_multiple_options', true);
					$rule_multiple_options_array = explode(',',$rule_multiple_options);
					// foreach element, category or product
					foreach($rule_multiple_options_array as $rule_element){
						$rule_element_array = explode('_',$rule_element);
						$appliesnew = $rule_element;
						// if is category
						if ($rule_element_array[0] === 'category'){
							$category_name = get_term( $rule_element_array[1] )->name;
							$category_products_number = 0;
							$category_products_value = 0;
							if(is_object($cart)) {
								foreach($cart->get_cart() as $cart_item){
								if(b2bking()->b2bking_has_category($rule_element_array[1], 'product_cat', $cart_item['product_id'])){
									$item_price = $cart_item['data']->get_price(); 
									$item_qty = $cart_item["quantity"];// Quantity
									$item_line_total = $cart_item["line_total"]+$cart_item['line_tax']; // Item total price (price x quantity)
									$category_products_number += $item_qty; // ctotal number of items in cart
									$category_products_value += $item_line_total; // calculated total items amount
								}
								}
							}
							// if category exists in cart, continue process
							if ($category_products_number !== 0){
								// only if largest/smallest
								if ($howmuch === $computed_rules[$minimum_maximum][$quantity_value][$appliesnew]){
									if ($quantity_value === 'value'){
										if ($minimum_maximum === 'minimum_order'){
											if ( floatval($category_products_value) < $howmuch ) {
												$error = 1;
												if( is_cart() ) {
													wc_print_notice( 
														sprintf( esc_html__('Your current order value total of products in category %s is %s — the minimum value you can order is %s ','b2bking') , 
															$category_name,
															wc_price ($category_products_value), 
															wc_price ($howmuch) 
														), 'error' 
													);
												} else {
													wc_add_notice( 
														sprintf( esc_html__('Your current order value total of products in category %s is %s — the minimum value you can order is %s ','b2bking') , 
															$category_name,
															wc_price ($category_products_value), 
															wc_price ($howmuch) 
														), 'error' 
													);
												}
											}
										} else if ($minimum_maximum === 'maximum_order'){
											if ( floatval($category_products_value) > $howmuch ) {
												$error = 1;
												if( is_cart() ) {
													wc_print_notice( 
														sprintf( esc_html__('Your current order value total of products in category %s is %s — the maximum value you can order is %s ','b2bking') , 
															$category_name,
															wc_price ($category_products_value), 
															wc_price ($howmuch) 
														), 'error' 
													);
												} else {
													wc_add_notice( 
														sprintf( esc_html__('Your current order value total of products in category %s is %s — the maximum value you can order is %s ','b2bking') , 
															$category_name,
															wc_price ($category_products_value), 
															wc_price ($howmuch) 
														), 'error' 
													);
												}
											}
										}
									} else if ($quantity_value === 'quantity'){
										if ($minimum_maximum === 'minimum_order'){
											if ( $category_products_number < intval($howmuch) ) {
												$error = 1;
												if( is_cart() ) {
													wc_print_notice( 
														sprintf( esc_html__('Your current order quantity total of products in category %s is %s — the minimum quantity you can order is %s ','b2bking') , 
															$category_name,
															$category_products_number, 
															$howmuch 
														), 'error' 
													);
												} else {
													wc_add_notice( 
														sprintf( esc_html__('Your current order quantity total of products in category %s is %s — the minimum quantity you can order is %s ','b2bking') , 
															$category_name,
															$category_products_number, 
															$howmuch 
														), 'error' 
													);
												}
											}
										} else if ($minimum_maximum === 'maximum_order'){
											if ( $category_products_number > intval($howmuch) ) {
												$error = 1;
												if( is_cart() ) {
													wc_print_notice( 
														sprintf( esc_html__('Your current order quantity total of products in category %s is %s — the maximum quantity you can order is %s ','b2bking') , 
															$category_name,
															$category_products_number, 
															$howmuch 
														), 'error' 
													);
												} else {
													wc_add_notice( 
														sprintf( esc_html__('Your current order quantity total of products in category %s is %s — the maximum quantity you can order is %s ','b2bking') , 
															$category_name,
															$category_products_number, 
															$howmuch 
														), 'error' 
													);
												}
											}
										}
									}
								}
							}

						// if is product
						} else if ($rule_element_array[0] === 'product'){
							$product_name = get_the_title(intval($rule_element_array[1]));
							$product_number = 0;
							$product_value = 0;
							if(is_object($cart)) {
								foreach($cart->get_cart() as $cart_item){
								if(intval($rule_element_array[1]) === $cart_item['product_id'] || intval($rule_element_array[1]) === $cart_item['variation_id'] ){
									$item_price = $cart_item['data']->get_price(); 
									$item_qty = $cart_item["quantity"];// Quantity
									$item_line_total = $cart_item["line_total"]+$cart_item['line_tax']; // Item total price (price x quantity)
									$product_number += $item_qty; // ctotal number of items in cart
									$product_value += $item_line_total; // calculated total items amount
								}
								}
							}
							// if product exists in cart, continue process
							if ($product_number !== 0){
								// only if largest/smallest
								if ($howmuch === $computed_rules[$minimum_maximum][$quantity_value][$appliesnew]){
									if ($quantity_value === 'value'){
										if ($minimum_maximum === 'minimum_order'){
											if ( floatval($product_value) < $howmuch ) {
												$error = 1;
												if( is_cart() ) {
													wc_print_notice( 
														sprintf( esc_html__('Your current order value total of %s is %s — the minimum value you can order is %s ','b2bking') , 
															$product_name,
															wc_price ($product_value), 
															wc_price ($howmuch) 
														), 'error' 
													);
												} else {
													wc_add_notice( 
														sprintf( esc_html__('Your current order value total of %s is %s — the minimum value you can order is %s ','b2bking') , 
															$product_name,
															wc_price ($product_value), 
															wc_price ($howmuch) 
														), 'error' 
													);
												}
											}
										} else if ($minimum_maximum === 'maximum_order'){
											if ( floatval($product_value) > $howmuch ) {
												$error = 1;
												if( is_cart() ) {
													wc_print_notice( 
														sprintf( esc_html__('Your current order value total of %s is %s — the maximum value you can order is %s ','b2bking') , 
															$product_name,
															wc_price ($product_value), 
															wc_price ($howmuch) 
														), 'error' 
													);
												} else {
													wc_add_notice( 
														sprintf( esc_html__('Your current order value total of %s is %s — the maximum value you can order is %s ','b2bking') , 
															$product_name,
															wc_price ($product_value), 
															wc_price ($howmuch) 
														), 'error' 
													);
												}
											}
										}
									} else if ($quantity_value === 'quantity'){
										if ($minimum_maximum === 'minimum_order'){
											if ( $product_number < intval($howmuch) ) {
												$error = 1;
												if( is_cart() ) {
													wc_print_notice( 
														sprintf( esc_html__('Your current order quantity total of %s is %s — the minimum quantity you can order is %s ','b2bking') , 
															$product_name,
															$product_number, 
															$howmuch 
														), 'error' 
													);
												} else {
													wc_add_notice( 
														sprintf( esc_html__('Your current order quantity total of %s is %s — the minimum quantity you can order is %s ','b2bking') , 
															$product_name,
															$product_number, 
															$howmuch 
														), 'error' 
													);
												}
											}
										} else if ($minimum_maximum === 'maximum_order'){
											if ( $product_number > intval($howmuch) ) {
												$error = 1;
												if( is_cart() ) {
													wc_print_notice( 
														sprintf( esc_html__('Your current order quantity total of %s is %s — the maximum quantity you can order is %s ','b2bking') , 
															$product_name,
															$product_number, 
															$howmuch 
														), 'error' 
													);
												} else {
													wc_add_notice( 
														sprintf( esc_html__('Your current order quantity total of %s is %s — the maximum quantity you can order is %s ','b2bking') , 
															$product_name,
															$product_number, 
															$howmuch 
														), 'error' 
													);
												}
											}
										}
									}
								}
							}
						} 
					}
				}
			}
		}

		if ($error === 1){
			remove_action( 'woocommerce_proceed_to_checkout','woocommerce_button_proceed_to_checkout', 20);
		}
	}

	// Dynamic rule minimum / maximum order
	public static function b2bking_dynamic_minmax_order_amount_quantity( $args, $product ) {

		if (!method_exists($product,'get_id')){
			return $args;
		}

		$user_id = get_current_user_id();

		$product_id = $product->get_id();

		//	$dynamic_minmax_rules = get_transient('b2bking_minmax_'.get_current_user_id());
		$dynamic_minmax_rules = b2bking()->get_global_data('b2bking_minmax',false, get_current_user_id());


		if (!$dynamic_minmax_rules){

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

			$rules_ids_elements = get_option('b2bking_have_minmax_rules_list_ids_elements', array());

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

			//set_transient ('b2bking_minmax_'.get_current_user_id(), $user_applicable_rules);
			b2bking()->set_global_data('b2bking_minmax', $user_applicable_rules, false, get_current_user_id());

			$dynamic_minmax_rules = $user_applicable_rules;

		}



		$largest_maximum = 'none';
		$smallest_minimum = 'none';

		foreach($dynamic_minmax_rules as $dynamic_minmax_rule){
			// get rule details
			$minimum_maximum = get_post_meta($dynamic_minmax_rule, 'b2bking_rule_what', true);
			$quantity_value = get_post_meta($dynamic_minmax_rule, 'b2bking_rule_quantity_value', true);
			$howmuch = floatval(get_post_meta($dynamic_minmax_rule, 'b2bking_rule_howmuch', true));

			if ($quantity_value === 'value'){
				// woocs
				$howmuch = b2bking()->get_woocs_price($howmuch);
			}

			$applies = get_post_meta($dynamic_minmax_rule, 'b2bking_rule_applies', true);
			if ($applies === 'cart_total'){


			} else {
				// rule is category or product rule or multiple select rule
				$applies = explode('_',$applies);
				if ($applies[0] === 'category'){

					
				}  else if ($applies[0] === 'product'){
					// rule is product rule
					if(intval($applies[1]) === $product_id){
						if ($quantity_value === 'quantity'){
							if ($minimum_maximum === 'maximum_order'){
								if ($largest_maximum === 'none'){
									$largest_maximum = $howmuch;
								} else if ($largest_maximum < $howmuch) {
									$largest_maximum = $howmuch;
								}
							} else if ($minimum_maximum === 'minimum_order'){
								if ($smallest_minimum === 'none'){
									$smallest_minimum = $howmuch;
								} else if ($smallest_minimum > $howmuch) {
									$smallest_minimum = $howmuch;
								}
							}
						}
					}

				// multiple select rule
				} else if ($applies[0] === 'multiple'){
					$rule_multiple_options = get_post_meta($dynamic_minmax_rule, 'b2bking_rule_applies_multiple_options', true);
					$rule_multiple_options_array = explode(',',$rule_multiple_options);
					// foreach element, category or product
					foreach($rule_multiple_options_array as $rule_element){
						$rule_element_array = explode('_',$rule_element);
						// if is category
						if ($rule_element_array[0] === 'category'){



						// if is product
						} else if ($rule_element_array[0] === 'product'){

							if(intval($rule_element_array[1]) === $product_id){
								if ($quantity_value === 'quantity'){
									if ($minimum_maximum === 'maximum_order'){
										if ($largest_maximum === 'none'){
											$largest_maximum = $howmuch;
										} else if ($largest_maximum < $howmuch) {
											$largest_maximum = $howmuch;
										}
									} else if ($minimum_maximum === 'minimum_order'){
										if ($smallest_minimum === 'none'){
											$smallest_minimum = $howmuch;
										} else if ($smallest_minimum > $howmuch) {
											$smallest_minimum = $howmuch;
										}
									}
								}
							}
						} 
					}
				}
			}
		}


		if ($smallest_minimum !== 'none'){

			if ( is_cart() || b2bking()->is_side_cart() ) {
				$args['min_value'] = $smallest_minimum;
				$args['min_qty'] = $smallest_minimum; 
			} else {

				//	if (is_product()){
						// check qty already in cart and adjust minimums
						$itemqty = 0;
						$cart = WC()->cart;
						if (is_object($cart)){
							foreach( $cart->get_cart() as $cart_item ){
								// for variable products (product varations)
								if ( $product_id === $cart_item['product_id'] ){
								    $itemqty = $cart_item['quantity'];
								}
							}
						}
						$smallest_minimum -= $itemqty;
						if ($smallest_minimum < 1){
							$smallest_minimum = 1;
						}


						$args['min_value'] = $smallest_minimum;
						$args['min_qty'] = $smallest_minimum; 
						$args['input_value'] = $smallest_minimum;
						if (isset($args['step']) && $args['step']!==1){
							// find the smallest qty above the minimum that also follows step
							$step = $args['step'];
							$stepfinal = $step;
							while ($stepfinal < $smallest_minimum ) {
								$stepfinal += $step;
							}

							$args['input_value'] = $stepfinal;
							$args['min_value'] = $stepfinal;
							$args['min_qty'] = $stepfinal; 
						}
				//	}
		
			}

		}

		if ($largest_maximum !== 'none'){

			if (is_cart() || b2bking()->is_side_cart()){
				$args['max_value'] = $largest_maximum;
				$args['max_qty'] = $largest_maximum; 
			} else {


				//	if (is_product()){
						// check qty already in cart and adjust minimums
						$itemqty = 0;
						$cart = WC()->cart;
						if (is_object($cart)){
							foreach( $cart->get_cart() as $cart_item ){
								// for variable products (product varations)
								if ( $product_id === $cart_item['product_id'] ){
								    $itemqty = $cart_item['quantity'];
								}
							}
						}
						

						$largest_maximum -= $itemqty;
				
						$args['max_value'] = $largest_maximum;
						$args['max_qty'] = $largest_maximum;

						if (isset($args['step']) && $args['step']!==1){
							// find the smallest qty above the minimum that also follows step
							$step = $args['step'];
							$stepfinal = $step;
							while ($stepfinal <= $largest_maximum ) {
								$stepfinal += $step;
							}
							$stepfinal -= $step;

							$args['input_value'] = $stepfinal;
							$args['max_value'] = $stepfinal;
							$args['max_qty'] = $stepfinal; 
						} 
				//	}
				
			}


		}
						   
		return $args;
	}

	public static function b2bking_dynamic_minmax_order_amount_quantity_variation( $args, $instance, $product ) {

		if (!method_exists($product,'get_id')){
			return $args;
		}


		$user_id = get_current_user_id();
		$product_id = $product->get_id();

		//	$dynamic_minmax_rules = get_transient('b2bking_minmax_'.get_current_user_id());
		$dynamic_minmax_rules = b2bking()->get_global_data('b2bking_minmax',false, get_current_user_id());
		if (!$dynamic_minmax_rules){

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

			$rules_ids_elements = get_option('b2bking_have_minmax_rules_list_ids_elements', array());

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

		//	set_transient ('b2bking_minmax_'.get_current_user_id(), $user_applicable_rules);
			b2bking()->set_global_data('b2bking_minmax', $user_applicable_rules, false, get_current_user_id());

			$dynamic_minmax_rules = $user_applicable_rules;

		}

		$largest_maximum = 'none';
		$smallest_minimum = 'none';

		foreach($dynamic_minmax_rules as $dynamic_minmax_rule){
			// get rule details
			$minimum_maximum = get_post_meta($dynamic_minmax_rule, 'b2bking_rule_what', true);
			$quantity_value = get_post_meta($dynamic_minmax_rule, 'b2bking_rule_quantity_value', true);
			$howmuch = floatval(get_post_meta($dynamic_minmax_rule, 'b2bking_rule_howmuch', true));

			if ($quantity_value === 'value'){
				// woocs
				$howmuch = b2bking()->get_woocs_price($howmuch);
			}
			
			$applies = get_post_meta($dynamic_minmax_rule, 'b2bking_rule_applies', true);
			if ($applies === 'cart_total'){
		

			} else {
				// rule is category or product rule or multiple select rule
				$applies = explode('_',$applies);
				if ($applies[0] === 'category'){
					// rule is category rule
					if(b2bking()->b2bking_has_category($applies[1], 'product_cat', $product_id)){
						if ($quantity_value === 'quantity'){
							if ($minimum_maximum === 'maximum_order'){
								if ($largest_maximum === 'none'){
									$largest_maximum = $howmuch;
								} else if ($largest_maximum < $howmuch) {
									$largest_maximum = $howmuch;
								}
							} else if ($minimum_maximum === 'minimum_order'){
								if ($smallest_minimum === 'none'){
									$smallest_minimum = $howmuch;
								} else if ($smallest_minimum > $howmuch) {
									$smallest_minimum = $howmuch;
								}
							}
						}
					}
					
				}  else if ($applies[0] === 'product'){
					// rule is product rule
					if(intval($applies[1]) === $product_id){
						if ($quantity_value === 'quantity'){
							if ($minimum_maximum === 'maximum_order'){
								if ($largest_maximum === 'none'){
									$largest_maximum = $howmuch;
								} else if ($largest_maximum < $howmuch) {
									$largest_maximum = $howmuch;
								}
							} else if ($minimum_maximum === 'minimum_order'){
								if ($smallest_minimum === 'none'){
									$smallest_minimum = $howmuch;
								} else if ($smallest_minimum > $howmuch) {
									$smallest_minimum = $howmuch;
								}
							}
						}
					}

				// multiple select rule
				} else if ($applies[0] === 'multiple'){
					$rule_multiple_options = get_post_meta($dynamic_minmax_rule, 'b2bking_rule_applies_multiple_options', true);
					$rule_multiple_options_array = explode(',',$rule_multiple_options);
					// foreach element, category or product
					foreach($rule_multiple_options_array as $rule_element){
						$rule_element_array = explode('_',$rule_element);
						// if is category
						if ($rule_element_array[0] === 'category'){

							if(b2bking()->b2bking_has_category($rule_element_array[1], 'product_cat', $product_id)){
								if ($quantity_value === 'quantity'){
									if ($minimum_maximum === 'maximum_order'){
										if ($largest_maximum === 'none'){
											$largest_maximum = $howmuch;
										} else if ($largest_maximum < $howmuch) {
											$largest_maximum = $howmuch;
										}
									} else if ($minimum_maximum === 'minimum_order'){
										if ($smallest_minimum === 'none'){
											$smallest_minimum = $howmuch;
										} else if ($smallest_minimum > $howmuch) {
											$smallest_minimum = $howmuch;
										}
									}
								}
							}

						// if is product
						} else if ($rule_element_array[0] === 'product'){

							if(intval($rule_element_array[1]) === $product_id){
								if ($quantity_value === 'quantity'){
									if ($minimum_maximum === 'maximum_order'){
										if ($largest_maximum === 'none'){
											$largest_maximum = $howmuch;
										} else if ($largest_maximum < $howmuch) {
											$largest_maximum = $howmuch;
										}
									} else if ($minimum_maximum === 'minimum_order'){
										if ($smallest_minimum === 'none'){
											$smallest_minimum = $howmuch;
										} else if ($smallest_minimum > $howmuch) {
											$smallest_minimum = $howmuch;
										}
									}
								}
							}
						} 
					}
				}
			}
		}

		if ($smallest_minimum !== 'none'){

			if ( is_cart() || b2bking()->is_side_cart()) {
				$args['min_value'] = $smallest_minimum;
				$args['min_qty'] = $smallest_minimum; 
			} else {

				//	if (is_product()){
						// check qty already in cart and adjust minimums
						$itemqty = 0;
						$cart = WC()->cart;
						if (is_object($cart)){
							foreach( $cart->get_cart() as $cart_item ){
								// for variable products (product varations)
								if ( $product_id === $cart_item['product_id'] || $product_id === $cart_item['variation_id']){
								    $itemqty = $cart_item['quantity'];
								}
							}
						}
						$smallest_minimum -= $itemqty;
						if ($smallest_minimum < 1){
							$smallest_minimum = 1;
						}

						$args['min_value'] = $smallest_minimum;
						$args['min_qty'] = $smallest_minimum; 
						$args['input_value'] = $smallest_minimum;
						if (isset($args['step']) && $args['step']!==1){
							// find the smallest qty above the minimum that also follows step
							$step = $args['step'];
							$stepfinal = $step;
							while ($stepfinal < $smallest_minimum ) {
								$stepfinal += $step;
							}

							$args['input_value'] = $stepfinal;
							$args['min_value'] = $stepfinal;
							$args['min_qty'] = $stepfinal; 
						}
				//	}
				
			}

		}

		if ($largest_maximum !== 'none'){

			if (is_cart() || b2bking()->is_side_cart()){
				$args['max_value'] = $largest_maximum;
				$args['max_qty'] = $largest_maximum; 
			} else {

				//	if (is_product()){
						// check qty already in cart and adjust minimums
						$itemqty = 0;
						$cart = WC()->cart;
						if (is_object($cart)){
							foreach( $cart->get_cart() as $cart_item ){
								// for variable products (product varations)
								if ( $product_id === $cart_item['product_id'] || $product_id === $cart_item['variation_id']){
								    $itemqty = $cart_item['quantity'];
								}
							}
						}
						$largest_maximum -= $itemqty;
						$args['max_value'] = $largest_maximum;
						$args['max_qty'] = $largest_maximum; 

						if (isset($args['step']) && $args['step']!==1){
							// find the smallest qty above the minimum that also follows step
							$step = $args['step'];
							$stepfinal = $step;
							while ($stepfinal <= $largest_maximum ) {
								$stepfinal += $step;
							}
							$stepfinal -= $step;

							$args['input_value'] = $stepfinal;
							$args['max_value'] = $stepfinal;
							$args['max_qty'] = $stepfinal; 
						}
				//	}
				
			}


		}
						   
		return $args;
	}


	public static function b2bking_dynamic_rule_required_multiple_quantity( $args, $product ) {

		if (!method_exists($product,'get_id')){
			return $args;
		}

		$current_product_id = $product->get_id();


		// RE-ENABLED since B2BKing 4.2.44

		// if product is variable, SKIP the rule
		// variable products must be settable as required through multiple variations
		// so we do not want to set a required for the product as a whole
		
		if (apply_filters('b2bking_required_multiple_variation_use_parent', false)){
			if ($product->is_type('variable')){
				return $args;
			}
		}

		/*
		For now disabled, at least as long as WooCommerce does not support step for individual variations.
		Specifically the 'woocommerce_available_variation' filter does not support step


		The problem is:
		-> If we allow it to be settable as required through multiple variations, then we need to be able to set required multiple for individual variations. BUT we annot do that because woocommerce does not support step there right now.
		*/

		
		$user_id = get_current_user_id();
		$account_type = get_user_meta($user_id,'b2bking_account_type', true);
		if ($account_type === 'subaccount'){
			// for all intents and purposes set current user as the subaccount parent
			$parent_user_id = get_user_meta($user_id, 'b2bking_account_parent', true);
			$user_id = $parent_user_id;
		}

		// Get current product
		$current_product_id = $product->get_id();

		// 1) Get all rules and check if any rules apply to the product
		//	$rules_that_apply_to_product = get_transient('b2bking_required_multiple_rules_apply_'.$current_product_id);
		$rules_that_apply_to_product = b2bking()->get_global_data('b2bking_required_multiple_rules_apply',$current_product_id);

		if (!$rules_that_apply_to_product){

			$rules_that_apply = array();
			$required_multiple_rules_option = get_option('b2bking_have_required_multiple_rules_list_ids', '');
			if (!empty($required_multiple_rules_option)){
				$required_multiple_rules_v2_ids = explode(',',$required_multiple_rules_option);
			} else {
				$required_multiple_rules_v2_ids = array();
			}

			foreach ($required_multiple_rules_v2_ids as $rule_id){
				$applies = get_post_meta($rule_id,'b2bking_rule_applies', true);
				if ($applies === 'product_'.$current_product_id){
					array_push($rules_that_apply, $rule_id);
				} else if ($applies === 'multiple_options'){
					$multiple_options = get_post_meta($rule_id,'b2bking_rule_applies_multiple_options', true);
					$multiple_options_array = explode(',', $multiple_options);
					if (in_array('product_'.$current_product_id, $multiple_options_array)){
						array_push($rules_that_apply, $rule_id);
					} else {
						// try categories

						// removed in version 4.2.16 as it should not apply to individual products when a req multiple category is set
						/*
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
						*/
					}
					
				} else if (explode('_', $applies)[0] === 'category'){
					// removed in version 4.2.16 as it should not apply to individual products when a req multiple category is set
					/*
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
					*/
				}
			}

			//set_transient('b2bking_required_multiple_rules_apply_'.$current_product_id,$rules_that_apply);
			b2bking()->set_global_data('b2bking_required_multiple_rules_apply', $rules_that_apply, $current_product_id);

			$rules_that_apply_to_product = $rules_that_apply;
		}
		// 2) If no rules apply for product, set transient for current user to empty array
		if (empty($rules_that_apply_to_product)){
			//set_transient('b2bking_required_multiple_'.$current_product_id.'_'.get_current_user_id(), array());
			b2bking()->set_global_data('b2bking_required_multiple',array(),$current_product_id,get_current_user_id());
		} else {

			// if transient does not already exist
		//	if (!get_transient('b2bking_required_multiple_'.$current_product_id.'_'.get_current_user_id())){
			if (!b2bking()->get_global_data('b2bking_required_multiple',$current_product_id, get_current_user_id())){

				// 3) If some rules apply, for each rule, check if it applies to the current user and build array.
				$rules_that_apply_to_user = array();
				$user_id = get_current_user_id();
				$account_type = get_user_meta($user_id,'b2bking_account_type', true);
				if ($account_type === 'subaccount'){
					// for all intents and purposes set current user as the subaccount parent
					$parent_user_id = get_user_meta($user_id, 'b2bking_account_parent', true);
					$user_id = $parent_user_id;
				}

				$user_is_b2b = get_user_meta($user_id,'b2bking_b2buser', true);
				$user_group = b2bking()->get_user_group($user_id);


				foreach ($rules_that_apply_to_product as $rule_id){
					$who = get_post_meta($rule_id,'b2bking_rule_who', true);
					// first check guest users
					if ($user_id === 0){
						if ($who === 'user_0'){
							array_push($rules_that_apply_to_user, $rule_id);
						} else if ($who === 'multiple_options'){
							$multiple_options = get_post_meta($rule_id,'b2bking_rule_who_multiple_options', true);
							$multiple_options_array = explode(',',$multiple_options);
							if (in_array('user_0',$multiple_options_array)){
								array_push($rules_that_apply_to_user, $rule_id);
							}
						}
					} else {
						// user is not guest
						if ($who === 'all_registered' || $who === 'user_'.$user_id){
							array_push($rules_that_apply_to_user, $rule_id);
						} else {
							// if multiple options and all

							if ($user_is_b2b !== 'yes'){
								// user is b2c
								if ($who === 'everyone_registered_b2c'){
									array_push($rules_that_apply_to_user, $rule_id);
								} else if ($who === 'multiple_options'){
									$multiple_options = get_post_meta($rule_id,'b2bking_rule_who_multiple_options', true);
									$multiple_options_array = explode(',',$multiple_options);
									if (in_array('all_registered',$multiple_options_array)){
										array_push($rules_that_apply_to_user, $rule_id);
									} else if (in_array('everyone_registered_b2c',$multiple_options_array)){
										array_push($rules_that_apply_to_user, $rule_id);
									} else if (in_array('user_'.$user_id,$multiple_options_array)){
										array_push($rules_that_apply_to_user, $rule_id);
									}
								}
							} else {	
								// user is b2b
								if ($who === 'everyone_registered_b2b'){
									array_push($rules_that_apply_to_user, $rule_id);
								} else if ($who === 'group_'.$user_group){
									array_push($rules_that_apply_to_user, $rule_id);
								} else if ($who === 'multiple_options'){

									$multiple_options = get_post_meta($rule_id,'b2bking_rule_who_multiple_options', true);
									$multiple_options_array = explode(',',$multiple_options);

									if (in_array('all_registered',$multiple_options_array)){
										array_push($rules_that_apply_to_user, $rule_id);
									} else if (in_array('everyone_registered_b2b',$multiple_options_array)){
										array_push($rules_that_apply_to_user, $rule_id);
									} else if (in_array('user_'.$user_id,$multiple_options_array)){
										array_push($rules_that_apply_to_user, $rule_id);
									} else if (in_array('group_'.$user_group,$multiple_options_array)){
										array_push($rules_that_apply_to_user, $rule_id);
									}
								}
							}
						}
					}
				}

				// either an empty array or an array with rules
				//set_transient('b2bking_required_multiple_'.$current_product_id.'_'.get_current_user_id(), $rules_that_apply_to_user);
				b2bking()->set_global_data('b2bking_required_multiple', $rules_that_apply_to_user, $current_product_id, get_current_user_id());
			}
		}

		
		//	$required_multiple_rules = get_transient('b2bking_required_multiple_'.$current_product_id.'_'.get_current_user_id());

		$required_multiple_rules = b2bking()->get_global_data('b2bking_required_multiple',$current_product_id, get_current_user_id());

		if (empty($required_multiple_rules)){

		} else {
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

		if (empty($required_multiple_rules)){
			$required_multiple_rules = array();
		}

		// if multiple fapply, give the smallest to the user
		$have_required_multiple = NULL;
		$smallest_required_multiple = 0;

		foreach ($required_multiple_rules as $required_multiple_rule){
			// Get rule details
			$type = get_post_meta($required_multiple_rule, 'b2bking_rule_what', true);
			$howmuch = get_post_meta($required_multiple_rule, 'b2bking_rule_howmuch', true);
			$applies = explode('_',get_post_meta($required_multiple_rule, 'b2bking_rule_applies', true));
			$rule_multiple_options = get_post_meta($required_multiple_rule, 'b2bking_rule_applies_multiple_options', true);
			$rule_multiple_options_array = explode(',',$rule_multiple_options);
			$cart = WC()->cart;
			// Get conditions
			$passconditions = 'yes';
			$conditions = get_post_meta($required_multiple_rule, 'b2bking_rule_conditions', true);
			
			if (!empty($conditions)){
				$conditions = explode('|',$conditions);

				if ($applies[0] === 'multiple'){ // if rule is multiple products / categories rule
					$temporary_pass_conditions = 'no'; // if at least 1 element passes, change to yes
					// for each element that applies to the product and is part of the rule, check if at least 1 passes the conditions
					foreach($current_product_belongsto_array as $element){
						if(in_array($element, $rule_multiple_options_array)){
							$element_array = explode('_', $element);
							// if element is product or if element is category
							if ($element_array[0] === 'product'){
								$passes_inside_conditions = 'yes';
								$product_quantity = 0;
								if(is_object($cart)) {
									foreach($cart->get_cart() as $cart_item){
									if(intval($element_array[1]) === intval($cart_item['product_id'])){
										$product_quantity = $cart_item["quantity"];// Quantity
										break;
									}
									}
								}
								// check all product conditions against it
								foreach ($conditions as $condition){
									$condition_details = explode(';',$condition);

		    						if (substr($condition_details[0], -5) === 'value') {
									$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
								}
									switch ($condition_details[0]){
										case 'product_quantity':
											switch ($condition_details[1]){
												case 'greater':
													if (! ($product_quantity > intval($condition_details[2]))){
														$passes_inside_conditions = 'no';
														break 3;
													}
												break;
												case 'equal':
													if (! ($product_quantity === intval($condition_details[2]))){
														$passes_inside_conditions = 'no';
														break 3;
													}
												break;
												case 'smaller':
													if (! ($product_quantity < intval($condition_details[2]))){
														$passes_inside_conditions = 'no';
														break 3;
													}
												break;
											}
											break;
										case 'cart_total_quantity':
											switch ($condition_details[1]){
												case 'greater':
													if (! ($cart->cart_contents_count > intval($condition_details[2]))){
														$passes_inside_conditions = 'no';
														break 3;
													}
												break;
												case 'equal':
													if (! ($cart->cart_contents_count === intval($condition_details[2]))){
														$passes_inside_conditions = 'no';
														break 3;
													}
												break;
												case 'smaller':
													if (! ($cart->cart_contents_count < intval($condition_details[2]))){
														$passes_inside_conditions = 'no';
														break 3;
													}
												break;
											}
											break;
									}
								}
								if ($passes_inside_conditions === 'yes'){
									$temporary_pass_conditions = 'yes';
									break; // if 1 element passed, no need to check all other elements
								}
							} else if ($element_array[0] === 'category'){
								// check all category conditions against it + car total conditions
								$passes_inside_conditions = 'yes';
								$category_quantity = 0;
								if(is_object($cart)) {
									foreach($cart->get_cart() as $cart_item){
										if(b2bking()->b2bking_has_category($element_array[1], 'product_cat', $cart_item['product_id'])){
											$category_quantity += $cart_item["quantity"]; // add item quantity
										}
									}
								}
								foreach ($conditions as $condition){
									$condition_details = explode(';',$condition);

		    						if (substr($condition_details[0], -5) === 'value') {
									$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
								}
									switch ($condition_details[0]){
										case 'category_product_quantity':
											switch ($condition_details[1]){
												case 'greater':
													if (! ($category_quantity > intval($condition_details[2]))){
														$passes_inside_conditions = 'no';
														break 3;
													}
												break;
												case 'equal':
													if (! ($category_quantity === intval($condition_details[2]))){
														$passes_inside_conditions = 'no';
														break 3;
													}
												break;
												case 'smaller':
													if (! ($category_quantity < intval($condition_details[2]))){
														$passes_inside_conditions = 'no';
														break 3;
													}
												break;
											}
											break;
										case 'cart_total_quantity':
											switch ($condition_details[1]){
												case 'greater':
													if (! ($cart->cart_contents_count > intval($condition_details[2]))){
														$passes_inside_conditions = 'no';
														break 3;
													}
												break;
												case 'equal':
													if (! ($cart->cart_contents_count === intval($condition_details[2]))){
														$passes_inside_conditions = 'no';
														break 3;
													}
												break;
												case 'smaller':
													if (! ($cart->cart_contents_count < intval($condition_details[2]))){
														$passes_inside_conditions = 'no';
														break 3;
													}
												break;
											}
											break;
									}
								}
								if ($passes_inside_conditions === 'yes'){
									$temporary_pass_conditions = 'yes';
									break; // if 1 element passed, no need to check all other elements
								}
							}
						}
					} //foreach element end

					if ($temporary_pass_conditions === 'no'){
						$passconditions = 'no';
					}

				} else { // if rule is simple product, category, or total cart role 
					$category_products_number = 0;
					$category_products_value = 0;
					$products_number = 0;
					$products_value = 0;
					

					// Check rule is category rule or product rule
					if ($applies[0] === 'category'){

						// Calculate number of products in cart of this category AND total price of these products
						if(is_object($cart)) {
							foreach($cart->get_cart() as $cart_item){
							if(b2bking()->b2bking_has_category($applies[1], 'product_cat', $cart_item['product_id'])){
								$item_qty = $cart_item["quantity"];// Quantity
								$item_line_total = $cart_item["line_total"]; // Item total price (price x quantity)
								$category_products_number += $item_qty; // ctotal number of items in cart
								$category_products_value += $item_line_total; // calculated total items amount
							}
							}
						}
					} else if ($applies[0] === 'product') {
						if(is_object($cart)) {
							foreach($cart->get_cart() as $cart_item){
							if(intval($current_product_id) === intval($cart_item['product_id'])){
								$item_qty = $cart_item["quantity"];// Quantity
								if (isset($cart_item['line_total'])){
									$item_line_total = $cart_item["line_total"]; // Item total price (price x quantity)
									$products_value += $item_line_total; // calculated total items amount
								} 
								$products_number += $item_qty; // ctotal number of items in cart
							
							}
							}
						}
					}

					foreach ($conditions as $condition){
						$condition_details = explode(';',$condition);

		    		if (substr($condition_details[0], -5) === 'value') {
									$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
								}
						switch ($condition_details[0]){
							case 'product_quantity':
								switch ($condition_details[1]){
									case 'greater':
										if (! ($products_number > intval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'equal':
										if (! ($products_number === intval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'smaller':
										if (! ($products_number < intval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
								}
								break;
							
							case 'category_product_quantity':
								switch ($condition_details[1]){
									case 'greater':
										if (! ($category_products_number > intval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'equal':
										if (! ($category_products_number === intval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'smaller':
										if (! ($category_products_number < intval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
								}
								break;
							
							case 'cart_total_quantity':
								switch ($condition_details[1]){
									case 'greater':
										if (! ($cart->cart_contents_count > intval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'equal':
										if (! ($cart->cart_contents_count === intval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'smaller':
										if (! ($cart->cart_contents_count < intval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
								}
								break;
							
						}
					}
				}		
			}

			// Passed conditions
			if ($passconditions === 'yes'){
				if ($have_required_multiple === NULL){
					$have_required_multiple = 'yes';
					$smallest_required_multiple = floatval($howmuch);
				} else {
					if (floatval($howmuch) < $smallest_required_multiple){
						$smallest_required_multiple = floatval($howmuch);
					}   
				}
			} else {
				// do nothing
			}

		} //foreach end

		if($have_required_multiple !== NULL){

			$step = $smallest_required_multiple;

			if ( ! is_cart() && ! b2bking()->is_side_cart() ) {
				$args['input_value'] = $step;
				$args['min_value'] = $step; 
				$args['min_qty'] = $step; 
				$args['step'] = $step; 
			} else {
				$args['min_value'] = $step;
				$args['min_qty'] = $step; 
				$args['step'] = $step; 
			}

		}
						   
		return $args;

	}

	public static function b2bking_dynamic_rule_required_multiple_quantity_variation( $args, $instance, $product ) {

		if (!method_exists($product,'get_id')){
			return $args;
		}

		
		$user_id = get_current_user_id();
		$account_type = get_user_meta($user_id,'b2bking_account_type', true);
		if ($account_type === 'subaccount'){
			// for all intents and purposes set current user as the subaccount parent
			$parent_user_id = get_user_meta($user_id, 'b2bking_account_parent', true);
			$user_id = $parent_user_id;
		}

		// Get current product
		$current_product_id = $product->get_id();

		

		// 1) Get all rules and check if any rules apply to the product
		//	$rules_that_apply_to_product = get_transient('b2bking_required_multiple_rules_apply_'.$current_product_id);
		$rules_that_apply_to_product = b2bking()->get_global_data('b2bking_required_multiple_rules_apply',$current_product_id);
		if (!$rules_that_apply_to_product){

			$rules_that_apply = array();
			$required_multiple_rules_option = get_option('b2bking_have_required_multiple_rules_list_ids', '');
			if (!empty($required_multiple_rules_option)){
				$required_multiple_rules_v2_ids = explode(',',$required_multiple_rules_option);
			} else {
				$required_multiple_rules_v2_ids = array();
			}

			foreach ($required_multiple_rules_v2_ids as $rule_id){
				$applies = get_post_meta($rule_id,'b2bking_rule_applies', true);
				if ($applies === 'product_'.$current_product_id){
					array_push($rules_that_apply, $rule_id);
				} else if ($applies === 'multiple_options'){
					$multiple_options = get_post_meta($rule_id,'b2bking_rule_applies_multiple_options', true);
					$multiple_options_array = explode(',', $multiple_options);
					if (in_array('product_'.$current_product_id, $multiple_options_array)){
						array_push($rules_that_apply, $rule_id);
					} else {
						// try categories
						// removed in version 4.2.16 as it should not apply to individual products when a req multiple category is set
						/*
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
						*/
					}
					
				} else if (explode('_', $applies)[0] === 'category'){
					// removed in version 4.2.16 as it should not apply to individual products when a req multiple category is set
					/*
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
					*/
				}
			}

			//set_transient('b2bking_required_multiple_rules_apply_'.$current_product_id,$rules_that_apply);
			b2bking()->set_global_data('b2bking_required_multiple_rules_apply', $rules_that_apply, $current_product_id);

			$rules_that_apply_to_product = $rules_that_apply;
		}
		// 2) If no rules apply for product, set transient for current user to empty array
		if (empty($rules_that_apply_to_product)){
			//set_transient('b2bking_required_multiple_'.$current_product_id.'_'.get_current_user_id(), array());
			b2bking()->set_global_data('b2bking_required_multiple', array(), $current_product_id, get_current_user_id());
		} else {
			// if transient does not already exist
		//	if (!get_transient('b2bking_required_multiple_'.$current_product_id.'_'.get_current_user_id())){
			if (!b2bking()->get_global_data('b2bking_required_multiple',$current_product_id,get_current_user_id())){

				// 3) If some rules apply, for each rule, check if it applies to the current user and build array.
				$rules_that_apply_to_user = array();
				$user_id = get_current_user_id();
				$account_type = get_user_meta($user_id,'b2bking_account_type', true);
				if ($account_type === 'subaccount'){
					// for all intents and purposes set current user as the subaccount parent
					$parent_user_id = get_user_meta($user_id, 'b2bking_account_parent', true);
					$user_id = $parent_user_id;
				}

				$user_is_b2b = get_user_meta($user_id,'b2bking_b2buser', true);
				$user_group = b2bking()->get_user_group($user_id);

				foreach ($rules_that_apply_to_product as $rule_id){
					$who = get_post_meta($rule_id,'b2bking_rule_who', true);
					// first check guest users
					if ($user_id === 0){
						if ($who === 'user_0'){
							array_push($rules_that_apply_to_user, $rule_id);
						} else if ($who === 'multiple_options'){
							$multiple_options = get_post_meta($rule_id,'b2bking_rule_who_multiple_options', true);
							$multiple_options_array = explode(',',$multiple_options);
							if (in_array('user_0',$multiple_options_array)){
								array_push($rules_that_apply_to_user, $rule_id);
							}
						}
					} else {
						// user is not guest
						if ($who === 'all_registered' || $who === 'user_'.$user_id){
							array_push($rules_that_apply_to_user, $rule_id);
						} else {
							if ($user_is_b2b !== 'yes'){
								// user is b2c
								if ($who === 'everyone_registered_b2c'){
									array_push($rules_that_apply_to_user, $rule_id);
								} else if ($who === 'multiple_options'){
									$multiple_options = get_post_meta($rule_id,'b2bking_rule_who_multiple_options', true);
									$multiple_options_array = explode(',',$multiple_options);
									if (in_array('everyone_registered_b2c',$multiple_options_array)){
										array_push($rules_that_apply_to_user, $rule_id);
									} else if (in_array('user_'.$user_id,$multiple_options_array)){
										array_push($rules_that_apply_to_user, $rule_id);
									}
								}
							} else {	
								// user is b2b
								if ($who === 'everyone_registered_b2b'){
									array_push($rules_that_apply_to_user, $rule_id);
								} else if ($who === 'group_'.$user_group){
									array_push($rules_that_apply_to_user, $rule_id);
								} else if ($who === 'multiple_options'){
									$multiple_options = get_post_meta($rule_id,'b2bking_rule_who_multiple_options', true);
									$multiple_options_array = explode(',',$multiple_options);
									if (in_array('everyone_registered_b2b',$multiple_options_array)){
										array_push($rules_that_apply_to_user, $rule_id);
									} else if (in_array('user_'.$user_id,$multiple_options_array)){
										array_push($rules_that_apply_to_user, $rule_id);
									} else if (in_array('group_'.$user_group,$multiple_options_array)){
										array_push($rules_that_apply_to_user, $rule_id);
									}
								}
							}
						}
					}
				}

				// either an empty array or an array with rules
				//set_transient('b2bking_required_multiple_'.$current_product_id.'_'.get_current_user_id(), $rules_that_apply_to_user);
				b2bking()->set_global_data('b2bking_required_multiple', $rules_that_apply_to_user, $current_product_id, get_current_user_id());
			}
		}

		
		//	$required_multiple_rules = get_transient('b2bking_required_multiple_'.$current_product_id.'_'.get_current_user_id());
		$required_multiple_rules = b2bking()->get_global_data('b2bking_required_multiple',$current_product_id,get_current_user_id());

		if (empty($required_multiple_rules)){

		} else {
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

		if (empty($required_multiple_rules)){
			$required_multiple_rules = array();
		}

		// if multiple fapply, give the smallest to the user
		$have_required_multiple = NULL;
		$smallest_required_multiple = 0;

		foreach ($required_multiple_rules as $required_multiple_rule){
			// Get rule details
			$type = get_post_meta($required_multiple_rule, 'b2bking_rule_what', true);
			$howmuch = get_post_meta($required_multiple_rule, 'b2bking_rule_howmuch', true);

			$applies = explode('_',get_post_meta($required_multiple_rule, 'b2bking_rule_applies', true));
			$rule_multiple_options = get_post_meta($required_multiple_rule, 'b2bking_rule_applies_multiple_options', true);
			$rule_multiple_options_array = explode(',',$rule_multiple_options);
			$cart = WC()->cart;
			// Get conditions
			$passconditions = 'yes';
			$conditions = get_post_meta($required_multiple_rule, 'b2bking_rule_conditions', true);
			
			if (!empty($conditions)){
				$conditions = explode('|',$conditions);

				if ($applies[0] === 'multiple'){ // if rule is multiple products / categories rule
					$temporary_pass_conditions = 'no'; // if at least 1 element passes, change to yes
					// for each element that applies to the product and is part of the rule, check if at least 1 passes the conditions
					foreach($current_product_belongsto_array as $element){
						if(in_array($element, $rule_multiple_options_array)){
							$element_array = explode('_', $element);
							// if element is product or if element is category
							if ($element_array[0] === 'product'){
								$passes_inside_conditions = 'yes';
								$product_quantity = 0;
								if(is_object($cart)) {
									foreach($cart->get_cart() as $cart_item){
									if(intval($element_array[1]) === intval($cart_item['product_id'])){
										$product_quantity = $cart_item["quantity"];// Quantity
										break;
									}
									}
								}
								// check all product conditions against it
								foreach ($conditions as $condition){
									$condition_details = explode(';',$condition);

			    					if (substr($condition_details[0], -5) === 'value') {
										$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
									}

									switch ($condition_details[0]){
										case 'product_quantity':
											switch ($condition_details[1]){
												case 'greater':
													if (! ($product_quantity > intval($condition_details[2]))){
														$passes_inside_conditions = 'no';
														break 3;
													}
												break;
												case 'equal':
													if (! ($product_quantity === intval($condition_details[2]))){
														$passes_inside_conditions = 'no';
														break 3;
													}
												break;
												case 'smaller':
													if (! ($product_quantity < intval($condition_details[2]))){
														$passes_inside_conditions = 'no';
														break 3;
													}
												break;
											}
											break;
										case 'cart_total_quantity':
											switch ($condition_details[1]){
												case 'greater':
													if (! ($cart->cart_contents_count > intval($condition_details[2]))){
														$passes_inside_conditions = 'no';
														break 3;
													}
												break;
												case 'equal':
													if (! ($cart->cart_contents_count === intval($condition_details[2]))){
														$passes_inside_conditions = 'no';
														break 3;
													}
												break;
												case 'smaller':
													if (! ($cart->cart_contents_count < intval($condition_details[2]))){
														$passes_inside_conditions = 'no';
														break 3;
													}
												break;
											}
											break;
									}
								}
								if ($passes_inside_conditions === 'yes'){
									$temporary_pass_conditions = 'yes';
									break; // if 1 element passed, no need to check all other elements
								}
							} else if ($element_array[0] === 'category'){
								// check all category conditions against it + car total conditions
								$passes_inside_conditions = 'yes';
								$category_quantity = 0;
								if(is_object($cart)) {
									foreach($cart->get_cart() as $cart_item){
										if(b2bking()->b2bking_has_category($element_array[1], 'product_cat', $cart_item['product_id'])){
											$category_quantity += $cart_item["quantity"]; // add item quantity
										}
									}
								}
								foreach ($conditions as $condition){
									$condition_details = explode(';',$condition);

			    					if (substr($condition_details[0], -5) === 'value') {
										$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
									}
									switch ($condition_details[0]){
										case 'category_product_quantity':
											switch ($condition_details[1]){
												case 'greater':
													if (! ($category_quantity > intval($condition_details[2]))){
														$passes_inside_conditions = 'no';
														break 3;
													}
												break;
												case 'equal':
													if (! ($category_quantity === intval($condition_details[2]))){
														$passes_inside_conditions = 'no';
														break 3;
													}
												break;
												case 'smaller':
													if (! ($category_quantity < intval($condition_details[2]))){
														$passes_inside_conditions = 'no';
														break 3;
													}
												break;
											}
											break;
										case 'cart_total_quantity':
											switch ($condition_details[1]){
												case 'greater':
													if (! ($cart->cart_contents_count > intval($condition_details[2]))){
														$passes_inside_conditions = 'no';
														break 3;
													}
												break;
												case 'equal':
													if (! ($cart->cart_contents_count === intval($condition_details[2]))){
														$passes_inside_conditions = 'no';
														break 3;
													}
												break;
												case 'smaller':
													if (! ($cart->cart_contents_count < intval($condition_details[2]))){
														$passes_inside_conditions = 'no';
														break 3;
													}
												break;
											}
											break;
									}
								}
								if ($passes_inside_conditions === 'yes'){
									$temporary_pass_conditions = 'yes';
									break; // if 1 element passed, no need to check all other elements
								}
							}
						}
					} //foreach element end

					if ($temporary_pass_conditions === 'no'){
						$passconditions = 'no';
					}

				} else { // if rule is simple product, category, or total cart role 
					$category_products_number = 0;
					$category_products_value = 0;
					$products_number = 0;
					$products_value = 0;
					

					// Check rule is category rule or product rule
					if ($applies[0] === 'category'){

						// Calculate number of products in cart of this category AND total price of these products
						if(is_object($cart)) {
							foreach($cart->get_cart() as $cart_item){
							if(b2bking()->b2bking_has_category($applies[1], 'product_cat', $cart_item['product_id'])){
								$item_qty = $cart_item["quantity"];// Quantity
								$item_line_total = $cart_item["line_total"]; // Item total price (price x quantity)
								$category_products_number += $item_qty; // ctotal number of items in cart
								$category_products_value += $item_line_total; // calculated total items amount
							}
							}
						}
					} else if ($applies[0] === 'product') {
						if(is_object($cart)) {
							foreach($cart->get_cart() as $cart_item){
							if(intval($current_product_id) === intval($cart_item['product_id'])){
								$item_qty = $cart_item["quantity"];// Quantity
								if (isset($cart_item['line_total'])){
									$item_line_total = $cart_item["line_total"]; // Item total price (price x quantity)
									$products_value += $item_line_total; // calculated total items amount
								} 
								$products_number += $item_qty; // ctotal number of items in cart
							
							}
							}
						}
					}

					foreach ($conditions as $condition){
						$condition_details = explode(';',$condition);

		    			if (substr($condition_details[0], -5) === 'value') {
							$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
						}

						switch ($condition_details[0]){
							case 'product_quantity':
								switch ($condition_details[1]){
									case 'greater':
										if (! ($products_number > intval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'equal':
										if (! ($products_number === intval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'smaller':
										if (! ($products_number < intval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
								}
								break;
							
							case 'category_product_quantity':
								switch ($condition_details[1]){
									case 'greater':
										if (! ($category_products_number > intval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'equal':
										if (! ($category_products_number === intval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'smaller':
										if (! ($category_products_number < intval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
								}
								break;
							
							case 'cart_total_quantity':
								switch ($condition_details[1]){
									case 'greater':
										if (! ($cart->cart_contents_count > intval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'equal':
										if (! ($cart->cart_contents_count === intval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'smaller':
										if (! ($cart->cart_contents_count < intval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
								}
								break;
							
						}
					}
				}		
			}

			// Passed conditions
			if ($passconditions === 'yes'){
				if ($have_required_multiple === NULL){
					$have_required_multiple = 'yes';
					$smallest_required_multiple = floatval($howmuch);
				} else {
					if (floatval($howmuch) < $smallest_required_multiple){
						$smallest_required_multiple = floatval($howmuch);
					}   
				}
			} else {
				// do nothing
			}

		} //foreach end


		if($have_required_multiple !== NULL){

			$step = $smallest_required_multiple;

			if ( ! is_cart() && ! b2bking()->is_side_cart()) {
				$args['input_value'] = $step;
				$args['min_value'] = $step; 
				$args['min_qty'] = $step; 
				$args['step'] = $step; 
			} else {
				$args['min_value'] = $step;
				$args['min_qty'] = $step; 
				$args['step'] = $step; 
			}

		} else {
			// NOTHING WAS FOUND, TRY TO SEARCH FOR PARENT IN CASE OF VARIATION 
			// THIS BRACKET IS INVALIDATED IF STEP BECOMES SUPPORTED FOR woocommerce_available_variation
			// Get parent product


			// REMOVED SINCE B2BKING 4.2.44 - step support JS now added

			// only usable via filters
			if (apply_filters('b2bking_required_multiple_variation_use_parent', false)){
				$current_parentproduct_id = wp_get_post_parent_id($current_product_id);
				// if there is a parent
				if ($current_parentproduct_id !== 0){

					

					// 1) Get all rules and check if any rules apply to the product
				//	$rules_that_apply_to_product = get_transient('b2bking_required_multiple_rules_apply_'.$current_parentproduct_id);
					$rules_that_apply_to_product = b2bking()->get_global_data('b2bking_required_multiple_rules_apply',$current_parentproduct_id);
					if (!$rules_that_apply_to_product){

						$rules_that_apply = array();
						$required_multiple_rules_option = get_option('b2bking_have_required_multiple_rules_list_ids', '');
						if (!empty($required_multiple_rules_option)){
							$required_multiple_rules_v2_ids = explode(',',$required_multiple_rules_option);
						} else {
							$required_multiple_rules_v2_ids = array();
						}

						foreach ($required_multiple_rules_v2_ids as $rule_id){
							$applies = get_post_meta($rule_id,'b2bking_rule_applies', true);
							if ($applies === 'product_'.$current_parentproduct_id){
								array_push($rules_that_apply, $rule_id);
							} else if ($applies === 'multiple_options'){
								$multiple_options = get_post_meta($rule_id,'b2bking_rule_applies_multiple_options', true);
								$multiple_options_array = explode(',', $multiple_options);
								if (in_array('product_'.$current_parentproduct_id, $multiple_options_array)){
									array_push($rules_that_apply, $rule_id);
								}
							}
						}

						//set_transient('b2bking_required_multiple_rules_apply_'.$current_parentproduct_id,$rules_that_apply);
						b2bking()->set_global_data('b2bking_required_multiple_rules_apply', $rules_that_apply, $current_parentproduct_id);
						$rules_that_apply_to_product = $rules_that_apply;
					}
					// 2) If no rules apply for product, set transient for current user to empty array
					if (empty($rules_that_apply_to_product)){
						//set_transient('b2bking_required_multiple_'.$current_parentproduct_id.'_'.get_current_user_id(), array());
						b2bking()->set_global_data('b2bking_required_multiple', array(), $current_parentproduct_id, get_current_user_id());
					} else {
						// if transient does not already exist
					//	if (!get_transient('b2bking_required_multiple_'.$current_parentproduct_id.'_'.get_current_user_id())){
						if (!b2bking()->get_global_data('b2bking_required_multiple',$current_parentproduct_id,get_current_user_id())){

							// 3) If some rules apply, for each rule, check if it applies to the current user and build array.
							$rules_that_apply_to_user = array();
							$user_id = get_current_user_id();
							$account_type = get_user_meta($user_id,'b2bking_account_type', true);
							if ($account_type === 'subaccount'){
								// for all intents and purposes set current user as the subaccount parent
								$parent_user_id = get_user_meta($user_id, 'b2bking_account_parent', true);
								$user_id = $parent_user_id;
							}

							$user_is_b2b = get_user_meta($user_id,'b2bking_b2buser', true);
							$user_group = b2bking()->get_user_group($user_id);

							foreach ($rules_that_apply_to_product as $rule_id){
								$who = get_post_meta($rule_id,'b2bking_rule_who', true);
								// first check guest users
								if ($user_id === 0){
									if ($who === 'user_0'){
										array_push($rules_that_apply_to_user, $rule_id);
									} else if ($who === 'multiple_options'){
										$multiple_options = get_post_meta($rule_id,'b2bking_rule_who_multiple_options', true);
										$multiple_options_array = explode(',',$multiple_options);
										if (in_array('user_0',$multiple_options_array)){
											array_push($rules_that_apply_to_user, $rule_id);
										}
									}
								} else {
									// user is not guest
									if ($who === 'all_registered' || $who === 'user_'.$user_id){
										array_push($rules_that_apply_to_user, $rule_id);
									} else {
										if ($user_is_b2b !== 'yes'){
											// user is b2c
											if ($who === 'everyone_registered_b2c'){
												array_push($rules_that_apply_to_user, $rule_id);
											} else if ($who === 'multiple_options'){
												$multiple_options = get_post_meta($rule_id,'b2bking_rule_who_multiple_options', true);
												$multiple_options_array = explode(',',$multiple_options);
												if (in_array('everyone_registered_b2c',$multiple_options_array)){
													array_push($rules_that_apply_to_user, $rule_id);
												} else if (in_array('user_'.$user_id,$multiple_options_array)){
													array_push($rules_that_apply_to_user, $rule_id);
												}
											}
										} else {	
											// user is b2b
											if ($who === 'everyone_registered_b2b'){
												array_push($rules_that_apply_to_user, $rule_id);
											} else if ($who === 'group_'.$user_group){
												array_push($rules_that_apply_to_user, $rule_id);
											} else if ($who === 'multiple_options'){
												$multiple_options = get_post_meta($rule_id,'b2bking_rule_who_multiple_options', true);
												$multiple_options_array = explode(',',$multiple_options);
												if (in_array('everyone_registered_b2b',$multiple_options_array)){
													array_push($rules_that_apply_to_user, $rule_id);
												} else if (in_array('user_'.$user_id,$multiple_options_array)){
													array_push($rules_that_apply_to_user, $rule_id);
												} else if (in_array('group_'.$user_group,$multiple_options_array)){
													array_push($rules_that_apply_to_user, $rule_id);
												}
											}
										}
									}
								}
							}

							// either an empty array or an array with rules
							//set_transient('b2bking_required_multiple_'.$current_parentproduct_id.'_'.get_current_user_id(), $rules_that_apply_to_user);
							b2bking()->set_global_data('b2bking_required_multiple', $rules_that_apply_to_user, $current_parentproduct_id, get_current_user_id());
						}
					}

					
				//	$required_multiple_rules = get_transient('b2bking_required_multiple_'.$current_parentproduct_id.'_'.get_current_user_id());
					$required_multiple_rules = b2bking()->get_global_data('b2bking_required_multiple',$current_parentproduct_id,get_current_user_id());

					if (empty($required_multiple_rules)){

					} else {
						$current_product_categories = b2bking()->get_all_product_categories( $current_parentproduct_id, 'product_cat' );
						if (empty($current_product_categories)){
							// if no categories, this may be a variation, check parent categories
							$possible_parent_id = wp_get_post_parent_id($current_parentproduct_id);
							if ($possible_parent_id !== 0){
								// if product has parent
								$current_product_categories = b2bking()->get_all_product_categories( $possible_parent_id, 'product_cat' );
							}
						}
						$current_product_belongsto_array = array_map(function($value) { return 'category_'.$value; }, $current_product_categories);
						// add the product to the array to search for all relevant rules
						array_push($current_product_belongsto_array, 'product_'.$current_parentproduct_id);
					}

					if (empty($required_multiple_rules)){
						$required_multiple_rules = array();
					}

					// if multiple fapply, give the smallest to the user
					$have_required_multiple = NULL;
					$smallest_required_multiple = 0;

					foreach ($required_multiple_rules as $required_multiple_rule){
						// Get rule details
						$type = get_post_meta($required_multiple_rule, 'b2bking_rule_what', true);
						$howmuch = get_post_meta($required_multiple_rule, 'b2bking_rule_howmuch', true);

						$applies = explode('_',get_post_meta($required_multiple_rule, 'b2bking_rule_applies', true));
						$rule_multiple_options = get_post_meta($required_multiple_rule, 'b2bking_rule_applies_multiple_options', true);
						$rule_multiple_options_array = explode(',',$rule_multiple_options);
						$cart = WC()->cart;
						// Get conditions
						$passconditions = 'yes';
						$conditions = get_post_meta($required_multiple_rule, 'b2bking_rule_conditions', true);
						
						if (!empty($conditions)){
							$conditions = explode('|',$conditions);

							if ($applies[0] === 'multiple'){ // if rule is multiple products / categories rule
								$temporary_pass_conditions = 'no'; // if at least 1 element passes, change to yes
								// for each element that applies to the product and is part of the rule, check if at least 1 passes the conditions
								foreach($current_product_belongsto_array as $element){
									if(in_array($element, $rule_multiple_options_array)){
										$element_array = explode('_', $element);
										// if element is product or if element is category
										if ($element_array[0] === 'product'){
											$passes_inside_conditions = 'yes';
											$product_quantity = 0;
											if(is_object($cart)) {
												foreach($cart->get_cart() as $cart_item){
												if(intval($element_array[1]) === intval($cart_item['product_id'])){
													$product_quantity = $cart_item["quantity"];// Quantity
													break;
												}
												}
											}
											// check all product conditions against it
											foreach ($conditions as $condition){
												$condition_details = explode(';',$condition);

									    		if (substr($condition_details[0], -5) === 'value') {
													$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
												}

												switch ($condition_details[0]){
													case 'product_quantity':
														switch ($condition_details[1]){
															case 'greater':
																if (! ($product_quantity > intval($condition_details[2]))){
																	$passes_inside_conditions = 'no';
																	break 3;
																}
															break;
															case 'equal':
																if (! ($product_quantity === intval($condition_details[2]))){
																	$passes_inside_conditions = 'no';
																	break 3;
																}
															break;
															case 'smaller':
																if (! ($product_quantity < intval($condition_details[2]))){
																	$passes_inside_conditions = 'no';
																	break 3;
																}
															break;
														}
														break;
													case 'cart_total_quantity':
														switch ($condition_details[1]){
															case 'greater':
																if (! ($cart->cart_contents_count > intval($condition_details[2]))){
																	$passes_inside_conditions = 'no';
																	break 3;
																}
															break;
															case 'equal':
																if (! ($cart->cart_contents_count === intval($condition_details[2]))){
																	$passes_inside_conditions = 'no';
																	break 3;
																}
															break;
															case 'smaller':
																if (! ($cart->cart_contents_count < intval($condition_details[2]))){
																	$passes_inside_conditions = 'no';
																	break 3;
																}
															break;
														}
														break;
												}
											}
											if ($passes_inside_conditions === 'yes'){
												$temporary_pass_conditions = 'yes';
												break; // if 1 element passed, no need to check all other elements
											}
										} else if ($element_array[0] === 'category'){
											// check all category conditions against it + car total conditions
											$passes_inside_conditions = 'yes';
											$category_quantity = 0;
											if(is_object($cart)) {
												foreach($cart->get_cart() as $cart_item){
													if(b2bking()->b2bking_has_category($element_array[1], 'product_cat', $cart_item['product_id'])){
														$category_quantity += $cart_item["quantity"]; // add item quantity
													}
												}
											}
											foreach ($conditions as $condition){
												$condition_details = explode(';',$condition);

								    			if (substr($condition_details[0], -5) === 'value') {
													$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
												}

												switch ($condition_details[0]){
													case 'category_product_quantity':
														switch ($condition_details[1]){
															case 'greater':
																if (! ($category_quantity > intval($condition_details[2]))){
																	$passes_inside_conditions = 'no';
																	break 3;
																}
															break;
															case 'equal':
																if (! ($category_quantity === intval($condition_details[2]))){
																	$passes_inside_conditions = 'no';
																	break 3;
																}
															break;
															case 'smaller':
																if (! ($category_quantity < intval($condition_details[2]))){
																	$passes_inside_conditions = 'no';
																	break 3;
																}
															break;
														}
														break;
													case 'cart_total_quantity':
														switch ($condition_details[1]){
															case 'greater':
																if (! ($cart->cart_contents_count > intval($condition_details[2]))){
																	$passes_inside_conditions = 'no';
																	break 3;
																}
															break;
															case 'equal':
																if (! ($cart->cart_contents_count === intval($condition_details[2]))){
																	$passes_inside_conditions = 'no';
																	break 3;
																}
															break;
															case 'smaller':
																if (! ($cart->cart_contents_count < intval($condition_details[2]))){
																	$passes_inside_conditions = 'no';
																	break 3;
																}
															break;
														}
														break;
												}
											}
											if ($passes_inside_conditions === 'yes'){
												$temporary_pass_conditions = 'yes';
												break; // if 1 element passed, no need to check all other elements
											}
										}
									}
								} //foreach element end

								if ($temporary_pass_conditions === 'no'){
									$passconditions = 'no';
								}

							} else { // if rule is simple product, category, or total cart role 
								$category_products_number = 0;
								$category_products_value = 0;
								$products_number = 0;
								$products_value = 0;
								

								// Check rule is category rule or product rule
								if ($applies[0] === 'category'){

									// Calculate number of products in cart of this category AND total price of these products
									if(is_object($cart)) {
										foreach($cart->get_cart() as $cart_item){
										if(b2bking()->b2bking_has_category($applies[1], 'product_cat', $cart_item['product_id'])){
											$item_qty = $cart_item["quantity"];// Quantity
											$item_line_total = $cart_item["line_total"]; // Item total price (price x quantity)
											$category_products_number += $item_qty; // ctotal number of items in cart
											$category_products_value += $item_line_total; // calculated total items amount
										}
										}
									}
								} else if ($applies[0] === 'product') {
									if(is_object($cart)) {
										foreach($cart->get_cart() as $cart_item){
										if(intval($current_parentproduct_id) === intval($cart_item['product_id'])){
											$item_qty = $cart_item["quantity"];// Quantity
											if (isset($cart_item['line_total'])){
												$item_line_total = $cart_item["line_total"]; // Item total price (price x quantity)
												$products_value += $item_line_total; // calculated total items amount
											} 
											$products_number += $item_qty; // ctotal number of items in cart
										
										}
										}
									}
								}

								foreach ($conditions as $condition){
									$condition_details = explode(';',$condition);

					    			if (substr($condition_details[0], -5) === 'value') {
										$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
									}

									switch ($condition_details[0]){
										case 'product_quantity':
											switch ($condition_details[1]){
												case 'greater':
													if (! ($products_number > intval($condition_details[2]))){
														$passconditions = 'no';
														break 3;
													}
												break;
												case 'equal':
													if (! ($products_number === intval($condition_details[2]))){
														$passconditions = 'no';
														break 3;
													}
												break;
												case 'smaller':
													if (! ($products_number < intval($condition_details[2]))){
														$passconditions = 'no';
														break 3;
													}
												break;
											}
											break;
										
										case 'category_product_quantity':
											switch ($condition_details[1]){
												case 'greater':
													if (! ($category_products_number > intval($condition_details[2]))){
														$passconditions = 'no';
														break 3;
													}
												break;
												case 'equal':
													if (! ($category_products_number === intval($condition_details[2]))){
														$passconditions = 'no';
														break 3;
													}
												break;
												case 'smaller':
													if (! ($category_products_number < intval($condition_details[2]))){
														$passconditions = 'no';
														break 3;
													}
												break;
											}
											break;
										
										case 'cart_total_quantity':
											switch ($condition_details[1]){
												case 'greater':
													if (! ($cart->cart_contents_count > intval($condition_details[2]))){
														$passconditions = 'no';
														break 3;
													}
												break;
												case 'equal':
													if (! ($cart->cart_contents_count === intval($condition_details[2]))){
														$passconditions = 'no';
														break 3;
													}
												break;
												case 'smaller':
													if (! ($cart->cart_contents_count < intval($condition_details[2]))){
														$passconditions = 'no';
														break 3;
													}
												break;
											}
											break;
										
									}
								}
							}		
						}

						// Passed conditions
						if ($passconditions === 'yes'){
							if ($have_required_multiple === NULL){
								$have_required_multiple = 'yes';
								$smallest_required_multiple = floatval($howmuch);
							} else {
								if (floatval($howmuch) < $smallest_required_multiple){
									$smallest_required_multiple = floatval($howmuch);
								}   
							}
						} else {
							// do nothing
						}

					} //foreach end


					if($have_required_multiple !== NULL){

						$step = $smallest_required_multiple;

						if ( ! is_cart() && ! b2bking()->is_side_cart() ) {
							$args['input_value'] = $step;
							$args['min_value'] = $step; 
							$args['min_qty'] = $step; 
							$args['step'] = $step; 
						} else {
							$args['min_value'] = $step;
							$args['min_qty'] = $step; 
							$args['step'] = $step; 
						}

					}
				}
			}

			
		}


		return $args;

	}

	public static function b2bking_dynamic_rule_required_multiple_quantity_number( $quantity, $product_id ) {
			$product = wc_get_product($product_id);

			if (!is_a($product,'WC_Product') && !is_a($product,'WC_Product_Variation')){
				return $quantity;
			}

			if ($product->is_type('variable')){
			//  deactivated  4.2.14
			//	return $quantity;
			}

			$user_id = get_current_user_id();
			$account_type = get_user_meta($user_id,'b2bking_account_type', true);
			if ($account_type === 'subaccount'){
				// for all intents and purposes set current user as the subaccount parent
				$parent_user_id = get_user_meta($user_id, 'b2bking_account_parent', true);
				$user_id = $parent_user_id;
			}

			// Get current product
			$current_product_id = $product_id;

			$currentusergroupidnr = b2bking()->get_user_group($user_id);
			if (!$currentusergroupidnr || empty($currentusergroupidnr)){
				$currentusergroupidnr = 'invalid';
			}

			$response = b2bking()->get_applicable_rules('required_multiple', $current_product_id, $user_id);

			// abort early
			if ($response === 'norules'){
				return $quantity;
			}

			$required_multiple_rules = $response[0];
			$current_product_belongsto_array = $response[1];

			// if multiple fapply, give the smallest to the user
			$have_required_multiple = NULL;
			$smallest_required_multiple = 0;

			foreach ($required_multiple_rules as $required_multiple_rule){
				// Get rule details
				$type = get_post_meta($required_multiple_rule, 'b2bking_rule_what', true);
				$howmuch = get_post_meta($required_multiple_rule, 'b2bking_rule_howmuch', true);
				$applies = explode('_',get_post_meta($required_multiple_rule, 'b2bking_rule_applies', true));
				$rule_multiple_options = get_post_meta($required_multiple_rule, 'b2bking_rule_applies_multiple_options', true);
				$rule_multiple_options_array = explode(',',$rule_multiple_options);
				$cart = WC()->cart;
				// Get conditions
				$passconditions = 'yes';
				$conditions = get_post_meta($required_multiple_rule, 'b2bking_rule_conditions', true);
				
				if (!empty($conditions)){
					$conditions = explode('|',$conditions);

					if ($applies[0] === 'multiple'){ // if rule is multiple products / categories rule
						$temporary_pass_conditions = 'no'; // if at least 1 element passes, change to yes
						// for each element that applies to the product and is part of the rule, check if at least 1 passes the conditions
						foreach($current_product_belongsto_array as $element){
							if(in_array($element, $rule_multiple_options_array)){
								$element_array = explode('_', $element);
								// if element is product or if element is category
								if ($element_array[0] === 'product'){
									$passes_inside_conditions = 'yes';
									$product_quantity = 0;
									if(is_object($cart)) {
										foreach($cart->get_cart() as $cart_item){
										if(intval($element_array[1]) === intval($cart_item['product_id'])){
											$product_quantity = $cart_item["quantity"];// Quantity
											break;
										}
										}
									}
									// check all product conditions against it
									foreach ($conditions as $condition){
										$condition_details = explode(';',$condition);

					    				if (substr($condition_details[0], -5) === 'value') {
											$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
										}

										switch ($condition_details[0]){
											case 'product_quantity':
												switch ($condition_details[1]){
													case 'greater':
														if (! ($product_quantity > intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
													case 'equal':
														if (! ($product_quantity === intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
													case 'smaller':
														if (! ($product_quantity < intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
												}
												break;
											case 'cart_total_quantity':
												switch ($condition_details[1]){
													case 'greater':
														if (! ($cart->cart_contents_count > intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
													case 'equal':
														if (! ($cart->cart_contents_count === intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
													case 'smaller':
														if (! ($cart->cart_contents_count < intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
												}
												break;
										}
									}
									if ($passes_inside_conditions === 'yes'){
										$temporary_pass_conditions = 'yes';
										break; // if 1 element passed, no need to check all other elements
									}
								} else if ($element_array[0] === 'category'){
									// check all category conditions against it + car total conditions
									$passes_inside_conditions = 'yes';
									$category_quantity = 0;
									if(is_object($cart)) {
										foreach($cart->get_cart() as $cart_item){
											if(b2bking()->b2bking_has_category($element_array[1], 'product_cat', $cart_item['product_id'])){
												$category_quantity += $cart_item["quantity"]; // add item quantity
											}
										}
									}
									foreach ($conditions as $condition){
										$condition_details = explode(';',$condition);

					    				if (substr($condition_details[0], -5) === 'value') {
											$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
										}

										switch ($condition_details[0]){
											case 'category_product_quantity':
												switch ($condition_details[1]){
													case 'greater':
														if (! ($category_quantity > intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
													case 'equal':
														if (! ($category_quantity === intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
													case 'smaller':
														if (! ($category_quantity < intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
												}
												break;
											case 'cart_total_quantity':
												switch ($condition_details[1]){
													case 'greater':
														if (! ($cart->cart_contents_count > intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
													case 'equal':
														if (! ($cart->cart_contents_count === intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
													case 'smaller':
														if (! ($cart->cart_contents_count < intval($condition_details[2]))){
															$passes_inside_conditions = 'no';
															break 3;
														}
													break;
												}
												break;
										}
									}
									if ($passes_inside_conditions === 'yes'){
										$temporary_pass_conditions = 'yes';
										break; // if 1 element passed, no need to check all other elements
									}
								}
							}
						} //foreach element end

						if ($temporary_pass_conditions === 'no'){
							$passconditions = 'no';
						}

					} else { // if rule is simple product, category, or total cart role 
						$category_products_number = 0;
						$category_products_value = 0;
						$products_number = 0;
						$products_value = 0;
						

						// Check rule is category rule or product rule
						if ($applies[0] === 'category'){

							// Calculate number of products in cart of this category AND total price of these products
							if(is_object($cart)) {
								foreach($cart->get_cart() as $cart_item){
								if(b2bking()->b2bking_has_category($applies[1], 'product_cat', $cart_item['product_id'])){
									$item_qty = $cart_item["quantity"];// Quantity
									$item_line_total = $cart_item["line_total"]; // Item total price (price x quantity)
									$category_products_number += $item_qty; // ctotal number of items in cart
									$category_products_value += $item_line_total; // calculated total items amount
								}
								}
							}
						} else if ($applies[0] === 'product') {
							if(is_object($cart)) {
								foreach($cart->get_cart() as $cart_item){
								if(intval($current_product_id) === intval($cart_item['product_id'])){
									$item_qty = $cart_item["quantity"];// Quantity
									if (isset($cart_item['line_total'])){
										$item_line_total = $cart_item["line_total"]; // Item total price (price x quantity)
										$products_value += $item_line_total; // calculated total items amount
									} 
									$products_number += $item_qty; // ctotal number of items in cart
								
								}
								}
							}
						}

						foreach ($conditions as $condition){
							$condition_details = explode(';',$condition);

			    			if (substr($condition_details[0], -5) === 'value') {
									$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
								}
							switch ($condition_details[0]){
								case 'product_quantity':
									switch ($condition_details[1]){
										case 'greater':
											if (! ($products_number > intval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
										case 'equal':
											if (! ($products_number === intval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
										case 'smaller':
											if (! ($products_number < intval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
									}
									break;
								
								case 'category_product_quantity':
									switch ($condition_details[1]){
										case 'greater':
											if (! ($category_products_number > intval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
										case 'equal':
											if (! ($category_products_number === intval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
										case 'smaller':
											if (! ($category_products_number < intval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
									}
									break;
								
								case 'cart_total_quantity':
									switch ($condition_details[1]){
										case 'greater':
											if (! ($cart->cart_contents_count > intval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
										case 'equal':
											if (! ($cart->cart_contents_count === intval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
										case 'smaller':
											if (! ($cart->cart_contents_count < intval($condition_details[2]))){
												$passconditions = 'no';
												break 3;
											}
										break;
									}
									break;
								
							}
						}
					}		
				}

				// Passed conditions
				if ($passconditions === 'yes'){
					if ($have_required_multiple === NULL){
						$have_required_multiple = 'yes';
						$smallest_required_multiple = floatval($howmuch);
					} else {
						if (floatval($howmuch) < $smallest_required_multiple){
							$smallest_required_multiple = floatval($howmuch);
						}   
					}
				} else {
					// do nothing
				}

			} //foreach end
			if($have_required_multiple !== NULL){

				$step = $smallest_required_multiple;

				if ($quantity < $step){
					return $step;
				}

			} 
							   
			return $quantity;

	}

	public static function b2bking_dynamic_rule_required_multiple(){

		$user_id = get_current_user_id();
		$cart = WC()->cart;

		//	$required_multiple_rules = get_transient('b2bking_required_multiple_'.get_current_user_id());
		$required_multiple_rules = b2bking()->get_global_data('b2bking_required_multiple', false, get_current_user_id());
		if (!$required_multiple_rules){

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

			$rules_ids_elements = get_option('b2bking_have_required_multiple_rules_list_ids_elements', array());

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

		//	set_transient ('b2bking_required_multiple_'.get_current_user_id(), $user_applicable_rules);
			b2bking()->set_global_data('b2bking_required_multiple', $user_applicable_rules, false, get_current_user_id());
			$required_multiple_rules = $user_applicable_rules;
		}

		foreach ($required_multiple_rules as $required_multiple_rule){
			// Get rule details
			$applies = explode('_',get_post_meta($required_multiple_rule, 'b2bking_rule_applies', true));
			$howmuch = floatval(get_post_meta($required_multiple_rule, 'b2bking_rule_howmuch', true));

			if($applies[0] === 'multiple'){
				$rule_multiple_options = get_post_meta($required_multiple_rule, 'b2bking_rule_applies_multiple_options', true);
				$rule_multiple_options_array = explode(',',$rule_multiple_options);
				// For each elementof the rule (Category or product)
				foreach ($rule_multiple_options_array as $rule_element){
					$rule_element_array = explode('_',$rule_element);
					// if is category
					if ($rule_element_array[0] === 'category'){
						$category_products_number = 0;
						$category_products_value = 0;
						if(is_object($cart)) {
							foreach($cart->get_cart() as $cart_item){
							if(b2bking()->b2bking_has_category($rule_element_array[1], 'product_cat', $cart_item['product_id'])){
								$item_price = $cart_item['data']->get_price(); 
								$item_qty = $cart_item["quantity"];// Quantity
								$item_line_total = $cart_item["line_total"]; // Item total price (price x quantity)
								$category_products_number += $item_qty; // ctotal number of items in cart
								$category_products_value += $item_line_total; // calculated total items amount
							}
							}
						}

						// Check rule applicability conditions
						$passconditions = 'yes';
						$conditions = get_post_meta($required_multiple_rule, 'b2bking_rule_conditions', true);

						if (!empty($conditions)){
							$conditions = explode('|',$conditions);
							foreach ($conditions as $condition){
								$condition_details = explode(';',$condition);

					    		if (substr($condition_details[0], -5) === 'value') {
									$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
								}
								switch ($condition_details[0]){
									case 'category_product_quantity':
										switch ($condition_details[1]){
											case 'greater':
												if (! ($category_products_number > intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'equal':
												if (! ($category_products_number === intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'smaller':
												if (! ($category_products_number < intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
										}
										break;

									case 'category_product_value':
										switch ($condition_details[1]){
											case 'greater':
												if (! (floatval($category_products_value) > floatval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'equal':
												if (! (floatval($category_products_value) === floatval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'smaller':
												if (! (floatval($category_products_value) < floatval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
										}
										break;
									case 'cart_total_quantity':
										switch ($condition_details[1]){
											case 'greater':
												if (! ($cart->cart_contents_count > intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'equal':
												if (! ($cart->cart_contents_count === intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'smaller':
												if (! ($cart->cart_contents_count < intval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
										}
										break;
									case 'cart_total_value':
										switch ($condition_details[1]){
											case 'greater':
												if (! (floatval(apply_filters('b2bking_cart_subtotal', WC()->cart->get_subtotal())) > floatval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'equal':
												if (! (floatval(apply_filters('b2bking_cart_subtotal', WC()->cart->get_subtotal())) === floatval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
											case 'smaller':
												if (! (floatval(apply_filters('b2bking_cart_subtotal', WC()->cart->get_subtotal())) < floatval($condition_details[2]))){
													$passconditions = 'no';
													break 3;
												}
											break;
										}
										break;
								}
							}
						}

						// Passed conditions
						if ($passconditions === 'yes'){
							$category_name = get_term( $rule_element_array[1] )->name;
							if ( (b2bking()->custom_modulo($category_products_number, $howmuch) ) > 0) {
								// get all products in the category and show them
								$names_list = '';
								if(is_object($cart)) {
									foreach($cart->get_cart() as $cart_item){
										if(b2bking()->b2bking_has_category($rule_element_array[1], 'product_cat', $cart_item['product_id'])){
											$name = wc_get_product($cart_item['product_id'])->get_name();
											$names_list.= $name.', ';
										}
									}
								}
								// remove last 2 chars (comma and space)
								$names_list = substr($names_list, 0, -2);
								wc_add_notice( sprintf( esc_html__('Products in the %s category must be purchased in multiples of %s products: '.$names_list, 'b2bking'), $category_name, $howmuch ), 'error' );
							}
						}

					// if is product
					} else if ($rule_element_array[0] === 'product'){
						if ($rule_element_array[1] !== ''){
								$products_number = 0;
								$products_value = 0;
								if(is_object($cart)) {
									foreach($cart->get_cart() as $cart_item){
									if(intval($rule_element_array[1]) === $cart_item['product_id'] || intval($rule_element_array[1]) === $cart_item['variation_id']){
										$item_price = $cart_item['data']->get_price(); 
										$item_qty = $cart_item["quantity"];// Quantity
										$item_line_total = $cart_item["line_total"]; // Item total price (price x quantity)
										$products_number += $item_qty; // ctotal number of items in cart
										$products_value += $item_line_total; // calculated total items amount
									}
									}
								}
								// Check rule applicability conditions
								$passconditions = 'yes';
								$conditions = get_post_meta($required_multiple_rule, 'b2bking_rule_conditions', true);

								if (!empty($conditions)){
									$conditions = explode('|',$conditions);
									foreach ($conditions as $condition){
										$condition_details = explode(';',$condition);

							    		if (substr($condition_details[0], -5) === 'value') {
									$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
								}
										switch ($condition_details[0]){
											case 'product_quantity':
												switch ($condition_details[1]){
													case 'greater':
														if (! ($products_number > intval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
													case 'equal':
														if (! ($products_number === intval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
													case 'smaller':
														if (! ($products_number < intval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
												}
												break;

											case 'product_value':
												switch ($condition_details[1]){
													case 'greater':
														if (! (floatval($products_value) > floatval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
													case 'equal':
														if (! (floatval($products_value) === floatval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
													case 'smaller':
														if (! (floatval($products_value) < floatval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
												}
												break;
											
											case 'cart_total_quantity':
												switch ($condition_details[1]){
													case 'greater':
														if (! ($cart->cart_contents_count > intval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
													case 'equal':
														if (! ($cart->cart_contents_count === intval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
													case 'smaller':
														if (! ($cart->cart_contents_count < intval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
												}
												break;
											case 'cart_total_value':
												switch ($condition_details[1]){
													case 'greater':
														if (! (floatval(apply_filters('b2bking_cart_subtotal', WC()->cart->get_subtotal())) > floatval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
													case 'equal':
														if (! (floatval(apply_filters('b2bking_cart_subtotal', WC()->cart->get_subtotal())) === floatval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
													case 'smaller':
														if (! (floatval(apply_filters('b2bking_cart_subtotal', WC()->cart->get_subtotal())) < floatval($condition_details[2]))){
															$passconditions = 'no';
															break 3;
														}
													break;
												}
												break;
										}
									}
								}

								// Passed conditions
								if ($passconditions === 'yes'){
									$product_name = get_the_title(intval($rule_element_array[1]));

									if ( (b2bking()->custom_modulo($products_number, $howmuch) ) > 0) {
										wc_add_notice( sprintf( esc_html__('The product named %s must be purchased in multiples of %s pieces', 'b2bking'), $product_name, $howmuch ), 'error' );
									}	
								}

						}
						
					}
				}

			} else {

				$category_products_number = 0;
				$category_products_value = 0;
				$products_number = 0;
				$products_value = 0;

				// If rule is category or product rule, calculate numbers and value. If total cart rule, it is not necessary
				if ($applies[0] === 'category'){

					// Calculate number of products in cart of this category AND total price of these products
					if(is_object($cart)) {
						foreach($cart->get_cart() as $cart_item){
						if(b2bking()->b2bking_has_category($applies[1], 'product_cat', $cart_item['product_id'])){
							$item_price = $cart_item['data']->get_price(); 
							$item_qty = $cart_item["quantity"];// Quantity
							$item_line_total = $cart_item["line_total"]; // Item total price (price x quantity)
							$category_products_number += $item_qty; // ctotal number of items in cart
							$category_products_value += $item_line_total; // calculated total items amount
						}
						}
					}
				} else if ($applies[0] === 'product') {
					if(is_object($cart)) {
						foreach($cart->get_cart() as $cart_item){
						if(intval($applies[1]) === $cart_item['product_id'] || intval($applies[1]) === $cart_item['variation_id']){
							$item_price = $cart_item['data']->get_price(); 
							$item_qty = $cart_item["quantity"];// Quantity
							$item_line_total = $cart_item["line_total"]; // Item total price (price x quantity)
							$products_number += $item_qty; // ctotal number of items in cart
							$products_value += $item_line_total; // calculated total items amount
						}
						}
					}
				}

				// Check rule applicability conditions
				$passconditions = 'yes';
				$conditions = get_post_meta($required_multiple_rule, 'b2bking_rule_conditions', true);

				if (!empty($conditions)){
					$conditions = explode('|',$conditions);
					foreach ($conditions as $condition){
						$condition_details = explode(';',$condition);

			    		if (substr($condition_details[0], -5) === 'value') {
									$condition_details[2] = b2bking()->get_woocs_price($condition_details[2]);
								}
						switch ($condition_details[0]){
							case 'product_quantity':
								switch ($condition_details[1]){
									case 'greater':
										if (! ($products_number > intval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'equal':
										if (! ($products_number === intval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'smaller':
										if (! ($products_number < intval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
								}
								break;
							
							case 'category_product_quantity':
								switch ($condition_details[1]){
									case 'greater':
										if (! ($category_products_number > intval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'equal':
										if (! ($category_products_number === intval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'smaller':
										if (! ($category_products_number < intval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
								}
								break;
							case 'product_value':
								switch ($condition_details[1]){
									case 'greater':
										if (! (floatval($products_value) > floatval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'equal':
										if (! (floatval($products_value) === floatval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'smaller':
										if (! (floatval($products_value) < floatval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
								}
								break;
							
							case 'category_product_value':
								switch ($condition_details[1]){
									case 'greater':
										if (! (floatval($category_products_value) > floatval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'equal':
										if (! (floatval($category_products_value) === floatval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'smaller':
										if (! (floatval($category_products_value) < floatval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
								}
								break;
							case 'cart_total_quantity':
								switch ($condition_details[1]){
									case 'greater':
										if (! ($cart->cart_contents_count > intval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'equal':
										if (! ($cart->cart_contents_count === intval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'smaller':
										if (! ($cart->cart_contents_count < intval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
								}
								break;
							case 'cart_total_value':
								switch ($condition_details[1]){
									case 'greater':
										if (! (floatval(apply_filters('b2bking_cart_subtotal', WC()->cart->get_subtotal())) > floatval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'equal':
										if (! (floatval(apply_filters('b2bking_cart_subtotal', WC()->cart->get_subtotal())) === floatval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
									case 'smaller':
										if (! (floatval(apply_filters('b2bking_cart_subtotal', WC()->cart->get_subtotal())) < floatval($condition_details[2]))){
											$passconditions = 'no';
											break 3;
										}
									break;
								}
								break;
						}
					}
				}

				// Passed conditions
				if ($passconditions === 'yes'){
					// Rule passed conditions, therefore it applies. Continue by checking the actual rule content.
					$howmuch = floatval(get_post_meta($required_multiple_rule, 'b2bking_rule_howmuch', true));

					// cart rule
					if ($applies[0] === 'cart'){
						if ( ( b2bking()->custom_modulo($cart->cart_contents_count, $howmuch) ) > 0) {
							wc_add_notice( sprintf( esc_html__('Total cart quantity purchased must be in multiples of %s products', 'b2bking'), $howmuch ), 'error' );
						}
					// category rule
					} else if ($applies[0] === 'category'){
						$category_name = get_term( $applies[1] )->name;

						if ( (b2bking()->custom_modulo($category_products_number, $howmuch) ) > 0) {
							// get all products in the category and show them
							$names_list = '';
							if(is_object($cart)) {
								foreach($cart->get_cart() as $cart_item){
									if(b2bking()->b2bking_has_category($applies[1], 'product_cat', $cart_item['product_id'])){
										$name = wc_get_product($cart_item['product_id'])->get_name();
										$names_list.= $name.', ';
									}
								}
							}
							// remove last 2 chars (comma and space)
							$names_list = substr($names_list, 0, -2);
							wc_add_notice( sprintf( esc_html__('Products in the %s category must be purchased in multiples of %s products: '.$names_list, 'b2bking'), $category_name, $howmuch ), 'error' );
						}
					// product rule	
					} else if ($applies[0] === 'product'){
						$product_name = get_the_title(intval($applies[1]));

						if ( (b2bking()->custom_modulo($products_number, $howmuch) ) > 0) {
							wc_add_notice( sprintf( esc_html__('The product named %s must be purchased in multiples of %s pieces', 'b2bking'), $product_name, $howmuch ), 'error' );
						}	
					}
				}
			}
		}
	}


	public static function b2bking_dynamic_rule_zero_tax_product($tax_class, $product){

		// Get current product
		$current_product_id = $product->get_id();

		//	$tax_exemption_rules = get_transient('b2bking_tax_exemption_'.$current_product_id.'_'.get_current_user_id());
		$tax_exemption_rules = b2bking()->get_global_data('b2bking_tax_exemption',$current_product_id,get_current_user_id());
		if (!$tax_exemption_rules){

			$user_id = get_current_user_id();
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

			global $woocommerce;
			$customertest = $woocommerce->customer;

			if (is_a($customertest, 'WC_Customer')){
				$tax_setting = get_option('woocommerce_tax_based_on');
				if ($tax_setting === 'shipping'){
					$user_country = WC()->customer->get_shipping_country();
				} else {
					$user_country = WC()->customer->get_billing_country();
				}
			} else {
				$user_country = 'NOTACUSTOMER';
			}
			$user_vat = get_user_meta($user_id, 'b2bking_user_vat_status', true);

			$array_countries_and_requires = array(
								'relation' => 'AND',
								array(
									'key' => 'b2bking_rule_countries',
									'value' => $user_country,
									'compare'=> 'LIKE'
								),
								array(
									'relation' => 'OR',
									array(
										'key' => 'b2bking_rule_requires',
										'value' => 'nothing',
									),
									array(
										'key' => 'b2bking_rule_requires',
										'value' => $user_vat // should be 'validated_vat'
									),
								),
							);

			$variation_rules_apply = 'no';
			$post_parent_id = wp_get_post_parent_id($current_product_id);
			if ($post_parent_id !== 0){
				// product is variable, check if there are individual product rules
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

				$multiselect_array = array(
									'relation' => 'OR'
								);
				foreach($current_product_belongsto_array as $element){
					array_push($multiselect_array, array(
						'key' => 'b2bking_rule_applies_multiple_options',
						'value' => $element,
						'compare' => 'LIKE'
						)
					);
				}

				// Get all dynamic rules for fixed price that apply to the product or its categories for the user or the user's group
				$tax_exemption_ids = get_option('b2bking_have_tax_exemption_rules_list_ids', '');
				if (!empty($tax_exemption_ids)){
					$tax_exemption_ids = explode(',',$tax_exemption_ids);
				} else {
					$tax_exemption_ids = array();
				}
				$tax_exemption_rules_initial = get_posts([
					'post_type' => 'b2bking_rule',
					'post_status' => 'publish',
					'numberposts' => -1,
					'post__in' => $tax_exemption_ids,
					'fields'        => 'ids', // Only get post IDs
					'meta_query'=>  array(
							'relation' => 'OR',
							array(
									'key' => 'b2bking_rule_applies',
									'value' => $current_product_belongsto_array,
									'compare' => 'IN'
							),
							array(
									'key' => 'b2bking_rule_applies',
									'value' => 'cart_total',
							),
							array(
								// OR rule is Multi Select and contains the product or one of its categories
								'relation' => 'AND',
								array(
									'key' => 'b2bking_rule_applies',
									'value' => 'multiple_options',
								),
								$multiselect_array
							)
						)
				]);

				if (empty($tax_exemption_rules_initial)){
					$tax_exemption_rules = array();
				} else {
					$tax_exemption_rules = get_posts([
						'post_type' => 'b2bking_rule',
						'post__in' => $tax_exemption_rules_initial,
						'fields'        => 'ids', // Only get post IDs
						'numberposts' => -1,
						'meta_query'=> array(
							$array_who,
							$array_countries_and_requires, // also checks user's country and VAT requirements for the rule
						)
					]);
				}
				

				if (empty($tax_exemption_rules)){
					// no individual rules, apply parent rules
					$current_product_id = $post_parent_id;
				} else {
					$variation_rules_apply = 'yes';
				}
				
			}
			
			if ($variation_rules_apply === 'no'){
				// get rules again, with parent
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

				$multiselect_array = array(
									'relation' => 'OR'
								);
				foreach($current_product_belongsto_array as $element){
					array_push($multiselect_array, array(
						'key' => 'b2bking_rule_applies_multiple_options',
						'value' => $element,
						'compare' => 'LIKE'
						)
					);
				}
				

				// Get all dynamic rule tax exemptions that apply to the user or user's group
				$tax_exemption_ids = get_option('b2bking_have_tax_exemption_rules_list_ids', '');
				if (!empty($tax_exemption_ids)){
					$tax_exemption_ids = explode(',',$tax_exemption_ids);
				} else {
					$tax_exemption_ids = array();
				}
				$tax_exemption_rules_initial = get_posts([
					'post_type' => 'b2bking_rule',
					'post_status' => 'publish',
					'numberposts' => -1,
					'post__in' => $tax_exemption_ids,
					'fields'        => 'ids', // Only get post IDs
					'meta_query'=>  array(
							'relation' => 'OR',
							array(
									'key' => 'b2bking_rule_applies',
									'value' => $current_product_belongsto_array,
									'compare' => 'IN'
							),
							array(
									'key' => 'b2bking_rule_applies',
									'value' => 'cart_total',
							),
							array(
								// OR rule is Multi Select and contains the product or one of its categories
								'relation' => 'AND',
								array(
									'key' => 'b2bking_rule_applies',
									'value' => 'multiple_options',
								),
								$multiselect_array
							)
						)
				]);

				if (empty($tax_exemption_rules_initial)){
					$tax_exemption_rules = array();
				} else {
					$tax_exemption_rules = get_posts([
						'post_type' => 'b2bking_rule',
						'post__in' => $tax_exemption_rules_initial,
						'fields'        => 'ids', // Only get post IDs
						'numberposts' => -1,
						'meta_query'=> array(
							$array_who,
							$array_countries_and_requires, // also checks user's country and VAT requirements for the rule
						)
					]);
				}

			}

			//set_transient ('b2bking_tax_exemption_'.$current_product_id.'_'.get_current_user_id(), $tax_exemption_rules);
			b2bking()->set_global_data('b2bking_tax_exemption', $tax_exemption_rules, $current_product_id, get_current_user_id());
		}
		// if there are tax exemption rules, set tax rate as zero
		if (!empty($tax_exemption_rules)){
			$tax_class = 'Zero Rate';
		}
		return $tax_class;

	}

	public static function b2bking_dynamic_rule_tax_exemption(){
		$user_id = get_current_user_id();

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

		global $woocommerce;
		$customer = $woocommerce->customer;

		$exempt = false;

		if (is_a($customer, 'WC_Customer')){
			$tax_setting = get_option('woocommerce_tax_based_on');
			if ($tax_setting === 'shipping'){
				$user_country = WC()->customer->get_shipping_country();
			} else {
				$user_country = WC()->customer->get_billing_country();
			}

			$user_vat = get_user_meta($user_id, 'b2bking_user_vat_status', true);

			if ($user_vat !== 'validated_vat'){
				// if validate vat button is enabled
				if (intval(get_option('b2bking_validate_vat_button_checkout_setting', 0)) === 1){
					delete_transient('b2bking_tax_exemption_user_'.get_current_user_id());
					if(isset($_COOKIE['b2bking_validated_vat_status'])){
						$user_vat = sanitize_text_field($_COOKIE['b2bking_validated_vat_status']);
					} else {
						$user_vat = 'invalid';
					}
				}
			}

			$array_countries_and_requires = array(
								'relation' => 'AND',
								array(
									'key' => 'b2bking_rule_countries',
									'value' => $user_country,
									'compare'=> 'LIKE'
								),
								array(
									'relation' => 'OR',
									array(
										'key' => 'b2bking_rule_requires',
										'value' => 'nothing',
									),
									array(
										'key' => 'b2bking_rule_requires',
										'value' => $user_vat // should be 'validated_vat'
									),
								),
							);

			// Get all dynamic rule tax exemptions that apply to the user or user's group
			$tax_exemption_user_ids = get_option('b2bking_have_tax_exemption_user_rules_list_ids', '');
			if (!empty($tax_exemption_user_ids)){
				$tax_exemption_user_ids = explode(',',$tax_exemption_user_ids);
			} else {
				$tax_exemption_user_ids = array();
			}
			
			// CACHE CANNOT APPLY FOR LOGGED OUT USERS ( = user 0)
		//	$tax_exemption_rules = get_transient('b2bking_tax_exemption_user_'.get_current_user_id());
			$tax_exemption_rules = b2bking()->get_global_data('b2bking_tax_exemption_user',false,get_current_user_id());
			if (!$tax_exemption_rules || intval(get_current_user_id()) === 0){

				$tax_exemption_rules = get_posts([
					'post_type' => 'b2bking_rule',
					'post_status' => 'publish',
					'post__in' => $tax_exemption_user_ids,
					'fields'        => 'ids', // Only get post IDs
					'numberposts' => -1,
					'meta_query'=> array(
						'relation' => 'AND',
						$array_who,
						$array_countries_and_requires,
					)
				]);
				//set_transient ('b2bking_tax_exemption_user_'.get_current_user_id(), $tax_exemption_rules);
				b2bking()->set_global_data('b2bking_tax_exemption_user', $tax_exemption_rules, false, get_current_user_id());
			}

			// if user requires different country than shop country for tax exemption
			$donotexempt = false;				
			if (intval(get_option( 'b2bking_vat_exemption_different_country_setting', 0 )) === 1){
				// get if user is vat exempt
				$delivery_country = WC()->customer->get_shipping_country();

				// get shop country
				$default = wc_get_base_location();
				$shop_country = apply_filters( 'woocommerce_countries_base_country', $default['country'] );
				if ($delivery_country === $shop_country){
					// dont exempt from vat, delete tax exemption rules
					$donotexempt = true;
				}
			}

			// if there are tax exemption rules, set user as tax exempt
			if (!empty($tax_exemption_rules) && ($donotexempt === false)){

				// check if VAT should be displayed as fee. If yes, change mechanism to be done only with settings
				$display_vat_as_fee = 'no';
				foreach ($tax_exemption_rules as $rule){
					$vat_display = get_post_meta($rule, 'b2bking_rule_showtax', true);

					$shipping_included = get_post_meta($rule, 'b2bking_rule_tax_shipping', true);
					$shipping_included_rate = get_post_meta($rule, 'b2bking_rule_tax_shipping_rate', true);

					if ($vat_display === 'yes'){
						$display_vat_as_fee = 'yes';
						break;
					}
				}
				if ($display_vat_as_fee === 'yes'){
					WC()->customer->set_is_vat_exempt(false);
					$exempt = false;
				} else {
					// if there are tax exemption rules, set user as tax exempt
					WC()->customer->set_is_vat_exempt(true);
					$exempt = true;
				}
			} else {
				WC()->customer->set_is_vat_exempt(false);
				$exempt = false;
			}
		}

		// the one here overwrites the one in public
		if (intval(get_option( 'b2bking_modify_suffix_vat_setting', 0 )) === 1){
			if (apply_filters('b2bking_modify_suffix', true, get_current_user_id())){

				if ($exempt){
					add_filter( 'woocommerce_get_price_suffix', 'add_price_suffixb', 9999, 4 );
					  
					function add_price_suffixb( $html, $product, $price, $qty ){
					    $html = '<small class="woocommerce-price-suffix"> '.apply_filters('b2bking_price_suffix_ex_vat', esc_html__('ex. VAT', 'b2bking')).'</small>';
					    return $html;
					}
				} else {
					add_filter( 'woocommerce_get_price_suffix', 'add_price_suffixtwob', 9999, 4 );
					  
					function add_price_suffixtwob( $html, $product, $price, $qty ){
						// here we account for the situation where rules are set to excl shop incl cart
						if (get_option('woocommerce_tax_display_shop') === 'excl'){
							$html = '<small class="woocommerce-price-suffix"> '.apply_filters('b2bking_price_suffix_ex_vat', esc_html__('ex. VAT', 'b2bking')).'</small>';
						} else {
					    	$html = '<small class="woocommerce-price-suffix"> '.apply_filters('b2bking_price_suffix_inc_vat', esc_html__('inc. VAT', 'b2bking')).'</small>';
						}
					    return $html;
					}
				}
			}
		}

	}

	public static function b2bking_dynamic_rule_tax_exemption_prices_excl_tax_in_shop ($value){

	    $user_id = get_current_user_id();
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
	    global $woocommerce;
	    $customer = $woocommerce->customer;

	    if (is_a($customer, 'WC_Customer')){
	    	$tax_setting = get_option('woocommerce_tax_based_on');
	    	if ($tax_setting === 'shipping'){
	    		$user_country = WC()->customer->get_shipping_country();
	    	} else {
	    		$user_country = WC()->customer->get_billing_country();
	    	}

	    	$user_vat = get_user_meta($user_id, 'b2bking_user_vat_status', true);

	    	$array_countries_and_requires = array(
	    						'relation' => 'AND',
	    						array(
	    							'key' => 'b2bking_rule_countries',
	    							'value' => $user_country,
	    							'compare'=> 'LIKE'
	    						),
	    						array(
	    							'relation' => 'OR',
	    							array(
	    								'key' => 'b2bking_rule_requires',
	    								'value' => 'nothing',
	    							),
	    							array(
	    								'key' => 'b2bking_rule_requires',
	    								'value' => $user_vat // should be 'validated_vat'
	    							),
	    						),
	    					);

	    	// Get all dynamic rule tax exemptions that apply to the user or user's group
	    	$tax_exemption_user_ids = get_option('b2bking_have_tax_exemption_user_rules_list_ids', '');
	    	if (!empty($tax_exemption_user_ids)){
	    		$tax_exemption_user_ids = explode(',',$tax_exemption_user_ids);
	    	} else {
	    		$tax_exemption_user_ids = array();
	    	}

	    //	$tax_exemption_rules = get_transient('b2bking_tax_exemption_user_'.get_current_user_id());
	    	$tax_exemption_rules = b2bking()->get_global_data('b2bking_tax_exemption_user',false,get_current_user_id());
	    	if (!$tax_exemption_rules || intval(get_current_user_id()) === 0){

	    		$tax_exemption_rules = get_posts([
	    			'post_type' => 'b2bking_rule',
	    			'post_status' => 'publish',
	    			'post__in' => $tax_exemption_user_ids,
	    			'fields'        => 'ids', // Only get post IDs
	    			'numberposts' => -1,
	    			'meta_query'=> array(
	    				$array_who,
	    				$array_countries_and_requires,
	    			)
	    		]);

	    		//set_transient ('b2bking_tax_exemption_user_'.get_current_user_id(), $tax_exemption_rules);
	    		b2bking()->set_global_data('b2bking_tax_exemption_user', $tax_exemption_rules, false, get_current_user_id());
	    	}
	    		
	    	// if there are tax exemption rules, set user as tax exempt
	    	if (!empty($tax_exemption_rules)){
	    		// check if there is any rule that displays VAT as fee
	    		$display_vat_as_fee = 'no';
	    		foreach ($tax_exemption_rules as $rule){
	    			$vat_display = get_post_meta($rule, 'b2bking_rule_showtax', true);

	    			$shipping_included = get_post_meta($rule, 'b2bking_rule_tax_shipping', true);
	    			$shipping_included_rate = get_post_meta($rule, 'b2bking_rule_tax_shipping_rate', true);

	    			if ($vat_display === 'yes'){
	    				$display_vat_as_fee = 'yes';
	    				break;
	    			}
	    		}
	    		if ($display_vat_as_fee === 'yes'){
	    			return 'excl';
	    		}
	    	} else {
	    		// do nothing
	    	}
	    }

	    return $value;
	}


	public static function b2bking_dynamic_rule_tax_exemption_fees_display_only( ){
		$user_id = get_current_user_id();
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
		global $woocommerce;
		$customer = $woocommerce->customer;

		if (is_a($customer, 'WC_Customer')){
			$tax_setting = get_option('woocommerce_tax_based_on');
			if ($tax_setting === 'shipping'){
				$user_country = WC()->customer->get_shipping_country();
			} else {
				$user_country = WC()->customer->get_billing_country();
			}

		
			$user_vat = get_user_meta($user_id, 'b2bking_user_vat_status', true);


			$array_countries_and_requires = array(
								'relation' => 'AND',
								array(
									'key' => 'b2bking_rule_countries',
									'value' => $user_country,
									'compare'=> 'LIKE'
								),
								array(
									'relation' => 'OR',
									array(
										'key' => 'b2bking_rule_requires',
										'value' => 'nothing',
									),
									array(
										'key' => 'b2bking_rule_requires',
										'value' => $user_vat // should be 'validated_vat'
									),
								),
							);

			// Get all dynamic rule tax exemptions that apply to the user or user's group
			$tax_exemption_user_ids = get_option('b2bking_have_tax_exemption_user_rules_list_ids', '');
			if (!empty($tax_exemption_user_ids)){
				$tax_exemption_user_ids = explode(',',$tax_exemption_user_ids);
			} else {
				$tax_exemption_user_ids = array();
			}

		//	$tax_exemption_rules = get_transient('b2bking_tax_exemption_user_'.get_current_user_id());
			$tax_exemption_rules = b2bking()->get_global_data('b2bking_tax_exemption_user',false,get_current_user_id());
			if (!$tax_exemption_rules || intval(get_current_user_id()) === 0){

				$tax_exemption_rules = get_posts([
					'post_type' => 'b2bking_rule',
					'post_status' => 'publish',
					'post__in' => $tax_exemption_user_ids,
					'fields'        => 'ids', // Only get post IDs
					'numberposts' => -1,
					'meta_query'=> array(
						'relation' => 'AND',
						$array_who,
						$array_countries_and_requires,
					)
				]);

				//set_transient ('b2bking_tax_exemption_user_'.get_current_user_id(), $tax_exemption_rules);
				b2bking()->set_global_data('b2bking_tax_exemption_user', $tax_exemption_rules, false, get_current_user_id());

			}
				
			// if there are tax exemption rules, set user as tax exempt
			if (!empty($tax_exemption_rules)){
				// check if there is any rule that displays VAT as fee
				$display_vat_as_info = 'no';
				foreach ($tax_exemption_rules as $rule){
					$vat_display = get_post_meta($rule, 'b2bking_rule_showtax', true);

					$shipping_included = get_post_meta($rule, 'b2bking_rule_tax_shipping', true);
					$shipping_included_rate = get_post_meta($rule, 'b2bking_rule_tax_shipping_rate', true);

					if ($vat_display === 'display_only'){
						$display_vat_as_info = 'yes';
						break;
					}
				}
				if ($display_vat_as_info === 'yes'){

					// calculate VAT
					$tax_vat = 0;
					$tax_name = 'VAT';
					$cart = WC()->cart;
					if(is_object($cart)) {
						foreach($cart->get_cart() as $item){
						//Get product by supplying variation id or product_id
						$product = wc_get_product( $item['variation_id'] ? $item['variation_id'] : $item['product_id'] );
						if ($product->is_taxable()){
							$tax_rates = WC_Tax::get_rates( $product->get_tax_class() );
							if (!empty($tax_rates)) {
								$tax_rate = reset($tax_rates);

								$productprice = wc_get_price_excluding_tax($product);
								// if product is an offer, get offer price
								$offer_id_prod = get_option('b2bking_offer_product_id_setting', 0);
								$offer_id_prod = apply_filters('b2bking_get_offer_product_id', $offer_id_prod, $product->get_id(), 'tax_exemption');
								if ($product->get_id() === intval($offer_id_prod) || $product->get_id() === 3225464){
									$productprice = $item['line_subtotal']/$item['quantity'];
								}

								// if there is a discounted price
								$productpricedynamic = B2bking_Dynamic_Rules::b2bking_dynamic_rule_discount_sale_price($productprice, $product );
								if ($productprice !== $productpricedynamic){
									$productprice = wc_get_price_excluding_tax($product, array('qty' => 1, 'price' => $product->get_sale_price() ));
								}

								$tax_vat += ($productprice * $tax_rate['rate'] * $item['quantity'])/100;
								$tax_name = $tax_rate['label'];
							}
						}
						}
					}

					// if shipping included add shipping
					if (isset($shipping_included)){
						if ($shipping_included === 'yes'){
							$cart = WC()->cart;
							$shipping_cost = $cart->get_shipping_total();

							$tax_vat += ($shipping_cost*$shipping_included_rate/100);

						} else {
							// do nothing
						}
					}
					if ($tax_vat !== 0 && $tax_vat !== NULL){
						echo ' <tr class="b2bking-cart-withholding-tax">
									<th>' . apply_filters('b2bking_withholding_tax_text', esc_html__( "Withholding Tax (not paid)", "b2bking" ) ) . '</th>
									<td data-title="'.esc_html__( "Withholding Tax (not paid)", "b2bking" ).'">' . wc_price($tax_vat) . '</td>
								</tr>';  
					}

				}
			} else {
				// do nothing
			}
		}
	}

	public static function b2bking_dynamic_rule_currency_symbol($symbol, $currency){
			$user_id = get_current_user_id();

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


				// Get all dynamic rule tax exemptions that apply to the user or user's group
				$currency_rules_ids = get_option('b2bking_have_currency_rules_list_ids', '');
				if (!empty($currency_rules_ids)){
					$currency_rules_ids = explode(',',$currency_rules_ids);
				} else {
					$currency_rules_ids = array();
				}
				
			//	$currency_rules = get_transient('b2bking_currency_user_'.get_current_user_id());
				$currency_rules = b2bking()->get_global_data('b2bking_currency_user',false,get_current_user_id());
				if (!$currency_rules){

					$currency_rules = get_posts([
						'post_type' => 'b2bking_rule',
						'post_status' => 'publish',
						'post__in' => $currency_rules_ids,
						'fields'        => 'ids', // Only get post IDs
						'numberposts' => -1,
						'meta_query'=> array(
							$array_who,
						)
					]);
					//set_transient ('b2bking_currency_user_'.get_current_user_id(), $currency_rules);
					b2bking()->set_global_data('b2bking_currency_user', $currency_rules, false, get_current_user_id());

				}
				
			
			// if there are currency symbol rules
			if (!empty($currency_rules)){
				$symbol_letters = get_post_meta($currency_rules[0], 'b2bking_rule_currency', true);
				$symbols = get_woocommerce_currency_symbols();
				return $symbols[$symbol_letters];
			} else {
				return $currency;
			}
	}

	public static function b2bking_dynamic_rule_currency($currency){
			$user_id = get_current_user_id();

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


				// Get all dynamic rule tax exemptions that apply to the user or user's group
				$currency_rules_ids = get_option('b2bking_have_currency_rules_list_ids', '');
				if (!empty($currency_rules_ids)){
					$currency_rules_ids = explode(',',$currency_rules_ids);
				} else {
					$currency_rules_ids = array();
				}
				
			//	$currency_rules = get_transient('b2bking_currency_user_'.get_current_user_id());
				$currency_rules = b2bking()->get_global_data('b2bking_currency_user',false,get_current_user_id());
				if (!$currency_rules){

					$currency_rules = get_posts([
						'post_type' => 'b2bking_rule',
						'post_status' => 'publish',
						'post__in' => $currency_rules_ids,
						'fields'        => 'ids', // Only get post IDs
						'numberposts' => -1,
						'meta_query'=> array(
							$array_who,
						)
					]);
					//set_transient ('b2bking_currency_user_'.get_current_user_id(), $currency_rules);
					b2bking()->set_global_data('b2bking_currency_user', $currency_rules, false, get_current_user_id());

				}
				
			
			// if there are currency symbol rules
			if (!empty($currency_rules)){
				$symbol_letters = get_post_meta($currency_rules[0], 'b2bking_rule_currency', true);
				return $symbol_letters;
			} else {
				return $currency;
			}
	}
	
}