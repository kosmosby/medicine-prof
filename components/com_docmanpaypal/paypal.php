<?php
/*PHP Paypal IPN Integration Class Demonstration File
 *  4.16.2005 - Micah Carrick, email@micahcarrick.com
 *
 *  This file demonstrates the usage of paypal.class.php, a class designed  
 *  to aid in the interfacing between your website, paypal, and the instant
 *  payment notification (IPN) interface.  This single file serves as 4 
 *  virtual pages depending on the "action" varialble passed in the URL. It's
 *  the processing page which processes form data being submitted to paypal, it
 *  is the page paypal returns a user to upon success, it's the page paypal
 *  returns a user to upon canceling an order, and finally, it's the page that
 *  handles the IPN request from Paypal.
 *
 *  I tried to comment this file, aswell as the acutall class file, as well as
 *  I possibly could.  Please email me with questions, comments, and suggestions.
 *  See the header of paypal.class.php for additional resources and information.
*/
//if ((!is_numeric($_REQUEST['id']) or !isset($_REQUEST['id'])) and !isset($_REQUEST['action'])) {
//	die("<h1>What are you doing here ?</h1>");
//}
//error_reporting(0);


@require_once('paypal.class.php');  // include the class file
$dm = new docmanpaypal();
if (!$dm->constructRun) {
	$dm->__construct();
}

extract($dm->cfg);
$p = new paypal_class;             // initiate an instance of the class


//TODO: Da se mahnat si4ki mysql_query... i taka, shtoto mahnah gore connection-a.

//$paypalbusiness = mysql_result(mysql_query("select value from $mosConfig_dbprefix" . "docmanpaypalconfig where name = 'paypalemail'"),0);
//$notifyemail = mysql_result(mysql_query("select value from $mosConfig_dbprefix" . "docmanpaypalconfig where name = 'notifyemail'"),0);
//$sandbox = mysql_result(mysql_query("select value from $mosConfig_dbprefix" . "docmanpaypalconfig where name = 'sandbox'"),0);
if ($sandbox == 'No') {
	$p->paypal_url = 'https://www.paypal.com/cgi-bin/webscr';
} else {
	$p->paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
}
$componentdir = 'components/com_docmanpaypal';
// if there is not action variable, set the default action of 'process'
$request_action = JRequest::getVar('action');
if (empty($request_action)) $request_action = 'process'; 

