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

class JTheFactoryOrder extends JObject
{
    var $contex=null;
    function __construct()
    {
        $this->contex= APP_PREFIX.'.orderclass';
    }
    static function getInstance()
    {
        static $instance;

        if (!is_object($instance))
            $instance=new JTheFactoryOrder();

        return $instance;

    }

    static function addOrderItem($item=null)
    {
        static $items=array();
        if ($item) $items[]=$item;
        return $items;
    }

}

