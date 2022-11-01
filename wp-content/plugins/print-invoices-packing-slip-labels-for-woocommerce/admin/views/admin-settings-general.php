<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}
$wf_admin_img_path=WF_PKLIST_PLUGIN_URL . 'admin/images/';
?>
<div class="wf-tab-content" data-id="<?php echo $target_id;?>">
	<p><?php _e("The company name and the address details from the sections below will be used as the 'Shipping from' address in the invoice and other related documents.",'print-invoices-packing-slip-labels-for-woocommerce'); ?></p>
	<h3 style="margin-bottom:0px; padding-bottom:5px; border-bottom:dashed 1px #ccc;"><?php _e('Company Info', 'print-invoices-packing-slip-labels-for-woocommerce'); ?></h3>
	<table class="form-table wf-form-table">
	    <?php
		self::generate_form_field(array(
			array(
			'label'=>__("Name",'print-invoices-packing-slip-labels-for-woocommerce'),
			'option_name'=>"woocommerce_wf_packinglist_companyname",
			),
			array(
				'type'=>"uploader",
				'label'=>__("Logo",'print-invoices-packing-slip-labels-for-woocommerce'),
				'option_name'=>"woocommerce_wf_packinglist_logo",
				'help_text'=>__("Ensure to select company logo from ‘Invoice > Customize > Company Logo’ to reflect on the invoice. Recommended size is 150x50px.", 'print-invoices-packing-slip-labels-for-woocommerce'),
			),
			array(
				'label'=>__("Company Tax ID",'print-invoices-packing-slip-labels-for-woocommerce'), 
				'option_name'=>"woocommerce_wf_packinglist_sender_vat",
				'help_text'=>__("Specify your company tax ID. For e.g. you may enter as VAT: GB123456789, GSTIN:0948745 or ABN:51 824 753 556", 'print-invoices-packing-slip-labels-for-woocommerce'),
			),
			array(
				'type'=>'textarea',
				'label'=>__("Footer",'print-invoices-packing-slip-labels-for-woocommerce'),
				'option_name'=>"woocommerce_wf_packinglist_footer",
				'help_text'=>__("Set up a footer which will be used across the respective documents.",'print-invoices-packing-slip-labels-for-woocommerce'),
			),
		));
		?>
	</table>

	<h3 style="margin-bottom:0px; padding-bottom:5px; border-bottom:dashed 1px #ccc;">
		<?php _e('Address(Sender details)', 'print-invoices-packing-slip-labels-for-woocommerce'); ?> <?php echo Wf_Woocommerce_Packing_List_Admin::set_tooltip('address_details'); ?>
		<?php $tooltip_conf=Wf_Woocommerce_Packing_List_Admin::get_tooltip_configs('load_default_address'); ?>	
			<a class="wf_pklist_load_address_from_woo <?php echo $tooltip_conf['class'];?>" <?php echo $tooltip_conf['text'];?>>
				<span class="dashicons dashicons-admin-page"></span>
				<?php _e('Load from WooCommerce','print-invoices-packing-slip-labels-for-woocommerce');?>
			</a>
		</h3>
	<table class="form-table wf-form-table">
		<?php
		self::generate_form_field(array(
			array(
				'label'=>__("Department/Business Unit/Sender",'print-invoices-packing-slip-labels-for-woocommerce'),
				'option_name'=>"woocommerce_wf_packinglist_sender_name",
				'mandatory'=>false,
			),
			array(
				'label'=>__("Address line 1",'print-invoices-packing-slip-labels-for-woocommerce'),
				'option_name'=>"woocommerce_wf_packinglist_sender_address_line1",
				'mandatory'=>true,
			),
			array(
				'label'=>__("Address line 2",'print-invoices-packing-slip-labels-for-woocommerce'),
				'option_name'=>"woocommerce_wf_packinglist_sender_address_line2",
			),
			array(
				'label'=>__("City",'print-invoices-packing-slip-labels-for-woocommerce'),
				'option_name'=>"woocommerce_wf_packinglist_sender_city",
				'mandatory'=>true,
			)
		));
        $country_selected=Wf_Woocommerce_Packing_List::get_option('wf_country');
        if( strstr( $country_selected, ':' ))
        {
			$country_selected = explode( ':', $country_selected );
			$country         = current( $country_selected );
			$state           = end( $country_selected );                                            
		}else 
		{
			$country = $country_selected;
			$state   = '*';
		}                                 
        ?>
        <tr valign="top">
	        <th scope="row" >
	        	<label for="wf_country">
	        	<?php _e('Country/State','print-invoices-packing-slip-labels-for-woocommerce'); ?><span class="wt_pklist_required_field">*</span></label></th>
	        <td>
	        	<select name="wf_country" data-placeholder="<?php esc_attr_e( 'Choose a country&hellip;', 'print-invoices-packing-slip-labels-for-woocommerce' ); ?>" required="required">
						<option value="">--<?php _e('Select country', 'print-invoices-packing-slip-labels-for-woocommerce');?>--</option>
						<?php WC()->countries->country_dropdown_options($country,$state ); ?>
         		</select>
	        </td>
	        <td></td>
	    </tr>
	    <?php
	    self::generate_form_field(array(
			array(
				'label'=>__("Postal code",'print-invoices-packing-slip-labels-for-woocommerce'),
				'option_name'=>"woocommerce_wf_packinglist_sender_postalcode",
				'mandatory'=>true,
			),
			array(
				'label'=>__("Contact number",'print-invoices-packing-slip-labels-for-woocommerce'), 
				'option_name'=>"woocommerce_wf_packinglist_sender_contact_number",
			),
		));
	    ?>
	</table>
	<h3 style="margin-bottom:0px; padding-bottom:5px; border-bottom:dashed 1px #ccc;"><?php _e('Other settings', 'print-invoices-packing-slip-labels-for-woocommerce'); ?></h3>
	<table class="form-table wf-form-table">
	    <?php
	    $mPDF_addon_url='https://wordpress.org/plugins/mpdf-addon-for-pdf-invoices/';
	    $form_fields=array(
	    	array(
				'type'=>"checkbox",
				'label'=>__("Display state name",'print-invoices-packing-slip-labels-for-woocommerce'),
				'option_name'=>"woocommerce_wf_state_code_disable",
				'field_vl' => 'yes',
				'help_text'=>__('Enable it to display the state name instead of state code in from,return,billing and shipping addresses','print-invoices-packing-slip-labels-for-woocommerce'),
			),
			array(
				'type'=>"checkbox",
				'label'=>__("Preview before printing",'print-invoices-packing-slip-labels-for-woocommerce'),
				'option_name'=>"woocommerce_wf_packinglist_preview",
				'field_vl' => 'enabled',
			),
			array(
				'type'=>"checkbox",
				'label'=>__("Enable RTL support",'print-invoices-packing-slip-labels-for-woocommerce'),
				'option_name'=>"woocommerce_wf_add_rtl_support",
				'field_vl' => 'Yes',
				'help_text'=>sprintf(__("RTL support for documents. For better RTL integration in PDF documents please use our %s mPDF addon %s.", 'print-invoices-packing-slip-labels-for-woocommerce'), '<a href="'.$mPDF_addon_url.'" target="_blank">', '</a>'),
			)
		);

	    /**
	    *	@since 2.6.6
	    *	Add PDF library switching option if multiple libraries available
	    */
	    if(is_array($pdf_libs) && count($pdf_libs)>1)
		{
			$pdf_libs_form_arr=array();
			foreach ($pdf_libs as $key => $value)
			{
				$pdf_libs_form_arr[$key]=(isset($value['title']) ? $value['title'] : $key);
			}
			$form_fields[]=array(
				'type'=>"radio",
				'label'=>__("PDF library",'print-invoices-packing-slip-labels-for-woocommerce'),
				'option_name'=>"active_pdf_library",
				'radio_fields'=>$pdf_libs_form_arr,
				'help_text'=>__('The default library to generate PDF', 'print-invoices-packing-slip-labels-for-woocommerce'),
			);
		}

		$is_tax_enabled=wc_tax_enabled();
	    $tax_not_enabled_info='';
	    $incl_tax_img = '<br><br><img src="'.$wf_admin_img_path.'/incl_tax.png" alt="include tax img" width="100%" style="background: #f0f0f1;padding: 10px;">'; 
		$excl_tax_img = '<br><br><img src="'.$wf_admin_img_path.'/excl_tax.png" alt="include tax img" width="100%" style="background: #f0f0f1;padding: 10px;">'; 
	    if(!$is_tax_enabled)
    	{
    		$tax_not_enabled_info.='<br>';
    		$tax_not_enabled_info.=sprintf(__('%sNote:%s You have not enabled tax option in WooCommerce. If you need to apply tax for new orders you need to enable it %s here. %s', 'print-invoices-packing-slip-labels-for-woocommerce'), '<b>', '</b>', '<a href="'.admin_url('admin.php?page=wc-settings').'" target="_blank">', '</a>');
    		$incl_tax_img = ""; 
    		$excl_tax_img = "";
    	}
		$form_fields[]=array(
					'type'=>'radio',
					'label'=>__('Display price in the product table', 'print-invoices-packing-slip-labels-for-woocommerce'),
					'option_name'=>"woocommerce_wf_generate_for_taxstatus",
					'field_name'=>"woocommerce_wf_generate_for_taxstatus[]",
					'field_id'=>"woocommerce_wf_generate_for_taxstatus",
					'attr'=>($is_tax_enabled ? '' : "disabled"),
					'after_form_field'=>($is_tax_enabled ? '<a href="'.admin_url('admin.php?page=wc-settings&tab=tax').'" class="" target="_blank" style="text-align:center;">'.__('WooCommerce tax settings', 'print-invoices-packing-slip-labels-for-woocommerce').'<span class="dashicons dashicons-external"></span></a>' : ""),
					'radio_fields'=>array(
						'ex_tax'=>__('Exclude tax','print-invoices-packing-slip-labels-for-woocommerce'),
						'in_tax'=>__('Include tax','print-invoices-packing-slip-labels-for-woocommerce'),
					),
					'help_text_conditional'=>array(
		                array(
		                    'help_text'=>__('All price columns displayed will be inclusive of tax.', 'print-invoices-packing-slip-labels-for-woocommerce').$tax_not_enabled_info.$incl_tax_img,
		                    'condition'=>array(
		                        array('field'=>'woocommerce_wf_generate_for_taxstatus', 'value'=>'in_tax')
		                    )
		                ),
		                array(
		                    'help_text'=>__('All price columns displayed will be exclusive of tax.', 'print-invoices-packing-slip-labels-for-woocommerce').$tax_not_enabled_info.$excl_tax_img,
		                    'condition'=>array(
		                        array('field'=>'woocommerce_wf_generate_for_taxstatus', 'value'=>'ex_tax')
		                    )
		                )
		            ),
				);

		self::generate_form_field($form_fields);
		?>
	</table>
	<?php 
    include "admin-settings-save-button.php";
    ?>
</div>