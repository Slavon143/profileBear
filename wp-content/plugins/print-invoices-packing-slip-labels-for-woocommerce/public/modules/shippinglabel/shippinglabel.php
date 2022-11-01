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

class Wf_Woocommerce_Packing_List_Shippinglabel
{
	public $module_id='';
	public static $module_id_static='';
	public $module_base='shippinglabel';
    private $customizer=null;
	public function __construct()
	{
		$this->module_id=Wf_Woocommerce_Packing_List::get_module_id($this->module_base);
		self::$module_id_static=$this->module_id;

		add_filter('wf_module_default_settings',array($this,'default_settings'),10,2);

		/**
		*	@since 2.6.9
		*	Hooks to customizer right panel
		*/
		add_filter('wf_module_customizable_items',array($this,'get_customizable_items'),10,2);
		add_filter('wf_module_non_options_fields',array($this,'get_non_options_fields'),10,2);
		add_filter('wf_module_non_disable_fields',array($this,'get_non_disable_fields'),10,2);
		add_filter('wf_pklist_alter_customize_inputs',array($this,'alter_customize_inputs'),10,3);
		//hook to add which fiedls to convert
		add_filter('wf_module_convert_to_design_view_html',array($this,'convert_to_design_view_html'),10,3);

		//hook to generate template html
		add_filter('wf_module_generate_template_html',array($this,'generate_template_html'),10,6);
		
		//hide empty fields on template
		add_filter('wf_pklist_alter_hide_empty',array($this,'hide_empty_elements'),10,6);

		add_action('wt_print_doc',array($this,'print_it'),10,2);
		//add_action('wt_pklist_document_save_settings',array($this,'save_settings'),10,2);

		//initializing customizer		
		$this->customizer=Wf_Woocommerce_Packing_List::load_modules('customizer');


		add_filter('wt_print_metabox',array($this,'add_metabox_data'),10,3);
		add_filter('wt_print_actions',array($this,'add_print_buttons'),10,3);
		add_filter('wt_print_bulk_actions',array($this,'add_bulk_print_buttons'));
		add_filter('wf_pklist_alter_find_replace',array($this,'alter_find_replace'),10,5);
		
		add_filter('wt_pklist_alter_tooltip_data',array($this,'register_tooltips'),1);

		/* @since 2.6.9 add admin menu */
		add_filter('wt_admin_menu', array($this,'add_admin_pages'),10,1);

	}

	/**
	 *	@since 2.6.9
	 *  Items needed to be converted to design view
	 */
	public function convert_to_design_view_html($find_replace,$html,$template_type)
	{
		if($template_type==$this->module_base)
		{	
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_shipping_address($find_replace,$template_type);	
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_other_data($find_replace,$template_type,$html);
			$find_replace=$this->extra_fields_dummy_data($find_replace,$html,$template_type);
			$find_replace=$this->toggle_footer($find_replace);
			$find_replace = $this->toggle_qrcode($find_replace);
		}
		return $find_replace;
	}

	/**
	 *	@since 2.6.9
	 *  Dummy data for extra fields on template
	 */
	private function extra_fields_dummy_data($find_replace,$html,$template_type)
	{
		$find_replace['[wfte_weight]']='10 Kg';
		$find_replace['[wfte_ship_date]']=date('d-m-Y');
		$find_replace['[wfte_additional_data]']='';
		return $find_replace;
	}

	/**
	 *	@since 2.6.9
	 *  Alter customizer inputs
	 */
	public function alter_customize_inputs($fields,$type,$template_type)
	{
		if($template_type==$this->module_base)
		{
			if($type=='from_address' || $type=='shipping_address')
			{
				$fields=array(
					array(
						'label'=>__('Title','print-invoices-packing-slip-labels-for-woocommerce'),
						'css_prop'=>'html',
						'trgt_elm'=>$type.'_label',
					),
					array(
						'label'=>__('Title font size','print-invoices-packing-slip-labels-for-woocommerce'),
						'type'=>'text_inputgrp',
						'css_prop'=>'font-size',
						'trgt_elm'=>$type.'_label',
						'width'=>'49%',
					),
					array(
						'label'=>__('Address font size','print-invoices-packing-slip-labels-for-woocommerce'),
						'type'=>'text_inputgrp',
						'css_prop'=>'font-size',
						'trgt_elm'=>$type.'_val',
						'width'=>'49%',
						'float'=>'right',
					),
				);
			}
			elseif($type=='tel' || $type=='weight' || $type=='ship_date')
			{
				$fields=array(
					array(
						'label'=>__('Title','print-invoices-packing-slip-labels-for-woocommerce'),
						'css_prop'=>'html',
						'trgt_elm'=>$type.'_label',
					),
					array(
						'label'=>__('Title font size','print-invoices-packing-slip-labels-for-woocommerce'),
						'type'=>'text_inputgrp',
						'css_prop'=>'font-size',
						'trgt_elm'=>$type,
					)
				);
			}
		}
		return $fields;
	}

