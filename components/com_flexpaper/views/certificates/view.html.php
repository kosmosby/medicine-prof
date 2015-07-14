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
class flexpaperViewCertificates extends JViewLegacy
{

	/**
	 * Display the view
	 *
	 * @return	mixed	False on error, null otherwise.
	 */
	function display($tpl = null)
	{

        $document =& JFactory::getDocument();

        $document->addScript('administrator/components/com_flexpaper/js/jquery.js');
        $document->addScript('administrator/components/com_flexpaper/js/admin.js');


        // Initialise variables
		$items		= $this->get('Items');
 
//        echo "<pre>";
//        print_r($items); die;


		if($items === false)
		{
			return JError::raiseError(404, 'there are no items for display');
		}

        $mosConfig_live_site = 'http://'. $_SERVER['HTTP_HOST'];
        $this->assignRef('path',$mosConfig_live_site);

        $this->assignRef('items',$items);

        $itemid = JRequest::getVar('Itemid');
        $this->assignRef('itemid',$itemid);


        parent::display($tpl);
	}

}