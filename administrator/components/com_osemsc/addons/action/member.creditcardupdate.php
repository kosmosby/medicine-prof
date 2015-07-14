<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionMemberCreditcardupdate
{
	public function getOrders()
	{

		$member_id = JRequest::getInt('member_id',0);	
		$start = JRequest::getInt('start',0);
		$limit = JRequest::getInt('limit',0);
		$type = JRequest::getInt('type',0);
		
		$db = oseDB::instance();
		$where = array();
		
		
		$filterStatus = JRequest::getString('filter_status',null);


		$where[] = "o.entry_type IN ('msc','msc_list')";
		$where[] = "o.payment_from != 'system_admin'";
		$where[] = "o.`user_id` = '{$member_id}'";
		$where[] = "o.`payment_method` IN ('beanstream','authorize','paypal_cc')";
		$where[] = "o.`payment_mode` = 'a' ";
		$where[] = "o.`order_status`='confirmed'";
		$where = oseDB::implodeWhere($where);
		
		$query = " SELECT COUNT(*) "
				." FROM `#__osemsc_order` AS o "
				." INNER JOIN `#__users` AS u ON u.id = o.user_id"
				. $where
				//." ORDER BY o.create_date DESC"
				//." LIMIT {$start},{$limit} "
				;
		$db->setQuery($query);
		//oseExit($db->getQuery());
		$total = $db->loadResult();
		
		$query = " SELECT CONCAT('Order:',o.order_id) AS title, u.username,u.name, o.* "
				." FROM `#__osemsc_order` AS o "
				." INNER JOIN `#__users` AS u ON u.id = o.user_id"
				. $where
				." ORDER BY o.create_date DESC"
				//." LIMIT {$start},{$limit} "
				;
		$db->setQuery($query,$start,$limit);
		//oseExit($db->getQuery());
		$items = oseDB::loadList();
		$return = array();
		$i=0;
		foreach ($items as $item)
		{
			$item['mscTitle'] = self::getMSCTitle($item['order_id']);
			$item['name'] = $item['user_id'].' - '. $item['name'];
			$item['title'] = $item['title'].' - '. $item['mscTitle'];
			$return[$i] = $item;
			$i++;
		}
		
		$result = array();
		$result['total'] = $total;//$this->getTotal();
		$result['results'] = $return;

		$result = oseJson::encode($result);

		oseExit($result);
	}

	private function getMSCTitle($orderID)
	{
		$db = oseDB::instance();
		$query = " SELECT acl.title FROM `#__osemsc_acl` AS acl, `#__osemsc_order_item` AS oitem " .
				 " WHERE oitem.order_id = ". (int)$orderID.
				 " AND acl.id = oitem.entry_id";
		$db->setQuery($query);
		$titles = $db->loadObjectlist();
		$return = array();
		foreach ($titles as $title)
		{
			$return[]= $title->title;
		}
		$return = implode("<br />", $return);
		return $return;
	}
	
	function update()
	{
		$db = oseDB::instance();
		$member_id = JRequest::getInt('member_id',0);	
		$query = " SELECT * FROM `#__osemsc_order`"
				." WHERE `user_id` = '{$member_id}' AND `payment_method` IN ('beanstream','authorize','paypal_cc')"
				." AND `payment_mode` = 'a' AND `order_status`='confirmed'"
				;
		$db->setQuery($query);
		$list = oseDB::loadList();

		if(count($list) < 1)
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('ERROR');
			
			$result = oseJson::encode($result);
			//oseExit($result);
		}
		
		$creditInfo= array();
		$post = JRequest::get('post');
		$order_id = JRequest::getInt('order_id');
		$creditInfo['creditcard_type']= $post['creditcard_type'];
		$creditInfo['creditcard_name']= $post['creditcard_name'];
		$creditInfo['creditcard_owner'] = $creditInfo['creditcard_name'];
		$creditInfo['creditcard_number']= JRequest::getCmd('creditcard_number');
		$creditInfo['creditcard_year'] = $post['creditcard_year'];
		$creditInfo['creditcard_month'] = $post['creditcard_month'];
		$creditInfo['creditcard_expirationdate']= $post['creditcard_year'].'-'.$post['creditcard_month'];
		$creditInfo['creditcard_cvv']= $post['creditcard_cvv'];
		
		$payment= oseRegistry :: call('payment');
		$pOrder = new osePaymentOrder();
		$orderInfo = $pOrder->getOrder( array('`order_id` = '.$order_id),'obj' );
		
		switch($orderInfo->payment_method)
		{
			case('beanstream'):
				$updated = $pOrder->BeanStreamModify($orderInfo, $creditInfo);
			break;
			
			case('authorize'):
				$updated = $pOrder->AuthorizeARBUpdateProfile($orderInfo, $creditInfo);
				$result = array();
				if($updated['success'])
				{
					$result['success'] = true;
					$result['title'] = JText::_('DONE');
					$result['content'] = JText::_('UPDATED');
				}
				else
				{
					$result['success'] = false;
					$result['title'] = JText::_('ERROR');
					$result['content'] = $updated['content'];
				}
					
				$result = oseJson::encode($result);
				oseExit($result);
			break;
			
			case('paypal_cc'):
				$updated = $pOrder->PaypalAPIUpdateCreditCard($orderInfo, $creditInfo);
				$result = array();
				if($updated['success'])
				{
					$result['success'] = true;
					$result['title'] = JText::_('DONE');
					$result['content'] = JText::_('UPDATED');
				}
				else
				{
					$result['success'] = false;
					$result['title'] = JText::_('ERROR');
					$result['content'] = $updated['content'];
				}
					
				$result = oseJson::encode($result);
				oseExit($result);
			break;
			default:
			
			break;
		}
		
		$result = array();
		if($updated)
		{
			$result['success'] = true;
			$result['title'] = JText::_('DONE');
					$result['content'] = JText::_('UPDATED');
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('ERROR');
		}
			
		$result = oseJson::encode($result);
		oseExit($result);
	}
}
?>