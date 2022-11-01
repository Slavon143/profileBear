<?php if( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * WC_Gateway_Svea class.
 *
 * @extends WC_Payment_Gateway
 */
class WC_Gateway_Svea_Checkout extends WC_Payment_Gateway {
	 //Format of the transition for part payment campaigns
	const PART_PAYMENT_TRANSIENT_FORMAT = 'sco_part_pay_campaigns_%s';

	const GATEWAY_ID = 'svea_checkout';

	/**
	 * @var array List of Svea payment methods
	 */
	private $svea_payment_methods;

	/**
	 * @var array List of activated customer types
	 */
	private $customer_types;

	/**
	 * @var string Secret for Sweden
	 */
	private $secret_se;

	/**
	 * @var string Merchant for Sweden
	 */
	private $merchant_id_se;

	/**
	 * @var bool Whether or not testmode for Sweden is activated
	 */
	private $testmode_se;

	/**
	 * @var string Secret for Norway
	 */
	private $secret_no;

	/**
	 * @var string Merchant for Norway
	 */
	private $merchant_id_no;

	/**
	 * @var bool Whether or not testmode for Norway is activated
	 */
	private $testmode_no;

	/**
	 * @var string Secret for Finland
	 */
	private $secret_fi;

	/**
	 * @var string Merchant for Finland
	 */
	private $merchant_id_fi;

	/**
	 * @var bool Whether or not testmode for Finland is activated
	 */
	private $testmode_fi;

	/**
	 * @var string Secret for Denmark
	 */
	private $secret_dk;

	/**
	 * @var string Merchant for Denmark
	 */
	private $merchant_id_dk;

	/**
	 * @var bool Whether or not testmode for Denmark is activated
	 */
	private $testmode_dk;

	/**
	 * @var bool Whether or not to display the product widget
	 */
	public $display_product_widget;

	private static $log_enabled = false;
	private static $log = null;

	/**
	 * Static instance of this class
	 *
	 * @var WC_Gateway_Svea_Checkout
	 */
	private static $instance = null;

	private static $hooks_enabled = false;

	public static function get_instance() {
		if( is_null( self::$instance ) ) {
			self::$instance = new WC_Gateway_Svea_Checkout();
		}

		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		if( is_null( self::$instance ) ) {
			self::$instance = $this;
		}

		$this->id                   = self::GATEWAY_ID;
		$this->method_title         = __( 'Svea Checkout', WC_Svea_Checkout_i18n::TEXT_DOMAIN );
		$this->method_description   = __( 'Svea Checkout provides a fully featured checkout solution that speeds up the checkout process for your customers.', WC_Svea_Checkout_i18n::TEXT_DOMAIN );
		$this->has_fields           = true;
		$this->view_transaction_url = '';
		$this->supports             = array(
			'products',
			'refunds',
		);

		$this->svea_payment_methods = array(
			'INVOICE'           => __( 'Invoice', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
			'PAYMENTPLAN'       => __( 'Payment Plan', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
			'SVEACARDPAY'       => __( 'Card Payment', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
			'SVEACARDPAY_PF'    => __( 'Card Payment', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
			'ACCOUNT'           => __( 'Account Credit', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
			'ACCOUNTCREDIT'     => __( 'Account Credit', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
			'TRUSTLY'           => __( 'Trustly', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
			'BANKAXESS'         => __( 'Direct bank', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
			'DBAKTIAFI'         => __( 'Direct bank', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
			'DBALANDSBANKENFI'  => __( 'Direct bank', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
			'DBDANSKEBANKSE'    => __( 'Direct bank', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
			'DBNORDEAFI'        => __( 'Direct bank', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
			'DBNORDEASE'        => __( 'Direct bank', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
			'DBPOHJOLAFI'       => __( 'Direct bank', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
			'DBSAMPOFI'         => __( 'Direct bank', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
			'DBSEBSE'           => __( 'Direct bank', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
			'DBSEBFTGSE'        => __( 'Direct bank', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
			'DBSHBSE'           => __( 'Direct bank', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
			'DBSPANKKIFI'       => __( 'Direct bank', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
			'DBSWEDBANKSE'      => __( 'Direct bank', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
			'DBTAPIOLAFI'       => __( 'Direct bank', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
			'SWISH' 			=> __( 'Swish', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
			'VIPPS' 			=> __( 'Vipps', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
			'MOBILEPAY' 		=> __( 'Mobilepay', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
		);

		// Load the form fields.
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		// Get setting values.
		$this->enabled          = apply_filters( 'woocommerce_sco_settings_enabled', $this->get_option( 'enabled' ) );
		$this->title            = apply_filters( 'woocommerce_sco_settings_title', __( $this->get_option( 'title' ), WC_Svea_Checkout_i18n::TEXT_DOMAIN ) );
		self::$log_enabled      = $this->get_option( 'log' ) === 'yes';

		$this->customer_types   = apply_filters( 'woocommerce_sco_settings_customer_types', $this->get_option( 'customer_types' ) );

		// Sweden
		$this->secret_se        = apply_filters( 'woocommerce_sco_settings_secret_se', $this->get_option( 'secret_se' ) );
		$this->merchant_id_se   = apply_filters( 'woocommerce_sco_settings_merchant_id_se', $this->get_option( 'merchant_id_se' ) );
		$this->testmode_se      = apply_filters( 'woocommerce_sco_settings_testmode_se', $this->get_option( 'testmode_se' ) );

		// Norway
		$this->secret_no        = apply_filters( 'woocommerce_sco_settings_secret_no', $this->get_option( 'secret_no' ) );
		$this->merchant_id_no   = apply_filters( 'woocommerce_sco_settings_merchant_id_no', $this->get_option( 'merchant_id_no' ) );
		$this->testmode_no      = apply_filters( 'woocommerce_sco_settings_testmode_no', $this->get_option( 'testmode_no' ) );

		// Finland
		$this->secret_fi        = apply_filters( 'woocommerce_sco_settings_secret_fi', $this->get_option( 'secret_fi' ) );
		$this->merchant_id_fi   = apply_filters( 'woocommerce_sco_settings_merchant_id_fi', $this->get_option( 'merchant_id_fi' ) );
		$this->testmode_fi      = apply_filters( 'woocommerce_sco_settings_testmode_fi', $this->get_option( 'testmode_fi' ) );

		// Denmark
		$this->secret_dk        = apply_filters( 'woocommerce_sco_settings_secret_dk', $this->get_option( 'secret_dk' ) );
		$this->merchant_id_dk   = apply_filters( 'woocommerce_sco_settings_merchant_id_dk', $this->get_option( 'merchant_id_dk' ) );
		$this->testmode_dk      = apply_filters( 'woocommerce_sco_settings_testmode_dk', $this->get_option( 'testmode_dk' ) );

		$this->display_product_widget   = apply_filters( 'woocommerce_sco_settings_product_widget', $this->get_option( 'display_product_widget' ) );

		// Prevent duplicate hooks
		if( ! self::$hooks_enabled ) {
			$this->add_hooks();
		}
	}

	public function add_hooks() {
        add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'svea_co_display_extra_admin_order_meta' ), 10, 1 );
        add_action( 'woocommerce_order_details_after_order_table', array( $this, 'svea_co_display_extra_order_meta' ), 11, 1 );
        add_action( 'woocommerce_email_after_order_table', array( $this, 'svea_co_display_extra_admin_order_meta' ), 11, 1 );
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'clear_merchant_validation_cache' ) );
        add_action( 'woocommerce_api_svea_checkout_push', array( $this, 'process_push' ), 10, 1 );
        // add_action( 'admin_notices', array( $this, 'push_notice' ), 10, 1 ); @TODO Add this when pushes have been accumulated

		// Shortcode for part payments on product pages
		add_shortcode( 'svea_checkout_part_payment_widget', [ $this, 'product_part_payment_widget_shortcode' ] );

        // If option 'display_product_widget' is checked in settings, display widget for part payment plans
        if( $this->display_product_widget == 'yes' ) {
            // Get position of the widget from settings
	        $product_widget_position = intval( $this->get_option( 'product_widget_position' ) );

	        // Set a default position
	        if( $product_widget_position <= 0 ) {
		        $product_widget_position = 11;
	        }

	        add_action( 'woocommerce_single_product_summary', array( $this, 'product_part_payment_widget' ), $product_widget_position, 1 );
        }

        self::$hooks_enabled = true;
    }

	/**
     * Display notices if
     *
	 * @return void
	 */
    public function push_notice() {
        if( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $last_push = get_option( 'sco_last_push', false );

        if( ! $last_push ) {
	        $this->print_svea_errors(
		        array(
			        '<strong>' . __( 'If you recently installed or updated Svea Checkout for WooCommerce you can dismiss this message. It will disappear when you receive your first successful order.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ) . '</strong>',
		            '',
		            __( 'No pushes from Svea have been able to reach your store. This may cause orders not to show up in your order list.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
                    __( 'Some known issues:', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
                    sprintf(
                        '<ol><li>%s</li></ol>',
                        implode( '</li><li>', array(
	                        __( 'Maintenance mode plugins', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
                            __( 'Security plugins blocking (firewalls)', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
                            __( 'General plugin conflicts', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
                            __( 'Missing SSL-certificate', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
                        ) )
                    ),
                    sprintf(
                        __( 'If you are still having issues, please contact Svea\'s support on %s', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
                        '<a href="mailto:support-webpay@sveaekonomi.se">support-webpay@sveaekonomi.se</a>'
                    )

                )
	        );
        } else if( time() - $last_push > ( 60 * 60 * 24 * 7 ) ) {
            $this->print_svea_errors(
	            array(
                    __( 'It was over 7 days ago since the last push from Svea was able to reach your store. If have not had any orders in a while, this might be the cause. Otherwise you should dig into this further. This may cause orders not to show up in your order list.', WC_Svea_Checkout_i18n::TEXT_DOMAIN )
                )
            );
        }
    }

	/**
	 * Print errors in admin
	 *
	 * @param array $error_messages
	 * 
	 * @return void
	 */
    private function print_svea_errors( $error_messages ) {
	    printf(
		    '<div class="notice notice-error">%1$s</div>',
		    sprintf(
			    '<p><strong>%s:</strong></p><p>%s</p>',
			    esc_html( __( 'Svea Checkout', WC_Svea_Checkout_i18n::TEXT_DOMAIN ) ),
                implode( '<br />', $error_messages )
		    )
	    );
    }

	/**
	 * Get payment plans by country
     *
	 * @param array $merchant_data Merchant data to fetch part payment plans for
     *
	 * @return array|WP_Error List of payment plan campaigns
	 */
    public function get_part_payment_plans( $merchant_data ) {

	    // Get campaigns from cache to save bandwidth and loading time
	    $campaigns = get_transient( sprintf( self::PART_PAYMENT_TRANSIENT_FORMAT, $merchant_data['CountryCode'] ) );

	    // If no transient is saved, make new request
	    if( ! $campaigns ) {
		    $checkout_merchant_id   = $merchant_data['MerchantId'];
		    $checkout_secret        = $merchant_data['Secret'];
		    $base_url               = $merchant_data['BaseUrl'];
		    $conn                   = \Svea\Checkout\Transport\Connector::init( $checkout_merchant_id, $checkout_secret, $base_url );
		    $checkoutClient         = new \Svea\Checkout\CheckoutClient( $conn );

		    $data = array(
			    'IsCompany' => false
		    );

		    try {
		        // Get available part payment plans from Svea
			    $campaigns = $checkoutClient->getAvailablePartPaymentCampaigns( $data );
			    // Save response in transient to save loading time
			    set_transient( sprintf( self::PART_PAYMENT_TRANSIENT_FORMAT, $merchant_data['CountryCode'] ), $campaigns, 60 * 60 );
		    } catch ( Exception $e ) {
			    self::log( 'Cannot fetch part payment plans from Svea.' );

			    return new WP_Error( 'svea_error', __( 'Error when getting part payment plans from Svea. Please try again.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ) );
		    }
	    }

	    return $campaigns;
	}
	
	/**
	 * Shortcode for part payment widget
	 * 
	 * @return string
	 */
	public function product_part_payment_widget_shortcode() {
		if ( ! is_product() ) {
			return '';
		}

		ob_start();
		$this->product_part_payment_widget();
		return ob_get_clean();
	}

	/**
	 * Part payment widget used on the product page if activated
     *
	 * @return void
	 */
    public function product_part_payment_widget() {
	    global $product;

	    // get merchant settings
	    $country_data = $this->get_merchant_settings();

	    $product_types = apply_filters( 'woocommerce_sco_part_pay_widget_product_types', array( 'simple', 'variable' ) );

	    // Check if product is any of the specified product types
	    if( ! $product->is_type( $product_types ) ) {
		    return;
	    }

	    // Get part payment plans from Svea
	    $campaigns = $this->get_part_payment_plans( $country_data );

	    if( empty( $campaigns ) ) {
		    return;
	    }

	    // Get price of current product
	    $price = floatval( $product->get_price() );

	    // Filter out suitable campaigns
	    $campaigns = array_values( array_filter( $campaigns, function( $campaign ) use ( $price ) {
		    return ( isset( $campaign['PaymentPlanType'] ) && intval( $campaign['PaymentPlanType'] ) !== 2 )
				&& $price >= $campaign['FromAmount'] && $price <= $campaign['ToAmount'];
	    } ) );

	    $lowest_price_per_month = false;

		// Find the lowest campaign price
        foreach( $campaigns as $campaign ) {
			$campaign_price = $price * $campaign['MonthlyAnnuityFactor'] + $campaign['NotificationFee'];

			if( $lowest_price_per_month === false ) {
				$lowest_price_per_month = $campaign_price;
			} else {
	            // Get the cost per month from current plan
		        $lowest_price_per_month = min( $lowest_price_per_month, $campaign_price );
			}
        }

	    if( $lowest_price_per_month === false || $lowest_price_per_month <= 0 ) {
		    return;
	    }

	    // Get logo for current country
	    $svea_icon = $this->get_svea_part_pay_logo_by_country( $country_data['CountryCode'] );

	    ?>
        <p class="svea-part-payment-widget"><img src="<?php echo esc_url( $svea_icon ); ?>" /><?php printf( __( 'Part pay from %s/month', WC_Svea_Checkout_i18n::TEXT_DOMAIN ), wc_price( round( $lowest_price_per_month ) ) ); ?></p>
	    <?php
    }

	/**
	 * Get Svea Part Pay logo depending on country
	 *
	 * @param string $country
	 *
	 * @return string URL of the part pay logo
	 */
	public function get_svea_part_pay_logo_by_country( $country = '' ) {
	    // Set default logo
		$default_logo = 'https://cdn.svea.com/webpay/Svea_Primary_RGB_medium.png';

		$country = strtoupper( $country );

		// Get logos from Sveas cdn
		$logos = array(
			'SE' => 'https://cdn.svea.com/webpay/Svea_Primary_RGB_medium.png',
			'NO' => 'https://cdn.svea.com/webpay/Svea_Primary_RGB_medium.png',
			'FI' => 'https://cdn.svea.com/webpay/Svea_Primary_RGB_medium.png',
			'DE' => 'https://cdn.svea.com/webpay/Svea_Primary_RGB_medium.png',
		);

		$logo = $default_logo;

		// Set logo for current country
		if( isset( $logos[$country] ) ) {
			$logo = $logos[$country];
		}

		return apply_filters( 'woocommerce_sco_part_pay_icon', $logo, $country );
	}

	/**
     * Check if company customer type is default
     *
	 * @return bool Whether or not company customer type is default
	 */
	public function is_company_default() {
	    return apply_filters( 'woocommerce_sco_settings_default_customer_type', $this->get_option( 'default_customer_type' ) ) == 'company';
    }

	/**
	 * Check if preset email is read only
	 *
	 * @return bool Whether or not preset email is read only
	 */
	public function is_preset_email_read_only() {
	    return apply_filters( 'woocommerce_sco_settings_preset_value_email_read_only', $this->get_option( 'preset_value_email_read_only' ) ) === 'yes';
    }

	/**
	 * Check if preset phone is read only
	 *
	 * @return bool Whether or not preset phone is read only
	 */
	public function is_preset_phone_read_only() {
		return apply_filters( 'woocommerce_sco_settings_preset_value_phone_read_only', $this->get_option( 'preset_value_phone_read_only' ) ) === 'yes';
	}

	/**
	 * Check if preset zip code is read only
	 *
	 * @return bool Whether or not preset zip code is read only
	 */
	public function is_preset_zip_code_read_only() {
		return apply_filters( 'woocommerce_sco_settings_preset_value_zip_code_read_only', $this->get_option( 'preset_value_zip_code_read_only' ) ) === 'yes';
	}

	/**
	 * Check if cancelled orders should be saved
	 *
	 * @return bool Whether or not cancelled orders should be saved
	 */
	public function should_save_cancelled_orders() {
	    return apply_filters( 'woocommerce_sco_settings_save_cancelled_orders', $this->get_option( 'save_cancelled_orders' ) ) === 'yes';
    }

	/**
	 * Check if "change address" should be hidden in the iframe
	 *
	 * @return bool Whether or not "change address" should be hidden in the iframe
	 */
    public function should_hide_change_address() {
	    return apply_filters( 'woocommerce_sco_settings_hide_change_address', $this->get_option( 'hide_change_address' ) ) === 'yes';
    }

	/**
	 * Check if "not you?" should be hidden in the iframe
	 *
	 * @return bool Whether or not "not you?" should be hidden in the iframe
	 */
	public function should_hide_not_you() {
		return apply_filters( 'woocommerce_sco_settings_hide_not_you', $this->get_option( 'hide_not_you' ) ) === 'yes';
	}

	/**
	 * Check if the anonymous flow should be hidden in the iframe
	 *
	 * @return bool Whether or not the anonymous flow should be hidden in the iframe
	 */
	public function should_hide_anonymous() {
		return apply_filters( 'woocommerce_sco_settings_hide_anonymous', $this->get_option( 'hide_anonymous' ) ) === 'yes';
	}

	/**
     * Get a list of base countries
     *
	 * @return array List of base countries
	 */
	public static function get_base_countries() {
		$base_countries = array(
			'SE'      => __( 'Sweden', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
			'NO'      => __( 'Norway', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
			'FI'      => __( 'Finland', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
		);

		return $base_countries;
	}

	/**
     * Alias for get_base_countries()
     *
	 * @return array List of base countries
	 */
	public static function get_base_countries_as_options() {
		return self::get_base_countries();
	}

	/**
     * Print admin options page
     *
	 * @return void
	 */
	public function admin_options() {
		$this->validate_merchant_credentials();

		parent::admin_options();
	}

	/**
     * Validate the entered merchant credentials, displaying an error if they are invalid
     *
	 * @return void
	 */
	private function validate_merchant_credentials() {
		$countries_status_codes = array();

		$base_countries = self::get_base_countries();

		foreach( $base_countries as $country => $country_label ) {
			$country_status_code = 200;

			$cached_status_code = get_transient( 'woocommerce_sco_country_credentials_status_code_' . strtolower( $country ) );

			if( $cached_status_code !== false ) {
				$countries_status_codes[$country] = $cached_status_code;
				continue;
			}

			// Test fetching an order that doesn't exist
			$country_settings = $this->get_country_settings( $country );

			$checkout_merchant_id = $country_settings['MerchantId'];
		    $checkout_secret = $country_settings['Secret'];

		    if( empty( $checkout_merchant_id ) || empty( $checkout_secret ) ) {
		    	$countries_status_codes[$country] = 0;
		    	continue;
		    }

		    $admin_base_url        = $country_settings['AdminBaseUrl'];
		    $admin_connector       = \Svea\Checkout\Transport\Connector::init( $checkout_merchant_id, $checkout_secret, $admin_base_url );
		    $admin_checkout_client = new \Svea\Checkout\CheckoutAdminClient( $admin_connector );

		    $data['OrderId'] = -1;

		    try {
			    $admin_response = $admin_checkout_client->getOrder( $data );
		    } catch ( Exception $e ) {
		    	$country_status_code = $e->getCode();
		    }

		    $countries_status_codes[$country] = $country_status_code;

		    // Cache request until credentials are changed
		    set_transient( 'woocommerce_sco_country_credentials_status_code_' . strtolower( $country ), $country_status_code );
		}

		$error_messages = array(
			'401' => __( 'Merchant ID and secret are either incorrect or does not have permission from Svea to connect. You might have entered test credentials in production mode or vice versa.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
		);

		$errors_to_display = array();

		foreach( $countries_status_codes as $country_code => $country_status_code ) {
			if( ! isset( $error_messages[$country_status_code] ) ) {
				continue;
			}

			$error_message = $error_messages[$country_status_code];

			$errors_to_display[] = '<strong>' . $country_code . '</strong>: ' . $error_message;
		}

		if( count( $errors_to_display ) > 0 ) {
			?>
			<div class="error woocommerce-sco-error">
				<ul>
					<?php foreach( $errors_to_display as $error ) : ?>
					<li><?php echo $error; ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<?php
		}
	}

	/**
	 * Clear the merchant validation transient cache when options are changed
	 *
	 * @return void
	 */
	public static function clear_merchant_validation_cache() {
	    $base_countries = self::get_base_countries();

		foreach( $base_countries as $country => $country_label ) {
			delete_transient( 'woocommerce_sco_country_credentials_status_code_' . strtolower( $country ) );
		}
	}

	/**
	 * Logging method.
     *
	 * @param string $message
	 */
	public static function log( $message ) {
		if ( self::$log_enabled ) {
			if ( is_null( self::$log ) ) {
				self::$log = new WC_Logger();
			}

			self::$log->add( self::GATEWAY_ID, $message );
		}
	}

	/**
	 * Check if this payment gateway is available to be used
	 * in the WooCommerce Checkout.
	 *
	 * @return boolean Whether or not this payment gateway is available
	 */
	public function is_available() {
		// Return false since we don't want this payment gateway to be in the WooCommerce Checkout
		return false;
	}

	public function get_customer_types() {
	    return $this->customer_types;
    }

	/**
	 * This function returns merchants based on currency and country
	 *
     * @param string $currency Currency to get merchant settings for
	 * @param string $country Country to get merchant settings for
	 * @return array $settings Returns current country settings with country specific information.
	 */
	public function get_merchant_settings( $currency = '', $country = '' ) {
		if( empty( $currency ) ) {
			$currency = get_woocommerce_currency();
		}

		if( empty( $country ) ) {
		    $country = WC()->customer->get_billing_country();
        }

		$currency = strtoupper( $currency );
		$country = strtoupper( $country );

		switch( $currency ) {
			case 'SEK':
				$settings = array(
					'CountryCode'   => 'SE',
					'Currency'      => 'SEK',
					'Locale'        => 'sv-SE',
					'Secret'        => $this->secret_se,
					'MerchantId'    => $this->merchant_id_se,
					'BaseUrl'       => $this->testmode_se == 'yes' ? \Svea\Checkout\Transport\Connector::TEST_BASE_URL : \Svea\Checkout\Transport\Connector::PROD_BASE_URL,
					'AdminBaseUrl'  => $this->testmode_se == 'yes' ? \Svea\Checkout\Transport\Connector::TEST_ADMIN_BASE_URL : \Svea\Checkout\Transport\Connector::PROD_ADMIN_BASE_URL,
				);
				break;
			case 'EUR':
			    // If multiple countries has this currency you can give other credentials for it
			    switch( $country ) {
                    default:
				    $settings = array(
					    'CountryCode'   => 'FI',
					    'Currency'      => 'EUR',
					    'Locale'        => 'fi-FI',
					    'Secret'        => $this->secret_fi,
					    'MerchantId'    => $this->merchant_id_fi,
					    'BaseUrl'       => $this->testmode_fi == 'yes' ? \Svea\Checkout\Transport\Connector::TEST_BASE_URL : \Svea\Checkout\Transport\Connector::PROD_BASE_URL,
					    'AdminBaseUrl'  => $this->testmode_fi == 'yes' ? \Svea\Checkout\Transport\Connector::TEST_ADMIN_BASE_URL : \Svea\Checkout\Transport\Connector::PROD_ADMIN_BASE_URL,
				    );

				    break;
                }

				break;
			case 'NOK':
				$settings = array(
					'CountryCode'   => 'NO',
					'Currency'      => 'NOK',
					'Locale'        => 'nn-NO',
					'Secret'        => $this->secret_no,
					'MerchantId'    => $this->merchant_id_no,
					'BaseUrl'       => $this->testmode_no == 'yes' ? \Svea\Checkout\Transport\Connector::TEST_BASE_URL : \Svea\Checkout\Transport\Connector::PROD_BASE_URL,
					'AdminBaseUrl'  => $this->testmode_no == 'yes' ? \Svea\Checkout\Transport\Connector::TEST_ADMIN_BASE_URL : \Svea\Checkout\Transport\Connector::PROD_ADMIN_BASE_URL,
				);
				break;
            case 'DKK':
	            $settings = array(
		            'CountryCode'   => 'DK',
		            'Currency'      => 'DKK',
		            'Locale'        => 'da-DK',
		            'Secret'        => $this->secret_dk,
		            'MerchantId'    => $this->merchant_id_dk,
		            'BaseUrl'       => $this->testmode_dk == 'yes' ? \Svea\Checkout\Transport\Connector::TEST_BASE_URL : \Svea\Checkout\Transport\Connector::PROD_BASE_URL,
		            'AdminBaseUrl'  => $this->testmode_dk == 'yes' ? \Svea\Checkout\Transport\Connector::TEST_ADMIN_BASE_URL : \Svea\Checkout\Transport\Connector::PROD_ADMIN_BASE_URL,
	            );
	            break;
			default:
				$settings = array(
					'CountryCode' => '',
					'Currency'    => '',
					'Locale'      => '',
					'Secret'      => '',
					'MerchantId'  => '',
                    'BaseUrl'     => '',
				);
		}

		return $settings;
	}

	/**
	 * This function returns merchants based solely on country
	 *
	 * @param string $country Country to get merchant settings for
	 * @return array $settings Returns current country settings with country specific information.
	 */
	public function get_country_settings( $country = '' ) {
		if( empty( $country ) ) {
			$country = WC()->customer->get_billing_country();
		}

		$country = strtoupper( $country );

		switch( $country ) {
			case 'SE':
				$settings = array(
					'CountryCode'   => 'SE',
					'Currency'      => 'SEK',
					'Locale'        => 'sv-SE',
					'Secret'        => $this->secret_se,
					'MerchantId'    => $this->merchant_id_se,
					'BaseUrl'       => $this->testmode_se == 'yes' ? \Svea\Checkout\Transport\Connector::TEST_BASE_URL : \Svea\Checkout\Transport\Connector::PROD_BASE_URL,
					'AdminBaseUrl'  => $this->testmode_se == 'yes' ? \Svea\Checkout\Transport\Connector::TEST_ADMIN_BASE_URL : \Svea\Checkout\Transport\Connector::PROD_ADMIN_BASE_URL,
				);
				break;
			case 'FI':
                $settings = array(
                    'CountryCode'   => 'FI',
                    'Currency'      => 'EUR',
                    'Locale'        => 'fi-FI',
                    'Secret'        => $this->secret_fi,
                    'MerchantId'    => $this->merchant_id_fi,
                    'BaseUrl'       => $this->testmode_fi == 'yes' ? \Svea\Checkout\Transport\Connector::TEST_BASE_URL : \Svea\Checkout\Transport\Connector::PROD_BASE_URL,
                    'AdminBaseUrl'  => $this->testmode_fi == 'yes' ? \Svea\Checkout\Transport\Connector::TEST_ADMIN_BASE_URL : \Svea\Checkout\Transport\Connector::PROD_ADMIN_BASE_URL,
                );

                break;
			case 'NO':
				$settings = array(
					'CountryCode'   => 'NO',
					'Currency'      => 'NOK',
					'Locale'        => 'nn-NO',
					'Secret'        => $this->secret_no,
					'MerchantId'    => $this->merchant_id_no,
					'BaseUrl'       => $this->testmode_no == 'yes' ? \Svea\Checkout\Transport\Connector::TEST_BASE_URL : \Svea\Checkout\Transport\Connector::PROD_BASE_URL,
					'AdminBaseUrl'  => $this->testmode_no == 'yes' ? \Svea\Checkout\Transport\Connector::TEST_ADMIN_BASE_URL : \Svea\Checkout\Transport\Connector::PROD_ADMIN_BASE_URL,
				);
				break;
			case 'DK':
				$settings = array(
					'CountryCode'   => 'DK',
					'Currency'      => 'DKK',
					'Locale'        => 'da-DK',
					'Secret'        => $this->secret_dk,
					'MerchantId'    => $this->merchant_id_dk,
					'BaseUrl'       => $this->testmode_dk == 'yes' ? \Svea\Checkout\Transport\Connector::TEST_BASE_URL : \Svea\Checkout\Transport\Connector::PROD_BASE_URL,
					'AdminBaseUrl'  => $this->testmode_dk == 'yes' ? \Svea\Checkout\Transport\Connector::TEST_ADMIN_BASE_URL : \Svea\Checkout\Transport\Connector::PROD_ADMIN_BASE_URL,
				);
				break;
			default:
				$settings = array(
					'CountryCode' => '',
					'Currency'    => '',
					'Locale'      => '',
					'Secret'      => '',
					'MerchantId'  => '',
					'BaseUrl'     => '',
				);
		}

		return $settings;
	}

	public static function is_valid_vat_percentage( $country, $vat_percentage ) {
	    $country = strtoupper( $country );

	    $vat_percentages = array(
            'SE'        => array( 0, 6, 12, 25 ),
            'FI'        => array( 0, 10, 15, 24 ),
            'NO'        => array( 0, 8, 10, 11.11, 15, 24, 25 ),
            'DK'        => array( 0, 25 ),
        );

	    if( ! isset( $vat_percentages[$country] ) ) {
	        return false;
        }

	    return in_array( $vat_percentage, $vat_percentages[$country] );
    }

	/**
	 * @throws WC_Data_Exception
     *
     * @return void
	 */
	public function process_push() {
		if( ! isset( $_GET['order_id'] ) || ! isset( $_GET['key'] ) ) {
			status_header( 403 );
			exit;
		}

		$wc_order_id = absint( $_GET['order_id'] );
		$wc_order_key = wc_clean( $_GET['key'] );

		$wc_order = wc_get_order( $wc_order_id );

		// Is order valid?
		if( ! $wc_order || ! $wc_order->key_is_valid( $wc_order_key ) ) {
			status_header( 403 );
			exit;
		}

		// Save svea order id
		$svea_order_id = $wc_order->get_meta( '_svea_co_order_id', true );

		if( ! $svea_order_id ) {
			status_header( 403 );
			exit;
		}

		// Push was successful
        update_option( 'sco_last_push', time() );

		self::log( sprintf( 'Received push from Svea, fetching information from order-id %s', $wc_order->get_id() ) );

		$result = $this->sync_order_with_svea( $wc_order, true );

		$success = isset( $result['success'] ) && $result['success'];

		// Build array to send to action 'woocommerce_checkout_order_processed'
		$posted = $this->get_posted_data( $wc_order );

		if( ! empty( $posted ) ) {
			// Set $_POST data
			$this->set_posted_data( $posted );

			do_action( 'woocommerce_checkout_order_processed', $wc_order->get_id(), $posted, $wc_order );
		}

		if( ! $success ) {
			if( isset( $result['error_code'] ) ) {
				status_header( intval( $result['error_code'] ) );
			}

			exit;
		}

		do_action( 'woocommerce_sco_after_push_order', $wc_order );
	}

	/**
	 * @param WC_Order $wc_order The WooCommerce order to sync
	 * @param bool $sync_status Whether to sync the order status or not
	 *
	 * @return array
	 * @throws WC_Data_Exception
	 */
	public function sync_order_with_svea( $wc_order, $sync_status = false ) {
		// Get svea order id
		$svea_order_id = $wc_order->get_meta( '_svea_co_order_id', true );

		if( ! $svea_order_id ) {
			return array(
				'success'       => false,
				'error_code'    => 403,
				'message'       => '',
			);
		}
		self::log( sprintf( 'Syncing order %s with Svea.', $wc_order->get_id() ) );

		$country_settings = $this->get_merchant_settings( $wc_order->get_currency(), $wc_order->get_billing_country() );
		$checkout_merchant_id = $country_settings['MerchantId'];
		$checkout_secret = $country_settings['Secret'];

		$base_url = $country_settings['BaseUrl'];
		$connector = \Svea\Checkout\Transport\Connector::init( $checkout_merchant_id, $checkout_secret, $base_url );
		$checkout_client = new \Svea\Checkout\CheckoutClient( $connector );

		$data['OrderId'] = intval( $svea_order_id );

		try {
			$response = $checkout_client->get( apply_filters( 'woocommerce_sco_get_order', $data ) );
		} catch( Exception $e ) {
			self::log( sprintf( 'Received error when fetching information from Svea: %s', $e->getMessage() ) );

			return array(
				'success'       => false,
				'error_code'    => 500,
				'message'       => '',
			);
		}

		if( ! isset( $response['Status'] ) ) {
			self::log( 'Status wasn\'t set for the order. Exiting.' );

			return array(
				'success'       => false,
				'error_code'    => 400,
				'message'       => '',
			);
		}

		$svea_status = strtoupper( wc_clean( $response['Status'] ) );

		if( $svea_status == 'CREATED' ) {
			// Don't handle newly created orders

			return array(
				'success'       => true,
				'message'       => '',
			);
		}

		// Save billing address
		if( ! isset( $response['BillingAddress'] ) ) {
			self::log( 'Billing address is not set. Exiting.' );

			return array(
				'success'       => false,
				'error_code'    => 400,
				'message'       => '',
			);
		}

		if( ! isset( $response['EmailAddress'] ) ) {
			self::log( 'Email-address is not set. Exiting.' );

			return array(
				'success'       => false,
				'error_code'    => 400,
				'message'       => '',
			);
		}

		if( ! isset( $response['Customer']['IsCompany'] ) ) {
			self::log( 'IsCompany is not set for the customer. Exiting.' );

			return array(
				'success'       => false,
				'error_code'    => 400,
				'message'       => '',
			);
		}

		// Sync response address with WooCommerce Order
        $this->sync_order_address_with_svea( $response, $wc_order );

		// Save Svea payment method and display payment method in admin
		if( isset( $response['PaymentType'] ) ) {
			$svea_payment_type = strtoupper( sanitize_text_field( $response['PaymentType'] ) );

			// Check if Payment method is set and exists in array
			if( isset( $this->svea_payment_methods[$svea_payment_type] ) ) {
				$wc_order->set_payment_method_title(
					sprintf( '%s (%s)', $this->get_title(), $this->svea_payment_methods[$svea_payment_type] )
				);
			}

			// Check if payment method exists in our array (for translation), else, save the new payment type to meta data
			$svea_payment_type_label = isset( $this->svea_payment_methods[$svea_payment_type] ) ? $this->svea_payment_methods[$svea_payment_type] : ucfirst( strtolower( $svea_payment_type ) );

			// Update
			$wc_order->update_meta_data( '_svea_co_payment_type', $svea_payment_type_label );
			$wc_order->save();
		}

		// Only sync status if parameter is given
		if( ! $sync_status ) {
			return array(
				'success'       => true,
				'message'       => '',
			);
		}

		self::log( sprintf( 'Status: %s', $svea_status ) );

        //Only update order status if payment is not final in Svea
        if( ! $wc_order->meta_exists( '_svea_co_order_final' ) ) {

	        if ( $svea_status === 'FINAL' ) {
	            // Sync order rows from response with wc_order
		        if( apply_filters( 'use_svea_order_sync', true ) && isset( $response['Cart']['Items'] ) ) {
			        $this->sync_svea_order_rows( $response['Cart']['Items'], $wc_order );
		        }

		        $wc_order->update_meta_data( '_svea_co_order_final', current_time( 'timestamp', true ) );

		        if ( ! $wc_order->has_status( 'processing' ) && ! $wc_order->has_status( 'completed' ) ) {
			        // Set order date to the date the order is paid
			        $wc_order->set_date_created( current_time( 'timestamp', true ) );
			        $wc_order->set_date_modified( current_time( 'timestamp', true ) );

			        // Set WooCommerce order to complete
			        $wc_order->payment_complete( $svea_order_id );
		        } else if ( $wc_order->has_status( 'completed' ) && empty( $wc_order->get_meta( '_svea_co_deliver_date' ) ) ) {
					/*
						If it's already complete we can try to deliver the order right away.
						However, there is no guarantee that the order will actually be present in Payment admin this soon. 
						Therefore we make a (really) dirty fix by sleeping php which should buy Svea enough time to send the order into PA

						Sorry
					*/
					sleep(2);
					$this->deliver_order( $svea_order_id );
				}

		        $wc_order->save();

	        } else if ( $svea_status === 'CONFIRMED' && ! $wc_order->has_status( 'pending' ) ) {
		        // If orderstatus from Svea is confirmed -> set WooCommerce Status to pending

		        $wc_order->update_status( 'pending', __( 'Awaiting payment from Svea Checkout', WC_Svea_Checkout_i18n::TEXT_DOMAIN ) );

	        } else if ( $this->should_save_cancelled_orders() && $svea_status === 'CANCELLED' && ! $wc_order->has_status( 'cancelled' ) ) {
		        // If orderstatus from Svea is cancelled -> set WooCommerce Status to cancelled

		        $wc_order->update_meta_data( '_svea_co_order_cancelled', true );

		        $wc_order->update_status( 'cancelled', __( 'Unpaid order cancelled - time limit reached.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ) );

		        $wc_order->save();

	        } else {

		        self::log( 'Status wasn\'t updated for order.' );

		        return array(
			        'success'    => false,
			        'error_code' => 403,
			        'message'    => '',
		        );

	        }
		}

		return array(
			'success'       => true,
			'message'       => '',
		);
	}

	/**
     * Sync order rows from response with wc_order
     *
	 * @param array $svea_cart_items
	 * @param WC_Order $wc_order
     *
     * @return void
	 */
    public function sync_svea_order_rows( $svea_cart_items, $wc_order ) {
	    $svea_wc_order_item_row_ids = array();

        $rounding_order_id = $wc_order->get_meta( '_svea_co_rounding_order_row_id', true );

        if( $rounding_order_id ) {
	        $svea_wc_order_item_row_ids[] = $rounding_order_id;
        }

        foreach ( $wc_order->get_items( array( 'line_item', 'fee', 'shipping' ) ) as $item_key => $order_item ) {
            $svea_wc_order_item_row_ids[] = intval( $order_item->get_meta( '_svea_co_order_row_id', true ) );
        }

        foreach ( $svea_cart_items as $svea_cart_item ) {
	        if ( ! in_array( $svea_cart_item['RowNumber'], $svea_wc_order_item_row_ids ) ) {
                $product_id = wc_get_product_id_by_sku( $svea_cart_item['ArticleNumber'] );
                if( $product_id ){
	               $order_item = new WC_Order_Item_Product();
	               $order_item->set_product_id( $product_id );
                } else {
                    $order_item = new WC_Order_Item_Fee();
                }

                $quantity = $svea_cart_item['Quantity'] / 100;
		        $total = ( $svea_cart_item['UnitPrice'] / 100 )  * $quantity;

		        $order_item->set_props( array(
			        'quantity'     => $quantity,
                    'name'         => $svea_cart_item['Name'],
			        'total'        => $total / ( $svea_cart_item['VatPercent'] / 10000 + 1 ),
			        'total_tax'    => $total - ( $total / ( $svea_cart_item['VatPercent'] / 10000 + 1 ) ),
		        ) );

		        $wc_order->add_item( $order_item );
		        $order_item->update_meta_data( '_svea_co_order_row_id', $svea_cart_item['RowNumber'] );
		        $order_item->save();
	        }
		}
		
		$wc_order->calculate_totals();
		
		do_action( 'woocommerce_sco_before_push_update_order_items', $wc_order );
		
		$wc_order->save();

		do_action( 'woocommerce_sco_after_push_update_order_items', $wc_order );
    }

	/**
	 * @param Array $response
	 * @param WC_order $wc_order
	 *
	 * @throws WC_Data_Exception
	 */
	private function sync_order_address_with_svea( $response, $wc_order ) {
		$is_company = $response['Customer']['IsCompany'] ? true : false;

		$wc_order->update_meta_data( '_svea_co_is_company', $is_company );

		$email_address = wc_clean( $response['EmailAddress'] );

		// Create account if user doesn't exist and user wants to
		$user = get_user_by( 'email', $email_address );

		$customer = false;

		if( $user !== false && isset( $user->ID ) ) {
			$customer = new WC_Customer( $user->ID );

			if( $customer->get_id() <= 0 ) {
				$customer = false;
			}
		}

		$enable_signup = get_option( 'woocommerce_enable_signup_and_login_from_checkout' ) == 'yes' ? true : false;
		$enable_guest_checkout = get_option( 'woocommerce_enable_guest_checkout' ) == 'yes' ? true : false;

		$must_create_account = $enable_guest_checkout || $user !== false ? false : true;

		if( $user === false && ( $must_create_account || ( $wc_order->get_meta( '_should_create_account', true ) != false && $enable_signup ) ) ) {

			// Generate username
			$username = sanitize_user( current( explode( '@', $email_address ) ), true );

			$append = 1;
			$o_username = $username;

			while( username_exists( $username ) ) {
				$username = $o_username . $append;
				++$append;
			}

			// Generate password
			$password = wp_generate_password();

			$password_generated = true;

			$new_customer_data = apply_filters( 'woocommerce_new_customer_data', array(
				'user_login'    => $username,
				'user_pass'     => $password,
				'user_email'    => $email_address,
				'role'          => 'customer',
			) );

			$customer_id = wp_insert_user( $new_customer_data );

			do_action( 'woocommerce_created_customer', $customer_id, $new_customer_data, $password_generated );

			$customer = new WC_Customer( $customer_id );
		}

		// Set email on order and customer
		$wc_order->set_billing_email( $email_address );

		if( $customer ) {
		    $customer->set_billing_email( $email_address );
        }

		if( isset( $response['PhoneNumber'] ) && ! empty( wc_clean( $response['PhoneNumber'] ) ) ) {
			$phone_number = wc_clean( $response['PhoneNumber'] );

			$wc_order->set_billing_phone( $phone_number );

			if( $customer ) {
				$customer->set_billing_phone( $phone_number );
			}
		}

		if ( $is_company ) {
			$address_fields = array(
				'FullName'      => 'company',
				'StreetAddress' => 'address_1',
				'CoAddress'     => 'address_2',
				'PostalCode'    => 'postcode',
				'City'          => 'city',
				'CountryCode'   => 'country',
			);
		} else {
			$address_fields = array(
				// 'FirstName'         => 'first_name',
				// 'LastName'          => 'last_name',
				'StreetAddress' => 'address_1',
				'CoAddress'     => 'address_2',
				'PostalCode'    => 'postcode',
				'City'          => 'city',
				'CountryCode'   => 'country',
			);
		}

		$billing_address  = array();
		$shipping_address = array();

		if ( $is_company ) {
			if ( isset( $response['CustomerReference'] ) && ! empty( wc_clean( $response['CustomerReference'] ) ) ) {
				$customer_reference = wc_clean( $response['CustomerReference'] );

				// Save customer reference as first and last name on order
				$customer_full_name = WC_Svea_Checkout_Helper::split_customer_name( $customer_reference );

				$billing_address['first_name'] = $customer_full_name['first_name'];
				$billing_address['last_name']  = $customer_full_name['last_name'];

				$shipping_address['first_name'] = $customer_full_name['first_name'];
				$shipping_address['last_name']  = $customer_full_name['last_name'];

				$wc_order->update_meta_data( '_svea_co_customer_reference', $customer_reference );
			} else {
				$billing_address['first_name'] = $response['BillingAddress']['FullName'];
				$shipping_address['first_name'] = $response['ShippingAddress']['FullName'];
			}

			// Save company registration number
			if ( ! empty( $response['Customer']['NationalId'] ) ) {
				$reg_nr = trim( $response['Customer']['NationalId'] );

				// Strip first two numbers for Swedish registration numbers
				if ( isset( $response['BillingAddress']['CountryCode'] ) && strtoupper( $response['BillingAddress']['CountryCode'] ) === 'SE'
					&& strlen( $reg_nr ) > 10 ) {
					$reg_nr = substr( $reg_nr, 2 );
				}

				$wc_order->update_meta_data( '_svea_co_company_reg_number', $reg_nr );
			}
		} else {
			// Handle different name formats
			if ( ! empty( $response['BillingAddress']['FirstName'] ) && ! empty( $response['BillingAddress']['LastName'] ) ) {
				$billing_address['first_name'] = $response['BillingAddress']['FirstName'];
				$billing_address['last_name']  = $response['BillingAddress']['LastName'];
			} else if ( ! empty( $response['BillingAddress']['FullName'] ) ) {
				$customer_full_name = WC_Svea_Checkout_Helper::split_customer_name( $response['BillingAddress']['FullName'] );

				$billing_address['first_name'] = $customer_full_name['first_name'];
				$billing_address['last_name']  = $customer_full_name['last_name'];
			}

			if ( ! empty( $response['ShippingAddress']['FirstName'] ) && ! empty( $response['ShippingAddress']['LastName'] ) ) {
				$shipping_address['first_name'] = $response['ShippingAddress']['FirstName'];
				$shipping_address['last_name']  = $response['ShippingAddress']['LastName'];
			} else if ( ! empty( $response['ShippingAddress']['FullName'] ) ) {
				$customer_full_name = WC_Svea_Checkout_Helper::split_customer_name( $response['ShippingAddress']['FullName'] );

				$shipping_address['first_name'] = $customer_full_name['first_name'];
				$shipping_address['last_name']  = $customer_full_name['last_name'];
			} else {
				$shipping_address['first_name'] = isset( $billing_address['first_name'] ) ? $billing_address['first_name'] : '';
				$shipping_address['last_name']  = isset( $billing_address['last_name'] ) ? $billing_address['last_name'] : '';;
			}
		}

		foreach ( $address_fields as $svea_key => $wc_key ) {
			$billing_address_value = false;

			// Save Billing Address
			if ( isset( $response['BillingAddress'][ $svea_key ] ) && ! empty( wc_clean( $response['BillingAddress'][ $svea_key ] ) ) ) {
				$billing_address_value = wc_clean( $response['BillingAddress'][ $svea_key ] );

				$billing_address[ $wc_key ] = $billing_address_value;
			}

			// Save Shipping Address
			if ( isset( $response['ShippingAddress'][ $svea_key ] ) && ! empty( wc_clean( $response['ShippingAddress'][ $svea_key ] ) ) ) {
				$shipping_address_value = wc_clean( $response['ShippingAddress'][ $svea_key ] );

				$shipping_address[ $wc_key ] = $shipping_address_value;
			} else if ( $billing_address_value !== false ) {
				$shipping_address[ $wc_key ] = $billing_address_value;
			}
		}

		// Save billing address data
		foreach( $billing_address as $key => $value ) {
			if ( is_callable( array( $wc_order, "set_billing_{$key}" ) ) ) {
				$wc_order->{"set_billing_{$key}"}( $value );

				// Save billing address to customer for future purchases
				if( $customer !== false && is_callable( array( $customer, "set_billing_{$key}" ) ) ) {
					$customer->{"set_billing_{$key}"}( $value );
				}
			}
		}

		// Save shipping address data
		foreach( $shipping_address as $key => $value ) {
			if ( is_callable( array( $wc_order, "set_shipping_{$key}" ) ) ) {
				$wc_order->{"set_shipping_{$key}"}( $value );

				// Save shipping address to customer for future purchases
				if( $customer !== false && is_callable( array( $customer, "set_shipping_{$key}" ) ) ) {
					$customer->{"set_shipping_{$key}"}( $value );
				}
			}
		}

		if( isset( $response['BillingAddress']['IsGeneric'] ) && $response['BillingAddress']['IsGeneric'] ) {
		    $wc_order->update_meta_data( '_svea_co_is_generic', true );

		    // Handle generic address lines
            if( isset( $response['BillingAddress']['AddressLines'] ) ) {
                $wc_order->update_meta_data( '_svea_co_billing_address_lines', $response['BillingAddress']['AddressLines'] );
            }

            if( isset( $response['ShippingAddress']['AddressLines'] ) ) {
                $wc_order->update_meta_data( '_svea_co_shipping_address_lines', $response['ShippingAddress']['AddressLines'] );
            }
        }

		if( $customer !== false ) {
			$order_data = array(
				'order_id'      => $wc_order->get_id(),
				'customer_id'   => $customer->get_id(),
			);

			// Set user as customer
			wc_update_order( $order_data );
		}

		if( $customer !== false ) {
			$customer->save();
		}

		$wc_order->save();
    }

	/**
	 * Process confirmation
	 *
	 * @param int $order_id ID of the order being confirmed
	 * @return mixed
	 */
	public function process_confirmation( $order_id ) {
		$wc_order = wc_get_order( $order_id );

		self::log( 'Processing confirmation' );

		if( ! $wc_order ) {
			self::log( 'No order by this ID' );
			return new WP_Error( 'sco_order_not_exists', __( 'There is no order by this ID.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ) );
		}

		$svea_order_id = $wc_order->get_meta( '_svea_co_order_id', true );

		if( ! $svea_order_id ) {
			return new WP_Error( 'no_sco_order_id', __( 'Svea order id is not set for this order', WC_Svea_Checkout_i18n::TEXT_DOMAIN ) );
		}

		self::log( 'Syncing order with Svea' );

		$this->sync_order_with_svea( $wc_order );

		// Build array to send to action 'woocommerce_checkout_order_processed'
		$posted = $this->get_posted_data( $wc_order );

		// Set $_POST data
		$this->set_posted_data( $wc_order );

		if( ! empty( $posted ) ) {
			do_action( 'woocommerce_checkout_order_processed', $wc_order->get_id(), $posted, $wc_order );
		}

		// Empty current cart
		self::log( 'Empty WooCommerce cart' );
		WC()->cart->empty_cart();

		self::log( 'Unset session "order_awaiting_payment"' );
		WC()->session->__unset( 'order_awaiting_payment' );

		// Redirect to checkout order received page
		wp_redirect( $this->get_return_url( $wc_order ) );
		exit;
	}


	/**
     * This function returns an array with data needed to be sent to hook
     *
	 * @param obj $order current wc order object
     * @return array $posted data that will be sent to hook
	 */
	public function get_posted_data( $order ) {
	    $posted = array();

		$post_data = $order->get_meta( '_sco_post_data', true );

		// Parse encoded string to array $form_data
		if( $post_data && is_array( $post_data ) ) {
			$posted = $post_data;
        }

		$billing_info = $order->get_data()['billing'] ? $order->get_data()['billing'] : '';
		$shipping_info = $order->get_data()['shipping'] ? $order->get_data()['shipping'] : '';
		$payment_method = $order->get_data()['payment_method'] ? $order->get_data()['payment_method'] : '';

		foreach( $billing_info as $b_key => $b_value ) {
			$posted['billing_' . $b_key] = $b_value;
		}

		foreach( $shipping_info as $s_key => $s_value ) {
			$posted['shipping_' . $s_key] = $s_value;
		}

		if( $payment_method !== '' ) {
			$posted['payment_method'] = $payment_method;
		}

		return $posted;
    }

	/**
	 * This function sets the form_data in $_POST variable if it doesn't exists already
     *
	 * @param array $post_data Array with post data
	 */
	public function set_posted_data( $post_data ) {
		// Set $_POST if not already set
		foreach( $post_data as $key => $value ) {
			if( ! isset( $_POST[ $key ] ) ) {

			    // Sanitize data
			    if( is_array( $value ) ) {
				    $_POST[ $key ] = array_map( 'sanitize_text_field', wp_unslash( $value ) );
                } else {
				    $_POST[ $key ] = sanitize_textarea_field( $value );
                }
			}
		}
    }

	/**
	 * Display reference name and payment method on the order edit page
	 *
	 * @param $order WC_Order the order which the meta will be displayed on
	 *
	 * @return void
	 */
	public function svea_co_display_extra_admin_order_meta( $order ) {
		?>
		<div class="address">
		<?php if( $order->get_meta( '_svea_co_is_company', true ) ) : ?>
			<?php if ( ( $customer_reference = $order->get_meta( '_svea_co_customer_reference', true ) ) ) : ?>
			<p>
				<strong><?php _e( 'Svea Payment reference:', WC_Svea_Checkout_i18n::TEXT_DOMAIN ); ?></strong>
				<?php echo esc_attr( $customer_reference ); ?>
			</p>
			<?php endif; ?>
			<?php if ( ( $registration_number = $order->get_meta( '_svea_co_company_reg_number', true ) ) ) : ?>
			<p>
				<strong><?php _e( 'Organisation number:', WC_Svea_Checkout_i18n::TEXT_DOMAIN ); ?></strong>
				<?php echo esc_attr( $registration_number ); ?>
			</p>
			<?php endif; ?>
		<?php endif; ?>
		<?php if ( ( $payment_type = $order->get_meta( '_svea_co_payment_type', true ) ) ) : ?>
			<p>
				<strong><?php _e( 'Svea Payment method:', WC_Svea_Checkout_i18n::TEXT_DOMAIN ); ?></strong>
				<?php echo esc_attr( $payment_type ); ?>
			</p>
		<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Display reference name on the order edit page
	 *
	 * @param $order WC_Order the order which the meta will be displayed on
	 */
	public function svea_co_display_extra_order_meta( $order ) {

		if( ! ( $customer_reference = get_post_meta( $order->get_id(), '_svea_co_customer_reference', true ) ) ) {
			return;
		} ?>

		<h2 class="woocommerce-order-details__title">
			<?php _e( 'Payment information', WC_Svea_Checkout_i18n::TEXT_DOMAIN ); ?>
		</h2>
		<table class="woocommerce-table woocommerce-table--customer-details shop_table customer_details">
			<tbody>

			<?php if( $customer_reference ) : ?>
				<tr>
					<th><?php _e( 'Payment reference', WC_Svea_Checkout_i18n::TEXT_DOMAIN ); ?></th>
					<td><?php echo esc_attr( $customer_reference ); ?></td>
				</tr>
			<?php endif; ?>

			</tbody>
		</table>
		<?php
	}

	/**
	 * Process refunds for orders
	 *
	 * @param int $order_id ID of the order being credited
	 * @param float $amount Amount being refunded
     * @param string $reason Reason for the refund
     *
	 * @return WP_Error|boolean whether or not the refund was processed
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		$wc_order = wc_get_order( $order_id );

		if( ! $wc_order ) {
			return false;
		}

		$svea_order_id = $wc_order->get_meta( '_svea_co_order_id', true );

		if( ! $svea_order_id ) {
			return new WP_Error( 'no_sco_order_id', __( 'Svea order id is not set for this order', WC_Svea_Checkout_i18n::TEXT_DOMAIN ) );
		}

		$client_settings = $this->get_merchant_settings( $wc_order->get_currency(), $wc_order->get_billing_country() );

		$checkout_merchant_id = $client_settings['MerchantId'];
		$checkout_secret = $client_settings['Secret'];
		$admin_base_url = $client_settings['AdminBaseUrl'];

		$connector = \Svea\Checkout\Transport\Connector::init( $checkout_merchant_id, $checkout_secret, $admin_base_url );

		$checkout_client = new \Svea\Checkout\CheckoutAdminClient( $connector );

		$data = array(
			'OrderId'   => intval( $svea_order_id ),
		);

		try {
			$response = $checkout_client->getOrder( apply_filters( 'woocommerce_sco_get_order', $data ) );
		} catch(Exception $e) {
			self::log( 'Error when getting order from Svea.' );
			return new WP_Error( 'svea_error', __( 'Error when getting order from Svea. Please try again.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ) );
		}

		if( ! isset( $response['Actions'] ) ) {
			self::log( 'Actions were not available for the order "' . $order_id . '""' );
			return new WP_Error( 'svea_no_actions', __( 'Svea has no actions available for this order.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ) );
        }

        // Cancel order amount if action is available
		if( in_array( 'CanCancelAmount', $response['Actions'] ) ) {
			$cancelled_amount = 0;

			if( isset( $response['CancelledAmount'] ) ) {
				$cancelled_amount = intval( $response['CancelledAmount'] );
			}

			// Increment already credited amount with the new credit amount
			$cancelled_amount = $cancelled_amount + ( intval( round( $amount * 100 ) ) );

			$cancel_data = array(
				'OrderId'            => intval( $svea_order_id ),
				'CancelledAmount'    => $cancelled_amount,
			);

			try {
				$response = $checkout_client->cancelOrderAmount( apply_filters( 'woocommerce_sco_credit_order_amount', $cancel_data ) );
			} catch (Exception $e) {
				self::log( 'Error when cancelling order amount in Svea. Please try again. Error message: ' . $e->getMessage() );
				return new WP_Error( 'svea_error', __( 'Error when cancelling order amount in Svea. Please try again.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ) );
			}

			$wc_order->add_order_note(
				sprintf( __( 'Cancelled %s in Svea.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ), wc_price( $amount ) )
			);

			self::log( sprintf( __( 'Cancelled %s in Svea.' ), $amount ) );

			return true;
		} else if( ! isset( $response['Deliveries'][0] ) ) {
			self::log( 'No deliveries were found on the order "' . $order_id . '""' );
			return new WP_Error( 'svea_no_deliveries', __( 'No deliveries were found on this order. You can only credit if the order has been delivered.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ) );
		}

		$delivery = $response['Deliveries'][0];

		if( in_array( 'CanCreditNewRow', $delivery['Actions'] ) ) {
			$order_rows = $delivery['OrderRows'];

			$delivery_total = $delivery['DeliveryAmount'];
			$credited_total = $delivery['CreditedAmount'];

			// Calculate amount left that can be credited
			$amount_left = $delivery_total - $credited_total;

			$order_rows_total = 0.0;

			$tax_amounts = array();

			// Calculate the tax amounts
			foreach( $order_rows as $order_row ) {
				$tax_rate = intval( $order_row['VatPercent'] / 100 );

				$row_total = ( $order_row['UnitPrice'] / 100 ) * ( $order_row['Quantity'] / 100 );

				// If there is a discount, add it to the calculation
				$row_total *= ( 100 - ( $order_row['DiscountPercent'] / 100 ) ) / 100;

				$order_rows_total += $row_total;

				if( isset( $tax_amounts[$tax_rate] ) ) {
					$tax_amounts[$tax_rate] += $row_total;
				} else {
					$tax_amounts[$tax_rate] = $row_total;
				}
			}

			$total_credit_amount = 0;

			foreach( $tax_amounts as $tax_rate => $tax_amount ) {
				if( $tax_amount > 0 ) {
					$tax_part = min( 1.0, $tax_amount / $order_rows_total );

					$credit_amount = round( $tax_part * $amount * 100 ) / 100;

					// Skip 0 credits
					if ( $credit_amount <= 0 ) {
						continue;
					}

					$total_credit_amount += $credit_amount;

					// Handle cases where we have a negative rounding and are trying to credit
					// more than the total order value
					if ( $total_credit_amount > $amount_left / 100 ) {
						$total_credit_diff = $total_credit_amount - ( $amount_left / 100 );

						$total_credit_amount -= $total_credit_diff;
						$credit_amount -= $total_credit_diff;
					}

					$credit_name = sprintf( __( 'Credit (%s)', WC_Svea_Checkout_i18n::TEXT_DOMAIN ), $tax_rate . '%' );

					if( ! empty( $reason ) ) {
						$credit_name .= ', ' . sprintf( __( 'reason: %s', WC_Svea_Checkout_i18n::TEXT_DOMAIN ), $reason );

						if( function_exists( 'mb_strlen' ) ) {
							if( mb_strlen( $credit_name ) > 40 ) {
								$credit_name = trim( mb_substr( $credit_name, 0, 37 ) ) . '...';
							}
						} else if( strlen( $credit_name ) > 40 ) {
							$credit_name = trim( substr( $credit_name, 0, 37 ) ) . '...';
						}
					}

					$credit_data = array(
						'OrderId'       => intval( $svea_order_id ),
						'DeliveryId'    => intval( $delivery['Id'] ),
						'NewCreditRow'  => array(
							'Name'          => $credit_name,
							'Quantity'      => 100,
							'VatPercent'    => intval( $tax_rate ) * 100,
							'UnitPrice'     => round( $credit_amount * 100 ),
						)
					);

					try {
						$response = $checkout_client->creditNewOrderRow( apply_filters( 'woocommerce_sco_credit_new_order_row', $credit_data ) );
					} catch (Exception $e) {
						self::log( 'Error when crediting in Svea. Please try again.' );
						return new WP_Error( 'svea_error', __( 'Error when crediting in Svea. Please try again.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ) );
					}
				}
			}

			$credit_diff = $amount - $total_credit_amount;

			$credit_diff_rounded = round( $credit_diff * 100 );

			$sum_credit_amount = $total_credit_amount * 100 + $credited_total;

			// Check if the rest of the order should be credited and add to diff
			if ( $wc_order->get_remaining_refund_amount() <= 0 && $sum_credit_amount < $wc_order->get_total_refunded() ) {
				$credit_diff_rounded = max( $credit_diff_rounded, round( ( $wc_order->get_total_refunded() - $sum_credit_amount ) * 100 ) );
			}

			if( $credit_diff_rounded > 0 ) {
				self::log( sprintf( __( 'Diff %s' ), $credit_diff_rounded ) );
				$credit_data = array(
					'OrderId'       => intval( $svea_order_id ),
					'DeliveryId'    => intval( $delivery['Id'] ),
					'NewCreditRow'  => array(
						'Name'              => __( 'Credit rounding', WC_Svea_Checkout_i18n::TEXT_DOMAIN ),
						'Quantity'          => 100,
						'UnitPrice'         => $credit_diff_rounded,
						'VatPercent'        => 0,
					)
				);

				try {
					$response = $checkout_client->creditNewOrderRow( apply_filters( 'woocommerce_sco_credit_new_order_row', $credit_data ) );
				} catch (Exception $e) {
					self::log( 'Error when crediting in Svea. Please try again.' );
					return new WP_Error( 'svea_error', __( 'Error when crediting in Svea. Please try again.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ) );
				}
			}



			$wc_order->add_order_note(
				sprintf( __( 'Credited %s in Svea.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ), wc_price( $amount ) )
			);

			self::log( sprintf( __( 'Credited %s in Svea.' ), $amount ) );

			return true;
		} else if( in_array( 'CanCreditAmount', $delivery['Actions'] ) ) {
			$credited_amount = 0;

			if( isset( $delivery['CreditedAmount'] ) ) {
				$credited_amount = intval( $delivery['CreditedAmount'] );
			}

			// Increment already credited amount with the new credit amount
			$credit_amount = $credited_amount + ( intval( round( $amount * 100 ) ) );

			$credit_data = array(
				'OrderId'           => intval( $svea_order_id ),
				'DeliveryId'        => intval( $delivery['Id'] ),
				'CreditedAmount'    => $credit_amount,
			);

			try {
				$response = $checkout_client->creditOrderAmount( apply_filters( 'woocommerce_sco_credit_order_amount', $credit_data ) );
			} catch (Exception $e) {
				self::log( 'Error when crediting in Svea. Please try again.' );
				return new WP_Error( 'svea_error', __( 'Error when crediting in Svea. Please try again.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ) );
			}

			$wc_order->add_order_note(
				sprintf( __( 'Credited %s in Svea.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ), wc_price( $amount ) )
			);

			self::log( sprintf( __( 'Credited %s in Svea.' ), $amount ) );

			return true;
		} else {
			self::log( 'The order "' . $order_id . '" can not be credited.' );
			return new WP_Error( 'svea_error', __( 'This order can not be credited.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ) );
		}
	}

	/**
	 * Process delivery for order
	 *
	 * @param int $order_id ID of the order being delivered
     *
	 * @return mixed whether or not the order was delivered in Svea
	 */
	public function deliver_order( $order_id ) {
		$wc_order = wc_get_order( $order_id );

		$svea_order_id = $wc_order->get_meta( '_svea_co_order_id', true );

		if( ! $svea_order_id ) {
			return false;
		}

		$client_settings = $this->get_merchant_settings( $wc_order->get_currency(), $wc_order->get_billing_country() );

		$checkout_merchant_id = $client_settings['MerchantId'];
		$checkout_secret = $client_settings['Secret'];
		$admin_base_url = $client_settings['AdminBaseUrl'];

		// Check if merchant ID and secret is set
		if( ! isset( $checkout_merchant_id[0] ) || ! isset( $checkout_secret[0] ) ) {
			return false;
		}

		$connector = \Svea\Checkout\Transport\Connector::init( $checkout_merchant_id, $checkout_secret, $admin_base_url );

		$checkout_client = new \Svea\Checkout\CheckoutAdminClient( $connector );

		$get_data = array(
			'OrderId'   => intval( $svea_order_id ),
		);

		try {
			$get_response = $checkout_client->getOrder( apply_filters( 'woocommerce_sco_get_order', $get_data ) );
		} catch(Exception $e) {
			self::log( 'Error when getting order from Svea.' );
			return new WP_Error( 'svea_error', __( 'Error when getting order from Svea. Please try again.', WC_Svea_Checkout_i18n::TEXT_DOMAIN ) );
		}

		// Check if status is already synced
		if( isset( $get_response['OrderStatus'] ) && strtoupper( $get_response['OrderStatus'] ) === 'DELIVERED' ) {
			$wc_order->add_order_note(
				__( 'Order is already delivered in Svea. No action needed.', WC_Svea_Checkout_i18n::TEXT_DOMAIN )
			);

			return true;
		}

		$deliver_data = array(
			'OrderId'       => intval( $svea_order_id ),
			'OrderRowIds'   => array() // Deliver entire order
		);

		try {
			$checkout_client->deliverOrder( apply_filters( 'woocommerce_sco_deliver_order', $deliver_data ) );
		} catch (Exception $e) {
			$wc_order->add_order_note(
				sprintf( __( 'Error received when trying to deliver order in Svea: %s' ), $e->getMessage() )
			);

			return false;
		}

		$wc_order->add_order_note(
			__( 'Order was delivered in Svea.', WC_Svea_Checkout_i18n::TEXT_DOMAIN )
		);

		self::log( 'Order was delivered in Svea.' );

		$wc_order->update_meta_data( '_svea_co_deliver_date', date( 'Y-m-d H:i:s' ) );
		$wc_order->save();

		return true;
	}

	/**
	 * Process cancellation of order
	 *
	 * @param int $order_id ID of the order being cancelled
	 * @return boolean whether or not the order was cancelled in Svea
	 */
	public function cancel_order( $order_id ) {
		$wc_order = wc_get_order( $order_id );

		$svea_order_id = $wc_order->get_meta( '_svea_co_order_id', true );

		if( ! $svea_order_id || $wc_order->meta_exists( '_svea_co_order_cancelled' ) ) {
			return false;
		}

		$client_settings = $this->get_merchant_settings( $wc_order->get_currency(), $wc_order->get_billing_country() );

		$checkout_merchant_id = $client_settings['MerchantId'];
		$checkout_secret = $client_settings['Secret'];
		$admin_base_url = $client_settings['AdminBaseUrl'];

		// Check if merchant ID and secret is set
		if( ! isset( $checkout_merchant_id[0] ) || ! isset( $checkout_secret[0] ) ) {
			return false;
		}

		$connector = \Svea\Checkout\Transport\Connector::init( $checkout_merchant_id, $checkout_secret, $admin_base_url );

		$checkout_admin_client = new \Svea\Checkout\CheckoutAdminClient( $connector );

		$data = array(
			'OrderId'       => intval( $svea_order_id ),
		);

		try {
			$checkout_admin_client->cancelOrder( apply_filters( 'woocommerce_sco_cancel_order', $data ) );
		} catch (Exception $e) {
			$wc_order->add_order_note(
				sprintf( __( 'Error received when trying to cancel order in Svea: %s' ), $e->getMessage() )
			);
			self::log( sprintf( __( 'Error received when trying to cancel order in Svea: %s' ), $e->getMessage() ) );

			return false;
		}

		$wc_order->add_order_note(
			__( 'Order was cancelled in Svea.', WC_Svea_Checkout_i18n::TEXT_DOMAIN )
		);

		self::log( 'Order was cancelled in Svea.' );

		$wc_order->update_meta_data( '_svea_co_cancel_date', date( 'Y-m-d H:i:s' ) );
		$wc_order->save();

		return true;
	}

	/**
	 * Process refund of order
	 *
	 * @param int $order_id ID of the order being cancelled
	 * @return boolean whether or not the order was refunded in Svea
	 */
	public function refund_order( $order_id ) {
		$wc_order = wc_get_order( $order_id );

		if( ! $wc_order ) {
			return false;
		}

		$refund_amount = $wc_order->get_total() - $wc_order->get_total_refunded();

		return $this->process_refund( $order_id, $refund_amount );
	}

	/**
	 * Initialise Gateway Settings Form Fields
	 */
	public function init_form_fields() {
		$this->form_fields = include( 'settings-svea-checkout.php' );
	}

	/**
	 * Get pages for the standard checkout page option field
	 * 
	 * @return array
	 */
	public function standard_checkout_page_options() {
		global $pagenow;

		$page = isset( $_GET['page'] ) ? $_GET['page'] : '';
		$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : '';
		$section = isset( $_GET['section'] ) ? $_GET['section'] : '';
		
		// only run this function on svea checkout settings page
		if ( $pagenow !== 'admin.php' || $page !== 'wc-settings' || $tab !== 'checkout' || $section !== 'svea_checkout' ) {
			return;
		}

		$pages = get_pages();

		$pages_array = [
			'0' => __( 'Choose page' ),
		];

		foreach ( $pages as $page ) {
			$pages_array[$page->ID] = $page->post_title;
		}

		return $pages_array;
	}

}
