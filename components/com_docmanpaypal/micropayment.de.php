<?php 
$method = JRequest::getVar('method');
$session =& JFactory::getSession();
$cart = $session->get('cart');
$mode = JRequest::getVar('mode');

if ($mode == 'cart')
{
	$doc_id = implode(",",$cart['id']);
	$amount = $cart['total'] * 100;
}
if ($mode == 'single')
{
	$doc_id = JRequest::getInt('id');
	$amount = $dm->getPrice($doc_id) * 100;
}

switch ($task) {
	case "submit_order":
		switch ($method) {
			case "Call2Pay":
				$array = array('my_id' => $my->id,'doc_id' => $doc_id, 'order_id' => $order_id, 'key' => $key, 'mode' => $mode);
				$custom = base64_encode(serialize($array));
				//mail('deian@motov.net',"CUSTOM - " . $method,$custom);
				
				if ($amount < 49) {
					$amount = 49;
				}
				$mainframe->redirect("{$dm->cfg[Call2Pay]}&amount=$amount&title=" . _DMP_CART_ORDER . "&custom=$custom");
				//echo "<a href=\"\">link</a>";
				break;
			case "MobilePay":
				$array = array('my_id' => $my->id,'doc_id' => $doc_id, 'order_id' => $order_id, 'key' => $key, 'mode' => $mode);
				$custom = base64_encode(serialize($array));
				//mail('deian@motov.net',"CUSTOM - " . $method,$custom);
				
				if ($amount < 49) {
					$amount = 49;
				}
				$mainframe->redirect("{$dm->cfg[MobilePay]}&amount=$amount&title=" . _DMP_CART_ORDER . "&custom=$custom");
				//echo "<a href=\"\">link</a>";
				break;
			case "eBank2Pay":
				$array = array('my_id' => $my->id,'doc_id' => $doc_id, 'order_id' => $order_id, 'key' => $key, 'mode' => $mode);
				$custom = base64_encode(serialize($array));
				//mail('deian@motov.net',"CUSTOM - " . $method,$custom);
				
				if ($amount < 49) {
					$amount = 49;
				}
				$mainframe->redirect("{$dm->cfg[eBank2Pay]}&amount=$amount&title=" . _DMP_CART_ORDER . "&custom=$custom");
				//echo "<a href=\"\">link</a>";
				break;
				
			default:
				$call2Pay = new stdClass();
				$call2Pay->text = 'Call2Pay';
				$call2Pay->value= 'Call2Pay';
		
				$MobilePay = new stdClass();
				$MobilePay->text = 'MobilePay';
				$MobilePay->value= 'MobilePay';
				
				$eBank2Pay = new stdClass();
				$eBank2Pay->text = 'eBank2Pay';
				$eBank2Pay->value= 'eBank2Pay';
				echo $dm->cfg['micropaymentDeHeader']
				?>
				
				<form action="<?php echo JURI::base(); ?>" method="GET">
				
				<?php
				echo JHTML::_( 'select.radiolist', array($call2Pay,$MobilePay,$eBank2Pay),'method',null,'value','text','Call2Pay');
				?>
				<input type="hidden" name="option" value="<?php echo $option; ?>" />
				<input type="hidden" name="task" value="submit_order" />
				<input type="hidden" name="merchant" value="micropayment.de" />
				<input type="hidden" name="mode" value="<?php echo JRequest::getVar('mode'); ?>" />
				<input type="hidden" name="id" value="<?php echo JRequest::getInt('id'); ?>" />
				<input type="submit" />
				</form>
				<?php 
				break;
		}
		break;
	case "ipn":
			 $tmp = unserialize(base64_decode(JRequest::getVar('custom')));
	         $my_id = (int)$tmp['my_id'];
	         @$doc_id = $tmp['doc_id'];
	         $order_id = (int)$tmp['order_id'];
	         $key = $tmp['key'];
	         $mode = $tmp['mode'];
	         
	         $dm->completeOrder($order_id,$mode);
	         $dm->updateOrder($order_id,array(
//				'first_name' =>  $p->ipn_data['first_name'],
//				'last_name' => $p->ipn_data['last_name'],
//				'organization' => $p->ipn_data['payer_business_name'],
//				'address' => $p->ipn_data['address_street'],
//				'city' => $p->ipn_data['address_city'],
//				'state' => $p->ipn_data['address_state'],
//				'zip' => $p->ipn_data['address_zip'],
				'country' => $p->ipn_data['country'],
//				'phone' => '-',
	         	'price' => JRequest::getVar('amount')  / 100,
	         	'mc_currency' => JRequest::getVar('currency'),
//	         	'transaction' => $p->ipn_data['txn_id'],
//	         	'email' => $p->ipn_data['payer_email'],
	         	'merchant' => 'Micropayment.de',
	         	'src' => $mode
	         ));
			$trenner 	= "\n";
			
			$status		= 'ok';
			$url		= JURI::base() . "index.php?option=com_docmanpaypal&task=doc_download&mode=" . $mode . "&gid=" . $doc_id . "&order_id=$order_id&key=$key";
			$target		= '_top';
			$forward	= 1;
			
			$response = 'status=' . $status;
			$response.= $trenner;
			$response.= 'url=' . $url;
			$response.= $trenner;
			$response.= 'target=' . $target;
			$response.= $trenner;
			$response.= 'forward=' . $forward;
			
			echo $response;
		
		break;
}
?>