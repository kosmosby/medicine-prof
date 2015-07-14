<?php
/**------------------------------------------------------------------------
com_bids - Auction Factory 2.5.0
------------------------------------------------------------------------
 * @author TheFactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thefactory.ro
 * Technical Support: Forum - http://www.thefactory.ro/joomla-forum/
-------------------------------------------------------------------------*/


defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
jimport('joomla.application.component.helper');

class bidsModelBidsCategory extends JModel
{
    protected $categories = null;

    public function loadCategoryTree($parentId=1,$max_depth=null,$watchlist_user=null,$countAuctions=false,$filterLetter='all') {

        $db = $this->getDbo();

        $where = array(
                    'c.extension='.$db->quote('com_bids'),
                    'c.published=1',
                    'p.id='.$db->quote($parentId),
                    'c.id<>'.$db->quote($parentId)//exclude parent
                );

        if($max_depth) {
            $where[] = 'c.level<=(p.level+'.$max_depth.')';
        }

        if ('all' != $filterLetter) {
            $where[] = " `c`.`title` LIKE '" . trim($db->escape($filterLetter)) . "%' AND c.level=1 ";
        }

        $query = $db->getQuery(true);
        $query->select('c.*, c.level-p.level AS depth, COUNT(DISTINCT s.id) AS nrSubcategories')
                ->from('#__categories AS c')
                ->leftJoin( '#__categories AS p ON c.lft BETWEEN p.lft AND p.rgt' )
                ->leftJoin( '#__categories AS s ON s.parent_id=c.id' )
                ->where($where)
                ->order('c.lft')
                ->group('c.id');

        if ($watchlist_user) {
            $query->select('COUNT(w.id) AS watchListed_flag');
            $query->leftJoin('`#__bid_watchlist_cats` w ON w.catid=c.id and w.userid='.$db->quote($watchlist_user) );
        }
        if($countAuctions) {
            $query->select( 'COUNT(DISTINCT a.id) AS nrAuctions' );
            $query->leftJoin( '`#__bid_auctions` a ON a.cat=c.id AND a.published AND a.close_offer=0 AND a.close_by_admin=0 AND a.start_date<=UTC_TIMESTAMP() AND a.end_date>=UTC_TIMESTAMP()' );
        }

        $db->setQuery($query);

        $this->categories = $db->loadObjectList('id');
    }

    // CATEGORIES WATCHLIST (favoriteS)
    public function addWatch($id)
    {
        $db = JFactory::getDBO();
        $user = JFactory::getUser();

        if($user->guest || !$id) {
            return false;
        }

        $db->setQuery("INSERT INTO #__bid_watchlist_cats SET userid = " . $db->quote($user->id) . ",catid=" . $db->quote($id) );

        return $db->query();
    }

    public function delWatch($id)
    {
        $db = JFactory::getDBO();
        $user = JFactory::getUser();

        if($user->guest || !$id) {
            return false;
        }

        $db->setQuery("DELETE FROM #__bid_watchlist_cats WHERE userid = " . $db->quote($user->id) . " AND catid=" . $db->quote($id) );

        return $db->query();
    }

    public function getParent($id) {

        $db = JFactory::getDbo();
        $db->setQuery('SELECT p.*
                        FROM #__categories AS c
                        LEFT JOIN #__categories AS p
                            ON c.parent_id=p.id
                        WHERE c.id='.$db->quote($id) );

        return $db->loadObject();
    }

    public function getCategoryPathString($catid) {

        $database = JFactory::getDbo();

        $q = $database->getQuery(true);
        $q->select('p.*')
            ->from('#__categories c')
            ->leftJoin('#__categories p ON c.lft BETWEEN p.lft AND p.rgt')
            ->where('p.extension=\'com_bids\' AND p.published=1 AND c.id='.$database->quote($catid))
            ->order('p.lft ASC');
        $database->setQuery($q);
        $pathRows = $database->loadObjectList();

        $path = array();
        foreach($pathRows as $r) {

            if($r->id==1) {
                //category system root
                continue;
            }

            $path[] = JFilterOutput::stringURLUnicodeSlug($r->title);
        }

        return $path;
    }
}