switch ($request_action) {
    
   case 'process':      // Process and order...
   		if (is_numeric(JRequest::getVar('id')) and JRequest::getVar('id') > 0) { //SINGLE
   			$item = $dm->getItem(JRequest::getVar('id'));
   			$price = $item->price;
			$name = $item->dmname;
	 		$p->add_field('return', JURI::base() . "index.php?option=com_docmanpaypal&task=doc_download&mode=" . JRequest::getVar('mode') . "&gid=" . (int)JRequest::getVar('id') . "&order_id=$order_id&key=$key&Itemid=" . (int)JRequest::getVar('Itemid'));
			$p->add_field('cancel_return', JURI::base() . "index.php?option=com_docmanpaypal&task=order_canceled");
			//$p->add_field('notify_url', JURI::base() ."index.php?option=com_docmanpaypal&task=ipn&mode=single&action=ipn&merchant=PayPal");
			$p->add_field('notify_url', JURI::base() ."index.php?option=com_docmanpaypal&task=ipn&mode=single&action=ipn&Itemid=" . (int)JRequest::getVar('Itemid'));
			$p->add_field('item_name', $name);
			$p->add_field('item_number', $order_id);
			$p->add_field('amount', $price);
			//$p->add_field('upload',1);//fix when added cart
			$p->add_field('custom', base64_encode(serialize(array('my_id' => $my->id,'doc_id' => (int)JRequest::getVar('id'), 'order_id' => $order_id, 'key' => $key))));
			if ($cfg['useVat'] > 0) {
				$p->add_field('tax', $dm->vatCalc($price));
			}
			if ($item->vendor_id == 0) {
				$p->add_field('business', $cfg['paypalemail']);
			} else {
				$p->add_field('business', $item->paypalemail);
			}
			
   		} else if (JRequest::getVar('mode') == 'cart'){ //CART
   			$session = JFactory::getSession();
   			$cart = $session->get('cart');
	 		$p->add_field('return', JURI::base() . "index.php?option=com_docmanpaypal&Itemid=" . $session->get('Itemid') . "&task=doc_download&mode=" . JRequest::getVar('mode') . "&gid=" . (int)JRequest::getVar('id') . "&order_id=$order_id&key=$key");
			$p->add_field('cancel_return', JURI::base() . "index.php?option=com_docmanpaypal&task=order_canceled");
			//$p->add_field('notify_url', JURI::base() . "index.php?option=com_docmanpaypal&task=ipn&mode=cart&action=ipn&merchant=PayPal");
			$p->add_field('notify_url', JURI::base() . "index.php?option=com_docmanpaypal&task=ipn&mode=cart&action=ipn");
			$k = 0;
			foreach ($cart['id'] as $id) {
				$k++;
				$item = $dm->getItem($id);
				$p->add_field('item_name_' . $k, $item->dmname);
				$p->add_field('item_number_' . $k, $item->id);
				$p->add_field('amount_' . $k, $item->price);
			}
			$p->add_field('upload',1);
			//$p->add_field('item_name', 'Digital Delivery');
			$p->add_field('custom', base64_encode(serialize(array('my_id' => $my->id,'doc_id' => implode(",",$cart['id']), 'order_id' => $order_id, 'key' => $key))));
			if ($cfg['useVat'] > 0) {
				$p->add_field('tax', $dm->vatCalc($cart['total']));
			}
			$p->add_field('business', $cfg['paypalemail']);
   		} else { 
   			die("<h1>Failure.</h1>");
   		}
      //$key = md5($_SERVER['REMOTE_ADDR'] . time() . rand(100,9999));

	  if (strlen($cfg['paypalCountry']) == 2) {
		  $p->add_field('lc',$cfg['paypalCountry']);
	  }
   	  //$p->add_field('business', $cfg['paypalemail']);
      $p->add_field('receiver_email', $cfg['notifyemail']);
      $p->add_field('currency_code', $cfg['currency']);

      $p->submit_paypal_post($cfg['paypal_processing_page']); // submit the fields to paypal
      //$p->dump_fields();      // for debugging, output a table of all the fields
      break;
      
   case 'ipn':
   
   	  // Paypal is calling page for IPN validation...
      // It's important to remember that paypal calling this script.  There
      // is no output here.  This is where you validate the IPN data and if it's
      // valid, update your database to signify that the user has payed.  If
      // you try and use an echo or printf function here it's not going to do you
      // a bit of good.  This is on the "backend".  That is why, by default, the
      // class logs all IPN data to a text file.
//		$to = 'bbhsoft@gmail.com';    //  your email
         //mail('deian@motov.net','DEBUG CART',print_r(JRequest::get('post'),true));

		if ($p->validate_ipn()) {
          //mail('deian@motov.net','debug info 3',var_export($_REQUEST,true));
         // Payment has been recieved and IPN is verified.  This is where you
         // update your database to activate or process the order, or setup
         // the database with the user's order details, email an administrator,
         // etc.  You can access a slew of information via the ipn_data() array.
  
         // Check the paypal documentation for specifics on what information
         // is available in the IPN POST variables.  Basically, all the POST vars
         // which paypal sends, which we send back for validation, are now stored
         // in the ipn_data() array.
  
         // For this example, we'll just email ourselves ALL the data.
         $subject = 'DOCman PayPal IPN (Pay Per Download) - PayPal Order Received!';
         $body =  "An instant payment notification was successfully recieved\n";
         $body .= "from ".$p->ipn_data['payer_email']." on ".date('m/d/Y');
         $body .= " at ".date('g:i A')."\n\nDetails:\n";
         $tmp = unserialize(base64_decode($_POST['custom']));
			
         //
         $mode = JRequest::getVar('mode');
         $my_id = (int)$tmp['my_id'];
         @$doc_id = $tmp['doc_id'];
         $order_id = (int)$tmp['order_id'];
         $key = $tmp['key'];

	 	 //mail('deian@motov.net','debug data',$order_id . ' ' . $mode . ' ' . print_r($tmp,true));
         $priceFromDb = $dm->getPrice($doc_id);
         
         if ($dm->cfg['useVat'] > 0 && $mode != 'cart') {
         	$priceFromDb = $priceFromDb + ($dm->vatCalc($priceFromDb));
         }
         $chk1 = round(floatval($priceFromDb),2);
         $chk2 = floatval($p->ipn_data['mc_gross']);
         if ($chk1 - $chk2 != 0) {
         	//die();
         }
         
         if ($p->ipn_data['mc_currency'] != $dm->cfg['currency']) {
		 	$problemOccured = true;
		 }
		 if ($p->ipn_data['test_ipn'] == 1 && $dm->cfg['sandbox'] == 'No') {
		 	$problemOccured = true;
		 }
		 if ($dm->vendor_exists($p->ipn_data['receiver_email']) > 0 || $dm->cfg['paypalemail'] == $p->ipn_data['receiver_email']) {
		 	//
		 } else {
			 $problemOccured = true;
		 }
		 
         if ($problemOccured) {
         	//die();
         }
         echo 'OK';
	 	 //mail('deian@motov.net','completeorder?',$order_id . ' ' . $mode . print_r($_REQUEST,true)); ;
		 $dm->completeOrder($order_id,$mode);
         $dm->updateOrder($order_id,array(
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
         	'merchant' => 'PayPal',
         	'src' => $mode
         ));
         
         foreach ($p->ipn_data as $ipnkey => $ipnvalue) { $body .= "\n$ipnkey: $ipnvalue"; }
         	 $app = JFactory::getApplication();
         	 $mail = JFactory::getMailer();
	         //$mail->IsHTML(true);
	         $mail->setBody($body);
	         $mail->setSubject($subject);
	         if ($app->getCfg('mailer') == 'smtp') {
		         $mail->useSMTP($app->getCfg('smtpauth'),$app->getCfg('smtphost'),$app->getCfg('smtpuser'),$app->getCfg('smtppass'),$app->getCfg('smtpsecure'),$app->getCfg('smtpport'));
	 		 }
	 		 $mail->setSender(array($app->getCfg('mailfrom'), $app->getCfg('fromname')));
	         $mail->addRecipient($notifyemail);
	         $mail->Send();
         
         
         if ($cfg['emailDelivery'] == 1) {
			$dm->doEmailDelivery($p->ipn_data['payer_email'],$doc_id,$order_id,$key);
         }
         echo "OK";
      } 
      break;
 }     
?>