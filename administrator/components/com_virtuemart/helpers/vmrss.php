<?php
/**
 * RSS helper class
 *
 * @package	VirtueMart
 * @subpackage Helpers
 * @author Valerie Isaksen
 * @copyright Copyright (c) 2014 VirtueMart Team and author. All rights reserved.
 */
defined('_JEXEC') or die('Restricted access');
defined('DS') or define('DS', DIRECTORY_SEPARATOR);
//defined('_JEXEC') or define('_JEXEC', 1);


class vmRSS{

	/**
	 * Get feed
	 * @author valerie isaksen
	 * @param $rssUrl
	 * @param $max
	 * @return mixed
	 */
	static public function getCPsRssFeed($rssUrl,$max) {

		$cache_time=2880; // 2days
		$cache = JFactory::getCache ('com_virtuemart_rss');

		$cache->setLifeTime($cache_time);
		$cache->setCaching (1);
		$feeds = $cache->call (array('vmRSS', 'getRssFeed'), $rssUrl, $max);

		return $feeds;
	}

	/**
	 * @author Valerie Isaksen
	 * Returns the RSS feed from Extensions.virtuemart.net
	 * @return mixed
	 */
	public static $extFeeds = 0;
	static public function getExtensionsRssFeed() {
		if (empty(self::$extFeeds)) {
			self::$extFeeds =  self::getCPsRssFeed("http://extensions.virtuemart.net/?format=feed&type=rss", 15);
		}
		return self::$extFeeds;
	}

	/**
	 * @author Valerie Isaksen
	 * Returns the RSS feed from virtuemart.net
	 * @return mixed
	 */
	public static $vmFeeds = 0;
	static public function getVirtueMartRssFeed() {
 		if (empty(self::$vmFeeds)) {
			self::$vmFeeds =  self::getCPsRssFeed("http://virtuemart.net/news/list-all-news?format=feed&type=rss", 5);
		}
		return self::$vmFeeds;
	}

	/**
	 * @param $rssURL
	 * @param $max
	 * @return array|bool
	 */
	static public function getRssFeed($rssURL, $max) {

		if (JVM_VERSION < 3){
			jimport('simplepie.simplepie');
			$rssFeed = new SimplePie($rssURL);

			$feeds = array();
			$count = $rssFeed->get_item_quantity();
			$limit=min($max,$count);
			for ($i = 0; $i < $limit; $i++) {
				$feed = new StdClass();
				$item = $rssFeed->get_item($i);
				$feed->link = $item->get_link();
				$feed->title = $item->get_title();
				$feed->description = $item->get_description();
				$feeds[] = $feed;
			}
			return $feeds;

		} else {
			jimport('joomla.feed.factory');
			$feed = new JFeedFactory;
			$rssFeed = $feed->getFeed($rssURL);

			if (empty($rssFeed) or !is_object($rssFeed)) return false;

			for ($i = 0; $i < $max; $i++) {
				if (!$rssFeed->offsetExists($i)) {
					break;
				}
				$feed = new StdClass();
				$uri = (!empty($rssFeed[$i]->uri) || !is_null($rssFeed[$i]->uri)) ? $rssFeed[$i]->uri : $rssFeed[$i]->guid;
				$text = !empty($rssFeed[$i]->content) || !is_null($rssFeed[$i]->content) ? $rssFeed[$i]->content : $rssFeed[$i]->description;
				$feed->link = $uri;
				$feed->title = $rssFeed[$i]->title;
				$feed->description = $text;
				$feeds[] = $feed;
			}
			return $feeds;
		}

	}
}


// pure php no closing tag
