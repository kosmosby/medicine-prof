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

class JUDownloadFrontHelperMail
{

	// Property store cache
	protected static $cache = array();

	// This is static variable to save state of send email
	public static $sendMailError;

	// This is static variable to save state of send email
	public static $sendMailReportMessage;

	/*
	 * @param      $event
	 * @param null $data
	 *
	 * @return bool
	 */
	public static function sendEmailByEvent($event, $itemId = null, $data = array())
	{
		return true;
	}

	/**
	 * Send all mailqs in database
	 *
	 * @return bool
	 */
	public static function sendMailq($limit = null, $report = false)
	{
		return true;
	}
}
