<?php
defined('_JEXEC') or die(";)");
class oseMscItem extends oseTreeModel
{
	protected $id = 0;
	protected $user_id = 0;
	protected $content = null;
	protected $type = 0;
	protected $form_id = 1;
	protected $template_id = 2;
	protected $start_date = 1;
	protected $end_date = 1;
	protected $create_date = 1;
	protected $activated = 0;
	protected $status = null;
	//protected $data = array();
	protected $ordering = 1;
	protected $lft = 1;
	protected $rgt = 2;
	protected $leaf = 0;
	protected $level = 1;
	protected $published = 1;
	
	protected $params = '';
	
	protected $_prefix = 'oseMscItem';
	protected $_isNew = false;
	protected $_table = '#__osemsc_acl';
	protected $_tableType = '#__osemsc_plan_type';
	protected $_extend = null;
	protected $_type = array();
	
	function __construct($p = array())
	{
		parent::__construct($p);
		
		if(count($this->get('_type')) < 1)
		{
			$db = oseDB::instance();
			$query = " SELECT * FROM `{$this->_tableType}`";
			$db->setQuery($query);
			$this->set('_type',oseDB::loadList('obj','id'));
		}
			
		if($this->get('id',0) > 0)
		{
			$info = $this->getTypeInfo();
			unset($info['id']);
			if(isset($info['params']))
			{
				
				$info['typeParams'] = oseGetValue($info,'params');
				unset($info['params']);
				
			}
			$this->setProperties($info);
			
		}
		else
		{
			$this->set('_isNew',true);
		}
	}
	
	protected function getTypeInfo()
	{
		$type = $this->get('type');
		$this->_extend = $this->getTypeItem($type);
		return $this->_extend->getProperties();
	}
	
	function getTypeItem($type)
	{
		$db = oseDB::instance();
		$query = " SELECT * FROM `{$this->_tableType}`"
				." WHERE `id` = '".$type."'"
				;
		$db->setQuery($query);
		$item = oseDB::loadItem('obj');
		$this->set('type_name',$item->name);
		$this->set('type_title',$item->title);
		require_once(dirname(__FILE__).DS.'type'.DS.$item->name.'.php');
		$class = $this->_prefix.$item->name;
		
		$class = new $class(array('msc_id'=>$this->get('id')));
		return $class;
	}
	
	function create()
	{
		$db = oseDB::instance();
		$query = " SELECT COUNT(*) FROM `{$this->_table}`"
				//." WHERE `level`='1'"
				;
		$db->setQuery($query);
		$num = $db->loadResult();
		if($num > 0)
		{
			$this->set('lft',$num*2+1);
			$this->set('rgt',$num*2+2);
			$this->set('ordering',$num+1);
		}
		
		$vals = $this->getProperties();
		$updated = oseDB::insert($this->_table,$vals);
		
		if($updated)
		{
			$this->set('id',$updated);
			foreach($this->_type as $k => $o)
			{
				$class = $this->getTypeItem($o->id);
			}
			$this->__construct();
			return $updated;
		}
		else
		{
			return false;
		}
	}
	
	function update()
	{
		$vals = $this->getProperties();
		$updated = oseDB::update($this->_table,'id',$vals);
		
		if($updated)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	function delete()
	{
		$db = oseDB::instance();
		
		$query = " SELECT `id` FROM `{$this->_table}`"
				." WHERE lft BETWEEN {$this->lft} AND {$this->rgt}"
				;
		$db->setQuery($query);
		$ids = $db->loadResultArray();
		JArrayHelper::toInteger($ids);
		$ids = implode(',',$ids);
		
		$updated = parent::delete();
		if($updated)
		{
			foreach($this->_type as $k => $o)
			{
				$class = $this->getTypeItem($o->id);
				$query = " DELETE FROM `".$class->get('_table')."`"
						." WHERE `plan_id` IN ($ids)"
						;
				$db->setQuery($query);
				oseDB::query();
			}
		}
		
		return $updated;
		/*
		$updated = oseDB::delete($this->_table,array('id'=>$this->get('id')));
		
		if($updated)
		{
			foreach($this->_type as $k => $o)
			{
				$class = $this->getTypeItem($o->id);
				$class->delete();
			}
		}
		
		return $updated;*/
	}
	
	function refresh()
	{
		$db = oseDB::instance();
		$query = " SELECT * FROM `{$this->_table}`"
				." WHERE `id` = '{$this->id}'"
				;
		$db->setQuery($query);
		$item = oseDB::loadItem();
		$this->__construct($item);
	}
}
?>