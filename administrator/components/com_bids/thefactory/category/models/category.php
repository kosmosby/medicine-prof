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


jimport('joomla.application.component.model');
jimport('joomla.application.component.helper');

class JTheFactoryModelCategory extends JModel {

    protected $category_table = null;

	function __construct() {

        $myApp= JTheFactoryApplication::getInstance();
        $this->category_table = $myApp->getIniValue('table','categories');

        parent::__construct();
	}

    function getCategoryCount($include_disabled=false) {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $where = array(
                    'extension='.$db->quote(APP_EXTENSION),
                    ( $include_disabled ? '' : 'published=1')
                );

        $query->select('COUNT(1)')
            ->from('#__categories')
            ->where($where);

        $db->setQuery($query);

        return $db->loadResult();
    }

    function getCategoryTree($parentId=1,$include_disabled=false) {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $where = array(
                    'c.extension='.$db->quote(APP_EXTENSION),
                    'p.id='.$db->quote($parentId)
                );

        if(!$include_disabled) {
            $where[] = 'c.published=1';
        }

        $query->select('c.*, c.level-p.level AS depth')
            ->from('#__categories AS c')
            ->leftJoin('#__categories AS p ON c.lft BETWEEN p.lft AND p.rgt')
            ->where($where)
            ->order('c.lft');

        $db->setQuery($query);

        return $db->loadObjectList('id');
    }

    function getCategoriesNested($parentid=0,$enabled_only=true) {

        return $this->getCategoryTree($parentid,$enabled_only);
    }

    function getCategoryPathString($catid) {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('path')
            ->from('#__categories')
            ->where('id='.$db->quote($catid));

        $db->setQuery($query);

        return $db->loadResult();
    }
}
