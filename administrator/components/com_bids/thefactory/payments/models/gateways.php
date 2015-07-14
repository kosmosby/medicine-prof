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

class JTheFactoryModelGateways extends JModel
{
    var $context='gateways';
    var $tablename=null;
    function __construct()
    {
        $this->context=APP_EXTENSION."_gateways.";
        $this->tablename='#__'.APP_PREFIX.'_paysystems';

        parent::__construct();
    }
    function getGatewayList($enabled=true)
    {
        $where=($enabled)?"where `enabled`=1":"";
        $db= $this->getDbo();
        $db->setQuery("select * from `{$this->tablename}` {$where} order by `ordering` ");
        return $db->loadObjectList();
    }
    function toggle($itemname)
    {
        $db= $this->getDbo();
        $db->setQuery("select enabled from `{$this->tablename}` where id='{$itemname}' ");
        $enabled=$db->loadResult()?"0":"1";
        $db->setQuery("update `{$this->tablename}` set enabled='$enabled' where id='{$itemname}' ");
        $db->query();
        return $enabled?(JText::_("FACTORY_GATEWAY_ENABLED")):(JText::_("FACTORY_GATEWAY_DISABLED"));
    }
    function setdefault($itemname)
    {
        $db= $this->getDbo();
        $db->setQuery("update `{$this->tablename}` set isdefault=0 ");
        $db->query();

        $db->setQuery("update `{$this->tablename}` set isdefault=1 where id='{$itemname}' ");
        $db->query();
    }
    function saveGatewayParams($gateway,$params)
    {
        $db= $this->getDbo();
        $db->setQuery('update `'.$this->tablename.'` set params='.
            $db->quote($params). " where classname=".
            $db->quote($gateway)
        );
        $db->query();

    }
    function loadGatewayParams($gateway)
    {
        $db= $this->getDbo();
        $db->setQuery('select `params` from `'.$this->tablename.'` where classname='.
            $db->quote($gateway)
        );
        $params=$db->loadResult();
        $config = new JRegistry($params);
        return $config;
    }
    function getGatewayObject($name)
    {
        $MyApp= JTheFactoryApplication::getInstance();
        $path=$MyApp->app_path_admin.'payments'.DS.'plugins'.DS.'gateways'.DS.strtolower($name).DS.'controller.php';
        if (!file_exists($path))
            return null;
        require_once($path);
        if (!class_exists($name))
            return null;
        $gateway=new $name();
        return $gateway;
    }
}
?>
