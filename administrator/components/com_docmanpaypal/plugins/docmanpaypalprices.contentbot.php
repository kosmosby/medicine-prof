<?php
//error_reporting(0);
defined('_JEXEC') or die('Restricted access');


require_once(JPATH_ADMINISTRATOR . '/components/com_docmanpaypal/docmanpaypal.class.php');

$lang = JFactory::getLanguage();
$l = substr($lang->_lang,0,2);
$langFile = 'components/com_docmanpaypal/lang/' . $l . '.php';
if (file_exists($langFile)) {
	include_once($langFile);
} else {
	include_once JPATH_ROOT . '/components/com_docmanpaypal/lang/en.php';
}


//if (file_exists($lang_file)) {
//	include_once($lang_file);
//} else {
//	$l = 'en-GB';
//}

JApplication::registerEvent( 'onFetchButtons', 'bot_docmanpaypalprices2' );
function bot_docmanpaypalprices2($params) {
    global $mainframe, $_DOCMAN, $_DMUSER, $currency_symbols;
	$database =& JFactory::getDBO();
	$my = JFactory::getUser();
    require_once($_DOCMAN->getPath('classes', 'button'));
    require_once($_DOCMAN->getPath('classes', 'token'));
    $_DOCMAN->loadLanguage('frontend');
    $doc        = & $params['doc'];
    $file       = & $params['file'];
    $objDBDoc   = $doc->objDBTable;
	$buttonStaysBuyNow = false;
	
    $objFormatData   = $doc->objFormatData;

    if ($objFormatData->id > 0) {
	    $currency = docmanpaypal::cfg('currency');
	    
	    $gid = $objFormatData->id;
	    $item = docmanpaypal::getItem($gid);
	    
	    $database->setQuery("select price from #__docmanpaypal where id = '$gid' limit 1;");
	    $price = $database->loadResult();
	    
	    $database->setQuery("select dmdescription from #__docman where id = $gid limit 1");
	    $dmdescription = $database->loadResult();
	    
	    $document_price = number_format($price,2);
		//free fownloads for user in specific groups
                if ($my->id > 0) {
                        $tmp = docmanpaypal::cfg('free_for_usertypes');
                        $tmp = explode(',',$tmp);
                        if (in_array($my->usertype,$tmp)) {
                                $price = 0;
                        }
                        if (docmanpaypal::isInDOCmanFreeDownloadGroup()) {
                                $price = 0;
                        }
                        //die($price);
                }
		//free fownloads for user in specific groups END
		if ($price > 0) {
			$document_price = number_format($price,2);
			$priceFormat = docmanpaypal::cfg('priceFormat');
			$priceFormatted = str_replace(array('%priceText%','%price%','%currency%'),array(_DMP_PRICE,$document_price,$currency),$priceFormat);
			$objFormatData->dmdescription = $priceFormatted . $dmdescription;
			$objFormatData->dmdescription = str_replace('%price%',$currency_symbols[$currency] . number_format($price,2),$objFormatData->dmdescription);
			//$objFormatData->dmdescription = '<div id="DMP_PRICE_DIV"><span class="DMP_PRICE">' . _DMP_PRICE . '</span> <span class="DMP_PRICE_VALUE">' . $document_price . '</span> <span class="DMP_PRICE_CURRENCY">' . $currency . '</span></div>' . $dmdescription;
		}
	}
    
    $botParams  = bot_docmanpaypalpricesParams2();
    //print_r($botParams);
    $js = "javascript:if(confirm('"._DML_ARE_YOU_SURE."')) {window.location='%s'}";
    $buttons = array();

    $tmp = docmanpaypal::cfg('moreThanOnce');
	if ($tmp > 0 && $my->id > 0) {
		unset($tmp);
	    $database->setQuery("select order_id from #__docmanpaypalorders where buyer_id = " . $my->id . " and file_id = $gid OR file_id like '$gid,%' or file_id like '%,$gid,%' or file_id like '%,$gid' and completed = 1 order by order_id desc limit 1");
	    //die($database->getQuery());
	    $tmp = $database->loadResult();
	    if ($tmp > 0) {
			$database->setQuery("select downloads from #__docmanpaypaldownloads where order_id = $tmp and id = $gid order by order_id desc limit 1");
			//die($database->getQuery());
			$downloads = $database->loadResult();
			
			if ($downloads < $item->downloadslimit && is_null($downloads) == false) {
		    	$price = 0;
			}
	    	$buttonStaysBuyNow = docmanpaypal::cfg('buttonStaysBuyNow');
	    }
	}

	//offline good update
	if ($item->offlineGood > 0) {
		$price = $item->price;
	}
	//
	if ($botParams->get('priceInBuyNow',1)) {
		$priceInBuyNow = ' (' . $currency_symbols[$currency] . number_format($price,2) . ')';
	}
	if ($botParams->get('priceInTitle',1) && $price > 0) {
		$objFormatData->dmname .= ' - ' . $currency_symbols[$currency] . number_format($price,2);
	}
	

	if ($price > 0) {
	    if ($_DMUSER->canDownload($objDBDoc) AND $botParams->get('download', 1)) {
	    	if (docmanpaypal::cfg('useCart') > 0) {
	    		$buttons['addToCart'] = new DOCMAN_Button('addToCart', _DMP_ADD_TO_CART, 'javascript:addToCart(' . $gid . ');');
	    	}
			if ($botParams->get('buynow',1)) {
		    	$buttons['download'] = new DOCMAN_Button('download', _DMP_BUY_NOW . $priceInBuyNow, $doc->_formatLink('doc_download'));
			}
	    }
	} else {
		//die('shit');
	    if ($_DMUSER->canDownload($objDBDoc) AND $botParams->get('download', 1) AND $buttonStaysBuyNow == 0) {
	        $buttons['download'] = new DOCMAN_Button('download', _DML_BUTTON_DOWNLOAD, $doc->_formatLink('doc_download'));
	    }
	    if ($_DMUSER->canDownload($objDBDoc) AND $botParams->get('download', 1) AND $buttonStaysBuyNow == 1) {
	        $buttons['download'] = new DOCMAN_Button('download', _DMP_BUY_NOW . $priceInBuyNow, $doc->_formatLink('doc_download'));
	    }
	}
	
	
	//pdfPreview option
	if (pathinfo($objFormatData->dmfilename,PATHINFO_EXTENSION) == 'pdf' && docmanpaypal::cfg('pdfPreviewPages') > 0 && $price > 0) {
		$buttons['pdfPreview'] = new DOCMAN_Button('pdfPreview', _DMP_PDFPREVIEWBUTTON, JRoute::_('index.php?&option=com_docmanpaypal&task=pdfPreview&id=' . $gid));
	}
	//
	//custom buttons
	if ($item->buttons != '' && strpos($item->buttons,'|')) {
		$tmp = str_replace("\r","",$item->buttons);
		$tmp = explode("\n",$tmp);
		foreach ($tmp as $tmp) {
			if (strpos($tmp,"|")) {
				$t = explode("|",$tmp);
				$btn++;
				if (count($t) > 1) {
					$buttons['btn' . $btn] = new DOCMAN_Button('btn' . $btn, $t[0], JRoute::_($t[1]));
				}
			}
		}
	}
	//
    if ($_DMUSER->canDownload($objDBDoc) AND $botParams->get('view', 1) && $price == 0) {
        $viewtypes = trim($_DOCMAN->getCfg('viewtypes'));
        if ($viewtypes != '' && ($viewtypes == '*' || stristr($viewtypes, $file->ext))) {
            $link = $doc->_formatLink('doc_view', null, true, 'index2.php');
            $params = new DMmosParameters('popup=1');
            $buttons['view'] = new DOCMAN_Button('view', _DML_BUTTON_VIEW, $link, $params);
        }
    }
    if($botParams->get('details', 1)) {
        $buttons['details'] = new DOCMAN_Button('details', _DML_BUTTON_DETAILS, $doc->_formatLink('doc_details'));
    }
    if ($_DMUSER->canEdit($objDBDoc) AND $botParams->get('edit', 1)) {
        $buttons['edit'] = new DOCMAN_Button('edit', _DML_BUTTON_EDIT, $doc->_formatLink('doc_edit'));
    }
    if ($_DMUSER->canMove($objDBDoc) AND $botParams->get('move', 1)) {
        $buttons['move'] = new DOCMAN_Button('move', _DML_BUTTON_MOVE, $doc->_formatLink('doc_move'));
    }
    if ($_DMUSER->canDelete($objDBDoc) AND $botParams->get('delete', 1)) {
        $link = $doc->_formatLink('doc_delete', null, null, null, true);
        $buttons['delete'] = new DOCMAN_Button('delete', _DML_BUTTON_DELETE, sprintf($js, $link));
    }
    if ($_DMUSER->canUpdate($objDBDoc) AND $botParams->get('update', 1)) {
        $buttons['update'] = new DOCMAN_Button('update', _DML_BUTTON_UPDATE, $doc->_formatLink('doc_update'));
    }
    if ($_DMUSER->canReset($objDBDoc) AND $botParams->get('reset', 1)) {
        $buttons['reset'] = new DOCMAN_Button('reset', _DML_BUTTON_RESET, sprintf($js, $doc->_formatLink('doc_reset')));
    }
    if ($_DMUSER->canCheckin($objDBDoc) AND $objDBDoc->checked_out AND $botParams->get('checkout', 1)) {
        $params = new DMmosParameters('class=checkin');
        $buttons['checkin'] = new DOCMAN_Button('checkin', _DML_BUTTON_CHECKIN, $doc->_formatLink('doc_checkin'), $params);
    }
    if ($_DMUSER->canCheckout($objDBDoc) AND !$objDBDoc->checked_out AND $botParams->get('checkout', 1)) {
        $buttons['checkout'] = new DOCMAN_Button('checkout', _DML_BUTTON_CHECKOUT, $doc->_formatLink('doc_checkout'));
    }
    if ($_DMUSER->canApprove($objDBDoc) AND !$objDBDoc->approved AND $botParams->get('approve', 1)) {
        $params = new DMmosParameters('class=approve');
        $link   = $doc->_formatLink('doc_approve', null, null, null, true);
        $buttons['approve'] = new DOCMAN_Button('approve', _DML_BUTTON_APPROVE, $link, $params);
    }
    if ($_DMUSER->canPublish($objDBDoc) AND $botParams->get('publish', 1)) {
        $params = new DMmosParameters('class=publish');
        $link   = $doc->_formatLink('doc_publish', null, null, null, true);
        $buttons['publish'] = new DOCMAN_Button('publish', _DML_BUTTON_PUBLISH, $link, $params);
    }
    if ($_DMUSER->canUnPublish($objDBDoc) AND $botParams->get('publish', 1)) {
        $link   = $doc->_formatLink('doc_unpublish', null, null, null, true);
        $buttons['unpublish'] = new DOCMAN_Button('unpublish', _DML_BUTTON_UNPUBLISH, $link);
    }
    //print_r($buttons);
    return $buttons;
}
function bot_docmanpaypalpricesParams2() {
    global $_MAMBOTS;
    $database =& JFactory::getDBO();
    $dbtable = '#__plugins';
    if(defined('_DM_J15')) {
    	$dbtable = '#__plugins';
    }
	// check if param query has previously been processed
    if ( !isset($_MAMBOTS->_docman_mambot_params['docmanpaypalprices']) ) {
        // load mambot params info
        $query = "SELECT params"
        . "\n FROM $dbtable"
        . "\n WHERE element = 'docmanpaypalprices.contentbot'"
        . "\n AND folder = 'docman'"
        ;
        $database->setQuery( $query );
        $params = $database->loadResult();
        // save query to class variable
        if (isset($params)) {
        	$_MAMBOTS->_docman_mambot_params['docmanpaypalprices.contentbot'] = $params;
        }
    }
    // pull query data from class variable
    @$botParams = new JParameter(  $params );
    return $botParams;
}
?>