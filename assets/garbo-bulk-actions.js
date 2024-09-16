(function ($) {

	$(document).ready(function(){
		// change label of default currency for regular price
		$('#woocommerce-fields-bulk .inline-edit-group:has(input[name="_regular_price"])').addClass(['NOK', 'currency']);
		let default_regular_label = $('#woocommerce-fields-bulk .inline-edit-group:has(input[name="_regular_price"]) label .title').text();
		$('#woocommerce-fields-bulk .inline-edit-group:has(input[name="_regular_price"]) label .title').text(default_regular_label + ' NOK');

		// change label of default currency for sale price
		$('#woocommerce-fields-bulk .inline-edit-group:has(input[name="_sale_price"])').addClass(['NOK', 'currency']);
		let default_sale_label = $('#woocommerce-fields-bulk .inline-edit-group:has(input[name="_sale_price"]) label .title').text();
		$('#woocommerce-fields-bulk .inline-edit-group:has(input[name="_sale_price"]) label .title').text(default_sale_label + ' NOK');

		// move the currency fields into the general woocommerce block
		let lastIndex = $('#woocommerce-fields-bulk .inline-edit-group:has(input[name="_sale_price"])').index('#woocommerce-fields-bulk > .inline-edit-group');
		$('#garbo-fields-bulk .inline-edit-group').each(function(){
			lastIndex++;
			$('#woocommerce-fields-bulk .inline-edit-group:nth-of-type('+lastIndex+')').after($(this));
		});
		$('#garbo-fields-bulk').hide();
	});


})(jQuery);