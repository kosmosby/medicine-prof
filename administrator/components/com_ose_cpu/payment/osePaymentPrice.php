<?php
defined('_JEXEC') or die(";)");
class osePaymentPrice {
	protected $pCurrency= null;
	protected $sCurrency= null;
	protected $rateList= array();
	function __construct($pCurrency, $rateList) {
		$this->setPrimary($pCurrency);
		$this->setExchangeRateList($rateList);
		$this->setSelectedCurrency($pCurrency);
	}
	function setPrimary($pCurrency) {
		$this->pCurrency= $pCurrency;
		return $this->pCurrency;
	}
	function setSelectedCurrency($sCurrency) {
		$this->sCurrency= $sCurrency;
	}
	function setExchangeRateList($rateList) {
		$rateList= oseObject :: setValue($rateList, $this->pCurrency, array('currency' => $this->pCurrency, 'rate' => 1));
		$this->rateList= $rateList;
	}
	function getPrimary() {
		return $this->pCurrency;
	}
	function getExchangeRateList() {
		return $this->rateList;
	}
	function getRate($currency) {
		$obj= oseObject :: getValue($this->rateList, $currency);
		return oseObject :: getValue($obj, 'rate');
	}
	function exchangeCurrency($price, $currency) {
		$rate= $this->getRate($currency);
		$newPrice= $price * $rate;
		return $newPrice;
	}
	function pricing($price, $currency, $msc_id, $msc_option, $renew= false) {
		$price= $this->exchangeCurrency($price, $currency);
		// discount start
		if($renew) {
			$paymentAdvs= oseRegistry :: call('msc')->getExtInfo($msc_id, 'paymentAdv');
			if(!empty($paymentAdvs)) {
				$paymentAdv= oseObject :: getValue($paymentAdvs, $msc_option);
				if(!empty($paymentAdv)) {
					$discount_num= oseObject :: getValue($paymentAdv, 'renew_discount');
					$discount_type= oseObject :: getValue($paymentAdv, 'renew_discount_type');
					$price= $this->discount($price, $discount_num, $discount_type);
				}
			}
		}
		//$price = $price;
		// discount end
		$price= number_format($price, 2, '.', '');
		return $price;
		//$price =
	}
	function discount($price, $discount_num, $discount_type) {
		switch($discount_type) {
			case('rate') :
				$newPrice= $this->discountByRate($price, $discount_num);
				break;
			case('amount') :
				$newPrice= $this->discountByNum($price, $discount_num);
				break;
			default :
				$newPrice= $price;
				break;
		}
		return $newPrice; //number_format($newPrice,2,'.','');
	}
	function discountByNum($price, $amount, $is_round= false, $round= 2) {
		if($is_round) {
			$price= $price - $this->exchangeCurrency($amount, $this->sCurrency);
			$price= round($price, $round);
		} else {
			$price= $price - $this->exchangeCurrency($amount, $this->sCurrency);
		}
		if($price < 0) {
			$price= 0;
		}
		return $price;
	}
	function discountByRate($price, $rate, $is_round= false, $round= 2) {
		$price= (float)($price *(100 - $rate) / 100);
		if($is_round) {
			$price= round($price, $round);
		}
		return $price;
	}
	function getDiscountByRate($rate, $price, $is_round= true, $round= 2) {
		if($is_round) {
			$discount= $price * $rate / 100;
			$discount= round($discount, $round);
		} else {
			$discount= (float)($price * $rate / 100);
		}
		return $discount;
	}
}
?>