<?php
/**
 * Packinglist section of the plugin
 *
 * @link       
 * @since 2.5.0     
 *
 * @package  Wf_Woocommerce_Packing_List  
 */
if (!defined('ABSPATH')) {
    exit;
}

class Wf_Woocommerce_Packing_List_Packinglist
{
	public $module_id='';
	public static $module_id_static='';
	public $module_base='packinglist';
	public $module_title='';
    private $customizer=null;
	public function __construct()
	{
		$this->module_id=Wf_Woocommerce_Packing_List::get_module_id($this->module_base);
		self::$module_id_static=$this->module_id;
		$this->module_title=__('Packing slip', 'print-invoices-packing-slip-labels-for-woocommerce');

		add_filter('wf_module_default_settings',array($this,'default_settings'),10,2);
		//hook to generate template html
		add_filter('wf_module_generate_template_html',array($this,'generate_template_html'),10,6);
		add_action('wt_print_doc',array($this,'print_it'),10,2);
		add_action('wt_pklist_document_save_settings',array($this,'save_settings'),10,2);
		
		//hide empty fields on template
		add_filter('wf_pklist_alter_hide_empty',array($this,'hide_empty_elements'),10,6);

		//filter to alter settings
		add_filter('wf_pklist_alter_settings',array($this,'alter_settings'),10,2);		
		add_filter('wf_pklist_alter_option',array($this,'alter_option'),10,4);

		//alter product table column
		add_filter('wf_pklist_alter_product_table_head',array($this,'alter_product_table_head'),10,3);

		//initializing customizer		
		$this->customizer=Wf_Woocommerce_Packing_List::load_modules('customizer');

		add_filter('wf_pklist_document_setting_fields',array($this,'admin_settings_page'),10,1);

		
		add_filter('wt_print_metabox',array($this,'add_metabox_data'),10,3);
		add_filter('wt_print_actions',array($this,'add_print_buttons'),10,3);
		add_filter('wt_print_bulk_actions',array($this,'add_bulk_print_buttons'));

		add_filter('wf_pklist_alter_find_replace',array($this,'alter_find_replace'),10,5);
		add_filter('wt_pklist_alter_tooltip_data',array($this,'register_tooltips'),1);
	}

	/**
	* 	@since 2.5.8
	* 	Hook the tooltip data to main tooltip array
	*/
	public function register_tooltips($tooltip_arr)
	{
		$tooltip_arr[$this->module_id]=array(
			$this->module_id.'[woocommerce_wf_attach_image_packinglist]'=>__('Enable to include product image in the document.','print-invoices-packing-slip-labels-for-woocommerce'),
			$this->module_id.'[woocommerce_wf_add_customer_note_in_packinglist]'=>__('Enable to display the customer\'s note in the document.','print-invoices-packing-slip-labels-for-woocommerce'),
			$this->module_id.'[woocommerce_wf_packinglist_footer_pk]'=>__('Enable to display footer content in the document.','print-invoices-packing-slip-labels-for-woocommerce'),
		);
		return $tooltip_arr;
	}

	public function alter_find_replace($find_replace,$template_type,$order,$box_packing,$order_package)
	{
		if($template_type==$this->module_base)
		{
			$is_footer=Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_packinglist_footer_pk',$this->module_id);
			if($is_footer!='Yes')
			{
				$find_replace['wfte_footer']='wfte_hidden';
			}
		}
		return $find_replace;
	}
	public function alter_product_table_head($columns_list_arr,$template_type,$order)
	{
		if($template_type==$this->module_base)
		{
			$is_image_enabled=Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_attach_image_'.$this->module_base,$this->module_id);
			if($is_image_enabled=='No')
			{				
				if(isset($columns_list_arr['image'])) //image column exists
				{
					$columns_list_arr['-image']=$columns_list_arr['image'];
					unset($columns_list_arr['image']);
				}
			}else
			{
				if(!isset($columns_list_arr['image'])) //image column exists
				{
					$columns_list_arr['image']=isset($columns_list_arr['-image']) ? $columns_list_arr['-image'] : 'Image';
				}
				unset($columns_list_arr['-image']); //if exists
			}
		}
		return $columns_list_arr;
	}
	public function alter_option($vl,$settings,$option_name,$base_id)
	{
		if($base_id==$this->module_id)
		{
			if($option_name=='wf_'.$this->module_base.'_contactno_email')
			{
				if($settings['woocommerce_wf_add_customer_note_in_'.$this->module_base]=='Yes')
				{
					if(array_search('cus_note',$vl)===false)
					{
						$vl[]='cus_note';
					}
				}else
				{
					if(($key=array_search('cus_note',$vl))!==false)
					{
						unset($vl[$key]);
					}
				}
			}
		}
		return $vl;
	}
	public function alter_settings($settings,$base_id)
	{
		if($base_id==$this->module_id)
		{
			$vl=$settings['wf_'.$this->module_base.'_contactno_email'];
			if($settings['woocommerce_wf_add_customer_note_in_'.$this->module_base]=='Yes')
			{
				if(array_search('cus_note',$vl)===false)
				{
					$vl[]='cus_note';
				}
			}else
			{
				if(($key=array_search('cus_note',$vl))!==false)
				{
					unset($vl[$key]);
				}
			}
			$settings['wf_'.$this->module_base.'_contactno_email']=$vl;
		}
		return $settings;
	}

