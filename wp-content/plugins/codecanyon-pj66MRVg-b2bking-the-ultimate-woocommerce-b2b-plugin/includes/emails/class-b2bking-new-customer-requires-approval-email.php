<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class B2bking_New_Customer_Requires_Approval_Email extends WC_Email {

    public function __construct() {

        // set ID, this simply needs to be a unique name
        $this->id = 'b2bking_new_customer_requires_approval_email';

        // this is the title in WooCommerce Email settings
        $this->title = esc_html__('New customer requires approval', 'b2bking');

        // this is the description in WooCommerce email settings
        $this->description = esc_html__('This email is sent to admin when a new customer registers and requires manual approval', 'b2bking');

        // these are the default heading and subject lines that can be overridden using the settings
        $this->heading = esc_html__('New customer requires approval', 'b2bking');
        $this->subject = esc_html__('New customer requires approval', 'b2bking');

        $this->template_base  = B2BKING_DIR . 'includes/emails/templates/';
        $this->template_html  = 'new-customer-requires-approval-email-template.php';
        $this->template_plain =  'plain-new-customer-requires-approval-email-template.php';
        
        // Call parent constructor to load any other defaults not explicity defined here
        parent::__construct();

        // this sets the recipient to the settings defined below in init_form_fields()
        $this->recipient = $this->get_option( 'recipient' );

        // if none was entered, just use the WP admin email as a fallback
        if ( ! $this->recipient ){
            $this->recipient = get_option( 'admin_email' );
        }

        add_action( 'woocommerce_created_customer_notification', array( $this, 'trigger'), 10, 3 );

    }

    public function trigger($customer_id, $data, $password) {
        if ( ! $this->is_enabled() || ! $this->get_recipient() ){
           return;
        }
        // check if account is subaccounts. If subaccount, do not send
        $account_type = get_user_meta($customer_id,'b2bking_account_type', true);
        if ($account_type === 'subaccount'){
            return;
        }
        
        $this->object = new WP_User( $customer_id );
        $this->user_login         = stripslashes( $this->object->user_login );
		$this->user_email         = stripslashes( $this->object->user_email );

        $this->subject = apply_filters('b2bking_requires_approval_email_heading', $this->subject, $this->user_login);
        // check if customer requires manual approval
        $account_approved = get_user_meta($customer_id, 'b2bking_account_approved', true);
        if ($account_approved === 'no'){
            $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
        }
     
    }

    public function get_content_html() {
        ob_start();
        if (method_exists($this, 'get_additional_content')){
            $additional_content_checked = $this->get_additional_content();
        } else {
            $additional_content_checked = false;
        }
        wc_get_template( $this->template_html, array(
            'email_heading'      => apply_filters('b2bking_requires_approval_email_heading', $this->get_heading(), $this->user_login),
            'additional_content' => $additional_content_checked,
            'user_login'         => $this->user_login,
            'email'              => $this,
        ), $this->template_base, $this->template_base  );
        return ob_get_clean();
    }


    public function get_content_plain() {
        ob_start();
        if (method_exists($this, 'get_additional_content')){
            $additional_content_checked = $this->get_additional_content();
        } else {
            $additional_content_checked = false;
        }
        wc_get_template( $this->template_plain, array(
            'email_heading'      => $this->get_heading(),
			'additional_content' => $additional_content_checked,
			'user_login'         => $this->user_login,
			'email'              => $this,
        ), $this->template_base, $this->template_base );
        return ob_get_clean();
    }

    public function init_form_fields() {

        $this->form_fields = array(
            'enabled'    => array(
                'title'   => esc_html__( 'Enable/Disable', 'b2bking' ),
                'type'    => 'checkbox',
                'label'   => esc_html__( 'Enable this email notification', 'b2bking' ),
                'default' => 'yes',
            ),
            'recipient'  => array(
                'title'       => esc_html__('Recipient(s)','b2bking'),
                'type'        => 'text',
                'description' => esc_html__('Enter recipients (comma separated) for this email. Defaults to','b2bking').sprintf( '<code>%s</code>.', esc_attr( get_option( 'admin_email' ) ) ),
                'placeholder' => '',
                'default'     => ''
            ),
            'subject'    => array(
                'title'       => 'Subject',
                'type'        => 'text',
                'description' => esc_html__('This controls the email subject line. Leave blank to use the default subject: ','b2bking').sprintf( '<code>%s</code>.', $this->subject ),
                'placeholder' => '',
                'default'     => ''
            ),
            'heading'    => array(
                'title'       => esc_html__('Email Heading','b2bking'),
                'type'        => 'text',
                'description' => esc_html__('This controls the main heading contained within the email notification. Leave blank to use the default heading: ','b2bking').sprintf( '<code>%s</code>.', $this->heading ),
                'placeholder' => '',
                'default'     => ''
            ),
            'email_type' => array(
                'title'       => esc_html__('Email type','b2bking'),
                'type'        => 'select',
                'description' => esc_html__('Choose which format of email to send.','b2bking'),
                'default'     => 'html',
                'class'       => 'email_type',
                'options'     => array(
                    'plain'     => 'Plain text',
                    'html'      => 'HTML', 'woocommerce',
                    'multipart' => 'Multipart', 'woocommerce',
                )
            ),
            'additional_content' => array(
                'title'       => __( 'Additional content', 'woocommerce' ),
                'description' => __( 'Text to appear below the main email content.', 'woocommerce' ),
                'css'         => 'width:400px; height: 75px;',
                'placeholder' => __( 'N/A', 'woocommerce' ),
                'type'        => 'textarea',
                'default'     => '',
                'desc_tip'    => true,
            ),
        );
    }

}
return new B2bking_New_Customer_Requires_Approval_Email();