<?php
defined('_JEXEC') or die(";)");

class osePaymentCartOS extends osePaymentCart
{
	var $cart = array();
	
	function __construct()
	{
		$session =JFactory::getSession();
		$osecart = $session->get('osecart',array());
		
		if(empty($osecart))
		{
			$this->cart['currency'] = $this->getSelectedCurrency();
			$this->cart['items'] = array();
			$this->cart['total'] = '0.00';
			$this->cart['next_total'] = '0.00';
			$this->cart['subtotal'] = '0.00';
			$this->cart['discount'] = '0.00';
			$this->cart['tax'] = array();
			$this->cart['params'] = array();
			$this->cart['registerType'] = 'onestep';
		}
		else
		{
			$this->cart['registerType'] = oseObject::getValue($osecart,'registerType');
			
			if($this->cart['registerType'] != 'onestep')
			{
				$this->cart['currency'] = $this->getSelectedCurrency();
				$this->cart['items'] = array();
				$this->cart['total'] = '0.00';
				$this->cart['next_total'] = '0.00';
				$this->cart['subtotal'] = '0.00';
				$this->cart['discount'] = '0.00';
				$this->cart['tax'] = array();
				$this->cart['params'] = array();
			}
			else
			{
				$this->cart['currency'] = $this->getSelectedCurrency();
				$this->cart['total'] = oseObject::getValue($osecart,'total');
				$this->cart['next_total'] = oseObject::getValue($osecart,'next_total');
				$this->cart['subtotal'] = oseObject::getValue($osecart,'subtotal');
				$this->cart['tax'] = oseObject::getValue($osecart,'tax');
				$this->cart['params'] = oseObject::getValue($osecart,'params');
				$this->cart['discount'] = oseObject::getValue($osecart,'discount');
				$this->cart['items'] = oseObject::getValue($osecart,'items');
				$this->cart['items'] = $this->refreshCartItems();
			}
			
			$this->cart['registerType'] = 'onestep';
			$this->update();
		}
 	}
 
	
	function getCart()
	{
		$session =JFactory::getSession();
		$osecart = $session->get('osecart',array());
		
		return $osecart;
	}
	
	function getSelectedCurrency()
	{
		$session =JFactory::getSession();
		$osePaymentCurrency = $session->get('osePaymentCurrency',oseRegistry::call('msc')->getConfig('currency','obj')->primary_currency);
		
		return $osePaymentCurrency;
	}
	
	function setSelectedCurrency($ose_currency)
	{
		$this->set('currency',$ose_currency);
	}
	
	function update()
	{
		$session =JFactory::getSession();
		
		$session->set('osecart',$this->cart);
	}
	
	function addItem($entry_id,$entry_type,$item)
	{
		$items = $this->get('items');
		
		$items[0] = $item;
		
		$this->set('items',$items);
	}
	
	function removeItem($entry_id,$entry_type)
	{
		$items = $this->get('items');
		
		unset($items[0]);
		
		$this->set('items',$items);
	}
	
	function set($key,$value)
	{
		$this->cart[$key] = $value;
	}
	
