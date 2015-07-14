<?php
defined('_JEXEC') or die(";)");

class oseUser2Msc extends oseUser2Instance
{
	protected $active_membership = array();
	protected $expired_membership = array();
	protected $suspend_membership = array();
	
	protected $_table = '#__osemsc_member';
	
	function __construct($user_id)
	{
		parent::__construct($user_id);
		// get License
		$this->getMap();
	}

	function getMap()
	{
		// get License
		$db = oseDB::instance();
		
		// active
		$where = array();
		$where[] = "`member_id` = '{$this->user_id}'";
		$where[] = "`status` = 1";
		$where = oseDB::implodeWhere($where);
		$query = " SELECT *"
				." FROM `{$this->_table}`"
				. $where
				." ORDER BY `id` ASC"
				;
		$db->setQuery($query);
		$items = oseDB::loadList('obj','msc_id');
		$this->set('active_membership',$items);
		
		//expired
		$where = array();
		$where[] = "`member_id` = '{$this->user_id}'";
		$where[] = "`status` = 2";
		$where = oseDB::implodeWhere($where);
		$query = " SELECT *"
				." FROM `{$this->_table}`"
				. $where
				." ORDER BY `id` ASC"
				;
		$db->setQuery($query);
		$items = oseDB::loadList('obj','msc_id');
		$this->set('expired_membership',$items);
		
		//suspend
		$where = array();
		$where[] = "`member_id` = '{$this->user_id}'";
		$where[] = "`status` = 3";
		$where = oseDB::implodeWhere($where);
		$query = " SELECT *"
		." FROM `{$this->_table}`"
		. $where
		." ORDER BY `id` ASC"
						;
				$db->setQuery($query);
		$items = oseDB::loadList('obj','msc_id');
		$this->set('suspend_membership',$items);
	}
	
	function join($id,$amount =1 ,$entry_type = 'msc',$entry_option = null)
	{
		$result = array();
		
		$db = oseDB::instance();
		
		// need to check in order generating
		/*
		$mscInfo = oseCall('msc')->instance('plan',array('id'=>$id));
		$query = " SELECT COUNT(*) FROM `#__osemsc_member`"
				." WHERE `msc_id`='{$id}' AND `status`=1"
				;
		$db->setQuery($query);
		$total = $db->loadResult();
		if($total >= $mscInfo->restricted_number)
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('PLAN_JOIN_FAILED');
			return $result;
		}
		*/
		$query = " SELECT * FROM `{$this->_table}`"
				." WHERE `msc_id`='{$id}' AND `member_id`='{$this->user_id}'"
				;
		$db->setQuery($query);
		$item = oseDB::loadItem('obj');
		
		$vals = array();
		$vals['msc_id'] = $id;
		$vals['member_id'] = $this->user_id;
		$vals['status'] = 1;
		$vals['eternal'] = 0;
		
		if(empty($item))
		{
			//$vals['start_date'] = oseHtml2::getDateTime();
			$id = $updated = oseDB::insert($this->_table,$vals);
			
			// join addon
			if($updated)
			{
				$params = array();
				$params['user_id']= $this->user_id;
				$params['mm_id']= $id;
				$params['order_id']= $this->get('order_id');
				$params['order_item_id']= $this->get('order_item_id');
			
				$addon = oseCall('msc')->instance('addon',$params);
				$jResult = $addon->runActionGroup('join','save',false);
					
				$updated = $jResult['success'];
					
				if(!$updated)
				{
					return $jResult;
				}
			}
		}
		else
		{
			$id = $vals['id'] = $item->id;
			$updated = oseDB::update($this->_table,'id',$vals);
			
			// renew/activate addon
			if($updated)
			{
				$params = array();
				$params['user_id']= $this->user_id;
				$params['mm_id']= $id;
				$params['order_id']= $this->get('order_id');
				$params['order_item_id']= $this->get('order_item_id');

				$addon = oseCall('msc')->instance('addon',$params);
				$jResult = $addon->runActionGroup('renew','renew',false);
					
				$updated = $jResult['success'];
				
				if(!$updated)
				{
					return $jResult;
				}
			}
		}
		
		if($updated)
		{
			// welcome email
			$msc = oseCall('msc')->instance('plan',array('id'=>$id));
			
			$mscExtmsc = $msc->getExt('msc');
			$wel_email = oseGetValue($mscExtmsc,'wel_email'); 
			if(empty($wel_email))
			{
				$config = oseConfig::load($entry_type,'email','obj');
				$wel_email = oseGetValue($config,'default_wel_email',0);
			}
			
			if( $wel_email )
			{
				$email = oseCall('email');
				$eObj = $email->getEmail( $wel_email );
				
				$content = array();
				$user = oseCall('user2')->instance($this->user_id);
				
				$content['user'] = (object)$user->outputPayment($entry_type);
				
				$mem = new stdClass();
				
				$content['member'] = $mem;
			
				$eObj->setEmailVariables($content);
				$eObj->transEmail();
				$eTemp = $eObj->output();
				$email->sendEmail($eTemp, $this->get('email'));
				if(oseGetValue($config,'sendWel2Admin',false))
				{
					//$group = oseConfig::getAdminGroup('contract');
					$group = oseGetValue($config,'email_admin_group');
					if(empty($group))
					{
						$group = oseConfig::getAdminGroup($entry_type);
					}
					
					$email->sendToGroup($eTemp, $group);
				}
			}
				
			$result['success'] = true;
			$result['title'] = JText::_('SUCCESS');
			$result['content'] = JText::_('PLAN_JOIN_SUCCEED');
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('PLAN_JOIN_FAILED');
		}
		return $result;
	}
	
