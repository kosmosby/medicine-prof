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

class JUDownloadSearchHelper
{
	
	public static function getDocumentsSearch($searchword, $state, $cat_id = null)
	{
		
		$searchword = trim($searchword);

		$app        = JFactory::getApplication();
		$user       = JFactory::getUser();
		$levels     = $user->getAuthorisedViewLevels();
		$levels_str = implode(',', $levels);
		$db         = JFactory::getDbo();
		$nullDate   = $db->getNullDate();
		$nowDate    = JFactory::getDate()->toSql();
		$query      = $db->getQuery(true);

		$listOrder = $state->get('list.ordering');
		$listDirn  = $state->get('list.direction');
		
		if ($app->isAdmin())
		{
			$query->SELECT('d.id, d.title, d.alias, d.published, d.publish_up, d.publish_down, d.checked_out, d.checked_out_time, d.featured, d.access, d.created_by, d.downloads, d.language');

			$query->SELECT('c.id AS category_id, c.title AS category_title');

			$where_str = $app->isSite() ? ' AND cm.published = 1' : '';
			$query->SELECT('(SELECT COUNT(*) FROM #__judownload_comments AS cm WHERE (cm.doc_id = d.id AND cm.approved = 1' . $where_str . ')) AS comments');

			$query->SELECT('(SELECT COUNT(*) FROM #__judownload_reports AS r WHERE r.item_id = d.id AND r.type="document") AS reports');

			$where_str = $app->isSite() ? ' AND s.published = 1' : '';
			$query->SELECT('(SELECT COUNT(*) FROM #__judownload_subscriptions AS s WHERE s.item_id = d.id AND s.type="document"' . $where_str . ') AS subscriptions');

			$query->SELECT('ua.name AS created_by_name');
			$query->JOIN("LEFT", "#__users AS ua ON d.created_by = ua.id");

			$query->SELECT('ua3.name AS checked_out_name');
			$query->JOIN("LEFT", "#__users AS ua3 ON d.checked_out = ua3.id");

			$query->SELECT("vl.title AS access_title");
			$query->JOIN("LEFT", "#__viewlevels AS vl ON d.access = vl.id");
		}
		
		else
		{
			$query->SELECT('d.*');
			if ($app->isSite())
			{
				JUDownloadFrontHelper::optimizeListDocumentQuery($query);
			}
		}

		$query->FROM('#__judownload_documents AS d');
		$query->JOIN('', '#__judownload_documents_xref AS dxref ON d.id = dxref.doc_id');
		$query->JOIN('', '#__judownload_categories AS c ON (c.id = dxref.cat_id)');

		
		$field_query = $db->getQuery(true);
		
		$field_query->select("field.*");
		$field_query->from("#__judownload_fields AS field");
		$field_query->select("plg.folder");
		$field_query->join("", "#__judownload_plugins AS plg ON field.plugin_id = plg.id");
		$field_query->join("", "#__judownload_fields_groups AS field_group ON field_group.id = field.group_id");
		$field_query->join("", "#__judownload_categories AS c2 ON (c2.fieldgroup_id = field_group.id OR field.group_id = 1)");
		if ($app->isSite())
		{
			if (is_array($cat_id))
			{
				$cat_id = (array) $cat_id;
				$field_query->where('(c2.id IN (' . implode(",", $cat_id) . ') OR field.group_id = 1)');
			}
			elseif ($cat_id)
			{
				$cat_id = (int) $cat_id;
				$field_query->where('(c2.id = ' . $cat_id . ' OR field.group_id = 1)');
			}
			$field_query->where('c2.published = 1');
			$field_query->where('c2.publish_up <= ' . $db->quote($nowDate));
			$field_query->where('(c2.publish_down = ' . $db->quote($nullDate) . ' OR c2.publish_down > ' . $db->quote($nowDate) . ')');
		}
		
		$field_query->where('field.simple_search = 1');
		$field_query->where('field.published = 1');
		$field_query->where('field.publish_up <= ' . $db->quote($nowDate));
		$field_query->where('(field.publish_down = ' . $db->quote($nullDate) . ' OR field.publish_down > ' . $db->quote($nowDate) . ')');
		$field_query->where('(field.field_name != "cat_id"');
		$field_query->where("field_group.published = 1)");
		$field_query->group('field.id');
		$db->setQuery($field_query);
		$fields = $db->loadObjectList();
		$where  = array();
		if ($fields)
		{
			foreach ($fields AS $field)
			{
				if ($searchword)
				{
					$fieldClass = JUDownloadFrontHelperField::getField($field);
					$fieldClass->onSimpleSearch($query, $where, $searchword);
				}
			}

			if (!empty($where))
			{
				$query->WHERE("(" . implode(" OR ", $where) . ")");
			}
		}

		$app = JFactory::getApplication();
		if ($app->isAdmin())
		{
			$published = $state->get('filter.state');
			if (is_numeric($published))
			{
				$query->WHERE('d.published = ' . (int) $published);
			}

			$cat_id = $state->get('filter.category');
			if (is_numeric($cat_id))
			{
				$query->WHERE('dxref.cat_id = ' . (int) $cat_id);
			}

			$access_level = $state->get('filter.access');
			if (is_numeric($access_level) && ($access_level != 0))
			{
				$query->WHERE('d.access = ' . (int) $access_level);
			}

			
			$language = $state->get('filter.language');
			if ($language)
			{
				$query->where('d.language = ' . $db->quote($language));
			}
		}
		else
		{
			$query->where('d.published = 1');
			if ($cat_id)
			{
				if (is_array($cat_id))
				{
					$cat_id = (array) $cat_id;
					$query->where('c.id IN (' . implode(",", $cat_id) . ')');
				}
				elseif ($cat_id)
				{
					$cat_id = (int) $cat_id;
					$query->where('c.id = ' . $cat_id);
				}
			}

			$query->where('d.publish_up <= ' . $db->quote($nowDate));
			$query->where('(d.publish_down = ' . $db->quote($nullDate) . ' OR d.publish_down > ' . $db->quote($nowDate) . ')');

			
			if ($user->get('guest'))
			{
				$query->where('d.access IN (' . $levels_str . ')');
			}
			else
			{
				$query->where('(d.access IN (' . $levels_str . ') OR (d.created_by = ' . $user->id . '))');
			}
		}

		$query->where('d.approved = 1');

		JUDownloadFrontHelperField::appendFieldOrderingPriority($query, null, $listOrder, $listDirn);

		
		$query->group('d.id');

		return $query;
	}

	
	}

