<?php
defined('_JEXEC') or die(";)");


class oseMembership_J15 extends oseMembership
{

	function __construct()
	{
	}

	function __invoke()
	{

	}

	function __toString()
	{
		return get_parent_class($this).' Version 1.5';
	}

	// osefile or osefolder only


	function save()
	{

	}

	function create($parent_id = 0, $ordering = 999)
	{
		$db = oseDB::instance();

		$query = " SELECT count(*) FROM `#__osemsc_acl`"
				." WHERE parent_id = {$parent_id} AND ordering ={$ordering} "
				;
		$db->setQuery($query);
		$existSibling = $db->loadResult();


		if($existSibling > 0)
		{
			if(!oseMscTree::add(array('parent_id'=>$parent_id,'ordering'=>999)))
			{
				return false;
			}

			$node = oseMscTree::getNodeByOrder($parent_id,'999','obj');
			oseMscTree::treeOrderChange($node,$ordering);
			oseMscTree::orderChange($node,$ordering);

			oseMscTree::reorder($parent_id);
		}
		else
		{
			// At the first time
			if(oseMscTree::isEmpty(0))
			{
				$parent_id = 0;
				$ordering = 1;
			}

			if(!oseMscTree::add(array('parent_id'=>$parent_id,'ordering'=>$ordering)))
			{
				return false;
			}
			$node = oseMscTree::getNodeByOrder($parent_id,$ordering,'obj');
			oseMscTree::reorder($parent_id);
		}

		return $node->id;
	}

	function update($var)
	{
		//$db = oseDB::instance();
		return oseDB::update('#__osemsc_acl','id',$var);
	}

