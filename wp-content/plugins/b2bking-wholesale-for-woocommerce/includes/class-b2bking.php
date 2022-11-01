<?php

class B2bkingcore {

	function __construct() {

		// Include dynamic rules code
		require_once ( B2BKINGCORE_DIR . 'public/class-b2bking-dynamic-rules.php' );

		add_action('plugins_loaded', function(){

			// important for correct plugin loading order
			if (class_exists('B2bking')){
				update_option('b2bking_main_active', 'yes');
			} else {
				update_option('b2bking_main_active', 'no');
			}
		});

		// Handle Ajax Requests
		if ( wp_doing_ajax() ){

			// interferes in the product_idct page for some reason with variation loading
			add_action('plugins_loaded', function(){
		
				// Add Fixed Price Rule to AJAX product searches
				// Check if plugin status is B2B OR plugin status is Hybrid and user is B2B user.
				if(isset($_COOKIE['b2bking_userid'])){
					$cookieuserid = sanitize_text_field($_COOKIE['b2bking_userid']);
				} else {
					$cookieuserid = '999999999999';
				}
		
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
			
			});

    		// Approve and Reject users
    		add_action( 'wp_ajax_b2bkingapproveuser', array($this, 'b2bkingapproveuser') );
    		add_action( 'wp_ajax_nopriv_b2bkingapproveuser', array($this, 'b2bkingapproveuser') );
    		add_action( 'wp_ajax_b2bkingrejectuser', array($this, 'b2bkingrejectuser') );
    		add_action( 'wp_ajax_nopriv_b2bkingrejectuser', array($this, 'b2bkingrejectuser') );
    	
    		// Dismiss "activate woocommerce" admin notice permanently
    		add_action( 'wp_ajax_b2bking_dismiss_activate_woocommerce_admin_notice', array($this, 'b2bking_dismiss_activate_woocommerce_admin_notice') );

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
    		add_action( 'wp_ajax_b2bking_dismiss_review_admin_notice', array( $this, 'b2bking_dismiss_review_admin_notice' ) );
    		add_action( 'wp_ajax_b2bking_dismiss_review_admin_notice_temporary', array( $this, 'b2bking_dismiss_review_admin_notice_temporary' ) );

    		// Save Special group settings (b2c and guests) in groups
    		add_action( 'wp_ajax_nopriv_b2bking_b2c_special_group_save_settings', array($this, 'b2bking_b2c_special_group_save_settings') );
    		add_action( 'wp_ajax_b2bking_b2c_special_group_save_settings', array($this, 'b2bking_b2c_special_group_save_settings') );
    		add_action( 'wp_ajax_nopriv_b2bking_logged_out_special_group_save_settings', array($this, 'b2bking_logged_out_special_group_save_settings') );
    		add_action( 'wp_ajax_b2bking_logged_out_special_group_save_settings', array($this, 'b2bking_logged_out_special_group_save_settings') );
    		
    		// Backend Customers Panel
    		add_action( 'wp_ajax_nopriv_b2bking_admin_customers_ajax', array($this, 'b2bking_admin_customers_ajax') );
    		add_action( 'wp_ajax_b2bking_admin_customers_ajax', array($this, 'b2bking_admin_customers_ajax') );

    		// Backend Update User Data
    		add_action( 'wp_ajax_nopriv_b2bkingupdateuserdata', array($this, 'b2bkingupdateuserdata') );
    		add_action( 'wp_ajax_b2bkingupdateuserdata', array($this, 'b2bkingupdateuserdata') );

    		// Get page content function
			add_action( 'wp_ajax_b2bking_get_page_content', array($this, 'b2bking_get_page_content') );
    		add_action( 'wp_ajax_nopriv_b2bking_get_page_content', array($this, 'b2bking_get_page_content') );
    		
		}

		// Add email classes
		add_filter( 'woocommerce_email_classes', array($this, 'b2bking_add_email_classes') );
		// Add extra email actions (account approved finish)
		add_filter( 'woocommerce_email_actions', array($this, 'b2bking_add_email_actions'));

		// Run Admin/Public code 
		if ( is_admin() ) { 
			global $b2bking_admin;
			require_once B2BKINGCORE_DIR . '/admin/class-b2bking-admin.php';
			$b2bking_admin = new B2bkingcore_Admin();
		} else if ( !$this->b2bking_is_login_page() ) {
			global $b2bking_public;
			require_once B2BKINGCORE_DIR . '/public/class-b2bking-public.php';
			$b2bking_public = new B2bkingcore_Public();
		}
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
			B2bkingcore_Admin::b2bking_groups_page_content();
		} else if ($page === 'b2c_users'){
			B2bkingcore_Admin::b2bking_b2c_users_page_content();
		} else if ($page === 'logged_out_users'){
			B2bkingcore_Admin::b2bking_logged_out_users_page_content();
		} else if ($page === 'dashboard'){
			B2bkingcore_Admin::b2bking_dashboard_page_content();
		} else if ($page === 'customers'){
			B2bkingcore_Admin::b2bking_customers_page_content();
		} else if ($page === 'orderform'){
			B2bkingcore_Admin::b2bking_orderform_page_content();
		} else if ($page === 'tieredpricing'){
			B2bkingcore_Admin::b2bking_tieredpricing_page_content();
		} else if ($page === 'businessregistration'){
			B2bkingcore_Admin::b2bking_businessregistration_page_content();
		} else if ($page === 'tools'){
			B2bkingcore_Admin::b2bking_tools_page_content();
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

	function b2bking_get_edit_post_type_page($post_type_input){

		echo B2bkingcore_Admin::get_header_bar();


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

	function b2bking_user_is_in_list($list){
		// get user data
		$user_data_current_user_id = get_current_user_id();
		if (intval($user_data_current_user_id) === 0){
			// check cookies
			if (isset($_COOKIE['b2bking_userid'])){
				$user_data_current_user_id = sanitize_text_field($_COOKIE['b2bking_userid']);
			}
		}
		$user_data_current_user_b2b = get_user_meta($user_data_current_user_id, 'b2bking_b2buser', true);
		$user_data_current_user_group = get_user_meta($user_data_current_user_id, 'b2bking_customergroup', true);
		// checks based on user id, b2b status and group, if it's part of an applicable rules list
		$is_in_list = 'no';
		$list_array = explode(',',$list);
		if (intval($user_data_current_user_id) !== 0){
			if (in_array('everyone_registered', $list_array)){
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

	    $email_classes['B2bkingcore_New_Customer_Email'] = include B2BKINGCORE_DIR .'/includes/emails/class-b2bking-new-customer-email.php';

	    $email_classes['B2bkingcore_New_Message_Email'] = include B2BKINGCORE_DIR .'/includes/emails/class-b2bking-new-message-email.php';

	    $email_classes['B2bkingcore_New_Customer_Requires_Approval_Email'] = include B2BKINGCORE_DIR .'/includes/emails/class-b2bking-new-customer-requires-approval-email.php';

	    $email_classes['B2bkingcore_Your_Account_Approved_Email'] = include B2BKINGCORE_DIR .'/includes/emails/class-b2bking-your-account-approved-email.php';

	    return $email_classes;
	}

	// Add email actions
	function b2bking_add_email_actions( $actions ) {
	    $actions[] = 'b2bking_account_approved_finish';
	    $actions[] = 'b2bking_new_message';
	    return $actions;
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

			$group_name = get_the_title(get_user_meta($user_id, 'b2bking_customergroup', true));
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
			array_push($data['data'],array($name_link, $company_name, $group_name, $account_type, $approval));
			
		}

		echo json_encode($data);
		
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

		// approve account
		update_user_meta($user_id, 'b2bking_account_approved', 'yes');
		// place user in customer group 
		update_user_meta($user_id, 'b2bking_customergroup', $group);
		// add role
		$user_obj = new WP_User($user_id);
		$user_obj->add_role('b2bking_role_'.$group);
		// set user as b2b enabled
		update_user_meta($user_id, 'b2bking_b2buser', 'yes');


		// create action hook to send "account approved" email
		$email_address = sanitize_text_field(get_user_by('id', $user_id)->user_email);
		do_action( 'b2bking_account_approved_finish', $email_address );

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

		// delete account
		wp_delete_user($user_id);

		// check if this function is being run by delete subaccount in the frontend
		if(isset($_POST['issubaccount'])){
			$current_user = get_current_user_id();
			// remove subaccount from user meta
			$subaccounts_number = get_user_meta($current_user, 'b2bking_subaccounts_number', true);
			$subaccounts_number = $subaccounts_number - 1;
			update_user_meta($current_user, 'b2bking_subaccounts_number', sanitize_text_field($subaccounts_number));

			$subaccounts_list = get_user_meta($current_user, 'b2bking_subaccounts_list', true);
			$subaccounts_list = str_replace(','.$user_id,'',$subaccounts_list);
			update_user_meta($current_user, 'b2bking_subaccounts_list', sanitize_text_field($subaccounts_list));
		}

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
				// update user meta if field not empty
				update_user_meta($user_id, 'b2bking_custom_field_'.$field_id, sanitize_text_field($_POST['field_'.$field_id]));
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
	// Individual product pricing functions for AJAX below
	function b2bking_individual_pricing_fixed_price($price, $product){
		
			if (is_user_logged_in()){
				$user_id = get_current_user_id();
		    	$account_type = get_user_meta($user_id,'b2bking_account_type', true);
		    	if ($account_type === 'subaccount'){
		    		// for all intents and purposes set current user as the subaccount parent
		    		$parent_user_id = get_user_meta($user_id, 'b2bking_account_parent', true);
		    		$user_id = $parent_user_id;
		    	}

		    	// check transient to see if the current price has been set already via another function
		    	if (get_transient('b2bking_user_'.$user_id.'_product_'.$product->get_id().'_custom_set_price') === $price){
		    		return $price;
		    	}

		    	$is_b2b_user = get_the_author_meta( 'b2bking_b2buser', $user_id );
				$currentusergroupidnr = get_the_author_meta( 'b2bking_customergroup', $user_id );
				if ($is_b2b_user === 'yes'){
					// Search if there is a specific price set for the user's group
					$b2b_price = get_post_meta($product->get_id(), 'b2bking_regular_product_price_group_'.$currentusergroupidnr, true );
					if (!empty($b2b_price)){
						// ADD WOOCS COMPATIBILITY
			    		if (class_exists('WOOCS')) {
							global $WOOCS;
							$currrent = $WOOCS->current_currency;
							if ($currrent != $WOOCS->default_currency) {
								$currencies = $WOOCS->get_currencies();
								$rate = $currencies[$currrent]['rate'];
								$b2b_price = $b2b_price / ($rate);
							}
						}

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

	public function b2bking_dismiss_review_admin_notice() {
		// Check security nonce.
		if ( ! check_ajax_referer( 'b2bking_notice_security_nonce', 'security' ) ) {
			wp_send_json_error( 'Invalid security token sent.' );
			wp_die();
		}

		update_user_meta( get_current_user_id(), 'b2bking_dismiss_getstarted_notice', 1 );
		update_user_meta( get_current_user_id(), 'b2bking_dismiss_getstarted_notice_time', false);


		echo 'success';
		exit();
	}


	public function b2bking_dismiss_review_admin_notice_temporary() {
		// Check security nonce.
		if ( ! check_ajax_referer( 'b2bking_notice_security_nonce', 'security' ) ) {
			wp_send_json_error( 'Invalid security token sent.' );
			wp_die();
		}

		update_user_meta( get_current_user_id(), 'b2bking_dismiss_getstarted_notice', 1 );
		update_user_meta( get_current_user_id(), 'b2bking_dismiss_getstarted_notice_time', time());

		echo 'success';
		exit();
	}

	function b2bking_individual_pricing_discount_sale_price( $sale_price, $product ){

		if (is_user_logged_in()){
			$user_id = get_current_user_id();
	    	$account_type = get_user_meta($user_id,'b2bking_account_type', true);
	    	if ($account_type === 'subaccount'){
	    		// for all intents and purposes set current user as the subaccount parent
	    		$parent_user_id = get_user_meta($user_id, 'b2bking_account_parent', true);
	    		$user_id = $parent_user_id;
	    	}

	    	$is_b2b_user = get_the_author_meta( 'b2bking_b2buser', $user_id );
			$currentusergroupidnr = get_the_author_meta( 'b2bking_customergroup', $user_id );
			if ($is_b2b_user === 'yes'){
				// Search if there is a specific price set for the user's group
				$b2b_price = get_post_meta($product->get_id(), 'b2bking_sale_product_price_group_'.$currentusergroupidnr, true );
				if (!empty($b2b_price)){
					// ADD WOOCS COMPATIBILITY
		    		if (class_exists('WOOCS')) {
						global $WOOCS;
						$currrent = $WOOCS->current_currency;
						if ($currrent != $WOOCS->default_currency) {
							$currencies = $WOOCS->get_currencies();
							$rate = $currencies[$currrent]['rate'];
							$b2b_price = $b2b_price / ($rate);
						}
					}

					return $b2b_price;
				} else {
					return $sale_price;
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


		if (is_user_logged_in()){
			$user_id = get_current_user_id();
	    	$account_type = get_user_meta($user_id,'b2bking_account_type', true);
	    	if ($account_type === 'subaccount'){
	    		// for all intents and purposes set current user as the subaccount parent
	    		$parent_user_id = get_user_meta($user_id, 'b2bking_account_parent', true);
	    		$user_id = $parent_user_id;
	    	}

	    	$is_b2b_user = get_the_author_meta( 'b2bking_b2buser', $user_id );
			$currentusergroupidnr = get_the_author_meta( 'b2bking_customergroup', $user_id );
			if ($is_b2b_user === 'yes'){
				// Search if there is a specific price set for the user's group
				$b2b_price = get_post_meta($product->get_id(), 'b2bking_sale_product_price_group_'.$currentusergroupidnr, true );
				if (!empty($b2b_price)){

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

	    return $price_html;
	}

	function b2bking_individual_pricing_discount_display_dynamic_price_in_cart($cart){
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

    	$is_b2b_user = get_the_author_meta( 'b2bking_b2buser', $user_id );
		$currentusergroupidnr = get_the_author_meta( 'b2bking_customergroup', $user_id );
		if ($is_b2b_user === 'yes'){
			// Iterate through each cart item
			foreach( $cart->get_cart() as $cart_item ) {
				// Search if there is a specific price set for the user's group
				if (isset($cart_item['variation_id']) && intval($cart_item['variation_id']) !== 0){
					$b2b_price = get_post_meta($cart_item['variation_id'], 'b2bking_sale_product_price_group_'.$currentusergroupidnr, true );
					$product_id_set = $cart_item['variation_id'];
				} else {
					$b2b_price = get_post_meta($cart_item['product_id'], 'b2bking_sale_product_price_group_'.$currentusergroupidnr, true );
					$product_id_set = $cart_item['product_id'];
				}
				
				if (!empty($b2b_price)){
					$cart_item['data']->set_price( $b2b_price );
					set_transient('b2bking_user_'.$user_id.'_product_'.$product_id_set.'_custom_set_price', $b2b_price);
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

    	$is_b2b_user = get_the_author_meta( 'b2bking_b2buser', $user_id );
		$currentusergroupidnr = get_the_author_meta( 'b2bking_customergroup', $user_id );
		if ($is_b2b_user === 'yes'){
			if (isset($cart_item['variation_id']) && intval($cart_item['variation_id']) !== 0){
				$b2b_price = get_post_meta($cart_item['variation_id'], 'b2bking_sale_product_price_group_'.$currentusergroupidnr, true );
				$product_id_set = $cart_item['variation_id'];
			} else {
				$b2b_price = get_post_meta($cart_item['product_id'], 'b2bking_sale_product_price_group_'.$currentusergroupidnr, true );
				$product_id_set = $cart_item['product_id'];
			}

			if (!empty($b2b_price)){

				require_once ( B2BKINGCORE_DIR . 'public/class-b2bking-helper.php' );
				$helper = new B2bkingcore_Helper();
				
				$discount_price = $helper->b2bking_wc_get_price_to_display( wc_get_product($product_id_set), array( 'price' => $cart_item['data']->get_sale_price() ) ); // get sale price
				
				if ($discount_price !== NULL && $discount_price !== ''){
					$price = wc_price($discount_price, 4); 
				}
			} 
		}
		return $price;
	}
}

