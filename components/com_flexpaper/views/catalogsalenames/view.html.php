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
class flexpaperViewCatalogSaleNames extends JViewLegacy
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

        $this->category		= $this->get('Category');
        $this->cat_id = $this->get('cat_id');
        $this->atx		= $this->get('Atx');

        $app    = JFactory::getApplication();
        $pathway = $app->getPathway();
        $pathway->addItem('Лекарства', 'index.php?option=com_flexpaper&view=catalogcategories');
        $pathway->addItem($this->category, 'index.php?option=com_flexpaper&task=catalogatx&view=catalogatx&cat_id='.$this->cat_id);
        $pathway->addItem($this->atx, 'index.php?option=com_flexpaper&view=catalogcategories');


//        echo "<pre>";
//        print_r($this->items); die;

		if($this->items === false)
		{
			return JError::raiseError(404, 'there are no items for display');
		}

        $this->itemid = JRequest::getVar('Itemid');

		parent::display($tpl);
	}

}