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

class JUDownloadModelTag extends JUDLModelList
{
	
	protected function populateState($ordering = null, $direction = null)
	{
		
		$app = JFactory::getApplication();

		
		$pk = $app->input->getInt('id', 0);
		$this->setState('tag.id', $pk);

		$params = JUDownloadHelper::getParams();
		$this->setState('params', $params);

		
		if ($this->context)
		{
			$documentPagination = $params->get('document_pagination', 10);

			$limitArray = JUDownloadFrontHelper::customLimitBox();

			if (is_array($limitArray) && count($limitArray))
			{
				$limit = $app->input->getUint('limit', null);
				if (is_null($limit) || in_array($limit, $limitArray))
				{
					$limit = $app->getUserStateFromRequest($this->context . '.list.limit', 'limit', $documentPagination, 'uint');
				}
				else
				{
					$limit = $documentPagination;
				}
			}
			else
			{
				$limit = $app->getUserStateFromRequest($this->context . '.list.limit', 'limit', $documentPagination, 'uint');
			}

			$this->setState('list.limit', $limit);

			$this->setState('list.start', $app->input->getUint('limitstart', 0));

			$orderCol = $app->getUserStateFromRequest($this->context . '.list.ordering', 'filter_order', '');
			$this->setState('list.ordering', $orderCol);

			$listOrder = $app->getUserStateFromRequest($this->context . '.list.direction', 'filter_order_Dir', 'ASC');
			$this->setState('list.direction', $listOrder);
		}
		else
		{
			$this->setState('list.start', 0);
			$this->state->set('list.limit', 0);
		}
	}

	
	public function getTag()
	{
		$tagId = (int) $this->getState('tag.id');

		$db       = $this->getDbo();
		$nullDate = $db->getNullDate();
		$nowDate  = JFactory::getDate()->toSql();

		$user      = JFactory::getUser();
		$levels    = $user->getAuthorisedViewLevels();
		$levelsStr = implode(',', $levels);

		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__judownload_tags');
		$query->where('id =' . $tagId);
		$query->where('published = 1');
		$query->where('(publish_up = ' . $db->quote($nullDate) . ' OR publish_up <= ' . $db->quote($nowDate) . ')');
		$query->where('(publish_down = ' . $db->quote($nullDate) . ' OR publish_down >= ' . $db->quote($nowDate) . ')');
		
		$query->where('access IN (' . $levelsStr . ')');

		$db->setQuery($query);

		return $db->loadObject();
	}

	
	protected function getListQuery()
	{
		$tagId     = (int) $this->getState('tag.id');
		$ordering  = $this->getState('list.ordering', '');
		$direction = $this->getState('list.direction', 'ASC');

		$user      = JFactory::getUser();
		$levels    = $user->getAuthorisedViewLevels();
		$levelsStr = implode(',', $levels);

		$db       = JFactory::getDbo();
		$nullDate = $db->getNullDate();
		$nowDate  = JFactory::getDate()->toSql();

		
		$query = $db->getQuery(true);
		$query->select('d.*');
		$query->from('#__judownload_documents AS d');

		JUDownloadFrontHelper::optimizeListDocumentQuery($query);

		
		$query->join('', '#__judownload_tags_xref AS txref ON txref.doc_id = d.id');

		
		$query->where('d.approved = 1');

		
		$query->where('d.published = 1');
		$query->where('(d.publish_up = ' . $db->quote($nullDate) . ' OR d.publish_up <= ' . $db->quote($nowDate) . ')');
		$query->where('(d.publish_down = ' . $db->quote($nullDate) . ' OR d.publish_down >= ' . $db->quote($nowDate) . ')');

		
		if ($user->get('guest'))
		{
			$query->where('d.access IN (' . $levelsStr . ')');
		}
		else
		{
			$query->where('(d.access IN (' . $levelsStr . ') OR (d.created_by = ' . $user->id . '))');
		}

		
		$query->where('txref.tag_id=' . $tagId);

		
		$app         = JFactory::getApplication();
		$languageTag = JFactory::getLanguage()->getTag();
		if ($app->getLanguageFilter())
		{
			$query->where('d.language IN (' . $db->quote($languageTag) . ',' . $db->quote('*') . ',' . $db->quote('') . ')');
		}

		JUDownloadFrontHelperField::appendFieldOrderingPriority($query, null, $ordering, $direction);

		return $query;
	}

	
	public function getItems()
	{
		$params             = $this->getState('params');
		$documentObjectList = parent::getItems();

		JUDownloadFrontHelper::appendDataToDocumentObjList($documentObjectList, $params);

		return $documentObjectList;
	}

	
	public function getStart()
	{
		return $this->getState('list.start');
	}
}