<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionRegisterPayment_sisow
{
	function getIssuerList()
	{
		$payment= oseRegistry :: call('payment');
		$IssuerList = $payment->getInstance('Order')->SisowgetIssuerList();
		$result = array();
				
		if(empty($IssuerList))
		{
			$result['total'] = 0;
			$result['results'] = '';
		}else{
			$items = array();
			foreach($IssuerList as $key => $value)
			{
				$item = array();
				$item['id'] = $key;
				$item['name'] = $value;
				$items[] = $item;
			}
			$result['total'] = count($items);
			$result['results'] = $items;
		}
		return $result;
	}

}
?>