<?php
defined('_JEXEC') or die(";)");
JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_osemsc'.DS.'helpers'.DS.'table');
class oseMsc
{
	protected $prefix = 'oseMsc';
	
	function instance($type = 'item',$params = array())
	{
		if(strtolower($type) == 'item')
		{
			require_once(dirname(__FILE__).DS.'oseContractItem.php');
			
			$class = $this->prefix."Item";
			
			if(isset($params['item']))
			{
				$item = oseGetValue($params,'item',array());
				$instance = new $class($item);
			}
			elseif(isset($params['id']) && $params['id'] > 0)
			{
				$id = oseGetValue($params,'id',0);
				$db = oseDB::instance();
				$query = " SELECT * FROM `#__ose_contract_list`"
						." WHERE `id` = '{$id}'"
						;
				$db->setQuery($query);
				$item = oseDB::loadItem();
				$instance = new $class($item);
			}
			else
			{
				$instance = new $class();
				return $instance;
			}
			
			return $instance;
			
		}
		elseif(strtolower($type) == 'itemtype')
		{
			require_once(dirname(__FILE__).DS.'oseContractItem.php');
			
			$id = oseGetValue($params,'id',0);
			$type_id = oseGetValue($params,'type_id',0);
			
			$db = oseDB::instance();
			$query = " SELECT * FORM `#__ose_contract_type`"
					." WHERE `id` = '{$type_id}'"
					;
			$db->setQuery($query);
			$item = oseDB::loadItem('obj');
	
			require_once(dirname(__FILE__).DS.'type'.DS.$item->name.'.php');
			$class = $this->_prefix.$item->name;
			
			$instance = new $class(array('id'=>$this->get('id')));
			
			return $instance;
			
		}
		elseif(strtolower($type) == 'plan')
		{
			require_once(dirname(__FILE__).DS.$this->prefix.ucfirst($type).'.php');
			
			$class = $this->prefix.ucfirst($type);
			
			if(isset($params['item']))
			{
				$item = oseGetValue($params,'item',array());
				$instance = new $class($item);
			}
			elseif(isset($params['id']) && $params['id'] > 0)
			{
				$id = oseGetValue($params,'id',0);
				$db = oseDB::instance();
				$query = " SELECT * FROM `#__osemsc_acl`"
						." WHERE `id` = '{$id}'"
						;
				$db->setQuery($query);
				$item = oseDB::loadItem();
				$instance = new $class($item);
			}
			else
			{
				$instance = new $class();
				return $instance;
			}
			
			return $instance;
			
		}
		elseif(strtolower($type) == 'addon')
		{
			require_once(dirname(__FILE__).DS.$this->prefix.ucfirst($type).'.php');
			
			//$class = $this->prefix.ucfirst($type);
			$class = 'oseMscAddonV7'; 
			if(!empty($params))
			{
				$instance = new $class($params);
			}
			else
			{
				$instance = new $class();
				return $instance;
			}
			
			return $instance;
			
		}
		else
		{
			return null;
		}
	}
	
	function getInfo($id,$type = 'array')
	{
		$db = oseDB::instance();

		$where = array();
		$where[] = "`id` = ".$db->Quote($id);

		$where = oseDB::implodeWhere($where);
		$query = " SELECT * FROM `#__osemsc_acl`"
					." WHERE `id` = '{$id}'"
					;
		$db->setQuery($query);
		$item = oseDB::loadItem($type);

		return $item;
	}
}
?>