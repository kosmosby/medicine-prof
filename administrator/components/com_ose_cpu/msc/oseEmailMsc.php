<?php
defined('_JEXEC') or die(";)");

class oseEmailMsc extends oseEmailObject
{
	protected $table = '#__ose_app_email';
	
	function __construct($item = array())
	{
		parent::__construct($item);
		$this->content = array();
	}
	
	function getEmailParams($type)
	{
		$params = parent::getEmailParams($type);
		
		switch($type)
		{
			case('wel_email'):
				unset($params['member.start_date']);
				unset($params['member.expired_date']);
				unset($params['member.period']);
				unset($params['member.msc_title']);
				unset($params['member.msc_des']);
				//$params['member.credit_amount'] = 'member.credit_amount';
				//$params['member.total_credit_amount'] = 'member.total_credit_amount';
			break;
			
			case('receipt'):
				unset($params['member.start_date']);
				unset($params['member.expired_date']);
				unset($params['member.period']);
				unset($params['member.msc_title']);
				unset($params['order.recurring_frequency']);
			break;
		}
		return $params;
	}
	
	function getEmailVariables($type,$params = array())
	{
		switch($type)
		{
			case('wel_email'):
				//$order_item_id = $params['order_item_id'];
				$entry_id = $params['entry_id'];
				//$entry_type = $params['entry_type'];
				//$entry_option= $params['entry_option'];
				$user = $params['user'];
				$this->getEmailVariablesUser($user);
				$this->getEmailVariablesMember($user,$entry_id,$entry_type = 'credit');
			break;
		}

	}
	
	/*protected function getEmailVariablesUser($user)
	{
		$db = oseDB::instance();

	 	$pUser = oseCall('user2');
	 	$pUser->init($user->id);
	 	
	 	$config = oseConfig::load('credit','email','obj');
	 	$email_app = oseGetValue($config,'email_app','default');
		
		$userInfo = array();
		$userInfo['username'] = $pUser->get('user')->username;
		$userInfo['name'] = $pUser->get('user')->name;
		$userInfo['email'] = $pUser->get('user')->email;
		
		$userInfo['first_name'] = $pUser->get('first_name');
		$userInfo['last_name'] = $pUser->get('last_name');
		$userInfo['company'] = $pUser->get('company');
		$userInfo['addr1'] = $pUser->get('addr1');
		$userInfo['addr2'] = $pUser->get('addr2');
		$userInfo['city'] = $pUser->get('city');
		$userInfo['locale_country'] = $pUser->get('country');
		$userInfo['locale_state'] = $pUser->get('state');
		
		$userInfo['postcode'] = $pUser->get('postcode');
		$userInfo['telephone'] = $pUser->get('telephone');
		
		// custom field value
		$db = oseDB::instance();
		$query = "SELECT * FROM `#__ose_commerce_form_field_value`"
				." WHERE `app` = '{$email_app}' AND `user_id` = '{$this->id}'"
				;
		$db->setQuery($query);
		$fields = oseDB::loadList('obj');
		
		foreach($fields as $field)
		{
			$userInfo[$field->field] = $field->value;
		}
		
		$this->content['user'] = $userInfo;

	}
	
	protected function getEmailVariablesMember($user,$entry_id,$entry_type = 'credit',$entry_option= null)
	{
		$db = oseDB::instance();
		$content = array();
		$credit = oseCall('credit');
		$creditInfo = $credit->getInfo($entry_id);
		$content['total_credit_amount'] = $user->get('credit_amount');
		$content['credit_amount'] = $creditInfo['credit_amount'];
		$content['title'] = $creditInfo['title'];
		$content['title'] = $creditInfo['title'];
		$content = $creditInfo;
		$this->content['member'] = $content;
	}*/

}