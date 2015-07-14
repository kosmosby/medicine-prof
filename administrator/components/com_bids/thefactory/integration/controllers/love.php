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
 * @subpackage: integration
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

class JTheFactoryIntegrationLoveController extends JControllerLegacy
{
    var $_name='IntegrationLove';
    var $name='IntegrationLove';

	function __construct()
	{
	   $MyApp= JTheFactoryApplication::getInstance();
       
	   $config=array(
            'view_path'=>$MyApp->app_path_admin.'integration'.DS."views",
       );
       JLoader::register('JTheFactoryIntegrationLove',$MyApp->app_path_admin.'integration/lovefactory.php');
       JLoader::register('JTheFactoryIntegrationLoveToolbar',$MyApp->app_path_admin.'integration/toolbar/love.php');
       JLoader::register('JTheFactoryIntegrationLoveHelper',$MyApp->app_path_admin.'integration/helper/love.php');
               
       parent::__construct($config);
    }
    function execute($task)
    {
        JTheFactoryIntegrationLoveToolbar::display($task);
        parent::execute($task);
    }
    function getView( $name = '', $type = 'html', $prefix = '', $config = array() )
    {
        $MyApp= JTheFactoryApplication::getInstance();
        $config['template_path']=$MyApp->app_path_admin.'integration'.DS."views".DS."integrationlove".DS."tmpl";
        return parent::getView($name,$type,'JTheFactoryView',$config);
    }
	
    function display()
    {
        $integrationFields=JTheFactoryIntegrationLove::getIntegrationFields();
        $integrationArray=JTheFactoryIntegrationLove::getIntegrationArray();
        
    	$database =  JFactory::getDBO();
    	$query = "SELECT `name` as value,`title` as text FROM #__comprofiler_fields order by `name`";
    	$database->setQuery($query);
    	$cbfields = $database->loadObjectList();
        $cbfields = array_merge(array(JHTML::_("select.option",'','-'.JText::_("FACTORY_NONE").'-')),$cbfields);
        
        $view=$this->getView();
        $view->assignRef('integrationFields',$integrationFields);
        $view->assignRef('integrationArray',$integrationArray);
        $view->assignRef('lovefields',$cbfields);
        $view->assign('love_detected',JTheFactoryIntegrationLove::detectIntegration());
        
        $view->display();
    }

    function save()
    {
        $MyApp = JTheFactoryApplication::getInstance();
        $tablename=$MyApp->getIniValue("field_map_table","profile-integration");
        
        $fields=JTheFactoryIntegrationCB::getIntegrationFields();
        $db= JFactory::getDBO();
        
        foreach($fields as $field)
        {
            $cb = JRequest::getVar($field,null);
            $db->setQuery("select * from `{$tablename}` where `field`='{$field}'");
            $res= $db->loadObject();
            if ($res)
                $db->setQuery("update `{$tablename}` set `assoc_field`='{$cb}' where `field`='{$field}'");
            else        
                $db->setQuery("insert into `{$tablename}` set `assoc_field`='{$cb}' ,`field`='{$field}'");
            $db->query();
        }
        
        $this->setRedirect("index.php?option=".APP_EXTENSION."&task=integrationLove.display",JText::_("FACTORY_SETTINGS_SAVED"));    
    }
    	
    

	
}


