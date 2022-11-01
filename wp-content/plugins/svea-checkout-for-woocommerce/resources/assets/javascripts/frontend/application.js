jQuery(document).ready(function ($) {
	var $scoPage = $('.wc-svea-checkout-page');

	// Only run this code on the Svea Checkout page
	if($scoPage.length <= 0) {
		return;
	}

	var WCSveaCheckout = function() {
		this.init();
	};

	WCSveaCheckout.prototype = {
		scoOrderId: false,

		scoAPIEnabled: false,

		init: function() {
			// Fetch the Svea Checkout ID to detect if order is re-created
			this.scoOrderId = $scoPage.data('sco-order-id') || false;

			// Send heartbeat every 10 minutes, keeps the order alive even when user is inactive
			window.setInterval(this.heartbeat, 1000 * 60 * 10);

			this.attachEvents();
		},

		// Function for updating fragments
		updateFragments: function(fragments) {
			$.each( fragments, function ( key, value ) {
				$( key ).replaceWith( value );
				$( key ).unblock();
			} );
		},

		// Heartbeat
		heartbeat: function () {
			var data = {
				security: wc_sco_params.sco_heartbeat_nonce,
			};

			$.ajax({
				type: 'POST',
				url: wc_checkout_params.wc_ajax_url.toString().replace('%%endpoint%%', 'sco_heartbeat'),
				data: data,
				success: function (data) {
					if (data.reload === 'true') {
						window.location.reload();
						return;
					}
				}
			});
		},

		saveOrderInformation: function() {
			var self = this;

			var data = {
				security: wc_sco_params.update_sco_order_information,
				form_data: $('.wc-svea-checkout-form').serialize()
			};

			$.ajax({
				type: 'POST',
				url: wc_checkout_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'update_sco_order_information' ),
				data: data,
				success: function(data) {
					// Always update the fragments
					if (data && data.fragments) {
						self.updateFragments(data.fragments);
					}
				}
			});
		},

		observeSveaData: function() {
			var self = this;
			// dont run this code if setting for postal code sync should not sync
			if(wc_sco_params.sync_zip_code === '1') {
				window.scoApi.observeEvent('identity.postalCode', function (data) {
					var newPostCode = data.value || '';

					// Don't update with empty post codes
					if (newPostCode.length <= 0) {
						return;
					}

					var oldPostCode = $('#billing_postcode').val();

					// Only refresh data when post code is changed
					if (oldPostCode === newPostCode) {
						return;
					}

					$('#billing_postcode').val(newPostCode);
					self.refreshData();
				});
			}
		},

		attachSveaEvents: function() {
			var self = this;
			
			var checkoutReady = function () {
				if (!('scoApi' in window)) {
					return;
				}

				self.scoAPIEnabled = true;

				// Listen for changes in postcode and save it for the customer
				// to enable shipping methods for specific postcodes
				self.observeSveaData();

				// Listen for reloads of the Svea Checkout
				$(document).on('checkoutReady', function () {
					if (!('scoApi' in window)) {
						return;
					}

					self.observeSveaData();
				});
			};

			// Svea Checkout JS API, listen for postcode changes
			if ('scoApi' in window) {
				checkoutReady();
			} else {
				$( document ).one( 'checkoutReady', checkoutReady );
			}
		},

		blockCheckout: function(block) {
			if(block) {
				// Add class to checkout page to display loading icon and prevent click
				$('.wc-svea-checkout-page').addClass('updating');
			} else {
				// Remove class from checkout page to enable click
				$('.wc-svea-checkout-page').removeClass('updating');
			}
		},

		setCheckoutUpdating: function(updating) {
			if(updating) {
				// Add class to checkout page to display loading icon and prevent click
				this.blockCheckout(true);

				if(this.scoAPIEnabled) {
					// Disable the checkout to enable changes
					window.scoApi.setCheckoutEnabled(false);
				}
			} else {
				// Remove class from checkout page to enable click
				this.blockCheckout(false);

				if(this.scoAPIEnabled) {
					// Enable the checkout, reflecting changes made
					window.scoApi.setCheckoutEnabled(true);
				}
			}
		},

		attachCheckoutEvents: function() {
			var self = this;

			this.currentCountry = $('.wc-svea-checkout-page #billing_country').val();

			$(document).on('change', '.wc-svea-checkout-page #billing_country', function (e) {
				var newCountry = $('.wc-svea-checkout-page #billing_country').val();

				// Only refresh data if the country has been changed
				if(self.currentCountry != newCountry) {
					self.refreshData();
					self.currentCountry = newCountry;
				}
			});

			$(document).on('change', '.wc-svea-checkout-page #shipping_method .shipping_method', function (e) {
				self.refreshData();
			});

			// Custom event to allow other plugins to refresh the checkout data
			$(document).on('sco_refresh_data', function (e) {
				self.refreshData();
			});

			$(document).on('change', '.wc-svea-checkout-page .wc-svea-checkout-form', function (e) {
				// Save order information on all changes on checkout.
				self.saveOrderInformation();
			});

			// Custom event to allow other plugins to save custom order information
			$(document).on('sco_save_order_information', function (e) {
				self.saveOrderInformation();
			});

			// Listen to checkout event triggered when you for instance add a coupon code
			$('body').on('update_checkout', function () {
				self.refreshData();
			});
		},

		attachEvents: function() {
			var self = this;

			this.attachSveaEvents();
			this.attachCheckoutEvents();
		},

		refreshData: function() {
			var self = this;

			var data = {
				security: wc_sco_params.refresh_sco_snippet_nonce,
				country: $('#billing_country').val(),
				postcode: $('#billing_postcode').val(),
				post_data: $('.wc-svea-checkout-form').serialize()
			};

			var shipping_methods = {};

			$('select.shipping_method, input[name^="shipping_method"][type="radio"]:checked, input[name^="shipping_method"][type="hidden"]').each(function () {
				shipping_methods[$(this).data('index')] = $(this).val();
			});

			data.shipping_method = shipping_methods;

			// Add class to container to display loading effect
			self.setCheckoutUpdating(true);

			$.ajax({
				type: 'POST',
				url: wc_checkout_params.wc_ajax_url.toString().replace('%%endpoint%%', 'refresh_sco_snippet'),
				data: data,
				success: function (data) {

					// Reload the page if requested
					if ('true' === data.reload) {
						window.location.reload();
						return;
					}

					// Remove any notices added previously
					$('.woocommerce-NoticeGroup-updateOrderReview').remove();

					// Always update the fragments
					if (data && data.fragments) {
						self.updateFragments(data.fragments);
					}

					var fetchedScoOrderId = data.sco_order_id || false;

					// Only replace snippet if order ID is different or if the API is not enabled to prevent token missmatch
					if (fetchedScoOrderId != self.scoOrderId || !self.scoAPIEnabled) {
						self.scoOrderId = fetchedScoOrderId;

						// Update the Svea Checkout snippet
						if ('sco_snippet' in data) {
							$('.wc-svea-checkout-checkout-module').replaceWith(data.sco_snippet);
							$('.wc-svea-checkout-checkout-module').unblock();
						}
					}

					// Check for error
					if ('failure' === data.result) {

						var $container = $('.wc-svea-checkout-page');

						// Remove notices from all sources
						$('.woocommerce-error, .woocommerce-message').remove();

						// Add new errors returned by this event
						if (data.messages) {
							$container.prepend('<div class="woocommerce-NoticeGroup-updateOrderReview">' + data.messages + '</div>');
						} else {
							$container.prepend(data);
						}

						// Lose focus for all fields
						$container.find('.input-text, select, input:checkbox').blur();

						// Scroll to top
						$('html, body').animate({
							scrollTop: ($container.offset().top - 100)
						}, 1000);

					}

					// Fire updated_checkout e
					$(document.body).trigger('updated_checkout', [data]);

					self.setCheckoutUpdating(false);
				},
				error: function (xhr, textStatus, errorThrown) {
					window.location.reload();
				}
			});
		},

	};

	new WCSveaCheckout();
});
