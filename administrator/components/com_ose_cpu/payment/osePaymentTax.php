<?php
defined('_JEXEC') or die(";)");
class osePaymentTax {
	static function getTax($country,$state)
	{
		$db = oseDB::instance();
		$result= array();
		$result['country'] = $country;
		$result['state'] = $state;
		$result['rate'] = 0;
		$result['file_control'] = null;
		$result['has_file_control'] = 0;


		$config = oseRegistry::call('msc')->getConfig('','obj');
		$result['rate'] = oseObject::getValue($config,'global_tax',0);
		$result['vat_number'] = oseObject::getValue($config,'vat_number',false);
		if(empty($country))
		{
			return $result;
		}

		if(empty($state))
		{
			$state = 'all';
		}

		// Enable Europe VAT Number Validation
		$enable_VAT = false;
		$EU = array('AUT','BEL', 'BGR', 'CYP', 'CZE', 'DNK', 'EST', 'FIN', 'FRA', 'DEU', 'GRC', 'HUN', 'IRL', 'ITA', 'LVA', 'LTU', 'LUX', 'MLT', 'NLD', 'POL', 'PRT', 'ROM', 'SVN', 'SVK', 'ESP', 'SWE', 'GBR',  'HRV', 'ISL', 'HNE', 'TUR', 'MKD',  'ALB', 'AND', 'ARM', 'AZE', 'BLR', 'BIH', 'GEO', 'LIE', 'MCO', 'MDA', 'NOR', 'RUS', 'SMR', 'SRB', 'CHE', 'UKR', 'VAR');
		$default_country = oseObject::getValue($config,'default_country',0);
		$query = "SELECT country_3_code FROM `#__osemsc_country` WHERE `country_id` = '{$default_country}'";
		$db->setQuery($query);
		$default_country = $db->loadResult();
		if(oseObject::getValue($config,'enable_europe_vatnumber_validate',false) && $country == $default_country || in_array($country,$EU))
		{
			if(oseObject::getValue($config,'enable_europe_vatnumber_validate',false) && oseObject::getValue($config,'vat_number',false))
			{
				$enable_VAT=true;
			}
		}
		if($enable_VAT)
		{
			require_once(dirname(__FILE__).DS.'tax'.DS.'EuropeVATValidate.php');
			$check = new  Vat_checker();

			$query = " SELECT * FROM `#__osemsc_country`"
					." WHERE `country_3_code` = '{$country}'"
					;
			$db->setQuery($query);

			$item = oseDB::loadItem('obj');
			$vat_number = JRequest::getCmd('bill_vat_number');
			if($check->is_valid_regex($vat_number,$item->country_2_code) !== false)
			{
				if($check->is_valid_europa($vat_number,$item->country_2_code) !== false)
				{
					$result['rate'] = 0;
				}else{
					$enable_VAT = false;
				}
			}
			
			$list = array('ALB','ARM','MCO','SMR','SRB','RUS','ISL','HRV','MDA','NOR','CHE','TUR');
			if(in_array($country,$list))
			{
				$result['rate'] = 0;
				$enable_VAT = true;
			}
		}
		if(!$enable_VAT)
		{
			$query = " SELECT * FROM `#__osemsc_tax`"
					." WHERE `country_3_code` = '{$country}' AND `state_2_code` = 'all'"
					;
			$db->setQuery($query);
			$all = oseDB::loadItem('obj');
			if(empty($all))
			{
				$query = " SELECT * FROM `#__osemsc_tax`"
						." WHERE `country_3_code` = '{$country}' AND `state_2_code` = '--'"
						;
				$db->setQuery($query);
				$all = oseDB::loadItem('obj');
			}
			$noState = false;
			if(!empty($state))
			{
				$query = " SELECT * FROM `#__osemsc_tax`"
						." WHERE `country_3_code` = '{$country}' AND `state_2_code` ='{$state}'"
						;
				$db->setQuery($query);
				$item = oseDB::loadItem('obj');

				if(!empty($item))
				{
					if($item->has_file_control)
					{
						$result['file_control'] = $item->file_control;
						$result['has_file_control'] = 1;
					}
					else
					{
						$result['rate'] = $item->rate;
					}
				}
				else
				{
					$noState = true;
				}
			}

			if(!empty($all) && $noState)
			{
				if($all->has_file_control)
				{
					$result['file_control'] = $all->file_control;
					$result['has_file_control'] = 1;
				}
				else
				{
					$result['rate'] = $all->rate;
				}
			}
		}


		return $result;
	}

}

abstract class osePaymentTaxExtend
{
	abstract function getTaxAmount($amount, $country, $state);
}
?>