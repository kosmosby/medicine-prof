{import_js_block}
{literal}
	function payPalPay(bussiness, item_name, item_number, invoice, amount, quantity, url_return, currency_code){
		document.getElementById('BIDS_business').value=bussiness;
		document.getElementById('BIDS_item_name').value=item_name;
		document.getElementById('BIDS_item_number').value=item_number;
		document.getElementById('BIDS_invoice').value=invoice;
		document.getElementById('BIDS_amount').value=amount;
		document.getElementById('BIDS_quantity').value=quantity;		
		document.getElementById('BIDS_url_return').value=url_return;		
		document.getElementById('BIDS_currency_code').value=currency_code;		
		document.BIDS_paypalForm.submit();		
	}    						                                              
{/literal}
{/import_js_block}
