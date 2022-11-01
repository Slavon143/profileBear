<?php
/**
 * Free VS Pro Comparison
 *
 * @link       
 * @since 2.7.4    
 *
 * @package  Wf_Woocommerce_Packing_List  
 */
if (!defined('ABSPATH')) {
    exit;
}

class Wf_Woocommerce_Packing_List_Freevspro
{
	public $module_id='';
	public static $module_id_static='';
	public $module_base='freevspro';
	public function __construct()
	{
		$this->module_id=Wf_Woocommerce_Packing_List::get_module_id($this->module_base);
		self::$module_id_static=$this->module_id;

		/** 
		*	ajax hook to show other Webtoffee plugins 
		*/
		add_action('wp_ajax_wt_pklist_wt_other_pluigns', array($this, 'wt_other_pluigns'));

		/*
		*	Init
		*/
		add_action('admin_init', array($this, 'init'));
	}

	/**
	*	Ajax function to show WT other free plugins
	*/
	public function wt_other_pluigns()
	{
		if(!Wf_Woocommerce_Packing_List_Admin::check_write_access()) 
    	{
    		exit();
    	}

		$other_plugins_arr=array(
		    array(
		    	'key'=>'decorator-woocommerce-email-customizer',
		    	'title'=>__('Decorator – WooCommerce Email Customizer', 'print-invoices-packing-slip-labels-for-woocommerce'),
		    	'description'=>__('Customize your WooCommerce emails now and stand out from the crowd!', 'print-invoices-packing-slip-labels-for-woocommerce'),
		    	'file'=>'decorator.php'
		    ),
		    array(
		    	'key'=>'cookie-law-info',
		    	'title'=>__('GDPR Cookie Consent (CCPA Ready)', 'print-invoices-packing-slip-labels-for-woocommerce'),
		    	'description'=>__('This plugin will assist you in making your website GDPR (RGPD, DSVGO) compliant.', 'print-invoices-packing-slip-labels-for-woocommerce'),
		    	'icon'=>'icon-256x256.png',
		    ),
		    array(
		    	'key'=>'users-customers-import-export-for-wp-woocommerce',
		    	'title'=>__('Import Export WordPress Users and WooCommerce Customers', 'print-invoices-packing-slip-labels-for-woocommerce'),
		    	'description'=>__('This plugin allows you to import and export WordPress users and WooCommerce customers quickly and easily.', 'print-invoices-packing-slip-labels-for-woocommerce'),
		    ),
		    array(
		    	'key'=>'order-xml-file-export-import-for-woocommerce',
		    	'title'=>__('Order XML File Export Import for WooCommerce', 'print-invoices-packing-slip-labels-for-woocommerce'),
		    	'description'=>__('The Order XML File Export Import Plugin for WooCommerce will export your WooCommerce orders in XML format.', 'print-invoices-packing-slip-labels-for-woocommerce'),
		    ),
		    array(
		    	'key'=>'wt-woocommerce-related-products',
		    	'title'=>__('Related Products for WooCommerce', 'print-invoices-packing-slip-labels-for-woocommerce'),
		    	'description'=>__('This plugin allows you to choose related products for a particular product.', 'print-invoices-packing-slip-labels-for-woocommerce'),
		    	'file'=>'custom-related-products.php',
		    	'icon'=>'icon-256x256.png',
		    ),
		    array(
		    	'key'=>'wt-woocommerce-sequential-order-numbers',
		    	'title'=>__('Sequential Order Number for WooCommerce', 'print-invoices-packing-slip-labels-for-woocommerce'),
		    	'description'=>__('Using this plugin, you will always get sequential order number for woocommerce.', 'print-invoices-packing-slip-labels-for-woocommerce'),
		    	'file'=>'wt-advanced-order-number.php'
		    ),
		    array(
		    	'key'=>'wt-smart-coupons-for-woocommerce',
		    	'title'=>__('Smart Coupons for WooCommerce', 'print-invoices-packing-slip-labels-for-woocommerce'), 
		    	'description'=>__('This plugin provides additional features with default WooCommerce coupons to get more conversions.', 'print-invoices-packing-slip-labels-for-woocommerce'), 
		    	'file'=>'wt-smart-coupon.php',
		    	'icon'=>'icon-256x256.png',
		    ),
		    array(
		    	'key'=>'webtoffee-product-feed',
		    	'title'=>__('WebToffee Product Feed for Facebook', 'print-invoices-packing-slip-labels-for-woocommerce'),
		    	'description'=>__('This plugin allows you to integrate your WooCommerce store with Facebook in a few steps by syncing your store’s products with it.', 'print-invoices-packing-slip-labels-for-woocommerce'),
		    	'icon'=>'icon-128x128.jpg',
		    ),
		    array(
		    	'key'=>'wp-migration-duplicator',
		    	'title'=>__('WordPress Migration & Duplicator', 'print-invoices-packing-slip-labels-for-woocommerce'),
		    	'description'=>__('This plugin exports your WordPress website media files, plugins and themes including the database with a single click.', 'print-invoices-packing-slip-labels-for-woocommerce'),
		    	'icon'=>'icon-128x128.jpg',
		    ),
		    array(
		    	'key'=>'express-checkout-paypal-payment-gateway-for-woocommerce',
		    	'title'=>__('PayPal Express Checkout Payment Gateway for WooCommerce', 'print-invoices-packing-slip-labels-for-woocommerce'),
		    	'description'=>__('With this plugin, your customer can use their credit cards or PayPal Money to make order from cart page itself.', 'print-invoices-packing-slip-labels-for-woocommerce'),
		    	'icon'=>'icon-128x128.jpg',
		    ),
		);

		shuffle($other_plugins_arr);
		
		$mpdf_arr=array(
	    	'key'=>'mpdf-addon-for-pdf-invoices',
	    	'title'=>__('mPDF add-on for PDF Invoices, Packing Slips, Delivery Notes & Shipping Labels', 'print-invoices-packing-slip-labels-for-woocommerce'),
	    	'description'=>__('Add-on to support RTL and Unicode languages seamlessly in PDF documents.', 'print-invoices-packing-slip-labels-for-woocommerce'),
	    	'file'=>'wt-woocommerce-packing-list-mpdf.php',
		);

		/* mPDF as first item */
		$other_plugins_arr=array_merge(array($mpdf_arr), $other_plugins_arr);

		$plugin_count=0;
		ob_start();		
		?>
		<div class="wt_pklist_other_plugins_hd"><?php _e('OTHER FREE SOLUTIONS FROM WEBTOFFEE', 'print-invoices-packing-slip-labels-for-woocommerce');?></div>
		<?php
		foreach($other_plugins_arr as $plugin_data)
		{
			if($plugin_count>=5) //maximum 3 plugins
			{
				break;
			}
			$plugin_key=$plugin_data['key'];
			$plugin_file=WP_PLUGIN_DIR.'/'.$plugin_key.'/'.(isset($plugin_data['file']) ? $plugin_data['file'] : $plugin_key.'.php');
			if(!file_exists($plugin_file)) //plugin not installed
			{
				$plugin_count++;
				$plugin_title=$plugin_data['title'];
				$plugin_icon=isset($plugin_data['icon']) ? $plugin_data['icon'] : 'icon-128x128.png';
				?>
				<div class="wt_pklist_other_plugin_box">
		            <div class="wt_pklist_other_plugin_hd">
		                <?php echo $plugin_title;?>
		            </div>
		            <div class="wt_pklist_other_plugin_con">
		                <?php echo $plugin_data['description'];?>
		            </div>
		            <div class="wt_pklist_other_plugin_foot">
		                <a href="https://wordpress.org/plugins/<?php echo $plugin_key;?>/" target="_blank" class="wt_pklist_other_plugin_foot_install_btn"><?php _e('Download', 'print-invoices-packing-slip-labels-for-woocommerce');?></a>
		            </div>
		        </div>
				<?php
			}
		}
		$html=ob_get_clean();

		if($plugin_count>0)
		{
			echo $html;
		}

		exit();
	}


