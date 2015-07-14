<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComLogmanDatabaseRowExtension extends ComExtmanDatabaseRowExtension
{
	public function save()
	{
		$result = parent::save();

		if ($result)
		{
			$db = JFactory::getDBO();
			if (version_compare(JVERSION, '1.6', '<'))
			{
			    // Do not show the component in menu manager
			    $db->setQuery("UPDATE #__components SET link = '' WHERE link = 'option=com_logman'");
			    $db->query();

			    $db->setQuery("SELECT id FROM #__modules WHERE module = 'mod_logman' AND title='MOD_LOGMAN' AND published = 0");
			    $id = $db->loadResult();
			    if ($id) {
				    $db->setQuery(sprintf("UPDATE `#__modules` SET title = 'LOGman - Activity Stream', position = 'cpanel', ordering = -1, published = 1
				    	WHERE id = %d LIMIT 1", $id));
					$db->query();
			    }
			}
			else
			{
				$db->setQuery("SELECT id FROM #__modules WHERE module = 'mod_logman' AND published <> -2 AND position = ''");
				$id = $db->loadResult();
				if ($id) {
                    $db->setQuery(sprintf("UPDATE `#__modules` SET position = 'cpanel', ordering = -1, published = 1, params = '{\"limit\":\"10\",\"direction\":\"desc\"}'
				    	WHERE id = %d LIMIT 1", $id));
                    $db->query();
                    $db->setQuery("REPLACE INTO #__modules_menu VALUES ($id, 0)");
                    $db->query();
				}

				// Remove com_activities from the menu table
				$db->setQuery("SELECT id FROM #__menu WHERE link = 'index.php?option=com_activities'");
				$id = $db->loadResult();

				if ($id)
				{
					$table	= JTable::getInstance('menu');
					$table->bind(array('id' => $id));
					$table->delete();
				}
			}
		}

		return $result;
	}
}