<?php
defined('_JEXEC') or die(";)");
if (class_exists('oseMscAddon'))
{
	require_once(dirname(dirname(__FILE__)).DS.'membership'.DS.'oseMscAddon.php'); 
}	
class oseMscAddonV7 extends oseObject
{
	protected $_table = '#__osemsc_addon';
	protected $_table_app = '#__osemsc_addon_applied';
	
	protected $user_id = 0;
	protected $p = array();

	function __construct($p = array())
	{
		$this->user_id = oseGetValue($p,'user_id');
		if(empty($this->user_id))
		{
			$user = oseCall('user2')->instance();
			$this->user_id = $user->get('id');
		}
		$p = oseSetValue($p,'user_id',$this->user_id);
		$this->set('p',$p);
	}
	
	public function getList($type,$backend = false)
	{
		$db = oseDB::instance();
		$where = array();
		
		if('member' == substr($type,0,6))
		{
			$where[] = "a.`type` LIKE 'member_%'";
		}
		else
		{
			$where[] = "a.`type` = ".$db->Quote($type);
		}

		$where[] = "b.`enabled` = 1 ";

		$where = oseDB::implodeWhere($where);
		$query = " SELECT a.*,b.enabled,b.custom,b.backend AS custom_backend, b.backend_enabled AS custom_backend_enabled,b.frontend AS custom_frontend, b.frontend_enabled AS custom_frontend_enabled"
				." FROM `#__osemsc_addon` AS a"
				." INNER JOIN `#__osemsc_addon_applied` AS b ON b.addon_id =a.id"
				.$where
				;
		$db->setQuery($query);
		$types = oseDB::loadList('array','name');
		
		foreach($types as $key => $type)
		{
			$valid = $this->authorize($type['type'],$type['name'],$backend);
			
			if(!$valid)
			{
				unset($types[$key]);
				continue;
			}
			
			if($type['custom'])
			{
				$type['backend'] = $type['custom_backend'];
				$type['frontend'] = $type['custom_frontend'];
				$type['backend_enabled'] = $type['custom_backend_enabled'];
				$type['frontend_enabled'] = $type['custom_frontend_enabled'];
			}
			
			if($type['action'] == 1)
			{
				$type['action'] = "{$type['type']}.{$type['name']}";
			}
			
			$types[$key] = $type;
		}
		//$types = array_values($types);
		return $types;
	}
	
	public function runActionSingle($type,$name,$action,$backend = false)	
	{
		if('member' == substr($type,0,6))
		{
			$type = 'member';
		}
		
		$valid = $this->authorize($type,$name,$backend);
		if(!$valid)
		{
			$result = array();
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = sprintf(JTEXT::_('ADDON_AUTHORIZATION_ERROR'),$this->user_id,$type,$name);
			return $result;
		}
		
		$fileOld = "{$type}.{$name}.php";
		$fileNew = "{$name}.php";
		$filepath = ($backend == false)?JPATH_SITE:JPATH_ADMINISTRATOR;
		$filepath.= DS.'components'.DS.'com_osemsc'.DS.'addons'.DS.'action'.DS.$type;
		
		if(JFile::exists($filepath.DS.$fileOld))
		{
			require_once($filepath.DS.$fileOld);
		}
		elseif(JFile::exists($filepath.DS.$fileNew))
		{
			require_once($filepath.DS.$fileNew);
		}
		else
		{
			$result = array();
			$result['success'] = true;
			$result['title'] = true;
			$result['success'] = true;
			return $result;
		}
		
		$className = 'oseMscAddonAction'.ucfirst($type).ucfirst($name);
		$class = new $className($this->get('p'));
		
		return call_user_func(array($class,$action),$this->get('p'));
	}
	
	public function runActionGroup($type,$action,$backend = false)	
	{
		$db = oseDB::instance();
		
		$addons = $this->getList($type,$backend);

		/*$where = array();
		if('member' == substr($type,0,6))
		{
			$where[] = "`type` = '{$type}'";
		}
		else
		{
			$where[] = "`type`='{$type}'";
		}
			
		$where[] = ($backend == true)?"`backend_enabled`='1'":"`frontend_enabled`='1'";
		$where[] = "`action` != 0";
		$where = oseDB::implodeWhere($where);
		
		$query = " SELECT * FROM `#__osemsc_addon`"
				.$where
				;
		$db->setQuery($query);
		$objs = oseDB::loadList('obj');*/
		
		foreach($addons as $obj)
		{
			if($obj['action'] == '0')
			{
				continue;
			}
			
			if($backend)
			{
				if($obj['backend_enabled'] != 1)
				{
					continue;
				}
			}
			else
			{
				if($obj['frontend_enabled'] != 1)
				{
					continue;
				}
			}

			if('member' == substr($type,0,6))
			{
				$type = 'member';
			}

			$updated = $this->runActionSingle($type,$obj['name'],$action,$backend);

			if($updated['success'] == false)
			{
				$this->setError($updated['content']);
			}
		}
		
		$errors = $this->getErrors();
		
		$result = array();
		if(count($errors) > 0)
		{
			$result['success'] = false;
			$result['title'] = JText::_('ERROR');
			$result['content'] = '<ui><li>'.implode("</li><li>",$errors).'</li></ui>';
		}
		else
		{
			$result['success'] = true;
			$result['title'] = JText::_('SUCCESS');
			$result['content'] = JText::_('SUCCESS');
		}
		
		return $result;
	}
	
	function authorize($type,$name,$backend=false)
	{
		$db = oseDB::instance();
		
		if('member' == substr($type,0,6))
		{
			$nType = 'member';
		}
		else
		{
			$nType = $type;
		}
		
		// user or member
		if($this->user_id <=0)
		{
			return false;
		}
		else
		{
			
			$my = oseCall('user2')->instance();
			// addons only run in backend, need specific group. e.g. super admin
			if(!$backend && in_array($type,array('panel','bridge','content')))
			{
				return false;
			}
			elseif($backend && in_array($type,array('panel','bridge','content')))
			{
				if(!$my->isUserAdmin())
				{
					return false;
				}
			}
			
			if($nType == 'member')
			{
				if($backend)
				{
					if(!$my->isUserAdmin())
					{
						return false;
					}
				}
				else
				{
					$user = oseCall('user2')->instance($this->user_id,'msc');
					
					if(in_array($name,array('juser','billinginfo','profile')))
					{
						
					}
					elseif(count($user->get('active_membership')) < 1)
					{
						return false;
					}
				}
			}
		}
		
		// run addon validate if func existed
		$fileOld = "{$nType}.{$name}.php";
		$fileNew = "{$name}.php";
		$filepath = ($backend == false)?JPATH_SITE:JPATH_ADMINISTRATOR;
		$filepath.= DS.'components'.DS.'com_osemsc'.DS.'addons'.DS.'action'.DS.$nType;
		
		if(JFile::exists($filepath.DS.$fileOld))
		{
			require_once($filepath.DS.$fileOld);
		}
		elseif(JFile::exists($filepath.DS.$fileNew))
		{
			require_once($filepath.DS.$fileNew);
		}
		else
		{
			return true;
		}

		$className = 'oseMscAddonAction'.ucfirst($nType).ucfirst($name);
		
		if($nType == 'member')
		{
			$class = new $className($this->get('p'));
		}
		else
		{
			$class = new $className($this->get('p'));
		}
		
		
		if(method_exists($class,'validateAccess'))
		{
			return call_user_func(array($class,'validateAccess'));
		}
			
		return true;
	}
}
?>