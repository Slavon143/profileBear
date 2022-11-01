<?php 
$wf_filters_help_doc_cat = array(
	'order_details' => __('Order Details','print-invoices-packing-slip-labels-for-woocommerce'),
	'product_table' => __('Product Table','print-invoices-packing-slip-labels-for-woocommerce'),
	'summary_table' => __('Summary Table','print-invoices-packing-slip-labels-for-woocommerce'),
	'others' => __('Others','print-invoices-packing-slip-labels-for-woocommerce'),
);
$wf_filters_help_doc_lists=array(
	'order_details' => array(
		'wf_pklist_alter_order_date'=> array(
			'title'=>__('Alter order date','print-invoices-packing-slip-labels-for-woocommerce'),
			'description' => __("This filter is used to alter the order date in all the document templates","print-invoices-packing-slip-labels-for-woocommerce"),
			'params'=>'$order_date, $template_type, $order',
			'function_name'=>'wt_pklist_change_order_date_format',
			'function_code'=>'
				/* new date format */ <br />
				return <i class={inbuilt_fn}>date</i>("Y-m-d",strtotime(<span class={prms_css}>$order_date</span>)); <br />
			',
			'function_code_copy' => '
				/* new date format */ <br />
				return date("Y-m-d",strtotime($order_date)); <br />
			',
		),
		'wf_pklist_alter_invoice_date'=> array(
			'title'=>__('Alter invoice date','print-invoices-packing-slip-labels-for-woocommerce'),
			'description' => __("This filter is used to alter the invoice date in all the document templates","print-invoices-packing-slip-labels-for-woocommerce"),
			'params'=>'$invoice_date, $template_type, $order',
			'function_name'=>'wt_pklist_change_invoice_date_format',
			'function_code'=>'
				/* new date format */ <br />
				return <i class={inbuilt_fn}>date</i>("M d Y",strtotime(<span class={prms_css}>$invoice_date</span>)); <br />
			',
			'function_code_copy' => '
				/* new date format */ <br />
				return date("M d Y",strtotime($invoice_date)); <br />
			',
		),
		'wf_pklist_alter_dispatch_date'=> array(
			'title' => __('Alter dispatch date','print-invoices-packing-slip-labels-for-woocommerce'),
			'description'=> __('Alter dispatch date','print-invoices-packing-slip-labels-for-woocommerce'),
			'params'=>'$dispatch_date, $template_type, $order',
			'function_name'=>'wt_pklist_change_dispatch_date_format',
			'function_code'=>'
				/* new date format */ <br />
				return <i class={inbuilt_fn}>date</i>("d - M - y",strtotime(<span class={prms_css}>$dispatch_date</span>)); <br />
			',
			'function_code_copy'=>'
				/* new date format */ <br />
				return date("d - M - y",strtotime($dispatch_date)); <br />
			',
		),
		'wf_pklist_alter_barcode_data'=> array(
			'title' => __('Alter barcode information','print-invoices-packing-slip-labels-for-woocommerce'),
			'description'=>__('This filter is used to alter the barcode information for all the document templates','print-invoices-packing-slip-labels-for-woocommerce'),
			'params'=>'$invoice_number, $template_type, $order',
			'function_name'=>'wt_pklist_order_number_in_barcode',
			'function_code'=>'
				/* order number in barcode */ <br />
				return $order->get_order_number();<br />
			',
		),
		'wf_pklist_alter_shipping_address'=> array(
			'title' => __('Alter shipping address','print-invoices-packing-slip-labels-for-woocommerce'),
			'description'=> __('Alter shipping address','print-invoices-packing-slip-labels-for-woocommerce'),
			'params'=>'$shipping_address, $template_type, $order',
			'function_name'=>'wt_pklist_alter_shipping_addr',
			'function_code'=>'
				/* To unset existing field */ <br />
				if(!empty($shipping_address[\'field_name\']))<br/>
				{<br/>
					unset($shipping_address[\'field_name\']);<br/>
				} <br /><br />

				/* add a new field shipping address */ <br />
				$shipping_address[\'new_field\']=\'new field value\';<br /><br />
				return $shipping_address;<br />',
		),
		'wf_pklist_alter_billing_address'=> array(
			'title'=> __('Alter billing address','print-invoices-packing-slip-labels-for-woocommerce'),
			'description'=> __('Alter billing address','print-invoices-packing-slip-labels-for-woocommerce'),
			'params'=>'$billing_address, $template_type, $order',
			'function_name'=>'wt_pklist_alter_billing_addr',
			'function_code'=>'
				/* To unset existing field */ <br />
				if(!empty($billing_address[\'field_name\']))<br/>
				{<br/>
					unset($billing_address[\'field_name\']);<br/>
				} <br /><br />

				/* add a new field billing address */ <br />
				$billing_address[\'new_field\']=\'new field value\';<br /><br />
				return $billing_address;<br />'
		),
		'wf_pklist_alter_shipping_from_address'=> array(
			'title' => __('Alter shipping from address','print-invoices-packing-slip-labels-for-woocommerce'),
			'description'=> __('Alter shipping from address','print-invoices-packing-slip-labels-for-woocommerce'),
			'params'=>'$fromaddress, $template_type, $order',
			'function_name'=>'wt_pklist_alter_from_addr',
			'function_code'=>'
				/* To unset existing field */ <br />
				if(!empty($fromaddress[\'field_name\']))<br/>
				{<br/>
					unset($fromaddress[\'field_name\']);<br/>
				} <br /><br />

				/* add a new field from address */ <br />
				$fromaddress[\'new_field\']=\'new field value\';<br /><br />
				return $fromaddress;<br />'
		),
		'wf_pklist_add_additional_info'=> array(
			'title' => __('Add additional information','print-invoices-packing-slip-labels-for-woocommerce'),
			'description'=> __('Add additional info','print-invoices-packing-slip-labels-for-woocommerce'),
			'params'=>'$additional_info, $template_type, $order',
			'function_name'=>'wt_pklist_add_additional_data',
			'function_code'=>'
				$additional_info.=\'Additional text\';<br />
				return $additional_info;<br />
			',
		),
	),

	'product_table' => array(
		'wf_pklist_alter_product_table_head'=> array(
			'title' => __('Alter product table head.(Add,remove, change the order)','print-invoices-packing-slip-labels-for-woocommerce'),
			'description'=> __('Alter product table head.(Add, remove, change order)','print-invoices-packing-slip-labels-for-woocommerce'),
			'params'=>'$columns_list_arr, $template_type, $order',
			'function_name'=>'wt_pklist_alter_product_columns',
			'function_code'=>'
				/* removing image column */ <br />
				unset($columns_list_arr[\'image\']); <br /><br />

				/* adding a new custom column with text align right */ <br />
				$columns_list_arr[\'new_col\']=\'&lt;th class=&quot;wfte_product_table_head_new_col wfte_text_right&quot; col-type=&quot;new_col&quot;&gt;__[New column]__&lt;/th&gt;\'; <br />
				<br />

				return $columns_list_arr;<br />
			',
		),
		'wf_pklist_alter_package_product_name'=> array(
			'title' => __('Alter the product name in package documents','print-invoices-packing-slip-labels-for-woocommerce'),
			'description'=> __('Alter product name in product (Works with Packing List, Shipping Label and Delivery note only)','print-invoices-packing-slip-labels-for-woocommerce'),
			'params'=>'$item_name, $template_type, $_product, $item, $order',
			'function_name'=>'wt_pklist_alter_product_name_package_doc',
			'function_code'=>'
				/* custom code here */<br />
				return $item_name;<br />
			',
		),
		'wf_pklist_add_package_product_variation'=> array(
			'title'=> __('Add product variation in package documents','print-invoices-packing-slip-labels-for-woocommerce'),
			'description'=> __('Add product variation in product (Works with Packing List, Shipping Label and Delivery note only)','print-invoices-packing-slip-labels-for-woocommerce'),
			'params'=>'$item_meta, $template_type, $_product, $item, $order',
			'function_name'=>'wt_pklist_add_meta_in_package_doc',
			'function_code' => '
				/* custom code here to add product variation */<br />
			',
		),
		'wf_pklist_alter_package_item_quantiy'=> array(
			'title' => __('Alter item quantity in package documents','print-invoices-packing-slip-labels-for-woocommerce'),
			'description'=> __('Alter item quantity in product table (Works with Packing List, Shipping Label and Delivery note only)','print-invoices-packing-slip-labels-for-woocommerce'),
			'params'=>'$item_quantity, $template_type, $_product, $item, $order',
			'function_name'=>'wt_pklist_package_item_quantiy',
			'function_code'=>'
				$item_quantity=\'New quantity\';<br />
				return $item_quantity;<br />',
		),
		'wf_pklist_alter_package_item_total'=> array(
			'title' => __('Alter item total in package documents','print-invoices-packing-slip-labels-for-woocommerce'),
			'description'=> __('Alter item total in product table (Works with Packing List, Shipping Label and Delivery note only)','print-invoices-packing-slip-labels-for-woocommerce'),
			'params'=>'$product_total, $template_type, $_product, $item, $order',
			'function_name'=>'wt_pklist_package_item_total',
			'function_code'=>'
				$product_total=\'New total price\';<br />
				return $product_total;<br />',
		),
		'wf_pklist_package_product_table_additional_column_val'=> array(
			'title' => __('Add additional column in product table of package documents','print-invoices-packing-slip-labels-for-woocommerce'),
			'description'=> __('You can add additional column head via `wf_pklist_alter_product_table_head` filter. You need to add column data via this filter. (Works with Packing List, Shipping Label and Delivery note only)','print-invoices-packing-slip-labels-for-woocommerce'),
			'params'=>'$column_data, $template_type, $columns_key, $_product, $item, $order',
			'function_name'=>'wt_pklist_package_add_custom_col_vl',
			'function_code'=>'				
				if($columns_key==\'new_col\')<br />
				{ <br />
					&nbsp;&nbsp;&nbsp;&nbsp; $column_data=\'Column data\'; <br />
				}<br />
				return $column_data;<br />
			',
		),
		'wf_pklist_alter_package_product_table_columns'=> array(
			'title' => __('Alter product table column in package documents','print-invoices-packing-slip-labels-for-woocommerce'),
			'description'=> __('Alter product table column. (Works with Packing List, Shipping Label and Delivery note only)','print-invoices-packing-slip-labels-for-woocommerce'),
			'params'=>'$product_row_columns, $template_type, $_product, $item, $order',
			'function_name' => 'wt_pklist_alter_product_columns_in_package',
			'function_code' => '
				/* custom code here */<br />
				return $product_row_columns;<br />
			',
		),
		'wf_pklist_alter_product_name'=> array(
			'title' => __('Alter product name','print-invoices-packing-slip-labels-for-woocommerce'),
			'description'=> __('Alter product name. (Works with Invoice and Dispatch label only)','print-invoices-packing-slip-labels-for-woocommerce'),
			'params'=>'$order_item_name, $template_type, $_product, $order_item, $order',
			'function_name'=>'wt_pklist_new_prodct_name',
			'function_code' => '
				/* custom code here */ <br />
				return $order_item_name, <br />
			',
		),
		'wf_pklist_add_product_variation'=> array(
			'title' => __('Add product variation','print-invoices-packing-slip-labels-for-woocommerce'),
			'description'=> __('Add product variation. (Works with Invoice and Dispatch label only)','print-invoices-packing-slip-labels-for-woocommerce'),
			'params'=>'$item_meta, $template_type, $_product, $order_item, $order',
			'function_name'=>'wt_pklist_prodct_varition',
			'function_code'=>'
				/* custom code here */
				return $item_meta; <br />
			',
		),
		'wf_pklist_alter_item_quantiy'=> array(
			'title' => __('Alter item quantity','print-invoices-packing-slip-labels-for-woocommerce'),
			'description'=> __('Alter item quantity. (Works with Invoice and Dispatch label only)','print-invoices-packing-slip-labels-for-woocommerce'),
			'params'=>'$order_item_qty, $template_type, $_product, $order_item, $order',
			'function_name'=>'wt_pklist_item_qty',
			'function_code'=>'
				$order_item_qty=\'New item quantity\';<br />
				return $order_item_qty;<br />',
		),
		'wf_pklist_alter_item_price'=> array(
			'title' => __('Alter item price','print-invoices-packing-slip-labels-for-woocommerce'),
			'description'=> __('Alter item price. (Works with Invoice and Dispatch label only)','print-invoices-packing-slip-labels-for-woocommerce'),
			'params'=>'$item_price, $template_type, $_product, $order_item, $order',
			'function_name'=>'wt_pklist_item_price',
			'function_code'=>'
				$item_price=\'New item price\';<br />
				return $item_price;<br />',
		),
		'wf_pklist_alter_item_price_formated'=> array(
			'title' => __('Alter item price format','print-invoices-packing-slip-labels-for-woocommerce'),
			'description'=> __('Alter formated item price. (Works with Invoice and Dispatch label only)','print-invoices-packing-slip-labels-for-woocommerce'),
			'params'=>'$item_price_formated, $template_type, $item_price, $_product, $order_item, $order',
			'function_name'=>'wt_pklist_item_price_formatted',
			'function_code'=>'
				$item_price_formated=\'New item formatted price\';<br />
				return $item_price_formated;<br />',
		),
		'wf_pklist_alter_item_total'=> array(
			'title' => __('Alter item total','print-invoices-packing-slip-labels-for-woocommerce'),
			'description'=> __('Alter item total. (Works with Invoice and Dispatch label only)','print-invoices-packing-slip-labels-for-woocommerce'),
			'params'=>'$product_total, $template_type, $_product, $order_item, $order',
			'function_name'=>'wt_pklist_item_total',
			'function_code'=>'
				$product_total=\'New product total\';<br />
				return $product_total;<br />'
		),
		'wf_pklist_alter_item_total_formated'=> array(
			'title' => __('Alter item total format','print-invoices-packing-slip-labels-for-woocommerce'),
			'description'=> __('Alter formated item total. (Works with Invoice and Dispatch label only)','print-invoices-packing-slip-labels-for-woocommerce'),
			'params'=>'$product_total_formated, $template_type, $product_total, $_product, $order_item, $order',
			'function_name'=>'wt_pklist_item_total_formatted',
			'function_code'=>'
				$product_total_formated=\'New product total formatted\';<br />
				return $product_total_formated;<br />'
		),
		'wf_pklist_product_table_additional_column_val'=> array(
			'title' => __('Add additional column in product table','print-invoices-packing-slip-labels-for-woocommerce'),
			'description'=> __('You can add additional column head via `wf_pklist_alter_product_table_head` filter. You need to add column data via this filter. (Works with Invoice and Dispatch label only)','print-invoices-packing-slip-labels-for-woocommerce'),
			'params'=>'$column_data, $template_type, $columns_key, $_product, $order_item, $order',
			'function_name'=>'wt_pklist_add_custom_col_vl',
			'function_code'=>'				
				if($columns_key==\'new_col\')<br />
				{ <br />
					&nbsp;&nbsp;&nbsp;&nbsp; $column_data=\'Column data\'; <br />
				}<br />
				return $column_data;<br />
			',
		),
		'wf_pklist_alter_product_table_columns'=> array(
			'title' => __('Alter product table column','print-invoices-packing-slip-labels-for-woocommerce'),
			'description'=> __('Alter product table column. (Works with Invoice and Dispatch label only)','print-invoices-packing-slip-labels-for-woocommerce'),
			'params'=>'$product_row_columns, $template_type, $_product, $order_item, $order',
			'function_name' => 'wt_pklist_alter_product_columns',
			'function_code' => '
				/* custom code here */<br />
				return $product_row_columns;<br />
			',
		),
	),
	'summary_table' => array(
		'wf_pklist_alter_subtotal'=> array(
			'title' => __('Alter subtotal','print-invoices-packing-slip-labels-for-woocommerce'),
			'description'=> __('Alter subtotal','print-invoices-packing-slip-labels-for-woocommerce'),
			'params'=>'$sub_total, $template_type, $order',
			'function_name'=>'wt_pklist_alter_sub',
			'function_code'=>'
				$sub_total=\'New subtotal\';<br />
				return $sub_total;<br />
			'
		),
		'wf_pklist_alter_subtotal_formated'=> array(
			'title' => __('Alter subtotal format','print-invoices-packing-slip-labels-for-woocommerce'),
			'description'=> __('Alter formated subtotal','print-invoices-packing-slip-labels-for-woocommerce'),
			'params'=>'$sub_total_formated, $template_type, $sub_total, $order',
			'function_name'=>'wt_pklist_alter_formated_sub',
			'function_code'=>'
				$sub_total_formated=\'New formatted subtotal\';<br />
				return $sub_total_formated;<br />
			'
		),
		'wf_pklist_alter_shipping_method'=> array(
			'title' => __('Alter shipping method','print-invoices-packing-slip-labels-for-woocommerce'),
			'description'=> __('Alter shipping method','print-invoices-packing-slip-labels-for-woocommerce'),
			'params'=>'$shipping, $template_type, $order',
			'function_name'=>'wt_pklist_alter_ship_method',
			'function_code'=>'
				$shipping=\'New shipping method\';<br />
				return $shipping;<br />'
		),
		'wf_pklist_alter_fee'=> array(
			'title' => __('Alter fee','print-invoices-packing-slip-labels-for-woocommerce'),
			'description'=> __('Alter fee','print-invoices-packing-slip-labels-for-woocommerce'),
			'params'=>'$fee_detail_html, $template_type, $fee_detail, $user_currency, $order',
			'function_name'=>'wt_pklist_new_fee',
			'function_code'=>'
				$fee_detail_html=\'New Fee\';<br />
				return $fee_detail_html;<br />'
		),
		'wf_pklist_alter_total_fee'=> array(
			'title' => __('Alter total fee','print-invoices-packing-slip-labels-for-woocommerce'),
			'description'=> __('Alter total fee','print-invoices-packing-slip-labels-for-woocommerce'),
			'params'=>'$fee_total_amount_formated, $template_type, $fee_total_amount, $user_currency, $order',
			'function_name'=>'wt_pklist_new_formated_fee',
			'function_code'=>'
				$fee_total_amount_formated=\'New Formated Fee\';<br />
				return $fee_total_amount_formated;<br />'
		),
		'wf_pklist_alter_total_price'=> array(
			'title' => __('Alter total price','print-invoices-packing-slip-labels-for-woocommerce'),
			'description'=> __('Alter total price','print-invoices-packing-slip-labels-for-woocommerce'),
			'params'=>'$total_price, $template_type, $order',
			'function_name'=>'wt_pklist_alter_total_price',
			'function_code'=>'
				$total_price=\'New Price\';<br />
				return $total_price;<br />
			',
		),
		'wf_pklist_alter_total_price_in_words'=> array(
			'title' => __('Alter total price in words','print-invoices-packing-slip-labels-for-woocommerce'),
			'description'=> __('Alter total price in words','print-invoices-packing-slip-labels-for-woocommerce'),
			'params'=>'$total_in_words, $template_type, $order',
			'function_name'=>'wt_pklist_alter_total_price_in_words',
			'function_code'=>'
				$total_in_words=\'Price in words: \'.$total_in_words;<br />
				return $total_in_words;<br />
			',
		),
	),
	'others'=>array(
		'wf_pklist_alter_meta_value'=> array(
			'title' => __('Alter meta data','print-invoices-packing-slip-labels-for-woocommerce'),
			'description'=> __('Alter meta data.','print-invoices-packing-slip-labels-for-woocommerce'),
			'params'=>'$meta_value, $meta_data, $meta_key',
			'function_name'=>'wt_pklist_alter_meta',
			'function_code'=>'
				$meta_value=\'Enter the meta value\';<br />
				return $meta_value;<br />',
		),
		'wf_pklist_order_additional_item_meta'=> array(
			'title'=> __('Alter additional item meta','print-invoices-packing-slip-labels-for-woocommerce'),
			'description'=> __('Alter additional item meta','print-invoices-packing-slip-labels-for-woocommerce'),
			'params'=>'$order_item_meta_data, $template_type, $order',
			'function_name'=>'wf_pklist_add_order_meta',
			'function_code'=>'			
				/* get post meta */<br/>
				$order_id = $order->get_id(); <br/>
				$meta=get_post_meta($order_id, \'_meta_key\', true);<br/>
				$order_item_meta_data=$meta;<br />
				return $order_item_meta_data;<br />'
		),
		'wf_pklist_toggle_received_seal'=> array(
			'title' => __('Hide/Show received seal in invoice','print-invoices-packing-slip-labels-for-woocommerce'),
			'description'=> __('Hide/Show received seal in invoice.','print-invoices-packing-slip-labels-for-woocommerce'),
			'params'=>'$is_enable_received_seal, $template_type, $order',
			'function_name'=>'wt_pklist_toggle_received_seal',
			'function_code'=>'
				/* hide or show received seal */  <br />
				if($order->get_status()==\'refunded\')<br />
				{ <br />
				&nbsp;&nbsp;&nbsp;&nbsp; return false;  <br />
				}<br />
				return true; <br />
			',
		),
		'wf_pklist_received_seal_extra_text'=> array(
			'title'=> __('Add extra text in received seal','print-invoices-packing-slip-labels-for-woocommerce'),
			'description'=> __('Add extra text in received seal.','print-invoices-packing-slip-labels-for-woocommerce'),
			'params'=>'$extra_text, $template_type, $order',
			'function_name'=>'wt_pklist_received_seal_extra_text',
			'function_code'=>'
				/* add invoice date in received seal */  <br />
				$order_id=$order->get_id();  <br />
				$invoice_date=get_post_meta($order_id, \'_wf_invoice_date\', true);  <br />
				if($invoice_date)   <br />
				{   <br />
					&nbsp;&nbsp;&nbsp;&nbsp; return \'&lt;br /&gt;\'.<i class={inbuilt_fn}>date</i>(\'Y-m-d\',$invoice_date);  <br />
				} <br />
				return \'\'; <br />
			',
		),
		'wf_pklist_alter_template_html'=> array(
			'title' => __('Alter template HTML before printing','print-invoices-packing-slip-labels-for-woocommerce'),
			'description'=> __('Alter template HTML before printing.','print-invoices-packing-slip-labels-for-woocommerce'),
			'params'=>'$html, $template_type',
			'function_name'=>'wt_pklist_add_custom_css_in_invoice_html',
			'function_code'=>'
				/* add cutsom css in invoice */  <br />
				if($template_type==\'invoice\')<br />
				{ <br />
					&nbsp;&nbsp;&nbsp;&nbsp; $html.=\'&lt;style type=&quot;text/css&quot;&gt; body{ font-weight:bold; } &lt;/style&gt;\'; <br />
				}<br />
				return $html;<br />
			',
		),
		'wf_alter_line_item_variation_data'=> array(
			'description'=> __('Alter the variation data.','print-invoices-packing-slip-labels-for-woocommerce'),
			'params'=>' $current_item, $meta_data, $id, $value'
		),
		'wf_pklist_alter_settings'=> array(
			'description'=> __('Alter the settings array','print-invoices-packing-slip-labels-for-woocommerce'),
			'params'=>'$settings,$base_id',
			'function_name'=>'wt_pklist_alter_setting',
			'function_code'=>'
				
				/* To remove a setting from the list */<br/>
				unset($settings[\'setting_name\']);<br/><br/>
				
				/* add new setting to the list */<br/>
				$settings[\'new_setting_name\']=\'new default value\';<br/><br/>			

				return $settings;<br />',
		),
		'wf_pklist_alter_footer_data'=> array(
			'title' => __('Alter the footer data','print-invoices-packing-slip-labels-for-woocommerce'),
			'description'=> __('Alter the footer data.','print-invoices-packing-slip-labels-for-woocommerce'),
			'params'=>'$footer_data,$template_type,$order',
			'function_name'=>'wt_pklist_alter_footer',
			'function_code'=>'
					$footer_data=\'Footer name\';<br />
					return $footer_data;<br />',
		),
	),
);
?>