	function get($key)
	{
		if(isset($this->cart[$key]))
		{
			return $this->cart[$key];
		}
		else
		{
			return null;
		}
	}
	
	
	function refreshCartItems($items = array(),$currency = null)
	{
		$user = JFactory::getUser();
		$member = oseRegistry::call('member');
		
		$items = $this->get('items');
		$currency = $this->get('currency');
	
		if(!$user->guest)
		{
			$member->instance($user->id);
			$checkRenew = true;
		}
		else
		{
			$checkRenew = false;
		}
		
		if(count($items) > 0)
		{
			$msc = oseRegistry::call('msc');
			foreach($items as $key => $item)
			{
				$title = oseObject::getValue($item,'title');
				
				$entry_type = oseObject::getValue($item,'entry_type');
				$item = oseObject::setValue($item,'isRenew',false);
				
				switch($entry_type )
				{
					case('msc'):
						$msc_id = oseObject::getValue($item,'entry_id');
						$msc_option = oseObject::getValue($item,'msc_option');
						$mscInfo = $msc->getPaymentMscInfo($msc_id,$currency,$msc_option);
						
						if(empty($mscInfo))
						{
							unset($items[$key]);
							break;
						}
						
						$item = array_merge($item,$mscInfo);
						
						if(oseObject::getValue($item,'eternal'))
						{
							$this->updateParams('payment_mode','m');
						}
						
						if($checkRenew)
						{
							$hasHistory = $member->hasHistory(array('join','activate','renew'),oseObject::getValue($item,'id'),null);
							//echo oseDB::instance()->_sql;
							
							if($this->getParams('payment_mode') == 'm')
							{
								if($hasHistory)
								{
									$item = oseObject::setValue($item,'isRenew',true);
									
									if(oseObject::getValue($item,'has_trial'))
									{
										$total = oseObject::getValue($item,'standard_raw_price','0');
										$item = oseObject::setValue($item,'has_trial',0);
										$item = oseObject::setValue($item,'first_raw_price',$total);
										$item = oseObject::setValue($item,'first_price',$currency.' '.$total);
									}
								}
								else
								{
									$item = oseObject::setValue($item,'standard_renewal_raw_price',oseObject::getValue($item,'standard_raw_price'));
									$item = oseObject::setValue($item,'standard_renewal_price', $currency.' '.oseObject::getValue($item,'standard_raw_price'));
								}
							}
							else
							{
								if($hasHistory)
								{
									$item = oseObject::setValue($item,'isRenew',true);
									
									if(oseObject::getValue($item,'has_trial'))
									{
										$total = oseObject::getValue($item,'standard_raw_price','0');
										$item = oseObject::setValue($item,'has_trial',0);
										$item = oseObject::setValue($item,'first_raw_price',$total);
										$item = oseObject::setValue($item,'first_price',$currency.' '.$total);
									}
								}
								
								$item = oseObject::setValue($item,'standard_renewal_raw_price',oseObject::getValue($item,'standard_raw_price'));
								$item = oseObject::setValue($item,'standard_renewal_price', $currency.' '.oseObject::getValue($item,'standard_raw_price'));
							}	
						}
						else
						{
							if($this->getParams('payment_mode') == 'm')
							{
								
								$item = oseObject::setValue($item,'isRenew',true);
								
								if(oseObject::getValue($item,'has_trial'))
								{
									$total = oseObject::getValue($item,'standard_raw_price','0');
									$item = oseObject::setValue($item,'has_trial',0);
									$item = oseObject::setValue($item,'first_raw_price',$total);
									$item = oseObject::setValue($item,'first_price',$currency.' '.$total);
								}
								
							}
							
							$item = oseObject::setValue($item,'standard_renewal_raw_price',oseObject::getValue($item,'standard_raw_price'));
							$item = oseObject::setValue($item,'standard_renewal_price', $currency.' '.oseObject::getValue($item,'standard_raw_price'));
						}
						
					break;
					
					case('license'):
						$license_key = oseObject::getValue($item,'entry_id');
						$license = oseRegistry::call('lic')->getInstance(0);
			
						$licenseInfo = $license->getKeyInfo($license_key,'obj');
						$licenseInfoParams = oseJson::decode(oseObject::getValue($licenseInfo,'params'));
						
						
						$mscInfo = oseRegistry::call('msc')->getPaymentMscInfo($licenseInfo->msc_id,$currency,$licenseInfoParams->msc_option);
						
						if(empty($mscInfo))
						{
							unset($items[$key]);
							break;
						}
						
						$item = array_merge($item,$mscInfo);
						
						if(oseObject::getValue($item,'eternal'))
						{
							$this->updateParams('payment_mode','m');
						}
						//$extLicInfo = $msc->getExtInfo(oseObject::getValue($licenseInfoParams,'msc_id'),'lic','obj');
						$discount_num = oseObject::getValue($licenseInfoParams,'discount',0);
						$discount_type = oseObject::getValue($licenseInfoParams,'discount_type','rate');
					
						$priceSystem = oseRegistry::call('payment')->getInstance('Price');
						$priceSystem->setSelectedCurrency($currency);
						$total = $priceSystem->discount(oseObject::getValue($mscInfo,'standard_raw_price'),$discount_num,$discount_type);
						
						$total = number_format($total,2,'.','');
						$item = oseObject::setValue($item,'msc_option',$licenseInfoParams->msc_option);
						
						if(oseObject::getValue($item,'has_trial'))
						{
							$firstPrice = oseObject::getValue($item,'standard_raw_price','0');
							//$item = oseObject::setValue($item,'has_trial',0);
							$item = oseObject::setValue($item,'first_raw_price',$firstPrice);
							$item = oseObject::setValue($item,'first_price',$currency.' '.$firstPrice);
						}
						
						$item = oseObject::setValue($item,'standard_renewal_raw_price',$total);
						$item = oseObject::setValue($item,'standard_renewal_price', $currency.' '.$total);
					break;
				}
				
				$mscInfoParams = oseJson::decode(oseObject::getValue($mscInfo,'params','{}'));
				
				
				$db = oseDB::instance();
				if(!oseObject::getValue($mscInfoParams,'after_payment_menuid',false))
				{
					$query = " SELECT * FROM `#__menu`"
							." WHERE `link` LIKE 'index.php?option=com_osemsc&view=member'"
							;
					$db->setQuery($query);
					$mItem = oseDB::loadItem('obj');
					
					if(empty($mItem))
					{
						$link = "index.php?option=com_osemsc&view=member";
					}
					else
					{
						$link = "index.php?option=com_osemsc&view=member&Itemid={$mItem->id}";
					}
				}
				else
				{
					$query = " SELECT * FROM `#__menu`"
							." WHERE id = '{$mscInfoParams->after_payment_menuid}'"
							;
					$db->setQuery($query);
					$mItem = oseDB::loadItem('obj');
					
					//@ todo
					$link = $mItem->link."&Itemid={$mItem->id}";
				}//*/
				$this->updateParams('returnUrl',urlencode($link));
				
				//oseExit($item);		
				$items[$key] = $item;
			}
			
			$this->set('items',$items); 
			
			$this->refreshSubTotal();
		}
		
		return $items;
	}
	
