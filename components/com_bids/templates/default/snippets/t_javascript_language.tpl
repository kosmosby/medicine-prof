{import_js_block}
{literal}
function html_entity_decode(str)
{
	//jd-tech.net
	var tarea=document.createElement('textarea');
	tarea.innerHTML = str; return tarea.value;
	tarea.parentNode.removeChild(tarea);
}
{/literal}

var language=Array();
language["bid_err_empty_bid"]=html_entity_decode('{'COM_BIDS_ERR_EMPTY_BID'|translate}');
language["bid_err_increase"]=html_entity_decode('{'COM_BIDS_ERR_INCREASE'|translate}');
language["bid_err_must_be_greater_mybid"]=html_entity_decode('{'COM_BIDS_ERR_MUST_BE_GREATER_MYBID'|translate}');
language["bid_err_must_be_greater"]=html_entity_decode('{'COM_BIDS_ERR_MUST_BE_GREATER'|translate}');
language["bid_err_terms"]=html_entity_decode('{'COM_BIDS_ERR_TERMS'|translate}');
language["bid_bid_greater_than_bin"]=html_entity_decode('{'COM_BIDS_BID_GREATER_THAN_BIN'|translate}');

language["bid_confirm_close_auction"]=html_entity_decode('{'COM_BIDS_CONFIRM_CLOSE_AUCTION'|translate}');
language["bid_err_bin_must_be_greater"]=html_entity_decode('{'COM_BIDS_ERR_BIN_MUST_BE_GREATER'|translate}');
language["bid_auction_bin_zero"]=html_entity_decode('{'COM_BIDS_AUCTION_BIN_ZERO'|translate}');
language["bid_err_title_valid"]=html_entity_decode('{'COM_BIDS_ERR_TITLE_VALID'|translate}');
language["bid_err_published_valid"]=html_entity_decode('{'COM_BIDS_ERR_PUBLISHED_VALID'|translate}');
language["bid_err_auction_type_valid"]=html_entity_decode('{'COM_BIDS_ERR_AUCTION_TYPE_VALID'|translate}');
language["bid_err_payment_valid"]=html_entity_decode('{'COM_BIDS_ERR_PAYMENT_VALID'|translate}');
language["bid_err_reserve_price_valid"]=html_entity_decode('{'COM_BIDS_ERR_RESERVE_PRICE_VALID'|translate}');
language["bid_err_min_increase_valid"]=html_entity_decode('{'COM_BIDS_ERR_MIN_INCREASE_VALID'|translate}');
language["bid_err_start_date_valid"]=html_entity_decode('{'COM_BIDS_ERR_START_DATE_VALID'|translate}');
language["bid_err_end_date_valid"]=html_entity_decode('{'COM_BIDS_ERR_END_DATE_VALID'|translate}');
language["bid_err_initial_price_valid"]=html_entity_decode('{'COM_BIDS_ERR_INITIAL_PRICE_VALID'|translate}');
language["bid_err_initial_price_zero"]=html_entity_decode('{'COM_BIDS_ERR_INITIAL_PRICE_ZERO'|translate}');
language["bin_js_alert"]=html_entity_decode('{'COM_BIDS_BIN_JS_ALERT'|translate}');
language["bin_js_alert_q"]=html_entity_decode('{'COM_BIDS_BIN_JS_ALERT_Q'|translate}');
language["bid_maxpp"]=html_entity_decode('{'COM_BIDS_MAXPP'|translate}');
language["bid_bid_price"]=html_entity_decode('{'COM_BIDS_BID_PRICE'|translate}');
language["bid_err_max_valability"]=html_entity_decode('{'COM_BIDS_NOT_VALID_DATE_INTERVAL'|translate} : {$bidCfg->bid_opt_availability}');
language["bid_err_picture_is_required"]=html_entity_decode('{'COM_BIDS_ERR_PICTURE_IS_REQUIRED'|translate}');

language["bid_initial_price"]=html_entity_decode('{'COM_BIDS_INITIAL_PRICE'|translate}');
language["bid_tab_curreny"]=html_entity_decode('{'COM_BIDS_TAB_CURRENY'|translate}');
language["bid_err_empty_suggest"]=html_entity_decode('{'COM_BIDS_ERR_EMPTY_SUGGEST'|translate}');
language["bid_choose_category"]=html_entity_decode('{'COM_BIDS_CHOOSE_CATEGORY'|translate}');

//1.7.0
language["bid_fill_in_required"]=html_entity_decode('{'COM_BIDS_FILL_IN_REQUIRED'|translate}');
language["bid_err_price"]=html_entity_decode('{'COM_BIDS_ERR_PRICE'|translate}');

language["bid_more"]=html_entity_decode('{'COM_BIDS_MORE'|translate}');
language["bid_fewer"]=html_entity_decode('{'COM_BIDS_FEWER'|translate}');

language["bid_err_enter_name"]=html_entity_decode('{'COM_BIDS_ERR_ENTER_NAME'|translate}');
language["bid_err_enter_surname"]=html_entity_decode('{'COM_BIDS_ERR_ENTER_SURNAME'|translate}');
language["bid_err_enter_country"]=html_entity_decode('{'COM_BIDS_ERR_ENTER_COUNTRY'|translate}');
language["bid_err_enter_address"]=html_entity_decode('{'COM_BIDS_ERR_ENTER_ADDRESS'|translate}');
language["bid_err_enter_city"]=html_entity_decode('{'COM_BIDS_ERR_ENTER_CITY'|translate}');
language["bid_must_rate"]=html_entity_decode('{'COM_BIDS_MUST_RATE'|translate}');
language["bid_are_sure_bid"]=html_entity_decode('{'COM_BIDS_ARE_SURE_BID'|translate}');
language["bid_err_fields_compulsory"]=html_entity_decode('{'COM_BIDS_ERR_FIELDS_COMPULSORY'|translate}');

var JS_ROOT_HOST='{$ROOT_HOST}';


var days='{'COM_BIDS_DAYS'|translate},';
var expired='{'COM_BIDS_EXPIRED'|translate}';
var auctionClosed='{'COM_BIDS_CLOSED'|translate}';

var bid_max_availability={$bidCfg->bid_opt_availability};
var bid_date_format='{$bidCfg->bid_opt_date_format}';

if(typeof Calendar != "undefined")
	Calendar._TT["DEF_DATE_FORMAT"]=dateformat('{$bidCfg->bid_opt_date_format}');

{literal}
function dateformat(php_format)
{
    d='y-mm-dd';
    if (php_format=='Y-m-d') d='y-mm-dd';
    if (php_format=='Y-d-m') d='y-dd-mm';
    if (php_format=='m/d/Y') d='mm/dd/y';
    if (php_format=='d/m/Y') d='dd/mm/y';
    if (php_format=='D, F d Y') d='y-mm-dd';

    return d;
}

function termsAlert(){
	var txt_info = language['bid_err_terms'];
	alert(txt_info);
}

{/literal}
{/import_js_block}
