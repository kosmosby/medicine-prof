<?php

defined('_JEXEC') or die('Restricted access');


class modBidsCategoryTreeHelper {

    static function getCategories($countAuctions) {

        $db = JFactory::getDbo();

        $where = array(
            'c.extension=\'com_bids\'',
            'c.published=1',
        );

        $query = $db->getQuery(true);
        $query->select('c.*, c.level-p.level AS depth, COUNT(DISTINCT s.id) AS nrSubcategories')
                ->from('#__categories AS c')
                ->leftJoin( '#__categories AS p ON c.lft BETWEEN p.lft AND p.rgt' )
                ->leftJoin( '#__categories AS s ON s.parent_id=c.id' )
                ->where($where)
                ->group('c.id')
                ->order('c.lft');
        if($countAuctions) {
            $query->select( 'COUNT(DISTINCT a.id) AS nrAuctions' );
            $query->leftJoin( '`#__bid_auctions` a ON a.cat=c.id AND a.published AND a.close_offer=0 AND a.close_by_admin=0 AND a.start_date<=UTC_TIMESTAMP() AND a.end_date>=UTC_TIMESTAMP()' );
        }

        $db->setQuery($query);

        return $db->loadObjectList();
    }

    static function catHTML( $categories, $countSubcategories, $countAuctions, $moduleId ) {

        $html = '<ul id="bids_treecontainer_'.$moduleId.'" class="filetree closed">';

        $lastKnownDepth = 1;

        foreach($categories as $category) {

            if($category->depth > $lastKnownDepth) {
                $html .= '<ul class="filetree closed">';
            }

            if( ($ascendSteps = $lastKnownDepth - $category->depth) > 0 ) {
                for($i=0; $i<$ascendSteps; $i++) {
                    $html .= '</li></ul>';
                }
                $html .= '</li>';
            }

            $html .= '<li>
                        <span class="'.($category->nrSubcategories ? 'folder' : 'file').' category_css">
                            <a href="'.JRoute::_('index.php?option=com_bids&task=listauctions&cat='.$category->id).'">' .
                                $category->title .
                                ($countAuctions ? (' ( '.$category->nrAuctions.' )') : '' ) .
                                ($countSubcategories ? (' ( '.$category->nrSubcategories.' )') : '' ) .
                            '</a>
                        </span>';



            $lastKnownDepth = $category->depth;
        }

        $html .= '</ul>';

        return $html;
    }
}