	function setCartItems($items,$vkey,$value)
	{	
		if(count($items) > 0)
		{
			foreach($items as $key => $item)
			{
				$item = oseObject::setValue($item,$vkey,$value);
				$items[$key] = $item;
			}
		}
		
		$this->set('items',$items);
	}
	
	function refreshSubTotal()
	{
		$items = $this->get('items');
		
		if(empty($items))
		{
			$items = array();
		}
		
		$params = $this->get('params');
		
		$coupon = $this->getParams('coupon');
		
		/*
		 * ncd short for no coupon discount
		 * 2cd short for 2 coupon discount
		 */
		$price	   = array();
		$price_2cd = array();
		$price_ncd = array(0);
		$nextPrice = array(0);
		
		
		$renewDiscount = array();
		$renewDiscount_ncd = array();
		$renewDiscount_2cd = array();
		$coupon_msc_ids = explode(',',$this->getParams('coupon_msc_ids'));
		
		foreach($items as $key => $item)
		{
			$itemPrice = oseObject::getValue($item,'first_raw_price');
			$itemDiscount = oseObject::getValue($item,'standard_raw_price') - oseObject::getValue($item,'standard_renewal_raw_price');;
			switch(oseObject::getValue($item,'entry_type'))
			{
				case('license'):
					$price_ncd[] = $itemPrice;
					$renewDiscount_ncd[] = $itemDiscount;
				break;
				
				case('msc'):
				default:
					// decide whether the coupon run effects
					if(in_array('all',$coupon_msc_ids) || in_array(oseObject::getValue($item,'id'),$coupon_msc_ids))
					{
						if($this->getParams('coupon_range') == 'new_member_only')
						{
							if(oseObject::getValue($item,'isRenew'))
							{
								$price_ncd[] = $itemPrice;
								$renewDiscount_ncd[] = $itemDiscount;
							}
							else
							{
								$price_2cd[] = $itemPrice;
								$renewDiscount_2cd[] = $itemDiscount;
							}
						}
						else
						{
							$price_2cd[] = $itemPrice;
							$renewDiscount_2cd[] = $itemDiscount;
						}
					}
					else
					{
						$price_ncd[] = $itemPrice;
						$renewDiscount_ncd[] = $itemDiscount;
					}
						
				break;
			}
			$price[] = $itemPrice;
				
			$itemPriceNext = oseObject::getValue($item,'second_raw_price');
			$nextPrice[] = $itemPriceNext;
			
			$renewDiscount[] = $itemDiscount;
		}
		
		$totalNext = array_sum($nextPrice);
		
		
		$subtotal = array_sum($price);
		$subtotal_2cd = array_sum($price_2cd);
		$subtotal_ncd = array_sum($price_ncd);
		
		$renewDiscount = array_sum($renewDiscount);
		$renewDiscount_ncd = array_sum($renewDiscount_ncd);
		$renewDiscount_2cd = array_sum($renewDiscount_2cd);
		
		if($this->getParams('payment_mode') == 'm')
		{
			$subtotal = $totalNext;
		}
		
		// Count Total Price
		$total = $subtotal;
		$subtotal_2cd = $subtotal - $subtotal_ncd - $renewDiscount_2cd;
		
		if(!empty($coupon))
		{
			$discount_num = oseObject::getValue($params,'coupon_discount',0);
			$discount_type = oseObject::getValue($params,'coupon_discount_type','rate');
		
			$priceSystem = oseRegistry::call('payment')->getInstance('Price');
			$subtotal_2cd = $priceSystem->discount($subtotal_2cd,$discount_num,$discount_type);
		}
		
		// Discount
		// coupon discount amount => $subtotal - $subtotal_ncd - $subtotal_2cd
		$discount = $subtotal - $subtotal_ncd - $subtotal_2cd + $renewDiscount_ncd;
		//oseExit(array($subtotal,$subtotal_ncd,$subtotal_2cd,$renewDiscount,$discount,$params));
		//oseExit($this->cart);
		$this->set('discount',number_format($discount,2,'.',''));
		//
		
		// Subtotal
		$this->setSubTotal(number_format($subtotal,2,'.',''));
		
		// Total
		$total = $subtotal - $discount;
		//	Tax
		if($this->getTaxParams('has_file_control'))
		{
			$country = $this->getTaxParams('country');
			$state = $this->getTaxParams('state');
			require_once(OSECPU_B_PATH.DS.'payment'.DS.'tax'.DS.$this->getTaxParams('file_control'));
			$taxFile = basename($this->getTaxParams('file_control'),'.php');
			$taxAmount = call_user_func(array($taxFile,'getTaxAmount'),$total,$country,$state);
			$nextTaxAmount = call_user_func(array($taxFile,'getTaxAmount'),$totalNext,$country,$state);
		}
		else
		{
			$rate = $this->getTaxParams('rate',0);
			$taxAmount = round($total * $rate/100, 2);
			$nextTaxAmount = round($totalNext * $rate/100, 2);
		}
		
		$this->updateTaxParams('amount',number_format($taxAmount,2,'.',''));
		$this->updateTaxParams('next_amount',number_format($nextTaxAmount,2,'.',''));
		
		
		$total = $total + $taxAmount;
		$total = number_format($total,2,'.','');
		$this->set('total',$total);
		
		// Recurring Standard Price
		$totalNext = $totalNext + $nextTaxAmount;
		$totalNext = number_format($totalNext,2,'.','');
		$this->set('next_total',$totalNext);
		
		return $total;
	}
	
