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


jimport('joomla.database.tablenested');

class JUDownloadTableComment extends JTableNested
{
	public static $getPath;
	public static $getTree;

	protected $_comment_interval;
	protected $_comment_interval_same_document;
	protected $_comment_latest;
	protected $_comment_latest_same_document;

	
	public function __construct(&$db)
	{
		$this->_comment_interval               = 0;
		$this->_comment_interval_same_document = 0;
		$this->_comment_latest                 = 0;
		$this->_comment_latest_same_document   = 0;
		parent::__construct('#__judownload_comments', 'id', $db);
	}

	
	public function getPath($pk = null, $diagnostic = false)
	{
		$k  = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		if (!isset(self::$getPath[$pk][(int) $diagnostic]))
		{
			self::$getPath[$pk][(int) $diagnostic] = parent::getPath($pk, $diagnostic);
		}

		return self::$getPath[$pk][(int) $diagnostic];
	}

	
	public function getTree($pk = null, $diagnostic = false)
	{
		$k  = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		if (!isset(self::$getTree[$pk][(int) $diagnostic]))
		{
			self::$getTree[$pk][(int) $diagnostic] = parent::getTree($pk, $diagnostic);
		}

		return self::$getTree[$pk][(int) $diagnostic];
	}

	
	public function rebuild($parentId = null, $leftId = 0, $level = 0, $path = '')
	{
		
		if ($parentId === null)
		{
			
			$parentId = $this->getRootId();
			if ($parentId === false)
			{
				return false;
			}
		}

		
		if (!isset($this->_cache['rebuild.sql']))
		{
			$query = $this->_db->getQuery(true);
			$query->select($this->_tbl_key);
			$query->from($this->_tbl);
			$query->where('parent_id = %d');

			
			if (property_exists($this, 'ordering'))
			{
				$query->order('parent_id, ordering, lft');
			}
			else
			{
				$query->order('parent_id, lft');
			}
			$this->_cache['rebuild.sql'] = (string) $query;
		}

		

		
		$this->_db->setQuery(sprintf($this->_cache['rebuild.sql'], (int) $parentId));
		$children = $this->_db->loadObjectList();

		
		$rightId = $leftId + 1;

		
		foreach ($children AS $node)
		{
			
			
			
			$rightId = $this->rebuild($node->{$this->_tbl_key}, $rightId, $level + 1);

			
			if ($rightId === false)
			{
				return false;
			}
		}

		
		
		$query = $this->_db->getQuery(true);
		$query->update($this->_tbl);
		$query->set('lft = ' . (int) $leftId);
		$query->set('rgt = ' . (int) $rightId);
		$query->set('level = ' . (int) $level);
		$query->where($this->_tbl_key . ' = ' . (int) $parentId);
		$this->_db->setQuery($query);

		
		if (!$this->_db->execute())
		{
			$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_REBUILD_FAILED', get_class($this), $this->_db->getErrorMsg()));
			$this->setError($e);

			return false;
		}

		
		return $rightId + 1;
	}


	
	public function check()
	{
		$app = JFactory::getApplication();
		if ($app->isSite())
		{
			$params                  = JUDownloadHelper::getParams(null, $this->doc_id);
			$this->_comment_interval = $params->get('comment_interval', 60);
			if ($this->_comment_interval > 0)
			{
				$this->_comment_latest = strtotime($this->getLatestCommentTime());
			}
			$this->_comment_interval_same_document = $params->get('comment_interval_in_same_document', 60);
			if ($this->_comment_interval_same_document > 0)
			{
				$this->_comment_latest_same_document = strtotime($this->getLatestCommentTime($this->doc_id));
			}
		}

		if (!parent::check())
		{
			$this->setError(JText::_('COM_JUDOWNLOAD_COMMENT_FAILED'));

			return false;
		}

		return true;
	}

	
	public function store($updateNulls = false)
	{
		$app = JFactory::getApplication();
		if ($app->isSite())
		{
			if ($this->_comment_latest)
			{
				$timeNow = strtotime($this->created);
				$waiting = ($this->_comment_latest + $this->_comment_interval) - $timeNow;
				if ($waiting > 0)
				{
					$this->setError($this->getErrorForIntervalComment($waiting));

					return false;
				}
			}

			if ($this->_comment_latest_same_document > 0)
			{
				$timeNow = strtotime($this->created);
				$waiting = ($this->_comment_latest_same_document + $this->_comment_interval_same_document) - $timeNow;
				if ($waiting > 0)
				{
					$this->setError($this->getErrorForIntervalComment($waiting));

					return false;
				}
			}
		}

		if (!parent::store($updateNulls))
		{
			$this->setError(JText::_('COM_JUDOWNLOAD_COMMENT_FAILED'));

			return false;
		}

		return true;
	}