	public function hide_empty_elements($hide_on_empty_fields,$template_type)
	{
		if($template_type==$this->module_base)
		{
			$hide_on_empty_fields[]='wfte_qr_code';
			$hide_on_empty_fields[]='wfte_box_name';
		}
		return $hide_on_empty_fields;
	}

	public function save_settings()
	{
		$the_options=Wf_Woocommerce_Packing_List::get_settings($this->module_id);
		//save settings
		foreach($the_options as $key => $value) 
        {
            if(isset($_POST[$this->module_id][$key]))
            {
            	$the_options[$key]=$this->validate_settings_data($_POST[$this->module_id][$key],$key);
            }
        }
        $the_options['wf_'.$this->module_base.'_contactno_email']=array('contact_number','email');
        Wf_Woocommerce_Packing_List::update_settings($the_options,$this->module_id);
	    // save settings
	}

	public function validate_settings_data($val,$key)
	{
		switch ($key) 
		{
			case 'woocommerce_wf_attach_image_packinglist':
			case 'woocommerce_wf_add_customer_note_in_packinglist':
			case 'woocommerce_wf_packinglist_footer_pk':
			case 'woocommerce_wf_packinglist_variation_data':
				$val=sanitize_text_field($val);
			break;
			case 'wf_'.$this->module_base.'_contactno_email':
				$val=array_map('sanitize_text_field',$val);
			break;
			default:
				
			break;
		}
		return $val;
	}

	public function admin_settings_page($html)
	{
		ob_start();
		include(plugin_dir_path( __FILE__ ).'views/general.php');
		$html.=ob_get_clean();
		return $html;
	} 

	/**
	 *  Items needed to be converted to HTML for print
	 */
	public function generate_template_html($find_replace,$html,$template_type,$order,$box_packing=null,$order_package=null)
	{
		if($template_type==$this->module_base)
		{
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_billing_address($find_replace,$template_type,$order);		
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_shipping_address($find_replace,$template_type,$order);					
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::package_doc_items($find_replace,$template_type,$order,$box_packing,$order_package);
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_product_table($find_replace,$template_type,$html,$order,$box_packing,$order_package);		
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_other_data($find_replace,$template_type,$html,$order);		
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_order_data($find_replace,$template_type,$html,$order);		
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_extra_fields($find_replace,$template_type,$html,$order);
		}
		return $find_replace;
	}

