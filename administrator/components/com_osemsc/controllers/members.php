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


class oseMscControllerMembers extends oseMscController
{
    public function __construct()
    {
        parent::__construct();
    } //function

	// new parent_node, old parent_node, active node.
	function getList()
	{
		$model = $this->getModel('members');

		$msc_id = JRequest::getVar('msc_id',array(),'post');
		if(empty($msc_id[0]))
		{
			$db = oseDB::instance();
			$query = "SELECT * FROM `#__osemsc_acl`";
			$db->setQuery($query);
			$objs = oseDB::loadList('obj');
			$msc_id = array();
			foreach($objs as $obj)
			{
				$msc_id[] = $obj->id;
			}
		}
		$result = $model->getList($msc_id);


		if($result['total'] < 1)
		{
			$result['total'] = 0;
		}

		$result = oseJSON::encode($result);

		echo $result;exit;
	}

	function getItem()
	{
		oseExit('');
	}

	function getUsers()
	{
		$model = $this->getModel('members');

		$result = $model->getUsers();

		if($result['total'] < 1)
		{
			$result['total'] = 0;
		}

		$result = oseJSON::encode($result);
		oseExit($result);
	}

	function joinMsc()
	{
		$model = $this->getModel('members');

		$member_ids = JRequest::getVar('member_ids',array(),'post','array');

		$msc_id = JRequest::getInt('msc_id',0);
		$msc_option = JRequest::getCmd('msc_option',0);
		$result = array();

		if(empty($msc_id))
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');;
			$result['content'] = JText::_('ERROR');;
		}
		else
		{
			$msc = oseRegistry::call('msc');
			$node = $msc->getInfo($msc_id,'obj');
			if(!$node->leaf)
			{
				$result['success'] = false;
				$result['title'] = JText::_('ERROR');
				$result['content'] = JText::_('PLEASE_SELECT_A_LEAF_MEMBERSHIP');
			}
			else
			{
				$cart = oseMscPublic::getCart();
				$cart->addItem(null,null,array('entry_id'=>$msc_id,'entry_type'=>'msc','msc_option'=>$msc_option));
				$cart->refreshCartItems();
				$paymentInfo = $cart->output();
				if(!empty($msc_option))
				{
					$payment = oseRegistry::call('msc')->getExtInfo($msc_id,'payment','array');
					$payment = $payment[$msc_option];
					$price = oseObject::getValue($payment,'a3');
					$paymentInfo['payment_price'] = $price;
					$paymentInfo = oseObject::setParams($paymentInfo,array(
					'a3'=>$price,'total'=>$price,'next_total'=>0,'subtotal'=>$price
					));
				}else{
					$paymentInfo['payment_price'] = '0.00';
					$paymentInfo = oseObject::setParams($paymentInfo,array(
						'a3'=>0,'total'=>0,'next_total'=>0
					));
				}
				foreach($member_ids as $member_id)
				{
					
					$updated = $model->joinMsc($member_id,$paymentInfo);
					
					if(!$updated['success'])
					{
						//break;
					}
				}
				
				$result = $updated;
				if($updated['success'])
				{
					$result = $updated;
					$result['success'] = true;
					$result['title'] = JText::_('DONE');
					$result['content'] = JText::_('ADDED_SUCCESSFULLY');
				}
				else
				{
					$result = $updated;
					$result['success'] = false;
					$result['title'] = JText::_('ERROR');;
					$result['content'] = JText::_("MEMBER_ID")." :{$member_id} ".JText::_("FAILED_JOINING");
				}
			}
		}

		$result = oseJson::encode($result);

