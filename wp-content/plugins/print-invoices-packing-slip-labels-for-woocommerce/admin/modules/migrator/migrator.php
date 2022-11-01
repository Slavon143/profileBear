<?php
/**
 * Migrator, To migrate data from vesrion below 2.5.0
 *
 * @link       
 * @since 2.5.0 
 * @since 2.5.1 - Default values added for options     
 *
 * @package  Wf_Woocommerce_Packing_List  
 */
if (!defined('ABSPATH')) {
    exit;
}
class Wf_Woocommerce_Packing_List_Migrator
{
	public $module_id=WF_PKLIST_POST_TYPE.'_migrator';
	private static $option_fields=array(
		'main'=>array(
			'woocommerce_wf_packinglist_companyname'=>'woocommerce_wf_packinglist_companyname',
			'woocommerce_wf_packinglist_logo'=>'woocommerce_wf_packinglist_logo',
			'woocommerce_wf_packinglist_footer'=>'woocommerce_wf_packinglist_footer',
			'woocommerce_wf_packinglist_sender_name'=>'woocommerce_wf_packinglist_sender_name',
			'woocommerce_wf_packinglist_sender_address_line1'=>'woocommerce_wf_packinglist_sender_address_line1',
			'woocommerce_wf_packinglist_sender_address_line2'=>'woocommerce_wf_packinglist_sender_address_line2',
			'woocommerce_wf_packinglist_sender_city'=>'woocommerce_wf_packinglist_sender_city',
			'wf_country'=>'wf_country', //use store country
			'woocommerce_wf_packinglist_sender_postalcode'=>'woocommerce_wf_packinglist_sender_postalcode',
			'woocommerce_wf_add_rtl_support'=>'woocommerce_wf_packinglist_rtl_settings_enable',
		),
		'invoice'=>array(
			'woocommerce_wf_generate_for_orderstatus'=>'woocommerce_wf_generate_for_orderstatus',
        	'woocommerce_wf_packinglist_logo'=>'woocommerce_wf_packinglist_invoice_logo',
        	'woocommerce_wf_add_invoice_in_mail'=>'woocommerce_wf_add_invoice_in_mail',
        	'woocommerce_wf_packinglist_frontend_info'=>'woocommerce_wf_packinglist_frontend_info',
        	'woocommerce_wf_invoice_number_format'=>"woocommerce_wf_invoice_number_format",
			'woocommerce_wf_Current_Invoice_number'=>'woocommerce_wf_Current_Invoice_number',
			'woocommerce_wf_invoice_start_number'=>'woocommerce_wf_invoice_start_number',
			'woocommerce_wf_invoice_number_prefix'=>'woocommerce_wf_invoice_number_prefix',
			'woocommerce_wf_invoice_padding_number'=>'woocommerce_wf_invoice_padding_number',
			'woocommerce_wf_invoice_number_postfix'=>'woocommerce_wf_invoice_number_postfix',
			'woocommerce_wf_invoice_as_ordernumber'=>"woocommerce_wf_invoice_as_ordernumber",
			'woocommerce_wf_enable_invoice'=>"woocommerce_wf_enable_invoice",
			'woocommerce_wf_add_customer_note_in_invoice'=>"woocommerce_wf_add_customer_note_in_invoice",
			'wf_woocommerce_invoice_show_print_button' => 'wf_woocommerce_invoice_show_print_button',
		),
		'packinglist'=>array(
			'woocommerce_wf_attach_image_packinglist'=>'woocommerce_wf_attach_image_packinglist',
			'woocommerce_wf_add_customer_note_in_packinglist'=>"woocommerce_wf_add_customer_note_in_packinglist",
			'woocommerce_wf_packinglist_footer_pk'=>"woocommerce_wf_packinglist_footer_pk",
		),
		'deliverynote'=>array(
			'woocommerce_wf_attach_image_deliverynote'=>'woocommerce_wf_attach_image_delivery_note',
			'woocommerce_wf_add_customer_note_in_deliverynote'=>"woocommerce_wf_add_customer_note_in_deliverynote",
			'woocommerce_wf_packinglist_footer_dn'=>"woocommerce_wf_packinglist_footer_dn",
		),
		'dispatchlabel'=>array(
			'woocommerce_wf_add_customer_note_in_dispatchlabel'=>"woocommerce_wf_add_customer_note_in_dispatchlabel",
			'woocommerce_wf_packinglist_footer_dl'=>"woocommerce_wf_packinglist_footer_dl",
		),
		'shippinglabel'=>array(
			'woocommerce_wf_packinglist_footer_sl'=>'woocommerce_wf_packinglist_footer_sl',
		),
	);
	private static $def_vals=array(
		'main'=>array(
			'woocommerce_wf_packinglist_companyname'=>'',
			'woocommerce_wf_packinglist_logo'=>'',
			'woocommerce_wf_packinglist_footer'=>'',
			'woocommerce_wf_packinglist_sender_name'=>'',
			'woocommerce_wf_packinglist_sender_address_line1'=>'',
			'woocommerce_wf_packinglist_sender_address_line2'=>'',
			'woocommerce_wf_packinglist_sender_city'=>'',
			'wf_country'=>'',
			'woocommerce_wf_packinglist_sender_postalcode'=>'',
			'woocommerce_wf_packinglist_sender_contact_number'=>'',
			'woocommerce_wf_packinglist_sender_vat'=>'',
			'woocommerce_wf_packinglist_preview'=>'enabled',
			'woocommerce_wf_packinglist_package_type'=>'single_packing', //just keeping to avoid errors
			'woocommerce_wf_packinglist_boxes'=>array(),
			'woocommerce_wf_add_rtl_support'=>'No',
		),
		'invoice'=>array(
        	'woocommerce_wf_generate_for_orderstatus'=>array('wc-completed'),
        	'woocommerce_wf_attach_invoice'=>array(),
        	'woocommerce_wf_packinglist_logo'=>'',
        	'woocommerce_wf_add_invoice_in_mail'=>'No',
        	'woocommerce_wf_packinglist_frontend_info'=>'Yes',
        	'woocommerce_wf_invoice_number_format'=>"[number]",
			'woocommerce_wf_Current_Invoice_number'=>1,
			'woocommerce_wf_invoice_start_number'=>1,
			'woocommerce_wf_invoice_number_prefix'=>'',
			'woocommerce_wf_invoice_padding_number'=>0,
			'woocommerce_wf_invoice_number_postfix'=>'',
			'woocommerce_wf_invoice_as_ordernumber'=>"Yes",
			'woocommerce_wf_enable_invoice'=>"Yes",
			'woocommerce_wf_add_customer_note_in_invoice'=>"No", //Add customer note
			'wf_invoice_contactno_email'=>array('contact_number','email'),
			'wf_woocommerce_invoice_show_print_button' => array('order_listing','order_details','order_email'),
		),
		'packinglist'=>array(
			'woocommerce_wf_attach_image_packinglist'=>'Yes',
			'woocommerce_wf_add_customer_note_in_packinglist'=>'No',
			'woocommerce_wf_packinglist_footer_pk'=>'No',
			'wf_packinglist_contactno_email'=>array('contact_number','email'),
		),
		'deliverynote'=>array(
			'woocommerce_wf_attach_image_deliverynote'=>'Yes',
			'woocommerce_wf_add_customer_note_in_deliverynote'=>'No',
			'woocommerce_wf_packinglist_footer_dn'=>'No',
			'wf_deliverynote_contactno_email'=>array('contact_number','email'),
		),
		'dispatchlabel'=>array(
			'woocommerce_wf_add_customer_note_in_dispatchlabel'=>'No',
			'woocommerce_wf_packinglist_footer_dl'=>'No',
			'wf_dispatchlabel_contactno_email'=>array('contact_number','email'),
		),
		'shippinglabel'=>array(
			'woocommerce_wf_packinglist_label_size'=>2, //full page
			'woocommerce_wf_enable_multiple_shipping_label'=>'Yes',
			'woocommerce_wf_packinglist_footer_sl'=>'No',
			'wf_shipping_label_column_number'=>1,
			'wf_shippinglabel_contactno_email'=>array('contact_number','email'),
		),
	);
	public function __construct()
	{

	}
	public static function migrate()
	{	
		self::$def_vals['main']['wf_country']=(get_option('woocommerce_default_country') ? get_option('woocommerce_default_country') : '');
		self::migrate_options();
		self::migrate_templates();

		//module status always below, becuase if module is disabled. all other migrations will fail
		self::migrate_module_status();

	}

