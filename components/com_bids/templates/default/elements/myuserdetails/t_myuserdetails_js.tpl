{import_js_block}
{literal}
function myValidate(f) {

    if (document.formvalidator.isValid(f)) {
        return true;
    }

    return false;
}

function formvalidate() {
 	var form = document.auctionForm;
	// do field validation
	if (form.name.value == "") {
		alert( language["bid_err_enter_name"]);
		return false;
	} else if (form.surname.value == "") {
		alert( language["bid_err_enter_surname"] );
		return false;
	} else if (form.country.value == 0) {
		alert( language["bid_err_enter_country"]);
		return false;
	} else if (form.address.value == 0) {
		alert( language["bid_err_enter_address"] );
		return false;
	} else if (form.city.value == 0) {
		alert( language["bid_err_enter_city"] );
		return false;
	}
	return true;
 }
 {/literal}
 {/import_js_block}
