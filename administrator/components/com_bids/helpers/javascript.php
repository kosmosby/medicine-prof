<?php

defined('_JEXEC') or die('Restrited access.');

$bidCfg = BidsHelperTools::getConfig();

$lang = JFactory::getLanguage();
$lang->load('com_bids', JPATH_SITE);

?>
function html_entity_decode(str)
{
    //jd-tech.net
    var tarea=document.createElement('textarea');
    tarea.innerHTML = str; return tarea.value;
    tarea.parentNode.removeChild(tarea);
}


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


var language=Array();
language["bid_err_empty_bid"]=html_entity_decode('<?php echo JText::_('COM_BIDS_ERR_EMPTY_BID'); ?>');
language["bid_err_increase"]=html_entity_decode('<?php echo JText::_('COM_BIDS_ERR_INCREASE'); ?>');
language["bid_err_must_be_greater_mybid"]=html_entity_decode('<?php echo JText::_('COM_BIDS_ERR_MUST_BE_GREATER_MYBID'); ?>');
language["bid_err_must_be_greater"]=html_entity_decode('<?php echo JText::_('COM_BIDS_ERR_MUST_BE_GREATER'); ?>');
language["bid_err_terms"]=html_entity_decode('<?php echo JText::_('COM_BIDS_ERR_TERMS'); ?>');
language["bid_bid_greater_than_bin"]=html_entity_decode('<?php echo JText::_('COM_BIDS_BID_GREATER_THAN_BIN'); ?>');

language["bid_confirm_close_auction"]=html_entity_decode('<?php echo JText::_('COM_BIDS_CONFIRM_CLOSE_AUCTION'); ?>');
language["bid_err_bin_must_be_greater"]=html_entity_decode('<?php echo JText::_('COM_BIDS_ERR_BIN_MUST_BE_GREATER'); ?>');
language["bid_auction_bin_zero"]=html_entity_decode('<?php echo JText::_('COM_BIDS_AUCTION_BIN_ZERO'); ?>');
language["bid_err_title_valid"]=html_entity_decode('<?php echo JText::_('COM_BIDS_ERR_TITLE_VALID'); ?>');
language["bid_err_published_valid"]=html_entity_decode('<?php echo JText::_('COM_BIDS_ERR_PUBLISHED_VALID'); ?>');
language["bid_err_auction_type_valid"]=html_entity_decode('<?php echo JText::_('COM_BIDS_ERR_AUCTION_TYPE_VALID'); ?>');
language["bid_err_payment_valid"]=html_entity_decode('<?php echo JText::_('COM_BIDS_ERR_PAYMENT_VALID'); ?>');
language["bid_err_reserve_price_valid"]=html_entity_decode('<?php echo JText::_('COM_BIDS_ERR_RESERVE_PRICE_VALID'); ?>');
language["bid_err_min_increase_valid"]=html_entity_decode('<?php echo JText::_('COM_BIDS_ERR_MIN_INCREASE_VALID'); ?>');
language["bid_err_start_date_valid"]=html_entity_decode('<?php echo JText::_('COM_BIDS_ERR_START_DATE_VALID'); ?>');
language["bid_err_end_date_valid"]=html_entity_decode('<?php echo JText::_('COM_BIDS_ERR_END_DATE_VALID'); ?>');
language["bid_err_initial_price_valid"]=html_entity_decode('<?php echo JText::_('COM_BIDS_ERR_INITIAL_PRICE_VALID'); ?>');
language["bid_err_initial_price_zero"]=html_entity_decode('<?php echo JText::_('COM_BIDS_ERR_INITIAL_PRICE_ZERO'); ?>');
language["bin_js_alert"]=html_entity_decode('<?php echo JText::_('COM_BIDS_BIN_JS_ALERT'); ?>');
language["bin_js_alert_q"]=html_entity_decode('<?php echo JText::_('COM_BIDS_BIN_JS_ALERT_Q'); ?>');
language["bid_maxpp"]=html_entity_decode('<?php echo JText::_('COM_BIDS_MAXPP'); ?>');
language["bid_bid_price"]=html_entity_decode('<?php echo JText::_('COM_BIDS_BID_PRICE'); ?>');
language["bid_err_max_valability"]=html_entity_decode('<?php echo JText::_('COM_BIDS_NOT_VALID_DATE_INTERVAL'); ?>: <?php echo $bidCfg->bid_opt_availability; ?>');
language["bid_err_picture_is_required"]=html_entity_decode('<?php echo JText::_('COM_BIDS_ERR_PICTURE_IS_REQUIRED'); ?>');

