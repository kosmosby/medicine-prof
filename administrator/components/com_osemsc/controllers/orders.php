<?php
/**
  * @version     5.0 +
  * @package        Open Source Membership Control - com_osemsc
  * @subpackage    Open Source Access Control - com_osemsc
  * @author        Open Source Excellence (R) {@link  http://www.opensource-excellence.com}
  * @author        Created on 15-Nov-2010
  * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
  *
  *
  *  This program is free software: you can redistribute it and/or modify
  *  it under the terms of the GNU General Public License as published by
  *  the Free Software Foundation, either version 3 of the License, or
  *  (at your option) any later version.
  *
  *  This program is distributed in the hope that it will be useful,
  *  but WITHOUT ANY WARRANTY; without even the implied warranty of
  *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  *  GNU General Public License for more details.
  *
  *  You should have received a copy of the GNU General Public License
  *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
  *  @Copyright Copyright (C) 2010- Open Source Excellence (R)
*/
defined('_JEXEC') or die("Direct Access Not Allowed");


class oseMscControllerOrders extends oseMscController
{
    public function __construct()
    {
        parent::__construct();
    } //function


	public function getOrders()
	{
		$model = $this->getModel('orders');

		$start = JRequest::getInt('start',0);
		$limit = JRequest::getInt('limit',0);
		$type = JRequest::getInt('type',0);

		$result = $model->getOrders($type,$start,$limit);

		$result = oseJson::encode($result);

		oseExit($result);
	}

	public function confirmOrder()
	{
		$model = $this->getModel('orders');

		$order_id = JRequest::getInt('order_id',0);

		$result = $model->confirmOrder($order_id);

		$result = oseJson::encode($result);

		oseExit($result);
	}

	public function pendingOrder()
	{
		$model = $this->getModel('orders');

		$order_id = JRequest::getInt('order_id',0);

		$result = $model->pendingOrder($order_id);

		$result = oseJson::encode($result);

		oseExit($result);
	}
	
	public function Truncate()
	{
		$model = $this->getModel('orders');

		$result = $model->Truncate();

		$result = oseJson::encode($result);

		oseExit($result);
	}
	
	function getOrderMemInfo()
	{
		$order_id = JRequest::getInt('order_id',0);
		$db= oseDB :: instance();
		$where= array();
		$where[]= "`order_id` = ".$db->quote($order_id);
		$payment= oseRegistry :: call('payment');
		$orderInfo = $payment->getOrder($where, 'obj');
		$orderInfoParams= oseJson :: decode($orderInfo->params);
		
		$orderItems = $payment->getOrderItems($order_id,'obj');
		$orderItem = $orderItems[0];
		$orderItemParams = oseJson :: decode($orderItem->params);
		
		$msc_id = $orderItemParams->msc_id;
		$msc_option = $orderItemParams->msc_option;
		$msc= oseRegistry :: call('msc');
		$mscInfo = $msc->getInfo($msc_id,'obj');
		$paymentInfos = $msc->getExtInfo($msc_id,'payment');
		$paymentInfo = $paymentInfos[$msc_option];
		if(!empty($paymentInfo['optionname']))
		{
			$optionname = $paymentInfo['optionname'];
		}else{
			$optionname = $paymentInfo['recurrence_num'].' '.$paymentInfo['recurrence_unit'].' membership';
		}
           
		$paymentOrder = $payment->getInstance('Order');
		$billinginfo = $paymentOrder->getBillingInfo($orderInfo->user_id);
		
		$query = " SELECT f.name,v.value FROM `#__osemsc_fields` AS f "
				." INNER JOIN `#__osemsc_fields_values` AS v"
				." ON f.`id` = v.`field_id`"
				." WHERE `member_id` = ".$orderInfo->user_id;
		$db->setQuery($query);
		$profiles = $db->loadObjectList();	
		
		echo "<div style=\"color: #238db4; font-weight: bold; font-size:12px;\">".JText::_('MEMBERSHIP_INFORMATION').":</div>";
		echo "<div>".JText::_('MEMBERSHIP')." : ".$mscInfo->title."</div>";
		echo "<div>".JText::_('MEMBERSHIP_OPTION')." : ".$optionname."</div>";
		echo "</br>";
		
		echo "<div style=\"color: #238db4; font-weight: bold; font-size:12px;\">".JText::_('BILLING_INFORMATION').":</div>";
		echo "<div>".JText::_('FIRST_NAME')." : ".$billinginfo->firstname."</div>";
		echo "<div>".JText::_('LAST_NAME')." : ".$billinginfo->lastname."</div>";
		echo "<div>".JText::_('EMAIL')." : ".$billinginfo->email."</div>";
		echo "<div>".JText::_('ADDRESS')." : ".$billinginfo->addr1." ".$billinginfo->addr2."</div>";
		echo "<div>".JText::_('CITY')." : ".$billinginfo->city."</div>";
		echo "<div>".JText::_('STATE')." : ".$billinginfo->state."</div>";
		echo "<div>".JText::_('COUNTRY')." : ".$billinginfo->country."</div>";
		echo "<div>".JText::_('ZIP_POSTAL_CODE')." : ".$billinginfo->postcode."</div>";
		echo "<div>".JText::_('PHONE')." : ".$billinginfo->telephone."</div>";
		echo "</br>";
		
		echo "<div style=\"color: #238db4; font-weight: bold; font-size:12px;\">".JText::_('ADDITIONAL_INFORMATION').":</div>";
		foreach($profiles as $profile)
		{
			echo "<div>".$profile->name." : ".$profile->value."</div>";
		}
		oseExit();
	}
	
	function getAllOptions()
	{
		$model = $this->getModel('orders');

		$result = $model->getAllOptions();

    	$result = oseJson::encode($result);
		oseExit($result);
	}
	
	function getUsers()
	{
		$model = $this->getModel('orders');

		$result = $model->getUsers();

		$result = oseJson::encode($result);

		oseExit($result);
	}
	
	function createOrder()
	{
		$model = $this->getModel('orders');

		$result = $model->createOrder();

		$result = oseJson::encode($result);

		oseExit($result);
	}
}


?>