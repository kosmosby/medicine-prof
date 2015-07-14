<?php

defined('_JEXEC') or die("Direct Access Not Allowed");

class BBCanadianTax extends osePaymentTaxExtend {
	static public function getTax($amount, $country, $state){
		if(!self::isACanadianState($country, $state)) return $amount;
		return self::applyRate($amount, self::getRate($state));
	}

	static public function getRate($state){
		if(self::isGST($state)) return '.05';
		switch(strtoupper($state)){
			case 'BC':
				return '.07'; // 0.12
				break;
			case 'NS':
				return '.15';
				break;
			case 'OR':
				return '.05';
				break;
			case('NB'):
			case('ON'):
			case('NL'):
				return '.08';
				break;
			case('NS'):
				return '.10';
				break;
			default:
				return '.05';
				break;
		}
	}

	static public function isACanadianState($country, $state){
		if(!$country || !$state) return false;
		if(!in_array($country,array('Canada','CAN'))) return false;
		if($state == 'all' || empty($state)) return true;
		if(!in_array(strtoupper($state), 
			 array('AB', 'SK', 'MB', 'QC', 'PE', 'NT', 'NU', 'YT', 'NS', 'NL', 'NB', 'ON', 'BC')))
		return false;
		return true;
	}

	static public function isGST($state){
		if(in_array($state, array('AB', 'SK', 'MB', 'QC', 'PE', 'NT', 'NU', 'YT'))) return true;
		return false;
	}

	static public function applyRate($amount, $rate){
		return round(($amount + ($amount * $rate)), 2);
	}

	static public function getTaxAmount($amount, $country, $state){
		if(!self::isACanadianState($country, $state)) return 0;//$amount;
		return round($amount * (self::getRate($state)), 2);
	}

}