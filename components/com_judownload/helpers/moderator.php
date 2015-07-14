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

class JUDownloadFrontHelperModerator
{

	// Property store cache
	protected static $cache = array();

	/*
	 * Method check current user is moderator.
	 *
	 * @return boolean
	 */
	public static function isModerator()
	{
		return false;
	}

	/*
	 * Get moderator object of $modId on $catId
	 * If not specify $modId -> check current user
	 * (!)Notice: we don't need to JOIN user table, because invalid user can not login
	 */
	public static function getModerator($catId, $modId = null)
	{
		return null;
	}

	/*
	 * Get moderator object by $modId
	 */
	public static function getModeratorById($modId, $checkPublished = false)
	{
		return null;
	}

	/*
	 * Check if moderator perform any task with document
	 *
	 * @param int    $mainCategoryId
	 * @param string $task
	 *
	 * @return boolean
	 */
	public static function checkModeratorCanDoWithDocument($mainCategoryId, $task)
	{
		return false;
	}


	/*
	 * Method check right when moderator perform any task with comment
	 *
	 * @param int    $commentId
	 * @param string $task
	 *
	 * @return boolean
	 */
	public static function checkModeratorCanDoWithComment($commentId, $task)
	{
		return false;
	}
}