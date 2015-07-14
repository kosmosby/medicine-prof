<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionRegisterMsc
{
	public static function getList()
	{
		$objs = oseMscPublic::getList();

		$combo = array();
    	$combo['total'] = count($objs);
    	$combo['results'] = $objs;

    	$combo = oseJson::encode($combo);
		oseExit($combo);
	}


	public static function getOptions()
	{
		$msc_id = JRequest::getInt('msc_id', null); 
		$list = oseMscPublic::getList();
		$options = array();
		$msc = oseRegistry::call('msc');
		foreach($list as $key => $entry)
		{
			$cur_mscid = oseObject::getValue($entry,'id',0);
			if (!empty($msc_id) && $cur_mscid!=$msc_id)
			{
				continue; 
			}
			$node = $msc->getInfo($msc_id,'obj');
			$paymentInfos = $msc->getExtInfo($msc_id,'payment');
			$cart = oseMscPublic::getCart();
	    	$osePaymentCurrency = $cart->get('currency');
	    	$items = $cart->get('items');
			$option = oseMscPublic::generatePriceOption($node,$paymentInfos,$osePaymentCurrency);
			$paymentAdvInfos = $msc->getExtInfo($msc_id,'paymentAdv');
			foreach ( $option as $key => $obj )
			{
				$visible = self::checkVisibility($msc_id, $obj['id'], $paymentAdvInfos);

				if ($visible != true)
				{ 		
					unset($option[$key]);
				}
			}		
			$options = array_merge($options,$option);
		}
		$combo = array();
    	$combo['total'] = count($options);
    	$combo['results'] = $options;
    	$combo = oseJson::encode($combo);
		oseExit($combo);
	}

	public static function getOptions_E()
	{
		$msc_id = JRequest::getInt('msc_id',0);
		$msc = oseRegistry::call('msc');
		$node = $msc->getInfo($msc_id,'obj');
		$paymentInfos = $msc->getExtInfo($msc_id,'payment');

		$cart = oseMscPublic::getCart();

    	$osePaymentCurrency = $cart->get('currency');

    	$items = $cart->get('items');
    	if (isset($items[0]))
		{$oseMscPayment = $items[0];}
		else
		{$oseMscPayment = array();}

		$option = array();
		$i=0;

		$option = oseMscPublic::generatePriceOption($node,$paymentInfos,$osePaymentCurrency);
		//oseExit($option);
		$combo = array();
    	$combo['total'] = count($option);
    	$combo['results'] = $option;

    	$combo = oseJson::encode($combo);
		oseExit($combo);
	}

	private static function getAllOptions()
	{
		$list = oseMscPublic::getList();

		$options = array();

		$msc = oseRegistry::call('msc');
		foreach($list as $key => $entry)
		{
			$msc_id = oseObject::getValue($entry,'id',0);

			$node = $msc->getInfo($msc_id,'obj');
			$paymentInfos = $msc->getExtInfo($msc_id,'payment');
			
			$cart = oseMscPublic::getCart();

	    	$osePaymentCurrency = $cart->get('currency');

	    	$items = $cart->get('items');

			$option = oseMscPublic::generatePriceOption($node,$paymentInfos,$osePaymentCurrency);
			$paymentAdvInfos = $msc->getExtInfo($msc_id,'paymentAdv');
			foreach ( $option as $key => $obj )
			{
				$visible = self::checkVisibility($msc_id, $obj['id'], $paymentAdvInfos);

				if ($visible != true)
				{ 		
					unset($option[$key]);
				}
			}		
			$options = array_merge($options,$option);
		}

		$combo = array();
    	$combo['total'] = count($options);
    	$combo['results'] = $options;

    	$combo = oseJson::encode($combo);
		oseExit($combo);
	}

	function getCurrencyListCombo()
	{
		$List = oseRegistry::call('msc')->getCurrencyList();

		$options = array();
		foreach($List as $key => $value)
		{
			$options[] = array('value'=>$value['currency']) ;
		}

		$combo = array();
    	$combo['total'] = count($options);
    	$combo['results'] = $options;

    	$combo = oseJson::encode($combo);
		oseExit($combo);
	}

	function getCurrencyListWithExit()
	{
		$List = oseRegistry::call('msc')->getCurrencyList();

		$currency = oseMscPublic::getSelectedCurrency();

		$options = array();
		foreach($List as $key => $value)
		{
			$options[] = JHTML::_('select.option',  $value['currency'], $value['currency']);
		}


		$combo = JHTML::_('select.genericlist',  $options, 'ose_currency', ' class="ose_currency"  size="1" style="width:75px"', 'value', 'text', $currency );

		oseExit($combo);
	}

	function getCurrencyList()
	{
		$List = oseRegistry::call('msc')->getCurrencyList();

		$currency = oseMscPublic::getSelectedCurrency();

		$options = array();
		foreach($List as $key => $value)
		{
			$options[] = JHTML::_('select.option',  $value['currency'], $value['currency']);
		}


		$combo = JHTML::_('select.genericlist',  $options, 'ose_currency', 'onChange="javascript:oseMsc.reg.reload()" class="ose_currency"  size="1" style="width:200px"', 'value', 'text', $currency );

		return $combo;
	}

	function getMscList_M()
	{
		$cartItems = oseMscPublic::getCartItems();
		///$item = $items[0];

		$db = oseDB::instance();

		$where = array();

		$where[] = "published = 1";

		$msc_option = 0;

		if(!empty($items[0]))
		{
			$msc_id = oseObject::getValue($items[0],'entry_id');

			if(!empty($msc_id))
			{
				$where[] = "id = {$msc_id}";
			}
			$msc_option = oseObject::getValue($items[0],'msc_option');
		}

		$where = oseDB::implodeWhere($where);

		$query = " SELECT * FROM `#__osemsc_acl`"
				. $where
				." ORDER BY lft ASC"
				;

		$db->setQuery($query);

		$objs = oseDB::loadList('obj');

		//$mscExtend = oseRegistry::call('msc')->getConfig('global','obj')->msc_extend;

		$items = array();

		$session =& JFactory::getSession();
    	$osePaymentCurrency = $session->get('osePaymentCurrency',oseRegistry::call('msc')->getConfig('currency','obj')->primary_currency);

		foreach($objs as  $obj)
		{


			if(empty($msc_option))
			{
				$paymentInfos = oseRegistry::call('msc')->getExtInfo($obj->id,'payment','array');
				//oseExit($paymentInfos);
				foreach($paymentInfos as $key => $paymentInfo)
				{
					$fItem = oseRegistry::call('msc')->getPaymentMscInfo($obj->id,$osePaymentCurrency,$key);
					$fItem = oseObject::setValue($fItem,'msc_option',$key);
					$items[] = $fItem;

				}
			}
			else
			{
				$fItem = oseRegistry::call('msc')->getPaymentMscInfo($obj->id,$osePaymentCurrency,$msc_option);
				$fItem = oseObject::setValue($fItem,'msc_option',$msc_option);
				$items[] = $fItem;
			}

		}

		$total = count($items);

		$result = array();

		if($total > 0)
		{
			$result['total'] = $total;
			$result['results'] = $items;
		}
		$result = oseJson::encode($result);

		oseExit($result);
	}

	function saveOption()
	{
		$config = osemscPublic::getConfig('register','obj');
		$msc_id = JRequest::getInt('msc_id',0);
		$msc_option = JRequest::getCmd('msc_option',null);

		if($config->register_form == 'onestep')
		{
			$cart = oseMscPublic::getCart();

			//$cart = oseMscPublic::getCart();
			$item = array('entry_id'=>$msc_id,'entry_type'=>'msc','msc_option'=>$msc_option);
			$cart->addItem($item['entry_id'],$item['entry_type'],$item);
			$cart->update();
		}

		oseExit(true);
	}

	function getSelectedMsc()
	{
		$cart = oseMscPublic::getCart();

		$items = $cart->get('items');
		if (!is_array($items))
		{
			$items = array($items);
		}
		$result = array();
		$result['total'] = count($items);
		$result['results'] = array_values($items);

		return $result;
		//$result = oseJson::encode(array("result"=>$result));
		//oseExit($result);
	}
	
	function checkVisibility($msc_id, $objID, $paymentAdvInfos)
	{
		if (isset($paymentAdvInfos[$objID])) 
		{
			$mscids = self::getUserMsc(); 

			if(isset($paymentAdvInfos[$objID]['option_visibility']))
			{
				switch( $paymentAdvInfos[$objID]['option_visibility'] )
				{
					case 1:
						if (!empty($mscids))
						{
							if ($paymentAdvInfos[$objID]['nosamemembership'])
							{
								if (in_array($msc_id, $mscids))
								{
									return false; 
								}
								else
								{
									return true; 
								}			
							}
							else
							{
								return true;
							}
						}
						else
						{
							return false; 
						}					
					break;
	
					case 0:
						return true; 		
					break;
					
					case -1:
						if (!empty($mscids))
						{
							return false;
						}
						else
						{
							return true; 
						}		
					break;
				}
			}else 
			{
				return true; 
			}
			
		}
		else
		{
			return true; 
		}
	}
	
	function getUserMsc()
	{
		$db= JFactory::getDBO(); 
		$user = JFactory::getUser(); 
		$query = " SELECT `msc_id` FROM `#__osemsc_member` ". 
				 " WHERE `member_id` = ". (int)$user->id.
				 " AND status = 1 "; 
		$db->setQuery($query);
		$results = $db->loadResultArray();
		return  $results; 
	}
}
?>