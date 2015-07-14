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

class JUDownloadFieldCore_comments extends JUDownloadFieldText
{
	protected $field_name = 'comments';

	protected function getValue()
	{

		$app = JFactory::getApplication();
		
		if ($app->isSite() && isset($this->doc->total_comments) && !is_null($this->doc->total_comments))
		{
			return $this->doc->total_comments;
		}

		$user = JFactory::getUser();
		$db   = JFactory::getDbo();
		if ($app->isSite())
		{
			$query = $db->getQuery(true);
			$query->select('COUNT(*)');
			$query->from('#__judownload_comments AS cm');
			$query->where('doc_id =' . $this->doc_id);
			$query->where('level = 1');
			$query->where('approved = 1');

			$moderator = JUDownloadFrontHelperModerator::getModerator($this->doc->cat_id);
			$getAll    = false;
			if ($user->authorise('core.admin', 'com_judownload'))
			{
				$getAll = true;
			}

			if (is_object($moderator))
			{
				if ($moderator->comment_edit || $moderator->comment_edit_state || $moderator->comment_delete)
				{
					$getAll = true;
				}
			}

			if (!$getAll)
			{
				$query->where('published = 1');
				$params                = JUDownloadHelper::getParams(null, $this->doc_id);
				$negative_vote_comment = $params->get('negative_vote_comment');
				if (is_numeric($negative_vote_comment) && $negative_vote_comment > 0)
				{
					$query->where('(total_votes - helpful_votes) <' . $negative_vote_comment);
				}
			}
		}
		else
		{
			$query = $db->getQuery(true);
			$query->select('COUNT(*)');
			$query->from('#__judownload_comments AS cm');
			$query->where('doc_id =' . $this->doc_id);
			$query->where('level = 1');
			$query->where('approved = 1');
		}
		$db->setQuery($query);
		$totalComments = $db->loadResult();

		return $totalComments;
	}

	
	public function storeValue($value, $type = 'default', $inputData = null)
	{
		return true;
	}

	public function getPredefinedValuesHtml()
	{
		return '<span class="readonly">' . JText::_('COM_JUDOWNLOAD_NOT_SET') . '</span>';
	}

	public function getBackendOutput()
	{
		$value = $this->value;

		return '<span class="comments"><a href="index.php?option=com_judownload&view=comments&doc_id=' . $this->doc_id . '" title="' . JText::_('COM_JUDOWNLOAD_VIEW_COMMENTS') . '">' . JText::plural('COM_JUDOWNLOAD_N_COMMENT', $value) . '</a></span>';
	}

	public function onSimpleSearch(&$query, &$where, $search)
	{
		if ($search !== "")
		{
			$app       = JFactory::getApplication();
			$where_str = $app->isSite() ? ' AND cm.published = 1' : '';
			$where[]   = "(SELECT COUNT(*) FROM #__judownload_comments AS cm (cm.doc_id = d.id AND cm.approved = 1$where_str)) = " . (int) $search;
		}
	}

	public function onSearch(&$query, &$where, $search)
	{
		if (is_array($search) && !empty($search))
		{
			$app       = JFactory::getApplication();
			$where_str = $app->isSite() ? ' AND cm.published = 1' : '';
			if ($search['from'] !== "" && $search['to'] !== "")
			{
				$from = (int) $search['from'];
				$to   = (int) $search['to'];
				if ($from > $to)
				{
					$this->swap($from, $to);
				}

				$where[] = "(SELECT COUNT(*) FROM #__judownload_comments AS cm WHERE (cm.doc_id = d.id AND cm.approved = 1$where_str)) BETWEEN $from AND $to";
			}
			elseif ($search['from'] !== "")
			{
				$from = (int) $search['from'];

				$where[] = "(SELECT COUNT(*) FROM #__judownload_comments AS cm WHERE (cm.doc_id = d.id AND cm.approved = 1$where_str)) >= $from";
			}
			elseif ($search['to'] !== "")
			{
				$to = (int) $search['to'];

				$where[] = "(SELECT COUNT(*) FROM #__judownload_comments AS cm WHERE (cm.doc_id = d.id AND cm.approved = 1$where_str)) <= $to";
			}
		}
		else
		{
			$this->onSimpleSearch($query, $where, $search);
		}
	}

	public function orderingPriority(&$query = null)
	{

		$app       = JFactory::getApplication();
		$where_str = $app->isSite() ? ' AND cm.published = 1' : '';
		$this->appendQuery($query, 'select', '(SELECT COUNT(*) FROM #__judownload_comments AS cm WHERE (cm.doc_id = d.id AND cm.approved = 1' . $where_str . ')) AS comments');

		return array('ordering' => 'comments', 'direction' => $this->priority_direction);
	}
}

?>