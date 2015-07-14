<?php
/**
 * ------------------------------------------------------------------------
 * JUDownload for Joomla 2.5, 3.x
 * ------------------------------------------------------------------------
 *
 * @copyright      Copyright (C) 2010-2015 JoomUltra Co., Ltd. All Rights Reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @author         JoomUltra Co., Ltd
 * @website        http://www.joomultra.com
 * @----------------------------------------------------------------------@
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');


jimport('joomla.application.component.modellist');

class JUDownloadModelStyles extends JModelList
{

	
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'style.id',
				'style.title',
				'tpl.id',
				'plg.id',
				'plg.title',
				'plg.author',
				'plg.version',
				'style.created',
				'plg.license',
				'style.home',
				'style.lft',
				'template'
			);
		}

		parent::__construct($config);
	}

	
	protected function populateState($ordering = null, $direction = null)
	{
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$templateId = $this->getUserStateFromRequest($this->context . '.filter.template', 'filter_template');
		$this->setState('filter.template', $templateId);

		parent::populateState('style.lft', 'asc');
	}

	
	protected function getStoreId($id = '')
	{
		
		$id .= ':' . $this->getState('list.search');
		$id .= ':' . $this->getState('filter.template');

		return parent::getStoreId($id);
	}

	protected function getListQuery()
	{
		
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('style.*');
		$query->from('#__judownload_template_styles AS style');

		
		$query->select('plg.title AS template_title, plg.folder AS folder');
		$query->join('', '#__judownload_templates AS tpl ON tpl.id = style.template_id');
		$query->join('', '#__judownload_plugins AS plg ON plg.id = tpl.plugin_id');

		
		$query->select('l.title AS language_title');
		$query->select('l.image AS image');
		$query->join('LEFT', '#__languages AS l ON l.lang_code = style.home');

		
		$query->select('ua.name AS checked_out_name');
		$query->join('LEFT', '#__users AS ua ON ua.id = style.checked_out');

		
		if ($templateId = $this->getState('filter.template'))
		{
			$query->where('tpl.id = ' . (int) $templateId);
		}

		
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('style.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->quote('%' . $db->escape($search, true) . '%');
				$query->where('plg.title LIKE ' . $search . ' OR style.title LIKE ' . $search);
			}
		}

		
		$orderCol  = $this->getState('list.ordering', 'style.lft');
		$orderDirn = $this->getState('list.direction', 'ASC');

		if ($orderCol != '')
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}

}