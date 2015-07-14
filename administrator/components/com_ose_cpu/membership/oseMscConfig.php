<?php
defined('_JEXEC') or die(";)");

class oseMscConfig
{
	static function getConfig($config_type = null,$type = 'array')
	{
		$db = oseDB::instance();

		$where = array();

		if(!empty($config_type))
		{
			if(is_array($config_type))
			{
				$values = array();
				foreach($config_type as $configType)
				{
					$values[] = $db->Quote($configType);
				}

				$where[] = 'type IN ('.implode(',',$values).')';
			}
			else
			{
				$where[] = 'type='.$db->Quote($config_type);
			}


		}

		$where = oseDB::implodeWhere($where);

		$query = " SELECT * FROM `#__osemsc_configuration` "
				. $where
				;

		$db->setQuery($query);

		$objs = oseDB::loadList('obj');

		if($type == 'array')
		{
			$config = array();
			foreach($objs as $obj)
			{
				$config[$obj->key] = $obj->value;//oseObject::setValue($config,$obj->key,$obj->value);
			}
		}
		else
		{
			$config = new stdClass();
			foreach($objs as $obj)
			{
				$config->{$obj->key} = $obj->value;//oseObject::setValue($config,$obj->key,$obj->value);
			}
		}
		/*foreach($objs as $obj)
		{
			$config = oseObject::setValue($config,$obj->key,$obj->value);
		}*/

		$config = oseObject::setValue($config,'id',1);

		return $config;
	}

	function generateGmapScript()
	{
		$config = self::getConfig('global','obj');

		$link = 'http://maps.google.com/maps?file=api&amp;v=2.x&amp;key='.$config->gmap_key;
		return $link;
	}

	static function getConfigItem($configItemName,$config_type,$type = 'array')
	{
		$db = oseDB::instance();

		$configItemName = $db->Quote($configItemName);

		$config_type = $db->Quote($config_type);

		$query = " SELECT * FROM `#__osemsc_configuration`"
				." WHERE `key` = {$configItemName} AND `type` = {$config_type}"
				;

		$db->setQuery($query);

		$item = oseDB::loadItem($type);

		return $item;
	}
}
?>