	/**
	 *	@since 2.6.9
	 *  Which items need enable in right customization panel
	 */
	public function get_customizable_items($settings,$base_id)
	{
		if($base_id==$this->module_id)
		{
			$only_pro_html='<span style="color:red;"> ('.__('Pro version','print-invoices-packing-slip-labels-for-woocommerce').')</span>';
			//these fields are the classname in template Eg: `company_logo` will point to `wfte_company_logo`
			$settings = array(
				'company_logo'=>__('Company Logo','print-invoices-packing-slip-labels-for-woocommerce').$only_pro_html,
				'from_address'=>__('From Address','print-invoices-packing-slip-labels-for-woocommerce'),
				'shipping_address'=>__('To Address','print-invoices-packing-slip-labels-for-woocommerce'),				
				'order_number'=>__('Order Number','print-invoices-packing-slip-labels-for-woocommerce'),				
				'weight'=>__('Weight','print-invoices-packing-slip-labels-for-woocommerce'),				
				'ship_date'=>__('Ship date','print-invoices-packing-slip-labels-for-woocommerce'),				
				'invoice_number'=>__('Tracking Number','print-invoices-packing-slip-labels-for-woocommerce'),				
				'email'=>__('Email Field','print-invoices-packing-slip-labels-for-woocommerce'),
				'tel'=>__('Tel Field','print-invoices-packing-slip-labels-for-woocommerce'),
				'barcode'=>__('Barcode','print-invoices-packing-slip-labels-for-woocommerce'),
				//'footer'=>__('Footer','print-invoices-packing-slip-labels-for-woocommerce'),
			);
			$settings['return_policy'] = __('Return Policy','print-invoices-packing-slip-labels-for-woocommerce').$only_pro_html;
			return $settings;
		}
		return $settings;
	}

	/**
	*	@since 2.6.9
	* 	These are the fields that have no customizable options, Just on/off
	* 
	*/
	public function get_non_options_fields($settings,$base_id)
	{
		if($base_id==$this->module_id)
		{
			return array(
				'barcode',
				'return_policy',
			);
		}
		return $settings;
	}

	/**
	*	@since 2.6.9
	* 	These are the fields that are switchable
	* 
	*/
	public function get_non_disable_fields($settings,$base_id)
	{
		if($base_id==$this->module_id)
		{
			return array(
				'from_address',
				'shipping_address',
			);
		}
		return $settings;
	}

	/**
	* 	Add admin menu
	*	@since 	2.6.9
	*/
	public function add_admin_pages($menus)
	{
		$menus[]=array(
			'submenu',
			WF_PKLIST_POST_TYPE,
			__('Shipping label','print-invoices-packing-slip-labels-for-woocommerce'),
			__('Shipping label','print-invoices-packing-slip-labels-for-woocommerce'),
			'manage_woocommerce',
			$this->module_id,
			array($this, 'admin_settings_page')
		);
		return $menus;
	}

	/**
	*  	Admin settings page
	*	@since 	2.6.9
	*/
	public function admin_settings_page()
	{
		
		$params=array(
			'nonces' => array(
	            'main'=>wp_create_nonce($this->module_id),
	        ),
	        'ajax_url' => admin_url('admin-ajax.php'),
	        'msgs'=>array(
	        	'enter_order_id'=>__('Please enter order number','print-invoices-packing-slip-labels-for-woocommerce'),
	        	'generating'=>__('Generating','print-invoices-packing-slip-labels-for-woocommerce'),
	        	'error'=>__('Error','print-invoices-packing-slip-labels-for-woocommerce'),
	        )
		);
		wp_localize_script($this->module_id,$this->module_id,$params);
		$the_options=Wf_Woocommerce_Packing_List::get_settings($this->module_id);

	    //initializing necessary modules, the argument must be current module name/folder
	    if(!is_null($this->customizer))
		{
			$this->customizer->init($this->module_base);
		}


		include(plugin_dir_path( __FILE__ ).'views/admin-settings.php');
	}

	/**
	*	@since 2.6.8
	*	Toggle footer visibility based on option
	*/
	private function toggle_footer($find_replace)
	{
		$is_footer=Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_packinglist_footer_sl', $this->module_id);
		if($is_footer!='Yes')
		{
			$find_replace['wfte_footer']='wfte_hidden';
		}
		return $find_replace;
	}

