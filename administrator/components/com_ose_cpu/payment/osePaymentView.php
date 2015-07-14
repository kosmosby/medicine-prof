<?php
defined('_JEXEC') or die(";)");

class osePaymentView
{
	function getMscInfo($msc_id,$osePaymentCurrency,$option = 0)
	{
		$node = oseMscTree::getNode($msc_id);
		
		$ext = array();

		$node = self::getExtSpecificPayment($node,$msc_id,'a',$osePaymentCurrency,$option);
		
		return $node;
	}

	
	/*
	function getExtPayment($node,$msc_id)
	{
		$payment = oseMscAddon::getExtInfo($msc_id,'payment','obj');
		oseObject::setValue($node,'payment_mode',$payment->payment_mode);
		oseObject::setValue($node,'currency',$payment->currency);
		
		//oseExit($msc_id);
		switch(strtolower(oseObject::getValue($payment,'payment_mode')))
		{
			case('m'):
				$node = $this->getPriceManual($node,$payment);
				oseObject::setValue($node,'a_price',JText::_('not support!'));
				
				if(strtolower($payment->recurrence_mode) == 'fixed')
				{
					$period = JHtml::date( $payment->start_date).' - '. JHtml::date( $payment->expired_date);
					$node= oseObject::setValue($node,'period',$period);
				}
				else
				{
					$hasS = ($payment->recurrence_num > 1)?'s':null;
					$period = $payment->recurrence_num.' '.$payment->recurrence_unit.$hasS;
					
				}
				
				$node = oseObject::setValue($node,'period',$period);
			break;
			
			case('a'):
				$node = $this->getPriceAutomatic($node,$payment);
				oseObject::setValue($node,'m_price',JText::_('not support!'));
			break;
			
			default:
				$node = $this->getPriceManual($node,$payment);
				$node = $this->getPriceAutomatic($node,$payment);
			break;
		}
		
		return $node;
	}
	
	function getPriceManual($node,$payment)
	{
		$price = $payment->price;
		$payment->currency = 'USD';
		$m_discount = 0;
		if(!empty($payment->discount))
		{
			$price = osePaymentPrice::discountByRate(oseObject::getValue($payment,'discount'),$price);
			
			$m_discount = oseObject::getValue($payment,'discount');
		}
		
		$coupon = JRequest::getString('coupon_code',null);
		
		// is_valid($coupon);
		if($coupon)
		{
			$price = osePaymentPrice::discountByRate(oseObject::getValue($payment,'coupon_discount'),$price);
			$m_discount += $payment->coupon_discount;
		}
		
		$m_price = $payment->currency.' '.round($price,2);
		$node = oseObject::setValue($node,'m_discount',$m_discount);
		$node = oseObject::setValue($node,'m_price',$m_price);
		
		return $node;
	}
	*/
	
	function getPriceStandard($node,$payment,$osePaymentCurrency)
	{
		$a3 = oseObject::getValue($payment,'a3');
		$p3 = oseObject::getValue($payment,'p3');
		$t3 = oseObject::getValue($payment,'t3');
		
		$primaryCurrency = oseRegistry::call('msc')->getConfigItem('primary_currency','currency','obj');
		
		$priceSystem = new osePaymentPrice($primaryCurrency->value,oseJson::decode($primaryCurrency->default));
		$priceSystem->setSelectedCurrency($osePaymentCurrency);
		$standard_a3 = $priceSystem->pricing($a3,$osePaymentCurrency,oseObject::getValue($node,'id'),oseObject::getValue($payment,'id'));
		$renew_a3 = $priceSystem->pricing($a3,$osePaymentCurrency,oseObject::getValue($node,'id'),oseObject::getValue($payment,'id'),true);
		
		$standard_price = $standard_a3;
		$standard_renew_price = $renew_a3;
		
		$node = oseObject::setValue($node,'standard_raw_price',$standard_price);
		$node = oseObject::setValue($node,'standard_price',$osePaymentCurrency.' '.$standard_price);
		
		$node = oseObject::setValue($node,'standard_renewal_raw_price', $standard_renew_price);
		$node = oseObject::setValue($node,'standard_renewal_price', $osePaymentCurrency.' '.$standard_renew_price);
		
		if($p3 > 1)
		{
			$t3 .= 's';
		}
		
		$node = oseObject::setValue($node,'standard_recurrence', $p3.' '.JText::_(strtoupper($t3)));
		
		return $node;
	}
	
	function getPriceTrial($node,$payment,$osePaymentCurrency)
	{
		$a1 = oseObject::getValue($payment,'a1');
		$p1 = oseObject::getValue($payment,'p1');
		$t1 = oseObject::getValue($payment,'t1');
	
		
		$primaryCurrency = oseRegistry::call('msc')->getConfigItem('primary_currency','currency','obj');
		
		$priceSystem = new osePaymentPrice($primaryCurrency->value,oseJson::decode($primaryCurrency->default));
		$priceSystem->setSelectedCurrency($osePaymentCurrency);
		$a1 = $priceSystem->pricing($a1,$osePaymentCurrency,oseObject::getValue($node,'id'),oseObject::getValue($payment,'id'));
		
		$trial_price = $a1;
		$node = oseObject::setValue($node,'trial_raw_price',$trial_price);
		$node = oseObject::setValue($node,'trial_price',$osePaymentCurrency.' '.$trial_price);
		
		if($p1 > 1)
		{
			$t1 .= 's';
		}
		
		$node = oseObject::setValue($node,'trial_recurrence', $p1.' '.JText::_(strtoupper($t1)));
		
		return $node;
	}
	