	function isMscExist($msc_id)
	{
		$db = oseDB::instance();
		$query = " SELECT count(*) FROM `#__osemsc_acl` "
				." WHERE msc_id='{$msc_id}' "
				;
		$db->setQuery($query);
		if($db->loadResult() > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function remove($msc_id)
	{
		return oseMscTree::delNode($msc_id);
	}


	function addMember()
	{

	}

	function inviteMember()
	{

	}

	function kickMember($msc_id)
	{

	}

	function cancelMember($msc_id)
	{

	}


	function reorder($parent_id = 0)
	{
		return oseMscTree::reorder($parent_id);
	}


	function getInfo($msc_id,$type = 'array')
	{
		$item = oseMscTree::getNode($msc_id,$type);

		//$item = oseObject::setValue($item,'image',OSEMSC_B_URL.'/'.oseObject::getValue($item,'image'));
		return $item;
	}


	function getExtInfo($msc_id,$xtype,$type = 'array')
	{
		switch( $xtype )
		{
			case('paymentAdv'):
			case ('payment'):
				$info = oseMscAddon::getExtInfoItem($msc_id,$xtype,'obj');
				if(empty($info))
				{
					return array();
				}
				else
				{
					$isArray = ($type == 'array')?true:false;
					$items = oseJson::decode($info->params,$isArray);

					if(empty($items))
					{
						$items = array();
					}
					return $items;
				}

			break;

			default:
				$info = oseMscAddon::getExtInfo($msc_id,$xtype,$type);
				return $info;
			break;
		}


	}

	function getExtInfoItem($msc_id,$xtype,$type = 'array')
	{
		$info = oseMscAddon::getExtInfoItem($msc_id,$xtype,$type);
		return $info;
	}

	function orderChange($node,$ordering)
	{
		if(!oseMscTree::treeOrderChange($node,$ordering))
		{
			return false;
		}

		if(!oseMscTree::orderChange($node,$ordering))
		{
			return true;
		}

		return true;
	}

	function getParentChildren($msc_id)
	{
		$node = oseMscTree::getNode($msc_id,'obj');

		$db = oseDB::instance();

		oseDB::lock(' #__osemsc_acl READ;');

		$query = " SELECT *,CONCAT('(',ordering,')',title) AS displayText FROM `#__osemsc_acl`"
				." WHERE parent_id = {$node->parent_id} "
				;
		$db->setQuery($query);
		//oseExit($db->_sql);
		$objs = oseDB::loadList();

		oseDB::unlock();
		return $objs;
	}
	function getloginRedirect($msc_id)
	{
		$db = oseDB::instance();
		oseDB::lock('#__menu');
		$objs = array();
		$query= "SELECT * FROM `#__menu` WHERE `published` = 1 ORDER BY `menutype`, `ordering` ASC";
		$db->setQuery($query);
		$menus= $db->loadObjectList();
		$i = 0;
		if(!empty($menus)) {
			foreach($menus as $menu) {
			   $objs[$i]->menuid = $menu->id;
			   $objs[$i]->displayText = '[ID-'.$menu->id. ']-'.$menu->name;
			   $i++;
			}
		}
		oseDB::unlock();
		return $objs;
	}
	function retrieveTree($type = 'array')
	{
		$db = oseDB::instance();
		//$searchName = $db->Quote($msc_id);

		$query = "LOCK TABLE `#__osemsc_acl` AS node READ, `#__osemsc_acl` AS parent READ";
		$db->setQuery($query);
		oseDB::query();

		$where = array();



		$where[] = 'node.lft BETWEEN parent.lft AND parent.rgt';

		$where = oseDB::implodeWhere($where);

		$query = " SELECT node.*,CONCAT( REPEAT('--', COUNT(parent.id)-1), node.title) AS treename
 "
				." FROM `#__osemsc_acl` AS node,`#__osemsc_acl` AS parent"
				. $where
				." GROUP BY node.id "
				." ORDER BY node.lft; "
				;
		$db->setQuery($query);

		//oseExit($db->_sql);
		$objs = oseDB::loadList($type);

		oseDB::unlock();
		//oseExit($objs);
		return $objs;
		//return oseMscTree::retrieveTree($msc_id,$type);
	}

	function runAddonAction($action_name,$params = array(),$manual = false, $backend = true)
	{
		return oseMscAddon::runAction($action_name,$params,$manual,$backend);
	}

	function getConfig($config_type = null,$type = 'array')
	{
		$config = oseMscConfig::getConfig($config_type,$type);

		return $config;
	}

	function getConfigItem($itemName,$config_type = null,$type = 'array')
	{
		$config = oseMscConfig::getConfigItem($itemName,$config_type,$type);

		return $config;
	}

	function getCurrencyList()
	{
		$item = self::getConfigItem('primary_currency','currency','obj');
		$currencyInfos = oseJson::decode($item->default,true);
		//oseExit($currencyInfos);
		$list = array();
		$list[] = array('currency'=>$item->value,'rate'=>1);
		if (!is_array($currencyInfos))
		{
			$currencyInfos = array($currencyInfos);
		}
		$i = 0 ;
		foreach ($currencyInfos as $currencyInfo)
		{
			if ($currencyInfo =='')
			{
				unset($currencyInfos[$i]);
			}
			$i ++;
		}
		$listValues = array_values($currencyInfos);

		$list = array_merge($list,$listValues);

		return $list;
	}

	function getPaymentMscInfo($msc_id,$currency,$msc_option)
	{
		return oseRegistry::call('payment')->getInstance('View')->getMscInfo($msc_id,$currency,$msc_option );
	}

	function getMembers($msc_id,$status,$search = null, $start = 0,$limit = 20,$type = 'array')
	{
		$db = oseDB::instance();

		$where = array();


		if (!empty($search))
		{
			$searchEscaped = strtolower($db->Quote( '%'.$search.'%', false ));
			$searchEscaped = str_replace(" ", "%", $searchEscaped);

			$where[] = " LOWER(mem.username) LIKE {$searchEscaped} "
					  ." OR LOWER(mem.name) LIKE {$searchEscaped}  "
					  ." OR LOWER(mem.email) LIKE {$searchEscaped} "
					  ;
		}

		if(is_array($msc_id))
		{
			$msc_id = "('".implode("','",$msc_id)."')";
			$where[] = ' mem.msc_id IN '. $msc_id;//$db->Quote($msc_id);
		}
		else
		{
			$where[] = ' mem.msc_id = '. $db->Quote($msc_id);
		}

		//$where[] = ' luv.group_name = '. $db->Quote('master');

		if($status == 1 || $status == 0)
		{
			$where[] = ' mem.status = '. $db->Quote($status);
		}

		//$where = array_merge($where,oseJSON::generateQueryWhere());

		// Generate the where query
		$where = ( count( $where ) ? ' WHERE (' . implode( ') AND (', $where ) . ')' : '' );



		$result = array();
		$result['results'] = oseMemGroup::getMscMembers($msc_id,$where,$start,$limit);
		//oseExit($db->getQuery());
		$result['total'] = oseMemGroup::getGroupTotal($msc_id,$where);

		return $result;
	}
}