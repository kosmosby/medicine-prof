<?php
error_reporting(0);

defined('_JEXEC') or die('Access Denied');
if (!defined('DS')) { define('DS',DIRECTORY_SEPARATOR); }


$my = &JFactory::getUser();
$lang = JFactory::getLanguage();
$l = substr($lang->_lang,0,2);
$langFile = JPATH_COMPONENT . DS .'lang'. DS . $l . '.php';

if (file_exists($langFile)) {
	include_once($langFile);
} else {
	include_once JPATH_COMPONENT . DS .'lang'. DS .'en.php';
}

$option = 'com_docmanpaypal';


$task = JRequest::getVar('task');
require_once("administrator/components/com_docmanpaypal/docmanpaypal.class.php");
$componentdir = '/administrator' . $componentdir;
$database = JFactory::getDBO();
$dm = new docmanpaypal();
if (!$dm->constructRun) {
	$dm->__construct();
}
$cfg = $dm->getConfig();
$document = JFactory::getDocument();


switch ($task) {
	case "order":
		if ($dm->cfg['requireRegistration'] > 0) {
			if ($my->id == 0) {
				$mainframe->redirect('index.php',_DMP_YOUNEEDTOLOGIN);
			}
		}
		$cfg = $dm->getConfig();
		$mode = JRequest::getVar('mode','single');
		echo $cfg['merchant_header'];
?>
<form action="<?php echo JURI::base(); ?>" method="GET">
<table border="0">
<?php
$merchants = explode(',',$cfg['merchants']);
//print_r($merchants);
if (count($merchants) > 1) {
	foreach ($merchants as $m) {
		$ml = strtolower($m);
		echo "\r\n<tr><td valign=\"middle\"><input type=\"radio\" name=\"merchant\" value=\"$ml\" id=\"$ml\" /></td><td><span class=\"merchant_$ml\">$m</span></td></tr>";
	}
	echo "
<script>
document.getElementsByName('merchant')[0].checked = 'checked';
</script>
	";
} else if (count($merchants) == 1) {
		$ml = strtolower($merchants[0]);
		if (JRequest::getVar('mode') == 'cart') {
			$session = JFactory::getSession();
			$cart = $session->get('cart');
			if ($cart == null) {
				$mainframe->redirect('index.php',_DMP_CANT_SUBMIT_EMTPY_CART);
			}
		}
		switch ($merchants[0]) {
			case "PayPal":
				$mainframe->redirect(JURI::base() . "index.php?option=com_docmanpaypal&task=submit_order&mode=$mode&id=" . JRequest::getVar('id'));
				break;
/*			case "Moneybookers":
				$mainframe->redirect(JURI::base() . "index.php?option=com_docmanpaypal&task=submit_order&mode=$mode&merchant=$ml&id=" . JRequest::getVar('id'));
				break;
			case "Netcash.co.za":
				$mainframe->redirect(JURI::base() . "index.php?option=com_docmanpaypal&task=submit_order&mode=$mode&merchant=$ml&id=" . JRequest::getVar('id'));
				break;
			case "Authorize.Net":
				$mainframe->redirect(JURI::base() . "index.php?option=com_docmanpaypal&task=submit_order&mode=$mode&merchant=$ml&id=" . JRequest::getVar('id'));
				break;*/
		}
}
?>
</table>

<input type="submit" value="<?php echo _DMP_SUBMITORDER; ?>" />
<input type="hidden" name="task" value="submit_order" />
<input type="hidden" name="option" value="<?php echo $option; ?>">
<input type="hidden" name="mode" value="<?php echo $mode; ?>">
<input type="hidden" name="id" value="<?php echo JRequest::getInt('id'); ?>">
</form>
<?php
		//include_once('paypal.php');
		break;
	case "submit_order":
		$merchant = JRequest::getVar('merchant','paypal');
		//$merchant = 'paypal';
		if ($dm->expired) {
			$mainframe->enqueueMessage('PayPal IPN for DOCman Trial version has expired, visit <a href="http://motov.net/downloads/paypal-paid-downloads/paypal-ipn-for-docman-3-2-5-stable/download/doc_download">this link</a> to obtain a full version.','error');
			$dm->sendExpiredEmail();
			$mainframe->redirect('index.php');
		}
		//echo $merchant;
		//$jhtml = new JHTML;
		$js = '
		  jQuery(function() {
		    document.dmp_order_form.submit();
		  })();
		';
		$document->addScriptDeclaration($js);
		$dm_id = JRequest::getVar('id',null,null,'int');
		$tmp = $dm->makeBlankOrder($dm_id,JRequest::getVar('mode'));
		$order_id = $tmp['id'];
		$key = $tmp['key'];
		switch ($merchant) {
			case "paypal":
				include_once('paypal.php');
				break;
			case "moneybookers":
				include_once('moneybookers.php');
				break;
			case "google checkout":
				include_once('googleCheckout.php');
				break;
			case "netcash.co.za":
				include_once('netcash.php');
				break;
			case "authorize.net":
				include_once('authorize.net.php');
				break;
			case "micropayment.de":
				include_once('micropayment.de.php');
				break;
		}
		break;
	case "doc_download":
		JHTML::_('behavior.modal'); 
		//mail('deian@motov.net', 'DEBUG DOC DOWNLOAD', print_r($_REQUEST,true));
		$task2 = JRequest::getVar('task2');
		if ($task2 == 'order_canceled') {
			$mainframe->redirect(JRoute::_('index.php?&task=order_canceled&option=com_docmanpaypal'));
		} else if ($task2 == 'ipn') {
			include_once 'netcash.php'; //netcash fix.
			die();
		}
		$mode = JRequest::getVar('mode');
		$order = $dm->getOrder(JRequest::getInt('order_id'));
		if ($order->completed == 0) {
			$mainframe->enqueueMessage(JText::_('WAITING_FOR_PAYPAL'),'info');
			echo '<script>	setTimeout("location.reload(true);",5000);</script>';
			break;
		}
		if ($mode == 'single') {
		$gid = (int)$_GET['gid'];
		$order_id = JRequest::getVar('order_id',null,null,'int');
		$key = JRequest::getVar('key');

		$database->setQuery("select * from #__docman_documents where docman_document_id = '$gid'");
		$tmp = $database->loadAssocList();
		$tmp = $tmp[0];
		$dmname = $tmp['dmname'];

		$dmpcfg = $dm->getConfig();
		$database->setQuery("select title as dmname from #__docman_documents where docman_document_id = '$gid'");
		$dmname = $database->loadResult();
		//$downloadLink = JURI::base() . "index.php?option=com_docman&task=doc_download&order_id=$order_id&gid=$gid&key=$key";
		$downloadLink = JURI::base() . "index.php?option=com_docman&view=download&id=" . $gid . '&Itemid=' . (int)JRequest::getVar('Itemid') . '&key=' . $key;
		$web = str_replace('%%downloadlink%%',$downloadLink,$dmpcfg['thankyoupagecode']);
		$web = str_replace('%%downloadname%%',$dmname,$web);
		echo $web;
		} else if ($mode == 'cart') {
			$order_id = JRequest::getInt('order_id',null,null,'int');
			$key = JRequest::getVar('key');
			$out = '';
			$document->addStylesheet(JURI::base() . 'components/com_docman/themes/default/css/theme.css');
			$dm->cartEmpty();
			$web = $dm->cfg['thankyoupagecodeCart'];
			$order = $dm->getOrder(JRequest::getInt('order_id'));
			if ($order) {
				$ids = explode(",",$order->file_id);
				$out .= '';
				foreach ($ids as $id) {
					$item = $dm->getItem($id);
					if ($item->offlineGood == 0) {
					$out .= '<div class="dm_taskbar" style="float:none; padding-top:20px;"><ul><li style="float:none;"><a href="' . JRoute::_("index.php?option=com_docman&view=download&Itemid=" . (int)JRequest::getVar('Itemid') . "&order_id=$order_id&id=$id&key=$key") . '" style="display:inline;">' . _DMP_DOWNLOAD . ' <b>' . $item->dmname . '</b></a></li></ul></div>';
					} else {
						$out .= '<div class="dm_taskbar" style="float:none; padding-top:20px;"><ul><li style="float:none;"><b>' . $item->dmname . '</b></li></ul></div>';
					}
				}
				$out .= '';
			}
			
			$web = str_replace('%%cartDownloadList%%', $out, $web);
			echo $web;
		}
		break;
	case "downloadslimit":
/*		$order_id = (int)$_GET['order_id'];
		$database->setQuery("select user_id from #__docmanpaypalorders where order_id = '$order_id' limit 1");
		$seller_id = $database->loadResult();
		$cfg = $dm->getConfig($seller_id);*/
		$doc_id = JRequest::getVar('id');
		$mainframe->enqueueMessage(sprintf(_DMP_DOWNLOADSLIMITREACHED,JURI::base() . 'index.php?&option=com_docmanpaypal&task=order&id=' . $doc_id), 'notice');
		echo $dm->cfg['downloadslimitpage'];
		break;
	case "saleslimit":
		$seller_id = (int)$_GET['user_id'];
		$cfg = $dm->getConfig($seller_id);
		echo $cfg['saleslimitpage'];
		break;
	case "ipn":
		$Merchant = JRequest::getVar('merchant','PayPal');
		$merchants = $dm->getMerchants();
		if (in_array($Merchant,$merchants)) {
			if ($Merchant == 'PayPal') { include_once 'paypal.php'; }
			if ($Merchant == 'Micropayment.de') { include_once 'micropayment.de.php'; }
		}
		break;
	case "addToCart":
		if ($dm->cfg['useCart'] == 0) {
			echo json_encode(array('html' => _DMP_CART_DISABLED));
			break;
		}
		$session = JFactory::getSession();
		$session->set('Itemid',(int)JRequest::getVar('Itemid'));
		$dm->cartAdd(JRequest::getVar('id'));
		echo json_encode(array('html' => $dm->cartShow()));
		break;
	case "cartEmpty":
		$dm->cartEmpty();
		echo json_encode(array('html' => $dm->cartShow()));
		break;
	case "order_canceled":
		echo $dm->cfg['ordercanceledpage'];
		break;
	case "cartCheckout":
		$mainframe->redirect('index.php?&option=com_docmanpaypal&task=order&mode=cart');
		break;
	case "pdfPreview":
		$id = JRequest::getInt('id');
		$database->setQuery("select * from #__docman_documents where docman_document_id = $id limit 1");
		$row = $database->loadObject();

		if (pathinfo($row->storage_path,PATHINFO_EXTENSION) != 'pdf') {
			die('This is not a pdf file.');
		}

		@ini_set('memory_limit','512M');
    	$database = &JFactory::getDBO();
	    $document = &JFactory::getDocument();
	    $doc = &JDocument::getInstance('raw');
	    $document = $doc;
	    
		require_once(JPATH_ADMINISTRATOR . DS .'components' . DS. 'com_docmanpaypal' . DS . 'fpdi' . DS . 'FPDI_Protection.php');
		$pdf = new FPDI_Protection();
		if ($dm->cfg['pdfOrientation'] == 'portrait') {
		$pdf->FPDF('P', 'in', array('8.267','11.692'));
		} else {
		$pdf->FPDF('P', 'in', array('11.692','8.267'));
		}
		$sourceFile = $dm->docman_path . DS . $row->storage_path;
		
		$pdfPreviewPath = dirname($sourceFile) . DS . basename($sourceFile,'.pdf') . "_preview.pdf";
		
		//die($pdfPreviewPath);
		
		if (file_exists($pdfPreviewPath)) {
			$sourceFile = $pdfPreviewPath;
		}
		$pagecount = $pdf->setSourceFile($sourceFile);
		for ($loop = 1; $loop <= $dm->cfg['pdfPreviewPages']; $loop++) {
		$tplidx = $pdf->importPage($loop);
		$pdf->addPage();
		$pdf->useTemplate($tplidx);
		}
		$pdf->Output('preview.pdf','D');
		break;
}
?>