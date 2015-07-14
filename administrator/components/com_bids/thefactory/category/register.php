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
 * @subpackage: category
-------------------------------------------------------------------------*/ 
defined('_JEXEC') or die('Restricted access');

class JTheFactoryCategoryRegister {

    static function registerModule($app=null) {

        if (!$app) {
            $app= JTheFactoryApplication::getInstance();
        }

        if ($app->getIniValue('use_category_management')) {
            JLoader::register('JTheFactoryCategoryTable',$app->app_path_admin.'category'.DS.'table.category.php');
            JHtml::addIncludePath($app->app_path_admin.'category'.DS.'html');
            JModelLegacy::addIncludePath($app->app_path_admin.'category'.DS.'models');
            $lang=JFactory::getLanguage();
            $lang->load('thefactory.category',JPATH_ADMINISTRATOR);
        }
    }
}
