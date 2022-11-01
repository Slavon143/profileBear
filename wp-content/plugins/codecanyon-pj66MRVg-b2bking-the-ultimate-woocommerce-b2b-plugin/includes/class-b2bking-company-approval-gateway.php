<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class B2BKing_Approval_Gateway extends WC_Gateway_COD {

    /**
     * Setup general properties for the gateway.
     */
    protected function setup_properties() {
        $this->id                 = 'b2bking-approval-gateway';
        $this->icon               = apply_filters( 'b2bking_approval_gateway_icon', '' );
        $this->method_title       = esc_html__( 'Company Approval Order', 'b2bking' );
        $this->method_description = esc_html__( 'For subaccount orders that will need to be approved by the parent account.', 'b2bking' );
        $this->has_fields         = false;
    }

    /**
     * Initialise Gateway Settings Form Fields.
     */
    public function init_form_fields() {
        $shipping_methods = array();

        foreach ( WC()->shipping()->load_shipping_methods() as $method ) {
            $shipping_methods[ $method->id ] = $method->get_method_title();
        }

        $this->form_fields = array(
            'enabled' => array(
                'title'       => esc_html__( 'Enable/Disable', 'b2bking' ),
                'label'       => esc_html__( 'Enable company approval orders', 'b2bking' ),
                'type'        => 'checkbox',
                'description' => '',
                'default'     => 'no',
            ),
            'title' => array(
                'title'       => esc_html__( 'Title', 'b2bking' ),
                'type'        => 'text',
                'description' => esc_html__( 'This controls the title which the user sees during checkout.', 'b2bking' ),
                'default'     => esc_html__( 'Pending Company Approval', 'b2bking' ),
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => esc_html__( 'Description', 'b2bking' ),
                'type'        => 'textarea',
                'description' => esc_html__( 'Payment method description that the customer will see on your website.', 'b2bking' ),
                'default'     => esc_html__( 'The order will be sent for review and approval.', 'b2bking' ),
                'desc_tip'    => true,
            ),
            'instructions' => array(
                'title'       => esc_html__( 'Instructions', 'b2bking' ),
                'type'        => 'textarea',
                'description' => esc_html__( 'Instructions that will be added to the thank you page.', 'b2bking' ),
                'default'     => esc_html__( 'The order will be sent for review and approval.', 'b2bking' ),
                'desc_tip'    => true,
            ),

       );
    }

    function process_payment( $order_id ) {
      $order = wc_get_order( $order_id );

      if ( $order->get_total() > 0 ) {
          $order->update_status( apply_filters( 'b2bking_company_approval_order_status', 'wc-pcompany', $order ), esc_html__( 'Pending company approval.', 'b2bking' ) );
      } else {
          $order->payment_complete();
      }

      // Remove cart.
      WC()->cart->empty_cart();

      // Fire message to parent account
      $parent_user_id = get_user_meta(get_current_user_id(),'b2bking_account_parent', true);
      $parent_user = new WP_User($parent_user_id);
      $currentuser = wp_get_current_user();
      $currentname = $currentuser->first_name.' '.$currentuser->last_name;
      $message = apply_filters('b2bking_approval_message_parent', 'One of your subaccounts, '.$currentname.', has placed an order that is now pending your review. Click <a href="'.esc_url( wc_get_account_endpoint_url( 'orders' ) ).'">here</a> to view orders.');
      do_action( 'b2bking_new_message', $parent_user->user_email, $message, 'Quoteemail:1', 0 );


      // Return thankyou redirect.
      return array(
          'result'   => 'success',
          'redirect' => $this->get_return_url( $order ),
      );

    }
}