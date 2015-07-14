<?php
defined('_JEXEC') or die(";)");

abstract class oseAbstractPaymentGateWay extends osePaymentOrder
{

}

class osePaymentGateWay extends oseAbstractPaymentGateWay
{
	protected $pgw_id = 0;
	protected $title = null;
	protected $folder = null;
	protected $filename = null;
	protected $checkout_method = null;
	protected $authorize_method = null;
	protected $is_cc = null;
	protected $hasIPN = null;
	protected $ipn_filename = null;
	protected $config = null;
	protected $params = null;
	protected $v = '1.0';

	//protected abstract function preparePostVars();

	function __construct($item = null)
	{
		if(!empty($item))
		{
			$this->init($item);
		}
	}

	function getGateWay($orderInfo,$payment_method = null)
	{
		if(!empty($payment_method))
		{
			$payment_method = $payment_method;
		}
		else
		{
			$payment_method = oseObject::getValue($orderInfo,'payment_method');
		}

		$item = $this->getGWInfo($payment_method);


		if(empty($item))
		{
			return false;
		}

		$path = dirname(__FILE__).DS.'gateway'.DS.$item->folder.DS.$item->filename.'.php';

		if(JFile::exists($path))
		{
			require_once($path);
			$class = 'osePaymentGateWay'.ucfirst($item->filename);
			$instance = new $class($orderInfo,$item);

			return $instance;
		}
		else
		{
			return false;
		}
	}

	protected function init($item)
	{
		$item = empty($item)?array():(array)$item;
		foreach($item as $key => $value)
		{
			if($key == 'config')
			{
				$value = empty($value)?'{}':$value;

				$this->set($key,oseJson::decode($value));
			}
			elseif($key == 'params')
			{
				$this->set($key,$this->getParams($item));
			}
			else
			{
				$this->set($key,$value);
			}
		}
	}

	function generateDesc($order_id)
	{
		return JText::_('Payment for Order ID:'.$order_id);
	}

	public function getGWInfo($payment_method)
	{
		$db = oseDB::instance();

		if(is_numeric($payment_method))
		{
			$query = " SELECT * FROM `#__osemsc_paymentgateway`"
					." WHERE `id` = '{$payment_method}'"
					;
		}
		else
		{
			$query = " SELECT * FROM `#__osemsc_paymentgateway`"
					." WHERE `filename` = ".$db->Quote($payment_method)
					;
		}

		$db->setQuery($query);

		$item = oseDB::loadItem('obj');

		return  $item;
	}

	public function checkout()
	{
		switch($this->checkout_method)
		{
			case('form'):
				return $this->generateForm();
			break;

			case('api'):

			break;

			default:

			break;
		}
	}

	public function setBillingInfo($billing)
	{
		$this->set('billingInfo',$billing);
	}

	public function matchConfig($key)
	{
		$appParams = $this->get('app_config',false);
		if($appParams)
		{
			return oseObject::getValue($appParams,$key);
		}
		else
		{
			$params = $this->get('config',false);
			if($params)
			{
				return oseObject::getValue($params,$key);
			}
			else
			{
				return null;
			}
		}
	}
}
?>