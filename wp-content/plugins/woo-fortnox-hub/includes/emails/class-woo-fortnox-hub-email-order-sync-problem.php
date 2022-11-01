<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
/**
 * New Order Email
 *
 * An email sent to the admin when an order fails to sync to Fortnox.
 *
 * @class Woo_Fortnox_Hub_Email_Failed_Order_Sync
 * @version 1.0
 * @extends WC_Email_Failed_Order
 */
class Woo_Fortnox_Hub_Email_Failed_Order_Sync extends WC_Email_Failed_Order
{

    /**
     * Constructor
     */
    public function __construct()
    {

        $this->id = 'fortnox_failed_order_sync';
        $this->title = __('Fortnox Failed order sync', 'woo-fortnox-hub');
        $this->description = __('Fortnox failed order sync emails are sent to the admin when an order fails to sync to Fortnox.', 'woo-fortnox-hub');

        $this->heading = __('Fortnox failed order sync', 'woo-fortnox-hub');
        $this->subject = __('[{blogname}] Order failed to sync to Fortnox ({order_number}) - {order_date}', 'woo-fortnox-hub');

        $this->template_html = 'emails/admin-failed-order-sync.php';
        $this->template_plain = 'emails/plain/admin-failed-order-sync.php';
        $this->template_base = plugin_dir_path(WC_FH()->plugin_file) . 'templates/';

        // Triggers for this email
        add_action('fortnox_order_sync_failed', array($this, 'trigger'));

        // We want all the parent's methods, with none of its properties, so call its parent's constructor, rather than my parent constructor
        WC_Email::__construct();

        // Other settings
        $this->recipient = $this->get_option('recipient');

        if (!$this->recipient) {
            $this->recipient = get_option('admin_email');
        }
    }

    /**
     * trigger function.
     *
     * We need to override WC_Email_Customer_Completed_Order's trigger method because it expects to be run only once
     * per request (but multiple subscription renewal orders can be generated per request).
     *
     * @access public
     * @return void
     */
    public function trigger($order_id, $order = null)
    {

        if ($order_id) {
            $this->object = wc_get_order($order_id);
            $this->recipient = wcs_get_objects_property($this->object, 'billing_email');

            $order_date_index = array_search('{order_date}', $this->find);
            if (false === $order_date_index) {
                $this->find['order_date'] = '{order_date}';
                $this->replace['order_date'] = wcs_format_datetime(wcs_get_objects_property($this->object, 'date_created'));
            } else {
                $this->replace[$order_date_index] = wcs_format_datetime(wcs_get_objects_property($this->object, 'date_created'));
            }

            $order_number_index = array_search('{order_number}', $this->find);
            if (false === $order_number_index) {
                $this->find['order_number'] = '{order_number}';
                $this->replace['order_number'] = $this->object->get_order_number();
            } else {
                $this->replace[$order_number_index] = $this->object->get_order_number();
            }
        }

        if (!$this->is_enabled() || !$this->get_recipient()) {
            return;
        }

        $this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());
    }

    /**
     * Get the default e-mail subject.
     *
     * @since 2.5.3
     * @return string
     */
    public function get_default_subject()
    {
        return $this->subject;
    }

    /**
     * Get the default e-mail heading.
     *
     * @since 2.5.3
     * @return string
     */
    public function get_default_heading()
    {
        return $this->heading;
    }

    /**
     * get_subject function.
     *
     * @access public
     * @return string
     */
    public function get_subject()
    {
        return apply_filters('woocommerce_subscriptions_email_subject_customer_completed_renewal_order', parent::get_subject(), $this->object);
    }

    /**
     * get_heading function.
     *
     * @access public
     * @return string
     */
    public function get_heading()
    {
        return apply_filters('woocommerce_email_heading_customer_renewal_order', parent::get_heading(), $this->object);
    }

    /**
     * get_content_html function.
     *
     * @access public
     * @return string
     */
    public function get_content_html()
    {
        return wc_get_template_html(
            $this->template_html,
            array(
                'order' => $this->object,
                'email_heading' => $this->get_heading(),
                'additional_content' => is_callable(array($this, 'get_additional_content')) ? $this->get_additional_content() : '', // WC 3.7 introduced an additional content field for all emails.
                'sent_to_admin' => true,
                'plain_text' => false,
                'email' => $this,
            ),
            '',
            $this->template_base
        );
    }

    /**
     * get_content_plain function.
     *
     * @access public
     * @return string
     */
    public function get_content_plain()
    {
        return wc_get_template_html(
            $this->template_plain,
            array(
                'order' => $this->object,
                'email_heading' => $this->get_heading(),
                'additional_content' => is_callable(array($this, 'get_additional_content')) ? $this->get_additional_content() : '', // WC 3.7 introduced an additional content field for all emails.
                'sent_to_admin' => true,
                'plain_text' => true,
                'email' => $this,
            ),
            '',
            $this->template_base
        );
    }
}
