<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Content categories view.
 *
 * @package		Joomla.Site
 * @subpackage	com_weblinks
 * @since 1.5
 */
class flexpaperViewcourses extends JViewLegacy
{

	/**
	 * Display the view
	 *
	 * @return	mixed	False on error, null otherwise.
	 */
	function display($tpl = null)
	{

        $task =  JRequest::getVar('task');
        $view_menu = false;
        if($task== 'mydocs') {
            $view_menu = true;
        }
        $this->assignRef('view_menu',$view_menu);

        $document =& JFactory::getDocument();

        $document->addStyleSheet(JURI::base().'components/com_ose_cpu/extjs/resources/css/ext-all.css');
        $document->addStyleSheet(JURI::base().'components/com_osemsc/assets/css/msc5.css');

        $document->addStyleSheet(JURI::base().'templates/kalite/css/custom.css"');


        $document->addScript(JURI::base().'components/com_ose_cpu/extjs/adapter/ext/ext-base.js ');
        $document->addScript(JURI::base().'components/com_ose_cpu/extjs/ext-all.js ');
        $document->addScript(JURI::base().'components/com_ose_cpu/extjs/ose/app.msg.js ');

        $document->addScript(JURI::base().'components/com_ose_cpu/extjs/locale/ext-lang-en.js ');
        $document->addScript(JURI::base().'components/com_osemsc/libraries/init.js ');
        $document->addScript(JURI::base().'components/com_osemsc/modules/memberships/ext.memberships.memberships.js ');


        $document->addScript(JURI::base().'components/com_osemsc/views/memberships/js/js.memberships.js ');


        $document->addScript(JURI::base().'components/com_flexpaper/js/jquery.js');
        $document->addScript(JURI::base().'components/com_flexpaper/js/custom.js');

        // Initialise variables
		$items		= $this->get('Items');

         //echo "<pre>";
         //print_r($items); die;

		if($items === false)
		{
			return JError::raiseError(404, 'there are no items for display');
		}

		$this->assignRef('items',$items);

        $itemid = JRequest::getVar('Itemid');
        $this->assignRef('itemid',$itemid);

		parent::display($tpl);
	}

}