	private function toggle_qrcode($find_replace)
	{
		$template_type=$this->module_base;
		$show_qrcode_placeholder = false;
		$show_qrcode_placeholder = apply_filters('wt_pklist_show_qrcode_placeholder_in_template',$show_qrcode_placeholder,$template_type);
		if($show_qrcode_placeholder === false)
		{
			$find_replace['wfte_qrcode']='wfte_hidden';
		}
		return $find_replace;
	} 
	/**
	* 	@since 2.5.8
	* 	Hook the tooltip data to main tooltip array
	*/
	public function register_tooltips($tooltip_arr)
	{
		$tooltip_arr[$this->module_id]=array(
			$this->module_id.'[woocommerce_wf_packinglist_label_size]'=>__('Prints the shipping label in full page. Provisions to create custom sized labels are allowed in premium versions.','print-invoices-packing-slip-labels-for-woocommerce'),
			$this->module_id.'[woocommerce_wf_packinglist_footer_sl]'=>__('Enable to display footer content in the document.','print-invoices-packing-slip-labels-for-woocommerce'),
		);
		return $tooltip_arr;
	}

	/**
	*	@since 2.5.0
	*	@since 2.6.9 footer display option moved to separate function
	*/
	public function alter_find_replace($find_replace,$template_type,$order,$box_packing,$order_package)
	{
		if($template_type==$this->module_base)
		{
			$find_replace=$this->toggle_footer($find_replace);
		}
		return $find_replace;
	}

	public function hide_empty_elements($hide_on_empty_fields,$template_type)
	{
		if($template_type==$this->module_base)
		{
			$hide_on_empty_fields[]='wfte_qrcode';
			$hide_on_empty_fields[]='wfte_box_name';
			$hide_on_empty_fields[]='wfte_ship_date';
			$hide_on_empty_fields[]='wfte_weight';
			$hide_on_empty_fields[]='wfte_barcode';
		}
		return $hide_on_empty_fields;
	}

	/**
	 *  Items needed to be converted to HTML for print
	 */
	public function generate_template_html($find_replace,$html,$template_type,$order,$box_packing=null,$order_package=null)
	{
		if($template_type==$this->module_base)
		{	
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_shipping_address($find_replace,$template_type,$order);					
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::package_doc_items($find_replace,$template_type,$order,$box_packing,$order_package);	
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_other_data($find_replace,$template_type,$html,$order);		
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_order_data($find_replace,$template_type,$html,$order);		
			$find_replace=Wf_Woocommerce_Packing_List_CustomizerLib::set_extra_fields($find_replace,$template_type,$html,$order);
		}
		return $find_replace;
	}

	public function default_settings($settings,$base_id)
	{
		if($base_id==$this->module_id)
		{
			return array(
				'woocommerce_wf_packinglist_label_size'=>2, //full page
				'woocommerce_wf_enable_multiple_shipping_label'=>'Yes',
				'woocommerce_wf_packinglist_footer_sl'=>'No',
				'wf_shipping_label_column_number'=>1,
				'wf_'.$this->module_base.'_contactno_email'=>array('contact_number','email'),
			);
		}else
		{
			return $settings;
		}
	}

