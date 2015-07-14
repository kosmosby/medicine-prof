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

class JUDownloadFrontHelperRating
{
	
	protected static $cache = array();

	
	public static function getTotalDocumentVotesOfUser($userId, $docId)
	{
		if (!$userId)
		{
			return 0;
		}
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(*)');
		$query->from('#__judownload_rating');
		$query->where('user_id = ' . $userId);
		$query->where('doc_id = ' . $docId);
		$db->setQuery($query);
		$totalRates = $db->loadResult();

		return $totalRates;
	}

	
	public static function getLastTimeVoteDocumentOfUser($userId, $documentId)
	{
		if (!$userId)
		{
			return 0;
		}
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('MAX(created)');
		$query->from('#__judownload_rating');
		$query->where('doc_id =' . $documentId);
		$query->where('user_id = ' . $userId);
		$db->setQuery($query);

		return $db->loadResult();
	}

	
	public static function calculateRatingScore($ratingData, $doc_id, $criteriaArray)
	{
		if (count($criteriaArray) > 0)
		{
			$totalScore   = 0;
			$totalWeights = 0;
			foreach ($criteriaArray AS $criteria)
			{
				$totalScore += $criteria->value * $criteria->weights;
				$totalWeights += $criteria->weights;
			}
			$voteScore = $totalScore / $totalWeights;
		}
		else
		{
			$voteScore = $ratingData['ratingValue'];
		}

		
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('judownload');
		$onCalculateRating = $dispatcher->trigger('onCalculateRating', array($ratingData, $doc_id, $criteriaArray));
		if (count($onCalculateRating))
		{
			
			$voteScore = $onCalculateRating[0];
		}

		return $voteScore;
	}

}