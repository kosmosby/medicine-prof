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


class BidsHelperProfile extends JTheFactoryUserProfile {

    //need to overwrite the singleton method so we can load the profile using "getUserProfile" defined in child class (this one)
    static function &getInstance($profile_mode='component',$userid=0) {

        if(!$userid) {
            $userid = JFactory::getUser()->id;
        }

        if ( !isset (self::$instances[$userid])) {
            self::$instances[$userid] = new self($profile_mode);
            self::$instances[$userid]->getUserProfile($userid);
        }

        return self::$instances[$userid];
    }

    function getUserProfile($userid=null) {

        parent::getUserProfile($userid);

        $cfg = BidsHelperTools::getConfig();

        //override main profile behavior with some component specific rules
        if(!$cfg->bid_opt_enable_acl) {
            $this->isSeller = 1-$this->guest;
            $this->isBidder = 1-$this->guest;
        } else {
            $u = JFactory::getUser($userid);

            $this->isBidder = count( array_intersect($u->groups,$cfg->bid_opt_bidder_groups) ) ? 1:0;
            $this->isSeller = count( array_intersect($u->groups,$cfg->bid_opt_seller_groups) ) ? 1:0;
        }
    }
}