language["bid_initial_price"]=html_entity_decode('<?php echo JText::_('COM_BIDS_INITIAL_PRICE'); ?>');
language["bid_tab_curreny"]=html_entity_decode('<?php echo JText::_('COM_BIDS_TAB_CURRENY'); ?>');
language["bid_err_empty_suggest"]=html_entity_decode('<?php echo JText::_('COM_BIDS_ERR_EMPTY_SUGGEST'); ?>');
language["bid_choose_category"]=html_entity_decode('<?php echo JText::_('COM_BIDS_CHOOSE_CATEGORY'); ?>');

//1.7.0
language["bid_fill_in_required"]=html_entity_decode('<?php echo JText::_('COM_BIDS_FILL_IN_REQUIRED'); ?>');
language["bid_err_price"]=html_entity_decode('<?php echo JText::_('COM_BIDS_ERR_PRICE'); ?>');

language["bid_more"]=html_entity_decode('<?php echo JText::_('COM_BIDS_MORE'); ?>');
language["bid_fewer"]=html_entity_decode('<?php echo JText::_('COM_BIDS_FEWER'); ?>');

language["bid_err_enter_name"]=html_entity_decode('<?php echo JText::_('COM_BIDS_ERR_ENTER_NAME'); ?>');
language["bid_err_enter_surname"]=html_entity_decode('<?php echo JText::_('COM_BIDS_ERR_ENTER_SURNAME'); ?>');
language["bid_err_enter_country"]=html_entity_decode('<?php echo JText::_('COM_BIDS_ERR_ENTER_COUNTRY'); ?>');
language["bid_err_enter_address"]=html_entity_decode('<?php echo JText::_('COM_BIDS_ERR_ENTER_ADDRESS'); ?>');
language["bid_err_enter_city"]=html_entity_decode('<?php echo JText::_('COM_BIDS_ERR_ENTER_CITY'); ?>');
language["bid_must_rate"]=html_entity_decode('<?php echo JText::_('COM_BIDS_MUST_RATE'); ?>');
language["bid_are_sure_bid"]=html_entity_decode('<?php echo JText::_('COM_BIDS_ARE_SURE_BID'); ?>');
language["bid_err_fields_compulsory"]=html_entity_decode('<?php echo JText::_('COM_BIDS_ERR_FIELDS_COMPULSORY'); ?>');

var JS_ROOT_HOST='{$ROOT_HOST}';


var days='<?php echo JText::_('COM_BIDS_DAYS'); ?>,';
var expired='<?php echo JText::_('COM_BIDS_EXPIRED'); ?>';
var auctionClosed='<?php echo JText::_('COM_BIDS_CLOSED'); ?>';

var bid_max_availability='<?php echo $bidCfg->bid_opt_availability; ?>';
var bid_date_format='<?php echo $bidCfg->bid_opt_date_format; ?>';

Joomla.submitbutton = function (pressbutton) {

    var frm = document.adminForm;
    frm.task.value = pressbutton;

    switch(pressbutton) {
        case 'saveauction':

            if(validateForm(frm)) {
                frm.submit();
            }
            break;
        default:
            Joomla.submitform(pressbutton);
            break;
    }
}

window.addEvent('domready', function(){
    if(typeof Calendar != "undefined") {
        Calendar._TT["DEF_DATE_FORMAT"]=dateformat('<?php echo $bidCfg->bid_opt_date_format; ?>');
    }
});