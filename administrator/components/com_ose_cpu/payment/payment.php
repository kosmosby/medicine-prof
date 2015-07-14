<?php
defined('_JEXEC') or die(";)");

class osePayment extends oseInit
{

	protected $task = array();
	protected $prefix = 'osePayment';
	protected $path = null;
	protected $instance = array();

	function __construct()
	{
		$this->path = dirname(__FILE__);
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
		$this->registerTask('getOrderItem','getOrder');
	}

	protected function registerInstance($task,$instanceName)
	{
		$this->instance[$task] = $instanceName;
	}

	protected function setRegisteredInstances()
	{
		// NULL
	}

	function __call($name,$args)
	{
		if(isset($this->task[$name]))
		{
			return call_user_func_array(array($this,$this->task[$name]),$args);
		}
		else
		{
			oseExit($name. 'Error');
		}
	}

	function __toString()
	{
		return get_class($this);
	}

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
	public function instance($type,$params = array())
	{
		if(strtolower($type) == 'order')
		{
			$orderType = $params['type'];
			require_once(dirname(__FILE__).DS.strtoupper($orderType).DS.'osePaymentOrder.php');
			require_once($this->path.DS.strtoupper($orderType).DS.'osePaymentOrder.php');
			$class = "{$this->prefix}Order{$orderType}";
			$instance = new $class();
			return $instance;
		}
		elseif(strtolower($type) == 'cart')
		{
			$orderType = $params['type'];
			require_once(dirname(__FILE__).DS.strtoupper($orderType).DS.'osePaymentCart.php');
			require_once($this->path.DS.strtoupper($orderType).DS.'osePaymentCart.php');
			$class = "{$this->prefix}Cart{$orderType}";
			$instance = new $class();
			return $instance;
		}
		else
		{
			return null;
		}
	}
	public static function getInstance($type,$params = array())
	{
		static $instance;
		$className = "osePayment{$type}";

		if(strtolower($type) == 'price')
		{
			$primaryCurrency = self::getCurrencyInfo();
			$instance = new osePaymentPrice($primaryCurrency->value,oseJson::decode($primaryCurrency->default));
			return $instance;
		}

		if(!$instance instanceof $className)
		{
			$instance = new $className();
		}

		return $instance;
	}

	/*
	 * discount, type: 1.renewal, 2.coupon discount
	 * 								2.coupon discount type: 1.rate, 2.number.
	 * tax rate

	function pricingFeeDiscount($price,$dicount, $rate, $type = 'coupon')
	{
		return $price * $rate;
	}

	function pricingFeeTax($price,$rate = 0)
	{
		return $price * $rate;
	}

	function pricingDonation($price, $type, $value = 0)
	{
		return $value;
	}

	function pricingAmount($price, $round = '2')
	{
		return round($price,$round);
	}
	 */
	function generateOrder($msc_id, $user_id,$params)
	{
		$order = new osePaymentOrder();

		return $order->generateOrder($msc_id, $user_id,$params);
	}

	function generateOrderNumber($user_id)
	{
		$order_number = osePaymentOrder::generateOrderNumber($user_id);

		return $order_number;
	}

	function generateOrderParams($msc_id,$price,$payment_mode,$msc_option)
	{
		$order = new osePaymentOrder();

		return $order->generateOrderParams($msc_id,$price,$payment_mode,$msc_option);
	}

	function updateOrder($order_id,$status,$params = array())
	{
		$order = new osePaymentOrder();

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
		$order = $this->getInstance('Order');
		$pConfig= oseMscConfig :: getConfig('payment', 'obj');
		if ($pConfig->paypal_mode=='paypal_paypalpro')
		{
			$html = $order->PaypalAPIPostForm($orderInfo,$params);
		}
		else
		{
			$html = $order->PaypalExpPostForm($orderInfo,$params);
		}
		return $html;
	}

	function PaypalAPIGetOrderDetails($token)
	{
		$order = new osePaymentOrder();
		return $order->PaypalAPIGetOrderDetails($token);
	}
	function PaypalAPIPay($order_id, $token)
	{
		$order = new osePaymentOrder();
		return $order->PaypalAPIPay($order_id, $token);
		
	}
	function PaypalAPICreateProfile($orderID, $token)
	{
		$order = new osePaymentOrder();
		return $order->PaypalAPICreateProfile($orderID, $token);
	}
	function getGCOForm($orderInfo)
	{
		return osePaymentOrder::get_gcoform($orderInfo);
	}

	function get2COForm($orderInfo)
	{
		return osePaymentOrder::get_2coform($orderInfo);
	}

	function getCCForm($orderInfo)
	{
		return osePaymentOrder::get_ccform($orderInfo);
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
					return $order->AuthorizeAIMPay($orderInfo,$credit_info, $params );
				}
				elseif ($orderInfo->payment_mode == 'a')
				{
					return $order->AuthorizeARBCreateProfile($orderInfo,$credit_info, $params );
				}
			break;
			case('beanstream'):
			    if ($orderInfo->payment_mode == 'm')
				{
					return $order->BeanStreamOneOffPay($orderInfo,$credit_info, false,$params );
				}
				elseif ($orderInfo->payment_mode == 'a')
				{
					return $order->BeanStreamCreateProfile($orderInfo,$credit_info,$params );
				}
			break;
			case('eway'):
			    if ($orderInfo->payment_mode == 'm')
				{
					return $order->eWayOneoffPay($orderInfo,$credit_info, $params );
				}
				elseif ($orderInfo->payment_mode == 'a')
				{
					return $order->eWayCreateProfile($orderInfo,$credit_info, $params );
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
	
	function getGateWay($orderInfo)
	{
		$gw = $this->getInstance('GateWay');
		
		return $gw->getGateWay($orderInfo);
	}
}