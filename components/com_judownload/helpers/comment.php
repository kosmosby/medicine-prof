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

class JUDownloadFrontHelperComment
{
	
	protected static $cache = array();

	
	public static function getRootComment()
	{
		$storeId = md5(__METHOD__);
		if (!isset(self::$cache[$storeId]))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from('#__judownload_comments');
			$query->where('parent_id = 0');
			$query->where('level = 0');
			$db->setQuery($query);
			self::$cache[$storeId] = $db->loadObject();
		}

		return self::$cache[$storeId];
	}

	
	public static function getCommentObject($commentId, $select = 'cm.*', $resetCache = false)
	{
		if (!$commentId)
		{
			return null;
		}

		
		if (strpos(",", $select) !== false)
		{
			$selectColumnArr = explode(",", $select);
			sort($selectColumnArr);
			$select = implode(",", $selectColumnArr);
		}

		$storeID = md5(__METHOD__ . "::" . $commentId . "::" . $select);
		if (!isset(self::$cache[$storeID]) || $resetCache)
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select($select);
			$query->select('r.score');
			$query->select('r.id AS rating_id');
			$query->from('#__judownload_comments AS cm');
			$query->join('LEFT', '#__judownload_rating AS r ON cm.rating_id = r.id');
			$query->where('cm.id = ' . $commentId);
			$db->setQuery($query);
			self::$cache[$storeID] = $db->loadObject();
		}

		return self::$cache[$storeID];
	}

	
	public static function parseCommentText($str, $docId = null)
	{
		$params                       = JUDownloadHelper::getParams(null, $docId);
		$auto_link_url_in_comment     = $params->get('auto_link_url_in_comment', 1);
		$trim_long_url_in_comment     = $params->get('trim_long_url_in_comment', 0);
		$front_portion_url_in_comment = $params->get('front_portion_url_in_comment', 0);
		$back_portion_url_in_comment  = $params->get('back_portion_url_in_comment', 0);
		$str                          = JUDownloadFrontHelperComment::autoLinkVideo($str, $docId);
		if ($auto_link_url_in_comment)
		{
			if ($params->get('nofollow_link_in_comment', 1))
			{
				$noFollow = 'rel="nofollow"';
			}
			else
			{
				$noFollow = '';
			}

			$regex = "#http(?:s)?:\/\/(?:www\.)?[\.0-9a-z]{1,255}(\.[a-z]{2,4}){1,2}([\/\?][^\s]{1,}){0,}[\/]?#i";
			preg_match_all($regex, $str, $matches);

			$matches = array_unique($matches[0]);

			if (count($matches) > 0)
			{
				foreach ($matches AS $url)
				{
					$shortenUrl = urldecode($url);
					
					if ($trim_long_url_in_comment > 0 && strlen($shortenUrl) > $trim_long_url_in_comment)
					{
						if ($front_portion_url_in_comment > 0 || $back_portion_url_in_comment > 0)
						{
							$frontStr   = $front_portion_url_in_comment > 0 ? substr($shortenUrl, 0, $front_portion_url_in_comment) : "";
							$backStr    = $back_portion_url_in_comment > 0 ? substr($shortenUrl, (int) (0 - $back_portion_url_in_comment)) : "";
							$shortenUrl = $frontStr . '...' . $backStr;
						}

						$shortenUrl = '<a ' . $noFollow . ' href="' . $url . '">' . $shortenUrl . '</a> ';
						$str        = str_replace(trim($url), $shortenUrl, $str);
						$str        = JUDownloadFrontHelperString::replaceIgnore(trim($url), $shortenUrl, $str);
					}
					
					else
					{
						$str = JUDownloadFrontHelperString::replaceIgnore($url, '<a ' . $noFollow . ' href="' . $url . '">' . trim($shortenUrl) . '</a> ', $str);
					}
				}
			}
		}

		
		$forbidden_words = array_map('trim', explode(",", strtolower(str_replace("\n", ",", $params->get('forbidden_words', '')))));
		if (trim($params->get('forbidden_words', '')) && count($forbidden_words))
		{
			$forbidden_words_replaced_by = $params->get('forbidden_words_replaced_by', '***');
			foreach ($forbidden_words AS $val)
			{
				$str = preg_replace('#' . $val . '#ism', $forbidden_words_replaced_by, $str);
			}
		}

		return $str;
	}

	
	protected static function autoLinkVideo($text, $docId = null)
	{
		$params                        = JUDownloadHelper::getParams(null, $docId);
		$auto_embed_youtube_in_comment = $params->get('auto_embed_youtube_in_comment', 0);
		$auto_embed_vimeo_in_comment   = $params->get('auto_embed_vimeo_in_comment', 0);
		$video_width_in_comment        = $params->get('video_width_in_comment', 360);
		$video_height_in_comment       = $params->get('video_height_in_comment', 240);

		
		$regexYoutube = "#(?:http(?:s)?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:v|vi|user)\/))([^\?&\"'<>\/\s]+)(?:$|\/|\?|\&)?#i";
		preg_match_all($regexYoutube, $text, $matchesYoutube);

		if ($auto_embed_youtube_in_comment && count($matchesYoutube[0]))
		{
			foreach ($matchesYoutube[0] AS $key => $match)
			{
				$youtube_html = JUDownloadFrontHelperComment::parseVideo($match, $video_width_in_comment, $video_height_in_comment);
				$text         = str_replace($matchesYoutube[0][$key], $youtube_html . '<br/>', $text);
			}
		}

		
		$regexVimeo = "#(?:http(?:s)?:\/\/)?(?:www\.)?vimeo.com\/(\d+)(?:$|\/|\?)?#";
		preg_match_all($regexVimeo, $text, $matchesVimeo);

		if ($auto_embed_vimeo_in_comment && count($matchesVimeo[0]))
		{
			$arrIdVimeo = array_unique($matchesVimeo[0]);
			foreach ($arrIdVimeo AS $key => $match)
			{
				$vimeo_html = JUDownloadFrontHelperComment::parseVideo($match, $video_width_in_comment, $video_height_in_comment);
				$text       = str_replace($matchesVimeo[0][$key], $vimeo_html, $text);
			}
		}

		return $text;
	}

	
	public static function parseVideo($url, $video_width_in_comment = 360, $video_height_in_comment = 240)
	{
		$document = JFactory::getDocument();

		
		$ytRegex = "#(?:http(?:s)?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'<>\/\s]+)(?:$|\/|\?|\&)?#i";
		preg_match($ytRegex, $url, $ytMatches);

		if (isset($ytMatches[1]))
		{
			$videoId = $ytMatches[1];
			if ($videoId)
			{
				$src          = "http://www.youtube.com/embed/" . $videoId . "?hd=1&wmode=opaque&controls=1&showinfo=0;rel=0";
				$youtube_html = '<iframe width="' . $video_width_in_comment . '" height="' . $video_height_in_comment . '" src="' . $src . '" frameborder="0" allowfullscreen ></iframe>';
			}
			else
			{
				$youtube_html = '';
			}

			$document->addScript("https://www.youtube.com/iframe_api");

			return $youtube_html;
		}

		
		$vmRegex = "#(?:http(?:s)?:\/\/)?(?:www\.)?(?:player\.)?vimeo.com(?:\/video)?\/(\d+)(?:$|\/|\?)?#";
		preg_match($vmRegex, $url, $vmMatches);
		if (isset($vmMatches[1]))
		{
			$videoId = $vmMatches[1];
			if ($videoId)
			{
				$src        = "http://player.vimeo.com/video/" . $videoId . "?title=0&byline=0&portrait=0;api=1";
				$vimeo_html = '<iframe width="' . $video_width_in_comment . '" height="' . $video_height_in_comment . '" src="' . $src . '" frameborder="0" allowfullscreen ></iframe>';
			}
			else
			{
				$vimeo_html = '';
			}
			$document->addScript("http://a.vimeocdn.com/js/froogaloop2.min.js");

			return $vimeo_html;
		}

		return false;
	}

	
	public static function getTotalCommentsOnDocumentOfUser($docId, $userId)
	{
		if (!$userId)
		{
			return 0;
		}
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(*)');
		$query->from('#__judownload_comments');
		$query->where('doc_id = ' . $docId);
		$query->where('user_id = ' . $userId);
		$query->where('level = 1');
		$db->setQuery($query);
		$result = $db->loadResult();

		return $result;
	}

	
	public static function getTotalCommentsOnDocumentForGuest($documentId, $guestEmail)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select("COUNT(*)");
		$query->from('#__judownload_comments');
		$query->where('doc_id = ' . $documentId);
		$query->where('guest_email = ' . $db->quote($guestEmail));
		$query->where('level = 1');
		$db->setQuery($query);
		$total = $db->loadResult();

		return $total;
	}

	
	public static function getTotalApprovedCommentsOfUser($userId)
	{
		if (!$userId)
		{
			return 0;
		}
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(*)');
		$query->from('#__judownload_comments');
		$query->where('user_id = ' . $userId);
		$query->where('approved = 1');
		$query->where('level = 1');
		$db->setQuery($query);
		$result = $db->loadResult();

		return $result;
	}

	
	public static function getTotalApprovedRepliesOfUser($userId)
	{
		if (!$userId)
		{
			return 0;
		}
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(*)');
		$query->from('#__judownload_comments');
		$query->where('user_id = ' . $userId);
		$query->where('approved = 1');
		$query->where('level > 1');
		$db->setQuery($query);
		$result = $db->loadResult();

		return $result;
	}

}