	function activate($id,$entry_type = 'msc',$entry_option = null)
	{
		$db = oseDB::instance();

		$query = " SELECT * FROM `{$this->_table}`"
				." WHERE `msc_id`='{$id}' AND `member_id`='{$this->user_id}'"
				;
		$db->setQuery($query);
		$item = oseDB::loadItem('obj');

		$info = $this->find($id);
		
		if(empty($info) || !in_array($info->get('status') ,array(1,3)))
		{
			// no need to quit if empty
			$result = array();
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('NO_MEMBERSHIP_TO_ACTIVATE');
			return $result;
		}

		$info->set('status','active');
		$updated = $info->update();
		
		if($updated)
		{
			// cancel email
			$msc = oseCall('msc')->instance('plan',array('id'=>$id));
			$mscExtmsc = $msc->getExt('msc');
			$active_email = oseGetValue($mscExtmsc,'active_email'); 
			if(empty($active_email))
			{
				$config = oseConfig::load($entry_type,'email','obj');
				$active_email = oseGetValue($config,'default_active_email',0);
			}
				
			if( $active_email )
			{
				$email = oseCall('email');
				$eObj = $email->getEmail( $active_email );

				$content = array();
				$user = oseCall('user2')->instance($this->user_id);

				$content['user'] = (object)$user->outputPayment($entry_type);

				$mem = new stdClass();
				$content['member'] = $mem;

				$eObj->setEmailVariables($content);
				$eObj->transEmail();
				$eTemp = $eObj->output();
				$email->sendEmail($eTemp, $this->get('email'));
				//
				if(oseGetValue($config,'sendActivated2Admin',false))
				{
					//$group = oseConfig::getAdminGroup('contract');
					$group = explode(',',oseGetValue($config,'email_admin_group'));
					if(empty($group))
					{
						$group = oseConfig::getAdminGroup($entry_type);
					}

					$email->sendToGroup($eTemp, $group);
				}
			}

			$result['success'] = true;
			$result['title'] = JText::_('SUCCESS');
			$result['content'] = JText::_('MEMBERSHIP_JOIN_SUCCEED');
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('MEMBERSHIP_JOIN_FAILED');
		}
		return $result;
	}
	
	function suspend($id,$entry_type = 'msc',$entry_option = null)
	{
		$db = oseDB::instance();

		$query = " SELECT * FROM `{$this->_table}`"
				." WHERE `msc_id`='{$id}' AND `member_id`='{$this->user_id}'"
				;
		$db->setQuery($query);
		$item = oseDB::loadItem('obj');

		$info = $this->find($id);
		
		if(empty($info) || !in_array($info->get('status') ,array(1,3)))
		{
			// no need to quit if empty
			$result = array();
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('NO_MEMBERSHIP_TO_SUSPEND');
			return $result;
		}

		$info->set('status','freeze');
		$updated = $info->update();

		if($updated)
		{
			// cancel email
			$msc = oseCall('msc')->instance('plan',array('id'=>$id));
			$mscExtmsc = $msc->getExt('msc');
			$suspend_email = oseGetValue($mscExtmsc,'suspend_email'); 
			if(empty($suspend_email))
			{
				$config = oseConfig::load($entry_type,'email','obj');
				$suspend_email = oseGetValue($config,'default_suspend_email',0);
			}
				
			if( $suspend_email )
			{
				$email = oseCall('email');
				$eObj = $email->getEmail( $suspend_email );

				$content = array();
				$user = oseCall('user2')->instance($this->user_id);

				$content['user'] = (object)$user->outputPayment($entry_type);

				$mem = new stdClass();
				$content['member'] = $mem;

				$eObj->setEmailVariables($content);
				$eObj->transEmail();
				$eTemp = $eObj->output();
				$email->sendEmail($eTemp, $this->get('email'));
				//
				if(oseGetValue($config,'sendWel2Admin',false))
				{
					//$group = oseConfig::getAdminGroup('contract');
					$group = explode(',',oseGetValue($config,'email_admin_group'));
					if(empty($group))
					{
						$group = oseConfig::getAdminGroup($entry_type);
					}

					$email->sendToGroup($eTemp, $group);
				}
			}

			$result['success'] = true;
			$result['title'] = JText::_('SUCCESS');
			$result['content'] = JText::_('MEMBERSHIP_JOIN_SUCCEED');
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('MEMBERSHIP_JOIN_FAILED');
		}
		return $result;
	}
	
