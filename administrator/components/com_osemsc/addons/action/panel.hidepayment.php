<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionPanelHidepayment extends oseMscAddon
{
	public static function save($params = array())
	{
		$result = array();
		$post = JRequest::get('post');
		if (oseMscAddon::quickSavePanel('hidepayment_',$post))
		{
			$result['success'] = true;
			$result['title'] = JText::_('DONE');
			$result['content'] = JText::_('SAVE_SUCCESSFULLY');
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('ERROR');
		}
		
		return $result;
	}
	
		
	public static function getMethods($params = array())
	{
	$pConfig = oseMscConfig::getConfig('payment','obj');

		$methods = array();

		if(!empty($pConfig->enable_cc))
		{
			$cc_methods = explode(',',$pConfig->cc_methods);

			foreach( $cc_methods as $cc_method)
			{
				switch ($cc_method)
				{
					case('authorize'):
						$methods[] = array('id'=> 4, 'value'=>$cc_method, 'text' =>  JText::_('Credit_Card'));
					break;

					case('paypal_cc'):
						$methods[] = array('id'=> 4, 'value'=>$cc_method, 'text' => JText::_('Credit_Card'));
					break;

					case('eway'):
						$methods[] = array('id'=> 4, 'value'=>$cc_method, 'text' => JText::_('Credit_Card'));
					break;

					case('epay'):
						$methods[] = array('id'=> 4, 'value'=>$cc_method, 'text' => JText::_('Credit_Card'));
					break;

					case('vpcash_cc'):
						$methods[] = array('id'=> 4, 'value'=>$cc_method, 'text' => JText::_('Credit_Card'));
					break;

					case('usaepay'):
						$methods[] = array('id'=> 4, 'value'=>$cc_method, 'text' => JText::_('Credit_Card'));
					break;
					
					case('oospay'):
						$methods[] = array('id'=> 4, 'value'=>$cc_method, 'text' => JText::_('Credit_Card'));
					break;
					
					case('ebs'):
						$methods[] = array('id'=> 4, 'value'=>$cc_method, 'text' => JText::_('Credit_Card'));
					break;
					
					default:
						$methods[] = array('id'=> 4, 'value'=>$cc_method, 'text' => JText::_('Credit_Card'));
					break;
				}
			}
		}

		if(!empty($pConfig->enable_paypal))
		{
			$methods[] = array('id'=> 1, 'value'=>'paypal', 'text' =>  JText::_('Paypal'));
		}

		if(!empty($pConfig->enable_gco))
		{
			$methods[] = array('id'=> 2, 'value'=>'gco', 'text' => JText::_('Google_Checkout'));
		}

		if(!empty($pConfig->enable_twoco))
		{
			$methods[] = array('id'=> 3, 'value'=>'2co', 'text' => JText::_('2Checkout'));
		}

		if(!empty($pConfig->enable_poffline))
		{
			$methods[] = array('id'=> 5, 'value'=>'poffline', 'text' => JText::_('Pay_Offline'));
		}

		if(!empty($pConfig->enable_vpcash))
		{
			$methods[] = array('id'=> 6, 'value'=>'vpcash', 'text' => JText::_('VirtualPayCash'));
		}

		if(!empty($pConfig->enable_bbva))
		{
			$methods[] = array('id'=> 7, 'value'=>'bbva', 'text' => JText::_('BBVA'));
		}
		
		if(!empty($pConfig->enable_payfast))
		{
			$methods[] = array('id'=> 8, 'value'=>'payfast', 'text' => JText::_('PayFast'));
		}

		if(!empty($pConfig->enable_clickbank))
		{
			$methods[] = array('id'=> 9, 'value'=>'clickbank', 'text' => JText::_('ClickBank'));
		}
		
		if(!empty($pConfig->enable_ccavenue))
		{
			$methods[] = array('id'=> 10, 'value'=>'ccavenue', 'text' => JText::_('CCAvenue'));
		}
		
		if(!empty($pConfig->enable_icepay))
		{
			$methods[] = array('id'=> 11, 'value'=>'icepay', 'text' => JText::_('ICEPAY'));
		}
		
		if(!empty($pConfig->enable_liqpay))
		{
			$methods[] = array('id'=> 12, 'value'=>'liqpay', 'text' => JText::_('LiqPay'));
		}
		
		if(!empty($pConfig->enable_realex))
		{
			$methods[] = array('id'=> 13, 'value'=>'realex_'.$pConfig->realex_mode, 'text' => JText::_('Realex Payments'));
		}
		
		if(!empty($pConfig->enable_sisow))
		{
			
			$methods[] = array('id'=> 14, 'value'=>'sisow', 'text' => JText::_('Sisow'));
		}
		
		if(!empty($pConfig->enable_pagseguro))
		{
			$methods[] = array('id'=> 15, 'value'=>'pagseguro', 'text' => JText::_('PagSeguro'));
		}
		
		$result = array();
		
		if(count($methods) < 1)
		{
			$result['total'] = 0;
			$result['results'] = '';
		}
		else
		{
			$result['total'] = count($methods);
			$result['results'] = $methods;
		}
		
		return $result;
	}
}
?>