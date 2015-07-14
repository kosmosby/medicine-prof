<?php
/**------------------------------------------------------------------------
thefactory - The Factory Class Library - v 2.0.0
------------------------------------------------------------------------
 * @author TheFactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thefactory.ro
 * Technical Support: Forum - http://www.thefactory.ro/joomla-forum/
 * @build: 01/04/2012
 * @package: thefactory
 * @subpackage: payments
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

class JTheFactoryModelPricing extends JModel
{
    var $context='pricing';
    var $tablename=null;
    function __construct()
    {
        $this->context=APP_EXTENSION."_pricing.";
        $this->tablename='#__'.APP_PREFIX.'_pricing';

        parent::__construct();
    }
    function getPricingList()
    {
        $db= $this->getDbo();
        $db->setQuery("select * from `{$this->tablename}` order by `ordering` ");
        return $db->loadObjectList();
    }
    function toggle($itemname)
    {
        $db= $this->getDbo();
        $db->setQuery("select enabled from `{$this->tablename}` where itemname='{$itemname}' ");
        $enabled=$db->loadResult()?"0":"1";
        $db->setQuery("update `{$this->tablename}` set enabled='$enabled' where itemname='{$itemname}' ");
        $db->query();
        return $enabled?(JText::_("FACTORY_ITEM_ENABLED")):(JText::_("FACTORY_ITEM_DISABLED"));
    }
}
?>
