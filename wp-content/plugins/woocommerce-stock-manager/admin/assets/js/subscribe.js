// For Klawoo subscribe
jQuery(function () {
	jQuery("form[name=wsm_klawoo_subscribe]").on( 'submit', function (e) {
		e.preventDefault();
		var formData = {};
		jQuery.each(jQuery('form[name=wsm_klawoo_subscribe]').serializeArray(), function() {
			formData[this.name] = this.value;
		});

		var data = {
			action : 'wsm_klawoo_subscribe',
			params : formData,
		};

		jQuery.post( ajaxurl, data, function( response ) {
			var result = jQuery(response);
			if ( result.find("h2").text() == "You\'re subscribed!" ) {
				jQuery("td[id=wsm_promo_msg_content]").html("<div class='wsm_success'>Thank you for subscribing!!!</div>");
			}

		});
	});
});