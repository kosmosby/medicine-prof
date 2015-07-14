<?php
defined('_JEXEC') or die(";)");
class osePaymentOrderPnw extends osePaymentOrder
{
	var $table= null;
	function __construct($table= '#__osemsc_order')
	{
		$this->table= $table;
	}
	function getErrorMessage($paymentMethod, $code, $message = null)
	{
		$return = array();
		$return['payment']= $paymentMethod;
		$return['success']= false;
		$return['title']= JText :: _('Error');
		switch($code)
		{
			case '0000':
			$return['content']= $message;
			break;
			case '0001':
			$return['content']= JText :: _("ePay Customer ID or Username is not setup properly, please contact administrators for this issue.");
			break;
			case '0002':
			$return['content']= JText :: _("Please check your membership setting. Membership Price cannot be empty.");
			break;
			case '0003':
			$return['content']= JText :: _("ePay Payment Processor is not enabled, please enable it through OSE backend.");
			break;
			case '0004':
			$return['content']= JText :: _("Your order is activated, but the automatic billing has not been created. The error reported from our payment gatePay is: <br />");
			$return['content'].=$message;
			break;

		}
		return $return;
	}

	
	
	function PNWCreateProfile($isSub = 1,$params)
	{
		$user_id= $params['user_id'];
		$project_id= $params['project_id'];
		$project_password = $params['project_password'];
		$amount = $params['amount'];
		$currency_id = $params['currency_id'];
		$language_id = $params['language_id'];
		$orderId = $params['order_number'];
		$reason1 = $params['reason_1'];
		//$expires = $this->transInterval($params['recurrence_unit'],$params['expires']);
		//$expires = $expires['length'];
		
		$data = array(
	       'user_id' => $user_id,
	       'project_id' => $project_id,
			'sender_holder' => '',
           'sender_account_number' => '',
		   'sender_bank_code' => '',
		   'sender_country_id' => '',
	       'amount' => $amount,
	       'currency_id' => $currency_id,
	       'reason_1' => $reason1,
	       'reason_2' => '',
	       'user_variable_0' => $orderId,
		   'user_variable_1' => '',
		   'user_variable_2' => '',
		   'user_variable_3' => '',
		   'user_variable_4' => '',
		   'user_variable_5' => '',
		   //'expires' => '',
		   //'max_usage' => '',
		   //'language_id' => $language_id,
		   'project_password' => $project_password
		);
		
		$hash = $this->getHash($data);
		
		$form = oseHtml::getInstance('form');
		
		$link = OSEMSC_F_URL;//.'/index.php?option=com_osemsc';
		
		$hiddenMerchantnumber = $form->hidden('user_id',$user_id);
		$hiddenAmount = $form->hidden('amount',$amount);
		$hiddenCurrency = $form->hidden('currency_id',$currency_id);
		$hiddenUV0 = $form->hidden('user_variable_0',$orderId);
		$hiddenReason1 = $form->hidden('reason_1',$reason1);
		$hiddenReason2 = $form->hidden('reason_2','');
		//$hiddenExpires  = $form->hidden('expires',$expires);
		$hiddenProject  = $form->hidden('project_id',$project_id);
		//$hiddenLanguage = $form->hidden('language_id',$language_id);
		$hiddenHash  = $form->hidden('hash',$hash);
		$submit = $form->submit('submit_button','submit','submit_button');
		
		
		
		
		
		//$form->append('<script type="text/javascript" src="http://www.epay.dk/js/standardwindow.js"></script> ');
		$form->createForm('https://www.directebanking.com/payment/start',null,null,'POST','ePay_window');
		$form->addLevel();
		$form->sc('et');
		$form->append($hiddenMerchantnumber);
		$form->sc('et');
		$form->append($hiddenProject);
		$form->sc('et');
		$form->append($hiddenAmount);
		$form->sc('et');
		$form->append($hiddenCurrency);
		$form->sc('et');
		$form->append($hiddenUV0);
		$form->sc('et');
		$form->append($hiddenReason1);
		$form->sc('et');
		$form->append($hiddenReason2);
		$form->sc('et');
		$form->append($hiddenHash);
		$form->sc('et');
		//$form->append($hiddenLanguage);
		//$form->sc('et');
		$form->append($submit);
		$form->sc('et');
		$form->subLevel();
		$form->sc('et');
		$form->endForm();
		
		$html = $form->output();
		
		return $html;
	}
	
	function transInterval($t, $p) {
		$results= array();
		$t= strtolower($t);
		switch($t) {
			case "year" :
				$results['length']= $p * 365;
				$results['unit']= 'days';
				$results['unit2']= 'day';
				break;
			case "month" :
				$results['length']= $p * 30;
				$results['unit']= 'days';
					$results['unit2']= 'day';
				break;
			case "week" :
				$results['length']= $p * 7;
				$results['unit']= 'days';
				$results['unit2']= 'day';
				break;
			case "day" :
				if($p) {
					$results['length']= $p;
					$results['unit']= 'days';
					$results['unit2']= 'day';
				}
				break;
		}
	
		return $results;
	}
	
	function getHash($data)
	{
		$data_implode = implode('|', $data);
		//echo $data_implode;exit;
		$hash = sha1($data_implode);
		return $hash;
	}
}
?>