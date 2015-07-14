<?php
defined('_JEXEC') or die(";)");

class oseEmail extends oseInit
{
	protected $table = '#__ose_app_email';
	protected $task = array();
	protected $prefix = 'oseEmail';
	protected $path = null;
	protected $instance = array();
	protected $emailParams = array();
	protected $contentVars = array();
	
	function __construct()
	{
		$this->path = dirname(__FILE__);
		$this->setRegisteredTasks();
		$this->setRegisteredInstances();
	}

	protected function setRegisteredTasks()
	{
		// NULL
	}

	protected function setRegisteredInstances()
	{
		// NULL
	}
	
	protected function send($subject, $body,$address)
	{
		$mail = &JFactory::getMailer();
		$mail->addRecipient($address);
		$mail->setSubject($subject);
		$mail->setBody($body);
		$mail->IsHTML(true);
		$mail->Send();
	}

	function sendEmail($email,$emailAddress = null)
	{
		//$email = self::getDoc($email_id,'obj');

		if(empty($emailAddress) || empty($email->subject) || empty($email->body))
		{
			return false;
		}
		return self::send($email->subject, $email->body,$emailAddress);
	}
	
	function sendToGroup($email,$group,$force2Send = false)
	{
		$db = oseDB::instance();

		$version = oseHTML::getJoomlaVersion();
		
		if(is_array($group))
		{
			$group = implode(',',$group);
		}
		
		$where = array();
		if(!$force2Send)
		{
			$where[] = "`sendEmail` =1";
		}
		
		if($version == '1.5')
		{
			$where[] = "`gid` IN ( {$group} )"; 
			$where = oseDB::implodeWhere($where);
			$query = " SELECT * FROM `#__users` AS u "
					. $where
					;
		}
		else
		{
			$where[] = "g.group_id IN ( {$group} )"; 
			$where = oseDB::implodeWhere($where);
			$query = " SELECT u.* FROM `#__users` AS u "
					." INNER JOIN `#__user_usergroup_map` AS g ON g.user_id = u.id"
					. $where
					;
		}

		$db->setQuery($query);
		$objs = oseDB::loadList('obj');

		foreach($objs as  $obj)
		{
			self::sendEmail($email,$obj->email);
		}

		return true;
	}
	
	public function transEmail($email)
	{
		$params = $this->get('emailParams');
		$content_item = $this->get('contentVars');
		
		$db = JFactory::getDBO();

		$tEmailSubject = $email->subject;
		$tEmailBody = $email->body;

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
		$email->subject = $tEmailSubject;
		$email->body = $tEmailBody;

		return $email;
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
	
	public function SimpletransEmail($email)
	{
		$params = $this->get('emailParams');
		$content_item = $this->get('contentVars');
		
		$db = JFactory::getDBO();

		$tEmailSubject = $email->subject;
		$tEmailBody = $email->body;

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
		$email->subject = $tEmailSubject;
		$email->body = $tEmailBody;

		return $email;
	}
	
	public function create($array = array())
	{
		require_once(dirname(__FILE__).DS.'emailObject.php');
		
		$classname = get_class($this).'Object';
		
		$class = new $classname($array);
		
		return $class;
	}
	
	function getEmails($app)
	{
		$db = oseDB::instance();
		$query = " SELECT * FROM `{$this->table}`"
				." WHERE `app` = ".$db->Quote($app)
				;
		$db->setQuery($query);
		$list = oseDB::loadList('obj');	
		return $list;
	}
	
	function getEmail($id)
	{
		$db = oseDB::instance();
		$query = " SELECT * FROM `{$this->table}`"
				." WHERE `id` = ".$db->Quote($id)
				;
		$db->setQuery($query);
		$item = oseDB::loadItem();
		
		if(empty($item))
		{
			$item = array();
			$classname = get_class($this).'Object';
			$class = new $classname($item);
		}
		else
		{
			$class = $this->getApp($item['app'], $item);
		}
		//require_once(dirname(__FILE__).DS.'emailObject.php');
		
		
		
		return $class;
	}
	
	function getApp($app,$item = array())
	{
		$file = ucfirst(strtolower($app));
		$folder = strtolower($app);
		
		switch($folder)
		{
			case('lic'):
				$folder = 'license2';
			break;
			
			default:
			
			break;
		}
		require_once(OSECPU_B_PATH.DS.$folder.DS."oseEmail{$file}.php");
		$classname = $this->prefix."{$app}";
		return new $classname($item);
	}
}