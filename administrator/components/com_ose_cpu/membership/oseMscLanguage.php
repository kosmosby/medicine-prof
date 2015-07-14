<?php
defined('_JEXEC') or die(";)");

class oseMscLanguage
{
	function translate($table, $obj)
	{
			jimport( 'joomla.language.language' );
			$lang=JRequest::getVar("lang");
			if (empty($lang))
			{
				$config =& JFactory::getConfig();
				$lang = $config->getValue('config.language');
			}
			$curLangID = self::getLangID($lang);
			return $obj;

	}
	function getLangID($lang)
	{
		$db = oseDB::instance();
		$query = "SELECT id FROM `#__languages` WHERE `code` = ".$db->Quote($lang, true)." OR  `shortcode` = ".$db->Quote($lang, true);
		$id = $db->loadResult();
		return $id;
	}
}
?>
