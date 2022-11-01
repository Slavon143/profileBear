<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class B2BKing_Purchase_Order_Gateway extends WC_Payment_Gateway {

	public $token;

	public function __construct () {
		$this->token 			= 'b2bking-purchase-order-gateway';
		$this->plugin_url 		= plugin_dir_url( __FILE__ );
		$this->plugin_path 		= plugin_dir_path( __FILE__ );

		$this->id                 = 'B2BKing_Purchase_Order_Gateway';
		$this->method_title       = __( 'Purchase Order', 'b2bking' );
        $this->method_description = __( 'Allows customers to enter their purchase order number at checkout. This number will be manually provided to the customer.' );
		$this->has_fields         = true;

		$this->init_form_fields();
		$this->init_settings();

		$this->title = $this->settings['title'];
		$this->description = $this->settings['description'];
		$this->instructions = $this->settings['instructions'];


		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thank_you' ) );
	}

    /**
	 * Register the gateway's fields.
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function init_form_fields () {
	   $this->form_fields = array(
	            'enabled' => array(
	                'title' => __( 'Enable/Disable', 'b2bking' ),
	                'type' => 'checkbox',
	                'label' => __( 'Enable Purchase Orders.', 'b2bking' ),
	                'default' => 'no' ),
	            'title' => array(
	                'title' => __( 'Title:', 'b2bking' ),
	                'type'=> 'text',
	                'description' => __( 'This controls the title which the user sees during checkout.', 'b2bking' ),
	                'default' => __( 'Purchase Order', 'b2bking' ) ),
	            'description' => array(
	                'title' => __( 'Description:', 'b2bking' ),
	                'type' => 'textarea',
	                'description' => __( 'This controls the description which the user sees during checkout.', 'b2bking' ),
	                'default' => __( 'Please add your P.O. number below.', 'b2bking' ) ),
				 'instructions' => array(
	                'title' => __('Thank You note:', 'b2bking'),
	                'type' => 'textarea',
	                'instructions' => __( 'Instructions that will be added to the thank you page.', 'b2bking' ),
	                'default' => '' )

	        );
	}


	public function admin_options () {
		echo '<h3>'.__( 'Purchase Order Payment Gateway', 'b2bking' ) . '</h3>';
		echo '<table class="form-table">';
		// Generate the HTML For the settings form.
		$this->generate_settings_html();
		echo '</table>';
	}


    public function payment_fields () {
        if( $this->description ) echo wpautop( wptexturize( $this->description ) );

        // In case of an AJAX refresh of the page, check the form post data to see if we can repopulate an previously entered PO
		$po_number = '';
		if ( isset( $_REQUEST[ 'post_data' ] ) ) {
			parse_str( $_REQUEST[ 'post_data' ], $post_data );
	        if ( isset( $post_data[ 'po_number_field' ] ) ) {
				$po_number = $post_data[ 'po_number_field' ];
	        }
		}
?>
		<fieldset>
			<p class="form-row form-row-first">
				<label for="poorder"><?php _e( 'Purchase Order', 'b2bking' ); ?> <span class="required">*</span></label>
				<input type="text" class="input-text" value="<?php echo esc_attr( $po_number ); ?>" id="po_number_field" name="po_number_field" />
			</p>
		</fieldset>
<?php
    }

	public function process_payment( $order_id ) {
		$order = new WC_Order( $order_id );

		$poorder = $this->get_post( 'po_number_field' );

		if ( isset( $poorder ) ) {
			$order->update_meta_data( '_po_number', esc_attr( $poorder ) );
			$order->set_transaction_id( esc_attr( $poorder ) );
			$order->save();
		}

		$order->update_status( apply_filters( 'b2bking_purchase_order_status', 'on-hold', $order ), esc_html__( 'Waiting to be processed', 'b2bking' ) );


		// Reduce stock levels
		if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
			$order->reduce_order_stock();
		} else {
			wc_reduce_stock_levels( $order->get_id() );
		}

		// Remove cart
		WC()->cart->empty_cart();

		// Return thankyou redirect
		return array(
		'result' 	=> 'success',
		'redirect'	=> $this->get_return_url( $order )
		);
	}

	public function thank_you () {
        echo $this->instructions != '' ? wpautop( $this->instructions ) : '';
    }

	private function get_post ( $name ) {
		if( isset( $_POST[$name] ) ) {
			return $_POST[$name];
		} else {
			return NULL;
		}
	}

	public function validate_fields () {
		$poorder = $this->get_post( 'po_number_field' );
		if( ! $poorder ) {
			if ( function_exists ( 'wc_add_notice' ) ) {
				// Replace deprecated $woocommerce_add_error() function.
				wc_add_notice ( __ ( 'Please enter your PO Number.', 'b2bking' ), 'error' );
			} else {
				WC()->add_error( __( 'Please enter your PO Number.', 'b2bking' ) );
			}
			return false;
		} else {
			return true;
		}
	}

}
