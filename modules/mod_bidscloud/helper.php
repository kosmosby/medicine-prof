<?php
/**
 * @package AuctionsFactory
 * @version 1.5.0
 * @copyright www.thefactory.ro
 * @license: commercial
*/
defined('_JEXEC') or die('Restricted access');

class mod_bidscloudHelper
{
	static function getTags()
	{
        $db = JFactory::getDBO();

        $query = $db->getQuery(true);
        $query->select('t.id,t.tagname,t.auction_id')
            ->from('#__bid_tags AS t')
            ->leftJoin('#__bid_auctions AS a ON t.auction_id=a.id')
            ->where('a.close_offer=0 AND a.published=1')
            ->group('t.id');
		
		$db->setQuery($query);

		return $db->loadObjectList('id');
	} 

	static function shuffle_assoc(&$array) {

		if (count($array)>1) {

            $keys = array_keys($array);
            shuffle($keys);

            $new = array();
            foreach($keys as $key) {
                $new[$key] = $array[$key];
            }

            $array = $new;
		}
		return true;
	}
}