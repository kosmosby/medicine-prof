<?php
defined('_JEXEC') or die(";)");

class osePaymentMscList extends oseMscList
{
	function drawLeaf($node)
	{
		$item = $this->generateFirstMsc($node);
		$array = array();
		$session =JFactory::getSession();
    	$osePaymentCurrency = $session->get('osePaymentCurrency',oseRegistry::call('msc')->getConfig('currency','obj')->primary_currency);
		$detail_link = '';//'<a href="javascript:void(0);" class="msc-detail" id="msc-detail-'.oseObject::getValue($node,'id').'">'.JText::_("[Details...]").'</a>';
		$array['title'] = parent::drawSubTitle('<p><a href="index.php?option=com_osemsc&view=memberships&msc_id='.oseObject::getValue($node,'id').'&layout=detail">'.oseObject::getValue($item,'title')." </a><span>{$detail_link}</span></p>");
		$payment = oseRegistry::call('msc')->getExtInfo(oseObject::getValue($node,'id'),'payment');
		if(empty($payment))
		{
			return $array;
		}

		$paymentKeys = array_keys($payment);
		$basic = $payment[$paymentKeys[0]];
		$basic = (object)$basic;
		$option = array();
		foreach ( $payment as $obj )
		{
			$node = oseRegistry::call('payment')->getInstance('View')->getPriceStandard($node,$obj,$osePaymentCurrency);
			$optionPrice = oseObject::getValue($node,'standard_price').' for every '.JText::_(oseObject::getValue($node,'standard_recurrence')) ;

			if(oseObject::getValue($obj,'has_trial'))
			{
				$node = oseRegistry::call('payment')->getInstance('View')->getPriceTrial($node,$obj,$osePaymentCurrency);
				$optionPrice .= ' ('.oseObject::getValue($node,'trial_price').' in first '.JText::_(oseObject::getValue($node,'trial_recurrence')).')';
			}

			$option[] = JHTML::_('select.option',  oseObject::getValue($obj,'id'),$optionPrice);
		}

		$combo = JHTML::_('select.genericlist',  $option, 'msc_option', ' class="msc_options"  size="1" style="width:200px"', 'value', 'text', $basic->id );
		$array['price'] = "<div class='msc-price-box'><span>".JText::_('Options').":</span>".parent::drawPrice($combo).'</div>';
		$array['intro'] = "<div class='msc-intro-box'><span>".JText::_('Description').":</span>".$this->drawIntro(oseObject::getValue($item,'desc')).'<a href="javascript:void(0)" class="show-detail">'.JText::_('READ_MORE').'</a></div>';
		$array['desc'] = "<div class='msc-desc-box'><span>".JText::_('Description').":</span>".$this->drawDesc(oseObject::getValue($item,'desc')).'<a href="javascript:void(0)" class="show-detail">'.JText::_('HIDE_FULL_TEXT').'</a></div>';
		$array['button'] = "<div class='msc-button-box'><ul>";
		if(strtolower($basic->payment_mode) == 'm')
		{
			$array['button'] .= '<li>'.$this->drawButton($node,'m',JText::_('Subscribe')).'</li>';
		}
		elseif(strtolower($basic->payment_mode) == 'a')
		{
			$array['button'] .= '<li>'.$this->drawButton($node,'a',JText::_('Subscribe')).'</li>';
		}
		else
		{
			$array['button'] .= '<li>'.$this->drawButton($node,'a',JText::_('Subscribe')).'</li>';
		}
		$array['button'] .= "</ul></div>";
		return $array;
	}
	function drawSubLeaf($node)
	{
		$item = self::generateSubMsc($node);

		$array = array();

		$payment = oseMscAddon::getExtInfo(oseObject::getValue($node,'id'),'payment','obj');

		$price = oseObject::getValue($item,'standard_price').' for every '.oseObject::getValue($item,'standard_recurrence');
		if($payment->has_trial)
		{
			$price.= ' ('.oseObject::getValue($item,'trial_price').' in the first '.oseObject::getValue($item,'trial_recurrence').')';
		}

		if(strtolower($payment->payment_mode) == 'm')
		{
			$array['button'] = $this->drawButton($node,'m',JText::_('Manual Renewal'));
		}
		elseif(strtolower($payment->payment_mode) == 'a')
		{
			$array['button'] = $this->drawButton($node,'a',JText::_('Automatic Renewal'));
		}
		else
		{
			$array['button'] = $this->drawButton($node,'m',JText::_('Manual Renewal'));
			$array['button'] .= $this->drawButton($node,'a',JText::_('Automatic Renewal'));
		}

		$array['title'] = $this->drawSubTitle('<p>|__'.oseObject::getValue($item,'title')."</p>");
		$array['price'] = $this->drawPrice(oseObject::getValue($item,'price'));
		$array['period'] = $this->drawPeriod(oseObject::getValue($item,'period'));

		$array = implode("\r\n",$array);

		return $array;
	}

