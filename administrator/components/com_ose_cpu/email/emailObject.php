<?php
defined('_JEXEC') or die(";)");

class oseEmailObject extends oseInit
{
	protected $table = '#__ose_app_email';
	protected $task = array();
	
	protected $id = null;
	protected $app = null;
	protected $subject = array();
	protected $body = array();
	protected $params = null;
	protected $type = null;
	
	protected $emailParams = array();
	protected $contentVars = array();
	protected $isNew = false;
	
	function __construct($item = array())
	{
		parent::__construct($item);
		
		$params = $this->get('params');
		if( !empty($params) )
		{
			$this->buildEmailParams($params);
		}
		
		if($this->get('id') == 0)
		{
			$this->set('isNew',true);
		}
	}
	
	function buildEmailParams($array)
	{
		$this->set('emailParams',$array);
		//return $this->get('emailParams');
	}
	
	function setEmailVariables($content_item)
	{
		$this->set('contentVars',$content_item);
	}
	
	function update()
	{
		$vals = $this->toArray();
		//$vals['id'] = $this->get('id');
		//$vals['subject'] = $this->get('subject');
		//$vals['body'] = $this->get('body');
		unset($vals['emailParams']);
		unset($vals['contentVars']);
		$vals['params'] = oseJson::encode($this->get('params'));
		
		return oseDB::update($this->table,'id',$vals);
	}
	
	public function simpleTransEmail()
	{
		$params = $this->get('emailParams');
		$content_item = $this->get('contentVars');

		$tEmailSubject = $this->get('subject');
		$tEmailBody = $this->get('body');

		foreach ($params as $key => $param)
		{

			if(isset($content_item[$param]))
			{
				$replace = $content_item[$param];
			}
			else
			{
				$replace = null;
			}

			$tKey = $key;//preg_replace('/_/','.',$key,1);

			$tEmailSubject = str_replace("[{$tKey}]",$replace,$tEmailSubject);
			$tEmailBody = str_replace("[{$tKey}]",$replace,$tEmailBody);
		}
		$jroot = JURI::root();
		$jroot = explode("components", $jroot);
		$jroot = $jroot[0];
		$tEmailBody = str_replace("../", $jroot, $tEmailBody);
		
		$this->subject = $tEmailSubject;
		$this->body = $tEmailBody;
	}
	
	public function transEmail()
	{
		$params = $this->get('emailParams');
		$content_item = $this->get('contentVars');
		
		$db = JFactory::getDBO();

		$tEmailSubject = $this->get('subject');
		$tEmailBody = $this->get('body');

		foreach ($params as $key => $param)
		{
			$arr = explode('.',$param);
			$valueType = $arr[0];
			$valueName = $arr[1];

			if(isset($content_item[$valueType]->{$valueName}))
			{
				$replace = $content_item[$valueType]->{$valueName};
			}
			else
			{
				$replace = null;
			}

			$tKey = $key;//preg_replace('/_/','.',$key,1);

			$tEmailSubject = str_replace("[{$tKey}]",$replace,$tEmailSubject);
			$tEmailBody = str_replace("[{$tKey}]",$replace,$tEmailBody);
		}
		$jroot = JURI::root();
		$jroot = explode("components", $jroot);
		$jroot = $jroot[0];
		$tEmailBody = str_replace("../", $jroot, $tEmailBody);
		
		$this->subject = $tEmailSubject;
		$this->body = $tEmailBody;

	}
	
	function output()
	{
		$email = new stdClass();
		$email->subject = $this->get('subject');
		$email->body = $this->get('body');
		return $email;
	}
	
	function create()
	{
		$vals = $this->toArray();
		
		unset($vals['id']);
		$vals['params'] = oseJson::encode($this->get('params'));
		return oseDB::insert($this->table,$vals);
	}
	
