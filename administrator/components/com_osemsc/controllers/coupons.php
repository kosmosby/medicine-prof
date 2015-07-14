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
defined( '_JEXEC' ) or die( ';)' );

class osemscControllerCoupons extends osemscController
{
	function __construct()
	{
		parent::__construct();
	}

	function getMscList()
	{
		$db = oseDB::instance();

		$query = " SELECT * FROM `#__osemsc_acl`";

		$db->setQuery($query);
		$objs = oseDB::loadList('obj');

		$result = array();

		$result['total'] = count($objs);
		$result['results'] = $objs;

		$result = oseJson::encode($result);
		oseExit($result);
	}

	function getCurrencyList()
	{
		$db = oseDB::instance();

		$query = " SELECT * FROM `#__osemsc_configuration` WHERE `key` = 'primary_currency'";

		$db->setQuery($query);
		$objs = oseDB::loadList('obj');
		$currency = array();
		$currency[0]['title'] = $objs[0]->value;
		$currency[0]['value'] = $objs[0]->value;
		$others = oseJSON::decode ($objs[0]->default);
		$i = 1;
		if (!empty($others))
		{
			foreach ($others as $key => $value)
			{
				$currency[$i]['title'] = $key;
				$currency[$i]['value'] = $key;
				$i++;
			}
		}
		$result = array();
		$result['results'] = $currency;
		$result['total'] = count($result['results']);
		$result = oseJson::encode($result);
		oseExit($result);
	}
	function save()
	{
		$model = $this->getModel('coupons');

		$id = JRequest::getInt('id',0);
		$post = JRequest::get('post');

		$result = array();
		$result['success'] = true;

		if(empty($id))
		{
			$title = $post['title'];
			if(empty($title))
			{
				$result['success'] = false;
				$result['title'] = JText::_('ERROR');
				$result['content'] = JText::_('TITLE_CAN_NOT_BE_EMPTY');
			}
			else
			{
				$updated = $model->add($title);

				if($updated)
				{
					$result['success'] = true;
					$result['title'] = JText::_('DONE');
					$result['content'] = JText::_('ADDED_SUCCESSFULLY');
				}
				else
				{
					$result['success'] = false;
					$result['title'] = JText::_('ERROR');
					$result['content'] = JText::_('FAILING_ADDING');
				}
			}
		}
		else
		{
			$params = array();
			$params['amount'] = oseObject::getValue($post,'amount',0);
			$params['title'] = $post['title'];
			$params['code'] = $post['code'];
			$params['type'] = oseObject::getValue($post,'type',null);;
			$params['amount_infinity'] = oseObject::getValue($post,'amount_infinity',0);
			$params['discount'] = oseObject::getValue($post,'discount',0);
			$params['discount_type'] = $post['discount_type'];
			$params['params'] = oseJson::encode(array(
				'range'=>$post['range']
				,'range2'=>oseObject::getValue($post,'range2','first')
				,'amount_left'=>oseObject::getValue($post,'amount_left',0)
				,'msc_ids'=>oseObject::getValue($post,'msc_ids','all')
				,'currencies'=>oseObject::getValue($post,'currencies','all')
			));
			$updated = $model->update($id,$params);

			if($updated)
			{
				$result['success'] = true;
				$result['title'] = JText::_('DONE');
				$result['content'] = JText::_('ADDED_SUCCESSFULLY');
			}
			else
			{
				$result['success'] = false;
				$result['title'] = JText::_('ERROR');
				$result['content'] = JText::_('FAILING_ADDING');
			}
		}

		$result = oseJSON::encode($result);

		oseExit($result);
	}

	function remove()
	{
		$result = array();

		$id = JRequest::getInt('id',0);

		$model = $this->getModel('coupons');
		$updated = $model->remove($id);

		if($updated)
		{
			$result['success'] = true;
			$result['title'] = JText::_('DONE');
			$result['content'] = JText::_('REMOVE_SUCCESSFULLY');
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('FAIL_TO_REMOVE');
		}


		$result = oseJSON::encode($result);

		oseExit($result);
	}

	function getCoupons()
	{
    	$db = oseDB::instance();

    	$query = " SELECT * FROM `#__osemsc_coupon`";

    	$db->setQuery($query);

  		$items = $db->loadObjectlist();

		$result = array();

		$result['total'] = count($items);
		$result['results'] = $items;

		$result = oseJSON::encode($result);

		oseExit($result);
	}

	function getCouponsParams()
	{
		$model = $this->getModel('coupons');
		$id = JRequest::getInt('id',0);

		$item = $model->getInfo($id);

		$result = array();
		$result['total'] = empty($item)?0:1;
		$result['result'] = $item;
		$result['success'] = true;
		$result = oseJSON::encode($result);
		oseExit($result);
	}


	function loadParams()
	{
		$model = $this->getModel('licenses');

		$id = JRequest::getCmd('id',null);
		$type = JRequest::getCmd('type',null);

		$updated = $model->loadParams($type);

		echo '<script type="text/javascript">'."\r\n";
		require_once($updated);
		echo "\r\n".'</script>';
		oseExit();

	}

	function getCouponUsers()
	{
		$model = $this->getModel('coupons');
		$id = JRequest::getInt('id',0);

		$items = $model->getUsersTable($id);

		//$result = array();
		//$result['result'] = $items;
		//$result = oseJSON::encode($result);
		oseExit($items);
	}

	function getCouponHistory()
	{
    	$db = oseDB::instance();
		$id = JRequest::getInt('id',0);
		$start= JRequest :: getInt('start', 0);
		$limit= JRequest :: getInt('limit', 25);
		$paid = JRequest::getInt('paid',-1);
		
		$where = array();
		$where[] = ' c.`coupon_id` = '.$db->Quote($id);
		if($paid>=0)
		{
			$where[] = ' c.`paid` = '.$db->Quote($paid);
		}
		$where = oseDB::implodeWhere($where);
		
		$query = " SELECT count(*) FROM `#__osemsc_coupon_user` AS c" 
    			//." INNER JOIN `#__users` AS u"
    			//." ON c.`user_id` = u.`id`"
    			.$where;

    	$db->setQuery($query);

  		$total = $db->loadResult();
  		
    	$query = " SELECT c.* FROM `#__osemsc_coupon_user` AS c" 
    			//." INNER JOIN `#__users` AS u"
    			//." ON c.`user_id` = u.`id`"
    			.$where;

    	$db->setQuery($query, $start, $limit);

  		$items = $db->loadObjectlist();
		foreach($items as $item)
		{
			$user_id = $item->user_id;
			$query = " SELECT username FROM `#__users`"
					." WHERE `id` = '{$user_id}'"
					;
			$db->setQuery($query);
			$username = $db->loadResult();
			$item->username = empty($username)?'Guest':$username;
		}
		$result = array();

		$result['total'] = $total;
		$result['results'] = $items;

		$result = oseJSON::encode($result);

		oseExit($result);
	}
}