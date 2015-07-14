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
class flexpaperViewVideoconsultant extends JViewLegacy
{

	/**
	 * Display the view
	 *
	 * @return	mixed	False on error, null otherwise.
	 */
	function display($tpl = null)
	{
        $document =& JFactory::getDocument();

        $document->addScript(JURI::base().'components/com_flexpaper/js/jquery.js');
        $document->addScript(JURI::base().'components/com_flexpaper/js/socket.io.js');
        $document->addScript(JURI::base().'components/com_flexpaper/js/simplewebrtc.bundle.js');

        // Initialise variables
		//$items		= $this->get('Items');

//		if($items === false)
//		{
//			return JError::raiseError(404, 'there are no items for display');
//		}

        //$itemid = JRequest::getVar('Itemid');

        //$this->assignRef('itemid',$itemid);

		//$this->assignRef('items',$items);

		parent::display($tpl);
	}

}