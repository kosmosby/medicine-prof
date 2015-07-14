<?php
defined('_JEXEC') or die(";)");


class osePaymentSC extends osePayment
{
	protected $task = array();
	protected $instance = array();
	
	function __construct()
	{
		$this->setRegisteredTasks();
		$this->setRegisteredInstances();
	}
	
	protected function registerTask($task,$funcName)
	{
		$this->task[$task] = $funcName;
	}
	
	protected function setRegisteredTasks()
	{
		// NULL
	}
	
	protected function registerInstance($task,$instanceName)
	{
		$this->instance[$task] = $instanceName;
	}
	
	protected function setRegisteredInstances()
	{
		// NULL
		$this->registerInstance('MscList','osePaymentMscList');
		$this->registerInstance('View','osePaymentView');
		$this->registerInstance('Price','osePaymentPrice');
		//$this->registerInstance('Order','osePaymentOrder');
		$this->registerInstance('Tax','osePaymentTax');
		$this->registerInstance('GateWay','osePaymentGateWay');
	}

	function __toString()
	{
		return get_class($this);
	}
	
	static public function getInstance($type)
	{
		static $instance;
		$className = "osePaymentSC{$type}";
		
		$arrayInstance = array();
		$arrayInstance['MscList'] = 'osePaymentMscList';
		$arrayInstance['View'] = 'osePaymentView';
		$arrayInstance['Price'] = 'osePaymentPrice';
		$arrayInstance['Tax'] = 'osePaymentTax';
		$arrayInstance['GateWay'] = 'osePaymentGateWay';
		
		if(isset($arrayInstance[$type]))
		{
			if(strtolower($type) == 'price')
			{
				$primaryCurrency = osePayment::getCurrencyInfo();
				$instance = new osePaymentPrice($primaryCurrency->value,oseJson::decode($primaryCurrency->default));
				return $instance;
			}
			
			$className = $arrayInstance[$type];
		}
		else
		{
			$className = "osePaymentSC{$type}";
		}
		
		if(!$instance instanceof $className)
		{
			$instance = new $className();
		}
		
		return $instance;
	}
	
/*
	public function getInstanceByVersionUnable()
	{
		static $instance;

		if(!empty($instance))
		{
			return $instance;
		}

		jimport( 'joomla.version' );
		$version = new JVersion();
		$version = substr($version->getShortVersion(),0,3);

		if($version == '1.5')
		{
			$className = get_class($this).'_J15';
			$instance = new $className();
		}
		elseif($version == '1.6')
		{
			$className = get_class($this).'_J16';
			$instance = new $className();
		}
		else
		{

		}

		return $instance;
	}

	public function getInstance($type)
	{
		static $instance;
		
		if(isset($this->instance[$type]))
		{
			if(strtolower($type) == 'price')
			{
				$primaryCurrency = osePayment::getCurrencyInfo();
				$instance = new osePaymentPrice($primaryCurrency->value,oseJson::decode($primaryCurrency->default));
				return $instance;
			}
			
			$className = $this->instance[$type];
		}
		else
		{
			$className = "osePaymentSC{$type}";
		}
		

		if(!$instance instanceof $className)
		{
			$instance = new $className();
		}

		return $instance;
	}

	
	function generateOrder($msc_id, $user_id,$params)
	{
		$order = $this->getInstance('Order');

		return $order->generateOrder($msc_id, $user_id,$params);
	}

	function generateOrderNumber($user_id)
	{
		$order_number = osePaymentOrder::generateOrderNumber($user_id);

		return $order_number;
	}

	function generateOrderParams($msc_id,$price,$payment_mode,$msc_option)
	{
		$order = $this->getInstance('Order');

		return $order->generateOrderParams($msc_id,$price,$payment_mode,$msc_option);
	}

	function updateOrder($order_id,$status,$params = array())
	{
		$order = $this->getInstance('Order');

		return $order->updateOrder($order_id,$status,$params);
	}

	function getCurrencyInfo()
	{
		$primaryCurrency = oseRegistry::call('msc')->getConfigItem('primary_currency','currency','obj');

		return $primaryCurrency;
	}

	function countDiscount($price,$payment)
	{
		$newPrice = 0;

		if(empty($payment->discount))
		{
			return $price;
		}

		if($payment->discount_unit == 'rate')
		{
			$newPrice = osePaymentPrice::discountByRate($payment->discount,$price);
		}
		else
		{
			$newPrice = osePaymentPrice::discountByNum($payment->discount,$price);
		}

		return $newPrice;
	}

	function GetPaypalForm($orderInfo,$params = array())
	{
		$pConfig= oseMscConfig :: getConfig('payment', 'obj');
		if ($pConfig->paypal_mode=='Website Payment Pro')
		{
			
			$html = $this->getInstance('Order')->PaypalAPIPostForm($orderInfo,$params);
		}
		else
		{
			$html = $this->getInstance('Order')->PaypalExpPostForm($orderInfo,$params);
		}
		return $html;
	}

	function PaypalAPIGetOrderDetails($token)
	{
		return $this->getInstance('Order')->PaypalAPIGetOrderDetails($token);
	}
	function PaypalAPIPay($order_id, $token)
	{
		return $this->getInstance('Order')->PaypalAPIPay($order_id, $token);
	}
	function PaypalAPICreateProfile($orderID, $token)
	{
		return $this->getInstance('Order')->PaypalAPICreateProfile($orderID, $token);
	}
	function getGCOForm($orderInfo)
	{
		return $this->getInstance('Order')->get_gcoform($orderInfo);
	}

	function get2COForm($orderInfo)
	{
		return $this->getInstance('Order')->get_2coform($orderInfo);
	}

	function getCCForm($orderInfo)
	{
		return $this->getInstance('Order')->get_ccform($orderInfo);
	}

	function getOrder($where = array(),$type = 'array')
	{
		$order = new osePaymentOrder();
		return $order->getOrder($where,$type);
	}

	function getMscInfo($msc_id,$currency)
	{
		$view = $this->getInstance('View');
		return $view->getMscInfo($msc_id,$currency);
	}

	function joinMsc($msc_id,$user_id)
	{

	}
	
	function processCCForm($orderInfo,$credit_info,$method = 'authorize', $params= array())
	{
		$order = $this->getInstance('Order');
		
		switch($method)
		{
			case('authorize'):
			    if ($orderInfo->payment_mode == 'm')
				{
					$result = $order->AuthorizeAIMPay($orderInfo,$credit_info, $params );
					
					return $result;
				}
				elseif ($orderInfo->payment_mode == 'a')
				{
					return $order->AuthorizeARBCreateProfile($orderInfo,$credit_info, $params );
				}
			break;
			case('paypal_cc'):
				return $order->PaypalAPICCPay($orderInfo,$credit_info, $params );
			break;
		}

	}
	
	function getOrderItem($where = array(),$type = 'array')
	{
		return $this->getInstance('Order')->getOrderItem($where,$type);
	}
	
	function getOrderItems($order_id,$type = 'array')
	{
		return $this->getInstance('Order')->getOrderItems($order_id,$type);
	}
*/
}