	function getSubTotal()
	{
		return $this->get('subtotal');
	}
	
	function setSubTotal($subtotal)
	{
		$this->set('subtotal',$subtotal);
	}
	
	function countCart()
	{
		$num = count($this->get('items'));
		if($num == 1)
		{
			return 	$num;
		}
		else
		{
			return false;
		}
	}
	
	function updateParams($key,$value)
	{
		$params = $this->get('params');
		
		$params = oseObject::setValue($params,$key,$value);
		
		$this->set('params',$params);
	}
	
	function getParams($key)
	{
		$params = $this->get('params');
		
		return  oseObject::getValue($params,$key);
	}
	
	function init()
	{
		$this->cart = array();
		$this->update();
	}
	
	function getTaxParams($key,$default = null)
	{
		$params = $this->get('tax');
		
		return  oseObject::getValue($params,$key,$default);
	}
	
	function updateTaxParams($key,$value)
	{
		$params = $this->get('tax');
		
		$params = oseObject::setValue($params,$key,$value);
		
		$this->set('tax',$params);
	}
	
	function output()
	{
		// p short for payment
		$p = array();
		$p['items'] = $this->get('items');
		$oneItem = $p['items'][0];
		$p['payment_price'] = $this->get('total');
		$p['payment_currency'] = $this->get('currency');
        //$p['create_date'] = oseHTML::getDateTime();//date("Y-m-d H:i:s");
		//$p['payment_method'] = $payment_method;
		$p['payment_mode'] = $this->getParams('payment_mode');
		$p['payment_from'] = 'system_reg';

		$p['params'] = array();
		$p['params']['total'] = $this->get('total');
		$p['params']['next_total'] = $this->get('next_total');
		$p['params']['discount'] = $this->get('discount');
		$p['params']['subtotal'] = $this->getSubtotal();
		$p['params']['coupon_user_id'] = $this->getParams('coupon_user_id');
		$p['params']['gross_tax'] = $this->getTaxParams('amount');
		$p['params']['next_gross_tax'] = $this->getTaxParams('next_amount');
		$p['params']['vat_number'] = $this->getTaxParams('vat_number');
		//$p['params']['timestamp'] = uniqid("{$member_id}_",true);
		$p['params']['returnUrl'] = $this->getParams('returnUrl');
		
		if($p['payment_mode'] == 'a')
		{
			$p['params']['has_trial'] = oseObject::getValue($oneItem,'has_trial',0);
		}
		else
		{
			$p['params']['has_trial'] = 0;
		}
		
		$p['params']['a1'] = $p['params']['total'];
		$p['params']['p1'] = oseObject::getValue($oneItem,'p1',0);
		$p['params']['t1'] = oseObject::getValue($oneItem,'t1');
		$p['params']['a3'] = $this->get('next_total');
		$p['params']['p3'] = oseObject::getValue($oneItem,'p3',0);
		$p['params']['t3'] = oseObject::getValue($oneItem,'t3');
    	$p['params'] = oseJSON::encode($p['params']);
		//$this->init();
		
		return $p;
	}
}
?>