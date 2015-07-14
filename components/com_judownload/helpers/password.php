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

class JUDownloadFrontHelperPassword
{
	
	protected static $cache = array();

	
	public static function allowEnterPassword($documentId)
	{
		$params                 = JUDownloadHelper::getParams(null, $documentId);
		$maxWrongPasswordTimes  = $params->get('max_wrong_password_times', 5);
		$blockEnterPasswordTime = $params->get('block_enter_password_time', 600);
		$session                = JFactory::getSession();
		$timeNow                = JFactory::getDate()->toSql();
		$timeNowStamp           = strtotime($timeNow);
		
		$ss_wrongPasswordTimes = 'judl-wrong-password-' . $documentId;
		
		$ss_blockDownloadTime = 'judl-block-download-time-' . $documentId;
		if ($session->has($ss_blockDownloadTime))
		{
			$waitTime = $timeNowStamp - $session->get($ss_blockDownloadTime);
		}
		else
		{
			$waitTime = 0;
		}

		if ($session->has($ss_wrongPasswordTimes))
		{
			$ss_wrongPasswordTimes = (int) $session->get($ss_wrongPasswordTimes);
		}
		else
		{
			$ss_wrongPasswordTimes = 0;
		}

		if ($ss_wrongPasswordTimes < $maxWrongPasswordTimes || $waitTime > $blockEnterPasswordTime)
		{
			return true;
		}

		return false;
	}

	
	public static function checkPassword($documentObject)
	{
		
		$isDocumentOwner = JUDownloadFrontHelperPermission::isDocumentOwner($documentObject->id);
		
		$isModerator = JUDownloadFrontHelperModerator::isModerator();

		if ($isDocumentOwner)
		{
			
			$asset = 'com_judownload.document.' . $documentObject->id;
			$user  = JFactory::getUser();
			if ($user->authorise('judl.document.download.own.no_restrict', $asset))
			{
				return true;
			}
		}

		if ($isModerator)
		{
			
			$mainCategory   = JUDownloadFrontHelperCategory::getMainCategory($documentObject->id);
			$modCanDownload = JUDownloadFrontHelperModerator::checkModeratorCanDoWithDocument($mainCategory->id, 'document_download');
			if ($modCanDownload)
			{
				return true;
			}

			
			if ($documentObject->approved < 1)
			{
				
				$modCanApproval = JUDownloadFrontHelperModerator::checkModeratorCanDoWithDocument($mainCategory->id, 'document_approve');
				if ($modCanApproval)
				{
					return true;
				}
			}
		}

		$session = JFactory::getSession();
		
		if ($session->get('judl-download-password-' . $documentObject->id, '') === $documentObject->download_password)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}