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
 * @subpackage: positions
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

class JTheFactoryPositionsController extends JTheFactoryController
{
    var $name='Positions';
    var $_name='Positions';
	function __construct()
	{
       parent::__construct();
       $MyApp= JTheFactoryApplication::getInstance();
       JLoader::register('JTheFactoryThemesHelper',$MyApp->app_path_admin.'themes/helper/themes.php');

    }
    function listPages()
    {
        $model= JModel::getInstance('Positions','JTheFactoryModel');

        $theme=JTheFactoryThemesHelper::getCurrentTheme();
        $themeheader=JTheFactoryThemesHelper::getThemeHeader($theme);
        $pages=JTheFactoryThemesHelper::getThemePages($theme);

        for($i=0;$i<count($pages);$i++)
            $pages[$i]->fields=$model->getFieldsForPage($pages[$i]->name);

        $view=$this->getView('pages');
        $view->assignRef('pages',$pages);
        $view->assignRef('themeheader',$themeheader);
        $view->display('list');
    }
    function listPositions()
    {
        $page=JRequest::getVar('page');
        if (is_array($page)) $page=$page[0];
        
        $model= JModel::getInstance('Positions','JTheFactoryModel');
        $theme=JTheFactoryThemesHelper::getCurrentTheme();
        $themeheader=JTheFactoryThemesHelper::getThemeHeader($theme);
        $pages=JTheFactoryThemesHelper::getThemePages($theme);

        if(!$page && count($pages)) $page= $pages[0]->name;// default
        $positions=JTheFactoryThemesHelper::getPagePositions($theme,$page);

        for($i=0;$i<count($pages);$i++)
            $pages[$i]->fields=$model->getFieldsForPage($pages[$i]->name);

        for($i=0;$i<count($positions);$i++)
            $positions[$i]->fields=$model->getFieldsForPosition($positions[$i]->pagename,$positions[$i]->name);

        $pagehtml=JTheFactoryPositionsHelper::htmlPageSelect($pages,$page);

        $view=$this->getView('positions');
        $view->assignRef('positions',$positions);
        $view->assignRef('themeheader',$themeheader);
        $view->assignRef('pagehtml',$pagehtml);
        $view->assignRef('page',$page);
        $view->display('list');
    }
    function listFields()
    {
        $page=JRequest::getString('page');
        $position=JRequest::getVar('position');
        if (is_array($position)) $position=$position[0];

        $model= JModel::getInstance('Positions','JTheFactoryModel');
        $theme=JTheFactoryThemesHelper::getCurrentTheme();
        $themeheader=JTheFactoryThemesHelper::getThemeHeader($theme);
        $pages=JTheFactoryThemesHelper::getThemePages($theme);
        
        if(!$page && count($pages)) $page=$pages[0]->name;
        $positions=JTheFactoryThemesHelper::getPagePositions($theme,$page);

        if(!$page && count($pages)) $page= $pages[0]->name;// default
        if(!$position && count($positions)) $position= $positions[0]->name;// default

        for($i=0;$i<count($pages);$i++)
            $pages[$i]->fields=$model->getFieldsForPage($pages[$i]->name);

        for($i=0;$i<count($positions);$i++)
            $positions[$i]->fields=$model->getFieldsForPosition($positions[$i]->pagename,$positions[$i]->name);

        $pagehtml=JTheFactoryPositionsHelper::htmlPageSelect($pages,$page);
        $positionshtml=JTheFactoryPositionsHelper::htmlPositionSelect($positions,$position);
        $fields=$model->getFieldsForPosition($page,$position);

        $view=$this->getView('fields');

        $view->assignRef('fields',$fields);
        $view->assignRef('themeheader',$themeheader);
        $view->assignRef('pagehtml',$pagehtml);
        $view->assignRef('positionshtml',$positionshtml);
        $view->assignRef('page',$page);
        $view->assignRef('position',$position);

        $view->display('list');
    }
    function assignFields()
    {
        $page=JRequest::getString('page');
        $position=JRequest::getVar('position');
        $model= JModel::getInstance('Positions','JTheFactoryModel');
        $theme=JTheFactoryThemesHelper::getCurrentTheme();
        
        $theme_page=JTheFactoryThemesHelper::getPage($theme,$page);
        $db_page=(string)$theme_page->attributes()->fieldpage;
  
        $fields=$model->getFieldsForPosition($page,$position);
        $fields_all=$model->getAllFields($db_page);

        
        $htmlfields_all=JTheFactoryPositionsHelper::htmlFieldsMultiselect($fields_all,'fields_all[]','','',$fields);
        $htmlfields=JTheFactoryPositionsHelper::htmlFieldsMultiselect($fields,'fields[]');

        JHTML::_( 'behavior.mootools' );//load mootools before fields.js
        JHTML::script("administrator/components/".APP_EXTENSION."/thefactory/positions/js/positions.js");
        $view=$this->getView('fields');

        $view->assignRef('fields',$fields);
        $view->assignRef('allfields',$fields_all);
        $view->assignRef('pageobj',$theme_page);
        $view->assignRef('page',$page);
        $view->assignRef('position',$position);
        $view->assignRef('htmlfields_all',$htmlfields_all);
        $view->assignRef('htmlfields',$htmlfields);
        
        $view->display('assign');
    }
    function cancelAssigns()
    {
        $page=JRequest::getString('page');
        $position=JRequest::getVar('position');
        $this->setRedirect('index.php?option='.APP_EXTENSION.'&task=positions.listfields&page='.$page.'&position='.$position);
    }
    function saveAssigns()
    {
        $page=JRequest::getString('page');
        $position=JRequest::getVar('position');
        $fields=JRequest::getVar('fields');

        $model= JModel::getInstance('Positions','JTheFactoryModel');

        $model->deleteFieldsFromPosition($page,$position);
        $model->addFieldsToPosition($page,$position,$fields);

        $this->setRedirect('index.php?option='.APP_EXTENSION.'&task=positions.listfields&page='.$page.'&position='.$position);
    }
    function delete()
    {
        $page=JRequest::getString('page');
        $position=JRequest::getVar('position');
        $fieldid=JRequest::getVar('field');

        $model= JModel::getInstance('Positions','JTheFactoryModel');

        $model->deleteFieldsFromPosition($page,$position,$fieldid);

        $this->setRedirect('index.php?option='.APP_EXTENSION.'&task=positions.listfields&page='.$page.'&position='.$position);
    }
}