	/**
	 *  Module default settings
	 */
	public function default_settings($settings,$base_id)
	{
		if($base_id==$this->module_id)
		{
			return array(
				'woocommerce_wf_attach_image_packinglist'=>'Yes',
				'woocommerce_wf_add_customer_note_in_packinglist'=>'No',
				'woocommerce_wf_packinglist_footer_pk'=>'No',
				'woocommerce_wf_packinglist_variation_data'=>'Yes', //Add product variation data
				'wf_'.$this->module_base.'_contactno_email'=>array('contact_number','email'),
			);
		}else
		{
			return $settings;
		}
	}
	public function add_bulk_print_buttons($actions)
	{
		$actions['print_packinglist']=__('Print Packing slip','print-invoices-packing-slip-labels-for-woocommerce');
		return $actions;
	}
	public function add_print_buttons($html,$order,$order_id)
	{
		$this->generate_print_button_data($order,$order_id,"list_page");
		return $html;
	}
	private function generate_print_button_data($order,$order_id, $button_location="detail_page")
	{
		$icon_url=plugin_dir_url(__FILE__).'/assets/images/packinglist-icon.png';
		$label_txt=__('Print Packing slip','print-invoices-packing-slip-labels-for-woocommerce');
		Wf_Woocommerce_Packing_List_Admin::generate_print_button_data($order,$order_id,'print_packinglist',$label_txt,$icon_url,0,$button_location);
		if($button_location=="detail_page")
		{
			$icon_url_dw=plugin_dir_url(__FILE__).'/assets/images/download-packinglist.png';
			$label_txt_dw=__('Download Packing slip','print-invoices-packing-slip-labels-for-woocommerce');
			Wf_Woocommerce_Packing_List_Admin::generate_print_button_data($order, $order_id, 'download_packinglist', $label_txt_dw, $icon_url_dw, 0, $button_location);
		}
	}
	public function add_metabox_data($html,$order,$order_id)
	{
		$this->generate_print_button_data($order,$order_id);
		return $html;
	}
	
	/* 
	* Print_window for packinglist
	* @param $orders : order ids
	*/    
    public function print_it($order_ids,$action) 
    {
    	if($action=='print_packinglist' || $action=='download_packinglist')
    	{   
    		if(!is_array($order_ids))
    		{
    			return;
    		}    
	        if(!is_null($this->customizer))
	        {
	        	$pdf_name=$this->customizer->generate_pdf_name($this->module_title, $order_ids);
	        	$this->customizer->template_for_pdf=($action=='download_packinglist' ? true : false);	        	
	        	$html=$this->generate_order_template($order_ids,$pdf_name);				
				if($action=='download_packinglist')
	        	{
	        		$this->customizer->generate_template_pdf($html, $this->module_base, $pdf_name, 'download');
	        	}else
	        	{
	        		echo $html;
	        	}
	        }
	        exit();
    	}
    }
    public function generate_order_template($orders,$page_title)
    {
    	$template_type=$this->module_base;
    	//taking active template html
    	$html=$this->customizer->get_template_html($template_type);
    	$style_blocks=$this->customizer->get_style_blocks($html);
    	$html=$this->customizer->remove_style_blocks($html,$style_blocks);
    	$out='';
    	if($html!="")
    	{
    		if (!class_exists('Wf_Woocommerce_Packing_List_Box_packing')) {
		        include_once WF_PKLIST_PLUGIN_PATH.'includes/class-wf-woocommerce-packing-list-box_packing.php';
		    }
	        $box_packing=new Wf_Woocommerce_Packing_List_Box_packing();
	        $out_arr=array();
	        foreach ($orders as $order_id)
	        {
	        	$order = ( WC()->version < '2.7.0' ) ? new WC_Order($order_id) : new wf_order($order_id);
				$order_packages=null;
				$order_packages=$box_packing->create_order_package($order, $template_type);
				$number_of_order_package=count($order_packages);
				if(!empty($order_packages)) 
				{
					$order_pack_inc=0;
					foreach ($order_packages as $order_package_id => $order_package)
					{
						$order_pack_inc++;
						$order=( WC()->version < '2.7.0' ) ? new WC_Order($order_id) : new wf_order($order_id);
						$out_arr[]=$this->customizer->generate_template_html($html,$template_type,$order,$box_packing,$order_package);			            
					}
					$document_created = Wf_Woocommerce_Packing_List_Admin::created_document_count($order_id,$template_type); 
				}else
				{
					wp_die(__("Unable to print Packing slip. Please check the items in the order.",'print-invoices-packing-slip-labels-for-woocommerce'), "", array());
				}
			}
			$out=implode('<p class="pagebreak"></p>',$out_arr).'<p class="no-page-break"></p>';

			$out=$this->customizer->append_style_blocks($out,$style_blocks);
			//adding header and footer
			$out=$this->customizer->append_header_and_footer_html($out,$template_type,$page_title);
    	}
    	return $out;
    }
}
new Wf_Woocommerce_Packing_List_Packinglist();