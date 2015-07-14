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
class flexpaperViewCatalogAtx extends JViewLegacy
{

	/**
	 * Display the view
	 *
	 * @return	mixed	False on error, null otherwise.
	 */
	function display($tpl = null)
	{
        $document =& JFactory::getDocument();

        // Initialise variables
		$this->items		= $this->get('Items');

        $this->category = $this->get('Category');

        $app    = JFactory::getApplication();
        $pathway = $app->getPathway();
        $pathway->addItem('Лекарства', 'index.php?option=com_flexpaper&view=catalogcategories');
        $pathway->addItem($this->category, 'index.php?option=com_flexpaper&view=catalogcategories');


        if($this->items === false)
		{
			return JError::raiseError(404, 'there are no items for display');
		}

        $this->itemid = JRequest::getVar('Itemid');

		parent::display($tpl);
	}

}