jQuery(function ($) {
	$('#the-list').on('click', '.editinline', function () {

		/**
		 * Extract metadata and put it as the value for the custom field form
		 */
		inlineEditPost.revert();

		var post_id = $(this).closest('tr').attr('id');

		post_id = post_id.replace("post-", "");

		var $fortnox_inline_data = $('#fortnox_inline_' + post_id),
        $wc_inline_data = $('#woocommerce_inline_' + post_id);

		$('input[name="_fortnox_manufacturer"]', '.inline-edit-row').val($fortnox_inline_data.find(".fortnox_manufacturer").text());
        $('input[name="_fortnox_manufacturer_article_number"]', '.inline-edit-row').val($fortnox_inline_data.find(".fortnox_manufacturer_article_number").text());
        $('input[name="_fortnox_purchase_price"]', '.inline-edit-row').val($fortnox_inline_data.find(".fortnox_purchase_price").text());
        $('input[name="_fortnox_stock_place"]', '.inline-edit-row').val($fortnox_inline_data.find(".fortnox_stock_place").text());
        $('input[name="_fortnox_unit"]', '.inline-edit-row').val($fortnox_inline_data.find(".fortnox_unit").text());
        $('input[name="_fortnox_barcode"]', '.inline-edit-row').val($fortnox_inline_data.find(".fortnox_barcode").text());
		/**
		 * Only show custom field for appropriate types of products (simple and variable)
		 */
		var product_type = $wc_inline_data.find('.product_type').text();

		if (product_type == 'simple') {
            $('.fortnox_fields').show();
		} else {
            $('.fortnox_fields').hide();
		}

	});
});