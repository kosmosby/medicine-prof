<?php

defined('_JEXEC') or die('Restricted access.');

JLoader::register('bidsModelAuction',JPATH_COMPONENT_SITE.DS.'models'.DS.'auction.php');

jimport('joomla.application.component.modelform');

class JBidsAdminModelAuction extends bidsModelAuction {}
