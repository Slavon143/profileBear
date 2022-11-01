<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class B2bking_New_Offer_Email extends WC_Email {

    public function __construct() {

        // set ID, this simply needs to be a unique name
        $this->id = 'b2bking_new_offer_email';

        // this is the title in WooCommerce Email settings
        $this->title = esc_html__('You received a new offer!', 'b2bking');

        // this is the description in WooCommerce email settings
        $this->description = esc_html__('This email is sent when an offer is emailed to customers', 'b2bking');

        // these are the default heading and subject lines that can be overridden using the settings
        $this->heading = esc_html__('You received a new offer!', 'b2bking');
        $this->subject = esc_html__('You received a new offer!', 'b2bking');

        $this->template_base  = B2BKING_DIR . 'includes/emails/templates/';
        $this->template_html  = 'new-offer-email-template.php';
        $this->template_plain =  'plain-new-offer-email-template.php';
        
        // Call parent constructor to load any other defaults not explicity defined here
        parent::__construct();

        add_action( 'b2bking_new_offer_notification', array( $this, 'trigger'), 10, 4 );

    }

    public function trigger($email_address, $message,  $offerid, $offerlink) {

        $this->recipient = $email_address;
        $this->message = $message;
        $this->offerid = $offerid;
        $this->offerlink = $offerlink;

        if ( ! $this->is_enabled() || ! $this->get_recipient() ){
           return;
        }
        
        do_action('wpml_switch_language_for_email', $email_address);
        $this->heading = esc_html__('You received a new offer!', 'b2bking');
        $this->subject = esc_html__('You received a new offer!', 'b2bking');
        $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
        do_action('wpml_restore_language_from_email');
     
    }

    public function get_content_html() {
        ob_start();
        if (method_exists($this, 'get_additional_content')){
            $additional_content_checked = $this->get_additional_content();
        } else {
            $additional_content_checked = false;
        }
        wc_get_template( $this->template_html, array(
            'email_heading'      => $this->get_heading(),
            'additional_content' => $additional_content_checked,
            'message'            => $this->message,
            'offerid'     => $this->offerid,
            'offerlink'     => $this->offerlink,
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
            'message'            => $this->message,
            'offerid'     => $this->offerid,
            'offerlink'     => $this->offerlink,
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
return new B2bking_New_Offer_Email();