	// trigger when membership cancelled
	function cancel($id,$entry_type = 'msc', $entry_option = null)
	{
		$db = oseDB::instance();

		$query = " SELECT `id` FROM `{$this->_table}`"
		." WHERE `msc_id`='{$id}' AND `member_id`='{$this->user_id}'"
		;
		$db->setQuery($query);
		$mm_id = $db->loadResult();

		$info = $this->find($id);

		if(empty($info) || !in_array($info->get('status') ,array(1,3)))
		{
			// no need to quit if empty
			$result = array();
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('NO_MEMBERSHIP_TO_CANCEL');
			return $result;
		}

		$info->set('status','refunded');
		$updated = $info->update();

		// cancel addon
		if($updated)
		{
			$addon = oseCall('msc')->instance('addon',array('user_id'=>$this->user_id,'mm_id'=>$mm_id));
			$jResult = $addon->runActionGroup('join','quit',false);

			$updated = $jResult['success'];
			if($updated)
			{
				// cancel email
				$msc = oseCall('msc')->instance('plan',array('id'=>$id));
				$mscExtmsc = $msc->getExt('msc');
				$cancel_email = oseGetValue($mscExtmsc,'cancel_email');
				if(empty($cancel_email))
				{
					$config = oseConfig::load($entry_type,'email','obj');
					$cancel_email = oseGetValue($config,'default_cancel_email',0);
				}
					
				if( $cancel_email )
				{
					$email = oseCall('email');
					$eObj = $email->getEmail( $cancel_email );
						
					$content = array();
					$user = oseCall('user2')->instance($this->user_id);
						
					$content['user'] = (object)$user->outputPayment($entry_type);
						
					$mem = new stdClass();
					$content['member'] = $mem;
						
					$eObj->setEmailVariables($content);
					$eObj->transEmail();
					$eTemp = $eObj->output();
					$email->sendEmail($eTemp, $this->get('email'));
					//
					if(oseGetValue($config,'sendCancel2Admin',false))
					{
						//$group = oseConfig::getAdminGroup('contract');
						$group = explode(',',oseGetValue($config,'email_admin_group'));
						if(empty($group))
						{
							$group = oseConfig::getAdminGroup($entry_type);
						}
							
						$email->sendToGroup($eTemp, $group);
					}
				}

				oseDB::delete('#__osemsc_member',array('id'=>$mm_id));
				$result['success'] = true;
				$result['title'] = JText::_('SUCCESS');
				$result['content'] = JText::_('MEMBERSHIP_CANCEL_SUCCESS');
			}
			else
			{
				$result['success'] = false;
				$result['title'] = JText::_('ERROR');
				$result['content'] = JText::_('MEMBERSHIP_CANCEL_FAILED');
			}
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('MEMBERSHIP_JOIN_FAILED');
		}
		return $result;
	}

