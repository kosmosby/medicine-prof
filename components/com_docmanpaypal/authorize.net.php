<?php 
require_once JPATH_COMPONENT . DS . 'anet_php_sdk' . DS . 'AuthorizeNet.php'; // The SDK
$url = JURI::base() . substr($_SERVER['REQUEST_URI'],1) . '&format=raw&post=1' . '&order_id=' . $order_id . '&key=' . $key . '&myid=' . $my->id;
//echo $url;
//error_reporting(E_ALL);
if (JRequest::getVar('post') == 0) {
$api_login_id = $dm->cfg['authorizenet_login_id'];
$transaction_key = $dm->cfg['authorizenet_transaction_key'];
$md5_setting = $dm->cfg['authorizenet_md5_setting']; // Your MD5 Setting
$amount = $dm->getPrice(JRequest::getInt('id'));
error_reporting(0);
echo $dm->cfg['authorizenet_processing_page'];
AuthorizeNetDPM::directPostDemo($url, $api_login_id, $transaction_key, $amount, $md5_setting, $dm->cfg['authorizenet_test_mode']);

?>
* NOTE: To receive the file via email you must be logged in as a registered user.
<?php 
} else if (JRequest::getVar('post') == 1) {
	if (JRequest::getVar('x_response_code') == 1) {
		 $order_id = JRequest::getVar('order_id');
		 $key = JRequest::getVar('key');		
		 $myid = JRequest::getVar('myid');
		 
		 $user =& JFactory::getUser($myid);
		 
	     $dm->completeOrder($order_id);
/*         $dm->updateOrder($order_id,array(
			'first_name' =>  $p->ipn_data['first_name'],
			'last_name' => $p->ipn_data['last_name'],
			'organization' => $p->ipn_data['payer_business_name'],
			'address' => $p->ipn_data['address_street'],
			'city' => $p->ipn_data['address_city'],
			'state' => $p->ipn_data['address_state'],
			'zip' => $p->ipn_data['address_zip'],
			'country' => $p->ipn_data['address_country'],
			'phone' => '-',
         	'price' => $p->ipn_data['mc_gross'],
         	'mc_currency' => $p->ipn_data['mc_currency'],
         	'transaction' => $p->ipn_data['txn_id'],
         	'email' => $p->ipn_data['payer_email'],
         	'merchant' => 'PayPal'
         ));*/
		 $body = '';
         $subject = 'DOCman PayPal IPN (Pay Per Download) - Authorize.Net Order Received!';
         foreach ($_POST as $ipnkey => $ipnvalue) { $body .= "\n$ipnkey: $ipnvalue"; }
         //mail($notifyemail, $subject, $body);

         	 $conf =& JFactory::getConfig();
			 $mail = JFactory::getMailer();
	         //$mail->IsHTML(true);
	         $mail->to = array();	
	         $mail->setBody($body);
	         $mail->setSubject($subject);
	         if ($conf->getValue('config.mailer') == 'smtp') {
	         	$mail->useSMTP($conf->getValue('config.smtpauth'),$conf->getValue('config.smtphost'),$conf->getValue('config.smtpuser'),$conf->getValue('config.smtppass'),$conf->getValue('config.smtpsecure'),$conf->getValue('config.smtpport'));
	         }
	         $mail->setSender($conf->getValue('config.mailfrom'));
	         $mail->addRecipient($dm->cfg['authorizenet_notifyemail']);
	         $mail->Send();
         
         if ($cfg['emailDelivery'] == 1 && $myid > 0) {
			$dm->doEmailDelivery($user->email,JRequest::getInt('id'),$order_id,$key);
         }
		
		$redirect_url = JURI::base() . "index.php?option=com_docmanpaypal&task=doc_download&gid=" . (int)JRequest::getVar('id') . "&order_id=$order_id&key=$key";
	} else {
		$redirect_url = JURI::base() . "index.php?option=com_docmanpaypal&task=order_canceled";
	}
	echo "<html><head><script language=\"javascript\">
                <!--
                window.location=\"{$redirect_url}\";
                //-->
                </script>
                </head><body><noscript><meta http-equiv=\"refresh\" content=\"1;url={$redirect_url}\"></noscript>
                </body></html>";
}

?>