	public function add_bulk_print_buttons($actions)
	{
		$actions['print_shippinglabel']=__('Print Shipping Label','print-invoices-packing-slip-labels-for-woocommerce');
		return $actions;
	}
	public function add_print_buttons($html,$order,$order_id)
	{
		$this->generate_print_button_data($order,$order_id,"list_page");
		return $html;
	}
	private function generate_print_button_data($order,$order_id,$button_location="detail_page")
	{
		$icon_url=plugin_dir_url(__FILE__).'/assets/images/shippinglabel-icon.png';
		$label_txt=__('Print Shipping Label','print-invoices-packing-slip-labels-for-woocommerce');
		Wf_Woocommerce_Packing_List_Admin::generate_print_button_data($order,$order_id,'print_shippinglabel',$label_txt,$icon_url,0,$button_location);
	}
	public function add_metabox_data($html,$order,$order_id)
	{
		$this->generate_print_button_data($order,$order_id);
		return $html;
	}
	
	
	/* 
	* Print_window for shippinglabel
	* @param $orders : order ids
	*/    
    public function print_it($order_ids,$action) 
    {
    	if($action=='print_shippinglabel')
    	{   
    		if(!is_array($order_ids))
    		{
    			return;
    		}   
	        if(!is_null($this->customizer))
	        {
	        	$pdf_name=$this->customizer->generate_pdf_name($this->module_base,$order_ids);

	        	//add custom size css here.
	        	if(Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_packinglist_label_size',$this->module_id)==1) 
	        	{
	        		$this->customizer->custom_css.='
	        		.wfte_custom_shipping_size{
	        			width:'.Wf_Woocommerce_Packing_List::get_option('wf_custom_label_size_width',$this->module_id).'in !important;
	        			min-height:'.Wf_Woocommerce_Packing_List::get_option('wf_custom_label_size_height',$this->module_id).'in !important;
	        		}
	        		.wfte_main{ display:inline-block;}
	        		';
	        	}
	        	//RTL enabled
	        	if(Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_add_rtl_support')=='Yes')
	        	{
	        		$this->customizer->custom_css.='';
	        	}
	        	$html=$this->generate_order_template($order_ids,$pdf_name);
	        	echo $html;
	        }
	        exit();
    	}
    }
    public function generate_order_template($orders,$page_title)
    {
    	if(Wf_Woocommerce_Packing_List::is_from_address_available()===false) 
    	{
    		wp_die(__("Please add shipping from address in the plugin's general settings.",'print-invoices-packing-slip-labels-for-woocommerce'), "", array());
        }

    	$template_type=$this->module_base;
    	//taking active template html
    	$html=$this->customizer->get_template_html($template_type);
    	$style_blocks=$this->customizer->get_style_blocks($html);
    	$html=$this->customizer->remove_style_blocks($html,$style_blocks);
    	$out='<style type="text/css">
    	.wfte_main{ margin:5px;}
    	div{ page-break-inside:avoid;}
    	</style>';
    	$out_arr=array();
    	if($html!="")
    	{
    		$is_single_page_print=Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_enable_multiple_shipping_label',$this->module_id);
    		$label_column_number=Wf_Woocommerce_Packing_List::get_option('wf_shipping_label_column_number',$this->module_id);
			if((int) $label_column_number!=$label_column_number || (int) $label_column_number<=0)
			{
                $label_column_number=4;
            }

            //box packing
    		if (!class_exists('Wf_Woocommerce_Packing_List_Box_packing')) {
		        include_once WF_PKLIST_PLUGIN_PATH.'includes/class-wf-woocommerce-packing-list-box_packing.php';
		    }
	        $box_packing=new Wf_Woocommerce_Packing_List_Box_packing();
	        $order_pack_inc=0;
	        if($is_single_page_print=='Yes') //when paper size is not fit to handle labels, then shrink it or keep dimension, Default: shrink
			{
				$keep_label_dimension=false;
				$keep_label_dimension=apply_filters('wf_pklist_label_keep_dimension',$keep_label_dimension,$template_type);
			}
	        foreach ($orders as $order_id)
	        {
	        	$order = ( WC()->version < '2.7.0' ) ? new WC_Order($order_id) : new wf_order($order_id);
				$order_packages=null;
				$order_packages=$box_packing->create_order_package($order, $template_type);
				$number_of_order_package=count($order_packages);
				if(!empty($order_packages)) 
				{
					foreach ($order_packages as $order_package_id => $order_package)
					{
						if($is_single_page_print=='Yes')
						{
							if(($order_pack_inc%$label_column_number)==0)
							{
								if($order_pack_inc>0) //not starting of loop
								{
									$out.='</div>'; 
								}
								$flex_wrap=$keep_label_dimension ? 'wrap' : 'nowrap';
								$out.='<div style="align-items:start; display:flex; flex-direction:row; flex-wrap:'.$flex_wrap.'; align-content:flex-start; align-items:stretch;">'; //comment this line to give preference to label size
							}
						}
						$order_pack_inc++;
						$order=( WC()->version < '2.7.0' ) ? new WC_Order($order_id) : new wf_order($order_id);						
						if($is_single_page_print=='No')
						{
							$out_arr[]=$this->customizer->generate_template_html($html,$template_type,$order,$box_packing,$order_package);
						}else
						{
							$out.=$this->customizer->generate_template_html($html,$template_type,$order,$box_packing,$order_package);	
						}						
					}
					$document_created = Wf_Woocommerce_Packing_List_Admin::created_document_count($order_id,$template_type);
				}else
				{
					wp_die(__("Unable to print Packing slip. Please check the items in the order.",'print-invoices-packing-slip-labels-for-woocommerce'), "", array());
				}
			}
			if($is_single_page_print=='Yes')
			{
				if($order_pack_inc>0) //items exists
				{
					$out.='</div>';
				}
			}else
			{
				$out=implode('<p class="pagebreak"></p>',$out_arr).'<p class="no-page-break">';
			}
			$out=$this->customizer->append_style_blocks($out,$style_blocks);
			//adding header and footer
			$out=$this->customizer->append_header_and_footer_html($out,$template_type,$page_title);
    	}
    	return $out;
    }
}
new Wf_Woocommerce_Packing_List_Shippinglabel();