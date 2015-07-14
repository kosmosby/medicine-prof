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


class JUDownloadModelDashboard extends JModelList
{
	
	protected function getListQuery()
	{
		return true;
	}

	

	public function getLastCreatedComments()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('cm.*');
		$query->from('#__judownload_comments AS cm');
		$query->select('d.title AS document_title');
		$query->join('LEFT', '#__judownload_documents AS d ON(d.id = cm.doc_id)');
		$query->select('ua.name AS created_by_name');
		$query->join('LEFT', '#__users AS ua ON(ua.id = cm.user_id)');
		$query->select('ua1.name AS checked_out_name');
		$query->join('LEFT', '#__users AS ua1 ON(ua1.id = cm.checked_out)');
		$query->where('cm.parent_id != 0 AND cm.level != 0');
		$db->setQuery($query, 0, 5);
		$data = $db->loadObjectList();

		return $data;
	}

	public function getLastDownloadedDocuments()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select("log.date AS download_date, log.reference AS log_reference");
		$query->from('#__judownload_logs AS log');
		$query->select('ua.name AS download_by_name');
		$query->join('LEFT', '#__users AS ua ON ua.id = log.user_id');
		$query->select("d.id AS document_id, d.title AS document_title, d.checked_out, d.checked_out_time, d.checked_out_time, ua1.name AS checked_out_name");
		$query->join('LEFT', '#__judownload_documents AS d ON d.id = log.item_id');
		$query->join('LEFT', '#__users AS ua1 ON ua1.id = d.checked_out');
		$query->where('log.event = "document.download"');
		$query->order('log.date');
		$db->setQuery($query, 0, 5);

		$data = $db->loadObjectList();

		return $data;
	}

	public function getCategories($doc_id, $link = true)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		if ($link)
		{
			$query->select("c.title, c.id");
		}
		else
		{
			$query->select("c.title");
		}

		$query->from("#__judownload_categories AS c");
		$query->join("", "#__judownload_documents_xref AS dxref ON dxref.cat_id = c.id");
		$query->join("", "#__judownload_documents AS d ON dxref.doc_id = d.id");
		$query->where("d.id = $doc_id");
		$query->order("dxref.main DESC, c.title ASC");
		$db->setQuery($query);
		$result = array();
		if ($link)
		{
			$categories = $db->loadObjectList();
			foreach ($categories AS $category)
			{
				$href     = "index.php?option=com_judownload&view=listcats&cat_id=" . $category->id;
				$result[] = "<a href=\"$href\">" . $category->title . "</a>";
			}
		}
		else
		{
			$result = $db->loadColumn();
		}

		return implode(", ", $result);
	}

	public function getDocuments($type, $limit = 5)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->SELECT("d.id, d.title, d.created, d.modified, d.updated, d.version, d.hits, d.downloads, d.approved, d.published, d.checked_out, d.checked_out_time");
		$query->FROM("#__judownload_documents AS d");
		$query->SELECT("ua1.name AS checked_out_name");
		$query->JOIN("LEFT", "#__users AS ua1 ON ua1.id = d.checked_out");
		switch ($type)
		{
			case "lastCreatedDocuments" :
				$query->SELECT("ua.name AS created_by_name");
				$query->JOIN("LEFT", "#__users AS ua ON ua.id = d.created_by");
				$query->ORDER("d.created DESC LIMIT 0, $limit");
				break;

			case "lastUpdatedDocuments" :
				$query->SELECT("ua.name AS modified_by_name");
				$query->JOIN("LEFT", "#__users AS ua ON ua.id = d.modified_by");
				$query->WHERE("d.updated > '0000-00-00 00:00:00'");
				$query->ORDER("d.updated DESC LIMIT 0, $limit");
				break;

			case "topDownloadDocuments" :
				$query->WHERE("d.downloads > 0");
				$query->ORDER("d.downloads DESC LIMIT 0, $limit");
				break;

			case "popularDocuments" :
				$query->WHERE("d.hits > 0");
				$query->ORDER("d.hits DESC LIMIT 0, $limit");
				break;
		}
		$db->setQuery($query);
		$data = $db->loadObjectList();

		return $data;
	}

	public function getStatistics()
	{
		$db     = $this->getDbo();
		$static = array();

		$query = $db->getQuery(true);
		$query->SELECT("COUNT(*) AS total_categories");
		$query->FROM("#__judownload_categories");
		$db->setQuery($query);
		$total                = $db->loadResult();
		$static['Categories'] = $total;

		$query = $db->getQuery(true);
		$query->SELECT("COUNT(*) AS total_documents");
		$query->FROM("#__judownload_documents");
		$db->setQuery($query);
		$total               = $db->loadResult();
		$static['Documents'] = $total;

		$query = $db->getQuery(true);
		$query->SELECT("COUNT(*) AS total_tags");
		$query->FROM("#__judownload_tags");
		$db->setQuery($query);
		$total          = $db->loadResult();
		$static['Tags'] = $total;

		$query = $db->getQuery(true);
		$query->SELECT("COUNT(*) AS total_comments");
		$query->FROM("#__judownload_comments");
		$query->where('parent_id != 0 AND level != 0');
		$db->setQuery($query);
		$total              = $db->loadResult();
		$static['Comments'] = $total;

		$query = $db->getQuery(true);
		$query->SELECT("COUNT(*) AS total_collections");
		$query->FROM("#__judownload_collections");
		$db->setQuery($query);
		$total                 = $db->loadResult();
		$static['Collections'] = $total;

		return $static;
	}

	public function getTotalUnreadReports()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->SELECT("COUNT(*)");
		$query->FROM("#__judownload_reports");
		$query->WHERE("`read` != 1");
		$db->setQuery($query);

		return $db->loadResult();
	}

	public function getTotalMailqs()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->SELECT("COUNT(*)");
		$query->FROM("#__judownload_mailqs AS mq");
		$query->JOIN('', '#__judownload_emails AS m ON (mq.email_id = m.id)');
		$db->setQuery($query);

		return $db->loadResult();
	}
}
