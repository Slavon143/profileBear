<?php
/**
 * Checkout review order block
 *
 * This template can be overridden by copying it to yourtheme/svea-checkout-for-woocommerce/svea-checkout.php.
 *
 * We will in some rare cases update this file. For this reason, it is important that you keep a look at the version-number and implement the new changes in your theme.
 * If you do not keep this file updated, there is no guarantee that the plugin will work as intended.
 *
 * @author  The Generation
 * @package Svea_Checkout_For_WooCommerce/Templates
 * @version 1.5.0
 */

if( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

wc_print_notices();

// Get checkout object
$checkout = WC()->checkout();

// If checkout registration is disabled and not logged in, the user cannot checkout
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) );
	return;
}

do_action( 'woocommerce_sco_before_checkout_page' ); ?>

<section class="wc-svea-checkout-page" data-sco-order-id="<?php echo isset( $svea_checkout_module['sco_order_id'] ) ? $svea_checkout_module['sco_order_id'] : false; ?>">
    <div class="wc-svea-checkout-page-inner">
        
        <?php do_action( 'woocommerce_sco_before_order_details' ); ?>
        
        <div class="wc-svea-checkout-order-details">

	        <?php do_action( 'woocommerce_before_checkout_form', $checkout ); ?>

            <?php do_action( 'woocommerce_sco_before_co_form' ); ?>
            
            <form class="wc-svea-checkout-form">
                <?php

                // Billing country selector
                woocommerce_form_field(
                    'billing_country',
                    array(
                        'label'       => __( 'Country', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
                        'description' => '',
                        'required'    => true,
                        'type'        => 'country',
                    ),
                    WC()->customer->get_billing_country()
                );

                // Billing postcode field

                ?>
                <input id="billing_postcode" type="hidden" name="billing_postcode" value="<?php echo WC()->customer->get_billing_postcode(); ?>" />

                <?php do_action( 'woocommerce_sco_before_review_order_table' ); ?>
                
                <?php

                $review_order_template = locate_template( 'svea-checkout-for-woocommerce/checkout/review-order.php' );

                if( $review_order_template == '' ) {
                    $review_order_template = WC_SVEA_CHECKOUT_DIR . '/templates/checkout/review-order.php';
                }

                include( $review_order_template );

                do_action( 'woocommerce_sco_after_review_order_table' );

                do_action( 'woocommerce_sco_before_notes_field' ); ?>
                
                <div class="wc-svea-checkout-notes-field">
                    <?php

                    $notes_field = array(
                        'type'        => 'textarea',
                        'label'       => __( 'Order notes', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
                        'placeholder' => esc_attr__( 'Notes about your order, e.g. special notes for delivery.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
                        'class'       => array( 'notes' ),
                        'id'          => 'order_comments',
                        'default'     => isset( $customer_note ) ? $customer_note : '',
                    ); ?>
                    
                    <?php do_action( 'woocommerce_sco_before_notes_form' ); ?>
                    
                    <div class="woocommerce"><?php woocommerce_form_field( 'order_comments', $notes_field ); ?><form></div>
                    
                    <?php do_action( 'woocommerce_sco_after_notes_form' ); ?>
                    
                </div>

                <?php do_action( 'woocommerce_sco_after_notes_field' ); ?>

                <?php if ( ! is_user_logged_in() && WC()->checkout->enable_signup && WC()->checkout->enable_guest_checkout ) : ?>

	                <?php do_action( 'woocommerce_sco_before_login_field' ); ?>

                    <div class="wc-svea-checkout-login-field">

                        <p class="form-row form-row-wide create-account">
                            <?php do_action( 'woocommerce_sco_before_login_input' ); ?>
                            <input class="input-checkbox" id="createaccount" <?php checked( ( isset( $should_create_account ) && $should_create_account ) || ( true === WC()->checkout->get_value( 'createaccount' ) || ( true === apply_filters( 'woocommerce_create_account_default_checked', false ) ) ), true ) ?> type="checkbox" name="createaccount" value="1" /> <label for="createaccount" class="checkbox"><?php _e( 'Create an account?', 'woocommerce' ); ?></label>
                            <?php do_action( 'woocommerce_sco_after_login_input' ); ?>
                        </p>

                    </div>

	                <?php do_action( 'woocommerce_sco_after_login_field' ); ?>

                <?php endif; ?>

	            <?php do_action( 'woocommerce_after_checkout_billing_form', $checkout ); ?>
                
            </form>
                        
            <?php do_action( 'woocommerce_sco_after_co_form' ); ?>

	        <?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
                        
        </div>
        
        <?php do_action( 'woocommerce_sco_before_sco_module' ); ?>
        
        <div class="wc-svea-checkout-checkout-module">
            <?php echo $svea_checkout_module['snippet']; ?>
        </div>
        
        <?php do_action( 'woocommerce_sco_after_sco_module' ); ?>

    </div>
    
</section>

<?php do_action( 'woocommerce_sco_after_checkout_page' );
