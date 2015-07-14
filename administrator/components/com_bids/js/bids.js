
// Settings functions
function addCurreny()
{
	el=document.getElementById('temp_currency');
    if(!el.value) {
        return;
    }
	el2=document.getElementById('currency_list');

	var elOptNew = document.createElement('option');
	elOptNew.text = el.value.toUpperCase();
	elOptNew.value = el.value.toUpperCase();
	try {
		el2.add(elOptNew, null); // standards compliant; doesn't work in IE
	}
	catch(ex) {
		el2.add(elOptNew); // IE only
	}
	el.value='';

}
function delCurrency()
{
	var elSel = document.getElementById('currency_list');
	var i;
	for (i = elSel.length - 1; i>=0; i--) {
		if (elSel.options[i].selected) {
			elSel.remove(i);
		}
	}
}

var MONTH_NAMES=new Array('January','February','March','April','May','June','July','August','September','October','November','December','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
var DAY_NAMES=new Array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sun','Mon','Tue','Wed','Thu','Fri','Sat');

function LZ(x) {return(x<0||x>9?"":"0")+x}

function formatDate(format) {
	date = new Date()
	format=format+"";
	var result="";
	var i_format=0;
	var c="";
	var token="";
	var y=date.getYear()+"";
	var M=date.getMonth()+1;
	var d=date.getDate();
	var E=date.getDay();
	var H=date.getHours();
	var m=date.getMinutes();
	var s=date.getSeconds();
	var yyyy,yy,MMM,MM,dd,hh,h,mm,ss,ampm,HH,H,KK,K,kk,k;
	// Convert real date parts into formatted versions
	var value=new Object();
	if (y.length < 4) {y=""+(y-0+1900);}
	value["y"]=""+y;
	value["yyyy"]=y;
	value["yy"]=y.substring(2,4);
	value["M"]=M;
	value["MM"]=LZ(M);
	value["MMM"]=MONTH_NAMES[M-1];
	value["NNN"]=MONTH_NAMES[M+11];
	value["d"]=d;
	value["dd"]=LZ(d);
	value["E"]=DAY_NAMES[E+7];
	value["EE"]=DAY_NAMES[E];
	value["H"]=H;
	value["HH"]=LZ(H);
	if (H==0){value["h"]=12;}
	else if (H>12){value["h"]=H-12;}
	else {value["h"]=H;}
	value["hh"]=LZ(value["h"]);
	if (H>11){value["K"]=H-12;} else {value["K"]=H;}
	value["k"]=H+1;
	value["KK"]=LZ(value["K"]);
	value["kk"]=LZ(value["k"]);
	if (H > 11) { value["a"]="PM"; }
	else { value["a"]="AM"; }
	value["m"]=m;
	value["mm"]=LZ(m);
	value["s"]=s;
	value["ss"]=LZ(s);
	while (i_format < format.length) {
		c=format.charAt(i_format);
		token="";
		while ((format.charAt(i_format)==c) && (i_format < format.length)) {

			token += format.charAt(i_format++);
		}
		if (value[token] != null) { result=result + value[token]; }
		else { result=result + token; }
	}
	return result;
}

function showDateInFormat(format){
	var buf_format;
	//var now = new Date();

	switch(format){
		case'Y-m-d':
		buf_format = "yyyy-MM-dd";
		break;
		case'Y-d-m':
		buf_format = "yyyy-dd-MM";
		break;
		case'm/d/Y':
		buf_format = "MM/dd/yyyy";
		break;
		case'd/m/Y':
		buf_format = "dd/MM/yyyy";
		break;
		case'D, F d Y':
		buf_format = "E, MMM dd yyyy";
		break;
	}

	document.getElementById('datef').innerHTML = formatDate(buf_format);
}

function showTimeInFormat(formatt){
	var buf_format;

	switch(formatt){
		case'H:i':
		buf_format = "HH:mm";
		break;
		case'h:iA':
		buf_format = "h:mm a";
		break;
	}
	document.getElementById('timef').innerHTML = formatDate(buf_format);
}

// Joomla  Submitbutton overridden
function submitbutton(action) {
	if (action=='savesettings'){

		var elSel = document.getElementById('currency_list');
		if ( elSel.length<=0){
			alert('Empty Currency List!');
			return;
		}
		var i;
		document.adminForm.currency.value='';
		for (i =0 ; i< elSel.length; i++) {
			document.adminForm.currency.value+=elSel.options[i].value+'~';
		}

		var elSelP = document.getElementById('payment_list');
		if ( elSelP.length<=0){
			alert('Empty Payment List!');
			return;
		}

		var j;
		document.adminForm.payment.value='';
		for (j =0 ; j < elSelP.length; j++) {
			document.adminForm.payment.value+=elSelP.options[j].value+'~';
		}


	}

	submitform(action);
}


function toggleMailCaptcha(val){
	document.getElementById('tab_joomla').style.display='none';
	document.getElementById('tab_smarty').style.display='none';
	document.getElementById('tab_recaptcha').style.display='none';
	if($("tab_"+val))
		document.getElementById("tab_"+val).style.display='';
}

function addPayment(){
	el=document.getElementById('temp_payment'); el2=document.getElementById('payment_list');
	var elOptNew = document.createElement('option');
	elOptNew.text = el.value;
	elOptNew.value = el.value;
	try {
		el2.add(elOptNew, null);
	}catch(ex) {
		el2.add(elOptNew);
	}
	el.value='';
}
function delPayment(){var elSel = document.getElementById('payment_list'); var i;for (i = elSel.length - 1; i>=0; i--) { if (elSel.options[i].selected) { elSel.remove(i);}}}
function toggle_acl(){
	frm=document.adminForm;
	var acl=frm.enable_acl.value;
	if (acl==0){
		frm.acl_type.disabled=true;
		frm.bidder_group.disabled=true;
		frm.seller_group.disabled=true;
	}else{
		frm.acl_type.disabled=false;
		frm.bidder_group.disabled=false;
		frm.seller_group.disabled=false;
	}
}

function getCheckedValue(radioObj){ if(!radioObj) return "";var radioLength = radioObj.length;if(radioLength == undefined) if(radioObj.checked) return radioObj.value; else return ""; for(var i = 0; i < radioLength; i++) { if(radioObj[i].checked) { return radioObj[i].value; } }return "";}

function change_date_settings(rb_date){

	val=getCheckedValue(rb_date);
	frm=document.adminForm;

	if (val==1){
		frm.bid_opt_enable_hour.disabled=false;
		frm.bid_opt_default_period.disabled=true;
		frm.bid_opt_proxy_default_period.disabled=true;
		frm.bid_opt_bin_default_period.disabled=true;
		frm.bid_opt_reserve_price_default_period.disabled=true;
	}else{
		frm.bid_opt_enable_hour.disabled=true;
		frm.bid_opt_default_period.disabled=false;
		frm.bid_opt_proxy_default_period.disabled=false;
		frm.bid_opt_bin_default_period.disabled=false;
		frm.bid_opt_reserve_price_default_period.disabled=false;
	}
}

function toggleVisibilityByRadiobox(id,radio,invert) {
    if(typeof invert != 'undefined') {
        $(id).style.display = getCheckedValue(radio)==1 ? 'none' : '';
    } else {
        $(id).style.display = getCheckedValue(radio)==1 ? '' : 'none';
    }
}

function curSelect(sel){
	if( sel==1 ){
		document.getElementById("bin_opt_limit_suggestions").disabled = false;
	} else {
		document.getElementById("bin_opt_limit_suggestions").disabled = "disabled";
	}
}

function toggleElement(elId) {
    var el = document.getElementById(elId);
    if(el.style.display=='none') {
        el.style.display = '';
    } else {
        el.style.display = 'none';
    }
}

function textarea2tinymce(htmlId) {
    if (typeof tinymce == 'undefined') {
        return;
    }
    tinyMCE.execCommand('mceAddControl', false, htmlId);
}

function checkPriceSeparators() {
    var dec = document.getElementById('bid_decimal_separator');
    var thd = document.getElementById('bid_thousand_separator');
    if(dec.value==thd.value) {
        dec.value = ',';
        thd.value = '.';
    }
}