	// trigger when membership cancelled
	function quit($id,$entry_type = 'msc', $entry_option = null)
	{
		$db = oseDB::instance();
		
		$query = " SELECT `id` FROM `{$this->_table}`"
				." WHERE `msc_id`='{$id}' AND `member_id`='{$this->user_id}'"
				;
		$db->setQuery($query);
		$mm_id = $db->loadResult();

		$info = $this->find($id);

		if(empty($info) || !in_array($info->get('status') ,array(1,3)))
		{
			// no need to quit if empty
			$result = array();
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('NO_MEMBERSHIP_TO_CANCEL');
			return $result;
		}
		
		$info->set('status','refunded');
		$updated = $info->update();

		// cancel addon
		if($updated)
		{
			$addon = oseCall('msc')->instance('addon',array('user_id'=>$this->user_id,'mm_id'=>$mm_id));
			$jResult = $addon->runActionGroup('join','quit',false);

			$updated = $jResult['success'];
			if($updated)
			{
				// cancel email
				$msc = oseCall('msc')->instance('plan',array('id'=>$id));
				$mscExtmsc = $msc->getExt('msc');
				$cancel_email = oseGetValue($mscExtmsc,'cancel_email'); 
				if(empty($cancel_email))
				{
					$config = oseConfig::load($entry_type,'email','obj');
					$cancel_email = oseGetValue($config,'default_cancel_email',0);
				}
			
				if( $cancel_email )
				{
					$email = oseCall('email');
					$eObj = $email->getEmail( $cancel_email );
			
					$content = array();
					$user = oseCall('user2')->instance($this->user_id);
			
					$content['user'] = (object)$user->outputPayment($entry_type);
			
					$mem = new stdClass();
					$content['member'] = $mem;
					
					$eObj->setEmailVariables($content);
					$eObj->transEmail();
					$eTemp = $eObj->output();
					$email->sendEmail($eTemp, $this->get('email'));
					//
					if(oseGetValue($config,'sendCancel2Admin',false))
					{
						//$group = oseConfig::getAdminGroup('contract');
						$group = explode(',',oseGetValue($config,'email_admin_group'));
						if(empty($group))
						{
							$group = oseConfig::getAdminGroup($entry_type);
						}
							
						$email->sendToGroup($eTemp, $group);
					}
				}
				
				oseDB::delete('#__osemsc_member',array('id'=>$mm_id));
				$result['success'] = true;
				$result['title'] = JText::_('SUCCESS');
				$result['content'] = JText::_('MEMBERSHIP_CANCEL_SUCCESS');
			}
			else
			{
				$result['success'] = false;
				$result['title'] = JText::_('ERROR');
				$result['content'] = JText::_('MEMBERSHIP_CANCEL_FAILED');
			}
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('MEMBERSHIP_JOIN_FAILED');
		}
		return $result;
	}
	
	function expire($id,$entry_type = 'msc', $entry_option = null)
	{
		$db = oseDB::instance();
		
		$query = " SELECT * FROM `{$this->_table}`"
				." WHERE `msc_id`='{$id}' AND `member_id`='{$this->user_id}'"
				;
		$db->setQuery($query);
		$item = oseDB::loadItem('obj');
		
		$mscInfo = $this->find($id);

		if(empty($info) || !in_array($info->get('status') ,array(1,3)))
		{
			// no need to quit if empty
			$result = array();
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('NO_MEMBERSHIP_TO_CANCEL');
			return $result;
		}

		$info->set('status','expired');
		$updated = $info->update();


		// cancel addon
		if($updated)
		{
			$addon = oseCall('msc')->instance('addon',array('user_id'=>$this->user_id,'mm_id'=>$item->id));
			$jResult = $addon->runActionGroup('join','expire',false);

			$updated = $jResult['success'];

			if($updated)
			{
				return $jResult;
			}
		}

		if($updated)
		{
			$addon = oseCall('msc')->instance('addon',array('user_id'=>$this->user_id,'mm_id'=>$item->id));
			$jResult = $addon->runActionGroup('join','cancel',false);
			
			$updated = $jResult['success'];
			if($updated)
			{
				// cancel email
				$msc = oseCall('msc')->instance('plan',array('id'=>$id));
				$mscExtmsc = $msc->getExt('msc');
				$exp_email = oseGetValue($mscExtmsc,'exp_email');
				if(empty($exp_email))
				{
					$config = oseConfig::load($entry_type,'email','obj');
					$exp_email = oseGetValue($config,'default_exp_email',0);
				}
					
				if( $exp_email )
				{
					$email = oseCall('email');
					$eObj = $email->getEmail( $exp_email );
						
					$content = array();
					$user = oseCall('user2')->instance($this->user_id);
						
					$content['user'] = (object)$user->outputPayment($entry_type);
						
					$mem = new stdClass();
					$content['member'] = $mem;
						
					$eObj->setEmailVariables($content);
					$eObj->transEmail();
					$eTemp = $eObj->output();
					$email->sendEmail($eTemp, $this->get('email'));
					//
					if(oseGetValue($config,'sendExp2Admin',false))
					{
						//$group = oseConfig::getAdminGroup('contract');
						$group = explode(',',oseGetValue($config,'email_admin_group'));
						if(empty($group))
						{
							$group = oseConfig::getAdminGroup($entry_type);
						}

						$email->sendToGroup($eTemp, $group);
					}
				}
						
					$result['success'] = true;
					$result['title'] = JText::_('SUCCESS');
					$result['content'] = JText::_('MEMBERSHIP_CANCEL_SUCCESS');
			}

			$result['success'] = true;
			$result['title'] = JText::_('SUCCESS');
			$result['content'] = JText::_('MEMBERSHIP_JOIN_SUCCEED');
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('MEMBERSHIP_JOIN_FAILED');
		}
		return $result;
	}
	
