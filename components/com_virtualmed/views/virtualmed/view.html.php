<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_helloworld
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * HTML View class for the HelloWorld Component
 *
 * @since  0.0.1
 */
class VirtualmedViewVirtualmed extends JViewLegacy
{
	/**
	 * Display the Hello World view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	function display($tpl = null)
	{
		// Assign data to the view
		$this->items = $this->get('Items');

        $document = JFactory::getDocument();
        $document->addStyleSheet(JUri::base() . 'components/com_virtualmed/bootstrap-treeview/bower_components/bootstrap/dist/css/bootstrap.css');
        $document->addStyleSheet(JUri::base() . 'components/com_comprofiler/plugin/user/plug_cbactivity/templates/default/template.css?v=0f0c3b2aec30252f');

        $document->addStyleSheet(JUri::base() . 'components/com_virtualmed/css/custom.css');

        $document->addScript(JUri::base() . 'components/com_virtualmed/bootstrap-treeview/bower_components/jquery/dist/jquery.js');
        $document->addScript(JUri::base() . 'components/com_virtualmed/bootstrap-treeview/js/bootstrap-treeview.js');
        $document->addScript(JUri::base() . 'components/com_virtualmed/js/custom.js');

        // Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JLog::add(implode('<br />', $errors), JLog::WARNING, 'jerror');

			return false;
		}

		// Display the view
		parent::display($tpl);
	}
}