	public function getErrorForIntervalComment($waiting)
	{
		$date    = new JDate($waiting);
		$waiting = $date->format('d H i s');

		$timeArr = explode(' ', $waiting);

		$time_str = '';
		if ($timeArr[0] - 1)
		{
			$time_str .= " " . JText::plural('COM_JUDOWNLOAD_TIME_N_DAY', $timeArr[0] - 1);
		}

		if ($timeArr[1])
		{
			$time_str .= " " . JText::plural('COM_JUDOWNLOAD_TIME_N_HOUR', $timeArr[1]);
		}

		if ($timeArr[2])
		{
			$time_str .= " " . JText::plural('COM_JUDOWNLOAD_TIME_N_MINUTE', $timeArr[2]);
		}

		if ($timeArr[3])
		{
			$time_str .= " " . JText::plural('COM_JUDOWNLOAD_TIME_N_SECOND', $timeArr[3]);
		}

		$error = JText::sprintf('COM_JUDOWNLOAD_YOU_HAVE_TO_WAIT_TIME_BEFORE_SUBMIT_NEW_COMMENT', $time_str);

		return $error;
	}

	public function getLatestCommentTime($docId = 0)
	{

		$user  = JFactory::getUser();
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('created')
			->from('#__judownload_comments')
			->order('id DESC');
		if ($docId > 0)
		{
			$query->where('doc_id = ' . $docId);
		}
		if ($user->id > 0)
		{
			$query->where('user_id =' . $user->id);
		}
		else
		{
			$ipAddress = JUDownloadFrontHelper::getIpAddress();
			$query->where('ip_address = "' . $ipAddress . '"');
		}

		$db->setQuery($query, 0, 1);
		$result = $db->loadResult();

		return $result;
	}


	
	protected function _delete($comment)
	{
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_judownload/tables');
		$db = $this->_db;
		
		$query = "DELETE FROM #__judownload_subscriptions WHERE item_id = " . $comment->id . " AND type = 'comment'";
		$db->setQuery($query);
		$db->execute();

		
		$query = "DELETE FROM #__judownload_reports WHERE item_id = " . $comment->id . " AND type = 'comment'";
		$db->setQuery($query);
		$db->execute();

		
		$query = "SELECT r.id FROM #__judownload_rating AS r JOIN #__judownload_comments AS cm ON r.id = cm.rating_id WHERE cm.id = " . $comment->id;
		$db->setQuery($query);
		$ratingIds = $db->loadColumn();
		if ($ratingIds)
		{
			$ratingTable = JTable::getInstance("Rating", "JUDownloadTable");
			foreach ($ratingIds AS $ratingId)
			{
				$ratingTable->delete($ratingId);
			}
		}

		
		if ($comment->parent_id == 0 && $comment->approved == 1)
		{
			$query = "SELECT SUM(score) AS total_ratings, COUNT(*) AS total_votes FROM #__judownload_rating AS rating LEFT JOIN #__judownload_comments AS comment ON (comment.rating_id = rating.id AND comment.approved = 1) WHERE rating.doc_id = " . $comment->doc_id;
			$db->setQuery($query);
			$score          = $db->loadObject();
			$document_table = JTable::getInstance("Document", "JUDownloadTable");
			if ($document_table->load($comment->doc_id))
			{
				if ($score->total_votes > 0)
				{
					$document_table->rating = (float) $score->total_ratings / $score->total_votes;
				}
				else
				{
					$document_table->rating = 0;
				}

				$document_table->total_votes = $score->total_votes;
				$document_table->store();
			}
		}

		
		$query = "DELETE FROM #__judownload_mailqs
			      WHERE item_id =" . $comment->id . "
						AND email_id IN (SELECT id FROM #__judownload_emails WHERE (`event` LIKE 'comment.%' and `event` != 'comment.delete'))";
		$db->setQuery($query);
		$db->execute();

		
		JUDownloadHelper::deleteLogs('comment', $comment->id);

		return true;
	}

	
	public function delete($pk = null, $children = true)
	{
		
		$k  = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		
		if ($this->_trackAssets)
		{
			$name  = $this->_getAssetName();
			$asset = JTable::getInstance('Asset');

			
			if (!$asset->_lock())
			{
				
				return false;
			}

			if ($asset->loadByName($name))
			{
				
				if (!$asset->delete(null, $children))
				{
					$this->setError($asset->getError());
					$asset->_unlock();

					return false;
				}
				$asset->_unlock();
			}
			else
			{
				$this->setError($asset->getError());
				$asset->_unlock();

				return false;
			}
		}

		
		if (!$node = $this->_getNode($pk))
		{
			
			$this->_unlock();

			return false;
		}

		$commentObjList = array();
		
		if ($children)
		{
			$tree = $this->getTree($pk);
			foreach ($tree AS $comment)
			{
				$this->_delete($comment);
				$commentObjList[] = JUDownloadFrontHelperComment::getCommentObject($comment->id);
			}
			
			if (!$this->_lock())
			{
				
				return false;
			}
			
			$query = $this->_db->getQuery(true);
			$query->delete();
			$query->from($this->_tbl);
			$query->where('lft BETWEEN ' . (int) $node->lft . ' AND ' . (int) $node->rgt);
			$this->_runQuery($query, 'JLIB_DATABASE_ERROR_DELETE_FAILED');

			
			$query = $this->_db->getQuery(true);
			$query->update($this->_tbl);
			$query->set('lft = lft - ' . (int) $node->width);
			$query->where('lft > ' . (int) $node->rgt);
			$this->_runQuery($query, 'JLIB_DATABASE_ERROR_DELETE_FAILED');

			
			$query = $this->_db->getQuery(true);
			$query->update($this->_tbl);
			$query->set('rgt = rgt - ' . (int) $node->width);
			$query->where('rgt > ' . (int) $node->rgt);
			$this->_runQuery($query, 'JLIB_DATABASE_ERROR_DELETE_FAILED');

		}
		
		else
		{
			$this->_delete($node);
			
			if (!$this->_lock())
			{
				
				return false;
			}

			
			$query = $this->_db->getQuery(true);
			$query->delete();
			$query->from($this->_tbl);
			$query->where('lft = ' . (int) $node->lft);
			$this->_runQuery($query, 'JLIB_DATABASE_ERROR_DELETE_FAILED');

			
			$query = $this->_db->getQuery(true);
			$query->update($this->_tbl);
			$query->set('lft = lft - 1');
			$query->set('rgt = rgt - 1');
			$query->set('level = level - 1');
			$query->where('lft BETWEEN ' . (int) $node->lft . ' AND ' . (int) $node->rgt);
			$this->_runQuery($query, 'JLIB_DATABASE_ERROR_DELETE_FAILED');

			
			$query = $this->_db->getQuery(true);
			$query->update($this->_tbl);
			$query->set('parent_id = ' . (int) $node->parent_id);
			$query->where('parent_id = ' . (int) $node->$k);
			$this->_runQuery($query, 'JLIB_DATABASE_ERROR_DELETE_FAILED');

			
			$query = $this->_db->getQuery(true);
			$query->update($this->_tbl);
			$query->set('lft = lft - 2');
			$query->where('lft > ' . (int) $node->rgt);
			$this->_runQuery($query, 'JLIB_DATABASE_ERROR_DELETE_FAILED');

			
			$query = $this->_db->getQuery(true);
			$query->update($this->_tbl);
			$query->set('rgt = rgt - 2');
			$query->where('rgt > ' . (int) $node->rgt);
			$this->_runQuery($query, 'JLIB_DATABASE_ERROR_DELETE_FAILED');

		}

		
		$this->_unlock();

		if ($children && !empty($commentObjList))
		{
			
			foreach ($commentObjList AS $commentObj)
			{
				
				if ($commentObj->approved == 0)
				{
					JUDownloadFrontHelperMail::sendEmailByEvent('comment.reject', $commentObj->id, get_object_vars($commentObj));
				}
				
				elseif (JUDownloadFrontHelperModerator::isModerator())
				{
					JUDownloadFrontHelperMail::sendEmailByEvent('comment.moddelete', $commentObj->id, get_object_vars($commentObj));
				}
				
				else
				{
					JUDownloadFrontHelperMail::sendEmailByEvent('comment.userdelete', $commentObj->id, get_object_vars($commentObj));
				}
			}
		}
		else
		{
			$commentObj = JUDownloadFrontHelperComment::getCommentObject($node->id);
			
			
			if ($commentObj->approved == 0)
			{
				JUDownloadFrontHelperMail::sendEmailByEvent('comment.reject', $commentObj->id, get_object_vars($commentObj));
			}
			
			elseif (JUDownloadFrontHelperModerator::isModerator())
			{
				JUDownloadFrontHelperMail::sendEmailByEvent('comment.moddelete', $commentObj->id, get_object_vars($commentObj));
			}
			
			else
			{
				JUDownloadFrontHelperMail::sendEmailByEvent('comment.userdelete', $commentObj->id, get_object_vars($commentObj));
			}
		}

		return true;
	}
}
