=== Svea Checkout for WooCommerce ===
Contributors: sveaekonomi, thegeneration
Tags: woocommerce, svea ekonomi, checkout, payment gateway, svea checkout, credit card, invoice, part payment, direct bank
Donate link: https://www.svea.com/
Requires at least: 4.9
Tested up to: 6.0
Requires PHP: 7.0
WC requires at least: 4.0.0
WC tested up to: 6.8.2
License: Apache 2.0
License URI: https://www.apache.org/licenses/LICENSE-2.0
Stable tag: 1.8.3

Supercharge your WooCommerce Store with powerful features to pay via Svea Checkout

== Description ==

Accept Credit cards, invoice, direct bank and part payments in your WooCommerce store. Svea Checkout for WooCommerce is a fully featured checkout solution which simplifies the checkout for your customers and increases conversion.

Advantages for you as a customer:

* Supports sales to both B2B and B2C clients
* Machine learning - learns how your customer likes to pay
* One payment gateway where all payment methods and merchant agreements are included

= Part payment widget =

The plugin provides a widget that can be displayed on the product pages to inform your customers that they can pay with part payments in the checkout. It will display the lowest monthly price which they can pay through part payments.

To activate the feature, follow these steps:

1. Go to **WooCommerce > Settings > Payments > Svea Checkout**
2. Check the box **Display product part payment widget**
3. Select where on the page you want to display the widget
4. View the part payment widget on the product page for eligable products. If the widget is not displayed it might be due to the price since part payment plans often require a minimum amount.

There's also a shortcode available to display the part payment widget on a product page. Add the shortcode `[svea_checkout_part_payment_widget]` to the product page you want to use it on. Or call `do_shortcode()` through a template file.

== Installation ==

1. Install the plugin either through your web browser in the WordPress admin panel or manually through FTP.
2. Activate the plugin
3. Go to WooCommerce > Settings > Advanced > Page setup
4. Set "Checkout page" to the page created at activation, "Svea Checkout"
5. Configure the credentials by browsing to WooCommerce > Settings > Payments > Svea Checkout
6. Check the box "Activate Svea Checkout"
7. Enter "Merchant ID" and "Secret", these credentials are required for the plugin to work
8. Select standard WooCommerce checkout page to redirect to if cart total is 0
9. Follow Sveas instructions to get your production credentials

== Upgrade Notice ==

= 1.17.2 =
1.17.2 is a patch release.

= 1.17.1 =
1.17.1 is a patch release.

= 1.17.0 =
1.17.0 is a minor release.

= 1.16.0 =
1.16.0 is a minor release.

= 1.15.1 =
1.15.1 is a patch release.

= 1.15.0 =
1.15.0 is a minor release.

= 1.14.3 =
1.14.3 is a patch release.

= 1.14.2 =
1.14.2 is a patch release.

= 1.14.1 =
1.14.1 is a patch release.

= 1.14.0 =
1.14.0 is a minor release.

= 1.13.2 =
1.13.2 is a patch release.

= 1.13.1 =
1.13.1 is a patch release.

= 1.13.0 =
1.13.0 is a minor release.

= 1.12.0 =
1.12.0 is a minor release.

= 1.11.0 =
1.11.0 is a minor release.

= 1.10.0 =
1.10.0 is a minor release.

= 1.9.0 =
1.9.0 is a minor release.

= 1.8.0 =
1.8.0 is a minor release.

= 1.7.2 =
1.7.2 is a patch release.

= 1.7.1 =
1.7.1 is a patch release.

= 1.7.0 =
1.7.0 is a minor release.

= 1.6.0 =
1.6.0 is a minor release.

= 1.5.0 =
1.5.0 is a minor release.

== Screenshots ==

1. Initial checkout page before contact details are entered.
2. Checkout page after contact details are entered and possible payment methods are displayed.
3. Thank-you page displayed after successful purchase through Svea Checkout.
4. Settings page used to configure the plugin.

== Changelog ==

= 1.17.2 2020-10-07 =
* Observe postcode changes even if checkout is loaded earlier

= 1.17.1 2020-09-21 =
* Update Svea logo
* Fix line calculations

= 1.17.0 2020-09-11 =
* Redirect to standard WooCommerce checkout page if cart total is 0
* Properly calculate credit amounts when products have many decimals

= 1.16.0 2020-08-05 =
* Shortcode for part payment widget
* Create new order when there's a change in currency

= 1.15.1 2020-05-14 =
* Handle problems with crediting orders that contain order rows with many decimals
* Properly fetch the order row ID when adding order rows to an order

= 1.15.0 2020-04-14 =
* Storing and displaying company reg numbers on view order page

= 1.14.3 2020-04-10 =
* Optimizations

= 1.14.2 2020-04-06 =
* Multisite support

= 1.14.1 2020-03-27 =
* Checkout performance improvements

= 1.14.0 2020-03-24 =
* Add support for WooCommerce 4.0
* Add support for multisite network activation

= 1.13.2 2020-02-03 =
* Split customer reference to populate first- and lastname fields on company orders

= 1.13.1 2019-09-26 =
* Change do_action-format for woocommerce checkout order processed

= 1.13.0 2019-06-27 =
* Add support for Denmark
* Start logging pushes to add warnings if the connection to Svea is not working in the future

= 1.12.0 2019-06-18 =
* Filters to enable reload of checkout
* Only target Svea-checkout form for changes to prevent unneeded ajax-requests

= 1.11.0 2019-04-24 =
* Compatibility with WooCommerce 3.6
* Add filter to toggle order rows-sync after completed order

= 1.10.0 2019-03-13 =
* Add ability to hide elements in the Svea iframe through settings

= 1.9.0 2019-03-13 =
* Add ability to hide elements in the Svea iframe through settings

= 1.9.0 2019-02-14 =
* Add support for invoice fee

= 1.8.0 2019-02-01 =
* Add setting to keep cancelled orders
* Prevent empty cancelled orders from showing up in the order list

= 1.7.2 2019-01-03 =
* Upgrade Svea integration package
* Test with WordPress 5.0.2
* Changes to syncing of unfinished orders

= 1.7.1 2018-11-18 =
* Fix calculations for whether or not to display part payment widget on products

= 1.7.0 2018-10-18 =
* Added support for WooCommerce Subscribe to Newsletter and other third party plugins adding fields to the checkout
* Add new part payment widget with options to show and select position on product page
* Add option to disable ZIP code syncing between Svea and WooCommerce, usable for shops only selling digital wares

= 1.6.0 2018-09-28 =
* Add support for WooCommerce Shipping Calculator
* Add versioning to template files
* Add new hooks and filters

= 1.5.0 2018-06-21 =
* Add support for zipcode based shipping
* Optimize and improve load times for the checkout

== Frequently Asked Questions ==

= What type of clients can I sell to through the gateway? =

You can sell to both B2B and B2C clients through the gateway.