	function drawButton($node,$payment_mode,$name)
	{
		//$div = $this->drawDiv('msc-button','msc-button-'.oseObject::getValue($node,'id'));
		$button = '<button class="msc-button-select-'.$payment_mode.'" id="msc-button-select-'.$payment_mode.'-'.oseObject::getValue($node,'id').'">'
				 .ucfirst($name)
				 .'</button>'
				 ;

		//return sprintf($div,$button);
		return $button;
	}

	function generateFirstMsc($node)
	{
    	$item = null;
    	$msc_id = oseObject::getValue($node,'id');

    	$puzzle = array();

    	if(oseObject::getValue($node,'leaf'))
    	{
    		/*
    		$payment = oseRegistry::call('payment');
    		$node = $payment->getMscInfo($msc_id,$this->currency);

    		$puzzle['standard_price'] = $this->getStandardPrice($node);
    		$puzzle['standard_recurrence'] = $this->getRecurrence($node,'standard');
    		$puzzle['trial_price'] = $this->getTrialPrice($node);
    		$puzzle['trial_recurrence'] = $this->getRecurrence($node,'trial');
    		*/
    		$puzzle['leaf'] = true;
    	}
    	else
    	{
    		$puzzle['leaf'] = false;
    	}

    	$puzzle['id'] = $msc_id;
    	$puzzle['title'] = $this->getTitle($node,true);
		$puzzle['desc'] = $this->getDesc($node);
		$puzzle['level'] = $this->getLevel($node);
		$puzzle['image'] = $this->getImage($node);
    	$puzzle['first'] = true;
    	//oseExit($puzzle);
    	return $puzzle;
    	//$objs[$oKey]['card'] = $item;

	}

	function generateSubMsc($node)
	{/*
		$msc_id = oseObject::getValue($node,'id');

		if(oseObject::getValue($node,'leaf'))
		{
			$payment = oseRegistry::call('payment');
    		$node = $payment->getMscInfo($msc_id);

    		$puzzle['price'] = $this->getPrice($node);
    		$puzzle['period'] = $this->getPeriod($node);
    		$puzzle['leaf'] = true;
		}
		else
		{
			$puzzle['leaf'] = false;
		}

		$puzzle['id'] = $msc_id;
		$puzzle['first'] = false;
		$puzzle['title'] = $this->getTitle($node);
		$puzzle['desc'] = $this->getDesc($node);
		$puzzle['level'] = $this->getLevel($node);
		//oseExit($puzzle);
		return $puzzle;
		*/
		return '';
	}

	function getPrice($node)
	{
		//oseExit($node);
		$payment_mode = strtolower(oseObject::getValue($node,'payment_mode'));

		$mPrice = oseObject::getValue($node,'m_price');
		$aPrice = oseObject::getValue($node,'a_price');

		if($payment_mode == 'm')
		{
			$price = parent::drawPrice($mPrice,'m');
		}
		elseif($payment_mode == 'a')
		{
			$price = parent::drawPrice($aPrice,'a');
		}
		else
		{
			$has_trial = oseObject::getValue($node,'has_trial');

			if($has_trial)
			{
				$price = parent::drawPrice($aPrice,'a');
			}
			else
			{
				$price = parent::drawPrice($aPrice,'a');
			}


		}

		return $price;
	}


}
?>