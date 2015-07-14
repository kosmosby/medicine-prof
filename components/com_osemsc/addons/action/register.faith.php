<?php
defined('_JEXEC') or die(";)");

class oseMscAddonActionRegisterFaith
{
	function getFaiths()
	{
		$db= oseDB::instance();
		
		$query = " SELECT * FROM `#__osemsc_email` "
				." WHERE type = 'faith' "
				;
		
		$db->setQuery($query);
		
		$terms = oseDB::loadList();
		
		$total = count($terms);
		
		$result = array();
		$result['total'] = $total;
		$result['results'] = $terms;
		
		$result = oseJson::encode($result);
		
		oseExit($result);
	}
	
	function getFaith()
	{
		$id = JRequest::getInt('id',0);
		$db= oseDB::instance();
		
		$query = " SELECT * FROM `#__osemsc_email` "
				." WHERE type = 'faith' AND id = {$id} "
				;
		
		$db->setQuery($query);
		
		$term = oseDB::loadItem();
		
		$result = empty($term)?'':$term;
		
		$result = oseJson::encode($result);
		
		oseExit($result);
	}

}
?>