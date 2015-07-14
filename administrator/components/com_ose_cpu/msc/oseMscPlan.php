<?php
defined('_JEXEC') or die(";)");
class oseMscPlan extends oseTreeModel {
	protected $id = 0;
	protected $parent_id = 0;
	protected $title = null;
	protected $alias = null;
	protected $type = 'requiredoption';
	protected $description = null;
	protected $restricted_number = 99999;
	protected $msc_option = null;
	//protected $ordering = 1;
	//protected $lft = 1;
	//protected $rgt = 2;
	//protected $leaf = 0;
	//protected $level = 1;
	protected $published = 1;
	protected $image = null;
	protected $menuid = null;
	protected $params = '';
	protected $_prefix = 'oseMscPlan';
	protected $_isNew = false;
	protected $_table = '#__osemsc_acl';
	protected $_tableType = '#__osemsc_plan_type';
	protected $_extend = null;
	protected $_type = array();
	protected $_ext = array(); // extension setting
	function __construct($p = array()) {
		parent::__construct($p);
		if (count($this->get('_type')) < 1) {
			$db = oseDB::instance();
			$query = " SELECT * FROM `{$this->_tableType}`";
			$db->setQuery($query);
			$this->set('_type', oseDB::loadList('obj', 'id'));
		}
		if ($this->get('id', 0) > 0) {
			$info = $this->getTypeInfo();
			unset($info['id']);
			if (isset($info['params'])) {
				$info['typeParams'] = oseGetValue($info, 'params');
				unset($info['params']);
			}
			$this->setProperties($info);
		} else {
			$this->set('_isNew', true);
		}
	}
	protected function getTypeInfo() {
		$type = $this->get('type');
		$this->_extend = $this->getTypeItem($type);
		if (!empty($this->_extend))
		{	
			return $this->_extend->getProperties();
		}
		else
		{
			return false; 
		}	
	}
	function getTypeItem($type) {
		$db = oseDB::instance();
		$query = " SELECT * FROM `{$this->_tableType}`" . " WHERE `name` = '" . $type . "'";
		$db->setQuery($query);
		$item = oseDB::loadItem('obj');
		if (!empty($item))
		{	
			$this->set('type_name', $item->name);
			$this->set('type_title', $item->title);
			require_once(dirname(__FILE__) . DS . 'plan' . DS . $item->name . '.php');
			$class = $this->_prefix . $item->name;
			$class = new $class(array('plan_id' => $this->get('id')));
			return $class;
		}
		else
		{
			return false; 
		}	
	}
	function create() {
		$values = $this->getProperties();
		$tAddon = JTable::getInstance('Membership', 'oseMscTable');
		$tAddon->set('_tbl', $this->_table);
		//
		if ($values['id'] > 0) {
			$tAddon->load($values['id']);
		}
		// Set the new parent id if parent id not matched OR while New/Save as Copy .
		if ($tAddon->parent_id != $values['parent_id'] || $values['id'] == 0) {
			$tAddon->setLocation($values['parent_id'], 'last-child');
		}
		// Bind the data.
		if (!$tAddon->bind($values)) {
			$this->setError($tAddon->getError());
		}
		// Check the data.
		if (!$tAddon->check()) {
			$this->setError($tAddon->getError());
		}
		$updated = $tAddon->store();
		if ($updated) {
			$this->set('id', $tAddon->get('id'));
			foreach ($this->_type as $k => $o) {
				$class = $this->getTypeItem($o->name);
			}
			return $tAddon->get('id');
		} else {
			return false;
		}
	}
	function update() {
		$values = $this->getProperties();
		$tAddon = JTable::getInstance('Membership', 'oseMscTable');
		$tAddon->set('_tbl', $this->_table);
		//
		if ($values['id'] > 0) {
			$tAddon->load($values['id']);
		}
		// Set the new parent id if parent id not matched OR while New/Save as Copy .
		if ($tAddon->parent_id != $values['parent_id'] || $values['id'] == 0) {
			$tAddon->setLocation($values['parent_id'], 'last-child');
		}
		// Bind the data.
		if (!$tAddon->bind($values)) {
			$this->setError($tAddon->getError());
		}
		// Check the data.
		if (!$tAddon->check()) {
			$this->setError($tAddon->getError());
		}
		$updated = $tAddon->store();
		if ($updated) {
			return true;
		} else {
			return false;
		}
	}
	function delete() {
		$db = oseDB::instance();
		$query = " SELECT `id` FROM `{$this->_table}`" . " WHERE lft BETWEEN {$this->lft} AND {$this->rgt}";
		$db->setQuery($query);
		$ids = $db->loadResultArray();
		JArrayHelper::toInteger($ids);
		$ids = implode(',', $ids);
		$updated = parent::delete();
		if ($updated) {
			foreach ($this->_type as $k => $o) {
				$class = $this->getTypeItem($o->name);
				//$class->delete();
				if ($class->get('_table', false) != false) {
					$query = " DELETE FROM `" . $class->get('_table') . "`" . " WHERE `plan_id` IN ($ids)";
					$db->setQuery($query);
					oseDB::query();
				}
			}
		}
		return $updated;
	}
	function output() {
		$extend = $this->get('_extend');
		if (!empty($extend)) {
			$info = $extend->output();
		}
		$msc = oseCall('msc');
		$tMsc = JTable::getInstance('Membership', 'oseMscTable');
		if ($this->get('parent_id') != $tMsc->getRootId()) {
			$cartItem = $msc->instance('plan', array('id' => $this->get('parent_id')));
			$info['main_title'] = $cartItem->get('title');
			$info['title'] = $this->get('title');
		} else {
			$info['main_title'] = $this->get('title');
			$info['title'] = '';
		}
		$info['image'] = $this->get('image');
		return $info;
	}
	function refresh() {
		$db = oseDB::instance();
		$query = " SELECT * FROM `{$this->_table}`" . " WHERE `id` = '{$this->id}'";
		$db->setQuery($query);
		$item = oseDB::loadItem();
		$this->__construct($item);
	}
	function getExt($type) {
		$db = oseDB::instance();
		if (OSEMSCVERSION>='7.0.0')
		{	
			$query = "SELECT * FROM `#__osemsc_ext`" . " WHERE `msc_id` = '{$this->id}' AND `type`=" . $db->Quote($type);
		}
		else
		{
			$query = "SELECT * FROM `#__osemsc_ext`" . " WHERE `id` = '{$this->id}' AND `type`=" . $db->Quote($type);
		}	
		$db->setQuery($query);
		$item = oseDB::loadItem();
		$item['params'] = oseJson::decode(oseGetValue($item, 'params', '{}'), true);
		$item = array_merge($item, $item['params']);
		unset($item['params']);
		$ext = $this->get('_ext');
		$ext[$type] = $item;
		$this->set('_ext', $ext);
		return $item;
	}
	function rebuild() {
		$table = JTable::getInstance('Membership', 'oseMscTable');
		$tAddon->set('_tbl', $this->_table);
		$this->reorder();
		//return $table->rebuild();
	}
	function getTotal() {
		$db = oseDB::instance();
		//JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'table');
		$table = JTable::getInstance('Membership', 'oseMscTable');
		$table->load($this->get('id'));
		$tree = $table->getTree();
		$ids = array();
		foreach ($tree as $item) {
			$ids[] = $item->id;
		}
		$ids = '(' . implode(',', $ids) . ')';
		$query = " SELECT COUNT(*) FROM `#__osemsc_member`" . " WHERE `msc_id` IN {$ids}";
		$db->setQuery($query);
		$total = $db->loadResult();
		return $total;
	}
	function isAllowed2Join() {
		$db = oseDB::instance();
		$total = $this->getTotal();
		$now = oseHtml2::getDateTime();
		// before validate the access, we delete the useless data first
		$query = " DELETE FROM `#__osemsc_member_join_session`" . " WHERE `msc_id`='{$this->id}' AND `start_date` < DATE_SUB('{$now}',INTERVAL 30 MINUTE)";
		$db->setQuery($query);
		oseDB::query();
		// detect whether it is full
		if ($total == $this->restricted_number) {
			return false;
		}
		// default half an hour
		$query = "SELECT COUNT(*) FROM `#__osemsc_member_join_session`" . " WHERE `msc_id`='{$this->id}' AND `start_date` > DATE_SUB('{$now}',INTERVAL 30 MINUTE)";
		$db->setQuery($query);
		$temporary = $db->loadResult();
		$nowTotal = $total + $temporary;
		if ($nowTotal >= $this->restricted_number) {
			return false;
		} else {
			return true;
		}
	}
}
?>