=== WooCommerce Fortnox Hub ===
Contributors: BjornTech
Tags: woocommerce, fortnox, integration, hub, accounting, zettle, klarna, paypal, stripe, swish
Requires at least: 4.9
Tested up to: 6.0
Requires PHP: 7.3
Stable tag: 5.4.3
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Integrates WooCommerce with Fortnox

== Description ==

This plugin integrates WooCommerce with Fortnox. Automating your accounting and inventory.

It can create a Fortnox Invoice or Order based on a WooCommerce Order.

Customer data will be stored in the Fortnox customer database and will be automatically updated if changed in a new order.

Invoices can automatically be set to paid based on the payment method used in the WooCommerce order.

The plugin can create internal invoices and voucher to automate the handling of payouts for a number of payment gateways.

- Klarna
- Stripe
- Nets
- Clearhaus
- Swish, if you are using the BjornTech Swish plugin (https://wordpress.org/plugins/woo-swish-e-commerce/)
- Zettle, if you are using the BjornTech Zettle plugin (https://wordpress.org/plugins/woo-zettle-integration/)

== Requirements ==

The following licenses from Fortnox are required in order for the basic functionality of the plugin to work:

- Bookkeeping
- Invoicing
- Integration

== Installation ==

You can find detailed installation and configuration instructions [here](https://bjorntech.com/sv/kom-igang-med-fortnox-hub?utm_source=wp-fortnox&utm_medium=plugin&utm_campaign=product)

== Changelog ==
= 5.4.3 =
* Fix: Zettle payouts sometimes not correctly syncing currency
= 5.4.2 =
* Working with WooCommerce 6.9
* New: Added the possibility to add coupon codes used in the WooCommerce order to invoice
* Fix: Credit invoices sometimes not getting automatically bookkeeped
* Fix: Credit invoice paid date incorrectly set to original invoice date
* Fix: Klarna payout settings missing fee account settings
* Dev: Added more filters to Clearhaus payout module
= 5.4.1 =
* Fix: Fortnox Getting started message keeps reappearing
* Fix: Rest of the World shipping zone not displaying
* Fix: Warehouse scope not added with Oauth
= 5.4.0 =
* Working with WooCommerce 6.7 and Wordpress 6.0
* New: Added the possibility to customize which invoices that should be bookkeeped - based on payment method
* New: Added setting to set invoice/order language in Fortnox
* New: Added more filters
* New: Added possibility to not update email on invoice if customer exists
* Fix: Sale price picked when using pricelists instead of regular price if sale price exist
* Fix: Refund rows on invoice sometimes showing random item descriptions
* Fix: Zettle payout date not always accurate
* Fix: Klarna payouts invoices not always being created
* Fix: Clearhouse payouts using test credentials in production with certain PHP versions
* Fix: Improved connection to Fortnox
= 5.3.0 =
* Working with WooCommerce 6.4
* New: Added possibility to generate payouts as voucher instead of invoice.
* New: Added possibility to generate payouts without details of the underlying payments.
* New: Support for Payment Plugins for Stripe WooCommerce when creating payout for Stripe.
* New: Support for creating invoices directly instead of orders when default is to create orders
* New: Support for setting warehouse ready on orders and invoices.
* New: Added support for Nets, Svea and Clearhaus for payouts.
* New: Added filter for Stripe secret key.
* New: Added support for dynamic payment methods such as Briqpay
* Fix: Changed to correct country code for South Korea.
= 5.2.3 =
* Working with WooCommerce 6.3
= 5.2.2 =
* Working with Wordpress 5.9
* Fix: Stockchange from Fortnox did not trigger low-/outof-stock emails.
* Fix: Order numbers are now printed instead of order id in Error messages. 
* Fix: Faulty description of Fortnox e-mail body.
= 5.2.1 =
* Working with WooCommerce 6.1
* Fix: Option to clean previosly entered VAT numbers using the xxxxxxnnnn format not allowed by Fortnox any longer.
* Fix: Order processing can now be stopped if organization number is missing.
* Fix: Changed to correct country code for Russia.
* Fix: Added current customer to the 'fortnox_customer_data_before_processing' filter.
= 5.2.0 =
* Support for new Fortnox OAuth flow
= 5.1.4 =
* Fix: partial credits failed when the credit includes the shipping row
* Fix: API_BLANK was not set as constant, causing the plugin to fail when loading in some environments.
= 5.1.3 =
* Working with WooCommerce 6.0
* Fix: If one of the adress field was empty in a new order and at the same time had content on a Fortnox customer card, the card data was faulty added to the Order/Invoice.
* Fix: Stocklevel was faulty changed on a product in Fortnox when an order item including the product was removed from a order manually in order admin.
= 5.1.2 =
* Fix: Rows in partial credits was not updated correctly in Fortnox causing credit invoice to be blank.
* Fix: Handle stock was always set when an article was updated from Fortnox, causing an update loop in some cases.
* Fix: Version number of javascript used in bulk edit was not correctly set.
= 5.1.1 =
* Fix: PW Giftcard error caused orders/invoices not to sync
= 5.1.0 =
* Working with WooCommerce 5.9
* New: Added possibility to quick edit Fortnox fields in Products view
* New: Added extended options for article handling
* Fix: Unable to sync certain country codes
= 5.0.2 =
* Fix: Clearing invoice rows on partially credit invoices to avoid mix of data due to Fortnox new way of handling rows when updating.
= 5.0.1 =
* Fix: get_country() called in wrong context.
= 5.0.0 =
* Working with WooCommerce 5.8
* The plugin now requires PHP 7.3
* New: Added possibility to trigger invoice action on invoice setting to paid.
* New: Added advanced setting to delete invoice payments accidently mapped to e-commerce order from bank import.
* New: Added acvanced setting to configure the system to not use external references in Fortnox when checking if invoice was already synced.
* New: Added the possibility to set accounts for fees.
* Fix: Wrong context was used getting prices.
* Fix: Order/Invoice was not created in a correct way when a PW Gift card was used as payment.
* Fix: Prices from WooCommerce was taken in 'view' context. Causing problems when using other plugins that do change the display of prices.
* Fix: $organization number not declared caused warning message in the system log.
* Fix: Lenght was not checked when updating Description on products, causing updates to fail if the description was longer than 50 characters.f
* Fix: Paypal credit invoices caused the invoice status checker to crash.
* Fix: Zettle payout invoices did not include all payments when using WEEKLY payouts.
* Fix: Changed id for Klarna payment gateway from 'klarna' to 'klarna_payments'
= 4.9.2 =
* Tested with Wordpress 5.8 and WooCommerce 5.5
* Fix: Allowed to set/get standard prices when using B2B-King.
* New: Added filter when finding existing customer in Fortnox.
= 4.9.1 =
* Fix: re-added the possibility to access the legacy print template setting.
* Fix: If OSS is configured VAT exemption on validated customer is not booked correctly.
= 4.9.0 =
* Fix: If an Invoice payment was created but not booked the Invoice check function wrongly handled the Invoice as already paid.
* Fix: When manual syncing a cancelled order the cancel-process for Order/Invoice was not performed.
* Fix: The automatic invoice check failed to run in some cases.
* Fix: Shipping amounts were rounded in the wrong way in Fortnox Order/Invoices if decimails in the webshop was set to 0.
* Fix: If using 0 decimals in WooCommerce the plugin did still use the non-rounded number when creating order/invoices.
* Fix: Moved the OSS settings from Advanced to Account settings.
* Fix: Refunds did crash because of missing country on refund order.
* Fix: Fortnox Order/Invoice was not set to be reversed VAT if using WooCommerce EU VAT Assistance.
= 4.8.2 =
* Fix: A Fortnox Invoice should be credited rather than cancelled if the creating Woo order was paid.
* Fix: VAT account was not selected correctly if country-info was missing on the order.
* Fix: Order item name on existing article was overwritten by WooCommerce Name.
* Fix: Order fails to sync if the order is refunded, paid with Stripe and the order currency differs from the Stripe payout currency.
= 4.8.1 =
* Fix: Product updates failed when the advanced setting to update account on Fortnox products where enabled.
= 4.8.0 =
* New: Added the possibility to prevent syncing for orders paid with a specific payment method.
* Fix: When bulk-syncing the orders where synced in reversed order.
* Fix: Previous settings in shipping was not converted to the new structure causing shipping field on the order/invoice to be empty.
* Fix: In some cases a manual sync of a refunded order did not cancel the Order/Invoice in Fortnox.
= 4.7.2 =
* Fix: Sales account was incorrectly set to Fortnox Default instead of the account set in the plugin. Please do check account on bookings made from installing 4.7.0
= 4.7.1 =
* Fix: Refunds where the initial Fortnox Order/Invoice was not booked/printed did not cancel the Order/Invoice as expected.
* Fix: Price on free Shipping was set to "null" instead of 0, causing Order not to sync.
= 4.7.0 =
* Working with WooCommerce 5.3
* New: Added the possibility to add an adminstration fee on created orders/invoices. Can also be set to 0 to avoid standard Fee in Fortnox. See advanced settings.
* New: Added an advanced setting to force the Action scheduler to process the queue if running on a system where CRON is not running.
* Fix: A partial refund on an Invoice using the ACCRUAL accounting method fails if the original invoice was not booked
* Fix: Checking also for zettle as well as izettle to identify Zettle orders since the service changed name.
* Fix: Shipping settings did not handle shipping zones in a correct way.
* Fix: Orders with no customer identifier (mail or orgnaisation number) causes multiple customers to be created.
* Fix: Unschedule of action sometimes fails because of a bug in the Action scheduler. Removed unshedule actions temporary.
* Fix: Removed the full daily update from Fortnox since it is no longer needed.
* Fix: If an account was specified on an article Fortnox did not use the account on a order/invoice row.
* Fix: Emailing of invoices to customers from Fortnox failed sometimes.
* Fix: Full refund of an invoice failed if accouting method was CASH and the invoice was paid.
* New
= 4.6.3 =
* Fix: If an account number is changed in settings and order/invoices where updated the VAT was not re-calculated on order/invoice rows
* Fix: Full refunds of an invoice if using 'CASH' as accounting method failed.
* Fix: Partial refunds when failed if an order was setup to create Fortnox Order.
* Fix: When using the advanced setting "Queue admin requests" the plugin did fail to check invoice statuses.
* Fix: Metatdata on products where not saved correctly in some cases.
* Fix: No account number was set on shipping row with 0 tax, causing processing error in Fortnox.
= 4.6.2 =
* Fix: Account number on sales was set to 0 when using the PW Giftcards plugin.
= 4.6.1 =
* Fix: Category selections did not contain emtpy categories.
* Fix: Print handling crashes when checking refund invoices.
= 4.6.0 =
* New: Added product unit names as cached information in order to increase performance.
* New: Added the possibility to configure products to be set as "housework" products in Fortnox orders/invoices. Enable this function in the advanced settings page.
* New: Added the possibility to use any metadata as organisation number.
* New: Added the possibility to use the WooCommerce order number as Order/Invoice number.
* New: Added support WC Product Price Based on Country makes it possible to link Fortnox-pricelists to a pricing zone.
* New: Added the possibility to select to sync only articles with the "Webshop article" set.
* New: Added the possibility to set product status and category on product being created from Fortnox.
* New: Added support for PW Giftcards
* Fix: Product unit names was only checked for duplicates by Unit name instead of Unit name + Unit code.
* Fix: Date for invoice & article-sync was not cleared when changing automatic options.
= 4.5.0 =
* New: Added the possibility to set print template per payment method.
* New: Added the possibility to select sending of invoice to customer per payment method.
* Fix: Purhcase prices was updated in Fortnox also when not configured to do so.
* Fix: Shipping Article number used for Shipping was not saved correctly in settings.
* Fix: Selection of order status for creation was empty after 4.4.0
= 4.4.0 =
* New: Added the possibility to use the Fortnox Freight field on Order/Invioce for Shipping costs.
* New: Added the possibility to select if customer order note should be copied from an Order to an Invoice automatically.
* New: Added support for "Admin columns pro".
* New: Added support for price sync to and from "WooCommerce Wholesale Prices" & "WooCommerce Wholesale Prices pro".
* New: Using 'SE' as country if no country is set on WooCommerce order.
* New: Added possiblity to put delivery details on the Invoice/Order only without update of the customer card in Fortnox.
* New: Added the possiblity to bulk sync WooCommerce orders to Fortnox.
* New: Added the possibility to sync stocklevel to Fortnox when pressing sync button in product list.
* New: Added Manufacurer, Manufacturer article, Stock-place, Unit and Barcode as extra fields on a WooCommerce product and the possibility to sync the fields to and from Fortnox.
* New: Added the possibility to automatically create WooCommerce simple product from a Fortnox article when it is created in Fortnox.
* New: Using mapped products in WooCommerce when doing a full sync rather than asking Fortnox for all article.
* New: Probibiting automatic updating to Fortnox when a product is updated from Fortnox, preventing endless loops in updates.
* Fix: Always set Discount to 0 in order rows to aviod errors if an existing Fortnox customer have a Invoice discount registred.
* Fix: Added the possibility to configure to use the Shipping field on Fortnox Order/Invoice instead of adding shipping as an item.
* Fix: If shipping cost was 0 the shipping row was wrongly created with an account number for the 0% VAT.


== Upgrade Notice ==