	function getEmailParams($type)
	{
		$params = array();

		switch($type)
		{
			case('cancelorder_email'):
			case('receipt'):
				$params['user.username'] = 'user.username';
				$params['user.name'] = 'user.jname';
				$params['user.email'] = 'user.email';
				//$params['user.user_status'] = 'user.block';
				$params['user.firstname'] = 'user.first_name';
				$params['user.lastname'] = 'user.last_name';
				//$params['user.primary_contact'] = 'user.primary_contact';
				$params['user.company'] = 'user.company';
				$params['user.address1'] = 'user.addr1';
				$params['user.address2'] = 'user.addr2';
				$params['user.city'] = 'user.city';
				$params['user.state'] = 'user.state';
				$params['user.country'] = 'user.country';
				$params['user.postcode'] = 'user.postcode';
				$params['user.telephone'] = 'user.telephone';

				$params['order.order_id'] = 'order.order_id';
				$params['order.order_number'] = 'order.order_number';
				$params['order.order_status'] = 'order.order_status';
				$params['order.payment_serial_number'] = 'order.payment_serial_number';
				//$params['order.price'] = 'order.price';
				//$params['order.final_price'] = 'order.payment_price';
				$params['order.vat_number'] = 'order.vat_number';
				$params['order.payment_currency'] = 'order.payment_currency';
				//$params['order.subtotal'] = 'order.subtotal';
				//$params['order.total'] = 'order.total';
				//$params['order.gross_tax'] = 'order.gross_tax';
				//$params['order.discount'] = 'order.discount';
				$params['order.itemlist'] = 'order.itemlist';
				$params['order.payment_method'] = 'order.payment_method';
				$params['order.date'] = 'order.create_date';
				$params['order.payment_mode'] = 'order.payment_mode';
				//$params['order.recurring_price'] = 'order.recurring_price';
				$params['order.recurring_frequency'] = 'order.recurring_frequency';
				$params['order.cycle'] = 'order.cycle';
			break;

			case('wel_email'):
				$params['user.username'] = 'user.username';
				$params['user.name'] = 'user.name';
				$params['user.email'] = 'user.email';
				//$params['user.user_status'] = 'user.block';
				$params['user.firstname'] = 'user.first_name';
				$params['user.lastname'] = 'user.last_name';
				//$params['user.primary_contact'] = 'user.primary_contact';
				$params['user.company'] = 'user.company';
				$params['user.address1'] = 'user.addr1';
				$params['user.address2'] = 'user.addr2';
				$params['user.city'] = 'user.city';
				$params['user.state'] = 'user.state';
				$params['user.country'] = 'user.country';
				$params['user.postcode'] = 'user.postcode';
				$params['user.telephone'] = 'user.telephone';

				$params['member.start_date'] = 'member.start_date';
				$params['member.expired_date'] = 'member.real_expired_date';
				$params['member.period'] = 'member.period';
				$params['member.msc_title'] = 'member.msc_title';
				$params['member.plan'] = 'member.title';
				$params['member.plan_desc'] = 'member.desc';
				$params['member.msc_des'] = 'member.msc_des';
				$params['member.status'] = 'member.status';

				/*$params['order.order_id'] = 'order.order_id';
				$params['order.order_number'] = 'order.order_number';
				$params['order.order_status'] = 'order.order_status';
				//$params['order.price'] = 'order.price';
				//$params['order.final_price'] = 'order.payment_price';
				$params['order.subtotal'] = 'order.subtotal';
				$params['order.total'] = 'order.total';
				$params['order.discount'] = 'order.discount';
				$params['order.table'] = 'order.table';
				$params['order.payment_method'] = 'order.payment_method';
				$params['order.date'] = 'order.create_date';
*/

			break;

			case('reg_email'):
				$params['user.username'] = 'user.username';
				$params['user.name'] = 'user.name';
				$params['user.password'] = 'user.password';
				$params['user.email'] = 'user.email';
				$params['user.firstname'] = 'user.first_name';
				$params['user.lastname'] = 'user.last_name';
				//$params['user.primary_contact'] = 'user.primary_contact';
				//$params['user.user_status'] = 'user.block';
			break;

			case('cancel_email'):
				$params['user.username'] = 'user.username';
				$params['user.name'] = 'user.jname';
				$params['user.email'] = 'user.email';
				$params['user.firstname'] = 'user.first_name';
				$params['user.lastname'] = 'user.last_name';
				$params['user.primary_contact'] = 'user.primary_contact';

				$params['member.start_date'] = 'member.start_date';
				$params['member.expired_date'] = 'member.expired_date';
			break;

			case('exp_email'):
				$params['user.username'] = 'user.username';
				$params['user.name'] = 'user.jname';
				$params['user.email'] = 'user.email';
				$params['user.firstname'] = 'user.firstname';
				$params['user.lastname'] = 'user.lastname';
				$params['user.primary_contact'] = 'user.block';

				$params['member.start_date'] = 'member.start_date';
				$params['member.expired_date'] = 'member.expired_date';
			break;

			case('notification'):
				$params['user.username'] = 'user.username';
				$params['user.name'] = 'user.jname';
				$params['user.email'] = 'user.email';
				$params['user.firstname'] = 'user.firstname';
				$params['user.lastname'] = 'user.lastname';
				$params['user.primary_contact'] = 'user.block';

				$params['member.start_date'] = 'member.start_date';
				$params['member.expired_date'] = 'member.expired_date';
			break;

			default:
				$params = array();
			break;
		}

		return $params;
	}
}