	/**
	*	Initiate module
	*/
	public function init()
	{
		/**
		*	Add settings tab
		*/
		add_filter('wf_pklist_plugin_settings_tabhead', array( $this, 'settings_tabhead'), 11);
		add_action('wf_pklist_plugin_out_settings_form', array($this, 'out_settings_form'));
		
		add_action('wt_pklist_settings_sidebar_before', array($this, 'settings_sidebar'));
		add_action('wt_pklist_settings_bottom', array($this, 'settings_bottom'));
	}

	/**
	* 	settings page bottom banner
	*	
	*/
	public function settings_bottom($arr)
	{
		$pro_upgarde_features=array(
		    __('Generate and print additional documents (Address Labels, Pick Lists, Proforma Invoices, and Credit Notes).', 'print-invoices-packing-slip-labels-for-woocommerce'),
		    __('Advanced customization options along with code editor.', 'print-invoices-packing-slip-labels-for-woocommerce'),
		    __('Choose from pre-built templates.', 'print-invoices-packing-slip-labels-for-woocommerce'),
		    __('Choose from multiple packaging types for Packing slips.', 'print-invoices-packing-slip-labels-for-woocommerce'), 
		    __('Supports custom sizes for address/shipping labels.', 'print-invoices-packing-slip-labels-for-woocommerce'),  
		    __('Group order items by category in the product table of documents.', 'print-invoices-packing-slip-labels-for-woocommerce'),
		    __('Display individual tax column(s) in the product table.', 'print-invoices-packing-slip-labels-for-woocommerce'),   
		    __('Share Packing Slip PDF and Picklist PDF as separate mail.', 'print-invoices-packing-slip-labels-for-woocommerce'),   
		    __('Sort order items in the product table of documents.', 'print-invoices-packing-slip-labels-for-woocommerce'),   
		    __('Add-on for remote printing (PrintNode).', 'print-invoices-packing-slip-labels-for-woocommerce'),
		    __('Supports checkout, order, product meta fields & product attributes.', 'print-invoices-packing-slip-labels-for-woocommerce'),
		);
		include plugin_dir_path( __FILE__ ).'views/bottom-banner.php';
	}

	/**
	* 	Settings page sidebar
	*	
	*/
	public function settings_sidebar($arr)
	{
		if(isset($_GET['page']) && $_GET['page']==WF_PKLIST_POST_TYPE)
		{
			wp_enqueue_script($this->module_id, plugin_dir_url( __FILE__ ).'assets/js/freevspro.js', array('jquery'), WF_PKLIST_VERSION);
		}
		include plugin_dir_path( __FILE__ ).'views/goto-pro.php';
	}

	/**
	* 	Tab head for admin settings page
	*	
	*/
	public function settings_tabhead($arr)
	{
		$arr[$this->module_base]=__('Free vs. Pro', 'print-invoices-packing-slip-labels-for-woocommerce');
		return $arr;
	}

	/**
	* 
	*	Tab content
	*/
	public function out_settings_form($args)
	{
		$view_file=plugin_dir_path( __FILE__ ).'views/comparison-table.php';
		$params=array();
		Wf_Woocommerce_Packing_List_Admin::envelope_settings_tabcontent($this->module_base, $view_file, '', $params, 0);
	}

}
new Wf_Woocommerce_Packing_List_Freevspro();