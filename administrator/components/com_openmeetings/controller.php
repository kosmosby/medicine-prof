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

// import Joomla controller library
jimport('joomla.application.component.controller');

/**
 * General Controller of component
*/
class OpenMeetingsController extends JController
{
	/**
	 * display task
	 *
	 * @return void
	 */
	function display($cachable = false)
	{
		JRequest::setVar('view', JRequest::getCmd('view', 'omrooms'));

		// call parent behavior
		parent::display($cachable);
	}

	function omrooms($cachable = false)
	{
		JRequest::setVar('view', JRequest::getCmd('view', 'omrooms'));

		// call parent behavior
		parent::display($cachable);
	}

	function omroom_add()
	{
		// Set the view for a single revue
		$view =& $this->getView( JRequest::getVar( 'view', 'omroom' ), 'html' );
		$model =& $this->getModel( 'omroom' );
		$view->setModel( $model, true );

		$view->add();
	}

	function omroom_edit()
	{
		// Get the requested id(s) as an array of ids
		$cids = JRequest::getVar('cid', null, 'default', 'array');
		if( $cids === null )
		{
			// Report an error if there was no cid parameter in the request
			JError::raiseError( 500, JText::_('CID_MISSING') );
		}

		// Get the first revue to be edited
		$rowId = (int)$cids[0];

		// Set the view and model for a single revue
		$view =& $this->getView( JRequest::getVar( 'view', 'omroom' ), 'html' );
		$model =& $this->getModel( 'omroom' );
		$view->setModel( $model, true );

		// Display the edit form for the requested revue
		$view->edit( $rowId );
	}

	function omroom_save()
	{
		$model =& $this->getModel( 'omroom' );
		$model->store();

		$redirectTo = JRoute::_('index.php?option=' . JRequest::getVar('option') . '&task=omrooms', false);
		$this->setRedirect( $redirectTo, JText::_('OMROOM_SAVED') );
	}

	function omroom_cancel()
	{
		$redirectTo = JRoute::_('index.php?option=' . JRequest::getVar('option') . '&task=omrooms', false);
		$this->setRedirect( $redirectTo, JText::_('CANCELLED') );
	}

	function omrooms_delete()
	{
		// Retrieve the ids to be removed
		$cids = JRequest::getVar('cid', null, 'default', 'array');
		if( $cids === null )
		{
			// Make sure there were records to be removed
			JError::raiseError( 500, JText::_('CID_MISSING') );
		}

		$model =& $this->getModel( 'omrooms');
		$model->delete( $cids);
		$redirectTo = JRoute::_('index.php?option=' . JRequest::getVar( 'option' ) . '&task=omrooms', false);

		$this->setRedirect( $redirectTo, JText::_('OMROOMS_DELETED') );
	}




}