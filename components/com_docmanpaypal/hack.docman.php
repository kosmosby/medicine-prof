<?php
//print_r($GLOBALS);
$classPath = JPATH_SITE . "/administrator/components/com_docmanpaypal/docmanpaypal.class.php";
//echo $classPath;
require_once($classPath);
$dm = new docmanpaypal();
if (!$dm->constructRun) {
	$dm->__construct();
}

$doc_id = JRequest::getVar('id',null,null,'int');
$key = JRequest::getVar('key');
$order_id = JRequest::getVar('order_id',null,null,'int');

if (!$doc_id) {
	$doc_id = (int)JRequest::getVar('alias');
}
$item = $dm->getItem($doc_id);

$my = JFactory::getUser();


$dm->cfg = $dm->getConfig();
$price = $dm->getPrice($doc_id);


/**
 * free for user /**
  * @author Deian
  *
  * free for user type
  */

//die(print_r($my,true));
//die("{$my->usertype} | " . $dm->cfg['free_for_usertypes']);
//if (array_key_exists(,explode(",",$dm->cfg['free_for_usertypes']))) {


if (is_array($my->groups)) {
	foreach ($my->groups as $group => $group_id) {
		$group = $dm->groupName($group_id);
		if (in_array($group, explode(",",$dm->cfg['free_for_usertypes']))) {
			$price = 0;
		}
	}
}

/**
 * Free X seconds after registration
 */
/*if (time() - strtotime($my->registerDate) <= $dm->cfg['free_download_after_seconds'] && $price > 0) {
	$price = 0;
}*/

/**
 * The user is back from PayPal and has the order key, or it's the delivery email, can he download? Did he hit the download limit?
 */
//if ($order_id > 0 && $price > 0) {
if ($price > 0) {
	$canDownload = $dm->canDownload($doc_id,$key);
	if ($canDownload == true) {
		$price = 0;
	}
}

//Check if it's offline good, and take the user to buy now again.
if ($item->offlineGood > 0) {
	$price = $item->price;
}


/**
 * If there is no reason that let's the user download for free, we redirect him to finish the order!
 */
if ($price > 0) { 
	$mainframe = JFactory::getApplication();
	$mainframe->redirect('index.php?&option=com_docmanpaypal&task=submit_order&mode=single&id=' . $doc_id . '&Itemid=' . (int)JRequest::getVar('Itemid')); 
}
?>