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


jimport('joomla.application.component.modeladmin');

if (!class_exists('JUDownloadModelComment'))
{
	require_once JPATH_ADMINISTRATOR . "/components/com_judownload/models/comment.php";
}

class JUDownloadModelPendingComment extends JUDownloadModelComment
{
	
	function approve($comment_ids)
	{
		if (!is_array($comment_ids) || empty($comment_ids))
		{
			$this->setError('COM_JUDOWNLOAD_NO_ITEM_SELECTED');

			return false;
		}

		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_judownload/tables');
		$comment_table = JTable::getInstance("Comment", "JUDownloadTable");
		$count         = 0;
		$comment_ids   = (array) $comment_ids;
		$rootComment   = JUDownloadFrontHelperComment::getRootComment();
		$docIds        = array();
		foreach ($comment_ids AS $comment_id)
		{
			$comment_table->reset();
			
			if ($comment_table->load($comment_id) && $comment_table->parent_id == $rootComment->id && $comment_table->approved == 0)
			{
				$docIds[$comment_table->doc_id] = $comment_table->doc_id;
			}

			$user                         = JFactory::getUser();
			$date                         = JFactory::getDate();
			$comment_table->approved      = 1;
			$comment_table->published     = 1;
			$comment_table->approved_by   = $user->id;
			$comment_table->approved_time = $date->toSql();
			$comment_table->store();
			$count++;

			
			JUDownloadFrontHelperMail::sendEmailByEvent('comment.approve', $comment_id);

			
			$logData = array(
				'user_id'   => $comment_table->user_id,
				'event'     => 'comment.approve',
				'item_id'   => $comment_id,
				'doc_id'    => $comment_table->doc_id,
				'value'     => 0,
				'reference' => '',
			);

			JUDownloadFrontHelperLog::addLog($logData);
		}

		
		foreach ($docIds AS $docId)
		{
			JUDownloadHelper::rebuildRating($docId);
		}

		return $count;
	}

	
	public function save($data)
	{
		$app            = JFactory::getApplication();
		$comment_option = $app->input->get('approval_option');

		if (parent::save($data))
		{
			if ($comment_option == 'approve')
			{
				$this->approve(array($data['id']));
			}

			return true;
		}

		return false;
	}

	public function getPrevOrNextCommentId($type = 'next')
	{
		$app        = JFactory::getApplication();
		$comment_id = $app->input->getInt('id', 0);
		$db         = JFactory::getDbo();
		$query      = $db->getQuery(true);
		$query->select('id');
		$query->from('#__judownload_comments');
		$query->where('approved != 1');

		switch ($type)
		{
			case 'prev':
				$query->where('id < ' . $comment_id);
				$query->order('id DESC');
				break;

			case 'next':
			default:
				$query->where('id > ' . $comment_id);
				$query->order('id ASC');
				break;
		}

		$db->setQuery($query, 0, 1);
		$next_prev_comment = $db->loadResult();

		return $next_prev_comment;
	}

}
