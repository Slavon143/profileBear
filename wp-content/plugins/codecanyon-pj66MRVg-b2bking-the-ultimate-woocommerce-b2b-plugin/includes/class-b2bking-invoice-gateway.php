<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class B2BKing_Invoice_Gateway extends WC_Gateway_COD {

    /**
     * Setup general properties for the gateway.
     */
    protected function setup_properties() {
        $this->id                 = 'b2bking-invoice-gateway';
        $this->icon               = apply_filters( 'b2bking_invoice_gateway_icon', '' );
        $this->method_title       = esc_html__( 'Invoice Payment', 'b2bking' );
        $this->method_description = esc_html__( 'Allows invoice payments. Website admin will manually invoice the customer.', 'b2bking' );
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
                'label'       => esc_html__( 'Enable invoice payments', 'b2bking' ),
                'type'        => 'checkbox',
                'description' => '',
                'default'     => 'no',
            ),
            'title' => array(
                'title'       => esc_html__( 'Title', 'b2bking' ),
                'type'        => 'text',
                'description' => esc_html__( 'This controls the title which the user sees during checkout.', 'b2bking' ),
                'default'     => esc_html__( 'Invoice Payment', 'b2bking' ),
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => esc_html__( 'Description', 'b2bking' ),
                'type'        => 'textarea',
                'description' => esc_html__( 'Payment method description that the customer will see on your website.', 'b2bking' ),
                'default'     => esc_html__( 'Thank you for ordering, you will soon receive an invoice.', 'b2bking' ),
                'desc_tip'    => true,
            ),
            'instructions' => array(
                'title'       => esc_html__( 'Instructions', 'b2bking' ),
                'type'        => 'textarea',
                'description' => esc_html__( 'Instructions that will be added to the thank you page.', 'b2bking' ),
                'default'     => esc_html__( 'Thank you for ordering, you will soon receive an invoice.', 'b2bking' ),
                'desc_tip'    => true,
            ),
            'enable_for_methods' => array(
                'title'             => esc_html__( 'Enable for shipping methods', 'b2bking' ),
                'type'              => 'multiselect',
                'class'             => 'wc-enhanced-select',
                'css'               => 'width: 400px;',
                'default'           => '',
                'description'       => esc_html__( 'If invoice upon delivery is only available for certain methods, set it up here. Leave blank to enable for all methods.', 'b2bking' ),
                'options'           => $shipping_methods,
                'desc_tip'          => true,
                'custom_attributes' => array(
                    'data-placeholder' => esc_html__( 'Select shipping methods', 'b2bking' ),
                ),
            ),
            'enable_for_virtual' => array(
                'title'             => esc_html__( 'Accept for virtual orders', 'b2bking' ),
                'label'             => esc_html__( 'Accept invoice if the order is virtual', 'b2bking' ),
                'type'              => 'checkbox',
                'default'           => 'yes',
            ),
       );
    }

    function process_payment( $order_id ) {
      $order = wc_get_order( $order_id );

      if ( $order->get_total() > 0 ) {
          // Mark as processing or on-hold (payment won't be taken until delivery).
          $order->update_status( apply_filters( 'b2bking_invoice_payment_order_status', 'on-hold', $order ), esc_html__( 'Awaiting invoice payment.', 'b2bking' ) );
      } else {
          $order->payment_complete();
      }

      // Remove cart.
      WC()->cart->empty_cart();

      // Return thankyou redirect.
      return array(
          'result'   => 'success',
          'redirect' => $this->get_return_url( $order ),
      );

    }
}