<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionRegisterPayment_Mode
{
	public static function savePaymentMode()
	{
		oseMscPublic::savePaymentMode();
		oseExit(true);
	}

	function getPaymentMode()
	{
		$cart = oseMscPublic::getCart();
		$payment_mode = oseObject::getValue($cart->get('params'),'payment_mode',oseMscPublic::getPaymentMode());

		$items = array();

		$config = oseMscPublic::getConfig('global', 'obj');

		$default = oseMscPublic::getPaymentMode('default.payment_mode');
		if(!in_array($default,array('a','m')))
		{
			$default = 0;//$items[$default]['checked']= true;
		}

		if($config->payment_mode == 'a') {
			$items['a']= array('id' => 1, 'payment_mode' => 'a', 'text' => JText :: _('AUTOMATIC_RENEWAL'), 'checked' => true);
		}
		elseif($config->payment_mode == 'm') {
			$items['m']= array('id' => 1, 'payment_mode' => 'm', 'text' => JText :: _('MANUAL_RENEWAL'), 'checked' => true);
		} else {
			$items['a']= array('id' => 1, 'payment_mode' => 'a', 'text' => JText :: _('AUTOMATIC_RENEWAL'));
			$items['m']= array('id' => 2, 'payment_mode' => 'm', 'text' => JText :: _('MANUAL_RENEWAL'));
			$hasChecked= false;

			$hasChecked= false;
			foreach($items as $key => $item) {
				if($item['payment_mode'] == $payment_mode) {
					$items[$key]['checked'] = true;
					$hasChecked= true;
					$checkValue = $items[$key]['payment_mode'];
					break;
				} else {
					$items[$key]['checked']= false;
				}
			}
			if(!$hasChecked) {
				$default = oseMscPublic::getPaymentMode('default.payment_mode');

				$items[$default]['checked']= true;

				$checkValue = $items[$default]['payment_mode'];
			}
		}

		if(empty($checkValue))
		{
			$checkValue = $default;
		}

		foreach($items as $key => $item)
		{
			$items[$key] = oseObject::setValue($item,'checkValue',$checkValue);
		}


		$result = array();
		$result['total'] = count($items);
		$items = array_values($items);
		$result['results'] = $items;

		$result = oseJson::encode($result);

		oseExit($result);
	}
}
?>