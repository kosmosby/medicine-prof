<?php
defined('_JEXEC') or die(";)");

class osePaymentCartSC extends osePaymentCart
{
	var $cart = array();
	
	function __construct()
	{
		$session =& JFactory::getSession();
		$osecart = $session->get('osecart',array());
		
		if(empty($osecart))
		{
			$this->cart['currency'] = $this->getSelectedCurrency();
			$this->cart['items'] = array();
			$this->cart['total'] = '0.00';
			$this->cart['next_total'] = '0.00';
			$this->cart['subtotal'] = '0.00';
			$this->cart['discount'] = '0.00';
			$this->cart['params'] = array();
			$this->cart['tax'] = array();
			$this->cart['registerType'] = 'cart';
		}
		else
		{
			$this->cart['registerType'] = oseObject::getValue($osecart,'registerType');
			
			if($this->cart['registerType'] != 'cart')
			{
				$this->cart['currency'] = $this->getSelectedCurrency();
				$this->cart['items'] = array();
				$this->cart['subtotal'] = '0.00';
				$this->cart['next_total'] = '0.00';
				$this->cart['total'] = '0.00';
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
				$this->cart['params'] = oseObject::getValue($osecart,'params');
				$this->cart['discount'] = oseObject::getValue($osecart,'discount');
				$this->cart['items'] = oseObject::getValue($osecart,'items',array());
				$this->cart['items'] = $this->refreshCartItems();
				$this->cart['tax'] = oseObject::getValue($osecart,'tax');
			}
			
			$this->cart['registerType'] = 'cart';
		}
		
		$this->updateParams('payment_mode','m');
		$this->update();
		
 	}
 
	
	
	
	function addItem($entry_id,$entry_type,$item)
	{
		$items = $this->get('items');
		
		$items["{$entry_type}-{$entry_id}"] = $item;
		
		$this->set('items',$items);
	}
	
	function removeItem($entry_id,$entry_type)
	{
		$items = $this->get('items');
		
		unset($items["{$entry_type}-{$entry_id}"]);
		
		$this->set('items',$items);
	}
	
	function refreshCartItems($items = array(),$currency = null)
	{
		return parent::refreshCartItems($items,$currency);
		/*
		$user = JFactory::getUser();
		$member = oseRegistry::call('member');
		
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
			foreach($items as $key => $item)
			{
				$title = oseObject::getValue($item,'title');
				
				$entry_type = oseObject::getValue($item,'entry_type');
				$item = oseObject::setValue($item,'isRenew',false);
				$msc = oseRegistry::call('msc');
				switch($entry_type )
				{
					case('msc'):
						$msc_id = oseObject::getValue($item,'entry_id');
						$msc_option = oseObject::getValue($item,'msc_option');
						$mscInfo = $msc->getPaymentMscInfo($msc_id,$currency,$msc_option);
						$item = array_merge($item,$mscInfo);
						
						if($checkRenew)
						{
							$hasHistory = $member->hasHistory(array('join','activate','renew'),oseObject::getValue($item,'id'),null);
							//echo oseDB::instance()->_sql;
							
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
							$item = oseObject::setValue($item,'standard_renewal_raw_price',oseObject::getValue($item,'standard_raw_price'));
							$item = oseObject::setValue($item,'standard_renewal_price', $currency.' '.oseObject::getValue($item,'standard_raw_price'));
						}
						
					break;
					
					case('license'):
						$license_key = oseObject::getValue($item,'entry_id');
						$license = oseRegistry::call('lic')->getInstance(0);
			
						$licenseInfo = $license->getKeyInfo($license_key,'obj');
						$licenseInfoParams = oseJson::decode(oseObject::getValue($licenseInfo,'params'));
						
						//oseExit($licenseInfoParams);
						$mscInfo = oseRegistry::call('msc')->getPaymentMscInfo($licenseInfo->msc_id,$currency,$licenseInfoParams->msc_option);
						
						$item = array_merge($item,$mscInfo);
						
						$extLicInfo = $msc->getExtInfo(oseObject::getValue($licenseInfoParams,'msc_id'),'lic','obj');
						$discount_num = oseObject::getValue($licenseInfoParams,'discount',0);
						$discount_type = oseObject::getValue($licenseInfoParams,'discount_type','rate');
					
						$priceSystem = oseRegistry::call('payment')->getInstance('Price');
						$priceSystem->setSelectedCurrency($currency);
						$total = $priceSystem->discount(oseObject::getValue($mscInfo,'standard_raw_price'),$discount_num,$discount_type);
						
						$total = number_format($total,2,'.','');
						$item = oseObject::setValue($item,'msc_option',$extLicInfo->license_msc_option);
						
						//$item = oseObject::setValue($item,'first_raw_price',$total);
						//$item = oseObject::setValue($item,'second_raw_price',$total);
						//$item = oseObject::setValue($item,'standard_raw_price',$total);
						
						//$item = oseObject::setValue($item,'first_price',$currency.' '.$total);
						//$item = oseObject::setValue($item,'second_price',$currency.' '.$total);
						//$item = oseObject::setValue($item,'standard_price',$currency.' '.$total);
						
						$item = oseObject::setValue($item,'standard_renewal_raw_price',$total);
						$item = oseObject::setValue($item,'standard_renewal_price', $currency.' '.$total);
					break;
				}
				
				//oseExit($item);		
				$items[$key] = $item;
			}
			
			$this->set('items',$items); 
			
			$this->refreshSubTotal();
		}
		
		return $items;
		*/
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
	
	/*
	 *  To get the ultimate price
	 */
	function refreshSubTotal()
	{
		return parent::refreshSubTotal();
		/*
		$items = $this->get('items');
		
		if(empty($items))
		{
			$items = array();
		}
		
		$params = $this->get('params');
		
		$coupon = $this->getParams('coupon');
		
		
		// ncd short for no coupon discount
		// 2cd short for 2 coupon discount
		
		$price	   = array();
		$price_2cd = array();
		$price_ncd = array(0);
		$nextPrice = array(0);
		
		
		$renewDiscount = array();
		$renewDiscount_ncd = array();
		$renewDiscount_2cd = array();
		
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
		$total = number_format($total,2,'.','');
		$this->set('total',$total);
		
		// Recurring Standard Price
		$totalNext = number_format($totalNext,2,'.','');
		$this->set('next_total',$totalNext);
		
		return $total;
		*/
	}
	
	
	
	function countCart()
	{
		$num = count($this->get('items'));
		if($num > 0)
		{
			return 	$num;
		}
		else
		{
			return false;
		}
	}
	
	/*///////////////////////////////////////////////////////////////////
	 * ////////////////////// Extends ////////////////////////////////////
	 ////////////////////////////////////////////////////////////////////
	function getCart()
	{
		$session =& JFactory::getSession();
		$osecart = $session->get('osecart',array());
		
		return $osecart;
	}
	
	function getSelectedCurrency()
	{
		$session =& JFactory::getSession();
		$osePaymentCurrency = $session->get('osePaymentCurrency',oseRegistry::call('msc')->getConfig('currency','obj')->primary_currency);
		
		return $osePaymentCurrency;
	}
	
	function setSelectedCurrency($ose_currency)
	{
		$this->set('currency',$ose_currency);
	}
	
	function update()
	{
		$session =& JFactory::getSession();
		$session->set('osecart',$this->cart);
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
	
	private function set($key,$value)
	{
		$this->cart[$key] = $value;
	}
	
	function getSubTotal()
	{
		return $this->get('subtotal');
	}
	
	function setSubTotal($subtotal)
	{
		$this->set('subtotal',$subtotal);
	}
	*/
}
?>