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

class JTheFactoryIntegrationController extends JControllerLegacy{
    var $_name='Integration';
    var $name='Integration';
    var $modulename='Integration';
	function __construct()
	{
	   $MyApp= JTheFactoryApplication::getInstance();
       
	   $config=array(
            'view_path'=>$MyApp->app_path_admin.'integration'.DS."views",
       );
        parent::__construct($config);
       $lang=JFactory::getLanguage();
       $lang->load('thefactory.'.strtolower($this->modulename));
    }
    function getView( $name = '', $type = '', $prefix = '', $config = array() )
    {
        $MyApp= JTheFactoryApplication::getInstance();
        $config['template_path']=$MyApp->app_path_admin.'integration'.DS."views".DS."integration".DS."tmpl";
        return parent::getView($name,$type,'JTheFactoryView',$config);
    }	



}

?>