		oseExit($result);
	}
	
	function importUser2Member()
	{
		$result = array();
		$model = $this->getModel('members');

		$limit = 500;
		$iterate_number = JRequest::getInt('iterate_number',0);
		
		$msc_id = JRequest::getInt('msc_id',0);
		$msc_option = JRequest::getCmd('msc_option',0);
		JRequest::setVar('start',0);
		JRequest::setVar('limit',$limit);
		
		$members = $model->getUsers();
	
		$total = $members['total'];
		$iterate_total = ceil($total/$limit);
		$result['number'] = $total;
		$result['end'] = ($iterate_total == 1)? true: false;
		
		if(empty($msc_id))
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');;
			$result['content'] = JText::_('ERROR');;
		}
		else
		{
			$msc = oseRegistry::call('msc');
			$node = $msc->getInfo($msc_id,'obj');
			if(!$node->leaf)
			{
				$result['success'] = false;
				$result['title'] = JText::_('ERROR');;
				$result['content'] = JText::_('PLEASE_SELECT_A_LEAF_MEMBERSHIP');
			}
			else
			{
				$cart = oseMscPublic::getCart();
				$cart->addItem(null,null,array('entry_id'=>$msc_id,'entry_type'=>'msc','msc_option'=>$msc_option));
				$cart->refreshCartItems();
				$paymentInfo = $cart->output();
				
				foreach($members['results'] as $member_id)
				{
					$updated = $model->joinMsc($member_id['id'],$paymentInfo);
					
					if(!$updated['success'])
					{
						break;
					}
				}
				
				//$result = $updated;
				if($updated['success'])
				{
					$result = array_merge($result,$updated);
					$result['iterate_number'] = $iterate_number+1;
					$result['success'] = true;
					$result['title'] = JText::_('DONE');
					$result['content'] = JText::_('ADDED_SUCCESSFULLY');
				}
				else
				{
					$result = $updated;
					$result['success'] = false;
					$result['title'] = JText::_('ERROR');;
					$result['content'] = JText::_("MEMBER_ID")." :{$member_id} ".JText::_("FAILED_JOINING");
				}
			}
		}

		$result = oseJson::encode($result);

		oseExit($result);
	}

	function cancelMsc()
	{
		$model = $this->getModel('members');

		$member_ids = JRequest::getVar('member_ids',array(),'post','array');

		$msc_id = JRequest::getInt('msc_id',0);

		if(!empty($msc_id))
		{
			$updated = $model->cancelMsc($member_ids,$msc_id);
		}
		else
		{
			$updated = array('success'=>false);
		}

		$result = array();

		if(oseObject::getValue($updated,'success',false))
		{
			$result['success'] = true;
			$result['title'] = JText::_('DONE');
			$result['content'] = JText::_('REMOVE_SUCCESSFULLY');
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');;
			$result['content'] = JText::_('FAIL_REMOVING');
		}

		$result = oseJson::encode($result);

		oseExit($result);
	}

	function removeMember()
	{
		$model = $this->getModel('members');

		$member_ids = JRequest::getVar('member_ids',array(),'post','array');

		$msc_id = JRequest::getInt('msc_id',0);

		if(!empty($msc_id))
		{
			$updated = $model->cancelMsc($member_ids,$msc_id);
		}
		else
		{
			$updated = array('success'=>false);
		}
		if(oseObject::getValue($updated,'success',false))
		{
			foreach($member_ids as $member_id)
			{
				$db = oseDB::instance();
				$query = "DELETE FROM `#__osemsc_member` WHERE `member_id` = '{$member_id}' AND `msc_id` = '{$msc_id}' AND `status` = 0";
				$db->setQuery($query);
				$db->query();
			}
		}	
		$result = array();

		if(oseObject::getValue($updated,'success',false))
		{
			$result['success'] = true;
			$result['title'] = JText::_('DONE');
			$result['content'] = JText::_('REMOVE_SUCCESSFULLY');
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');;
			$result['content'] = JText::_('FAIL_REMOVING');
		}

		$result = oseJson::encode($result);

		oseExit($result);
	}
	
	function getAddon()
	{
		$result = array();

		$addon_name = JRequest::getCmd('addon_name',null);
		$type = JRequest::getCmd('addon_type',null);

		echo '<script type="text/javascript">'."\r\n";
		require_once(JPATH_SITE.DS.oseMscMethods::getAddonPath($addon_name.'.js','member'));
		echo "\r\n".'</script>';
		oseExit();
	}

	public static function getOptions()
	{
		$msc_id = JRequest::getInt('msc_id',0);
		$msc = oseRegistry::call('msc');
		$node = $msc->getInfo($msc_id,'obj');
		$paymentInfos = $msc->getExtInfo($msc_id,'payment');
		
		$cart = oseMscPublic::getCart();
    	$osePaymentCurrency = $cart->get('currency');
		$option = oseMscPublic::generatePriceOption($node,$paymentInfos,$osePaymentCurrency);
		$options = array();
		$options = array_merge($options,$option);
		
/*
		$session =& JFactory::getSession();
    	$osePaymentCurrency = $session->get('osePaymentCurrency',oseRegistry::call('msc')->getConfig('currency','obj')->primary_currency);
    	$oseMscPayment = $session->get('oseMscPayment',array('msc_id'=>0,'msc_option'=>0));

		$option = array();

		if(!empty($oseMscPayment['msc_option']))
		{
			$paymentInfo = oseObject::getValue($paymentInfos,$oseMscPayment['msc_option']);
			$node = oseRegistry::call('payment')->getInstance('View')->getPriceStandard($node,$paymentInfo,$osePaymentCurrency);
			$optionPrice = oseObject::getValue($node,'standard_price').' for every '.oseObject::getValue($node,'standard_recurrence') ;

			if(oseObject::getValue($paymentInfo,'has_trial'))
			{
				$node = oseRegistry::call('payment')->getInstance('View')->getPriceTrial($node,$paymentInfo,$osePaymentCurrency);
				$optionPrice .= ' ('.oseObject::getValue($node,'trial_price').' in first '.oseObject::getValue($node,'trial_recurrence').')';
			}

			$option[] = array('id'=>oseObject::getValue($paymentInfo,'id'),'text'=>$optionPrice);

		}
		else
		{
			foreach($paymentInfos as  $paymentInfo)
			{
				$node = oseRegistry::call('payment')->getInstance('View')->getPriceStandard($node,$paymentInfo,$osePaymentCurrency);
				$optionPrice = oseObject::getValue($node,'standard_price').' for every '.oseObject::getValue($node,'standard_recurrence') ;

				if(oseObject::getValue($paymentInfo,'has_trial'))
				{
					$node = oseRegistry::call('payment')->getInstance('View')->getPriceTrial($node,$paymentInfo,$osePaymentCurrency);
					$optionPrice .= ' ('.oseObject::getValue($node,'trial_price').' in first '.oseObject::getValue($node,'trial_recurrence').')';
				}

				$option[] = array('id'=>oseObject::getValue($paymentInfo,'id'),'text'=>$optionPrice);
			}

		}
*/
		$combo = array();
    	$combo['total'] = count($options);
    	$combo['results'] = $options;

    	$combo = oseJson::encode($combo);
		oseExit($combo);
	}
}


?>