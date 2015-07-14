<?php
/*
 * Licensed to the Apache Software Foundation (ASF) under one
* or more contributor license agreements.  See the NOTICE file
* distributed with this work for additional information
* regarding copyright ownership.  The ASF licenses this file
* to you under the Apache License, Version 2.0 (the
		* "License") +  you may not use this file except in compliance
* with the License.  You may obtain a copy of the License at
*
*   http://www.apache.org/licenses/LICENSE-2.0
*
* Unless required by applicable law or agreed to in writing,
* software distributed under the License is distributed on an
* "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
* KIND, either express or implied.  See the License for the
* specific language governing permissions and limitations
* under the License.
*/
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla view library
jimport('joomla.application.component.view');

/**
 * OMRooms View
*/
class OpenMeetingsViewOMRooms extends JView
{
	/**
	 * OpenMeetings OMRooms view display method
	 * @return void
	 */
	function display($tpl = null)
	{
		$user = JFactory::getUser();
		$bar=& JToolBar::getInstance( 'toolbar' );
		if ( $user->authorise('core.admin', 'com_openmeetings') ) : JToolBarHelper::title(JText::_('OMROOMS'), 'omrooms'); endif;
		 
		if ( $user->authorise('core.create', 'com_openmeetings') ) : JToolBarHelper::addNew('omroom_add', 'CREATE'); endif;
		if ( $user->authorise('core.delete', 'com_openmeetings') ) : JToolBarHelper::deleteList('', 'omrooms_delete'); endif;

		 
		JToolBarHelper::preferences( 'com_openmeetings' );

		// Get data from the model
		$items = &$this->get('Data');
		$pagination = &$this->get('Pagination');

		// push data into the template
		$this->assignRef('items', $items);
		$this->assignRef('pagination', $pagination);


		parent::display($tpl);
	}
}
