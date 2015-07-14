<?php

defined('_JEXEC') or die('Restricted access');

class JTheFactoryEventPermissions extends JTheFactoryEvents
{
    function onBeforeExecuteTask(&$stopexecution)
    {
        $app = JFactory::getApplication();
        if ($app->isAdmin()) {
            return;
        }

        $task = strtolower(JRequest::getCmd('task','listauctions'));
        $controllerClass = JRequest::getWord('controller');
        $acl = BidsHelperTools::getBidsACL();
        $app = JFactory::getApplication();
        $cfg = BidsHelperTools::getConfig();
        $user = JFactory::getUser();
        if (strpos($task,'.')!==FALSE){
            $task=explode('.',$task);
            $controllerClass=$task[0];
            $task=$task[1];
        }
        if (in_array($task,$acl->anonTasks)) {
            return; //Anon Task ok
        }
        if (!$user->id){
            //By default tasks need to be done by logged users

            JError::raiseNotice("701",JText::_("COM_BIDS_YOU_NEED_TO_LOGIN_IN_ORDER_TO_ACCESS_THIS_SECTION"));
            $app->redirect(BidsHelperRoute::getAuctionListRoute(null,false));
            $stopexecution=true;
            return;
        }
        //Only Logged user from now on
        //var_dump($task);exit;
        //User must have his profile Filled for this task
        $userprofile = BidsHelperTools::getUserProfileObject();
        if (!$userprofile->checkProfile($user->id)) {
            //Profile is not filled! we must redirect
            if(!$r = BidsHelperTools::redirectToProfile()) {
                $r = BidsHelperRoute::getUserdetailsRoute();
            }
            $app->redirect($r, JText::_("COM_BIDS_ERR_MORE_USER_DETAILS") );
            $stopexecution=true;
            return;
        }


        if (!$cfg->bid_opt_enable_acl || !isset($acl->taskmapping[$task]))
            return; // no need to check other ACL Seller/Bidder taskmap

        if (!$userprofile)
            $userprofile = BidsHelperTools::getUserProfileObject();

        $userprofile->getUserProfile();

        //$cfg->bidder_groups
        //$cfg->seller_groups
        $user_groups=JAccess::getGroupsByUser($user->id);

        $isBidder=count(array_intersect($user_groups,$cfg->bid_opt_bidder_groups))>0;
        $isSeller=count(array_intersect($user_groups,$cfg->bid_opt_seller_groups))>0;

        if ($acl->taskmapping[$task]=='seller' && !$isSeller)
        {
            //Task allows only SELLERS
            JError::raiseNotice("701",JText::_("COM_BIDS_YOU_NEED_TO_BE_A_SELLER_IN_ORDER_TO_ACCESS_THIS_SECTION"));
            $app->redirect(BidsHelperRoute::getAuctionListRoute(null,false));
            $stopexecution=true;
            return;

        }

        if ($acl->taskmapping[$task]=='bidder' && !$isBidder)
        {
            //Task allows only SELLERS
            JError::raiseNotice("701",JText::_("COM_BIDS_YOU_NEED_TO_BE_A_BIDDER_IN_ORDER_TO_ACCESS_THIS_SECTION"));
            $app->redirect(BidsHelperRoute::getAuctionListRoute(null,false));
            $stopexecution=true;
            $app->close();
            return;

        }
    }

}
