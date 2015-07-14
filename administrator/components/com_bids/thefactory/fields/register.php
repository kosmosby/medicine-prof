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
 * @subpackage: custom_fields
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

class JTheFactoryFieldsRegister
{
    static function registerModule($app=null)
    {
        if (!$app)
    	   $app= JTheFactoryApplication::getInstance();
		if ($app->getIniValue('use_custom_fields')){
            JLoader::register('FactoryFieldsTbl',$app->app_path_admin."fields".DS.'tables'.DS.'table.class.php');
            JLoader::register('CustomFieldsFactory',$app->app_path_admin.DS.'fields'.DS.'helper'.DS.'factory.class.php');
            JLoader::register('FactoryFieldTypes',$app->app_path_admin.'fields'.DS.'plugins'.DS.'field_types.php');
            JLoader::register('FactoryFieldValidator',$app->app_path_admin.'fields'.DS.'plugins'.DS.'field_validator.php');
            JLoader::register('JTheFactoryListModel',$app->app_path_admin."fields".DS.'models'.DS.'listmodel.php');
            JHtml::addIncludePath($app->app_path_admin.'fields'.DS.'html');
            JTable::addIncludePath($app->app_path_admin.'fields'.DS.'tables');
		}
   }
}
