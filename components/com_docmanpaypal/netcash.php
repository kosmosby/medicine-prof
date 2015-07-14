<?php 
$dm = new docmanpaypal();
if (!$dm->constructRun) {
	$dm->__construct();
}
$doc_id = (int)JRequest::getVar('id');

if ($dm->hasLicense == false) {
	$product_price = 0.10;
}

if ($task == 'submit_order') {
$seller_id = mysql_result(mysql_query("select user_id from $mosConfig_dbprefix" . "docmanpaypal where `id` = " . JRequest::getVar('id')),0);
if (is_numeric(JRequest::getVar('id')) and JRequest::getVar('id') > 0) {
	$product_price = mysql_result(mysql_query("select price from $mosConfig_dbprefix" . "docmanpaypal where `id` = " . JRequest::getVar('id')),0);
	$product_name = mysql_result(mysql_query("select dmname from $mosConfig_dbprefix" . "docman where `id` = " . JRequest::getVar('id')),0);
} else {
	die("<h1>Failure.</h1>");
}
if ($dm->cfg['useVat'] > 0) {
	$vat =  $dm->vatCalc($product_price);
}
$custom = str_split(base64_encode(serialize(array('doc_id' => (int)JRequest::getVar('id'), 'order_id' => $order_id, 'key' => $key))),50);
?>
<form method="POST" action="https://gateway.netcash.co.za/vvonline/ccnetcash.asp " name="dmp_order_form">
<input type="hidden" name="m_1" value="<?php echo $dm->cfg['netcash_username']; ?>">
<input type="hidden" name="m_2" value="<?php echo $dm->cfg['netcash_password']; ?>">
<input type="hidden" name="m_3" value="<?php echo $dm->cfg['netcash_pin']; ?>">
<input type="hidden" name="p1" value="<?php echo $dm->cfg['netcash_terminal']; ?>">
<input type="hidden" name="p2" value="<?php echo $key; ?>">
<input type="hidden" name="p3" value="<?php echo $product_name; ?>">
<input type="hidden" name="p4" value="<?php echo $product_price; ?>">
<input type="hidden" name="p10" value="<?php echo JURI::base(); ?>index.php?option=com_docmanpaypal&task=doc_download&task2=order_canceled">
<input type="hidden" name="Budget" value="Y">
<input type="hidden" name="m_4" value="<?php echo addslashes($custom[0]); ?>">
<input type="hidden" name="m_5" value="<?php echo addslashes($custom[1]); ?>">
<input type="hidden" name="m_6" value="<?php echo addslashes($custom[2]); ?>">

<input type="hidden" name="m_10" value="<?php echo "&option=com_docmanpaypal&task=doc_download&gid=" . (int)JRequest::getVar('id') . "&order_id=$order_id&key=$key"; ?>">
<?php echo $dm->cfg['netcash_processing_page']; ?>
<!--<input type="submit" value="Pay by Credit Card">
--></form>

<?php
}
if (@$task2 == 'ipn') {
	//mail('deian@motov.net','Debug',print_r($_POST,true));
	$result = mysql_query("select * from $mosConfig_dbprefix" . "docmanpaypalconfig");
	while ($row = mysql_fetch_assoc($result)) {
		$$row['name'] = $row['value'];
	}
	foreach ($_REQUEST as $key => $val) {
		$$key = $val;
		$mailbody .= "$key = $val\n";
	}
	$tmp = unserialize(base64_decode($Extra1. $Extra2 . $Extra3));

    $doc_id = (int)$tmp['doc_id'];
    $order_id = (int)$tmp['order_id'];
    $key = $tmp['key'];
	//mail('deian@motov.net', 'debug', print_r($_REQUEST,true) . "\r\n\r\n" . print_r($tmp,true));
    if ($TransactionAccepted == 'true') {
	    $dm->completeOrder(JRequest::getVar('order_id'));
	
	    $dm->updateOrder(JRequest::getVar('order_id'),array(
	    'price' => $Amount,
	    'mc_currency' => 'ZAR',
	    'transaction' => $Reference,
	    'email' => 'Credit Card',
	    'merchant' => 'Netcash'
	    ));
    }    
    	 $conf =& JFactory::getConfig();
		 $mail = JFactory::getMailer();
	     //$mail->IsHTML(true);
	     $mail->to = array();	
	     $mail->setBody($mailbody);
	     $mail->setSubject('DOCman PayPal IPN (Pay Per Download) - Netcash Order Received!');
	     if ($conf->getValue('config.mailer') == 'smtp') {
	      	$mail->useSMTP($conf->getValue('config.smtpauth'),$conf->getValue('config.smtphost'),$conf->getValue('config.smtpuser'),$conf->getValue('config.smtppass'),$conf->getValue('config.smtpsecure'),$conf->getValue('config.smtpport'));
	     }
	     $mail->setSender($conf->getValue('config.mailfrom'));
	     $mail->addRecipient($dm->cfg['netcash_notifyemail']);
	     $mail->Send();
	
	if ($dm->cfg['emailDelivery'] == 1) {
		$dm->doEmailDelivery($pay_from_email,$doc_id,$order_id,$key);
	}
}
?>