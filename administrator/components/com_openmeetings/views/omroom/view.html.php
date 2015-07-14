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
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.view' );

class OpenMeetingsViewOMRoom extends JView {
	protected $form;
	protected $data;
	protected $state;
	protected $defaults;

	function edit($id) {
		// Build the toolbar for the edit function
		JToolBarHelper::title(JText::_('Edit Row') . ': [<small>Edit</small>]');
		JToolBarHelper::save('omroom_save');
		JToolBarHelper::cancel('omroom_cancel');

		// Get the row
		$this->form = $this->get("Form");
		$this->item = $this->get("Data");
		$this->state = $this->get("State");



		parent::display();
	}

	function add() {
		// Build the toolbar for the add function
		JToolBarHelper::title( JText::_('Add Row') . ': [<small>Add</small>]' );
		JToolBarHelper::save('omroom_save');
		JToolBarHelper::cancel('omroom_cancel');

		$this->form		= $this->get('Form');

		parent::display();
	}
}
?>