	function getExtSpecificPayment($node,$msc_id,$type,$osePaymentCurrency,$option)
	{
		
		$payment = oseRegistry::call('msc')->getExtInfo($msc_id,'payment','array');
		
		//$payment = oseJson::decode($payment->params,true);
		
		if(empty($payment))
		{
			return false;
		}
		
		if(empty($option))
		{
			$options = array_keys($payment);
			$option = $options[0];
		}
		else
		{
			$options = array_keys($payment);
			//print_r($options); oseExit($option);
			if(count($options) > 0)
			{	
				if(!in_array($option,$options) )
				{
					$option = $options[0];
				}
			}
			else
			{
				return false;
			}
		}
		
		if(!isset($payment[$option]))
		{
			return false;
		}
		
		$payment = $payment[$option];
		$node= oseObject::setValue($node,'msc_option',$option);
		if(oseObject::getValue($payment,'payment_mode') != $type && oseObject::getValue($payment,'payment_mode') != 'b')
		{
			$type = oseObject::getValue($payment,'payment_mode');
		}
		
		$node= oseObject::setValue($node,'has_trial',oseObject::getValue($payment,'has_trial'));
		$node= oseObject::setValue($node,'recurrence_mode',oseObject::getValue($payment,'recurrence_mode'));
		
		$node = $this->getPriceStandard($node,$payment,$osePaymentCurrency);
		if(oseObject::getValue($payment,'recurrence_mode') == 'fixed')
		{
			//$period = oseHtml::date( oseObject::getValue($payment,'start_date')).' - '. oseHtml::date( oseObject::getValue($payment,'expired_date'));
			$start_date = date("l,d F Y",strtotime(oseObject::getValue($payment,'start_date')));
			$expired_date = date("l,d F Y",strtotime(oseObject::getValue($payment,'expired_date')));
			if($start_date == $expired_date)
			{
				$period =$start_date;
			}else{
				$period =$start_date.' - '. $expired_date;
			}
			
			$node= oseObject::setValue($node,'standard_recurrence',$period);
			$node= oseObject::setValue($node,'first_raw_price',oseObject::getValue($node,'standard_raw_price'));
			$node= oseObject::setValue($node,'second_raw_price',oseObject::getValue($node,'standard_raw_price'));
			
			$node= oseObject::setValue($node,'first_price',oseObject::getValue($node,'standard_price'));
			$node= oseObject::setValue($node,'second_price',$osePaymentCurrency.' '.oseObject::getValue($node,'standard_raw_price'));
			$node= oseObject::setValue($node,'p3',0);
			$node= oseObject::setValue($node,'t3','week');
			$node= oseObject::setValue($node,'eternal',0);
			$node= oseObject::setValue($node,'start_date',oseObject::getValue($payment,'start_date'));
			$node= oseObject::setValue($node,'expired_date',oseObject::getValue($payment,'expired_date'));
		}
		else
		{
			
			
			if(oseObject::getValue($payment,'has_trial'))
			{
				$node = $this->getPriceTrial($node,$payment,$osePaymentCurrency);
				
				$node= oseObject::setValue($node,'first_raw_price',oseObject::getValue($node,'trial_raw_price'));
				$node= oseObject::setValue($node,'second_raw_price',oseObject::getValue($node,'standard_raw_price'));
				
				$node= oseObject::setValue($node,'first_price',oseObject::getValue($node,'trial_price'));
				$node= oseObject::setValue($node,'second_price',oseObject::getValue($node,'standard_price'));
				
				$node= oseObject::setValue($node,'p1',oseObject::getValue($payment,'p1'));
				$node= oseObject::setValue($node,'t1',oseObject::getValue($payment,'t1'));
			
			}
			else
			{
				$node= oseObject::setValue($node,'first_raw_price',oseObject::getValue($node,'standard_raw_price'));
				$node= oseObject::setValue($node,'second_raw_price',oseObject::getValue($node,'standard_raw_price'));
				
				$node= oseObject::setValue($node,'first_price',oseObject::getValue($node,'standard_price'));
				$node= oseObject::setValue($node,'second_price',$osePaymentCurrency.' '.oseObject::getValue($node,'standard_raw_price'));
				
			}
			
			$node= oseObject::setValue($node,'p3',oseObject::getValue($payment,'p3'));
			$node= oseObject::setValue($node,'t3',oseObject::getValue($payment,'t3'));
			$node= oseObject::setValue($node,'eternal',oseObject::getValue($payment,'eternal',0));
		}
		
		return $node;
	}
	
}	
?>