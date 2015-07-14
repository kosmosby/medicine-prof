<?php

defined('_JEXEC') or die('Restricted access');

class BidsHelperView
{
    static function prepareCategoryTree(&$categories,$task='categories')
    {
        uasort($categories,array('self','levelSort'));

        foreach($categories as $k=>$cat) {

            $cat->link = BidsHelperRoute::getCategoryRoute($cat->id,$task);
            $cat->view = BidsHelperRoute::getAuctionListRoute(array('cat'=>$cat->id));
            if (isset($cat->watchListed_flag)) {
                $cat->link_watchlist = $cat->watchListed_flag ? BidsHelperRoute::getDelToCatWatchlist($cat->id) : BidsHelperRoute::getAddToCatWatchlist($cat->id);
            }

            if(isset($categories[$cat->parent_id]) ) {
                $categories[$cat->parent_id]->subcategories[] = $cat;
                unset($categories[$k]);
            }
        }
    }

    //sort categories by level (depth) DESCENDING, so the tree structure can be built with just one loop
    static function levelSort($cat1,$cat2) {

        if($cat1->level==$cat2->level) {
            return $cat1->lft < $cat2->lft ? -1 : 1;// keep the original order for siblings
        }
        return $cat1->level > $cat2->level ? -1 : 1;
    }
}
