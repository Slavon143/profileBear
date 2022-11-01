<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.webtoffee.com/
 * @since      2.5.0
 *
 * @package    Wf_Woocommerce_Packing_List
 * @subpackage Wf_Woocommerce_Packing_List/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      2.5.0
 * @package    Wf_Woocommerce_Packing_List
 * @subpackage Wf_Woocommerce_Packing_List/includes
 * @author     WebToffee <info@webtoffee.com>
 */
class Wf_Woocommerce_Packing_List_Activator {

	/**
     *  Plugin activation hook
     *
     *  @since   2.5.0
     *  @since   2.6.3 Added option to secure directory with htaccess   
     *  @since   2.7.0 Added option to update Store address from Woo
     */
	public static function activate() {
	    global $wpdb;

	    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );    
        include plugin_dir_path(WF_PKLIST_PLUGIN_FILENAME)."admin/modules/migrator/migrator.php";   
        if(is_multisite()) 
        {   
            if(is_network_admin()){
                // Get all blogs in the network and activate plugin on each one
                $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
                foreach($blog_ids as $blog_id ) 
                {
                    self::install_tables_multi_site($blog_id);
                }   
            }else{
                $current_blog_id = get_current_blog_id();
                self::install_tables_multi_site($current_blog_id);
            }
        }
        else 
        {
            self::install_tables();
            self::copy_address_from_woo();
            self::save_plugin_version();
            Wf_Woocommerce_Packing_List_Migrator::migrate();
        }
        self::secure_upload_dir();
        do_action("wt_pklist_activate");
	}

    /**
    *   @since 2.8.4
    *   Fix the issue of inserting the table in all the site when its multisite
    */
    public static function install_tables_multi_site($blog_id){
        switch_to_blog( $blog_id );
        self::install_tables();
        self::copy_address_from_woo();
        self::save_plugin_version();
        Wf_Woocommerce_Packing_List_Migrator::migrate();
        restore_current_blog();
    }

    /**
    *   @since 2.7.0
    *   Update store address from Woo   
    */
    public static function copy_address_from_woo()
    {
        if(class_exists('Wf_Woocommerce_Packing_List'))
        {
            /* all fields are empty. */
            if((Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_packinglist_sender_address_line1')=='' && 
                Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_packinglist_sender_address_line2') == '' && 
                Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_packinglist_sender_city') == '' && 
                Wf_Woocommerce_Packing_List::get_option('wf_country') == '' && 
                Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_packinglist_sender_postalcode') == '')) 
            {
                Wf_Woocommerce_Packing_List::update_option('woocommerce_wf_packinglist_sender_address_line1', get_option('woocommerce_store_address'));
                Wf_Woocommerce_Packing_List::update_option('woocommerce_wf_packinglist_sender_address_line2', get_option('woocommerce_store_address_2'));
                Wf_Woocommerce_Packing_List::update_option('woocommerce_wf_packinglist_sender_city', get_option('woocommerce_store_city'));
                Wf_Woocommerce_Packing_List::update_option('wf_country', get_option('woocommerce_default_country'));
                Wf_Woocommerce_Packing_List::update_option('woocommerce_wf_packinglist_sender_postalcode', get_option('woocommerce_store_postcode'));
            }
        }
    }

    /**
    *   @since 2.6.3
    *   Secure directory with htaccess  
    */
    public static function secure_upload_dir()
    {
        $upload_dir=Wf_Woocommerce_Packing_List::get_temp_dir('path');
        if(!is_dir($upload_dir))
        {
            @mkdir($upload_dir, 0700);
        }

        $files_to_create=array('.htaccess' => 'deny from all', 'index.php'=>'<?php // Silence is golden');
        foreach($files_to_create as $file=>$file_content)
        {
            if(!file_exists($upload_dir.'/'.$file))
            {
                $fh=@fopen($upload_dir.'/'.$file, "w");
                if(is_resource($fh))
                {
                    fwrite($fh,$file_content);
                    fclose($fh);
                }
            }
        }    
    }

	public static function install_tables()
	{
		global $wpdb;
		//install necessary tables
		//creating table for saving template data================
        $search_query = "SHOW TABLES LIKE %s";
        $charset_collate = $wpdb->get_charset_collate();
        //$tb=Wf_Woocommerce_Packing_List::$template_data_tb;
        $tb='wfpklist_template_data';
        $like = '%' . $wpdb->prefix.$tb.'%';
        $table_name = $wpdb->prefix.$tb;
        if(!$wpdb->get_results($wpdb->prepare($search_query, $like), ARRAY_N)) 
        {
            $sql_settings = "CREATE TABLE IF NOT EXISTS `$table_name` (
			  `id_wfpklist_template_data` int(11) NOT NULL AUTO_INCREMENT,
			  `template_name` varchar(200) NOT NULL,
			  `template_html` text NOT NULL,
			  `template_from` varchar(200) NOT NULL,
			  `is_active` int(11) NOT NULL DEFAULT '0',
			  `template_type` varchar(200) NOT NULL,
			  `created_at` int(11) NOT NULL DEFAULT '0',
			  `updated_at` int(11) NOT NULL DEFAULT '0',
			  PRIMARY KEY(`id_wfpklist_template_data`)
			) DEFAULT CHARSET=utf8;";
            dbDelta($sql_settings);
        }
        //creating table for saving template data================
	}

    public static function save_plugin_version(){
        if(false === get_option('wfpklist_basic_version')){
            update_option('wfpklist_basic_version_prev','none');
        }else{
            $prev_version = get_option('wfpklist_basic_version','none');
            update_option('wfpklist_basic_version_prev',$prev_version);
        }
        update_option('wfpklist_basic_version',WF_PKLIST_VERSION);

        if(false === get_option('wt_pklist_installation_date')){
            if(get_option('wt_pklist_start_date')){
                $install_date = get_option('wt_pklist_start_date',time());
            }else{
                $install_date = time();
            }
            update_option('wt_pklist_installation_date',$install_date);
        }

        if(false === get_option('invoice_empty_count')){
            update_option('invoice_empty_count',1);
        }
    }
}