	function delete($msc_id = null)
	{
		$db = oseDB::instance();

		$where = array();
		if(!empty($msc_id))
		{
			$where['msc_id'] = $msc_id;
		}
		$where['member_id'] = $this->user->id;
		$updated = oseDB::delete($this->_table,$where);
		
		
		if($updated)
		{
			$result['success'] = true;
			$result['title'] = JText::_('SUCCESS');
			$result['content'] = JText::_('MEMBERSHIP_QUIT_SUCCEED');
		}
		else
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = JText::_('MEMBERSHIP_QUIT_FAILED');
		}
		return $result;
	}
	
	function find($msc_id)
	{
		// first search data in active memberships
		$memberships = $this->get('active_membership');
		if(isset($memberships[$msc_id]))
		{
			return new oseMemberItem($memberships[$msc_id]);
		}
		
		// then search data in expired memberships
		$memberships = $this->get('expired_membership');
		if(isset($memberships[$msc_id]))
		{
			return new oseMemberItem($memberships[$msc_id]);
		}
		
		// then search data in suspend memberships
		$memberships = $this->get('suspend_membership');
		if(isset($memberships[$msc_id]))
		{
			return new oseMemberItem($memberships[$msc_id]);
		}
		
		return array();
	}
	
	
	function hasPaidContent($ctype,$content_type,$content_id)
	{
		//$memInfo = $this->getMembercontract($member_id,'obj');
		$list = $this->get('content');

		if(empty($list[$ctype]) || empty($list[$ctype][$content_type]) || !in_array($content_id,$list[$ctype][$content_type]))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	function addPaidContent($ctype,$content_type,$content_id)
	{
		$list = $this->get('content');

		if( empty($list[$ctype]) || !is_array($list[$ctype]) )
		{
			$list[$ctype] = array();
		}

		if( empty($list[$ctype][$content_type]) || !is_array($list[$ctype][$content_type]))
		{
			$list[$ctype][$content_type] = array();
		}

		// if not has value, add it
		if(!in_array($content_id,$list[$ctype][$content_type]))
		{
			array_push($list[$ctype][$content_type],$content_id);
		}

		$vals = array();
		$vals['member_id'] = $this->user->id;//$memInfo->id;
		$vals['content'] = oseJson::encode($list);
		return oseDB::update('#__ose_contract_member','member_id',$vals);
	}
	
	
	
	function sendJoinEmail($id)
	{
		
	}
}

class oseMemberItem extends oseObject
{
	public $id = 0;
	public $msc_id = 0;
	public $member_id = 0;
	public $status = 0;
	public $eternal = 0;
	public $start_date = 0;
	public $expired_date = 0;
	public $notified  = 0;
	public $notified2 = 0;
	public $notified3 = 0;
	public $params = '';
	
	protected $_table = '#__osemsc_member';
	protected $_isNew = false;
	
	function __construct($p=array())
	{
		parent::__construct($p);
	}
	
	function create()
	{
		$vals = $this->getProperties();
		return oseDB::insert($this->_table,$vals);
	}
	
	function set($key,$value = null)
	{
		if($key == 'status')
		{
			if(!is_numeric($value))
			{
				$db = oseDB::instance();
				$query = "  SELECT * FROm `#__osemsc_member_status`"
				." WHERE `name` = '{$value}'"
				;
				$db->setQuery($query);
				$item = oseDB::loadItem('obj');
				parent::set($key,$item->id);
			}
			else
			{
				parent::set($key,$value);
			}
		}
		else
		{
			parent::set($key,$value);
		}
	}
	function update()
	{
		$vals = $this->getProperties();
		return oseDB::update($this->_table,'id',$vals);
	}
	
	function delete()
	{
		$vals = $this->getProperties();
		return oseDB::delete($this->_table,array('id'=>$this->id));
	}
}
?>