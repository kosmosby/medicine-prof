<?php

defined('_JEXEC') or die('Restricted access');

class JTheFactoryEventAddSubMenu extends JTheFactoryEvents {

    function onAfterExecuteTask($controller) {

        $controllerName = strtolower($controller->getName());
        $method = strtolower($controller->get('task'));

        $task = $controllerName.'.'.$method;

        //what tasks need the submenuhelper added?
        $aTasks = array(
            'config.display',
            'orders.listing',
            'payments.listing',
            'balances.listing',
            'themes.listthemes',
            'currencies.listing',
            'pricing.listing',
            'gateways.listing',
            'mailman.mails',
            'about.main'
        );

        if(!in_array($task,$aTasks)) {
            return;
        }

        BidsHelperAdmin::subMenuHelper();
    }
}