	private static function migrate_module_status()
	{
		if(!get_option('wf_pklist_module_status_migrated'))  //check already migrated
		{
			update_option('wf_pklist_module_status_migrated',1);
			$wt_pklist_common_modules=get_option('wt_pklist_common_modules');
			$module_list=array(
				'invoice'=>'woocommerce_wf_enable_invoice',
				'packinglist'=>'woocommerce_wf_enable_packing_slip',
				'shippinglabel'=>'woocommerce_wf_enable_shipping_label',
				'deliverynote'=>'woocommerce_wf_enable_delivery_note',
				'dispatchlabel'=>'woocommerce_wf_enable_dispath_label',
			);
			$wt_pklist_common_modules=(!$wt_pklist_common_modules && !is_array($wt_pklist_common_modules) ? array() : $wt_pklist_common_modules); 
			foreach($module_list as $module_id=>$module_st_option)
			{
				$st=get_option($module_st_option);
				if($st)
				{
					if($st=='Yes')
					{
						$wt_pklist_common_modules[$module_id]=1;
					}else
					{
						$wt_pklist_common_modules[$module_id]=0;	
					}
				}else //if option not set, that means modules is enabled
				{
					$wt_pklist_common_modules[$module_id]=1;
				}
			}
			update_option('wt_pklist_common_modules',$wt_pklist_common_modules);
		}
	}
	private static function migrate_templates()
	{
		global $wpdb;
		if(!get_option('wf_pklist_templates_migrated'))  //check already migrated
		{
			update_option('wf_pklist_templates_migrated',1);
			
			ob_start();
			include plugin_dir_path(__FILE__).'data/data.invoice.migrator.template.php';
			$base_html=ob_get_clean();

			$template_data=$wpdb->get_results($wpdb->prepare("SELECT option_name,option_value FROM {$wpdb->prefix}options 
			    WHERE option_name LIKE '%s' 
			    ORDER BY option_name ASC",'wf_invoice_template_%'), ARRAY_A);
			if($template_data && is_array($template_data))
			{
			    $arr_key=array_column($template_data,'option_name');
			    $arr_val=array_column($template_data,'option_value');
			    $template_data_new=array_combine($arr_key,$arr_val);
			    end($template_data_new);
			    $for_loop_end=(int) str_replace(array('wf_invoice_template_','value','custom','name','from'),'',key($template_data_new));
			    $active_key=get_option('wf_invoice_active_key');
			    $tme=time();
			    $table_name=$wpdb->prefix.'wfpklist_template_data';
			    for($i=0; $i<=$for_loop_end; $i++)
			    {
			        $template_key='wf_invoice_template_'.$i;
			        if(isset($template_data_new[$template_key])) //template exists
			        {
			            if(isset($template_data_new[$template_key.'value'])) //value exists, then its a custom created
			            {
			                $name=isset($template_data_new[$template_key.'name']) ? $template_data_new[$template_key.'name'] : 'Untitled'.$i;
			                $is_active=($active_key==$template_key ? 1 : 0);           
			                $data_arr=explode("|",$template_data_new[$template_key.'value']);               
			                $insert_data=array(
			                    'template_name'=>$name,
			                    'template_html'=>self::migrate_html($base_html,$data_arr),
			                    'template_from'=>0,
			                    'template_type'=>'invoice',
			                    'created_at'=>$tme,
			                    'updated_at'=>$tme,
			                    'is_active'=>$is_active
			                );
			                $insert_data_type=array(
			                    '%s','%s','%d','%s','%d','%d','%d'
			                ); 
			                $wpdb->insert($table_name,$insert_data,$insert_data_type);
			            }
			        }
			    }
			}
		}
	}
	private static function migrate_html($html,$data_arr)
	{
	    $find_replace=array();
	    if($data_arr[3]=='logo')
        {
        	$find_replace['[wfte_mig_logo_toggle]']='';
        	$find_replace['[wfte_mig_company_name_toggle]']='wfte_hidden';
        }else
        {
        	$find_replace['[wfte_mig_logo_toggle]']='wfte_hidden';
        	$find_replace['[wfte_mig_company_name_toggle]']='';
        }
	    if($data_arr[2]!== 'no')
	    {
	        $find_replace['[wfte_mig_logomain_toggle]']='';
	    }else
	    {
	        $find_replace['[wfte_mig_logomain_toggle]']='wfte_hidden';
	    }
	    if(is_numeric($data_arr[0]))
	    {
	    	$find_replace['[wfte_mig_logo_width]']='width:'.$data_arr[0].'px; ';
	    }else
	    {
	    	$find_replace['[wfte_mig_logo_width]']='width:'.$data_arr[0].'; ';
	    }
	    if(is_numeric($data_arr[1]))
	    {
	    	$find_replace['[wfte_mig_logo_height]']='height:'.$data_arr[1].'px; ';
	    }else
	    {
	    	$find_replace['[wfte_mig_logo_height]']='height:'.$data_arr[1].'; ';
	    }

	    if($data_arr[4]== 'no')
	    {
	        $find_replace['[wfte_mig_invnum_toggle]']='wfte_hidden';
	    }else
	    {
	        $find_replace['[wfte_mig_invnum_toggle]']='';
	    }
	    $find_replace['[wfte_mig_invnum_fsize]']='font-size:'.$data_arr[5].'px; ';
	    $find_replace['[wfte_mig_invdte_fsize]']='font-size:'.$data_arr[11].'px; ';
	    $find_replace['[wfte_mig_invnum_fweight]']='font-weight:'.$data_arr[6].'; ';
	    if($data_arr[94]== 'no')
	    {
	        $find_replace['[wfte_mig_ordnum_toggle]']='wfte_hidden';
	    }else
	    {
	        $find_replace['[wfte_mig_ordnum_toggle]']='';
	    }
	    $find_replace['[wfte_mig_ordnum_label]']=$data_arr[90];
	    $find_replace['[wfte_mig_ordnum_fsize]']='font-size:'.$data_arr[89].'px; ';
	    $find_replace['[wfte_mig_orddte_fsize]']='font-size:'.$data_arr[17].'px; ';
	    $find_replace['[wfte_mig_ordnum_fweight]']='font-weight:'.$data_arr[91].'; ';
	    $find_replace['[wfte_mig_orddte_fweight]']='font-weight:'.$data_arr[19].'; ';
	    if($data_arr[92]!='default')
	    {
	        $find_replace['[wfte_mig_ordnum_color]']='color:#'.$data_arr[92].'; ';
	    }else
	    {
	        $find_replace['[wfte_mig_ordnum_color]']='';
	    }
	    if($data_arr[20]!='default')
	    {
	        $find_replace['[wfte_mig_orddte_color]']='color:#'.$data_arr[20].'; ';
	    }else
	    {
	        $find_replace['[wfte_mig_orddte_color]']='';
	    }
	    if($data_arr[8]!='default')
	    {
	        $find_replace['[wfte_mig_invnum_color]']='color:#'.$data_arr[8].'; ';
	    }else
	    {
	        $find_replace['[wfte_mig_invnum_color]']='';
	    }
	    if($data_arr[9]== 'no')
	    {
	        $find_replace['[wfte_mig_invdte_toggle]']='wfte_hidden';
	    }else
	    {
	        $find_replace['[wfte_mig_invdte_toggle]']='';
	    }
	    if($data_arr[15]== 'no')
	    {
	        $find_replace['[wfte_mig_orddte_toggle]']='wfte_hidden';
	    }else
	    {
	        $find_replace['[wfte_mig_orddte_toggle]']='';
	    }
	    $find_replace['[wfte_mig_invdte_frmt]']=$data_arr[10];
	    $find_replace['[wfte_mig_orddte_frmt]']=$data_arr[16];
	    $find_replace['[wfte_mig_invnum_label]']=$data_arr[7];
	    $find_replace['[wfte_mig_invdte_label]']=$data_arr[12];
	    $find_replace['[wfte_mig_orddte_label]']=$data_arr[18];
	    $find_replace['[wfte_mig_invdte_fweight]']=$data_arr[13];
	    if($data_arr[14]!='default')
	    {
	        $find_replace['[wfte_mig_invdte_color]']='color:#'.$data_arr[14].'; ';
	    }else
	    {
	        $find_replace['[wfte_mig_invdte_color]']='';
	    }

	    if($data_arr[21]== 'no')
	    {
	        $find_replace['[wfte_mig_frmaddr_toggle]']='wfte_hidden';
	    }else
	    {
	        $find_replace['[wfte_mig_frmaddr_toggle]']='';
	    }
	    $find_replace['[wfte_mig_frmaddr_label]']=$data_arr[22];

	    $find_replace['[wfte_mig_frmaddr_txtalign]']=self::set_text_align($data_arr[23]);

	    if($data_arr[24]!='default')
	    {
	        $find_replace['[wfte_mig_frmaddr_color]']='color:#'.$data_arr[24].'; ';
	    }else
	    {
	        $find_replace['[wfte_mig_frmaddr_color]']='';
	    }

	    if($data_arr[25]== 'no')
	    {
	        $find_replace['[wfte_mig_biladdr_toggle]']='wfte_hidden';
	    }else
	    {
	        $find_replace['[wfte_mig_biladdr_toggle]']='';
	    }
	    $find_replace['[wfte_mig_biladdr_label]']=$data_arr[26];
	    $find_replace['[wfte_mig_biladdr_txtalign]']=self::set_text_align($data_arr[27]);
	    if($data_arr[28]!='default')
	    {
	        $find_replace['[wfte_mig_biladdr_color]']='color:#'.$data_arr[28].'; ';
	    }else
	    {
	        $find_replace['[wfte_mig_biladdr_color]']='';
	    }

	    if($data_arr[29]== 'no')
	    {
	        $find_replace['[wfte_mig_shipaddr_toggle]']='wfte_hidden';
	    }else
	    {
	        $find_replace['[wfte_mig_shipaddr_toggle]']='';
	    }
	    $find_replace['[wfte_mig_shipaddr_label]']=$data_arr[30];
	    $find_replace['[wfte_mig_shipaddr_txtalign]']=self::set_text_align($data_arr[31]);
	    if($data_arr[32]!='default')
	    {
	        $find_replace['[wfte_mig_shipaddr_color]']='color:#'.$data_arr[32].'; ';
	    }else
	    {
	        $find_replace['[wfte_mig_shipaddr_color]']='';
	    }
	    $find_replace['[wfte_mig_exdta_fsize]']='font-size:'.$data_arr[81].'px; ';
	    if($data_arr[80]!='none'){
	    	$find_replace['[wfte_mig_exdta_txt]']=str_replace('-*-', '|', $data_arr[80]);
	    }else
	    {
	    	$find_replace['[wfte_mig_exdta_txt]']='';
	    }
	    

	    //==================================
	    if($data_arr[63]== 'no')
	    {
	        $find_replace['[wfte_mig_ptble_toggle]']='wfte_hidden';
	    }else
	    {
	        $find_replace['[wfte_mig_ptble_toggle]']='';
	    }
	    if($data_arr[64]!='default')
	    {
	        $find_replace['[wfte_mig_ptble_bg]']='background:#'.$data_arr[64].'; ';
	        $find_replace['[wfte_mig_ptblehd_brdr]']='border-color:#'.$data_arr[64].'; ';
	    }else
	    {
	        $find_replace['[wfte_mig_ptble_bg]']='';
	        $find_replace['[wfte_mig_ptblehd_brdr]']='';
	    }
	    if($data_arr[65]!='default')
	    {
	        $find_replace['[wfte_mig_ptblehd_color]']='color:#'.$data_arr[65].'; ';
	    }else
	    {
	        $find_replace['[wfte_mig_ptblehd_color]']='';
	    }
	    $find_replace['[wfte_mig_ptble_txtalign]']=self::set_text_align($data_arr[66]);

	    if($data_arr[67]!='default')
	    {
	        $find_replace['[wfte_mig_ptblebd_color]']='color:#'.$data_arr[67].'; ';
	    }else
	    {
	        $find_replace['[wfte_mig_ptblebd_color]']='';
	    }
	    $find_replace['[wfte_mig_ptbbody_txtalign]']=self::set_text_align($data_arr[68]);
	    $find_replace['[wfte_mig_prdt_label]']=$data_arr[70];
	    $find_replace['[wfte_mig_sku_label]']=$data_arr[69];
	    $find_replace['[wfte_mig_qty_label]']=$data_arr[71];
	    $find_replace['[wfte_mig_prce_label]']=$data_arr[88];
	    $find_replace['[wfte_mig_ttprce_label]']=$data_arr[72];
	    $find_replace['[wfte_mig_subtt_label]']=$data_arr[73];
	    $find_replace['[wfte_mig_ship_label]']=$data_arr[74];
	    $find_replace['[wfte_mig_crtdsc_label]']=$data_arr[75];
	    $find_replace['[wfte_mig_orddsc_label]']=$data_arr[76];
	    $find_replace['[wfte_mig_tttx_label]']=$data_arr[77];
	    $find_replace['[wfte_mig_ttl_label]']=$data_arr[78];
	    $find_replace['[wfte_mig_fee_label]']=$data_arr[93];
	    $find_replace['[wfte_mig_payinf_label]']=$data_arr[79];

	    if(get_option('woocommerce_wf_packinglist_footer_in')=='Yes')
	    {
	    	$find_replace['[wfte_mig_footer_toggle]']='';
	    }else
		{
			$find_replace['[wfte_mig_footer_toggle]']='wfte_hidden';	
		}

	    return str_replace(array_keys($find_replace),array_values($find_replace),$html);
	}
	private static function set_text_align($vl)
	{
		if($vl== 'right')
	    {
	        $out='wfte_text_right';
	    }elseif($vl== 'center')
	    {
	        $out='wfte_text_center';
	    }
	    else
	    {
	        $out='wfte_text_left';
	    }
	    return $out;
	}
	/**
	* @since 2.5.1 
	* generate module id
	*/
	private static function get_module_id($module_base)
	{
		return 'wf_woocommerce_packing_list_'.$module_base;
	}

	/**
	* @since 2.5.1 
	* update plugin settings
	*/
	private static function update_settings($the_options,$base_id='')
	{
		if($base_id!="" && $base_id!='main') //main is reserved so do not allow modules named main
		{
			update_option($base_id,$the_options);
		}
		if($base_id=="")
		{
			update_option('Wf_Woocommerce_Packing_List',$the_options);
		}
	}

	/**
	* @since 2.5.0 
	* @since 2.5.1 default value checking added
	* migrate settings
	*/
	private static function migrate_options()
	{
		if(!get_option('wf_pklist_options_migrated'))  //check already migrated
		{
			if(!get_option('Wf_Woocommerce_Packing_List') && !get_option('woocommerce_wf_packinglist_companyname')) //new install
			{
				update_option('wf_pklist_new_install',1); //user after 2.5.0 update
			}

			update_option('wf_pklist_options_migrated',1); //
			foreach (self::$option_fields as $module_key=>$options)
			{
				$module_id=($module_key=='main' ? '' : self::get_module_id($module_key));
				$the_options=array();
				foreach($options as $option_key_new=>$option_key_old)
				{
					$old_vl=get_option($option_key_old);
					if(!$old_vl)
					{
						$old_vl='';
						if(isset(self::$def_vals[$module_key][$option_key_new]))
						{	
							$old_vl=self::$def_vals[$module_key][$option_key_new];
							if($module_key == "main"){
								$old_vl = self::get_def_value_from_wc_and_wp($option_key_new,$old_vl);
							}
						}
					}
					$old_vl=($old_vl==='no' ? 'No' : $old_vl);
					$the_options[$option_key_new]=$old_vl;
				}
				self::update_settings($the_options,$module_id);
			}
		}else{
			$module_key = "invoice";
			$module_id = self::get_module_id($module_key);
			$woocommerce_wf_packinglist_frontend_info = Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_packinglist_frontend_info',$module_id);
			if($woocommerce_wf_packinglist_frontend_info == "No" || $woocommerce_wf_packinglist_frontend_info == "no"){
				Wf_Woocommerce_Packing_List::update_option('wf_woocommerce_invoice_show_print_button',array(),$module_id);
				Wf_Woocommerce_Packing_List::update_option('woocommerce_wf_packinglist_frontend_info',"Yes",$module_id);
			}
		}
	}

	private static function get_def_value_from_wc_and_wp($key_name,$result){

		switch ($key_name) {
			case 'woocommerce_wf_packinglist_logo':
				if ( has_custom_logo() ) {
					$custom_logo_id = get_theme_mod( 'custom_logo' );
					$invoice_logo = wp_get_attachment_image_src( $custom_logo_id , 'full' );
				    return esc_url( $invoice_logo[0] );
				}else{
					return $result;
				}
				break;
			case 'woocommerce_wf_packinglist_sender_name':
				return (get_option('blogname') ? get_option('blogname') : $result);
				break;
			case 'woocommerce_wf_packinglist_sender_address_line1':
				return (get_option('woocommerce_store_address') ? get_option('woocommerce_store_address') : $result);
				break;
			case 'woocommerce_wf_packinglist_sender_address_line2':
				return (get_option('woocommerce_store_address_2') ? get_option('woocommerce_store_address_2') : $result);
				break;
			case 'woocommerce_wf_packinglist_sender_city':
				return (get_option('woocommerce_store_city') ? get_option('woocommerce_store_city') : $result);
				break;
			case 'wf_country':
				return (get_option('woocommerce_default_country') ? get_option('woocommerce_default_country') : $result);
				break;
			case 'woocommerce_wf_packinglist_sender_postalcode':
				return (get_option('woocommerce_store_postcode') ? get_option('woocommerce_store_postcode') : $result);
				break;
			default:
				return $result;
				break;
		}
	}
}