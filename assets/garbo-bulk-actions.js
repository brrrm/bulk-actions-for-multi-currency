(function ($) {
	console.log( 'ready');
	$(document).ready(function(){
		$('#woocommerce-fields-bulk .inline-edit-group:has(input[name="_regular_price"])').addClass(['NOK', 'currency']);
		let default_regular_label = $('#woocommerce-fields-bulk .inline-edit-group:has(input[name="_regular_price"]) label .title').text();
		$('#woocommerce-fields-bulk .inline-edit-group:has(input[name="_regular_price"]) label .title').text(default_regular_label + ' NOK');

		$('#woocommerce-fields-bulk .inline-edit-group:has(input[name="_sale_price"])').addClass(['NOK', 'currency']);
		let default_sale_label = $('#woocommerce-fields-bulk .inline-edit-group:has(input[name="_sale_price"]) label .title').text();
		$('#woocommerce-fields-bulk .inline-edit-group:has(input[name="_sale_price"]) label .title').text(default_sale_label + ' NOK');


		let lastIndex = $('#woocommerce-fields-bulk .inline-edit-group:has(input[name="_sale_price"])').index('#woocommerce-fields-bulk > .inline-edit-group');
		$('#garbo-fields-bulk .inline-edit-group').each(function(){
			console.log(lastIndex);
			lastIndex++;
			$('#woocommerce-fields-bulk .inline-edit-group:nth-of-type('+lastIndex+')').after($(this));
		});
		$('#garbo-fields-bulk').hide();
	});
	$(document).on(
		'change',
		'#garbo-fields-bulk .inline-edit-group .change_to',
		function() {
			console.log('call!');
			if ( 0 < $( this ).val() ) {
				$( this ).closest( 'div' ).find( '.change-input' ).show();
			} else {
				$( this ).closest( 'div' ).find( '.change-input' ).hide();
			}

		}
	);


})(jQuery);