<?php
if(!class_exists('wf_order') && class_exists('WC_Order')){

	class wf_order extends WC_Order{
		public $id;
		public $shipping_country;
		public $shipping_first_name;
		public $shipping_last_name;
		public $shipping_company;
		public $shipping_address_1;
		public $shipping_address_2;
		public $shipping_city;
		public $shipping_state;
		public $shipping_postcode;
		public $billing_email;
		public $billing_phone;
		public $billing_address_1;
		public $billing_address_2;
		public $billing_city;
		public $billing_postcode;
		public $billing_country;
		public $billing_state;
		public $billing_company;
		public $billing_first_name;
		public $billing_last_name;
		public $order;
		public function __construct( $order ){
			global $woocommerce;
			$this->wc_version = WC()->version;
			$this->set_order( $order );
		}

		public function set_order( $order ){
			if( is_numeric( $order) ){
				parent::__construct( $order );
			}
			elseif( is_object( $order))
			{
				$order_id=$this->get_id_from_order_obj( $order );
				if(wc_get_order($order_id))
				{
					parent::__construct( $this->get_id_from_order_obj( $order ) );
				}
			}
			
			$this->order = $order;
			$this->set_order_properties();
		}

		private function get_id_from_order_obj( $order_obj ){
			return ( $this->wc_version < '2.7.0' ) ? $order_obj->id : $order_obj->get_id();
		}

		public function __call($method_name, $args) {
	    if ('get_shipping_number' == $method_name) {
	        return $this->order->get_meta('_shipping_number', true);
	    }
	    if ('get_shipping_neighborhood' == $method_name) {
	        return $this->order->get_meta('_shipping_neighborhood', true);
	    }
	    return $this->order->$method_name();
	}


		private function set_order_properties(){
			$this->id 					= ( $this->wc_version < '2.7.0' ) ? $this->id : $this->get_id();
			$this->shipping_country 	= ( $this->wc_version < '2.7.0' ) ? $this->shipping_country : $this->get_shipping_country();
			$this->shipping_first_name 	= ( $this->wc_version < '2.7.0' ) ? $this->shipping_first_name : $this->get_shipping_first_name();
			$this->shipping_last_name 	= ( $this->wc_version < '2.7.0' ) ? $this->shipping_last_name : $this->get_shipping_last_name();
			$this->shipping_company 	= ( $this->wc_version < '2.7.0' ) ? $this->shipping_company : $this->get_shipping_company();
			$this->shipping_address_1 	= ( $this->wc_version < '2.7.0' ) ? $this->shipping_address_1 : $this->get_shipping_address_1();
			$this->shipping_address_2 	= ( $this->wc_version < '2.7.0' ) ? $this->shipping_address_2 : $this->get_shipping_address_2();
			$this->shipping_city 		= ( $this->wc_version < '2.7.0' ) ? $this->shipping_city : $this->get_shipping_city();
			$this->shipping_state 		= ( $this->wc_version < '2.7.0' ) ? $this->shipping_state : $this->get_shipping_state();
			$this->shipping_postcode 	= ( $this->wc_version < '2.7.0' ) ? $this->shipping_postcode : $this->get_shipping_postcode();
			$this->billing_email 		= ( $this->wc_version < '2.7.0' ) ? $this->billing_email : $this->get_billing_email();
			$this->billing_phone 		= ( $this->wc_version < '2.7.0' ) ? $this->billing_phone : $this->get_billing_phone();
			$this->billing_address_1 	= ( $this->wc_version < '2.7.0' ) ? $this->billing_address_1 : $this->get_billing_address_1();
			$this->billing_address_2 	= ( $this->wc_version < '2.7.0' ) ? $this->billing_address_1 : $this->get_billing_address_2();
			$this->billing_city 		= ( $this->wc_version < '2.7.0' ) ? $this->billing_city : $this->get_billing_city();
			$this->billing_postcode 	= ( $this->wc_version < '2.7.0' ) ? $this->billing_postcode  : $this->get_billing_postcode();
			$this->billing_country 		= ( $this->wc_version < '2.7.0' ) ? $this->billing_country  : $this->get_billing_country();
			$this->billing_state 		= ( $this->wc_version < '2.7.0' ) ? $this->billing_state  : $this->get_billing_state();
			$this->billing_company 		= ( $this->wc_version < '2.7.0' ) ? $this->billing_company  : $this->get_billing_company();
			$this->billing_first_name 	= ( $this->wc_version < '2.7.0' ) ? $this->billing_first_name  : $this->get_billing_first_name();
			$this->billing_last_name 	= ( $this->wc_version < '2.7.0' ) ? $this->billing_last_name  : $this->get_billing_last_name();
			$this->order_date 			= ( $this->wc_version < '2.7.0' ) ? $this->order_date  : wc_format_datetime( $this->get_date_created() );
			
		}

		public function get_order_currency(){
			return ( $this->wc_version < '2.7.0' ) ? parent::get_order_currency() : $this->get_currency();
		}
	}
}


if( !class_exists('wf_product') ){
	class wf_product{
		public $id;
		public $length;
		public $width;
		public $height;
		public $weight;
		public $variation_id;
		public $obj;
		public function __construct( $item ){
			$this->wc_version 	= WC()->version;
			$this->obj 			= wc_get_product( $item );
			$this->set_item_properties();
		}

		public function __call( $method_name, $args ){
			return $this->obj->$method_name();
		}

		private function set_item_properties(){
			$this->id 			= ( $this->wc_version < '2.7.0' ) ? $this->obj->id : $this->obj->get_id();
			$this->length 		= ( $this->wc_version < '2.7.0' ) ? $this->obj->length : $this->obj->get_length();
			$this->width 		= ( $this->wc_version < '2.7.0' ) ? $this->obj->width : $this->obj->get_width();
			$this->height 		= ( $this->wc_version < '2.7.0' ) ? $this->obj->height : $this->obj->get_height();
			$this->weight 		= ( $this->wc_version < '2.7.0' ) ? $this->obj->weight : $this->obj->get_weight();
			$this->variation_id = ( $this->wc_version < '2.7.0' ) ? $this->obj->variation_id : $this->obj->get_id(); //get_id will always be the variation ID if this is a variation
		}
	}
}
